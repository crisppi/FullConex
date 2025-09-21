<?php
// Review DAO
require_once("globals.php");
require_once("db.php");
require_once("dao/acomodacaoDao.php");
require_once("./models/acomodacao.php");

$hospitalId = $_POST['hospital'];
$acomodacaoDao = new acomodacaoDao($conn, $BASE_URL);

$result = $acomodacaoDao->findGeralByHospital($hospitalId);

// Gera opções para o select de acomodações
$options = '';
$options .= '<option value="0">Selecione</option>';
foreach ($result as $acom) {
    $options .= '<option value="' . $acom['id_acomodacao'] . '">' . $acom['acomodacao_aco'] . '</option>';
}

echo $options;
