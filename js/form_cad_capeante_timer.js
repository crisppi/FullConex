(function () {
    'use strict';

    function setupTimer(form) {
        if (!form) return;
        var timerStart = null;

        function startTimer() {
            if (timerStart === null) {
                timerStart = Date.now();
            }
        }

        function stopTimer() {
            var field = form.querySelector('input[name="timer_cap"]');
            if (!field) return;
            var elapsed = timerStart !== null ? Math.max(0, Math.round((Date.now() - timerStart) / 1000)) : 0;
            field.value = elapsed;
        }

        ['input', 'change', 'keydown', 'focus'].forEach(function (evt) {
            form.addEventListener(evt, startTimer, true);
        });

        form.addEventListener('submit', stopTimer);
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupTimer(document.getElementById('multi-step-form'));
        setupTimer(document.getElementById('form-capeante-rah'));
    });
})();
