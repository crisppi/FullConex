<?php
include_once("check_logado.php");
require_once("templates/header.php");

if (!isset($conn) || !($conn instanceof PDO)) {
    die("Conexão não disponível para o painel.");
}

function buildCapeanteDateExpr(string $alias = '')
{
    $prefix = $alias !== '' ? rtrim($alias, '.') . '.' : '';
    return "COALESCE(
        STR_TO_DATE(NULLIF({$prefix}data_digit_capeante,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF({$prefix}data_digit_capeante,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF({$prefix}data_digit_capeante,''), '%d/%m/%Y %H:%i:%s'),
        STR_TO_DATE(NULLIF({$prefix}data_digit_capeante,''), '%d/%m/%Y'),
        STR_TO_DATE(NULLIF({$prefix}data_create_cap,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF({$prefix}data_create_cap,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF({$prefix}data_create_cap,''), '%d/%m/%Y %H:%i:%s'),
        STR_TO_DATE(NULLIF({$prefix}data_create_cap,''), '%d/%m/%Y')
    )";
}

function buildInternDateExpr(string $alias = '')
{
    $prefix = $alias !== '' ? rtrim($alias, '.') . '.' : '';
    return "COALESCE(
        STR_TO_DATE(NULLIF({$prefix}data_create_int,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF({$prefix}data_lancamento_int,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF({$prefix}data_create_int,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF({$prefix}data_create_int,''), '%d/%m/%Y')
    )";
}

function perfFetchValue(PDO $conn, string $sql, array $params = [], $default = 0)
{
    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $val = $stmt->fetchColumn();
        return $val !== false && $val !== null ? $val : $default;
    } catch (Throwable $e) {
        error_log('[PERF_DASH][VALUE] ' . $e->getMessage());
        return $default;
    }
}

function perfFetchAll(PDO $conn, string $sql, array $params = []): array
{
    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('[PERF_DASH][ALL] ' . $e->getMessage());
        return [];
    }
}

$visitaDateExpr = "COALESCE(
    STR_TO_DATE(NULLIF(v.data_visita_vis,''), '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(NULLIF(v.data_visita_vis,''), '%Y-%m-%d'),
    STR_TO_DATE(NULLIF(v.data_visita_vis,''), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(NULLIF(v.data_visita_vis,''), '%d/%m/%Y')
)";
$visitaLancExpr = "COALESCE(
    STR_TO_DATE(NULLIF(v.data_lancamento_vis,''), '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(NULLIF(v.data_lancamento_vis,''), '%Y-%m-%d'),
    STR_TO_DATE(NULLIF(v.data_lancamento_vis,''), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(NULLIF(v.data_lancamento_vis,''), '%d/%m/%Y'),
    NOW()
)";

$tempoMedioConta = perfFetchValue(
    $conn,
    "SELECT ROUND(AVG(GREATEST(0,
                TIMESTAMPDIFF(DAY,
                    COALESCE(data_inicial_capeante, data_digit_capeante),
                    COALESCE(NULLIF(data_fech_capeante,'0000-00-00'), data_digit_capeante)
                )
            )),1)
     FROM tb_capeante
    WHERE data_digit_capeante >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
      AND data_inicial_capeante IS NOT NULL
      AND COALESCE(NULLIF(data_fech_capeante,'0000-00-00'), data_digit_capeante) IS NOT NULL",
    [],
    0.0
);

$visitasUlt30 = perfFetchValue(
    $conn,
    "SELECT COUNT(*) FROM tb_visita v
      WHERE $visitaDateExpr IS NOT NULL
        AND $visitaDateExpr >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
    [],
    0
);

$taxaNegociacao = perfFetchValue(
    $conn,
    "SELECT ROUND(
            SUM(CASE WHEN data_fim_neg IS NOT NULL AND data_fim_neg <> '0000-00-00' THEN 1 ELSE 0 END)
            / NULLIF(COUNT(*),0) * 100, 1)
       FROM tb_negociacao
      WHERE data_inicio_neg IS NULL OR data_inicio_neg >= DATE_SUB(CURDATE(), INTERVAL 120 DAY)",
    [],
    0.0
);

$contasDigitadasMes = perfFetchValue(
    $conn,
    "SELECT COUNT(*) FROM tb_capeante
      WHERE data_digit_capeante >= DATE_FORMAT(CURDATE(), '%Y-%m-01')",
    [],
    0
);

$auditorRows = perfFetchAll(
    $conn,
    "SELECT 
        v.fk_usuario_vis AS auditor_id,
        COALESCE(u.usuario_user, u.nome_user, CONCAT('ID ', v.fk_usuario_vis)) AS auditor,
        COUNT(*) AS visitas_30d,
        ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(DAY, $visitaDateExpr, $visitaLancExpr))),1) AS sla_dias
     FROM tb_visita v
     LEFT JOIN tb_user u ON u.id_usuario = v.fk_usuario_vis
    WHERE $visitaDateExpr IS NOT NULL
      AND $visitaDateExpr >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY v.fk_usuario_vis
    HAVING visitas_30d > 0
    ORDER BY visitas_30d DESC
    LIMIT 12"
);

$negRows = perfFetchAll(
    $conn,
    "SELECT fk_usuario_neg AS auditor_id,
            SUM(CASE WHEN data_fim_neg IS NOT NULL AND data_fim_neg <> '0000-00-00' THEN 1 ELSE 0 END) AS concluidas,
            COUNT(*) AS total
       FROM tb_negociacao
      WHERE data_inicio_neg IS NULL OR data_inicio_neg >= DATE_SUB(CURDATE(), INTERVAL 120 DAY)
    GROUP BY fk_usuario_neg"
);
$negByUser = [];
foreach ($negRows as $row) {
    $negByUser[$row['auditor_id']] = [
        'concluidas' => (int) $row['concluidas'],
        'total'      => (int) $row['total'],
    ];
}

$auditorRanking = [];
foreach ($auditorRows as $row) {
    $auditorId = $row['auditor_id'];
    $neg = $negByUser[$auditorId] ?? ['concluidas' => 0, 'total' => 0];
    $taxa = $neg['total'] > 0 ? round(($neg['concluidas'] / $neg['total']) * 100, 1) : 0.0;
    $score = ($row['visitas_30d'] * 4) + ($taxa * 0.6) - ($row['sla_dias'] * 1.5);
    $auditorRanking[] = [
        'nome'        => $row['auditor'],
        'visitas'     => (int) $row['visitas_30d'],
        'sla'         => (float) $row['sla_dias'],
        'taxa'        => $taxa,
        'neg_total'   => $neg['total'],
        'neg_ok'      => $neg['concluidas'],
        'score'       => round($score, 1),
    ];
}

usort($auditorRanking, function ($a, $b) {
    return $b['score'] <=> $a['score'];
});

$rangeDays = 120;
$capeanteRangeExpr = buildCapeanteDateExpr('ca');
$internRangeExpr = buildInternDateExpr('i');

$capeanteMaxExpr = buildCapeanteDateExpr('');
$internMaxExpr = buildInternDateExpr('');

$latestCapeante = perfFetchValue(
    $conn,
    "SELECT DATE_FORMAT(MAX({$capeanteMaxExpr}), '%Y-%m-%d') FROM tb_capeante"
);
$latestIntern = perfFetchValue(
    $conn,
    "SELECT DATE_FORMAT(MAX({$internMaxExpr}), '%Y-%m-%d') FROM tb_internacao"
);

$capBase = $latestCapeante ?: date('Y-m-d');
$intBase = $latestIntern ?: date('Y-m-d');
$capRangeSql = "DATE_SUB('{$capBase}', INTERVAL {$rangeDays} DAY)";
$intRangeSql = "DATE_SUB('{$intBase}', INTERVAL {$rangeDays} DAY)";

$capeanteUserKey = "CASE 
    WHEN ca.fk_id_aud_adm IS NOT NULL AND ca.fk_id_aud_adm <> 0 THEN CONCAT('id:', ca.fk_id_aud_adm)
    WHEN TRIM(COALESCE(ca.usuario_create_cap,'')) <> '' THEN CONCAT('user:', LOWER(TRIM(ca.usuario_create_cap)))
    ELSE 'user:indefinido'
END";
$internUserKey = "CASE 
    WHEN i.fk_usuario_int IS NOT NULL AND i.fk_usuario_int <> 0 THEN CONCAT('id:', i.fk_usuario_int)
    WHEN TRIM(COALESCE(i.usuario_create_int,'')) <> '' THEN CONCAT('user:', LOWER(TRIM(i.usuario_create_int)))
    ELSE 'user:indefinido'
END";
$visitaUserKey = "CASE 
    WHEN v.fk_usuario_vis IS NOT NULL AND v.fk_usuario_vis <> 0 THEN CONCAT('id:', v.fk_usuario_vis)
    WHEN TRIM(COALESCE(v.usuario_create,'')) <> '' THEN CONCAT('user:', LOWER(TRIM(v.usuario_create)))
    ELSE 'user:indefinido'
END";

$adminRows = perfFetchAll(
    $conn,
    "SELECT 
        {$capeanteUserKey} AS admin_key,
        COALESCE(u.usuario_user, u.nome_user, NULLIF(TRIM(ca.usuario_create_cap),''), 'Usuário sem identificação') AS admin_nome,
        COUNT(*) AS total_contas,
        ROUND(AVG(CASE WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                 THEN GREATEST(0, TIMESTAMPDIFF(HOUR,
                    {$capeanteStartExpr},
                    {$capeanteDigitExpr}
                 ))
            END),1) AS tempo_horas,
        ROUND(SUM(COALESCE(ca.valor_final_capeante, ca.valor_apresentado_capeante)),2) AS valor_total
     FROM tb_capeante ca
     LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
    WHERE {$capeanteRangeExpr} >= {$capRangeSql}
    GROUP BY admin_key, admin_nome
    HAVING admin_key IS NOT NULL AND admin_key <> ''
    ORDER BY total_contas DESC"
);

$adminMonthly = perfFetchAll(
    $conn,
    "SELECT 
        DATE_FORMAT(data_digit_capeante, '%Y-%m-01') AS mes_ref,
        DATE_FORMAT(data_digit_capeante, '%b/%Y') AS etiqueta,
        COUNT(*) AS total,
        ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(DAY,
            COALESCE(data_inicial_capeante, data_digit_capeante),
            COALESCE(NULLIF(data_fech_capeante,'0000-00-00'), data_digit_capeante)
        ))),1) AS tempo
     FROM tb_capeante
    WHERE data_digit_capeante >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes_ref, etiqueta
    ORDER BY mes_ref ASC"
);

$maxMonthlyTotal = 0;
foreach ($adminMonthly as $m) {
    if ((int)$m['total'] > $maxMonthlyTotal) {
        $maxMonthlyTotal = (int)$m['total'];
    }
}
$maxMonthlyTotal = max(1, $maxMonthlyTotal);

$capeanteStartExpr = "
    COALESCE(
        STR_TO_DATE(NULLIF(ca.data_inicial_capeante,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF(ca.data_final_capeante,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF(ca.data_fech_capeante,''), '%Y-%m-%d')
    )";
$capeanteDigitExpr = "
    COALESCE(
        STR_TO_DATE(NULLIF(ca.data_digit_capeante,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF(ca.data_digit_capeante,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF(ca.data_digit_capeante,''), '%d/%m/%Y %H:%i:%s'),
        STR_TO_DATE(NULLIF(ca.data_digit_capeante,''), '%d/%m/%Y'),
        STR_TO_DATE(NULLIF(ca.data_create_cap,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF(ca.data_create_cap,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF(ca.data_create_cap,''), '%d/%m/%Y %H:%i:%s'),
        STR_TO_DATE(NULLIF(ca.data_create_cap,''), '%d/%m/%Y')
    )";

$contaTempoRows = perfFetchAll(
    $conn,
    "SELECT 
        {$capeanteUserKey} AS admin_id,
        COALESCE(u.usuario_user, u.nome_user, NULLIF(TRIM(ca.usuario_create_cap),''), 'Usuário sem identificação') AS admin_nome,
        COUNT(*) AS total_registros,
        ROUND(AVG(CASE 
            WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                THEN GREATEST(0, TIMESTAMPDIFF(HOUR, {$capeanteStartExpr}, {$capeanteDigitExpr}))
        END),1) AS tempo_horas
     FROM tb_capeante ca
     LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
    WHERE {$capeanteRangeExpr} >= {$capRangeSql}
    GROUP BY admin_id, admin_nome
    HAVING admin_id <> ''
    ORDER BY tempo_horas ASC
    LIMIT 10"
);

$internStartExpr = "STR_TO_DATE(NULLIF(i.data_intern_int,''), '%Y-%m-%d')";
$internCreateExpr = "
    COALESCE(
        STR_TO_DATE(NULLIF(i.data_create_int,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF(i.data_lancamento_int,''), '%Y-%m-%d %H:%i:%s')
    )";

$internTempoRows = perfFetchAll(
    $conn,
    "SELECT 
        {$internUserKey} AS usuario_id,
        COALESCE(u.usuario_user, u.nome_user, NULLIF(TRIM(i.usuario_create_int),''), 'Usuário sem identificação') AS admin_nome,
        COUNT(*) AS total_registros,
        ROUND(AVG(CASE 
            WHEN {$internStartExpr} IS NOT NULL AND {$internCreateExpr} IS NOT NULL
                THEN GREATEST(0, TIMESTAMPDIFF(HOUR, {$internStartExpr}, {$internCreateExpr}))
        END),1) AS tempo_horas
     FROM tb_internacao i
     LEFT JOIN tb_user u ON u.id_usuario = i.fk_usuario_int
    WHERE {$internRangeExpr} >= {$intRangeSql}
    GROUP BY usuario_id, admin_nome
    HAVING usuario_id <> ''
    ORDER BY tempo_horas ASC
    LIMIT 10"
);

$rankingContaUsers = perfFetchAll(
    $conn,
    "SELECT 
        {$capeanteUserKey} AS user_key,
        COALESCE(u.usuario_user, u.nome_user, NULLIF(TRIM(ca.usuario_create_cap),''), 'Usuário sem identificação') AS admin_nome,
        COUNT(*) AS total_contas,
        ROUND(SUM(COALESCE(ca.valor_final_capeante, ca.valor_apresentado_capeante)),2) AS valor_total
     FROM tb_capeante ca
     LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
    WHERE {$capeanteRangeExpr} >= {$capRangeSql}
    GROUP BY user_key, admin_nome
    HAVING user_key <> ''
    ORDER BY total_contas DESC
    LIMIT 8"
);

$visitaLaunchExpr = "
    COALESCE(
        STR_TO_DATE(NULLIF(v.data_lancamento_vis,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF(v.data_lancamento_vis,''), '%Y-%m-%d'),
        STR_TO_DATE(NULLIF(v.data_visita_vis,''), '%Y-%m-%d %H:%i:%s'),
        STR_TO_DATE(NULLIF(v.data_visita_vis,''), '%Y-%m-%d')
    )";

$rankingVisitas = perfFetchAll(
    $conn,
    "SELECT 
        {$visitaUserKey} AS user_key,
        COALESCE(u.usuario_user, u.nome_user, NULLIF(TRIM(v.usuario_create),''), 'Usuário sem identificação') AS auditor_nome,
        COUNT(*) AS total_visitas,
        ROUND(AVG(CASE 
            WHEN {$visitaDateExpr} IS NOT NULL AND {$visitaLancExpr} IS NOT NULL
                THEN GREATEST(0, TIMESTAMPDIFF(DAY, {$visitaDateExpr}, {$visitaLancExpr}))
        END),1) AS sla_dias
     FROM tb_visita v
     LEFT JOIN tb_user u ON u.id_usuario = v.fk_usuario_vis
    WHERE {$visitaLaunchExpr} IS NOT NULL
      AND {$visitaLaunchExpr} >= {$capRangeSql}
    GROUP BY user_key, auditor_nome
    HAVING user_key <> ''
    ORDER BY total_visitas DESC
    LIMIT 8"
);

$currentUserId = (int)($_SESSION['id_usuario'] ?? 0);
$currentUserName = $_SESSION['nome_user'] ?? $_SESSION['login_user'] ?? $_SESSION['email_user'] ?? 'Você';
$myContaStats = null;
$myInternStats = null;

$userMatchFiltersCap = [];
$userMatchParamsCap = [':fallback' => $currentUserName];
if ($currentUserId > 0) {
    $userMatchFiltersCap[] = "ca.fk_id_aud_adm = :uid_cap";
    $userMatchParamsCap[':uid_cap'] = $currentUserId;
}
if (!empty($_SESSION['email_user'])) {
    $userMatchFiltersCap[] = "ca.usuario_create_cap = :email_cap";
    $userMatchParamsCap[':email_cap'] = $_SESSION['email_user'];
}
if (!empty($_SESSION['login_user'])) {
    $userMatchFiltersCap[] = "ca.usuario_create_cap = :login_cap";
    $userMatchParamsCap[':login_cap'] = $_SESSION['login_user'];
}
$capWhere = $userMatchFiltersCap ? '(' . implode(' OR ', $userMatchFiltersCap) . ')' : '0';

$userMatchFiltersInt = [];
$userMatchParamsInt = [':fallback' => $currentUserName];
if ($currentUserId > 0) {
    $userMatchFiltersInt[] = "i.fk_usuario_int = :uid_int";
    $userMatchParamsInt[':uid_int'] = $currentUserId;
}
if (!empty($_SESSION['email_user'])) {
    $userMatchFiltersInt[] = "i.usuario_create_int = :email_int";
    $userMatchParamsInt[':email_int'] = $_SESSION['email_user'];
}
if (!empty($_SESSION['login_user'])) {
    $userMatchFiltersInt[] = "i.usuario_create_int = :login_int";
    $userMatchParamsInt[':login_int'] = $_SESSION['login_user'];
}
$intWhere = $userMatchFiltersInt ? '(' . implode(' OR ', $userMatchFiltersInt) . ')' : '0';

if ($capWhere !== '0') {
    $myConta = perfFetchAll(
        $conn,
        "SELECT 
            COALESCE(u.usuario_user, u.nome_user, ca.usuario_create_cap, :fallback) AS admin_nome,
            COUNT(*) AS total_registros,
            ROUND(AVG(CASE WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                      THEN GREATEST(0, TIMESTAMPDIFF(HOUR, {$capeanteStartExpr}, {$capeanteDigitExpr}))
                 END),1) AS tempo_horas
         FROM tb_capeante ca
         LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
        WHERE {$capWhere}
          AND {$capeanteRangeExpr} >= {$capRangeSql}
        LIMIT 1",
        $userMatchParamsCap
    );
    if ($myConta) {
        $myContaStats = $myConta[0];
    }
}

if ($intWhere !== '0') {
    $myIntern = perfFetchAll(
        $conn,
        "SELECT 
            COALESCE(u.usuario_user, u.nome_user, i.usuario_create_int, :fallback) AS admin_nome,
            COUNT(*) AS total_registros,
            ROUND(AVG(CASE WHEN {$internStartExpr} IS NOT NULL AND {$internCreateExpr} IS NOT NULL
                      THEN GREATEST(0, TIMESTAMPDIFF(HOUR, {$internStartExpr}, {$internCreateExpr}))
                 END),1) AS tempo_horas
         FROM tb_internacao i
         LEFT JOIN tb_user u ON u.id_usuario = i.fk_usuario_int
        WHERE {$intWhere}
          AND {$internRangeExpr} >= {$intRangeSql}
        LIMIT 1",
        $userMatchParamsInt
    );
    if ($myIntern) {
        $myInternStats = $myIntern[0];
    }
}

function perfBadge(float $score): array
{
    if ($score >= 120) {
        return ['Elite', '#2563eb'];
    }
    if ($score >= 80) {
        return ['Expert', '#7c3aed'];
    }
    if ($score >= 40) {
        return ['Focus', '#f97316'];
    }
    return ['Boost', '#94a3b8'];
}

function perfFmt($value, $dec = 0)
{
    return number_format($value, $dec, ',', '.');
}
?>

<style>
.performance-wrapper {
    width: min(1650px, 96vw);
    margin: 24px auto 60px;
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont;
}
.perf-hero {
    background: linear-gradient(120deg, #eef2ff, #f1e8ff, #fde8ff);
    border-radius: 22px;
    padding: 32px;
    border: 1px solid rgba(94, 35, 99, .12);
    box-shadow: 0 24px 50px rgba(73, 37, 90, .12);
    margin-bottom: 26px;
}
.perf-hero h1 {
    font-weight: 800;
    margin-bottom: 10px;
    color: #2f1e3a;
}
.perf-hero p {
    margin: 0;
    color: #4b3d59;
    font-size: 1rem;
}
.perf-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 18px;
    margin-bottom: 32px;
}
.personal-grid {
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    margin-bottom: 24px;
}
.personal-card strong {
    font-size: 2.2rem;
}
.personal-card span {
    display: block;
    margin-top: 2px;
    font-size: .85rem;
    color: #6d5c82;
}
.perf-card {
    background: #fff;
    border-radius: 18px;
    padding: 20px;
    border: 1px solid rgba(93, 35, 99, .08);
    box-shadow: 0 10px 25px rgba(20, 11, 29, .08);
}
.perf-card h3 {
    font-size: .9rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin: 0 0 8px;
    color: #5c4c71;
}
.perf-card strong {
    font-size: 2rem;
    color: #1f1728;
}
.perf-card span {
    display: block;
    margin-top: 4px;
    font-size: .85rem;
    color: #6b5c80;
}
.perf-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
    gap: 28px;
}
.perf-panel {
    background: #fff;
    border-radius: 20px;
    border: 1px solid rgba(93, 35, 99, .1);
    box-shadow: 0 16px 32px rgba(17, 10, 25, .08);
    padding: 24px;
}
.perf-panel h2 {
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #321c47;
    margin-bottom: 18px;
}
.perf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .92rem;
}
.perf-table th,
.perf-table td {
    padding: 10px 8px;
    text-align: left;
    border-bottom: 1px solid #f1ecf6;
}
.perf-table th {
    text-transform: uppercase;
    font-size: .75rem;
    letter-spacing: .08em;
    color: #8a7b97;
}
.badge-score {
    border-radius: 999px;
    padding: 4px 12px;
    font-weight: 600;
    font-size: .85rem;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.badge-score i {
    font-size: .9rem;
}
.monthly-bar {
    margin-bottom: 14px;
}
.monthly-bar span {
    font-size: .85rem;
    color: #4a3a5f;
    font-weight: 600;
}
.monthly-bar .bar-track {
    width: 100%;
    background: #f3eef7;
    border-radius: 999px;
    height: 10px;
    margin: 6px 0;
    overflow: hidden;
}
.monthly-bar .bar-fill {
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #7c3aed, #f472b6);
    width: var(--bar, 10%);
}
.monthly-bar small {
    color: #746487;
}
.adm-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid #f0ebf5;
}
.adm-card:last-child {
    border-bottom: none;
}
.adm-card strong {
    color: #311a46;
}
.adm-card em {
    font-style: normal;
    color: #6c5a83;
    font-size: .85rem;
}
@media (max-width: 768px) {
    .perf-card strong {
        font-size: 1.6rem;
    }
}
</style>

<div class="performance-wrapper">
    <div class="perf-hero">
        <h1>Painel de performance das equipes</h1>
        <p>Combine indicadores operacionais com o ritmo da central administrativa para reagir rápido a gargalos e reconhecer resultados.</p>
    </div>

    <div class="perf-grid">
        <div class="perf-card">
            <h3>Tempo médio para fechar conta</h3>
            <strong><?= perfFmt($tempoMedioConta, 1) ?> <small style="font-size:1rem;color:#8a7a9f;">dias</small></strong>
            <span>Média das contas finalizadas nos últimos 90 dias.</span>
        </div>
        <div class="perf-card">
            <h3>Visitas registradas (30 dias)</h3>
            <strong><?= perfFmt($visitasUlt30) ?></strong>
            <span>Produção do time assistencial com visitas lançadas.</span>
        </div>
        <div class="perf-card">
            <h3>Taxa de negociação concluída</h3>
            <strong><?= perfFmt($taxaNegociacao, 1) ?>%</strong>
            <span>Considera negociações fechadas nos últimos 4 meses.</span>
        </div>
        <div class="perf-card">
            <h3>Contas lançadas na central (mês)</h3>
            <strong><?= perfFmt($contasDigitadasMes) ?></strong>
            <span>Volume digitado pela equipe administrativa.</span>
        </div>
    </div>

    <?php if ($currentUserId > 0 && ($myContaStats || $myInternStats)): ?>
    <div class="perf-grid personal-grid">
        <?php if ($myContaStats): ?>
        <?php $tempoConta = $myContaStats['tempo_horas'] ?? null; ?>
        <div class="perf-card personal-card">
            <h3>Seus lançamentos — Contas (<?= $rangeDays ?>d)</h3>
            <strong><?= perfFmt($myContaStats['total_registros'] ?? 0) ?></strong>
            <span><?= is_numeric($tempoConta) ? perfFmt($tempoConta, 1) . ' h médios' : 'Sem base para SLA' ?></span>
            <small style="color:#9a8fb0;">Referência: <?= htmlspecialchars($myContaStats['admin_nome'] ?? $currentUserName) ?></small>
        </div>
        <?php endif; ?>
        <?php if ($myInternStats): ?>
        <?php $tempoInt = $myInternStats['tempo_horas'] ?? null; ?>
        <div class="perf-card personal-card">
            <h3>Seus lançamentos — Internações (<?= $rangeDays ?>d)</h3>
            <strong><?= perfFmt($myInternStats['total_registros'] ?? 0) ?></strong>
            <span><?= is_numeric($tempoInt) ? perfFmt($tempoInt, 1) . ' h médios' : 'Sem base para SLA' ?></span>
            <small style="color:#9a8fb0;">Referência: <?= htmlspecialchars($myInternStats['admin_nome'] ?? $currentUserName) ?></small>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="perf-sections">
        <div class="perf-panel">
            <h2><i class="bi bi-trophy"></i> Ranking operacional (auditores)</h2>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Auditor</th>
                        <th>Visitas</th>
                        <th>SLA médio</th>
                        <th>Negociações</th>
                        <th>Badge</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$auditorRanking): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;padding:26px;color:#7a6a8a;">Sem dados recentes.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($auditorRanking as $row):
                        $badge = perfBadge($row['score']);
                        $badgeColor = $badge[1];
                        $negDesc = $row['neg_total'] > 0 ? "{$row['neg_ok']}/{$row['neg_total']} ({$row['taxa']}%)" : '0';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= perfFmt($row['visitas']) ?></td>
                        <td><?= perfFmt($row['sla'], 1) ?> d</td>
                        <td><?= $negDesc ?></td>
                        <td><span class="badge-score" style="background:<?= $badgeColor ?>;"><i class="bi bi-star-fill"></i><?= $badge[0] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="perf-panel">
            <h2><i class="bi bi-diagram-3"></i> Central administrativa</h2>
            <div>
                <?php if (!$adminRows): ?>
                <p style="color:#7a6a8a;margin-bottom:0;">Sem lançamentos nos últimos 60 dias.</p>
                <?php else: ?>
                <?php foreach ($adminRows as $adm):
                    $tempo = is_numeric($adm['tempo_horas']) ? $adm['tempo_horas'] : 0;
                ?>
                <div class="adm-card">
                    <div>
                        <strong><?= htmlspecialchars($adm['admin_nome']) ?></strong>
                        <em><?= perfFmt($adm['total_contas']) ?> contas • <?= perfFmt($tempo, 1) ?>h por conta</em>
                    </div>
                    <span style="font-weight:600;color:#34a853;">R$ <?= perfFmt($adm['valor_total'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <hr style="margin:20px 0;border-color:#f1ecf6;">
            <h2 style="font-size:1rem;margin-bottom:10px;"><i class="bi bi-graph-up"></i> Produção mensal</h2>
            <?php if (!$adminMonthly): ?>
            <p style="color:#7a6a8a;">Ainda não há histórico suficiente.</p>
            <?php else: ?>
            <?php foreach ($adminMonthly as $mes):
                $pct = round(($mes['total'] / $maxMonthlyTotal) * 100, 1);
            ?>
            <div class="monthly-bar">
                <span><?= htmlspecialchars(ucfirst($mes['etiqueta'])) ?></span>
                <div class="bar-track">
                    <div class="bar-fill" style="--bar:<?= $pct ?>%;"></div>
                </div>
                <small><?= perfFmt($mes['total']) ?> contas • <?= perfFmt($mes['tempo'], 1) ?> dias médios</small>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="perf-sections" style="margin-top:28px;">
        <div class="perf-panel">
            <h2><i class="bi bi-stopwatch"></i> Tempo de lançamento — Contas</h2>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Profissional</th>
                        <th>Tempo médio</th>
                        <th>Volumes <?= $rangeDays ?>d</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$contaTempoRows): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;padding:24px;color:#7a6a8a;">Sem contas lançadas no
                            período.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($contaTempoRows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['admin_nome']) ?></td>
                        <td><?= is_numeric($row['tempo_horas']) ? perfFmt($row['tempo_horas'], 1) . ' h' : '—' ?></td>
                        <td><?= perfFmt($row['total_registros']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p style="font-size:.85rem;color:#7a6a8a;margin-top:10px;">Tempo calculado entre o período enviado no
                capeante e a digitação do administrativo.</p>
        </div>
        <div class="perf-panel">
            <h2><i class="bi bi-hospital"></i> Tempo de lançamento — Internações</h2>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Profissional</th>
                        <th>Tempo médio</th>
                        <th>Internações <?= $rangeDays ?>d</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$internTempoRows): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;padding:24px;color:#7a6a8a;">Sem registros recentes.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($internTempoRows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['admin_nome']) ?></td>
                        <td><?= is_numeric($row['tempo_horas']) ? perfFmt($row['tempo_horas'], 1) . ' h' : '—' ?></td>
                        <td><?= perfFmt($row['total_registros']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p style="font-size:.85rem;color:#7a6a8a;margin-top:10px;">Tempo calculado entre a data de internação e o
                lançamento feito pelo administrativo.</p>
        </div>
    </div>

    <div class="perf-sections" style="margin-top:28px;">
        <div class="perf-panel">
            <h2><i class="bi bi-list-ol"></i> Ranking — Lançamento de contas</h2>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Total de contas</th>
                        <th>Valor total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rankingContaUsers): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;padding:24px;color:#7a6a8a;">Sem produtividade registrada
                            nos últimos <?= $rangeDays ?> dias.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rankingContaUsers as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['admin_nome']) ?></td>
                        <td><?= perfFmt($row['total_contas']) ?></td>
                        <td>R$ <?= perfFmt($row['valor_total'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="perf-panel">
            <h2><i class="bi bi-journal-check"></i> Ranking — Lançamento de visitas</h2>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Total de visitas</th>
                        <th>SLA médio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rankingVisitas): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;padding:24px;color:#7a6a8a;">Sem lançamentos registrados nos
                            últimos <?= $rangeDays ?> dias.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rankingVisitas as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['auditor_nome']) ?></td>
                        <td><?= perfFmt($row['total_visitas']) ?></td>
                        <td><?= is_numeric($row['sla_dias']) ? perfFmt($row['sla_dias'], 1) . ' d' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
