// ****************************************** //
// ENTRADA DE DADOS DE ACOMODACAO - ESTRUTURA CONDICIONAL
// ****************************************** //


function formatAcomod(event) {
    let input = event.target;
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres que não são números

    // Verifica se há algum valor para formatar
    if (value.length === 0) {
        input.value = '';
        return;
    }

    // Adiciona a vírgula dos centavos
    value = (value / 100).toFixed(2).replace('.', ',');

    // Adiciona os pontos de milhar
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    // Atualiza o valor do input
    input.value = `R$${value}`;
}