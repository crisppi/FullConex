<?php

include_once("globals.php");
include_once("db.php");

include_once("models/message.php");
include_once("dao/usuarioDao.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/alta.php");
include_once("dao/altaDao.php");

include_once("models/uti.php");
include_once("dao/utiDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);

$internacaoDao = new internacaoDAO($conn, $BASE_URL);
$utiDao = new utiDao($conn, $BASE_URL);
$altaDao = new altaDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

$alta = new alta();

// Receber os dados dos inputs
$id_internacao = filter_input(INPUT_POST, "id_internacao");
$internado_int = filter_input(INPUT_POST, "internado_int");
$internado_alt = "n";
$data_alta_alt = filter_input(INPUT_POST, "data_alta_alt");
$hora_alta_alt = filter_input(INPUT_POST, "hora_alta_alt");
$tipo_alta_alt = filter_input(INPUT_POST, "tipo_alta_alt");
$data_create_alt = filter_input(INPUT_POST, "data_create_alt");
$usuario_alt = filter_input(INPUT_POST, "usuario_alt");
$fk_usuario_alt = filter_input(INPUT_POST, "fk_usuario_alt");

$alta->data_alta_alt = $data_alta_alt;
$alta->hora_alta_alt = $hora_alta_alt;
$alta->tipo_alta_alt = $tipo_alta_alt;
$alta->usuario_alt = $usuario_alt;
$alta->data_create_alt = $data_create_alt;
$alta->fk_id_int_alt = $id_internacao;
$alta->internado_alt = $internado_alt;
$alta->fk_usuario_alt = $fk_usuario_alt;

$id_uti = filter_input(INPUT_POST, "id_uti");
$alta_uti = filter_input(INPUT_POST, "alta_uti");
$data_alta_uti = filter_input(INPUT_POST, "data_alta_uti");

$altaDao->create($alta);

$internacaoData = new Internacao();

$internacaoData->id_internacao = $id_internacao;
$internacaoData->internado_int = $internado_int;

$internacaoDao->updateAlta($internacaoData);

if ($alta_uti == "alta_uti") {
    // Receber os dados dos inputs
    $internado_uti = filter_input(INPUT_POST, "internado_uti");
    $fk_internacao_uti = filter_input(INPUT_POST, "fk_internacao_uti");

    $UTIData = $utiDao->findById($id_uti);

    $UTIData->data_alta_uti = $data_alta_uti;
    $UTIData->fk_internacao_uti = $fk_internacao_uti;
    $UTIData->internado_uti = $internado_uti;
    $UTIData->id_uti = $id_uti;

    $utiDao->findAltaUpdate($UTIData);
}

header('location:internacoes/lista');
