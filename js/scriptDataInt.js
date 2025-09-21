// ****************************************** //
// PEGAR DADOS DOS INPUTS //    
// ****************************************** //

let dataInt = document.getElementById("data_intern_int");

// ********** INICIO VERIFICAR DATA INTERNACAO ********  // 
dataInt.addEventListener("blur", function() {

        // PEGAR DATA INICIAL DO CAPEANTE

        let dataInt = document.getElementById("data_intern_int");
        let dataVis = document.getElementById("data_visita_int");

        dataIntV = dataInt.value;
        dataVisV = dataVis.value;

        dataIntVDao = new Date(dataIntV);
        dataVisDao = new Date(dataVisV);

        var dataIntV = (dataIntVDao.getTime());
        var dataVisV = (dataVisDao.getTime());

        var dif1 = dataIntV > dataVisV; // ver se a data inicial da prorrogacao é menor que a data da internacao
        var divMsg = document.querySelector("#notif-input");

        if (dif1 === true) {
            divMsg.style.display = "block";
            dataInt.style.borderColor = "red";
            dataInt.value = "";
            dataInt.focus();

        } else {
            divMsg.style.display = "none";
            dataInt.style.borderColor = "gray";

        }

    })
    // ********* FIM VERIFICAR DATA INICIAL ********//