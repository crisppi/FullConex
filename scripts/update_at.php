<?php

// criar campo updated_at em todas as tabelas do banco mydb_accert_new
$host = "mydb-accert-new.mysql.uhserver.com";
$dbname = "mydb_accert_new";
$user = "diretoria5";
$pass = 'Fullcare12@';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obter todas as tabelas do banco
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Verificar se a coluna já existe
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE 'updated_at'");
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            echo "Adicionando updated_at na tabela `$table`...\n";

            // Adicionar campo updated_at
            $alter = "ALTER TABLE `$table` 
                      ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP 
                      ON UPDATE CURRENT_TIMESTAMP";
            $pdo->exec($alter);
        } else {
            echo "Tabela `$table` já tem o campo updated_at.\n";
        }
    }

    echo "Concluído.\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
