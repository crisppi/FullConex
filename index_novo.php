<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCare</title>
    <style>
    body {
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        font-family: Arial, sans-serif;
        background: linear-gradient(45deg, #5e2363 50%, #5bd9f3 50%);
    }


    .login-container {
        display: flex;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        overflow: visible;
        width: 990px;
    }

    .login-form {
        padding: 40px;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        width: 60%;
        background: linear-gradient(to bottom right, rgba(53, 186, 225, 0.8), rgba(91, 217, 243, 0.9));
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .login-form-logo {
        width: 100%;
        margin-bottom: 20px;
    }

    .login-form h2 {
        color: white;
        font-size: 24px;
        margin-bottom: 15px;
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
        border-bottom: 2px solid white;
        background: transparent;
        /* color: white; */
        font-size: 16px;
        outline: none;
    }

    .input-container label {
        position: absolute;
        top: 10px;
        left: 0;
        color: rgba(255, 255, 255, 0.7);
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .input-container input:focus+label,
    .input-container input:not(:placeholder-shown)+label {
        top: -20px;
        font-size: 12px;
        color: white;
    }

    .login-btn {
        width: 100%;
        padding: 15px;
        background-color: rgba(91, 217, 243, 0.1);
        /* Cor de fundo roxo escuro */
        color: white;
        border: 2px solid white;
        /* Borda branca fina */
        cursor: pointer;
        font-size: 18px;
        border-radius: 30px;
        /* Bordas cilíndricas */
        margin-top: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .login-btn:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        background-color: #5e2363;
        /* Cor levemente diferente ao passar o mouse */
    }


    .forgot-password {
        color: white;
        text-align: center;
        margin-top: 20px;
    }

    .forgot-password a {
        color: white;
        text-decoration: none;
    }

    .side-panel {
        padding: 40px;
        background-color: #421849;
        color: white;
        width: 40%;
        height: 570px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        margin-top: -20px;
        margin-bottom: -50px;
        text-align: center;
        position: relative;
    }

    .logo-container {
        position: absolute;
        top: 10px;
        left: 20px;
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

    .side-panel h3 {
        margin-bottom: 5px;
    }

    .side-panel p {
        margin-bottom: 10px;
        line-height: 1.5;
        color: #c9c9c9;
    }

    .side-panel .email-btn {
        background: #421849;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 16px;
        border-radius: 5px;
    }

    /* Error message styling - floating at the bottom center */
    .error-message {
        position: fixed;
        bottom: 20px;
        /* Distance from the bottom of the screen */
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        /* Adjust the width as needed */
        max-width: 450px;
        /* Maximum width for larger screens */
        padding: 15px;
        background-color: rgba(53, 186, 225, 0.8);
        /* Light red background for error */
        border-left: 5px solid red;
        /* Red border to emphasize the error */
        border-radius: 5px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        /* Subtle shadow for modern look */
        color: white;
        font-size: 17px;
        animation: fadeIn 0.5s ease-in-out;
        z-index: 1000;
        /* Ensure it's on top of other content */
    }


    /* Define the animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Apply the animation to the body */
    body {
        opacity: 0;
        animation: fadeIn 0.3s ease-in forwards;
    }
    </style>



</head>

<body>

    <div class="login-container">
        <div class="login-form">
            <img src="img/logo_branco.svg" alt="Login Form Logo" class="login-form-logo">
            <div class="form-content">
                <form action="check_login.php" method="post" autocomplete="off">
                    <div class="input-container">
                        <input type="email" name="email_login" autocomplete="off" id="email_login" required
                            style="border-radius: 10px; border: 1px solid #ccc; padding: 10px; font-size: 14px; width: 100%; box-sizing: border-box; background-color: rgba(255, 255, 255, 0.6);">
                        <label for="email_login">Email</label>
                    </div>
                    <div class="input-container">
                        <input type="password" id="senha_login" autocomplete="off" name="senha_login" required
                            style="border-radius: 10px; border: 1px solid #ccc; padding: 10px; font-size: 14px; width: 100%; box-sizing: border-box; background-color: rgba(255, 255, 255, 0.6);">
                        <label for="senha_login">Senha</label>
                    </div>
                    <input type="submit" value="Login" class="login-btn">
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
                <img src="img/notebook_full.svg" alt="Exciting News Image" class="monitor-image">
                <h3>Novidades!</h3>
                <p>Agora temos um Dashboard para acompanhamento de indicadores em tempo real. Acompanhe pacientes
                    internados, contas e muito mais!</p>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const emailInput = document.getElementById("email_login");
        const senhaInput = document.getElementById("senha_login");

        // Limpar os campos manualmente
        emailInput.value = "";
        senhaInput.value = "";

        // Prevenir preenchimento automático
        setTimeout(() => {
            emailInput.value = "";
            senhaInput.value = "";
        }, 100);
    });
    </script>
    <script>
    document.querySelector("form").addEventListener("submit", function(event) {
        // Resetar os campos após o envio do formulário
        setTimeout(() => {
            this.reset();
        }, 100);
    });
    </script>

</body>

</html>