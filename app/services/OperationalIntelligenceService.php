<?php

class OperationalIntelligenceService
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function demandForecast(int $days = 7): array
    {
        $days = max(1, min($days, 14));

        $stmt = $this->conn->prepare("
            SELECT DATE(ac.data_intern_int) AS dia, COUNT(*) AS total
              FROM tb_internacao ac
             WHERE ac.data_intern_int IS NOT NULL
               AND ac.data_intern_int >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
             GROUP BY dia
             ORDER BY dia ASC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $history = [];
        foreach ($rows as $row) {
            $history[] = [
                'date'  => $row['dia'],
                'value' => (int) $row['total'],
            ];
        }

        $last7 = array_slice(array_column($history, 'value'), -7);
        $prev7 = array_slice(array_column($history, 'value'), -14, 7);
        $avgRecent = $this->safeAverage($last7);
        $avgPrev = $this->safeAverage($prev7);
        $trend = $avgRecent - $avgPrev;

        $forecast = [];
        for ($i = 1; $i <= $days; $i++) {
            $date = (new DateTimeImmutable("today +{$i} day"))->format('Y-m-d');
            $value = max(0, round($avgRecent + ($trend * ($i / max(1, $days)))));
            $forecast[] = [
                'date'  => $date,
                'value' => (int) $value,
            ];
        }

        return [
            'history'       => $history,
            'forecast'      => $forecast,
            'avg_recent'    => $avgRecent,
            'avg_previous'  => $avgPrev,
            'trend'         => $trend,
            'message'       => $this->buildForecastMessage($avgRecent, $trend)
        ];
    }

    public function anomalyDetection(): array
    {
        $stmt = $this->conn->prepare("
            SELECT DATE(ac.data_intern_int) AS dia, COUNT(*) AS total
              FROM tb_internacao ac
             WHERE ac.data_intern_int IS NOT NULL
               AND ac.data_intern_int >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY dia
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $values = [];
        $today = date('Y-m-d');
        $todayValue = 0;

        foreach ($rows as $row) {
            $values[] = (int) $row['total'];
            if ($row['dia'] === $today) {
                $todayValue = (int) $row['total'];
            }
        }

        $avg = $this->safeAverage($values);
        $std = $this->safeStdDev($values, $avg);
        $limit = $avg + (2 * $std);

        return [
            'today'      => $todayValue,
            'average'    => $avg,
            'std_dev'    => $std,
            'threshold'  => $limit,
            'is_anomaly' => $todayValue > $limit,
            'message'    => $todayValue > $limit
                ? 'Volume de hoje acima do esperado: investigar causas (fraude, uso incomum ou pico sazonal).'
                : 'Volume dentro da faixa prevista.'
        ];
    }

    public function conversionScores(int $limit = 5): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                hos.id_hospital,
                hos.nome_hosp,
                SUM(CASE 
                        WHEN ng.data_fim_neg IS NOT NULL 
                         AND ng.data_fim_neg <> '0000-00-00' 
                    THEN 1 ELSE 0 END) AS concluidas,
                COUNT(*) AS total,
                SUM(CASE 
                        WHEN ng.data_fim_neg IS NOT NULL 
                         AND ng.data_fim_neg <> '0000-00-00' 
                    THEN 1 ELSE 0 END) / COUNT(*) AS prob_conversion
            FROM tb_negociacao ng
            INNER JOIN tb_internacao ac ON ng.fk_id_int = ac.id_internacao
            INNER JOIN tb_hospital hos ON ac.fk_hospital_int = hos.id_hospital
            WHERE ng.data_inicio_neg >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY hos.id_hospital, hos.nome_hosp
            HAVING total >= 3
            ORDER BY prob_conversion ASC
            LIMIT :limite
        ");
        $stmt->bindValue(':limite', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $prob = isset($row['prob_conversion']) ? (float)$row['prob_conversion'] : (
                ((int)$row['total']) > 0 ? ((int)$row['concluidas']) / (int)$row['total'] : 0
            );
            $results[] = [
                'hospital'    => $row['nome_hosp'],
                'total'       => (int)$row['total'],
                'concluidas'  => (int)$row['concluidas'],
                'probability' => round($prob, 2),
                'risk'        => $prob < 0.45 ? 'alto' : ($prob < 0.65 ? 'atenção' : 'positivo')
            ];
        }

        return $results;
    }

    public function hospitalForecast(?int $hospitalId = null, ?int $seguradoraId = null, int $days = 7): array
    {
        if (!$hospitalId && !$seguradoraId) {
            return [
                'available' => false,
                'message'   => 'Selecione ao menos um hospital ou operadora para gerar a previsão.'
            ];
        }

        $days = max(3, min($days, 21));
        $where = [
            "ac.data_intern_int IS NOT NULL",
            "ac.data_intern_int >= DATE_SUB(CURDATE(), INTERVAL 120 DAY)"
        ];
        $params = [];
        $joins = "";

        if ($hospitalId) {
            $where[] = "ac.fk_hospital_int = :hospital";
            $params[':hospital'] = $hospitalId;
        }
        if ($seguradoraId) {
            $joins .= " LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int ";
            $where[] = "pa.fk_seguradora_pac = :seguradora";
            $params[':seguradora'] = $seguradoraId;
        }

        $sql = "
            SELECT DATE(ac.data_intern_int) AS dia, COUNT(*) AS total
            FROM tb_internacao ac
            {$joins}
            WHERE " . implode(' AND ', $where) . "
            GROUP BY dia
            ORDER BY dia ASC
        ";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (!$rows) {
            return [
                'available' => false,
                'message'   => 'Ainda não há histórico suficiente para esta combinação.'
            ];
        }

        $history = [];
        foreach ($rows as $row) {
            $history[] = [
                'date'  => $row['dia'],
                'value' => (int) $row['total']
            ];
        }

        $values = array_column($history, 'value');
        $recentWindow = array_slice($values, -7);
        $prevWindow   = array_slice($values, -14, 7);
        $avgRecent = $this->safeAverage($recentWindow);
        $avgPrev = $this->safeAverage($prevWindow);
        $trend = $avgRecent - $avgPrev;

        $forecast = [];
        for ($i = 1; $i <= $days; $i++) {
            $date = (new DateTimeImmutable("today +{$i} day"))->format('Y-m-d');
            $value = max(0, round($avgRecent + ($trend * ($i / max(1, $days)))));
            $forecast[] = [
                'date'  => $date,
                'value' => (int) $value
            ];
        }

        $filters = [
            'hospital'   => $hospitalId ? $this->fetchHospitalSummary($hospitalId) : null,
            'operadora'  => $seguradoraId ? $this->fetchSeguradoraSummary($seguradoraId) : null,
        ];

        $msgParts = [];
        if ($filters['hospital']) $msgParts[] = $filters['hospital']['nome'];
        if ($filters['operadora']) $msgParts[] = "Operadora: " . $filters['operadora']['nome'];
        $context = $msgParts ? implode(' • ', $msgParts) : 'Seleção atual';
        $message = "{$context}: média recente de " . number_format($avgRecent, 1) . " casos/dia e tendência de "
            . number_format($trend, 1) . ". Use a projeção para planejar visitas, UTI e faturamento.";

        return [
            'available'      => true,
            'history'        => $history,
            'forecast'       => $forecast,
            'avg_recent'     => $avgRecent,
            'avg_previous'   => $avgPrev,
            'trend'          => $trend,
            'message'        => $message,
            'filters'        => $filters,
        ];
    }

    private function fetchHospitalSummary(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT id_hospital, nome_hosp FROM tb_hospital WHERE id_hospital = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ['id' => (int)$row['id_hospital'], 'nome' => $row['nome_hosp']] : null;
    }

    private function fetchSeguradoraSummary(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT id_seguradora, nome_seg FROM tb_seguradora WHERE id_seguradora = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ['id' => (int)$row['id_seguradora'], 'nome' => $row['nome_seg']] : null;
    }

    private function safeAverage(array $values): float
    {
        $values = array_filter($values, fn($v) => is_numeric($v));
        if (empty($values)) {
            return 0.0;
        }
        return array_sum($values) / count($values);
    }

    private function safeStdDev(array $values, float $mean): float
    {
        $values = array_filter($values, fn($v) => is_numeric($v));
        if (count($values) <= 1) {
            return 0.0;
        }
        $sum = 0.0;
        foreach ($values as $value) {
            $sum += pow($value - $mean, 2);
        }
        return sqrt($sum / count($values));
    }

    private function buildForecastMessage(float $avg, float $trend): string
    {
        if ($trend > 1.5) {
            return "Demanda em tendência de alta (+ " . number_format($trend, 1) . " casos). Prepare recursos extras.";
        }
        if ($trend < -1.5) {
            return "Demanda em leve queda (" . number_format($trend, 1) . "). Aproveite para acelerar auditorias.";
        }
        return "Demanda estável, média diária em " . number_format($avg, 1) . " internações.";
    }

    public function lengthOfStayForecasts(int $limit = 25): array
    {
        $current = $this->fetchCurrentInpatients($limit);
        if (!$current) {
            return [
                'available' => false,
                'message'   => 'Não há pacientes internados sem previsão disponível.'
            ];
        }

        $stats = $this->fetchLosHistoricStats();
        if (!$stats['map']) {
            return [
                'available' => false,
                'message'   => 'Ainda não há histórico suficiente para estimar permanência.'
            ];
        }

        $entries = [];
        foreach ($current as $row) {
            $prediction = $this->matchLosPrediction($row, $stats);
            $entries[] = array_merge($row, $prediction);
        }

        usort($entries, function($a, $b) {
            return $b['status_score'] <=> $a['status_score'];
        });

        return [
            'available' => true,
            'message'   => 'Estimativas geradas com base no histórico dos últimos 12 meses.',
            'entries'   => $entries
        ];
    }

    private function fetchCurrentInpatients(int $limit): array
    {
        $sql = "
            SELECT
                ac.id_internacao,
                ac.data_intern_int,
                ac.fk_hospital_int,
                ac.grupo_patologia_int,
                ac.acomodacao_int,
                ac.tipo_admissao_int,
                ac.internado_int,
                ho.nome_hosp,
                pa.nome_pac,
                pa.fk_seguradora_pac,
                se.seguradora_seg,
                GREATEST(DATEDIFF(CURRENT_DATE, ac.data_intern_int), 0) AS dias_atual,
                (
                    SELECT MAX(data_visita_vis)
                      FROM tb_visita
                     WHERE fk_internacao_vis = ac.id_internacao
                       AND (retificado IS NULL OR retificado IN (0, '0', '', 'n', 'N'))
                ) AS ultima_visita,
                (
                    SELECT COUNT(*)
                      FROM tb_visita
                     WHERE fk_internacao_vis = ac.id_internacao
                       AND (retificado IS NULL OR retificado IN (0, '0', '', 'n', 'N'))
                ) AS total_visitas,
                (
                    SELECT COUNT(*)
                      FROM tb_prorrogacao
                     WHERE fk_internacao_pror = ac.id_internacao
                ) AS total_prorrog,
                (
                    SELECT SUM(
                        CASE WHEN LOWER(IFNULL(evento_adverso_ges,'')) = 's'
                             THEN 1 ELSE 0 END
                    )
                      FROM tb_gestao
                     WHERE fk_internacao_ges = ac.id_internacao
                ) AS eventos_adversos
            FROM tb_internacao ac
            LEFT JOIN tb_hospital ho ON ho.id_hospital = ac.fk_hospital_int
            LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
            LEFT JOIN tb_seguradora se ON se.id_seguradora = pa.fk_seguradora_pac
            LEFT JOIN tb_alta al ON al.fk_id_int_alt = ac.id_internacao
            WHERE (ac.internado_int = 's' OR ac.internado_int IS NULL)
              AND (al.data_alta_alt IS NULL OR al.data_alta_alt = '0000-00-00')
            ORDER BY ac.data_intern_int ASC
            LIMIT :limite
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limite', max(5, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(function($row) {
            $row['dias_atual'] = (int)($row['dias_atual'] ?? 0);
            $row['fk_seguradora_pac'] = isset($row['fk_seguradora_pac']) ? (int)$row['fk_seguradora_pac'] : null;
            $row['total_visitas'] = (int)($row['total_visitas'] ?? 0);
            $row['total_prorrog'] = (int)($row['total_prorrog'] ?? 0);
            $row['eventos_adversos'] = (int)($row['eventos_adversos'] ?? 0);
            $ultimaVisita = $row['ultima_visita'] ?? null;
            if ($ultimaVisita && $ultimaVisita !== '0000-00-00') {
                $row['dias_sem_visita'] = max(0, (new DateTime($ultimaVisita))->diff(new DateTime())->days);
            } else {
                $row['dias_sem_visita'] = $row['dias_atual'];
            }
            return $row;
        }, $rows);
    }

    private function fetchLosHistoricStats(): array
    {
        $map = [];
        try {
            $sql = "
                SELECT
                    ac.fk_hospital_int,
                    pa.fk_seguradora_pac,
                    ac.grupo_patologia_int,
                    AVG(dias) AS media,
                    STDDEV_POP(dias) AS desvio
                FROM (
                    SELECT
                        ac.fk_hospital_int,
                        pa.fk_seguradora_pac,
                        ac.grupo_patologia_int,
                        GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1) AS dias
                    FROM tb_internacao ac
                    INNER JOIN tb_alta al ON al.fk_id_int_alt = ac.id_internacao
                    LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
                    WHERE ac.data_intern_int >= DATE_SUB(CURDATE(), INTERVAL 360 DAY)
                      AND al.data_alta_alt IS NOT NULL
                      AND al.data_alta_alt <> '0000-00-00'
                ) t
                GROUP BY fk_hospital_int, fk_seguradora_pac, grupo_patologia_int
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $row) {
                $key = $this->buildLosKey(
                    $row['fk_hospital_int'] ?? null,
                    $row['fk_seguradora_pac'] ?? null,
                    $row['grupo_patologia_int'] ?? null
                );
                $map[$key] = [
                    'avg' => (float)$row['media'],
                    'std' => max(0.5, (float)($row['desvio'] ?? 0))
                ];
            }
        } catch (PDOException $e) {
            if ((int)$e->getCode() !== 42 && strpos($e->getMessage(), '1054') === false) {
                throw $e;
            }
            $fallbackSql = "
                SELECT
                    AVG(GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1)) AS media,
                    STDDEV_POP(GREATEST(DATEDIFF(al.data_alta_alt, ac.data_intern_int), 1)) AS desvio
                FROM tb_internacao ac
                INNER JOIN tb_alta al ON al.fk_id_int_alt = ac.id_internacao
                WHERE ac.data_intern_int >= DATE_SUB(CURDATE(), INTERVAL 360 DAY)
                  AND al.data_alta_alt IS NOT NULL
                  AND al.data_alta_alt <> '0000-00-00'
            ";
            $stmt = $this->conn->prepare($fallbackSql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            if ($row) {
                $map[$this->buildLosKey(null, null, null)] = [
                    'avg' => (float)($row['media'] ?? 10),
                    'std' => max(1.0, (float)($row['desvio'] ?? 2))
                ];
            }
        }

        $overallAvg = array_values(array_map(fn($stat) => $stat['avg'], $map));
        return [
            'map' => $map,
            'overall' => [
                'avg' => $this->safeAverage($overallAvg) ?: 10,
                'std' =>  max(1.0, $this->safeStdDev($overallAvg, $this->safeAverage($overallAvg)))
            ]
        ];
    }

    private function buildLosKey($hospitalId, $segId, $grupo): string
    {
        return ($hospitalId ?: 0) . '|' . ($segId ?: 0) . '|' . ($grupo ?: '#');
    }

    private function matchLosPrediction(array $row, array $stats): array
    {
        $hospital = $row['fk_hospital_int'] ?? null;
        $seg = $row['fk_seguradora_pac'] ?? null;
        $grupo = $row['grupo_patologia_int'] ?? null;

        $candidates = [
            $this->buildLosKey($hospital, $seg, $grupo),
            $this->buildLosKey($hospital, $seg, null),
            $this->buildLosKey($hospital, null, $grupo),
            $this->buildLosKey($hospital, null, null),
            $this->buildLosKey(null, $seg, null),
            $this->buildLosKey(null, null, $grupo),
            $this->buildLosKey(null, null, null)
        ];

        $stat = null;
        foreach ($candidates as $key) {
            if (isset($stats['map'][$key])) {
                $stat = $stats['map'][$key];
                break;
            }
        }
        if (!$stat) $stat = $stats['overall'];

        $avg = max(1.0, (float)$stat['avg']);
        $std = max(1.0, (float)$stat['std']);
        $diasAtual = (int)($row['dias_atual'] ?? 0);
        $prevTotal = max($diasAtual, round($avg));
        $min = max($diasAtual, round($prevTotal - $std));
        $max = round($prevTotal + $std);
        $remaining = $prevTotal - $diasAtual;
        $statusScore = 0;
        $statusLabel = 'no_prazo';
        $alertMsg = 'Dentro do intervalo previsto.';
        if ($diasAtual > ($max)) {
            $statusLabel = 'atrasado';
            $statusScore = 2 + ($diasAtual - $max);
            $alertMsg = 'Acima do intervalo previsto.';
        } elseif ($diasAtual >= $min && $diasAtual <= $max) {
            $statusLabel = 'atencao';
            $statusScore = 1;
            $alertMsg = 'Em fase crítica do intervalo previsto.';
        }

        return [
            'prev_total'      => $prevTotal,
            'prev_min'        => $min,
            'prev_max'        => $max,
            'prev_remaining'  => $remaining,
            'status'          => $statusLabel,
            'status_score'    => $statusScore,
            'status_message'  => $alertMsg
        ];
    }

    public function explainabilityDrivers(int $limit = 25): array
    {
        $los = $this->lengthOfStayForecasts($limit);
        if (empty($los['available'])) {
            return [
                'available' => false,
                'message'   => 'Não foi possível carregar as explicações no momento.'
            ];
        }

        $entries = [];
        foreach ($los['entries'] as $entry) {
            $entries[] = array_merge($entry, [
                'factors' => $this->buildExplainabilityFactors($entry)
            ]);
        }

        return [
            'available' => true,
            'message'   => 'Fatores calculados com base no histórico e eventos recentes.',
            'entries'   => $entries
        ];
    }

    private function buildExplainabilityFactors(array $entry): array
    {
        $factors = [];
        $dias = (int)($entry['dias_atual'] ?? 0);
        $diasSemVisita = (int)($entry['dias_sem_visita'] ?? $dias);
        $totalProrrog = (int)($entry['total_prorrog'] ?? 0);
        $eventos = (int)($entry['eventos_adversos'] ?? 0);
        $status = (string)($entry['status'] ?? '');
        $remaining = (int)($entry['prev_remaining'] ?? 0);

        if ($dias >= 10) {
            $factors[] = "Permanência acumulada de {$dias} dias (acima da média).";
        }
        if ($status === 'atrasado') {
            $factors[] = "Excedeu o limite superior previsto ({$entry['prev_max']} dias).";
        } elseif ($status === 'atencao') {
            $factors[] = "Entrou na janela crítica do intervalo previsto.";
        }
        if ($remaining <= 0) {
            $factors[] = "Modelos apontam elegibilidade imediata para alta.";
        }
        if ($diasSemVisita >= 4) {
            $factors[] = "Última visita há {$diasSemVisita} dias — revisar programação.";
        }
        if ($totalProrrog > 0) {
            $factors[] = "{$totalProrrog} prorrogação(ões) em aberto.";
        }
        if ($eventos > 0) {
            $factors[] = "{$eventos} evento(s) adverso(s) registrado(s) nesta internação.";
        }

        if (!$factors) {
            $factors[] = "Nenhum fator crítico identificado; acompanhar rotina.";
        }

        return array_slice($factors, 0, 4);
    }

    private function parseCurrency($value): float
    {
        if ($value === null || $value === '') return 0.0;
        if (is_numeric($value)) return (float)$value;
        $clean = str_replace(['.', ' '], '', (string)$value);
        $clean = str_replace(',', '.', $clean);
        $num = filter_var($clean, FILTER_VALIDATE_FLOAT);
        return $num !== false ? (float)$num : 0.0;
    }

    private function logisticProbability(float $score): float
    {
        $x = ($score - 50) / 10;
        return round(1 / (1 + exp(-$x)), 3);
    }

    public function glosaRiskAlerts(int $limit = 40): array
    {
        $sql = "
            SELECT
                ca.id_capeante,
                ca.fk_int_capeante,
                ca.data_inicial_capeante,
                ca.data_final_capeante,
                ca.data_digit_capeante,
                ca.data_fech_capeante,
                ca.valor_apresentado_capeante,
                ca.valor_final_capeante,
                ca.valor_glosa_total,
                ca.em_auditoria_cap,
                ca.aberto_cap,
                ca.encerrado_cap,
                ca.senha_finalizada,
                ca.conta_parada_cap,
                ca.parada_motivo_cap,
                ca.parcial_capeante,
                ca.pacote,
                ca.desconto_valor_cap,
                ca.negociado_desconto_cap,
                ac.data_intern_int,
                ac.fk_hospital_int AS hospital_id,
                ho.nome_hosp,
                pa.nome_pac,
                pa.fk_seguradora_pac AS seguradora_id,
                se.seguradora_seg
            FROM tb_capeante ca
            LEFT JOIN tb_internacao ac ON ac.id_internacao = ca.fk_int_capeante
            LEFT JOIN tb_hospital ho ON ho.id_hospital = ac.fk_hospital_int
            LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
            LEFT JOIN tb_seguradora se ON se.id_seguradora = pa.fk_seguradora_pac
            WHERE ca.deletado_cap <> 's' OR ca.deletado_cap IS NULL
            ORDER BY ca.data_inicial_capeante DESC, ca.id_capeante DESC
            LIMIT :limite
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limite', max(10, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (!$rows) {
            return [
                'available' => false,
                'message'   => 'Nenhuma conta disponível para análise.'
            ];
        }

        $entries = [];
        foreach ($rows as $row) {
            $entries[] = $this->buildGlosaRiskEntry($row);
        }

        usort($entries, fn($a, $b) => $b['probability'] <=> $a['probability']);

        return [
            'available' => true,
            'message'   => 'Probabilidades estimadas com base em status e valores registrados.',
            'entries'   => $entries
        ];
    }

    private function buildGlosaRiskEntry(array $row): array
    {
        $valorApresentado = $this->parseCurrency($row['valor_apresentado_capeante'] ?? 0);
        $valorGlosa = $this->parseCurrency($row['valor_glosa_total'] ?? 0);
        $valorFinal = $this->parseCurrency($row['valor_final_capeante'] ?? 0);
        $diasAberto = 0;
        if (!empty($row['data_inicial_capeante']) && $row['data_inicial_capeante'] !== '0000-00-00') {
            $diasAberto = max(0, (new DateTime($row['data_inicial_capeante']))->diff(new DateTime())->days);
        }

        $score = 10;
        $factors = [];
        if (!empty($row['conta_parada_cap']) && strtolower($row['conta_parada_cap']) === 's') {
            $score += 25;
            $factors[] = 'Conta marcada como parada';
        }
        if (!empty($row['em_auditoria_cap']) && strtolower($row['em_auditoria_cap']) === 's') {
            $score += 10;
            $factors[] = 'Em auditoria pendente';
        }
        if (!empty($row['aberto_cap']) && strtolower($row['aberto_cap']) === 's') {
            $score += 6;
            $factors[] = 'Conta ainda aberta';
        }
        if (strtolower($row['encerrado_cap'] ?? '') !== 's') {
            $score += 4;
        }
        if ($diasAberto > 30) {
            $score += min(18, ($diasAberto - 30) * 0.6);
            $factors[] = "Aberta há {$diasAberto} dias";
        }
        if ($valorApresentado > 0) {
            $glosaRatio = $valorGlosa / max(1, $valorApresentado);
            if ($glosaRatio >= 0.25) {
                $score += 15;
                $factors[] = 'Glosa projetada acima de 25%';
            } elseif ($glosaRatio >= 0.15) {
                $score += 8;
                $factors[] = 'Glosa projetada acima de 15%';
            }
        } else {
            $glosaRatio = 0;
        }
        if ($valorApresentado >= 80000) {
            $score += 6;
            $factors[] = 'Conta de alto valor';
        }
        if ($valorFinal <= 0 && $valorApresentado > 0) {
            $score += 5;
            $factors[] = 'Sem valor final registrado';
        }
        if (!empty($row['parada_motivo_cap'])) {
            $factors[] = 'Motivo parada: ' . $row['parada_motivo_cap'];
        }
        if (!empty($row['desconto_valor_cap']) || !empty($row['negociado_desconto_cap'])) {
            $factors[] = 'Desconto/negociação em andamento';
        }

        $score = max(5, min(95, $score));
        $prob = $this->logisticProbability($score);
        $riskLevel = $prob >= 0.7 ? 'alto' : ($prob >= 0.45 ? 'moderado' : 'baixo');
        $recommendation = $this->glosaRecommendation($riskLevel, $row, $diasAberto);

        return [
            'id_capeante'       => (int)($row['id_capeante'] ?? 0),
            'internacao_id'     => (int)($row['fk_int_capeante'] ?? 0),
            'nome_pac'          => $row['nome_pac'] ?? '',
            'nome_hosp'         => $row['nome_hosp'] ?? '',
            'hospital_id'       => (int)($row['hospital_id'] ?? 0),
            'operadora'         => $row['seguradora_seg'] ?? '',
            'seguradora_id'     => (int)($row['seguradora_id'] ?? 0),
            'data_inicial'      => $row['data_inicial_capeante'] ?? null,
            'valor_apresentado' => $valorApresentado,
            'valor_glosa'       => $valorGlosa,
            'glosa_ratio'       => $glosaRatio,
            'dias_aberto'       => $diasAberto,
            'status_flags'      => [
                'parada'      => $row['conta_parada_cap'] ?? '',
                'em_auditoria'=> $row['em_auditoria_cap'] ?? '',
                'aberto'      => $row['aberto_cap'] ?? '',
                'encerrado'   => $row['encerrado_cap'] ?? '',
                'senha_finalizada' => $row['senha_finalizada'] ?? ''
            ],
            'probability'       => $prob,
            'risk_level'        => $riskLevel,
            'factors'           => $factors ?: ['Sem fatores críticos identificados.'],
            'recommendation'    => $recommendation
        ];
    }

    private function glosaRecommendation(string $level, array $row, int $diasAberto): string
    {
        switch ($level) {
            case 'alto':
                return 'Revisar imediatamente com enfermagem/médico, validar justificativas e sinalizar operadora para possível glosa.';
            case 'moderado':
                return 'Agendar revisão prévia e confirmar documentos antes do faturamento; alinhar negociação.';
            default:
                return 'Monitorar na rotina e garantir atualização de documentos antes do envio ao faturamento.';
        }
    }

    public function clinicalClusters(int $limit = 120): array
    {
        $sql = "
            SELECT
                pa.id_paciente,
                pa.nome_pac,
                se.seguradora_seg,
                COUNT(*) AS total_intern,
                AVG(GREATEST(DATEDIFF(COALESCE(al.data_alta_alt, CURRENT_DATE), ac.data_intern_int), 0)) AS media_dias,
                SUM(CASE WHEN (al.data_alta_alt IS NULL OR al.data_alta_alt = '0000-00-00') THEN 1 ELSE 0 END) AS abertas,
                SUM(CASE WHEN GREATEST(DATEDIFF(COALESCE(al.data_alta_alt, CURRENT_DATE), ac.data_intern_int), 0) >= 20 THEN 1 ELSE 0 END) AS longas,
                COUNT(DISTINCT CASE WHEN LOWER(IFNULL(ge.evento_adverso_ges,'')) = 's' THEN ge.id_gestao END) AS eventos,
                MIN(ac.data_intern_int) AS primeira_int,
                MAX(ac.data_intern_int) AS ultima_int
            FROM tb_internacao ac
            LEFT JOIN tb_alta al ON al.fk_id_int_alt = ac.id_internacao
            LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
            LEFT JOIN tb_seguradora se ON se.id_seguradora = pa.fk_seguradora_pac
            LEFT JOIN tb_gestao ge ON ge.fk_internacao_ges = ac.id_internacao
            WHERE pa.id_paciente IS NOT NULL
              AND ac.data_intern_int >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)
            GROUP BY pa.id_paciente
            ORDER BY ultima_int DESC
            LIMIT :limite
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limite', max(20, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (!$rows) {
            return [
                'available' => false,
                'message'   => 'Sem pacientes suficientes para agrupar no momento.'
            ];
        }

        $entries = [];
        foreach ($rows as $row) {
            $entries[] = $this->classifyClinicalCluster($row);
        }

        usort($entries, function ($a, $b) {
            $order = ['Alta complexidade', 'Crônico recorrente', 'Risco clínico', 'Perfil padrão'];
            $idxA = array_search($a['cluster_label'], $order,true);
            $idxB = array_search($b['cluster_label'], $order,true);
            return ($idxA === false ? 99 : $idxA) <=> ($idxB === false ? 99 : $idxB);
        });

        return [
            'available' => true,
            'message'   => 'Clusters formados com base em permanência média, recorrência e eventos adversos.',
            'entries'   => $entries
        ];
    }

    private function classifyClinicalCluster(array $row): array
    {
        $total = (int)($row['total_intern'] ?? 0);
        $media = round((float)($row['media_dias'] ?? 0), 1);
        $abertas = (int)($row['abertas'] ?? 0);
        $longas = (int)($row['longas'] ?? 0);
        $eventos = (int)($row['eventos'] ?? 0);

        $label = 'Perfil padrão';
        $descricao = 'Sem indícios de criticidade recente.';
        $acao = 'Operadora mantém monitoramento de rotina.';

        if ($media >= 20 || $longas > 0 || $abertas > 0) {
            $label = 'Alta complexidade';
            $descricao = 'Permanência média elevada ou conta ainda aberta além da expectativa.';
            $acao = 'Operadora deve acionar o hospital para negociar alta e revisar cobertura imediatamente.';
        } elseif ($total >= 3 && $media >= 10) {
            $label = 'Crônico recorrente';
            $descricao = 'Várias internações no período e permanência prolongada.';
            $acao = 'Operadora deve estruturar plano longitudinal e pactuar metas assistenciais com o prestador.';
        } elseif ($eventos > 0) {
            $label = 'Risco clínico';
            $descricao = 'Eventos adversos recentes exigem vigilância.';
            $acao = 'Operadora intensifica auditoria clínica e cobra revisão de protocolos do hospital.';
        }

        return [
            'paciente_id'   => (int)($row['id_paciente'] ?? 0),
            'nome_pac'      => $row['nome_pac'] ?? '',
            'operadora'     => $row['seguradora_seg'] ?? '',
            'total_intern'  => $total,
            'media_dias'    => $media,
            'abertas'       => $abertas,
            'longas'        => $longas,
            'eventos'       => $eventos,
            'cluster_label' => $label,
            'cluster_desc'  => $descricao,
            'acao'          => $acao
        ];
    }
}
