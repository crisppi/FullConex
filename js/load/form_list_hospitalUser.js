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
    window.location = "<?= $BASE_URL ?>del_evento.php?id_evento=<?= $id_evento ?>";
};

function cancelar() {
    let idAcoes = (document.getElementById('id-confirmacao'));
    console.log("chegou no cancelar");
    idAcoes.style.display = 'none';

};