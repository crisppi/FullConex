$(document).ready(function() {
    $("input.dinheiro").maskMoney({
        showSymbol: true,
        symbol: "R$",
        decimal: ",",
        thousands: "."
    });
});