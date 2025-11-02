<?php

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

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$Internacaos = $Internacao_geral->findGeral();

$limite  = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT) ?: 10;
$inicio  = 0;

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$pacientes   = $pacienteDao->findGeral($limite, $inicio);

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$hospitals      = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias   = $patologiaDao->findGeral();

$altaDao    = new altaDAO($conn, $BASE_URL);
$internacao = new internacaoDAO($conn, $BASE_URL);

?>
<!-- FORMULARIO DE PESQUISAS -->
<div class="container-fluid form_container" id='main-container' style="margin-top:-25px;">
    <h4 class="page-title">Alta Hospitalar</h4>
    <hr>
    <div class="complete-table">
        <div id="navbarToggleExternalContent" class="table-filters">
            <div>
                <form action="" id="select-internacao-form" method="GET">
                    <?php
                    $pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS);
                    $pesqInternado = filter_input(INPUT_GET, 'pesqInternado', FILTER_SANITIZE_SPECIAL_CHARS) ?: "s";
                    $limite        = filter_input(INPUT_GET, 'limite') ?: $limite;
                    $pesquisa_pac  = filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS);
                    $ordenar       = filter_input(INPUT_GET, 'ordenar');
                    $data_alta     = filter_input(INPUT_GET, 'data_alta', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
                    $data_alta_max = filter_input(INPUT_GET, 'data_alta_max', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
                    ?>
                    <div class="row">
                        <div class="col-sm-2" style="padding:2px !important;padding-left:16px !important;">
                            <input class="form-control form-control-sm" style="margin-top:7px;" type="text"
                                name="pesquisa_nome" placeholder="Selecione o Hospital" value="<?= $pesquisa_nome ?>">
                        </div>
                        <div class="col-sm-2" style="padding:2px !important">
                            <input class="form-control form-control-sm" style="margin-top:7px;" type="text"
                                name="pesquisa_pac" placeholder="Selecione o Paciente" value="<?= $pesquisa_pac ?>">
                        </div>

                        <div class="col-sm-1" style="padding:2px !important">
                            <select class="form-control mb-3 form-control-sm" style="margin-top:7px;" id="limite"
                                name="limite">
                                <option value="">Reg por página</option>
                                <option value="5" <?= $limite == '5'  ? 'selected' : null ?>>Reg por pág = 5</option>
                                <option value="10" <?= $limite == '10' ? 'selected' : null ?>>Reg por pág = 10</option>
                                <option value="20" <?= $limite == '20' ? 'selected' : null ?>>Reg por pág = 20</option>
                                <option value="50" <?= $limite == '50' ? 'selected' : null ?>>Reg por pág = 50</option>
                            </select>
                        </div>
                        <div class="col-sm-2" style="padding:2px !important">
                            <select class="form-control mb-3 form-control-sm" style="margin-top:7px;" id="ordenar"
                                name="ordenar">
                                <option value="">Classificar por</option>
                                <option value="id_internacao" <?= $ordenar == 'id_internacao'   ? 'selected' : null ?>>
                                    Internação</option>
                                <option value="nome_pac" <?= $ordenar == 'nome_pac'        ? 'selected' : null ?>>
                                    Paciente</option>
                                <option value="nome_hosp" <?= $ordenar == 'nome_hosp'       ? 'selected' : null ?>>
                                    Hospital</option>
                                <option value="data_intern_int"
                                    <?= $ordenar == 'data_intern_int' ? 'selected' : null ?>>Data Internação</option>
                            </select>
                        </div>
                        <div class="col-sm-1" style="padding:2px !important">
                            <input class="form-control form-control-sm" type="date" style="margin-top:7px;"
                                name="data_alta" placeholder="Data Alta Min" value="<?= $data_alta ?>">
                        </div>
                        <div class="col-sm-1" style="padding:2px !important">
                            <input class="form-control form-control-sm" type="date" style="margin-top:7px;"
                                name="data_alta_max" placeholder="Data Alta Max" value="<?= $data_alta_max ?>">
                        </div>
                        <div class="col-sm-1" style="padding:2px !important" style="margin:0px 0px 20px 0px">
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
        <?php
        // validacao de lista de hospital por usuario (o nivel sera o filtro)
        if (isset($_SESSION['nivel']) && $_SESSION['nivel'] == 3) {
            $auditor = ($_SESSION['id_usuario']);
        } else {
            $auditor = null;
        };

        $QtdTotalInt = new internacaoDAO($conn, $BASE_URL);

        // METODO DE BUSCA DE PAGINACAO 
        $pesquisa_nome   = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $limite          = filter_input(INPUT_GET, 'limite') ? filter_input(INPUT_GET, 'limite') : $limite;
        $pesquisa_pac    = filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS);
        $ordenar         = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 'id_internacao';
        $data_intern_int = filter_input(INPUT_GET, 'data_intern_int') ?: null;
        $data_alta       = filter_input(INPUT_GET, 'data_alta') ?: null;
        $data_alta_max   = filter_input(INPUT_GET, 'data_alta_max') ?: null;
        if (empty($data_alta_max)) {
            $data_alta_max = date('Y-m-d');
        }

        $condicoes = [
            strlen((string)$pesquisa_nome)   ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"' : null,
            strlen((string)$pesquisa_pac)    ? 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"'  : null,
            strlen((string)$data_intern_int) ? 'data_intern_int = "' . $data_intern_int . '"' : null,
            strlen((string)$auditor)         ? 'hos.fk_usuario_hosp = "' . $auditor . '"'     : null,
            strlen((string)$data_alta)       ? 'alta.data_alta_alt BETWEEN "' . $data_alta . '" AND "' . $data_alta_max . '"' : null
        ];
        $condicoes = array_filter($condicoes);
        $where     = implode(' AND ', $condicoes);

        // contagem
        $qtdIntItens1 = $altaDao->findAltaWhere($where, $order ?? null, $obLimite ?? null);
        $qtdIntItens  = is_countable($qtdIntItens1) ? count($qtdIntItens1) : 0;

        // ordem + paginação
        $order        = $ordenar;
        $obPagination = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);
        $obLimite     = $obPagination->getLimit();

        // consulta paginada
        $query = $altaDao->findAltaWhere($where, $order ?? null, $obLimite ?? null);

        // paginação blocada
        if ($qtdIntItens > $limite) {
            $paginas        = $obPagination->getPages();
            $total_pages    = count($paginas);

            function paginasAtuais($var)
            {
                $blocoAtual = isset($_GET['bl']) ? $_GET['bl'] : 0;
                return $var['bloco'] == (($blocoAtual) / 5) + 1;
            }
            $block_pages        = array_filter($paginas, "paginasAtuais");
            $first_page_in_block = $block_pages ? reset($block_pages)["pg"] : 1;
            $last_page_in_block = $block_pages ? end($block_pages)["pg"]   : 1;
            $first_block        = $paginas ? reset($paginas)["bloco"]      : 1;
            $last_block         = $paginas ? end($paginas)["bloco"]        : 1;
            $current_block      = $block_pages ? reset($block_pages)["bloco"] : 1;
        } else {
            $total_pages = 1;
            $first_page_in_block = $last_page_in_block = $first_block = $last_block = $current_block = 1;
            $paginas = [];
            $block_pages = [];
        }
        ?>
        <div>
            <div id="table-content">
                <table class="table table-sm table-striped  table-hover table-condensed">
                    <thead>
                        <tr>
                            <th scope="col" width="3%">Id-Int</th>
                            <th scope="col" width="3%">UTI</th>
                            <th scope="col" width="14%">Hospital</th>
                            <th scope="col" width="14%">Paciente</th>
                            <th scope="col" width="7%">Tipo Alta</th>
                            <th scope="col" width="8%">Data Alta</th>
                            <th scope="col" width="4%">Remover</th>
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
                            <td>
                                <input type="checkbox" class="ckAlta" value="<?= (int)$intern['id_alta'] ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if ($qtdIntItens == 0): ?>
                        <tr>
                            <td colspan="7" scope="row" class="col-id" style='font-size:15px'>
                                Não foram encontrados registros
                            </td>
                        </tr>
                        <?php endif ?>
                    </tbody>
                </table>

                <div style="text-align:right">
                    <input type="hidden" id="qtd" value="<?php echo (int)$qtdIntItens ?>">
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
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_internacao_alta.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print 1 ?>&bl=<?php print 0 ?>')">
                                    <i class="fa-solid fa-angles-left"></i></a>
                            </li>
                            <?php endif; ?>
                            <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="#"
                                    onclick="loadContent('list_internacao_alta.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual - 1 ?>&bl=<?php print $blocoAtual - 5 ?>')">
                                    <i class="fa-solid fa-angle-left"></i> </a>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++): ?>
                            <li class="page-item <?php print ($_GET['pag'] ?? 1) == $i ? "active" : "" ?>">
                                <a class="page-link" href="#"
                                    onclick="loadContent('list_internacao_alta.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $i ?>&bl=<?php print $blocoAtual ?>')">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($current_block < $last_block): ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_internacao_alta.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print $paginaAtual + 1 ?>&bl=<?php print $blocoAtual + 5 ?>')"><i
                                        class="fa-solid fa-angle-right"></i></a>
                            </li>
                            <?php endif; ?>
                            <?php if ($current_block < $last_block): ?>
                            <li class="page-item">
                                <a class="page-link" id="blocoNovo" href="#"
                                    onclick="loadContent('list_internacao_alta.php?pesquisa_nome=<?php print $pesquisa_nome ?>&pesquisa_pac=<?php print $pesquisa_pac ?>&pesqInternado=<?php print $pesqInternado ?>&limite=<?php print $limite ?>&ordenar=<?php print $ordenar ?>&pag=<?php print count($paginas) ?>&bl=<?php print ($last_block - 1) * 5 ?>')"><i
                                        class="fa-solid fa-angles-right"></i></a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </div>

                    <!-- MANTEVE A POSIÇÃO DO BOTÃO -->
                    <div class="col-sm-3">
                        <button id="btnRemoveAltas" class="btn btn-outline-danger">
                            <i class="fa-solid fa-trash-can me-1"></i> Remover alta(s) selecionada(s)
                        </button>
                    </div>

                    <div class="table-counter">
                        <p
                            style="margin-bottom:25px;font-size:1em; font-weight:600; font-family:var(--bs-font-sans-serif); text-align:right">
                            <?php echo "Total: " . (int)$qtdIntItens ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function($) {
    // ---- Helpers de rota/filtros atuais
    function reloadLista() {
        loadContent(
            'list_internacao_alta.php?pesquisa_nome=<?= $pesquisa_nome ?>' +
            '&pesquisa_pac=<?= $pesquisa_pac ?>' +
            '&pesqInternado=<?= $pesqInternado ?>' +
            '&limite=<?= $limite ?>' +
            '&ordenar=<?= $ordenar ?>' +
            '&pag=<?= $_GET["pag"] ?? 1 ?>' +
            '&bl=<?= $_GET["bl"] ?? 0 ?>'
        );
    }

    // ---- SUBMIT do formulário (com namespace para não duplicar)
    $(document)
        .off('submit.alta', '#select-internacao-form')
        .on('submit.alta', '#select-internacao-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            $.ajax({
                url: $form.attr('action'),
                type: $form.attr('method') || 'GET',
                data: $form.serialize(),
                success: function(response) {
                    var temp = document.createElement('div');
                    temp.innerHTML = response;
                    var tableContent = temp.querySelector('#table-content');
                    if (tableContent) $('#table-content').html(tableContent);
                },
                error: function() {
                    $('#responseMessage').html('Ocorreu um erro ao enviar o formulário.');
                }
            });
        });

    // ---- Clique no botão "Remover alta(s)" (único bind, com namespace)
    $(document)
        .off('click.alta', '#btnRemoveAltas')
        .on('click.alta', '#btnRemoveAltas', function(e) {
            e.preventDefault();

            const ids = $('.ckAlta:checked').map(function() {
                return $(this).val();
            }).get();
            if (!ids.length) {
                alert('Selecione pelo menos uma alta.');
                return;
            }

            if (!confirm('Confirmar a reversão de ' + ids.length + ' alta(s)?')) return;

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: 'alta_reverter.php', // endpoint final
                type: 'POST',
                data: {
                    ids: ids
                },
                success: function(resp) {
                    try {
                        const j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                        if (j && j.ok) {
                            reloadLista();
                        } else {
                            alert((j && j.msg) ? j.msg : 'Falha ao reverter.');
                        }
                    } catch (err) {
                        alert('Erro inesperado ao processar a resposta.');
                    }
                },
                error: function() {
                    alert('Erro de comunicação.');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

    // ---- Primeira carga
    $(function() {
        reloadLista();
    });

})(jQuery);
</script>


<script src="./js/input-estilo.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

<!-- (Seu include de jQuery estava malformado; sem mexer em posição do botão/selects, só corrijo a tag para funcionar) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<script src="./scripts/cadastro/general.js"></script>
<script src="./js/ajaxNav.js"></script>
<script>
$(document).on('click', '#btnRemoveAltas', function(e) {
    e.preventDefault();

    const ids = $('.ckAlta:checked').map(function() {
        return $(this).val();
    }).get();
    if (!ids.length) {
        alert('Selecione pelo menos uma alta.');
        return;
    }

    if (!confirm('Confirmar marcação de NÃO internado para ' + ids.length + ' alta(s)?')) return;

    $.ajax({
        url: 'alta_reverter.php',
        type: 'POST',
        data: {
            ids: ids
        },
        success: function(resp) {
            try {
                const j = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                if (j.ok) {
                    // Recarrega mantendo filtros/paginação atuais
                    loadContent(
                        'list_internacao_alta.php?pesquisa_nome=<?php print $pesquisa_nome ?>' +
                        '&pesquisa_pac=<?php print $pesquisa_pac ?>' +
                        '&pesqInternado=<?php print $pesqInternado ?>' +
                        '&limite=<?php print $limite ?>' +
                        '&ordenar=<?php print $ordenar ?>' +
                        '&pag=<?php print $_GET["pag"] ?? 1 ?>' +
                        '&bl=<?php print $_GET["bl"] ?? 0 ?>'
                    );
                    return;
                }
                alert(j.msg || 'Falha ao atualizar.');
            } catch (e) {
                alert('Erro inesperado.');
            }
        },
        error: function() {
            alert('Erro de comunicação.');
        }
    });
});
</script>