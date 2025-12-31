<?php
ob_start();

require_once("templates/header.php");
require_once("models/message.php");

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(16));
}

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/patologia.php");
include_once("dao/patologiaDao.php");

include_once("models/paciente.php");
include_once("dao/pacienteDao.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("models/alta.php");
include_once("dao/altaDao.php");

include_once("models/pagination.php");

$somenteListaAltas = isset($somenteListaAltas) ? (bool)$somenteListaAltas : false;

$altaDao    = new altaDAO($conn, $BASE_URL);
$internacao = new internacaoDAO($conn, $BASE_URL);

/* ===================== FILTROS VIA GET ===================== */

$pesquisa_nome   = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$pesquisa_pac    = filter_input(INPUT_GET, 'pesquisa_pac',   FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$pesquisa_matricula = filter_input(INPUT_GET, 'pesquisa_matricula', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$pesqInternado   = filter_input(INPUT_GET, 'pesqInternado',  FILTER_SANITIZE_SPECIAL_CHARS) ?: 's';
$limite          = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT) ?: 10;
$ordenar         = filter_input(INPUT_GET, 'ordenar', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$data_alta       = filter_input(INPUT_GET, 'data_alta', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$data_alta_max   = filter_input(INPUT_GET, 'data_alta_max', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';

if ($data_alta && !$data_alta_max) {
    $data_alta_max = date('Y-m-d');
}

/* ===================== WHERE (MESMA LÓGICA DO EXPORT) ===================== */

$condicoes = [];

// Hospital (ho.nome_hosp)
if (strlen(trim((string)$pesquisa_nome)) > 0) {
    $condicoes[] = 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"';
}

// Paciente (pa.nome_pac)
if (strlen(trim((string)$pesquisa_pac)) > 0) {
    $condicoes[] = 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"';
}
if (strlen(trim((string)$pesquisa_matricula)) > 0) {
    $condicoes[] = 'pa.matricula_pac LIKE "%' . $pesquisa_matricula . '%"';
}

// Data de alta
if (strlen(trim((string)$data_alta)) > 0) {
    $ini = $data_alta;
    $fim = $data_alta_max ?: $data_alta;
    $condicoes[] = 'alta.data_alta_alt BETWEEN "' . $ini . '" AND "' . $fim . '"';
}

$condicoes = array_filter($condicoes);
$where     = implode(' AND ', $condicoes);

/* ===================== CONTAGEM + PAGINAÇÃO ===================== */

$order = $ordenar ?: 'id_internacao DESC';
$qtdIntItens1 = $altaDao->findAltaWhere($where, $order, null);
$qtdIntItens  = is_countable($qtdIntItens1) ? count($qtdIntItens1) : 0;

$obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);
$obLimite     = $obPagination->getLimit();

$query = $altaDao->findAltaWhere($where, $order, $obLimite ?: null);

if ($qtdIntItens > $limite) {
    $paginas     = $obPagination->getPages();
    $total_pages = count($paginas);

    function paginasAtuais($var)
    {
        $blocoAtual = isset($_GET['bl']) ? (int)$_GET['bl'] : 0;
        return $var['bloco'] == (($blocoAtual) / 5) + 1;
    }

    $block_pages         = array_filter($paginas, "paginasAtuais");
    $first_page_in_block = $block_pages ? reset($block_pages)["pg"] : 1;
    $last_page_in_block  = $block_pages ? end($block_pages)["pg"]   : 1;
    $first_block         = $paginas ? reset($paginas)["bloco"]      : 1;
    $last_block          = $paginas ? end($paginas)["bloco"]        : 1;
    $current_block       = $block_pages ? reset($block_pages)["bloco"] : 1;
} else {
    $total_pages = 1;
    $first_page_in_block = $last_page_in_block = $first_block = $last_block = $current_block = 1;
    $paginas = [];
    $block_pages = [];
}

$paginationParams = [
    'pesquisa_nome' => $pesquisa_nome,
    'pesquisa_pac' => $pesquisa_pac,
    'pesquisa_matricula' => $pesquisa_matricula,
    'pesqInternado' => $pesqInternado,
    'limite' => $limite,
    'ordenar' => $ordenar,
    'data_alta' => $data_alta,
    'data_alta_max' => $data_alta_max
];

$buildListaAltaLink = function($pagina, $bloco) use ($paginationParams, $BASE_URL, $somenteListaAltas) {
    $params = $paginationParams;
    $params['bl'] = $bloco;

    $params = array_filter($params, function($value) {
        return $value !== null && $value !== '' && $value !== false;
    });

    $pathBase = $somenteListaAltas ? 'listas/altas' : 'internacoes/reverter-alta';
    $pagina = max(1, (int)$pagina);
    $path = rtrim($BASE_URL, '/') . '/' . $pathBase . '/pagina/' . $pagina;

    $query = http_build_query($params);
    return $path . ($query ? '?' . $query : '');
};

?>
<style>
    /* Chips roxos para seleção de campos (modal export) */
    .export-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        border-radius: 999px;
        background-color: #5e2363;
        color: #ffffff;
        font-size: 0.85rem;
        border: none;
        cursor: pointer;
        margin: 4px 6px 4px 0;
        white-space: nowrap;
    }

    .export-pill.inactive {
        background-color: #f1f1f1;
        color: #5e2363;
        border: 1px solid #5e2363;
    }

    .export-pill i {
        font-size: 0.8rem;
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

    .tabela-altas thead th {
        padding: 0.8rem 0.95rem;
        font-size: 0.82rem;
    }

    .tabela-altas tbody td,
    .tabela-altas tbody th {
        padding: 0.78rem 0.95rem;
        font-size: 0.82rem;
        vertical-align: middle;
    }
</style>

<div class="container-fluid form_container" id="main-container" style="margin-top:-25px;">

    <div class="d-flex justify-content-between align-items-center">
        <h4 class="page-title">Alta Hospitalar</h4>

        <!-- Botão Excel: abre modal para escolher campos -->
        <a href="#" id="btnExportExcelAlta" class="btn btn-success btn-sm text-white"
            style="background-color:#198754; border-color:#198754;">
            <i class="fa-solid fa-file-excel me-1 text-white"></i> Exportar Excel
        </a>
    </div>

    <hr>

    <div class="complete-table">
        <div id="navbarToggleExternalContent" class="table-filters">
            <div>
                <form action="" id="select-internacao-form" method="GET">
                    <div class="row">
                        <div class="col-sm-2" style="padding:2px !important;padding-left:16px !important;">
                            <input class="form-control form-control-sm" style="margin-top:7px;" type="text"
                                name="pesquisa_nome" placeholder="Selecione o Hospital"
                                value="<?= htmlspecialchars((string)$pesquisa_nome) ?>">
                        </div>
                        <div class="col-sm-2" style="padding:2px !important">
                            <input class="form-control form-control-sm" style="margin-top:7px;" type="text"
                                name="pesquisa_pac" placeholder="Selecione o Paciente"
                                value="<?= htmlspecialchars((string)$pesquisa_pac) ?>">
                        </div>
                        <div class="col-sm-2" style="padding:2px !important">
                            <input class="form-control form-control-sm" style="margin-top:7px;" type="text"
                                name="pesquisa_matricula" placeholder="Matrícula"
                                value="<?= htmlspecialchars((string)$pesquisa_matricula) ?>">
                        </div>

                        <div class="col-sm-1" style="padding:2px !important">
                            <select class="form-control mb-3 form-control-sm" style="margin-top:7px;" id="limite"
                                name="limite">
                                <option value="">Reg por página</option>
                                <option value="5" <?= $limite == 5  ? 'selected' : null ?>>Reg por pág = 5</option>
                                <option value="10" <?= $limite == 10 ? 'selected' : null ?>>Reg por pág = 10</option>
                                <option value="20" <?= $limite == 20 ? 'selected' : null ?>>Reg por pág = 20</option>
                                <option value="50" <?= $limite == 50 ? 'selected' : null ?>>Reg por pág = 50</option>
                            </select>
                        </div>

                        <div class="col-sm-2" style="padding:2px !important">
                            <select class="form-control mb-3 form-control-sm" style="margin-top:7px;" id="ordenar"
                                name="ordenar">
                                <option value="">Classificar por</option>
                                <option value="id_internacao" <?= $ordenar == 'id_internacao' ? 'selected' : null ?>>
                                    Internação
                                </option>
                                <option value="nome_pac" <?= $ordenar == 'nome_pac' ? 'selected' : null ?>>
                                    Paciente
                                </option>
                                <option value="nome_hosp" <?= $ordenar == 'nome_hosp' ? 'selected' : null ?>>
                                    Hospital
                                </option>
                                <option value="data_alta_alt" <?= $ordenar == 'data_alta_alt' ? 'selected' : null ?>>
                                    Data Alta
                                </option>
                            </select>
                        </div>

                        <div class="col-sm-1" style="padding:2px !important">
                            <input class="form-control form-control-sm" type="date" style="margin-top:7px;"
                                name="data_alta" placeholder="Data Alta Min"
                                value="<?= htmlspecialchars((string)$data_alta) ?>">
                        </div>

                        <div class="col-sm-1" style="padding:2px !important">
                            <input class="form-control form-control-sm" type="date" style="margin-top:7px;"
                                name="data_alta_max" placeholder="Data Alta Max"
                                value="<?= htmlspecialchars((string)$data_alta_max) ?>">
                        </div>

                        <div class="col-sm-1" style="padding:2px !important">
                            <button type="submit" class="btn btn-primary"
                                style="background-color:#5e2363;width:42px;height:32px;margin-top:7px;border-color:#5e2363">
                                <span class="material-icons" style="margin-left:-3px;margin-top:-2px;">search</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- BASE DAS PESQUISAS -->
        <div>
            <div id="table-content">

                <table class="table table-sm table-striped table-hover table-condensed tabela-altas">
                    <thead>
                        <tr>
                            <th scope="col" width="3%">Id-Int</th>
                            <th scope="col" width="3%">UTI</th>
                            <th scope="col" width="14%">Hospital</th>
                            <th scope="col" width="14%">Paciente</th>
                            <th scope="col" width="7%">Tipo Alta</th>
                            <th scope="col" width="8%">Data Alta</th>
                            <?php if (!$somenteListaAltas): ?>
                            <th scope="col" width="4%">Remover</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($query as $intern): ?>
                            <tr style="font-size:15px">
                                <td scope="row" class="col-id">
                                    <?= htmlspecialchars((string)$intern["fk_id_int_alt"]) ?>
                                </td>
                                <td scope="row" class="col-id">
                                    <?= !empty($intern["id_uti"]) ? 'Sim' : 'Não' ?>
                                </td>
                                <td scope="row">
                                    <?= htmlspecialchars((string)$intern["nome_hosp"]) ?>
                                </td>
                                <td scope="row">
                                    <?= htmlspecialchars((string)$intern["nome_pac"]) ?>
                                </td>
                                <td scope="row">
                                    <?= htmlspecialchars((string)$intern["tipo_alta_alt"]) ?>
                                </td>
                                <td scope="row">
                                    <?= htmlspecialchars(date('d/m/Y', strtotime((string)$intern["data_alta_alt"]))) ?>
                                </td>
                                <?php if (!$somenteListaAltas): ?>
                                <td>
                                    <input type="checkbox" class="ckAlta" value="<?= (int)$intern['id_alta'] ?>">
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <?php if ($qtdIntItens == 0): ?>
                            <tr>
                                <td colspan="7" scope="row" class="col-id" style="font-size:15px">
                                    Não foram encontrados registros
                                </td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>

                <div style="text-align:right">
                    <input type="hidden" id="qtd" value="<?= (int)$qtdIntItens ?>">
                </div>

                <div style="display: flex;margin-top:20px">
                    <div class="pagination" style="margin: 0 auto;">

                        <?php if (($total_pages ?? 1) > 1): ?>
                            <ul class="pagination">
                                <?php
                                $blocoAtual  = isset($_GET['bl']) ? (int)$_GET['bl'] : 0;
                                $paginaAtual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
                                ?>
                                <?php if ($current_block > $first_block): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $buildListaAltaLink(1, 0) ?>">
                                            <i class="fa-solid fa-angles-left"></i></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $buildListaAltaLink($paginaAtual - 1, $blocoAtual - 5) ?>">
                                            <i class="fa-solid fa-angle-left"></i> </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++): ?>
                                    <li class="page-item <?= ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">
                                        <a class="page-link" href="<?= $buildListaAltaLink($i, $blocoAtual) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_block < $last_block): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $buildListaAltaLink($paginaAtual + 1, $blocoAtual + 5) ?>">
                                            <i class="fa-solid fa-angle-right"></i></a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($current_block < $last_block): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $buildListaAltaLink(count($paginas), ($last_block - 1) * 5) ?>">
                                            <i class="fa-solid fa-angles-right"></i></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <?php if (!$somenteListaAltas): ?>
                    <div class="col-sm-3">
                        <button id="btnRemoveAltas" class="btn btn-outline-danger">
                            <i class="fa-solid fa-trash-can me-1"></i> Remover alta(s) selecionada(s)
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="table-counter">
                        <p style="margin-bottom:25px;font-size:1em;font-weight:600;
                                  font-family:var(--bs-font-sans-serif);text-align:right">
                            <?= "Total: " . (int)$qtdIntItens ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$somenteListaAltas): ?>
<div class="modal fade" id="modalReverterAlta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:1rem;">
            <div class="modal-header">
                <h5 class="modal-title">Reverter alta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Confirmar a reversão de <strong><span id="qtdAltasSel">0</span></strong> alta(s)?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmReverter" class="btn btn-danger">Confirmar reversão</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal: Mensagem (info/erro) -->
<div class="modal fade" id="modalMsg" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:1rem;">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMsgTitle">Aviso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" id="modalMsgBody">...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Selecionar campos do Excel -->
<div class="modal fade" id="modalExportAltaCampos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:1rem;">
            <div class="modal-header">
                <h5 class="modal-title">Campos a exibir/exportar para o Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <!-- Barra Selecionar todos / Limpar -->
                <div class="export-pill-toolbar">
                    <button type="button" class="btn btn-light btn-sm" id="btnExportSelectAllAlta">
                        ✓ Selecionar todos
                    </button>
                    <button type="button" class="btn btn-light btn-sm" id="btnExportClearAlta">
                        ✕ Limpar
                    </button>
                </div>

                <!-- Chips -->
                <div class="mb-2">
                    <!-- ID Internação -->
                    <button type="button" class="export-pill" data-target="#cbColIdInt">
                        # ID da internação
                    </button>
                    <input type="checkbox" class="d-none" id="cbColIdInt" name="colsAlta[]" value="id_int" checked>

                    <!-- Hospital -->
                    <button type="button" class="export-pill" data-target="#cbColHosp">
                        <i class="fa-solid fa-hospital"></i> Hospital
                    </button>
                    <input type="checkbox" class="d-none" id="cbColHosp" name="colsAlta[]" value="hosp" checked>

                    <!-- Paciente -->
                    <button type="button" class="export-pill" data-target="#cbColPac">
                        <i class="fa-solid fa-user"></i> Nome do paciente
                    </button>
                    <input type="checkbox" class="d-none" id="cbColPac" name="colsAlta[]" value="pac" checked>

                    <!-- Tipo Alta -->
                    <button type="button" class="export-pill" data-target="#cbColTipoAlta">
                        <i class="fa-regular fa-square-check"></i> Tipo alta
                    </button>
                    <input type="checkbox" class="d-none" id="cbColTipoAlta" name="colsAlta[]" value="tipo_alta"
                        checked>

                    <!-- Data Alta -->
                    <button type="button" class="export-pill" data-target="#cbColDataAlta">
                        <i class="fa-regular fa-calendar"></i> Data alta
                    </button>
                    <input type="checkbox" class="d-none" id="cbColDataAlta" name="colsAlta[]" value="data_alta"
                        checked>

                    <!-- UTI -->
                    <button type="button" class="export-pill" data-target="#cbColUti">
                        UTI
                    </button>
                    <input type="checkbox" class="d-none" id="cbColUti" name="colsAlta[]" value="uti" checked>

                    <!-- Senha -->
                    <button type="button" class="export-pill inactive" data-target="#cbColSenha">
                        Senha
                    </button>
                    <input type="checkbox" class="d-none" id="cbColSenha" name="colsAlta[]" value="senha">

                    <!-- Matrícula -->
                    <button type="button" class="export-pill inactive" data-target="#cbColMatricula">
                        Matrícula
                    </button>
                    <input type="checkbox" class="d-none" id="cbColMatricula" name="colsAlta[]" value="matricula">

                    <!-- Evolução (relatório) -->
                    <button type="button" class="export-pill inactive" data-target="#cbColEvolucao">
                        Relatório / Evolução
                    </button>
                    <input type="checkbox" class="d-none" id="cbColEvolucao" name="colsAlta[]" value="evolucao">

                    <!-- Ações -->
                    <button type="button" class="export-pill inactive" data-target="#cbColAcoes">
                        Ações
                    </button>
                    <input type="checkbox" class="d-none" id="cbColAcoes" name="colsAlta[]" value="acoes">

                    <!-- Programação -->
                    <button type="button" class="export-pill inactive" data-target="#cbColProgramacao">
                        Programação
                    </button>
                    <input type="checkbox" class="d-none" id="cbColProgramacao" name="colsAlta[]" value="programacao">

                    <!-- Especialidade -->
                    <button type="button" class="export-pill inactive" data-target="#cbColEspecialidade">
                        Especialidade
                    </button>
                    <input type="checkbox" class="d-none" id="cbColEspecialidade" name="colsAlta[]"
                        value="especialidade">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmExportAlta" class="btn btn-success">
                    Exportar XLSX (Excel)
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<script>
    const SOMENTE_LISTA_ALTAS = <?= $somenteListaAltas ? 'true' : 'false' ?>;
    (function($) {
        function showMsg(title, body) {
            $('#modalMsgTitle').text(title || 'Aviso');
            $('#modalMsgBody').html(body || '');
            new bootstrap.Modal(document.getElementById('modalMsg')).show();
        }

        // Abre modal de campos ao clicar em Exportar Excel
        $(document).on('click', '#btnExportExcelAlta', function(e) {
            e.preventDefault();
            e.stopPropagation();
            new bootstrap.Modal(document.getElementById('modalExportAltaCampos')).show();
        });

        // Clique em um chip: alterna estado + checkbox oculto
        $(document).on('click', '.export-pill', function() {
            var $pill = $(this);
            var target = $pill.data('target');
            var $cb = $(target);

            var ativo = !$pill.hasClass('inactive');
            if (ativo) {
                $pill.addClass('inactive');
                $cb.prop('checked', false);
            } else {
                $pill.removeClass('inactive');
                $cb.prop('checked', true);
            }
        });

        // Selecionar todos
        $(document).on('click', '#btnExportSelectAllAlta', function() {
            $('.export-pill').removeClass('inactive');
            $('input[name="colsAlta[]"]').prop('checked', true);
        });

        // Limpar
        $(document).on('click', '#btnExportClearAlta', function() {
            $('.export-pill').addClass('inactive');
            $('input[name="colsAlta[]"]').prop('checked', false);
        });

        // Confirmar exportação
        $(document).on('click', '#btnConfirmExportAlta', function(e) {
            e.preventDefault();

            var cols = [];
            $('input[name="colsAlta[]"]:checked').each(function() {
                cols.push($(this).val());
            });

            if (!cols.length) {
                showMsg('Seleção necessária', 'Selecione pelo menos um campo para exportar.');
                return;
            }

            var query = $('#select-internacao-form').serialize();
            var colsParam = 'cols=' + encodeURIComponent(cols.join(','));

            if (query) {
                query += '&' + colsParam;
            } else {
                query = colsParam;
            }

            var url = '<?= $BASE_URL ?>exportar_excel_list_alta.php';
            if (query) {
                url += '?' + query;
            }

            var modalEl = document.getElementById('modalExportAltaCampos');
            var modalObj = bootstrap.Modal.getInstance(modalEl);
            if (modalObj) modalObj.hide();

            window.open(url, '_blank');
        });

        // Submit filtros (AJAX)
        $(document)
            .off('submit.alta', '#select-internacao-form')
            .on('submit.alta', '#select-internacao-form', function(e) {
                e.preventDefault();
                var $form = $(this);
                $.ajax({
                    url: $form.attr('action') || 'list_internacao_alta.php',
                    type: $form.attr('method') || 'GET',
                    data: $form.serialize(),
                    success: function(response) {
                        var temp = document.createElement('div');
                        temp.innerHTML = response;
                        var tableContent = temp.querySelector('#table-content');
                        if (tableContent) {
                            $('#table-content').html(tableContent.innerHTML);
                        }
                    },
                    error: function() {
                        showMsg('Erro', 'Ocorreu um erro ao enviar o formulário.');
                    }
                });
            });

        if (!SOMENTE_LISTA_ALTAS) {
            let idsSelecionados = [];

            $(document)
                .off('click.alta', '#btnRemoveAltas')
                .on('click.alta', '#btnRemoveAltas', function(e) {
                    e.preventDefault();
                    idsSelecionados = $('.ckAlta:checked').map(function() {
                        return $(this).val();
                    }).get();
                    if (!idsSelecionados.length) {
                        showMsg('Seleção necessária', 'Selecione pelo menos uma alta para reverter.');
                        return;
                    }
                    $('#qtdAltasSel').text(idsSelecionados.length);
                    new bootstrap.Modal(document.getElementById('modalReverterAlta')).show();
                });

            $(document)
                .off('click.alta', '#btnConfirmReverter')
                .on('click.alta', '#btnConfirmReverter', function() {
                    var $btn = $(this);
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: 'alta_reverter.php',
                        type: 'POST',
                        data: {
                            ids: idsSelecionados
                        },
                        success: function(resp) {
                            const j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                            if (j && j.ok) {
                                bootstrap.Modal.getInstance(document.getElementById('modalReverterAlta'))
                                    .hide();
                                location.reload();
                            } else {
                                showMsg('Falha', (j && j.msg) ? j.msg : 'Falha ao reverter.');
                            }
                        },
                        error: function() {
                            showMsg('Erro de comunicação', 'Não foi possível contatar o servidor.');
                        },
                        complete: function() {
                            $btn.prop('disabled', false);
                        }
                    });
                });
        }

    })(jQuery);
</script>

<script src="./js/input-estilo.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="./scripts/cadastro/general.js"></script>
<script src="./js/ajaxNav.js"></script>
