<?php

if (!defined("FLOW_LOGGER_AUTO_V1")) {
    define("FLOW_LOGGER_AUTO_V1", 1);
    @require_once(__DIR__ . "/utils/flow_logger.php");
    if (function_exists("flowLogStart") && function_exists("flowLog")) {
        $__flowCtxAuto = flowLogStart(basename(__FILE__, ".php"), [
            "type" => $_POST["type"] ?? $_GET["type"] ?? null,
            "method" => $_SERVER["REQUEST_METHOD"] ?? null,
        ]);
        register_shutdown_function(function () use ($__flowCtxAuto) {
            $err = error_get_last();
            if ($err && in_array(($err["type"] ?? 0), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                flowLog($__flowCtxAuto, "shutdown.fatal", "ERROR", [
                    "message" => $err["message"] ?? null,
                    "file" => $err["file"] ?? null,
                    "line" => $err["line"] ?? null,
                ]);
            }
            flowLog($__flowCtxAuto, "request.finish", "INFO");
        });
    }
}

require_once("globals.php");
require_once("db.php");
require_once("dao/capeanteDao.php");

header('Content-Type: application/json');

// Verifica se o método é POST e se os IDs foram enviados
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ids'])) {
    $idsParaFaturar = $_POST['ids'];

    // Garante que os IDs sejam um array
    if (!is_array($idsParaFaturar) || empty($idsParaFaturar)) {
        echo json_encode(['success' => false, 'message' => 'Nenhuma conta selecionada.']);
        exit;
    }

    $capeanteDao = new capeanteDAO($conn, $BASE_URL);
    $erros = [];
    $sucessos = 0;

    foreach ($idsParaFaturar as $id_capeante) {
        // Validação simples para garantir que é um número inteiro positivo
        if (!filter_var($id_capeante, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
            continue; // Pula IDs inválidos
        }

        try {
            // Chama o método no DAO para atualizar o status
            $capeanteDao->marcarComoFaturado($id_capeante);
            $sucessos++;
        } catch (Exception $e) {
            $erros[] = "Erro ao faturar a conta ID $id_capeante.";
        }
    }

    if (empty($erros)) {
        echo json_encode(['success' => true, 'message' => $sucessos . ' conta(s) marcada(s) como faturada(s) com sucesso!']);
    } else {
        $mensagemErro = 'Operação concluída com ' . $sucessos . ' sucesso(s) e ' . count($erros) . ' erro(s).';
        echo json_encode(['success' => false, 'message' => $mensagemErro]);
    }
} else {
    // Responde com erro se a requisição não for POST ou se os dados não foram enviados
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
}
?>