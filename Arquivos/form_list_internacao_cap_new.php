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
    $QtdTotalInt = new internacaoDAO($conn, $BASE_URL);
    $capeante = $capeante_geral->findGeral($limite, $inicio);

    $hospital_geral = new HospitalDAO($conn, $BASE_URL);
    $hospitals = $hospital_geral->findGeral($limite, $inicio);

    $patologiaDao = new patologiaDAO($conn, $BASE_URL);
    $patologias = $patologiaDao->findGeral();

    $internacao = new internacaoDAO($conn, $BASE_URL);

    $pesqInternado = null;
    $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
    $senha_fin = filter_input(INPUT_GET, 'senha_fin') ?: null;
    $med_check = filter_input(INPUT_GET, 'med_check') ?: null;
    $enf_check = filter_input(INPUT_GET, 'enf_check') ?: null;
    $adm_check = filter_input(INPUT_GET, 'adm_check') ?: null;
    $id_internacao = filter_input(INPUT_GET, 'id_internacao') ?: null;
    $id_capeante = filter_input(INPUT_GET, 'id_capeante') ?: null;
    $limite = filter_input(INPUT_GET, 'limite');
    $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
    $ordenar = filter_input(INPUT_GET, 'ordenar');

    ?>
    <!-- FORMULARIO DE PESQUISAS -->
    <div class="container">
        <nav class="navbar navbar-light bg-light">
            <div class="container py-2">
                <button id="buttonId" class="navbar-toggler" type="button" style="color:rgb(55,75,355)">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 style="margin-left: 30px; padding-top:10px" class="page-title"> Capeantes - Contas em Auditoria</h4>
            </div>
        </nav>
        <div class="container py-2" style="display:none" id="navbarToggleExternalContent">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

            <form class="formulario visible" action="" id="select-internacao-form" method="GET">

                <div class="form-group row">
                    <div style="margin-left: 30px;" class="form-group col-sm-3">
                        <label>Pesquisa por Hospital</label>
                        <input class="form-control" type="text" name="pesquisa_nome" placeholder="Selecione o Hospital" value="<?= $pesquisa_nome ?>">
                    </div>
                    <div class="form-group col-sm-3">
                        <label style="margin-left: 30px;">Pesquisa por Paciente</label>
                        <input style="margin-left: 30px;" class="form-control" type="text" name="pesquisa_pac" placeholder="Selecione o Paciente" value="<?= $pesquisa_pac ?>">
                    </div>

                    <div style="margin-left:90px" class="form-group col-sm-1">
                        <label>Limite</label>
                        <select class="form-control mb-3" id="limite" name="limite">
                            <option value="">Reg por página</option>
                            <option value="5" <?= $limite == '5' ? 'selected' : null ?>>5</option>
                            <option value="10" <?= $limite == '10' ? 'selected' : null ?>>10</option>
                            <option value="20" <?= $limite == '20' ? 'selected' : null ?>>20</option>
                            <option value="50" <?= $limite == '50' ? 'selected' : null ?>>50</option>
                        </select>
                    </div>
                    <div style="margin-left:40px" class="form-group col-sm-1">
                        <label>Classificar</label>
                        <select class="form-control mb-3" id="ordenar" name="ordenar">
                            <option value="">Classificar por</option>
                            <option value="id_internacao" <?= $ordenar == 'id_internacao' ? 'selected' : null ?>>Internação</option>
                            <option value="nome_pac" <?= $ordenar == 'nome_pac' ? 'selected' : null ?>>Paciente</option>
                            <option value="nome_hosp" <?= $ordenar == 'nome_hosp' ? 'selected' : null ?>>Hospital</option>
                            <option value="data_intern_int" <?= $ordenar == 'data_intern_int' ? 'selected' : null ?>>Data Internação</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div style="margin-left: 30px;" class="form-group col-sm-2">
                        <label>Médico check</label>
                        <select class="form-control mb-3" id="med_check" name="med_check">
                            <option value=""></option>
                            <option value="s" <?= $med_check == 's' ? 'selected' : null ?>>Sim</option>
                            <option value="n" <?= $med_check == 'n' ? 'selected' : null ?>>Não</option>
                            <option value="" <?= ($med_check != 's' and $med_check != 'n') ? 'selected' : null ?>>Todos</option>
                        </select>
                    </div>
                    <div style="margin-left: 30px;" class="form-group col-sm-2">
                        <label>Enf check</label>
                        <select class="form-control mb-3" id="enf_check" name="enf_check">
                            <option value=""></option>
                            <option value="s" <?= $enf_check == 's' ? 'selected' : null ?>>Sim</option>
                            <option value="n" <?= $enf_check == 'n' ? 'selected' : null ?>>Não</option>
                            <option value="" <?= ($enf_check != 's' and $enf_check != 'n') ? 'selected' : null ?>>Todos</option>
                        </select>
                    </div>
                    <div style="margin-left: 30px;" class="form-group col-sm-2">
                        <label>Adm check</label>
                        <select class="form-control mb-3" id="adm_check" name="adm_check">
                            <option value=""></option>
                            <option value="s" <?= $adm_check == 's' ? 'selected' : null ?>>Sim</option>
                            <option value="n" <?= $adm_check == 'n' ? 'selected' : null ?>>Não</option>
                            <option value="" <?= ($adm_check != 's' and $adm_check != 'n') ? 'selected' : null ?>>Todos</option>
                        </select>
                    </div>

                </div>
                <div class="form-group row">
                    <div class="form-group col-sm-1" style="margin:0px 0px 10px 30px">
                        <button type="submit" class="btn btn-primary mb-1 btn-int-pesq"><span class="material-icons">
                                person_search
                            </span></button>
                    </div>
                </div>
        </div>
        </form>
    </div>

    <!-- BASE DAS PESQUISAS -->
    <?php

    // validacao de lista de hospital por usuario (o nivel sera o filtro)
    if ($_SESSION['nivel'] == 3) {
        $auditor = ($_SESSION['id_usuario']);
    } else {
        $auditor = null;
    };
    $pesqInternado = null;
    $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
    // $senha_fin = filter_input(INPUT_GET, 'senha_fin') ?: null;
    $med_check = filter_input(INPUT_GET, 'med_check') ?: null;
    $enf_check = filter_input(INPUT_GET, 'enf_check') ?: null;
    $adm_check = filter_input(INPUT_GET, 'adm_check') ?: null;
    $id_internacao = filter_input(INPUT_GET, 'id_internacao') ?: null;
    $id_capeante = filter_input(INPUT_GET, 'id_capeante') ?: null;
    $limite = filter_input(INPUT_GET, 'limite') ?: 10;
    $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
    $data_intern_int = filter_input(INPUT_GET, 'data_intern_int') ?: null;
    $ordenar = filter_input(INPUT_GET, 'ordenar');
    $senha_final = "s";

    $condicoes = [
        strlen($pesquisa_nome) ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"' : NULL,
        strlen($pesquisa_pac) ? 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"' : NULL,
        // strlen($senha_fin) ? 'senha_finalizada = "' . $senha_fin . '"' : NULL,
        strlen($med_check) ? 'med_check = "' . $med_check . '"' : NULL,
        strlen($enf_check) ? 'enfer_check = "' . $enf_check . '"' : NULL,
        strlen($adm_check) ? 'adm_check = "' . $adm_check . '"' : NULL,
        strlen($id_internacao) ? 'id_internacao = "' . $id_internacao . '"' : NULL,
        strlen($id_capeante) ? 'id_capeante = "' . $id_capeante . '"' : NULL,
        strlen($senha_final) ? 'senha_finalizada <> "' . $senha_final . '"' : NULL,
        strlen($data_intern_int) ? 'data_intern_int = "' . $data_intern_int . '"' : NULL,
        strlen($auditor) ? 'hos.fk_usuario_hosp = "' . $auditor . '"' : NULL,
    ];

    $condicoes = array_filter($condicoes);
    // REMOVE POSICOES VAZIAS DO FILTRO
    $where = implode(' AND ', $condicoes);

    // QUANTIDADE Internacao
    $qtdIntItens1 = $QtdTotalInt->selectAllInternacaoCapList($where);

    $i = 0;
    foreach ($qtdIntItens1 as $count) {
        $i++;
    }
    $qtdIntItens = $i;
    $totalcasos = ceil($qtdIntItens / $limite);

    // PAGINACAO
    $order = $ordenar;

    $obPagination = new pagination($qtdIntItens,  $_GET['pag'] ?? 1, $limite ?? 10);

    $obLimite = $obPagination->getLimit();

    // PREENCHIMENTO DO FORMULARIO COM QUERY
    $query = $internacao_geral->selectAllInternacaoCapList($where, $order, $obLimite);

    // GETS 
    unset($_GET['pag']);
    $gets = http_build_query($_GET['pag']);

    $contarNodeRegistros = 0; // CONTAR NUMERO DE REGISTROS
    foreach ($qtdIntItens1 as $count) {
        $contarNodeRegistros++;
    };

    $blocoNovo = $blocoNovo + 5;
    // PAGINACAO

    // <!-- total de intens -->
    $qtdLinksPagina = $contarNodeRegistros; // TOTAL DE LIKS POR PAGINA

    // verificar se qtd de itens > 5
    if ($contarNodeRegistros > 5) {
        $qtdLinksPagina =  6;
    } else {
        $qtdLinksPagina = 1;
    };

    $qtdIntItens = $contarNodeRegistros; // total de registros
    $totalcasos = ceil($qtdIntItens / 5);


    // CRIAR LINKS DOS BOTOES DE NAVEGACAO, CONFORME QT DE REGISTROS
    switch ($qtdLinksPagina) {
        case 1:
            $qtdLinksPagina = 1;
            break;
        case 2:
            $qtdLinksPagina = 2;
            break;
        case 3:
            $qtdLinksPagina = 3;
            break;
        case 4:
            $qtdLinksPagina = 4;
            break;
        default:
            $qtdLinksPagina = 5;
    };
    $qtdLinksPagina = ($totalcasos / 5) + 1;

    // PAGINACAO
    $paginacao = '';
    $paginas = $obPagination->getPages();

    foreach ($paginas as $pagina) {
        $class = $pagina['atual'] ? 'btn-primary' : 'btn-light';
        $paginacao .= '<li class="page-item"><a href="?pag=' . $pagina['pg'] . '&' . $gets . '">
            <button type="button" class="btn ' . $class . '">' . $pagina['pg'] . '</button>
    <li class="page-item"></a>';
    };

    ?>
    <div class="container">
        <h6 class="page-title">Internações</h6>
        <?php
        $dataFech = date('Y-m-d');

        include_once("check_nivel.php");
        ?> <table class="table table-sm table-striped  table-hover table-condensed">

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

                    ?>
                        <tr>
                            <td scope="row" class="col-id"><?= $intern["id_internacao"]; ?></td>
                            <td scope="row" class="col-id"><?= $intern["id_capeante"]; ?></td>
                            <td scope="row" class="nome-coluna-table"><em><b><?= $intern["nome_hosp"] ?></b></em></td>
                            <td scope="row"><?= $intern["nome_pac"] ?></td>
                            <td scope="row"><?= date('d/m/Y', strtotime($intern["data_intern_int"])) ?></td>

                            <td scope="row"><?php if ($intern["med_check"] === "s") { ?>
                                    <span id="boot-icon" class="bi bi-card-checklist" style="font-size: 1.1rem; font-weight:800; color: rgb(0, 128, 55);"></span> <?php }; ?>
                            </td>
                            <td scope="row"><?php if ($intern["enfer_check"] == "s") { ?>
                                    <span id="boot-icon" class="bi bi-card-checklist" style="font-size: 1.1rem; font-weight:800; color: rgb(234, 128, 55);"></span>
                                <?php }; ?>
                            </td>

                            <td scope="row"><?php if ($intern["adm_check"] === "s") { ?>
                                    <span id="boot-icon" class="bi bi-check" style="font-size: 1.1rem; font-weight:800; color: rgb(25, 78, 255);"></span>
                                <?php }; ?>
                            </td>
                            <td scope="row"><?php if ($intern["senha_finalizada"] == "s") { ?>
                                    <span id="boot-icon" class="bi bi-briefcase" style="font-size: 1.1rem; font-weight:800; color: rgb(255, 25, 55);"></span>
                                <?php }; ?>
                            </td>
                            <td scope="row"><?= $intern["parcial_num"]; ?>
                            </td>

                            <td class="action">
                                <a href="<?= $BASE_URL ?>show_internacao.php?id_internacao=<?= $intern["id_internacao"] ?>"><i style="color:green; margin-right:10px" class="aparecer-acoes fas fa-eye check-icon"></i></a>

                                <?php if ($intern['id_capeante']) { ?>
                                    <a href="<?= $BASE_URL ?>cad_capeante_new.php?id_capeante=<?= $intern["id_capeante"] ?>"><i style="color:rgb(255, 55, 25); text-decoration: none; font-size: 10px; font-weigth:bold; margin-left:5px;margin-right:5px" name="type" value="capeante" class="aparecer-acoes bi bi-file-text"> Em análise</i></a>
                                <?php } else { ?>
                                    <a href="<?= $BASE_URL ?>cad_capeante.php?id_internacao=<?= $intern["id_internacao"] ?>"><i style="color:rgb(25, 78, 255); text-decoration: none; font-size: 10px; font-weigth:bold; margin-left:5px;margin-right:5px" name="type" value="capeante" class="aparecer-acoes bi bi-file-text"> Iniciar</i></a>
                                <?php } ?>




                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>


            <!-- salvar variavel qtdIntItens no PHP para passar para JS -->
            <div style="text-align:right">
                <input type="hidden" id="qtd" value="<?php echo $qtdIntItens ?>">
            </div>

            <!-- mostrar quantidade registro abaixo da tabela -->
            <div style="text-align:right">
                <p style="font-size:1em; font-style: italic; font-weight:600"><?php echo "Total: " . $qtdIntItens ?>
                </p>
            </div>

            <!-- paginacao que aparece abaixo da tabela -->
            <div>
                <?php
                "<div style=margin-left:20px;>";
                echo "<div style='color:blue; margin-left:20px;'>";
                echo "</div>";
                echo "<nav aria-label='Page navigation example'>";
                echo " <ul class='pagination'>";

                echo " <li class='page-item'><a class='page-link' href='list_internacao_cap_fin.php?pg=1&" . $gets . "''><span aria-hidden='true'>&Lang;</span></a></li>";

                echo "<li id='blocoAnterior' class='page-item'><a class='page-link' href='#" . $gets . "'&'" . $blocoNovo . "><span aria-hidden='true'>&lang;</span></a></li>";
                ?>
                <?= $paginacao ?>
                <?php

                echo "<li id='blocoNovo' class='page-item'><a class='page-link' href='#" . $gets . "'&'" . $blocoNovo . "><span aria-hidden='true'>&rang;</span></a></li>";

                echo "<li class='page-item'><a class='page-link' href='list_internacao_cap_finphp?pag=$qtdIntItens&" . $gets . "''><span aria-hidden='true'>&Rang;</span></a></li>";
                echo " </ul>";
                echo "</nav>";
                ?>
                <div id="maximo" style="display:none">
                    <p>Não localizado mais registros</p>
                </div>
                <hr>
            </div>


            <div>
                <a class="btn btn-success styled" href="internacoes/nova">Novo capeante</a>
            </div>
    </div>
    <!------------------------------------>
    <!-- JQUERY PARA MOSTRAR CAMPOS PESQUISA-->
    <!------------------------------------>
    <script>
        $(function() {
            $('#buttonId').bind('click', function() {
                $('#navbarToggleExternalContent').toggle('fast');

            });
        });
    </script>
    <!---------------------------------->
    <!-- SCRIPT JQUERY PARA NAVEGACAO -->
    <!---------------------------------->

    <script>
        // PEGAR O VALOR DOS GETS DA PAGINA //
        var hospital = "<?php print $pesquisa_nome; ?>"
        var paciente = "<?php print $pesquisa_pac; ?>"
        var internado = "<?php print $pesqInternado; ?>"
        var limite = "<?php print $limite; ?>"
        var ordenar = "<?php print $ordenar; ?>"
        var auditor = "<?php print $auditor; ?>"

        // PEGAR O VALOR DO GET E PASSAR PARA O BLOCO ATUAL //
        var blocoAtual = <?php if (isset($_GET['bl'])) {
                                print $_GET['bl'];
                            } else {
                                print 0;
                            }; ?>;
        var blocoNo = 0;

        // VALOR TOTAL DOS REGISTROS
        var totalReg = $("#qtd").val();
        var totalBloco = totalReg / 5;
        totalBloco = parseInt(totalBloco);

        // PEGAR VALOR DO DO GET E PASSAR PARA O [BL]
        var bl = <?php if (isset($_GET['bl'])) {
                        print $_GET['bl'];
                    } else {
                        print 0;
                    }; ?>;

        // VALORES INICIAIS DOS BOTOES DE NAVEGACAO //
        $('#button1').text(blocoNo + blocoAtual + 1);
        $('#button2').text(blocoNo + blocoAtual + 2);
        $('#button3').text(blocoNo + blocoAtual + 3);
        $('#button4').text(blocoNo + blocoAtual + 4);
        $('#button5').text(blocoNo + blocoAtual + 5);
        bloco1 = blocoNo + 1;
        bloco2 = blocoNo + 2;
        bloco3 = blocoNo + 3;
        bloco4 = blocoNo + 4;
        bloco5 = blocoNo + 5;

        $('#paginacao1').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco1 + '&bl=' + bl);
        $('#paginacao2').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco2 + '&bl=' + bl);
        $('#paginacao3').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco2 + '&bl=' + bl);
        $('#paginacao4').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco2 + '&bl=' + bl);
        $('#paginacao5').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco2 + '&bl=' + bl);

        // MUDAR O BOTAO DE NAVEGACAO PARA CIMA//
        $(function() {
            $('#blocoNovo').click(function() {

                if ((blocoNo + bl) < totalReg / 5) {
                    bl = bl + 5
                } else {
                    maximo.style.display = "flex";
                    setTimeout(function() {
                        maximo.style.display = "none"
                    }, 2000);
                }

                $('#button1').text(blocoNo + 1 + bl);
                bloco1 = blocoNo + 1 + blocoAtual;
                $('#paginacao1').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco1 + '&bl=' + bl);

                bloco2 = blocoNo + 2 + bl;
                $('#button2').text(blocoNo + 2 + bl);
                $('#paginacao2').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco2 + '&bl=' + bl);

                bloco3 = blocoNo + 3;
                $('#button3').text(blocoNo + 3 + bl);
                $('#paginacao3').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco3 + '&bl=' + bl);

                bloco4 = blocoNo + 4;
                $('#button4').text(blocoNo + 4 + bl);
                $('#paginacao4').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco4 + '&bl=' + bl);

                bloco5 = blocoNo + 5;
                $('#button5').text(blocoNo + 5 + bl);
                $('#paginacao5').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco5 + '&bl=' + bl);
            })
        });

        // MUDAR O BOTAO DE NAGEVACAO PARA BAIXO//

        $(function() {
            $('#blocoAnterior').click(function() {
                blocoNo = blocoNo - 5;
                if (blocoNo < 0) {
                    blocoNo = 0
                }
                bl = bl - 5

                $('#button1').text(blocoNo + 1 + bl);
                bloco1 = blocoNo + 1 + blocoAtual;
                $('#paginacao1').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco1 + '&bl=' + bl);

                bloco2 = blocoNo + 2 + bl;
                $('#button2').text(blocoNo + 2 + bl);
                $('#paginacao2').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco2 + '&bl=' + bl);

                bloco3 = blocoNo + 3;
                $('#button3').text(blocoNo + 3 + bl);
                $('#paginacao3').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco3 + '&bl=' + bl);

                bloco4 = blocoNo + 4;
                $('#button4').text(blocoNo + 4 + bl);
                $('#paginacao4').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco4 + '&bl=' + bl);

                bloco5 = blocoNo + 5;
                $('#button5').text(blocoNo + 5 + bl);
                $('#paginacao5').attr('href', 'list_internacao_cap_fin.php?pesquisa_nome=' + hospital + '&pesquisa_pac=' + paciente + '&pesqInternado=' + internado + '&limite_pag=' + limite + '&ordenar=' + ordenar + '&pag=' + bloco5 + '&bl=' + bl);
            })
        })
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
    </script>
