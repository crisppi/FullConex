<?php

/**
 * export_capeante_rah_pdf.php — RAH completo no layout do PDF enviado
 * Grupos (tabelas) EXCLUSIVAMENTE do FORM (POST) — sem SELECT para linhas.
 *
 * Modos:
 *  GET ?id_capeante=6                 -> abre inline (I)
 *  GET ?id_capeante=6&download=1      -> força download (D)
 *  GET ?id_capeante=6&save_only=1     -> salva em /exports e responde JSON
 *  GET ?debug=1                       -> debug curto (mostra contagens)
 *  GET ?selftest=1                    -> teste rápido do TCPDF
 *  GET ?health=1                      -> diagnóstico TCPDF/DB/exports
 *  POST (qualquer) ou prefer_post=1   -> usa dados recém-enviados para os grupos
 */

declare(strict_types=1);
@date_default_timezone_set('America/Sao_Paulo');

/* ---------- HARDEN ---------- */
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '0');
@ini_set('output_buffering', '0');

/* ---------- FLAGS ---------- */
$DEBUG    = isset($_GET['debug'])    && $_GET['debug']    === '1';
$SELFTEST = isset($_GET['selftest']) && $_GET['selftest'] === '1';
$HEALTH   = isset($_GET['health'])   && $_GET['health']   === '1';

@ini_set('display_errors', $DEBUG ? '1' : '0');
error_reporting(E_ALL);

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

/* ---------- LIMPA QUALQUER BUFFER ---------- */
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
  $s = str_replace('.', '', $s);
  $s = str_replace(',', '.', $s);
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

/* ---------- HEALTH ---------- */
if ($HEALTH) {
  $resp = ['ok' => true, 'tcpdf' => false, 'db' => false, 'exports' => false, 'exports_path' => __DIR__ . '/exports'];
  try {
    require_tcpdf_or_throw();
    $resp['tcpdf'] = true;
  } catch (Throwable $e) {
    $resp['tcpdf_err'] = $e->getMessage();
  }
  try {
    if (!isset($conn) || !($conn instanceof PDO)) {
      foreach ([__DIR__ . '/globals.php', __DIR__ . '/db.php', __DIR__ . '/config.php'] as $cfg) if (is_file($cfg)) require_once $cfg;
    }
    if (!isset($conn) || !($conn instanceof PDO)) {
      $dsn  = "mysql:host=" . (getenv('DB_HOST') ?: 'localhost') . ";dbname=" . (getenv('DB_NAME') ?: 'fullconex') . ";charset=utf8mb4";
      $user = getenv('DB_USER') ?: 'root';
      $pass = getenv('DB_PASS') ?: 'mysql';
      $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      ]);
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

/* ---------- SELFTEST ---------- */
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

/* ---------- DB CONN (para CABEÇALHO apenas) ---------- */
if (!isset($conn) || !($conn instanceof PDO)) {
  foreach ([__DIR__ . '/globals.php', __DIR__ . '/db.php', __DIR__ . '/config.php'] as $cfg) if (is_file($cfg)) require_once $cfg;
}
if (!isset($conn) || !($conn instanceof PDO)) {
  $dsn  = "mysql:host=" . (getenv('DB_HOST') ?: 'localhost') . ";dbname=" . (getenv('DB_NAME') ?: 'fullconex') . ";charset=utf8mb4";
  $user = getenv('DB_USER') ?: 'root';
  $pass = getenv('DB_PASS') ?: 'mysql';
  $conn = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
}
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ---------- VALIDACAO ---------- */
if ($idCapeante <= 0) {
  header('Content-Type: application/json; charset=UTF-8', true, 400);
  echo json_encode(['ok' => false, 'error' => 'Parâmetro id_capeante ausente ou inválido.']);
  exit;
}

/* ---------- CAPA: SELECT PRINCIPAL (alta vem de tb_alta) ---------- */
$sql = "
SELECT
  c.*,
  i.id_internacao, i.data_intern_int, i.hora_intern_int, i.senha_int, i.num_atendimento_int,
  p.id_paciente, p.nome_pac, p.cpf_pac, p.data_nasc_pac,
  h.id_hospital, h.nome_hosp, h.cnpj_hosp,
  al.data_alta_alt AS data_alta_alt,
  pr.prorrog1_ini_pror AS prorroga_inicio, pr.prorrog1_fim_pror AS prorroga_fim
FROM tb_capeante c
LEFT JOIN tb_internacao  i  ON i.id_internacao   = c.fk_int_capeante
LEFT JOIN tb_paciente    p  ON p.id_paciente     = i.fk_paciente_int
LEFT JOIN tb_hospital    h  ON h.id_hospital     = i.fk_hospital_int
LEFT JOIN (
  SELECT x.*
  FROM tb_prorrogacao x
  WHERE x.id_prorrogacao = (
    SELECT x2.id_prorrogacao
    FROM tb_prorrogacao x2
    WHERE x2.fk_internacao_pror = x.fk_internacao_pror
    ORDER BY COALESCE(x2.prorrog1_fim_pror, x2.prorrog1_ini_pror) DESC, x2.id_prorrogacao DESC
    LIMIT 1
  )
) pr ON pr.fk_internacao_pror = i.id_internacao
LEFT JOIN (
  SELECT a.*
  FROM tb_alta a
  WHERE a.id_alta = (
    SELECT a2.id_alta
    FROM tb_alta a2
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

/* ---------- MERGE POST (apenas escalares do cabeçalho) ---------- */
if ($preferPost && !empty($_POST)) {
  foreach ($_POST as $k => $v) {
    if (is_string($k) && !is_array($v) && !is_object($v)) {
      $dados[$k] = $v;
    }
  }
  foreach ($dados as $k => $v) {
    if (!is_string($k) || !is_string($v)) continue;
    if (in_array($k, ['valor_apresentado_capeante', 'valor_final_capeante'], true)) {
      $dados[$k] = brl_to_float($v);
    }
  }
}

/* ---------- CAMPOS DO CABEÇALHO (layout fixo) ---------- */
$hospitalNome   = $dados['nome_hosp'] ?? '';
$hospitalCNPJ   = $dados['cnpj_hosp'] ?? '';
$pacienteNome   = $dados['nome_pac'] ?? '';
$pacienteCPF    = $dados['cpf_pac'] ?? '';
$senhaAut       = $dados['senha_int'] ?? '';
$matricula      = $dados['num_atendimento_int'] ?? '';
$dataInternacao = $dados['data_intern_int'] ?? '';
$horaInternacao = $dados['hora_intern_int'] ?? '';
$dataAlta       = $dados['data_alta_alt'] ?? ''; // somente tb_alta

$idade = '';
if (!empty($dados['data_nasc_pac'])) {
  $n = new DateTime((string)$dados['data_nasc_pac']);
  $idade = $n->diff(new DateTime('today'))->y . ' anos';
}

/* Período de Cobrança: data_inicial_capeante a data_final_capeante (sem misturar com alta) */
$periodoIni = $dados['data_inicial_capeante'] ?: ($dados['data_intern_int'] ?? '');
$periodoFim = $dados['data_final_capeante']   ?: '';

$tipoConta = (!empty($dados['parcial_capeante']) && $dados['parcial_capeante'] === 's')
  ? ('Parcial ' . (string)($dados['parcial_num'] ?? ''))
  : 'Conta Única';

$visaoConta    = $dados['acomodacao_cap'] ?? ($dados['acomodacao_int'] ?? '');
$contaAuditada = (isset($dados['encerrado_cap']) && $dados['encerrado_cap'] === 's') ? 'Sim' : 'Não';

$prorrogacaoTxt = (!empty($dados['prorrog1_ini_pror']) || !empty($dados['prorrog1_fim_pror']))
  ? (dt($dados['prorrog1_ini_pror'] ?? '') . ' a ' . dt($dados['prorrog1_fim_pror'] ?? ''))
  : ((!empty($dados['prorroga_inicio']) || !empty($dados['prorroga_fim'])) ? (dt($dados['prorroga_inicio']) . ' a ' . dt($dados['prorroga_fim'])) : '');

/* ---------- COLETORES DE GRUPOS (FORM APENAS) ---------- */
/* ---------- COLETORES DE GRUPOS (FORM + FALLBACK LEGADO) ---------- */
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

/* 1) Tenta dados estruturados (já suportados) */
$diarias   = rows_from_post($_POST['diarias']   ?? null);
$uti       = rows_from_post($_POST['uti']       ?? null);
$cc        = rows_from_post($_POST['cc']        ?? null);
$exames    = rows_from_post($_POST['exames']    ?? null);
$materiais = rows_from_post($_POST['materiais'] ?? null);
$hon       = rows_from_post($_POST['hon']       ?? null);
$outros    = rows_from_post($_POST['outros']    ?? null);

/* 2) FALLBACK: se algum grupo não veio no formato novo, monta a partir dos campos legados */
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
  $map = [
    'outros_pacote'  => 'Pacote',
    'outros_remocao' => 'Remoção',
  ];
  foreach ($map as $pfx => $label) if ($ln = $legacy_line($pfx, $label)) $outros[] = $ln;
}

/* ---------- DEBUG CURTO ---------- */
if ($DEBUG) {
  header('Content-Type: text/plain; charset=UTF-8');
  echo "Fonte grupos: POST\n";
  echo "id_capeante: $idCapeante | Hosp: {$hospitalNome} | Paciente: {$pacienteNome}\n";
  echo "Linhas: diarias=" . count($diarias) . ", uti=" . count($uti) . ", cc=" . count($cc) . ", exames=" . count($exames) . ", materiais=" . count($materiais) . ", hon=" . count($hon) . ", outros=" . count($outros) . "\n";
  exit;
}

/* ---------- TCPDF LOAD ---------- */
require_tcpdf_or_throw();

/* ---------- PDF CONFIG ---------- */
class PDFCapeanteRAH extends TCPDF {}
$pdf = new PDFCapeanteRAH('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle('RAH - Capeante ' . $idCapeante);
$pdf->SetMargins(10, 14, 10);
$pdf->SetAutoPageBreak(true, 16);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 8);

/* ---------- CABEÇALHO NO LAYOUT DO PDF ENVIADO ---------- */
$cell = function (string $label, $val, string $w, string $align = 'L') {
  $txt = trim((string)$val);
  $v   = $txt === '' ? '&nbsp;' : safe($txt);
  return '<td width="' . $w . '" style="vertical-align:middle;"><b>' . $label . ':</b> <span style="font-weight:normal;">' . $v . '</span></td>';
};

$headHtml = '
<style>
  .hr-thick{border-top:1.2px solid #000;margin:4px 0 6px 0;}
  .small{font-size:9px;}
</style>
<table cellpadding="3" cellspacing="0" border="0" width="100%" style="line-height:1.35;">
  <tr>
    <td colspan="3" style="font-size:12px;font-weight:bold;text-align:center;padding-bottom:6px;">
      RELATÓRIO DE AUDITORIA HOSPITALAR - RAH
    </td>
  </tr>
  <tr>
    ' . $cell('Referenciado', $hospitalNome, '50%') . '
    ' . $cell('Senha',        $senhaAut,      '25%') . '
    ' . $cell('Data de Internação', dt($dataInternacao), '25%') . '
  </tr>
  <tr>
    ' . $cell('CNPJ',        $hospitalCNPJ,  '33%') . '
    ' . $cell('Matrícula',   $matricula,     '33%') . '
    ' . $cell('Hora',        $horaInternacao, '34%') . '
  </tr>
  <tr>
    ' . $cell('Paciente', $pacienteNome, '50%') . '
    ' . $cell('CPF',      $pacienteCPF,  '25%') . '
    ' . $cell('Idade',    $idade,        '25%') . '
  </tr>
  <tr>
    ' . $cell('Período de Cobrança', (dt($periodoIni) . ($periodoFim ? (' a ' . dt($periodoFim)) : '')), '50%') . '
    ' . $cell('Data de Alta', dt($dataAlta), '25%') . '
    ' . $cell('Conta Auditada?', $contaAuditada, '25%') . '
  </tr>
  <tr>
    ' . $cell('Tipo de Conta', $tipoConta,  '50%') . '
    ' . $cell('Visão',         $visaoConta, '25%') . '
    <td width="25%"></td>
  </tr>' .
  ($prorrogacaoTxt ? '<tr><td colspan="3"><b>Prorrogação vigente:</b> <span style="font-weight:normal;">' . $prorrogacaoTxt . '</span></td></tr>' : '') .
  '</table>
<div class="hr-thick"></div>';
$pdf->writeHTML($headHtml, true, false, false, false, '');

/* ---------- QUADRO / TABELAS COM LAYOUT SEMELHANTE AO PDF ENVIADO ---------- */
function renderGroupTable(TCPDF $pdf, string $titulo, array $linhas): void
{
  // filtra realmente vazias
  $linhas = array_values(array_filter($linhas, function ($r) {
    $d = trim((string)($r['desc'] ?? ''));
    return $d !== '' || (int)($r['qtd'] ?? 0) || (float)($r['cob_antes'] ?? 0) || (float)($r['glosa'] ?? 0) || (float)($r['apos'] ?? 0);
  }));
  if (empty($linhas)) return;

  $pdf->SetFont('helvetica', 'B', 9);
  $pdf->Cell(0, 7, $titulo, 0, 1, 'L');
  $pdf->SetFont('helvetica', '', 8);

  $thead = '
  <style>
    .tb { border:1px solid #000; }
    .tb td { border:1px solid #000; }
    .th { font-weight:bold; background-color:#f0f0f0; }
  </style>
  <table class="tb" cellpadding="3" cellspacing="0" width="100%">
    <tr class="th">
      <td width="46%">Descrição</td>
      <td width="8%"  align="center">Qtd</td>
      <td width="15%" align="right">Cobrado</td>
      <td width="15%" align="right">Glosado</td>
      <td width="16%" align="right">Liberado</td>
    </tr>';

  $rows = '';
  foreach ($linhas as $ln) {
    $desc = safe($ln['desc'] ?? '');
    $qtd  = (int)($ln['qtd'] ?? 0);
    $cob  = (float)($ln['cob_antes'] ?? 0);
    $glo  = (float)($ln['glosa'] ?? 0);
    $apos = array_key_exists('apos', $ln) ? (float)$ln['apos'] : max(0, $cob - $glo);
    $rows .= '<tr>'
      . '<td>' . $desc . '</td>'
      . '<td align="center">' . $qtd . '</td>'
      . '<td align="right">' . brl($cob) . '</td>'
      . '<td align="right">' . brl($glo) . '</td>'
      . '<td align="right">' . brl($apos) . '</td>'
      . '</tr>';
  }

  $pdf->writeHTML($thead . $rows . '</table><br/>', true, false, true, false, '');
}

/* ---------- IMPRIME GRUPOS (ordem e títulos) ---------- */
renderGroupTable($pdf, 'DIÁRIAS',                   $diarias);
renderGroupTable($pdf, 'DESPESAS NA UTI',           $uti);
renderGroupTable($pdf, 'DESPESAS NO CENTRO CIRÚRGICO', $cc);
renderGroupTable($pdf, 'EXAMES',                    $exames);
renderGroupTable($pdf, 'MATERIAIS / OPME',          $materiais);
renderGroupTable($pdf, 'HONORÁRIOS',                $hon);
renderGroupTable($pdf, 'OUTROS',                    $outros);

/* ---------- TOTAIS (conforme regra enviada) ---------- */
$sum = function (array $a, string $k) {
  $t = 0.0;
  foreach ($a as $r) {
    $t += (float)($r[$k] ?? 0);
  }
  return $t;
};
$totCobrado = $sum($diarias, 'cob_antes') + $sum($uti, 'cob_antes') + $sum($cc, 'cob_antes') + $sum($exames, 'cob_antes') + $sum($materiais, 'cob_antes') + $sum($hon, 'cob_antes') + $sum($outros, 'cob_antes');
$totGlosa   = $sum($diarias, 'glosa')     + $sum($uti, 'glosa')     + $sum($cc, 'glosa')     + $sum($exames, 'glosa')     + $sum($materiais, 'glosa')     + $sum($hon, 'glosa')     + $sum($outros, 'glosa');
$totApos    = $sum($diarias, 'apos')      + $sum($uti, 'apos')      + $sum($cc, 'apos')      + $sum($exames, 'apos')      + $sum($materiais, 'apos')      + $sum($hon, 'apos')      + $sum($outros, 'apos');

$desconto           = (float)($dados['desconto_valor_cap'] ?? 0.0);
$valorApresentado   = (float)($dados['valor_apresentado_capeante'] ?? 0.0);
$valorFinalCapeante = (float)($dados['valor_final_capeante'] ?? 0.0);
if ($valorFinalCapeante > 0) {
  $totApos = $valorFinalCapeante;
}
$valorFinal = max(0, $totApos - $desconto);

$totHtml = '
<style>.tot td{border:1px solid #000;}</style>
<table class="tot" cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr style="font-weight:bold;background-color:#f0f0f0;">
    <td width="25%">Cobrado</td>
    <td width="25%">Glosado</td>
    <td width="25%">Após Auditoria</td>
    <td width="25%">Desconto</td>
  </tr>
  <tr>
    <td>' . brl($totCobrado) . '</td>
    <td>' . brl($totGlosa)   . '</td>
    <td>' . brl($totApos)    . '</td>
    <td>' . brl($desconto)   . '</td>
  </tr>
  <tr style="font-weight:bold;">
    <td colspan="3" align="right">Apresentado:</td>
    <td>' . brl($valorApresentado) . '</td>
  </tr>
  <tr style="font-weight:bold;background-color:#f0f0f0;">
    <td colspan="3" align="right">VALOR TOTAL:</td>
    <td>' . brl($valorFinal) . '</td>
  </tr>
</table>';
$pdf->writeHTML($totHtml, true, false, false, false, '');

/* ---------- CAMPOS FINAIS (iguais ao trecho do git que você citou) ---------- */
$comentario = $dados['comentario_auditoria'] ?? '';
$cid        = $dados['cid_cap'] ?? ($dados['cid_principal'] ?? '');
$proced     = $dados['proced_principal'] ?? '';
$auditor    = $dados['nome_auditor'] ?? ($dados['fk_id_aud_med'] ?? '');

$pdf->Ln(2);
$pdf->writeHTML('<b>Comentário:</b><br/><div style="border:1px solid #000;padding:6px;min-height:38px;">' . safe($comentario ?: '—') . '</div>', true, false, false, false, '');
$pdf->Ln(1);
$pdf->writeHTML('<table cellpadding="3" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="50%"><b>CID:</b> ' . safe($cid) . '</td>
    <td width="50%"><b>Procedimento:</b> ' . safe($proced) . '</td>
  </tr>
</table>', true, false, false, false, '');
$pdf->Ln(3);
$pdf->writeHTML('<table cellpadding="3" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="60%"><b>Auditor(a):</b> ' . safe($auditor) . ' &nbsp;&nbsp; <b>Data:</b> ' . date('d/m/Y') . '</td>
    <td width="40%" style="text-align:right;"><b>' . safe($hospitalNome) . '</b> &nbsp;&nbsp; CNPJ: ' . safe($hospitalCNPJ) . '</td>
  </tr>
</table>', true, false, false, false, '');

/* ---------- RODAPÉ ---------- */
$pdf->SetY(-12);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 10, 'Gerado por FullCare • ' . date('d/m/Y H:i'), 0, 0, 'R');

/* ---------- SAÍDA/ARQUIVO ---------- */
$fname      = 'RAH_Capeante_' . (int)$idCapeante . '.pdf';
$exportsDir = __DIR__ . '/exports';
if (!is_dir($exportsDir)) {
  @mkdir($exportsDir, 0775, true);
}
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
    $resp['ok']        = true;
    $resp['file_path'] = $abs;
    $resp['file_url']  = base_url_guess() . '/exports/' . rawurlencode($fname);
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