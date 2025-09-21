<?php

class Cid
{
  public $id_cid;
  public $cat;
  public $descricao;
}

interface cidDAOInterface
{

  public function buildCid($cid);
  public function findAll();
};
