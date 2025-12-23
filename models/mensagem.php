<?php

class Mensagem
{
    public $id_mensagem;
    public $de_usuario;
    public $para_usuario;
    public $mensagem;
    public $data_mensagem;
    public $vista;
}

interface mensagemDAOInterface
{
    public function buildMensagem($data);
    public function findAll();
    public function findById($id_mensagem);
    public function getMensagemsBetweenUsers($de_usuario, $para_usuario, $ultima_msg);
    public function create(Mensagem $mensagem, bool $flashFeedback = true);
    public function update(Mensagem $mensagem);
    public function destroy($id_mensagem);
    public function selectAllMensagems($where = null, $order = null, $limit = null);
    public function QtdMensagens($where = null);
}
