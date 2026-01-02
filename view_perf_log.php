<?php
require_once("check_logado.php");
require_once("templates/header.php");

echo "<h1>Debug - Log de Performance</h1>";
echo "<p><small>Atualizar a página de performance e depois voltar aqui para ver os logs</small></p>";

// Tentar ler o arquivo de erro
$logFile = "/Applications/AMPPS/www/FullConex/php-error.log";

if (!file_exists($logFile)) {
    echo "<p style='color:red;'>Arquivo de log não encontrado em: $logFile</p>";
} else {
    echo "<p>Lendo arquivo: $logFile</p>";
    
    // Ler as últimas 100 linhas
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100);
    
    // Filtrar por PERF_RANKING
    $perfLines = array_filter($lastLines, function($line) {
        return strpos($line, 'PERF_RANKING') !== false;
    });
    
    if (empty($perfLines)) {
        echo "<p style='color:orange;'>Nenhum log PERF_RANKING encontrado nas últimas 100 linhas</p>";
        echo "<h3>Últimas linhas do log:</h3>";
        echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd; overflow-x:auto; max-height:400px;'>";
        echo htmlspecialchars(implode("", array_slice($lastLines, -20)));
        echo "</pre>";
    } else {
        echo "<h3>Logs de PERF_RANKING encontrados:</h3>";
        echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd; overflow-x:auto; max-height:600px;'>";
        foreach ($perfLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    }
}

echo "<hr>";
echo "<h2>Instruções:</h2>";
echo "<ol>";
echo "<li>Abra em outra aba: <a href='dashboard_performance.php' target='_blank'>dashboard_performance.php</a></li>";
echo "<li>Volte aqui e clique em F5 para atualizar</li>";
echo "<li>Os logs de execução aparecerão acima</li>";
echo "</ol>";

require_once("templates/footer.php");
?>
