<?php

require_once("templates/header.php");

require_once("models/message.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/patologia.php");
include_once("dao/patologiaDao.php");

include_once("models/paciente.php");
include_once("dao/pacienteDao.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("models/capeante.php");
include_once("dao/capeanteDao.php");

include_once("models/pagination.php");

$internacao_geral = new internacaoDAO($conn, $BASE_URL);
$internacaos = $internacao_geral->findGeral();

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$capeante_geral = new capeanteDAO($conn, $BASE_URL);
$capeante = $capeante_geral->findGeral($limite, $inicio);

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$internacao = new internacaoDAO($conn, $BASE_URL);

// inicializacao de variaveis
$data_intern_int = null;
$order = null;
$obLimite = null;
$blocoNovo = null;

$where = null;

// METODO DE BUSCA DE PAGINACAO 
$pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome') ?: null;
$senha_fin = filter_input(INPUT_GET, 'senha_fin') ?: null;
$idcapeante = filter_input(INPUT_GET, 'idcapeante') ?: null;
$med_check = filter_input(INPUT_GET, 'med_check') ?: null;
$enf_check = filter_input(INPUT_GET, 'enf_check') ?: null;
$adm_check = filter_input(INPUT_GET, 'adm_check') ?: null;
$data_intern_int = filter_input(INPUT_GET, 'data_intern_int') ?: null;
$pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac') ?: null;
$limite = filter_input(INPUT_GET, 'limite') ? filter_input(INPUT_GET, 'limite') : 10;
$ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;
?>
<!-- FORMULARIO DE PESQUISAS -->
<div class="container form_container" style="margin-top:12px;">
    <div class="container">
        <h4 class="page-title" style="color: #3A3A3A">Capeantes - Auditoria</h4>
    </div>
    <hr>
    <div class="container" id="navbarToggleExternalContent">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <form class=" visible" action="" id="select-internacao-form" method="GET">
            <!-- <h6 style="margin-left: 0px; padding-top:10px" class="page-title"> Pesquisa Capeantes</h6> -->

            <div class="form-group row">
                <div class="form-group col-sm-3">
                    <input class="form-control form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787"
                        type="text" name="pesquisa_nome" placeholder="Selecione o Hospital"
                        value="<?= $pesquisa_nome ?>">
                </div>
                <div class="form-group col-sm-3">
                    <input class="form-control form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787"
                        type="text" name="pesquisa_pac" placeholder="Selecione o Paciente" value="<?= $pesquisa_pac ?>">
                </div>
                <div class="col-sm-1" style="padding:2px !important">
                    <select class="form-control mb-3 form-control-sm"
                        style="margin-top:7px;font-size:.8em; color:#878787" id="limite" name="limite">
                        <option value="">Reg por pag</option>
                        <option value="5" <?= $limite == '5' ? 'selected' : null ?>>Reg por pág = 5
                        </option>
                        <option value="10" <?= $limite == '10' ? 'selected' : null ?>>Reg por pág = 10
                        </option>
                        <option value="20" <?= $limite == '20' ? 'selected' : null ?>>Reg por pág = 20
                        </option>
                        <option value="50" <?= $limite == '50' ? 'selected' : null ?>>Reg por pág = 50
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787"
                        id="ordenar" name="ordenar">
                        <option value="">Classificar por</option>
                        <option value="id_internacao" <?= $ordenar == 'id_internacao' ? 'selected' : null ?>>Internação
                        </option>
                        <option value="nome_pac" <?= $ordenar == 'nome_pac' ? 'selected' : null ?>>Paciente</option>
                        <option value="nome_hosp" <?= $ordenar == 'nome_hosp' ? 'selected' : null ?>>Hospital</option>
                        <option value="data_intern_int" <?= $ordenar == 'data_intern_int' ? 'selected' : null ?>>Data
                            Internação</option>
                    </select>
                </div>
            </div>
            <div style="margin-top:10px" class="form-group row">
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787"
                        id="med_check" name="med_check">
                        <option value="s" <?= $med_check == 's' ? 'selected' : null ?>>Sim</option>
                        <option value="n" <?= $med_check == 'n' ? 'selected' : null ?>>Não</option>
                        <option value="" <?= ($med_check != 's' and $med_check != 'n') ? 'selected' : null ?>>Med Check
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787"
                        id="enf_check" name="enf_check">
                        <option value=""></option>
                        <option value="s" <?= $enf_check == 's' ? 'selected' : null ?>>Sim</option>
                        <option value="n" <?= $enf_check == 'n' ? 'selected' : null ?>>Não</option>
                        <option value="" <?= ($enf_check != 's' and $enf_check != 'n') ? 'selected' : null ?>>Enf Check
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787"
                        id="adm_check" name="adm_check">
                        <option value="s" <?= $adm_check == 's' ? 'selected' : null ?>>Sim</option>
                        <option value="n" <?= $adm_check == 'n' ? 'selected' : null ?>>Não</option>
                        <option value="" <?= ($adm_check != 's' and $adm_check != 'n') ? 'selected' : null ?>>Adm Check
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787"
                        id="senha_fin" name="senha_fin">
                        <option value="" <?= ($senha_fin != 's' and $senha_fin != 'n') ? 'selected' : null ?>>Senha
                            Finalizada</option>
                        <option value="s" <?= $senha_fin == 's' ? 'selected' : null ?>>Sim</option>
                        <option value="n" <?= $senha_fin == 'n' ? 'selected' : null ?>>Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-1" style="margin:0px 0px 20px 0px">
                    <button type="submit" class="btn btn-primary"
                        style="background-color:#5e2363;width:42px;height:32px;margin-top:7px;border-color:#5e2363"><span
                            class="material-icons" style="margin-left:-3px;margin-top:-2px;">
                            search
                        </span></button>
                </div>
            </div>
        </form>
    </div>

    <!-- BASE DAS PESQUISAS -->
    <?php

    // validacao de lista de hospital por usuario (o nivel sera o filtro)
    if ($_SESSION['nivel'] == 3 or $_SESSION['nivel'] == 1) {
        $auditor = ($_SESSION['id_usuario']);
    } else {
        $auditor = null;
    };

    //Instanciando a classe
    $QtdTotalInt = new internacaoDAO($conn, $BASE_URL);
    // METODO DE BUSCA DE PAGINACAO 
    $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
    $senha_fin = filter_input(INPUT_GET, 'senha_fin', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'n';
    $med_check = filter_input(INPUT_GET, 'med_check') ?: null;
    $enf_check = filter_input(INPUT_GET, 'enf_check') ?: null;
    $adm_check = filter_input(INPUT_GET, 'adm_check') ?: null;
    $data_intern_int = filter_input(INPUT_GET, 'data_intern_int') ?: null;
    $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
    $limite = filter_input(INPUT_GET, 'limite') ? filter_input(INPUT_GET, 'limite') : 10;
    $ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;
    $order = null;

    $condicoes = [
        strlen($pesquisa_nome) ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"' : NULL,
        strlen($pesquisa_pac) ? 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"' : NULL,
        strlen($senha_fin) ? 'senha_finalizada = "' . $senha_fin . '"' : null,
        strlen($med_check) ? 'med_check = "' . $med_check . '"' : NULL,
        strlen($enf_check) ? 'enfer_check = "' . $enf_check . '"' : NULL,
        strlen($adm_check) ? 'adm_check = "' . $adm_check . '"' : NULL,
        strlen($data_intern_int) ? 'data_intern_int = "' . $data_intern_int . '"' : NULL,
        strlen($auditor) ? 'hos.fk_usuario_hosp = "' . $auditor . '"' : NULL,

    ];

    $condicoes = array_filter($condicoes);
    // REMOVE POSICOES VAZIAS DO FILTRO
    $where = implode(' AND ', $condicoes);

    // QUANTIDADE Internacao
    $qtdIntItens1 = $QtdTotalInt->selectAllInternacaoCap($where);

    $qtdIntItens = count($qtdIntItens1);
    $totalcasos = ceil($qtdIntItens / $limite);

    // PAGINACAO
    $order = $ordenar;

    $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);

    $obLimite = $obPagination->getLimit();

    // PREENCHIMENTO DO FORMULARIO COM QUERY
    $query = $internacao_geral->selectAllInternacaoCap($where, $order, $obLimite);

    // // PAGINACAO
    $order = $ordenar;

    $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);

    $obLimite = $obPagination->getLimit();

    // PREENCHIMENTO DO FORMULARIO COM QUERY
    // $query = $internacao->selectAllInternacaoList($where, $order, $obLimite);

    // PAGINACAO
    if ($qtdIntItens > $limite) {
        $paginacao = '';
        $paginas = $obPagination->getPages();
        $pagina = 1;
        $total_pages = count($paginas);

        // FUNCAO PARA CONTROLE DO NUMERO DE PAGINAS, UTILIZANDO A QUANTIDADE DE PAGINAS CALCULADAS NA VARIAVEL PAGINAS PELE METODO getPages

        function paginasAtuais($var)
        {
            $blocoAtual = isset($_GET['bl']) ? $_GET['bl'] : 0;
            return $var['bloco'] == (($blocoAtual) / 5) + 1;
        }
        $block_pages = array_filter($paginas, "paginasAtuais"); // REFERENCIA FUNCAO CRIADA ACIMA
        $first_page_in_block = reset($block_pages)["pg"];
        $last_page_in_block = end($block_pages)["pg"];
        $first_block = reset($paginas)["bloco"];
        $last_block = end($paginas)["bloco"];
        $current_block = reset($block_pages)["bloco"];
    }
    ?>
    <!-- INICIO DA TABELA -->
    <div class="container">
        <div id="table-content">
            <?php
            $dataFech = date('Y-m-d');

            ?>
            <table class="table table-sm table-striped  table-hover table-condensed">
                <table class="table table-sm table-striped  table-hover table-condensed">
                    <thead>
                        <tr>
                            <th scope="col" style="width:4%">Reg</th>
                            <th scope="col" style="width:6%">Conta No.</th>
                            <th scope="col" style="width:23%">Hospital</th>
                            <th scope="col" style="width:23%">Paciente</th>
                            <th scope="col" style="width:12%">Data internação</th>
                            <th scope="col" style="width:4%">Med</th>
                            <th scope="col" style="width:4%">Enf</th>
                            <th scope="col" style="width:4%">Adm</th>
                            <th scope="col" style="width:4%">Final</th>
                            <th scope="col" style="width:4%">Parcial</th>
                            <th scope="col" style="width:13%">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($query as $intern) :
                            extract($intern);
                            // echo "<pre>";
                            // print_r($intern);
                            // // PAGINACAO
                        ?>
                        <tr style="font-size:15px">
                            <td scope="row" class="col-id"><em><b>
                                        <?= $intern["id_internacao"]; ?></em></b></td>
                            <td scope="row" class="col-id"><b>
                                    <?= $intern["id_capeante"]; ?>
                                </b></td>
                            <td scope="row" class="nome-coluna-table"><em><b>
                                        <?= $intern["nome_hosp"] ?></em></b></td>
                            <td scope="row">
                                <?= $intern["nome_pac"] ?>
                            </td>
                            <td scope="row">
                                <?= date('d/m/Y', strtotime($intern["data_intern_int"])) ?>
                            </td>

                            <td scope="row">
                                <?php if ($intern["med_check"] === "s") { ?>
                                <span id="boot-icon" class="bi bi-check-circle"
                                    style="font-size: 1.1rem; font-weight:800; color: rgb(0, 78, 86);"></span>
                                <?php }; ?>
                            </td>
                            <td scope="row">
                                <?php if ($intern["enfer_check"] == "s") { ?>
                                <span id="boot-icon" class="bi bi-check-circle"
                                    style="font-size: 1.1rem; font-weight:800; color: rgb(234, 128, 55);"></span>
                                <?php }; ?>
                            </td>

                            <td scope="row">
                                <?php if ($intern["adm_check"] === "s") { ?>
                                <span id="boot-icon" class="bi bi-check-circle"
                                    style="font-size: 1.1rem; font-weight:800; color: rgb(25, 78, 255);"></span>
                                <?php }; ?>
                            </td>
                            <td scope="row">
                                <?php if ($intern["senha_finalizada"] == "s") { ?>
                                <span id="boot-icon" class="bi bi-briefcase"
                                    style="font-size: 1.1rem; font-weight:800; color: rgb(255, 25, 55);"></span>
                                <?php }; ?>
                            </td>
                            <td scope="row">
                                <?= $intern["parcial_num"]; ?>
                            </td>

                            <td class="action">
                                <a
                                    href="<?= $BASE_URL ?>show_internacao.php?id_internacao=<?= $intern["id_internacao"] ?>"><i
                                        style="color:green; margin-right:10px"
                                        class="aparecer-acoes fas fa-eye check-icon"></i></a>

                                <?php
                                    if ($intern['encerrado_cap'] <> "s")
                                        if ($intern['em_auditoria_cap'] == "s") { ?>
                                <a
                                    href="<?= $BASE_URL ?>cad_capeante_audit.php?id_capeante=<?= $intern["id_capeante"] ?>"><i
                                        style="color:rgb(255, 55, 25); text-decoration: none; font-size: 10px; font-weight:bold; margin-left:5px;margin-right:5px"
                                        name="type" value="capeante" class="aparecer-acoes bi bi-file-text"> Em
                                        análise</i></a>
                                <?php } else { ?>
                                <a
                                    href="<?= $BASE_URL ?>cad_capeante_audit.php?id_internacao=<?= $intern["id_internacao"] ?>"><i
                                        style="color:rgb(25, 78, 255); text-decoration: none; font-size: 10px; font-weight:bold; margin-left:5px;margin-right:5px"
                                        name="type" value="capeante" class="aparecer-acoes bi bi-file-text">
                                        Iniciar</i></a>
                                <?php } {
                                    };
                                    ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if ($qtdIntItens == 0) : ?>
                        <tr>
                            <td colspan="11" scope="row" class="col-id" style='font-size:15px'>
                                Não foram encontrados registros
                            </td>
                        </tr>

                        <?php endif ?>
                    </tbody>
                </table>


                <!-- salvar variavel qtdIntItens no PHP para passar para JS -->
                <div style="text-align:right">
                    <input type="hidden" id="qtd" value="<?php echo $qtdIntItens ?>">
                </div>

                <div class="pagination" style="margin: 0 auto;">
                    <?php if ($total_pages ?? 1 > 1) : ?>
                    <ul class="pagination">
                        <?php
                            $blocoAtual = isset($_GET['bl']) ? $_GET['bl'] : 0;
                            $paginaAtual = isset($_GET['pag']) ? $_GET['pag'] : 1;
                            ?>
                        <?php if ($current_block > $first_block) : ?>
                        <li class="page-item">
                            <a class="page-link" id="blocoNovo" href="#"
                                onclick="loadContent('list_internacao_cap_audit.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>')">
                                <i class="fa-solid fa-angles-left"></i></a>
                        </li>
                        <?php endif; ?>
                        <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1) : ?>
                        <li class="page-item">
                            <a class="page-link" href="#"
                                onclick="loadContent('list_internacao_cap_audit.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&med_check=<?php print $med_check ?>&enf_check=<?php print $enf_check ?>&adm_check=<?php print $adm_check ?>&senha_fin=<?php print $senha_fin ?>&pag=<?php print print $paginaAtual - 1 ?>&bl=<?php print print $blocoAtual - 5 ?>')">
                                <i class="fa-solid fa-angle-left"></i> </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++) : ?>
                        <li class="page-item <?php print ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">

                            <a class="page-link" href="#"
                                onclick="loadContent('list_internacao_cap_audit.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&med_check=<?php print $med_check ?>&enf_check=<?php print $enf_check ?>&adm_check=<?php print $adm_check ?>&senha_fin=<?php print $senha_fin ?>&pag=<?php print $i ?>&bl=<?php print $blocoAtual ?>')">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($current_block < $last_block) : ?>
                        <li class="page-item">
                            <a class="page-link" id="blocoNovo" href="#"
                                onclick="loadContent('list_internacao_cap_audit.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&med_check=<?php print $med_check ?>&enf_check=<?php print $enf_check ?>&adm_check=<?php print $adm_check ?>&senha_fin=<?php print $senha_fin ?>&pag=<?php print $paginaAtual + 1 ?>&bl=<?php print $blocoAtual + 5 ?>')"><i
                                    class="fa-solid fa-angle-right"></i></a>
                        </li>
                        <?php endif; ?>
                        <?php if ($current_block < $last_block) : ?>
                        <li class="page-item">
                            <a class="page-link" id="blocoNovo" href="#"
                                onclick="loadContent('list_internacao_cap_audit.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&med_check=<?php print $med_check ?>&enf_check=<?php print $enf_check ?>&adm_check=<?php print $adm_check ?>&senha_fin=<?php print $senha_fin ?>&pag=<?php print print count($paginas) ?>&bl=<?php print print ($last_block - 1) * 5 ?>')"><i
                                    class="fa-solid fa-angles-right"></i></a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <?php endif; ?>
                </div>


                <div>
                    <a class="btn btn-success styled"
                        style="background-color: #35bae1;font-family:var(--bs-font-sans-serif);box-shadow: 0px 10px 15px -3px rgba(0,0,0,0.1);border:none"
                        href="cad_capeante.php">Novo Capeante</a>
                </div>
        </div>
    </div>
</div>
<script>
// ajax para submit do formulario de pesquisa
$(document).ready(function() {
    $('#select-internacao-form').submit(function(e) {
        e.preventDefault(); // Impede o comportamento padrão de enviar o formulário

        var formData = $(this).serialize(); // Serializa os dados do formulário

        $.ajax({
            url: $(this).attr('action'), // URL do formulário
            type: $(this).attr('method'), // Método do formulário (POST)
            data: formData, // Dados serializados do formulário
            success: function(response) {
                // Crie um elemento temporário para armazenar a resposta HTML
                var tempElement = document.createElement('div');
                tempElement.innerHTML = response;

                // Encontre o elemento com o ID "table-content" dentro do elemento temporário
                var tableContent = tempElement.querySelector('#table-content');
                $('#table-content').html(tableContent);
            },
            error: function() {
                $('#responseMessage').html('Ocorreu um erro ao enviar o formulário.');
            }
        });
    });
});

$(document).ready(function() {
    loadContent(
        'list_internacao_cap_audit.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&med_check=<?php print $med_check ?>&enf_check=<?php print $enf_check ?>&adm_check=<?php print $adm_check ?>&senha_fin=<?php print $senha_fin ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>'
    );
});
</script>
<script src="./js/input-estilo.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
</script>
<script src="./scripts/cadastro/general.js"></script>