<?php

if (!isset($_SESSION)) {
    session_start();
} else {
}

if (!isset($_SESSION['email_user'])) {
    header('location:index.php');
}

if ($_SESSION['ativo'] != 's') {
    $erro_login = "Usuário inativo";
    $_SESSION['mensagem'] = $erro_login;
    header('location:index.php');
} else {
};
if (!defined('SKIP_HEADER') || !SKIP_HEADER) {
    require_once("templates/header.php");
}
