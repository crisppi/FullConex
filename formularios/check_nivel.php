<?php

if ($_SESSION['cargo'] === "Enf_auditor") {
    echo "<div class='logado'>";
    echo "Olá ";
    echo  $_SESSION['email_user'];
    echo "!! ";
    echo "<br>";
    echo "  Você está logado como Enfermeiro(a)";
    echo "</div>";
};
if ($_SESSION['cargo'] === "Med_auditor") {
    echo "<div class='logado'>";
    echo "Olá ";
    echo  "<b>" . $_SESSION['email_user'] . "</b>";
    echo "!! ";
    echo "  Você está logado como Médico(a)";
    echo "</div>";
};
if ($_SESSION['cargo'] === "Adm") {
    echo "<div class='logado'>";
    echo "Olá ";
    echo  $_SESSION['email_user'];
    echo "!! ";
    echo "  Você está logado como Administrativo(a)";
    echo "</div>";
};

if ($_SESSION['cargo'] === "Diretoria") {
    echo "<div class='logado'>";
    echo "Olá ";
    echo  $_SESSION['email_user'];
    echo "!! ";
    echo "  Você está logado como Diretor(a)";
    echo "</div>";
};

if ($_SESSION['cargo'] === "Gerência") {
    echo "<div class='logado'>";
    echo "Olá ";
    echo  $_SESSION['email_user'];
    echo "!! ";
    echo "  Você está logado como Diretor(a)";
    echo "</div>";
};
