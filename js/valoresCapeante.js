// Máscara para valores monetários

$('.dinheiro').maskMoney({
    prefix: 'R$ ',
    allowNegative: false,
    thousands: '.',
    decimal: ',',
    affixesStay: true
});

$('.dinheiro_total').maskMoney({
    prefix: 'R$ ',
    allowNegative: false,
    thousands: '.',
    decimal: ',',
    affixesStay: true
});

function calcularTotalFinal() {
    let glosaMed = parseFloat($('#valor_glosa_med').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;
    let glosaEnf = parseFloat($('#valor_glosa_enf').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;
    let glosaTotal = glosaMed + glosaEnf;
    let valorTotal = parseFloat($('#valor_apresentado_capeante').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;
    var divMsg1 = document.querySelector(".notif3");
    var divMsg2 = document.querySelector(".notif4");

    $('#total-final').text('R$ ' + (valorTotal - glosaTotal).toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    if ((glosaMed + glosaEnf) > valorTotal) {
        $('#btn-next-1').prop('disabled', true);
        divMsg1.style.display = "block";
        divMsg2.style.display = "block";
    }
    else {
        $('#btn-next-1').prop('disabled', false);
        divMsg1.style.display = "none";
        divMsg2.style.display = "none";
    }

}

// Cálculo dos totais
function calcularTotais() {
    let totalValores = 0;
    let totalGlosas = 0;
    let valorTotal = parseFloat($('#valor_apresentado_capeante').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;

    let glosaMed = parseFloat($('#valor_glosa_med').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;
    let glosaEnf = parseFloat($('#valor_glosa_enf').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;
    let glosaTotal = glosaMed + glosaEnf

    $('.dinheiro').each(function () {
        let valor = $(this).val().replace(/[^\d,-]/g, '').replace(',', '.');
        if (!isNaN(valor) && valor.length > 0) {
            valor = parseFloat(valor);
            if ($(this).attr('name').startsWith('glosa')) {
                totalGlosas += valor;
            } else {
                if (!$(this).attr('name').startsWith('valor_apresentado_capeante')) {
                    totalValores += valor;
                }
            }
        }
    });
    $('#total-apresentado').text('R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    $('#total-valores-final').text('R$ ' + totalValores.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    $('#total-valores').text('R$ ' + totalValores.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    $('#total-glosas-final').text('R$ ' + glosaTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    $('#total-glosas').text('R$ ' + totalGlosas.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    $('#total-final').text('R$ ' + (totalValores - totalGlosas).toLocaleString('pt-BR', { minimumFractionDigits: 2 }));

    diffGlosa = Math.trunc((totalGlosas - (glosaEnf + glosaMed)) * 1000) / 1000
    diff = Math.trunc((valorTotal - totalValores) * 1000) / 1000
    if (Math.abs(diff) > 0.001 & totalValores > 0) {
        $('#btn-next-2').prop('disabled', true);
        $('#nodiff_valor').hide();
        $('#diff_valor').text('Diferença no valor total apresentado de R$' + diff.toLocaleString('pt-BR', { minimumFractionDigits: 2 })).show();
    } else {
        $('#nodiff_valor').show();
        $('#diff_valor').hide();
    }
    if (Math.abs(diffGlosa) > 0.001 & totalGlosas > 0) {
        $('#btn-next-2').prop('disabled', true);
        $('#nodiff_valor_glosa').hide();
        $('#diff_valor_glosa').text('Diferença no valor total de glosa apresentado de R$' + diffGlosa.toLocaleString('pt-BR', { minimumFractionDigits: 2 })).show();
    } else {
        $('#nodiff_valor_glosa').show();
        $('#diff_valor_glosa').hide();
    }

    if( (Math.abs(diff) <= 0.001 & Math.abs(diffGlosa) <= 0.001 ) | (totalValores == 0 & totalGlosas == 0)){
        $('#btn-next-2').prop('disabled', false);
    }


}

function calcularDesconto() {
    let valorTotal = parseFloat($('#valor_apresentado_capeante').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;
    let glosaMed = parseFloat($('#valor_glosa_med').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;
    let glosaEnf = parseFloat($('#valor_glosa_enf').val().replace(/[R$.]/g, '').replace(',', '.')) || 0;
    let valorDesconto = parseInt($('#desconto_valor_cap').val()) || 0;
    if (valorDesconto > 0) {
        let valorFinalDesconto = (valorTotal - (glosaMed + glosaEnf)) - ((valorTotal - (glosaMed+ glosaEnf)) * (valorDesconto / 100))

        $('#total-valores-final-desconto').text('R$ ' + valorFinalDesconto.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    }

}
calcularTotais()
calcularDesconto()
calcularTotalFinal()
// Atualizar totais quando valores forem alterados
$('.dinheiro').on('keyup', calcularTotais);

$('.dinheiro_total').on('keyup', calcularTotalFinal);

$('#desconto_valor_cap').on('keyup', calcularDesconto)