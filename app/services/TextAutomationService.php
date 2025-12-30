<?php

class TextAutomationService
{
    private PDO $conn;
    private ?array $prorrogacaoColumns = null;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function generateTexts(int $internacaoId): array
    {
        if ($internacaoId <= 0) {
            throw new InvalidArgumentException('Informe o ID da internação.');
        }

        $context = $this->fetchContext($internacaoId);
        if (!$context) {
            throw new RuntimeException('Internação não encontrada ou sem dados vinculados.');
        }

        $visits = $this->fetchRecentVisits($internacaoId, 4);
        $prorrogacoes = $this->fetchProrrogacoes($internacaoId);

        return [
            'context' => $context,
            'visit_summary' => $this->buildVisitSummary($context, $visits),
            'visit_bullets' => $this->buildVisitBullets($visits),
            'prorrogacao_summary' => $this->buildProrrogacaoSummary($context, $prorrogacoes, $visits),
            'prorrogacao_bullets' => $this->buildProrrogacaoBullets($prorrogacoes),
        ];
    }

    private function fetchContext(int $internacaoId): ?array
    {
        $sql = "
            SELECT 
                ac.id_internacao,
                ac.data_intern_int,
                ac.acomodacao_int,
                ac.grupo_patologia_int,
                ac.modo_internacao_int,
                ac.tipo_admissao_int,
                ac.internado_int,
                ac.timer_int,
                hos.nome_hosp,
                pa.nome_pac,
                pa.idade_pac,
                pa.sexo_pac,
                pa.data_nasc_pac
            FROM tb_internacao ac
            INNER JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
            INNER JOIN tb_hospital hos ON hos.id_hospital = ac.fk_hospital_int
            WHERE ac.id_internacao = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $internacaoId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $row['dias_internado'] = $this->calcDiasInternado($row['data_intern_int']);
        $row['idade'] = $this->resolveIdade($row['idade_pac'], $row['data_nasc_pac']);

        return $row;
    }

    private function fetchRecentVisits(int $internacaoId, int $limit = 3): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                id_visita,
                data_visita_vis,
                visita_no_vis,
                visita_med_vis,
                visita_enf_vis,
                COALESCE(rel_visita_vis, '') AS rel_visita_vis,
                COALESCE(acoes_int_vis, '') AS acoes_int_vis
            FROM tb_visita
            WHERE fk_internacao_vis = :id
            ORDER BY data_visita_vis DESC, id_visita DESC
            LIMIT :limite
        ");
        $stmt->bindValue(':id', $internacaoId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        $visits = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $visits[] = [
                'id'        => (int) $row['id_visita'],
                'data'      => $row['data_visita_vis'],
                'numero'    => $row['visita_no_vis'],
                'resumo'    => trim($row['rel_visita_vis']),
                'acoes'     => trim($row['acoes_int_vis']),
                'resp_med'  => $row['visita_med_vis'],
                'resp_enf'  => $row['visita_enf_vis']
            ];
        }

        return $visits;
    }

    private function fetchProrrogacoes(int $internacaoId): array
    {
        $base = [
            'id_prorrogacao',
            'prorrog1_ini_pror',
            'prorrog1_fim_pror',
            'diarias_1',
            'acomod1_pror',
            'isol_1_pror'
        ];
        $optionals = [
            'alto_custo_pror',
            'rel_alto_custo_pror',
            'evento_adverso_pror',
            'rel_evento_adverso_pror',
            'home_care_pror',
            'rel_home_care_pror'
        ];
        $availableCols = $this->getProrrogacaoColumns();
        $selectCols = $base;
        foreach ($optionals as $col) {
            if (in_array($col, $availableCols, true)) {
                $selectCols[] = $col;
            }
        }

        $sql = sprintf(
            "SELECT %s FROM tb_prorrogacao WHERE fk_internacao_pror = :id ORDER BY prorrog1_ini_pror DESC, id_prorrogacao DESC LIMIT 4",
            implode(', ', $selectCols)
        );

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $internacaoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function buildVisitSummary(array $context, array $visits): string
    {
        $nome = $context['nome_pac'] ?? 'Paciente';
        $idade = $context['idade'] ? "{$context['idade']} anos" : 'idade não informada';
        $hospital = $context['nome_hosp'] ?? 'hospital não identificado';
        $dias = $context['dias_internado'];
        $acomodacao = $context['acomodacao_int'] ?: 'acomodação não registrada';
        $patologia = $context['grupo_patologia_int'] ?: 'sem patologia principal cadastrada';

        $intro = sprintf(
            '%s (%s) está internado(a) há %s dia(s) no %s, em %s, para manejo de %s.',
            $nome,
            $idade,
            $dias,
            $hospital,
            strtolower($acomodacao),
            strtolower($patologia)
        );

        if (empty($visits)) {
            return $intro . ' Ainda não há registros de visitas assistenciais que permitam detalhar a evolução.';
        }

        $trechos = [];
        foreach ($visits as $visit) {
            $data = $visit['data'] ? date('d/m', strtotime($visit['data'])) : 'data não informada';
            $resumo = $visit['resumo'] ?: 'sem evolução registrada';
            $acoes = $visit['acoes'] ? " Ações sugeridas: {$visit['acoes']}." : '';

            $referencia = $visit['resp_med'] ?: $visit['resp_enf'] ?: 'equipe assistencial';
            $trechos[] = "{$data} - Avaliação {$referencia}: {$resumo}{$acoes}";
        }

        return $intro . ' Principais evoluções recentes: ' . implode(' ', $trechos);
    }

    private function buildVisitBullets(array $visits): array
    {
        $bullets = [];
        foreach ($visits as $visit) {
            $data = $visit['data'] ? date('d/m/Y', strtotime($visit['data'])) : 'data não informada';
            $resp = $visit['resp_med'] ?: $visit['resp_enf'] ?: 'Equipe assistencial';
            $texto = trim($visit['resumo'] ?: $visit['acoes']);
            if (!$texto) {
                $texto = 'Registro sem detalhes clínicos.';
            }
            $bullets[] = "{$data} - {$resp}: {$texto}";
        }
        return $bullets;
    }

    private function buildProrrogacaoSummary(array $context, array $prorrogacoes, array $visits): ?string
    {
        if (empty($prorrogacoes)) {
            return null;
        }

        $ultima = $prorrogacoes[0];
        $inicio = $ultima['prorrog1_ini_pror'] ? date('d/m/Y', strtotime($ultima['prorrog1_ini_pror'])) : 'data não informada';
        $fim = $ultima['prorrog1_fim_pror'] ? date('d/m/Y', strtotime($ultima['prorrog1_fim_pror'])) : 'data não informada';
        $diarias = $ultima['diarias_1'] ? "{$ultima['diarias_1']} diárias adicionais" : 'quantitativo não informado';

        $texto = "Solicita-se manutenção do internamento entre {$inicio} e {$fim}, totalizando {$diarias}. ";

        if (!empty($visits)) {
            $ultimaVisita = $visits[0];
            $texto .= "A última visita registrada descreve: " . ($ultimaVisita['resumo'] ?: 'evolução sem descrição detalhada') . '. ';
        }

        if (!empty($ultima['rel_alto_custo_pror'])) {
            $texto .= "Justificativa financeira: {$ultima['rel_alto_custo_pror']}. ";
        }
        if (!empty($ultima['rel_evento_adverso_pror'])) {
            $texto .= "Registro de evento adverso: {$ultima['rel_evento_adverso_pror']}. ";
        }
        if (!empty($ultima['rel_home_care_pror'])) {
            $texto .= "Plano de desospitalização/home care: {$ultima['rel_home_care_pror']}. ";
        }

        $texto .= "Recomenda-se manter monitoramento diário e reavaliar necessidade de prorrogação conforme resposta clínica.";

        return $texto;
    }

    private function buildProrrogacaoBullets(array $prorrogacoes): array
    {
        $bullets = [];
        foreach ($prorrogacoes as $pr) {
            $periodo = [];
            if ($pr['prorrog1_ini_pror']) {
                $periodo[] = date('d/m', strtotime($pr['prorrog1_ini_pror']));
            }
            if ($pr['prorrog1_fim_pror']) {
                $periodo[] = date('d/m/Y', strtotime($pr['prorrog1_fim_pror']));
            }
            $periodoTxt = $periodo ? implode(' a ', $periodo) : 'Período não informado';
            $diarias = $pr['diarias_1'] ? "{$pr['diarias_1']} diárias" : 'quantitativo não informado';
            $just = $pr['rel_alto_custo_pror'] ?? $pr['rel_home_care_pror'] ?? $pr['rel_evento_adverso_pror'] ?? 'Registro sem justificativa detalhada.';
            $bullets[] = "{$periodoTxt} - {$diarias}. {$just}";
        }
        return $bullets;
    }

    private function calcDiasInternado(?string $dataInternacao): int
    {
        if (!$dataInternacao) {
            return 0;
        }
        try {
            $inicio = new DateTime($dataInternacao);
            $agora = new DateTime();
            return max(0, (int)$inicio->diff($agora)->format('%a'));
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function resolveIdade($idadeInformada, $dataNascimento): ?int
    {
        if (is_numeric($idadeInformada) && (int)$idadeInformada > 0) {
            return (int)$idadeInformada;
        }
        if ($dataNascimento) {
            try {
                $nasc = new DateTime($dataNascimento);
                $agora = new DateTime();
                return (int)$nasc->diff($agora)->y;
            } catch (Throwable $e) {
                return null;
            }
        }
        return null;
    }

    private function getProrrogacaoColumns(): array
    {
        if ($this->prorrogacaoColumns !== null) {
            return $this->prorrogacaoColumns;
        }
        try {
            $stmt = $this->conn->query("
                SELECT COLUMN_NAME 
                  FROM information_schema.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'tb_prorrogacao'
            ");
            $this->prorrogacaoColumns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [], 'COLUMN_NAME');
        } catch (Throwable $e) {
            $this->prorrogacaoColumns = [];
        }
        return $this->prorrogacaoColumns;
    }
}
