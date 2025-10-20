<?php
$start = microtime(true); // Marca o início da execução da página

ob_start(); // Output Buffering Start

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

include_once("models/visita.php");
include_once("dao/visitaDao.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("models/pagination.php");

// inicializacao de variaveis
$data_intern_int = null;
$order = null;
$obLimite = null;
$blocoNovo = null;
$senha_int = null;
$where = null;
$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$Internacaos = $Internacao_geral->findGeral();


$pacienteDao = new pacienteDAO($conn, $BASE_URL);

$gestaoDao = new gestaoDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$internacao = new internacaoDAO($conn, $BASE_URL);
$visitaDao = new visitaDAO($conn, $BASE_URL);
$limite_pag = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
$limite = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
$ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;

?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<!-- FORMULARIO DE PESQUISAS -->
<div class="container-fluid" id='main-container' style="margin-top: -25px;">
    <!-- Main CSS-->
    <!-- <link href="./diversos/CoolAdmin-master/css/theme.css" rel="stylesheet"  media="all"> -->
    <script src="./js/ajaxNav.js"></script>
    <!-- <div class="container"> -->
    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 10px;">
        <h4 class="page-title" style="color: #3A3A3A;">Listagem - Internação</h4>

        <!-- Botões lado a lado -->
        <div class="d-flex">
            <!-- Botão de Exportar para Excel -->
            <a href="<?= $BASE_URL ?>exportar_excel_list_intern.php" class="btn btn-success"
                style="border-radius:10px; margin-right: 10px;">Exportar para
                Excel</a>

            <!-- Botão de Nova Internação -->
            <a class="btn btn-success" href="<?= $BASE_URL ?>cad_internacao.php"
                style="border-radius:10px;background-color:#35bae1;font-family:var(--bs-font-sans-serif);box-shadow:0px 10px 15px -3px rgba(0,0,0,0.1);border:none">
                <i class="fa-solid fa-plus" style="font-size:1rem;margin-right:5px;"></i>
                Nova Internação
            </a>

        </div>
    </div>

    <hr style="margin-top: 1px; margin-bottom: 10px;">
    <div class="complete-table">
        <div id="navbarToggleExternalContent" class="table-filters">
            <form action="" id="select-internacao-form" method="GET">
                <?php $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
                $pesqInternado = filter_input(INPUT_GET, 'pesqInternado') ? filter_input(INPUT_GET, 'pesqInternado') : 's';
                $limite = filter_input(INPUT_GET, 'limite_pag');
                $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
                $ordenar = filter_input(INPUT_GET, 'ordenar');
                $data_intern_int = filter_input(INPUT_GET, 'data_intern_int') ?: null;
                $data_intern_int_max = filter_input(INPUT_GET, 'data_intern_int_max') ?: null;
                ?>
                <div class="form-group row">
                    <div class="form-group col-sm-2" style="padding:2px;padding-left:16px !important;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="pesquisa_nome" placeholder="Selecione o Hospital" value="<?= $pesquisa_nome ?>">
                    </div>
                    <div class="form-group col-sm-2" style="padding:2px;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="pesquisa_pac" placeholder="Selecione o Paciente" value="<?= $pesquisa_pac ?>">
                    </div>
                    <div class="form-group col-sm-1" style="padding:2px;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="senha_int" placeholder="Senha" value="<?= $senha_int ?>">
                    </div>
                    <div class="col-sm-1" style="padding:2px !important">
                        <select class="form-control mb-3 form-control-sm" style="color:#878787;margin-top:7px;"
                            id="limite" name="limite_pag">
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
                    <div class="form-group col-sm-1" style="padding:2px;">
                        <select class="form-control form-control-sm" style="color:#878787;margin-top:7px;" id="ordenar"
                            name="ordenar">
                            <option value="">Classificar</option>
                            <option value="nome_pac" <?= $ordenar == 'nome_pac' ? 'selected' : null ?>>Paciente
                            </option>
                            <option value="nome_hosp" <?= $ordenar == 'nome_hosp' ? 'selected' : null ?>>Hospital
                            </option>
                            <option value="id_internacao" <?= $ordenar == 'id_internacao' ? 'selected' : null ?>>
                                Internação</option>
                            <option value="data_intern_int" <?= $ordenar == 'data_intern_int' ? 'selected' : null ?>>
                                Data Internação
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-sm-1" style="padding:2px;">
                        <input class="form-control form-control-sm" type="date" style="color:#878787;margin-top:7px;"
                            name="data_intern_int" placeholder="Data Internação Min" value="<?= $data_intern_int ?>">
                    </div>
                    <div class="form-group col-sm-1" style="padding:2px;">
                        <input class="form-control form-control-sm" type="date" style="color:#878787;margin-top:7px;"
                            name="data_intern_int_max" placeholder="Data Internação Max"
                            value="<?= $data_intern_int_max ?>">
                    </div>
                    <div class="form-group col-sm-1" style="padding:2px;">
                        <button type="submit" class="btn btn-primary"
                            style="background-color:#5e2363;width:42px;height:32px;border-color:#5e2363;margin-top:7px;">
                            <span class="material-icons" style="margin-left:-3px;margin-top:-2px;">
                                search
                            </span>
                        </button>
                        <!-- </div> -->
                        <!-- </div> -->
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
        //Instanciando a classe
        $QtdTotalInt = new internacaoDAO($conn, $BASE_URL);

        // METODO DE BUSCA DE PAGINACAO 
        $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $pesqInternado = filter_input(INPUT_GET, 'pesqInternado', FILTER_SANITIZE_SPECIAL_CHARS) ?: "s";
        $limite = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
        $pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS);
        $senha_int = filter_input(INPUT_GET, 'senha_int', FILTER_SANITIZE_SPECIAL_CHARS);
        $data_intern_int = filter_input(INPUT_GET, 'data_intern_int');
        $data_intern_int_max = filter_input(INPUT_GET, 'data_intern_int_max');
        if (empty($data_intern_int_max)) {
            $data_intern_int_max = date('Y-m-d'); // Formato de data compatível com SQL
        }
        $ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;

        $condicoes = [
            strlen($pesquisa_nome) ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"' : null,
            strlen($pesquisa_pac) ? 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"' : null,
            strlen($pesqInternado) ? 'internado_int = "' . $pesqInternado . '"' : NULL,
            strlen($data_intern_int) ? 'data_intern_int BETWEEN "' . $data_intern_int . '" AND "' . $data_intern_int_max . '"' : NULL,
            // strlen($senha_int) ? 'senha_int = "' . $senha_int . '"' : NULL,
            strlen($senha_int) ? 'senha_int LIKE "%' . $senha_int . '%"' : null,
            strlen($auditor) ? 'hos.fk_usuario_hosp = "' . $auditor . '"' : NULL,
        ];

        $condicoes = array_filter($condicoes);

        // REMOVE POSICOES VAZIAS DO FILTRO
        $where = implode(' AND ', $condicoes);
        // QUANTIDADE Internacao
        $qtdIntItens1 = $QtdTotalInt->selectAllInternacaoList($where, $order, $obLimite);

        $qtdIntItens = count($qtdIntItens1);
        $totalcasos = ceil($qtdIntItens / $limite);

        // PAGINACAO
        $order = $ordenar;

        $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);

        $obLimite = $obPagination->getLimit();

        // PREENCHIMENTO DO FORMULARIO COM QUERY
        $query = $internacao->selectAllInternacaoList($where, $order, $obLimite);

        $contarVis = 0;
        $condicoesPreditivo = [
            strlen($pesquisa_nome) ? 'ac.fk_patologia_int LIKE "%' . $pesquisa_nome . '%"' : NULL,
            strlen($pesquisa_pac) ? 'nome_pac LIKE "%' . $pesquisa_pac . '%"' : NULL,

        ];

        // REMOVE POSICOES VAZIAS DO FILTRO
        $condicoesPreditivo = array_filter($condicoesPreditivo);
        // REMOVE POSICOES VAZIAS DO FILTRO
        $wherePreditivo = implode(' AND ', $condicoesPreditivo);

        $preditivoPatologia = $internacao->PreditivoIntPatologAntec($wherePreditivo);

        $verificarVisitas = $visitaDao->selectUltimaVisitaComInternacao($where);
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
        <div style="margin-top:3px;" id='container'>
            <div id="table-content">
                <table class="table table-sm table-striped table-hover table-condensed">
                    <thead>
                        <tr>
                            <th scope="col" style="min-width: 50px;">Id-Int</th>
                            <th scope="col" style="min-width: 150px;">Hospital</th>
                            <th scope="col" style="min-width: 150px;">Paciente</th>
                            <th scope="col" style="min-width: 100px;">Data Int</th>
                            <th scope="col" style="min-width: 80px;">Senha</th>
                            <th scope="col" style="min-width: 80px;">Dias Int</th>
                            <th scope="col" style="min-width: 80px;">Últ Visita</th>
                            <th scope="col" style="min-width: 80px;">Visita Med</th>
                            <th scope="col" style="min-width: 80px;">Visita Enf</th>
                            <th scope="col" style="min-width: 80px;">Nº Visita</th>
                            <th scope="col" style="min-width: 80px;">Gestão</th>
                            <th scope="col" style="min-width: 80px;">UTI</th>
                            <th scope="col" style="width:5%; white-space: nowrap;">
                                Preditivo
                                <i data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Este campo mostra o valor preditivo da internação, baseado na patologia, antecedentes e faixa etária de internações do banco de dados, gerando uma média de internação de paciente com mesmas características."
                                    class="bi bi-eye"
                                    style="font-size: 1.2em; margin-left: 5px; vertical-align: middle;"></i>
                            </th>

                            <th scope="col" style="min-width: 80px;">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php

                        foreach ($query as $intern):
                            // extract($query);
                            $visitas = $visitaDao->joinVisitaInternacao($intern["id_internacao"]);
                            $hoje = date('Y-m-d');
                            $atual = new DateTime($hoje);

                            $datainternacao = date("Y-m-d", strtotime($intern['data_intern_int']));
                            $dataIntern = new DateTime($datainternacao);

                            $dataVisita = date("Y-m-d", strtotime($intern['data_visita_vis']));

                            $dataVis = new DateTime($dataVisita);

                            $dataVisitaInt = date("Y-m-d", strtotime($intern['data_visita_int']));

                            $dataVisInternacao = new DateTime($dataVisitaInt);

                            $diasIntern = $dataIntern->diff($atual); //calcular dias de internacao

                            $countVisitas = count($visitas);

                        ?>
                        <tr style="font-size:13px">
                            <td scope="row" class="col-id">
                                <?= $intern["id_internacao"] ?>
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
                            <td scope="row" style="font-weight:bolder;">
                                <?= $intern["senha_int"] ?>
                            </td>
                            <td scope="row">
                                <?= $diasIntern->days ?>
                            </td>
                            <td scope="row">
                                <?php
                                    usort($visitas, function ($a, $b) {
                                        return strtotime($a['data_visita_vis']) - strtotime($b['data_visita_vis']);
                                    });


                                    if ($visitas) {
                                        print(date('d/m/Y', strtotime(end($visitas)['data_visita_vis'])));
                                    }
                                    ?>
                            </td>
                            <td scope="row">

                                <?php
                                    $id_internacao4 = $intern['id_internacao'];
                                    $cargoVis = 'Med_auditor';
                                    $condicoesVis = [
                                        strlen($id_internacao4) ? 'vi.fk_internacao_vis LIKE "%' . $id_internacao4 . '%"' : null,
                                        strlen($cargoVis) ? 'vi.visita_auditor_prof_med LIKE "%' . $cargoVis . '%"' : null,
                                    ];
                                    $condicoesVis = array_filter($condicoesVis);
                                    // REMOVE POSICOES VAZIAS DO FILTRO
                                    $whereVis = implode(' AND ', $condicoesVis);
                                    // print_r($whereVis);
                                    $visitasVis = $visitaDao->selectUltimaVisitaComInternacao($whereVis);

                                    if (isset($visitasVis[0]['dias_desde_ultima_visita'])) {
                                        $dias = $visitasVis[0]['dias_desde_ultima_visita'];

                                        if ($dias !== null) {
                                            // Define a cor e o ícone com base na quantidade de dias
                                            if ($dias <= 7) {
                                                $cor = 'green';
                                                $icone = '<i class="fas fa-check-circle" style="color: green; margin-right: 5px;"></i>';
                                            } elseif ($dias > 7 && $dias <= 10) {
                                                $cor = 'orange';
                                                $icone = '<i class="fas fa-exclamation-circle" style="color: orange; margin-right: 5px;"></i>';
                                            } else {
                                                $cor = 'red';
                                                $icone = '<i class="fas fa-times-circle" style="color: red; margin-right: 5px;"></i>';
                                            }

                                            // Exibe o valor com a cor e o ícone correspondente
                                            echo "$icone<span style='color: $cor; font-weight: bold;'>$dias dias</span>";
                                        } else {
                                            // Exibe "--" com um ícone cinza caso o valor não esteja disponível
                                            // $icone = '<i class="fas fa-minus-circle" style="color: gray; margin-right: 5px;"></i>';
                                            echo "<span>--</span>";
                                        }
                                    } else {
                                        // Exibe "--" com um ícone cinza caso o valor não esteja disponível
                                        // $icone = '<i class="fas fa-minus-circle" style="color: gray; margin-right: 5px;"></i>';
                                        echo "<span style='color: gray;'>--</span>";
                                    }

                                    ?>
                                </p>
                            </td>
                            <!-- visita enfermagem -->
                            <td scope="row">

                                <?php
                                    $id_internacao4Enf = $intern['id_internacao'];
                                    $cargoVisEnf = "Enf_Auditor";
                                    $condicoesVisEnf = [
                                        strlen($id_internacao4) ? 'vi.fk_internacao_vis LIKE "%' . $id_internacao4Enf . '%"' : null,
                                        strlen($cargoVisEnf) ? 'vi.visita_auditor_prof_enf LIKE "%' . $cargoVisEnf . '%"' : null,
                                    ];
                                    $condicoesVisEnf = array_filter($condicoesVisEnf);
                                    // REMOVE POSICOES VAZIAS DO FILTRO
                                    $whereVisEnf = implode(' AND ', $condicoesVisEnf);

                                    $visitasVisEnf = $visitaDao->selectUltimaVisitaComInternacao($whereVisEnf);

                                    if (isset($visitasVisEnf[0]['dias_desde_ultima_visita'])) {
                                        $diasEnf = $visitasVisEnf[0]['dias_desde_ultima_visita'];

                                        if ($diasEnf !== null) {
                                            // Define a cor e o ícone com base na quantidade de dias
                                            if ($diasEnf <= 7) {
                                                $cor = 'green';
                                                $icone = '<i class="fas fa-check-circle" style="color: green; margin-right: 5px;"></i>';
                                            } elseif ($diasEnf > 7 && $diasEnf <= 10) {
                                                $cor = 'orange';
                                                $icone = '<i class="fas fa-exclamation-circle" style="color: orange; margin-right: 5px;"></i>';
                                            } else {
                                                $cor = 'red';
                                                $icone = '<i class="fas fa-times-circle" style="color: red; margin-right: 5px;"></i>';
                                            }

                                            // Exibe o valor com a cor e o ícone correspondente
                                            echo "$icone<span style='color: $cor; font-weight: bold;'>$diasEnf dias</span>";
                                        } else {
                                            // Exibe "--" com um ícone cinza caso o valor não esteja disponível
                                            // $icone = '<i class="fas fa-minus-circle" style="color: gray; margin-right: 5px;"></i>';
                                            echo "<span>--</span>";
                                        }
                                    } else {
                                        // Exibe "--" com um ícone cinza caso o valor não esteja disponível
                                        // $icone = '<i class="fas fa-minus-circle" style="color: gray; margin-right: 5px;"></i>';
                                        echo "<span style='color: gray;'>--</span>";
                                    }

                                    ?>
                                </p>
                            </td>
                            <td scope="row">
                                <?php
                                    print_r($countVisitas);

                                    ?>
                            </td>
                            <td scope="row">
                                <?php

                                    $id_internacao3 = $intern['id_internacao'];

                                    $condicoesGes = [
                                        strlen($id_internacao3) ? 'ge.fk_internacao_ges LIKE "%' . $id_internacao3 . '%"' : null,
                                    ];
                                    $condicoesGes = array_filter($condicoesGes);
                                    // REMOVE POSICOES VAZIAS DO FILTRO
                                    $whereGes = implode(' AND ', $condicoesGes);
                                    $gestaos = $gestaoDao->selectAllGestaoLis($whereGes);
                                    if ($gestaos) {
                                    ?>
                                <a href=""><i style="color:green; font-size:1.8em" class="bi bi-card-checklist fw-bold">
                                    </i></a>

                                <?php
                                    } else
                                        print_r("--");
                                    ?>
                            </td>
                            <td scope="row">
                                <?php
                                    if ($intern['internado_uti'] == 's') {
                                    ?><a href=""><i class="bi bi-clipboard-heart"
                                        style="color: blue; font-size: 1.8em; margin-right: 8px;"></i>
                                </a>
                                <?php
                                    } else
                                        print_r("--");
                                    ?>
                            </td>
                            <td scope="row">
                                <?php
                                    $condicoesPreditivo = [
                                        strlen($intern["fk_patologia_int"]) ? 'ac.fk_patologia_int = ' . $intern["fk_patologia_int"] : null,
                                        strlen($intern["intern_antec_ant_int"]) ? 'an.intern_antec_ant_int = ' . $intern["intern_antec_ant_int"] : null,
                                    ];

                                    // Remove condições vazias
                                    $condicoesPreditivo = array_filter($condicoesPreditivo);

                                    // Concatena as condições com 'AND'
                                    $wherePreditivo = implode(' AND ', $condicoesPreditivo);

                                    // Passa para a função
                                    $preditivoPatologia = $internacao->PreditivoIntPatologAntec($wherePreditivo);

                                    // Transformar o valor do índice [4] em um inteiro
                                    $valorInteiro = intval($preditivoPatologia[4]);

                                    // Imprime o valor convertido
                                    echo "<span style='font-size:1.2em; color:blue; font-weight:500;'>$valorInteiro</span>";
                                    ?>
                            </td>

                            <td class="action">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" id="navbarScrollingDropdown"
                                        role="button" data-bs-toggle="dropdown" style="color:#5e2363"
                                        aria-expanded="false">
                                        <i class="bi bi-stack"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                        <?php if ($pesqInternado == "s" and $intern['censo_int'] <> "s") { ?>
                                        <li><button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>show_internacao.php?id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: 1rem;"><i class="fas fa-eye"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                Ver</button>
                                        </li>

                                        <?php }; ?>

                                        <?php if ($pesqInternado == "s" and $intern['censo_int'] == "s" and $intern['primeira_vis_int'] == 'n') { ?>
                                        <li><button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>edit_internacao.php?id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: .9rem;">
                                                <i class="bi bi-pencil-square"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                Rel. Inicial
                                            </button></li>

                                        <?php }; ?>
                                        <li>
                                            <button type="button" class="btn btn-default"
                                                style="font-size: .9rem; display: inline-flex; align-items: center;"
                                                onclick="window.location.href='<?= $BASE_URL ?>cad_visita.php?id_internacao=<?= $intern['id_internacao'] ?>'">
                                                <i class="bi bi-file-text"
                                                    style="font-size: 1rem; margin-right:5px; color: rgb(27,156,55);"></i>
                                                Visita
                                            </button>
                                        </li>


                                        <?php if ($pesqInternado == "s") { ?>

                                        <li> <button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>edit_alta.php?type=alta&id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: .9rem;"><i class="bi bi-door-open"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                Alta
                                            </button>
                                        </li>
                                        <?php }; ?>
                                        <li><button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>edit_internacao_EA.php?id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: .9rem;">
                                                <i class="bi bi-pencil-square"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                Ev Adverso
                                            </button></li>
                                        <li><button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>edit_internacao_TUSS.php?id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: .9rem;">
                                                <i class="bi bi-pencil-square"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                TUSS
                                            </button></li>
                                        <li>
                                        <li>
                                            <button type="button" class="btn btn-default"
                                                style="font-size: .9rem; display: inline-flex; align-items: center;"
                                                onclick="window.location.href='<?= $BASE_URL ?>edit_internacao.php?id_internacao=<?= $intern['id_internacao'] ?>'">
                                                <i class="bi bi-pencil-square"
                                                    style="font-size: 1rem; margin-right: 5px; color: rgb(27,156,55);"></i>
                                                Editar
                                            </button>
                                        </li>

                                        <li>
                                            <button class="btn btn-default"
                                                onclick="callProcessPdf(<?= $intern['id_internacao'] ?>)"
                                                style="font-size: .9rem;">
                                                <i class="bi-file-earmark-pdf"
                                                    style="font-size: 1rem; margin-right:5px; color: rgb(27,156,55);"></i>
                                                PDF
                                            </button>
                                        </li>



                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <!-- <tr class="spacer"></tr> -->
                        <?php endforeach; ?>
                        <?php if ($qtdIntItens == 0): ?>
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

                <div style="display: flex;margin-top:20px;">


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
                                    onclick="loadContent('list_internacao.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&senha_int=<?php print $senha_int ?>&data_intern_int=<?php print $data_intern_int ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>')">
                                    <i class="fa-solid fa-angles-left"></i></a>
                            </li>
                            <?php endif; ?>
                            <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="#"
                                    onclick="loadContent('list_internacao.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&senha_int=<?php print $senha_int ?>&data_intern_int=<?php print $data_intern_int ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual - 1 ?>&bl=<?php print $blocoAtual - 5 ?>')">
                                    <i class="fa-solid fa-angle-left"></i> </a>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++): ?>
                            <li class="page-item <?php print ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">

                                <a class="page-link" href="#"
                                    onclick="loadContent('list_internacao.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&senha_int=<?php print $senha_int ?>&data_intern_int=<?php print $data_intern_int ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $i ?>&bl=<?php print $blocoAtual ?>')">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($current_block < $last_block): ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_internacao.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&senha_int=<?php print $senha_int ?>&data_intern_int=<?php print $data_intern_int ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual + 1 ?>&bl=<?php print $blocoAtual + 5 ?>')"><i
                                        class="fa-solid fa-angle-right"></i></a>
                            </li>
                            <?php endif; ?>
                            <?php if ($current_block < $last_block): ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_internacao.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&senha_int=<?php print $senha_int ?>&data_intern_int=<?php print $data_intern_int ?>&pesqInternado=<?php print $pesqInternado ?>&limite_pag=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print count($paginas) ?>&bl=<?php print ($last_block - 1) * 5 ?>')"><i
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
<script type="text/javascript">
function callProcessPdf(id_internacao) {
    // Redireciona a página para o gerador de PDF, passando o ID como parâmetro GET.
    // Isso fará com que o navegador baixe/abra o PDF gerado pelo PHP.
    window.location.href = 'process_pdf_intern.php?id=' + encodeURIComponent(id_internacao);
}
</script>

<script>
// ajax para submit do formulario de pesquisa
$(document).ready(function() {
    $('#select-internacao-form').submit(function(e) {
        e.preventDefault(); // Impede o comportamento padrão de enviar o formulário

        var formData = $(this).serialize(); // Serializa os dados do formulário

        $.ajax({
            url: $(this).attr('action'), // URL do formulário
            type: $(this).attr('method'), // Método do formulário (GET)
            data: formData, // Dados serializados do formulário
            success: function(response) {
                // Atualiza o conteúdo da tabela com a resposta do servidor
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
</script>
<script src="./js/input-estilo.js"></script>

<script src="./js/scriptDataAltaHospitalar.js"></script>

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

<?php
require_once("templates/footer.php");
?>