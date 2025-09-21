<?php
include_once ("check_logado.php");
include_once ("globals.php");
include_once("models/mensagem.php");
include_once("dao/mensagemDao.php");

// Database connection
$mensagemDao = new mensagemDAO($conn, $BASE_URL);

// Getting form data
$de_usuario = $_POST['de_usuario'];
$para_usuario = $_POST['para_usuario'];
$mensagem_content = $_POST['mensagem'];

// Create a new Mensagem object
$mensagem = new Mensagem();
$mensagem->de_usuario = $de_usuario;
$mensagem->para_usuario = $para_usuario;
$mensagem->mensagem = $mensagem_content;
$mensagem->data_mensagem = date("Y-m-d H:i:s");
$mensagem->vista = 0; // Initially, the message is unread

// Save the message to the database
$mensagemDao->create($mensagem);

// No need to return anything; the AJAX will reload the messages
?>
