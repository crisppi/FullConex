<?php
// visitas.php — Lista + Filtros + Seleção de campos + Export CSV (tudo em 1 arquivo)
// Requer: check_logado.php, globals.php ($BASE_URL), db.php ($conn),
//         templates/header.php e templates/footer.php

ob_start(); // evita “headers already sent” no export

include_once __DIR__ . "/check_logado.php";
include_once __DIR__ . "/globals.php"; // $BASE_URL
include_once __DIR__ . "/db.php";      // $conn (PDO)

/* ============== Helpers ============== */
function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function fmt_br($raw)
{
    $raw = trim((string)$raw);
    if ($raw === '') return '';
    $d10 = substr($raw, 0, 10);
    $dt  = DateTime::createFromFormat('Y-m-d', $d10);
    if ($dt)  return $dt->format('d/m/Y');
    $dt2 = DateTime::createFromFormat('d/m/Y', $d10);
    if ($dt2) return $dt2->format('d/m/Y');
    $ts = @strtotime($raw);
    return $ts ? date('d/m/Y', $ts) : $raw;
}
function qs_keep(array $replace = [])
{
    $keep = $_GET;
    foreach ($replace as $k => $v) $keep[$k] = $v;
    return http_build_query($keep);
}

/* ============== Tabelas (seu schema) ============== */
$T_INT = 'tb_internacao';  // id_internacao, fk_paciente_int, fk_hospital_int, fk_patologia_int, fk_patologia2, data_intern_int, acomodacao_int, especialidade_int, visita_auditor_prof_med
$T_PAC = 'tb_paciente';    // id_paciente, nome_pac
$T_HOS = 'tb_hospital';    // id_hospital, nome_hosp
$T_VIS = 'tb_visita';      // fk_internacao_vis, data_visita_vis (dd/mm/aaaa), visita_auditor_prof_med, retificado
$T_ALT = 'tb_alta';        // fk_id_int_alt, data_alta_alt (dd/mm/aaaa)
$T_PAT = 'tb_patologia';   // id_patologia, fk_cid_10_pat
$T_CID = 'tb_cid';         // id_cid, cat, descricao

/* ============== Campos exibíveis/exportáveis ============== */
$fieldsMap = [
    'nome_paciente'   => ['label' => 'Nome do paciente',   'sql' => "pa.nome_pac AS nome_paciente"],
    'hospital'        => ['label' => 'Hospital',           'sql' => "ho.nome_hosp AS hospital"],
    'data_internacao' => ['label' => 'Data internação',    'sql' => "i.data_intern_int AS data_internacao"],
    'data_visita'     => ['label' => 'Data visita',        'sql' => "v.data_visita AS data_visita"],
    'auditor_medico'  => ['label' => 'Auditor médico (visita)', 'sql' => "COALESCE(NULLIF(v.auditor_medico,''), NULLIF(i.visita_auditor_prof_med,'')) AS auditor_medico"],
    'acomodacao'      => ['label' => 'Acomodação',         'sql' => "i.acomodacao_int AS acomodacao"],
    'patologia'       => ['label' => 'Patologia',          'sql' => "pc.patologia AS patologia"],
    'especialidade'   => ['label' => 'Especialidade',      'sql' => "i.especialidade_int AS especialidade"],
    'alta_flag'       => ['label' => 'Alta',               'sql' => "IF(a1.fk_id_int_alt IS NULL,'Não','Sim') AS alta_flag"],
    'data_alta'       => ['label' => 'Data alta',          'sql' => "a1.data_alta_alt AS data_alta"],
    'cid'             => ['label' => 'CID',                'sql' => "pc.cid AS cid"],
];

/* ============== Seleção / Filtros / Paginação / Export ============== */
$selected = isset($_GET['fields']) && is_array($_GET['fields'])
    ? array_values(array_intersect(array_keys($fieldsMap), $_GET['fields']))
    : array_keys($fieldsMap);
if (empty($selected)) $selected = array_keys($fieldsMap);

$nomePaciente = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$hospitalId   = isset($_GET['hospital_id']) ? trim($_GET['hospital_id']) : '';
$dtIni        = isset($_GET['dt_ini']) ? trim($_GET['dt_ini']) : '';
$dtFim        = isset($_GET['dt_fim']) ? trim($_GET['dt_fim']) : '';

$limite = isset($_GET['limite']) && ctype_digit($_GET['limite']) ? (int)$_GET['limite'] : 20;
$pag    = isset($_GET['pag'])    && ctype_digit($_GET['pag'])    ? (int)$_GET['pag']    : 1;
$offset = max(0, ($pag - 1) * $limite);

$isExport = isset($_GET['export']) && $_GET['export'] == '1';

/* ============== SELECT dinâmico ============== */
$select = implode(", ", array_map(fn($k) => $fieldsMap[$k]['sql'], $selected));

/* ============== Subconsultas ============== */
/* Última visita por internação (data + auditor, ignora retificados) */
$vSub = "
LEFT JOIN (
  SELECT
    fk_internacao_vis AS fk_internacao,
    MAX(STR_TO_DATE(NULLIF(data_visita_vis,''),'%d/%m/%Y')) AS dt_visita_max,
    SUBSTRING_INDEX(
      GROUP_CONCAT(data_visita_vis ORDER BY STR_TO_DATE(NULLIF(data_visita_vis,''),'%d/%m/%Y') DESC),
      ',', 1
    ) AS data_visita,
    SUBSTRING_INDEX(
      GROUP_CONCAT(NULLIF(visita_auditor_prof_med,'') ORDER BY STR_TO_DATE(NULLIF(data_visita_vis,''),'%d/%m/%Y') DESC),
      ',', 1
    ) AS auditor_medico
  FROM $T_VIS
  WHERE (retificado IS NULL OR retificado = 0 OR retificado = 'n')
  GROUP BY fk_internacao_vis
) v ON v.fk_internacao = i.id_internacao
";

/* Última alta por internação */
$aLast = "
LEFT JOIN (
  SELECT fk_id_int_alt,
         MAX(STR_TO_DATE(NULLIF(data_alta_alt,''),'%d/%m/%Y')) AS dt_alta_max,
         SUBSTRING_INDEX(
           GROUP_CONCAT(data_alta_alt ORDER BY STR_TO_DATE(NULLIF(data_alta_alt,''),'%d/%m/%Y') DESC),
           ',', 1
         ) AS data_alta_alt
  FROM $T_ALT
  GROUP BY fk_id_int_alt
) a1 ON a1.fk_id_int_alt = i.id_internacao
";

/* Patologia/CID usando fk_patologia_int e fk_patologia2 da internação */
$pcSub = "
LEFT JOIN (
  SELECT x.id_internacao AS fk_int,
         GROUP_CONCAT(DISTINCT CONCAT_WS(' - ', c.cat, c.descricao) SEPARATOR ' | ') AS patologia,
         GROUP_CONCAT(DISTINCT c.cat SEPARATOR ' | ') AS cid
  FROM (
      SELECT id_internacao, fk_patologia_int AS pid FROM $T_INT
      UNION ALL
      SELECT id_internacao, fk_patologia2     AS pid FROM $T_INT
  ) x
  LEFT JOIN $T_PAT p ON p.id_patologia = x.pid
  LEFT JOIN $T_CID c ON c.id_cid = p.fk_cid_10_pat
  GROUP BY x.id_internacao
) pc ON pc.fk_int = i.id_internacao
";

/* ============== WHERE + filtros ============== */
$sqlBase = "
FROM $T_INT i
JOIN $T_PAC pa ON pa.id_paciente      = i.fk_paciente_int
LEFT JOIN $T_HOS ho ON ho.id_hospital = i.fk_hospital_int
$vSub
$aLast
$pcSub
WHERE 1=1
";
$params = [];
if ($nomePaciente !== '') {
    $sqlBase .= " AND pa.nome_pac LIKE :nome ";
    $params[':nome'] = "%$nomePaciente%";
}
if ($hospitalId   !== '') {
    $sqlBase .= " AND i.fk_hospital_int = :hid ";
    $params[':hid'] = $hospitalId;
}
if ($dtIni !== '') {
    $sqlBase .= " AND v.dt_visita_max >= :dtini ";
    $params[':dtini'] = $dtIni; // vem do <input type="date"> no formato YYYY-MM-DD
}
if ($dtFim !== '') {
    $sqlBase .= " AND v.dt_visita_max <= :dtfim ";
    $params[':dtfim'] = $dtFim;
}

$sqlOrder = " ORDER BY COALESCE(v.dt_visita_max, STR_TO_DATE(i.data_intern_int,'%d/%m/%Y')) DESC, pa.nome_pac ASC ";

/* ============== Export CSV (antes do HTML) ============== */
if ($isExport) {
    try {
        $sql = "SELECT $select $sqlBase $sqlOrder";
        $st  = $conn->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        if (ob_get_length()) {
            @ob_end_clean();
        }

        $fname = "visitas_" . date("Ymd_His") . ".csv";
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream; charset=UTF-8'); // força download
        header('Content-Disposition: attachment; filename="' . $fname . '"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // BOM UTF-8

        $out = fopen('php://output', 'w');
        $header = array_map(fn($k) => $fieldsMap[$k]['label'], $selected);
        fputcsv($out, $header, ';');

        foreach ($rows as $r) {
            $line = [];
            foreach ($selected as $k) {
                $val = $r[$k] ?? '';
                if (in_array($k, ['data_internacao', 'data_visita', 'data_alta'], true)) $val = fmt_br($val);
                $line[] = $val;
            }
            fputcsv($out, $line, ';');
        }
        fclose($out);
        exit;
    } catch (Throwable $e) {
        if (ob_get_length()) {
            @ob_end_clean();
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo "EXPORT ERROR: " . $e->getMessage() . "\n\nSQL:\n" . ($sql ?? '');
        exit;
    }
}

/* ============== Contagem (pagina) ============== */
$total = 0;
try {
    $stc = $conn->prepare("
    SELECT COUNT(*)
      FROM $T_INT i
      JOIN $T_PAC pa ON pa.id_paciente      = i.fk_paciente_int
      LEFT JOIN $T_HOS ho ON ho.id_hospital = i.fk_hospital_int
      $vSub
     WHERE 1=1
       " . ($nomePaciente !== '' ? " AND pa.nome_pac LIKE :nome " : "") . "
       " . ($hospitalId   !== '' ? " AND i.fk_hospital_int = :hid " : "") . "
       " . ($dtIni        !== '' ? " AND v.dt_visita_max >= :dtini " : "") . "
       " . ($dtFim        !== '' ? " AND v.dt_visita_max <= :dtfim " : "") . "
");

    foreach ($params as $k => $v) $stc->bindValue($k, $v);
    $stc->execute();
    $total = (int)$stc->fetchColumn();
} catch (Throwable $e) {
    $total = 0;
}

/* ============== Dados (pagina) ============== */
$rows = [];
$sql = "SELECT $select $sqlBase $sqlOrder LIMIT $limite OFFSET $offset";
try {
    $st = $conn->prepare($sql);
    foreach ($params as $k => $v) $st->bindValue($k, $v);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
}

/* ============== Hospitais dropdown ============== */
$hospitais = [];
try {
    $hStmt = $conn->query("SELECT id_hospital, nome_hosp FROM $T_HOS ORDER BY nome_hosp");
    if ($hStmt) $hospitais = $hStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

/* ============== Render ============== */
include_once __DIR__ . "/templates/header.php";
?>
<!-- ===== CSS do layout (mantém o visual “bonito”) ===== -->
<style>
:root {
    --brand: #5e2363;
    --brand-700: #4b1c50;
    --brand-800: #431945;
    --brand-100: #f3e9f8;
    --brand-050: #faf5fd;
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

/* Chips (toggle pills) */
.btn-outline-brand {
    border-color: var(--brand);
    color: var(--brand);
    background: #fff;
    transition: .15s ease;
}

.btn-outline-brand:hover {
    background: var(--brand-100);
    color: var(--brand);
    border-color: var(--brand);
}

.btn-check:checked+.btn-outline-brand {
    background: var(--brand);
    color: #fff;
    border-color: var(--brand);
    box-shadow: 0 0 0 .15rem rgba(94, 35, 99, .15);
}

/* Ações grudadas no fim da card */
.sticky-actions {
    position: sticky;
    bottom: -8px;
    background: #fff;
    padding-top: 6px;
}

/* Inputs com ícone agradável */
.input-group>.form-select,
.input-group>.form-control {
    border-left: 0;
}

.input-group-text {
    background: #fff;
}
</style>

<div class="container-fluid" style="margin-top:12px;">
    <h4 class="page-title">Lista de Visitas</h4>
    <hr>

    <?php
    // ícones dos campos (Bootstrap Icons)
    $fieldIcons = [
        'nome_paciente'   => 'bi-person',
        'hospital'        => 'bi-hospital',
        'data_internacao' => 'bi-calendar2-plus',
        'data_visita'     => 'bi-calendar2-event',
        'auditor_medico'  => 'bi-person-vcard',
        'acomodacao'      => 'bi-door-closed',
        'patologia'       => 'bi-clipboard2-pulse',
        'especialidade'   => 'bi-stethoscope',
        'alta_flag'       => 'bi-box-arrow-up-right',
        'data_alta'       => 'bi-calendar2-check',
        'cid'             => 'bi-hash'
    ];
    ?>

    <form method="get" class="card p-3 mb-3 shadow-sm border-0" id="form-visitas">
        <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <label class="form-label fw-semibold m-0 fs-5">Campos a exibir/exportar</label>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm" id="btn-check-all">
                    <i class="bi bi-check2-all me-1"></i>Selecionar todos
                </button>
                <button type="button" class="btn btn-light btn-sm" id="btn-uncheck-all">
                    <i class="bi bi-x-lg me-1"></i>Limpar
                </button>
            </div>
        </div>

        <!-- Chips -->
        <div class="field-chips d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($fieldsMap as $key => $meta):
                $checked = in_array($key, $selected, true);
                $icon    = $fieldIcons[$key] ?? 'bi-check';
            ?>
            <input type="checkbox" class="btn-check field-check" id="f_<?= h($key) ?>" name="fields[]"
                value="<?= h($key) ?>" <?= $checked ? 'checked' : '' ?>>
            <label class="btn btn-outline-brand btn-sm rounded-pill px-3" for="f_<?= h($key) ?>">
                <i class="bi <?= $icon ?> me-1"></i><?= h($meta['label']) ?>
            </label>
            <?php endforeach; ?>
        </div>

        <div class="mb-2">
            <label class="form-label fw-semibold m-0">Filtros</label>
        </div>

        <div class="row g-3">
            <!-- Nome -->
            <div class="col-12 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="nome" class="form-control" placeholder="Nome do paciente"
                        value="<?= h($nomePaciente) ?>">
                </div>
            </div>
            <!-- Hospital -->
            <div class="col-12 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-hospital"></i></span>
                    <select name="hospital_id" class="form-select">
                        <option value="">— Hospital —</option>
                        <?php foreach ($hospitais as $h): ?>
                        <option value="<?= $h['id_hospital'] ?>"
                            <?= $hospitalId == $h['id_hospital'] ? 'selected' : '' ?>>
                            <?= h($h['nome_hosp']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <!-- Período -->
            <div class="col-6 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar2"></i></span>
                    <input type="date" name="dt_ini" class="form-control" value="<?= h($dtIni) ?>" title="De">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar2-check"></i></span>
                    <input type="date" name="dt_fim" class="form-control" value="<?= h($dtFim) ?>" title="Até">
                </div>
            </div>
            <!-- Limite -->
            <div class="col-12 col-sm-6 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                    <select name="limite" class="form-select">
                        <?php foreach ([10, 20, 50, 100] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $limite == $opt ? 'selected' : '' ?>><?= $opt ?> por página
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="sticky-actions mt-3 d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-funnel me-1"></i>Aplicar
            </button>
            <button class="btn btn-success" type="submit" name="export" value="1">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Exportar CSV (Excel)
            </button>
        </div>
    </form>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <?php foreach ($selected as $k): ?>
                        <th class="col-<?= h($k) ?>"><?= h($fieldsMap[$k]['label']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows): foreach ($rows as $r): ?>
                    <tr>
                        <?php foreach ($selected as $k):
                                    $val = $r[$k] ?? '';
                                    if (in_array($k, ['data_internacao', 'data_visita', 'data_alta'], true)) $val = fmt_br($val);
                                ?>
                        <td class="col-<?= h($k) ?>"><?= h($val) ?></td>
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

        <?php $totalPages = max(1, (int)ceil($total / max(1, $limite))); ?>
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">Total: <?= (int)$total ?> registro(s)</div>
            <nav>
                <ul class="pagination m-0">
                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>"><a class="page-link"
                            href="?<?= qs_keep(['pag' => 1]) ?>">&laquo;</a></li>
                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>"><a class="page-link"
                            href="?<?= qs_keep(['pag' => max(1, $pag - 1)]) ?>">&lsaquo;</a></li>
                    <li class="page-item disabled"><span class="page-link">Página <?= (int)$pag ?> de
                            <?= (int)$totalPages ?></span></li>
                    <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>"><a class="page-link"
                            href="?<?= qs_keep(['pag' => min($totalPages, $pag + 1)]) ?>">&rsaquo;</a></li>
                    <li class="page-item <?= $pag >= $totalPages ? 'disabled' : '' ?>"><a class="page-link"
                            href="?<?= qs_keep(['pag' => $totalPages]) ?>">&raquo;</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- ===== Dependências idempotentes (ok repetir) ===== -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- ===== JS do layout (chips + mostrar/ocultar colunas) ===== -->
<script>
// Mostrar/ocultar colunas quando (des)marca os chips
document.addEventListener('change', function(ev) {
    const el = ev.target;
    if (!el.classList.contains('field-check')) return;
    const k = el.value;
    const display = el.checked ? '' : 'none';
    document.querySelectorAll('th.col-' + k + ', td.col-' + k).forEach(function(cell) {
        cell.style.display = display;
    });
});
// Selecionar todos / Limpar
document.getElementById('btn-check-all')?.addEventListener('click', function() {
    document.querySelectorAll('.field-check').forEach(function(chk) {
        if (!chk.checked) {
            chk.checked = true;
            chk.dispatchEvent(new Event('change'));
        }
    });
});
document.getElementById('btn-uncheck-all')?.addEventListener('click', function() {
    document.querySelectorAll('.field-check').forEach(function(chk) {
        if (chk.checked) {
            chk.checked = false;
            chk.dispatchEvent(new Event('change'));
        }
    });
});
</script>
<?php
include_once __DIR__ . "/templates/footer.php";
ob_end_flush();