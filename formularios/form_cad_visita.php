<?php

function fmtYmd(?string $s): ?string
{
    return !empty($s) ? date('Y-m-d', strtotime($s)) : null;
}
function fmtDmy(?string $s): string
{
    return !empty($s) ? date('d/m/Y', strtotime($s)) : '';
}

$hoje = date('Y-m-d');

// protege contra null
$visitaAnt = !empty($ultimaVis['data_visita_vis'])
    ? date("Y-m-d", strtotime($ultimaVis['data_visita_vis']))
    : null;

$intern = !empty($ultimaVis['data_intern_int'])
    ? date("Y-m-d", strtotime($ultimaVis['data_intern_int']))
    : null;

$atual = new DateTime($hoje);

// só cria DateTime se tiver valor
$visAnt     = $visitaAnt ? new DateTime($visitaAnt) : null;
$dataIntern = $intern    ? new DateTime($intern)    : null;

// diffs seguros (fallback = 0 dias)
if ($visAnt instanceof DateTime) {
    $intervaloUltimaVis = $visAnt->diff($atual);
} else {
    $intervaloUltimaVis = new DateInterval('P0D'); // 0 dias
}

if ($dataIntern instanceof DateTime) {
    $diasIntern = $dataIntern->diff($atual);
} else {
    $diasIntern = new DateInterval('P0D'); // 0 dias
}

// (Opcional) Se você preferir inteiros de dias, também pode expor:
$intervaloUltimaVisDias = $visAnt ? $visAnt->diff($atual)->days : 0;
$diasInternDias         = $dataIntern ? $dataIntern->diff($atual)->days : 0;


$visitasDAO = new visitaDAO($conn, $BASE_URL);
$internacaoDAO = new internacaoDAO($conn, $BASE_URL);
$query2DAO = new visitaDAO($conn, $BASE_URL);
$id_internacao = filter_input(INPUT_GET, "id_internacao", FILTER_SANITIZE_NUMBER_INT);
$visitas = $visitasDAO->joinVisitaInternacao($id_internacao);

$visitaMax = $internacaoDAO->selectInternVisLast(); // pegar o Id max da visita

$cargo = $_SESSION['cargo'];
if (($cargo == "Med_auditor") || ($cargo == "Enf_Auditor")) {
    $cargo;
} else {
    $cargo = null;
};
$condicoesvisita = [
    // strlen($cargo) ? ' se.cargo_user = " ' . $cargo . ' " '  : null,
    strlen($id_internacao) ? ' ac.id_internacao = ' . $id_internacao : null
];

$condicoesvisita = array_filter($condicoesvisita);
// REMOVE POSICOES VAZIAS DO FILTRO
$wherevisita = implode(' AND ', $condicoesvisita);

$ultimoReg = $visitaMax['0']['id_visita'];

$contarVis = 0; //contar numero de visitas por internacao 
$queryVis = $internacaoDAO->selectAllInternacaoCountVis($wherevisita);
$contarVis = $queryVis[0]['numero_de_id_visita'];
?>

<div class="row">
    <h4 class="w-100 position-relative text-center" style="
    background-color: #5e2363;
    color: #fff;
    padding: 13px 0;
    border-radius: 0.25rem;
">
        Cadastrar visita

        <?php if ($contarVis > 0): ?>
            <button type="button" class="btn btn-sm" style="
              position: absolute;
              right: 10px;
              top: 50%;
              transform: translateY(-50%);
              background-color: #5bd9f3;
            " data-bs-toggle="modal" data-bs-target="#myModal1">
                <i class="fas fa-eye me-2"></i>
                Visitas Anteriores
            </button>
        <?php endif; ?>
    </h4>



    <!-- </div> -->

    <form action="<?= $BASE_URL ?>process_visita.php" id="add-visita-form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="type" value="create">

        <div class="form-group row" style="margin:15px">
            <div id="view-contact-container" style="align-items:center">
                <hr>
                <span style="font-weight: 500; margin:0px 5px 0px 5px ">Reg Int:</span>
                <span
                    style="font-weight: 800; margin:0px 50px 0px 5px "><?= $internacaoList['0']['id_internacao'] ?></span>

                <!-- <span style="font-weight: 500; margin:0px 5px 0px 10px ">Reg Visita:</span>
                <span style="font-weight: 800; margin:0px 50px 0px 5px "><?= $visitaMax['0']['id_visita'] + 1 ?></span> -->

                <span class="card-title bold" style="font-weight: 500; margin:0px 5px 0px 20px">Hospital:</span>
                <span class="card-title bold"
                    style=" font-weight: 800; margin:0px 10px 0px 0px"><?= $internacaoList['0']['nome_hosp'] ?></span>
                <span style="font-weight: 500; margin:0px 5px 0px 30px">Paciente:</span>
                <span style=" font-weight: 800; margin:0px 10px 0px 0px"><?= $internacaoList['0']['nome_pac'] ?></span>
                <span style="font-weight: 500; margin:0px 5px 0px 30px">Data internação:</span>
                <span
                    style="font-weight: 800; margin:0px 80px 0px 0px"><?= date("d/m/Y", strtotime($internacaoList['0']['data_intern_int'])); ?></span>
                <span style="font-weight: 500; margin:0px 5px 0px 10px">Visita No.</span>
                <input type="text" readonly
                    style="text-align:center; font-weight:800; border: .5px solid #666666; background-color: #e0e0e0; width: 60px; border-radius: 5px;"
                    value="<?= $contarVis + 1 ?>" id="visita_no_vis" name="visita_no_vis">

                <hr>
            </div>
            <div class="form-group col-sm-2">
                <?php $agora = date('d-m-Y');
                ?>
                <label for="data_visita_vis">Data da Visita</label>
                <input type="date" value=' <?= $agora; ?>' class="form-control" id="data_visita_vis"
                    name="data_visita_vis">
                <p id="data-visita-error" style="color: red; display: none;">Data Inválida</p>

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
            <div class="form-group col-sm-3">
                <label for="retificou">Retificar Visita</label>
                <select class="form-control" id="retificou" name="retificou">
                    <option value="">Selecione a visita</option>
                    <?php foreach ((array) $visitasAntigas as $visita): ?>
                        <?php if (is_array($visita) && isset($visita['visita_no_vis'])): ?>
                            <option value="<?= $visita['visita_no_vis'] ?>">
                                Visita ID <?= $visita['visita_no_vis'] ?> -
                                <?= isset($visita['data_visita_vis']) ? DateTime::createFromFormat('Y-m-d', $visita['data_visita_vis'])->format('d/m/Y') : 'Data não informada' ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>


            <input type="hidden" value="" id="json-antec" name="json-antec">
            <input type="hidden" class="form-control" id="usuario_create" value="<?= $_SESSION['email_user'] ?>"
                name="usuario_create">
            <input type="hidden" class="form-control" class="form-control" id="fk_usuario_vis"
                value="<?= $_SESSION['id_usuario'] ?>" name="fk_usuario_vis">
            <input type="hidden" class="form-control" value="<?= $id_internacao ?>" id="fk_internacao_vis"
                name="fk_internacao_vis" placeholder="">
            <input type="hidden" id="id_hospital" name="id_hospital" value="<?= $internacaoList['0']['id_hospital'] ?>">

            <input type="hidden" class="form-control" id="fk_int_visita" name="fk_int_visita"
                value="<?= $ultimoReg + 1 ?>">

            <input type="hidden" class="form-control" id="fk_paciente_int" name="fk_paciente_int"
                value="<?= $internacaoList['0']['fk_paciente_int'] ?>">

            <input type="hidden" class="form-control" id="data_internacao" name="data_internacao"
                value="<?= date("d/m/Y", strtotime($internacaoList['0']['data_intern_int'])); ?>">
            <input type="hidden" class="form-control" id="data_intern_int" name="data_intern_int"
                value="<?= date("d/m/Y", strtotime($internacaoList['0']['data_intern_int'])); ?>">
            <div>
                <label for="rel_visita_vis">Relatório de Auditoria</label>
                <textarea type="textarea" style="resize:none" rows="2" onclick="aumentarTextAudit()"
                    class="form-control" id="rel_visita_vis" name="rel_visita_vis"></textarea>
            </div>
            <div style="margin-bottom:20px">
                <label for="acoes_int_vis">Ações da Auditoria</label>
                <textarea type="textarea" style="resize:none" rows="2" onclick="aumentarTextAcoes()"
                    class="form-control" id="acoes_int_vis" name="acoes_int_vis"></textarea>
            </div>
            <div>
                <label for="programacao_enf">Programação Terapêutica</label>
                <textarea type="textarea" style="resize:none" style="resize:none" rows="2"
                    onclick="aumentarTextProgVis()" class="form-control" id="programacao_enf"
                    name="programacao_enf"></textarea>
            </div>
            <div><br></div>

            <!--****************************************-->
            <!--************ div de detalhes ***********-->
            <!--****************************************-->
            <input type="hidden" class="form-control" id="select_detalhes" name="select_detalhes">
            <h4 class="text-center w-100"
                style="margin: 7px 10px 0px 0px;background-color: #5e2363;color: #fff;padding: 13px 0;border-radius: 0.25rem;">
                Detalhes do relatório</h4>
            <hr>
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
                            <option value=""></option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
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
                        <textarea type="textarea" style="resize:none" rows="2"
                            onclick="aumentarText('oportunidades_det')" class="form-control" id="oportunidades_det"
                            onblur="reduzirText('oportunidades_det', 3)" name="oportunidades_det"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="form-group col-sm-3">
                        <label class="control-label" for="liminar_det">Possui Liminar?</label>
                        <select class="form-control-sm form-control" id="liminar_det" name="liminar_det">
                            <option value=""></option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-3">
                        <label class="control-label" for="paliativos_det">Está em Cuidados Paliativos?</label>
                        <select class="form-control-sm form-control" id="paliativos_det" name="paliativos_det">
                            <option value=""></option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-3">
                        <label class="control-label" for="parto_det">Parto</label>
                        <select class="form-control-sm form-control" id="parto_det" name="parto_det">
                            <option value=""></option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
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

            <!-- ENTRADA DE DADOS AUTOMATICOS NO INPUT-->
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="n" id="internado_uti_int" name="internado_uti_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="n" id="internacao_uti_int" name="internacao_uti_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="s" id="internacao_ativa_int"
                    name="internacao_ativa_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="<?= ($_SESSION['id_usuario']) ?>" id="fk_usuario_vis"
                    name="fk_usuario_vis">
            </div>
            <div class="form-group col-sm-1">

                <input type="hidden" class="form-control" value="<?= ($_SESSION['cargo']) ?>" id="fk_usuario_vis"
                    name="fk_usuario_vis">
            </div>
            <!-- <div class="form-group col-sm-2">
                    <?php $agora = date('d-m-Y');
                    ?>
                    <input type="tyext" value=' <?= $agora; ?>' class="form-control" id="data_visita_vis"
                        name="data_visita_vis">
                </div> -->
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_enf_vis" name="visita_enf_vis"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                        echo 's';
                                    } else {
                                        echo 'n';
                                    }; ?>" value="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                                                                                                        echo 's';
                                                                                                                                    } else {
                                                                                                                                        echo 'n';
                                                                                                                                    }; ?>">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_med_vis" name="visita_med_vis"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                        echo 's';
                                    } else {
                                        echo 'n';
                                    }; ?>" value="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                                                                                                                        echo 's';
                                                                                                                                    } else {
                                                                                                                                        echo 'n';
                                                                                                                                    }; ?>">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_auditor_prof_enf" name="visita_auditor_prof_enf"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                        echo ($_SESSION['login_user']);
                                    }; ?>" value="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                        echo ('Enf_Auditor');
                                                    }; ?>">
            </div>

            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_auditor_prof_med" name="visita_auditor_prof_med"
                    placeholder="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                        echo ($_SESSION['login_user']);
                                    }; ?>" value="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                        echo ('Med_auditor');
                                                    }; ?>">
            </div>
            <h4 class="text-center w-100"
                style="margin: -15px 10px 0px 0px;background-color: #5e2363;color: #fff;padding: 13px 0;border-radius: 0.25rem;">
                Tabelas Adicionais</h4>
            <hr>
            <div class="form-group row d-flex justify-content-center align-items-end">
                <?php if ($_SESSION['cargo'] === 'Med_auditor' || ($_SESSION['cargo'] === 'Diretoria')) { ?>

                    <div class="form-group col-sm-2">
                        <label class="control-label" for="select_tuss">Tuss</label>
                        <select class="form-control select-purple" id="select_tuss" name="select_tuss">
                            <option value="">Selecione</option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="select_prorrog">Prorrogação</label>
                        <select class="form-control select-purple" id="select_prorrog" name="select_prorrog">
                            <option value="">Selecione</option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                <?php }; ?>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="select_gestao">Gestão</label>

                    <select class="form-control select-purple" id="select_gestao" name="select_gestao">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="select_uti">UTI</label>
                    <select class="form-control select-purple" id="select_uti" name="select_uti">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <?php if ($_SESSION['cargo'] === 'Med_auditor' || ($_SESSION['cargo'] === 'Diretoria')) { ?>

                    <div class="form-group col-sm-2">
                        <label class="control-label" for="select_negoc">Negociações</label>
                        <select class="form-control select-purple" id="select_negoc" name="select_negoc">
                            <option value="">Selecione</option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                    </div>
                <?php }; ?>

                <br>
            </div>
            <!-- FORMULARIO DE GESTÃO -->
            <?php include_once('formularios/form_cad_internacao_tuss.php'); ?>
            <!-- FORMULARIO DE GESTÃO -->

            <?php include_once('formularios/form_cad_internacao_gestao.php'); ?>

            <!-- FORMULARIO DE UTI -->
            <?php include_once('formularios/form_cad_internacao_uti.php'); ?>

            <!-- FORMULARIO DE PRORROGACOES -->
            <?php include_once('formularios/form_cad_internacao_prorrog.php'); ?>

            <!-- <FORMULARO DE NEGOCIACOES -->
            <?php include_once('formularios/form_cad_internacao_negoc.php'); ?>

            <div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Cadastrar
                </button>
            </div>
            <div style="margin-left:20px; width:500px" class="alert" id="alert" role="alert"></div>

    </form>
</div>

<!-- Modal para abrir tela de cadastro -->
<div class="modal fade" id="myModal1">
    <div class="modal-dialog  modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="page-title" style="color:white">Visitas</h4>
                <p class="page-description" style="color:white; margin-top:5px">Informações
                    sobre visitas anteriores</p>
            </div>
            <div class="modal-body">
                <?php

                if (!$visitas) {
                    echo ("<br>");
                    echo ("<p style='margin-left:100px'> <b>-- Esta internação ainda não possui visita -- </b></p>");
                    echo ("<br>");
                } else { ?>
                    <h6 class="page-title">Relatórios anteriores</h6>
                    <table class="table table-sm table-striped  table-hover table-condensed">
                        <thead>
                            <tr>
                                <th scope="col" style="width:2%">Visita</th>
                                <th scope="col" style="width:2%">Data visita</th>
                                <th scope="col" style="width:2%">Med</th>
                                <th scope="col" style="width:2%">Enf</th>
                                <th scope="col" style="width:15%">Relatório</th>
                                <th scope="col" style="width:2%">Visualizar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hoje = date('Y-m-d');
                            $atual = new DateTime($hoje);
                            foreach ($visitas as $intern):
                            ?>
                                <tr>
                                    <td scope="row"><?= $intern["id_visita"] ?></td>
                                    <td scope="row"><?= !empty($intern['data_visita_vis'])
                                                        ? date("d/m/Y", strtotime($intern['data_visita_vis']))
                                                        : date("d/m/Y", strtotime($intern['data_visita_int']));; ?>
                                    </td>
                                    <td scope="row" class="nome-coluna-table">
                                        <?php if ($intern["visita_med_vis"] == "s") { ?><span id="boot-icon" class="bi bi-check"
                                                style="font-size: 1.2rem; font-weight:800; color: rgb(0, 128, 55);"></span>
                                        <?php }; ?>
                                    </td>
                                    <td scope="row" class="nome-coluna-table">
                                        <?php if ($intern["visita_enf_vis"] == "s") { ?><span id="boot-icon" class="bi bi-check"
                                                style="font-size: 1.2rem; font-weight:800; color: rgb(0, 128, 55);"></span>
                                        <?php }; ?>
                                    </td>
                                    <td scope="row"><?= $intern['rel_visita_vis'] = !empty($intern['rel_visita_vis']) ? $intern['rel_visita_vis'] : $intern['rel_int'];
                                                    ?></td>
                                    <td><a href="<?= $BASE_URL ?>show_visita.php?id_visita=<?= $intern["id_visita"] ?>"><i
                                                style="color:green; margin-right:10px"
                                                class="aparecer-acoes fas fa-eye check-icon"></i></a>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php }; ?>
            </div>

        </div>
    </div>
</div>
<script src="js/select_visita.js"></script>
<script src="js/text_cad_visita.js"></script>
<script>
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

    const acomodacoes = <?php echo $jsonAcomodacoes; ?>;

    populateSelects(acomodacoes)

    //criar o json de antecedentes
    document.getElementById('fk_patologia2').addEventListener('change', function() {
        const selectedOptions = Array.from(this.selectedOptions).map(option => parseInt(option.value,
            10)); // Converte os valores para inteiros
        const fkPaciente = parseInt(document.getElementById('fk_paciente_int').value,
            10); // Garante que fkPaciente é inteiro
        const fkInternacao = parseInt(document.getElementById('fk_internacao_vis').value,
            10); // Garante que fkInternacao é inteiro

        const jsonAntecedentes = selectedOptions.map(idAntecedente => ({
            fk_id_paciente: fkPaciente,
            fk_internacao_ant_int: fkInternacao + 1, // Soma 1 ao valor de fkInternacao
            intern_antec_ant_int: idAntecedente // Certifica que idAntecedente é um número inteiro
        }));

        // Atualiza o campo hidden com o JSON gerado
        document.getElementById('json-antec').value = JSON.stringify(jsonAntecedentes);
    });

    // Função para calcular as diárias e validar as datas
    function calculateDiarias(container) {
        const dataAtual = new Date().toISOString().split("T")[0];
        const dataInicial = container.querySelector('[name="prorrog1_ini_pror"]').value;
        const dataInternacao = document.getElementById('data_internacao').value
        const dataFinal = container.querySelector('[name="prorrog1_fim_pror"]').value;
        const diariasField = container.querySelector('[name="diarias_1"]');
        const errorMessage = container.querySelector(".error-message");

        errorMessage.textContent = ""; // Limpa mensagens de erro

        if (dataInicial && dataFinal) {
            const inicio = new Date(dataInicial);
            const fim = new Date(dataFinal);
            const internacao = new Date(dataInternacao);

            if (inicio < internacao) {
                errorMessage.textContent = "A data inicial não pode ser menor que a data de internação.";
                errorMessage.style.display = "block";
                diariasField.value = "";
                return;
            }

            if (fim < inicio) {
                errorMessage.textContent = "A data final não pode ser menor que a data inicial.";
                errorMessage.style.display = "block";
                diariasField.value = "";
                return;
            }

            if (fim > new Date(dataAtual)) {
                errorMessage.textContent = "A data final não pode ser maior que a data atual.";
                errorMessage.style.display = "block";
                diariasField.value = "";
                return;
            }

            const diffTime = Math.abs(fim - inicio);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            diariasField.value = diffDays;
            errorMessage.style.display = "none";
        }
    }

    // Adiciona listeners para validação automática ao alterar as datas
    document.getElementById("fieldsContainer").addEventListener("input", (event) => {
        const fieldContainer = event.target.closest(".field-container");
        if (fieldContainer) {
            calculateDiarias(fieldContainer);
        }
    });
</script>


<script>
    var text_exames = document.querySelector("#exames_enf");

    function aumentarTextExames() {
        if (text_exames.rows == "2") {
            text_exames.rows = "30"
        } else {
            text_exames.rows = "2"
        }
    }

    // mudar linhas da oportunidades 
    var text_oport = document.querySelector("#oportunidades_enf");

    function aumentarTextOport() {
        if (text_oport.rows == "2") {
            text_oport.rows = "30"
        } else {
            text_oport.rows = "2"
        }
    }

    // mudar linhas da programacao 
    var text_programacao = document.querySelector("#programacao_enf");

    function aumentarTextProgramacao() {
        if (text_programacao.rows == "2") {
            text_programacao.rows = "30"
        } else {
            text_programacao.rows = "2"
        }
    }
</script>
<style>
    .modal-backdrop {
        display: none;

    }

    .modal {
        background: rgba(0, 0, 0, 0.5);

    }

    .modal-header {
        color: white;
        background: #35bae1;


    }
</style>
<script>
    const dataVisitaInput = document.getElementById('data_visita_vis');
    const dataVisitaError = document.getElementById('data-visita-error');
    const dataInternacaoVis = new Date(
        '<?= date('Y-m-d', strtotime($ultimaVis['data_intern_int'])); ?>'); // Data da internação
    const hoje = new Date(); // Data atual

    dataVisitaInput.addEventListener('change', () => {
        const dataVisita = new Date(dataVisitaInput.value);

        if (dataVisita < dataInternacaoVis || dataVisita > hoje) {
            dataVisitaError.style.display = 'block'; // Exibe o alerta
        } else {
            dataVisitaError.style.display = 'none'; // Oculta o alerta
        }
    });

    // Oculta o alerta ao clicar no campo
    dataVisitaInput.addEventListener('click', () => {
        dataVisitaError.style.display = 'none';
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const select = document.getElementById("retificou");

        if (!select.value) {
            // Data no formato 'dd/mm/yyyy' para exibir
            const hoje = new Date();
            const dia = String(hoje.getDate()).padStart(2, '0');
            const mes = String(hoje.getMonth() + 1).padStart(2, '0');
            const ano = hoje.getFullYear();
            const dataExibicao = `${dia}/${mes}/${ano}`;
            // Correct format: yyyy-MM-dd
            const dataValor = `${ano}-${mes}-${dia}`;
            console.log(dataValor); // Verifica o formato da data
            // Cria a nova opção
            const novaOption = document.createElement("option");
            novaOption.value = dataValor; // agora está no formato correto!
            novaOption.text = `Data Atual - ${dataExibicao}`;

            // Adiciona e seleciona
            select.add(novaOption);
            select.value = dataValor;
        }
    });
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>


<!-- <script src="js/text_cad_internacao.js"></script>
<script src="js/select_internacao.js"></script> -->