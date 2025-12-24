<?php
include_once("check_logado.php");
require_once("templates/header.php");
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20251226">
<script src="<?= $BASE_URL ?>js/bi.js?v=20251221"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>
<style>
.bi-header {
    position: relative;
}
.bi-header-actions.bi-header-floating {
    position: absolute;
    right: 0;
    top: 0;
}
.bi-wrapper .bi-grid-3x3-gap {
    display: none !important;
}
.bi-grid .bi-nav-icon,
.bi-grid .bi-grid-3x3-gap,
.bi-grid i,
.bi-grid svg {
    display: none !important;
}
.bi-nav-icon svg {
    width: 16px;
    height: 16px;
}
.bi-nav-icon svg circle {
    fill: currentColor;
}
</style>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Estrategia Terapeutica</h1>
        <div class="bi-header-actions bi-header-floating">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <svg viewBox="0 0 16 16" aria-hidden="true">
                    <circle cx="3" cy="3" r="1.2"></circle>
                    <circle cx="8" cy="3" r="1.2"></circle>
                    <circle cx="13" cy="3" r="1.2"></circle>
                    <circle cx="3" cy="8" r="1.2"></circle>
                    <circle cx="8" cy="8" r="1.2"></circle>
                    <circle cx="13" cy="8" r="1.2"></circle>
                    <circle cx="3" cy="13" r="1.2"></circle>
                    <circle cx="8" cy="13" r="1.2"></circle>
                    <circle cx="13" cy="13" r="1.2"></circle>
                </svg>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter"><label>Hospital</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Internacao</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Modo internacao</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Patologia</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Grupo patologia</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Internacao UTI</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Antecedente</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Sexo</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Faixa etaria</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Ano</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Mes</label><select><option>Todos</option></select></div>
        <div class="bi-actions"><button class="bi-btn" type="submit">Aplicar</button></div>
    </form>

    <div class="bi-panel" style="margin-top:16px; text-align:center;">
        <div style="font-weight:600; letter-spacing:0.04em;">
            SELECIONE OS FILTROS PARA DEFINIR QUAL A MELHOR ESTRATEGIA TERAPEUTICA PARA DETERMINADO
            CASO E ONDE PODERA OBTER MELHORES RESULTADOS ASSISTENCIAIS.
        </div>
    </div>

    <div class="bi-grid fixed-2" style="margin-top:16px;">
        <div class="bi-panel">
            <h3 class="text-center">Selecionado</h3>
            <div class="bi-kpis kpi-compact">
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Total internacoes</small><strong>44,00</strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Custo medio diaria</small><strong>-R$ 1,64</strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>MP</small><strong>-15078,43</strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Custo medio diaria UTI</small><strong>R$ 0,00</strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Internacao UTI</small><strong>11,00</strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Custo medio por conta</small><strong>R$ 29.418,43</strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Media permanencia UTI</small><strong>0,00</strong></div>
                <div class="bi-kpi kpi-indigo kpi-compact"><small>Valor apresentado</small><strong>R$ 1.088.482,05</strong></div>
            </div>
        </div>
        <div class="bi-panel">
            <h3 class="text-center">Global</h3>
            <div class="bi-kpis kpi-compact">
                <div class="bi-kpi kpi-rose kpi-compact"><small>Total internacoes</small><strong>44,00</strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Custo medio diaria</small><strong>-R$ 1,64</strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>MP</small><strong>-15078,43</strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Custo medio diaria UTI</small><strong>R$ 0,00</strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Internacao UTI</small><strong>11,00</strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Custo medio por conta</small><strong>R$ 29.418,43</strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Media permanencia UTI</small><strong>0,00</strong></div>
                <div class="bi-kpi kpi-rose kpi-compact"><small>Valor apresentado</small><strong>R$ 1.088.482,05</strong></div>
            </div>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
