// Máscara para valores monetários

$('.dinheiro').maskMoney({
    prefix: 'R$ ',
    allowNegative: false,
    thousands: '.',
    decimal: ',',
    affixesStay: true
});
