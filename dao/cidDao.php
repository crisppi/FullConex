<?php

require_once("./models/cid.php");
require_once("./models/message.php");

// Review DAO
require_once("dao/cidDao.php");

class cidDAO implements cidDAOInterface
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

    public function buildCid($data)
    {
        $cid = new cid();

        $cid->id_cid = $data["id_cid"];
        $cid->cat = $data["cat"];
        $cid->descricao = $data["descricao"];
       

        return $cid;
    }

    public function findAll()
    {
        $cid = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_cid
        ORDER BY id_cid DESC");

        $stmt->execute();

        $cid = $stmt->fetchAll();
        return $cid;
    }

    
}

