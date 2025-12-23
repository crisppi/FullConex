<?php
// ==========================================================
// globals.php — Bootstrap comum do app (Hostinger/AMPPS)
// Compatível com PHP < 8 (inclui polyfills)
// ==========================================================

// ------------------ 0) Polyfills PHP 8 --------------------
if (!function_exists('str_contains')) {
    function str_contains(string $h, string $n): bool
    {
        return $n === '' || strpos($h, $n) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $h, string $n): bool
    {
        return $n === '' || strncmp($h, $n, strlen($n)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $h, string $n): bool
    {
        if ($n === '') return true;
        $len = strlen($n);
        return substr($h, -$len) === $n;
    }
}

// ------------------ 1) Descobrir BASE PATH ----------------
// Ajuste manual padrão (produção na raiz):
$APP_BASE_PATH = '/';

// Detecta automaticamente a subpasta real do app (ex.: /FullCare) usando o DOCUMENT_ROOT
$__docroot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$__appDir  = str_replace('\\', '/', realpath(__DIR__) ?: __DIR__);
if ($__docroot !== '' && strpos($__appDir, $__docroot) === 0) {
    $relative = trim(substr($__appDir, strlen($__docroot)), '/');
    if ($relative !== '') {
        $APP_BASE_PATH = '/' . $relative . '/';
    }
}

// Fallback para ambientes locais antigos (mantém suporte a /FullConex, etc.)
$__host   = $_SERVER['HTTP_HOST']   ?? '';
$__script = $_SERVER['SCRIPT_NAME'] ?? '';
if ($APP_BASE_PATH === '/' && $__host && stripos($__host, 'localhost') !== false) {
    if (preg_match('#^/(FullCare|FullConex(?:Aud)?)(/|$)#i', $__script, $match)) {
        $APP_BASE_PATH = '/' . trim($match[1], '/') . '/';
    }
}
// Normaliza
$APP_BASE_PATH = '/' . trim($APP_BASE_PATH, '/') . '/';
if ($APP_BASE_PATH === '//') $APP_BASE_PATH = '/';

// ------------------ 2) Descobrir scheme/host ---------------
$httpsForwarded = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
    || (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], '"https"') !== false); // Cloudflare

$isHttps = $httpsForwarded
    || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);

$SCHEME = $isHttps ? 'https' : 'http';
$HOST   = $_SERVER['HTTP_HOST'] ?? 'localhost';

// ------------------ 3) BASE_URL estável --------------------
$BASE_URL = $SCHEME . '://' . $HOST . rtrim($APP_BASE_PATH, '/') . '/'; // sempre termina com '/'

// ------------------ 4) Sessão (com path correto) ----------
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Definições coerentes com o path da app
    $cookiePath = rtrim($APP_BASE_PATH, '/') ?: '/';

    if (version_compare(PHP_VERSION, '7.3', '>=')) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => $cookiePath,
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        // Fallback para PHP < 7.3
        ini_set('session.cookie_secure', $isHttps ? '1' : '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_path', $cookiePath);
        // Alguns ambientes aceitam:
        @ini_set('session.cookie_samesite', 'Lax');
        session_set_cookie_params(0, $cookiePath, '', $isHttps, true);
    }
    session_start();
}

// ------------------ 5) DB primeiro -------------------------
require_once __DIR__ . '/db.php';   // aqui dentro você cria $conn (PDO)

// ------------------ 6) Guard (autorização) -----------------
require_once __DIR__ . '/authz.php';

// Métodos que alteram estado
$__method     = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$__scriptBase = strtolower(basename($_SERVER['SCRIPT_NAME'] ?? ''));

// Endpoints liberados do Guard (não exigem sessão prévia)
$__guardSkip = [
    'check_login.php',   // login
    'logout.php',        // logout
    'index.php',         // tela de login
    'index_novo.php',    // sua tela de login nova
    'nova_senha.php',    // troca de senha inicial
    // acrescente aqui quaisquer webhooks ou callbacks públicos, se existirem
];

// Só aplica o Gate em métodos mutantes e quando o script NÃO está na whitelist
if (in_array($__method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && !in_array($__scriptBase, $__guardSkip, true)) {
    Gate::autoEnforce($conn, $BASE_URL);
}

require_once __DIR__ . '/app/schemaEnsurer.php';
ensure_visita_timer_column($conn);
ensure_internacao_timer_column($conn);
ensure_internacao_forecast_columns($conn);

// ------------------ 7) Helpers globais (opcional) ----------

if (!function_exists('app_url')) {
    /**
     * Gera URL absoluta baseada no BASE_URL
     * @example app_url('menu_app.php')
     */
    function app_url(string $path = ''): string
    {
        global $BASE_URL;
        $p = ltrim($path, '/');
        return rtrim($BASE_URL, '/') . ($p ? "/{$p}" : '/');
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('flash')) {
    /**
     * Define / lê flash message simples
     * flash('msg', 'texto');  // define
     * $m = flash('msg');      // lê e apaga
     */
    function flash(string $key, $val = null)
    {
        if ($val === null) {
            if (!isset($_SESSION[$key])) return null;
            $tmp = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $tmp;
        }
        $_SESSION[$key] = $val;
        return true;
    }
}
