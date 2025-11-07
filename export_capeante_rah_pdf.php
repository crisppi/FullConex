<?php

/**
 * export_capeante_rah_pdf.php — versão robusta c/ health/selftest
 *
 * Modos:
 *  GET ?id_capeante=6                 -> abre inline (I)
 *  GET ?id_capeante=6&download=1      -> força download (D)
 *  GET ?id_capeante=6&save_only=1     -> salva /exports e retorna JSON
 *  GET ?debug=1                       -> modo debug (texto/erros)
 *  GET ?selftest=1                    -> gera PDF simples sem banco (testa TCPDF)
 *  GET ?health=1                      -> diagnóstico (TCPDF/DB/permissões)
 *  POST (qualquer) ou prefer_post=1   -> usa dados recém-postados dos grupos
 */

declare(strict_types=1);
@date_default_timezone_set('America/Sao_Paulo');

/* ---------- HARDEN ---------- */
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '0');
@ini_set('output_buffering', '0');

/* ---------- DEBUG ---------- */
$DEBUG    = isset($_GET['debug']) && $_GET['debug'] === '1';
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

/* ---------- LIMPA QUALQUER SAÍDA ---------- */
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
function web_url_for_file(string $abs): string
{
  // tenta montar URL relativa ao DOCUMENT_ROOT
  $doc = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
  $absN = str_replace('\\', '/', $abs);
  if ($doc && str_starts_with($absN, $doc . '/')) {
    $rel = substr($absN, strlen($doc));
    return (base_url_guess() . $rel);
  }
  // fallback: assume /exports sob a mesma pasta do script
  return base_url_guess() . '/exports/' . rawurlencode(basename($abs));
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

/* ---------- SELFTEST ---------- */
if ($SELFTEST) {
  require_tcpdf_or_throw();
  $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
  $pdf->setPrintHeader(false);
  $pdf->setPrintFooter(false);
  $pdf->AddPage();
  $pdf->SetFont('helvetica', 'B', 14);
  $pdf->Cell(0, 10, 'Selftest OK - TCPDF carregado e saída funcionando', 0, 1, 'C');
  // garante cabeçalhos limpos
  while (ob_get_level() > 0) @ob_end_clean();
  $pdf->Output('selftest.pdf', 'I');
  exit;
}

/* ---------- DB CONN ---------- */
if (!isset($conn) || !($conn instanceof PDO)) {
  foreach ([__DIR__ . '/globals.php', __DIR__ . '/db.php', __DIR__ . '/config.php'] as $cfg) if (is_file($cfg)) require_once $cfg;
}
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

/* ---------- CAPA ---------- */
$sql = "
SELECT
  c.*,
  i.id_internacao, i.data_intern_int, i.data_alta_int, i.hora_intern_int, i.senha_int, i.num_atendimento_int,
  p.id_paciente, p.nome_pac, p.cpf_pac, p.data_nasc_pac,
  h.id_hospital, h.nome_hosp, h.cnpj_hosp,
  pr.prorrog1_ini_pror AS prorroga_inicio, pr.prorrog1_fim_pror AS prorroga_fim
FROM tb_capeante c
LEFT JOIN tb_internacao  i  ON i.id_internacao   = c.fk_int_capeante
LEFT JOIN tb_paciente    p  ON p.id_paciente     = i.fk_paciente_int
LEFT JOIN tb_hospital    h  ON h.id_hospital     = i.fk_hospital_int
LEFT JOIN (
  SELECT x.*
  FROM tb_prorrogacao x
  WHERE x.id_prorrogacao = (
    SELECT x2.id_prorrogacao FROM tb_prorrogacao x2
    WHERE x2.fk_internacao_pror = x.fk_internacao_pror
    ORDER BY COALESCE(x2.prorrog1_fim_pror, x2.prorrog1_ini_pror) DESC, x2.id_prorrogacao DESC
    LIMIT 1
  )
) pr ON pr.fk_internacao_pror = i.id_internacao
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

/* ---------- MERGE POST BÁSICO ---------- */
if ($preferPost && !empty($_POST)) {
  foreach ($_POST as $k => $v) $dados[$k] = $v;
  foreach ($dados as $k => $v) {
    if (preg_match('/_(cobrado|glosado|liberado)$/', $k) || in_array($k, ['valor_apresentado_capeante', 'valor_final_capeante'], true)) $dados[$k] = brl_to_float($v);
    if (preg_match('/_qtd$/', $k)) $dados[$k] = (int)$v;
  }
}

/* ---------- CAMPOS CAPA ---------- */
$hospitalNome   = $dados['nome_hosp'] ?? '';
$hospitalCNPJ   = $dados['cnpj_hosp'] ?? '';
$pacienteNome   = $dados['nome_pac'] ?? '';
$pacienteCPF    = $dados['cpf_pac'] ?? '';
$senhaAut       = $dados['senha_int'] ?? '';
$atendimento    = $dados['num_atendimento_int'] ?? '';
$dataInternacao = $dados['data_intern_int'] ?? '';
$dataAlta       = $dados['data_alta_int'] ?? '';
$horaInternacao = $dados['hora_intern_int'] ?? '';
$idade = '';
if (!empty($dados['data_nasc_pac'])) {
  $n = new DateTime((string)$dados['data_nasc_pac']);
  $idade = $n->diff(new DateTime('today'))->y . ' anos';
}
$prorrogacaoTxt = (!empty($dados['prorroga_inicio']) || !empty($dados['prorroga_fim'])) ? (dt($dados['prorroga_inicio']) . ' a ' . dt($dados['prorroga_fim'])) : '';
$periodoIni = $dados['data_inicial_capeante'] ?? $dataInternacao;
$periodoFim = $dados['data_final_capeante']   ?? $dataAlta;

/* ---------- GRUPOS ---------- */
function rows_from_post(?array $arr): array
{
  if (!is_array($arr)) return [];
  $out = [];
  foreach ($arr as $ln) {
    if (!is_array($ln)) continue;
    $desc = (string)($ln['desc'] ?? '');
    $qtd = (int)($ln['qtd'] ?? 0);
    $cob = brl_to_float($ln['valor_cobrado'] ?? $ln['cobrado'] ?? $ln['cob'] ?? 0);
    $glo = brl_to_float($ln['valor_glosado']  ?? $ln['glosado'] ?? $ln['glo'] ?? 0);
    $lib = array_key_exists('valor_liberado', $ln) ? brl_to_float($ln['valor_liberado']) : max(0, $cob - $glo);
    $obs = (string)($ln['obs'] ?? '');
    if ($desc === '' && !$qtd && !$cob && !$glo && $obs === '') continue;
    $out[] = ['desc' => $desc, 'qtd' => $qtd, 'cob_antes' => $cob, 'glosa' => $glo, 'apos' => $lib, 'obs' => $obs];
  }
  return $out;
}
function rows_from_table(PDO $conn, string $table, int $idCapeante): array
{
  $sql = "SELECT desc_item, qtd, valor_cobrado, valor_glosado, valor_liberado, obs FROM {$table} WHERE fk_capeante=:c ORDER BY 1 ASC";
  $q = $conn->prepare($sql);
  $q->execute([':c' => $idCapeante]);
  $rows = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
  $out = [];
  foreach ($rows as $r) {
    $desc = (string)($r['desc_item'] ?? '');
    $qtd = (int)($r['qtd'] ?? 0);
    $cob = (float)($r['valor_cobrado'] ?? 0);
    $glo = (float)($r['valor_glosado'] ?? 0);
    $lib = isset($r['valor_liberado']) ? (float)$r['valor_liberado'] : max(0, $cob - $glo);
    $obs = (string)($r['obs'] ?? '');
    if ($desc === '' && !$qtd && !$cob && !$glo && $obs === '') continue;
    $out[] = ['desc' => $desc, 'qtd' => $qtd, 'cob_antes' => $cob, 'glosa' => $glo, 'apos' => $lib, 'obs' => $obs];
  }
  return $out;
}
$usePost = $preferPost || ($_SERVER['REQUEST_METHOD'] === 'POST');
$diarias   = $usePost ? rows_from_post($_POST['diarias']   ?? null) : rows_from_table($conn, 'tb_cap_valores_diar',      $idCapeante);
$uti       = $usePost ? rows_from_post($_POST['uti']       ?? null) : rows_from_table($conn, 'tb_cap_valores_uti',       $idCapeante);
$cc        = $usePost ? rows_from_post($_POST['cc']        ?? null) : rows_from_table($conn, 'tb_cap_valores_cc',        $idCapeante);
$exames    = $usePost ? rows_from_post($_POST['exames']    ?? null) : rows_from_table($conn, 'tb_cap_valores_exames',    $idCapeante);
$materiais = $usePost ? rows_from_post($_POST['materiais'] ?? null) : rows_from_table($conn, 'tb_cap_valores_materiais', $idCapeante);
$hon       = $usePost ? rows_from_post($_POST['hon']       ?? null) : rows_from_table($conn, 'tb_cap_valores_hon',       $idCapeante);
$outros    = $usePost ? rows_from_post($_POST['outros']    ?? null) : rows_from_table($conn, 'tb_cap_valores_out',       $idCapeante);

/* ---------- DEBUG CURTO ---------- */
if ($DEBUG) {
  header('Content-Type: text/plain; charset=UTF-8');
  echo "Fonte grupos: " . ($usePost ? 'POST' : 'BANCO') . "\n";
  echo "id_capeante: $idCapeante | Hosp: {$hospitalNome} | Paciente: {$pacienteNome}\n";
  echo "Linhas: diarias=" . count($diarias) . ", uti=" . count($uti) . ", cc=" . count($cc) . ", exames=" . count($exames) . ", materiais=" . count($materiais) . ", hon=" . count($hon) . ", outros=" . count($outros) . "\n";
  exit;
}

/* ---------- TCPDF LOAD ---------- */
require_tcpdf_or_throw();

class PDFCapeanteRAH extends TCPDF
{
  public function Header()
  {
    $this->SetFont('helvetica', 'B', 12);
    $this->Cell(0, 8, 'Relatório de Auditoria Hospitalar - RAH', 0, 1, 'C');
    $this->Ln(2);
  }
  public function Footer()
  {
    $this->SetY(-12);
    $this->SetFont('helvetica', '', 8);
    $this->Cell(0, 10, 'Gerado por FullCare • ' . date('d/m/Y H:i'), 0, 0, 'R');
  }
}

/* ---------- INICIA PDF ---------- */
$pdf = new PDFCapeanteRAH('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle('RAH - Capeante ' . $idCapeante);
$pdf->SetMargins(10, 18, 10);
$pdf->SetAutoPageBreak(true, 16);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

/* ---------- CABECALHO HTML ---------- */
$headHtml = '
<table cellpadding="3" cellspacing="0" border="0" width="100%" style="line-height:1.3;">
  <tr>
    <td width="50%"><b>Referenciado:</b> ' . safe($hospitalNome) . '</td>
    <td width="25%"><b>Senha:</b> ' . safe($senhaAut) . '</td>
    <td width="25%"><b>Data de Internação:</b> ' . dt($dataInternacao) . '</td>
  </tr>
  <tr>
    <td><b>CNPJ:</b> ' . safe($hospitalCNPJ) . '</td>
    <td><b>Atendimento:</b> ' . safe($atendimento) . '</td>
    <td><b>Hora:</b> ' . safe($horaInternacao) . '</td>
  </tr>
  <tr>
    <td><b>Paciente:</b> ' . safe($pacienteNome) . '</td>
    <td><b>Idade:</b> ' . safe($idade) . '</td>
    <td><b>CPF:</b> ' . safe($pacienteCPF) . '</td>
  </tr>
  <tr>
    <td colspan="3"><b>Período de Cobrança:</b> ' . dt($periodoIni) . ' a ' . dt($periodoFim) . '</td>
  </tr>' .
  (!empty($prorrogacaoTxt) ? '<tr><td colspan="3"><b>Prorrogação vigente:</b> ' . $prorrogacaoTxt . '</td></tr>' : '') .
  '</table><hr/>';
$pdf->writeHTML($headHtml, true, false, false, false, '');

/* ---------- QUADRO RENDER ---------- */
function renderQuadro(TCPDF $pdf, string $titulo, array $linhas): void
{
  if (empty($linhas)) return;
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(0, 7, $titulo, 0, 1, 'L');
  $pdf->SetFont('helvetica', '', 9);
  $thead = '
  <table border="1" cellpadding="3" cellspacing="0" width="100%">
    <tr style="font-weight:bold;">
      <td width="36%">Descrição</td>
      <td width="8%"  align="center">Qtd</td>
      <td width="18%" align="right">Cobrado Antes</td>
      <td width="18%" align="right">Glosado</td>
      <td width="18%" align="right">Cobrado Após</td>
      <td width="12%">Observação</td>
    </tr>';
  $rows = '';
  foreach ($linhas as $ln) {
    $rows .= '<tr>' .
      '<td>' . safe($ln['desc']) . '</td>' .
      '<td align="center">' . (int)($ln['qtd'] ?? 0) . '</td>' .
      '<td align="right">' . brl((float)($ln['cob_antes'] ?? 0)) . '</td>' .
      '<td align="right">' . brl((float)($ln['glosa'] ?? 0)) . '</td>' .
      '<td align="right">' . brl((float)($ln['apos'] ?? max(0, ($ln['cob_antes'] ?? 0) - ($ln['glosa'] ?? 0)))) . '</td>' .
      '<td>' . safe($ln['obs'] ?? '') . '</td>' .
      '</tr>';
  }
  $pdf->writeHTML($thead . $rows . '</table><br/>', true, false, true, false, '');
}

/* ---------- RENDERIZA GRUPOS ---------- */
renderQuadro($pdf, 'DIÁRIAS', $diarias);
renderQuadro($pdf, 'UTI', $uti);
renderQuadro($pdf, 'CENTRO CIRÚRGICO', $cc);
renderQuadro($pdf, 'EXAMES', $exames);
renderQuadro($pdf, 'MATERIAIS / OPME', $materiais);
renderQuadro($pdf, 'HONORÁRIOS', $hon);
renderQuadro($pdf, 'OUTROS', $outros);

/* ---------- TOTAIS ---------- */
$sum = function (array $a, string $k) {
  $t = 0.0;
  foreach ($a as $r) {
    $t += (float)($r[$k] ?? 0);
  }
  return $t;
};
$totCobrado = $sum($diarias, 'cob_antes') + $sum($uti, 'cob_antes') + $sum($cc, 'cob_antes') + $sum($exames, 'cob_antes') + $sum($materiais, 'cob_antes') + $sum($hon, 'cob_antes') + $sum($outros, 'cob_antes');
$totGlosa  = $sum($diarias, 'glosa')    + $sum($uti, 'glosa')    + $sum($cc, 'glosa')    + $sum($exames, 'glosa')    + $sum($materiais, 'glosa')    + $sum($hon, 'glosa')    + $sum($outros, 'glosa');
$totApos   = $sum($diarias, 'apos')     + $sum($uti, 'apos')     + $sum($cc, 'apos')     + $sum($exames, 'apos')     + $sum($materiais, 'apos')     + $sum($hon, 'apos')     + $sum($outros, 'apos');

$desconto           = (float)($dados['desconto_valor_cap'] ?? 0.0);
$valorApresentado   = (float)($dados['valor_apresentado_capeante'] ?? 0.0);
$valorFinalCapeante = (float)($dados['valor_final_capeante'] ?? 0.0);
if ($valorFinalCapeante > 0) $totApos = $valorFinalCapeante;
$valorFinal = max(0, $totApos - $desconto);

$totHtml = '
<table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="33%"><b>Cobrado:</b> ' . brl($totCobrado) . '</td>
    <td width="33%"><b>Glosado:</b> ' . brl($totGlosa) . '</td>
    <td width="34%" style="text-align:right;"><b>Após Auditoria:</b> ' . brl($totApos) . '</td>
  </tr>
  <tr>
    <td width="33%"><b>Desconto:</b> ' . brl($desconto) . '</td>
    <td width="33%"><b>Apresentado:</b> ' . brl($valorApresentado) . '</td>
    <td width="34%" style="text-align:right;"><b>Valor Total:</b> ' . brl($valorFinal) . '</td>
  </tr>
</table>';
$pdf->writeHTML($totHtml, true, false, false, false, '');

/* ---------- COMPLEMENTOS ---------- */
$comentario = $dados['comentario_auditoria'] ?? '';
$cid = $dados['cid_cap'] ?? ($dados['cid_principal'] ?? '');
$proced = $dados['proced_principal'] ?? '';
$auditor = $dados['nome_auditor'] ?? ($dados['fk_id_aud_med'] ?? '');

$pdf->Ln(2);
$pdf->writeHTML('<b>Comentário:</b><br/><div style="border:1px solid #000;padding:6px;min-height:38px;">' . safe($comentario ?: '—') . '</div>', true, false, false, false, '');
$pdf->Ln(1);
$pdf->writeHTML('<table cellpadding="3" cellspacing="0" border="0" width="100%"><tr><td width="50%"><b>CID:</b> ' . safe($cid) . '</td><td width="50%"><b>Procedimento:</b> ' . safe($proced) . '</td></tr></table>', true, false, false, false, '');
$pdf->Ln(3);
$pdf->writeHTML('<table cellpadding="3" cellspacing="0" border="0" width="100%"><tr><td width="60%"><b>Auditor(a):</b> ' . safe($auditor) . ' &nbsp;&nbsp; <b>Data:</b> ' . date('d/m/Y') . '</td><td width="40%" style="text-align:right;"><b>' . safe($hospitalNome) . '</b> &nbsp;&nbsp; CNPJ: ' . safe($hospitalCNPJ) . '</td></tr></table>', true, false, false, false, '');

/* ---------- SAÍDA (única) ---------- */
$fname      = 'RAH_Capeante_' . (int)$idCapeante . '.pdf';
$exportsDir = __DIR__ . '/exports';
if (!is_dir($exportsDir)) {
  @mkdir($exportsDir, 0775, true);
}

$abs = $exportsDir . '/' . $fname;

/* Salva uma cópia no disco via TCPDF (modo F) */
try {
  // limpa buffers antes de qualquer output
  while (ob_get_level() > 0) {
    @ob_end_clean();
  }
  $pdf->Output($abs, 'F'); // grava o arquivo em /exports
  $exportsOk = is_file($abs) && filesize($abs) > 0;
} catch (Throwable $e) {
  $exportsOk = false;
  @file_put_contents($LOG_FILE, "[EXPORT WARN] Falha ao gravar PDF: {$e->getMessage()}\n", FILE_APPEND);
}

/* Modo save_only: responde JSON com a URL do arquivo salvo (se salvou) */
if ($saveOnly) {
  $resp = ['ok' => false, 'id_capeante' => (int)$idCapeante];
  if ($exportsOk) {
    $resp['ok']        = true;
    $resp['file_path'] = $abs;
    $resp['file_url']  = base_url_guess() . '/exports/' . rawurlencode($fname);
  } else {
    $resp['error'] = 'Não foi possível salvar o PDF em /exports.';
  }
  while (ob_get_level() > 0) {
    @ob_end_clean();
  }
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($resp);
  exit;
}

/* Stream: deixa o TCPDF setar os headers corretos */
while (ob_get_level() > 0) {
  @ob_end_clean();
}
$pdf->Output($fname, $download ? 'D' : 'I'); // D = download forçado, I = inline
exit;

/* Stream inline ou download forçado */
while (ob_get_level() > 0) {
  @ob_end_clean();
}
$disposition = $download ? 'attachment' : 'inline';

/* Cabeçalhos seguros para PDF */
header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disposition . '; filename="' . $fname . '"');
header('Content-Length: ' . strlen($pdfBinary));
header('Cache-Control: private, must-revalidate, max-age=0');
header('Pragma: public');

/* Envia o binário e encerra */
echo $pdfBinary;
exit;