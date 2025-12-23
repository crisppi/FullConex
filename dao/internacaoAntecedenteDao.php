<?php

require_once("./models/internacao_antecedente.php");
require_once("./models/message.php");

class InternacaoAntecedenteDAO
{
    private $conn;
    private $url;
    public $message;

    public function __construct(PDO $conn, $url)
    {
        $this->conn = $conn;
        $this->url = $url;
        $this->message = new Message($url);
    }
    public function buildintern_antec($data)
    {
        $intern_antec = new intern_antec();

        $intern_antec->intern_antec_ant_int = $data["intern_antec_ant_int"];
        $intern_antec->fk_internacao_ant_int = $data["fk_internacao_ant_int"];
        $intern_antec->fk_id_paciente = $data["fk_id_paciente"];
        // $intern_antec->fk_internacao_vis = $data["fk_internacao_vis"];

        return $intern_antec;
    }

    public function create(intern_antec $intern_antec)
    {
        // Check if the record already exists
        $checkStmt = $this->conn->prepare("SELECT COUNT(*) FROM tb_intern_antec WHERE intern_antec_ant_int = :intern_antec_ant_int AND fk_internacao_ant_int = :fk_internacao_ant_int");

        $checkStmt->bindParam(":intern_antec_ant_int", $intern_antec->intern_antec_ant_int);
        $checkStmt->bindParam(":fk_internacao_ant_int", $intern_antec->fk_internacao_ant_int);

        $checkStmt->execute();

        $count = $checkStmt->fetchColumn();

        if ($count == 0) {
            // If the record does not exist, insert it
            $stmt = $this->conn->prepare("INSERT INTO tb_intern_antec (
            intern_antec_ant_int,
            fk_internacao_ant_int,
            fk_id_paciente
        ) VALUES (
            :intern_antec_ant_int,
            :fk_internacao_ant_int,
            :fk_id_paciente
        )");

            // Bind all parameters
            $stmt->bindParam(":intern_antec_ant_int", $intern_antec->intern_antec_ant_int);
            $stmt->bindParam(":fk_internacao_ant_int", $intern_antec->fk_internacao_ant_int);
            $stmt->bindParam(":fk_id_paciente", $intern_antec->fk_id_paciente);

            // Execute the query
            $stmt->execute();
        } else {
            // Optional: Log or handle the case when the record already exists
            echo "Record already exists. No insertion was performed.";
        }
    }

    /**
     * Retorna todos os antecedentes vinculados à internação informada.
     * Inclui o nome do antecedente para facilitar o uso em telas.
     */
    public function findByInternacao(int $internacaoId): array
    {
        if ($internacaoId <= 0) {
            return [];
        }

        $stmt = $this->conn->prepare(
            "SELECT ia.*, a.antecedente_ant 
             FROM tb_intern_antec AS ia
             LEFT JOIN tb_antecedente AS a ON a.id_antecedente = ia.intern_antec_ant_int
             WHERE ia.fk_internacao_ant_int = :internacaoId
             ORDER BY a.antecedente_ant ASC"
        );

        $stmt->bindValue(":internacaoId", $internacaoId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

}
