<?php

declare(strict_types=1);

require_once("./models/visita.php");
require_once("./models/message.php");

class visitaDAO
{
    private PDO $conn;
    private string $url;
    public Message $message;

    private const TABLE = 'tb_visita';

    public function __construct(PDO $conn, string $url)
    {
        $this->conn    = $conn;
        $this->url     = $url;
        $this->message = new Message($url);
    }

    /* ======================================================
       BUILDER
       ====================================================== */
    public function buildvisita(array $data): visita
    {
        $v = new visita();

        $v->id_visita               = $data["id_visita"]               ?? null;
        $v->fk_internacao_vis       = $data["fk_internacao_vis"]       ?? null;
        $v->rel_visita_vis          = $data["rel_visita_vis"]          ?? null;
        $v->acoes_int_vis           = $data["acoes_int_vis"]           ?? null;
        $v->usuario_create          = $data["usuario_create"]          ?? null;
        $v->data_visita_vis         = $data["data_visita_vis"]         ?? null;
        $v->visita_no_vis           = $data["visita_no_vis"]           ?? null;
        $v->visita_auditor_prof_med = $data["visita_auditor_prof_med"] ?? null;
        $v->visita_auditor_prof_enf = $data["visita_auditor_prof_enf"] ?? null;
        $v->visita_med_vis          = $data["visita_med_vis"]          ?? null;
        $v->visita_enf_vis          = $data["visita_enf_vis"]          ?? null;
        $v->fk_usuario_vis          = $data["fk_usuario_vis"]          ?? null;
        $v->exames_enf              = $data["exames_enf"]              ?? null;
        $v->oportunidades_enf       = $data["oportunidades_enf"]       ?? null;
        $v->programacao_enf         = $data["programacao_enf"]         ?? null;
        $v->retificou               = $data["retificou"]               ?? null;
        $v->retificado              = $data["retificado"]              ?? null;

        return $v;
    }

    /* ======================================================
       HELPERS internos
       ====================================================== */
    /** Bind seguro para INT (aceita null/string vazia) */
    private function bindIntOrNull(PDOStatement $stmt, string $param, $value): void
    {
        if ($value === null || $value === '') {
            $stmt->bindValue($param, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue($param, (int)$value, PDO::PARAM_INT);
        }
    }

    /** Normaliza flags s/n */
    private function sn($val, string $default = 'n'): string
    {
        $v = strtolower((string)$val);
        return ($v === 's' || $v === 'n') ? $v : $default;
    }

    /** Data/hora padrão */
    private function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    /** Executa o INSERT base e retorna o lastInsertId */
    private function doInsert(visita $visita): int
    {
        $sql = "INSERT INTO " . self::TABLE . " (
            fk_internacao_vis,
            rel_visita_vis,
            acoes_int_vis,
            usuario_create,
            visita_auditor_prof_med,
            visita_auditor_prof_enf,
            visita_med_vis,
            visita_enf_vis,
            visita_no_vis,
            fk_usuario_vis,
            data_visita_vis,
            exames_enf,
            oportunidades_enf,
            programacao_enf,
            retificou
        ) VALUES (
            :fk_internacao_vis,
            :rel_visita_vis,
            :acoes_int_vis,
            :usuario_create,
            :visita_auditor_prof_med,
            :visita_auditor_prof_enf,
            :visita_med_vis,
            :visita_enf_vis,
            :visita_no_vis,
            :fk_usuario_vis,
            :data_visita_vis,
            :exames_enf,
            :oportunidades_enf,
            :programacao_enf,
            :retificou
        )";

        $stmt = $this->conn->prepare($sql);

        // INT/FK
        $this->bindIntOrNull($stmt, ":fk_internacao_vis", $visita->fk_internacao_vis);
        $this->bindIntOrNull($stmt, ":fk_usuario_vis",   $visita->fk_usuario_vis);

        // TEXTOS
        $stmt->bindValue(":rel_visita_vis",          $visita->rel_visita_vis);
        $stmt->bindValue(":acoes_int_vis",           $visita->acoes_int_vis);
        $stmt->bindValue(":usuario_create",          $visita->usuario_create);
        $stmt->bindValue(":visita_auditor_prof_med", $visita->visita_auditor_prof_med);
        $stmt->bindValue(":visita_auditor_prof_enf", $visita->visita_auditor_prof_enf);

        // FLAGS s/n
        $stmt->bindValue(":visita_med_vis", $this->sn($visita->visita_med_vis, 'n'));
        $stmt->bindValue(":visita_enf_vis", $this->sn($visita->visita_enf_vis, 'n'));

        // VISITA #
        $stmt->bindValue(":visita_no_vis", (int)($visita->visita_no_vis ?: 1), PDO::PARAM_INT);

        // DATAS
        $stmt->bindValue(":data_visita_vis", $visita->data_visita_vis ?: $this->now());

        // ENF
        $stmt->bindValue(":exames_enf",        $visita->exames_enf ?: 'Sem exames relevantes no período');
        $stmt->bindValue(":oportunidades_enf", $visita->oportunidades_enf);
        $stmt->bindValue(":programacao_enf",   $visita->programacao_enf);

        // RETIFICAÇÃO (FK para id_visita anterior, se existir)
        $this->bindIntOrNull($stmt, ":retificou", $visita->retificou);

        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    /* ======================================================
       CREATE / UPDATE / DELETE
       ====================================================== */
    /** Cria e retorna o ID (compatível com seu uso atual) */
    public function create(visita $visita): int
    {
        return $this->doInsert($visita);
    }

    /** Idêntico ao create(), deixando explícito o retorno do ID */
    public function createReturningId(visita $visita): int
    {
        return $this->doInsert($visita);
    }

    /** Atualiza apenas colunas reais; retorna sucesso */
    public function update(array $data): bool
    {
        $sql = "UPDATE " . self::TABLE . " SET
            rel_visita_vis          = :rel_visita_vis,
            acoes_int_vis           = :acoes_int_vis,
            usuario_create          = :usuario_create,
            visita_auditor_prof_med = :visita_auditor_prof_med,
            visita_auditor_prof_enf = :visita_auditor_prof_enf,
            visita_med_vis          = :visita_med_vis,
            visita_enf_vis          = :visita_enf_vis,
            visita_no_vis           = :visita_no_vis,
            fk_usuario_vis          = :fk_usuario_vis,
            data_visita_vis         = :data_visita_vis,
            exames_enf              = :exames_enf,
            oportunidades_enf       = :oportunidades_enf,
            programacao_enf         = :programacao_enf
        WHERE id_visita = :id_visita";

        $stmt = $this->conn->prepare($sql);

        // TEXTOS
        $stmt->bindValue(":rel_visita_vis",          $data['rel_visita_vis']          ?? null);
        $stmt->bindValue(":acoes_int_vis",           $data['acoes_int_vis']           ?? null);
        $stmt->bindValue(":usuario_create",          $data['usuario_create']          ?? null);
        $stmt->bindValue(":visita_auditor_prof_med", $data['visita_auditor_prof_med'] ?? null);
        $stmt->bindValue(":visita_auditor_prof_enf", $data['visita_auditor_prof_enf'] ?? null);

        // FLAGS
        $stmt->bindValue(":visita_med_vis", $this->sn($data['visita_med_vis'] ?? 'n', 'n'));
        $stmt->bindValue(":visita_enf_vis", $this->sn($data['visita_enf_vis'] ?? 'n', 'n'));

        // VISITA #
        $stmt->bindValue(":visita_no_vis", (int)($data['visita_no_vis'] ?? 1), PDO::PARAM_INT);

        // FK/INT
        $this->bindIntOrNull($stmt, ":fk_usuario_vis", $data['fk_usuario_vis'] ?? null);

        // DATAS
        $stmt->bindValue(":data_visita_vis", $data['data_visita_vis'] ?? $this->now());

        // ENF
        $stmt->bindValue(":exames_enf",        $data['exames_enf']        ?? null);
        $stmt->bindValue(":oportunidades_enf", $data['oportunidades_enf'] ?? null);
        $stmt->bindValue(":programacao_enf",   $data['programacao_enf']   ?? null);

        // PK
        $stmt->bindValue(":id_visita", (int)$data['id_visita'], PDO::PARAM_INT);

        $ok = $stmt->execute();

        if ($ok) {
            $this->message->setMessage("Visita atualizada com sucesso!", "success", "list_visita.php");
        }
        return $ok;
    }

    /** Exclui e avisa */
    public function destroy(int $id_visita): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM " . self::TABLE . " WHERE id_visita = :id_visita");
        $stmt->bindValue(":id_visita", $id_visita, PDO::PARAM_INT);
        $ok = $stmt->execute();

        if ($ok) {
            $this->message->setMessage("Visita removida com sucesso!", "success", "list_visita.php");
        }
        return $ok;
    }

    /* ======================================================
       FINDS
       ====================================================== */
    public function findAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM " . self::TABLE . " ORDER BY id_visita ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Primeira linha (historicamente usada como “geral”) */
    public function findGeral(): ?array
    {
        $stmt = $this->conn->query("SELECT * FROM " . self::TABLE . " ORDER BY id_visita ASC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id_visita): ?visita
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . self::TABLE . " WHERE id_visita = :id_visita");
        $stmt->bindValue(":id_visita", $id_visita, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->buildvisita($data) : null;
    }

    /** Retorna array de objetos visita (mantido para compatibilidade) */
    public function findByIdUpdate(int $id_visita): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . self::TABLE . " WHERE id_visita = :id_visita");
        $stmt->bindValue(":id_visita", $id_visita, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $out[] = $this->buildvisita($r);
        }
        return $out;
    }

    /** Visitas não retificadas de uma internação (mais novas primeiro) */
    public function findGeralByIntern(int $id_internacao): array
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM " . self::TABLE . "
            WHERE fk_internacao_vis = :id_internacao
              AND (retificado IS NULL OR retificado = 0)
            ORDER BY visita_no_vis DESC, data_visita_vis DESC, id_visita DESC
        ");
        $stmt->bindValue(':id_internacao', $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ======================================================
       JOINS ÚTEIS
       ====================================================== */
    public function joinVisitaInternacao(int $id_internacao): array
    {
        $sql = "SELECT 
        ac.id_internacao, ac.acoes_int, ac.data_intern_int, ac.data_visita_int, ac.rel_int,
        ac.fk_paciente_int, ac.usuario_create_int, ac.fk_hospital_int, ac.modo_internacao_int,
        ac.tipo_admissao_int, ac.especialidade_int, ac.titular_int, ac.grupo_patologia_int,
        ac.acomodacao_int, ac.fk_patologia_int, ac.fk_patologia2, ac.internado_int,
        ac.visita_no_int, ac.primeira_vis_int,
        pa.id_paciente, pa.nome_pac,
        vi.fk_internacao_vis, vi.rel_visita_vis, vi.acoes_int_vis, vi.usuario_create,
        vi.visita_auditor_prof_med, vi.visita_auditor_prof_enf, vi.visita_med_vis, vi.visita_enf_vis,
        vi.visita_no_vis, vi.fk_usuario_vis, vi.data_visita_vis, vi.id_visita,
        ho.id_hospital, ho.nome_hosp,
        u.usuario_user AS auditor_nome,
        u.reg_profissional_user AS auditor_registro  -- <--- CAMPO NOVO AQUI
    FROM tb_internacao ac
    LEFT JOIN tb_hospital ho ON ac.fk_hospital_int = ho.id_hospital
    INNER JOIN " . self::TABLE . " vi ON ac.id_internacao = vi.fk_internacao_vis
    LEFT JOIN tb_paciente pa ON ac.fk_paciente_int = pa.id_paciente
    LEFT JOIN tb_user u ON vi.fk_usuario_vis = u.id_usuario 
    WHERE vi.fk_internacao_vis = :id_internacao
      AND (vi.retificado IS NULL OR vi.retificado = 0)
    ORDER BY vi.data_visita_vis DESC, vi.id_visita DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_internacao', $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function joinVisitaInternacaoMax(int $id_internacao): array
    {
        $sql = "SELECT 
            ac.id_internacao, ac.acoes_int, ac.data_intern_int, ac.data_visita_int, ac.rel_int,
            ac.fk_paciente_int, ac.usuario_create_int, ac.fk_hospital_int, ac.modo_internacao_int,
            ac.tipo_admissao_int, ac.especialidade_int, ac.titular_int, ac.grupo_patologia_int,
            ac.acomodacao_int, ac.fk_patologia_int, ac.fk_patologia2, ac.internado_int,
            ac.visita_no_int, ac.primeira_vis_int,
            pa.id_paciente, pa.nome_pac,
            vi.fk_internacao_vis, vi.rel_visita_vis, vi.acoes_int_vis, vi.usuario_create,
            vi.visita_auditor_prof_med, vi.visita_auditor_prof_enf, vi.visita_med_vis, vi.visita_enf_vis,
            vi.visita_no_vis, vi.fk_usuario_vis, vi.data_visita_vis, vi.id_visita,
            ho.id_hospital, ho.nome_hosp
        FROM tb_internacao ac
        LEFT JOIN tb_hospital ho ON ac.fk_hospital_int = ho.id_hospital
        INNER JOIN " . self::TABLE . " vi ON ac.id_internacao = vi.fk_internacao_vis
        LEFT JOIN tb_paciente pa ON ac.fk_paciente_int = pa.id_paciente
        WHERE vi.fk_internacao_vis = :id_internacao
        ORDER BY vi.id_visita DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_internacao', $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function joinVisitaInternacaoShow(int $id_visita): array
    {
        $sql = "SELECT 
            ac.id_internacao, ac.acoes_int, ac.data_intern_int, ac.data_visita_int, ac.rel_int,
            ac.fk_paciente_int, ac.usuario_create_int, ac.fk_hospital_int, ac.modo_internacao_int,
            ac.tipo_admissao_int, ac.especialidade_int, ac.titular_int, ac.grupo_patologia_int,
            ac.acomodacao_int, ac.fk_patologia_int, ac.fk_patologia2, ac.internado_int,
            ac.visita_no_int, ac.primeira_vis_int,
            pa.id_paciente, pa.nome_pac,
            vi.fk_internacao_vis, vi.rel_visita_vis, vi.acoes_int_vis, vi.usuario_create,
            vi.visita_auditor_prof_med, vi.visita_auditor_prof_enf, vi.visita_med_vis, vi.visita_enf_vis,
            vi.visita_no_vis, vi.fk_usuario_vis, vi.data_visita_vis, vi.id_visita,
            ho.id_hospital, ho.nome_hosp
        FROM tb_internacao ac
        LEFT JOIN tb_hospital ho ON ac.fk_hospital_int = ho.id_hospital
        INNER JOIN " . self::TABLE . " vi ON ac.id_internacao = vi.fk_internacao_vis
        LEFT JOIN tb_paciente pa ON ac.fk_paciente_int = pa.id_paciente
        WHERE vi.id_visita = :id_visita
        ORDER BY ac.id_internacao ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_visita', $id_visita, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function joinVisitaShow(int $id_visita): array
    {
        return $this->joinVisitaInternacaoShow($id_visita);
    }

    /* ======================================================
       OUTROS
       ====================================================== */
    /** Marca como retificado um par (internação, visita_no) */
    public function retificarVisita(int $id_internacao, int $visita_no_vis): void
    {
        $stmt = $this->conn->prepare("
            UPDATE " . self::TABLE . "
               SET retificado = 1
             WHERE fk_internacao_vis = :id_internacao
               AND visita_no_vis     = :visita_no_vis
        ");
        $stmt->bindValue(':id_internacao', $id_internacao, PDO::PARAM_INT);
        $stmt->bindValue(':visita_no_vis', $visita_no_vis, PDO::PARAM_INT);
        $stmt->execute();
    }

    /** Último id inserido (útil p/ debug) */
    public function findLastId(): ?int
    {
        $stmt = $this->conn->query("SELECT id_visita FROM " . self::TABLE . " ORDER BY id_visita DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_visita'] : null;
    }

    /**
     * Retorna a última visita de uma internação.
     * Usa COALESCE(data_visita_vis, id_visita) para robustez caso não exista created_at na tabela.
     */
    // Substitua o método antigo por este:
    public function selectUltimaVisitaComInternacao($idInternacao, ?string $profFiltro = null): ?array
    {
        try {
            $id = (int)$idInternacao;
            if ($id <= 0) {
                return null;
            }

            $whereProf = '';
            if ($profFiltro === 'med') {
                $whereProf = " AND (v.visita_med_vis = 's' OR UPPER(v.visita_auditor_prof_med) LIKE 'MED%') ";
            } elseif ($profFiltro === 'enf') {
                $whereProf = " AND (v.visita_enf_vis = 's' OR UPPER(v.visita_auditor_prof_enf) LIKE 'ENF%') ";
            }

            $sql = "
            SELECT
                v.*,
                u.usuario_user  AS auditor_nome,
                u.email_user    AS auditor_email,
                DATEDIFF(CURDATE(), DATE(v.data_visita_vis)) AS dias_desde_ultima_visita
            FROM tb_visita v
            LEFT JOIN tb_usuario u ON u.id_usuario = v.fk_usuario_vis
            WHERE v.fk_internacao_vis = :id
              AND (v.retificado IS NULL OR v.retificado = 0)
              {$whereProf}
            ORDER BY
                COALESCE(v.data_visita_vis, v.id_visita) DESC,
                v.id_visita DESC
            LIMIT 1
        ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable $e) {
            // error_log($e->getMessage());
            return null;
        }
    }
}