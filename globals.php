<?php

if (!isset($_SESSION)) {
    session_start();
}
// print_r($_SESSION);
$BASE_URL = "http://" . $_SERVER["SERVER_NAME"] . dirname($_SERVER["REQUEST_URI"] . "?") . "/";
