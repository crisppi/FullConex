<?php

require_once("templates/header.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/patologia.php");
include_once("dao/patologiaDao.php");

include_once("models/paciente.php");
include_once("dao/pacienteDao.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("models/gestao.php");
include_once("dao/gestaoDao.php");

include_once("models/pagination.php");

//inicializacao de variaveis

$order = null;
$obLimite = null;
$blocoNovo = null;

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$Internacaos = $Internacao_geral->findGeral();

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$gestaoDao = new gestaoDAO($conn, $BASE_URL);
$QtdTotalGes = new gestaoDAO($conn, $BASE_URL);
$gestaos = $gestaoDao->findGeral($limite, $inicio);

$senha_int = null;

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$internacao = new internacaoDAO($conn, $BASE_URL);

$limite = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
$ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : '';

?>

<!-- FORMULARIO DE PESQUISAS -->
<div class="container-fluid form_container" id='main-container' style="margin-top:12px;">
    <script src="./js/ajaxNav.js"></script>
    <h4 class="page-title">Gestão</h4>
    <hr>
    <style>
    .gestao-filter-bar {
        display: flex;
        gap: 10px;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .gestao-filter-bar .filter-item {
        flex: 0 0 auto;
        min-width: 140px;
    }
    .gestao-filter-bar .filter-item.wide {
        min-width: 200px;
    }
    .gestao-filter-bar .filter-item.compact {
        min-width: 120px;
    }
    @media (max-width: 1199px) {
        .gestao-filter-bar {
            flex-wrap: wrap;
        }
    }
    </style>
    <div class="complete-table">
        <div id="navbarToggleExternalContent" class="table-filters">

            <form action="" id="select-internacao-form" method="GET">
                <?php $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS);
                $pesqGestao = filter_input(INPUT_GET, 'pesqGestao');
                $limite_pag = filter_input(INPUT_GET, 'limite_pag') ?? 10;
                $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS);
                $pesquisa_matricula = filter_input(INPUT_GET, 'pesquisa_matricula', FILTER_SANITIZE_SPECIAL_CHARS);
                $ordenar = filter_input(INPUT_GET, 'ordenar');
                $data_intern_int = filter_input(INPUT_GET, 'data_intern_int') ?: null;
                $data_intern_int_max = filter_input(INPUT_GET, 'data_intern_int_max') ?: null;
                ?>

                <div class="gestao-filter-bar" style="padding-left:16px;">
                    <div class="filter-item wide">
                        <select class="form-control mb-2 form-control-sm" id="pesqGestao" name="pesqGestao"
                            style="margin-top:7px;font-size:.8em; color:#878787">
                            <option value="">Selecione a Gestão</option>
                            <option value="home_care" <?= $pesqGestao == 'home_care' ? 'selected' : null ?>>Home care
                            </option>
                            <option value="desospitalizacao" <?= $pesqGestao == 'n' ? 'selected' : null ?>>
                                Desospitalização</option>0fdr2bnt
                            <option value="opme" <?= $pesqGestao == 'opme' ? 'selected' : null ?>>Opme</option>
                            <option value="alto" <?= $pesqGestao == 'alto' ? 'selected' : null ?>>Alto custo</option>
                        </select>
                    </div>
                    <div class="filter-item wide">
                        <input style="margin-top:7px;font-size:.8em; color:#878787" class="form-control form-control-sm"
                            type="text" name="pesquisa_nome" placeholder="Selecione o Hospital"
                            value="<?= $pesquisa_nome ?>">
                    </div>
                    <div class="filter-item wide">
                        <input style="margin-top:7px;font-size:.8em; color:#878787" class="form-control form-control-sm"
                            type="text" name="pesquisa_pac" placeholder="Selecione o Paciente"
                            value="<?= $pesquisa_pac ?>">
                    </div>
                    <div class="filter-item wide">
                        <input style="margin-top:7px;font-size:.8em; color:#878787" class="form-control form-control-sm"
                            type="text" name="pesquisa_matricula" placeholder="Matrícula"
                            value="<?= htmlspecialchars((string)$pesquisa_matricula) ?>">
                    </div>
                    <div class="filter-item compact">
                        <input style="margin-top:7px;font-size:.8em; color:#878787" class="form-control form-control-sm"
                            type="text" name="senha_int" placeholder="Senha" value="<?= $senha_int ?>">
                    </div>
                    <div class="filter-item compact">
                        <select class="form-control form-control-sm placeholder"
                            style="margin-top:7px;font-size:.8em; color:#878787" id="pesqInternado"
                            name="pesqInternado">
                            <option value="">Internados</option>
                            <option value="s" <?= $pesqInternado == 's' ? 'selected' : null ?>>Sim</option>
                            <option value="n" <?= $pesqInternado == 'n' ? 'selected' : null ?>>Não</option>
                        </select>
                    </div>
                    <div class="filter-item compact">
                        <select class="form-control mb-3 form-control-sm" style="margin-top:7px;" id="limite"
                            name="limite">
                            <option value="">Registros</option>
                            <option value="5" <?= $limite == '5' ? 'selected' : null ?>>5</option>
                            <option value="10" <?= $limite == '10' ? 'selected' : null ?>>10</option>
                            <option value="20" <?= $limite == '20' ? 'selected' : null ?>>20</option>
                            <option value="50" <?= $limite == '50' ? 'selected' : null ?>>50</option>
                        </select>
                    </div>
                    <div class="filter-item compact">
                        <select style="margin-top:7px;font-size:.8em; color:#878787"
                            class="form-control mb-3 form-control-sm" id="ordenar" name="ordenar">
                            <option value="">Classificar</option>
                            <option value="id_internacao" <?= $ordenar == 'id_internacao' ? 'selected' : null ?>>
                                Internação</option>
                            <option value="nome_pac" <?= $ordenar == 'nome_pac' ? 'selected' : null ?>>Paciente
                            </option>
                            <option value="nome_hosp" <?= $ordenar == 'nome_hosp' ? 'selected' : null ?>>Hospital
                            </option>
                        </select>
                    </div>
                    <div class="filter-item compact">
                        <input class="form-control form-control-sm" type="date"
                            style="margin-top:7px;font-size:.8em; color:#878787" name="data_intern_int"
                            placeholder="Data Internação Min" value="<?= $data_intern_int ?>">
                    </div>
                    <div class="filter-item compact">
                        <input class="form-control form-control-sm" type="date"
                            style="margin-top:7px;font-size:.8em; color:#878787" name="data_intern_int_max"
                            placeholder="Data Internação Max" value="<?= $data_intern_int_max ?>">
                    </div>
                    <div class="filter-item compact" style="min-width:90px">
                        <button type="submit" class="btn btn-primary w-100"
                            style="background-color:#5e2363;margin-top:7px;border-color:#5e2363">
                            <span class="material-icons" style="font-size:1rem;vertical-align:middle;">search</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- BASE DAS PESQUISAS -->

            <?php
            // SELECAO DA ENTRADA DO INPUT DE PESQUISA GESTAO
            $pesqGestao = filter_input(INPUT_GET, 'pesqGestao');
            // validacao de lista de hospital por usuario (o nivel sera o filtro)
            if ($_SESSION['nivel'] == 3) {
                $auditor = ($_SESSION['id_usuario']);
            } else {
                $auditor = null;
            };

            $test = isset($_GET);
            if ($test); {
                if ($pesqGestao == 'home_care') {
                    $gestaoHome = "s";
                } else {
                    $gestaoHome = null;
                }
            };
            if ($test); {
                if ($pesqGestao == 'desospitalizacao') {
                    $gestaoDesop = "s";
                } else {
                    $gestaoDesop = null;
                }
            };
            if ($test); {
                if ($pesqGestao == 'opme') {
                    $gestaoOPME = "s";
                } else {
                    $gestaoOPME = null;
                }
            };
            if ($test); {
                if ($pesqGestao == 'alto') {
                    $gestaoAlto = "s";
                } else {
                    $gestaoAlto = null;
                }
            };

            $QtdTotalInt = new internacaoDAO($conn, $BASE_URL);

            // METODO DE BUSCA DE PAGINACAO
            // validacao de lista de hospital por usuario (o nivel sera o filtro)
            if ($_SESSION['nivel'] == 3) {
                $auditor = ($_SESSION['id_usuario']);
            } else {
                $auditor = null;
            };
            $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
            $senha_int = filter_input(INPUT_GET, 'senha_int');
            $pesqInternado = filter_input(INPUT_GET, 'pesqInternado');
            $limite_pag = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
            $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
            $pesquisa_matricula = filter_input(INPUT_GET, 'pesquisa_matricula');
            $ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : '';
            $data_intern_int = filter_input(INPUT_GET, 'data_intern_int');
            $data_intern_int_max = filter_input(INPUT_GET, 'data_intern_int_max');
            if (empty($data_intern_int_max)) {
                $data_intern_int_max = date('Y-m-d'); // Formato de data compatível com SQL
            }

            $condicoes = [
                strlen($pesquisa_nome) ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"' : null,
                strlen($pesquisa_pac) ? 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"' : null,
                strlen($pesquisa_matricula) ? 'pa.matricula_pac LIKE "%' . $pesquisa_matricula . '%"' : null,
                strlen($senha_int) ? 'senha_int LIKE "%' . $senha_int . '%"' : null,
                strlen($pesqInternado) ? 'internado_int = "' . $pesqInternado . '"' : NULL,
                strlen($gestaoAlto) ? 'alto_custo_ges = "' . $gestaoAlto . '"' : NULL,
                strlen($gestaoOPME) ? 'opme_ges = "' . $gestaoOPME . '"' : NULL,
                strlen($gestaoDesop) ? 'desospitalizacao_ges = "' . $gestaoDesop . '"' : NULL,
                strlen($gestaoHome) ? 'home_care_ges = "' . $gestaoHome . '"' : NULL,
                strlen($auditor) ? 'hos.fk_usuario_hosp = "' . $auditor . '"' : NULL,
                strlen($data_intern_int) ? 'data_intern_int BETWEEN "' . $data_intern_int . '" AND "' . $data_intern_int_max . '"' : NULL,


            ];

            $condicoes = array_filter($condicoes);
            // REMOVE POSICOES VAZIAS DO FILTRO
            $where = implode(' AND ', $condicoes);
            $order = $ordenar ?: 'id_internacao DESC';

            $qtdGesItens1 = $QtdTotalGes->selectAllGestaoLis($where, $order, $obLimite);

            $qtdIntItens = count($qtdGesItens1); // total de registros
            $totalcasos = ceil($qtdIntItens / $limite);

            $qtdLinksPagina = ($totalcasos / 5) + 1;

            // PAGINACAO
            $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);
            $obLimite = $obPagination->getLimit();
            $paginacao = '';
            $paginas = $obPagination->getPages();
            $query = $gestaoDao->selectAllGestaoLis($where, $order, $obLimite);
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
            <div>
                <div id="table-content">
                    <!-- <?php include_once("check_nivel.php");
                            ?> -->
                    <table class="table table-sm table-striped  table-hover table-condensed">
                        <thead>
                            <tr>
                                <th scope="col">Id-Int</th>
                                <th scope="col">Internado</th>
                                <th scope="col">Hospital</th>
                                <th scope="col">Paciente</th>
                                <th scope="col">Senha</th>
                                <th scope="col">Data internação</th>
                                <th scope="col">Home care</th>
                                <th scope="col">Desospitalização</th>
                                <th scope="col">OPME</th>
                                <th scope="col">Alto Custo</th>
                                <th scope="col">Evento Adverso</th>
                                <th scope="col">Ações</th>
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
                                    <?= $intern["senha_int"] ?>
                                </td>
                                <td scope="row">
                                    <?= date('d/m/Y', strtotime($intern["data_intern_int"])) ?>
                                </td>
                                <td scope="row">
                                    <?php if ($intern["home_care_ges"] == "s") { ?>
                                    <a
                                        href="<?= $BASE_URL ?>show_gestao.php?id_gestao=<?= $gestaos['0']["id_gestao"] ?>"><i
                                            style="color:red; font-size:1.4em" class="bi bi-house-door">
                                        </i></a>
                                    <?php } else {
                                            echo "--";
                                        }; ?>
                                </td>
                                <td scope="row">
                                    <?php if ($intern["desospitalizacao_ges"] == "s") { ?>
                                    <a
                                        href="<?= $BASE_URL ?>show_gestao.php?id_gestao=<?= $gestaos['0']["id_gestao"] ?>"><i
                                            style="color:orange; font-size:1.5em" class="bi bi-house-up">
                                        </i></a>
                                    <?php } else {
                                            echo "--";
                                        }; ?>
                                </td>
                                <td scope="row">
                                    <?php if ($intern["opme_ges"] == "s") { ?>
                                    <a
                                        href="<?= $BASE_URL ?>show_gestao.php?id_gestao=<?= $gestaos['0']["id_gestao"] ?>"><i
                                            style="color:gray; font-size:1.4em" class="fas fa-procedures">
                                        </i></a>
                                    <?php } else {
                                            echo "--";
                                        }; ?>
                                </td>
                                <td scope="row">
                                    <?php if ($intern["alto_custo_ges"] == "s") { ?>
                                    <a
                                        href="<?= $BASE_URL ?>show_gestao.php?id_gestao=<?= $gestaos['0']["id_gestao"] ?>"><i
                                            style="color:green; font-size:1.4em" class="fas fa-dollar-sign">
                                        </i></a>
                                    <?php } else {
                                            echo "--";
                                        }; ?>
                                </td>
                                <td scope="row">
                                    <?php if ($intern["evento_adverso_ges"] == "s") { ?>
                                    <a
                                        href="<?= $BASE_URL ?>show_gestao.php?id_gestao=<?= $gestaos['0']["id_gestao"] ?>"><i
                                            style="color:blue; font-size:1.4em" class="bi bi-shield-exclamation">
                                        </i></a>
                                    <?php
                                        } else {
                                            echo "--";
                                        }; ?>
                                </td>

                                <td class="action">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" id="navbarScrollingDropdown"
                                            role="button" data-bs-toggle="dropdown" style="color:#5e2363"
                                            aria-expanded="false">
                                            <i class="bi bi-stack"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                            <button style="font-size:.9rem;" class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>show_gestao.php?id_gestao=<?= $intern['id_gestao'] ?>')"><i
                                                    class="fas fa-eye"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                Ver</button>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($qtdIntItens == 0): ?>
                            <tr>
                                <td colspan="12" scope="row" class="col-id" style='font-size:15px'>
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


                    <!-- paginacao que aparece abaixo da tabela -->
                    <div style="display: flex;margin-top:20px">
                        <div class="table-new-btn">
                            <a class="btn btn-success styled"
                                style="background-color: #35bae1;font-family:var(--bs-font-sans-serif);box-shadow: 0px 10px 15px -3px rgba(0,0,0,0.1);border:none"
                                href="internacoes/nova"><i class="fa-solid fa-plus" style='font-size: 1rem;'></i>Nova
                                internação</a>
                        </div>

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
                                        onclick="loadContent('list_gestao.php?pesqGestao=<?php echo $pesqGestao; ?>&pesquisa_nome=<?php echo $pesquisa_nome; ?>&data_intern_int=<?php print $data_intern_int ?>&senha_int=<?php echo $senha_int; ?>&pesquisa_pac=<?php echo $pesquisa_pac; ?>&limite_pag=<?php echo $limite_pag; ?>&ordenar=<?php echo $ordenar; ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>')">
                                        <i class="fa-solid fa-angles-left"></i></a>
                                </li>
                                <?php endif; ?>
                                <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="#"
                                        onclick="loadContent('list_gestao.php?pesqGestao=<?php echo $pesqGestao; ?>&pesquisa_nome=<?php echo $pesquisa_nome; ?>&pesquisa_pac=<?php echo $pesquisa_pac; ?>&data_intern_int=<?php print $data_intern_int ?>&senha_int=<?php echo $senha_int; ?>&limite_pag=<?php echo $limite_pag; ?>&ordenar=<?php echo $ordenar; ?>&pag=<?php print $paginaAtual - 1 ?>&bl=<?php print $blocoAtual - 5 ?>')">
                                        <i class="fa-solid fa-angle-left"></i> </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++): ?>
                                <li class="page-item <?php print ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">

                                    <a class="page-link" href="#"
                                        onclick="loadContent('list_gestao.php?pesqGestao=<?php echo $pesqGestao; ?>&pesquisa_nome=<?php echo $pesquisa_nome; ?>&data_intern_int=<?php print $data_intern_int ?>&senha_int=<?php echo $senha_int; ?>&pesquisa_pac=<?php echo $pesquisa_pac; ?>&limite_pag=<?php echo $limite_pag; ?>&ordenar=<?php echo $ordenar; ?>&pag=<?php echo $i; ?>&bl=<?php echo $blocoAtual; ?>')">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($current_block < $last_block): ?>
                                <li class="page-item">
                                    <a class="page-link" id="blocoNovo" href="#"
                                        onclick="loadContent('list_gestao.php?pesqGestao=<?php echo $pesqGestao; ?>&pesquisa_nome=<?php echo $pesquisa_nome; ?>&data_intern_int=<?php print $data_intern_int ?>&senha_int=<?php echo $senha_int; ?>&pesquisa_pac=<?php echo $pesquisa_pac; ?>&limite_pag=<?php echo $limite_pag; ?>&ordenar=<?php echo $ordenar; ?>&pag=<?php echo $i; ?>&bl=<?php print $blocoAtual + 5 ?>')"><i
                                            class="fa-solid fa-angle-right"></i></a>
                                </li>
                                <?php endif; ?>
                                <?php if ($current_block < $last_block): ?>
                                <li class="page-item">
                                    <a class="page-link" id="blocoNovo" href="#"
                                        onclick="loadContent('list_gestao.php?pesqGestao=<?php echo $pesqGestao; ?>&pesquisa_nome=<?php echo $pesquisa_nome; ?>&data_intern_int=<?php print $data_intern_int ?>&senha_int=<?php echo $senha_int; ?>&pesquisa_pac=<?php echo $pesquisa_pac; ?>&limite_pag=<?php echo $limite_pag; ?>&ordenar=<?php echo $ordenar; ?>&pag=<?php print count($paginas) ?>&bl=<?php print ($last_block - 1) * 5 ?>')"><i
                                            class="fa-solid fa-angles-right"></i></a>
                                </li>
                                <?php endif; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <div class="table-counter">
                            <p
                                style="font-size:1em; font-weight:600; font-family:var(--bs-font-sans-serif); text-align:right">
                                <?php echo "Total: " . $qtdIntItens ?>
                            </p>
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
            'list_gestao.php?pesqGestao=<?php echo $pesqGestao; ?>&pesquisa_nome=<?php echo $pesquisa_nome; ?>&pesquisa_pac=<?php echo $pesquisa_pac; ?>&senha_int=<?php echo $senha_int; ?>&limite_pag=<?php echo $limite_pag; ?>&ordenar=<?php echo $ordenar; ?>&pag=<?php echo 1; ?>&bl=<?php echo 0 ?>'
        );
    });
    </script>


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
    <script src="./scripts/cadastro/general.js"></script>
