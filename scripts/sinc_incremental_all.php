<?php
$logPath = __DIR__ . "/logs/sync_log.txt";
if (!file_exists(dirname($logPath))) {
    mkdir(dirname($logPath), 0755, true);
}

function registrarLog($mensagem) {
    global $logPath;
    $data = "[" . date('Y-m-d H:i:s') . "] ";
    file_put_contents($logPath, $data . $mensagem . PHP_EOL, FILE_APPEND);
}

// Mensagem inicial na tela
echo "<div style='background-color:#fff3cd; color:#856404; padding:10px; text-align:center;'>
        🔄 <strong>Sincronização incremental iniciada</strong> às " . date('d/m/Y H:i:s') . "
      </div>";
registrarLog("🔄 Sincronização iniciada.");

// Conexões
require_once __DIR__ . '/../db.php';

$pdoOrigem = $conn;
$pdoDestino = null;
$hostDestino = "mydb-accert-new.mysql.uhserver.com";
$dbnameDestino = "mydb_accert_new";
$userDestino = "diretoria5";
$passDestino = "Fullcare12@";
$charset = "utf8";

try {
    $pdoDestino = new PDO("mysql:host={$hostDestino};dbname={$dbnameDestino};charset={$charset}", $userDestino, $passDestino);
    $pdoDestino->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    registrarLog("❌ Erro de conexão (destino): " . $e->getMessage());
    exit("<div style='color:red;text-align:center;'>❌ Erro de conexão (destino): " . $e->getMessage() . "</div>");
}

// Limite de 4 horas atrás
$limiteTempo = date('Y-m-d H:i:s', strtotime('-4 hours'));
echo "<div style='text-align:center;'>🔎 Buscando registros alterados após <strong>$limiteTempo</strong></div>";
registrarLog("🔍 Buscando registros com updated_at >= $limiteTempo");

try {
    $tabelas = $pdoOrigem->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tabelas as $tabela) {
        try {
            $verifica = $pdoOrigem->query("SHOW COLUMNS FROM `$tabela` LIKE 'updated_at'");
            if ($verifica->rowCount() === 0) {
                echo "<div style='color:gray;'>⏭️ Tabela <strong>$tabela</strong> ignorada (sem campo updated_at)</div>";
                registrarLog("⏭️ Tabela $tabela ignorada (sem updated_at)");
                continue;
            }

            $stmt = $pdoOrigem->prepare("SELECT * FROM `$tabela` WHERE updated_at >= :limite");
            $stmt->bindValue(':limite', $limiteTempo);
            $stmt->execute();
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total = count($dados);
            if ($total === 0) {
                echo "<div style='color:gray;'>📭 Tabela <strong>$tabela</strong>: 0 registros a sincronizar.</div>";
                registrarLog("📭 Tabela $tabela: 0 registros encontrados.");
                continue;
            }

            $sincronizados = 0;

            foreach ($dados as $linha) {
                $colunas = implode(", ", array_map(fn($c) => "`$c`", array_keys($linha)));
                $valoresMarcadores = ":" . implode(", :", array_keys($linha));

                $sql = "INSERT INTO `$tabela` ($colunas) VALUES ($valoresMarcadores)
                        ON DUPLICATE KEY UPDATE ";

                $updates = [];
                foreach ($linha as $coluna => $valor) {
                    $updates[] = "`$coluna` = VALUES(`$coluna`)";
                }

                $sql .= implode(", ", $updates);

                $stmtInsert = $pdoDestino->prepare($sql);
                foreach ($linha as $coluna => $valor) {
                    $stmtInsert->bindValue(":$coluna", $valor);
                }

                $stmtInsert->execute();
                $sincronizados++;
            }

            echo "<div style='color:green;'>✅ Tabela <strong>$tabela</strong>: $sincronizados registros sincronizados.</div>";
            registrarLog("✅ Tabela $tabela: $sincronizados registros sincronizados.");

        } catch (Exception $eTabela) {
            echo "<div style='color:red;'>❌ Erro ao sincronizar tabela <strong>$tabela</strong>: " . $eTabela->getMessage() . "</div>";
            registrarLog("❌ Erro na tabela $tabela: " . $eTabela->getMessage());
        }
    }

    registrarLog("✅ Sincronização finalizada.");
    echo "<div style='background-color:#d4edda; color:#155724; padding:10px; text-align:center;'>
            ✅ <strong>Sincronização concluída</strong> às " . date('H:i:s') . "
          </div>";
} catch (Exception $e) {
    registrarLog("❌ Erro geral: " . $e->getMessage());
    echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; text-align:center;'>
            ❌ Erro geral: {$e->getMessage()}
          </div>";
}
?>
