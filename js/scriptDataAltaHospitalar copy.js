// ****************************************** //
// PEGAR DADOS DOS INPUTS //    
// ****************************************** //

let dataInt = document.getElementById("data_intern_int");
let data_alta = document.getElementById("data_alta_alt");

// ****************************************** //
// METODO DE VERIFICAR DATAS DA ALTA COMPARADO COM DATA INTERNACAO //
// ****************************************** //

// ********** INICIO VERIFICAR DATA ALTA ********  // 

data_alta.addEventListener("keypress", function(event) {
    divMsg.style.display = "none";
    divMsg.style.borderColor = "gray";

}); // limpar a notificacacao do glosa med
data_alta.addEventListener("blur", function() {

        let data_alta_alt = document.getElementById("data_alta_alt");
        data_alta_intV = data_alta_alt.value;

        let dataInt = document.getElementById("data_intern_int");
        dataIntV = dataInt.value;

        dataIntDao = new Date(dataIntV);
        dataAltaDao = new Date(data_alta_intV);

        var diaInt = (dataIntDao.getTime());
        var diaAlta = (dataAltaDao.getTime());

        var dif1 = diaAlta < diaInt; // ver se a data inicial da alta é menor que a data da internacao

        var divMsg = document.querySelector("#notif-input");

        if (dif1 === true) {
            divMsg.style.display = "block";
            data_alta_alt.style.borderColor = "red";
            data_alta_alt.value = "";
            data_alta_alt.focus();

        } else {
            divMsg.style.display = "none";
            data_alta_alt.style.borderColor = "gray";

        }

    })
    // ********* FIM VERIFICAR DATA ALTA HOSPITALAR ********//