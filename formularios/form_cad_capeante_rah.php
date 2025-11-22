<?php

/** =========================================================================
 *  formularios/form_capeante_auditRah.php
 *  Formulário “Capeante RAH” — página única, layout TUSS
 *  - Empilhado por setores (Apto/Enfermaria, UTI, Centro Cirúrgico)
 *  - Cada linha: Descrição | Qtd | Cobrado | Glosado | Cobrado Após (calc) | Observação
 *  - Usa selectAllInternacaoCap2() para carregar identificação
 *  ====================================================================== */

if (isset($conn) && $conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* Dependências principais do projeto */
require_once "models/usuario.php";
require_once "dao/usuarioDao.php";

require_once "dao/internacaoDao.php";
require_once "dao/pacienteDao.php";
require_once "dao/capeanteDao.php";
require_once "dao/patologiaDao.php";

// === CAD CENTRAL: DAOs e listas ===
if (!isset($usuarioDao) || !($usuarioDao instanceof UserDAO)) {
    $usuarioDao = new UserDAO($conn, $BASE_URL);
}
$usuariosAtivos = $usuarioDao->findMedicosEnfermeiros(); // médicos e enfermeiros
$usuariosAdm    = $usuarioDao->findAdministrativos();    // administrativos

// Se o projeto às vezes usa usuarioDAO (minúsculo), mantém alias:
if (!class_exists('userDAO') && class_exists('usuarioDAO')) {
    class_alias('usuarioDAO', 'userDAO');
}

/* Helpers */
$h = function ($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
};
$hi = function ($v) {
    return (int)($v ?? 0);
};
$fmtDateBR = function ($d): string {
    if (!is_string($d) || $d === '' || $d === '0000-00-00') return '';
    $ts = strtotime($d);
    return $ts ? date('d/m/Y', $ts) : '';
};

/* Instâncias */
$internacaoDAO = new internacaoDAO($conn, $BASE_URL);
$capeanteDAO   = new capeanteDAO($conn, $BASE_URL);

/* Parâmetros */
$id_capeante   = filter_input(INPUT_GET, 'id_capeante', FILTER_VALIDATE_INT) ?: null;
$id_internacao = filter_input(INPUT_GET, 'id_internacao', FILTER_VALIDATE_INT) ?: null;
$type          = (string)(filter_input(INPUT_GET, 'type') ?? 'update');

/* Recupera 1 linha principal */
$defaults = [
    'id_capeante' => null,
    'fk_int_capeante' => null,
    'id_internacao' => null,
    'nome_pac' => null,
    'nome_hosp' => null,
    'data_intern_int' => null,
    'pacote' => 'n',
    'parcial_capeante' => 'n',
    'parcial_num' => null,
    'data_inicial_capeante' => null,
    'data_final_capeante' => null,
    'valor_apresentado_capeante' => null,
    'valor_final_capeante' => null,
    'glosa_total' => null,
    'valor_diarias' => null,
    'glosa_diaria' => null,
    'valor_taxa' => null,
    'valor_materiais' => null,
    'valor_medicamentos' => null,
    'valor_sadt' => null,
    'valor_honorarios' => null,
    'valor_opme' => null
];

$where = '';
$order = 'ac.data_intern_int DESC, ac.id_internacao DESC';
if ($type === 'create' && $id_internacao)      $where = 'ac.id_internacao = ' . (int)$id_internacao;
elseif ($id_capeante)                           $where = 'ca.id_capeante = ' . (int)$id_capeante;

$row = $defaults;
if ($where) {
    $lista = $internacaoDAO->selectAllInternacaoCap2($where, $order, null);
    if (is_array($lista) && isset($lista[0]) && is_array($lista[0])) {
        $row = array_merge($defaults, $lista[0]);
    }
}
$novaParcial = filter_input(INPUT_GET, 'nova_parcial') ? true : false;
if ($type === 'create' && $novaParcial && $id_internacao) {
    $row['parcial_capeante'] = 's';
    if (empty($row['parcial_num'])) {
        try {
            $count = $capeanteDAO->getCapeantesCountByInternacao((int)$id_internacao);
            $row['parcial_num'] = $count + 1;
        } catch (Throwable $e) {
            $row['parcial_num'] = null;
        }
    }
}
$fv = function (string $k) use ($row) {
    return $row[$k] ?? null;
};
$hojeYMD = date('Y-m-d');

// === CAD CENTRAL: helpers de cargo/visibilidade ===
$cargoSessao = (string)($_SESSION['cargo'] ?? '');

$isMed = function ($cargo) {
    $c = mb_strtolower((string)$cargo, 'UTF-8');
    return in_array($c, ['med_auditor', 'medico_auditor'], true) || str_contains($c, 'med');
};
$isEnf = function ($cargo) {
    $c = mb_strtolower((string)$cargo, 'UTF-8');
    return in_array($c, ['enf_auditor', 'enfer_auditor'], true) || str_contains($c, 'enf');
};
$isAdm = function ($cargo) {
    $c = mb_strtolower((string)$cargo, 'UTF-8');
    return in_array($c, ['adm', 'administrador', 'administrativo'], true);
};

// Quem pode ver o bloco? (oculta para Méd/Enf)
$mostrarCadastroCentral = !($isMed($cargoSessao) || $isEnf($cargoSessao));

// Estado padrão do seletor "Ativar"
function isProfAssistencial(string $cargo): bool
{
    $norm = mb_strtolower(trim($cargo), 'UTF-8');
    $norm = preg_replace('/[\s\-]+/', '_', $norm);
    if (in_array($norm, ['med_auditor', 'enf_auditor', 'adm'], true)) return true;
    return (bool)preg_match('/^(med|enf)_?auditor$|^adm$/i', $norm);
}
$cadastroCentralDefault = isProfAssistencial($cargoSessao) ? 'n' : 's';

// Valores previamente salvos (se edição)
$medSelecionado = (int)($fv('fk_id_aud_med') ?? 0);
$enfSelecionado = (int)($fv('fk_id_aud_enf') ?? 0);
$admSelecionado = (int)($fv('fk_id_aud_adm') ?? 0);
?>

<link rel="stylesheet" href="<?= $h($BASE_URL) ?>css/rah.css">

<!-- ========================= FORM ========================= -->
<form id="form-capeante-rah" action="<?= $h($BASE_URL) ?>process_rah.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="type" value="<?= $h($type) ?>">
    <input type="hidden" name="id_capeante" value="<?= $type === 'create' ? '' : $hi($fv('id_capeante')) ?>">
    <input type="hidden" name="fk_int_capeante" value="<?= $hi($fv('id_internacao') ?: $fv('fk_int_capeante')) ?>">
    <input type="hidden" id="fk_id_aud_med" name="fk_id_aud_med" value="<?= (int)($fv('fk_id_aud_med') ?? 0) ?>">
    <input type="hidden" id="fk_id_aud_enf" name="fk_id_aud_enf" value="<?= (int)($fv('fk_id_aud_enf') ?? 0) ?>">
    <input type="hidden" id="fk_id_aud_adm" name="fk_id_aud_adm" value="<?= (int)($fv('fk_id_aud_adm') ?? 0) ?>">
    <input type="hidden" name="aud_med_capeante" value="<?= $h($fv('aud_med_capeante') ?? 'n') ?>">
    <input type="hidden" name="aud_enf_capeante" value="<?= $h($fv('aud_enf_capeante') ?? 'n') ?>">
    <input type="hidden" name="aud_adm_capeante" value="<?= $h($fv('aud_adm_capeante') ?? 'n') ?>">
    <input type="hidden" name="med_check" value="<?= $h($fv('med_check') ?? 'n') ?>">
    <input type="hidden" name="enfer_check" value="<?= $h($fv('enfer_check') ?? 'n') ?>">
    <input type="hidden" name="adm_check" value="<?= $h($fv('adm_check') ?? 'n') ?>">


    <!-- IDENTIFICAÇÃO -->
    <div class="id-card">
        <div class="id-header">
            <!-- Avatar com inicial do paciente -->
            <div class="id-avatar">
                <?= strtoupper(mb_substr($h($fv('nome_pac') ?: 'P'), 0, 1, 'UTF-8')) ?>
            </div>

            <!-- Título + chips -->
            <div class="id-title">
                <div class="id-name"><?= $h($fv('nome_pac')) ?></div>
                <div class="id-sub">
                    <span class="id-chip">
                        <i class="bi bi-hospital" style="margin-right:6px;"></i><?= $h($fv('nome_hosp')) ?>
                    </span>
                    <span class="id-sep">•</span>
                    <span class="id-chip">
                        <i class="bi bi-calendar-event" style="margin-right:6px;"></i>Data
                        Internação: <?= $fmtDateBR($fv('data_intern_int')) ?>
                    </span>
                    <span class="id-chip">
                        <i class="bi bi-card-list" style="margin-right:6px;"></i>Senha: <?= ($fv('senha_int')) ?>
                    </span>
                </div>
            </div>

            <!-- Infos à direita -->
            <div class="id-right">
                <div class="id-pill">ID Capeante: <?= $hi($fv('id_capeante')) ?></div>
                <div class="id-pill">ID Internação: <?= $hi($fv('id_internacao')) ?></div>
            </div>
        </div>
    </div>

    <!-- PERÍODO E VALORES GERAIS -->
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-title">Período e Totais</div>
        </div>

        <div class="sec-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" name="data_inicial_capeante"
                        value="<?= $h($fv('data_inicial_capeante') ?: $fv('data_intern_int')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Final</label>
                    <input type="date" class="form-control" name="data_final_capeante"
                        value="<?= $h($fv('data_final_capeante')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valor Apresentado</label>
                    <input type="text" class="form-control dinheiro" id="inp_val_apr" name="valor_apresentado_capeante"
                        value="<?= is_numeric($fv('valor_apresentado_capeante')) ? number_format((float)$fv('valor_apresentado_capeante'), 2, ',', '.') : '' ?>"
                        placeholder="R$ 0,00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valor Final</label>
                    <input type="text" class="form-control dinheiro" id="inp_val_fin" name="valor_final_capeante"
                        value="<?= is_numeric($fv('valor_final_capeante')) ? number_format((float)$fv('valor_final_capeante'), 2, ',', '.') : '' ?>"
                        placeholder="R$ 0,00">
                </div>
            </div>

            <div class="form-line-grid">
                <div class="form-group">
                    <label class="form-label">Data Fechamento</label>
                    <input type="date" class="form-control" name="data_fech_capeante" value="<?= $h($hojeYMD) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Data Digitação</label>
                    <input type="date" class="form-control" name="data_digit_capeante" value="<?= $h($hojeYMD) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Pacote</label>
                    <?php $pacoteVal = ($fv('pacote') ?? 'n'); ?>
                    <select name="pacote" class="form-select">
                        <option value="n" <?= $pacoteVal === 'n' ? 'selected' : ''; ?>>Não</option>
                        <option value="s" <?= $pacoteVal === 's' ? 'selected' : ''; ?>>Sim</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Parcial</label>
                    <?php $parcialVal = ($fv('parcial_capeante') ?? 'n'); ?>
                    <select name="parcial_capeante" class="form-select" id="parcial_capeante">
                        <option value="n" <?= $parcialVal === 'n' ? 'selected' : ''; ?>>Não</option>
                        <option value="s" <?= $parcialVal === 's' ? 'selected' : ''; ?>>Sim</option>
                    </select>
                </div>

                <div class="form-group fg-parcial-num" id="wrap_parcial_num"
                    style="<?= $parcialVal === 's' ? '' : 'display:none' ?>">
                    <label class="form-label">Número da Parcial</label>
                    <input type="number" class="form-control" name="parcial_num" value="<?= $h($fv('parcial_num')) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Encerrado</label>
                    <?php $encerradoVal = ($fv('encerrado_cap') ?? 's'); ?>
                    <select name="encerrado_cap" class="form-select" id="encerrado_cap">
                        <option value="s" <?= $encerradoVal === 's' ? 'selected' : ''; ?>>Sim</option>
                        <option value="n" <?= $encerradoVal === 'n' ? 'selected' : ''; ?>>Não</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Senha finalizada</label>
                    <?php $senhaFinalVal = ($fv('senha_finalizada') ?? 'n'); ?>
                    <select name="senha_finalizada" class="form-select" id="senha_finalizada">
                        <option value="n" <?= $senhaFinalVal === 'n' ? 'selected' : ''; ?>>Não</option>
                        <option value="s" <?= $senhaFinalVal === 's' ? 'selected' : ''; ?>>Sim</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <?php if ($mostrarCadastroCentral): ?>
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-title">Cadastro Equipe</div>
            <div class="sec-right">
                <div class="pill"><span>Status:</span> <strong id="cc-pill">—</strong></div>
            </div>
        </div>
        <div class="sec-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-2">
                    <label for="cadastro_central_cap" class="form-label">Ativar</label>
                    <select class="form-select form-select-sm" id="cadastro_central_cap" name="cadastro_central_cap">
                        <option value="n" <?= $cadastroCentralDefault === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $cadastroCentralDefault === 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label" for="cad_central_med_id">Médico(a)</label>
                    <select class="form-select form-select-sm" id="cad_central_med_id" name="fk_id_aud_med">
                        <option value="">Selecione</option>
                        <?php foreach ($usuariosAtivos as $u): if ($isMed($u['cargo_user'] ?? '')):
                                    $id = (int)($u['id_usuario'] ?? 0);
                                    $nome = (string)($u['usuario_user'] ?? '');
                                    $sel = ($id === $medSelecionado) ? 'selected' : ''; ?>
                        <option value="<?= $id ?>" <?= $sel ?>><?= $h($nome) ?></option>
                        <?php endif;
                            endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label" for="cad_central_enf_id">Enfermeiro(a)</label>
                    <select class="form-select form-select-sm" id="cad_central_enf_id" name="fk_id_aud_enf">
                        <option value="">Selecione</option>
                        <?php foreach ($usuariosAtivos as $u): if ($isEnf($u['cargo_user'] ?? '')):
                                    $id = (int)($u['id_usuario'] ?? 0);
                                    $nome = (string)($u['usuario_user'] ?? '');
                                    $sel = ($id === $enfSelecionado) ? 'selected' : ''; ?>
                        <option value="<?= $id ?>" <?= $sel ?>><?= $h($nome) ?></option>
                        <?php endif;
                            endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label" for="cad_central_adm_id">Administrativo(a)</label>
                    <select class="form-select form-select-sm" id="cad_central_adm_id" name="fk_id_aud_adm">
                        <option value="">Selecione</option>
                        <?php foreach ($usuariosAdm as $u):
                                $id = (int)($u['id_usuario'] ?? 0);
                                $nome = (string)($u['usuario_user'] ?? '');
                                $sel = ($id === $admSelecionado) ? 'selected' : ''; ?>
                        <option value="<?= $id ?>" <?= $sel ?>><?= $h($nome) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- DIÁRIAS (formato PDF) -->
    <div class="block" data-group="diarias">
        <!-- TÍTULO / TOGGLER -->
        <h5>
            Diárias
        </h5>

        <!-- CONTEÚDO COLAPSÁVEL (tuss-grid + totais) -->
        <div id="grp-diarias" class="collapse">
            <div class="tuss-grid mt-3">
                <div class="tg-head tg-col-desc">Diária</div>
                <div class="tg-head tg-col-qtd">Qtd.</div>
                <div class="tg-head tg-col-cob">Cobrado</div>
                <div class="tg-head tg-col-glo">Glosado</div>
                <div class="tg-head tg-col-lib">Cobrado Após</div>
                <div class="tg-head tg-col-obs">Observação</div>

                <!-- QUARTO / APTO -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Quarto / Apto</div>
                    <input name="ac_quarto_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_quarto_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_quarto_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_quarto_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_quarto_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- DAY CLINIC -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Day Clinic</div>
                    <input name="ac_dayclinic_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_dayclinic_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_dayclinic_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_dayclinic_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_dayclinic_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- UTI -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">UTI</div>
                    <input name="ac_uti_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_uti_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_uti_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_uti_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_uti_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- UTI / SEMI -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">UTI / Semi</div>
                    <input name="ac_utisemi_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_utisemi_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_utisemi_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_utisemi_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_utisemi_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- ENFERMARIA -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Enfermaria</div>
                    <input name="ac_enfermaria_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_enfermaria_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_enfermaria_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_enfermaria_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_enfermaria_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- BERÇÁRIO -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Berçário</div>
                    <input name="ac_bercario_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_bercario_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_bercario_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_bercario_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_bercario_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- ACOMPANHANTE -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Acompanhante</div>
                    <input name="ac_acompanhante_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_acompanhante_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_acompanhante_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_acompanhante_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_acompanhante_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>

                <!-- ISOLAMENTO -->
                <div class="tuss-row rah-row">
                    <div class="tg-lab tg-col-desc">Isolamento</div>
                    <input name="ac_isolamento_qtd" class="form-control tg-col-qtd" placeholder="Qtd.">
                    <input name="ac_isolamento_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                        placeholder="R$ 0,00">
                    <input name="ac_isolamento_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                        placeholder="R$ 0,00">
                    <input name="ac_isolamento_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                        placeholder="R$ 0,00" readonly>
                    <input name="ac_isolamento_obs" class="form-control tg-col-obs" placeholder="Observação">
                </div>
            </div>

            <!-- CONSOLIDADO LOCAL (Diárias) -->
            <div class="row g-2 mt-2 grp-totais">
                <div class="col-md-3">
                    <label class="form-label">Total Cobrado (Diárias)</label>
                    <input type="text" name="diarias_total_cobrado" class="form-control dinheiro grp-total-cobrado"
                        readonly value="R$ 0,00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Total Glosado (Diárias)</label>
                    <input type="text" name="diarias_total_glosado" class="form-control dinheiro grp-total-glosado"
                        readonly value="R$ 0,00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Total Cobrado Após (Diárias)</label>
                    <input type="text" name="diarias_total_liberado" class="form-control dinheiro grp-total-liberado"
                        readonly value="R$ 0,00">
                </div>
            </div>
        </div>
    </div>




    <!-- SETOR: APTO / ENFERMARIA -->
    <div class="block apto" data-group="apto">
        <h5>Apto / Enfermaria</h5>

        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Terapias</div>
                <input name="ap_terapias_qtd" class="form-control tg-col-qtd" placeholder="Qtd">
                <input name="ap_terapias_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_terapias_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_terapias_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_terapias_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Taxas / Aluguéis</div>
                <input name="ap_taxas_qtd" class="form-control tg-col-qtd">
                <input name="ap_taxas_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_taxas_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_taxas_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_taxas_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material de Consumo</div>
                <input name="ap_mat_consumo_qtd" class="form-control tg-col-qtd">
                <input name="ap_mat_consumo_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_mat_consumo_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_mat_consumo_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_mat_consumo_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Medicamentos</div>
                <input name="ap_medicametos_qtd" class="form-control tg-col-qtd">
                <input name="ap_medicametos_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_medicametos_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_medicametos_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_medicametos_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Gases Medicinais</div>
                <input name="ap_gases_qtd" class="form-control tg-col-qtd">
                <input name="ap_gases_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_gases_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_gases_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_gases_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material Especial</div>
                <input name="ap_mat_espec_qtd" class="form-control tg-col-qtd">
                <input name="ap_mat_espec_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_mat_espec_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_mat_espec_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_mat_espec_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Exames / SADT</div>
                <input name="ap_exames_qtd" class="form-control tg-col-qtd">
                <input name="ap_exames_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_exames_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_exames_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_exames_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Hemoderivados</div>
                <input name="ap_hemoderivados_qtd" class="form-control tg-col-qtd">
                <input name="ap_hemoderivados_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_hemoderivados_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_hemoderivados_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_hemoderivados_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Honorários</div>
                <input name="ap_honorarios_qtd" class="form-control tg-col-qtd">
                <input name="ap_honorarios_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="ap_honorarios_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="ap_honorarios_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="ap_honorarios_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>
        </div>

        <!-- CONSOLIDADO LOCAL (Apto) -->
        <div class="row g-2 mt-2 grp-totais">
            <div class="col-md-3">
                <label class="form-label">Total Cobrado (Apto)</label>
                <input type="text" name="apto_total_cobrado" class="form-control dinheiro grp-total-cobrado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Glosado (Apto)</label>
                <input type="text" name="apto_total_glosado" class="form-control dinheiro grp-total-glosado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Cobrado Após (Apto)</label>
                <input type="text" name="apto_total_liberado" class="form-control dinheiro grp-total-liberado" readonly
                    value="R$ 0,00">
            </div>
        </div>
    </div>


    <!-- SETOR: UTI -->
    <div class="block uti" data-group="uti">
        <h5>UTI</h5>

        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Terapias</div>
                <input name="uti_terapias_qtd" class="form-control tg-col-qtd">
                <input name="uti_terapias_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_terapias_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_terapias_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_terapias_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Taxas / Aluguéis</div>
                <input name="uti_taxas_qtd" class="form-control tg-col-qtd">
                <input name="uti_taxas_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_taxas_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_taxas_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_taxas_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material de Consumo</div>
                <input name="uti_mat_consumo_qtd" class="form-control tg-col-qtd">
                <input name="uti_mat_consumo_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_mat_consumo_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_mat_consumo_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_mat_consumo_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Medicamentos</div>
                <input name="uti_medicametos_qtd" class="form-control tg-col-qtd">
                <input name="uti_medicametos_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_medicametos_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_medicametos_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_medicametos_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Gases Medicinais</div>
                <input name="uti_gases_qtd" class="form-control tg-col-qtd">
                <input name="uti_gases_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_gases_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_gases_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_gases_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material Especial</div>
                <input name="uti_mat_espec_qtd" class="form-control tg-col-qtd">
                <input name="uti_mat_espec_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_mat_espec_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_mat_espec_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_mat_espec_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Exames / SADT</div>
                <input name="uti_exames_qtd" class="form-control tg-col-qtd">
                <input name="uti_exames_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_exames_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_exames_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_exames_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Hemoderivados</div>
                <input name="uti_hemoderivados_qtd" class="form-control tg-col-qtd">
                <input name="uti_hemoderivados_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_hemoderivados_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_hemoderivados_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_hemoderivados_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Honorários</div>
                <input name="uti_honorarios_qtd" class="form-control tg-col-qtd">
                <input name="uti_honorarios_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="uti_honorarios_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="uti_honorarios_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="uti_honorarios_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>
        </div>

        <!-- CONSOLIDADO LOCAL (UTI) -->
        <div class="row g-2 mt-2 grp-totais">
            <div class="col-md-3">
                <label class="form-label">Total Cobrado (UTI)</label>
                <input type="text" name="uti_total_cobrado" class="form-control dinheiro grp-total-cobrado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Glosado (UTI)</label>
                <input type="text" name="uti_total_glosado" class="form-control dinheiro grp-total-glosado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Cobrado Após (UTI)</label>
                <input type="text" name="uti_total_liberado" class="form-control dinheiro grp-total-liberado" readonly
                    value="R$ 0,00">
            </div>
        </div>
    </div>

    <!-- SETOR: CENTRO CIRÚRGICO -->
    <div class="block cc" data-group="cc">
        <h5>Centro Cirúrgico</h5>

        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Terapias</div>
                <input name="cc_terapias_qtd" class="form-control tg-col-qtd">
                <input name="cc_terapias_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_terapias_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_terapias_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_terapias_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Taxas / Aluguéis</div>
                <input name="cc_taxas_qtd" class="form-control tg-col-qtd">
                <input name="cc_taxas_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_taxas_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_taxas_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_taxas_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material de Consumo</div>
                <input name="cc_mat_consumo_qtd" class="form-control tg-col-qtd">
                <input name="cc_mat_consumo_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_mat_consumo_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_mat_consumo_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_mat_consumo_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Medicamentos</div>
                <input name="cc_medicametos_qtd" class="form-control tg-col-qtd">
                <input name="cc_medicametos_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_medicametos_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_medicametos_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_medicametos_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Gases Medicinais</div>
                <input name="cc_gases_qtd" class="form-control tg-col-qtd">
                <input name="cc_gases_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_gases_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_gases_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_gases_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Material Especial</div>
                <input name="cc_mat_espec_qtd" class="form-control tg-col-qtd">
                <input name="cc_mat_espec_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_mat_espec_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_mat_espec_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_mat_espec_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Exames / SADT</div>
                <input name="cc_exames_qtd" class="form-control tg-col-qtd">
                <input name="cc_exames_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_exames_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_exames_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_exames_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Hemoderivados</div>
                <input name="cc_hemoderivados_qtd" class="form-control tg-col-qtd">
                <input name="cc_hemoderivados_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_hemoderivados_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_hemoderivados_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_hemoderivados_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Honorários</div>
                <input name="cc_honorarios_qtd" class="form-control tg-col-qtd">
                <input name="cc_honorarios_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="cc_honorarios_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="cc_honorarios_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="cc_honorarios_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>
        </div>

        <!-- CONSOLIDADO LOCAL (CC) -->
        <div class="row g-2 mt-2 grp-totais">
            <div class="col-md-3">
                <label class="form-label">Total Cobrado (CC)</label>
                <input type="text" name="cc_total_cobrado" class="form-control dinheiro grp-total-cobrado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Glosado (CC)</label>
                <input type="text" name="cc_total_glosado" class="form-control dinheiro grp-total-glosado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Cobrado Após (CC)</label>
                <input type="text" name="cc_total_liberado" class="form-control dinheiro grp-total-liberado" readonly
                    value="R$ 0,00">
            </div>
        </div>
    </div>
    <!-- SETOR: OUTROS -->
    <div class="block" data-group="outros">
        <h5>Outros</h5>

        <div class="tuss-grid">
            <div class="tg-head tg-col-desc">Descrição</div>
            <div class="tg-head tg-col-qtd">Qtd.</div>
            <div class="tg-head tg-col-cob">Cobrado</div>
            <div class="tg-head tg-col-glo">Glosado</div>
            <div class="tg-head tg-col-lib">Cobrado Após</div>
            <div class="tg-head tg-col-obs">Observação</div>

            <!-- PACOTE -->
            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Pacote</div>
                <input name="outros_pacote_qtd" class="form-control tg-col-qtd" placeholder="Qtd">
                <input name="outros_pacote_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="outros_pacote_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="outros_pacote_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="outros_pacote_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>

            <!-- REMOÇÃO -->
            <div class="tuss-row rah-row">
                <div class="tg-lab tg-col-desc">Remoção</div>
                <input name="outros_remocao_qtd" class="form-control tg-col-qtd" placeholder="Qtd">
                <input name="outros_remocao_cobrado" class="form-control dinheiro tg-col-cob rah-cobrado"
                    placeholder="R$ 0,00">
                <input name="outros_remocao_glosado" class="form-control dinheiro tg-col-glo rah-glosado"
                    placeholder="R$ 0,00">
                <input name="outros_remocao_liberado" class="form-control dinheiro tg-col-lib rah-liberado"
                    placeholder="R$ 0,00" readonly>
                <input name="outros_remocao_obs" class="form-control tg-col-obs" placeholder="Observação">
            </div>
        </div>

        <!-- CONSOLIDADO LOCAL (Outros) -->
        <div class="row g-2 mt-2 grp-totais">
            <div class="col-md-3">
                <label class="form-label">Total Cobrado (Outros)</label>
                <input type="text" name="outros_total_cobrado" class="form-control dinheiro grp-total-cobrado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Glosado (Outros)</label>
                <input type="text" name="outros_total_glosado" class="form-control dinheiro grp-total-glosado" readonly
                    value="R$ 0,00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total Cobrado Após (Outros)</label>
                <input type="text" name="outros_total_liberado" class="form-control dinheiro grp-total-liberado"
                    readonly value="R$ 0,00">
            </div>
        </div>
    </div>


    <!-- AÇÕES -->
    <div class="block">
        <div class="actions">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Salvar</button>
            <button type="button" class="btn btn-outline-primary" id="btnSalvarPDF"><i class="bi bi-download"></i>
                Salvar PDF</button>
            <button type="button" class="btn btn-outline-secondary" id="btnEnviarEmail"><i
                    class="bi bi-envelope-fill"></i>
                Enviar PDF por e-mail</button>
        </div>
        <iframe id="iframeDownload" name="iframeDownload" style="display:none;"></iframe>
        <div id="mensagemStatus"
            style="display:none;margin-top:10px;padding:10px;border-radius:5px;font-weight:bold;text-align:center;">
        </div>
    </div>
</form>

<!-- Vendors -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script>

<!-- RAH (agora em /js) -->
<script src="<?= $h($BASE_URL) ?>js/rah-core.js" defer></script>
<script src="<?= $h($BASE_URL) ?>js/rah-calc.js" defer></script>
<script src="<?= $h($BASE_URL) ?>js/rah-ui.js" defer></script>
<script src="<?= $h($BASE_URL) ?>js/rah-pdf.js" defer></script>
<script src="<?= $h($BASE_URL) ?>js/rah-cadcentral.js" defer></script>
