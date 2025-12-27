<?php
include_once("check_logado.php");
require_once("templates/header.php");
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20260110">
<script src="diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="<?= $BASE_URL ?>js/bi.js?v=20260110"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Médico Titular</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegação">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter"><label>Internado</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Hospitais</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Tipo Internação</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Modo Admissao</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>UTI</label><select><option>Todos</option></select></div>
        <div class="bi-filter"><label>Data Internação</label><input type="date"></div>
        <div class="bi-filter"><label>Data Final</label><input type="date"></div>
        <div class="bi-actions"><button class="bi-btn" type="submit">Aplicar</button></div>
    </form>

    <div class="bi-grid fixed-2" style="margin-top:16px;">
        <div class="bi-panel">
            <h3>Valor por Médico</h3>
            <div class="bi-chart"><canvas id="chartValorMedico"></canvas></div>
        </div>
        <div class="bi-panel">
            <h3>MP por Médico</h3>
            <div class="bi-chart"><canvas id="chartMpMedico"></canvas></div>
        </div>
    </div>

    <div class="bi-panel" style="margin-top:16px;">
        <h3>Médico</h3>
        <div class="table-responsive">
            <table class="bi-table">
                <thead>
                    <tr>
                        <th>CRM</th>
                        <th>Médico</th>
                        <th>Paciente</th>
                        <th>MP</th>
                        <th>Valor Apresentado</th>
                        <th>Tipo admissao</th>
                        <th>Modo internação</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>12345</td>
                        <td>Dr. Paulo Ribeiro</td>
                        <td>Lucas Miranda</td>
                        <td>11</td>
                        <td>R$ 118.400</td>
                        <td>Urgencia</td>
                        <td>Clinica</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const medicoLabels = ['Dr. Paulo Ribeiro', 'Dra. Marina Costa', 'Dr. Jorge Silva', 'Dra. Julia Lemos'];
const medicoValores = [764958, 301126, 11677, 10722];
const medicoMp = [29, 8, 6, 5];

function barOptionsMoney() {
  return {
    plugins: { legend: { display: false } },
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
  };
}

new Chart(document.getElementById('chartValorMedico'), {
  type: 'bar',
  data: { labels: medicoLabels, datasets: [{ data: medicoValores, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: barOptionsMoney()
});

new Chart(document.getElementById('chartMpMedico'), {
  type: 'bar',
  data: { labels: medicoLabels, datasets: [{ data: medicoMp, backgroundColor: 'rgba(126,150,255,0.82)', borderRadius: 10 }] },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: '#e8f1ff' }, grid: { display: false } },
      y: { ticks: { color: '#e8f1ff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
    }
  }
});
</script>

<?php require_once("templates/footer.php"); ?>
