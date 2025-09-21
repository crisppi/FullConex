<?php

$host = "mdb-accert.mysql.uhserver.com";
$user = "diretoria2";
$pass = "Guga@0401";
$dbname = "mydb_accert; charset=utf8";
$port = 3306;
try {
    $conn = new PDO("mysql:dbname=$dbname;host=$host", $user, $pass);

    // Habilitar erros PDO
    // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,  "SET NAMES utf-8");
} catch (Exception $e) {

    header("location:sem_conexao.html");
    echo "Falha na conecção";
    include_once('semacesso.php');
}
