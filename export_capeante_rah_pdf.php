<?php

/**
 * export_capeante_rah_pdf.php — RAH PDF (compactado para caber em 1 página)
 * - Grupos vindos do FORM (POST) + fallback p/ campos legados
 * - APTO/ENF incluído antes de UTI
 * - ?compact=1  -> layout compacto
 * - ?ultra=1    -> layout ultra-compacto (ainda mais apertado)
 */


declare(strict_types=1);
@date_default_timezone_set('America/Sao_Paulo');
define('CUSTOM_TCPDF_PATH', 'D:/xampp/htdocs/FullConex/tcpdf_min/tcpdf.php');

/* ---------- HARDEN ---------- */
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '0');
@ini_set('output_buffering', '0');


if (!defined('K_PATH_MAIN'))  define('K_PATH_MAIN',  __DIR__ . '/tcpdf_min/');
if (!defined('K_PATH_FONTS')) define('K_PATH_FONTS', __DIR__ . '/tcpdf_min/fonts/');
if (!defined('K_PATH_CACHE')) {
  $cacheDir = __DIR__ . '/cache';
  if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
  define('K_PATH_CACHE', $cacheDir . DIRECTORY_SEPARATOR);
}

/* ---------- FLAGS ---------- */
$DEBUG    = isset($_GET['debug'])    && $_GET['debug']    === '1';
$SELFTEST = isset($_GET['selftest']) && $_GET['selftest'] === '1';
$HEALTH   = isset($_GET['health'])   && $_GET['health']   === '1';
$COMPACT  = isset($_GET['compact'])  && $_GET['compact']  === '1';
$ULTRA    = isset($_GET['ultra'])    && $_GET['ultra']    === '1';

@ini_set('display_errors', $DEBUG ? '1' : '0');
// error_reporting(EALL);

/* ---------- LOG ---------- */
$LOG_DIR  = __DIR__ . '/logs';
$LOG_FILE = $LOG_DIR . '/export_capeante_pdf.error.log';
if (!is_dir($LOG_DIR)) @mkdir($LOG_DIR, 0775, true);

/* ---------- HANDLERS ---------- */
set_error_handler(function ($sev, $msg, $file, $line) use ($LOG_FILE, $DEBUG) {
  $txt = "[PHP ERROR][$sev] $msg in $file:$line\n";
  @file_put_contents($LOG_FILE, $txt, FILE_APPEND);
  if ($DEBUG) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo $txt;
  }
  throw new ErrorException($msg, 0, $sev, $file, $line);
});
set_exception_handler(function ($ex) use ($LOG_FILE, $DEBUG) {
  $txt = "[UNCAUGHT] " . get_class($ex) . ": " . $ex->getMessage() . " in " . $ex->getFile() . ":" . $ex->getLine() . "\n" . $ex->getTraceAsString() . "\n";
  @file_put_contents($LOG_FILE, $txt, FILE_APPEND);
  if ($DEBUG) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo $txt;
  }
  http_response_code(500);
  exit;
});

/* ---------- LIMPA BUFFER ---------- */
if (function_exists('apache_setenv')) @apache_setenv('no-gzip', '1');
while (ob_get_level() > 0) @ob_end_clean();

/* ---------- PARAMS ---------- */
$idCapeante = (int)($_GET['id_capeante'] ?? $_POST['id_capeante'] ?? 0);
$saveOnly   = (($_GET['save_only'] ?? '0') === '1') || (($_POST['save_only'] ?? '0') === '1');
$download   = (($_GET['download']  ?? '0') === '1') || (($_POST['download']  ?? '0') === '1');
$preferPost = (($_GET['prefer_post'] ?? '0') === '1') || (($_POST['prefer_post'] ?? '0') === '1');
if ($_SERVER['REQUEST_METHOD'] === 'POST') $preferPost = true;

/* ---------- HELPERS ---------- */
function brl(float $v): string
{
  return 'R$ ' . number_format($v, 2, ',', '.');
}
function dt(?string $d): string
{
  if (!$d) return '';
  $t = strtotime($d);
  return $t ? date('d/m/Y', $t) : '';
}
function safe(mixed $s): string
{
  if ($s === null) return '';
  if (is_bool($s)) $s = $s ? '1' : '0';
  if (is_array($s) || is_object($s)) $s = json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function brl_to_float($s): float
{
  if ($s === null) return 0.0;
  $s = (string)$s;
  if ($s === '') return 0.0;
  $s = preg_replace('/[^\d.,\-]/', '', $s);
  $hasComma = strpos($s, ',') !== false;
  $hasDot   = strpos($s, '.') !== false;
  if ($hasComma) {
    // formato brasileiro (milhar . e decimal ,)
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '.', $s);
  } else {
    // sem vírgula: ponto é decimal ou simplesmente inteiro
    // apenas normaliza eventual vírgula isolada
    $s = str_replace(',', '.', $s);
  }
  $v = (float)$s;
  return is_finite($v) ? $v : 0.0;
}
function base_url_guess(): string
{
  if (!empty($GLOBALS['BASE_URL'])) return rtrim((string)$GLOBALS['BASE_URL'], '/');
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
  return $scheme . '://' . $host . ($dir ? $dir : '');
}

function group_from_db(?PDO $conn, int $fkCapeante, string $table, array $map): array
{
  if (!$conn || !$fkCapeante || !$table || !$map) return [];
  $stmt = $conn->prepare("SELECT * FROM {$table} WHERE fk_capeante = :fk LIMIT 1");
  $stmt->execute([':fk' => $fkCapeante]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) return [];
  $lines = [];
  foreach ($map as $label => $prefix) {
    $qtd = (int)($row[$prefix . '_qtd'] ?? 0);
    $cob = brl_to_float($row[$prefix . '_cobrado'] ?? $row[$prefix . '_cob'] ?? 0);
    $glo = brl_to_float($row[$prefix . '_glosado'] ?? $row[$prefix . '_glo'] ?? 0);
    $lib = brl_to_float($row[$prefix . '_liberado'] ?? $row[$prefix . '_lib'] ?? null);
    if (!isset($row[$prefix . '_liberado']) && !isset($row[$prefix . '_lib'])) $lib = max(0, $cob - $glo);
    $obs = (string)($row[$prefix . '_obs'] ?? '');
    if (!$qtd && !$cob && !$glo && trim($obs) === '' && trim($label) === '') continue;
    $lines[] = [
      'desc' => $label,
      'qtd' => $qtd,
      'cob_antes' => $cob,
      'glosa' => $glo,
      'apos' => $lib,
      'obs' => $obs
    ];
  }
  return $lines;
}

function group_from_row($row, array $map): array
{
  if (!$row) return [];
  if (is_object($row)) $row = get_object_vars($row);
  if (!is_array($row)) return [];
  $lines = [];
  foreach ($map as $label => $prefix) {
    $qtd = (int)($row[$prefix . '_qtd'] ?? 0);
    $cob = brl_to_float($row[$prefix . '_cobrado'] ?? $row[$prefix . '_cob'] ?? 0);
    $glo = brl_to_float($row[$prefix . '_glosado'] ?? $row[$prefix . '_glo'] ?? 0);
    $lib = brl_to_float($row[$prefix . '_liberado'] ?? $row[$prefix . '_lib'] ?? null);
    if (!isset($row[$prefix . '_liberado']) && !isset($row[$prefix . '_lib'])) $lib = max(0, $cob - $glo);
    $obs = (string)($row[$prefix . '_obs'] ?? '');
    if (!$qtd && !$cob && !$glo && trim($obs) === '' && trim($label) === '') continue;
    $lines[] = ['desc' => $label, 'qtd' => $qtd, 'cob_antes' => $cob, 'glosa' => $glo, 'apos' => $lib, 'obs' => $obs];
  }
  return $lines;
}

/* ---------- TCPDF ---------- */
function require_tcpdf_or_throw(): void
{
  $paths = [
    __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php',
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/tcpdf_min/tcpdf.php',
    __DIR__ . '/lib/tcpdf/tcpdf.php',
  ];
  foreach ($paths as $p) {
    if (is_file($p)) {
      require_once $p;
      if (class_exists('TCPDF', false) || class_exists('TCPDF')) return;
    }
  }
  header('Content-Type: application/json; charset=UTF-8', true, 500);
  echo json_encode(['ok' => false, 'error' => 'TCPDF não encontrado (vendor/tecnickcom/tcpdf ou tcpdf_min).']);
  exit;
}

/* ---------- HEALTH / SELFTEST ---------- */
if ($HEALTH) {
  $resp = ['ok' => true, 'tcpdf' => false, 'db' => false, 'exports' => false, 'exports_path' => __DIR__ . '/exports'];
  try {
    require_tcpdf_or_throw();
    $resp['tcpdf'] = true;
  } catch (Throwable $e) {
    $resp['tcpdf_err'] = $e->getMessage();
  }
  try {
    if (!isset($conn) || !($conn instanceof PDO)) foreach ([__DIR__ . '/globals.php', __DIR__ . '/db.php', __DIR__ . '/config.php'] as $cfg) if (is_file($cfg)) require_once $cfg;
    if (!isset($conn) || !($conn instanceof PDO)) {
      $dsn = "mysql:host=" . (getenv('DB_HOST') ?: 'localhost') . ";dbname=" . (getenv('DB_NAME') ?: 'fullconex') . ";charset=utf8mb4";
      $user = getenv('DB_USER') ?: 'root';
      $pass = getenv('DB_PASS') ?: 'mysql';
      $conn = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    }
    $conn->query('SELECT 1');
    $resp['db'] = true;
  } catch (Throwable $e) {
    $resp['db_err'] = $e->getMessage();
  }
  $dir = __DIR__ . '/exports';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  $resp['exports'] = is_dir($dir) && is_writable($dir);
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($resp);
  exit;
}
if ($SELFTEST) {
  require_tcpdf_or_throw();
  $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
  $pdf->setPrintHeader(false);
  $pdf->setPrintFooter(false);
  $pdf->AddPage();
  $pdf->SetFont('helvetica', 'B', 14);
  $pdf->Cell(0, 10, 'Selftest OK - TCPDF carregado e saída funcionando', 0, 1, 'C');
  while (ob_get_level() > 0) @ob_end_clean();
  $pdf->Output('selftest.pdf', 'I');
  exit;
}

/* ---------- DB (cabeçalho) ---------- */
if (!isset($conn) || !($conn instanceof PDO)) foreach ([__DIR__ . '/globals.php', __DIR__ . '/db.php', __DIR__ . '/config.php'] as $cfg) if (is_file($cfg)) require_once $cfg;

require_once __DIR__ . '/dao/CapValoresAPDao.php';
require_once __DIR__ . '/dao/CapValoresUTIDao.php';
require_once __DIR__ . '/dao/CapValoresCCDao.php';
require_once __DIR__ . '/dao/CapValoresOutDao.php';
require_once __DIR__ . '/dao/CapValoresDiarDao.php';

$capValoresApDao   = new CapValoresAPDAO($conn);
$capValoresUtiDao  = new CapValoresUTIDAO($conn);
$capValoresCcDao   = new CapValoresCCDAO($conn);
$capValoresOutDao  = new CapValoresOutDAO($conn);
$capValoresDiarDao = new CapValoresDiarDAO($conn);
if (!isset($conn) || !($conn instanceof PDO)) {
  $dsn = "mysql:host=" . (getenv('DB_HOST') ?: 'localhost') . ";dbname=" . (getenv('DB_NAME') ?: 'fullconex') . ";charset=utf8mb4";
  $user = getenv('DB_USER') ?: 'root';
  $pass = getenv('DB_PASS') ?: 'mysql';
  $conn = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
}
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ---------- VALIDACAO ---------- */
if ($idCapeante <= 0) {
  header('Content-Type: application/json; charset=UTF-8', true, 400);
  echo json_encode(['ok' => false, 'error' => 'Parâmetro id_capeante ausente ou inválido.']);
  exit;
}

/* ---------- SELECT PRINCIPAL ---------- */
$sql = "
SELECT
  c.*,
  i.id_internacao, i.data_intern_int, i.hora_intern_int, i.senha_int, i.num_atendimento_int,
  p.id_paciente, p.nome_pac, p.cpf_pac, p.data_nasc_pac,
  h.id_hospital, h.nome_hosp, h.cnpj_hosp,
  al.data_alta_alt AS data_alta_alt,
  pr.prorrog1_ini_pror AS prorroga_inicio, pr.prorrog1_fim_pror AS prorroga_fim
FROM tb_capeante c
LEFT JOIN tb_internacao  i  ON i.id_internacao = c.fk_int_capeante
LEFT JOIN tb_paciente    p  ON p.id_paciente   = i.fk_paciente_int
LEFT JOIN tb_hospital    h  ON h.id_hospital   = i.fk_hospital_int
LEFT JOIN (
  SELECT x.* FROM tb_prorrogacao x
  WHERE x.id_prorrogacao = (
    SELECT x2.id_prorrogacao FROM tb_prorrogacao x2
    WHERE x2.fk_internacao_pror = x.fk_internacao_pror
    ORDER BY COALESCE(x2.prorrog1_fim_pror, x2.prorrog1_ini_pror) DESC, x2.id_prorrogacao DESC
    LIMIT 1
  )
) pr ON pr.fk_internacao_pror = i.id_internacao
LEFT JOIN (
  SELECT a.* FROM tb_alta a
  WHERE a.id_alta = (
    SELECT a2.id_alta FROM tb_alta a2
    WHERE a2.fk_id_int_alt = a.fk_id_int_alt
    ORDER BY COALESCE(a2.data_alta_alt, '0000-00-00') DESC, a2.id_alta DESC
    LIMIT 1
  )
) al ON al.fk_id_int_alt = i.id_internacao
WHERE c.id_capeante = :id
LIMIT 1";
$st = $conn->prepare($sql);
$st->execute([':id' => $idCapeante]);
$dados = $st->fetch();
if (!$dados) {
  header('Content-Type: application/json; charset=UTF-8', true, 404);
  echo json_encode(['ok' => false, 'error' => "Capeante não encontrado (id_capeante={$idCapeante})."]);
  exit;
}

/* ---------- MERGE POST (escalares) ---------- */
if ($preferPost && !empty($_POST)) {
  foreach ($_POST as $k => $v) if (is_string($k) && !is_array($v) && !is_object($v)) $dados[$k] = $v;
  foreach ($dados as $k => $v) {
    if (!is_string($k) || !is_string($v)) continue;
    if (in_array($k, ['valor_apresentado_capeante', 'valor_final_capeante'], true)) $dados[$k] = brl_to_float($v);
  }
  if (isset($dados['comentarios_obs'])) {
    $comentariosObs = trim((string)$dados['comentarios_obs']);
  }
}

/* ---------- CAMPOS CABEÇALHO ---------- */
$hospitalNome   = $dados['nome_hosp'] ?? '';
$hospitalCNPJ   = $dados['cnpj_hosp'] ?? '';
$pacienteNome   = $dados['nome_pac'] ?? '';
$pacienteCPF    = $dados['cpf_pac'] ?? '';
$senhaAut       = $dados['senha_int'] ?? '';
$matricula      = $dados['num_atendimento_int'] ?? '';
$dataInternacao = $dados['data_intern_int'] ?? '';
$horaInternacao = $dados['hora_intern_int'] ?? '';
$dataAlta       = $dados['data_alta_alt'] ?? '';
$idade = '';
if (!empty($dados['data_nasc_pac'])) {
  $n = new DateTime((string)$dados['data_nasc_pac']);
  $idade = $n->diff(new DateTime('today'))->y . ' anos';
}
$periodoIni = $dados['data_inicial_capeante'] ?: ($dados['data_intern_int'] ?? '');
$periodoFim = $dados['data_final_capeante']   ?: '';
$tipoConta  = (!empty($dados['parcial_capeante']) && $dados['parcial_capeante'] === 's') ? ('Parcial ' . (string)($dados['parcial_num'] ?? '')) : 'Conta Única';
$visaoConta = $dados['acomodacao_cap'] ?? ($dados['acomodacao_int'] ?? '');
$contaAuditada = (isset($dados['encerrado_cap']) && $dados['encerrado_cap'] === 's') ? 'Sim' : 'Não';
$prorrogacaoTxt = (!empty($dados['prorrog1_ini_pror']) || !empty($dados['prorrog1_fim_pror']))
  ? (dt($dados['prorrog1_ini_pror'] ?? '') . ' a ' . dt($dados['prorrog1_fim_pror'] ?? ''))
  : ((!empty($dados['prorroga_inicio']) || !empty($dados['prorroga_fim'])) ? (dt($dados['prorroga_inicio']) . ' a ' . dt($dados['prorroga_fim'])) : '');

/* ---------- GRUPOS (POST + fallback legado) ---------- */
function rows_from_post(?array $arr): array
{
  if (!is_array($arr)) return [];
  $out = [];
  foreach ($arr as $ln) {
    if (!is_array($ln)) continue;
    $desc = (string)($ln['desc'] ?? '');
    $qtd  = (int)($ln['qtd'] ?? 0);
    $cob  = brl_to_float($ln['valor_cobrado'] ?? $ln['cobrado'] ?? $ln['cob'] ?? 0);
    $glo  = brl_to_float($ln['valor_glosado']  ?? $ln['glosado'] ?? $ln['glo'] ?? 0);
    $lib  = array_key_exists('valor_liberado', $ln) ? brl_to_float($ln['valor_liberado']) : max(0, $cob - $glo);
    $obs  = (string)($ln['obs'] ?? '');
    if ($desc === '' && !$qtd && !$cob && !$glo && $obs === '') continue;
    $out[] = ['desc' => $desc, 'qtd' => $qtd, 'cob_antes' => $cob, 'glosa' => $glo, 'apos' => $lib, 'obs' => $obs];
  }
  return $out;
}
$diarias   = rows_from_post($_POST['diarias']   ?? null);
$apto      = rows_from_post($_POST['apto']      ?? null);
$uti       = rows_from_post($_POST['uti']       ?? null);
$cc        = rows_from_post($_POST['cc']        ?? null);
$exames    = rows_from_post($_POST['exames']    ?? null);
$materiais = rows_from_post($_POST['materiais'] ?? null);
$hon       = rows_from_post($_POST['hon']       ?? null);
$outros    = rows_from_post($_POST['outros']    ?? null);

/* Fallback legado */
$legacy_line = function (string $pfx, string $desc) {
  $qtd = (int)($_POST[$pfx . '_qtd'] ?? 0);
  $cob = brl_to_float($_POST[$pfx . '_cobrado'] ?? 0);
  $glo = brl_to_float($_POST[$pfx . '_glosado'] ?? 0);
  $lib = max(0, $cob - $glo);
  if (!$qtd && !$cob && !$glo && trim((string)$desc) === '') return null;
  return ['desc' => $desc, 'qtd' => $qtd, 'cob_antes' => $cob, 'glosa' => $glo, 'apos' => $lib, 'obs' => (string)($_POST[$pfx . '_obs'] ?? '')];
};
$ensure_group = function (array &$grp) {
  if (!is_array($grp)) $grp = [];
};

if (empty($diarias)) {
  $diarRow = $capValoresDiarDao->findByCapeante($idCapeante);
  if ($diarRow) $diarias = group_from_row($diarRow, [
    'Quarto / Apto'    => 'ac_quarto',
    'Day Clinic'       => 'ac_dayclinic',
    'UTI'              => 'ac_uti',
    'UTI / Semi'       => 'ac_utisemi',
    'Enfermaria'       => 'ac_enfermaria',
    'Berçário'         => 'ac_bercario',
    'Acompanhante'     => 'ac_acompanhante',
    'Isolamento'       => 'ac_isolamento',
  ]);
}
if (empty($diarias)) {
  $diarias = group_from_db($conn, $idCapeante, 'tb_cap_valores_diar', [
    'Quarto / Apto'    => 'ac_quarto',
    'Day Clinic'       => 'ac_dayclinic',
    'UTI'              => 'ac_uti',
    'UTI / Semi'       => 'ac_utisemi',
    'Enfermaria'       => 'ac_enfermaria',
    'Berçário'         => 'ac_bercario',
    'Acompanhante'     => 'ac_acompanhante',
    'Isolamento'       => 'ac_isolamento',
  ]);
}
if (empty($apto)) {
  $apRow = $capValoresApDao->findByCapeante($idCapeante);
  if ($apRow) $apto = group_from_row($apRow, [
    'Terapias (AP)'       => 'ap_terapias',
    'Taxas (AP)'          => 'ap_taxas',
    'Mat. Consumo (AP)'   => 'ap_mat_consumo',
    'Medicamentos (AP)'   => 'ap_medicametos',
    'Gases (AP)'          => 'ap_gases',
    'OPME (AP)'           => 'ap_mat_espec',
    'Exames (AP)'         => 'ap_exames',
    'Hemoderivados (AP)'  => 'ap_hemoderivados',
    'Honorários (AP)'     => 'ap_honorarios',
  ]);
}
if (empty($apto)) {
  $apto = group_from_db($conn, $idCapeante, 'tb_cap_valores_ap', [
    'Terapias (AP)'       => 'ap_terapias',
    'Taxas (AP)'          => 'ap_taxas',
    'Mat. Consumo (AP)'   => 'ap_mat_consumo',
    'Medicamentos (AP)'   => 'ap_medicametos',
    'Gases (AP)'          => 'ap_gases',
    'OPME (AP)'           => 'ap_mat_espec',
    'Exames (AP)'         => 'ap_exames',
    'Hemoderivados (AP)'  => 'ap_hemoderivados',
    'Honorários (AP)'     => 'ap_honorarios',
  ]);
}
if (empty($uti)) {
  $utiRow = $capValoresUtiDao->findByCapeante($idCapeante);
  if ($utiRow) $uti = group_from_row($utiRow, [
    'Terapias (UTI)'       => 'uti_terapias',
    'Taxas (UTI)'          => 'uti_taxas',
    'Mat. Consumo (UTI)'   => 'uti_mat_consumo',
    'Medicamentos (UTI)'   => 'uti_medicametos',
    'Gases (UTI)'          => 'uti_gases',
    'OPME (UTI)'           => 'uti_mat_espec',
    'Exames (UTI)'         => 'uti_exames',
    'Hemoderivados (UTI)'  => 'uti_hemoderivados',
    'Honorários (UTI)'     => 'uti_honorarios',
  ]);
}
if (empty($uti)) {
  $uti = group_from_db($conn, $idCapeante, 'tb_cap_valores_uti', [
    'Terapias (UTI)'       => 'uti_terapias',
    'Taxas (UTI)'          => 'uti_taxas',
    'Mat. Consumo (UTI)'   => 'uti_mat_consumo',
    'Medicamentos (UTI)'   => 'uti_medicametos',
    'Gases (UTI)'          => 'uti_gases',
    'OPME (UTI)'           => 'uti_mat_espec',
    'Exames (UTI)'         => 'uti_exames',
    'Hemoderivados (UTI)'  => 'uti_hemoderivados',
    'Honorários (UTI)'     => 'uti_honorarios',
  ]);
}
if (empty($cc)) {
  $ccRow = $capValoresCcDao->findByCapeante($idCapeante);
  if ($ccRow) $cc = group_from_row($ccRow, [
    'Terapias (CC)'       => 'cc_terapias',
    'Taxas (CC)'          => 'cc_taxas',
    'Mat. Consumo (CC)'   => 'cc_mat_consumo',
    'Medicamentos (CC)'   => 'cc_medicametos',
    'Gases (CC)'          => 'cc_gases',
    'OPME (CC)'           => 'cc_mat_espec',
    'Exames (CC)'         => 'cc_exames',
    'Hemoderivados (CC)'  => 'cc_hemoderivados',
    'Honorários (CC)'     => 'cc_honorarios',
  ]);
}
if (empty($cc)) {
  $cc = group_from_db($conn, $idCapeante, 'tb_cap_valores_cc', [
    'Terapias (CC)'       => 'cc_terapias',
    'Taxas (CC)'          => 'cc_taxas',
    'Mat. Consumo (CC)'   => 'cc_mat_consumo',
    'Medicamentos (CC)'   => 'cc_medicametos',
    'Gases (CC)'          => 'cc_gases',
    'OPME (CC)'           => 'cc_mat_espec',
    'Exames (CC)'         => 'cc_exames',
    'Hemoderivados (CC)'  => 'cc_hemoderivados',
    'Honorários (CC)'     => 'cc_honorarios',
  ]);
}
$comentariosObs = '';
if (empty($outros)) {
  $outRow = $capValoresOutDao->findByCapeante($idCapeante);
  if ($outRow) {
    $comentariosObs = trim((string)($outRow['comentarios_obs'] ?? ''));
    $outros = group_from_row($outRow, [
      'Pacote'  => 'outros_pacote',
      'Remoção' => 'outros_remocao',
    ]);
  }
}
if (empty($outros)) {
  $outros = group_from_db($conn, $idCapeante, 'tb_cap_valores_out', [
    'Pacote'  => 'outros_pacote',
    'Remoção' => 'outros_remocao',
  ]);
}

if (empty($diarias)) {
  $ensure_group($diarias);
  $map = [
    'ac_quarto'       => 'Quarto',
    'ac_dayclinic'    => 'Day Clinic',
    'ac_uti'          => 'UTI',
    'ac_utisemi'      => 'UTI Semi',
    'ac_enfermaria'   => 'Enfermaria',
    'ac_bercario'     => 'Berçário',
    'ac_acompanhante' => 'Acompanhante',
    'ac_isolamento'   => 'Isolamento',
  ];
  foreach ($map as $pfx => $label) if ($ln = $legacy_line($pfx, $label)) $diarias[] = $ln;
}
if (empty($apto)) {
  $ensure_group($apto);
  $map = [
    'ap_terapias'      => 'Terapias (AP)',
    'ap_taxas'         => 'Taxas (AP)',
    'ap_mat_consumo'   => 'Mat. Consumo (AP)',
    'ap_medicametos'   => 'Medicamentos (AP)',
    'ap_gases'         => 'Gases (AP)',
    'ap_mat_espec'     => 'OPME (AP)',
    'ap_exames'        => 'Exames (AP)',
    'ap_hemoderivados' => 'Hemoderivados (AP)',
    'ap_honorarios'    => 'Honorários (AP)',
  ];
  foreach ($map as $pfx => $label) if ($ln = $legacy_line($pfx, $label)) $apto[] = $ln;
}
if (empty($uti)) {
  $ensure_group($uti);
  $map = [
    'uti_terapias'      => 'Terapias (UTI)',
    'uti_taxas'         => 'Taxas (UTI)',
    'uti_mat_consumo'   => 'Mat. Consumo (UTI)',
    'uti_medicametos'   => 'Medicamentos (UTI)',
    'uti_gases'         => 'Gases (UTI)',
    'uti_mat_espec'     => 'OPME (UTI)',
    'uti_hemoderivados' => 'Hemoderivados (UTI)',
  ];
  foreach ($map as $pfx => $label) if ($ln = $legacy_line($pfx, $label)) $uti[] = $ln;
}
if (empty($cc)) {
  $ensure_group($cc);
  $map = [
    'cc_terapias'      => 'Terapias (CC)',
    'cc_taxas'         => 'Taxas (CC)',
    'cc_mat_consumo'   => 'Mat. Consumo (CC)',
    'cc_medicametos'   => 'Medicamentos (CC)',
    'cc_gases'         => 'Gases (CC)',
    'cc_mat_espec'     => 'OPME (CC)',
    'cc_hemoderivados' => 'Hemoderivados (CC)',
  ];
  foreach ($map as $pfx => $label) if ($ln = $legacy_line($pfx, $label)) $cc[] = $ln;
}
if (empty($outros)) {
  $ensure_group($outros);
  $map = ['outros_pacote' => 'Pacote', 'outros_remocao' => 'Remoção'];
  foreach ($map as $pfx => $label) if ($ln = $legacy_line($pfx, $label)) $outros[] = $ln;
}

/* ---------- PDF CONFIG ---------- */
require_tcpdf_or_throw();
class PDFCapeanteRAH extends TCPDF {}

$MARGIN_LR   = $ULTRA ? 5 : ($COMPACT ? 6 : 10);
$MARGIN_TOP  = $ULTRA ? 7 : ($COMPACT ? 9 : 14);
$MARGIN_BOT  = $ULTRA ? 6 : ($COMPACT ? 8 : 16);
$BASE_FONT   = $ULTRA ? 6.5 : ($COMPACT ? 7 : 8);
$H_RATIO     = $ULTRA ? 0.98 : ($COMPACT ? 1.05 : 1.2);

$BORDER_MAIN = $ULTRA ? '0.5px' : ($COMPACT ? '0.6px' : '0.7px');
$BORDER_CELL = $BORDER_MAIN;
$BORDER_CLR1 = '#666';
$BORDER_CLR2 = '#9e9e9e';
$SHADE_BG    = '#f6f6f6';
$HEADLINE_W  = $ULTRA ? '0.7px' : ($COMPACT ? '0.8px' : '1.0px');

$PAD_CELL    = $ULTRA ? '1.3px' : ($COMPACT ? '1.5px' : '2px');
$GROUP_GAP   = $ULTRA ? 0.3 : ($COMPACT ? 0.5 : 1.0);

/* Título fixo p/ TODOS os grupos */
$TITLE_SIZE  = $ULTRA ? 6.0 : ($COMPACT ? 6.2 : 7.0);

/* Fontes específicas p/ números (Qtd e valores) */
$BODY_SIZE_PT = $ULTRA ? max($BASE_FONT - 0.5, 6) : ($COMPACT ? max($BASE_FONT - 1, 6) : $BASE_FONT);
$NUM_SIZE_PT  = max($BODY_SIZE_PT - 0.6, 5.5); // << menor que corpo, apenas Qtd/Cob/Glo/Lib

$pdf = new PDFCapeanteRAH('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle('RAH - Capeante ' . $idCapeante);
$pdf->SetMargins($MARGIN_LR, $MARGIN_TOP, $MARGIN_LR);
$pdf->SetAutoPageBreak(true, 28);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->setFontSubsetting(true);
$pdf->setCellHeightRatio($H_RATIO);
$pdf->setCellPaddings(0.5, 0.6, 0.5, 0.6);
$pdf->AddPage();
$pdf->SetFooterMargin(15);
$pdf->SetFont('helvetica', '', $BASE_FONT);

/* ========================= CABEÇALHO RAH (refatorado) ========================= */

// — Título —
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'RELATÓRIO DE AUDITORIA HOSPITALAR - RAH', 0, 1, 'C');
$pdf->Ln(2.5); // respiro entre o título e os dados

// — Fonte padrão dos dados —
$pdf->SetFont('helvetica', '', 9.5);

// helper de label/value
$lbl = 'font-weight:bold;';
$val = 'font-weight:normal;';

// — Tabela em duas colunas, com mais padding e melhor espaçamento de linha —
// Valores já formatados (evita label vazio ocupar espaço)
$dtIntern = isset($dados['data_intern_int']) && $dados['data_intern_int']
  ? date('d/m/Y', strtotime($dados['data_intern_int'])) : '';

$dtAlta   = isset($dados['data_alta_int']) && $dados['data_alta_int']
  ? date('d/m/Y', strtotime($dados['data_alta_int'])) : '';

$dtCobIni = isset($dados['data_cob_ini']) && $dados['data_cob_ini']
  ? date('d/m/Y', strtotime($dados['data_cob_ini'])) : '';

$dtCobFim = isset($dados['data_cob_fim']) && $dados['data_cob_fim']
  ? date('d/m/Y', strtotime($dados['data_cob_fim'])) : '';

// Datas do CAPEANTE vindas do formulário (com fallbacks seguros)
$capIniRaw = $dados['data_inicial_capeante'] ?? $dados['data_inicial_cap'] ?? $dados['data_inicial'] ?? null;
$capFimRaw = $dados['data_final_capeante']   ?? $dados['data_final_cap']   ?? $dados['data_final']   ?? null;

// se não houver inicial, usa data de internação; se não houver final, deixa vazio
$capIni = ($capIniRaw && $capIniRaw !== '0000-00-00') ? date('d/m/Y', strtotime($capIniRaw))
  : (isset($dados['data_intern_int']) && $dados['data_intern_int'] ? date('d/m/Y', strtotime($dados['data_intern_int'])) : '');

$capFim = ($capFimRaw && $capFimRaw !== '0000-00-00') ? date('d/m/Y', strtotime($capFimRaw)) : '';

$idade    = $dados['idade_pac'] ?? ($dados['idade'] ?? '');
$visao    = $dados['visao_cap'] ?? '';
$matric   = $dados['matricula'] ?? '';

$audOK = (($dados['em_auditoria_cap'] ?? 'n') === 's' || ($dados['senha_finalizada'] ?? 'n') === 's') ? 'Sim' : 'Não';

// Paleta/estilo

// estilos
$cardBG = '#FAFBFD';
$lineCL = '#E5EAF2';
$lbl    = 'font-weight:800;';          // antes era bold
$val    = 'font-weight:normal;';

$infoHTML = '
<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:2px;">
  <tr><td>
    <table width="100%" cellspacing="0" cellpadding="2" border="0"
           style="background-color:' . $cardBG . '; border:1px solid ' . $lineCL . '; line-height:1.15; font-size:9pt;">

      <!-- L1 -->
      <tr>
        <td width="58%" style="border-right:1px solid ' . $lineCL . ';">
          <span style="' . $lbl . '">Referenciado:</span>
          <span style="' . $val . '">' . safe($nome_hosp ?? ($dados['nome_hosp'] ?? '')) . '</span>
        </td>
        <td width="42%">
          <span style="' . $lbl . '">Data de Internação:</span>
          <span style="' . $val . '">' . safe($dtIntern) . '</span>'
  . ($dtAlta ? ' &nbsp; <span style="' . $lbl . '">Alta:</span> <span style="' . $val . '">' . safe($dtAlta) . '</span>' : '') .
  '</td>
      </tr>

      <!-- L2 -->
      <tr>
        <td width="58%" style="border-right:1px solid ' . $lineCL . ';">
          <span style="' . $lbl . '">CNPJ:</span>
          <span style="' . $val . '">' . safe($dados['cnpj_hosp'] ?? '') . '</span>'
  . ($matric ? ' &nbsp; <span style="' . $lbl . '">Matrícula:</span> <span style="' . $val . '">' . safe($matric) . '</span>' : '') .
  '</td>
        <td width="42%">
          <span style="' . $lbl . '">Senha:</span>
          <span style="' . $val . '">' . safe($dados['senha_int'] ?? '') . '</span>
          &nbsp; <span style="' . $lbl . '">Auditada?</span>
          <span style="' . $val . '">' . $audOK . '</span>
        </td>
      </tr>

      <!-- L3 -->
      <tr>
        <td width="58%" style="border-right:1px solid ' . $lineCL . ';">
          <span style="' . $lbl . '">Paciente:</span>
          <span style="' . $val . '">' . safe($dados['nome_pac'] ?? '') . '</span>
        </td>
        <td width="42%">'
  . ($idade ? '<span style="' . $lbl . '">Idade:</span> <span style="' . $val . '">' . safe($idade) . '</span>' : '') .
  '</td>
      </tr>

      <!-- L4 -->
      <tr>
        <td width="58%" style="border-right:1px solid ' . $lineCL . ';">
          <span style="' . $lbl . '">Período de Cobrança:</span>
          <span style="' . $val . '">' . safe($capIni) . (($capIni || $capFim) ? ' a ' : '') . safe($capFim) . '</span>
        </td>
        <td width="42%">
          <span style="' . $lbl . '">Tipo:</span>
          <span style="' . $val . '">' . safe($dados['tipo_conta'] ?? 'Conta Única') . '</span>'
  . ($visao ? ' &nbsp; <span style="' . $lbl . '">Visão:</span> <span style="' . $val . '">' . safe($visao) . '</span>' : '') .
  '</td>
      </tr>

    </table>
  </td></tr>
</table>
';




// escreve o bloco de informações
$pdf->writeHTML($infoHTML, true, false, true, false, '');

// — Linha divisória com margem superior/inf —
$pdf->Ln(1.5);
$m = $pdf->getMargins();
$left = $m['left'];
$right = $m['right'];
$usable = $pdf->getPageWidth() - $left - $right;

$pdf->SetDrawColor(120, 120, 120);
$pdf->SetLineWidth(0.5);
$y = $pdf->GetY();
$pdf->Line($left, $y, $left + $usable, $y);
$pdf->Ln(2.5); // respiro após a linha
/* ======================= FIM CABEÇALHO RAH (refatorado) ======================= */

/* ---------- TABELAS DE GRUPO ---------- */
function renderGroupTable(
  TCPDF $pdf,
  string $titulo,
  array $linhas,
  bool $compact,
  bool $ultra,
  int $fontBase,
  string $borderMain,
  string $borderCell,
  string $clrMain,
  string $clrCell,
  string $bg,
  string $pad,
  float $gapMM,
  float $titleFontOverride,
  float $bodySizePt,
  float $numSizePt
): void {
  // filtra linhas vazias
  $linhas = array_values(array_filter($linhas, function ($r) {
    $d = trim((string)($r['desc'] ?? ''));
    return $d !== '' || (int)($r['qtd'] ?? 0) || (float)($r['cob_antes'] ?? 0) || (float)($r['glosa'] ?? 0) || (float)($r['apos'] ?? 0);
  }));
  if (empty($linhas)) return;

  // Título (tamanho fixo)
  $pdf->SetFont('helvetica', 'B', $titleFontOverride);
  $pdf->writeHTML('<div style="margin:0;padding:0;line-height:1.05;">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</div>', false, false, false, false, '');

  // Tabela – corpo no bodySizePt, números no numSizePt (por CSS inline)
  $pdf->SetFont('helvetica', '', $bodySizePt);

  $thCob = ($compact || $ultra) ? 'Cob.' : 'Cobrado';
  $thGlo = ($compact || $ultra) ? 'Glo.' : 'Glosado';
  $thLib = ($compact || $ultra) ? 'Lib.' : 'Liberado';

  $wDesc = $ultra ? '51%' : ($compact ? '50%' : '46%');
  $wQtd  = $ultra ? '6%'  : ($compact ? '7%'  : '8%');
  $wVal  = $ultra ? '14%' : ($compact ? '14%' : '15%');

  $thead = '
  <style>
    .tb { border:' . $borderMain . ' solid ' . $clrMain . '; border-collapse:collapse; margin:0; }
    .tb td { border:' . $borderCell . ' solid ' . $clrCell . '; padding:' . $pad . '; }
    .th { font-weight:bold; background-color:' . $bg . '; }
  </style>
  <table class="tb" cellpadding="0" cellspacing="0" width="100%" style="margin:0;">
    <tr class="th">
      <td width="' . $wDesc . '">Descrição</td>
      <td width="' . $wQtd . '" align="center">Qtd</td>
      <td width="' . $wVal . '" align="right">' . $thCob . '</td>
      <td width="' . $wVal . '" align="right">' . $thGlo . '</td>
      <td width="' . $wVal . '" align="right">' . $thLib . '</td>
    </tr>';

  $rows = '';
  foreach ($linhas as $ln) {
    $desc = htmlspecialchars((string)($ln['desc'] ?? ''), ENT_QUOTES, 'UTF-8');
    $qtd  = (int)($ln['qtd'] ?? 0);
    $cob  = (float)($ln['cob_antes'] ?? 0);
    $glo  = (float)($ln['glosa'] ?? 0);
    $apos = array_key_exists('apos', $ln) ? (float)$ln['apos'] : max(0, $cob - $glo);

    $rows .= '<tr>
      <td>' . $desc . '</td>
      <td align="center" style="font-size:' . $numSizePt . 'pt; padding-left:1px; padding-right:1px;">' . $qtd . '</td>
      <td align="right"  style="font-size:' . $numSizePt . 'pt; padding-left:1px; padding-right:1px;">' . brl($cob) . '</td>
      <td align="right"  style="font-size:' . $numSizePt . 'pt; padding-left:1px; padding-right:1px;">' . brl($glo) . '</td>
      <td align="right"  style="font-size:' . $numSizePt . 'pt; padding-left:1px; padding-right:1px;">' . brl($apos) . '</td>
    </tr>';
  }

  $pdf->writeHTML($thead . $rows . '</table>', false, false, true, false, '');
  if ($gapMM > 0) $pdf->Ln($gapMM);
}

/* ---------- IMPRIME GRUPOS (ordem) ---------- */
renderGroupTable($pdf, 'DIÁRIAS',                       $diarias,   $COMPACT, $ULTRA, $BASE_FONT, $BORDER_MAIN, $BORDER_CELL, $BORDER_CLR1, $BORDER_CLR2, $SHADE_BG, $PAD_CELL, $GROUP_GAP, $TITLE_SIZE, $BODY_SIZE_PT, $NUM_SIZE_PT);
renderGroupTable($pdf, 'DESPESAS NO APTO / ENFERMARIA', $apto,      $COMPACT, $ULTRA, $BASE_FONT, $BORDER_MAIN, $BORDER_CELL, $BORDER_CLR1, $BORDER_CLR2, $SHADE_BG, $PAD_CELL, $GROUP_GAP, $TITLE_SIZE, $BODY_SIZE_PT, $NUM_SIZE_PT);
renderGroupTable($pdf, 'DESPESAS NA UTI',               $uti,       $COMPACT, $ULTRA, $BASE_FONT, $BORDER_MAIN, $BORDER_CELL, $BORDER_CLR1, $BORDER_CLR2, $SHADE_BG, $PAD_CELL, $GROUP_GAP, $TITLE_SIZE, $BODY_SIZE_PT, $NUM_SIZE_PT);
renderGroupTable($pdf, 'DESPESAS NO CENTRO CIRÚRGICO',  $cc,        $COMPACT, $ULTRA, $BASE_FONT, $BORDER_MAIN, $BORDER_CELL, $BORDER_CLR1, $BORDER_CLR2, $SHADE_BG, $PAD_CELL, $GROUP_GAP, $TITLE_SIZE, $BODY_SIZE_PT, $NUM_SIZE_PT);
renderGroupTable($pdf, 'EXAMES',                        $exames,    $COMPACT, $ULTRA, $BASE_FONT, $BORDER_MAIN, $BORDER_CELL, $BORDER_CLR1, $BORDER_CLR2, $SHADE_BG, $PAD_CELL, $GROUP_GAP, $TITLE_SIZE, $BODY_SIZE_PT, $NUM_SIZE_PT);
renderGroupTable($pdf, 'MATERIAIS / OPME',              $materiais, $COMPACT, $ULTRA, $BASE_FONT, $BORDER_MAIN, $BORDER_CELL, $BORDER_CLR1, $BORDER_CLR2, $SHADE_BG, $PAD_CELL, $GROUP_GAP, $TITLE_SIZE, $BODY_SIZE_PT, $NUM_SIZE_PT);
renderGroupTable($pdf, 'HONORÁRIOS',                    $hon,       $COMPACT, $ULTRA, $BASE_FONT, $BORDER_MAIN, $BORDER_CELL, $BORDER_CLR1, $BORDER_CLR2, $SHADE_BG, $PAD_CELL, $GROUP_GAP, $TITLE_SIZE, $BODY_SIZE_PT, $NUM_SIZE_PT);
renderGroupTable($pdf, 'OUTROS',                        $outros,    $COMPACT, $ULTRA, $BASE_FONT, $BORDER_MAIN, $BORDER_CELL, $BORDER_CLR1, $BORDER_CLR2, $SHADE_BG, $PAD_CELL, $GROUP_GAP, $TITLE_SIZE, $BODY_SIZE_PT, $NUM_SIZE_PT);

/* ---------- TOTAIS ---------- */
$sum = function (array $a, string $k) {
  $t = 0.0;
  foreach ($a as $r) $t += (float)($r[$k] ?? 0);
  return $t;
};

$totCobrado = $sum($diarias, 'cob_antes') + $sum($apto, 'cob_antes') + $sum($uti, 'cob_antes')
  + $sum($cc, 'cob_antes') + $sum($exames, 'cob_antes') + $sum($materiais, 'cob_antes')
  + $sum($hon, 'cob_antes') + $sum($outros, 'cob_antes');

$totGlosa   = $sum($diarias, 'glosa') + $sum($apto, 'glosa') + $sum($uti, 'glosa')
  + $sum($cc, 'glosa') + $sum($exames, 'glosa') + $sum($materiais, 'glosa')
  + $sum($hon, 'glosa') + $sum($outros, 'glosa');

$totApos    = $sum($diarias, 'apos') + $sum($apto, 'apos') + $sum($uti, 'apos')
  + $sum($cc, 'apos') + $sum($exames, 'apos') + $sum($materiais, 'apos')
  + $sum($hon, 'apos') + $sum($outros, 'apos');

$desconto           = (float)($dados['desconto_valor_cap'] ?? 0.0);
$valorApresentado   = (float)($dados['valor_apresentado_capeante'] ?? 0.0);
$valorFinalCapeante = (float)($dados['valor_final_capeante'] ?? 0.0);
$valorFinal = ($valorFinalCapeante > 0)
  ? $valorFinalCapeante
  : max(0, $totApos - $desconto);

$pdf->SetFont('helvetica', '', $BODY_SIZE_PT);
$totHtml = '
<style>
  .tot td{border:' . $BORDER_CELL . ' solid ' . $BORDER_CLR2 . '; padding:' . ($ULTRA ? '1.5px' : ($COMPACT ? '2px' : '3px')) . '; }
  .tot .th{background-color:' . $SHADE_BG . '; font-weight:bold;}
</style>
<table class="tot" cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr class="th">
    <td width="25%">Cobrado</td>
    <td width="25%">Glosado</td>
    <td width="25%">Após Auditoria</td>
    <td width="25%">Desconto</td>
  </tr>
  <tr>
    <td>' . brl($totCobrado) . '</td>
    <td>' . brl($totGlosa) . '</td>
    <td>' . brl($totApos) . '</td>
    <td>' . brl($desconto) . '</td>
  </tr>
  <tr style="font-weight:bold;">
    <td colspan="3" align="right">Apresentado:</td>
    <td>' . brl($valorApresentado) . '</td>
  </tr>
  <tr class="th">
    <td colspan="3" align="right">VALOR TOTAL:</td>
    <td>' . brl($valorFinal) . '</td>
  </tr>
</table>';
$pdf->writeHTML($totHtml, true, false, false, false, '');

/* ---------- CAMPOS FINAIS ---------- */
$comentarioBase = $dados['comentario_auditoria'] ?? '';
$comentario = $comentariosObs !== '' ? $comentariosObs : $comentarioBase;
$cid        = $dados['cid_cap'] ?? ($dados['cid_principal'] ?? '');
$proced     = $dados['proced_principal'] ?? '';
$auditor    = $dados['nome_auditor'] ?? ($dados['fk_id_aud_med'] ?? '');

//* ======================= BLOCO FINAL – COLAR COMO SUBSTITUTO ======================= */

$pad      = $ULTRA ? 3 : ($COMPACT ? 4 : 6);
$minH     = $ULTRA ? 14 : ($COMPACT ? 22 : 34);
$cellPad  = $ULTRA ? 1  : 1.5;
$lh       = 1.15;
$gapTop   = $ULTRA ? 0.5 : ($COMPACT ? 1.0 : 2.0);
$gapMid   = $ULTRA ? 0.3 : ($COMPACT ? 0.8 : 1.0);


// Garante que possamos olhar também o POST (quando vem direto do form)
$dados = array_merge(
  (is_array($_POST ?? null)  ? $_POST  : []),
  (is_array($dados ?? null)  ? $dados  : [])
);

// --- Nomes vindos do formulário (sem DAO) ---
$audMed = trim($_REQUEST['aud_med_nome'] ?? $dados['aud_med_nome'] ?? '');
$audEnf = trim($_REQUEST['aud_enf_nome'] ?? $dados['aud_enf_nome'] ?? '');
$adm    = trim($_REQUEST['aud_adm_nome'] ?? $dados['aud_adm_nome'] ?? '');

// Normaliza texto seguro para HTML
$audMedTxt = safe($audMed ?: '—');
$audEnfTxt = safe($audEnf ?: '—');
$admTxt    = safe($adm   ?: '—');


$htmlBlocoFinal = '
<table nobr="true" cellpadding="0" cellspacing="0" width="100%" style="page-break-inside: avoid;">
  <tr>
    <td style="font-weight:bold;">Comentário:</td>
  </tr>
  <tr>
    <td style="border:' . $BORDER_CELL . ' solid ' . $BORDER_CLR2 . ';
               padding:' . $pad . 'px;
               min-height:' . $minH . 'px;">' . safe($comentario ?: "—") . '</td>
  </tr>
</table>

<table nobr="true" cellpadding="' . $cellPad . '" cellspacing="0" border="0" width="100%"
       style="line-height:' . $lh . '; page-break-inside: avoid; margin-top:8px; font-size:9pt;">
  <tr>
    <!-- Auditor Médico -->
    <td width="34%" style="vertical-align:bottom;">
      <table cellpadding="0" cellspacing="0" border="0" width="95%">
        <tr><td style="font-weight:600; padding-bottom:4px;">Auditor Médico:</td></tr>
        <tr>
          <td style="border-top:0.5px solid #999; text-align:center; padding-top:3px;">' . $audMedTxt . '</td>
        </tr>
      </table>
    </td>

    <!-- Auditor Enf(a) -->
    <td width="33%" style="vertical-align:bottom;">
      <table cellpadding="0" cellspacing="0" border="0" width="95%">
        <tr><td style="font-weight:600; padding-bottom:4px;">Auditor Enf(a):</td></tr>
        <tr>
          <td style="border-top:0.5px solid #999; text-align:center; padding-top:3px;">' . $audEnfTxt . '</td>
        </tr>
      </table>
    </td>

    <!-- Administrativo(a) -->
    <td width="33%" style="vertical-align:bottom;">
      <table cellpadding="0" cellspacing="0" border="0" width="95%">
        <tr><td style="font-weight:600; padding-bottom:4px;">Administrativo(a):</td></tr>
        <tr>
          <td style="border-top:0.5px solid #999; text-align:center; padding-top:3px;">' . $admTxt . '</td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td colspan="3" style="padding-top:8px;">São Paulo, ' . date('d/m/Y') . '</td>
  </tr>
</table>
';


// escreve o bloco como transação: se quebrar página, desfaz e reescreve após AddPage()
$pdf->Ln($gapTop);
$__startPage = $pdf->getPage();
$pdf->startTransaction();
$pdf->writeHTML($htmlBlocoFinal, true, false, true, false, '');
$__endPage = $pdf->getPage();

if ($__endPage !== $__startPage) {
  $pdf->rollbackTransaction(true);
  $pdf->AddPage();
  $pdf->Ln($gapTop);
  $pdf->writeHTML($htmlBlocoFinal, true, false, true, false, '');
} else {
  $pdf->commitTransaction();
}

// pequeno espaço antes do carimbo final
$pdf->Ln($gapMid);

// linha discreta e carimbo "Gerado por..."
$__margins    = $pdf->getMargins();
$__left       = $__margins['left'];
$__right      = $__margins['right'];
$__usableW    = $pdf->getPageWidth() - $__left - $__right;

$__y = $pdf->GetY() + 3;
$pdf->SetLineWidth(0.6);
$pdf->SetDrawColor(30, 30, 30);
$pdf->Line($__left, $__y, $__left + $__usableW, $__y);
$pdf->Ln(2.5);

$pdf->SetFont('helvetica', '', $BODY_SIZE_PT);
$pdf->Cell(0, 6, 'Gerado por FullCareConex • ' . date('d/m/Y H:i'), 0, 1, 'R');

/* ===================== FIM DO BLOCO FINAL – NADA MAIS ALTERADO ===================== */

/* ---------- SAÍDA/ARQUIVO ---------- */
$fname      = 'RAH_Capeante_' . (int)$idCapeante . '.pdf';
$exportsDir = __DIR__ . '/exports';
if (!is_dir($exportsDir)) @mkdir($exportsDir, 0775, true);
$abs = $exportsDir . '/' . $fname;

/* Salva cópia no disco (F) */
$exportsOk = false;
try {
  while (ob_get_level() > 0) @ob_end_clean();
  $pdf->Output($abs, 'F');
  $exportsOk = is_file($abs) && filesize($abs) > 0;
} catch (Throwable $e) {
  @file_put_contents($LOG_FILE, "[EXPORT WARN] Falha ao gravar PDF: {$e->getMessage()}\n", FILE_APPEND);
}

/* save_only -> JSON */
if ($saveOnly) {
  $resp = ['ok' => false, 'id_capeante' => (int)$idCapeante];
  if ($exportsOk) {
    $resp['ok'] = true;
    $resp['file_path'] = $abs;
    $resp['file_url'] = base_url_guess() . '/exports/' . rawurlencode($fname);
  } else {
    $resp['error'] = 'Não foi possível salvar o PDF em /exports.';
  }
  while (ob_get_level() > 0) @ob_end_clean();
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($resp);
  exit;
}

/* Stream (I ou D) */
while (ob_get_level() > 0) @ob_end_clean();
$pdf->Output($fname, $download ? 'D' : 'I');
exit;
