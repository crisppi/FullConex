<?php

require_once("globals.php");
require_once("db.php");

// require_once("models/acomodacao.php");
require_once("dao/acomodacaoDao.php");

// require_once("models/message.php");

// $message = new Message($BASE_URL);
// $userDao = new UserDAO($conn, $BASE_URL);
$acomodacaoDao = new acomodacaoDAO($conn, $BASE_URL);

$de = filter_input(INPUT_POST, "de");
$para = filter_input(INPUT_POST, "para");
$qtd = filter_input(INPUT_POST, "qtd");


$saving = $acomodacaoDao->calcularSaving($de, $para, $qtd);

echo $saving[0];