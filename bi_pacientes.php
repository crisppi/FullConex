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

$hoje = date('Y-m-d');
$dataIni = filter_input(INPUT_GET, 'data_ini') ?: date('Y-m-d', strtotime('-180 days'));
$dataFim = filter_input(INPUT_GET, 'data_fim') ?: $hoje;
$internado = trim((string)(filter_input(INPUT_GET, 'internado') ?? ''));
$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;
$tipoInternação = trim((string)(filter_input(INPUT_GET, 'tipo_internacao') ?? ''));
$modoAdmissão = trim((string)(filter_input(INPUT_GET, 'modo_admissao') ?? ''));
$uti = trim((string)(filter_input(INPUT_GET, 'uti') ?? ''));
$rn = trim((string)(filter_input(INPUT_GET, 'rn') ?? ''));

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
if ($rn !== '') {
    $where .= " AND pa.recem_nascido_pac = :rn";
    $params[':rn'] = $rn;
}

$utiJoin = "LEFT JOIN (SELECT DISTINCT fk_internacao_uti FROM tb_uti) ut ON ut.fk_internacao_uti = i.id_internacao";
if ($uti === 's') {
    $where .= " AND ut.fk_internacao_uti IS NOT NULL";
}
if ($uti === 'n') {
    $where .= " AND ut.fk_internacao_uti IS NULL";
}

$sqlCusto = "
    SELECT
        pa.nome_pac AS paciente,
        SUM(ca.valor_apresentado_capeante) AS total_valor
    FROM tb_capeante ca
    INNER JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
    LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
    {$utiJoin}
    WHERE {$where}
    GROUP BY paciente
    ORDER BY total_valor DESC
    LIMIT 10
";
$stmt = $conn->prepare($sqlCusto);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$custoRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$sqlMedio = "
    SELECT
        pa.nome_pac AS paciente,
        AVG(ca.valor_apresentado_capeante) AS valor_medio
    FROM tb_capeante ca
    INNER JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
    LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
    {$utiJoin}
    WHERE {$where}
    GROUP BY paciente
    ORDER BY valor_medio DESC
    LIMIT 10
";
$stmt = $conn->prepare($sqlMedio);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$medioRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$labelsCusto = array_map(fn($r) => $r['paciente'] ?: 'Paciente', $custoRows);
$valsCusto = array_map(fn($r) => (float)$r['total_valor'], $custoRows);
$labelsMedio = array_map(fn($r) => $r['paciente'] ?: 'Paciente', $medioRows);
$valsMedio = array_map(fn($r) => (float)$r['valor_medio'], $medioRows);
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Dashboard Pacientes</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted">Custos por paciente</div>
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
            <label>Tipo internação</label>
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
            <label>Modo admissão</label>
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
            <label>RN</label>
            <select name="rn">
                <option value="">Todos</option>
                <option value="s" <?= $rn === 's' ? 'selected' : '' ?>>Sim</option>
                <option value="n" <?= $rn === 'n' ? 'selected' : '' ?>>Não</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Data inicial</label>
            <input type="date" name="data_ini" value="<?= e($dataIni) ?>">
        </div>
        <div class="bi-filter">
            <label>Data final</label>
            <input type="date" name="data_fim" value="<?= e($dataFim) ?>">
        </div>
        <div class="bi-actions">
            <button class="bi-btn" type="submit">Aplicar</button>
        </div>
    </form>

    <div class="bi-panel">
        <h3>Custo por paciente</h3>
        <div class="bi-chart">
            <canvas id="chartCustoPaciente"></canvas>
        </div>
    </div>

    <div class="bi-panel">
        <h3>Custo médio internação por paciente</h3>
        <div class="bi-chart">
            <canvas id="chartCustoMedio"></canvas>
        </div>
    </div>
</div>

<script>
const labelsCusto = <?= json_encode($labelsCusto) ?>;
const valsCusto = <?= json_encode($valsCusto) ?>;
const labelsMedio = <?= json_encode($labelsMedio) ?>;
const valsMedio = <?= json_encode($valsMedio) ?>;

function barChart(ctx, labels, data) {
    return new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ data, backgroundColor: 'rgba(141, 208, 255, 0.7)' }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            scales: {
                xAxes: [{
                    ticks: {
                        fontColor: '#eaf6ff',
                        autoSkip: false,
                        maxRotation: 25,
                        minRotation: 25
                    },
                    gridLines: { color: 'rgba(255,255,255,0.08)' }
                }],
                yAxes: [{
                    ticks: {
                        fontColor: '#eaf6ff',
                        autoSkip: false,
                        maxTicksLimit: 6
                    },
                    gridLines: { color: 'rgba(255,255,255,0.08)' }
                }]
            }
        }
    });
}

barChart(document.getElementById('chartCustoPaciente'), labelsCusto, valsCusto);
barChart(document.getElementById('chartCustoMedio'), labelsMedio, valsMedio);
</script>

<?php require_once("templates/footer.php"); ?>
