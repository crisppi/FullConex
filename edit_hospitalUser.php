<?php
// DEBUG TEMPORÁRIO (REMOVER APÓS TESTE)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);
include_once("check_logado.php");
include_once("check_logado.php");

require_once("templates/header.php");

require_once("models/hospitalUser.php");
require_once("dao/hospitalUserDao.php");

require_once("models/message.php");
include_once("check_logado.php");

require_once("templates/header.php");

include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

include_once("models/message.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("models/patologia.php");
include_once("dao/patologiaDao.php");

include_once("models/usuario.php");
include_once("dao/usuarioDAO.php");

include_once("models/uti.php");
include_once("dao/utiDao.php");

include_once("models/gestao.php");
include_once("dao/gestaoDao.php");

include_once("models/prorrogacao.php");
include_once("dao/prorrogacaoDao.php");

include_once("models/negociacao.php");
include_once("dao/negociacaoDao.php");

include_once("array_dados.php");

$internacaoDao = new internacaoDAO($conn, $BASE_URL);

$hospital_geral = new hospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$usuarioDao = new userDAO($conn, $BASE_URL);
$usuarios = $usuarioDao->findGeral($limite, $inicio);

$hospitalUserDao = new hospitalUserDAO($conn, $BASE_URL);
// $hospitalUser = $hospitalUserDao->findGeral();

// Receber id do usuário
$id_hospitalUser = filter_input(INPUT_GET, "id_hospitalUser");


$hospitalUser = $hospitalUserDao->joinHospitalUser($id_hospitalUser);
// extract($hospitalUser);

?>

<!-- formulario update -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div id="main-container" class="container">
    <div class="row">

        <h4 class="page-title">Atualizar Hospital por Usuário</h4>


        <form class="formulario-borderless" action="<?= $BASE_URL ?>process_hospitalUser.php" id="add-movie-form"
            method="POST" enctype="multipart/form-data">
            <input type="hidden" name="type" value="update">
            <input type="hidden" name="id_hospitalUser" value="<?= $id_hospitalUser ?>">
            <div class="form-group row">
                <!-- Select para Hospital -->
                <div class="form-group col-sm-3">
                    <label class="control-label" for="fk_hospital_user">Hospital</label>
                    <select class="form-control" id="fk_hospital_user" name="fk_hospital_user" required>
                        <option value="<?= $hospitalUser['fk_hospital_user']; ?>"
                            <?= ($hospitalUser['fk_hospital_user'] == $hospitalUser['fk_hospital_user']) ? 'selected' : '' ?>>
                            <?= $hospitalUser['nome_hosp']; ?>
                        </option>
                        <?php foreach ($hospitals as $hospital): ?>
                            <option value="<?= $hospital["id_hospital"] ?>"><?= $hospital["nome_hosp"] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Select para Usuário (com usuário e e-mail concatenados) -->
                <div class="form-group col-sm-4">
                    <label class="control-label" for="fk_usuario_hosp">Usuário e email</label>
                    <select class="form-control" id="fk_usuario_hosp" name="fk_usuario_hosp" required>
                        <?php if (!empty($hospitalUser) && isset($hospitalUser['fk_usuario_hosp'])): ?>
                            <option value="<?= $hospitalUser['fk_usuario_hosp']; ?>" selected>
                                <?= $hospitalUser['usuario_user'] . '   -   ' . $hospitalUser['email_user'] . '   -   ' . $hospitalUser['cargo_user']; ?>
                            </option>
                        <?php endif; ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['id_usuario']; ?>"
                                <?= (isset($hospitalUser['fk_usuario_hosp']) && $hospitalUser['fk_usuario_hosp'] == $usuario['id_usuario']) ? 'selected' : ''; ?>>
                                <?= $usuario['usuario_user'] . '   -   ' . $usuario['email_user'] . '   -   ' . $usuario['cargo_user']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p style="margin: 15px 0; font-size: 1rem; font-weight: 500;">
                    O usuário <strong><?= $hospitalUser['usuario_user'] ?></strong> está cadastrado para realizar
                    auditoria no hospital <strong><?= $hospitalUser['nome_hosp'] ?> Deseja alterar?</strong>.
                </p>

            </div>
            <br>
            <button type="submit" class="btn btn-primary">
                <i style="font-size: 1rem; margin-right: 5px;" name="type" value="edite"
                    class="fa-solid fa-check edit-icon"></i>Atualizar
            </button>
        </form>
    </div>
</div>

<?php
include_once("templates/footer.php");
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>


</html>