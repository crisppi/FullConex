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

$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;
$mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: null;
$ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: null;

$hospitais = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp")
    ->fetchAll(PDO::FETCH_ASSOC);
$anos = $conn->query("SELECT DISTINCT YEAR(data_intern_int) AS ano FROM tb_internacao WHERE data_intern_int IS NOT NULL AND data_intern_int <> '0000-00-00' ORDER BY ano DESC")
    ->fetchAll(PDO::FETCH_COLUMN);
$meses = [
    1 => 'Jan',
    2 => 'Fev',
    3 => 'Mar',
    4 => 'Abr',
    5 => 'Mai',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Ago',
    9 => 'Set',
    10 => 'Out',
    11 => 'Nov',
    12 => 'Dez',
];

$conds = [];
$params = [];
if ($hospitalId) {
    $conds[] = "i.fk_hospital_int = :hospital_id";
    $params[':hospital_id'] = $hospitalId;
}
if ($ano) {
    $conds[] = "YEAR(i.data_intern_int) = :ano";
    $params[':ano'] = $ano;
}
if ($mes) {
    $conds[] = "MONTH(i.data_intern_int) = :mes";
    $params[':mes'] = $mes;
}
$where = $conds ? ('AND ' . implode(' AND ', $conds)) : '';

$sqlBase = "
    FROM tb_internacao i
    LEFT JOIN (
        SELECT fk_id_int_alt, MAX(data_alta_alt) AS data_alta_alt
        FROM tb_alta
        GROUP BY fk_id_int_alt
    ) al ON al.fk_id_int_alt = i.id_internacao
    LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
    LEFT JOIN tb_seguradora s ON s.id_seguradora = pa.fk_seguradora_pac
    LEFT JOIN tb_patologia p ON p.id_patologia = i.fk_patologia_int
    LEFT JOIN tb_hospital h ON h.id_hospital = i.fk_hospital_int
    WHERE i.data_intern_int IS NOT NULL
      AND i.data_intern_int <> '0000-00-00'
      {$where}
";

$sqlKpis = "
    SELECT
        COUNT(*) AS total_internacoes,
        SUM(diarias) AS total_diarias,
        MAX(diarias) AS maior_permanencia
    FROM (
        SELECT
            i.id_internacao,
            (DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1) AS diarias,
            COALESCE(NULLIF(p.dias_pato, 0), NULLIF(s.longa_permanencia_seg, 0), 20) AS prazo_dias
        {$sqlBase}
    ) t
    WHERE t.diarias > t.prazo_dias
";
$stmt = $conn->prepare($sqlKpis);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$kpis = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$totalInternacoes = (int)($kpis['total_internacoes'] ?? 0);
$totalDiarias = (int)($kpis['total_diarias'] ?? 0);
$maiorPermanencia = (int)($kpis['maior_permanencia'] ?? 0);
$mp = $totalInternacoes > 0 ? round($totalDiarias / $totalInternacoes, 1) : 0.0;

$sqlHosp = "
    SELECT
        h.nome_hosp AS hospital,
        COUNT(*) AS total
    FROM (
        SELECT
            i.id_internacao,
            i.fk_hospital_int,
            (DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1) AS diarias,
            COALESCE(NULLIF(p.dias_pato, 0), NULLIF(s.longa_permanencia_seg, 0), 20) AS prazo_dias
        {$sqlBase}
    ) t
    INNER JOIN tb_hospital h ON h.id_hospital = t.fk_hospital_int
    WHERE t.diarias > t.prazo_dias
    GROUP BY h.nome_hosp
    ORDER BY total DESC
    LIMIT 10
";
$stmt = $conn->prepare($sqlHosp);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$rowsHosp = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$sqlTop = "
    SELECT
        i.id_internacao,
        h.nome_hosp AS hospital,
        pa.nome_pac AS paciente,
        (DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1) AS diarias,
        COALESCE(NULLIF(p.dias_pato, 0), NULLIF(s.longa_permanencia_seg, 0), 20) AS prazo_dias
    {$sqlBase}
    HAVING diarias > prazo_dias
    ORDER BY diarias DESC
    LIMIT 10
";
$stmt = $conn->prepare($sqlTop);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$rowsTop = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$chartLabels = array_map(fn($r) => $r['hospital'] ?? 'Sem hospital', $rowsHosp);
$chartValues = array_map(fn($r) => (int)($r['total'] ?? 0), $rowsHosp);
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20251229">
<script src="diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20251221"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Longa Permanencia</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <div class="bi-layout">
        <aside class="bi-sidebar bi-stack">
            <form class="bi-filter-card" method="get">
                <div class="bi-filter-card-header">Filtros</div>
                <div class="bi-filter-card-body bi-stack">
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
                        <label>Mes</label>
                        <select name="mes">
                            <option value="">Todos</option>
                            <?php foreach ($meses as $m => $label): ?>
                                <option value="<?= $m ?>" <?= (int)$mes === $m ? 'selected' : '' ?>><?= e($label) ?></option>
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
                    <div class="bi-filter-actions">
                        <button class="bi-filter-btn" type="submit">Aplicar</button>
                        <a class="bi-filter-btn" href="<?= $BASE_URL ?>LongaPermanenciaBI.php">Limpar Filtros</a>
                    </div>
                </div>
            </form>
        </aside>

        <section class="bi-main bi-stack">
            <div class="bi-kpis kpi-compact">
                <div class="bi-kpi kpi-indigo kpi-compact">
                    <small>Internacoes</small>
                    <strong><?= number_format($totalInternacoes, 0, ',', '.') ?></strong>
                </div>
                <div class="bi-kpi kpi-teal kpi-compact">
                    <small>Diarias</small>
                    <strong><?= number_format($totalDiarias, 0, ',', '.') ?></strong>
                </div>
                <div class="bi-kpi kpi-amber kpi-compact">
                    <small>MP</small>
                    <strong><?= number_format($mp, 1, ',', '.') ?></strong>
                </div>
                <div class="bi-kpi kpi-rose kpi-compact">
                    <small>Maior Permanencia</small>
                    <strong><?= number_format($maiorPermanencia, 0, ',', '.') ?></strong>
                </div>
            </div>

            <div class="bi-panel">
                <h3>Hospital</h3>
                <div class="bi-chart"><canvas id="chartLongaHosp"></canvas></div>
            </div>

            <div class="bi-panel">
                <h3>Diarias</h3>
                <div class="table-responsive">
                    <table class="bi-table">
                        <thead>
                            <tr>
                                <th>Diarias</th>
                                <th>Hospital</th>
                                <th>Relatorio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rowsTop as $row): ?>
                                <?php
                                    $diarias = (int)($row['diarias'] ?? 0);
                                    $paciente = $row['paciente'] ?? 'Sem paciente';
                                ?>
                                <tr>
                                    <td><?= number_format($diarias, 0, ',', '.') ?></td>
                                    <td><?= e($row['hospital'] ?? 'Sem hospital') ?></td>
                                    <td><?= e("Paciente {$paciente} - {$diarias} dias") ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$rowsTop): ?>
                                <tr>
                                    <td colspan="3">Sem registros</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
const lpLabels = <?= json_encode($chartLabels) ?>;
const lpValues = <?= json_encode($chartValues) ?>;
new Chart(document.getElementById('chartLongaHosp'), {
  type: 'bar',
  data: {
    labels: lpLabels,
    datasets: [{
      label: '',
      data: lpValues,
      backgroundColor: 'rgba(126, 150, 255, 0.8)',
      borderRadius: 10,
      maxBarThickness: 60
    }]
  },
  options: {
    legend: { display: false },
    plugins: { legend: { display: false } },
    scales: {
      x: {
        ticks: { color: '#e8f1ff' },
        grid: { display: false }
      },
      y: {
        ticks: { color: '#e8f1ff' },
        beginAtZero: true,
        grid: { color: 'rgba(255,255,255,0.1)' }
      }
    }
  }
});
</script>

<?php require_once("templates/footer.php"); ?>
