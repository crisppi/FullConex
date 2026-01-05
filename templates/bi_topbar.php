<?php
if (!isset($BASE_URL)) {
    $BASE_URL = '';
}

$biSections = [
    'Resumo' => [
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
        ['label' => 'Evolução', 'href' => 'bi/evolucao', 'file' => 'EvolucaoBI.php'],
        ['label' => 'Visita Inicial', 'href' => 'bi/visita-inicial', 'file' => 'VisitaInicialBI.php'],
        ['label' => 'Clínico Realizado', 'href' => 'bi/clinico-realizado', 'file' => 'ClinicoRealizadoBI.php'],
        ['label' => 'Estratégia Terapêutica', 'href' => 'bi/estrategia-terapeutica', 'file' => 'EstrategiaTerapeuticaBI.php'],
        ['label' => 'Médico Titular', 'href' => 'bi/medico-titular', 'file' => 'MedicoTitularBI.php'],
    ],
    'Auditoria' => [
        ['label' => 'Auditor', 'href' => 'bi/auditor', 'file' => 'AuditorBI.php'],
        ['label' => 'Auditor Visitas', 'href' => 'bi/auditor-visitas', 'file' => 'AuditorVisitasBI.php'],
        ['label' => 'Auditoria Produtividade', 'href' => 'bi/auditoria-produtividade', 'file' => 'AuditoriaProdutividadeBI.php'],
    ],
    'Operacional' => [
        ['label' => 'Seguradora', 'href' => 'bi/seguradora', 'file' => 'SeguradoraBI.php'],
        ['label' => 'Seguradora Detalhado', 'href' => 'bi/seguradora-detalhado', 'file' => 'SeguradoraDetalhadoBI.php'],
        ['label' => 'Performance Rede Hospitalar', 'href' => 'bi/performance-rede-hospitalar', 'file' => 'bi_performance_rede_hospitalar.php'],
        ['label' => 'Alto Custo', 'href' => 'bi/alto-custo', 'file' => 'AltoCusto.php'],
        ['label' => 'Internações com Risco', 'href' => 'bi/internacoes-risco', 'file' => 'InternacoesRiscoBI.php'],
        ['label' => 'Qualidade e Gestão', 'href' => 'bi/qualidade-gestao', 'file' => 'QualidadeGestaoBI.php'],
        ['label' => 'Home Care', 'href' => 'bi/home-care', 'file' => 'HomeCare.php'],
        ['label' => 'Desospitalização', 'href' => 'bi/desospitalizacao', 'file' => 'Desospitalizacao.php'],
        ['label' => 'OPME', 'href' => 'bi/opme', 'file' => 'Opme.php'],
        ['label' => 'Evento Adverso', 'href' => 'bi/evento-adverso', 'file' => 'EventoAdverso.php'],
    ],
    'Rede Hospitalar' => [
        ['label' => 'Comparativa', 'href' => 'bi/rede-comparativa', 'file' => 'bi_rede_comparativa.php'],
        ['label' => 'Custo por hospital', 'href' => 'bi/rede-custo', 'file' => 'bi_rede_custo.php'],
        ['label' => 'Glosa por hospital', 'href' => 'bi/rede-glosa', 'file' => 'bi_rede_glosa.php'],
        ['label' => 'Contas paradas', 'href' => 'bi/rede-rejeicao-capeante', 'file' => 'bi_rede_rejeicao_capeante.php'],
        ['label' => 'Permanência média', 'href' => 'bi/rede-permanencia', 'file' => 'bi_rede_permanencia.php'],
        ['label' => 'Eventos adversos', 'href' => 'bi/rede-eventos-adversos', 'file' => 'bi_rede_eventos_adversos.php'],
        ['label' => 'Readmissão 30d', 'href' => 'bi/rede-readmissao', 'file' => 'bi_rede_readmissao.php'],
        ['label' => 'Ranking', 'href' => 'bi/rede-ranking', 'file' => 'bi_rede_ranking.php'],
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
    'Controle de Gastos' => [
        ['label' => 'Sinistralidade por Patologia', 'href' => 'bi/gastos-patologia', 'file' => 'ControleGastosPatologiaBI.php'],
        ['label' => 'Sinistralidade por Hospital', 'href' => 'bi/gastos-hospital', 'file' => 'ControleGastosHospitalBI.php'],
        ['label' => 'Tendência de Custo', 'href' => 'bi/gastos-tendencia', 'file' => 'ControleGastosTendenciaBI.php'],
        ['label' => 'Análise de Alto Custo', 'href' => 'bi/gastos-alto-custo', 'file' => 'ControleGastosAltoCustoBI.php'],
        ['label' => 'Custo Evitável', 'href' => 'bi/gastos-custo-evitavel', 'file' => 'ControleGastosCustoEvitavelBI.php'],
        ['label' => 'Concentração de Risco', 'href' => 'bi/gastos-concentracao', 'file' => 'ControleGastosConcentracaoBI.php'],
        ['label' => 'Provisão vs Realizado', 'href' => 'bi/gastos-provisao-realizado', 'file' => 'ControleGastosProvisaoRealizadoBI.php'],
        ['label' => 'Custo Médio Diárias', 'href' => 'bi/custo-medio-diarias', 'file' => 'CustoMedioDiariasBI.php'],
        ['label' => 'Ranking Patologia', 'href' => 'bi/ranking-patologia', 'file' => 'RankingPatologiaBI.php'],
        ['label' => 'Ranking Hospitais', 'href' => 'bi/ranking-hospitais', 'file' => 'RankingHospitaisBI.php'],
        ['label' => 'Ranking Pacientes', 'href' => 'bi/ranking-pacientes', 'file' => 'RankingPacientesBI.php'],
    ],
    'Anomalias & Fraude' => [
        ['label' => 'Outliers de Permanência', 'href' => 'bi/anomalias-permanencia', 'file' => 'AnomaliasPermanenciaBI.php'],
        ['label' => 'Negociações Suspeitas', 'href' => 'bi/anomalias-negociacao', 'file' => 'AnomaliasNegociacaoBI.php'],
        ['label' => 'OPME sem Justificativa', 'href' => 'bi/anomalias-opme', 'file' => 'AnomaliasOPMEBI.php'],
    ],
    'Conformidade & Auditoria' => [
        ['label' => 'Documentação Completa', 'href' => 'bi/auditoria-documentacao', 'file' => 'AuditoriaDocumentacaoBI.php'],
        ['label' => 'Tempo de Resposta', 'href' => 'bi/auditoria-resposta', 'file' => 'AuditoriaTempoRespostaBI.php'],
    ],
    'Segmentação de Risco' => [
        ['label' => 'Pacientes Crônicos', 'href' => 'bi/risco-cronicos', 'file' => 'RiscoCronicosBI.php'],
        ['label' => 'Risco Readmissão', 'href' => 'bi/risco-readmissao', 'file' => 'RiscoReadmissaoBI.php'],
        ['label' => 'Casos Caros Previsíveis', 'href' => 'bi/risco-casos-caros', 'file' => 'RiscoCasosCarosBI.php'],
    ],
    'Negociação & Rede' => [
        ['label' => 'Volume vs Custo', 'href' => 'bi/rede-volume-custo', 'file' => 'RedeVolumeCustoBI.php'],
        ['label' => 'Mix de Casos', 'href' => 'bi/rede-mix-casos', 'file' => 'RedeMixCasosBI.php'],
        ['label' => 'Elasticidade de Preço', 'href' => 'bi/rede-elasticidade', 'file' => 'RedeElasticidadeBI.php'],
    ],
    'Qualidade & Desfecho' => [
        ['label' => 'Eventos Adversos', 'href' => 'bi/qualidade-eventos', 'file' => 'QualidadeEventosBI.php'],
        ['label' => 'Óbitos', 'href' => 'bi/qualidade-obitos', 'file' => 'QualidadeObitosBI.php'],
    ],
];

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$currentPath = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$basePath = trim((string) parse_url($BASE_URL ?? '', PHP_URL_PATH), '/');
if ($basePath !== '' && strpos($currentPath, $basePath) === 0) {
    $currentPath = trim(substr($currentPath, strlen($basePath)), '/');
}
$currentSection = '';
$currentLabel = '';
$flatPages = [];
$matchedByHref = false;

foreach ($biSections as $section => $items) {
    foreach ($items as $item) {
        $file = $item['file'] ?? $item['href'];
        $flatPages[] = $file;
        $hrefPath = trim((string) ($item['href'] ?? ''), '/');
        if ($hrefPath !== '' && $hrefPath === $currentPath) {
            $currentSection = $section;
            $currentLabel = $item['label'];
            $matchedByHref = true;
        }
        if ($file === $currentPage) {
            $currentSection = $section;
            $currentLabel = $item['label'];
        }
    }
}

if (!in_array($currentPage, $flatPages, true) && !$matchedByHref) {
    return;
}
?>

<style>
.bi-topbar {
    position: sticky;
    top: 40px;
    z-index: 900;
    background: #b9d2e4;
    border-bottom: 1px solid #7aa4c4;
    box-shadow: 0 8px 16px rgba(24, 46, 68, 0.08);
}

.bi-topbar::before {
    content: "";
    display: block;
    height: 6px;
    background: linear-gradient(90deg, #2f6fa0, #3e7fb2, #2f6fa0);
}

.bi-topbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 18px 6px;
}

.bi-topbar-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.75rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #5e6e7c;
    font-weight: 700;
}

.bi-crumb {
    color: #2d3a45;
    font-weight: 600;
    font-size: 0.95rem;
}

.bi-crumb span {
    color: #6f7f8c;
    font-weight: 600;
    margin: 0 6px;
}

.bi-topbar-select {
    min-width: 220px;
    border-radius: 10px;
    padding: 6px 10px;
    border: 1px solid rgba(54, 84, 111, 0.25);
    font-size: 0.9rem;
    color: #2f3b45;
    background: rgba(255, 255, 255, 0.85);
}

.bi-section-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 6px 18px 4px;
    width: 100%;
    box-sizing: border-box;
    overflow-x: auto;
}

.bi-section-tab {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(120, 146, 168, 0.45);
    color: #374552;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.bi-section-tab:hover {
    border-color: #3b6b95;
    color: #2d4f6c;
}

.bi-section-tab.bi-section-tab-nav {
    background: linear-gradient(135deg, #5a79ff, #3c56d6);
    border-color: rgba(77, 104, 228, 0.9);
    color: #ffffff;
    box-shadow: 0 6px 14px rgba(73, 99, 221, 0.25);
}

.bi-section-tab.bi-section-tab-nav:hover {
    border-color: rgba(52, 82, 196, 0.95);
    color: #ffffff;
}

.bi-section-tab.bi-section-tab-nav.is-active {
    background: linear-gradient(135deg, #4b5fd6, #2c3fb6);
    border-color: rgba(51, 69, 176, 0.95);
    color: #ffffff;
    box-shadow: 0 6px 14px rgba(45, 63, 170, 0.3);
}

.bi-section-tab.is-active {
    background: linear-gradient(135deg, #ffcc6b, #ff8a3d);
    border-color: rgba(255, 170, 85, 0.9);
    color: #2c1b0a;
    box-shadow: 0 6px 14px rgba(255, 140, 64, 0.25);
}

.bi-section-tabs[data-section="resumo"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #a28bdd, #6b58b5);
    border-color: rgba(140, 115, 205, 0.9);
    color: #1b1330;
}

.bi-section-tabs[data-section="clinico"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #52c0c4, #1f8b96);
    border-color: rgba(45, 160, 170, 0.9);
    color: #0b2a2e;
}

.bi-section-tabs[data-section="operacional"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #ffb362, #d97825);
    border-color: rgba(220, 125, 45, 0.9);
    color: #2b1606;
}

.bi-section-tabs[data-section="rede"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #7eb5ff, #3c79d6);
    border-color: rgba(85, 140, 210, 0.9);
    color: #0d1f35;
}

.bi-section-tabs[data-section="financeiro"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #ffd36e, #d7a13d);
    border-color: rgba(210, 160, 70, 0.9);
    color: #2c1b05;
}

.bi-section-tabs[data-section="gastos"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #ff8fa3, #d94b6a);
    border-color: rgba(215, 85, 115, 0.9);
    color: #2a0c16;
}

.bi-section-tabs[data-section="anomalias"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #ff7b7b, #c73838);
    border-color: rgba(200, 70, 70, 0.9);
    color: #2a0c0c;
}

.bi-section-tabs[data-section="conformidade"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #61d2c6, #2aa08f);
    border-color: rgba(60, 170, 150, 0.9);
    color: #0b2a26;
}

.bi-section-tabs[data-section="auditoria"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #63d5c0, #2fa38c);
    border-color: rgba(60, 160, 140, 0.9);
    color: #0f2a25;
}

.bi-section-tabs[data-section="risco"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #ffd36e, #d69a3a);
    border-color: rgba(215, 160, 70, 0.9);
    color: #2c1b05;
}

.bi-section-tabs[data-section="negociacao"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #72d2ff, #3b8fca);
    border-color: rgba(85, 150, 210, 0.9);
    color: #0c2133;
}

.bi-section-tabs[data-section="qualidade"] .bi-section-tab.is-active {
    background: linear-gradient(135deg, #b897ff, #6f4bd6);
    border-color: rgba(130, 100, 210, 0.9);
    color: #1b1330;
}

.bi-chipbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 6px 18px 14px;
    border-radius: 14px;
    position: relative;
    overflow: hidden;
    width: 100%;
    box-sizing: border-box;
}

.bi-chipbar::after {
    content: "";
    display: none;
}

.bi-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid #cfe0ee;
    color: #2e4b63;
    font-size: 0.85rem;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.85);
    white-space: nowrap;
    transition: all .15s ease;
}

.bi-chip:hover {
    border-color: #3b6b95;
    color: #2d4f6c;
}

.bi-chip.is-active {
    background: linear-gradient(135deg, #ffcc6b, #ff8a3d);
    color: #2c1b0a;
    border-color: rgba(255, 170, 85, 0.9);
    box-shadow: 0 6px 14px rgba(255, 140, 64, 0.25);
    transform: translateY(-1px);
}

.bi-chipbar[data-section="resumo"] {
    background: linear-gradient(135deg, rgba(120, 95, 170, 0.25), rgba(90, 70, 140, 0.25));
    border: 1px solid rgba(120, 95, 170, 0.35);
}

.bi-chipbar[data-section="clinico"] {
    background: linear-gradient(135deg, rgba(30, 135, 140, 0.24), rgba(20, 110, 120, 0.24));
    border: 1px solid rgba(30, 135, 140, 0.35);
}

.bi-chipbar[data-section="operacional"] {
    background: linear-gradient(135deg, rgba(210, 130, 40, 0.22), rgba(185, 105, 30, 0.22));
    border: 1px solid rgba(210, 130, 40, 0.35);
}

.bi-chipbar[data-section="rede"] {
    background: linear-gradient(135deg, rgba(60, 120, 190, 0.22), rgba(45, 95, 160, 0.22));
    border: 1px solid rgba(60, 120, 190, 0.35);
}

.bi-chipbar[data-section="financeiro"] {
    background: linear-gradient(135deg, rgba(210, 175, 70, 0.25), rgba(175, 140, 45, 0.25));
    border: 1px solid rgba(210, 175, 70, 0.35);
}

.bi-chipbar[data-section="gastos"] {
    background: linear-gradient(135deg, rgba(214, 90, 120, 0.25), rgba(178, 60, 90, 0.25));
    border: 1px solid rgba(214, 90, 120, 0.35);
}

.bi-chipbar[data-section="anomalias"] {
    background: linear-gradient(135deg, rgba(210, 90, 90, 0.25), rgba(170, 60, 60, 0.25));
    border: 1px solid rgba(210, 90, 90, 0.35);
}

.bi-chipbar[data-section="conformidade"] {
    background: linear-gradient(135deg, rgba(90, 200, 185, 0.25), rgba(60, 150, 135, 0.25));
    border: 1px solid rgba(90, 200, 185, 0.35);
}

.bi-chipbar[data-section="auditoria"] {
    background: linear-gradient(135deg, rgba(95, 205, 190, 0.25), rgba(60, 160, 145, 0.25));
    border: 1px solid rgba(95, 205, 190, 0.35);
}

.bi-chipbar[data-section="risco"] {
    background: linear-gradient(135deg, rgba(230, 190, 90, 0.25), rgba(180, 140, 45, 0.25));
    border: 1px solid rgba(230, 190, 90, 0.35);
}

.bi-chipbar[data-section="negociacao"] {
    background: linear-gradient(135deg, rgba(95, 170, 220, 0.25), rgba(60, 120, 180, 0.25));
    border: 1px solid rgba(95, 170, 220, 0.35);
}

.bi-chipbar[data-section="qualidade"] {
    background: linear-gradient(135deg, rgba(150, 120, 220, 0.25), rgba(110, 80, 190, 0.25));
    border: 1px solid rgba(150, 120, 220, 0.35);
}

.bi-chipbar[data-section="resumo"] .bi-chip,
.bi-chipbar[data-section="clinico"] .bi-chip,
.bi-chipbar[data-section="operacional"] .bi-chip,
.bi-chipbar[data-section="rede"] .bi-chip,
.bi-chipbar[data-section="financeiro"] .bi-chip,
.bi-chipbar[data-section="gastos"] .bi-chip,
.bi-chipbar[data-section="anomalias"] .bi-chip,
.bi-chipbar[data-section="conformidade"] .bi-chip,
.bi-chipbar[data-section="auditoria"] .bi-chip,
.bi-chipbar[data-section="risco"] .bi-chip,
.bi-chipbar[data-section="negociacao"] .bi-chip,
.bi-chipbar[data-section="qualidade"] .bi-chip {
    background: rgba(255, 255, 255, 0.9);
}

.bi-chipbar[data-section="resumo"] .bi-chip.is-active {
    background: linear-gradient(135deg, #a28bdd, #6b58b5);
    border-color: rgba(140, 115, 205, 0.9);
    color: #1b1330;
}

.bi-chipbar[data-section="clinico"] .bi-chip.is-active {
    background: linear-gradient(135deg, #52c0c4, #1f8b96);
    border-color: rgba(45, 160, 170, 0.9);
    color: #0b2a2e;
}

.bi-chipbar[data-section="operacional"] .bi-chip.is-active {
    background: linear-gradient(135deg, #ffb362, #d97825);
    border-color: rgba(220, 125, 45, 0.9);
    color: #2b1606;
}

.bi-chipbar[data-section="rede"] .bi-chip.is-active {
    background: linear-gradient(135deg, #7eb5ff, #3c79d6);
    border-color: rgba(85, 140, 210, 0.9);
    color: #0d1f35;
}

.bi-chipbar[data-section="financeiro"] .bi-chip.is-active {
    background: linear-gradient(135deg, #ffd36e, #d7a13d);
    border-color: rgba(210, 160, 70, 0.9);
    color: #2c1b05;
}

.bi-chipbar[data-section="gastos"] .bi-chip.is-active {
    background: linear-gradient(135deg, #ff8fa3, #d94b6a);
    border-color: rgba(215, 85, 115, 0.9);
    color: #2a0c16;
}

.bi-chipbar[data-section="anomalias"] .bi-chip.is-active {
    background: linear-gradient(135deg, #ff7b7b, #c73838);
    border-color: rgba(200, 70, 70, 0.9);
    color: #2a0c0c;
}

.bi-chipbar[data-section="conformidade"] .bi-chip.is-active {
    background: linear-gradient(135deg, #61d2c6, #2aa08f);
    border-color: rgba(60, 170, 150, 0.9);
    color: #0b2a26;
}

.bi-chipbar[data-section="auditoria"] .bi-chip.is-active {
    background: linear-gradient(135deg, #63d5c0, #2fa38c);
    border-color: rgba(60, 160, 140, 0.9);
    color: #0f2a25;
}

.bi-chipbar[data-section="risco"] .bi-chip.is-active {
    background: linear-gradient(135deg, #ffd36e, #d69a3a);
    border-color: rgba(215, 160, 70, 0.9);
    color: #2c1b05;
}

.bi-chipbar[data-section="negociacao"] .bi-chip.is-active {
    background: linear-gradient(135deg, #72d2ff, #3b8fca);
    border-color: rgba(85, 150, 210, 0.9);
    color: #0c2133;
}

.bi-chipbar[data-section="qualidade"] .bi-chip.is-active {
    background: linear-gradient(135deg, #b897ff, #6f4bd6);
    border-color: rgba(130, 100, 210, 0.9);
    color: #1b1330;
}

.bi-topbar-spacer {
    height: 22px;
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
    .bi-section-tabs,
    .bi-chipbar {
        padding: 6px 12px 10px;
    }

    .bi-topbar-select {
        width: 100%;
        min-width: 0;
    }
}
</style>

<?php
$sectionDisplay = [
    'Rede Hospitalar' => 'Comparativa Rede',
    'Controle de Gastos' => 'Controle de Gastos',
    'Anomalias & Fraude' => 'Anomalias & Fraude',
    'Auditoria' => 'Auditoria',
    'Conformidade & Auditoria' => 'Conformidade & Auditoria',
    'Segmentação de Risco' => 'Segmentação de Risco',
    'Negociação & Rede' => 'Negociação & Rede',
    'Qualidade & Desfecho' => 'Qualidade & Desfecho',
];
$sectionSlugMap = [
    'Resumo' => 'resumo',
    'Clínico' => 'clinico',
    'Operacional' => 'operacional',
    'Rede Hospitalar' => 'rede',
    'Financeiro' => 'financeiro',
    'Auditoria' => 'auditoria',
    'Controle de Gastos' => 'gastos',
    'Anomalias & Fraude' => 'anomalias',
    'Conformidade & Auditoria' => 'conformidade',
    'Segmentação de Risco' => 'risco',
    'Negociação & Rede' => 'negociacao',
    'Qualidade & Desfecho' => 'qualidade',
];
$activeSection = $currentSection ?: array_key_first($biSections);
$activeSectionSlug = $sectionSlugMap[$activeSection] ?? 'resumo';
$activeItems = $biSections[$activeSection] ?? [];
?>

<div class="bi-topbar">
    <div class="bi-topbar-inner">
        <div class="d-flex flex-column gap-1">
            <div class="bi-topbar-title">Navegação BI</div>
            <div class="bi-crumb">
                <?= htmlspecialchars($sectionDisplay[$activeSection] ?? $activeSection ?: 'Resumo', ENT_QUOTES, 'UTF-8') ?>
                <span>/</span>
                <?= htmlspecialchars($currentLabel ?: 'Painel', ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
        <select class="bi-topbar-select" onchange="if (this.value) window.location.href=this.value;">
            <option value="">Ir para relatório...</option>
            <?php foreach ($biSections as $section => $items): ?>
            <?php $sectionName = $sectionDisplay[$section] ?? $section; ?>
            <optgroup label="<?= htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach ($items as $item): ?>
                <?php $itemFile = $item['file'] ?? $item['href']; ?>
                <?php $hrefPath = trim((string) ($item['href'] ?? ''), '/'); ?>
                <option value="<?= $BASE_URL . $item['href'] ?>" <?= ($itemFile === $currentPage || $hrefPath === $currentPath) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </optgroup>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="bi-section-tabs" data-section="<?= htmlspecialchars($activeSectionSlug, ENT_QUOTES, 'UTF-8') ?>">
        <?php
        $navUrl = $BASE_URL . 'bi/navegacao';
        $navActive = $currentPage === 'bi_navegacao.php' || trim((string) $currentPath, '/') === 'bi/navegacao';
        ?>
        <a class="bi-section-tab bi-section-tab-nav <?= $navActive ? 'is-active' : '' ?>"
            href="<?= htmlspecialchars($navUrl, ENT_QUOTES, 'UTF-8') ?>">
            Navegação
        </a>
        <?php foreach ($biSections as $section => $items): ?>
        <?php
        $sectionName = $sectionDisplay[$section] ?? $section;
        $sectionUrl = $BASE_URL . ($items[0]['href'] ?? '');
        $isActiveSection = $section === $activeSection;
        ?>
        <a class="bi-section-tab <?= $isActiveSection ? 'is-active' : '' ?>"
            href="<?= htmlspecialchars($sectionUrl, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="bi-chipbar" data-section="<?= htmlspecialchars($activeSectionSlug, ENT_QUOTES, 'UTF-8') ?>">
        <?php foreach ($activeItems as $item): ?>
        <?php $itemFile = $item['file'] ?? $item['href']; ?>
        <?php $hrefPath = trim((string) ($item['href'] ?? ''), '/'); ?>
        <a class="bi-chip <?= ($itemFile === $currentPage || $hrefPath === $currentPath) ? 'is-active' : '' ?>"
            href="<?= $BASE_URL . $item['href'] ?>"
            title="<?= htmlspecialchars($activeSection . ' • ' . $item['label'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<div class="bi-topbar-spacer"></div>
