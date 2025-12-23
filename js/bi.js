if (typeof Chart !== 'undefined') {
  if (Chart.defaults.global) {
    Chart.defaults.global.defaultFontColor = '#eaf6ff';
    Chart.defaults.global.legend.labels.fontColor = '#eaf6ff';
  }
  if (Chart.defaults.scale && Chart.defaults.scale.ticks) {
    Chart.defaults.scale.ticks.fontColor = '#eaf6ff';
  }
  if (Chart.defaults.scale && Chart.defaults.scale.gridLines) {
    Chart.defaults.scale.gridLines.color = 'rgba(255,255,255,0.12)';
  }
}

window.biChartScales = function () {
  return {
    xAxes: [{
      ticks: { fontColor: '#eaf6ff' },
      gridLines: { color: 'rgba(255,255,255,0.12)' }
    }],
    yAxes: [{
      ticks: { fontColor: '#eaf6ff' },
      gridLines: { color: 'rgba(255,255,255,0.12)' }
    }]
  };
};

window.biLegendWhite = { labels: { fontColor: '#eaf6ff' } };

window.biMoneyTick = function (value) {
  const num = Number(value || 0);
  return 'R$ ' + num.toLocaleString('pt-BR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  });
};

document.addEventListener('DOMContentLoaded', () => {
  if (document.body) {
    document.body.classList.add('bi-theme');
    const nav = document.querySelector('.navbar.fixed-top');
    const navHeight = nav ? nav.offsetHeight : 100;
    document.body.style.paddingTop = (navHeight + 16) + 'px';
  }

  document.querySelectorAll('.bi-panel.bi-filters').forEach((panel) => {
    panel.style.display = 'flex';
    panel.style.flexWrap = 'nowrap';
    panel.style.gap = '12px';
    panel.style.alignItems = 'flex-end';
    panel.style.overflowX = 'auto';
    panel.style.paddingBottom = '6px';

    panel.querySelectorAll('.bi-filter').forEach((filter) => {
      filter.style.minWidth = '170px';
      filter.style.flex = '0 0 170px';
    });

    panel.querySelectorAll('input[type="date"]').forEach((input) => {
      input.style.setProperty('background-color', 'var(--bi-panel-strong)', 'important');
      input.style.setProperty('color', '#eaf6ff', 'important');
      input.style.setProperty('border-color', 'rgba(255,255,255,0.35)', 'important');
      input.style.setProperty('-webkit-text-fill-color', '#eaf6ff', 'important');
    });
  });
});
