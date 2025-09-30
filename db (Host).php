<?php
$host = "mysql.hostinger.com"; // Altere para o seu host
$user = "u650318666_root"; // Ou outro usuário definido
$pass = "Full@2025"; // Sua senha real
$dbname = "u650318666_mydb_accerthos";
$port = 3306;

try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::MYSQL_ATTR_SSL_CA => __DIR__ . "/cert/ca-certificate.crt", // Caminho correto para o certificado
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true

        ]
    );
} catch (Exception $e) {
    header("Location: sem_conexao.html");
    echo "Falha na conexão: " . $e->getMessage();
    include_once('semacesso.php');
}