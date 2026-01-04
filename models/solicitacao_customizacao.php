<?php

class SolicitacaoCustomizacao
{
    public $id_solicitacao;
    public $fk_usuario_solicitante;
    public $nome;
    public $empresa;
    public $cargo;
    public $email;
    public $telefone;
    public $data_solicitacao;
    public $modulo_outro;
    public $descricao;
    public $problema_atual;
    public $resultado_esperado;
    public $impacto_nivel;
    public $descricao_impacto;
    public $prioridade;
    public $prazo_desejado;
    public $responsavel;
    public $assinatura;
    public $data_aprovacao;
    public $prazo_resposta;
    public $precificacao;
    public $observacoes_resposta;
    public $aprovacao_resposta;
    public $data_resposta;
    public $aprovacao_conex;
    public $data_aprovacao_conex;
    public $responsavel_aprovacao_conex;
    public $status;
    public $resolvido_em;
    public $resolvido_por;
    public $versao_sistema;
    public $criado_em;
    public $atualizado_em;
}

interface SolicitacaoCustomizacaoDAOInterface
{
    public function create(SolicitacaoCustomizacao $solicitacao, array $modulos, array $tipos, array $anexos);
    public function update(SolicitacaoCustomizacao $solicitacao, array $modulos, array $tipos, array $anexos, array $removerAnexos);
    public function findAll();
    public function findById($id_solicitacao);
    public function findAnexos($id_solicitacao);
    public function findModulos($id_solicitacao);
    public function findTipos($id_solicitacao);
}
