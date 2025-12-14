(function() {
  const tips = {
    prorrogacao: {
      title: 'Prorrogação / Seguir prorrogação',
      body: 'Use "Sim" somente quando o hospital já possui autorização para ampliar o período e você precisa manter a conta aberta até a nova resposta.',
      bullets: [
        'Se a seguradora ainda não respondeu, mantenha "Não" e registre o pedido em Prorrogações.',
        'Selecionar "Sim" força o capeante/parcial a continuar no mesmo ciclo e evita o encerramento automático.'
      ],
      link: { href: 'manual_internacao.html', text: 'Ver manual de Internação' }
    },
    negociacao_tipo: {
      title: 'Tipo de negociação',
      body: 'Classifique o acordo antes de salvar para que dashboards e PDFs mostrem o cenário correto.',
      bullets: [
        'Ex.: Troca UTI/SEMI, Troca UTI/APTO ou pacote fechado combinado com o hospital.',
        'A seleção impacta os gráficos de savings e o resumo exibido no RAH.'
      ],
      link: { href: 'manual_negociacoes.html', text: 'Manual de Negociações' }
    }
  };

  const processedAnchors = new WeakSet();
  const activePopovers = new Set();

  function buildContent(tip) {
    const parts = [`<p class="assist-popover-text">${tip.body}</p>`];
    if (Array.isArray(tip.bullets) && tip.bullets.length) {
      const list = tip.bullets.map(item => `<li>${item}</li>`).join('');
      parts.push(`<ul class="assist-popover-list">${list}</ul>`);
    }
    if (tip.link) {
      parts.push(
        `<a href="${tip.link.href}" target="_blank" rel="noopener" class="assist-popover-link">${tip.link.text}</a>`
      );
    }
    return parts.join('');
  }

  function closeOthers(current) {
    activePopovers.forEach(instance => {
      if (instance !== current) {
        instance.hide();
      }
    });
  }

  function enhanceAnchor(anchor) {
    if (!anchor || processedAnchors.has(anchor)) return;
    const key = anchor.getAttribute('data-assist-key');
    const tip = tips[key];
    if (!tip) return;

    const target = anchor.closest('.assist-anchor') || anchor;
    processedAnchors.add(anchor);
    if (target !== anchor) {
      processedAnchors.add(target);
    }
    target.classList.add('assist-container');

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'assist-trigger';
    trigger.setAttribute('aria-label', 'Ajuda contextual');
    trigger.innerText = '?';
    target.appendChild(trigger);

    const placement = anchor.getAttribute('data-assist-placement') || 'right';

    if (window.bootstrap && bootstrap.Popover) {
      const instance = new bootstrap.Popover(trigger, {
        container: 'body',
        html: true,
        trigger: 'focus',
        sanitize: false,
        placement,
        title: tip.title,
        content: buildContent(tip),
        template:
          '<div class="popover assist-popover" role="tooltip">' +
          '<div class="popover-arrow"></div>' +
          '<div class="popover-header"></div>' +
          '<div class="popover-body"></div>' +
          '</div>'
      });

      trigger.addEventListener('show.bs.popover', () => {
        closeOthers(instance);
        activePopovers.add(instance);
      });

      trigger.addEventListener('hidden.bs.popover', () => {
        activePopovers.delete(instance);
      });
    } else {
      // Fallback nativo: título padrão do navegador
      trigger.setAttribute('title', `${tip.title} - ${tip.body}`);
    }
  }

  function scanAnchors(root = document) {
    const anchors = root.querySelectorAll('[data-assist-key]');
    anchors.forEach(enhanceAnchor);
  }

  function initTips() {
    scanAnchors();
    if (window.MutationObserver) {
      const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          mutation.addedNodes.forEach(node => {
            if (!(node instanceof HTMLElement)) return;
            if (node.matches('[data-assist-key]')) {
              enhanceAnchor(node);
            } else {
              scanAnchors(node);
            }
          });
        });
      });
      observer.observe(document.body, { childList: true, subtree: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTips);
  } else {
    initTips();
  }
})();
