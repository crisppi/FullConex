<?php

require_once("globals.php");
require_once("db.php");
require_once("models/estipulante.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/estipulanteDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$estipulanteDao = new estipulanteDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");
$typeDel = filter_input(INPUT_POST, "typeDel");


if ($type === "create") {

    $tipo = ($_FILES['logo_est']['type']);
    $tamanho_perm = 1024 * 1024 * 2;
    $size = $_FILES['logo_est']['size'];

    $erros = "";

    if (($_FILES['logo_est']['size']) > $tamanho_perm) {
        // codigo de erro caso arquivo maior que permitido
    } else {
        // condicao caso arquivo permitido

        $tipo = ($_FILES['logo_est']['type']);
        $arquivo = ($_FILES['logo_est']['name']);
        $temp_arq = ($_FILES['logo_est']['tmp_name']);
        $size = ($_FILES['logo_est']['size']);
        $pasta = "uploads";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);
        // Receber os dados dos inputs
        $nome_est = filter_input(INPUT_POST, "nome_est", FILTER_SANITIZE_SPECIAL_CHARS);
        $nome_est = strtoupper($nome_est);
        $endereco_est = filter_input(INPUT_POST, "endereco_est", FILTER_SANITIZE_SPECIAL_CHARS);

        $email01_est = filter_input(INPUT_POST, "email01_est", FILTER_SANITIZE_EMAIL);
        $email01_est = strtolower($email01_est);

        $email02_est = filter_input(INPUT_POST, "email02_est", FILTER_SANITIZE_EMAIL);
        $email02_est = strtolower($email02_est);

        $cidade_est = filter_input(INPUT_POST, "cidade_est", FILTER_SANITIZE_SPECIAL_CHARS);
        $bairro_est = filter_input(INPUT_POST, "bairro_est", FILTER_SANITIZE_SPECIAL_CHARS);
        $estado_est = filter_input(INPUT_POST, "estado_est", FILTER_SANITIZE_SPECIAL_CHARS);

        $cnpj_est = filter_input(INPUT_POST, "cnpj_est", FILTER_SANITIZE_SPECIAL_CHARS);
        $cnpj_est = str_replace('/', '', $cnpj_est);
        $cnpj_est = str_replace('-', '', $cnpj_est);
        $cnpj_est = str_replace('.', '', $cnpj_est);

        $telefone01_est = filter_input(INPUT_POST, "telefone01_est", FILTER_SANITIZE_SPECIAL_CHARS);
        $telefone01_est = str_replace('-', '', $telefone01_est);
        $telefone01_est = str_replace('(', '', $telefone01_est);
        $telefone01_est = str_replace(') ', '', $telefone01_est);

        $telefone02_est = filter_input(INPUT_POST, "telefone02_est", FILTER_SANITIZE_SPECIAL_CHARS);
        $telefone02_est = str_replace('-', '', $telefone02_est);
        $telefone02_est = str_replace('(', '', $telefone02_est);
        $telefone02_est = str_replace(') ', '', $telefone02_est);

        $data_create_est = filter_input(INPUT_POST, "data_create_est");
        $usuario_create_est = filter_input(INPUT_POST, "usuario_create_est");
        $fk_usuario_est = filter_input(INPUT_POST, "fk_usuario_est");
        $deletado_est = filter_input(INPUT_POST, "deletado_est");

        $nome_contato_est = filter_input(INPUT_POST, "nome_contato_est");
        $nome_responsavel_est = filter_input(INPUT_POST, "nome_responsavel_est");
        $email_contato_est = filter_input(INPUT_POST, "email_contato_est");
        $email_responsavel_est = filter_input(INPUT_POST, "email_responsavel_est");
        $telefone_contato_est = filter_input(INPUT_POST, "telefone_contato_est");
        $telefone_responsavel_est = filter_input(INPUT_POST, "telefone_responsavel_est");
        $cep_est = filter_input(INPUT_POST, "cep_est");

        $numero_est = filter_input(INPUT_POST, "numero_est");
        $logo_est = $arquivo;

        $estipulante = new estipulante();

        // Validação mínima de dados
        if (!empty($nome_est)) {

            $estipulante->nome_est = $nome_est;
            $estipulante->endereco_est = $endereco_est;
            $estipulante->bairro_est = $bairro_est;

            $estipulante->email02_est = $email02_est;
            $estipulante->email01_est = $email01_est;

            $estipulante->cidade_est = $cidade_est;
            $estipulante->estado_est = $estado_est;
            $estipulante->cnpj_est = $cnpj_est;
            $estipulante->telefone01_est = $telefone01_est;
            $estipulante->telefone02_est = $telefone02_est;

            $estipulante->numero_est = $numero_est;
            $estipulante->fk_usuario_est = $fk_usuario_est;
            $estipulante->logo_est = $logo_est;
            $estipulante->cep_est = $cep_est;

            $estipulante->data_create_est = $data_create_est;
            $estipulante->usuario_create_est = $usuario_create_est;
            $estipulante->fk_usuario_est = $fk_usuario_est;
            $estipulante->deletado_est = $deletado_est;

            $estipulante->nome_contato_est = $nome_contato_est;
            $estipulante->nome_responsavel_est = $nome_responsavel_est;
            $estipulante->email_contato_est = $email_contato_est;
            $estipulante->email_responsavel_est = $email_responsavel_est;
            $estipulante->telefone_contato_est = $telefone_contato_est;
            $estipulante->telefone_responsavel_est = $telefone_responsavel_est;


            $estipulanteDao->create($estipulante);
        } else {

            $message->setMessage("Você precisa adicionar pelo menos: nome_est do estipulante!", "error", "back");
        }
        header('Location: ' . $BASE_URL . 'estipulantes');
    }
}
if ($type === "update") {

    $tipo = ($_FILES['logo_est']['type']);
    $tamanho_perm = 1024 * 1024 * 2;
    $size = $_FILES['logo_est']['size'];

    $erros = "";

    if (($_FILES['logo_est']['size']) > $tamanho_perm) {
        // codigo de erro caso arquivo maior que permitido
    } else {
        // condicao caso arquivo permitido
        $tipo = ($_FILES['logo_est']['type']);
        $arquivo = ($_FILES['logo_est']['name']);
        $temp_arq = ($_FILES['logo_est']['tmp_name']);
        $size = ($_FILES['logo_est']['size']);
        $pasta = "uploads";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);
        $estipulanteDao = new estipulanteDAO($conn, $BASE_URL);
    }
    // Receber os dados dos inputs
    $id_estipulante = filter_input(INPUT_POST, "id_estipulante");
    $nome_est = filter_input(INPUT_POST, "nome_est", FILTER_SANITIZE_SPECIAL_CHARS);
    $nome_est = strtoupper($nome_est);
    $endereco_est = filter_input(INPUT_POST, "endereco_est", FILTER_SANITIZE_SPECIAL_CHARS);

    $email01_est = filter_input(INPUT_POST, "email01_est", FILTER_SANITIZE_EMAIL);
    $email01_est = strtolower($email01_est);

    $email02_est = filter_input(INPUT_POST, "email02_est", FILTER_SANITIZE_EMAIL);
    $email02_est = strtolower($email02_est);

    $cidade_est = filter_input(INPUT_POST, "cidade_est", FILTER_SANITIZE_SPECIAL_CHARS);
    $estado_est = filter_input(INPUT_POST, "estado_est", FILTER_SANITIZE_SPECIAL_CHARS);

    $cnpj_est = filter_input(INPUT_POST, "cnpj_est", FILTER_SANITIZE_SPECIAL_CHARS);
    $cnpj_est = str_replace('/', '', $cnpj_est);
    $cnpj_est = str_replace('-', '', $cnpj_est);
    $cnpj_est = str_replace('.', '', $cnpj_est);

    $telefone01_est = filter_input(INPUT_POST, "telefone01_est", FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone01_est = str_replace('-', '', $telefone01_est);
    $telefone01_est = str_replace('(', '', $telefone01_est);
    $telefone01_est = str_replace(') ', '', $telefone01_est);

    $telefone02_est = filter_input(INPUT_POST, "telefone02_est");
    $telefone02_est = str_replace('-', '', $telefone02_est);
    $telefone02_est = str_replace('(', '', $telefone02_est);
    $telefone02_est = str_replace(') ', '', $telefone02_est);
    $cep_est = filter_input(INPUT_POST, "cep_est");

    $nome_contato_est = filter_input(INPUT_POST, "nome_contato_est");
    $nome_responsavel_est = filter_input(INPUT_POST, "nome_responsavel_est");
    $email_contato_est = filter_input(INPUT_POST, "email_contato_est");
    $email_responsavel_est = filter_input(INPUT_POST, "email_responsavel_est");
    $telefone_contato_est = filter_input(INPUT_POST, "telefone_contato_est");
    $telefone_responsavel_est = filter_input(INPUT_POST, "telefone_responsavel_est");

    $data_create_est = filter_input(INPUT_POST, "data_create_est");
    $usuario_create_est = filter_input(INPUT_POST, "usuario_create_est");
    $fk_usuario_est = filter_input(INPUT_POST, "fk_usuario_est");

    $numero_est = filter_input(INPUT_POST, "numero_est");
    $bairro_est = filter_input(INPUT_POST, "bairro_est");
    $logo_est = $arquivo;

    $estipulanteData = $estipulanteDao->findById($id_estipulante);

    $estipulanteData->id_estipulante = $id_estipulante;
    $estipulanteData->nome_est = $nome_est;
    $estipulanteData->endereco_est = $endereco_est;
    $estipulanteData->email01_est = $email01_est;
    $estipulanteData->email02_est = $email02_est;
    $estipulanteData->cidade_est = $cidade_est;
    $estipulanteData->estado_est = $estado_est;

    $estipulanteData->telefone01_est = $telefone01_est;
    $estipulanteData->telefone02_est = $telefone02_est;
    $estipulanteData->numero_est = $numero_est;

    $estipulanteData->bairro_est = $bairro_est;
    $estipulanteData->logo_est = $logo_est;
    $estipulanteData->cnpj_est = $cnpj_est;

    $estipulanteData->data_create_est = $data_create_est;
    $estipulanteData->usuario_create_est = $usuario_create_est;
    $estipulanteData->fk_usuario_est = $fk_usuario_est;

    $estipulanteData->nome_contato_est = $nome_contato_est;
    $estipulanteData->nome_responsavel_est = $nome_responsavel_est;
    $estipulanteData->email_contato_est = $email_contato_est;
    $estipulanteData->email_responsavel_est = $email_responsavel_est;
    $estipulanteData->telefone_contato_est = $telefone_contato_est;
    $estipulanteData->telefone_responsavel_est = $telefone_responsavel_est;
    $estipulanteData->cep_est = $cep_est;

    $estipulanteDao->update($estipulanteData);

    header('Location: ' . $BASE_URL . 'estipulantes');
}


if ($type === "delUpdate") {

    $estipulanteDao = new estipulanteDAO($conn, $BASE_URL);

    $id_estipulante = filter_input(INPUT_POST, "id_estipulante");
    $deletado_est = 's';
    $estipulanteData = $estipulanteDao->findById($id_estipulante);

    $estipulanteData->id_estipulante = $id_estipulante;
    $estipulanteData->deletado_est = $deletado_est;

    $estipulanteDao->deletarUpdate($estipulanteData);
    header('Location: ' . $BASE_URL . 'estipulantes');
}

if ($type === "delete") {
    // Recebe os dados do form
    $id_estipulante = filter_input(INPUT_POST, "id_estipulante");

    $estipulanteDao = new estipulanteDAO($conn, $BASE_URL);

    $estipulante = $estipulanteDao->findById($id_estipulante);

    if (3 < 4) {

        $estipulanteDao->destroy($id_estipulante);

        include_once('list_estipulante.php');
    } else {

        $message->setMessage("Informações inválidas!", "error", "index.php");
    }
}