<?php
include_once("check_logado.php");
require_once("templates/header.php");

if (!isset($conn) || !($conn instanceof PDO)) {
    die("Conexão inválida.");
}

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$anoInput = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT);
$ano = ($anoInput !== null && $anoInput !== false) ? (int)$anoInput : null;
$mes = (int)(filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: 0);
$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;
$auditorId = filter_input(INPUT_GET, 'auditor_id', FILTER_VALIDATE_INT) ?: null;

$hospitais = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp")
    ->fetchAll(PDO::FETCH_ASSOC);
$auditores = $conn->query("SELECT id_usuario, usuario_user FROM tb_user ORDER BY usuario_user")
    ->fetchAll(PDO::FETCH_ASSOC);

if ($ano === null && !filter_has_var(INPUT_GET, 'ano')) {
    $stmtAno = $conn->query("
        SELECT MAX(YEAR(data_inicio_neg)) AS ano
        FROM tb_negociacao
        WHERE data_inicio_neg IS NOT NULL
          AND data_inicio_neg <> '0000-00-00'
    ");
    $anoDb = $stmtAno->fetch(PDO::FETCH_ASSOC) ?: [];
    $ano = (int)($anoDb['ano'] ?? date('Y'));
}

$where = "YEAR(ng.data_inicio_neg) = :ano";
$params = [':ano' => $ano];
if ($mes > 0) {
    $where .= " AND MONTH(ng.data_inicio_neg) = :mes";
    $params[':mes'] = $mes;
}
if ($hospitalId) {
    $where .= " AND i.fk_hospital_int = :hospital_id";
    $params[':hospital_id'] = $hospitalId;
}
if ($auditorId) {
    $where .= " AND ng.fk_usuario_neg = :auditor_id";
    $params[':auditor_id'] = $auditorId;
}

$sqlTot = "
    SELECT
        SUM(ng.saving) AS total_saving,
        COUNT(*) AS total_registros
    FROM tb_negociacao ng
    INNER JOIN tb_internacao i ON i.id_internacao = ng.fk_id_int
    WHERE {$where}
";
$stmt = $conn->prepare($sqlTot);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$tot = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$totalSaving = (float)($tot['total_saving'] ?? 0);
$totalRegistros = (int)($tot['total_registros'] ?? 0);

$sqlAuditor = "
    SELECT
        u.usuario_user AS auditor,
        SUM(ng.saving) AS total_saving,
        COUNT(*) AS total_registros
    FROM tb_negociacao ng
    INNER JOIN tb_internacao i ON i.id_internacao = ng.fk_id_int
    LEFT JOIN tb_user u ON u.id_usuario = ng.fk_usuario_neg
    WHERE {$where}
    GROUP BY auditor
    ORDER BY total_saving DESC
    LIMIT 12
";
$stmt = $conn->prepare($sqlAuditor);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$auditorRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$sqlTipo = "
    SELECT
        COALESCE(ng.tipo_negociacao, 'Sem tipo') AS tipo,
        SUM(ng.saving) AS total_saving,
        COUNT(*) AS total_registros
    FROM tb_negociacao ng
    INNER JOIN tb_internacao i ON i.id_internacao = ng.fk_id_int
    WHERE {$where}
    GROUP BY tipo
    ORDER BY total_saving DESC
";
$stmt = $conn->prepare($sqlTipo);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$tipoRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$labelsAud = array_map(fn($r) => $r['auditor'] ?: 'Sem auditor', $auditorRows);
$savingAud = array_map(fn($r) => (float)$r['total_saving'], $auditorRows);
$countAud = array_map(fn($r) => (int)$r['total_registros'], $auditorRows);

$labelsTipo = array_map(fn($r) => $r['tipo'] ?: 'Sem tipo', $tipoRows);
$savingTipo = array_map(fn($r) => (float)$r['total_saving'], $tipoRows);
$countTipo = array_map(fn($r) => (int)$r['total_registros'], $tipoRows);
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="diversos/chartjs/Chart.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Dashboard Saving</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted">Ano <?= e($ano) ?></div>
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
            <label>Mês</label>
            <select name="mes">
                <option value="0">Todos</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $mes === $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Ano</label>
            <input type="number" name="ano" value="<?= e($ano) ?>">
        </div>
        <div class="bi-filter">
            <label>Auditor</label>
            <select name="auditor_id">
                <option value="">Todos</option>
                <?php foreach ($auditores as $a): ?>
                    <option value="<?= (int)$a['id_usuario'] ?>" <?= $auditorId == $a['id_usuario'] ? 'selected' : '' ?>>
                        <?= e($a['usuario_user']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-actions">
            <button class="bi-btn" type="submit">Aplicar</button>
        </div>
    </form>

    <div class="bi-panel">
        <div class="bi-kpis">
            <div class="bi-kpi">
                <small>Total saving</small>
                <strong>R$ <?= number_format($totalSaving, 2, ',', '.') ?></strong>
            </div>
            <div class="bi-kpi">
                <small>Qtde de saving</small>
                <strong><?= $totalRegistros ?></strong>
            </div>
        </div>
    </div>

    <div class="bi-panel" style="margin-top:16px;">
        <h3>Valor de saving por auditor</h3>
        <div class="bi-chart">
            <canvas id="chartSavingAuditor"></canvas>
        </div>
    </div>
    <div class="bi-panel">
        <h3>Qtde de saving por auditor</h3>
        <div class="bi-chart">
            <canvas id="chartQtdeAuditor"></canvas>
        </div>
    </div>
    <div class="bi-panel">
        <h3>Tipo de saving por auditor - valores</h3>
        <div class="bi-chart">
            <canvas id="chartTipoSavingValor"></canvas>
        </div>
    </div>
    <div class="bi-panel">
        <h3>Tipo de saving por auditor - quantidade</h3>
        <div class="bi-chart">
            <canvas id="chartTipoSavingQtd"></canvas>
        </div>
    </div>
</div>

<script>
const labelsAud = <?= json_encode($labelsAud) ?>;
const savingAud = <?= json_encode($savingAud) ?>;
const countAud = <?= json_encode($countAud) ?>;
const labelsTipo = <?= json_encode($labelsTipo) ?>;
const savingTipo = <?= json_encode($savingTipo) ?>;
const countTipo = <?= json_encode($countTipo) ?>;

function barChart(ctx, labels, data, color) {
    return new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ data, backgroundColor: color }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            scales: window.biChartScales ? window.biChartScales() : undefined
        }
    });
}

barChart(document.getElementById('chartSavingAuditor'), labelsAud, savingAud, 'rgba(141, 208, 255, 0.7)');
barChart(document.getElementById('chartQtdeAuditor'), labelsAud, countAud, 'rgba(208, 113, 176, 0.7)');
barChart(document.getElementById('chartTipoSavingValor'), labelsTipo, savingTipo, 'rgba(121, 199, 255, 0.7)');
barChart(document.getElementById('chartTipoSavingQtd'), labelsTipo, countTipo, 'rgba(111, 223, 194, 0.7)');
</script>

<?php require_once("templates/footer.php"); ?>
