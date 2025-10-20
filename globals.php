<?php
// --- globals.php (trecho final recomendado) ---

// 1) Sessão (cookie escopo do app)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/FullConex', // ajuste se seu app estiver em outro path
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 2) BASE_URL estável (evita variáveis de ambiente confusas)
$BASE_URL = 'http://localhost/FullConex/'; // <= ajuste para seu host se necessário

// 3) DB primeiro: precisa ter $conn pronto ANTES do guard
require_once __DIR__ . '/db.php';  // aqui dentro você cria $conn (PDO)

// 4) Guard: só em métodos que mudam estado (não trava GET)
require_once __DIR__ . '/authz.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
    Gate::autoEnforce($conn, $BASE_URL);
}