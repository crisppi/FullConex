<?php

class Internacao
{
  public $id_internacao;
  public $rel_auditoria_int;
  public $fk_paciente_int;
  public $fk_hospital_int;
  public $data_create_int;
  public $usuario_create_int;
  public $acoes_int;
  public $acomodacao_int;
  public $visita_med_int;
  public $visita_enf_int;
  public $data_intern_int;
  public $data_lancamento_int;
  public $data_visita_int;
  public $timer_int;
  public $forecast_total_days;
  public $forecast_lower_days;
  public $forecast_upper_days;
  public $forecast_generated_at;
  public $forecast_model;
  public $forecast_confidence;
  public $especialidade_int;
  public $fk_antecedente_int;
  public $fk_patologia_int;
  public $fk_cid_int;
  public $fk_patologia2;
  public $fk_user_int;
  public $grupo_patologia_int;
  public $internado_int;
  public $tipo_admissao_int;
  public $no_visita_int;
  public $primeira_visita_int;
  public $senha_int;
  public $rel_int;
  public $crm_int;
  public $modo_internacao_int;
  public $titular_int;
  public $primeira_vis_int;
  public $visita_no_int;
  public $visita_auditor_prof_med;
  public $visita_auditor_prof_enf;
  public $fk_usuario_int;
  public $censo_int;
  public $conta_auditada_int;
  public $origem_int;
  public $conta_em_analise_int;
  public $programacao_int;
  public $rel_pertinente_int;
  public $int_pertinente_int;
  public $hora_intern_int;

  public $curativo_enf;
  public $dieta_enf;
  public $tqt_enf;
  public $oxig_enf;
  public $exames_enf;
  public $oportunidades_enf;
  public $programacao_enf;
  public $num_atendimento_int;
}

interface InternacaoDAOInterface
{
  public function findGeral();
  public function create(internacao $internacao);
  public function findById($id_internacao);

  public function findInternByInternado($pesquisa_hosp, $ativo, $limite, $inicio);
  public function joininternacaoHospitalshow($id_internacao);
  public function alta($id_internacao);

  public function findByIdUpdate($id_internacao);

  public function update(Internacao $internacao);
  public function updateCenso(Internacao $internacao);
  public function buildinternacao($internacao);

  // PUBLIC DE SELECAO DE FILTROS
  // public 1 -> selecao de hospital
  // public 2 -> selecao de internados
  // public 3 -> selecao de ambos filtros

  // public 1 -> selecao de hospital
  public function findByHospital($where_hosp, $limite, $inicio);
  // public 2 -> selecao de internados
  public function findByInternado($ativo, $limite, $inicio);
  // public 3 -> selecao de ambos filtros
  public function findByAmbos($pesquisa_hosp, $ativo, $limite, $inicio);
  // public 4 -> selecao de ambos filtros
  public function findByAll($limite, $inicio);


  public function selectAllInternacao($where = null, $order = null, $limit = null);
  public function QtdInternacao($where);
}
