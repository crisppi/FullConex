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
        NULLIF({$prefix}data_digit_capeante, '0000-00-00'),
        NULLIF({$prefix}data_fech_capeante, '0000-00-00'),
        NULLIF({$prefix}data_final_capeante, '0000-00-00'),
        NULLIF({$prefix}data_inicial_capeante, '0000-00-00'),
        NULLIF({$prefix}data_create_cap, '0000-00-00'),
        {$prefix}data_create_cap
    )";
}

function buildInternDateExpr(string $alias = '')
{
    $prefix = $alias !== '' ? rtrim($alias, '.') . '.' : '';
    return "COALESCE(
        NULLIF({$prefix}data_create_int, '0000-00-00'),
        NULLIF({$prefix}data_lancamento_int, '0000-00-00'),
        {$prefix}data_create_int
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

$defaultEnd = new DateTime('today');
$defaultStart = (clone $defaultEnd)->modify('-119 days'); // 120 dias padrão

$rawStart = filter_input(INPUT_GET, 'start_date');
$rawEnd   = filter_input(INPUT_GET, 'end_date');

$periodStart = DateTime::createFromFormat('Y-m-d', (string)$rawStart) ?: clone $defaultStart;
$periodEnd   = DateTime::createFromFormat('Y-m-d', (string)$rawEnd) ?: clone $defaultEnd;

if ($periodStart > $periodEnd) {
    [$periodStart, $periodEnd] = [$periodEnd, $periodStart];
}

$rangeDays = max(1, $periodStart->diff($periodEnd)->days + 1);
$periodInputs = [
    'start' => $periodStart->format('Y-m-d'),
    'end'   => $periodEnd->format('Y-m-d'),
];
$periodLabel = $periodStart->format('d/m/Y') . ' a ' . $periodEnd->format('d/m/Y');
$rangeParams = [
    ':dt_ini' => $periodStart->format('Y-m-d 00:00:00'),
    ':dt_fim' => $periodEnd->format('Y-m-d 23:59:59'),
];
$capeanteRangeExpr = buildCapeanteDateExpr('ca');
$internRangeExpr  = buildInternDateExpr('i');

$visitaDateExpr = "COALESCE(
    NULLIF(v.data_visita_vis, '0000-00-00 00:00:00'),
    NULLIF(v.data_visita_vis, '0000-00-00'),
    v.data_visita_vis
)";
$visitaLancExpr = "COALESCE(
    NULLIF(v.data_lancamento_vis, '0000-00-00 00:00:00'),
    NULLIF(v.data_lancamento_vis, '0000-00-00'),
    NULLIF(v.data_visita_vis, '0000-00-00 00:00:00'),
    NULLIF(v.data_visita_vis, '0000-00-00'),
    NOW()
)";
$negociacaoDateExpr = "COALESCE(
    NULLIF(data_inicio_neg, '0000-00-00'),
    NULLIF(data_fim_neg, '0000-00-00'),
    data_inicio_neg,
    data_fim_neg
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
    WHERE {$capeanteRangeExpr} BETWEEN :dt_ini AND :dt_fim
      AND data_inicial_capeante IS NOT NULL
      AND COALESCE(NULLIF(data_fech_capeante,'0000-00-00'), data_digit_capeante) IS NOT NULL",
    $rangeParams,
    0.0
);

$visitasPeriodo = perfFetchValue(
    $conn,
    "SELECT COUNT(*) FROM tb_visita v
      WHERE $visitaDateExpr BETWEEN :dt_ini AND :dt_fim",
    $rangeParams,
    0
);

$tempoMedioVisita = perfFetchValue(
    $conn,
    "SELECT ROUND(AVG(GREATEST(0,
                TIMESTAMPDIFF(DAY,
                    $visitaDateExpr,
                    $visitaLancExpr
                )
            )),1)
       FROM tb_visita v
      WHERE $visitaDateExpr BETWEEN :dt_ini AND :dt_fim",
    $rangeParams,
    0.0
);

$contasPeriodo = perfFetchValue(
    $conn,
    "SELECT COUNT(*) FROM tb_capeante ca
      WHERE {$capeanteRangeExpr} BETWEEN :dt_ini AND :dt_fim",
    $rangeParams,
    0
);

$internacoesPeriodo = perfFetchValue(
    $conn,
    "SELECT COUNT(*) FROM tb_internacao i
      WHERE {$internRangeExpr} BETWEEN :dt_ini AND :dt_fim",
    $rangeParams,
    0
);

$tempoMedioInternacao = perfFetchValue(
    $conn,
    "SELECT ROUND(AVG(GREATEST(0,
                TIMESTAMPDIFF(DAY,
                    COALESCE(
                        NULLIF(i.data_intern_int, '0000-00-00'),
                        i.data_intern_int
                    ),
                    COALESCE(
                        NULLIF(i.data_create_int, '0000-00-00 00:00:00'),
                        NULLIF(i.data_create_int, '0000-00-00'),
                        NULLIF(i.data_lancamento_int, '0000-00-00 00:00:00'),
                        NULLIF(i.data_lancamento_int, '0000-00-00'),
                        i.data_create_int,
                        i.data_lancamento_int
                    )
                )
            )),1)
       FROM tb_internacao i
      WHERE {$internRangeExpr} BETWEEN :dt_ini AND :dt_fim",
    $rangeParams,
    0.0
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
    WHERE $visitaDateExpr BETWEEN :dt_ini AND :dt_fim
    GROUP BY v.fk_usuario_vis
    HAVING visitas_30d > 0
    ORDER BY visitas_30d DESC
    LIMIT 12",
    $rangeParams
);

$negRows = perfFetchAll(
    $conn,
    "SELECT fk_usuario_neg AS auditor_id,
            SUM(CASE WHEN data_fim_neg IS NOT NULL AND data_fim_neg <> '0000-00-00' THEN 1 ELSE 0 END) AS concluidas,
            COUNT(*) AS total
       FROM tb_negociacao
      WHERE {$negociacaoDateExpr} BETWEEN :dt_ini AND :dt_fim
    GROUP BY fk_usuario_neg",
    $rangeParams
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

$capeanteUserKey = "LOWER(TRIM(COALESCE(
    NULLIF(ca.usuario_create_cap,''),
    NULLIF(u.email_user,''),
    NULLIF(u.login_user,''),
    CONCAT('id#', u.id_usuario),
    ''
)))";
$capeanteStartExpr = "
    COALESCE(
        NULLIF(ca.data_inicial_capeante, '0000-00-00'),
        NULLIF(ca.data_final_capeante, '0000-00-00'),
        NULLIF(ca.data_fech_capeante, '0000-00-00'),
        ca.data_inicial_capeante
    )";
$capeanteDigitExpr = "
    COALESCE(
        NULLIF(ca.data_digit_capeante, '0000-00-00 00:00:00'),
        NULLIF(ca.data_digit_capeante, '0000-00-00'),
        NULLIF(ca.data_create_cap, '0000-00-00 00:00:00'),
        NULLIF(ca.data_create_cap, '0000-00-00'),
        ca.data_digit_capeante,
        ca.data_create_cap
    )";
$capeanteSlaDaysExpr = "GREATEST(0, TIMESTAMPDIFF(DAY, {$capeanteStartExpr}, {$capeanteDigitExpr}))";
$capeanteSlaHoursExpr = "GREATEST(0, TIMESTAMPDIFF(HOUR, {$capeanteStartExpr}, {$capeanteDigitExpr}))";
$internUserKey = "LOWER(TRIM(COALESCE(
    NULLIF(i.usuario_create_int,''),
    NULLIF(u.email_user,''),
    NULLIF(u.login_user,''),
    CONCAT('id#', u.id_usuario),
    ''
)))";
$visitaUserKey = "LOWER(TRIM(COALESCE(v.usuario_create,'')))";

$adminRows = perfFetchAll(
    $conn,
    "SELECT 
        {$capeanteUserKey} AS admin_key,
        COALESCE(
            NULLIF(TRIM(ca.usuario_create_cap),''),
            NULLIF(TRIM(u.email_user),''),
            NULLIF(TRIM(u.login_user),''),
            CONCAT('ID ', u.id_usuario),
            'Usuário sem identificação'
        ) AS admin_nome,
        COUNT(*) AS total_contas,
        ROUND(AVG(CASE WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                 THEN GREATEST(0, TIMESTAMPDIFF(HOUR,
                    {$capeanteStartExpr},
                    {$capeanteDigitExpr}
                 ))
            END),1) AS tempo_horas,
        ROUND(SUM(COALESCE(ca.valor_final_capeante, ca.valor_apresentado_capeante)),2) AS valor_total,
        ROUND(AVG(CASE 
            WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                THEN {$capeanteSlaDaysExpr}
        END),1) AS sla_dias,
        ROUND(AVG(CASE 
            WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                THEN {$capeanteSlaHoursExpr}
        END),1) AS tempo_horas
     FROM tb_capeante ca
     LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
    WHERE {$capeanteRangeExpr} BETWEEN :dt_ini AND :dt_fim
      AND (TRIM(ca.usuario_create_cap) <> '' OR u.id_usuario IS NOT NULL)
    GROUP BY admin_key, admin_nome
    HAVING admin_key IS NOT NULL AND admin_key <> ''
    ORDER BY total_contas DESC",
    $rangeParams
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
    WHERE {$capeanteRangeExpr} BETWEEN :dt_ini AND :dt_fim
    GROUP BY mes_ref, etiqueta
    ORDER BY mes_ref ASC",
    $rangeParams
);

$maxMonthlyTotal = 0;
foreach ($adminMonthly as $m) {
    if ((int)$m['total'] > $maxMonthlyTotal) {
        $maxMonthlyTotal = (int)$m['total'];
    }
}
$maxMonthlyTotal = max(1, $maxMonthlyTotal);

$contaTempoRows = perfFetchAll(
    $conn,
    "SELECT 
        {$capeanteUserKey} AS admin_id,
        COALESCE(
            NULLIF(TRIM(ca.usuario_create_cap),''),
            NULLIF(TRIM(u.email_user),''),
            NULLIF(TRIM(u.login_user),''),
            CONCAT('ID ', u.id_usuario),
            'Usuário sem identificação'
        ) AS admin_nome,
        COUNT(*) AS total_registros,
        ROUND(AVG(CASE 
            WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                THEN GREATEST(0, TIMESTAMPDIFF(HOUR, {$capeanteStartExpr}, {$capeanteDigitExpr}))
        END),1) AS tempo_horas
     FROM tb_capeante ca
     LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
    WHERE {$capeanteRangeExpr} BETWEEN :dt_ini AND :dt_fim
      AND (TRIM(ca.usuario_create_cap) <> '' OR u.id_usuario IS NOT NULL)
    GROUP BY admin_id, admin_nome
    HAVING admin_id <> ''
    ORDER BY tempo_horas ASC
    LIMIT 10",
    $rangeParams
);

$internStartExpr = "
    COALESCE(
        NULLIF(i.data_intern_int, '0000-00-00'),
        i.data_intern_int
    )";
$internCreateExpr = "
    COALESCE(
        NULLIF(i.data_create_int, '0000-00-00 00:00:00'),
        NULLIF(i.data_create_int, '0000-00-00'),
        NULLIF(i.data_lancamento_int, '0000-00-00 00:00:00'),
        NULLIF(i.data_lancamento_int, '0000-00-00'),
        i.data_create_int,
        i.data_lancamento_int
    )";

$internTempoRows = perfFetchAll(
    $conn,
    "SELECT 
        {$internUserKey} AS usuario_id,
        COALESCE(
            NULLIF(TRIM(i.usuario_create_int),''),
            NULLIF(TRIM(u.email_user),''),
            NULLIF(TRIM(u.login_user),''),
            CONCAT('ID ', u.id_usuario),
            'Usuário sem identificação'
        ) AS admin_nome,
        COUNT(*) AS total_registros,
        ROUND(AVG(CASE 
            WHEN {$internStartExpr} IS NOT NULL AND {$internCreateExpr} IS NOT NULL
                THEN GREATEST(0, TIMESTAMPDIFF(DAY, {$internStartExpr}, {$internCreateExpr}))
        END),1) AS tempo_dias,
        ROUND(AVG(NULLIF(i.timer_int,0)),1) AS timer_seg
     FROM tb_internacao i
     LEFT JOIN tb_user u ON u.id_usuario = i.fk_usuario_int
    WHERE {$internRangeExpr} BETWEEN :dt_ini AND :dt_fim
      AND (TRIM(i.usuario_create_int) <> '' OR u.id_usuario IS NOT NULL)
    GROUP BY usuario_id, admin_nome
    HAVING usuario_id <> ''
    ORDER BY tempo_dias ASC
    LIMIT 10",
    $rangeParams
);

$rankingContaUsers = perfFetchAll(
    $conn,
    "SELECT 
        {$capeanteUserKey} AS user_key,
        COALESCE(
            NULLIF(TRIM(ca.usuario_create_cap),''),
            NULLIF(TRIM(u.email_user),''),
            NULLIF(TRIM(u.login_user),''),
            CONCAT('ID ', u.id_usuario),
            'Usuário sem identificação'
        ) AS admin_nome,
        COUNT(*) AS total_contas,
        ROUND(SUM(COALESCE(ca.valor_final_capeante, ca.valor_apresentado_capeante)),2) AS valor_total
     FROM tb_capeante ca
     LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
    WHERE {$capeanteRangeExpr} BETWEEN :dt_ini AND :dt_fim
      AND (TRIM(ca.usuario_create_cap) <> '' OR u.id_usuario IS NOT NULL)
    GROUP BY user_key, admin_nome
    HAVING user_key <> ''
    ORDER BY total_contas DESC
    LIMIT 8",
    $rangeParams
);

$visitaLaunchExpr = "
    COALESCE(
        NULLIF(v.data_lancamento_vis, '0000-00-00 00:00:00'),
        NULLIF(v.data_lancamento_vis, '0000-00-00'),
        v.data_lancamento_vis,
        NULLIF(v.data_visita_vis, '0000-00-00 00:00:00'),
        NULLIF(v.data_visita_vis, '0000-00-00'),
        v.data_visita_vis
    )";

$rankingVisitas = perfFetchAll(
    $conn,
    "SELECT 
        {$visitaUserKey} AS user_key,
        COALESCE(NULLIF(TRIM(v.usuario_create),''), 'Usuário sem identificação') AS auditor_nome,
        COUNT(*) AS total_visitas,
        ROUND(AVG(CASE 
            WHEN {$visitaDateExpr} IS NOT NULL AND {$visitaLancExpr} IS NOT NULL
                THEN GREATEST(0, TIMESTAMPDIFF(DAY, {$visitaDateExpr}, {$visitaLancExpr}))
        END),1) AS sla_dias
        ,
        ROUND(AVG(NULLIF(v.timer_vis,0)),1) AS timer_medio_seg
     FROM tb_visita v
     LEFT JOIN tb_user u ON u.id_usuario = v.fk_usuario_vis
    WHERE {$visitaLaunchExpr} IS NOT NULL
      AND TRIM(v.usuario_create) <> ''
      AND {$visitaLaunchExpr} BETWEEN :dt_ini AND :dt_fim
    GROUP BY user_key, auditor_nome
    HAVING user_key <> ''
    ORDER BY total_visitas DESC
    LIMIT 8",
    $rangeParams
);

$centralProfiles = [];
$centralTotals = [
    'contas' => ['total' => 0, 'sla_num' => 0, 'sla_den' => 0, 'tempo_num' => 0, 'tempo_den' => 0, 'valor' => 0],
    'internacoes' => ['total' => 0, 'sla_num' => 0, 'sla_den' => 0, 'timer_num' => 0, 'timer_den' => 0],
    'visitas' => ['total' => 0, 'sla_num' => 0, 'sla_den' => 0, 'timer_num' => 0, 'timer_den' => 0],
];
$registerProfile = function (string $rawKey = null, string $nome = null) use (&$centralProfiles) {
    $key = strtolower(trim((string) $rawKey));
    if ($key === '') {
        return null;
    }
    if (!isset($centralProfiles[$key])) {
        $centralProfiles[$key] = [
            'key' => $key,
            'nome' => $nome ?: 'Usuário sem identificação',
            'contas' => null,
            'internacoes' => null,
            'visitas' => null,
        ];
    } elseif ($nome && ($centralProfiles[$key]['nome'] === 'Usuário sem identificação')) {
        $centralProfiles[$key]['nome'] = $nome;
    }
    return $key;
};

foreach ($adminRows as $row) {
    $key = $registerProfile($row['admin_key'] ?? null, $row['admin_nome'] ?? null);
    if (!$key)
        continue;
    $centralProfiles[$key]['contas'] = [
        'total' => (int) ($row['total_contas'] ?? 0),
        'sla' => $row['sla_dias'] ?? null,
        'tempo' => $row['tempo_horas'] ?? null,
        'valor' => $row['valor_total'] ?? 0,
    ];
    $cnt = $centralProfiles[$key]['contas']['total'];
    $centralTotals['contas']['total'] += $cnt;
    $centralTotals['contas']['valor'] += (float) ($row['valor_total'] ?? 0);
    if ($cnt > 0 && is_numeric($row['sla_dias'])) {
        $centralTotals['contas']['sla_num'] += ((float) $row['sla_dias']) * $cnt;
        $centralTotals['contas']['sla_den'] += $cnt;
    }
    if ($cnt > 0 && is_numeric($row['tempo_horas'])) {
        $centralTotals['contas']['tempo_num'] += ((float) $row['tempo_horas']) * $cnt;
        $centralTotals['contas']['tempo_den'] += $cnt;
    }
}

foreach ($internTempoRows as $row) {
    $key = $registerProfile($row['usuario_id'] ?? null, $row['admin_nome'] ?? null);
    if (!$key)
        continue;
    $centralProfiles[$key]['internacoes'] = [
        'total' => (int) ($row['total_registros'] ?? 0),
        'sla' => $row['tempo_dias'] ?? null,
        'timer' => $row['timer_seg'] ?? null,
    ];
    $cnt = $centralProfiles[$key]['internacoes']['total'];
    $centralTotals['internacoes']['total'] += $cnt;
    if ($cnt > 0 && is_numeric($row['tempo_dias'])) {
        $centralTotals['internacoes']['sla_num'] += ((float) $row['tempo_dias']) * $cnt;
        $centralTotals['internacoes']['sla_den'] += $cnt;
    }
    if ($cnt > 0 && is_numeric($row['timer_seg'])) {
        $centralTotals['internacoes']['timer_num'] += ((float) $row['timer_seg']) * $cnt;
        $centralTotals['internacoes']['timer_den'] += $cnt;
    }
}

foreach ($rankingVisitas as $row) {
    $key = $registerProfile($row['user_key'] ?? null, $row['auditor_nome'] ?? null);
    if (!$key)
        continue;
    $centralProfiles[$key]['visitas'] = [
        'total' => (int) ($row['total_visitas'] ?? 0),
        'sla' => $row['sla_dias'] ?? null,
        'timer' => $row['timer_medio_seg'] ?? null,
    ];
    $cnt = $centralProfiles[$key]['visitas']['total'];
    $centralTotals['visitas']['total'] += $cnt;
    if ($cnt > 0 && is_numeric($row['sla_dias'])) {
        $centralTotals['visitas']['sla_num'] += ((float) $row['sla_dias']) * $cnt;
        $centralTotals['visitas']['sla_den'] += $cnt;
    }
    if ($cnt > 0 && is_numeric($row['timer_medio_seg'])) {
        $centralTotals['visitas']['timer_num'] += ((float) $row['timer_medio_seg']) * $cnt;
        $centralTotals['visitas']['timer_den'] += $cnt;
    }
}

$centralProfilesList = array_values($centralProfiles);
foreach ($centralProfilesList as &$profile) {
    $profile['total_combined'] = (int) ($profile['contas']['total'] ?? 0)
        + (int) ($profile['internacoes']['total'] ?? 0)
        + (int) ($profile['visitas']['total'] ?? 0);
}
unset($profile);
usort($centralProfilesList, function ($a, $b) {
    $diff = ($b['total_combined'] ?? 0) <=> ($a['total_combined'] ?? 0);
    if ($diff !== 0)
        return $diff;
    return strcasecmp($a['nome'] ?? '', $b['nome'] ?? '');
});

$centralSummary = [
    'contas' => [
        'total' => $centralTotals['contas']['total'],
        'sla' => $centralTotals['contas']['sla_den'] ? round($centralTotals['contas']['sla_num'] / $centralTotals['contas']['sla_den'], 1) : null,
        'tempo_horas' => $centralTotals['contas']['tempo_den'] ? round($centralTotals['contas']['tempo_num'] / $centralTotals['contas']['tempo_den'], 1) : null,
        'valor' => $centralTotals['contas']['valor'],
    ],
    'internacoes' => [
        'total' => $centralTotals['internacoes']['total'],
        'sla' => $centralTotals['internacoes']['sla_den'] ? round($centralTotals['internacoes']['sla_num'] / $centralTotals['internacoes']['sla_den'], 1) : null,
        'timer_seg' => $centralTotals['internacoes']['timer_den'] ? round($centralTotals['internacoes']['timer_num'] / $centralTotals['internacoes']['timer_den'], 1) : null,
    ],
    'visitas' => [
        'total' => $centralTotals['visitas']['total'],
        'sla' => $centralTotals['visitas']['sla_den'] ? round($centralTotals['visitas']['sla_num'] / $centralTotals['visitas']['sla_den'], 1) : null,
        'timer_seg' => $centralTotals['visitas']['timer_den'] ? round($centralTotals['visitas']['timer_num'] / $centralTotals['visitas']['timer_den'], 1) : null,
    ],
];

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
            COALESCE(
                NULLIF(TRIM(ca.usuario_create_cap),''),
                NULLIF(TRIM(u.email_user),''),
                NULLIF(TRIM(u.login_user),''),
                CONCAT('ID ', u.id_usuario),
                :fallback
            ) AS admin_nome,
            COUNT(*) AS total_registros,
            ROUND(AVG(CASE WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                      THEN GREATEST(0, TIMESTAMPDIFF(HOUR, {$capeanteStartExpr}, {$capeanteDigitExpr}))
                 END),1) AS tempo_horas
         FROM tb_capeante ca
         LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
        WHERE {$capWhere}
          AND {$capeanteRangeExpr} BETWEEN :dt_ini AND :dt_fim
        LIMIT 1",
        array_merge($rangeParams, $userMatchParamsCap)
    );
    if ($myConta) {
        $myContaStats = $myConta[0];
    }
}

if ($intWhere !== '0') {
    $myIntern = perfFetchAll(
        $conn,
        "SELECT 
            COALESCE(
                NULLIF(TRIM(i.usuario_create_int),''),
                NULLIF(TRIM(u.email_user),''),
                NULLIF(TRIM(u.login_user),''),
                CONCAT('ID ', u.id_usuario),
                :fallback
            ) AS admin_nome,
            COUNT(*) AS total_registros,
            ROUND(AVG(CASE WHEN {$internStartExpr} IS NOT NULL AND {$internCreateExpr} IS NOT NULL
                      THEN GREATEST(0, TIMESTAMPDIFF(DAY, {$internStartExpr}, {$internCreateExpr}))
                 END),1) AS tempo_dias,
            ROUND(AVG(NULLIF(i.timer_int,0)),1) AS timer_seg
         FROM tb_internacao i
         LEFT JOIN tb_user u ON u.id_usuario = i.fk_usuario_int
        WHERE {$intWhere}
          AND {$internRangeExpr} BETWEEN :dt_ini AND :dt_fim
        LIMIT 1",
        array_merge($rangeParams, $userMatchParamsInt)
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

function perfTimerLabel($seconds)
{
    if (!is_numeric($seconds)) {
        return null;
    }
    $minutes = max(0, ((float)$seconds) / 60);
    if ($minutes >= 60) {
        return perfFmt($minutes / 60, 1) . ' h';
    }
    return perfFmt($minutes, 1) . ' min';
}
?>

<style>
.performance-wrapper {
    width: 100%;
    max-width: none;
    margin: 24px 0 60px;
    padding: 0 24px;
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
.perf-card .card-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .8rem;
    background: #f3eef9;
    color: #5b3d7e;
    padding: 4px 10px;
    border-radius: 999px;
    font-weight: 600;
    margin-bottom: 6px;
}
.perf-card .card-pill small {
    font-size: .75rem;
    color: inherit;
    opacity: .9;
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
.perf-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
    margin-bottom: 18px;
}
.perf-filter label {
    font-size: .85rem;
    color: #66557f;
    margin-bottom: 4px;
    display: block;
}
.perf-filter input {
    border: 1px solid #d4c9eb;
    border-radius: 10px;
    padding: 6px 10px;
    font-size: .95rem;
}
.perf-filter button {
    border: none;
    border-radius: 999px;
    background: linear-gradient(120deg,#7c3aed,#9d4edd);
    color: #fff;
    padding: 10px 24px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s ease;
}
.perf-filter button:hover {
    opacity: .9;
}
.perf-range-info {
    color: #6c5a83;
    font-size: .9rem;
    margin-bottom: 22px;
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
    <form class="perf-filter" method="get">
        <div>
            <label for="start_date">Data inicial</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($periodInputs['start']) ?>">
        </div>
        <div>
            <label for="end_date">Data final</label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($periodInputs['end']) ?>">
        </div>
        <button type="submit">Aplicar período</button>
    </form>
    <div class="perf-range-info">
        Período selecionado: <strong><?= $periodLabel ?></strong> (<?= $rangeDays ?> dia<?= $rangeDays > 1 ? 's' : '' ?>)
    </div>

    <div class="perf-grid">
        <div class="perf-card">
            <h3>Contas lançadas (<?= $rangeDays ?>d)</h3>
            <strong><?= perfFmt($contasPeriodo) ?></strong>
            <div class="card-pill">Tempo médio <?= perfFmt($tempoMedioConta, 1) ?> d</div>
            <span>Volume de capeantes digitados no período selecionado.</span>
        </div>
        <div class="perf-card">
            <h3>Visitas registradas (<?= $rangeDays ?>d)</h3>
            <strong><?= perfFmt($visitasPeriodo) ?></strong>
            <div class="card-pill">SLA médio <?= perfFmt($tempoMedioVisita, 1) ?> d</div>
            <span>Produção assistencial com visitas preenchidas.</span>
        </div>
        <div class="perf-card">
            <h3>Internações cadastradas (<?= $rangeDays ?>d)</h3>
            <strong><?= perfFmt($internacoesPeriodo) ?></strong>
            <div class="card-pill">SLA médio <?= perfFmt($tempoMedioInternacao, 1) ?> d</div>
            <span>Entradas criadas pela central administrativa.</span>
        </div>
    </div>

    <div class="perf-sections">
        <div class="perf-panel">
            <h2><i class="bi bi-diagram-3"></i> Central administrativa</h2>
            <?php
            $centralSummaryRows = [
                [
                    'label' => 'Contas',
                    'data' => $centralSummary['contas'],
                    'timer' => isset($centralSummary['contas']['tempo_horas']) ? perfFmt($centralSummary['contas']['tempo_horas'], 1) . ' h' : null,
                ],
                [
                    'label' => 'Internações',
                    'data' => $centralSummary['internacoes'],
                    'timer' => perfTimerLabel($centralSummary['internacoes']['timer_seg'] ?? null),
                ],
                [
                    'label' => 'Visitas',
                    'data' => $centralSummary['visitas'],
                    'timer' => perfTimerLabel($centralSummary['visitas']['timer_seg'] ?? null),
                ],
            ];
            $hasCentralSummary = false;
            foreach ($centralSummaryRows as $row) {
                if (!empty($row['data']['total'])) {
                    $hasCentralSummary = true;
                    break;
                }
            }
            ?>
            <?php if (!$hasCentralSummary): ?>
            <p style="color:#7a6a8a;margin-bottom:0;">Sem lançamentos no período selecionado.</p>
            <?php else: ?>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Frente</th>
                        <th>Registros</th>
                        <th>SLA médio</th>
                        <th>Tempo médio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($centralSummaryRows as $row):
                        $data = $row['data'];
                        if (($data['total'] ?? 0) <= 0)
                            continue;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['label']) ?></td>
                        <td><?= perfFmt($data['total']) ?></td>
                        <td><?= isset($data['sla']) && $data['sla'] !== null ? perfFmt($data['sla'], 1) . ' d' : '—' ?></td>
                        <td><?= $row['timer'] ?? '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
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
            <h2><i class="bi bi-hospital"></i> Tempo de lançamento — Internações</h2>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Profissional</th>
                        <th>Internações <?= $rangeDays ?>d</th>
                        <th>Tempo de lançamento</th>
                        <th>Tempo médio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$internTempoRows): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:24px;color:#7a6a8a;">Sem registros recentes.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($internTempoRows as $row): ?>
                    <tr>
                        <?php $timerCronMin = isset($row['timer_seg']) && $row['timer_seg'] !== null ? round(((float)$row['timer_seg']) / 60, 1) : null; ?>
                        <td><?= htmlspecialchars($row['admin_nome']) ?></td>
                        <td><?= perfFmt($row['total_registros']) ?></td>
                        <td><?= $timerCronMin !== null ? perfFmt($timerCronMin, 1) . ' min' : '—' ?></td>
                        <td><?= is_numeric($row['tempo_dias']) ? perfFmt($row['tempo_dias'], 1) . ' d' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p style="font-size:.85rem;color:#7a6a8a;margin-top:10px;">Tempo calculado entre a data de internação e o
                lançamento feito pelo administrativo. O cronômetro reflete o tempo digitado em tela.</p>
        </div>
    </div>

    <div class="perf-sections" style="margin-top:28px;">
        <div class="perf-panel">
            <h2><i class="bi bi-list-ol"></i> Produtividade — Lançamento de contas</h2>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Total de contas</th>
                        <th>SLA médio</th>
                        <th>Tempo médio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rankingContaUsers): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:24px;color:#7a6a8a;">Sem produtividade registrada
                            nos últimos <?= $rangeDays ?> dias.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rankingContaUsers as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['admin_nome']) ?></td>
                        <td><?= perfFmt($row['total_contas']) ?></td>
                        <td><?= isset($row['sla_dias']) && $row['sla_dias'] !== null ? perfFmt($row['sla_dias'], 1) . ' d' : '—' ?>
                        </td>
                        <td><?= isset($row['tempo_horas']) && $row['tempo_horas'] !== null ? perfFmt($row['tempo_horas'], 1) . ' h' : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="perf-panel">
            <h2><i class="bi bi-journal-check"></i> Produtividade — Lançamento de visitas</h2>
            <table class="perf-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Total de visitas</th>
                        <th>SLA médio</th>
                        <th>Tempo médio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rankingVisitas): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:24px;color:#7a6a8a;">Sem lançamentos registrados nos
                            últimos <?= $rangeDays ?> dias.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rankingVisitas as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['auditor_nome']) ?></td>
                        <td><?= perfFmt($row['total_visitas']) ?></td>
                        <td><?= is_numeric($row['sla_dias']) ? perfFmt($row['sla_dias'], 1) . ' d' : '—' ?></td>
                        <?php
                            $timerMin = null;
                            if (isset($row['timer_medio_seg']) && $row['timer_medio_seg'] !== null) {
                                $timerMin = round(((float)$row['timer_medio_seg']) / 60, 1);
                            }
                        ?>
                        <td><?= $timerMin !== null ? perfFmt($timerMin, 1) . ' min' : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
