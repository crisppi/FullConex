<?php
include_once("check_logado.php");

require_once("models/usuario.php");
require_once("models/usuario.php");
require_once("dao/usuarioDao.php");
require_once("dao/pacienteDao.php");
require_once("templates/header.php");

include_once("array_dados.php");

$user = new Usuario();
$usuarioDao = new UserDAO($conn, $BASE_URL);

// Receber id do usuário
$id_usuario = filter_input(INPUT_GET, "id_usuario");

$usuario = $usuarioDao->findById_user($id_usuario);

?>

<!-- formulario update -->

<div id="main-container" class="container">
    <div class="row">
        <h4 class="page-title">Atualizar</h4>
        <form action="<?= $BASE_URL ?>process_usuario.php" id="add-movie-form" method="POST"
            enctype="multipart/form-data">
            <input type="hidden" class="form-control" id="id_usuario" name="id_usuario"
                value="<?= $usuario->id_usuario ?>">

            <input type="hidden" name="type" value="update">
            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label for="usuario_user">Nome do Usuário</label>
                    <input type="text" class="form-control" id="usuario_user" name="usuario_user"
                        value="<?= $usuario->usuario_user ?>" autofocus required>
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="sexo_user">Sexo</label>
                    <select class="form-control" id="sexo_user" name="sexo_user">
                        <option value="m" <?php if ($usuario->sexo_user == 'm')
                                                echo 'selected'; ?>>
                            <?php echo "Masc"; ?>
                        </option>
                        <option value="f" <?php if ($usuario->sexo_user == 'f')
                                                echo 'selected'; ?>>
                            <?php echo "Fem"; ?>
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-1">
                    <label for="usuario_user">Idade</label>
                    <input type="text" class="form-control" id="idade_user" name="idade_user"
                        value="<?= $usuario->idade_user ?>">
                </div>

            </div>
            <hr>
            <div class="form-group row">
                <div class="form-group col-sm-3">
                    <label for="cep_user">CEP</label>
                    <input type="text" onkeyup="consultarCEP(this, 'user')" class="form-control" id="cep_user"
                        name="cep_user" placeholder="Digite o CEP">
                </div>
                <div class="form-group col-sm-3">
                    <label for="endereco_user">Endereço</label>
                    <input type="text" readonly class="form-control" id="endereco_user" name="endereco_user"
                        placeholder="...">
                </div>
                <div class="form-group col-sm-3">
                    <label for="bairro_user">Bairro</label>
                    <input type="text" readonly class="form-control" id="bairro_user" name="bairro_user"
                        placeholder="...">
                </div>
                <div class="form-group col-sm-3">
                    <label for="cidade_user">Cidade</label>
                    <input type="text" readonly class="form-control" id="cidade_user" name="cidade_user"
                        placeholder="...">
                </div>
                <div class="form-group col-sm-2">
                    <label for="estado_user">Estado</label>
                    <select class="form-control" id="estado_user" name="estado_user">
                        <option value="">...</option>
                        <?php foreach ($estado_sel as $estado): ?>
                        <option value="<?= $estado ?>">
                            <?= $estado ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label for="numero_user">Número</label>
                    <input type="text" class="form-control" id="numero_user" name="numero_user">
                </div>
                <div class="form-group col-sm-2">
                    <label for="numero_user">Complemento</label>
                    <input type="text" class="form-control" id="numero_user" name="numero_user">
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="form-group col-sm-2 ">
                    <label for="cpf_user">CPF</label>
                    <input type="text" oninput="mascara(this, 'cpf')" class="form-control" id="cpf_user" name="cpf_user"
                        value="<?= $usuario->cpf_user ?>">
                </div>
                <div class="form-group col-sm-2">
                    <label for="email_user">email01</label>
                    <input type="email" class="form-control" id="email_user" name="email_user"
                        value="<?= $usuario->email_user ?>">
                </div>
                <div class="form-group col-sm-2">
                    <label for="email02_user">email02</label>
                    <input type="email" class="form-control" id="email02_user" name="email02_user"
                        value="<?= $usuario->email02_user ?>">
                </div>
                <div class="form-group col-sm-2">
                    <label for="telefone01_user">Telefone</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone01_user" name="telefone01_user" value="<?= $usuario->telefone01_user ?>">
                </div>
                <div class="form-group col-sm-2">
                    <label for="telefone02_user">Telefone - 02</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone02_user" name="telefone02_user" value="<?= $usuario->telefone02_user ?>">

                </div>
            </div>
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label class="control-label" for="cargo_user">Cargo</label>
                    <select class="form-control" id="cargo_user" name="cargo_user">
                        <option value="<?= $usuario->cargo_user; ?>" <?php if ($usuario->cargo_user == $usuario->cargo_user)
                                                                            echo 'selected'; ?>>
                            <?php echo $usuario->cargo_user; ?>
                            <?php foreach ($cargo_user as $cargo): ?>
                        <option value="<?= $cargo ?>">
                            <?= $cargo ?>
                        </option>
                        <?php endforeach; ?>
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="nivel_user">Nível</label>
                    <select class="form-control" id="nivel_user" name="nivel_user">
                        <option value="1" <?php if ($usuario->nivel_user == '1')
                                                echo 'selected'; ?>>
                            <?php echo "Nível 01"; ?>
                        </option>
                        <option value="2" <?php if ($usuario->nivel_user == '2')
                                                echo 'selected'; ?>>
                            <?php echo "Nível 02"; ?>
                        </option>
                        <option value="3" <?php if ($usuario->nivel_user == '3')
                                                echo 'selected'; ?>>
                            <?php echo "Nível 03"; ?>
                        </option>
                        <option value="4" <?php if ($usuario->nivel_user == '4')
                                                echo 'selected'; ?>>
                            <?php echo "Nível 04"; ?>
                        </option>
                        <option value="5" <?php if ($usuario->nivel_user == '5')
                                                echo 'selected'; ?>>
                            <?php echo "Nível 05"; ?>
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2 ">
                    <label class="control-label" for="depto_user">Departamento</label>
                    <select class="form-control" name="depto_user">
                        <option value="<?= $usuario->depto_user; ?>" <?php if ($usuario->depto_user == $usuario->depto_user)
                                                                            echo 'selected'; ?>>
                            <?php echo $usuario->depto_user; ?>
                            <?php foreach ($depto_sel as $depto): ?>
                        <option value="<?= $depto ?>">
                            <?= $depto ?>
                        </option>
                        <?php endforeach; ?>
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2 ">
                    <label class="control-label" for="vinculo_user">Selecione o vínculo</label>
                    <select class="form-control" name="vinculo_user">
                        <option value="<?= $usuario->vinculo_user; ?>" <?php if ($usuario->vinculo_user == $usuario->vinculo_user)
                                                                            echo 'selected'; ?>>
                            <?php echo $usuario->vinculo_user; ?>
                            <?php foreach ($vinculo_sel as $vinculo): ?>
                        <option value="<?= $vinculo ?>">
                            <?= $vinculo ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-left:10px" class="form-group col-sm-2">
                    <label class="control-label" for="data_admissao_user">Admissão</label>
                    <input class="form-control" value="<?= $usuario->data_admissao_user ?>" type="date"
                        id="data_admissao_user" name="data_admissao_user">
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="ativo_user">Ativo</label>
                    <select class="form-control" id="ativo_user" name="ativo_user">
                        <option value="s" <?php if ($usuario->ativo_user == 's')
                                                echo 'selected'; ?>>
                            <?php echo "Sim"; ?>
                        </option>
                        <option value="n" <?php if ($usuario->ativo_user == 'n')
                                                echo 'selected'; ?>>
                            <?php echo "Não"; ?>
                        </option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label for="reg_profissional_user">Reg.Profissional</label>
                    <input type="text" class="form-control" id="reg_profissional_user" name="reg_profissional_user"
                        value="<?= $usuario->reg_profissional_user ?>">
                </div>
                <div class="form-group col-sm-1">
                    <label for="tipo_reg_user">Tipo Reg</label>
                    <select class="form-control" name="tipo_reg_user">
                        <option <?php if ($usuario->tipo_reg_user == $usuario->tipo_reg_user)
                                    echo 'selected'; ?>>
                            <?php echo $usuario->tipo_reg_user; ?>
                        </option>
                        <?php foreach ($tipo_reg as $reg): ?>
                        <option value="<?= $reg ?>">
                            <?= $reg ?>
                        </option>
                        <?php endforeach; ?>
                        </option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="senha_user">Senha</label>
                    <input type="text" class="form-control" value="<?= $usuario->senha_user ?>" id="senha_user"
                        name="senha_user">
                </div>

                <div class="form-group col-sm-4">
                    <?php $agora = date('Y-m-d'); ?>
                    <input class="visible" type="hidden" class="form-control" value='<?= $agora; ?>'
                        id="data_create_user" name="data_create_user">
                </div>
                <div class="form-group col-sm-4">
                    <input type="hidden" class="form-control" id="usuario_create_user"
                        value="<?= $_SESSION['email_user'] ?>" name="usuario_create_user">
                </div>
                <div class="form-group col-sm-4">
                    <input type="hidden" class="form-control" id="fk_usuario_user"
                        value="<?= $_SESSION['id_usuario'] ?>" name="fk_usuario_user">
                </div>
                <div class="form-group col-sm-2">
                    <input type="hidden" class="form-control" value="s" id="senha_default_user"
                        name="senha_default_user">
                </div>
                <div class="form-group col-sm-12">
                    <label for="obs_user">Observações</label>
                    <textarea type="textarea" rows="2" onclick="aumentarTextObs()" class="form-control" id="obs_user"
                        name="obs_user"><?= $usuario->obs_user ?></textarea>
                </div>
                <div class="form-group-sm row">
                    <div class="form-group-sm col-sm-2">
                        <label for="foto_usuario">Foto</label>
                        <input type="file" onclick="novoArquivo()" name="foto_usuario" id="foto_usuario"
                            accept="image/png, image/jpeg">
                        <div class="notif-input oculto" id="notifImagem">
                            Tamanho do arquivo inválido!
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <br>
    </div>
    </form>
</div>

<script>
function mascara(i) {

    var v = i.value;

    if (isNaN(v[v.length - 1])) { // impede entrar outro caractere que não seja número
        i.value = v.substring(0, v.length - 1);
        return;
    }

    i.setAttribute("maxlength", "14");
    if (v.length == 3 || v.length == 7) i.value += ".";
    if (v.length == 11) i.value += "-";

}

function mascaraTelefone(event) {
    let tecla = event.key;
    let telefone = event.target.value.replace(/\D+/g, "");

    if (/^[0-9]$/i.test(tecla)) {
        telefone = telefone + tecla;
        let tamanho = telefone.length;

        if (tamanho >= 12) {
            return false;
        }

        if (tamanho > 10) {
            telefone = telefone.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
        } else if (tamanho > 5) {
            telefone = telefone.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
        } else if (tamanho > 2) {
            telefone = telefone.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
        } else {
            telefone = telefone.replace(/^(\d*)/, "($1");
        }

        event.target.value = telefone;
    }

    if (!["Backspace", "Delete"].includes(tecla)) {
        return false;
    }
}
var text_obs = document.querySelector("#obs_user");

function aumentarTextObs() {
    if (text_obs.rows == "2") {
        text_obs.rows = "20"
    } else {
        text_obs.rows = "2"
    }
}
</script>
<!-- mascara de cpf, telefone  -->
<script>
function mascara(i, t) {

    var v = i.value;

    if (isNaN(v[v.length - 1])) {
        i.value = v.substring(0, v.length - 1);
        return;
    }

    if (t == "data") {
        i.setAttribute("maxlength", "10");
        if (v.length == 2 || v.length == 5) i.value += "/";
    }

    if (t == "cpf") {
        i.setAttribute("maxlength", "14");
        if (v.length == 3 || v.length == 7) i.value += ".";
        if (v.length == 11) i.value += "-";
    }

    if (t == "cnpj") {
        i.setAttribute("maxlength", "18");
        if (v.length == 2 || v.length == 6) i.value += ".";
        if (v.length == 10) i.value += "/";
        if (v.length == 15) i.value += "-";
    }

    if (t == "cep") {
        i.setAttribute("maxlength", "9");
        if (v.length == 5) i.value += "-";
    }

    if (t == "tel") {
        if (v[0] == 12) {

            i.setAttribute("maxlength", "10");
            if (v.length == 5) i.value += "-";
            if (v.length == 0) i.value += "(";

        } else {
            i.setAttribute("maxlength", "9");
            if (v.length == 4) i.value += "-";
        }
    }
}

function mascaraTelefone(event) {
    let tecla = event.key;
    let telefone = event.target.value.replace(/\D+/g, "");

    if (/^[0-9]$/i.test(tecla)) {
        telefone = telefone + tecla;
        let tamanho = telefone.length;

        if (tamanho >= 12) {
            return false;
        }

        if (tamanho > 10) {
            telefone = telefone.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
        } else if (tamanho > 5) {
            telefone = telefone.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
        } else if (tamanho > 2) {
            telefone = telefone.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
        } else {
            telefone = telefone.replace(/^(\d*)/, "($1");
        }

        event.target.value = telefone;
    }

    if (!["Backspace", "Delete"].includes(tecla)) {
        return false;
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>


<?php
include_once("templates/footer.php");
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

</html>