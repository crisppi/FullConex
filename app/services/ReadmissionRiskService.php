<?php

class ReadmissionRiskService
{
    private const DEFAULT_ALERT_THRESHOLD = 0.55;
    private const DEFAULT_LONG_STAY_THRESHOLD = 20;

    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function scoreInternacao(int $internacaoId): array
    {
        if ($internacaoId <= 0) {
            return [
                'available' => false,
                'message'   => 'Internação inválida.'
            ];
        }

        $internacao = $this->fetchInternacaoContext($internacaoId);
        if (!$internacao) {
            return [
                'available' => false,
                'message'   => 'Não foi possível localizar os dados da internação.'
            ];
        }

        $pacienteId          = (int) ($internacao['fk_paciente_int'] ?? 0);
        $age                 = (int) ($internacao['idade'] ?? 0);
        $sexo                = strtoupper((string) ($internacao['sexo_pac'] ?? ''));
        $grupoPatologia      = (string) ($internacao['grupo_patologia_int'] ?? '');
        $patologiaDescricao  = (string) ($internacao['patologia_pat'] ?? ($internacao['descricao_pat'] ?? ''));
        $diasInternadoAtual  = (int) ($internacao['dias_atual'] ?? 0);
        $longThreshold       = (int) ($internacao['longa_permanencia_seg'] ?? self::DEFAULT_LONG_STAY_THRESHOLD);
        if ($longThreshold <= 0) {
            $longThreshold = self::DEFAULT_LONG_STAY_THRESHOLD;
        }

        $antecedentes = $this->countAntecedentes($internacaoId, $pacienteId);
        $admissions   = $this->previousAdmissionsSummary($pacienteId, $internacaoId);
        $eventStats   = $this->eventStats($internacaoId);

        $features = [
            'faixa_etaria'           => $this->ageBucket($age),
            'idade'                  => $age,
            'sexo'                   => $sexo ?: 'ND',
            'antecedentes'           => $antecedentes,
            'grupo_patologia'        => $grupoPatologia ?: 'Não informado',
            'patologia'              => $patologiaDescricao ?: 'Não informada',
            'internacoes_previas'    => $admissions['count'],
            'mp_previas'             => $admissions['avg_los'],
            'longa_permanencia'      => $diasInternadoAtual >= $longThreshold,
            'dias_internado_atual'   => $diasInternadoAtual,
            'eventos_adversos'       => $eventStats['count'],
            'mp_limite'              => $longThreshold,
            'evento_prolongou'       => $eventStats['prolongou'] > 0
        ];

        $score       = $this->computeScore($features);
        $probability = $this->logisticProbability($score);
        $riskLevel   = $probability >= 0.7 ? 'alto' : ($probability >= 0.45 ? 'moderado' : 'baixo');

        return [
            'available'       => true,
            'internacao_id'   => $internacaoId,
            'probability'     => $probability,
            'risk_level'      => $riskLevel,
            'risk_score'      => $score,
            'features'        => $features,
            'threshold'       => self::DEFAULT_ALERT_THRESHOLD,
            'trigger_alert'   => $probability >= self::DEFAULT_ALERT_THRESHOLD,
            'recommendations' => $this->buildRecommendations($riskLevel, $features),
            'explanation'     => $this->buildExplanation($features, $riskLevel)
        ];
    }

    private function fetchInternacaoContext(int $internacaoId): ?array
    {
        $sql = "
            SELECT 
                ac.id_internacao,
                ac.fk_paciente_int,
                ac.data_intern_int,
                ac.grupo_patologia_int,
                ac.fk_patologia_int,
                pa.sexo_pac,
                pa.data_nasc_pac,
                pa.fk_seguradora_pac,
                TIMESTAMPDIFF(YEAR, pa.data_nasc_pac, CURDATE()) AS idade,
                se.longa_permanencia_seg,
                pat.patologia_pat,
                pat.descricao AS descricao_pat,
                GREATEST(
                    DATEDIFF(
                        COALESCE(al.data_alta_alt, CURRENT_DATE),
                        ac.data_intern_int
                    ),
                    0
                ) AS dias_atual
            FROM tb_internacao ac
            LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
            LEFT JOIN tb_alta al ON al.fk_id_int_alt = ac.id_internacao
            LEFT JOIN tb_seguradora se ON se.id_seguradora = pa.fk_seguradora_pac
            LEFT JOIN tb_patologia pat ON pat.id_patologia = ac.fk_patologia_int
            WHERE ac.id_internacao = :internacao
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':internacao', $internacaoId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function countAntecedentes(int $internacaoId, int $pacienteId): int
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) 
                  FROM tb_intern_antec 
                 WHERE fk_internacao_ant_int = :internacao
                    OR (fk_id_paciente = :paciente AND fk_id_paciente > 0)
            ");
            $stmt->bindValue(':internacao', $internacaoId, PDO::PARAM_INT);
            $stmt->bindValue(':paciente', $pacienteId, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function previousAdmissionsSummary(int $pacienteId, int $currentInternacaoId): array
    {
        if ($pacienteId <= 0) {
            return ['count' => 0, 'avg_los' => 0.0];
        }
        $sql = "
            SELECT
                COUNT(*) AS total_intern,
                AVG(
                    GREATEST(
                        DATEDIFF(COALESCE(al.data_alta_alt, CURRENT_DATE), ac.data_intern_int),
                        0
                    )
                ) AS media_dias
            FROM tb_internacao ac
            LEFT JOIN tb_alta al ON al.fk_id_int_alt = ac.id_internacao
            WHERE ac.fk_paciente_int = :paciente
              AND ac.id_internacao <> :atual
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':paciente', $pacienteId, PDO::PARAM_INT);
        $stmt->bindValue(':atual', $currentInternacaoId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'count'   => (int) ($row['total_intern'] ?? 0),
            'avg_los' => round((float) ($row['media_dias'] ?? 0), 1)
        ];
    }

    private function eventStats(int $internacaoId): array
    {
        $sql = "
            SELECT 
                SUM(CASE WHEN LOWER(evento_adverso_ges) = 's' THEN 1 ELSE 0 END) AS eventos,
                SUM(CASE WHEN LOWER(evento_prolongou_internacao_ges) = 's' THEN 1 ELSE 0 END) AS prolongou
            FROM tb_gestao
            WHERE fk_internacao_ges = :internacao
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':internacao', $internacaoId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['eventos' => 0, 'prolongou' => 0];
        return [
            'count'     => (int) ($row['eventos'] ?? 0),
            'prolongou' => (int) ($row['prolongou'] ?? 0)
        ];
    }

    private function ageBucket(int $age): string
    {
        if ($age <= 0) {
            return 'indefinido';
        }
        if ($age < 18) {
            return 'pediátrico';
        }
        if ($age < 40) {
            return 'adulto jovem';
        }
        if ($age < 65) {
            return 'adulto';
        }
        return 'idoso';
    }

    private function computeScore(array $features): float
    {
        $score = 10.0;

        switch ($features['faixa_etaria']) {
            case 'idoso':
                $score += 22;
                break;
            case 'adulto':
                $score += 14;
                break;
            case 'adulto jovem':
                $score += 8;
                break;
            case 'pediátrico':
                $score += 12;
                break;
        }

        if (in_array($features['sexo'], ['M', 'O'], true)) {
            $score += 3;
        }

        $score += min(18, $features['antecedentes'] * 4);
        $score += min(25, $features['internacoes_previas'] * 6);

        if ($features['mp_previas'] >= 12) {
            $score += 10;
        } elseif ($features['mp_previas'] >= 8) {
            $score += 6;
        }

        if ($features['longa_permanencia']) {
            $score += 8;
        }

        if ($features['eventos_adversos'] > 0) {
            $score += min(12, $features['eventos_adversos'] * 4);
        }

        if ($features['evento_prolongou']) {
            $score += 6;
        }

        $grupo = mb_strtolower($features['grupo_patologia'], 'UTF-8');
        $pat   = mb_strtolower($features['patologia'], 'UTF-8');
        $gruposAlta = ['cardio', 'cardiologia', 'oncologia', 'neuro', 'uti', 'renal', 'sepse', 'resp', 'hemat'];
        foreach ($gruposAlta as $needle) {
            if (str_contains($grupo, $needle) || str_contains($pat, $needle)) {
                $score += 15;
                break;
            }
        }

        $score = max(5, min($score, 95));
        return $score;
    }

    private function logisticProbability(float $score): float
    {
        $x = ($score - 50) / 10;
        $prob = 1 / (1 + exp(-$x));
        return round($prob, 3);
    }

    private function buildExplanation(array $features, string $riskLevel): string
    {
        $parts = [];
        $parts[] = ucfirst($features['faixa_etaria']) . " ({$features['idade']} anos)";
        if ($features['antecedentes'] > 0) {
            $parts[] = "{$features['antecedentes']} antecedentes registrados";
        }
        if ($features['internacoes_previas'] > 0) {
            $parts[] = "{$features['internacoes_previas']} internações prévias";
        }
        if ($features['longa_permanencia']) {
            $parts[] = "permanência atual acima de {$features['mp_limite']} dias";
        }
        if ($features['eventos_adversos'] > 0) {
            $parts[] = "{$features['eventos_adversos']} evento(s) adverso(s) recente(s)";
        }

        $desc = implode(', ', $parts);
        if ($desc === '') {
            $desc = 'Sem fatores críticos identificados nos registros disponíveis.';
        } else {
            $desc .= '.';
        }

        return "Risco {$riskLevel}: {$desc}";
    }

    private function buildRecommendations(string $riskLevel, array $features): array
    {
        $recs = [];
        if ($riskLevel === 'alto') {
            $recs[] = 'Acionar protocolo intensivo (auditoria diária + visita presencial).';
            $recs[] = 'Validar plano de alta e acompanhamento domiciliar junto à família.';
        } elseif ($riskLevel === 'moderado') {
            $recs[] = 'Planejar visita extra e reforçar educação do paciente sobre sinais de alerta.';
        } else {
            $recs[] = 'Seguir rotina padrão e monitorar novos eventos adversos.';
        }

        if ($features['antecedentes'] >= 3) {
            $recs[] = 'Revisar antecedentes críticos e ajustar conciliação medicamentosa.';
        }
        if ($features['internacoes_previas'] >= 2) {
            $recs[] = 'Checar aderência aos protocolos pós-alta anteriores.';
        }
        if ($features['longa_permanencia']) {
            $recs[] = 'Avaliar barreiras sociais/assistenciais para alta segura.';
        }

        return $recs;
    }
}
