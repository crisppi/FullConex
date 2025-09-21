<div class="row">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <h4 class="page-title">Cadastrar Censo de Internação</h4>

    <?php $a = ($findMaxGesInt[0]);
    $ultimoReg = ($a["ultimoReg"]);
    $ultimoReg = $ultimoReg + 1;
    ?>
    <!-- selecionar hospital -->
    <div class="form-group row">
        <hr>

        <div class="form-group col-sm-2">
            <select onchange="myFunctionSelected()" class="form-select botao_select" id="hospital_selected"
                name="hospital_selected" required>
                <option value="<?= $hospital["nome_hosp"] ?? "Selecione o Hospital" ?>">Hospital</option>
                <?php
                foreach ($hospitals as $hospital): ?>
                    <option value="<?= $hospital["id_hospital"] ?>">
                        <?= $hospital["nome_hosp"] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <hr>
    </div>
    <!-- <p class="page-description">Adicione informações sobre a internação</p> -->
    <!-- FORMULARIO DE CADASTRO DO CENSO -->
    <form id="myForm" action="<?= $BASE_URL ?>process_censo.php" id="add-internacao-form" method="POST"
        enctype="multipart/form-data">
        <input type="hidden" name="type" value="create">
        <div class="form-group row">
            <input type="hidden" value="<?= $hospital["id_hospital"] ?>" name="fk_hospital_censo" id="fk_hospital_censo"
                value="fk_hospital_censo">
            <div class="form-group col-sm-3">
                <label for="fk_paciente_censo">Paciente</label>
                <select class="form-control form-control-sm selectpicker show-tick" data-size="5"
                    data-live-search="true" id="fk_paciente_censo" name="fk_paciente_censo" required>
                    <option value="">Selecione</option>
                    <?php foreach ($pacientes as $paciente): ?>
                        <option value="<?= $paciente["id_paciente"] ?>">
                            <?= $paciente["nome_pac"] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div>
                    <a style="font-size:0.6em; margin-left:7px; color:darkgray"
                        href="<?= $BASE_URL ?>cad_paciente.php?id_estipulante=<?= $id_estipulante ?? 0 ?>"><i
                            style="color:darkgray" name="type" value="edite" class="far fa-edit edit-icon"></i> Novo
                        Paciente</a>
                </div>
            </div>
            <?php $dataAtual = date('Y-m-d');
            ?>
            <div class="form-group col-sm-2">
                <label for="data_censo">Data Internação</label>
                <input type="date" class="form-control-sm form-control" id="data_censo"
                    value="<?php echo date('Y-m-d') ?>" name="data_censo">
                <div class="notif-input oculto" id="notif-input">
                    Data inválida !
                </div>
            </div>

            <div class="form-group col-sm-2">
                <label for="senha_censo">Senha</label>
                <input type="text" class="form-control-sm form-control" id="senha_censo" name="senha_censo">
            </div>
            <input type="hidden" class="form-control" value="s" id="censo_censo" name="censo_censo">

            <!-- ENTRADA DE DADOS AUTOMATICOS NO INPUT-->
        </div>
        <div class="row">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomodacao_censo">Acomodação</label>
                <select class="form-control-sm form-control" id="acomodacao_censo" name="acomodacao_censo">
                    <option value="">Selecione</option>
                    <?php
                    sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                        <option value="<?= $acomd; ?>">
                            <?= $acomd; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" for="modo_internacao_censo">Modo Admissão</label>
                <select class="form-control-sm form-control" id="modo_internacao_censo" name="modo_internacao_censo">
                    <option value="">Selecione</option>
                    <?php
                    sort($modo_internacao, SORT_ASC);
                    foreach ($modo_internacao as $modo_censo) { ?>
                        <option value="<?= $modo_censo; ?>">
                            <?= $modo_censo; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" for="tipo_admissao_censo">Tipo Internação</label>
                <select class="form-control-sm form-control" id="tipo_admissao_censo" name="tipo_admissao_censo">
                    <option value="">Selecione</option>
                    <?php
                    sort($tipo_admissao, SORT_ASC);
                    foreach ($tipo_admissao as $modo_adm) { ?>
                        <option value="<?= $modo_adm; ?>">
                            <?= $modo_adm; ?>
                        </option>
                    <?php } ?>
                </select>

            </div>
            <div class="form-group col-sm-3">
                <label for="titular_censo">Médico</label>
                <input type="text" class="form-control-sm form-control" id="titular_censo" name="titular_censo">
            </div>
            <input type="hidden" class="form-control" id="usuario_create_censo" value="<?= $_SESSION['email_user'] ?>"
                name="usuario_create_censo" readonly>
            <input type="hidden" class="form-control" id="fk_usuario_censo" value="<?= $_SESSION['id_usuario'] ?>"
                name="fk_usuario_censo">
            <div class="form-group col-sm-1">
                <?php $agora = date('Y-m-d H:i:s'); ?>
                <input type="hidden" class="form-control" id="data_create_censo" value='<?= $agora; ?>'
                    name="data_create_censo">
            </div>
            <div class="form-group row">
                <div>
                    <hr>
                </div>
            </div>
            <br>
            <div> <button type="submit" class="btn btn-primary">Cadastrar</button>
                <br>
            </div>
            <div style="margin-top:20px; margin-left:15px; width:500px;display:none" class="alert" id="alert"
                role="alert"></div>
        </div>

        <br>
    </form>
    <br>
</div>
<!-- Inclui o CSS do Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Inclui o JavaScript do Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- <script src="js/scriptDataInt.js"></script> -->
<script>
    $(document).ready(function() {
        $('.selectpicker').selectpicker();
        $('.selectpicker').selectpicker('refresh');
        $('.selectpicker').on('loaded.bs.select', function() {
            $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
        });
    });
</script>
<script>
    // var btnSelected = document.querySelector("#hospital_selected");
    function myFunctionSelected() {
        var btnSelected = document.querySelector("#hospital_selected").value;
        var btnSelectedCx = document.querySelector("#hospital_selected");
        var textoSelecao = document.querySelector("#texto-selecao");
        const btnHospital = document.querySelector("#fk_hospital_censo");
        btnHospital.value = btnSelected;
        $("#hospital_selected").css({
            "color": "black",
            "font-weight": "bold",
            "border": "2px",
            "border-color": "green",
            "border-style": "solid"

        });
        textoSelecao.textContent = "Você está lançando dados do Censo no Hospital";
    }
</script>

<!-- formulario ajax para envio form sem refresh -->
<script>
    $("#myForm").submit(function(event) {
        event.preventDefault(); //prevent default action 
        let post_url = $(this).attr("action"); //get form action url
        let request_method = $(this).attr("method"); //get form GET/POST method
        let form_data = $(this).serialize(); //Encode form elements for submission	
        $.ajax({
            url: post_url,
            type: request_method,
            data: form_data,
            success: function(result) {
                console.log(result);
                $('form').trigger("reset");
                var btnSelected = document.querySelector("#hospital_selected").value;
                const btnHospital = document.querySelector("#fk_hospital_censo");
                btnHospital.value = btnSelected;

                $('#fk_paciente_censo').val('').selectpicker('refresh');

                $('#fk_paciente_censo').val(null).trigger('change');

                if (result != '0') {
                    $('#alert').removeClass("alert-danger");
                    $('#alert').addClass("alert-success");
                    $('#alert').fadeIn().html("Censo cadastrado com sucesso");
                } else {
                    $('#alert').removeClass("alert-success");
                    $('#alert').addClass("alert-danger");
                    $('#alert').fadeIn().html("Paciente já internado");
                }


                setTimeout(function() {
                    $('#alert').fadeOut('Slow');
                }, 2000);
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>

<!-- CSS do Bootstrap-Select -->
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css">

<!-- JS do Bootstrap-Select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>