<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['email_user'])) {
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
if (!defined('SKIP_HEADER') || !SKIP_HEADER) {
    require_once("templates/header.php");
}
