<?php

function fmtYmd(?string $s): ?string
{
    return !empty($s) ? date('Y-m-d', strtotime($s)) : null;
}
function fmtDmy(?string $s): string
{
    return !empty($s) ? date('d/m/Y', strtotime($s)) : '';
}

$idSessao = (int)($_SESSION['id_usuario'] ?? 0);
$cargoSessao = $_SESSION['cargo'] ?? ($_SESSION['cargo_user'] ?? '');

include_once("dao/usuarioDao.php");
$usuarioDao = new UserDAO($conn, $BASE_URL);

$normCargoSessao = mb_strtolower(str_replace([' ', '-'], '_', (string)$cargoSessao), 'UTF-8');
$isMedSessao = strpos($normCargoSessao, 'med') === 0;
$isEnfSessao = strpos($normCargoSessao, 'enf') === 0;
$mostrarCadastroCentral = !($isMedSessao || $isEnfSessao);

$medicosAud = [];
$enfsAud = [];
try {
    $todos = $usuarioDao->findMedicosEnfermeiros();
    if (!is_array($todos)) $todos = [];
    foreach ($todos as $u) {
        $id = (int)($u['id_usuario'] ?? 0);
        if (!$id) continue;
        $cargo = (string)($u['cargo_user'] ?? '');
        $row = [
            'id_usuario'   => $id,
            'usuario_user' => (string)($u['usuario_user'] ?? ('#' . $id)),
            'cargo_user'   => $cargo,
        ];
        $cargoUpper = mb_strtoupper($cargo, 'UTF-8');
        if (strpos($cargoUpper, 'MED') === 0) {
            $medicosAud[] = $row;
        } elseif (strpos($cargoUpper, 'ENF') === 0) {
            $enfsAud[] = $row;
        }
    }
} catch (Throwable $e) {
    $medicosAud = $enfsAud = [];
}

$defaultVisitaMed = $isMedSessao ? 's' : 'n';
$defaultVisitaEnf = $isEnfSessao ? 's' : 'n';
$defaultAuditorMed = $isMedSessao ? $idSessao : '';
$defaultAuditorEnf = $isEnfSessao ? $idSessao : '';

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
        <input type="hidden" name="timer_vis" id="timer_vis" value="">

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
                <?php
                // Alterado de 'd-m-Y' para 'Y-m-d' para funcionar nos inputs type="date"
                $agora = date('Y-m-d');
                $agoraLanc = $agora;
                ?>
                <label for="data_visita_vis">Data da Visita</label>

                <input type="date" value="<?= $agora; ?>" class="form-control" id="data_visita_vis"
                    name="data_visita_vis">

                <p id="data-visita-error" style="color: red; display: none;">Data Inválida</p>
            </div>

            <div class="form-group col-sm-3">
                <label for="data_lancamento_vis">Data do lançamento</label>
                <input type="date" value="<?= $agoraLanc; ?>" class="form-control"
                    id="data_lancamento_vis" name="data_lancamento_vis" readonly tabindex="-1"
                    onfocus="this.blur();" onkeydown="return false;" style="cursor:not-allowed;">
                <small class="text-muted">Definida automaticamente pelo sistema.</small>
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

            <div class="form-group col-sm-4">
                <label class="control-label" for="fk_patologia2">Antecedentes do paciente</label>
                <select class="form-control selectpicker show-tick" data-live-search="true" data-size="6"
                    id="fk_patologia2" name="fk_patologia2[]" multiple title="Selecione os antecedentes">
                    <?php
                    $listaAntecedentes = is_array($antecedentes) ? $antecedentes : [];
                    usort($listaAntecedentes, function ($a, $b) {
                        $nomeA = isset($a["antecedente_ant"]) ? (string) $a["antecedente_ant"] : '';
                        $nomeB = isset($b["antecedente_ant"]) ? (string) $b["antecedente_ant"] : '';
                        return strcmp($nomeA, $nomeB);
                    });
                    $antecSelecionados = isset($antecedentesInternacaoIds) ? $antecedentesInternacaoIds : [];
                    foreach ($listaAntecedentes as $antecedente):
                        $idAntecedente = (int) ($antecedente["id_antecedente"] ?? 0);
                        if ($idAntecedente <= 0) {
                            continue;
                        }
                        $nomeAntecedente = $antecedente["antecedente_ant"] ?? '';
                        $selected = in_array($idAntecedente, $antecSelecionados, true) ? 'selected' : '';
                        ?>
                    <option value="<?= $idAntecedente ?>" <?= $selected ?>>
                        <?= htmlspecialchars($nomeAntecedente) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Use este campo para vincular antecedentes já cadastrados.</small>
            </div>


            <input type="hidden" value="" id="json-antec" name="json-antec">
            <input type="hidden" value="" id="id_visita_edit" name="id_visita_edit">
            <input type="hidden" class="form-control" id="usuario_create" value="<?= $_SESSION['email_user'] ?>"
                name="usuario_create">
            <input type="hidden" class="form-control" id="fk_usuario_vis" value="<?= $idSessao ?>"
                name="fk_usuario_vis">
            <input type="hidden" class="form-control" id="visita_med_vis" name="visita_med_vis"
                value="<?= $defaultVisitaMed ?>">
            <input type="hidden" class="form-control" id="visita_enf_vis" name="visita_enf_vis"
                value="<?= $defaultVisitaEnf ?>">
            <input type="hidden" class="form-control" id="visita_auditor_prof_med" name="visita_auditor_prof_med"
                value="<?= $defaultAuditorMed ?>">
            <input type="hidden" class="form-control" id="visita_auditor_prof_enf" name="visita_auditor_prof_enf"
                value="<?= $defaultAuditorEnf ?>">
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
            <?php if ($mostrarCadastroCentral): ?>
            <div class="w-100 my-3 p-3 border rounded" id="cadastro-central-visita"
                style="border-color:#8a2be2 !important;">
                <div class="fw-semibold text-primary mb-2" style="color:#5e2363 !important;">
                    Cadastro Central ativo
                    <small class="text-muted ms-2">(selecione o profissional responsável pela visita)</small>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label" for="visita_resp_tipo">Tipo de responsável</label>
                        <select id="visita_resp_tipo" class="form-select form-select-sm">
                            <option value="">(sem seleção)</option>
                            <option value="med">Médico auditor</option>
                            <option value="enf">Enfermeiro auditor</option>
                        </select>
                    </div>
                    <div class="col-sm-4 d-none" id="box_visita_resp_med">
                        <label class="form-label" for="visita_resp_med_id">Selecionar médico</label>
                        <select id="visita_resp_med_id" class="form-select form-select-sm">
                            <option value="">Selecione</option>
                            <?php foreach ($medicosAud as $med): ?>
                            <option value="<?= (int)$med['id_usuario'] ?>">
                                <?= htmlspecialchars($med['usuario_user'] ?? ('#' . $med['id_usuario'])) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4 d-none" id="box_visita_resp_enf">
                        <label class="form-label" for="visita_resp_enf_id">Selecionar enfermeiro</label>
                        <select id="visita_resp_enf_id" class="form-select form-select-sm">
                            <option value="">Selecione</option>
                            <?php foreach ($enfsAud as $enf): ?>
                            <option value="<?= (int)$enf['id_usuario'] ?>">
                                <?= htmlspecialchars($enf['usuario_user'] ?? ('#' . $enf['id_usuario'])) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div>
                <label for="rel_visita_vis">Relatório de Auditoria</label>
                <textarea type="textarea" style="resize:none" rows="2" onclick="aumentarTextAudit()"
                    class="form-control" id="rel_visita_vis" name="rel_visita_vis" autocomplete="off"
                    autocorrect="off" autocapitalize="none" spellcheck="false"></textarea>
            </div>
            <div style="margin-bottom:20px">
                <label for="acoes_int_vis">Ações da Auditoria</label>
                <textarea type="textarea" style="resize:none" rows="2" onclick="aumentarTextAcoes()"
                    class="form-control" id="acoes_int_vis" name="acoes_int_vis" autocomplete="off"
                    autocorrect="off" autocapitalize="none" spellcheck="false"></textarea>
            </div>
            <div>
                <label for="programacao_enf">Programação Terapêutica</label>
                <textarea type="textarea" style="resize:none" style="resize:none" rows="2"
                    onclick="aumentarTextProgVis()" class="form-control" id="programacao_enf"
                    name="programacao_enf" autocomplete="off" autocorrect="off" autocapitalize="none"
                    spellcheck="false"></textarea>
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
                            name="exames_det" autocomplete="off" autocorrect="off" autocapitalize="none"
                            spellcheck="false"></textarea>
                    </div>
                    <div>
                        <label for="oportunidades_det">Oportunidades</label>
                        <textarea type="textarea" style="resize:none" rows="2"
                            onclick="aumentarText('oportunidades_det')" class="form-control" id="oportunidades_det"
                            onblur="reduzirText('oportunidades_det', 3)" name="oportunidades_det" autocomplete="off"
                            autocorrect="off" autocapitalize="none" spellcheck="false"></textarea>
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
                            <th scope="col" style="width:2%">Editar</th>
                            <th scope="col" style="width:2%">Remover</th>
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
                            <td class="text-center">
                                <button type="button" class="btn btn-link p-0 text-primary"
                                    onclick="selecionarVisitaParaEditar(<?= (int) $intern['id_visita'] ?>)"
                                    title="Editar esta visita">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                <?php if (empty($intern['retificado'])): ?>
                                <button type="button" class="btn btn-link p-0 text-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalDeleteVisitaList"
                                    data-visita-id="<?= (int) $intern['id_visita'] ?>" title="Remover esta visita">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
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
<div class="modal fade" id="modalDeleteVisitaList" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Remover visita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente deletar esta visita?</p>
                <div class="alert alert-danger d-none js-delete-feedback" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" data-action="confirm-delete-row">Remover</button>
            </div>
        </div>
    </div>
</div>
<script src="js/select_visita.js"></script>
<script src="js/text_cad_visita.js"></script>
<script>
(function() {
    const fkInput = document.getElementById('fk_usuario_vis');
    if (!fkInput) return;

    const respTipo = document.getElementById('visita_resp_tipo');
    const boxMed = document.getElementById('box_visita_resp_med');
    const boxEnf = document.getElementById('box_visita_resp_enf');
    const selectMed = document.getElementById('visita_resp_med_id');
    const selectEnf = document.getElementById('visita_resp_enf_id');
    const flagMed = document.getElementById('visita_med_vis');
    const flagEnf = document.getElementById('visita_enf_vis');
    const auditorMed = document.getElementById('visita_auditor_prof_med');
    const auditorEnf = document.getElementById('visita_auditor_prof_enf');

    const sessionId = "<?= $idSessao ?>";
    const isMedSessao = <?= $isMedSessao ? 'true' : 'false' ?>;
    const isEnfSessao = <?= $isEnfSessao ? 'true' : 'false' ?>;

    function applySelection(userId, tipo) {
        if (fkInput) fkInput.value = userId || '';
        if (flagMed) flagMed.value = (tipo === 'med') ? 's' : (isMedSessao && !tipo ? 's' : 'n');
        if (flagEnf) flagEnf.value = (tipo === 'enf') ? 's' : (isEnfSessao && !tipo ? 's' : 'n');
        if (auditorMed) auditorMed.value = (tipo === 'med') ? userId : (isMedSessao && !tipo ? sessionId : '');
        if (auditorEnf) auditorEnf.value = (tipo === 'enf') ? userId : (isEnfSessao && !tipo ? sessionId : '');
    }

    function resetToSession() {
        if (isMedSessao) {
            applySelection(sessionId, 'med');
        } else if (isEnfSessao) {
            applySelection(sessionId, 'enf');
        } else {
            applySelection(sessionId, '');
        }
    }

    function hide(el) {
        if (!el) return;
        el.classList.add('d-none');
        el.hidden = true;
    }

    function show(el) {
        if (!el) return;
        el.classList.remove('d-none');
        el.hidden = false;
    }

    resetToSession();

    if (!respTipo) return;

    hide(boxMed);
    hide(boxEnf);

    respTipo.addEventListener('change', function() {
        const value = this.value;
        if (selectMed) selectMed.value = '';
        if (selectEnf) selectEnf.value = '';

        hide(boxMed);
        hide(boxEnf);
        resetToSession();

        if (value === 'med') {
            show(boxMed);
        } else if (value === 'enf') {
            show(boxEnf);
        } else {
            resetToSession();
        }
    });

    if (selectMed) selectMed.addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        if (!opt || !opt.value) {
            resetToSession();
            return;
        }
        applySelection(opt.value, 'med');
    });

    if (selectEnf) selectEnf.addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        if (!opt || !opt.value) {
            resetToSession();
            return;
        }
        applySelection(opt.value, 'enf');
    });
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('modalDeleteVisitaList');
    if (!modal) return;
    var confirmBtn = modal.querySelector('[data-action=\"confirm-delete-row\"]');
    var feedback = modal.querySelector('.js-delete-feedback');
    var currentId = null;

    modal.addEventListener('show.bs.modal', function(event) {
        var trigger = event.relatedTarget;
        currentId = trigger ? parseInt(trigger.getAttribute('data-visita-id'), 10) : null;
        if (feedback) {
            feedback.classList.add('d-none');
            feedback.textContent = '';
        }
        if (confirmBtn) confirmBtn.disabled = false;
    });

    if (!confirmBtn) return;

    confirmBtn.addEventListener('click', function() {
        if (!currentId) return;
        confirmBtn.disabled = true;

        var formData = new FormData();
        formData.append('type', 'delete');
        formData.append('id_visita', currentId);
        formData.append('redirect', window.location.href);
        formData.append('ajax', '1');

        fetch('process_visita.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function(resp) { return resp.json(); })
            .then(function(res) {
                if (res && res.success) {
                    window.location.reload();
                    return;
                }
                var msg = (res && res.message) ? res.message : 'Não foi possível remover a visita.';
                if (feedback) {
                    feedback.textContent = msg;
                    feedback.classList.remove('d-none');
                } else {
                    alert(msg);
                }
            })
            .catch(function() {
                if (feedback) {
                    feedback.textContent = 'Falha inesperada ao remover a visita.';
                    feedback.classList.remove('d-none');
                } else {
                    alert('Falha inesperada ao remover a visita.');
                }
            })
            .finally(function() {
                confirmBtn.disabled = false;
            });
    });
});
</script>
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

// criar o json de antecedentes
(function() {
    var selectAntecedente = document.getElementById('fk_patologia2');
    var hiddenJsonField = document.getElementById('json-antec');
    if (!selectAntecedente || !hiddenJsonField) return;

    function buildAntecedentesPayload() {
        var selectedOptions = Array.from(selectAntecedente.selectedOptions || []);
        var pacienteField = document.getElementById('fk_paciente_int');
        var internacaoField = document.getElementById('fk_internacao_vis');
        var fkPaciente = pacienteField ? parseInt(pacienteField.value || '0', 10) : null;
        var fkInternacao = internacaoField ? parseInt(internacaoField.value || '0', 10) : null;

        var payload = selectedOptions
            .map(function(option) {
                var idAntecedente = parseInt(option.value, 10);
                if (!idAntecedente) return null;
                return {
                    fk_id_paciente: fkPaciente,
                    fk_internacao_ant_int: fkInternacao,
                    intern_antec_ant_int: idAntecedente
                };
            })
            .filter(function(item) { return item !== null; });

        hiddenJsonField.value = payload.length ? JSON.stringify(payload) : '';
    }

    selectAntecedente.addEventListener('change', buildAntecedentesPayload);
    buildAntecedentesPayload();
})();

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
(function () {
    var form = document.getElementById('add-visita-form');
    var timerField = document.getElementById('timer_vis');
    var pacienteField = document.getElementById('fk_paciente_int');
    var timerStart = null;

    function startTimer() {
        if (timerStart === null) {
            timerStart = Date.now();
        }
    }

    if (pacienteField) {
        if (pacienteField.value) {
            startTimer();
        } else {
            pacienteField.addEventListener('change', function () {
                if (this.value) {
                    startTimer();
                }
            });
        }
    } else {
        startTimer();
    }

    ['pacienteSelecionado', 'paciente-selecionado', 'visitaPacienteSelecionado'].forEach(function (evtName) {
        document.addEventListener(evtName, startTimer);
    });

    if (form && timerField) {
        form.addEventListener('submit', function () {
            if (timerStart !== null) {
                var elapsed = Math.max(0, Math.round((Date.now() - timerStart) / 1000));
                timerField.value = elapsed;
            }
        });
    }
})();
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
window.VISITA_TUSS_DATA = <?= json_encode($tussPorVisita, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_TUSS_FALLBACK = <?= json_encode($tussPorInternacao, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_NEG_DATA = <?= json_encode($negPorVisita, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_NEG_FALLBACK = <?= json_encode($negPorInternacao, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_GESTAO_DATA = <?= json_encode($gestaoPorVisita, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_GESTAO_FALLBACK = <?= json_encode($gestaoPorInternacao, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_UTI_DATA = <?= json_encode($utiPorVisita, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_UTI_FALLBACK = <?= json_encode($utiPorInternacao, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_PRORR_DATA = <?= json_encode($prorrogPorVisita, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_PRORR_FALLBACK = <?= json_encode($prorrogPorInternacao, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.VISITA_INTER_MAP = <?= json_encode($visitaInterMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script>
const __VISITA_INTER_MAP = window.VISITA_INTER_MAP || {};
const __TUSS_FALLBACK = window.VISITA_TUSS_FALLBACK || {};
const __NEG_FALLBACK = window.VISITA_NEG_FALLBACK || {};
const __GESTAO_FALLBACK = window.VISITA_GESTAO_FALLBACK || {};
const __UTI_FALLBACK = window.VISITA_UTI_FALLBACK || {};
const __PRORR_FALLBACK = window.VISITA_PRORR_FALLBACK || {};
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const select = document.getElementById("retificou");

    if (!select.value) {
        const hoje = new Date();
        const dia = String(hoje.getDate()).padStart(2, '0');
        const mes = String(hoje.getMonth() + 1).padStart(2, '0');
        const ano = hoje.getFullYear();
        const dataExibicao = `${dia}/${mes}/${ano}`;
        const dataValor = `${ano}-${mes}-${dia}`;
        const novaOption = document.createElement("option");
        novaOption.value = dataValor;
        novaOption.text = `Data Atual - ${dataExibicao}`;
        select.add(novaOption);
        select.value = dataValor;
    }
});
</script>

<script>
(function() {
    const visitasOriginais = <?= json_encode($visitasAntigas ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const visitaMap = {};
    const visitaMapById = {};
    (visitasOriginais || []).forEach((row) => {
        if (!row || typeof row !== 'object') return;
        const noKey = row.visita_no_vis != null ? String(row.visita_no_vis) : null;
        const idKey = row.id_visita != null ? String(row.id_visita) : null;
        if (noKey) visitaMap[noKey] = row;
        if (idKey) visitaMapById[idKey] = row;
    });

    const selectRet = document.getElementById('retificou');
    const dataVisitaInput = document.getElementById('data_visita_vis');
    const visitaNoInput = document.getElementById('visita_no_vis');
    const relInput = document.getElementById('rel_visita_vis');
    const acoesInput = document.getElementById('acoes_int_vis');
    const examesInput = document.getElementById('exames_enf');
    const oportunidadesInput = document.getElementById('oportunidades_enf');
    const programacaoInput = document.getElementById('programacao_enf');
    const auditorMedInput = document.getElementById('visita_auditor_prof_med');
    const auditorEnfInput = document.getElementById('visita_auditor_prof_enf');
    const flagMedInput = document.getElementById('visita_med_vis');
    const flagEnfInput = document.getElementById('visita_enf_vis');
    const dataLancInput = document.getElementById('data_lancamento_vis');
    const editIdInput = document.getElementById('id_visita_edit');
    const fkVisitaInput = document.getElementById('fk_int_visita');
    const modalEl = document.getElementById('myModal1');

    if (!selectRet) return;

    function formatLancamentoDateValue(value) {
        if (!value) return '';
        const normalized = String(value).trim();
        const match = normalized.match(/^(\d{4}-\d{2}-\d{2})/);
        if (match) {
            return match[1];
        }
        const parsed = new Date(normalized.replace('T', ' '));
        if (!Number.isNaN(parsed.getTime())) {
            const pad = (n) => String(n).padStart(2, '0');
            return `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}`;
        }
        return '';
    }

    const defaults = {
        dataVisita: dataVisitaInput ? dataVisitaInput.value : '',
        visitaNo: visitaNoInput ? visitaNoInput.value : '',
        rel: relInput ? relInput.value : '',
        acoes: acoesInput ? acoesInput.value : '',
        exames: examesInput ? examesInput.value : '',
        oportunidades: oportunidadesInput ? oportunidadesInput.value : '',
        programacao: programacaoInput ? programacaoInput.value : '',
        fkVisita: fkVisitaInput ? fkVisitaInput.value : '',
        dataLanc: dataLancInput ? dataLancInput.value : ''
    };

    function fillCampos(vis) {
        if (visitaNoInput && vis.visita_no_vis != null) {
            visitaNoInput.value = vis.visita_no_vis;
        }
        if (fkVisitaInput && vis.id_visita != null) {
            fkVisitaInput.value = vis.id_visita;
        }
        if (editIdInput) editIdInput.value = vis.id_visita ?? '';
        if (dataVisitaInput && vis.data_visita_vis) {
            dataVisitaInput.value = vis.data_visita_vis;
        }
        if (dataLancInput) {
            const formattedLanc = formatLancamentoDateValue(vis.data_lancamento_vis);
            dataLancInput.value = formattedLanc || defaults.dataLanc || '';
        }
        if (relInput) relInput.value = vis.rel_visita_vis || '';
        if (acoesInput) acoesInput.value = vis.acoes_int_vis || '';
        if (examesInput) examesInput.value = vis.exames_enf || '';
        if (oportunidadesInput) oportunidadesInput.value = vis.oportunidades_enf || '';
        if (programacaoInput) programacaoInput.value = vis.programacao_enf || '';
        if (auditorMedInput) auditorMedInput.value = vis.visita_auditor_prof_med || '';
        if (auditorEnfInput) auditorEnfInput.value = vis.visita_auditor_prof_enf || '';
        if (flagMedInput) flagMedInput.value = vis.visita_med_vis || flagMedInput.value;
        if (flagEnfInput) flagEnfInput.value = vis.visita_enf_vis || flagEnfInput.value;
        hydrateTussForVisita(vis.id_visita);
        hydrateNegForVisita(vis.id_visita);
        hydrateGestaoForVisita(vis.id_visita);
        hydrateUtiForVisita(vis.id_visita);
        hydrateProrrogForVisita(vis.id_visita);
    }

    function resetCampos() {
        if (visitaNoInput) visitaNoInput.value = defaults.visitaNo;
        if (dataVisitaInput) dataVisitaInput.value = defaults.dataVisita;
        if (relInput) relInput.value = defaults.rel;
        if (acoesInput) acoesInput.value = defaults.acoes;
        if (examesInput) examesInput.value = defaults.exames;
        if (oportunidadesInput) oportunidadesInput.value = defaults.oportunidades;
        if (programacaoInput) programacaoInput.value = defaults.programacao;
        if (fkVisitaInput) fkVisitaInput.value = defaults.fkVisita;
        if (editIdInput) editIdInput.value = '';
        if (dataLancInput) dataLancInput.value = defaults.dataLanc;
        resetAdditionalTables();
    }

    selectRet.addEventListener('change', function() {
        const key = this.value && /^\d+$/.test(this.value) ? this.value : null;
        if (key && visitaMap[key]) {
            fillCampos(visitaMap[key]);
        } else {
            resetCampos();
        }
    });

    window.selecionarVisitaParaEditar = function(idVisita) {
        const mapKey = idVisita != null ? String(idVisita) : null;
        const visita = mapKey ? visitaMapById[mapKey] : null;
        if (!visita) return;
        if (selectRet && visita.visita_no_vis != null) {
            selectRet.value = String(visita.visita_no_vis);
            selectRet.dispatchEvent(new Event('change'));
        }
        if (modalEl) {
            if (window.bootstrap && window.bootstrap.Modal) {
                const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                instance.hide();
            } else if (window.jQuery) {
                $('#myModal1').modal('hide');
            }
        }
    };
})();

const GESTAO_FIELD_DEFAULTS = {
    alto_custo_ges: 'n',
    rel_alto_custo_ges: '',
    opme_ges: 'n',
    rel_opme_ges: '',
    home_care_ges: 'n',
    rel_home_care_ges: '',
    desospitalizacao_ges: 'n',
    rel_desospitalizacao_ges: '',
    evento_adverso_ges: 'n',
    rel_evento_adverso_ges: '',
    tipo_evento_adverso_gest: '',
    evento_sinalizado_ges: 'n',
    evento_discutido_ges: 'n',
    evento_negociado_ges: 'n',
    evento_valor_negoc_ges: '',
    evento_retorno_qual_hosp_ges: 'n',
    evento_classificado_hospital_ges: 'n',
    evento_data_ges: '',
    evento_encerrar_ges: 'n',
    evento_impacto_financ_ges: 'n',
    evento_prolongou_internacao_ges: 'n',
    evento_concluido_ges: 'n',
    evento_classificacao_ges: '',
    evento_prorrogar_ges: 'n',
    evento_fech_ges: 'n'
};

const UTI_FIELD_MAP = [
    { key: 'internado_uti', id: 'internado_uti', defaultValue: 's' },
    { key: 'motivo_uti', id: 'motivo_uti', defaultValue: '' },
    { key: 'just_uti', id: 'just_uti', defaultValue: 'Pertinente' },
    { key: 'criterios_uti', id: 'criterio_uti', defaultValue: '' },
    { key: 'data_internacao_uti', id: 'data_internacao_uti', defaultValue: '', formatter: normalizeDateValue },
    { key: 'hora_internacao_uti', id: 'hora_internacao_uti', defaultValue: '', formatter: normalizeTimeValue },
    { key: 'data_alta_uti', id: 'data_alta_uti', defaultValue: '', formatter: normalizeDateValue },
    { key: 'vm_uti', id: 'vm_uti', defaultValue: 'n' },
    { key: 'dva_uti', id: 'dva_uti', defaultValue: 'n' },
    { key: 'suporte_vent_uti', id: 'suporte_vent_uti', defaultValue: 'n' },
    { key: 'glasgow_uti', id: 'glasgow_uti', defaultValue: '' },
    { key: 'dist_met_uti', id: 'dist_met_uti', defaultValue: 'n' },
    { key: 'score_uti', id: 'score_uti', defaultValue: '' },
    { key: 'saps_uti', id: 'saps_uti', defaultValue: '' },
    { key: 'rel_uti', id: 'rel_uti', defaultValue: '' }
];

function resetAdditionalTables() {
    const selectTuss = document.getElementById('select_tuss');
    const selectNeg = document.getElementById('select_negoc');
    const selectGestao = document.getElementById('select_gestao');
    const selectUti = document.getElementById('select_uti');
    const selectProrrog = document.getElementById('select_prorrog');
    if (selectTuss) {
        selectTuss.value = '';
        selectTuss.dispatchEvent(new Event('change'));
    }
    if (selectNeg) {
        selectNeg.value = '';
        selectNeg.dispatchEvent(new Event('change'));
    }
    if (selectGestao) {
        selectGestao.value = '';
        selectGestao.dispatchEvent(new Event('change'));
    }
    if (selectUti) {
        selectUti.value = '';
        selectUti.dispatchEvent(new Event('change'));
    }
    if (selectProrrog) {
        selectProrrog.value = '';
        selectProrrog.dispatchEvent(new Event('change'));
    }
    resetTussFields();
    resetNegotiationFields();
    resetGestaoFields();
    resetUtiFields();
    resetProrrogFields();
}

function hydrateTussForVisita(visitaId) {
    const map = window.VISITA_TUSS_DATA || {};
    const key = visitaId != null ? String(visitaId) : null;
    let entries = key && map[key] ? map[key] : [];
    if ((!entries || !entries.length) && visitaId != null) {
        const interId = __VISITA_INTER_MAP[String(visitaId)];
        if (interId && __TUSS_FALLBACK[String(interId)]) {
            entries = __TUSS_FALLBACK[String(interId)];
        }
    }
    const selectTuss = document.getElementById('select_tuss');
    if (!selectTuss) return;
    if (!entries.length) {
        resetTussFields();
        selectTuss.value = '';
        selectTuss.dispatchEvent(new Event('change'));
        return;
    }
    selectTuss.value = 's';
    selectTuss.dispatchEvent(new Event('change'));
    applyTussEntries(entries);
}

function resetTussFields() {
    if (typeof clearTussInputs === 'function') {
        clearTussInputs();
    }
    const tussJsonField = document.getElementById('tuss-json');
    if (tussJsonField) tussJsonField.value = '';
}

function applyTussEntries(entries) {
    if (!Array.isArray(entries) || !entries.length) return;
    if (typeof clearTussInputs === 'function') clearTussInputs();
    const initial = document.querySelector('.tuss-field-container[data-initial="true"]');
    if (!initial) return;
    entries.forEach((entry, idx) => {
        let target = initial;
        if (idx > 0) {
            if (typeof addTussField === 'function') addTussField();
            const containers = document.querySelectorAll('.tuss-field-container');
            target = containers[containers.length - 1];
        }
        if (!target) return;
        const selectDesc = target.querySelector('[name="tuss_solicitado"]');
        if (selectDesc) {
            selectDesc.value = entry.tuss_solicitado || '';
            if (typeof $ !== 'undefined' && $.fn.selectpicker) $(selectDesc).selectpicker('refresh');
        }
        const dataInput = target.querySelector('[name="data_realizacao_tuss"]');
        if (dataInput) dataInput.value = (entry.data_realizacao_tuss || '').substring(0, 10);
        const qtdSol = target.querySelector('[name="qtd_tuss_solicitado"]');
        if (qtdSol) qtdSol.value = entry.qtd_tuss_solicitado || '';
        const qtdLib = target.querySelector('[name="qtd_tuss_liberado"]');
        if (qtdLib) qtdLib.value = entry.qtd_tuss_liberado || '';
        const liberado = target.querySelector('[name="tuss_liberado_sn"]');
        if (liberado) liberado.value = entry.tuss_liberado_sn || '';
    });
    if (typeof generateTussJSON === 'function') generateTussJSON();
}

function hydrateNegForVisita(visitaId) {
    const map = window.VISITA_NEG_DATA || {};
    const key = visitaId != null ? String(visitaId) : null;
    let entries = key && map[key] ? map[key] : [];
    if ((!entries || !entries.length) && visitaId != null) {
        const interId = __VISITA_INTER_MAP[String(visitaId)];
        if (interId && __NEG_FALLBACK[String(interId)]) {
            entries = __NEG_FALLBACK[String(interId)];
        }
    }
    const selectNeg = document.getElementById('select_negoc');
    if (!selectNeg) return;
    if (!entries.length) {
        resetNegotiationFields();
        selectNeg.value = '';
        selectNeg.dispatchEvent(new Event('change'));
        return;
    }
    selectNeg.value = 's';
    selectNeg.dispatchEvent(new Event('change'));
    applyNegotiationEntries(entries);
}

function resetNegotiationFields() {
    const containers = document.querySelectorAll('.negotiation-field-container');
    containers.forEach((container) => {
        if (container.hasAttribute('data-initial')) {
            container.querySelectorAll('input:not([type="hidden"]), select').forEach((el) => {
                el.value = '';
            });
        } else {
            container.remove();
        }
    });
    const jsonField = document.getElementById('negociacoes_json');
    if (jsonField) jsonField.value = '';
}

function applyNegotiationEntries(entries) {
    if (!Array.isArray(entries) || !entries.length) return;
    resetNegotiationFields();
    const base = document.querySelector('.negotiation-field-container[data-initial="true"]');
    if (!base) return;
    entries.forEach((entry, idx) => {
        let target = base;
        if (idx > 0) {
            if (typeof addNegotiationField === 'function') addNegotiationField();
            const containers = document.querySelectorAll('.negotiation-field-container');
            target = containers[containers.length - 1];
        }
        if (!target) return;
        const tipo = target.querySelector('[name="tipo_negociacao"]');
        if (tipo) tipo.value = entry.tipo_negociacao || '';
        const dataIni = target.querySelector('[name="data_inicio_negoc"]');
        if (dataIni) dataIni.value = (entry.data_inicio_negoc || '').substring(0, 10);
        const dataFim = target.querySelector('[name="data_fim_negoc"]');
        if (dataFim) dataFim.value = (entry.data_fim_negoc || '').substring(0, 10);
        const trocaDe = target.querySelector('[name="troca_de"]');
        if (trocaDe) trocaDe.value = entry.troca_de || '';
        const trocaPara = target.querySelector('[name="troca_para"]');
        if (trocaPara) trocaPara.value = entry.troca_para || '';
        const qtd = target.querySelector('[name="qtd"]');
        if (qtd) qtd.value = entry.qtd || '';
        const saving = target.querySelector('[name="saving"]');
        if (saving) saving.value = entry.saving || '';
        const savingShow = target.querySelector('[name="saving_show"]');
        if (savingShow) savingShow.value = entry.saving ? `R$ ${parseFloat(entry.saving).toFixed(2)}` : '';

        if (typeof setTrocaFromTipo === 'function') setTrocaFromTipo($(target));
        if (typeof calculateSaving === 'function') calculateSaving($(target));
    });
    if (typeof generateNegotiationsJSON === 'function') generateNegotiationsJSON();
    if (typeof validarTodasDatas === 'function') validarTodasDatas();
}

function resetGestaoFields() {
    Object.keys(GESTAO_FIELD_DEFAULTS).forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (!field) return;
        const defaultValue = GESTAO_FIELD_DEFAULTS[fieldId];
        field.value = defaultValue != null ? defaultValue : '';
        if (field.tagName === 'SELECT') {
            field.dispatchEvent(new Event('change'));
        }
    });
}

function applyGestaoEntry(entry) {
    if (!entry) {
        resetGestaoFields();
        return;
    }
    Object.keys(GESTAO_FIELD_DEFAULTS).forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (!field) return;
        let value = entry[fieldId];
        if (value === undefined || value === null || value === '') {
            value = GESTAO_FIELD_DEFAULTS[fieldId] ?? '';
        }
        field.value = value;
        if (field.tagName === 'SELECT') {
            field.dispatchEvent(new Event('change'));
        }
    });
}

function hydrateGestaoForVisita(visitaId) {
    const map = window.VISITA_GESTAO_DATA || {};
    const key = visitaId != null ? String(visitaId) : null;
    let entry = key && map[key] ? map[key] : null;
    if (!entry && visitaId != null) {
        const interId = __VISITA_INTER_MAP[String(visitaId)];
        if (interId && __GESTAO_FALLBACK[String(interId)]) {
            entry = __GESTAO_FALLBACK[String(interId)];
        }
    }
    const selectGestao = document.getElementById('select_gestao');
    if (!entry) {
        resetGestaoFields();
        if (selectGestao) {
            selectGestao.value = '';
            selectGestao.dispatchEvent(new Event('change'));
        }
        return;
    }
    if (selectGestao) {
        selectGestao.value = 's';
        selectGestao.dispatchEvent(new Event('change'));
    }
    applyGestaoEntry(entry);
}

function resetUtiFields() {
    UTI_FIELD_MAP.forEach((fieldInfo) => {
        const field = document.getElementById(fieldInfo.id);
        if (!field) return;
        const defaultValue = fieldInfo.defaultValue != null ? fieldInfo.defaultValue : '';
        field.value = defaultValue;
        if (field.tagName === 'SELECT') {
            field.dispatchEvent(new Event('change'));
        }
    });
    const justifyEl = document.querySelector('textarea[name="justifique_uti"]');
    if (justifyEl) justifyEl.value = '';
}

function applyUtiEntry(entry) {
    if (!entry) {
        resetUtiFields();
        return;
    }
    UTI_FIELD_MAP.forEach((fieldInfo) => {
        const field = document.getElementById(fieldInfo.id);
        if (!field) return;
        let value = entry[fieldInfo.key];
        if (fieldInfo.formatter && value) {
            value = fieldInfo.formatter(value);
        }
        if (value === undefined || value === null || value === '') {
            value = fieldInfo.defaultValue != null ? fieldInfo.defaultValue : '';
        }
        field.value = value;
        if (field.tagName === 'SELECT') {
            field.dispatchEvent(new Event('change'));
        }
    });
    const justifyEl = document.querySelector('textarea[name="justifique_uti"]');
    if (justifyEl && entry.justifique_uti) {
        justifyEl.value = entry.justifique_uti;
    }
}

function hydrateUtiForVisita(visitaId) {
    const map = window.VISITA_UTI_DATA || {};
    const key = visitaId != null ? String(visitaId) : null;
    let entry = key && map[key] ? map[key] : null;
    if (!entry && visitaId != null) {
        const interId = __VISITA_INTER_MAP[String(visitaId)];
        if (interId && __UTI_FALLBACK[String(interId)]) {
            entry = __UTI_FALLBACK[String(interId)];
        }
    }
    const selectUti = document.getElementById('select_uti');
    if (!entry) {
        resetUtiFields();
        if (selectUti) {
            selectUti.value = '';
            selectUti.dispatchEvent(new Event('change'));
        }
        return;
    }
    if (selectUti) {
        selectUti.value = 's';
        selectUti.dispatchEvent(new Event('change'));
    }
    applyUtiEntry(entry);
}

function normalizeDateValue(value) {
    return value ? String(value).substring(0, 10) : '';
}

function normalizeTimeValue(value) {
    return value ? String(value).substring(0, 5) : '';
}

function resetProrrogFields() {
    if (typeof clearProrrogInputs === 'function') {
        clearProrrogInputs();
    }
    const jsonField = document.getElementById('prorrogacoes-json');
    if (jsonField) jsonField.value = '';
}

function applyProrrogEntries(entries) {
    if (!Array.isArray(entries) || !entries.length) {
        resetProrrogFields();
        return;
    }
    if (typeof clearProrrogInputs === 'function') {
        clearProrrogInputs();
    }
    let base = document.querySelector('.field-container');
    if (!base && typeof addField === 'function') {
        addField();
        base = document.querySelector('.field-container');
    }
    if (!base) return;
    entries.forEach((entry, idx) => {
        let target = base;
        if (idx > 0 && typeof addField === 'function') {
            addField();
            const containers = document.querySelectorAll('.field-container');
            target = containers[containers.length - 1];
        }
        if (!target) return;
        const acomod = target.querySelector('[name="acomod1_pror"]');
        if (acomod) acomod.value = entry.acomod1_pror || '';
        const ini = target.querySelector('[name="prorrog1_ini_pror"]');
        if (ini) ini.value = normalizeDateValue(entry.prorrog1_ini_pror);
        const fim = target.querySelector('[name="prorrog1_fim_pror"]');
        if (fim) fim.value = normalizeDateValue(entry.prorrog1_fim_pror);
        const isol = target.querySelector('[name="isol_1_pror"]');
        if (isol) isol.value = entry.isol_1_pror || 'n';
        const diarias = target.querySelector('[name="diarias_1"]');
        if (diarias) diarias.value = entry.diarias_1 || '';
        if (typeof calculateDiarias === 'function') {
            calculateDiarias(target);
        }
    });
    if (typeof generateProrJSON === 'function') {
        generateProrJSON();
    }
}

function hydrateProrrogForVisita(visitaId) {
    const map = window.VISITA_PRORR_DATA || {};
    const key = visitaId != null ? String(visitaId) : null;
    let entries = key && map[key] ? map[key] : [];
    if ((!entries || !entries.length) && visitaId != null) {
        const interId = __VISITA_INTER_MAP[String(visitaId)];
        if (interId && __PRORR_FALLBACK[String(interId)]) {
            entries = __PRORR_FALLBACK[String(interId)];
        }
    }
    const selectProrr = document.getElementById('select_prorrog');
    if (!entries || !entries.length) {
        resetProrrogFields();
        if (selectProrr) {
            selectProrr.value = '';
            selectProrr.dispatchEvent(new Event('change'));
        }
        return;
    }
    if (selectProrr) {
        selectProrr.value = 's';
        selectProrr.dispatchEvent(new Event('change'));
    }
    applyProrrogEntries(entries);
}
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>


<!-- <script src="js/text_cad_internacao.js"></script>
<script src="js/select_internacao.js"></script> -->
