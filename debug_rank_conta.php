<?php
require_once("check_logado.php");

echo "<pre>";
echo "=== DEBUG CAPEANTE ===\n\n";

// 1. Verificar total de registros
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_capeante");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "1. Total de registros em tb_capeante: " . $row['total'] . "\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

// 2. Verificar data_create_cap
echo "\n2. Estatísticas de data_create_cap:\n";
try {
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            MIN(data_create_cap) as menor,
            MAX(data_create_cap) as maior,
            SUM(CASE WHEN data_create_cap IS NULL OR data_create_cap = '0000-00-00' OR data_create_cap = '0000-00-00 00:00:00' THEN 1 ELSE 0 END) as nulos
        FROM tb_capeante
    ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total: " . $row['total'] . "\n";
    echo "   Menor data: " . $row['menor'] . "\n";
    echo "   Maior data: " . $row['maior'] . "\n";
    echo "   Nulos/inválidos: " . $row['nulos'] . "\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

// 3. Verificar usuario_create_cap
echo "\n3. Estatísticas de usuario_create_cap:\n";
try {
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN usuario_create_cap IS NULL OR usuario_create_cap = '' THEN 1 ELSE 0 END) as vazios,
            COUNT(DISTINCT usuario_create_cap) as usuarios_unicos
        FROM tb_capeante
    ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total: " . $row['total'] . "\n";
    echo "   Vazios: " . $row['vazios'] . "\n";
    echo "   Usuários únicos: " . $row['usuarios_unicos'] . "\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

// 4. Período padrão
$defaultEnd = new DateTime('today');
$defaultStart = (clone $defaultEnd)->modify('-119 days');
$dtIni = $defaultStart->format('Y-m-d 00:00:00');
$dtFim = $defaultEnd->format('Y-m-d 23:59:59');

echo "\n4. Período padrão: $dtIni até $dtFim\n";

// 5. Registros neste período
try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM tb_capeante 
        WHERE data_create_cap BETWEEN :dt_ini AND :dt_fim
    ");
    $stmt->execute([':dt_ini' => $dtIni, ':dt_fim' => $dtFim]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Registros no período: " . $row['total'] . "\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

// 6. Amostra de usuarios_create_cap
echo "\n5. Amostra de usuario_create_cap (primeiros 10):\n";
try {
    $stmt = $conn->query("
        SELECT DISTINCT usuario_create_cap 
        FROM tb_capeante 
        WHERE usuario_create_cap IS NOT NULL AND usuario_create_cap != ''
        ORDER BY usuario_create_cap
        LIMIT 10
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - " . $row['usuario_create_cap'] . "\n";
    }
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

// 7. Teste da query de ranking
echo "\n6. Teste da query de ranking (sem data):\n";
try {
    $stmt = $conn->query("
        SELECT 
            COALESCE(
                NULLIF(TRIM(ca.usuario_create_cap), ''),
                'Sem usuário'
            ) AS admin_nome,
            COUNT(*) AS total_contas
        FROM tb_capeante ca
        GROUP BY admin_nome
        ORDER BY total_contas DESC
        LIMIT 10
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Encontrados: " . count($rows) . " usuários\n";
    foreach ($rows as $row) {
        echo "   - " . $row['admin_nome'] . ": " . $row['total_contas'] . " contas\n";
    }
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DEBUG ===\n";
echo "</pre>";
