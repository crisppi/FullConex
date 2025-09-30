<?php

declare(strict_types=1);

require_once "templates/header.php";
require_once "models/pagination.php";

// ======================================================================
// DAOs — use sempre require_once e nomes consistentes
// ======================================================================
require_once "dao/internacaoDAO.php";
require_once "dao/pacienteDAO.php";
require_once "dao/capeanteDAO.php";
require_once "dao/HospitalDAO.php";
require_once "dao/patologiaDAO.php";

// ======================================================================
// Instâncias
// ======================================================================
$internacaoDAO = new internacaoDAO($conn, $BASE_URL);
$pacienteDAO   = new pacienteDAO($conn, $BASE_URL);
$capeanteDAO   = new capeanteDAO($conn, $BASE_URL);
$hospitalDAO   = new HospitalDAO($conn, $BASE_URL);
$patologiaDAO  = new patologiaDAO($conn, $BASE_URL);

// ======================================================================
// Leitura de filtros
// ======================================================================
$pesquisa_nome   = filter_input(INPUT_GET, 'pesquisa_nome',   FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$pesquisa_pac    = filter_input(INPUT_GET, 'pesquisa_pac',    FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$senha_fin       = filter_input(INPUT_GET, 'senha_fin',       FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$med_check       = filter_input(INPUT_GET, 'med_check',       FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$enf_check       = filter_input(INPUT_GET, 'enf_check',       FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$adm_check       = filter_input(INPUT_GET, 'adm_check',       FILTER_SANITIZE_SPECIAL_CHARS) ?: null;
$data_intern_int = filter_input(INPUT_GET, 'data_intern_int', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

$limite  = (int)(filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT) ?: 10);
$ordenar = (string)(filter_input(INPUT_GET, 'ordenar') ?: '');
$pag     = (int)($_GET['pag'] ?? 1);

// ======================================================================
// Controle de acesso por cargo
//  - Adm/Med/Enf/Hospital → filtra por tb_hospitalUser
//  - Diretor/Gestor → sem filtro (vê tudo)
// ======================================================================
$cargoSessao = (string)($_SESSION['cargo'] ?? '');
$userIdSess  = (int)($_SESSION['id_usuario'] ?? 0);
$rolesComFiltro = [
    'Med_auditor',
    'Med_Auditor',
    'med_auditor',
    'medico_auditor',
    'Enf_Auditor',
    'enf_auditor',
    'enfer_auditor',
    'Adm',
    'adm',
    'Administrador',
    'administrador',
    'Hospital',
    'hospital'
];
$userFiltro = (in_array($cargoSessao, $rolesComFiltro, true) && $userIdSess > 0) ? $userIdSess : null;

// ======================================================================
// WHERE (atenção aos aliases do Cap2: ac/pa/ho/ca)
// ======================================================================
$cond = [
    strlen((string)$pesquisa_nome)   ? 'ho.nome_hosp LIKE ' . $conn->quote("%$pesquisa_nome%") : null,
    strlen((string)$pesquisa_pac)    ? 'pa.nome_pac  LIKE ' . $conn->quote("%$pesquisa_pac%") : null,
    ($senha_fin === 's' || $senha_fin === 'n') ? 'ca.senha_finalizada = ' . $conn->quote($senha_fin) : null,
    ($med_check === 's' || $med_check === 'n') ? 'ca.med_check        = ' . $conn->quote($med_check) : null,
    ($enf_check === 's' || $enf_check === 'n') ? 'ca.enfer_check      = ' . $conn->quote($enf_check) : null,
    ($adm_check === 's' || $adm_check === 'n') ? 'ca.adm_check        = ' . $conn->quote($adm_check) : null,
    $data_intern_int ? 'ac.data_intern_int = ' . $conn->quote($data_intern_int) : null,
];
$cond = array_values(array_filter($cond));
$where = $cond ? implode(' AND ', $cond) : null;

// ======================================================================
// ORDER BY seguro
// ======================================================================
$order = match ($ordenar) {
    'id_internacao'   => 'ac.id_internacao',
    'nome_pac'        => 'pa.nome_pac',
    'nome_hosp'       => 'ho.nome_hosp',
    'data_intern_int' => 'ac.data_intern_int',
    default           => 'ac.data_intern_int DESC, ac.id_internacao DESC',
};

// ======================================================================
// Paginação
// ======================================================================
$totalReg   = $internacaoDAO->countInternacaoCap2($userFiltro, $where);
$pagination = new pagination($totalReg, $pag, $limite);
$obLimite   = $pagination->getLimit(); // string "offset,rows"
$pages      = $pagination->getPages();

// ======================================================================
// Consulta principal (SEM DUPLICAÇÃO)
//   - selectAllInternacaoCap2 retorna 1 linha por internação
//   - capeante vem do último id_capeante (subselect no DAO)
// ======================================================================
$query = $internacaoDAO->selectAllInternacaoCap2($userFiltro, $where, $order, $obLimite);

// ======================================================================
// HTML
// ======================================================================
?>
<div class="container form_container" style="margin-top:12px;">
    <div class="container">
        <h4 class="page-title" style="color:#3A3A3A">Capeantes - Auditoria</h4>
    </div>
    <hr>
    <div class="container" id="navbarToggleExternalContent">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <form class="visible" action="" id="select-internacao-form" method="GET">
            <div class="form-group row">
                <div class="form-group col-sm-3">
                    <input class="form-control form-control-sm" style="margin-top:7px;font-size:.8em;color:#878787"
                        type="text" name="pesquisa_nome" placeholder="Selecione o Hospital"
                        value="<?= htmlspecialchars((string)$pesquisa_nome) ?>">
                </div>
                <div class="form-group col-sm-3">
                    <input class="form-control form-control-sm" style="margin-top:7px;font-size:.8em;color:#878787"
                        type="text" name="pesquisa_pac" placeholder="Selecione o Paciente"
                        value="<?= htmlspecialchars((string)$pesquisa_pac) ?>">
                </div>
                <div class="col-sm-1" style="padding:2px !important">
                    <select class="form-control mb-3 form-control-sm"
                        style="margin-top:7px;font-size:.8em;color:#878787" id="limite" name="limite">
                        <option value="">Reg por pag</option>
                        <option value="5" <?= $limite == 5  ? 'selected' : '' ?>>Reg por pág = 5</option>
                        <option value="10" <?= $limite == 10 ? 'selected' : '' ?>>Reg por pág = 10</option>
                        <option value="20" <?= $limite == 20 ? 'selected' : '' ?>>Reg por pág = 20</option>
                        <option value="50" <?= $limite == 50 ? 'selected' : '' ?>>Reg por pág = 50</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em;color:#878787"
                        id="ordenar" name="ordenar">
                        <option value="">Classificar por</option>
                        <option value="id_internacao" <?= $ordenar === 'id_internacao'   ? 'selected' : '' ?>>Internação
                        </option>
                        <option value="nome_pac" <?= $ordenar === 'nome_pac'        ? 'selected' : '' ?>>Paciente
                        </option>
                        <option value="nome_hosp" <?= $ordenar === 'nome_hosp'       ? 'selected' : '' ?>>Hospital
                        </option>
                        <option value="data_intern_int" <?= $ordenar === 'data_intern_int' ? 'selected' : '' ?>>Data
                            Internação</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:10px" class="form-group row">
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em;color:#878787"
                        id="med_check" name="med_check">
                        <option value="s" <?= $med_check === 's' ? 'selected' : '' ?>>Sim</option>
                        <option value="n" <?= $med_check === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="" <?= ($med_check !== 's' && $med_check !== 'n') ? 'selected' : '' ?>>Med Check
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em;color:#878787"
                        id="enf_check" name="enf_check">
                        <option value=""></option>
                        <option value="s" <?= $enf_check === 's' ? 'selected' : '' ?>>Sim</option>
                        <option value="n" <?= $enf_check === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="" <?= ($enf_check !== 's' && $enf_check !== 'n') ? 'selected' : '' ?>>Enf Check
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em;color:#878787"
                        id="adm_check" name="adm_check">
                        <option value="s" <?= $adm_check === 's' ? 'selected' : '' ?>>Sim</option>
                        <option value="n" <?= $adm_check === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="" <?= ($adm_check !== 's' && $adm_check !== 'n') ? 'selected' : '' ?>>Adm Check
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <select class="form-control form-control-sm" style="margin-top:7px;font-size:.8em;color:#878787"
                        id="senha_fin" name="senha_fin">
                        <option value="" <?= ($senha_fin !== 's' && $senha_fin !== 'n') ? 'selected' : '' ?>>Senha
                            Finalizada</option>
                        <option value="s" <?= $senha_fin === 's' ? 'selected' : '' ?>>Sim</option>
                        <option value="n" <?= $senha_fin === 'n' ? 'selected' : '' ?>>Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-1" style="margin:0 0 20px 0">
                    <button type="submit" class="btn btn-primary"
                        style="background-color:#5e2363;width:42px;height:32px;margin-top:7px;border-color:#5e2363">
                        <span class="material-icons" style="margin-left:-3px;margin-top:-2px;">search</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TABELA -->
    <div class="container" id="table-content">
        <table class="table table-sm table-striped table-hover table-condensed">
            <thead>
                <tr>
                    <th style="width:4%">Reg</th>
                    <th style="width:6%">Conta No.</th>
                    <th style="width:23%">Hospital</th>
                    <th style="width:23%">Paciente</th>
                    <th style="width:12%">Data internação</th>
                    <th style="width:4%">Med</th>
                    <th style="width:4%">Enf</th>
                    <th style="width:4%">Adm</th>
                    <th style="width:4%">Final</th>
                    <th style="width:4%">Parcial</th>
                    <th style="width:13%">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query): foreach ($query as $intern): ?>
                <tr style="font-size:15px">
                    <td><b><?= (int)$intern['id_internacao'] ?></b></td>
                    <td><b><?= htmlspecialchars((string)($intern['id_capeante'] ?? '')) ?></b></td>
                    <td><b><?= htmlspecialchars((string)$intern['nome_hosp']) ?></b></td>
                    <td><?= htmlspecialchars((string)$intern['nome_pac']) ?></td>
                    <td><?= $intern['data_intern_int'] ? date('d/m/Y', strtotime($intern['data_intern_int'])) : '' ?>
                    </td>

                    <td><?php if (($intern['med_check'] ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle" style="font-size:1.1rem;font-weight:800;color:#004E56;"></span>
                        <?php endif; ?>
                    </td>

                    <td><?php if (($intern['enfer_check'] ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle" style="font-size:1.1rem;font-weight:800;color:#EA8037;"></span>
                        <?php endif; ?>
                    </td>

                    <td><?php if (($intern['adm_check'] ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle" style="font-size:1.1rem;font-weight:800;color:#194eff;"></span>
                        <?php endif; ?>
                    </td>

                    <td><?php if (($intern['senha_finalizada'] ?? 'n') === 's'): ?>
                        <span class="bi bi-briefcase" style="font-size:1.1rem;font-weight:800;color:#ff1937;"></span>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars((string)($intern['parcial_num'] ?? '')) ?></td>

                    <td class="action">
                        <a
                            href="<?= $BASE_URL ?>show_internacao.php?id_internacao=<?= (int)$intern['id_internacao'] ?>">
                            <i class="fas fa-eye check-icon" style="color:green;margin-right:10px"></i>
                        </a>

                        <?php if (($intern['encerrado_cap'] ?? 'n') !== 's'): ?>
                        <?php if (($intern['em_auditoria_cap'] ?? 'n') === 's' && !empty($intern['id_capeante'])): ?>
                        <a href="<?= $BASE_URL ?>cad_capeante_audit.php?id_capeante=<?= (int)$intern['id_capeante'] ?>">
                            <i class="bi bi-file-text"
                                style="color:#ff3719;font-size:10px;font-weight:bold;margin:0 5px">
                                Em análise</i>
                        </a>
                        <?php else: ?>
                        <a
                            href="<?= $BASE_URL ?>cad_capeante_audit.php?id_internacao=<?= (int)$intern['id_internacao'] ?>">
                            <i class="bi bi-file-text"
                                style="color:#194eff;font-size:10px;font-weight:bold;margin:0 5px">
                                Iniciar</i>
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach;
                else: ?>
                <tr>
                    <td colspan="11" style="font-size:15px">Não foram encontrados registros</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- paginação simples -->
        <?php if ($totalReg > $limite && !empty($pages)): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php foreach ($pages as $p):
                        $isActive = (($p['pg'] ?? null) == $pag);
                        $pgNum    = (int)($p['pg'] ?? 1);
                    ?>
                <li class="page-item <?= $isActive ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query([
                                                            'pesquisa_nome'   => $pesquisa_nome,
                                                            'pesquisa_pac'    => $pesquisa_pac,
                                                            'senha_fin'       => $senha_fin,
                                                            'med_check'       => $med_check,
                                                            'enf_check'       => $enf_check,
                                                            'adm_check'       => $adm_check,
                                                            'data_intern_int' => $data_intern_int,
                                                            'ordenar'         => $ordenar,
                                                            'limite'          => $limite,
                                                            'pag'             => $pgNum,
                                                        ]) ?>"><?= $pgNum ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <div>
            <a class="btn btn-success styled"
                style="background-color:#35bae1;font-family:var(--bs-font-sans-serif);box-shadow:0 10px 15px -3px rgba(0,0,0,.1);border:none"
                href="cad_capeante.php">Novo Capeante</a>
        </div>
    </div>
</div>

<!-- Ajax do filtro (opcional: atualiza só a tabela) -->
<script>
$(document).ready(function() {
    $('#select-internacao-form').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: $(this).attr('action') || window.location.pathname,
            type: $(this).attr('method') || 'GET',
            data: formData,
            success: function(response) {
                const temp = document.createElement('div');
                temp.innerHTML = response;
                const tableContent = temp.querySelector('#table-content');
                if (tableContent) $('#table-content').html(tableContent.innerHTML);
            },
            error: function() {
                alert('Ocorreu um erro ao enviar o formulário.');
            }
        });
    });
});
</script>

<script src="./js/input-estilo.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js" rel="preload" as="script">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
</script>