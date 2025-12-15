<?php

if (!function_exists('ensure_visita_timer_column')) {
    function ensure_visita_timer_column(PDO $conn): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $stmt = $conn->query("
                SELECT COUNT(*) 
                  FROM information_schema.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'tb_visita'
                   AND COLUMN_NAME = 'timer_vis'
            ");
            $exists = (int)$stmt->fetchColumn() > 0;
            if ($exists) {
                return;
            }
            $conn->exec("
                ALTER TABLE tb_visita
                ADD COLUMN timer_vis INT NULL DEFAULT NULL AFTER programacao_enf
            ");
        } catch (Throwable $e) {
            error_log('[SCHEMA][timer_vis] ' . $e->getMessage());
        }
    }
}

if (!function_exists('ensure_internacao_timer_column')) {
    function ensure_internacao_timer_column(PDO $conn): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $stmt = $conn->query("
                SELECT COUNT(*)
                  FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'tb_internacao'
                   AND COLUMN_NAME = 'timer_int'
            ");
            $exists = (int)$stmt->fetchColumn() > 0;
            if ($exists) return;
            $conn->exec("
                ALTER TABLE tb_internacao
                ADD COLUMN timer_int INT NULL DEFAULT NULL AFTER num_atendimento_int
            ");
        } catch (Throwable $e) {
            error_log('[SCHEMA][timer_int] ' . $e->getMessage());
        }
    }
}
