<?php
include_once("check_logado.php");
require_once("globals.php");

$modules = ['Internação', 'Paciente', 'Hospital', 'Auditoria', 'Financeiro', 'Relatórios', 'Outro'];
$tipos = [
    'Novo recurso',
    'Alteração de recurso existente',
    'Correção de erro',
    'Integração com outro sistema',
    'Layout/Visual',
    'Relatório/Exportação',
];
$impactos = ['Baixo', 'Médio', 'Alto'];
$prioridades = ['Urgente', 'Alta', 'Média', 'Baixa'];

$nomeSessao = $_SESSION['usuario_user'] ?? '';
$emailSessao = $_SESSION['email_user'] ?? '';
$cargoSessao = $_SESSION['cargo'] ?? '';
$empresaSessao = '';
if ($emailSessao !== '' && strpos(strtolower($emailSessao), '@conex.') !== false) {
    $empresaSessao = 'Conex';
}
$dataHoje = date('Y-m-d');

$norm = function ($txt) {
    $txt = mb_strtolower(trim((string)$txt), 'UTF-8');
    $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $txt);
    $txt = $c !== false ? $c : $txt;
    return preg_replace('/[^a-z]/', '', $txt);
};
$isDiretoria = in_array($norm($_SESSION['cargo'] ?? ''), ['diretoria', 'diretor', 'administrador', 'admin', 'board'], true)
    || in_array($norm($_SESSION['nivel'] ?? ''), ['diretoria', 'diretor', 'administrador', 'admin', 'board'], true)
    || ((int)($_SESSION['nivel'] ?? 0) === -1);
?>

<style>
.customizacao-shell {
    background: linear-gradient(180deg, #f1f5fb 0%, #e7edf6 100%);
    padding: 24px;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(30, 55, 90, 0.08);
}
.customizacao-hero {
    background: radial-gradient(circle at top left, #f7fbff 0%, #e6eef9 65%, #dde7f4 100%);
    border: 1px solid #d3dceb;
    border-radius: 16px;
    padding: 18px 22px;
}
.customizacao-hero h2 {
    font-size: 1.15rem;
    margin: 0;
    color: #1f2a44;
}
.customizacao-hero p {
    margin: 4px 0 0;
    color: #6c7486;
}
.customizacao-card {
    border: 1px solid #e2e7f0;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(24, 44, 77, 0.06);
}
.customizacao-card .card-header {
    background: transparent;
    border-bottom: none;
    padding-bottom: 0;
}
.customizacao-card .card-title {
    font-size: 0.9rem;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: #5b2f74;
    font-weight: 700;
}
.customizacao-pill {
    border: 1px solid #d7deea;
    border-radius: 999px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fc;
}
.customizacao-pill input {
    margin-top: 0;
}
.customizacao-subtitle {
    font-size: 0.8rem;
    color: #7a8497;
}
</style>

<div class="container-fluid" id="main-container">
    <div class="customizacao-shell">
    <div class="customizacao-hero d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Solicitação de Customização</h2>
            <p>Organize o pedido com detalhes claros para agilizar o desenvolvimento.</p>
        </div>
        <?php if ($isDiretoria) { ?>
            <a class="btn btn-outline-secondary" href="<?= $BASE_URL ?>SolicitacaoCustomizacaoList.php">Ver listagem</a>
        <?php } ?>
    </div>

    <form action="<?= $BASE_URL ?>process_solicitacao_customizacao.php" method="POST" enctype="multipart/form-data" class="needs-validation">
        <input type="hidden" name="type" value="create">
        <div class="card customizacao-card mb-4">
            <div class="card-header">
                <div class="card-title">1. Identificação do solicitante</div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="nome">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($nomeSessao) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="empresa">Empresa</label>
                        <input type="text" class="form-control" id="empresa" name="empresa" value="<?= htmlspecialchars($empresaSessao) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="cargo">Cargo</label>
                        <input type="text" class="form-control" id="cargo" name="cargo" value="<?= htmlspecialchars($cargoSessao) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($emailSessao) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="telefone">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="data_solicitacao">Data da solicitação</label>
                        <input type="date" class="form-control" id="data_solicitacao" name="data_solicitacao" value="<?= $dataHoje ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card customizacao-card mb-4">
            <div class="card-header">
                <div class="card-title">2. Módulo a ser customizado</div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($modules as $module) { ?>
                        <div class="col-md-3">
                            <div class="customizacao-pill">
                                <input class="form-check-input" type="checkbox" name="modulos[]" value="<?= htmlspecialchars($module) ?>" id="modulo_<?= md5($module) ?>">
                                <label class="form-check-label" for="modulo_<?= md5($module) ?>"><?= htmlspecialchars($module) ?></label>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="col-md-12">
                        <label class="form-label customizacao-subtitle" for="modulo_outro">Se marcou Outro, descreva</label>
                        <input type="text" class="form-control" id="modulo_outro" name="modulo_outro">
                    </div>
                </div>
            </div>
        </div>

        <div class="card customizacao-card mb-4">
            <div class="card-header">
                <div class="card-title">3. Tipo de solicitação</div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($tipos as $tipo) { ?>
                        <div class="col-md-4">
                            <div class="customizacao-pill">
                                <input class="form-check-input" type="checkbox" name="tipos[]" value="<?= htmlspecialchars($tipo) ?>" id="tipo_<?= md5($tipo) ?>">
                                <label class="form-check-label" for="tipo_<?= md5($tipo) ?>"><?= htmlspecialchars($tipo) ?></label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="card customizacao-card mb-4">
            <div class="card-header">
                <div class="card-title">4. Detalhamento</div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="descricao">Descrição objetiva da necessidade</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="problema_atual">Como funciona hoje (problema atual)</label>
                        <textarea class="form-control" id="problema_atual" name="problema_atual" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="resultado_esperado">Como deve funcionar (resultado esperado)</label>
                        <textarea class="form-control" id="resultado_esperado" name="resultado_esperado" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card customizacao-card mb-4">
            <div class="card-header">
                <div class="card-title">5. Impacto e prioridade</div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="impacto_nivel">Impacto se não for feito</label>
                        <select class="form-select" id="impacto_nivel" name="impacto_nivel">
                            <option value="">Selecione</option>
                            <?php foreach ($impactos as $impacto) { ?>
                                <option value="<?= htmlspecialchars($impacto) ?>"><?= htmlspecialchars($impacto) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="descricao_impacto">Descrição do impacto</label>
                        <input type="text" class="form-control" id="descricao_impacto" name="descricao_impacto">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="prioridade">Prioridade</label>
                        <select class="form-select" id="prioridade" name="prioridade">
                            <option value="">Selecione</option>
                            <?php foreach ($prioridades as $prioridade) { ?>
                                <option value="<?= htmlspecialchars($prioridade) ?>"><?= htmlspecialchars($prioridade) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="prazo_desejado">Prazo desejado</label>
                        <input type="date" class="form-control" id="prazo_desejado" name="prazo_desejado">
                    </div>
                </div>
            </div>
        </div>

        <div class="card customizacao-card mb-4">
            <div class="card-header">
                <div class="card-title">6. Aprovação</div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="responsavel">Nome do responsável</label>
                        <input type="text" class="form-control" id="responsavel" name="responsavel">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="assinatura">Assinatura</label>
                        <input type="text" class="form-control" id="assinatura" name="assinatura">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="data_aprovacao">Data da aprovação</label>
                        <input type="date" class="form-control" id="data_aprovacao" name="data_aprovacao">
                    </div>
                </div>
            </div>
        </div>

        <div class="card customizacao-card mb-4">
            <div class="card-header">
                <div class="card-title">7. Anexos</div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">Formatos aceitos: JPG, PNG, PDF, DOC/DOCX.</p>
                <input type="file" class="form-control" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            </div>
        </div>

        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <strong>Precisa de apoio?</strong> Fale com o time FullCare no chat interno.
            </div>
            <a class="btn btn-sm btn-primary" href="<?= $BASE_URL ?>show_chat.php">Abrir chat FullCare</a>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success">Enviar solicitação</button>
        </div>
    </form>
    </div>
</div>

<?php include_once("templates/footer.php"); ?>
