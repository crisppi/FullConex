<?php
// exit;
// session_start();
include_once("globals.php");
include_once("db.php");
require_once("dao/usuarioDao.php");
require_once("models/message.php");
require_once("models/usuario.php");

$usuarioDao = new userDAO($conn, $BASE_URL);
?>
<!DOCTYPE html>
<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

<html>
<!-- <?php print_r($_SESSION); ?> -->

<head>
    <link href="<?php $BASE_URL ?>css/login.css" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
</head>

<body>
    <br>

    </div>
    <div class="box-form">
        <div class="left">
            <div class="overlay">

            </div>
        </div>


        <div class="right">
            <h1>Sistema
                Gestão</h1>
            <form action="check_login.php" method="POST">
                <div class="inputs">
                    <input type="hidden" id="loggedin" name="loggedin" value="loggedin">
                    <input type="text" placeholder="Usuario" name="email_login" onfocus="ocultar()" id="email_login" type="email" class="input" required>
                    <br>
                    <input type="password" placeholder="Senha" id="senha_login" name="senha_login" type="password" class="input" required>
                </div>


                <br><br>

                <div class="remember-me--forget-password">
                    <!-- Angular -->
                    <!-- <label>
                        <input type="checkbox" name="item" checked />
                        <span class="text-checkbox">Lembrar-me</span>
                    </label> -->
                    <p>Caso o acesso não esteja funcionando entre em contato com a administração</p>
                </div>

                <br>
                <button type="submit">Login</button>
        </div>
        </form>
    </div>

    <!-- <div class="login-form">
        <form action="check_login.php" method="POST">
            <div class="sign-in-htm">
                <input type="hidden" id="loggedin" name="loggedin" value="loggedin">
                <div class="group">
                    <label for="email_login" class="label">Usuário</label>
                    <input name="email_login" onfocus="ocultar()" id="email_login" type="email" required>
                </div>
                <div class="group">
                    <label for="senha_login" class="label">Senha</label>
                    <input id="senha_login" name="senha_login" type="password" class="input" required>
                </div>
                <div class="group">
                    <input type="submit" class="button" name="login" class="btn btn-info" value="Login">
                </div>
        </form>
        <?php
        if (isset($_SESSION['mensagem']) and $_SESSION['mensagem'] != "") { ?>
            <div id="msgErr" style="background-color:aliceblue; padding:10px; border-radius: 20px;">
                <?Php echo "<div style='color:red; text-align:center;'>" . $_SESSION['mensagem']; ?>
            </div>
        <?php };
        // print_r($_SESSION[0]['senha_default_user']);
        ?>
    </div> -->
</body>


</html>
<script type="text/javascript">
    function ocultar() {
        // console.log("chefasl")
        let msgErr = document.getElementById('msgErr').style.display = "none";
        let email = document.getElementById('email_login');
        let senha = document.getElementById('senha_login');
        email.value = ""
        senha.value = ""

    }
</script>

<style>
    body {
        background-image: linear-gradient(135deg, #35bae1 10%, #CDEE21 100%);
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
        font-family: "Open Sans", sans-serif;
        color: #333333;
    }

    .box-form {
        margin: 2% auto;
        width: 80%;
        height: 80%;
        background: #FFFFFF;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex: 1 1 100%;
        align-items: stretch;
        justify-content: space-between;
        box-shadow: 0 0 20px 6px #090b6f85;
    }

    @media (max-width: 980px) {
        .box-form {
            flex-flow: wrap;
            text-align: center;
            align-content: center;
            align-items: center;
        }
    }

    .box-form div {
        height: auto;
    }

    .box-form .left {
        color: #FFFFFF;
        width: 60%;
        background-size: cover;
        background-repeat: no-repeat;
        background-image: url("img/logo_accert.png");
        ;
        background-size: 80% auto;
        background-position: center center;
        overflow: hidden;
    }

    .box-form .left .overlay {
        padding: 30px;
        width: 100%;
        height: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }

    .box-form .left .overlay h1 {
        font-size: 10vmax;
        line-height: 1;
        font-weight: 900;
        margin-top: 40px;
        margin-bottom: 20px;
    }

    .box-form .left .overlay span p {
        margin-top: 30px;
        font-weight: 900;
    }

    .box-form .left .overlay span a {
        background: #3b5998;
        color: #FFFFFF;
        margin-top: 10px;
        padding: 14px 50px;
        border-radius: 100px;
        display: inline-block;
        box-shadow: 0 3px 6px 1px #042d4657;
    }

    .box-form .left .overlay span a:last-child {
        background: #1dcaff;
        margin-left: 30px;
    }

    .box-form .right {
        padding: 40px;
        overflow: hidden;
        width: 60%;
        margin-top: 12%;
    }

    @media (max-width: 980px) {
        .box-form .right {
            width: 100%;
        }
    }

    .box-form .right h5 {
        font-size: 6vmax;
        line-height: 0;
    }

    .box-form .right p {
        font-size: 14px;
        color: #B0B3B9;
    }

    .box-form .right .inputs {
        overflow: hidden;
    }

    .box-form .right input {
        width: 100%;
        padding: 10px;
        margin-top: 25px;
        font-size: 16px;
        border: none;
        outline: none;
        border-bottom: 2px solid #B0B3B9;
    }

    .box-form .right .remember-me--forget-password {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .box-form .right .remember-me--forget-password input {
        margin: 0;
        margin-right: 7px;
        width: auto;
    }

    .box-form .right button {
        float: right;
        color: #fff;
        font-size: 16px;
        padding: 12px 35px;
        border-radius: 10px;
        display: inline-block;
        border: 0;
        outline: 0;
        /* box-shadow: 0px 4px 20px 0px #449346; */
        background: #449346;
    }

    label {
        display: block;
        position: relative;
        margin-left: 30px;
    }

    label::before {
        content: ' \f00c';
        position: absolute;
        font-family: FontAwesome;
        background: transparent;
        border: 3px solid #70F570;
        border-radius: 4px;
        color: transparent;
        left: -30px;
        transition: all 0.2s linear;
    }

    label:hover::before {
        font-family: FontAwesome;
        content: ' \f00c';
        color: #fff;
        cursor: pointer;
        background: #70F570;
    }

    label:hover::before .text-checkbox {
        background: #70F570;
    }

    label span.text-checkbox {
        display: inline-block;
        height: auto;
        position: relative;
        cursor: pointer;
        transition: all 0.2s linear;
    }

    label input[type="checkbox"] {
        display: none;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.3.slim.min.js" integrity="sha256-ZwqZIVdD3iXNyGHbSYdsmWP//UBokj2FHAxKuSBKDSo=" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>