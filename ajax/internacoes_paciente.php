<?php
header('Content-Type: application/json; charset=utf-8');

// Muda o diretório de trabalho para a raiz do projeto (um nível acima de /ajax)
$ROOT = dirname(__DIR__);
chdir($ROOT);

// Agora pode requerer usando caminhos relativos à raiz
require_once 'globals.php';
require_once 'db.php';
require_once 'models/message.php';
require_once 'models/internacao.php'; // opcional, mas não atrapalha (require_once)
require_once 'dao/internacaoDao.php';

try {
    $pacId = filter_input(INPUT_GET, 'id_paciente', FILTER_VALIDATE_INT);
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int) ($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    if (!$pacId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id_paciente obrigatório']);
        exit;
    }

    $dao = new internacaoDAO($conn, $BASE_URL);

    // total
    $total = $dao->countByPaciente($pacId);

    // lista
    $rows = $dao->listByPaciente($pacId, $limit, $offset, 'data_intern_int', 'DESC');

    // formata datas
    $fmtDate = function ($d) {
        if (!$d || $d === '0000-00-00')
            return '';
        $dt = DateTime::createFromFormat('Y-m-d', $d);
        return $dt ? $dt->format('d/m/Y') : '';
    };

    $payload = array_map(function ($r) use ($fmtDate) {
        return [
            'id_internacao' => (int) ($r['id_internacao'] ?? 0),
            'admissao' => $fmtDate($r['data_intern_int'] ?? null),
            'alta' => $fmtDate($r['data_alta_alt'] ?? null),
            'hora_admissao' => $r['hora_intern_int'] ?? null,
            'hora_alta' => $r['hora_alta_alt'] ?? null,
            'unidade' => trim($r['nome_hosp'] ?? ''),
            'medico' => '', // TODO: incluir no SELECT se precisar
            'status' => (isset($r['internado_int']) && $r['internado_int'] === 's') ? 'Internado' : 'Alta',
            'prorrogacoes' => (int)($r['prorrogacoes'] ?? 0),
            'visitas' => (int)($r['visitas_total'] ?? 0)
        ];
    }, $rows ?: []);

    echo json_encode([
        'success' => true,
        'total' => (int) $total,
        'page' => $page,
        'limit' => $limit,
        'rows' => $payload
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno',
        'detail' => $e->getMessage() // remova em prod
    ]);
    exit;
}
