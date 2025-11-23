<?php

include_once("globals.php");
include_once("db.php");
require_once("models/usuario.php");
require_once("dao/usuarioDao.php");

$where = null;
$order = null;
$obLimite = null;

//Instanciando a classe
$usuario = new UserDAO($conn, $BASE_URL);
$QtdTotalUser = new UserDAO($conn, $BASE_URL);
$query = $usuario->selectAllUsuario($where, $order, $obLimite);

// METODO DE BUSCA DE LOGIN
$email_login = filter_input(INPUT_POST, 'email_login');
$senha_login = filter_input(INPUT_POST, 'senha_login');
$login = filter_input(INPUT_POST, 'login');
$condicoes = [
    strlen($email_login) ? 'email_user LIKE "%' . $email_login . '%"' : null

];

$condicoes = array_filter($condicoes);
// REMOVE POSICOES VAZIAS DO FILTRO
$where = implode(' AND ', $condicoes);
// QUANTIDADE USUARIOS

$query = $usuario->selectAllUsuario($where, $order, $obLimite);

$senha_user = $query['0']['senha_user'];

$senha_log = $senha_login;

if ($query[0]['ativo_user'] == "s") {

    $nivel = $query[0]['nivel_user'];
    $usuario_user = $query[0]['usuario_user'];
    $login_user = $query[0]['email_user'];
    $email_user = $query[0]['email_user'];
    $ativo = $query[0]['ativo_user'];
    $cargo = $query[0]['cargo_user'];
    $id_user = $query[0]['id_usuario'];
    $senha_user = $query[0]['senha_user'];
    $foto_usuario = $query[0]['foto_usuario'];

    $_SESSION['id_usuario'] = $id_user;
    $_SESSION['foto_usuario'] = $foto_usuario;;
    $_SESSION['email_user'] = $email_user;
    $_SESSION['senha_user'] = "";
    $_SESSION['login_user'] = $login_user;
    $_SESSION['usuario_user'] = $usuario_user;
    $_SESSION['ativo'] = $ativo;
    $_SESSION['id_usuario'] = $id_user;
    $_SESSION['nivel'] = $nivel;
    $_SESSION['cargo'] = $cargo;
    $_SESSION['mensagem'] = "";
    $_SESSION['msg'] = "";

    if ($_SESSION['nivel'] == -1) {
        (header('location: list_internacao_cap_fin.php'));
    } else header('Location: ' . $BASE_URL . 'dashboard');


    if ($query[0]['senha_default_user'] == "s") {
        header("location:nova_senha.php");
    } else {
        // navegar para dados de conferencia de senha
        if (password_verify($senha_log, $senha_user)) {

            $nivel_user = $query[0]['nivel_user'];
            $usuario_user = $query[0]['usuario_user'];
            $login_user = $query[0]['email_user'];
            $email_user = $query[0]['email_user'];
            $ativo = $query[0]['ativo_user'];
            $cargo = $query[0]['cargo_user'];
            $id_user = $query[0]['id_usuario'];
            $senha_user = $query[0]['senha_user'];

            $_SESSION['id_usuario'] = $id_user;
            $_SESSION['email_user'] = $email_user;
            $_SESSION['senha_user'] = $senha_user;
            $_SESSION['login_user'] = $login_user;
            $_SESSION['ativo'] = $ativo;
            $_SESSION['id_usuario'] = $id_user;
            $_SESSION['nivel'] = $nivel;
            $_SESSION['cargo'] = $cargo;
            $_SESSION['mensagem'] = "";
            $_SESSION['msg'] = "";

            if ($_SESSION['nivel'] == -1) {
                (header('location: list_internacao_cap_fin.php'));
            } else header('Location: ' . $BASE_URL . 'dashboard');
        } else {
            $erro_login = "Usuário ou senha inválidos";
            $_SESSION['mensagem'] = $erro_login;
            header('Location: ' . $BASE_URL . 'inicio');
        }
    };
} else {

    $erro_login = "Usuário Inativo!!";
    $_SESSION['mensagem'] = $erro_login;
    header('Location: ' . $BASE_URL . 'inicio');


    //verifica o cargo do usuario logado para ver se é enfermeiro ou auditor, caso contrario o cargo ficara nulo 
    // teste para filtro de hospitais
    $medico = "Med_auditor";
    $enfermagem = "Enf_Auditor";
    $medico2 = "Med_Auditor";
    $enfermagem2 = "Enf_auditor";

    $cargo = $_SESSION['cargo'];
    if (($cargo == $medico) || ($cargo == $enfermagem)  || ($cargo == $medico2) || ($cargo == $enfermagem2)) {
        $cargo;
    } else {
        $cargo = null;
    };
};