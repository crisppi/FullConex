<?php
include_once("check_logado.php");

require_once("globals.php");
require_once("db.php");
require_once("models/usuario.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");
?>
<?php
$message = new Message($BASE_URL);
$usuarioDao = new userDAO($conn, $BASE_URL);

$type = "delete";
//$type = filter_input(INPUT_POST, "type");

if ($type === "delete") {
    $id_usuario = filter_input(INPUT_GET, "id_usuario");

    $usuarioDao = new userDAO($conn, $BASE_URL);

    $usuario = $usuarioDao->findById_user($id_usuario);

    if ($usuario) {

        $usuarioDao->destroy($id_usuario);
        include_once("list_usuario.php");
    } else {

        $message->setMessage("Informações inválidas!", "error", "index.php");
    }
}
