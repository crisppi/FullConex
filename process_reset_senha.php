<?php
require_once("globals.php");
require_once("db.php");
require_once("dao/usuarioDao.php");

try {
    // Inicializa o DAO de usuário
    $usuarioDAO = new UserDAO($conn, $BASE_URL);

    // Obtém o ID do usuário via POST
    $id_user = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);

    if (!$id_user) {
        throw new Exception("ID do usuário inválido ou não fornecido.");
    }

    // Gera a senha padrão
    $senha_user = password_hash("1234", PASSWORD_DEFAULT);

    // Busca o usuário pelo ID
    $usuario = $usuarioDAO->findById_user($id_user);

    if (!$usuario) {
        throw new Exception("Usuário não encontrado para o ID fornecido: $id_user.");
    }

    // Atualiza os dados do usuário
    $usuario->senha_default_user = 's';
    $usuario->senha_user = $senha_user;

    $usuarioDAO->update($usuario);

    echo '1';
} catch (Exception $e) {
    // Log de erro
    error_log("Erro ao processar a requisição: " . $e->getMessage());

    // Retorno de erro para o cliente
    echo json_encode([
        "success" => false,
        "message" => "Ocorreu um erro: " . $e->getMessage()
    ]);
    http_response_code(500);
}
