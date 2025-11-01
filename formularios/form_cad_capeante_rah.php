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
    'acomodacao_int' => null,
    'acomodacao_cap' => null,
    'lote_cap' => null,
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
</style>

<!-- ========================= FORM ========================= -->
<form id="form-capeante-rah" action="<?= $h($BASE_URL) ?>process_capeanteRah.php" method="POST"
    enctype="multipart/form-data">
    <input type="hidden" name="type" value="<?= $h($type) ?>">
    <input type="hidden" name="id_capeante" value="<?= $hi($fv('id_capeante')) ?>">
    <input type="hidden" name="fk_int_capeante" value="<?= $hi($fv('id_internacao') ?: $fv('fk_int_capeante')) ?>">

    <!-- IDENTIFICAÇÃO -->
    <div class="block">
        <h5>Identificação</h5>
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">ID Capeante</label>
                <input type="text" class="form-control" value="<?= $hi($fv('id_capeante')) ?>" readonly>
            </div>
            <div class="col-md-2">
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
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label">Data Internação</label>
                <input type="text" class="form-control" value="<?= $fmtDateBR($fv('data_intern_int')) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Acomodação (Internação)</label>
                <input type="text" class="form-control" value="<?= $h($fv('acomodacao_int')) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Acomodação (Conta)</label>
                <input type="text" class="form-control" name="acomodacao_cap" value="<?= $h($fv('acomodacao_cap')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Lote</label>
                <input type="text" class="form-control" name="lote_cap" value="<?= $h($fv('lote_cap')) ?>">
            </div>
        </div>
    </div>

    <!-- PERÍODO E VALORES GERAIS -->
    <div class="block">
        <h5>Período e Totais</h5>
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
        </div>
    </div>

    <!-- SETOR: APTO / ENFERMARIA -->
    <div class="block apto">
        <h5>Setor Apto / Enfermaria</h5>
        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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
    </div>

    <!-- SETOR: UTI -->
    <div class="block uti">
        <h5>Setor UTI</h5>
        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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
    </div>

    <!-- SETOR: CENTRO CIRÚRGICO -->
    <div class="block cc">
        <h5>Setor Centro Cirúrgico</h5>
        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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

            <div class="tuss-row">
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
<!-- Cálculo da coluna "Cobrado Após" (substituir o script anterior) -->
<script>
(function() {
    // sempre use a mesma instância global do jQuery do projeto
    var $ = window.jQuery;

    // Converte "R$ 1.234,56" -> 1234.56
    function moneyToFloat(s) {
        if (!s) return 0;
        s = ('' + s).replace(/\./g, '').replace(',', '.');
        s = s.replace(/[^\d.\-]/g, '');
        var v = parseFloat(s);
        return isNaN(v) ? 0 : v;
    }
    // 1234.5 -> "R$ 1.234,50"
    function floatToMoney(v) {
        if (!isFinite(v)) v = 0;
        var parts = v.toFixed(2).split('.');
        var i = parts[0],
            d = parts[1];
        i = i.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'R$ ' + i + ',' + d;
    }

    function recalcRow($row) {
        var vCob = moneyToFloat($row.find('.rah-cobrado').val() || '');
        var vGlo = moneyToFloat($row.find('.rah-glosado').val() || '');
        var vApo = vCob - vGlo;
        if (vApo < 0) vApo = 0;
        $row.find('.rah-liberado').val(floatToMoney(vApo));
    }

    function recalcTotals() {
        var tCob = 0,
            tGlo = 0,
            tApo = 0;
        $('.tuss-row').each(function() {
            var $r = $(this);
            tCob += moneyToFloat($r.find('.rah-cobrado').val());
            tGlo += moneyToFloat($r.find('.rah-glosado').val());
            tApo += moneyToFloat($r.find('.rah-liberado').val());
        });
        if ($('#total_cobrado').length) $('#total_cobrado').val(floatToMoney(tCob));
        if ($('#total_glosado').length) $('#total_glosado').val(floatToMoney(tGlo));
        if ($('#total_liberado').length) $('#total_liberado').val(floatToMoney(tApo));

        if ($('#valor_final_capeante').length) {
            var desc = parseFloat(($('#desconto_valor_cap').val() || '').replace(',', '.')) || 0;
            $('#valor_final_capeante').val(floatToMoney(tApo * (1 - desc / 100)));
        }
    }

    function recalcAll() {
        $('.tuss-row').each(function() {
            recalcRow($(this));
        });
        recalcTotals();
    }

    // Delegação: captura mudanças mesmo com maskMoney
    $(document).on('input change keyup', '.rah-cobrado, .rah-glosado', function() {
        var $row = $(this).closest('.tuss-row');
        recalcRow($row);
        recalcTotals();
    });

    // Se houver campo de desconto, reflita nos totais
    $(document).on('input change keyup', '#desconto_valor_cap', recalcTotals);

    // Recalcular no carregamento (e após a máscara aplicar formatação)
    $(function() {
        // primeira passada
        recalcAll();
        // depois que a maskMoney tocar nos campos
        setTimeout(recalcAll, 50);
    });
})();
</script>