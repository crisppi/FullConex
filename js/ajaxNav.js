// js/ajaxNav.js

function runEmbeddedScripts(element) {
    if (!element) return;

    var scripts = element.querySelectorAll('script');
    scripts.forEach(function (script) {
        if (script.src) {
            var newScript = document.createElement('script');
            newScript.src = script.src;
            newScript.async = false;
            newScript.onload = newScript.onerror = function () {
                newScript.parentNode && newScript.parentNode.removeChild(newScript);
            };
            document.head.appendChild(newScript);
        } else {
            var code = script.textContent || script.innerHTML || '';
            if (code.trim()) {
                $.globalEval(code);
            }
        }
    });
}

function edit(url) {
    $.ajax({
        url: url,
        type: "GET",
        dataType: "html",
        success: function (response) {
            var tempElement = document.createElement('div');
            tempElement.innerHTML = response;

            var innerMain = tempElement.querySelector('#main-container');

            if (innerMain) {
                $('#main-container').html(innerMain.innerHTML);
                runEmbeddedScripts(innerMain);
            } else {
                $('#main-container').html(response);
                runEmbeddedScripts(tempElement);
            }

            // --- CORREÇÃO DO ERRO ---
            // Verifica se o plugin existe antes de tentar usar
            if ($.fn.selectpicker) {
                // Tenta inicializar nos novos selects
                $('.selectpicker').selectpicker();
                // Tenta atualizar caso já existam
                $('.selectpicker').selectpicker('refresh');
            }
        },
        error: function () {
            $('#responseMessage').html('Ocorreu um erro ao carregar a página.');
        }
    });
}

function loadContent(url) {
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function (data) {
            var tempElement = document.createElement('div');
            tempElement.innerHTML = data;

            var tableContent = tempElement.querySelector('#table-content');

            if (tableContent) {
                $('#table-content').html(tableContent.innerHTML);
                runEmbeddedScripts(tableContent);
            } else {
                $('#table-content').html(data);
                runEmbeddedScripts(tempElement);
            }

            // --- CORREÇÃO DO ERRO ---
            if ($.fn.selectpicker) {
                $('.selectpicker').selectpicker('refresh');
            }
        },
        error: function () {
            console.log('Error loading content');
        }
    });
}
