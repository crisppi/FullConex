<?php
// include_once("check_logado.php");

require_once("models/usuario.php");
require_once("models/usuario.php");
require_once("dao/usuarioDao.php");
require_once("dao/pacienteDao.php");
require_once("templates/header.php");

include_once("array_dados.php");

$user = new Usuario();
$usuarioDao = new UserDAO($conn, $BASE_URL);

?>
<div id="main-container" class="container">
    <div class="row">
        <h4 class="page-title">Por favor altere sua senha</h4>
        <form  action="<?= $BASE_URL ?>process_usuario.php" id="add-movie-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" class="form-control" id="id_usuario" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>">
            <input type="hidden" class="form-control" id="senha_usuario" name="senha_usuario" value="<?= $_SESSION['senha_user'] ?>">
            <input type="hidden" name="type" value="update-senha">
            <div class="form-group row">
            </div>
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="senha_user">Senha Atual</label>
                    <input type="password" class="form-control" id="senha_user" name="senha_user">
                    <div class="notif-input oculto" id="notif-erro">
                        Senha incorreta, tente novamente.
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="senha_user">Nova senha</label>
                    <input type="password" class="form-control" onkeyup="checkIn()" id="nova_senha_user" name="nova_senha_user">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="senha_user">Digite a senha novamente</label>
                    <input type="password" class="form-control" onblur="check()" id="nova_senha_user2">
                </div>
            </div>
            <div class="form-group col-sm-2">
                <input type="hidden" class="form-control" value="n" id="senha_default_user" name="senha_default_user">
                <div class="notif-input oculto" id="notif-input">
                    Senhas Diferentes!
                </div>
            </div>
            <button  type="submit" class="btn-sm btn-primary"><i style="font-size: 1rem;margin-right:5px;" name="type" value="edite"
            class="fa-solid fa-check edit-icon"></i>Atualizar</button>
    </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

<?php
include_once("templates/footer.php");
?>

<script src="js/senhas.js"></script>
<script>
    function checkSenha() {
        console.log("fasd")

        let senhaUsuarioBd = document.getElementById("senha_usuario"); //senha do banco de dados
        let senhaUsuarioLogin = document.getElementById("senha_user"); //senha digitada no formulario
        var divMsgErr = document.querySelector("#notif-erro");

        if (senhaUsuarioBd.value === senhaUsuarioLogin.value) {
            //console.log("senha igual")

        } else {
            divMsgErr.style.display = "block";
            senhaUsuarioLogin.value = "";
            senhaUsuarioLogin.focus();
            //console.log("senha diferente")
        }
    }
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

</html>
