<?php
require_once("globals.php");
require_once("db.php");

require_once("models/internacao.php");
require_once("dao/internacaoDao.php");

require_once("models/message.php");
$internacaoDao = new InternacaoDAO($conn, $BASE_URL);

$id_internacao = filter_input(INPUT_POST, "id_internacao");
$id_paciente = filter_input(INPUT_POST, "id_paciente");

if ($internacaoDao->checkInternAtiva($id_paciente) > 0) {
    echo "1";
}else{
    echo "0";
}

?>        