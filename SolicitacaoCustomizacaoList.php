<?php
include_once("check_logado.php");
require_once("globals.php");
require_once("dao/solicitacaoCustomizacaoDao.php");

$norm = function ($txt) {
    $txt = mb_strtolower(trim((string)$txt), 'UTF-8');
    $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $txt);
    $txt = $c !== false ? $c : $txt;
    return preg_replace('/[^a-z]/', '', $txt);
};
$isDiretoria = in_array($norm($_SESSION['cargo'] ?? ''), ['diretoria', 'diretor', 'administrador', 'admin', 'board'], true)
    || in_array($norm($_SESSION['nivel'] ?? ''), ['diretoria', 'diretor', 'administrador', 'admin', 'board'], true)
    || ((int)($_SESSION['nivel'] ?? 0) === -1);

if (!$isDiretoria) {
    http_response_code(403);
    die('Acesso negado. Requer cargo/nível: Diretoria.');
}

$dao = new SolicitacaoCustomizacaoDAO($conn, $BASE_URL);

$busca = trim((string)filter_input(INPUT_GET, 'q'));
$statusFiltro = trim((string)filter_input(INPUT_GET, 'status'));
$prioridadeFiltro = trim((string)filter_input(INPUT_GET, 'prioridade'));
$dataInicio = trim((string)filter_input(INPUT_GET, 'data_inicio'));
$dataFim = trim((string)filter_input(INPUT_GET, 'data_fim'));

$rows = $dao->findAll();
if ($busca !== '') {
    $q = mb_strtolower($busca, 'UTF-8');
    $rows = array_filter($rows, function ($row) use ($q) {
        $hay = mb_strtolower(trim(($row['nome'] ?? '') . ' ' . ($row['empresa'] ?? '') . ' ' . ($row['email'] ?? '')), 'UTF-8');
        return strpos($hay, $q) !== false;
    });
}
if ($statusFiltro !== '') {
    $rows = array_filter($rows, function ($row) use ($statusFiltro) {
        return ($row['status'] ?? '') === $statusFiltro;
    });
}
if ($prioridadeFiltro !== '') {
    $rows = array_filter($rows, function ($row) use ($prioridadeFiltro) {
        return ($row['prioridade'] ?? '') === $prioridadeFiltro;
    });
}
if ($dataInicio !== '' || $dataFim !== '') {
    $rows = array_filter($rows, function ($row) use ($dataInicio, $dataFim) {
        $data = $row['data_solicitacao'] ?? '';
        if ($data === '') {
            return false;
        }
        if ($dataInicio !== '' && $data < $dataInicio) {
            return false;
        }
        if ($dataFim !== '' && $data > $dataFim) {
            return false;
        }
        return true;
    });
}
?>

<style>
.list-customizacao-full {
    max-width: none;
    width: 100%;
    padding: 0 24px 24px;
}
.list-customizacao-full#main-container {
    width: 100%;
    margin: 0;
}
.list-customizacao-card,
.list-customizacao-card .table-responsive,
.list-customizacao-card table {
    width: 100%;
}
.list-customizacao-card .table thead th {
    background: #5e2363;
    color: #fff;
    border-color: #5e2363;
}
.list-customizacao-header {
    padding: 8px 0 16px;
}
.list-customizacao-card {
    width: 100%;
}
</style>

<div class="container-fluid list-customizacao-full" id="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Solicitações de Customização</h2>
            <p class="text-muted mb-0">Acompanhe o andamento das solicitações enviadas.</p>
        </div>
        <a class="btn btn-outline-primary" href="<?= $BASE_URL ?>SolicitacaoCustomizacao.php">Nova solicitação</a>
    </div>

    <form class="row g-2 align-items-center mb-3" method="GET" action="<?= $BASE_URL ?>SolicitacaoCustomizacaoList.php">
        <div class="col-md-4">
            <input type="text" class="form-control" name="q" placeholder="Buscar por nome/empresa/email" value="<?= htmlspecialchars($busca) ?>">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="status">
                <option value="">Status (todos)</option>
                <?php foreach (['Aberto','Em análise','Resolvido','Cancelado'] as $opt) { ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= $statusFiltro === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="prioridade">
                <option value="">Prioridade</option>
                <?php foreach (['Urgente','Alta','Média','Baixa'] as $opt) { ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= $prioridadeFiltro === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-1">
            <input type="date" class="form-control" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>">
        </div>
        <div class="col-md-1">
            <input type="date" class="form-control" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-outline-secondary w-100">Filtrar</button>
        </div>
    </form>

    <div class="card list-customizacao-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Solicitante</th>
                            <th>Empresa</th>
                            <th>Data</th>
                            <th>Prioridade</th>
                            <th>Status</th>
                            <th>Versão</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows) { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Nenhuma solicitação registrada.</td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($rows as $row) { ?>
                                <tr>
                                    <td><?= (int)$row['id_solicitacao'] ?></td>
                                    <td><?= htmlspecialchars($row['nome'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['empresa'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['data_solicitacao'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['prioridade'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['status'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['versao_sistema'] ?? '-') ?></td>
                                    <td class="text-end">
                                        <button
                                            class="btn btn-sm btn-outline-secondary me-2"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalSolicitacaoView"
                                            data-id="<?= (int)$row['id_solicitacao'] ?>"
                                            data-nome="<?= htmlspecialchars($row['nome'] ?? '', ENT_QUOTES) ?>"
                                            data-empresa="<?= htmlspecialchars($row['empresa'] ?? '', ENT_QUOTES) ?>"
                                            data-cargo="<?= htmlspecialchars($row['cargo'] ?? '', ENT_QUOTES) ?>"
                                            data-email="<?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES) ?>"
                                            data-telefone="<?= htmlspecialchars($row['telefone'] ?? '', ENT_QUOTES) ?>"
                                            data-data="<?= htmlspecialchars($row['data_solicitacao'] ?? '', ENT_QUOTES) ?>"
                                            data-modulo-outro="<?= htmlspecialchars($row['modulo_outro'] ?? '', ENT_QUOTES) ?>"
                                            data-prioridade="<?= htmlspecialchars($row['prioridade'] ?? '', ENT_QUOTES) ?>"
                                            data-status="<?= htmlspecialchars($row['status'] ?? '', ENT_QUOTES) ?>"
                                            data-modulos="<?= htmlspecialchars($row['modulos'] ?? '', ENT_QUOTES) ?>"
                                            data-tipos="<?= htmlspecialchars($row['tipos'] ?? '', ENT_QUOTES) ?>"
                                            data-descricao="<?= htmlspecialchars($row['descricao'] ?? '', ENT_QUOTES) ?>"
                                            data-problema="<?= htmlspecialchars($row['problema_atual'] ?? '', ENT_QUOTES) ?>"
                                            data-resultado="<?= htmlspecialchars($row['resultado_esperado'] ?? '', ENT_QUOTES) ?>"
                                            data-impacto="<?= htmlspecialchars($row['impacto_nivel'] ?? '', ENT_QUOTES) ?>"
                                            data-impacto-desc="<?= htmlspecialchars($row['descricao_impacto'] ?? '', ENT_QUOTES) ?>"
                                            data-prazo-desejado="<?= htmlspecialchars($row['prazo_desejado'] ?? '', ENT_QUOTES) ?>"
                                            data-responsavel="<?= htmlspecialchars($row['responsavel'] ?? '', ENT_QUOTES) ?>"
                                            data-assinatura="<?= htmlspecialchars($row['assinatura'] ?? '', ENT_QUOTES) ?>"
                                            data-data-aprovacao="<?= htmlspecialchars($row['data_aprovacao'] ?? '', ENT_QUOTES) ?>"
                                            data-prazo-resposta="<?= htmlspecialchars($row['prazo_resposta'] ?? '', ENT_QUOTES) ?>"
                                            data-precificacao="<?= htmlspecialchars($row['precificacao'] ?? '', ENT_QUOTES) ?>"
                                            data-observacoes="<?= htmlspecialchars($row['observacoes_resposta'] ?? '', ENT_QUOTES) ?>"
                                            data-aprovacao-resposta="<?= htmlspecialchars($row['aprovacao_resposta'] ?? '', ENT_QUOTES) ?>"
                                            data-data-resposta="<?= htmlspecialchars($row['data_resposta'] ?? '', ENT_QUOTES) ?>"
                                            data-resolvido-em="<?= htmlspecialchars($row['resolvido_em'] ?? '', ENT_QUOTES) ?>"
                                            data-resolvido-por="<?= htmlspecialchars($row['resolvido_por'] ?? '', ENT_QUOTES) ?>"
                                            data-versao="<?= htmlspecialchars($row['versao_sistema'] ?? '', ENT_QUOTES) ?>"
                                            data-aprovacao-conex="<?= htmlspecialchars($row['aprovacao_conex'] ?? '', ENT_QUOTES) ?>"
                                            data-data-aprovacao-conex="<?= htmlspecialchars($row['data_aprovacao_conex'] ?? '', ENT_QUOTES) ?>"
                                            data-responsavel-aprovacao-conex="<?= htmlspecialchars($row['responsavel_aprovacao_conex'] ?? '', ENT_QUOTES) ?>"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Ver
                                        </button>
                                        <a class="btn btn-sm btn-primary" href="<?= $BASE_URL ?>SolicitacaoCustomizacaoEdit.php?id=<?= (int)$row['id_solicitacao'] ?>">
                                            <i class="bi bi-pencil-square"></i>
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSolicitacaoView" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-solicitacao-view">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Solicitação <span id="view-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 modal-section conex-bloco">
                    <div class="col-12">
                        <div class="modal-section-title">Bloco Conex</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Solicitante</strong>
                        <div id="view-nome"></div>
                        <div id="view-empresa"></div>
                        <div id="view-cargo"></div>
                        <div id="view-email"></div>
                        <div id="view-telefone"></div>
                    </div>
                    <div class="col-md-6">
                        <strong>Resumo</strong>
                        <div id="view-data"></div>
                        <div id="view-prioridade"></div>
                        <div id="view-status"></div>
                        <div id="view-modulos"></div>
                        <div id="view-tipos"></div>
                        <div id="view-modulo-outro"></div>
                    </div>
                    <div class="col-md-6">
                        <strong>Impacto</strong>
                        <div id="view-impacto"></div>
                        <div id="view-impacto-desc"></div>
                        <div id="view-prazo-desejado"></div>
                    </div>
                    <div class="col-md-6">
                        <strong>Aprovação inicial</strong>
                        <div id="view-responsavel"></div>
                        <div id="view-assinatura"></div>
                        <div id="view-data-aprovacao"></div>
                    </div>
                    <div class="col-12">
                        <strong>Descrição</strong>
                        <div id="view-descricao"></div>
                    </div>
                    <div class="col-12">
                        <strong>Problema atual</strong>
                        <div id="view-problema"></div>
                    </div>
                    <div class="col-12">
                        <strong>Resultado esperado</strong>
                        <div id="view-resultado"></div>
                    </div>
                    <div class="col-12">
                        <strong>Aprovação Conex</strong>
                        <div id="view-aprovacao-conex"></div>
                        <div id="view-data-aprovacao-conex"></div>
                        <div id="view-responsavel-aprovacao-conex"></div>
                    </div>
                </div>
                <div class="row g-3 modal-section fullcare-bloco mt-4">
                    <div class="col-12">
                        <div class="modal-section-title">Bloco FullCare</div>
                    </div>
                    <div class="col-12">
                        <strong>Resposta FullCare</strong>
                        <div id="view-prazo-resposta"></div>
                        <div id="view-precificacao"></div>
                        <div id="view-observacoes"></div>
                        <div id="view-aprovacao-resposta"></div>
                        <div id="view-data-resposta"></div>
                        <div id="view-resolvido-em"></div>
                        <div id="view-resolvido-por"></div>
                        <div id="view-versao"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php include_once("templates/footer.php"); ?>

<style>
.modal-solicitacao-view {
    width: min(95vw, 1200px);
}
.modal-solicitacao-view .modal-content {
    border-radius: 14px;
}
.modal-section {
    padding: 16px;
    border-radius: 12px;
}
.modal-section-title {
    font-weight: 700;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.conex-bloco {
    background: #eff1f4;
    border: 1px solid #d6dbe2;
}
.conex-bloco .modal-section-title {
    color: #3f454d;
}
.fullcare-bloco {
    background: #f6e9fb;
    border: 1px solid #d9c4ea;
}
.fullcare-bloco .modal-section-title {
    color: #5e2363;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('modalSolicitacaoView');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        if (!button) return;

        var get = function(name) {
            return button.getAttribute(name) || '-';
        };

        document.getElementById('view-id').textContent = '#' + get('data-id');
        document.getElementById('view-nome').textContent = get('data-nome');
        document.getElementById('view-empresa').textContent = get('data-empresa');
        document.getElementById('view-cargo').textContent = get('data-cargo');
        document.getElementById('view-email').textContent = get('data-email');
        document.getElementById('view-telefone').textContent = get('data-telefone');
        document.getElementById('view-data').textContent = 'Data: ' + get('data-data');
        document.getElementById('view-prioridade').textContent = 'Prioridade: ' + get('data-prioridade');
        document.getElementById('view-status').textContent = 'Status: ' + get('data-status');
        document.getElementById('view-modulos').textContent = 'Módulos: ' + get('data-modulos');
        document.getElementById('view-tipos').textContent = 'Tipos: ' + get('data-tipos');
        document.getElementById('view-modulo-outro').textContent = 'Outro: ' + get('data-modulo-outro');
        document.getElementById('view-impacto').textContent = 'Impacto: ' + get('data-impacto');
        document.getElementById('view-impacto-desc').textContent = get('data-impacto-desc');
        document.getElementById('view-prazo-desejado').textContent = 'Prazo desejado: ' + get('data-prazo-desejado');
        document.getElementById('view-responsavel').textContent = 'Responsável: ' + get('data-responsavel');
        document.getElementById('view-assinatura').textContent = 'Assinatura: ' + get('data-assinatura');
        document.getElementById('view-data-aprovacao').textContent = 'Data: ' + get('data-data-aprovacao');
        document.getElementById('view-descricao').textContent = get('data-descricao');
        document.getElementById('view-problema').textContent = get('data-problema');
        document.getElementById('view-resultado').textContent = get('data-resultado');
        document.getElementById('view-prazo-resposta').textContent = 'Prazo: ' + get('data-prazo-resposta');
        document.getElementById('view-precificacao').textContent = 'Orçamento: ' + get('data-precificacao');
        document.getElementById('view-observacoes').textContent = get('data-observacoes');
        document.getElementById('view-aprovacao-resposta').textContent = 'Aprovação final: ' + get('data-aprovacao-resposta');
        document.getElementById('view-data-resposta').textContent = 'Data resposta: ' + get('data-data-resposta');
        document.getElementById('view-resolvido-em').textContent = 'Resolvido em: ' + get('data-resolvido-em');
        document.getElementById('view-resolvido-por').textContent = 'Resolvido por: ' + get('data-resolvido-por');
        document.getElementById('view-versao').textContent = 'Versão: ' + get('data-versao');
        document.getElementById('view-aprovacao-conex').textContent = 'Aprovado: ' + get('data-aprovacao-conex');
        document.getElementById('view-data-aprovacao-conex').textContent = 'Data: ' + get('data-data-aprovacao-conex');
        document.getElementById('view-responsavel-aprovacao-conex').textContent = 'Responsável: ' + get('data-responsavel-aprovacao-conex');
    });
});
</script>
