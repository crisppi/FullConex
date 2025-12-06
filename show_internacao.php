<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Internação - Detalhes</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script src="js/timeout.js"></script>
</head>

<?php
include_once("check_logado.php");
include_once("globals.php");
include_once("templates/header.php");

// Models / DAOs
include_once("models/internacao.php");
require_once("dao/internacaoDao.php");
include_once("models/hospital.php");
include_once("dao/hospitalDao.php");
include_once("models/patologia.php");
include_once("dao/patologiaDao.php");
include_once("dao/pacienteDao.php");

include_once("models/prorrogacao.php");
include_once("dao/prorrogacaoDao.php");

include_once("models/visita.php");
include_once("dao/visitaDao.php");

include_once("models/tuss.php");
include_once("dao/tussDao.php");

// Negociação
if (file_exists(__DIR__ . "/models/negociacao.php")) include_once("models/negociacao.php");
if (file_exists(__DIR__ . "/dao/negociacaoDao.php")) include_once("dao/negociacaoDao.php");

// === Helpers ===
function e($v)
{
    return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8');
}
function fmtDate($s)
{
    if (empty($s) || $s === '0000-00-00') return '-';
    $ts = strtotime(substr($s, 0, 10));
    return $ts ? date("d/m/Y", $ts) : '-';
}
if (!function_exists('ymd')) {
    function ymd($s)
    {
        if (!$s) return null;
        $s = trim((string)$s);
        $s = substr($s, 0, 10);
        $ts = strtotime($s);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
function after_dash($s)
{
    $s = trim((string)$s);
    if ($s === '') return '';
    $pos = mb_strpos($s, '-');
    $out = ($pos === false) ? $s : mb_substr($s, $pos + 1);
    $out = preg_replace('/\s+/', ' ', $out);
    return trim($out);
}
if (!function_exists('fmtDateAny')) {
    function fmtDateAny($s)
    {
        $y = ymd($s);
        return $y ? date('d/m/Y', strtotime($y)) : '-';
    }
}
function initials_from_name($name)
{
    $name = trim((string)$name);
    if ($name === '') return 'PA';
    $parts = preg_split('/\s+/', $name);
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last  = mb_substr(($parts[count($parts) - 1] ?? ''), 0, 1);
    return mb_strtoupper($first . $last);
}

// === Entrada ===
$id_internacao = filter_input(INPUT_GET, "id_internacao", FILTER_SANITIZE_NUMBER_INT);
$id_internacao = $id_internacao !== null ? trim($id_internacao) : '';

$internacaoDao = new internacaoDAO($conn, $BASE_URL);

// WHERE por ID
$whereParts = [];
if ($id_internacao !== '' && ctype_digit($id_internacao)) {
    $whereParts[] = 'ac.id_internacao = ' . (int)$id_internacao;
}
$where = implode(' AND ', $whereParts);
$order = null;
$limit = 1;

$internacoes = $internacaoDao->selectAllInternacao($where, $order, $limit);
$data = $internacoes && isset($internacoes[0]) ? $internacoes[0] : null;

if (!$data) {
?>
<div class="container mt-4">
    <div class="alert alert-warning">Nenhuma internação encontrada para o parâmetro informado.</div>
    <?php include_once("diversos/backbtn_internacao.php"); ?>
</div>
<?php
    include_once("templates/footer.php");
    exit;
}

// Datas / auxiliares
$iniciais = initials_from_name($data['nome_pac'] ?? '');
$data_intern_format = fmtDate($data['data_intern_int'] ?? '');

/* =========================================================
   VISITAS
   ========================================================= */
$visitas = [];
$visitaDAO = new visitaDAO($conn, $BASE_URL);

try {
    if (method_exists($visitaDAO, 'joinVisitaInternacao')) {
        $visitas = $visitaDAO->joinVisitaInternacao((int)$id_internacao) ?: [];
    }
} catch (Throwable $e) {
    $visitas = [];
}

function pick_visit_date($row)
{
    foreach (['data_visita', 'data_visita_vis', 'data', 'data_visita_int', 'created_at'] as $k) {
        if (!empty($row[$k])) {
            $ts = strtotime(substr($row[$k], 0, 19));
            if ($ts) return date('Y-m-d', $ts);
        }
    }
    return null;
}
function pick_visit_time($row)
{
    foreach (['data_visita', 'data_visita_vis', 'data', 'data_visita_int', 'created_at'] as $k) {
        if (!empty($row[$k])) {
            $ts = strtotime(substr($row[$k], 0, 19));
            if ($ts) return date('H:i', $ts);
        }
    }
    return null;
}
function pick_visit_text($row)
{
    foreach (['rel_visita', 'rel_visita_vis', 'rel_vis', 'relatorio', 'observacao', 'obs', 'descricao'] as $k) {
        if (!empty($row[$k])) return $row[$k];
    }
    return '';
}
function pick_visit_id($row)
{
    foreach (['id_visita', 'id', 'id_vst'] as $k) {
        if (!empty($row[$k])) return (int)$row[$k];
    }
    return crc32(json_encode($row));
}
function pick_visit_acomodacao($row)
{
    $keys = ['acomodacao', 'acomodacao_int', 'acomodacao_vis', 'acomodacao_atual', 'acomod', 'acomod_int'];
    foreach ($keys as $k) {
        if (!empty($row[$k])) {
            return $row[$k];
        }
    }
    return '';
}
function pick_visit_auditor($row)
{
    foreach (['auditor_nome', 'usuario_user', 'nome_usuario', 'usuario_cadastro', 'nome'] as $k) {
        if (!empty($row[$k])) return $row[$k];
    }
    if (!empty($row['usuario_create'])) {
        $parts = explode('@', $row['usuario_create']);
        return ucfirst($parts[0]);
    }
    return '';
}

$visitas_norm = [];
foreach (($visitas ?? []) as $v) {
    $d = pick_visit_date($v);

    $nomeAuditor = pick_visit_auditor($v);
    $registro = $v['auditor_registro'] ?? $v['reg_profissional_user'] ?? '';

    if (!empty($registro) && !empty($nomeAuditor)) {
        $nomeExibicao = $nomeAuditor . ' - ' . $registro;
    } else {
        $nomeExibicao = $nomeAuditor;
    }

    $visitas_norm[] = [
        '_id'      => pick_visit_id($v),
        '_date'    => $d ?: date('Y-m-d'),
        '_time'    => pick_visit_time($v),
        '_text'    => pick_visit_text($v),
        'acomodacao' => pick_visit_acomodacao($v),
        '_auditor' => $nomeExibicao,
        'retificado' => !empty($v['retificado']) ? 1 : 0,
        '_raw'     => $v,
    ];
}
usort($visitas_norm, fn($a, $b) => strcmp($a['_date'], $b['_date']));

$recentLimitInput = filter_input(INPUT_GET, 'recent_limit', FILTER_VALIDATE_INT);
$recentLimit = ($recentLimitInput && $recentLimitInput > 0) ? min($recentLimitInput, 20) : 5;
$recentOrderInput = filter_input(INPUT_GET, 'recent_order', FILTER_SANITIZE_SPECIAL_CHARS);
$recentOrder = in_array($recentOrderInput, ['asc', 'desc'], true) ? $recentOrderInput : 'desc';

$abaParam = filter_input(INPUT_GET, 'aba', FILTER_SANITIZE_SPECIAL_CHARS);
$abaAtual = $abaParam ?: 'resumo';
$abasValidas = ['resumo', 'visitas', 'prorrog', 'tuss', 'neg'];
if (!in_array($abaAtual, $abasValidas, true)) {
    $abaAtual = 'resumo';
}
if (!$abaParam && (!empty($_GET['recent_limit']) || !empty($_GET['recent_order']))) {
    $abaAtual = 'visitas';
}

$visitas_recent = $visitas_norm;
usort($visitas_recent, function ($a, $b) use ($recentOrder) {
    $ta = strtotime(($a['_date'] ?? '1970-01-01') . ' ' . ($a['_time'] ?? '00:00')) ?: 0;
    $tb = strtotime(($b['_date'] ?? '1970-01-01') . ' ' . ($b['_time'] ?? '00:00')) ?: 0;
    return $recentOrder === 'asc' ? ($ta <=> $tb) : ($tb <=> $ta);
});
$visitas_recent = array_slice($visitas_recent, 0, $recentLimit);

$minD = $visitas_norm ? $visitas_norm[0]['_date'] : null;
$maxD = $visitas_norm ? $visitas_norm[count($visitas_norm) - 1]['_date'] : null;
$spanDays = ($minD && $maxD) ? max(1, (new DateTime($minD))->diff(new DateTime($maxD))->days) : 1;
$minLabel = $minD ? date('d/m/Y', strtotime($minD)) : '';
$maxLabel = $maxD ? date('d/m/Y', strtotime($maxD)) : '';

$countVis = count($visitas_norm);

// Visita ativa
$vid_req = filter_input(INPUT_GET, 'vid', FILTER_SANITIZE_NUMBER_INT);
if (!$vid_req) $vid_req = filter_input(INPUT_GET, 'id_visita', FILTER_SANITIZE_NUMBER_INT);

$activeVisit = null;
if ($vid_req) {
    foreach ($visitas_norm as $vn) {
        if ($vn['_id'] === (int)$vid_req) {
            $activeVisit = $vn;
            break;
        }
    }
}
if (!$activeVisit && $visitas_norm) $activeVisit = $visitas_norm[count($visitas_norm) - 1];

$activeVisitRet = $activeVisit && !empty($activeVisit['retificado']);

$initDateLabel = '—';
$initTime = '';
$initText = '—';
$initId   = null;
$initAuditor = '';

if ($activeVisit) {
    $initDateLabel = date('d/m/Y', strtotime($activeVisit['_date']));
    $initTime      = $activeVisit['_time'] ?: '';
$initText      = trim($activeVisit['_text']) !== '' ? $activeVisit['_text'] : '—';
$initId        = (int)$activeVisit['_id'];
$initAuditor   = $activeVisit['_auditor'];
}

$visitaBtnClass = $initId ? 'btn-success' : 'btn-outline-secondary';
$visitaPdfBase = $BASE_URL . 'process_visita_pdf.php?id_internacao=' . urlencode((string)$id_internacao) . '&id_visita=';
$visitaPdfHref = $initId ? $visitaPdfBase . urlencode((string)$initId) : '#';
$visitaRangePdfBase = $BASE_URL . 'process_visita_pdf.php?range=1&id_internacao=' . urlencode((string)$id_internacao);

/* =========================================================
   PRORROGAÇÕES
   ========================================================= */
$prorrogacoes = [];
if (class_exists('prorrogacaoDAO')) {
    $prDAO = new prorrogacaoDAO($conn, $BASE_URL);
    if (method_exists($prDAO, 'selectInternacaoProrrog')) {
        $prorrogacoes = $prDAO->selectInternacaoProrrog((int)$id_internacao) ?: [];
    }
}
$pr_ini_raw = filter_input(INPUT_GET, 'pr_ini', FILTER_DEFAULT) ?: '';
$pr_fim_raw = filter_input(INPUT_GET, 'pr_fim', FILTER_DEFAULT) ?: '';
$pr_ini = ymd($pr_ini_raw);
$pr_fim = ymd($pr_fim_raw);

$pr_filtered = $prorrogacoes;
if ($pr_ini || $pr_fim) {
    $pr_filtered = array_filter($prorrogacoes, function ($p) use ($pr_ini, $pr_fim) {
        $ini = ymd($p['ini'] ?? null);
        $fim = ymd($p['fim'] ?? ($p['ini'] ?? null));
        if (!$ini && !$fim) return false;
        if ($pr_ini && $pr_fim) return ($fim >= $pr_ini) && ($ini <= $pr_fim);
        if ($pr_ini) return $fim >= $pr_ini;
        if ($pr_fim) return $ini <= $pr_fim;
        return true;
    });
}
usort($pr_filtered, function ($a, $b) {
    $da = strtotime($a['fim'] ?: ($a['ini'] ?? ''));
    $db = strtotime($b['fim'] ?: ($b['ini'] ?? ''));
    return $db <=> $da;
});
$pr_total_diarias = array_reduce($pr_filtered, fn($s, $p) => $s + (int)($p['diarias'] ?? 0), 0);

/* =========================================================
   TUSS
   ========================================================= */
$tussItens = [];
if (class_exists('tussDAO')) {
    $tussDAO = new tussDAO($conn, $BASE_URL);
    if (method_exists($tussDAO, 'selectAllTUSSByIntern')) {
        $tussItens = $tussDAO->selectAllTUSSByIntern((int)$id_internacao) ?: [];
    }
}
$tuss_ini_raw = filter_input(INPUT_GET, 'tuss_ini', FILTER_DEFAULT) ?: '';
$tuss_fim_raw = filter_input(INPUT_GET, 'tuss_fim', FILTER_DEFAULT) ?: '';
$tuss_ini = ymd($tuss_ini_raw);
$tuss_fim = ymd($tuss_fim_raw);

$tuss_filtered = $tussItens;
if ($tuss_ini || $tuss_fim) {
    $tuss_filtered = array_filter($tussItens, function ($t) use ($tuss_ini, $tuss_fim) {
        $dt = ymd($t['data_realizacao_tuss'] ?? null);
        if (!$dt) return false;
        if ($tuss_ini && $tuss_fim) return ($dt >= $tuss_ini) && ($dt <= $tuss_fim);
        if ($tuss_ini) return $dt >= $tuss_ini;
        if ($tuss_fim) return $dt <= $tuss_fim;
        return true;
    });
}
usort($tuss_filtered, function ($a, $b) {
    $da = strtotime($a['data_realizacao_tuss'] ?? '');
    $db = strtotime($b['data_realizacao_tuss'] ?? '');
    return $db <=> $da;
});
$tuss_tot_solic = array_reduce($tuss_filtered, fn($s, $r) => $s + (int)($r['qtd_tuss_solicitado'] ?? 0), 0);
$tuss_tot_lib   = array_reduce($tuss_filtered, fn($s, $r) => $s + (int)($r['qtd_tuss_liberado'] ?? 0), 0);

/* =========================================================
   NEGOCIAÇÕES
   ========================================================= */
$negociacoes = [];
if (class_exists('negociacaoDAO')) {
    $negDAO = new negociacaoDAO($conn, $BASE_URL);
    if (method_exists($negDAO, 'findByInternacao')) {
        $negociacoes = $negDAO->findByInternacao((int)$id_internacao) ?: [];
    }
}
$neg_ini_raw = filter_input(INPUT_GET, 'neg_ini', FILTER_DEFAULT) ?: '';
$neg_fim_raw = filter_input(INPUT_GET, 'neg_fim', FILTER_DEFAULT) ?: '';
$neg_ini = ymd($neg_ini_raw);
$neg_fim = ymd($neg_fim_raw);

$neg_filtered = $negociacoes;
if ($neg_ini || $neg_fim) {
    $neg_filtered = array_filter($negociacoes, function ($n) use ($neg_ini, $neg_fim) {
        $ini = ymd($n['data_inicio_neg'] ?? null);
        $fim = ymd($n['data_fim_neg'] ?? null) ?: $ini;
        if (!$ini && !$fim) return false;
        if ($neg_ini && $neg_fim) return ($fim >= $neg_ini) && ($ini <= $neg_fim);
        if ($neg_ini) return $fim >= $neg_ini;
        if ($neg_fim) return $ini <= $neg_fim;
        return true;
    });
}
usort($neg_filtered, function ($a, $b) {
    $da = strtotime($a['data_fim_neg'] ?? ($a['data_inicio_neg'] ?? ''));
    $db = strtotime($b['data_fim_neg'] ?? ($b['data_inicio_neg'] ?? ''));
    return $db <=> $da;
});
?>

<div id="main-container" class="container-fluid py-3">
    <div class="v2-max mx-auto">

        <div class="card shadow-sm mb-3 header-card">
            <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <div class="d-flex gap-3 align-items-center">
                    <div class="v2-avatar"><?= e($iniciais) ?></div>
                    <div>
                        <h4 class="mb-1"><?= e(mb_strtoupper($data['nome_pac'] ?? '-')) ?></h4>
                        <div class="d-flex flex-wrap gap-2 text-secondary small">
                            <span><i class="fa-solid fa-hospital me-1"></i><?= e($data['nome_hosp'] ?? '-') ?></span>
                            <span>•</span>
                            <span><i class="fa-solid fa-bed-pulse me-1"></i>Internação
                                <?= e($data['id_internacao'] ?? '-') ?></span>
                            <span>•</span>
                            <span><i class="fa-regular fa-calendar me-1"></i>Data da internação:
                                <?= e($data_intern_format) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <ul class="nav nav-pills mb-3" id="internTabs" role="tablist"
                    style="--bs-nav-pills-link-active-bg:#5e2363; --bs-nav-pills-link-active-color:#fff; --bs-nav-link-color:#5e2363; --bs-nav-link-hover-color:#5e2363;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link<?= $abaAtual === 'resumo' ? ' active' : '' ?>" id="resumo-tab"
                            data-bs-toggle="pill" data-bs-target="#resumo" type="button" role="tab">
                            <i class="fa-solid fa-bars me-2"></i>Resumo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link<?= $abaAtual === 'visitas' ? ' active' : '' ?>" id="visitas-tab"
                            data-bs-toggle="pill" data-bs-target="#visitas" type="button" role="tab">
                            <i class="fa-solid fa-stethoscope me-2"></i>Visitas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link<?= $abaAtual === 'prorrog' ? ' active' : '' ?>" id="prorrog-tab"
                            data-bs-toggle="pill" data-bs-target="#prorrog" type="button" role="tab">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>Prorrogações
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link<?= $abaAtual === 'tuss' ? ' active' : '' ?>" id="tuss-tab"
                            data-bs-toggle="pill" data-bs-target="#tuss" type="button" role="tab">
                            <i class="fa-solid fa-list-check me-2"></i>TUSS
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link<?= $abaAtual === 'neg' ? ' active' : '' ?>" id="neg-tab"
                            data-bs-toggle="pill" data-bs-target="#neg" type="button" role="tab">
                            <i class="fa-solid fa-handshake me-2"></i>Negociações
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="internTabsContent">
                    <div class="tab-pane fade<?= $abaAtual === 'resumo' ? ' show active' : '' ?>" id="resumo"
                        role="tabpanel" aria-labelledby="resumo-tab">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <div class="card ov-card ov-int"
                                    style="border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.06);background-image:linear-gradient(to right, var(--ov, #5e2363) 6px, #fff 6px);">
                                    <div class="card-body">
                                        <div class="ov-head">
                                            <div class="ov-icon"><i class="fa-solid fa-bed-pulse"></i></div>
                                            <h6 class="ov-title mb-0">Internação</h6>
                                        </div>
                                        <dl class="details-dl">
                                            <dt>Código</dt>
                                            <dd><?= e($data['id_internacao'] ?? '-') ?></dd>
                                            <dt>Senha</dt>
                                            <dd><?= e($data['senha_int'] ?? '-') ?></dd>
                                            <dt>Acomodação</dt>
                                            <dd><?= e($data['acomodacao_int'] ?? '—') ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="card ov-card ov-vis"
                                    style="border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.06);background-image:linear-gradient(to right, var(--ov, #0f766e) 6px, #fff 6px);">
                                    <div class="card-body">
                                        <div class="ov-head">
                                            <div class="ov-icon"><i class="fa-solid fa-user-nurse"></i></div>
                                            <h6 class="ov-title mb-0">Detalhes</h6>
                                        </div>
                                        <dl class="details-dl">
                                            <dt>Tipo admissão</dt>
                                            <dd><?= e($data['tipo_admissao_int'] ?? '-') ?></dd>
                                            <dt>Modo Internação</dt>
                                            <dd><?= e($data['modo_internacao_int'] ?? '-') ?></dd>
                                            <dt>Especialidade</dt>
                                            <dd><?= e($data['especialidade_int'] ?? '-') ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <div class="card ov-card ov-int"
                                    style="border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.06);background-image:linear-gradient(to right, var(--ov, #5e2363) 6px, #fff 6px);">
                                    <div class="card-body">
                                        <div class="ov-head">
                                            <h6 class="ov-title mb-0">Relatório Internação</h6>
                                        </div>
                                        <div class="v2-relatorio"><?= nl2br(e($data['rel_int'] ?? '-')) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade<?= $abaAtual === 'visitas' ? ' show active' : '' ?>" id="visitas"
                        role="tabpanel" aria-labelledby="visitas-tab">
                        <?php if (!$visitas_norm): ?>
                        <p class="text-muted mb-0">Nenhuma visita registrada para esta internação.</p>
                        <?php else: ?>
                        <div class="card ov-card ov-int"
                            style="border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.06);background-image:linear-gradient(to right, var(--ov, #5e2363) 6px, #fff 6px);">
                            <div class="card-body">
                                <div
                                    class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center ov-head gap-2">
                                    <div>
                                        <h6 class="ov-title mb-0">Período das visitas</h6>
                                        <?php if ($minLabel && $maxLabel): ?>
                                        <div class="small text-muted" id="vis-periodo-resumo"><?= e($minLabel) ?> —
                                            <?= e($maxLabel) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-secondary text-nowrap" id="vis-periodo-selecionado"
                                        style="display:none;">
                                        <strong>Período selecionado:</strong>
                                        <span id="vis-periodo-range"></span>
                                    </div>
                                </div>

                                <?php if ($minD && $maxD && $countVis > 0): ?>
                                <div class="mb-3">
                                    <form id="formFiltroVisitas" class="row g-2 align-items-end">
                                        <div class="col-sm-4 col-md-3">
                                            <label class="form-label small text-muted">Data inicial</label>
                                            <input type="date" id="vis_ini" class="form-control form-control-sm"
                                                value="<?= e($minD) ?>" data-default="<?= e($minD) ?>">
                                        </div>
                                        <div class="col-sm-4 col-md-3">
                                            <label class="form-label small text-muted">Data final</label>
                                            <input type="date" id="vis_fim" class="form-control form-control-sm"
                                                value="<?= e($maxD) ?>" data-default="<?= e($maxD) ?>">
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" id="btnAplicarVisitas" class="btn btn-sm btn-primary"
                                                style="background:#5e2363;border-color:#5e2363;">
                                                Aplicar
                                            </button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" id="btnLimparVisitas"
                                                class="btn btn-sm btn-outline-secondary">
                                                Limpar
                                            </button>
                                        </div>
                                    </form>
                                    <div class="small text-muted mt-2">
                                        As visitas fora do intervalo selecionado são escondidas na linha do tempo.
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php
                                $spanForWidth = max(1, $spanDays);
                                $timelineMarginPct = 3;
                                $trackWidthPx = max(800, $countVis * 160, $spanForWidth * 40);
                                $labelMinDistancePx = 120;
                                $markerPaddingPx = 40;
                                $offsetStepPx = 26;
                                $markerPositions = [];
                                ?>
                                <div class="ht-container">
                                    <div class="ht-track" style="width: <?= (int)$trackWidthPx ?>px">
                                        <div class="ht-bar"></div>
                                        <?php foreach ($visitas_norm as $i => $v):
                                                $daysFromMin = max(0, (new DateTime($minD ?: $v['_date']))->diff(new DateTime($v['_date']))->days);
                                                $usablePctRange = 100 - ($timelineMarginPct * 2);
                                                if ($spanDays > 0) {
                                                    $pct = $timelineMarginPct + (($daysFromMin / $spanDays) * $usablePctRange);
                                                } elseif ($countVis > 1) {
                                                    $pct = $timelineMarginPct + (($i / max(1, $countVis - 1)) * $usablePctRange);
                                                } else {
                                                    $pct = 50;
                                                }
                                                $pct = round(max($timelineMarginPct, min(100 - $timelineMarginPct, $pct)), 2);
                                                $leftPx = ($pct / 100) * $trackWidthPx;
                                                $finalPx = $leftPx;
                                                $maxAttempts = 12;
                                                for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                                                    $hasOverlap = false;
                                                    foreach ($markerPositions as $registeredPos) {
                                                        if (abs($finalPx - $registeredPos) < $labelMinDistancePx) {
                                                            $hasOverlap = true;
                                                            break;
                                                        }
                                                    }
                                                    if (!$hasOverlap) {
                                                        break;
                                                    }
                                                    $direction = ($attempt % 2 === 0) ? 1 : -1;
                                                    $steps = (int)ceil(($attempt + 1) / 2);
                                                    $shift = $direction * $steps * $offsetStepPx;
                                                    $finalPx = max($markerPaddingPx, min($trackWidthPx - $markerPaddingPx, $leftPx + $shift));
                                                }
                                                $markerPositions[] = $finalPx;
                                                $pctPosition = round(($finalPx / $trackWidthPx) * 100, 2);
                                                $isActive  = ($activeVisit && $activeVisit['_id'] === $v['_id']);
                                                $dataLabel = date('d/m/Y', strtotime($v['_date']));
                                                $hora      = $v['_time'] ?: '';
                                                $texto     = trim($v['_text']) !== '' ? $v['_text'] : '—';
                                                $auditorNome = $v['_auditor'] ?? '';
                                            ?>
                                        <a class="ht-marker<?= $isActive ? ' active' : '' ?>" href="#"
                                            style="left: <?= $pctPosition ?>%;" data-dateraw="<?= e($v['_date']) ?>"
                                            data-id="<?= (int)$v['_id'] ?>" data-date="<?= e($dataLabel) ?>"
                                            data-time="<?= e($hora) ?>" data-text="<?= e($texto) ?>"
                                            data-auditor="<?= e($auditorNome) ?>" data-retificado="<?= !empty($v['retificado']) ? '1' : '0' ?>"
                                            onclick="(function(m){
                                              document.querySelectorAll('#visitas .ht-marker.active').forEach(function(x){x.classList.remove('active');});
                                              m.classList.add('active');
                                              var d=m.dataset.date||'—', t=m.dataset.time||'', x=m.dataset.text||'—', i=m.dataset.id||'', aud=m.dataset.auditor||'';
                                              var dEl=document.getElementById('v-rel-date');
                                              var tWrap=document.getElementById('v-rel-time-wrap');
                                              var tEl=document.getElementById('v-rel-time');
                                              var xEl=document.getElementById('v-rel-text');
                                              var iWrap=document.getElementById('v-rel-id-wrap');
                                              var iEl=document.getElementById('v-rel-id');
                                              var audEl=document.getElementById('v-rel-auditor');
                                              var audWrap=document.getElementById('v-rel-auditor-wrap');

                                              if(dEl) dEl.textContent=d;
                                              if(tWrap) tWrap.style.display = t ? '' : 'none';
                                              if(tEl) tEl.textContent = t || '';
                                              if(xEl) xEl.textContent = x;
                                              if(iEl) iEl.textContent = i || '';
                                              if(iWrap){ if(i){ iWrap.classList.remove('d-none'); } else { iWrap.classList.add('d-none'); } }
                                              
                                              if(audEl) audEl.textContent = aud;
                                              if(audWrap) audWrap.style.display = aud ? 'block' : 'none';
                                              if(window.updateVisitaDeleteTarget){ window.updateVisitaDeleteTarget(i, m.dataset.retificado); }
                                              var pdfBtn=document.getElementById('btn-visita-pdf');
                                              if(pdfBtn){
                                                var base=pdfBtn.getAttribute('data-pdf-base')||'';
                                                if(i && base){
                                                  pdfBtn.href=base + encodeURIComponent(i);
                                                  pdfBtn.classList.remove('disabled');
                                                  pdfBtn.classList.remove('btn-outline-secondary');
                                                  pdfBtn.classList.add('btn-success');
                                                  pdfBtn.setAttribute('aria-disabled','false');
                                                }else{
                                                  pdfBtn.href='#';
                                                  pdfBtn.classList.add('disabled');
                                                  pdfBtn.classList.remove('btn-success');
                                                  pdfBtn.classList.add('btn-outline-secondary');
                                                  pdfBtn.setAttribute('aria-disabled','true');
                                                }
                                                var pdfDate=document.getElementById('btn-visita-date');
                                                if(pdfDate){
                                                  if(i && d){
                                                    pdfDate.textContent='Data: ' + d;
                                                    pdfDate.classList.remove('text-muted');
                                                  }else{
                                                    pdfDate.textContent='Selecione uma visita';
                                                    if(!pdfDate.classList.contains('text-muted')) pdfDate.classList.add('text-muted');
                                                  }
                                                }
                                              }

                                              var cont=document.querySelector('#visitas .ht-container');
                                              if(cont){ cont.scrollLeft = Math.max(0, m.offsetLeft - cont.clientWidth/2); }
                                          })(this); return false;">
                                            <span class="ht-label"><?= e($dataLabel) ?></span>
                                            <span class="ht-dot"></span>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <div class="d-flex flex-wrap gap-2 align-items-stretch">
                                            <?php if (!empty($visitas_norm)): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-ultimas-visitas"
                                                data-bs-toggle="modal" data-bs-target="#modalUltimasVisitas">
                                                <i class="fa-solid fa-clock-rotate-left me-1"></i>
                                                Últimas visitas
                                            </button>
                                            <?php endif; ?>
                                            <a id="btn-visita-pdf"
                                                class="btn btn-sm <?= e($visitaBtnClass) ?><?= $initId ? '' : ' disabled' ?>"
                                                data-pdf-base="<?= e($visitaPdfBase) ?>"
                                                href="<?= e($visitaPdfHref) ?>"
                                                target="_blank" rel="noopener"
                                                aria-disabled="<?= $initId ? 'false' : 'true' ?>">
                                                <i class="fa-solid fa-file-pdf me-1"></i> Baixar PDF
                                                <span id="btn-visita-date"
                                                    class="d-block small mt-1 text-start<?= $initId ? '' : ' text-muted' ?>">
                                                    <?= e($initId ? 'Data: ' . $initDateLabel : 'Selecione uma visita') ?>
                                                </span>
                                            </a>
                                            <a id="btn-visitas-range-pdf"
                                                class="btn btn-sm btn-outline-primary disabled"
                                                data-base="<?= e($visitaRangePdfBase) ?>" href="#"
                                                target="_blank" rel="noopener"
                                                aria-disabled="true">
                                                <i class="fa-solid fa-file-pdf me-1"></i> PDF (período)
                                                <span id="btn-visitas-range-info"
                                                    class="d-block small mt-1 text-start text-muted">
                                                    Use o filtro de datas
                                                </span>
                                            </a>
                                        </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($visitas_norm)): ?>
                                    <?php $disableDeleteBtn = ($countVis <= 1) || !$initId || $activeVisitRet; ?>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" id="btn-visita-delete-main"
                                            class="btn btn-sm btn-outline-danger<?= $disableDeleteBtn ? ' disabled' : '' ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalDeleteVisitaInternacao"
                                            data-delete-visita="<?= $initId ? e($initId) : '' ?>"
                                            aria-disabled="<?= $disableDeleteBtn ? 'true' : 'false' ?>"
                                            <?= $disableDeleteBtn ? 'disabled' : '' ?>>
                                            <i class="fa-solid fa-trash-can me-1"></i> Remover visita selecionada
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                    <div class="border-top mt-3 mb-3"></div>
                                    <div class="p-3 rounded-4 shadow-sm" style="background:#f9f9fb;border:1px solid #e0e3ea;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-secondary fw-semibold">Relatório da visita:
                            <span id="v-rel-date" class="text-dark"><?= e($initDateLabel) ?></span>
                        </h6>
                        <span id="v-rel-id-wrap"
                            class="badge bg-secondary-subtle text-secondary-emphasis<?= $initId ? '' : ' d-none' ?>">
                            ID <span id="v-rel-id"><?= e($initId ?: '') ?></span>
                        </span>
                    </div>
                    <div class="mt-3 p-3 rounded bg-white border" style="border-color:#e0e3ea;">
                        <div class="v2-relatorio" id="v-rel-text" style="white-space:pre-wrap">
                                                <?= e($initText) ?></div>
                                    </div>
                                    </div>

                                    <div id="v-rel-auditor-wrap"
                                        style="font-size: 0.85rem; color: #5e2363; font-weight: 600; margin-top: 10px; display: <?= !empty($initAuditor) ? 'block' : 'none' ?>;">
                                        <i class="fa-solid fa-user-doctor" style="margin-right: 5px;"></i> Visita
                                        realizada pelo(a) Auditor(a):
                                        <span id="v-rel-auditor"><?= e($initAuditor) ?></span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <!-- <div class="small text-secondary" id="vis-periodo-footer">

                                        </div> -->
                                        <div class="small"><span class="legend-dot"></span> Clique nas datas para
                                            visualizar o
                                            relatório</div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($visitas_recent)): ?>
                            <div class="mt-4 p-3 rounded-4 shadow-sm border" style="border-color:#e0e3ea;background:#f9f9fb;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <h6 class="text-uppercase small fw-semibold text-muted mb-0">
                                        Últimas <?= e($recentLimit) ?> visitas registradas
                                    </h6>
                                    <form class="d-flex flex-wrap align-items-center gap-2" method="get"
                                        action="<?= e($_SERVER['PHP_SELF']) ?>#visitas">
                                        <input type="hidden" name="id_internacao" value="<?= (int) $id_internacao ?>">
                                        <input type="hidden" name="aba" value="visitas">
                                        <?php if (!empty($vid_req)): ?>
                                        <input type="hidden" name="vid" value="<?= (int) $vid_req ?>">
                                        <?php endif; ?>
                                        <label class="small text-muted mb-0">Qtd
                                            <select name="recent_limit" class="form-select form-select-sm d-inline-block"
                                                style="width:auto;">
                                                <?php foreach ([3,5,10,15,20] as $opt): ?>
                                                <option value="<?= $opt ?>" <?= $recentLimit == $opt ? 'selected' : '' ?>>
                                                    <?= $opt ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label class="small text-muted mb-0">Ordem
                                            <select name="recent_order" class="form-select form-select-sm d-inline-block"
                                                style="width:auto;">
                                                <option value="desc" <?= $recentOrder === 'desc' ? 'selected' : '' ?>>Recente
                                                </option>
                                                <option value="asc" <?= $recentOrder === 'asc' ? 'selected' : '' ?>>Antiga
                                                </option>
                                            </select>
                                        </label>
                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Aplicar</button>
                                    </form>
                                </div>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($visitas_recent as $recent):
                                        $recentDate = $recent['_date'] ? date('d/m/Y', strtotime($recent['_date'])) : '—';
                                        $recentTime = trim((string)($recent['_time'] ?? ''));
                                        $recentText = trim((string)($recent['_text'] ?? ''));
                                        $recentAud  = trim((string)($recent['_auditor'] ?? ''));
                                        $recentId   = $recent['_id'] ?? ($recent['_raw']['id_visita'] ?? null);
                                        ?>
                                    <div class="p-3 rounded-3 bg-white border" style="border-color:#e0e3ea;">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="text-secondary fw-semibold">
                                                Relatório da visita:
                                                <span class="text-dark"><?= e($recentDate) ?></span>
                                            </div>
                                            <?php if ($recentId): ?>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                ID <?= e($recentId) ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($recentAud): ?>
                                        <div class="mt-2 small text-muted">
                                            <i class="fa-solid fa-user-doctor me-1"></i>
                                            <?= e($recentAud) ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="mt-3 p-3 rounded bg-light border" style="border-color:#e0e3ea;">
                                            <div class="small text-muted text-uppercase mb-1">Evolução</div>
                                            <p class="mb-0"><?= nl2br(e($recentText !== '' ? $recentText : '-')) ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                        <?php endif; ?>
<?php if (!empty($visitas_norm)): ?>
<div class="modal fade" id="modalDeleteVisitaInternacao" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Remover visita</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Deseja realmente deletar esta visita? Essa ação apenas desativa o registro.</p>
                                        <div class="alert alert-danger d-none js-delete-feedback" role="alert"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="button" class="btn btn-danger" data-action="confirm-delete">Remover</button>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php endif; ?>
</div>
<?php if (!empty($_GET['recent_limit']) || !empty($_GET['recent_order'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash !== '#visitas') {
        window.location.hash = 'visitas';
    }
});
</script>
<?php endif; ?>
                    <?php if (!empty($visitas_recent)): ?>
                    <div class="modal fade modal-ultimas-visitas" id="modalUltimasVisitas" tabindex="-1"
                        aria-labelledby="modalUltimasVisitasLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <div>
                                        <h5 class="modal-title" id="modalUltimasVisitasLabel">Últimas visitas</h5>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="ult-vis-header text-uppercase small fw-semibold text-muted mb-3">
                                        <div>Data da visita</div>
                                        <div>Evolução</div>
                                        <div>Profissional</div>
                                    </div>
                                    <div class="visita-list">
                                        <?php foreach ($visitas_recent as $vis):
                                            $d = $vis['_date'] ? date('d/m/Y', strtotime($vis['_date'])) : '-';
                                            $hora = $vis['_time'] ?: '';
                                            $relatorio = trim((string)($vis['_text'] ?? ''));
                                            $idVis = $vis['id_visita'] ?? ($vis['_raw']['id_visita'] ?? null);
                                            ?>
                                        <div class="visita-item rounded-4 shadow-sm mb-3 p-3"
                                            style="border:1px solid #e0e3ea;background:#f9f9fb;">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                <div class="text-secondary fw-semibold">
                                                    Relatório da visita:
                                                    <span class="text-dark"><?= e($d) ?></span>
                                                </div>
                                                <?php if ($idVis): ?>
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                    ID <?= e($idVis) ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-2">
                                                <span class="small text-muted">Profissional:</span>
                                                <strong><?= e($vis['_auditor'] ?: '-') ?></strong>
                                            </div>
                                            <div class="mt-3 p-3 rounded bg-white border" style="border-color:#e0e3ea;">
                                                <span class="small text-muted d-block mb-1">Evolução</span>
                                                <p class="mb-0"><?= nl2br(e($relatorio !== '' ? $relatorio : '-')) ?></p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="tab-pane fade<?= $abaAtual === 'prorrog' ? ' show active' : '' ?>" id="prorrog"
                        role="tabpanel" aria-labelledby="prorrog-tab">
                        <div class="card ov-card ov-int"
                            style="border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.06);background-image:linear-gradient(to right, var(--ov, #5e2363) 6px, #fff 6px);">
                            <div class="card-body">
                                <div class="ov-head">
                                    <h6 class="ov-title mb-0">Prorrogações</h6>
                                </div>
                                <form method="get" action="<?= e($_SERVER['PHP_SELF']) ?>#prorrog"
                                    class="row g-2 align-items-end mb-3">
                                    <input type="hidden" name="id_internacao" value="<?= e($id_internacao) ?>">
                                    <div class="col-sm-4 col-md-3">
                                        <label class="form-label small text-muted">Início</label>
                                        <input type="date" name="pr_ini" value="<?= e($pr_ini ?? $pr_ini_raw) ?>"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-sm-4 col-md-3">
                                        <label class="form-label small text-muted">Fim</label>
                                        <input type="date" name="pr_fim" value="<?= e($pr_fim ?? $pr_fim_raw) ?>"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-auto"><button class="btn btn-sm btn-primary"
                                            style="background:#5e2363;border-color:#5e2363;">Filtrar</button></div>
                                    <div class="col-auto"><a class="btn btn-sm btn-outline-secondary"
                                            href="<?= e($_SERVER['PHP_SELF']) . '?id_internacao=' . urlencode($id_internacao) ?>#prorrog">Limpar</a>
                                    </div>
                                </form>

                                <?php if (!empty($pr_filtered)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-2">
                                        <tbody>
                                            <tr class="table-light text-uppercase small fw-semibold">
                                                <td>Acomodação</td>
                                                <td>Período</td>
                                                <td class="text-center">Diárias</td>
                                                <td class="text-center">Isolamento</td>
                                            </tr>
                                            <?php foreach ($pr_filtered as $p):
                                                    $acom = e(after_dash($p['acomod'] ?? '-'));
                                                    $ini  = fmtDate($p['ini'] ?? '');
                                                    $fim  = fmtDate($p['fim'] ?? '');
                                                    $periodo = ($ini !== '-' || $fim !== '-') ? ($ini . ' — ' . $fim) : '-';
                                                    $dias = (int)($p['diarias'] ?? 0);
                                                    $isoRaw = strtolower((string)($p['isolamento'] ?? $p['isol_1_pror'] ?? ''));
                                                    $iso = ($isoRaw === 's' || $isoRaw === 'sim' || $isoRaw === '1') ? 'Sim' : 'Não';
                                                ?>
                                            <tr>
                                                <td><?= $acom ?></td>
                                                <td><?= $periodo ?></td>
                                                <td class="text-center"><?= $dias ?></td>
                                                <td class="text-center">
                                                    <?= $iso === 'Sim' ? '<span class="badge rounded-pill text-bg-danger">Sim</span>' : '<span class="badge rounded-pill text-bg-secondary">Não</span>' ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end fw-semibold">Total de diárias <?= (int)$pr_total_diarias ?></div>
                                <?php else: ?>
                                <div class="text-muted">Nenhuma
                                    prorrogação<?= ($pr_ini || $pr_fim) ? ' no período selecionado.' : ' registrada para esta internação.' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade<?= $abaAtual === 'tuss' ? ' show active' : '' ?>" id="tuss"
                        role="tabpanel" aria-labelledby="tuss-tab">
                        <div class="card ov-card ov-int"
                            style="border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.06);background-image:linear-gradient(to right, var(--ov, #5e2363) 6px, #fff 6px);">
                            <div class="card-body">
                                <div class="ov-head">
                                    <h6 class="ov-title mb-0">TUSS</h6>
                                </div>
                                <form method="get" action="<?= e($_SERVER['PHP_SELF']) ?>#tuss"
                                    class="row g-2 align-items-end mb-3">
                                    <input type="hidden" name="id_internacao" value="<?= e($id_internacao) ?>">
                                    <div class="col-sm-4 col-md-3">
                                        <label class="form-label small text-muted">Realização - Início</label>
                                        <input type="date" name="tuss_ini" value="<?= e($tuss_ini ?? $tuss_ini_raw) ?>"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-sm-4 col-md-3">
                                        <label class="form-label small text-muted">Realização - Fim</label>
                                        <input type="date" name="tuss_fim" value="<?= e($tuss_fim ?? $tuss_fim_raw) ?>"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-auto"><button class="btn btn-sm btn-primary"
                                            style="background:#5e2363;border-color:#5e2363;">Filtrar</button></div>
                                    <div class="col-auto"><a class="btn btn-sm btn-outline-secondary"
                                            href="<?= e($_SERVER['PHP_SELF']) . '?id_internacao=' . urlencode($id_internacao) ?>#tuss">Limpar</a>
                                    </div>
                                </form>

                                <?php if (!empty($tuss_filtered)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-2">
                                        <tbody>
                                            <tr class="table-light text-uppercase small fw-semibold">
                                                <td style="min-width:110px;">Código</td>
                                                <td>Terminologia</td>
                                                <td style="min-width:120px;">Realização</td>
                                                <td class="text-center" style="min-width:120px;">Solicitado</td>
                                                <td class="text-center" style="min-width:120px;">Liberado</td>
                                                <td class="text-center" style="min-width:110px;">Status</td>
                                            </tr>
                                            <?php foreach ($tuss_filtered as $t):
                                                    $cod = e($t['tuss_solicitado'] ?? '-');
                                                    $term = e($t['terminologia_tuss'] ?? '-');
                                                    $dt = fmtDateAny($t['data_realizacao_tuss'] ?? '');
                                                    $qsol = (int)($t['qtd_tuss_solicitado'] ?? 0);
                                                    $qlib = (int)($t['qtd_tuss_liberado'] ?? 0);
                                                    $libRaw = strtolower((string)($t['tuss_liberado_sn'] ?? ''));
                                                    $status = ($libRaw === 's' || $libRaw === 'sim' || $libRaw === '1') ? 'Liberado' : 'Pendente';
                                                    $badge = ($status === 'Liberado') ? 'text-bg-success' : 'text-bg-secondary';
                                                ?>
                                            <tr>
                                                <td class="fw-semibold"><?= $cod ?></td>
                                                <td><?= $term ?></td>
                                                <td><?= $dt ?></td>
                                                <td class="text-center"><?= $qsol ?></td>
                                                <td class="text-center"><?= $qlib ?></td>
                                                <td class="text-center"><span
                                                        class="badge rounded-pill <?= $badge ?>"><?= $status ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end gap-3">
                                    <div><span class="text-muted">Total solicitado:</span>
                                        <strong><?= (int)$tuss_tot_solic ?></strong>
                                    </div>
                                    <div><span class="text-muted">Total liberado:</span>
                                        <strong><?= (int)$tuss_tot_lib ?></strong>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="text-muted">Nenhum item
                                    TUSS<?= ($tuss_ini || $tuss_fim) ? ' no período selecionado.' : ' para esta internação.' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade<?= $abaAtual === 'neg' ? ' show active' : '' ?>" id="neg"
                        role="tabpanel" aria-labelledby="neg-tab">
                        <div class="card ov-card ov-int"
                            style="border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.06);background-image:linear-gradient(to right, var(--ov, #5e2363) 6px, #fff 6px);">
                            <div class="card-body">
                                <div class="ov-head">
                                    <h6 class="ov-title mb-0">Negociações</h6>
                                </div>
                                <form method="get" action="<?= e($_SERVER['PHP_SELF']) ?>#neg"
                                    class="row g-2 align-items-end mb-3">
                                    <input type="hidden" name="id_internacao" value="<?= e($id_internacao) ?>">
                                    <div class="col-sm-4 col-md-3">
                                        <label class="form-label small text-muted">Início</label>
                                        <input type="date" name="neg_ini" value="<?= e($neg_ini ?? $neg_ini_raw) ?>"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-sm-4 col-md-3">
                                        <label class="form-label small text-muted">Fim</label>
                                        <input type="date" name="neg_fim" value="<?= e($neg_fim ?? $neg_fim_raw) ?>"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-auto"><button class="btn btn-sm btn-primary"
                                            style="background:#5e2363;border-color:#5e2363;">Filtrar</button></div>
                                    <div class="col-auto"><a class="btn btn-sm btn-outline-secondary"
                                            href="<?= e($_SERVER['PHP_SELF']) . '?id_internacao=' . urlencode($id_internacao) ?>#neg">Limpar</a>
                                    </div>
                                </form>

                                <?php if (!empty($neg_filtered)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-2">
                                        <tbody>
                                            <tr class="table-light text-uppercase small fw-semibold">
                                                <td style="min-width:140px;">Tipo</td>
                                                <td>Troca</td>
                                                <td class="text-center" style="min-width:90px;">Qtd</td>
                                                <td class="text-center" style="min-width:110px;">Saving</td>
                                                <td style="min-width:190px;">Período</td>
                                                <td style="min-width:150px;">Atualizado</td>
                                            </tr>
                                            <?php foreach ($neg_filtered as $n):
                                                    $tipo = e($n['tipo_negociacao'] ?? '-');
                                                    $de   = e(after_dash($n['troca_de'] ?? '-'));
                                                    $para = e(after_dash($n['troca_para'] ?? '-'));
                                                    $qtd = e($n['qtd'] ?? '-');
                                                    $saving = e($n['saving'] ?? '-');
                                                    $ini = fmtDateAny($n['data_inicio_neg'] ?? '');
                                                    $fim = fmtDateAny($n['data_fim_neg'] ?? '');
                                                    $periodo = ($ini !== '-' || $fim !== '-') ? ($ini . ' — ' . $fim) : '-';
                                                    $upd = e($n['updated_at'] ?? '');
                                                    $updFmt = ($upd) ? date('d/m/Y H:i', strtotime($upd)) : '-';
                                                ?>
                                            <tr>
                                                <td class="fw-semibold"><?= $tipo ?></td>
                                                <td><?= $de ?> <i
                                                        class="fa-solid fa-arrow-right-arrow-left mx-1 text-muted"></i>
                                                    <?= $para ?></td>
                                                <td class="text-center"><?= $qtd ?></td>
                                                <td class="text-center"><?= $saving ?></td>
                                                <td><?= $periodo ?></td>
                                                <td><?= $updFmt ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-muted">Nenhuma
                                    negociação<?= ($neg_ini || $neg_fim) ? ' no período selecionado.' : ' para esta internação.' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted">Atualizado: <?= e(date('d/m/Y H:i')) ?></div>

                    <div class="d-flex gap-2">
                        <button type="button"
                            class="btn btn-sm rounded-pill text-white shadow-sm d-inline-flex align-items-center"
                            style="background-color: #5e2363; border-color: #5e2363;"
                            onclick="window.location.href='<?= $BASE_URL ?>cad_visita.php?id_internacao=<?= $id_internacao ?>'">
                            <i class="fa-solid fa-plus me-2"></i>Nova Visita
                        </button>

                        <a href="<?= !empty($_SERVER['HTTP_REFERER']) ? 'javascript:history.back()' : $BASE_URL . 'list_intenacao.php' ?>"
                            class="btn btn-ghost-brand btn-sm rounded-pill shadow-sm d-inline-flex align-items-center">
                            <i class="fa-solid fa-arrow-left me-2"></i>Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
    if (window.setupInternacaoTabs) window.setupInternacaoTabs();
    if (window.setupVisitasFilter) window.setupVisitasFilter();
    </script>

<?php if (!empty($visitas_norm)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var totalVisitas = <?= (int)$countVis ?>;
    var deleteBtn = document.getElementById('btn-visita-delete-main');
    var modal = document.getElementById('modalDeleteVisitaInternacao');
    var confirmBtn = modal ? modal.querySelector('[data-action="confirm-delete"]') : null;
    var feedback = modal ? modal.querySelector('.js-delete-feedback') : null;
    var currentId = <?= $initId ? (int)$initId : 'null' ?>;
    var currentRet = <?= $activeVisitRet ? 'true' : 'false' ?>;
    var redirectUrl = <?= json_encode($BASE_URL . 'show_internacao.php?id_internacao=' . (int)$id_internacao . '#visitas') ?>;

    function updateDeleteBtn() {
        if (!deleteBtn) return;
        var disabled = !currentId || totalVisitas <= 1 || currentRet;
        deleteBtn.disabled = disabled;
        deleteBtn.classList.toggle('disabled', disabled);
        deleteBtn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
        if (currentId) {
            deleteBtn.setAttribute('data-delete-visita', currentId);
        }
    }

    window.updateVisitaDeleteTarget = function(id, retFlag) {
        currentId = id ? parseInt(id, 10) : null;
        currentRet = (retFlag === true || retFlag === '1' || retFlag === 1);
        updateDeleteBtn();
    };

    updateDeleteBtn();

    if (!modal || !confirmBtn) return;

    modal.addEventListener('show.bs.modal', function(event) {
        if (!deleteBtn || deleteBtn.disabled) {
            event.preventDefault();
            return;
        }
        if (feedback) {
            feedback.classList.add('d-none');
            feedback.textContent = '';
        }
    });

    confirmBtn.addEventListener('click', function() {
        if (!currentId) return;
        confirmBtn.disabled = true;
        if (feedback) {
            feedback.classList.add('d-none');
            feedback.textContent = '';
        }

        var formData = new FormData();
        formData.append('type', 'delete');
        formData.append('id_visita', currentId);
        formData.append('redirect', redirectUrl);
        formData.append('ajax', '1');

        fetch('process_visita.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function(resp) { return resp.json(); })
            .then(function(res) {
                if (res && res.success) {
                    var target = res.redirect || redirectUrl || window.location.href;
                    try {
                        var absolute = new URL(target, window.location.origin).href;
                        if (absolute === window.location.href) {
                            window.location.reload();
                        } else {
                            window.location.href = target;
                        }
                    } catch (err) {
                        window.location.reload();
                    }
                    return;
                }
                var msg = (res && res.message) ? res.message : 'Não foi possível remover a visita.';
                if (feedback) {
                    feedback.textContent = msg;
                    feedback.classList.remove('d-none');
                } else {
                    alert(msg);
                }
            })
            .catch(function() {
                if (feedback) {
                    feedback.textContent = 'Falha inesperada ao remover a visita.';
                    feedback.classList.remove('d-none');
                } else {
                    alert('Falha inesperada ao remover a visita.');
                }
            })
            .finally(function() {
                confirmBtn.disabled = false;
            });
    });
});
</script>
<?php endif; ?>
<style>
    :root {
        --brand: #5e2363;
        --brand-700: #4b1c50;
        --brand-800: #431945;
        --brand-100: #f2e8f7;
        --brand-050: #f9f3fc;
        --teal: #0f766e;
        --teal-100: #d1fae5;
        --padX: 56px;
    }

    .v2-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #ecd5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #5e2363
    }

    .ov-card .ov-head {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: .5rem
    }

    .ov-card .ov-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--ov-accent-100, var(--brand-100));
        color: var(--ov-accent, var(--brand))
    }

    .ov-card.ov-int {
        --ov-accent: var(--brand);
        --ov-accent-100: var(--brand-100)
    }

    .btn-ultimas-visitas {
        border: 2px solid #c62828;
        color: #c62828;
        background-color: #fff;
        font-weight: 600;
    }

    .btn-ultimas-visitas:hover,
    .btn-ultimas-visitas:focus {
        background-color: #ffeceb;
        color: #a11212;
    }

    .modal-ultimas-visitas .modal-dialog {
        max-width: 95vw;
    }

    .modal-ultimas-visitas .modal-content {
        border-radius: 18px;
    }

    .modal-ultimas-visitas .modal-body {
        max-height: 75vh;
        overflow-y: auto;
        padding: 1.5rem 1.75rem;
    }

    .modal-ultimas-visitas .ult-vis-header {
        display: grid;
        grid-template-columns: 140px 1fr 220px;
        gap: 12px;
        letter-spacing: .08em;
    }

    .visita-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .visita-item {
        border: 1px solid #eee;
        border-radius: 14px;
        padding: 1rem 1.25rem;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        background: #fff;
    }

    .visita-item-header {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 12px;
        align-items: center;
        margin-bottom: .75rem;
    }

    .visita-label {
        display: block;
        font-size: .75rem;
        text-transform: uppercase;
        color: #9f9f9f;
        letter-spacing: .08em;
        margin-bottom: .1rem;
    }

    .visita-item-body p {
        margin: 0;
        color: #3a3a3a;
        font-size: .95rem;
        line-height: 1.4;
    }

    @media (max-width: 992px) {
        .modal-ultimas-visitas .ult-vis-header {
            display: none;
        }
        .visita-item-header {
            grid-template-columns: 1fr;
        }
    }

    .ov-card.ov-vis {
        --ov-accent: var(--teal);
        --ov-accent-100: var(--teal-100)
    }

    .btn-ghost-brand {
        color: var(--brand);
        background: var(--brand-050);
        border: 1px solid #eadcf3
    }

    .btn-ghost-brand:hover {
        background: var(--brand-100);
        color: var(--brand-800)
    }

    /* === TIMELINE === */
    .ht-container {
        position: relative;
        overflow-x: auto;
        padding: 24px var(--padX) 8px;
        display: flex;
        justify-content: center;
        scroll-snap-type: x mandatory
    }

    .ht-track {
        position: relative;
        height: 110px;
        margin: 0 auto;
        min-width: 100%;
    }

    .ht-bar {
        position: absolute;
        left: var(--padX);
        right: var(--padX);
        top: 56px;
        height: 6px;
        background: #eadcf3;
        border-radius: 999px;
        box-shadow: inset 0 0 0 1px #e5d8ef
    }

.ht-marker {
        position: absolute;
        top: 0;
        transform: translateX(-50%);
        text-align: center;
        cursor: pointer;
        color: inherit;
        text-decoration: none;
        scroll-snap-align: center;
        max-width: 45%;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        overflow: visible;
    }

    .ht-label {
        display: inline-block;
        font-size: 12px;
        color: var(--brand);
        white-space: nowrap;
        transition: all .2s ease;
        padding: 4px 8px;
        border-radius: 8px;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ht-marker:hover .ht-label {
        background: var(--brand-100);
        color: var(--brand-800)
    }

    .ht-marker.active .ht-label {
        background: var(--brand);
        color: #fff;
        font-weight: 700;
        transform: scale(1.02)
    }

    .ht-dot {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--brand);
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px var(--brand-100), 0 4px 10px rgba(0, 0, 0, .08);
        transition: all .2s ease
    }

    .ht-marker:hover .ht-dot {
        transform: scale(1.1)
    }

    .ht-marker.active .ht-dot {
        background: var(--brand-800);
        box-shadow: 0 0 0 4px var(--brand-100), 0 6px 14px rgba(0, 0, 0, .12)
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--brand);
        display: inline-block;
        margin-right: 6px
    }
    </style>
</div>

<?php require_once("templates/footer.php"); ?>

</html>
