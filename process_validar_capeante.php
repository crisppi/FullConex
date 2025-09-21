<?php

require_once("globals.php");
require_once("db.php");
require_once("dao/capeanteDao.php");

// Instantiate capeanteDAO
$capeanteDao = new capeanteDAO($conn, $BASE_URL);

// Get the ID from POST data
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT); // Validate as integer

if ($id) {
    // Find capeante by ID
    $capeante = $capeanteDao->findById($id);

    if ($capeante) {
        // Set the "impresso_cap" field to 's'
        $capeante->validacao_cap = 's';

        // Update the capeante in the database
        $capeanteDao->update($capeante);
        echo "Record updated successfully.";
    } else {
        echo "Record not found.";
    }
} else {
    echo "Invalid ID.";
}
