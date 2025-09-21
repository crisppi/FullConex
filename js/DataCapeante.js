// ****************************************** //
// PEGAR DADOS DOS INPUTS //    
// ****************************************** //

let dataInicConta = document.getElementById("data_inicial_capeante");
let data_final_conta = document.getElementById("data_final_capeante");
let data_intern_int = document.getElementById("data_intern_int");
let diarias_capeante = document.getElementById("diarias_capeante");
let parcial_date = document.getElementById("parcial_date");

// **********  VERIFICAR DATA INICIAL DO CAPEANTE  ********  // 

dataInicConta.addEventListener("blur", function() {

        // PEGAR DATA INICIAL DO CAPEANTE
        let dataInicConta = document.getElementById("data_inicial_capeante");
        dataInicContaV = dataInicConta.value;
        let dataInt = document.getElementById("data_intern_int");
        dataIntV = dataInt.value;
        // PEGA DATA DA ULTIMA PARCIAL

        let dataParcial = parcial_date;
        var dif2 = false
        if (dataParcial) {
            dataParcialV = dataParcial.value;
            dataParcialDao = new Date(dataParcialV);
            var dif2 = dataInicContaV < dataParcialV;
        }

        dataInicContaDao = new Date(dataInicContaV);
        dataIntVDao = new Date(dataIntV);

        var dif1 = dataInicContaV < dataIntV; // ver se a data inicial da prorrogacao é menor que a data da internacao
        var divMsg = document.querySelector(".notif1");

        if (dif2 | dif1) {
            divMsg.style.display = "block";
            dataInicConta.style.borderColor = "red";
            dataInicConta.value = "";
            dataInicConta.focus();

        } else {
            divMsg.style.display = "none";
            dataInicConta.style.borderColor = "gray";

        }


    })
    // ********* FIM VERIFICAR DATA INICIAL ********// 




// ********** INICIO VERIFICAR DATA FINAL ********  // 

// notificacao de data final menor q data internacao e data inicial
data_final_conta.addEventListener("blur", function() {
        // notificacao de data final menor q data internacao e maior q data inicial
        let dataInicConta = document.getElementById("data_inicial_capeante");
        dataInicContaV = dataInicConta.value;

        let dataInt = document.getElementById("data_intern_int");
        dataIntV = dataInt.value;

        let dataFinalConta = document.getElementById("data_final_capeante");
        dataFinalContaV = dataFinalConta.value;

        let diarias = document.getElementById("diarias_capeante");
        diariasV = diarias.value;

        dataIntVDao = new Date(dataIntV);
        dataInicContaDao = new Date(dataInicContaV);
        dataFinalContaDao = new Date(dataFinalContaV);

        var diaInt = (dataIntVDao.getTime());
        var diaIni = (dataInicContaDao.getTime());
        var diaFin = (dataFinalContaDao.getTime());

        var dif1 = diaIni >= diaInt; // ver se a data inicial da prorrogacao é menor que a data da internacao
        var dif2 = diaIni < diaFin; // ver se a data inicial da prorrogacao é menor que a data final da prorrogacao

        var divMsg2 = document.querySelector(".notif2");

        if (dif2 === false || dif1 === false) {
            divMsg2.style.display = "block";
            dataFinalConta.style.borderColor = "red";
            dataFinalConta.value = "";
            dataFinalConta.focus();

        } else {
            divMsg2.style.display = "none";
            dataFinalConta.style.borderColor = "gray";
            diarias.value = (diaFin - diaIni) / (24 * 60 * 60 * 1000);
        }
    })
    // ********** FIM VERIFICAR DATA FINAL ********  //