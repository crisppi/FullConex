<?php
include_once("check_logado.php");
require_once("templates/header.php");

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$links = [
    ['label' => 'UTI', 'href' => 'bi_uti.php'],
    ['label' => 'Patologia', 'href' => 'bi_patologia.php'],
    ['label' => 'Grupo Patologia', 'href' => 'GrupoPatologia.php'],
    ['label' => 'Antecedente', 'href' => 'Antecedente.php'],
    ['label' => 'Longa Permanência', 'href' => 'LongaPermanenciaBI.php'],
    ['label' => 'Estratégia Terapêutica', 'href' => 'EstrategiaTerapeuticaBI.php'],
    ['label' => 'Médico Titular', 'href' => 'MedicoTitularBI.php'],
    ['label' => 'Auditor', 'href' => 'AuditorBI.php'],
    ['label' => 'Auditor Visitas', 'href' => 'AuditorVisitasBI.php'],
    ['label' => 'Internações com Risco', 'href' => 'InternaçõesRiscoBI.php'],
    ['label' => 'Seguradora', 'href' => 'SeguradoraBI.php'],
    ['label' => 'Seguradora Detalhado', 'href' => 'SeguradoraDetalhadoBI.php'],
    ['label' => 'Qualidade e Gestão', 'href' => 'QualidadeGestaoBI.php'],
    ['label' => 'Financeiro Realizado', 'href' => 'FinanceiroRealizadoBI.php'],
    ['label' => 'Clínico Realizado', 'href' => 'ClinicoRealizadoBI.php'],
    ['label' => 'Auditoria Produtividade', 'href' => 'AuditoriaProdutividadeBI.php'],
    ['label' => 'Consolidado Gestão', 'href' => 'ConsolidadoGestaoBI.php'],
    ['label' => 'Consolidado Gestão Cards', 'href' => 'ConsolidadoGestaoCardsBI.php'],
    ['label' => 'Alto Custo', 'href' => 'AltoCusto.php'],
    ['label' => 'Home Care', 'href' => 'HomeCare.php'],
    ['label' => 'Desospitalizacao', 'href' => 'Desospitalizacao.php'],
    ['label' => 'OPME', 'href' => 'Opme.php'],
    ['label' => 'Evento Adverso', 'href' => 'EventoAdverso.php'],
    ['label' => 'Sinistro BI', 'href' => 'Sinistro.php'],
    ['label' => 'Producao BI', 'href' => 'Producao.php'],
    ['label' => 'Indicadores BI', 'href' => 'Indicadores.php'],
    ['label' => 'Sinistro YTD', 'href' => 'bi_sinistro_ytd.php'],
    ['label' => 'Producao YTD', 'href' => 'bi_producao_ytd.php'],
    ['label' => 'Saving', 'href' => 'bi_saving.php'],
    ['label' => 'Pacientes', 'href' => 'bi_pacientes.php'],
    ['label' => 'Hospitais', 'href' => 'bi_hospitais.php'],
    ['label' => 'Sinistro', 'href' => 'bi_sinistro.php'],
    ['label' => 'Perfil Sinistro', 'href' => 'bi_perfil_sinistro.php'],
    ['label' => 'Inteligencia Artificial', 'href' => 'bi_inteligencia.php'],
    ['label' => 'Tempo Médio Permanência', 'href' => 'relatorio_tmp.php'],
    ['label' => 'Prorrogacao x Alta', 'href' => 'relatorio_prorrogacao_vs_alta.php'],
    ['label' => 'Motivos Prorrogacao', 'href' => 'relatorio_motivos_prorrogacao.php'],
    ['label' => 'Backlog Autorizacoes', 'href' => 'relatorio_backlog_autorizacoes.php'],
];
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Navegação BI</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegação">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <div class="bi-panel">
        <div class="bi-nav-title">Painel de Navegação</div>
        <div class="bi-nav-grid">
            <?php foreach ($links as $link): ?>
                <a class="bi-nav-card" href="<?= e($link['href']) ?>">
                    <?= e($link['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
