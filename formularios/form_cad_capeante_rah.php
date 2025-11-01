<?php

/** =========================================================================
 *  formularios/form_capeante_auditRah.php
 *  Formulário “Capeante RAH” — página única, layout TUSS
 *  - Empilhado por setores (Apto/Enfermaria, UTI, Centro Cirúrgico)
 *  - Cada linha: Descrição | Qtd | Cobrado | Glosado | Cobrado Após (calc) | Observação
 *  - Usa selectAllInternacaoCap2() para carregar identificação
 *  ====================================================================== */

if (isset($conn) && $conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* Dependências principais do projeto */
require_once "models/usuario.php";
require_once "dao/usuarioDao.php";

require_once "dao/internacaoDao.php";
require_once "dao/pacienteDao.php";
require_once "dao/capeanteDao.php";
require_once "dao/patologiaDao.php";

/* Helpers */
$h = function ($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
};
$hi = function ($v) {
    return (int)($v ?? 0);
};
$fmtDateBR = function ($d): string {
    if (!is_string($d) || $d === '' || $d === '0000-00-00') return '';
    $ts = strtotime($d);
    return $ts ? date('d/m/Y', $ts) : '';
};

/* Instâncias */
$internacaoDAO = new internacaoDAO($conn, $BASE_URL);
$capeanteDAO   = new capeanteDAO($conn, $BASE_URL);

/* Parâmetros */
$id_capeante   = filter_input(INPUT_GET, 'id_capeante', FILTER_VALIDATE_INT) ?: null;
$id_internacao = filter_input(INPUT_GET, 'id_internacao', FILTER_VALIDATE_INT) ?: null;
$type          = (string)(filter_input(INPUT_GET, 'type') ?? 'update');

/* Recupera 1 linha principal */
$defaults = [
    'id_capeante' => null,
    'fk_int_capeante' => null,
    'id_internacao' => null,
    'nome_pac' => null,
    'nome_hosp' => null,
    'data_intern_int' => null,
    'pacote' => 'n',
    'parcial_capeante' => 'n',
    'parcial_num' => null,
    'data_inicial_capeante' => null,
    'data_final_capeante' => null,
    'valor_apresentado_capeante' => null,
    'valor_final_capeante' => null,
    'glosa_total' => null,
    'valor_diarias' => null,
    'glosa_diaria' => null,
    'valor_taxa' => null,
    'valor_materiais' => null,
    'valor_medicamentos' => null,
    'valor_sadt' => null,
    'valor_honorarios' => null,
    'valor_opme' => null
];

$where = '';
$order = 'ac.data_intern_int DESC, ac.id_internacao DESC';
if ($type === 'create' && $id_internacao)      $where = 'ac.id_internacao = ' . (int)$id_internacao;
elseif ($id_capeante)                           $where = 'ca.id_capeante = ' . (int)$id_capeante;

$row = $defaults;
if ($where) {
    $lista = $internacaoDAO->selectAllInternacaoCap2($where, $order, null);
    if (is_array($lista) && isset($lista[0]) && is_array($lista[0])) {
        $row = array_merge($defaults, $lista[0]);
    }
}
$fv = function (string $k) use ($row) {
    return $row[$k] ?? null;
};
$hojeYMD = date('Y-m-d');
?>
<!-- ========================= ESTILOS ========================= -->
<style>
body {
    background-color: #f5f7fa;
    font-family: "Nunito", "Helvetica Neue", Arial, sans-serif;
    color: #212529;
}

form#form-capeante-rah {
    max-width: 1400px;
    margin: 0 auto;
    padding: 10px 10px 40px 10px;
}

.block {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    padding: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
}

.block+.block {
    margin-top: 18px;
}

.block h5 {
    background: #f8f9fa;
    border-left: 6px solid #0d6efd;
    padding: 6px 10px;
    margin: -12px -12px 14px -12px;
    font-weight: 800;
    font-size: 1.02rem;
    color: #0d6efd;
    border-top-right-radius: 4px;
}

label.form-label {
    font-weight: 600;
    font-size: .85rem;
    color: #495057;
}

input.form-control,
select.form-select {
    border: 1px solid #ced4da;
    font-size: .9rem;
    height: 34px;
    padding: 4px 8px;
}

input.form-control:focus,
select.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 .1rem rgba(13, 110, 253, .25);
}

/* === GRID TUSS COM COLUNA CALCULADA ===
   Descrição | Qtd | Cobrado | Glosado | Cobrado Após (calc) | Observação */
.tuss-grid {
    display: grid;
    grid-template-columns: minmax(240px, 1.2fr) 110px 160px 160px 160px 1fr;
    gap: 8px 10px;
    align-items: center;
}

.tuss-grid .tg-head {
    font-weight: 700;
    font-size: .85rem;
    color: #495057;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 6px 8px;
}

.tuss-grid .tg-lab {
    font-weight: 600;
    color: #343a40;
}

.tuss-grid input.form-control {
    height: 34px;
    padding: 4px 8px;
}

.tuss-row {
    display: contents;
}

.tg-col-desc {
    grid-column: 1;
}

.tg-col-qtd {
    grid-column: 2;
}

.tg-col-cob {
    grid-column: 3;
    text-align: right;
}

.tg-col-glo {
    grid-column: 4;
    text-align: right;
}

/* NOVA COLUNA CALCULADA */
.tg-col-lib {
    grid-column: 5;
    text-align: right;
}

.tg-col-obs {
    grid-column: 6;
}

.block.apto h5 {
    border-left-color: #198754;
    color: #198754;
}

.block.uti h5 {
    border-left-color: #fd7e14;
    color: #fd7e14;
}

.block.cc h5 {
    border-left-color: #6f42c1;
    color: #6f42c1;
}

.actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width:768px) {
    .tuss-grid {
        grid-template-columns: minmax(150px, 1fr) 80px 120px 120px 120px 1fr;
    }

    .block h5 {
        font-size: .95rem;
    }

    label.form-label {
        font-size: .8rem;
    }
}

/* cabeçalho clicável com indicador */
.block h5.toggle {
    cursor: pointer;
    position: relative;
    user-select: none;
    padding-right: 28px;
    /* espaço pro caret */
}

.block h5.toggle::after {
    content: "▸";
    position: absolute;
    right: 10px;
    top: 6px;
    transition: transform .2s ease-in-out;
    font-weight: 700;
    opacity: .6;
}

/* quando aberto (aria-expanded=true), gira o caret */
.block h5.toggle[aria-expanded="true"]::after {
    transform: rotate(90deg);
    opacity: 1;
}
</style>

<!-- ========================= FORM ========================= -->
<form id="form-capeante-rah" action="<?= $h($BASE_URL) ?>process_capeanteRah.php" method="POST"
    enctype="multipart/form-data">
    <input type="hidden" name="type" value="<?= $h($type) ?>">
    <input type="hidden" name="id_capeante" value="<?= $hi($fv('id_capeante')) ?>">
    <input type="hidden" name="fk_int_capeante" value="<?= $hi($fv('id_internacao') ?: $fv('fk_int_capeante')) ?>">

    <!-- IDENTIFICAÇÃO -->
    <div class="block">
        <h4>Identificação</h4>
        <div class="row g-3">
            <div class="col-md-1">
                <label class="form-label">ID Capeante</label>
                <input type="text" class="form-control" value="<?= $hi($fv('id_capeante')) ?>" readonly>
            </div>
            <div class="col-md-1">
                <label class="form-label">ID Internação</label>
                <input type="text" class="form-control" value="<?= $hi($fv('id_internacao')) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Paciente</label>
                <input type="text" class="form-control" value="<?= $h($fv('nome_pac')) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Hospital</label>
                <input type="text" class="form-control" value="<?= $h($fv('nome_hosp')) ?>" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Data Internação</label>
                <input type="text" class="form-control" value="<?= $fmtDateBR($fv('data_intern_int')) ?>" readonly>
            </div>
        </div>

    </div>

    <!-- PERÍODO E VALORES GERAIS -->
    <div class="block">
        <h4>Período e Totais</h4>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Data Inicial</label>
                <input type="date" class="form-control" name="data_inicial_capeante"
                    value="<?= $h($fv('data_inicial_capeante') ?: $fv('data_intern_int')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Final</label>
                <input type="date" class="form-control" name="data_final_capeante"
                    value="<?= $h($fv('data_final_capeante')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Valor Apresentado</label>
                <input type="text" class="form-control dinheiro" name="valor_apresentado_capeante"
                    value="<?= is_numeric($fv('valor_apresentado_capeante')) ? number_format((float)$fv('valor_apresentado_capeante'), 2, ',', '.') : '' ?>"
                    placeholder="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Valor Final</label>
                <input type="text" class="form-control dinheiro" name="valor_final_capeante"
                    value="<?= is_numeric($fv('valor_final_capeante')) ? number_format((float)$fv('valor_final_capeante'), 2, ',', '.') : '' ?>"
                    placeholder="R$ 0,00">
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label">Pacote</label>
                <?php $pacoteVal = ($fv('pacote') ?? 'n'); ?>
                <select name="pacote" class="form-select">
                    <option value="n" <?= $pacoteVal === 'n' ? 'selected' : ''; ?>>Não</option>
                    <option value="s" <?= $pacoteVal === 's' ? 'selected' : ''; ?>>Sim</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Parcial</label>
                <?php $parcialVal = ($fv('parcial_capeante') ?? 'n'); ?>
                <select name="parcial_capeante" class="form-select" id="parcial_capeante">
                    <option value="n" <?= $parcialVal === 'n' ? 'selected' : ''; ?>>Não</option>
                    <option value="s" <?= $parcialVal === 's' ? 'selected' : ''; ?>>Sim</option>
                </select>
            </div>
            <div class="col-md-3" id="wrap_parcial_num" style="<?= $parcialVal === 's' ? '' : 'display:none' ?>">
                <label class="form-label">Número da Parcial</label>
                <input type="number" class="form-control" name="parcial_num" value="<?= $h($fv('parcial_num')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Fechamento</label>
                <input type="date" class="form-control" name="data_fech_capeante" value="<?= $h($hojeYMD) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Digitação</label>
                <input type="date" class="form-control" name="data_digit_capeante" value="<?= $h($hojeYMD) ?>">
            </div>
        </div>
    </div>
    <!-- DIÁRIAS (formato PDF) -->
    <div class="block" data-group="diarias">
        <!-- TÍTULO / TOGGLER -->
        <h5>
            Diárias
        </h5>

        <!-- CONTEÚDO COLAPSÁVEL (tuss-grid + totais) -->
        <div id="grp-diarias" class="collapse">
            <div class="tuss-grid mt-3">
                <div class="tg-head tg-col-desc">Diária</div>
                <div class="tg-head tg-col-qtd">Qtd.</div>
                <div class="tg-head tg-col-cob">Cobrado Antes</div>
                <div class="tg-head tg-col-glo">Glosado Após</div>
                <div class="tg-head tg-col-lib">Cobrado Após</div>
                <div class="tg-head tg-col-obs">Observação</div>

                <!-- QUARTO / APTO -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Quarto / Apto</div>
                    <input name="ac_quarto_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_quarto_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_quarto_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_quarto_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_quarto_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- DAY CLINIC -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Day Clinic</div>
                    <input name="ac_dayclinic_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_dayclinic_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_dayclinic_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_dayclinic_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_dayclinic_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- UTI -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">UTI</div>
                    <input name="ac_uti_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_uti_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_uti_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_uti_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_uti_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- UTI / SEMI -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">UTI / Semi</div>
                    <input name="ac_utisemi_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_utisemi_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_utisemi_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_utisemi_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_utisemi_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- ENFERMARIA -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Enfermaria</div>
                    <input name="ac_enfermaria_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_enfermaria_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_enfermaria_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_enfermaria_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_enfermaria_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- BERÇÁRIO -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Berçário</div>
                    <input name="ac_bercario_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_bercario_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_bercario_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_bercario_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_bercario_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- ACOMPANHANTE -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Acompanhante</div>
                    <input name="ac_acompanhante_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_acompanhante_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_acompanhante_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_acompanhante_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_acompanhante_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- ISOLAMENTO -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Isolamento</div>
                    <input name="ac_isolamento_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_isolamento_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_isolamento_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_isolamento_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_isolamento_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>
            </div>

            <!-- CONSOLIDADO LOCAL (Diárias) -->
            <div class="row g-2 mt-2 grp-totais">
                <div class="col-md-3">
                    <label class="form-label">Total Cobrado (Diárias)</label>
                    <input type="text" name="diarias_total_cobrado" class="form-control dinheiro grp-total-cobrado"
                        readonly value="R$ 0,00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Total Glosado (Diárias)</label>
                    <input type="text" name="diarias_total_glosado" class="form-control dinheiro grp-total-glosado"
                        readonly value="R$ 0,00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Total Cobrado Após (Diárias)</label>
                    <input type="text" name="diarias_total_liberado" class="form-control dinheiro grp-total-liberado"
                        readonly value="R$ 0,00">
                </div>
            </div>
        </div>
    </div>




    <!-- SETOR: APTO / ENFERMARIA -->
    <div class="block apto" data-group="apto">
        <h5>Setor Apto / Enfermaria</h5>

        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Terapias</div>
                <input name="ap_terapias_qtd" class="form-control tg-col-qtd" placeholder="Qtd">
                <input name="ap_terapias_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_terapias_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_terapias_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_terapias_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Taxas / Aluguéis</div>
                <input name="ap_taxas_qtd" class="form-control tg-col-qtd">
                <input name="ap_taxas_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_taxas_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_taxas_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_taxas_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material de Consumo</div>
                <input name="ap_mat_consumo_qtd" class="form-control tg-col-qtd">
                <input name="ap_mat_consumo_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_mat_consumo_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_mat_consumo_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_mat_consumo_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Medicamentos</div>
                <input name="ap_medicametos_qtd" class="form-control tg-col-qtd">
                <input name="ap_medicametos_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_medicametos_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_medicametos_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_medicametos_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Gases Medicinais</div>
                <input name="ap_gases_qtd" class="form-control tg-col-qtd">
                <input name="ap_gases_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_gases_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_gases_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_gases_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material Especial</div>
                <input name="ap_mat_espec_qtd" class="form-control tg-col-qtd">
                <input name="ap_mat_espec_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_mat_espec_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_mat_espec_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_mat_espec_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Exames / SADT</div>
                <input name="ap_exames_qtd" class="form-control tg-col-qtd">
                <input name="ap_exames_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_exames_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_exames_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_exames_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Hemoderivados</div>
                <input name="ap_hemoderivados_qtd" class="form-control tg-col-qtd">
                <input name="ap_hemoderivados_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_hemoderivados_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_hemoderivados_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_hemoderivados_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Honorários</div>
                <input name="ap_honorarios_qtd" class="form-control tg-col-qtd">
                <input name="ap_honorarios_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_honorarios_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_honorarios_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_honorarios_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>
        </div>

        <!-- CONSOLIDADO LOCAL (Apto) -->
        <div class="row g-2 mt-2 grp-totais">
            <div class="col-md-3">
                <label class="form-label">Total Cobrado (Apto)</label>
                <input type="text" name="apto_total_cobrado" class="form-control dinheiro grp-total-cobrado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Glosado (Apto)</label>
                <input type="text" name="apto_total_glosado" class="form-control dinheiro grp-total-glosado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Cobrado Após (Apto)</label>
                <input type="text" name="apto_total_liberado" class="form-control dinheiro grp-total-liberado" readonly
                    value="R$ 0,00">
            </div>
        </div>
    </div>


    <!-- SETOR: UTI -->
    <div class="block uti" data-group="uti">
        <h5>Setor UTI</h5>

        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Terapias</div>
                <input name="uti_terapias_qtd" class="form-control tg-col-qtd">
                <input name="uti_terapias_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_terapias_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_terapias_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_terapias_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Taxas / Aluguéis</div>
                <input name="uti_taxas_qtd" class="form-control tg-col-qtd">
                <input name="uti_taxas_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_taxas_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_taxas_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_taxas_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material de Consumo</div>
                <input name="uti_mat_consumo_qtd" class="form-control tg-col-qtd">
                <input name="uti_mat_consumo_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_mat_consumo_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_mat_consumo_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_mat_consumo_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Medicamentos</div>
                <input name="uti_medicametos_qtd" class="form-control tg-col-qtd">
                <input name="uti_medicametos_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_medicametos_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_medicametos_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_medicametos_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Gases Medicinais</div>
                <input name="uti_gases_qtd" class="form-control tg-col-qtd">
                <input name="uti_gases_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_gases_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_gases_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_gases_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material Especial</div>
                <input name="uti_mat_espec_qtd" class="form-control tg-col-qtd">
                <input name="uti_mat_espec_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_mat_espec_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_mat_espec_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_mat_espec_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Exames / SADT</div>
                <input name="uti_exames_qtd" class="form-control tg-col-qtd">
                <input name="uti_exames_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_exames_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_exames_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_exames_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Hemoderivados</div>
                <input name="uti_hemoderivados_qtd" class="form-control tg-col-qtd">
                <input name="uti_hemoderivados_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_hemoderivados_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_hemoderivados_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_hemoderivados_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Honorários</div>
                <input name="uti_honorarios_qtd" class="form-control tg-col-qtd">
                <input name="uti_honorarios_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_honorarios_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_honorarios_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_honorarios_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>
        </div>

        <!-- CONSOLIDADO LOCAL (UTI) -->
        <div class="row g-2 mt-2 grp-totais">
            <div class="col-md-3">
                <label class="form-label">Total Cobrado (UTI)</label>
                <input type="text" name="uti_total_cobrado" class="form-control dinheiro grp-total-cobrado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Glosado (UTI)</label>
                <input type="text" name="uti_total_glosado" class="form-control dinheiro grp-total-glosado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Cobrado Após (UTI)</label>
                <input type="text" name="uti_total_liberado" class="form-control dinheiro grp-total-liberado" readonly
                    value="R$ 0,00">
            </div>
        </div>
    </div>

    <!-- SETOR: CENTRO CIRÚRGICO -->
    <div class="block cc" data-group="cc">
        <h5>Setor Centro Cirúrgico</h5>

        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Terapias</div>
                <input name="cc_terapias_qtd" class="form-control tg-col-qtd">
                <input name="cc_terapias_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_terapias_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_terapias_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_terapias_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Taxas / Aluguéis</div>
                <input name="cc_taxas_qtd" class="form-control tg-col-qtd">
                <input name="cc_taxas_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_taxas_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_taxas_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_taxas_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material de Consumo</div>
                <input name="cc_mat_consumo_qtd" class="form-control tg-col-qtd">
                <input name="cc_mat_consumo_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_mat_consumo_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_mat_consumo_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_mat_consumo_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Medicamentos</div>
                <input name="cc_medicametos_qtd" class="form-control tg-col-qtd">
                <input name="cc_medicametos_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_medicametos_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_medicametos_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_medicametos_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Gases Medicinais</div>
                <input name="cc_gases_qtd" class="form-control tg-col-qtd">
                <input name="cc_gases_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_gases_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_gases_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_gases_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material Especial</div>
                <input name="cc_mat_espec_qtd" class="form-control tg-col-qtd">
                <input name="cc_mat_espec_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_mat_espec_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_mat_espec_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_mat_espec_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Exames / SADT</div>
                <input name="cc_exames_qtd" class="form-control tg-col-qtd">
                <input name="cc_exames_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_exames_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_exames_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_exames_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Hemoderivados</div>
                <input name="cc_hemoderivados_qtd" class="form-control tg-col-qtd">
                <input name="cc_hemoderivados_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_hemoderivados_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_hemoderivados_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_hemoderivados_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Honorários</div>
                <input name="cc_honorarios_qtd" class="form-control tg-col-qtd">
                <input name="cc_honorarios_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_honorarios_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_honorarios_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_honorarios_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>
        </div>

        <!-- CONSOLIDADO LOCAL (CC) -->
        <div class="row g-2 mt-2 grp-totais">
            <div class="col-md-3">
                <label class="form-label">Total Cobrado (CC)</label>
                <input type="text" name="cc_total_cobrado" class="form-control dinheiro grp-total-cobrado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Glosado (CC)</label>
                <input type="text" name="cc_total_glosado" class="form-control dinheiro grp-total-glosado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Cobrado Após (CC)</label>
                <input type="text" name="cc_total_liberado" class="form-control dinheiro grp-total-liberado" readonly
                    value="R$ 0,00">
            </div>
        </div>
    </div>


    <!-- AÇÕES -->
    <div class="block">
        <div class="actions">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Salvar</button>
            <button type="button" class="btn btn-outline-primary" id="btnSalvarPDF"><i class="bi bi-download"></i>
                Salvar PDF</button>
            <button type="button" class="btn btn-outline-secondary" id="btnEnviarEmail"><i
                    class="bi bi-envelope-fill"></i> Enviar PDF por e-mail</button>
        </div>
        <iframe id="iframeDownload" style="display:none;"></iframe>
        <div id="mensagemStatus"
            style="display:none;margin-top:10px;padding:10px;border-radius:5px;font-weight:bold;text-align:center;">
        </div>
    </div>
</form>

<!-- ========================= SCRIPTS ========================= -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- SHIM anti-erro para maskMoney (se carregar depois) -->
<script>
(function(w) {
    var $ = w.jQuery;
    if (!$) return;
    if (!$.fn.maskMoney) {
        $.fn.maskMoney = function() {
            return this;
        };
        $.fn.maskMoney.__stub__ = true;
    }
})(window);
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Inicialização / Comportamentos -->
<script>
(function() {
    function aplicarMascara(ctx) {
        if (!window.jQuery || !jQuery.fn || typeof jQuery.fn.maskMoney !== 'function') return;
        jQuery(ctx || document).find('.dinheiro').each(function() {
            jQuery(this).maskMoney({
                thousands: '.',
                decimal: ',',
                allowZero: true,
                precision: 2
            });
        });
    }

    jQuery(function() {
        aplicarMascara(document);

        $('#parcial_capeante').on('change', function() {
            if (this.value === 's') {
                $('#wrap_parcial_num').show();
            } else {
                $('#wrap_parcial_num').hide();
            }
        });

        $('#btnSalvarPDF').on('click', function() {
            const idCapeante = <?= $hi($fv('id_capeante')) ?>;
            const idInternacao = <?= $hi($fv('id_internacao') ?: $fv('fk_int_capeante')) ?>;
            const iframe = document.getElementById('iframeDownload');
            iframe.src = 'process_capeante_pdf.php?id_capeante=' + idCapeante +
                '&fk_int_capeante=' + idInternacao + '&save_only=1';
            mostrarMensagem('Capeante salvo em PDF com sucesso!', '#198754');
        });

        $('#btnEnviarEmail').on('click', function() {
            const idCapeante = <?= $hi($fv('id_capeante')) ?>;
            const idInternacao = <?= $hi($fv('id_internacao') ?: $fv('fk_int_capeante')) ?>;
            fetch('process_capeante_pdf.php?id_capeante=' + idCapeante + '&fk_int_capeante=' +
                idInternacao);
            mostrarMensagem('Email enviado com sucesso!', '#0d6efd');
        });
    });

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
})();
</script>

<!-- Cálculo da coluna "Cobrado Após" (apenas acrescentado) -->
<script>
/* =========================================================================
   CÁLCULOS RAH
   - Linha: Cobrado Após = max(0, Cobrado Antes - Glosado Após)
   - Bloco (data-group="..."): soma Cobrado / Glosado / Cobrado Após
   - Totais gerais: se existirem (#total_cobrado, #total_glosado, #total_liberado)
   - Robusto com maskMoney (formatações "R$ 1.234,56")
   ========================================================================= */
(function() {
    var $ = window.jQuery;

    // --- Utils moeda ---
    function moneyToFloat(s) {
        if (s == null) return 0;
        s = ('' + s).trim();
        if (!s) return 0;
        // remove R$, espaços, milhares e troca vírgula decimal
        s = s.replace(/[^\d.,-]/g, '').replace(/\./g, '').replace(',', '.');
        var v = parseFloat(s);
        return isNaN(v) ? 0 : v;
    }

    function floatToMoney(v) {
        if (!isFinite(v)) v = 0;
        var parts = v.toFixed(2).split('.');
        var i = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'R$ ' + i + ',' + parts[1];
    }

    // --- Linha ---
    function recalcRow($row) {
        var vCob = moneyToFloat($row.find('.rah-cobrado').val());
        var vGlo = moneyToFloat($row.find('.rah-glosado').val());
        var vLib = Math.max(0, vCob - vGlo);
        $row.find('.rah-liberado').val(floatToMoney(vLib));
    }

    // --- Bloco (consolidado local) ---
    function recalcBlock($block) {
        var tCob = 0,
            tGlo = 0,
            tLib = 0;
        $block.find('.tuss-row').each(function() {
            var $r = $(this);
            tCob += moneyToFloat($r.find('.rah-cobrado').val());
            tGlo += moneyToFloat($r.find('.rah-glosado').val());
            tLib += moneyToFloat($r.find('.rah-liberado').val());
        });
        // escreve nos campos do próprio bloco, se existirem
        var $cob = $block.find('.grp-total-cobrado');
        var $glo = $block.find('.grp-total-glosado');
        var $lib = $block.find('.grp-total-liberado');
        if ($cob.length) $cob.val(floatToMoney(tCob));
        if ($glo.length) $glo.val(floatToMoney(tGlo));
        if ($lib.length) $lib.val(floatToMoney(tLib));
    }

    // --- Totais gerais (opcional) ---
    function recalcGrandTotals() {
        var tCob = 0,
            tGlo = 0,
            tLib = 0;
        $('.tuss-row').each(function() {
            var $r = $(this);
            tCob += moneyToFloat($r.find('.rah-cobrado').val());
            tGlo += moneyToFloat($r.find('.rah-glosado').val());
            tLib += moneyToFloat($r.find('.rah-liberado').val());
        });
        if ($('#total_cobrado').length) $('#total_cobrado').val(floatToMoney(tCob));
        if ($('#total_glosado').length) $('#total_glosado').val(floatToMoney(tGlo));
        if ($('#total_liberado').length) $('#total_liberado').val(floatToMoney(tLib));

        // Se houver desconto (%) e valor_final_capeante, aplica no liberado geral
        var $desc = $('#desconto_valor_cap');
        var $valFinal = $('#valor_final_capeante');
        if ($desc.length && $valFinal.length) {
            var d = parseFloat(($desc.val() || '').replace(',', '.')) || 0;
            var vf = tLib * (1 - d / 100);
            $valFinal.val(floatToMoney(vf));
        }
    }

    // --- Orquestração ---
    function recalcAround($row) {
        // recalcula a linha
        recalcRow($row);
        // bloco mais próximo (com .block); se tiver consolidado, ele será atualizado
        var $block = $row.closest('.block');
        if ($block.length) recalcBlock($block);
        // totais gerais (se existirem)
        recalcGrandTotals();
    }

    function recalcAll() {
        $('.tuss-row').each(function() {
            recalcRow($(this));
        });
        // cada bloco com consolidado local
        $('.block').each(function() {
            recalcBlock($(this));
        });
        recalcGrandTotals();
    }

    // --- Eventos (funciona com maskMoney) ---
    $(document).on('input change keyup', '.rah-cobrado, .rah-glosado', function() {
        recalcAround($(this).closest('.tuss-row'));
    });

    // Dispara no carregamento; depois do maskMoney formatar, roda de novo
    $(function() {
        recalcAll();
        setTimeout(recalcAll, 60);
    });

    // Exponha utilitários, se precisar em outros scripts
    window.RAHCalc = {
        moneyToFloat,
        floatToMoney,
        recalcRow,
        recalcBlock,
        recalcGrandTotals,
        recalcAll
    };
})();
</script>
<script>
(function() {
    var $ = window.jQuery;

    // Reaplica máscara e força o recálculo do grupo quando a collapse abrir
    document.addEventListener('shown.bs.collapse', function(ev) {
        var $target = $(ev.target); // o div .collapse aberto
        var $block = $target.closest('.block');

        // aplica mascara nos campos desse bloco
        if ($.fn.maskMoney) {
            $block.find('.dinheiro').maskMoney({
                thousands: '.',
                decimal: ',',
                allowZero: true,
                precision: 2
            });
        }

        // dispara um recálculo local simulando mudança nos campos
        // (o seu script global já escuta os eventos e soma por bloco)
        $block.find('.rah-cobrado,.rah-glosado').first().trigger('input');
    });


})();
</script>
<script>
document.addEventListener('shown.bs.collapse', function(e) {
    const btn = document.querySelector('[data-bs-target="#' + e.target.id + '"] .toggle-caret');
    if (btn) btn.classList.add('rotate-180');
});
document.addEventListener('hidden.bs.collapse', function(e) {
    const btn = document.querySelector('[data-bs-target="#' + e.target.id + '"] .toggle-caret');
    if (btn) btn.classList.remove('rotate-180');
});
</script>
<style>
.rotate-180 {
    transform: rotate(180deg);
    transition: transform .2s;
}
</style>
<style>
/* Cursor e setinha no título */
.block>h5 {
    cursor: pointer;
    position: relative;
    padding-left: 28px;
}

.block>h5::before {
    content: "▸";
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 900;
    opacity: .7;
}

.block>h5[aria-expanded="true"]::before {
    content: "▾";
}

/* transição suave opcional (para quem não quer slide) */
/* .block-body{ transition:max-height .2s ease; overflow:hidden; } */
</style>

<style>
/* Cursor e setinha só para blocos colapsáveis */
.block>h5 {
    position: relative;
    padding-left: 28px;
}

.block>h5:not([data-static="1"]) {
    cursor: pointer;
}

.block>h5:not([data-static="1"])::before {
    content: "▸";
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 900;
    opacity: .7;
}

.block>h5[aria-expanded="true"]:not([data-static="1"])::before {
    content: "▾";
}
</style>

<script>
(function() {
    var $ = window.jQuery;
    if (!$) return;

    function isNaoColapsavel(t) {
        t = (t || "").trim().toLowerCase();
        return t === "identificação" || t === "periodo e totais" || t === "período e totais";
    }

    $('.block').each(function() {
        var $block = $(this);
        var $title = $block.children('h5').first();
        if (!$title.length) return;

        var $body = $title.nextAll();
        if (!$body.length) return;

        if (isNaoColapsavel($title.text())) {
            // Sempre aberto e sem interação
            $title.attr({
                'data-static': '1',
                'aria-expanded': 'true'
            });
            $body.show();
            $title.off('click.rahCollapse'); // garante sem toggle
            return;
        }

        // Colapsável: inicia fechado
        $title.attr('aria-expanded', 'false');
        $body.hide();

        $title.off('click.rahCollapse').on('click.rahCollapse', function() {
            var expanded = $title.attr('aria-expanded') === 'true';
            $title.attr('aria-expanded', expanded ? 'false' : 'true');
            $body.stop(true, true).slideToggle(160);
        });
    });
})();
</script>