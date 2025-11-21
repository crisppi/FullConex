<?php
// models/CapValores.php

class CapValor
{
    private $id_valor;
    private $fk_capeante;
    private $criado_em;
    private $atualizado_em;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }

    // ========== Hydrate ==========
    public function fromArray(array $data)
    {
        $this->id_valor     = isset($data['id_valor'])     ? (int)$data['id_valor']     : null;
        $this->fk_capeante  = isset($data['fk_capeante'])  ? (int)$data['fk_capeante']  : null;
        $this->criado_em    = isset($data['criado_em'])    ? $data['criado_em']         : null;
        $this->atualizado_em = isset($data['atualizado_em']) ? $data['atualizado_em']     : null;
    }

    // ========== Dehydrate ==========
    public function toArray()
    {
        return [
            'id_valor'      => $this->id_valor,
            'fk_capeante'   => $this->fk_capeante,
            'criado_em'     => $this->criado_em,
            'atualizado_em' => $this->atualizado_em,
        ];
    }

    // ========== Getters / Setters ==========

    public function getIdValor()
    {
        return $this->id_valor;
    }

    public function setIdValor($id_valor)
    {
        $this->id_valor = $id_valor;
    }

    public function getFkCapeante()
    {
        return $this->fk_capeante;
    }

    public function setFkCapeante($fk_capeante)
    {
        $this->fk_capeante = $fk_capeante;
    }

    public function getCriadoEm()
    {
        return $this->criado_em;
    }

    public function setCriadoEm($criado_em)
    {
        $this->criado_em = $criado_em;
    }

    public function getAtualizadoEm()
    {
        return $this->atualizado_em;
    }

    public function setAtualizadoEm($atualizado_em)
    {
        $this->atualizado_em = $atualizado_em;
    }
}