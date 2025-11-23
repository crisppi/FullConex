<?php
include_once("check_logado.php");

require_once("globals.php");
require_once("db.php");
require_once("models/censo.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/censoDao.php");

$userDao = new UserDAO($conn, $BASE_URL);
$censoDao = new censoDAO($conn, $BASE_URL);

$type = "delete";
//$type = filter_input(INPUT_POST, "type");

if ($type === "delete") {
    // Recebe os dados do form
    
    $id_censo = filter_input(INPUT_GET, "id_censo");
    print_r($id_censo);
    // exit();
    $censoDao->destroy($id_censo);
    include_once('censo/lista');
}