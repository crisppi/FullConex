<?php
include_once("check_logado.php");

require_once("models/usuario.php");
require_once("models/estipulante.php");
require_once("dao/usuarioDao.php");
require_once("dao/estipulanteDao.php");
require_once("templates/header.php");

include_once("array_dados.php");

$user = new Estipulante();
$userDao = new UserDAO($conn, $BASE_URL);
$estipulanteDao = new estipulanteDAO($conn, $BASE_URL);

// Receber id do estipulante
$id_estipulante = filter_input(INPUT_GET, "id_estipulante");

$estipulante = $estipulanteDao->findById($id_estipulante);
$estado_selecionado = $estipulante->estado_est;

print_r($estipulante);

$cnpj_est = $estipulante->cnpj_est;

// Formatação CNPJ
if (!empty($cnpj_est)) {
    $cnpj_est = preg_replace("/\D/", '', $cnpj_est);
    if (strlen($cnpj_est) === 14) {
        $bloco_1 = substr($cnpj_est, 0, 2);
        $bloco_2 = substr($cnpj_est, 2, 3);
        $bloco_3 = substr($cnpj_est, 5, 3);
        $bloco_4 = substr($cnpj_est, 8, 4);
        $dig_verificador = substr($cnpj_est, -2);
        $cnpj_formatado = $bloco_1 . "." . $bloco_2 . "." . $bloco_3 . "/" . $bloco_4 . "-" . $dig_verificador;
    } else {
        $cnpj_formatado = '';
    }
} else {
    $cnpj_formatado = '';
}

$telefone01_est = $estipulante->telefone01_est;
$telefone02_est = $estipulante->telefone02_est;

if (!empty($telefone01_est)) {
    $telefone01_est = preg_replace("/\D/", '', $telefone01_est);
    if (strlen($telefone01_est) === 10) {
        $bloco_1 = substr($telefone01_est, 0, 2);
        $bloco_2 = substr($telefone01_est, 2, 4);
        $bloco_3 = substr($telefone01_est, 6, 4);
        $telefone01_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } elseif (strlen($telefone01_est) === 11) {
        $bloco_1 = substr($telefone01_est, 0, 2);
        $bloco_2 = substr($telefone01_est, 2, 5);
        $bloco_3 = substr($telefone01_est, 7, 4);
        $telefone01_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } else {
        $telefone01_formatado = '';
    }
} else {
    $telefone01_formatado = '';
}

if (!empty($telefone02_est)) {
    $telefone02_est = preg_replace("/\D/", '', $telefone02_est);
    if (strlen($telefone02_est) === 10) {
        $bloco_1 = substr($telefone02_est, 0, 2);
        $bloco_2 = substr($telefone02_est, 2, 4);
        $bloco_3 = substr($telefone02_est, 6, 4);
        $telefone02_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } elseif (strlen($telefone02_est) === 11) {
        $bloco_1 = substr($telefone02_est, 0, 2);
        $bloco_2 = substr($telefone02_est, 2, 5);
        $bloco_3 = substr($telefone02_est, 7, 4);
        $telefone02_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } else {
        $telefone02_formatado = '';
    }
} else {
    $telefone02_formatado = '';
}

?>
<script src="css/ocultar.css"></script>

<!-- Formulário de Edição -->
<div id="main-container" class="container-fluid">
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: 33%;" aria-valuenow="33"
            aria-valuemin="0" aria-valuemax="100">Etapa 1 de 3</div>
    </div>

    <form action="<?= $BASE_URL ?>process_estipulante.php" id="multi-step-form" method="POST"
        enctype="multipart/form-data" class="needs-validation" novalidate>

        <input type="hidden" name="type" value="update">
        <input type="hidden" name="id_estipulante" value="<?= $estipulante->id_estipulante ?>">

        <!-- Step 1: Informações Básicas -->
        <div id="step-1" class="step">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="cnpj_est">CNPJ</label>
                    <input type="text" oninput="mascara(this, 'cnpj')" class="form-control" id="cnpj_est"
                        name="cnpj_est" value="<?= $cnpj_formatado ?>" placeholder="00.000.000/0000-00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_est">Estipulante</label>
                    <input type="text" class="form-control" id="nome_est" name="nome_est"
                        value="<?= $estipulante->nome_est ?>" placeholder="Nome do estipulante">
                </div>
            </div>
            <hr>


            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-primary" id="next-1" onclick="nextStep(2)">
                    Próximo <i class="fas fa-arrow-right"></i>
                </button>
                <!-- Div de confirmação, oculta inicialmente -->
                <div id="confirm-delete-div" style="font-weight: bold" class="oculto">

                    <div class="d-flex flex-column align-items-center px-3 my-3"
                        style="background-color: #f1f1f1; border-radius: 10px; border: 1px solid #ddd; display: none;">

                        <!-- Texto centralizado acima dos botões -->
                        <p style="font-weight: bold;" class="mb-2 text-center">Confirma Deletar?</p>

                        <!-- Botões de confirmação e cancelamento -->
                        <div class="d-flex justify-content-center mb-3">
                            <button type="button" class="btn btn-success mx-2" onclick="confirmAction()">
                                Sim <i class="fas fa-check"></i>
                            </button>

                            <button type="button" class="btn btn-danger mx-2" onclick="hideConfirmDelete()">
                                Não <i class="fas fa-ban"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-danger float-end" onclick="showConfirmDelete()">
                    Deletar <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Step 2: Endereço -->
        <div id="step-2" class="step" style="display:none;">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="cep_est">CEP</label>
                    <input type="text" oninput="mascara(this, 'cep')" onkeyup="consultarCEP(this, 'est')"
                        class="form-control" id="cep_est" name="cep_est" value="<?= $estipulante->cep_est ?>"
                        placeholder="00000-000">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="endereco_est">Endereço</label>
                    <input type="text" class="form-control" id="endereco_est" name="endereco_est"
                        value="<?= $estipulante->endereco_est ?>" placeholder="Rua, avenida...">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="bairro_est">Bairro</label>
                    <input readonly type="text" class="form-control" id="bairro_est" name="bairro_est"
                        value="<?= $estipulante->bairro_est ?>" placeholder="Bairro">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="cidade_est">Cidade</label>
                    <input readonly type="text" class="form-control" id="cidade_est" name="cidade_est"
                        value="<?= $estipulante->cidade_est ?>" placeholder="Cidade">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="estado_est">Estado</label>
                    <input readonly value="<?= $estipulante->estado_est ?>" class="form-control" id="estado_est" name="estado_est">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="numero_est">Número</label>
                    <input type="text" class="form-control" id="numero_est" name="numero_est"
                        value="<?= $estipulante->numero_est ?>" placeholder="Número">
                </div>
            </div>
            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
            <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                Próximo <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Step 3: Contato e Finalização -->
        <div id="step-3" class="step" style="display:none;">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="email01_est">Email Principal</label>
                    <input type="email" class="form-control" id="email01_est" name="email01_est"
                        value="<?= $estipulante->email01_est ?>" placeholder="exemplo@dominio.com">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="email02_est">Email Alternativo</label>
                    <input type="email" class="form-control" id="email02_est" name="email02_est"
                        value="<?= $estipulante->email02_est ?>" placeholder="exemplo@dominio.com">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone01_est">Telefone</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone01_est" name="telefone01_est" value="<?= $telefone01_formatado ?>"
                        placeholder="(00) 0000-0000">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone02_est">Celular</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone02_est" name="telefone02_est" value="<?= $telefone02_formatado ?>"
                        placeholder="(00) 00000-0000">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_contato_est">Nome do Contato</label>
                    <input type="text" class="form-control" id="nome_contato_est" name="nome_contato_est"
                        value="<?= $estipulante->nome_contato_est ?>" placeholder="Nome do contato">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_responsavel_est">Nome do Responsável</label>
                    <input type="text" class="form-control" id="nome_responsavel_est" name="nome_responsavel_est"
                        value="<?= $estipulante->nome_responsavel_est ?>" placeholder="Nome do responsável">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="logo_est">Logo</label>
                    <input type="file" class="form-control" name="logo_est" id="logo_est"
                        accept="image/png, image/jpeg">
                    <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
                </div>
            </div>
            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> Atualizar
            </button>
        </div>
        <script>
        // Função para mostrar a div de confirmação
        function showConfirmDelete() {
            const confirmDiv = document.getElementById("confirm-delete-div");
            if (confirmDiv) {
                confirmDiv.style.display = "flex";
            }
        }

        // Função para ocultar a div de confirmação
        function hideConfirmDelete() {
            const confirmDiv = document.getElementById("confirm-delete-div");
            if (confirmDiv) {
                confirmDiv.style.display = "none";
            }
        }

        // Função para confirmar a exclusão
        function confirmAction() {
            hideConfirmDelete(); // Oculta a div de confirmação

            // Inicia o processo de exclusão
            const form = document.getElementById("multi-step-form");
            form.action = "<?= $BASE_URL ?>process_estipulante.php";

            // Adiciona campos ocultos para o processo de deletar
            const inputType = document.createElement("input");
            inputType.type = "hidden";
            inputType.name = "type";
            inputType.value = "delUpdate";
            form.appendChild(inputType);

            const inputDeleted = document.createElement("input");
            inputDeleted.type = "hidden";
            inputDeleted.name = "deletado_est";
            inputDeleted.value = "s";
            form.appendChild(inputDeleted);

            // Envia o formulário
            form.submit();
        }
        </script>
    </form>
</div>

<script>
function mascara(i, t) {
    var v = i.value;
    if (isNaN(v[v.length - 1])) {
        i.value = v.substring(0, v.length - 1);
        return;
    }
    if (t == "cnpj") {
        i.setAttribute("maxlength", "18");
        if (v.length == 2 || v.length == 6) i.value += ".";
        if (v.length == 10) i.value += "/";
        if (v.length == 15) i.value += "-";
    }
    if (t == "cep") {
        i.setAttribute("maxlength", "9");
        if (v.length == 5) i.value += "-";
    }
}

function mascaraTelefone(event) {
    let tecla = event.key;
    let telefone = event.target.value.replace(/\D+/g, "");
    if (/^[0-9]$/i.test(tecla)) {
        telefone = telefone + tecla;
        let tamanho = telefone.length;
        if (tamanho >= 12) {
            return false;
        }
        if (tamanho > 10) {
            telefone = telefone.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
        } else if (tamanho > 5) {
            telefone = telefone.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
        } else if (tamanho > 2) {
            telefone = telefone.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
        } else {
            telefone = telefone.replace(/^(\d*)/, "($1");
        }
        event.target.value = telefone;
    }
    if (!["Backspace", "Delete"].includes(tecla)) {
        return false;
    }
}

imagem.addEventListener("change", function(e) {
    if (imagem.files[0].size > (1024 * 1024 * 2)) {
        var notifImagem = document.querySelector("#notifImagem");
        notifImagem.style.display = "block";
        imagem.value = '';
    }
})

function novoArquivo() {
    notifImagem.style.display = "none";
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<?php
include_once("templates/footer.php");
?>