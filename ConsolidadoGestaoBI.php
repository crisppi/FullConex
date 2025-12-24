<?php
include_once("check_logado.php");
require_once("templates/header.php");
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20251226">
<script src="diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20251221"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Consolidado Gestao</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter">
            <label>Hospital</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Internacao</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Modo internacao</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Patologia</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Grupo patologia</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Internado</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Antecedente</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Sexo</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Faixa etaria</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Ano</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Mes</label>
            <select><option>Todos</option></select>
        </div>
        <div class="bi-filter">
            <label>Internacao UTI</label>
            <select><option>Todos</option></select>
        </div>
    </form>

    <div class="bi-layout" style="margin-top:16px;">
        <section class="bi-main bi-stack">
            <div class="bi-grid fixed-2">
                <div class="bi-panel">
                    <h3>Alocacao dos Custos</h3>
                    <div class="bi-chart"><canvas id="chartAlocacao"></canvas></div>
                </div>
                <div class="bi-panel">
                    <h3>Composicao do Custo (%)</h3>
                    <div class="bi-chart"><canvas id="chartComposicao"></canvas></div>
                </div>
            </div>

            <div class="bi-grid fixed-2">
                <div class="bi-panel">
                    <h3>Analise da Glosa</h3>
                    <div class="bi-chart"><canvas id="chartGlosa"></canvas></div>
                </div>
                <div class="bi-panel">
                    <h3>Glosa</h3>
                    <div class="bi-panel-compact" style="min-height:220px;">
                        <div class="text-muted">Sem dados para exibir</div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="bi-sidebar bi-stack">
            <div class="bi-kpi kpi-berry">
                <small>Valor apresentado</small>
                <strong class="bi-kpi-big">R$ 1.088.482,05</strong>
            </div>
            <div class="bi-kpi kpi-berry">
                <small>Glosa medica</small>
                <strong>R$ 0,00</strong>
            </div>
            <div class="bi-kpi kpi-white">
                <small>Glosa medica</small>
                <strong>0,00%</strong>
            </div>
            <div class="bi-kpi kpi-berry">
                <small>Glosa enfermagem</small>
                <strong>R$ 0,00</strong>
            </div>
            <div class="bi-kpi kpi-white">
                <small>Glosa enfermagem</small>
                <strong>0,00%</strong>
            </div>
            <div class="bi-kpi kpi-berry">
                <small>Glosa total</small>
                <strong>R$ 58.079,41</strong>
            </div>
            <div class="bi-kpi kpi-white">
                <small>Glosa total</small>
                <strong>5,34%</strong>
            </div>
            <div class="bi-kpi kpi-berry">
                <small>Valor final</small>
                <strong>R$ 817.579,46</strong>
            </div>
            <div class="bi-kpi kpi-berry">
                <small>Custo medio diaria</small>
                <strong>-R$ 1,64</strong>
            </div>
        </aside>
    </div>
</div>

<script>
const alocLabels = ['Custos'];
const alocData = [294489, 154240, 90775, 50231, 48853];
const alocColors = ['#4c5bd3', '#d17aa4', '#7395b6', '#7c3a56', '#1b7f86'];

new Chart(document.getElementById('chartAlocacao'), {
  type: 'bar',
  data: {
    labels: alocLabels,
    datasets: [
      { label: 'Diarias', data: [294489], backgroundColor: alocColors[0] },
      { label: 'Honorarios', data: [154240], backgroundColor: alocColors[1] },
      { label: 'Mat/Med', data: [90775], backgroundColor: alocColors[2] },
      { label: 'SADT', data: [50231], backgroundColor: alocColors[3] },
      { label: 'Oxigenioterapia', data: [48853], backgroundColor: alocColors[4] }
    ]
  },
  options: {
    plugins: { legend: { labels: { color: '#e8f1ff' } } },
    scales: {
      x: { stacked: true, ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: {
        stacked: true,
        ticks: {
          color: '#e8f1ff',
          callback: (value) => window.biMoneyTick ? window.biMoneyTick(value) : value
        },
        grid: { color: 'rgba(255,255,255,0.1)' }
      }
    }
  }
});

new Chart(document.getElementById('chartComposicao'), {
  type: 'doughnut',
  data: {
    labels: ['Diarias', 'Honorarios', 'Mat/Med', 'SADT', 'Oxigenioterapia', 'Taxas'],
    datasets: [{
      data: [46.12, 24.15, 14.21, 7.87, 7.65, 4.0],
      backgroundColor: ['#4c5bd3', '#d17aa4', '#7395b6', '#7c3a56', '#1b7f86', '#5f6c7b']
    }]
  },
  options: {
    plugins: { legend: { position: 'left', labels: { color: '#e8f1ff' } } }
  }
});

new Chart(document.getElementById('chartGlosa'), {
  type: 'doughnut',
  data: {
    labels: ['Glosa Diarias', 'Glosa Honorarios', 'Glosa Mat/Med', 'Glosa Oxigenioterapia', 'Glosa SADT', 'Glosa Taxas'],
    datasets: [{
      data: [6.81, 44.63, 25.28, 10.61, 6.34, 6.34],
      backgroundColor: ['#4c5bd3', '#d17aa4', '#7c3a56', '#7395b6', '#1b7f86', '#5f6c7b']
    }]
  },
  options: {
    plugins: { legend: { position: 'left', labels: { color: '#e8f1ff' } } }
  }
});
</script>

<?php require_once("templates/footer.php"); ?>
