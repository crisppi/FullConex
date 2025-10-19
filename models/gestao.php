<?php

class gestao
{
  // IDs / FKs
  public ?int $id_gestao = null;
  public ?int $fk_user_ges = null;
  public ?int $fk_visita_ges = null;
  public ?int $fk_internacao_ges = null;

  // Controle de cadastro (FALTAVA ESTA!)
  public string $select_gestao = 'n';

  // Alto custo
  public string $alto_custo_ges = 'n';
  public ?string $rel_alto_custo_ges = '';

  // OPME
  public string $opme_ges = 'n';
  public ?string $rel_opme_ges = '';

  // Home care
  public string $home_care_ges = 'n';
  public ?string $rel_home_care_ges = '';

  // Desospitalização
  public string $desospitalizacao_ges = 'n';
  public ?string $rel_desospitalizacao_ges = '';

  // Evento adverso
  public string $evento_adverso_ges = 'n';
  public ?string $tipo_evento_adverso_gest = '';
  public ?string $rel_evento_adverso_ges = '';

  // Flags de evento
  public string $evento_sinalizado_ges = 'n';
  public string $evento_discutido_ges  = 'n';
  public string $evento_negociado_ges  = 'n';
  public string $evento_prorrogar_ges  = 'n';
  public string $evento_fech_ges       = 'n';

  // Valores / metadados
  public ?string $evento_valor_negoc_ges = '';

  public ?string $evento_retorno_qual_hosp_ges = '';
  public ?string $evento_classificado_hospital_ges = '';
  public ?string $evento_data_ges = '';
  public ?string $evento_encerrar_ges = '';
  public ?string $evento_impacto_financ_ges = '';
  public ?string $evento_prolongou_internacao_ges = '';
  public ?string $evento_concluido_ges = '';
  public ?string $evento_classificacao_ges = '';

  public function __construct()
  {
    // Já deixamos os defaults acima via tipagem/atribuição.
    // Se preferir, pode centralizar regras adicionais aqui.
  }
}

interface gestaoDAOInterface
{
  public function buildgestao($gestao);
  public function findAll();
  public function getgestao();
  public function findById($id_gestao);
  public function findByIdUpdate($id_gestao);
  public function create(gestao $gestao);
  public function update(gestao $gestao);
  public function destroy($id_gestao);
  public function findGeral();
  public function selectAllGestao($where = null, $order = null, $limit = null);
}