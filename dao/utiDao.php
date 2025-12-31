<?php

require_once("./models/uti.php");
require_once("./models/hospital.php");
require_once("./models/internacao.php");
require_once("./models/message.php");

// Review DAO
require_once("dao/utiDao.php");

class utiDAO implements utiDAOInterface
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

    public function buildUti($data)
    {
        $uti = new uti();

        $uti->id_uti = $data["id_uti"];
        $uti->fk_internacao_uti = $data["fk_internacao_uti"];
        $uti->fk_visita_uti = $data["fk_visita_uti"];
        $uti->criterios_uti = $data["criterios_uti"];
        $uti->data_alta_uti = $data["data_alta_uti"];
        $uti->dva_uti = $data["dva_uti"];
        $uti->data_internacao_uti = $data["data_internacao_uti"];
        $uti->especialidade_uti = $data["especialidade_uti"];
        $uti->internacao_uti = $data["internacao_uti"];
        $uti->internado_uti = $data["internado_uti"];
        $uti->just_uti = $data["just_uti"];
        $uti->motivo_uti = $data["motivo_uti"];
        $uti->rel_uti = $data["rel_uti"];
        $uti->saps_uti = $data["saps_uti"];
        $uti->score_uti = $data["score_uti"];
        $uti->vm_uti = $data["vm_uti"];
        $uti->fk_user_uti = $data["fk_user_uti"];
        $uti->glasgow_uti = $data["glasgow_uti"];
        $uti->dist_met_uti = $data["dist_met_uti"];
        $uti->suporte_vent_uti = $data["suporte_vent_uti"];
        $uti->justifique_uti = $data["justifique_uti"];
        $uti->hora_internacao_uti = $data["hora_internacao_uti"];
        $uti->fk_user_uti = $data["fk_user_uti"];


        return $uti;
    }
    public function joinUtiHospital()
    {

        $uti = [];

        $stmt = $this->conn->query("SELECT ac.id_uti, ac.valor_aco, ac.uti_aco, ho.id_hospital, ho.nome_hosp
         FROM tb_uti ac 
         iNNER JOIN tb_hospital as ho On  
         ac.fk_hospital = ho.id_hospital
         ORDER BY ac.id_uti DESC");
        $stmt->execute();
        $uti = $stmt->fetchAll();
        return $uti;
    }

    // mostrar acomocacao por id_uti
    public function joinutiHospitalshow($id_uti)
    {
        $stmt = $this->conn->query("SELECT ac.id_uti, 
        ac.fk_hospital, 
        ac.valor_aco, 
        ac.uti_aco, 
        ho.id_hospital, 
        ho.nome_hosp
         FROM tb_uti ac          
         iNNER JOIN tb_hospital as ho On  
         ac.fk_hospital = ho.id_hospital
         where id_uti = $id_uti   
         ");

        $stmt->execute();

        $uti = $stmt->fetch();
        return $uti;
    }
    public function findAll()
    {
    }

    public function getuti()
    {

        $uti = [];

        $stmt = $this->conn->query("SELECT * FROM tb_uti ORDER BY id_uti DESC");

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $utiArray = $stmt->fetchAll();

            foreach ($utiArray as $uti) {
                $uti[] = $this->builduti($uti);
            }
        }

        return $uti;
    }

    /**
     * Retorna dados crus de UTI por internação (usado para fallback em visitas antigas).
     */
    public function selectRawByInternacao(int $id_internacao): array
    {
        if ($id_internacao <= 0) {
            return [];
        }
        $stmt = $this->conn->prepare("SELECT * FROM tb_uti WHERE fk_internacao_uti = :id ORDER BY id_uti DESC");
        $stmt->bindValue(':id', $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retorna registros de UTI associados a uma visita.
     */
    public function selectByVisita(int $visitaId): array
    {
        if ($visitaId <= 0) {
            return [];
        }
        $stmt = $this->conn->prepare("SELECT * FROM tb_uti WHERE fk_visita_uti = :visita ORDER BY id_uti DESC");
        $stmt->bindValue(':visita', $visitaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Remove registros de UTI vinculados a uma visita específica.
     */
    public function deleteByVisita(int $visitaId): void
    {
        if ($visitaId <= 0) {
            return;
        }
        $stmt = $this->conn->prepare("DELETE FROM tb_uti WHERE fk_visita_uti = :visita");
        $stmt->bindValue(':visita', $visitaId, PDO::PARAM_INT);
        $stmt->execute();
    }


    public function findById($id_uti)
    {
        $uti = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_uti
                                    WHERE id_uti = :id_uti");

        $stmt->bindParam(":id_uti", $id_uti);
        $stmt->execute();

        $data = $stmt->fetch();
        $uti = $this->builduti($data);

        return $uti;
    }

    public function findByTitle($pesquisa_hosp)
    {

        $uti = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_uti
                                    WHERE nome_hosp LIKE :pesquisa_hosp");

        $stmt->bindValue(":nome_hosp", '%' . $pesquisa_hosp . '%');

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $utiArray = $stmt->fetchAll();

            foreach ($utiArray as $uti) {
                $uti[] = $this->builduti($uti);
            }
        }

        return $uti;
        $query = $uti;
    }
    // METODO PARA CRIAR NOVA INTERNACAO EM UTI ********** concluir *******
    public function create(uti $uti)
    {

        $stmt = $this->conn->prepare("INSERT INTO tb_uti (
        fk_internacao_uti, 
        fk_visita_uti, 
        criterios_uti, 
        data_alta_uti, 
        dva_uti, 
        data_internacao_uti, 
        especialidade_uti, 
        internacao_uti, 
        internado_uti, 
        just_uti,
        motivo_uti,
        rel_uti,
        saps_uti,
        score_uti,
        vm_uti,
        fk_user_uti,
        usuario_create_uti,
        glasgow_uti,
        dist_met_uti,
        suporte_vent_uti,
        justifique_uti,
        hora_internacao_uti
        
      ) VALUES (
        :fk_internacao_uti, 
        :fk_visita_uti, 
        :criterios_uti, 
        :data_alta_uti, 
        :dva_uti, 
        :data_internacao_uti,
        :especialidade_uti, 
        :internacao_uti,
        :internado_uti,
        :just_uti,
        :motivo_uti,
        :rel_uti,
        :saps_uti,
        :score_uti,
        :vm_uti,
        :fk_user_uti,
        :usuario_create_uti,
        :glasgow_uti,
        :dist_met_uti,
        :suporte_vent_uti,
        :justifique_uti,
        :hora_internacao_uti
        

     )");

        $stmt->bindParam(":fk_internacao_uti", $uti->fk_internacao_uti);
        $stmt->bindParam(":fk_visita_uti", $uti->fk_visita_uti);
        $stmt->bindParam(":criterios_uti", $uti->criterios_uti);
        $stmt->bindParam(":data_alta_uti", $uti->data_alta_uti);
        $stmt->bindParam(":dva_uti", $uti->dva_uti);
        $stmt->bindParam(":data_internacao_uti", $uti->data_internacao_uti);
        $stmt->bindParam(":especialidade_uti", $uti->especialidade_uti);
        $stmt->bindParam(":internacao_uti", $uti->internacao_uti);
        $stmt->bindParam(":internado_uti", $uti->internado_uti);
        $stmt->bindParam(":just_uti", $uti->just_uti);
        $stmt->bindParam(":motivo_uti", $uti->motivo_uti);
        $stmt->bindParam(":rel_uti", $uti->rel_uti);
        $stmt->bindParam(":saps_uti", $uti->saps_uti);
        $stmt->bindParam(":score_uti", $uti->score_uti);
        $stmt->bindParam(":vm_uti", $uti->vm_uti);
        $stmt->bindParam(":fk_user_uti", $uti->fk_user_uti);
        $stmt->bindParam(":usuario_create_uti", $uti->usuario_create_uti);
        $stmt->bindParam(":glasgow_uti", $uti->glasgow_uti);
        $stmt->bindParam(":dist_met_uti", $uti->dist_met_uti);
        $stmt->bindParam(":suporte_vent_uti", $uti->suporte_vent_uti);
        $stmt->bindParam(":hora_internacao_uti", $uti->hora_internacao_uti);
        $stmt->bindParam(":justifique_uti", $uti->justifique_uti);

        $stmt->execute();

        $query = $this->conn->prepare("UPDATE tb_internacao SET
        internacao_uti_int = :internacao_uti_int,
        internado_uti_int = :internado_uti_int

        WHERE id_internacao = :id_internacao
        ");

        $query->bindParam(":internacao_uti_int", $uti->internacao_uti_int);
        $query->bindParam(":internado_uti_int", $uti->internado_uti_int);
        $query->bindParam(":id_internacao", $uti->id_internacao);

        $query->execute();

        // Mensagem de sucesso por adicionar filme
        $this->message->setMessage("uti adicionado com sucesso!", "success", "cad_internacao_niveis.php");
    }

    public function update(uti $uti)
    {
        $stmt = $this->conn->prepare("UPDATE tb_uti SET
        fk_internacao_uti = :fk_internacao_uti,
        fk_visita_uti = :fk_visita_uti,
        criterios_uti = :criterios_uti,
        data_alta_uti = :data_alta_uti,
        dva_uti = :dva_uti,
        data_internacao_uti = :data_internacao_uti,
        especialidade_uti = :especialidade_uti,
        internacao_uti = :internacao_uti,
        internado_uti = :internado_uti,
        just_uti = :just_uti,
        motivo_uti = :motivo_uti,
        rel_uti = :rel_uti,
        saps_uti = :saps_uti,
        score_uti = :score_uti,
        vm_uti = :vm_uti,
        fk_user_uti = :fk_user_uti,
        usuario_create_uti = :usuario_create_uti,
        glasgow_uti = :glasgow_uti,
        dist_met_uti = :dist_met_uti,
        suporte_vent_uti = :suporte_vent_uti,
        justifique_uti = :justifique_uti,
        hora_internacao_uti = :hora_internacao_uti
    WHERE id_uti = :id_uti
    ");

        $stmt->bindParam(":fk_internacao_uti", $uti->fk_internacao_uti);
        $stmt->bindParam(":fk_visita_uti", $uti->fk_visita_uti);
        $stmt->bindParam(":criterios_uti", $uti->criterios_uti);
        $stmt->bindParam(":data_alta_uti", $uti->data_alta_uti);
        $stmt->bindParam(":dva_uti", $uti->dva_uti);
        $stmt->bindParam(":data_internacao_uti", $uti->data_internacao_uti);
        $stmt->bindParam(":especialidade_uti", $uti->especialidade_uti);
        $stmt->bindParam(":internacao_uti", $uti->internacao_uti);
        $stmt->bindParam(":internado_uti", $uti->internado_uti);
        $stmt->bindParam(":just_uti", $uti->just_uti);
        $stmt->bindParam(":motivo_uti", $uti->motivo_uti);
        $stmt->bindParam(":rel_uti", $uti->rel_uti);
        $stmt->bindParam(":saps_uti", $uti->saps_uti);
        $stmt->bindParam(":score_uti", $uti->score_uti);
        $stmt->bindParam(":vm_uti", $uti->vm_uti);
        $stmt->bindParam(":fk_user_uti", $uti->fk_user_uti);
        $stmt->bindParam(":usuario_create_uti", $uti->usuario_create_uti);
        $stmt->bindParam(":glasgow_uti", $uti->glasgow_uti);
        $stmt->bindParam(":dist_met_uti", $uti->dist_met_uti);
        $stmt->bindParam(":suporte_vent_uti", $uti->suporte_vent_uti);
        $stmt->bindParam(":justifique_uti", $uti->justifique_uti);
        $stmt->bindParam(":hora_internacao_uti", $uti->hora_internacao_uti);
        $stmt->bindParam(":id_uti", $uti->id_uti);

        $stmt->execute();

        $query = $this->conn->prepare("UPDATE tb_internacao SET
        internacao_uti_int = :internacao_uti_int,
        internado_uti_int = :internado_uti_int
    WHERE id_internacao = :id_internacao
    ");

        $query->bindParam(":internacao_uti_int", $uti->internacao_uti_int);
        $query->bindParam(":internado_uti_int", $uti->internado_uti_int);
        $query->bindParam(":id_internacao", $uti->id_internacao);

        $query->execute();

        // Mensagem de sucesso por editar uti
        $this->message->setMessage("UTI atualizado com sucesso!", "success", "list_internacao_uti.php");
    }



    // METODO PESQUISA UTI NOVA QUERY COMPLETA
    public function selectAllUTI($where = null, $order = null, $limit = null)
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
        $group = ' GROUP BY ac.id_internacao ';


        //MONTA A QUERY
        $query = $this->conn->query('SELECT DISTINCT
        uti.id_uti,
        uti.fk_internacao_uti,
        uti.criterios_uti, 
        uti.data_alta_uti, 
        uti.dva_uti, 
        uti.data_internacao_uti, 
        uti.especialidade_uti, 
        uti.internacao_uti, 
        uti.internado_uti, 
        uti.just_uti,
        uti.motivo_uti,
        uti.rel_uti,
        uti.saps_uti,
        uti.score_uti,
        uti.vm_uti,
        uti.glasgow_uti, 
        uti.dist_met_uti,
        uti.suporte_vent_uti,
        uti.justifique_uti,
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp,
        hos.fk_hospital_user,
        hos.fk_usuario_hosp,
        ac.id_internacao,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.internado_int,
        ac.internado_uti_int, 
        ac.internacao_uti_int, 
        ac.fk_paciente_int
        
        FROM tb_internacao as ac 
    
            LEFT JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital

    
			LEFT JOIN tb_hospitalUser AS hos ON
            hos.fk_hospital_user = ho.id_hospital
            
            inner JOIN tb_uti AS uti ON
            uti.fk_internacao_uti = ac.id_internacao
           
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente ' . $where . ' ' . $group . '' . $order . ' ' . $limit);

        $query->execute();

        $uti = $query->fetchAll();

        return $uti;
    }

    public function QtdInternacaoUTI($where = null, $order = null, $limit = null)
    {
        $internacao = [];
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';
        $group = ' GROUP BY ac.id_internacao ';

        $stmt = $this->conn->query('SELECT DISTINCT
        uti.id_uti,
        uti.fk_internacao_uti,
        uti.criterios_uti, 
        uti.data_alta_uti, 
        uti.dva_uti, 
        uti.data_internacao_uti, 
        uti.especialidade_uti, 
        uti.internacao_uti, 
        uti.internado_uti, 
        uti.just_uti,
        uti.motivo_uti,
        uti.rel_uti,
        uti.saps_uti,
        uti.score_uti,
        uti.vm_uti,
        uti.glasgow_uti, 
        uti.dist_met_uti,
        uti.suporte_vent_uti,
        uti.justifique_uti,
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp,
        ac.id_internacao,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.internado_int,
        ac.internado_uti_int, 
        ac.internacao_uti_int, 
        ac.fk_paciente_int,
        COUNT(id_uti) as qtd
        
        FROM tb_internacao as ac 
    
            LEFT JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital
    
			LEFT JOIN tb_hospitalUser AS hos ON
            hos.fk_hospital_user = ho.id_hospital

            inner JOIN tb_uti AS uti ON
            uti.fk_internacao_uti = ac.id_internacao
            
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente '
            . $where . '  ' . $group . '' . $order . ' ' . $limit);

        $stmt->execute();

        $QtdTotalInt = $stmt->fetch();

        return $QtdTotalInt;
    }

    // METODO PESQUISA UTI NOVA QUERY COMPLETA COM FILTRO POR USUARIO/HOSPITAL 
    //********************************************************** */
    public function selectAllUTIList($where = null, $order = null, $limit = null)
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        //MONTA A QUERY
        $query = $this->conn->query('SELECT DISTINCT
        uti.id_uti,
        uti.fk_internacao_uti,
        uti.criterios_uti, 
        uti.data_alta_uti, 
        uti.dva_uti, 
        uti.data_internacao_uti, 
        uti.especialidade_uti, 
        uti.internacao_uti, 
        uti.internado_uti, 
        uti.just_uti,
        uti.motivo_uti,
        uti.rel_uti,
        uti.saps_uti,
        uti.score_uti,
        uti.vm_uti,
        uti.glasgow_uti, 
        uti.dist_met_uti,
        uti.suporte_vent_uti,
        uti.justifique_uti,
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp,
        hos.fk_hospital_user,
        hos.fk_usuario_hosp,
        ac.id_internacao,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.internado_int,
        ac.internado_uti_int, 
        ac.internacao_uti_int, 
        ac.fk_paciente_int
        
        FROM tb_internacao as ac 
    
            inner JOIN tb_uti AS uti ON
            uti.fk_internacao_uti = ac.id_internacao
            
            INNER JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital

            LEFT JOIN tb_hospitalUser AS hos ON
            hos.fk_hospital_user = ho.id_hospital
    
            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente
            ' . $where . ' ' . $order . ' ' . $limit);

        $query->execute();

        $uti = $query->fetchAll();

        return $uti;
    }

    public function QtdInternacaoUTIList($where = null)
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';

        $stmt = $this->conn->query('SELECT 
        COUNT(uti.id_uti) as qtd, 
        uti.id_uti,
        uti.fk_internacao_uti,
        uti.criterios_uti, 
        uti.data_alta_uti, 
        uti.dva_uti, 
        uti.data_internacao_uti, 
        uti.especialidade_uti, 
        uti.internacao_uti, 
        uti.internado_uti, 
        uti.just_uti,
        uti.motivo_uti,
        uti.rel_uti,
        uti.saps_uti,
        uti.score_uti,
        uti.vm_uti,
        uti.glasgow_uti, 
        uti.dist_met_uti,
        uti.suporte_vent_uti,
        uti.justifique_uti,
        pa.id_paciente,
        pa.nome_pac,
        ho.id_hospital, 
        ho.nome_hosp,
        ac.id_internacao,
        ac.fk_hospital_int,
        ac.data_intern_int,
        ac.internado_int,
        ac.internado_uti_int, 
        ac.internacao_uti_int, 
        ac.fk_paciente_int
        
            FROM tb_uti as uti 
    
            INNER JOIN tb_internacao AS ac ON
            uti.fk_internacao_uti = ac.id_internacao
            
            INNER JOIN tb_hospital AS ho ON  
            ac.fk_hospital_int = ho.id_hospital

            INNER JOIN tb_paciente AS pa ON
            ac.fk_paciente_int = pa.id_paciente ' . $where);

        $stmt->execute();

        $QtdTotalInt = $stmt->fetch();

        return $QtdTotalInt;
    }
    public function findByIdUpdate($uti) //ainda nao corrigido
    {

        $stmt = $this->conn->prepare("UPDATE tb_uti SET
        fk_internacao_uti = :fk_internacao_uti,
        criterios_uti = :alto_custo_uti,
        rel_alto_custo_uti = :rel_alto_custo_uti,
        evento_adverso_uti = :evento_adverso_uti,
        rel_evento_adverso_uti = :rel_evento_adverso_uti,
        tipo_evento_adverso_utit = :tipo_evento_adverso_utit,
        opme_uti = :opme_uti,
        rel_opme_uti = :rel_opme_uti,
        home_care_uti = :home_care_uti,
        rel_home_care_uti = :rel_home_care_uti,
        desospitalizacao_uti = :desospitalizacao_uti,
        rel_desospitalizacao_uti = :rel_desospitalizacao_uti,
        glasgow_uti = :glasgow_uti,
        dist_met_uti = :dist_met_uti,
        suporte_vent_uti = :suporte_vent_uti,
        hora_internacao_uti = :hora_internacao_uti,
        justifique_uti = :justifique_uti

        WHERE id_uti = :id_uti 
      ");

        $stmt->bindParam(":fk_internacao_uti", $uti->fk_internacao_uti);
        $stmt->bindParam(":alto_custo_uti", $uti->alto_custo_uti);
        $stmt->bindParam(":rel_alto_custo_uti", $uti->rel_alto_custo_uti);
        $stmt->bindParam(":evento_adverso_uti", $uti->evento_adverso_uti);
        $stmt->bindParam(":rel_evento_adverso_uti", $uti->rel_evento_adverso_uti);
        $stmt->bindParam(":tipo_evento_adverso_utit", $uti->tipo_evento_adverso_utit);
        $stmt->bindParam(":opme_uti", $uti->opme_uti);
        $stmt->bindParam(":rel_opme_uti", $uti->rel_opme_uti);
        $stmt->bindParam(":home_care_uti", $uti->home_care_uti);
        $stmt->bindParam(":rel_home_care_uti", $uti->rel_home_care_uti);
        $stmt->bindParam(":rel_home_care_uti", $uti->rel_home_care_uti);
        $stmt->bindParam(":rel_desospitalizacao_uti", $uti->rel_desospitalizacao_uti);
        $stmt->bindParam(":glasgow_uti", $uti->glasgow_uti);
        $stmt->bindParam(":dist_met_uti", $uti->dist_met_uti);
        $stmt->bindParam(":suporte_vent_uti", $uti->suporte_vent_uti);
        $stmt->bindParam(":justifique_uti", $uti->justifique_uti);
        $stmt->bindParam(":hora_internacao_uti", $uti->hora_internacao_uti);

        $stmt->bindParam(":id_uti", $uti->id_uti);
        $stmt->execute();

        // Mensagem de sucesso por editar uti
        $this->message->setMessage("uti atualizado com sucesso!", "success", "list_internacao_uti.php");
    }
    // METODO PARA DAR ALTA DA UTI
    public function findAltaUpdate($UTIData) //ainda nao corrigido
    {

        $stmt = $this->conn->prepare("UPDATE tb_uti SET
        fk_internacao_uti = :fk_internacao_uti,
        data_alta_uti = :data_alta_uti,
        internado_uti = :internado_uti

        WHERE id_uti = :id_uti 
      ");

        $stmt->bindParam(":fk_internacao_uti", $UTIData->fk_internacao_uti);
        $stmt->bindParam(":data_alta_uti", $UTIData->data_alta_uti);
        $stmt->bindParam(":internado_uti", $UTIData->internado_uti);

        $stmt->bindParam(":id_uti", $UTIData->id_uti);
        $stmt->execute();

        // Mensagem de sucesso por editar uti
        $this->message->setMessage("uti atualizado com sucesso!", "success", "list_internacao_uti.php");
    }

    public function destroy($id_uti)
    {
        $stmt = $this->conn->prepare("DELETE FROM tb_uti WHERE id_uti = :id_uti");

        $stmt->bindParam(":id_uti", $id_uti);

        $stmt->execute();

        // Mensagem de sucesso por remover filme
        $this->message->setMessage("uti removido com sucesso!", "success", "list_internacao_uti.php");
    }

    public function selectInternacaoUti(int $idInternacao): array
    {
        $sql = '
        SELECT
            pr.id_uti,

            /* datas / horários */
            pr.data_internacao_uti         AS entrada,
            pr.hora_internacao_uti         AS hora,
            pr.data_alta_uti               AS saida,

            /* informações clínicas */
            pr.motivo_uti,
            pr.rel_uti,
            pr.criterios_uti,
            pr.just_uti,
            pr.vm_uti                      AS vent,          -- s / n
            pr.saps_uti,
            pr.score_uti,
            pr.glasgow_uti,
            pr.dist_met_uti,
            pr.suporte_vent_uti,
            pr.justifique_uti,
            pr.dva_uti,
            pr.vm_uti,
            /* dados administrativos */
            pr.especialidade_uti,
            pr.internacao_uti,
            pr.internado_uti,
            pr.fk_visita_uti,

            /* quem registrou */
            pr.fk_user_uti,
            pr.usuario_create_uti

        FROM tb_uti AS pr
        WHERE pr.fk_internacao_uti = :id
        ORDER BY pr.data_internacao_uti, pr.hora_internacao_uti
    ';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $idInternacao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findGeral()
    {

        $uti = [];

        $stmt = $this->conn->query("SELECT * FROM tb_uti ORDER BY id_uti DESC");

        $stmt->execute();

        $uti = $stmt->fetchAll();

        return $uti;
    }
    // pegar id max da internacao
    public function findMaxUTI()
    {

        $uti = [];

        $stmt = $this->conn->query("SELECT max(id_internacao) as ultimoReg from tb_internacao");

        $stmt->execute();

        $utiIdMax = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $utiIdMax;
    }
    public function findMaxUtiInt()
    {

        $gestao = [];

        $stmt = $this->conn->query("SELECT max(id_internacao) as ultimoReg from tb_internacao");

        $stmt->execute();

        $findMaxUtiInt = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $findMaxUtiInt;
    }

    public function findUTIInternacao($id_internacao)
    {

        // $gestao = [];
        $stmt = $this->conn->query("SELECT 
        ac.id_internacao, 
        ac.internado_int, 
        ac.data_intern_int, 
        ac.fk_paciente_int, 
        ac.internado_uti_int, 
        pa.id_paciente, 
        pa.nome_pac, 
        ac.usuario_create_int, 
        ac.fk_hospital_int, 
        ac.modo_internacao_int, 
        ac.tipo_admissao_int, 
        ho.id_hospital, 
        ho.nome_hosp, 
        ac.especialidade_int, 
        ac.titular_int, 
        ac.data_visita_int, 
        ac.grupo_patologia_int, 
        ac.acomodacao_int, 
        ac.fk_patologia_int, 
        pat.patologia_pat, 
        ut.fk_internacao_uti, 
        ut.id_uti

        FROM tb_internacao ac 

        left JOIN tb_hospital as ho On  
        ac.fk_hospital_int = ho.id_hospital

        left join tb_paciente as pa on
        ac.fk_paciente_int = pa.id_paciente

        left join tb_patologia as pat on
        ac.fk_patologia_int = pat.id_patologia

        inner join tb_uti as ut on
        ac.id_internacao = ut.fk_internacao_uti

        WHERE ac.id_internacao = $id_internacao
         ");

        $stmt->execute();

        $findUTIInternacao = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $findUTIInternacao;
    }
}
