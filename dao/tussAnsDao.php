<?php

require_once("./models/tuss_ans.php");
require_once("./models/message.php");

// Review DAO
require_once("dao/tussAnsDao.php");

class tussAnsDAO implements tussAnsDAOInterface
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

    public function buildtussAns($data)
    {
        $tussAns = new tussAns();

        $tussAns->id_tuss = $data["id_tuss"];
        $tussAns->cod_tuss = $data["cod_tuss"];
        $tussAns->terminologia_tuss = $data["terminologia_tuss"];


        return $tussAns;
    }

    public function findAll()
    {
        $tussAns = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_tuss_ans
        ORDER BY id_tuss DESC");

        $stmt->execute();

        $tussAns = $stmt->fetchAll();
        return $tussAns;
    }

}