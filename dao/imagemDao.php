<?php

require_once("./models/imagem.php");
require_once("dao/imagemDao.php");

class imagemDAO implements imagemDAOInterface
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

    public function buildimagem($data)
    {
        $imagem = new imagem();

        $imagem->id_imagem = $data["id_imagem"];
        $imagem->fk_imagem = $data["fk_imagem"];
        $imagem->imagem_img = $data["dataImg"];
        $imagem->imagem_name_img = $data["imagem_name_img"];

        return $imagem;
    }

    public function create(imagem $imagem)
    {

        $stmt = $this->conn->prepare("INSERT INTO tb_imagens (
        fk_imagem,
        imagem_img,
        imagem_name_img

      ) VALUES (
        :fk_imagem,
        :imagem_img,
        :imagem_name_img

     )");

        $stmt->bindParam(":fk_imagem", $imagem->fk_imagem);
        $stmt->bindParam(":imagem_img", $imagem->imagem_img);
        $stmt->bindParam(":imagem_name_img", $imagem->imagem_name_img);

        $stmt->execute();

        // Mensagem de sucesso por adicionar filme
        $this->message->setMessage("seguradoraImg adicionado com sucesso!", "success", "list_seguradora.php");
    }


    public function destroy($id_imagem)
    {
        $stmt = $this->conn->prepare("DELETE FROM tb_imagens WHERE id_imagem = :id_imagem");

        $stmt->bindParam(":id_imagem", $id_imagem);

        $stmt->execute();

        // Mensagem de sucesso por remover filme
        $this->message->setMessage("imagem removido com sucesso!", "success", "list_imagem.php");
    }


    public function findGeral()
    {

        $imagem = [];

        $stmt = $this->conn->query("SELECT * FROM tb_imagens ORDER BY id_imagem DESC");

        $stmt->execute();

        $imagem = $stmt->fetchAll();

        return $imagem;
    }
    public function selectAllimagem($where = null, $order = null, $limit = null)
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        //MONTA A QUERY
        $query = $this->conn->query('SELECT * FROM tb_imagens ' . $where . ' ' . $order . ' ' . $limit);

        $query->execute();

        $imagem = $query->fetchAll();

        return $imagem;
    }


    public function Qtdimagem($where = null, $order = null, $limite = null)
    {
        $hospital = [];
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limite = strlen($limite) ? 'LIMIT ' . $limite : '';

        $stmt = $this->conn->query('SELECT * ,COUNT(id_imagem) as qtd FROM tb_imagens ' . $where . ' ' . $order . ' ' . $limite);

        $stmt->execute();

        $QtdTotalPat = $stmt->fetch();

        return $QtdTotalPat;
    }

    public function findById($id_imagem)
    {
        $imagem = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_imagens
                                    WHERE id_imagem = :id_imagem");

        $stmt->bindParam(":id_imagem", $id_imagem);
        $stmt->execute();

        $data = $stmt->fetch();
        //var_dump($data);
        $imagem = $this->buildimagem($data);

        return $imagem;
    }
}
