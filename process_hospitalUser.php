<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require_once("check_logado.php");          // se seu projeto usa para sessão
require_once("globals.php");               // onde estiver $BASE_URL (ajuste se necessário)
require_once("db.php");                    // onde estiver $conn (ajuste se necessário)

require_once("models/hospitalUser.php");
require_once("dao/hospitalUserDao.php");

// Instancia o DAO
$hospitalUserDao = new hospitalUserDAO($conn, $BASE_URL);

// Coleta e normaliza os dados do POST
$type             = filter_input(INPUT_POST, 'type');
$id_hospitalUser  = filter_input(INPUT_POST, 'id_hospitalUser', FILTER_VALIDATE_INT);
$fk_usuario_hosp  = filter_input(INPUT_POST, 'fk_usuario_hosp', FILTER_VALIDATE_INT);
$fk_hospital_user = filter_input(INPUT_POST, 'fk_hospital_user', FILTER_VALIDATE_INT);

// Garantia de inteiros (0 quando null)
$id_hospitalUser  = $id_hospitalUser  ?: 0;
$fk_usuario_hosp  = $fk_usuario_hosp  ?: 0;
$fk_hospital_user = $fk_hospital_user ?: 0;

// Validação simples
if (!in_array($type, ['create', 'update'], true)) {
    // fallback: se não vier 'type', decide por id > 0
    $type = $id_hospitalUser > 0 ? 'update' : 'create';
}

try {
    if ($type === 'create') {

        // cria o objeto do modelo
        $hu = new hospitalUser();
        $hu->fk_usuario_hosp  = (int)$fk_usuario_hosp;
        $hu->fk_hospital_user = (int)$fk_hospital_user;

        // valida mínimos
        if ($hu->fk_usuario_hosp <= 0 || $hu->fk_hospital_user <= 0) {
            throw new RuntimeException("Selecione um usuário e um hospital válidos.");
        }

        // persiste
        $hospitalUserDao->create($hu);
        // o create do DAO já faz a mensagem/redirect
        include_once('list_hospitalUser.php');
    } elseif ($type === 'update') {

        // cria o objeto do modelo
        $hu = new hospitalUser();
        $hu->id_hospitalUser  = (int)$id_hospitalUser;
        $hu->fk_usuario_hosp  = (int)$fk_usuario_hosp;
        $hu->fk_hospital_user = (int)$fk_hospital_user;

        if ($hu->id_hospitalUser <= 0) {
            throw new RuntimeException("ID do vínculo inválido para atualizar.");
        }
        if ($hu->fk_usuario_hosp <= 0 || $hu->fk_hospital_user <= 0) {
            throw new RuntimeException("Selecione um usuário e um hospital válidos.");
        }

        // persiste
        $hospitalUserDao->update($hu);
        // o update do DAO já faz a mensagem/redirect
        include_once('list_hospitalUser.php');
    }
} catch (Throwable $e) {
    // usa o sistema de mensagens já existente
    $hospitalUserDao->message->setMessage(
        "Erro ao processar: " . $e->getMessage(),
        "error",
        "list_hospitalUser.php"
    );
    exit;
}