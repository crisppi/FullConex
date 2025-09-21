function consultarCEP(i, sufixo) {
    /* 
        Função responsável por receber os valores de input do CEP e requisitar na API do Viacep o endereço completo
        Parâmetros: i = valor do input digitado capturado pelo evento
    */
    i.setAttribute("maxlength", "9");
    if (i.value.length == 5) i.value += "-";
    var cep = i.value.replace("-", "");
    // Monta a URL de consulta
    var url = `https://viacep.com.br/ws/${cep}/json/`;

    const options = {
        method: 'GET',
        headers: {
            'Access-Control-Allow-Origin': true,
        }
    };

    if (cep.length == 8) {
        // Faz a requisição
        fetch(url, options)
            .then(response => response.json())
            .then(data => {
                // Verifica se a requisição foi bem sucedida
                if (!data.erro) {
                    // O endereço completo está disponível em data
                    console.log('Endereço:', data);
                    document.getElementById("endereco_" + sufixo).value = data.logradouro
                    document.getElementById("bairro_" + sufixo).value = data.bairro
                    document.getElementById("cidade_" + sufixo).value = data.localidade
                    document.getElementById("estado_" + sufixo).value = data.uf
                } else {
                    console.error('CEP não encontrado');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
            });
    }
}

function validarCPF(cpf) {
    /* 
        Função responsável por receber o valor do cpf e retornar se ele é valido ou não
        Parâmetros: cpf = valor do cpf digitado pelo usuário
    */
    cpf = cpf.replace(/[^\d]+/g, ''); // Remove caracteres não numéricos

    if (cpf == '') return false;

    // Verifica se o CPF tem 11 dígitos
    if (cpf.length != 11) return false;

    // Verifica se todos os dígitos são iguais
    var todosIguais = /^(.)\1+$/.test(cpf);
    if (todosIguais) return false;

    // Calcula e verifica o primeiro dígito verificador
    var soma = 0;
    for (var i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    var resto = 11 - (soma % 11);
    var digitoVerificador1 = (resto == 10 || resto == 11) ? 0 : resto;

    if (digitoVerificador1 != parseInt(cpf.charAt(9))) return false;

    // Calcula e verifica o segundo dígito verificador
    soma = 0;
    for (var i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = 11 - (soma % 11);
    var digitoVerificador2 = (resto == 10 || resto == 11) ? 0 : resto;

    if (digitoVerificador2 != parseInt(cpf.charAt(10))) return false;

    return true;
}



function mascara(i) {

    var v = i.value;

    if (isNaN(v[v.length - 1])) { // impede entrar outro caractere que não seja número
        i.value = v.substring(0, v.length - 1);
        return;
    }

    i.setAttribute("maxlength", "14");
    if (v.length == 3 || v.length == 7) i.value += ".";
    if (v.length == 11) i.value += "-";

}

var idCensoList = [];

// Function to check if idCensoList is empty and disable the submit button accordingly
function checkIdListEmpty() {
    var submitButton = document.getElementById("submitInter");
    if (submitButton) {
        if (idCensoList.length === 0) {
            submitButton.disabled = true;
        } else {
            submitButton.disabled = false;
        }
    }

}


// Call the function initially to set the initial state of the button
checkIdListEmpty();

function addList(id) {
    console.log(id)
    var index = idCensoList.indexOf(id);
    if (index === -1) {
        idCensoList.push(id); // Add ID to the list if it's not already present
    } else {
        idCensoList.splice(index, 1); // Remove ID from the list if it's already present
    }
    checkIdListEmpty();
}


// Function to send the ID list to PHP
function sendIdListToPHP() {
    // Iterate over each ID in idCensoList array
    idCensoList.forEach(function (id) {
        // Create a FormData object for each ID
        var formData = new FormData();
        // Append the ID and type to the FormData object
        formData.append('id_censo', id);
        formData.append('type', 'create');
        // Send AJAX request to PHP script
        fetch('process_censo_int.php?type=create&id_censo=' + id, {
            method: 'GET'
        })
            .then(response => response.text())
            .then(data => {
                location.reload();// Log the response from PHP
                // You can perform further actions here based on the response from PHP
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
}



// function validarCpfExistente(i, t) {
//     var v = i.value;
//     var formData = new FormData();
//     formData.append('cpf', v.replaceAll('.', '').replaceAll("-", ""));
//     if (v.length > 0) {
//         $.ajax({
//             url: 'process_cpf_paciente.php', // URL do arquivo PHP
//             type: 'POST', // Método de envio
//             processData: false, // Não processar os dados
//             contentType: false, // Não definir o tipo de conteúdo
//             data: formData, // Dados a serem enviados
//             success: function (response) {
//                 console.log(response);
//                 if (response == 0) {
//                     document.getElementById("validar_cpf").style.display = 'none'
//                     document.getElementById("cadastrar_paciente").disabled = false
//                 } else {
//                     document.getElementById("validar_cpf").style.display = 'block'
//                     document.getElementById("cadastrar_paciente").disabled = true
//                 }

//             },
//             error: function () {
//                 console.log("error")
//             }
//         });
//     }
// }


function mascara(i, t) {
    var v = i.value;
    if (isNaN(v[v.length - 1])) {
        i.value = v.substring(0, v.length - 1);
        return;
    }

    if (t == "data") {
        i.setAttribute("maxlength", "10");
        if (v.length == 2 || v.length == 5) i.value += "/";
    }

    if (t == "cpf") {
        i.setAttribute("maxlength", "14");
        if (v.length == 3 || v.length == 7) i.value += ".";
        if (v.length == 11) i.value += "-";
    }

    if (t == "cnpj") {
        i.setAttribute("maxlength", "18");
        if (v.length == 2 || v.length == 6) i.value += ".";
        if (v.length == 10) i.value += "/";
        if (v.length == 15) i.value += "-";
    }

    if (t == "cep") {
        i.setAttribute("maxlength", "9");
        if (v.length == 5) i.value += "-";
    }

    if (t == "tel") {
        if (v[0] == 12) {

            i.setAttribute("maxlength", "10");
            if (v.length == 5) i.value += "-";
            if (v.length == 0) i.value += "(";

        } else {
            i.setAttribute("maxlength", "9");
            if (v.length == 4) i.value += "-";
        }
    }
}

function mascaraTelefone(event) {
    let tecla = event.key;
    let telefone = event.target.value.replace(/\D+/g, "");

    if (/^[0-9]$/i.test(tecla)) {
        telefone = telefone + tecla;
        let tamanho = telefone.length;

        if (tamanho >= 12) {
            return false;
        }

        if (tamanho > 10) {
            telefone = telefone.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
        } else if (tamanho > 5) {
            telefone = telefone.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
        } else if (tamanho > 2) {
            telefone = telefone.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
        } else {
            telefone = telefone.replace(/^(\d*)/, "($1");
        }

        event.target.value = telefone;
    }

    if (!["Backspace", "Delete"].includes(tecla)) {
        return false;
    }
}

function aumentarTextObs() {
    // mudar linhas da obs paciente 
    var text_obs = document.getElementsByName("obs_pac");
    console.log(text_obs)
    if (text_obs.rows == "2") {
        text_obs.rows = "20"
    } else {
        text_obs.rows = "2"
    }
}


// ajax para navegacao 
function openModal(page) {
    $.ajax({
        url: page,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            // Crie um elemento temporário para armazenar a resposta HTML
            var tempElement = document.createElement('div');
            tempElement.innerHTML = data;

            // Encontre o elemento com o ID "table-content" dentro do elemento temporário
            var tableContent = tempElement.querySelector('#main-container');
            $('#content-php').html(tableContent);
        },
        error: function () {
            console.log('Error loading content');
        }
    });
}


// ajax para navegacao 
function openModalPat(page) {
    $.ajax({
        url: page,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            // Crie um elemento temporário para armazenar a resposta HTML
            var tempElement = document.createElement('div');
            tempElement.innerHTML = data;

            // Encontre o elemento com o ID "table-content" dentro do elemento temporário
            var tableContent = tempElement.querySelector('#main-container');
            $('#content-php').html(tableContent);
            $('.selectpicker').selectpicker();
            $('.selectpicker').selectpicker('refresh');
            $('.selectpicker').on('loaded.bs.select', function () {
                $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
            });
        },
        error: function () {
            console.log('Error loading content');
        }
    });
}


// ajax para navegacao 
function openModalAnt(page) {
    $.ajax({
        url: page,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            // Crie um elemento temporário para armazenar a resposta HTML
            var tempElement = document.createElement('div');
            tempElement.innerHTML = data;

            // Encontre o elemento com o ID "table-content" dentro do elemento temporário
            var tableContent = tempElement.querySelector('#main-container');
            $('#content-php').html(tableContent);

            $('.selectpicker').selectpicker();
            $('.selectpicker').selectpicker('refresh');
            $('.selectpicker').on('loaded.bs.select', function () {
                $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
            });
        },
        error: function () {
            console.log('Error loading content');
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


