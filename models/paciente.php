<?php

class Paciente
{
  public $id_paciente;
  public $nome_pac;
  public $nome_social_pac;
  public $idade_pac;
  public $data_nasc_pac;
  public $cidade_pac;
  public $endereco_pac;
  public $email01_pac;
  public $email02_pac;
  public $telefone01_pac;
  public $telefone02_pac;
  public $numero_pac;
  public $bairro_pac;
  public $complemento_pac;
  public $cpf_pac;
  public $mae_pac;
  public $ativo_pac;
  public $sexo_pac;
  public $data_create_pac;
  public $usuario_create_pac;
  public $fk_usuario_pac;
  public $fk_seguradora_pac;
  public $fk_estipulante_pac;
  public $obs_pac;
  public $matricula_pac;
  public $estado_pac;
  public $cep_pac;
  public $deletado_pac;
  public $num_atendimento_pac;
  public $recem_nascido_pac;
  public $mae_titular_pac;
  public $matricula_titular_pac;
  public $numero_rn_pac;
}

interface PacienteDAOInterface
{

  public function buildPaciente($paciente);
  public function findAll();
  public function findById($id_paciente);
  public function findByPac($pesquisa_nome, $limite, $inicio);
  public function create(Paciente $paciente);
  public function update(Paciente $paciente);
  public function destroy($id_paciente);
  public function findGeral();

  public function selectAllPaciente($where = null, $order = null, $limit = null);
  public function QtdPaciente();
}
;