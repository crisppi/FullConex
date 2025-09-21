<?php

require_once("globals.php");
require_once("db.php");
require_once("models/acomodacao.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/acomodacaoDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$acomodacaoDao = new acomodacaoDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário

if ($type === "create") {

    // Receber os dados dos inputs
    $acomodacao_aco = filter_input(INPUT_POST, "acomodacao_aco");

    $valor_aco = filter_input(INPUT_POST, "valor_aco");
    $valor_aco = str_replace('R$', '', $valor_aco);
    $valor_aco = str_replace('.', '', $valor_aco);
    $valor_aco = str_replace(',', '.', $valor_aco);

    $fk_hospital = filter_input(INPUT_POST, "fk_hospital");
    $data_contrato_aco = filter_input(INPUT_POST, "data_contrato_aco");

    $fk_usuario_acomodacao = filter_input(INPUT_POST, "fk_usuario_acomodacao");
    $usuario_create_acomodacao = filter_input(INPUT_POST, "usuario_create_acomodacao");
    $data_create_acomodacao = filter_input(INPUT_POST, "data_create_acomodacao");

    $acomodacao = new acomodacao();

    // Validação mínima de dados
    if (!empty($acomodacao_aco)) {

        $acomodacao->acomodacao_aco = $acomodacao_aco;
        $acomodacao->valor_aco = $valor_aco;
        $acomodacao->fk_hospital = $fk_hospital;
        $acomodacao->data_contrato_aco = $data_contrato_aco;

        $acomodacao->fk_usuario_acomodacao = $fk_usuario_acomodacao;
        $acomodacao->usuario_create_acomodacao = $usuario_create_acomodacao;
        $acomodacao->data_create_acomodacao = $data_create_acomodacao;

        $acomodacaoDao->create($acomodacao);
        header('location:list_acomodacao.php');
    } else {

        $message->setMessage("Você precisa adicionar pelo menos: acomodacao_aco do acomodacao!", "error", "back");
    }
} else if ($type === "update") {


    $acomodacao = new Acomodacao();
    // Receber os dados dos inputs
    $id_acomodacao = filter_input(INPUT_POST, "id_acomodacao");
    $fk_hospital = filter_input(INPUT_POST, "fk_hospital");
    $acomodacao_aco = filter_input(INPUT_POST, "acomodacao_aco");
    $valor_aco = filter_input(INPUT_POST, "valor_aco");
    $valor_aco = str_replace('R$', '', $valor_aco);
    $valor_aco = str_replace('.', '', $valor_aco);
    $valor_aco = str_replace(',', '.', $valor_aco);
    $data_contrato_aco = filter_input(INPUT_POST, "data_contrato_aco");

    $fk_usuario_acomodacao = filter_input(INPUT_POST, "fk_usuario_acomodacao");
    $usuario_create_acomodacao = filter_input(INPUT_POST, "usuario_create_acomodacao");
    $data_create_acomodacao = filter_input(INPUT_POST, "data_create_acomodacao");

    $acomodacao = $acomodacaoDao->joinAcomodacaoHospitalshow($id_acomodacao);

    $acomodacao['id_acomodacao'] = $id_acomodacao;
    $acomodacao['fk_hospital'] = $fk_hospital;
    $acomodacao['valor_aco'] = $valor_aco;
    $acomodacao['acomodacao_aco'] = $acomodacao_aco;
    $acomodacao['fk_usuario_acomodacao'] = $fk_usuario_acomodacao;
    $acomodacao['usuario_create_acomodacao'] = $usuario_create_acomodacao;
    $acomodacao['data_create_acomodacao'] = $data_create_acomodacao;
    $acomodacao['data_contrato_aco'] = $data_contrato_aco;
    $acomodacaoDao->update($acomodacao);

    header('location:list_acomodacao.php');
}

$type = filter_input(INPUT_GET, "type");

if ($type === "delete") {
    // Recebe os dados do form
    $id_acomodacao = filter_input(INPUT_GET, "id_acomodacao");

    $acomodacaoDao = new acomodacaoDAO($conn, $BASE_URL);

    $acomodacao = $acomodacaoDao->joinAcomodacaoHospitalShow($id_acomodacao);
    if ($acomodacao) {

        $acomodacaoDao->destroy($id_acomodacao);

        header('location:list_acomodacao.php');
    } else {

        $message->setMessage("Informações inválidas!", "error", "index.php");
    }
    header('location:list_acomodacao.php');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_hospital'])) {
    $id_hospital = filter_var($_POST['id_hospital'], FILTER_VALIDATE_INT);

    if ($id_hospital) {
        // Debug para o console do backend
        error_log("Processando o ID do hospital: $id_hospital");

        // Defina a condição para o WHERE
        $where = 'ho.id_hospital = ' . $id_hospital;

        // Obtenha as acomodações
        $acomodacaoDao = new AcomodacaoDAO($conn, $BASE_URL);
        $acomodacoes = $acomodacaoDao->selectAllacomodacao($where);

        if ($acomodacoes) {
            echo json_encode(['status' => 'success', 'acomodacoes' => $acomodacoes]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Nenhuma acomodação encontrada.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID do hospital inválido.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum hospital foi selecionado.']);
}
exit;