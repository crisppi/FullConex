<?php
include_once("check_logado.php");

include_once("models/pagination.php");

?>
<!DOCTYPE html>
<html lang="pt-br">
<script src="js/timeout.js"></script>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $BASE_URL ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $BASE_URL ?>js/timeout.js"></script>
    <link rel="stylesheet" href="<?= $BASE_URL ?>css/table_style.css">
</head>

<?php

$busca = filter_input(INPUT_GET, 'pesquisa_nome',FILTER_SANITIZE_SPECIAL_CHARS);
$ativo_hosp = filter_input(INPUT_GET, 'ativo_hosp',FILTER_SANITIZE_SPECIAL_CHARS);
$bl = filter_input(INPUT_GET, 'bl',FILTER_SANITIZE_SPECIAL_CHARS);


include_once("formularios/form_list_HospitalUser.php");
include_once("templates/footer.php");
?>

</html>
