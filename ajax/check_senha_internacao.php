<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$ROOT = dirname(__DIR__);
chdir($ROOT);

require_once 'globals.php';
require_once 'db.php';
require_once 'dao/internacaoDao.php';

$senha = isset($_GET['senha']) ? trim((string) $_GET['senha']) : '';
$ignore = filter_input(INPUT_GET, 'ignore', FILTER_VALIDATE_INT);

if ($senha === '') {
    echo json_encode(['success' => true, 'exists' => false]);
    exit;
}

try {
    $internacaoDao = new internacaoDAO($conn, $BASE_URL);
    $exists = $internacaoDao->senhaExists($senha, $ignore);
    echo json_encode(['success' => true, 'exists' => $exists]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao verificar senha',
        'detail' => $e->getMessage(),
    ]);
}
