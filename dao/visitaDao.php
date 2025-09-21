<?php

require_once("./models/visita.php");
require_once("./models/message.php");

// Review DAO
require_once("dao/visitaDao.php");

class visitaDAO implements visitaDAOInterface
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

    public function buildvisita($data)
    {
        $visita = new visita();

        $visita->fk_internacao_vis = $data["fk_internacao_vis"];
        $visita->rel_visita_vis = $data["rel_visita_vis"];
        $visita->acoes_int_vis = $data["acoes_int_vis"];
        $visita->usuario_create = $data["usuario_create"];
        $visita->data_visita_vis = $data["data_visita_vis"];
        $visita->visita_no_vis = $data["visita_no_vis"];
        $visita->visita_auditor_prof_med = $data["visita_auditor_prof_med"];
        $visita->visita_auditor_prof_enf = $data["visita_auditor_prof_enf"];
        $visita->visita_med_vis = $data["visita_med_vis"];
        $visita->visita_enf_vis = $data["visita_enf_vis"];
        $visita->fk_usuario_vis = $data["fk_usuario_vis"];
        $visita->exames_enf = $data["exames_enf"];
        $visita->retificado = $data["retificado"];

        $visita->fk_visita_prorr = $data["fk_visita_prorr"];

        return $visita;
    }

    public function joinvisitaHospital()
    {

        $visita = [];

        $stmt = $this->conn->query("SELECT ac.id_visita, 
        ac.valor_diaria, 
        ac.visitaNome, 
        ho.id_hospital, 
        ho.hospitalNome
         FROM tb_visita ac 
         iNNER JOIN tb_hospital as ho On  
         ac.fk_hospital = ho.id_hospital
         ORDER BY ac.id_visita asc");
        $stmt->execute();
        $visita = $stmt->fetchAll();
        return $visita;
    }

    // MÉTODO PARA PESQUISAR VISITA POR INTERNACAO
    public function joinVisitaInternacao($id_visita)
    {

        $stmt = $this->conn->query("SELECT 
        ac.id_internacao, 
        ac.acoes_int, 
        ac.data_intern_int, 
        ac.data_visita_int, 
        ac.rel_int, 
        ac.fk_paciente_int, 
        ac.usuario_create_int, 
        ac.fk_hospital_int, 
        ac.modo_internacao_int, 
        ac.tipo_admissao_int,
        ac.especialidade_int, 
        ac.titular_int, 
        ac.grupo_patologia_int, 
        ac.acomodacao_int, 
        ac.fk_patologia_int, 
        ac.fk_patologia2, 
        ac.internado_int,
        ac.visita_no_int,
        ac.primeira_vis_int,
        pa.id_paciente,
        pa.nome_pac,
        vi.fk_internacao_vis, 
        vi.rel_visita_vis, 
        vi.acoes_int_vis, 
        vi.usuario_create,
        vi.visita_auditor_prof_med,
        vi.visita_auditor_prof_enf,
        vi.visita_med_vis,
        vi.visita_enf_vis,
        vi.visita_no_vis,
        vi.fk_usuario_vis,
        vi.data_visita_vis,
        vi.id_visita,
        ho.id_hospital, 
        ho.nome_hosp 
    
        FROM tb_internacao ac 
    
            left JOIN tb_hospital as ho On  
            ac.fk_hospital_int = ho.id_hospital
    
            RIGHT JOIN tb_visita as vi On  
            ac.id_internacao = vi.fk_internacao_vis
    
            left join tb_paciente as pa on
            ac.fk_paciente_int = pa.id_paciente

            where vi.fk_internacao_vis = $id_visita and vi.retificado IS NULL

         ORDER BY vi.data_visita_vis DESC");
        $stmt->execute();
        $visita = $stmt->fetchAll();
        return $visita;
    }
    public function joinVisitaInternacaoMax($id_visita)
    {

        $stmt = $this->conn->query("SELECT 
        ac.id_internacao, 
        ac.acoes_int, 
        ac.data_intern_int, 
        ac.data_visita_int, 
        ac.rel_int, 
        ac.fk_paciente_int, 
        ac.usuario_create_int, 
        ac.fk_hospital_int, 
        ac.modo_internacao_int, 
        ac.tipo_admissao_int,
        ac.especialidade_int, 
        ac.titular_int, 
        ac.grupo_patologia_int, 
        ac.acomodacao_int, 
        ac.fk_patologia_int, 
        ac.fk_patologia2, 
        ac.internado_int,
        ac.visita_no_int,
        ac.primeira_vis_int,
        pa.id_paciente,
        pa.nome_pac,
        vi.fk_internacao_vis, 
        vi.rel_visita_vis, 
        vi.acoes_int_vis, 
        vi.usuario_create,
        vi.visita_auditor_prof_med,
        vi.visita_auditor_prof_enf,
        vi.visita_med_vis,
        vi.visita_enf_vis,
        vi.visita_no_vis,
        vi.fk_usuario_vis,
        vi.data_visita_vis,
        vi.id_visita,
        ho.id_hospital, 
        ho.nome_hosp

    
        FROM tb_internacao ac 
    
            left JOIN tb_hospital as ho On  
            ac.fk_hospital_int = ho.id_hospital
    
            RIGHT JOIN tb_visita as vi On  
            ac.id_internacao = vi.fk_internacao_vis
    
            left join tb_paciente as pa on
            ac.fk_paciente_int = pa.id_paciente

            where vi.fk_internacao_vis = $id_visita

         ORDER BY vi.id_visita DESC");

        $stmt->execute();

        $visita = $stmt->fetchAll();

        return $visita;
    }
    public function joinVisitaInternacaoShow($id_visita)
    {

        // $visita = [];

        $stmt = $this->conn->query("SELECT 
        ac.id_internacao, 
        ac.acoes_int, 
        ac.data_intern_int, 
        ac.data_visita_int, 
        ac.rel_int, 
        ac.fk_paciente_int, 
        ac.usuario_create_int, 
        ac.fk_hospital_int, 
        ac.modo_internacao_int, 
        ac.tipo_admissao_int,
        ac.especialidade_int, 
        ac.titular_int, 
        ac.grupo_patologia_int, 
        ac.acomodacao_int, 
        ac.fk_patologia_int, 
        ac.fk_patologia2, 
        ac.internado_int,
        ac.visita_no_int,
        ac.primeira_vis_int,
        pa.id_paciente,
        pa.nome_pac,
        vi.fk_internacao_vis, 
        vi.rel_visita_vis, 
        vi.acoes_int_vis, 
        vi.usuario_create,
        vi.visita_auditor_prof_med,
        vi.visita_auditor_prof_enf,
        vi.visita_med_vis,
        vi.visita_enf_vis,
        vi.visita_no_vis,
        vi.fk_usuario_vis,
        vi.data_visita_vis,
        vi.id_visita,
        ho.id_hospital, 
        ho.nome_hosp 
    
        FROM tb_internacao ac 
    
            left JOIN tb_hospital as ho On  
            ac.fk_hospital_int = ho.id_hospital
    
            RIGHT JOIN tb_visita as vi On  
            ac.id_internacao = vi.fk_internacao_vis
    
            left join tb_paciente as pa on
            ac.fk_paciente_int = pa.id_paciente

            where vi.id_visita = $id_visita

         ORDER BY ac.id_internacao asc");

        $stmt->execute();
        $visita = $stmt->fetchAll();
        return $visita;
    }
    public function joinVisitaShow($id_visita)
    {

        // $visita = [];

        $stmt = $this->conn->query("SELECT 
        ac.id_internacao, 
        ac.acoes_int, 
        ac.data_intern_int, 
        ac.data_visita_int, 
        ac.rel_int, 
        ac.fk_paciente_int, 
        ac.usuario_create_int, 
        ac.fk_hospital_int, 
        ac.modo_internacao_int, 
        ac.tipo_admissao_int,
        ac.especialidade_int, 
        ac.titular_int, 
        ac.grupo_patologia_int, 
        ac.acomodacao_int, 
        ac.fk_patologia_int, 
        ac.fk_patologia2, 
        ac.internado_int,
        ac.visita_no_int,
        ac.primeira_vis_int,
        pa.id_paciente,
        pa.nome_pac,
        vi.fk_internacao_vis, 
        vi.rel_visita_vis, 
        vi.acoes_int_vis, 
        vi.usuario_create,
        vi.visita_auditor_prof_med,
        vi.visita_auditor_prof_enf,
        vi.visita_med_vis,
        vi.visita_enf_vis,
        vi.visita_no_vis,
        vi.fk_usuario_vis,
        vi.data_visita_vis,
        vi.id_visita,
        ho.id_hospital, 
        ho.nome_hosp 
    
        FROM tb_internacao ac 
    
            left JOIN tb_hospital as ho On  
            ac.fk_hospital_int = ho.id_hospital
    
            RIGHT JOIN tb_visita as vi On  
            ac.id_internacao = vi.fk_internacao_vis
    
            left join tb_paciente as pa on
            ac.fk_paciente_int = pa.id_paciente

            where vi.id_visita = $id_visita

         ORDER BY ac.id_internacao asc");

        $stmt->execute();
        $visita = $stmt->fetchAll();
        return $visita;
    }
    // mostrar acomocacao por id_visita
    public function joinvisitaHospitalshow($id_visita)
    {
        $stmt = $this->conn->query("SELECT ac.id_visita, 
        ac.fk_hospital, 
        ac.valor_diaria, 
        ac.visitaNome, 
        ho.id_hospital, 
        ho.hospitalNome
         FROM tb_visita ac          
         iNNER JOIN tb_hospital as ho On  
         ac.fk_hospital = ho.id_hospital
         where id_visita = $id_visita   
         ");

        $stmt->execute();

        $visita = $stmt->fetch();
        return $visita;
    }
    public function findAll() {}

    public function getvisita()
    {

        $visita = [];

        $stmt = $this->conn->query("SELECT * FROM tb_visita ORDER BY id_visita asc");

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $visitaArray = $stmt->fetchAll();

            foreach ($visitaArray as $visita) {
                $visita[] = $this->buildvisita($visita);
            }
        }

        return $visita;
    }

    public function getvisitaByNome($nome)
    {

        $visita = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_visita
                                    WHERE visitaNome = :visitaNome
                                    ORDER BY id_visita asc");

        $stmt->bindParam(":visitaNome", $visitaNome);

        $stmt->execute();

        return $visita;
    }

    public function findById($id_visita)
    {
        $visita = [];
        $stmt = $this->conn->prepare("SELECT * FROM tb_visita
                                    WHERE id_visita = $id_visita");

        $stmt->bindParam(":id_visita", $id_visita);
        $stmt->execute();

        $data = $stmt->fetch();
        //var_dump($data);
        $visita = $this->buildvisita($data);

        return $visita;
    }

    public function findByIdUpdate($id_visita)
    {

        $visita = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_visita
                                    WHERE id_visita = :id_visita");

        $stmt->bindValue(":id_visita", $id_visita);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $visitaArray = $stmt->fetchAll();

            foreach ($visitaArray as $visita) {
                $visita[] = $this->buildvisita($visita);
            }
        }

        return $visita;
    }

    public function create(visita $visita)
    {

        $stmt = $this->conn->prepare("INSERT INTO tb_visita (
        fk_internacao_vis, 
        rel_visita_vis, 
        acoes_int_vis, 
        usuario_create,
        visita_auditor_prof_med,
        visita_auditor_prof_enf,
        visita_med_vis,
        visita_enf_vis,
        visita_no_vis,
        fk_usuario_vis,
        data_visita_vis,
        exames_enf,
        oportunidades_enf,
        programacao_enf,
        retificou
         
      ) VALUES (
        :fk_internacao_vis, 
        :rel_visita_vis, 
        :acoes_int_vis, 
        :usuario_create,
        :visita_auditor_prof_med,
        :visita_auditor_prof_enf,
        :visita_med_vis,
        :visita_enf_vis,
        :visita_no_vis,
        :fk_usuario_vis,
        :data_visita_vis,
        :exames_enf,
        :oportunidades_enf,
        :programacao_enf,
        :retificou


     )");

        $stmt->bindParam(":fk_internacao_vis", $visita->fk_internacao_vis);
        $stmt->bindParam(":rel_visita_vis", $visita->rel_visita_vis);
        $stmt->bindParam(":acoes_int_vis", $visita->acoes_int_vis);
        $stmt->bindParam(":usuario_create", $visita->usuario_create);
        $stmt->bindParam(":visita_auditor_prof_med", $visita->visita_auditor_prof_med);
        $stmt->bindParam(":visita_auditor_prof_enf", $visita->visita_auditor_prof_enf);
        $stmt->bindParam(":visita_med_vis", $visita->visita_med_vis);
        $stmt->bindParam(":visita_enf_vis", $visita->visita_enf_vis);
        $stmt->bindParam(":visita_no_vis", $visita->visita_no_vis);
        $stmt->bindParam(":fk_usuario_vis", $visita->fk_usuario_vis);
        $stmt->bindParam(":data_visita_vis", $visita->data_visita_vis);
        $stmt->bindParam(":exames_enf", $visita->exames_enf);
        $stmt->bindParam(":oportunidades_enf", $visita->oportunidades_enf);
        $stmt->bindParam(":programacao_enf", $visita->programacao_enf);
        $stmt->bindParam(":retificou", $visita->retificou);

        $stmt->execute();

        // Mensagem de sucesso por adicionar visita
        $this->message->setMessage("visita adicionado com sucesso!", "success", "list_visita.php");
    }

    public function update($visita)
    {

        $stmt = $this->conn->prepare("UPDATE tb_visita SET
        visitaNome = :visitaNome,
        valor_diaria = :valor_diaria,
        fk_hospital = :fk_hospital
        WHERE id_visita = :id_visita 
      ");

        $stmt->bindParam(":visitaNome", $visita['visitaNome']);
        $stmt->bindParam(":valor_diaria", $visita['valor_diaria']);
        $stmt->bindParam(":fk_hospital", $visita['fk_hospital']);
        $stmt->bindParam(":id_visita", $visita['id_visita']);

        // $stmt->bindParam(":data_create", $visita['data_create']);
        // $stmt->bindParam(":usuario_create", $visita['usuario_create']);
        $stmt->execute();

        // Mensagem de sucesso por editar visita
        $this->message->setMessage("visita atualizado com sucesso!", "success", "list_visita.php");
    }


    public function retificarVisita(int $id_internacao, int $visita_no_vis)
    {
        $stmt = $this->conn->prepare("
            UPDATE tb_visita
            SET retificado = 1
            WHERE fk_internacao_vis = :id_internacao and visita_no_vis = :visita_no_vis
        ");

        $stmt->bindParam(':id_internacao', $id_internacao, PDO::PARAM_INT);
        $stmt->bindParam(':visita_no_vis', $visita_no_vis, PDO::PARAM_INT);
        $stmt->execute();
    }


    public function destroy($id_visita)
    {
        $stmt = $this->conn->prepare("DELETE FROM tb_visita WHERE id_visita = :id_visita");

        $stmt->bindParam(":id_visita", $id_visita);

        $stmt->execute();

        // Mensagem de sucesso por remover filme
        $this->message->setMessage("visita removido com sucesso!", "success", "list_visita.php");
    }


    public function findGeral()
    {

        $visita = [];

        $stmt = $this->conn->query("SELECT * FROM tb_visita ORDER BY id_visita asc");

        $stmt->execute();

        $visita = $stmt->fetch();

        return $visita;
    }

    public function findGeralByIntern($id_internacao)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tb_visita WHERE fk_internacao_vis = :id_internacao AND retificado IS NULL ORDER BY visita_no_vis DESC");
        $stmt->bindParam(':id_internacao', $id_internacao, PDO::PARAM_INT);
        $stmt->execute();

        $visita = $stmt->fetchAll();

        return $visita;
    }




    public function selectUltimaVisitaComInternacao($where)
    {
        // Valida e monta o filtro
        $where = strlen($where) ? ' WHERE ' . $where : '';

        // Query
        $sql = "SELECT 
        vi.id_visita,
        vi.data_visita_vis,
        vi.fk_internacao_vis, 
        vi.usuario_create,
        vi.visita_auditor_prof_med,
        vi.visita_auditor_prof_enf,
        vi.visita_med_vis,
        vi.visita_enf_vis,
        vi.visita_no_vis,
        vi.fk_usuario_vis,
        ac.id_internacao, 
        ac.data_intern_int, 
        ac.data_visita_int,
        ac.senha_int, 
        ac.internado_int,
        ac.visita_no_int,
        pa.id_paciente,
        pa.nome_pac,
        se.id_usuario,
        se.usuario_user,
        se.email_user,
        se.cargo_user,
        se.nivel_user,
        se.ativo_user,
        ho.id_hospital,
        ho.nome_hosp, 
        hos.fk_hospital_user,
        hos.fk_usuario_hosp,
        DATEDIFF(CURRENT_DATE, vi.data_visita_vis) AS dias_desde_ultima_visita
    FROM tb_visita vi
    LEFT JOIN tb_internacao ac ON 
        vi.fk_internacao_vis = ac.id_internacao

    LEFT JOIN tb_hospital AS ho ON  
        ac.fk_hospital_int = ho.id_hospital

    LEFT JOIN tb_hospitalUser AS hos ON
        hos.fk_hospital_user = ho.id_hospital
        
	LEFT JOIN tb_user AS se ON  
        se.id_usuario = hos.fk_usuario_hosp

    LEFT JOIN tb_paciente AS pa ON
        ac.fk_paciente_int = pa.id_paciente 

    

    " . $where . "
    ORDER BY vi.data_visita_vis DESC 
    LIMIT 1";

        // Executa a consulta
        $query = $this->conn->query($sql);

        // Obtém os resultados
        $hospital = $query->fetchAll();

        return $hospital;
    }
}
