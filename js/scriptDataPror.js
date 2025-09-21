// ****************************************** //
// PEGAR DADOS DOS INPUTS //    
// ****************************************** //

const currentDate = new Date();

const dataIntP = document.getElementById("data_intern_int");
const dataPro = document.getElementById("prorrog1_ini_pror");
const dataProF = document.getElementById("prorrog1_fim_pror");
const dataPro2 = document.getElementById("prorrog2_ini_pror");
const dataProF2 = document.getElementById("prorrog2_fim_pror");
const dataPro3 = document.getElementById("prorrog3_ini_pror");
const dataProF3 = document.getElementById("prorrog3_fim_pror");

// Função para verificar se uma data é válida e não está no futuro
function isDateValid(date, referenceDate = currentDate) {
    const dateObj = new Date(date);
    return dateObj.getTime() <= referenceDate.getTime();
}

// Função para exibir mensagem de erro
function showError(element, messageElement) {
    messageElement.style.display = "block";
    element.style.borderColor = "red";
    element.value = "";
    element.focus();
}

// Função para ocultar mensagem de erro
function hideError(element, messageElement) {
    messageElement.style.display = "none";
    element.style.borderColor = "#d3d3d3";
}

// Função para calcular a diferença em dias entre duas datas
function calculateDaysDifference(startDate, endDate) {
    const diffTime = new Date(endDate).getTime() - new Date(startDate).getTime();
    return diffTime / (1000 * 60 * 60 * 24);
}

// Função para exibir pop-up se dias forem maiores que 15
function checkDaysLimit(days) {
    if (days > 15) {
        return confirm("Deseja prorrogar mais do que 15 diárias?");
    }
    return true;
}

// Verificação da data inicial da prorrogação 1 com a data da internação
dataPro.addEventListener("change", function () {
    const dataIntV = dataIntP.value;
    const dataProV = dataPro.value;
    const divMsg1 = document.querySelector("#notif-input1");

    if (!isDateValid(dataProV) || new Date(dataIntV).getTime() > new Date(dataProV).getTime()) {
        showError(dataPro, divMsg1);
    } else {
        hideError(dataPro, divMsg1);
    }
});

// Verificação da data final da prorrogação 1 com a data inicial da prorrogação 1
dataProF.addEventListener("change", function () {
    const dataProV = dataPro.value;
    const dataProFV = dataProF.value;
    const divMsg2 = document.querySelector("#notif-input2");

    if (!isDateValid(dataProFV) || new Date(dataProV).getTime() > new Date(dataProFV).getTime()) {
        showError(dataProF, divMsg2);
    } else {
        hideError(dataProF, divMsg2);

        const diarias1 = document.getElementById("diarias_1");
        const diariasDiv1 = document.getElementById("div_diarias_1");
        const daysDifference = calculateDaysDifference(dataProV, dataProFV);

        if (checkDaysLimit(daysDifference)) {
            diariasDiv1.style.display = "block";
            diarias1.value = daysDifference;
        } else {
            showError(dataProF, divMsg2);
        }
    }
});

// Verificação da data inicial da prorrogação 2 com a data final da prorrogação 1
dataPro2.addEventListener("change", function () {
    const dataProFV2 = dataProF.value;
    const dataPro2V = dataPro2.value;
    const divMsg3 = document.querySelector("#notif-input3");

    if (new Date(dataPro2V).getTime() < new Date(dataProFV2).getTime()) {
        showError(dataPro2, divMsg3);
    } else {
        hideError(dataPro2, divMsg3);
    }
});

// Verificação da data final da prorrogação 2 com a data inicial da prorrogação 2
dataProF2.addEventListener("change", function () {
    const dataPro2V = dataPro2.value;
    const dataProF2V = dataProF2.value;
    const divMsg4 = document.querySelector("#notif-input4");

    if (!isDateValid(dataProF2V) || new Date(dataPro2V).getTime() > new Date(dataProF2V).getTime()) {
        showError(dataProF2, divMsg4);
    } else {
        hideError(dataProF2, divMsg4);

        const diarias2 = document.getElementById("diarias_2");
        const diariasDiv2 = document.getElementById("div_diarias_2");
        const daysDifference = calculateDaysDifference(dataPro2V, dataProF2V);

        if (checkDaysLimit(daysDifference)) {
            diariasDiv2.style.display = "block";
            diarias2.value = daysDifference;
        } else {
            showError(dataProF2, divMsg4);
        }
    }
});

// Verificação da data inicial da prorrogação 3 com a data final da prorrogação 2
dataPro3.addEventListener("change", function () {
    const dataProF2V = dataProF2.value;
    const dataPro3V = dataPro3.value;
    const divMsg5 = document.querySelector("#notif-input5");

    if (new Date(dataPro3V).getTime() < new Date(dataProF2V).getTime()) {
        showError(dataPro3, divMsg5);
    } else {
        hideError(dataPro3, divMsg5);
    }
});

// Verificação da data final da prorrogação 3 com a data inicial da prorrogação 3
dataProF3.addEventListener("change", function () {
    const dataPro3V = dataPro3.value;
    const dataProF3V = dataProF3.value;
    const divMsg6 = document.querySelector("#notif-input6");

    if (!isDateValid(dataProF3V) || new Date(dataPro3V).getTime() > new Date(dataProF3V).getTime()) {
        showError(dataProF3, divMsg6);
    } else {
        hideError(dataProF3, divMsg6);

        const diarias3 = document.getElementById("diarias_3");
        const diariasDiv3 = document.getElementById("div_diarias_3");
        const daysDifference = calculateDaysDifference(dataPro3V, dataProF3V);

        if (checkDaysLimit(daysDifference)) {
            diariasDiv3.style.display = "block";
            diarias3.value = daysDifference;
        } else {
            showError(dataProF3, divMsg6);
        }
    }
});