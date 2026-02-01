<?php
require_once("globals.php");

$msg = $_SESSION['recuperacao_msg'] ?? '';
$msgType = $_SESSION['recuperacao_tipo'] ?? 'info';
unset($_SESSION['recuperacao_msg'], $_SESSION['recuperacao_tipo']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recuperar senha</title>
    <style>
    body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: Arial, sans-serif;
        background-image: linear-gradient(135deg, rgba(247, 244, 255, 0.55) 0%, rgba(226, 243, 255, 0.55) 60%, rgba(212, 240, 255, 0.55) 100%);
        background-attachment: fixed;
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

    .box {
        width: 420px;
        max-width: 92vw;
        background: #fff;
        border-radius: 16px;
        padding: 28px 26px;
        box-shadow: 0 14px 28px rgba(0, 0, 0, .12);
    }

    .box h1 {
        margin: 0 0 6px;
        font-size: 22px;
        color: #2b2b2b;
    }

    .box p {
        margin: 0 0 16px;
        color: #666;
        font-size: 14px;
    }

    label {
        font-size: 13px;
        color: #4a4a4a;
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
    }

    input[type="email"] {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #d6d6d6;
        font-size: 14px;
        box-sizing: border-box;
        margin-bottom: 14px;
    }

    .btn {
        width: 100%;
        border: none;
        border-radius: 999px;
        padding: 12px 16px;
        background: #5e2363;
        color: #fff;
        font-weight: 700;
        cursor: pointer;
        font-size: 14px;
    }

    .btn:hover {
        background: #4a1c50;
    }

    .msg {
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 13px;
        margin-bottom: 12px;
    }

    .msg.info {
        background: #eef6ff;
        color: #0b4a7a;
        border: 1px solid #cfe5ff;
    }

    .msg.error {
        background: #fff1f2;
        color: #7a0b0b;
        border: 1px solid #ffd5da;
    }

    .back {
        display: inline-block;
        margin-top: 14px;
        font-size: 13px;
        color: #5e2363;
        text-decoration: none;
        font-weight: 600;
    }
    </style>
</head>

<body>
    <div class="box">
        <h1>Recuperar senha</h1>
        <p>Informe seu e-mail cadastrado para receber um código de verificação.</p>

        <?php if ($msg): ?>
        <div class="msg <?= $msgType === 'error' ? 'error' : 'info' ?>">
            <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form action="process_recuperar_senha.php" method="post" autocomplete="off">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required />
            <button type="submit" class="btn">Enviar código</button>
        </form>

        <a class="back" href="<?= app_url('index_novo.php') ?>">Voltar ao login</a>
    </div>
</body>

</html>
