(function () {
    function getHeaderElement() {
        return document.querySelector('.navbar.fixed-top') || document.querySelector('.fixed-top');
    }

    function updateHeaderOffset() {
        var header = getHeaderElement();
        if (!header) return;

        var rect = header.getBoundingClientRect();
        var height = Math.max(0, Math.ceil(rect.height));

        document.documentElement.style.setProperty('--header-offset', height + 'px');
    }

    function scheduleUpdate() {
        window.requestAnimationFrame(updateHeaderOffset);
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateHeaderOffset();
        setTimeout(updateHeaderOffset, 0);
        setTimeout(updateHeaderOffset, 120);
        setTimeout(updateHeaderOffset, 300);
    });

    window.addEventListener('load', scheduleUpdate);
    window.addEventListener('resize', scheduleUpdate);
    window.addEventListener('orientationchange', scheduleUpdate);

    // cobre mudanças de zoom aplicadas por script do header
    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!target) return;
        if (target.id === 'zoom-in-btn' || target.id === 'zoom-out-btn') {
            setTimeout(updateHeaderOffset, 0);
            setTimeout(updateHeaderOffset, 150);
        }
    });

    // cobre abertura/fechamento do menu colapsado
    document.addEventListener('shown.bs.collapse', scheduleUpdate);
    document.addEventListener('hidden.bs.collapse', scheduleUpdate);

    if ('ResizeObserver' in window) {
        var header = getHeaderElement();
        if (header) {
            var observer = new ResizeObserver(function () {
                scheduleUpdate();
            });
            observer.observe(header);
        }
    }
})();
