<?php

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$Internacaos = $Internacao_geral->findGeral();

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$capeante_geral = new capeanteDAO($conn, $BASE_URL);
$capeante = $capeante_geral->findGeral($limite, $inicio);

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$internacao_geral = new internacaoDAO($conn, $BASE_URL);
$internacao = $internacao_geral->findGeral();

//Instanciando a classe
$QtdTotalInt = new internacaoDAO($conn, $BASE_URL);
// METODO DE BUSCA DE PAGINACAO 
$pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
$pesqInternado = filter_input(INPUT_GET, 'pesqInternado') ?: "s";
$limite = filter_input(INPUT_GET, 'limite') ? filter_input(INPUT_GET, 'limite') : 10;
$pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
$id_internacao = filter_input(INPUT_GET, 'id_internacao');
$id_capeante = filter_input(INPUT_GET, 'id_capeante');
$type = filter_input(INPUT_GET, 'type');

$ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;
// $buscaAtivo = in_array($buscaAtivo, ['s', 'n']) ?: "";
$condicoes = [
    strlen($id_capeante) ? 'id_capeante = "' . $id_capeante . '"' : NULL,
];
$condicoes = array_filter($condicoes);
$where = implode(' AND ', $condicoes);
$order = $ordenar;
$obLimite = null;
$dataFech = date('Y-m-d');

if ($type == 'create') {
    $condicoes = [
        strlen($id_capeante) ? 'id_capeante = "' . $id_capeante . '"' : NULL,
        strlen($id_internacao) ? 'id_internacao = "' . $id_internacao . '"' : NULL,
    ];
    $condicoes = array_filter($condicoes);
    $where = implode(' AND ', $condicoes);

    $intern = $internacao_geral->selectAllInternacaoNewCap($where, $order, $obLimite);
    $parcial_count = $capeante_geral->getCapeanteByInternacao($id_internacao);
    $parcial_date = $capeante_geral->getLastCapeanteByInternacao($id_internacao);
    // $intern['0']['parcial_capeante'] = 's';
    // $intern['0']['parcial_num'] = $parcial_count['qtd'];
    // $intern['0']['data_inicial_capeante'] = $parcial_date['0'];
} else {
    $condicoes = [
        strlen($id_capeante) ? 'id_capeante = "' . $id_capeante . '"' : NULL,
    ];
    $condicoes = array_filter($condicoes);
    $where = implode(' AND ', $condicoes);
    $intern = $internacao_geral->selectAllInternacaoCap($where, $order, $obLimite);
}

// ===== EXIBIÇÃO DO PERÍODO ANTERIOR (apenas para NOVA inclusão) =====
$fmtBR = function ($d) {
    if (empty($d) || $d === '0000-00-00') return null;
    $ts = strtotime($d);
    return $ts ? date('d/m/Y', $ts) : null;
};

$textoPeriodoAnterior = " — Primeira parcial";
$parcialDefault = ($type === 'create')
  ? 's'
  : (($intern['0']['parcial_capeante'] ?? 'n'));

if ($type === 'create') {
    $idIntCtx = (int)($intern[0]['id_internacao'] ?? $id_internacao);
    if ($idIntCtx > 0) {
        // usa o novo método do DAO
        $ultimo = $capeante_geral->getUltimoCapeantePeriodoByInternacao($idIntCtx);
        
        if (!empty($ultimo) && !empty($ultimo['id_capeante'])) {
            $iniBR = $fmtBR($ultimo['data_inicial_capeante'] ?? null);
            $fimBR = $fmtBR($ultimo['data_final_capeante']   ?? null);

            if ($iniBR && $fimBR) {
                $textoPeriodoAnterior = "Último Parcial — Período {$iniBR} a {$fimBR}";
            } elseif ($iniBR) {
                $textoPeriodoAnterior = "Último Parcial — Período iniciado em {$iniBR}";
            } elseif ($fimBR) {
                $textoPeriodoAnterior = "Último Parcial — Período até {$fimBR}";
            } else {
                $textoPeriodoAnterior = "Último Parcial — (período anterior não disponível)";
            }
        }
    }
}

extract($intern);
?>
<?php if ($type === 'create' && !empty($ultimo['data_final_capeante']) && $ultimo['data_final_capeante'] !== '0000-00-00'): ?>
  <input type="hidden" id="last_final_date" value="<?= htmlspecialchars($ultimo['data_final_capeante']) ?>">
<?php else: ?>
  <input type="hidden" id="last_final_date" value="">
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>

<div class="container-fluid" id="main-container">
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: 33%;" aria-valuenow="33"
            aria-valuemin="0" aria-valuemax="100">
            Etapa 1 de 3
        </div>
    </div>

    <form action="<?= $BASE_URL ?>process_capeante.php" id="multi-step-form" method="POST"
        enctype="multipart/form-data">
        <?php if ($type == "create") { ?>
        <input type="hidden" name="type" value="create">
        <input type="hidden" name="id_capeante" value="<?= null ?>">
        <!-- <input type="hidden" id="parcial_date" name="parcial_date" value="<?= $parcial_date['0'] ?>"> -->
        <?php } else { ?>
        <input type="hidden" name="type" value="update">
        <input type="hidden" name="id_capeante" value="<?= $intern['0']['id_capeante'] ?>">
        <?php } ?>
        <!-- profissionais  validacao para input-->

        <input type="hidden" class="form-control" id="adm_capeante" name="adm_capeante"
            value="<?php if ($_SESSION['cargo'] === "Adm") echo 's' ?>">
        <input type="hidden" class="form-control" id="aud_enf_capeante" name="aud_enf_capeante"
            value="<?php if ($_SESSION['cargo'] === "Enf_Auditor") echo 's' ?>">
        <input type="hidden" class="form-control" id="aud_med_capeante" name="aud_med_capeante"
            value="<?php if ($_SESSION['cargo'] === "Med_auditor") echo 's' ?>">

        <!-- preenchar lista com campos do BD conforme profissionais -->
        <input type="hidden" class="form-control" id="adm_check" name="adm_check"
            value="<?php if ($_SESSION['cargo'] === "Adm") { echo "s"; } else { echo ($intern['0']['adm_check']); } ?>">
        <input type="hidden" class="form-control" id="med_check" name="med_check"
            value="<?php if ($_SESSION['cargo'] === "Med_auditor") { echo "s"; } else { echo ($intern['0']['med_check']); } ?>">
        <input type="hidden" class="form-control" id="enfer_check" name="enfer_check"
            value="<?php if ($_SESSION['cargo'] === "Enf_Auditor") { echo "s"; } else { echo ($intern['0']['enfer_check']); } ?>">
        <!-- medico e enf id -->
        <input type="hidden" class="form-control" id="fk_id_aud_enf" name="fk_id_aud_enf"
            value="<?php if ($_SESSION['cargo'] === "Enf_Auditor") { echo $_SESSION['id_usuario']; } else { echo ($intern['0']['fk_id_aud_enf']); } ?>">
        <input type="hidden" class="form-control" id="fk_id_aud_med" name="fk_id_aud_med"
            value="<?php if ($_SESSION['cargo'] === "Med_auditor") { echo $_SESSION['id_usuario']; } else { echo ($intern['0']['fk_id_aud_med']); } ?>">

        <input type="hidden" class="form-control" id="fk_id_aud_adm" name="fk_id_aud_adm"
            value="<?php if ($_SESSION['cargo'] === "Adm") { echo $_SESSION['id_usuario']; } else { echo ($intern['0']['fk_id_aud_adm']); } ?>">
        <input type="hidden" class="form-control" id="fk_id_aud_hosp" name="fk_id_aud_hosp"
            value="<?php if ($_SESSION['cargo'] === "Hospital") { echo $_SESSION['id_usuario']; } else { echo ($intern['0']['fk_id_aud_hosp']); } ?>">
        <input type="hidden" class="form-control" id="fk_int_capeante" name="fk_int_capeante"
            value="<?= $intern['0']['id_internacao'] ?>" placeholder="<?= $intern['0']['id_internacao'] ?>">
        <input type="hidden" class="form-control" id="fk_hospital_int" name="fk_hospital_int"
            value="<?= $intern['0']['nome_hosp'] ?>" placeholder="<?= $intern['0']['nome_hosp'] ?>" readonly>
        <input type="hidden" class="form-control" id="fk_user_cap" name="fk_user_cap"
            value="<?= $_SESSION['id_usuario'] ?>" placeholder="<?= $_SESSION['id_usuario'] ?>">
        <input type="hidden" class="form-control" id="fk_paciente_int" name="fk_paciente_int"
            value="<?= $intern[0]['fk_paciente_int'] ?>" placeholder="<?= $intern[0]['nome_pac'] ?>" readonly>

        <input type="hidden" class="form-control" id="data_intern_int" name="data_intern_int"
            value="<?= $intern['0']['data_intern_int'] ?>">
        <?php $agora = date('Y-m-d H:i:s'); ?>
        <input type="hidden" class="form-control" id="data_create_cap" value='<?= $agora; ?>' name="data_create_cap"
            placeholder="">
        <input type="hidden" class="form-control" id="usuario_create_cap" value="<?= $_SESSION['email_user'] ?>"
            name="usuario_create_cap" placeholder="Digite o usuário">
        <input type="hidden" class="form-control" value="n" id="aberto_cap" name="aberto_cap">
        <input type="hidden" class="form-control" value="s" id="em_auditoria_cap" name="em_auditoria_cap">

        <!-- Step 1: Informações Básicas -->
        <div id="step-1" class="step">

            <h3>Passo 1: Informações Básicas</h3>
            <br>
            <div class="form-group row">
                <div id="view-contact-container" class="container-fluid d-flex align-items-center">
                    <div class="d-flex" style="flex-grow: 1; gap: 20px;">
                        <!-- Primeira coluna: Id Capeante, Id Internacao, Data Internacao -->
                        <div class="d-flex flex-column" style="flex-grow: 1;">
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Código Capeante:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['id_capeante'] ?>
                                </span>
                            </div>
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Código Internação :</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['id_internacao'] ?>
                                </span>
                            </div>
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Data Internação:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= date("d/m/Y", strtotime($intern['0']['data_intern_int'])); ?>
                                </span>
                            </div>
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Senha:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['senha_int']; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Segunda coluna: Hospital, Paciente -->
                        <div class="d-flex flex-column" style="flex-grow: 1;">
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Hospital:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['nome_hosp'] ?>
                                </span>
                            </div>
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Paciente:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['nome_pac'] ?>
                                </span>
                            </div>
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Parcial:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= htmlspecialchars($intern['0']['parcial_num'] ?? '') ?>
                                    <?php if ($type === 'create'): ?>
                                    <?= htmlspecialchars($textoPeriodoAnterior) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex ms-auto">
                        <?php if ($intern['0']['med_check'] == 's'): ?>
                        <span id="boot-icon" class="bi bi-check-circle"
                            style="font-size: 1.1rem; font-weight:600; color: rgb(0, 78, 86); margin-right: 10px;">
                            Auditado Médico
                        </span>
                        <?php endif; ?>
                        <?php if ($intern['0']['enfer_check'] == 's'): ?>
                        <span id="boot-icon" class="bi bi-check-circle"
                            style="font-size: 1.1rem; font-weight:600; color: rgb(234, 128, 55);">
                            Auditado Enfermeiro
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <hr>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_apresentado_capeante">Valor Apresentado</label>
                    <input type="text" class="form-control dinheiro" id="valor_apresentado_capeante"
                        name="valor_apresentado_capeante"
                        value="<?= is_numeric($intern['0']['valor_apresentado_capeante']) ? number_format($intern['0']['valor_apresentado_capeante'], 2, ',', '.') : '' ?>"
                        required>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label for="data_inicial_capeante">Data Inicial</label>
                    <input type="date" class="form-control" id="data_inicial_capeante" name="data_inicial_capeante"
                        value="<?=
                                !empty($intern[0]['data_inicial_capeante'])
                                    ? $intern[0]['data_inicial_capeante']
                                    : $intern[0]['data_intern_int']
                                ?>" required>
                    <div class="invalid-feedback notif1">
                        Data inicial inválida.
                    </div>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label for="data_final_capeante">Data Final</label>
                    <input type="date" class="form-control" id="data_final_capeante" name="data_final_capeante"
                        value="<?= $intern['0']['data_final_capeante'] ?>">
                    <div class="invalid-feedback notif2">
                        Data final inválida.
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-3 mb-2">
                    <label for="lote_cap">Lote</label>
                    <input type="text" class="form-control" id="lote_cap" name="lote_cap"
                        value="<?= $intern['0']['lote_cap'] ?>">
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label for="diarias_capeante">Diárias</label>
                    <input readonly type="text" class="form-control" id="diarias_capeante" name="diarias_capeante"
                        value="<?= $intern['0']['diarias_capeante'] ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="data_fech_capeante">Data Fechamento</label>
                    <input type="date" class="form-control" id="data_fech_capeante" name="data_fech_capeante"
                        value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_glosa_enf">Glosa Enfermagem</label>
                    <input type="text" class="dinheiro_total form-control" id="valor_glosa_enf" name="valor_glosa_enf"
                        value="<?= is_numeric($intern['0']['valor_glosa_enf']) ? number_format($intern['0']['valor_glosa_enf'], 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                    <p class="oculto mensagem_error" id="err_valor_glosa_enf">Digite um número!</p>
                    <div class="invalid-feedback notif3">
                        Glosa maior que o valor total.
                    </div>
                </div>

                <div class="form-group col-md-6 mb-3">
                    <label for="valor_glosa_med">Glosa Médica</label>
                    <input type="text" class="form-control dinheiro_total" placeholder="R$0,00" id="valor_glosa_med"
                        name="valor_glosa_med"
                        value="<?= is_numeric($intern['0']['valor_glosa_med']) ? number_format($intern['0']['valor_glosa_med'], 2, ',', '.') : '' ?>">
                    <div class="invalid-feedback notif4">
                        Glosa maior que o valor total.
                    </div>
                </div>
            </div>
            <hr>
            <button type="button" id="btn-next-1" class="btn btn-primary" onclick="nextStep(2)">
                Próximo <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Step 2: Valores e Glosas -->
        <div id="step-2" class="step" style="display:none;">
            <h3>Passo 2: Valores e Glosas</h3>
            <br>
            <div class="form-group row">
                <div id="view-contact-container" class="container-fluid d-flex align-items-center">
                    <div class="d-flex" style="flex-grow: 1; gap: 20px;">
                        <!-- Primeira coluna: Id Capeante, Id Internacao, Data Internacao -->
                        <div class="d-flex flex-column" style="flex-grow: 1;">
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Código Capeante:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['id_capeante'] ?>
                                </span>
                            </div>

                        </div>

                        <!-- Segunda coluna: Hospital, Paciente -->
                        <div class="d-flex flex-column" style="flex-grow: 1;">
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Paciente:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['nome_pac'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex ms-auto">
                        <?php if ($intern['0']['med_check'] == 's'): ?>
                        <span id="boot-icon" class="bi bi-check-circle"
                            style="font-size: 1.1rem; font-weight:600; color: rgb(0, 78, 86); margin-right: 10px;">
                            Auditado Médico
                        </span>
                        <?php endif; ?>
                        <?php if ($intern['0']['enfer_check'] == 's'): ?>
                        <span id="boot-icon" class="bi bi-check-circle"
                            style="font-size: 1.1rem; font-weight:600; color: rgb(234, 128, 55);">
                            Auditado Enfermeiro
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label style="font-size:15px">Total de Valores:</label><label id="diff_valor"
                        style="color: #a83232; margin-left: 6px;font-size:15px"></label><i
                        style="color:green;margin-left:1px;font-size:1.2em" placeholder="R$0,00" id="nodiff_valor"
                        class="fas fa-check"></i>

                    <p id="total-valores" style="font-weight: bold;">R$ 0,00</p>

                </div>
                <div class="form-group col-md-6">
                    <label style="font-size:15px">Total de Glosas:</label><label id="diff_valor_glosa"
                        style="color: #a83232; margin-left: 6px;font-size:15px"></label><i
                        style="color:green;margin-left:1px;font-size:1.2em" placeholder="R$0,00" id="nodiff_valor_glosa"
                        class="fas fa-check"></i>

                    <p id="total-glosas" style="font-weight: bold;">R$ 0,00</p>
                </div>
            </div>

            <div class="row">
                <!-- Valores -->
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_diarias">Valor Diárias</label>
                    <input type="text" class="form-control dinheiro" id="valor_diarias" placeholder="R$0,00"
                        name="valor_diarias"
                        value="<?= is_numeric($intern['0']['valor_diarias']) ? number_format($intern['0']['valor_diarias'], 2, ',', '.') : '' ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_diarias">Glosa Diárias</label>
                    <input type="text" class="form-control dinheiro" id="glosa_diarias" placeholder="R$0,00"
                        name="glosa_diaria"
                        value="<?= is_numeric($intern['0']['glosa_diaria']) ? number_format($intern['0']['glosa_diaria'], 2, ',', '.') : '' ?>">
                </div>

            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_oxig">Valor Oxigenioterapia</label>
                    <input type="text" class="form-control dinheiro" id="valor_oxig" placeholder="R$0,00"
                        name="valor_oxig"
                        value="<?= is_numeric($intern['0']['valor_oxig']) ? number_format($intern['0']['valor_oxig'], 2, ',', '.') : '' ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_oxig">Glosa Oxigenioterapia</label>
                    <input type="text" class="form-control dinheiro" id="glosa_oxig" placeholder="R$0,00"
                        name="glosa_oxig"
                        value="<?= is_numeric($intern['0']['glosa_oxig']) ? number_format($intern['0']['glosa_oxig'], 2, ',', '.') : '' ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_taxa">Valor Taxas</label>
                    <input type="text" class="form-control dinheiro" id="valor_taxa" placeholder="R$0,00"
                        name="valor_taxa"
                        value="<?= is_numeric($intern['0']['valor_taxa']) ? number_format($intern['0']['valor_taxa'], 2, ',', '.') : '' ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_taxas">Glosa Taxas</label>
                    <input type="text" class="form-control dinheiro" id="glosa_taxas" placeholder="R$0,00"
                        name="glosa_taxas"
                        value="<?= is_numeric($intern['0']['glosa_taxas']) ? number_format($intern['0']['glosa_taxas'], 2, ',', '.') : '' ?>">
                </div>
            </div>

            <div class="row">
                <!-- Glosas -->
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_matmed">Valor MatMed</label>
                    <input type="text" class="form-control dinheiro" id="valor_matmed" placeholder="R$0,00"
                        name="valor_matmed"
                        value="<?= is_numeric($intern['0']['valor_matmed']) ? number_format($intern['0']['valor_matmed'], 2, ',', '.') : '' ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_matmed">Glosa MatMed</label>
                    <input type="text" class="form-control dinheiro" id="glosa_matmed" placeholder="R$0,00"
                        name="glosa_matmed"
                        value="<?= is_numeric($intern['0']['glosa_matmed']) ? number_format($intern['0']['glosa_matmed'], 2, ',', '.') : '' ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_sadt">Valor SADT</label>
                    <input type="text" class="form-control dinheiro" id="valor_sadt" placeholder="R$0,00"
                        name="valor_sadt"
                        value="<?= is_numeric($intern['0']['valor_sadt']) ? number_format($intern['0']['valor_sadt'], 2, ',', '.') : '' ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_sadt">Glosa SADT</label>
                    <input type="text" class="form-control dinheiro" id="glosa_sadt" placeholder="R$0,00"
                        name="glosa_sadt"
                        value="<?= is_numeric($intern['0']['glosa_sadt']) ? number_format($intern['0']['glosa_sadt'], 2, ',', '.') : '' ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_honorarios">Valor Honorários</label>
                    <input type="text" class="form-control dinheiro" id="valor_honorarios" placeholder="R$0,00"
                        name="valor_honorarios"
                        value="<?= is_numeric($intern['0']['valor_honorarios']) ? number_format($intern['0']['valor_honorarios'], 2, ',', '.') : '' ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_honorarios">Glosa Honorários</label>
                    <input type="text" class="form-control dinheiro" id="glosa_honorarios" placeholder="R$0,00"
                        name="glosa_honorarios"
                        value="<?= is_numeric($intern['0']['glosa_honorarios']) ? number_format($intern['0']['glosa_honorarios'], 2, ',', '.') : '' ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_honorarios">Valor OPME</label>
                    <input type="text" class="form-control dinheiro" id="valor_opme" placeholder="R$0,00"
                        name="valor_opme"
                        value="<?= is_numeric($intern['0']['valor_opme']) ? number_format($intern['0']['valor_opme'], 2, ',', '.') : '' ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_honorarios">Glosa OPME</label>
                    <input type="text" class="form-control dinheiro" id="glosa_opme" placeholder="R$0,00"
                        name="glosa_opme"
                        value="<?= is_numeric($intern['0']['glosa_opme']) ? number_format($intern['0']['glosa_opme'], 2, ',', '.') : '' ?>">
                </div>
            </div>
            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
            <button type="button" id="btn-next-2" class="btn btn-primary" onclick="nextStep(3)">
                Próximo <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Step 3: Informações Adicionais -->
        <div id="step-3" class="step" style="display:none;">
            <h3>Passo 3: Informações Adicionais</h3>
            <br>
            <div class="form-group row">
                <div id="view-contact-container" class="container-fluid d-flex align-items-center">
                    <div class="d-flex" style="flex-grow: 1; gap: 20px;">
                        <!-- Primeira coluna: Id Capeante, Id Internacao, Data Internacao -->
                        <div class="d-flex flex-column" style="flex-grow: 1;">
                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Código Capeante:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['id_capeante'] ?>
                                </span>
                            </div>

                        </div>

                        <!-- Segunda coluna: Hospital, Paciente -->
                        <div class="d-flex flex-column" style="flex-grow: 1;">

                            <div>
                                <span class="card-title bold" style="font-weight: 600;">Paciente:</span>
                                <span class="card-title bold" style="font-weight: 500;">
                                    <?= $intern['0']['nome_pac'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex ms-auto">
                        <?php if ($intern['0']['med_check'] == 's'): ?>
                        <span id="boot-icon" class="bi bi-check-circle"
                            style="font-size: 1.1rem; font-weight:600; color: rgb(0, 78, 86); margin-right: 10px;">
                            Auditado Médico
                        </span>
                        <?php endif; ?>
                        <?php if ($intern['0']['enfer_check'] == 's'): ?>
                        <span id="boot-icon" class="bi bi-check-circle"
                            style="font-size: 1.1rem; font-weight:600; color: rgb(234, 128, 55);">
                            Auditado Enfermeiro
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="pacote">Pacote</label>
                    <select class="form-control" id="pacote" name="pacote">
                        <option value="n" <?= $intern['0']['pacote'] == 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $intern['0']['pacote'] == 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>
                <div class="form-group col-md-3 mb-3">
                    <label for="parcial_capeante">Parcial</label>
                    <select class="form-control" id="parcial_capeante" name="parcial_capeante">
                        <option value="n" <?= $parcialDefault === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $parcialDefault === 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>

                <?php $parcialAtivo = ($parcialDefault === 's'); ?>

                <div class="form-group col-md-3 mb-3" id="wrap_parcial_num"
                    style="<?= $parcialAtivo ? '' : 'display:none;' ?>">
                    <label for="parcial_num">Número Parcial</label>
                    <input type="number" class="form-control" id="parcial_num" name="parcial_num"
                        value="<?= htmlspecialchars($intern['0']['parcial_num'] ?? '') ?>"
                        <?= $parcialAtivo ? '' : 'disabled' ?>>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="senha_finalizada">Senha Finalizada</label>
                    <select class="form-control" id="senha_finalizada" name="senha_finalizada">
                        <option value="n" <?= $intern['0']['senha_finalizada'] == 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $intern['0']['senha_finalizada'] == 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="encerrado_cap">Capeante Encerrado</label>
                    <select class="form-control" id="encerrado_cap" name="encerrado_cap">
                        <option value="n" <?= $intern['0']['encerrado_cap'] == 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $intern['0']['encerrado_cap'] == 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <!-- Campo de Desconto -->
                <div id="div_val_desconto" class="form-group col-md-6 mb-3r">
                    <label for="desconto_valor_cap">Valor Desconto (em %)</label>
                    <input type="number" class="form-control" id="desconto_valor_cap" name="desconto_valor_cap"
                        value="<?= $intern['0']['desconto_valor_cap'] ?>">
                </div>

                <!-- Totais à Direita -->
                <div class="form-group col-md-6 mb-3 d-flex justify-content-start align-items-center">
                    <div class="text-right me-4">
                        <label>Valor Apresentado:</label>
                        <p id="total-apresentado" style="font-weight: bold;">R$ 0,00</p>
                    </div>
                    <div class="text-right me-4">
                        <label>Total de Glosas:</label>
                        <p id="total-glosas-final" style="font-weight: bold;">R$ 0,00</p>
                    </div>
                    <div class="text-right me-4">
                        <label>Valor Final:</label>
                        <p id="total-final" style="font-weight: bold;">R$ 0,00</p>
                    </div>
                    <div class="text-right me-4">
                        <label>Com Desconto:</label>
                        <p id="total-valores-final-desconto" style="font-weight: bold;">R$ 0,00</p>
                    </div>
                    <div class="text-right me-4">
                        <input id="checkbox_imprimir" name="checkbox_imprimir" value="1"
                            style="width: 15px;height: 15px;background-color: #f1f1f1;border-radius: 6px;margin-top:10px;margin-left:10px;"
                            type="checkbox" class="material-checkbox">
                        <span class="checkmark">Imprimir Capeante</span>
                    </div>
                </div>

            </div>

            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> Concluir
            </button>

            <button type="button" class="btn btn-outline-primary ms-2"
                onclick="baixarPDF(<?= $intern[0]['id_capeante'] ?>, <?= $intern[0]['id_internacao'] ?>)">
                <i class="bi bi-download"></i> Salvar PDF
            </button>

            <button id="btnEnviarEmail" type="button" class="btn btn-outline-secondary ms-2"
                onclick="enviarPDF(<?= $intern[0]['id_capeante'] ?>, <?= $intern[0]['id_internacao'] ?>)">
                <i class="bi bi-envelope-fill"></i> Email PDF
            </button>

            <iframe id="iframeDownload" style="display: none;"></iframe>

            <div id="mensagemStatus"
                style="display:none; margin-top:10px; padding:10px; border-radius:5px; font-weight:bold; text-align:center;">
            </div>

            <div style="width:500px;display:none" class="alert" id="alert" role="alert"></div>

        </div>
    </form>
</div>


<script>
function baixarPDF(idCapeante, idInternacao) {
    const iframe = document.getElementById("iframeDownload");
    iframe.src = `process_capeante_pdf.php?id_capeante=${idCapeante}&fk_int_capeante=${idInternacao}&save_only=1`;
    // Mostra mensagem de sucesso
    mostrarMensagem('Capeante salvo com sucesso!', '#28a745');
}

function enviarPDF(idCapeante, idInternacao) {
    fetch(`process_capeante_pdf.php?id_capeante=${idCapeante}&fk_int_capeante=${idInternacao}`)
    // Exibe mensagem logo após iniciar o download
    mostrarMensagem('Email enviado com sucesso!', 'green');
}

(function initParcialToggle() {
    const selectParcial = document.getElementById('parcial_capeante');
    const wrapParcial = document.getElementById('wrap_parcial_num');
    const inputParcial = document.getElementById('parcial_num');
    if (!selectParcial || !wrapParcial || !inputParcial) return;

    const apply = () => {
        const isSim = (selectParcial.value === 's');
        wrapParcial.style.display = isSim ? '' : 'none';
        inputParcial.disabled = !isSim;
    };

    apply();
    selectParcial.addEventListener('change', apply);
})();
</script>
<script>
function salvarCapeante(idCapeante, idInternacao) {
    $.ajax({
        url: 'process_capeante_pdf.php',
        method: 'GET',
        data: {
            id_capeante: idCapeante,
            fk_int_capeante: idInternacao,
            save_only: '1'
        },
        success: function() {
            mostrarMensagem('Capeante salvo com sucesso!', 'green');
        },
        error: function() {
            mostrarMensagem('Erro ao salvar o capeante.', 'red');
        }
    });
}

function mostrarMensagem(texto, cor) {
    const div = document.getElementById('mensagemStatus');
    div.textContent = texto;
    div.style.backgroundColor = cor;
    div.style.color = 'white';
    div.style.display = 'block';

    setTimeout(() => {
        div.style.display = 'none';
    }, 5000);
}
</script>
<script>
(function enforceStartAfterLastPartial() {
  const inputInicio = document.getElementById('data_inicial_capeante');
  const inputFim    = document.getElementById('data_final_capeante'); // opcional, só se quiser usar
  const lastFinalEl = document.getElementById('last_final_date');
  const feedbackEl  = document.querySelector('.invalid-feedback.notif1');

  if (!inputInicio || !lastFinalEl) return;

  const lastFinalStr = (lastFinalEl.value || '').trim(); // "YYYY-MM-DD"
  if (!lastFinalStr) return; // não há período anterior com data fim → sem restrição

  // Helpers
  const parseYMD = (s) => {
    // s = "YYYY-MM-DD"
    const [y, m, d] = s.split('-').map(Number);
    if (!y || !m || !d) return null;
    // Usa Date no fuso local, evitando UTC
    return new Date(y, m - 1, d);
  };
  const formatYMD = (d) => {
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  };
  const addDays = (d, days) => {
    const x = new Date(d.getFullYear(), d.getMonth(), d.getDate());
    x.setDate(x.getDate() + days);
    return x;
  };

  const lastFinal = parseYMD(lastFinalStr);
  if (!lastFinal) return;

  // Regra: nova “Data Inicial” deve começar APÓS o fim da última parcial
  const minStart = addDays(lastFinal, 1); // dia seguinte
  const minStartStr = formatYMD(minStart);

  // Força atributo min no input (o navegador já impede datas menores)
  inputInicio.setAttribute('min', minStartStr);

  // Se já veio um valor menor, ajusta
  const coerceIfNeeded = () => {
    const val = inputInicio.value;
    if (!val) return;
    const cur = parseYMD(val);
    if (!cur) return;

    if (cur < minStart) {
      // marca inválido e corrige para o mínimo permitido
      inputInicio.classList.add('is-invalid');
      inputInicio.value = minStartStr;
      inputInicio.setCustomValidity('A data inicial deve ser posterior ao fim da última parcial.');
      if (feedbackEl) {
        feedbackEl.textContent = `A data inicial deve ser a partir de ${minStartStr.replace(/-/g,'/')} (dia seguinte ao término da última parcial).`;
        feedbackEl.style.display = 'block';
      }
      // remove estado inválido ao mudar novamente
      setTimeout(() => {
        inputInicio.classList.remove('is-invalid');
        inputInicio.setCustomValidity('');
        if (feedbackEl) feedbackEl.style.display = '';
      }, 10);
    } else {
      inputInicio.classList.remove('is-invalid');
      inputInicio.setCustomValidity('');
      if (feedbackEl) feedbackEl.style.display = '';
    }
  };

  // Valida ao carregar e ao alterar
  coerceIfNeeded();
  inputInicio.addEventListener('change', coerceIfNeeded);
  inputInicio.addEventListener('blur', coerceIfNeeded);

  // (Opcional) também impedir que a "Data Final" seja < "Data Inicial"
  if (inputFim) {
    const syncFimMin = () => {
      if (!inputInicio.value) return;
      inputFim.setAttribute('min', inputInicio.value);
    };
    syncFimMin();
    inputInicio.addEventListener('change', syncFimMin);
  }
})();
</script>


<script src="js/DataCapeante.js"></script>
<script src="js/stepper.js"></script>
<script src="js/scriptPdf.js" defer> </script>
<script src="js/valoresCapeante.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>