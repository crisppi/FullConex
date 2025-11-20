// js/show_internacao_visitas.js
(function(window, $) {
    if (!$) return;

    var tabsBound = false;
    var visitasEventsBound = false;

    function ensureTimelineFocus() {
        var container = document.querySelector('#visitas .ht-container');
        if (!container) return;

        requestAnimationFrame(function() {
            var active = document.querySelector('#visitas .ht-marker.active');
            var fallbackList = document.querySelectorAll('#visitas .ht-marker');
            var target = active || (fallbackList.length ? fallbackList[fallbackList.length - 1] : null);
            if (!target) {
                container.scrollLeft = container.scrollWidth;
                return;
            }
            container.scrollLeft = Math.max(0, target.offsetLeft - container.clientWidth / 2);
        });
    }

    function setupTabs() {
        var tabsEl = document.getElementById('internTabs');
        if (!tabsEl) return;

        var hash = window.location.hash;
        if (hash) {
            var triggerEl = tabsEl.querySelector('button[data-bs-target="' + hash + '"]');
            if (triggerEl && window.bootstrap && window.bootstrap.Tab) {
                new window.bootstrap.Tab(triggerEl).show();
            }
        }

        if (tabsBound) {
            ensureTimelineFocus();
            return;
        }

        tabsEl.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function(ev) {
                var target = ev.target.getAttribute('data-bs-target');
                if (target) history.replaceState(null, '', target);
                if (target === '#visitas') {
                    setTimeout(ensureTimelineFocus, 0);
                }
            });
        });

        tabsBound = true;
    }

    function normDate(d) {
        if (!d) return '';
        return String(d).substring(0, 10);
    }

    function getVisitasContext() {
        var $visitasTab = $('#visitas');
        if (!$visitasTab.length) return null;

        var $ini = $('#vis_ini');
        var $fim = $('#vis_fim');
        var $markers = $visitasTab.find('.ht-marker');

        if (!$ini.length || !$fim.length || !$markers.length) return null;

        return {
            $visitasTab: $visitasTab,
            $ini: $ini,
            $fim: $fim,
            $markers: $markers
        };
    }

    function aplicarFiltro() {
        var ctx = getVisitasContext();
        if (!ctx) return;

        var $visitasTab = ctx.$visitasTab;
        var $ini = ctx.$ini;
        var $fim = ctx.$fim;
        var $markers = ctx.$markers;

        var ini = normDate($ini.val());
        var fim = normDate($fim.val());

        var ultimoVisivel = null;

        $markers.each(function() {
            var $m = $(this);
            var d = normDate($m.data('dateraw'));

            var visivel = true;
            if (ini && d < ini) visivel = false;
            if (fim && d > fim) visivel = false;

            $m.toggle(visivel);
            if (visivel) ultimoVisivel = this;
        });

        if (!ultimoVisivel) {
            return;
        }

        var ativoVisivel = null;
        $markers.each(function() {
            if (this.classList.contains('active') && $(this).is(':visible')) {
                ativoVisivel = this;
                return false;
            }
        });

        if (!ativoVisivel) {
            ativoVisivel = ultimoVisivel;
            ativoVisivel.click();
        }

        var cont = $visitasTab.find('.ht-container')[0];
        if (cont && ativoVisivel) {
            cont.scrollLeft = Math.max(0, ativoVisivel.offsetLeft - cont.clientWidth / 2);
        }
    }

    function limparFiltro() {
        var ctx = getVisitasContext();
        if (!ctx) return;

        var $visitasTab = ctx.$visitasTab;
        var $ini = ctx.$ini;
        var $fim = ctx.$fim;
        var $markers = ctx.$markers;

        var defIni = $ini.data('default') || '';
        var defFim = $fim.data('default') || '';

        $ini.val(defIni);
        $fim.val(defFim);

        $markers.show();

        var ativo = null;
        $markers.each(function() {
            if (this.classList.contains('active')) {
                ativo = this;
                return false;
            }
        });

        if (!ativo && $markers.length) {
            ativo = $markers[$markers.length - 1];
        }

        if (ativo) {
            ativo.click();
            var cont = $visitasTab.find('.ht-container')[0];
            if (cont) {
                cont.scrollLeft = Math.max(0, ativo.offsetLeft - cont.clientWidth / 2);
            }
        }
    }

    function bindVisitasEvents() {
        if (visitasEventsBound) return;

        $(document).on('click', '#btnAplicarVisitas', function(e) {
            e.preventDefault();
            aplicarFiltro();
        });

        $(document).on('click', '#btnLimparVisitas', function(e) {
            e.preventDefault();
            limparFiltro();
        });

        visitasEventsBound = true;
    }

    function setupVisitasFilter() {
        bindVisitasEvents();
        ensureTimelineFocus();
    }

    window.setupInternacaoTabs = setupTabs;
    window.setupVisitasFilter = setupVisitasFilter;

})(window, window.jQuery);
