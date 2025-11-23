<?php
ob_start();
require_once("globals.php");
require_once("db.php");
require_once("models/antecedente.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/antecedenteDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$antecedenteDao = new antecedenteDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário

if ($type === "create-ant") {

    // Receber os dados dos inputs
    $antecedente_ant = filter_input(INPUT_POST, "antecedente_ant", FILTER_SANITIZE_SPECIAL_CHARS);
    $antecedente_ant = strtoupper($antecedente_ant);
    $cid = filter_input(INPUT_POST, "cid_ant") ?? null;

    $fk_usuario_ant = filter_input(INPUT_POST, "fk_usuario_ant");
    $usuario_create_ant = filter_input(INPUT_POST, "usuario_create_ant");
    $data_create_ant = filter_input(INPUT_POST, "data_create_ant");

    $antecedente = new antecedente();

    // Validação mínima de dados
    if (!empty($antecedente_ant)) {

        $antecedente->antecedente_ant = $antecedente_ant;

        $antecedente->fk_usuario_ant = $fk_usuario_ant;
        $antecedente->usuario_create_ant = $usuario_create_ant;
        $antecedente->data_create_ant = $data_create_ant;
        $antecedente->fk_cid_10_ant = $cid;

        $antecedenteDao->create($antecedente);
        header('location:list_antecedente.php');
    } else {
        $message->setMessage("Você precisa adicionar pelo menos: Antecedente!", "error", "internacoes/nova");
    }
} else if ($type === "update-ant") {

    $antecedenteDao = new antecedenteDAO($conn, $BASE_URL);

    // Receber os dados dos inputs
    $id_antecedente = filter_input(INPUT_POST, "id_antecedente");
    $antecedente_ant = filter_input(INPUT_POST, "antecedente_ant", FILTER_SANITIZE_SPECIAL_CHARS);
    $antecedente_ant = ucwords(strtoupper($antecedente_ant));
    $cid = filter_input(INPUT_POST, "cid_ant") ?? null;

    $usuario_create_ant = filter_input(INPUT_POST, "usuario_create_ant");
    $data_create_ant = filter_input(INPUT_POST, "data_create_ant");
    $fk_usuario_ant = filter_input(INPUT_POST, "fk_usuario_ant");

    $antecedenteData = $antecedenteDao->findById($id_antecedente);

    $antecedenteData->id_antecedente = $id_antecedente;
    $antecedenteData->antecedente_ant = $antecedente_ant;
    $antecedenteData->fk_cid_10_ant = $cid;

    $antecedenteData->usuario_create_ant = $usuario_create_ant;
    $antecedenteData->data_create_ant = $data_create_ant;
    $antecedenteData->fk_usuario_ant = $fk_usuario_ant;

    $antecedenteDao->update($antecedenteData);

    header('location:list_antecedente.php');
};
if ($type === "delete") {
    // Recebe os dados do form
    $id_antecedente = filter_input(INPUT_POST, "id_antecedente");

    $antecedenteDao = new antecedenteDAO($conn, $BASE_URL);

    $antecedente = $antecedenteDao->findById($id_antecedente);
    if ($antecedente) {

        $antecedenteDao->destroy($id_antecedente);

        header('location:list_antecedente.php');
    } else {

        $message->setMessage("Informações inválidas!", "error", "index.php");
    }
}