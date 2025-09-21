<?php

class intern_antec
{
  public $id_intern_antec;
  public $intern_antec_ant_int;
  public $fk_internacao_ant_int;
  public $fk_id_paciente;
  public $fk_internacao_vis;
}

interface intern_antecDAOInterface
{

  public function buildintern_antec($intern_antec);
  public function findAll();
  public function findById($id_intern_antec);
  public function create(intern_antec $intern_antec);
  public function update(intern_antec $intern_antec);
  public function destroy($id_intern_antec);
  public function findGeral();
  public function selectAllintern_antec($where = null, $order = null, $limit = null);
  public function Qtdintern_antec();
};