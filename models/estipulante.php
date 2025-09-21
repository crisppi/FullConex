<?php

class Estipulante
{
  public $id_estipulante;
  public $nome_est;
  public $cidade_est;
  public $estado_est;
  public $endereco_est;
  public $email01_est;
  public $email02_est;
  public $telefone01_est;
  public $telefone02_est;
  public $numero_est;
  public $bairro_est;
  public $cnpj_est;
  public $ativo_est;
  public $coordMedico_est;
  public $emailCoordMedico_est;
  public $coordFat_est;
  public $email_coordFat_est;
  public $data_create_est;
  public $usuario_create_est;
  public $fk_usuario_est;
  public $logo_est;
  public $deletado_est;
  public $nome_contato_est;
  public $nome_responsavel_est;
  public $email_contato_est;
  public $email_responsavel_est;
  public $telefone_contato_est;
  public $telefone_responsavel_est;
  public $cep_est;
}

interface EstipulanteDAOInterface
{

  public function buildEstipulante($estipulante);
  public function findAll();
  public function getestipulante();
  public function findById($id_estipulante);
  public function findByEstipulante($pesquisa_nome);
  public function create(Estipulante $estipulante);
  public function update(Estipulante $estipulante);
  public function destroy($id_estipulante);
  public function findGeral();

  public function selectAllestipulante($where = null, $order = null, $limit = null);
  public function Qtdestipulante();
};