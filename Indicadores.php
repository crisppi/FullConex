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
    WHERE {$where}
";

$sqlStats = "
    SELECT
        COUNT(DISTINCT i.id_internacao) AS total_internacoes,
        SUM(GREATEST(1, DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1)) AS total_diarias,
        MAX(GREATEST(1, DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1)) AS maior_permanencia,
        SUM(CASE WHEN i.internado_int = 's' THEN 1 ELSE 0 END) AS internados
    {$sqlBase}
";
$stmt = $conn->prepare($sqlStats);
$stmt->execute($params);
$stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$totalInternações = (int)($stats['total_internacoes'] ?? 0);
$totalDiárias = (int)($stats['total_diarias'] ?? 0);
$maiorPermanencia = (int)($stats['maior_permanencia'] ?? 0);
$internados = (int)($stats['internados'] ?? 0);
$mp = $totalInternações > 0 ? round($totalDiárias / $totalInternações, 1) : 0.0;

$sqlFlags = "
    SELECT
        SUM(CASE WHEN g.evento_adverso_ges = 's' THEN 1 ELSE 0 END) AS evento_adverso,
        SUM(CASE WHEN g.home_care_ges = 's' THEN 1 ELSE 0 END) AS home_care,
        SUM(CASE WHEN g.opme_ges = 's' THEN 1 ELSE 0 END) AS opme,
        SUM(CASE WHEN g.alto_custo_ges = 's' THEN 1 ELSE 0 END) AS alto_custo
    FROM tb_gestao g
    JOIN tb_internacao i ON i.id_internacao = g.fk_internacao_ges
    {$utiJoin}
    WHERE {$where}
";
$stmtFlags = $conn->prepare($sqlFlags);
$stmtFlags->execute($params);
$flags = $stmtFlags->fetch(PDO::FETCH_ASSOC) ?: [];

$eventoAdverso = (int)($flags['evento_adverso'] ?? 0);
$homeCare = (int)($flags['home_care'] ?? 0);
$opme = (int)($flags['opme'] ?? 0);
$altoCusto = (int)($flags['alto_custo'] ?? 0);
$obitos = 0;

function fmtPct(float $value): string
{
    return number_format($value, 1, ',', '.') . '%';
}

$idxEventoAdverso = $totalInternações > 0 ? ($eventoAdverso / $totalInternações) * 100 : 0.0;
$idxHomeCare = $totalInternações > 0 ? ($homeCare / $totalInternações) * 100 : 0.0;
$idxOpme = $totalInternações > 0 ? ($opme / $totalInternações) * 100 : 0.0;
$idxAltoCusto = $totalInternações > 0 ? ($altoCusto / $totalInternações) * 100 : 0.0;
$idxObitos = $totalInternações > 0 ? ($obitos / $totalInternações) * 100 : 0.0;
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="diversos/chartjs/Chart.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Dashboard Indicadores</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi/navegacao" title="Navegacao">
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

    <div class="bi-panel" style="margin-top:16px;">
        <div class="bi-kpis kpi-compact">
            <div class="bi-kpi kpi-berry kpi-compact"><small>Internações</small><strong><?= $totalInternações ?></strong></div>
            <div class="bi-kpi kpi-teal kpi-compact"><small>Diárias</small><strong><?= $totalDiárias ?></strong></div>
            <div class="bi-kpi kpi-indigo kpi-compact"><small>MP</small><strong><?= number_format($mp, 1, ',', '.') ?></strong></div>
            <div class="bi-kpi kpi-rose kpi-compact"><small>Maior permanencia</small><strong><?= $maiorPermanencia ?></strong></div>
        </div>
    </div>

    <div class="bi-panel" style="margin-top:16px;">
        <h3>Indicadores de performance</h3>
        <div class="bi-kpis" style="margin-top:12px;">
            <div class="bi-kpi kpi-berry"><small>Internações</small><strong><?= $totalInternações ?></strong></div>
            <div class="bi-kpi kpi-berry"><small>Internados</small><strong><?= $internados ?></strong></div>
            <div class="bi-kpi kpi-teal with-badge">
                <small>Evento adverso</small>
                <strong><?= $eventoAdverso ?></strong>
                <span class="bi-kpi-badge"><?= fmtPct($idxEventoAdverso) ?></span>
            </div>
            <div class="bi-kpi kpi-teal with-badge">
                <small>Home care</small>
                <strong><?= $homeCare ?></strong>
                <span class="bi-kpi-badge"><?= fmtPct($idxHomeCare) ?></span>
            </div>
            <div class="bi-kpi kpi-indigo with-badge">
                <small>OPME</small>
                <strong><?= $opme ?></strong>
                <span class="bi-kpi-badge"><?= fmtPct($idxOpme) ?></span>
            </div>
            <div class="bi-kpi kpi-indigo with-badge">
                <small>Alto custo</small>
                <strong><?= $altoCusto ?></strong>
                <span class="bi-kpi-badge"><?= fmtPct($idxAltoCusto) ?></span>
            </div>
            <div class="bi-kpi kpi-steel with-badge">
                <small>Óbitos</small>
                <strong><?= $obitos ?></strong>
                <span class="bi-kpi-badge"><?= fmtPct($idxObitos) ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
