function apareceOpcoes() {
    $('#deletar-btn').val('nao');
    let mudancaStatus = ($('#deletar-btn').val())
    console.log(mudancaStatus);
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'block';
}

function deletar() {
    $('#deletar-btn').val('ok');
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';
    let mudancaStatus = ($('#deletar-btn').val())
    console.log(mudancaStatus);
    window.location = "<?= $BASE_URL ?>del_patologia.php?id_patologia=<?= $id_patologia ?>";
};

function cancelar() {
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';
    console.log("chegou no cancelar");

};
src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";