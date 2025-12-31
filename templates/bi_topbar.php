<?php
if (!isset($BASE_URL)) {
    $BASE_URL = '';
}

$biSections = [
    'Resumo' => [
        ['label' => 'Navegação', 'href' => 'bi/navegacao', 'file' => 'bi_navegacao.php'],
        ['label' => 'Consolidado', 'href' => 'bi/consolidado', 'file' => 'ConsolidadoGestaoBI.php'],
        ['label' => 'Consolidado Cards', 'href' => 'bi/consolidado-cards', 'file' => 'ConsolidadoGestaoCardsBI.php'],
        ['label' => 'Indicadores BI', 'href' => 'bi/indicadores', 'file' => 'Indicadores.php'],
    ],
    'Clínico' => [
        ['label' => 'UTI', 'href' => 'bi/uti', 'file' => 'bi_uti.php'],
        ['label' => 'Patologia', 'href' => 'bi/patologia', 'file' => 'bi_patologia.php'],
        ['label' => 'Grupo Patologia', 'href' => 'bi/grupo-patologia', 'file' => 'GrupoPatologia.php'],
        ['label' => 'Antecedente', 'href' => 'bi/antecedente', 'file' => 'Antecedente.php'],
        ['label' => 'Longa Permanência', 'href' => 'bi/longa-permanencia', 'file' => 'LongaPermanenciaBI.php'],
        ['label' => 'Clínico Realizado', 'href' => 'bi/clinico-realizado', 'file' => 'ClinicoRealizadoBI.php'],
        ['label' => 'Estratégia Terapêutica', 'href' => 'bi/estrategia-terapeutica', 'file' => 'EstrategiaTerapeuticaBI.php'],
        ['label' => 'Médico Titular', 'href' => 'bi/medico-titular', 'file' => 'MedicoTitularBI.php'],
        ['label' => 'Auditor', 'href' => 'bi/auditor', 'file' => 'AuditorBI.php'],
        ['label' => 'Auditor Visitas', 'href' => 'bi/auditor-visitas', 'file' => 'AuditorVisitasBI.php'],
        ['label' => 'Auditoria Produtividade', 'href' => 'bi/auditoria-produtividade', 'file' => 'AuditoriaProdutividadeBI.php'],
    ],
    'Operacional' => [
        ['label' => 'Seguradora', 'href' => 'bi/seguradora', 'file' => 'SeguradoraBI.php'],
        ['label' => 'Seguradora Detalhado', 'href' => 'bi/seguradora-detalhado', 'file' => 'SeguradoraDetalhadoBI.php'],
        ['label' => 'Alto Custo', 'href' => 'bi/alto-custo', 'file' => 'AltoCusto.php'],
        ['label' => 'Internações com Risco', 'href' => 'bi/internacoes-risco', 'file' => 'InternacoesRiscoBI.php'],
        ['label' => 'Qualidade e Gestão', 'href' => 'bi/qualidade-gestao', 'file' => 'QualidadeGestaoBI.php'],
        ['label' => 'Home Care', 'href' => 'bi/home-care', 'file' => 'HomeCare.php'],
        ['label' => 'Desospitalização', 'href' => 'bi/desospitalizacao', 'file' => 'Desospitalizacao.php'],
        ['label' => 'OPME', 'href' => 'bi/opme', 'file' => 'Opme.php'],
        ['label' => 'Evento Adverso', 'href' => 'bi/evento-adverso', 'file' => 'EventoAdverso.php'],
    ],
    'Financeiro' => [
        ['label' => 'Sinistro', 'href' => 'bi/sinistro', 'file' => 'Sinistro.php'],
        ['label' => 'Perfil Sinistro', 'href' => 'bi/perfil-sinistro', 'file' => 'bi_perfil_sinistro.php'],
        ['label' => 'Sinistro YTD', 'href' => 'bi/sinistro-ytd', 'file' => 'bi_sinistro_ytd.php'],
        ['label' => 'Financeiro Realizado', 'href' => 'bi/financeiro-realizado', 'file' => 'FinanceiroRealizadoBI.php'],
        ['label' => 'Produção', 'href' => 'bi/producao', 'file' => 'Producao.php'],
        ['label' => 'Produção YTD', 'href' => 'bi/producao-ytd', 'file' => 'bi_producao_ytd.php'],
        ['label' => 'Saving', 'href' => 'bi/saving', 'file' => 'bi_saving.php'],
        ['label' => 'Pacientes', 'href' => 'bi/pacientes', 'file' => 'bi_pacientes.php'],
        ['label' => 'Hospitais', 'href' => 'bi/hospitais', 'file' => 'bi_hospitais.php'],
        ['label' => 'Inteligência Artificial', 'href' => 'bi/inteligencia', 'file' => 'bi_inteligencia.php'],
        ['label' => 'Sinistro BI', 'href' => 'bi/sinistro-bi', 'file' => 'bi_sinistro.php'],
    ],
];

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$currentSection = '';
$currentLabel = '';
$flatPages = [];

foreach ($biSections as $section => $items) {
    foreach ($items as $item) {
        $file = $item['file'] ?? $item['href'];
        $flatPages[] = $file;
        if ($file === $currentPage) {
            $currentSection = $section;
            $currentLabel = $item['label'];
        }
    }
}

if (!in_array($currentPage, $flatPages, true)) {
    return;
}
?>

<style>
.bi-topbar {
    position: sticky;
    top: 40px;
    z-index: 900;
    background: rgba(255, 255, 255, 0.62);
    border-bottom: 1px solid rgba(230, 224, 238, 0.45);
    box-shadow: 0 4px 12px rgba(40, 16, 72, 0.04);
    backdrop-filter: blur(8px);
}

.bi-topbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 18px;
}

.bi-topbar-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.78rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #6b5f79;
    font-weight: 700;
}

.bi-crumb {
    color: #3b2a4a;
    font-weight: 600;
    font-size: 0.95rem;
}

.bi-crumb span {
    color: #8a7a98;
    font-weight: 600;
    margin: 0 6px;
}

.bi-topbar-select {
    min-width: 220px;
    border-radius: 10px;
    padding: 6px 10px;
    border: 1px solid #d9d2e3;
    font-size: 0.9rem;
    color: #3b2a4a;
    background: #f7f5fa;
}

.bi-chipbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px 18px 14px;
}

.bi-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid #e3dcea;
    color: #4a3658;
    font-size: 0.85rem;
    text-decoration: none;
    background: #f6f3fb;
    white-space: nowrap;
    transition: all .15s ease;
}

.bi-chip:hover {
    border-color: #5e2363;
    color: #5e2363;
}

.bi-chip.is-active {
    background: #e8def2;
    color: #3b2a4a;
    border-color: #bca9d6;
}

.bi-topbar-spacer {
    height: 28px;
}

@media (max-width: 900px) {
    .bi-topbar-inner {
        flex-direction: column;
        align-items: flex-start;
    }

    .bi-topbar {
        top: 36px;
    }
}

@media (max-width: 600px) {
    .bi-chipbar {
        padding: 8px 12px 12px;
    }

    .bi-topbar-select {
        width: 100%;
        min-width: 0;
    }
}
</style>

<div class="bi-topbar">
    <div class="bi-topbar-inner">
        <div class="d-flex flex-column gap-1">
            <div class="bi-topbar-title">Navegação BI</div>
            <div class="bi-crumb">
                <?= htmlspecialchars($currentSection ?: 'Resumo', ENT_QUOTES, 'UTF-8') ?>
                <span>/</span>
                <?= htmlspecialchars($currentLabel ?: 'Painel', ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
        <select class="bi-topbar-select" onchange="if (this.value) window.location.href=this.value;">
            <option value="">Ir para relatório...</option>
            <?php foreach ($biSections as $section => $items): ?>
            <optgroup label="<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach ($items as $item): ?>
                <?php $itemFile = $item['file'] ?? $item['href']; ?>
                <option value="<?= $BASE_URL . $item['href'] ?>" <?= $itemFile === $currentPage ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </optgroup>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="bi-chipbar">
        <?php foreach ($biSections as $section => $items): ?>
        <?php foreach ($items as $item): ?>
        <?php $itemFile = $item['file'] ?? $item['href']; ?>
        <a class="bi-chip <?= $itemFile === $currentPage ? 'is-active' : '' ?>"
            href="<?= $BASE_URL . $item['href'] ?>"
            title="<?= htmlspecialchars($section . ' • ' . $item['label'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
<div class="bi-topbar-spacer"></div>
