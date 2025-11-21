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
            SUM(CASE WHEN ca.encerrado_cap = 1    THEN 1 ELSE 0 END) AS encerrados
        FROM tb_capeante ca
        JOIN tb_internacao ac ON ca.fk_int_capeante = ac.id_internacao
        WHERE ac.fk_paciente_int = :pac
    ");
    $stmtSum->bindValue(':pac', $pacId, PDO::PARAM_INT);
    $stmtSum->execute();
    $summary = $stmtSum->fetch(PDO::FETCH_ASSOC) ?: [];

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

    $payload = array_map(function ($r) use ($fmtDate) {
        // flags/valores vêm como strings ou ints do banco
        $status = '—';
        if (isset($r['encerrado_cap']) && (int)$r['encerrado_cap'] === 1)       $status = 'Encerrado';
        elseif (isset($r['em_auditoria_cap']) && (int)$r['em_auditoria_cap']===1) $status = 'Em Auditoria';
        elseif (isset($r['aberto_cap']) && (int)$r['aberto_cap'] === 1)         $status = 'Aberto';

        return [
            'id_internacao'   => (int)($r['id_internacao'] ?? 0),
            'id_capeante'     => (int)($r['id_capeante'] ?? 0),
            'hospital'        => $r['nome_hosp'] ?? '',
            'periodo'         => trim(($fmtDate($r['data_inicial_capeante'] ?? null) ?: '—') . ' a ' . ($fmtDate($r['data_final_capeante'] ?? null) ?: '—')),
            'valor_apresentado' => (float)($r['valor_apresentado_capeante'] ?? 0),
            'valor_final'       => (float)($r['valor_final_capeante'] ?? 0),
            'glosa_total'       => (float)($r['valor_glosa_total'] ?? 0),
            'parcial'         => (isset($r['parcial_capeante']) && (int)$r['parcial_capeante'] === 1) ? ('Parcial #' . (int)($r['parcial_num'] ?? 0)) : '—',
            'status'          => $status,
            'id_valor'        => isset($r['id_valor']) ? (int)$r['id_valor'] : null,
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
