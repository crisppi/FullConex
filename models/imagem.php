<?php

class Imagem
{
  public $id_imagem;
  public $fk_imagem;
  public $imagem_img;
  public $imagem_name_img;
};

interface imagemDAOInterface
{
  public function create(imagem $imagem);
  public function buildimagem($imagem);

  // public function findAll();
  public function findById($id_imagem);
  // public function update(imagem $imagem);
  public function destroy($id_imagem);
  public function findGeral();

  public function selectAllImagem($where = null, $order = null, $limit = null);
  public function QtdImagem();
};
