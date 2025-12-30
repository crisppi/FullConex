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

$anoInput = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT);
$mesInput = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT);
$ano = ($anoInput !== null && $anoInput !== false) ? (int)$anoInput : null;
$mes = ($mesInput !== null && $mesInput !== false) ? (int)$mesInput : null;
if ($ano === null && !filter_has_var(INPUT_GET, 'ano')) {
    $ano = (int)date('Y');
}

$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;
$auditorNome = trim((string)(filter_input(INPUT_GET, 'auditor') ?? ''));

$hospitais = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp")
    ->fetchAll(PDO::FETCH_ASSOC);
$auditorListSql = "
    SELECT DISTINCT COALESCE(u.usuario_user, u2.usuario_user) AS auditor_nome
    FROM tb_visita v
    LEFT JOIN tb_user u ON u.id_usuario = v.fk_usuario_vis
    LEFT JOIN tb_user u2 ON u2.id_usuario = CAST(NULLIF(v.visita_auditor_prof_med,'') AS UNSIGNED)
    WHERE COALESCE(u.usuario_user, u2.usuario_user) IS NOT NULL
    ORDER BY auditor_nome
";
$auditores = $conn->query($auditorListSql)->fetchAll(PDO::FETCH_COLUMN);

$where = "v.fk_internacao_vis IS NOT NULL";
$params = [];
if (!empty($ano)) {
    $where .= " AND YEAR(v.data_visita_vis) = :ano";
    $params[':ano'] = (int)$ano;
}
if (!empty($mes)) {
    $where .= " AND MONTH(v.data_visita_vis) = :mes";
    $params[':mes'] = (int)$mes;
}
if (!empty($hospitalId)) {
    $where .= " AND i.fk_hospital_int = :hospital_id";
    $params[':hospital_id'] = (int)$hospitalId;
}
if (!empty($auditorNome)) {
    $where .= " AND COALESCE(u.usuario_user, u2.usuario_user) = :auditor_nome";
    $params[':auditor_nome'] = $auditorNome;
}

$auditorExpr = "COALESCE(u.usuario_user, u2.usuario_user, 'Sem informacoes')";

$sqlBaseIntern = "
    SELECT DISTINCT i.id_internacao,
        {$auditorExpr} AS auditor_nome,
        GREATEST(1, DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1) AS diarias
    FROM tb_visita v
    LEFT JOIN tb_user u ON u.id_usuario = v.fk_usuario_vis
    LEFT JOIN tb_user u2 ON u2.id_usuario = CAST(NULLIF(v.visita_auditor_prof_med,'') AS UNSIGNED)
    LEFT JOIN tb_internacao i ON i.id_internacao = v.fk_internacao_vis
    LEFT JOIN (
        SELECT fk_id_int_alt, MAX(data_alta_alt) AS data_alta_alt
        FROM tb_alta
        GROUP BY fk_id_int_alt
    ) al ON al.fk_id_int_alt = i.id_internacao
    WHERE {$where}
";

$sqlStats = "
    SELECT
        COUNT(DISTINCT id_internacao) AS total_internacoes,
        SUM(diarias) AS total_diarias,
        MAX(diarias) AS maior_permanencia,
        ROUND(AVG(diarias), 1) AS mp
    FROM ({$sqlBaseIntern}) t
";
$stmt = $conn->prepare($sqlStats);
$stmt->execute($params);
$stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$totalInternações = (int)($stats['total_internacoes'] ?? 0);
$totalDiárias = (int)($stats['total_diarias'] ?? 0);
$maiorPermanencia = (int)($stats['maior_permanencia'] ?? 0);
$mp = (float)($stats['mp'] ?? 0);

$sqlContas = "
    SELECT auditor_nome, SUM(COALESCE(ca.valor_apresentado_capeante,0)) AS total
    FROM ({$sqlBaseIntern}) t
    LEFT JOIN tb_capeante ca ON ca.fk_int_capeante = t.id_internacao
    GROUP BY auditor_nome
    ORDER BY total DESC
    LIMIT 12
";
$stmt = $conn->prepare($sqlContas);
$stmt->execute($params);
$contasRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$sqlGlosa = "
    SELECT auditor_nome, SUM(COALESCE(ca.valor_glosa_total,0)) AS total
    FROM ({$sqlBaseIntern}) t
    LEFT JOIN tb_capeante ca ON ca.fk_int_capeante = t.id_internacao
    GROUP BY auditor_nome
    ORDER BY total DESC
    LIMIT 12
";
$stmt = $conn->prepare($sqlGlosa);
$stmt->execute($params);
$glosaRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$sqlAuditadas = "
    SELECT auditor_nome, COUNT(DISTINCT id_internacao) AS total
    FROM ({$sqlBaseIntern}) t
    GROUP BY auditor_nome
    ORDER BY total DESC
    LIMIT 12
";
$stmt = $conn->prepare($sqlAuditadas);
$stmt->execute($params);
$auditadasRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$sqlVisitas = "
    SELECT {$auditorExpr} AS auditor_nome, COUNT(*) AS total
    FROM tb_visita v
    LEFT JOIN tb_user u ON u.id_usuario = v.fk_usuario_vis
    LEFT JOIN tb_user u2 ON u2.id_usuario = CAST(NULLIF(v.visita_auditor_prof_med,'') AS UNSIGNED)
    LEFT JOIN tb_internacao i ON i.id_internacao = v.fk_internacao_vis
    WHERE {$where}
    GROUP BY auditor_nome
    ORDER BY total DESC
    LIMIT 12
";
$stmt = $conn->prepare($sqlVisitas);
$stmt->execute($params);
$visitasRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

function labelsAndValues(array $rows): array
{
    $labels = array_map(fn($r) => $r['auditor_nome'] ?? 'Sem informacoes', $rows);
    $values = array_map(fn($r) => (float)($r['total'] ?? 0), $rows);
    return [$labels, $values];
}

[$contasLabels, $contasValues] = labelsAndValues($contasRows);
[$glosaLabels, $glosaValues] = labelsAndValues($glosaRows);
[$auditadasLabels, $auditadasValues] = labelsAndValues($auditadasRows);
[$visitasLabels, $visitasValues] = labelsAndValues($visitasRows);
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Auditor</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <div class="bi-layout">
        <aside class="bi-sidebar bi-stack">
            <div class="bi-panel">
                <h3>Auditor</h3>
                <div class="bi-filter-list">
                    <?php foreach ($auditores as $a): ?>
                        <div class="bi-filter-pill <?= $auditorNome === $a ? 'active' : '' ?>">
                            <a href="?auditor=<?= e($a) ?>" style="color:inherit;text-decoration:none;display:block;">
                                <?= e($a) ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <form class="bi-filter-card" method="get">
                <div class="bi-filter-card-header">Filtros</div>
                <div class="bi-filter-card-body bi-stack">
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
                        <label>Mes</label>
                        <select name="mes">
                            <option value="">Todos</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $mes == $m ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="bi-filter">
                        <label>Ano</label>
                        <input type="number" name="ano" value="<?= e($ano) ?>" min="2000" max="2100">
                    </div>
                    <?php if ($auditorNome !== ''): ?>
                        <input type="hidden" name="auditor" value="<?= e($auditorNome) ?>">
                    <?php endif; ?>
                    <button class="bi-filter-btn" type="submit">Aplicar</button>
                </div>
            </form>
        </aside>

        <section class="bi-main bi-stack">
            <div class="bi-kpis kpi-compact">
                <div class="bi-kpi kpi-berry kpi-compact">
                    <small>Internações</small>
                    <strong><?= number_format($totalInternações, 0, ',', '.') ?></strong>
                </div>
                <div class="bi-kpi kpi-teal kpi-compact">
                    <small>Diárias</small>
                    <strong><?= number_format($totalDiárias, 0, ',', '.') ?></strong>
                </div>
                <div class="bi-kpi kpi-indigo kpi-compact">
                    <small>MP</small>
                    <strong><?= number_format($mp, 1, ',', '.') ?></strong>
                </div>
                <div class="bi-kpi kpi-rose kpi-compact">
                    <small>Maior permanência</small>
                    <strong><?= number_format($maiorPermanencia, 0, ',', '.') ?></strong>
                </div>
            </div>

            <div class="bi-grid fixed-2">
                <div class="bi-panel">
                    <h3>Contas por Auditor</h3>
                    <div class="bi-chart"><canvas id="chartAuditorContas"></canvas></div>
                </div>
                <div class="bi-panel">
                    <h3>Glosa por Auditor</h3>
                    <div class="bi-chart"><canvas id="chartAuditorGlosa"></canvas></div>
                </div>
            </div>

            <div class="bi-grid fixed-2">
                <div class="bi-panel">
                    <h3>Contas Auditadas</h3>
                    <div class="bi-chart"><canvas id="chartAuditorAuditadas"></canvas></div>
                </div>
                <div class="bi-panel">
                    <h3>Visitas</h3>
                    <div class="bi-chart"><canvas id="chartAuditorVisitas"></canvas></div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
const contasLabels = <?= json_encode($contasLabels) ?>;
const contasValues = <?= json_encode($contasValues) ?>;
const glosaLabels = <?= json_encode($glosaLabels) ?>;
const glosaValues = <?= json_encode($glosaValues) ?>;
const auditadasLabels = <?= json_encode($auditadasLabels) ?>;
const auditadasValues = <?= json_encode($auditadasValues) ?>;
const visitasLabels = <?= json_encode($visitasLabels) ?>;
const visitasValues = <?= json_encode($visitasValues) ?>;

function barOptionsMoney() {
  return {
    legend: { display: false },
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: {
        ticks: {
          color: '#e8f1ff',
          callback: (value) => window.biMoneyTick ? window.biMoneyTick(value) : value
        },
        grid: { color: 'rgba(255,255,255,0.1)' },
        title: { display: true, text: 'Valor (R$)', color: '#e8f1ff' }
      },
      xAxes: [{ ticks: { fontColor: '#e8f1ff' }, gridLines: { display: false } }],
      yAxes: [{
        ticks: {
          fontColor: '#e8f1ff',
          callback: (value) => window.biMoneyTick ? window.biMoneyTick(value) : value
        },
        gridLines: { color: 'rgba(255,255,255,0.1)' },
        scaleLabel: { display: true, labelString: 'Valor (R$)', fontColor: '#e8f1ff' }
      }]
    }
  };
}

function barOptions() {
  return {
    legend: { display: false },
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: {
        ticks: { color: '#e8f1ff' },
        grid: { color: 'rgba(255,255,255,0.1)' },
        title: { display: true, text: 'Quantidade', color: '#e8f1ff' }
      },
      xAxes: [{ ticks: { fontColor: '#e8f1ff' }, gridLines: { display: false } }],
      yAxes: [{
        ticks: { fontColor: '#e8f1ff' },
        gridLines: { color: 'rgba(255,255,255,0.1)' },
        scaleLabel: { display: true, labelString: 'Quantidade', fontColor: '#e8f1ff' }
      }]
    }
  };
}

new Chart(document.getElementById('chartAuditorContas'), {
  type: 'bar',
  data: { labels: contasLabels, datasets: [{ label: '', data: contasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptionsMoney()
});

new Chart(document.getElementById('chartAuditorGlosa'), {
  type: 'bar',
  data: { labels: glosaLabels, datasets: [{ label: '', data: glosaValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptionsMoney()
});

new Chart(document.getElementById('chartAuditorAuditadas'), {
  type: 'bar',
  data: { labels: auditadasLabels, datasets: [{ label: '', data: auditadasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptions()
});

new Chart(document.getElementById('chartAuditorVisitas'), {
  type: 'bar',
  data: { labels: visitasLabels, datasets: [{ label: '', data: visitasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptions()
});
</script>

<?php require_once("templates/footer.php"); ?>
