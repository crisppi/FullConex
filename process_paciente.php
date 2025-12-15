<?php

require_once("globals.php");
require_once("db.php");
require_once("models/paciente.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
require_once("dao/pacienteDao.php");

$message = new Message($BASE_URL); // <-- Objeto $message original (linha 11)
$userDao = new UserDAO($conn, $BASE_URL);
$pacienteDao = new PacienteDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");
$typeDel = filter_input(INPUT_POST, "typeDel");

// Resgata dados do usuário

if ($type === "create") {
    $verificarId = $pacienteDao->verificaId1();

    // Receber os dados dos inputs
    $nome_pac = filter_input(INPUT_POST, "nome_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $nome_pac = strtoupper($nome_pac);
    $nome_social_pac = filter_input(INPUT_POST, "nome_social_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $nome_social_pac = strtoupper($nome_social_pac);
    $endereco_pac = filter_input(INPUT_POST, "endereco_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $email01_pac = filter_input(INPUT_POST, "email01_pac", FILTER_SANITIZE_EMAIL);
    $email01_pac = strtolower($email01_pac);

    $email02_pac = filter_input(INPUT_POST, "email02_pac", FILTER_SANITIZE_EMAIL);
    $email02_pac = strtolower($email02_pac);

    $cidade_pac = filter_input(INPUT_POST, "cidade_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $mae_pac = filter_input(INPUT_POST, "mae_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $data_nasc_pac = filter_input(INPUT_POST, "data_nasc_pac") ?: NULL;

    $cpf_pac = filter_input(INPUT_POST, "cpf_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $somenteNumerosCPF = preg_replace('/[^0-9]/', '', $cpf_pac);

    $telefone01_pac = filter_input(INPUT_POST, "telefone01_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone01_pac = str_replace('-', '', $telefone01_pac);
    $telefone01_pac = str_replace('(', '', $telefone01_pac);
    $telefone01_pac = str_replace(') ', '', $telefone01_pac);

    $telefone02_pac = filter_input(INPUT_POST, "telefone02_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone02_pac = str_replace('-', '', $telefone02_pac);
    $telefone02_pac = str_replace('(', '', $telefone02_pac);
    $telefone02_pac = str_replace(') ', '', $telefone02_pac);

    $numero_pac = filter_input(INPUT_POST, "numero_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $bairro_pac = filter_input(INPUT_POST, "bairro_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $complemento_pac = filter_input(INPUT_POST, "complemento_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $ativo_pac = filter_input(INPUT_POST, "ativo_pac");
    $sexo_pac = filter_input(INPUT_POST, "sexo_pac");
    $usuario_create_pac = filter_input(INPUT_POST, "usuario_create_pac");
    $data_create_pac = filter_input(INPUT_POST, "data_create_pac");
    $fk_estipulante_pac = filter_input(INPUT_POST, "fk_estipulante_pac") ?: 1;
    $fk_seguradora_pac = filter_input(INPUT_POST, "fk_seguradora_pac") ?: 1;
    $fk_usuario_pac = filter_input(INPUT_POST, "fk_usuario_pac");
    $obs_pac = filter_input(INPUT_POST, "obs_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $matricula_pac = filter_input(INPUT_POST, "matricula_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $estado_pac = filter_input(INPUT_POST, "estado_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $cep_pac = filter_input(INPUT_POST, "cep_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $deletado_pac = filter_input(INPUT_POST, "deletado_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $num_atendimento_pac = filter_input(INPUT_POST, "num_atendimento_pac");
    $cep_pac = str_replace('-', '', $cep_pac);
    // --- Novos campos (RN / mãe titular / matrícula titular)
    $recem_nascido_pac = filter_input(INPUT_POST, "recem_nascido_pac"); // 's' | 'n' | ''
    $mae_titular_pac = filter_input(INPUT_POST, "mae_titular_pac");   // 's' | 'n' | '' (só se RN = s)
    $matricula_titular_pac = filter_input(INPUT_POST, "matricula_titular_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    // Número do RN (campo do form: numero_recem_nascido_pac) -> INT ou NULL
    $numero_recem_nascido_pac = filter_input(INPUT_POST, "numero_recem_nascido_pac", FILTER_SANITIZE_NUMBER_INT);
    $numero_rn_pac = null;

    if ($recem_nascido_pac === 's') {
        // se for RN, número é opcional/obrigatório? (se quiser obrigar, valide aqui)
        if ($numero_recem_nascido_pac !== null && $numero_recem_nascido_pac !== '') {
            $numero_rn_pac = (int) preg_replace('/\D/', '', $numero_recem_nascido_pac);
        } else {
            // caso queira exigir, descomente:
            // $message->setMessage("Informe o Número RN.", "error", "back");
            // exit;
            $numero_rn_pac = null;
        }
    } else {
        $numero_rn_pac = null;
    }


    // Normalização e validação condicional
    if ($recem_nascido_pac !== 's') {
        // Não é RN -> não avaliamos mãe titular / matrícula
        $mae_titular_pac = null;
        $matricula_titular_pac = null;
    } else {
        // É RN
        if ($mae_titular_pac === 'n') {
            // Mãe NÃO é titular -> matrícula da titular é obrigatória
            if (!$matricula_titular_pac) {
                $message->setMessage("Informe a matrícula da titular (recém-nascido com mãe não titular).", "error", "back");
                exit;
            }
        } else {
            // Mãe é titular (ou não selecionado)
            $matricula_titular_pac = null;
        }
    }


    $paciente = new Paciente();
    // Validação mínima de dados4
    if (3 < 4) {

        $paciente->nome_pac = $nome_pac;
        $paciente->nome_social_pac = $nome_social_pac;
        $paciente->endereco_pac = $endereco_pac;
        $paciente->sexo_pac = $sexo_pac;
        $paciente->mae_pac = $mae_pac;
        $paciente->bairro_pac = $bairro_pac;
        $paciente->data_nasc_pac = $data_nasc_pac;
        $paciente->email02_pac = $email02_pac;
        $paciente->email01_pac = $email01_pac;
        $paciente->cidade_pac = $cidade_pac;

        // =================================================================
        // INÍCIO DA CORREÇÃO 1 (CPF Duplicado no Create)
        // =================================================================
        $paciente->cpf_pac = empty($somenteNumerosCPF) ? null : $somenteNumerosCPF;
        // =================================================================
        // FIM DA CORREÇÃO 1
        // =================================================================

        $paciente->telefone01_pac = $telefone01_pac;
        $paciente->telefone02_pac = $telefone02_pac;
        $paciente->numero_pac = $numero_pac;
        $paciente->complemento_pac = $complemento_pac;
        $paciente->ativo_pac = $ativo_pac;
        $paciente->data_create_pac = $data_create_pac;
        $paciente->usuario_create_pac = $usuario_create_pac;
        $paciente->fk_usuario_pac = $fk_usuario_pac;
        $paciente->fk_seguradora_pac = $fk_seguradora_pac;
        $paciente->fk_estipulante_pac = $fk_estipulante_pac;
        $paciente->obs_pac = $obs_pac;
        $paciente->matricula_pac = $matricula_pac;
        $paciente->estado_pac = $estado_pac;
        $paciente->cep_pac = $cep_pac;
        $paciente->deletado_pac = $deletado_pac;
        $paciente->num_atendimento_pac = $num_atendimento_pac;
        $paciente->recem_nascido_pac = $recem_nascido_pac ?: null;
        $paciente->mae_titular_pac = $mae_titular_pac ?: null;
        $paciente->matricula_titular_pac = $matricula_titular_pac ?: null;
        $paciente->numero_rn_pac = $numero_rn_pac;

        $pacienteDao->create($paciente);
        $novoId = (int)$conn->lastInsertId();
        // Detecção de requisição feita dentro do modal global
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'paciente' => [
                    'id'   => $novoId,
                    'nome' => $nome_pac
                ]
            ]);
            exit;
        }
        header("Location: " . $BASE_URL . "pacientes");
    } else {

        $message->setMessage("Você precisa adicionar pelo menos: nome_pac do paciente!", "error", "back");
    }

    // =================================================================
    // INÍCIO DA CORREÇÃO 2 (Erro $message no Update)
    // =================================================================
} else if ($type === "update") {

    // O código de teste (linhas 159-166) que sobrescrevia $message foi REMOVIDO.

    $pacienteDao = new PacienteDAO($conn, $BASE_URL);
    // =================================================================
    // FIM DA CORREÇÃO 2
    // =================================================================

    // Receber os dados dos inputs
    $id_paciente = filter_input(INPUT_POST, "id_paciente");
    $nome_pac = filter_input(INPUT_POST, "nome_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $nome_pac = strtoupper($nome_pac);
    $nome_social_pac = filter_input(INPUT_POST, "nome_social_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $nome_social_pac = strtoupper($nome_social_pac);
    $endereco_pac = filter_input(INPUT_POST, "endereco_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $sexo_pac = filter_input(INPUT_POST, "sexo_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $data_nasc_pac = filter_input(INPUT_POST, "data_nasc_pac") ?: NULL;
    $email01_pac = filter_input(INPUT_POST, "email01_pac", FILTER_SANITIZE_EMAIL);
    $email01_pac = strtolower($email01_pac);

    $email02_pac = filter_input(INPUT_POST, "email02_pac", FILTER_SANITIZE_EMAIL);
    $email02_pac = strtolower($email02_pac);

    $cidade_pac = filter_input(INPUT_POST, "cidade_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $cpf_pac = filter_input(INPUT_POST, "cpf_pac");
    $somenteNumerosCPF = preg_replace('/[^0-9]/', '', $cpf_pac);

    $telefone01_pac = filter_input(INPUT_POST, "telefone01_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone01_pac = str_replace('-', '', $telefone01_pac);
    $telefone01_pac = str_replace('(', '', $telefone01_pac);
    $telefone01_pac = str_replace(') ', '', $telefone01_pac);

    $telefone02_pac = filter_input(INPUT_POST, "telefone02_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone02_pac = str_replace('-', '', $telefone02_pac);
    $telefone02_pac = str_replace('(', '', $telefone02_pac);
    $telefone02_pac = str_replace(') ', '', $telefone02_pac);

    $numero_pac = filter_input(INPUT_POST, "numero_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $bairro_pac = filter_input(INPUT_POST, "bairro_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $mae_pac = filter_input(INPUT_POST, "mae_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, "status");
    $obs_pac = filter_input(INPUT_POST, "obs_pac");
    $obs_pac = filter_input(INPUT_POST, "obs_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $matricula_pac = filter_input(INPUT_POST, "matricula_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $complemento_pac = filter_input(INPUT_POST, "complemento_pac");
    $complemento_pac = filter_input(INPUT_POST, "complemento_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $estado_pac = filter_input(INPUT_POST, "estado_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $cep_pac = filter_input(INPUT_POST, "cep_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $cep_pac = str_replace(') ', '', $cep_pac);
    $num_atendimento_pac = filter_input(INPUT_POST, "num_atendimento_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    $fk_seguradora_pac = filter_input(INPUT_POST, "fk_seguradora_pac") ?: 1;
    $fk_estipulante_pac = filter_input(INPUT_POST, "fk_estipulante_pac") ?: 1;

    // --- Novos campos (RN / mãe titular / matrícula titular)
    $recem_nascido_pac = filter_input(INPUT_POST, "recem_nascido_pac"); // 's' | 'n' | ''
    $mae_titular_pac = filter_input(INPUT_POST, "mae_titular_pac");   // 's' | 'n' | ''
    $matricula_titular_pac = filter_input(INPUT_POST, "matricula_titular_pac", FILTER_SANITIZE_SPECIAL_CHARS);
    // Número do RN (campo do form: numero_recem_nascido_pac) -> INT ou NULL
    $numero_recem_nascido_pac = filter_input(INPUT_POST, "numero_recem_nascido_pac", FILTER_SANITIZE_NUMBER_INT);
    $numero_rn_pac = null;

    if ($recem_nascido_pac === 's') {
        if ($numero_recem_nascido_pac !== null && $numero_recem_nascido_pac !== '') {
            $numero_rn_pac = (int) preg_replace('/\D/', '', $numero_recem_nascido_pac);
        } else {
            $numero_rn_pac = null;
        }
    } else {
        $numero_rn_pac = null;
    }


    // Normalização e validação condicional
    if ($recem_nascido_pac !== 's') {
        $mae_titular_pac = null;
        $matricula_titular_pac = null;
    } else {
        if ($mae_titular_pac === 'n') {
            if (!$matricula_titular_pac) {
                // Agora $message é um objeto e esta linha funciona.
                $message->setMessage("Informe a matrícula da titular (recém-nascido com mãe não titular).", "error", "back");
                exit;
            }
        } else {
            $matricula_titular_pac = null;
        }
    }


    $pacienteData = $pacienteDao->findByIdSeg($id_paciente);

    $pacienteData->id_paciente = $id_paciente;
    $pacienteData->nome_pac = $nome_pac;
    $pacienteData->nome_social_pac = $nome_social_pac;
    $pacienteData->endereco_pac = $endereco_pac;
    $pacienteData->email01_pac = $email01_pac;
    $pacienteData->email02_pac = $email02_pac;
    $pacienteData->cidade_pac = $cidade_pac;

    // Correção do CPF no Update (já estava correta no seu envio)
    $pacienteData->cpf_pac = empty($somenteNumerosCPF) ? null : $somenteNumerosCPF;

    $pacienteData->telefone01_pac = $telefone01_pac;
    $pacienteData->telefone02_pac = $telefone02_pac;
    $pacienteData->mae_pac = $mae_pac;
    $pacienteData->data_nasc_pac = $data_nasc_pac;
    $pacienteData->numero_pac = $numero_pac;
    $pacienteData->bairro_pac = $bairro_pac;
    $pacienteData->complemento_pac = $complemento_pac;
    $pacienteData->sexo_pac = $sexo_pac;
    $pacienteData->obs_pac = $obs_pac;
    $pacienteData->matricula_pac = $matricula_pac;
    $pacienteData->estado_pac = $estado_pac;
    $pacienteData->cep_pac = $cep_pac;
    $pacienteData->fk_seguradora_pac = $fk_seguradora_pac;
    $pacienteData->fk_estipulante_pac = $fk_estipulante_pac;
    $pacienteData->num_atendimento_pac = $num_atendimento_pac;
    $pacienteData->recem_nascido_pac = $recem_nascido_pac ?: null;
    $pacienteData->mae_titular_pac = $mae_titular_pac ?: null;
    $pacienteData->matricula_titular_pac = $matricula_titular_pac ?: null;
    $pacienteData->numero_rn_pac = $numero_rn_pac;


    $pacienteDao->update($pacienteData);

    header("Location: " . $BASE_URL . "pacientes");
}

if ($type === "delete") {
    // Recebe os dados do form
    $id_paciente = filter_input(INPUT_GET, "id_paciente");
    $pacienteDao = new PacienteDAO($conn, $BASE_URL);

    $paciente = $pacienteDao->findById($id_paciente);

    if ($paciente) {

        $pacienteDao->destroy($id_paciente);

        header("Location: " . $BASE_URL . "pacientes");
    } else {

        $message->setMessage("Informações inválidas!", "error", "index.php");
    }
}

if ($type === "delUpdate") {

    $pacienteDao = new pacienteDAO($conn, $BASE_URL);

    $id_paciente = filter_input(INPUT_POST, "id_paciente");
    $deletado_pac = 's';

    $pacienteData = $pacienteDao->findByIdSeg($id_paciente);

    $pacienteData->id_paciente = $id_paciente;
    $pacienteData->deletado_pac = $deletado_pac;

    $pacienteDao->deletarUpdate($pacienteData);

    header("Location: " . $BASE_URL . "pacientes");
}
