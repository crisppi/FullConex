<?php
ob_start();

include_once __DIR__ . "/check_logado.php";
include_once __DIR__ . "/globals.php";
include_once __DIR__ . "/db.php";

/* ==== Helpers ==== */
function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function fmt_br($raw)
{
    $raw = trim((string)$raw);
    if ($raw === '') return '';
    $d10 = substr($raw, 0, 10);
    if ($dt = DateTime::createFromFormat('Y-m-d', $d10)) return $dt->format('d/m/Y');
    if ($dt = DateTime::createFromFormat('d/m/Y', $d10)) return $dt->format('d/m/Y');
    $ts = @strtotime($raw);
    return $ts ? date('d/m/Y', $ts) : $raw;
}
function qs_keep(array $replace = [])
{
    $q = $_GET;
    foreach ($replace as $k => $v) {
        $q[$k] = $v;
    }
    return http_build_query($q);
}

$DEBUG = !empty($_GET['debug']);

/* ==== Tabelas ==== */
$T_INT = 'tb_internacao';
$T_PAC = 'tb_paciente';
$T_HOS = 'tb_hospital';
$T_VIS = 'tb_visita';
$T_ALT = 'tb_alta';
$T_PAT = 'tb_patologia';
$T_CID = 'tb_cid';
$T_USR = 'tb_user';

/* ==== Entrada ==== */
$nomePaciente = trim($_GET['nome'] ?? '');
$hospitalId   = trim($_GET['hospital_id'] ?? '');
$dtIni        = trim($_GET['dt_ini'] ?? ''); // YYYY-MM-DD
$dtFim        = trim($_GET['dt_fim'] ?? ''); // YYYY-MM-DD

if ($dtIni !== '' && $dtFim !== '' && $dtIni > $dtFim) {
    [$dtIni, $dtFim] = [$dtFim, $dtIni];
}

$limite = isset($_GET['limite']) && ctype_digit($_GET['limite']) ? (int)$_GET['limite'] : 20;
$pag    = isset($_GET['pag'])    && ctype_digit($_GET['pag'])    ? (int)$_GET['pag']    : 1;
$limite = max(1, min(1000, $limite));
$pag    = max(1, $pag);
$offset = max(0, ($pag - 1) * $limite);

$isExport = isset($_GET['export']) && $_GET['export'] == '1';

/* ==== Campos exibíveis ==== */
$fieldsMap = [
    'id_visita'       => ['label' => 'ID da visita',     'sql' => "v1.id_visita AS id_visita"],
    'hospital'        => ['label' => 'Hospital',         'sql' => "ho.nome_hosp AS hospital"],
    'nome_paciente'   => ['label' => 'Nome do paciente', 'sql' => "pa.nome_pac AS nome_paciente"],
    'data_internacao' => ['label' => 'Data internação',  'sql' => "i.data_intern_int AS data_internacao"],
    'data_visita'     => ['label' => 'Data visita',      'sql' => "v.data_visita_fmt AS data_visita"],
    'auditor_medico'  => [
        'label' => 'Auditor médico',
        'sql'   => "COALESCE(u.usuario_user, u2.usuario_user, NULLIF(v1.visita_auditor_prof_med,'')) AS auditor_medico"
    ],
    'acomodacao'      => ['label' => 'Acomodação',       'sql' => "i.acomodacao_int AS acomodacao"],
    'patologia'       => ['label' => 'Patologia',        'sql' => "pc.patologia AS patologia"],
    'especialidade'   => ['label' => 'Especialidade',    'sql' => "i.especialidade_int AS especialidade"],
    'alta_flag'       => ['label' => 'Alta',             'sql' => "IF(a1.fk_id_int_alt IS NULL,'Não','Sim') AS alta_flag"],
    'data_alta'       => ['label' => 'Data alta',        'sql' => "a1.data_alta_alt AS data_alta"],
    'cid'             => ['label' => 'CID',              'sql' => "pc.cid AS cid"],
    'rel_visita_vis'  => ['label' => 'Relatório Visita', 'sql' => "v1.rel_visita_vis AS rel_visita_vis"],
];

/* ==== SELECT dinâmico ==== */
$selected = isset($_GET['fields']) && is_array($_GET['fields'])
    ? array_values(array_intersect(array_keys($fieldsMap), $_GET['fields']))
    : array_keys($fieldsMap);
if (!$selected) $selected = array_keys($fieldsMap);

/* ==== Período para escolher visita correspondente ==== */
$params = [];
$vPickDateSQL = '';
if ($dtIni !== '' || $dtFim !== '') {
    $condsPick = [];
    if ($dtIni !== '') {
        $condsPick[] = "CAST(v0.parsed_date AS DATE) >= :pini";
        $params[':pini'] = $dtIni;
    }
    if ($dtFim !== '') {
        $condsPick[] = "CAST(v0.parsed_date AS DATE) <= :pfim";
        $params[':pfim'] = $dtFim;
    }
    $condsPick[] = "v0.parsed_date IS NOT NULL";
    $vPickDateSQL = "WHERE " . implode(' AND ', $condsPick);
}

/* ==== Lógica para focar apenas em 'rel_visita_vis' ==== */
$cleanExpr = "TRIM(REPLACE(REPLACE(v0.rel_visita_vis, CHAR(13), ''), CHAR(10), ''))";
$hasTextExpr = "($cleanExpr IS NOT NULL AND $cleanExpr <> '')";

/* ==== Subselect: LÓGICA para escolher a visita ==== */
$vPick = "
LEFT JOIN (
  SELECT
    v0.fk_internacao_vis AS fk_internacao,
    COALESCE(
      SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN {$hasTextExpr} AND v0.parsed_date IS NOT NULL THEN v0.id_visita END ORDER BY v0.parsed_date DESC, v0.id_visita DESC SEPARATOR ','), ',', 1),
      SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN v0.parsed_date IS NOT NULL THEN v0.id_visita END ORDER BY v0.parsed_date DESC, v0.id_visita DESC SEPARATOR ','), ',', 1),
      SUBSTRING_INDEX(GROUP_CONCAT(v0.id_visita ORDER BY v0.id_visita DESC SEPARATOR ','), ',', 1)
    ) AS id_visita_pick,
    DATE_FORMAT(COALESCE(MAX(CASE WHEN {$hasTextExpr} THEN v0.parsed_date END), MAX(v0.parsed_date)), '%d/%m/%Y') AS data_visita_fmt
  FROM (
    SELECT t.*,
      COALESCE(
        STR_TO_DATE(NULLIF(t.data_visita_vis,''), '%Y-%m-%d %H:%i:%s'), STR_TO_DATE(NULLIF(t.data_visita_vis,''), '%Y-%m-%dT%H:%i:%s'), STR_TO_DATE(NULLIF(t.data_visita_vis,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF(t.data_visita_vis,''), '%d/%m/%Y %H:%i:%s'), STR_TO_DATE(NULLIF(t.data_visita_vis,''), '%d/%m/%Y %H:%i'), STR_TO_DATE(NULLIF(t.data_visita_vis,''), '%d/%m/%Y'),
        STR_TO_DATE(NULLIF(t.data_visita_vis,''), '%d-%m-%Y %H:%i:%s'), STR_TO_DATE(NULLIF(t.data_visita_vis,''), '%d-%m-%Y')
      ) AS parsed_date
    FROM $T_VIS t
    WHERE (t.retificado IS NULL OR t.retificado IN (0,'0','','n','N'))
  ) v0
  $vPickDateSQL
  GROUP BY v0.fk_internacao_vis
) v ON v.fk_internacao = i.id_internacao
";

/* ==== JOINs ==== */
$v1Join = "LEFT JOIN $T_VIS v1 ON v1.id_visita = CAST(v.id_visita_pick AS UNSIGNED)";
$uJoin  = "LEFT JOIN $T_USR u  ON u.id_usuario  = v1.fk_usuario_vis";
$uJoin2 = "LEFT JOIN $T_USR u2 ON u2.id_usuario = CAST(NULLIF(v1.visita_auditor_prof_med,'') AS UNSIGNED)";

$aLast = "
LEFT JOIN (
  SELECT fk_id_int_alt, DATE_FORMAT(MAX(COALESCE(STR_TO_DATE(NULLIF(data_alta_alt,''), '%Y-%m-%d %H:%i:%s'), STR_TO_DATE(NULLIF(data_alta_alt,''), '%Y-%m-%d'), STR_TO_DATE(NULLIF(data_alta_alt,''), '%d/%m/%Y'))), '%d/%m/%Y') AS data_alta_alt
  FROM $T_ALT GROUP BY fk_id_int_alt
) a1 ON a1.fk_id_int_alt = i.id_internacao
";

$pcSub = "
LEFT JOIN (
  SELECT x.id_internacao AS fk_int, GROUP_CONCAT(DISTINCT CONCAT_WS(' - ', c.cat, c.descricao) SEPARATOR ' | ') AS patologia, GROUP_CONCAT(DISTINCT c.cat SEPARATOR ' | ') AS cid
  FROM (SELECT id_internacao, fk_patologia_int AS pid FROM $T_INT UNION ALL SELECT id_internacao, fk_patologia2 AS pid FROM $T_INT) x
  LEFT JOIN $T_PAT p ON p.id_patologia = x.pid LEFT JOIN $T_CID c ON c.id_cid = p.fk_cid_10_pat GROUP BY x.id_internacao
) pc ON pc.fk_int = i.id_internacao
";

/* ==== Base + filtros ==== */
$whereConditions = " WHERE 1=1 ";
$paramsBase = $params;

if ($nomePaciente !== '') {
    $whereConditions .= " AND pa.nome_pac LIKE :nome ";
    $paramsBase[':nome'] = "%$nomePaciente%";
}
if ($hospitalId !== '') {
    $whereConditions .= " AND i.fk_hospital_int = :hid ";
    $paramsBase[':hid'] = $hospitalId;
}

// Se período definido, garante que só traga internações com visita escolhida
if ($dtIni !== '' || $dtFim !== '') {
    $whereConditions .= " AND v.id_visita_pick IS NOT NULL ";
}

$sqlBase = "
FROM $T_INT i
JOIN $T_PAC pa ON pa.id_paciente = i.fk_paciente_int
LEFT JOIN $T_HOS ho ON ho.id_hospital = i.fk_hospital_int
$vPick
$v1Join
$uJoin
$uJoin2
$aLast
$pcSub
$whereConditions
";

/* ==== Ordenação ==== */
$sqlOrder = "
ORDER BY
  COALESCE(STR_TO_DATE(v.data_visita_fmt,'%d/%m/%Y'), STR_TO_DATE(i.data_intern_int,'%Y-%m-%d %H:%i:%s'), STR_TO_DATE(i.data_intern_int,'%Y-%m-%d'), STR_TO_DATE(i.data_intern_int,'%d/%m/%Y')) DESC,
  pa.nome_pac ASC
";

/* ==== SELECT final ==== */
$sqlData = "SELECT " . implode(", ", array_map(fn($k) => $fieldsMap[$k]['sql'], $selected)) . " $sqlBase $sqlOrder LIMIT $limite OFFSET $offset";

/* ==== Contagem ==== */
$total = 0;
$errCount = null;
try {
    $countSql = "SELECT COUNT(DISTINCT i.id_internacao) " . $sqlBase;
    $stc = $conn->prepare($countSql);
    foreach ($paramsBase as $k => $v) $stc->bindValue($k, $v);
    $stc->execute();
    $total = (int)$stc->fetchColumn();
} catch (Throwable $e) {
    $errCount = $e->getMessage();
}

/* ==== Dados ==== */
$rows = [];
$errRows = null;
try {
    $st = $conn->prepare($sqlData);
    foreach ($paramsBase as $k => $v) $st->bindValue($k, $v);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $errRows = $e->getMessage();
}

/* ==== Hospitais ==== */
$hospitais = [];
try {
    $hStmt = $conn->query("SELECT id_hospital, nome_hosp FROM $T_HOS ORDER BY nome_hosp");
    if ($hStmt) $hospitais = $hStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

/* ==== Export (XLSX) ==== */
if ($isExport) {
    try {
        // Carrega os dados com os mesmos filtros/ordenação
        $sql = "SELECT " . implode(", ", array_map(fn($k) => $fieldsMap[$k]['sql'], $selected)) . " $sqlBase $sqlOrder";
        $st  = $conn->prepare($sql);
        foreach ($paramsBase as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        $rowsExp = $st->fetchAll(PDO::FETCH_ASSOC);

        if (ob_get_length()) @ob_end_clean();

        // Autoload PhpSpreadsheet
        require_once __DIR__ . '/vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Visitas');

        // Cabeçalho
        $col = 1;
        foreach ($selected as $k) {
            $sheet->setCellValueByColumnAndRow($col, 1, $fieldsMap[$k]['label']);
            $col++;
        }

        // Linhas
        $row = 2;
        foreach ($rowsExp as $r) {
            $col = 1;
            foreach ($selected as $k) {
                $val = $r[$k] ?? '';
                if (in_array($k, ['data_internacao', 'data_visita', 'data_alta'], true)) {
                    $val = fmt_br($val); // saída dd/mm/aaaa
                }
                // Como texto: preserva zeros à esquerda e evita reinterpretação
                $sheet->setCellValueExplicitByColumnAndRow(
                    $col,
                    $row,
                    (string)$val,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $col++;
            }
            $row++;
        }

        // Estilo e largura
        $sheet->getStyleByColumnAndRow(1, 1, count($selected), 1)->getFont()->setBold(true);
        for ($c = 1; $c <= count($selected); $c++) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }

        // Download
        $fname = "visitas_" . date("Ymd_His") . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fname . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        // $writer->setPreCalculateFormulas(false); // opcional
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
    } catch (Throwable $e) {
        if (ob_get_length()) @ob_end_clean();
        die("EXPORT XLSX ERROR: " . $e->getMessage());
    }
}

/* ==== Render ==== */
include_once __DIR__ . "/templates/header.php";
?>
<style>
    :root {
        --brand: #5e2363;
        --brand-100: #f3e9f8;
    }

    .page-title {
        color: #3A3A3A;
    }

    .card {
        border-radius: 14px;
    }

    .card.shadow-sm {
        box-shadow: 0 8px 24px rgba(0, 0, 0, .06) !important;
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

    .sticky-actions {
        position: sticky;
        bottom: -8px;
        background: #fff;
        padding-top: 6px;
    }

    .input-group>.form-select,
    .input-group>.form-control {
        border-left: 0;
    }

    .input-group-text {
        background: #fff;
    }

    .report-truncate {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        line-height: 1.4;
    }
</style>

<div class="container-fluid" style="margin-top:-10px;">
    <h4 class="page-title mt-0 mb-2">Lista de Visitas</h4>
    <hr class="mt-1 mb-3">

    <?php if ($DEBUG): ?>
        <div class="alert alert-warning">
            <div><strong>DEBUG ON</strong></div>
            <div>Período: <code><?= h($dtIni) ?></code> a <code><?= h($dtFim) ?></code></div>
            <div><u>SQL DATA</u>:</div>
            <div><code style="white-space:pre-wrap"><?= h($sqlData) ?></code></div>
            <div>Params: <code><?= h(json_encode($paramsBase, JSON_UNESCAPED_UNICODE)) ?></code></div>
            <?php if ($errCount) echo "<div>Count error: <code>" . h($errCount) . "</code></div>"; ?>
            <?php if ($errRows)  echo "<div>Rows error: <code>" . h($errRows) . "</code></div>";  ?>
        </div>
    <?php endif; ?>

    <?php
    $fieldIcons = [
        'id_visita'       => 'bi-hash',
        'hospital'        => 'bi-hospital',
        'nome_paciente'   => 'bi-person',
        'data_internacao' => 'bi-calendar2-plus',
        'data_visita'     => 'bi-calendar2-event',
        'cid'             => 'bi-hash',
        'auditor_medico'  => 'bi-person-vcard',
        'acomodacao'      => 'bi-door-closed',
        'patologia'       => 'bi-clipboard2-pulse',
        'especialidade'   => 'bi-stethoscope',
        'alta_flag'       => 'bi-box-arrow-up-right',
        'data_alta'       => 'bi-calendar2-check',
        'rel_visita_vis'  => 'bi-file-earmark-text',
    ];
    ?>

    <form method="get" class="card p-3 mb-3 shadow-sm border-0" id="form-visitas">
        <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <label class="form-label fw-semibold m-0 fs-5">Campos a exibir/exportar</label>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm" id="btn-check-all"><i
                        class="bi bi-check2-all me-1"></i>Selecionar todos</button>
                <button type="button" class="btn btn-light btn-sm" id="btn-uncheck-all"><i
                        class="bi bi-x-lg me-1"></i>Limpar</button>
            </div>
        </div>

        <div class="field-chips d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($fieldsMap as $key => $meta):
                $checked = in_array($key, $selected, true);
                $icon = $fieldIcons[$key] ?? 'bi-check'; ?>
                <input type="checkbox" class="btn-check field-check" id="f_<?= h($key) ?>" name="fields[]"
                    value="<?= h($key) ?>" <?= $checked ? 'checked' : '' ?>>
                <label class="btn btn-outline-brand btn-sm rounded-pill px-3" for="f_<?= h($key) ?>"><i
                        class="bi <?= $icon ?> me-1"></i><?= h($meta['label']) ?></label>
            <?php endforeach; ?>
        </div>

        <div class="mb-2"><label class="form-label fw-semibold m-0">Filtros</label></div>

        <div class="row g-3">
            <div class="col-12 col-lg-3">
                <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input
                        type="text" name="nome" class="form-control" placeholder="Nome do paciente"
                        value="<?= h($nomePaciente) ?>"></div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="input-group"><span class="input-group-text"><i class="bi bi-hospital"></i></span>
                    <select name="hospital_id" class="form-select">
                        <option value="">— Hospital —</option>
                        <?php foreach ($hospitais as $h): ?><option value="<?= $h['id_hospital'] ?>"
                                <?= $hospitalId == $h['id_hospital'] ? 'selected' : '' ?>><?= h($h['nome_hosp']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="input-group"><span class="input-group-text"><i class="bi bi-calendar2"></i></span><input
                        type="date" name="dt_ini" class="form-control" value="<?= h($dtIni) ?>" title="De"></div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="input-group"><span class="input-group-text"><i
                            class="bi bi-calendar2-check"></i></span><input type="date" name="dt_fim"
                        class="form-control" value="<?= h($dtFim) ?>" title="Até"></div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <div class="input-group"><span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                    <select name="limite" class="form-select" onchange="this.form.submit()">
                        <?php foreach ([10, 20, 50, 100] as $opt): ?><option value="<?= $opt ?>"
                                <?= $limite == $opt ? 'selected' : '' ?>><?= $opt ?> por página</option><?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="sticky-actions mt-3 d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Aplicar</button>
            <button class="btn btn-success" type="submit" name="export" value="1">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Exportar XLSX (Excel)
            </button>
            <input type="hidden" name="debug" value="<?= $DEBUG ? 1 : 0 ?>">
        </div>
    </form>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr><?php foreach ($selected as $k): ?><th class="col-<?= h($k) ?>">
                                <?= h($fieldsMap[$k]['label']) ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                    <?php if ($rows): foreach ($rows as $r): ?>
                            <tr>
                                <?php foreach ($selected as $k):
                                    $val = $r[$k] ?? '';
                                    if (in_array($k, ['data_internacao', 'data_visita', 'data_alta'], true)) $val = fmt_br($val);
                                ?>
                                    <td class="col-<?= h($k) ?>">
                                        <?php if ($k === 'rel_visita_vis'): ?>
                                            <div class="report-truncate" title="<?= h($val) ?>"><?= h($val) ?></div>
                                        <?php else: ?>
                                            <?= h($val) ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="<?= count($selected) ?>">Nada encontrado para os filtros aplicados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $totalPages = max(1, (int)ceil($total / max(1, $limite))); ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">Total: <?= (int)$total ?> registro(s)</div>
            <nav>
                <ul class="pagination m-0">
                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>"><a class="page-link"
                            href="?<?= qs_keep(['pag' => 1]) ?>">&laquo;</a></li>
                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>"><a class="page-link"
                            href="?<?= qs_keep(['pag' => max(1, $pag - 1)]) ?>">&lsaquo;</a></li>
                    <li class="page-item disabled"><span class="page-link">Página <?= $pag ?> de
                            <?= $totalPages ?></span></li>
                    <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>"><a class="page-link"
                            href="?<?= qs_keep(['pag' => min($totalPages, $pag + 1)]) ?>">&rsaquo;</a></li>
                    <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>"><a class="page-link"
                            href="?<?= qs_keep(['pag' => $totalPages]) ?>">&raquo;</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const updateColumnVisibility = (checkbox) => {
            const k = checkbox.value;
            const isChecked = checkbox.checked;
            document.querySelectorAll('th.col-' + k + ', td.col-' + k).forEach(cell => {
                cell.style.display = isChecked ? '' : 'none';
            });
        };
        const fieldCheckboxes = document.querySelectorAll('.field-check');
        fieldCheckboxes.forEach(updateColumnVisibility);
        document.addEventListener('change', e => {
            if (e.target.classList.contains('field-check')) {
                updateColumnVisibility(e.target);
            }
        });
        document.getElementById('btn-check-all')?.addEventListener('click', () => {
            fieldCheckboxes.forEach(chk => {
                if (!chk.checked) {
                    chk.checked = true;
                    updateColumnVisibility(chk);
                }
            });
        });
        document.getElementById('btn-uncheck-all')?.addEventListener('click', () => {
            fieldCheckboxes.forEach(chk => {
                if (chk.checked) {
                    chk.checked = false;
                    updateColumnVisibility(chk);
                }
            });
        });
    });
</script>
<?php
include_once __DIR__ . "/templates/footer.php";
ob_end_flush();
