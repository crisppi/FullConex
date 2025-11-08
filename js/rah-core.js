// Namespace único
window.RAH = window.RAH || {};

// Shims leves (evitam ReferenceError se libs não carregarem)
(function (w) {
  const $ = w.jQuery;

  // recalcAll “vazio” até o rah-calc definir de verdade
  w.recalcAll = w.recalcAll || function () {};

  if ($ && !$.fn.selectpicker) {
    $.fn.selectpicker = function () { return this; };
    $.fn.selectpicker.__stub__ = true;
  }
  if ($ && !$.fn.maskMoney) {
    $.fn.maskMoney = function () { return this; };
    $.fn.maskMoney.__stub__ = true;
  }
})(window);

// Utils de moeda
(function (RAH) {
  function moneyToFloat(s) {
    if (s == null) return 0;
    s = ('' + s).trim();
    if (!s) return 0;
    s = s.replace(/[^\d.,-]/g, '').replace(/\./g, '').replace(',', '.');
    const v = parseFloat(s);
    return isNaN(v) ? 0 : v;
  }
  function floatToMoney(v) {
    if (!isFinite(v)) v = 0;
    const p = v.toFixed(2).split('.');
    const i = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return 'R$ ' + i + ',' + p[1];
  }
  RAH.moneyToFloat = moneyToFloat;
  RAH.floatToMoney  = floatToMoney;
})(window.RAH);
