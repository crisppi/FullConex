<?php

require_once("globals.php");
require_once("db.php");
require_once("models/tuss.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/tussDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$tussDao = new tussDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário



exit();



if ($type === "create___") {

    // Receber os dados dos inputs
    $fk_int_tuss = filter_input(INPUT_POST, "fk_int_tuss");
    $tuss_solicitado = filter_input(INPUT_POST, "tuss_solicitado");
    $data_realizacao_tuss = filter_input(INPUT_POST, "data_realizacao_tuss");
    $qtd_tuss_solicitado = filter_input(INPUT_POST, "qtd_tuss_solicitado");
    $qtd_tuss_liberado = filter_input(INPUT_POST, "qtd_tuss_liberado");
    $tuss_liberado_sn = filter_input(INPUT_POST, "tuss_liberado_sn");

    $tuss = new tuss();

    // Validação mínima de dados
    if (!empty($fk_int_tuss)) {

        $tuss->fk_int_tuss = $fk_int_tuss;
        $tuss->tuss_solicitado = $tuss_solicitado;
        $tuss->data_realizacao_tuss = $data_realizacao_tuss;
        $tuss->qtd_tuss_solicitado = $qtd_tuss_solicitado;
        $tuss->qtd_tuss_liberado = $qtd_tuss_liberado;
        $tuss->tuss_liberado_sn = $tuss_liberado_sn;

        $tussDao->create($tuss);
    } else {

        $message->setMessage("Você precisa adicionar pelo menos: tuss!", "error", "back");
    }
}
if ($type === "create-vis___") {

    // Receber os dados dos inputs
    $fk_int_tuss = filter_input(INPUT_POST, "fk_int_tuss");
    $tuss_solicitado = filter_input(INPUT_POST, "tuss_solicitado");
    $data_realizacao_tuss = filter_input(INPUT_POST, "data_realizacao_tuss");
    $qtd_tuss_solicitado = filter_input(INPUT_POST, "qtd_tuss_solicitado") ?: null;
    $qtd_tuss_liberado = filter_input(INPUT_POST, "qtd_tuss_liberado") ?: null;
    $tuss_liberado_sn = filter_input(INPUT_POST, "tuss_liberado_sn");

    $tuss = new tuss();

    // Validação mínima de dados
    if (!empty($tuss_solicitado)) {

        $tuss->fk_int_tuss = $fk_int_tuss;
        $tuss->tuss_solicitado = $tuss_solicitado;
        $tuss->data_realizacao_tuss = $data_realizacao_tuss;
        $tuss->qtd_tuss_solicitado = $qtd_tuss_solicitado;
        $tuss->qtd_tuss_liberado = $qtd_tuss_liberado;
        $tuss->tuss_liberado_sn = $tuss_liberado_sn;


        $tussDao->create($tuss);
    } else {

        $message->setMessage("Você precisa adicionar pelo menos: tuss!", "error", "back");
    }
}