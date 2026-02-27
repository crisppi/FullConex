function apareceOpcoes() {
    $('#deletar-btn').val('nao');
    let mudancaStatus = ($('#deletar-btn').val())
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'block';
}

function deletar() {
    $('#deletar-btn').val('ok');
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';
    let mudancaStatus = ($('#deletar-btn').val())
    window.location = "<?= $BASE_URL ?>del_acomodacao.php?id_acomodacao=<?= $id_acomodacao ?>";
};

function cancelar() {
    let idAcoes = (document.getElementById('id-confirmacao'));
    idAcoes.style.display = 'none';

};

src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";