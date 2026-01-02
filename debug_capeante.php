<?php
// Debug simples: verificar quantos dados existem
require_once("check_logado.php");
require_once("templates/header.php");

echo "<h2>Debug tb_capeante</h2>";

echo "<h3>1. Total de registros na tabela</h3>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_capeante");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total: " . $row['total'] . "<br>";
} catch (Throwable $e) {
    echo "Erro: " . $e->getMessage();
}

echo "<h3>2. Registros nos últimos 120 dias por data_create_cap</h3>";
try {
    $start = new DateTime('today');
    $start->modify('-119 days');
    $startStr = $start->format('Y-m-d 00:00:00');
    $endStr = date('Y-m-d 23:59:59');

    echo "Período: $startStr até $endStr<br>";

    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM tb_capeante 
        WHERE data_create_cap BETWEEN :dt_ini AND :dt_fim
    ");
    $stmt->execute([':dt_ini' => $startStr, ':dt_fim' => $endStr]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total: " . $row['total'] . "<br>";
} catch (Throwable $e) {
    echo "Erro: " . $e->getMessage();
}

echo "<h3>3. Amostra de usuários (usuario_create_cap)</h3>";
try {
    $stmt = $conn->query("
        SELECT DISTINCT usuario_create_cap 
        FROM tb_capeante 
        WHERE usuario_create_cap IS NOT NULL 
        AND usuario_create_cap != '' 
        LIMIT 10
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['usuario_create_cap'] . "<br>";
    }
} catch (Throwable $e) {
    echo "Erro: " . $e->getMessage();
}

echo "<h3>4. Colunas de tb_capeante</h3>";
try {
    $stmt = $conn->query("DESCRIBE tb_capeante");
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>" . $row['Field'] . "</li>";
    }
    echo "</ul>";
} catch (Throwable $e) {
    echo "Erro: " . $e->getMessage();
}
