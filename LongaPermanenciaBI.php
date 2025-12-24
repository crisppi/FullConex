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
        <h1 class="bi-title">Longa Permanencia</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <div class="bi-layout">
        <aside class="bi-sidebar bi-stack">
            <div class="bi-filter-card">
                <div class="bi-filter-card-header">Filtros</div>
                <div class="bi-filter-card-body bi-stack">
                    <div class="bi-filter">
                        <label>Hospital</label>
                        <select>
                            <option>Todos</option>
                            <option>Bp Mirante - Hospital Sao Jose</option>
                            <option>Hospital Do Coracao</option>
                        </select>
                    </div>
                    <div class="bi-filter">
                        <label>Mes</label>
                        <select>
                            <option>Todos</option>
                            <option>Outubro</option>
                            <option>Novembro</option>
                        </select>
                    </div>
                    <div class="bi-filter">
                        <label>Ano</label>
                        <select>
                            <option>Todos</option>
                            <option>2024</option>
                            <option>2025</option>
                        </select>
                    </div>
                    <button class="bi-filter-btn" type="button">Limpar Filtros</button>
                </div>
            </div>
        </aside>

        <section class="bi-main bi-stack">
            <div class="bi-kpis kpi-compact">
                <div class="bi-kpi kpi-white kpi-compact">
                    <small>Internacoes</small>
                    <strong>44</strong>
                </div>
                <div class="bi-kpi kpi-white kpi-compact">
                    <small>Diarias</small>
                    <strong>663,45 Mil</strong>
                </div>
                <div class="bi-kpi kpi-white kpi-compact">
                    <small>MP</small>
                    <strong>15,1 Mil</strong>
                </div>
                <div class="bi-kpi kpi-white kpi-compact">
                    <small>Maior Permanencia</small>
                    <strong>121</strong>
                </div>
            </div>

            <div class="bi-panel">
                <h3>Hospital</h3>
                <div class="bi-chart"><canvas id="chartLongaHosp"></canvas></div>
            </div>

            <div class="bi-panel">
                <h3>Diarias</h3>
                <div class="table-responsive">
                    <table class="bi-table">
                        <thead>
                            <tr>
                                <th>Diarias</th>
                                <th>Hospital</th>
                                <th>Relatorio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>126</td>
                                <td>Bp Mirante - Hospital Sao Jose</td>
                                <td>Relatorio de longa permanencia.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
const lpLabels = [
  'Bp Mirante - Hospital Sao Jose',
  'Hospital Do Coracao',
  'Hospital Israelita Albert Einstein',
  'Humana Magna',
  'Santa Joana'
];
const lpValues = [19, 16, 7, 1, 1];
new Chart(document.getElementById('chartLongaHosp'), {
  type: 'bar',
  data: {
    labels: lpLabels,
    datasets: [{
      data: lpValues,
      backgroundColor: 'rgba(126, 150, 255, 0.8)',
      borderRadius: 10,
      maxBarThickness: 60
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      x: {
        ticks: { color: '#e8f1ff' },
        grid: { display: false }
      },
      y: {
        ticks: { color: '#e8f1ff' },
        grid: { color: 'rgba(255,255,255,0.1)' }
      }
    }
  }
});
</script>

<?php require_once("templates/footer.php"); ?>
