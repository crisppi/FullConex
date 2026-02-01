<?php
require_once("globals.php");

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$msg = $_SESSION['recuperacao_msg'] ?? '';
$msgType = $_SESSION['recuperacao_tipo'] ?? 'info';
unset($_SESSION['recuperacao_msg'], $_SESSION['recuperacao_tipo']);

$tokenOk = false;
$emailMask = '';
if ($token) {
    try {
        $tokenHash = hash('sha256', $token);
        $stmt = $conn->prepare("
            SELECT email, expires_at, used_at
              FROM tb_user_password_reset
             WHERE token_hash = :th
             LIMIT 1
        ");
        $stmt->bindValue(':th', $tokenHash);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && empty($row['used_at']) && strtotime($row['expires_at']) >= time()) {
            $tokenOk = true;
            $email = (string)($row['email'] ?? '');
            if ($email) {
                $parts = explode('@', $email);
                $name = $parts[0] ?? '';
                $domain = $parts[1] ?? '';
                $nameMask = mb_substr($name, 0, 2) . str_repeat('*', max(0, mb_strlen($name) - 2));
                $emailMask = $domain ? ($nameMask . '@' . $domain) : $nameMask;
            }
        }
    } catch (Throwable $e) {
        $tokenOk = false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Redefinir senha</title>
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
        width: 460px;
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

    input[type="text"],
    input[type="password"] {
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
        <h1>Redefinir senha</h1>
        <?php if ($emailMask): ?>
        <p>Enviamos o código para <strong><?= htmlspecialchars($emailMask, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
        <?php else: ?>
        <p>Informe o código recebido e a nova senha.</p>
        <?php endif; ?>

        <?php if ($msg): ?>
        <div class="msg <?= $msgType === 'error' ? 'error' : 'info' ?>">
            <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <?php if ($tokenOk): ?>
        <form action="process_redefinir_senha.php" method="post" autocomplete="off">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" />

            <label for="codigo">Código recebido</label>
            <input type="text" id="codigo" name="codigo" inputmode="numeric" maxlength="6" required />

            <label for="senha">Nova senha</label>
            <input type="password" id="senha" name="senha" required />

            <label for="senha2">Confirmar nova senha</label>
            <input type="password" id="senha2" name="senha2" required />

            <button type="submit" class="btn">Atualizar senha</button>
        </form>
        <?php else: ?>
        <div class="msg error">Link inválido ou expirado. Solicite um novo código.</div>
        <?php endif; ?>

        <a class="back" href="<?= app_url('index_novo.php') ?>">Voltar ao login</a>
    </div>
</body>

</html>
