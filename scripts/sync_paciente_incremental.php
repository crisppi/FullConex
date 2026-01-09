<?php
// Mensagem inicial
echo "<div style='background-color:#fff3cd; color:#856404; padding:10px; text-align:center;'>
        🔄 <strong>Sincronização incremental iniciada</strong> às " . date('d/m/Y H:i:s') . "
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
    exit("<div style='color:red;text-align:center;'>❌ Erro de conexão (destino): " . $e->getMessage() . "</div>");
}

// Definir data limite: hoje às 16h00
$hoje = date('Y-m-d');
$horaLimite = "$hoje 16:00:00";

echo "<div style='text-align:center;padding:6px;'>🕓 Buscando registros com updated_at >= <strong>$horaLimite</strong></div>";

try {
    $stmt = $pdoOrigem->prepare("SELECT * FROM tb_paciente WHERE updated_at >= :limite");
    $stmt->bindValue(':limite', $horaLimite);
    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalEncontrados = count($dados);
    echo "<div style='text-align:center;color:blue;'>🔍 <strong>$totalEncontrados</strong> registros encontrados.</div>";

    $sincronizados = 0;

    foreach ($dados as $linha) {
        $colunas = implode(", ", array_map(fn($c) => "`$c`", array_keys($linha)));
        $valoresMarcadores = ":" . implode(", :", array_keys($linha));

        $sql = "INSERT INTO tb_paciente ($colunas) VALUES ($valoresMarcadores)
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

    echo "<div style='text-align:center;color:green;font-weight:bold;'>
            🔁 <strong>$sincronizados</strong> registros sincronizados com sucesso.
          </div>";

    echo "<div style='background-color:#d4edda; color:#155724; padding:10px; text-align:center;'>
            ✅ <strong>Sincronização concluída</strong> às " . date('H:i:s') . "
          </div>";
} catch (Exception $e) {
    echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; text-align:center;'>
            ❌ Erro ao sincronizar dados: {$e->getMessage()}
          </div>";
}
