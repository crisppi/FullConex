(function (w) {
  const $  = w.jQuery;
  const R  = w.RAH;

  function recalcRow($row) {
    const vCob = R.moneyToFloat($row.find('.rah-cobrado').val());
    const vGlo = R.moneyToFloat($row.find('.rah-glosado').val());
    const vLib = Math.max(0, vCob - vGlo);
    $row.find('.rah-liberado').val(R.floatToMoney(vLib));
  }

  function recalcBlock($block) {
    let tCob = 0, tGlo = 0, tLib = 0;
    $block.find('.tuss-row').each(function () {
      const $r = $(this);
      tCob += R.moneyToFloat($r.find('.rah-cobrado').val());
      tGlo += R.moneyToFloat($r.find('.rah-glosado').val());
      tLib += R.moneyToFloat($r.find('.rah-liberado').val());
    });
    $block.find('.grp-total-cobrado').val(R.floatToMoney(tCob));
    $block.find('.grp-total-glosado').val(R.floatToMoney(tGlo));
    $block.find('.grp-total-liberado').val(R.floatToMoney(tLib));
  }

  function recalcGrandTotals() {
    let tCob = 0, tGlo = 0, tLib = 0;
    $('.tuss-row').each(function () {
      const $r = $(this);
      tCob += R.moneyToFloat($r.find('.rah-cobrado').val());
      tGlo += R.moneyToFloat($r.find('.rah-glosado').val());
      tLib += R.moneyToFloat($r.find('.rah-liberado').val());
    });
    if ($('#total_cobrado').length)  $('#total_cobrado').val(R.floatToMoney(tCob));
    if ($('#total_glosado').length)  $('#total_glosado').val(R.floatToMoney(tGlo));
    if ($('#total_liberado').length) $('#total_liberado').val(R.floatToMoney(tLib));

    // Bridge para espelhar no “Período e Totais”
    if (w.RAHSync && typeof w.RAHSync.syncPeriodTotals === 'function') {
      w.RAHSync.syncPeriodTotals(tCob, tLib);
    }
  }

  function recalcAround($row) {
    recalcRow($row);
    const $block = $row.closest('.block');
    if ($block.length) recalcBlock($block);
    recalcGrandTotals();
  }

  function recalcAll() {
    $('.tuss-row').each(function () { recalcRow($(this)); });
    $('.block').each(function () {
      const $blk = $(this);
      if ($blk.find('.tuss-row').length) recalcBlock($blk);
    });
    recalcGrandTotals();
  }

  // Eventos
  $(function () {
    $(document).on('input change keyup', '.rah-cobrado, .rah-glosado', function () {
      recalcAround($(this).closest('.tuss-row'));
    });
    recalcAll();
    setTimeout(recalcAll, 60);
  });

  // API pública
  w.RAHCalc = { recalcRow, recalcBlock, recalcGrandTotals, recalcAll };
  w.recalcAll = recalcAll; // compat com chamadas existentes
})(window);
