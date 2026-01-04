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

$startInput = filter_input(INPUT_GET, 'data_inicio') ?: '';
$endInput = filter_input(INPUT_GET, 'data_fim') ?: '';
$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;

$defaultStart = date('Y-m-01', strtotime('-11 months'));
$defaultEnd = date('Y-m-d');

$startDate = DateTime::createFromFormat('Y-m-d', $startInput) ?: new DateTime($defaultStart);
$endDate = DateTime::createFromFormat('Y-m-d', $endInput) ?: new DateTime($defaultEnd);
$startDate->modify('first day of this month');
$endDate->modify('last day of this month');

$startStr = $startDate->format('Y-m-d');
$endStr = $endDate->format('Y-m-d');

$mesMap = [
    '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr',
    '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
    '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
];

$monthKeys = [];
$monthLabels = [];
$cursor = clone $startDate;
$limitEnd = (clone $endDate)->modify('first day of next month');
while ($cursor < $limitEnd) {
    $key = $cursor->format('Y-m');
    $monthKeys[] = $key;
    $monthLabels[] = $mesMap[$cursor->format('m')] . '/' . $cursor->format('Y');
    $cursor->modify('+1 month');
}

$hospitais = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp")
    ->fetchAll(PDO::FETCH_ASSOC);

$params = [':start' => $startStr, ':end' => $endStr];
$whereHosp = '';
if ($hospitalId) {
    $whereHosp = " AND i.fk_hospital_int = :hospital_id";
    $params[':hospital_id'] = (int)$hospitalId;
}

$dateExpr = "COALESCE(NULLIF(ca.data_final_capeante,'0000-00-00'), NULLIF(ca.data_fech_capeante,'0000-00-00'), NULLIF(ca.data_create_cap,'0000-00-00'))";

// Apresentado x pos-auditoria (mensal)
$sqlValores = "
    SELECT DATE_FORMAT(ref_date, '%Y-%m') AS ym,
           SUM(COALESCE(valor_apresentado_capeante,0)) AS valor_apresentado,
           SUM(COALESCE(valor_final_capeante,0)) AS valor_final
    FROM (
        SELECT ca.valor_apresentado_capeante,
               ca.valor_final_capeante,
               {$dateExpr} AS ref_date,
               i.fk_hospital_int
        FROM tb_capeante ca
        JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
    ) t
    WHERE ref_date IS NOT NULL AND ref_date <> '0000-00-00'
      AND ref_date BETWEEN :start AND :end
      {$whereHosp}
    GROUP BY ym
    ORDER BY ym ASC
";
$stmt = $conn->prepare($sqlValores);
$stmt->execute($params);
$valorRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$apresentado = array_fill_keys($monthKeys, 0.0);
$final = array_fill_keys($monthKeys, 0.0);
foreach ($valorRows as $row) {
    $ym = $row['ym'];
    if (!isset($apresentado[$ym])) continue;
    $apresentado[$ym] = (float)($row['valor_apresentado'] ?? 0);
    $final[$ym] = (float)($row['valor_final'] ?? 0);
}

// Top seguradoras por valor apresentado
$sqlTopSeg = "
    SELECT s.id_seguradora, COALESCE(s.seguradora_seg, 'Sem informacoes') AS nome,
           SUM(COALESCE(ca.valor_apresentado_capeante,0)) AS total
    FROM tb_capeante ca
    JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
    LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
    LEFT JOIN tb_seguradora s ON s.id_seguradora = pa.fk_seguradora_pac
    WHERE {$dateExpr} IS NOT NULL AND {$dateExpr} <> '0000-00-00'
      AND {$dateExpr} BETWEEN :start AND :end
      {$whereHosp}
    GROUP BY s.id_seguradora
    ORDER BY total DESC
    LIMIT 5
";
$stmt = $conn->prepare($sqlTopSeg);
$stmt->execute($params);
$topSegRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$topSegIds = array_filter(array_map(fn($r) => $r['id_seguradora'], $topSegRows));
$segSeries = [];
foreach ($topSegRows as $row) {
    $segSeries[$row['id_seguradora']] = [
        'nome' => $row['nome'],
        'data' => array_fill_keys($monthKeys, 0.0)
    ];
}

if ($topSegIds) {
    $inPlaceholders = implode(',', array_fill(0, count($topSegIds), '?'));
    $sqlSegSeries = "
        SELECT s.id_seguradora, DATE_FORMAT(ref_date, '%Y-%m') AS ym,
               SUM(COALESCE(valor_apresentado_capeante,0)) AS total
        FROM (
            SELECT ca.valor_apresentado_capeante,
                   {$dateExpr} AS ref_date,
                   i.fk_hospital_int,
                   pa.fk_seguradora_pac
            FROM tb_capeante ca
            JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
            LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
        ) t
        LEFT JOIN tb_seguradora s ON s.id_seguradora = t.fk_seguradora_pac
        WHERE ref_date IS NOT NULL AND ref_date <> '0000-00-00'
          AND ref_date BETWEEN ? AND ?
          " . ($hospitalId ? " AND fk_hospital_int = ? " : "") . "
          AND s.id_seguradora IN ({$inPlaceholders})
        GROUP BY s.id_seguradora, ym
        ORDER BY ym ASC
    ";
    $bind = [$startStr, $endStr];
    if ($hospitalId) {
        $bind[] = (int)$hospitalId;
    }
    $bind = array_merge($bind, $topSegIds);
    $stmt = $conn->prepare($sqlSegSeries);
    $stmt->execute($bind);
    $segRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($segRows as $row) {
        $id = $row['id_seguradora'];
        $ym = $row['ym'];
        if (!isset($segSeries[$id]['data'][$ym])) continue;
        $segSeries[$id]['data'][$ym] = (float)($row['total'] ?? 0);
    }
}

// Top 10 contas por valor apresentado
$sqlTopContas = "
    SELECT i.id_internacao,
           pa.nome_pac,
           ho.nome_hosp,
           ca.valor_apresentado_capeante AS valor
    FROM tb_capeante ca
    JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
    LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
    LEFT JOIN tb_hospital ho ON ho.id_hospital = i.fk_hospital_int
    WHERE {$dateExpr} IS NOT NULL AND {$dateExpr} <> '0000-00-00'
      AND {$dateExpr} BETWEEN :start AND :end
      {$whereHosp}
    ORDER BY ca.valor_apresentado_capeante DESC
    LIMIT 10
";
$stmt = $conn->prepare($sqlTopContas);
$stmt->execute($params);
$topContas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$topLabels = [];
$topValues = [];
foreach ($topContas as $row) {
    $label = '#' . $row['id_internacao'] . ' - ' . ($row['nome_pac'] ?? 'Sem paciente');
    $topLabels[] = $label;
    $topValues[] = (float)($row['valor'] ?? 0);
}
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="diversos/chartjs/Chart.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Financeiro Realizado</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted">Últimos 12 meses</div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi/navegacao" title="Navegação">
                <i class="bi bi-grid-3x3-gap"></i>
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
            <label>Data inicio</label>
            <input type="date" name="data_inicio" value="<?= e($startStr) ?>">
        </div>
        <div class="bi-filter">
            <label>Data fim</label>
            <input type="date" name="data_fim" value="<?= e($endStr) ?>">
        </div>
        <div class="bi-actions">
            <button class="bi-btn" type="submit">Aplicar</button>
        </div>
    </form>

    <div class="bi-grid fixed-2" style="margin-top:16px;">
        <div class="bi-panel">
            <h3 class="text-center" style="margin-bottom:12px;">Apresentado x Pós-auditoria</h3>
            <div class="bi-chart"><canvas id="chartValores"></canvas></div>
        </div>
        <div class="bi-panel">
            <h3 class="text-center" style="margin-bottom:12px;">Variação por seguradora (apresentado)</h3>
            <div class="bi-chart"><canvas id="chartSeguradora"></canvas></div>
        </div>
        <div class="bi-panel">
            <h3 class="text-center" style="margin-bottom:12px;">Top 10 contas por valor apresentado</h3>
            <div class="bi-chart"><canvas id="chartTopContas"></canvas></div>
        </div>
    </div>
</div>

<script>
const chartLabels = <?= json_encode($monthLabels) ?>;
const valorApresentado = <?= json_encode(array_values($apresentado)) ?>;
const valorFinal = <?= json_encode(array_values($final)) ?>;
const segSeries = <?= json_encode($segSeries) ?>;
const topLabels = <?= json_encode($topLabels) ?>;
const topValues = <?= json_encode($topValues) ?>;

function groupedBar(ctx, labels, dataA, dataB) {
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Apresentado', data: dataA, backgroundColor: 'rgba(127, 196, 255, 0.7)' },
                { label: 'Pós-auditoria', data: dataB, backgroundColor: 'rgba(208, 113, 176, 0.7)' }
            ]
        },
        options: {
            legend: window.biLegendWhite ? window.biLegendWhite : undefined,
            scales: window.biChartScales ? window.biChartScales() : undefined
        }
    });
}

function multiLine(ctx, labels, series) {
    const palette = [
        'rgba(141, 208, 255, 0.9)',
        'rgba(255, 198, 108, 0.9)',
        'rgba(111, 223, 194, 0.9)',
        'rgba(255, 99, 132, 0.9)',
        'rgba(173, 131, 255, 0.9)'
    ];
    const datasets = Object.values(series).map((s, i) => ({
        label: s.nome,
        data: Object.values(s.data),
        borderColor: palette[i % palette.length],
        backgroundColor: palette[i % palette.length],
        fill: false,
        tension: 0.25
    }));
    return new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
            legend: window.biLegendWhite ? window.biLegendWhite : undefined,
            scales: window.biChartScales ? window.biChartScales() : undefined
        }
    });
}

function horizontalBar(ctx, labels, data) {
    return new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels,
            datasets: [{
                label: 'Valor apresentado',
                data,
                backgroundColor: 'rgba(121, 199, 255, 0.7)'
            }]
        },
        options: {
            legend: window.biLegendWhite ? window.biLegendWhite : undefined,
            scales: window.biChartScales ? window.biChartScales() : undefined
        }
    });
}

groupedBar(document.getElementById('chartValores'), chartLabels, valorApresentado, valorFinal);
multiLine(document.getElementById('chartSeguradora'), chartLabels, segSeries);
horizontalBar(document.getElementById('chartTopContas'), topLabels, topValues);
</script>

<?php require_once("templates/footer.php"); ?>
