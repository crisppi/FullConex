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
    include_once("models/internacao.php");
    require_once("dao/internacaoDao.php");

    include_once("models/hospital.php");
    include_once("dao/hospitalDao.php");

    include_once("models/patologia.php");
    include_once("dao/patologiaDao.php");

    include_once("models/paciente.php");
    include_once("dao/pacienteDAO.php");

    include_once("models/capeante.php");
    include_once("dao/capeanteDAO.php");


    // Pegar o id da internacao
    // Pegar o id da internacao
    $id_capeante = filter_input(INPUT_GET, "id_capeante", FILTER_SANITIZE_NUMBER_INT);
    $fk_int_capeante = filter_input(INPUT_GET, "fk_int_capeante", FILTER_SANITIZE_NUMBER_INT);
    $where = $fk_int_capeante;
    $condicoes = [
        strlen($id_capeante) ? 'ca.id_capeante LIKE "%' . $id_capeante . '%"' : null,
    ];

    $condicoes = array_filter($condicoes);
    // REMOVE POSICOES VAZIAS DO FILTRO
    $where = implode(' AND ', $condicoes);
    $internacao;
    $order = null;
    $obLimite = null;
    $capeanteDao = new capeanteDAO($conn, $BASE_URL);

    //Instanciar o metodo internacao   
    $internacao = $capeanteDao->selectAllcapeante($where, $order, $obLimite);
    ?>
    <div id='main-container' style="margin:15px">
        <span>
            <button type="submit"
                style="margin-left:3px; font-size: 15px; background:transparent; border-color:transparent; color:green"
                class="delete-btn">
                <i class="d-inline-block fas fa-eye check-icon">
                </i>
            </button>
            <h4 style="margin-top:10px; margin-left:20px"> Dados da internação do paciente:
                <?= $internacao['0']['nome_pac'] ?> </h4>
        </span>

        <div class="card-header container-fluid" id="view-contact-container">
            <span style="font-weight: 500;" class="card-title bold"> Internação: </span>
            <span class="card-title bold"> <?= $internacao['0']['id_internacao'] ?>
            </span>
            <br>
        </div>
        <div class="card-header container-fluid" id="view-contact-container">
            <span style="font-weight: 500;" class="card-title bold">
                Visita: </span>
            <span class="card-title bold">
                <?= date("d/m/Y", strtotime($internacao['0']['data_visita_int']))  ?>
            </span>
            <br>
        </div>
        <div class="card-body">

            <span style="font-weight: 500;" class=" card-text bold">
                Hospital: </span>
            <span class=" card-text bold">
                <?= $internacao['0']['nome_hosp'] ?>
            </span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">
                Data
                Internação:
            </span>
            <span class=" card-text bold">
                <?= date("d/m/Y", strtotime($internacao['0']['data_intern_int'])) ?>
            </span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">
                Tipo
                Internação:
            </span>
            <span class=" card-text bold">
                <?= $internacao['0']['tipo_admissao_int'] ?>
            </span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">
                Modo
                Admissão:
            </span>
            <span class=" card-text bold">
                <?= $internacao['0']['modo_internacao_int'] ?>
            </span>
            <br>

            <span style="font-weight: 500;" class=" card-text bold">
                Especialidade:
            </span>
            <span class=" card-text bold">
                <?= $internacao['0']['especialidade_int'] ?>
            </span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">
                Grupo
                Patologia:
            </span>
            <span class=" card-text bold">
                <?= $internacao['0']['grupo_patologia_int'] ?>
            </span>
            <br>
            <span style="font-weight: 500;" class=" card-text bold">
                Médico:
            </span>
            <span class=" card-text bold">
                <?= $internacao['0']['titular_int'] ?>
            </span>
            <hr>

            <span style="font-weight: 500;" class=" card-text bold">
                Valor
                Apresentado:
            </span>
            <span class=" texto2">
                <?php
                $numero = floatval($internacao['0']['valor_apresentado_capeante']);
                echo "R$ " . number_format($numero, 2, ',', '.')
                ?>
            </span>
            <br>

            <span style="font-weight: 500;" class=" card-text bold">
                Valor
                Final:
            </span>
            <span class=" texto2">
                <?php
                $numero = floatval($internacao['0']['valor_final_capeante']);
                echo "R$ " . number_format($numero, 2, ',', '.')
                ?>
            </span>
            <hr>
        </div>

        <?php include_once("diversos/backbtn_capeante.php"); ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js">
    </script>
    <?php
    require_once("templates/footer.php");
    ?>
</body>

</html>