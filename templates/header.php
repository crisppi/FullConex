<?php

include_once("globals.php");
include_once("db.php");
date_default_timezone_set('America/Sao_Paulo');
header("Content-type: text/html; charset=utf-8");

// Caminho default
$defaultFoto = $BASE_URL . 'img/user-default.png';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$sessionNivel = isset($_SESSION['nivel']) ? (int) $_SESSION['nivel'] : 0;
$sessionUsuario = $_SESSION['usuario_user'] ?? '';
$sessionIdUsuario = $_SESSION['id_usuario'] ?? null;

$chatUnreadCount = 0;
$chatAssistantLink = $BASE_URL . 'show_chat.php';
if (!empty($sessionIdUsuario)) {
    try {
        $stmtChat = $conn->prepare("SELECT COUNT(*) FROM tb_mensagem WHERE para_usuario = :para AND vista = 0");
        $stmtChat->bindValue(':para', (int) $sessionIdUsuario, PDO::PARAM_INT);
        $stmtChat->execute();
        $chatUnreadCount = (int) $stmtChat->fetchColumn();
    } catch (Exception $e) {
        $chatUnreadCount = 0;
    }

    try {
        require_once __DIR__ . '/../app/services/AssistenteVirtualService.php';
        $headerAssistantService = new AssistenteVirtualService($conn, $BASE_URL);
        $chatAssistantLink = $BASE_URL . 'show_chat.php?para_usuario=' . $headerAssistantService->getAssistantUserId();
    } catch (Throwable $th) {
        $chatAssistantLink = $BASE_URL . 'show_chat.php';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCare</title>
    <base href="<?= $BASE_URL ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= $BASE_URL ?>img/full-ico.ico">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/css/font-face.css" rel="stylesheet" media="all">
    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/vendor/mdi-font/css/material-design-iconic-font.min.css"
        rel="stylesheet" media="all">
    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/vendor/animsition/animsition.min.css" rel="stylesheet"
        media="all">
    <link
        href="<?= $BASE_URL ?>diversos/CoolAdmin-master/vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css"
        rel="stylesheet" media="all">
    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet"
        media="all">
    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/vendor/perfect-scrollbar/perfect-scrollbar.css"
        rel="stylesheet" media="all">
    <link href="<?= $BASE_URL ?>diversos/CoolAdmin-master/css/theme.css" rel="stylesheet" media="all">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css">
    <link href="<?= $BASE_URL ?>css/style.css" rel="stylesheet">
    <link href="<?= $BASE_URL ?>css/legendas.css" rel="stylesheet">
    <link href="<?= $BASE_URL ?>css/styleMenu.css?v=<?= @filemtime(__DIR__ . '/../css/styleMenu.css') ?>" rel="stylesheet">
    <link href="<?= $BASE_URL ?>css/style_show_internacao.css" rel="stylesheet">

    <!-- ======= APENAS DESIGN (logos alinhados e simétricos) ======= -->
    <style>
    .navbar .navbar-brand {
        display: inline-flex !important;
        align-items: center;
        line-height: 1;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .navbar .navbar-brand .logo-novo {
        height: 56px !important;
        width: auto !important;
        max-height: none !important;
        min-height: 0 !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    @media (max-width: 1199.98px) {
        .navbar .navbar-brand .logo-novo {
            height: 52px !important;
        }
    }

    @media (max-width: 575.98px) {
        .navbar .navbar-brand .logo-novo {
            height: 48px !important;
        }
    }

    .header-actions {
        margin-left: auto !important;
        margin-right: 0 !important;
        gap: 0.75rem !important;
    }

    .header-actions #global-patient-search {
        min-width: 300px;
        flex: 0 0 auto;
    }

    .header-chat-launcher {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .header-chat-launcher .chat-unread-badge {
        font-size: 0.65rem;
    }

    #search-results-dropdown {
        z-index: 2000;
    }

    #search-results-dropdown .dropdown-item {
        white-space: normal;
        line-height: 1.2;
    }

    #search-results-dropdown .dropdown-item.active,
    #search-results-dropdown .dropdown-item:focus,
    #search-results-dropdown .dropdown-item:hover {
        background: #f2f6ff;
        color: #1f1f1f;
    }

    #search-results-dropdown .dropdown-item small {
        color: #5c5c5c;
    }

    @media (max-width: 575.98px) {
        .header-actions {
            width: 100%;
        }

        .header-actions #global-patient-search {
            min-width: 0;
            width: 100%;
        }
    }
    </style>
</head>

<body>
    <button type="button" id="return-flow-btn"
        style="display:none;position:fixed;bottom:24px;right:24px;z-index:1100;padding:0.65rem 1.2rem;border:none;border-radius:999px;background:#5e2363;color:#fff;font-weight:600;box-shadow:0 12px 25px rgba(94,35,99,0.35);cursor:pointer;">
        Voltar ao fluxo anterior
    </button>
    <div class="col-md-12" style="padding:0 !important">
        <nav class="navbar navbar-expand-lg navbar-light bg-light nav_bar_custom fixed-top">
            <div class="bar_color" style="position:fixed;top:0;z-index:1000;width:100%;height:5px;background-image: linear-gradient(to right, #5e2363,#5bd9f3);
            ">
            </div>
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <img src="<?= $BASE_URL ?>img/LogoFullCare.png" class="logo-novo" style="max-width: 100%;
                        height: auto;
                        width: auto\9;
                        max-height: 100px;
                        min-height: 50px;" alt="FullCare">
                </a>
                <div class="collapse navbar-collapse" id="navbarScroll">
                    <ul class="nav-tabs navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll align-items-center"
                        style="--bs-scroll-height: 80px;">
                        <!-- Ícone de mensagem -->

                        <?php if ($sessionNivel > 0) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="bi bi-stack edit-icon"></i>
                                Menu
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>dashboard"><i
                                            class="bi bi-speedometer2"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>
                                        Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>manual.html"><i class="bi bi-person"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>
                                        Manual</a></li>
                                <?php }; ?>
                            </ul>
                        </li>

                        <?php if ($sessionNivel > 3) { ?>
                        <li id="drop1" class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="<?= $BASE_URL ?>pacientes"
                                id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="bi bi-people-fill edit-icon"></i>
                                Usuários
                            </a>
                            <ul class="dropdown-menu" id="dropMenu1" aria-labelledby="navbarScrollingDropdown">

                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_usuario.php"><i
                                            class="bi bi-file-medical"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(155, 95, 76);"></i>
                                        Pesquisa Usuários</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_hospitalUser.php"><i
                                            class="bi bi-person-badge"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(15, 155, 176);"></i>
                                        Hospital por Usuário</a>
                                </li>
                            </ul>
                        </li>

                        <?php }; ?>
                        <?php if ($sessionNivel > 3) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="fa-solid fa-pen-to-square edit-icon"></i>
                                Cadastros
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>pacientes"><i class="bi bi-person"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>
                                        Pacientes</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>hospitais"><span
                                            class="bi bi-hospital"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(67, 125, 525);"></span>
                                        Hospitais</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>seguradoras"><span
                                            class=" bi bi-heart-pulse"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(178, 156, 55);"></span>
                                        Seguradora</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>estipulantes"><i
                                            class="bi bi-building"
                                            style="font-size:  1rem;margin-right:5px; color: rgb(213, 12, 155);"></i>
                                        Estipulante</a></li>
                                <li>

                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_acomodacao.php"><i
                                            class=" bi bi-clipboard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(145, 156, 55);"></i>
                                        Acomodação</a></li>
                                <!-- <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_patologia.php"><span
                                            class=" bi bi-virus"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(178, 155, 155);"></span>
                                        Patologia</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_antecedente.php"><i
                                            class="bi bi-people"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(178, 156, 55);"></i>
                                        Antecedente</a></li> -->
                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($sessionNivel >= 3) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="fa-solid fa-calendar edit-icon"></i>
                                Produção
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">

                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>internacoes/nova"><i
                                            class="bi bi-calendar2-date"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i> Nova
                                        Internação</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>censo/lista"><i class="bi bi-book"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(222, 156, 55);"></i>
                                        Censo</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>internacoes/lista"> <i
                                            class="bi bi-calendar2-date"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>

                                        Internação</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>internacoes/uti"> <i
                                            class="bi bi-clipboard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                        Internação UTI</a>
                                </li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>gestao"><i
                                            class="bi bi-postcard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(144, 17, 194);"></i>
                                        Gestão</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <!-- <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_internacao_uti_alta.php"><span
                                            id="boot-icon3" class="bi bi-box-arrow-left"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(167, 25, 55);"></span>
                                        Alta UTI</a></li> -->
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>internacoes/reverter-alta"><span
                                            id="boot-icon3" class="bi bi-postcard-heart"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(16, 15, 155);"></span>
                                        Reverter altas</a>
                                </li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>internacoes/gerar-alta"><span
                                            class="bi bi-clipboard-check"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(9, 132, 227);"></span>
                                        Gerar altas</a>
                                </li>
                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($sessionNivel >= 3): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dropdownContasRah" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-journal-richtext me-1" style="color:#5e2363;"></i>Contas
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownContasRah">
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_internacao_cap_rah.php">
                                        <i class="bi bi-currency-dollar text-success me-2"></i>Contas para Auditar
                                    </a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_internacao_cap_fin.php">
                                        <i class="bi bi-shield-check text-primary me-2"></i>Contas Finalizadas
                                    </a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_internacao_senha_fin.php">
                                        <i class="bi bi-bookmark-check text-danger me-2"></i>Senhas Finalizadas
                                    </a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_internacao_cap_par.php">
                                        <i class="bi bi-pause-circle text-warning me-2"></i>Contas Paradas
                                    </a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_internacao_cap_jornada.php">
                                        <i class="bi bi-diagram-3 text-info me-2"></i>Jornada da Conta
                                    </a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if ($sessionNivel >= 3) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="fa-solid fa-list edit-icon"></i>
                                Listas
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">

                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>censo/lista"><i class="bi bi-book"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(222, 156, 55);"></i>
                                        Censo</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>internacoes/lista"> <i
                                            class="bi bi-calendar2-date"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>
                                        Internação</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>internacoes/uti"> <i
                                            class="bi bi-clipboard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                        Internação UTI</a>
                                </li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>gestao"><i
                                            class="bi bi-postcard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(144, 17, 194);"></i>
                                        Gestão</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>listas/altas"><i
                                            class="bi bi-clipboard-check"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(9,132,227);"></i>
                                        Lista de altas</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>internacoes/ciclo"><i
                                            class="bi bi-postcard-heart"
                                            style="font-size:  1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                        Rota do Paciente</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>visitas/lista"><i
                                            class="bi bi-postcard-heart"
                                            style="font-size:  1rem;margin-right:5px; color: rgb(27,156, 55);"></i>
                                        Lista de Visitas</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>internacoes/sem-senha"><i
                                            class="bi bi-shield-exclamation"
                                            style="font-size:  1rem;margin-right:5px; color:#d63384;"></i>
                                        Internações sem senha</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>negociacoes"><i
                                            class="bi bi-currency-dollar"
                                            style="font-size: 1rem;margin-right:5px; color:#0d6efd;"></i>
                                        Negociações</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>negociacoes/graficos"><i
                                            class="bi bi-bar-chart"
                                            style="font-size: 1rem;margin-right:5px; color:#20c997;"></i>
                                        Gráfico Negociações</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_fila_tarefas.php"><i
                                            class="bi bi-list-check"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(20, 120, 90);"></i>
                                        Fila de Tarefas</a></li>

                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($sessionNivel >= 3) { ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="fa-solid fa-file-invoice edit-icon"></i>
                                Faturamento
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>visitas/lista"><i
                                            class="bi bi-list-check"
                                            style="font-size: 1rem;margin-right:5px; color:#5e2363;"></i>
                                        Lista Visitas</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>faturamento_visitas.php"><i
                                            class="bi bi-clipboard-check"
                                            style="font-size: 1rem;margin-right:5px; color:#0a4fa3;"></i>
                                        Faturamento Visitas</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>faturamento_mensal.php"><i
                                            class="bi bi-calendar-range"
                                            style="font-size: 1rem;margin-right:5px; color:#0a6840;"></i>
                                        Faturamento Mensal Visitas</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>faturamento_mensal_contas.php"><i
                                            class="bi bi-calendar3"
                                            style="font-size: 1rem;margin-right:5px; color:#b35400;"></i>
                                        Faturamento Mensal Contas</a></li>
                                <!-- <li><a class="dropdown-item" href="<?= $BASE_URL ?>list_internacao_cap_fin.php"><i
                                            class="bi bi-card-checklist"
                                            style="font-size: 1rem;margin-right:5px; color:rgb(28, 118, 175);"></i>
                                        Contas</a></li> -->
                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($sessionNivel >= 3) { ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="bi bi-bar-chart-line edit-icon"></i>
                                BI
                            </a>
                            <ul class="dropdown-menu bi-dropdown bi-mega" aria-labelledby="navbarScrollingDropdown">
                                <li class="bi-mega-col">
                                    <span class="bi-mega-title">Resumo</span>
                                    <ul class="bi-mega-list">
                                        <li><a class="dropdown-item bi-dropdown-featured" href="<?= $BASE_URL ?>bi_navegacao.php"><i
                                                    class="bi bi-grid-3x3-gap"
                                                    style="font-size: 1rem;margin-right:5px; color:#9fd7ff;"></i>
                                                Navegacao</a></li>
                                        <li class="bi-submenu">
                                            <a class="dropdown-item" href="#"><i
                                                        class="bi bi-layers"
                                                        style="font-size: 1rem;margin-right:5px; color:#7cc4ff;"></i>
                                                Consolidado</a>
                                            <ul class="bi-submenu-list">
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>ConsolidadoGestaoCardsBI.php"><i
                                                            class="bi bi-grid"
                                                            style="font-size: 1rem;margin-right:5px; color:#7cc4ff;"></i>
                                                        Consolidado Gestao Cards</a></li>
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>ConsolidadoGestaoBI.php"><i
                                                            class="bi bi-layers"
                                                            style="font-size: 1rem;margin-right:5px; color:#7cc4ff;"></i>
                                                        Consolidado Gestao</a></li>
                                            </ul>
                                        </li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>Indicadores.php"><i
                                                    class="bi bi-speedometer2"
                                                    style="font-size: 1rem;margin-right:5px; color:#9fd7ff;"></i>
                                                Indicadores BI</a></li>
                                    </ul>
                                </li>
                                <li class="bi-mega-col">
                                    <span class="bi-mega-title">Clinico</span>
                                    <ul class="bi-mega-list">
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_uti.php"><i
                                                    class="bi bi-heart-pulse"
                                                    style="font-size: 1rem;margin-right:5px; color:#ff9fb3;"></i>
                                                UTI</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_patologia.php"><i
                                                    class="bi bi-clipboard2-pulse"
                                                    style="font-size: 1rem;margin-right:5px; color:#b7d3ff;"></i>
                                                Patologia</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>GrupoPatologia.php"><i
                                                    class="bi bi-diagram-3"
                                                    style="font-size: 1rem;margin-right:5px; color:#a6d8c0;"></i>
                                                Grupo Patologia</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>Antecedente.php"><i
                                                    class="bi bi-journal-text"
                                                    style="font-size: 1rem;margin-right:5px; color:#f0b67f;"></i>
                                                Antecedente</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>LongaPermanenciaBI.php"><i
                                                    class="bi bi-hourglass-split"
                                                    style="font-size: 1rem;margin-right:5px; color:#9ad0f5;"></i>
                                                Longa Permanencia</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>ClinicoRealizadoBI.php"><i
                                                    class="bi bi-activity"
                                                    style="font-size: 1rem;margin-right:5px; color:#8bd3ff;"></i>
                                                Clínico Realizado</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>EstrategiaTerapeuticaBI.php"><i
                                                    class="bi bi-compass"
                                                    style="font-size: 1rem;margin-right:5px; color:#c6b5e8;"></i>
                                                Estrategia Terapeutica</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>MedicoTitularBI.php"><i
                                                    class="bi bi-person-badge"
                                                    style="font-size: 1rem;margin-right:5px; color:#ffb3d1;"></i>
                                                Medico Titular</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>AuditorBI.php"><i
                                                    class="bi bi-person-lines-fill"
                                                    style="font-size: 1rem;margin-right:5px; color:#b7d3ff;"></i>
                                                Auditor</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>AuditorVisitasBI.php"><i
                                                    class="bi bi-table"
                                                    style="font-size: 1rem;margin-right:5px; color:#9fd7ff;"></i>
                                                Auditor Visitas</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>AuditoriaProdutividadeBI.php"><i
                                                    class="bi bi-bar-chart"
                                                    style="font-size: 1rem;margin-right:5px; color:#9fd7ff;"></i>
                                                Auditoria Produtividade</a></li>
                                    </ul>
                                </li>
                                <li class="bi-mega-col">
                                    <span class="bi-mega-title">Operacional</span>
                                    <ul class="bi-mega-list">
                                        <li class="bi-submenu">
                                            <a class="dropdown-item" href="#"><i
                                                        class="bi bi-shield-check"
                                                        style="font-size: 1rem;margin-right:5px; color:#8dd0ff;"></i>
                                                Seguradora</a>
                                            <ul class="bi-submenu-list">
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>SeguradoraBI.php"><i
                                                            class="bi bi-shield-check"
                                                            style="font-size: 1rem;margin-right:5px; color:#8dd0ff;"></i>
                                                        Seguradora</a></li>
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>SeguradoraDetalhadoBI.php"><i
                                                            class="bi bi-shield-plus"
                                                            style="font-size: 1rem;margin-right:5px; color:#8dd0ff;"></i>
                                                        Seguradora Detalhado</a></li>
                                            </ul>
                                        </li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>AltoCusto.php"><i
                                                    class="bi bi-exclamation-diamond"
                                                    style="font-size: 1rem;margin-right:5px; color:#ff9fb3;"></i>
                                                Alto Custo</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>InternacoesRiscoBI.php"><i
                                                    class="bi bi-exclamation-triangle"
                                                    style="font-size: 1rem;margin-right:5px; color:#ffb86c;"></i>
                                                Internações com Risco</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>QualidadeGestaoBI.php"><i
                                                    class="bi bi-clipboard-data"
                                                    style="font-size: 1rem;margin-right:5px; color:#9ad0f5;"></i>
                                                Qualidade e Gestão</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>HomeCare.php"><i
                                                    class="bi bi-house-heart"
                                                    style="font-size: 1rem;margin-right:5px; color:#9ad0f5;"></i>
                                                Home Care</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>Desospitalizacao.php"><i
                                                    class="bi bi-arrow-down-right-circle"
                                                    style="font-size: 1rem;margin-right:5px; color:#c6b5e8;"></i>
                                                Desospitalização</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>Opme.php"><i
                                                    class="bi bi-bandaid"
                                                    style="font-size: 1rem;margin-right:5px; color:#c8a6ff;"></i>
                                                OPME</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>EventoAdverso.php"><i
                                                    class="bi bi-exclamation-octagon"
                                                    style="font-size: 1rem;margin-right:5px; color:#ff9f9f;"></i>
                                                Evento Adverso</a></li>
                                    </ul>
                                </li>
                                <li class="bi-mega-col">
                                    <span class="bi-mega-title">Financeiro</span>
                                    <ul class="bi-mega-list">
                                        <li class="bi-submenu">
                                            <a class="dropdown-item" href="#"><i
                                                        class="bi bi-clipboard-data"
                                                        style="font-size: 1rem;margin-right:5px; color:#ff8fb1;"></i>
                                                Sinistro</a>
                                            <ul class="bi-submenu-list">
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>Sinistro.php"><i
                                                            class="bi bi-clipboard-data"
                                                            style="font-size: 1rem;margin-right:5px; color:#ff8fb1;"></i>
                                                        Sinistro BI</a></li>
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_sinistro_ytd.php"><i
                                                            class="bi bi-bar-chart-steps"
                                                            style="font-size: 1rem;margin-right:5px; color:#8dd0ff;"></i>
                                                        Sinistro YTD</a></li>
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_perfil_sinistro.php"><i
                                                            class="bi bi-pie-chart"
                                                            style="font-size: 1rem;margin-right:5px; color:#7cc4ff;"></i>
                                                        Perfil Sinistro</a></li>
                                            </ul>
                                        </li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>FinanceiroRealizadoBI.php"><i
                                                    class="bi bi-currency-exchange"
                                                    style="font-size: 1rem;margin-right:5px; color:#ffd166;"></i>
                                                Financeiro Realizado</a></li>
                                        <li class="bi-submenu">
                                            <a class="dropdown-item" href="#"><i
                                                        class="bi bi-graph-up"
                                                        style="font-size: 1rem;margin-right:5px; color:#d071b0;"></i>
                                                Produção</a>
                                            <ul class="bi-submenu-list">
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>Producao.php"><i
                                                            class="bi bi-graph-up"
                                                            style="font-size: 1rem;margin-right:5px; color:#d071b0;"></i>
                                                        Producao BI</a></li>
                                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_producao_ytd.php"><i
                                                            class="bi bi-graph-up"
                                                            style="font-size: 1rem;margin-right:5px; color:#d071b0;"></i>
                                                        Produção YTD</a></li>
                                            </ul>
                                        </li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_saving.php"><i
                                                    class="bi bi-cash-coin"
                                                    style="font-size: 1rem;margin-right:5px; color:#5fd3b5;"></i>
                                                Saving</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_pacientes.php"><i
                                                    class="bi bi-people"
                                                    style="font-size: 1rem;margin-right:5px; color:#ffc56c;"></i>
                                                Pacientes</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_hospitais.php"><i
                                                    class="bi bi-hospital"
                                                    style="font-size: 1rem;margin-right:5px; color:#a2b5ff;"></i>
                                                Hospitais</a></li>
                                        <li><a class="dropdown-item" href="<?= $BASE_URL ?>bi_inteligencia.php"><i
                                                    class="bi bi-lightbulb"
                                                    style="font-size: 1rem;margin-right:5px; color:#8bd3ff;"></i>
                                                Inteligencia Artificial</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($sessionNivel > 0) { ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="bi bi-robot edit-icon"></i>
                                Inteligência Operacional
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>dashboard_operacional.php"><i
                                            class="bi bi-grid-3x3-gap"
                                            style="font-size: 1rem;margin-right:5px; color:#5e2363;"></i>
                                        Dashboard 360°</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>dashboard_performance.php"><i
                                            class="bi bi-trophy"
                                            style="font-size: 1rem;margin-right:5px; color:#7c3aed;"></i>
                                        Performance equipes</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>faturamento_previsao.php"><i
                                            class="bi bi-graph-up-arrow"
                                            style="font-size: 1rem;margin-right:5px; color:#1d9ad8;"></i>
                                        Previsão faturamento</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>dashboard_mensal.php"><i
                                            class="bi bi-graph-up-arrow"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(94, 35, 99);"></i>
                                        Painel Mensal</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>inteligencia_operadora.php"><i
                                            class="bi bi-shield-check"
                                            style="font-size: 1rem;margin-right:5px; color:#0ea5e9;"></i>
                                        Inteligência da Operadora</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>relatorio_tmp.php"><i
                                            class="bi bi-activity"
                                            style="font-size: 1rem;margin-right:5px; color:#0ea5e9;"></i>
                                        TMP por CID/Procedimento/Convênio</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>relatorio_prorrogacao_vs_alta.php"><i
                                            class="bi bi-hourglass-split"
                                            style="font-size: 1rem;margin-right:5px; color:#f59e0b;"></i>
                                        Prorrogação vs Alta no prazo</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>relatorio_motivos_prorrogacao.php"><i
                                            class="bi bi-list-check"
                                            style="font-size: 1rem;margin-right:5px; color:#10b981;"></i>
                                        Motivos de Prorrogação</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>relatorio_backlog_autorizacoes.php"><i
                                            class="bi bi-card-checklist"
                                            style="font-size: 1rem;margin-right:5px; color:#ef4444;"></i>
                                        Backlog de Autorizações</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>operational_intelligence.php"><i
                                            class="bi bi-robot"
                                            style="font-size: 1rem;margin-right:5px; color:#20c997;"></i>
                                        Previsões Operacionais</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>permanencia_alertas.php"><i
                                            class="bi bi-clock-history"
                                            style="font-size: 1rem;margin-right:5px; color:#0d9488;"></i>
                                        Permanências e alertas</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>explicabilidade_insights.php"><i
                                            class="bi bi-lightbulb"
                                            style="font-size: 1rem;margin-right:5px; color:#f97316;"></i>
                                        Insights explicáveis</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>risco_glosa.php"><i
                                            class="bi bi-exclamation-octagon"
                                            style="font-size: 1rem;margin-right:5px; color:#ef4444;"></i>
                                        Oportunidade de glosa / contas</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>clusterizacao_clinica.php"><i
                                            class="bi bi-diagram-3"
                                            style="font-size: 1rem;margin-right:5px; color:#0ea5e9;"></i>
                                        Clusterização clínica</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>text_automation.php"><i
                                            class="bi bi-pencil-square"
                                            style="font-size: 1rem;margin-right:5px; color:#fb923c;"></i>
                                        Assistente de Textos</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>solicitacao_customizacao_pdf.php"
                                        target="_blank">
                                        <i class="bi bi-file-earmark-text"
                                            style="font-size: 1rem;margin-right:5px; color: #5e2363;"></i>
                                        Solicitação de Customização (PDF)
                                    </a></li>
                                <?php if ($sessionNivel > 3) { ?>
                                <li class="nav-item">
                                    <a class="dropdown-item" href="<?= $BASE_URL ?>admin_permissao.php">
                                        <i class="bi bi-shield-lock"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(21, 56, 210);"></i>
                                        Permissões
                                    </a>
                                </li>
                                <?php }; ?>
                            </ul>
                        </li>
                        <?php }; ?>
                        <!-- <?php if ($_SESSION['nivel'] >= 2) { ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size: 1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="fa-solid fa-pills edit-icon"></i>
                                DRG
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item"
                                        href="<?php $BASE_URL ?>list_internacao_patologia.php"><span id="boot-icon1"
                                            class="bi bi-capsule-pill"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(77, 155, 67);"> </span>
                                        Pesquisa internações
                                    </a></li>
                                <li>
                            </ul>
                        </li>
                        <?php }; ?> -->
                        <!-- <?php if ($_SESSION['nivel'] > 3) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i style="font-size:  1rem;margin-right:5px; color:#5e2363;" name="type" value="edite"
                                    class="fa-solid fa-print edit-icon"></i>
                                Relatórios
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>relatorios.php"><span
                                            id="boot-icon1" class="bi bi-clipboard-data"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(77, 155, 67);">
                                        </span> Relatórios </a></li>
                                <li>
                                <li><a class="dropdown-item"
                                        href="https://app.powerbi.com/reportEmbed?reportId=162595d1-241c-45dc-b282-e5134dc77636&autoAuth=true&ctid=5d8203ef-bc77-4057-86a0-56d58ebd6258">
                                        <span id="boot-icon1" class="bi bi-clipboard-data"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(77, 155, 67);">
                                        </span> Relatórios - APP</a></li>
                                <li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>relatorios_capeante.php"><span
                                            id="boot-icon1" class="bi bi-clipboard-data"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(77, 155, 67);">
                                        </span> Relatórios Capeantes</a></li>
                                <li>
                            </ul>
                        </li>

                        <?php }; ?>
                    </ul> -->
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto header-actions pe-3">
                <a href="<?= htmlspecialchars($chatAssistantLink) ?>"
                    class="btn btn-outline-secondary position-relative header-chat-launcher"
                    title="Chat interno e Assistente Virtual">
                    <i class="bi bi-chat-dots"></i>
                    <span class="d-none d-xl-inline ms-1">Chat</span>
                    <?php if ($chatUnreadCount > 0): ?>
                    <span
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger chat-unread-badge">
                        <?= $chatUnreadCount > 99 ? '99+' : $chatUnreadCount ?>
                    </span>
                    <?php endif; ?>
                </a>
                <form class="d-flex position-relative" id="global-patient-search" autocomplete="off">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="inp-search-paciente"
                            placeholder="Pesquisar por senha, matrícula ou nome"
                            aria-label="Buscar por senha, matrícula ou nome" />
                    </div>

                    <div id="search-results-dropdown" class="dropdown-menu show"
                        style="display:none; max-height: 350px; overflow:auto; width: 420px; position:absolute; top:100%; left:0; z-index: 2000;">
                    </div>
                </form>

                <div class="account-wrap">
                    <div class="account-item clearfix js-item-menu" style="margin-right:0">
                        <div class="image" style="margin-top:15px">
                            <?php
                            // imagem padrão
                            $defaultFoto = $BASE_URL . 'uploads/usuarios/default-user.jpeg';

                            // arquivo da sessão (sanitizado) e checagem no filesystem
                            $sessFoto  = $_SESSION['foto_usuario'] ?? '';
                            $fileName  = $sessFoto ? basename($sessFoto) : '';
                            $fsPath    = __DIR__ . '/uploads/usuarios/' . $fileName;
                            $urlFoto   = ($fileName && is_file($fsPath))
                                ? ($BASE_URL . 'uploads/usuarios/' . $fileName)
                                : $defaultFoto;
                            ?>
                            <img src="<?= htmlspecialchars($urlFoto) ?>" alt="Usuário"
                                onerror="this.onerror=null;this.src='<?= $defaultFoto ?>';" />
                        </div>
                        <div class="content">
                            <a class="js-acc-btn" href="#"><?php print $sessionUsuario ?></a>
                        </div>
                        <div class="account-dropdown js-dropdown">

                            <!-- <div class="account-dropdown__body">
                                <div class="account-dropdown__item">
                                    <a href="#">
                                        <i class="zmdi zmdi-account"></i>Account</a>
                                </div>
                                <div class="account-dropdown__item">
                                    <a href="#">
                                        <i class="zmdi zmdi-settings"></i>Setting</a>
                                </div>
                                <div class="account-dropdown__item">
                                    <a href="#">
                                        <i class="zmdi zmdi-money-box"></i>Billing</a>
                                </div>
                            </div> -->
                            <div class="account-dropdown__footer">
                                <a href="<?php $BASE_URL ?>destroi.php">
                                    <i class="zmdi zmdi-power"></i>Sair</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- notification message -->
        <?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
        <?php
        $flashMsg  = $_SESSION['mensagem']      ?? '';
        $flashType = $_SESSION['mensagem_tipo'] ?? 'danger';
        unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']);
        ?>
        <?php if ($flashMsg): ?>
        <div class="container mt-3">
            <div id="app-flash"
                class="alert alert-<?= htmlspecialchars($flashType) ?> text-center alert-dismissible fade show"
                role="alert">
                <?= htmlspecialchars($flashMsg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        </div>

        <script>
        (function() {
            var el = document.getElementById('app-flash');
            if (!el) return;

            // fecha visualmente ~9.8s (para dar tempo da transição)
            setTimeout(function() {
                try {
                    if (window.bootstrap && bootstrap.Alert) {
                        bootstrap.Alert.getOrCreateInstance(el).close();
                    } else {
                        el.classList.remove('show'); // some a classe de exibição
                    }
                } catch (e) {}
            }, 9800);

            // remove do DOM em 10s (garantia)
            setTimeout(function() {
                if (el && el.parentNode) el.parentNode.removeChild(el);
            }, 5000);
        })();
        </script>
        <?php endif; ?>

        <div class="modal fade" id="globalModal">
            <div class="modal-dialog  modal-lg modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div style="padding-left:20px;padding-top:20px;">
                        <h4>Paciente</h4>
                        <p class="page-description">Informações
                            do paciente</p>
                    </div>
                    <div class="modal-body">
                        <div id="global-content-php"></div>
                    </div>
                </div>
            </div>
        </div>

</body>
<script src="js/fix-header.js"></script>

<!-- Jquery JS-->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>

<!-- Bootstrap JS-->
<script src="./diversos/CoolAdmin-master/vendor/bootstrap-4.1/popper.min.js"></script>
<script src="./diversos/CoolAdmin-master/vendor/bootstrap-4.1/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<!-- Vendor JS       -->
<script src="./diversos/CoolAdmin-master/vendor/slick/slick.min.js">
</script>
<script src="./diversos/CoolAdmin-master/vendor/wow/wow.min.js"></script>
<script src="./diversos/CoolAdmin-master/vendor/animsition/animsition.min.js"></script>
<script src="./diversos/CoolAdmin-master/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
</script>
<script src="./diversos/CoolAdmin-master/vendor/counter-up/jquery.waypoints.min.js"></script>
<script src="./diversos/CoolAdmin-master/vendor/counter-up/jquery.counterup.min.js">
</script>
<script src="./diversos/CoolAdmin-master/vendor/circle-progress/circle-progress.min.js"></script>
<script src="./diversos/CoolAdmin-master/vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="./diversos/CoolAdmin-master/vendor/chartjs/Chart.bundle.min.js"></script>
<script src="./diversos/CoolAdmin-master/vendor/select2/select2.min.js"></script>
<script src="./scripts/cadastro/general.js"></script>
<script src="js/stepper.js"></script>
<script src="js/show_internacao_visitas.js"></script>
<script src="<?= $BASE_URL ?>js/contextual-assistant.js"></script>
</script>
<script>
// Base para links absolutos
const BASE_URL = '<?= $BASE_URL ?>';

function setupModalForms(container, modalEl) {
    if (!container || !modalEl) return;
    const forms = container.querySelectorAll('form');
    forms.forEach((form) => {
        if (form.dataset.modalAjaxBound === '1') return;
        form.dataset.modalAjaxBound = '1';

        form.addEventListener('submit', function modalFormSubmit(ev) {
            if (!modalEl.contains(form)) return;
            ev.preventDefault();

            const action = form.getAttribute('action') || window.location.href;
            const method = (form.getAttribute('method') || 'POST').toUpperCase();
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(action, {
                    method,
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(resp => {
                    const ct = resp.headers.get('content-type') || '';
                    if (ct.includes('application/json')) {
                        return resp.json();
                    }
                    return resp.text().then(html => ({
                        html
                    }));
                })
                .then(payload => {
                    if (payload && payload.success) {
                        if (window.bootstrap && bootstrap.Modal) {
                            const inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            inst.hide();
                        } else if (window.$ && typeof $('#globalModal').modal === 'function') {
                            $('#globalModal').modal('hide');
                        }
                        document.dispatchEvent(new CustomEvent('modalFormSuccess', {
                            detail: payload
                        }));
                        if (payload.paciente) {
                            document.dispatchEvent(new CustomEvent('paciente:cadastrado', {
                                detail: payload.paciente
                            }));
                        }
                        return;
                    }
                    if (payload && payload.html) {
                        const temp = document.createElement('div');
                        temp.innerHTML = payload.html;
                        let inner = temp.querySelector('#main-container') || temp.querySelector('main') || temp.querySelector('body');
                        const html = inner ? inner.innerHTML : payload.html;
                        renderModalBody(container, html, modalEl);
                        return;
                    }
                    throw new Error('Resposta inesperada');
                })
                .catch(() => {
                    container.innerHTML = '<div class="p-4 text-danger">Erro ao processar o formulário.</div>';
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    });
}

function renderModalBody(target, html, modalEl) {
    if (!target) return;
    target.innerHTML = html;

    try {
        if (window.$ && typeof $('.selectpicker').selectpicker === 'function') {
            $('.selectpicker', target).selectpicker();
            $('.selectpicker', target).selectpicker('refresh');
        }
    } catch (_) {}

    setupModalForms(target, modalEl);
}

if (typeof window.openModalPac !== 'function') {
    window.openModalPac = function(url, titulo = 'Cadastro') {
        const modalEl = document.getElementById('globalModal');
        if (!modalEl) {
            console.warn('[openModalPac] #globalModal não encontrado. Navegando para:', url);
            window.location.href = url;
            return;
        }

        const body = modalEl.querySelector('.modal-body');
        const titleEl = modalEl.querySelector('.modal-title');
        if (titleEl) titleEl.textContent = titulo;
        body.innerHTML = '<div class="p-4 text-center text-muted">Carregando...</div>';

        // Bootstrap 5.0/5.1: não tem getOrCreateInstance
        let bsModal = null;
        if (window.bootstrap && bootstrap.Modal) {
            if (typeof bootstrap.Modal.getInstance === 'function') {
                bsModal = bootstrap.Modal.getInstance(modalEl);
            }
            if (!bsModal) {
                bsModal = new bootstrap.Modal(modalEl); // 5.0/5.1 OK
            }
            bsModal.show();
        } else if (window.$ && typeof $('#globalModal').modal === 'function') {
            // fallback jQuery/BS4
            $('#globalModal').modal('show');
        }

        fetch(url, {
                credentials: 'same-origin'
            })
            .then(r => r.text())
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html;
                let inner = temp.querySelector('#main-container') || temp.querySelector('main') || temp.querySelector('body');
                const resolvedHtml = inner ? inner.innerHTML : html;
                renderModalBody(body, resolvedHtml, modalEl);
            })
            .catch(err => {
                console.error(err);
                body.innerHTML = '<div class="p-4 text-danger">Falha ao carregar conteúdo do modal.</div>';
            });
    };
}

// --- debounce simples ---
function debounce(fn, wait) {
    let t;
    return function(...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    }
}

const $input = $('#inp-search-paciente');
const $menu = $('#search-results-dropdown');

// Renderiza itens no dropdown
function renderResults(items) {
    if (!items || !items.length) {
        const termo = $input.val().trim();
        $menu.html(`
        <div class="dropdown-item text-muted">Nada encontrado. Tente outra senha, matrícula ou nome.</div>
        <a href="#" id="create-new-pac" class="dropdown-item d-flex justify-content-between align-items-center">
            <div>
                <div><strong>Cadastrar novo paciente</strong></div>
                ${termo ? `<small class="text-muted">Iniciar cadastro com: <em>${termo}</em></small>` : ''}
            </div>
            <i class="bi bi-plus-circle"></i>
        </a>
        `).show();
        return;
    }

    const html = items.map((p, idx) => {
        const metaParts = [];
        if (p.senha) metaParts.push(`Senha: ${p.senha}`);
        if (p.matricula) metaParts.push(`Matrícula: ${p.matricula}`);
        if (p.nascimento_fmt) metaParts.push(`Nasc.: ${p.nascimento_fmt}`);
        const meta = metaParts.length ? `<small class="text-muted">${metaParts.join(' • ')}</small>` : '';
        const nome = p.nome || 'Paciente sem nome';

        return `
        <a href="hub_paciente/paciente${encodeURIComponent(p.id_paciente)}"
            class="dropdown-item d-flex justify-content-between align-items-center ${idx === 0 ? 'active' : ''}"
            data-id="${p.id_paciente}">
            <div>
                <div><strong>${nome}</strong></div>
                ${meta}
            </div>
            <i class="bi bi-arrow-return-right"></i>
        </a>
        `;
    }).join('');
    $menu.html(html).show();
}


// Faz a busca
const doSearch = debounce(function() {
    const q = $input.val().trim();
    if (q.length < 2) {
        $menu.hide();
        return;
    }
    $.getJSON('ajax/pacientes_search.php', {
            q
        })
        .done(res => {
            console.log('[BUSCA OK]', res);
            renderResults(res);
        })
        .fail((jqXHR, textStatus, errorThrown) => {
            console.error('[BUSCA ERRO]', {
                status: jqXHR.status,
                textStatus,
                errorThrown,
                responseText: jqXHR.responseText
            });
            $menu
                .html(
                    `<div class="dropdown-item text-danger">
            Erro ao buscar (${jqXHR.status} / ${textStatus})<br>
                <small>${errorThrown}</small>
        </div>`
                )
                .show();
        });

}, 250);

$input.on('input', doSearch);

// Fecha dropdown ao clicar fora
$(document).on('click', function(e) {
    if (!$(e.target).closest('#global-patient-search').length) {
        $menu.hide();
    }
});

// Teclas: ↑ ↓ Enter Esc
$input.on('keydown', function(e) {
    const $items = $menu.find('.dropdown-item');
    if (!$items.length || $menu.is(':hidden')) return;

    let $current = $items.filter('.active');
    let idx = $items.index($current);

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        $current.removeClass('active');
        idx = (idx + 1) % $items.length;
        $items.eq(idx).addClass('active')[0].scrollIntoView({
            block: 'nearest'
        });
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        $current.removeClass('active');
        idx = (idx - 1 + $items.length) % $items.length;
        $items.eq(idx).addClass('active')[0].scrollIntoView({
            block: 'nearest'
        });
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const href = ($current.length ? $current : $items.eq(0)).attr('href');
        if (href) window.location.href = href;
    } else if (e.key === 'Escape') {
        $menu.hide();
    }
});

// Clique em item
$menu.on('click', '.dropdown-item', function(e) {
    // deixa o link funcionar (navegar)
});
$menu.on('click', '#create-new-pac', function(e) {
    e.preventDefault();
    const termo = $input.val().trim();
    // Se quiser pré-preencher:
    // const url = BASE_URL + 'cad_paciente.php' + (termo ? ('?nome_pac=' + encodeURIComponent(termo)) : '');
    const url = BASE_URL + 'cad_paciente.php';
    openModalPac(url, 'Cadastrar novo paciente'); // <— só isso
    $menu.hide();
});

function escapeAttrValue(val) {
    return String(val)
        .replace(/\\/g, '\\\\')
        .replace(/"/g, '\\"');
}

function navigateWithReturn(url) {
    try {
        sessionStorage.setItem('return_flow_url', window.location.href);
    } catch (e) {}
    try {
        const draft = collectFormDraft();
        if (draft) {
            sessionStorage.setItem('return_form_draft', JSON.stringify(draft));
        } else {
            sessionStorage.removeItem('return_form_draft');
        }
    } catch (e) {}
    window.location.href = url;
}

function collectFormDraft() {
    const form = document.getElementById('myForm');
    if (!form) return null;
    const elements = Array.from(form.elements || []);
    const values = {};
    const checks = {};
    let hasValue = false;

    const skipTypes = ['button', 'submit', 'reset', 'file'];

    elements.forEach(el => {
        if (!el || !el.name || el.disabled) return;
        const type = (el.type || '').toLowerCase();
        if (skipTypes.includes(type)) return;

        if (type === 'checkbox') {
            if (!checks[el.name]) checks[el.name] = {};
            const key = el.value || '__on__';
            checks[el.name][key] = el.checked;
            if (el.checked) hasValue = true;
            return;
        }

        if (type === 'radio') {
            if (el.checked) {
                values[el.name] = el.value;
                hasValue = true;
            } else if (!(el.name in values)) {
                values[el.name] = null;
            }
            return;
        }

        if (el.tagName === 'SELECT' && el.multiple) {
            const selected = Array.from(el.options || [])
                .filter(opt => opt.selected)
                .map(opt => opt.value);
            values[el.name] = selected;
            if (selected.length) hasValue = true;
            return;
        }

        values[el.name] = el.value;
        if (el.value) hasValue = true;
    });

    if (!hasValue) return null;

    return {
        url: window.location.href,
        timestamp: Date.now(),
        values,
        checks
    };
}

function restoreFormDraft() {
    let raw = null;
    try {
        raw = sessionStorage.getItem('return_form_draft');
    } catch (e) {
        raw = null;
    }
    if (!raw) return;
    let payload;
    try {
        payload = JSON.parse(raw);
    } catch (e) {
        sessionStorage.removeItem('return_form_draft');
        return;
    }
    if (!payload || payload.url !== window.location.href) return;
    const form = document.getElementById('myForm');
    if (!form) return;

    const values = payload.values || {};
    Object.keys(values).forEach(name => {
        const field = form.elements.namedItem(name);
        if (!field) return;
        const stored = values[name];

        if (field instanceof RadioNodeList || (field.length && field[0] && field[0].type === 'radio')) {
            const radios = field.length ? Array.from(field) : [field];
            radios.forEach(radio => {
                radio.checked = stored !== null && radio.value === stored;
            });
            return;
        }

        if (field.tagName === 'SELECT' && field.multiple && Array.isArray(stored)) {
            Array.from(field.options || []).forEach(opt => {
                opt.selected = stored.includes(opt.value);
            });
            return;
        }

        field.value = stored ?? '';
    });

    const checkboxStates = payload.checks || {};
    Object.keys(checkboxStates).forEach(name => {
        const states = checkboxStates[name];
        const selector = 'input[type="checkbox"][name="' + escapeAttrValue(name) + '"]';
        const boxes = form.querySelectorAll(selector);
        boxes.forEach(box => {
            const key = box.value || '__on__';
            if (Object.prototype.hasOwnProperty.call(states, key)) {
                box.checked = !!states[key];
            }
        });
    });

    if (window.$ && $.fn.selectpicker) {
        $('.selectpicker', form).each(function() {
            try {
                $(this).selectpicker('refresh');
            } catch (_) {}
        });
    }

    try {
        sessionStorage.removeItem('return_form_draft');
    } catch (_) {}
}

document.addEventListener('keydown', function(e) {
    if (!e.ctrlKey || !e.shiftKey) return;
    const key = (e.key || '').toUpperCase();
    let handled = false;

    if (key === 'I') {
        handled = true;
        navigateWithReturn(BASE_URL + 'internacoes/nova');
    } else if (key === 'P') {
        handled = true;
        openModalPac(BASE_URL + 'cad_paciente.php', 'Cadastrar novo paciente');
    } else if (key === 'V') {
        handled = true;
        navigateWithReturn(BASE_URL + 'cad_visita.php');
    } else if (key === 'S') {
        handled = true;
        if (typeof triggerInternacaoAutoSave === 'function') {
            triggerInternacaoAutoSave();
        } else {
            const form = document.getElementById('myForm');
            form && form.submit();
        }
    } else if (key === 'L') {
        handled = true;
        navigateWithReturn(BASE_URL + 'internacoes/lista');
    } else if (key === 'C') {
        handled = true;
        navigateWithReturn(BASE_URL + 'internacoes/rah');
    } else if (key === 'A') {
        handled = true;
        navigateWithReturn(BASE_URL + 'listas/altas');
    }

    if (handled) {
        e.preventDefault();
        e.stopPropagation();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('return-flow-btn');
    let target = null;
    try {
        target = sessionStorage.getItem('return_flow_url');
    } catch (e) {
        target = null;
    }
    if (target && window.location.href === target) {
        try {
            sessionStorage.removeItem('return_flow_url');
        } catch (_) {}
        target = null;
    }
    if (target && btn) {
        btn.style.display = 'flex';
        btn.addEventListener('click', function() {
            try {
                sessionStorage.removeItem('return_flow_url');
            } catch (_) {}
                window.location.href = target;
        });
    } else if (btn) {
        btn.style.display = 'none';
    }
    restoreFormDraft();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var delay = 160;
    document.querySelectorAll('.bi-dropdown.bi-mega').forEach(function(menu) {
        menu.addEventListener('mouseleave', function() {
            menu.querySelectorAll('.bi-submenu.open').forEach(function(openItem) {
                openItem.classList.remove('open');
            });
        });
    });

    document.querySelectorAll('.bi-dropdown.bi-mega .bi-submenu').forEach(function(item) {
        var timer;
        var trigger = item.querySelector('.dropdown-item');
        if (trigger) {
            trigger.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                item.parentElement.querySelectorAll('.bi-submenu.open').forEach(function(openItem) {
                    if (openItem !== item) {
                        openItem.classList.remove('open');
                        openItem.classList.remove('submenu-left');
                    }
                });
                if (item.classList.contains('open')) {
                    item.classList.remove('open');
                    item.classList.remove('submenu-left');
                    return;
                }
                item.classList.add('open');
                item.classList.remove('submenu-left');
                var submenu = item.querySelector('.bi-submenu-list');
                if (submenu) {
                    var rect = submenu.getBoundingClientRect();
                    if (rect.right > window.innerWidth) {
                        item.classList.add('submenu-left');
                    }
                }
            });
        }
        item.addEventListener('mouseenter', function() {
            timer = setTimeout(function() {
                item.parentElement.querySelectorAll('.bi-submenu.open').forEach(function(openItem) {
                    if (openItem !== item) {
                        openItem.classList.remove('open');
                    }
                });
                item.classList.add('open');
                item.classList.remove('submenu-left');
                var submenu = item.querySelector('.bi-submenu-list');
                if (submenu) {
                    var rect = submenu.getBoundingClientRect();
                    if (rect.right > window.innerWidth) {
                        item.classList.add('submenu-left');
                    }
                }
            }, delay);
        });
        item.addEventListener('mouseleave', function() {
            clearTimeout(timer);
            item.classList.remove('open');
            item.classList.remove('submenu-left');
        });
        item.addEventListener('focusin', function() {
            item.classList.add('open');
            item.classList.remove('submenu-left');
            var submenu = item.querySelector('.bi-submenu-list');
            if (submenu) {
                var rect = submenu.getBoundingClientRect();
                if (rect.right > window.innerWidth) {
                    item.classList.add('submenu-left');
                }
            }
        });
        item.addEventListener('focusout', function() {
            item.classList.remove('open');
            item.classList.remove('submenu-left');
        });
    });
});
</script>

</html>
