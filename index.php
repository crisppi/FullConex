<?php

include_once("globals.php");
include_once("db.php");
require_once("dao/usuarioDao.php");
require_once("models/message.php");
require_once("models/usuario.php");

$usuarioDao = new userDAO($conn, $BASE_URL);
?>
<!DOCTYPE html>
<link rel="shortcut icon" type="image/x-icon" href="img/full-ico.ico">

<html>

<head>
    <!-- <link href="<?php $BASE_URL ?>css/login.css" rel="stylesheet"> -->
    <!-- <link href="<?php $BASE_URL ?>css/styleIndex.css" rel="stylesheet"> -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
</head>

<body>

    <?php include_once("index_novo.php"); ?>

    
</body>

</html>
<script type="text/javascript">
    function ocultar() {
        let msgErr = document.getElementById('msgErr').style.display = "none";
        let email = document.getElementById('email_login');
        let senha = document.getElementById('senha_login');
        email.value = ""
        senha.value = ""

    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>
<script src="https://code.jquery.com/jquery-3.6.3.slim.min.js"
    integrity="sha256-ZwqZIVdD3iXNyGHbSYdsmWP//UBokj2FHAxKuSBKDSo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>