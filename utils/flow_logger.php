<?php

if (!function_exists('flowLogStart')) {
    function flowLogStart(string $flow, array $context = [], ?string $logFile = null): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $sessionUserId = $_SESSION['id_usuario'] ?? null;
        $sessionUserName = $_SESSION['usuario_user'] ?? ($_SESSION['login_user'] ?? ($_SESSION['email_user'] ?? null));

        if (isset($context['trace_id']) && $context['trace_id']) {
            $traceId = (string)$context['trace_id'];
        } else {
            try {
                $traceId = bin2hex(random_bytes(8));
            } catch (Throwable $e) {
                $traceId = uniqid('trace_', true);
            }
        }
        $file = $logFile ?: (__DIR__ . '/../logs/flow_operacional.log');

        $base = [
            'flow' => $flow,
            'trace_id' => $traceId,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'session_user_id' => $sessionUserId,
            'session_user_name' => $sessionUserName,
            'ts' => date('c')
        ];

        $ctx = array_merge($base, $context);
        $ctx['_log_file'] = $file;

        flowLog($ctx, 'request.start', 'INFO');

        return $ctx;
    }
}

if (!function_exists('flowLog')) {
    function flowLog(array $ctx, string $stage, string $level = 'INFO', array $data = []): void
    {
        $file = $ctx['_log_file'] ?? (__DIR__ . '/../logs/flow_operacional.log');
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $line = [
            'ts' => date('c'),
            'level' => strtoupper($level),
            'flow' => $ctx['flow'] ?? null,
            'trace_id' => $ctx['trace_id'] ?? null,
            'stage' => $stage,
            'ctx' => array_diff_key($ctx, array_flip(['_log_file'])),
            'data' => $data
        ];

        @file_put_contents(
            $file,
            json_encode($line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
    }
}
