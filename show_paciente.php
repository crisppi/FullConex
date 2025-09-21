<?php
include_once("check_logado.php");

include_once("globals.php");

include_once("models/paciente.php");
include_once("dao/pacienteDao.php");
include_once("templates/header.php");

// Pegar o id do paceinte
$id_paciente = filter_input(INPUT_GET, "id_paciente", FILTER_SANITIZE_NUMBER_INT);
$paciente;
$pacienteDao = new PacienteDAO($conn, $BASE_URL);

//Instanciar o metodo paciente   
$paciente = $pacienteDao->findById($id_paciente);
extract($paciente);
$telefone01_format = $telefone02_format = $cnpj_format = null;

if (strlen($paciente['0']['telefone01_pac']) > 0) {

    if (strlen($paciente['0']['telefone01_pac']) == 10) {
        $telefone01_format = '(' .
            substr($paciente['0']['telefone01_pac'], 0, 2) . ') ' .
            substr($paciente['0']['telefone01_pac'], 2, 4) . '-' .
            substr($paciente['0']['telefone01_pac'], 6, 9);
    } else {
        $telefone01_format = '(' .
            substr($paciente['0']['telefone01_pac'], 0, 2) . ') ' .
            substr($paciente['0']['telefone01_pac'], 2, 5) . '-' .
            substr($paciente['0']['telefone01_pac'], 7, 9);
    }
} else {
    $telefone01_format = null;
};
if (strlen($paciente['0']['telefone02_pac']) > 0) {

    if (strlen($paciente['0']['telefone02_pac']) == 10) {
        $telefone02_format = '(' .
            substr($paciente['0']['telefone02_pac'], 0, 2) . ') ' .
            substr($paciente['0']['telefone02_pac'], 2, 4) . '-' .
            substr($paciente['0']['telefone02_pac'], 6, 9);
    } else {
        $telefone02_format = '(' .
            substr($paciente['0']['telefone02_pac'], 0, 2) . ') ' .
            substr($paciente['0']['telefone02_pac'], 2, 5) . '-' .
            substr($paciente['0']['telefone02_pac'], 7, 9);
    }
} else {
    $telefone02_format = null;
};

$paciente['0']['telefone01_pac'] = $telefone01_format;
$paciente['0']['telefone02_pac'] = $telefone02_format;

$cpf_pac = $paciente['0']['cpf_pac'];
$bloco_1 = substr($cpf_pac, 0, 3);
$bloco_2 = substr($cpf_pac, 3, 3);
$bloco_3 = substr($cpf_pac, 6, 3);
$dig_verificador = substr($cpf_pac, -2);
$cpf_formatado = $bloco_1 . "." . $bloco_2 . "." . $bloco_3 . "-" . $dig_verificador;
?>
<script src="js/timeout.js"></script>

<div style="margin:15px" id="main-container">
    <h4 style="margin-top:20px">Dados do paciente Registro no:
        <?= $id_paciente ?>
    </h4>
    <div class="card">
        <h6 style="margin:10px 0 10px 20px">Dados pessoais</h6>
        <div class="card-header container-fluid" id="view-contact-container">
            <span style="font-size:large; font-weight:600" class="card-title bold">Nome:</span>
            <span style="font-size:large; font-weight:600" class="card-title bold">
                <?= $paciente['0']['nome_pac'] ?>
            </span>
            <span style="margin-left:200px" class="card-title bold">Nome da Mãe:</span>
            <span class="card-title bold">
                <?= $paciente['0']['mae_pac'] ?>
            </span>
            <span style="margin-left:200px" class="card-title bold">CPF:</span>
            <span class="card-title bold">
                <?= $cpf_formatado ?>
            </span>
            <br>
            <span class="card-title bold">Seguradora:</span>
            <span class="card-title bold">
                <?= $paciente['0']['seguradora_seg'] ?>
            </span>
        </div>
        <div class="card-body">
            <h6>Dados cadastrais</h6>
            <span class=" card-text bold">Endereço: </span>
            <span class=" card-text bold">
                <?= $paciente['0']['endereco_pac'] ?>
            </span>
            <span class=" card-text bold">, </span>
            <span class=" card-text bold">
                <?= $paciente['0']['numero_pac'] ?>
            </span>
            <br>
            <span class=" card-text bold">Bairro: </span>
            <span class=" card-text bold">
                <?= $paciente['0']['bairro_pac'] ?>
            </span>
            <span style="margin-left:200px" class="card-text bold">Cidade: </span>
            <span class="card-text bold">
                <?= $paciente['0']['cidade_pac'] ?>
            </span>
            <span style="margin-left:200px" class="card-text bold">Estado: </span>
            <span class="card-text bold">
                <?= $paciente['0']['estado_pac'] ?>
            </span>
        </div>
        <hr>
        <div class="card-body">
            <h6>Contatos</h6>
            <span class=" card-text bold">Email: </span>
            <span class=" card-text bold">
                <?= $paciente['0']['email01_pac'] ?>
            </span>
            <span style="margin-left:200px" class=" card-text bold">Email 02: </span>
            <span class=" card-text bold">
                <?= $paciente['0']['email02_pac'] ?>
            </span>
            <br>
            <span class=" card-text bold">Telefone: </span>
            <span class=" card-text bold">
                <?= $paciente['0']['telefone01_pac'] ?>
            </span>
            <span style="margin-left:200px" class=" card-text bold">Tel 02: </span>
            <span class=" card-text bold">
                <?= $paciente['0']['telefone02_pac'] ?>
            </span>
        </div>
        <hr>
        <div class="card-body">
            <h6>Empresa</h6>
            <span class=" card-text bold">Seguradora: </span>
            <span class=" card-text bold">
                <?= $paciente['0']['seguradora_seg'] ?>
            </span>
            <br>
            <span class=" card-text bold">Estipulante: </span>
            <span class=" card-text bold">
                <?= $paciente['0']['nome_est'] ?>
            </span>
            <hr>
        </div>
        <div style="margin-left:20px" id="id-confirmacao" class="btn_acoes visible">

            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <form display="in-line" id="form_delete"
                        action="process_paciente.php?id_paciente=<?= $id_paciente ?>" method="POST">
                        <input type="hidden" value="deletando">
                        <!-- <input type="hidden" name="type" value="delete"> -->
                        <input type="hidden" name="typeDel" value="delUpdate">
                        <input type="hidden" name="id_paciente" value="<?= $paciente['0']['id_paciente'] ?>">

                        <button class="btn btn-danger" value="deletar" type="submit" id="deletar-btn"
                            name="deletar">Deletar</button>

                    </form>
                    <br>
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

    btnDeletar = (document.getElementById('deletar-btn').value);

    idAcoes.style.display = 'none';

    window.location = "<?= $BASE_URL ?>dele_paciente.php?id_paciente=<?= $id_paciente ?>";
};

function cancelar() {
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';
    window.location = "<?= $BASE_URL ?>list_paciente.php?>";

};
src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
</script>
<script src="js/apagarModal.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>