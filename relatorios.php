<?php

include_once("check_logado.php");
// session_start();
require_once("templates/header.php");
?>

<div>

    <iframe title="FullCare 2025" width="1140" height="600"
        src="https://app.powerbi.com/reportEmbed?reportId=473d4599-ca84-438c-b6a8-d6e014a7aca9&autoAuth=true&ctid=5d8203ef-bc77-4057-86a0-56d58ebd6258&chromeless=true"
        frameborder="0" allowFullScreen="true">
    </iframe>



</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<?php
require_once("templates/footer.php");
?>