<?php
require_once("check_logado.php");

if (!isset($conn) || !($conn instanceof PDO)) {
    die("Conexão não disponível.");
}

$defaultEnd = new DateTime('today');
$defaultStart = (clone $defaultEnd)->modify('-119 days');

$rangeParams = [
    ':dt_ini' => $defaultStart->format('Y-m-d 00:00:00'),
    ':dt_fim' => $defaultEnd->format('Y-m-d 23:59:59'),
];

echo "<h2>Teste Query Contas</h2>";

// Teste 1: Verificar se há dados na tabela
echo "<h3>Teste 1: Contagem de registros em tb_capeante</h3>";
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tb_capeante WHERE data_create_cap BETWEEN :dt_ini AND :dt_fim");
    $stmt->execute($rangeParams);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de registros no período: " . $result['total'] . "<br>";
} catch (Throwable $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
}

// Teste 2: Ver colunas da tabela
echo "<h3>Teste 2: Colunas de tb_capeante</h3>";
try {
    $stmt = $conn->query("DESCRIBE tb_capeante");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "</pre>";
} catch (Throwable $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
}

// Teste 3: Ver alguns registros simples
echo "<h3>Teste 3: Amostra de dados</h3>";
try {
    $stmt = $conn->prepare("
        SELECT 
            id_capeante,
            usuario_create_cap,
            fk_id_aud_adm,
            data_create_cap,
            data_digit_capeante,
            timer_cap
        FROM tb_capeante 
        WHERE data_create_cap BETWEEN :dt_ini AND :dt_fim
        LIMIT 5
    ");
    $stmt->execute($rangeParams);
    echo "<table border='1'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td>" . $val . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} catch (Throwable $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
}

echo "<h3>Período testado</h3>";
echo "De: " . $rangeParams[':dt_ini'] . "<br>";
echo "Até: " . $rangeParams[':dt_fim'] . "<br>";
