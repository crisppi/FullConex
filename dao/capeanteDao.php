<?php

require_once("./models/capeante.php");
require_once("./models/message.php");

class capeanteDAO implements capeanteDAOInterface
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

    /** Monta objeto */
    public function buildcapeante($data)
    {
        $capeante = new capeante();

        $capeante->id_capeante               = $data["id_capeante"]             ?? null;
        $capeante->adm_capeante              = $data["adm_capeante"]            ?? null;
        $capeante->adm_check                 = $data["adm_check"]               ?? null;
        $capeante->aud_enf_capeante          = $data["aud_enf_capeante"]        ?? null;
        $capeante->aud_med_capeante          = $data["aud_med_capeante"]        ?? null;
        $capeante->data_fech_capeante        = $data["data_fech_capeante"]      ?? null;
        $capeante->data_final_capeante       = $data["data_final_capeante"]     ?? null;
        $capeante->data_inicial_capeante     = $data["data_inicial_capeante"]   ?? null;
        $capeante->diarias_capeante          = $data["diarias_capeante"]        ?? null;
        $capeante->lote_cap                  = $data["lote_cap"]                ?? null;
        $capeante->glosa_diaria              = $data["glosa_diaria"]            ?? null;
        $capeante->glosa_honorarios          = $data["glosa_honorarios"]        ?? null;
        $capeante->glosa_matmed              = $data["glosa_matmed"]            ?? null;
        $capeante->glosa_oxig                = $data["glosa_oxig"]              ?? null;
        $capeante->glosa_sadt                = $data["glosa_sadt"]              ?? null;
        $capeante->glosa_taxas               = $data["glosa_taxas"]             ?? null;
        $capeante->glosa_opme                = $data["glosa_opme"]              ?? null;
        $capeante->med_check                 = $data["med_check"]               ?? null;
        $capeante->enfer_check               = $data["enfer_check"]             ?? null;
        $capeante->pacote                    = $data["pacote"]                  ?? null;
        $capeante->parcial_capeante          = $data["parcial_capeante"]        ?? null;
        $capeante->parcial_num               = $data["parcial_num"]             ?? null;
        $capeante->fk_int_capeante           = $data["fk_int_capeante"]         ?? null;
        $capeante->fk_user_cap               = $data["fk_user_cap"]             ?? null;
        $capeante->valor_apresentado_capeante = $data["valor_apresentado_capeante"] ?? null;
        $capeante->valor_diarias             = $data["valor_diarias"]           ?? null;
        $capeante->valor_final_capeante      = $data["valor_final_capeante"]    ?? null;
        $capeante->valor_glosa_enf           = $data["valor_glosa_enf"]         ?? null;
        $capeante->valor_glosa_med           = $data["valor_glosa_med"]         ?? null;
        $capeante->valor_glosa_total         = $data["valor_glosa_total"]       ?? null;
        $capeante->valor_honorarios          = $data["valor_honorarios"]        ?? null;
        $capeante->valor_matmed              = $data["valor_matmed"]            ?? null;
        $capeante->valor_oxig                = $data["valor_oxig"]              ?? null;
        $capeante->valor_sadt                = $data["valor_sadt"]              ?? null;
        $capeante->valor_taxa                = $data["valor_taxa"]              ?? null;
        $capeante->valor_opme                = $data["valor_opme"]              ?? null;
        $capeante->desconto_valor_cap        = $data["desconto_valor_cap"]      ?? null;
        $capeante->negociado_desconto_cap    = $data["negociado_desconto_cap"]  ?? null;
        $capeante->em_auditoria_cap          = $data["em_auditoria_cap"]        ?? null;
        $capeante->aberto_cap                = $data["aberto_cap"]              ?? null;
        $capeante->encerrado_cap             = $data["encerrado_cap"]           ?? null;
        $capeante->senha_finalizada          = $data["senha_finalizada"]        ?? null;
        $capeante->conta_parada_cap          = $data["conta_parada_cap"]        ?? null;
        $capeante->parada_motivo_cap         = $data["parada_motivo_cap"]       ?? ($data["parada_motivo"] ?? null);
        $capeante->fk_id_aud_enf             = $data["fk_id_aud_enf"]           ?? null;
        $capeante->fk_id_aud_med             = $data["fk_id_aud_med"]           ?? null;
        $capeante->fk_id_aud_adm             = $data["fk_id_aud_adm"]           ?? null;
        $capeante->fk_id_aud_hosp            = $data["fk_id_aud_hosp"]          ?? null;
        $capeante->impresso_cap              = $data["impresso_cap"]            ?? null;

        // NOVO: flags de faturamento (ambos os nomes) + alias
        $capeante->conta_faturada_cap        = $data["conta_faturada_cap"]      ?? null;
        $capeante->conta_fatura_cap          = $data["conta_fatura_cap"]        ?? null;
        $capeante->faturada_flag             = $data["faturada_flag"]           ?? null;

        return $capeante;
    }

    /** Listas básicas */
    public function findAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM tb_capeante ORDER BY id_capeante ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getcapeantesBynome_pac($nome_pac)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tb_capeante WHERE nome_pac = :nome_pac ORDER BY id_capeante ASC");
        $stmt->bindParam(":nome_pac", $nome_pac);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $capeantes = [];
        foreach ($rows as $row) $capeantes[] = $this->buildcapeante($row);
        return $capeantes;
    }

    public function findById($id_capeante)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tb_capeante WHERE id_capeante = :id_capeante");
        $stmt->bindParam(":id_capeante", $id_capeante, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->buildcapeante($data) : null;
    }

    /** BUSCA por nome de paciente com paginação (corrigido LIMIT/binds) */
    public function findByPac($pesquisa_nome, $limite, $inicio)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM tb_capeante
            WHERE nome_pac LIKE :nome_pac
            ORDER BY nome_pac ASC
            LIMIT :inicio, :limite
        ");
        $like = "%{$pesquisa_nome}%";
        $stmt->bindParam(":nome_pac", $like, PDO::PARAM_STR);
        $stmt->bindParam(":inicio", $inicio, PDO::PARAM_INT);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** CREATE */
    public function create(capeante $capeante)
    {
        // Se for parcial e não veio número, calcula automaticamente
        if (
            isset($capeante->parcial_capeante) && $capeante->parcial_capeante === 's' &&
            !empty($capeante->fk_int_capeante) &&
            (empty($capeante->parcial_num) && $capeante->parcial_num !== 0)
        ) {
            $capeante->parcial_num = $this->getCapeantesCountByInternacao((int)$capeante->fk_int_capeante) + 1;
        }
        $stmt = $this->conn->prepare("
            INSERT INTO tb_capeante (
                adm_capeante, adm_check, aud_enf_capeante, aud_med_capeante,
                data_fech_capeante, data_final_capeante, data_inicial_capeante,
                diarias_capeante, lote_cap, glosa_diaria, glosa_honorarios,
                glosa_matmed, glosa_oxig, glosa_sadt, glosa_taxas, glosa_opme,
                med_check, enfer_check, pacote, parcial_capeante, parcial_num,
                fk_int_capeante, fk_user_cap, valor_apresentado_capeante, valor_diarias,
                valor_final_capeante, valor_glosa_enf, valor_glosa_med, valor_glosa_total,
                valor_honorarios, valor_matmed, valor_oxig, valor_sadt, valor_opme,
                senha_finalizada, desconto_valor_cap, negociado_desconto_cap,
                em_auditoria_cap, aberto_cap, encerrado_cap, valor_taxa,
                usuario_create_cap, data_create_cap, conta_parada_cap, parada_motivo_cap,
                fk_id_aud_enf, fk_id_aud_med, fk_id_aud_adm, fk_id_aud_hosp
            ) VALUES (
                :adm_capeante, :adm_check, :aud_enf_capeante, :aud_med_capeante,
                :data_fech_capeante, :data_final_capeante, :data_inicial_capeante,
                :diarias_capeante, :lote_cap, :glosa_diaria, :glosa_honorarios,
                :glosa_matmed, :glosa_oxig, :glosa_sadt, :glosa_taxas, :glosa_opme,
                :med_check, :enfer_check, :pacote, :parcial_capeante, :parcial_num,
                :fk_int_capeante, :fk_user_cap, :valor_apresentado_capeante, :valor_diarias,
                :valor_final_capeante, :valor_glosa_enf, :valor_glosa_med, :valor_glosa_total,
                :valor_honorarios, :valor_matmed, :valor_oxig, :valor_sadt, :valor_opme,
                :senha_finalizada, :desconto_valor_cap, :negociado_desconto_cap,
                :em_auditoria_cap, :aberto_cap, :encerrado_cap, :valor_taxa,
                :usuario_create_cap, :data_create_cap, :conta_parada_cap, :parada_motivo_cap,
                :fk_id_aud_enf, :fk_id_aud_med, :fk_id_aud_adm, :fk_id_aud_hosp
            )
        ");

        // binds (mesmo conjunto que você já tinha)
        $stmt->bindParam(":adm_capeante", $capeante->adm_capeante);
        $stmt->bindParam(":adm_check", $capeante->adm_check);
        $stmt->bindParam(":aud_enf_capeante", $capeante->aud_enf_capeante);
        $stmt->bindParam(":aud_med_capeante", $capeante->aud_med_capeante);
        $stmt->bindParam(":data_fech_capeante", $capeante->data_fech_capeante);
        $stmt->bindParam(":data_final_capeante", $capeante->data_final_capeante);
        $stmt->bindParam(":data_inicial_capeante", $capeante->data_inicial_capeante);
        $stmt->bindParam(":diarias_capeante", $capeante->diarias_capeante);
        $stmt->bindParam(":lote_cap", $capeante->lote_cap);
        $stmt->bindParam(":glosa_diaria", $capeante->glosa_diaria);
        $stmt->bindParam(":glosa_honorarios", $capeante->glosa_honorarios);
        $stmt->bindParam(":glosa_matmed", $capeante->glosa_matmed);
        $stmt->bindParam(":glosa_oxig", $capeante->glosa_oxig);
        $stmt->bindParam(":glosa_sadt", $capeante->glosa_sadt);
        $stmt->bindParam(":glosa_taxas", $capeante->glosa_taxas);
        $stmt->bindParam(":glosa_opme", $capeante->glosa_opme);
        $stmt->bindParam(":med_check", $capeante->med_check);
        $stmt->bindParam(":enfer_check", $capeante->enfer_check);
        $stmt->bindParam(":pacote", $capeante->pacote);
        $stmt->bindParam(":parcial_capeante", $capeante->parcial_capeante);
        $stmt->bindParam(":parcial_num", $capeante->parcial_num);
        $stmt->bindParam(":fk_int_capeante", $capeante->fk_int_capeante);
        $stmt->bindParam(":fk_user_cap", $capeante->fk_user_cap);
        $stmt->bindParam(":valor_apresentado_capeante", $capeante->valor_apresentado_capeante);
        $stmt->bindParam(":valor_diarias", $capeante->valor_diarias);
        $stmt->bindParam(":valor_final_capeante", $capeante->valor_final_capeante);
        $stmt->bindParam(":valor_glosa_enf", $capeante->valor_glosa_enf);
        $stmt->bindParam(":valor_glosa_med", $capeante->valor_glosa_med);
        $stmt->bindParam(":valor_glosa_total", $capeante->valor_glosa_total);
        $stmt->bindParam(":valor_honorarios", $capeante->valor_honorarios);
        $stmt->bindParam(":valor_matmed", $capeante->valor_matmed);
        $stmt->bindParam(":valor_oxig", $capeante->valor_oxig);
        $stmt->bindParam(":valor_sadt", $capeante->valor_sadt);
        $stmt->bindParam(":valor_opme", $capeante->valor_opme);
        $stmt->bindParam(":senha_finalizada", $capeante->senha_finalizada);
        $stmt->bindParam(":desconto_valor_cap", $capeante->desconto_valor_cap);
        $stmt->bindParam(":negociado_desconto_cap", $capeante->negociado_desconto_cap);
        $stmt->bindParam(":em_auditoria_cap", $capeante->em_auditoria_cap);
        $stmt->bindParam(":aberto_cap", $capeante->aberto_cap);
        $stmt->bindParam(":encerrado_cap", $capeante->encerrado_cap);
        $stmt->bindParam(":valor_taxa", $capeante->valor_taxa);
        $stmt->bindParam(":usuario_create_cap", $capeante->usuario_create_cap);
        $stmt->bindParam(":data_create_cap", $capeante->data_create_cap);
        $stmt->bindParam(":conta_parada_cap", $capeante->conta_parada_cap);
        $stmt->bindParam(":parada_motivo_cap", $capeante->parada_motivo_cap);
        $stmt->bindParam(":fk_id_aud_enf", $capeante->fk_id_aud_enf);
        $stmt->bindParam(":fk_id_aud_med", $capeante->fk_id_aud_med);
        $stmt->bindParam(":fk_id_aud_adm", $capeante->fk_id_aud_adm);
        $stmt->bindParam(":fk_id_aud_hosp", $capeante->fk_id_aud_hosp);

        $stmt->execute();
        $this->message->setMessage("capeante adicionado com sucesso!", "success", "list_internacao_cap.php");
    }

    /** UPDATE (mantida a sua lógica, só mantendo organização) */
    public function update(capeante $capeante)
    {
        try {
            $sql = "UPDATE tb_capeante SET
                adm_capeante = :adm_capeante,
                adm_check = :adm_check,
                aud_enf_capeante = :aud_enf_capeante,
                aud_med_capeante = :aud_med_capeante,
                data_fech_capeante = :data_fech_capeante,
                data_final_capeante = :data_final_capeante,
                data_inicial_capeante = :data_inicial_capeante,
                diarias_capeante = :diarias_capeante,
                glosa_diaria = :glosa_diaria,
                lote_cap = :lote_cap,
                glosa_honorarios = :glosa_honorarios,
                glosa_matmed = :glosa_matmed,
                glosa_oxig = :glosa_oxig,
                glosa_sadt = :glosa_sadt,
                glosa_taxas = :glosa_taxas,
                glosa_opme = :glosa_opme,
                med_check = :med_check,
                enfer_check = :enfer_check,
                pacote = :pacote,
                parcial_capeante = :parcial_capeante,
                parcial_num = :parcial_num,
                fk_int_capeante = :fk_int_capeante,
                fk_user_cap = :fk_user_cap,
                valor_apresentado_capeante = :valor_apresentado_capeante,
                valor_diarias = :valor_diarias,
                valor_final_capeante = :valor_final_capeante,
                valor_glosa_enf = :valor_glosa_enf,
                valor_glosa_med = :valor_glosa_med,
                valor_glosa_total = :valor_glosa_total,
                valor_honorarios = :valor_honorarios,
                valor_matmed = :valor_matmed,
                valor_oxig = :valor_oxig,
                valor_sadt = :valor_sadt,
                valor_taxa = :valor_taxa,
                valor_opme = :valor_opme,
                senha_finalizada = :senha_finalizada,
                desconto_valor_cap = :desconto_valor_cap,
                negociado_desconto_cap = :negociado_desconto_cap,
                em_auditoria_cap = :em_auditoria_cap,
                aberto_cap = :aberto_cap,
                encerrado_cap = :encerrado_cap,
                usuario_create_cap = :usuario_create_cap,
                data_create_cap = :data_create_cap,
                conta_parada_cap = :conta_parada_cap,
                parada_motivo_cap = :parada_motivo_cap,
                impresso_cap = :impresso_cap,
                fk_id_aud_enf = :fk_id_aud_enf,
                fk_id_aud_med = :fk_id_aud_med,
                fk_id_aud_adm = :fk_id_aud_adm,
                fk_id_aud_hosp = :fk_id_aud_hosp,
                validacao_cap = :validacao_cap
                WHERE id_capeante = :id_capeante";

            $stmt = $this->conn->prepare($sql);

            // binds (mesmos do seu código original)
            $stmt->bindParam(":adm_capeante", $capeante->adm_capeante);
            $stmt->bindParam(":adm_check", $capeante->adm_check);
            $stmt->bindParam(":med_check", $capeante->med_check);
            $stmt->bindParam(":enfer_check", $capeante->enfer_check);
            $stmt->bindParam(":aud_enf_capeante", $capeante->aud_enf_capeante);
            $stmt->bindParam(":aud_med_capeante", $capeante->aud_med_capeante);
            $stmt->bindParam(":data_fech_capeante", $capeante->data_fech_capeante);
            $stmt->bindParam(":data_final_capeante", $capeante->data_final_capeante);
            $stmt->bindParam(":data_inicial_capeante", $capeante->data_inicial_capeante);
            $stmt->bindParam(":diarias_capeante", $capeante->diarias_capeante);
            $stmt->bindParam(":glosa_diaria", $capeante->glosa_diaria);
            $stmt->bindParam(":lote_cap", $capeante->lote_cap);
            $stmt->bindParam(":glosa_honorarios", $capeante->glosa_honorarios);
            $stmt->bindParam(":glosa_matmed", $capeante->glosa_matmed);
            $stmt->bindParam(":glosa_oxig", $capeante->glosa_oxig);
            $stmt->bindParam(":glosa_sadt", $capeante->glosa_sadt);
            $stmt->bindParam(":glosa_taxas", $capeante->glosa_taxas);
            $stmt->bindParam(":glosa_opme", $capeante->glosa_opme);
            $stmt->bindParam(":pacote", $capeante->pacote);
            $stmt->bindParam(":parcial_capeante", $capeante->parcial_capeante);
            $stmt->bindParam(":parcial_num", $capeante->parcial_num);
            $stmt->bindParam(":fk_int_capeante", $capeante->fk_int_capeante);
            $stmt->bindParam(":fk_user_cap", $capeante->fk_user_cap);
            $stmt->bindParam(":valor_apresentado_capeante", $capeante->valor_apresentado_capeante);
            $stmt->bindParam(":valor_diarias", $capeante->valor_diarias);
            $stmt->bindParam(":valor_honorarios", $capeante->valor_honorarios);
            $stmt->bindParam(":valor_matmed", $capeante->valor_matmed);
            $stmt->bindParam(":valor_taxa", $capeante->valor_taxa);
            $stmt->bindParam(":valor_oxig", $capeante->valor_oxig);
            $stmt->bindParam(":valor_sadt", $capeante->valor_sadt);
            $stmt->bindParam(":valor_opme", $capeante->valor_opme);
            $stmt->bindParam(":valor_glosa_enf", $capeante->valor_glosa_enf);
            $stmt->bindParam(":valor_glosa_med", $capeante->valor_glosa_med);
            $stmt->bindParam(":valor_glosa_total", $capeante->valor_glosa_total);
            $stmt->bindParam(":valor_final_capeante", $capeante->valor_final_capeante);
            $stmt->bindParam(":senha_finalizada", $capeante->senha_finalizada);
            $stmt->bindParam(":negociado_desconto_cap", $capeante->negociado_desconto_cap);
            $stmt->bindParam(":desconto_valor_cap", $capeante->desconto_valor_cap);
            $stmt->bindParam(":em_auditoria_cap", $capeante->em_auditoria_cap);
            $stmt->bindParam(":aberto_cap", $capeante->aberto_cap);
            $stmt->bindParam(":encerrado_cap", $capeante->encerrado_cap);
            $stmt->bindParam(":conta_parada_cap", $capeante->conta_parada_cap);
            $stmt->bindParam(":parada_motivo_cap", $capeante->parada_motivo_cap);
            $stmt->bindParam(":usuario_create_cap", $capeante->usuario_create_cap);
            $stmt->bindParam(":data_create_cap", $capeante->data_create_cap);
            $stmt->bindParam(":id_capeante", $capeante->id_capeante, PDO::PARAM_INT);
            $stmt->bindParam(":impresso_cap", $capeante->impresso_cap);
            $stmt->bindValue(":fk_id_aud_enf",  $capeante->fk_id_aud_enf === "" ? null : $capeante->fk_id_aud_enf,  $capeante->fk_id_aud_enf === "" ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(":fk_id_aud_med",  $capeante->fk_id_aud_med, PDO::PARAM_INT);
            $stmt->bindValue(":fk_id_aud_adm",  $capeante->fk_id_aud_adm === "" ? null : $capeante->fk_id_aud_adm,  $capeante->fk_id_aud_adm === "" ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(":fk_id_aud_hosp", $capeante->fk_id_aud_hosp);
            $stmt->bindParam(":validacao_cap",  $capeante->validacao_cap);

            $stmt->execute();
            $this->message->setMessage("Capeante atualizado com sucesso!", "success", "list_internacao_cap_audit.php");
        } catch (PDOException $e) {
            print_r($e->getMessage());
        }
    }

    /** DELETE */
    public function destroy($id_capeante)
    {
        $stmt = $this->conn->prepare("DELETE FROM tb_capeante WHERE id_capeante = :id");
        $stmt->bindParam(":id", $id_capeante, PDO::PARAM_INT);
        $stmt->execute();
        $this->message->setMessage("capeante removido com sucesso!", "success", "list_capeante.php");
    }

    /** Listagem geral simples */
    public function findGeral()
    {
        $stmt = $this->conn->query("SELECT * FROM tb_capeante ORDER BY id_capeante");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** SELECT com filtros/paginação trazendo alias de FATURAMENTO */
    public function selectAllcapeante($where = null, $order = null, $limite = null)
    {
        $where  = strlen($where)  ? 'WHERE ' . $where  : '';
        $order  = strlen($order)  ? 'ORDER BY ' . $order : '';
        $limite = strlen($limite) ? 'LIMIT ' . $limite : '';
        $group  = " GROUP BY ac.id_internacao ";

        $sql = "
        SELECT 
            ac.id_internacao,
            ac.acoes_int,
            ac.data_intern_int,
            ac.data_visita_int,
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
            ac.senha_int,
            pa.id_paciente,
            pa.nome_pac,
            ho.id_hospital,
            ho.nome_hosp,
            ho.email01_hosp,
            ca.id_capeante,
            ca.fk_int_capeante,
            ca.valor_apresentado_capeante,
            ca.valor_final_capeante,
            ca.data_inicial_capeante,
            ca.data_final_capeante,
            ca.lote_cap,
            ca.adm_check,
            ca.med_check,
            ca.enfer_check,
            ca.parcial_num,
            ca.parcial_capeante,
            ca.glosa_diaria,
            ca.glosa_honorarios,
            ca.glosa_matmed,
            ca.glosa_oxig,
            ca.glosa_sadt,
            ca.glosa_taxas,
            ca.glosa_opme,
            ca.pacote,
            ca.valor_diarias,
            ca.valor_glosa_enf,
            ca.valor_glosa_med,
            ca.valor_honorarios,
            ca.valor_matmed,
            ca.valor_glosa_total,
            ca.valor_oxig,
            ca.valor_sadt,
            ca.valor_taxa,
            ca.valor_opme,
            ca.senha_finalizada,
            ca.negociado_desconto_cap,
            ca.desconto_valor_cap,
            ca.encerrado_cap,
            ca.aberto_cap,
            ca.em_auditoria_cap,
            ca.conta_parada_cap,
            ca.parada_motivo_cap,
            ca.fk_id_aud_med,
            ca.fk_id_aud_enf,
            ca.fk_id_aud_adm,
            ca.fk_id_aud_hosp,
            COALESCE(ca.conta_fatura_cap, ca.conta_faturada_cap) AS faturada_flag
        FROM tb_internacao ac
        LEFT JOIN tb_capeante  ca ON ca.fk_int_capeante = ac.id_internacao
        LEFT JOIN tb_hospital  ho ON ac.fk_hospital_int = ho.id_hospital
        LEFT JOIN tb_paciente  pa ON ac.fk_paciente_int = pa.id_paciente
        $where
        $group
        $order
        $limite";

        $query = $this->conn->query($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /** SELECT usado na sua lista (com alias faturada_flag incluso) */
    public function selectInternacaoCap($where = null, $order = null, $limit = null)
    {
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        $sql = "
        SELECT 
            ac.id_internacao,
            ac.data_intern_int,
            ac.data_visita_int,
            ac.fk_hospital_int,
            ac.internado_int,
            ac.visita_no_int,
            ac.primeira_vis_int,
            pa.id_paciente,
            pa.nome_pac,
            ho.id_hospital,
            ho.nome_hosp,
            ca.id_capeante,
            ca.fk_int_capeante,
            ca.valor_apresentado_capeante,
            ca.valor_final_capeante,
            ca.data_inicial_capeante,
            ca.data_final_capeante,
            ca.lote_cap,
            ca.adm_check,
            ca.med_check,
            ca.enfer_check,
            ca.parcial_num,
            ca.parcial_capeante,
            ca.glosa_diaria,
            ca.glosa_honorarios,
            ca.glosa_matmed,
            ca.glosa_oxig,
            ca.glosa_sadt,
            ca.glosa_taxas,
            ca.glosa_opme,
            ca.pacote,
            ca.valor_diarias,
            ca.valor_glosa_enf,
            ca.valor_glosa_med,
            ca.valor_honorarios,
            ca.valor_matmed,
            ca.valor_glosa_total,
            ca.valor_oxig,
            ca.valor_sadt,
            ca.valor_taxa,
            ca.valor_opme,
            ca.senha_finalizada,
            ca.negociado_desconto_cap,
            ca.desconto_valor_cap,
            ca.em_auditoria_cap,
            ca.aberto_cap,
            ca.encerrado_cap,
            ca.conta_parada_cap,
            ca.parada_motivo_cap,
            COALESCE(ca.conta_fatura_cap, ca.conta_faturada_cap) AS faturada_flag
        FROM tb_internacao ac
        LEFT JOIN tb_hospital  ho ON ac.fk_hospital_int = ho.id_hospital
        LEFT JOIN tb_paciente  pa ON ac.fk_paciente_int = pa.id_paciente
        LEFT JOIN tb_capeante  ca ON ac.id_internacao = ca.fk_int_capeante
        $where
        $order
        $limit";

        $query = $this->conn->query($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Quantidade com correções de vírgula e nomes */
    public function Qtdcapeante($where = null, $order = null, $limite = null)
    {
        $where  = strlen($where)  ? 'WHERE ' . $where  : '';
        $order  = strlen($order)  ? 'ORDER BY ' . $order : '';
        $limite = strlen($limite) ? 'LIMIT ' . $limite : '';
        $group  = ' GROUP BY ac.id_internacao ';

        $sql = "
        SELECT 
            ac.id_internacao,
            ac.data_intern_int,
            ac.data_visita_int,
            ac.fk_paciente_int,
            ac.fk_hospital_int,
            pa.id_paciente,
            pa.nome_pac,
            ho.id_hospital,
            ho.nome_hosp,
            ca.id_capeante,
            ca.fk_int_capeante,
            ca.senha_finalizada,
            ca.em_auditoria_cap,
            ca.aberto_cap,
            ca.encerrado_cap,
            ca.conta_parada_cap,
            ca.parada_motivo_cap,
            COUNT(ac.id_internacao) AS qtd
        FROM tb_internacao ac
        LEFT JOIN tb_capeante ca ON ca.fk_int_capeante = ac.id_internacao
        LEFT JOIN tb_hospital ho ON ac.fk_hospital_int = ho.id_hospital
        LEFT JOIN tb_paciente pa ON ac.fk_paciente_int = pa.id_paciente
        $where
        $group
        $order
        $limite";

        $stmt = $this->conn->query($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCapeanteByInternacao($id_internacao)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(fk_int_capeante) AS qtd
            FROM tb_capeante
            WHERE fk_int_capeante = :id
        ");
        $stmt->bindParam(":id", $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastCapeanteByInternacao($id_internacao)
    {
        $stmt = $this->conn->prepare("
            SELECT data_final_capeante, data_inicial_capeante, parcial_num
            FROM tb_capeante
            WHERE id_capeante = (
                SELECT MAX(id_capeante) FROM tb_capeante WHERE fk_int_capeante = :id
            )
        ");
        $stmt->bindParam(":id", $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastCapeanteIdByInternacao($id_internacao)
    {
        $stmt = $this->conn->prepare("
            SELECT id_capeante
            FROM tb_capeante
            WHERE id_capeante = (
                SELECT MAX(id_capeante) FROM tb_capeante WHERE fk_int_capeante = :id
            )
        ");
        $stmt->bindParam(":id", $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findMaxCapeante()
    {
        $stmt = $this->conn->query("SELECT COALESCE(MAX(id_internacao),1) AS ultimoReg FROM tb_internacao");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** ===== MARCAR FATURADO ===== */

    /** Marca UM capeante como faturado (seta ambas colunas pra 's') */
    public function marcarComoFaturado($id_capeante)
    {
        $stmt = $this->conn->prepare("
            UPDATE tb_capeante
               SET conta_faturada_cap = 's',
                   conta_fatura_cap   = 's'
             WHERE id_capeante = :id
        ");
        $stmt->bindParam(":id", $id_capeante, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new Exception("Não foi possível atualizar o banco de dados (faturado).");
        }
    }

    /** Marca VÁRIOS capeantes como faturados */
    public function marcarComoFaturadoEmLote(array $ids)
    {
        if (empty($ids)) return;
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (empty($ids)) return;

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
            UPDATE tb_capeante
               SET conta_faturada_cap = 's',
                   conta_fatura_cap   = 's'
             WHERE id_capeante IN ($in)
        ";
        $stmt = $this->conn->prepare($sql);
        foreach ($ids as $i => $v) $stmt->bindValue($i + 1, $v, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new Exception("Falha ao faturar as contas selecionadas.");
        }
    }
    /**
     * Retorna o último capeante (parcial) da mesma internação, se existir.
     * Usado para informar o período anterior ao criar uma nova parcial.
     * Retorna: id_capeante, parcial_num, data_inicial_capeante, data_final_capeante.
     */

    /** Conta quantos capeantes já existem para a internação */
    public function getCapeantesCountByInternacao(int $id_internacao): int
    {
        $row = $this->getCapeanteByInternacao($id_internacao); // você já tem esse método
        return isset($row['qtd']) ? (int)$row['qtd'] : 0;
    }

    /** Retorna o último capeante (id, parcial_num e período) da mesma internação */
    public function getUltimoCapeantePeriodoByInternacao(int $id_internacao)
    {
        $sql = "
        SELECT 
            id_capeante,
            parcial_num,
            data_inicial_capeante,
            data_final_capeante
        FROM tb_capeante
        WHERE fk_int_capeante = :id
        ORDER BY id_capeante DESC
        LIMIT 1
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id_internacao, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Prepara dados úteis para abertura de nova parcial */
    public function prepararNovaParcial(int $id_internacao): array
    {
        $count  = $this->getCapeantesCountByInternacao($id_internacao);
        $ultimo = $this->getUltimoCapeantePeriodoByInternacao($id_internacao);

        return [
            'fk_int_capeante'       => $id_internacao,
            'proxima_parcial'       => $count + 1,
            'ultimo_id_capeante'    => $ultimo['id_capeante'] ?? null,
            'periodo_anterior_ini'  => $ultimo['data_inicial_capeante'] ?? null,
            'periodo_anterior_fim'  => $ultimo['data_final_capeante']   ?? null,
        ];
    }
}
