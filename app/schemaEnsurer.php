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

if (!function_exists('ensure_internacao_forecast_columns')) {
    function ensure_internacao_forecast_columns(PDO $conn): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        $columns = [
            'forecast_total_days' => "ALTER TABLE tb_internacao ADD COLUMN forecast_total_days DECIMAL(6,2) NULL DEFAULT NULL AFTER timer_int",
            'forecast_lower_days' => "ALTER TABLE tb_internacao ADD COLUMN forecast_lower_days DECIMAL(6,2) NULL DEFAULT NULL AFTER forecast_total_days",
            'forecast_upper_days' => "ALTER TABLE tb_internacao ADD COLUMN forecast_upper_days DECIMAL(6,2) NULL DEFAULT NULL AFTER forecast_lower_days",
            'forecast_generated_at' => "ALTER TABLE tb_internacao ADD COLUMN forecast_generated_at DATETIME NULL DEFAULT NULL AFTER forecast_upper_days",
            'forecast_model' => "ALTER TABLE tb_internacao ADD COLUMN forecast_model VARCHAR(60) NULL DEFAULT NULL AFTER forecast_generated_at",
            'forecast_confidence' => "ALTER TABLE tb_internacao ADD COLUMN forecast_confidence TINYINT NULL DEFAULT NULL AFTER forecast_model",
        ];

        foreach ($columns as $column => $ddl) {
            try {
                $stmt = $conn->prepare("
                    SELECT COUNT(*)
                      FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME = 'tb_internacao'
                       AND COLUMN_NAME = :column
                ");
                $stmt->bindValue(':column', $column, PDO::PARAM_STR);
                $stmt->execute();
                $exists = (int)$stmt->fetchColumn() > 0;
                if ($exists) {
                    continue;
                }
                $conn->exec($ddl);
            } catch (Throwable $e) {
                error_log('[SCHEMA][forecast:' . $column . '] ' . $e->getMessage());
            }
        }
    }
}

if (!function_exists('ensure_schema_version_table')) {
    function ensure_schema_version_table(PDO $conn): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $stmt = $conn->prepare("
                SELECT COUNT(*)
                  FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'schema_version'
            ");
            $stmt->execute();
            $exists = (int)$stmt->fetchColumn() > 0;
            if ($exists) {
                return;
            }
            $conn->exec("
                CREATE TABLE schema_version (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    version VARCHAR(64) NOT NULL,
                    description TEXT NULL,
                    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    applied_by VARCHAR(150) NULL,
                    file_name VARCHAR(150) NULL,
                    checksum CHAR(64) NULL,
                    UNIQUE KEY uq_schema_version (version)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Throwable $e) {
            error_log('[SCHEMA][schema_version] ' . $e->getMessage());
        }
    }
}
