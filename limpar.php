<?php
$login = "Um teste de or '1='1;";
$resultado = preg_replace('/[^[:alpha:]_]/', '', $login);
echo $resultado;
