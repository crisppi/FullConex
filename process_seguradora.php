<?php

require_once("globals.php");
require_once("db.php");

require_once("models/seguradora.php");
require_once("dao/seguradoraDao.php");

require_once("models/usuario.php");
require_once("dao/usuarioDao.php");

require_once("models/message.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$seguradoraDao = new seguradoraDAO($conn, $BASE_URL);

$type = filter_input(INPUT_POST, "type");
$typeDel = filter_input(INPUT_POST, "typeDel");

if ($type === "create") {

    $tipo = ($_FILES['logo_seg']['type']);
    $tamanho_perm = 1024 * 1024 * 2;
    $size = $_FILES['logo_seg']['size'];

    $erros = "";

    if (($_FILES['logo_seg']['size']) > $tamanho_perm) {
        // codigo de erro caso arquivo maior que permitido
    } else {
        // condicao caso arquivo permitido
        $arquivo = ($_FILES['logo_seg']['name']);
        $temp_arq = ($_FILES['logo_seg']['tmp_name']);
        $pasta = "uploads";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);

        // Resgata dados da imagem
        $tipo = ($_FILES['logo_seg']['type']);
        $arquivo = ($_FILES['logo_seg']['name']);
        $temp_arq = ($_FILES['logo_seg']['tmp_name']);
        $size = ($_FILES['logo_seg']['size']);
        $pasta = "uploads";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);

        // Receber os dados dos inputs
        $seguradora_seg = filter_input(INPUT_POST, "seguradora_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $seguradora_seg = strtoupper($seguradora_seg);
        $endereco_seg = filter_input(INPUT_POST, "endereco_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $bairro_seg = filter_input(INPUT_POST, "bairro_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $email01_seg = filter_input(INPUT_POST, "email01_seg", FILTER_SANITIZE_EMAIL);
        $email02_seg = filter_input(INPUT_POST, "email02_seg", FILTER_SANITIZE_EMAIL);
        $cidade_seg = filter_input(INPUT_POST, "cidade_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $estado_seg = filter_input(INPUT_POST, "estado_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $cep_seg = filter_input(INPUT_POST, "cep_seg", FILTER_SANITIZE_SPECIAL_CHARS);

        $cnpj_seg = filter_input(INPUT_POST, "cnpj_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $cnpj_seg = str_replace('/', '', $cnpj_seg);
        $cnpj_seg = str_replace('-', '', $cnpj_seg);
        $cnpj_seg = str_replace('.', '', $cnpj_seg);

        $telefone01_seg = filter_input(INPUT_POST, "telefone01_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $telefone01_seg = str_replace('-', '', $telefone01_seg);
        $telefone01_seg = str_replace('(', '', $telefone01_seg);
        $telefone01_seg = str_replace(') ', '', $telefone01_seg);

        $telefone02_seg = filter_input(INPUT_POST, "telefone02_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $telefone02_seg = str_replace('-', '', $telefone02_seg);
        $telefone02_seg = str_replace('(', '', $telefone02_seg);
        $telefone02_seg = str_replace(') ', '', $telefone02_seg);

        $numero_seg = filter_input(INPUT_POST, "numero_seg");
        $data_create_seg = filter_input(INPUT_POST, "data_create_seg");
        $fk_usuario_seg = filter_input(INPUT_POST, "fk_usuario_seg");
        $usuario_create_seg = filter_input(INPUT_POST, "usuario_create_seg");
        $ativo_seg = filter_input(INPUT_POST, "ativo_seg");
        $coordenador_seg = filter_input(INPUT_POST, "coordenador_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $contato_seg = filter_input(INPUT_POST, "contato_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $coord_rh_seg = filter_input(INPUT_POST, "coord_rh_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $deletado_seg = filter_input(INPUT_POST, "deletado_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $valor_alto_custo_seg = filter_input(INPUT_POST, "valor_alto_custo_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $dias_visita_seg = filter_input(INPUT_POST, "dias_visita_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $dias_visita_uti_seg = filter_input(INPUT_POST, "dias_visita_uti_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $longa_permanencia_seg = filter_input(INPUT_POST, "longa_permanencia_seg", FILTER_SANITIZE_SPECIAL_CHARS);

        $logo_segArray = filter_input(INPUT_POST, "logo_seg");
        $logo_seg = $arquivo;

        $seguradora = new seguradora();

        // Validação mínima de dados
        if (!empty($seguradora_seg)) {

            $seguradora->seguradora_seg = $seguradora_seg;
            $seguradora->endereco_seg = $endereco_seg;
            $seguradora->bairro_seg = $bairro_seg;
            $seguradora->email01_seg = $email01_seg;
            $seguradora->email02_seg = $email02_seg;
            $seguradora->cidade_seg = $cidade_seg;
            $seguradora->estado_seg = $estado_seg;
            $seguradora->cnpj_seg = $cnpj_seg;
            $seguradora->telefone01_seg = $telefone01_seg;
            $seguradora->telefone02_seg = $telefone02_seg;
            $seguradora->numero_seg = $numero_seg;
            $seguradora->numero_seg = $numero_seg;
            $seguradora->data_create_seg = $data_create_seg;
            $seguradora->fk_usuario_seg = $fk_usuario_seg;
            $seguradora->usuario_create_seg = $usuario_create_seg;
            $seguradora->coordenador_seg = $coordenador_seg;
            $seguradora->contato_seg = $contato_seg;
            $seguradora->coord_rh_seg = $coord_rh_seg;
            $seguradora->ativo_seg = $ativo_seg;
            $seguradora->dias_visita_seg = $dias_visita_seg;
            $seguradora->dias_visita_uti_seg = $dias_visita_uti_seg;
            $seguradora->valor_alto_custo_seg = $valor_alto_custo_seg;
            $seguradora->longa_permanencia_seg = $longa_permanencia_seg;
            $seguradora->logo_seg = $logo_seg;
            $seguradora->deletado_seg = $deletado_seg;
            $seguradora->cep_seg = $cep_seg;

            $seguradoraDao->create($seguradora);
            header("Location: ' . $BASE_URL . 'seguradoras");
        }
    }
} else if ($type === "update") {

    $tipo = ($_FILES['logo_seg']['type']);
    $tamanho_perm = 1024 * 1024 * 1;
    $size = $_FILES['logo_seg']['size'];

    $erros = "";

    if (($_FILES['logo_seg']['size']) > $tamanho_perm) {
        // codigo de erro caso arquivo maior que permitido
    } else {

        $arquivo = ($_FILES['logo_seg']['name']);
        $temp_arq = ($_FILES['logo_seg']['tmp_name']);
        $pasta = "uploads";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);

        // Receber os dados dos inputs
        $id_seguradora = filter_input(INPUT_POST, "id_seguradora");
        $seguradora_seg = filter_input(INPUT_POST, "seguradora_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $seguradora_seg = strtoupper($seguradora_seg);
        $endereco_seg = filter_input(INPUT_POST, "endereco_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $email01_seg = filter_input(INPUT_POST, "email01_seg", FILTER_SANITIZE_EMAIL);
        $email01_seg = strtolower($email01_seg);

        $email02_seg = filter_input(INPUT_POST, "email02_seg", FILTER_SANITIZE_EMAIL);
        $email02_seg = strtolower($email02_seg);

        $cidade_seg = filter_input(INPUT_POST, "cidade_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $estado_seg = filter_input(INPUT_POST, "estado_seg", FILTER_SANITIZE_SPECIAL_CHARS);

        $cnpj_seg = filter_input(INPUT_POST, "cnpj_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $cnpj_seg = str_replace('/', '', $cnpj_seg);
        $cnpj_seg = str_replace('-', '', $cnpj_seg);
        $cnpj_seg = str_replace('.', '', $cnpj_seg);

        $telefone01_seg = filter_input(INPUT_POST, "telefone01_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $telefone01_seg = str_replace('-', '', $telefone01_seg);
        $telefone01_seg = str_replace('(', '', $telefone01_seg);
        $telefone01_seg = str_replace(') ', '', $telefone01_seg);

        $telefone02_seg = filter_input(INPUT_POST, "telefone02_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $telefone02_seg = str_replace('-', '', $telefone02_seg);
        $telefone02_seg = str_replace('(', '', $telefone02_seg);
        $telefone02_seg = str_replace(') ', '', $telefone02_seg);

        $numero_seg = filter_input(INPUT_POST, "numero_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $bairro_seg = filter_input(INPUT_POST, "bairro_seg");
        $data_create_seg = filter_input(INPUT_POST, "data_create_seg");
        $usuario_create_seg = filter_input(INPUT_POST, "usuario_create_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $fk_usuario_seg = filter_input(INPUT_POST, "fk_usuario_seg");
        $coordenador_seg = filter_input(INPUT_POST, "coordenador_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $contato_seg = filter_input(INPUT_POST, "contato_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $ativo_seg = filter_input(INPUT_POST, "ativo_seg");
        $cep_seg = filter_input(INPUT_POST, "cep_seg");

        $dias_visita_seg = filter_input(INPUT_POST, "dias_visita_seg");
        $dias_visita_uti_seg = filter_input(INPUT_POST, "dias_visita_uti_seg");
        $longa_permanencia_seg = filter_input(INPUT_POST, "longa_permanencia_seg");
        $valor_alto_custo_seg = filter_input(INPUT_POST, "valor_alto_custo_seg");
        $valor_alto_custo_seg = str_replace('R$', '', $valor_alto_custo_seg);
        $valor_alto_custo_seg = str_replace('.', '', $valor_alto_custo_seg);
        // $valor_alto_custo_seg = str_replace('(', '', $valor_alto_custo_seg);

        $deletado_seg = filter_input(INPUT_POST, "deletado_seg");

        $coord_rh_seg = filter_input(INPUT_POST, "coord_rh_seg", FILTER_SANITIZE_SPECIAL_CHARS);
        $logo_seg = $arquivo;

        $seguradoraData = $seguradoraDao->findById($id_seguradora);

        $seguradoraData->id_seguradora = $id_seguradora;
        $seguradoraData->seguradora_seg = $seguradora_seg;
        $seguradoraData->endereco_seg = $endereco_seg;
        $seguradoraData->email01_seg = $email01_seg;
        $seguradoraData->email02_seg = $email02_seg;
        $seguradoraData->cidade_seg = $cidade_seg;
        $seguradoraData->estado_seg = $estado_seg;
        $seguradoraData->cnpj_seg = $cnpj_seg;
        $seguradoraData->telefone01_seg = $telefone01_seg;
        $seguradoraData->telefone02_seg = $telefone02_seg;
        $seguradoraData->numero_seg = $numero_seg;
        $seguradoraData->bairro_seg = $bairro_seg;
        $seguradoraData->data_create_seg = $data_create_seg;
        $seguradoraData->fk_usuario_seg = $fk_usuario_seg;
        $seguradoraData->usuario_create_seg = $usuario_create_seg;
        $seguradoraData->coordenador_seg = $coordenador_seg;
        $seguradoraData->contato_seg = $contato_seg;
        $seguradoraData->ativo_seg = $ativo_seg;
        $seguradoraData->coord_rh_seg = $coord_rh_seg;
        $seguradoraData->valor_alto_custo_seg = $valor_alto_custo_seg;
        $seguradoraData->dias_visita_uti_seg = $dias_visita_uti_seg;
        $seguradoraData->dias_visita_seg = $dias_visita_seg;
        $seguradoraData->longa_permanencia_seg = $longa_permanencia_seg;
        $seguradoraData->deletado_seg = $deletado_seg;
        $seguradoraData->logo_seg = $logo_seg;
        $seguradoraData->cep_seg = $cep_seg;

        $seguradoraDao->update($seguradoraData);

        header("Location: " . $BASE_URL . "seguradoras");
        exit;
    }
}

if ($type === "delete") {
    // Recebe os dados do form
    $id_seguradora = filter_input(INPUT_POST, "id_seguradora");

    $seguradoraDao = new seguradoraDAO($conn, $BASE_URL);

    $seguradora = $seguradoraDao->findById($id_seguradora);

    if ($seguradora) {

        $seguradoraDao->destroy($id_seguradora);
        header("Location: " . $BASE_URL . "seguradoras");
        exit;
    } else {

        //$message->setMessage("Informações inválidas!", "error", "index.php");
    }
}

if ($type === "delUpdate") {

    $seguradoraDao = new seguradoraDAO($conn, $BASE_URL);

    $id_seguradora = filter_input(INPUT_POST, "id_seguradora");
    $deletado_seg = 's';

    $seguradoraData = $seguradoraDao->findById($id_seguradora);

    $seguradoraData->id_seguradora = $id_seguradora;
    $seguradoraData->deletado_seg = $deletado_seg;

    $seguradoraDao->deletarUpdate($seguradoraData);

    header("Location: " . $BASE_URL . "seguradoras");
    exit;
}
