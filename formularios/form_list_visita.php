<?php

require_once("templates/header.php");

require_once("models/message.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/patologia.php");
include_once("dao/patologiaDao.php");

include_once("models/paciente.php");
include_once("dao/pacienteDao.php");

include_once("models/gestao.php");
include_once("dao/gestaoDao.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("models/visita.php");
include_once("dao/visitaDao.php");

include_once("models/usuario.php");
include_once("dao/usuarioDao.php");

include_once("models/pagination.php");

// inicializacao de variaveis
$data_intern_int = null;
$order = null;
$obLimite = null;
$blocoNovo = null;
$where = null;
$Internacao_geral = new internacaoDAO($conn, $BASE_URL);

$pacienteDao = new pacienteDAO($conn, $BASE_URL);

$gestaoDao = new gestaoDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$internacao = new internacaoDAO($conn, $BASE_URL);

$internacaoDAO = new internacaoDAO($conn, $BASE_URL);
$visitaDao = new visitaDAO($conn, $BASE_URL);


$limite_pag = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
$limite = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
$ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;
$pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
$pesqInternado = filter_input(INPUT_GET, 'pesqInternado') ? filter_input(INPUT_GET, 'pesqInternado') : 's';
$limite = filter_input(INPUT_GET, 'limite_pag');
$pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
?>

<!-- FORMULARIO DE PESQUISAS -->
<div class="container" id='main-container' style="margin-top:12px;">

    <!-- script jquery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="./js/ajaxNav.js"></script>
    <script src="./js/load/form_list_visita.js"></script>
    <div class="container">
        <h4 class="page-title" style="color: #3A3A3A">Listagem - Visita</h4>
    </div>
    <hr>

    <div class="container" id="navbarToggleExternalContent">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <form action="" id="select-internacao-form" method="GET">
            <div class="form-group row">
                <div class="form-group col-sm-3">
                    <input class="form-control form-control-sm" type="text"
                        style="margin-top:7px;font-size:.8em; color:#878787" name="pesquisa_nome"
                        placeholder="Selecione o Hospital" autofocus value="<?= $pesquisa_nome ?>">
                </div>
                <div class="form-group col-sm-3">
                    <input class="form-control form-control-sm" type="text"
                        style="margin-top:7px;font-size:.8em; color:#878787" name="pesquisa_pac"
                        placeholder="Selecione o Paciente" value="<?= $pesquisa_pac ?>">
                </div>

                <div class="form-group col-sm-2">
                    <select class="form-control sm-3 form-control-sm placeholder col-12"
                        style="margin-top:7px;font-size:.8em; color:#878787" id="pesqInternado" name="pesqInternado">
                        <option value="">Busca por Internados</option>
                        <option value="s" <?= $pesqInternado == 's' ? 'selected' : null ?>>Sim</option>
                        <option value="n" <?= $pesqInternado == 'n' ? 'selected' : null ?>>Não</option>
                    </select>
                </div>
                <div class="col-sm-1" style="padding:2px !important">
                    <select class="form-control mb-3 form-control-sm" style="margin-top:7px;" id="limite" name="limite">
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
                <div class="form-group col-sm-2">
                    <select class="form-control sm-3 form-control-sm"
                        style="margin-top:7px;font-size:.8em; color:#878787" id="ordenar" name="ordenar">
                        <option value="">Classificar por</option>
                        <option value="nome_pac" <?= $ordenar == 'nome_pac' ? 'selected' : null ?>>Paciente</option>
                        <option value="nome_hosp" <?= $ordenar == 'nome_hosp' ? 'selected' : null ?>>Hospital</option>
                        <option value="id_internacao" <?= $ordenar == 'id_internacao' ? 'selected' : null ?>>Internação
                        </option>
                        <option value="data_intern_int" <?= $ordenar == 'data_intern_int' ? 'selected' : null ?>>Data
                            Internação</option>
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
        <!-- BASE DAS PESQUISAS -->
        <?php

        // validacao de lista de hospital por usuario (o nivel sera o filtro)
        if ($_SESSION['nivel'] == 3) {
            $auditor = ($_SESSION['id_usuario']);
        } else {
            $auditor = null;
        };
        //Instanciando a classe
        $QtdTotalInt = new internacaoDAO($conn, $BASE_URL);
        $cargo = $_SESSION['cargo'];

        // METODO DE BUSCA DE PAGINACAO 
        $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
        $pesqInternado = filter_input(INPUT_GET, 'pesqInternado') ?: "s";
        $limite = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
        $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
        $data_intern_int = filter_input(INPUT_GET, 'data_intern_int');
        $ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;

        $condicoes = [
            strlen($pesquisa_nome) ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"' : null,
            strlen($pesquisa_pac) ? 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"' : null,
            strlen($pesqInternado) ? 'internado_int = "' . $pesqInternado . '"' : null,
            strlen($data_intern_int) ? 'data_intern_int = "' . $data_intern_int . '"' : NULL,
            // strlen($cargo) ? ' se.cargo_user = " ' . $cargo . ' " '  : null,
            // strlen($auditor) ? 'hos.fk_usuario_hosp = "' . $auditor . '"' : NULL,

        ];

        $condicoes = array_filter($condicoes);

        // REMOVE POSICOES VAZIAS DO FILTRO
        $where = implode(' AND ', $condicoes);
        // QUANTIDADE Internacao
        $qtdIntItens1 = $QtdTotalInt->selectAllInternacaoCountVis($where, $order, $obLimite);

        $qtdIntItens = count($qtdIntItens1);
        $totalcasos = ceil($qtdIntItens / $limite);

        // PAGINACAO
        $order = $ordenar;

        $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);

        $obLimite = $obPagination->getLimit();

        // PREENCHIMENTO DO FORMULARIO COM QUERY
        $query = $internacao->selectAllInternacaoCountVis($where, $order, $obLimite);
        // $visitas = $visitaDao->joinVisitaInternacao($where);
        $contarVis = 0;

        // PAGINACAO
        if ($qtdIntItens > $limite) {
            $paginacao = '';
            $paginas = $obPagination->getPages();
            $pagina = 1;
            $total_pages = count($paginas);
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
        <!-- TABELA DE REGISTROS -->
        <div class="container" id="table-content">
            <div class="row">

                <table class="table table-sm table-striped  table-hover table-condensed">

                    <thead>
                        <tr>
                            <th scope="col" width="4%">Id</th>
                            <th scope="col" width="5%">Internado</th>
                            <th scope="col" width="15%">Hospital</th>
                            <th scope="col" width="18%">Paciente</th>
                            <th scope="col" width="6%">Data int</th>
                            <th scope="col" width="6%">Senha</th>
                            <th scope="col" width="6%">Dias Int</th>
                            <th scope="col" width="8%">Últ Visita</th>
                            <th scope="col" width="8%">Dias Visita</th>
                            <th scope="col" width="6%">No Visita</th>
                            <th scope="col" width="6%">Vis Enf</th>
                            <th scope="col" width="6%">Vis Med</th>
                            <th scope="col" width="5%">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        foreach ($query as $intern) :
                            extract($query);
                            $visitas = $visitaDao->joinVisitaInternacao($intern["id_internacao"]);
                            $hoje = date('Y-m-d');
                            $atual = new DateTime($hoje);

                            $datainternacao = date("Y-m-d", strtotime($intern['data_intern_int']));
                            $dataIntern = new DateTime($datainternacao);

                            $dataVisita = date("Y-m-d", strtotime($intern['data_visita_vis']));

                            // $dataVisitaInt = date("Y-m-d", strtotime($intern['data_visita_int']));
                            // $dataVisInternacao = new DateTime($dataVisitaInt);

                            $dataVisitaInt = date("Y-m-d", strtotime($intern['data_visita_int']));

                            $dataVisInternacao = new DateTime($dataVisitaInt);

                            $diasIntern = $dataIntern->diff($atual); //calcular dias de internacao

                            $countVisitas = count($visitas);

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
                            <td scope="row" style="font-weight:bolder;">
                                <?= $intern["nome_hosp"] ?>
                            </td>
                            <td scope="row">
                                <?= $intern["nome_pac"] ?>
                            </td>
                            <td scope="row">
                                <?= date('d/m/Y', strtotime($intern["data_intern_int"])) ?>
                            </td>
                            <td scope="row">
                                <?= $intern["senha_int"] ?>
                            </td>
                            <td scope="row">
                                <?= $diasIntern->days ?>
                            </td>
                            <td scope="row">
                                <?php if ($intern["censo_int"] == "n") {
                                        // Define a comparison function

                                        extract($visitas);

                                        usort($visitas, function ($a, $b) {
                                            return $a['data_visita_vis'] > $b['data_visita_vis'];
                                        });

                                        if ($visitas) {
                                            print(date('d/m/Y', strtotime(end($visitas)['data_visita_vis'])));
                                        }
                                    } ?>
                            </td>
                            <td scope="row" style="font-weight:800">

                                <?php
                                    if ($visitas) {
                                        if ($intern['censo_int'] == "s") {
                                            $qtdDias = $dataIntern->diff($atual);
                                            $qt = $qtdDias->days; ?>
                                <p style="font-size:1em;color:red; font-weight:800">
                                    <?php
                                                print_r($qt);
                                                ?>
                                    <?php
                                        } else {
                                            $interMax = ($intern);
                                            $dataVisitaInt = date("Y-m-d", strtotime($interMax['data_visita_vis']));
                                            $dataVisInternacao = new DateTime($dataVisitaInt);
                                            $diasVisita = $dataVisInternacao->diff($atual); // calcular dias da visita
                                            $qtDiasVisita = $diasVisita->days;
                                            print_r($qtDiasVisita);
                                        }
                                    }
                                        ?>
                                </p>
                            </td>
                            <td scope="row">
                                <?php
                                    echo $countVisitas
                                    ?>
                            </td>
                            <td scope="row">
                                <?php

                                    $id_internacao3 = $intern['id_internacao'];
                                    $cargo = $_SESSION['cargo'];
                                    // $cargo = "Enf_Auditor";

                                    $condicoesVisEnf = [
                                        strlen($id_internacao3) ? 'vi.fk_internacao_vis = ' . $id_internacao3 . ' ' : null,
                                        // strlen($cargo) ? 'se.cargo_user = "' . $cargo . '" ' : null,

                                    ];

                                    $condicoesVisEnf = array_filter($condicoesVisEnf);
                                    // REMOVE POSICOES VAZIAS DO FILTRO

                                    $wherevisita = implode(' AND ', $condicoesVisEnf);

                                    $internacaoEnf = $internacao->selectInternVisCargo($wherevisita);

                                    if ($internacaoEnf) {
                                        print(date('d/m/Y', strtotime($internacaoEnf[0]['data_visita_vis'])));
                                    } else
                                        print_r("--");
                                    ?>
                            </td>
                            <td scope="row">
                                <?php
                                    if ($intern['internado_uti'] == 's') {
                                    ?><a href=""><i style="color:blue; font-size:.9em"
                                        class="bi bi-clipboard2-check-fill">
                                    </i></a>
                                <?php
                                    } else
                                        print_r("--");
                                    ?>
                            </td>
                            <td class="action">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" id="navbarScrollingDropdown"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="color:#5e2363">
                                        <i class="bi bi-stack"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                        <li>
                                            <?php if ($pesqInternado == "s" and $intern['censo_int'] <> "s") { ?>
                                            <button style="font-size: .9rem;" class="btn btn-default"
                                                onclick="edit('<?php echo ($intern['rel_visita_vis'] < 0) ? $BASE_URL . "show_internacao.php?id_internacao=" . $intern['id_internacao'] : $BASE_URL . "show_internacao.php?id_internacao=" . $intern['id_visita']; ?>')">
                                                <i class="fas fa-eye"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                Ver
                                            </button>

                                            <?php }; ?>
                                        </li>
                                        <li>
                                            <?php if ($pesqInternado == "s" and $intern['censo_int'] == "s") { ?>
                                            <button style="font-size: .9rem;" class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>cad_internacao_censo.php?id_internacao=<?= $intern['id_internacao'] ?>')">
                                                <i name="type" value="update" class="bi bi-file-text"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);">
                                                </i>Rel Inicial
                                            </button>
                                            <?php }; ?>
                                        </li>
                                        <li>
                                            <?php
                                                if ($intern['censo_int'] == "n") {
                                                    if ($pesqInternado == "s") { ?>
                                            <button style="font-size: .9rem;" class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>cad_visita.php?id_internacao=<?= $intern['id_internacao'] ?>')"><i
                                                    name="type" value="visita"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"
                                                    class="bi bi-file-text">
                                                </i>Visita</button>
                                            <?php }; ?>
                                            <?php }; ?>
                                        </li>
                                        <li>
                                            <?php if ($pesqInternado == "s") { ?>
                                            <form class="d-inline-block delete-form" action="edit_alta.php"
                                                method="get">
                                                <input type="hidden" name="type" value="alta">
                                                <input type="hidden" name="id_internacao"
                                                    value="<?= $intern["id_internacao"] ?>">
                                                <button style="font-size: .9rem;" class="btn btn-default"><i
                                                        class="bi bi-door-open"
                                                        style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);">
                                                    </i>Alta</button>
                                            </form></a>
                                            <?php }; ?>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if ($qtdIntItens == 0) : ?>
                        <tr>
                            <td colspan="13" scope="row" class="col-id" style='font-size:15px'>
                                Não foram encontrados registros
                            </td>
                        </tr>

                        <?php endif ?>
                    </tbody>
                </table>

                <div style="text-align:right">
                    <input type="hidden" id="qtd" value="<?php echo $qtdIntItens ?>">
                </div>
                <div class="container" style="display: flex;">
                    <div>
                        <a class="btn btn-success styled"
                            style="background-color: #35bae1;font-family:var(--bs-font-sans-serif);box-shadow: 0px 10px 15px -3px rgba(0,0,0,0.1);border:none"
                            href="cad_internacao.php">Nova internação</a>
                    </div>

                    <!-- Modal para abrir tela de cadastro -->
                    <div class="modal fade" id="myModal">
                        <div class="modal-dialog  modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="page-title" style="color:white;">Cadastrar Internação</h4>
                                    <p class="page-description" style="color:white; margin-top:5px">Adicione informações
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
                        <?php if ($total_pages ?? 1 > 1) : ?>
                        <ul class="pagination">
                            <?php
                                $blocoAtual = isset($_GET['bl']) ? $_GET['bl'] : 0;
                                $paginaAtual = isset($_GET['pag']) ? $_GET['pag'] : 1;
                                ?>
                            <?php if ($current_block > $first_block) : ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>')">
                                    <i class="fa-solid fa-angles-left"></i></a>
                            </li>
                            <?php endif; ?>
                            <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1) : ?>
                            <li class="page-item">
                                <a class="page-link" href="#"
                                    onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual - 1 ?>&bl=<?php print $blocoAtual - 5 ?>')">
                                    <i class="fa-solid fa-angle-left"></i> </a>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++) : ?>
                            <li class="page-item <?php print ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">

                                <a class="page-link" href="#"
                                    onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $i ?>&bl=<?php print $blocoAtual ?>')">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($current_block < $last_block) : ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual + 1 ?>&bl=<?php print $blocoAtual + 5 ?>')"><i
                                        class="fa-solid fa-angle-right"></i></a>
                            </li>
                            <?php endif; ?>
                            <?php if ($current_block < $last_block) : ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print count($paginas) ?>&bl=<?php print ($last_block - 1) * 5 ?>')"><i
                                        class="fa-solid fa-angles-right"></i></a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </div>

                    <div>
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
                console.log(response)
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
        'list_visita.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>'
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
<?php
require_once("templates/footer.php");
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" ;>
</script>
<script src="./scripts/cadastro/general.js"></script>