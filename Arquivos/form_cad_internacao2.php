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

?>
<link href="<?php $BASE_URL ?>css/style.css" rel="stylesheet">

<div class="row">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <div class="form-group row">
        <h4 class="page-title">Cadastrar internação</h4>
        <hr>
        <div class="form-group col-sm-3">
            <label class="control-label" for="hospital_selected"><span style="color: red; ">*</span> Hospital </label>

            <select onchange="myFunctionSelected()" onchange="hospitalChanged(this.value)"
                style=" border: 1px;border-color:gray;border-style:solid; font-size:small; color: gray; margin-top:8px;"
                class="form-select botao_select" id="hospital_selected" name="hospital_selected" required>
                <option value="<?= $hospital["nome_hosp"] ?? "Selecione o Hospital" ?>">Selecione o Hospital</option>
                <?php
                foreach ($listHopitaisPerfil as $hospital): ?>
                    <option value="<?= htmlspecialchars($hospital['id_hospital']); ?>"><?= $hospital["nome_hosp"] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="hidden" id="RegInt" readonly class="form-control" name="RegInt" value="<?= $ultimoReg ?>">
        <hr>
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
        <input type="hidden" id="fk_user_uti" value="<?= $_SESSION['id_usuario'] ?>" name="fk_user_uti">
        <div class=" form-group row">
            <input type="hidden" value="<?= $hospital["id_hospital"] ?>" name="fk_hospital_int" id="fk_hospital_int">

            <div class="form-group col-sm-3" style="margin-bottom:-5px">
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
                        href="<?= $BASE_URL ?>cad_paciente.php?id_estipulante=<?= $id_estipulante ?? 0 ?>">
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
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="hora_intern_int">Hora</label>
                <input type="time" class="form-control form-control-sm" id="hora_intern_int" value=""
                    name="hora_intern_int">
            </div>
            <div class="form-group col-sm-2">
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

            <div class="form-group col-sm-2">
                <label for="usuario_create_int">Usuário</label>
                <input type="text" class="form-control form-control-sm" id="usuario_create_int"
                    value="<?= $_SESSION['email_user'] ?>" name="usuario_create_int" readonly>
            </div>

            <input type="hidden" id="id_internacao" readonly class="form-control" name="id_internacao"
                value="<?= $ultimoReg ?>">
            <!-- ENTRADA DE DADOS AUTOMATICOS NO INPUT-->
            <input type="hidden" value="s" id="primeira_vis_int" name="primeira_vis_int">
            <input type="hidden" value="0" id="visita_no_int" name="visita_no_int">
            <input type="hidden" id="visita_enf_int" name="visita_enf_int" placeholder="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                                                            echo 's';
                                                                                        } else {
                                                                                            echo 'n';
                                                                                        }; ?>" value="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                                                                            echo 's';
                                                                                                        } else {
                                                                                                            echo 'n';
                                                                                                        }; ?>">


            <input type="hidden" id="visita_med_int" name="visita_med_int" placeholder="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                                                            echo 's';
                                                                                        } else {
                                                                                            echo 'n';
                                                                                        }; ?>" value="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                                                                                            echo 's';
                                                                                                        } else {
                                                                                                            echo 'n';
                                                                                                        }; ?>">


            <input type="hidden" id="visita_auditor_prof_enf" name="visita_auditor_prof_enf" placeholder="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                                                                                echo ($_SESSION['email_user']);
                                                                                                            }; ?>"
                value="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                            echo ($_SESSION['email_user']);
                        }; ?>">
            <input type="hidden" id="visita_auditor_prof_med" name="visita_auditor_prof_med" placeholder="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                                                                                echo ($_SESSION['email_user']);
                                                                                                            }; ?>"
                value="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                            echo ($_SESSION['email_user']);
                        }; ?>">

        </div>
        <div class="row" style="margin-top:-10px">
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
                <input type="text" class="form-control form-control-sm" id="titular_int" name="titular_int">
            </div>
            <div class="form-group col-sm-1">
                <label for="crm_int">CRM</label>
                <input type="text" class="form-control form-control-sm" id="crm_int" name="crm_int">
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

            <div class="form-group col-sm-2">
                <label class="control-label" for="origem_int">Origem</label>
                <select class=" form-control-sm form-control" id="origem_int" name="origem_int">
                    <option value="">Selecione</option>
                    <?php foreach ($origem as $origens): ?>
                        <option value="<?= $origens ?>"><?= $origens ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label for="senha_int">Senha</label>
                <input type="text" class="form-control form-control-sm" id="senha_int" name="senha_int">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="fk_patologia2">Antecedente</label>
                <select class="form-control-sm  form-control selectpicker show-tick" data-size="5"
                    data-live-search="true" id="fk_patologia2" name="fk_patologia2">
                    <option value="">Selecione</option>
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
        </div>

        <div>
            <br>
        </div>
        <div class="form-group " style="margin-left:0px; margin-top:-15px">
            <div>
                <label for="rel_int">Relatório de Auditoria</label>
                <textarea type="textarea" style="resize:none" rows="2" onclick="aumentarTextAudit()"
                    class="form-control" id="rel_int" name="rel_int"></textarea>
            </div>
            <div>
                <label for="acoes_int">Ações da Auditoria</label>
                <textarea rows="2" style="resize:none" onclick="aumentarTextAcoes()" type="textarea"
                    class="form-control" id="acoes_int" name="acoes_int"></textarea>
            </div>
            <div>
                <label for="programacao_int">Programação Terapêutica</label>
                <textarea type="textarea" style="resize:none" rows="2" onclick="aumentarTextProgInt()"
                    class="form-control" id="programacao_int" name="programacao_int"></textarea>
            </div>
            <div><br></div>

            <!--****************************************-->
            <!--************ div de detalhes ***********-->
            <!--****************************************-->
            <input type="hidden" class="form-control" id="select_detalhes" name="select_detalhes">

            <div class="form-group col-sm-2" style=" margin-top:-15px">
                <label class="control-label" style="font-weight: bold;" for="relatorio-detalhado">Relatório
                    detalhado</label>
                <select class="form-control-sm form-control" id="relatorio-detalhado" name="relatorio-detalhado">
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
        <div id="div-detalhado" class="form-group row">
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
                            <input class="form-check-input " type="checkbox" name="tqt_det" id="tqt_det" value="TQT">
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


            <div class="form-group row">
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
                    <select class="form-control-sm form-control" id="medicacao" name="medic_alto_custo_det">
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
                    <textarea type="textarea" style="resize:none" rows="3" onclick="aumentarText('exames_det')"
                        onblur="reduzirText('exames_det', 3)" class="form-control" id="exames_det"
                        name="exames_det"></textarea>
                </div>
                <div>
                    <label for="oportunidades_det">Oportunidades</label>
                    <textarea type="textarea" style="resize:none" rows="2" onclick="aumentarText('oportunidades_det')"
                        class="form-control" id="oportunidades_det" onblur="reduzirText('oportunidades_det', 3)"
                        name="oportunidades_det"></textarea>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group col-sm-3">
                    <label class="control-label" for="liminar_det">Possui Liminar?</label>
                    <select class="form-control-sm form-control" id="liminar_det" name="liminar_det">
                        <option value="n">Não</option>
                        <option value="s">Sim</option>
                    </select>
                </div>
                <div class="form-group col-sm-3">
                    <label class="control-label" for="paliativos_det">Está em Cuidados Paliativos?</label>
                    <select class="form-control-sm form-control" id="paliativos_det" name="paliativos_det">
                        <option value="n">Não</option>
                        <option value="s">Sim</option>
                    </select>
                </div>
                <div class="form-group col-sm-3">
                    <label class="control-label" for="parto_det">Parto</label>
                    <select class="form-control-sm form-control" id="parto_det" name="parto_det">
                        <option value="n">Não</option>
                        <option value="s">Sim</option>
                    </select>
                </div>
                <div class="form-group col-sm-3">
                    <label class="control-label" for="braden_det">Escala de Braden</label>
                    <select class="form-control-sm form-control" id="braden_det" name="braden_det">
                        <option value=""></option>
                        <option value="alto">Alto</option>
                        <option value="moderado">Moderado</option>
                        <option value="baixo">Baixo</option>
                    </select>
                </div>
            </div>
            <div>
                <hr>
            </div>
        </div>
</div>

<div class="form-group row">
    <?php
    if ($_SESSION['cargo'] === 'Med_auditor' || ($_SESSION['cargo'] === 'Diretoria')) { ?>
        <div class="form-group col-sm-2">
            <label class="control-label" style="font-weight: bold;" for="select_tuss">Tuss</label>
            <select class="form-control-sm form-control" id="select_tuss" name="select_tuss">
                <option value="">Selecione</option>
                <option value="s">Sim</option>
                <option value="n">Não</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="font-weight: bold;" for="select_prorrog">Prorrogação</label>
            <select class="form-control-sm form-control" id="select_prorrog" name="select_prorrog">
                <option value="">Selecione</option>
                <option value="s">Sim</option>
                <option value="n">Não</option>
            </select>
        </div>
    <?php } ?>

    <div class="form-group col-sm-2">
        <label class="control-label" style="font-weight: bold;" for="select_gestao">Gestão</label>
        <select class="form-control-sm form-control" id="select_gestao" name="select_gestao">
            <option value=" ">Selecione</option>
            <option value="s">Sim</option>
            <option value="n">Não</option>
        </select>
    </div>

    <div class="form-group col-sm-2">
        <label class="control-label" style="font-weight: bold;" for="select_uti">UTI</label>
        <select class="form-control-sm form-control" id="select_uti" name="select_uti">
            <option value="">Selecione</option>
            <option value="s">Sim</option>
            <option value="n">Não</option>
        </select>
    </div>
    <?php
    if ($_SESSION['cargo'] === 'Med_auditor' || ($_SESSION['cargo'] === 'Diretoria')) { ?>
        <div class="form-group col-sm-2">
            <label class="control-label" style="font-weight: bold;" for="select_negoc">Negociações</label>
            <select class="form-control-sm form-control" id="select_negoc" name="select_negoc">
                <option value="">Selecione</option>
                <option value="s">Sim</option>
                <option value="n">Não</option>
            </select>
        </div>
    <?php } ?>

</div>

<input type="hidden" class="form-control" value="<?= $ultimoReg ?>" id="fk_int_capeante" name="fk_int_capeante">
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
        <input type="file" class="form-control" name="intern_files[]" id="intern_files" accept="image/png, image/jpeg"
            multiple>
        <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
    </div>
</div>

<div>
    <hr>
    <button type="submit" class="btn btn-success"><i style="font-size: 1rem;margin-right:5px;" name="type" value="edite"
            class="fa-solid fa-check edit-icon"></i>Cadastrar</button>
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

    $(document).ready(function() {
        $('.selectpicker').selectpicker();
        $('.selectpicker').selectpicker('refresh');
        $('.selectpicker').on('loaded.bs.select', function() {
            $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
        });
    });

    $(document).ready(function() {
        $('.selectpicker').selectpicker();
        $('.selectpicker').selectpicker('refresh');
        $('.selectpicker').on('loaded.bs.select', function() {
            $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
        });
    });
</script>

<!-- <script src="js/scriptDataInt.js"></script> -->
<script src="<?= $BASE_URL ?>js/text_cad_internacao.js"></script>
<script src="js/select_internacao.js"></script>

<script>
    var btnSelected = document.querySelector("#hospital_selected");

    function myFunctionSelected() {
        var btnSelected = document.querySelector("#hospital_selected").value;
        var btnSelectedCx = document.querySelector("#hospital_selected");
        // var textoSelecao = document.querySelector("#texto-selecao");
        const btnHospital = document.querySelector("#fk_hospital_int");
        btnHospital.value = btnSelected;
        $("#hospital_selected").css({
            "color": "black",
            "font-weight": "bold",
            "border": "2px",
            "border-color": "green",
            "border-style": "solid"

        });
        // textoSelecao.textContent = "Você está lançando dados da Internação no Hospital";
    }

    var relatorioDetalhado = document.getElementById("#relatorio-detalhado"); //mudar cor do select qdo selecionado
    $('#relatorio-detalhado').change(function() {
        var optionDetalhes = $('#relatorio-detalhado').find(":selected").text();

        if (optionDetalhes == "Sim") {
            $("#relatorio-detalhado").css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px",
                "border-color": "green",
                "border-style": "solid"

            });

        } else {
            $("#relatorio-detalhado").val("");
            $("#relatorio-detalhado").css({
                "color": "gray",
                "font-weight": "normal",
                "border": "1px",
                "border-color": "gray",

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
    var ultimoReg = <?php echo $ultimoReg ?>;
    // formulario ajax para envio form sem refresh
    $("#myForm").submit(function(event) {
        event.preventDefault(); //prevent default action 
        let post_url = $(this).attr("action"); //get form action url
        let request_method = $(this).attr("method"); //get form GET/POST method
        let form_data = new FormData(this); // 'this' refers to the form

        var fk_int_capeante_js = document.querySelector("#fk_int_capeante");
        $.ajax({
            url: post_url,
            type: request_method,
            processData: false, // Prevent jQuery from processing data
            contentType: false, // Prevent jQuery from setting contentType
            data: form_data,
            success: function(result) {
                console.log(result)
                if (result == '1') {

                    // Zera todos os inputs e selects da página form_cad_internacao_prorrog
                    document.querySelectorAll('#container-prorrog input, #container-prorrog select')
                        .forEach((element) => {
                            if (element.tagName === 'SELECT') {
                                element.value = ''; // Reseta selects
                            } else {
                                element.value = ''; // Reseta inputs
                            }
                        });

                    $('form').trigger("reset");
                    $('#fk_paciente_int').val('').selectpicker('refresh');
                    $('#fk_patologia2').val('').selectpicker('refresh');
                    $('#fk_patologia_int').val('').selectpicker('refresh');

                    $('#fk_paciente_int').val(null).trigger('change');
                    $('#fk_patologia2').val(null).trigger('change');
                    $('#fk_patologia_int').val(null).trigger('change');

                    var valorInternacao = parseInt($("#id_internacao").val());
                    var valorRegInt = parseInt($("#RegInt").val());
                    var valorInternacaoTuss = parseInt($("#fk_int_tuss").val());
                    var valorInternacaoUTI = parseInt($("#fk_internacao_uti").val());
                    var valorInternacaoNegoc = parseInt($("#fk_id_int").val());
                    var valorInternacaoPror = parseInt($("#fk_internacao_pror").val());
                    var valorInternacaoGes = parseInt($("#fk_internacao_ges").val());
                    var valorIntDetalhes = parseInt($("#fk_int_det").val());

                    var adicionarValor = document.querySelector("#proximoId_int").textContent;
                    adicionarValor = parseInt(adicionarValor) + 1;
                    $('#proximoId_int').text(adicionarValor);
                    var ultimoReg = <?php echo $ultimoReg ?>;

                    var novoValorInternacao = parseInt(ultimoReg) + adicionarValor;
                    $("#RegInt").val(novoValorInternacao);
                    $("#proximoId_int").val(novoValorInternacao);

                    // Atualiza o valor do input com o novo valor
                    $("#id_internacao").val(novoValorInternacao);
                    $("#fk_int_tuss").val(novoValorInternacao);
                    $("#fk_internacao_uti").val(novoValorInternacao);
                    $("#fk_id_int").val(novoValorInternacao);
                    $("#RegInt").val(novoValorInternacao);
                    $("#fk_internacao_pror").val(novoValorInternacao);
                    $("#fk_internacao_ges").val(novoValorInternacao);
                    $("#fk_int_det").val(novoValorInternacao);

                    var divGes = document.querySelector("#container-gestao");
                    var divTuss = document.querySelector("#container-tuss");
                    var divPro = document.querySelector("#container-prorrog");
                    var divUti = document.querySelector("#container-uti");
                    var divNeg = document.querySelector("#container-negoc");
                    var divDet = document.querySelector("#div-detalhado");

                    divUti.style.display = "none";
                    divTuss.style.display = "none";
                    divPro.style.display = "none";
                    divGes.style.display = "none";
                    divNeg.style.display = "none";
                    divDet.style.display = "none";

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

                    var select_detalhes = document.getElementById("#relatorio-detalhado");
                    $("#relatorio-detalhado").val("");
                    $("#relatorio-detalhado").css({
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
                    $('#alert').removeClass("alert-danger");
                    $('#alert').addClass("alert-success");
                    $('#alert').fadeIn().html("Cadastrado com sucesso");
                    setTimeout(function() {
                        $('#alert').fadeOut('Slow');
                    }, 2000);
                } else if (result == '0') {
                    $('#alert').removeClass("alert-success");
                    $('#alert').addClass("alert-danger");
                    $('#alert').fadeIn().html("Paciente possui internação ativa");
                    setTimeout(function() {
                        $('#alert').fadeOut('Slow');
                    }, 2000);

                }
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

    document.getElementById("data_intern_int").addEventListener("blur", async function() {
        const dataInternacao = this.value;

        const limitCheck = await checkDaysLimit(dataInternacao);
        if (!limitCheck) {
            this.value = "";
        }
    });
</script>

<script>
    // Quando o hospital é selecionado, envie uma solicitação AJAX
    // $('#hospital_selected').on('change', function() {
    //     var hospitalId = $(this).val();
    //     $.ajax({
    //         url: 'process_filter_acomodacao.php',
    //         type: 'POST',
    //         data: {
    //             hospital: hospitalId
    //         },
    //         success: function(data) {
    //             // Atualiza todos os campos com name="troca_de"
    //             $('select[name="troca_de"]').html(data);
    //             $('select[name="troca_para"]').html(data);

    //             // Limpa todos os campos saving e quantidade
    //             $('input[name="saving"]').val('');
    //             $('input[name="qtd"]').val('');

    //             document.querySelectorAll('input[name="saving"]').forEach((field) => {
    //                 field.value = '';
    //             });
    //             document.querySelectorAll('input[name="qtd"]').forEach((field) => {
    //                 field.value = '';
    //             });

    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Erro ao buscar acomodações:', error);
    //         }
    //     });
    // });
    // let hospitalId = ''; // Variável global para armazenar o valor

    // Função para calcular o saving

    $(document).ready(function() {
        // Evento de mudança para o hospital selecionado
        $('#hospital_selected').on('change', function() {

            const id_hospital = $(this).val(); // Captura o ID do hospital selecionado

            if (!id_hospital) {
                console.warn("Nenhum hospital foi selecionado.");
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
                    `<option value="${ac.id_acomodacao}" data-valor="${ac.valor_aco}">${ac.acomodacao_aco}</option>`;
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
            const trocaDeOption = container.find('select[name="troca_de"] option:selected');
            const trocaParaOption = container.find('select[name="troca_para"] option:selected');
            const quantidadeInput = container.find('input[name="qtd"]');

            const trocaDe = trocaDeOption.data('valor') || 0; // Pega o valor ou define como 0
            const trocaPara = trocaParaOption.data('valor') || 0;
            const quantidade = parseInt(quantidadeInput.val(), 10) || 0;

            // Atualiza os inputs com os valores corretos
            container.find('input[name="troca_de"]').val(trocaDe);
            container.find('input[name="troca_para"]').val(trocaPara);

            // Valida se os valores são numéricos e maiores que zero
            if (isNaN(trocaDe) || isNaN(trocaPara) || isNaN(quantidade)) {
                console.warn("Dados inválidos: trocaDe, trocaPara ou quantidade");
                container.find('input[name="saving"]').val('');
                container.find('input[name="saving_show"]').val('').css('color', '');
                return;
            }

            // Calcula o saving
            const saving = (trocaDe - trocaPara) * quantidade;
            // Atualiza os campos de saving
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