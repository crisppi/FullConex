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
include_once("dao/hospitalUserDao.php");

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
$sortField = trim($_GET['sort_field'] ?? '');
$sortDir   = strtolower($_GET['sort_dir'] ?? 'desc');
$sortDir   = $sortDir === 'asc' ? 'asc' : 'desc';
$onlySemSenhaParam = filter_input(INPUT_GET, 'sem_senha', FILTER_SANITIZE_SPECIAL_CHARS);
$onlySemSenha = in_array($onlySemSenhaParam, ['1', 1, 'true', 'on'], true);

$hospital_geral     = new HospitalDAO($conn, $BASE_URL);
$patologiaDao       = new patologiaDAO($conn, $BASE_URL);
$visitaDao          = new visitaDAO($conn, $BASE_URL);
$internacao         = new internacaoDAO($conn, $BASE_URL);
$hospitalUserDao    = new hospitalUserDAO($conn, $BASE_URL);

$hospitalOptions = [];
try {
    $nivelSessao     = (int) ($_SESSION['nivel'] ?? 0);
    $usuarioSessaoId = (int) ($_SESSION['id_usuario'] ?? 0);
    $rawHospitais    = [];

    if ($nivelSessao >= 4) {
        $rawHospitais = $hospital_geral->findGeral();
    } elseif ($hospitalUserDao && $usuarioSessaoId) {
        $rawHospitais = $hospitalUserDao->listarPorUsuario($usuarioSessaoId);
        if (!is_array($rawHospitais) || !count($rawHospitais)) {
            $rawHospitais = $hospital_geral->findGeral();
        }
    } else {
        $rawHospitais = $hospital_geral->findGeral();
    }

    if (is_array($rawHospitais)) {
        foreach ($rawHospitais as $hospitalRow) {
            $nome = trim((string) ($hospitalRow['nome_hosp'] ?? ''));
            if ($nome && !isset($hospitalOptions[$nome])) {
                $hospitalOptions[$nome] = $nome;
            }
        }
        if ($hospitalOptions) {
            ksort($hospitalOptions, SORT_NATURAL | SORT_FLAG_CASE);
        }
    }
} catch (Throwable $th) {
    $hospitalOptions = [];
}
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

.th-sortable {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.th-sortable .sort-icons a {
    text-decoration: none;
    font-size: 0.85rem;
    color: #ffffff;
    margin-left: 2px;
    opacity: 0.7;
}

.th-sortable .sort-icons a.active {
    color: #ffd966;
    opacity: 1;
    font-weight: bold;
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

.filter-intel-wrapper {
    border: 1px solid #ebe2f3;
    border-radius: 14px;
    padding: 12px 16px;
    margin-bottom: 12px;
    background: #fdfbff;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.filter-intel-wrapper h6 {
    font-weight: 800;
    color: #5e2363;
    margin-bottom: 6px;
}

.filter-intel-wrapper small {
    color: #7a6b84;
    display: block;
}

.filter-intel-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}

.filter-intel-grid .smart-search-group {
    flex: 1;
    min-width: 220px;
}

.filter-intel-grid label {
    font-size: .82rem;
    font-weight: 700;
    color: #7a6b84;
}

.filter-intel-grid .input-group {
    display: flex;
    gap: 6px;
}

.filter-intel-grid input[type="text"] {
    flex: 1;
}

.filter-memory-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-memory-actions button {
    border-radius: 999px;
    font-size: .82rem;
    font-weight: 600;
    border: 1px solid #bfa3d1;
    background: #fff;
    color: #5e2363;
    padding: 6px 14px;
    transition: all .15s ease;
}

.filter-memory-actions button:hover {
    background: #5e2363;
    color: #fff;
}

.filter-favorites {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.filter-favorite-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 4px 12px;
    font-size: .78rem;
    font-weight: 600;
    border: 1px solid #ffcad9;
    color: #a03a5e;
    background: #fff5f8;
    cursor: pointer;
}

.filter-favorite-chip .remove {
    font-size: .75rem;
    color: #c24360;
    cursor: pointer;
}

.filter-favorite-chip .remove:hover {
    color: #8a1433;
}

.filter-empty-hint {
    font-size: .78rem;
    color: #a690b3;
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
        <h4 class="page-title" style="color: #3A3A3A;">
            <?= $onlySemSenha ? 'Internações com senha pendente' : 'Listagem - Internação' ?>
        </h4>

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
                $pesquisa_matricula  = filter_input(INPUT_GET, 'pesquisa_matricula');
                $ordenar             = filter_input(INPUT_GET, 'ordenar');
                $data_intern_int     = filter_input(INPUT_GET, 'data_intern_int') ?: null;
                $data_intern_int_max = filter_input(INPUT_GET, 'data_intern_int_max') ?: null;
                $senha_int           = filter_input(INPUT_GET, 'senha_int') ?: null;
                ?>
                <div class="filter-intel-wrapper">
                    <h6>Memória de filtros e busca inteligente</h6>
                    <div class="filter-intel-grid">
                        <div class="smart-search-group">
                            <label for="smartSearchPhrase">Busca em linguagem natural</label>
                            <div class="input-group">
                                <input type="text" id="smartSearchPhrase" class="form-control form-control-sm"
                                    placeholder='Ex.: "contas Einstein outubro 2023" ou "paciente Ana maio"'>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnApplySmartSearch">
                                    Aplicar frase
                                </button>
                            </div>
                            <small>Tente combinar hospital, paciente, mês/ano ou senha em uma frase única.</small>
                        </div>
                        <div class="filter-memory-actions">
                            <button type="button" id="btnApplyLastFilter">Aplicar último filtro</button>
                            <button type="button" id="btnSaveFavFilter">Salvar como favorito</button>
                            <button type="button" id="btnClearFilters">Limpar filtros</button>
                        </div>
                    </div>
                    <div class="filter-favorites" id="filterFavorites"></div>
                    <div class="filter-empty-hint" id="filterFavoritesHint">Nenhum favorito salvo ainda.</div>
                </div>
                <div class="form-group row">
                    <div class="form-group col-sm-2" style="padding:2px;padding-left:16px !important;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="pesquisa_nome" list="internacaoHospitaisList" placeholder="Selecione o Hospital"
                            value="<?= htmlspecialchars((string)$pesquisa_nome) ?>">
                        <datalist id="internacaoHospitaisList">
                            <?php foreach ($hospitalOptions as $nomeHosp): ?>
                                <option value="<?= htmlspecialchars($nomeHosp) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="form-group col-sm-2" style="padding:2px;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="pesquisa_pac" placeholder="Selecione o Paciente"
                            value="<?= htmlspecialchars((string)$pesquisa_pac) ?>">
                    </div>

                    <div class="form-group col-sm-2" style="padding:2px;">
                        <input class="form-control form-control-sm" type="text" style="color:#878787;margin-top:7px;"
                            name="pesquisa_matricula" placeholder="Matrícula"
                            value="<?= htmlspecialchars((string)($pesquisa_matricula ?? '')) ?>">
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
                <input type="hidden" name="sem_senha" value="<?= $onlySemSenha ? '1' : '0' ?>">
                <input type="hidden" name="sort_field" value="<?= htmlspecialchars((string)$sortField) ?>">
                <input type="hidden" name="sort_dir" value="<?= htmlspecialchars((string)$sortDir) ?>">
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
        $pesquisa_matricula  = filter_input(INPUT_GET, 'pesquisa_matricula', FILTER_SANITIZE_SPECIAL_CHARS);
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
            strlen($pesquisa_matricula)  ? 'pa.matricula_pac LIKE "%' . $pesquisa_matricula . '%"'         : null,
            strlen($pesqInternado)       ? 'internado_int = "' . $pesqInternado . '"'                      : null,
            strlen($data_intern_int)     ? 'data_intern_int BETWEEN "' . $data_intern_int . '" AND "' . $data_intern_int_max . '"' : null,
            strlen($senha_int)           ? 'ac.senha_int LIKE "%' . $senha_int . '%"'                         : null,
            strlen($auditor)             ? 'hos.fk_usuario_hosp = "' . $auditor . '"'                      : null,
            $onlySemSenha ? '(ac.senha_int IS NULL OR TRIM(ac.senha_int) = "")' : null,
        ];

        $condicoes = array_filter($condicoes);
        $where     = implode(' AND ', $condicoes);

        $sortableColumns = [
            'id_internacao'   => 'ac.id_internacao',
            'nome_hosp'       => 'ho.nome_hosp',
            'nome_pac'        => 'pa.nome_pac',
            'data_intern_int' => 'ac.data_intern_int'
        ];
        $dropdownOrders = [
            'nome_pac'        => 'pa.nome_pac ASC',
            'nome_hosp'       => 'ho.nome_hosp ASC',
            'id_internacao'   => 'ac.id_internacao DESC',
            'data_intern_int' => 'ac.data_intern_int DESC'
        ];

        if ($sortField && isset($sortableColumns[$sortField])) {
            $order = $sortableColumns[$sortField] . ' ' . strtoupper($sortDir);
        } elseif ($ordenar && isset($dropdownOrders[$ordenar])) {
            $order = $dropdownOrders[$ordenar];
            $sortField = '';
        } else {
            $order = 'ac.id_internacao DESC';
        }

        $qtdIntItens1 = $QtdTotalInt->selectAllInternacaoList($where, $order, $obLimite);
        $qtdIntItens  = count($qtdIntItens1);
        $totalcasos   = ceil($qtdIntItens / $limite);

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
            'pesquisa_matricula'  => $pesquisa_matricula,
            'senha_int'           => $senha_int,
            'data_intern_int'     => $data_intern_int,
            'data_intern_int_max' => $data_intern_int_max,
            'pesqInternado'       => $pesqInternado,
            'limite_pag'          => $limite,
            'ordenar'             => $ordenar,
            'sort_field'          => $sortField,
            'sort_dir'            => $sortDir,
            'sem_senha'           => $onlySemSenha ? '1' : null,
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
                            <?php
                            $sortableHeaders = [
                                'id_internacao'   => ['label' => 'Id-Int',   'style' => 'min-width: 50px;'],
                                'nome_hosp'       => ['label' => 'Hospital', 'style' => 'min-width: 150px;'],
                                'nome_pac'        => ['label' => 'Paciente', 'style' => 'min-width: 150px;'],
                                'data_intern_int' => ['label' => 'Data Int', 'style' => 'min-width: 100px;'],
                            ];
                            foreach ($sortableHeaders as $key => $meta):
                                $ascActive = ($sortField === $key && $sortDir === 'asc');
                                $descActive = ($sortField === $key && $sortDir === 'desc');
                                $ascUrl = buildInternacaoPaginationUrl($paginationBaseParams, ['sort_field' => $key, 'sort_dir' => 'asc', 'pag' => 1]);
                                $descUrl = buildInternacaoPaginationUrl($paginationBaseParams, ['sort_field' => $key, 'sort_dir' => 'desc', 'pag' => 1]);
                            ?>
                            <th scope="col" style="<?= $meta['style'] ?>" class="text-center">
                                <div class="th-sortable justify-content-center">
                                    <span><?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="sort-icons">
                                        <a href="<?= htmlspecialchars($ascUrl, ENT_QUOTES, 'UTF-8') ?>"
                                            class="<?= $ascActive ? 'active' : '' ?>" title="Ordenar crescente">↑</a>
                                        <a href="<?= htmlspecialchars($descUrl, ENT_QUOTES, 'UTF-8') ?>"
                                            class="<?= $descActive ? 'active' : '' ?>" title="Ordenar decrescente">↓</a>
                                    </span>
                                </div>
                            </th>
                            <?php endforeach; ?>
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
                                <?= htmlspecialchars($intern["nome_hosp"], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td scope="row">
                                <?= htmlspecialchars($intern["nome_pac"], ENT_QUOTES, 'UTF-8') ?>
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
                                            <button type="button" class="btn btn-default" style="font-size: .9rem;"
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
                                            <button type="button" class="btn btn-default" style="font-size: .9rem;"
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
                                <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($firstPageUrl) ?>">
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
                                <a class="page-link" href="<?= htmlspecialchars($prevUrl) ?>">
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
                                <a class="page-link" href="<?= htmlspecialchars($pageUrl) ?>">
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
                                <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($nextUrl) ?>">
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
                                <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($lastUrl) ?>">
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

                        <!-- Última visita médica (quadro clínico) -->
                        <input class="btn-check" type="checkbox" name="colsIntern[]" value="ultima_visita_medico"
                            id="campo_ultima_visita_medico" autocomplete="off" checked>
                        <label class="btn btn-sm rounded-pill export-pill" for="campo_ultima_visita_medico">
                            Última visita médica (quadro clínico)
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
    $('#ordenar').on('change', function() {
        $('input[name="sort_field"]').val('');
        $('input[name="sort_dir"]').val('');
    });

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

<script>
(function() {
    const storageKeys = {
        last: 'fullconex:listInternacao:lastFilter',
        fav: 'fullconex:listInternacao:favorites'
    };
    const form = document.getElementById('select-internacao-form');
    if (!form) return;

    const btnApplyLast = document.getElementById('btnApplyLastFilter');
    const btnSaveFav = document.getElementById('btnSaveFavFilter');
    const btnClear = document.getElementById('btnClearFilters');
    const favoritesWrap = document.getElementById('filterFavorites');
    const favoritesHint = document.getElementById('filterFavoritesHint');
    const smartInput = document.getElementById('smartSearchPhrase');
    const btnSmart = document.getElementById('btnApplySmartSearch');

    const fieldNames = [
        'pesquisa_nome',
        'pesquisa_pac',
        'pesquisa_matricula',
        'senha_int',
        'limite_pag',
        'ordenar',
        'data_intern_int',
        'data_intern_int_max',
        'pesqInternado',
        'sem_senha',
        'sort_field',
        'sort_dir'
    ];

    const hiddenDefaults = {
        pesqInternado: form.elements.namedItem('pesqInternado')?.value || 's',
        sem_senha: form.elements.namedItem('sem_senha')?.value || '0',
        sort_field: '',
        sort_dir: form.elements.namedItem('sort_dir')?.value || 'desc'
    };

    const storageAvailable = (() => {
        try {
            const test = '__fc_filter__';
            localStorage.setItem(test, '1');
            localStorage.removeItem(test);
            return true;
        } catch (err) {
            return false;
        }
    })();

    function readFormValues() {
        const values = {};
        fieldNames.forEach((name) => {
            const field = form.elements.namedItem(name);
            if (!field) return;
            if (field.type === 'checkbox') {
                values[name] = field.checked ? '1' : '0';
            } else {
                values[name] = field.value ?? '';
            }
        });
        return values;
    }

    function fillFormValues(values) {
        if (!values) return;
        fieldNames.forEach((name) => {
            if (!(name in values)) return;
            const field = form.elements.namedItem(name);
            if (!field) return;
            if (field.type === 'checkbox') {
                field.checked = values[name] === '1';
            } else {
                field.value = values[name];
            }
        });
    }

    function persistLastFilter(values) {
        if (!storageAvailable) return;
        localStorage.setItem(storageKeys.last, JSON.stringify(values));
    }

    function getLastFilter() {
        if (!storageAvailable) return null;
        const data = localStorage.getItem(storageKeys.last);
        if (!data) return null;
        try {
            return JSON.parse(data);
        } catch (err) {
            return null;
        }
    }

    function getFavorites() {
        if (!storageAvailable) return [];
        const data = localStorage.getItem(storageKeys.fav);
        if (!data) return [];
        try {
            const parsed = JSON.parse(data);
            return Array.isArray(parsed) ? parsed : [];
        } catch (err) {
            return [];
        }
    }

    function saveFavorites(list) {
        if (!storageAvailable) return;
        localStorage.setItem(storageKeys.fav, JSON.stringify(list));
    }

    function renderFavorites() {
        const favorites = getFavorites();
        if (favoritesWrap) favoritesWrap.innerHTML = '';
        if (!favorites.length) {
            if (favoritesHint) favoritesHint.style.display = 'block';
            return;
        }
        if (favoritesHint) favoritesHint.style.display = 'none';
        favorites.forEach((fav, index) => {
            const chip = document.createElement('div');
            chip.className = 'filter-favorite-chip';
            chip.dataset.index = String(index);
            chip.innerHTML = `
                <span class="label">${fav.label}</span>
                <span class="remove" title="Remover favorito">&times;</span>
            `;
            chip.addEventListener('click', (event) => {
                if (event.target.classList.contains('remove')) return;
                applyFavorite(index);
            });
            chip.querySelector('.remove').addEventListener('click', (event) => {
                event.stopPropagation();
                removeFavorite(index);
            });
            favoritesWrap && favoritesWrap.appendChild(chip);
        });
    }

    function applyFavorite(index) {
        const favorites = getFavorites();
        const fav = favorites[index];
        if (!fav) return;
        fillFormValues(fav.values);
        form.submit();
    }

    function removeFavorite(index) {
        const favorites = getFavorites();
        favorites.splice(index, 1);
        saveFavorites(favorites);
        renderFavorites();
    }

    function handleSaveFavorite() {
        const current = readFormValues();
        const labelDefault = current.pesquisa_nome || current.pesquisa_pac || current.pesquisa_matricula ||
            'Novo favorito';
        const label = prompt('Nome do favorito:', labelDefault);
        if (!label) return;
        const favorites = getFavorites();
        favorites.unshift({
            label: label.trim(),
            savedAt: new Date().toISOString(),
            values: current
        });
        if (favorites.length > 5) {
            favorites.length = 5;
        }
        saveFavorites(favorites);
        renderFavorites();
    }

    function handleApplyLast() {
        const last = getLastFilter();
        if (!last) {
            alert('Nenhum filtro anterior encontrado.');
            return;
        }
        fillFormValues(last);
        form.submit();
    }

    function handleClearFilters() {
        ['pesquisa_nome', 'pesquisa_pac', 'pesquisa_matricula', 'senha_int', 'data_intern_int',
            'data_intern_int_max'
        ].forEach((name) => {
            const field = form.elements.namedItem(name);
            if (field) field.value = '';
        });
        ['limite_pag', 'ordenar'].forEach((name) => {
            const field = form.elements.namedItem(name);
            if (field && field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            }
        });
        Object.keys(hiddenDefaults).forEach((name) => {
            const field = form.elements.namedItem(name);
            if (field) field.value = hiddenDefaults[name];
        });
    }

    function parseSmartPhrase(phrase) {
        if (!phrase) return null;
        const cleaned = phrase.trim();
        if (!cleaned) return null;
        const months = {
            janeiro: '01',
            fevereiro: '02',
            marco: '03',
            março: '03',
            abril: '04',
            maio: '05',
            junho: '06',
            julho: '07',
            agosto: '08',
            setembro: '09',
            outubro: '10',
            novembro: '11',
            dezembro: '12'
        };
        const result = {};
        const lower = cleaned.toLowerCase();

        let monthInfo = null;
        Object.keys(months).some((name) => {
            const regex = new RegExp(name, 'i');
            const match = cleaned.match(regex);
            if (match) {
                const yearMatch = cleaned.match(/20\d{2}/);
                const year = yearMatch ? parseInt(yearMatch[0], 10) : new Date().getFullYear();
                const monthNum = parseInt(months[name], 10);
                const start = `${year}-${String(monthNum).padStart(2, '0')}-01`;
                const endDay = new Date(year, monthNum, 0).getDate();
                const end =
                    `${year}-${String(monthNum).padStart(2, '0')}-${String(endDay).padStart(2, '0')}`;
                result.data_intern_int = start;
                result.data_intern_int_max = end;
                monthInfo = {
                    index: match.index,
                    length: match[0].length
                };
                return true;
            }
            return false;
        });

        const hospRegex =
            /(?:contas|hospital|hosp)\s+([^0-9]+?)(?=(?:janeiro|fevereiro|mar[cç]o|abril|maio|junho|julho|agosto|setembro|outubro|novembro|dezembro|paciente|\d{4}|$))/i;
        const hospMatch = cleaned.match(hospRegex);
        if (hospMatch) {
            result.pesquisa_nome = hospMatch[1].trim();
        } else if (monthInfo && monthInfo.index > 0) {
            const possible = cleaned.slice(0, monthInfo.index).replace(/^(contas|hospital|hosp)\s+/i, '').trim();
            if (possible) result.pesquisa_nome = possible;
        }

        const pacRegex =
            /paciente\s+([^0-9]+?)(?=(?:contas|hospital|hosp|janeiro|fevereiro|mar[cç]o|abril|maio|junho|julho|agosto|setembro|outubro|novembro|dezembro|\d{4}|$))/i;
        const pacMatch = cleaned.match(pacRegex);
        if (pacMatch) {
            result.pesquisa_pac = pacMatch[1].trim();
        }

        const senhaMatch = cleaned.match(/senha\s+([\w-]+)/i);
        if (senhaMatch) {
            result.senha_int = senhaMatch[1];
        }

        const matriculaMatch = cleaned.match(/matr[íi]cula\s+([\w.-]+)/i);
        if (matriculaMatch) {
            result.pesquisa_matricula = matriculaMatch[1];
        }

        if (Object.keys(result).length === 0) {
            return null;
        }
        return result;
    }

    function handleSmartSearch() {
        const phrase = smartInput.value;
        const parsed = parseSmartPhrase(phrase);
        if (!parsed) {
            alert('Não foi possível interpretar esta frase. Tente informar hospital, paciente ou mês.');
            return;
        }
        fillFormValues(parsed);
        form.submit();
    }

    if (storageAvailable) {
        const hasQuery = window.location.search.length > 1;
        const last = getLastFilter();
        if (last && !hasQuery) {
            fillFormValues(last);
        }
        renderFavorites();
    } else {
        if (favoritesHint) {
            favoritesHint.textContent = 'Memória de filtros não disponível neste navegador.';
            favoritesHint.style.display = 'block';
        }
    }

    form.addEventListener('submit', () => {
        const values = readFormValues();
        persistLastFilter(values);
    });

    if (btnSaveFav) btnSaveFav.addEventListener('click', handleSaveFavorite);
    if (btnApplyLast) btnApplyLast.addEventListener('click', handleApplyLast);
    if (btnClear) btnClear.addEventListener('click', handleClearFilters);
    if (btnSmart) btnSmart.addEventListener('click', handleSmartSearch);
    if (smartInput) {
        smartInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                handleSmartSearch();
            }
        });
    }
})();
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
