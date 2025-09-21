function callPHP(idCapeante) {
    const data = new URLSearchParams();
    data.append('id', idCapeante);
    console.log("call php")
    // Make a POST request to the PHP script
    fetch('process_capeante_imp.php', {
        method: 'POST',
        body: data
    })
        .then(response => response.text())
        .then(responseText => {
            // Update the page with the response from PHP
            console.log(responseText)
        })
        .catch(error => console.error('Error:', error));
}

function callValidarCapeante(idCapeante) {
    const data = new URLSearchParams();
    data.append('id', idCapeante);
    console.log("call php")
    // Make a POST request to the PHP script
    fetch('process_validar_capeante.php', {
        method: 'POST',
        body: data
    })
        .then(response => response.text())
        .then(responseText => {
            // Update the page with the response from PHP
            console.log(responseText)
        })
        .catch(error => console.error('Error:', error));
}

function generatePdf() {
    console.log("teste imprimir 2");
    const content = document.querySelector('#content');
    const idCapeante = document.querySelector('#id-capeante')?.innerText;
    const nomeHosp = document.querySelector('#nomeHosp')?.innerText;

    // console.log("Content:", content);
    // console.log("ID Capeante:", idCapeante);
    // console.log("Nome Hosp:", nomeHosp);

    if (!content || !idCapeante || !nomeHosp) {
        console.error("Um ou mais elementos não foram encontrados no DOM");
        return;
    }

    const options = {
        margin: [20, 10, 10, 10],
        filename: nomeHosp + "-CapeanteNo-" + idCapeante + ".pdf",
        html2canvas: { scale: 1.5 },
        jsPDF: { unit: "mm", format: "a4", orientation: 'portrait' }
    };

    html2pdf().set(options).from(content).save();

    callPHP(idCapeante);


}