<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "Conexao invalida.\n");
    exit(1);
}

$dbName = $conn->query("SELECT DATABASE()")->fetchColumn();
if (!$dbName) {
    fwrite(STDERR, "Banco nao identificado.\n");
    exit(1);
}

$excludedTables = [
    'tb_log_historico',
    'tb_visita_log',
    'tb_capeante_log',
];

$tables = $conn->prepare("
    SELECT TABLE_NAME
      FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = :db
     ORDER BY TABLE_NAME
");
$tables->execute([':db' => $dbName]);
$tableNames = $tables->fetchAll(PDO::FETCH_COLUMN) ?: [];

if (!$tableNames) {
    fwrite(STDERR, "Nenhuma tabela encontrada.\n");
    exit(1);
}

function fetchColumns(PDO $conn, string $db, string $table): array
{
    $stmt = $conn->prepare("
        SELECT COLUMN_NAME
          FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :db
           AND TABLE_NAME = :table
         ORDER BY ORDINAL_POSITION
    ");
    $stmt->execute([':db' => $db, ':table' => $table]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

function fetchPkColumns(PDO $conn, string $db, string $table): array
{
    $stmt = $conn->prepare("
        SELECT COLUMN_NAME
          FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = :db
           AND TABLE_NAME = :table
           AND CONSTRAINT_NAME = 'PRIMARY'
         ORDER BY ORDINAL_POSITION
    ");
    $stmt->execute([':db' => $db, ':table' => $table]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

function buildJsonObject(array $columns, string $rowAlias): string
{
    $parts = [];
    foreach ($columns as $col) {
        $safeCol = str_replace("`", "``", $col);
        $parts[] = "'" . $col . "', " . $rowAlias . ".`" . $safeCol . "`";
    }
    if (!$parts) {
        return "NULL";
    }
    return "JSON_OBJECT(" . implode(", ", $parts) . ")";
}

$sql = [];
$sql[] = "START TRANSACTION;";
$sql[] = "";
$sql[] = "-- Ajustes na tabela de log (tb_log_historico)";
$sql[] = "ALTER TABLE tb_log_historico";
$sql[] = "  ADD COLUMN IF NOT EXISTS usuario_id INT NULL AFTER email_user,";
$sql[] = "  ADD COLUMN IF NOT EXISTS usuario_nome VARCHAR(255) NULL AFTER usuario_id,";
$sql[] = "  ADD COLUMN IF NOT EXISTS ip VARCHAR(45) NULL AFTER usuario_nome,";
$sql[] = "  ADD COLUMN IF NOT EXISTS user_agent VARCHAR(512) NULL AFTER ip,";
$sql[] = "  ADD COLUMN IF NOT EXISTS created_at DATETIME NULL AFTER user_agent;";
$sql[] = "";

foreach ($tableNames as $table) {
    if (in_array($table, $excludedTables, true)) {
        continue;
    }

    $columns = fetchColumns($conn, $dbName, $table);
    if (!$columns) {
        continue;
    }

    $pkCols = fetchPkColumns($conn, $dbName, $table);
    $linhaIdExprNew = "NULL";
    $linhaIdExprOld = "NULL";
    if (count($pkCols) === 1) {
        $pk = str_replace("`", "``", $pkCols[0]);
        $linhaIdExprNew = "NEW.`{$pk}`";
        $linhaIdExprOld = "OLD.`{$pk}`";
    }

    $jsonNew = buildJsonObject($columns, "NEW");
    $jsonOld = buildJsonObject($columns, "OLD");

    $sql[] = "-- Triggers para {$table}";
    $sql[] = "DROP TRIGGER IF EXISTS trg_log_insert_{$table};";
    $sql[] = "DROP TRIGGER IF EXISTS trg_log_update_{$table};";
    $sql[] = "DROP TRIGGER IF EXISTS trg_log_delete_{$table};";
    $sql[] = "DELIMITER $$";
    $sql[] = "CREATE TRIGGER trg_log_insert_{$table}";
    $sql[] = "AFTER INSERT ON {$table}";
    $sql[] = "FOR EACH ROW";
    $sql[] = "BEGIN";
    $sql[] = "  INSERT INTO tb_log_historico";
    $sql[] = "    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)";
    $sql[] = "  VALUES";
    $sql[] = "    ('{$table}', 'INSERT', NOW(), {$linhaIdExprNew}, NULL, {$jsonNew}, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());";
    $sql[] = "END$$";
    $sql[] = "DELIMITER ;";
    $sql[] = "";
    $sql[] = "DELIMITER $$";
    $sql[] = "CREATE TRIGGER trg_log_update_{$table}";
    $sql[] = "AFTER UPDATE ON {$table}";
    $sql[] = "FOR EACH ROW";
    $sql[] = "BEGIN";
    $sql[] = "  INSERT INTO tb_log_historico";
    $sql[] = "    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)";
    $sql[] = "  VALUES";
    $sql[] = "    ('{$table}', 'UPDATE', NOW(), {$linhaIdExprNew}, {$jsonOld}, {$jsonNew}, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());";
    $sql[] = "END$$";
    $sql[] = "DELIMITER ;";
    $sql[] = "";
    $sql[] = "DELIMITER $$";
    $sql[] = "CREATE TRIGGER trg_log_delete_{$table}";
    $sql[] = "AFTER DELETE ON {$table}";
    $sql[] = "FOR EACH ROW";
    $sql[] = "BEGIN";
    $sql[] = "  INSERT INTO tb_log_historico";
    $sql[] = "    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)";
    $sql[] = "  VALUES";
    $sql[] = "    ('{$table}', 'DELETE', NOW(), {$linhaIdExprOld}, {$jsonOld}, NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());";
    $sql[] = "END$$";
    $sql[] = "DELIMITER ;";
    $sql[] = "";
}

$sql[] = "COMMIT;";

$outputPath = __DIR__ . "/log_triggers.sql";
file_put_contents($outputPath, implode("\n", $sql) . "\n");

echo "Gerado: {$outputPath}\n";
