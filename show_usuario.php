<?php

use function PHPSTORM_META\type;

include_once("check_logado.php");

include_once("globals.php");

include_once("models/usuario.php");
include_once("dao/usuarioDao.php");
include_once("templates/header.php");

// Pegar o id do paceinte
$id_usuario = filter_input(INPUT_GET, "id_usuario", FILTER_SANITIZE_NUMBER_INT);

$usuario;

$usuarioDao = new userDAO($conn, $BASE_URL);

//Instanciar o metodo usuario   
$usuario = $usuarioDao->findById_user($id_usuario);
// print_r($usuario);

if (strlen($usuario->telefone01_user) > 0) {

    $telefone01_format = '(' .
        substr($usuario->telefone01_user, 0, 2) . ') ' .
        substr($usuario->telefone01_user, 2, 4) . '-' .
        substr($usuario->telefone01_user, 6, 9);
} else {
    $telefone01_format = null;
};
if (strlen($usuario->telefone02_user) > 0) {

    $telefone02_format = '(' .
        substr($usuario->telefone02_user, 0, 2) . ') ' .
        substr($usuario->telefone02_user, 2, 4) . '-' .
        substr($usuario->telefone02_user, 6, 9);
} else {
    $telefone02_format = null;
};
?>
<script src="js/timeout.js"></script>

<div style="margin:15px" id="main-container">
    <h4>Dados do usuário Registro no:
        <?= $usuario->id_usuario ?>
    </h4>
    <div class="card">
        <div class="card-header container-fluid" id="view-contact-container">
            <span style="font-size:large; font-weight:800" class="card-title bold">Nome:</span>
            <span class="card-title bold"><em><b>
                        <?= $usuario->usuario_user ?>
                    </b></em></span>
            <br>
        </div>

        <div class="card-body">
            <h6>Dados Cadastrais</h6>
            <span class="card-text bold">Endereço:</span>
            <span class="card-text bold">
                <?= $usuario->endereco_user . "," ?>
            </span>
            <span class="card-text bold">
                <?= $usuario->numero_user ?>
            </span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Bairro:</span>
            <span class="card-text bold">
                <?= $usuario->bairro_user ?>
            </span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Cidade:</span>
            <span class="card-text bold">
                <?= $usuario->cidade_user ?>
            </span>

            <hr>
        </div>
        <div style="margin-top:-5px" class="card-body">
            <h6>Dados Contato</h6>
            <span class=" card-text bold">Email: </span>
            <span class=" card-text bold">
                <?= $usuario->email_user ?>
            </span>
            <span style="margin:10px 0 10px 25%" class=" card-text bold"> Email 02:</span>
            <span class=" card-text bold">
                <?= $usuario->email02_user ?>
            </span>
            <br>
            <span class=" card-text bold">Telefone:</span>
            <span class=" card-text bold">
                <?= $telefone01_format ?>
            </span>
            <span style="margin:10px 0 10px 25%" class=" card-text bold">Telefone:</span>
            <span class=" card-text bold">
                <?= $telefone02_format ?>
            </span>
            <hr>
        </div>
        <hr>
        <div style="margin-left:20px" id="id-confirmacao" class="btn_acoes visible">

            <p style="font-weight: bold; font-size:1.0em; margin:15px 0;">Deseja inativar este usuário?</p>

            <!-- <button class="btn btn-success" type="button" id="btn-cancelar" name="cancelar">Cancelar</button> -->
            <button class="btn btn-danger" onclick=deletar() value="default" type="button" id="deletar-btn"
                name="deletar">Deletar</button>
        </div>
        <br>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

</div>

<script>
function apareceOpcoes() {
    $('#deletar-btn').val('nao');
    let mudancaStatus = ($('#deletar-btn').val())
    console.log(mudancaStatus);
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'block';
}

function deletar() {
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';
    window.location = "<?= $BASE_URL ?>del_usuario.php?id_usuario=<?= $id_usuario ?>";

};


$(function() {
    $('#btn-cancelar').on('click', function() {
        console.log("cancelou")
        // Volta para a listagem sem precisar de onclick inline
        window.location.href = '<?= $BASE_URL ?>list_usuario.php';
    });
});
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>