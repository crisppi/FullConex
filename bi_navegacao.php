<?php
include_once("check_logado.php");
require_once("templates/header.php");

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$links = [
    ['label' => 'UTI', 'href' => 'bi/uti'],
    ['label' => 'Patologia', 'href' => 'bi/patologia'],
    ['label' => 'Grupo Patologia', 'href' => 'bi/grupo-patologia'],
    ['label' => 'Antecedente', 'href' => 'bi/antecedente'],
    ['label' => 'Longa Permanência', 'href' => 'bi/longa-permanencia'],
    ['label' => 'Estratégia Terapêutica', 'href' => 'bi/estrategia-terapeutica'],
    ['label' => 'Médico Titular', 'href' => 'bi/medico-titular'],
    ['label' => 'Auditor', 'href' => 'bi/auditor'],
    ['label' => 'Auditor Visitas', 'href' => 'bi/auditor-visitas'],
    ['label' => 'Internações com Risco', 'href' => 'bi/internacoes-risco'],
    ['label' => 'Seguradora', 'href' => 'bi/seguradora'],
    ['label' => 'Seguradora Detalhado', 'href' => 'bi/seguradora-detalhado'],
    ['label' => 'Qualidade e Gestão', 'href' => 'bi/qualidade-gestao'],
    ['label' => 'Financeiro Realizado', 'href' => 'bi/financeiro-realizado'],
    ['label' => 'Clínico Realizado', 'href' => 'bi/clinico-realizado'],
    ['label' => 'Auditoria Produtividade', 'href' => 'bi/auditoria-produtividade'],
    ['label' => 'Consolidado Gestão', 'href' => 'bi/consolidado'],
    ['label' => 'Consolidado Gestão Cards', 'href' => 'bi/consolidado-cards'],
    ['label' => 'Alto Custo', 'href' => 'bi/alto-custo'],
    ['label' => 'Home Care', 'href' => 'bi/home-care'],
    ['label' => 'Desospitalizacao', 'href' => 'bi/desospitalizacao'],
    ['label' => 'OPME', 'href' => 'bi/opme'],
    ['label' => 'Evento Adverso', 'href' => 'bi/evento-adverso'],
    ['label' => 'Sinistro BI', 'href' => 'bi/sinistro'],
    ['label' => 'Producao BI', 'href' => 'bi/producao'],
    ['label' => 'Indicadores BI', 'href' => 'bi/indicadores'],
    ['label' => 'Sinistro YTD', 'href' => 'bi/sinistro-ytd'],
    ['label' => 'Producao YTD', 'href' => 'bi/producao-ytd'],
    ['label' => 'Saving', 'href' => 'bi/saving'],
    ['label' => 'Pacientes', 'href' => 'bi/pacientes'],
    ['label' => 'Hospitais', 'href' => 'bi/hospitais'],
    ['label' => 'Sinistro', 'href' => 'bi/sinistro-bi'],
    ['label' => 'Perfil Sinistro', 'href' => 'bi/perfil-sinistro'],
    ['label' => 'Inteligencia Artificial', 'href' => 'bi/inteligencia'],
    ['label' => 'Tempo Médio Permanência', 'href' => 'inteligencia/tmp'],
    ['label' => 'Prorrogacao x Alta', 'href' => 'inteligencia/prorrogacao-vs-alta'],
    ['label' => 'Motivos Prorrogacao', 'href' => 'inteligencia/motivos-prorrogacao'],
    ['label' => 'Backlog Autorizacoes', 'href' => 'inteligencia/backlog-autorizacoes'],
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
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi/navegacao" title="Navegação">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <div class="bi-panel">
        <div class="bi-nav-title">Painel de Navegação</div>
        <div class="bi-nav-grid">
            <?php foreach ($links as $link): ?>
                <a class="bi-nav-card" href="<?= $BASE_URL . e($link['href']) ?>">
                    <?= e($link['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
