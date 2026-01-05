<?php
include_once("check_logado.php");
require_once("templates/header.php");

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$navGroups = [
    [
        'title' => 'Resumo',
        'key' => 'resumo',
        'items' => [
            ['label' => 'Consolidado Gestão', 'href' => 'bi/consolidado'],
            ['label' => 'Consolidado Gestão Cards', 'href' => 'bi/consolidado-cards'],
            ['label' => 'Indicadores BI', 'href' => 'bi/indicadores'],
        ],
    ],
    [
        'title' => 'Clínico',
        'key' => 'clinico',
        'items' => [
            ['label' => 'UTI', 'href' => 'bi/uti'],
            ['label' => 'Patologia', 'href' => 'bi/patologia'],
            ['label' => 'Grupo Patologia', 'href' => 'bi/grupo-patologia'],
            ['label' => 'Antecedente', 'href' => 'bi/antecedente'],
            ['label' => 'Longa Permanência', 'href' => 'bi/longa-permanencia'],
            ['label' => 'Evolução', 'href' => 'bi/evolucao'],
            ['label' => 'Visita Inicial', 'href' => 'bi/visita-inicial'],
            ['label' => 'Estratégia Terapêutica', 'href' => 'bi/estrategia-terapeutica'],
            ['label' => 'Médico Titular', 'href' => 'bi/medico-titular'],
            ['label' => 'Clínico Realizado', 'href' => 'bi/clinico-realizado'],
        ],
    ],
    [
        'title' => 'Auditoria',
        'key' => 'auditoria',
        'items' => [
            ['label' => 'Auditor', 'href' => 'bi/auditor'],
            ['label' => 'Auditor Visitas', 'href' => 'bi/auditor-visitas'],
            ['label' => 'Auditoria Produtividade', 'href' => 'bi/auditoria-produtividade'],
        ],
    ],
    [
        'title' => 'Operacional',
        'key' => 'operacional',
        'items' => [
            ['label' => 'Seguradora', 'href' => 'bi/seguradora'],
            ['label' => 'Seguradora Detalhado', 'href' => 'bi/seguradora-detalhado'],
            ['label' => 'Performance Rede Hospitalar', 'href' => 'bi/performance-rede-hospitalar'],
            ['label' => 'Alto Custo', 'href' => 'bi/alto-custo'],
            ['label' => 'Internações com Risco', 'href' => 'bi/internacoes-risco'],
            ['label' => 'Qualidade e Gestão', 'href' => 'bi/qualidade-gestao'],
            ['label' => 'Home Care', 'href' => 'bi/home-care'],
            ['label' => 'Desospitalizacao', 'href' => 'bi/desospitalizacao'],
            ['label' => 'OPME', 'href' => 'bi/opme'],
            ['label' => 'Evento Adverso', 'href' => 'bi/evento-adverso'],
        ],
    ],
    [
        'title' => 'Rede Hospitalar',
        'key' => 'rede',
        'items' => [
            ['label' => 'Comparativa', 'href' => 'bi/rede-comparativa'],
            ['label' => 'Custo', 'href' => 'bi/rede-custo'],
            ['label' => 'Glosa', 'href' => 'bi/rede-glosa'],
            ['label' => 'Rejeição Capeante', 'href' => 'bi/rede-rejeicao-capeante'],
            ['label' => 'Permanência', 'href' => 'bi/rede-permanencia'],
            ['label' => 'Eventos Adversos', 'href' => 'bi/rede-eventos-adversos'],
            ['label' => 'Readmissão', 'href' => 'bi/rede-readmissao'],
            ['label' => 'Ranking', 'href' => 'bi/rede-ranking'],
        ],
    ],
    [
        'title' => 'Financeiro',
        'key' => 'financeiro',
        'items' => [
            ['label' => 'Financeiro Realizado', 'href' => 'bi/financeiro-realizado'],
            ['label' => 'Sinistro BI', 'href' => 'bi/sinistro'],
            ['label' => 'Perfil Sinistro', 'href' => 'bi/perfil-sinistro'],
            ['label' => 'Sinistro YTD', 'href' => 'bi/sinistro-ytd'],
            ['label' => 'Producao BI', 'href' => 'bi/producao'],
            ['label' => 'Producao YTD', 'href' => 'bi/producao-ytd'],
            ['label' => 'Saving', 'href' => 'bi/saving'],
            ['label' => 'Pacientes', 'href' => 'bi/pacientes'],
            ['label' => 'Hospitais', 'href' => 'bi/hospitais'],
            ['label' => 'Sinistro', 'href' => 'bi/sinistro-bi'],
            ['label' => 'Inteligencia Artificial', 'href' => 'bi/inteligencia'],
        ],
    ],
    [
        'title' => 'Controle de Gastos',
        'key' => 'gastos',
        'items' => [
            ['label' => 'Sinistralidade por Patologia', 'href' => 'bi/gastos-patologia'],
            ['label' => 'Sinistralidade por Hospital', 'href' => 'bi/gastos-hospital'],
            ['label' => 'Tendência de Custo', 'href' => 'bi/gastos-tendencia'],
            ['label' => 'Análise de Alto Custo', 'href' => 'bi/gastos-alto-custo'],
            ['label' => 'Custo Evitável', 'href' => 'bi/gastos-custo-evitavel'],
            ['label' => 'Concentração de Risco', 'href' => 'bi/gastos-concentracao'],
            ['label' => 'Provisão vs Realizado', 'href' => 'bi/gastos-provisao-realizado'],
            ['label' => 'Custo Médio Diárias', 'href' => 'bi/custo-medio-diarias'],
            ['label' => 'Ranking Patologia', 'href' => 'bi/ranking-patologia'],
            ['label' => 'Ranking Hospitais', 'href' => 'bi/ranking-hospitais'],
            ['label' => 'Ranking Pacientes', 'href' => 'bi/ranking-pacientes'],
        ],
    ],
    [
        'title' => 'Anomalias & Fraude',
        'key' => 'anomalias',
        'items' => [
            ['label' => 'Outliers de Permanência', 'href' => 'bi/anomalias-permanencia'],
            ['label' => 'Negociações Suspeitas', 'href' => 'bi/anomalias-negociacao'],
            ['label' => 'OPME sem Justificativa', 'href' => 'bi/anomalias-opme'],
        ],
    ],
    [
        'title' => 'Conformidade & Auditoria',
        'key' => 'conformidade',
        'items' => [
            ['label' => 'Documentação Completa', 'href' => 'bi/auditoria-documentacao'],
            ['label' => 'Tempo de Resposta', 'href' => 'bi/auditoria-resposta'],
        ],
    ],
    [
        'title' => 'Segmentação de Risco',
        'key' => 'risco',
        'items' => [
            ['label' => 'Pacientes Crônicos', 'href' => 'bi/risco-cronicos'],
            ['label' => 'Risco Readmissão', 'href' => 'bi/risco-readmissao'],
            ['label' => 'Casos Caros Previsíveis', 'href' => 'bi/risco-casos-caros'],
        ],
    ],
    [
        'title' => 'Negociação & Rede',
        'key' => 'negociacao',
        'items' => [
            ['label' => 'Volume vs Custo', 'href' => 'bi/rede-volume-custo'],
            ['label' => 'Mix de Casos', 'href' => 'bi/rede-mix-casos'],
            ['label' => 'Elasticidade de Preço', 'href' => 'bi/rede-elasticidade'],
        ],
    ],
    [
        'title' => 'Qualidade & Desfecho',
        'key' => 'qualidade',
        'items' => [
            ['label' => 'Eventos Adversos', 'href' => 'bi/qualidade-eventos'],
            ['label' => 'Óbitos', 'href' => 'bi/qualidade-obitos'],
        ],
    ],
    [
        'title' => 'Inteligência',
        'key' => 'inteligencia',
        'items' => [
            ['label' => 'Tempo Médio Permanência', 'href' => 'inteligencia/tmp'],
            ['label' => 'Prorrogacao x Alta', 'href' => 'inteligencia/prorrogacao-vs-alta'],
            ['label' => 'Motivos Prorrogacao', 'href' => 'inteligencia/motivos-prorrogacao'],
            ['label' => 'Backlog Autorizacoes', 'href' => 'inteligencia/backlog-autorizacoes'],
        ],
    ],
];
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Navegação BI</h1>
        <div class="bi-header-actions">
            <a class="bi-btn bi-btn-secondary" href="javascript:history.back()" title="Voltar">Voltar</a>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi/navegacao" title="Navegação">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <div class="bi-panel">
        <div class="bi-nav-title">Painel de Navegação</div>
        <?php foreach ($navGroups as $group): ?>
            <div class="bi-nav-group" data-theme="<?= e($group['key']) ?>">
                <div class="bi-nav-group-title"><?= e($group['title']) ?></div>
                <div class="bi-nav-grid">
                    <?php foreach ($group['items'] as $link): ?>
                        <a class="bi-nav-card" href="<?= $BASE_URL . e($link['href']) ?>">
                            <?= e($link['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
