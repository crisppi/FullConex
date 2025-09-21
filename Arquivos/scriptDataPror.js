// ****************************************** //
// PEGAR DADOS DOS INPUTS //    
// ****************************************** //

var currentDate = new Date();
console.log(currentDate)
let dataIntP = document.getElementById("data_intern_int");

let dataPro = document.getElementById("prorrog1_ini_pror");
let dataProF = document.getElementById("prorrog1_fim_pror");

let dataPro2 = document.getElementById("prorrog2_ini_pror");
let dataProF2 = document.getElementById("prorrog2_fim_pror");

let dataPro3 = document.getElementById("prorrog3_ini_pror");
let dataProF3 = document.getElementById("prorrog3_fim_pror");

// ********** INICIO VERIFICACAO DATA INTERNACAO E DATA INICIAL PRORROGACAO 1********  // 
dataPro.addEventListener("change", function() {

        // let dataInt = document.getElementById("data_intern_int");
        let dataPro = document.getElementById("prorrog1_ini_pror");
        var currentDate = new Date();

        dataIntV1 = dataIntP.value;
        dataProV = dataPro.value;

        dataIntVDao = new Date(dataIntV1);
        dataProDao = new Date(dataProV);

        var dataIntV1 = (dataIntVDao.getTime());
        var dataProV = (dataProDao.getTime());

        var dif11 = dataIntV1 > dataProV; // ver se a data inicial da prorrogacao é menor que a data da internacao

        var divMsg1 = document.querySelector("#notif-input1");

        if (dataIntV1 > dataProV || dataProV > currentDate) { // se data da prorrog for maior q internacao - msg de erro
            setTimeout(function() {
                divMsg1.style.display = "block";
            }, 50);
            dataPro.style.borderColor = "red";
            dataPro.value = "";
            dataPro.focus();

        } else {
            divMsg1.style.display = "none";
            dataPro.style.borderColor = "#d3d3d3";

        }

    })
    // ********** FIM VERIFICACAO DATA INTERNACAO E DATA INICIAL PRORROGACAO 1  ********  // 

// ********** INICIO VERIFICACAO DATA FINAL PRORROGACAO 1 COM DATA INICIAL PRORROGACAO 1 ********  // 
dataProF.addEventListener("change", function() {

        let dataPro2 = document.getElementById("prorrog1_ini_pror");
        let dataProF = document.getElementById("prorrog1_fim_pror");

        dataProV = dataPro2.value;
        dataProVF = dataProF.value;

        dataProDao = new Date(dataProV);
        dataProFDao = new Date(dataProVF);

        var dataProV = (dataProDao.getTime());
        var dataProVF = (dataProFDao.getTime());

        var dif12 = dataProV < dataProVF; // ver se a data inicial da prorrogacao é menor que a data da internacao
        var diariasV = dataProVF - dataProV;
        var divMsg2 = document.querySelector("#notif-input2");

        if (dataProV > dataProVF || dataProV > currentDate) {
            divMsg2.style.display = "block";
            dataProF.style.borderColor = "red";
            dataProF.value = "";
            dataProF.focus();

        } else {
            divMsg2.style.display = "none";
            dataProF.style.borderColor = "#d3d3d3";
            let diarias1 = document.getElementById("diarias_1");
            let diarias_Div_1 = document.getElementById("div_diarias_1");

            var diferencaEmMilissegundos = dataProVF - dataProV;
            var diferencaEmDias = diferencaEmMilissegundos / 1000 / 60 / 60 / 24

            diarias_Div_1.style.display = "block"
            diarias1.value = diferencaEmDias;

        }
    })
    // ********** FIM VERIFICACAO DATA FINAL PRORROGACAO 1 COM DATA INICIAL PRORROGACAO 1 ********  //

// ********** INICIO VERIFICACAO DATA INICIAL PRORROGACAO 2 COM DATA FINAL PRORROGACAO 1 ********  // 
dataPro2.addEventListener("change", function() {

        let dataProFb = document.getElementById("prorrog1_fim_pror");
        let dataPro2 = document.getElementById("prorrog2_ini_pror");

        dataProFV2 = dataProFb.value;
        dataPro2V = dataPro2.value;

        dataProFV2Dao = new Date(dataProFV2);
        dataPro2Dao = new Date(dataPro2V);

        var dataProVF2 = (dataProFV2Dao.getTime());
        var dataProV2 = (dataPro2Dao.getTime());

        var dif13 = dataProV2 < dataProVF2; // ver se a data inicial da prorrogacao2 é menor que a data da prorrogacao1

        var divMsg3 = document.querySelector("#notif-input3");

        if (dif13 === true) {
            divMsg3.style.display = "block";
            dataPro2.style.borderColor = "red";
            dataPro2.value = "";
            dataPro2.focus();

        } else {
            divMsg3.style.display = "none";
            dataPro2.style.borderColor = "#d3d3d3";

        }
    })
    // ********** FIM VERIFICACAO DATA INICIAL PRORROGACAO 2 COM DATA FINAL PRORROGACAO 1 ********  //

// ********** INICIO VERIFICACAO DATA FINAL PRORROGACAO 2 COM DATA INICIAL PRORROGACAO 2 ********  // 
dataProF2.addEventListener("change", function() {

        let dataProF2 = document.getElementById("prorrog2_fim_pror");
        let dataPro2B = document.getElementById("prorrog2_ini_pror");

        dataProFV2B = dataProF2.value;
        dataPro2VB = dataPro2B.value;

        dataProFV2BDao = new Date(dataProFV2B);
        dataPro2BDao = new Date(dataPro2VB);

        var dataProVF2B = (dataProFV2BDao.getTime());
        var dataProV2B = (dataPro2BDao.getTime());

        var dif14 = dataProV2B < dataProVF2B; // ver se a data inicial da prorrogacao2 é menor que a data da prorrogacao1
        var diarias2V = dataProVF2B - dataProV2B;

        var divMsg4 = document.querySelector("#notif-input4");

        if (dif14 === false) {
            divMsg4.style.display = "block";
            dataProF2.style.borderColor = "red";
            dataProF2.value = "";
            dataProF2.focus();

        } else {
            divMsg4.style.display = "none";
            dataProF2.style.borderColor = "#d3d3d3";

            let diarias2 = document.getElementById("diarias_2");
            let diarias_Div_2 = document.getElementById("div_diarias_2");

            var diferencaEmMilissegundos2 = dataProVF2B - dataProV2B;
            var diferencaEmDias2 = diferencaEmMilissegundos2 / 1000 / 60 / 60 / 24
            diarias_Div_2.style.display = "block"
            diarias2.value = diferencaEmDias2;

        }
    })
    // ********** FIM VERIFICACAO DATA FINAL PRORROGACAO 2 COM DATA INICIAL PRORROGACAO 2 ********  //

// ********** INICIO VERIFICACAO DATA INICIAL PRORROGACAO 3 COM DATA FINAL PRORROGACAO 2 ********  // 
dataPro3.addEventListener("blur", function() {

        let dataProFc = document.getElementById("prorrog2_fim_pror");
        let dataPro3 = document.getElementById("prorrog3_ini_pror");

        dataProFV2b = dataProFc.value;
        dataPro3V = dataPro3.value;

        dataProFV2bDao = new Date(dataProFV2b);
        dataPro3Dao = new Date(dataPro3V);

        var dataProVF2b = (dataProFV2bDao.getTime());
        var dataProV3 = (dataPro3Dao.getTime());

        var dif15 = dataProV3 < dataProVF2b; // ver se a data inicial da prorrogacao2 é menor que a data da prorrogacao1

        var divMsg5 = document.querySelector("#notif-input5");

        if (dif15 === true) {
            divMsg5.style.display = "block";
            dataPro3.style.borderColor = "red";
            dataPro3.value = "";
            dataPro3.focus();

        } else {
            divMsg5.style.display = "none";
            dataPro3.style.borderColor = "#d3d3d3";

            let diarias2 = document.getElementById("diarias_2");
            let diarias_Div_2 = document.getElementById("div_diarias_2");

            var diferencaEmMilissegundos2 = dataProVF2B - dataProV2B;
            var diferencaEmDias2 = diferencaEmMilissegundos2 / 1000 / 60 / 60 / 24
            diarias_Div_2.style.display = "block"
            diarias2.value = diferencaEmDias2;

        }
    })
    // ********** FIM VERIFICACAO DATA INICIAL PRORROGACAO 3 COM DATA FINAL PRORROGACAO 2 ********  //

// ********** INICIO VERIFICACAO DATA FINAL PRORROGACAO 3 COM DATA INICIAL PRORROGACAO 3 ********  // 
dataProF3.addEventListener("change", function() {

        let dataProF3 = document.getElementById("prorrog3_fim_pror");
        let dataPro3B = document.getElementById("prorrog3_ini_pror");

        dataProFV3B = dataProF3.value;
        dataPro3VB = dataPro3B.value;

        dataProFV3BDao = new Date(dataProFV3B);
        dataPro3BDao = new Date(dataPro3VB);

        var dataProVF3B = (dataProFV3BDao.getTime());
        var dataProV3B = (dataPro3BDao.getTime());

        var dif16 = dataProV3B < dataProVF3B; // ver se a data inicial da prorrogacao2 é menor que a data da prorrogacao1

        var divMsg6 = document.querySelector("#notif-input6");

        if (dif16 === false) {
            divMsg6.style.display = "block";
            dataProF3.style.borderColor = "red";
            dataProF3.value = "";
            dataProF3.focus();

        } else {
            divMsg6.style.display = "none";
            dataProF3.style.borderColor = "#d3d3d3";


            let diarias3 = document.getElementById("diarias_3");
            let diarias_Div_3 = document.getElementById("div_diarias_3");

            var diferencaEmMilissegundos3 = dataProVF3B - dataProV3B;
            var diferencaEmDias3 = diferencaEmMilissegundos3 / 1000 / 60 / 60 / 24
            diarias_Div_3.style.display = "block"
            diarias3.value = diferencaEmDias3;

        }
    })
    // ********** FIM VERIFICACAO DATA FINAL PRORROGACAO 2 COM DATA INICIAL PRORROGACAO 3 ********  //