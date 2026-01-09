<?php

// Mensagem inicial
echo "<div style='background-color:#fff3cd; color:#856404; padding:10px; text-align:center;'>
        🔄 <strong>Sincronização iniciada</strong> às " . date('d/m/Y H:i:s') . "...
      </div>";

echo "<div style='background-color:#fff3cd; color:#856404; padding:10px; text-align:center; border-bottom:1px solid #ffeeba;'>
        🔄 Iniciando sincronização do banco <strong>Hostinger</strong> para <strong>UOLHost</strong>...
      </div>";

require_once __DIR__ . '/../db.php';

$pdoOrigem = $conn;
$pdoDestino = null;
$hostDestino = "mydb-accert-new.mysql.uhserver.com";
$userDestino = "diretoria5";
$passDestino = "Fullcare12@";
$dbnameDestino = "mydb_accert_new";
$charset = "utf8";

try {
    $pdoDestino = new PDO("mysql:host={$hostDestino};dbname={$dbnameDestino};charset={$charset}", $userDestino, $passDestino);
    $pdoDestino->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    exit("❌ Erro de conexão (destino): " . $e->getMessage());
}

// Obter todas as tabelas da origem
try {
    $tabelas = $pdoOrigem->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $totalTabelas = count($tabelas);
    echo "<h3 style='text-align:center;color:#444;'>🔄 Iniciando sincronização de <strong>$totalTabelas</strong> tabelas...</h3>";
} catch (Exception $e) {
    exit("Erro ao listar tabelas: " . $e->getMessage());
}

// Loop pelas tabelas
foreach ($tabelas as $tabela) {
    try {
        // Desabilita constraints no destino
        $pdoDestino->exec("SET FOREIGN_KEY_CHECKS = 0");

        // Busca dados da origem
        $stmt = $pdoOrigem->query("SELECT * FROM `$tabela`");
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apaga os dados da tabela destino
        $pdoDestino->exec("DELETE FROM `$tabela`");

        // Insere novamente os dados
        foreach ($dados as $linha) {
            $colunas = implode(", ", array_map(fn($c) => "`$c`", array_keys($linha)));
            $valoresMarcadores = ":" . implode(", :", array_keys($linha));

            $sql = "INSERT INTO `$tabela` ($colunas) VALUES ($valoresMarcadores)";
            $stmtInsert = $pdoDestino->prepare($sql);

            foreach ($linha as $coluna => $valor) {
                $stmtInsert->bindValue(":$coluna", $valor);
            }

            $stmtInsert->execute();
        }

        // Reabilita constraints
        $pdoDestino->exec("SET FOREIGN_KEY_CHECKS = 1");

        echo "<div style='background:#e8f5e9;padding:6px;margin:4px;border-left:5px solid #2e7d32'>
                ✅ Tabela <strong>$tabela</strong> sincronizada com sucesso.
              </div>";
    } catch (Exception $e) {
        echo "<div style='background:#ffebee;padding:6px;margin:4px;border-left:5px solid #c62828'>
                ❌ Erro ao sincronizar tabela <strong>$tabela</strong>: {$e->getMessage()}
              </div>";
    }
}

echo "<div style='background-color:#d4edda; color:#155724; padding:10px; text-align:center;'>
        ✅ <strong> 🎉 Sincronização concluída</strong> às " . date('d/m/Y H:i:s') . "
      </div>";

?>
