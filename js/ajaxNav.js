function edit(url) {

    $.ajax({
        url: url, // URL do formulário
        type: "GET", // Método do formulário (POST)
        success: function (response) {

            // Crie um elemento temporário para armazenar a resposta HTML
            var tempElement = document.createElement('div');
            tempElement.innerHTML = response;

            // Encontre o elemento com o ID "table-content" dentro do elemento temporário
            var tableContent = tempElement.querySelector('#main-container');
            $('#main-container').html(tableContent);
            $('.selectpicker').selectpicker();
            $('.selectpicker').selectpicker('refresh');
            $('.selectpicker').on('loaded.bs.select', function () {
                $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
            });

        },


        error: function () {
            $('#responseMessage').html('Ocorreu um erro ao enviar o formulário.');
        }
    });
}


// ajax para navegacao 
function loadContent(url) {
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            // Crie um elemento temporário para armazenar a resposta HTML
            var tempElement = document.createElement('div');
            tempElement.innerHTML = data;

            // Encontre o elemento com o ID "table-content" dentro do elemento temporário
            var tableContent = tempElement.querySelector('#table-content');
            $('#table-content').html(tableContent);
        },
        error: function () {
            console.log('Error loading content');
        }
    });
}
