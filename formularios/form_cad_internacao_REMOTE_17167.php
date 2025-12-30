<?php
require_once("templates/header.php");
require_once("models/message.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/acomodacao.php");
include_once("dao/acomodacaoDao.php");

include_once("dao/cidDao.php");
$cid = new cidDAO($conn, $BASE_URL);
$cids = $cid->findAll();

// ...
$id_paciente_get = filter_input(INPUT_GET, 'id_paciente', FILTER_VALIDATE_INT) ?: 0;
// ...

/* === UsuarioDAO: usar somente findMedicosEnfermeiros() === */
include_once("dao/usuarioDao.php");
$usuarioDao = new userDAO($conn, $BASE_URL);

// === Recupera o último ID de internação sem depender de método ultimoId() ===
if (!isset($ultimoReg)) {
    $ultimoReg = 0;
    try {
        $stmt = $conn->query("SELECT MAX(id_internacao) AS max_id FROM internacao");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        $ultimoReg = isset($row['max_id']) ? (int) $row['max_id'] : 0;
    } catch (Throwable $e) {
        // se der erro, mantém 0 (primeiro registro)
        $ultimoReg = 0;
    }
}

/* === DAOs auxiliares / util === */
$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$acomodacaoDao = new acomodacaoDAO($conn, $BASE_URL);
$acomodacao = $acomodacaoDao->findGeral();

/* === Sessão === */
$idSessao = $_SESSION["id_usuario"] ?? '';
$cargoSessao = $_SESSION['cargo'] ?? ($_SESSION['cargo_user'] ?? '');
$emailSessao = $_SESSION['email_user'] ?? '';
$nivelSessaoRaw = (string) ($_SESSION['nivel'] ?? '');
$nivelSessaoInt = (int) $nivelSessaoRaw;
$normCargoSessao = mb_strtolower(str_replace([' ', '-'], '_', (string) $cargoSessao), 'UTF-8');
$isMedOuEnf = in_array($normCargoSessao, ['med_auditor', 'medico_auditor', 'enf_auditor', 'enfer_auditor'], true);
$cargoSessaoLower = mb_strtolower((string) $cargoSessao, 'UTF-8');
$isDiretorSessao = (mb_stripos($cargoSessaoLower, 'diretor') !== false)
    || (mb_stripos($cargoSessaoLower, 'diretoria') !== false)
    || in_array($nivelSessaoRaw, ['1', '-1'], true);
$isCadastroCentralUser = (mb_stripos($cargoSessaoLower, 'analista') !== false);
$cadastroCentralObrigatorio = $isDiretorSessao || $isCadastroCentralUser;
$mostrarCadastroCentral = $cadastroCentralObrigatorio || !$isMedOuEnf;

$dataAtual = date('Y-m-d');
$agora = date('Y-m-d');
$agoraLanc = date('Y-m-d\TH:i');

/* ==========================================================
   CONTROLE DE ACESSO POR CARGO
   ========================================================== */
$cargo = $_SESSION['cargo'] ?? '';
$userId = (int) ($_SESSION['id_usuario'] ?? 0);
$rolesFiltrados = ['Med_auditor', 'Enf_Auditor', 'Adm'];
$aplicarFiltroUsuario = in_array($cargo, $rolesFiltrados, true) ? $userId : null;

/* === AUDITORES via UsuarioDAO::findMedicosEnfermeiros() === */
$medicosAud = [];
$enfsAud = [];
try {
    $todos = $usuarioDao->findMedicosEnfermeiros();
    if (!is_array($todos))
        $todos = [];
    foreach ($todos as $u) {
        $id = $u['id_usuario'] ?? null;
        $nome = $u['usuario_user'] ?? null;
        $email = $u['email_user'] ?? null;
        $cargo = $u['cargo_user'] ?? '';
        if (!$id)
            continue;

        $row = [
            'id_usuario' => (int) $id,
            'usuario_user' => (string) $nome,
            'email_user' => (string) $email,
            'cargo_user' => (string) $cargo,
        ];

        $c = mb_strtoupper((string) $cargo, 'UTF-8');
        if (strpos($c, 'MED') === 0)
            $medicosAud[] = $row;
        elseif (strpos($c, 'ENF') === 0)
            $enfsAud[] = $row;
    }
} catch (Throwable $e) {
    $medicosAud = $enfsAud = [];
}
if (!isset($listaHospitais) || !is_array($listaHospitais)) {
    include_once("dao/hospitalDao.php");
    include_once("dao/hospitalUserDao.php");

    $hospitalUserDao = new hospitalUserDAO($conn, $BASE_URL);
    $hospitalDao = new hospitalDAO($conn, $BASE_URL);

    $userIdSessao = (int) ($_SESSION['id_usuario'] ?? 0);
    $nivelSessaoLista = $nivelSessaoInt;

    if ($nivelSessaoLista > 3) {
        $rawHospitais = $hospitalDao->findGeral();
    } else {
        $rawHospitais = $hospitalUserDao->listarPorUsuario($userIdSessao);
    }

    $listaHospitais = [];
    if (is_array($rawHospitais)) {
        foreach ($rawHospitais as $h) {
            $id = $h['id_hospital'] ?? $h['fk_hospital_user'] ?? null;
            $nome = trim($h['nome_hosp'] ?? '');
            if ($id && $nome) {
                $listaHospitais[$id] = ['id_hospital' => $id, 'nome_hosp' => $nome];
            }
        }
        $listaHospitais = array_values($listaHospitais); // dedup
    }
} ?>
<link href="<?= $BASE_URL ?>css/style.css" rel="stylesheet">

<style>
/* z-index do dropdown do header */
.navbar .dropdown-menu {
    z-index: 1055;
}

/* Selects roxos (tabelas adicionais) */
.select-purple {
    color: #fff;
    background-color: #5e2363;
    border: 1px solid #5e2363;
}

.select-purple:focus {
    box-shadow: 0 0 0 .25rem rgba(94, 35, 99, .25);

}

.is-invalid {
    border-color: #dc3545 !important;
}

.retroativa-banner {
    width: 100%;
    margin-top: 6px;
    padding: 14px 18px;
    border-radius: 8px;
    border: 1px solid #f5c2c7;
    background: #f8d7da;
    color: #842029;
    font-size: .95rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.retroativa-banner i {
    font-size: 1.15rem;
}
.hospital-select-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    flex-wrap: nowrap;
}
.hospital-select-wrapper select {
    flex: 1 1 260px;
    min-width: 260px;
}
.hospital-tip {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 0;
    position: relative;
    flex: 0 0 auto;
}
@media (max-width: 767.98px) {
    .hospital-select-wrapper {
        flex-wrap: wrap;
    }
    .hospital-tip {
        margin-top: 6px;
    }
}
.hospital-tip button {
    border: none;
    background: #f4e9fb;
    color: #5e2363;
    border-radius: 999px;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    cursor: pointer;
    transition: transform .15s ease;
}
.hospital-tip button:disabled {
    opacity: .5;
    cursor: not-allowed;
}
.hospital-tip button:not(:disabled):hover {
    transform: translateY(-1px);
}
#myForm {
    transition: filter .2s ease, opacity .2s ease;
}
.hospital-tip-popover {
    min-width: 220px;
    background: #fff;
    border: 1px solid #e1d4ef;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: .85rem;
    color: #4a2c60;
    box-shadow: 0 6px 22px rgba(57, 15, 94, 0.12);
    display: none;
}
.hospital-tip-popover strong {
    color: #2e1146;
}
.hospital-tip-popover.show {
    display: block;
}
.hospital-uti-alert {
    display: none;
    margin-top: 10px;
    padding: 10px 14px;
    border-radius: 12px;
    background: #fff2f2;
    border: 1px solid #f4bebe;
    color: #851010;
    font-weight: 600;
    font-size: .9rem;
}
.hospital-uti-alert.show {
    display: block;
}
.patient-insight-card {
    margin-top: 6px;
    border: 1px solid #ebe2f3;
    border-radius: 14px;
    padding: 10px 14px;
    background: #faf8ff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.6);
    font-size: .85rem;
    color: #4a2c60;
}
.patient-insight-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.patient-insight-header .label {
    font-weight: 700;
    color: #5e2363;
}
.patient-insight-header a {
    font-size: .78rem;
    text-decoration: none;
    color: #5e2363;
    font-weight: 600;
}
.patient-insight-header a.disabled {
    pointer-events: none;
    opacity: .5;
}
.patient-insight-metrics {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.patient-insight-metrics div {
    background: #fff;
    border: 1px solid #e1d4ef;
    border-radius: 10px;
    padding: 6px 10px;
    font-size: .78rem;
    line-height: 1.2;
    min-width: 120px;
}
.patient-insight-metrics div strong {
    display: block;
    font-size: 1rem;
    color: #2d1144;
}
.patient-insight-inline-btn {
    border: none;
    background: #f4e9fb;
    color: #5e2363;
    border-radius: 999px;
    width: 26px;
    height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    cursor: pointer;
    transition: transform .15s ease, background .15s ease;
}
.patient-insight-inline-btn:disabled {
    opacity: .5;
    cursor: not-allowed;
}
.patient-insight-inline-btn.active {
    background: #5e2363;
    color: #fff;
}
.patient-insight-inline-btn:not(:disabled):hover {
    transform: translateY(-1px);
}
</style>



<div class="row" style="margin-top:-5px;">
    <div class="form-group row">
        <!-- <span type="text" value="<?= ($ultimoReg + 1) ?>"> -->
        <h4 class="text-center w-100"
            style="margin:-7px 10px;background-color:#5e2363;color:#fff;padding:13px 0;border-radius:.25rem;">
            Cadastrar internação
        </h4>
        <hr>

        <div class="col-12 d-flex align-items-end flex-wrap justify-content-between" style="margin-top:-20px;">
            <!-- <div class="form-group mb-0">
                <label class="control-label" for="RegInt">Id-Int</label>
                <input type="text" id="RegInt" name="RegInt" readonly class="form-control"
                    style="height: 45px; background-color: #fff; color: #000; font-weight: 500; opacity: 1; cursor: default;"
                    value="<?= ($ultimoReg + 1) ?>">
            </div> -->
            <div class="form-group mb-0" style="min-width:300px;">
                <label class="control-label" for="hospital_selected" style="margin-bottom:2px;">
                    <span style="color:red;">*</span> Hospital
                </label>
                <div class="hospital-select-wrapper">
                    <select onchange="myFunctionSelected()" class="form-select botao_select" id="hospital_selected"
                        name="hospital_selected" required
                        style="height:45px !important;border:1px solid #555;font-size:1em;background-color:#fff;color:#000;">
                        <option value="">Selecione</option>
                        <?php if (!empty($listaHospitais)): ?>
                        <?php foreach ($listaHospitais as $h): ?>
                        <option value="<?= htmlspecialchars($h['id_hospital']) ?>">
                            <?= htmlspecialchars($h['nome_hosp']) ?>
                        </option>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <option value="">Nenhum hospital disponível</option>
                        <?php endif; ?>
                    </select>
                        <div class="hospital-tip" id="hospitalTipContainer">
                            <button type="button" id="hospitalTipButton" title="Clique para mostrar/ocultar os insights" disabled>i</button>
                            <div class="hospital-tip-popover" id="hospitalTipPopover">
                            Selecione um hospital para ver negociações e internações em UTI.
                            </div>
                        </div>
                    </div>
                <div id="hospitalUtiAlert" class="hospital-uti-alert"></div>
            </div>



            <!-- Mostra nome selecionado -->
            <div class="d-flex justify-content-center align-items-center" style="flex:1">
                <div id="hospitalNomeTexto" style="
  display: none;
  align-items: center;
  justify-content: center;
  position: relative;
  max-width: 800px;
  margin-left: 450px;
  height: 60px;
  padding: 0 40px;
  border: 2px solid #640764ff;
  border-radius: 8px;
  font-size: 1.2em;
  font-weight: 600;
  color: #000;
background-image: linear-gradient(135deg, #ffffff 0%, #f5f0f8 40%, #e5cdee 90%);
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  white-space: nowrap;
">
                </div>

            </div>


            <div class="d-flex justify-content-center align-items-center" style="flex:1">
                <div id="hospitalNomeTexto"
                    style="width:100%;display:none;max-width:500px;margin-left:-500px;height:75px;padding:0 50px;border:2px solid #28a745;border-radius:8px;font-size:1.2em;font-weight:600;color:#000;background-color:#f8fff8;align-items:center;justify-content:center;text-align:center;">
                </div>
            </div>
        </div>

        <hr class="w-100">
    </div>

    <form class="visible" action="<?= $BASE_URL ?>process_internacao.php" id="myForm" method="POST"
        enctype="multipart/form-data">
        <div style="text-align:right;">
            <p style="font-size:.6em;color:red;margin-top:-20px;">* Campos Obrigatórios</p>
        </div>

        <input type="hidden" name="type" value="create">
        <input type="hidden" name="timer_int" id="timer_int" value="">
        <p style="display:none" id="proximoId_int">0</p>
        <input type="hidden" value="n" id="censo_int" name="censo_int">

        <!-- fk_usuario_int: padrão = usuário logado; Cadastro Central pode sobrescrever -->
        <input type="hidden" value="<?= htmlspecialchars($idSessao) ?>" id="fk_usuario_int" name="fk_usuario_int">

        <div class="form-group row">
            <input type="hidden" value="" name="fk_hospital_int" id="fk_hospital_int">


            <div class="form-group col-sm-3" style="margin-bottom:-5px">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="control-label mb-0" for="fk_paciente_int">
                        <span style="color:red;">*</span> Paciente
                    </label>
                    <button type="button" id="patientInsightToggle" class="patient-insight-inline-btn" title="Mostrar resumo do paciente" aria-expanded="false">i</button>
                </div>
                <select data-size="5" data-live-search="true"
                    class="form-control form-control-sm selectpicker show-tick" id="fk_paciente_int"
                    name="fk_paciente_int" required>
                    <option value="">Selecione</option>
                    <?php
                    if (!is_array($pacientes)) {
                        $pacientes = [];
                    };
                    usort($pacientes, fn($a, $b) => strcmp($a["nome_pac"], $b["nome_pac"]));
                    foreach ($pacientes as $paciente): ?>
                    <option value="<?= (int) $paciente["id_paciente"] ?>"><?= htmlspecialchars($paciente["nome_pac"]) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <a style="font-size:.8em;margin-left:7px;color:blue;" href="#"
                        onclick="openModalPac('<?= $BASE_URL ?>cad_paciente.php', 'Cadastrar paciente'); return false;">
                        <i style="color:blue;margin-bottom:7px;" class="far fa-edit edit-icon"></i> Novo Paciente
                    </a>
                </div>
                <div class="patient-insight-card" id="patientInsightCard"
                    data-hub-base="<?= $BASE_URL ?>hub_paciente.php?id_paciente=" style="display:none;">
                    <div class="patient-insight-header">
                        <span class="label">Resumo do paciente</span>
                        <a href="#" id="patientInsightHub" class="disabled" target="_blank" rel="noopener">Abrir HUB</a>
                    </div>
                    <div id="patientInsightBody">
                        Selecione um paciente para visualizar o histórico resumido.
                    </div>
                </div>

            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" for="data_intern_int"><span style="color:red;">*</span> Data
                    Internação</label>
                <input type="date" class="form-control form-control-sm" id="data_intern_int" required value=""
                    name="data_intern_int">
                <p id="erro-data-internacao" style="color:red;font-size:.7em;display:none;margin-top:5px;"></p>
            </div>

            <div class="form-group col-sm-1">
                <label class="control-label" for="hora_intern_int">Hora</label>
                <input type="time" class="form-control form-control-sm" id="hora_intern_int" value=""
                    name="hora_intern_int">
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" for="data_lancamento_int">Data lançamento</label>
                <input type="datetime-local" class="form-control form-control-sm" id="data_lancamento_int"
                    name="data_lancamento_int" value="<?= $agoraLanc ?>">
            </div>

            <div class="form-group col-sm-1">
                <label for="data_visita_int"><span style="color:red;">*</span> Data Visita</label>
                <input type="date" value='<?= $dataAtual; ?>' class="form-control form-control-sm" id="data_visita_int"
                    name="data_visita_int">
                <p id="error-message" style="color:red;display:none;font-size:.6em;"></p>
            </div>

            <div class="form-group col-sm-1">
                <label class="control-label" for="internado_int">Internado</label>
                <select class="form-control-sm form-control" id="internado_int" name="internado_int">
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>

            <div class="form-group col-sm-2" id="div-data-alta" style="display:none">
                <label class="control-label" for="data_alta_alt"> Data/Hora Alta</label>
                <input type="datetime-local" class="form-control form-control-sm" id="data_alta_alt"
                    name="data_alta_alt" step="60">
            </div>

            <div class="form-group col-sm-2" id="div-motivo-alta" style="display:none">
                <label class="control-label" for="tipo_alta_alt"> Motivo Alta</label>
                <select class="form-control" id="tipo_alta_alt" name="tipo_alta_alt">
                    <option value="">Selecione o motivo da alta</option>
                    <?php
                    if (!is_array($dados_alta)) {
                        $dados_alta = [];
                    };
                    sort($dados_alta, SORT_ASC);
                    foreach ($dados_alta as $alta): ?>
                    <option value="<?= htmlspecialchars($alta); ?>"><?= htmlspecialchars($alta); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-12 d-none" id="retroativa-container">
                <div id="retroativa-alert" class="retroativa-banner d-none">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span id="retroativa-alert-text"></span>
                </div>
            </div>

            <input type="hidden" id="retroativa_confirmada" name="retroativa_confirmada" value="0">

            <input type="hidden" id="id_internacao" readonly class="form-control" name="id_internacao"
                value="<?= $ultimoReg ?>">
            <input type="hidden" value="s" id="primeira_vis_int" name="primeira_vis_int">
            <input type="hidden" value="0" id="visita_no_int" name="visita_no_int">

            <!-- Flags do responsável (atualizadas pelo JS unificado) -->
            <input type="hidden" id="visita_enf_int" name="visita_enf_int" value="n">
            <input type="hidden" id="visita_med_int" name="visita_med_int" value="n">
            <input type="hidden" id="visita_auditor_prof_enf" name="visita_auditor_prof_enf" value="">
            <input type="hidden" id="visita_auditor_prof_med" name="visita_auditor_prof_med" value="">
            <input type="hidden" id="cad_central_obrigatorio" name="cad_central_obrigatorio"
                value="<?= $cadastroCentralObrigatorio ? '1' : '0' ?>">
        </div>

        <!-- ===== CADASTRO CENTRAL (só aparece se NÃO for med/enf) ===== -->
        <?php if ($mostrarCadastroCentral): ?>
        <div id="cadastro-central-wrapper" class="form-group row"
            style="margin-top:8px;display:block !important;border:2px dashed #8a2be2;padding:10px;border-radius:8px;">
            <div class="form-group col-sm-12" style="margin-bottom:6px;">
                <span style="font-weight:700;color:#5e2363;">Cadastro Central ativo</span>
                <?php if ($cadastroCentralObrigatorio): ?>
                <small style="margin-left:8px;color:#b02a37;font-weight:600;">Cadastro central obrigatório: selecione o tipo e o responsável.</small>
                <?php else: ?>
                <small style="margin-left:8px;color:#666;">(opcional: escolha o tipo e o responsável)</small>
                <?php endif; ?>
            </div>

            <div class="form-group row align-items-end">
                <div class="form-group col-sm-3">
                    <label class="control-label" for="resp_tipo">Responsável pela visita</label>
                    <select id="resp_tipo" class="form-control form-control-sm">
                        <option value="">(sem seleção)</option>
                        <option value="med">Médico auditor</option>
                        <option value="enf">Enfermeiro auditor</option>
                    </select>
                </div>

                <div class="form-group col-sm-4 d-none" id="box_resp_med">
                    <label class="control-label" for="resp_med_id">Selecionar médico</label>
                    <select id="resp_med_id" class="form-control form-control-sm selectpicker" data-live-search="true"
                        data-size="5" title="Selecione">
                        <option value="">Selecione</option>
                        <?php foreach ($medicosAud as $m): ?>
                        <option value="<?= (int) $m['id_usuario'] ?>"
                            data-email="<?= htmlspecialchars($m['email_user'] ?? '') ?>">
                            <?= htmlspecialchars($m['usuario_user'] ?? ('#' . $m['id_usuario'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-sm-5 d-none" id="box_resp_enf">
                    <label class="control-label" for="resp_enf_id">Selecionar enfermeiro</label>
                    <select id="resp_enf_id" class="form-control form-control-sm selectpicker" data-live-search="true"
                        data-size="5" title="Selecione">
                        <option value="">Selecione</option>
                        <?php foreach ($enfsAud as $e): ?>
                        <option value="<?= (int) $e['id_usuario'] ?>"
                            data-email="<?= htmlspecialchars($e['email_user'] ?? '') ?>">
                            <?= htmlspecialchars($e['usuario_user'] ?? ('#' . $e['id_usuario'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- ===== /CADASTRO CENTRAL ===== -->

        <div class="row">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomodacao_int">Acomodação</label>
                <select class="form-control-sm form-control" id="acomodacao_int" name="acomodacao_int">
                    <option value="">Selecione</option>
                    <?php
                    $dados_acomodacao = is_array($dados_acomodacao ?? null) ? $dados_acomodacao : [];
                    sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd): ?>
                    <option value="<?= htmlspecialchars($acomd) ?>"><?= htmlspecialchars($acomd) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="especialidade_int">Especialidade</label>
                <input list="especialidade-options" class="form-control-sm form-control" id="especialidade_int"
                    name="especialidade_int" placeholder="Selecione ou digite">
                <datalist id="especialidade-options">
                    <?php
                    if (!is_array($dados_especialidade)) {
                        $dados_especialidade = [];
                    };
                    sort($dados_especialidade, SORT_ASC);
                    foreach ($dados_especialidade as $especial): ?>
                    <option value="<?= htmlspecialchars($especial) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-group col-sm-3">
                <label for="titular_int">Médico</label>
                <input type="text" maxlength="100" class="form-control form-control-sm" id="titular_int"
                    name="titular_int">
            </div>
            <div class="form-group col-sm-1">
                <label for="crm_int">CRM</label>
                <input type="text" maxlength="10" class="form-control form-control-sm" id="crm_int" name="crm_int">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="modo_internacao_int">Modo Admissão</label>
                <select class="form-control-sm form-control" id="modo_internacao_int" name="modo_internacao_int">
                    <option value="">Selecione</option>
                    <?php
                    if (!is_array($modo_internacao)) {
                        $modo_internacao = [];
                    };
                    sort($modo_internacao, SORT_ASC);
                    foreach ($modo_internacao as $modo):  ?>
                    <option value="<?= htmlspecialchars($modo) ?>"><?= htmlspecialchars($modo) ?></option>
                    <?php endforeach; ?>
                </select>

            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tipo_admissao_int">Tipo Internação</label>
                <select class="form-control-sm form-control" id="tipo_admissao_int" name="tipo_admissao_int">
                    <option value="">Selecione</option>
                    <option value="Eletiva">Eletiva</option>
                    <option value="Urgência">Urgência</option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <div style="display:none;" id="div_int_pertinente_int" class="form-group col-sm-2">
                <label class="control-label" for="int_pertinente_int"><span style="color:red;">*</span> Internação
                    pertinente?</label>
                <select class="form-control-sm form-control" id="int_pertinente_int" name="int_pertinente_int">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <div id="div_rel_pertinente_int" style="display:none;" class="form-group col-sm-8">
                <label for="rel_pertinente_int">Justifique não pertinência</label>
                <textarea data-saude-autocomplete="true" style="resize:none" rows="3" class="form-control"
                    id="rel_pertinente_int" name="rel_pertinente_int"></textarea>
            </div>
        </div>

        <div class="form-group row">
            <!-- <div class="form-group col-sm-3">
                <label class="control-label" for="fk_patologia_int">Patologia</label>
                <select class="form-control-sm form-control selectpicker show-tick" data-size="5"
                    data-live-search="true" id="fk_patologia_int" name="fk_patologia_int">
                    <option value="">Selecione</option>
                    <?php
                    if (!is_array($patologias)) {
                        $patologias = [];
                    };
                    usort($patologias, fn($a, $b) => strcmp($a["patologia_pat"], $b["patologia_pat"]));
                    foreach ($patologias as $patologia): ?>
                    <option value="<?= (int) $patologia["id_patologia"] ?>">
                        <?= htmlspecialchars($patologia["patologia_pat"]) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div> -->
            <div class="form-group col-sm-3">
                <label class="control-label" for="fk_cid_int">CID</label>
                <select class="form-control selectpicker show-tick" data-size="5" id="fk_cid_int" name="fk_cid_int"
                    data-live-search="true">
                    <option value="">Selecione o CID</option>
                    <?php foreach ($cids as $cid): ?>
                    <option value="<?= $cid["id_cid"] ?>">
                        <?= $cid['cat'] . " - " . $cid["descricao"] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" for="grupo_patologia_int">Grupo Patologia</label>
                <select class="form-control-sm form-control" id="grupo_patologia_int" name="grupo_patologia_int">
                    <option value="">Selecione</option>
                    <?php foreach ($dados_grupo_pat as $grupo): ?>
                    <option value="<?= htmlspecialchars($grupo) ?>"><?= htmlspecialchars($grupo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-sm-1">
                <label class="control-label" for="origem_int">Origem</label>
                <select class="form-control-sm form-control" id="origem_int" name="origem_int">
                    <option value="">Selecione</option>
                    <?php foreach ($origem as $origens): ?>
                    <option value="<?= htmlspecialchars($origens) ?>"><?= htmlspecialchars($origens) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label for="senha_int">Senha</label>
                <input type="text" maxlength="20" class="form-control form-control-sm" id="senha_int" name="senha_int">
            </div>
            <div class="form-group col-sm-2">
                <label for="num_atendimento_int">Num. Atendimento</label>
                <input type="text" maxlength="20" class="form-control form-control-sm" id="num_atendimento_int"
                    name="num_atendimento_int">
            </div>

            <!-- <div class="form-group col-sm-2">
                <label class="control-label" for="fk_patologia2">Antecedente</label>
                <select class="form-control-sm form-control selectpicker show-tick" data-size="5"
                    data-live-search="true" id="fk_patologia2" name="fk_patologia2[]" multiple title="Selecione">
                    <?php
                    if (!is_array($antecedentes)) {
                        $antecedentes = [];
                    };
                    usort($antecedentes, fn($a, $b) => strcmp($a["antecedente_ant"], $b["antecedente_ant"]));
                    foreach ($antecedentes as $antecedente): ?>
                    <option value="<?= (int) $antecedente["id_antecedente"] ?>">
                        <?= htmlspecialchars($antecedente["antecedente_ant"]) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div> -->
            <!-- <input type="hidden" value="" id="json-antec" name="json-antec"> -->
        </div>

        <div><br></div>

        <div class="form-group" style="margin-left:0px; margin-top:-15px">
            <div>
                <label for="rel_int">Relatório de Auditoria</label>
                <textarea data-saude-autocomplete="true" maxlength="5000" style="resize:none" rows="2"
                    onclick="aumentarText('rel_int')" class="form-control" id="rel_int" name="rel_int"></textarea>
            </div>

            <!-- Chat Widget -->
            <!-- <div id="chat-widget" style="position: fixed; bottom: 20px; right: 20px; width: 300px; z-index: 9999;">
                <div id="chat-header" style="background-color: #007bff; color: white; padding: 10px; cursor: pointer;">
                    Chat - Assistente Virtual
                </div>
                <div id="chat-body"
                    style="display: none; border: 1px solid #ccc; background: white; max-height: 400px; overflow-y: auto;">
                    <div id="chat-messages" style="padding: 10px; font-size: 0.9em;"></div>
                    <div style="padding: 10px;">
                        <input type="text" id="chat-input" placeholder="Digite sua mensagem..."
                            style="width: 100%; padding: 5px; border: 1px solid #ccc;">
                        <button id="chat-send"
                            style="margin-top: 5px; width: 100%; background-color: #007bff; color: white; border: none; padding:5px;">Enviar</button>
                    </div>
                </div>
            </div> -->

            <div style="margin-top: 10px;">
                <label for="acoes_int">Ações da Auditoria</label>
                <textarea data-saude-autocomplete="true" rows="2" style="resize:none"
                    onclick="aumentarText('acoes_int')" class="form-control" maxlength="5000" id="acoes_int"
                    name="acoes_int"></textarea>
            </div>

            <div style="margin-top: 10px;">
                <label for="programacao_int">Programação Terapêutica</label>
                <textarea data-saude-autocomplete="true" style="resize:none" maxlength="5000" rows="2"
                    onclick="aumentarText('programacao_int')" class="form-control" id="programacao_int"
                    name="programacao_int"></textarea>
            </div>

            <div><br></div>

            <style>
            .detalhes-card {
                background: #fff;
                border-radius: 22px;
                border: 1px solid #ebe1f5;
                box-shadow: 0 12px 28px rgba(45, 18, 70, .08);
                padding: 24px 26px;
                margin: 10px 0 22px;
            }

            .detalhes-card__header {
                display: flex;
                align-items: center;
                margin-bottom: 18px;
            }

            .detalhes-card__title {
                display: flex;
                align-items: center;
                margin: 0;
                color: #3a184f;
                font-weight: 600;
            }

            .detalhes-card__marker {
                width: 6px;
                height: 26px;
                border-radius: 10px;
                margin-right: 12px;
                background: linear-gradient(180deg, #a45cc4, #d28ff1);
            }
            </style>

            <div class="detalhes-card">
                <div class="detalhes-card__header">
                    <h4 class="detalhes-card__title">
                        <span class="detalhes-card__marker"></span>
                        Detalhes do relatório
                    </h4>
                </div>

                <input type="hidden" class="form-control" id="select_detalhes" name="select_detalhes">

                <div class="form-group row">
                    <div class="form-group col-sm-2" style="margin-left:10px;">
                        <label class="control-label" style="font-weight: bold;" for="relatorio-detalhado">Relatório
                            detalhado</label>
                        <select class="form-control-sm form-control" id="relatorio-detalhado" name="relatorio-detalhado"
                            style="color:white; font-weight:normal; border:1px solid #5e2363; background-color:#5e2363;">
                            <option value="">Selecione</option>
                            <option value="s">Sim</option>
                            <option value="n">Não</option>
                        </select>
                        <p id="text-detalhado"
                            style="font-size:0.7em; text-align:center; margin-top:8px; margin-left:8px">
                            Selecione este campo caso deseje detalhar a visita
                        </p>
                    </div>
                    <div class="form-group col-sm-3">
                        <input type="hidden" id="data_create_int" value='<?= $agora; ?>' name="data_create_int">
                    </div>
                </div>

                <div id="div-detalhado" class="form-group row" style="margin-left:-12px">
                    <div class="form-group row">
                        <input type="hidden" readonly id="fk_int_det" name="fk_int_det" value="<?= ($ultimoReg + 1) ?>">

                        <div class="form-group col-sm-2">
                            <label class="control-label" for="curativo_det">Curativo</label>
                            <select class="form-control-sm form-control" id="curativo_det" name="curativo_det">
                                <option value="">Selecione</option>
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="dieta_det">Tipo dieta</label>
                            <select class="form-control-sm form-control" id="dieta_det" name="dieta_det">
                                <option value="">Selecione</option>
                                <option value="Oral">Oral</option>
                                <option value="Enteral">Enteral</option>
                                <option value="NPP">NPP</option>
                                <option value="Jejum">Jejum</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="nivel_consc_det">Nível de Consciência</label>
                            <select class="form-control-sm form-control" id="nivel_consc_det" name="nivel_consc_det">
                                <option value="">Selecione</option>
                                <option value="Consciente">Consciente</option>
                                <option value="Comatoso">Comatoso</option>
                                <option value="Vigil">Vigil</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="oxig_det">Oxigênio</label>
                            <select class="form-control-sm form-control" id="oxig_det" name="oxig_det">
                                <option value="">Selecione</option>
                                <option value="Cateter">Cateter</option>
                                <option value="Mascara">Máscara</option>
                                <option value="VNI">VNI</option>
                                <option value="Alto Fluxo">Alto Fluxo</option>
                            </select>
                        </div>
                        <div id="div-oxig" class="form-group col-sm-1">
                            <label class="control-label" for="oxig_uso_det">Lts O2</label>
                            <input class="form-control-sm form-control" type="text" name="oxig_uso_det">
                        </div>

                        <div class="form-group col-sm-3">
                            <label class="control-label">Dispositivos</label>
                            <div class="d-flex flex-wrap align-items-center">
                                <div class="form-check ">
                                    <label style="margin-left:-30px" class="control-label" for="tqt_det">TQT</label>
                                    <input class="form-check-input" type="checkbox" name="tqt_det" id="tqt_det"
                                        value="TQT">
                                </div>
                                <div class="form-check">
                                    <label style="margin-left:-30px" class="control-label" for="svd_det">SVD</label>
                                    <input class="form-check-input" type="checkbox" name="svd_det" id="svd_det"
                                        value="SVD">
                                </div>
                                <div class="form-check" style="text-align: center;">
                                    <label style="margin-left:-30px" class="control-label" for="sne_det">SNE</label>
                                    <input class="form-check-input" type="checkbox" name="sne_det" id="sne_det"
                                        value="SNE">
                                </div>
                                <div class="form-check">
                                    <label style="margin-left:-30px" class="control-label" for="gtt_det">GTT</label>
                                    <input class="form-check-input" type="checkbox" name="gtt_det" id="gtt_det"
                                        value="GTT">
                                </div>
                                <div class="form-check">
                                    <label style="margin-left:-30px" class="control-label" for="dreno_det">Dreno</label>
                                    <input class="form-check-input" type="checkbox" name="dreno_det" id="dreno_det"
                                        value="Dreno">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="margin-top: -20px;">
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="hemoderivados_det">Hemoderivados</label>
                            <select class="form-control-sm form-control" id="hemoderivados_det"
                                name="hemoderivados_det">
                                <option value="">Selecione</option>
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="dialise_det">Diálise</label>
                            <select class="form-control-sm form-control" id="dialise_det" name="dialise_det">
                                <option value="">Selecione</option>
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="oxigenio_hiperbarica_det">Oxigenioterapia
                                Hiperbárica</label>
                            <select class="form-control-sm form-control" id="oxigenio_hiperbarica_det"
                                name="oxigenio_hiperbarica_det">
                                <option value="">Selecione</option>
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-1">
                            <label class="control-label" for="qt_det">QT</label>
                            <select class="form-control-sm form-control" id="qt_det" name="qt_det">
                                <option value=""></option>
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-1">
                            <label class="control-label" for="rt_det">RT</label>
                            <select class="form-control-sm form-control" id="rt_det" name="rt_det">
                                <option value=""></option>
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-1">
                            <label class="control-label" for="acamado_det">Acamado</label>
                            <select class="form-control-sm form-control" id="acamado_det" name="acamado_det">
                                <option value=""></option>
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-1">
                            <label class="control-label" for="atb_det">Antibiótico</label>
                            <select class="form-control-sm form-control" id="atb_det" name="atb_det">
                                <option value=""></option>
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div id="atb" class="form-group col-sm-3">
                            <label class="control-label" for="atb_uso_det">Antibiótico em uso</label>
                            <input class="form-control" type="text" name="atb_uso_det">
                        </div>
                        <div class="form-group col-sm-1">
                            <label class="control-label" for="medic_alto_custo_det">Medicação</label>
                            <select class="form-control-sm form-control" id="medic_alto_custo_det"
                                name="medic_alto_custo_det">
                                <option value="n">Não</option>
                                <option value="s">Sim</option>
                            </select>
                        </div>
                        <div id="medicacaoDet" class="form-group col-sm-3">
                            <label class="control-label" for="qual_medicamento_det">Medicação alto custo</label>
                            <input class="form-control-sm form-control" type="text" name="qual_medicamento_det">
                        </div>
                        <div>
                            <label for="exames_det">Exames relevantes</label>
                            <textarea data-saude-autocomplete="true" style="resize:none" maxlength="5000" rows="3"
                                onclick="aumentarText('exames_det')" onblur="reduzirText('exames_det', 3)"
                                class="form-control" id="exames_det" name="exames_det"></textarea>
                        </div>
                        <div>
                            <label for="oportunidades_det">Oportunidades</label>
                            <textarea data-saude-autocomplete="true" style="resize:none" maxlength="5000" rows="2"
                                onclick="aumentarText('oportunidades_det')" class="form-control" id="oportunidades_det"
                                onblur="reduzirText('oportunidades_det', 3)" name="oportunidades_det"></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="liminar_det">Possui Liminar?</label>
                            <select class="form-control-sm form-control" id="liminar_det" name="liminar_det">
                                <option value="n">Não</option>
                                <option value="s">Sim</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="paliativos_det">Está em Cuidados Paliativos?</label>
                            <select class="form-control-sm form-control" id="paliativos_det" name="paliativos_det">
                                <option value="n">Não</option>
                                <option value="s">Sim</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="parto_det">Parto</label>
                            <select class="form-control-sm form-control" id="parto_det" name="parto_det">
                                <option value="n">Não</option>
                                <option value="s">Sim</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="braden_det">Escala de Braden</label>
                            <select class="form-control-sm form-control" id="braden_det" name="braden_det">
                                <option value=""></option>
                                <option value="alto">Alto</option>
                                <option value="moderado">Moderado</option>
                                <option value="baixo">Baixo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .tabelas-adicionais-card {
            background: #fff;
            border-radius: 22px;
            border: 1px solid #ebe1f5;
            box-shadow: 0 12px 28px rgba(45, 18, 70, .08);
            padding: 22px 24px;
            margin-top: 25px;
        }

        .tabelas-adicionais-card__header {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }

        .tabelas-adicionais-card__title {
            display: flex;
            align-items: center;
            margin: 0;
            color: #3a184f;
            font-weight: 600;
        }

        .tabelas-adicionais-card__marker {
            width: 6px;
            height: 26px;
            border-radius: 10px;
            margin-right: 12px;
            background: linear-gradient(180deg, #7b3f99, #b279d0);
        }
        </style>

        <div class="tabelas-adicionais-card">
            <div class="tabelas-adicionais-card__header">
                <h4 class="tabelas-adicionais-card__title">
                    <span class="tabelas-adicionais-card__marker"></span>
                    Tabelas Adicionais
                </h4>
            </div>

            <div class="form-group row d-flex justify-content-center align-items-end" style="gap: 15px;">
                <?php if ($cargoSessao === 'Med_auditor' || $cargoSessao === 'Diretoria') { ?>
                <div class="form-group col-sm-2">
                    <label class="control-label" style="font-weight: bold;" for="select_tuss">Tuss</label>
                    <select class="form-control-sm form-control select-purple" id="select_tuss" name="select_tuss">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" style="font-weight: bold;" for="select_prorrog">Prorrogação</label>
                    <select class="form-control-sm form-control select-purple" id="select_prorrog"
                        name="select_prorrog">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <?php } ?>

                <div class="form-group col-sm-2">
                    <label class="control-label" style="font-weight: bold;" for="select_gestao">Gestão</label>
                    <select class="form-control-sm form-control select-purple" id="select_gestao" name="select_gestao">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>

                <div class="form-group col-sm-2">
                    <label class="control-label" style="font-weight: bold;" for="select_uti">UTI</label>
                    <select class="form-control-sm form-control select-purple" id="select_uti" name="select_uti">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>

                <?php if ($cargoSessao === 'Med_auditor' || $cargoSessao === 'Diretoria') { ?>
                <div class="form-group col-sm-2">
                    <label class="control-label" style="font-weight: bold;" for="select_negoc">Negociações</label>
                    <select class="form-control-sm form-control select-purple" id="select_negoc" name="select_negoc">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <?php } ?>
            </div>
        </div>

        <input type="hidden" class="form-control" value="<?= ($ultimoReg + 1) ?>" id="fk_int_capeante"
            name="fk_int_capeante">
        <input type="hidden" class="form-control" value="n" id="encerrado_cap" name="encerrado_cap">
        <input type="hidden" class="form-control" value="s" id="aberto_cap" name="aberto_cap">
        <input type="hidden" class="form-control" value="n" id="em_auditoria_cap" name="em_auditoria_cap">
        <input type="hidden" class="form-control" value="n" id="senha_finalizada" name="senha_finalizada">

        <?php include_once('formularios/form_cad_internacao_tuss.php'); ?>
        <?php include_once('formularios/form_cad_internacao_gestao.php'); ?>
        <?php include_once('formularios/form_cad_internacao_uti.php'); ?>
        <?php include_once('formularios/form_cad_internacao_prorrog.php'); ?>
        <?php include_once('formularios/form_cad_internacao_negoc.php'); ?>

        <div class="row">
            <div class="form-group col-md-6">
                <label for="intern_files">Arquivos</label>
                <input type="file" class="form-control" name="intern_files[]" id="intern_files"
                    accept="image/png, image/jpeg" multiple>
                <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
            </div>
        </div>

        <div>
            <hr>
            <!-- ... dentro do <form id="myForm"> ... -->

            <button type="submit" class="btn btn-success btn-lg fixed-submit">
                <i class="fa-solid fa-check edit-icon" style="font-size:1rem;margin-right:8px;"></i>
                Cadastrar
            </button>


            <br><br>
            <div style="width:500px;display:none" class="alert" id="alert" role="alert"></div>
        </div>
    </form>
</div>
<!-- Modal retroativa -->
<div class="modal fade" id="modalInternacaoAtiva" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Paciente já internado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>
                    Paciente internado no <strong id="modalInternacaoHospital">—</strong> desde
                    <strong id="modalInternacaoData">—</strong>.
                </p>
                <p class="mb-0">
                    Deseja registrar uma nova internação retroativa? Ela deve ser salva já com a alta informada.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-action="cancel-retroativa">Cancelar</button>
                <button type="button" class="btn btn-primary" data-action="confirm-retroativa">Continuar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal senha duplicada -->
<div class="modal fade" id="modalSenhaDuplicada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Senha já cadastrada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p id="modalSenhaDuplicadaTexto" class="mb-0">
                    Esta senha já está vinculada a outra internação. Informe uma senha diferente.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>


<script>
window.formInternacaoConfig = Object.assign({}, window.formInternacaoConfig || {}, {
    prefillPacienteId: <?= !empty($id_paciente_get) ? (int) $id_paciente_get : 'null' ?>,
    idSessao: <?= json_encode($idSessao ?? '') ?>,
    cargoSessao: <?= json_encode($cargoSessao ?? '') ?>,
    ultimoReg: <?= (int) $ultimoReg ?>
});
</script>
<script src="<?= $BASE_URL ?>js/form_cad_internacao.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="<?= $BASE_URL ?>js/saude-autocomplete.js?v=2"></script>
