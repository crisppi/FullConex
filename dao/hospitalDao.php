<?php

require_once("./models/hospital.php");
require_once("./models/message.php");

// Review DAO
require_once("dao/hospitalDao.php");

class HospitalDAO implements HospitalDAOInterface
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

    public function buildHospital($data)
    {
        $hospital = new Hospital();

        $hospital->id_hospital = $data["id_hospital"];
        $hospital->nome_hosp = $data["nome_hosp"];
        $hospital->endereco_hosp = $data["endereco_hosp"];
        $hospital->numero_hosp = $data["numero_hosp"];
        $hospital->cidade_hosp = $data["cidade_hosp"];
        $hospital->estado_hosp = $data["estado_hosp"];
        $hospital->cnpj_hosp = $data["cnpj_hosp"];
        $hospital->email01_hosp = $data["email01_hosp"];
        $hospital->email02_hosp = $data["email02_hosp"];
        $hospital->telefone01_hosp = $data["telefone01_hosp"];
        $hospital->telefone02_hosp = $data["telefone02_hosp"];
        $hospital->bairro_hosp = $data["bairro_hosp"];
        $hospital->fk_usuario_hosp = $data["fk_usuario_hosp"];
        $hospital->usuario_create_hosp = $data["usuario_create_hosp"];
        $hospital->data_create_hosp = $data["data_create_hosp"];
        $hospital->longitude_hosp = $data["longitude_hosp"];
        $hospital->latitude_hosp = $data["latitude_hosp"];
        $hospital->coordenador_medico_hosp = $data["coordenador_medico_hosp"];
        $hospital->diretor_hosp = $data["diretor_hosp"];
        $hospital->ativo_hosp = $data["ativo_hosp"];
        $hospital->coordenador_fat_hosp = $data["coordenador_fat_hosp"];
        $hospital->logo_hosp = $data["logo_hosp"];
        $hospital->cep_hosp = $data["cep_hosp"];
        $hospital->deletado_hosp = $data["deletado_hosp"];

        return $hospital;
    }

    public function findAll()
    {
        $hospital = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_hospital WHERE id_hospital > 1
        ORDER BY id_hospital asc");

        $stmt->execute();

        $hospital = $stmt->fetchAll();
        return $hospital;
    }


    public function findByHosp($pesquisa_nome)
    {

        $hospital = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_hospital
                                    WHERE nome_hosp LIKE :nome_hosp ");

        $stmt->bindValue(":nome_hosp", '%' . $pesquisa_nome . '%');

        $stmt->execute();

        $hospital = $stmt->fetchAll();
        return $hospital;
    }

    public function gethospital()
    {

        $hospital = [];

        $stmt = $this->conn->query("SELECT * FROM tb_hospital ORDER BY id_hospital asc");

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $hospitalArray = $stmt->fetchAll();

            foreach ($hospitalArray as $hospital) {
                $hospital[] = $this->buildHospital($hospital);
            }
        }

        return $hospital;
    }

    public function gethospitalByNome($nome)
    {

        $hospital = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_hospital
                                    WHERE nome_hosp = :nome_hosp
                                    ORDER BY id_hospital asc");

        $stmt->bindParam(":nome_hosp", $nome_hosp);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $hospitalArray = $stmt->fetchAll();

            foreach ($hospitalArray as $hospital) {
                $hospital[] = $this->buildHospital($hospital);
            }
        }

        return $hospital;
    }

    public function findById($id_hospital)
    {
        $hospital = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_hospital
                                    WHERE id_hospital = :id_hospital");

        $stmt->bindParam(":id_hospital", $id_hospital);
        $stmt->execute();

        $data = $stmt->fetch();
        $hospital = $this->buildHospital($data);

        return $hospital;
    }

    public function create(Hospital $hospital)
    {

        $stmt = $this->conn->prepare("INSERT INTO tb_hospital (
        nome_hosp, 
        ativo_hosp, 
        endereco_hosp, 
        numero_hosp, 
        bairro_hosp,
        cidade_hosp,
        estado_hosp,
        email01_hosp, 
        email02_hosp, 
        cnpj_hosp, 
        telefone01_hosp, 
        telefone02_hosp, 
        fk_usuario_hosp,
        data_create_hosp,
        usuario_create_hosp,
        latitude_hosp,
        longitude_hosp,
        coordenador_medico_hosp,
        diretor_hosp,
        coordenador_fat_hosp,
        logo_hosp,
        deletado_hosp,
        cep_hosp
      ) VALUES (
        :nome_hosp, 
        :ativo_hosp, 
        :endereco_hosp, 
        :numero_hosp, 
        :bairro_hosp,
        :cidade_hosp,
        :estado_hosp,
        :email01_hosp, 
        :email02_hosp, 
        :cnpj_hosp, 
        :telefone01_hosp, 
        :telefone02_hosp, 
        :fk_usuario_hosp,
        :data_create_hosp,
        :usuario_create_hosp,
        :latitude_hosp,
        :longitude_hosp,
        :coordenador_medico_hosp,
        :diretor_hosp,
        :coordenador_fat_hosp,
        :logo_hosp,
        :deletado_hosp,
        :cep_hosp
     )");

        $stmt->bindParam(":nome_hosp", $hospital->nome_hosp);
        $stmt->bindParam(":ativo_hosp", $hospital->ativo_hosp);
        $stmt->bindParam(":endereco_hosp", $hospital->endereco_hosp);
        $stmt->bindParam(":numero_hosp", $hospital->numero_hosp);
        $stmt->bindParam(":bairro_hosp", $hospital->bairro_hosp);
        $stmt->bindParam(":email01_hosp", $hospital->email01_hosp);
        $stmt->bindParam(":email02_hosp", $hospital->email02_hosp);
        $stmt->bindParam(":cnpj_hosp", $hospital->cnpj_hosp);
        $stmt->bindParam(":telefone01_hosp", $hospital->telefone01_hosp);
        $stmt->bindParam(":telefone02_hosp", $hospital->telefone02_hosp);
        $stmt->bindParam(":cidade_hosp", $hospital->cidade_hosp);
        $stmt->bindParam(":estado_hosp", $hospital->estado_hosp);
        $stmt->bindParam(":fk_usuario_hosp", $hospital->fk_usuario_hosp);
        $stmt->bindParam(":usuario_create_hosp", $hospital->usuario_create_hosp);
        $stmt->bindParam(":data_create_hosp", $hospital->data_create_hosp);
        $stmt->bindParam(":latitude_hosp", $hospital->latitude_hosp);
        $stmt->bindParam(":longitude_hosp", $hospital->longitude_hosp);
        $stmt->bindParam(":coordenador_medico_hosp", $hospital->coordenador_medico_hosp);
        $stmt->bindParam(":diretor_hosp", $hospital->diretor_hosp);
        $stmt->bindParam(":coordenador_fat_hosp", $hospital->coordenador_fat_hosp);
        $stmt->bindParam(":logo_hosp", $hospital->logo_hosp);
        $stmt->bindParam(":deletado_hosp", $hospital->deletado_hosp);
        $stmt->bindParam(":cep_hosp", $hospital->cep_hosp);

        $stmt->execute();

        // Mensagem de sucesso por adicionar filme
        $this->message->setMessage("hospital adicionado com sucesso!", "success", "list_hospital.php");
    }

    public function update(Hospital $hospital)
    {

        $stmt = $this->conn->prepare("UPDATE tb_hospital SET
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

        $stmt->bindParam(":nome_hosp", $hospital->nome_hosp);
        $stmt->bindParam(":ativo_hosp", $hospital->ativo_hosp);
        $stmt->bindParam(":endereco_hosp", $hospital->endereco_hosp);
        $stmt->bindParam(":numero_hosp", $hospital->numero_hosp);
        $stmt->bindParam(":email01_hosp", $hospital->email01_hosp);
        $stmt->bindParam(":email02_hosp", $hospital->email02_hosp);
        $stmt->bindParam(":cnpj_hosp", $hospital->cnpj_hosp);
        $stmt->bindParam(":telefone01_hosp", $hospital->telefone01_hosp);
        $stmt->bindParam(":telefone02_hosp", $hospital->telefone02_hosp);
        $stmt->bindParam(":cidade_hosp", $hospital->cidade_hosp);
        $stmt->bindParam(":bairro_hosp", $hospital->bairro_hosp);
        $stmt->bindParam(":estado_hosp", $hospital->estado_hosp);
        $stmt->bindParam(":latitude_hosp", $hospital->latitude_hosp);
        $stmt->bindParam(":longitude_hosp", $hospital->longitude_hosp);
        $stmt->bindParam(":coordenador_medico_hosp", $hospital->coordenador_medico_hosp);
        $stmt->bindParam(":diretor_hosp", $hospital->diretor_hosp);
        $stmt->bindParam(":coordenador_fat_hosp", $hospital->coordenador_fat_hosp);
        $stmt->bindParam(":logo_hosp", $hospital->logo_hosp);
        $stmt->bindParam(":cep_hosp", $hospital->cep_hosp);

        $stmt->bindParam(":id_hospital", $hospital->id_hospital);
        $stmt->execute();

        // Mensagem de sucesso por editar hospital
        $this->message->setMessage("hospital atualizado com sucesso!", "success", "list_hospital.php");
    }


    public function deletarUpdate(Hospital $hospital)
    {
        $deletado = "s";
        $stmt = $this->conn->prepare("UPDATE tb_hospital SET
        
        deletado_hosp = :deletado_hosp

        WHERE id_hospital = :id_hospital 
      ");

        $stmt->bindParam(":deletado_hosp", $hospital->deletado_hosp);

        $stmt->bindParam(":id_hospital", $hospital->id_hospital);
        $stmt->execute();

        // Mensagem de sucesso por editar hospital
        $this->message->setMessage("hospital atualizado com sucesso!", "success", "list_hospital.php");
    }


    public function destroy($id_hospital)
    {
        $stmt = $this->conn->prepare("DELETE FROM tb_hospital WHERE id_hospital = :id_hospital");

        $stmt->bindParam(":id_hospital", $id_hospital);

        $stmt->execute();

        // Mensagem de sucesso por remover filme
        $this->message->setMessage("hospital removido com sucesso!", "success", "list_hospital.php");
    }


    public function findGeral()
    {

        $hospital = [];

        $stmt = $this->conn->query("SELECT * FROM tb_hospital WHERE id_hospital > 1 AND deletado_hosp <> 's' ORDER BY nome_hosp asc");

        $stmt->execute();

        $hospital = $stmt->fetchAll();

        return $hospital;
    }

    public function selectAllhospital($where = null, $order = null, $limit = null) // function para pesquisar apenas os hospitais que nao foram deletados 
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $where = $where . 'AND deletado_hosp <> "s"'; // filtrar apenas os hospitais que nao foram deletados
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        //MONTA A QUERY
        $query = $this->conn->query('SELECT * FROM tb_hospital ' . $where . ' ' . $order . ' ' . $limit);

        $query->execute();

        $hospital = $query->fetchAll();

        return $hospital;
    }

    public function selectAllhospitalComDeletados($where = null, $order = null, $limit = null) // function para pesquisar todos hospitaism inclusive os deletados
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        //MONTA A QUERY
        $query = $this->conn->query('SELECT * FROM tb_hospital ' . $where . ' ' . $order . ' ' . $limit);

        $query->execute();

        $hospital = $query->fetchAll();

        return $hospital;
    }
    public function QtdHospital($where = null, $order = null, $limite = null)
    {
        $hospital = [];
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limite = strlen($limite) ? 'LIMIT ' . $limite : '';

        $stmt = $this->conn->query('SELECT * ,COUNT(id_hospital) as qtd FROM tb_hospital ' . $where . ' ' . $order . ' ' . $limite);

        $stmt->execute();

        $QtdTotalHosp = $stmt->fetch();

        return $QtdTotalHosp;
    }
}