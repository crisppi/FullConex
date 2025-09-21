<div class="row">
    <h2 class="page-title">Cadastrar internação</h2>
    <p class="page-description">Adicione informações sobre a internação</p>
    <?php
    $limit = 1;
    $order = "id_internacao desc";
    $where = null;
    $internacaoUltima = $internacaoList->selectAllInternacao($where, $order, $limit);
    ?>
    <div id="view-contact-container" class="container-fluid" style="align-items:center">
        <hr>
        <span class="card-title bold" style="font-weight: 500; margin:0px 5px 0px 20px">Hospital:</span>
        <span class="card-title bold" style=" font-weight: 800; margin:0px 10px 0px 0px"><?= $internacaoUltima['0']['nome_hosp'] ?></span>
        <span style="font-weight: 500; margin:0px 5px 0px 80px">Paciente:</span>
        <span style=" font-weight: 800; margin:0px 10px 0px 0px"><?= $internacaoUltima['0']['nome_pac'] ?></span>
        <span style="font-weight: 500; margin:0px 5px 0px 80px">Data internação:</span>

        <span style="font-weight: 800; margin:0px 80px 0px 0px"><?= date("d/m/Y", strtotime($internacaoUltima['0']['data_intern_int'])); ?></span>
        <span style="font-weight: 500; margin:0px 5px 0px 40px ">Internação:</span>
        <span style="font-weight: 500; margin:0px 80px 0px 5px "><?= $internacaoUltima['0']['id_internacao'] ?></span>
        <hr>
    </div>

</div>
<script src="js/scriptDataInt.js"></script>
<script type="text/javascript">
    // JS PARA APARECER REL EVENTO ADVERSO
    var select_evento = document.querySelector('#evento_adverso_ges');

    select_evento.addEventListener('change', setEvento);

    function setEvento() {
        var choice_evento = select_evento.value;

        if (choice_evento === 'Sim') {

            if (div_rel_evento.style.display === "none") {
                div_rel_evento.style.display = "block";
                div_tipo_evento.style.display = "block";
            }

        }
        if (choice_evento === 'Não') {

            if (div_rel_evento.style.display === "block") {
                div_rel_evento.style.display = "none";
                div_tipo_evento.style.display = "none";
            }
        }
    }
    // JS PARA APARECER REL OPME
    var select_opme = document.querySelector('#opme_ges');

    select_opme.addEventListener('change', setOpme);

    function setOpme() {
        var choice_opme = select_opme.value;

        if (choice_opme === 'Sim') {

            if (div_rel_opme.style.display === "none") {
                div_rel_opme.style.display = "block";
            }

        }
        if (choice_opme === 'Não') {

            if (div_rel_opme.style.display === "block") {
                div_rel_opme.style.display = "none";
            }
        }
    }
    // JS PARA APARECER REL DESOSPITALIZACAO
    var select_desospitalizacao = document.querySelector('#desospitalizacao_ges');

    select_desospitalizacao.addEventListener('change', setdesospitalizacao);

    function setdesospitalizacao() {
        var choice_desospitalizacao = select_desospitalizacao.value;

        if (choice_desospitalizacao === 'Sim') {

            if (div_rel_desospitalizacao.style.display === "none") {
                div_rel_desospitalizacao.style.display = "block";
            }

        }
        if (choice_desospitalizacao === 'Não') {

            if (div_rel_desospitalizacao.style.display === "block") {
                div_rel_desospitalizacao.style.display = "none";
            }
        }
    }
    // JS PARA APARECER REL HOME CARE
    var select_home_care = document.querySelector('#home_care_ges');

    select_home_care.addEventListener('change', sethome_care);

    function sethome_care() {
        var choice_home_care = select_home_care.value;

        if (choice_home_care === 'Sim') {

            if (div_rel_home_care.style.display === "none") {
                div_rel_home_care.style.display = "block";
            }

        }
        if (choice_home_care === 'Não') {

            if (div_rel_home_care.style.display === "block") {
                div_rel_home_care.style.display = "none";
            }
        }
    }
    // JS PARA APARECER REL ALTO CUSTO
    var select_alto_custo = document.querySelector('#alto_custo_ges');

    select_alto_custo.addEventListener('change', setalto_custo);

    function setalto_custo() {
        var choice_alto_custo = select_alto_custo.value;

        if (choice_alto_custo === 'Sim') {

            if (div_rel_alto_custo.style.display === "none") {
                div_rel_alto_custo.style.display = "block";
            }

        }
        if (choice_alto_custo === 'Não') {

            if (div_rel_alto_custo.style.display === "block") {
                div_rel_alto_custo.style.display = "none";
            }
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>