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
$cargo = trim((string)(filter_input(INPUT_GET, 'cargo') ?? ''));
$auditorId = filter_input(INPUT_GET, 'auditor_id', FILTER_VALIDATE_INT) ?: null;

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

$cargoMap = [
    'adm' => ['administrativo', 'adm', 'administrador'],
    'enf' => ['enf_auditor', 'enfer_auditor', 'enfermeiro'],
    'med' => ['med_auditor', 'medico_auditor', 'medico'],
];
$cargoValues = $cargoMap[$cargo] ?? array_merge(...array_values($cargoMap));

$cargoWhere = '';
$cargoParams = [];
if ($cargoValues) {
    $ph = [];
    foreach ($cargoValues as $i => $val) {
        $key = ":cargo{$i}";
        $ph[] = $key;
        $cargoParams[$key] = $val;
    }
    $cargoWhere = " AND LOWER(u.cargo_user) IN (" . implode(',', $ph) . ")";
}

function build_query(array $base, array $overrides = []): string
{
    $params = array_merge($base, $overrides);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    return http_build_query($params);
}

$baseQuery = [
    'hospital_id' => $hospitalId,
    'mes' => $mes,
    'ano' => $ano,
    'cargo' => $cargo,
    'auditor_id' => $auditorId,
];

$sqlAuditores = "
    SELECT id_usuario, usuario_user, cargo_user
    FROM tb_user u
    WHERE ativo_user = 1
    {$cargoWhere}
    ORDER BY usuario_user ASC
";
$stmt = $conn->prepare($sqlAuditores);
foreach ($cargoParams as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$auditores = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$capeanteWhere = ["ca.ref_date IS NOT NULL", "ca.ref_date <> '0000-00-00'"];
$capeanteParams = [];
if ($ano) {
    $capeanteWhere[] = "YEAR(ca.ref_date) = :ano";
    $capeanteParams[':ano'] = $ano;
}
if ($mes) {
    $capeanteWhere[] = "MONTH(ca.ref_date) = :mes";
    $capeanteParams[':mes'] = $mes;
}
if ($hospitalId) {
    $capeanteWhere[] = "i.fk_hospital_int = :hospital_id";
    $capeanteParams[':hospital_id'] = $hospitalId;
}
if ($auditorId) {
    $capeanteWhere[] = "aud.auditor_id = :auditor_id";
    $capeanteParams[':auditor_id'] = $auditorId;
}
$capeanteWhereSql = implode(' AND ', $capeanteWhere);

$sqlCapeanteAuditores = "
    SELECT
        aud.auditor_id,
        COALESCE(u.usuario_user, CONCAT('ID ', aud.auditor_id)) AS auditor,
        SUM(COALESCE(ca.valor_apresentado_capeante,0)) AS total_contas,
        SUM(COALESCE(ca.valor_glosa_total,0)) AS total_glosa,
        COUNT(DISTINCT ca.id_capeante) AS contas_auditadas
    FROM (
        SELECT
            ca.*,
            COALESCE(NULLIF(ca.data_inicial_capeante,'0000-00-00'), NULLIF(ca.data_digit_capeante,'0000-00-00'), NULLIF(ca.data_fech_capeante,'0000-00-00')) AS ref_date
        FROM tb_capeante ca
    ) ca
    INNER JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
    LEFT JOIN (
        SELECT
            id_capeante,
            COALESCE(NULLIF(fk_id_aud_med,0), NULLIF(fk_id_aud_enf,0), NULLIF(fk_id_aud_adm,0)) AS auditor_id
        FROM tb_capeante
    ) aud ON aud.id_capeante = ca.id_capeante
    LEFT JOIN tb_user u ON u.id_usuario = aud.auditor_id
    WHERE aud.auditor_id IS NOT NULL
      AND {$capeanteWhereSql}
      {$cargoWhere}
    GROUP BY aud.auditor_id, auditor
    ORDER BY total_contas DESC
    LIMIT 12
";
$stmt = $conn->prepare($sqlCapeanteAuditores);
foreach ($capeanteParams as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
foreach ($cargoParams as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$rowsAuditores = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$visitWhere = [];
$visitParams = [];
if ($ano) {
    $visitWhere[] = "YEAR(v.data_visita_vis) = :ano";
    $visitParams[':ano'] = $ano;
}
if ($mes) {
    $visitWhere[] = "MONTH(v.data_visita_vis) = :mes";
    $visitParams[':mes'] = $mes;
}
if ($hospitalId) {
    $visitWhere[] = "i.fk_hospital_int = :hospital_id";
    $visitParams[':hospital_id'] = $hospitalId;
}
if ($auditorId) {
    $visitWhere[] = "v.fk_usuario_vis = :auditor_id";
    $visitParams[':auditor_id'] = $auditorId;
}
$visitWhereSql = $visitWhere ? ('AND ' . implode(' AND ', $visitWhere)) : '';

$sqlVisitas = "
    SELECT
        v.fk_usuario_vis AS auditor_id,
        COALESCE(u.usuario_user, CONCAT('ID ', v.fk_usuario_vis)) AS auditor,
        COUNT(*) AS total
    FROM tb_visita v
    INNER JOIN tb_internacao i ON i.id_internacao = v.fk_internacao_vis
    LEFT JOIN tb_user u ON u.id_usuario = v.fk_usuario_vis
    WHERE (v.retificado IS NULL OR v.retificado = 0)
      {$visitWhereSql}
      {$cargoWhere}
    GROUP BY auditor_id, auditor
    ORDER BY total DESC
    LIMIT 12
";
$stmt = $conn->prepare($sqlVisitas);
foreach ($visitParams as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
foreach ($cargoParams as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$rowsVisitas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$kpiWhere = [];
$kpiParams = [];
if ($hospitalId) {
    $kpiWhere[] = "i.fk_hospital_int = :hospital_id";
    $kpiParams[':hospital_id'] = $hospitalId;
}
if ($ano) {
    $kpiWhere[] = "YEAR(i.data_intern_int) = :ano";
    $kpiParams[':ano'] = $ano;
}
if ($mes) {
    $kpiWhere[] = "MONTH(i.data_intern_int) = :mes";
    $kpiParams[':mes'] = $mes;
}
$kpiJoin = '';
if ($auditorId || $cargoValues) {
    $kpiJoin = "
        LEFT JOIN (
            SELECT
                fk_int_capeante,
                COALESCE(NULLIF(fk_id_aud_med,0), NULLIF(fk_id_aud_enf,0), NULLIF(fk_id_aud_adm,0)) AS auditor_id
            FROM tb_capeante
        ) aud ON aud.fk_int_capeante = i.id_internacao
        LEFT JOIN tb_user u ON u.id_usuario = aud.auditor_id
    ";
    if ($auditorId) {
        $kpiWhere[] = "aud.auditor_id = :auditor_id";
        $kpiParams[':auditor_id'] = $auditorId;
    }
    if ($cargoWhere) {
        $kpiWhere[] = ltrim(str_replace('AND', '', $cargoWhere));
    }
}
$kpiWhereSql = $kpiWhere ? ('WHERE ' . implode(' AND ', $kpiWhere)) : '';

$sqlKpis = "
    SELECT
        COUNT(DISTINCT i.id_internacao) AS total_internacoes,
        SUM(GREATEST(1, DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1)) AS total_diarias,
        MAX(GREATEST(1, DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1)) AS maior_permanencia
    FROM tb_internacao i
    LEFT JOIN (
        SELECT fk_id_int_alt, MAX(data_alta_alt) AS data_alta_alt
        FROM tb_alta
        GROUP BY fk_id_int_alt
    ) al ON al.fk_id_int_alt = i.id_internacao
    {$kpiJoin}
    {$kpiWhereSql}
";
$stmt = $conn->prepare($sqlKpis);
foreach ($kpiParams as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
foreach ($cargoParams as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$kpis = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$totalInternacoes = (int)($kpis['total_internacoes'] ?? 0);
$totalDiarias = (int)($kpis['total_diarias'] ?? 0);
$maiorPermanencia = (int)($kpis['maior_permanencia'] ?? 0);
$mp = $totalInternacoes > 0 ? round($totalDiarias / $totalInternacoes, 1) : 0.0;

$auditorLabels = array_map(fn($r) => $r['auditor'], $rowsAuditores);
$contasValues = array_map(fn($r) => (float)$r['total_contas'], $rowsAuditores);
$glosaLabels = array_map(fn($r) => $r['auditor'], $rowsAuditores);
$glosaValues = array_map(fn($r) => (float)$r['total_glosa'], $rowsAuditores);
$auditadasValues = array_map(fn($r) => (int)$r['contas_auditadas'], $rowsAuditores);
$visitasLabels = array_map(fn($r) => $r['auditor'], $rowsVisitas);
$visitasValues = array_map(fn($r) => (int)$r['total'], $rowsVisitas);
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20251229">
<script src="diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20251221"></script>
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
                <h3>Cargo</h3>
                <div class="bi-filter-list">
                    <?php
                        $cargoOptions = [
                            'adm' => 'Administrativo',
                            'enf' => 'Auditor Enfermeira(o)',
                            'med' => 'Auditor Medico(a)',
                        ];
                    ?>
                    <?php foreach ($cargoOptions as $key => $label): ?>
                        <?php $qs = build_query($baseQuery, ['cargo' => $key]); ?>
                        <a class="bi-filter-pill <?= $cargo === $key ? 'active' : '' ?>" href="<?= $BASE_URL ?>AuditorBI.php<?= $qs ? ('?' . $qs) : '' ?>">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bi-panel">
                <h3>Auditor</h3>
                <div class="bi-filter-list">
                    <?php foreach ($auditores as $aud): ?>
                        <?php
                            $idAud = (int)$aud['id_usuario'];
                            $qs = build_query($baseQuery, ['auditor_id' => $idAud]);
                        ?>
                        <a class="bi-filter-pill <?= $auditorId === $idAud ? 'active' : '' ?>" href="<?= $BASE_URL ?>AuditorBI.php<?= $qs ? ('?' . $qs) : '' ?>">
                            <?= e($aud['usuario_user'] ?? 'Sem nome') ?>
                        </a>
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
                    <input type="hidden" name="cargo" value="<?= e($cargo) ?>">
                    <input type="hidden" name="auditor_id" value="<?= $auditorId ? (int)$auditorId : '' ?>">
                    <div class="bi-filter-actions">
                        <button class="bi-filter-btn" type="submit">Aplicar</button>
                        <a class="bi-filter-btn" href="<?= $BASE_URL ?>AuditorBI.php">Limpar</a>
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
const auditorLabels = <?= json_encode($auditorLabels) ?>;
const contasValues = <?= json_encode($contasValues) ?>;
const glosaValues = <?= json_encode($glosaValues) ?>;
const glosaLabels = <?= json_encode($glosaLabels) ?>;
const auditadasValues = <?= json_encode($auditadasValues) ?>;
const visitasValues = <?= json_encode($visitasValues) ?>;
const visitasLabels = <?= json_encode($visitasLabels) ?>;

function barOptions() {
  return {
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: { ticks: { color: '#e8f1ff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
    }
  };
}

new Chart(document.getElementById('chartAuditorContas'), {
  type: 'bar',
  data: { labels: auditorLabels, datasets: [{ data: contasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: {
    ...barOptions(),
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: {
        ticks: {
          color: '#e8f1ff',
          callback: (value) => window.biMoneyTick ? window.biMoneyTick(value) : value
        },
        grid: { color: 'rgba(255,255,255,0.1)' }
      }
    }
  }
});

new Chart(document.getElementById('chartAuditorGlosa'), {
  type: 'bar',
  data: { labels: glosaLabels, datasets: [{ data: glosaValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: {
    ...barOptions(),
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: {
        ticks: {
          color: '#e8f1ff',
          callback: (value) => window.biMoneyTick ? window.biMoneyTick(value) : value
        },
        grid: { color: 'rgba(255,255,255,0.1)' }
      }
    }
  }
});

new Chart(document.getElementById('chartAuditorAuditadas'), {
  type: 'bar',
  data: { labels: auditorLabels, datasets: [{ data: auditadasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptions()
});

new Chart(document.getElementById('chartAuditorVisitas'), {
  type: 'bar',
  data: { labels: visitasLabels, datasets: [{ data: visitasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptions()
});
</script>

<?php require_once("templates/footer.php"); ?>
