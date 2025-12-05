$(document).ready(function () {
    const $inputs = $('#form_pesquisa input[type="text"], #form_pesquisa select, #select-internacao-form input[type="text"], #select-internacao-form select');

    function atualizarEstilos() {
        $inputs.each(function () {
            const temValor = $(this).val().trim() !== '';
            if (temValor || $(this).is(':focus')) {
                $(this).addClass('input-selecionado');
            } else {
                $(this).removeClass('input-selecionado');
            }
        });
    }

    atualizarEstilos();
    $inputs.on('focus blur input change', function () {
        atualizarEstilos();
    });

    const $allSelects = $('select');

    function togglePlaceholderClass(el) {
        const hasValue = $(el).val() && $(el).val().toString().trim() !== '';
        $(el).toggleClass('select-placeholder-empty', !hasValue);
    }

    $allSelects.each(function () {
        togglePlaceholderClass(this);
    });

    $allSelects.on('change input', function () {
        togglePlaceholderClass(this);
    });
});
