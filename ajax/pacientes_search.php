<?php
// ajax/pacientes_search.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Muda o diretório de trabalho para a raiz do projeto (um nível acima de /ajax)
$ROOT = dirname(__DIR__);
chdir($ROOT);

// Agora pode requerer usando caminhos relativos à raiz
require_once 'globals.php';
require_once 'db.php';
require_once 'models/message.php';
require_once 'models/paciente.php'; // opcional, mas não atrapalha (require_once)
require_once 'dao/pacienteDao.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';


if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $dao = new PacienteDAO($conn, $BASE_URL);

    $rows = $dao->searchForHeader($q, 10);

    // Formatação leve para o front
    $out = array_map(function ($r) {
        $nasc_fmt = null;
        if (!empty($r['data_nasc_pac'])) {
            $dt = new DateTime($r['data_nasc_pac']);
            $nasc_fmt = $dt->format('d/m/Y');
        }
        return [
            'id_paciente' => (int) $r['id_paciente'],
            'nome' => $r['nome_pac'] ?? '',
            'matricula' => $r['matricula_pac'] ?? '',
            'nascimento_fmt' => $nasc_fmt,
            'senha' => $r['ultima_senha'] ?? ''
        ];
    }, $rows);

    echo json_encode($out);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
