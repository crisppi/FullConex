<?php

class prorrogacao
{
  public $id_prorrogacao;
  public $acomod1_pror;
  public $isol_1_pror;
  public $fk_internacao_pror;
  public $prorrog1_fim_pror;
  public $prorrog1_ini_pror;
  public $diarias_1;

  public $fk_usuario_pror;
  public $fk_visita_pror;
}

interface prorrogacaoDAOInterface
{

  public function buildprorrogacao($prorrogacao);
  public function findById($id_prorrogacao);
  public function findByIdUpdate($id_prorrogacao);
  public function create(prorrogacao $prorrogacao);
  public function update(prorrogacao $prorrogacao);
  public function destroy($id_prorrogacao);
  public function findGeral();
};
