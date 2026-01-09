<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "Conexao invalida.\n");
    exit(1);
}

$user = 'u650318666_diretoria10';

// Certifica que a tabela de monitoramento existe
$conn->exec("
    CREATE TABLE IF NOT EXISTS monitor_conexoes (
        capturacao DATETIME NOT NULL,
        conexoes INT NOT NULL,
        PRIMARY KEY (capturacao)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4
");

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
      FROM information_schema.PROCESSLIST
     WHERE user = :user
");
$stmt->execute([':user' => $user]);
$count = (int) $stmt->fetchColumn(0);

$insert = $conn->prepare("
    INSERT INTO monitor_conexoes (capturacao, conexoes)
    VALUES (NOW(), :conexoes)
");
$insert->execute([':conexoes' => $count]);

echo date('Y-m-d H:i:s') . " -> {$count} conexoes registradas para {$user}.\n";
