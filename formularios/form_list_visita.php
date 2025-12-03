<?php
// visitas.php — lista + export CSV com campos selecionáveis (suporta partial=1)
include_once __DIR__ . "/db.php";
header("Content-type: text/html; charset=utf-8");

// campos
$fieldsMap = [
    'nome_paciente'   => ['label' => 'Nome do paciente',   'sql' => 'p.nome AS nome_paciente'],
    'data_internacao' => ['label' => 'Data de internação', 'sql' => 'i.data_internacao'],
    'hospital'        => ['label' => 'Hospital',           'sql' => 'h.nome_hospital AS hospital'],
    'acomodacao'      => ['label' => 'Acomodação',         'sql' => 'a.descricao AS acomodacao'],
    'data_visita'     => ['label' => 'Data da visita',     'sql' => 'v.data_visita'],
    'medico_visita'   => ['label' => 'Médico (visita)',    'sql' => 'pr.nome_profissional AS medico_visita'],
    'usuario'         => ['label' => 'Usuário',            'sql' => 'u.usuario_user AS usuario'],
    'patologia'       => ['label' => 'Patologia (CID)',    'sql' => 'pat.patologia AS patologia'],
];

$selected = (isset($_GET['fields']) && is_array($_GET['fields']))
    ? array_values(array_intersect(array_keys($fieldsMap), $_GET['fields']))
    : array_keys($fieldsMap);
if (!$selected) $selected = array_keys($fieldsMap);

// filtros
$nomePaciente = trim($_GET['nome'] ?? '');
$hospitalId   = trim($_GET['hospital_id'] ?? '');
$dtIni        = trim($_GET['dt_ini'] ?? '');
$dtFim        = trim($_GET['dt_fim'] ?? '');
$isExport     = (($_GET['export'] ?? '') === '1');
$isPartial    = (($_GET['partial'] ?? '') === '1');

// paginação
$limite = (ctype_digit($_GET['limite'] ?? '') ? (int)$_GET['limite'] : 20);
$pag    = (ctype_digit($_GET['pag'] ?? '')    ? (int)$_GET['pag']    : 1);
$offset = max(0, ($pag - 1) * $limite);

// SELECT dinâmico
$select = implode(", ", array_map(fn($k) => $fieldsMap[$k]['sql'], $selected));

// subquery patologia
$patologiaSubquery = "
  LEFT JOIN (
    SELECT p2.fk_internacao,
           GROUP_CONCAT(CONCAT(IFNULL(c.cat,''), ' - ', IFNULL(c.descricao,'')) ORDER BY p2.id_patologia SEPARATOR ' | ') AS patologia
    FROM tb_patologia p2
    LEFT JOIN tb_cid c ON c.id_cid = p2.fk_cid_10_pat
    GROUP BY p2.fk_internacao
  ) pat ON pat.fk_internacao = i.id_internacao
";

// FROM base
$sqlBase = "
FROM tb_internacao i
JOIN tb_paciente        p  ON p.id_paciente        = i.fk_paciente
LEFT JOIN tb_hospital   h  ON h.id_hospital        = i.fk_hospital
LEFT JOIN tb_acomodacao a  ON a.id_acomodacao      = i.fk_acomodacao
LEFT JOIN tb_usuario    u  ON u.id_usuario         = i.fk_usuario
LEFT JOIN tb_visita     v  ON v.fk_internacao      = i.id_internacao
LEFT JOIN tb_profissional pr ON pr.id_profissional = v.fk_profissional
$patologiaSubquery
WHERE 1=1
";

// filtros (bind)
$params = [];
if ($nomePaciente !== '') {
    $sqlBase .= " AND p.nome LIKE :nome ";
    $params[':nome'] = "%$nomePaciente%";
}
if ($hospitalId   !== '') {
    $sqlBase .= " AND i.fk_hospital = :hid ";
    $params[':hid']  = $hospitalId;
}
if ($dtIni        !== '') {
    $sqlBase .= " AND DATE(i.data_internacao) >= :dtini ";
    $params[':dtini'] = $dtIni;
}
if ($dtFim        !== '') {
    $sqlBase .= " AND DATE(i.data_internacao) <= :dtfim ";
    $params[':dtfim'] = $dtFim;
}

$sqlOrder = " ORDER BY COALESCE(v.data_visita, i.data_internacao) DESC, p.nome ASC ";

// COUNT otimizado
$sqlCount = "
SELECT COUNT(*) 
FROM tb_internacao i
JOIN tb_paciente p ON p.id_paciente = i.fk_paciente
LEFT JOIN tb_hospital h ON h.id_hospital = i.fk_hospital
LEFT JOIN tb_acomodacao a ON a.id_acomodacao = i.fk_acomodacao
LEFT JOIN tb_usuario u ON u.id_usuario = i.fk_usuario
LEFT JOIN tb_visita v ON v.fk_internacao = i.id_internacao
WHERE 1=1
";
$paramsCount = [];
if ($nomePaciente !== '') {
    $sqlCount .= " AND p.nome LIKE :nome ";
    $paramsCount[':nome'] = "%$nomePaciente%";
}
if ($hospitalId   !== '') {
    $sqlCount .= " AND i.fk_hospital = :hid ";
    $paramsCount[':hid']  = $hospitalId;
}
if ($dtIni        !== '') {
    $sqlCount .= " AND DATE(i.data_internacao) >= :dtini ";
    $paramsCount[':dtini'] = $dtIni;
}
if ($dtFim        !== '') {
    $sqlCount .= " AND DATE(i.data_internacao) <= :dtfim ";
    $paramsCount[':dtfim'] = $dtFim;
}

$stmtC = $conn->prepare($sqlCount);
$stmtC->execute($paramsCount);
$total = (int)$stmtC->fetchColumn();

$sql = "SELECT $select $sqlBase $sqlOrder LIMIT :lim OFFSET :off";
$stmt = $conn->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// EXPORT CSV
if ($isExport) {
    $fname = "visitas_" . date("Ymd_His") . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    echo "\xEF\xBB\xBF"; // BOM
    $out = fopen('php://output', 'w');

    $header = array_map(fn($k) => $fieldsMap[$k]['label'], $selected);
    fputcsv($out, $header, ';');

    foreach ($rows as $r) {
        $line = [];
        foreach ($selected as $k) {
            $val = $r[$k] ?? '';
            if (in_array($k, ['data_internacao', 'data_visita'], true) && $val) {
                $ts = strtotime($val);
                $val = $ts ? date('d/m/Y', $ts) : $val;
            }
            $line[] = $val;
        }
        fputcsv($out, $line, ';');
    }
    fclose($out);
    exit;
}

// paginação
$totalPages = max(1, (int)ceil($total / max(1, $limite)));
function qs_keep($replace = [])
{
    $keep = $_GET;
    foreach ($replace as $k => $v) $keep[$k] = $v;
    return http_build_query($keep);
}

// se pedir partial, entrega só a tabela + paginação
if ($isPartial): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <?php foreach ($selected as $k): ?>
                <th><?= htmlspecialchars($fieldsMap[$k]['label']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
                <?php foreach ($selected as $k): ?>
                <td>
                    <?php
                                    $val = $r[$k] ?? '';
                                    if (in_array($k, ['data_internacao', 'data_visita'], true) && $val) {
                                        $ts = strtotime($val);
                                        $val = $ts ? date('d/m/Y', $ts) : $val;
                                    }
                                    echo htmlspecialchars($val);
                                    ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach;
                else: ?>
            <tr>
                <td colspan="<?= count($selected) ?>">Nada encontrado</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center">
    <div class="text-muted small">Total: <?= $total ?> registro(s)</div>
    <nav>
        <ul class="pagination m-0">
            <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= qs_keep(['pag' => 1, 'partial' => 1]) ?>">&laquo;</a>
            </li>
            <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= qs_keep(['pag' => max(1, $pag - 1), 'partial' => 1]) ?>">&lsaquo;</a>
            </li>
            <li class="page-item disabled">
                <span class="page-link">Página <?= $pag ?> de <?= $totalPages ?></span>
            </li>
            <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="?<?= qs_keep(['pag' => min($totalPages, $pag + 1), 'partial' => 1]) ?>">&rsaquo;</a>
            </li>
            <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= qs_keep(['pag' => $totalPages, 'partial' => 1]) ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
</div>
<?php
    // fim partial
    exit;
endif;
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <?php
    $exportTitle = isset($pageTitle) && $pageTitle ? $pageTitle : 'Lista de Visitas';
    ?>
    <title><?= htmlspecialchars($exportTitle . ' (com exportação)', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    body {
        padding: 20px
    }

    .card {
        border-radius: 16px
    }

    table thead th {
        white-space: nowrap
    }

    td,
    th {
        vertical-align: middle
    }
    </style>
</head>

<body>
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <?php foreach ($selected as $k): ?>
                        <th><?= htmlspecialchars($fieldsMap[$k]['label']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows): foreach ($rows as $r): ?>
                    <tr>
                        <?php foreach ($selected as $k): ?>
                        <td>
                            <?php
                                        $val = $r[$k] ?? '';
                                        if (in_array($k, ['data_internacao', 'data_visita'], true) && $val) {
                                            $ts = strtotime($val);
                                            $val = $ts ? date('d/m/Y', $ts) : $val;
                                        }
                                        echo htmlspecialchars($val);
                                        ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach;
                    else: ?>
                    <tr>
                        <td colspan="<?= count($selected) ?>">Nada encontrado</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">Total: <?= $total ?> registro(s)</div>
            <nav>
                <ul class="pagination m-0">
                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= qs_keep(['pag' => 1]) ?>">&laquo;</a>
                    </li>
                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= qs_keep(['pag' => max(1, $pag - 1)]) ?>">&lsaquo;</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link">Página <?= $pag ?> de
                            <?= $totalPages ?></span></li>
                    <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= qs_keep(['pag' => min($totalPages, $pag + 1)]) ?>">&rsaquo;</a>
                    </li>
                    <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= qs_keep(['pag' => $totalPages]) ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</body>

</html>
