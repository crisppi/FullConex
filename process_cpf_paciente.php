<?php 

require_once("globals.php");
require_once("db.php");

// require_once("models/acomodacao.php");
require_once("dao/pacienteDao.php");

// require_once("models/message.php");

// $message = new Message($BASE_URL);
// $userDao = new UserDAO($conn, $BASE_URL);
$pacienteDao = new pacienteDao($conn, $BASE_URL);

$cpf = filter_input(INPUT_POST, "cpf");

$result= $pacienteDao->validarCpfExistente($cpf);

if(empty($result)){
    echo 0;
}else{
    echo 1;
}
