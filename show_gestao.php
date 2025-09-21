<!DOCTYPE html>
<html lang="pt-br">
<script src="js/timeout.js"></script>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
    <?php
    include_once("check_logado.php");

    include_once("globals.php");
    include_once("templates/header.php");

    include_once("models/internacao.php");
    require_once("dao/internacaoDao.php");

    require_once("models/message.php");

    include_once("models/hospital.php");
    include_once("dao/hospitalDao.php");

    include_once("models/paciente.php");
    include_once("dao/pacienteDAO.php");

    include_once("models/gestao.php");
    include_once("dao/gestaoDao.php");


    // Pegar o id da internacao
    $id_gestao = filter_input(INPUT_GET, "id_gestao", FILTER_SANITIZE_NUMBER_INT);
    // $where = $id_internacao;
    $internacao;
    $order = null;
    $obLimite = 1;
    $gestaoDao = new gestaoDAO($conn, $BASE_URL);

    //Instanciar o metodo internacao   
    $condicoes = [
        strlen($id_gestao) ? 'ge.id_gestao LIKE "%' . $id_gestao . '%"' : null,

    ];
    $condicoes = array_filter($condicoes);
    // REMOVE POSICOES VAZIAS DO FILTRO
    $where = implode(' AND ', $condicoes);
    $gestao = $gestaoDao->selectAllGestaoLis($where, $order, $obLimite);
    // print_r($gestao);

    ?>
    <div style="margin:15px" id='main-container'>
        <span><button type="submit"
                style="margin-left:3px; font-size: 25px; background:transparent; border-color:transparent; color:green"
                style="margin-top:10px; margin-left:20px" class="delete-btn"><i
                    class="d-inline-block fas fa-eye check-icon"></i></button>
            <h4 style="margin-top:10px; margin-left:20px">Dados da gestão do paciente: <?= $gestao['0']['nome_pac'] ?>
            </h4>
        </span>

        <div class="card-header container-fluid" id="view-contact-container">
            <span style="font-weight: 500;" class="card-title bold">ID Internação:</span>
            <span class="card-title bold"><?= $gestao['0']['id_internacao'] ?></span>
            <br>
        </div>

        <div class="card-body">
            <span style="font-weight: 500;" class=" card-text bold">Hospital:</span>
            <span class=" card-text bold"><?= $gestao['0']['nome_hosp'] ?></span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">Data Internação:</span>
            <span class=" card-text bold"><?= date("d/m/Y", strtotime($gestao['0']['data_intern_int'])) ?></span>
            <br>
            <hr>

            <?php if ($gestao['0']['home_care_ges'] == "s") { ?>
            <?php if ($gestao['0']['home_care_ges'] == "s") { ?>
            <span style="font-weight: 700;" class=" card-text bold">Notificação de Home Care</span>
            <?php } ?>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">Relatório:</span>
            <span class=" card-text bold"><?= $gestao['0']['rel_home_care_ges'] ?></span>
            <hr>
            <?php } ?>
            <?php if ($gestao['0']['home_care_ges'] == "s") { ?>
            <?php if ($gestao['0']['desospitalizacao_ges'] == "s") { ?>
            <span style="font-weight: 700;" class=" card-text bold">Notificação de Desospitalização</span>
            <?php } ?>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">Relatório:</span>
            <span class=" card-text bold"><?= $gestao['0']['rel_desospitalizacao_ges'] ?></span>
            <hr>
            <?php } ?>

            <?php if ($gestao['0']['alto_custo_ges'] == "s") { ?>

            <!-- <span style="font-weight: 500;" class=" card-text bold">Alto custo:</span> -->

            <span class=" card-text bold">
                <?php if ($gestao['0']['alto_custo_ges'] == "s") { ?>
                <span style="font-weight: 700;" class=" card-text bold">Notificação de Alto Custo</span>
                <?php } ?>
            </span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">Relatório:</span>
            <span class=" card-text bold"><?= $gestao['0']['rel_alto_custo_ges'] ?></span>
            <br>
            <hr>
            <?php } ?>

            <?php if ($gestao['0']['opme_ges'] == "s") { ?>
            <span class=" card-text bold">
                <?php if ($gestao['0']['opme_ges'] == "s") { ?>
                <span style="font-weight: 700;" class=" card-text bold">Notificação de OPME</span>
                <?php } ?>
            </span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">Relatório:</span>
            <span class=" card-text bold"><?= $gestao['0']['rel_opme_ges'] ?></span>
            <br>
            <hr>
            <?php } ?>

            <?php if ($gestao['0']['evento_adverso_ges'] == "s") { ?>
            <?php if ($gestao['0']['evento_adverso_ges'] == "s") { ?>
            <span style="font-weight: 700;" class=" card-text bold">Notificação de Evento Adverso</span>
            <?php } ?>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">Relatório:</span>
            <span class=" card-text bold"><?= $gestao['0']['rel_evento_adverso_ges'] ?></span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">Tipo Evento:</span>
            <span class=" card-text bold"><?= $gestao['0']['tipo_evento_adverso_gest'] ?></span>
            <br>
            <hr>
            <?php } ?>

            <br>
        </div>

        <?php
        include_once("diversos/backbtn_gestao.php");
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    require_once("templates/footer.php");
    ?>
</body>

</html>