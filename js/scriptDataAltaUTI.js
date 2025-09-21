// ****************************************** //
// PEGAR DADOS DOS INPUTS //    
// ****************************************** //

let dataInt = document.getElementById("data_intern_int");
let data_alta_UTI = document.getElementById("data_alta_uti");

// ****************************************** //
// METODO DE VERIFICAR DATAS DA ALTA COMPARADO COM DATA INTERNACAO //
// ****************************************** //

// ********** INICIO VERIFICAR DATA ALTA ********  // 
data_alta_UTI.addEventListener("blur", function() {

        let data_alta_UTI = document.getElementById("data_alta_uti");
        data_alta_UTI_intV = data_alta_UTI.value;

        let dataInt = document.getElementById("data_intern_int");
        dataIntV = dataInt.value;

        dataIntDao = new Date(dataIntV);
        dataAltaDao = new Date(data_alta_UTI_intV);

        var diaInt = (dataIntDao.getTime());
        console.log(diaInt);
        var diaAlta = (dataAltaDao.getTime());
        console.log(diaAlta);

        var dif2 = diaAlta < diaInt; // ver se a data inicial da prorrogacao é menor que a data da internacao
        console.log(dif2);

        var divMsg2 = document.querySelector("#notif-input2");

        if (dif2 === true) {
            divMsg2.style.display = "block";
            data_alta_UTI.style.borderColor = "red";
            data_alta_UTI.value = "";
            data_alta_UTI.focus();

        } else {
            divMsg2.style.display = "none";
            data_alta_UTI.style.borderColor = "gray";

        }

    })
    // ********* FIM VERIFICAR DATA ALTA HOSPITALAR ********//