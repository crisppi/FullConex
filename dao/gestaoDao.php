<?php

require_once("./models/gestao.php");
require_once("./models/hospital.php");
require_once("./models/message.php");

// Review DAO
require_once("dao/gestaoDao.php");

class gestaoDAO implements gestaoDAOInterface
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

    public function buildgestao($data)
    {
        $gestao = new gestao();

        $gestao->id_gestao = $data["id_gestao"];
        $gestao->alto_custo_ges = $data["alto_custo_ges"];
        $gestao->rel_alto_custo_ges = $data["rel_alto_custo_ges"];

        $gestao->opme_ges = $data["opme_ges"];
        $gestao->rel_opme_ges = $data["rel_opme_ges"];

        $gestao->home_care_ges = $data["home_care_ges"];
        $gestao->rel_home_care_ges = $data["rel_home_care_ges"];

        $gestao->desospitalizacao_ges = $data["desospitalizacao_ges"];
        $gestao->rel_desospitalizacao_ges = $data["rel_desospitalizacao_ges"];

        $gestao->fk_user_ges = $data["fk_user_ges"];
        $gestao->fk_visita_ges = $data["fk_visita_ges"];
        $gestao->fk_internacao_ges = $data["fk_internacao_ges"];

        $gestao->evento_adverso_ges = $data["evento_adverso_ges"];
        $gestao->rel_evento_adverso_ges = $data["rel_evento_adverso_ges"];
        $gestao->tipo_evento_adverso_gest = $data["tipo_evento_adverso_gest"];
        $gestao->evento_sinalizado_ges = $data["evento_sinalizado_ges"];
        $gestao->evento_discutido_ges = $data["evento_discutido_ges"];
        $gestao->evento_negociado_ges = $data["evento_negociado_ges"];
        $gestao->evento_valor_negoc_ges = $data["evento_valor_negoc_ges"];
        $gestao->evento_prorrogar_ges = $data["evento_prorrogar_ges"];
        $gestao->evento_fech_ges = $data["evento_fech_ges"];

        $gestao->evento_retorno_qual_hosp_ges = $data["evento_retorno_qual_hosp_ges"];
        $gestao->evento_classificado_hospital_ges = $data["evento_classificado_hospital_ges"];
        $gestao->evento_data_ges = $data["evento_data_ges"];
        $gestao->evento_encerrar_ges = $data["evento_encerrar_ges"];
        $gestao->evento_impacto_financ_ges = $data["evento_impacto_financ_ges"];
        $gestao->evento_prolongou_internacao_ges = $data["evento_prolongou_internacao_ges"];
        $gestao->evento_concluido_ges = $data["evento_concluido_ges"];
        $gestao->evento_classificacao_ges = $data["evento_classificacao_ges"];

        return $gestao;
    }
    public function joingestaoHospital()
    {

        $gestao = [];

        $stmt = $this->conn->query("SELECT ac.id_gestao, ac.valor_aco, ac.gestao_aco, ho.id_hospital, ho.nome_hosp
         FROM tb_gestao ac 
         iNNER JOIN tb_hospital as ho On  
         ac.fk_hospital = ho.id_hospital
         ORDER BY ac.id_gestao DESC");
        $stmt->execute();
        $gestao = $stmt->fetchAll();
        return $gestao;
    }

    // mostrar acomocacao por id_gestao
    public function joingestaoHospitalshow($id_gestao)
    {
        $stmt = $this->conn->query("SELECT ac.id_gestao, ac.fk_hospital, ac.valor_aco, ac.gestao_aco, ho.id_hospital, ho.nome_hosp
         FROM tb_gestao ac          
         iNNER JOIN tb_hospital as ho On  
         ac.fk_hospital = ho.id_hospital
         where id_gestao = $id_gestao   
         ");

        $stmt->execute();

        $gestao = $stmt->fetch();
        return $gestao;
    }
    public function findAll()
    {
    }

    public function getgestao()
    {

        $gestao = [];

        $stmt = $this->conn->query("SELECT * FROM tb_gestao ORDER BY id_gestao DESC");

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $gestaoArray = $stmt->fetchAll();

            foreach ($gestaoArray as $gestao) {
                $gestao[] = $this->buildgestao($gestao);
            }
        }

        return $gestao;
    }



    public function findById($id_gestao)
    {
        $gestao = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_gestao
                                    WHERE id_gestao = :id_gestao");

        $stmt->bindParam(":id_gestao", $id_gestao);
        $stmt->execute();

        $data = $stmt->fetch();
        $gestao = $this->buildgestao($data);

        return $gestao;
    }

    public function findByIdInt($id_int)
    {
        $gestao = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_gestao
                                    WHERE fk_internacao_ges = :id_int");

        $stmt->bindParam(":id_int", $id_int);
        $stmt->execute();

        $data = $stmt->fetch();

        if ($data == False) {
            $gestao = new gestao();
        } else {
            $gestao = $this->buildgestao($data);
        }

        return $gestao;
    }

    public function debugQuery($query, $params)
    {
        foreach ($params as $key => $value) {
            // Add quotes for string values
            $escapedValue = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
            $query = str_replace(":$key", $escapedValue, $query);
        }
        return $query;
    }
    public function create(gestao $gestao)
    {
        $query = "INSERT INTO tb_gestao (
            fk_internacao_ges, 
            fk_visita_ges, 
            alto_custo_ges, 
            rel_alto_custo_ges, 
            evento_adverso_ges, 
            rel_evento_adverso_ges, 
            tipo_evento_adverso_gest, 
            evento_valor_negoc_ges,
            evento_sinalizado_ges,
            evento_discutido_ges,
            evento_negociado_ges,
            evento_prorrogar_ges,
            evento_fech_ges,
            opme_ges, 
            rel_opme_ges, 
            home_care_ges, 
            rel_home_care_ges,
            desospitalizacao_ges,
            rel_desospitalizacao_ges,
            fk_user_ges,
            evento_retorno_qual_hosp_ges,
            evento_classificado_hospital_ges,
            evento_data_ges,
            evento_encerrar_ges,
            evento_impacto_financ_ges,
            evento_prolongou_internacao_ges,
            evento_concluido_ges,
            evento_classificacao_ges

        ) VALUES (
            :fk_internacao_ges, 
            :fk_visita_ges, 
            :alto_custo_ges, 
            :rel_alto_custo_ges, 
            :evento_adverso_ges, 
            :rel_evento_adverso_ges, 
            :tipo_evento_adverso_gest,
            :evento_valor_negoc_ges,
            :evento_sinalizado_ges,
            :evento_discutido_ges,
            :evento_negociado_ges,
            :evento_prorrogar_ges,
            :evento_fech_ges,
            :opme_ges, 
            :rel_opme_ges, 
            :home_care_ges, 
            :rel_home_care_ges,
            :desospitalizacao_ges,
            :rel_desospitalizacao_ges,
            :fk_user_ges,
            :evento_retorno_qual_hosp_ges,
            :evento_classificado_hospital_ges,
            :evento_data_ges,
            :evento_encerrar_ges,
            :evento_impacto_financ_ges,
            :evento_prolongou_internacao_ges,
            :evento_concluido_ges,
            :evento_classificacao_ges

        )";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $params = [
            "fk_internacao_ges" => $gestao->fk_internacao_ges,
            "fk_visita_ges" => $gestao->fk_visita_ges,
            "alto_custo_ges" => $gestao->alto_custo_ges,
            "rel_alto_custo_ges" => $gestao->rel_alto_custo_ges,
            "evento_adverso_ges" => $gestao->evento_adverso_ges,
            "rel_evento_adverso_ges" => $gestao->rel_evento_adverso_ges,
            "tipo_evento_adverso_gest" => $gestao->tipo_evento_adverso_gest,
            "evento_valor_negoc_ges" => $gestao->evento_valor_negoc_ges,
            "evento_sinalizado_ges" => $gestao->evento_sinalizado_ges,
            "evento_discutido_ges" => $gestao->evento_discutido_ges,
            "evento_negociado_ges" => $gestao->evento_negociado_ges,
            "evento_prorrogar_ges" => $gestao->evento_prorrogar_ges,
            "evento_fech_ges" => $gestao->evento_fech_ges,
            "opme_ges" => $gestao->opme_ges,
            "rel_opme_ges" => $gestao->rel_opme_ges,
            "home_care_ges" => $gestao->home_care_ges,
            "rel_home_care_ges" => $gestao->rel_home_care_ges,
            "desospitalizacao_ges" => $gestao->desospitalizacao_ges,
            "rel_desospitalizacao_ges" => $gestao->rel_desospitalizacao_ges,
            "fk_user_ges" => $gestao->fk_user_ges,
            "evento_retorno_qual_hosp_ges" => $gestao->evento_retorno_qual_hosp_ges,
            "evento_classificado_hospital_ges" => $gestao->evento_classificado_hospital_ges,
            "evento_data_ges" => $gestao->evento_data_ges,
            "evento_encerrar_ges" => $gestao->evento_encerrar_ges,
            "evento_impacto_financ_ges" => $gestao->evento_impacto_financ_ges,
            "evento_prolongou_internacao_ges" => $gestao->evento_prolongou_internacao_ges,
            "evento_concluido_ges" => $gestao->evento_concluido_ges,
            "evento_classificacao_ges" => $gestao->evento_classificacao_ges

        ];

        foreach ($params as $key => $value) {
            // Convert empty strings to null
            $stmt->bindValue(":$key", $value === '' ? null : $value, $value === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        }


        // foreach ($params as $key => $value) {
        //     // Add quotes for string values
        //     $escapedValue = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
        //     $query = str_replace(":$key", $escapedValue, $query);
        // }
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            die("SQL Error: " . $errorInfo[2]);
        }

        $this->message->setMessage("Gestão adicionada com sucesso!", "success", "internacoes/lista");
    }

    public function update(gestao $gestao)
    {
        $stmt = $this->conn->prepare("UPDATE tb_gestao SET
            fk_internacao_ges            = :fk_internacao_ges,
            alto_custo_ges               = :alto_custo_ges,
            rel_alto_custo_ges           = :rel_alto_custo_ges,
            evento_adverso_ges           = :evento_adverso_ges,
            rel_evento_adverso_ges       = :rel_evento_adverso_ges,
            tipo_evento_adverso_gest     = :tipo_evento_adverso_gest,
            opme_ges                     = :opme_ges,
            rel_opme_ges                 = :rel_opme_ges,
            home_care_ges                = :home_care_ges,
            rel_home_care_ges            = :rel_home_care_ges,
            desospitalizacao_ges         = :desospitalizacao_ges,
            rel_desospitalizacao_ges     = :rel_desospitalizacao_ges,
            evento_sinalizado_ges        = :evento_sinalizado_ges,
            evento_discutido_ges         = :evento_discutido_ges,
            evento_negociado_ges         = :evento_negociado_ges,
            evento_prorrogar_ges         = :evento_prorrogar_ges,
            evento_fech_ges              = :evento_fech_ges,
            evento_valor_negoc_ges       = :evento_valor_negoc_ges
        WHERE id_gestao = :id_gestao");

        $stmt->bindParam(':fk_internacao_ges', $gestao->fk_internacao_ges);
        $stmt->bindParam(':alto_custo_ges', $gestao->alto_custo_ges);
        $stmt->bindParam(':rel_alto_custo_ges', $gestao->rel_alto_custo_ges);
        $stmt->bindParam(':evento_adverso_ges', $gestao->evento_adverso_ges);
        $stmt->bindParam(':rel_evento_adverso_ges', $gestao->rel_evento_adverso_ges);
        $stmt->bindParam(':tipo_evento_adverso_gest', $gestao->tipo_evento_adverso_gest);
        $stmt->bindParam(':opme_ges', $gestao->opme_ges);
        $stmt->bindParam(':rel_opme_ges', $gestao->rel_opme_ges);
        $stmt->bindParam(':home_care_ges', $gestao->home_care_ges);
        $stmt->bindParam(':rel_home_care_ges', $gestao->rel_home_care_ges);
        $stmt->bindParam(':desospitalizacao_ges', $gestao->desospitalizacao_ges);
        $stmt->bindParam(':rel_desospitalizacao_ges', $gestao->rel_desospitalizacao_ges);
        $stmt->bindParam(':evento_sinalizado_ges', $gestao->evento_sinalizado_ges);
        $stmt->bindParam(':evento_discutido_ges', $gestao->evento_discutido_ges);
        $stmt->bindParam(':evento_negociado_ges', $gestao->evento_negociado_ges);
        $stmt->bindParam(':evento_prorrogar_ges', $gestao->evento_prorrogar_ges);
        $stmt->bindParam(':evento_fech_ges', $gestao->evento_fech_ges);
        $stmt->bindParam(':evento_valor_negoc_ges', $gestao->evento_valor_negoc_ges);
        $stmt->bindParam(':id_gestao', $gestao->id_gestao);

        $stmt->execute();

        // Mensagem de sucesso ao editar gestão
        $this->message->setMessage("Gestão atualizada com sucesso!", "success", "internacoes/lista");
    }


    public function findByIdUpdate($gestao)
    {

        $stmt = $this->conn->prepare("UPDATE tb_gestao SET
        fk_internacao_ges = :fk_internacao_ges,
        alto_custo_ges = :alto_custo_ges,
        rel_alto_custo_ges = :rel_alto_custo_ges,
        evento_adverso_ges = :evento_adverso_ges,
        rel_evento_adverso_ges = :rel_evento_adverso_ges,
        tipo_evento_adverso_gest = :tipo_evento_adverso_gest,
        opme_ges = :opme_ges,
        rel_opme_ges = :rel_opme_ges,
        home_care_ges = :home_care_ges,
        rel_home_care_ges = :rel_home_care_ges,
        desospitalizacao_ges = :desospitalizacao_ges,
        rel_desospitalizacao_ges = :rel_desospitalizacao_ges,
        evento_retorno_qual_hosp_ges = :rel_desospitalizacao_ges,
        evento_classificado_hospital_ges = :evento_classificado_hospital_ges,
        evento_data_ges = :evento_data_ges,
        evento_encerrar_ges = :evento_encerrar_ges,
        evento_impacto_financ_ges = :evento_impacto_financ_ges,
        evento_prolongou_internacao_ges = :evento_prolongou_internacao_ges,
        evento_concluido_ges = :evento_concluido_ges,
        evento_classificacao_ges = :evento_classificacao_ges

        WHERE id_gestao = :id_gestao 
      ");

        $stmt->bindParam(":fk_internacao_ges", $gestao->fk_internacao_ges);
        $stmt->bindParam(":alto_custo_ges", $gestao->alto_custo_ges);
        $stmt->bindParam(":rel_alto_custo_ges", $gestao->rel_alto_custo_ges);
        $stmt->bindParam(":evento_adverso_ges", $gestao->evento_adverso_ges);
        $stmt->bindParam(":rel_evento_adverso_ges", $gestao->rel_evento_adverso_ges);
        $stmt->bindParam(":tipo_evento_adverso_gest", $gestao->tipo_evento_adverso_gest);
        $stmt->bindParam(":opme_ges", $gestao->opme_ges);
        $stmt->bindParam(":rel_opme_ges", $gestao->rel_opme_ges);
        $stmt->bindParam(":home_care_ges", $gestao->home_care_ges);
        $stmt->bindParam(":rel_home_care_ges", $gestao->rel_home_care_ges);
        $stmt->bindParam(":rel_home_care_ges", $gestao->rel_home_care_ges);
        $stmt->bindParam(":rel_desospitalizacao_ges", $gestao->rel_desospitalizacao_ges);

        $stmt->bindParam(":evento_retorno_qual_hosp_ges", $gestao->evento_retorno_qual_hosp_ges);
        $stmt->bindParam(":evento_classificado_hospital_ges", $gestao->evento_classificado_hospital_ges);
        $stmt->bindParam(":evento_data_ges", $gestao->evento_data_ges);
        $stmt->bindParam(":evento_encerrar_ges", $gestao->evento_encerrar_ges);
        $stmt->bindParam(":evento_impacto_financ_ges", $gestao->evento_impacto_financ_ges);
        $stmt->bindParam(":evento_prolongou_internacao_ges", $gestao->evento_prolongou_internacao_ges);
        $stmt->bindParam(":evento_concluido_ges", $gestao->evento_concluido_ges);
        $stmt->bindParam(":evento_classificacao_ges", $gestao->evento_classificacao_ges);

        $stmt->bindParam(":id_gestao", $gestao->id_gestao);
        $stmt->execute();

        // Mensagem de sucesso por editar gestao
        $this->message->setMessage("gestao atualizado com sucesso!", "success", "list_gestao.php");
    }

    public function destroy($id_gestao)
    {
        $stmt = $this->conn->prepare("DELETE FROM tb_gestao WHERE id_gestao = :id_gestao");

        $stmt->bindParam(":id_gestao", $id_gestao);

        $stmt->execute();

        // Mensagem de sucesso por remover filme
        $this->message->setMessage("gestao removido com sucesso!", "success", "cad_internacao_niveis.php");
    }


    public function findGeral()
    {

        $gestao = [];

        $stmt = $this->conn->query("SELECT * FROM tb_gestao ORDER BY id_gestao DESC");

        $stmt->execute();

        $gestao = $stmt->fetchAll();

        return $gestao;
    }
    // pegar id max da internacao
    public function findMax()
    {

        $gestao = [];

        $stmt = $this->conn->query("SELECT max(id_internacao) as ultimoReg from tb_internacao");

        $stmt->execute();

        $gestaoIdMax = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $gestaoIdMax;
    }
    public function findMaxVis()
    {

        $gestao = [];

        $stmt = $this->conn->query("SELECT max(id_visita) as ultimoReg from tb_visita");

        $stmt->execute();

        $gestaoIdMaxVis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $gestaoIdMaxVis;
    }
    public function findMaxGesInt()
    {

        $gestao = [];

        $stmt = $this->conn->query("SELECT max(id_internacao) as ultimoReg from tb_internacao");

        $stmt->execute();

        $findMaxGesInt = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $findMaxGesInt;
    }

    /**
     * Retorna todos os registros de gestão vinculados à internação.
     */
    public function selectRawByInternacao(int $id_internacao): array
    {
        if ($id_internacao <= 0) {
            return [];
        }
        $stmt = $this->conn->prepare("SELECT * FROM tb_gestao WHERE fk_internacao_ges = :id ORDER BY id_gestao DESC");
        $stmt->bindValue(':id', $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retorna todos os registros de gestão vinculados à visita.
     */
    public function selectByVisita(int $visitaId): array
    {
        if ($visitaId <= 0) {
            return [];
        }
        $stmt = $this->conn->prepare("SELECT * FROM tb_gestao WHERE fk_visita_ges = :visita ORDER BY id_gestao DESC");
        $stmt->bindValue(':visita', $visitaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Remove registros de gestão associados a uma visita.
     */
    public function deleteByVisita(int $visitaId): void
    {
        if ($visitaId <= 0) {
            return;
        }
        $stmt = $this->conn->prepare("DELETE FROM tb_gestao WHERE fk_visita_ges = :visita");
        $stmt->bindValue(':visita', $visitaId, PDO::PARAM_INT);
        $stmt->execute();
    }


    // METODO PESQUISA UTI NOVA QUERY COMPLETA
    public function selectAllGestao($where = null, $order = null, $limit = null)
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        //MONTA A QUERY
        $query = $this->conn->query('SELECT 
        ge.id_gestao,
        ge.fk_internacao_ges,
        ge.home_care_ges,
        ge.rel_home_care_ges,
        ge.alto_custo_ges,
        ge.rel_alto_custo_ges,
        ge.evento_adverso_ges,
        ge.rel_evento_adverso_ges,
        ge.tipo_evento_adverso_gest,
        ge.opme_ges,
        ge.rel_opme_ges,
        ge.desospitalizacao_ges,
        ge.rel_desospitalizacao_ges,
        ge.evento_sinalizado_ges,
        ge.evento_discutido_ges,
        ge.evento_negociado_ges,
        ge.evento_prorrogar_ges,
        ge.evento_fech_ges,
        ge.evento_retorno_qual_hosp_ges,
        ge.evento_classificado_hospital_ges,
        ge.evento_data_ges,
        ge.evento_encerrar_ges,
        ge.evento_impacto_financ_ges,
        ge.evento_prolongou_internacao_ges,
        ge.evento_concluido_ges,
        ge.evento_classificacao_ges,
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp,
        ac.id_internacao,
        ac.internado_int,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.fk_paciente_int
         
        FROM tb_gestao as ge 
    
            INNER JOIN tb_internacao AS ac ON
            ge.fk_internacao_ges = ac.id_internacao
            
            INNER JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital
    
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente ' . $where . ' ' . $order . ' ' . $limit);

        $query->execute();

        $uti = $query->fetchAll();

        return $uti;
    }
    public function QtdGestao($where = null, $order = null, $limite = null)
    {
        $hospital = [];
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limite = strlen($limite) ? 'LIMIT ' . $limite : '';

        $stmt = $this->conn->query('SELECT 
        ge.id_gestao,
        ge.fk_internacao_ges,
        ge.home_care_ges,
        ge.rel_home_care_ges,
        ge.alto_custo_ges,
        ge.rel_alto_custo_ges,
        ge.evento_adverso_ges,
        ge.rel_evento_adverso_ges,
        ge.tipo_evento_adverso_gest,
        ge.opme_ges,
        ge.rel_opme_ges,
        ge.desospitalizacao_ges,
        ge.rel_desospitalizacao_ges,
        ge.evento_sinalizado_ges,
        ge.evento_discutido_ges,
        ge.evento_negociado_ges,
        ge.evento_prorrogar_ges,
        ge.evento_fech_ges,
        ge.evento_retorno_qual_hosp_ges,
        ge.evento_classificado_hospital_ges,
        ge.evento_data_ges,
        ge.evento_encerrar_ges,
        ge.evento_impacto_financ_ges,
        ge.evento_prolongou_internacao_ges,
        ge.evento_concluido_ges,
        ge.evento_classificacao_ges,
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp,
        ac.id_internacao,
        ac.internado_int,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.fk_paciente_int,
        COUNT(id_gestao) as qtd
        
        FROM tb_gestao ge 
    
            INNER JOIN tb_internacao AS ac ON
            ge.fk_internacao_ges = ac.id_internacao
            
            INNER JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital
    
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente ' . $where . ' ' . $order . ' ' . $limite);

        $stmt->execute();

        $QtdTotalAnt = $stmt->fetch();

        return $QtdTotalAnt;
    }
    // METODO PESQUISA UTI NOVA QUERY COMPLETA
    public function selectAllGestaoLis($where = null, $order = null, $limit = null)
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        $group = ' GROUP BY ge.id_gestao ';


        //MONTA A QUERY
        $query = $this->conn->query('SELECT 
        ge.id_gestao,
        ge.fk_internacao_ges,
        ge.home_care_ges,
        ge.rel_home_care_ges,
        ge.alto_custo_ges,
        ge.rel_alto_custo_ges,
        ge.evento_adverso_ges,
        ge.rel_evento_adverso_ges,
        ge.tipo_evento_adverso_gest,
        ge.opme_ges,
        ge.rel_opme_ges,
        ge.desospitalizacao_ges,
        ge.rel_desospitalizacao_ges,
        ge.evento_sinalizado_ges,
        ge.evento_discutido_ges,
        ge.evento_negociado_ges,
        ge.evento_prorrogar_ges,
        ge.evento_retorno_qual_hosp_ges,
        ge.evento_classificado_hospital_ges,
        ge.evento_data_ges,
        ge.evento_encerrar_ges,
        ge.evento_impacto_financ_ges,
        ge.evento_prolongou_internacao_ges,
        ge.evento_concluido_ges,
        ge.evento_classificacao_ges,
        ge.evento_fech_ges,
        hos.fk_hospital_user,
        hos.fk_usuario_hosp,
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp, 
        se.id_usuario,
        se.usuario_user,
        ac.id_internacao,
        ac.internado_int,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.senha_int,
        ac.fk_paciente_int
         
        FROM tb_gestao ge 
    
            INNER JOIN tb_internacao AS ac ON
            ge.fk_internacao_ges = ac.id_internacao

            INNER JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital

            LEFT JOIN tb_hospitalUser AS hos ON
            hos.fk_hospital_user = ho.id_hospital

            left JOIN tb_user AS se ON  
            se.id_usuario = hos.fk_usuario_hosp
    
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente ' . $where . ' ' . $group . '' . $order . ' ' . $limit);

        $query->execute();

        $uti = $query->fetchAll();

        return $uti;
    }
    public function QtdGestaoLis($where = null, $order = null, $limite = null)
    {
        $hospital = [];
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limite = strlen($limite) ? 'LIMIT ' . $limite : '';

        $stmt = $this->conn->query('SELECT
                COUNT(id_gestao) as qtd,
 
        ge.id_gestao,
        ge.fk_internacao_ges,
        ge.home_care_ges,
        ge.rel_home_care_ges,
        ge.alto_custo_ges,
        ge.rel_alto_custo_ges,
        ge.evento_adverso_ges,
        ge.rel_evento_adverso_ges,
        ge.tipo_evento_adverso_gest,
        ge.opme_ges,
        ge.rel_opme_ges,
        ge.desospitalizacao_ges,
        ge.rel_desospitalizacao_ges,
        ge.evento_sinalizado_ges,
        ge.evento_discutido_ges,
        ge.evento_negociado_ges,
        ge.evento_prorrogar_ges,
        ge.evento_fech_ges,
        ge.evento_retorno_qual_hosp_ges,
        ge.evento_classificado_hospital_ges,
        ge.evento_data_ges,
        ge.evento_encerrar_ges,
        ge.evento_impacto_financ_ges,
        ge.evento_prolongou_internacao_ges,
        ge.evento_concluido_ges,
        ge.evento_classificacao_ges,
        hos.fk_hospital_user,
        hos.fk_usuario_hosp,
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp, 
        se.id_usuario,
        se.usuario_user,
        ac.id_internacao,
        ac.internado_int,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.senha_int,
        ac.fk_paciente_int
         
        FROM tb_gestao ge 
    
            INNER JOIN tb_internacao AS ac ON
            ge.fk_internacao_ges = ac.id_internacao

            INNER JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital

            LEFT JOIN tb_hospitalUser AS hos ON
            hos.fk_hospital_user = ho.id_hospital

            left JOIN tb_user AS se ON  
            se.id_usuario = hos.fk_usuario_hosp
    
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente ' . $where . ' ' . $order . ' ' . $limite);

        $stmt->execute();

        $QtdTotalAnt = $stmt->fetch();

        return $QtdTotalAnt;
    }
}
