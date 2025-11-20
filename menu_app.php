<?php
// DEBUG TEMPORÁRIO (REMOVER APÓS TESTE)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
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

// -----------------------------
// ENTRADAS E SESSÃO
// -----------------------------
$hospital_selecionado = isset($_POST['hospital_id']) ? (int)$_POST['hospital_id'] : 0;
$id_usuario_sessao    = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;
$nivel_sessao         = isset($_SESSION['nivel']) ? (int)$_SESSION['nivel'] : 99;

// -----------------------------
// CONDIÇÕES / WHEREs
// -----------------------------
$condicoes = [
    $hospital_selecionado ? "ac.fk_hospital_int = {$hospital_selecionado}" : null,
    ($id_usuario_sessao && $nivel_sessao <= 3) ? "hos.fk_usuario_hosp = {$id_usuario_sessao}" : null
];

$condicoes_vis = [
    $hospital_selecionado ? "ac.fk_hospital_int = {$hospital_selecionado}" : null,
    "ac.internado_int = 's'",
    "(vi.id_visita = (SELECT MAX(vi2.id_visita) FROM tb_visita vi2 WHERE vi2.fk_internacao_vis = ac.id_internacao) OR vi.id_visita IS NULL)"
];

$condicoes_hospital = [
    "DATEDIFF(CURRENT_DATE(), data_intern_int) > longa_permanencia_seg",
    $hospital_selecionado ? "i.fk_hospital_int = {$hospital_selecionado}" : null,
    ($id_usuario_sessao && $nivel_sessao <= 3) ? "hos.fk_usuario_hosp = {$id_usuario_sessao}" : null,
    "i.internado_int = 's'",
    ($id_usuario_sessao && $nivel_sessao <= 3) ? "i.fk_hospital_int IN (SELECT hos.fk_hospital_user FROM tb_hospitalUser hos WHERE hos.fk_usuario_hosp = {$id_usuario_sessao})" : null
];

$condicoes_contas = [
    "c.conta_parada_cap = 's'",
    $hospital_selecionado ? "i.fk_hospital_int = {$hospital_selecionado}" : null,
    ($id_usuario_sessao && $nivel_sessao <= 3) ? "i.fk_hospital_int IN (SELECT hos.fk_hospital_user FROM tb_hospitalUser hos WHERE hos.fk_usuario_hosp = {$id_usuario_sessao})" : null
];

$condicoes_gerais = [
    $hospital_selecionado ? "i.fk_hospital_int = {$hospital_selecionado}" : null,
    ($id_usuario_sessao && $nivel_sessao <= 3) ? "i.fk_hospital_int IN (SELECT hos.fk_hospital_user FROM tb_hospitalUser hos WHERE hos.fk_usuario_hosp = {$id_usuario_sessao})" : null
];

$condicoes_gerais_reint = [
    $hospital_selecionado ? "ac.fk_hospital_int = {$hospital_selecionado}" : null
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

// -----------------------------
// LISTA DE HOSPITAIS POR PERFIL
// -----------------------------
if ($nivel_sessao > 3) {
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
$dados_internacoes_geral   = $Internacao_geral->selectAllInternacaoList($where);
$dados_internacoes_uti     = $Internacao_geral->QtdInternacao("ac.internado_int = 's' AND ut.id_uti IS NOT NULL");
$dados_internacoes_visitas = $Internacao_geral->selectInternVisLastWhere($where_vis);

// Capeante (concatenação corrigida)
$capFilter  = "ca.em_auditoria_cap IS NULL";
$where_cap  = trim($where) !== '' ? ($where . " AND " . $capFilter) : $capFilter;
$dados_capeante = $Internacao_geral->selectAllInternacaoCapList($where_cap);

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

    if ($dtVisita instanceof DateTime) {
        $dias = ($dtVisita > $hoje) ? 0 : $dtVisita->diff($hoje)->days;
        return $dias > 10;
    }
    if ($dtIntern instanceof DateTime) {
        $dias = ($dtIntern > $hoje) ? 0 : $dtIntern->diff($hoje)->days;
        return $dias > 10;
    }
    return false;
}
$dados_visitas_atraso = array_filter((array)$dados_internacoes_visitas, 'filterVisitasAtrasadas');

// Ordena por data e pega os 8 mais recentes
usort($dados_visitas_atraso, function ($a, $b) {
    return strcmp($a['data_visita_vis'] ?? '', $b['data_visita_vis'] ?? '');
});
$dados_visitas_atraso_list = array_slice($dados_visitas_atraso, -8);

// Indicadores
$drg_acima          = $indicadores->getDrgAcima($where_gerais);
$perc_uti           = $indicadores->getUtiPerc($where_gerais);

// Longa permanência
$longa_perm         = $indicadores->getLongaPermanencia($where_hospital);
$longa_perm_list    = $indicadores->getLongaPermanencia($where_hospital);
if (!empty($longa_perm_list)) {
    usort($longa_perm_list, function ($a, $b) {
        return strcmp($a['data_intern_int'] ?? '', $b['data_intern_int'] ?? '');
    });
    $longa_perm_list = array_slice($longa_perm_list, -8);
} else {
    $longa_perm_list = [];
}

// Contas paradas
$contas_paradas     = $indicadores->getContasParadas($where_contas);

// UTI não pertinente
$uti_nao_pertinente = $indicadores->getUtiPertinente($where_gerais);

// Score baixo
$score_baixo        = $indicadores->getScoreBaixo($where_gerais);

// Reinternações
$reinternacaohosp    = $Internacao_geral->reinternacaoNova($where_gerais_reint);
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
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    grid-template-rows: repeat(2, 1fr);
    gap: 10px;
    width: 100%;
}

.grid-item {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 1.5em;
    border-radius: 5px;
    background: linear-gradient(to bottom, #5a296a, #7a3a80);
    height: 120px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
}

.title-item {
    position: absolute;
    top: 10px;
    left: 15px;
    font-size: 0.8em;
    color: white
}

.icon-item {
    position: absolute;
    bottom: 10px;
    left: 15px;
    font-size: 1.25em;
    color: #5bd9f3;
}

.badge-item {
    position: absolute;
    bottom: 15px;
    right: 15px;
    width: 100px;
    color: #9c27b0 !important;
    background-color: #f3e5f5 !important;
    padding: 5px 10px;
    border-radius: 10em;
    font-size: 0.9em;
    text-align: center;
}

.select-item {
    position: absolute;
    bottom: 18px;
    left: 15px;
    width: 90%;
}

.button-item {
    position: absolute;
    bottom: 2px;
    right: 5px;
    width: 60px;
}

.select-hospital {
    background: transparent;
    color: white;
}

#hospital_id.open {
    background-color: transparent !important;
    color: white
}

#hospital_id.open option {
    color: gray
}

.header_div {
    height: 40px;
    background: linear-gradient(135deg, #7a3a80, #5a296a);
    color: white;
    border-top-right-radius: 5px;
    border-top-left-radius: 5px;
    text-align: center;
}
</style>

<script src="js/timeout.js"></script>

<div id='main-container'>
    <div class="container-fluid" style="margin-top:20px">
        <div class="grid-container">
            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-hospital"></i> Filtrar Hospital</div>
                <form id="filter-status-form" method="POST">
                    <div class="select-item">
                        <select name="hospital_id" id="hospital_id" class="form-control form-control-md select-hospital"
                            style="margin-bottom:4px;">
                            <option value=""><?= htmlspecialchars($hospital_name, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php foreach ($dados_hospital_select as $hospital1):
                                $hid = (int)$hospital1['id_hospital'];
                                $hn  = (string)$hospital1['nome_hosp'];
                            ?>
                            <option value="<?= $hid ?>" <?= ($hospital_selecionado === $hid ? 'selected' : '') ?>>
                                <?= htmlspecialchars($hn !== '' ? $hn : ('Hospital #' . $hid), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn button-item"
                            style="background-color:transparent;width:42px;height:37px;border:none;">
                            <span class="material-icons"
                                style="margin-left:-3px;margin-top:-2px;color:white">search</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-bed"></i> Total Internados</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= count($dados_internacoes) ?></div>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-clock"></i> Longa Permanência</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= !empty($longa_perm) ? count($longa_perm) : 0 ?></div>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-bars-progress"></i> Reinternações &lt; 2 dias</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= $total_reinternacoes ?? 0 ?></div>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-calendar"></i> Visitas em Atraso</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= count($dados_visitas_atraso) ?></div>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-stethoscope"></i> Acima meta DRG</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= $drg_acima[0] ?? 0 ?></div>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-dollar-sign"></i> Contas em Auditoria</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= is_array($dados_capeante) ? count($dados_capeante) : 0 ?></div>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-circle-stop"></i> Contas Paradas</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= $contas_paradas[0] ?? 0 ?></div>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-percent"></i> Porcentagem em UTI</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= $perc_uti[0] ?? "0.00%" ?></div>
            </div>

            <div class="grid-item">
                <div class="title-item"><i class="fa-solid fa-heart"></i> UTI Não Pertinente</div>
                <div class="icon-item"><i class="fa-solid fa-chart-simple"></i></div>
                <div class="badge-item"><?= $uti_nao_pertinente[0] ?? 0 ?></div>
            </div>
        </div>
    </div>

    <div class=" container-fluid">
        <div class="row m-t-25">
            <div class="col-sm-6 col-lg-6">
                <div class="header_div">
                    <spam>Visitas em atraso</spam>
                    <i style="color:white; margin-left:10px;margin-top:10px;float:left"
                        class="fa-solid fa-right-to-bracket"></i>
                </div>
                <table style="margin-top:10px;" class="table table-sm table-striped table-hover table-condensed">
                    <thead style="background: linear-gradient(135deg, #7a3a80, #5a296a);">
                        <tr>
                            <th scope="col" style="width:3%">Hospital</th>
                            <th scope="col" style="width:3%">Paciente</th>
                            <th scope="col" style="width:3%">Ultima Visita</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados_visitas_atraso_list as $intern): ?>
                        <?php
                            if (!empty($intern["data_visita_vis"])) {
                                $date = new DateTime($intern["data_visita_vis"]);
                                $formattedDate = $date->format('d/m/Y');
                            } else {
                                $formattedDate = "Sem visita";
                            }
                            ?>
                        <tr style="font-size:15px">
                            <td scope="row">
                                <?= htmlspecialchars($intern["nome_hosp"] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td scope="row">
                                <a
                                    href="<?= $BASE_URL ?>cad_visita.php?id_internacao=<?= (int)($intern["id_internacao"] ?? 0) ?>">
                                    <i class="bi bi-box-arrow-in-right fw-bold"
                                        style="margin-right:8px; font-size:1.2em;"></i>
                                </a>
                                <?= htmlspecialchars($intern["nome_pac"] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td scope="row"><?= $formattedDate ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (count($dados_visitas_atraso_list) == 0): ?>
                        <tr>
                            <td colspan="3" scope="row" class="col-id" style='font-size:15px'>
                                Não foram encontrados registros
                            </td>
                        </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>

            <div class="col-sm-6 col-lg-6">
                <div class="header_div">
                    <spam>Pacientes de longa permanência</spam>
                    <i style="color:white; margin-left:10px;margin-top:10px;float:left"
                        class="fa-solid fa-right-to-bracket"></i>
                </div>
                <table style="margin-top:10px;" class="table table-sm table-striped table-hover table-condensed">
                    <thead style="background: linear-gradient(135deg, #7a3a80, #5a296a);">
                        <tr>
                            <th scope="col" style="width:3%">Hospital</th>
                            <th scope="col" style="width:3%">Paciente</th>
                            <th scope="col" style="width:3%">Data Internação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($longa_perm_list as $intern): ?>
                        <?php
                            if (!empty($intern["data_intern_int"])) {
                                $date = new DateTime($intern["data_intern_int"]);
                                $formattedDate = $date->format('d/m/Y');
                            } else {
                                $formattedDate = "Sem visita";
                            }
                            ?>
                        <tr style="font-size:15px">
                            <td scope="row">
                                <?= htmlspecialchars($intern["nome_hosp"] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td scope="row">
                                <a
                                    href="<?= $BASE_URL ?>show_internacao.php?id_internacao=<?= (int)($intern["id_internacao"] ?? 0) ?>">
                                    <i class="bi bi-box-arrow-right"
                                        style="color:green; margin-right:8px; font-size:1.2em;"></i>
                                </a>
                                <?= htmlspecialchars($intern["nome_pac"] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td scope="row"><?= $formattedDate ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (count($longa_perm_list) == 0): ?>
                        <tr>
                            <td colspan="3" scope="row" class="col-id" style='font-size:15px'>
                                Não foram encontrados registros
                            </td>
                        </tr>
                        <?php endif ?>
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
        console.log(error);
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
<script src="diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="diversos/CoolAdmin-master/vendor/select2/select2.min.js"></script>
<!-- Main JS-->
<script src="diversos/CoolAdmin-master/js/main.js"></script>
<script src="scripts/cadastro/general.js"></script>
<!-- <script src="js/ajaxNav.js"></script> -->

<?php require_once("templates/footer.php"); ?>