<?php

require_once("./models/message.php");

// Review DAO
require_once("./models/hospitalUser.php");
require_once("dao/hospitalUserDao.php");

class hospitalUserDAO implements hospitalUserDAOInterface
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

    public function buildhospitalUser($data)
    {
        $hospitalUser = new hospitalUser();

        $hospitalUser->id_hospitalUser = $data["id_hospitalUser"];
        $hospitalUser->fk_usuario_hosp = $data["fk_usuario_hosp"];
        $hospitalUser->fk_hospital_user = $data["fk_hospital_user"];

        return $hospitalUser;
    }

    public function findAll()
    {
        $hospitalUser = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_hospitalUser
        ORDER BY id_hospitalUser asc");

        $stmt->execute();

        $hospitalUser = $stmt->fetchAll();
        return $hospitalUser;
    }


    public function findByHosp($pesquisa_nome)
    {

        $hospitalUser = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_hospitalUser
                                    WHERE nome_hosp LIKE :nome_hosp ");

        $stmt->bindValue(":nome_hosp", '%' . $pesquisa_nome . '%');

        $stmt->execute();

        $hospitalUser = $stmt->fetchAll();
        return $hospitalUser;
    }

    public function gethospitalUser()
    {

        $hospitalUser = [];

        $stmt = $this->conn->query("SELECT * FROM tb_hospitalUser ORDER BY id_hospitalUser asc");

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $hospitalUserArray = $stmt->fetchAll();

            foreach ($hospitalUserArray as $hospitalUser) {
                $hospitalUser[] = $this->buildhospitalUser($hospitalUser);
            }
        }

        return $hospitalUser;
    }

    public function findById($id_hospitalUser)
    {
        $hospitalUser = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_hospitalUser  
        
        WHERE id_hospitalUser = $id_hospitalUser");

        $stmt->execute();

        $hospitalUser = $stmt->fetch();
        // print_r($hospitalUser);
        // exit;
        // $hospitalUser = $this->buildhospitalUser($stmt);
        // print_r($hospitalUser);
        // exit;

        return $hospitalUser;
    }

    public function create(hospitalUser $hospitalUser)
    {

        $stmt = $this->conn->prepare("INSERT INTO tb_hospitalUser (
        fk_usuario_hosp, 
        fk_hospital_user 
        
      ) VALUES (
        :fk_usuario_hosp, 
        :fk_hospital_user
        
     )");

        $stmt->bindParam(":fk_usuario_hosp", $hospitalUser->fk_usuario_hosp);
        $stmt->bindParam(":fk_hospital_user", $hospitalUser->fk_hospital_user);

        $stmt->execute();

        // Mensagem de sucesso por adicionar filme
        $this->message->setMessage("hospitalUser adicionado com sucesso!", "success", "list_hospitalUser.php");
    }

    public function update(hospitalUser $hospitalUser)
    {

        $stmt = $this->conn->prepare("UPDATE tb_hospitalUser SET
        fk_usuario_hosp = :fk_usuario_hosp,
        fk_hospital_user = :fk_hospital_user

        WHERE id_hospitalUser = :id_hospitalUser 
      ");

        $stmt->bindParam(":fk_usuario_hosp", $hospitalUser->fk_usuario_hosp);
        $stmt->bindParam(":fk_hospital_user", $hospitalUser->fk_hospital_user);

        $stmt->bindParam(":id_hospitalUser", $hospitalUser->id_hospitalUser);

        $stmt->execute();

        // Mensagem de sucesso por editar hospitalUser
        $this->message->setMessage("hospitalUser atualizado com sucesso!", "success", "list_hospitalUser.php");
    }

    public function destroy($id_hospitalUser)
    {
        $stmt = $this->conn->prepare("DELETE FROM tb_hospitalUser WHERE id_hospitalUser = :id_hospitalUser");

        $stmt->bindParam(":id_hospitalUser", $id_hospitalUser);

        $stmt->execute();

        // Mensagem de sucesso por remover filme
        $this->message->setMessage("hospitalUser removido com sucesso!", "success", "list_hospitalUser.php");
    }


    public function findGeral()
    {

        $hospitalUser = [];

        $stmt = $this->conn->query("SELECT * FROM tb_hospitalUser ORDER BY id_hospitalUser asc");

        $stmt->execute();

        $hospitalUser = $stmt->fetchAll();

        return $hospitalUser;
    }

    public function selectAllhospitalUser($where = null, $order = null, $limit = null)
    {
        //DADOS DA QUERY
        $where  = !empty($where)  ? 'WHERE '    . $where  : '';
        $order  = !empty($order)  ? 'ORDER BY ' . $order  : '';
        $limit  = !empty($limit)  ? 'LIMIT '    . $limit  : '';


        //MONTA A QUERY
        $query = $this->conn->query('SELECT 
        
        hu.id_hospitalUser,
        hu.fk_usuario_hosp,
        hu.fk_hospital_user,
        ho.id_hospital,
        us.id_usuario,
        us.usuario_user,
        us.email_user,
        us.cargo_user,
        us.nivel_user,
        us.ativo_user,
        ho.nome_hosp 
        
        FROM tb_hospitalUser hu 

        left JOIN tb_hospital as ho On  
        hu.fk_hospital_user = ho.id_hospital
        
		left JOIN tb_user as us On  
        hu.fk_usuario_hosp = us.id_usuario
        
         ' . $where . ' ' . $order . ' ' . $limit);

        $query->execute();

        $hospitalUser = $query->fetchAll();

        return $hospitalUser;
    }


    public function QtdhospitalUser($where = null, $order = null, $limite = null)
    {
        $hospitalUser = [];
        //DADOS DA QUERY
        $where  = !empty($where)  ? 'WHERE '    . $where  : '';
        $order  = !empty($order)  ? 'ORDER BY ' . $order  : '';
        $limit  = !empty($limit)  ? 'LIMIT '    . $limit  : '';


        $stmt = $this->conn->query('SELECT hu.id_hospitalUser,
        hu.fk_usuario_hosp,
        hu.fk_hospital_user,
        ho.id_hospital,
        us.id_usuario,
        us.usuario_user,
        us.cargo_user,
        ho.nome_hosp,
        COUNT(id_hospitalUser) as qtd
        
        FROM tb_hospitalUser hu 

        left JOIN tb_hospital as ho On  
        hu.fk_hospital_user = ho.id_hospital
        
		left JOIN tb_user as us On  
        hu.fk_usuario_hosp = us.id_usuario ' . $where . ' ' . $order . ' ' . $limite);

        $stmt->execute();

        $QtdTotalHosp = $stmt->fetch();

        return $QtdTotalHosp;
    }
    public function selecHospUser($id_usuario)
    {
        //MONTA A QUERY
        $query = $this->conn->query('SELECT 
        
        hu.id_hospitalUser,
        hu.fk_usuario_hosp,
        hu.fk_hospital_user,
        ho.id_hospital,
        us.id_usuario,
        us.usuario_user,
        us.cargo_user,
        ho.nome_hosp 
        
        FROM tb_hospitalUser hu 

        left JOIN tb_hospital as ho On  
        hu.fk_hospital_user = ho.id_hospital
        
		left JOIN tb_user as us On  
        hu.fk_usuario_hosp = us.id_usuario

        WHERE id_hospital_user = $id_usuario
        
        ');

        $query->execute();

        $hospitalUser = $query->fetchAll();

        return $hospitalUser;
    }
    public function joinHospitalUser($id_user)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                hu.id_hospitalUser,
                hu.fk_usuario_hosp,
                hu.fk_hospital_user,
                ho.nome_hosp,
                hu.fk_hospital_user as fk_hospital_int,
                hu.fk_hospital_user as id_hospital,
                us.usuario_user,
                us.email_user,
                us.id_usuario,
                us.cargo_user
            FROM tb_hospitalUser hu
            LEFT JOIN tb_hospital ho ON hu.fk_hospital_user = ho.id_hospital
            LEFT JOIN tb_user us ON hu.fk_usuario_hosp = us.id_usuario
            WHERE hu.fk_usuario_hosp = :id_usuario
        ");

        $stmt->bindValue(":id_usuario", $id_user, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function joinHospitalUserAll()

    {
        $stmt = $this->conn->query("SELECT 
        
        hu.id_hospitalUser,
        hu.fk_usuario_hosp,
        hu.fk_hospital_user,
        ho.id_hospital,
        us.id_usuario,
        us.usuario_user,
        ho.nome_hosp 
        
        FROM tb_hospitalUser hu 

        left JOIN tb_hospital as ho On  
        hu.fk_hospital_user = ho.id_hospital
        
		left JOIN tb_user as us On  
        hu.fk_usuario_hosp = us.id_usuario
                  ");

        $stmt->execute();

        $hospitalUserJoin = $stmt->fetchAll();
        return $hospitalUserJoin;
    }

    public function findByIdUser($id_hospitalUser)
    {
        $hospitalUser = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_hospitalUser
                                    WHERE id_hospitalUser = :id_hospitalUser");
        $stmt->bindParam(":id_hospitalUser", $id_hospitalUser);
        $stmt->execute();

        $data = $stmt->fetch();
        // var_dump($data);
        $hospitalUser = $this->buildhospitalUser($data);

        return $hospitalUser;
    }
}
