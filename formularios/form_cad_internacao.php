<?php

require_once("templates/header.php");

require_once("models/message.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/acomodacao.php");
include_once("dao/acomodacaoDao.php");

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);

$acomodacaoDao = new acomodacaoDAO($conn, $BASE_URL);
$acomodacao = $acomodacaoDao->findGeral();
$id_paciente_get = filter_input(INPUT_GET, 'id_paciente', FILTER_VALIDATE_INT);


?>
<link href="<?php $BASE_URL ?>css/style.css" rel="stylesheet">

<div class="row">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <div class="form-group row">
        <h4 class="text-center w-100" style="
    margin: -7px 10px;
    background-color: #5e2363;
    color: #fff;
    padding: 13px 0;
    border-radius: 0.25rem;
  ">Cadastrar internação</h4>
        <hr>

        <div class="col-12 d-flex align-items-end flex-wrap justify-content-between" style="margin-top: -20px;">
            <!-- Bloco dos SELECTS à ESQUERDA ocupando ~66% -->
            <div class="d-flex flex-wrap align-items-end" style="gap: 30px; flex: 2;">
                <!-- Campo Id-Int -->
                <div class="form-group mb-0">
                    <label class="control-label" for="RegInt">Id-Int</label>
                    <input type="text" id="RegInt" name="RegInt" readonly class="form-control"
                        style="height: 45px; background-color: #fff; color: #000; font-weight: 500; opacity: 1; cursor: default;"
                        value="<?= ($ultimoReg + 1) ?>">
                </div>

                <!-- Select do Hospital -->
                <div class="form-group mb-0" style="min-width: 300px;">
                    <label class="control-label" for="hospital_selected" style="margin-bottom: 2px;">
                        <span style="color: red;">*</span>
                        Hospital</label>
                    <select onchange="myFunctionSelected()"
                        style="height: 45px !important; border: 1px solid #555; font-size: 1em; background-color: #fff; color: #000; width: 100%;"
                        class="form-select botao_select" id="hospital_selected" name="hospital_selected" required>
                        <option value="">Selecione</option>
                        <?php foreach ($listHopitaisPerfil as $hospital): ?>
                        <option value="<?= htmlspecialchars($hospital['id_hospital']); ?>">
                            <?= $hospital["nome_hosp"] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Div CENTRAL ao centro horizontalmente -->
            <div class="d-flex justify-content-center align-items-center" style="flex: 1">
                <div id="hospitalNomeTexto" style="
                width: 100%;
                display: none;
                max-width: 500px;
                margin-left:-500px;
                height: 75px;
                padding: 0 50px;
                border: 2px solid #28a745;
                border-radius: 8px;
                font-size: 1.2em;
                font-weight: 600;
                color: #000;
                background-color: #f8fff8;
                align-items: center;
                justify-content: center;
                text-align: center;">
                    <!-- Nome do hospital aqui -->
                </div>
            </div>
        </div>

        <hr class="w-100">
    </div>


    <form class="visible" action="<?= $BASE_URL ?>process_internacao.php" id="myForm" method="POST"
        enctype="multipart/form-data">
        <div style="text-align: right;">
            <p style="font-size: .6em; color:red; margin-top: -20px;">* Campos Obrigatórios</p>
        </div>

        <input type="hidden" name="type" value="create">
        <p style="display:none" id="proximoId_int">0</p>
        <input type="hidden" value="n" id="censo_int" name="censo_int">
        <input type="hidden" value="<?= $_SESSION["id_usuario"] ?>" id="fk_usuario_int" name="fk_usuario_int">
        <div class=" form-group row">
            <input type="hidden" value="<?= $hospital["id_hospital"] ?>" name="fk_hospital_int" id="fk_hospital_int">

            <div class="form-group col-sm-3" style="margin-bottom:-25px">
                <label class="control-label" for="fk_paciente_int"><span style="color: red; ">*</span> Paciente </label>
                <select onchange="teste()" data-size="5" data-live-search="true"
                    class="form-control form-control-sm selectpicker show-tick" id="fk_paciente_int"
                    name="fk_paciente_int" required>
                    <option value="">Selecione</option>
                    <?php
                    // Ordena o array de pacientes em ordem ascendente pelo nome
                    usort($pacientes, function ($a, $b) {
                        return strcmp($a["nome_pac"], $b["nome_pac"]);
                    });
                    foreach ($pacientes as $paciente): ?>
                    <option value="<?= $paciente["id_paciente"] ?>"><?= $paciente["nome_pac"] ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <a style="font-size: 0.6em; margin-left: 7px; color: blue;"
                        href="<?= $BASE_URL ?>list_paciente.php?id_paciente=<?= $id_paciente ?? 0 ?>">
                        <i style="color: blue; margin-bottom: 7px;" class="far fa-edit edit-icon"></i> Novo Paciente
                    </a>
                    <div id="alert_intern" style="font-size: 0.6em; margin-left: 7px; color: red;display:none">
                        Paciente já internado
                    </div>
                </div>
            </div>

            <?php $dataAtual = date('Y-m-d');
            ?>
            <div class="form-group col-sm-2">
                <label class="control-label" for="data_intern_int"><span style="color: red; ">*</span> Data
                    Internação</label>
                <input type="date" class="form-control form-control-sm" id="data_intern_int" required value=""
                    name="data_intern_int">
                <p id="erro-data-internacao" style="color: red; font-size: 0.7em; display: none; margin-top: 5px;"></p>

            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="hora_intern_int">Hora</label>
                <input type="time" class="form-control form-control-sm" id="hora_intern_int" value=""
                    name="hora_intern_int">
            </div>



            <div class="form-group col-sm-1">
                <label for="data_visita_int"><span style="color: red; ">*</span> Data Visita</label>
                <input type="date" value='<?= $dataAtual; ?>' class="form-control form-control-sm" id="data_visita_int"
                    name="data_visita_int">
                <p id="error-message" style="color: red; display: none;font-size: 0.6em;"></p>

            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="internado_int">Internado</label>
                <select class="form-control-sm form-control" id="internado_int" name="internado_int">
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>

            <div class="form-group col-sm-1" id="div-data-alta" style="display:none">
                <label class="control-label" for="data_alta_alt"> Data Alta</label>
                <input type="date" class="form-control form-control-sm" id="data_alta_alt" name="data_alta_alt">
            </div>

            <div class="form-group col-sm-2" id="div-motivo-alta" style="display:none">
                <label class="control-label" for="tipo_alta_alt"> Motivo Alta</label>
                <select class="form-control" id="tipo_alta_alt" name="tipo_alta_alt">
                    <option value="">Selecione o motivo da alta</option>
                    <?php
                    sort($dados_alta, SORT_ASC);
                    foreach ($dados_alta as $alta) { ?>
                    <option value="<?= $alta; ?>">
                        <?= $alta; ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

            <input type="hidden" id="id_internacao" readonly class="form-control" name="id_internacao"
                value="<?= $ultimoReg ?>">
            <!-- ENTRADA DE DADOS AUTOMATICOS NO INPUT-->
            <input type="hidden" value="s" id="primeira_vis_int" name="primeira_vis_int">
            <input type="hidden" value="0" id="visita_no_int" name="visita_no_int">
            <input type="hidden" id="visita_enf_int" name="visita_enf_int" value="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                                                        echo 's';
                                                                                    } else {
                                                                                        echo 'n';
                                                                                    }; ?>">

            <input type="hidden" id="visita_med_int" name="visita_med_int" value="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                                                                        echo 's';
                                                                                    } else {
                                                                                        echo 'n';
                                                                                    }; ?>">

            <input type="hidden" id="visita_auditor_prof_enf" name="visita_auditor_prof_enf" value="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                                                                        echo ($_SESSION['email_user']);
                                                                                                    }; ?>">
            <input type="hidden" id="visita_auditor_prof_med" name="visita_auditor_prof_med" value="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                                                                        echo ($_SESSION['email_user']);
                                                                                                    }; ?>">

        </div>
        <div class="row">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomodacao_int">Acomodação</label>
                <select class="form-control-sm form-control" id="acomodacao_int" name="acomodacao_int">
                    <option value="">Selecione</option>
                    <?php
                    sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="especialidade_int">Especialidade</label>
                <select class="form-control-sm form-control" id="especialidade_int" name="especialidade_int">
                    <option value="">Selecione</option>
                    <?php
                    sort($dados_especialidade, SORT_ASC);
                    foreach ($dados_especialidade as $especial) { ?>
                    <option value="<?= $especial; ?>"><?= $especial; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-3">
                <label for="titular_int">Médico</label>
                <input type="text" maxlength="100" class="form-control form-control-sm" id="titular_int"
                    name="titular_int">
            </div>
            <div class="form-group col-sm-1">
                <label for="crm_int">CRM</label>
                <input type="text" maxlength="10" class="form-control form-control-sm" id="crm_int" name="crm_int">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="modo_internacao_int">Modo Admissão</label>
                <select class="form-control-sm  form-control" id="modo_internacao_int" name="modo_internacao_int">
                    <option value="">Selecione</option>
                    <option value="Clínica">Clínica</option>
                    <option value="Pediatria">Pediatria</option>
                    <option value="Ortopedia">Ortopedia</option>
                    <option value="Obstetrícia">Obstetrícia</option>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tipo_admissao_int">Tipo Internação</label>
                <select class="form-control-sm form-control" id="tipo_admissao_int" name="tipo_admissao_int">
                    <option value="">Selecione</option>
                    <option value="Eletiva">Eletiva</option>
                    <option value="Urgência">Urgência</option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <div style="display:none;" id="div_int_pertinente_int" class="form-group col-sm-2">
                <label class="control-label" for="int_pertinente_int"><span style="color: red; ">*</span> Internação
                    pertinente?</label>
                <select class="form-control-sm form-control" id="int_pertinente_int" name="int_pertinente_int">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <div id="div_rel_pertinente_int" style="display:none;" class="form-group col-sm-8">
                <label for="rel_pertinente_int">Justifique não pertinência</label>
                <textarea type="textarea" style="resize:none" rows="3" class="form-control" id="rel_pertinente_int"
                    name="rel_pertinente_int"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <div class="form-group col-sm-3">
                <label class="control-label" for="fk_patologia_int">Patologia</label>
                <select class="form-control-sm form-control selectpicker show-tick" data-size="5"
                    data-live-search="true" id="fk_patologia_int" name="fk_patologia_int">
                    <option value="">Selecione</option>
                    <?php
                    // Ordena o array de patologias em ordem ascendente de patologia
                    usort($patologias, function ($a, $b) {
                        return strcmp($a["patologia_pat"], $b["patologia_pat"]);
                    });
                    foreach ($patologias as $patologia): ?>
                    <option value="<?= $patologia["id_patologia"] ?>"><?= $patologia["patologia_pat"] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="form-group col-sm-2">
                <label class="control-label" for="grupo_patologia_int">Grupo Patologia</label>
                <select class="form-control-sm form-control" id="grupo_patologia_int" name="grupo_patologia_int">
                    <option value="">Selecione</option>
                    <?php foreach ($dados_grupo_pat as $grupo): ?>
                    <option value="<?= $grupo ?>"><?= $grupo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-sm-1">
                <label class="control-label" for="origem_int">Origem</label>
                <select class=" form-control-sm form-control" id="origem_int" name="origem_int">
                    <option value="">Selecione</option>
                    <?php foreach ($origem as $origens): ?>
                    <option value="<?= $origens ?>"><?= $origens ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-sm-1">
                <label for="senha_int">Senha</label>
                <input type="text" maxlength="20" class="form-control form-control-sm" id="senha_int" name="senha_int">
            </div>
            <div class="form-group col-sm-2">
                <label for="senha_int">Num. Atendimento</label>
                <input type="text" maxlength="20" class="form-control form-control-sm" id="num_atendimento_int"
                    name="num_atendimento_int">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="fk_patologia2">Antecedente</label>
                <select class="form-control-sm form-control selectpicker show-tick" data-size="5"
                    data-live-search="true" id="fk_patologia2" name="fk_patologia2[]" multiple title="Selecione">
                    <!-- Adicione o atributo title -->

                    <?php
                    // Ordena o array de pacientes em ordem ascendente pelo nome
                    usort($antecedentes, function ($a, $b) {
                        return strcmp($a["antecedente_ant"], $b["antecedente_ant"]);
                    });
                    foreach ($antecedentes as $antecedente): ?>
                    <option value="<?= $antecedente["id_antecedente"] ?>"><?= $antecedente["antecedente_ant"] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" value="" id="json-antec" name="json-antec">
        </div>
        <div>
            <br>
        </div>
        <div class="form-group " style="margin-left:0px; margin-top:-15px">
            <div>
                <label for="rel_int">Relatório de Auditoria</label>
                <textarea type="textarea" maxlength="5000" style="resize:none" rows="2" onclick="aumentarTextAudit()"
                    class="form-control" id="rel_int" name="rel_int"></textarea>
            </div>

            <div id="chat-widget" style="position: fixed; bottom: 20px; right: 20px; width: 300px; z-index: 9999;">
                <div id="chat-header" style="background-color: #007bff; color: white; padding: 10px; cursor: pointer;">
                    Chat - Assistente Virtual
                </div>
                <div id="chat-body"
                    style="display: none; border: 1px solid #ccc; background: white; max-height: 400px; overflow-y: auto;">
                    <div id="chat-messages" style="padding: 10px; font-size: 0.9em;"></div>
                    <div style="padding: 10px;">
                        <input type="text" id="chat-input" placeholder="Digite sua mensagem..."
                            style="width: 100%; padding: 5px; border: 1px solid #ccc;">
                        <button id="chat-send"
                            style="margin-top: 5px; width: 100%; background-color: #007bff; color: white; border: none; padding: 5px;">Enviar</button>
                    </div>
                </div>
            </div>

            <div style="margin-top: 10px;">
                <label for="acoes_int">Ações da Auditoria</label>
                <textarea rows="2" style="resize:none" onclick="aumentarTextAcoes()" type="textarea"
                    class="form-control" maxlength="5000" id="acoes_int" name="acoes_int"></textarea>
            </div>
            <div style="margin-top: 10px;">
                <label for="programacao_int">Programação Terapêutica</label>
                <textarea type="textarea" style="resize:none" maxlength="5000" rows="2" onclick="aumentarTextProgInt()"
                    class="form-control" id="programacao_int" name="programacao_int"></textarea>
            </div>
            <div><br></div>
            <hr>
            <h4 class="text-center w-100"
                style="margin: 7px 10px 0px 0px;background-color: #5e2363;color: #fff;padding: 13px 0;border-radius: 0.25rem;">
                Detalhes do relatório</h4>
            <hr>
            <!--****************************************-->
            <!--************ div de detalhes ***********-->
            <!--****************************************-->
            <input type="hidden" class="form-control" id="select_detalhes" name="select_detalhes">
            <div class="form-group row">

                <div class="form-group col-sm-2" style="margin-left: 10px;">
                    <label class="control-label" style="font-weight: bold;" for="relatorio-detalhado">Relatório
                        detalhado</label>
                    <select class="form-control-sm form-control" id="relatorio-detalhado" name="relatorio-detalhado"
                        style="color:white;
           font-weight:normal;
           border:1px solid #5e2363;
           background-color:#5e2363;">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                    <p id="text-detalhado" style="font-size:0.7em; text-align:center; margin-top:8px; margin-left:8px">
                        Selecione este
                        campo caso deseje
                        detalhar a visita</p>
                </div>
                <div class="form-group col-sm-3">
                    <?php $agora = date('Y-m-d'); ?>
                    <input type="hidden" id="data_create_int" value='<?= $agora; ?>' name="data_create_int">
                </div>
                <div>
                    <hr>
                </div>
            </div>

            <div id="div-detalhado" class="form-group row" style="margin-left:-12px">
                <div class="form-group row">
                    <input type="hidden" readonly id="fk_int_det" name="fk_int_det" value="<?= ($ultimoReg + 1) ?> ">

                    <div class="form-group col-sm-2">
                        <label class="control-label" for="curativo_det">Curativo</label>
                        <select class="form-control-sm  form-control" id="curativo_det" name="curativo_det">
                            <option value="">Selecione</option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="dieta_det">Tipo dieta</label>
                        <select class="form-control-sm  form-control" id="dieta_det" name="dieta_det">
                            <option value="">Selecione</option>
                            <option value="Oral">Oral</option>
                            <option value="Enteral">Enteral</option>
                            <option value="NPP">NPP</option>
                            <option value="Jejum">Jejum</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="nivel_consc_det">Nível de Consciência</label>
                        <select class="form-control-sm  form-control" id="nivel_consc_det" name="nivel_consc_det">
                            <option value="">Selecione</option>
                            <option value="Consciente">Consciente</option>
                            <option value="Comatoso">Comatoso</option>
                            <option value="Vigil">Vigil</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="oxig_det">Oxigênio</label>
                        <select class="form-control-sm form-control" id="oxig_det" name="oxig_det">
                            <option value="">Selecione</option>
                            <option value="Cateter">Cateter</option>
                            <option value="Mascara">Máscara</option>
                            <option value="VNI">VNI</option>
                            <option value="Alto Fluxo">Alto Fluxo</option>
                        </select>
                    </div>
                    <div id="div-oxig" class="form-group col-sm-1">
                        <label class="control-label" for="oxig_uso_det">Lts O2</label>
                        <input class="form-control-sm form-control" type="text" name="oxig_uso_det"></input>
                    </div>
                    <style>

                    </style>
                    <div class="form-group col-sm-3">
                        <label class="control-label">Dispositivos</label>
                        <div class="d-flex flex-wrap align-items-center">

                            <div class="form-check ">
                                <label style="margin-left:-30px" class="control-label" for="tqt_det">TQT</label>
                                <input class="form-check-input " type="checkbox" name="tqt_det" id="tqt_det"
                                    value="TQT">
                            </div>
                            <div class="form-check">
                                <label style="margin-left:-30px" class="control-label" for="svd_det">SVD</label>
                                <input class="form-check-input" type="checkbox" name="svd_det" id="svd_det" value="SVD">
                            </div>
                            <div class="form-check" style="text-align: center;">
                                <label style="margin-left:-30px" class="control-label" for="sne_det"
                                    style="display: block;">SNE</label>
                                <input class="form-check-input" type="checkbox" name="sne_det" id="sne_det" value="SNE">
                            </div>
                            <div class="form-check">
                                <label style="margin-left:-30px" style="margin-left:-30px" class="control-label"
                                    for="gtt_det">GTT</label>
                                <input class="form-check-input" type="checkbox" name="gtt_det" id="gtt_det" value="GTT">
                            </div>
                            <div class="form-check">
                                <label style="margin-left:-30px" class="control-label" for="dreno_det">Dreno</label>
                                <input class="form-check-input" type="checkbox" name="dreno_det" id="dreno_det"
                                    value="Dreno">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group row" style="margin-top: -20px;">
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="hemoderivados_det">Hemoderivados</label>
                        <select class="form-control-sm  form-control" id="hemoderivados_det" name="hemoderivados_det">
                            <option value="">Selecione</option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="dialise_det">Diálise</label>
                        <select class="form-control-sm  form-control" id="dialise_det" name="dialise_det">
                            <option value="">Selecione</option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="oxigenio_hiperbarica_det">Oxigenioterapia Hiperbárica</label>
                        <select class="form-control-sm  form-control" id="oxigenio_hiperbarica_det"
                            name="oxigenio_hiperbarica_det">
                            <option value="">Selecione</option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-1">
                        <label class="control-label" for="qt_det">QT</label>
                        <select class="form-control-sm form-control" id="qt_det" name="qt_det">
                            <option value=""></option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-1">
                        <label class="control-label" for="rt_det">RT</label>
                        <select class="form-control-sm form-control" id="rt_det" name="rt_det">
                            <option value=""></option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-1">
                        <label class="control-label" for="acamado_det">Acamado</label>
                        <select class="form-control-sm form-control" id="acamado_det" name="acamado_det">
                            <option value=""></option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-1">
                        <label class="control-label" for="atb_det">Antibiótico</label>
                        <select class="form-control-sm form-control" id="atb_det" name="atb_det">
                            <option value=""></option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div id="atb" class="form-group col-sm-3">
                        <label class="control-label" for="atb_uso_det">Antibiótico em uso</label>
                        <input class="form-control" type="text" name="atb_uso_det"></input>
                    </div>
                    <div class="form-group col-sm-1">
                        <label class="control-label" for="medic_alto_custo_det">Medicação</label>
                        <select class="form-control-sm form-control" id="medic_alto_custo_det"
                            name="medic_alto_custo_det">
                            <option value="n">Não</option>
                            <option value="s">Sim</option>
                        </select>
                    </div>
                    <div id="medicacaoDet" class="form-group col-sm-3">
                        <label class="control-label" for="qual_medicamento_det">Medicação alto custo</label>
                        <input class="form-control-sm form-control" type="text" name="qual_medicamento_det"></input>
                    </div>
                    <div>
                        <label for="exames_det">Exames relevantes</label>
                        <textarea type="textarea" style="resize:none" maxlength="5000" rows="3"
                            onclick="aumentarText('exames_det')" onblur="reduzirText('exames_det', 3)"
                            class="form-control" id="exames_det" name="exames_det"></textarea>
                    </div>
                    <div>
                        <label for="oportunidades_det">Oportunidades</label>
                        <textarea type="textarea" style="resize:none" maxlength="5000" rows="2"
                            onclick="aumentarText('oportunidades_det')" class="form-control" id="oportunidades_det"
                            onblur="reduzirText('oportunidades_det', 3)" name="oportunidades_det"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="liminar_det">Possui Liminar?</label>
                        <select class="form-control-sm form-control" id="liminar_det" name="liminar_det">
                            <option value="n">Não</option>
                            <option value="s">Sim</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="paliativos_det">Está em Cuidados Paliativos?</label>
                        <select class="form-control-sm form-control" id="paliativos_det" name="paliativos_det">
                            <option value="n">Não</option>
                            <option value="s">Sim</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="parto_det">Parto</label>
                        <select class="form-control-sm form-control" id="parto_det" name="parto_det">
                            <option value="n">Não</option>
                            <option value="s">Sim</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="braden_det">Escala de Braden</label>
                        <select class="form-control-sm form-control" id="braden_det" name="braden_det">
                            <option value=""></option>
                            <option value="alto">Alto</option>
                            <option value="moderado">Moderado</option>
                            <option value="baixo">Baixo</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
        <h4 class="text-center w-100"
            style="margin: -15px 10px 0px 0px;background-color: #5e2363;color: #fff;padding: 13px 0;border-radius: 0.25rem;">
            Tabelas Adicionais</h4>
        <hr>
        <div class="form-group row d-flex justify-content-center align-items-end" style="gap: 15px;">


            <?php
            if ($_SESSION['cargo'] === 'Med_auditor' || ($_SESSION['cargo'] === 'Diretoria')) { ?>
            <div class="form-group col-sm-2">
                <label class="control-label" style="font-weight: bold;" for="select_tuss">Tuss</label>
                <select class="form-control-sm form-control select-purple" id="select_tuss" name="select_tuss">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" style="font-weight: bold;" for="select_prorrog">Prorrogação</label>
                <select class="form-control-sm form-control select-purple" id="select_prorrog" name="select_prorrog">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <?php } ?>

            <div class="form-group col-sm-2">
                <label class="control-label" style="font-weight: bold;" for="select_gestao">Gestão</label>
                <select class="form-control-sm form-control select-purple" id="select_gestao" name="select_gestao">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" style="font-weight: bold;" for="select_uti">UTI</label>
                <select class="form-control-sm form-control select-purple" id="select_uti" name="select_uti">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <?php
            if ($_SESSION['cargo'] === 'Med_auditor' || ($_SESSION['cargo'] === 'Diretoria')) { ?>
            <div class="form-group col-sm-2">
                <label class="control-label" style="font-weight: bold;" for="select_negoc">Negociações</label>
                <select class="form-control-sm form-control select-purple" id="select_negoc" name="select_negoc">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <?php } ?>

        </div>

        <input type="hidden" class="form-control" value="<?= ($ultimoReg + 1) ?>" id="fk_int_capeante"
            name="fk_int_capeante">
        <input type="hidden" class="form-control" value="n" id="encerrado_cap" name="encerrado_cap">
        <input type="hidden" class="form-control" value="s" id="aberto_cap" name="aberto_cap">
        <input type="hidden" class="form-control" value="n" id="em_auditoria_cap" name="em_auditoria_cap">
        <input type="hidden" class="form-control" value="n" id="senha_finalizada" name="senha_finalizada">

        <!-- <FORMULARO DE NEGOCIACOES -->
        <?php include_once('formularios/form_cad_internacao_tuss.php'); ?>

        <!-- FORMULARIO DE GESTÃO -->
        <?php include_once('formularios/form_cad_internacao_gestao.php'); ?>

        <!-- FORMULARIO DE UTI -->
        <?php include_once('formularios/form_cad_internacao_uti.php'); ?>

        <!-- FORMULARIO DE PRORROGACOES -->
        <?php include_once('formularios/form_cad_internacao_prorrog.php'); ?>

        <!-- <FORMULARO DE NEGOCIACOES -->
        <?php include_once('formularios/form_cad_internacao_negoc.php'); ?>


        <div class="row">
            <div class="form-group col-md-6">
                <label for="intern_files">Arquivos</label>
                <input type="file" class="form-control" name="intern_files[]" id="intern_files"
                    accept="image/png, image/jpeg" multiple>
                <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
            </div>
        </div>

        <div>
            <hr>
            <button type="submit" class="btn btn-success"><i style="font-size: 1rem;margin-right:5px;" name="type"
                    value="edite" class="fa-solid fa-check edit-icon"></i>Cadastrar</button>
            <br>
            <br>
            <div style="width:500px;display:none" class="alert" id="alert" role="alert"></div>
        </div>

    </form>
</div>

<!-- <div id="customDialog30dias" class="custom-dialog">
    <div class="custom-dialog-content">
        <div class="custom-dialog-header">
            <span id="customDialog30diasTitle">Atenção</span>
            <span class="close" onclick="closeDialog()">&times;</span>
        </div>
        <div class="custom-dialog-body">
            <p>Deseja realizar internação com data superior a 30 dias?</p>
        </div>
        <div class="custom-dialog-footer">
            <button class="confirm" onclick="confirmDialog(true)">Sim</button>
            <button class="cancel" onclick="confirmDialog(false)">Não</button>
        </div>
    </div>
</div> -->
<script>
function aumentarText(textareaId) {
    document.getElementById(textareaId).rows = 20;
}

function reduzirText(textareaId, originalRows) {
    document.getElementById(textareaId).rows = originalRows;
}
</script>
<script>
$(document).ready(function() {
    $('.selectpicker').selectpicker();
    $('.selectpicker').selectpicker('refresh');
    $('.selectpicker').on('loaded.bs.select', function() {
        $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
    });
});
</script>

<!-- <script src="js/scriptDataInt.js"></script> -->
<script src="js/text_cad_internacao.js"></script>
<script src="js/select_internacao.js"></script>

<script>
var btnSelected = document.querySelector("#hospital_selected");

function myFunctionSelected() {
    const select = document.querySelector("#hospital_selected");
    const selectedValue = select.value;
    const selectedText = select.options[select.selectedIndex].text;
    const inputHospital = document.querySelector("#fk_hospital_int");
    const divNome = document.querySelector("#hospitalNomeTexto");

    inputHospital.value = selectedValue;

    $("#hospital_selected").css({
        "color": "black",
        "font-weight": "bold",
        "border": "2px solid green",
        "padding-top": "3px",
        "padding-bottom": "3px",
        "line-height": "normal"
    });

    if (selectedValue !== "") {
        divNome.textContent = selectedText;
        divNome.style.display = "flex";
    } else {
        divNome.textContent = "";
        divNome.style.display = "none";
    }
}






var relatorioDetalhado = document.getElementById("#relatorio-detalhado"); //mudar cor do select qdo selecionado
$('#relatorio-detalhado').change(function() {
    var optionDetalhes = $('#relatorio-detalhado').find(":selected").text();
    // Estilo inicial = "Não"
    $("#relatorio-detalhado").css({
        "color": "white",
        "font-weight": "normal",
        "border": "1px solid #5e2363",
        "background-color": "#5e2363"
    });
    if (optionDetalhes == "Sim") {
        $("#relatorio-detalhado").css({
            "color": "black",
            "font-weight": "bold",
            "border": "2px",
            "border-color": "green",
            "border-style": "solid",
            "background-color": "#d8b4fe" // lilás claro


        });

    } else {
        $("#relatorio-detalhado").val("");
        $("#relatorio-detalhado").css({
            "color": "white",
            "font-weight": "normal",
            "border": "1px solid #5e2363",
            "background-color": "#5e2363" // lilás claro
        });
    }
});
</script>
<script>
// aparecer campo atb em uso
$(document).ready(function() {
    $('#medicacaoDet').hide(); // Oculta o campo de texto quando a página carrega

    $('#medicacao').change(function() {
        if ($(this).val() === 's') {
            $('#medicacaoDet').show();
        } else {
            $('#medicacaoDet').hide();
        }
    });
});

// aparecer campo medicacao alto custo em uso

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
        if ($(this).val() === 'Cateter' || $(this).val() == 'Mascara') {
            $('#div-oxig').show();
        } else {
            $('#div-oxig').hide();
        }
    });
});
</script>


<script>
// mostrar div de uti caso alterar acaomodacao int para UTI
document.getElementById("acomodacao_int").addEventListener("change", function() {
    var divUti = document.querySelector("#container-uti");
    if (this.value === "UTI") {
        divUti.style.display = "block";
    } else {
        divUti.style.display = "none";
    }
});
let pacienteStatus = null; // Variável global para armazenar o status do paciente

function teste() {
    event.preventDefault(); //prevent default action 
    let post_url = "check_internacao.php"; //get form action url
    let request_method = "POST"; //get form GET/POST method
    var paciente = document.querySelector("#fk_paciente_int").value;
    $.ajax({
        url: post_url,
        type: request_method,
        data: {
            id_paciente: paciente
        },
        success: function(result) {

            var alert_div = document.getElementById('alert_intern');
            if (result == 1) {
                alert_div.style.display = "block";
            } else {
                alert_div.style.display = "none";

            }
        }
    })
}
// formulario ajax para envio form sem refresh
$("#myForm").submit(function(event) {
    event.preventDefault(); // Impede o envio tradicional do formulário
    let post_url = $(this).attr("action"); // Obtém a URL de ação do formulário
    let request_method = $(this).attr("method"); // Obtém o método do formulário (GET/POST)
    let form_data = new FormData(this); // Cria um objeto FormData com os dados do formulário


    // 1. Salva o valor selecionado do select de hospitais
    const hospitalSelected = document.getElementById("hospital_selected").value;

    $.ajax({
        url: post_url,
        type: request_method,
        processData: false, // Impede o jQuery de processar os dados
        contentType: false, // Impede o jQuery de definir o contentType
        data: form_data,
        success: function(result) {

            if (3 < 4) {

                // Increment the reg_int value
                const regIntInput = $("#RegInt");
                const currentRegInt = parseInt(regIntInput.val());
                const newRegInt = currentRegInt + 1;

                regIntInput.val(newRegInt);

                // . Success alert
                $('#alert').removeClass("alert-danger").addClass("alert-success");
                $('#alert').fadeIn().html("Cadastrado com sucesso");
                setTimeout(function() {
                    $('#alert').fadeOut('Slow');
                }, 3000);

                // 2. Resetando os campos de input, select e textarea EXCETO os campos `hidden`
                document.querySelectorAll('input, select, textarea').forEach((element) => {
                    if (element.type !== "hidden" && element.id !== "hospital_selected") {
                        element.value = '';
                    }
                });

                // 3. Restaura o valor selecionado do select de hospitais
                document.getElementById("hospital_selected").value = hospitalSelected;

                // 4. Atualiza outros selects (exceto o de hospitais)
                $('#fk_paciente_int').val('').selectpicker('refresh');
                $('#fk_patologia2').val('').selectpicker('refresh');
                $('#fk_patologia_int').val('').selectpicker('refresh');

                // 5. Update other values
                const adicionarValor = parseInt(document.querySelector("#proximoId_int")
                    .textContent) + 1;
                const ultimoReg = <?= $ultimoReg ?>;
                const novoValorInternacao = parseInt(ultimoReg) + adicionarValor;

                $("#proximoId_int").text(adicionarValor);
                $("#proximoId_int").val(novoValorInternacao);

                $("#RegInt").val(newRegInt);
                $("#fk_int_tuss").val(novoValorInternacao);
                $("#fk_internacao_uti").val(novoValorInternacao);
                $("#fk_id_int").val(novoValorInternacao);
                $("#fk_internacao_pror").val(novoValorInternacao);
                $("#fk_internacao_ges").val(novoValorInternacao);
                $("#fk_int_det").val(novoValorInternacao);
                document.getElementById("internado_int").value = "s";
                document.getElementById("internado_int").querySelector("option[value='s']")
                    .selected = true;

                // 6. Hide containers
                const containers = [
                    "#container-gestao",
                    "#container-tuss",
                    "#container-prorrog",
                    "#container-uti",
                    "#container-negoc",
                    "#div-detalhado"
                ];
                containers.forEach((container) => {
                    document.querySelector(container).style.display = "none";
                });

                // 7. Restaura a borda dos selects após o reset (exceto o de hospitais)
                document.querySelectorAll(
                    "#select_tuss, #select_gestao, #relatorio-detalhado, #select_prorrog, #select_uti, #select_negoc, select"
                ).forEach(select => {
                    if (select.id !== "hospital_selected") {
                        select.value = ""; // Reseta o valor do select
                        select.style.border =
                            "1px solid #ced4da"; // Restaura a borda padrão do Bootstrap
                        select.style.color = "gray"; // Mantém a aparência de "placeholder"
                        select.style.fontWeight = "normal";
                        select.style.backgroundColor = "white"; // Remove a cor de fundo
                    }
                });

                // 8. Atualiza selects que usam Bootstrap Select (exceto o de hospitais)
                $('#select_tuss, #select_gestao, #relatorio-detalhado, #select_prorrog, #select_uti, #select_negoc')
                    .each(function() {
                        if (this.id !== "hospital_selected") {
                            $(this).selectpicker('val', ""); // Reseta o valor
                            $(this).selectpicker('refresh'); // Atualiza o componente Bootstrap
                            $(this).next('.dropdown-toggle').attr('title', 'Selecione').find(
                                '.filter-option-inner-inner').text('Selecione');
                        }
                    });

                // 9. Success alert
                $('#alert').removeClass("alert-danger").addClass("alert-success");
                $('#alert').fadeIn().html("Cadastrado com sucesso");
                setTimeout(function() {
                    $('#alert').fadeOut('Slow');
                }, 3000);

            } else if (result == '0') {

                $('#alert').removeClass("alert-success").addClass("alert-danger");
                $('#alert').fadeIn().html("Paciente possui internação ativa");
                setTimeout(function() {
                    $('#alert').fadeOut('Slow');
                }, 2000);
            }

            // Clear additional fields
            clearTussInputs();
            clearProrrogInputs();

        },

        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.log("XHR response:", xhr.responseText);
        }
    });
});

var dialogResult = false;

function checkDaysLimit(dataInternacao) {
    const dataAtual = new Date();
    const dataInt = new Date(dataInternacao);
    const diffTime = Math.abs(dataInt - dataAtual);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays > 30) {
        openDialog();
        return new Promise((resolve) => {
            const checkResult = setInterval(() => {
                if (document.getElementById("customDialog30dias").style.display === "none") {
                    clearInterval(checkResult);
                    resolve(dialogResult);
                }
            }, 100);
        });
    }
    return Promise.resolve(true);
}

document.getElementById("data_intern_int").addEventListener("blur", function() {
    const input = this;
    const dataInternacao = new Date(input.value);
    const dataHoje = new Date();
    const erroDiv = document.getElementById("erro-data-internacao");

    erroDiv.style.display = "none";
    erroDiv.textContent = "";

    if (!input.value) return;

    const dataFormatadaHoje = dataHoje.toISOString().split("T")[0];
    const dataFormatadaInput = input.value;

    // Caso a data seja futura
    if (dataFormatadaInput > dataFormatadaHoje) {
        erroDiv.textContent = "A data da internação não pode ser maior que a data atual.";
        erroDiv.style.display = "block";
        input.value = "";

        setTimeout(() => {
            erroDiv.style.display = "none";
            erroDiv.textContent = "";
        }, 5000);
        return;
    }

    // Verifica se a data está mais de 30 dias no passado
    const diffEmMilissegundos = dataHoje - dataInternacao;
    const diffDias = diffEmMilissegundos / (1000 * 60 * 60 * 24);

    if (diffDias > 30) {
        erroDiv.textContent = "Deseja prorrogar acima de 30 dias?";
        erroDiv.style.display = "block";

        setTimeout(() => {
            erroDiv.style.display = "none";
            erroDiv.textContent = "";
        }, 7000);
    }
});
</script>

<script>
$(document).ready(function() {
    // Evento de mudança para o hospital selecionado
    $('#hospital_selected').on('change', function() {

        const id_hospital = $(this).val(); // Captura o ID do hospital selecionado

        if (!id_hospital) {
            return;
        }

        // Solicitação AJAX para buscar dados filtrados
        fetchAcomodacoes(id_hospital);
    });

    // Função para realizar a requisição AJAX e preencher os selects
    function fetchAcomodacoes(id_hospital) {
        $.ajax({
            url: 'process_acomodacao.php', // Endereço do script no servidor
            type: 'POST',
            dataType: 'json',
            data: {
                id_hospital
            }, // Dados enviados ao servidor
            beforeSend: function() {

            },
            success: function(response) {

                if (response.status === 'success') {
                    const acomodacoes = response.acomodacoes;

                    // Atualiza os selects "troca_de" e "troca_para"
                    populateSelects(acomodacoes);
                } else {
                    console.error("Erro recebido do servidor:", response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro na requisição AJAX:", error);
                console.error("Status:", status);
                console.error("Resposta completa:", xhr.responseText);
            },
        });
    }


    // Função para popular os selects "troca_de" e "troca_para" com as acomodações recebidas
    function populateSelects(acomodacoes) {
        let options = '<option value="">Selecione a Acomodação</option>';
        acomodacoes.forEach(ac => {
            options +=
                `<option value="${ac.id_acomodacao}-${ac.acomodacao_aco}" data-valor="${ac.valor_aco}">${ac.acomodacao_aco}</option>`;
        });

        // Atualiza os selects com as novas opções
        $('select[name="troca_de"]').html(options);
        $('select[name="troca_para"]').html(options);

        // Limpa os campos relacionados
        $('input[name="saving"]').val('');
        $('input[name="qtd"]').val('');
        $('input[name="saving_show"]').val('').css('color', '');
    }

    // Função para calcular savings ao alterar os selects ou a quantidade
    $(document).on('change keyup', 'select[name="troca_de"], select[name="troca_para"], input[name="qtd"]',
        function() {
            calculateSavings($(this).closest('.negotiation-field-container'));
        });

    function carregarValoresTroca(container) {
        // Pega os valores selecionados dos selects
        const trocaDeOption = container.find('select[name="troca_de"] option:selected');
        const trocaParaOption = container.find('select[name="troca_para"] option:selected');

        // Extrai os valores do atributo 'data-valor'
        const trocaDe = parseFloat(trocaDeOption.data('valor')) || 0;
        const trocaPara = parseFloat(trocaParaOption.data('valor')) || 0;

        // Carrega os valores nos inputs correspondentes
        container.find('input[name="troca_de"]').val(trocaDe);
        container.find('input[name="troca_para"]').val(trocaPara);

    }

    // Função para calcular e atualizar os campos de savings
    function calculateSavings(container) {
        // Pega os selects selecionados
        const trocaDeOption = container.find('select[name="troca_de"] option:selected');
        const trocaParaOption = container.find('select[name="troca_para"] option:selected');
        const quantidadeInput = container.find('input[name="qtd"]');

        // Extraímos o valor correto do atributo 'data-valor'
        const trocaDeValor = parseFloat(trocaDeOption.attr('data-valor')) || 0;
        const trocaParaValor = parseFloat(trocaParaOption.attr('data-valor')) || 0;
        const quantidade = parseInt(quantidadeInput.val(), 10) || 0;

        // Se algum valor estiver inválido, apenas limpamos o campo e saímos
        if (isNaN(trocaDeValor) || isNaN(trocaParaValor) || isNaN(quantidade)) {
            container.find('input[name="saving"]').val('');
            container.find('input[name="saving_show"]').val('').css('color', '');
            return;
        }

        // Cálculo correto do saving
        const saving = (trocaDeValor - trocaParaValor) * quantidade;

        // Atualiza os campos de saving com o formato correto
        container.find('input[name="saving"]').val(saving.toFixed(2));
        container.find('input[name="saving_show"]').val(
            saving >= 0 ? `R$ ${saving.toFixed(2)}` : `-R$ ${Math.abs(saving).toFixed(2)}`
        ).css('color', saving >= 0 ? 'green' : 'red');
    }

});




// Exibe o container apenas quando select_prorrog for "s"
document.addEventListener("DOMContentLoaded", function() {
    const selectProrrog = document.getElementById("select_prorrog");
    const containerProrrog = document.getElementById("container-prorrog");

    if (selectProrrog) {
        selectProrrog.addEventListener("change", function() {
            if (this.value === "s") {
                containerProrrog.style.display = "block";
            } else {
                containerProrrog.style.display = "none";
            }
        });

        // Verifica o valor inicial
        if (selectProrrog.value === "s") {
            containerProrrog.style.display = "block";
        } else {
            containerProrrog.style.display = "none";
        }
    }
});
</script>
<?php if (!empty($id_paciente_get)): ?>
<script>
(function preselectPaciente() {
    var tries = 0;
    var idPac = "<?= (int)$id_paciente_get ?>";

    function apply() {
        var $sel = $('#fk_paciente_int');
        if (!$sel.length) return false;

        // Seta o valor
        $sel.val(idPac);

        // Se usa bootstrap-select, atualiza UI
        if ($sel.hasClass('selectpicker') && typeof $sel.selectpicker === 'function') {
            $sel.selectpicker('refresh');
        }

        // Dispara sua verificação de internação ativa (se existir)
        if (typeof teste === 'function') {
            try {
                teste();
            } catch (e) {
                console.warn('teste() falhou:', e);
            }
        }
        return true;
    }

    // Tenta algumas vezes até o select/BS-Select estar pronto
    (function waitUntilReady() {
        if (apply()) return;
        if (++tries < 20) return setTimeout(waitUntilReady, 100);
        console.warn('Não foi possível pré-selecionar o paciente.');
    })();
})();
</script>
<?php endif; ?>

<script>
document.getElementById("data_visita_int").addEventListener("change", function() {
    const dataInternacao = new Date(document.getElementById("data_intern_int").value);
    const dataVisita = new Date(this.value);
    const hoje = new Date();
    const seteDiasDepois = new Date();
    seteDiasDepois.setDate(hoje.getDate() + 7);

    const errorMessage = document.getElementById("error-message");

    // Reseta a mensagem de erro
    errorMessage.style.display = "none";
    errorMessage.textContent = "";

    // Validações
    if (dataVisita < dataInternacao) {
        errorMessage.textContent = "A data da visita não pode ser menor que a data de internação.";
        errorMessage.style.display = "block";
    } else if (dataVisita > seteDiasDepois) {
        errorMessage.textContent = "A data da visita não pode ser maior que 7 dias da data atual.";
        errorMessage.style.display = "block";
    }
});

// internacao pertinente
document.getElementById("tipo_admissao_int").addEventListener("change", function() {
    const tipoAdmissao = this.value;
    const divPertinente = document.getElementById("div_int_pertinente_int");
    const divRelPertinente = document.getElementById("div_rel_pertinente_int");

    // Resetando a visibilidade
    divPertinente.style.display = "none";
    divRelPertinente.style.display = "none";

    if (tipoAdmissao === "Urgência") {
        divPertinente.style.display = "block";

        document.getElementById("int_pertinente_int").addEventListener("change", function() {
            const intPertinente = this.value;

            if (intPertinente === "n") {
                divRelPertinente.style.display = "block";
            } else {
                divRelPertinente.style.display = "none";
            }
        });
    }
});

document.querySelector("form").addEventListener("submit", function(event) {
    generateNegotiationsJSON(); // Gera o JSON antes do envio

    // Remove os campos individuais antes de enviar o formulário
    const inputsToDisable = document.querySelectorAll(
        'input[name="troca_de"], input[name="troca_para"], input[name="qtd"], input[name="saving"]'
    );
    inputsToDisable.forEach((input) => input.disabled = true);
});


//criar o json de antecedentes
document.getElementById('fk_patologia2').addEventListener('change', function() {
    const selectedOptions = Array.from(this.selectedOptions).map(option => parseInt(option.value,
        10)); // Converte os valores para inteiros
    const fkPaciente = parseInt(document.getElementById('fk_paciente_int').value,
        10); // Garante que fkPaciente é inteiro
    const fkInternacao = parseInt(document.getElementById('id_internacao').value,
        10); // Garante que fkInternacao é inteiro

    const jsonAntecedentes = selectedOptions.map(idAntecedente => ({
        fk_id_paciente: fkPaciente,
        fk_internacao_ant_int: fkInternacao + 1, // Soma 1 ao valor de fkInternacao
        intern_antec_ant_int: idAntecedente // Certifica que idAntecedente é um número inteiro
    }));

    // Atualiza o campo hidden com o JSON gerado
    document.getElementById('json-antec').value = JSON.stringify(jsonAntecedentes);
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const selectInternado = document.getElementById("internado_int");
    const divDataAlta = document.getElementById("div-data-alta");
    const divMotivoAlta = document.getElementById("div-motivo-alta");

    function toggleDataAlta() {
        if (selectInternado.value === "s") {
            divDataAlta.style.display = "none"; // esconde
            divMotivoAlta.style.display = "none"; // esconde
            document.getElementById("data_alta_alt").value = ""; // limpa o valor
            document.getElementById("tipo_alta_alt").value = ""; // limpa o valor
        } else {
            divDataAlta.style.display = "block"; // mostra
            divMotivoAlta.style.display = "block"; // mostra
        }
    }

    // roda no carregamento da página
    toggleDataAlta();

    // roda quando o select mudar
    selectInternado.addEventListener("change", toggleDataAlta);
});

document.getElementById("chat-header").addEventListener("click", function() {
    const chatBody = document.getElementById("chat-body");
    chatBody.style.display = chatBody.style.display === "none" ? "block" : "none";
});

document.getElementById("chat-send").addEventListener("click", function() {
    const inputField = document.getElementById("chat-input");
    const message = inputField.value.trim();
    if (message) {
        const messagesDiv = document.getElementById("chat-messages");

        // Log da mensagem enviada
        console.log("Enviando mensagem:", message);

        fetch("diversos/chatgpt_handler.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    message
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log("Resposta recebida:", data); // Log da resposta
                const botMessage = document.createElement("div");
                botMessage.style.color = "green";
                botMessage.textContent = "Bot: " + (data.reply || "Sem resposta");
                messagesDiv.appendChild(botMessage);

                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .catch(error => {
                console.error("Erro ao conectar:", error); // Log do erro
                const errorMessage = document.createElement("div");
                errorMessage.style.color = "red";
                errorMessage.textContent = "Erro ao conectar com o bot.";
                messagesDiv.appendChild(errorMessage);
            });

        inputField.value = "";
    }
});
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>