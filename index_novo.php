<!DOCTYPE html>
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
        height: 100vh;
        font-family: Arial, sans-serif;
        background: linear-gradient(45deg, #5e2363 50%, #5bd9f3 50%);
        opacity: 0;
        animation: fadeIn .3s ease-in forwards;
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
        box-shadow: 0 10px 20px rgba(0, 0, 0, .2);
        overflow: visible;
        width: 990px;
        max-width: 95vw;
    }

    /* ===============================
       Bloco Azul (formulário)
    =============================== */
    .login-form {
        padding: 40px;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        width: 60%;
        background: linear-gradient(to bottom right, rgba(53, 186, 225, .8), rgba(91, 217, 243, .9));
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    .login-form-logo {
        width: 100%;
        margin-bottom: 20px;
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
        font-size: 16px;
        outline: none;
    }

    .input-container label {
        position: absolute;
        top: 10px;
        left: 0;
        color: rgba(255, 255, 255, .7);
        pointer-events: none;
        transition: all .3s ease;
    }

    .input-container input:focus+label,
    .input-container input:not(:placeholder-shown)+label {
        top: -20px;
        font-size: 12px;
        color: #fff;
    }

    .login-btn {
        width: 100%;
        padding: 15px;
        background: rgba(91, 217, 243, .1);
        color: #fff;
        border: 2px solid #fff;
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

    /* ===============================
       Bloco Lilás (lado direito)
    =============================== */
    .side-panel {
        padding: 40px;
        background: #421849;
        color: #fff;
        width: 40%;
        height: 570px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .15);
        border-radius: 10px;
        margin-top: -20px;
        margin-bottom: -50px;
        text-align: center;
        position: relative;
    }

    .side-panel-content {
        margin-top: 70px;
    }

    .side-panel img.monitor-image {
        width: 100%;
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

    /* ===============================
       CONEX AUD no topo + TAGLINE abaixo
       (sem alterar HTML)
    =============================== */
    :root {
        --conex-side-h: 30px;
        /* altura do logo */
        --conex-side-top: 18px;
        /* distância do topo */
        --conex-tagline-gap: 8px;
        /* espaço entre logo e texto */
        --conex-tagline-size: 12px;
    }

    @media (max-width:768px) {
        :root {
            --conex-side-h: 28px;
            --conex-side-top: 16px;
            --conex-tagline-size: 11px;
        }
    }

    @media (max-width:480px) {
        :root {
            --conex-side-h: 26px;
            --conex-side-top: 14px;
            --conex-tagline-size: 10.5px;
        }
    }

    /* Logo (pinta só o desenho com máscara) */
    .side-panel::before {
        content: "";
        position: absolute;
        top: var(--conex-side-top);
        left: 50%;
        transform: translateX(-50%);
        height: var(--conex-side-h);
        aspect-ratio: 6.6 / 1;
        -webkit-mask: url('img/LogoConexAud.png') no-repeat center / contain;
        mask: url('img/LogoConexAud.png') no-repeat center / contain;
        background: linear-gradient(90deg, #FFFFFF 0%, #BFC7CF 100%);
        pointer-events: none;
        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, .06));
    }

    /* Texto/Tagline abaixo do logo */
    .side-panel::after {
        /* >>> edite aqui o texto que você quer mostrar abaixo do logo <<< */
        content: "Acesso exclusivo — Conex Aud";
        position: absolute;
        top: calc(var(--conex-side-top) + var(--conex-side-h) + var(--conex-tagline-gap));
        left: 50%;
        transform: translateX(-50%);
        font-size: var(--conex-tagline-size);
        font-weight: 600;
        letter-spacing: .06em;
        color: #E9EDF2;
        /* cinza claro/branco suave */
        opacity: .95;
        pointer-events: none;
        white-space: nowrap;
        text-transform: uppercase;
        text-shadow: 0 1px 0 rgba(0, 0, 0, .10);
    }

    /* Fallback sem mask */
    @supports not ((mask: url()) or (-webkit-mask: url())) {
        .side-panel::before {
            background: url('img/LogoConexAud.png') no-repeat center / contain;
            filter: grayscale(1) brightness(2.2) contrast(1.1);
        }
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