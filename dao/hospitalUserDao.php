<?php

require_once("./models/message.php");
require_once("./models/hospitalUser.php");

class hospitalUserDAO implements hospitalUserDAOInterface
{
    private $conn;
    private $url;
    public $message;

    // Tabelas (ajuste os nomes se no seu banco forem diferentes)
    private const TBL_LINK = 'tb_hospitalUser';
    private const TBL_HOSP = 'tb_hospital';
    private const TBL_USER = 'tb_user';

    public function __construct(PDO $conn, $url)
    {
        $this->conn    = $conn;
        $this->url     = $url;
        $this->message = new Message($url);

        // Modo seguro
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /* ==========================
       BUILD (public p/ interface)
    ========================== */
    public function buildhospitalUser($data)
    {
        $hu = new hospitalUser();
        $hu->id_hospitalUser  = isset($data["id_hospitalUser"])  ? (int)$data["id_hospitalUser"]  : null;
        $hu->fk_usuario_hosp  = isset($data["fk_usuario_hosp"])  ? (int)$data["fk_usuario_hosp"]  : null;
        $hu->fk_hospital_user = isset($data["fk_hospital_user"]) ? (int)$data["fk_hospital_user"] : null;
        return $hu;
    }

    /* ==========================
       CRUD básico
    ========================== */
    public function findAll()
    {
        $sql = "SELECT * FROM " . self::TBL_LINK . " ORDER BY id_hospitalUser DESC";
        return $this->conn->query($sql)->fetchAll();
    }

    public function findById($id_hospitalUser)
    {
        $sql = "SELECT * FROM " . self::TBL_LINK . " WHERE id_hospitalUser = :id LIMIT 1";
        $st  = $this->conn->prepare($sql);
        $st->bindValue(":id", (int)$id_hospitalUser, PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: [];
    }

    public function create(hospitalUser $hospitalUser)
    {
        $sql = "INSERT INTO " . self::TBL_LINK . " (fk_usuario_hosp, fk_hospital_user)
                VALUES (:u, :h)";
        $st = $this->conn->prepare($sql);
        $st->bindValue(":u", $hospitalUser->fk_usuario_hosp,  PDO::PARAM_INT);
        $st->bindValue(":h", $hospitalUser->fk_hospital_user, PDO::PARAM_INT);
        $st->execute();

        $this->message->setMessage("Vínculo criado com sucesso!", "success", "list_hospitalUser.php");
    }

    public function update(hospitalUser $hospitalUser)
    {
        $sql = "UPDATE " . self::TBL_LINK . "
                   SET fk_usuario_hosp = :u,
                       fk_hospital_user = :h
                 WHERE id_hospitalUser = :id";
        $st = $this->conn->prepare($sql);
        $st->bindValue(":u",  $hospitalUser->fk_usuario_hosp,  PDO::PARAM_INT);
        $st->bindValue(":h",  $hospitalUser->fk_hospital_user, PDO::PARAM_INT);
        $st->bindValue(":id", $hospitalUser->id_hospitalUser,  PDO::PARAM_INT);
        $st->execute();

        $this->message->setMessage("Vínculo atualizado com sucesso!", "success", "list_hospitalUser.php");
    }

    public function destroy($id_hospitalUser)
    {
        $sql = "DELETE FROM " . self::TBL_LINK . " WHERE id_hospitalUser = :id";
        $st  = $this->conn->prepare($sql);
        $st->bindValue(":id", (int)$id_hospitalUser, PDO::PARAM_INT);
        $st->execute();

        $this->message->setMessage("Vínculo removido com sucesso!", "success", "list_hospitalUser.php");
    }

    /* ==========================
       Consultas auxiliares
    ========================== */
    public function findGeral()
    {
        $sql = "SELECT * FROM " . self::TBL_LINK . " ORDER BY id_hospitalUser DESC";
        return $this->conn->query($sql)->fetchAll();
    }

    public function findByHosp($pesquisa_nome)
    {
        // Busca por nome do hospital (JOIN correto)
        $sql = "SELECT 
                    hu.*,
                    h.nome_hosp
                FROM " . self::TBL_LINK . " hu
                LEFT JOIN " . self::TBL_HOSP . " h ON h.id_hospital = hu.fk_hospital_user
                WHERE h.nome_hosp LIKE :nome
                ORDER BY h.nome_hosp";
        $st = $this->conn->prepare($sql);
        $st->bindValue(":nome", "%{$pesquisa_nome}%");
        $st->execute();
        return $st->fetchAll();
    }

    public function gethospitalUser()
    {
        $sql = "SELECT * FROM " . self::TBL_LINK . " ORDER BY id_hospitalUser DESC";
        $rows = $this->conn->query($sql)->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[] = $this->buildhospitalUser($row);
        }
        return $out;
    }

    public function selectAllhospitalUser($where = null, $order = null, $limit = null)
    {
        $where = !empty($where) ? "WHERE {$where}" : "";
        $order = !empty($order) ? "ORDER BY {$order}" : "";
        $limit = !empty($limit) ? "LIMIT {$limit}" : "";

        $sql = "SELECT 
                    hu.id_hospitalUser,
                    hu.fk_usuario_hosp,
                    hu.fk_hospital_user,
                    h.id_hospital,
                    h.nome_hosp,
                    u.id_usuario,
                    u.usuario_user,
                    u.email_user,
                    u.cargo_user,
                    u.nivel_user,
                    u.ativo_user
                FROM " . self::TBL_LINK . " hu
                LEFT JOIN " . self::TBL_HOSP . " h ON h.id_hospital = hu.fk_hospital_user
                LEFT JOIN " . self::TBL_USER . " u ON u.id_usuario  = hu.fk_usuario_hosp
                {$where} {$order} {$limit}";
        return $this->conn->query($sql)->fetchAll();
    }

    public function QtdhospitalUser($where = null, $order = null, $limite = null)
    {
        $where = !empty($where) ? "WHERE {$where}" : "";
        $order = !empty($order) ? "ORDER BY {$order}" : "";
        $limit = !empty($limite) ? "LIMIT {$limite}" : "";

        $sql = "SELECT COUNT(hu.id_hospitalUser) AS qtd
                FROM " . self::TBL_LINK . " hu
                LEFT JOIN " . self::TBL_HOSP . " h ON h.id_hospital = hu.fk_hospital_user
                LEFT JOIN " . self::TBL_USER . " u ON u.id_usuario  = hu.fk_usuario_hosp
                {$where} {$order} {$limit}";
        $row = $this->conn->query($sql)->fetch();
        return $row ?: ['qtd' => 0];
    }

    public function selecHospUser($id_usuario)
    {
        // Vínculos por USUÁRIO (lista)
        $sql = "SELECT 
                    hu.id_hospitalUser,
                    hu.fk_usuario_hosp,
                    hu.fk_hospital_user,
                    h.id_hospital,
                    h.nome_hosp,
                    u.id_usuario,
                    u.usuario_user,
                    u.cargo_user
                FROM " . self::TBL_LINK . " hu
                LEFT JOIN " . self::TBL_HOSP . " h ON h.id_hospital = hu.fk_hospital_user
                LEFT JOIN " . self::TBL_USER . " u ON u.id_usuario  = hu.fk_usuario_hosp
                WHERE hu.fk_usuario_hosp = :id";
        $st = $this->conn->prepare($sql);
        $st->bindValue(":id", (int)$id_usuario, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function joinHospitalUserAll()
    {
        $sql = "SELECT 
                    hu.id_hospitalUser,
                    hu.fk_usuario_hosp,
                    hu.fk_hospital_user,
                    h.id_hospital,
                    h.nome_hosp,
                    u.id_usuario,
                    u.usuario_user
                FROM " . self::TBL_LINK . " hu
                LEFT JOIN " . self::TBL_HOSP . " h ON h.id_hospital = hu.fk_hospital_user
                LEFT JOIN " . self::TBL_USER . " u ON u.id_usuario  = hu.fk_usuario_hosp
                ORDER BY hu.id_hospitalUser DESC";
        return $this->conn->query($sql)->fetchAll();
    }

    /* ==========================
       Métodos p/ telas
    ========================== */

    /** Busca UMA linha por PK (use na tela de edição pelo id_hospitalUser) */
    public function findByPk(int $id_hospitalUser): ?array
    {
        $sql = "SELECT 
                    hu.id_hospitalUser,
                    hu.fk_usuario_hosp,
                    hu.fk_hospital_user,
                    h.nome_hosp,
                    u.usuario_user,
                    u.email_user,
                    u.cargo_user
                FROM " . self::TBL_LINK . " hu
                LEFT JOIN " . self::TBL_HOSP . " h ON h.id_hospital = hu.fk_hospital_user
                LEFT JOIN " . self::TBL_USER . " u ON u.id_usuario  = hu.fk_usuario_hosp
                WHERE hu.id_hospitalUser = :id
                LIMIT 1";
        $st = $this->conn->prepare($sql);
        $st->bindValue(":id", $id_hospitalUser, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        return $row ?: null;
    }

    /**
     * Compatibilidade: este método existia no seu projeto.
     * Aceita um ID e tenta:
     *  1) achar por PK (id_hospitalUser);
     *  2) se não achar, usa como id de USUÁRIO (fk_usuario_hosp) e retorna a 1ª linha.
     * Retorno: UMA linha (array associativo) ou [].
     */
    public function joinHospitalUser($id)
    {
        $id = (int)$id;

        // 1) tenta como PK
        $row = $this->findByPk($id);
        if ($row) return $row;

        // 2) tenta como id do usuário (pega a primeira)
        $sql = "SELECT 
                    hu.id_hospitalUser as id_hospital,
                    hu.fk_usuario_hosp,
                    hu.fk_hospital_user,
                    h.nome_hosp,
                    u.usuario_user,
                    u.email_user,
                    u.cargo_user
                FROM " . self::TBL_LINK . " hu
                LEFT JOIN " . self::TBL_HOSP . " h ON h.id_hospital = hu.fk_hospital_user
                LEFT JOIN " . self::TBL_USER . " u ON u.id_usuario  = hu.fk_usuario_hosp
                WHERE hu.fk_usuario_hosp = :id
                ORDER BY hu.id_hospitalUser DESC
                ";
        $st = $this->conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * EXIGÊNCIA DA INTERFACE:
     * Busca por ID de USUÁRIO (fk_usuario_hosp).
     * Se a interface esperar UMA linha, devolvemos a primeira.
     * Se preferir todas as linhas, troque para "return $rows;".
     */
    public function findByIdUser($id_usuario)
    {
        $rows = $this->selecHospUser((int)$id_usuario);
        return $rows[0] ?? [];
    }
    public function listarPorUsuario(int $userId): array
    {
        $sql = "
            SELECT 
                h.id_hospital,
                h.nome_hosp
            FROM " . self::TBL_LINK . " hu
            INNER JOIN " . self::TBL_HOSP . " h ON h.id_hospital = hu.fk_hospital_user
            WHERE hu.fk_usuario_hosp = :uid
            ORDER BY h.nome_hosp ASC
        ";
        $st = $this->conn->prepare($sql);
        $st->bindValue(':uid', $userId, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll() ?: [];
    }
}