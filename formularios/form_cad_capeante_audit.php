<?php

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

// require_once "config.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ======================================================================
// DAOs / Models
// ======================================================================
require_once "models/usuario.php";
require_once "dao/usuarioDao.php";
// Instancia o DAO se ainda não existir
if (!isset($usuarioDao) || !($usuarioDao instanceof UserDAO)) {
    $usuarioDao = new UserDAO($conn, $BASE_URL);
}
$usuariosAtivos = $usuarioDao->findMedicosEnfermeiros(); // médico/enfermeiro
$usuariosAdm    = $usuarioDao->findAdministrativos();    // administrativos

/**
 * Alias de compatibilidade: se o projeto definir usuarioDAO/UsuarioDAO,
 * permite continuar instanciando como userDAO sem quebrar.
 */
if (!class_exists('userDAO') && class_exists('usuarioDAO')) {
    class_alias('usuarioDAO', 'userDAO');
}
if (!class_exists('userDAO') && class_exists('UsuarioDAO')) {
    class_alias('UsuarioDAO', 'userDAO');
}

// Instâncias
$internacao_geral = new internacaoDAO($conn, $BASE_URL);
$pacienteDao      = new pacienteDAO($conn, $BASE_URL);
$capeante_geral   = new capeanteDAO($conn, $BASE_URL);
$hospital_geral   = new HospitalDAO($conn, $BASE_URL);
$patologiaDao     = new patologiaDAO($conn, $BASE_URL);
$usuarioDao       = new userDAO($conn, $BASE_URL);

// Listas auxiliares (use $limite padrão)
$limite         = filter_input(INPUT_GET, 'limite', FILTER_VALIDATE_INT) ?: 10;
$inicio         = null;
$pacientes      = $pacienteDao->findGeral($limite, $inicio);
$hospitals      = $hospital_geral->findGeral($limite, $inicio);
$patologias     = $patologiaDao->findGeral();
$usuariosAtivos = $usuarioDao->findMedicosEnfermeiros();

// ======================================================================
// PARÂMETROS
// ======================================================================
$id_internacao = filter_input(INPUT_GET, 'id_internacao', FILTER_VALIDATE_INT) ?: null;
$id_capeante   = filter_input(INPUT_GET, 'id_capeante',   FILTER_VALIDATE_INT) ?: null;
$type          = (string)(filter_input(INPUT_GET, 'type') ?? '');
$order         = (string)(filter_input(INPUT_GET, 'ordenar', FILTER_DEFAULT) ?: 'ac.data_intern_int DESC, ac.id_internacao DESC');
$obLimite      = null; // use como "10" ou "0,10" se quiser

// ======================================================================
// CONTROLE DE ACESSO
// - diretor/gestor: sem filtro por hospital
// - med/enf/adm/hospital: filtra por vínculo na tb_hospitalUser
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
$precisaFiltro = in_array($cargoSessao, $rolesComFiltro, true);

// userId que aciona o filtro interno do método (ou null p/ diretor/gestor)
$userFiltro = ($precisaFiltro && $userIdSess > 0) ? $userIdSess : null;

// ======================================================================
// HELPERS
// ======================================================================
$h  = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$hi = fn($v) => (int)($v ?? 0);
$fmtDateBR = function ($d): string {
    if (!is_string($d) || $d === '' || $d === '0000-00-00') return '';
    $ts = strtotime($d);
    return $ts ? date('d/m/Y', $ts) : '';
};

// ======================================================================
// BUSCA PRINCIPAL (usa selectAllInternacaoCap2($userFiltro, $where, $order, $limit))
// - CREATE: filtra pela internação
// - EDIT:   filtra pelo id_capeante (alias ca existe no LEFT JOIN do método)
// ======================================================================
$intern = [];
$ultimo = []; // sempre definido

if ($type === 'create' && $id_internacao) {
    $where  = "ac.id_internacao = " . (int)$id_internacao;
    $intern = $internacao_geral->selectAllInternacaoCap2($userFiltro, $where, $order, $obLimite);

    // infos de parciais (seguem seus métodos)
    $parcial_count = $capeante_geral->getCapeanteByInternacao($hi($id_internacao));
    $parcial_date  = $capeante_geral->getLastCapeanteByInternacao($hi($id_internacao));

    // último período para o texto "período anterior"
    $ultimo = $capeante_geral->getUltimoCapeantePeriodoByInternacao($hi($id_internacao)) ?: [];
} elseif ($type !== 'create' && $id_capeante) {
    $where  = "ca.id_capeante = " . (int)$id_capeante;
    $intern = $internacao_geral->selectAllInternacaoCap2($userFiltro, $where, $order, $obLimite);
}

// ======================================================================
// NORMALIZA A 1ª LINHA (defaults)
// ======================================================================
$defaults = [
    'id_capeante' => null,
    'fk_int_capeante' => null,
    'data_inicial_capeante' => null,
    'data_final_capeante' => null,
    'valor_apresentado_capeante' => null,
    'valor_final_capeante' => null,
    'glosa_total' => null,
    'senha_finalizada' => 'n',
    'encerrado_cap' => 'n',
    'aberto_cap' => 's',
    'em_auditoria_cap' => 'n',
    'lote_cap' => null,
    'conta_faturada_cap' => null,
    'parcial_capeante' => 'n',
    'parcial_num' => null,
    'diarias_capeante' => null,
    'valor_glosa_enf' => null,
    'valor_glosa_med' => null,
    'id_internacao' => null,
    'data_intern_int' => null,
    'fk_paciente_int' => null,
    'fk_hospital_int' => null,
    'id_paciente' => null,
    'nome_pac' => null,
    'id_hospital' => null,
    'nome_hosp' => null,
    'senha_int' => null,
    'cadastro_central_cap' => 'n',
    'fk_id_aud_med' => null,
    'fk_id_aud_enf' => null,
    'adm_check' => 'n',
    'med_check' => 'n',
    'enfer_check' => 'n',
    'valor_diarias' => null,
    'glosa_diaria' => null,
    'valor_oxig' => null,
    'glosa_oxig' => null,
    'valor_taxa' => null,
    'glosa_taxas' => null,
    'valor_matmed' => null,
    'glosa_matmed' => null,
    'valor_sadt' => null,
    'glosa_sadt' => null,
    'valor_honorarios' => null,
    'glosa_honorarios' => null,
    'valor_opme' => null,
    'glosa_opme' => null,
    'desconto_valor_cap' => null
];

$internRow = $defaults;
if (is_array($intern) && isset($intern[0]) && is_array($intern[0])) {
    $internRow = array_merge($defaults, $intern[0]);
}
$val = fn(string $k) => $internRow[$k] ?? null;

// ======================================================================
// TEXTO PERÍODO ANTERIOR + PARCIAL DEFAULT
// ======================================================================
$textoPeriodoAnterior = " — Primeira parcial";
$parcialDefault = ($type === 'create') ? 's' : ((($val('parcial_capeante') ?? 'n') === 's') ? 's' : 'n');

if ($type === 'create') {
    $idIntCtx = $hi($val('id_internacao') ?: $id_internacao);
    if ($idIntCtx > 0 && !empty($ultimo)) {
        $iniBR = $fmtDateBR($ultimo['data_inicial_capeante'] ?? null);
        $fimBR = $fmtDateBR($ultimo['data_final_capeante']   ?? null);
        if ($iniBR && $fimBR) $textoPeriodoAnterior = "Último Parcial — Período {$iniBR} a {$fimBR}";
        elseif ($iniBR)           $textoPeriodoAnterior = "Último Parcial — Período iniciado em {$iniBR}";
        elseif ($fimBR)           $textoPeriodoAnterior = "Último Parcial — Período até {$fimBR}";
        else                      $textoPeriodoAnterior = "Último Parcial — (período anterior não disponível)";
    }
}

// ======================================================================
// CADASTRO CENTRAL (estado inicial)
// ======================================================================
$medSelecionado = $hi($val('fk_id_aud_med'));
$enfSelecionado = $hi($val('fk_id_aud_enf'));

$cargoSessao = $_SESSION['cargo'] ?? '';

function isProfissionalAssistencial(string $cargo): bool
{
    $norm = mb_strtolower(trim($cargo), 'UTF-8');
    $norm = preg_replace('/[\s\-]+/', '_', $norm);
    if (in_array($norm, ['med_auditor', 'enf_auditor', 'adm'], true)) {
        return true;
    }
    return (bool) preg_match('/^(med|enf)_?auditor$|^adm$/i', $norm);
}
$cadastroCentralDefault = isProfissionalAssistencial($cargoSessao) ? 'n' : 's';

$isMed = static function ($cargo) {
    $c = mb_strtolower((string)$cargo, 'UTF-8');
    return in_array($c, ['med_auditor', 'medico_auditor'], true);
};
$isEnf = static function ($cargo) {
    $c = mb_strtolower((string)$cargo, 'UTF-8');
    return in_array($c, ['enf_auditor', 'enfer_auditor'], true);
};
$isAdm = static function ($cargo) {
    $c = mb_strtolower((string)$cargo, 'UTF-8');
    return in_array($c, ['adm', 'administrador', 'administrativo'], true);
};
$mostrarCadastroCentral = !($isMed($cargoSessao) || $isEnf($cargoSessao));

$agora = date('Y-m-d H:i:s');
$lastFinalDateHidden = '';
if ($type === 'create' && !empty($ultimo['data_final_capeante']) && $ultimo['data_final_capeante'] !== '0000-00-00') {
    $lastFinalDateHidden = (string)$ultimo['data_final_capeante'];
}
?>
<input type="hidden" id="last_final_date" value="<?= $h($lastFinalDateHidden) ?>">

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<div class="container-fluid px-0" id="main-container" style="background:#f5f6f8; min-height:100vh; ">
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: 33%;" aria-valuenow="33"
            aria-valuemin="0" aria-valuemax="100">
            Etapa 1 de 3
        </div>
    </div>

    <form action="<?= $h($BASE_URL) ?>process_capeante.php" id="multi-step-form" method="POST"
        enctype="multipart/form-data">
        <?php if ($type === "create"): ?>
        <input type="hidden" name="type" value="create">
        <input type="hidden" name="id_capeante" value="">
        <?php else: ?>
        <input type="hidden" name="type" value="update">
        <input type="hidden" name="id_capeante" value="<?= $hi($val('id_capeante')) ?>">
        <?php endif; ?>

        <!-- flags de cargo (VISÍVEIS) -->
        <input type="text" id="adm_capeante" name="adm_capeante"
            value="<?= ($_SESSION['cargo'] ?? '') === 'Adm' ? 's' : '' ?>" readonly>

        <input type="text" id="aud_enf_capeante" name="aud_enf_capeante"
            value="<?= ($_SESSION['cargo'] ?? '') === 'Enf_Auditor' ? 's' : '' ?>" readonly>

        <input type="text" id="aud_med_capeante" name="aud_med_capeante"
            value="<?= ($_SESSION['cargo'] ?? '') === 'Med_auditor' ? 's' : '' ?>" readonly>

        <!-- checks (VISÍVEIS) -->
        <input type="text" id="adm_check" name="adm_check"
            value="<?= (($_SESSION['cargo'] ?? '') === 'Adm') ? 's' : $h($val('adm_check')) ?>" readonly>

        <input type="text" id="med_check" name="med_check"
            value="<?= (($_SESSION['cargo'] ?? '') === 'Med_auditor') ? 's' : $h($val('med_check')) ?>" readonly>

        <input type="text" id="enfer_check" name="enfer_check"
            value="<?= (($_SESSION['cargo'] ?? '') === 'Enf_Auditor') ? 's' : $h($val('enfer_check')) ?>" readonly>

        <!-- FKs (VISÍVEIS) -->
        <input type="text" id="fk_id_aud_adm" name="fk_id_aud_adm"
            value="<?= (($_SESSION['cargo'] ?? '') === 'Adm') ? $hi($_SESSION['id_usuario'] ?? 0) : $hi($val('fk_id_aud_adm')) ?>"
            readonly>

        <input type="text" id="fk_id_aud_enf" name="fk_id_aud_enf"
            value="<?= (($_SESSION['cargo'] ?? '') === 'Enf_Auditor') ? $hi($_SESSION['id_usuario'] ?? 0) : $hi($val('fk_id_aud_enf')) ?>"
            readonly>

        <input type="text" id="fk_id_aud_med" name="fk_id_aud_med"
            value="<?= (($_SESSION['cargo'] ?? '') === 'Med_auditor') ? $hi($_SESSION['id_usuario'] ?? 0) : $hi($val('fk_id_aud_med')) ?>"
            readonly>

        <!-- nível do usuário -->
        <input type="hidden" id="nivel_user" value="<?= (int)($_SESSION['nivel'] ?? 0) ?>">

        <!-- chaves -->
        <input type="hidden" id="fk_int_capeante" name="fk_int_capeante"
            value="<?= $hi($val('id_internacao') ?: $id_internacao) ?>">
        <input type="hidden" id="fk_hospital_int" name="fk_hospital_int" value="<?= $h($val('nome_hosp')) ?>" readonly>
        <input type="hidden" id="fk_user_cap" name="fk_user_cap" value="<?= $hi($_SESSION['id_usuario'] ?? 0) ?>">
        <input type="hidden" id="fk_paciente_int" name="fk_paciente_int" value="<?= $hi($val('fk_paciente_int')) ?>"
            readonly>
        <input type="hidden" id="data_intern_int" name="data_intern_int" value="<?= $h($val('data_intern_int')) ?>">
        <input type="hidden" id="data_create_cap" name="data_create_cap" value="<?= $h($agora) ?>">
        <input type="hidden" id="usuario_create_cap" name="usuario_create_cap"
            value="<?= $h($_SESSION['email_user'] ?? '') ?>">
        <input type="hidden" id="aberto_cap" name="aberto_cap" value="n">
        <input type="hidden" id="em_auditoria_cap" name="em_auditoria_cap" value="s">

        <!-- Step 1 -->
        <div id="step-1" class="step">
            <h3>Passo 1: Informações Básicas</h3>
            <br>
            <div class="form-group row">
                <!-- ALTERADO: align-items-start flex-wrap -->
                <div id="view-contact-container" class="container-fluid d-flex align-items-start flex-wrap">

                    <!-- ALTERADO: wrapper ocupa 100% -->
                    <div class="d-flex w-100" style="flex:1 1 100%; gap:20px; flex-wrap:wrap;">

                        <!-- COLUNA ESQUERDA -->
                        <div class="d-flex flex-column" style="flex:1 1 280px; min-width:280px;">
                            <div><span class="card-title bold" style="font-weight:600;">Código Capeante:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('id_capeante')) ?></span>
                            </div>
                            <div><span class="card-title bold" style="font-weight:600;">Código Internação :</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('id_internacao') ?: $id_internacao) ?></span>
                            </div>
                            <div><span class="card-title bold" style="font-weight:600;">Data Internação:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $fmtDateBR($val('data_intern_int')) ?></span>
                            </div>
                            <div><span class="card-title bold" style="font-weight:600;">Senha:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('senha_int')) ?></span>
                            </div>
                        </div>

                        <!-- COLUNA DIREITA -->
                        <div class="d-flex flex-column" style="flex:1 1 280px; min-width:280px;">
                            <div><span class="card-title bold" style="font-weight:600;">Hospital:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('nome_hosp')) ?></span>
                            </div>
                            <div><span class="card-title bold" style="font-weight:600;">Paciente:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('nome_pac')) ?></span>
                            </div>
                            <div><span class="card-title bold" style="font-weight:600;">Parcial:</span>
                                <span class="card-title bold" style="font-weight:500;">
                                    <?= $h($val('parcial_num')) ?>
                                    <?= $type === 'create' ? $h($textoPeriodoAnterior) : '' ?>
                                </span>
                            </div>
                        </div>

                        <!-- CADASTRO CENTRAL  -->
                        <?php if ($mostrarCadastroCentral): ?>
                        <div style="flex:0 0 100%; width:100%;">
                            <!-- ALTERADO: w-100 -->
                            <div id="cadastro-central-wrapper" class="w-100 border rounded-3 p-3 mb-3"
                                style="border:2px solid #0d6efd;background:#f8fbff;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-people-fill me-2"></i>
                                    <strong class="text-primary" style="font-size:1rem;">Cadastro Central</strong>
                                </div>

                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-lg-2">
                                        <label for="cadastro_central_cap" class="form-label">Ativar</label>
                                        <select class="form-control form-select-sm" id="cadastro_central_cap"
                                            name="cadastro_central_cap">
                                            <option value="n" <?= $cadastroCentralDefault === 'n' ? 'selected' : '' ?>>
                                                Não</option>
                                            <option value="s" <?= $cadastroCentralDefault === 's' ? 'selected' : '' ?>>
                                                Sim</option>
                                        </select>
                                    </div>

                                    <!-- Médico -->
                                    <div id="box-cadcentral-med" class="col-12 col-lg-3">
                                        <label class="form-label" for="cad_central_med_id">Médico (a) </label>
                                        <select class="form-control form-select-sm" id="cad_central_med_id"
                                            name="fk_id_aud_med">
                                            <option value="">Selecione</option>
                                            <?php foreach ($usuariosAtivos as $u): if ($isMed($u['cargo_user'] ?? '')):
                                                        $id = (int)($u['id_usuario'] ?? 0);
                                                        $nome = (string)($u['usuario_user'] ?? '');
                                                        $sel = ($id === $medSelecionado) ? 'selected' : ''; ?>
                                            <option value="<?= $id ?>" <?= $sel ?>>
                                                <?= $h($nome) ?></option>
                                            <?php endif;
                                                endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Enfermeiro -->
                                    <div id="box-cadcentral-enf" class="col-12 col-lg-3">
                                        <label class="form-label" for="cad_central_enf_id">Enfermeiro(a) </label>
                                        <select class="form-control form-select-sm" id="cad_central_enf_id"
                                            name="fk_id_aud_enf">
                                            <option value="">Selecione</option>
                                            <?php foreach ($usuariosAtivos as $u): if ($isEnf($u['cargo_user'] ?? '')):
                                                        $id = (int)($u['id_usuario'] ?? 0);
                                                        $nome = (string)($u['usuario_user'] ?? '');

                                                        $sel = ($id === $enfSelecionado) ? 'selected' : ''; ?>
                                            <option value="<?= $id ?>" <?= $sel ?>>
                                                <?= $h($nome) ?></option>
                                            <?php endif;
                                                endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- Administrador -->
                                    <div id="box-cadcentral-enf" class="col-12 col-lg-3">
                                        <label class="form-label" for="cad_central_enf_id">Administrativo (a) </label>
                                        <select class="form-control form-select-sm" id="cad_central_adm_id">
                                            <option value="">Selecione</option>
                                            <?php
                                                $admSelecionado = (int)($val('fk_id_aud_adm') ?? 0);
                                                foreach ($usuariosAdm as $u):
                                                    $id   = (int)($u['id_usuario'] ?? 0);
                                                    $nome = (string)($u['usuario_user'] ?? '');
                                                    $sel = ($id === $admSelecionado) ? 'selected' : ''; ?>
                                            <option value="<?= $id ?>" <?= $sel ?>>
                                                <?= $h($nome) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>

                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- /CADASTRO CENTRAL -->
                        <?php endif; ?>


                    </div>

                    <!-- badges à direita -->
                    <div class="d-flex ms-auto">
                        <?php if (($val('med_check') ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle"
                            style="font-size:1.1rem;font-weight:600;color:#004E56;margin-right:10px;">Auditado
                            Médico</span>
                        <?php endif; ?>
                        <?php if (($val('enfer_check') ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle"
                            style="font-size:1.1rem;font-weight:600;color:#EA8037;">Auditado Enfermeiro</span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <hr>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_apresentado_capeante">Valor Apresentado</label>
                    <input type="text" class="form-control dinheiro" id="valor_apresentado_capeante"
                        name="valor_apresentado_capeante"
                        value="<?= is_numeric($val('valor_apresentado_capeante')) ? number_format((float)$val('valor_apresentado_capeante'), 2, ',', '.') : '' ?>"
                        required>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label for="data_inicial_capeante">Data Inicial</label>
                    <input type="date" class="form-control" id="data_inicial_capeante" name="data_inicial_capeante"
                        value="<?= $h($val('data_inicial_capeante') ?: $val('data_intern_int')) ?>" required>
                    <div class="invalid-feedback notif1">Data inicial inválida.</div>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label for="data_final_capeante">Data Final</label>
                    <input type="date" class="form-control" id="data_final_capeante" name="data_final_capeante"
                        value="<?= $h($val('data_final_capeante')) ?>">
                    <div class="invalid-feedback notif2">Data final inválida.</div>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3 mb-2">
                    <label for="lote_cap">Lote</label>
                    <input type="text" class="form-control" id="lote_cap" name="lote_cap"
                        value="<?= $h($val('lote_cap')) ?>">
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label for="diarias_capeante">Diárias</label>
                    <input readonly type="text" class="form-control" id="diarias_capeante" name="diarias_capeante"
                        value="<?= $h($val('diarias_capeante')) ?>">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="data_fech_capeante">Data Fechamento</label>
                    <input type="date" class="form-control" id="data_fech_capeante" name="data_fech_capeante"
                        value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_glosa_enf">Glosa Enfermagem</label>
                    <input type="text" class="dinheiro_total form-control" id="valor_glosa_enf" name="valor_glosa_enf"
                        value="<?= is_numeric($val('valor_glosa_enf')) ? number_format((float)$val('valor_glosa_enf'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                    <p class="oculto mensagem_error" id="err_valor_glosa_enf">Digite um número!</p>
                    <div class="invalid-feedback notif3">Glosa maior que o valor total.</div>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_glosa_med">Glosa Médica</label>
                    <input type="text" class="form-control dinheiro_total" placeholder="R$0,00" id="valor_glosa_med"
                        name="valor_glosa_med"
                        value="<?= is_numeric($val('valor_glosa_med')) ? number_format((float)$val('valor_glosa_med'), 2, ',', '.') : '' ?>">
                    <div class="invalid-feedback notif4">Glosa maior que o valor total.</div>
                </div>
            </div>

            <hr>
            <button type="button" id="btn-next-1" class="btn btn-primary" onclick="nextStep(2)">Próximo <i
                    class="fas fa-arrow-right"></i></button>
        </div>

        <!-- Step 2 -->
        <div id="step-2" class="step" style="display:none;">
            <h3>Passo 2: Valores e Glosas</h3>
            <br>
            <div class="form-group row">
                <!-- também ajustado para consistência -->
                <div id="view-contact-container" class="container-fluid d-flex align-items-start flex-wrap">
                    <div class="d-flex" style="flex-grow:1; gap:20px;">
                        <div class="d-flex flex-column" style="flex-grow:1;">
                            <div><span class="card-title bold" style="font-weight:600;">Código Capeante:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('id_capeante')) ?></span>
                            </div>
                        </div>
                        <div class="d-flex flex-column" style="flex-grow:1;">
                            <div><span class="card-title bold" style="font-weight:600;">Paciente:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('nome_pac')) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex ms-auto">
                        <?php if (($val('med_check') ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle"
                            style="font-size:1.1rem;font-weight:600;color:#004E56;margin-right:10px;">Auditado
                            Médico</span>
                        <?php endif; ?>
                        <?php if (($val('enfer_check') ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle"
                            style="font-size:1.1rem;font-weight:600;color:#EA8037;">Auditado Enfermeiro</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label style="font-size:15px">Total de Valores:</label><label id="diff_valor"
                        style="color:#a83232;margin-left:6px;font-size:15px"></label>
                    <i style="color:green;margin-left:1px;font-size:1.2em" id="nodiff_valor" class="fas fa-check"></i>
                    <p id="total-valores" style="font-weight:bold;">R$ 0,00</p>
                </div>
                <div class="form-group col-md-6">
                    <label style="font-size:15px">Total de Glosas:</label><label id="diff_valor_glosa"
                        style="color:#a83232;margin-left:6px;font-size:15px"></label>
                    <i style="color:green;margin-left:1px;font-size:1.2em" id="nodiff_valor_glosa"
                        class="fas fa-check"></i>
                    <p id="total-glosas" style="font-weight:bold;">R$ 0,00</p>
                </div>
            </div>

            <!-- valores/glosas -->
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_diarias">Valor Diárias</label>
                    <input type="text" class="form-control dinheiro" id="valor_diarias" name="valor_diarias"
                        value="<?= is_numeric($val('valor_diarias')) ? number_format((float)$val('valor_diarias'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_diarias">Glosa Diárias</label>
                    <input type="text" class="form-control dinheiro" id="glosa_diarias" name="glosa_diaria"
                        value="<?= is_numeric($val('glosa_diaria')) ? number_format((float)$val('glosa_diaria'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_oxig">Valor Oxigenioterapia</label>
                    <input type="text" class="form-control dinheiro" id="valor_oxig" name="valor_oxig"
                        value="<?= is_numeric($val('valor_oxig')) ? number_format((float)$val('valor_oxig'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_oxig">Glosa Oxigenioterapia</label>
                    <input type="text" class="form-control dinheiro" id="glosa_oxig" name="glosa_oxig"
                        value="<?= is_numeric($val('glosa_oxig')) ? number_format((float)$val('glosa_oxig'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_taxa">Valor Taxas</label>
                    <input type="text" class="form-control dinheiro" id="valor_taxa" name="valor_taxa"
                        value="<?= is_numeric($val('valor_taxa')) ? number_format((float)$val('valor_taxa'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_taxas">Glosa Taxas</label>
                    <input type="text" class="form-control dinheiro" id="glosa_taxas" name="glosa_taxas"
                        value="<?= is_numeric($val('glosa_taxas')) ? number_format((float)$val('glosa_taxas'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_matmed">Valor MatMed</label>
                    <input type="text" class="form-control dinheiro" id="valor_matmed" name="valor_matmed"
                        value="<?= is_numeric($val('valor_matmed')) ? number_format((float)$val('valor_matmed'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_matmed">Glosa MatMed</label>
                    <input type="text" class="form-control dinheiro" id="glosa_matmed" name="glosa_matmed"
                        value="<?= is_numeric($val('glosa_matmed')) ? number_format((float)$val('glosa_matmed'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_sadt">Valor SADT</label>
                    <input type="text" class="form-control dinheiro" id="valor_sadt" name="valor_sadt"
                        value="<?= is_numeric($val('valor_sadt')) ? number_format((float)$val('valor_sadt'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_sadt">Glosa SADT</label>
                    <input type="text" class="form-control dinheiro" id="glosa_sadt" name="glosa_sadt"
                        value="<?= is_numeric($val('glosa_sadt')) ? number_format((float)$val('glosa_sadt'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_honorarios">Valor Honorários</label>
                    <input type="text" class="form-control dinheiro" id="valor_honorarios" name="valor_honorarios"
                        value="<?= is_numeric($val('valor_honorarios')) ? number_format((float)$val('valor_honorarios'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_honorarios">Glosa Honorários</label>
                    <input type="text" class="form-control dinheiro" id="glosa_honorarios" name="glosa_honorarios"
                        value="<?= is_numeric($val('glosa_honorarios')) ? number_format((float)$val('glosa_honorarios'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="valor_opme">Valor OPME</label>
                    <input type="text" class="form-control dinheiro" id="valor_opme" name="valor_opme"
                        value="<?= is_numeric($val('valor_opme')) ? number_format((float)$val('valor_opme'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="glosa_opme">Glosa OPME</label>
                    <input type="text" class="form-control dinheiro" id="glosa_opme" name="glosa_opme"
                        value="<?= is_numeric($val('glosa_opme')) ? number_format((float)$val('glosa_opme'), 2, ',', '.') : '' ?>"
                        placeholder="R$0,00">
                </div>
            </div>

            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="fas fa-arrow-left"></i>
                Voltar</button>
            <button type="button" id="btn-next-2" class="btn btn-primary" onclick="nextStep(3)">Próximo <i
                    class="fas fa-arrow-right"></i></button>
        </div>

        <!-- Step 3 -->
        <div id="step-3" class="step" style="display:none;">
            <h3>Passo 3: Informações Adicionais</h3>
            <br>
            <div class="form-group row">
                <!-- também ajustado -->
                <div id="view-contact-container" class="container-fluid d-flex align-items-start flex-wrap">
                    <div class="d-flex" style="flex-grow:1; gap:20px;">
                        <div class="d-flex flex-column" style="flex-grow:1;">
                            <div>
                                <span class="card-title bold" style="font-weight:600;">Código Capeante:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('id_capeante')) ?></span>
                            </div>
                        </div>
                        <div class="d-flex flex-column" style="flex-grow:1;">
                            <div>
                                <span class="card-title bold" style="font-weight:600;">Paciente:</span>
                                <span class="card-title bold"
                                    style="font-weight:500;"><?= $h($val('nome_pac')) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex ms-auto">
                        <?php if (($val('med_check') ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle"
                            style="font-size:1.1rem;font-weight:600;color:#004E56;margin-right:10px;">Auditado
                            Médico</span>
                        <?php endif; ?>
                        <?php if (($val('enfer_check') ?? 'n') === 's'): ?>
                        <span class="bi bi-check-circle"
                            style="font-size:1.1rem;font-weight:600;color:#EA8037;">Auditado Enfermeiro</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3 mb-3">
                    <label for="pacote">Pacote</label>
                    <select class="form-control" id="pacote" name="pacote">
                        <?php $pacoteVal = ($val('pacote') ?? 'n'); ?>
                        <option value="n" <?= $pacoteVal === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $pacoteVal === 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label for="parcial_capeante">Parcial</label>
                    <select class="form-control" id="parcial_capeante" name="parcial_capeante">
                        <option value="n" <?= $parcialDefault === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $parcialDefault === 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>

                <?php $parcialAtivo = ($parcialDefault === 's'); ?>
                <div class="form-group col-md-3 mb-3" id="wrap_parcial_num"
                    style="<?= $parcialAtivo ? '' : 'display:none;' ?>">
                    <label for="parcial_num">Número Parcial</label>
                    <input type="number" class="form-control" id="parcial_num" name="parcial_num"
                        value="<?= $h($val('parcial_num')) ?>" <?= $parcialAtivo ? '' : 'disabled' ?>>
                </div>
            </div>

            <div class="row">
                <!-- Senha Finalizada padrão N -->
                <div class="form-group col-md-6 mb-3">
                    <label for="senha_finalizada">Senha Finalizada</label>
                    <?php $senhaVal = ($val('senha_finalizada') ?? 'n'); ?>
                    <select class="form-control" id="senha_finalizada" name="senha_finalizada">
                        <option value="n" <?= $senhaVal === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $senhaVal === 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>

                <!-- Encerrado padrão N -->
                <div class="form-group col-md-6 mb-3">
                    <label for="encerrado_cap">Capeante Encerrado</label>
                    <?php $encVal = ($val('encerrado_cap') ?? 'n'); ?>
                    <select class="form-control" id="encerrado_cap" name="encerrado_cap">
                        <option value="n" <?= $encVal === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $encVal === 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div id="div_val_desconto" class="form-group col-md-6 mb-3r">
                    <label for="desconto_valor_cap">Valor Desconto (em %)</label>
                    <input type="number" class="form-control" id="desconto_valor_cap" name="desconto_valor_cap"
                        value="<?= $h($val('desconto_valor_cap')) ?>">
                </div>

                <div class="form-group col-md-6 mb-3 d-flex justify-content-start align-items-center">
                    <div class="text-right me-4">
                        <label>Valor Apresentado:</label>
                        <p id="total-apresentado" style="font-weight:bold;">R$ 0,00</p>
                    </div>
                    <div class="text-right me-4">
                        <label>Total de Glosas:</label>
                        <p id="total-glosas-final" style="font-weight:bold;">R$ 0,00</p>
                    </div>
                    <div class="text-right me-4">
                        <label>Valor Final:</label>
                        <p id="total-final" style="font-weight:bold;">R$ 0,00</p>
                    </div>
                    <div class="text-right me-4">
                        <label>Com Desconto:</label>
                        <p id="total-valores-final-desconto" style="font-weight:bold;">R$ 0,00</p>
                    </div>
                    <div class="text-right me-4">
                        <input id="checkbox_imprimir" name="checkbox_imprimir" value="1"
                            style="width:15px;height:15px;background-color:#f1f1f1;border-radius:6px;margin-top:10px;margin-left:10px;"
                            type="checkbox" class="material-checkbox">
                        <span class="checkmark">Imprimir Capeante</span>
                    </div>
                </div>
            </div>

            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep(2)"><i class="fas fa-arrow-left"></i>
                Voltar</button>
            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Concluir</button>

            <button type="button" class="btn btn-outline-primary ms-2"
                onclick="baixarPDF(<?= $hi($val('id_capeante')) ?>, <?= $hi($val('id_internacao') ?: $id_internacao) ?>)">
                <i class="bi bi-download"></i> Salvar PDF
            </button>

            <button id="btnEnviarEmail" type="button" class="btn btn-outline-secondary ms-2"
                onclick="enviarPDF(<?= $hi($val('id_capeante')) ?>, <?= $hi($val('id_internacao') ?: $id_internacao) ?>)">
                <i class="bi bi-envelope-fill"></i> Email PDF
            </button>

            <iframe id="iframeDownload" style="display:none;"></iframe>
            <div id="mensagemStatus"
                style="display:none;margin-top:10px;padding:10px;border-radius:5px;font-weight:bold;text-align:center;">
            </div>
            <div style="width:500px;display:none" class="alert" id="alert" role="alert"></div>
        </div>

    </form>
</div>

<script>
function baixarPDF(idCapeante, idInternacao) {
    const iframe = document.getElementById("iframeDownload");
    iframe.src = `process_capeante_pdf.php?id_capeante=${idCapeante}&fk_int_capeante=${idInternacao}&save_only=1`;
    mostrarMensagem('Capeante salvo com sucesso!', '#28a745');
}

function enviarPDF(idCapeante, idInternacao) {
    fetch(`process_capeante_pdf.php?id_capeante=${idCapeante}&fk_int_capeante=${idInternacao}`);
    mostrarMensagem('Email enviado com sucesso!', 'green');
}

function mostrarMensagem(texto, cor) {
    const div = document.getElementById('mensagemStatus');
    div.textContent = texto;
    div.style.backgroundColor = cor;
    div.style.color = 'white';
    div.style.display = 'block';
    setTimeout(() => {
        div.style.display = 'none';
    }, 5000);
}

// Stepper
function nextStep(n) {
    document.getElementById('step-' + (n - 1)).style.display = 'none';
    document.getElementById('step-' + n).style.display = 'block';
    document.getElementById('progressBar').style.width = (n * 33) + '%';
    document.getElementById('progressBar').textContent = 'Etapa ' + n + ' de 3';
}

function prevStep(n) {
    document.getElementById('step-' + (n + 1)).style.display = 'none';
    document.getElementById('step-' + n).style.display = 'block';
    document.getElementById('progressBar').style.width = (n * 33) + '%';
    document.getElementById('progressBar').textContent = 'Etapa ' + n + ' de 3';
}

// Regra: Data inicial nova > fim da última parcial
(function enforceStartAfterLastPartial() {
    const inputInicio = document.getElementById('data_inicial_capeante');
    const inputFim = document.getElementById('data_final_capeante');
    const lastFinalEl = document.getElementById('last_final_date');
    const feedbackEl = document.querySelector('.invalid-feedback.notif1');
    if (!inputInicio || !lastFinalEl) return;

    const lastFinalStr = (lastFinalEl.value || '').trim();
    if (!lastFinalStr) return;

    const parseYMD = (s) => {
        const [y, m, d] = s.split('-').map(Number);
        if (!y || !m || !d) return null;
        return new Date(y, m - 1, d);
    };
    const formatYMD = (d) => {
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    };
    const addDays = (d, days) => {
        const x = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        x.setDate(x.getDate() + days);
        return x;
    };

    const lastFinal = parseYMD(lastFinalStr);
    if (!lastFinal) return;

    const minStart = addDays(lastFinal, 1);
    const minStartStr = formatYMD(minStart);
    inputInicio.setAttribute('min', minStartStr);

    const coerceIfNeeded = () => {
        const val = inputInicio.value;
        if (!val) return;
        const cur = parseYMD(val);
        if (!cur) return;
        if (cur < minStart) {
            inputInicio.classList.add('is-invalid');
            inputInicio.value = minStartStr;
            inputInicio.setCustomValidity('A data inicial deve ser posterior ao fim da última parcial.');
            if (feedbackEl) {
                feedbackEl.textContent =
                    `A data inicial deve ser a partir de ${minStartStr.replace(/-/g,'/')} (dia seguinte ao término da última parcial).`;
                feedbackEl.style.display = 'block';
            }
            setTimeout(() => {
                inputInicio.classList.remove('is-invalid');
                inputInicio.setCustomValidity('');
                if (feedbackEl) feedbackEl.style.display = '';
            }, 10);
        } else {
            inputInicio.classList.remove('is-invalid');
            inputInicio.setCustomValidity('');
            if (feedbackEl) feedbackEl.style.display = '';
        }
    };

    coerceIfNeeded();
    inputInicio.addEventListener('change', coerceIfNeeded);
    inputInicio.addEventListener('blur', coerceIfNeeded);

    if (inputFim) {
        const syncFimMin = () => {
            if (!inputInicio.value) return;
            inputFim.setAttribute('min', inputInicio.value);
        };
        syncFimMin();
        inputInicio.addEventListener('change', syncFimMin);
    }
})();

// Toggle Cadastro Central (mostra/oculta selects)
(function cadCentralToggle() {
    const sel = document.getElementById('cadastro_central_cap');
    const boxM = document.getElementById('box-cadcentral-med');
    const boxE = document.getElementById('box-cadcentral-enf');
    const boxA = document.getElementById('box-cadcentral-adm'); // opcional (pode não existir)
    const med = document.getElementById('cad_central_med_id');
    const enf = document.getElementById('cad_central_enf_id');
    const adm = document.getElementById('cad_central_adm_id'); // opcional

    if (!sel) return;

    const apply = () => {
        const on = sel.value === 's';
        [boxM, boxE, boxA].forEach(b => {
            if (b) {
                b.style.display = on ? 'block' : 'none';
                b.setAttribute('aria-hidden', on ? 'false' : 'true');
            }
        });
        if (med) med.disabled = !on;
        if (enf) enf.disabled = !on;
        if (adm) adm.disabled = !on;
    };

    sel.value = sel.value || 's';
    apply();
    sel.addEventListener('change', apply);
})();

// SINCRONISMO: nível 4/5 seta flags ("s") e FKs conforme seleção do Cadastro Central
(function syncFlagsFromCentralSelections() {
    const NIVEL = parseInt(document.getElementById('nivel_user')?.value || '0', 10);
    if (![4, 5].includes(NIVEL)) return; // só Secretaria/Diretor

    const cadAtivar = document.getElementById('cadastro_central_cap');
    const selMed = document.getElementById('cad_central_med_id');
    const selEnf = document.getElementById('cad_central_enf_id');
    const selAdm = document.getElementById('cad_central_adm_id'); // pode não existir

    const fldAdmCap = document.getElementById('adm_capeante');
    const fldEnfCap = document.getElementById('aud_enf_capeante');
    const fldMedCap = document.getElementById('aud_med_capeante');

    const fldAdmChk = document.getElementById('adm_check');
    const fldEnfChk = document.getElementById('enfer_check');
    const fldMedChk = document.getElementById('med_check');

    const fkAdm = document.getElementById('fk_id_aud_adm');
    const fkEnf = document.getElementById('fk_id_aud_enf');
    const fkMed = document.getElementById('fk_id_aud_med');

    const getHas = (el) => !!(el && el.value && String(el.value).trim() !== '');

    const apply = () => {
        const ativo = cadAtivar && cadAtivar.value === 's';
        const hasMed = ativo && getHas(selMed);
        const hasEnf = ativo && getHas(selEnf);
        const hasAdm = ativo && getHas(selAdm);

        if (fldMedCap) fldMedCap.value = hasMed ? 's' : '';
        if (fldEnfCap) fldEnfCap.value = hasEnf ? 's' : '';
        if (fldAdmCap) fldAdmCap.value = hasAdm ? 's' : '';

        if (fldMedChk) fldMedChk.value = hasMed ? 's' : '';
        if (fldEnfChk) fldEnfChk.value = hasEnf ? 's' : '';
        if (fldAdmChk) fldAdmChk.value = hasAdm ? 's' : '';

        if (fkMed) fkMed.value = hasMed ? selMed.value : '';
        if (fkEnf) fkEnf.value = hasEnf ? selEnf.value : '';
        if (fkAdm && selAdm) fkAdm.value = hasAdm ? selAdm.value : '';

        if (!ativo) { // desativou o cadastro central -> zera tudo
            if (fldMedCap) fldMedCap.value = '';
            if (fldEnfCap) fldEnfCap.value = '';
            if (fldAdmCap) fldAdmCap.value = '';
            if (fldMedChk) fldMedChk.value = '';
            if (fldEnfChk) fldEnfChk.value = '';
            if (fldAdmChk) fldAdmChk.value = '';
            if (fkMed) fkMed.value = '';
            if (fkEnf) fkEnf.value = '';
            if (fkAdm) fkAdm.value = '';
        }
    };

    ['change', 'blur'].forEach(evt => {
        if (cadAtivar) cadAtivar.addEventListener(evt, apply);
        if (selMed) selMed.addEventListener(evt, apply);
        if (selEnf) selEnf.addEventListener(evt, apply);
        if (selAdm) selAdm.addEventListener(evt, apply);
    });

    apply(); // inicial
})();
</script>

<script src="js/DataCapeante.js"></script>
<script src="js/stepper.js"></script>
<script src="js/scriptPdf.js" defer></script>
<script src="js/valoresCapeante.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>