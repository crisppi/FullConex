<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Internação - Detalhes</title>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6.5.2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Timeout -->
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
function e($v) { return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8'); }
function fmtDate($s) {
    if (empty($s) || $s === '0000-00-00') return '-';
    $ts = strtotime(substr($s, 0, 10));
    return $ts ? date("d/m/Y", $ts) : '-';
}
if (!function_exists('ymd')) {
    function ymd($s) {
        if (!$s) return null;
        $s = trim((string)$s);
        $s = substr($s, 0, 10);
        $ts = strtotime($s);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
function after_dash($s){
    $s = trim((string)$s);
    if ($s === '') return '';
    $pos = mb_strpos($s, '-');
    $out = ($pos === false) ? $s : mb_substr($s, $pos + 1);
    $out = preg_replace('/\s+/', ' ', $out);
    return trim($out);
}
if (!function_exists('fmtDateAny')) {
    function fmtDateAny($s) {
        $y = ymd($s);
        return $y ? date('d/m/Y', strtotime($y)) : '-';
    }
}
function initials_from_name($name){
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
   VISITAS — lista por internação + detalhe via id_visita
   ========================================================= */
$visitas = [];
$visitaDAO = new visitaDAO($conn, $BASE_URL);

// 1) Carrega a LISTA de visitas da internação (para a timeline)
try {
    if (method_exists($visitaDAO, 'findGeralByIntern')) {
        $visitas = $visitaDAO->findGeralByIntern((int)$id_internacao) ?: [];
    } elseif (method_exists($visitaDAO, 'joinVisitaInternacao')) {
        $visitas = $visitaDAO->joinVisitaInternacao((int)$id_internacao) ?: [];
    }
} catch (Throwable $e) {
    $visitas = [];
}

// 2) Helpers para extrair campos da visita
function pick_visit_date($row){
    foreach (['data_visita','data_visita_vis','data','data_visita_int','created_at'] as $k){
        if (!empty($row[$k])) {
            $ts = strtotime(substr($row[$k], 0, 19));
            if ($ts) return date('Y-m-d', $ts);
        }
    }
    return null;
}
function pick_visit_time($row){
    foreach (['data_visita','data_visita_vis','data','data_visita_int','created_at'] as $k){
        if (!empty($row[$k])) {
            $ts = strtotime(substr($row[$k], 0, 19));
            if ($ts) return date('H:i', $ts);
        }
    }
    return null;
}
function pick_visit_text($row){
    foreach (['rel_visita','rel_visita_vis','rel_vis','relatorio','observacao','obs','descricao'] as $k){
        if (!empty($row[$k])) return $row[$k];
    }
    return '';
}
function pick_visit_id($row){
    foreach (['id_visita','id','id_vst'] as $k){
        if (!empty($row[$k])) return (int)$row[$k];
    }
    return crc32(json_encode($row)); // fallback
}

// 3) Normaliza + ordena ASC (crescente)
$visitas_norm = [];
foreach (($visitas ?? []) as $v) {
    $d = pick_visit_date($v);
    $visitas_norm[] = [
        '_id'   => pick_visit_id($v),
        '_date' => $d ?: date('Y-m-d'),
        '_time' => pick_visit_time($v),
        '_text' => pick_visit_text($v),
        '_raw'  => $v,
    ];
}
usort($visitas_norm, fn($a,$b) => strcmp($a['_date'],$b['_date']));

// 4) Intervalo
$minD = $visitas_norm ? $visitas_norm[0]['_date'] : null;
$maxD = $visitas_norm ? $visitas_norm[count($visitas_norm)-1]['_date'] : null;
$spanDays = ($minD && $maxD) ? max(1,(new DateTime($minD))->diff(new DateTime($maxD))->days) : 1;

// 5) Visita ativa (?vid= || ?id_visita=) ou última
$vid_req = filter_input(INPUT_GET,'vid',FILTER_SANITIZE_NUMBER_INT);
if (!$vid_req) $vid_req = filter_input(INPUT_GET,'id_visita',FILTER_SANITIZE_NUMBER_INT);

$activeVisit = null;
if ($vid_req) {
    foreach ($visitas_norm as $vn) {
        if ($vn['_id'] === (int)$vid_req) { $activeVisit = $vn; break; }
    }
}
if (!$activeVisit && $visitas_norm) $activeVisit = $visitas_norm[count($visitas_norm)-1];

// ===== Valores iniciais do relatório (data/hora/texto/ID) =====
$initDateLabel = '—';
$initTime = '';
$initText = '—';
$initId   = null;

if ($activeVisit) {
    $initDateLabel = date('d/m/Y', strtotime($activeVisit['_date']));
    $initTime      = $activeVisit['_time'] ?: '';
    $initText      = trim($activeVisit['_text']) !== '' ? $activeVisit['_text'] : '—';
    $initId        = (int)$activeVisit['_id'];
}

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
$pr_ini_raw = filter_input(INPUT_GET,'pr_ini',FILTER_DEFAULT) ?: '';
$pr_fim_raw = filter_input(INPUT_GET,'pr_fim',FILTER_DEFAULT) ?: '';
$pr_ini = ymd($pr_ini_raw);
$pr_fim = ymd($pr_fim_raw);

$pr_filtered = $prorrogacoes;
if ($pr_ini || $pr_fim) {
    $pr_filtered = array_filter($prorrogacoes, function($p) use ($pr_ini,$pr_fim){
        $ini = ymd($p['ini'] ?? null);
        $fim = ymd($p['fim'] ?? ($p['ini'] ?? null));
        if (!$ini && !$fim) return false;
        if ($pr_ini && $pr_fim) return ($fim >= $pr_ini) && ($ini <= $pr_fim);
        if ($pr_ini) return $fim >= $pr_ini;
        if ($pr_fim) return $ini <= $pr_fim;
        return true;
    });
}
usort($pr_filtered,function($a,$b){
    $da = strtotime($a['fim'] ?: ($a['ini'] ?? ''));
    $db = strtotime($b['fim'] ?: ($b['ini'] ?? ''));
    return $db <=> $da; // DESC
});
$pr_total_diarias = array_reduce($pr_filtered, fn($s,$p)=>$s+(int)($p['diarias']??0), 0);

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
$tuss_ini_raw = filter_input(INPUT_GET,'tuss_ini',FILTER_DEFAULT) ?: '';
$tuss_fim_raw = filter_input(INPUT_GET,'tuss_fim',FILTER_DEFAULT) ?: '';
$tuss_ini = ymd($tuss_ini_raw);
$tuss_fim = ymd($tuss_fim_raw);

$tuss_filtered = $tussItens;
if ($tuss_ini || $tuss_fim) {
    $tuss_filtered = array_filter($tussItens,function($t) use($tuss_ini,$tuss_fim){
        $dt = ymd($t['data_realizacao_tuss'] ?? null);
        if (!$dt) return false;
        if ($tuss_ini && $tuss_fim) return ($dt >= $tuss_ini) && ($dt <= $tuss_fim);
        if ($tuss_ini) return $dt >= $tuss_ini;
        if ($tuss_fim) return $dt <= $tuss_fim;
        return true;
    });
}
usort($tuss_filtered,function($a,$b){
    $da = strtotime($a['data_realizacao_tuss'] ?? '');
    $db = strtotime($b['data_realizacao_tuss'] ?? '');
    return $db <=> $da;
});
$tuss_tot_solic = array_reduce($tuss_filtered, fn($s,$r)=>$s+(int)($r['qtd_tuss_solicitado']??0), 0);
$tuss_tot_lib   = array_reduce($tuss_filtered, fn($s,$r)=>$s+(int)($r['qtd_tuss_liberado']??0), 0);

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
$neg_ini_raw = filter_input(INPUT_GET,'neg_ini',FILTER_DEFAULT) ?: '';
$neg_fim_raw = filter_input(INPUT_GET,'neg_fim',FILTER_DEFAULT) ?: '';
$neg_ini = ymd($neg_ini_raw);
$neg_fim = ymd($neg_fim_raw);

$neg_filtered = $negociacoes;
if ($neg_ini || $neg_fim) {
    $neg_filtered = array_filter($negociacoes,function($n) use($neg_ini,$neg_fim){
        $ini = ymd($n['data_inicio_neg'] ?? null);
        $fim = ymd($n['data_fim_neg'] ?? null) ?: $ini;
        if (!$ini && !$fim) return false;
        if ($neg_ini && $neg_fim) return ($fim >= $neg_ini) && ($ini <= $neg_fim);
        if ($neg_ini) return $fim >= $neg_ini;
        if ($neg_fim) return $ini <= $neg_fim;
        return true;
    });
}
usort($neg_filtered,function($a,$b){
    $da = strtotime($a['data_fim_neg'] ?? ($a['data_inicio_neg'] ?? ''));
    $db = strtotime($b['data_fim_neg'] ?? ($b['data_inicio_neg'] ?? ''));
    return $db <=> $da;
});
?>

<div id="main-container" class="container-fluid py-3">
    <div class="v2-max mx-auto">

        <!-- Header Card -->
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

        <!-- Abas -->
        <div class="card shadow-sm">
            <div class="card-body">
                <ul class="nav nav-pills mb-3" id="internTabs" role="tablist"
                    style="--bs-nav-pills-link-active-bg:#5e2363; --bs-nav-pills-link-active-color:#fff; --bs-nav-link-color:#5e2363; --bs-nav-link-hover-color:#5e2363;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="resumo-tab" data-bs-toggle="pill" data-bs-target="#resumo"
                            type="button" role="tab">
                            <i class="fa-solid fa-bars me-2"></i>Resumo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="visitas-tab" data-bs-toggle="pill" data-bs-target="#visitas"
                            type="button" role="tab">
                            <i class="fa-solid fa-stethoscope me-2"></i>Visitas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prorrog-tab" data-bs-toggle="pill" data-bs-target="#prorrog"
                            type="button" role="tab">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>Prorrogações
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tuss-tab" data-bs-toggle="pill" data-bs-target="#tuss"
                            type="button" role="tab">
                            <i class="fa-solid fa-list-check me-2"></i>TUSS
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="neg-tab" data-bs-toggle="pill" data-bs-target="#neg" type="button"
                            role="tab">
                            <i class="fa-solid fa-handshake me-2"></i>Negociações
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="internTabsContent">
                    <!-- Resumo -->
                    <div class="tab-pane fade show active" id="resumo" role="tabpanel" aria-labelledby="resumo-tab">
                        <div class="row g-3">
                            <!-- Card Internação -->
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

                            <!-- Card Detalhes -->
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

                        <!-- Relatório Internação -->
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

                    <!-- VISITAS -->
                    <div class="tab-pane fade" id="visitas" role="tabpanel" aria-labelledby="visitas-tab">
                        <?php if (!$visitas_norm): ?>
                        <p class="text-muted mb-0">Nenhuma visita registrada para esta internação.</p>
                        <?php else: ?>
                        <div class="card ov-card ov-int"
                            style="border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.06);background-image:linear-gradient(to right, var(--ov, #5e2363) 6px, #fff 6px);">
                            <div class="card-body">
                                <div class="ov-head">
                                    <h6 class="ov-title mb-0">Linha do tempo de Visitas</h6>
                                </div>

                                <!-- Timeline (crescente + com respiro nas bordas) -->
                                <?php $countVis = count($visitas_norm); $trackWidthPx = max(800, $countVis * 160); ?>
                                <div class="ht-container">
                                    <div class="ht-track" style="width: <?= (int)$trackWidthPx ?>px">
                                        <div class="ht-bar"></div>
                                        <?php foreach ($visitas_norm as $i => $v):
                                            $daysFromMin = max(0,(new DateTime($minD ?: $v['_date']))->diff(new DateTime($v['_date']))->days);
                                            $pct = $spanDays ? round(($daysFromMin / $spanDays) * 100, 2) : 0;
                                            $pct = max(2, min(98, $pct));
                                            $edgeLeft  = ($pct <= 3.5);
                                            $edgeRight = ($pct >= 96.5);
                                            $edgeCls   = ($edgeLeft ? ' edge-left' : '') . ($edgeRight ? ' edge-right' : '');
                                            $isActive  = ($activeVisit && $activeVisit['_id'] === $v['_id']);
                                            $dataLabel = date('d/m/Y', strtotime($v['_date']));
                                            $hora      = $v['_time'] ?: '';
                                            $texto     = trim($v['_text']) !== '' ? $v['_text'] : '—';
                                        ?>
                                        <a class="ht-marker<?= $edgeCls ?><?= $isActive ? ' active' : '' ?>" href="#"
                                            style="left: <?= $pct ?>%;" data-id="<?= (int)$v['_id'] ?>"
                                            data-date="<?= e($dataLabel) ?>" data-time="<?= e($hora) ?>"
                                            data-text="<?= e($texto) ?>" onclick="(function(m){
                                              document.querySelectorAll('#visitas .ht-marker.active').forEach(function(x){x.classList.remove('active');});
                                              m.classList.add('active');
                                              var d=m.dataset.date||'—', t=m.dataset.time||'', x=m.dataset.text||'—', i=m.dataset.id||'';
                                              var dEl=document.getElementById('v-rel-date');
                                              var tWrap=document.getElementById('v-rel-time-wrap');
                                              var tEl=document.getElementById('v-rel-time');
                                              var xEl=document.getElementById('v-rel-text');
                                              var iWrap=document.getElementById('v-rel-id-wrap');
                                              var iEl=document.getElementById('v-rel-id');
                                              if(dEl) dEl.textContent=d;
                                              if(tWrap) tWrap.style.display = t ? '' : 'none';
                                              if(tEl) tEl.textContent = t || '';
                                              if(xEl) xEl.textContent = x;
                                              if(iEl) iEl.textContent = i || '';
                                              if(iWrap){ if(i){ iWrap.classList.remove('d-none'); } else { iWrap.classList.add('d-none'); } }
                                              var cont=document.querySelector('#visitas .ht-container');
                                              if(cont){ cont.scrollLeft = Math.max(0, m.offsetLeft - cont.clientWidth/2); }
                                          })(this); return false;">
                                            <span class="ht-label"><?= e($dataLabel) ?></span>
                                            <span class="ht-dot"></span>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Relatório (apenas abaixo) -->
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">
                                            Relatório da visita: <span id="v-rel-date"><?= e($initDateLabel) ?></span>
                                            <span id="v-rel-time-wrap" class="text-muted"
                                                style="<?= $initTime ? '' : 'display:none' ?>">
                                                • <span id="v-rel-time"><?= e($initTime) ?></span>
                                            </span>
                                            <span id="v-rel-id-wrap"
                                                class="badge rounded-pill text-bg-secondary ms-2<?= $initId ? '' : ' d-none' ?>">
                                                ID <span id="v-rel-id"><?= e($initId ?: '') ?></span>
                                            </span>
                                        </h6>
                                    </div>
                                    <div class="p-3 rounded border" style="border-color:#eee">
                                        <div class="v2-relatorio" id="v-rel-text" style="white-space:pre-wrap">
                                            <?= e($initText) ?></div>
                                    </div>

                                    <?php if ($minD && $maxD): ?>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="small text-secondary"><?= e(date('d/m/Y', strtotime($minD))) ?> —
                                            <?= e(date('d/m/Y', strtotime($maxD))) ?></div>
                                        <div class="small"><span class="legend-dot"></span> Clique nas datas para ver o
                                            relatório</div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- PRORROGAÇÕES -->
                    <div class="tab-pane fade" id="prorrog" role="tabpanel" aria-labelledby="prorrog-tab">
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

                    <!-- TUSS -->
                    <div class="tab-pane fade" id="tuss" role="tabpanel" aria-labelledby="tuss-tab">
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

                    <!-- NEGOCIAÇÕES -->
                    <div class="tab-pane fade" id="neg" role="tabpanel" aria-labelledby="neg-tab">
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
                    <a href="<?= !empty($_SERVER['HTTP_REFERER']) ? 'javascript:history.back()' : $BASE_URL . 'internacoes.php' ?>"
                        class="btn btn-ghost-brand btn-sm rounded-pill shadow-sm">
                        <i class="fa-solid fa-arrow-left me-2"></i>Voltar
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Bootstrap JS (se não for carregado pelo layout pai, descomente) -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->

<script>
// Tabs: manter hash na URL + rolar timeline para a direita ao abrir Visitas
(function() {
    var hash = window.location.hash;
    if (hash) {
        var triggerEl = document.querySelector('#internTabs button[data-bs-target="' + hash + '"]');
        if (triggerEl && window.bootstrap && bootstrap.Tab) new bootstrap.Tab(triggerEl).show();
    }
    document.querySelectorAll('#internTabs button[data-bs-toggle="pill"]').forEach(function(btn) {
        btn.addEventListener('shown.bs.tab', function(ev) {
            var target = ev.target.getAttribute('data-bs-target');
            if (target) history.replaceState(null, '', target);
            if (target === '#visitas') {
                setTimeout(function() {
                    var cont = document.querySelector('#visitas .ht-container');
                    if (cont) cont.scrollLeft = cont.scrollWidth;
                }, 0);
            }
        });
    });
})();
</script>

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
    /* respiro nas bordas da timeline */
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
    max-width: 100%
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
    max-width: 45%
}

.ht-marker.edge-left {
    transform: none
}

.ht-marker.edge-right {
    transform: translateX(-100%)
}

.ht-label {
    display: inline-block;
    font-size: 12px;
    color: var(--brand);
    margin-bottom: 6px;
    white-space: nowrap;
    transition: all .2s ease;
    padding: 4px 8px;
    border-radius: 8px;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis
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

<?php require_once("templates/footer.php"); ?>

</html>