    <?php $agora = date('Y-m-d'); ?>

    <div class="row">
        <div class="form-group row">
            <h4 class="page-title">Rever Evento Adverso</h4>
            <hr>
        </div>

        <!-- Barra de progresso -->
        <div class="progress">
            <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 50%" aria-valuenow="50"
                aria-valuemin="0" aria-valuemax="100">Etapa 1 de 2</div>
        </div>
        <br>

        <!-- Etapa 1 -->
        <div id="step-1" class="step">
            <h3>Passo 1: Informações Básicas</h3>
            <hr>
            <div class="form-group row">
                <div class="form-group col-sm-6">
                    <label for="hospital_selected">Hospital</label>
                    <select onchange="myFunctionSelected()" class="form-control" id="hospital_selected"
                        name="hospital_selected" required>
                        <option value="<?= $int_hospital->id_hospital ?>">
                            <?= $int_hospital->nome_hosp ?? "Selecione o Hospital" ?>
                            <?php foreach ($listHopitaisPerfil as $hospital): ?>
                        <option value="<?= $hospital["id_hospital"] ?>"
                            <?= $int_hospital->nome_hosp == $hospital["id_hospital"] ? 'selected' : '' ?>>
                            <?= $hospital["nome_hosp"] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label for="fk_paciente_int">Paciente</label>
                    <select class="form-control selectpicker show-tick" data-size="5" data-live-search="true"
                        id="fk_paciente_int" name="fk_paciente_int" required>
                        <option value=""><?= $int_paciente['nome_pac'] ?? "Selecione o Hospital" ?></option>
                        <?php
                        usort($pacientes, function ($a, $b) {
                            return strcmp($a["nome_pac"], $b["nome_pac"]);
                        });
                        foreach ($pacientes as $paciente): ?>
                        <option value="<?= $paciente["id_paciente"] ?>"
                            <?= $intern["fk_paciente_int"] == $paciente["id_paciente"] ? 'selected' : '' ?>>
                            <?= $paciente["nome_pac"] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <div id="alert_intern" style="font-size: 0.6em; margin-left: 7px; color: red; display:none">Paciente
                        já
                        internado</div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group col-sm-6">
                    <label for="data_intern_int">Data Internação</label>
                    <input type="date" readonly class="form-control " id="data_intern_int" required
                        name="data_intern_int" value="<?= $intern["data_intern_int"] ?>">
                </div>
                <div class="form-group col-sm-6">
                    <label for="data_visita_int">Data Visita</label>
                    <input type="date" class="form-control " id="data_visita_int"
                        value="<?= $intern["data_visita_int"] ?>" name="data_visita_int" readonly>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group col-sm-6">
                    <label for="internado_int">Internado</label>
                    <select class="form-control" readonly id="internado_int" name="internado_int">
                        <option value="s" <?= $intern["internado_int"] == "s" ? 'selected' : '' ?>>Sim</option>
                        <option value="n" <?= $intern["internado_int"] == "n" ? 'selected' : '' ?>>Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label for="usuario_create_int">Usuário</label>
                    <input type="text" class="form-control " id="usuario_create_int"
                        value="<?= $_SESSION['email_user'] ?>" name="usuario_create_int" readonly>
                </div>
                <div class="form-group col-sm-12">
                    <label for="rel_int">Relatório de Auditoria</label>
                    <textarea type="textarea" style="resize:none" rows="2" onclick="aumentarTextAudit()"
                        class="form-control" id="rel_int" name="rel_int"><?php echo $intern['rel_int'] ?></textarea>
                </div>
            </div>

            <!-- Botões de navegação -->
            <div class="form-group">
                <button type="button" class="btn btn-secondary" onclick="prevStep()" disabled>Voltar</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Próxima Etapa</button>
            </div>
        </div>

        <!-- Etapa 2 -->
        <div id="step-2" class="step" style="display: none;">
            <h3>Passo 2: Avento Adverso</h3>
            <hr>
            <?php include_once('form_edit_internacao_gestao_EA.php'); ?>

            <!-- Botões de navegação -->
            <div class="form-group">
                <button type="button" class="btn btn-secondary" onclick="prevStep()">Voltar</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Próxima Etapa</button>
            </div>
        </div>

    </div>

    <script>
$(document).ready(function() {
    $('.selectpicker').selectpicker();
    $('.selectpicker').selectpicker('refresh');
    $('.selectpicker').on('loaded.bs.select', function() {
        $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
    });
});
let currentStep = 1;

function nextStep() {
    document.getElementById('step-' + currentStep).style.display = 'none';
    currentStep++;
    document.getElementById('step-' + currentStep).style.display = 'block';
    updateProgressBar();
}

function prevStep() {
    if (currentStep > 1) {
        document.getElementById('step-' + currentStep).style.display = 'none';
        currentStep--;
        document.getElementById('step-' + currentStep).style.display = 'block';
        updateProgressBar();
    }
}

function updateProgressBar() {
    const totalSteps = 2;
    const progressPercentage = (currentStep / totalSteps) * 100;
    document.getElementById('progress-bar').style.width = progressPercentage + '%';
    document.getElementById('progress-bar').innerText = 'Etapa ' + currentStep + ' de ' + totalSteps;
}
    </script>



    <script src="js/scriptDataInt.js"></script>
    <script src="<?= $BASE_URL ?>js/text_cad_internacao.js"></script>
    <script src="js/select_internacao.js"></script>

    <script>
// var btnSelected = document.querySelector("#hospital_selected");
function myFunctionSelected() {
    var btnSelected = document.querySelector("#hospital_selected").value;
    var btnSelectedCx = document.querySelector("#hospital_selected");
    var textoSelecao = document.querySelector("#texto-selecao");
    const btnHospital = document.querySelector("#fk_hospital_int");
    btnHospital.value = btnSelected;
    $("#hospital_selected").css({
        "color": "black",
        "font-weight": "bold",
        "border": "2px",
        "border-color": "green",
        "border-style": "solid"

    });
    textoSelecao.textContent = "Você está lançando dados da Internação no Hospital";
}
    </script>
    <script>
// aparecer campo atb em uso
$(document).ready(function() {
    $('#atb').hide(); // Oculta o campo de texto quando a página carrega

    $('#atb_det').change(function() {
        if ($(this).val() === 's') {
            $('#atb').show();
        } else {
            $('#atb').hide();
        }
    });
});

// aparecer campo litros de O2
$(document).ready(function() {
    $('#div-oxig').hide(); // Oculta o campo de texto quando a página carrega

    $('#oxig_det').change(function() {
        console.log(document.querySelector('#titular_int'))
        if ($(this).val() === 'Cateter' || $(this).val() == 'Mascara') {
            $('#div-oxig').show();
        } else {
            $('#div-oxig').hide();
        }
    });
});
    </script>
    <!-- formulario ajax para envio form sem refresh -->
    <script>
// let capeante_adicional = 0;
novoValorInternacao = <?= $intern['id_internacao'] ?>;


$("#myForm").submit(function(event) {

    event.preventDefault(); //prevent default action 
    let post_url = $(this).attr("action"); //get form action url
    let request_method = $(this).attr("method"); //get form GET/POST method
    let form_data = $(this).serialize(); //Encode form elements for submission	
    const fk_int_capeante_js = document.querySelector("#fk_int_capeante");
    $.ajax({
        url: post_url,
        type: request_method,
        data: form_data,
        success: function(result) {
            console.log(result)
            $('form').trigger("reset");
            var btnSelected = document.querySelector("#hospital_selected").value;
            var btnSelectedCx = document.querySelector("#hospital_selected");
            var textoSelecao = document.querySelector("#texto-selecao");
            const btnHospital = document.querySelector("#fk_hospital_int");

            var valorInternacao = parseInt($("#id_internacao").val());
            var valorInternacaoTuss = parseInt($("#fk_int_tuss").val());
            var valorInternacaoUTI = parseInt($("#fk_internacao_uti").val());
            var valorInternacaoNegoc = parseInt($("#fk_id_int").val());
            var valorInternacaoPror = parseInt($("#fk_internacao_pror").val());
            var valorInternacaoGes = parseInt($("#fk_internacao_ges").val());

            // Soma o novoValor ao valor do input
            var novoValorInternacao = valorInternacao;

            // Atualiza o valor do input com o novo valor
            $("#id_internacao").val(novoValorInternacao);
            $("#fk_int_tuss").val(novoValorInternacao);
            $("#fk_internacao_uti").val(novoValorInternacao);
            $("#fk_id_int").val(novoValorInternacao);
            $("#fk_internacao_pror").val(novoValorInternacao);
            $("#fk_internacao_ges").val(novoValorInternacao);

            btnHospital.value = btnSelected;
            $("#hospital_selected").css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px",
                "border-color": "green",
                "border-style": "solid"

            });
            textoSelecao.textContent = "Você está lançando dados da Internação no Hospital";
            var textoAtual = $("#proximoId_int").text();
            var novoValor = parseInt(textoAtual);

            $("#proximoId_int").text(novoValor);
            $("#RegInt").val(novoValorInternacao);

            var valorInternacao = parseInt($("#id_internacao").val());
            var valorInternacaoTuss = parseInt($("#fk_int_tuss").val());
            var valorInternacaoUTI = parseInt($("#fk_internacao_uti").val());
            var valorInternacaoNegoc = parseInt($("#fk_id_int").val());
            var valorInternacaoPror = parseInt($("#fk_internacao_pror").val());
            var valorInternacaoGes = parseInt($("#fk_internacao_ges").val());

            // Atualiza o valor do input com o novo valor
            $("#id_internacao").val(novoValorInternacao);
            $("#fk_int_tuss").val(novoValorInternacao);
            $("#fk_internacao_uti").val(novoValorInternacao);
            $("#fk_id_int").val(novoValorInternacao);
            $("#fk_internacao_pror").val(novoValorInternacao);
            $("#fk_internacao_ges").val(novoValorInternacao);
            var btnSelected = document.querySelector("#hospital_selected").value;


            var divGes = document.querySelector("#container-gestao");
            var divTuss = document.querySelector("#container-tuss");
            var divPro = document.querySelector("#container-prorrog");
            var divUti = document.querySelector("#container-uti");
            var divNeg = document.querySelector("#container-negoc");

            divUti.style.display = "none";
            divTuss.style.display = "none";
            divPro.style.display = "none";
            divGes.style.display = "none";
            divNeg.style.display = "none";

            $("#select_tuss").val("");
            $("#select_tuss").css({
                "color": "gray",
                "font-weight": "normal",
                "border": "1px",
                "border-color": "gray",

            });

            var select_gestao = document.getElementById("#select_gestao");
            $("#select_gestao").val("");
            $("#select_gestao").css({
                "color": "gray",
                "font-weight": "normal",
                "border": "1px",
                "border-color": "gray",

            });

            var select_detalhes = document.getElementById("#select_detalhes");
            $("#select_detalhes").val("");
            $("#select_detalhes").css({
                "color": "gray",
                "font-weight": "normal",
                "border": "1px",
                "border-color": "gray",

            });
            var select_prorrog = document.getElementById("#select_prorrog");
            $("#select_prorrog").val("");
            $("#select_prorrog").css({
                "color": "gray",
                "font-weight": "normal",
                "border": "1px",
                "border-color": "gray",

            });

            var select_uti = document.getElementById("#select_uti");
            $("#select_uti").val("");
            $("#select_uti").css({
                "color": "gray",
                "font-weight": "normal",
                "border": "1px",
                "border-color": "gray",

            });

            var select_negoc = document.getElementById("#select_negoc");
            $("#select_negoc").val("");
            $("#select_negoc").css({
                "color": "gray",
                "font-weight": "normal",
                "border": "1px",
                "border-color": "gray",

            });

            $('#alert').addClass("alert-success");
            $('#alert').fadeIn().html("Editado com sucesso");
            setTimeout(function() {
                $('#alert').fadeOut('Slow');
            }, 2000);

        }
    });
});
    </script>