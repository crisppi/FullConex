<?php

class TussAns
{
    public $id_tuss;
    public $cod_tuss;
    public $terminologia_tuss;
    public $roll_tuss;
    public $sub_grupo_tuss;
    public $grupo_tuss;
}

interface TussAnsDAOInterface
{
    public function buildTussAns($tussAns);
    public function findAll();
};