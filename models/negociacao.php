<?php

class negociacao
{
  public $id_negociacao;
  public $fk_id_int;
  public $qtd;
  public $troca_de;
  public $troca_para;
  public $fk_usuario_neg;
  public $fk_visita_neg;
  public $fk_internacao_neg;
  public $saving;
  public $data_inicio_neg;
  public $data_fim_neg;
  public $tipo_negociacao;
}

interface negociacaoDAOInterface
{

  public function buildnegociacao($negociacao);
  public function findById($id_negociacao);
  public function create(negociacao $negociacao);
  public function update(negociacao $negociacao);
  public function destroy($id_negociacao);
  public function findGeral();
  public function findByLastId($lastId);
};