// pegar valores dos inputs dos valores

let inputDiarias = document.getElementById("valor_diarias");
let inputMat = document.getElementById("valor_matmed");
let inputOxig = document.getElementById("valor_oxig");
let inputSadt = document.getElementById("valor_sadt");
let inputTaxa = document.getElementById("valor_taxa");
let inputHono = document.getElementById("valor_honorarios");

let inputGDiarias = document.getElementById("glosa_diaria");
let inputGoxi = document.getElementById("glosa_oxig");
let inputGmat = document.getElementById("glosa_matmed");
let inputGsadt = document.getElementById("glosa_sadt");
let inputGtaxa = document.getElementById("glosa_taxas");
let inputGhono = document.getElementById("glosa_honorarios");

let inputGenf = document.getElementById("valor_glosa_enf");
let inputGmed = document.getElementById("valor_glosa_med");

let inputApresentado = document.getElementById("valor_apresentado_capeante");
let inputApresentadoVal = document.getElementById("valor_apresentado_capeante").value;

let inputDesconto = document.getElementById("negociado_desconto_cap");
let inputDescontoV = document.getElementById("desconto_valor_cap");
// let inputDescontoVal = document.getElementById("desconto_valor_cap").value;



// validacao entrada valor apresentado 
inputApresentado.addEventListener("blur", function() {
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);
});


// *****************************//
// validacao entrada valor diarias 
inputDiarias.addEventListener("blur", function() {
    let notifDiarias = document.getElementById("notif-input-diarias");
    let inputDiarias = document.getElementById("valor_diarias");
    let inputDiariasVal = document.getElementById("valor_diarias").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputDiariasVal > inputApresentadoVal) {
        inputDiarias.style.borderColor = "red";
        inputDiarias.value = "";
        inputDiarias.focus();
        notifDiarias.style.display = "block";
    } else {
        notifDiarias.style.display = "none";
        inputDiarias.style.borderColor = "gray";

    }
});

function fDiarias() {
    let notifDiarias = document.getElementById("notif-input-Gdiarias");
    let inputDiarias = document.getElementById("valor_diarias");
    notifDiarias.style.display = "none";
    inputDiarias.style.borderColor = "gray";
}

// ****************************//
// validacao entrada valor Glosa diarias 
inputGDiarias.addEventListener("blur", function() {
    let notifGDiarias = document.getElementById("notif-input-Gdiarias");
    let inputGDiarias = document.getElementById("glosa_diaria");
    let inputGDiariasVal = document.getElementById("glosa_diaria").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputGDiariasVal > inputApresentadoVal) {
        inputGDiarias.style.borderColor = "red";
        inputGDiarias.value = "";
        inputGDiarias.focus();
        notifGDiarias.style.display = "block";
    } else {
        notifGDiarias.style.display = "none";
        inputGDiarias.style.borderColor = "gray";

    }
});

function fGDiarias() {
    let notifGDiarias = document.getElementById("notif-input-Gdiarias");
    let inputGDiarias = document.getElementById("glosa_diaria");
    notifGDiarias.style.display = "none";
    inputGDiarias.style.borderColor = "gray";
}

// *****************************//
// validacao entrada valor Mat 
inputMat.addEventListener("blur", function() {
    let notifMat = document.getElementById("notif-input-mat");
    let inputMat = document.getElementById("valor_matmed");
    let inputMatVal = document.getElementById("valor_matmed").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputMatVal > inputApresentadoVal) {
        inputMat.style.borderColor = "red";
        inputMat.value = "";
        inputMat.focus();
        notifMat.style.display = "block";
    } else {
        notifMat.style.display = "none";
        inputMat.style.borderColor = "gray";
    }
});

function fMatmed() {
    let notifMat = document.getElementById("notif-input-mat");
    let inputMat = document.getElementById("valor_matmed");
    notifMat.style.display = "none";
    inputMat.style.borderColor = "gray";
}
// *****************************//
// validacao entrada valor Glosa Mat Med
inputGmat.addEventListener("blur", function() {
    let notifGmat = document.getElementById("notif-input-Gmat");
    let inputGmat = document.getElementById("glosa_matmed");
    let inputGmatVal = document.getElementById("glosa_matmed").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputGmatVal > inputApresentadoVal) {
        inputGmat.style.borderColor = "red";
        inputGmat.value = "";
        inputGmat.focus();
        notifGmat.style.display = "block";
    } else {
        notifGmat.style.display = "none";
        inputGmat.style.borderColor = "gray";
    }
});

function fGmat() {
    let notifGmat = document.getElementById("notif-input-Gmat");
    let inputGmat = document.getElementById("glosa_matmed");
    notifGmat.style.display = "none";
    inputGmat.style.borderColor = "gray";
}

// *****************************//
// validacao entrada valor Oxig 
inputOxig.addEventListener("blur", function() {
    let notifOxig = document.getElementById("notif-input-oxig");
    let inputOxig = document.getElementById("valor_oxig");
    let inputOxigVal = document.getElementById("valor_oxig").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputOxigVal > inputApresentadoVal) {
        inputOxig.style.borderColor = "red";
        inputOxig.value = "";
        inputOxig.focus();
        notifOxig.style.display = "block";
    } else {
        notifOxig.style.display = "none";
        inputOxig.style.borderColor = "gray";
    }
});

function fOxig() {
    let notifOxig = document.getElementById("notif-input-oxig");
    let inputOxig = document.getElementById("valor_oxig");
    notifOxig.style.display = "none";
    inputOxig.style.borderColor = "gray";
}

// *****************************//
// validacao entrada Glosa Oxig //
inputGoxi.addEventListener("blur", function() {
    let notifGoxi = document.getElementById("notif-input-Goxi");
    let inputGoxi = document.getElementById("glosa_oxig");
    let inputGoxiVal = document.getElementById("glosa_oxig").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputGoxiVal > inputApresentadoVal) {
        inputGoxi.style.borderColor = "red";
        inputGoxi.value = "";
        inputGoxi.focus();
        notifGoxi.style.display = "block";
    } else {
        notifGoxi.style.display = "none";
        inputGoxi.style.borderColor = "gray";
    }
});

function fGoxi() {
    let notifGoxi = document.getElementById("notif-input-Goxi");
    let inputGoxi = document.getElementById("glosa_oxig");
    notifGoxi.style.display = "none";
    inputGoxi.style.borderColor = "gray";
}

// *****************************//
// validacao entrada valor SADT 
inputSadt.addEventListener("blur", function() {
    let notifSadt = document.getElementById("notif-input-sadt");
    let inputSadt = document.getElementById("valor_sadt");
    let inputSadtVal = document.getElementById("valor_sadt").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputSadtVal > inputApresentadoVal) {
        inputSadt.style.borderColor = "red";
        inputSadt.value = "";
        inputSadt.focus();
        notifSadt.style.display = "block";
    } else {
        notifSadt.style.display = "none";
        inputSadt.style.borderColor = "gray";
    }
});

function fSadt() {
    let notifSadt = document.getElementById("notif-input-sadt");
    let inputSadt = document.getElementById("valor_sadt");
    notifSadt.style.display = "none";
    inputSadt.style.borderColor = "gray";
}

// *****************************//
// validacao entrada glosa SADT 
inputGsadt.addEventListener("blur", function() {
    let notifGsadt = document.getElementById("notif-input-Gsadt");
    let inputGsadt = document.getElementById("glosa_sadt");
    let inputGsadtVal = document.getElementById("glosa_sadt").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputGsadtVal > inputApresentadoVal) {
        inputGsadt.style.borderColor = "red";
        inputGsadt.value = "";
        inputGsadt.focus();
        notifGsadt.style.display = "block";
    } else {
        notifGsadt.style.display = "none";
        inputGsadt.style.borderColor = "gray";
    }
});

function fGsadt() {
    let notifGsadt = document.getElementById("notif-input-Gsadt");
    let inputGsadt = document.getElementById("glosa_sadt");
    notifGsadt.style.display = "none";
    inputGsadt.style.borderColor = "gray";
}


// *****************************//
// validacao entrada glosa Taxa 
inputGtaxa.addEventListener("blur", function() {
    let notifGtaxa = document.getElementById("notif-input-Gtaxa");
    let inputGtaxa = document.getElementById("glosa_taxas");
    let inputGtaxaVal = document.getElementById("glosa_taxas").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputGtaxaVal > inputApresentadoVal) {
        inputGtaxa.style.borderColor = "red";
        inputGtaxa.value = "";
        inputGtaxa.focus();
        notifGtaxa.style.display = "block";
    } else {
        notifGtaxa.style.display = "none";
        inputGtaxa.style.borderColor = "gray";
    }
});

function fGtaxa() {
    let notifGtaxa = document.getElementById("notif-input-Gtaxa");
    let inputGtaxa = document.getElementById("glosa_taxas");
    notifGtaxa.style.display = "none";
    inputGtaxa.style.borderColor = "gray";
}

// *****************************//
// validacao entrada valor Taxas 
inputTaxa.addEventListener("blur", function() {
    let notifTaxa = document.getElementById("notif-input-taxa");
    let inputTaxa = document.getElementById("valor_taxa");
    let inputTaxaVal = document.getElementById("valor_taxa").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputTaxaVal > inputApresentadoVal) {
        inputTaxa.style.borderColor = "red";
        inputTaxa.value = "";
        inputTaxa.focus();
        notifTaxa.style.display = "block";
    } else {
        notifTaxa.style.display = "none";
        inputTaxa.style.borderColor = "gray";
    }
});

function fTaxa() {
    let notifTaxa = document.getElementById("notif-input-taxa");
    let inputTaxa = document.getElementById("valor_taxa");
    notifTaxa.style.display = "none";
    inputTaxa.style.borderColor = "gray";
}

// *****************************//
// validacao entrada valor Hono 
inputHono.addEventListener("blur", function() {
    let notifHono = document.getElementById("notif-input-hono");
    let inputHono = document.getElementById("valor_honorarios");
    let inputHonoVal = document.getElementById("valor_honorarios").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputHonoVal > inputApresentadoVal) {
        inputHono.style.borderColor = "red";
        inputHono.value = "";
        inputHono.focus();
        notifHono.style.display = "block";
    } else {
        notifHono.style.display = "none";
        inputHono.style.borderColor = "gray";
    }
});

function fHono() {
    let notifHono = document.getElementById("notif-input-hono");
    let inputHono = document.getElementById("valor_honorarios");
    notifHono.style.display = "none";
    inputHono.style.borderColor = "gray";
}
// *****************************//
// validacao entrada glosa Hono 
inputGhono.addEventListener("blur", function() {
    let notifGhono = document.getElementById("notif-input-Ghono");
    let inputGhono = document.getElementById("glosa_honorarios");
    let inputGhonoVal = document.getElementById("glosa_honorarios").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputGhonoVal > inputApresentadoVal) {
        inputGhono.style.borderColor = "red";
        inputGhono.value = "";
        inputGhono.focus();
        notifGhono.style.display = "block";
    } else {
        notifGhono.style.display = "none";
        inputGhono.style.borderColor = "gray";
    }
});

function fGhono() {
    let notifGhono = document.getElementById("notif-input-Ghono");
    let inputGhono = document.getElementById("glosa_honorarios");
    notifGhono.style.display = "none";
    inputGhono.style.borderColor = "gray";
}

// *****************************//
// validacao entrada glosa Enf

inputGenf.addEventListener("blur", function() {
    let notifGenf = document.getElementById("notif-input-Genf");
    let inputGenf = document.getElementById("valor_glosa_enf");
    let inputGenfVal = document.getElementById("valor_glosa_enf").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputGenfVal > inputApresentadoVal) {
        inputGenf.style.borderColor = "red";
        inputGenf.value = "";
        inputGenf.focus();
        notifGenf.style.display = "block";
    } else {
        notifGenf.style.display = "none";
        inputGenf.style.borderColor = "gray";
    }
});

function fGenf() {
    let notifGenf = document.getElementById("notif-input-Genf");
    let inputGenf = document.getElementById("valor_glosa_enf");
    notifGenf.style.display = "none";
    inputGenf.style.borderColor = "gray";
}

// *****************************//
// validacao entrada glosa Med

inputGmed.addEventListener("blur", function() {
    let notifGmed = document.getElementById("notif-input-Gmed");
    let inputGmed = document.getElementById("valor_glosa_med");
    let inputGmedVal = document.getElementById("valor_glosa_med").value;
    let inputApresentadoVal = (document.getElementById("valor_apresentado_capeante").value);

    if (inputGmedVal > inputApresentadoVal) {
        inputGmed.style.borderColor = "red";
        inputGmed.value = "";
        inputGmed.focus();
        notifGmed.style.display = "block";
    } else {
        notifGmed.style.display = "none";
        inputGmed.style.borderColor = "gray";
    }
});

function fGmed() {
    let notifGmed = document.getElementById("notif-input-Gmed");
    let inputGmed = document.getElementById("valor_glosa_med");
    notifGmed.style.display = "none";
    inputGmed.style.borderColor = "gray";
}