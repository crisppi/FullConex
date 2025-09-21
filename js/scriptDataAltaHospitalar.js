// limpar a notificacacao do glosa med
function checkDataAlta() {
    console.log("Teste alyta")
    let dataInt = document.getElementById("data_intern_int");
    let data_alta = document.getElementById("data_alta_alt");
    let data_alta_alt = document.getElementById("data_alta_alt");
    data_alta_intV = data_alta_alt.value;

    dataInt = document.getElementById("data_intern_int");
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

}
// ********* FIM VERIFICAR DATA ALTA HOSPITALAR ********//