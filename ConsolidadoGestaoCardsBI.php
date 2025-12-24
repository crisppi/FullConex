<?php
include_once("check_logado.php");
require_once("templates/header.php");
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20251226">
<script src="<?= $BASE_URL ?>js/bi.js?v=20251221"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Consolidado Gestao Cards</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter"><label>Hospital</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Internacao</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Modo internacao</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Patologia</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Grupo patologia</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Internado</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Antecedente</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Sexo</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Faixa etaria</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Ano</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Mes</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Internacao UTI</label><select><option>Todos</option></select></div>
    </form>

    <div class="bi-layout" style="margin-top:16px;">
        <section class="bi-main">
            <div class="bi-grid fixed-3">
                <div class="bi-panel">
                    <h3 class="text-center">Ano Atual</h3>
                    <div class="bi-stack">
                        <div class="bi-kpi kpi-indigo"><small>Total internacoes</small><strong>44,00</strong></div>
                        <div class="bi-kpi kpi-indigo"><small>Total de diarias</small><strong>-663.451,00</strong></div>
                        <div class="bi-kpi kpi-indigo"><small>MP</small><strong>-15078,43</strong></div>
                        <div class="bi-kpi kpi-indigo"><small>Internacao UTI</small><strong>11,00</strong></div>
                        <div class="bi-kpi kpi-indigo"><small>Diarias UTI</small><strong>(Em branco)</strong></div>
                        <div class="bi-kpi kpi-indigo"><small>Media permanencia UTI</small><strong>0,00</strong></div>
                    </div>
                </div>
                <div class="bi-panel">
                    <h3 class="text-center">Ano Anterior</h3>
                    <div class="bi-stack">
                        <div class="bi-kpi kpi-rose"><small>Total internacoes</small><strong>(Em branco)</strong></div>
                        <div class="bi-kpi kpi-rose"><small>Total diarias (YTD)</small><strong>--</strong></div>
                        <div class="bi-kpi kpi-rose"><small>MP - YTD</small><strong>--</strong></div>
                        <div class="bi-kpi kpi-rose"><small>Internacao UTI</small><strong>0,00</strong></div>
                        <div class="bi-kpi kpi-rose"><small>Diarias UTI</small><strong>0,00</strong></div>
                        <div class="bi-kpi kpi-rose"><small>Media permanencia UTI</small><strong>0,00</strong></div>
                    </div>
                </div>
                <div class="bi-panel">
                    <h3 class="text-center">Variacao</h3>
                    <div class="bi-stack">
                        <div class="bi-kpi kpi-steel"><small>Total internacoes</small><strong>--</strong></div>
                        <div class="bi-kpi kpi-steel"><small>Total de diarias</small><strong>--</strong></div>
                        <div class="bi-kpi kpi-steel"><small>Media de permanencia</small><strong>--</strong></div>
                        <div class="bi-kpi kpi-steel"><small>Internacao UTI</small><strong>--</strong></div>
                        <div class="bi-kpi kpi-steel"><small>Diarias UTI</small><strong>--</strong></div>
                        <div class="bi-kpi kpi-steel"><small>Media permanencia UTI</small><strong>--</strong></div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="bi-sidebar bi-stack">
            <div class="bi-kpi kpi-berry"><small>Valor apresentado</small><strong>R$ 1.088.482,05</strong></div>
            <div class="bi-kpi kpi-berry"><small>Glosa medica</small><strong>R$ 0,00</strong></div>
            <div class="bi-kpi kpi-white"><small>Glosa medica</small><strong>0,00%</strong></div>
            <div class="bi-kpi kpi-berry"><small>Glosa enfermagem</small><strong>R$ 0,00</strong></div>
            <div class="bi-kpi kpi-white"><small>Glosa enfermagem</small><strong>0,00%</strong></div>
            <div class="bi-kpi kpi-berry"><small>Glosa total</small><strong>R$ 58.079,41</strong></div>
            <div class="bi-kpi kpi-white"><small>Glosa total</small><strong>5,34%</strong></div>
            <div class="bi-kpi kpi-berry"><small>Valor final</small><strong>R$ 817.579,46</strong></div>
            <div class="bi-kpi kpi-teal"><small>Custo medio diaria</small><strong>-R$ 1,64</strong></div>
            <div class="bi-kpi kpi-indigo"><small>Custo medio diaria UTI</small><strong>R$ 0,00</strong></div>
            <div class="bi-kpi kpi-amber"><small>Total de contas</small><strong>37,00</strong></div>
            <div class="bi-kpi kpi-indigo"><small>Custo medio por conta</small><strong>R$ 29.418,43</strong></div>
        </aside>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
