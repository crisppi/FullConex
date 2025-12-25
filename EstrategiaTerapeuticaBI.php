<?php
include_once("check_logado.php");
require_once("templates/header.php");

if (!isset($conn) || !($conn instanceof PDO)) {
    die("Conexao invalida.");
}

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function fmt_num($value, int $decimals = 2): string
{
    return number_format((float)$value, $decimals, ',', '.');
}

function fmt_money($value): string
{
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

$anoInput = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT);
$mesInput = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT);
$ano = ($anoInput !== null && $anoInput !== false) ? (int)$anoInput : null;
$mes = ($mesInput !== null && $mesInput !== false) ? (int)$mesInput : null;
if ($ano === null && !filter_has_var(INPUT_GET, 'ano')) {
    $ano = (int)date('Y');
}
$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;
$tipoInternacao = trim((string)(filter_input(INPUT_GET, 'tipo_internacao') ?? ''));
$modoInternacao = trim((string)(filter_input(INPUT_GET, 'modo_internacao') ?? ''));
$patologiaId = filter_input(INPUT_GET, 'patologia_id', FILTER_VALIDATE_INT) ?: null;
$grupoPatologia = trim((string)(filter_input(INPUT_GET, 'grupo_patologia') ?? ''));
$uti = trim((string)(filter_input(INPUT_GET, 'uti') ?? ''));
$antecedenteId = filter_input(INPUT_GET, 'antecedente_id', FILTER_VALIDATE_INT) ?: null;
$sexo = trim((string)(filter_input(INPUT_GET, 'sexo') ?? ''));
$faixaEtaria = trim((string)(filter_input(INPUT_GET, 'faixa_etaria') ?? ''));

$hospitais = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp")
    ->fetchAll(PDO::FETCH_ASSOC);
$tiposInt = $conn->query("SELECT DISTINCT tipo_admissao_int FROM tb_internacao WHERE tipo_admissao_int IS NOT NULL AND tipo_admissao_int <> '' ORDER BY tipo_admissao_int")
    ->fetchAll(PDO::FETCH_COLUMN);
$modos = $conn->query("SELECT DISTINCT modo_internacao_int FROM tb_internacao WHERE modo_internacao_int IS NOT NULL AND modo_internacao_int <> '' ORDER BY modo_internacao_int")
    ->fetchAll(PDO::FETCH_COLUMN);
$patologias = $conn->query("SELECT id_patologia, patologia_pat FROM tb_patologia ORDER BY patologia_pat")
    ->fetchAll(PDO::FETCH_ASSOC);
$grupos = $conn->query("SELECT DISTINCT grupo_patologia_int FROM tb_internacao WHERE grupo_patologia_int IS NOT NULL AND grupo_patologia_int <> '' ORDER BY grupo_patologia_int")
    ->fetchAll(PDO::FETCH_COLUMN);
$antecedentes = $conn->query("SELECT id_antecedente, antecedente_ant FROM tb_antecedente WHERE antecedente_ant IS NOT NULL AND antecedente_ant <> '' ORDER BY antecedente_ant")
    ->fetchAll(PDO::FETCH_ASSOC);
$anos = $conn->query("SELECT DISTINCT YEAR(data_intern_int) AS ano FROM tb_internacao WHERE data_intern_int IS NOT NULL AND data_intern_int <> '0000-00-00' ORDER BY ano DESC")
    ->fetchAll(PDO::FETCH_COLUMN);

$faixasEtarias = [
    '0-19' => '0-19',
    '20-39' => '20-39',
    '40-59' => '40-59',
    '60-79' => '60-79',
    '80+' => '80+',
    'Sem informacao' => 'Sem informacao',
];

function idade_cond(string $faixa, string $alias = 'pa'): ?string
{
    switch ($faixa) {
        case '0-19':
            return "{$alias}.idade_pac < 20";
        case '20-39':
            return "{$alias}.idade_pac >= 20 AND {$alias}.idade_pac < 40";
        case '40-59':
            return "{$alias}.idade_pac >= 40 AND {$alias}.idade_pac < 60";
        case '60-79':
            return "{$alias}.idade_pac >= 60 AND {$alias}.idade_pac < 80";
        case '80+':
            return "{$alias}.idade_pac >= 80";
        case 'Sem informacao':
            return "{$alias}.idade_pac IS NULL";
        default:
            return null;
    }
}

function build_where_internacao(array $filters, array &$params, bool $applyUti): array
{
    $where = "1=1";
    $params = [];
    if (!empty($filters['ano'])) {
        $where .= " AND YEAR(i.data_intern_int) = :ano";
        $params[':ano'] = (int)$filters['ano'];
    }
    if (!empty($filters['mes'])) {
        $where .= " AND MONTH(i.data_intern_int) = :mes";
        $params[':mes'] = (int)$filters['mes'];
    }
    if (!empty($filters['hospital_id'])) {
        $where .= " AND i.fk_hospital_int = :hospital_id";
        $params[':hospital_id'] = (int)$filters['hospital_id'];
    }
    if (!empty($filters['tipo_internacao'])) {
        $where .= " AND i.tipo_admissao_int = :tipo_internacao";
        $params[':tipo_internacao'] = $filters['tipo_internacao'];
    }
    if (!empty($filters['modo_internacao'])) {
        $where .= " AND i.modo_internacao_int = :modo_internacao";
        $params[':modo_internacao'] = $filters['modo_internacao'];
    }
    if (!empty($filters['patologia_id'])) {
        $where .= " AND i.fk_patologia_int = :patologia_id";
        $params[':patologia_id'] = (int)$filters['patologia_id'];
    }
    if (!empty($filters['grupo_patologia'])) {
        $where .= " AND i.grupo_patologia_int = :grupo_patologia";
        $params[':grupo_patologia'] = $filters['grupo_patologia'];
    }
    if (!empty($filters['antecedente_id'])) {
        $where .= " AND i.fk_patologia2 = :antecedente_id";
        $params[':antecedente_id'] = (int)$filters['antecedente_id'];
    }
    if (!empty($filters['sexo'])) {
        $where .= " AND pa.sexo_pac = :sexo";
        $params[':sexo'] = $filters['sexo'];
    }
    if (!empty($filters['faixa_etaria'])) {
        $cond = idade_cond($filters['faixa_etaria'], 'pa');
        if ($cond) {
            $where .= " AND {$cond}";
        }
    }

    $utiJoin = "LEFT JOIN (SELECT DISTINCT fk_internacao_uti FROM tb_uti) ut ON ut.fk_internacao_uti = i.id_internacao";
    if ($applyUti) {
        if ($filters['uti'] === 's') {
            $where .= " AND ut.fk_internacao_uti IS NOT NULL";
        } elseif ($filters['uti'] === 'n') {
            $where .= " AND ut.fk_internacao_uti IS NULL";
        }
    }

    return [$where, $utiJoin];
}

function build_where_financeiro(array $filters, array &$params, bool $applyUti): string
{
    $where = "ref_date IS NOT NULL AND ref_date <> '0000-00-00'";
    $params = [];
    if (!empty($filters['ano'])) {
        $where .= " AND YEAR(ref_date) = :ano";
        $params[':ano'] = (int)$filters['ano'];
    }
    if (!empty($filters['mes'])) {
        $where .= " AND MONTH(ref_date) = :mes";
        $params[':mes'] = (int)$filters['mes'];
    }
    if (!empty($filters['hospital_id'])) {
        $where .= " AND fk_hospital_int = :hospital_id";
        $params[':hospital_id'] = (int)$filters['hospital_id'];
    }
    if (!empty($filters['tipo_internacao'])) {
        $where .= " AND tipo_admissao_int = :tipo_internacao";
        $params[':tipo_internacao'] = $filters['tipo_internacao'];
    }
    if (!empty($filters['modo_internacao'])) {
        $where .= " AND modo_internacao_int = :modo_internacao";
        $params[':modo_internacao'] = $filters['modo_internacao'];
    }
    if (!empty($filters['patologia_id'])) {
        $where .= " AND fk_patologia_int = :patologia_id";
        $params[':patologia_id'] = (int)$filters['patologia_id'];
    }
    if (!empty($filters['grupo_patologia'])) {
        $where .= " AND grupo_patologia_int = :grupo_patologia";
        $params[':grupo_patologia'] = $filters['grupo_patologia'];
    }
    if (!empty($filters['antecedente_id'])) {
        $where .= " AND fk_patologia2 = :antecedente_id";
        $params[':antecedente_id'] = (int)$filters['antecedente_id'];
    }
    if (!empty($filters['sexo'])) {
        $where .= " AND sexo_pac = :sexo";
        $params[':sexo'] = $filters['sexo'];
    }
    if (!empty($filters['faixa_etaria'])) {
        $cond = idade_cond($filters['faixa_etaria'], 't');
        if ($cond) {
            $where .= " AND {$cond}";
        }
    }
    if ($applyUti) {
        if ($filters['uti'] === 's') {
            $where .= " AND ut.fk_internacao_uti IS NOT NULL";
        } elseif ($filters['uti'] === 'n') {
            $where .= " AND ut.fk_internacao_uti IS NULL";
        }
    }

    return $where;
}

function internacao_stats(PDO $conn, array $filters): array
{
    $params = [];
    [$where, $utiJoin] = build_where_internacao($filters, $params, true);

    $sql = "
        SELECT
            COUNT(DISTINCT i.id_internacao) AS total_internacoes,
            SUM(GREATEST(1, DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1)) AS total_diarias
        FROM tb_internacao i
        LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
        LEFT JOIN (
            SELECT fk_id_int_alt, MAX(data_alta_alt) AS data_alta_alt
            FROM tb_alta
            GROUP BY fk_id_int_alt
        ) al ON al.fk_id_int_alt = i.id_internacao
        {$utiJoin}
        WHERE {$where}
    ";
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $totalInternacoes = (int)($row['total_internacoes'] ?? 0);
    $totalDiarias = (int)($row['total_diarias'] ?? 0);
    $mp = $totalInternacoes > 0 ? ($totalDiarias / $totalInternacoes) : 0.0;

    return [
        'total_internacoes' => $totalInternacoes,
        'total_diarias' => $totalDiarias,
        'mp' => $mp,
    ];
}

function uti_stats(PDO $conn, array $filters): array
{
    if (($filters['uti'] ?? '') === 'n') {
        return ['total_internacoes' => 0, 'total_diarias' => 0, 'mp' => 0.0];
    }
    $filters['uti'] = 's';

    $params = [];
    [$where] = build_where_internacao($filters, $params, false);

    $sql = "
        SELECT
            COUNT(*) AS total_internacoes_uti,
            SUM(GREATEST(1, DATEDIFF(COALESCE(u.max_data_alta, CURDATE()), u.min_data_internacao) + 1)) AS total_diarias_uti
        FROM (
            SELECT
                u.fk_internacao_uti,
                MIN(NULLIF(u.data_internacao_uti, '0000-00-00')) AS min_data_internacao,
                MAX(NULLIF(u.data_alta_uti, '0000-00-00')) AS max_data_alta
            FROM tb_uti u
            WHERE u.data_internacao_uti IS NOT NULL AND u.data_internacao_uti <> '0000-00-00'
            GROUP BY u.fk_internacao_uti
        ) u
        INNER JOIN tb_internacao i ON i.id_internacao = u.fk_internacao_uti
        LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
        WHERE {$where}
    ";
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $totalInternacoes = (int)($row['total_internacoes_uti'] ?? 0);
    $totalDiarias = (int)($row['total_diarias_uti'] ?? 0);
    $mp = $totalInternacoes > 0 ? ($totalDiarias / $totalInternacoes) : 0.0;

    return [
        'total_internacoes' => $totalInternacoes,
        'total_diarias' => $totalDiarias,
        'mp' => $mp,
    ];
}

function financeiro_stats(PDO $conn, array $filters): array
{
    $dateExpr = "COALESCE(NULLIF(ca.data_inicial_capeante,'0000-00-00'), NULLIF(ca.data_digit_capeante,'0000-00-00'), NULLIF(ca.data_fech_capeante,'0000-00-00'))";
    $params = [];
    $where = build_where_financeiro($filters, $params, true);

    $sql = "
        SELECT
            SUM(COALESCE(t.valor_apresentado_capeante,0)) AS valor_apresentado,
            COUNT(DISTINCT t.id_capeante) AS total_contas
        FROM (
            SELECT
                ca.id_capeante,
                ca.fk_int_capeante,
                ca.valor_apresentado_capeante,
                {$dateExpr} AS ref_date,
                ac.fk_hospital_int,
                ac.tipo_admissao_int,
                ac.modo_internacao_int,
                ac.fk_patologia_int,
                ac.grupo_patologia_int,
                ac.fk_patologia2,
                pa.sexo_pac,
                pa.idade_pac
            FROM tb_capeante ca
            INNER JOIN tb_internacao ac ON ac.id_internacao = ca.fk_int_capeante
            LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
        ) t
        LEFT JOIN (SELECT DISTINCT fk_internacao_uti FROM tb_uti) ut ON ut.fk_internacao_uti = t.fk_int_capeante
        WHERE {$where}
    ";
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'valor_apresentado' => (float)($row['valor_apresentado'] ?? 0),
        'total_contas' => (int)($row['total_contas'] ?? 0),
    ];
}

$filtersSelected = [
    'ano' => $ano,
    'mes' => $mes,
    'hospital_id' => $hospitalId,
    'tipo_internacao' => $tipoInternacao,
    'modo_internacao' => $modoInternacao,
    'patologia_id' => $patologiaId,
    'grupo_patologia' => $grupoPatologia,
    'uti' => $uti,
    'antecedente_id' => $antecedenteId,
    'sexo' => $sexo,
    'faixa_etaria' => $faixaEtaria,
];

$filtersGlobal = [
    'ano' => $ano,
    'mes' => $mes,
    'hospital_id' => null,
    'tipo_internacao' => '',
    'modo_internacao' => '',
    'patologia_id' => null,
    'grupo_patologia' => '',
    'uti' => '',
    'antecedente_id' => null,
    'sexo' => '',
    'faixa_etaria' => '',
];

$selInternacao = internacao_stats($conn, $filtersSelected);
$selFinanceiro = financeiro_stats($conn, $filtersSelected);
$selUti = uti_stats($conn, $filtersSelected);
$selUtiFinanceiro = ($uti === 'n') ? ['valor_apresentado' => 0.0, 'total_contas' => 0] : financeiro_stats($conn, array_merge($filtersSelected, ['uti' => 's']));

$globInternacao = internacao_stats($conn, $filtersGlobal);
$globFinanceiro = financeiro_stats($conn, $filtersGlobal);
$globUti = uti_stats($conn, $filtersGlobal);
$globUtiFinanceiro = financeiro_stats($conn, array_merge($filtersGlobal, ['uti' => 's']));

$selCustoMedioDiaria = $selInternacao['total_diarias'] > 0 ? ($selFinanceiro['valor_apresentado'] / $selInternacao['total_diarias']) : 0.0;
$selCustoMedioDiariaUti = $selUti['total_diarias'] > 0 ? ($selUtiFinanceiro['valor_apresentado'] / $selUti['total_diarias']) : 0.0;
$selCustoMedioConta = $selFinanceiro['total_contas'] > 0 ? ($selFinanceiro['valor_apresentado'] / $selFinanceiro['total_contas']) : 0.0;

$globCustoMedioDiaria = $globInternacao['total_diarias'] > 0 ? ($globFinanceiro['valor_apresentado'] / $globInternacao['total_diarias']) : 0.0;
$globCustoMedioDiariaUti = $globUti['total_diarias'] > 0 ? ($globUtiFinanceiro['valor_apresentado'] / $globUti['total_diarias']) : 0.0;
$globCustoMedioConta = $globFinanceiro['total_contas'] > 0 ? ($globFinanceiro['valor_apresentado'] / $globFinanceiro['total_contas']) : 0.0;
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20251226">
<script src="<?= $BASE_URL ?>js/bi.js?v=20251221"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>
<style>
.bi-header {
    position: relative;
}
.bi-header-actions.bi-header-floating {
    position: absolute;
    right: 0;
    top: 0;
}
.bi-wrapper .bi-grid-3x3-gap {
    display: none !important;
}
.bi-grid .bi-nav-icon,
.bi-grid .bi-grid-3x3-gap,
.bi-grid i,
.bi-grid svg {
    display: none !important;
}
.bi-nav-icon svg {
    width: 16px;
    height: 16px;
}
.bi-nav-icon svg circle {
    fill: currentColor;
}
</style>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Estrategia Terapeutica</h1>
        <div class="bi-header-actions bi-header-floating">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <svg viewBox="0 0 16 16" aria-hidden="true">
                    <circle cx="3" cy="3" r="1.2"></circle>
                    <circle cx="8" cy="3" r="1.2"></circle>
                    <circle cx="13" cy="3" r="1.2"></circle>
                    <circle cx="3" cy="8" r="1.2"></circle>
                    <circle cx="8" cy="8" r="1.2"></circle>
                    <circle cx="13" cy="8" r="1.2"></circle>
                    <circle cx="3" cy="13" r="1.2"></circle>
                    <circle cx="8" cy="13" r="1.2"></circle>
                    <circle cx="13" cy="13" r="1.2"></circle>
                </svg>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter">
            <label>Hospital</label>
            <select name="hospital_id">
                <option value="">Todos</option>
                <?php foreach ($hospitais as $h): ?>
                    <option value="<?= (int)$h['id_hospital'] ?>" <?= $hospitalId == $h['id_hospital'] ? 'selected' : '' ?>>
                        <?= e($h['nome_hosp']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Internacao</label>
            <select name="tipo_internacao">
                <option value="">Todos</option>
                <?php foreach ($tiposInt as $tipo): ?>
                    <option value="<?= e($tipo) ?>" <?= $tipoInternacao === $tipo ? 'selected' : '' ?>>
                        <?= e($tipo) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Modo internacao</label>
            <select name="modo_internacao">
                <option value="">Todos</option>
                <?php foreach ($modos as $modo): ?>
                    <option value="<?= e($modo) ?>" <?= $modoInternacao === $modo ? 'selected' : '' ?>>
                        <?= e($modo) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Patologia</label>
            <select name="patologia_id">
                <option value="">Todos</option>
                <?php foreach ($patologias as $p): ?>
                    <option value="<?= (int)$p['id_patologia'] ?>" <?= $patologiaId == $p['id_patologia'] ? 'selected' : '' ?>>
                        <?= e($p['patologia_pat']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Grupo patologia</label>
            <select name="grupo_patologia">
                <option value="">Todos</option>
                <?php foreach ($grupos as $g): ?>
                    <option value="<?= e($g) ?>" <?= $grupoPatologia === $g ? 'selected' : '' ?>>
                        <?= e($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Internacao UTI</label>
            <select name="uti">
                <option value="">Todos</option>
                <option value="s" <?= $uti === 's' ? 'selected' : '' ?>>Sim</option>
                <option value="n" <?= $uti === 'n' ? 'selected' : '' ?>>Nao</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Antecedente</label>
            <select name="antecedente_id">
                <option value="">Todos</option>
                <?php foreach ($antecedentes as $a): ?>
                    <option value="<?= (int)$a['id_antecedente'] ?>" <?= $antecedenteId == $a['id_antecedente'] ? 'selected' : '' ?>>
                        <?= e($a['antecedente_ant']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Sexo</label>
            <select name="sexo">
                <option value="">Todos</option>
                <option value="M" <?= $sexo === 'M' ? 'selected' : '' ?>>Masculino</option>
                <option value="F" <?= $sexo === 'F' ? 'selected' : '' ?>>Feminino</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Faixa etaria</label>
            <select name="faixa_etaria">
                <option value="">Todos</option>
                <?php foreach ($faixasEtarias as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $faixaEtaria === $key ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Ano</label>
            <select name="ano">
                <option value="">Todos</option>
                <?php foreach ($anos as $a): ?>
                    <option value="<?= (int)$a ?>" <?= (int)$ano === (int)$a ? 'selected' : '' ?>>
                        <?= (int)$a ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Mes</label>
            <select name="mes">
                <option value="">Todos</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $mes === $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="bi-actions"><button class="bi-btn" type="submit">Aplicar</button></div>
    </form>

    <div class="bi-panel" style="margin-top:16px; text-align:center;">
        <div style="font-weight:600; letter-spacing:0.04em;">
            SELECIONE OS FILTROS PARA DEFINIR QUAL A MELHOR ESTRATEGIA TERAPEUTICA PARA DETERMINADO
            CASO E ONDE PODERA OBTER MELHORES RESULTADOS ASSISTENCIAIS.
        </div>
    </div>

    <div class="bi-grid fixed-2" style="margin-top:16px;">
        <div class="bi-panel">
            <h3 class="text-center">Selecionado</h3>
            <div class="bi-kpis kpi-compact">
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Total internacoes</small><strong><?= fmt_num($selInternacao['total_internacoes']) ?></strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Custo medio diaria</small><strong><?= fmt_money($selCustoMedioDiaria) ?></strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>MP</small><strong><?= fmt_num($selInternacao['mp']) ?></strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Custo medio diaria UTI</small><strong><?= fmt_money($selCustoMedioDiariaUti) ?></strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Internacao UTI</small><strong><?= fmt_num($selUti['total_internacoes']) ?></strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Custo medio por conta</small><strong><?= fmt_money($selCustoMedioConta) ?></strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Media permanencia UTI</small><strong><?= fmt_num($selUti['mp']) ?></strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Valor apresentado</small><strong><?= fmt_money($selFinanceiro['valor_apresentado']) ?></strong></div>
            </div>
        </div>
        <div class="bi-panel">
            <h3 class="text-center">Global</h3>
            <div class="bi-kpis kpi-compact">
                <div class="bi-kpi kpi-rose kpi-compact"><small>Total internacoes</small><strong><?= fmt_num($globInternacao['total_internacoes']) ?></strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Custo medio diaria</small><strong><?= fmt_money($globCustoMedioDiaria) ?></strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>MP</small><strong><?= fmt_num($globInternacao['mp']) ?></strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Custo medio diaria UTI</small><strong><?= fmt_money($globCustoMedioDiariaUti) ?></strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Internacao UTI</small><strong><?= fmt_num($globUti['total_internacoes']) ?></strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Custo medio por conta</small><strong><?= fmt_money($globCustoMedioConta) ?></strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Media permanencia UTI</small><strong><?= fmt_num($globUti['mp']) ?></strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Valor apresentado</small><strong><?= fmt_money($globFinanceiro['valor_apresentado']) ?></strong></div>
            </div>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
