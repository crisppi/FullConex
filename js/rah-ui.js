(function (w) {
  const $ = w.jQuery;
  const R = w.RAH;

  // Máscara dinheiro
  function applyMask(ctx) {
    if (!$ || !$.fn.maskMoney || $.fn.maskMoney.__stub__ === true) return;
    $(ctx || document).find('.dinheiro').maskMoney({
      thousands: '.', decimal: ',', allowZero: true, precision: 2
    });
  }

  // Colapsáveis
  function setupCollapsibles() {
    function isNaoColapsavel(t) {
      t = (t || "").trim().toLowerCase();
      return t === "identificação" || t === "periodo e totais" || t === "período e totais";
    }
    $('.block').each(function () {
      const $block = $(this);
      const $title = $block.children('h5').first();
      const $body  = $title.nextAll();
      if (!$title.length || !$body.length) return;

      if (isNaoColapsavel($title.text())) {
        $title.attr({'data-static':'1','aria-expanded':'true'});
        $body.show();
        return;
      }
      $title.attr('aria-expanded','false'); $body.hide();
      $title.off('click.rahCollapse').on('click.rahCollapse', function () {
        const expanded = $title.attr('aria-expanded') === 'true';
        $title.attr('aria-expanded', expanded ? 'false' : 'true');
        $body.stop(true,true).slideToggle(160);
      });
    });

    document.addEventListener('shown.bs.collapse', function (ev) {
      const $blk = $(ev.target).closest('.block');
      applyMask($blk.get(0));
      $blk.find('.rah-cobrado,.rah-glosado').first().trigger('input');
    });
  }

  // Espelho “Período e Totais”
  (function setupPeriodMirror() {
    function syncPeriodTotals(tCob, tLib) {
      const $desc  = $('#desconto_valor_cap');
      let d = 0;
      if ($desc.length) d = R.moneyToFloat($desc.val());
      const vFinal = Math.max(0, tLib - d);

      const $inpApr = $('[name="valor_apresentado_capeante"]').first();
      const $inpFin = $('[name="valor_final_capeante"]').first();

      function setMoney($inp, valorNum) {
        if (!$inp.length) return;
        if ($.fn.maskMoney && $.fn.maskMoney.__stub__ !== true) $inp.maskMoney('mask', Number(valorNum));
        else $inp.val(R.floatToMoney(valorNum)).trigger('change');
      }
      setMoney($inpApr, tCob);
      setMoney($inpFin, vFinal);

      $('.pill-val-apr').text(R.floatToMoney(tCob));
      $('.pill-val-fin').text(R.floatToMoney(vFinal));
    }
    w.RAHSync = { syncPeriodTotals };

    function sumAll() {
      let tCob = 0, tLib = 0;
      $('.tuss-row').each(function () {
        const $r = $(this);
        tCob += R.moneyToFloat($r.find('.rah-cobrado').val());
        tLib += R.moneyToFloat($r.find('.rah-liberado').val());
      });
      const $desc = $('#desconto_valor_cap');
      let d = 0;
      if ($desc.length) d = R.moneyToFloat($desc.val());
      const vFinal = Math.max(0, tLib - d);
      return { tCob, vFinal };
    }

    function mirrorNow() {
      const tot = sumAll();
      const $apr = $('[name="valor_apresentado_capeante"]').first();
      const $fin = $('[name="valor_final_capeante"]').first();

      if ($.fn.maskMoney && $.fn.maskMoney.__stub__ !== true) {
        $apr.maskMoney('mask', Number(tot.tCob));
        $fin.maskMoney('mask', Number(tot.vFinal));
      } else {
        $apr.val(R.floatToMoney(tot.tCob));
        $fin.val(R.floatToMoney(tot.vFinal));
      }
      $('.pill-val-apr').text(R.floatToMoney(tot.tCob));
      $('.pill-val-fin').text(R.floatToMoney(tot.vFinal));
    }

    $(function () {
      $(document).on('input change keyup', '.rah-cobrado, .rah-glosado, #desconto_valor_cap', mirrorNow);
      document.addEventListener('shown.bs.collapse', function () { applyMask(document); mirrorNow(); });
      mirrorNow();
      setTimeout(mirrorNow, 80);
    });
  })();

  // Cadastro Central: habilita/desabilita selects + pill
  function setupCadastroCentral() {
    function setEnabled(el, on) { if (!el) return; el.disabled = !on; if (!on) el.value = ""; }
    function updateUI() {
      const on = (document.getElementById('cadastro_central_cap')?.value || 'n') === 's';
      setEnabled(document.getElementById('cad_central_med_id'), on);
      setEnabled(document.getElementById('cad_central_enf_id'), on);
      setEnabled(document.getElementById('cad_central_adm_id'), on);
      const pill = document.getElementById('cc-pill');
      if (pill) pill.textContent = on ? 'Ativo' : 'Inativo';
    }
    document.addEventListener('change', (e) => {
      if (e.target && e.target.id === 'cadastro_central_cap') updateUI();
    });
    document.addEventListener('DOMContentLoaded', updateUI);
  }

  // Hidrata selects a partir dos hidden
  function hydrateSelects() {
    function setSelectByValue(sel, value, fallback) {
      if (!sel || !value || value === "0") return;
      let opt = sel.querySelector('option[value="' + value + '"]');
      if (!opt) {
        opt = document.createElement('option');
        opt.value = value;
        opt.textContent = fallback || ('Selecionado (ID ' + value + ')');
        sel.insertBefore(opt, sel.firstChild);
      }
      sel.value = value;
    }
    function fireChange(el) {
      if (!el) return;
      try { el.dispatchEvent(new Event('change', { bubbles:true })); }
      catch(e){ const evt=document.createEvent('HTMLEvents'); evt.initEvent('change', true, false); el.dispatchEvent(evt); }
    }
    function hydrate() {
      const selCentral = document.getElementById('cadastro_central_cap');
      const selMed    = document.getElementById('cad_central_med_id');
      const selEnf    = document.getElementById('cad_central_enf_id');
      const selAdm    = document.getElementById('cad_central_adm_id');

      const fkMed = (document.getElementById('fk_id_aud_med')||{}).value || "";
      const fkEnf = (document.getElementById('fk_id_aud_enf')||{}).value || "";
      const fkAdm = (document.getElementById('fk_id_aud_adm')||{}).value || "";

      if (selCentral && (fkMed && fkMed!=='0' || fkEnf && fkEnf!=='0' || fkAdm && fkAdm!=='0')) {
        selCentral.value = 's'; fireChange(selCentral);
      }
      setSelectByValue(selMed, fkMed, 'Médico selecionado (ID ' + fkMed + ')');
      setSelectByValue(selEnf, fkEnf, 'Enfermeiro(a) selecionado(a) (ID ' + fkEnf + ')');
      setSelectByValue(selAdm, fkAdm, 'Administrativo selecionado (ID ' + fkAdm + ')');

      fireChange(selMed); fireChange(selEnf); fireChange(selAdm); fireChange(selCentral);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', hydrate);
    else hydrate();
  }

  // Boot
  $(function () {
    applyMask(document);
    setupCollapsibles();
    setupCadastroCentral();
    hydrateSelects();
  });

  // expõe util para quando inserir linhas dinamicamente
  w.RAHUI = { applyMask };
})(window);
