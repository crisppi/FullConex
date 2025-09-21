<?php

require_once("globals.php");
require_once("db.php");
require_once("models/patologia.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/patologiaDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$patologiaDao = new patologiaDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário

if ($type === "create") {

    // Receber os dados dos inputs
    $patologia_pat = filter_input(INPUT_POST, "patologia_pat", FILTER_SANITIZE_SPECIAL_CHARS);
    $patologia_pat = ucwords(strtoupper($patologia_pat));

    $dias_pato = filter_input(INPUT_POST, "dias_pato", FILTER_SANITIZE_SPECIAL_CHARS);

    $fk_usuario_pat = filter_input(INPUT_POST, "fk_usuario_pat");
    $usuario_create_pat = filter_input(INPUT_POST, "usuario_create_pat");
    $data_create_pat = filter_input(INPUT_POST, "data_create_pat");

    $cid = filter_input(INPUT_POST, "cid_pat") ?? null;

    $patologia = new patologia();

    // Validação mínima de dados
    if (!empty($patologia_pat)) {

        $patologia->patologia_pat = $patologia_pat;
        $patologia->dias_pato = $dias_pato;
        $patologia->fk_cid_10_pat = $cid;

        $patologia->fk_usuario_pat = $fk_usuario_pat;
        $patologia->usuario_create_pat = $usuario_create_pat;
        $patologia->data_create_pat = $data_create_pat;

        $patologiaDao->create($patologia);
        header('location:list_patologia.php');
    } else {

        $message->setMessage("Você precisa adicionar pelo menos: patologiaNome do patologia!", "error", "back");
    }
} else if ($type === "update") {

    $patologiaDao = new patologiaDAO($conn, $BASE_URL);

    // Receber os dados dos inputs
    $id_patologia = filter_input(INPUT_POST, "id_patologia");
    $patologia_pat = filter_input(INPUT_POST, "patologia_pat", FILTER_SANITIZE_SPECIAL_CHARS);
    $patologia_pat = strtoupper($patologia_pat);

    $dias_pato = filter_input(INPUT_POST, "dias_pato", FILTER_SANITIZE_SPECIAL_CHARS);

    $cid = filter_input(INPUT_POST, "cid_pat") ?? null;

    $fk_usuario_pat = filter_input(INPUT_POST, "fk_usuario_pat");
    $usuario_create_pat = filter_input(INPUT_POST, "usuario_create_pat");
    $data_create_pat = filter_input(INPUT_POST, "data_create_pat");

    $patologiaData = $patologiaDao->findById($id_patologia);

    $patologiaData->id_patologia = $id_patologia;
    $patologiaData->patologia_pat = $patologia_pat;
    $patologiaData->dias_pato = $dias_pato;
    $patologiaData->fk_cid_10_pat = $cid;

    $patologiaData->fk_usuario_pat = $fk_usuario_pat;
    $patologiaData->usuario_create_pat = $usuario_create_pat;
    $patologiaData->data_create_pat = $data_create_pat;

    $patologiaDao->update($patologiaData);

    include_once('list_patologia.php');
}

if ($type === "delete") {

    // Recebe os dados do form
    $id_patologia = filter_input(INPUT_POST, "id_patologia");

    $patologiaDao = new patologiaDAO($conn, $BASE_URL);

    $patologia = $patologiaDao->findById($id_patologia);

    if (3 < 4) {

        $patologiaDao->destroy($id_patologia);

        header('location:list_patologia.php');
    } else {

        $message->setMessage("Informações inválidas!", "error", "index.php");
    }
}