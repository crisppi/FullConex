<?php

include_once("globals.php");
require_once(__DIR__ . "/utils/flow_logger.php");

$redirectLogin = $BASE_URL . 'index.php';

$failLogin = static function (string $mensagem) use ($redirectLogin): void {
    $_SESSION['login_error'] = $mensagem;

    // Limpa dados de sessão de autenticação para não permitir entrada parcial.
    unset(
        $_SESSION['id_usuario'],
        $_SESSION['foto_usuario'],
        $_SESSION['email_user'],
        $_SESSION['senha_user'],
        $_SESSION['login_user'],
        $_SESSION['usuario_user'],
        $_SESSION['ativo'],
        $_SESSION['nivel'],
        $_SESSION['cargo'],
        $_SESSION['fk_seguradora_user']
    );

    header('Location: ' . $redirectLogin);
    exit;
};

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . $redirectLogin);
    exit;
}

$email_login = trim((string)filter_input(INPUT_POST, 'email_login', FILTER_SANITIZE_EMAIL));
$senha_login = (string)filter_input(INPUT_POST, 'senha_login');

if ($email_login === '' || $senha_login === '') {
    unset($_SESSION['login_error']);
    header('Location: ' . $redirectLogin);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT
            id_usuario,
            usuario_user,
            email_user,
            senha_user,
            senha_default_user,
            ativo_user,
            nivel_user,
            cargo_user,
            foto_usuario,
            fk_seguradora_user
        FROM tb_user
        WHERE email_user = :email
        LIMIT 1
    ");
    $stmt->bindValue(':email', $email_login, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    error_log('[LOGIN] ' . $e->getMessage());
    $failLogin('Não foi possível realizar o login agora. Tente novamente.');
}

if (!is_array($user)) {
    $user = [];
}

if (count($user) === 0) {
    $failLogin('E-mail ou senha inválidos. Verifique os dados e tente novamente.');
}

if (($user['ativo_user'] ?? 'n') !== 's') {
    $failLogin('Seu usuário está inativo. Entre em contato com o administrador.');
}

$senhaUser = (string)($user['senha_user'] ?? '');
$senhaValida = $senhaUser !== '' && (
    password_verify($senha_login, $senhaUser) ||
    hash_equals($senhaUser, $senha_login)
);

if (!$senhaValida) {
    $failLogin('E-mail ou senha inválidos. Verifique os dados e tente novamente.');
}

session_regenerate_id(true);

$_SESSION['id_usuario'] = (int)($user['id_usuario'] ?? 0);
$_SESSION['foto_usuario'] = (string)($user['foto_usuario'] ?? '');
$_SESSION['email_user'] = (string)($user['email_user'] ?? '');
$_SESSION['senha_user'] = '';
$_SESSION['login_user'] = (string)($user['email_user'] ?? '');
$_SESSION['usuario_user'] = (string)($user['usuario_user'] ?? '');
$_SESSION['ativo'] = (string)($user['ativo_user'] ?? '');
$_SESSION['nivel'] = (int)($user['nivel_user'] ?? 99);
$_SESSION['cargo'] = (string)($user['cargo_user'] ?? '');
$_SESSION['fk_seguradora_user'] = isset($user['fk_seguradora_user'])
    ? (int)$user['fk_seguradora_user']
    : null;
unset($_SESSION['login_error']);
$_SESSION['msg'] = '';

if (function_exists('flowLogStart') && function_exists('flowLog')) {
    $loginCtx = flowLogStart('auth_login', [
        'session_user_id' => (int)($_SESSION['id_usuario'] ?? 0),
        'session_user_name' => (string)($_SESSION['usuario_user'] ?? ''),
        'email_user' => (string)($_SESSION['email_user'] ?? ''),
        'nivel' => (int)($_SESSION['nivel'] ?? 0),
        'cargo' => (string)($_SESSION['cargo'] ?? ''),
    ]);
    flowLog($loginCtx, 'login.success', 'INFO', [
        'target' => ((int)($_SESSION['nivel'] ?? 0) === -1) ? 'list_internacao_cap_fin.php' : 'dashboard',
    ]);
}

if (($user['senha_default_user'] ?? 'n') === 's') {
    header('Location: ' . $BASE_URL . 'nova_senha.php');
    exit;
}

if ((int)$_SESSION['nivel'] === -1) {
    header('Location: ' . $BASE_URL . 'list_internacao_cap_fin.php');
    exit;
}

header('Location: ' . $BASE_URL . 'dashboard');
exit;
