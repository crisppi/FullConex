<?php

class tuss
{
  public $id_tuss;
  public $tuss_solicitado;
  public $tuss_liberado_sn;
  public $qtd_tuss_solicitado;
  public $qtd_tuss_liberado;
  public $glosa_tuss;
  public $data_realizacao_tuss;
  public $data_create_tuss;
  public $fk_vis_tuss;
  public $fk_usuario_tuss;
  public $fk_int_tuss;
}

interface tussDAOInterface
{

  public function buildtuss($tuss);
  public function findAll();
  public function create(tuss $tuss);
  public function update(tuss $tuss);
  public function destroy($id_tuss);
  public function findGeral();
  public function selectAlltuss($where = null, $order = null, $limit = null);
  public function findByIdIntern($id_internacao);
};
