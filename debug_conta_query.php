<?php
// DEBUG: Mostrar a query de contas com valores reais

require_once("check_logado.php");
require_once("templates/header.php");

$defaultEnd = new DateTime('today');
$defaultStart = (clone $defaultEnd)->modify('-119 days');

function buildCapeanteDateExpr(string $alias = '') {
    $prefix = $alias !== '' ? rtrim($alias, '.') . '.' : '';
    return "COALESCE(
        NULLIF({$prefix}data_digit_capeante, '0000-00-00'),
        NULLIF({$prefix}data_fech_capeante, '0000-00-00'),
        NULLIF({$prefix}data_final_capeante, '0000-00-00'),
        NULLIF({$prefix}data_inicial_capeante, '0000-00-00'),
        NULLIF({$prefix}data_create_cap, '0000-00-00'),
        {$prefix}data_create_cap
    )";
}

$capeanteRangeExpr = buildCapeanteDateExpr('ca');
$capeanteStartExpr = "
    COALESCE(
        NULLIF(ca.data_inicial_capeante, '0000-00-00'),
        NULLIF(ca.data_final_capeante, '0000-00-00'),
        NULLIF(ca.data_fech_capeante, '0000-00-00'),
        ca.data_inicial_capeante
    )";
$capeanteDigitExpr = "
    COALESCE(
        NULLIF(ca.data_digit_capeante, '0000-00-00 00:00:00'),
        NULLIF(ca.data_digit_capeante, '0000-00-00'),
        NULLIF(ca.data_create_cap, '0000-00-00 00:00:00'),
        NULLIF(ca.data_create_cap, '0000-00-00'),
        ca.data_digit_capeante,
        ca.data_create_cap
    )";
$capeanteSlaDaysExpr = "GREATEST(0, TIMESTAMPDIFF(DAY, {$capeanteStartExpr}, {$capeanteDigitExpr}))";

$rangeParams = [
    ':dt_ini' => $defaultStart->format('Y-m-d 00:00:00'),
    ':dt_fim' => $defaultEnd->format('Y-m-d 23:59:59'),
];

$sql = "SELECT 
        COALESCE(ca.usuario_create_cap, u.login_user, u.email_user, CONCAT('ID ', COALESCE(ca.fk_id_aud_adm, 0)), 'Sem usuário') AS admin_nome,
        COUNT(*) AS total_contas,
        ROUND(SUM(COALESCE(ca.valor_final_capeante, ca.valor_apresentado_capeante, 0)),2) AS valor_total,
        ROUND(AVG(CASE 
            WHEN {$capeanteStartExpr} IS NOT NULL AND {$capeanteDigitExpr} IS NOT NULL
                THEN {$capeanteSlaDaysExpr}
            ELSE 0
        END),1) AS sla_dias,
        ROUND(AVG(NULLIF(ca.timer_cap,0)),1) AS timer_seg
     FROM tb_capeante ca
     LEFT JOIN tb_user u ON u.id_usuario = ca.fk_id_aud_adm
    WHERE {$capeanteRangeExpr} IS NOT NULL 
      AND {$capeanteRangeExpr} BETWEEN :dt_ini AND :dt_fim
    GROUP BY COALESCE(ca.usuario_create_cap, u.login_user, u.email_user, ca.fk_id_aud_adm)
    ORDER BY total_contas DESC
    LIMIT 10";

echo "<h2>Debug Query Contas</h2>";
echo "<h3>Período</h3>";
echo "De: " . $defaultStart->format('Y-m-d H:i:s') . "<br>";
echo "Até: " . $defaultEnd->format('Y-m-d H:i:s') . "<br>";

echo "<h3>Query SQL</h3>";
echo "<pre>" . htmlspecialchars($sql) . "</pre>";

echo "<h3>Resultado</h3>";
try {
    $stmt = $conn->prepare($sql);
    
    foreach ($rangeParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total de linhas: " . count($results) . "<br>";
    
    if (count($results) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr>";
        foreach (array_keys($results[0]) as $col) {
            echo "<th>" . $col . "</th>";
        }
        echo "</tr>";
        
        foreach ($results as $row) {
            echo "<tr>";
            foreach ($row as $val) {
                echo "<td>" . htmlspecialchars((string)$val) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Nenhum resultado encontrado.";
    }
} catch (Throwable $e) {
    echo "<strong style='color:red;'>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
