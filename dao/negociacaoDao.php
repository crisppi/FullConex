<?php

require_once("./models/negociacao.php");
require_once("./models/message.php");

// Review DAO
require_once("dao/negociacaoDao.php");

class negociacaoDAO implements negociacaoDAOInterface
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

    public function buildNegociacao($data)
    {
        $negociacao = new Negociacao();

        $negociacao->id_negociacao = $data["id_negociacao"];
        $negociacao->fk_id_int = $data["fk_id_int"];

        $negociacao->qtd = $data["qtd"];
        $negociacao->troca_de = $data["troca_de"];
        $negociacao->troca_para = $data["troca_para"];
        $negociacao->saving = $data["saving"];

        $negociacao->fk_usuario_neg = $data["fk_usuario_neg"];

        return $negociacao;
    }
    public function joinnegociacaoHospitalshow($id_negociacao)
    {
        $stmt = $this->conn->query("SELECT ac.id_negociacao, ac.fk_hospital, ac.valor_aco, ac.negociacao_aco, ho.id_hospital, ho.nome_hosp
         FROM tb_negociacao ac          
         iNNER JOIN tb_hospital as ho On  
         ac.fk_hospital = ho.id_hospital
         where id_negociacao = $id_negociacao   
         ");

        $stmt->execute();

        $negociacao = $stmt->fetch();
        return $negociacao;
    }
    public function findAll()
    {
        $negociacao = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_negociacao
        ORDER BY id_negociacao DESC");

        $stmt->execute();

        $negociacao = $stmt->fetchAll();
        return $negociacao;
    }

    public function findByNegociacao($pesquisa_nome)
    {

        $usuario = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_negociacao
                                    WHERE nome_est LIKE :nome_est ");

        $stmt->bindValue(":nome_est", '%' . $pesquisa_nome . '%');

        $stmt->execute();

        $usuario = $stmt->fetchAll();
        return $usuario;
    }
    public function getnegociacao()
    {

        $negociacao = [];

        $stmt = $this->conn->query("SELECT * FROM tb_negociacao ORDER BY id_negociacao DESC");

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $negociacaoArray = $stmt->fetchAll();

            foreach ($negociacaoArray as $negociacao) {
                $negociacao[] = $this->buildNegociacao($negociacao);
            }
        }

        return $negociacao;
    }

    public function getnegociacaoByNome($nome)
    {

        $negociacao = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_negociacao
                                    WHERE nome_est = :nome_est
                                    ORDER BY id_negociacao DESC");

        $stmt->bindParam(":nome_est", $nome_est);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $negociacaoArray = $stmt->fetchAll();

            foreach ($negociacaoArray as $negociacao) {
                $negociacao[] = $this->buildNegociacao($negociacao);
            }
        }

        return $negociacao;
    }

    public function findById($id_negociacao)
    {
        $negociacao = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_negociacao
                                    WHERE id_negociacao = :id_negociacao");

        $stmt->bindParam(":id_negociacao", $id_negociacao);
        $stmt->execute();

        $data = $stmt->fetch();
        //var_dump($data);
        $negociacao = $this->buildNegociacao($data);

        return $negociacao;
    }



    public function create(Negociacao $negociacao)
    {
        $stmt = $this->conn->prepare("
        INSERT INTO tb_negociacao (
            fk_id_int, 
            troca_de, 
            troca_para, 
            qtd, 
            saving, 
            fk_usuario_neg,
            data_inicio_neg,
            data_fim_neg,
            tipo_negociacao
        ) VALUES (
            :fk_id_int, 
            :troca_de, 
            :troca_para, 
            :qtd, 
            :saving, 
            :fk_usuario_neg,
            :data_inicio_neg,
            :data_fim_neg,
            :tipo_negociacao
        )
    ");

        $stmt->bindParam(":fk_id_int", $negociacao->fk_id_int);
        $stmt->bindParam(":troca_de", $negociacao->troca_de);
        $stmt->bindParam(":troca_para", $negociacao->troca_para);
        $stmt->bindParam(":qtd", $negociacao->qtd);
        $stmt->bindParam(":saving", $negociacao->saving);
        $stmt->bindParam(":fk_usuario_neg", $negociacao->fk_usuario_neg);
        $stmt->bindParam(":data_inicio_neg", $negociacao->data_inicio_neg);
        $stmt->bindParam(":data_fim_neg", $negociacao->data_fim_neg);
        $stmt->bindParam(":tipo_negociacao", $negociacao->tipo_negociacao);

        $stmt->execute();

        $this->message->setMessage("Negociação adicionada com sucesso!", "success", "cad_internacao_niveis.php");
    }


    public function update(Negociacao $negociacao)
    {
        $stmt = $this->conn->prepare("
        UPDATE tb_negociacao SET
            fk_id_int = :fk_id_int,
            troca_de = :troca_de,
            troca_para = :troca_para,
            qtd = :qtd,
            saving = :saving,
            fk_usuario_neg = :fk_usuario_neg,
            data_inicio_neg = :data_inicio_neg,
            data_fim_neg = :data_fim_neg,
            tipo_negociacao = :tipo_negociacao
        WHERE id_negociacao = :id_negociacao
    ");

        $stmt->bindParam(":fk_id_int", $negociacao->fk_id_int);
        $stmt->bindParam(":troca_de", $negociacao->troca_de);
        $stmt->bindParam(":troca_para", $negociacao->troca_para);
        $stmt->bindParam(":qtd", $negociacao->qtd);
        $stmt->bindParam(":saving", $negociacao->saving);
        $stmt->bindParam(":fk_usuario_neg", $negociacao->fk_usuario_neg);
        $stmt->bindParam(":data_inicio_neg", $negociacao->data_inicio_neg);
        $stmt->bindParam(":data_fim_neg", $negociacao->data_fim_neg);
        $stmt->bindParam(":tipo_negociacao", $negociacao->tipo_negociacao);

        $stmt->bindParam(":id_negociacao", $negociacao->id_negociacao);

        $stmt->execute();

        $this->message->setMessage("Negociação atualizada com sucesso!", "success", "list_negociacao.php");
    }


    public function destroy($id_negociacao)
    {
        $stmt = $this->conn->prepare("DELETE FROM tb_negociacao WHERE id_negociacao = :id_negociacao");

        $stmt->bindParam(":id_negociacao", $id_negociacao);

        $stmt->execute();

        // Mensagem de sucesso por remover filme
        $this->message->setMessage("negociacao removido com sucesso!", "success", "list_negociacao.php");
    }

    // METODO DE PROCURA POR ID DA INTERNACAO PARA UTILIZACAO NO FORM NEGOCIACAO    
    public function findByLastId($lastId)
    {

        $negociacao = [];

        $stmt = $this->conn->query("SELECT 
        ng.id_negociacao,
        ng.fk_id_int, 
        ng.qtd_1, 
        ng.qtd_2, 
        ng.qtd_3, 
        ng.troca_de_1, 
        ng.troca_de_2, 
        ng.troca_de_3, 
        ng.troca_para_1, 
        ng.troca_para_2, 
        ng.troca_para_3,
        ng.fk_usuario_neg, 
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp,
        ac.id_internacao,
        ac.internado_int,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.fk_paciente_int,
        ad.fk_hospital,
        ad.valor_aco,
        ad.acomodacao_aco
        
        FROM tb_negociacao ng 
    
            left JOIN tb_internacao AS ac ON
            ng.fk_id_int = ac.id_internacao
            
            INNER JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital
    
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente 
            
            INNER JOIN tb_acomodacao AS ad ON  
            ho.id_hospital = ad.fk_hospital
            
            WHERE ac.id_internacao = $lastId ");

        $stmt->execute();

        $negociacao = $stmt->fetchAll();

        return $negociacao;
    }

    // METODO DE PROCURA SEM FILTROS
    public function findGeral()
    {

        $negociacao = [];

        $stmt = $this->conn->query("SELECT 
        ng.id_negociacao,
        ng.fk_id_int, 
        ng.qtd_1, 
        ng.qtd_2, 
        ng.qtd_3, 
        ng.troca_de_1, 
        ng.troca_de_2, 
        ng.troca_de_3, 
        ng.troca_para_1, 
        ng.troca_para_2, 
        ng.troca_para_3,
        ng.fk_usuario_neg, 
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp,
        ac.id_internacao,
        ac.internado_int,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.fk_paciente_int
        
        FROM tb_negociacao ng 
    
            INNER JOIN tb_internacao AS ac ON
            ng.fk_id_int = ac.id_internacao
            
            INNER JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital
    
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente 
        ");

        $stmt->execute();

        $negociacao = $stmt->fetchAll();

        return $negociacao;
    }

    // dao/negociacaoDao.php
    public function selectAllnegociacao($where = null, $order = null, $limit = null)
    {
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

    $sql = "
        SELECT
            ng.id_negociacao,
            ng.fk_id_int,
            ng.troca_de,
            ng.troca_para,
            ng.qtd,
            ng.saving,
            ng.data_inicio_neg,
            ng.data_fim_neg,
            ng.tipo_negociacao,
            ng.fk_usuario_neg,
            ng.fk_visita_neg,
            ng.deletado_neg,
            ng.updated_at
        FROM tb_negociacao AS ng
        $where
        $order
        $limit
    ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectNegociacoesDetalhes($where = null, $order = null, $limit = null)
    {
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : 'ORDER BY ng.data_inicio_neg DESC';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        $sql = "
            SELECT
                ng.id_negociacao,
                ng.fk_id_int,
                ng.troca_de,
                ng.troca_para,
                ng.qtd,
                ng.saving,
                ng.data_inicio_neg,
                ng.data_fim_neg,
                ng.tipo_negociacao,
                ng.fk_usuario_neg,
                ng.fk_visita_neg,
                ng.deletado_neg,
                ng.updated_at,
                ac.senha_int,
                ac.data_intern_int,
                ho.nome_hosp,
                pa.nome_pac,
            us.usuario_user AS nome_usuario,
            pa.matricula_pac
            FROM tb_negociacao AS ng
            LEFT JOIN tb_internacao ac ON ng.fk_id_int = ac.id_internacao
            LEFT JOIN tb_hospital ho ON ac.fk_hospital_int = ho.id_hospital
            LEFT JOIN tb_paciente pa ON ac.fk_paciente_int = pa.id_paciente
            LEFT JOIN tb_user us ON ng.fk_usuario_neg = us.id_usuario
            $where
            $order
            $limit
        ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countNegociacoesDetalhes($where = null)
    {
        $where = strlen($where) ? 'WHERE ' . $where : '';

        $sql = "
            SELECT COUNT(*) AS total
            FROM tb_negociacao AS ng
            LEFT JOIN tb_internacao ac ON ng.fk_id_int = ac.id_internacao
            LEFT JOIN tb_hospital ho ON ac.fk_hospital_int = ho.id_hospital
            LEFT JOIN tb_paciente pa ON ac.fk_paciente_int = pa.id_paciente
            $where
        ";

        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public function findMaxVis()
    {

        $gestao = [];

        $stmt = $this->conn->query("SELECT max(id_visita) as ultimoReg from tb_visita");

        $stmt->execute();

        $gestaoIdMaxVis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $gestaoIdMaxVis;
    }
    public function Qtdnegociacao($where = null, $order = null, $limite = null)
    {
        $negociacao = [];
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limite = strlen($limite) ? 'LIMIT ' . $limite : '';

        $stmt = $this->conn->query('SELECT * ,COUNT(id_negociacao) as qtd FROM tb_negociacao ' . $where . ' ' . $order . ' ' . $limite);

        $stmt->execute();

        $QtdTotalEst = $stmt->fetch();

        return $QtdTotalEst;
    }

    public function existeNegociacao($negociacao)
    {
        $query = "SELECT COUNT(*) AS total FROM tb_negociacao 
                  WHERE fk_id_int = :fk_id_int 
                  AND troca_de = :troca_de 
                  AND troca_para = :troca_para 
                  AND qtd = :qtd 
                  AND saving = :saving";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_id_int', $negociacao->fk_id_int);
        $stmt->bindParam(':troca_de', $negociacao->troca_de);
        $stmt->bindParam(':troca_para', $negociacao->troca_para);
        $stmt->bindParam(':qtd', $negociacao->qtd);
        $stmt->bindParam(':saving', $negociacao->saving);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['total'])) {
            return $result['total'] > 0;
        }

        return false; // Ou trate como desejar
    }
    public function findByInternacao($id_internacao)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tb_negociacao WHERE fk_id_int = :fk_id_int");
        $stmt->bindParam(':fk_id_int', $id_internacao);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Remove todas as negociações associadas a uma visita específica. */
    public function deleteByVisita(int $visitaId): void
    {
        if ($visitaId <= 0) return;
        $stmt = $this->conn->prepare("DELETE FROM tb_negociacao WHERE fk_visita_neg = :visita");
        $stmt->bindValue(':visita', $visitaId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // dao/negociacaoDao.php
public function selectByInternacao(
    int $id_internacao,
    ?string $ini = null,     // "YYYY-MM-DD" (opcional)
    ?string $fim = null,     // "YYYY-MM-DD" (opcional)
    bool $includeDeleted = false
): array {
    // datas estão em VARCHAR na tabela — tentamos parsear no MySQL
    $dateIniExpr = "COALESCE(STR_TO_DATE(ng.data_inicio_neg,'%Y-%m-%d'), STR_TO_DATE(ng.data_inicio_neg,'%d/%m/%Y'))";
    $dateFimExpr = "COALESCE(STR_TO_DATE(ng.data_fim_neg,'%Y-%m-%d'), STR_TO_DATE(ng.data_fim_neg,'%d/%m/%Y'))";

    $where = ["ng.fk_id_int = :id"];
    $params = [":id" => $id_internacao];

    if (!$includeDeleted) {
        $where[] = "(ng.deletado_neg IS NULL OR ng.deletado_neg = '' OR ng.deletado_neg = 'N')";
    }

    // interseção de períodos (se fornecer ini/fim)
    if ($ini) {
        $where[] = "IFNULL($dateFimExpr, $dateIniExpr) >= :ini";
        $params[":ini"] = $ini;
    }
    if ($fim) {
        $where[] = "IFNULL($dateIniExpr, $dateFimExpr) <= :fim";
        $params[":fim"] = $fim;
    }

    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $sql = "
        SELECT
            ng.id_negociacao,
            ng.fk_id_int,
            ng.troca_de,
            ng.troca_para,
            ng.qtd,
            ng.saving,
            ng.data_inicio_neg,
            ng.data_fim_neg,
            ng.tipo_negociacao,
            ng.fk_usuario_neg,
            ng.fk_visita_neg,
            ng.deletado_neg,
            ng.updated_at
        FROM tb_negociacao AS ng
        $whereSql
        ORDER BY
            COALESCE($dateFimExpr, $dateIniExpr, ng.updated_at) DESC,
            ng.id_negociacao DESC
    ";

    $stmt = $this->conn->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
