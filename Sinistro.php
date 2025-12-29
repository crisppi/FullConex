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

$hoje = date('Y-m-d');
$dataIni = filter_input(INPUT_GET, 'data_ini') ?: date('Y-m-d', strtotime('-120 days'));
$dataFim = filter_input(INPUT_GET, 'data_fim') ?: $hoje;
$internado = trim((string)(filter_input(INPUT_GET, 'internado') ?? ''));
$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;
$tipoInternação = trim((string)(filter_input(INPUT_GET, 'tipo_internacao') ?? ''));
$modoAdmissão = trim((string)(filter_input(INPUT_GET, 'modo_admissao') ?? ''));
$uti = trim((string)(filter_input(INPUT_GET, 'uti') ?? ''));

$hospitais = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp")
    ->fetchAll(PDO::FETCH_ASSOC);
$tiposInt = $conn->query("SELECT DISTINCT tipo_admissao_int FROM tb_internacao WHERE tipo_admissao_int IS NOT NULL AND tipo_admissao_int <> '' ORDER BY tipo_admissao_int")
    ->fetchAll(PDO::FETCH_COLUMN);
$modosAdm = $conn->query("SELECT DISTINCT modo_internacao_int FROM tb_internacao WHERE modo_internacao_int IS NOT NULL AND modo_internacao_int <> '' ORDER BY modo_internacao_int")
    ->fetchAll(PDO::FETCH_COLUMN);

$where = "i.data_intern_int BETWEEN :data_ini AND :data_fim";
$params = [
    ':data_ini' => $dataIni,
    ':data_fim' => $dataFim,
];
if ($internado !== '') {
    $where .= " AND i.internado_int = :internado";
    $params[':internado'] = $internado;
}
if ($hospitalId) {
    $where .= " AND i.fk_hospital_int = :hospital_id";
    $params[':hospital_id'] = $hospitalId;
}
if ($tipoInternação !== '') {
    $where .= " AND i.tipo_admissao_int = :tipo";
    $params[':tipo'] = $tipoInternação;
}
if ($modoAdmissão !== '') {
    $where .= " AND i.modo_internacao_int = :modo";
    $params[':modo'] = $modoAdmissão;
}

$utiJoin = "LEFT JOIN (SELECT DISTINCT fk_internacao_uti FROM tb_uti) ut ON ut.fk_internacao_uti = i.id_internacao";
if ($uti === 's') {
    $where .= " AND ut.fk_internacao_uti IS NOT NULL";
}
if ($uti === 'n') {
    $where .= " AND ut.fk_internacao_uti IS NULL";
}

$sqlBase = "
    FROM tb_internacao i
    {$utiJoin}
    LEFT JOIN (
        SELECT fk_id_int_alt, MAX(data_alta_alt) AS data_alta_alt
        FROM tb_alta
        GROUP BY fk_id_int_alt
    ) al ON al.fk_id_int_alt = i.id_internacao
    LEFT JOIN tb_patologia p ON p.id_patologia = i.fk_patologia_int
    LEFT JOIN tb_capeante ca ON ca.fk_int_capeante = i.id_internacao
    WHERE {$where}
";

function distQuery(PDO $conn, string $labelExpr, string $sqlBase, array $params, string $metric, int $limit = 12): array
{
    $sql = "
        SELECT {$labelExpr} AS label, {$metric} AS total
        {$sqlBase}
        GROUP BY label
        ORDER BY total DESC
        LIMIT {$limit}
    ";
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

$labelGrupo = "COALESCE(NULLIF(i.grupo_patologia_int,''), p.patologia_pat, 'Sem informacoes')";

$rowsApresentado = distQuery($conn, $labelGrupo, $sqlBase, $params, "SUM(COALESCE(ca.valor_apresentado_capeante,0))", 10);
$rowsGlosa = distQuery($conn, $labelGrupo, $sqlBase, $params, "SUM(COALESCE(ca.valor_glosa_total,0))", 10);
$rowsFinal = distQuery($conn, $labelGrupo, $sqlBase, $params, "SUM(COALESCE(ca.valor_final_capeante,0))", 10);
$rowsIntern = distQuery($conn, $labelGrupo, $sqlBase, $params, "COUNT(DISTINCT i.id_internacao)", 10);

function labelsAndValues(array $rows): array
{
    $labels = array_map(fn($r) => $r['label'] ?? 'Sem informacoes', $rows);
    $values = array_map(fn($r) => (float)($r['total'] ?? 0), $rows);
    return [$labels, $values];
}

[$labelsApresentado, $valuesApresentado] = labelsAndValues($rowsApresentado);
[$labelsGlosa, $valuesGlosa] = labelsAndValues($rowsGlosa);
[$labelsFinal, $valuesFinal] = labelsAndValues($rowsFinal);
[$labelsIntern, $valuesIntern] = labelsAndValues($rowsIntern);
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Dashboard Sinistro</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter">
            <label>Internado</label>
            <select name="internado">
                <option value="">Todos</option>
                <option value="s" <?= $internado === 's' ? 'selected' : '' ?>>Sim</option>
                <option value="n" <?= $internado === 'n' ? 'selected' : '' ?>>Não</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Hospitais</label>
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
            <label>Tipo Internação</label>
            <select name="tipo_internacao">
                <option value="">Todos</option>
                <?php foreach ($tiposInt as $tipo): ?>
                    <option value="<?= e($tipo) ?>" <?= $tipoInternação === $tipo ? 'selected' : '' ?>>
                        <?= e($tipo) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Modo Admissão</label>
            <select name="modo_admissao">
                <option value="">Todos</option>
                <?php foreach ($modosAdm as $modo): ?>
                    <option value="<?= e($modo) ?>" <?= $modoAdmissão === $modo ? 'selected' : '' ?>>
                        <?= e($modo) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>UTI</label>
            <select name="uti">
                <option value="">Todos</option>
                <option value="s" <?= $uti === 's' ? 'selected' : '' ?>>Sim</option>
                <option value="n" <?= $uti === 'n' ? 'selected' : '' ?>>Não</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Data Internação</label>
            <input type="date" name="data_ini" value="<?= e($dataIni) ?>">
        </div>
        <div class="bi-filter">
            <label>Data Final</label>
            <input type="date" name="data_fim" value="<?= e($dataFim) ?>">
        </div>
        <div class="bi-actions">
            <button class="bi-btn" type="submit">Aplicar</button>
        </div>
    </form>

    <div class="bi-grid fixed-2" style="margin-top:16px;">
        <div class="bi-panel">
            <h3>Valor apresentado</h3>
            <div class="bi-chart"><canvas id="chartApresentado"></canvas></div>
        </div>
        <div class="bi-panel">
            <h3>Glosa total</h3>
            <div class="bi-chart"><canvas id="chartGlosa"></canvas></div>
        </div>
    </div>

    <div class="bi-grid fixed-2" style="margin-top:16px;">
        <div class="bi-panel">
            <h3>Valor final</h3>
            <div class="bi-chart"><canvas id="chartFinal"></canvas></div>
        </div>
        <div class="bi-panel">
            <h3>Internações</h3>
            <div class="bi-chart"><canvas id="chartIntern"></canvas></div>
        </div>
    </div>
</div>

<script>
const labelsApresentado = <?= json_encode($labelsApresentado) ?>;
const valuesApresentado = <?= json_encode($valuesApresentado) ?>;
const labelsGlosa = <?= json_encode($labelsGlosa) ?>;
const valuesGlosa = <?= json_encode($valuesGlosa) ?>;
const labelsFinal = <?= json_encode($labelsFinal) ?>;
const valuesFinal = <?= json_encode($valuesFinal) ?>;
const labelsIntern = <?= json_encode($labelsIntern) ?>;
const valuesIntern = <?= json_encode($valuesIntern) ?>;

function barChart(ctx, labels, data, color, yTickCallback, yLabel) {
    const scales = window.biChartScales ? window.biChartScales() : {};
    if (!scales.xAxes) {
        scales.xAxes = [{ ticks: { fontColor: '#e8f1ff' }, gridLines: { display: false } }];
    }
    if (!scales.yAxes) {
        scales.yAxes = [{
            ticks: { fontColor: '#e8f1ff' },
            gridLines: { color: 'rgba(255,255,255,0.1)' }
        }];
    }
    if (scales.yAxes[0]) {
        scales.yAxes[0].ticks = scales.yAxes[0].ticks || {};
        scales.yAxes[0].ticks.fontColor = '#e8f1ff';
        if (yTickCallback) {
            scales.yAxes[0].ticks.callback = yTickCallback;
        }
        if (yLabel) {
            scales.yAxes[0].scaleLabel = {
                display: true,
                labelString: yLabel,
                fontColor: '#e8f1ff'
            };
        }
    }
    scales.x = scales.x || { ticks: { color: '#e8f1ff' }, grid: { display: false } };
    scales.y = scales.y || { ticks: { color: '#e8f1ff' }, grid: { color: 'rgba(255,255,255,0.1)' } };
    if (scales.y.ticks) {
        scales.y.ticks.color = '#e8f1ff';
        if (yTickCallback) {
            scales.y.ticks.callback = yTickCallback;
        }
    }
    if (yLabel) {
        scales.y.title = { display: true, text: yLabel, color: '#e8f1ff' };
    }
    return new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ data, backgroundColor: color }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            plugins: { legend: { display: false } },
            scales
        }
    });
}

barChart(document.getElementById('chartApresentado'), labelsApresentado, valuesApresentado, 'rgba(141, 208, 255, 0.7)', window.biMoneyTick, 'Valor (R$)');
barChart(document.getElementById('chartGlosa'), labelsGlosa, valuesGlosa, 'rgba(208, 113, 176, 0.7)', window.biMoneyTick, 'Valor (R$)');
barChart(document.getElementById('chartFinal'), labelsFinal, valuesFinal, 'rgba(111, 223, 194, 0.7)', window.biMoneyTick, 'Valor (R$)');
barChart(document.getElementById('chartIntern'), labelsIntern, valuesIntern, 'rgba(255, 198, 108, 0.7)', null, 'Quantidade');
</script>

<?php require_once("templates/footer.php"); ?>
