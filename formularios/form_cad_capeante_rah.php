<?php
// form_capeante_auditRah.php

declare(strict_types=1);

// ================== BOOT BÁSICO (ajuste caminhos conforme seu projeto) ==================
require_once("templates/header.php");     // seu header padrão
require_once("models/message.php");

// Models/DAOs usados apenas para pintar cabeçalho do form
require_once("models/internacao.php");
require_once("dao/internacaoDao.php");

require_once("models/paciente.php");
require_once("dao/pacienteDao.php");

require_once("models/capeante.php");
require_once("dao/capeanteDao.php");

require_once("models/hospital.php");
require_once("dao/hospitalDao.php");

require_once("models/usuario.php");
require_once("dao/usuarioDao.php");

// $conn e $BASE_URL devem existir a partir do seu bootstrap (globals)
if (isset($conn) && $conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ================== HELPERS LOCAIS (tolerantes) ==================
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

// ================== PARÂMETROS DE ENTRADA ==================
$type          = (string)(filter_input(INPUT_GET, 'type') ?? 'create'); // create|update
$id_capeante   = filter_input(INPUT_GET, 'id_capeante', FILTER_VALIDATE_INT) ?: null;
$id_internacao = filter_input(INPUT_GET, 'id_internacao', FILTER_VALIDATE_INT) ?: null;

// ================== CARREGAMENTO DE CONTEXTO (pintar header do form) ==================
$internacaoDao = new internacaoDAO($conn, $BASE_URL);
$capeanteDao   = new capeanteDAO($conn, $BASE_URL);

$order   = "ac.data_intern_int DESC, ac.id_internacao DESC";
$limite  = null;
$obLimit = null;
$intern  = [];
if ($type === 'create' && $id_internacao) {
    $intern = $internacaoDao->selectAllInternacaoCap2("ac.id_internacao = " . (int)$id_internacao, $order, $obLimit);
} elseif ($type !== 'create' && $id_capeante) {
    $intern = $internacaoDao->selectAllInternacaoCap2("ca.id_capeante = " . (int)$id_capeante, $order, $obLimit);
}
$internRowDefaults = [
    'id_capeante'                  => $id_capeante,
    'id_internacao'                => $id_internacao,
    'nome_pac'                     => null,
    'nome_hosp'                    => null,
    'data_intern_int'              => null,
    'acomodacao_int'               => null,
    'acomodacao_cap'               => null,
    'lote_cap'                     => null,
    'pacote'                       => 'n',
    'parcial_capeante'             => 'n',
    'parcial_num'                  => null,
    'data_inicial_capeante'        => null,
    'data_final_capeante'          => null,
    'data_fech_capeante'           => date('Y-m-d'),
    'valor_diarias'                => null,
    'glosa_diaria'                 => null,
    'valor_apresentado_capeante'   => null,
    'valor_glosa_total'            => null,
    'valor_final_capeante'         => null,
    'desconto_valor_cap'           => null,
    // campos de outras sessões (se já vierem da base, mantém)
    'valor_taxa'                   => null,
    'valor_materiais'              => null,
    'valor_medicamentos'           => null,
    'valor_honorarios'             => null,
    'valor_sadt'                   => null,
    'valor_opme'                   => null
];
$internRow = $internRowDefaults;
if (is_array($intern) && isset($intern[0]) && is_array($intern[0])) {
    $internRow = array_merge($internRow, $intern[0]);
}
$val = function (string $k) use ($internRow) {
    return $internRow[$k] ?? null;
};

// ================== PÁGINA ÚNICA (EMPILHADA) ==================
?>
<div class="container-fluid px-0" id="main-container" style="margin-top:10px; background:#f5f6f8; min-height:100vh;">

    <form action="<?= $h($BASE_URL) ?>process_capeante.php" id="form-capeante" method="POST"
        enctype="multipart/form-data">
        <?php if ($type === "create"): ?>
        <input type="hidden" name="type" value="create">
        <input type="hidden" name="id_capeante" value="">
        <?php else: ?>
        <input type="hidden" name="type" value="update">
        <input type="hidden" name="id_capeante" value="<?= $hi($val('id_capeante')) ?>">
        <?php endif; ?>

        <input type="hidden" id="fk_int_capeante" name="fk_int_capeante"
            value="<?= $hi($val('id_internacao') ?: $id_internacao) ?>">
        <input type="hidden" id="fk_user_cap" name="fk_user_cap" value="<?= $hi($_SESSION['id_usuario'] ?? 0) ?>">
        <input type="hidden" id="aberto_cap" name="aberto_cap" value="n">
        <input type="hidden" id="em_auditoria_cap" name="em_auditoria_cap" value="s">

        <!-- ================== IDENTIFICAÇÃO ================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Identificação</h5>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Código Capeante</label>
                        <input type="text" class="form-control" value="<?= $h($val('id_capeante')) ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Internação</label>
                        <input type="text" class="form-control"
                            value="<?= $h($val('id_internacao') ?: $id_internacao) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Paciente</label>
                        <input type="text" class="form-control" value="<?= $h($val('nome_pac')) ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hospital</label>
                        <input type="text" class="form-control" value="<?= $h($val('nome_hosp')) ?>" readonly>
                    </div>

                    <div class="col-md-3">
                        <label for="data_inicial_capeante" class="form-label">Data Inicial</label>
                        <input type="date" id="data_inicial_capeante" name="data_inicial_capeante" class="form-control"
                            value="<?= $h($val('data_inicial_capeante') ?: $val('data_intern_int')) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="data_final_capeante" class="form-label">Data Final</label>
                        <input type="date" id="data_final_capeante" name="data_final_capeante" class="form-control"
                            value="<?= $h($val('data_final_capeante')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="acomodacao_cap" class="form-label">Acomodação</label>
                        <input type="text" id="acomodacao_cap" name="acomodacao_cap" class="form-control"
                            value="<?= $h($val('acomodacao_int') ?: $val('acomodacao_cap')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="lote_cap" class="form-label">Lote</label>
                        <input type="text" id="lote_cap" name="lote_cap" class="form-control"
                            value="<?= $h($val('lote_cap')) ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="pacote" class="form-label">Pacote</label>
                        <?php $pacoteVal = ($val('pacote') ?? 'n'); ?>
                        <select id="pacote" name="pacote" class="form-select">
                            <option value="n" <?= $pacoteVal === 'n' ? 'selected' : '' ?>>Não</option>
                            <option value="s" <?= $pacoteVal === 's' ? 'selected' : '' ?>>Sim</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="parcial_capeante" class="form-label">Parcial</label>
                        <?php $parcialDefault = ($type === 'create' ? 's' : (($val('parcial_capeante') ?? 'n') === 's' ? 's' : 'n')); ?>
                        <select id="parcial_capeante" name="parcial_capeante" class="form-select">
                            <option value="n" <?= $parcialDefault === 'n' ? 'selected' : '' ?>>Não</option>
                            <option value="s" <?= $parcialDefault === 's' ? 'selected' : '' ?>>Sim</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="parcial_num" class="form-label">Número Parcial</label>
                        <input type="number" id="parcial_num" name="parcial_num" class="form-control"
                            value="<?= $h($val('parcial_num')) ?>" <?= $parcialDefault === 's' ? '' : 'disabled' ?>>
                    </div>
                    <div class="col-md-3">
                        <label for="data_fech_capeante" class="form-label">Data Fechamento</label>
                        <input type="date" id="data_fech_capeante" name="data_fech_capeante" class="form-control"
                            value="<?= $h($val('data_fech_capeante') ?: date('Y-m-d')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- ================== DIÁRIAS (ACOMODAÇÕES) ================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Acomodações (Diárias)</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th style="min-width:160px;">Tipo</th>
                                <th>Qtd.</th>
                                <th>Cobrado</th>
                                <th>Glosado</th>
                                <th>Após auditoria</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tipos = ['QUARTO/APTO', 'DAY CLINIC', 'UTI', 'UTI/SEMI', 'ENFERMARIA', 'BERÇÁRIO', 'ACOMPANHANTE', 'ISOLAMENTO'];
                            foreach ($tipos as $i => $t): ?>
                            <tr>
                                <td><?= $h($t) ?></td>
                                <td><input type="text" class="form-control form-control-sm diarias-qtd"
                                        data-row="<?= $i ?>"></td>
                                <td><input type="text" class="form-control form-control-sm dinheiro diarias-cob"
                                        data-row="<?= $i ?>"></td>
                                <td><input type="text" class="form-control form-control-sm dinheiro diarias-glo"
                                        data-row="<?= $i ?>"></td>
                                <td><input type="text" class="form-control form-control-sm dinheiro diarias-apr"
                                        data-row="<?= $i ?>" readonly></td>
                                <td><input type="text" class="form-control form-control-sm diarias-obs"
                                        data-row="<?= $i ?>"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="valor_diarias" class="form-label">Valor Diárias (total)</label>
                        <input type="text" class="form-control dinheiro" id="valor_diarias" name="valor_diarias"
                            value="<?= is_numeric($val('valor_diarias')) ? number_format((float)$val('valor_diarias'), 2, ',', '.') : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="glosa_diaria" class="form-label">Glosa Diárias (total)</label>
                        <input type="text" class="form-control dinheiro" id="glosa_diaria" name="glosa_diaria"
                            value="<?= is_numeric($val('glosa_diaria')) ? number_format((float)$val('glosa_diaria'), 2, ',', '.') : '' ?>">
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Linhas padrão para as três seções (AP/UTI/CC)
        $apRows  = [
            ['TERAPIAS', 'ap_terapias'],
            ['TAXAS / ALUGUÉIS', 'ap_taxas'],
            ['MATERIAL DE CONSUMO', 'ap_mat_consumo'],
            ['MEDICAMENTOS', 'ap_medicametos'],
            ['GASES MEDICINAIS', 'ap_gases'],
            ['MATERIAL ESPECIAL', 'ap_mat_espec'],
            ['EXAMES', 'ap_exames'],
            ['HEMODERIVADOS', 'ap_hemoderivados'],
            ['HONORÁRIOS', 'ap_honorarios'],
        ];
        $utiRows = [
            ['TERAPIAS', 'uti_terapias'],
            ['TAXAS / ALUGUÉIS', 'uti_taxas'],
            ['MATERIAL DE CONSUMO', 'uti_mat_consumo'],
            ['MEDICAMENTOS', 'uti_medicametos'],
            ['GASES MEDICINAIS', 'uti_gases'],
            ['MATERIAL ESPECIAL', 'uti_mat_espec'],
            ['EXAMES', 'uti_exames'],
            ['HEMODERIVADOS', 'uti_hemoderivados'],
            ['HONORÁRIOS', 'uti_honorarios'],
        ];
        $ccRows  = [
            ['TERAPIAS', 'cc_terapias'],
            ['TAXAS / ALUGUÉIS', 'cc_taxas'],
            ['MATERIAL DE CONSUMO', 'cc_mat_consumo'],
            ['MEDICAMENTOS', 'cc_medicametos'],
            ['GASES MEDICINAIS', 'cc_gases'],
            ['MATERIAL ESPECIAL', 'cc_mat_espec'],
            ['EXAMES', 'cc_exames'],
            ['HEMODERIVADOS', 'cc_hemoderivados'],
            ['HONORÁRIOS', 'cc_honorarios'],
        ];
        ?>

        <!-- ================== AP ================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Despesas no Quarto/Enfermaria (AP)</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th style="min-width:220px;">Item</th>
                                <th>Qtd.</th>
                                <th>Cobrado</th>
                                <th>Glosado</th>
                                <th>Após auditoria</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apRows as [$label, $prefix]): ?>
                            <tr>
                                <td><?= $h($label) ?></td>
                                <td><input name="<?= $prefix ?>_qtd" class="form-control form-control-sm"
                                        value="<?= $h($val($prefix . '_qtd')) ?>"></td>
                                <td><input name="<?= $prefix ?>_cobrado" class="form-control form-control-sm dinheiro"
                                        value="<?= $h($val($prefix . '_cobrado')) ?>"></td>
                                <td><input name="<?= $prefix ?>_glosado" class="form-control form-control-sm dinheiro"
                                        value="<?= $h($val($prefix . '_glosado')) ?>"></td>
                                <td><input class="form-control form-control-sm dinheiro" data-aprov-of="<?= $prefix ?>"
                                        readonly></td>
                                <td><input name="<?= $prefix ?>_obs" class="form-control form-control-sm"
                                        value="<?= $h($val($prefix . '_obs')) ?>"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================== UTI ================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Despesas na UTI</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th style="min-width:220px;">Item</th>
                                <th>Qtd.</th>
                                <th>Cobrado</th>
                                <th>Glosado</th>
                                <th>Após auditoria</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($utiRows as [$label, $prefix]): ?>
                            <tr>
                                <td><?= $h($label) ?></td>
                                <td><input name="<?= $prefix ?>_qtd" class="form-control form-control-sm"
                                        value="<?= $h($val($prefix . '_qtd')) ?>"></td>
                                <td><input name="<?= $prefix ?>_cobrado" class="form-control form-control-sm dinheiro"
                                        value="<?= $h($val($prefix . '_cobrado')) ?>"></td>
                                <td><input name="<?= $prefix ?>_glosado" class="form-control form-control-sm dinheiro"
                                        value="<?= $h($val($prefix . '_glosado')) ?>"></td>
                                <td><input class="form-control form-control-sm dinheiro" data-aprov-of="<?= $prefix ?>"
                                        readonly></td>
                                <td><input name="<?= $prefix ?>_obs" class="form-control form-control-sm"
                                        value="<?= $h($val($prefix . '_obs')) ?>"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================== CC ================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Despesas no Centro Cirúrgico (CC)</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th style="min-width:220px;">Item</th>
                                <th>Qtd.</th>
                                <th>Cobrado</th>
                                <th>Glosado</th>
                                <th>Após auditoria</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ccRows as [$label, $prefix]): ?>
                            <tr>
                                <td><?= $h($label) ?></td>
                                <td><input name="<?= $prefix ?>_qtd" class="form-control form-control-sm"
                                        value="<?= $h($val($prefix . '_qtd')) ?>"></td>
                                <td><input name="<?= $prefix ?>_cobrado" class="form-control form-control-sm dinheiro"
                                        value="<?= $h($val($prefix . '_cobrado')) ?>"></td>
                                <td><input name="<?= $prefix ?>_glosado" class="form-control form-control-sm dinheiro"
                                        value="<?= $h($val($prefix . '_glosado')) ?>"></td>
                                <td><input class="form-control form-control-sm dinheiro" data-aprov-of="<?= $prefix ?>"
                                        readonly></td>
                                <td><input name="<?= $prefix ?>_obs" class="form-control form-control-sm"
                                        value="<?= $h($val($prefix . '_obs')) ?>"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================== TOTAIS & OUTROS ================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Totais & Outros</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="valor_apresentado_capeante">Valor Apresentado</label>
                        <input type="text" class="form-control dinheiro" id="valor_apresentado_capeante"
                            name="valor_apresentado_capeante"
                            value="<?= is_numeric($val('valor_apresentado_capeante')) ? number_format((float)$val('valor_apresentado_capeante'), 2, ',', '.') : '' ?>"
                            required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Total Glosas</label>
                        <input type="text" class="form-control dinheiro" id="total_glosas_geral"
                            name="valor_glosa_total" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valor Final (após glosas)</label>
                        <input type="text" class="form-control dinheiro" id="total_final_apos"
                            name="valor_final_capeante" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="desconto_valor_cap">Desconto (%)</label>
                        <input type="number" class="form-control" id="desconto_valor_cap" name="desconto_valor_cap"
                            value="<?= $h($val('desconto_valor_cap')) ?>">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Salvar</button>
                    <button type="button" class="btn btn-outline-primary"
                        onclick="baixarPDF(<?= $hi($val('id_capeante')) ?>, <?= $hi($val('id_internacao') ?: $id_internacao) ?>)">
                        <i class="bi bi-download"></i> Salvar PDF
                    </button>
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="enviarPDF(<?= $hi($val('id_capeante')) ?>, <?= $hi($val('id_internacao') ?: $id_internacao) ?>)">
                        <i class="bi bi-envelope-fill"></i> Email PDF
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<!-- ================== JS (máscara + cálculos) ================== -->

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- SHIM: evita erro se algum script chamar .maskMoney antes do plugin carregar -->
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

<!-- Plugin maskMoney -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>

<script>
(function() {
    function aplicarMascara(ctx) {
        if (!window.jQuery || !jQuery.fn || typeof jQuery.fn.maskMoney !== 'function' || jQuery.fn.maskMoney
            .__stub__) return;
        jQuery(ctx || document).find('.dinheiro').each(function() {
            jQuery(this).maskMoney({
                thousands: '.',
                decimal: ',',
                allowZero: true,
                allowNegative: false,
                precision: 2
            });
        });
    }
    jQuery(function() {
        aplicarMascara(document);
    });
})();
</script>

<script>
(function() {
    function parseBR(v) {
        if (!v) return 0;
        v = (v + '').replace(/\./g, '').replace(',', '.');
        var n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    function fmtBR(n) {
        return (n || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function recomputeAfterForPrefix(prefix, scope) {
        var cob = parseBR($(scope).find('input[name="' + prefix + '_cobrado"]').val());
        var glo = parseBR($(scope).find('input[name="' + prefix + '_glosado"]').val());
        var apr = Math.max(cob - glo, 0);
        $(scope).find('input[data-aprov-of="' + prefix + '"]').val(fmtBR(apr));
        return {
            cob,
            glo,
            apr
        };
    }

    function somarGrupo($tbody) {
        var totC = 0,
            totG = 0,
            totA = 0;
        $tbody.find('input[data-aprov-of]').each(function() {
            var prefix = $(this).attr('data-aprov-of');
            var row = $(this).closest('tr');
            var r = recomputeAfterForPrefix(prefix, row);
            totC += r.cob;
            totG += r.glo;
            totA += r.apr;
        });
        return {
            totC,
            totG,
            totA
        };
    }

    function recomputeDiarias() {
        var dC = 0,
            dG = 0;
        $('.diarias-cob').each(function() {
            dC += parseBR(this.value);
        });
        $('.diarias-glo').each(function() {
            dG += parseBR(this.value);
        });
        $('.diarias-apr').each(function(i, el) {
            var row = $(el).closest('tr');
            var c = parseBR(row.find('.diarias-cob').val());
            var g = parseBR(row.find('.diarias-glo').val());
            $(el).val(fmtBR(Math.max(c - g, 0)));
        });
        $('#valor_diarias').val(fmtBR(dC)).trigger('change');
        $('#glosa_diaria').val(fmtBR(dG)).trigger('change');
        return {
            totC: dC,
            totG: dG,
            totA: Math.max(dC - dG, 0)
        };
    }

    function recomputeAll() {
        var gAP = somarGrupo($('table:contains("Despesas no Quarto") tbody'));
        var gUTI = somarGrupo($('table:contains("Despesas na UTI") tbody'));
        var gCC = somarGrupo($('table:contains("Centro Cirúrgico") tbody'));
        var gDia = recomputeDiarias();

        var totalGlosas = gAP.totG + gUTI.totG + gCC.totG + gDia.totG;
        $('#total_glosas_geral').val(fmtBR(totalGlosas));

        var apresentado = parseBR($('#valor_apresentado_capeante').val());
        var final = Math.max(apresentado - totalGlosas, 0);
        $('#total_final_apos').val(fmtBR(final));
    }

    $(document).on('input change', 'input[name$="_cobrado"], input[name$="_glosado"]', recomputeAll);
    $(document).on('input change', '.diarias-cob, .diarias-glo', recomputeAll);
    $(document).on('input change', '#valor_apresentado_capeante, #desconto_valor_cap', recomputeAll);
    $('#parcial_capeante').on('change', function() {
        $('#parcial_num').prop('disabled', $(this).val() !== 's');
    });

    $(function() {
        recomputeAll();
    });
})();
</script>

<script>
// Botões PDF (mantém compatível com seu fluxo atual)
function baixarPDF(idCapeante, idInternacao) {
    if (!idCapeante || !idInternacao) {
        return;
    }
    var iframe = document.getElementById("iframeDownload");
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'iframeDownload';
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
    }
    iframe.src = "process_capeante_pdf.php?id_capeante=" + idCapeante + "&fk_int_capeante=" + idInternacao +
        "&save_only=1";
}

function enviarPDF(idCapeante, idInternacao) {
    if (!idCapeante || !idInternacao) {
        return;
    }
    fetch("process_capeante_pdf.php?id_capeante=" + idCapeante + "&fk_int_capeante=" + idInternacao)
        .catch(() => {});
}
</script>