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

if ($type === "create-negoc") {
    // Receber o JSON de negociações
    $negociacoesJSON = filter_input(INPUT_POST, "negociacoes_json", FILTER_DEFAULT);

    if ($negociacoesJSON) {
        $negociacoes = json_decode($negociacoesJSON, true); // Decodifica o JSON para um array associativo
        $fk_id_int = filter_input(INPUT_POST, "fk_id_int", FILTER_VALIDATE_INT);
        $fk_usuario_neg = filter_input(INPUT_POST, "fk_usuario_neg", FILTER_VALIDATE_INT);

        $errors = [];
        $successCount = 0;

        if (is_array($negociacoes) && !empty($negociacoes)) {
            foreach ($negociacoes as $negociacao) {
                $trocaDe = filter_var($negociacao['troca_de'], FILTER_VALIDATE_INT);
                $trocaPara = filter_var($negociacao['troca_para'], FILTER_VALIDATE_INT);
                $qtd = filter_var($negociacao['qtd'], FILTER_VALIDATE_INT);
                $saving = filter_var(str_replace('R$', '', $negociacao['saving']), FILTER_VALIDATE_FLOAT);

                if (!$trocaDe || !$trocaPara || !$qtd || $saving === false) {
                    $errors[] = "Dados incompletos ou inválidos para uma negociação.";
                    continue;
                }

                $novaNegociacao = new Negociacao();
                $novaNegociacao->setFkIdInt($fk_id_int);
                $novaNegociacao->setFkUsuarioNeg($fk_usuario_neg);
                $novaNegociacao->setTrocaDe($trocaDe);
                $novaNegociacao->setTrocaPara($trocaPara);
                $novaNegociacao->setQuantidade($qtd);
                $novaNegociacao->setSaving($saving);

                if ($negociacaoDao->create($novaNegociacao)) {
                    $successCount++;
                } else {
                    $errors[] = "Erro ao salvar negociação com troca_de: $trocaDe e troca_para: $trocaPara.";
                }
            }

            if (empty($errors)) {
                $message->setMessage("Todas as negociações foram salvas com sucesso.", "success", "back");
            } else {
                $message->setMessage("$successCount negociações salvas. Erros: " . implode(', ', $errors), "warning", "back");
            }
        } else {
            $message->setMessage("Nenhuma negociação válida foi enviada.", "error", "back");
        }
    } else {
        $message->setMessage("Nenhum dado de negociação foi enviado.", "error", "back");
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
