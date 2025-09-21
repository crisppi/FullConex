// ****************************************** //
// PEGAR DADOS DOS INPUTS //    
// ****************************************** //

let dataIntP = document.getElementById("data_intern_int");

let dataPro = document.getElementById("prorrog1_ini_pror");
let dataProF = document.getElementById("prorrog1_fim_pror");

let dataPro2 = document.getElementById("prorrog2_ini_pror");
let dataProF2 = document.getElementById("prorrog2_fim_pror");

let dataPro3 = document.getElementById("prorrog3_ini_pror");
let dataProF3 = document.getElementById("prorrog3_fim_pror");

// Função para validar as datas
function validarDatas(dataInicio, dataFim, divMsg, diariasDiv = null, diariasInput = null) {
    let dataInicioV = dataInicio.value;
    let dataFimV = dataFim.value;

    let dataInicioDao = new Date(dataInicioV);
    let dataFimDao = new Date(dataFimV);

    // Verificar se as datas são válidas
    if (isNaN(dataInicioDao) || isNaN(dataFimDao)) {
        divMsg.textContent = "Data inválida.";
        divMsg.style.display = "block";
        dataFim.style.borderColor = "red";
        dataFim.value = "";
        dataFim.focus();
        return;
    }

    var dataInicioMs = dataInicioDao.getTime();
    var dataFimMs = dataFimDao.getTime();

    var dif = dataInicioMs > dataFimMs; // Ver se a data inicial é maior que a data final

    if (dif) {
        divMsg.textContent = "A data final não pode ser anterior à data inicial.";
        divMsg.style.display = "block";
        dataFim.style.borderColor = "red";
        dataFim.value = "";
        dataFim.focus();
    } else {
        divMsg.style.display = "none";
        dataFim.style.borderColor = "#d3d3d3";

        if (diariasDiv && diariasInput) {
            var diferencaEmMilissegundos = dataFimMs - dataInicioMs;
            var diferencaEmDias = diferencaEmMilissegundos / 1000 / 60 / 60 / 24;

            diariasDiv.style.display = "block";
            diariasInput.value = diferencaEmDias;
        }
    }
}

// ********** INICIO VERIFICACAO DATA INTERNACAO E DATA INICIAL PRORROGACAO 1 ********  // 
dataPro.addEventListener("blur", function() {
    let divMsg1 = document.querySelector("#notif-input1");
    validarDatas(dataIntP, dataPro, divMsg1);
});

// ********** INICIO VERIFICACAO DATA FINAL PRORROGACAO 1 COM DATA INICIAL PRORROGACAO 1 ********  // 
dataProF.addEventListener("blur", function() {
    let divMsg2 = document.querySelector("#notif-input2");
    let diarias1 = document.getElementById("diarias_1");
    let diarias_Div_1 = document.getElementById("div_diarias_1");
    validarDatas(dataPro, dataProF, divMsg2, diarias_Div_1, diarias1);
});

// ********** INICIO VERIFICACAO DATA INICIAL PRORROGACAO 2 COM DATA FINAL PRORROGACAO 1 ********  // 
dataPro2.addEventListener("blur", function() {
    let divMsg3 = document.querySelector("#notif-input3");
    validarDatas(dataProF, dataPro2, divMsg3);
});

// ********** INICIO VERIFICACAO DATA FINAL PRORROGACAO 2 COM DATA INICIAL PRORROGACAO 2 ********  // 
dataProF2.addEventListener("blur", function() {
    let divMsg4 = document.querySelector("#notif-input4");
    let diarias2 = document.getElementById("diarias_2");
    let diarias_Div_2 = document.getElementById("div_diarias_2");
    validarDatas(dataPro2, dataProF2, divMsg4, diarias_Div_2, diarias2);
});

// ********** INICIO VERIFICACAO DATA INICIAL PRORROGACAO 3 COM DATA FINAL PRORROGACAO 2 ********  // 
dataPro3.addEventListener("blur", function() {
    let divMsg5 = document.querySelector("#notif-input5");
    validarDatas(dataProF2, dataPro3, divMsg5);
});

// ********** INICIO VERIFICACAO DATA FINAL PRORROGACAO 3 COM DATA INICIAL PRORROGACAO 3 ********  // 
dataProF3.addEventListener("blur", function() {
    let divMsg6 = document.querySelector("#notif-input6");
    let diarias3 = document.getElementById("diarias_3");
    let diarias_Div_3 = document.getElementById("div_diarias_3");
    validarDatas(dataPro3, dataProF3, divMsg6, diarias_Div_3, diarias3);
});