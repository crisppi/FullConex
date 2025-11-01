<?php
/* ============================================================
 * process_rah.php
 * Processa o formulário "Capeante RAH" (layout TUSS em blocos)
 * - Atualiza identificação/períodos na tb_capeante
 * - Persiste grupos em tb_cap_valores_ap / _uti / _cc
 *   usando fk_capeante e os nomes legados (ap_/uti_/cc_)
 * ============================================================ */

require_once("globals.php");
require_once("db.php");

require_once("models/capeante.php");
require_once("dao/capeanteDao.php");

require_once("models/message.php");
require_once("dao/usuarioDao.php");

$message     = new Message($BASE_URL);
$capeanteDao = new capeanteDAO($conn, $BASE_URL);

$type = filter_input(INPUT_POST, "type") ?: 'update';

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
    // 2 casas, ponto como decimal (mais seguro para cast numérico no MySQL)
    return number_format($num, 2, '.', '');
}

/* ---------- Captura chaves/identificação ---------- */
$id_capeante    = intPOST("id_capeante");                 // capeante.id_capeante (update)
$fk_internacao  = intPOST("fk_int_capeante");             // capeante.fk_int_capeante

$pacote         = strPOST("pacote") ?: 'n';
$parcial        = strPOST("parcial_capeante") ?: 'n';
$parcial_num    = intPOST("parcial_num");

$data_inicial   = datePOST("data_inicial_capeante");
$data_final     = datePOST("data_final_capeante");
$data_fech      = datePOST("data_fech_capeante");
$data_digit     = datePOST("data_digit_capeante");

/* ---------- Funções de consolidação por linha ---------- */
function capturarLinha($prefix)
{
    // Espera *_qtd (int), *_cobrado (money), *_glosado (money), *_obs (string)
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
 * DIÁRIAS
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
foreach ($diarias_ids as $idp) {
    $diarias_rows[$idp] = capturarLinha($idp);
}
$diarias_cob = somaCampo($diarias_rows, 'cobrado');
$diarias_glo = somaCampo($diarias_rows, 'glosado');
$diarias_lib = somaCampo($diarias_rows, 'lib');

/* ============================================================
 * APTO / ENFERMARIA (mapeia categorias → prefixos do FORM)
 * ============================================================ */
$ap_form = [
    'terapias'       => 'ap_terapias',
    'taxas'          => 'ap_taxas',
    'mat_consumo'    => 'ap_mat_consumo',
    'medicamentos'   => 'ap_medicametos',   // coluna legada com "medicamEtos"
    'gases'          => 'ap_gases',
    'mat_especial'   => 'ap_mat_espec',
    'exames'         => 'ap_exames',
    'hemoderivados'  => 'ap_hemoderivados',
    'honorarios'     => 'ap_honorarios',
];
$ap_calc = [];
foreach ($ap_form as $cat => $pfx) $ap_calc[$cat] = capturarLinha($pfx);

/* ============================================================
 * UTI
 * ============================================================ */
$uti_form = [
    'terapias'       => 'uti_terapias',
    'taxas'          => 'uti_taxas',
    'mat_consumo'    => 'uti_mat_consumo',
    'medicamentos'   => 'uti_medicametos',
    'gases'          => 'uti_gases',
    'mat_especial'   => 'uti_mat_espec',
    'exames'         => 'uti_exames',
    'hemoderivados'  => 'uti_hemoderivados',
    'honorarios'     => 'uti_honorarios',
];
$uti_calc = [];
foreach ($uti_form as $cat => $pfx) $uti_calc[$cat] = capturarLinha($pfx);

/* ============================================================
 * CENTRO CIRÚRGICO
 * ============================================================ */
$cc_form = [
    'terapias'       => 'cc_terapias',
    'taxas'          => 'cc_taxas',
    'mat_consumo'    => 'cc_mat_consumo',
    'medicamentos'   => 'cc_medicametos',
    'gases'          => 'cc_gases',
    'mat_especial'   => 'cc_mat_espec',
    'exames'         => 'cc_exames',
    'hemoderivados'  => 'cc_hemoderivados',
    'honorarios'     => 'cc_honorarios',
];
$cc_calc = [];
foreach ($cc_form as $cat => $pfx) $cc_calc[$cat] = capturarLinha($pfx);

/* ---------- Totais por setor ---------- */
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
list($ap_cob, $ap_glo, $ap_lib)     = somarSetor($ap_calc);
list($uti_cob, $uti_glo, $uti_lib)  = somarSetor($uti_calc);
list($cc_cob, $cc_glo, $cc_lib)     = somarSetor($cc_calc);

/* ---------- Totais globais ---------- */
$total_cobrado  = (float)$diarias_cob + $ap_cob + $uti_cob + $cc_cob;
$total_glosado  = (float)$diarias_glo + $ap_glo + $uti_glo + $cc_glo;
$total_liberado = (float)$diarias_lib + $ap_lib + $uti_lib + $cc_lib;

/* ---------- Totais por categoria (soma dos 3 setores) ---------- */
$cat_sum = function ($key) use ($ap_calc, $uti_calc, $cc_calc) {
    return (float)($ap_calc[$key]['lib'] + $uti_calc[$key]['lib'] + $cc_calc[$key]['lib']);
};
/* glosas por categoria */
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

    // Totais por categoria permanecem (compatibilidade)
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
    // recupera o ID criado para salvar grupos
    $id_capeante = $cap->id_capeante ?? null;
    if (!$id_capeante) {
        // fallback: buscar último inserido dessa internação
        $id_capeante = $conn->lastInsertId();
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

    // Totais por categoria (mantidos para compatibilidade)
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
}

error_log("[RAH] Capeante ID {$id_capeante} | Cobrado={$total_cobrado} | Glosado={$total_glosado} | Liberado={$total_liberado}");

/* ============================================================
 * Persistência dos grupos AP / UTI / CC
 * - Usa fk_capeante
 * - Nomes legados (ap_/uti_/cc_), varchar(20) para valores
 * - Upsert manual (existe? UPDATE : INSERT)
 * ============================================================ */

/* Upsert genérico para tabela de grupo (AP/UTI/CC) */
function rah_upsert_grupo(PDO $conn, string $tabela, int $fk_capeante, array $mapCatToLegacyCols, array $calcPorCat)
{
    // Verifica se já existe registro para fk_capeante
    $exists = false;
    $stmt = $conn->prepare("SELECT 1 FROM {$tabela} WHERE fk_capeante = ? LIMIT 1");
    $stmt->execute([$fk_capeante]);
    $exists = (bool)$stmt->fetchColumn();

    // Monta SET clause e bind
    $cols = ['fk_capeante'];
    $vals = [$fk_capeante];

    foreach ($mapCatToLegacyCols as $cat => $legacyPrefix) {
        $row = $calcPorCat[$cat] ?? ['qtd' => 0, 'cobrado' => 0, 'glosado' => 0, 'lib' => 0, 'obs' => null];

        $qtd     = (int)($row['qtd'] ?? 0);
        $cobrado = to_varchar20($row['cobrado'] ?? 0);
        $glosado = to_varchar20($row['glosado'] ?? 0);
        $obs     = strPOST($legacyPrefix . "_obs"); // pega OBS direto do POST pelo mesmo prefixo (já capturado antes, mas garantimos)

        // Nomes legados:
        $colQtd  = "{$legacyPrefix}_qtd";
        $colCob  = "{$legacyPrefix}_cobrado";
        $colGlo  = "{$legacyPrefix}_glosado";
        $colObs  = "{$legacyPrefix}_obs";

        if ($exists) {
            // UPDATE: montamos dinamicamente
            $sets[] = "{$colQtd} = ?";
            $sets[] = "{$colCob} = ?";
            $sets[] = "{$colGlo} = ?";
            $sets[] = "{$colObs} = ?";
            $valsUpd[] = (string)$qtd;
            $valsUpd[] = $cobrado;
            $valsUpd[] = $glosado;
            $valsUpd[] = $obs;
        } else {
            // INSERT: acumulamos colunas e valores
            $cols[] = $colQtd;
            $vals[] = (string)$qtd;
            $cols[] = $colCob;
            $vals[] = $cobrado;
            $cols[] = $colGlo;
            $vals[] = $glosado;
            $cols[] = $colObs;
            $vals[] = $obs;
        }
    }

    if ($exists) {
        $sql = "UPDATE {$tabela} SET " . implode(', ', $sets) . " WHERE fk_capeante = ?";
        $valsUpd[] = $fk_capeante;
        $st = $conn->prepare($sql);
        $st->execute($valsUpd);
    } else {
        // Garante colunas em ordem
        $ph = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO {$tabela} (" . implode(',', $cols) . ") VALUES ({$ph})";
        $st = $conn->prepare($sql);
        $st->execute($vals);
    }
}

/* Mapeamentos: categoria → prefixo legado por tabela */
$map_ap = [
    'terapias'      => 'ap_terapias',
    'taxas'         => 'ap_taxas',
    'mat_consumo'   => 'ap_mat_consumo',
    'medicamentos'  => 'ap_medicametos',   // legado
    'gases'         => 'ap_gases',
    'mat_especial'  => 'ap_mat_espec',
    'exames'        => 'ap_exames',
    'hemoderivados' => 'ap_hemoderivados',
    'honorarios'    => 'ap_honorarios',
];
$map_uti = [
    'terapias'      => 'uti_terapias',
    'taxas'         => 'uti_taxas',
    'mat_consumo'   => 'uti_mat_consumo',
    'medicamentos'  => 'uti_medicametos',
    'gases'         => 'uti_gases',
    'mat_especial'  => 'uti_mat_espec',
    'exames'        => 'uti_exames',
    'hemoderivados' => 'uti_hemoderivados',
    'honorarios'    => 'uti_honorarios',
];
$map_cc = [
    'terapias'      => 'cc_terapias',
    'taxas'         => 'cc_taxas',
    'mat_consumo'   => 'cc_mat_consumo',
    'medicamentos'  => 'cc_medicametos',
    'gases'         => 'cc_gases',
    'mat_especial'  => 'cc_mat_espec',
    'exames'        => 'cc_exames',
    'hemoderivados' => 'cc_hemoderivados',
    'honorarios'    => 'cc_honorarios',
];

/* Salva grupos */
if ($id_capeante) {
    rah_upsert_grupo($conn, 'tb_cap_valores_ap',  (int)$id_capeante, $map_ap,  $ap_calc);
    rah_upsert_grupo($conn, 'tb_cap_valores_uti', (int)$id_capeante, $map_uti, $uti_calc);
    rah_upsert_grupo($conn, 'tb_cap_valores_cc',  (int)$id_capeante, $map_cc,  $cc_calc);
}

/* Redireciona de volta */
header("Location: list_internacao_cap_rah.php");
exit;