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

function fmtMoney($value): string
{
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function fmtInt($value): string
{
    return number_format((int)$value, 0, ',', '.');
}

function hasSinistroData(array $sinistro): bool
{
    return ($sinistro['valor_apresentado'] ?? 0) > 0
        || ($sinistro['valor_final'] ?? 0) > 0
        || ($sinistro['valor_glosa'] ?? 0) > 0;
}

function hasInternaçãoData(array $internacao): bool
{
    return ($internacao['total_internacoes'] ?? 0) > 0
        || ($internacao['total_diarias'] ?? 0) > 0;
}

function hasUtiData(array $uti): bool
{
    return ($uti['total_internacoes'] ?? 0) > 0
        || ($uti['total_diarias'] ?? 0) > 0;
}

function pctOrNull($current, $previous): ?float
{
    if ($previous <= 0) {
        return null;
    }
    return (($current - $previous) / $previous) * 100;
}

$ano = (int)(filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: date('Y'));
$hospitalId = filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) ?: null;
$tipoAdmissão = trim((string)(filter_input(INPUT_GET, 'tipo_admissao') ?? ''));

$hospitais = $conn->query("SELECT id_hospital, nome_hosp FROM tb_hospital ORDER BY nome_hosp")
    ->fetchAll(PDO::FETCH_ASSOC);
$tiposAdm = $conn->query("SELECT DISTINCT tipo_admissao_int FROM tb_internacao WHERE tipo_admissao_int IS NOT NULL AND tipo_admissao_int <> '' ORDER BY tipo_admissao_int")
    ->fetchAll(PDO::FETCH_COLUMN);

function sinistroTotals(PDO $conn, int $ano, ?int $hospitalId, string $tipoAdmissão): array
{
    $dateExpr = "COALESCE(NULLIF(ca.data_inicial_capeante,'0000-00-00'), NULLIF(ca.data_digit_capeante,'0000-00-00'), NULLIF(ca.data_fech_capeante,'0000-00-00'))";
    $where = "ref_date IS NOT NULL AND ref_date <> '0000-00-00' AND YEAR(ref_date) = :ano";
    $params = [':ano' => $ano];
    if ($hospitalId) {
        $where .= " AND fk_hospital_int = :hospital_id";
        $params[':hospital_id'] = $hospitalId;
    }
    if ($tipoAdmissão !== '') {
        $where .= " AND tipo_admissao_int = :tipo";
        $params[':tipo'] = $tipoAdmissão;
    }

    $sql = "
        SELECT
            SUM(valor_apresentado_capeante) AS valor_apresentado,
            SUM(valor_glosa_total) AS valor_glosa,
            SUM(valor_glosa_med) AS valor_glosa_med,
            SUM(valor_glosa_enf) AS valor_glosa_enf,
            SUM(valor_final_capeante) AS valor_final
        FROM (
            SELECT
                ca.valor_apresentado_capeante,
                ca.valor_glosa_total,
                ca.valor_glosa_med,
                ca.valor_glosa_enf,
                ca.valor_final_capeante,
                {$dateExpr} AS ref_date,
                ac.fk_hospital_int,
                ac.tipo_admissao_int
            FROM tb_capeante ca
            INNER JOIN tb_internacao ac ON ac.id_internacao = ca.fk_int_capeante
        ) t
        WHERE {$where}
    ";

    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'valor_apresentado' => (float)($row['valor_apresentado'] ?? 0),
        'valor_glosa' => (float)($row['valor_glosa'] ?? 0),
        'valor_glosa_med' => (float)($row['valor_glosa_med'] ?? 0),
        'valor_glosa_enf' => (float)($row['valor_glosa_enf'] ?? 0),
        'valor_final' => (float)($row['valor_final'] ?? 0),
    ];
}

function internacaoStats(PDO $conn, int $ano, ?int $hospitalId, string $tipoAdmissão): array
{
    $where = "YEAR(i.data_intern_int) = :ano";
    $params = [':ano' => $ano];
    if ($hospitalId) {
        $where .= " AND i.fk_hospital_int = :hospital_id";
        $params[':hospital_id'] = $hospitalId;
    }
    if ($tipoAdmissão !== '') {
        $where .= " AND i.tipo_admissao_int = :tipo";
        $params[':tipo'] = $tipoAdmissão;
    }

    $sql = "
        SELECT
            COUNT(*) AS total_internacoes,
            SUM(GREATEST(1, DATEDIFF(COALESCE(al.data_alta_alt, CURDATE()), i.data_intern_int) + 1)) AS total_diarias
        FROM tb_internacao i
        LEFT JOIN (
            SELECT fk_id_int_alt, MAX(data_alta_alt) AS data_alta_alt
            FROM tb_alta
            GROUP BY fk_id_int_alt
        ) al ON al.fk_id_int_alt = i.id_internacao
        WHERE {$where}
    ";
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $totalInternações = (int)($row['total_internacoes'] ?? 0);
    $totalDiárias = (int)($row['total_diarias'] ?? 0);
    $mp = $totalInternações > 0 ? round($totalDiárias / $totalInternações, 1) : 0.0;

    return [
        'total_internacoes' => $totalInternações,
        'total_diarias' => $totalDiárias,
        'mp' => $mp,
    ];
}

function utiStats(PDO $conn, int $ano, ?int $hospitalId, string $tipoAdmissão): array
{
    $where = "YEAR(data_intern_int) = :ano";
    $params = [':ano' => $ano];
    if ($hospitalId) {
        $where .= " AND fk_hospital_int = :hospital_id";
        $params[':hospital_id'] = $hospitalId;
    }
    if ($tipoAdmissão !== '') {
        $where .= " AND tipo_admissao_int = :tipo";
        $params[':tipo'] = $tipoAdmissão;
    }

    $sql = "
        SELECT
            COUNT(*) AS total_internacoes_uti,
            SUM(GREATEST(1, DATEDIFF(COALESCE(max_data_alta, CURDATE()), min_data_internacao) + 1)) AS total_diarias_uti
        FROM (
            SELECT
                u.fk_internacao_uti,
                MIN(NULLIF(u.data_internacao_uti, '0000-00-00')) AS min_data_internacao,
                MAX(NULLIF(u.data_alta_uti, '0000-00-00')) AS max_data_alta,
                i.fk_hospital_int,
                i.tipo_admissao_int,
                i.data_intern_int
            FROM tb_uti u
            INNER JOIN tb_internacao i ON i.id_internacao = u.fk_internacao_uti
            WHERE u.data_internacao_uti IS NOT NULL AND u.data_internacao_uti <> '0000-00-00'
            GROUP BY u.fk_internacao_uti, i.fk_hospital_int, i.tipo_admissao_int, i.data_intern_int
        ) t
        WHERE {$where}
    ";

    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $totalInternações = (int)($row['total_internacoes_uti'] ?? 0);
    $totalDiárias = (int)($row['total_diarias_uti'] ?? 0);
    $mp = $totalInternações > 0 ? round($totalDiárias / $totalInternações, 1) : 0.0;

    return [
        'total_internacoes' => $totalInternações,
        'total_diarias' => $totalDiárias,
        'mp' => $mp,
    ];
}

$sinistroAtual = sinistroTotals($conn, $ano, $hospitalId, $tipoAdmissão);
$sinistroPrev = sinistroTotals($conn, $ano - 1, $hospitalId, $tipoAdmissão);

$internacaoAtual = internacaoStats($conn, $ano, $hospitalId, $tipoAdmissão);
$internacaoPrev = internacaoStats($conn, $ano - 1, $hospitalId, $tipoAdmissão);

$utiAtual = utiStats($conn, $ano, $hospitalId, $tipoAdmissão);
$utiPrev = utiStats($conn, $ano - 1, $hospitalId, $tipoAdmissão);

$glosaPct = $sinistroAtual['valor_apresentado'] > 0
    ? ($sinistroAtual['valor_glosa'] / $sinistroAtual['valor_apresentado']) * 100
    : 0.0;

$apresentadoVar = pctOrNull($sinistroAtual['valor_apresentado'], $sinistroPrev['valor_apresentado']);
$internacoesVar = pctOrNull($internacaoAtual['total_internacoes'], $internacaoPrev['total_internacoes']);
$diariasVar = pctOrNull($internacaoAtual['total_diarias'], $internacaoPrev['total_diarias']);
$utiVar = pctOrNull($utiAtual['total_internacoes'], $utiPrev['total_internacoes']);

$hospitalNome = 'Todos Hospitais';
if ($hospitalId) {
    foreach ($hospitais as $h) {
        if ((int)$h['id_hospital'] === (int)$hospitalId) {
            $hospitalNome = $h['nome_hosp'];
            break;
        }
    }
}
$tipoLabel = $tipoAdmissão !== '' ? $tipoAdmissão : 'Todos';

$temSinistro = hasSinistroData($sinistroAtual);
$temInternação = hasInternaçãoData($internacaoAtual);
$temUti = hasUtiData($utiAtual);
$temAlgum = $temSinistro || $temInternação || $temUti;
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Dashboard Inteligencia Artificial</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegação">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter">
            <label>Ano</label>
            <input type="number" name="ano" value="<?= e($ano) ?>">
        </div>
        <div class="bi-filter">
            <label>Hospital</label>
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
            <label>Tipo admissão</label>
            <select name="tipo_admissao">
                <option value="">Todos</option>
                <?php foreach ($tiposAdm as $tipo): ?>
                    <option value="<?= e($tipo) ?>" <?= $tipoAdmissão === $tipo ? 'selected' : '' ?>>
                        <?= e($tipo) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="bi-actions">
            <button class="bi-btn" type="submit">Aplicar</button>
        </div>
    </form>

    <div class="bi-panel bi-report">
        <h3>Relatório Anual de Sinistralidade Hospitalar - <?= e($hospitalNome) ?> - (<?= e($ano) ?>)</h3>
        <div class="bi-report-meta">Tipo admissão: <?= e($tipoLabel) ?></div>

        <?php if (!$temAlgum): ?>
            <p>Sem dados para o recorte selecionado.</p>
        <?php endif; ?>

        <?php if ($temSinistro): ?>
        <div class="bi-report-section">
            <h4>1. Analise de Contas Apresentadas</h4>
            <p>
                O valor total das contas apresentadas no ano foi de <strong><?= fmtMoney($sinistroAtual['valor_apresentado']) ?></strong>.
                <?php if ($sinistroPrev['valor_apresentado'] > 0): ?>
                    Em relacao a <?= e($ano - 1) ?> (<?= fmtMoney($sinistroPrev['valor_apresentado']) ?>), houve
                    <?= $apresentadoVar !== null && $apresentadoVar >= 0 ? 'aumento' : 'reducao' ?>
                    de <strong><?= number_format(abs($apresentadoVar ?? 0), 1, ',', '.') ?>%</strong>.
                <?php else: ?>
                    Nao ha base comparativa em <?= e($ano - 1) ?> para este recorte.
                <?php endif; ?>
            </p>
        </div>

        <div class="bi-report-section">
            <h4>2. Resultado Final e Oportunidade de Glosa</h4>
            <p>
                Apos ajustes e auditorias, o valor final consolidado foi de <strong><?= fmtMoney($sinistroAtual['valor_final']) ?></strong>.
                A oportunidade de glosa somou <strong><?= fmtMoney($sinistroAtual['valor_glosa']) ?></strong>,
                representando <strong><?= number_format($glosaPct, 2, ',', '.') ?>%</strong> do valor apresentado.
            </p>
            <p class="bi-report-list">
                Glosa medica: <strong><?= fmtMoney($sinistroAtual['valor_glosa_med']) ?></strong> | 
                Glosa de enfermagem: <strong><?= fmtMoney($sinistroAtual['valor_glosa_enf']) ?></strong>
            </p>
        </div>
        <?php endif; ?>

        <?php if ($temInternação): ?>
        <div class="bi-report-section">
            <h4>3. Internações Gerais</h4>
            <p>
                O total de internacoes registradas foi de <strong><?= fmtInt($internacaoAtual['total_internacoes']) ?></strong>,
                com <strong><?= fmtInt($internacaoAtual['total_diarias']) ?></strong> diarias e MP de
                <strong><?= number_format($internacaoAtual['mp'], 1, ',', '.') ?> dias</strong>.
            </p>
            <p>
                <?php if ($internacaoPrev['total_internacoes'] > 0): ?>
                    Em relacao a <?= e($ano - 1) ?>, a variacao foi de
                    <strong><?= number_format(abs($internacoesVar ?? 0), 1, ',', '.') ?>%</strong> nas internacoes e
                    <strong><?= number_format(abs($diariasVar ?? 0), 1, ',', '.') ?>%</strong> nas diarias.
                <?php else: ?>
                    Nao ha historico comparativo para internacoes no ano anterior.
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>

        <?php if ($temUti): ?>
        <div class="bi-report-section">
            <h4>4. Internações em UTI</h4>
            <p>
                Foram registradas <strong><?= fmtInt($utiAtual['total_internacoes']) ?></strong> internacoes em UTI,
                com <strong><?= fmtInt($utiAtual['total_diarias']) ?></strong> diarias e MP UTI de
                <strong><?= number_format($utiAtual['mp'], 1, ',', '.') ?> dias</strong>.
            </p>
            <p>
                <?php if ($utiPrev['total_internacoes'] > 0): ?>
                    A variacao frente a <?= e($ano - 1) ?> foi de
                    <strong><?= number_format(abs($utiVar ?? 0), 1, ',', '.') ?>%</strong> no volume de internacoes em UTI.
                <?php else: ?>
                    Nao ha historico comparativo de UTI no ano anterior para este recorte.
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
