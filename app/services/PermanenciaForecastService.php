<?php

class PermanenciaForecastService
{
    private PDO $conn;
    private array $comboStats = [];
    private array $hospitalStats = [];
    private ?array $globalStats = null;
    private string $modelVersion = 'permanencia-lite-v1';
    private int $historyMonths = 18;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function refreshActiveForecasts(?int $hospitalId = null, int $ttlHours = 6): array
    {
        $records = $this->fetchActiveInternacoes($hospitalId, $ttlHours);
        if (empty($records)) {
            return ['updated' => 0, 'skipped' => 0, 'model' => $this->modelVersion];
        }

        $this->loadHistoricalStats($hospitalId);

        $updated = 0;
        $skipped = 0;

        foreach ($records as $row) {
            $forecast = $this->estimateForecast($row);
            if ($forecast === null) {
                $skipped++;
                continue;
            }
            $this->persistForecast((int)$row['id_internacao'], $forecast);
            $updated++;
        }

        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'model'   => $this->modelVersion
        ];
    }

    public function fetchDashboardRows(?int $hospitalId, ?int $usuarioId, ?int $nivelPerfil, int $limit = 8): array
    {
        $conditions = [
            "ac.internado_int = 's'",
            "ac.data_intern_int IS NOT NULL",
            "ac.forecast_total_days IS NOT NULL"
        ];
        $params = [];

        if ($hospitalId) {
            $conditions[] = "ac.fk_hospital_int = :dhosp";
            $params[':dhosp'] = $hospitalId;
        }

        if ($usuarioId && $nivelPerfil !== null && $nivelPerfil <= 3) {
            $conditions[] = "ac.fk_hospital_int IN (
                SELECT hos.fk_hospital_user 
                  FROM tb_hospitalUser hos 
                 WHERE hos.fk_usuario_hosp = :duser
            )";
            $params[':duser'] = $usuarioId;
        }

        $sql = "
            SELECT
                ac.id_internacao,
                pa.nome_pac,
                hos.nome_hosp,
                ac.data_intern_int,
                ac.forecast_total_days,
                ac.forecast_lower_days,
                ac.forecast_upper_days,
                ac.forecast_confidence,
                ac.forecast_generated_at,
                GREATEST(DATEDIFF(CURRENT_DATE(), ac.data_intern_int), 0) AS dias_internado
            FROM tb_internacao ac
            JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
            JOIN tb_hospital hos ON hos.id_hospital = ac.fk_hospital_int
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY (ac.forecast_total_days - GREATEST(DATEDIFF(CURRENT_DATE(), ac.data_intern_int), 0)) ASC
            LIMIT " . (int) max(1, $limit);

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchActiveInternacoes(?int $hospitalId, int $ttlHours): array
    {
        $conditions = [
            "ac.internado_int = 's'",
            "ac.data_intern_int IS NOT NULL"
        ];
        $params = [];

        if ($hospitalId) {
            $conditions[] = "ac.fk_hospital_int = :hosp";
            $params[':hosp'] = $hospitalId;
        }

        $ttlHours = max(1, (int)$ttlHours);
        $conditions[] = "(ac.forecast_generated_at IS NULL OR ac.forecast_generated_at < DATE_SUB(NOW(), INTERVAL {$ttlHours} HOUR))";

        $sql = "
            SELECT
                ac.id_internacao,
                ac.fk_hospital_int,
                ac.grupo_patologia_int,
                ac.acomodacao_int,
                ac.modo_internacao_int,
                ac.tipo_admissao_int,
                ac.data_intern_int,
                pa.sexo_pac,
                pa.idade_pac AS idade_informada,
                TIMESTAMPDIFF(YEAR, pa.data_nasc_pac, CURRENT_DATE) AS idade_calculada
            FROM tb_internacao ac
            JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
            WHERE " . implode(' AND ', $conditions);

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function loadHistoricalStats(?int $hospitalId): void
    {
        $this->comboStats = [];
        $this->hospitalStats = [];
        $this->globalStats = null;

        try {
            $historyLimit = (new DateTimeImmutable('now'))->sub(new DateInterval('P' . $this->historyMonths . 'M'))->format('Y-m-d');

            $filters = [
                "al.data_alta_alt IS NOT NULL",
                "al.data_alta_alt >= ac.data_intern_int",
                "al.data_alta_alt >= :limitDate"
            ];
            if ($hospitalId) {
                $filters[] = "ac.fk_hospital_int = :histHospital";
            }
            $whereSql = implode(' AND ', $filters);

            $sqlCombo = "
                SELECT
                    ac.fk_hospital_int AS hospital_id,
                    COALESCE(NULLIF(ac.grupo_patologia_int, ''), '__any__') AS grupo,
                    COALESCE(NULLIF(ac.acomodacao_int, ''), '__any__') AS acomodacao,
                    COUNT(*) AS samples,
                    AVG(GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1)) AS media_dias,
                    STDDEV_POP(GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1)) AS desvio_dias
                FROM tb_alta al
                INNER JOIN tb_internacao ac ON al.fk_id_int_alt = ac.id_internacao
                WHERE $whereSql
                GROUP BY ac.fk_hospital_int, grupo, acomodacao
            ";

            $stmt = $this->conn->prepare($sqlCombo);
            $stmt->bindValue(':limitDate', $historyLimit);
            if ($hospitalId) {
                $stmt->bindValue(':histHospital', $hospitalId, PDO::PARAM_INT);
            }
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                if ((int)$row['samples'] < 3) {
                    continue;
                }
                $key = $this->makeKey((int)$row['hospital_id'], $row['grupo'], $row['acomodacao']);
                $this->comboStats[$key] = [
                    'avg'     => (float)$row['media_dias'],
                    'std'     => isset($row['desvio_dias']) ? (float)$row['desvio_dias'] : 0.0,
                    'samples' => (int)$row['samples'],
                ];
            }

            $sqlHospital = "
                SELECT
                    ac.fk_hospital_int AS hospital_id,
                    COUNT(*) AS samples,
                    AVG(GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1)) AS media_dias,
                    STDDEV_POP(GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1)) AS desvio_dias
                FROM tb_alta al
                INNER JOIN tb_internacao ac ON al.fk_id_int_alt = ac.id_internacao
                WHERE $whereSql
                GROUP BY ac.fk_hospital_int
            ";

            $stmtHosp = $this->conn->prepare($sqlHospital);
            $stmtHosp->bindValue(':limitDate', $historyLimit);
            if ($hospitalId) {
                $stmtHosp->bindValue(':histHospital', $hospitalId, PDO::PARAM_INT);
            }
            $stmtHosp->execute();
            foreach ($stmtHosp->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                if ((int)$row['samples'] < 5) {
                    continue;
                }
                $key = $this->makeKey((int)$row['hospital_id'], null, null);
                $this->hospitalStats[$key] = [
                    'avg'     => (float)$row['media_dias'],
                    'std'     => isset($row['desvio_dias']) ? (float)$row['desvio_dias'] : 0.0,
                    'samples' => (int)$row['samples'],
                ];
            }

            $sqlGlobal = "
                SELECT
                    COUNT(*) AS samples,
                    AVG(GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1)) AS media_dias,
                    STDDEV_POP(GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1)) AS desvio_dias
                FROM tb_alta al
                INNER JOIN tb_internacao ac ON al.fk_id_int_alt = ac.id_internacao
                WHERE al.data_alta_alt IS NOT NULL
                  AND al.data_alta_alt >= ac.data_intern_int
                  AND al.data_alta_alt >= :limitDateGlobal
            ";

            $stmtGlobal = $this->conn->prepare($sqlGlobal);
            $stmtGlobal->bindValue(':limitDateGlobal', $historyLimit);
            $stmtGlobal->execute();
            $global = $stmtGlobal->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($global) {
                $this->globalStats = [
                    'avg'     => $global['media_dias'] ? (float)$global['media_dias'] : null,
                    'std'     => isset($global['desvio_dias']) ? (float)$global['desvio_dias'] : 0.0,
                    'samples' => (int)$global['samples'],
                ];
            }
        } catch (Throwable $e) {
            error_log('[ForecastService][history] ' . $e->getMessage());
            $this->comboStats = [];
            $this->hospitalStats = [];
            $this->globalStats = null;
        }
    }

    private function estimateForecast(array $row): ?array
    {
        $diasInternado = $this->diasDesdeInternacao($row['data_intern_int']);
        if ($diasInternado === null) {
            return null;
        }

        $stats = $this->resolveStats(
            (int)$row['fk_hospital_int'],
            $row['grupo_patologia_int'] ?? null,
            $row['acomodacao_int'] ?? null
        );

        if (!$stats || ($stats['avg'] ?? 0) <= 0) {
            $stats = [
                'avg'     => max($diasInternado + 3, 5),
                'std'     => max(1.5, ($diasInternado + 3) * 0.25),
                'samples' => 0
            ];
        }

        $expected = (float)$stats['avg'];
        $expected += $this->ageAdjustment($row);
        $expected += $this->acomodacaoAdjustment($row['acomodacao_int'] ?? null);
        $expected += $this->modoAdjustment($row['modo_internacao_int'] ?? null, $row['tipo_admissao_int'] ?? null);
        $expected = max($expected, $diasInternado + 1);

        $std = $stats['std'] ?? 0.0;
        if ($std <= 0) {
            $std = max(1.5, $expected * 0.20);
        }
        $spread = max($std * 1.15, $expected * 0.15);

        $lower = max($diasInternado, $expected - $spread);
        $upper = $expected + $spread;

        return [
            'total'      => round($expected, 1),
            'lower'      => round($lower, 1),
            'upper'      => round($upper, 1),
            'confidence' => $this->confidenceFromSamples($stats['samples'])
        ];
    }

    private function persistForecast(int $internacaoId, array $forecast): void
    {
        $sql = "
            UPDATE tb_internacao
               SET forecast_total_days = :total,
                   forecast_lower_days = :lower,
                   forecast_upper_days = :upper,
                   forecast_generated_at = NOW(),
                   forecast_model = :model,
                   forecast_confidence = :conf
             WHERE id_internacao = :id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':total', $forecast['total']);
        $stmt->bindValue(':lower', $forecast['lower']);
        $stmt->bindValue(':upper', $forecast['upper']);
        $stmt->bindValue(':model', $this->modelVersion);
        $stmt->bindValue(':conf', $forecast['confidence'], PDO::PARAM_INT);
        $stmt->bindValue(':id', $internacaoId, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function resolveStats(int $hospitalId, ?string $grupo, ?string $acomodacao): ?array
    {
        $comboKey = $this->makeKey($hospitalId, $grupo, $acomodacao);
        if (isset($this->comboStats[$comboKey])) {
            return $this->comboStats[$comboKey];
        }

        $hospitalKey = $this->makeKey($hospitalId, null, null);
        if (isset($this->hospitalStats[$hospitalKey])) {
            return $this->hospitalStats[$hospitalKey];
        }

        return $this->globalStats;
    }

    private function makeKey(?int $hospitalId, ?string $grupo, ?string $acomodacao): string
    {
        $hid = (int)($hospitalId ?? 0);
        return implode('|', [
            $hid,
            $this->normalizeKey($grupo),
            $this->normalizeKey($acomodacao)
        ]);
    }

    private function normalizeKey(?string $val): string
    {
        if ($val === null) {
            return '__any__';
        }
        $trim = strtolower(trim($val));
        return $trim !== '' ? $trim : '__any__';
    }

    private function diasDesdeInternacao(?string $dataInternacao): ?int
    {
        if (empty($dataInternacao)) {
            return null;
        }
        try {
            $inicio = new DateTimeImmutable($dataInternacao);
            $hoje = new DateTimeImmutable('today');
            return max(0, $inicio->diff($hoje)->days);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function ageAdjustment(array $row): float
    {
        $idade = null;
        if (isset($row['idade_informada']) && $row['idade_informada'] !== null && $row['idade_informada'] !== '') {
            $idade = (int)$row['idade_informada'];
        } elseif (isset($row['idade_calculada']) && $row['idade_calculada'] !== null) {
            $idade = (int)$row['idade_calculada'];
        }

        if ($idade === null) {
            return 0.0;
        }

        if ($idade >= 80) {
            return 4.5;
        }
        if ($idade >= 70) {
            return 2.5;
        }
        if ($idade <= 12) {
            return 1.0;
        }
        return 0.0;
    }

    private function acomodacaoAdjustment(?string $acomodacao): float
    {
        if (!$acomodacao) {
            return 0.0;
        }
        $acomodacao = strtolower($acomodacao);
        if (strpos($acomodacao, 'uti') !== false) {
            return 4.0;
        }
        if (strpos($acomodacao, 'apart') !== false) {
            return 0.8;
        }
        return 0.0;
    }

    private function modoAdjustment(?string $modo, ?string $tipoAdmissao): float
    {
        $ajuste = 0.0;
        $modo = strtolower((string)$modo);
        $tipoAdmissao = strtolower((string)$tipoAdmissao);

        if (strpos($modo, 'urg') !== false || strpos($modo, 'emerg') !== false) {
            $ajuste += 1.5;
        }
        if (strpos($tipoAdmissao, 'cirurg') !== false) {
            $ajuste += 1.0;
        }
        if (strpos($tipoAdmissao, 'obst') !== false) {
            $ajuste -= 0.5;
        }

        return $ajuste;
    }

    private function confidenceFromSamples(int $samples): int
    {
        if ($samples <= 0) {
            return 45;
        }
        if ($samples >= 50) {
            return 95;
        }
        return 50 + (int)round((min($samples, 50) / 50) * 45);
    }
}
