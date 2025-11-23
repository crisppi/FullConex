<?php
include_once("check_logado.php");

include_once("globals.php");

include_once("models/estipulante.php");
include_once("dao/estipulanteDao.php");
include_once("templates/header.php");

// Pegar o id do paceinte
$id_estipulante = filter_input(INPUT_GET, "id_estipulante", FILTER_SANITIZE_NUMBER_INT);

$estipulante;

$estipulanteDao = new estipulanteDAO($conn, $BASE_URL);

//Instanciar o metodo estipulante   
$estipulante = $estipulanteDao->findById($id_estipulante);
$telefone01_format = $telefone02_format = $cnpj_format = null;
$telefone01_format = $telefone02_format = $cnpj_format = null;
if (strlen($estipulante->telefone01_est) > 0) {

    if (strlen($estipulante->telefone01_est) == 10) {
        $telefone01_format = '(' .
            substr($estipulante->telefone01_est, 0, 2) . ') ' .
            substr($estipulante->telefone01_est, 2, 4) . '-' .
            substr($estipulante->telefone01_est, 6, 9);
    } else {
        $telefone01_format = '(' .
            substr($estipulante->telefone01_est, 0, 2) . ') ' .
            substr($estipulante->telefone01_est, 2, 5) . '-' .
            substr($estipulante->telefone01_est, 7, 9);
    }
} else {
    $telefone01_format = null;
};
if (strlen($estipulante->telefone02_est) > 0) {

    if (strlen($estipulante->telefone02_est) == 10) {
        $telefone02_format = '(' .
            substr($estipulante->telefone02_est, 0, 2) . ') ' .
            substr($estipulante->telefone02_est, 2, 4) . '-' .
            substr($estipulante->telefone02_est, 6, 9);
    } else {
        $telefone02_format = '(' .
            substr($estipulante->telefone02_est, 0, 2) . ') ' .
            substr($estipulante->telefone02_est, 2, 5) . '-' .
            substr($estipulante->telefone02_est, 7, 9);
    }
} else {
    $telefone02_format = null;
};

if (strlen($estipulante->cnpj_est) > 0) {

    $cnpj_format =

        substr($estipulante->cnpj_est, 0, 2) . '.' .
        substr($estipulante->cnpj_est, 2, 3) . '.' .
        substr($estipulante->cnpj_est, 5, 3) . '/' .
        substr($estipulante->cnpj_est, 8, 4) . '-' .
        substr($estipulante->cnpj_est, 12, 2);
} else {
    $telefone02_format = null;
};
$estipulante->cnpj_est = $cnpj_format;
$estipulante->telefone01_est = $telefone01_format;
$estipulante->telefone02_est = $telefone02_format;
?>
<div style="margin:15px" id="main-container">
    <script src="js/timeout.js"></script>

    <h4>Dados do estipulante Registro no:
        <?= $estipulante->id_estipulante ?>
    </h4>
    <div class="card">
        <div class="card-header container" id="view-contact-container">
            <h6>Dados Empresa</h6>
            <span class="card-title bold">Estipulante:</span>
            <span class="card-title bold">
                <?= $estipulante->nome_est ?>
            </span>
            <span style="margin:10px 0 10px 250px" class="card-title bold">CNPJ:</span>
            <span class="card-title bold">
                <?= $estipulante->cnpj_est ?>
            </span>
        </div>
        <div class="card-body">
            <h6>Dados Cadastrais</h6>
            <span class="card-text bold">Endereço:</span>
            <span class="card-text bold">
                <?= $estipulante->endereco_est . "," ?>
            </span>
            <span class="card-text bold">
                <?= $estipulante->numero_est ?>
            </span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Bairro:</span>
            <span class="card-text bold">
                <?= $estipulante->bairro_est ?>
            </span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Cidade:</span>
            <span class="card-text bold">
                <?= $estipulante->cidade_est ?>
            </span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Estado:</span>
            <span class="card-text bold">
                <?= $estipulante->estado_est ?>
            </span>
            <hr>
        </div>
        <div style="margin-top:-5px" class="card-body">
            <h6>Dados Contato</h6>
            <span class=" card-text bold">Email: </span>
            <span class=" card-text bold">
                <?= $estipulante->email01_est ?>
            </span>
            <span style="margin:10px 0 10px 25%" class=" card-text bold"> Email 02:</span>
            <span class=" card-text bold">
                <?= $estipulante->email02_est ?>
            </span>
            <br>
            <span class=" card-text bold">Telefone:</span>
            <span class=" card-text bold">
                <?= $estipulante->telefone01_est ?>
            </span>
            <span style="margin:10px 0 10px 25%" class=" card-text bold">Telefone:</span>
            <span class=" card-text bold">
                <?= $estipulante->telefone02_est ?>
            </span>
            <hr>
        </div>
        <div class="card-body">

            <div style="margin-left:20px" id="id-confirmacao" class="btn_acoes visible">
                <p>Deseja deletar este Estipulante?
                </p>
                <div class="form-group row">
                    <div class="form-group col-sm-2">
                        <form display="in-line" id="form_delete"
                            action="process_estipulante.php?id_estipulante=<?= $id_estipulante ?>" method="post">
                            <input type="hidden" value="deletando">
                            <!-- <input type="hidden" name="type" value="delete"> -->
                            <input type="hidden" name="typeDel" value="delUpdate">
                            <input type="hidden" name="id_estipulante" value="<?= $estipulante->id_estipulante ?>">
                            <button class="btn btn-danger" value="deletar" type="submit" id="deletar-btn"
                                name="deletar">Deletar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- INICIO SCRIPTS JAVASCRIPT -->
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
    window.location = "<?= $BASE_URL ?>del_estipulante.php?id_estipulante=<?= $id_estipulante ?>";

};

function cancelar() {
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';
    console.log("chegou no cancelar");
    window.location = "<?= $BASE_URL ?>estipulantes";


};
src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
