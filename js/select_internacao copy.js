// aparecer e pegar dados do select do detalhes

var relatorioDetalhado = document.getElementById("#relatorio-detalhado"); //mudar cor do select qdo selecionado
$('#relatorio-detalhado').change(function () {
    var optionDetalhes = $('#relatorio-detalhado').find(":selected").text();

    if (optionDetalhes == "Sim") {
        $("#relatorio-detalhado").css({
            "color": "black",
            "font-weight": "bold",
            "border": "2px",
            "border-color": "green",
            "border-style": "solid"

        });
    };
});

// aparecer e pegar dados do select do Tuss
$('#select_tuss').change(function () {
    var option = $('#select_tuss').find(":selected").text();

    if (option == "Sim") {
        var divTuss = document.querySelector("#container-tuss");
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        if (divTuss.style.display === "none") {
            divTuss.style.display = "block";
            divPro.style.display = "none";
            divUti.style.display = "none";
            divNeg.style.display = "none";

            var select_tuss = document.getElementById("#select_tuss"); //mudar cor do select qdo selecionado
            $("#select_tuss").css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px",
                "border-color": "green",
                "border-style": "solid"
            });

        } else {
            divTuss.style.display = "none";
        }

    } else {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divTuss = document.querySelector("#container-tuss");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");
        divTuss.style.display = "none";

        var select_tuss = document.getElementById("#select_tuss");
        $("#select_tuss").css({
            "color": "gray",
            "font-weight": "normal",
            "border": "1px",
            "border-color": "gray",
            "border-style": "solid"

        });
    };
});

// aparecer e pegar dados do select do Gestao
$('#select_gestao').change(function () {
    var option = $('#select_gestao').find(":selected").text();

    if (option == "Sim") {
        var divTuss = document.querySelector("#container-tuss");
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");


        if (divGes.style.display === "none") {
            divGes.style.display = "block";
            divPro.style.display = "none";
            divTuss.style.display = "none";
            divUti.style.display = "none";
            divNeg.style.display = "none";

            var select_gestao = document.getElementById("#select_gestao"); //mudar cor do select qdo selecionado
            $("#select_gestao").css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px",
                "border-color": "green",
                "border-style": "solid"

            });

        } else {
            divGes.style.display = "none";
        }

    } else {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divTuss = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");
        divGes.style.display = "none";

        var select_gestao = document.getElementById("#select_gestao");
        $("#select_gestao").css({
            "color": "gray",
            "font-weight": "normal",
            "border": "1px",
            "border-color": "gray",
            "border-style": "solid"

        });
    };
});

// aparecer e pegar dados do select UTI

$('#select_uti').change(function () {
    var option = $('#select_uti').find(":selected").text();

    if (option == "Sim") {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");


        if (divUti.style.display === "none") {
            divUti.style.display = "block";
            divPro.style.display = "none";
            divGes.style.display = "none";
            divNeg.style.display = "none";

            var select_uti = document.getElementById("#select_uti"); //mudar cor do select qdo selecionado
            $("#select_uti").css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px",
                "border-color": "green",
                "border-style": "solid"

            });

        } else {
            divUti.style.display = "none";
        }

    } else {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        divUti.style.display = "none";
        var select_uti = document.getElementById("#select_uti");
        $("#select_uti").css({
            "color": "gray",
            "font-weight": "normal",
            "border": "1px",
            "border-color": "gray",
            "border-style": "solid"

        });
    };
});

// aparecer e pegar dados do select prorrogacao

$('#select_prorrog').change(function () {
    var option = $('#select_prorrog').find(":selected").text();

    // console.log(option);
    if (option == "Sim") {
        var divTuss = document.querySelector("#container-tuss");
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        if (divPro.style.display === "none") {
            divPro.style.display = "block";
            divGes.style.display = "none";
            divTuss.style.display = "none";
            divUti.style.display = "none";
            divNeg.style.display = "none";

            var select_prorrog = document.getElementById("#select_prorrog"); //mudar cor do select qdo selecionado
            $("#select_prorrog").css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px",
                "border-color": "green",
                "border-style": "solid"

            });


        } else {
            divPro.style.display = "none";
        }

    } else {
        var divTuss = document.querySelector("#container-tuss");
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        divPro.style.display = "none";

        var select_prorrog = document.getElementById("#select_prorrog");
        $("#select_prorrog").css({
            "color": "gray",
            "font-weight": "normal",
            "border": "1px",
            "border-color": "gray",
            "border-style": "solid"

        });
    };
});

// aparecer e pegar dados do select negociacao

$('#select_negoc').change(function () {
    var option = $('#select_negoc').find(":selected").text();

    // console.log(option);
    if (option == "Sim") {
        var divTuss = document.querySelector("#container-tuss");
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        if (divNeg.style.display === "none") {
            divNeg.style.display = "block";
            divTuss.style.display = "none";
            divPro.style.display = "none";
            divUti.style.display = "none";
            divGes.style.display = "none";

            var select_negoc = document.getElementById("#select_negoc"); //mudar cor do select qdo selecionado
            $("#select_negoc").css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px",
                "border-color": "green",
                "border-style": "solid",
                "background-color": "rgb(128, 110, 129)",


            });

        } else {

            divNeg.style.display = "none";

        }

    } else {
        var divTuss = document.querySelector("#container-tuss");
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        divNeg.style.display = "none";
        var select_negoc = document.getElementById("#select_negoc");
        $("#select_negoc").css({
            "color": "gray",
            "font-weight": "normal",
            "border": "1px",
            "border-color": "gray",
            "border-style": "solid"

        });
    };
});

// aparecer campo atb em uso
$(document).ready(function () {
    $('#atb').hide(); // Oculta o campo de texto quando a página carrega

    $('#atb_enf').change(function () {
        if ($(this).val() === 's') {
            $('#atb').show();
        } else {
            $('#atb').hide();
        }
    });
});
// aparecer campo litros de O2
$(document).ready(function () {
    $('#div-oxig').hide(); // Oculta o campo de texto quando a página carrega

    $('#oxig_enf').change(function () {
        if ($(this).val() === 'Cateter' || $(this).val() == 'Mascara') {
            $('#div-oxig').show();
        } else {
            $('#div-oxig').hide();
        }
    });
});
// aparecer campos relatorio detalhado
$(document).ready(function () {
    if (document.querySelector("#relatorio-detalhado").value === 's') {
        $('#div-detalhado').show();
        $('#text-detalhado').hide();
    } else {
        $('#div-detalhado').hide();
    }

    $('#relatorio-detalhado').change(function () {
        if ($(this).val() === 's') {
            $('#div-detalhado').show();
        } else {
            $('#div-detalhado').hide();
        }
    });
});