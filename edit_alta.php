<?php
include_once("check_logado.php");

require_once("templates/header.php");
require_once("models/usuario.php");
require_once("models/internacao.php");
require_once("dao/usuarioDao.php");
require_once("dao/internacaoDao.php");
include("array_dados.php");

$internacao = new internacao();
$userDao = new UserDAO($conn, $BASE_URL);
$internacaoDao = new internacaoDAO($conn, $BASE_URL);

// Receber id do usuário
$id_internacao = filter_input(INPUT_GET, "id_internacao");
$internacao = $internacaoDao->findById($id_internacao);

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$order = null;
$limite = null;
$condicoes = [
    strlen($id_internacao) ? 'id_internacao = "' . $id_internacao . '"' : NULL,
];
$condicoes = array_filter($condicoes);
// REMOVE POSICOES VAZIAS DO FILTRO
$where = implode(' AND ', $condicoes);
$internacao = $internacaoDao->selectAllInternacao($where, $order, $limite);
extract($internacao);

$dataAtual = date('Y-m-d');

?>

<style>
    .alta-page {
        width: 100%;
        margin: 0;
        padding: 0 0 40px;
    }

    .alta-hero {
        background: linear-gradient(135deg, #1f5d99, #58a9ff);
        color: #fff;
        border-radius: 28px;
        padding: 20px 24px;
        box-shadow: 0 20px 40px rgba(24, 0, 30, 0.25);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin: 15px;
    }

    .alta-hero h1 {
        margin: 0;
        font-size: 1.4rem;
        letter-spacing: .02em;
        color: #fff;
    }

    .alta-hero__tag {
        background: rgba(255, 255, 255, 0.2);
        padding: 6px 14px;
        border-radius: 999px;
        font-weight: 600;
        font-size: .78rem;
    }

    .alta-page__content {
        margin: 16px 15px 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .alta-card {
        background: #f5f5f9;
        border: 1px solid #ebe1f5;
        border-radius: 18px;
        box-shadow: 0 12px 24px rgba(45, 18, 70, .08);
        padding: 18px 18px 22px;
    }

    .alta-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .alta-card__eyebrow {
        text-transform: uppercase;
        letter-spacing: .3em;
        font-size: .62rem;
        margin: 0;
        color: #5e2363;
    }

    .alta-card__title {
        margin: 2px 0 0;
        font-size: 1.2rem;
        color: #2e114c;
        font-weight: 600;
    }

    .alta-card__tag {
        background: #f8eefc;
        color: #5e2363;
        padding: 4px 12px;
        border-radius: 999px;
        font-weight: 600;
        font-size: .75rem;
    }

    .alta-actions {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 4px 6px 0;
    }
</style>

<!-- formulario alta -->
<div class="alta-page">
    <div class="alta-hero">
        <div>
            <h1>Alta Hospitalar</h1>
        </div>
        <span class="alta-hero__tag">Campos obrigatórios em destaque</span>
    </div>

    <div class="alta-page__content">
        <form action="<?= $BASE_URL ?>process_alta.php" id="add-movie-form" method="POST"
            enctype="multipart/form-data">
            <input type="hidden" name="type" value="alta">

            <div class="alta-card alta-card--general">
                <div class="alta-card__header">
                    <div>
                        <p class="alta-card__eyebrow">Dados essenciais</p>
                        <h2 class="alta-card__title">Hospital, paciente e datas</h2>
                    </div>
                    <span class="alta-card__tag">Campos obrigatórios marcados</span>
                </div>
                <div class="row g-2">
                    <div class="form-group col-sm-1">
                        <label class="control-label">Id-Int</label>
                        <input type="text" readonly class="form-control" id="id_internacao" name="id_internacao"
                            value="<?= $internacao['0']['id_internacao'] ?>">
                    </div>
                    <div class="form-group col-sm-3">
                        <label class="control-label">Hospital</label>
                        <input type="text" readonly class="form-control" value="<?= $internacao['0']['nome_hosp'] ?>">
                    </div>
                    <div class="form-group col-sm-3">
                        <label class="control-label">Paciente</label>
                        <input type="text" readonly class="form-control" value="<?= $internacao['0']['nome_pac'] ?>">
                    </div>

                    <div class="form-group col-sm-2">
                        <label class="control-label" for="data_alta_int">Data internação</label>
                        <input type="date" class="form-control"
                            value='<?php echo $internacao['0']['data_intern_int'] ?>' id="data_intern_int"
                            name="data_intern_int" readonly placeholder="" required>
                    </div>
                </div>
            </div>

            <div class="alta-card alta-card--alta">
                <div class="alta-card__header">
                    <div>
                        <p class="alta-card__eyebrow">Alta</p>
                        <h3 class="alta-card__title">Data, hora e motivo</h3>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="data_alta_alt">Data Alta</label>
                        <input type="date" onchange="checkDataAlta()" class="form-control"
                            value='<?php echo date('Y-m-d') ?>' id="data_alta_alt" name="data_alta_alt" placeholder=""
                            autofocus required>
                        <div class="notif-input oculto" id="notif-input">
                            Data inválida !
                        </div>
                    </div>
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="hora_alta_alt">Hora Alta</label>
                        <input type="time" class="form-control" value='<?= date('H:i') ?>' id="hora_alta_alt"
                            name="hora_alta_alt" required>
                    </div>
                    <div class="form-group col-sm-3">
                        <label class="control-label" for="tipo_alta_alt">Tipo de alta</label>
                        <select class="form-control" id="tipo_alta_alt" name="tipo_alta_alt" required>
                            <option value="">Selecione o motivo da alta</option>
                            <?php
                            sort($dados_alta, SORT_ASC);
                            foreach ($dados_alta as $alta) { ?>
                            <option value="<?= $alta; ?>">
                                <?= $alta; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <input type="hidden" class="form-control" value="n" id="internado_int" name="internado_int"
                    placeholder="">
                <input type="hidden" class="form-control" value='<?php echo date('Y-m-d') ?>' id="data_create_alt"
                    name="data_create_alt" placeholder="">
                <input type="hidden" value="<?= $_SESSION['email_user']; ?>" class="form-control" id="usuario_alt"
                    name="usuario_alt">
                <input type="hidden" class="form-control" id="fk_usuario_alt" value="<?= $_SESSION['id_usuario'] ?>"
                    name="fk_usuario_alt" placeholder="Digite o usuário">
            </div>

            <?php if ($internacao['0']['internado_uti'] == "s") { ?>
            <div class="alta-card alta-card--uti">
                <div class="alta-card__header">
                    <div>
                        <p class="alta-card__eyebrow">UTI</p>
                        <h3 class="alta-card__title">Alta de UTI</h3>
                    </div>
                    <span class="alta-card__tag">Obrigatório</span>
                </div>
                <div class="row g-2">
                    <div class="form-group col-sm-2">
                        <label class="control-label" for="data_alta_uti">Data alta UTI</label>
                        <input type="date" class="form-control" value='<?php echo date('Y-m-d') ?>'
                            id="data_alta_uti" name="data_alta_uti" require>
                        <div class="notif-input oculto" id="notif-input2">
                            Data inválida !
                        </div>
                    </div>
                    <div class="form-group col-sm-2">
                        <input class="form-control" type="hidden" name="alta_uti" value="alta_uti">
                    </div>
                    <div class="form-group col-sm-2">
                        <input type="hidden" class="form-control" name="id_uti"
                            value="<?= $internacao['0']['fk_internacao_uti'] ?>">
                    </div>
                    <div class="form-group col-sm-2">
                        <input type="hidden" name="id_uti" value="<?= $internacao['0']['id_uti'] ?>">
                    </div>
                    <div class="form-group col-sm-2">
                        <input type="hidden" class="form-control" value="n" id="internado_uti" name="internado_uti"
                            placeholder="internado_uti">
                    </div>

                    <input type="hidden" name="type-uti" id="alta_uti" value="alta_uti">
                    <input type="hidden" name="fk_internacao_uti" id="fk_internacao_uti"
                        value="<?= $internacao['0']['fk_internacao_uti'] ?>">
                    <div class="form-group col-sm-2">
                        <input type="hidden" class="form-control" value="n" id="internado_uti" name="internado_uti"
                            placeholder="internado_uti">
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="alta-actions">
                <button id="cadastrar_alta" type="submit" class="btn btn-primary">
                    <i style="font-size: 1rem;" name="type" value="edite" class="fa-solid fa-check edit-icon"></i>
                    Alta
                </button>
                <?php include_once("diversos/backbtn_internacao.php"); ?>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<?php
include_once("templates/footer.php");
?>
<script src="js/scriptDataAltaHospitalar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>

</html>
