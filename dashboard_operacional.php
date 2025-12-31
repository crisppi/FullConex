<?php
include_once("check_logado.php");
require_once("templates/header.php");

if (!isset($conn) || !($conn instanceof PDO)) {
    die("Conexão não disponível.");
}

function dashFetchCount(PDO $conn, string $sql): int
{
    try {
        $stmt = $conn->query($sql);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('[DASHBOARD_360][COUNT] ' . $e->getMessage());
        return 0;
    }
}

$internacoesAtivas = dashFetchCount(
    $conn,
    "SELECT COUNT(*) FROM tb_internacao WHERE internado_int = 's'"
);

$contasAuditoria = dashFetchCount(
    $conn,
    "SELECT COUNT(*) FROM tb_capeante WHERE COALESCE(encerrado_cap,'n') <> 's'"
);

$visitasAtrasadas = dashFetchCount(
    $conn,
    "SELECT COUNT(*)
       FROM tb_visita
      WHERE DATE(IFNULL(data_visita_vis, DATE(data_lancamento_vis))) < CURDATE()
        AND (data_lancamento_vis IS NULL OR data_lancamento_vis = '0000-00-00 00:00:00')"
);

$negociacoesPendentes = dashFetchCount(
    $conn,
    "SELECT COUNT(*) FROM tb_negociacao WHERE data_fim_neg IS NULL OR data_fim_neg = '0000-00-00'"
);

$eventosCriticos = dashFetchCount(
    $conn,
    "SELECT COUNT(*)
       FROM tb_gestao
      WHERE evento_adverso_ges = 's'
        AND (evento_encerrar_ges IS NULL OR evento_encerrar_ges <> 's')"
);

$cards = [
    [
        'label' => 'Internações ativas',
        'value' => $internacoesAtivas,
        'icon'  => 'bi-hospital',
        'color' => '#2563eb',
        'link'  => 'internacoes/lista',
        'desc'  => 'Pacientes internados em acompanhamento.'
    ],
    [
        'label' => 'Contas em auditoria',
        'value' => $contasAuditoria,
        'icon'  => 'bi-journal-text',
        'color' => '#7c3aed',
        'link'  => 'list_internacao_cap_rah.php',
        'desc'  => 'Capeantes ainda sem encerramento.'
    ],
    [
        'label' => 'Visitas atrasadas',
        'value' => $visitasAtrasadas,
        'icon'  => 'bi-calendar-x',
        'color' => '#dc2626',
        'link'  => 'lista_visitas.php?sort_field=data_visita&sort_dir=asc',
        'desc'  => 'Visitas sem lançamento atualizado.'
    ],
    [
        'label' => 'Negociações pendentes',
        'value' => $negociacoesPendentes,
        'icon'  => 'bi-arrow-repeat',
        'color' => '#ea580c',
        'link'  => 'manual_negociacoes.html',
        'desc'  => 'Registros sem data de conclusão.'
    ],
    [
        'label' => 'Eventos críticos',
        'value' => $eventosCriticos,
        'icon'  => 'bi-exclamation-octagon',
        'color' => '#b91c1c',
        'link'  => 'manual_eventos.html',
        'desc'  => 'Eventos adversos ainda abertos.'
    ],
];

$prioridades = [];
try {
    $sqlScore = "
        SELECT
            i.id_internacao,
            p.nome_pac,
            h.nome_hosp,
            DATEDIFF(CURDATE(), DATE(i.data_intern_int)) AS dias_internado,
            COALESCE(SUM(c.valor_apresentado_capeante), 0) AS valor_apresentado,
            COALESCE(SUM(CASE WHEN g.evento_adverso_ges = 's' AND (g.evento_encerrar_ges IS NULL OR g.evento_encerrar_ges <> 's') THEN 1 ELSE 0 END), 0) AS eventos_abertos
        FROM tb_internacao i
        JOIN tb_paciente  p ON p.id_paciente   = i.fk_paciente_int
        JOIN tb_hospital  h ON h.id_hospital   = i.fk_hospital_int
        LEFT JOIN tb_capeante c ON c.fk_int_capeante = i.id_internacao
        LEFT JOIN tb_gestao   g ON g.fk_internacao_ges = i.id_internacao
        WHERE i.internado_int = 's'
        GROUP BY i.id_internacao
        ORDER BY i.data_intern_int ASC
        LIMIT 30";
    $stmtScore = $conn->prepare($sqlScore);
    $stmtScore->execute();
    $rows = $stmtScore->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $dias     = max(0, (int)($row['dias_internado'] ?? 0));
        $valorApr = (float)($row['valor_apresentado'] ?? 0);
        $eventos  = max(0, (int)($row['eventos_abertos'] ?? 0));

        $score = round(($dias * 1.2) + ($valorApr / 1000) + ($eventos * 5), 1);
        $row['score'] = $score;
        $row['valor_apresentado'] = $valorApr;
        $prioridades[] = $row;
    }

    usort($prioridades, function ($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    $prioridades = array_slice($prioridades, 0, 8);
} catch (Throwable $e) {
    error_log('[DASHBOARD_360][SCORE] ' . $e->getMessage());
    $prioridades = [];
}
?>

<style>
.dashboard-wrapper {
    width: 100%;
    max-width: none;
    margin: 24px 0 60px;
    padding: 0 24px;
}
.dash-hero {
    background: linear-gradient(120deg, #fff6fb, #fbe1f2 60%, #f3cee6);
    color: #3b1d4f;
    border-radius: 18px;
    padding: 32px;
    margin-bottom: 26px;
    border: 1px solid rgba(94, 35, 99, .12);
    box-shadow: 0 20px 45px rgba(94, 35, 99, .15);
}
.dash-hero h1 {
    font-weight: 800;
    letter-spacing: .02em;
    margin-bottom: 8px;
    font-size: clamp(1.8rem, 3vw, 2.4rem);
}
.dash-hero p {
    margin: 0;
    opacity: .85;
}
.dash-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 22px;
}
.dash-card {
    border-radius: 18px;
    padding: 20px;
    background: #fff;
    border: 1px solid rgba(93, 35, 99, .08);
    box-shadow: 0 10px 20px rgba(20, 11, 29, .08);
    transition: transform .15s ease, box-shadow .15s ease;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
}
.dash-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 28px rgba(20, 11, 29, .15);
}
.dash-card .dash-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    margin-bottom: 12px;
    font-size: 1.2rem;
}
.dash-card h3 {
    font-size: .95rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    font-weight: 700;
    margin: 0;
    color: #4b3d59;
}
.dash-card .dash-value {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 10px 0;
    color: #1f1034;
}
.dash-card p {
    margin: 0;
    color: #5a5565;
    font-size: .9rem;
}
.dash-card span {
    margin-top: auto;
    font-size: .85rem;
    color: #5e2363;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.dash-table-card {
    margin-top: 40px;
    border-radius: 18px;
    border: 1px solid rgba(94, 35, 99, .1);
    background: #fff;
    box-shadow: 0 12px 25px rgba(13, 10, 30, .08);
}
.dash-table-card h4 {
    padding: 18px 24px;
    margin: 0;
    border-bottom: 1px solid rgba(94, 35, 99, .1);
    font-weight: 800;
    color: #3b1d4f;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.dash-table-card table {
    width: 100%;
    border-collapse: collapse;
}
.dash-table-card th,
.dash-table-card td {
    padding: 14px 18px;
    font-size: .95rem;
    text-align: left;
}
.dash-table-card th {
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 700;
    color: #7a6a86;
    border-bottom: 1px solid rgba(94, 35, 99, .08);
    background: #fbf7ff;
}
.dash-table-card tr + tr td {
    border-top: 1px solid rgba(94, 35, 99, .05);
}
.badge-score {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border-radius: 999px;
    font-weight: 700;
    font-size: .85rem;
    color: #fff;
    background: linear-gradient(120deg, #5e2363, #a23ec3);
}
.badge-score.low { background: linear-gradient(120deg, #0d9488, #3b82f6); }
.badge-score.mid { background: linear-gradient(120deg, #f97316, #ef4444); }
.badge-score.high { background: linear-gradient(120deg, #be185d, #7e22ce); }
@media (max-width: 768px) {
    .dash-card .dash-value { font-size: 2rem; }
}
</style>

<div class="dashboard-wrapper">
    <div class="dash-hero">
        <h1>Painel Operacional 360°</h1>
        <p>Resumo em tempo real das principais frentes (internação, contas, visitas, negociações e eventos).</p>
    </div>

    <div class="dash-grid">
        <?php foreach ($cards as $card): ?>
        <a class="dash-card" href="<?= $BASE_URL . $card['link'] ?>">
            <div class="dash-icon" style="background: <?= $card['color'] ?>;">
                <i class="bi <?= $card['icon'] ?>"></i>
            </div>
            <h3><?= htmlspecialchars($card['label']) ?></h3>
            <div class="dash-value"><?= number_format($card['value'], 0, ',', '.') ?></div>
            <p><?= htmlspecialchars($card['desc']) ?></p>
            <span>Ver detalhes <i class="bi bi-arrow-right-short"></i></span>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="dash-table-card">
        <h4>
            Score de prioridade por paciente
            <small style="font-size:.85rem;color:#7a6a86;">Fórmula: dias internado (x1.2) + valor apresentado (÷1000) + eventos (x5)</small>
        </h4>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Internação</th>
                        <th>Paciente</th>
                        <th>Hospital</th>
                        <th>Dias</th>
                        <th>Valor apresentado (R$)</th>
                        <th>Eventos</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$prioridades): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;color:#7a6a86;padding:30px;">
                            Nenhum paciente priorizado no momento.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($prioridades as $row):
                        $scoreLabel = $row['score'] >= 25 ? 'high' : ($row['score'] >= 15 ? 'mid' : 'low');
                    ?>
                    <tr>
                        <td>#<?= (int) $row['id_internacao'] ?></td>
                        <td><?= htmlspecialchars($row['nome_pac']) ?></td>
                        <td><?= htmlspecialchars($row['nome_hosp']) ?></td>
                        <td><?= (int) $row['dias_internado'] ?></td>
                        <td>R$ <?= number_format($row['valor_apresentado'], 2, ',', '.') ?></td>
                        <td><?= (int) $row['eventos_abertos'] ?></td>
                        <td><span class="badge-score <?= $scoreLabel ?>"><?= $row['score'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
