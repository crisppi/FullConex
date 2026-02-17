<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['email_user']) && empty($_SESSION['id_usuario'])) {
    header('location:index.php');
    exit;
}

$ativo = $_SESSION['ativo'] ?? null;
if ($ativo !== 's') {
    $erro_login = "Usuário inativo";
    $_SESSION['mensagem'] = $erro_login;
    header('location:index.php');
    exit;
} else {
};

require_once(__DIR__ . "/utils/flow_logger.php");
if (function_exists('flowLog')) {
    $accessCtx = [
        'flow' => 'page_access',
        'trace_id' => $_SERVER['UNIQUE_ID'] ?? substr(md5((string)microtime(true) . (string)($_SESSION['id_usuario'] ?? '0')), 0, 16),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'session_user_id' => $_SESSION['id_usuario'] ?? null,
        'session_user_name' => $_SESSION['usuario_user'] ?? ($_SESSION['login_user'] ?? ($_SESSION['email_user'] ?? null)),
        'ts' => date('c')
    ];
    flowLog($accessCtx, 'page.access', 'INFO', [
        'script' => basename((string)($_SERVER['SCRIPT_NAME'] ?? '')),
        'query_string' => $_SERVER['QUERY_STRING'] ?? null
    ]);
}

if (!defined('SKIP_HEADER') || !SKIP_HEADER) {
    require_once("templates/header.php");
}
