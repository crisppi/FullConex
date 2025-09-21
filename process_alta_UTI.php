<?php
require_once("globals.php");
require_once("db.php");
require_once("models/internacao.php");
// require_once("models/message.php");
// require_once("dao/usuarioDao.php");
require_once("dao/internacaoDao.php");

require_once("models/uti.php");
require_once("dao/utiDao.php");

// $userDao = new UserDAO($conn, $BASE_URL);
$internacaoDao = new internacaoDAO($conn, $BASE_URL);
$utiDao = new utiDAO($conn, $BASE_URL);

$id_internacao = filter_input(INPUT_POST, "id_internacao");

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário
if ($type === "update") {
    // exit;
    // Receber os dados dos inputs
    $internado_uti = filter_input(INPUT_POST, "internado_uti");
    $fk_internacao_uti = filter_input(INPUT_POST, "fk_internacao_uti");
    $data_alta_uti = filter_input(INPUT_POST, "data_alta_uti");
    $internado_uti = filter_input(INPUT_POST, "internado_uti");
    $id_uti = filter_input(INPUT_POST, "id_uti");
    $UTIData = $utiDao->findById($id_uti);

    $UTIData->data_alta_uti = $data_alta_uti;
    $UTIData->fk_internacao_uti = $fk_internacao_uti;
    $UTIData->internado_uti = $internado_uti;
    $UTIData->id_uti = $id_uti;

    $utiDao->findAltaUpdate($UTIData);

    include_once('list_internacao_uti.php');
}
