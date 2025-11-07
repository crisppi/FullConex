<?php

/**
 * process_rah.php (revisado)
 * - Salva FKs/flags (médico, enfermeiro, adm) apenas em tb_capeante
 * - Nas tabelas acessórias, persiste somente valores (sem FKs de profissionais)
 */

declare(strict_types=1);

require_once "globals.php";
require_once "db.php";

require_once "models/capeante.php";
require_once "dao/capeanteDao.php";

require_once "models/message.php";
require_once "dao/usuarioDao.php";

$message     = new Message($BASE_URL);
$capeanteDao = new capeanteDAO($conn, $BASE_URL);

$type = filter_input(INPUT_POST, "type") ?: 'update';

/* Loga os dados recebidos do formulário para facilitar depuração */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formPayload = $_POST;
    foreach ($formPayload as $key => $value) {
        if (is_string($value) && strlen($value) > 500) {
            $formPayload[$key] = substr($value, 0, 500) . '... (truncated)';
        }
    }
    $jsonFlags = 0;
    foreach (['JSON_UNESCAPED_UNICODE', 'JSON_UNESCAPED_SLASHES', 'JSON_PARTIAL_OUTPUT_ON_ERROR'] as $constName) {
        if (defined($constName)) {
            $jsonFlags |= constant($constName);
        }
    }
    $jsonPayload = $jsonFlags ? json_encode($formPayload, $jsonFlags) : json_encode($formPayload);
    error_log('[RAH][FORM_DATA] ' . ($jsonPayload ?: 'Falha ao converter os dados do formulário em JSON.'));
}

/* ---------- Helpers ---------- */
function limparCampo($valor)
{
    $valor = (string)($valor ?? '');
    $valor = str_replace(['R$', ' '], '', $valor);
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    return $valor === '' ? null : $valor;
}
function moneyPOST($name)
{
    $v = limparCampo(filter_input(INPUT_POST, $name));
    return $v === null ? 0.0 : (float)$v;
}
function intPOST($name)
{
    $v = filter_input(INPUT_POST, $name, FILTER_VALIDATE_INT);
    return ($v === false || $v === null) ? null : (int)$v;
}
function strPOST($name)
{
    $v = filter_input(INPUT_POST, $name);
    return $v === null ? null : trim($v);
}
function datePOST($name)
{
    $v = strPOST($name);
    if (!$v || $v === '0000-00-00') return null;
    return $v;
}
/* varchar(20) destino: numérico simples em string (sem R$) */
function to_varchar20($num)
{
    if ($num === null) $num = 0;
    $num = (float)$num;
    return number_format($num, 2, '.', ''); // ex.: 1234.56
}

/* ---------- Descoberta dinâmica de colunas ---------- */
function table_columns(PDO $conn, string $table): array
{
    static $cache = [];
    $key = strtolower($table);
    if (isset($cache[$key])) return $cache[$key];

    $cols = [];
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM `{$table}`");
        if ($stmt) {
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $cols[] = strtolower((string)$r['Field']);
            }
        }
    } catch (Throwable $e) {
    }
    return $cache[$key] = $cols;
}

/**
 * Mantém apenas chaves que existem na tabela, com suporte a aliases.
 * $aliases = ['logico' => ['col1','col2',...]]
 */
function filter_existing_cols(PDO $conn, string $table, array $candidates, array $aliases = []): array
{
    $existing = table_columns($conn, $table);
    $result = [];

    foreach ($candidates as $col => $val) {
        if ($val === null) continue;
        if (in_array(strtolower($col), $existing, true)) {
            $result[$col] = $val;
        }
    }
    foreach ($aliases as $logical => $options) {
        $value = $candidates[$logical] ?? null;
        if ($value === null) continue;
        foreach ($options as $alias) {
            if (in_array(strtolower($alias), $existing, true)) {
                $result[$alias] = $value;
                break;
            }
        }
    }
    return $result;
}

/* ---------- Captura chaves/identificação ---------- */
$id_capeante    = intPOST("id_capeante");
$fk_internacao  = intPOST("fk_int_capeante");

$pacote         = strPOST("pacote") ?: 'n';
$parcial        = strPOST("parcial_capeante") ?: 'n';
$parcial_num    = intPOST("parcial_num");

$data_inicial   = datePOST("data_inicial_capeante");
$data_final     = datePOST("data_final_capeante");
$data_fech      = datePOST("data_fech_capeante");
$data_digit     = datePOST("data_digit_capeante");

/* ---------- Profissionais (inputs hidden) + flags opcionais ---------- */
$aud_med_capeante = intPOST('fk_id_aud_med'); // médico auditor
$aud_enf_capeante = intPOST('fk_id_aud_enf'); // enfermeiro auditor
$adm_capeante     = intPOST('fk_id_aud_adm'); // administrativo/adm

$med_check   = strtolower((string)(strPOST('med_check')   ?? 'n')) === 's' ? 's' : 'n';
$enfer_check = strtolower((string)(strPOST('enfer_check') ?? 'n')) === 's' ? 's' : 'n';
$adm_check   = strtolower((string)(strPOST('adm_check')   ?? 'n')) === 's' ? 's' : 'n';

/* ---------- Consolidação por linha ---------- */
function capturarLinha($prefix)
{
    $qtd     = intPOST($prefix . "_qtd") ?? 0;
    $cobrado = moneyPOST($prefix . "_cobrado");
    $glosado = moneyPOST($prefix . "_glosado");
    $obs     = strPOST($prefix . "_obs");
    $lib     = max(0.0, $cobrado - $glosado);

    return [
        'qtd'     => (int)$qtd,
        'cobrado' => (float)$cobrado,
        'glosado' => (float)$glosado,
        'lib'     => (float)$lib,
        'obs'     => $obs
    ];
}
function somaCampo($arr, $chave)
{
    $t = 0.0;
    foreach ($arr as $r) $t += (float)($r[$chave] ?? 0);
    return $t;
}

/* ============================================================
 * DIÁRIAS (linhas AC_*)
 * ============================================================ */
$diarias_ids = [
    'ac_quarto',
    'ac_dayclinic',
    'ac_uti',
    'ac_utisemi',
    'ac_enfermaria',
    'ac_bercario',
    'ac_acompanhante',
    'ac_isolamento'
];
$diarias_rows = [];
foreach ($diarias_ids as $idp) $diarias_rows[$idp] = capturarLinha($idp);

$diarias_cob = somaCampo($diarias_rows, 'cobrado');
$diarias_glo = somaCampo($diarias_rows, 'glosado');
$diarias_lib = somaCampo($diarias_rows, 'lib');

/* ============================================================
 * APTO / ENFERMARIA
 * ============================================================ */
$ap_form = [
    'terapias' => 'ap_terapias',
    'taxas' => 'ap_taxas',
    'mat_consumo' => 'ap_mat_consumo',
    'medicamentos' => 'ap_medicametos', // legado
    'gases' => 'ap_gases',
    'mat_especial' => 'ap_mat_espec',
    'exames' => 'ap_exames',
    'hemoderivados' => 'ap_hemoderivados',
    'honorarios' => 'ap_honorarios',
];
$ap_calc = [];
foreach ($ap_form as $cat => $pfx) $ap_calc[$cat] = capturarLinha($pfx);

/* ============================================================
 * UTI
 * ============================================================ */
$uti_form = [
    'terapias' => 'uti_terapias',
    'taxas' => 'uti_taxas',
    'mat_consumo' => 'uti_mat_consumo',
    'medicamentos' => 'uti_medicametos',
    'gases' => 'uti_gases',
    'mat_especial' => 'uti_mat_espec',
    'exames' => 'uti_exames',
    'hemoderivados' => 'uti_hemoderivados',
    'honorarios' => 'uti_honorarios',
];
$uti_calc = [];
foreach ($uti_form as $cat => $pfx) $uti_calc[$cat] = capturarLinha($pfx);

/* ============================================================
 * CENTRO CIRÚRGICO
 * ============================================================ */
$cc_form = [
    'terapias' => 'cc_terapias',
    'taxas' => 'cc_taxas',
    'mat_consumo' => 'cc_mat_consumo',
    'medicamentos' => 'cc_medicametos',
    'gases' => 'cc_gases',
    'mat_especial' => 'cc_mat_espec',
    'exames' => 'cc_exames',
    'hemoderivados' => 'cc_hemoderivados',
    'honorarios' => 'cc_honorarios',
];
$cc_calc = [];
foreach ($cc_form as $cat => $pfx) $cc_calc[$cat] = capturarLinha($pfx);

/* ============================================================
 * OUTROS (Pacote / Remoção)
 * ============================================================ */
$outros_form = ['pacote' => 'outros_pacote', 'remocao' => 'outros_remocao'];
$outros_calc = [];
foreach ($outros_form as $cat => $pfx) $outros_calc[$cat] = capturarLinha($pfx);

/* ---------- Totais ---------- */
function somarSetor($calc)
{
    $tCob = $tGlo = $tLib = 0.0;
    foreach ($calc as $r) {
        $tCob += (float)$r['cobrado'];
        $tGlo += (float)$r['glosado'];
        $tLib += (float)$r['lib'];
    }
    return [$tCob, $tGlo, $tLib];
}
list($ap_cob, $ap_glo, $ap_lib)             = somarSetor($ap_calc);
list($uti_cob, $uti_glo, $uti_lib)          = somarSetor($uti_calc);
list($cc_cob, $cc_glo, $cc_lib)             = somarSetor($cc_calc);
list($outros_cob, $outros_glo, $outros_lib) = somarSetor($outros_calc);

$total_cobrado  = (float)$diarias_cob + $ap_cob + $uti_cob + $cc_cob + $outros_cob;
$total_glosado  = (float)$diarias_glo + $ap_glo + $uti_glo + $cc_glo + $outros_glo;
$total_liberado = (float)$diarias_lib + $ap_lib + $uti_lib + $cc_lib + $outros_lib;

/* ---------- Totais por categoria (AP/UTI/CC) ---------- */
$cat_sum = function ($key) use ($ap_calc, $uti_calc, $cc_calc) {
    return (float)($ap_calc[$key]['lib'] + $uti_calc[$key]['lib'] + $cc_calc[$key]['lib']);
};
$cat_glo = function ($key) use ($ap_calc, $uti_calc, $cc_calc) {
    return (float)($ap_calc[$key]['glosado'] + $uti_calc[$key]['glosado'] + $cc_calc[$key]['glosado']);
};

$valor_diarias      = (float)$diarias_lib;
$valor_taxa         = $cat_sum('taxas');
$valor_materiais    = $cat_sum('mat_consumo');
$valor_medicamentos = $cat_sum('medicamentos');
$valor_sadt         = $cat_sum('exames');
$valor_honorarios   = $cat_sum('honorarios');
$valor_opme         = $cat_sum('mat_especial');
$valor_oxig         = $cat_sum('gases');

$glosa_diaria       = (float)$diarias_glo;
$glosa_taxas        = $cat_glo('taxas');
$glosa_matmed       = $cat_glo('mat_consumo');
$glosa_medicamentos = $cat_glo('medicamentos');
$glosa_sadt         = $cat_glo('exames');
$glosa_honorarios   = $cat_glo('honorarios');
$glosa_opme         = $cat_glo('mat_especial');
$glosa_oxig         = $cat_glo('gases');

$valor_apresentado  = (float)$total_cobrado;

/* Desconto (%) opcional */
$desconto_valor_cap = strPOST("desconto_valor_cap"); // "5" (%), ou null
$desconto_pct       = $desconto_valor_cap !== null ? (float)str_replace(',', '.', $desconto_valor_cap) : 0.0;
$valor_final        = $total_liberado * (1 - ($desconto_pct / 100));
$valor_glosa_total  = max(0.0, $valor_apresentado - $valor_final);

/* ============================================================
 * Atualiza/Cria CAPEANTE (identificação + períodos + totais)
 * ============================================================ */
if ($type === 'create') {
    $cap = new capeante();
    $cap->fk_int_capeante             = $fk_internacao;
    $cap->data_inicial_capeante       = $data_inicial;
    $cap->data_final_capeante         = $data_final;
    $cap->data_fech_capeante          = $data_fech;
    $cap->data_digit_capeante         = $data_digit;
    $cap->pacote                      = $pacote;
    $cap->parcial_capeante            = $parcial;
    $cap->parcial_num                 = $parcial_num;

    $cap->valor_apresentado_capeante  = $valor_apresentado;
    $cap->valor_final_capeante        = $valor_final;

    // Totais por categoria (compatibilidade)
    $cap->valor_diarias               = $valor_diarias;
    $cap->valor_taxa                  = $valor_taxa;
    $cap->valor_materiais             = $valor_materiais;
    $cap->valor_medicamentos          = $valor_medicamentos;
    $cap->valor_sadt                  = $valor_sadt;
    $cap->valor_honorarios            = $valor_honorarios;
    $cap->valor_opme                  = $valor_opme;
    $cap->valor_oxig                  = $valor_oxig;

    $cap->glosa_diaria                = $glosa_diaria;
    $cap->glosa_taxas                 = $glosa_taxas;
    $cap->glosa_matmed                = $glosa_matmed;
    $cap->glosa_medicamentos          = $glosa_medicamentos;
    $cap->glosa_sadt                  = $glosa_sadt;
    $cap->glosa_honorarios            = $glosa_honorarios;
    $cap->glosa_opme                  = $glosa_opme;
    $cap->glosa_oxig                  = $glosa_oxig;
    $cap->valor_glosa_total           = $valor_glosa_total;

    $cap->desconto_valor_cap          = $desconto_valor_cap;
    $cap->last_cap                    = 1;

    $capeanteDao->create($cap);

    // ID criado
    $novoId = $cap->id_capeante ?? null;
    if (!$novoId) $novoId = (int)$conn->lastInsertId();
    $id_capeante = (int)$novoId;

    // ===== Grava profissionais + flags APENAS no tb_capeante =====
    if ($id_capeante) {
        $candidates = [
            'fk_medico'     => $aud_med_capeante,
            'fk_enfermeiro' => $aud_enf_capeante,
            'fk_adm'        => $adm_capeante,
            'med_check'     => $med_check,
            'enfer_check'   => $enfer_check,
            'adm_check'     => $adm_check,
        ];
        $aliases = [
            'fk_medico'     => ['fk_medico', 'id_medico', 'medico_id', 'fk_medico_auditor'],
            'fk_enfermeiro' => ['fk_enfermeiro', 'id_enfermeiro', 'enfermeiro_id', 'fk_enf', 'fk_enf_auditor'],
            'fk_adm'        => ['fk_adm', 'id_adm', 'adm_id', 'fk_admin', 'admin_id'],
            'med_check'     => ['med_check', 'check_med', 'med_assinou'],
            'enfer_check'   => ['enfer_check', 'check_enf', 'enf_assinou'],
            'adm_check'     => ['adm_check', 'check_adm', 'adm_assinou'],
        ];
        $filtered = filter_existing_cols($conn, 'tb_capeante', $candidates, $aliases);
        if (!empty($filtered)) {
            $sets = [];
            $vals = [];
            foreach ($filtered as $col => $val) {
                $sets[] = "`{$col}` = ?";
                $vals[] = $val;
            }
            $vals[] = $id_capeante;
            $sql = "UPDATE tb_capeante SET " . implode(', ', $sets) . " WHERE id_capeante = ?";
            $conn->prepare($sql)->execute($vals);
        }
    }
} else {
    $cap = new capeante();
    $cap->id_capeante                 = $id_capeante;
    $cap->fk_int_capeante             = $fk_internacao;
    $cap->data_inicial_capeante       = $data_inicial;
    $cap->data_final_capeante         = $data_final;
    $cap->data_fech_capeante          = $data_fech;
    $cap->data_digit_capeante         = $data_digit;
    $cap->pacote                      = $pacote;
    $cap->parcial_capeante            = $parcial;
    $cap->parcial_num                 = $parcial_num;

    $cap->valor_apresentado_capeante  = $valor_apresentado;
    $cap->valor_final_capeante        = $valor_final;

    // Totais por categoria (compatibilidade)
    $cap->valor_diarias               = $valor_diarias;
    $cap->valor_taxa                  = $valor_taxa;
    $cap->valor_materiais             = $valor_materiais;
    $cap->valor_medicamentos          = $valor_medicamentos;
    $cap->valor_sadt                  = $valor_sadt;
    $cap->valor_honorarios            = $valor_honorarios;
    $cap->valor_opme                  = $valor_opme;
    $cap->valor_oxig                  = $valor_oxig;

    $cap->glosa_diaria                = $glosa_diaria;
    $cap->glosa_taxas                 = $glosa_taxas;
    $cap->glosa_matmed                = $glosa_matmed;
    $cap->glosa_medicamentos          = $glosa_medicamentos;
    $cap->glosa_sadt                  = $glosa_sadt;
    $cap->glosa_honorarios            = $glosa_honorarios;
    $cap->glosa_opme                  = $glosa_opme;
    $cap->glosa_oxig                  = $glosa_oxig;
    $cap->valor_glosa_total           = $valor_glosa_total;

    $cap->desconto_valor_cap          = $desconto_valor_cap;

    $capeanteDao->update($cap);

    // ===== Atualiza profissionais + flags APENAS no tb_capeante =====
    if ($id_capeante) {
        $candidates = [
            'fk_medico'     => $aud_med_capeante,
            'fk_enfermeiro' => $aud_enf_capeante,
            'fk_adm'        => $adm_capeante,
            'med_check'     => $med_check,
            'enfer_check'   => $enfer_check,
            'adm_check'     => $adm_check,
        ];
        $aliases = [
            'fk_medico'     => ['fk_medico', 'id_medico', 'medico_id', 'fk_medico_auditor'],
            'fk_enfermeiro' => ['fk_enfermeiro', 'id_enfermeiro', 'enfermeiro_id', 'fk_enf', 'fk_enf_auditor'],
            'fk_adm'        => ['fk_adm', 'id_adm', 'adm_id', 'fk_admin', 'admin_id'],
            'med_check'     => ['med_check', 'check_med', 'med_assinou'],
            'enfer_check'   => ['enfer_check', 'check_enf', 'enf_assinou'],
            'adm_check'     => ['adm_check', 'check_adm', 'adm_assinou'],
        ];
        $filtered = filter_existing_cols($conn, 'tb_capeante', $candidates, $aliases);
        if (!empty($filtered)) {
            $sets = [];
            $vals = [];
            foreach ($filtered as $col => $val) {
                $sets[] = "`{$col}` = ?";
                $vals[] = $val;
            }
            $vals[] = $id_capeante;
            $sql = "UPDATE tb_capeante SET " . implode(', ', $sets) . " WHERE id_capeante = ?";
            $conn->prepare($sql)->execute($vals);
        }
    }
}

error_log("[RAH] Capeante ID {$id_capeante} | Cobrado={$total_cobrado} | Glosado={$total_glosado} | Liberado={$total_liberado}");

/* ============================================================
 * Persistência dos grupos AP / UTI / CC / DIÁRIAS / OUTROS
 * - Somente valores (sem FKs de profissionais)
 * ============================================================ */

/**
 * Upsert genérico para tabela de grupo (AP/UTI/CC/DIAR/OUT).
 * $extraCols: use apenas o que for imprescindível (ex.: fk_int_capeante em OUTROS).
 */
function rah_upsert_grupo(PDO $conn, string $tabela, int $fk_capeante, array $mapCatToLegacyCols, array $calcPorCat, array $extraCols = [])
{
    // Verifica se já existe registro para fk_capeante
    $stmt = $conn->prepare("SELECT 1 FROM {$tabela} WHERE fk_capeante = ? LIMIT 1");
    $stmt->execute([$fk_capeante]);
    $exists = (bool)$stmt->fetchColumn();

    if ($exists) {
        $sets = [];
        $valsUpd = [];

        foreach ($extraCols as $col => $val) {
            if ($col === 'fk_capeante') continue;
            $sets[]    = "{$col} = ?";
            $valsUpd[] = $val;
        }

        foreach ($mapCatToLegacyCols as $cat => $legacyPrefix) {
            $row = $calcPorCat[$cat] ?? ['qtd' => 0, 'cobrado' => 0, 'glosado' => 0, 'obs' => null];

            $qtd     = (int)($row['qtd'] ?? 0);
            $cobrado = to_varchar20($row['cobrado'] ?? 0);
            $glosado = to_varchar20($row['glosado'] ?? 0);
            $obs     = $row['obs'] ?? null;

            $sets[]    = "{$legacyPrefix}_qtd = ?";
            $sets[]    = "{$legacyPrefix}_cobrado = ?";
            $sets[]    = "{$legacyPrefix}_glosado = ?";
            $sets[]    = "{$legacyPrefix}_obs = ?";

            $valsUpd[] = (string)$qtd;
            $valsUpd[] = $cobrado;
            $valsUpd[] = $glosado;
            $valsUpd[] = $obs;
        }
        $valsUpd[] = $fk_capeante;
        $sql = "UPDATE {$tabela} SET " . implode(', ', $sets) . " WHERE fk_capeante = ?";
        $conn->prepare($sql)->execute($valsUpd);
    } else {
        $cols = ['fk_capeante'];
        $vals = [$fk_capeante];

        foreach ($extraCols as $col => $val) {
            if ($col === 'fk_capeante') continue;
            $cols[] = $col;
            $vals[] = $val;
        }

        foreach ($mapCatToLegacyCols as $cat => $legacyPrefix) {
            $row = $calcPorCat[$cat] ?? ['qtd' => 0, 'cobrado' => 0, 'glosado' => 0, 'obs' => null];

            $qtd     = (int)($row['qtd'] ?? 0);
            $cobrado = to_varchar20($row['cobrado'] ?? 0);
            $glosado = to_varchar20($row['glosado'] ?? 0);
            $obs     = $row['obs'] ?? null;

            $cols[] = "{$legacyPrefix}_qtd";
            $vals[] = (string)$qtd;
            $cols[] = "{$legacyPrefix}_cobrado";
            $vals[] = $cobrado;
            $cols[] = "{$legacyPrefix}_glosado";
            $vals[] = $glosado;
            $cols[] = "{$legacyPrefix}_obs";
            $vals[] = $obs;
        }
        $ph = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO {$tabela} (" . implode(',', $cols) . ") VALUES ({$ph})";
        $conn->prepare($sql)->execute($vals);
    }
}

/* Mapeamentos: categoria → prefixo legado por tabela */
$map_ap = [
    'terapias' => 'ap_terapias',
    'taxas' => 'ap_taxas',
    'mat_consumo' => 'ap_mat_consumo',
    'medicamentos' => 'ap_medicametos',
    'gases' => 'ap_gases',
    'mat_especial' => 'ap_mat_espec',
    'exames' => 'ap_exames',
    'hemoderivados' => 'ap_hemoderivados',
    'honorarios' => 'ap_honorarios',
];
$map_uti = [
    'terapias' => 'uti_terapias',
    'taxas' => 'uti_taxas',
    'mat_consumo' => 'uti_mat_consumo',
    'medicamentos' => 'uti_medicametos',
    'gases' => 'uti_gases',
    'mat_especial' => 'uti_mat_espec',
    'exames' => 'uti_exames',
    'hemoderivados' => 'uti_hemoderivados',
    'honorarios' => 'uti_honorarios',
];
$map_cc = [
    'terapias' => 'cc_terapias',
    'taxas' => 'cc_taxas',
    'mat_consumo' => 'cc_mat_consumo',
    'medicamentos' => 'cc_medicametos',
    'gases' => 'cc_gases',
    'mat_especial' => 'cc_mat_espec',
    'exames' => 'cc_exames',
    'hemoderivados' => 'cc_hemoderivados',
    'honorarios' => 'cc_honorarios',
];
$map_diar = [
    'ac_quarto' => 'ac_quarto',
    'ac_dayclinic' => 'ac_dayclinic',
    'ac_uti' => 'ac_uti',
    'ac_utisemi' => 'ac_utisemi',
    'ac_enfermaria' => 'ac_enfermaria',
    'ac_bercario' => 'ac_bercario',
    'ac_acompanhante' => 'ac_acompanhante',
    'ac_isolamento' => 'ac_isolamento',
];
$map_out = ['pacote' => 'outros_pacote', 'remocao' => 'outros_remocao'];

/* Salva grupos (somente se temos id_capeante válido) */
if ($id_capeante) {

    // Garante fk_internacao válido para OUTROS
    if (!$fk_internacao) {
        $fk_internacao = (int)$conn
            ->query("SELECT fk_int_capeante FROM tb_capeante WHERE id_capeante = " . (int)$id_capeante)
            ->fetchColumn();
    }

    // >>> Sem FKs de profissionais nas acessórias <<<
    rah_upsert_grupo($conn, 'tb_cap_valores_ap',   (int)$id_capeante, $map_ap,   $ap_calc);
    rah_upsert_grupo($conn, 'tb_cap_valores_uti',  (int)$id_capeante, $map_uti,  $uti_calc);
    rah_upsert_grupo($conn, 'tb_cap_valores_cc',   (int)$id_capeante, $map_cc,   $cc_calc);
    rah_upsert_grupo($conn, 'tb_cap_valores_diar', (int)$id_capeante, $map_diar, $diarias_rows);

    // OUTROS: inclui apenas o fk_int_capeante por integridade
    rah_upsert_grupo(
        $conn,
        'tb_cap_valores_out',
        (int)$id_capeante,
        $map_out,
        $outros_calc,
        ['fk_int_capeante' => (int)$fk_internacao]
    );
}

/* Redireciona de volta */
header("Location: list_internacao_cap_rah.php");
exit;