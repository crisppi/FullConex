// Função genérica para lidar com os selects e exibição de divs
function handleSelectChange(selectId, divId) {
    $(`#${selectId}`).change(function () {
        var option = $(this).find(":selected").text();

        // Lista de todos os selects e containers
        var selects = ["select_tuss", "select_gestao", "select_prorrog", "select_uti", "select_negoc"];
        var containers = ["container-tuss", "container-gestao", "container-prorrog", "container-uti", "container-negoc"];

        // Esconde todos os containers
        containers.forEach(function (container) {
            var div = document.querySelector(`#${container}`);
            if (div) {
                div.style.display = "none";
            }
        });

        // Reseta estilos de todos os selects (sem fundo roxo)
        selects.forEach(function (select) {
            $(`#${select}`).css({
                "color": "gray",
                "font-weight": "normal",
                "border": "1px solid gray",
                "background-color": "" // Remove o fundo roxo
            });
        });

        // Se a opção for "Sim", aplica estilo ao select ativo e exibe o container correspondente
        if (option === "Sim") {
            $(`#${selectId}`).css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px solid green",
                "background-color": "rgba(128, 110, 129, 0.3)" // Fundo roxo claro
            });

            var activeDiv = document.querySelector(`#${divId}`);
            if (activeDiv) {
                activeDiv.style.display = "block";
            }
        }

        // Mantém o estilo para outros selects com "Sim" (sem fundo roxo)
        selects.forEach(function (select) {
            if (select !== selectId) { // Ignora o ativo atual
                var selectedOption = $(`#${select}`).find(":selected").text();
                if (selectedOption === "Sim") {
                    $(`#${select}`).css({
                        "color": "black",
                        "font-weight": "bold",
                        "border": "2px solid green",
                        "background-color": "" // Sem fundo roxo
                    });
                }
            }
        });
    });
}

// Adiciona o evento para cada select
handleSelectChange("select_tuss", "container-tuss");
handleSelectChange("select_gestao", "container-gestao");
handleSelectChange("select_prorrog", "container-prorrog");
handleSelectChange("select_uti", "container-uti");
handleSelectChange("select_negoc", "container-negoc");
