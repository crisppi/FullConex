<?php
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
$monthRef     = trim($_GET['mes_ref'] ?? '');
$quinzenaSel  = trim($_GET['quinzena'] ?? '');
$limite       = isset($_GET['limite']) && ctype_digit($_GET['limite']) ? (int)$_GET['limite'] : 20;
$pag          = isset($_GET['pag'])    && ctype_digit($_GET['pag'])    ? (int)$_GET['pag']    : 1;
$limite       = max(1, min(500, $limite));
$pag          = max(1, $pag);
$offset       = max(0, ($pag - 1) * $limite);

$fieldsMap = [
    'id_capeante'   => ['label' => 'ID Conta', 'field' => 'id_capeante'],
    'id_internacao' => ['label' => 'ID Int', 'field' => 'id_internacao'],
    'senha'         => ['label' => 'Senha', 'field' => 'senha_int'],
    'hospital'      => ['label' => 'Hospital', 'field' => 'hospital'],
    'cnpj_hospital' => ['label' => 'CNPJ do hospital', 'field' => 'cnpj_hospital'],
    'nome_paciente' => ['label' => 'Nome do paciente', 'field' => 'paciente'],
    'matricula'     => ['label' => 'Matrícula', 'field' => 'matricula'],
    'parcial_num'   => ['label' => 'Parcial', 'field' => 'parcial_num'],
    'data_inicial'  => ['label' => 'Data inicial', 'field' => 'data_inicial_fmt'],
    'data_final'    => ['label' => 'Data final', 'field' => 'data_final_fmt'],
    'data_digitacao'=> ['label' => 'Data digitação', 'field' => 'data_digit_fmt'],
    'ciclo'         => ['label' => 'Ciclo (30 dias)', 'field' => 'ciclo_label'],
    'valor_apresentado' => ['label' => 'Valor apresentado', 'field' => 'valor_apresentado_capeante'],
    'valor_final'   => ['label' => 'Valor final', 'field' => 'valor_final_capeante'],
    'conta_faturada_cap' => ['label' => 'Faturado?', 'field' => 'conta_faturada_cap'],
];

$selected = isset($_GET['fields']) && is_array($_GET['fields'])
    ? array_values(array_intersect(array_keys($fieldsMap), $_GET['fields']))
    : array_keys($fieldsMap);
if (!$selected) $selected = array_keys($fieldsMap);

$fixedFields = ['cnpj_hospital', 'valor_final', 'conta_faturada_cap'];
foreach ($fixedFields as $fixedField) {
    if (!in_array($fixedField, $selected, true)) {
        $selected[] = $fixedField;
    }
}
$visibleFields = array_values(array_diff($selected, $fixedFields));
if (!$visibleFields) {
    $visibleFields = array_values(array_diff(array_keys($fieldsMap), $fixedFields));
}

$periodoInicioFiltro = trim($_GET['dt_inicio'] ?? '');
$periodoFimFiltro     = trim($_GET['dt_fim'] ?? '');

$periodoFim = $periodoFimFiltro !== '' ? $periodoFimFiltro : ($dtBase !== '' ? $dtBase : date('Y-m-d'));
$periodoIni = $periodoInicioFiltro !== '' ? $periodoInicioFiltro : date('Y-m-d', strtotime($periodoFim . ' -30 days'));

$selectedMonthValue = '';
if ($monthRef !== '' && preg_match('/^\d{4}\-\d{2}$/', $monthRef)) {
    $selectedMonthValue = $monthRef;
    $dtBase = $monthRef . '-01';
}

if ($quinzenaSel !== '') {
    $baseDate = DateTime::createFromFormat('Y-m-d', $dtBase) ?: new DateTime();
    $firstDay = (clone $baseDate)->modify('first day of this month');
    $midDay   = (clone $firstDay)->modify('+14 days'); // day 15
    $secondStart = (clone $firstDay)->modify('+15 days'); // day 16
    $lastDay  = (clone $firstDay)->modify('last day of this month');
    if ($quinzenaSel === '1') {
        $periodoIni = $firstDay->format('Y-m-d');
        $periodoFim = $midDay->format('Y-m-d');
    } elseif ($quinzenaSel === '2') {
        $periodoIni = $secondStart->format('Y-m-d');
        $periodoFim = $lastDay->format('Y-m-d');
    }
} elseif ($selectedMonthValue !== '' && $periodoInicioFiltro === '' && $periodoFimFiltro === '') {
    $baseDate = DateTime::createFromFormat('Y-m-d', $dtBase) ?: new DateTime();
    $inicioMes = (clone $baseDate)->modify('first day of this month');
    $fimMes = (clone $baseDate)->modify('last day of this month');
    $periodoIni = $inicioMes->format('Y-m-d');
    $periodoFim = $fimMes->format('Y-m-d');
}

$params = [
    ':ini' => $periodoIni . ' 00:00:00',
    ':fim' => $periodoFim . ' 23:59:59'
];

$filtroCampo = 'ca.data_digit_capeante';
if (isset($_GET['filtro_data']) && $_GET['filtro_data'] === 'final') {
    $filtroCampo = 'COALESCE(ca.data_final_capeante, ca.data_fech_capeante, ca.data_digit_capeante)';
}

$where = "WHERE {$filtroCampo} BETWEEN :ini AND :fim
    AND (ca.conta_faturada_cap IS NULL OR ca.conta_faturada_cap = '' OR LOWER(ca.conta_faturada_cap) <> 's')
    AND (ca.encerrado_cap = 's')";
if ($nomePaciente !== '') {
    $where .= " AND pa.nome_pac LIKE :nome ";
    $params[':nome'] = "%{$nomePaciente}%";
}
if ($hospitalId !== '') {
    $where .= " AND i.fk_hospital_int = :hid ";
    $params[':hid'] = $hospitalId;
}

$sqlBase = "
FROM tb_capeante ca
JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
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
    ca.id_capeante,
    i.id_internacao,
    i.senha_int,
    i.data_intern_int AS data_intern_raw,
    ca.data_inicial_capeante AS data_inicial_raw,
    ca.data_final_capeante AS data_final_raw,
    ca.data_digit_capeante AS data_digit_raw,
    (
        SELECT MIN(ca2.data_digit_capeante)
        FROM tb_capeante ca2
        WHERE ca2.fk_int_capeante = ca.fk_int_capeante
          AND ca2.data_digit_capeante IS NOT NULL
    ) AS primeira_digitacao_raw,
    ho.nome_hosp AS hospital,
    ho.cnpj_hosp AS cnpj_hospital,
    pa.nome_pac AS paciente,
    pa.matricula_pac AS matricula,
    ca.parcial_num,
    DATE_FORMAT(ca.data_inicial_capeante, '%d/%m/%Y') AS data_inicial_fmt,
    DATE_FORMAT(ca.data_final_capeante, '%d/%m/%Y') AS data_final_fmt,
    DATE_FORMAT(ca.data_digit_capeante, '%d/%m/%Y') AS data_digit_fmt,
    ca.valor_apresentado_capeante,
    ca.valor_final_capeante,
    ca.conta_faturada_cap
$sqlBase
ORDER BY i.id_internacao DESC, ca.data_final_capeante DESC, ca.id_capeante DESC
LIMIT $limite OFFSET $offset
";
$stmt = $conn->prepare($dataSql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$parseDate = function ($value) {
    if (!$value || $value === '0000-00-00') {
        return null;
    }
    try {
        return new DateTime($value);
    } catch (Throwable $e) {
        return null;
    }
};

foreach ($rows as &$row) {
    $digit = $parseDate($row['data_digit_raw'] ?? null);
    $baseDigit = $parseDate($row['primeira_digitacao_raw'] ?? null);
    $final = $parseDate($row['data_final_raw'] ?? null);

    $cycleStart = $digit ?: ($baseDigit ?: $final);
    if (!$cycleStart) {
        $row['ciclo_label'] = '—';
        continue;
    }

    $cycleEnd = (clone $cycleStart)->modify('+30 days');
    $row['ciclo_label'] = $cycleStart->format('d/m/Y') . ' a ' . $cycleEnd->format('d/m/Y');
}
unset($row);

if (isset($_GET['export']) && $_GET['export'] == '1') {
    require_once __DIR__ . '/vendor/autoload.php';
    $stmtExport = $conn->prepare(str_replace("LIMIT $limite OFFSET $offset", '', $dataSql));
    foreach ($params as $k => $v) $stmtExport->bindValue($k, $v);
    $stmtExport->execute();
    $rowsExp = $stmtExport->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Faturamento Mensal Contas');

    $col = 1;
    foreach ($visibleFields as $key) {
        $sheet->setCellValueByColumnAndRow($col, 1, $fieldsMap[$key]['label']);
        $col++;
    }

    $rowIndex = 2;
    foreach ($rowsExp as $r) {
        $col = 1;
        foreach ($visibleFields as $key) {
            $field = $fieldsMap[$key]['field'] ?? $key;
            $val = $r[$field] ?? '';
            if (in_array($key, ['valor_apresentado', 'valor_final'], true)) {
                $val = number_format((float)$val, 2, ',', '.');
            } elseif ($key === 'conta_faturada_cap') {
                $val = strtolower($r['conta_faturada_cap'] ?? 'n') === 's' ? 'Sim' : 'Não';
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

    $sheet->getStyleByColumnAndRow(1, 1, count($visibleFields), 1)->getFont()->setBold(true);
    for ($c = 1; $c <= count($visibleFields); $c++) {
        $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
    }

    $fname = "faturamento_mensal_contas_" . date("Ymd_His") . ".xlsx";
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

$monthOptions = [];
$monthBase = DateTime::createFromFormat('Y-m-d', $dtBase) ?: new DateTime();
$monthStart = (clone $monthBase)->modify('-5 months')->modify('first day of this month');
for ($m = 0; $m < 12; $m++) {
    $label = $monthStart->format('m/Y');
    $value = $monthStart->format('Y-m');
    $monthOptions[] = ['value' => $value, 'label' => $label];
    $monthStart->modify('+1 month');
}

include_once __DIR__ . '/templates/header.php';
$brandColor = '#3a6d3a';
$brandSoftColor = '#e2f2e2';
$fieldIcons = [
    'id_capeante'   => 'bi-briefcase',
    'id_internacao' => 'bi-hash',
    'senha'         => 'bi-shield-lock',
    'hospital'      => 'bi-hospital',
    'cnpj_hospital' => 'bi-building',
    'nome_paciente' => 'bi-person',
    'matricula'     => 'bi-123',
    'parcial_num'   => 'bi-collection',
    'data_inicial'  => 'bi-calendar-event',
    'data_final'    => 'bi-calendar-check',
    'data_digitacao'=> 'bi-calendar2-week',
    'ciclo'         => 'bi-calendar-range',
    'valor_apresentado' => 'bi-currency-dollar',
    'valor_final'   => 'bi-cash-stack',
    'conta_faturada_cap' => 'bi-clipboard-check',
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
        background: #2b4e2a;
        border-color: #2b4e2a;
    }
</style>

<div class="container-fluid" style="margin-top:-10px;">
    <h4 class="page-title mt-0 mb-2">Faturamento Mensal Contas</h4>
    <hr class="mt-1 mb-3">

    <form method="get" class="card p-3 mb-3 shadow-sm border-0" id="form-faturamento-mensal">
        <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <label class="form-label fw-semibold m-0 fs-5">Campos a exibir</label>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm" id="btn-contas-check-all"><i class="bi bi-check2-all me-1"></i>Selecionar todos</button>
                <button type="button" class="btn btn-light btn-sm" id="btn-contas-uncheck-all"><i class="bi bi-x-lg me-1"></i>Limpar</button>
            </div>
        </div>
        <div class="field-chips d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($fieldsMap as $key => $meta):
                if (in_array($key, $fixedFields, true)) continue;
                $checked = in_array($key, $visibleFields, true);
                $icon = $fieldIcons[$key] ?? 'bi-check'; ?>
                <input type="checkbox" class="btn-check field-check" id="fc_<?= h($key) ?>" name="fields[]"
                    value="<?= h($key) ?>" <?= $checked ? 'checked' : '' ?>>
                <label class="btn btn-outline-brand btn-sm rounded-pill px-3" for="fc_<?= h($key) ?>"><i
                        class="bi <?= $icon ?> me-1"></i><?= h($meta['label']) ?></label>
            <?php endforeach; ?>
        </div>

        <div class="mb-2"><label class="form-label fw-semibold m-0">Filtros</label></div>

        <div class="row g-3">
            <div class="col-12 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="nome" class="form-control" placeholder="Nome do paciente"
                        value="<?= h($nomePaciente) ?>">
                </div>
            </div>
            <div class="col-12 col-lg-3">
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
                    <span class="input-group-text"><i class="bi bi-calendar4-range"></i></span>
                    <select name="mes_ref" class="form-select" onchange="this.form.submit()">
                        <option value="" <?= $selectedMonthValue === '' ? 'selected' : '' ?>>Mês do período</option>
                        <?php foreach ($monthOptions as $opt): ?>
                            <option value="<?= h($opt['value']) ?>" <?= $selectedMonthValue === $opt['value'] ? 'selected' : '' ?>>
                                <?= h($opt['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar2-check"></i></span>
                    <select name="quinzena" class="form-select" onchange="this.form.submit()">
                        <option value="">Período livre</option>
                        <option value="1" <?= $quinzenaSel === '1' ? 'selected' : '' ?>>1ª quinzena (01 a 15)</option>
                        <option value="2" <?= $quinzenaSel === '2' ? 'selected' : '' ?>>2ª quinzena (16 ao fim)</option>
                    </select>
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
                    <span class="input-group-text"><i class="bi bi-funnel"></i></span>
                    <select name="filtro_data" class="form-select" onchange="this.form.submit()">
                        <option value="digitacao" <?= (!isset($_GET['filtro_data']) || $_GET['filtro_data'] !== 'final') ? 'selected' : '' ?>>Data de digitação</option>
                        <option value="final" <?= (isset($_GET['filtro_data']) && $_GET['filtro_data'] === 'final') ? 'selected' : '' ?>>Data final</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                    <input type="date" name="dt_inicio" class="form-control" value="<?= h($periodoIni) ?>" placeholder="Início">
                    <input type="date" name="dt_fim" class="form-control" value="<?= h($periodoFim) ?>" placeholder="Fim">
                </div>
            </div>
        </div>
        <input type="hidden" name="dt_base" value="<?= h($dtBase) ?>">

        <input type="hidden" name="sort_field" value="">
        <input type="hidden" name="sort_dir" value="">
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Aplicar</button>
            <button class="btn btn-success" type="submit" name="export" value="1">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Exportar XLSX (Excel)
            </button>
        </div>
    </form>

    <div class="card p-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="form-check form-switch d-flex align-items-center gap-2">
                <input class="form-check-input" type="checkbox" id="chkContasSelectAll">
                <label class="form-check-label" for="chkContasSelectAll">Selecionar todos</label>
            </div>
            <button class="btn btn-primary" id="btnFaturarContas" type="button" disabled>
                <i class="bi bi-currency-dollar me-1"></i>Faturar selecionados
                <span class="badge bg-light text-dark ms-2" id="badgeContasSel">0</span>
            </button>
            <div id="contasFeedback" class="flex-grow-1"></div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle" id="tabelaContas">
                <thead>
                    <tr>
                        <th class="text-center" style="width:70px"><i class="bi bi-check2-square"></i></th>
                        <?php foreach ($visibleFields as $k): ?>
                            <th class="col-<?= h($k) ?>"><?= h($fieldsMap[$k]['label']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows): foreach ($rows as $r): ?>
                        <tr>
                            <td class="text-center">
                                <?php $isFaturado = strtolower($r['conta_faturada_cap'] ?? 'n') === 's'; ?>
                                <input type="checkbox" class="form-check-input chk-conta"
                                    style="transform: scale(1.2);"
                                    value="<?= (int)$r['id_capeante'] ?>" <?= $isFaturado ? 'disabled' : '' ?>>
                            </td>
                            <?php foreach ($visibleFields as $k):
                                $fieldName = $fieldsMap[$k]['field'] ?? $k;
                                $val = $r[$fieldName] ?? '';
                                if (in_array($k, ['valor_apresentado', 'valor_final'], true)) {
                                    $val = number_format((float)$val, 2, ',', '.');
                                } elseif ($k === 'conta_faturada_cap') {
                                    $val = strtolower($r['conta_faturada_cap'] ?? 'n') === 's' ? 'Sim' : 'Não';
                                }
                            ?>
                                <td class="col-<?= h($k) ?>"><?= h($val) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="<?= count($visibleFields) + 1 ?>">Nenhuma conta encontrada no período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php $totalPages = max(1, (int)ceil($total / max(1, $limite))); ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">Total: <?= $total ?> conta(s)</div>
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
        const cells = document.querySelectorAll('#tabelaContas th.col-' + k + ', #tabelaContas td.col-' + k);
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
    document.getElementById('btn-contas-check-all')?.addEventListener('click', () => {
        fieldCheckboxes.forEach(chk => {
            if (!chk.checked) {
                chk.checked = true;
                updateColumnVisibility(chk);
            }
        });
    });
    document.getElementById('btn-contas-uncheck-all')?.addEventListener('click', () => {
        fieldCheckboxes.forEach(chk => {
            if (chk.checked) {
                chk.checked = false;
                updateColumnVisibility(chk);
            }
        });
    });

    const selectAll = document.getElementById('chkContasSelectAll');
    const checkboxes = () => Array.from(document.querySelectorAll('.chk-conta:not(:disabled)'));
    const checked = () => Array.from(document.querySelectorAll('.chk-conta:checked'));
    const badge = document.getElementById('badgeContasSel');
    const btnFaturar = document.getElementById('btnFaturarContas');
    const feedback = document.getElementById('contasFeedback');

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
        if (ev.target.classList.contains('chk-conta')) {
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
        fetch('processa_faturamento_mensal_contas.php', {
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
