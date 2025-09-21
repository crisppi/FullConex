<?php
include_once("check_logado.php");

require_once("globals.php");
require_once("db.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/hospitalUserDao.php");

$userDao = new UserDAO($conn, $BASE_URL);
$hospitalUserDao = new hospitalUserDAO($conn, $BASE_URL);

$type = "delete";
//$type = filter_input(INPUT_POST, "type");

if ($type === "delete") {
    // Recebe os dados do form
    
    $id_hospitalUser = filter_input(INPUT_GET, "id_hospitalUser");
    // exit();
    $hospitalUserDao->destroy($id_hospitalUser);
    include_once('list_HospitalUser.php');
}