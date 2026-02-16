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
    body {
        background: linear-gradient(180deg, #f6f3ff 0%, #ede6fa 100%);
        min-height: 100vh;
    }

    /* z-index do dropdown do header */
    .navbar .dropdown-menu {
        z-index: 1055;
    }

    /* Selects roxos (tabelas adicionais) */
    .select-purple,
    .select-purple:focus {
        background-color: #5e2363 !important;
        color: #fff !important;
        border: 1px solid #5e2363 !important;
    }

    .bootstrap-select .dropdown-toggle.select-purple,
    .bootstrap-select .dropdown-toggle.select-purple .filter-option,
    .bootstrap-select .dropdown-toggle.select-purple .filter-option-inner,
    .bootstrap-select .dropdown-toggle.select-purple .filter-option-inner-inner,
    .bootstrap-select .dropdown-toggle.select-purple.btn {
        background-color: #5e2363 !important;
        color: #fff !important;
        border: 1px solid #5e2363 !important;
        font-weight: 600 !important;
        box-shadow: none !important;
    }

    .detail-select,
    .detail-select:focus {
        background-color: #5e2363 !important;
        color: #fff !important;
        border: 1px solid #5e2363 !important;
        font-weight: 700;
        box-shadow: none !important;
    }

    .detail-select option {
        background-color: #5e2363 !important;
        color: #fff !important;
        font-weight: 700;
    }

    .detail-select option {
        background-color: #fff !important;
        color: #2d114c !important;
        font-weight: 700;
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
        flex: 1 1 100%;
        min-width: 100%;
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

    .hospital-tip button:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    @media (min-width: 768px) {

        .hospital-col,
        .patient-col {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
    }

    .internacao-head-row {
        margin-left: -6px;
        margin-right: -6px;
    }

    .internacao-head-row>.form-group {
        padding-left: 6px;
        padding-right: 6px;
    }

    .internacao-head-row label {
        margin-bottom: 4px;
    }

    .hospital-col label {
        margin-bottom: 0px;
    }

    .hospital-col .d-flex.mb-1 {
        margin-bottom: 0 !important;
    }

    .hospital-col .hospital-select-wrapper {
        margin-top: -2px;
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
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .6);
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

    .patient-select-btn {
        height: 34px !important;
        padding: 6px 10px !important;
        border: 2px solid #7a1e57 !important;
        box-shadow: 0 3px 8px rgba(122, 30, 87, 0.12) !important;
    }

    .hospital-select-btn {
        height: 34px !important;
        padding: 6px 10px !important;
        border: 1px solid #555 !important;
        box-shadow: none !important;
        background-color: #fff !important;
        color: #000 !important;
    }

    .hospital-select-btn:focus,
    .hospital-select-btn:active,
    .bootstrap-select.show>.hospital-select-btn {
        border-color: #5e2363 !important;
        box-shadow: 0 0 0 0.15rem rgba(94, 35, 99, 0.15) !important;
    }

    .patient-select-btn .filter-option {
        display: flex;
        align-items: center;
    }

    .patient-select-btn:focus,
    .patient-select-btn:active,
    .bootstrap-select.show>.patient-select-btn {
        border-color: #5e2363 !important;
        box-shadow: 0 0 0 0.2rem rgba(94, 35, 99, 0.2) !important;
    }

    /* Evita o select nativo ficar clicável por baixo do selectpicker */
    #hospital_selected.selectpicker,
    #fk_paciente_int.selectpicker {
        display: none !important;
    }

    .internacao-page {
        width: 100%;
        margin: -26px 0 0;
        padding: 0 0 60px;
    }

    .internacao-page__hero {
        background: linear-gradient(135deg, #5a2f78, #a06bd4);
        color: #fff;
        border-radius: 32px;
        padding: 18px 26px;
        box-shadow: 0 25px 50px rgba(24, 0, 30, 0.35);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin: -12px 0 4px;
    }

    .internacao-page__hero h1 {
        margin: 0 0 6px;
        font-size: 1.6rem;
        letter-spacing: .02em;
        color: #fff;
    }

    .internacao-page__hero p {
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
        max-width: 480px;
    }

    .internacao-page__tag {
        background: rgba(255, 255, 255, 0.15);
        padding: 8px 18px;
        border-radius: 999px;
        font-weight: 600;
        font-size: .8rem;
    }

    .internacao-page__content {
        margin-top: 4px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .internacao-card {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.9));
        border-radius: 28px;
        padding: 18px 10px;
        border: 1px solid #ece6f2;
        box-shadow: 0 20px 50px rgba(94, 35, 99, 0.08);
        margin: 0;
        width: 100%;
    }

    .internacao-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    .internacao-card__eyebrow {
        text-transform: uppercase;
        letter-spacing: .35em;
        font-size: .65rem;
        margin: 0;
        color: #000;
    }

    .internacao-card__title {
        margin: 2px 0 0;
        font-size: 1.4rem;
        color: #000;
        font-weight: 600;
    }

    .internacao-card__tag {
        background: #f8eefc;
        color: #5e2363;
        padding: 6px 16px;
        border-radius: 999px;
        font-weight: 600;
        font-size: .8rem;
    }

    .internacao-card__body {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .internacao-card--central .internacao-card__body {
        gap: 8px;
    }

    .internacao-card--central {
        border-color: #c7aedc;
        background: #f5f5f9;
        padding: 14px 22px 10px;
    }

    .internacao-card--central .internacao-card__header {
        margin-bottom: 2px;
    }

    #cadastro-central-wrapper .form-group {
        margin-bottom: 4px;
    }

    .internacao-card--general {
        border-color: #c7aedc;
        background: #f5f5f9;
    }

    .internacao-card--fields,
    .internacao-card--notes,
    .detalhes-card,
    .tabelas-adicionais-card {
        border-color: #ebe1f5;
        border-width: 1px;
        border-style: solid;
        background: #f5f5f9;
        box-shadow: 0 12px 24px rgba(45, 18, 70, .08);
    }

    .internacao-card--fields .internacao-card__body {
        gap: 8px;
    }

    .internacao-card--fields .internacao-card__body .row,
    .internacao-card--fields .internacao-card__body .form-group.row {
        margin-top: 0;
    }

    .internacao-card--fields .form-group {
        margin-bottom: 6px;
    }

    .internacao-card--fields .form-group.row {
        margin-top: 4px;
        margin-bottom: 4px;
    }

    .internacao-card--fields .form-check {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin: 0 6px;
    }

    .internacao-card--fields .form-check label {
        margin-left: 0 !important;
        font-weight: 600;
        color: #2b1f3b;
    }

    .detalhes-card {
        border-radius: 22px;
        background: #f5f5f9;
        border: 1px solid #ebe1f5;
        box-shadow: 0 12px 24px rgba(45, 18, 70, .08);
        padding: 12px 20px 6px;
        margin: 0;
    }

    .detalhes-card__header {
        display: flex;
        align-items: center;
        margin-bottom: 18px;
    }

    .detalhes-card__controls {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 4px;
        flex-wrap: wrap;
    }

    .detalhes-card__controls .form-group {
        margin-bottom: 0;
    }

    .detalhes-card__controls #text-detalhado {
        margin: 8px 0 0;
        text-align: left;
        max-width: 260px;
    }

    #relatorio-detalhado {
        min-width: 180px;
        background-color: #fff;
        border: 1px solid #4caf50;
        color: #2d114c;
        font-weight: 700;
        box-shadow: none;
    }

    #relatorio-detalhado:focus {
        box-shadow: 0 0 0 .2rem rgba(76, 175, 80, .25);
        border-color: #2e7d32;
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

    .tabelas-adicionais-card {
        border-radius: 22px;
        background: #f5f5f9;
        border: 1px solid #ebe1f5;
        box-shadow: 0 12px 24px rgba(45, 18, 70, .08);
        padding: 12px 20px 10px;
        margin-top: 24px;
        clear: both;
    }

    .tabelas-adicionais-card .form-group {
        flex: 0 0 150px;
        max-width: 150px;
    }

    @media (max-width: 992px) {
        .tabelas-adicionais-card .form-group {
            flex: 0 0 45%;
            max-width: 100%;
        }
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

    .tabelas-selects {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 8px;
    }

    .tabelas-selects .tabelas-col {
        flex: 1 1 240px;
        max-width: 270px;
    }

    /* Placeholder mais claro em todos inputs/textarea */
    input::placeholder,
    textarea::placeholder {
        color: #c4c4c4 !important;
        opacity: 1;
    }

    /* Placeholder do search do bootstrap-select */
    .bootstrap-select .dropdown-menu .bs-searchbox input::placeholder {
        color: #c4c4c4 !important;
        opacity: 1;
    }

    /* Texto “placeholder” dos selects (bootstrap-select e nativo) */
    .bootstrap-select>.dropdown-toggle.bs-placeholder,
    .bootstrap-select>.dropdown-toggle.bs-placeholder .filter-option,
    .bootstrap-select>.dropdown-toggle.bs-placeholder .filter-option-inner-inner {
        color: #c4c4c4 !important;
        font-weight: 500;
    }

    .bootstrap-select>.dropdown-toggle.select-placeholder,
    .bootstrap-select>.dropdown-toggle.select-placeholder .filter-option,
    .bootstrap-select>.dropdown-toggle.select-placeholder .filter-option-inner-inner {
        color: #c4c4c4 !important;
        font-weight: 500;
    }

    select:required:invalid {
        color: #c4c4c4 !important;
    }

    option[value=""] {
        color: #c4c4c4;
    }

    @media (max-width: 768px) {
        .tabelas-selects .tabelas-col {
            flex: 0 0 48%;
            max-width: 48%;
        }
    }
</style>

<script>
    function triggerInternacaoAutoSave() {
        const form = document.getElementById('myForm');
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');

        // Impede salvar se houver campos obrigatórios faltando
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
            if (typeof form.reportValidity === 'function') form.reportValidity();
            return;
        }

        const restoreVisual = () => {
            form.style.filter = '';
            form.style.opacity = '';
            if (submitBtn) submitBtn.disabled = false;
        };

        if (submitBtn) submitBtn.disabled = true;
        form.style.filter = 'blur(2px)';
        form.style.opacity = '0.6';

        setTimeout(() => {
            // Se tiver jQuery/Bootstrap, dispara submit pelo jQuery (mantém handlers)
            if (typeof window.jQuery === 'function') {
                window.jQuery(form).trigger('submit');
                restoreVisual();
                return;
            }

            // Fallback sem jQuery: tenta disparar evento antes do submit
            const evt = new Event('submit', {
                cancelable: true,
                bubbles: true
            });
            const ok = form.dispatchEvent(evt);

            if (ok) form.submit();
            else restoreVisual();
        }, 150);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('myForm');
        var timerField = document.getElementById('timer_int');
        var pacienteSelect = document.getElementById('fk_paciente_int');
        var matriculaField = document.getElementById('matricula_paciente_display');
        var dataInternDt = document.getElementById('data_intern_int_dt');
        var dataIntern = document.getElementById('data_intern_int');
        var horaIntern = document.getElementById('hora_intern_int');
        var timerStart = null;
        var intervalId = null;
        var lastMatriculaMatch = '';

        function normalizeMatricula(value) {
            return (value || '').toLowerCase().replace(/[^0-9a-z]/g, '');
        }

        function selectPacienteByMatricula(value, options = {}) {
            if (!pacienteSelect) return;
            var search = normalizeMatricula(value);
            if (!search) return;
            var listOptions = Array.from(pacienteSelect.options || []);
            var match = listOptions.find(function(opt) {
                return normalizeMatricula(opt.getAttribute('data-matricula') || '') === search;
            });
            if (!match && (options.allowPartial !== false) && search.length >= 3) {
                match = listOptions.find(function(opt) {
                    return normalizeMatricula(opt.getAttribute('data-matricula') || '').includes(search);
                });
            }
            if (match && match.value) {
                pacienteSelect.value = match.value;
                if (window.jQuery && window.jQuery.fn && window.jQuery(pacienteSelect).hasClass('selectpicker')) {
                    window.jQuery(pacienteSelect).selectpicker('refresh');
                }
                handlePacienteChange();
                return true;
            }
            return false;
        }

        window.sortPacienteOptionsDesc = function() {
            var select = document.getElementById('fk_paciente_int');
            if (!select || select.options.length <= 1) return;
            var options = Array.from(select.options).slice(1);
            options.sort(function(a, b) {
                return parseInt(b.value || '0', 10) - parseInt(a.value || '0', 10);
            });
            options.forEach(function(opt) {
                select.appendChild(opt);
            });
        };

        function startTimer() {
            if (timerStart === null) {
                timerStart = Date.now();
            }
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }

        function scheduleValueWatch() {
            if (!pacienteSelect || intervalId) return;
            intervalId = setInterval(function() {
                if (pacienteSelect.value) {
                    startTimer();
                    handlePacienteChange();
                }
            }, 700);
        }

        function handlePacienteChange() {
            if (!pacienteSelect) return;
            var selectedText = pacienteSelect.options[pacienteSelect.selectedIndex]?.text?.trim() || '';
            var id = pacienteSelect.value;
            if (matriculaField) {
                var opt = pacienteSelect.options[pacienteSelect.selectedIndex];
                var matricula = opt ? (opt.getAttribute('data-matricula') || '') : '';
                matriculaField.value = id ? matricula : '';
            }
            if (id) startTimer();
            if (window.patientInsightsHelper && typeof window.patientInsightsHelper.fetch === 'function') {
                window.patientInsightsHelper.fetch(id, selectedText);
            }
        }

        if (pacienteSelect) {
            window.sortPacienteOptionsDesc();
            if (pacienteSelect.value) {
                startTimer();
                handlePacienteChange();
            } else {
                scheduleValueWatch();
            }
            pacienteSelect.addEventListener('change', handlePacienteChange);

            if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.on === 'function') {
                window.jQuery(function() {
                    window.jQuery('#fk_paciente_int').on('changed.bs.select', function() {
                        handlePacienteChange();
                    });
                });
            }
        } else {
            startTimer();
        }

        function triggerMatriculaSearch(eventSource) {
            if (!matriculaField) return;
            var value = matriculaField.value;
            if (eventSource === 'input') {
                var normalized = normalizeMatricula(value);
                if (!normalized) {
                    lastMatriculaMatch = '';
                    return;
                }
                if (normalized === lastMatriculaMatch) return;
                if (selectPacienteByMatricula(value, {
                        allowPartial: false
                    })) {
                    lastMatriculaMatch = normalized;
                }
                return;
            }
            lastMatriculaMatch = '';
            selectPacienteByMatricula(value);
        }

        if (matriculaField) {
            matriculaField.addEventListener('keydown', function(evt) {
                if (evt.key === 'Enter') {
                    evt.preventDefault();
                    triggerMatriculaSearch('enter');
                }
            });
            matriculaField.addEventListener('blur', function() {
                triggerMatriculaSearch('blur');
            });
            matriculaField.addEventListener('input', function() {
                triggerMatriculaSearch('input');
            });
        }

        ['pacienteSelecionado', 'paciente-selecionado'].forEach(function(evtName) {
            document.addEventListener(evtName, startTimer);
        });

        if (form && timerField) {
            form.addEventListener('submit', function() {
                var elapsed = 0;
                if (timerStart !== null) {
                    elapsed = Math.max(0, Math.round((Date.now() - timerStart) / 1000));
                }
                timerField.value = elapsed;
            });
        }

        function syncInternacaoHidden() {
            if (!dataInternDt || !dataIntern || !horaIntern) return;
            if (!dataInternDt.value) {
                dataIntern.value = '';
                horaIntern.value = '';
                return;
            }
            var parts = dataInternDt.value.split('T');
            dataIntern.value = parts[0] || '';
            horaIntern.value = parts[1] ? parts[1].slice(0, 5) : '';
        }

        if (dataInternDt) {
            dataInternDt.addEventListener('change', syncInternacaoHidden);
            dataInternDt.addEventListener('input', syncInternacaoHidden);
            syncInternacaoHidden();
        }
        if (form) {
            form.addEventListener('submit', syncInternacaoHidden);
        }
    });
</script>


<div class="internacao-page">
    <div class="internacao-page__hero">
        <div>
            <h1>Cadastrar internação</h1>
        </div>
        <span class="internacao-page__tag">Campos obrigatórios em destaque</span>
    </div>
    <div class="internacao-page__content">
        <form class="visible" action="<?= htmlspecialchars(rtrim($BASE_URL, '/') . '/process_internacao.php', ENT_QUOTES, 'UTF-8') ?>" id="myForm" method="POST"
            enctype="multipart/form-data">
            <div class="internacao-card internacao-card--general">
                <div class="internacao-card__header">
                    <div>
                        <p class="internacao-card__eyebrow">Dados essenciais</p>
                    </div>
                </div>
                <div class="internacao-card__body">
                    <div class="col-12 d-flex align-items-end flex-wrap justify-content-between" style="margin-top:-20px;">
                        <div class="form-group mb-0 hospital-col">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="control-label mb-0" for="hospital_selected">
                                    <span style="color:red;">*</span> Hospital
                                </label>
                            </div>
                            <div class="hospital-select-wrapper">
                                <select onchange="myFunctionSelected()"
                                    class="botao_select selectpicker show-tick selectpicker-init-hide" id="hospital_selected"
                                    name="hospital_selected" required data-live-search="true"
                                    data-live-search-placeholder="Pesquise por Hospital" data-none-selected-text="Pesquise por Hospital"
                                    data-width="100%" data-style="hospital-select-btn"
                                    style="font-size:1em;background-color:#fff;color:#000;">
                                    <option value=""></option>
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
                            </div>
                        </div>
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
                    </div>
                    <div class="form-group row internacao-head-row">
                        <input type="hidden" value="" name="fk_hospital_int" id="fk_hospital_int">

                        <div class="form-group col-sm-4 patient-col" style="margin-bottom:-5px">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="control-label mb-0" for="fk_paciente_int">
                                    <span style="color:red;">*</span> Paciente
                                </label>
                                <button type="button" id="patientInsightToggle" class="patient-insight-inline-btn"
                                    title="Mostrar resumo do paciente" aria-expanded="false">i</button>
                            </div>
                            <select data-size="5" data-live-search="true" data-live-search-placeholder="Pesquisa por nome"
                                data-style="patient-select-btn" data-width="100%"
                                data-none-selected-text="Pesquisa por nome"
                                class="form-control input-lg-fullcare selectpicker show-tick" id="fk_paciente_int"
                                name="fk_paciente_int" required>
                                <option value=""></option>
                                <?php
                                if (!is_array($pacientes)) {
                                    $pacientes = [];
                                };
                                usort($pacientes, fn($a, $b) => ((int) $b["id_paciente"]) <=> ((int) $a["id_paciente"]));
                                foreach ($pacientes as $paciente): ?>
                                    <?php
                                    $matriculaPac = trim((string) ($paciente["matricula_pac"] ?? ""));
                                    $pacienteLabel = $paciente["nome_pac"];
                                    ?>
                                    <option value="<?= (int) $paciente["id_paciente"] ?>"
                                        data-matricula="<?= htmlspecialchars($matriculaPac) ?>"
                                        data-tokens="<?= htmlspecialchars(trim((string) $paciente["nome_pac"])) ?>">
                                        <?= htmlspecialchars($pacienteLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <script>
                                (function initPacienteSelectpicker() {
                                    var tries = 0;

                                    function attempt() {
                                        if (window.jQuery && jQuery.fn && typeof jQuery.fn.selectpicker === 'function') {
                                            var $sel = jQuery('#fk_paciente_int');
                                            if ($sel.length && !$sel.data('selectpicker')) {
                                                $sel.selectpicker();
                                                $sel.selectpicker('refresh');
                                            }
                                            return;
                                        }
                                        if (++tries < 60) setTimeout(attempt, 50);
                                    }

                                    attempt();
                                })();
                            </script>
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
                                    <a href="#" id="patientInsightHub" class="disabled" target="_blank" rel="noopener">Abrir
                                        HUB</a>
                                </div>
                                <div id="patientInsightBody">
                                    Selecione um paciente para visualizar o histórico resumido.
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-6 col-sm-2">
                            <label class="control-label" for="matricula_paciente_display">Matrícula</label>
                            <input type="text" class="form-control input-lg-fullcare" id="matricula_paciente_display"
                                placeholder="Digite para pesquisar por matrícula" list="matricula_list">
                            <datalist id="matricula_list">
                                <?php foreach ($pacientes as $paciente): ?>
                                    <?php $matriculaPac = trim((string) ($paciente["matricula_pac"] ?? "")); ?>
                                    <?php if ($matriculaPac === '') continue; ?>
                                    <option value="<?= htmlspecialchars($matriculaPac) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="data_intern_int_dt"><span style="color:red;">*</span> Data
                                Internação</label>
                            <input type="datetime-local" class="form-control input-lg-fullcare" id="data_intern_int_dt" required
                                value="" name="data_intern_int_dt">
                            <input type="hidden" id="data_intern_int" name="data_intern_int" value="">
                            <input type="hidden" id="hora_intern_int" name="hora_intern_int" value="">
                            <p id="erro-data-internacao" style="color:red;font-size:.7em;display:none;margin-top:5px;"></p>
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="data_lancamento_int">Data lançamento</label>
                            <input type="datetime-local" class="form-control input-lg-fullcare" id="data_lancamento_int"
                                name="data_lancamento_int" value="<?= $agoraLanc ?>">
                        </div>

                        <div class="form-group col-sm-1">
                            <label for="data_visita_int">Data Visita</label>
                            <input type="date" value='<?= $dataAtual; ?>' class="form-control input-lg-fullcare" id="data_visita_int"
                                name="data_visita_int">
                            <p id="error-message" style="color:red;display:none;font-size:.6em;"></p>
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="internado_int">Internado</label>
                            <select class="input-lg-fullcare form-control" id="internado_int" name="internado_int">
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-2" id="div-data-alta" style="display:none">
                            <label class="control-label" for="data_alta_alt"> Data/Hora Alta</label>
                            <input type="datetime-local" class="form-control input-lg-fullcare" id="data_alta_alt"
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
                </div>
            </div>
            <input type="hidden" name="type" value="create">
            <input type="hidden" name="timer_int" id="timer_int" value="">
            <p style="display:none" id="proximoId_int">0</p>
            <input type="hidden" value="n" id="censo_int" name="censo_int">

            <!-- fk_usuario_int: padrão = usuário logado; Cadastro Central pode sobrescrever -->
            <input type="hidden" value="<?= htmlspecialchars($idSessao) ?>" id="fk_usuario_int" name="fk_usuario_int">

            <!-- ===== CADASTRO CENTRAL (só aparece se NÃO for med/enf) ===== -->
            <?php if ($mostrarCadastroCentral): ?>
                <div id="cadastro-central-wrapper" class="internacao-card internacao-card--central">
                    <div class="internacao-card__header">
                        <div>
                            <p class="internacao-card__eyebrow">Cadastro Central</p>
                            <!-- título removido conforme solicitado -->
                        </div>
                        <span class="internacao-card__tag">
                            <?php if ($cadastroCentralObrigatorio): ?>
                                Obrigatório selecionar tipo e responsável
                            <?php else: ?>
                                Opcional: escolha o responsável
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="internacao-card__body">
                        <div class="form-group row align-items-end">
                            <div class="form-group col-sm-3">
                                <label class="control-label" for="resp_tipo">Responsável pela visita</label>
                                <select id="resp_tipo" class="form-control input-lg-fullcare">
                                    <option value="">(sem seleção)</option>
                                    <option value="med">Médico auditor</option>
                                    <option value="enf">Enfermeiro auditor</option>
                                </select>
                            </div>

                            <div class="form-group col-sm-4 d-none" id="box_resp_med">
                                <label class="control-label" for="resp_med_id">Selecionar médico</label>
                                <select id="resp_med_id" class="form-control input-lg-fullcare selectpicker" data-live-search="true"
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
                                <select id="resp_enf_id" class="form-control input-lg-fullcare selectpicker" data-live-search="true"
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
                </div>
            <?php endif; ?>

            <div class="internacao-card internacao-card--fields">
                <div class="internacao-card__header">
                    <div>
                        <p class="internacao-card__eyebrow">Dados assistenciais</p>
                        <!-- título removido conforme solicitado -->
                    </div>
                </div>
                <div class="internacao-card__body">
                    <div class="row">
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="acomodacao_int">Acomodação</label>
                            <select class="input-lg-fullcare form-control" id="acomodacao_int" name="acomodacao_int">
                                <option value=""></option>
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
                            <input list="especialidade-options" class="input-lg-fullcare form-control" id="especialidade_int"
                                name="especialidade_int" placeholder="">
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
                            <input type="text" maxlength="100" class="form-control input-lg-fullcare" id="titular_int"
                                name="titular_int">
                        </div>
                        <div class="form-group col-sm-1">
                            <label for="crm_int">CRM</label>
                            <input type="text" maxlength="10" class="form-control input-lg-fullcare" id="crm_int" name="crm_int">
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="modo_internacao_int">Modo Admissão</label>
                            <select class="input-lg-fullcare form-control" id="modo_internacao_int" name="modo_internacao_int">
                                <option value=""></option>
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
                            <select class="input-lg-fullcare form-control" id="tipo_admissao_int" name="tipo_admissao_int">
                                <option value=""></option>
                                <option value="Eletiva">Eletiva</option>
                                <option value="Urgência">Urgência</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row" id="row-int-pertinente" style="display:none;">
                        <div style="display:none;" id="div_int_pertinente_int" class="form-group col-sm-2">
                            <label class="control-label" for="int_pertinente_int"><span style="color:red;">*</span> Internação
                                pertinente?</label>
                            <select class="input-lg-fullcare form-control" id="int_pertinente_int" name="int_pertinente_int">
                                <option value=""></option>
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
                <select class="input-lg-fullcare form-control selectpicker show-tick" data-size="5"
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
                                <option value="">CID</option>
                                <?php foreach ($cids as $cid): ?>
                                    <option value="<?= $cid["id_cid"] ?>">
                                        <?= $cid['cat'] . " - " . $cid["descricao"] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-sm-2">
                            <label class="control-label" for="grupo_patologia_int">Grupo Patologia</label>
                            <select class="input-lg-fullcare form-control" id="grupo_patologia_int" name="grupo_patologia_int">
                                <option value=""></option>
                                <?php foreach ($dados_grupo_pat as $grupo): ?>
                                    <option value="<?= htmlspecialchars($grupo) ?>"><?= htmlspecialchars($grupo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="origem_int">Origem</label>
                            <select class="input-lg-fullcare form-control" id="origem_int" name="origem_int">
                                <option value=""></option>
                                <?php foreach ($origem as $origens): ?>
                                    <option value="<?= htmlspecialchars($origens) ?>"><?= htmlspecialchars($origens) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-sm-2">
                            <label for="senha_int">Senha</label>
                            <input type="text" maxlength="20" class="form-control input-lg-fullcare" id="senha_int" name="senha_int">
                        </div>
                        <div class="form-group col-sm-2">
                            <label for="num_atendimento_int">Num. Atendimento</label>
                            <input type="text" maxlength="20" class="form-control input-lg-fullcare" id="num_atendimento_int"
                                name="num_atendimento_int">
                        </div>

                        <!-- <div class="form-group col-sm-2">
                <label class="control-label" for="fk_patologia2">Antecedente</label>
                <select class="input-lg-fullcare form-control selectpicker show-tick" data-size="5"
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

                </div>
            </div>
    </div>

    <div class="internacao-card internacao-card--notes">
        <div class="internacao-card__header">
            <div>
                <p class="internacao-card__eyebrow">Auditoria</p>
                <!-- título removido conforme solicitado -->
            </div>
        </div>
        <div class="internacao-card__body">
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

            <div>
                <label for="acoes_int">Ações da Auditoria</label>
                <textarea data-saude-autocomplete="true" rows="2" style="resize:none"
                    onclick="aumentarText('acoes_int')" class="form-control" maxlength="5000" id="acoes_int"
                    name="acoes_int"></textarea>
            </div>

            <div>
                <label for="programacao_int">Programação Terapêutica</label>
                <textarea data-saude-autocomplete="true" style="resize:none" maxlength="5000" rows="2"
                    onclick="aumentarText('programacao_int')" class="form-control" id="programacao_int"
                    name="programacao_int"></textarea>
            </div>
        </div>
    </div>

        <div class="tabelas-adicionais-card">
            <div class="tabelas-adicionais-card__header">
                <h4 class="tabelas-adicionais-card__title">
                    <span class="tabelas-adicionais-card__marker"></span>
                    Tabelas Adicionais
                </h4>
            </div>

        <div class="tabelas-selects d-flex flex-wrap justify-content-between align-items-end">
            <div class="form-group tabelas-col">
                <label class="control-label" style="font-weight: bold;" for="relatorio-detalhado">Relatório detalhado</label>
                <select class="input-lg-fullcare form-control detail-select" id="relatorio-detalhado" name="relatorio-detalhado">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <?php if ($cargoSessao === 'Med_auditor' || $cargoSessao === 'Diretoria') { ?>
                <div class="form-group tabelas-col">
                    <label class="control-label" style="font-weight: bold;" for="select_tuss">Tuss</label>
                    <select class="input-lg-fullcare form-control select-purple" id="select_tuss" name="select_tuss">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group tabelas-col">
                    <label class="control-label" style="font-weight: bold;" for="select_prorrog">Prorrogação</label>
                    <select class="input-lg-fullcare form-control select-purple" id="select_prorrog"
                        name="select_prorrog">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
            <?php } ?>

            <div class="form-group tabelas-col">
                <label class="control-label" style="font-weight: bold;" for="select_gestao">Gestão Assistencial</label>
                <select class="input-lg-fullcare form-control select-purple" id="select_gestao" name="select_gestao">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>

            <div class="form-group tabelas-col">
                <label class="control-label" style="font-weight: bold;" for="select_uti">UTI</label>
                <select class="input-lg-fullcare form-control select-purple" id="select_uti" name="select_uti">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>

            <?php if ($cargoSessao === 'Med_auditor' || $cargoSessao === 'Diretoria') { ?>
                <div class="form-group tabelas-col">
                    <label class="control-label" style="font-weight: bold;" for="select_negoc">Negociações</label>
                    <select class="input-lg-fullcare form-control select-purple" id="select_negoc" name="select_negoc">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
            <?php } ?>
            </div>
        </div>

        <div id="tabelas-dynamic-stack"></div>

        <?php include_once('formularios/form_cad_internacao_detalhes.php'); ?>

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

<?php if (!empty($id_paciente_get)): ?>
    <script>
        (function preselectPaciente() {
            var tentativas = 0;
            var idPac = "<?= (int) $id_paciente_get ?>";

            function aplicar() {
                var $sel = $('#fk_paciente_int');
                if (!$sel.length) return false;

                // seta o valor
                $sel.val(idPac);

                // se estiver usando bootstrap-select, atualiza a UI
                if ($.fn.selectpicker && $sel.hasClass('selectpicker')) {
                    $sel.selectpicker('refresh');
                }

                var el = document.getElementById('fk_paciente_int');
                if (el) {
                    el.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }

                // dispara sua verificação de internação ativa
                if (typeof window.triggerInternacaoCheck === 'function') {
                    try {
                        window.triggerInternacaoCheck();
                    } catch (e) {
                        console.warn('triggerInternacaoCheck falhou:', e);
                    }
                }
                return true;
            }

            // tenta algumas vezes até o select/BS-Select estarem prontos
            (function aguardarPronto() {
                if (aplicar()) return;
                if (++tentativas < 30) return setTimeout(aguardarPronto, 100);
                console.warn('Não foi possível pré-selecionar o paciente.');
            })();
        })();
    </script>
<?php endif; ?>
<script>
    function aumentarText(id) {
        const el = document.getElementById(id);
        if (el) el.rows = 20;
    }

    function reduzirText(id, rows) {
        const el = document.getElementById(id);
        if (el) el.rows = rows;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // id do textarea + número de linhas “fechado”
        const campos = [
            ['rel_int', 2],
            ['acoes_int', 2],
            ['programacao_int', 2],
        ];

        campos.forEach(([id, rowsFechado]) => {
            const el = document.getElementById(id);
            if (!el) return;

            // ao focar, expande
            el.addEventListener('focus', () => aumentarText(id));

            // ao perder o foco, volta para o tamanho original
            el.addEventListener('blur', () => reduzirText(id, rowsFechado));
        });
    });
    // selectpicker só se o plugin existir (evita quebrar tudo)
    $(function() {
        if ($.fn.selectpicker) {
            function updateSelectPlaceholder($select) {
                if (!$select || !$select.length) return;
                var val = $select.val();
                var $btn = $select.closest('.bootstrap-select').find('button.dropdown-toggle');
                if ($btn.length) {
                    $btn.toggleClass('select-placeholder', !val);
                }
            }

            $('.selectpicker').each(function() {
                var $el = $(this);
                if (!$el.data('selectpicker')) {
                    $el.selectpicker();
                }
            });
            $('.selectpicker').selectpicker('refresh');
            $('.selectpicker').each(function() {
                updateSelectPlaceholder($(this));
            });
            var $pacientePicker = $('#fk_paciente_int');
            if ($pacientePicker.length) {
                var picker = $pacientePicker.data('selectpicker');
                var $searchInput = picker && picker.$searchbox ? picker.$searchbox.find('input') : null;
                if ($searchInput && $searchInput.length) {
                    $searchInput.attr('placeholder', 'Pesquisa por nome');
                }
            }
            $('.selectpicker').on('loaded.bs.select', function() {
                var $picker = $(this).data('selectpicker');
                var $searchInput = $picker && $picker.$searchbox ? $picker.$searchbox.find('input') : null;
                if (!$searchInput || !$searchInput.length) return;
                if ($(this).attr('id') === 'fk_paciente_int') {
                    $searchInput.attr('placeholder', 'Pesquisa por nome');
                } else {
                    $searchInput.attr('placeholder', 'Digite para pesquisar...');
                }
                updateSelectPlaceholder($(this));
            }).on('changed.bs.select', function() {
                updateSelectPlaceholder($(this));
            });
        }
    });

    const hospitalInsightsHelper = (function() {
        const button = document.getElementById('hospitalTipButtonInline');
        const popover = document.getElementById('hospitalTipPopover');
        const alertBox = document.getElementById('hospitalUtiAlert');
        const defaultMessage = 'Selecione um hospital para ver negociações e pacientes em UTI.';

        function hideAlert() {
            if (alertBox) {
                alertBox.textContent = '';
                alertBox.classList.remove('show');
            }
        }

        function showAlert(message) {
            if (!alertBox) return;
            alertBox.textContent = message;
            alertBox.classList.add('show');
        }

        function setPopover(content) {
            if (!popover) return;
            popover.innerHTML = content;
        }

        function setLoading(hospitalName) {
            if (button) {
                button.disabled = true;
            }
            if (popover) {
                popover.classList.remove('show');
            }
            setPopover(`Carregando dados de <strong>${hospitalName}</strong>...`);
            hideAlert();
        }

        function reset() {
            if (button) button.disabled = true;
            if (popover) popover.classList.remove('show');
            setPopover(defaultMessage, false);
            hideAlert();
        }

        async function fetchInsights(hospitalId, hospitalName) {
            if (!hospitalId) {
                reset();
                return;
            }
            setLoading(hospitalName || 'hospital selecionado');
            try {
                const response = await fetch('<?= $BASE_URL ?>ajax/hospital_insights.php?id_hospital=' + encodeURIComponent(
                    hospitalId), {
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('Falha ao consultar insights.');
                const payload = await response.json();
                if (!payload.success || !payload.data) {
                    throw new Error(payload.error || 'Resposta inválida.');
                }
                const data = payload.data;
                if (button) button.disabled = false;
                const percent = data.percent_uti ?? 0;
                const longStay = data.long_stay ?? 0;
                const longThreshold = data.long_threshold ?? 0;
                const html = `
                <div><strong>${hospitalName || 'Hospital selecionado'}</strong></div>
                <div>Negociações registradas: <strong>${data.negociacoes ?? 0}</strong></div>
                <div>Internações em UTI: <strong>${data.inter_uti ?? 0}</strong></div>
                <div>Total de internações: <strong>${data.total_internacoes ?? 0}</strong></div>
                <div>UTI vs Total: <strong>${percent}%</strong></div>
                <div>MP Hospital: <strong>${data.mp_hospital ?? 0} dias</strong></div>
                <div>MP UTI: <strong>${data.mp_uti ?? 0} dias</strong></div>
                <div>Longa permanência (&ge; ${longThreshold} dias): <strong>${longStay}</strong></div>
            `;
                setPopover(html);
                if (data.uti_alert) {
                    const threshold = data.threshold ?? 0;
                    showAlert(
                        `Alerta: ${data.inter_uti} internações em UTI neste hospital (limite ${threshold}).`
                    );
                } else {
                    hideAlert();
                }
            } catch (err) {
                if (button) button.disabled = false;
                setPopover(`Não foi possível carregar os dados. ${err.message}`);
                showAlert('Não foi possível verificar os pacientes em UTI agora.');
            }
        }

        if (button && popover) {
            button.addEventListener('click', function() {
                if (button.disabled) return;
                popover.classList.toggle('show');
            });
            document.addEventListener('click', function(evt) {
                if (!popover || !button) return;
                if (popover.contains(evt.target) || button.contains(evt.target)) return;
                popover.classList.remove('show');
            });
        }

        reset();
        return {
            fetch: fetchInsights,
            reset: reset
        };
    })();

    const patientInsightsHelper = (function() {
        const card = document.getElementById('patientInsightCard');
        const body = document.getElementById('patientInsightBody');
        const hubLink = document.getElementById('patientInsightHub');
        const hubBase = card ? card.dataset.hubBase || '' : '';
        const defaultMessage = 'Selecione um paciente para visualizar o histórico resumido.';
        let requestId = 0;

        function setMessage(msg) {
            if (body) body.innerHTML = msg;
        }

        function disableHub() {
            if (hubLink) {
                hubLink.classList.add('disabled');
                hubLink.href = '#';
            }
        }

        function enableHub(idPaciente) {
            if (hubLink) {
                hubLink.classList.remove('disabled');
                hubLink.href = hubBase ? hubBase + encodeURIComponent(idPaciente) : '#';
            }
        }

        function reset() {
            setMessage(defaultMessage);
            disableHub();
        }

        async function fetchInsights(pacId, pacName) {
            if (!card || !body) return;
            if (!pacId) {
                reset();
                return;
            }
            const current = ++requestId;
            setMessage(`Carregando dados de <strong>${pacName || 'paciente'}</strong>...`);
            disableHub();
            try {
                const response = await fetch('<?= $BASE_URL ?>ajax/paciente_insights.php?id_paciente=' + encodeURIComponent(
                    pacId), {
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('Falha ao consultar resumo.');
                const payload = await response.json();
                if (current !== requestId) return;
                if (!payload.success || !payload.data) throw new Error(payload.error ||
                    'Resposta inválida.');
                const data = payload.data;
                const html = `
                <div class="patient-insight-metrics">
                    <div>
                        Total internações
                        <strong>${data.total_internacoes ?? 0}</strong>
                    </div>
                    <div>
                        Nº de diárias
                        <strong>${data.total_diarias ?? 0}</strong>
                    </div>
                    <div>
                        MP (dias)
                        <strong>${data.mp ?? 0}</strong>
                    </div>
                </div>
            `;
                setMessage(html);
                enableHub(pacId);
            } catch (err) {
                if (current !== requestId) return;
                setMessage(`Não foi possível carregar o resumo. ${err.message}`);
                disableHub();
            }
        }

        reset();
        return {
            fetch: fetchInsights,
            reset
        };
    })();

    const patientInsightDisplay = (function() {
        const card = document.getElementById('patientInsightCard');
        const btn = document.getElementById('patientInsightToggle');
        let visible = false;

        function update() {
            if (card) card.style.display = visible ? 'block' : 'none';
            if (btn) {
                btn.classList.toggle('active', visible);
                btn.setAttribute('aria-expanded', visible ? 'true' : 'false');
            }
        }

        if (btn) {
            btn.addEventListener('click', function() {
                visible = !visible;
                update();
            });
        }

        update();

        return {
            hide() {
                visible = false;
                update();
            },
            isVisible() {
                return visible;
            }
        };
    })();

    // Hospital selecionado -> mostra nome e grava hidden
    function myFunctionSelected() {
        const select = document.getElementById("hospital_selected");
        const inputHospital = document.getElementById("fk_hospital_int");
        const divNome = document.getElementById("hospitalNomeTexto");

        if (!select || !inputHospital || !divNome) return;

        const id = select.value || "";
        const nome = select.options[select.selectedIndex]?.text || "";

        inputHospital.value = id;

        if (id) {
            select.style.color = "black";
            select.style.fontWeight = "bold";
            select.style.border = "2px solid green";
            divNome.textContent = nome;
            divNome.style.display = "flex";
            if (hospitalInsightsHelper && typeof hospitalInsightsHelper.fetch === 'function') {
                hospitalInsightsHelper.fetch(id, nome);
            }
        } else {
            select.style.color = "#000";
            select.style.fontWeight = "normal";
            select.style.border = "1px solid #555";
            divNome.textContent = "";
            divNome.style.display = "none";
            if (hospitalInsightsHelper && typeof hospitalInsightsHelper.reset === 'function') {
                hospitalInsightsHelper.reset();
            }
        }
    }

    // Estilo do select "relatório detalhado"
    $('#relatorio-detalhado').on('change', function() {
        const optionDetalhes = $(this).find(":selected").text();
        const baseCss = {
            "color": "white",
            "font-weight": "normal",
            "border": "1px solid #5e2363",
            "background-color": "#5e2363"
        };
        $(this).css(baseCss);
        if (optionDetalhes === "Sim") {
            $(this).css({
                "color": "black",
                "font-weight": "bold",
                "border": "2px solid green",
                "background-color": "#d8b4fe"
            });
        } else {
            $(this).val("").css(baseCss);
        }
    });

    // Toggle campos dependentes
    $(function() {
        $('#medicacaoDet').hide();
        $('#medic_alto_custo_det').on('change', function() {
            ($(this).val() === 's') ? $('#medicacaoDet').show(): $('#medicacaoDet').hide();
        });

        $('#atb').hide();
        $('#atb_det').on('change', function() {
            ($(this).val() === 's') ? $('#atb').show(): $('#atb').hide();
        });

        $('#div-oxig').hide();
        $('#oxig_det').on('change', function() {
            ($('#oxig_det').val() === 'Cateter' || $('#oxig_det').val() === 'Mascara') ? $('#div-oxig')
                .show(): $('#div-oxig').hide();
        });
    });

    // Mostrar UTI se acomodação == UTI
    document.getElementById("acomodacao_int").addEventListener("change", function() {
        const divUti = document.querySelector("#container-uti");
        if (divUti) divUti.style.display = (this.value === "UTI") ? "block" : "none";
    });

    // Validação de datas
    document.getElementById("data_intern_int").addEventListener("blur", function() {
        const input = this;
        const dataInternacao = new Date(input.value);
        const dataHoje = new Date();
        const erroDiv = document.getElementById("erro-data-internacao");

        erroDiv.style.display = "none";
        erroDiv.textContent = "";
        if (!input.value) return;
        const dataFormatadaHoje = dataHoje.toISOString().split("T")[0];

        if (input.value > dataFormatadaHoje) {
            erroDiv.textContent = "A data da internação não pode ser maior que a data atual.";
            erroDiv.style.display = "block";
            input.value = "";
            return setTimeout(() => {
                erroDiv.style.display = "none";
                erroDiv.textContent = "";
            }, 5000);
        }

        const diffDias = (dataHoje - dataInternacao) / (1000 * 60 * 60 * 24);
        if (diffDias > 30) {
            erroDiv.textContent = "Deseja prorrogar acima de 30 dias?";
            erroDiv.style.display = "block";
            setTimeout(() => {
                erroDiv.style.display = "none";
                erroDiv.textContent = "";
            }, 7000);
        }
    });

    document.getElementById("data_visita_int").addEventListener("change", function() {
        const dataInternacao = new Date(document.getElementById("data_intern_int").value);
        const dataVisita = new Date(this.value);
        const hoje = new Date();
        const seteDiasDepois = new Date();
        seteDiasDepois.setDate(hoje.getDate() + 7);
        const errorMessage = document.getElementById("error-message");
        errorMessage.style.display = "none";
        errorMessage.textContent = "";

        if (document.getElementById("data_intern_int").value && dataVisita < dataInternacao) {
            errorMessage.textContent = "A data da visita não pode ser menor que a data de internação.";
            return errorMessage.style.display = "block";
        }
        if (dataVisita > seteDiasDepois) {
            errorMessage.textContent = "A data da visita não pode ser maior que 7 dias da data atual.";
            errorMessage.style.display = "block";
        }
    });

    // Internação pertinente (quando tipo = Urgência)
    document.getElementById("tipo_admissao_int").addEventListener("change", function() {
        const tipo = this.value;
        const divPertinente = document.getElementById("div_int_pertinente_int");
        const divRelPertinente = document.getElementById("div_rel_pertinente_int");
        divPertinente.style.display = "none";
        divRelPertinente.style.display = "none";
        if (tipo === "Urgência") {
            divPertinente.style.display = "block";
            document.getElementById("int_pertinente_int").addEventListener("change", function() {
                divRelPertinente.style.display = (this.value === "n") ? "block" : "none";
            }, {
                once: true
            });
        }
    });

    // JSON de antecedentes
    // document.getElementById('fk_patologia2').addEventListener('change', function() {
    //     const selectedOptions = Array.from(this.selectedOptions).map(o => parseInt(o.value, 10));
    //     const fkPaciente = parseInt(document.getElementById('fk_paciente_int').value || '0', 10);
    //     const fkInternacao = parseInt(document.getElementById('id_internacao').value || '0', 10);
    //     const jsonAntecedentes = selectedOptions.map(idAntecedente => ({
    //         fk_id_paciente: fkPaciente,
    //         fk_internacao_ant_int: fkInternacao + 1,
    //         intern_antec_ant_int: idAntecedente
    //     }));
    //     document.getElementById('json-antec').value = JSON.stringify(jsonAntecedentes);
    // });

    // Mostrar/ocultar campos de alta conforme "Internado"
    document.addEventListener("DOMContentLoaded", function() {
        const selectInternado = document.getElementById("internado_int");
        const divDataAlta = document.getElementById("div-data-alta");
        const divMotivoAlta = document.getElementById("div-motivo-alta");

        function toggleDataAlta() {
            if (selectInternado.value === "s") {
                divDataAlta.style.display = "none";
                divMotivoAlta.style.display = "none";
                document.getElementById("data_alta_alt").value = "";
                document.getElementById("tipo_alta_alt").value = "";
            } else {
                divDataAlta.style.display = "block";
                divMotivoAlta.style.display = "block";
            }
        }
        toggleDataAlta();
        selectInternado.addEventListener("change", toggleDataAlta);
    });



    /* ==========================================================
       CADASTRO CENTRAL — LÓGICA ÚNICA (sem duplicações)
       Regras:
       - fk_usuario_int = ID do responsável selecionado
       - visita_med_int / visita_enf_int = 's' / 'n' conforme tipo
       - visita_auditor_prof_med = SEMPRE o ID (espelhado de fk_usuario_int) SE tipo != 'enf'; caso 'enf', fica vazio
       - visita_auditor_prof_enf não é usado (fica vazio)
       ========================================================== */
    function mirrorVisitMedFromFk() {
        const fk = document.getElementById('fk_usuario_int')?.value || '';
        const tipo = document.getElementById('resp_tipo')?.value || '';
        const medHidden = document.getElementById('visita_auditor_prof_med');
        const updateGroup = (selector) => {
            document.querySelectorAll(selector).forEach(el => {
                if (el) el.value = fk;
            });
        };
        if (medHidden) {
            medHidden.value = (tipo === 'enf') ? '' : fk;
        }
        updateGroup('#fk_usuario_neg');
        updateGroup('#fk_usuario_tuss');
        updateGroup('input[name="fk_usuario_tuss"]');
        updateGroup('#fk_usuario_pror');
        updateGroup('input[name="fk_usuario_pror"]');
        updateGroup('#fk_user_uti');
    }
    document.addEventListener('DOMContentLoaded', mirrorVisitMedFromFk);
    document.addEventListener('DOMContentLoaded', function() {
        const formInternacao = document.getElementById('myForm');
        if (formInternacao) {
            formInternacao.addEventListener('submit', mirrorVisitMedFromFk);
        }
    });

    (function() {
        const respTipo = document.getElementById('resp_tipo');
        const boxMed = document.getElementById('box_resp_med');
        const boxEnf = document.getElementById('box_resp_enf');
        const selMed = document.getElementById('resp_med_id');
        const selEnf = document.getElementById('resp_enf_id');

        const fkUsuario = document.getElementById('fk_usuario_int');
        const flgMed = document.getElementById('visita_med_int');
        const flgEnf = document.getElementById('visita_enf_int');
        const emailMed = document.getElementById('visita_auditor_prof_med'); // usado para ID do médico responsável
        const emailEnf = document.getElementById('visita_auditor_prof_enf'); // não utilizado (mantém vazio)

        const idSessao = "<?= htmlspecialchars($idSessao) ?>";
        const cargoSessao = "<?= addslashes($cargoSessao) ?>";
        const clearInvalid = (el) => {
            if (el) el.classList.remove('is-invalid');
        };

        function refreshPicker(el) {
            if (window.$ && $.fn.selectpicker && el && $(el).hasClass('selectpicker')) {
                $(el).selectpicker('refresh');
            }
        }

        function hide(el) {
            if (el) {
                el.classList.add('d-none');
                el.hidden = true;
                el.style.display = '';
                refreshPicker(el.querySelector('select') || el);
            }
        }

        function show(el) {
            if (el) {
                el.classList.remove('d-none');
                el.hidden = false;
                el.style.display = '';
                refreshPicker(el.querySelector('select') || el);
            }
        }

        function resetToSessionUser() {
            if (!fkUsuario) return;
            fkUsuario.value = idSessao || '';
            if (flgMed) flgMed.value = (cargoSessao === 'Med_auditor') ? 's' : 'n';
            if (flgEnf) flgEnf.value = (cargoSessao === 'Enf_Auditor') ? 's' : 'n';
            if (emailMed) emailMed.value = ''; // será setado por mirrorVisitMedFromFk
            if (emailEnf) emailEnf.value = '';
            mirrorVisitMedFromFk();
        }

        function resetCadastroCentralUI() {
            respTipo?.classList.remove('is-invalid');
            if (respTipo) respTipo.value = '';

            [selMed, selEnf].forEach(function(selectEl) {
                if (!selectEl) return;
                selectEl.classList.remove('is-invalid');
                selectEl.value = '';
                refreshPicker(selectEl);
            });

            hide(boxMed);
            hide(boxEnf);
            resetToSessionUser();
        }

        // inicia oculto
        hide(boxMed);
        hide(boxEnf);
        resetToSessionUser();

        window.cadastroCentralHelper = window.cadastroCentralHelper || {};
        window.cadastroCentralHelper.reset = resetCadastroCentralUI;
        window.cadastroCentralHelper.resetToSessionUser = resetToSessionUser;

        respTipo?.addEventListener('change', function() {
            clearInvalid(respTipo);
            clearInvalid(selMed);
            clearInvalid(selEnf);
            const v = this.value;
            if (selMed) selMed.value = '';
            if (selEnf) selEnf.value = '';
            if (flgMed) flgMed.value = 'n';
            if (flgEnf) flgEnf.value = 'n';
            if (emailMed) emailMed.value = '';
            if (emailEnf) emailEnf.value = '';
            if (fkUsuario) fkUsuario.value = idSessao;

            hide(boxMed);
            hide(boxEnf);
            if (v === 'med') {
                show(boxMed);
                refreshPicker(selMed);
                if (flgMed) flgMed.value = 's';
            }
            if (v === 'enf') {
                show(boxEnf);
                refreshPicker(selEnf);
                if (flgEnf) flgEnf.value = 's';
            }
            mirrorVisitMedFromFk();
        });

        selMed?.addEventListener('change', function() {
            clearInvalid(selMed);
            const opt = this.selectedOptions[0];
            if (!opt?.value) {
                resetToSessionUser();
                return;
            }
            if (fkUsuario) fkUsuario.value = opt.value;
            if (flgMed) flgMed.value = 's';
            if (flgEnf) flgEnf.value = 'n';
            if (emailEnf) emailEnf.value = '';
            mirrorVisitMedFromFk();
        });

        selEnf?.addEventListener('change', function() {
            clearInvalid(selEnf);
            const opt = this.selectedOptions[0];
            if (!opt?.value) {
                resetToSessionUser();
                return;
            }
            if (fkUsuario) fkUsuario.value = opt.value;
            if (flgMed) flgMed.value = 'n';
            if (flgEnf) flgEnf.value = 's';
            if (emailMed) emailMed.value = ''; // tipo enf → campo do médico fica vazio
            if (emailEnf) emailEnf.value = '';
            mirrorVisitMedFromFk();
        });
    })();
    // Prorrogação: mostra container quando "s"
    document.addEventListener("DOMContentLoaded", function() {
        const selectProrrog = document.getElementById("select_prorrog");
        const containerProrrog = document.getElementById("container-prorrog");
        if (selectProrrog && containerProrrog) {
            function toggleProrrog() {
                containerProrrog.style.display = (selectProrrog.value === "s") ? "block" : "none";
            }
            selectProrrog.addEventListener("change", toggleProrrog);
            toggleProrrog();
        }
    });


    // SUBMIT AJAX
    // formulario ajax para envio form sem refresh
    $("#myForm").submit(function(event) {
        event.preventDefault(); // Impede o envio tradicional do formulário
        let post_url = $(this).attr("action"); // Obtém a URL de ação do formulário
        let request_method = $(this).attr("method"); // Obtém o método do formulário (GET/POST)
        let form_data = new FormData(this); // Cria um objeto FormData com os dados do formulário

        // Garante envio dos campos de gestão mesmo quando o container é manipulado via DOM.
        (function ensureGestaoFields(fd) {
            const defaults = {
                alto_custo_ges: 'n',
                home_care_ges: 'n',
                opme_ges: 'n',
                desospitalizacao_ges: 'n',
                evento_adverso_ges: 'n',
                evento_sinalizado_ges: 'n',
                evento_discutido_ges: 'n',
                evento_negociado_ges: 'n',
                evento_retorno_qual_hosp_ges: 'n',
                evento_encerrar_ges: 'n',
                evento_impacto_financ_ges: 'n',
                evento_prolongou_internacao_ges: 'n',
                evento_fech_ges: 'n',
                evento_prorrogar_ges: 'n',
                rel_alto_custo_ges: '',
                rel_home_care_ges: '',
                rel_opme_ges: '',
                rel_desospitalizacao_ges: '',
                tipo_evento_adverso_gest: '',
                rel_evento_adverso_ges: '',
                evento_data_ges: '',
                evento_classificacao_ges: '',
                evento_valor_negoc_ges: '',
                fk_internacao_ges: ''
            };

            Object.keys(defaults).forEach(function(name) {
                const el = document.querySelector('[name="' + name + '"]');
                const value = el ? (el.value ?? '') : defaults[name];
                fd.set(name, value === null || value === undefined ? defaults[name] : String(value));
            });
        })(form_data);


        // 1. Salva o valor selecionado do select de hospitais
        const hospitalSelected = document.getElementById("hospital_selected").value;

        // 1.A. Validação do Hospital
        if (hospitalSelected === "") {
            // Usa a div de alerta existente para exibir o erro
            $('#alert').removeClass("alert-success").addClass("alert-danger");
            $('#alert').fadeIn().html("<b>Erro:</b> O campo Hospital é obrigatório.");

            // --- INÍCIO DA ALTERAÇÃO ---
            // Adiciona borda vermelha para indicar erro no campo
            $("#hospital_selected").css("border", "2px solid red");
            // --- FIM DA ALTERAÇÃO ---

            // Oculta a mensagem após 3 segundos
            setTimeout(function() {
                $('#alert').fadeOut('Slow');
            }, 3000);

            // Impede a execução do AJAX
            return;
        }

        // (Opcional, mas bom) Se passou na validação, garante que a borda não esteja vermelha
        // A função myFunctionSelected já deve ter deixado verde se um valor foi selecionado.
        // Esta linha é uma segurança extra caso algum cenário não dispare o 'onchange'.
        // Se a borda já for verde (ou padrão), não fará mal.
        if ($("#hospital_selected").css("border-color") === "rgb(255, 0, 0)") { // Verifica se a cor é vermelho
            $("#hospital_selected").css("border", "2px solid green"); // Muda para verde se estava vermelha
        }

        if (typeof window.isSenhaDuplicada === 'function' && window.isSenhaDuplicada()) {
            $('#alert').removeClass("alert-success").addClass("alert-danger");
            $('#alert').fadeIn().html("Esta senha já está cadastrada para outra internação.");
            setTimeout(function() {
                $('#alert').fadeOut('Slow');
            }, 3500);
            return;
        }

        const cadCentralObrig = document.getElementById('cad_central_obrigatorio')?.value === '1';
        if (cadCentralObrig) {
            const respTipoEl = document.getElementById('resp_tipo');
            const respMedEl = document.getElementById('resp_med_id');
            const respEnfEl = document.getElementById('resp_enf_id');
            [respTipoEl, respMedEl, respEnfEl].forEach(function(el) {
                if (el) el.classList.remove('is-invalid');
            });

            const respTipoVal = respTipoEl?.value || '';
            let cadMsg = '';

            if (!respTipoVal) {
                cadMsg = 'Selecione o tipo de responsável pela visita.';
                respTipoEl?.classList.add('is-invalid');
            } else if (respTipoVal === 'med' && !(respMedEl?.value)) {
                cadMsg = 'Selecione o médico responsável pela visita.';
                respMedEl?.classList.add('is-invalid');
            } else if (respTipoVal === 'enf' && !(respEnfEl?.value)) {
                cadMsg = 'Selecione o enfermeiro responsável pela visita.';
                respEnfEl?.classList.add('is-invalid');
            }

            if (cadMsg) {
                $('#alert').removeClass("alert-success").addClass("alert-danger");
                $('#alert').fadeIn().html("<b>Erro:</b> " + cadMsg);
                setTimeout(function() {
                    $('#alert').fadeOut('Slow');
                }, 3000);
                return;
            }
        }


        $.ajax({
            url: post_url,
            type: request_method,
            processData: false, // Impede o jQuery de processar os dados
            contentType: false, // Impede o jQuery de definir o contentType
            data: form_data,
            success: function(result) {
                const resposta = String(result || '').trim();
                if (resposta === 'paciente_internado') {
                    $('#alert').removeClass("alert-success").addClass("alert-danger");
                    $('#alert').fadeIn().html(
                        "Paciente possui internação ativa e precisa confirmar retroativa.");
                    setTimeout(function() {
                        $('#alert').fadeOut('Slow');
                    }, 3000);
                    return;
                }
                if (resposta === 'retroativa_sem_alta') {
                    $('#alert').removeClass("alert-success").addClass("alert-danger");
                    $('#alert').fadeIn().html(
                        "Para retroativa, marque 'Internado = Não' e informe a data/motivo da alta."
                    );
                    setTimeout(function() {
                        $('#alert').fadeOut('Slow');
                    }, 3500);
                    return;
                }
                if (resposta === 'senha_duplicada') {
                    $('#alert').removeClass("alert-success").addClass("alert-danger");
                    $('#alert').fadeIn().html(
                        "Esta senha já está cadastrada para outra internação.");
                    setTimeout(function() {
                        $('#alert').fadeOut('Slow');
                    }, 3500);
                    return;
                }
                if (resposta === 'hospital_required') {
                    $('#alert').removeClass("alert-success").addClass("alert-danger");
                    $('#alert').fadeIn().html("<b>Erro:</b> Selecione o Hospital antes de salvar.");
                    $("#hospital_selected").css("border", "2px solid red");
                    setTimeout(function() {
                        $('#alert').fadeOut('Slow');
                    }, 3000);
                    return;
                }

                if (resposta === '0') {
                    $('#alert').removeClass("alert-success").addClass("alert-danger");
                    $('#alert').fadeIn().html("Paciente possui internação ativa");
                    setTimeout(function() {
                        $('#alert').fadeOut('Slow');
                    }, 2000);
                    return;
                }

                // Sucesso (resposta vazia ou texto padrão)
                {
                    // Increment the reg_int value
                    const regIntInput = $("#RegInt");
                    const currentRegInt = parseInt(regIntInput.val());
                    const newRegInt = currentRegInt + 1;

                    regIntInput.val(newRegInt);

                    // . Success alert
                    $('#alert').removeClass("alert-danger").addClass("alert-success");
                    $('#alert').fadeIn().html("Cadastrado com sucesso");
                    setTimeout(function() {
                        $('#alert').fadeOut('Slow');
                    }, 3000);

                    // 2. Resetando os campos de input, select e textarea EXCETO os campos `hidden` e o select do hospital
                    document.querySelectorAll('input, select, textarea').forEach((element) => {
                        if (element.type !== "hidden" && element.id !==
                            "hospital_selected") {
                            element.value = '';
                        }
                    });

                    if (window.cadastroCentralHelper && typeof window.cadastroCentralHelper
                        .reset ===
                        'function') {
                        window.cadastroCentralHelper.reset();
                    }

                    // 3. Restaura o valor selecionado do select de hospitais (já feito antes do AJAX)
                    // document.getElementById("hospital_selected").value = hospitalSelected; // Não precisa redefinir aqui

                    // 4. Atualiza outros selects (exceto o de hospitais)
                    $('#fk_paciente_int').val('').selectpicker('refresh');
                    $('#fk_patologia2').val('').selectpicker('refresh');
                    $('#fk_patologia_int').val('').selectpicker('refresh');

                    // 5. Update other values
                    const adicionarValor = parseInt(document.querySelector("#proximoId_int")
                        .textContent) + 1;
                    const ultimoReg = <?= $ultimoReg ?>;
                    const novoValorInternacao = parseInt(ultimoReg) + adicionarValor;

                    $("#proximoId_int").text(adicionarValor);
                    $("#proximoId_int").val(
                        novoValorInternacao); // Este seletor estava incorreto, corrigido para val()

                    // $("#RegInt").val(newRegInt); // Já atualizado acima
                    $("#fk_int_tuss").val(novoValorInternacao);
                    $("#fk_internacao_uti").val(novoValorInternacao);
                    $("#fk_id_int").val(novoValorInternacao);
                    $("#fk_internacao_pror").val(novoValorInternacao);
                    $("#fk_internacao_ges").val(novoValorInternacao);
                    $("#fk_int_det").val(novoValorInternacao);
                    document.getElementById("internado_int").value = "s";
                    document.getElementById("internado_int").querySelector("option[value='s']")
                        .selected = true;

                    // 6. Hide containers
                    const containers = [
                        "#container-gestao",
                        "#container-tuss",
                        "#container-prorrog",
                        "#container-uti",
                        "#container-negoc",
                        "#div-detalhado"
                    ];
                    containers.forEach((container) => {
                        document.querySelector(container).style.display = "none";
                    });

                    // 7. Restaura a borda dos selects após o reset (exceto o de hospitais)
                    document.querySelectorAll(
                        "#select_tuss, #select_gestao, #relatorio-detalhado, #select_prorrog, #select_uti, #select_negoc, select" // Removido 'select' genérico para evitar redefinir o hospital
                    ).forEach(select => {
                        if (select.id !==
                            "hospital_selected"
                        ) { // Garante que não afeta o select de hospital
                            select.value = ""; // Reseta o valor do select
                            select.style.border =
                                "1px solid #ced4da"; // Borda padrão Bootstrap
                            select.style.color =
                                "#6c757d"; // Cor padrão Bootstrap para placeholder
                            select.style.fontWeight = "normal";
                            select.style.backgroundColor = "#fff"; // Fundo padrão
                        }
                    });
                    // Especificamente resetar os selects roxos para o estilo padrão deles
                    $('.select-purple').css({
                        "color": "#2d114c",
                        "font-weight": "700",
                        "border": "1px solid #4caf50",
                        "background-color": "#fff"
                    });


                    // 8. Atualiza selects que usam Bootstrap Select (exceto o de hospitais)
                    // Já feito acima para paciente, patologia, etc. O reset dos selects roxos não usa selectpicker.


                    // 9. Success alert (já feito no início do success)
                    // $('#alert').removeClass("alert-danger").addClass("alert-success"); ...


                    $('#retroativa_confirmada').val('0');
                    $('#retroativa-alert').addClass('d-none');
                    $('#retroativa-container').addClass('d-none');
                }

                // Clear additional fields
                clearTussInputs();
                clearProrrogInputs();

            },

            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.log("XHR response:", xhr.responseText);
            }
        });
    });

    // Prorrogação: mostra container quando "s"
    document.addEventListener("DOMContentLoaded", function() {
        const selectProrrog = document.getElementById("select_prorrog");
        const containerProrrog = document.getElementById("container-prorrog");
        if (selectProrrog && containerProrrog) {
            function toggleProrrog() {
                containerProrrog.style.display = (selectProrrog.value === "s") ? "block" : "none";
            }
            selectProrrog.addEventListener("change", toggleProrrog);
            toggleProrrog();
        }
    });

    // Mostrar UTI se acomodação == UTI
    document.getElementById("acomodacao_int").addEventListener("change", function() {
        const divUti = document.querySelector("#container-uti");
        if (divUti) divUti.style.display = (this.value === "UTI") ? "block" : "none";
    });

    // Tabelas adicionais (Tuss, Gestão, UTI, Prorrogação, Negociações)
    document.addEventListener('DOMContentLoaded', function() {

        function setupToggle(selectId, containerId) {
            const selectEl = document.getElementById(selectId);
            const containerEl = document.getElementById(containerId);
            const stack = document.getElementById('tabelas-dynamic-stack');

            if (!selectEl || !containerEl) return;

            function aplicar() {
                if (selectEl.value === 's') {
                    containerEl.style.display = 'block';
                    if (stack) {
                        stack.insertBefore(containerEl, stack.firstChild);
                    }
                } else {
                    containerEl.style.display = 'none';
                }
            }

            // garante estado inicial
            aplicar();
            // atualiza ao mudar
            selectEl.addEventListener('change', aplicar);
        }

        // Tuss
        setupToggle('select_tuss', 'container-tuss');

        // Prorrogação
        setupToggle('select_prorrog', 'container-prorrog');

        // Gestão
        setupToggle('select_gestao', 'container-gestao');

        // Negociações
        setupToggle('select_negoc', 'container-negoc');

        // UTI: depende do select_uti e da acomodação
        (function() {
            const selectUti = document.getElementById('select_uti');
            const acomEl = document.getElementById('acomodacao_int');
            const containerUti = document.getElementById('container-uti');
            const stack = document.getElementById('tabelas-dynamic-stack');

            if (!containerUti) return;

            function aplicarUti() {
                const viaSelect = selectUti && selectUti.value === 's';
                const viaAcomod = acomEl && acomEl.value === 'UTI';
                const ativo = (viaSelect || viaAcomod);
                containerUti.style.display = ativo ? 'block' : 'none';
                if (ativo && stack) {
                    stack.insertBefore(containerUti, stack.firstChild);
                }
            }

            aplicarUti();

            if (selectUti) {
                selectUti.addEventListener('change', aplicarUti);
            }
            if (acomEl) {
                acomEl.addEventListener('change', aplicarUti);
            }
        })();
    });

    // Relatório Detalhado
    (function() {
        const selectDet = document.getElementById('relatorio-detalhado');
        const divDet = document.getElementById('div-detalhado');
        const wrapperDet = document.getElementById('detalhes-card-wrapper');

        if (!selectDet || !divDet || !wrapperDet) return;

        function aplicar() {
            const ativo = selectDet.value === 's';
            wrapperDet.style.display = ativo ? 'block' : 'none';
            divDet.style.display = ativo ? 'block' : 'none';
        }

        aplicar();
        selectDet.addEventListener('change', aplicar);
    })();


    // Carregar acomodações via hospital (para negociações/savings)
    $(document).ready(function() {
        $('#hospital_selected').on('change', function() {
            const id_hospital = $(this).val();
            if (!id_hospital) return;
            fetchAcomodacoes(id_hospital);
        });

        function fetchAcomodacoes(id_hospital) {
            $.ajax({
                url: 'process_acomodacao.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id_hospital
                },
                success: function(response) {
                    if (response.status === 'success') populateSelects(response.acomodacoes);
                    else console.error("Erro recebido do servidor:", response.message);
                },
                error: function(xhr, status, error) {
                    console.error("Erro na requisição AJAX:", error, "Status:", status, "Resposta:",
                        xhr
                        .responseText);
                },
            });
        }

        function populateSelects(acomodacoes) {
            let options = '<option value="">Selecione a Acomodação</option>';
            acomodacoes.forEach(ac => {
                options +=
                    `<option value="${ac.id_acomodacao}-${ac.acomodacao_aco}" data-valor="${ac.valor_aco}">${ac.acomodacao_aco}</option>`;
            });
            $('select[name="troca_de"]').html(options);
            $('select[name="troca_para"]').html(options);
            $('input[name="saving"]').val('');
            $('input[name="qtd"]').val('');
            $('input[name="saving_show"]').val('').css('color', '');
        }

        $(document).on('change keyup', 'select[name="troca_de"], select[name="troca_para"], input[name="qtd"]',
            function() {
                calculateSavings($(this).closest('.negotiation-field-container'));
            });

        function calculateSavings(container) {
            const trocaDeOption = container.find('select[name="troca_de"] option:selected');
            const trocaParaOption = container.find('select[name="troca_para"] option:selected');
            const quantidadeInput = container.find('input[name="qtd"]');
            const trocaDeValor = parseFloat(trocaDeOption.attr('data-valor')) || 0;
            const trocaParaValor = parseFloat(trocaParaOption.attr('data-valor')) || 0;
            const quantidade = parseInt(quantidadeInput.val(), 10) || 0;

            if (isNaN(trocaDeValor) || isNaN(trocaParaValor) || isNaN(quantidade)) {
                container.find('input[name="saving"]').val('');
                container.find('input[name="saving_show"]').val('').css('color', '');
                return;
            }
            const saving = (trocaDeValor - trocaParaValor) * quantidade;
            container.find('input[name="saving"]').val(saving.toFixed(2));
            container.find('input[name="saving_show"]').val(
                saving >= 0 ? `R$ ${saving.toFixed(2)}` : `-R$ ${Math.abs(saving).toFixed(2)}`
            ).css('color', saving >= 0 ? 'green' : 'red');
        }
    });

    // Segurança extra: antes de enviar, se houver auditor selecionado em algum anexo, marca "em auditoria"
    (function() {
        const fkAudMed = document.getElementById('fk_id_aud_med');
        const fkAudEnf = document.getElementById('fk_id_aud_enf');
        const aberto = document.getElementById('aberto_cap');
        const emAud = document.getElementById('em_auditoria_cap');

        document.getElementById('myForm')?.addEventListener('submit', function() {
            const temMed = fkAudMed && fkAudMed.value;
            const temEnf = fkAudEnf && fkAudEnf.value;
            if (temMed || temEnf) {
                if (aberto) aberto.value = 'n';
                if (emAud) emAud.value = 's';
            }
        });
    })();

    document.addEventListener('DOMContentLoaded', function() {
        window.triggerInternacaoCheck = window.triggerInternacaoCheck || function() {};
        var pacienteSelect = document.getElementById('fk_paciente_int');
        var retroInput = document.getElementById('retroativa_confirmada');
        var retroContainer = document.getElementById('retroativa-container');
        var retroBanner = document.getElementById('retroativa-alert');
        var retroText = document.getElementById('retroativa-alert-text');
        var internadoSelect = document.getElementById('internado_int');
        var dataAltaField = document.getElementById('data_alta_alt');
        var modalEl = document.getElementById('modalInternacaoAtiva');
        var modalInstance = (window.bootstrap && bootstrap.Modal && modalEl) ? new bootstrap.Modal(modalEl) : null;
        var modalHospital = document.getElementById('modalInternacaoHospital');
        var modalData = document.getElementById('modalInternacaoData');
        var confirmBtn = modalEl ? modalEl.querySelector('[data-action="confirm-retroativa"]') : null;
        var cancelBtn = modalEl ? modalEl.querySelector('[data-action="cancel-retroativa"]') : null;
        var activeInfo = null;

        function hideRetroBanner() {
            if (retroContainer) retroContainer.classList.add('d-none');
            if (retroBanner) retroBanner.classList.add('d-none');
            if (retroInput) retroInput.value = '0';
        }

        function showRetroBanner(info) {
            if (!retroBanner || !retroText) return;
            var hosp = info?.hospital || 'hospital não informado';
            var data = info?.data_formatada || 'data não informada';
            retroText.textContent = "Paciente internado no " + hosp + " desde " + data +
                ". Informe a alta ao lançar esta internação retroativa.";
            if (retroContainer) retroContainer.classList.remove('d-none');
            retroBanner.classList.remove('d-none');
        }

        function formatDateTimeLocal(dateObj) {
            if (!(dateObj instanceof Date)) return '';
            var local = new Date(dateObj.getTime() - dateObj.getTimezoneOffset() * 60000);
            return local.toISOString().slice(0, 16);
        }

        function forcarAltaCampos() {
            if (internadoSelect && internadoSelect.value !== 'n') {
                internadoSelect.value = 'n';
                internadoSelect.dispatchEvent(new Event('change'));
            }
            if (dataAltaField && !dataAltaField.value) {
                dataAltaField.value = formatDateTimeLocal(new Date());
            }
        }

        function consultarInternacaoAtiva(pacienteId, skipModal) {
            if (!pacienteId) {
                hideRetroBanner();
                activeInfo = null;
                return;
            }
            fetch('ajax/check_internacao_ativa.php?id_paciente=' + encodeURIComponent(pacienteId))
                .then(function(resp) {
                    return resp.json();
                })
                .then(function(data) {
                    if (!data || !data.success) {
                        throw new Error(data?.error || 'Erro ao consultar internação ativa.');
                    }
                    if (data.hasActive) {
                        activeInfo = data.active;
                        if (modalHospital) modalHospital.textContent = data.active.hospital || '—';
                        if (modalData) modalData.textContent = data.active.data_formatada || '—';
                        if (!skipModal && modalInstance) {
                            modalInstance.show();
                        }
                    } else {
                        activeInfo = null;
                        hideRetroBanner();
                    }
                })
                .catch(function(err) {
                    console.error('Falha ao verificar internação ativa:', err);
                });
        }

        if (pacienteSelect) {
            var onPacienteChange = function() {
                hideRetroBanner();
                consultarInternacaoAtiva(pacienteSelect.value, false);
            };
            pacienteSelect.addEventListener('change', onPacienteChange);
            if (window.jQuery && jQuery.fn && typeof jQuery.fn.on === 'function') {
                jQuery(function($) {
                    $('#fk_paciente_int').on('changed.bs.select', function() {
                        onPacienteChange();
                    });
                });
            }
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (retroInput) retroInput.value = '1';
                if (activeInfo) showRetroBanner(activeInfo);
                forcarAltaCampos();
                modalInstance && modalInstance.hide();
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                modalInstance && modalInstance.hide();
                activeInfo = null;
                var matriculaField = document.getElementById('matricula_paciente_display');
                if (matriculaField) matriculaField.value = '';
                if (pacienteSelect) {
                    pacienteSelect.value = '';
                    if (window.jQuery && jQuery.fn.selectpicker && jQuery(pacienteSelect).hasClass(
                            'selectpicker')) {
                        jQuery(pacienteSelect).selectpicker('refresh');
                    }
                }
            });
        }

        window.triggerInternacaoCheck = function() {
            if (pacienteSelect) {
                consultarInternacaoAtiva(pacienteSelect.value, false);
            }
        };
    });

    document.addEventListener('DOMContentLoaded', function() {
        var senhaInput = document.getElementById('senha_int');
        var senhaModalEl = document.getElementById('modalSenhaDuplicada');
        var senhaModal = (window.bootstrap && bootstrap.Modal && senhaModalEl) ? new bootstrap.Modal(senhaModalEl) : null;
        var senhaTexto = document.getElementById('modalSenhaDuplicadaTexto');
        var senhaDuplicadaFlag = false;

        function verificarSenhaDuplicada(valor) {
            if (!valor) {
                senhaDuplicadaFlag = false;
                return;
            }
            fetch('ajax/check_senha_internacao.php?senha=' + encodeURIComponent(valor))
                .then(function(resp) {
                    return resp.json();
                })
                .then(function(data) {
                    if (data && data.success && data.exists) {
                        senhaDuplicadaFlag = true;
                        if (senhaTexto) {
                            senhaTexto.textContent = 'A senha "' + valor +
                                '" já está vinculada a outra internação. Informe uma senha diferente.';
                        }
                        if (senhaModal) senhaModal.show();
                    } else {
                        senhaDuplicadaFlag = false;
                    }
                })
                .catch(function(err) {
                    console.error('Erro ao verificar senha:', err);
                });
        }

        if (senhaInput) {
            senhaInput.addEventListener('blur', function() {
                var valor = (this.value || '').trim();
                if (valor) verificarSenhaDuplicada(valor);
            });
            senhaInput.addEventListener('input', function() {
                senhaDuplicadaFlag = false;
            });
        }

        window.isSenhaDuplicada = function() {
            return senhaDuplicadaFlag;
        };
    });

    document.addEventListener('paciente:cadastrado', function(event) {
        const data = event.detail || {};
        const novoId = data.id || data.id_paciente;
        if (!novoId) return;
        const select = document.getElementById('fk_paciente_int');
        if (!select) return;

        let option = Array.from(select.options).find(opt => String(opt.value) === String(novoId));
        const label = data.nome || data.nome_pac || `Paciente #${novoId}`;
        const matricula = data.matricula || data.matricula_pac || '';

        if (!option) {
            option = new Option(label, novoId, true, true);
            select.appendChild(option);
        } else {
            option.selected = true;
            option.textContent = label;
        }
        if (matricula) {
            option.setAttribute('data-matricula', matricula);
        }

        if (typeof window.sortPacienteOptionsDesc === 'function') {
            window.sortPacienteOptionsDesc();
        }

        if (window.$ && $.fn.selectpicker && $(select).hasClass('selectpicker')) {
            $(select).selectpicker('refresh');
            $(select).selectpicker('val', String(novoId));
        } else {
            select.value = novoId;
        }
        select.dispatchEvent(new Event('change', {
            bubbles: true
        }));
    });
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
<!-- <script src="<?= $BASE_URL ?>js/saude-autocomplete.js?v=2"></script> -->
