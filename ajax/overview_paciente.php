<?php
// ajax/overview_paciente.php
header('Content-Type: application/json; charset=utf-8');
session_start();

// muda contexto p/ raiz
$ROOT = dirname(__DIR__);
chdir($ROOT);

require_once 'globals.php';
require_once 'db.php';
require_once 'models/message.php';
require_once 'models/internacao.php';
require_once 'dao/internacaoDao.php';
require_once 'models/visita.php';
require_once 'dao/visitaDao.php';

try {
  $pacId = filter_input(INPUT_GET, 'id_paciente', FILTER_VALIDATE_INT);
  if (!$pacId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'id_paciente obrigatório']);
    exit;
  }

  $internacaoDao = new internacaoDAO($conn, $BASE_URL);
  $visitaDao     = new visitaDAO($conn, $BASE_URL);

  $fmtDate = function ($d, $fmt='d/m/Y') {
    if (!$d || $d === '0000-00-00') return null;
    $dt = DateTime::createFromFormat('Y-m-d', $d) ?: new DateTime($d);
    return $dt ? $dt->format($fmt) : null;
  };

  // 1) Internação atual (se houver)
  $internacaoAtual = null;
  $idIntAtiva = $internacaoDao->checkInternAtiva($pacId); // retorna id ou null
  if ($idIntAtiva) {
    $int = $internacaoDao->findById($idIntAtiva); // objeto internacao (buildinternacao)
    if ($int) {
      $internacaoAtual = [
        'id_internacao' => (int)($int->id_internacao ?? 0),
        'data'          => $fmtDate($int->data_intern_int ?? null),
        'hora'          => $int->hora_intern_int ?? null,
        'hospital_id'   => (int)($int->fk_hospital_int ?? 0),
        'status'        => ($int->internado_int ?? '') === 's' ? 'Internado' : 'Alta',
        'acomodacao'    => $int->acomodacao_int ?? null,
        'especialidade' => $int->especialidade_int ?? null,
        'grupo_pat'     => $int->grupo_patologia_int ?? null,
      ];
    }
  } else {
    // opcional: pegar a última internação (sem estar internado) para mostrar algo
    // você já tem métodos para listar por paciente; se quiser, depois implemento aqui.
  }

  // 2) Última visita (por paciente) — usando seu selectUltimaVisitaComInternacao(where)
  // monta um WHERE seguro para o método (ele já faz LIMIT 1 e ordena por data desc)
  $safePac = (int)$pacId;
  $where = "ac.fk_paciente_int = {$safePac} AND vi.retificado IS NULL";
  $ultimaVisita = null;
  $vis = $visitaDao->selectUltimaVisitaComInternacao($where);
  if (!empty($vis)) {
    $v = $vis[0];
    $ultimaVisita = [
      'id_visita'     => (int)($v['id_visita'] ?? 0),
      'data'          => $fmtDate($v['data_visita_vis'] ?? null),
      'visita_no'     => (int)($v['visita_no_vis'] ?? 0),
      'usuario'       => $v['usuario_user'] ?? ($v['usuario_create'] ?? null),
      'hospital'      => $v['nome_hosp'] ?? null,
      'dias_desde'    => isset($v['dias_desde_ultima_visita']) ? (int)$v['dias_desde_ultima_visita'] : null
    ];
  }

  // 3) Prorrogações recentes — Aguardando seu DAO de prorrogação
  $prorrogacoes = []; // TODO quando você enviar o DAO/consulta

  // 4) Exames recentes — Aguardando seu DAO/consulta de exames
  $exames = []; // TODO quando você enviar o DAO/consulta

  echo json_encode([
    'success'          => true,
    'internacao_atual' => $internacaoAtual,
    'ultima_visita'    => $ultimaVisita,
    'prorrogacoes'     => $prorrogacoes,
    'exames'           => $exames
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error'   => 'Erro interno',
    'detail'  => $e->getMessage()
  ]);
}
