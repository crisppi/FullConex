<?php
// DEBUG TEMPORÁRIO (REMOVER APÓS TESTE)
ini_set('log_errors', '1');
error_reporting(E_ALL);

include_once("check_logado.php");

require_once("templates/header.php");
require_once("models/message.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/hospitalUser.php");
include_once("dao/hospitalUserDao.php");

include_once("models/uti.php");
include_once("dao/utiDao.php");

include_once("models/capeante.php");
include_once("dao/capeanteDao.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("dao/indicadoresDao.php");
require_once __DIR__ . '/app/services/PermanenciaForecastService.php';

// -----------------------------
// ENTRADAS E SESSÃO
// -----------------------------
$hospital_selecionado = isset($_POST['hospital_id']) ? (int)$_POST['hospital_id'] : 0;
if (isset($_POST['clear_hospital']) && (int)$_POST['clear_hospital'] === 1) {
    $hospital_selecionado = 0;
}
$id_usuario_sessao    = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;
$nivel_sessao         = isset($_SESSION['nivel']) ? (int)$_SESSION['nivel'] : 99;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$normCargoAccess = static function ($txt): string {
    $txt = mb_strtolower(trim((string)$txt), 'UTF-8');
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $txt);
    $txt = $ascii !== false ? $ascii : $txt;
    return preg_replace('/[^a-z]/', '', $txt);
};
$isSeguradoraRole = (strpos($normCargoAccess($_SESSION['cargo'] ?? ''), 'seguradora') !== false);
$seguradoraUserId = (int)($_SESSION['fk_seguradora_user'] ?? 0);
if ($isSeguradoraRole && $seguradoraUserId <= 0) {
    try {
        $uid = (int)($_SESSION['id_usuario'] ?? 0);
        if ($uid > 0) {
            $stmtSeg = $conn->prepare("SELECT fk_seguradora_user FROM tb_user WHERE id_usuario = :id LIMIT 1");
            $stmtSeg->bindValue(':id', $uid, PDO::PARAM_INT);
            $stmtSeg->execute();
            $seguradoraUserId = (int)($stmtSeg->fetchColumn() ?: 0);
            if ($seguradoraUserId > 0) {
                $_SESSION['fk_seguradora_user'] = $seguradoraUserId;
            }
        }
    } catch (Throwable $e) {
        error_log('[DASH_MENU][SEGURADORA] ' . $e->getMessage());
    }
}
$seguradoraUserNome = '';
if ($isSeguradoraRole && $seguradoraUserId > 0) {
    try {
        $stmtSegNome = $conn->prepare("SELECT seguradora_seg FROM tb_seguradora WHERE id_seguradora = :id LIMIT 1");
        $stmtSegNome->bindValue(':id', $seguradoraUserId, PDO::PARAM_INT);
        $stmtSegNome->execute();
        $seguradoraUserNome = (string)($stmtSegNome->fetchColumn() ?: '');
    } catch (Throwable $e) {
        $seguradoraUserNome = '';
    }
}

$seguradoraCondAc = null;
$seguradoraCondI  = null;
if ($isSeguradoraRole) {
    if ($seguradoraUserId > 0) {
        $seguradoraCondAc = "EXISTS (SELECT 1 FROM tb_paciente pa_s WHERE pa_s.id_paciente = ac.fk_paciente_int AND pa_s.fk_seguradora_pac = {$seguradoraUserId})";
        $seguradoraCondI  = "EXISTS (SELECT 1 FROM tb_paciente pa_s WHERE pa_s.id_paciente = i.fk_paciente_int AND pa_s.fk_seguradora_pac = {$seguradoraUserId})";
    } else {
        $seguradoraCondAc = "1=0";
        $seguradoraCondI  = "1=0";
    }
}

function dashCacheGet(string $key, int $ttl)
{
    $cache = $_SESSION['dash_menu_cache'] ?? [];
    if (!isset($cache[$key])) return null;
    $item = $cache[$key];
    if (!is_array($item) || !isset($item['ts'])) return null;
    if ((time() - (int)$item['ts']) > $ttl) return null;
    return $item['data'] ?? null;
}

function dashCacheSet(string $key, $data): void
{
    if (!isset($_SESSION['dash_menu_cache'])) $_SESSION['dash_menu_cache'] = [];
    $_SESSION['dash_menu_cache'][$key] = [
        'ts' => time(),
        'data' => $data,
    ];
}

$cacheBase = 'dash_menu_' . $hospital_selecionado . '_' . $id_usuario_sessao . '_' . $nivel_sessao . '_' . ($isSeguradoraRole ? 'seg' : 'geral') . '_' . $seguradoraUserId;

// -----------------------------
// CONDIÇÕES / WHEREs
// -----------------------------
$condicoes = [
    $hospital_selecionado ? "ac.fk_hospital_int = {$hospital_selecionado}" : null,
    (!$isSeguradoraRole && $id_usuario_sessao && $nivel_sessao <= 3) ? "hos.fk_usuario_hosp = {$id_usuario_sessao}" : null,
    $seguradoraCondAc
];

$condicoes_vis = [
    $hospital_selecionado ? "ac.fk_hospital_int = {$hospital_selecionado}" : null,
    "ac.internado_int = 's'",
    "(vi.id_visita = (SELECT MAX(vi2.id_visita) FROM tb_visita vi2 WHERE vi2.fk_internacao_vis = ac.id_internacao) OR vi.id_visita IS NULL)",
    $seguradoraCondAc
];

$condicoes_hospital = [
    "DATEDIFF(CURRENT_DATE(), i.data_intern_int) > COALESCE(s.longa_permanencia_seg, 0)",
    $hospital_selecionado ? "i.fk_hospital_int = {$hospital_selecionado}" : null,
    (!$isSeguradoraRole && $id_usuario_sessao && $nivel_sessao <= 3) ? "hos.fk_usuario_hosp = {$id_usuario_sessao}" : null,
    "i.internado_int = 's'",
    (!$isSeguradoraRole && $id_usuario_sessao && $nivel_sessao <= 3) ? "i.fk_hospital_int IN (SELECT hu.fk_hospital_user FROM tb_hospitalUser hu WHERE hu.fk_usuario_hosp = {$id_usuario_sessao})" : null,
    $seguradoraCondI
];

$condicoes_contas = [
    "c.conta_parada_cap = 's'",
    $hospital_selecionado ? "i.fk_hospital_int = {$hospital_selecionado}" : null,
    (!$isSeguradoraRole && $id_usuario_sessao && $nivel_sessao <= 3) ? "i.fk_hospital_int IN (SELECT hu.fk_hospital_user FROM tb_hospitalUser hu WHERE hu.fk_usuario_hosp = {$id_usuario_sessao})" : null,
    $seguradoraCondI
];

$condicoes_gerais = [
    $hospital_selecionado ? "i.fk_hospital_int = {$hospital_selecionado}" : null,
    (!$isSeguradoraRole && $id_usuario_sessao && $nivel_sessao <= 3) ? "i.fk_hospital_int IN (SELECT hu.fk_hospital_user FROM tb_hospitalUser hu WHERE hu.fk_usuario_hosp = {$id_usuario_sessao})" : null,
    $seguradoraCondI
];

$condicoes_gerais_reint = [
    $hospital_selecionado ? "ac.fk_hospital_int = {$hospital_selecionado}" : null,
    ($isSeguradoraRole
        ? ($seguradoraUserId > 0 ? "pa.fk_seguradora_pac = {$seguradoraUserId}" : "1=0")
        : null)
];

$condicoes               = array_filter($condicoes);
$condicoes_vis           = array_filter($condicoes_vis);
$condicoes_hospital      = array_filter($condicoes_hospital);
$condicoes_contas        = array_filter($condicoes_contas);
$condicoes_gerais        = array_filter($condicoes_gerais);
$condicoes_gerais_reint  = array_filter($condicoes_gerais_reint);

// WHERE finais
$where              = implode(' AND ', $condicoes);
$where_vis          = implode(' AND ', $condicoes_vis);
$where_hospital     = implode(' AND ', $condicoes_hospital);
$where_contas       = implode(' AND ', $condicoes_contas);
$where_gerais       = implode(' AND ', $condicoes_gerais);
$where_gerais_reint = implode(' AND ', $condicoes_gerais_reint);

// -----------------------------
// DAOs
// -----------------------------
$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$uti_geral        = $uti = new utiDAO($conn, $BASE_URL);
$hospitalUser     = new hospitalUserDAO($conn, $BASE_URL);
$hospital         = new hospitalDAO($conn, $BASE_URL);
$indicadores      = new indicadoresDAO($conn, $BASE_URL);
$forecastService  = new PermanenciaForecastService($conn);
$forecastSummary  = ['updated' => 0, 'skipped' => 0, 'model' => 'permanencia-lite-v1'];
$forecastRows     = [];
try {
    $refreshKey = $cacheBase . '_forecast_refresh_ts';
    $lastRefresh = dashCacheGet($refreshKey, 3600);
    $shouldRefresh = !$lastRefresh || (time() - (int)$lastRefresh) > 600;
    if ($shouldRefresh) {
        $forecastSummary = $forecastService->refreshActiveForecasts($hospital_selecionado ?: null);
        dashCacheSet($refreshKey, time());
        dashCacheSet($cacheBase . '_forecast_summary', $forecastSummary);
    } else {
        $cachedSummary = dashCacheGet($cacheBase . '_forecast_summary', 3600);
        if (is_array($cachedSummary)) $forecastSummary = $cachedSummary;
    }

    $forecastRows = dashCacheGet($cacheBase . '_forecast_rows', 120);
    if (!is_array($forecastRows)) {
        $forecastRows = $forecastService->fetchDashboardRows(
            $hospital_selecionado ?: null,
            $id_usuario_sessao ?: null,
            $isSeguradoraRole ? null : ($nivel_sessao ?? null),
            8,
            ($isSeguradoraRole && $seguradoraUserId > 0) ? $seguradoraUserId : null
        );
        if (!is_array($forecastRows)) {
            $forecastRows = [];
        }
        dashCacheSet($cacheBase . '_forecast_rows', $forecastRows);
    }
    if (!is_array($forecastRows)) {
        $forecastRows = [];
    }
} catch (Throwable $e) {
    error_log('[ForecastService] ' . $e->getMessage());
}

// -----------------------------
// LISTA DE HOSPITAIS POR PERFIL
// -----------------------------
if ($isSeguradoraRole || $nivel_sessao > 3) {
    $dados_hospital = $hospital->findGeral();
} else {
    $dados_hospital = $hospitalUser->joinHospitalUser($id_usuario_sessao);
}

// Normalização defensiva (pode vir int/string/obj/array)
$dados_hospital = array_values(array_filter(array_map(function ($h) {
    if (is_array($h)) {
        return [
            'id_hospital' => isset($h['id_hospital']) ? (int)$h['id_hospital'] : 0,
            'nome_hosp'   => isset($h['nome_hosp']) ? (string)$h['nome_hosp'] : ''
        ];
    }
    if (is_object($h)) {
        return [
            'id_hospital' => isset($h->id_hospital) ? (int)$h->id_hospital : 0,
            'nome_hosp'   => isset($h->nome_hosp) ? (string)$h->nome_hosp : ''
        ];
    }
    if (is_int($h) || is_numeric($h)) {
        return ['id_hospital' => (int)$h, 'nome_hosp' => ''];
    }
    if (is_string($h)) {
        return ['id_hospital' => 0, 'nome_hosp' => $h];
    }
    return null;
}, (array)$dados_hospital), function ($x) {
    return is_array($x) && array_key_exists('id_hospital', $x);
}));

// --- SOMENTE hospitais válidos (id > 0), sem duplicatas por ID e ordenados por nome ---
$map = [];
foreach ($dados_hospital as $h) {
    if (!is_array($h)) continue;
    $hid = (int)($h['id_hospital'] ?? 0);
    if ($hid <= 0) continue; // remove “Medico”, emails etc.
    $map[$hid] = [
        'id_hospital' => $hid,
        'nome_hosp'   => (string)($h['nome_hosp'] ?? '')
    ];
}
$dados_hospital_select = array_values($map);
usort($dados_hospital_select, function ($a, $b) {
    return strcasecmp($a['nome_hosp'] ?? '', $b['nome_hosp'] ?? '');
});

// Hospital selecionado (se houver)
$filtered_hospital = [];
if ($hospital_selecionado > 0) {
    foreach ($dados_hospital_select as $h) {
        if ((int)$h['id_hospital'] === $hospital_selecionado) {
            $filtered_hospital = [$h];
            break;
        }
    }
}

// Nome a exibir no topo do select
$hospital_name = (!empty($filtered_hospital) && !empty($filtered_hospital[0]['nome_hosp']))
    ? ucwords(strtolower($filtered_hospital[0]['nome_hosp']))
    : 'Todos Hospitais';

// -----------------------------
// BUSCAS
// -----------------------------
$dados_internacoes_geral = dashCacheGet($cacheBase . '_internacoes_geral', 60);
if (!is_array($dados_internacoes_geral)) {
    $dados_internacoes_geral = $Internacao_geral->selectAllInternacaoList($where);
    dashCacheSet($cacheBase . '_internacoes_geral', $dados_internacoes_geral);
}

$dados_internacoes_uti = dashCacheGet($cacheBase . '_internacoes_uti', 60);
if (!is_array($dados_internacoes_uti)) {
    $utiWhereParts = ["ac.internado_int = 's'", "ut.id_uti IS NOT NULL"];
    if ($hospital_selecionado) $utiWhereParts[] = "ac.fk_hospital_int = {$hospital_selecionado}";
    if ($seguradoraCondAc) $utiWhereParts[] = $seguradoraCondAc;
    $dados_internacoes_uti = $Internacao_geral->QtdInternacao(implode(' AND ', $utiWhereParts));
    dashCacheSet($cacheBase . '_internacoes_uti', $dados_internacoes_uti);
}

$dados_internacoes_visitas = dashCacheGet($cacheBase . '_internacoes_visitas', 60);
if (!is_array($dados_internacoes_visitas)) {
    $dados_internacoes_visitas = $Internacao_geral->selectInternVisLastWhere($where_vis);
    dashCacheSet($cacheBase . '_internacoes_visitas', $dados_internacoes_visitas);
}

$ultimaVisitaPorInternacao = [];
foreach ((array)$dados_internacoes_visitas as $vis) {
    $id = (int)($vis['id_internacao'] ?? $vis['fk_internacao_vis'] ?? 0);
    $dataVisita = $vis['data_visita_vis'] ?? null;
    if ($id <= 0 || empty($dataVisita)) {
        continue;
    }
    $ts = strtotime($dataVisita);
    if ($ts === false) {
        continue;
    }
    if (!isset($ultimaVisitaPorInternacao[$id]) || $ts > $ultimaVisitaPorInternacao[$id]['ts']) {
        $ultimaVisitaPorInternacao[$id] = [
            'data' => $dataVisita,
            'ts' => $ts,
        ];
    }
}

// Capeante (concatenação corrigida)
$capFilter  = "ca.em_auditoria_cap IS NULL";
$where_cap  = trim($where) !== '' ? ($where . " AND " . $capFilter) : $capFilter;
$dados_capeante = dashCacheGet($cacheBase . '_capeante', 60);
if (!is_array($dados_capeante)) {
    $dados_capeante = $Internacao_geral->selectAllInternacaoCapList($where_cap);
    dashCacheSet($cacheBase . '_capeante', $dados_capeante);
}

// -----------------------------
// FILTROS AUXILIARES
// -----------------------------
function filterInternados($value)
{
    return (isset($value['internado_int']) && $value['internado_int'] === 's');
}
$dados_internacoes = array_filter((array)$dados_internacoes_geral, 'filterInternados');

// Visitas em atraso
function filterVisitasAtrasadas($value)
{
    $hoje  = new DateTime('today');
    $toDate = function ($s) {
        if (empty($s)) return null;
        $dt = DateTime::createFromFormat('Y-m-d', $s);
        if ($dt instanceof DateTime) return $dt;
        $ts = strtotime($s);
        if ($ts === false) return null;
        $dt = new DateTime();
        $dt->setTimestamp($ts);
        return $dt;
    };
    $dtVisita = $toDate($value['data_visita_vis'] ?? null);
    $dtIntern = $toDate($value['data_visita_int'] ?? null);
    $limiteDias = (int)($value['dias_visita_seg'] ?? 0);
    if ($limiteDias <= 0) {
        $limiteDias = 10;
    }

    if ($dtVisita instanceof DateTime) {
        $dias = ($dtVisita > $hoje) ? 0 : $dtVisita->diff($hoje)->days;
        return $dias > $limiteDias;
    }
    if ($dtIntern instanceof DateTime) {
        $dias = ($dtIntern > $hoje) ? 0 : $dtIntern->diff($hoje)->days;
        return $dias > $limiteDias;
    }
    return false;
}
$dados_visitas_atraso = array_filter((array)$dados_internacoes_visitas, 'filterVisitasAtrasadas');

function diasDesdeData($data)
{
    if (empty($data)) {
        return null;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $data);
    if (!($dt instanceof DateTime)) {
        $ts = strtotime($data);
        if ($ts === false) {
            return null;
        }
        $dt = new DateTime();
        $dt->setTimestamp($ts);
    }
    $hoje = new DateTime('today');
    if ($dt > $hoje) {
        return 0;
    }
    return $dt->diff($hoje)->days;
}

// Ordena por data e pega os 8 mais recentes
usort($dados_visitas_atraso, function ($a, $b) {
    return strcmp($a['data_visita_vis'] ?? '', $b['data_visita_vis'] ?? '');
});
$dados_visitas_atraso_list = array_slice($dados_visitas_atraso, -8);

// Indicadores
$drg_acima = dashCacheGet($cacheBase . '_drg_acima', 60);
if (!is_array($drg_acima)) {
    $drg_acima = $indicadores->getDrgAcima($where_gerais);
    dashCacheSet($cacheBase . '_drg_acima', $drg_acima);
}

$perc_uti = dashCacheGet($cacheBase . '_perc_uti', 60);
if (!is_array($perc_uti)) {
    $perc_uti = $indicadores->getUtiPerc($where_gerais);
    dashCacheSet($cacheBase . '_perc_uti', $perc_uti);
}

// Longa permanência
$longa_perm = dashCacheGet($cacheBase . '_longa_perm', 60);
if (!is_array($longa_perm)) {
    $longa_perm = $indicadores->getLongaPermanencia($where_hospital);
    dashCacheSet($cacheBase . '_longa_perm', $longa_perm);
}
$longa_perm_list = $longa_perm;
if (!empty($longa_perm_list)) {
    usort($longa_perm_list, function ($a, $b) {
        return strcmp($a['data_intern_int'] ?? '', $b['data_intern_int'] ?? '');
    });
    $longa_perm_list = array_slice($longa_perm_list, -8);
} else {
    $longa_perm_list = [];
}

// Contas paradas
$contas_paradas = dashCacheGet($cacheBase . '_contas_paradas', 60);
if (!is_array($contas_paradas)) {
    $contas_paradas = $indicadores->getContasParadas($where_contas);
    dashCacheSet($cacheBase . '_contas_paradas', $contas_paradas);
}

// UTI não pertinente
$uti_nao_pertinente = dashCacheGet($cacheBase . '_uti_nao_pertinente', 60);
if (!is_array($uti_nao_pertinente)) {
    $uti_nao_pertinente = $indicadores->getUtiPertinente($where_gerais);
    dashCacheSet($cacheBase . '_uti_nao_pertinente', $uti_nao_pertinente);
}

// Score baixo
$score_baixo = dashCacheGet($cacheBase . '_score_baixo', 60);
if (!is_array($score_baixo)) {
    $score_baixo = $indicadores->getScoreBaixo($where_gerais);
    dashCacheSet($cacheBase . '_score_baixo', $score_baixo);
}

// Reinternações
$reinternacaohosp = dashCacheGet($cacheBase . '_reinternacao', 60);
if (!is_array($reinternacaohosp)) {
    $reinternacaohosp = $Internacao_geral->reinternacaoNova($where_gerais_reint);
    dashCacheSet($cacheBase . '_reinternacao', $reinternacaohosp);
}
$total_reinternacoes = is_array($reinternacaohosp) ? count($reinternacaohosp) : 0;
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos de Internações</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Fontfaces CSS-->
    <link href="diversos/CoolAdmin-master/css/font-face.css" rel="stylesheet" media="all">
    <link href="diversos/CoolAdmin-master/vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet"
        media="all">
    <link href="diversos/CoolAdmin-master/vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet"
        media="all">
    <link href="diversos/CoolAdmin-master/vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet"
        media="all">
    <!-- Bootstrap CSS-->
    <link href="diversos/CoolAdmin-master/vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
    <!-- Vendor CSS-->
    <link href="diversos/CoolAdmin-master/vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="diversos/CoolAdmin-master/vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css"
        rel="stylesheet" media="all">
    <link href="diversos/CoolAdmin-master/vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="diversos/CoolAdmin-master/vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="diversos/CoolAdmin-master/vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="diversos/CoolAdmin-master/vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="diversos/CoolAdmin-master/vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">
    <!-- Main CSS-->
    <link href="diversos/CoolAdmin-master/css/theme.css" rel="stylesheet" media="all">
</head>

<style>
.grid-container {
    width: 100%;
    margin-bottom: 12px;
}

.kpi-grid-container {
    display: grid;
    grid-template-columns: repeat(3, minmax(210px, 1fr));
    gap: 12px;
    width: 100%;
}

.grid-item {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: #3f2a59;
    border-radius: 18px;
    background:
        radial-gradient(circle at 12% 110%, rgba(90, 197, 255, 0.16), transparent 55%),
        linear-gradient(145deg, #f5f0ff, #ece6f7);
    min-height: 120px;
    box-shadow: 0 8px 18px rgba(39, 24, 58, 0.10);
    border: 1px solid rgba(134, 155, 204, 0.22);
    overflow: hidden;
    padding: 10px 0;
    transition: transform .15s ease, box-shadow .15s ease;
}

.grid-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 24px rgba(39, 24, 58, 0.14);
}

.grid-item-filter {
    width: 100%;
}

.grid-item-kpi.kpi-neutral {
    border-left: 4px solid #6d49ab;
}

.grid-item-kpi.kpi-info {
    border-left: 4px solid #2b8dc2;
}

.grid-item-kpi.kpi-warning {
    border-left: 4px solid #d19a2a;
}

.grid-item-kpi.kpi-critical {
    border-left: 4px solid #c64c64;
}

.grid-item::before {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top left, rgba(122, 89, 170, 0.16), transparent 58%);
    opacity: 0.65;
    pointer-events: none;
}

.grid-item::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, rgba(150, 129, 214, 0.95), rgba(120, 210, 245, 0.95));
    opacity: 0.9;
}

.title-item {
    position: absolute;
    top: 10px;
    left: 14px;
    right: 14px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 8px;
    min-height: 40px;
    width: calc(100% - 28px);
    text-align: left;
    line-height: 1.2;
    font-size: 0.92rem;
    color: #34204f;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
    white-space: normal;
    text-shadow: none;
}

.title-item i {
    flex: 0 0 auto;
}

.icon-item {
    position: absolute;
    bottom: 14px;
    left: 16px;
    font-size: .95rem;
    color: #ffffff;
    background: linear-gradient(145deg, #8354ba, #5e3a8a);
    border-radius: 50%;
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 12px rgba(46, 28, 70, 0.18);
    opacity: .9;
}

.badge-item {
    position: absolute;
    bottom: 12px;
    right: 16px;
    min-width: clamp(92px, 20vw, 124px);
    max-width: calc(100% - 84px);
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    font-weight: 800;
    color: #452b63 !important;
    background: linear-gradient(140deg, rgba(255, 255, 255, 0.9), rgba(248, 249, 255, 0.78)) !important;
    padding: 6px clamp(10px, 1.6vw, 14px);
    border-radius: 999px;
    font-size: clamp(1rem, 1.25vw, 1.32rem);
    text-align: center;
    border: 1px solid rgba(142, 161, 199, 0.28);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (max-width: 1400px) {
    .title-item {
        font-size: 0.75em;
    }
}

@media (max-width: 980px) {
    .badge-item {
        min-width: clamp(82px, 24vw, 102px);
        min-height: 42px;
        font-size: clamp(0.9rem, 2.8vw, 1.08rem);
    }
}

@media (max-width: 1200px) {
    .kpi-grid-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 860px) {
    .kpi-grid-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .grid-item {
        height: 118px;
    }
}

@media (max-width: 520px) {
    .kpi-grid-container {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .grid-item {
        height: 116px;
    }
}

/* Forca alinhamento dos cards do topo, mesmo com CSS global carregado depois */
.grid-container .grid-item .title-item {
    left: 14px !important;
    right: 14px !important;
    width: calc(100% - 28px) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    text-align: left !important;
    line-height: 1.2 !important;
    white-space: normal !important;
}

.grid-container .grid-item .badge-item {
    min-height: 46px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    line-height: 1 !important;
}

.badge-item.badge-neutral {
    color: #452b63 !important;
}

.badge-item.badge-info {
    color: #0d6695 !important;
    border-color: rgba(78, 169, 218, 0.35);
}

.badge-item.badge-warning {
    color: #996200 !important;
    border-color: rgba(216, 172, 93, 0.45);
}

.badge-item.badge-critical {
    color: #ad2944 !important;
    border-color: rgba(200, 92, 116, 0.42);
}

.select-item {
    position: absolute;
    bottom: 18px;
    left: 15px;
    right: 15px;
}

.select-wrapper {
    width: 100%;
}

.select-shell {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.93);
    border-radius: 18px;
    border: 1px solid rgba(118, 77, 150, 0.35);
    box-shadow: 0 8px 18px rgba(27, 10, 36, 0.12), inset 0 2px 0 rgba(255, 255, 255, 0.9);
    padding: 4px 4px 4px 12px;
    gap: 10px;
}

.select-chevron {
    color: #8a6aa8;
    font-size: 1rem;
    pointer-events: none;
}

.button-item {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, #6f40bc, #2f4fcb);
    box-shadow: 0 8px 14px rgba(14, 24, 74, 0.36);
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
}

.button-item span {
    color: #fff;
    margin: 0;
}

.select-hospital {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    flex: 1;
    border: none;
    background: transparent;
    color: #432654;
    padding: 0.55rem 0.4rem;
    font-size: 0.95rem;
}

.select-hospital:focus {
    outline: none;
}

.select-hospital option {
    color: #3b1d4a;
    background: #f7f1ff;
}

.select-hospital option:checked,
.select-hospital option:focus {
    background: #6b3d7d;
    color: #fff;
}

.header_div {
    background: linear-gradient(135deg, #5a2f78, #a06bd4);
    color: #fff;
    border-radius: 32px;
    padding: 18px 26px;
    box-shadow: 0 25px 50px rgba(24, 0, 30, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin: 8px 0 4px;
    font-weight: 600;
    letter-spacing: 0.02em;
}

.scope-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 10px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    background: #f3edff;
    border: 1px solid #d6c5f7;
    color: #5e2363;
}
</style>

<script src="js/timeout.js"></script>

<div id='main-container'>
    <div class="container-fluid" style="margin-top:6px">
        <?php if ($isSeguradoraRole): ?>
            <div class="scope-badge">
                Escopo: Seguradora <?= htmlspecialchars($seguradoraUserNome !== '' ? $seguradoraUserNome : ('#' . $seguradoraUserId), ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <div class="grid-container">
            <div class="grid-item grid-item-filter">
                <div class="title-item"><i class="fa-solid fa-hospital"></i> Filtrar Hospital</div>
                <form id="filter-status-form" method="POST">
                    <div class="select-item">
                        <div class="select-wrapper">
                            <div class="select-shell">
                                <select name="hospital_id" id="hospital_id"
                                    class="form-control form-control-md select-hospital">
                                    <option value=""><?= htmlspecialchars($hospital_name, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                    <?php foreach ($dados_hospital_select as $hospital1):
                                        $hid = (int)$hospital1['id_hospital'];
                                        $hn  = (string)$hospital1['nome_hosp'];
                                    ?>
                                    <option value="<?= $hid ?>" <?= ($hospital_selecionado === $hid ? 'selected' : '') ?>>
                                        <?= htmlspecialchars($hn !== '' ? $hn : ('Hospital #' . $hid), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="select-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                                <button type="submit" class="btn button-item">
                                    <span class="material-icons">search</span>
                                </button>
                                <button type="submit" name="clear_hospital" value="1" class="btn button-item" title="Limpar filtro hospital">
                                    <span class="material-icons">close</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="kpi-grid-container">
            <div class="grid-item grid-item-kpi kpi-neutral">
                <div class="title-item"><i class="fa-solid fa-bed"></i> Total Internados</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-neutral"><?= count($dados_internacoes) ?></div>
            </div>

            <div class="grid-item grid-item-kpi kpi-warning">
                <div class="title-item"><i class="fa-solid fa-clock"></i> Longa Permanência</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-warning"><?= !empty($longa_perm) ? count($longa_perm) : 0 ?></div>
            </div>

            <div class="grid-item grid-item-kpi kpi-warning">
                <div class="title-item"><i class="fa-solid fa-bars-progress"></i> Reinternações &lt; 2 dias</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-warning"><?= $total_reinternacoes ?? 0 ?></div>
            </div>

            <div class="grid-item grid-item-kpi kpi-warning">
                <div class="title-item"><i class="fa-solid fa-calendar"></i> Visitas em Atraso</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-warning"><?= count($dados_visitas_atraso) ?></div>
            </div>

            <div class="grid-item grid-item-kpi kpi-critical">
                <div class="title-item"><i class="fa-solid fa-stethoscope"></i> Acima meta DRG</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-critical"><?= $drg_acima[0] ?? 0 ?></div>
            </div>

            <div class="grid-item grid-item-kpi kpi-info">
                <div class="title-item"><i class="fa-solid fa-dollar-sign"></i> Contas em Auditoria</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-info"><?= is_array($dados_capeante) ? count($dados_capeante) : 0 ?></div>
            </div>

            <div class="grid-item grid-item-kpi kpi-critical">
                <div class="title-item"><i class="fa-solid fa-circle-stop"></i> Contas Paradas</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-critical"><?= $contas_paradas[0] ?? 0 ?></div>
            </div>

            <div class="grid-item grid-item-kpi kpi-info">
                <div class="title-item"><i class="fa-solid fa-percent"></i> Porcentagem em UTI</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-info"><?= $perc_uti[0] ?? "0.00%" ?></div>
            </div>

            <div class="grid-item grid-item-kpi kpi-critical">
                <div class="title-item"><i class="fa-solid fa-heart"></i> UTI Não Pertinente</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item badge-critical"><?= $uti_nao_pertinente[0] ?? 0 ?></div>
            </div>
        </div>
    </div>

    <div class=" container-fluid">
        <div class="row m-t-25">
            <div class="col-12">
                <div class="header_div">
                    <span>Visitas em atraso</span>
                </div>
                <div id="dash-visitas-atraso" class="dash-table-loading">
                    Carregando...
                </div>
            </div>

            <div class="col-12" style="margin-top:20px;">
                <div class="header_div">
                    <span>Pacientes de longa permanência</span>
                </div>
                <div id="dash-longa-perm" class="dash-table-loading">
                    Carregando...
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row m-t-25">
            <div class="col-12">
                <div class="header_div d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <span>Previsão de permanência (IA)</span>
                        <i class="fa-solid fa-robot" style="color:white; margin-left:10px;"></i>
                    </div>
                    <small style="color:#f1f1f1">
                        Modelo <?= htmlspecialchars($forecastSummary['model'] ?? 'n/d', ENT_QUOTES, 'UTF-8') ?> ·
                        <?= (int)($forecastSummary['updated'] ?? 0) ?> recalculados agora
                    </small>
                </div>
                <table class="table table-sm table-striped table-hover table-condensed" style="margin-top:10px;">
                    <thead style="background: linear-gradient(135deg, #7a3a80, #5a296a);">
                        <tr>
                            <th style="width:18%">Hospital</th>
                            <th style="width:22%">Paciente</th>
                            <th style="width:12%">Dias atuais</th>
                            <th style="width:14%">Previsto (dias)</th>
                            <th style="width:14%">Alta estimada</th>
                            <th style="width:12%">Intervalo</th>
                            <th style="width:8%">Conf.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ((array)$forecastRows as $prev): ?>
                        <?php
                            $diasAtuais = (int)($prev['dias_internado'] ?? 0);
                            $prevTotal = isset($prev['forecast_total_days']) ? (float)$prev['forecast_total_days'] : null;
                            $tempoRestante = $prevTotal !== null ? round($prevTotal - $diasAtuais, 1) : null;
                            $lower = isset($prev['forecast_lower_days']) ? (float)$prev['forecast_lower_days'] : null;
                            $upper = isset($prev['forecast_upper_days']) ? (float)$prev['forecast_upper_days'] : null;
                            $confidence = isset($prev['forecast_confidence']) ? (int)$prev['forecast_confidence'] : null;
                            $statusClass = 'badge bg-secondary';
                            $statusLabel = 'Sem IA';
                            if ($tempoRestante !== null) {
                                if ($tempoRestante <= 0) {
                                    $statusClass = 'badge bg-danger';
                                    $statusLabel = 'Atrasado';
                                } elseif ($tempoRestante <= 2) {
                                    $statusClass = 'badge bg-warning text-dark';
                                    $statusLabel = 'Risco';
                                } else {
                                    $statusClass = 'badge bg-success';
                                    $statusLabel = 'No prazo';
                                }
                            }
                            $altaEstimativa = '-';
                            if (!empty($prev['data_intern_int']) && $prevTotal !== null) {
                                try {
                                    $altaDate = new DateTime($prev['data_intern_int']);
                                    $altaDate->modify('+' . ceil($prevTotal) . ' days');
                                    $altaEstimativa = $altaDate->format('d/m');
                                } catch (Throwable $e) {
                                    $altaEstimativa = '-';
                                }
                            }
                            $intervaloTexto = ($lower !== null && $upper !== null)
                                ? sprintf('%sd - %sd', round($lower), round($upper))
                                : '—';
                            $tempoRestanteTexto = $tempoRestante !== null
                                ? sprintf('%s%s d', $tempoRestante > 0 ? '+' : '', $tempoRestante)
                                : '—';
                            $confTexto = $confidence ? $confidence . '%' : '—';
                            $atualizadoEm = '-';
                            if (!empty($prev['forecast_generated_at'])) {
                                try {
                                    $atualizadoEm = (new DateTime($prev['forecast_generated_at']))->format('d/m H:i');
                                } catch (Throwable $e) {
                                    $atualizadoEm = '-';
                                }
                            }
                            ?>
                        <tr style="font-size:15px">
                            <td>
                                <?= htmlspecialchars($prev['nome_hosp'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                                <span class="<?= $statusClass ?>" style="font-size:0.75rem;">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= $BASE_URL ?>show_internacao.php?id_internacao=<?= (int)$prev['id_internacao'] ?>">
                                    <i class="bi bi-box-arrow-up-right fw-bold"
                                        style="margin-right:6px; font-size:1.1em;"></i>
                                </a>
                                <?= htmlspecialchars($prev['nome_pac'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                                <small class="text-muted">Atualizado <?= $atualizadoEm ?></small>
                            </td>
                            <td><?= $diasAtuais ?> d</td>
                            <td>
                                <?= $prevTotal !== null ? round($prevTotal, 1) . ' d' : '—' ?><br>
                                <?php if ($tempoRestante !== null): ?>
                                <span class="fw-semibold"><?= htmlspecialchars($tempoRestanteTexto, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $altaEstimativa ?></td>
                            <td><?= $intervaloTexto ?></td>
                            <td><?= $confTexto ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count((array)$forecastRows) === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center" style="font-size:15px;">
                                Sem registros para os filtros aplicados.
                                <?= $isSeguradoraRole ? ' Você está visualizando somente dados da sua seguradora.' : '' ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    try {
        var ctx = document.getElementById("sales-chart2");
        if (ctx) {
            ctx.height = 150;
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["2010", "2011", "2012", "2013", "2014", "2015", "2016"],
                    type: 'line',
                    defaultFontFamily: 'Poppins',
                    datasets: [{
                        label: "Foods",
                        data: [0, 30, 10, 120, 50, 63, 10],
                        backgroundColor: 'transparent',
                        borderColor: 'rgba(220,53,69,0.75)',
                        borderWidth: 3,
                        pointStyle: 'circle',
                        pointRadius: 5,
                        pointBorderColor: 'transparent',
                        pointBackgroundColor: 'rgba(220,53,69,0.75)',
                    }, {
                        label: "Electronics",
                        data: [0, 50, 40, 80, 40, 79, 120],
                        backgroundColor: 'transparent',
                        borderColor: 'rgba(40,167,69,0.75)',
                        borderWidth: 3,
                        pointStyle: 'circle',
                        pointRadius: 5,
                        pointBorderColor: 'transparent',
                        pointBackgroundColor: 'rgba(40,167,69,0.75)',
                    }]
                },
                options: {
                    responsive: true,
                    tooltips: {
                        mode: 'index',
                        titleFontSize: 12,
                        titleFontColor: '#000',
                        bodyFontColor: '#000',
                        backgroundColor: '#fff',
                        titleFontFamily: 'Poppins',
                        bodyFontFamily: 'Poppins',
                        cornerRadius: 3,
                        intersect: false
                    },
                    legend: {
                        display: false,
                        labels: {
                            usePointStyle: true,
                            fontFamily: 'Poppins'
                        }
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            scaleLabel: {
                                display: false,
                                labelString: 'Month'
                            },
                            ticks: {
                                fontFamily: "Poppins"
                            }
                        }],
                        yAxes: [{
                            display: true,
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Value',
                                fontFamily: "Poppins"
                            },
                            ticks: {
                                fontFamily: "Poppins"
                            }
                        }]
                    },
                    title: {
                        display: false,
                        text: 'Normal Legend'
                    }
                }
            });
        }
    } catch (error) {
    }

    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    document.addEventListener('DOMContentLoaded', function() {
        const selectElement = document.getElementById('hospital_id');
        selectElement.addEventListener('focus', function() {
            selectElement.classList.add('open');
        });
        selectElement.addEventListener('blur', function() {
            selectElement.classList.remove('open');
        });

        function parseDashValue(value, type) {
            const text = (value || '').trim();
            if (type === 'number') {
                const num = parseFloat(text.replace(/[^\d.-]/g, ''));
                return Number.isFinite(num) ? num : -Infinity;
            }
            if (type === 'date') {
                const parts = text.split('/');
                if (parts.length === 3) {
                    return Number(parts[2] + parts[1].padStart(2, '0') + parts[0].padStart(2, '0'));
                }
                return -Infinity;
            }
            return text.toLowerCase();
        }

        function sortDashTable(table, colIndex, dir, type) {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort(function(a, b) {
                const aCell = a.children[colIndex];
                const bCell = b.children[colIndex];
                const aVal = parseDashValue(aCell ? aCell.textContent : '', type);
                const bVal = parseDashValue(bCell ? bCell.textContent : '', type);
                if (type === 'text') {
                    return dir === 'asc' ? aVal.localeCompare(bVal, 'pt-BR') : bVal.localeCompare(aVal, 'pt-BR');
                }
                return dir === 'asc' ? (aVal - bVal) : (bVal - aVal);
            });
            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        }

        document.addEventListener('click', function(event) {
            const link = event.target.closest('.dash-sortable .sort-icons a');
            if (!link) return;
            event.preventDefault();
            const th = link.closest('th');
            const table = link.closest('table');
            if (!th || !table) return;
            const dir = link.getAttribute('data-dir') || 'asc';
            const type = th.getAttribute('data-sort-type') || 'text';
            const colIndex = th.cellIndex;

            table.querySelectorAll('.sort-icons a').forEach(function(a) {
                a.classList.remove('active');
            });
            link.classList.add('active');

            sortDashTable(table, colIndex, dir, type);
        });

        function loadDashTables() {
            const visitasEl = document.getElementById('dash-visitas-atraso');
            const longaEl = document.getElementById('dash-longa-perm');
            if (!visitasEl || !longaEl) return;

            const formData = new URLSearchParams();
            const hospVal = selectElement ? selectElement.value : '';
            if (hospVal) formData.append('hospital_id', hospVal);

            fetch('<?= $BASE_URL ?>ajax/dashboard_tabelas.php?_ts=' + Date.now(), {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(function(html) {
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const visitasContent = temp.querySelector('#dash-visitas-atraso-content');
                const longaContent = temp.querySelector('#dash-longa-perm-content');
                visitasEl.innerHTML = visitasContent ? visitasContent.innerHTML : '<div style="padding:10px">Não foi possível carregar.</div>';
                longaEl.innerHTML = longaContent ? longaContent.innerHTML : '<div style="padding:10px">Não foi possível carregar.</div>';
            })
            .catch(function() {
                visitasEl.innerHTML = '<div style="padding:10px">Erro ao carregar.</div>';
                longaEl.innerHTML = '<div style="padding:10px">Erro ao carregar.</div>';
            });
        }

        loadDashTables();
    });
    </script>
</div>
</body>

</html>

<style>
.container {
    width: 100%;
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.chart-container {
    max-width: calc(33% - 10px);
    flex-grow: 1;
    margin: 0 5px;
    border: none;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
}

.container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

.div {
    width: calc(33.33% - 20px);
    margin: 10px;
    height: 120px;
    border: none;
    background-color: none;
}

.header_div spam {
    margin: 0;
    color: white;
}

canvas {
    width: 100%;
    border: none;
}

.dash-table-loading {
    margin-top: 10px;
    min-height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-weight: 600;
    background: #f8f7fb;
    border-radius: 10px;
    border: 1px dashed rgba(94, 35, 99, 0.2);
}

.dash-table-scroll {
    margin-top: 10px;
    max-height: 420px;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 10px;
    width: 100%;
}

.dash-table-scroll table {
    width: 100%;
}

.dash-table-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: linear-gradient(135deg, #7a3a80, #5a296a);
}

.th-sortable {
    white-space: nowrap;
}

.th-sortable .sort-icons {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    margin-left: 6px;
    vertical-align: middle;
}

.th-sortable .sort-icons a {
    text-decoration: none;
    font-size: 0.75rem;
    color: #ffffff;
    margin-left: 2px;
    opacity: 0.7;
}

.th-sortable .sort-icons a.active {
    color: #ffd966;
    opacity: 1;
    font-weight: bold;
}
</style>

<!-- Jquery JS-->
<script src="diversos/CoolAdmin-master/vendor/jquery-3.2.1.min.js"></script>
<!-- Bootstrap JS-->
<script src="diversos/CoolAdmin-master/vendor/bootstrap-4.1/popper.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/bootstrap-4.1/bootstrap.min.js"></script>
<!-- Vendor JS       -->
<script src="diversos/CoolAdmin-master/vendor/slick/slick.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/wow/wow.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/animsition/animsition.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/counter-up/jquery.waypoints.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/counter-up/jquery.counterup.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/circle-progress/circle-progress.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="diversos/chartjs/Chart.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/select2/select2.min.js"></script>
<!-- Main JS-->
<script src="diversos/CoolAdmin-master/js/main.js"></script>
<script src="scripts/cadastro/general.js"></script>
<!-- <script src="js/ajaxNav.js"></script> -->

<?php require_once("templates/footer.php"); ?>
