<?php
require_once("templates/header.php");
require_once("models/message.php");

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function dateToTs(?string $date): ?int
{
    if (!$date) {
        return null;
    }
    $ts = strtotime($date);
    return $ts ? (int)$ts : null;
}

function daysExclusive(int $startTs, int $endTs): int
{
    if ($endTs <= $startTs) {
        return 0;
    }
    return (int)floor(($endTs - $startTs) / 86400);
}

function tsToDate(int $ts): string
{
    return date('d/m/Y', $ts);
}

function computeCoverageAndGaps(array $intervals, int $startTs, int $endTs): array
{
    if (!$intervals) {
        return [0, daysExclusive($startTs, $endTs), [[tsToDate($startTs), tsToDate($endTs)]]];
    }

    usort($intervals, fn($a, $b) => $a['s'] <=> $b['s']);

    $coveredDays = 0;
    $gaps = [];
    $curS = $intervals[0]['s'];
    $curE = $intervals[0]['e'];

    foreach ($intervals as $idx => $it) {
        if ($idx === 0) {
            continue;
        }
        if ($it['s'] <= $curE) {
            if ($it['e'] > $curE) {
                $curE = $it['e'];
            }
            continue;
        }
        if ($curS > $startTs) {
            $gapStart = $startTs;
            $gapEnd = $curS;
            if ($gapEnd > $gapStart) {
                $gaps[] = [tsToDate($gapStart), tsToDate($gapEnd)];
            }
        }
        $coveredDays += daysExclusive($curS, $curE);
        $curS = $it['s'];
        $curE = $it['e'];
    }

    if ($curS > $startTs) {
        $gapStart = $startTs;
        $gapEnd = $curS;
        if ($gapEnd > $gapStart) {
            $gaps[] = [tsToDate($gapStart), tsToDate($gapEnd)];
        }
    }
    $coveredDays += daysExclusive($curS, $curE);

    if ($curE < $endTs) {
        $gapStart = $curE;
        $gapEnd = $endTs;
        if ($gapEnd > $gapStart) {
            $gaps[] = [tsToDate($gapStart), tsToDate($gapEnd)];
        }
    }

    $totalDays = daysExclusive($startTs, $endTs);
    $missingDays = max(0, $totalDays - $coveredDays);

    return [$coveredDays, $missingDays, $gaps];
}

$pesquisa_pac  = trim((string)filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS));
$pesquisa_hosp = trim((string)filter_input(INPUT_GET, 'pesquisa_hosp', FILTER_SANITIZE_SPECIAL_CHARS));
$data_ini      = filter_input(INPUT_GET, 'data_ini') ?: date('Y-m-d', strtotime('-90 days'));
$data_fim      = filter_input(INPUT_GET, 'data_fim') ?: date('Y-m-d');
$limite        = (int)(filter_input(INPUT_GET, 'limite') ?: 20);
$paginaAtual   = (int)(filter_input(INPUT_GET, 'pag') ?: 1);
$limite        = max(1, $limite);

$where = "i.data_intern_int BETWEEN :data_ini AND :data_fim";
$params = [
    ':data_ini' => $data_ini,
    ':data_fim' => $data_fim,
];
if ($pesquisa_pac !== '') {
    $where .= " AND pa.nome_pac LIKE :pac";
    $params[':pac'] = '%' . $pesquisa_pac . '%';
}
if ($pesquisa_hosp !== '') {
    $where .= " AND ho.nome_hosp LIKE :hosp";
    $params[':hosp'] = '%' . $pesquisa_hosp . '%';
}

$sql = "
    SELECT
        i.id_internacao,
        i.data_intern_int,
        pa.nome_pac,
        ho.nome_hosp,
        alt.data_alta_alt
    FROM tb_internacao i
    LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
    LEFT JOIN tb_hospital ho ON ho.id_hospital = i.fk_hospital_int
    LEFT JOIN (
        SELECT fk_id_int_alt, MAX(data_alta_alt) AS data_alta_alt
        FROM tb_alta
        GROUP BY fk_id_int_alt
    ) alt ON alt.fk_id_int_alt = i.id_internacao
    WHERE {$where}
    ORDER BY i.data_intern_int DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$internacoes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$ids = array_values(array_filter(array_map(fn($r) => (int)($r['id_internacao'] ?? 0), $internacoes)));
$prorrogacoes = [];
if ($ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("
        SELECT fk_internacao_pror, prorrog1_ini_pror, prorrog1_fim_pror
        FROM tb_prorrogacao
        WHERE fk_internacao_pror IN ({$placeholders})
        ORDER BY fk_internacao_pror, prorrog1_ini_pror
    ");
    $stmt->execute($ids);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = (int)($row['fk_internacao_pror'] ?? 0);
        if ($id) {
            $prorrogacoes[$id][] = $row;
        }
    }
}

$pendentes = [];
$hoje = date('Y-m-d');
foreach ($internacoes as $int) {
    $id = (int)($int['id_internacao'] ?? 0);
    if (!$id) {
        continue;
    }
    $startTs = dateToTs($int['data_intern_int'] ?? null);
    if (!$startTs) {
        continue;
    }
    $endStr = $int['data_alta_alt'] ?: $hoje;
    $endTs = dateToTs($endStr);
    if (!$endTs || $endTs < $startTs) {
        continue;
    }

    $intervals = [];
    $rows = $prorrogacoes[$id] ?? [];
    $prStart = null;
    $prEnd = null;
    foreach ($rows as $p) {
        $iniTs = dateToTs($p['prorrog1_ini_pror'] ?? null);
        if (!$iniTs) {
            continue;
        }
        $fimTs = dateToTs($p['prorrog1_fim_pror'] ?? null);
        if (!$fimTs) {
            $fimTs = $endTs;
        }
        if ($fimTs < $startTs || $iniTs > $endTs) {
            continue;
        }
        $iniTs = max($iniTs, $startTs);
        $fimTs = min($fimTs, $endTs);
        if ($prStart === null || $iniTs < $prStart) {
            $prStart = $iniTs;
        }
        if ($prEnd === null || $fimTs > $prEnd) {
            $prEnd = $fimTs;
        }
        $intervals[] = ['s' => $iniTs, 'e' => $fimTs];
    }

    [$coveredDays, $missingDays, $gaps] = computeCoverageAndGaps($intervals, $startTs, $endTs);
    if ($missingDays > 0) {
        $pendentes[] = [
            'id_internacao' => $id,
            'nome_pac' => $int['nome_pac'] ?? '',
            'nome_hosp' => $int['nome_hosp'] ?? '',
            'data_ini' => $int['data_intern_int'] ?? null,
            'data_fim' => $endStr,
            'prorrog_count' => count($rows),
            'missing_days' => $missingDays,
            'total_days' => daysExclusive($startTs, $endTs),
            'covered_days' => $coveredDays,
            'gaps' => $gaps,
            'prorrog_range_start' => $prStart ? tsToDate($prStart) : null,
            'prorrog_range_end' => $prEnd ? tsToDate($prEnd) : null,
            'data_alta' => $int['data_alta_alt'] ?? null,
        ];
    }
}

$total = count($pendentes);
$totalPaginas = max(1, (int)ceil($total / $limite));
$paginaAtual = max(1, min($paginaAtual, $totalPaginas));
$offset = ($paginaAtual - 1) * $limite;
$paginaItens = array_slice($pendentes, $offset, $limite);

$window = 5;
$paginaInicio = max(1, $paginaAtual - $window);
$paginaFim = min($totalPaginas, $paginaAtual + $window);
if ($paginaFim - $paginaInicio < 2 * $window) {
    if ($paginaInicio == 1) {
        $paginaFim = min($totalPaginas, $paginaInicio + 2 * $window);
    } elseif ($paginaFim == $totalPaginas) {
        $paginaInicio = max(1, $paginaFim - 2 * $window);
    }
}

function buildQuery(array $params): string
{
    return http_build_query(array_filter($params, fn($v) => $v !== null && $v !== ''));
}
?>

<style>
    .prorrog-pendente-page {
        padding-top: 30px;
    }
</style>
<div class="container-fluid form_container prorrog-pendente-page" style="margin-top:15px;">
    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 0;">
        <h4 class="page-title" style="margin-top:-10px;">Prorrogação Pendente</h4>
    </div>
    <hr style="margin-top: 5px; margin-bottom: 10px;">

    <div class="complete-table">
        <div class="table-filters">
            <form method="GET" class="row g-2">
                <div class="col-sm-3" style="padding:2px !important;padding-left:16px !important;">
                    <input class="form-control form-control-sm" type="text" name="pesquisa_pac"
                        placeholder="Paciente" value="<?= e($pesquisa_pac) ?>">
                </div>
                <div class="col-sm-3" style="padding:2px !important;">
                    <input class="form-control form-control-sm" type="text" name="pesquisa_hosp"
                        placeholder="Hospital" value="<?= e($pesquisa_hosp) ?>">
                </div>
                <div class="col-sm-2" style="padding:2px !important;">
                    <input class="form-control form-control-sm" type="date" name="data_ini"
                        value="<?= e($data_ini) ?>">
                </div>
                <div class="col-sm-2" style="padding:2px !important;">
                    <input class="form-control form-control-sm" type="date" name="data_fim"
                        value="<?= e($data_fim) ?>">
                </div>
                <div class="col-sm-1" style="padding:2px !important;">
                    <select class="form-control form-control-sm" name="limite">
                        <option value="10" <?= $limite == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= $limite == 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $limite == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $limite == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="col-sm-1" style="padding:2px !important;">
                    <button type="submit" class="btn btn-primary"
                        style="background-color:#5e2363;width:42px;height:32px;border-color:#5e2363">
                        <span class="material-icons" style="margin-left:-3px;margin-top:-2px;">search</span>
                    </button>
                </div>
            </form>
        </div>

        <div id="table-content">
            <table class="table table-sm table-striped table-hover table-condensed">
                <thead>
                    <tr>
                        <th scope="col">Internação</th>
                        <th scope="col">Paciente</th>
                        <th scope="col">Hospital</th>
                        <th scope="col">Período (internação)</th>
                        <th scope="col">Alta</th>
                        <th scope="col">Data da alta</th>
                        <th scope="col">Nº de prorrogações</th>
                        <th scope="col">Período prorrogado</th>
                        <th scope="col">Período em aberto</th>
                        <th scope="col">Período sem prorrogação</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$paginaItens): ?>
                        <tr>
                            <td colspan="11" class="text-muted">Nenhuma pendência encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($paginaItens as $row): ?>
                            <tr>
                                <td>
                                    <a href="<?= e($BASE_URL) ?>show_internacao.php?id_internacao=<?= (int)$row['id_internacao'] ?>">
                                        <?= (int)$row['id_internacao'] ?>
                                    </a>
                                </td>
                                <td><?= e($row['nome_pac']) ?></td>
                                <td><?= e($row['nome_hosp']) ?></td>
                                <td>
                                    <?= e(date('d/m/Y', strtotime($row['data_ini']))) ?>
                                    →
                                    <?= e(date('d/m/Y', strtotime($row['data_fim']))) ?>
                                </td>
                                <td><?= !empty($row['data_alta']) ? 'Sim' : 'Não' ?></td>
                                <td><?= !empty($row['data_alta']) ? e(date('d/m/Y', strtotime($row['data_alta']))) : '-' ?></td>
                                <td><?= (int)$row['prorrog_count'] ?></td>
                                <td>
                                    <?php if (!empty($row['prorrog_range_start']) && !empty($row['prorrog_range_end'])): ?>
                                        <?= (int)$row['covered_days'] ?> dias | <?= e($row['prorrog_range_start']) ?>
                                        → <?= e($row['prorrog_range_end']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= (int)$row['missing_days'] ?> dias</td>
                                <td>
                                    <?php
                                    $gaps = $row['gaps'] ?? [];
                                    if (!$gaps) {
                                        echo '-';
                                    } else {
                                        $parts = array_map(fn($g) => $g[0] . ' → ' . $g[1], $gaps);
                                        echo e(implode(' | ', $parts));
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-outline-primary"
                                        title="Editar prorrogação"
                                        href="<?= e($BASE_URL) ?>edit_internacao.php?id_internacao=<?= (int)$row['id_internacao'] ?>&section=prorrog#collapseProrrog">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="display:flex; align-items:center; margin-top:20px;">
                <div class="pagination" style="margin: 0 auto;">
                    <?php if ($totalPaginas > 1): ?>
                        <ul class="pagination">
                            <?php
                            $queryBase = [
                                'pesquisa_pac' => $pesquisa_pac,
                                'pesquisa_hosp' => $pesquisa_hosp,
                                'data_ini' => $data_ini,
                                'data_fim' => $data_fim,
                                'limite' => $limite,
                            ];
                            ?>
                            <?php if ($paginaAtual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="list_prorrogacao_pendente.php?<?= e(buildQuery($queryBase + ['pag' => 1])) ?>">
                                        <i class="fa-solid fa-angles-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="list_prorrogacao_pendente.php?<?= e(buildQuery($queryBase + ['pag' => $paginaAtual - 1])) ?>">
                                        <i class="fa-solid fa-angle-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = $paginaInicio; $i <= $paginaFim; $i++): ?>
                                <li class="page-item <?= $paginaAtual === $i ? 'active' : '' ?>">
                                    <a class="page-link" href="list_prorrogacao_pendente.php?<?= e(buildQuery($queryBase + ['pag' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($paginaAtual < $totalPaginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="list_prorrogacao_pendente.php?<?= e(buildQuery($queryBase + ['pag' => $paginaAtual + 1])) ?>">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="list_prorrogacao_pendente.php?<?= e(buildQuery($queryBase + ['pag' => $totalPaginas])) ?>">
                                        <i class="fa-solid fa-angles-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="table-counter">
                    <p style="margin-bottom:25px;font-size:1em; font-weight:600; font-family:var(--bs-font-sans-serif); text-align:right">
                        <?php echo "Total: " . (int)$total ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
