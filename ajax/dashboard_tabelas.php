<?php
define('SKIP_HEADER', true);
chdir(__DIR__ . '/..');
require_once(__DIR__ . '/../globals.php');
include_once(__DIR__ . '/../check_logado.php');
include_once(__DIR__ . '/../models/internacao.php');
include_once(__DIR__ . '/../dao/internacaoDao.php');
include_once(__DIR__ . '/../dao/indicadoresDao.php');

header('Content-Type: text/html; charset=utf-8');

try {

$hospital_selecionado = (int)(filter_input(INPUT_POST, 'hospital_id', FILTER_SANITIZE_NUMBER_INT)
    ?: filter_input(INPUT_GET, 'hospital_id', FILTER_SANITIZE_NUMBER_INT));
$id_usuario_sessao    = (int)($_SESSION['id_usuario'] ?? 0);
$nivel_sessao         = (int)($_SESSION['nivel'] ?? 99);
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
        error_log('[DASH_TABELAS][SEGURADORA] ' . $e->getMessage());
    }
}

$condicoes_vis = [
    $hospital_selecionado ? "ac.fk_hospital_int = {$hospital_selecionado}" : null,
    "ac.internado_int = 's'",
    "(vi.id_visita = (SELECT MAX(vi2.id_visita) FROM tb_visita vi2 WHERE vi2.fk_internacao_vis = ac.id_internacao) OR vi.id_visita IS NULL)",
    $isSeguradoraRole
        ? ($seguradoraUserId > 0 ? "pa.fk_seguradora_pac = {$seguradoraUserId}" : '1=0')
        : null
];
$condicoes_hospital = [
    "DATEDIFF(CURRENT_DATE(), i.data_intern_int) > COALESCE(s.longa_permanencia_seg, 0)",
    $hospital_selecionado ? "i.fk_hospital_int = {$hospital_selecionado}" : null,
    (!$isSeguradoraRole && $id_usuario_sessao && $nivel_sessao <= 3) ? "hos.fk_usuario_hosp = {$id_usuario_sessao}" : null,
    "i.internado_int = 's'",
    (!$isSeguradoraRole && $id_usuario_sessao && $nivel_sessao <= 3) ? "i.fk_hospital_int IN (SELECT hu.fk_hospital_user FROM tb_hospitalUser hu WHERE hu.fk_usuario_hosp = {$id_usuario_sessao})" : null,
    $isSeguradoraRole
        ? ($seguradoraUserId > 0 ? "p.fk_seguradora_pac = {$seguradoraUserId}" : '1=0')
        : null
];

$where_vis      = implode(' AND ', array_filter($condicoes_vis));
$where_hospital = implode(' AND ', array_filter($condicoes_hospital));

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$indicadores      = new indicadoresDAO($conn, $BASE_URL);

$dados_internacoes_visitas = $Internacao_geral->selectInternVisLastWhere($where_vis);

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

$dados_visitas_atraso = [];
foreach ((array)$dados_internacoes_visitas as $v) {
    $diasUlt = diasDesdeData($v['data_visita_vis'] ?? ($v['data_visita_int'] ?? null));
    if ($diasUlt === null) continue;
    $limite = (int)($v['dias_visita_seg'] ?? 0);
    if ($limite <= 0) $limite = 10;
    $atraso = $diasUlt - $limite;
    if ($atraso <= 0) continue;
    $v['_dias_atraso'] = $atraso;
    $dados_visitas_atraso[] = $v;
}
usort($dados_visitas_atraso, function ($a, $b) {
    return ($b['_dias_atraso'] ?? 0) <=> ($a['_dias_atraso'] ?? 0);
});
$dados_visitas_atraso_list = array_slice($dados_visitas_atraso, 0, 50);

$longa_perm = $indicadores->getLongaPermanencia($where_hospital);
$longa_perm_list = $longa_perm;
if (!empty($longa_perm_list)) {
    usort($longa_perm_list, function ($a, $b) {
        $da = diasDesdeData($a['data_intern_int'] ?? null) ?? 0;
        $db = diasDesdeData($b['data_intern_int'] ?? null) ?? 0;
        return $db <=> $da; // mais dias internado primeiro
    });
$longa_perm_list = array_slice($longa_perm_list, 0, 50);
} else {
    $longa_perm_list = [];
}

} catch (Throwable $e) {
    error_log('[DASH_TABELAS] ' . $e->getMessage());
    echo '<div id="dash-visitas-atraso-content"><div style="padding:10px">Erro ao carregar.</div></div>';
    echo '<div id="dash-longa-perm-content"><div style="padding:10px">Erro ao carregar.</div></div>';
    exit;
}
?>

<div id="dash-visitas-atraso-content">
    <div class="dash-table-scroll">
    <table style="margin-top:10px;" class="table table-sm table-striped table-hover table-condensed dash-sortable">
        <thead style="background: linear-gradient(135deg, #7a3a80, #5a296a);">
            <tr>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="number">Id Int
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="text">Hospital
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="text">Seguradora
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="text">Paciente
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="date">Ultima Visita
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="number">Dias última visita
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
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
                $diasUltimaVisita = diasDesdeData($intern["data_visita_vis"] ?? null);
                if ($diasUltimaVisita === null) {
                    $diasUltimaVisita = diasDesdeData($intern["data_visita_int"] ?? null);
                }
                $limiteDiasVisita = (int)($intern["dias_visita_seg"] ?? 0);
                if ($limiteDiasVisita <= 0) {
                    $limiteDiasVisita = 10;
                }
                $classeDiasVisita = '';
                if ($diasUltimaVisita !== null && $limiteDiasVisita > 0) {
                    if ($diasUltimaVisita >= $limiteDiasVisita) {
                        $classeDiasVisita = 'text-danger fw-semibold';
                    } elseif ($diasUltimaVisita === ($limiteDiasVisita - 1)) {
                        $classeDiasVisita = 'text-warning fw-semibold';
                    } else {
                        $classeDiasVisita = 'text-success fw-semibold';
                    }
                }
                ?>
            <tr style="font-size:15px">
                <td scope="row"><?= (int)($intern["id_internacao"] ?? 0) ?></td>
                <td scope="row">
                    <?= htmlspecialchars($intern["nome_hosp"] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td scope="row">
                    <?= htmlspecialchars($intern["seguradora_seg"] ?? '—', ENT_QUOTES, 'UTF-8') ?>
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
                <td scope="row" class="<?= $classeDiasVisita ?>">
                    <?= $diasUltimaVisita !== null ? (int)$diasUltimaVisita . ' dias' : '—' ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($dados_visitas_atraso_list) == 0): ?>
            <tr>
                <td colspan="6" scope="row" class="col-id" style='font-size:15px'>
                    Não foram encontrados registros
                </td>
            </tr>
            <?php endif ?>
        </tbody>
    </table>
    </div>
</div>

<div id="dash-longa-perm-content">
    <div class="dash-table-scroll">
    <table style="margin-top:10px;" class="table table-sm table-striped table-hover table-condensed dash-sortable">
        <thead style="background: linear-gradient(135deg, #7a3a80, #5a296a);">
            <tr>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="number">Id Int
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="text">Hospital
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="text">Seguradora
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="text">Paciente
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="date">Data Internação
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="date">Última visita
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc">▼</a>
                    </span>
                </th>
                <th scope="col" style="width:3%" class="th-sortable" data-sort-type="number">Dias Internacao
                    <span class="sort-icons">
                        <a href="#" data-dir="asc">▲</a>
                        <a href="#" data-dir="desc" class="active">▼</a>
                    </span>
                </th>
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
                $diasUltimaVisita = null;
                $ultimaVisitaData = null;
                $idIntern = (int)($intern["id_internacao"] ?? 0);
                if ($idIntern > 0 && isset($ultimaVisitaPorInternacao[$idIntern])) {
                    $rawData = $ultimaVisitaPorInternacao[$idIntern]['data'] ?? null;
                    if (!empty($rawData)) {
                        try {
                            $ultimaVisitaData = (new DateTime($rawData))->format('d/m/Y');
                        } catch (Throwable $e) {
                            $ultimaVisitaData = null;
                        }
                        $diasUltimaVisita = diasDesdeData($rawData);
                    }
                }
                $diasInternacao = diasDesdeData($intern["data_intern_int"] ?? null);
                ?>
            <tr style="font-size:15px">
                <td scope="row"><?= (int)($intern["id_internacao"] ?? 0) ?></td>
                <td scope="row">
                    <?= htmlspecialchars($intern["nome_hosp"] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td scope="row">
                    <?= htmlspecialchars($intern["seguradora_seg"] ?? '—', ENT_QUOTES, 'UTF-8') ?>
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
                <td scope="row"><?= $ultimaVisitaData ?? '—' ?></td>
                <td scope="row" class="text-danger fw-semibold">
                    <?= $diasInternacao !== null ? $diasInternacao . ' dias' : '—' ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($longa_perm_list) == 0): ?>
            <tr>
                <td colspan="7" scope="row" class="col-id" style='font-size:15px'>
                    Não foram encontrados registros
                </td>
            </tr>
            <?php endif ?>
        </tbody>
    </table>
    </div>
</div>
