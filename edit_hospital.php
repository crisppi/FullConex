<?php
include_once("check_logado.php");

require_once("models/usuario.php");
require_once("models/hospital.php");
require_once("dao/usuarioDao.php");
require_once("dao/hospitalDao.php");
require_once("templates/header.php");

$user = new hospital();
$userDao = new UserDAO($conn, $BASE_URL);
$hospitalDao = new hospitalDAO($conn, $BASE_URL);

// Receber id do usuário
$id_hospital = filter_input(INPUT_GET, "id_hospital");

$hospital = $hospitalDao->findById($id_hospital);
include_once("array_dados.php");


$cep_hosp = $hospital->cep_hosp;

if (!empty($cep_hosp)) {
    // Remove qualquer caractere não numérico (se necessário)
    $cep_hosp = preg_replace("/\D/", '', $cep_hosp);

    // Verifica se o CEP tem 8 dígitos
    if (strlen($cep_hosp) === 8) {
        // Formatação para CEP: XXXXX-XXX
        $bloco_1 = substr($cep_hosp, 0, 5); // Primeira parte do CEP
        $bloco_2 = substr($cep_hosp, 5, 3); // hospunda parte do CEP
        $cep_formatado = "$bloco_1-$bloco_2";
    } else {
        $cep_formatado = ''; // Caso o CEP não tenha 8 dígitos
    }
} else {
    $cep_formatado = ''; // Não aplica formatação se o valor estiver vazio
}

$cnpj_hosp = $hospital->cnpj_hosp;

if (!empty($cnpj_hosp)) {
    // Remove qualquer caractere não numérico (se necessário)
    $cnpj_hosp = preg_replace("/\D/", '', $cnpj_hosp);

    // Verifica se o CNPJ tem 14 dígitos
    if (strlen($cnpj_hosp) === 14) {
        // Formatação para CNPJ: XX.XXX.XXX/XXXX-XX
        $bloco_1 = substr($cnpj_hosp, 0, 2);
        $bloco_2 = substr($cnpj_hosp, 2, 3);
        $bloco_3 = substr($cnpj_hosp, 5, 3);
        $bloco_4 = substr($cnpj_hosp, 8, 4);
        $dig_verificador = substr($cnpj_hosp, -2);
        $cnpj_formatado = $bloco_1 . "." . $bloco_2 . "." . $bloco_3 . "/" . $bloco_4 . "-" . $dig_verificador;
    } else {
        $cnpj_formatado = ''; // Caso o CNPJ não tenha 14 dígitos
    }
} else {
    $cnpj_formatado = ''; // Não aplica formatação se o valor estiver vazio
}

$telefone01_hosp = $hospital->telefone01_hosp;
$telefone02_hosp = $hospital->telefone02_hosp;

if (!empty($telefone01_hosp)) {
    // Remove qualquer caractere não numérico (se necessário)
    $telefone01_hosp = preg_replace("/\D/", '', $telefone01_hosp);

    // Verifica se o telefone tem 10 ou 11 dígitos
    if (strlen($telefone01_hosp) === 10) {
        // Formatação para telefone fixo: (00) 0000-0000
        $bloco_1 = substr($telefone01_hosp, 0, 2); // DDD
        $bloco_2 = substr($telefone01_hosp, 2, 4); // Primeira parte do número
        $bloco_3 = substr($telefone01_hosp, 6, 4); // hospunda parte do número
        $telefone01_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } elseif (strlen($telefone01_hosp) === 11) {
        // Formatação para celular: (00) 00000-0000
        $bloco_1 = substr($telefone01_hosp, 0, 2); // DDD
        $bloco_2 = substr($telefone01_hosp, 2, 5); // Primeira parte do número (5 dígitos)
        $bloco_3 = substr($telefone01_hosp, 7, 4); // hospunda parte do número
        $telefone01_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } else {
        $telefone01_formatado = ''; // Caso o telefone não tenha 10 ou 11 dígitos
    }
} else {
    $telefone01_formatado = ''; // Não aplica formatação se o valor estiver vazio
}

// Repetir a lógica para o hospundo telefone
if (!empty($telefone02_hosp)) {
    $telefone02_hosp = preg_replace("/\D/", '', $telefone02_hosp);

    if (strlen($telefone02_hosp) === 10) {
        $bloco_1 = substr($telefone02_hosp, 0, 2);
        $bloco_2 = substr($telefone02_hosp, 2, 4);
        $bloco_3 = substr($telefone02_hosp, 6, 4);
        $telefone02_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } elseif (strlen($telefone02_hosp) === 11) {
        $bloco_1 = substr($telefone02_hosp, 0, 2);
        $bloco_2 = substr($telefone02_hosp, 2, 5);
        $bloco_3 = substr($telefone02_hosp, 7, 4);
        $telefone02_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } else {
        $telefone02_formatado = '';
    }
} else {
    $telefone02_formatado = '';
}
?>
<script src="css/ocultar.css"></script>

<div class="container-fluid" id="main-container">
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: 25%;" aria-valuenow="25"
            aria-valuemin="0" aria-valuemax="100">Etapa 1 de 4</div>
    </div>

    <form action="<?= $BASE_URL ?>process_hospital.php" id="multi-step-form" method="POST"
        enctype="multipart/form-data">
        <input type="hidden" name="type" value="update">
        <input type="hidden" class="form-control" id="id_hospital" value="<?= $hospital->id_hospital ?>"
            name="id_hospital">

        <!-- Step 1: Informações Básicas -->
        <div id="step-1" class="step">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="cnpj_hosp">CNPJ</label>
                    <input type="text" oninput="mascara(this, 'cnpj')" value="<?= $cnpj_formatado ?>"
                        class="form-control" id="cnpj_hosp" name="cnpj_hosp">
                    <div class="invalid-feedback">Por favor, insira um CNPJ válido.</div>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_hosp">Nome do Hospital</label>
                    <input type="text" class="form-control" id="nome_hosp" value="<?= $hospital->nome_hosp ?>"
                        name="nome_hosp">
                    <div class="invalid-feedback">Por favor, insira o nome do hospital.</div>
                </div>
            </div>
            <hr>

            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-primary" onclick="nextStep2(2)">
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
        <!-- Step 2: Endereço e Localização -->
        <div id="step-2" class="step" style="display:none;">
            <div class="row">
                <div class="form-group col-md-4 mb-3">
                    <label for="cep_hosp">CEP</label>
                    <input type="text" value="<?= $cep_formatado ?>" onkeyup="consultarCEP(this, 'hosp')"
                        class="form-control" id="cep_hosp" name="cep_hosp">
                </div>
                <div class="form-group col-md-8 mb-3">
                    <label for="endereco_hosp">Endereço</label>
                    <input readonly type="text" class="form-control" value="<?= $hospital->endereco_hosp ?>"
                        id="endereco_hosp" name="endereco_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="bairro_hosp">Bairro</label>
                    <input readonly type="text" class="form-control" id="bairro_hosp"
                        value="<?= $hospital->bairro_hosp ?>" name="bairro_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="cidade_hosp">Cidade</label>
                    <input readonly type="text" class="form-control" value="<?= $hospital->cidade_hosp ?>"
                        id="cidade_hosp" name="cidade_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="estado_hosp">Estado</label>
                    <input readonly class="form-control" id="estado_hosp" name="estado_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="numero_hosp">Número</label>
                    <input type="text" class="form-control" id="numero_hosp" name="numero_hosp"
                        value="<?= $hospital->numero_hosp ?>">
                </div>
            </div>

            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep2(1)">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
            <button type="button" class="btn btn-primary" onclick="nextStep2(3)">
                Próximo <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Step 3: Contato -->
        <div id="step-3" class="step" style="display:none;">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="email01_hosp">Email Principal</label>
                    <input type="email" class="form-control" value="<?= $hospital->email01_hosp ?>" id="email01_hosp"
                        name="email01_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="email02_hosp">Email Alternativo</label>
                    <input type="email" class="form-control" value="<?= $hospital->email02_hosp ?>" id="email02_hosp"
                        name="email02_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone01_hosp">Telefone Principal</label>
                    <input type="text" onkeydown="mascaraTelefone(event)" maxlength="11" class="form-control"
                        id="telefone01_hosp" value="<?= $telefone01_formatado ?>" name="telefone01_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone02_hosp">Telefone Alternativo</label>
                    <input type="text" onkeydown="mascaraTelefone(event)" maxlength="11" class="form-control"
                        id="telefone02_hosp" value="<?= $telefone02_formatado ?>" name="telefone02_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="ativo_hosp">Ativo</label>
                    <select class="form-control" name="ativo_hosp">
                        <option value="s" <?= ($hospital->ativo_hosp == 's') ? 'selected' : '' ?>>Sim</option>
                        <option value="n" <?= ($hospital->ativo_hosp == 'n') ? 'selected' : '' ?>>Não</option>
                    </select>
                </div>
            </div>
            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep2(2)">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>

            <button type="button" class="btn btn-primary" onclick="nextStep2(4)">
                Próximo <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Step 4: Coordenadas e Responsáveis -->
        <div id="step-4" class="step" style="display:none;">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="coordenador_medico_hosp">Coordenador Médico</label>
                    <input type="text" class="form-control" value="<?= $hospital->coordenador_medico_hosp ?>"
                        id="coordenador_medico_hosp" name="coordenador_medico_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="diretor_hosp">Diretor</label>
                    <input type="text" class="form-control" value="<?= $hospital->diretor_hosp ?>" id="diretor_hosp"
                        name="diretor_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="coordenador_fat_hosp">Coordenador de Faturamento</label>
                    <input type="text" class="form-control" value="<?= $hospital->coordenador_fat_hosp ?>"
                        id="coordenador_fat_hosp" name="coordenador_fat_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="latitude_hosp">Latitude</label>
                    <input type="text" class="form-control" id="latitude_hosp" name="latitude_hosp"
                        placeholder="<?= $hospital->latitude_hosp ?>" value="<?= $hospital->latitude_hosp ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="longitude_hosp">Longitude</label>
                    <input type="text" class="form-control" id="longitude_hosp" name="longitude_hosp"
                        placeholder="<?= $hospital->longitude_hosp ?>" value="<?= $hospital->longitude_hosp ?>">
                </div>
            </div>

            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep2(3)">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
            <button type="submit" class="btn btn-success" style="margin: 15px 0">
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
                form.action = "<?= $BASE_URL ?>process_hospital.php";

                // Adiciona campos ocultos para o processo de deletar
                const inputType = document.createElement("input");
                inputType.type = "hidden";
                inputType.name = "type";
                inputType.value = "delUpdate";
                form.appendChild(inputType);

                const inputDeleted = document.createElement("input");
                inputDeleted.type = "hidden";
                inputDeleted.name = "deletado_hos";
                inputDeleted.value = "s";
                form.appendChild(inputDeleted);

                // Envia o formulário
                form.submit();
            }
        </script>
    </form>
</div>

<script>
    function mascara(i) {
        var v = i.value;
        if (isNaN(v[v.length - 1])) { // impede entrar outro caractere que não seja número
            i.value = v.substring(0, v.length - 1);
            return;
        }
        i.setAttribute("maxlength", "14");
        if (v.length == 3 || v.length == 7) i.value += ".";
        if (v.length == 11) i.value += "-";
    }
</script>
<script>
    function mascara(i, t) {

        var v = i.value;

        if (isNaN(v[v.length - 1])) {
            i.value = v.substring(0, v.length - 1);
            return;
        }

        if (t == "data") {
            i.setAttribute("maxlength", "10");
            if (v.length == 2 || v.length == 5) i.value += "/";
        }

        if (t == "cpf") {
            i.setAttribute("maxlength", "14");
            if (v.length == 3 || v.length == 7) i.value += ".";
            if (v.length == 11) i.value += "-";
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

        if (t == "tel") {
            if (v[0] == 12) {

                i.setAttribute("maxlength", "10");
                if (v.length == 5) i.value += "-";
                if (v.length == 0) i.value += "(";

            } else {
                i.setAttribute("maxlength", "9");
                if (v.length == 4) i.value += "-";
            }
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
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

</html>