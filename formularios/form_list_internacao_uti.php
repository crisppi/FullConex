<?php

require_once("templates/header.php");

// require_once("models/message.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/patologia.php");
include_once("dao/patologiaDao.php");

include_once("models/paciente.php");
include_once("dao/pacienteDao.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("models/uti.php");
include_once("dao/utiDao.php");

include_once("models/pagination.php");

$where = null;
$internacao_geral = new internacaoDAO($conn, $BASE_URL);
$internacaos = $internacao_geral->findGeral($where, $limite, $inicio);

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$hospital_geral = new hospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$internacao = new internacaoDAO($conn, $BASE_URL);
$order = null;
$obLimite = null;
$uti = new utiDAO($conn, $BASE_URL);

?>

<!-- FORMULARIO DE PESQUISAS -->
<div class="container-fluid form_container" style="margin-top:-5px;" id='main-container'>
    <h4 class="page-title">Internação UTI</h4>
    <hr>
    <div class="complete-table">
        <div id="navbarToggleExternalContent" class="table-filters">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <div class="row">
                <form action="" id="select-internacao-form" method="GET">
                    <?php $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS);
                    $pesqInternado = filter_input(INPUT_GET, 'pesqInternado', FILTER_SANITIZE_SPECIAL_CHARS);
                    $limite_pag = filter_input(INPUT_GET, 'limite_pag');
                    $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS);
                    $pesquisa_matricula = filter_input(INPUT_GET, 'pesquisa_matricula', FILTER_SANITIZE_SPECIAL_CHARS);
                    $ordenar = filter_input(INPUT_GET, 'ordenar');
                    ?>
                    <div class="row">
                        <div class="col-sm-3" style="padding:2px !important;padding-left:16px !important;">
                            <!-- <label>Pesquisa por Hospital</label> -->
                            <input class="form-control form-control-sm" type="text" name="pesquisa_nome"
                                style="margin-top:7px;font-size:.8em; color:#878787" placeholder="Hospital" autofocus
                                value="<?= $pesquisa_nome ?>">
                        </div>
                        <div class="col-sm-3" style="padding:2px !important">
                            <!-- <label>Pesquisa por Paciente</label> -->
                            <input class="form-control form-control-sm" type="text" name="pesquisa_pac"
                                style="margin-top:7px;font-size:.8em; color:#878787" placeholder="Paciente"
                                value="<?= $pesquisa_pac ?>">
                        </div>
                        <div class="col-sm-2" style="padding:2px !important">
                            <input class="form-control form-control-sm" type="text" name="pesquisa_matricula"
                                style="margin-top:7px;font-size:.8em; color:#878787" placeholder="Matrícula"
                                value="<?= htmlspecialchars((string)$pesquisa_matricula) ?>">
                        </div>

                        <div class="col-sm-2" style="padding:2px !important">
                            <!-- <label>Internados</label> -->
                            <select class="form-control sm-3 form-control-sm" id="pesqInternado" name="pesqInternado"
                                style="margin-top:7px">
                                <option value="">Busca por Internados</option>
                                <option value="s" <?= $pesqInternado == 's' ? 'selected' : null ?>>Sim</option>
                                <option value="n" <?= $pesqInternado == 'n' ? 'selected' : null ?>>Não</option>
                            </select>
                        </div>
                        <div class="col-sm-1" style="padding:2px !important">
                            <select class="form-control mb-3 form-control-sm" style="margin-top:7px;" id="limite"
                                name="limite">
                                <option value="">Reg por página</option>
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
                        <div class="col-sm-2" style="padding:2px !important">
                            <!-- <label>Classificar</label> -->
                            <select class="form-control sm-3 form-control-sm" id="ordenar" name="ordenar"
                                style="margin-top:7px">
                                <option value="">Classificar por</option>
                                <option value="nome_pac" <?= $ordenar == 'nome_pac' ? 'selected' : null ?>>Paciente
                                </option>
                                <option value="nome_hosp" <?= $ordenar == 'nome_hosp' ? 'selected' : null ?>>Hospital
                                </option>
                                <option value="id_internacao" <?= $ordenar == 'id_internacao' ? 'selected' : null ?>>
                                    Internação
                                </option>
                                <option value="data_intern_int"
                                    <?= $ordenar == 'data_intern_int' ? 'selected' : null ?>>
                                    Data
                                    Internação</option>
                            </select>
                        </div>
                        <div class="col-sm-1" style="padding:2px !important" style="margin:0px 0px 20px 0px">
                            <button type="submit" class="btn btn-primary"
                                style="background-color:#5e2363;width:42px;height:32px;margin-top:7px;border-color:#5e2363"><span
                                    class="material-icons" style="margin-left:-3px;margin-top:-2px;">
                                    search
                                </span></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- BASE DAS PESQUISAS -->
        <?php

        // validacao de lista de hospital por usuario (o nivel sera o filtro)
        if ($_SESSION['nivel'] == 3) {
            $auditor = ($_SESSION['id_usuario']);
        } else {
            $auditor = null;
        };

        //Instanciando a classe
        $QtdTotalIntUTI = new utiDAO($conn, $BASE_URL);
        // METODO DE BUSCA DE PAGINACAO
        $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
        $pesqInternado = filter_input(INPUT_GET, 'pesqInternado');
        $limite_pag = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
        $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
        $pesquisa_matricula = filter_input(INPUT_GET, 'pesquisa_matricula');
        $data_intern_int = filter_input(INPUT_GET, 'data_intern_int') ?: null;
        $ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : '';
        $uti_internacao = 's';
        $uti_internado = 's';
        // $buscaAtivo = in_array($buscaAtivo, ['s', 'n']) ?: "";
        $condicoes = [
            strlen($pesquisa_nome) ? 'nome_hosp LIKE "%' . $pesquisa_nome . '%"' : null,
            strlen($pesquisa_pac) ? 'nome_pac LIKE "%' . $pesquisa_pac . '%"' : null,
            strlen($pesquisa_matricula) ? 'pa.matricula_pac LIKE "%' . $pesquisa_matricula . '%"' : null,
            strlen($pesqInternado) ? 'internado_int = "' . $pesqInternado . '"' : NULL,
            strlen($uti_internacao) ? 'uti.internacao_uti = "s"' : "s",
            strlen($uti_internado) ? 'uti.internado_uti = "s"' : "s",
            strlen($data_intern_int) ? 'data_intern_int = "' . $data_intern_int . '"' : NULL,
            strlen($auditor) ? 'hos.fk_usuario_hosp = "' . $auditor . '"' : NULL,

        ];
        $condicoes = array_filter($condicoes);
        // REMOVE POSICOES VAZIAS DO FILTRO
        $where = implode(' AND ', $condicoes);
        $order = $ordenar ?: 'id_internacao DESC';
        // QUANTIDADE InternacaoS
        $qtdIntItens1 = $uti->selectAllUTI($where, $order, $obLimite);
        // print_r($qtdIntItens1);
        $qtdIntItens = count($qtdIntItens1);
        // PAGINACAO
        $qtdIntItens = count($qtdIntItens1);
        $totalcasos = ceil($qtdIntItens / $limite);

        $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite_pag);

        $obLimite = $obPagination->getLimit();

        // PREENCHIMENTO DO FORMULARIO COM QUERY
        $query = $uti->selectAllUTI($where, $order, $obLimite);

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

        <div style="margin-top:10px;" id='container'>
            <div id="table-content">

                <!-- <h6 class="page-title">Relatório de internações - UTI</h6> -->
                <table class="table table-sm table-striped table-hover table-condensed">
                    <thead>
                        <tr>
                            <th scope="col" width="4%">Id-Int</th>
                            <th scope="col" width="4%">Internado</th>
                            <th scope="col" width="15%">Hospital</th>
                            <th scope="col" width="15%">Paciente</th>
                            <th scope="col" width="8%">Data internação</th>
                            <th scope="col" width="4%">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($query as $intern):
                            extract($query);
                        ?>
                        <tr style="font-size:13px">
                            <td scope="row" class="col-id">
                                <?= $intern["id_internacao"] ?>
                            </td>
                            <td scope="row" class="nome-coluna-table">
                                <?php if ($intern["internado_int"] == "s") {
                                        echo "Sim";
                                    } else {
                                        echo "Não";
                                    }; ?>
                            </td>
                            <td scope="row" class="nome-coluna-table">
                                <?= $intern["nome_hosp"] ?>
                            </td>
                            <td scope="row">
                                <?= $intern["nome_pac"] ?>
                            </td>
                            <td scope="row">
                                <?= date('d/m/Y', strtotime($intern["data_intern_int"])) ?>
                            </td>
                            <td class="action">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" id="navbarScrollingDropdown"
                                        role="button" data-bs-toggle="dropdown" style="color:#5e2363"
                                        aria-expanded="false">
                                        <i class="bi bi-stack"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                        <li>
                                            <button class="btn btn-default" style="font-size: .9rem;"
                                                onclick="edit('<?= $BASE_URL ?>show_internacao.php?id_internacao=<?= $intern['id_internacao'] ?>')"><i
                                                    class="fas fa-eye"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>Ver</button>
                                        </li>
                                        <li>
                                            <form class="d-inline-block delete-form" action="edit_alta_uti.php"
                                                method="get">
                                                <input type="hidden" name="type" value="update">
                                                <!-- <input type="hidden" name="alta" value="alta"> -->
                                                <input type="hidden" name="id_internacao"
                                                    value="<?= $intern["id_internacao"] ?>">
                                                <button class="btn btn-default" style="font-size: .9rem;"><i
                                                        style="font-size: 1rem;margin-right:5px; color: rgb(67, 125, 525);"
                                                        class="bi bi-door-open"></i>Alta</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if ($qtdIntItens == 0): ?>
                        <tr>
                            <td colspan="8" scope="row" class="col-id" style='font-size:15px'>
                                Não foram encontrados registros
                            </td>
                        </tr>

                        <?php endif ?>
                    </tbody>
                </table>
                <!-- paginacao que aparece abaixo da tabela -->
                <div style="display: flex;margin-top:20px">

                    <!-- Modal para abrir tela de cadastro -->
                    <div class="modal fade" id="myModal">
                        <div class="modal-dialog  modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="page-title" style="color:white;">Cadastrar Internação</h4>
                                    <p class="page-description" style="color:white; margin-top:5px">Adicione
                                        informações
                                        sobre a internação</p>
                                </div>
                                <div class="modal-body">
                                    <div id="content-php"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Modal para abrir tela de cadastro -->

                    <div class="pagination" style="margin: 0 auto;">
                        <?php if ($total_pages ?? 1 > 1): ?>
                        <ul class="pagination">
                            <?php
                                $blocoAtual = isset($_GET['bl']) ? $_GET['bl'] : 0;
                                $paginaAtual = isset($_GET['pag']) ? $_GET['pag'] : 1;
                                ?>
                            <?php if ($current_block > $first_block): ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_internacao_uti.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>')">
                                    <i class="fa-solid fa-angles-left"></i></a>
                            </li>
                            <?php endif; ?>
                            <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="#"
                                    onclick="loadContent('list_internacao_uti.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual - 1 ?>&bl=<?php print $blocoAtual - 5 ?>')">
                                    <i class="fa-solid fa-angle-left"></i> </a>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++): ?>
                            <li class="page-item <?php print ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">

                                <a class="page-link" href="#"
                                    onclick="loadContent('list_internacao_uti.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $i ?>&bl=<?php print $blocoAtual ?>')">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($current_block < $last_block): ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_internacao_uti.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual + 1 ?>&bl=<?php print $blocoAtual + 5 ?>')"><i
                                        class="fa-solid fa-angle-right"></i></a>
                            </li>
                            <?php endif; ?>
                            <?php if ($current_block < $last_block): ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_internacao_uti.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print count($paginas) ?>&bl=<?php print ($last_block - 1) * 5 ?>')"><i
                                        class="fa-solid fa-angles-right"></i></a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </div>

                    <div class="table-counter">
                        <p
                            style="margin-bottom:25px;font-size:1em; font-weight:600; font-family:var(--bs-font-sans-serif); text-align:right">
                            <?php echo "Total: " . $qtdIntItens ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
// ajax para submit do formulario de pesquisa
$(document).ready(function() {
    $('#select-internacao-form').submit(function(e) {
        e.preventDefault(); // Impede o comportamento padrão de enviar o formulário
        console.log("teste")
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
// ajax para navegacao 
function loadContent(url) {
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            // Crie um elemento temporário para armazenar a resposta HTML
            var tempElement = document.createElement('div');
            tempElement.innerHTML = data;

            // Encontre o elemento com o ID "table-content" dentro do elemento temporário
            var tableContent = tempElement.querySelector('#table-content');
            $('#table-content').html(tableContent);
        },
        error: function() {
            console.log('Error loading content');
        }
    });
}
$(document).ready(function() {
    loadContent(
        'list_internacao_uti.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>'
    );
});
</script>


<script src="./js/input-estilo.js"></script>

<style>
.modal-backdrop {
    display: none;

}

.modal {
    background: rgba(0, 0, 0, 0.5);

}

.modal-header {
    color: white;
    background: #35bae1;
}
</style>


<script src="./js/input-estilo.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
</script>
<script src="./js/ajaxNav.js"></script>
