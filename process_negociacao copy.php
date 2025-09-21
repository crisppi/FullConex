<?php

require_once("globals.php");
require_once("db.php");
require_once("models/negociacao.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/negociacaoDao.php");
include_once("models/internacao.php");
require_once("dao/internacaoDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$negociacaoDao = new negociacaoDAO($conn, $BASE_URL);

$internacaoDAO = new internacaoDAO($conn, $BASE_URL);
$internacaoID = $internacaoDAO->findLastId();
$internacaoID = $internacaoID['0'];

$a = $internacaoID['0'];

$niveis = $internacaoDAO->findLast($a);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

if ($type === "create") {

    // Receber os dados dos inputs negociacao
    $fk_id_int = filter_input(INPUT_POST, "fk_id_int");

    $troca_de_1 = filter_input(INPUT_POST, "troca_de_1");
    $troca_para_1 = filter_input(INPUT_POST, "troca_para_1");
    $qtd_1 = filter_input(INPUT_POST, "qtd_1");
    $saving_1_antes = filter_input(INPUT_POST, "saving_1") ?: NULL;
    $saving_1 = str_replace('R$', '', $saving_1_antes);


    $troca_de_2 = filter_input(INPUT_POST, "troca_de_2");
    $troca_para_2 = filter_input(INPUT_POST, "troca_para_2");
    $qtd_2 = filter_input(INPUT_POST, "qtd_2");
    $saving_2 = filter_input(INPUT_POST, "saving_2") ?: NULL;
    $saving_2 = str_replace('R$', '', $saving_2);

    $troca_de_3 = filter_input(INPUT_POST, "troca_de_3");
    $troca_para_3 = filter_input(INPUT_POST, "troca_para_3");
    $qtd_3 = filter_input(INPUT_POST, "qtd_3");
    $saving_3 = filter_input(INPUT_POST, "saving_3") ?: NULL;
    $saving_3 = str_replace('R$', '', $saving_3);

    $fk_usuario_neg = filter_input(INPUT_POST, "fk_usuario_neg");

    $negociacao = new negociacao();

    // Validação mínima de dados
    if (3 < 4) {

        $negociacao->troca_de_1 = $troca_de_1;
        $negociacao->troca_para_1 = $troca_para_1;
        $negociacao->fk_usuario_neg = $fk_id_int;
        $negociacao->qtd_1 = $qtd_1;
        $negociacao->saving_1 = $saving_1;

        $negociacao->troca_de_2 = $troca_de_2;
        $negociacao->troca_para_2 = $troca_para_2;
        $negociacao->qtd_2 = $qtd_2;
        $negociacao->saving_2 = $saving_2;

        $negociacao->troca_de_3 = $troca_de_3;
        $negociacao->troca_para_3 = $troca_para_3;
        $negociacao->qtd_3 = $qtd_3;
        $negociacao->saving_3 = $saving_3;

        $negociacao->fk_usuario_neg = $fk_usuario_neg;

        $negociacaoDao->create($negociacao);

        header('location: cad_internacao_niveis.php');
    } else {

        $message->setMessage("Você precisa adicionar pelo menos: negociacao_aco do negociacao!", "error", "back");
    }
} else if ($type === "update") {

    $negociacao = new negociacao();

    // Receber os dados dos inputs
    $id_negociacao = filter_input(INPUT_POST, "id_negociacao");
    $fk_hospital = filter_input(INPUT_POST, "fk_hospital");
    $negociacao_aco = filter_input(INPUT_POST, "negociacao_aco");
    $valor_aco = filter_input(INPUT_POST, "valor_aco");

    $negociacao = $negociacaoDao->joinnegociacaoHospitalshow($id_negociacao);

    $negociacao['id_negociacao'] = $id_negociacao;
    $negociacao['fk_hospital'] = $fk_hospital;
    $negociacao['valor_aco'] = $valor_aco;
    $negociacao['negociacao_aco'] = $negociacao_aco;

    $negociacaoDao->update($negociacao);

    header('location: cad_internacao_niveis.php');
}