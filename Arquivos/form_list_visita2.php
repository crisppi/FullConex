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

include_once("models/pagination.php");

$obLimite = null;

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$Internacaos = $Internacao_geral->findGeral();

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$internacao = new internacaoDAO($conn, $BASE_URL);

?>
<!-- FORMULARIO DE PESQUISAS -->
<div class="container form_container" style="margin-top:12px;">
    <div class="container">
        <h4 class="page-title" style="color: #3A3A3A">Relação Internação - Visitas</h4>
    </div>
    <hr>
    <div class="container" id="navbarToggleExternalContent">
        <div>
            <form action="" id="select-internacao-form" method="GET">
                <!-- <h6 style="margin-left: 30px; padding-top:10px" class="page-title">Pesquisa internações</h6> -->
                <?php
                // validacao de lista de hospital por usuario (o nivel sera o filtro)
                if ($_SESSION['nivel'] == 3) {
                    $auditor = ($_SESSION['id_usuario']);
                } else {
                    $auditor = null;
                };
                $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
                $pesqInternado = filter_input(INPUT_GET, 'pesqInternado') ?: "s";
                $limite = filter_input(INPUT_GET, 'limite');
                $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
                $data_intern_int = filter_input(INPUT_GET, 'data_intern_int') ?: null;
                $ordenar = filter_input(INPUT_GET, 'ordenar');
                ?>
                <div class="form-group row" style="margin-bottom:18px">

                    <div class="form-group col-sm-3">
                        <input class="form-control form-control-sm" type="text" style="margin-top:7px;font-size:.8em; color:#878787" name="pesquisa_nome" placeholder="Selecione o Hospital" value="<?= $pesquisa_nome ?>">
                    </div>
                    <div class="form-group col-sm-3">

                        <input class="form-control form-control-sm" type="text" style="margin-top:7px;font-size:.8em; color:#878787" name="pesquisa_pac" placeholder="Selecione o Paciente" value="<?= $pesquisa_pac ?>">
                    </div>

                    <div class="form-group col-sm-1">
                        <select class="form-control mb-3 form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787" id="pesqInternado" name="pesqInternado">
                            <option value="">Busca por Internados</option>
                            <option value="s" <?= $pesqInternado == 's' ? 'selected' : null ?>>Sim</option>
                            <option value="n" <?= $pesqInternado == 'n' ? 'selected' : null ?>>Não</option>
                        </select>
                    </div>

                    <div class="form-group col-sm-1">
                        <select class="form-control mb-3 form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787" id="limite" name="limite">
                            <option value="">Reg por página</option>
                            <option value="5" <?= $limite == '5' ? 'selected' : null ?>>5</option>
                            <option value="10" <?= $limite == '10' ? 'selected' : null ?>>10</option>
                            <option value="20" <?= $limite == '20' ? 'selected' : null ?>>20</option>
                            <option value="50" <?= $limite == '50' ? 'selected' : null ?>>50</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2">
                        <select class="form-control mb-3 form-control-sm" style="margin-top:7px;font-size:.8em; color:#878787" id="ordenar" name="ordenar">
                            <option value="">Classificar por</option>
                            <option value="id_internacao" <?= $ordenar == 'id_internacao' ? 'selected' : null ?>>Internação
                            </option>
                            <option value="nome_pac" <?= $ordenar == 'nome_pac' ? 'selected' : null ?>>Paciente</option>
                            <option value="nome_hosp" <?= $ordenar == 'nome_hosp' ? 'selected' : null ?>>Hospital</option>
                            <option value="data_intern_int" <?= $ordenar == 'data_intern_int' ? 'selected' : null ?>>Data
                                Internação</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-1" style="margin:0px 0px 20px 0px">
                        <button type="submit" class="btn btn-primary" style="background-color:#5e2363;width:42px;height:32px;margin-top:7px;border-color:#5e2363"><span class="material-icons" style="margin-left:-3px;margin-top:-2px;">
                                search
                            </span></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- BASE DAS PESQUISAS -->
        <?php
        //Instanciando a classe
        $QtdTotalInt = new internacaoDAO($conn, $BASE_URL);
        // METODO DE BUSCA DE PAGINACAO 
        $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
        $pesqInternado = filter_input(INPUT_GET, 'pesqInternado') ?: "s";
        // $semAlta = filter_input(INPUT_GET, 'pesqInternado') ?: "null";
        $limite = filter_input(INPUT_GET, 'limite') ? filter_input(INPUT_GET, 'limite') : 10;
        $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
        $ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;
        // $buscaAtivo = in_array($buscaAtivo, ['s', 'n']) ?: "";
        // print_r($auditor);
        $condicoes = [
            strlen($pesquisa_nome) ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"' : null,
            strlen($pesquisa_pac) ? 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"' : null,
            strlen($pesqInternado) ? 'internado_int = "' . $pesqInternado . '"' : NULL,
            strlen($data_intern_int) ? 'data_intern_int = "' . $data_intern_int . '"' : NULL,
            strlen($auditor) ? 'hos.fk_usuario_hosp = "' . $auditor . '"' : NULL,
            'ac.id_internacao=vi.fk_internacao_vis'
        ];

        $condicoes = array_filter($condicoes);
        // REMOVE POSICOES VAZIAS DO FILTRO
        $where = implode(' AND ', $condicoes);
        // PAGINACAO
        $order = $ordenar;

        $qtdIntItens1 = $QtdTotalInt->selectAllInternacaoCountVis($where, $order, $obLimite);

        $qtdIntItens = count($qtdIntItens1);
        $totalcasos = ceil($qtdIntItens / $limite);

        $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);

        $obLimite = $obPagination->getLimit();

        // PREENCHIMENTO DO FORMULARIO COM QUERY
        $query = $internacao->selectAllInternacaoCountVis($where, $order, $obLimite);

        // PAGINACAO
        $paginacao = '';
        $paginas = $obPagination->getPages();

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
        };

        ?>
        <div class="container">
            <!-- <?php include_once("check_nivel.php");
                    ?> -->
            <div class="row" id="table-content">
                <table class="table table-sm table-striped  table-hover table-condensed">
                    <thead>
                        <tr>
                            <th scope="col" style="width:3%">Id</th>
                            <th scope="col" style="width:3%">Internado</th>
                            <th scope="col" style="width:12%">Hospital</th>
                            <th scope="col" style="width:12%">Paciente</th>
                            <th scope="col" style="width:5%">Data internação</th>
                            <th scope="col" style="width:5%">Diárias</th>
                            <th scope="col" style="width:5%">Data visita</th>
                            <th scope="col" style="width:5%">Dias Visita</th>
                            <th scope="col" style="width:2%">Med</th>
                            <th scope="col" style="width:2%">Enf</th>
                            <th scope="col" style="width:4%">No Vis</th>
                            <th scope="col" style="width:10%">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $hoje = date('Y-m-d');
                        $atual = new DateTime($hoje);
                        foreach ($query as $intern) :
                            extract($query);

                            $ultimaVis = end($intern);
                        ?>
                            <tr style="font-size:15px">
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
                                <td scope="row">
                                    <?php
                                    $diasintern = date("Y/m/d", strtotime($intern['data_intern_int']));
                                    $dataIntern = new DateTime($diasintern);
                                    $diasIntern = $dataIntern->diff($atual);
                                    echo $diasIntern->days;
                                    ?>
                                </td>

                                <td scope="row">
                                    <?php // data da visita //
                                    $id_internacao2 = $intern['id_internacao'];
                                    $query2 = $internacao->selectInternVis($id_internacao2);
                                    if (3 < 4) {
                                        // echo date('d/m/Y', strtotime($intern["data_visita_vis"]));
                                        foreach ($query2 as $inter2) {
                                            extract($inter2);
                                            if ($inter2['fk_internacao_vis'] == $intern['id_internacao']) {
                                                if ($inter2['data_visita_vis']) {
                                                    print_r(date('d/m/Y', strtotime($inter2['data_visita_vis'])));
                                                    echo "<br>";
                                                }
                                            }
                                        }
                                    };
                                    ?>
                                </td>

                                <td scope="row">
                                    <?php
                                    foreach ($query2 as $inter2) {
                                        extract($inter2);
                                        if ($inter2['fk_internacao_vis'] == $intern['id_internacao']) {
                                        }
                                        $visitaAnt = date("Y-m-d", strtotime($inter2['data_visita_vis']));
                                        $visAnt = new DateTime($visitaAnt);
                                        $intervaloUltimaVis = $visAnt->diff($atual);
                                        if ($inter2['data_visita_vis'] != null) {
                                            if ($intervaloUltimaVis->days > 10) {
                                                echo "<span style='color:red'>";
                                                echo $intervaloUltimaVis->days;
                                                echo "<br>";
                                                echo "</span>";
                                            } else {
                                                echo "<span style='color:black'>";
                                                echo $intervaloUltimaVis->days;
                                                echo "<br>";
                                                "</span>";
                                            }
                                        }
                                    }

                                    ?>
                                </td>
                                <td scope="row" class="nome-coluna-table">
                                    <?php if ($intern["visita_med_vis"] == "s") { ?><span id="boot-icon" class="bi bi-check" style="font-size: 1.2rem; font-weight:800; color: rgb(0, 128, 55);"></span>
                                    <?php }; ?>
                                </td>
                                <td scope="row" class="nome-coluna-table">
                                    <?php if ($intern["visita_enf_vis"] == "s") { ?><span id="boot-icon" class="bi bi-check" style="font-size: 1.2rem; font-weight:800; color: rgb(0, 128, 55);"></span>
                                    <?php }; ?>
                                </td>
                                <td scope="row" class="nome-coluna-table">
                                    <?= $intern["numero_de_id_visita"] ?>
                                </td>

                                <td class="action">
                                    <a href="<?= $BASE_URL ?>show_visita.php?id_internacao=<?= $intern["id_internacao"] ?>"><i style="color:green; margin-right:8px" class="aparecer-acoes fas fa-eye check-icon"></i></a>

                                    <?php if ($pesqInternado == "s") { ?>
                                        <a href="<?= $BASE_URL ?>cad_visita.php?id_internacao=<?= $intern["id_internacao"] ?>"><i style="color:black; text-decoration: none; font-size: 10px; font-weigth:bold; margin-left:5px;margin-right:5px" name="type" value="visita" class="aparecer-acoes bi bi-file-text">
                                                Visita</i></a>
                                    <?php }; ?>

                                    <?php if ($pesqInternado == "s") { ?>
                                        <form class="d-inline-block delete-form" action="edit_alta.php" method="get">
                                            <input type="hidden" name="type" value="alta">
                                            <input type="hidden" name="id_internacao" value="<?= $intern["id_internacao"] ?>">
                                            <button type="hidden" style="margin-left:3px; font-size: 10px; background:transparent; border-color:transparent; font-weight:bold; color:red" class="delete-btn"><i class=" d-inline-block bi bi-door-open">ALTA</i></button>
                                        </form>
                                    <?php }; ?>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="container" style="display: flex;">
                    <div>
                        <a class="btn btn-success styled" style="background-color: #35bae1;font-family:var(--bs-font-sans-serif);box-shadow: 0px 10px 15px -3px rgba(0,0,0,0.1);border:none" href="internacoes/nova">Nova internação</a>
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
                                        <a class="page-link" id="blocoNovo" href="#" onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>')">
                                            <i class="fa-solid fa-angles-left"></i></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1) : ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#" onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual - 1 ?>&bl=<?php print $blocoAtual - 5 ?>')">
                                            <i class="fa-solid fa-angle-left"></i> </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++) : ?>
                                    <li class="page-item <?php print ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">

                                        <a class="page-link" href="#" onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $i ?>&bl=<?php print $blocoAtual ?>')">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_block < $last_block) : ?>
                                    <li class="page-item">
                                        <a class="page-link" id="blocoNovo" href="#" onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual + 1 ?>&bl=<?php print $blocoAtual + 5 ?>')"><i class="fa-solid fa-angle-right"></i></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($current_block < $last_block) : ?>
                                    <li class="page-item">
                                        <a class="page-link" id="blocoNovo" href="#" onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print count($paginas) ?>&bl=<?php print ($last_block - 1) * 5 ?>')"><i class="fa-solid fa-angles-right"></i></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div>
                        <p style="font-size:1em; font-weight:600; font-family:var(--bs-font-sans-serif); text-align:right">
                            <?php echo "Total: " . $qtdIntItens ?>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!------------------------------------>
<!-- JQUERY PARA MOSTRAR CAMPOS PESQUISA-->
<!------------------------------------>
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
        loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>');
    });
</script>

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
        loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>');
    });
</script>

<script>
    $(function() {
        $('#buttonId').bind('click', function() {
            $('#navbarToggleExternalContent').toggle('fast');

        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
</script>
