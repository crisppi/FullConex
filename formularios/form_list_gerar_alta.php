<?php
ob_start();

require_once("templates/header.php");
require_once("models/message.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");
include_once("models/paciente.php");
include_once("dao/pacienteDao.php");
include_once("models/hospital.php");
include_once("dao/hospitalDao.php");
include_once("models/pagination.php");
include_once("array_dados.php");

$internacaoDao = new internacaoDAO($conn, $BASE_URL);
$paginationObj = new pagination(0, 1, 10);

$pesquisa_hosp = trim((string)(filter_input(INPUT_GET, 'pesquisa_hosp', FILTER_SANITIZE_SPECIAL_CHARS) ?: ''));
$pesquisa_pac  = trim((string)(filter_input(INPUT_GET, 'pesquisa_pac', FILTER_SANITIZE_SPECIAL_CHARS) ?: ''));
$pesquisa_matricula = trim((string)(filter_input(INPUT_GET, 'pesquisa_matricula', FILTER_SANITIZE_SPECIAL_CHARS) ?: ''));
$limite        = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT) ?: 10;
$ordenar       = filter_input(INPUT_GET, 'ordenar', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'data_intern_int DESC';
$pagAtual      = filter_input(INPUT_GET, 'pag', FILTER_VALIDATE_INT) ?: 1;

$condicoes = ['ac.internado_int = "s"'];
if ($pesquisa_hosp !== '') {
    $condicoes[] = 'ho.nome_hosp LIKE "%' . $pesquisa_hosp . '%"';
}
if ($pesquisa_pac !== '') {
    $condicoes[] = 'pa.nome_pac LIKE "%' . $pesquisa_pac . '%"';
}
if ($pesquisa_matricula !== '') {
    $condicoes[] = 'pa.matricula_pac LIKE "%' . $pesquisa_matricula . '%"';
}
$where = implode(' AND ', $condicoes);

$dadosTotais = $internacaoDao->selectAllInternacaoList($where, $ordenar, null);
$qtdItens = is_array($dadosTotais) ? count($dadosTotais) : 0;
$paginationObj = new pagination($qtdItens, $pagAtual, $limite);
$lista = $internacaoDao->selectAllInternacaoList($where, $ordenar, $paginationObj->getLimit());
$totalPages = $qtdItens > 0 ? (int)ceil($qtdItens / $limite) : 1;

$dadosAlta = $dados_alta ?? [];
sort($dadosAlta);
?>

<div class="container-fluid form_container" id="main-container" style="margin-top:-20px;">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="page-title">Gerar altas</h4>
    </div>
    <hr>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Hospital</label>
                    <input type="text" class="form-control form-control-sm" name="pesquisa_hosp"
                        value="<?= htmlspecialchars($pesquisa_hosp) ?>" placeholder="Nome do hospital">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Paciente</label>
                    <input type="text" class="form-control form-control-sm" name="pesquisa_pac"
                        value="<?= htmlspecialchars($pesquisa_pac) ?>" placeholder="Nome do paciente">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Matrícula</label>
                    <input type="text" class="form-control form-control-sm" name="pesquisa_matricula"
                        value="<?= htmlspecialchars($pesquisa_matricula) ?>" placeholder="Matrícula do paciente">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Registros</label>
                    <select name="limite" class="form-select form-select-sm">
                        <?php foreach ([10, 20, 50] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $limite == $opt ? 'selected' : '' ?>>
                            <?= $opt ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-sm btn-primary" type="submit" style="background:#5e2363;border:none;">Filtrar</button>
                    <a href="list_internacao_gerar_alta.php" class="btn btn-sm btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .gerar-alta-card {
            position: relative;
            border-radius: 20px;
            padding: 1.3rem 1.6rem 1.6rem 2.2rem;
            margin-bottom: 1rem;
            background: #fff;
            border: 1px solid #efe7f6;
            box-shadow: 0 12px 35px -22px rgba(97, 35, 133, 0.6);
        }
        .gerar-alta-card::before {
            content: "";
            position: absolute;
            left: 0.8rem;
            top: 1.2rem;
            bottom: 1.2rem;
            width: 4px;
            border-radius: 10px;
            background: linear-gradient(180deg, #7b2dbf, #c35c91);
        }
        .gerar-alta-card .tag {
            font-size: 0.72rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #8b8ca5;
        }
        .gerar-alta-card .summary-title {
            font-weight: 600;
            color: #2c2742;
            font-size: 1rem;
        }
        .gerar-alta-card .info-pill {
            background: #f9f3ff;
            border-radius: 999px;
            padding: .35rem 1rem;
            font-weight: 600;
            color: #72318f;
        }
        .gerar-alta-card .shadow-field {
            background: #f9fafc;
            border-radius: 14px;
            padding: .85rem 1rem;
            border: 1px solid #eef0f4;
            height: 100%;
        }
        .gerar-alta-card hr {
            margin: 1.2rem 0;
            opacity: .12;
            border-color: #7b2dbf;
        }
        @media (max-width: 991px) {
            .gerar-alta-card {
                padding: 1rem 1.2rem;
            }
        }
    </style>

    <form action="process_gerar_altas.php" method="POST" id="form-gerar-altas">
        <input type="hidden" name="type" value="gerar_altas">
        <?php if ($lista): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <span class="fw-semibold text-muted">
                Marque as internações, preencha data/hora/motivo e clique em <strong>Gerar altas</strong>.
            </span>
            <button type="submit" class="btn btn-lg text-white"
                style="background:#7b2dbf;border-radius:30px;padding:.55rem 1.6rem;">
                Gerar altas selecionadas
            </button>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (!$lista): ?>
                <div class="text-center text-muted py-4">Nenhum paciente internado.</div>
                <?php else: ?>
                <?php foreach ($lista as $row):
                $idIntern = (int)($row['id_internacao'] ?? 0);
                $fieldPrefix = 'alta_' . $idIntern;
                $dataInternacaoFormatada = !empty($row['data_intern_int'])
                    ? date('d/m/Y', strtotime($row['data_intern_int']))
                    : '—';
                $internadoUti = strtolower((string)($row['internado_uti'] ?? 'n')) === 's';
                $idUti = (int)($row['id_uti'] ?? 0);
                $fkInternacaoUti = (int)($row['fk_internacao_uti'] ?? $idIntern);
            ?>
                <div class="gerar-alta-card">
                    <input type="hidden" name="<?= $fieldPrefix ?>_uti_flag" value="<?= $internadoUti ? 's' : 'n' ?>">
                    <?php if ($internadoUti && $idUti): ?>
                    <input type="hidden" name="<?= $fieldPrefix ?>_uti_id" value="<?= $idUti ?>">
                    <input type="hidden" name="<?= $fieldPrefix ?>_uti_fk" value="<?= $fkInternacaoUti ?>">
                    <?php endif; ?>
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <span class="tag d-inline-block mb-1">Hospital</span>
                        <div class="summary-title"><?= htmlspecialchars($row['nome_hosp'] ?? '-') ?></div>
                        <small class="text-muted"><?= htmlspecialchars($row['acomodacao_int'] ?? '') ?></small>
                    </div>
                    <div>
                        <span class="tag d-inline-block mb-1">Paciente</span>
                        <div class="summary-title"><?= htmlspecialchars($row['nome_pac'] ?? '-') ?></div>
                        <small class="text-muted"><?= htmlspecialchars($row['titular_int'] ?? '') ?></small>
                    </div>
                    <div class="text-lg-end">
                        <span class="tag d-block">Data internação</span>
                        <span class="info-pill"><?= $dataInternacaoFormatada ?></span>
                        <div class="mt-2">
                            <span class="badge bg-secondary text-white">ID-INT <?= $idIntern ?></span>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-3 align-items-end">
                    <?php if ($internadoUti && $idUti): ?>
                    <div class="col-12">
                        <div class="shadow-field bg-light">
                            <span class="d-block text-danger fw-semibold">Paciente na UTI</span>
                            <small class="text-muted">Informe a data da alta da UTI antes de gerar.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tag mb-1">Data alta UTI</div>
                        <div class="shadow-field">
                            <input type="date" class="form-control form-control-sm border-0 bg-transparent p-0"
                                name="<?= $fieldPrefix ?>_uti_data">
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <div class="tag mb-1">Data da alta</div>
                        <div class="shadow-field">
                            <input type="date" class="form-control form-control-sm border-0 bg-transparent p-0"
                                name="<?= $fieldPrefix ?>_data">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="tag mb-1">Hora da alta</div>
                        <div class="shadow-field">
                            <input type="time" class="form-control form-control-sm border-0 bg-transparent p-0"
                                name="<?= $fieldPrefix ?>_hora">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tag mb-1">Motivo da alta</div>
                        <div class="shadow-field">
                            <select class="form-select form-select-sm border-0 bg-transparent p-0"
                                name="<?= $fieldPrefix ?>_motivo">
                                <option value="">Selecione...</option>
                                <?php foreach ($dadosAlta as $motivo): ?>
                                <option value="<?= htmlspecialchars($motivo) ?>"><?= htmlspecialchars($motivo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="tag mb-1">Gerar</div>
                        <label class="shadow-field d-flex justify-content-center">
                            <input type="checkbox" class="form-check-input" name="gerar[]" value="<?= $idIntern ?>">
                        </label>
                    </div>
                </div>
            </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>

    </form>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
        <span class="text-muted small">Total encontrado: <?= $qtdItens ?></span>
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Paginação">
            <ul class="pagination justify-content-center mb-0">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $pagAtual ? 'active' : '' ?>">
                    <a class="page-link" href="<?= 'internacoes/gerar-alta/pagina/' . $i ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
