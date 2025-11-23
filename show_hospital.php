<?php
include_once("check_logado.php");

include_once("globals.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");
include_once("templates/header.php");

// Pegar o id do paceinte
$id_hospital = filter_input(INPUT_GET, "id_hospital", FILTER_SANITIZE_NUMBER_INT);

$hospital;

$hospitalDao = new hospitalDAO($conn, $BASE_URL);

//Instanciar o metodo hospital   
$hospital = $hospitalDao->findById($id_hospital);
$telefone01_format = $telefone02_format = $cnpj_format = null;
if (strlen($hospital->telefone01_hosp) > 0) {

    if (strlen($hospital->telefone01_hosp) == 10) {
        $telefone01_format = '(' .
            substr($hospital->telefone01_hosp, 0, 2) . ') ' .
            substr($hospital->telefone01_hosp, 2, 4) . '-' .
            substr($hospital->telefone01_hosp, 6, 9);
    } else {
        $telefone01_format = '(' .
            substr($hospital->telefone01_hosp, 0, 2) . ') ' .
            substr($hospital->telefone01_hosp, 2, 5) . '-' .
            substr($hospital->telefone01_hosp, 7, 9);
    }
} else {
    $telefone01_format = null;
};
if (strlen($hospital->telefone02_hosp) > 0) {

    if (strlen($hospital->telefone02_hosp) == 10) {
        $telefone02_format = '(' .
            substr($hospital->telefone02_hosp, 0, 2) . ') ' .
            substr($hospital->telefone02_hosp, 2, 4) . '-' .
            substr($hospital->telefone02_hosp, 6, 9);
    } else {
        $telefone02_format = '(' .
            substr($hospital->telefone02_hosp, 0, 2) . ') ' .
            substr($hospital->telefone02_hosp, 2, 5) . '-' .
            substr($hospital->telefone02_hosp, 7, 9);
    }
} else {
    $telefone02_format = null;
};

if (strlen($hospital->cnpj_hosp) > 0) {

    $cnpj_format =

        substr($hospital->cnpj_hosp, 0, 2) . '.' .
        substr($hospital->cnpj_hosp, 2, 3) . '.' .
        substr($hospital->cnpj_hosp, 5, 3) . '/' .
        substr($hospital->cnpj_hosp, 8, 4) . '-' .
        substr($hospital->cnpj_hosp, 12, 2);
} else {
    $telefone02_format = null;
};

$hospital->cnpj_hosp = $cnpj_format;
$hospital->telefone01_hosp = $telefone01_format;
$hospital->telefone02_hosp = $telefone02_format;
?>
<script src="js/timeout.js"></script>

<div style="margin:15px" id="main-container">
    <h4>Dados do Hospital Registro no: <?= $hospital->id_hospital ?></h4>
    <div class="card">
        <div class="card-header container" id="view-contact-container">
            <!-- <h6>Dados Hospital</h6> -->

            <?php
            // monta o caminho físico até o arquivo
            $logoFile = __DIR__ . '/uploads/' . $hospital->logo_hosp;

            // só exibe se tiver nome e o arquivo existir
            if (!empty($hospital->logo_hosp) && file_exists($logoFile)):
            ?>
            <img src="uploads/<?= htmlspecialchars($hospital->logo_hosp, ENT_QUOTES) ?>" height="80" width="80"
                alt="Logo do Hospital">
            <?php
            endif;
            ?>

            <span class="card-title bold">Hospital:</span>
            <span class="card-title bold"><?= $hospital->nome_hosp ?></span>
            <span style="margin:10px 0 10px 250px" class="card-title bold">CNPJ:</span>
            <span class="card-title bold"><?= $cnpj_format ?></span>
        </div>
        <div class="card-body">
            <h6>Dados Cadastrais</h6>
            <span class="card-text bold">Endereço:</span>
            <span class="card-text bold"><?= $hospital->endereco_hosp . "," ?></span>
            <span class="card-text bold"><?= $hospital->numero_hosp ?></span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Bairro:</span>
            <span class="card-text bold"><?= $hospital->bairro_hosp ?></span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Cidade:</span>
            <span class="card-text bold"><?= $hospital->cidade_hosp ?></span>
            <span style="margin:10px 0 10px 25%" class="card-text bold">Estado:</span>
            <span class="card-text bold"><?= $hospital->estado_hosp ?></span>
            <hr>
        </div>
        <div style="margin-top:-5px" class="card-body">
            <h6>Dados Contato</h6>
            <span class=" card-text bold">Email: </span>
            <span class=" card-text bold"><?= $hospital->email01_hosp ?></span>
            <span style="margin:10px 0 10px 25%" class=" card-text bold"> Email 02:</span>
            <span class=" card-text bold"><?= $hospital->email02_hosp ?></span>
            <br>
            <span class=" card-text bold">Telefone:</span>
            <span class=" card-text bold"><?= $hospital->telefone01_hosp ?></span>
            <span style="margin:10px 0 10px 25%" class=" card-text bold">Telefone:</span>
            <span class=" card-text bold"><?= $hospital->telefone02_hosp ?></span>
            <hr>
        </div>
        <div class="card-body">
            <div style="margin-left:20px" id="id-confirmacao" class="btn_acoes visible">
                <div class="form-group row">
                    <div class="form-group col-sm-2">
                        <form display="in-line" id="form_delete"
                            action="process_hospital.php?id_hospital=<?= $id_hospital ?>" method="POST">
                            <input type="hidden" value="deletando">
                            <!-- <input type="hidden" name="type" value="delete"> -->
                            <input type="hidden" name="typeDel" value="delUpdate">

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
    window.location = "<?= $BASE_URL ?>del_hospital.php?id_hospital=<?= $id_hospital ?>";

};

function cancelar() {
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';
    window.location = "<?= $BASE_URL ?>hospitais";
};
src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
