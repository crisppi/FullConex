<?php

class Detalhes
{
  public $id_detalhes;
  public $fk_vis_det;
  public $fk_int_det;

  public $curativo_det;
  public $dieta_det;
  public $nivel_consc_det;
  public $oxig_det;
  public $oxig_uso_det;
  public $qt_det;
  public $dispositivo_det;
  public $atb_det;
  public $acamado_det;
  public $atb_uso_det;
  public $exames_det;
  public $hemoderivados_det;
  public $dialise_det;
  public $oxigenio_hiperbarica_det;
  public $oportunidades_det;

  public $tqt_det;
  public $svd_det;
  public $gtt_det;
  public $dreno_det;
  public $lesoes_pele_det;
  public $rt_det;
  public $medic_alto_custo_det;
  public $qual_medicamento_det;

  public $paliativos_det;
  public $braden_det;
  public $liminar_det;
  public $parto_det;
}

interface detalhesDAOInterface
{

  public function builddetalhes($detalhes);
  public function create(detalhes $detalhes);
};
