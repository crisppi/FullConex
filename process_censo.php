<?php
require_once("globals.php");
require_once("db.php");

require_once("models/censo.php");
require_once("dao/censoDao.php");

require_once("models/internacao.php");
require_once("dao/internacaoDao.php");


$censoDao = new CensoDAO($conn, $BASE_URL);
$internacaoDao = new InternacaoDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário
if ($type === "create") {
    
    // Receber os dados dos inputs
    $fk_hospital_censo = filter_input(INPUT_POST, "fk_hospital_censo");
    $fk_paciente_censo = filter_input(INPUT_POST, "fk_paciente_censo");
    $data_censo = filter_input(INPUT_POST, "data_censo");
    $senha_censo = filter_input(INPUT_POST, "senha_censo");
    $acomodacao_censo = filter_input(INPUT_POST, "acomodacao_censo");
    $tipo_admissao_censo = filter_input(INPUT_POST, "tipo_admissao_censo");
    $modo_internacao_censo = filter_input(INPUT_POST, "modo_internacao_censo");
    $usuario_create_censo = filter_input(INPUT_POST, "usuario_create_censo");
    $data_create_censo = filter_input(INPUT_POST, "data_create_censo");
    $titular_censo = filter_input(INPUT_POST, "titular_censo");

    $censo = new censo();


    // Validação mínima de dados
    if (3 < 4) {

        $censo->fk_hospital_censo = $fk_hospital_censo;
        $censo->fk_paciente_censo = $fk_paciente_censo;
        $censo->data_censo = $data_censo;
        $censo->senha_censo = $senha_censo;
        $censo->acomodacao_censo = $acomodacao_censo;
        $censo->tipo_admissao_censo = $tipo_admissao_censo;
        $censo->modo_internacao_censo = $modo_internacao_censo;
        $censo->usuario_create_censo = $usuario_create_censo;
        $censo->data_create_censo = $data_create_censo;
        $censo->titular_censo = $titular_censo;
        if ($internacaoDao->checkInternAtiva($censo->fk_paciente_censo) > 0) {
            echo '0';
        }else {
            $censoDao->create($censo);
            echo '1';
        }
    };
}

if ($type == "update") {
    // Receber os dados dos inputs
    $fk_hospital_censo = filter_input(INPUT_POST, "fk_hospital_censo");
    $fk_paciente_censo = filter_input(INPUT_POST, "fk_paciente_censo");
    $data_censo = filter_input(INPUT_POST, "data_censo");
    $senha_censo = filter_input(INPUT_POST, "senha_censo");
    $acomodacao_censo = filter_input(INPUT_POST, "acomodacao_censo");
    $tipo_admissao_censo = filter_input(INPUT_POST, "tipo_admissao_censo");
    $modo_internacao_censo = filter_input(INPUT_POST, "modo_internacao_censo");
    $usuario_create_censo = filter_input(INPUT_POST, "usuario_create_censo");
    $data_create_censo = filter_input(INPUT_POST, "data_create_censo");
    $titular_censo = filter_input(INPUT_POST, "titular_censo");

    $censo = new censo();

    if (3 < 4) {

        $censo->fk_hospital_censo = $fk_hospital_censo;
        $censo->fk_paciente_censo = $fk_paciente_censo;
        $censo->data_censo = $data_censo;
        $censo->senha_censo = $senha_censo;
        $censo->acomodacao_censo = $acomodacao_censo;
        $censo->tipo_admissao_censo = $tipo_admissao_censo;
        $censo->modo_internacao_censo = $modo_internacao_censo;
        $censo->usuario_create_censo = $usuario_create_censo;
        $censo->data_create_censo = $data_create_censo;
        $censo->id_censo = $id_censo;
        $censo->titular_censo = $titular_censo;
        $censoDao->update($censo);

        // header("location:censo/lista");
    };

    // header("location:censo/lista");
};

// header("location:censo/lista");