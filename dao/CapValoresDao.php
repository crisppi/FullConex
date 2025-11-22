<?php

require_once __DIR__ . '/../models/CapValores.php';

class CapValoresDAO
{
    private PDO $conn;
    private string $table = 'tb_cap_valores';

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function findById(int $id_valor): ?array
    {
        $sql = "
            SELECT cv.*, ca.*, ac.*, pa.nome_pac, ho.nome_hosp
            FROM {$this->table} cv
            LEFT JOIN tb_capeante ca   ON ca.id_capeante   = cv.fk_capeante
            LEFT JOIN tb_internacao ac ON ac.id_internacao = ca.fk_int_capeante
            LEFT JOIN tb_paciente pa   ON pa.id_paciente   = ac.fk_paciente_int
            LEFT JOIN tb_hospital ho   ON ho.id_hospital   = ac.fk_hospital_int
            WHERE cv.id_valor = :id
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id_valor, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByCapeante(int $fk_capeante): ?array
    {
        $sql = "
            SELECT cv.*, ca.*, ac.*, pa.nome_pac, ho.nome_hosp
            FROM {$this->table} cv
            LEFT JOIN tb_capeante ca   ON ca.id_capeante   = cv.fk_capeante
            LEFT JOIN tb_internacao ac ON ac.id_internacao = ca.fk_int_capeante
            LEFT JOIN tb_paciente pa   ON pa.id_paciente   = ac.fk_paciente_int
            LEFT JOIN tb_hospital ho   ON ho.id_hospital   = ac.fk_hospital_int
            WHERE cv.fk_capeante = :fk
            ORDER BY cv.id_valor DESC
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':fk', $fk_capeante, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function ensureByCapeante(int $fk_capeante): int
    {
        $existing = $this->findByCapeante($fk_capeante);
        if ($existing && isset($existing['id_valor'])) {
            $this->touch((int)$existing['id_valor']);
            return (int)$existing['id_valor'];
        }

        $nextId = (int)$this->conn->query("SELECT COALESCE(MAX(id_valor), 0) + 1 FROM {$this->table}")->fetchColumn();
        $sql = "INSERT INTO {$this->table} (id_valor, fk_capeante, criado_em, atualizado_em)
                VALUES (:id, :fk, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $nextId, PDO::PARAM_INT);
        $stmt->bindValue(':fk', $fk_capeante, PDO::PARAM_INT);
        $stmt->execute();
        return $nextId;
    }

    public function touch(int $id_valor, ?int $fk_capeante = null): void
    {
        $sql = "UPDATE {$this->table}
                SET atualizado_em = CURRENT_TIMESTAMP"
                . ($fk_capeante !== null ? ", fk_capeante = :fk" : "")
                . " WHERE id_valor = :id";

        $stmt = $this->conn->prepare($sql);
        if ($fk_capeante !== null) {
            $stmt->bindValue(':fk', $fk_capeante, PDO::PARAM_INT);
        }
        $stmt->bindValue(':id', $id_valor, PDO::PARAM_INT);
        $stmt->execute();
    }
}
