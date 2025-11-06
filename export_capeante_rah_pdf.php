<?php
/**
 * export_capeante_pdf.php
 * Gera PDF no formato RAH (Relatório de Auditoria Hospitalar) a partir do capeante.
 * Uso:
 *   export_capeante_pdf.php?id_capeante=6
 *   export_capeante_pdf.php?id_capeante=6&debug=1   (mostra texto em vez de PDF)
 */

declare(strict_types=1);

// ===================== CONFIG / DEBUG =====================
$DEBUG = isset($_GET['debug']) && $_GET['debug'] == '1';
@ini_set('display_errors', $DEBUG ? '1' : '0');
error_reporting(E_ALL);

// Pasta para logs (opcional)
$LOG_DIR  = __DIR__ . '/logs';
$LOG_FILE = $LOG_DIR . '/export_capeante_pdf.error.log';
if (!is_dir($LOG_DIR)) { @mkdir($LOG_DIR, 0775, true); }

// Handlers de erro/exception
set_error_handler(function ($severity, $message, $file, $line) use ($LOG_FILE, $DEBUG) {
    $txt = "[PHP ERROR] [$severity] $message in $file:$line\n";
    @file_put_contents($LOG_FILE, $txt, FILE_APPEND);
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $txt;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
set_exception_handler(function ($ex) use ($LOG_FILE, $DEBUG) {
    $txt = "[UNCAUGHT] " . get_class($ex) . ": " . $ex->getMessage() . " in " .
        $ex->getFile() . ":" . $ex->getLine() . "\n" . $ex->getTraceAsString() . "\n";
    @file_put_contents($LOG_FILE, $txt, FILE_APPEND);
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $txt;
    }
    http_response_code(500);
    exit;
});

// Garantir que não há saída antes do PDF
while (ob_get_level() > 0) { @ob_end_clean(); }
ob_start();

// ===================== VALIDA PARAM =====================
$idCapeante = isset($_GET['id_capeante']) ? (int)$_GET['id_capeante'] : 0;
if ($idCapeante <= 0) {
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "Parâmetro id_capeante inválido.";
    }
    http_response_code(400);
    exit;
}

// ===================== CONEXÃO PDO =====================
// Se você já usa um config silencioso que define $conn, carregue-o:
if (!isset($conn) || !($conn instanceof PDO)) {
    $tried = false;
    foreach ([__DIR__ . '/config.php', dirname(__DIR__) . '/config.php'] as $cfg) {
        if (is_file($cfg)) {
            $tried = true;
            require_once $cfg; // deve setar $conn (PDO) sem imprimir nada
            if (isset($conn) && $conn instanceof PDO) break;
        }
    }
    if (!isset($conn) || !($conn instanceof PDO)) {
        // Fallback por variáveis de ambiente / defaults locais
        $DB_HOST = getenv('DB_HOST') ?: 'localhost';
        $DB_NAME = getenv('DB_NAME') ?: 'fullconex'; // ajuste p/ seu nome real
        $DB_USER = getenv('DB_USER') ?: 'root';
        $DB_PASS = getenv('DB_PASS') ?: 'mysql';     // AMPPS default
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $conn = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}

// ===================== HELPERS =====================
function brl(float $v): string { return 'R$ ' . number_format($v, 2, ',', '.'); }
function dt(?string $d): string {
    if (!$d) return '';
    $t = strtotime($d);
    return $t ? date('d/m/Y', $t) : '';
}
function safe(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// ===================== BUSCA DADOS PRINCIPAIS =====================
// Ajuste os alias para bater com o seu schema real.
$sql = "
SELECT
    c.*,

    i.id_internacao,
    i.data_intern_int,
    i.hora_intern_int,
    i.senha_int,
    i.num_atendimento_int,

    p.id_paciente,
    p.nome_pac,
    p.cpf_pac,
    p.data_nasc_pac,

    h.id_hospital,
    h.nome_hosp,
    h.cnpj_hosp,

    pr.prorrog1_ini_pror AS prorroga_inicio,
    pr.prorrog1_fim_pror AS prorroga_fim
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
WHERE c.id_capeante = :id
LIMIT 1
";
$st = $conn->prepare($sql);
$st->execute([':id' => $idCapeante]);
$dados = $st->fetch();

if (!$dados) {
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "Capeante não encontrado (id_capeante={$idCapeante}).";
    }
    http_response_code(404);
    exit;
}

// ===================== MAPEAMENTO CAMPOS CABEÇALHO =====================
$hospitalNome   = $dados['nome_hosp'] ?? '';
$hospitalCNPJ   = $dados['cnpj_hosp'] ?? '';
$pacienteNome   = $dados['nome_pac'] ?? '';
$pacienteCPF    = $dados['cpf_pac'] ?? '';
$senhaAut       = $dados['senha_int'] ?? '';
$atendimento    = $dados['num_atendimento_int'] ?? '';
$dataInternacao = $dados['data_intern_int'] ?? '';
$horaInternacao = $dados['hora_intern_int'] ?? '';
$prorrogacaoTxt = (!empty($dados['prorroga_inicio']) || !empty($dados['prorroga_fim']))
    ? (dt($dados['prorroga_inicio']) . ' a ' . dt($dados['prorroga_fim']))
    : '';

$idade = '';
if (!empty($dados['data_nasc_pac'])) {
    $nasc = new DateTime((string)$dados['data_nasc_pac']);
    $idade = $nasc->diff(new DateTime('today'))->y . ' anos';
}

// Período do capeante (ou da internação)
$periodoIni = $dados['data_inicial_capeante'] ?? $dataInternacao;
$periodoFim = $dados['data_final_capeante']   ?? null;

// ===================== CARREGAR TCPDF =====================
$tcpdfLoaded = false;
$tcpdfPaths = [
    __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php', // composer direto
    __DIR__ . '/vendor/autoload.php',               // composer autoload
    __DIR__ . '/tcpdf_min/tcpdf.php',               // pacote manual
    __DIR__ . '/lib/tcpdf/tcpdf.php',               // custom
];
foreach ($tcpdfPaths as $p) {
    if (is_file($p)) {
        require_once $p;
        $tcpdfLoaded = class_exists('TCPDF', false) || class_exists('TCPDF');
        if ($tcpdfLoaded) break;
    }
}
if (!$tcpdfLoaded) {
    throw new RuntimeException('TCPDF não encontrado. Instale via Composer (tecnickcom/tcpdf) ou coloque tcpdf_min/ ao lado deste arquivo.');
}

// ===================== QUADROS (RAH) =====================
// Se ainda não tem os valores por item, usamos seus totais atuais do capeante.
// Depois, ao normalizar (tb_cap_valores_*), troque a origem aqui.
$diarias = [
  ['desc'=>'QUARTO / APTO', 'qtd'=>(int)($dados['qtd_apto'] ?? 0), 'cob_antes'=>(float)($dados['valor_apto'] ?? 0), 'glosa'=>(float)($dados['glosa_apto'] ?? 0)],
  ['desc'=>'DAY CLINIC',    'qtd'=>(int)($dados['qtd_day'] ?? 0),  'cob_antes'=>(float)($dados['valor_day'] ?? 0),  'glosa'=>(float)($dados['glosa_day'] ?? 0)],
  ['desc'=>'UTI',           'qtd'=>(int)($dados['qtd_uti'] ?? 0),  'cob_antes'=>(float)($dados['valor_uti'] ?? 0),  'glosa'=>(float)($dados['glosa_uti'] ?? 0)],
  ['desc'=>'UTI / SEMI',    'qtd'=>(int)($dados['qtd_semi'] ?? 0), 'cob_antes'=>(float)($dados['valor_semi'] ?? 0), 'glosa'=>(float)($dados['glosa_semi'] ?? 0)],
  ['desc'=>'ENFERMARIA',    'qtd'=>(int)($dados['qtd_enf'] ?? 0),  'cob_antes'=>(float)($dados['valor_enf'] ?? 0),  'glosa'=>(float)($dados['glosa_enf'] ?? 0)],
  ['desc'=>'BERÇÁRIO',      'qtd'=>(int)($dados['qtd_berc'] ?? 0), 'cob_antes'=>(float)($dados['valor_berc'] ?? 0), 'glosa'=>(float)($dados['glosa_berc'] ?? 0)],
  ['desc'=>'ACOMPANHANTE',  'qtd'=>(int)($dados['qtd_acomp'] ?? 0),'cob_antes'=>(float)($dados['valor_acomp'] ?? 0),'glosa'=>(float)($dados['glosa_acomp'] ?? 0)],
  ['desc'=>'ISOLAMENTO',    'qtd'=>(int)($dados['qtd_isol'] ?? 0), 'cob_antes'=>(float)($dados['valor_isol'] ?? 0), 'glosa'=>(float)($dados['glosa_isol'] ?? 0)],
];

$enf = [
  ['desc'=>'TERAPIAS',            'cob_antes'=>(float)($dados['valor_terap_enf'] ?? 0), 'glosa'=>(float)($dados['glosa_terap_enf'] ?? 0)],
  ['desc'=>'TAXAS / ALUGUEIS',    'cob_antes'=>(float)($dados['valor_taxa'] ?? 0),      'glosa'=>(float)($dados['glosa_taxas'] ?? 0)],
  ['desc'=>'MATERIAL DE CONSUMO', 'cob_antes'=>(float)($dados['valor_matmed'] ?? 0),    'glosa'=>(float)($dados['glosa_matmed'] ?? 0)],
  ['desc'=>'MEDICAMENTOS',        'cob_antes'=>(float)($dados['valor_medicamentos'] ?? 0),'glosa'=>(float)($dados['glosa_medicamentos'] ?? 0)],
  ['desc'=>'GASES MEDICINAIS',    'cob_antes'=>(float)($dados['valor_oxig'] ?? 0),      'glosa'=>(float)($dados['glosa_oxig'] ?? 0)],
  ['desc'=>'MATERIAL ESPECIAL',   'cob_antes'=>(float)($dados['valor_materiais'] ?? 0), 'glosa'=>(float)($dados['glosa_materiais'] ?? 0)],
  ['desc'=>'EXAMES',              'cob_antes'=>(float)($dados['valor_sadt'] ?? 0),      'glosa'=>(float)($dados['glosa_sadt'] ?? 0)],
  ['desc'=>'HEMODERIVADOS',       'cob_antes'=>(float)($dados['valor_hemo'] ?? 0),      'glosa'=>(float)($dados['glosa_hemo'] ?? 0)],
  ['desc'=>'HONORÁRIOS',          'cob_antes'=>(float)($dados['valor_honorarios'] ?? 0),'glosa'=>(float)($dados['glosa_honorarios'] ?? 0)],
];

$uti = [
  ['desc'=>'TERAPIAS',            'cob_antes'=>(float)($dados['valor_terap_uti'] ?? 0), 'glosa'=>(float)($dados['glosa_terap_uti'] ?? 0)],
  ['desc'=>'TAXAS / ALUGUEIS',    'cob_antes'=>(float)($dados['valor_taxa_uti'] ?? 0),  'glosa'=>(float)($dados['glosa_taxa_uti'] ?? 0)],
  ['desc'=>'MATERIAL DE CONSUMO', 'cob_antes'=>(float)($dados['valor_mat_uti'] ?? 0),   'glosa'=>(float)($dados['glosa_mat_uti'] ?? 0)],
  ['desc'=>'MEDICAMENTOS',        'cob_antes'=>(float)($dados['valor_meds_uti'] ?? 0),  'glosa'=>(float)($dados['glosa_meds_uti'] ?? 0)],
  ['desc'=>'GASES MEDICINAIS',    'cob_antes'=>(float)($dados['valor_gases_uti'] ?? 0), 'glosa'=>(float)($dados['glosa_gases_uti'] ?? 0)],
  ['desc'=>'MATERIAL ESPECIAL',   'cob_antes'=>(float)($dados['valor_me_uti'] ?? 0),    'glosa'=>(float)($dados['glosa_me_uti'] ?? 0)],
  ['desc'=>'EXAMES',              'cob_antes'=>(float)($dados['valor_exames_uti'] ?? 0),'glosa'=>(float)($dados['glosa_exames_uti'] ?? 0)],
  ['desc'=>'HEMODERIVADOS',       'cob_antes'=>(float)($dados['valor_hemo_uti'] ?? 0),  'glosa'=>(float)($dados['glosa_hemo_uti'] ?? 0)],
  ['desc'=>'HONORÁRIOS',          'cob_antes'=>(float)($dados['valor_hon_uti'] ?? 0),   'glosa'=>(float)($dados['glosa_hon_uti'] ?? 0)],
];

$cc = [
  ['desc'=>'TERAPIAS',            'cob_antes'=>(float)($dados['valor_terap_cc'] ?? 0), 'glosa'=>(float)($dados['glosa_terap_cc'] ?? 0)],
  ['desc'=>'TAXAS / ALUGUEIS',    'cob_antes'=>(float)($dados['valor_taxa_cc'] ?? 0),  'glosa'=>(float)($dados['glosa_taxa_cc'] ?? 0)],
  ['desc'=>'MATERIAL DE CONSUMO', 'cob_antes'=>(float)($dados['valor_mat_cc'] ?? 0),   'glosa'=>(float)($dados['glosa_mat_cc'] ?? 0)],
  ['desc'=>'MEDICAMENTOS',        'cob_antes'=>(float)($dados['valor_meds_cc'] ?? 0),  'glosa'=>(float)($dados['glosa_meds_cc'] ?? 0)],
  ['desc'=>'GASES MEDICINAIS',    'cob_antes'=>(float)($dados['valor_gases_cc'] ?? 0), 'glosa'=>(float)($dados['glosa_gases_cc'] ?? 0)],
  ['desc'=>'MATERIAL ESPECIAL',   'cob_antes'=>(float)($dados['valor_me_cc'] ?? 0),    'glosa'=>(float)($dados['glosa_me_cc'] ?? 0)],
  ['desc'=>'EXAMES',              'cob_antes'=>(float)($dados['valor_exames_cc'] ?? 0),'glosa'=>(float)($dados['glosa_exames_cc'] ?? 0)],
  ['desc'=>'HEMODERIVADOS',       'cob_antes'=>(float)($dados['valor_hemo_cc'] ?? 0),  'glosa'=>(float)($dados['glosa_hemo_cc'] ?? 0)],
  ['desc'=>'HONORÁRIOS',          'cob_antes'=>(float)($dados['valor_hon_cc'] ?? 0),   'glosa'=>(float)($dados['glosa_hon_cc'] ?? 0)],
];

$outros = [
  ['desc'=>'PACOTE',  'cob_antes'=>(float)($dados['valor_pacote'] ?? 0), 'glosa'=>(float)($dados['glosa_pacote'] ?? 0)],
  ['desc'=>'REMOÇÃO', 'cob_antes'=>(float)($dados['valor_remocao'] ?? 0),'glosa'=>(float)($dados['glosa_remocao'] ?? 0)],
];

// Completa campos default
$fix = function(array $arr): array {
  foreach ($arr as &$r) {
    $r['qtd']  = (int)($r['qtd']  ?? 0);
    $r['obs']  = (string)($r['obs'] ?? '');
    $c         = (float)($r['cob_antes'] ?? 0);
    $g         = (float)($r['glosa']     ?? 0);
    $r['apos'] = (float)($r['apos'] ?? max(0, $c - $g));
  }
  return $arr;
};
$diarias = $fix($diarias);
$enf     = $fix($enf);
$uti     = $fix($uti);
$cc      = $fix($cc);
$outros  = $fix($outros);

// Totais globais (seu consolidado do capeante prevalece se existir)
$sum = function(array $arr, string $k){ $t=0.0; foreach($arr as $r){ $t += (float)($r[$k] ?? 0); } return $t; };
$totCobrado = $sum($diarias,'cob_antes') + $sum($enf,'cob_antes') + $sum($uti,'cob_antes') + $sum($cc,'cob_antes') + $sum($outros,'cob_antes');
$totGlosa   = $sum($diarias,'glosa')     + $sum($enf,'glosa')     + $sum($uti,'glosa')     + $sum($cc,'glosa')     + $sum($outros,'glosa');
$totApos    = $sum($diarias,'apos')      + $sum($enf,'apos')      + $sum($uti,'apos')      + $sum($cc,'apos')      + $sum($outros,'apos');

// Consolidados já salvos no capeante (se existirem)
$desconto           = (float)($dados['desconto_valor_cap'] ?? 0.0);
$valorApresentado   = (float)($dados['valor_apresentado_capeante'] ?? 0.0);
$valorFinalCapeante = (float)($dados['valor_final_capeante'] ?? 0.0);
if ($valorFinalCapeante > 0) { $totApos = $valorFinalCapeante; }
$valorFinal = max(0, $totApos - $desconto);

// Campos finais: comentário / CID / procedimento / auditor
$comentario = $dados['comentario_auditoria'] ?? '';
$cid        = $dados['cid_cap'] ?? ($dados['cid_principal'] ?? '');
$proced     = $dados['proced_principal'] ?? '';
$auditor    = $dados['nome_auditor'] ?? ($dados['fk_id_aud_med'] ?? '');

// ===================== MODO DEBUG (texto) =====================
if ($DEBUG) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "OK (debug)\n";
    echo "Paciente: {$pacienteNome}\nHospital: {$hospitalNome}\n";
    echo "Totais -> Cobrado: " . brl($totCobrado) . " | Glosado: " . brl($totGlosa) . " | Após: " . brl($totApos) . " | Desconto: " . brl($desconto) . " | Valor Final: " . brl($valorFinal) . "\n";
    ob_end_clean(); // não gera PDF em debug
    exit;
}

// ===================== CLASSE PDF (cabeçalho/rodapé) =====================
class PDFCapeanteRAH extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica','B',12);
        $this->Cell(0, 8, 'Relatório de Auditoria Hospitalar - RAH', 0, 1, 'C');
        $this->Ln(2);
    }
    public function Footer() {
        $this->SetY(-12);
        $this->SetFont('helvetica','',8);
        $this->Cell(0, 10, 'Gerado por FullCare • ' . date('d/m/Y H:i'), 0, 0, 'R');
    }
}

// ===================== INSTÂNCIA PDF =====================
$pdf = new PDFCapeanteRAH('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle('Relatório de Auditoria Hospitalar (RAH)');
$pdf->SetMargins(10, 18, 10);
$pdf->SetAutoPageBreak(true, 16);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

// ===================== CABEÇALHO (IDENTIFICAÇÃO) =====================
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
'</table>
<hr/>';
$pdf->writeHTML($headHtml, true, false, false, false, '');

// ===================== HELPER TABELA RAH =====================
function renderQuadro(TCPDF $pdf, string $titulo, array $linhas): void {
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell(0,7,$titulo,0,1,'L');
    $pdf->SetFont('helvetica','',9);

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
        $rows .= '<tr>'.
          '<td>'.safe($ln['desc']).'</td>'.
          '<td align="center">'.(int)($ln['qtd'] ?? 0).'</td>'.
          '<td align="right">'.brl((float)($ln['cob_antes'] ?? 0)).'</td>'.
          '<td align="right">'.brl((float)($ln['glosa'] ?? 0)).'</td>'.
          '<td align="right">'.brl((float)($ln['apos'] ?? max(0, ($ln['cob_antes'] ?? 0)-($ln['glosa'] ?? 0)))).'</td>'.
          '<td>'.safe($ln['obs'] ?? '').'</td>'.
        '</tr>';
    }
    $tfoot = '</table><br/>';
    $pdf->writeHTML($thead.$rows.$tfoot, true, false, true, false, '');
}

// ===================== QUADROS =====================
$pdf->Ln(2);
renderQuadro($pdf, 'DIÁRIAS', $diarias);
renderQuadro($pdf, 'DESPESAS QUARTO / ENFERMARIA', $enf);
renderQuadro($pdf, 'DESPESAS NA UTI', $uti);
renderQuadro($pdf, 'DESPESAS CENTRO CIRÚRGICO', $cc);
renderQuadro($pdf, 'OUTROS', $outros);

// ===================== TOTAIS =====================
$pdf->Ln(1);
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

// ===================== COMENTÁRIO + CID / PROCED + ASSINATURA =====================
$pdf->Ln(2);
$pdf->writeHTML('<b>Comentário:</b><br/><div style="border:1px solid #000;padding:6px;min-height:38px;">'.safe($comentario ?: '—').'</div>', true, false, false, false, '');
$pdf->Ln(1);
$pdf->writeHTML('
<table cellpadding="3" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="50%"><b>CID:</b> '.safe($cid).'</td>
    <td width="50%"><b>Procedimento:</b> '.safe($proced).'</td>
  </tr>
</table>', true, false, false, false, '');

$pdf->Ln(3);
$pdf->writeHTML('
<table cellpadding="3" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="60%"><b>Auditor(a):</b> '.safe($auditor).' &nbsp;&nbsp; <b>Data:</b> '.date('d/m/Y').'</td>
    <td width="40%" style="text-align:right;"><b>'.safe($hospitalNome).'</b> &nbsp;&nbsp; CNPJ: '.safe($hospitalCNPJ).'</td>
  </tr>
</table>', true, false, false, false, '');

// ===================== SAÍDA =====================
@ob_end_clean(); // limpa qualquer resíduo
$pdf->Output('RAH_Capeante_' . $idCapeante . '.pdf', 'I');
// Para download direto: $pdf->Output('RAH_Capeante_' . $idCapeante . '.pdf', 'D');
// Para salvar no servidor: $pdf->Output(__DIR__.'/storage/pdf/RAH_'.$idCapeante.'.pdf', 'F');