<?php

class capeante
{
  public $adm_capeante;
  public $adm_check;
  public $aud_enf_capeante;
  public $aud_med_capeante;
  public $aud_adm_capeante;
  public $data_fech_capeante;
  public $data_digit_capeante;
  public $data_final_capeante;
  public $data_inicial_capeante;
  public $diarias_capeante;
  public $lote_cap;
  public $acomodacao_cap;
  public $enfer_check;
  public $exchangerate;
  public $glosa_diaria;
  public $glosa_honorarios;
  public $glosa_hemoderivados;
  public $glosa_matmed;
  public $glosa_materiais;
  public $glosa_medicamentos;
  public $glosa_oxig;
  public $glosa_sadt;
  public $glosa_taxas;
  public $glosa_opme;
  public $hospital_capeante;
  public $id_capeante;
  public $med_check;
  public $pac_capeante;
  public $pacote;
  public $parcial_capeante;
  public $parcial_num;
  public $fk_int_capeante;
  public $fk_user_cap;
  public $valor_apresentado_capeante;
  public $valor_diarias;
  public $valor_final_capeante;
  public $valor_glosa_enf;
  public $valor_glosa_med;
  public $valor_glosa_total;
  public $valor_honorarios;
  public $valor_hemoderivados;
  public $valor_matmed;
  public $valor_materiais;
  public $valor_medicamentos;
  public $valor_oxig;
  public $valor_sadt;
  public $valor_taxa;
  public $valor_opme;
  public $desconto_valor_cap;
  public $negociado_desconto_cap;
  public $encerrado_cap;
  public $aberto_cap;
  public $em_auditoria_cap;
  public $senha_finalizada;
  public $usuario_create_cap;
  public $data_create_cap;
  public $timer_cap;
  public $timer_start_cap;
  public $timer_end_cap;
  public $last_cap;
  public $conta_parada_cap;
  public $parada_motivo_cap;
  public $impresso_cap;
  public $fk_id_aud_enf;
  public $fk_id_aud_med;
  public $fk_id_aud_adm;
  public $fk_id_aud_hosp;
  public $validacao_cap;
  public $conta_fatura_cap;
  public $conta_faturada_cap;
  public $faturada_flag;
}


interface capeanteDAOInterface
{
  public function buildcapeante($capeante);
  public function findAll();
  public function findById($id_capeante);
  public function findByPac($pesquisa_nome, $limite, $inicio);
  public function create(capeante $capeante);
  public function update(capeante $capeanteUpdate);
  public function destroy($id_capeante);
  public function findGeral();
  public function findMaxCapeante();
  public function selectAllcapeante($where = null, $order = null, $limit = null);
  public function Qtdcapeante($where);
};
