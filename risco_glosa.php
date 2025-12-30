<?php
include_once("check_logado.php");
include_once("globals.php");
require_once("app/services/OperationalIntelligenceService.php");

$service = new OperationalIntelligenceService($conn);
$glosaData = $service->glosaRiskAlerts();

include_once("templates/header.php");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Oportunidade de glosa / conta parada</title>
    <style>
        .insight-card {
            border-radius: 12px;
            border: 1px solid #e7e7e7;
            background: #fff;
            margin: 0.5rem 0 1rem;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(95,35,99,0.08);
        }
        .alert {
            margin-bottom: 0;
        }
        .risk-pill {
            display:inline-flex;
            align-items:center;
            gap:6px;
            border-radius:999px;
            padding:0.2rem 0.75rem;
            font-weight:600;
        }
        .risk-pill.alto {background:#fee2e2;color:#991b1b;}
        .risk-pill.moderado {background:#fff1c3;color:#a15c00;}
        .risk-pill.baixo {background:#dcfce7;color:#166534;}
        .factor-chip {
            display:inline-flex;
            align-items:center;
            background:#f4f4ff;
            color:#4338ca;
            font-size:0.82rem;
            border-radius:999px;
            padding:0.2rem 0.65rem;
            margin:0.15rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="margin-top:24px; padding:0 0 16px;">
        <div class="row mb-2">
            <div class="col-12">
                <h2 class="mb-0 fw-semibold" style="color:#5e2363;">Painel de oportunidade de glosa</h2>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-12">
                <div class="alert alert-info">
                    <strong>Oportunidade de glosa ou conta parada</strong> — visão dedicada da auditoria da operadora. 
                    Identifica contas com maior potencial de glosa (glosa alta, processos parados, auditoria sem retorno) e sugere ações antes do faturamento.
                </div>
            </div>
        </div>

        <div class="insight-card">
            <?php if (!empty($glosaData['available'])): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Conta / Paciente</th>
                            <th>Hospital</th>
                            <th>Operadora</th>
                            <th class="text-center">Dias em aberto</th>
                            <th class="text-center">Oportunidade de glosa</th>
                            <th>Fatores chave</th>
                            <th>Ação recomendada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($glosaData['entries'] as $entry): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold">Capeante #<?= (int)$entry['id_capeante'] ?> · <?= htmlspecialchars($entry['nome_pac'] ?? 'Paciente') ?></div>
                                <small class="text-muted">
                                    Internação <?= (int)$entry['internacao_id'] ?> · Início <?= $entry['data_inicial'] ? date('d/m/Y', strtotime($entry['data_inicial'])) : '—' ?>
                                </small>
                            </td>
                            <td><?= htmlspecialchars($entry['nome_hosp'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($entry['operadora'] ?? '-') ?></td>
                            <td class="text-center fw-semibold"><?= (int)$entry['dias_aberto'] ?>d</td>
                            <td class="text-center">
                                <div class="risk-pill <?= $entry['risk_level'] ?>">
                                    <?= ucfirst($entry['risk_level']) ?> · <?= number_format($entry['probability'] * 100, 1) ?>%
                                </div>
                                <small class="d-block text-muted">
                                    Glosa projetada: <?= number_format($entry['glosa_ratio'] * 100, 1) ?>% | Valor: R$ <?= number_format($entry['valor_glosa'], 2, ',', '.') ?>
                                </small>
                            </td>
                            <td>
                                <?php foreach ($entry['factors'] as $factor): ?>
                                <span class="factor-chip"><?= htmlspecialchars($factor) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <div class="small"><?= htmlspecialchars($entry['recommendation']) ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted"><?= htmlspecialchars($glosaData['message']) ?></small>
            <?php else: ?>
            <p class="text-muted mb-0"><?= htmlspecialchars($glosaData['message'] ?? 'Sem dados para análise.') ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
