<?php
$cpf = 11122233389;
// print_r($cpf);
$bloco_1 = substr($cpf, 0, 3);
$bloco_2 = substr($cpf, 3, 3);
$bloco_3 = substr($cpf, 6, 3);
$dig_verificador = substr($cpf, -2);
$cpf_formatado = $bloco_1 . "." . $bloco_2 . "." . $bloco_3 . "-" . $dig_verificador;
print_r($cpf_formatado);
