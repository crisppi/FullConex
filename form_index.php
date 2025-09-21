<form action="check_login.php" method="POST" autocomplete="off">
    <div class="inputs">
        <input type="hidden" id="loggedin" name="loggedin" value="loggedin">
        <input type="text" placeholder="Usuario" name="email_login" onfocus="ocultar()" id="email_login" class="input"
            required autocomplete="off"
            style="border-radius: 10px; border: 1px solid #ccc; padding: 10px; font-size: 14px; width: 100%; box-sizing: border-box;">
        <br>
        <input type="password" placeholder="Senha" id="senha_login" name="senha_login" class="input" required
            autocomplete="off"
            style="border-radius: 10px; border: 1px solid #ccc; padding: 10px; font-size: 14px; width: 100%; box-sizing: border-box;">
    </div>

    <br><br>

    <div class="remember-me--forget-password">
        <p style="text-align:center; margin-right:40px">Caso o acesso não esteja funcionando entre em contato com a
            administração!</p>
    </div>

    <br>
    <button style="background-color:#421849" type="submit">Login</button>
</form>

<script>
document.getElementById('email_login').setAttribute('autocomplete', 'off');
document.getElementById('senha_login').setAttribute('autocomplete', 'off');
</script>