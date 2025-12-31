<?php

require_once("./models/hospital.php");
require_once("./models/message.php");

class HospitalDAO implements HospitalDAOInterface
{
    private $conn; // sem tipo
    private $url;  // sem tipo
    public $message;

    public function __construct(PDO $conn, $url)
    {
        $this->conn = $conn;
        $this->url  = $url;
        $this->message = new Message($url);

        // Força fetch associativo neste DAO
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function buildHospital($data)
    {
        $hospital = new Hospital();

        $hospital->id_hospital               = isset($data["id_hospital"]) ? $data["id_hospital"] : null;
        $hospital->nome_hosp                 = isset($data["nome_hosp"]) ? $data["nome_hosp"] : null;
        $hospital->endereco_hosp             = isset($data["endereco_hosp"]) ? $data["endereco_hosp"] : null;
        $hospital->numero_hosp               = isset($data["numero_hosp"]) ? $data["numero_hosp"] : null;
        $hospital->cidade_hosp               = isset($data["cidade_hosp"]) ? $data["cidade_hosp"] : null;
        $hospital->estado_hosp               = isset($data["estado_hosp"]) ? $data["estado_hosp"] : null;
        $hospital->cnpj_hosp                 = isset($data["cnpj_hosp"]) ? $data["cnpj_hosp"] : null;
        $hospital->email01_hosp              = isset($data["email01_hosp"]) ? $data["email01_hosp"] : null;
        $hospital->email02_hosp              = isset($data["email02_hosp"]) ? $data["email02_hosp"] : null;
        $hospital->telefone01_hosp           = isset($data["telefone01_hosp"]) ? $data["telefone01_hosp"] : null;
        $hospital->telefone02_hosp           = isset($data["telefone02_hosp"]) ? $data["telefone02_hosp"] : null;
        $hospital->bairro_hosp               = isset($data["bairro_hosp"]) ? $data["bairro_hosp"] : null;
        $hospital->fk_usuario_hosp           = isset($data["fk_usuario_hosp"]) ? $data["fk_usuario_hosp"] : null;
        $hospital->usuario_create_hosp       = isset($data["usuario_create_hosp"]) ? $data["usuario_create_hosp"] : null;
        $hospital->data_create_hosp          = isset($data["data_create_hosp"]) ? $data["data_create_hosp"] : null;
        $hospital->longitude_hosp            = isset($data["longitude_hosp"]) ? $data["longitude_hosp"] : null;
        $hospital->latitude_hosp             = isset($data["latitude_hosp"]) ? $data["latitude_hosp"] : null;
        $hospital->coordenador_medico_hosp   = isset($data["coordenador_medico_hosp"]) ? $data["coordenador_medico_hosp"] : null;
        $hospital->diretor_hosp              = isset($data["diretor_hosp"]) ? $data["diretor_hosp"] : null;
        $hospital->ativo_hosp                = isset($data["ativo_hosp"]) ? $data["ativo_hosp"] : null;
        $hospital->coordenador_fat_hosp      = isset($data["coordenador_fat_hosp"]) ? $data["coordenador_fat_hosp"] : null;
        $hospital->logo_hosp                 = isset($data["logo_hosp"]) ? $data["logo_hosp"] : null;
        $hospital->cep_hosp                  = isset($data["cep_hosp"]) ? $data["cep_hosp"] : null;
        $hospital->deletado_hosp             = isset($data["deletado_hosp"]) ? $data["deletado_hosp"] : null;

        return $hospital;
    }

    /* ================== READS (arrays associativos) ================== */

    public function findAll()
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM tb_hospital
            WHERE id_hospital > 1
            ORDER BY id_hospital DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByHosp($pesquisa_nome)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM tb_hospital
            WHERE nome_hosp LIKE :nome_hosp
            ORDER BY nome_hosp ASC
        ");
        $stmt->bindValue(":nome_hosp", '%' . $pesquisa_nome . '%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================== READS que retornam MODELS ================== */

    public function gethospital()
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM tb_hospital
            ORDER BY id_hospital DESC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->buildHospital($row);
        }
        return $out;
    }

    public function gethospitalByNome($nome_hosp)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM tb_hospital
            WHERE nome_hosp = :nome_hosp
            ORDER BY id_hospital DESC
        ");
        $stmt->bindValue(":nome_hosp", $nome_hosp);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->buildHospital($row);
        }
        return $out;
    }

    public function findById($id_hospital)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM tb_hospital
            WHERE id_hospital = :id_hospital
        ");
        $stmt->bindValue(":id_hospital", $id_hospital, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->buildHospital($data) : null;
    }

    /* ================== CREATE / UPDATE / DELETE ================== */

    public function create(Hospital $hospital)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO tb_hospital (
                nome_hosp, ativo_hosp, endereco_hosp, numero_hosp, bairro_hosp,
                cidade_hosp, estado_hosp, email01_hosp, email02_hosp, cnpj_hosp,
                telefone01_hosp, telefone02_hosp, fk_usuario_hosp, data_create_hosp,
                usuario_create_hosp, latitude_hosp, longitude_hosp, coordenador_medico_hosp,
                diretor_hosp, coordenador_fat_hosp, logo_hosp, deletado_hosp, cep_hosp
            ) VALUES (
                :nome_hosp, :ativo_hosp, :endereco_hosp, :numero_hosp, :bairro_hosp,
                :cidade_hosp, :estado_hosp, :email01_hosp, :email02_hosp, :cnpj_hosp,
                :telefone01_hosp, :telefone02_hosp, :fk_usuario_hosp, :data_create_hosp,
                :usuario_create_hosp, :latitude_hosp, :longitude_hosp, :coordenador_medico_hosp,
                :diretor_hosp, :coordenador_fat_hosp, :logo_hosp, :deletado_hosp, :cep_hosp
            )
        ");

        $stmt->bindValue(":nome_hosp", $hospital->nome_hosp);
        $stmt->bindValue(":ativo_hosp", $hospital->ativo_hosp);
        $stmt->bindValue(":endereco_hosp", $hospital->endereco_hosp);
        $stmt->bindValue(":numero_hosp", $hospital->numero_hosp);
        $stmt->bindValue(":bairro_hosp", $hospital->bairro_hosp);
        $stmt->bindValue(":cidade_hosp", $hospital->cidade_hosp);
        $stmt->bindValue(":estado_hosp", $hospital->estado_hosp);
        $stmt->bindValue(":email01_hosp", $hospital->email01_hosp);
        $stmt->bindValue(":email02_hosp", $hospital->email02_hosp);
        $stmt->bindValue(":cnpj_hosp", $hospital->cnpj_hosp);
        $stmt->bindValue(":telefone01_hosp", $hospital->telefone01_hosp);
        $stmt->bindValue(":telefone02_hosp", $hospital->telefone02_hosp);
        $stmt->bindValue(":fk_usuario_hosp", $hospital->fk_usuario_hosp);
        $stmt->bindValue(":data_create_hosp", $hospital->data_create_hosp);
        $stmt->bindValue(":usuario_create_hosp", $hospital->usuario_create_hosp);
        $stmt->bindValue(":latitude_hosp", $hospital->latitude_hosp);
        $stmt->bindValue(":longitude_hosp", $hospital->longitude_hosp);
        $stmt->bindValue(":coordenador_medico_hosp", $hospital->coordenador_medico_hosp);
        $stmt->bindValue(":diretor_hosp", $hospital->diretor_hosp);
        $stmt->bindValue(":coordenador_fat_hosp", $hospital->coordenador_fat_hosp);
        $stmt->bindValue(":logo_hosp", $hospital->logo_hosp);
        $stmt->bindValue(":deletado_hosp", $hospital->deletado_hosp);
        $stmt->bindValue(":cep_hosp", $hospital->cep_hosp);

        $stmt->execute();

        $this->message->setMessage("hospital adicionado com sucesso!", "success", "hospitais");
    }

    public function update(Hospital $hospital)
    {
        $stmt = $this->conn->prepare("
            UPDATE tb_hospital SET
                nome_hosp = :nome_hosp,
                ativo_hosp = :ativo_hosp,
                endereco_hosp = :endereco_hosp,
                numero_hosp = :numero_hosp,
                email01_hosp = :email01_hosp,
                email02_hosp = :email02_hosp,
                cnpj_hosp = :cnpj_hosp,
                telefone01_hosp = :telefone01_hosp,
                telefone02_hosp = :telefone02_hosp,
                cidade_hosp = :cidade_hosp,
                bairro_hosp = :bairro_hosp,
                latitude_hosp = :latitude_hosp,
                longitude_hosp = :longitude_hosp,
                coordenador_medico_hosp = :coordenador_medico_hosp,
                diretor_hosp = :diretor_hosp,
                estado_hosp = :estado_hosp,
                coordenador_fat_hosp = :coordenador_fat_hosp,
                logo_hosp = :logo_hosp,
                cep_hosp = :cep_hosp
            WHERE id_hospital = :id_hospital
        ");

        $stmt->bindValue(":nome_hosp", $hospital->nome_hosp);
        $stmt->bindValue(":ativo_hosp", $hospital->ativo_hosp);
        $stmt->bindValue(":endereco_hosp", $hospital->endereco_hosp);
        $stmt->bindValue(":numero_hosp", $hospital->numero_hosp);
        $stmt->bindValue(":email01_hosp", $hospital->email01_hosp);
        $stmt->bindValue(":email02_hosp", $hospital->email02_hosp);
        $stmt->bindValue(":cnpj_hosp", $hospital->cnpj_hosp);
        $stmt->bindValue(":telefone01_hosp", $hospital->telefone01_hosp);
        $stmt->bindValue(":telefone02_hosp", $hospital->telefone02_hosp);
        $stmt->bindValue(":cidade_hosp", $hospital->cidade_hosp);
        $stmt->bindValue(":bairro_hosp", $hospital->bairro_hosp);
        $stmt->bindValue(":estado_hosp", $hospital->estado_hosp);
        $stmt->bindValue(":latitude_hosp", $hospital->latitude_hosp);
        $stmt->bindValue(":longitude_hosp", $hospital->longitude_hosp);
        $stmt->bindValue(":coordenador_medico_hosp", $hospital->coordenador_medico_hosp);
        $stmt->bindValue(":diretor_hosp", $hospital->diretor_hosp);
        $stmt->bindValue(":coordenador_fat_hosp", $hospital->coordenador_fat_hosp);
        $stmt->bindValue(":logo_hosp", $hospital->logo_hosp);
        $stmt->bindValue(":cep_hosp", $hospital->cep_hosp);
        $stmt->bindValue(":id_hospital", $hospital->id_hospital, PDO::PARAM_INT);

        $stmt->execute();

        $this->message->setMessage("hospital atualizado com sucesso!", "success", "hospitais");
    }

    public function deletarUpdate(Hospital $hospital)
    {
        $stmt = $this->conn->prepare("
            UPDATE tb_hospital SET
                deletado_hosp = :deletado_hosp
            WHERE id_hospital = :id_hospital
        ");
        $stmt->bindValue(":deletado_hosp", $hospital->deletado_hosp);
        $stmt->bindValue(":id_hospital", $hospital->id_hospital, PDO::PARAM_INT);
        $stmt->execute();

        $this->message->setMessage("hospital atualizado com sucesso!", "success", "hospitais");
    }

    public function destroy($id_hospital)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM tb_hospital
            WHERE id_hospital = :id_hospital
        ");
        $stmt->bindValue(":id_hospital", $id_hospital, PDO::PARAM_INT);
        $stmt->execute();

        $this->message->setMessage("hospital removido com sucesso!", "success", "hospitais");
    }

    /* ============== LISTAGENS AVANÇADAS (associativas) ============== */

    public function findGeral()
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM tb_hospital
            WHERE id_hospital > 1 AND deletado_hosp <> 's'
            ORDER BY nome_hosp ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectAllhospital($where = null, $order = null, $limit = null)
    {
        $conds = array();
        if (strlen((string)$where)) $conds[] = $where;
        $conds[] = "deletado_hosp <> 's'";

        $sql = "SELECT * FROM tb_hospital";
        if (!empty($conds))  $sql .= " WHERE " . implode(' AND ', $conds);
        if (strlen((string)$order)) $sql .= " ORDER BY " . $order;
        if (strlen((string)$limit)) $sql .= " LIMIT " . $limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectAllhospitalComDeletados($where = null, $order = null, $limit = null)
    {
        $sql = "SELECT * FROM tb_hospital";
        if (strlen((string)$where)) $sql .= " WHERE " . $where;
        if (strlen((string)$order)) $sql .= " ORDER BY " . $order;
        if (strlen((string)$limit)) $sql .= " LIMIT " . $limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function QtdHospital($where = null, $order = null, $limite = null)
    {
        $sql = "SELECT COUNT(id_hospital) AS qtd FROM tb_hospital";
        if (strlen((string)$where))  $sql .= " WHERE " . $where;
        if (strlen((string)$order))  $sql .= " ORDER BY " . $order;
        if (strlen((string)$limite)) $sql .= " LIMIT " . $limite;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row : array('qtd' => 0);
    }
}