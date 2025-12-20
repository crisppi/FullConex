<?php
$exporting = isset($_GET['export']) && $_GET['export'] == '1';
if ($exporting) {
    define('SKIP_HEADER', true);
}
require_once __DIR__ . '/check_logado.php';
require_once __DIR__ . '/globals.php';
require_once __DIR__ . '/db.php';

if (!function_exists('h')) {
    function h($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

$nomePaciente = trim($_GET['nome'] ?? '');
$hospitalId   = trim($_GET['hospital_id'] ?? '');
$dtBase       = trim($_GET['dt_base'] ?? date('Y-m-d'));
$limite       = isset($_GET['limite']) && ctype_digit($_GET['limite']) ? (int)$_GET['limite'] : 20;
$pag          = isset($_GET['pag'])    && ctype_digit($_GET['pag'])    ? (int)$_GET['pag']    : 1;
$limite       = max(1, min(500, $limite));
$pag          = max(1, $pag);
$offset       = max(0, ($pag - 1) * $limite);

$fieldsMap = [
    'id_internacao' => ['label' => 'ID Int', 'field' => 'id_internacao'],
    'id_visita'     => ['label' => 'Id Visita', 'field' => 'id_visita'],
    'senha'         => ['label' => 'Senha', 'field' => 'senha_int'],
    'nome_paciente' => ['label' => 'Nome do paciente', 'field' => 'paciente'],
    'hospital'      => ['label' => 'Hospital', 'field' => 'hospital'],
    'cnpj_hospital' => ['label' => 'CNPJ do hospital', 'field' => 'cnpj_hospital'],
    'matricula'     => ['label' => 'Matrícula', 'field' => 'matricula'],
    'data_visita'   => ['label' => 'Data visita', 'field' => 'data_visita_vis_fmt'],
    'data_lancamento' => ['label' => 'Data lançamento', 'field' => 'data_lancamento_vis_fmt'],
    'faturado_vis'  => ['label' => 'Faturado?', 'field' => 'faturado_vis'],
];

$selected = isset($_GET['fields']) && is_array($_GET['fields'])
    ? array_values(array_intersect(array_keys($fieldsMap), $_GET['fields']))
    : array_keys($fieldsMap);
if (!$selected) $selected = array_keys($fieldsMap);

$periodoFim = $dtBase !== '' ? $dtBase : date('Y-m-d');
$periodoIni = date('Y-m-d', strtotime($periodoFim . ' -30 days'));

$params = [
    ':ini' => $periodoIni . ' 00:00:00',
    ':fim' => $periodoFim . ' 23:59:59'
];

$where = "WHERE v.data_lancamento_vis BETWEEN :ini AND :fim AND (v.faturado_vis IS NULL OR v.faturado_vis = '' OR v.faturado_vis <> 's')";
if ($nomePaciente !== '') {
    $where .= " AND pa.nome_pac LIKE :nome ";
    $params[':nome'] = "%{$nomePaciente}%";
}
if ($hospitalId !== '') {
    $where .= " AND i.fk_hospital_int = :hid ";
    $params[':hid'] = $hospitalId;
}

$sqlBase = "
FROM tb_visita v
JOIN tb_internacao i ON i.id_internacao = v.fk_internacao_vis
JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
LEFT JOIN tb_hospital ho ON ho.id_hospital = i.fk_hospital_int
$where
";

$countSql = "SELECT COUNT(*) $sqlBase";
$stmtCount = $conn->prepare($countSql);
foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
$stmtCount->execute();
$total = (int)$stmtCount->fetchColumn();

$dataSql = "
SELECT
    v.id_visita AS id_visita,
    i.id_internacao AS id_internacao,
    i.senha_int AS senha_int,
    pa.nome_pac AS paciente,
    pa.matricula_pac AS matricula,
    ho.nome_hosp AS hospital,
    ho.cnpj_hosp AS cnpj_hospital,
    DATE_FORMAT(v.data_visita_vis, '%d/%m/%Y') AS data_visita_vis_fmt,
    DATE_FORMAT(v.data_lancamento_vis, '%d/%m/%Y') AS data_lancamento_vis_fmt,
    v.faturado_vis,
    v.data_faturamento_vis
$sqlBase
ORDER BY i.id_internacao DESC, v.data_lancamento_vis DESC
LIMIT $limite OFFSET $offset
";
$stmt = $conn->prepare($dataSql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['export']) && $_GET['export'] == '1') {
    require_once __DIR__ . '/vendor/autoload.php';
    $exportSql = "
SELECT
    v.id_visita AS id_visita,
    i.id_internacao AS id_internacao,
    i.senha_int AS senha_int,
    pa.nome_pac AS paciente,
    pa.matricula_pac AS matricula,
    ho.nome_hosp AS hospital,
    ho.cnpj_hosp AS cnpj_hospital,
        DATE_FORMAT(v.data_visita_vis, '%d/%m/%Y') AS data_visita_vis_fmt,
        DATE_FORMAT(v.data_lancamento_vis, '%d/%m/%Y') AS data_lancamento_vis_fmt,
        v.faturado_vis
    $sqlBase
    ORDER BY i.id_internacao DESC, v.data_lancamento_vis DESC
    ";
    $stmtExport = $conn->prepare($exportSql);
    foreach ($params as $k => $v) $stmtExport->bindValue($k, $v);
    $stmtExport->execute();
    $rowsExp = $stmtExport->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Faturamento Mensal Visitas');

    $logoPath = __DIR__ . '/img/LogoConexAud.png';
    if (file_exists($logoPath)) {
        $logo = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $logo->setName('Logo');
        $logo->setDescription('Logo Conex');
        $logo->setPath($logoPath);
        $logo->setHeight(32);
        $logo->setCoordinates('A2');
        $logo->setWorksheet($sheet);
    }

    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(max(1, count($selected)));
    $sheet->getRowDimension(1)->setRowHeight(28);
    $sheet->getRowDimension(2)->setRowHeight(18);
    $sheet->setCellValue('D1', 'Faturamento Mensal - Visitas');
    $sheet->mergeCells('D1:' . $lastCol . '1');
    $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(13);
    $sheet->setCellValue('D2', 'Data da extração: ' . date('d/m/Y H:i'));
    $sheet->mergeCells('D2:' . $lastCol . '2');

    $sheet->setShowGridlines(false);
    $headerRow = 6;
    $col = 1;
    foreach ($selected as $key) {
        $sheet->setCellValueByColumnAndRow($col, $headerRow, $fieldsMap[$key]['label']);
        $col++;
    }

    $rowIndex = $headerRow + 1;
    foreach ($rowsExp as $r) {
        $col = 1;
        foreach ($selected as $key) {
            $field = $fieldsMap[$key]['field'] ?? $key;
            $val = $r[$field] ?? '';
            if ($key === 'faturado_vis') {
                $val = strtolower($r['faturado_vis'] ?? 'n') === 's' ? 'Sim' : 'Não';
            }
            $sheet->setCellValueExplicitByColumnAndRow(
                $col,
                $rowIndex,
                (string)$val,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $col++;
        }
        $rowIndex++;
    }

    $headerStyle = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E5E5E5'],
        ],
        'font' => ['bold' => true],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => 'BDBDBD'],
            ],
        ],
    ];
    $borderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => 'D0D0D0'],
            ],
        ],
    ];

    $sheet->getStyleByColumnAndRow(1, $headerRow, count($selected), $headerRow)->applyFromArray($headerStyle);
    for ($c = 1; $c <= count($selected); $c++) {
        $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
    }
    $lastDataRow = $rowIndex - 1;
    if ($lastDataRow >= $headerRow) {
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $lastDataRow)->applyFromArray($borderStyle);
    }

    $fname = "faturamento_mensal_" . date("Ymd_His") . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    $spreadsheet->disconnectWorksheets();
    exit;
}

$hospitais = [];
$stmtHosp = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp");
if ($stmtHosp) $hospitais = $stmtHosp->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/templates/header.php';
?>
<?php
$brandColor = '#d45a10';
$brandSoftColor = '#ffd5b3';
$fieldIcons = [
    'id_internacao' => 'bi-hash',
    'id_visita'     => 'bi-hash',
    'senha'         => 'bi-shield-lock',
    'nome_paciente' => 'bi-person',
    'hospital'      => 'bi-hospital',
    'cnpj_hospital' => 'bi-building',
    'matricula'     => 'bi-123',
    'data_visita'   => 'bi-calendar-event',
    'data_lancamento' => 'bi-calendar2-week',
    'faturado_vis'  => 'bi-cash-coin',
];
?>
<style>
    :root {
        --brand: <?= htmlspecialchars($brandColor, ENT_QUOTES, 'UTF-8') ?>;
        --brand-100: <?= htmlspecialchars($brandSoftColor, ENT_QUOTES, 'UTF-8') ?>;
    }
    .btn-outline-brand {
        border-color: var(--brand);
        color: var(--brand);
        background: #fff;
    }
    .btn-outline-brand:hover {
        background: var(--brand-100);
    }
    .btn-check:checked+.btn-outline-brand {
        background: var(--brand);
        color: #fff;
        border-color: var(--brand);
    }
    .btn-primary {
        background: var(--brand);
        border-color: var(--brand);
    }
    .btn-primary:hover,
    .btn-primary:focus {
        background: #c25c10;
        border-color: #c25c10;
    }
    .field-chips {
        gap: .5rem;
    }
</style>

<div class="container-fluid" style="margin-top:-10px;">
    <h4 class="page-title mt-0 mb-2">Faturamento Mensal Visitas</h4>
    <hr class="mt-1 mb-3">

    <form method="get" class="card p-3 mb-3 shadow-sm border-0">
        <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <label class="form-label fw-semibold m-0 fs-5">Campos a exibir</label>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm" id="btn-mensal-check-all"><i class="bi bi-check2-all me-1"></i>Selecionar todos</button>
                <button type="button" class="btn btn-light btn-sm" id="btn-mensal-uncheck-all"><i class="bi bi-x-lg me-1"></i>Limpar</button>
            </div>
        </div>
        <div class="field-chips d-flex flex-wrap mb-3">
            <?php foreach ($fieldsMap as $key => $meta):
                $checked = in_array($key, $selected, true);
                $icon = $fieldIcons[$key] ?? 'bi-check'; ?>
                <input type="checkbox" class="btn-check field-check" id="fm_<?= h($key) ?>" name="fields[]"
                    value="<?= h($key) ?>" <?= $checked ? 'checked' : '' ?>>
                <label class="btn btn-outline-brand btn-sm rounded-pill px-3" for="fm_<?= h($key) ?>"><i
                        class="bi <?= $icon ?> me-1"></i><?= h($meta['label']) ?></label>
            <?php endforeach; ?>
        </div>

        <div class="mb-2"><label class="form-label fw-semibold m-0">Filtros</label></div>

        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="nome" class="form-control" placeholder="Nome do paciente"
                        value="<?= h($nomePaciente) ?>">
                </div>
            </div>
            <div class="col-12 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-hospital"></i></span>
                    <select name="hospital_id" class="form-select">
                        <option value="">— Hospital —</option>
                        <?php foreach ($hospitais as $h): ?>
                            <option value="<?= $h['id_hospital'] ?>" <?= $hospitalId == $h['id_hospital'] ? 'selected' : '' ?>>
                                <?= h($h['nome_hosp']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-week"></i></span>
                    <input type="date" name="dt_base" class="form-control" value="<?= h($dtBase) ?>">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                    <select name="limite" class="form-select" onchange="this.form.submit()">
                        <?php foreach ([10, 20, 50, 100] as $opt): ?>
                            <option value="<?= $opt ?>" <?= $limite == $opt ? 'selected' : '' ?>><?= $opt ?> por página</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                    <input type="text" class="form-control" value="<?= h(date('d/m/Y', strtotime($periodoIni))) ?> - <?= h(date('d/m/Y', strtotime($periodoFim))) ?>" readonly>
                </div>
            </div>
            <div class="col-12 col-lg-2 d-flex justify-content-end gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Aplicar</button>
                <button class="btn btn-success" type="submit" name="export" value="1">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Exportar XLSX (Excel)
                </button>
            </div>
        </div>
        <input type="hidden" name="sort_field" value="">
        <input type="hidden" name="sort_dir" value="">
    </form>

    <div class="card p-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="form-check form-switch d-flex align-items-center gap-2">
                <input class="form-check-input" type="checkbox" id="chkMensalSelectAll">
                <label class="form-check-label" for="chkMensalSelectAll">Selecionar todos</label>
            </div>
            <button class="btn btn-primary" id="btnFaturarMensal" type="button" disabled>
                <i class="bi bi-currency-dollar me-1"></i>Faturar selecionados
                <span class="badge bg-light text-dark ms-2" id="badgeMensalSel">0</span>
            </button>
            <div id="mensalFeedback" class="flex-grow-1"></div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle" id="tabelaMensal">
                <thead>
                    <tr>
                        <th class="text-center" style="width:70px"><i class="bi bi-check2-square"></i></th>
                        <?php foreach ($selected as $k): ?>
                            <th class="col-<?= h($k) ?>"><?= h($fieldsMap[$k]['label']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows): foreach ($rows as $r): ?>
                        <tr>
                            <td class="text-center">
                                <?php $isFaturado = strtolower($r['faturado_vis'] ?? 'n') === 's'; ?>
                                <input type="checkbox" class="form-check-input chk-mensal"
                                    style="transform: scale(1.2);"
                                    value="<?= (int)$r['id_visita'] ?>" <?= $isFaturado ? 'disabled' : '' ?>>
                            </td>
                            <?php foreach ($selected as $k):
                                $fieldName = $fieldsMap[$k]['field'] ?? $k;
                                $val = $r[$fieldName] ?? '';
                                if ($k === 'faturado_vis') {
                                    $val = strtolower($r['faturado_vis'] ?? 'n') === 's' ? 'Sim' : 'Não';
                                }
                            ?>
                                <td class="col-<?= h($k) ?>"><?= h($val) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="<?= count($selected) + 1 ?>">Nenhuma visita encontrada no período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php $totalPages = max(1, (int)ceil($total / max(1, $limite))); ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">Total: <?= $total ?> visita(s)</div>
            <nav>
                <ul class="pagination m-0">
                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pag' => 1])) ?>">&laquo;</a></li>
                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pag' => max(1, $pag - 1)])) ?>">&lsaquo;</a></li>
                    <li class="page-item disabled"><span class="page-link">Página <?= $pag ?> de <?= $totalPages ?></span></li>
                    <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pag' => min($totalPages, $pag + 1)])) ?>">&rsaquo;</a></li>
                    <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pag' => $totalPages])) ?>">&raquo;</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const formEl = document.getElementById('form-faturamento-mensal');
    const updateColumnVisibility = (checkbox) => {
        const k = checkbox.value;
        const isChecked = checkbox.checked;
        const cells = document.querySelectorAll('#tabelaMensal th.col-' + k + ', #tabelaMensal td.col-' + k);
        if (isChecked && cells.length === 0 && formEl) {
            formEl.submit();
            return;
        }
        cells.forEach(cell => cell.style.display = isChecked ? '' : 'none');
    };
    const fieldCheckboxes = document.querySelectorAll('.field-check');
    fieldCheckboxes.forEach(updateColumnVisibility);
    document.addEventListener('change', e => {
        if (e.target.classList.contains('field-check')) {
            updateColumnVisibility(e.target);
        }
    });
    document.getElementById('btn-mensal-check-all')?.addEventListener('click', () => {
        fieldCheckboxes.forEach(chk => {
            if (!chk.checked) {
                chk.checked = true;
                updateColumnVisibility(chk);
            }
        });
    });
    document.getElementById('btn-mensal-uncheck-all')?.addEventListener('click', () => {
        fieldCheckboxes.forEach(chk => {
            if (chk.checked) {
                chk.checked = false;
                updateColumnVisibility(chk);
            }
        });
    });

    const selectAll = document.getElementById('chkMensalSelectAll');
    const checkboxes = () => Array.from(document.querySelectorAll('.chk-mensal:not(:disabled)'));
    const checked = () => Array.from(document.querySelectorAll('.chk-mensal:checked'));
    const badge = document.getElementById('badgeMensalSel');
    const btnFaturar = document.getElementById('btnFaturarMensal');
    const feedback = document.getElementById('mensalFeedback');

    function updateBadge() {
        const total = checked().length;
        if (badge) badge.textContent = total.toString();
        if (btnFaturar) btnFaturar.disabled = total === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes().forEach(chk => chk.checked = selectAll.checked);
            updateBadge();
        });
    }

    document.addEventListener('change', (ev) => {
        if (ev.target.classList.contains('chk-mensal')) {
            if (selectAll && !ev.target.checked) selectAll.checked = false;
            updateBadge();
        }
    });

    btnFaturar?.addEventListener('click', () => {
        const ids = checked().map(chk => parseInt(chk.value, 10)).filter(id => id > 0);
        if (!ids.length) return;
        btnFaturar.disabled = true;
        btnFaturar.classList.add('disabled');
        if (feedback) feedback.innerHTML = '';
        fetch('processa_faturamento_mensal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        }).then(resp => resp.json())
          .then(data => {
            if (feedback) {
                const cls = data.success ? 'alert-success' : 'alert-danger';
                feedback.innerHTML = `<div class="alert ${cls} my-2 py-2">${data.message || 'Operação concluída.'}</div>`;
            }
            if (data.success) {
                setTimeout(() => window.location.reload(), 1200);
            } else {
                btnFaturar.disabled = false;
                btnFaturar.classList.remove('disabled');
            }
          })
          .catch(() => {
            if (feedback) {
                feedback.innerHTML = '<div class="alert alert-danger my-2 py-2">Não foi possível faturar. Tente novamente.</div>';
            }
            btnFaturar.disabled = false;
            btnFaturar.classList.remove('disabled');
          });
    });

    updateBadge();
});
</script>
    <form method="post" class="card p-3 mb-3 shadow-sm border-0" id="form-faturamento-mensal">
