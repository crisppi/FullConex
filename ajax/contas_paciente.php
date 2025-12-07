<?php
// ajax/contas_paciente.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Garantir que o CWD é a raiz do projeto
$ROOT = dirname(__DIR__);
chdir($ROOT);

require_once 'globals.php';
require_once 'db.php';
require_once 'models/message.php';

// IMPORTANTE: carregue o model ANTES do DAO, pq o seu DAO usa require_once("./models/...") relativo
require_once 'models/capeante.php';
require_once 'dao/capeanteDao.php';

try {
    if (!isset($_SESSION['id_usuario'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'não autorizado']);
        exit;
    }

    $pacId = filter_input(INPUT_GET, 'id_paciente', FILTER_VALIDATE_INT);
    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    if (!$pacId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id_paciente obrigatório']);
        exit;
    }

    $dao = new capeanteDAO($conn, $BASE_URL);

    // --------- TOTAL (COUNT) ----------
    // Contamos quantos capeantes existem para internações do paciente
    $stmtCount = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM tb_capeante ca
        JOIN tb_internacao ac ON ca.fk_int_capeante = ac.id_internacao
        WHERE ac.fk_paciente_int = :pac
    ");
    $stmtCount->bindValue(':pac', $pacId, PDO::PARAM_INT);
    $stmtCount->execute();
    $total = (int)($stmtCount->fetchColumn() ?: 0);

    // --------- RESUMO (somas e status) ----------
    $stmtSum = $conn->prepare("
        SELECT 
            COALESCE(SUM(ca.valor_apresentado_capeante),0) AS soma_apresentado,
            COALESCE(SUM(ca.valor_final_capeante),0)       AS soma_final,
            COALESCE(SUM(ca.valor_glosa_total),0)          AS soma_glosa_total,
            COALESCE(SUM(ca.glosa_diaria),0)               AS soma_glosa_diaria,
            COALESCE(SUM(ca.glosa_honorarios),0)           AS soma_glosa_honorarios,
            COALESCE(SUM(ca.glosa_matmed),0)               AS soma_glosa_matmed,
            COALESCE(SUM(ca.glosa_oxig),0)                 AS soma_glosa_oxig,
            COALESCE(SUM(ca.glosa_sadt),0)                 AS soma_glosa_sadt,
            COALESCE(SUM(ca.glosa_taxas),0)                AS soma_glosa_taxas,
            COALESCE(SUM(ca.glosa_opme),0)                 AS soma_glosa_opme,
            SUM(CASE WHEN ca.em_auditoria_cap = 1 THEN 1 ELSE 0 END) AS em_auditoria,
            SUM(CASE WHEN ca.aberto_cap = 1       THEN 1 ELSE 0 END) AS abertos,
            SUM(CASE WHEN ca.encerrado_cap = 1    THEN 1 ELSE 0 END) AS encerrados,
            COUNT(*) AS total_contas,
            COUNT(DISTINCT ca.fk_int_capeante) AS total_internacoes,
            AVG(DATEDIFF(
                COALESCE(ca.data_final_capeante, ca.data_fech_capeante, ca.data_digit_capeante, al.data_alta_alt, ac.data_intern_int),
                COALESCE(ca.data_inicial_capeante, ac.data_intern_int)
            )) AS media_dias,
            COALESCE(SUM(ca.valor_final_capeante),0) / NULLIF(COUNT(*),0) AS custo_medio_conta
        FROM tb_capeante ca
        JOIN tb_internacao ac ON ca.fk_int_capeante = ac.id_internacao
        LEFT JOIN (
            SELECT a.*
            FROM tb_alta a
            WHERE a.id_alta = (
                SELECT a2.id_alta FROM tb_alta a2
                WHERE a2.fk_id_int_alt = a.fk_id_int_alt
                ORDER BY COALESCE(a2.data_alta_alt, '0000-00-00') DESC, a2.id_alta DESC
                LIMIT 1
            )
        ) AS al ON al.fk_id_int_alt = ac.id_internacao
        WHERE ac.fk_paciente_int = :pac
    ");
    $stmtSum->bindValue(':pac', $pacId, PDO::PARAM_INT);
    $stmtSum->execute();
    $summary = $stmtSum->fetch(PDO::FETCH_ASSOC) ?: [];
    if ($summary) {
        $summary['media_permanencia'] = isset($summary['media_dias']) && $summary['media_dias'] !== null
            ? round((float)$summary['media_dias'], 1) . ' dias'
            : null;
        $totalInternacoes = (int)($summary['total_internacoes'] ?? 0);
        $summary['custo_medio_internacao'] = $totalInternacoes > 0
            ? (float)($summary['soma_final'] ?? 0) / $totalInternacoes
            : 0;
    }

    // --------- LISTA (paginada) ----------
    // usando seu selectAllcapeante(where, order, limit) — ATENÇÃO: ele concatena strings, então garanta ints
    $where = "ac.fk_paciente_int = " . (int)$pacId;
    $order = "ca.id_capeante DESC";
    $limitSql = $offset . ", " . $limit;

    $rows = $dao->selectAllcapeante($where, $order, $limitSql) ?: [];

    // formatação leve
    $fmtDate = function ($d) {
        if (!$d || $d === '0000-00-00') return null;
        $dt = DateTime::createFromFormat('Y-m-d', $d) ?: new DateTime($d);
        return $dt ? $dt->format('d/m/Y') : null;
    };

    $parseDate = function ($d) {
        if (!$d || $d === '0000-00-00') return null;
        try {
            return new DateTime($d);
        } catch (Throwable $e) {
            return null;
        }
    };

    $earliestDigitByIntern = [];
    foreach ($rows as $tmp) {
        $idInt = (int)($tmp['id_internacao'] ?? 0);
        if ($idInt <= 0) continue;
        $digit = $parseDate($tmp['data_digit_capeante'] ?? null);
        if (!$digit) continue;
        if (!isset($earliestDigitByIntern[$idInt]) || $digit < $earliestDigitByIntern[$idInt]) {
            $earliestDigitByIntern[$idInt] = $digit;
        }
    }

    $calcCycle = function (array $row) use ($parseDate, $earliestDigitByIntern) {
        $idInt = (int)($row['id_internacao'] ?? 0);
        $baseDigit = $idInt && isset($earliestDigitByIntern[$idInt])
            ? clone $earliestDigitByIntern[$idInt]
            : $parseDate($row['data_digit_capeante'] ?? null);
        if (!$baseDigit) {
            return [null, null, null];
        }

        $admissao = $parseDate($row['data_intern_int'] ?? null);

        $cycleIndex = (int)($row['parcial_num'] ?? 1);
        if ($cycleIndex <= 0) $cycleIndex = 1;

        $cycleStart = clone $baseDigit;
        if ($cycleIndex > 1) {
            $cycleStart->modify('+' . ($cycleIndex - 1) . ' month');
        }
        if ($admissao && $cycleStart < $admissao) {
            $cycleStart = clone $admissao;
        }

        $cycleEnd = (clone $cycleStart)->modify('+1 month');
        return [$cycleIndex, $cycleStart, $cycleEnd];
    };

    $payload = array_map(function ($r) use ($fmtDate, $calcCycle) {
        // flags/valores vêm como strings ou ints do banco
        $status = '—';
        if (isset($r['encerrado_cap']) && (int)$r['encerrado_cap'] === 1)       $status = 'Encerrado';
        elseif (isset($r['em_auditoria_cap']) && (int)$r['em_auditoria_cap']===1) $status = 'Em Auditoria';
        elseif (isset($r['aberto_cap']) && (int)$r['aberto_cap'] === 1)         $status = 'Aberto';

        $rawFlag = $r['parcial_capeante'] ?? null;
        $isParcial = false;
        if ($rawFlag !== null) {
            $str = strtolower(trim((string)$rawFlag));
            $isParcial = in_array($str, ['s', '1', 'sim', 'true'], true);
        }
        $parcialNum = (int)($r['parcial_num'] ?? 0);
        if (!$isParcial && $parcialNum > 0) $isParcial = true;
        $inicioRaw = $r['data_inicial_capeante'] ?? null;
        $fimRaw = $r['data_final_capeante'] ?? null;
        $inicioIso = ($inicioRaw && $inicioRaw !== '0000-00-00') ? $inicioRaw : null;
        $fimIso = ($fimRaw && $fimRaw !== '0000-00-00') ? $fimRaw : null;
        $isPeriodoAberto = $fimIso === null;

        [$cycleIndex, $cycleStartDt, $cycleEndDt] = $calcCycle($r);
        $cycleStartIso = $cycleStartDt ? $cycleStartDt->format('Y-m-d') : null;
        $cycleEndIso = $cycleEndDt ? $cycleEndDt->format('Y-m-d') : null;
        $cycleLabel = ($cycleStartDt && $cycleEndDt)
            ? $cycleStartDt->format('d/m/Y') . ' a ' . $cycleEndDt->format('d/m/Y')
            : null;

        return [
            'id_internacao'      => (int)($r['id_internacao'] ?? 0),
            'id_capeante'        => (int)($r['id_capeante'] ?? 0),
            'hospital'           => $r['nome_hosp'] ?? '',
            'periodo'            => trim(($fmtDate($r['data_inicial_capeante'] ?? null) ?: '—') . ' a ' . ($fmtDate($r['data_final_capeante'] ?? null) ?: '—')),
            'periodo_inicio_raw' => $inicioIso,
            'periodo_fim_raw'    => $fimIso,
            'periodo_em_aberto'  => $isPeriodoAberto,
            'cycle_num'          => $cycleIndex,
            'cycle_inicio_raw'   => $cycleStartIso,
            'cycle_fim_raw'      => $cycleEndIso,
            'cycle_label'        => $cycleLabel,
            'valor_apresentado'  => (float)($r['valor_apresentado_capeante'] ?? 0),
            'valor_final'        => (float)($r['valor_final_capeante'] ?? 0),
            'glosa_total'        => (float)($r['valor_glosa_total'] ?? 0),
            'parcial'            => $isParcial ? ('Parcial #' . ($parcialNum ?: 1)) : '—',
            'is_parcial'         => $isParcial,
            'parcial_numero'     => $isParcial ? ($parcialNum ?: null) : null,
            'status'             => $status,
            'id_valor'           => isset($r['id_valor']) ? (int)$r['id_valor'] : null,
            'data_fechamento'    => $fmtDate($r['data_fech_capeante'] ?? null),
            'data_lancamento'    => $fmtDate($r['data_digit_capeante'] ?? null),
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'total'   => $total,
        'page'    => $page,
        'limit'   => $limit,
        'summary' => [
            'soma_apresentado'    => (float)($summary['soma_apresentado'] ?? 0),
            'soma_final'          => (float)($summary['soma_final'] ?? 0),
            'soma_glosa_total'    => (float)($summary['soma_glosa_total'] ?? 0),
            'em_auditoria'        => (int)($summary['em_auditoria'] ?? 0),
            'abertos'             => (int)($summary['abertos'] ?? 0),
            'encerrados'          => (int)($summary['encerrados'] ?? 0),
            'total_contas'        => (int)($summary['total_contas'] ?? $total),
            'total_internacoes'   => (int)($summary['total_internacoes'] ?? 0),
            'custo_medio_conta'   => (float)($summary['custo_medio_conta'] ?? 0),
            'custo_medio_internacao' => (float)($summary['custo_medio_internacao'] ?? 0),
            'media_permanencia'   => $summary['media_permanencia'] ?? null,
        ],
        'rows'    => $payload
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Erro interno',
        'detail'  => $e->getMessage()
    ]);
}
