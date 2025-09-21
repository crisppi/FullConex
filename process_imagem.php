<?php

require_once("globals.php");
require_once("db.php");

require_once("models/seguradora.php");
require_once("dao/seguradoraDao.php");

require_once("models/imagem.php");
require_once("dao/imagemDao.php");

require_once("models/usuario.php");
require_once("dao/usuarioDao.php");

require_once("models/message.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$imagemDao = new imagemDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");
$fk_imagem = filter_input(INPUT_POST, "fk_imagem");

// Resgata dados do usuário
if ($type === "create") {

    // Receber os dados dos inputs
    $imagemSegImg = filter_input(INPUT_POST, "imagemSegImg");

    echo "<pre>";
    // print_r($_POST);
    print_r($_FILES);
    print_r($_FILES['imagem']);


    // echo "</pre>";

    // $type_SegImg = $_FILES['logo_seg']['type'];
    // $size_SegImg = $_FILES['logo_seg']['size'];
    $pasta_temp = $_FILES['imagem']['tmp_name'];
    $arquivo = $_FILES['imagem']['name'];

    // // $dataImg = base64_encode($_FILES[]);
    // echo "<pre>";
    // print_r($_FILES);
    // echo "</pre>";
    // print_r($size_SegImg);
    // print_r($type_SegImg);
    print_r($arquivo);
    // print_r($tmp_name);
    // print_r($conteudo);
    // print_r($dataImg);
    // exit;


    // // print_r($_FILES['logo_seg']['tmp_name']);
    $conteudo = file_get_contents($_FILES["imagem"]["tmp_name"]);
    $dataImg = base64_encode($conteudo);
    // echo $dataImg;

    // $conteudo = $dataImg;
    // // echo $conteudo;
    // // salvar a imagem na pasta upload
    $pasta = "uploads";
    move_uploaded_file($pasta_temp, $pasta . "/" . $arquivo);

    // $fk_seguradora_img = null;

    // if ($pasta_temp != "none") {
    //     $fp = fopen($pasta_temp, "r");
    //     $conteudo = fread($fp, $size_SegImg);
    //     $conteudo = addslashes($conteudo);
    //     fclose($fp);
    // }

    // exit;

    $imagem = new imagem();

    // Validação mínima de dados
    if (3 < 4) {

        $imagem->fk_imagem = $fk_imagem;
        $imagem->imagem_img = $dataImg;
        $imagem->imagem_name_img = $arquivo;


        $imagemDao->create($imagem);
    }
}
