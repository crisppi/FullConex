<?php
include_once("check_logado.php");

include_once("globals.php");

include_once("models/seguradora.php");
include_once("dao/seguradoraDao.php");
include_once("templates/header.php");

// Pegar o id do paceinte
$id_seguradora = filter_input(INPUT_GET, "id_seguradora", FILTER_SANITIZE_NUMBER_INT);

$seguradora;

$seguradoraDao = new seguradoraDAO($conn, $BASE_URL);

//Instanciar o metodo seguradora   
$seguradora = $seguradoraDao->findById($id_seguradora);
$telefone01_format = $telefone02_format = $cnpj_format = null;
$telefone01_format = $telefone02_format = $cnpj_format = null;
if (strlen($seguradora->telefone01_seg) > 0) {

    if (strlen($seguradora->telefone01_seg) == 10) {
        $telefone01_format = '(' .
            substr($seguradora->telefone01_seg, 0, 2) . ') ' .
            substr($seguradora->telefone01_seg, 2, 4) . '-' .
            substr($seguradora->telefone01_seg, 6, 9);
    } else {
        $telefone01_format = '(' .
            substr($seguradora->telefone01_seg, 0, 2) . ') ' .
            substr($seguradora->telefone01_seg, 2, 5) . '-' .
            substr($seguradora->telefone01_seg, 7, 9);
    }
} else {
    $telefone01_format = null;
};
if (strlen($seguradora->telefone02_seg) > 0) {

    if (strlen($seguradora->telefone02_seg) == 10) {
        $telefone02_format = '(' .
            substr($seguradora->telefone02_seg, 0, 2) . ') ' .
            substr($seguradora->telefone02_seg, 2, 4) . '-' .
            substr($seguradora->telefone02_seg, 6, 9);
    } else {
        $telefone02_format = '(' .
            substr($seguradora->telefone02_seg, 0, 2) . ') ' .
            substr($seguradora->telefone02_seg, 2, 5) . '-' .
            substr($seguradora->telefone02_seg, 7, 9);
    }
} else {
    $telefone02_format = null;
};

if (strlen($seguradora->cnpj_seg) > 0) {

    $cnpj_format =

        substr($seguradora->cnpj_seg, 0, 2) . '.' .
        substr($seguradora->cnpj_seg, 2, 3) . '.' .
        substr($seguradora->cnpj_seg, 5, 3) . '/' .
        substr($seguradora->cnpj_seg, 8, 4) . '-' .
        substr($seguradora->cnpj_seg, 12, 2);
} else {
    $telefone02_format = null;
};
$seguradora->cnpj_seg = $cnpj_format;
$seguradora->telefone01_seg = $telefone01_format;
$seguradora->telefone02_seg = $telefone02_format;

// header("Content-Type: logo_seg/png");

// Exibe a imagem
// echo $logo_seg;

?>
<script src="js/timeout.js"></script>

<div style="margin:15px" id="main-container">

    <h4>Dados da Seguradora Registro no: <?= $seguradora->id_seguradora ?></h4>
    <div class="card">
        <div class="card-header container" id="view-contact-container">
            <h6>Dados Empresa</h6>

            <?php if (strlen($seguradora->logo_seg) > 0) { ?>
            <img src="uploads/<?= $seguradora->logo_seg; ?>" height="80" width="80">
            <?php }; ?>

            <span style="font-size:large; font-weight:800" class="card-title bold">Seguradora:</span>
            <span style="font-size:large; font-weight:800"
                class="card-title bold"><?= $seguradora->seguradora_seg ?></span>
            <span style="margin:10px 0 10px 250px" class="card-title bold">CNPJ:</span>
            <span class="card-title bold"><?= $seguradora->cnpj_seg ?></span>
        </div>
        <div class="card-body">
            <h6>Dados Cadastrais</h6>
            <span class="card-text bold">Endereço:</span>
            <span class="card-text bold"><?= $seguradora->endereco_seg . "," ?></span>
            <span class="card-text bold"><?= $seguradora->numero_seg ?></span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Bairro:</span>
            <span class="card-text bold"><?= $seguradora->bairro_seg ?></span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Cidade:</span>
            <span class="card-text bold"><?= $seguradora->cidade_seg ?></span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Estado:</span>
            <span class="card-text bold"><?= $seguradora->estado_seg ?></span>
            <hr>
        </div>
        <div style="margin-top:-5px" class="card-body">
            <h6>Dados Contato</h6>
            <span class=" card-text bold">Email: </span>
            <span class=" card-text bold"><?= $seguradora->email01_seg ?></span>
            <span style="margin:10px 0 10px 25%" class=" card-text bold"> Email 02:</span>
            <span class=" card-text bold"><?= $seguradora->email02_seg ?></span>
            <br>
            <span class=" card-text bold">Telefone:</span>
            <span class=" card-text bold"><?= $seguradora->telefone01_seg ?></span>
            <span style="margin:10px 0 10px 25%" class=" card-text bold">Telefone:</span>
            <span class=" card-text bold"><?= $seguradora->telefone02_seg ?></span>
            <hr>
        </div>
        <div class="form-group row">
            <div style="margin-left:20px" id="id-confirmacao" class="btn_acoes visible">
                <p>Deseja deletar esta Seguradora: <?= "<em><b>" . $seguradora->seguradora_seg ?></em></b>?</p>
                <div class="form-group row">

                    <div class="form-group col-sm-2">
                        <form display="in-line" id="form_delete"
                            action="process_seguradora.php?id_seguradora=<?= $id_seguradora ?>" method="POST">
                            <input type="hidden" value="deletando">
                            <!-- <input type="hidden" name="type" value="delete"> -->
                            <input type="hidden" name="typeDel" value="delUpdate">
                            <input type="hidden" name="id_seguradora" value="<?= $seguradora->id_seguradora ?>">
                            <button class="btn btn-danger" value="deletar" type="submit" id="deletar-btn"
                                name="deletar">Deletar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    window.location = "<?= $BASE_URL ?>del_seguradora.php?id_seguradora=<?= $id_seguradora ?>";

};

function cancelar() {
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';
    console.log("chegou no cancelar");
    window.location = "<?= $BASE_URL ?>seguradoras";


};
src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<?php
require_once("templates/footer.php");
?>
