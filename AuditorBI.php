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
        <h1 class="bi-title">Auditor</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <div class="bi-layout">
        <aside class="bi-sidebar bi-stack">
            <div class="bi-panel">
                <h3>Cargo</h3>
                <div class="bi-filter-list">
                    <div class="bi-filter-pill active">Administrativo</div>
                    <div class="bi-filter-pill">Auditor Enfermeira(o)</div>
                    <div class="bi-filter-pill">Auditor Medico(a)</div>
                </div>
            </div>

            <div class="bi-panel">
                <h3>Auditor</h3>
                <div class="bi-filter-list">
                    <div class="bi-filter-pill">Diretor</div>
                    <div class="bi-filter-pill">Enfermeiro 1</div>
                    <div class="bi-filter-pill active">Izabella</div>
                    <div class="bi-filter-pill">Jorge Lemos</div>
                    <div class="bi-filter-pill">Joubene</div>
                    <div class="bi-filter-pill">Kris</div>
                    <div class="bi-filter-pill">Marcio Adm</div>
                    <div class="bi-filter-pill">Regina Silva</div>
                </div>
            </div>

            <div class="bi-filter-card">
                <div class="bi-filter-card-header">Filtros</div>
                <div class="bi-filter-card-body bi-stack">
                    <div class="bi-filter">
                        <label>Hospitais</label>
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
                    <button class="bi-filter-btn" type="button">Aplicar</button>
                </div>
            </div>
        </aside>

        <section class="bi-main bi-stack">
            <div class="bi-kpis kpi-compact">
                <div class="bi-kpi kpi-white kpi-compact">
                    <small>Internacoes</small>
                    <strong>33</strong>
                </div>
                <div class="bi-kpi kpi-white kpi-compact">
                    <small>Diarias</small>
                    <strong>663,87 Mil</strong>
                </div>
                <div class="bi-kpi kpi-white kpi-compact">
                    <small>MP</small>
                    <strong>20,1 Mil</strong>
                </div>
                <div class="bi-kpi kpi-white kpi-compact">
                    <small>Maior Permanencia</small>
                    <strong>121</strong>
                </div>
            </div>

            <div class="bi-grid fixed-2">
                <div class="bi-panel">
                    <h3>Contas por Auditor</h3>
                    <div class="bi-chart"><canvas id="chartAuditorContas"></canvas></div>
                </div>
                <div class="bi-panel">
                    <h3>Glosa por Auditor</h3>
                    <div class="bi-chart"><canvas id="chartAuditorGlosa"></canvas></div>
                </div>
            </div>

            <div class="bi-grid fixed-2">
                <div class="bi-panel">
                    <h3>Contas Auditadas</h3>
                    <div class="bi-chart"><canvas id="chartAuditorAuditadas"></canvas></div>
                </div>
                <div class="bi-panel">
                    <h3>Visitas</h3>
                    <div class="bi-chart"><canvas id="chartAuditorVisitas"></canvas></div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
const auditorLabels = ['Regina Silva', 'Jorge Lemos', 'Izabella', 'Enfermeiro 1', 'Joubene', 'Diretor'];
const contasValues = [157666.23, 127551.10, 105246.56, 17911.65, 10721.57, 400.00];
const glosaValues = [4713.51, 2842.92, 333.51, 105.54];
const glosaLabels = ['Regina Silva', 'Jorge Lemos', 'Enfermeiro 1', 'Izabella'];
const auditadasValues = [13, 5, 3, 3, 1, 1];
const visitasValues = [15, 10, 8, 3, 1, 1];

function barOptions() {
  return {
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: { ticks: { color: '#e8f1ff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
    }
  };
}

new Chart(document.getElementById('chartAuditorContas'), {
  type: 'bar',
  data: { labels: auditorLabels, datasets: [{ data: contasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: {
    ...barOptions(),
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: {
        ticks: {
          color: '#e8f1ff',
          callback: (value) => window.biMoneyTick ? window.biMoneyTick(value) : value
        },
        grid: { color: 'rgba(255,255,255,0.1)' }
      }
    }
  }
});

new Chart(document.getElementById('chartAuditorGlosa'), {
  type: 'bar',
  data: { labels: glosaLabels, datasets: [{ data: glosaValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: {
    ...barOptions(),
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: {
        ticks: {
          color: '#e8f1ff',
          callback: (value) => window.biMoneyTick ? window.biMoneyTick(value) : value
        },
        grid: { color: 'rgba(255,255,255,0.1)' }
      }
    }
  }
});

new Chart(document.getElementById('chartAuditorAuditadas'), {
  type: 'bar',
  data: { labels: auditorLabels, datasets: [{ data: auditadasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptions()
});

new Chart(document.getElementById('chartAuditorVisitas'), {
  type: 'bar',
  data: { labels: auditorLabels, datasets: [{ data: visitasValues, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptions()
});
</script>

<?php require_once("templates/footer.php"); ?>
