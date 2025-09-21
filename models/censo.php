<?php

class censo
{
  public $id_censo;
  public $fk_paciente_censo;
  public $fk_hospital_censo;
  public $data_censo;
  public $senha_censo;
  public $acomodacao_censo;
  public $tipo_admissao_censo;
  public $modo_internacao_censo;
  public $usuario_create_censo;
  public $data_create_censo;
  public $titular_censo;
  public $internado;
}

interface censoDAOInterface
{

  public function buildcenso($censo);
  public function findAll();
  public function getcenso();
  public function findById($id_censo);
  public function findByIdUpdate($id_censo);
  public function create(censo $censo);
  public function update(censo $censo);
  public function destroy($id_censo);
  public function findGeral();
  public function selectAllcenso($where = null, $order = null, $limit = null);
};
