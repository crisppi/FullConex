<?php
include_once("check_logado.php");
require_once("templates/header.php");

if (!isset($conn) || !($conn instanceof PDO)) {
    die("Conexao invalida.");
}

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$anoInput = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT);
$mesInput = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT);
$ano = ($anoInput !== null && $anoInput !== false) ? (int)$anoInput : null;
$mes = ($mesInput !== null && $mesInput !== false) ? (int)$mesInput : null;
if ($ano === null && !filter_has_var(INPUT_GET, 'ano')) {
    $ano = (int)date('Y');
}

$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;
$auditorNome = trim((string)(filter_input(INPUT_GET, 'auditor') ?? ''));
$profissional = trim((string)(filter_input(INPUT_GET, 'profissional') ?? ''));

$hasUsuarioTable = false;
try {
    $hasUsuarioTable = (bool)$conn->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tb_usuario' LIMIT 1")
        ->fetchColumn();
} catch (Throwable $e) {
    $hasUsuarioTable = false;
}
$usuarioJoin = $hasUsuarioTable ? "LEFT JOIN tb_usuario u ON u.id_usuario = v.fk_usuario_vis" : "";
$auditorFilterExpr = $hasUsuarioTable
    ? "COALESCE(u.usuario_user, NULLIF(v.visita_auditor_prof_med,''), NULLIF(v.visita_auditor_prof_enf,''))"
    : "COALESCE(NULLIF(v.visita_auditor_prof_med,''), NULLIF(v.visita_auditor_prof_enf,''))";
$auditorExpr = $hasUsuarioTable
    ? "COALESCE(u.usuario_user, NULLIF(v.visita_auditor_prof_med,''), NULLIF(v.visita_auditor_prof_enf,''), 'Sem informacoes')"
    : "COALESCE(NULLIF(v.visita_auditor_prof_med,''), NULLIF(v.visita_auditor_prof_enf,''), 'Sem informacoes')";

$hospitais = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp")
    ->fetchAll(PDO::FETCH_ASSOC);
$auditores = $conn->query("SELECT DISTINCT {$auditorFilterExpr} AS auditor_nome
    FROM tb_visita v
    {$usuarioJoin}
    WHERE {$auditorFilterExpr} IS NOT NULL
    ORDER BY auditor_nome")
    ->fetchAll(PDO::FETCH_COLUMN);

$where = "v.fk_internacao_vis IS NOT NULL";
$params = [];
if (!empty($ano)) {
    $where .= " AND YEAR(v.data_visita_vis) = :ano";
    $params[':ano'] = (int)$ano;
}
if (!empty($mes)) {
    $where .= " AND MONTH(v.data_visita_vis) = :mes";
    $params[':mes'] = (int)$mes;
}
if (!empty($hospitalId)) {
    $where .= " AND i.fk_hospital_int = :hospital_id";
    $params[':hospital_id'] = (int)$hospitalId;
}
if (!empty($auditorNome)) {
    $where .= " AND {$auditorFilterExpr} = :auditor_nome";
    $params[':auditor_nome'] = $auditorNome;
}
if ($profissional === 'medico') {
    $where .= " AND (v.visita_med_vis = 's' OR UPPER(v.visita_auditor_prof_med) LIKE 'MED%')";
} elseif ($profissional === 'enfermeiro') {
    $where .= " AND (v.visita_enf_vis = 's' OR UPPER(v.visita_auditor_prof_enf) LIKE 'ENF%')";
}

$seguradoraExpr = "COALESCE(NULLIF(s.seguradora_seg,''), 'Sem informacoes')";
$sql = "
    SELECT
        {$seguradoraExpr} AS seguradora_nome,
        {$auditorExpr} AS auditor_nome,
        h.id_hospital,
        h.nome_hosp,
        COUNT(*) AS total
    FROM tb_visita v
    {$usuarioJoin}
    LEFT JOIN tb_internacao i ON i.id_internacao = v.fk_internacao_vis
    LEFT JOIN tb_paciente pa ON pa.id_paciente = i.fk_paciente_int
    LEFT JOIN tb_seguradora s ON s.id_seguradora = pa.fk_seguradora_pac
    LEFT JOIN tb_hospital h ON h.id_hospital = i.fk_hospital_int
    WHERE {$where}
    GROUP BY seguradora_nome, auditor_nome, h.id_hospital
    ORDER BY seguradora_nome, auditor_nome
";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$matrix = [];
$colTotals = [];
$grandTotal = 0;
foreach ($hospitais as $h) {
    $colTotals[$h['id_hospital']] = 0;
}

foreach ($rows as $row) {
    $seg = $row['seguradora_nome'] ?? 'Sem informacoes';
    $aud = $row['auditor_nome'] ?? 'Sem informacoes';
    $hid = (int)($row['id_hospital'] ?? 0);
    $total = (int)($row['total'] ?? 0);
    if (!isset($matrix[$seg])) {
        $matrix[$seg] = [
            'rows' => [],
            'audTotals' => [],
            'colTotals' => [],
            'sum' => 0,
        ];
    }
    if (!isset($matrix[$seg]['rows'][$aud])) {
        $matrix[$seg]['rows'][$aud] = [];
        $matrix[$seg]['audTotals'][$aud] = 0;
    }
    $matrix[$seg]['rows'][$aud][$hid] = $total;
    $matrix[$seg]['audTotals'][$aud] += $total;
    $matrix[$seg]['colTotals'][$hid] = ($matrix[$seg]['colTotals'][$hid] ?? 0) + $total;
    $matrix[$seg]['sum'] += $total;

    $colTotals[$hid] = ($colTotals[$hid] ?? 0) + $total;
    $grandTotal += $total;
}
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Seguradora Detalhado</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted">Perfil Sinistro 2</div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegação">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter">
            <label>Hospitais</label>
            <select name="hospital_id">
                <option value="">Todos</option>
                <?php foreach ($hospitais as $h): ?>
                    <option value="<?= (int)$h['id_hospital'] ?>" <?= $hospitalId == $h['id_hospital'] ? 'selected' : '' ?>>
                        <?= e($h['nome_hosp']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Mês</label>
            <select name="mes">
                <option value="">Todos</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $mes == $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Ano</label>
            <input type="number" name="ano" value="<?= e($ano) ?>" min="2000" max="2100">
        </div>
        <div class="bi-filter">
            <label>Nome Auditor</label>
            <select name="auditor">
                <option value="">Todos</option>
                <?php foreach ($auditores as $a): ?>
                    <option value="<?= e($a) ?>" <?= $auditorNome === $a ? 'selected' : '' ?>>
                        <?= e($a) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-filter">
            <label>Profissional Auditor</label>
            <select name="profissional">
                <option value="">Todos</option>
                <option value="medico" <?= $profissional === 'medico' ? 'selected' : '' ?>>Medico</option>
                <option value="enfermeiro" <?= $profissional === 'enfermeiro' ? 'selected' : '' ?>>Enfermeiro</option>
            </select>
        </div>
        <div class="bi-actions">
            <button class="bi-btn" type="submit">Aplicar</button>
        </div>
    </form>

    <div class="bi-panel" style="margin-top:16px;">
        <h3 class="text-center" style="margin-bottom:12px;">Quantidade de Visitas</h3>
        <div class="table-responsive">
            <table class="bi-table">
                <thead>
                    <tr>
                        <th>Seguradora</th>
                        <?php foreach ($hospitais as $h): ?>
                            <th><?= e($h['nome_hosp']) ?></th>
                        <?php endforeach; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$matrix): ?>
                        <tr>
                            <td colspan="<?= count($hospitais) + 2 ?>">Sem informacoes</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($matrix as $seg => $segData): ?>
                            <tr>
                                <td><strong><?= e($seg) ?></strong></td>
                                <?php foreach ($hospitais as $h): ?>
                                    <?php $val = $segData['colTotals'][$h['id_hospital']] ?? 0; ?>
                                    <td><?= (int)$val ?></td>
                                <?php endforeach; ?>
                                <td><?= (int)($segData['sum'] ?? 0) ?></td>
                            </tr>
                            <?php foreach ($segData['rows'] as $aud => $data): ?>
                                <tr>
                                    <td>&nbsp;&nbsp;<?= e($aud) ?></td>
                                    <?php foreach ($hospitais as $h): ?>
                                        <?php $val = $data[$h['id_hospital']] ?? 0; ?>
                                        <td><?= (int)$val ?></td>
                                    <?php endforeach; ?>
                                    <td><?= (int)($segData['audTotals'][$aud] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <tr>
                            <td>Total</td>
                            <?php foreach ($hospitais as $h): ?>
                                <td><?= (int)($colTotals[$h['id_hospital']] ?? 0) ?></td>
                            <?php endforeach; ?>
                            <td><?= (int)$grandTotal ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
