<!DOCTYPE html>
<?php $currentAppVersion = app_latest_version($conn); ?>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FullCare</title>

    <style>
    /* ===============================
       Base
    =============================== */
    body {
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        font-family: Arial, sans-serif;
        background-image: linear-gradient(135deg, rgba(247, 244, 255, 0.55) 0%, rgba(226, 243, 255, 0.55) 60%, rgba(212, 240, 255, 0.55) 100%);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        position: relative;
        opacity: 0;
        animation: fadeIn .3s ease-in forwards;
    }

    body::before {
        content: "";
        position: fixed;
        inset: 0;
        background: url("img/17450.jpg") center / cover no-repeat;
        opacity: 0.25;
        z-index: -1;
        pointer-events: none;
    }

    @keyframes fadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    .login-container {
        display: flex;
        border-radius: 10px;
        overflow: visible;
        width: 990px;
        max-width: 95vw;
        align-items: center;
    }

    /* ===============================
       Bloco Azul (formulário)
    =============================== */
    .login-form {
        padding: 12px 22px 16px;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        width: 42%;
        height: 400px;
        min-height: 0;
        background: linear-gradient(160deg, #2d63a6, #92bee2);
        box-shadow: 0 10px 20px rgba(0, 0, 0, .2);
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        position: relative;
    }

    .login-form-logo {
        width: 100%;
        max-width: 256px;
        margin-bottom: 12px;
        display: block;
    }

    .form-content {
        width: 60%;
    }

    .input-container {
        position: relative;
        margin: 20px 0;
        width: 100%;
    }

    .input-container input {
        width: 100%;
        padding: 10px 0;
        border: none;
        border-bottom: 2px solid #fff;
        background: transparent;
        font-size: 13px;
        outline: none;
    }

    .input-container label {
        position: absolute;
        top: 10px;
        left: 0;
        color: rgba(255, 255, 255, .7);
        pointer-events: none;
        transition: all .3s ease;
        font-size: 13px;
    }

    .input-container input:focus+label,
    .input-container input:not(:placeholder-shown)+label {
        top: -20px;
        font-size: 11px;
        color: #fff;
    }

    .login-btn {
        width: 100%;
        padding: 15px;
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.6);
        cursor: pointer;
        font-size: 18px;
        border-radius: 30px;
        margin-top: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .1);
        transition: all .3s ease;
    }

    .login-btn:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, .2);
        background: #5e2363;
    }

    .forgot-password {
        color: #fff;
        text-align: center;
        margin-top: 20px;
    }

    .forgot-password a {
        color: #fff;
        text-decoration: none;
    }

    .login-links {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        margin: -6px 0 10px;
    }

    .login-links a {
        font-size: 12px;
        color: #f3f7ff;
        text-decoration: none;
        font-weight: 600;
    }

    .login-links a:hover {
        text-decoration: underline;
    }

    /* ===============================
       Bloco Lilás (lado direito)
    =============================== */
    .side-panel {
        padding: 22px;
        background: linear-gradient(160deg, #4b2f70, #612f7d 80%);
        color: #fff;
        width: 58%;
        max-height: 500px;
        min-height: 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .15);
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        border-radius: 10px;
        margin-top: 0;
        margin-bottom: -20px;
        text-align: center;
        position: relative;
    }

    .side-panel-content {
        margin-top: 22px;
    }

    .side-panel img.monitor-image {
        width: 70%;
        height: auto;
        object-fit: contain;
    }

    .side-panel h3,
    .side-panel p,
    .side-panel .email-btn {
        margin: 10px 0;
    }

    .side-panel p {
        margin-bottom: 10px;
        line-height: 1.5;
        color: #c9c9c9;
    }

    .side-panel .email-btn {
        background: #421849;
        color: #fff;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 16px;
        border-radius: 5px;
    }

    .side-panel::before {
        content: "SISTEMA FULLCARE";
        position: absolute;
        top: 18px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        color: #E9EDF2;
        opacity: .95;
        text-transform: uppercase;
        text-shadow: 0 1px 0 rgba(0, 0, 0, .10);
        pointer-events: none;
        white-space: nowrap;
    }

    /* ===============================
       Mensagem de erro (flutuante)
    =============================== */
    .error-message {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        max-width: 450px;
        padding: 15px;
        background: rgba(53, 186, 225, .8);
        border-left: 5px solid red;
        border-radius: 5px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .1);
        color: #fff;
        font-size: 17px;
        animation: fadeIn .5s ease-in-out;
        z-index: 1000;
    }

    /* ===============================
       Responsivo
    =============================== */
    @media (max-width: 1024px) {
        body {
            padding: 24px 16px;
            height: auto;
        }

        .login-container {
            width: 100%;
            max-width: 860px;
        }

        .form-content {
            width: 70%;
        }
    }

    @media (max-width: 900px) {
        .login-container {
            flex-direction: column;
            border-radius: 16px;
            overflow: hidden;
        }

        .login-form,
        .side-panel {
            width: 100%;
            border-radius: 0;
            height: auto;
        }

    .login-form {
            padding: 16px 20px 18px;
            min-height: 0;
        }

        .side-panel {
            min-height: 0;
            margin: 0;
            padding: 28px 24px 32px;
        }

        .side-panel-content {
            margin-top: calc(var(--conex-side-top) + var(--conex-side-h) + var(--conex-tagline-gap) + 24px);
        }
    }

    @media (max-width: 600px) {
    .side-panel {
            display: none;
        }

        body {
            min-height: 600px;
            align-items: flex-start;
        }

        .login-container {
            align-items: flex-start;
            height: 600px;
        }

        .login-form {
            padding: 6px 10px 6px;
            min-height: 0;
            height: 600px;
            max-height: 600px;
        }

        .login-form-logo {
            max-width: 150px;
            margin-bottom: 3px;
        }

        .form-content {
            width: 100%;
        }

        .input-container {
            margin: 4px 0;
        }

        .input-container input {
            padding: 4px 0 !important;
            font-size: 11px !important;
        }

        .input-container label {
            font-size: 11px;
        }

        .input-container input:focus+label,
        .input-container input:not(:placeholder-shown)+label {
            top: -13px;
            font-size: 9px;
        }

        .login-btn {
            padding: 6px;
            margin-top: 4px;
            font-size: 14px;
        }
    }

    .login-footer {
        position: fixed;
        bottom: 12px;
        right: 24px;
        color: rgba(255, 255, 255, .85);
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        pointer-events: none;
    }

    @media (max-width: 900px) {
        .login-footer {
            left: 50%;
            right: auto;
            transform: translateX(-50%);
        }
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-form">
            <img src="img/logo_branco.svg" alt="Login Form Logo" class="login-form-logo" />
            <div class="form-content">
                <form action="check_login.php" method="post" autocomplete="off">
                    <div class="input-container">
                        <input type="email" name="email_login" autocomplete="off" id="email_login" required
                            style="border-radius:10px; border:1px solid #ccc; padding:10px; font-size:14px; width:100%; box-sizing:border-box; background-color: rgba(255,255,255,.6);" />
                        <label for="email_login">Email</label>
                    </div>

                    <div class="input-container">
                        <input type="password" id="senha_login" autocomplete="off" name="senha_login" required
                            style="border-radius:10px; border:1px solid #ccc; padding:10px; font-size:14px; width:100%; box-sizing:border-box; background-color: rgba(255,255,255,.6);" />
                        <label for="senha_login">Senha</label>
                    </div>

                    <div class="login-links">
                        <a href="esqueci_senha.php">Esqueci minha senha</a>
                    </div>

                    <input type="submit" value="Login" class="login-btn" />
                </form>

                <!-- Error message -->
                <?php if (isset($_SESSION['mensagem']) && $_SESSION['mensagem'] != "") { ?>
                <div class="error-message">
                    <p><?php echo $_SESSION['mensagem']; ?></p>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="side-panel">
            <div class="side-panel-content">
                <img src="img/notebook_full.svg" alt="Exciting News Image" class="monitor-image" />
                <h3>Novidades!</h3>
                <p>Decisões melhores começam com dados claros. Veja internações e contas evoluindo em tempo real.

                    Mais visão, menos suposição: indicadores que conectam cuidado e eficiência.</p>
            </div>
        </div>
    </div>

    <div class="login-footer">
        Versão <?= htmlspecialchars($currentAppVersion) ?>
    </div>

    <script>
    // limpar os campos manualmente e evitar autocompletar
    document.addEventListener("DOMContentLoaded", () => {
        const emailInput = document.getElementById("email_login");
        const senhaInput = document.getElementById("senha_login");
        emailInput.value = "";
        senhaInput.value = "";
        setTimeout(() => {
            emailInput.value = "";
            senhaInput.value = "";
        }, 100);
    });

    // resetar campos após o submit
    document.querySelector("form").addEventListener("submit", function() {
        setTimeout(() => {
            this.reset();
        }, 100);
    });
    </script>
</body>

</html>
