    <?php
    ob_start(); // Output Buffering Start

    require_once("templates/header.php");

    require_once("models/message.php");

    include_once("models/internacao.php");
    include_once("dao/internacaoDao.php");

    include_once("models/patologia.php");
    include_once("dao/patologiaDao.php");

    include_once("models/paciente.php");
    include_once("dao/pacienteDao.php");

    include_once("models/capeante.php");
    include_once("dao/capeanteDao.php");

    include_once("models/hospital.php");
    include_once("dao/hospitalDao.php");

    include_once("models/usuario.php");
    include_once("dao/usuarioDao.php");

    include_once("models/pagination.php");

    if (!function_exists('formatDateBrSafe')) {
        function formatDateBrSafe(?string $date): string
        {
            if (!$date || $date === '0000-00-00') {
                return '—';
            }
            $ts = strtotime($date);
            return $ts ? date('d/m/Y', $ts) : '—';
        }
    }

    $rahListContext = $RAH_LIST_CONTEXT ?? 'auditar';
    $rahFormAction  = $RAH_FORM_ACTION ?? 'list_internacao_cap_rah.php';
    $isFinalContext = ($rahListContext === 'finalizadas');
    $isSenhasContext = ($rahListContext === 'senhas');
    if ($isFinalContext || $isSenhasContext) {
        $tableColspan = 10;
    } else {
        $tableColspan = 14;
    }

    // =====================================================================
    // Sessão / Papel / Diretor
    // =====================================================================
    $cargoSessao = $_SESSION['cargo'] ?? '';
    $nivelSessao = $_SESSION['nivel'] ?? null;
    $userId      = $_SESSION['id_usuario'] ?? null;

    // Diretor: cargo contém "diretor" (case-insensitive) OU nivel == 1
    $isDiretor = (stripos((string)$cargoSessao, 'diretor') !== false) || ((string)$nivelSessao === '1');

    // =====================================================================
    // Inicialização
    // =====================================================================
    $data_intern_int   = null;
    $order             = null;
    $obLimite          = null;
    $where             = null;
    $inicio            = $inicio ?? 0;

    // =====================================================================
    // DAOs
    // =====================================================================
    $internacao       = new internacaoDAO($conn, $BASE_URL);
    $capeante_geral   = new capeanteDAO($conn, $BASE_URL);
    $pacienteDao      = new pacienteDAO($conn, $BASE_URL);
    $usuarioDao       = new userDAO($conn, $BASE_URL);
    $hospital_geral   = new HospitalDAO($conn, $BASE_URL);
    $patologiaDao     = new patologiaDAO($conn, $BASE_URL);

    // =====================================================================
    // GET (filtros)
    // =====================================================================
    $limite_pag       = filter_input(INPUT_GET, 'limite_pag') ? filter_input(INPUT_GET, 'limite_pag') : 10;
    $limite           = filter_input(INPUT_GET, 'limite')     ? filter_input(INPUT_GET, 'limite')     : 10;
    $ordenar          = filter_input(INPUT_GET, 'ordenar')    ? filter_input(INPUT_GET, 'ordenar')    : 'id_capeante_desc';

$pesquisa_nome       = filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS);
$pesquisa_pac        = filter_input(INPUT_GET, 'pesquisa_pac',  FILTER_SANITIZE_SPECIAL_CHARS);
$pesquisa_matricula  = filter_input(INPUT_GET, 'pesquisa_matricula', FILTER_SANITIZE_SPECIAL_CHARS);
$encerrado_cap = filter_input(INPUT_GET, 'encerrado_cap'); // '' | 's' | 'n'
if (($encerrado_cap === null || $encerrado_cap === '') && isset($FORCE_ENCERRADO_CAP_RAH)) {
    $encerrado_cap = $FORCE_ENCERRADO_CAP_RAH;
}

$senha_fin           = filter_input(INPUT_GET, 'senha_fin') ?: NULL;
if (($senha_fin === null || $senha_fin === '') && isset($FORCE_SENHA_FIN_RAH)) {
    $senha_fin = $FORCE_SENHA_FIN_RAH;
}
$conta_parada        = filter_input(INPUT_GET, 'conta_parada') ?: NULL;
$idcapeante          = filter_input(INPUT_GET, 'idcapeante') ?: NULL;
    $senha_int           = filter_input(INPUT_GET, 'senha_int', FILTER_SANITIZE_SPECIAL_CHARS) ?: NULL;
    $lote                = filter_input(INPUT_GET, 'lote', FILTER_SANITIZE_SPECIAL_CHARS) ?: NULL;
    $data_intern_int     = filter_input(INPUT_GET, 'data_intern_int') ?: NULL;
    $data_intern_int_max = filter_input(INPUT_GET, 'data_intern_int_max') ?: NULL;
    $id_hosp             = filter_input(INPUT_GET, 'id_hosp', FILTER_SANITIZE_NUMBER_INT) ?: null;

    if (empty($data_intern_int_max)) {
        $data_intern_int_max = date('Y-m-d'); // compatível com SQL
    }

    // =====================================================================
    // Hospitais visíveis (para SELECT) – dedup por id_hospital
    // =====================================================================
    $hospitalsRaw = $hospital_geral->findGeral();

    $hospitalsDedup = [];
    foreach ($hospitalsRaw as $h) {
        $hid  = $h['id_hospital']     ?? $h['id'] ?? null;
        $hnom = $h['nome_hosp']       ?? $h['nome'] ?? '';
        $fk   = $h['fk_usuario_hosp'] ?? null;

        if (!$isDiretor) {
            if ($fk && $userId && (string)$fk !== (string)$userId) continue; // apenas hospitais do profissional
        }
        if ($hid && !isset($hospitalsDedup[$hid])) {
            $hospitalsDedup[$hid] = ['id_hospital' => $hid, 'nome_hosp' => $hnom];
        }
    }
    $hospitals = array_values($hospitalsDedup);

    // auto-seleciona hospital único do profissional
    if (!$isDiretor && empty($id_hosp) && count($hospitals) === 1) {
        $id_hosp = (string)$hospitals[0]['id_hospital'];
    }

    // =====================================================================
    // WHERE (filtro principal) – a lista é de CAPEANTE, mas filtramos por campos relacionados
    // =====================================================================
    $condicoes = [
        // Hospital escolhido
        strlen((string)$id_hosp) ? 'ho.id_hospital = ' . (int)$id_hosp : NULL,

        // Filtros usuais
        strlen($pesquisa_nome) ? 'ho.nome_hosp LIKE "%' . $pesquisa_nome . '%"' : NULL,
        strlen($pesquisa_pac)  ? 'pa.nome_pac  LIKE "%' . $pesquisa_pac  . '%"' : NULL,
        strlen($pesquisa_matricula) ? 'pa.matricula_pac LIKE "%' . $pesquisa_matricula . '%"' : NULL,
        strlen($lote)          ? 'ca.lote_cap = "' . $lote . '"'                 : NULL,
        strlen($idcapeante)    ? 'ca.id_capeante LIKE "%' . $idcapeante . '%"'   : NULL,
        strlen($senha_fin)     ? 'senha_finalizada = "' . $senha_fin . '"'       : NULL,
        ($conta_parada === 's' || $conta_parada === 'n') ? 'ca.conta_parada_cap = "' . $conta_parada . '"' : NULL,
        ($encerrado_cap === 's' || $encerrado_cap === 'n') ? 'ca.encerrado_cap = "' . $encerrado_cap . '"' : NULL,
        strlen($senha_int)     ? 'senha_int LIKE "%' . $senha_int . '%"'         : NULL,
        strlen($data_intern_int) ? 'data_intern_int BETWEEN "' . $data_intern_int . '" AND "' . $data_intern_int_max . '"' : NULL,

        (!$isDiretor && strlen((string)$userId)) ? 'ho.fk_usuario_hosp = "' . $userId . '"' : NULL
    ];

    $condicoes = array_filter($condicoes);
    $where = implode(' AND ', $condicoes);

    // Parâmetros base para montar as URLs de paginação mantendo os filtros
    $rahPaginationBaseParams = [
        'id_hosp'           => $id_hosp,
        'pesquisa_nome'     => $pesquisa_nome,
        'pesquisa_pac'      => $pesquisa_pac,
        'pesquisa_matricula'=> $pesquisa_matricula,
        'senha_fin'         => $senha_fin,
        'encerrado_cap'     => $encerrado_cap,
        'conta_parada'      => $conta_parada,
        'senha_int'         => $senha_int,
        'lote'              => $lote,
        'idcapeante'        => $idcapeante,
        'data_intern_int'   => $data_intern_int,
        'data_intern_int_max' => $data_intern_int_max,
        'limite'            => $limite,
        'ordenar'           => $ordenar,
        'limite_pag'        => $limite_pag,
    ];

    if (!function_exists('buildRahPaginationUrl')) {
        function buildRahPaginationUrl(string $action, array $baseParams, array $override = []): string
        {
            $params = array_merge($baseParams, $override);
            $params = array_filter($params, function ($value) {
                return $value !== null && $value !== '';
            });
            $query = http_build_query($params);
            global $BASE_URL;
            $actionPath = ltrim($action, '/');
            $baseUrl    = rtrim($BASE_URL, '/') . '/' . $actionPath;

            return $query ? $baseUrl . '?' . $query : $baseUrl;
        }
    }

    // =====================================================================
    // Consulta TOTAL (bruto) + TOTAL DEDUP por id_capeante
    // =====================================================================
    $QtdTotalIntDao = new internacaoDAO($conn, $BASE_URL);
    $qtdArray       = $capeante_geral->selectAllCapeanteRah($where, $order);

    // TOTAL deduplicado por id_capeante
    $__ids_total = [];
    foreach ((array)$qtdArray as $row) {
        if (isset($row['id_capeante'])) {
            $__ids_total[(string)$row['id_capeante']] = true;
        } else {
            // Se vier linha sem id_capeante, conta também para não “perder” itens
            $__ids_total['__sem_id__' . spl_object_id((object)$row)] = true;
        }
    }
    $qtdIntItens = count($__ids_total);

    // =====================================================================
    // Paginação + consulta da página
    // =====================================================================
    $totalcasos     = ceil(max($qtdIntItens, 1) / max((int)$limite, 1));
    $pesqInternado  = null;

    $mapOrder = [
        'id_internacao' => 'ac.id_internacao',
        'id_capeante_desc' => 'ca.id_capeante DESC',
        'id_capeante' => 'ca.id_capeante',
        'senha_int' => 'ac.senha_int',
        'nome_pac' => 'pa.nome_pac',
        'nome_hosp' => 'ho.nome_hosp',
        'data_intern_int' => 'ac.data_intern_int',
    ];
    $order = $mapOrder[$ordenar] ?? 'ca.id_capeante DESC';
    $obPagination   = new pagination($qtdIntItens, $_GET['pag'] ?? 1, $limite ?? 10);
    $obLimite       = $obPagination->getLimit();

    $query          = $internacao->selectAllInternacaoCapList($where, $order, $obLimite);

    // =====================================================================
    // DEDUP da página por id_capeante (para não repetir linhas no render)
    // =====================================================================
    $__seen_cape   = [];
    $__render_rows = [];
    foreach ((array)$query as $row) {
        if ($encerrado_cap === 's' && (($row['encerrado_cap'] ?? 'n') !== 's')) {
            continue;
        }
        $idc = $row['id_capeante'] ?? null;
        if ($idc === null) {
            $__render_rows[] = $row; // mantém, embora não devesse acontecer
            continue;
        }
        if (!isset($__seen_cape[$idc])) {
            $__seen_cape[$idc] = true;
            $__render_rows[]   = $row;
        }
    }
    $qtdIntItens_pagina = count($__render_rows);
    $__render_count     = $qtdIntItens_pagina;

    // =====================================================================
    // Blocos e páginas (para navegação)
    // =====================================================================
    $havePages = false;
    if ($qtdIntItens > $limite) {
        $paginas     = $obPagination->getPages();
        $total_pages = count($paginas);
        $havePages   = $total_pages > 1;

        if ($havePages) {
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
    }
    ?>
    <!-- FORMULARIO DE PESQUISAS -->
    <div class="container-fluid form_container" id='main-container' style="margin-top:-5px;">

        <script src="https://code.jquery.com/jquery-3.7.0.min.js"
            integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
        <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 10px;">
            <h4 class="page-title" style="color: #3A3A3A;">
                <?php
                if ($rahListContext === 'senhas') {
                    echo 'Senhas Finalizadas';
                } elseif ($isFinalContext) {
                    echo 'Contas Finalizadas';
                } else {
                    echo 'Contas para Auditar';
                }
                ?>
            </h4>
        </div>

        <style>
        .filter-date-col {
            flex: 0 0 150px;
            width: 150px;
            max-width: 150px;
        }
        @media (max-width: 991.98px) {
            .filter-date-col {
                flex: 1 0 100%;
                width: 100%;
                max-width: 100%;
            }
        }
        </style>
        <div class="complete-table">

            <div id="navbarToggleExternalContent" class="table-filters">
                <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
                <form id="select-internacao-form" method="GET"
                    action="<?= htmlspecialchars($rahFormAction, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group row">
                        <!-- SELECT de Hospital (sem duplicidade) -->
                        <div class="form-group col-sm-3" style="padding:2px !important;padding-left:16px !important;">
                            <select class="form-control form-control-sm"
                                style="margin-top:7px; font-size:.8em; color:#878787" name="id_hosp" id="id_hosp">
                                <option value=""><?= $isDiretor ? 'Todos os Hospitais' : 'Selecione o Hospital' ?>
                                </option>
                                <?php foreach ($hospitals as $h): ?>
                                <option value="<?= (int)$h['id_hospital'] ?>"
                                    <?= ((string)$id_hosp === (string)$h['id_hospital']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$h['nome_hosp']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-sm-2" style="padding:2px !important">
                            <input class="form-control form-control-sm"
                                style="margin-top:7px; font-size:.8em; color:#878787" type="text" name="pesquisa_pac"
                                placeholder="Paciente" value="<?= htmlspecialchars((string)$pesquisa_pac) ?>">
                        </div>

                        <div class="form-group col-sm-2" style="padding:2px !important">
                            <input class="form-control form-control-sm"
                                style="margin-top:7px; font-size:.8em; color:#878787" type="text" name="pesquisa_matricula"
                                placeholder="Matrícula" value="<?= htmlspecialchars((string)$pesquisa_matricula) ?>">
                        </div>

                        <div class="form-group col-sm-2" style="padding:2px !important">
                            <input class="form-control form-control-sm"
                                style="margin-top:7px; font-size:.8em; color:#878787" type="text" name="senha_int"
                                placeholder="Senha" value="<?= htmlspecialchars((string)$senha_int) ?>">
                        </div>

                        <div class="form-group col-sm-1" style="padding:2px !important">
                            <input class="form-control form-control-sm"
                                style="margin-top:7px; font-size:.8em; color:#878787" type="text" name="lote"
                                placeholder="Lote" value="<?= htmlspecialchars((string)$lote) ?>">
                        </div>

                        <div class="form-group col-sm-1" style="padding:2px !important">
                            <input class="form-control form-control-sm"
                                style="margin-top:7px; font-size:.8em; color:#878787" type="text" name="idcapeante"
                                placeholder="Capeante" value="<?= htmlspecialchars((string)$idcapeante) ?>">
                        </div>

                        <div class="col-sm-1" style="padding:2px !important">
                            <select class="form-control mb-3 form-control-sm"
                                style="margin-top:7px;font-size:.8em; color:#878787" id="limite" name="limite">
                                <option value="">Reg/pág</option>
                                <option value="5" <?= $limite == '5'  ? 'selected' : null ?>>5</option>
                                <option value="10" <?= $limite == '10' ? 'selected' : null ?>>10</option>
                                <option value="20" <?= $limite == '20' ? 'selected' : null ?>>20</option>
                                <option value="50" <?= $limite == '50' ? 'selected' : null ?>>50</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row" style="margin-top:-20px">
                        <div class="form-group col-sm-1" style="padding:2px !important;padding-left:16px !important;">
                            <select class="form-control form-control-sm"
                                style="margin-top:7px;font-size:.8em; color:#878787" id="ordenar" name="ordenar">
                                <option value="">Classificar por</option>
                                <option value="id_capeante_desc"
                                    <?= $ordenar == 'id_capeante_desc' ? 'selected' : '' ?>>
                                    No.capeante (desc)
                                </option>
                                <option value="id_internacao" <?= $ordenar == 'id_internacao'  ? 'selected' : '' ?>>
                                    Internação
                                </option>
                                <option value="id_capeante" <?= $ordenar == 'id_capeante'    ? 'selected' : '' ?>>
                                    No.capeante (asc)
                                </option>
                                <option value="senha_int" <?= $ordenar == 'senha_int'       ? 'selected' : '' ?>>Senha
                                </option>
                                <option value="nome_pac" <?= $ordenar == 'nome_pac'        ? 'selected' : '' ?>>Paciente
                                </option>
                                <option value="nome_hosp" <?= $ordenar == 'nome_hosp'       ? 'selected' : '' ?>>
                                    Hospital
                                </option>
                                <option value="data_intern_int" <?= $ordenar == 'data_intern_int' ? 'selected' : '' ?>>
                                    Data
                                    Internação</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-2" style="padding:2px !important">
                        <select class="form-control form-control-sm"
                            style="margin-top:7px;font-size:.8em; color:#878787" id="senha_fin" name="senha_fin">
                            <option value="">Senha finalizada</option>
                            <option value="s" <?= $senha_fin === 's' ? 'selected' : '' ?>>Sim</option>
                            <option value="n" <?= $senha_fin === 'n' ? 'selected' : '' ?>>Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2" style="padding:2px !important">
                        <select class="form-control form-control-sm"
                            style="margin-top:7px;font-size:.8em; color:#878787" id="encerrado_cap"
                            name="encerrado_cap">
                            <option value="" <?= ($encerrado_cap === null || $encerrado_cap === '') ? 'selected' : '' ?>>Encerrado (Todos)</option>
                            <option value="s" <?= $encerrado_cap === 's' ? 'selected' : '' ?>>Sim</option>
                            <option value="n" <?= $encerrado_cap === 'n' ? 'selected' : '' ?>>Não</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-2" style="padding:2px !important">
                        <select class="form-control form-control-sm"
                            style="margin-top:7px;font-size:.8em; color:#878787" id="conta_parada"
                            name="conta_parada">
                            <option value="" <?= ($conta_parada === null || $conta_parada === '') ? 'selected' : '' ?>>Conta parada (Todas)</option>
                            <option value="s" <?= $conta_parada === 's' ? 'selected' : '' ?>>Paradas</option>
                            <option value="n" <?= $conta_parada === 'n' ? 'selected' : '' ?>>Ativas</option>
                        </select>
                    </div>

                        <div class="form-group col-sm-2 filter-date-col" style="padding:2px !important">
                            <input class="form-control form-control-sm" type="date"
                                style="margin-top:7px;font-size:.8em; color:#878787" name="data_intern_int"
                                placeholder="Data Internação Min"
                                value="<?= htmlspecialchars((string)$data_intern_int) ?>">
                        </div>
                        <div class="form-group col-sm-2 filter-date-col" style="padding:2px !important">
                            <input class="form-control form-control-sm" type="date"
                                style="margin-top:7px;font-size:.8em; color:#878787" name="data_intern_int_max"
                                placeholder="Data Internação Max"
                                value="<?= htmlspecialchars((string)$data_intern_int_max) ?>">
                        </div>
                        <div class="form-group col-sm-1" style="padding:2px !important">
                            <button type="submit" class="btn btn-primary"
                                style="background-color:#5e2363;width:42px;height:32px;margin-top:7px;border-color:#5e2363">
                                <span class="material-icons" style="margin-left:-3px;margin-top:-2px;">search</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div>
                <div id="table-content">
                    <?php if ($isFinalContext || $isSenhasContext): ?>
                    <table class="table table-sm table-striped  table-hover table-condensed">
                        <thead>
                            <tr>
                                <th scope="col" style="width:4%">Reg Int</th>
                                <th scope="col" style="width:6%">Capeante</th>
                                <th scope="col" style="width:12%">Hospital</th>
                                <th scope="col" style="width:16%">Paciente</th>
                                <th scope="col" style="width:10%">Senha</th>
                                <th scope="col" style="width:8%">Data internação</th>
                                <th scope="col" style="width:8%">Data fechamento</th>
                                <th scope="col" style="width:8%">Data digitação</th>
                                <th scope="col" style="width:6%;">Cap Encer</th>
                                <th scope="col" style="width:15%;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($__render_rows as $intern): extract($intern); ?>
                            <tr style="font-size:13px">
                                <td scope="row" class="col-id"><b><?= $intern["id_internacao"]; ?></b></td>
                                <td scope="row" class="col-id">
                                    <div class="small mb-1"><strong>#<?= $intern["id_capeante"]; ?></strong></div>
                                    <?php if (isset($intern["parcial_num"])): ?>
                                        <span class="badge bg-light text-muted">Parcial <?= htmlspecialchars((string)$intern["parcial_num"]) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td scope="row" class="nome-coluna-table"><b><?= $intern["nome_hosp"] ?></b></td>
                                <td scope="row"><?= $intern["nome_pac"] ?></td>
                                <td scope="row"><?= $intern["senha_int"] ?></td>
                                <td scope="row"><?= date('d/m/Y', strtotime($intern["data_intern_int"])) ?></td>
                                <td scope="row"><?= formatDateBrSafe($intern["data_fech_capeante"] ?? null) ?></td>
                                <td scope="row"><?= formatDateBrSafe($intern["data_digit_capeante"] ?? null) ?></td>
                                <td scope="row">
                                        <?php if (($intern["encerrado_cap"] ?? 'n') === "s") { ?>
                                        <span class="legenda-aberto" style="cursor:default;"><span class="bi bi-briefcase"
                                                style="font-size:1.1rem;color:green;font-weight:800;"></span></span>
                                        <?php } ?>
                                </td>
                                <td class="action">
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <a class="btn btn-outline-primary btn-sm" href="#"
                                            onclick="edit('<?= $BASE_URL ?>cad_capeante_rah.php?id_capeante=<?= $intern['id_capeante'] ?>')">
                                            Editar RAH
                                        </a>
                                        <a class="btn btn-outline-secondary btn-sm" target="_blank"
                                            href="<?= $BASE_URL ?>export_capeante_rah_pdf.php?id_capeante=<?= $intern['id_capeante'] ?>&download=0">
                                            Ver RAH PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if ($__render_count === 0): ?>
                            <tr>
                                <td colspan="<?= $tableColspan ?>" scope="row" class="col-id" style='font-size:15px'>
                                    Não foram encontrados registros
                                </td>
                            </tr>
                            <?php endif ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <table class="table table-sm table-striped  table-hover table-condensed">
                        <thead>
                            <tr>
                                <th scope="col" style="width:4%">Reg Int</th>
                                <th scope="col" style="width:6%">Capeante</th>
                                <th scope="col" style="width:12%">Hospital</th>
                                <th scope="col" style="width:16%">Paciente</th>
                                <th scope="col" style="width:10%">Senha</th>
                                <th scope="col" style="width:8%">Data internação</th>
                                <th scope="col" style="width:8%;">Data fechamento</th>
                                <th scope="col" style="width:8%;">Data digitação</th>
                                <th scope="col" style="width:6%;">Cap Encer</th>
                                <?php if (!$isSenhasContext): ?>
                                <th scope="col" style="width:6%;">Parcial</th>
                                <?php endif; ?>
                                <th scope="col" style="width:13%">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($__render_rows as $intern): extract($intern); ?>
                            <tr style="font-size:13px">
                                <td scope="row" class="col-id"><b><?= $intern["id_internacao"]; ?></b></td>
                                <td scope="row" class="col-id">
                                    <div class="small mb-1"><strong>#<?= $intern["id_capeante"]; ?></strong></div>
                                    <?php if (isset($intern["parcial_num"])): ?>
                                        <span class="badge bg-light text-muted">Parcial <?= htmlspecialchars((string)$intern["parcial_num"]) ?></span>
                                    <?php endif; ?>
                                </td>
                            <td scope="row" class="nome-coluna-table"><b><?= $intern["nome_hosp"] ?></b></td>
                            <td scope="row"><?= $intern["nome_pac"] ?></td>
                            <td scope="row"><?= $intern["senha_int"] ?></td>
                            <td scope="row"><?= date('d/m/Y', strtotime($intern["data_intern_int"])) ?></td>
                            <td scope="row"><?= formatDateBrSafe($intern["data_fech_capeante"] ?? null) ?></td>
                            <td scope="row"><?= formatDateBrSafe($intern["data_digit_capeante"] ?? null) ?></td>
                                <td scope="row">
                                    <?php if (($intern["encerrado_cap"] ?? 'n') === "s") { ?>
                                    <a class="legenda-aberto"><span class="bi bi-briefcase"
                                            style="font-size:1.1rem;color:green;font-weight:800;"></span></a>
                                    <?php } ?>
                                </td>
                                <?php if (!$isSenhasContext): ?>
                                    <td scope="row"><?= $intern["parcial_num"]; ?></td>
                                <?php endif; ?>

                                <td class="action">
                                    <?php if ($isSenhasContext): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a class="btn btn-outline-primary btn-sm"
                                                href="#"
                                                onclick="edit('<?= $BASE_URL ?>cad_capeante_rah.php?id_capeante=<?= $intern['id_capeante'] ?>')">
                                                Editar RAH
                                            </a>
                                            <a class="btn btn-outline-secondary btn-sm" target="_blank"
                                                href="<?= $BASE_URL ?>export_capeante_rah_pdf.php?id_capeante=<?= $intern['id_capeante'] ?>&download=0">
                                                Ver RAH PDF
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <?php if (($intern['encerrado_cap'] ?? 'n') !== "s"): ?>
                                            <?php if (($intern['em_auditoria_cap'] ?? 'n') === "s"): ?>
                                            <a class="legenda-em-auditoria" href="<?= $BASE_URL ?>cad_capeante_rah.php?id_capeante=<?= $intern['id_capeante'] ?>">
                                                <i class="bi bi-file-text"
                                                    style="color:#db5a0f;font-size:1.1em;margin:0 5px"></i>
                                                <span style="color:#db5a0f;">Analisar</span>
                                            </a>
                                            <?php else: ?>
                                            <a class="legenda-iniciar" href="<?= $BASE_URL ?>cad_capeante_rah.php?id_capeante=<?= $intern['id_capeante'] ?>">
                                                <i class="bi bi-file-text"
                                                    style="color:rgb(25,78,255);font-size:1.1em;font-weight:bold;margin:0 5px"></i>
                                                <span>Rah -</span>
                                            </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                        <span class="legenda-encerrado" style="cursor:not-allowed;">
                                            <i class="bi"
                                                style="color:black;text-decoration:none;font-size:1.1em;font-weight:bold;margin:0 5px">
                                                Encerrado</i>
                                        </span>
                                        <?php endif; ?>

                                        <a class="legenda-parcial"
                                            href="<?= $BASE_URL ?>cad_capeante_rah.php?id_internacao=<?= $intern["id_internacao"] ?>&type=create&nova_parcial=1">
                                            <i class="legenda-parcial bi bi-file-text"
                                                style="color:green;text-decoration:none;font-size:10px;font-weight:bold;margin:0 5px">
                                                Parcial</i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if ($__render_count === 0): ?>
                            <tr>
                                <td colspan="<?= $tableColspan ?>" scope="row" class="col-id" style='font-size:15px'>
                                    Não foram encontrados registros
                                </td>
                            </tr>
                            <?php endif ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <div style="display:flex;margin:10px 25px 25px 25px;align-items:center;gap:16px;">
                        <div class="pagination" style="margin:10px auto;">
                            <?php if (!empty($havePages) && $havePages): ?>
                            <ul class="pagination">
                                <?php
                                    $blocoAtual   = isset($_GET['bl']) ? (int)$_GET['bl'] : 0;
                                    $paginaAtual  = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
                                    ?>
                                <?php if ($current_block > $first_block): ?>
                                <?php
                                        $firstPageUrl = buildRahPaginationUrl($rahFormAction, $rahPaginationBaseParams, [
                                            'pag' => 1,
                                            'bl'  => 0,
                                        ]);
                                        ?>
                                <li class="page-item">
                                    <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($firstPageUrl) ?>"
                                        onclick="return paginateRah('<?= htmlspecialchars($firstPageUrl, ENT_QUOTES) ?>');">
                                        <i class="fa-solid fa-angles-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if ($current_block <= $last_block && $last_block > 1 && $current_block != 1): ?>
                                <?php
                                        $prevPageUrl = buildRahPaginationUrl($rahFormAction, $rahPaginationBaseParams, [
                                            'pag' => max(1, $paginaAtual - 1),
                                            'bl'  => max(0, $blocoAtual - 5),
                                        ]);
                                        ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= htmlspecialchars($prevPageUrl) ?>"
                                        onclick="return paginateRah('<?= htmlspecialchars($prevPageUrl, ENT_QUOTES) ?>');">
                                        <i class="fa-solid fa-angle-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = $first_page_in_block; $i <= $last_page_in_block; $i++): ?>
                                <?php
                                        $pageUrl = buildRahPaginationUrl($rahFormAction, $rahPaginationBaseParams, [
                                            'pag' => $i,
                                            'bl'  => $blocoAtual,
                                        ]);
                                        ?>
                                <li class="page-item <?= (($_GET['pag'] ?? 1) == $i) ? "active" : "" ?>">
                                    <a class="page-link" href="<?= htmlspecialchars($pageUrl) ?>"
                                        onclick="return paginateRah('<?= htmlspecialchars($pageUrl, ENT_QUOTES) ?>');">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($current_block < $last_block): ?>
                                <?php
                                        $nextPageUrl = buildRahPaginationUrl($rahFormAction, $rahPaginationBaseParams, [
                                            'pag' => min($total_pages, $paginaAtual + 1),
                                            'bl'  => $blocoAtual + 5,
                                        ]);
                                        ?>
                                <li class="page-item">
                                    <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($nextPageUrl) ?>"
                                        onclick="return paginateRah('<?= htmlspecialchars($nextPageUrl, ENT_QUOTES) ?>');">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if ($current_block < $last_block): ?>
                                <?php
                                        $lastPageUrl = buildRahPaginationUrl($rahFormAction, $rahPaginationBaseParams, [
                                            'pag' => $total_pages,
                                            'bl'  => ($last_block - 1) * 5,
                                        ]);
                                        ?>
                                <li class="page-item">
                                    <a class="page-link" id="blocoNovo" href="<?= htmlspecialchars($lastPageUrl) ?>"
                                        onclick="return paginateRah('<?= htmlspecialchars($lastPageUrl, ENT_QUOTES) ?>');">
                                        <i class="fa-solid fa-angles-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                            <?php endif; ?>
                        </div>

                        <div class="table-counter">
                            <p
                                style="font-size:1em;font-weight:600;font-family:var(--bs-font-sans-serif);text-align:right;margin:0;">
                                <?php echo "Total: " . (int)$__render_count ?>
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

<script>
// AJAX para submit do formulário de pesquisa
$(document).ready(function() {
    $('#select-internacao-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method') || 'GET',
            data: formData,
            success: function(response) {
                var tempElement = document.createElement('div');
                tempElement.innerHTML = response;
                var tableContent = tempElement.querySelector('#table-content');
                if (tableContent) {
                    $('#table-content').html(tableContent.innerHTML);
                } else {
                    $('#table-content').html(response);
                }
            },
            error: function() {
                alert('Ocorreu um erro ao enviar o formulário.');
            }
        });
    });
});

// Carregamento inicial
$(document).ready(function() {
    var initialRahUrl = '<?= htmlspecialchars(buildRahPaginationUrl(
        $rahFormAction,
        $rahPaginationBaseParams,
        [
            'pag' => $_GET['pag'] ?? 1,
            'bl'  => $_GET['bl'] ?? 0,
        ]
    ), ENT_QUOTES) ?>';
    if (typeof loadContent === 'function') {
        loadContent(initialRahUrl);
    }
});
    </script>

    <script>
if (typeof window.paginateRah !== 'function') {
    window.paginateRah = function(url) {
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js";
    </script>
    <script src="./js/ajaxNav.js"></script>
    <script src="./scripts/cadastro/general.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
