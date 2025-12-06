<?php

if (!isset($_SESSION)) {
    session_start();
}

include_once("globals.php");
include_once("db.php");

include_once("models/message.php");
include_once("models/internacao.php");
include_once("models/alta.php");
include_once("models/uti.php");

include_once("dao/internacaoDao.php");
include_once("dao/altaDao.php");
include_once("dao/utiDao.php");

$message = new Message($BASE_URL);
$redirectPage = "list_internacao_gerar_alta.php";

if (!isset($_SESSION["email_user"])) {
    $message->setMessage("Sessão expirada, faça login novamente.", "error", "index.php");
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $message->setMessage("Método inválido.", "error", $redirectPage);
    header("Location: {$redirectPage}");
    exit;
}

$type = filter_input(INPUT_POST, "type");
if ($type !== "gerar_altas") {
    $message->setMessage("Requisição inválida.", "error", $redirectPage);
    header("Location: {$redirectPage}");
    exit;
}

$selecionadas = filter_input(INPUT_POST, "gerar", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
if (empty($selecionadas)) {
    $message->setMessage("Selecione ao menos uma internação para gerar a alta.", "error", $redirectPage);
    header("Location: {$redirectPage}");
    exit;
}

$altaDao = new altaDAO($conn, $BASE_URL);
$internacaoDao = new internacaoDAO($conn, $BASE_URL);
$utiDao = new utiDAO($conn, $BASE_URL);

$usuarioAlt = $_SESSION["email_user"] ?? "sistema";
$fkUsuarioAlt = $_SESSION["id_usuario"] ?? null;
$dataCreate = date("Y-m-d");

$erros = [];
$sucesso = 0;

foreach ($selecionadas as $rawId) {
    $idInternacao = (int) $rawId;
    if ($idInternacao <= 0) {
        continue;
    }

    $prefix = "alta_{$idInternacao}_";
    $dataAlta = trim((string) ($_POST[$prefix . "data"] ?? ""));
    $horaAlta = trim((string) ($_POST[$prefix . "hora"] ?? ""));
    $motivoAlta = trim((string) ($_POST[$prefix . "motivo"] ?? ""));

    if ($dataAlta === "" || $motivoAlta === "") {
        $erros[] = "ID {$idInternacao}: informe data e motivo da alta.";
        continue;
    }

    $utiFlag = $_POST[$prefix . "uti_flag"] ?? 'n';
    $utiId = (int)($_POST[$prefix . "uti_id"] ?? 0);
    $utiData = trim((string)($_POST[$prefix . "uti_data"] ?? ""));
    $utiFk = (int)($_POST[$prefix . "uti_fk"] ?? $idInternacao);

    if ($utiFlag === 's') {
        if ($utiId <= 0) {
            $erros[] = "ID {$idInternacao}: registro de UTI não localizado.";
            continue;
        }
        if ($utiData === "") {
            $erros[] = "ID {$idInternacao}: informe a data de alta da UTI.";
            continue;
        }
    }

    $alta = new alta();
    $alta->fk_id_int_alt = $idInternacao;
    $alta->tipo_alta_alt = $motivoAlta;
    $alta->data_alta_alt = $dataAlta;
    $alta->hora_alta_alt = $horaAlta !== "" ? $horaAlta : null;
    $alta->internado_alt = "n";
    $alta->usuario_alt = $usuarioAlt;
    $alta->data_create_alt = $dataCreate;
    $alta->fk_usuario_alt = $fkUsuarioAlt;

    try {
        $altaDao->create($alta);

        $internacao = new Internacao();
        $internacao->id_internacao = $idInternacao;
        $internacao->internado_int = "n";
        $internacaoDao->updateAlta($internacao);

        if ($utiFlag === 's') {
            $UTIData = $utiDao->findById($utiId);
            if (!$UTIData) {
                $erros[] = "ID {$idInternacao}: UTI não encontrada para atualização.";
            } else {
                $UTIData->data_alta_uti = $utiData;
                $UTIData->fk_internacao_uti = $utiFk ?: $idInternacao;
                $UTIData->internado_uti = "n";
                $UTIData->id_uti = $utiId;
                $utiDao->findAltaUpdate($UTIData);
            }
        }

        $sucesso++;
    } catch (Throwable $th) {
        $erros[] = "ID {$idInternacao}: " . $th->getMessage();
    }
}

if ($sucesso > 0) {
    $texto = "{$sucesso} alta(s) gerada(s) com sucesso.";
    if ($erros) {
        $texto .= " Alguns registros não foram processados: " . implode(" | ", $erros);
        $message->setMessage($texto, "warning", $redirectPage);
    } else {
        $message->setMessage($texto, "success", $redirectPage);
    }
} else {
    $textoErro = $erros ? implode(" | ", $erros) : "Não foi possível gerar as altas selecionadas.";
    $message->setMessage($textoErro, "error", $redirectPage);
}

header("Location: {$redirectPage}");
exit;
