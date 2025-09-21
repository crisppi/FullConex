<?php
// DEBUG TEMPORÁRIO (REMOVER APÓS TESTE)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);


include_once("check_logado.php");

require_once("templates/header.php");
include_once("models/internacao.php");
require_once("dao/internacaoDao.php");
require_once("models/message.php");

include_once("models/hospital.php");
include_once("dao/hospitalDao.php");

include_once("models/patologia.php");
include_once("dao/patologiaDao.php");

include_once("models/visita.php");
include_once("dao/visitaDao.php");

include_once("models/paciente.php");
require_once("dao/pacienteDao.php");

include_once("models/gestao.php");
include_once("dao/gestaoDao.php");

include_once("models/acomodacao.php");
include_once("dao/acomodacaoDao.php");

include_once("models/prorrogacao.php");
include_once("dao/prorrogacaoDao.php");

include_once("models/uti.php");
include_once("dao/utiDao.php");

include_once("models/tuss_ans.php");
include_once("dao/tussAnsDao.php");

include_once("models/tuss.php");
include_once("dao/tussDao.php");

include_once("array_dados.php");


include_once("models/antecedente.php");
include_once("dao/antecedenteDao.php");

$internacaoDao = new internacaoDAO($conn, $BASE_URL);

$hospital_geral = new hospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$visita = new visitaDAO($conn, $BASE_URL);
$visitas = $visita->findGeral($limite, $inicio);


$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$gestao = new gestaoDAO($conn, $BASE_URL);
$findMaxVis = $gestao->findMaxVis();

$gestao = new gestaoDAO($conn, $BASE_URL);
$gestaoIdMax = $gestao->findMax();
$findMaxGesInt = $gestao->findMaxGesInt();

$uti = new utiDAO($conn, $BASE_URL);
$utiIdMax = $uti->findMaxUTI();

$prorrogacao = new prorrogacaoDAO($conn, $BASE_URL);
$prorrogacaoIdMax = $prorrogacao->findMaxPror();

$acomodacaoDao = new acomodacaoDAO($conn, $BASE_URL);
$acomodacao = $acomodacaoDao->findGeral();

$tuss = new tussAnsDAO($conn, $BASE_URL);
$tussGeral = $tuss->findAll();

$tussInt = new tussDAO($conn, $BASE_URL);

$id_internacao = filter_input(INPUT_GET, 'id_internacao', FILTER_VALIDATE_INT);

$visitasAntigas = $visita->findGeralByIntern($id_internacao);

$condicoes = [
    strlen($id_internacao) ? 'id_internacao = "' . $id_internacao . '"' : NULL,
];
$condicoes = array_filter($condicoes);
// REMOVE POSICOES VAZIAS DO FILTRO
$where = implode(' AND ', $condicoes);
$internacaoList = $internacaoDao->selectAllInternacaoVis($where, $order = null, $limit = null);
$tussIntern = $tussInt->selectAllTUSSByIntern($id_internacao);
$prorrogIntern = $prorrogacao->selectAllInternacaoProrrog($id_internacao);
extract($internacaoList);

$ultimaVis = end($internacaoList);
$ultimaReg = end($internacaoList);

$acomodacoes = $acomodacaoDao->findGeralByHospital($ultimaVis['id_hospital']);
$jsonAcomodacoes = json_encode($acomodacoes);

$antecedenteDao = new antecedenteDAO($conn, $BASE_URL);
$antecedentes = $antecedenteDao->findGeral();

?>

<div id="main-container" style="background-color: #f0f2f4ff;padding: 20px;">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <!-- FORMULARIO INTERNACAO -->
    <?php include_once('formularios/form_cad_visita.php'); ?>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>