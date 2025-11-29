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
$data_intern_int      = null;
$order                = null;
$obLimite             = null;
$blocoNovo            = null;
$senha_int            = null;
$where                = null;

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$Internacaos      = $Internacao_geral->findGeral();

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$gestaoDao   = new gestaoDAO($conn, $BASE_URL);

$limite  = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
$ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$patologiaDao   = new patologiaDAO($conn, $BASE_URL);
$visitaDao      = new visitaDAO($conn, $BASE_URL);
$internacao     = new internacaoDAO($conn, $BASE_URL);
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<style>
/* Chips roxos para seleção de campos (modal export) */
/* Pills lilás maiores, com ícones brancos */
.export-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 20px;
    /* mais “gordinho” */
    border-radius: 999px;
    background-color: #5e2363;
    /* roxo cheio */
    color: #ffffff;
    /* texto branco */
    font-size: 0.95rem;
    /* fonte um pouco maior */
    font-weight: 600;
    border: none;
    cursor: pointer;
    margin: 6px 8px 6px 0;
    white-space: nowrap;
}

/* Estado desativado (contorno) */
.export-pill.inactive {
    background-color: #ffffff;
    color: #5e2363;
    border: 1px solid #5e2363;
}

/* Ícones sempre brancos nas pills ativas */
.export-pill i {
    color: #ffffff;
    /* ícones brancos */
    font-size: 1rem;
    /* maior que antes */
}

/* Ícones roxos quando a pill está desativada */
.export-pill.inactive i {
    color: #5e2363;
}


.export-pill-toolbar {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 8px;
    margin-bottom: 4px;
}

.export-pill-toolbar button {
    font-size: 0.8rem;
    padding: 2px 10px;
    border-radius: 999px;
}

.modal-backdrop {
    display: none;
}

.modal {
    background: rgba(0, 0, 0, 0.5);
}

.modal-header.modal-header-blue {
    color: white;
    background: #35bae1;
}

/* Lista de ações da internação com alinhamento à esquerda */
.action .dropdown-menu {
    padding: 8px 0;
    min-width: 190px;
}

.action .dropdown-menu li {
    margin: 0;
}

.action .dropdown-menu .btn-default {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    border: none;
    background: transparent;
    font-size: 0.95rem;
    color: #3a3a3a;
    justify-content: flex-start;
    text-align: left;
    padding: 6px 16px;
}

.action .dropdown-menu .btn-default:hover {
    background-color: #f4f4f4;
}

.action .dropdown-menu .btn-default i {
    margin: 0;
    min-width: 18px;
    font-weight: 700;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SHIM do selectpicker: impede o erro mesmo se alguém chamar .selectpicker() -->
<script>
if (typeof jQuery !== 'undefined') {
    (function($) {
        if (!$.fn.selectpicker) {
            $.fn.selectpicker = function() {
                // não faz nada, só evita erro
                return this;
            };
        }
    })(jQuery);
}
</script>

<!-- <script src="js/ajaxNav.js"></script> -->

<!-- FORMULARIO DE PESQUISAS -->
<div class="container-fluid" id='main-container'>

    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 10px;">
        <h4 class="page-title" style="color: #3A3A3A;">Listagem - Internação</h4>

        <?php
        // valores default para montagem de URL / filtros
        $busca               = $busca               ?? '';
        $busca_user          = $busca_user          ?? '';
        $ordenar             = $ordenar             ?? 1;
        $limite              = $limite              ?? 10;
        $senha_int           = $senha_int           ?? '';
        $data_intern_int     = $data_intern_int     ?? '';
        $data_intern_int_max = $data_intern_int_max ?? '';
        ?>

        <div class="d-flex">
            <!-- Botão de Exportar para Excel (abre modal) -->
            <a href="#" id="btn-exportar-excel" class="btn btn-success" style="border-radius:10px; margin-right: 10px;">
                Exportar para Excel
            </a>

            <!-- Botão de Nova Internação -->
            <a class="btn btn-success" href="<?= $BASE_URL ?>internacoes/nova"
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
                <?php
                $pesquisa_nome       = filter_input(INPUT_GET, 'pesquisa_nome');
                $pesqInternado       = filter_input(INPUT_GET, 'pesqInternado') ? filter_input(INPUT_GET, 'pesqInternado') : 's';
                $limite              = filter_input(INPUT_GET, 'limite_pag');
                $pesquisa_pac        = filter_input(INPUT_GET, 'pesquisa_pac');
                $ordenar             = filter_input(INPUT_GET, 'ordenar');
                $data_intern_int     = filter_input(INPUT_GET, 'data_intern_int') ?: null;
                $data_intern_int_max = filter_input(INPUT_GET, 'data_intern_int_max') ?: null;
                $senha_int           = filter_input(INPUT_GET, 'senha_int') ?: null;
                ?>
                <div class="form-group row">
                    <div class="form-group col-sm-2" style="padding:2px;padding-left:16px !important;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="pesquisa_nome" placeholder="Selecione o Hospital"
                            value="<?= htmlspecialchars((string)$pesquisa_nome) ?>">
                    </div>

                    <div class="form-group col-sm-2" style="padding:2px;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="pesquisa_pac" placeholder="Selecione o Paciente"
                            value="<?= htmlspecialchars((string)$pesquisa_pac) ?>">
                    </div>

                    <div class="form-group col-sm-1" style="padding:2px;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="senha_int" placeholder="Senha" value="<?= htmlspecialchars((string)$senha_int) ?>">
                    </div>

                    <div class="col-sm-1" style="padding:2px !important">
                        <select class="form-control mb-3 form-control-sm" style="color:#878787;margin-top:7px;"
                            id="limite" name="limite_pag">
                            <option value="">Reg por pag</option>
                            <option value="5" <?= $limite == '5'  ? 'selected' : null ?>>Reg por pág = 5</option>
                            <option value="10" <?= $limite == '10' ? 'selected' : null ?>>Reg por pág = 10</option>
                            <option value="20" <?= $limite == '20' ? 'selected' : null ?>>Reg por pág = 20</option>
                            <option value="50" <?= $limite == '50' ? 'selected' : null ?>>Reg por pág = 50</option>
                        </select>
                    </div>

                    <div class="form-group col-sm-1" style="padding:2px;">
                        <select class="form-control form-control-sm" style="color:#878787;margin-top:7px;" id="ordenar"
                            name="ordenar">
                            <option value="">Classificar</option>
                            <option value="nome_pac" <?= $ordenar == 'nome_pac'       ? 'selected' : null ?>>Paciente
                            </option>
                            <option value="nome_hosp" <?= $ordenar == 'nome_hosp'      ? 'selected' : null ?>>Hospital
                            </option>
                            <option value="id_internacao" <?= $ordenar == 'id_internacao'  ? 'selected' : null ?>>
                                Internação</option>
                            <option value="data_intern_int" <?= $ordenar == 'data_intern_int' ? 'selected' : null ?>>
                                Data
                                Internação</option>
                        </select>
                    </div>

                    <div class="form-group col-sm-1" style="padding:2px;">
                        <input class="form-control form-control-sm" type="date" style="color:#878787;margin-top:7px;"
                            name="data_intern_int" placeholder="Data Internação Min"
                            value="<?= htmlspecialchars((string)$data_intern_int) ?>">
                    </div>

                    <div class="form-group col-sm-1" style="padding:2px;">
                        <input class="form-control form-control-sm" type="date" style="color:#878787;margin-top:7px;"
                            name="data_intern_int_max" placeholder="Data Internação Max"
                            value="<?= htmlspecialchars((string)$data_intern_int_max) ?>">
                    </div>

                    <div class="form-group col-sm-1" style="padding:2px;">
                        <button type="submit" class="btn btn-primary"
                            style="background-color:#5e2363;width:42px;height:32px;border-color:#5e2363;margin-top:7px;">
                            <span class="material-icons" style="margin-left:-3px;margin-top:-2px;">
                                search
                            </span>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="pesqInternado" value="<?= htmlspecialchars((string)$pesqInternado) ?>">
            </form>
        </div>

        <?php
        // validacao de lista de hospital por usuario (o nivel sera o filtro)
        if ($_SESSION['nivel'] == 3) {
            $auditor = ($_SESSION['id_usuario']);
        } else {
            $auditor = null;
        }

        $QtdTotalInt = new internacaoDAO($conn, $BASE_URL);

        // METODO DE BUSCA DE PAGINACAO 
        $pesquisa_nome       = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $pesqInternado       = filter_input(INPUT_GET, 'pesqInternado', FILTER_SANITIZE_SPECIAL_CHARS) ?: "s";
        $limite              = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
        $pesquisa_pac        = filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS);
        $senha_int           = filter_input(INPUT_GET, 'senha_int', FILTER_SANITIZE_SPECIAL_CHARS);
        $data_intern_int     = filter_input(INPUT_GET, 'data_intern_int');
        $data_intern_int_max = filter_input(INPUT_GET, 'data_intern_int_max');

        if (empty($data_intern_int_max)) {
            $data_intern_int_max = date('Y-m-d');
        }

        $ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;

        $condicoes = [
            strlen($pesquisa_nome)       ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"'                  : null,
            strlen($pesquisa_pac)        ? 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"'                    : null,
            strlen($pesqInternado)       ? 'internado_int = "' . $pesqInternado . '"'                      : null,
            strlen($data_intern_int)     ? 'data_intern_int BETWEEN "' . $data_intern_int . '" AND "' . $data_intern_int_max . '"' : null,
            strlen($senha_int)           ? 'senha_int LIKE "%' . $senha_int . '%"'                         : null,
            strlen($auditor)             ? 'hos.fk_usuario_hosp = "' . $auditor . '"'                      : null,
        ];

        $condicoes = array_filter($condicoes);
        $where     = implode(' AND ', $condicoes);

        $qtdIntItens1 = $QtdTotalInt->selectAllInternacaoList($where, $order, $obLimite);
        $qtdIntItens  = count($qtdIntItens1);
        $totalcasos   = ceil($qtdIntItens / $limite);

        $order        = $ordenar;
        $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);
        $obLimite     = $obPagination->getLimit();

$query = $internacao->selectAllInternacaoList($where, $order, $obLimite);

        $verificarVisitas = $visitaDao->selectUltimaVisitaComInternacao($where);

        if ($qtdIntItens > $limite) {
            $paginacao   = '';
            $paginas     = $obPagination->getPages();
            $pagina      = 1;
            $total_pages = count($paginas);

            function paginasAtuais($var)
            {
                $blocoAtual = isset($_GET['bl']) ? $_GET['bl'] : 0;
                return $var['bloco'] == (($blocoAtual) / 5) + 1;
            }
            $block_pages         = array_filter($paginas, "paginasAtuais");
            $first_page_in_block = reset($block_pages)["pg"];
            $last_page_in_block  = end($block_pages)["pg"];
            $first_block         = reset($paginas)["bloco"];
            $last_block          = end($paginas)["bloco"];
            $current_block       = reset($block_pages)["bloco"];
        }

        $paginationBaseParams = [
            'pesquisa_nome'       => $pesquisa_nome,
            'pesquisa_pac'        => $pesquisa_pac,
            'senha_int'           => $senha_int,
            'data_intern_int'     => $data_intern_int,
            'data_intern_int_max' => $data_intern_int_max,
            'pesqInternado'       => $pesqInternado,
            'limite_pag'          => $limite,
            'ordenar'             => $ordenar,
        ];

        if (!function_exists('buildInternacaoPaginationUrl')) {
            function buildInternacaoPaginationUrl(array $baseParams, array $override = []): string
            {
                $params = array_merge($baseParams, $override);
                $params = array_filter($params, function ($value) {
                    return $value !== null && $value !== '';
                });

                $query = http_build_query($params);
                global $BASE_URL;
                $baseUrl = rtrim($BASE_URL, '/') . '/internacoes/lista';

                return $query ? $baseUrl . '?' . $query : $baseUrl;
            }
        }
        ?>

        <!-- TABELA DE REGISTROS -->
        <div style="margin-top:3px;" id="container">
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
                            <th scope="col" style="min-width: 80px;">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($query as $intern):
                            $visitas = $visitaDao->joinVisitaInternacao($intern["id_internacao"]);

                            $hoje  = date('Y-m-d');
                            $atual = new DateTime($hoje);

                            $datainternacao = date("Y-m-d", strtotime($intern['data_intern_int']));
                            $dataIntern     = new DateTime($datainternacao);

                            $diasIntern     = $dataIntern->diff($atual);
                            $countVisitas   = count($visitas);
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
                                        echo date('d/m/Y', strtotime(end($visitas)['data_visita_vis']));
                                    }
                                    ?>
                            </td>

                            <!-- Visita Médica -->
                            <td scope="row">
                                <?php
                                    $id_internacao4 = $intern['id_internacao'];
                                    $cargoVis       = 'Med_auditor';

                                    $condicoesVis = [
                                        strlen($id_internacao4) ? 'vi.fk_internacao_vis LIKE "%' . $id_internacao4 . '%"' : null,
                                        strlen($cargoVis)       ? 'vi.visita_auditor_prof_med LIKE "%' . $cargoVis . '%"' : null,
                                    ];
                                    $condicoesVis = array_filter($condicoesVis);
                                    $whereVis     = implode(' AND ', $condicoesVis);
                                    $visitasVis   = $visitaDao->selectUltimaVisitaComInternacao($whereVis);

                                    if (isset($visitasVis[0]['dias_desde_ultima_visita'])) {
                                        $dias = $visitasVis[0]['dias_desde_ultima_visita'];

                                        if ($dias !== null) {
                                            if ($dias <= 7) {
                                                $cor   = 'green';
                                                $icone = '<i class="fas fa-check-circle" style="color: green; margin-right: 5px;"></i>';
                                            } elseif ($dias > 7 && $dias <= 10) {
                                                $cor   = 'orange';
                                                $icone = '<i class="fas fa-exclamation-circle" style="color: orange; margin-right: 5px;"></i>';
                                            } else {
                                                $cor   = 'red';
                                                $icone = '<i class="fas fa-times-circle" style="color: red; margin-right: 5px;"></i>';
                                            }
                                            echo "$icone<span style='color: $cor; font-weight: bold;'>{$dias} dias</span>";
                                        } else {
                                            echo "<span>--</span>";
                                        }
                                    } else {
                                        echo "<span style='color: gray;'>--</span>";
                                    }
                                    ?>
                            </td>

                            <!-- Visita Enfermagem -->
                            <td scope="row">
                                <?php
                                    $id_internacao4Enf = $intern['id_internacao'];
                                    $cargoVisEnf       = "Enf_Auditor";

                                    $condicoesVisEnf = [
                                        strlen($id_internacao4Enf) ? 'vi.fk_internacao_vis LIKE "%' . $id_internacao4Enf . '%"' : null,
                                        strlen($cargoVisEnf)       ? 'vi.visita_auditor_prof_enf LIKE "%' . $cargoVisEnf . '%"' : null,
                                    ];
                                    $condicoesVisEnf = array_filter($condicoesVisEnf);
                                    $whereVisEnf     = implode(' AND ', $condicoesVisEnf);

                                    $visitasVisEnf = $visitaDao->selectUltimaVisitaComInternacao($whereVisEnf);

                                    if (isset($visitasVisEnf[0]['dias_desde_ultima_visita'])) {
                                        $diasEnf = $visitasVisEnf[0]['dias_desde_ultima_visita'];

                                        if ($diasEnf !== null) {
                                            if ($diasEnf <= 7) {
                                                $cor   = 'green';
                                                $icone = '<i class="fas fa-check-circle" style="color: green; margin-right: 5px;"></i>';
                                            } elseif ($diasEnf > 7 && $diasEnf <= 10) {
                                                $cor   = 'orange';
                                                $icone = '<i class="fas fa-exclamation-circle" style="color: orange; margin-right: 5px;"></i>';
                                            } else {
                                                $cor   = 'red';
                                                $icone = '<i class="fas fa-times-circle" style="color: red; margin-right: 5px;"></i>';
                                            }
                                            echo "$icone<span style='color: $cor; font-weight: bold;'>{$diasEnf} dias</span>";
                                        } else {
                                            echo "<span>--</span>";
                                        }
                                    } else {
                                        echo "<span style='color: gray;'>--</span>";
                                    }
                                    ?>
                            </td>

                            <td scope="row">
                                <?= $countVisitas ?>
                            </td>

                            <td scope="row">
                                <?php
                                    $id_internacao3 = $intern['id_internacao'];

                                    $condicoesGes = [
                                        strlen($id_internacao3) ? 'ge.fk_internacao_ges LIKE "%' . $id_internacao3 . '%"' : null,
                                    ];
                                    $condicoesGes = array_filter($condicoesGes);
                                    $whereGes     = implode(' AND ', $condicoesGes);
                                    $gestaos      = $gestaoDao->selectAllGestaoLis($whereGes);

                                    if ($gestaos) {
                                        echo '<a href=""><i style="color:green; font-size:1.8em" class="bi bi-card-checklist fw-bold"></i></a>';
                                    } else {
                                        echo "--";
                                    }
                                    ?>
                            </td>

                            <td scope="row">
                                <?php
                                    if ($intern['internado_uti'] == 's') {
                                        echo '<a href=""><i class="bi bi-clipboard-heart" style="color: blue; font-size: 1.8em; margin-right: 8px;"></i></a>';
                                    } else {
                                        echo "--";
                                    }
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
                                        <li>
                                            <button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>show_internacao.php?id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: 1rem;">
                                                <i class="fas fa-eye"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                Visualização
                                            </button>
                                        </li>
                                        <?php } ?>

                                        <?php if ($pesqInternado == "s" and $intern['censo_int'] == "s" and $intern['primeira_vis_int'] == 'n') { ?>
                                        <li>
                                            <button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>edit_internacao.php?id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: .9rem;">
                                                <i class="bi bi-pencil-square"
                                                    style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                                Rel. Inicial
                                            </button>
                                        </li>
                                        <?php } ?>

                                        <li>
                                            <button type="button" class="btn btn-default"
                                                style="font-size: .9rem;"
                                                onclick="window.location.href='<?= $BASE_URL ?>cad_visita.php?id_internacao=<?= $intern['id_internacao'] ?>'">
                                                <i class="bi bi-file-text"
                                                    style="font-size: 1rem; margin-right:5px; color: rgba(128, 27, 156, 1);"></i>
                                                Visita
                                            </button>
                                        </li>

                                        <?php if ($pesqInternado == "s") { ?>
                                        <li>
                                            <button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>edit_alta.php?type=alta&id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: .9rem;">
                                                <i class="bi bi-door-open"
                                                    style="font-size: 1rem;margin-right:5px; color: rgba(27, 64, 156, 1);"></i>
                                                Alta
                                            </button>
                                        </li>
                                        <?php } ?>

                                        <li>
                                            <!-- <button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>edit_internacao_EA.php?id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: .9rem;">
                                                <i class="bi bi-pencil-square"
                                                    style="font-size: 1rem;margin-right:5px; color: rgba(27, 27, 156, 1);"></i>
                                                Ev Adverso
                                            </button> -->
                                        </li>

                                        <li>
                                            <!-- <button class="btn btn-default"
                                                onclick="edit('<?= $BASE_URL ?>edit_internacao_TUSS.php?id_internacao=<?= $intern['id_internacao'] ?>')"
                                                style="font-size: .9rem;">
                                                <i class="bi bi-pencil-square"
                                                    style="font-size: 1rem;margin-right:5px; color: rgba(156, 27, 85, 1);"></i>
                                                TUSS
                                            </button> -->
                                        </li>

                                        <li>
                                            <button type="button" class="btn btn-default"
                                                style="font-size: .9rem;"
                                                onclick="window.location.href='<?= $BASE_URL ?>edit_internacao.php?id_internacao=<?= $intern['id_internacao'] ?>'">
                                                <i class="bi bi-pencil-square"
                                                    style="font-size: 1rem; margin-right: 5px; color: rgba(113, 27, 156, 1);"></i>
                                                Editar
                                            </button>
                                        </li>

                                        <li>
                                            <button class="btn btn-default"
                                                onclick="callProcessPdf(<?= $intern['id_internacao'] ?>)"
                                                style="font-size: .9rem;">
                                                <i class="bi-file-earmark-pdf"
                                                    style="font-size: 1rem; margin-right:5px; color: #ff7043;"></i>
                                                PDF - Internação
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if ($qtdIntItens == 0): ?>
                        <tr>
                            <td colspan="13" scope="row" class="col-id" style="font-size:15px">
                                Não foram encontrados registros
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div style="text-align:right">
                    <input type="hidden" id="qtd" value="<?= (int)$qtdIntItens ?>">
                </div>

                <div style="display: flex;margin-top:20px;">

                    <!-- Modal para abrir tela de cadastro -->
                    <div class="modal fade" id="myModal">
                        <div class="modal-dialog  modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="modal-header modal-header-blue">
                                    <h4 class="page-title" style="color:white;">Cadastrar Internação</h4>
                                    <p class="page-description" style="color:white; margin-top:5px">
                                        Adicione informações sobre a internação
                                    </p>
                                </div>
                                <div class="modal-body">
                                    <div id="content-php"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PAGINAÇÃO -->
                    <div class="pagination" style="margin: 0 auto;">
                        <?php if ($total_pages ?? 1 > 1): ?>
                        <ul class="pagination">
                            <?php
                                $blocoAtual  = isset($_GET['bl']) ? $_GET['bl'] : 0;
                                $paginaAtual = isset($_GET['pag']) ? $_GET['pag'] : 1;
                                ?>
                            <?php if ($current_block > $first_block): ?>
                            <?php
                                    $firstPageUrl = buildInternacaoPaginationUrl($paginationBaseParams, [
                                        'pag' => 1,
                                        'bl'  => 0
                                    ]);
                                    ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($firstPageUrl) ?>"
                                    onclick="return paginateInternacao('<?= htmlspecialchars($firstPageUrl, ENT_QUOTES) ?>');">
                                    <i class="fa-solid fa-angles-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1): ?>
                            <?php
                                    $prevPage  = max(1, $paginaAtual - 1);
                                    $prevBlock = max(0, $blocoAtual - 5);
                                    $prevUrl   = buildInternacaoPaginationUrl($paginationBaseParams, [
                                        'pag' => $prevPage,
                                        'bl'  => $prevBlock
                                    ]);
                                    ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= htmlspecialchars($prevUrl) ?>"
                                    onclick="return paginateInternacao('<?= htmlspecialchars($prevUrl, ENT_QUOTES) ?>');">
                                    <i class="fa-solid fa-angle-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++): ?>
                            <?php
                                    $pageUrl = buildInternacaoPaginationUrl($paginationBaseParams, [
                                        'pag' => $i,
                                        'bl'  => $blocoAtual
                                    ]);
                                    ?>
                            <li class="page-item <?= ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">
                                <a class="page-link" href="<?= htmlspecialchars($pageUrl) ?>"
                                    onclick="return paginateInternacao('<?= htmlspecialchars($pageUrl, ENT_QUOTES) ?>');">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($current_block < $last_block): ?>
                            <?php
                                    $nextPage  = min($total_pages, $paginaAtual + 1);
                                    $nextBlock = $blocoAtual + 5;
                                    $nextUrl   = buildInternacaoPaginationUrl($paginationBaseParams, [
                                        'pag' => $nextPage,
                                        'bl'  => $nextBlock
                                    ]);
                                    ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($nextUrl) ?>"
                                    onclick="return paginateInternacao('<?= htmlspecialchars($nextUrl, ENT_QUOTES) ?>');">
                                    <i class="fa-solid fa-angle-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php if ($current_block < $last_block): ?>
                            <?php
                                    $lastUrl = buildInternacaoPaginationUrl($paginationBaseParams, [
                                        'pag' => $total_pages,
                                        'bl'  => ($last_block - 1) * 5
                                    ]);
                                    ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($lastUrl) ?>"
                                    onclick="return paginateInternacao('<?= htmlspecialchars($lastUrl, ENT_QUOTES) ?>');">
                                    <i class="fa-solid fa-angles-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </div>

                    <div class="table-counter">
                        <p style="margin-bottom:25px;font-size:1em; font-weight:600;
                                  font-family:var(--bs-font-sans-serif); text-align:right">
                            <?= "Total: " . (int)$qtdIntItens ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Selecionar campos do Excel (Internação) -->
<!-- Modal: Campos a exibir/exportar para o Excel (Internação) -->
<div class="modal fade" id="modalExportInternCampos" tabindex="-1" aria-labelledby="modalExportInternCamposLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">

            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalExportInternCamposLabel">
                    Campos a exibir/exportar para o Excel
                </h5>

                <div class="d-flex align-items-center gap-2 me-3">
                    <!-- Selecionar todos -->
                    <button type="button" class="btn btn-sm rounded-pill" id="btnInternSelectAll"
                        style="background-color:#f5f1ff;border:none;color:#555;">
                        ✓ Selecionar todos
                    </button>
                    <!-- Limpar -->
                    <button type="button" class="btn btn-sm rounded-pill" id="btnInternClear"
                        style="background-color:#f5f1ff;border:none;color:#555;">
                        ✕ Limpar
                    </button>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <form id="formCamposExcelIntern">
                    <!-- Pills – use a mesma classe de pill do modal Alta se já existir -->
                    <div class="d-flex flex-wrap gap-2">
                        <!-- ID Internação -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="id_int" id="campo_id_int"
                            autocomplete="off" checked>
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_id_int">
                            # ID da internação
                        </label>

                        <!-- Hospital -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="hosp" id="campo_hosp"
                            autocomplete="off" checked>
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_hosp">
                            🏥 Hospital
                        </label>

                        <!-- Nome do paciente -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="pac" id="campo_pac"
                            autocomplete="off" checked>
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_pac">
                            👤 Nome do paciente
                        </label>

                        <!-- Data Internação -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="data_intern"
                            id="campo_data_intern" autocomplete="off" checked>
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_data_intern">
                            📅 Data da internação
                        </label>

                        <!-- Hora Internação -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="hora_intern"
                            id="campo_hora_intern" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_hora_intern">
                            ⏰ Hora da internação
                        </label>

                        <!-- UTI -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="uti" id="campo_uti"
                            autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_uti">
                            UTI
                        </label>

                        <!-- Acomodação -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="acomodacao"
                            id="campo_acomodacao" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_acomodacao">
                            Acomodação
                        </label>

                        <!-- Senha -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="senha" id="campo_senha"
                            autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_senha">
                            Senha
                        </label>

                        <!-- Matrícula -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="matricula"
                            id="campo_matricula" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_matricula">
                            Matrícula
                        </label>

                        <!-- Tipo Admissão -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="tipo_adm"
                            id="campo_tipo_adm" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_tipo_adm">
                            Tipo admissão
                        </label>

                        <!-- Modo Internação -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="modo" id="campo_modo"
                            autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_modo">
                            Modo internação
                        </label>

                        <!-- Internado -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="internado"
                            id="campo_internado" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_internado">
                            Internado
                        </label>

                        <!-- Especialidade -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="especialidade"
                            id="campo_especialidade" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_especialidade">
                            Especialidade
                        </label>

                        <!-- Patologia -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="patologia"
                            id="campo_patologia" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_patologia">
                            Patologia
                        </label>

                        <!-- Relatório / Evolução -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="relatorio"
                            id="campo_relatorio" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_relatorio">
                            Relatório / Evolução
                        </label>

                        <!-- Ações -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="acoes" id="campo_acoes"
                            autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_acoes">
                            Ações
                        </label>

                        <!-- Programação -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="programacao"
                            id="campo_programacao" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_programacao">
                            Programação
                        </label>

                        <!-- Médico Titular -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="medico_titular"
                            id="campo_medico_titular" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_medico_titular">
                            Médico titular
                        </label>

                        <!-- Nome do profissional -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="profissional"
                            id="campo_profissional" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_profissional">
                            Nome do profissional
                        </label>

                        <!-- Cargo do profissional -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="profissional_cargo"
                            id="campo_profissional_cargo" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_profissional_cargo">
                            Cargo do profissional
                        </label>

                        <!-- Registro do profissional -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="profissional_registro"
                            id="campo_profissional_registro" autocomplete="off">
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_profissional_registro">
                            Registro profissional
                        </label>

                    </div>

                </form>
            </div>

            <div class="modal-footer border-0 d-flex justify-content-between">
                <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="btn btn-success rounded-pill" id="btnConfirmExportIntern">
                    Exportar XLSX (Excel)
                </button>
            </div>

        </div>
    </div>
</div>



<script type="text/javascript">
function callProcessPdf(id_internacao) {
    window.location.href = 'process_pdf_intern.php?id=' + encodeURIComponent(id_internacao);
}
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<script>
// ajax para submit do formulario de pesquisa + modal de exportação
$(document).ready(function() {

    // ============================
    // 1) SUBMIT AJAX – FILTRO
    // ============================
    $('#select-internacao-form').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action') || 'internacoes/lista',
            type: $(this).attr('method') || 'GET',
            data: formData,
            success: function(response) {
                var tempElement = document.createElement('div');
                tempElement.innerHTML = response;

                var tableContent = tempElement.querySelector('#table-content');
                if (tableContent) {
                    $('#table-content').html(tableContent.innerHTML);
                }
            },
            error: function() {
                $('#responseMessage').html('Ocorreu um erro ao enviar o formulário.');
            }
        });
    });

    // ==========================================
    // 2) ABRIR MODAL DE CAMPOS DO EXCEL
    // ==========================================
    $('#btn-exportar-excel').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        new bootstrap.Modal(document.getElementById('modalExportInternCampos')).show();
    });

    // ==========================================
    // 3) PILLS (chips lilás) <-> checkboxes
    // ==========================================

    // Deixa as pills com visual coerente com o estado dos checkboxes
    function syncPillsFromCheckboxes() {
        $('#formCamposExcelIntern input[name="colsIntern[]"]').each(function() {
            var id = $(this).attr('id'); // ex: campo_id_int
            var $label = $('label[for="' + id + '"]'); // pill correspondente

            if ($(this).is(':checked')) {
                $label.removeClass('inactive');
            } else {
                $label.addClass('inactive');
            }
        });
    }

    // Chamada inicial ao abrir a página
    syncPillsFromCheckboxes();

    // Clique em uma pill -> alterna checkbox correspondente
    $(document).on('click', '.export-pill', function(e) {
        e.preventDefault();

        var $pill = $(this);
        var forId = $pill.attr('for'); // exemplo: "campo_id_int"
        var $cb = $('#' + forId);

        var novoStatus = !$cb.prop('checked');
        $cb.prop('checked', novoStatus);

        if (novoStatus) {
            $pill.removeClass('inactive');
        } else {
            $pill.addClass('inactive');
        }
    });

    // Botão "Selecionar todos"
    $('#btnInternSelectAll').on('click', function(e) {
        e.preventDefault();
        $('#formCamposExcelIntern input[name="colsIntern[]"]').prop('checked', true);
        syncPillsFromCheckboxes();
    });

    // Botão "Limpar"
    $('#btnInternClear').on('click', function(e) {
        e.preventDefault();
        $('#formCamposExcelIntern input[name="colsIntern[]"]').prop('checked', false);
        syncPillsFromCheckboxes();
    });

    // ==========================================
    // 4) CONFIRMAR EXPORTAÇÃO EXCEL
    // ==========================================
    $('#btnConfirmExportIntern').on('click', function(e) {
        e.preventDefault();

        // 1) Campos marcados no modal
        var campos = [];
        $('input[name="colsIntern[]"]:checked').each(function() {
            campos.push($(this).val());
        });

        if (!campos.length) {
            alert('Selecione pelo menos um campo para exportar.');
            return;
        }

        // 2) Filtros da listagem
        var queryParts = [];
        var baseQuery = $('#select-internacao-form').serialize();
        if (baseQuery) {
            queryParts.push(baseQuery);
        }

        // 3) Param "campos" em CSV
        queryParts.push('campos=' + encodeURIComponent(campos.join(',')));

        // 4) Filtro adicional de profissional

        var query = queryParts.join('&');

        // 4) URL final
        var urlExcel = '<?= $BASE_URL ?>exportar_excel_list_intern.php';
        if (query) {
            urlExcel += '?' + query;
        }

        // 5) Fecha modal
        var modalEl = document.getElementById('modalExportInternCampos');
        var modalObj = bootstrap.Modal.getInstance(modalEl);
        if (modalObj) modalObj.hide();

        // 6) Abre Excel
        window.open(urlExcel, '_blank');
    });

});
</script>

<script>
if (typeof window.paginateInternacao !== 'function') {
    window.paginateInternacao = function(url) {
        if (typeof loadContent === 'function') {
            loadContent(url);
            return false;
        }
        window.location.href = url;
        return false;
    };
}
</script>

<script src="./js/input-estilo.js"></script>
<script src="./js/scriptDataAltaHospitalar.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="./js/ajaxNav.js"></script>

<?php
require_once("templates/footer.php");
?>
