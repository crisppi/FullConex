    <?php

    require_once("./models/detalhes.php");
    require_once("./models/message.php");

    // Review DAO
    require_once("dao/detalhesDao.php");

    class detalhesDAO implements detalhesDAOInterface
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

        public function builddetalhes($data)
        {
            $detalhes = new detalhes();

            $detalhes->fk_vis_det = $data["fk_vis_det"];
            $detalhes->fk_int_det = $data["fk_int_det"];

            $detalhes->curativo_det = $data["curativo_det"];
            $detalhes->dieta_det = $data["dieta_det"];
            $detalhes->nivel_consc_det = $data["nivel_consc_det"];
            $detalhes->oxig_det = $data["oxig_det"];
            $detalhes->oxig_uso_det = $data["oxig_uso_det"];
            $detalhes->qt_det = $data["qt_det"];
            $detalhes->dispositivo_det = $data["dispositivo_det"];
            $detalhes->atb_det = $data["atb_det"];
            $detalhes->atb_uso_det = $data["atb_uso_det"];
            $detalhes->acamado_det = $data["acamado_det"];
            $detalhes->exames_det = $data["exames_det"];
            $detalhes->oportunidades_det = $data["oportunidades_det"];
            $detalhes->hemoderivados_det = $data["hemoderivados_det"];
            $detalhes->oxigenio_hiperbarica_det = $data["oxigenio_hiperbarica_det"];
            $detalhes->dialise_det = $data["dialise_det"];

            $detalhes->tqt_det = $data["tqt_det"];
            $detalhes->svd_det = $data["svd_det"];
            $detalhes->gtt_det = $data["gtt_det"];
            $detalhes->dreno_det = $data["dreno_det"];
            $detalhes->rt_det = $data["rt_det"];
            $detalhes->lesoes_pele_det = $data["lesoes_pele_det"];
            $detalhes->medic_alto_custo_det = $data["medic_alto_custo_det"];
            $detalhes->qual_medicamento_det = $data["qual_medicamento_det"];

            $detalhes->paliativos_det = $data["paliativos_det"];
            $detalhes->braden_det = $data["braden_det"];
            $detalhes->liminar_det = $data["liminar_det"];
            $detalhes->parto_det = $data["parto_det"];

            return $detalhes;
        }

        public function create(detalhes $detalhes)
        {

            $stmt = $this->conn->prepare("INSERT INTO tb_detalhes (
            fk_int_det,
            fk_vis_det,
            curativo_det,
            dieta_det,
            nivel_consc_det,
            oxig_det,
            oxig_uso_det,
            qt_det,
            dispositivo_det,
            atb_det,
            atb_uso_det,
            acamado_det,
            exames_det,
            oportunidades_det,
            tqt_det,
            svd_det,
            gtt_det,
            dreno_det,
            rt_det,
            lesoes_pele_det,
            medic_alto_custo_det,
            qual_medicamento_det,
            paliativos_det,
            braden_det,
            liminar_det,
            parto_det,
            hemoderivados_det,
            dialise_det,
            oxigenio_hiperbarica_det
    
        ) VALUES (
            :fk_int_det,
            :fk_vis_det,
            :curativo_det,
            :dieta_det,
            :nivel_consc_det,
            :oxig_det,
            :oxig_uso_det,
            :qt_det,
            :dispositivo_det,
            :atb_det,
            :atb_uso_det,
            :acamado_det,
            :exames_det,
            :oportunidades_det,
            :tqt_det,
            :svd_det,
            :gtt_det,
            :dreno_det,
            :rt_det,
            :lesoes_pele_det,
            :medic_alto_custo_det,
            :qual_medicamento_det,
            :paliativos_det,
            :braden_det,
            :liminar_det,
            :parto_det,
            :hemoderivados_det,
            :dialise_det,
            :oxigenio_hiperbarica_det
    
        )");

            $stmt->bindParam(":fk_int_det", $detalhes->fk_int_det);
            $stmt->bindParam(":fk_vis_det", $detalhes->fk_vis_det);
            $stmt->bindParam(":curativo_det", $detalhes->curativo_det);
            $stmt->bindParam(":dieta_det", $detalhes->dieta_det);
            $stmt->bindParam(":nivel_consc_det", $detalhes->nivel_consc_det);
            $stmt->bindParam(":oxig_det", $detalhes->oxig_det);
            $stmt->bindParam(":oxig_uso_det", $detalhes->oxig_uso_det);
            $stmt->bindParam(":qt_det", $detalhes->qt_det);
            $stmt->bindParam(":dispositivo_det", $detalhes->dispositivo_det);
            $stmt->bindParam(":atb_det", $detalhes->atb_det);
            $stmt->bindParam(":atb_uso_det", $detalhes->atb_uso_det);
            $stmt->bindParam(":acamado_det", $detalhes->acamado_det);
            $stmt->bindParam(":exames_det", $detalhes->exames_det, PDO::PARAM_STR);
            $stmt->bindParam(":oportunidades_det", $detalhes->oportunidades_det, PDO::PARAM_STR);
            $stmt->bindParam(":tqt_det", $detalhes->tqt_det);
            $stmt->bindParam(":svd_det", $detalhes->svd_det);
            $stmt->bindParam(":gtt_det", $detalhes->gtt_det);
            $stmt->bindParam(":dreno_det", $detalhes->dreno_det);
            $stmt->bindParam(":rt_det", $detalhes->rt_det);
            $stmt->bindParam(":lesoes_pele_det", $detalhes->lesoes_pele_det);
            $stmt->bindParam(":medic_alto_custo_det", $detalhes->medic_alto_custo_det);
            $stmt->bindParam(":qual_medicamento_det", $detalhes->qual_medicamento_det, PDO::PARAM_STR);
            $stmt->bindParam(":paliativos_det", $detalhes->paliativos_det);
            $stmt->bindParam(":braden_det", $detalhes->braden_det);
            $stmt->bindParam(":liminar_det", $detalhes->liminar_det);
            $stmt->bindParam(":parto_det", $detalhes->parto_det);
            $stmt->bindParam(":oxigenio_hiperbarica_det", $detalhes->oxigenio_hiperbarica_det);
            $stmt->bindParam(":hemoderivados_det", $detalhes->hemoderivados_det);
            $stmt->bindParam(":dialise_det", $detalhes->dialise_det);
            // $stmt->debugDumpParams();   // <- mostra SQL + binds

            $stmt->execute();

            // Mensagem de sucesso por adicionar filme
            $this->message->setMessage("detalhes adicionado com sucesso!", "success", "list_detalhes.php");
        }


        public function findById($id_internacao)
        {
            $detalhes = [];
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes
                                        WHERE fk_int_det = :id_internacao");
            $stmt->bindParam(":id_internacao", $id_internacao);
            $stmt->execute();

            $data = $stmt->fetch();


            // var_dump($data);
            if ($data != null) {
                $detalhes = $this->builddetalhes($data);
            }


            return $detalhes;
        }
        // detalhesDAO.php
        public function findByInternacao($idInternacao)
        {
            $stmt = $this->conn->prepare(
                "SELECT * FROM tb_detalhes WHERE fk_int_det = :idInt"
            );
            $stmt->bindParam(':idInt', $idInternacao, PDO::PARAM_INT);
            $stmt->execute();

            // Caso exista mais de um detalhe para a mesma internação
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        public function update(detalhes $detalhes)
        {
            $stmt = $this->conn->prepare("UPDATE tb_detalhes SET
            curativo_det              = :curativo_det,
            dieta_det                 = :dieta_det,
            nivel_consc_det           = :nivel_consc_det,
            oxig_det                  = :oxig_det,
            oxig_uso_det              = :oxig_uso_det,
            qt_det                    = :qt_det,
            dispositivo_det           = :dispositivo_det,
            atb_det                   = :atb_det,
            atb_uso_det               = :atb_uso_det,
            acamado_det               = :acamado_det,
            exames_det                = :exames_det,
            oportunidades_det         = :oportunidades_det,
            tqt_det                   = :tqt_det,
            svd_det                   = :svd_det,
            gtt_det                   = :gtt_det,
            dreno_det                 = :dreno_det,
            rt_det                    = :rt_det,
            lesoes_pele_det           = :lesoes_pele_det,
            medic_alto_custo_det      = :medic_alto_custo_det,
            qual_medicamento_det      = :qual_medicamento_det,
            paliativos_det            = :paliativos_det,
            braden_det                = :braden_det,
            liminar_det               = :liminar_det,
            parto_det                 = :parto_det,
            hemoderivados_det         = :hemoderivados_det,
            dialise_det               = :dialise_det,
            oxigenio_hiperbarica_det  = :oxigenio_hiperbarica_det
        WHERE fk_int_det = :fk_int_det
    ");
            $stmt->bindParam(':fk_int_det',             $detalhes->fk_int_det,          PDO::PARAM_INT);
            $stmt->bindParam(':curativo_det',           $detalhes->curativo_det);
            $stmt->bindParam(':dieta_det',              $detalhes->dieta_det);
            $stmt->bindParam(':nivel_consc_det',        $detalhes->nivel_consc_det);
            $stmt->bindParam(':oxig_det',               $detalhes->oxig_det);
            $stmt->bindParam(':oxig_uso_det',           $detalhes->oxig_uso_det);
            $stmt->bindParam(':qt_det',                 $detalhes->qt_det);
            $stmt->bindParam(':dispositivo_det',        $detalhes->dispositivo_det);
            $stmt->bindParam(':atb_det',                $detalhes->atb_det);
            $stmt->bindParam(':atb_uso_det',            $detalhes->atb_uso_det);
            $stmt->bindParam(':acamado_det',            $detalhes->acamado_det);
            $stmt->bindParam(':exames_det',             $detalhes->exames_det,          PDO::PARAM_STR);
            $stmt->bindParam(':oportunidades_det',      $detalhes->oportunidades_det,   PDO::PARAM_STR);
            $stmt->bindParam(':tqt_det',                $detalhes->tqt_det);
            $stmt->bindParam(':svd_det',                $detalhes->svd_det);
            $stmt->bindParam(':gtt_det',                $detalhes->gtt_det);
            $stmt->bindParam(':dreno_det',              $detalhes->dreno_det);
            $stmt->bindParam(':rt_det',                 $detalhes->rt_det);
            $stmt->bindParam(':lesoes_pele_det',        $detalhes->lesoes_pele_det);
            $stmt->bindParam(':medic_alto_custo_det',   $detalhes->medic_alto_custo_det);
            $stmt->bindParam(':qual_medicamento_det',   $detalhes->qual_medicamento_det, PDO::PARAM_STR);
            $stmt->bindParam(':paliativos_det',         $detalhes->paliativos_det);
            $stmt->bindParam(':braden_det',             $detalhes->braden_det);
            $stmt->bindParam(':liminar_det',            $detalhes->liminar_det);
            $stmt->bindParam(':parto_det',              $detalhes->parto_det);
            $stmt->bindParam(':hemoderivados_det',      $detalhes->hemoderivados_det);
            $stmt->bindParam(':dialise_det',            $detalhes->dialise_det);
            $stmt->bindParam(':oxigenio_hiperbarica_det', $detalhes->oxigenio_hiperbarica_det);
            $stmt->execute();


            // Mensagem de sucesso por atualizar detalhes
            // $this->message->setMessage("detalhes atualizado com sucesso!", "success", "list_detalhes.php");
        }
        public function destroy($id_internacao)
        {
            $stmt = $this->conn->prepare("DELETE FROM tb_detalhes WHERE fk_int_det = :fk_int_det");
            $stmt->bindParam(":fk_int_det", $id_internacao);
            $stmt->execute();

            // Mensagem de sucesso por remover detalhes
            $this->message->setMessage("detalhes removido com sucesso!", "success", "list_detalhes.php");
        }


        public function findAll()
        {
            $detalhes = [];
            $stmt = $this->conn->query("SELECT * FROM tb_detalhes");
            $data = $stmt->fetchAll();

            if ($data) {
                foreach ($data as $detalhe) {
                    $detalhes[] = $this->builddetalhes($detalhe);
                }
            }

            return $detalhes;
        }
        public function findByVisita($idVisita)
        {
            $detalhes = [];
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_vis_det = :idVisita");
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($data) {
                foreach ($data as $detalhe) {
                    $detalhes[] = $this->builddetalhes($detalhe);
                }
            }

            return $detalhes;
        }
        public function findByInternacaoAndVisita($idInternacao, $idVisita)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_int_det = :idInternacao AND fk_vis_det = :idVisita");
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByVisitaAndInternacao($idVisita, $idInternacao)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_vis_det = :idVisita AND fk_int_det = :idInternacao");
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdDetalhes($idDetalhes)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE id_det = :idDetalhes");
            $stmt->bindParam(":idDetalhes", $idDetalhes);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdVisita($idVisita)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_vis_det = :idVisita");
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        public function findByIdInternacao($idInternacao)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_int_det = :idInternacao");
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        public function findByIdDetalhesAndVisita($idDetalhes, $idVisita)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE id_det = :idDetalhes AND fk_vis_det = :idVisita");
            $stmt->bindParam(":idDetalhes", $idDetalhes);
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdDetalhesAndInternacao($idDetalhes, $idInternacao)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE id_det = :idDetalhes AND fk_int_det = :idInternacao");
            $stmt->bindParam(":idDetalhes", $idDetalhes);
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdVisitaAndInternacao($idVisita, $idInternacao)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_vis_det = :idVisita AND fk_int_det = :idInternacao");
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdVisitaAndDetalhes($idVisita, $idDetalhes)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_vis_det = :idVisita AND id_det = :idDetalhes");
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->bindParam(":idDetalhes", $idDetalhes);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdInternacaoAndDetalhes($idInternacao, $idDetalhes)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_int_det = :idInternacao AND id_det = :idDetalhes");
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->bindParam(":idDetalhes", $idDetalhes);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdDetalhesAndVisitaAndInternacao($idDetalhes, $idVisita, $idInternacao)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE id_det = :idDetalhes AND fk_vis_det = :idVisita AND fk_int_det = :idInternacao");
            $stmt->bindParam(":idDetalhes", $idDetalhes);
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdVisitaAndDetalhesAndInternacao($idVisita, $idDetalhes, $idInternacao)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_vis_det = :idVisita AND id_det = :idDetalhes AND fk_int_det = :idInternacao");
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->bindParam(":idDetalhes", $idDetalhes);
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function findByIdInternacaoAndDetalhesAndVisita($idInternacao, $idDetalhes, $idVisita)
        {
            $stmt = $this->conn->prepare("SELECT * FROM tb_detalhes WHERE fk_int_det = :idInternacao AND id_det = :idDetalhes AND fk_vis_det = :idVisita");
            $stmt->bindParam(":idInternacao", $idInternacao);
            $stmt->bindParam(":idDetalhes", $idDetalhes);
            $stmt->bindParam(":idVisita", $idVisita);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }