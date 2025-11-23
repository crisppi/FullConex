<?php

include_once("globals.php");
include_once("db.php");
date_default_timezone_set('America/Sao_Paulo');
header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head style="position:fixed">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">

    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

    <title>Full-2023</title>
    <!-- Boostrap -->
    <link href="<?php $BASE_URL ?>css/style.css" rel="stylesheet">
    <link href="<?php $BASE_URL ?>css/legendas.css" rel="stylesheet">
    <link href="<?php $BASE_URL ?>css/styleMenu.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.4/bootstrap-icons.svg">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    <!-- boostrap icones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">

    <!-- script jquery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <nav class="navbar navbar-light bg-light">

</head>

<body>
    <div class="col-md-12">
        <div class="bar_color" style="width:100%;height:3px;background-image: linear-gradient(to right, #421849, #ce4fe3);
            ">
        </div>
        <nav class="navbar navbar-expand-lg navbar-light bg-light nav_bar_custom">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <img src="img/full-03.png" style="width:50px; height:50px " alt="Full">
                </a> <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div>
                    <h3 class="titulo_header" style="margin:0 50px 0 20px; text-align:center">Sistema Gestão</h3>
                </div>
                <div class="collapse navbar-collapse" id="navbarScroll">
                    <ul class="nav-tabs navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll"
                        style="--bs-scroll-height: 100px;">

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Menu
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>dashboard"><i
                                            class="bi bi-person"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>
                                        Status</a></li>
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>menu"><span
                                            class="bi bi-hospital"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(67, 125, 525);"></span>
                                        Menu</a></li>
                            </ul>
                        </li>
                        <?php if ($_SESSION['nivel'] > 3) { ?>
                        <li id="drop1" class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="<?= $BASE_URL ?>pacientes"
                                id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Usuários
                            </a>
                            <ul class="dropdown-menu" id="dropMenu1" aria-labelledby="navbarScrollingDropdown">
                                <!-- <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_usuario.php"><i class="bi bi-person-add" style="font-size: 1rem; margin-right:5px; color: rgb(15, 155, 76);"></i> Cadastro Usuário</a></li>
                  <li>
                  <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_hospitalUser.php"><i class="bi bi-person-add" style="font-size: 1rem; margin-right:5px; color: rgb(15, 15, 276);"></i> Cadastro
                      Hospital/Usuário</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li> -->
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_usuario.php"><i
                                            class="bi bi-file-medical"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(155, 95, 76);"></i>
                                        Pesquisa Usuários</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_hospitalUser.php"><i
                                            class="bi bi-person-badge"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(15, 155, 176);"></i>
                                        Hospital por Usuário</a>
                                </li>
                            </ul>
                        </li>
                        <!-- <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle " href="<?= $BASE_URL ?>pacientes" id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Cadastro
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                  <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_paciente.php"><span class="bi bi-person" style="font-size: 1rem;margin-right:5px; color: rgb(155, 155, 76);"></span> Paciente</a></li>
                  <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_hospital.php"><span class="bi bi-hospital" style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></span> Hospital</a></li>
                  <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_seguradora.php"><span class="bi bi-heart-pulse" style="font-size: 1rem;margin-right:5px; color: rgb(255, 215, 55);"></span> Seguradora</a></li>
                  <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_estipulante.php"><span class="bi bi-building" style="font-size: 1rem; margin-right:5px; color: rgb(255, 25, 55);"></span> Estipulante</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_acomodacao.php"><span class="bi bi-clipboard-heart" style="font-size: 1rem; margin-right:5px; color: rgb(155, 155, 76);"></span> Acomodação</a></li>
                  <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_patologia.php"><span class="bi bi-virus" style="font-size: 1rem; margin-right:5px; color: rgb(155, 155, 76);"></span> Patologia</a></li>
                  <li><a class="dropdown-item" href="<?php $BASE_URL ?>cad_antecedente.php"><span class="bi bi-people" style="font-size: 1rem; margin-right:5px; color: rgb(155, 155, 76);"></span> Antecedente</a></li>
                </ul>
              </li> -->
                        <?php }; ?>
                        <?php if ($_SESSION['nivel'] > 3) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Cadastros
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?= $BASE_URL ?>pacientes"><i
                                            class="bi bi-person"
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
                                            style="font-size: 1rem;margin-right:5px; color: rgb(213, 12, 155);"></i>
                                        Estipulante</a></li>
                                <li>

                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_acomodacao.php"><i
                                            class=" bi bi-clipboard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(145, 156, 55);"></i>
                                        Acomodação</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_patologia.php"><span
                                            class=" bi bi-virus"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(178, 155, 155);"></span>
                                        Patologia</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_antecedente.php"><i
                                            class="bi bi-people"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(178, 156, 55);"></i>
                                        Antecedente</a></li>
                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($_SESSION['nivel'] >= 2 or $_SESSION['nivel'] == 1) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Censo
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>censo/novo"><i
                                            class="bi bi-book"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>
                                        Cadastro Censo</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_censo_adm.php"><i
                                            class="bi bi-book"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27, 156, 55);"></i>
                                        Lista Censo - ADM</a></li>
                                <li>
                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($_SESSION['nivel'] >= 3) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Produção
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <!-- <li><a class="dropdown-item" href="<?php $BASE_URL ?>censo/novo"><i class="bi bi-book" style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i> Cadastro Censo</a></li> -->
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>internacoes/nova"><i
                                            class="bi bi-calendar2-date"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i> Nova
                                        Internação</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>internacoes/lista"><i
                                            class="bi bi-pencil-square"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(255, 25, 55);"></i>
                                        Visita</a></li>
                                <li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>censo/lista"><i
                                            class="bi bi-book"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27, 156, 55);"></i>
                                        Lista Censo</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>internacoes/lista"> <i
                                            class="bi bi-calendar2-date"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i> Lista
                                        Internação</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_internacao_uti.php"> <i
                                            class="bi bi-clipboard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i> Lista
                                        Internação UTI</a>
                                </li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_gestao.php"><i
                                            class="bi bi-postcard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i> Lista
                                        Gestão</a></li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_internacao_uti_alta.php"><span
                                            id="boot-icon3" class="bi bi-box-arrow-left"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(167, 25, 55);"></span>
                                        Alta UTI</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_internacao_alta.php"><span
                                            id="boot-icon3" class="bi bi-box-arrow-left"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(16, 15, 155);"></span>
                                        Alta Hospitalar</a>
                                </li>
                            </ul>
                        </li>
                        <?php }; ?>
                        <?php if ($_SESSION['nivel'] >= 3) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Listas
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">

                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>censo/lista"><i
                                            class="bi bi-book"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27, 156, 55);"></i>
                                        Lista Censo</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>internacoes/lista"> <i
                                            class="bi bi-calendar2-date"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i> Lista
                                        Internação</a></li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_internacao_uti.php"> <i
                                            class="bi bi-clipboard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i> Lista
                                        Internação UTI</a>
                                </li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_gestao.php"><i
                                            class="bi bi-postcard-heart"
                                            style="font-size: 1rem;margin-right:5px; color: rgb(27,156, 55);"></i> Lista
                                        Gestão</a></li>

                            </ul>
                        </li>
                        <?php }; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Contas
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_internacao_cap.php"><span
                                            id="boot-icon1" class="bi bi-currency-dollar"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(77, 155, 67);">
                                        </span> Contas para Auditar</a></li>
                                <li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>list_internacao_cap_fin.php"> <span
                                            id="boot-icon" class="bi bi-shield-check fw-bold"
                                            style="font-size: 1rem; margin-right:5px;color: rgb(21, 56, 210);"> </span>
                                        Contas Finalizadas
                                    </a></li>

                            </ul>
                        </li>
                        <?php if ($_SESSION['nivel'] >= 2) { ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
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
                        <?php }; ?>
                        <?php if ($_SESSION['nivel'] > 3) { ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle " href="#" id="navbarScrollingDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Relatórios
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>relatorios.php"><span
                                            id="boot-icon1" class="bi bi-clipboard-data"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(77, 155, 67);">
                                        </span> Relatórios </a></li>
                                <li>
                                <li><a class="dropdown-item" href="<?php $BASE_URL ?>relatorios_capeante.php"><span
                                            id="boot-icon1" class="bi bi-clipboard-data"
                                            style="font-size: 1rem; margin-right:5px; color: rgb(77, 155, 67);">
                                        </span> Relatórios Capeantes</a></li>
                                <li>
                            </ul>
                        </li>
                        <?php }; ?>
                    </ul>
                </div>
            </div>

            <a href="" class="text-dark">
                <i class="fas fa-envelope fa-2x"></i>
                <span class="badge rounded-pill badge-notification bg-danger">9</span>
            </a>
            <div class="col-md-2" style="margin-right:10px; font-weight:600 ;font-size:12px; text-align: center">

                <?php
        if ($_SESSION) {
          echo "<span style='color:#181818; font-size:1.0em; text-align: center'>Bem vindo: " . $_SESSION['email_user'] . "</span><br>";
          $agora = date('d/m/Y H:i');
        } else {
          echo "<span style='color:red'> Você não esta logado!</span>" . "<br>";
        }
        $agora = date('d/m/Y H:i');
        echo "<div >";
        echo "<span style='text-align:center; color:#181818; font-size:0.8em; text-align: center'>Data: " . $agora;
        ?>

                <div style='text-align:center'>
                    <a class="dropdown-item" style="color:#35bae1; font-size:larger; font-weight:600"
                        href="<?php $BASE_URL ?>destroi.php"> Sair</a>
                </div>
            </div>
    </div>
    </nav>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

</html>
