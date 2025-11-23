<?php
require_once("globals.php");
require_once("db.php");
require_once("models/hospital.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/hospitalDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$hospitalDao = new hospitalDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");
$typeDel = filter_input(INPUT_POST, "typeDel");


// Resgata dados do usuário
if ($type === "create") {

    $erros = "";

    // Receber os dados dos inputs
    $nome_hosp = filter_input(INPUT_POST, "nome_hosp");
    $nome_hosp = ucwords(strtolower($nome_hosp));
    $endereco_hosp = filter_input(INPUT_POST, "endereco_hosp");
    $email01_hosp = filter_input(INPUT_POST, "email01_hosp");
    $email02_hosp = filter_input(INPUT_POST, "email02_hosp");
    $cidade_hosp = filter_input(INPUT_POST, "cidade_hosp");
    $estado_hosp = filter_input(INPUT_POST, "estado_hosp");
    $ativo_hosp = filter_input(INPUT_POST, "ativo_hosp");
    $cep_hosp = filter_input(INPUT_POST, "cep_hosp");
    $cep_hosp = str_replace('-', '', $cep_hosp);

    $cnpj_hosp = filter_input(INPUT_POST, "cnpj_hosp");
    $cnpj_hosp = str_replace('/', '', $cnpj_hosp);
    $cnpj_hosp = str_replace('-', '', $cnpj_hosp);
    $cnpj_hosp = str_replace('.', '', $cnpj_hosp);

    $telefone01_hosp = filter_input(INPUT_POST, "telefone01_hosp");
    $telefone01_hosp = str_replace('-', '', $telefone01_hosp);
    $telefone01_hosp = str_replace('(', '', $telefone01_hosp);
    $telefone01_hosp = str_replace(') ', '', $telefone01_hosp);

    $telefone02_hosp = filter_input(INPUT_POST, "telefone02_hosp");
    $telefone02_hosp = str_replace('-', '', $telefone02_hosp);
    $telefone02_hosp = str_replace('(', '', $telefone02_hosp);
    $telefone02_hosp = str_replace(') ', '', $telefone02_hosp);

    $numero_hosp = filter_input(INPUT_POST, "numero_hosp");
    $bairro_hosp = filter_input(INPUT_POST, "bairro_hosp");
    $fk_usuario_hosp = filter_input(INPUT_POST, "fk_usuario_hosp");
    $usuario_create_hosp = filter_input(INPUT_POST, "usuario_create_hosp");
    $data_create_hosp = filter_input(INPUT_POST, "data_create_hosp");
    $longitude_hosp = filter_input(INPUT_POST, "longitude_hosp");
    $latitude_hosp = filter_input(INPUT_POST, "latitude_hosp");
    $coordenador_medico_hosp = filter_input(INPUT_POST, "coordenador_medico_hosp");
    $diretor_hosp = filter_input(INPUT_POST, "diretor_hosp");
    $coordenador_fat_hosp = filter_input(INPUT_POST, "coordenador_fat_hosp");
    $deletado_hosp = filter_input(INPUT_POST, "deletado_hosp");

    $hospital = new hospital();

    // Validação mínima de dados
    if (!empty($nome_hosp)) {

        $hospital->nome_hosp = $nome_hosp;
        $hospital->ativo_hosp = $ativo_hosp;
        $hospital->endereco_hosp = $endereco_hosp;
        $hospital->bairro_hosp = $bairro_hosp;
        $hospital->cep_hosp = $cep_hosp;
        $hospital->email02_hosp = $email02_hosp;
        $hospital->email01_hosp = $email01_hosp;
        $hospital->cidade_hosp = $cidade_hosp;
        $hospital->estado_hosp = $estado_hosp;
        $hospital->cnpj_hosp = $cnpj_hosp;
        $hospital->telefone01_hosp = $telefone01_hosp;
        $hospital->telefone02_hosp = $telefone02_hosp;
        $hospital->numero_hosp = $numero_hosp;
        $hospital->bairro_hosp = $bairro_hosp;
        $hospital->fk_usuario_hosp = $fk_usuario_hosp;
        $hospital->usuario_create_hosp = $usuario_create_hosp;
        $hospital->data_create_hosp = $data_create_hosp;
        $hospital->longitude_hosp = $longitude_hosp;
        $hospital->latitude_hosp = $latitude_hosp;
        $hospital->coordenador_medico_hosp = $coordenador_medico_hosp;
        $hospital->diretor_hosp = $diretor_hosp;
        $hospital->coordenador_fat_hosp = $coordenador_fat_hosp;
        $hospital->deletado_hosp = $deletado_hosp;

      

        $hospitalDao->create($hospital);
    }
    header("Location: " . $BASE_URL . "hospitais");
    exit;
}
if ($type === "update") {


    $erros = "";


    $hospitalDao = new hospitalDAO($conn, $BASE_URL);

    // Receber os dados dos inputs
    $nome_hosp = filter_input(INPUT_POST, "nome_hosp");
    $nome_hosp = ucwords(strtolower($nome_hosp));
    $endereco_hosp = filter_input(INPUT_POST, "endereco_hosp");
    $email01_hosp = filter_input(INPUT_POST, "email01_hosp");
    $email01_hosp = strtolower($email01_hosp);

    $email02_hosp = filter_input(INPUT_POST, "email02_hosp");
    $email02_hosp = strtolower($email02_hosp);

    $cidade_hosp = filter_input(INPUT_POST, "cidade_hosp");
    $estado_hosp = filter_input(INPUT_POST, "estado_hosp");
    $ativo_hosp = filter_input(INPUT_POST, "ativo_hosp");
    $cep_hosp = filter_input(INPUT_POST, "cep_hosp");
    $cep_hosp = str_replace('-', '', $cep_hosp);

    $cnpj_hosp = filter_input(INPUT_POST, "cnpj_hosp");
    $cnpj_hosp = str_replace('/', '', $cnpj_hosp);
    $cnpj_hosp = str_replace('-', '', $cnpj_hosp);
    $cnpj_hosp = str_replace('.', '', $cnpj_hosp);

    $telefone01_hosp = filter_input(INPUT_POST, "telefone01_hosp");
    $telefone01_hosp = str_replace('-', '', $telefone01_hosp);
    $telefone01_hosp = str_replace('(', '', $telefone01_hosp);
    $telefone01_hosp = str_replace(') ', '', $telefone01_hosp);

    $telefone02_hosp = filter_input(INPUT_POST, "telefone02_hosp");
    $telefone02_hosp = str_replace('-', '', $telefone02_hosp);
    $telefone02_hosp = str_replace('(', '', $telefone02_hosp);
    $telefone02_hosp = str_replace(') ', '', $telefone02_hosp);


    $numero_hosp = filter_input(INPUT_POST, "numero_hosp");
    $bairro_hosp = filter_input(INPUT_POST, "bairro_hosp");
    $longitude_hosp = filter_input(INPUT_POST, "longitude_hosp");
    $latitude_hosp = filter_input(INPUT_POST, "latitude_hosp");
    $coordenador_medico_hosp = filter_input(INPUT_POST, "coordenador_medico_hosp");
    $diretor_hosp = filter_input(INPUT_POST, "diretor_hosp");
    $coordenador_fat_hosp = filter_input(INPUT_POST, "coordenador_fat_hosp");
    $id_hospital = filter_input(INPUT_POST, "id_hospital");

    $hospitalData = $hospitalDao->findById($id_hospital);

    $hospitalData->id_hospital = $id_hospital;
    $hospitalData->nome_hosp = $nome_hosp;
    $hospitalData->endereco_hosp = $endereco_hosp;
    $hospitalData->email01_hosp = $email01_hosp;
    $hospitalData->email02_hosp = $email02_hosp;
    $hospitalData->cidade_hosp = $cidade_hosp;
    $hospitalData->estado_hosp = $estado_hosp;
    $hospitalData->cep_hosp = $cep_hosp;
    $hospitalData->cnpj_hosp = $cnpj_hosp;
    $hospitalData->telefone01_hosp = $telefone01_hosp;
    $hospitalData->telefone02_hosp = $telefone02_hosp;
    $hospitalData->numero_hosp = $numero_hosp;
    $hospitalData->bairro_hosp = $bairro_hosp;
    $hospitalData->longitude_hosp = $longitude_hosp;
    $hospitalData->latitude_hosp = $latitude_hosp;
    $hospitalData->coordenador_medico_hosp = $coordenador_medico_hosp;
    $hospitalData->diretor_hosp = $diretor_hosp;
    $hospitalData->coordenador_fat_hosp = $coordenador_fat_hosp;
    $hospitalData->ativo_hosp = $ativo_hosp;

    $hospitalDao->update($hospitalData);
    header("Location: " . $BASE_URL . "hospitais");
    exit;
}

if ($type === "delUpdate") {

    $hospitalDao = new hospitalDAO($conn, $BASE_URL);

    $id_hospital = filter_input(INPUT_POST, "id_hospital");
    $deletado_hosp = 's';
    $hospitalData = $hospitalDao->findById($id_hospital);

    $hospitalData->id_hospital = $id_hospital;
    $hospitalData->deletado_hosp = $deletado_hosp;

    $hospitalDao->deletarUpdate($hospitalData);

    header("Location: " . $BASE_URL . "hospitais");
    exit;
}

if ($type === "delete") {
    // Recebe os dados do form
    $id_hospital = filter_input(INPUT_POST, "id_hospital");

    $hospitalDao = new hospitalDAO($conn, $BASE_URL);

    $hospital = $hospitalDao->findById($id_hospital);

    if (3 < 4) {

        $hospitalDao->destroy($id_hospital);

        header("Location: " . $BASE_URL . "hospitais");
        exit;
    } else {

        $message->setMessage("Informações inválidas!", "error", "index.php");
    }
}
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     var_dump($_POST); // Exibe os dados enviados
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_hospital'])) {
    // Salva o conteúdo de $_POST em uma variável
    $dadosPost = $_POST;

    // Filtra o ID do hospital para garantir que é um inteiro válido
    $id_hospital = filter_var($dadosPost['id_hospital'], FILTER_VALIDATE_INT);

    if ($id_hospital) {
        echo "ID do Hospital Selecionado: " . $id_hospital; // Retorna o ID do hospital
    } else {
        echo "ID inválido.";
    }
} else {
    // Salva os dados do array $_POST em uma variável, mesmo sem id_hospital definido
    $dadosPost = $_POST;

    // Exibe os dados enviados para debug
    echo "Dados recebidos: ";
    print_r($dadosPost);

    echo "Nenhum hospital foi selecionado.";
}
