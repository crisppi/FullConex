<?php
// include_once("check_logado.php");

require_once("models/usuario.php");
require_once("models/usuario.php");
require_once("dao/usuarioDao.php");
require_once("dao/pacienteDao.php");
require_once("templates/header.php");

include_once("array_dados.php");

$user = new Usuario();
$usuarioDao = new UserDAO($conn, $BASE_URL);

?>
<style>
    .password-page {
        min-height: calc(100vh - 190px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px 12px;
    }

    .password-card {
        width: 100%;
        max-width: 760px;
        border: 1px solid #e6dff1;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(70, 41, 96, 0.12);
        background: #fff;
    }

    .password-card-header {
        padding: 22px 24px;
        background: linear-gradient(120deg, #f9f3ff 0%, #eef4ff 100%);
        border-bottom: 1px solid #eee7f7;
    }

    .password-card-header h4 {
        margin: 0;
        color: #4f2763;
        font-weight: 700;
    }

    .password-card-header p {
        margin: 8px 0 0;
        color: #6f6f7d;
        font-size: 0.95rem;
    }

    .password-card-body {
        padding: 22px 24px 24px;
    }

    .password-label {
        font-weight: 600;
        color: #4f4f5d;
        margin-bottom: 6px;
    }

    .password-field .form-control {
        height: 44px;
        border-radius: 10px;
        border: 1px solid #d6d8e0;
    }

    .password-field .form-control:focus {
        border-color: #6c3a8a;
        box-shadow: 0 0 0 0.18rem rgba(108, 58, 138, 0.12);
    }

    .password-toggle {
        border: 1px solid #d6d8e0;
        border-left: none;
        border-radius: 0 10px 10px 0;
        background: #fafbff;
        color: #5a5f72;
        min-width: 46px;
    }

    .password-hint {
        margin-top: 10px;
        background: #f8f7ff;
        border: 1px solid #e4e2f7;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.86rem;
        color: #5d6074;
    }

    .password-hint ul {
        margin: 6px 0 0;
        padding-left: 18px;
    }

    .password-alert {
        display: none;
        margin-top: 8px;
        margin-bottom: 0;
        font-size: 0.86rem;
    }

    .password-actions {
        margin-top: 18px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<div id="main-container" class="container-fluid password-page">
    <div class="password-card">
        <div class="password-card-header">
            <h4>Primeiro acesso: altere sua senha</h4>
            <p>Para continuar, confirme sua senha atual e defina uma nova senha segura.</p>
        </div>

        <div class="password-card-body">
            <form action="<?= $BASE_URL ?>process_usuario.php" id="add-movie-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" class="form-control" id="id_usuario" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>">
                <input type="hidden" class="form-control" id="senha_usuario" name="senha_usuario" value="<?= $_SESSION['senha_user'] ?>">
                <input type="hidden" name="type" value="update-senha">
                <input type="hidden" class="form-control" value="n" id="senha_default_user" name="senha_default_user">

                <div class="row g-3">
                    <div class="col-12">
                        <label class="password-label" for="senha_user">Senha atual</label>
                        <div class="input-group password-field">
                            <input type="password" class="form-control" id="senha_user" name="senha_user"
                                autocomplete="current-password" oninput="checkInSenha()">
                            <button type="button" class="btn password-toggle" data-toggle-password="#senha_user" title="Mostrar/ocultar senha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="alert alert-danger password-alert" id="notif-erro">Senha incorreta, tente novamente.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="password-label" for="nova_senha_user">Nova senha</label>
                        <div class="input-group password-field">
                            <input type="password" class="form-control" id="nova_senha_user" name="nova_senha_user"
                                autocomplete="new-password" onkeyup="checkIn()">
                            <button type="button" class="btn password-toggle" data-toggle-password="#nova_senha_user" title="Mostrar/ocultar senha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="password-label" for="nova_senha_user2">Confirme a nova senha</label>
                        <div class="input-group password-field">
                            <input type="password" class="form-control" id="nova_senha_user2" autocomplete="new-password" onblur="check()">
                            <button type="button" class="btn password-toggle" data-toggle-password="#nova_senha_user2" title="Mostrar/ocultar senha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning password-alert" id="notif-input">As senhas informadas nao conferem.</div>

                <div class="password-hint">
                    Recomendacao:
                    <ul>
                        <li>Use pelo menos 8 caracteres.</li>
                        <li>Combine letras maiusculas, minusculas, numeros e simbolos.</li>
                        <li>Evite sequencias simples e dados pessoais.</li>
                    </ul>
                </div>

                <div class="password-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check me-1"></i>Atualizar senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once("templates/footer.php");
?>

<script src="js/senhas.js"></script>
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var selector = btn.getAttribute('data-toggle-password');
            var input = document.querySelector(selector);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
            }
        });
    });

    document.getElementById('add-movie-form').addEventListener('submit', function(e) {
        var senhaAtual = document.getElementById('senha_user').value || '';
        var novaSenha = document.getElementById('nova_senha_user').value || '';
        var novaSenha2 = document.getElementById('nova_senha_user2').value || '';
        var erroAtual = document.getElementById('notif-erro');
        var erroNova = document.getElementById('notif-input');

        erroAtual.style.display = 'none';
        erroNova.style.display = 'none';

        if (!senhaAtual || !novaSenha || !novaSenha2) {
            e.preventDefault();
            erroNova.textContent = 'Preencha todos os campos para continuar.';
            erroNova.style.display = 'block';
            return;
        }
        if (novaSenha !== novaSenha2) {
            e.preventDefault();
            erroNova.textContent = 'As senhas informadas nao conferem.';
            erroNova.style.display = 'block';
        }
    });

    function checkSenha() {
        return true;
    }

    function checkInSenha() {
        var divMsgErr = document.querySelector("#notif-erro");
        if (divMsgErr) {
            divMsgErr.style.display = "none";
        }
    }
</script>
</html>
