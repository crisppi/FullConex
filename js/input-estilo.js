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
});
