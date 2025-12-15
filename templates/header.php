<?php

include_once("globals.php");
include_once("db.php");
date_default_timezone_set('America/Sao_Paulo');
header("Content-type: text/html; charset=utf-8");

// Caminho default
$defaultFoto = $BASE_URL . 'img/user-default.png';
$hideConexLogoParam = strtolower((string)(filter_input(INPUT_GET, 'hide_conex', FILTER_SANITIZE_SPECIAL_CHARS) ?? ''));
$hideConexLogo = in_array($hideConexLogoParam, ['1', 'true', 'sim', 'on'], true);

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
    <link href="<?= $BASE_URL ?>css/styleMenu.css" rel="stylesheet">
    <link href="<?= $BASE_URL ?>css/style_show_internacao.css" rel="stylesheet">

    <!-- ======= APENAS DESIGN (logos alinhados e simétricos) ======= -->
    <style>
    /* Ajustes controlados por variáveis */
    :root {
        /* Altura visual do FullCare (compensa borda branca) */
        --fullcare-h: 56px;
        /* Altura do ConexAud (texto mais delgado) */
        --conexaud-h: 28px;
        /* Espaço entre os logos */
        --logos-space: 14px;
    }

    @media (max-width: 1199.98px) {
        :root {
            --fullcare-h: 52px;
            --conexaud-h: 26px;
            --logos-space: 12px;
        }
    }

    @media (max-width: 575.98px) {
        :root {
            --fullcare-h: 48px;
            --conexaud-h: 24px;
            --logos-space: 10px;
        }
    }

    /* Mantém a brand como flex para alinhar os dois logos horizontalmente */
    .navbar .navbar-brand {
        display: inline-flex;
        align-items: center;
        line-height: 1;
    }

    /* FullCare com altura exata e espaçamento consistente */
    .navbar .navbar-brand .logo-novo {
        height: var(--fullcare-h) !important;
        width: auto !important;
        max-height: none !important;
        min-height: 0 !important;
        display: block;
        margin-right: var(--logos-space) !important;
    }

    /* ConexAud injetado ao lado SEM mexer no HTML */
    .navbar .navbar-brand::after {
        content: "";
        display: inline-block;
        height: var(--conexaud-h);
        /* Proporção aprox. 330x50 = 6.6:1 -> largura deriva da altura */
        width: calc(var(--conexaud-h) * 6.6);
        background: url('<?= $BASE_URL ?>img/LogoConexAud.png') no-repeat center / contain;
        vertical-align: middle;
        opacity: .98;
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

    body.no-conex-brand .navbar .navbar-brand::after {
        display: none !important;
    }

    body.no-conex-brand .navbar .navbar-brand .logo-novo {
        margin-right: 0 !important;
    }
    </style>
</head>

<body class="<?= $hideConexLogo ? 'no-conex-brand' : '' ?>">
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

                        <?php if ($_SESSION['nivel'] > 0) { ?>

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
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>manual.html"><i class="bi bi-person"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>
                                        Manual</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>solicitacao_customizacao_pdf.php"
                                        target="_blank">
                                        <i class="bi bi-file-earmark-text"
                                            style="font-size: 1rem;margin-right:5px; color: #5e2363;"></i>
                                        Solicitação de Customização (PDF)
                                    </a></li>
                                <?php if ($_SESSION['nivel'] > 3) { ?>
                                <li class="nav-item">
                                    <a class="dropdown-item" href="<?= $BASE_URL ?>admin_permissao.php">
                                        <i class="bi bi-shield-lock"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(21, 56, 210);"></i>
                                        Permissões
                                    </a>
                                </li>
                                <?php }; ?>


                                <?php }; ?>
                            </ul>
                        </li>



                        <?php if ($_SESSION['nivel'] > 3) { ?>
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
                        <?php if ($_SESSION['nivel'] > 3) { ?>

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

                        <?php if ($_SESSION['nivel'] >= 3) { ?>

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
                        <?php if ($_SESSION['nivel'] >= 3): ?>
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

                        <?php if ($_SESSION['nivel'] >= 3) { ?>

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

                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($_SESSION['nivel'] >= 3) { ?>
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
                            <a class="js-acc-btn" href="#"><?php print $_SESSION['usuario_user'] ?></a>
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
</script>

</html>
