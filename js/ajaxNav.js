// js/ajaxNav.js

function runEmbeddedScripts(element) {
    if (!element) return Promise.resolve();

    var scripts = Array.from(element.querySelectorAll('script'));
    if (!scripts.length) return Promise.resolve();

    function execInline(script) {
        var code = script.textContent || script.innerHTML || '';
        if (!code.trim()) return;
        if (window.jQuery && typeof $.globalEval === 'function') {
            $.globalEval(code);
        } else {
            new Function(code)();
        }
    }

    return scripts.reduce(function (chain, script) {
        return chain.then(function () {
            return new Promise(function (resolve) {
                var cleanup = function () {
                    if (script.parentNode) {
                        script.parentNode.removeChild(script);
                    }
                };
                if (script.src) {
                    var newScript = document.createElement('script');
                    newScript.src = script.src;
                    newScript.async = false;
                    newScript.onload = function () {
                        cleanup();
                        if (newScript.parentNode) newScript.parentNode.removeChild(newScript);
                        resolve();
                    };
                    newScript.onerror = function () {
                        cleanup();
                        if (newScript.parentNode) newScript.parentNode.removeChild(newScript);
                        resolve();
                    };
                    document.head.appendChild(newScript);
                } else {
                    execInline(script);
                    cleanup();
                    resolve();
                }
            });
        });
    }, Promise.resolve());
}

function edit(url) {
    if (typeof url === 'string' && /capeante_rah\.php/.test(url)) {
        window.location.href = url;
        return;
    }
    $.ajax({
        url: url,
        type: "GET",
        dataType: "html",
        success: function (response) {
            var tempElement = document.createElement('div');
            tempElement.innerHTML = response;

            var innerMain = tempElement.querySelector('#main-container');
            var target = innerMain || tempElement;

            if (innerMain) {
                $('#main-container').html(innerMain.innerHTML);
            } else {
                $('#main-container').html(response);
            }

            runEmbeddedScripts(target).then(function () {
                if ($.fn.selectpicker) {
                    $('.selectpicker').selectpicker();
                    $('.selectpicker').selectpicker('refresh');
                }
                if (typeof window.applyHeaderSortOnListPages === 'function') {
                    window.applyHeaderSortOnListPages();
                }
            });
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
            var target = tableContent || tempElement;

            if (tableContent) {
                $('#table-content').html(tableContent.innerHTML);
            } else {
                $('#table-content').html(data);
            }

            runEmbeddedScripts(target).then(function () {
                if ($.fn.selectpicker) {
                    $('.selectpicker').selectpicker('refresh');
                }
                if (typeof window.applyHeaderSortOnListPages === 'function') {
                    window.applyHeaderSortOnListPages();
                }
            });
        },
        error: function () {
        }
    });
}
