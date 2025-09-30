<?php
// Conexão principal (mydb_accert_ho - Hostinger)
// $host2 = "2.59.150.2";
// $user2 = "u650318666_diretoria10";
// $pass2 = "Fullcare12@";
// $dbname2 = "u650318666_mydb_accert_ho";

// Conexão alternativa 1 (mydb_accert_new - UOLHOST)
$host1 = "mydb-accert-new.mysql.uhserver.com";
$user1 = "diretoria5";
$pass1 = "Fullcare12@";
$dbname1 = "mydb_accert_new";

// Conexão alternativa 2 (mydb_accert - UOLHOST)
$host3 = "mdb-accert.mysql.uhserver.com";
$user3 = "diretoria2";
$pass3 = "Guga@0401";
$dbname3 = "mydb_accert";

$charset = "utf8";
$port = 3306;
$fonte_conexao = "";

try {
    // Tentativa com a conexão principal (Hostinger)
    $conn = new PDO("mysql:host=$host1;dbname=$dbname1;charset=$charset", $user1, $pass1);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $fonte_conexao = "Hostinger ($dbname1)";
} catch (Exception $e1) {
    try {
        // Tentativa com a alternativa 1 (UOLHOST NEW)
        $conn = new PDO("mysql:host=$host2;dbname=$dbname2;charset=$charset", $user2, $pass2);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $fonte_conexao = "UOLHOST NEW ($dbname2)";
    } catch (Exception $e2) {
        try {
            // Tentativa com a alternativa 2 (UOLHOST Fallback)
            $conn = new PDO("mysql:host=$host3;dbname=$dbname3;charset=$charset", $user3, $pass3);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $fonte_conexao = "UOLHOST Fallback ($dbname3)";
        } catch (Exception $e3) {
            header("Location: sem_conexao.html");
            exit("❌ Falha nas conexões com os bancos de dados.");
        }
    }
}