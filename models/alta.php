<?php

class alta
{
  public $id_alta;
  public $fk_id_int_alt;
  public $tipo_alta_alt;
  public $data_alta_alt;
  public $hora_alta_alt;
  public $internado_alt;
  public $usuario_alt;
  public $data_create_alt;
  public $fk_usuario_alt;

}

interface altaDAOInterface
{

  public function buildalta($alta);
  public function findById($id_alta);
  public function create(alta $alta);
  public function findGeral();
};
