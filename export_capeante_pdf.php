<?php

/**
 * export_capeante_pdf.php
 * Gera PDF (RAH) a partir do capeante.
 * Uso: export_capeante_pdf.php?id_capeante=6
 * Dica: export_capeante_pdf.php?id_capeante=6&debug=1  (mostra erros em texto)
 */

declare(strict_types=1);

// ===================== CONFIG BÁSICA =====================
$DEBUG = isset($_GET['debug']) && $_GET['debug'] == '1';
@ini_set('display_errors', $DEBUG ? '1' : '0');
error_reporting(E_ALL);

// Pasta de logs (ajuste se quiser)
$LOG_DIR  = __DIR__ . '/logs';
$LOG_FILE = $LOG_DIR . '/export_capeante_pdf.error.log';
if (!is_dir($LOG_DIR)) {
    @mkdir($LOG_DIR, 0775, true);
}

// Handler para registrar QUALQUER erro/exception
set_error_handler(function ($severity, $message, $file, $line) use ($LOG_FILE, $DEBUG) {
    $txt = "[PHP ERROR] [$severity] $message in $file:$line\n";
    @file_put_contents($LOG_FILE, $txt, FILE_APPEND);
    if ($DEBUG) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo $txt;
    }
    // converte em ErrorException para cair no catch, interrompendo o fluxo
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
    exit; // sem imprimir HTML para não sujar o PDF
});

// Evita qualquer lixo anterior
while (ob_get_level() > 0) {
    @ob_end_clean();
}
ob_start(); // só para capturar eventual echo acidental; limpamos antes do PDF

// ===================== VALIDAR PARÂMETRO =====================
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
/**
 * Se você já tem um config que NÃO imprime nada, pode usar:
 * require_once __DIR__ . '/config.php'; // deve definir $conn (PDO)
 * Aqui deixo um fallback local.
 */
// ===================== CONEXÃO PDO =====================
// Tenta usar um config.php que NÃO imprime nada e já expõe $conn (PDO)
if (!isset($conn) || !($conn instanceof PDO)) {
    $configTried = false;
    $configPaths = [
        __DIR__ . '/config.php',            // raiz do FullConex
        dirname(__DIR__) . '/config.php',   // caso esteja em subpasta
    ];
    foreach ($configPaths as $cfg) {
        if (is_file($cfg)) {
            $configTried = true;
            require_once $cfg; // deve definir $conn OU pelo menos DB creds
            if (isset($conn) && $conn instanceof PDO) {
                break;
            }
        }
    }

    if (!isset($conn) || !($conn instanceof PDO)) {
        // Usa variáveis de ambiente se existir (recomendado)
        $DB_HOST = getenv('DB_HOST') ?: 'localhost';
        $DB_NAME = getenv('DB_NAME') ?: 'fullconex'; // <<< ajuste aqui para o NOME CORRETO
        $DB_USER = getenv('DB_USER') ?: 'root';
        $DB_PASS = getenv('DB_PASS') ?: 'mysql';     // AMPPS padrão

        // Se quiser garantir que não está usando o nome errado:
        // dica: troque 'fullconex' pelo seu banco local real (ex.: 'FullConex', 'fullconexdb', etc.)
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $conn = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}


// ===================== FUNÇÕES AUX =====================
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
function safe(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ===================== BUSCA DADOS =====================
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

// ===================== MAPA DE CAMPOS =====================
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

// Caso tenha período no capeante:
$periodoIni = $dados['data_inicial_capeante'] ?? $dataInternacao;
$periodoFim = $dados['data_final_capeante']   ?? null;

// Valores (ajuste nomes conforme seu schema)
$desconto           = (float)($dados['desconto_valor_cap'] ?? 0.0);
$valorApresentado   = (float)($dados['valor_apresentado_capeante'] ?? 0.0);
$valorFinalCapeante = (float)($dados['valor_final_capeante'] ?? 0.0);

// Grupos resumidos (se tiver itens detalhados, troque por SUMs)
$linhas = [
    [
        'grupo' => 'DIÁRIAS',
        'rotulo' => 'Diárias (Apto/UTI/Semi)',
        'qtd' => (int)($dados['diarias_capeante'] ?? 0),
        'cobrado' => (float)($dados['valor_diarias'] ?? 0),
        'glosado' => (float)($dados['glosa_diaria'] ?? 0)
    ],
    [
        'grupo' => 'ENF/OUTROS',
        'rotulo' => 'Taxas / Aluguéis',
        'cobrado' => (float)($dados['valor_taxa'] ?? 0),
        'glosado' => (float)($dados['glosa_taxas'] ?? 0)
    ],
    [
        'grupo' => 'ENF/OUTROS',
        'rotulo' => 'Material de Consumo',
        'cobrado' => (float)($dados['valor_matmed'] ?? 0),
        'glosado' => (float)($dados['glosa_matmed'] ?? 0)
    ],
    [
        'grupo' => 'ENF/OUTROS',
        'rotulo' => 'Materiais',
        'cobrado' => (float)($dados['valor_materiais'] ?? 0),
        'glosado' => (float)($dados['glosa_materiais'] ?? 0)
    ],
    [
        'grupo' => 'ENF/OUTROS',
        'rotulo' => 'Medicamentos',
        'cobrado' => (float)($dados['valor_medicamentos'] ?? 0),
        'glosado' => (float)($dados['glosa_medicamentos'] ?? 0)
    ],
    [
        'grupo' => 'ENF/OUTROS',
        'rotulo' => 'Gases Medicinais',
        'cobrado' => (float)($dados['valor_oxig'] ?? 0),
        'glosado' => (float)($dados['glosa_oxig'] ?? 0)
    ],
    [
        'grupo' => 'SADT/CC',
        'rotulo' => 'Exames / SADT',
        'cobrado' => (float)($dados['valor_sadt'] ?? 0),
        'glosado' => (float)($dados['glosa_sadt'] ?? 0)
    ],
    [
        'grupo' => 'SADT/CC',
        'rotulo' => 'Honorários',
        'cobrado' => (float)($dados['valor_honorarios'] ?? 0),
        'glosado' => (float)($dados['glosa_honorarios'] ?? 0)
    ],
    [
        'grupo' => 'SADT/CC',
        'rotulo' => 'OPME / Especiais',
        'cobrado' => (float)($dados['valor_opme'] ?? 0),
        'glosado' => (float)($dados['glosa_opme'] ?? 0)
    ],
];

// Calcula “após”
$totCobrado = 0.0;
$totGlosa = 0.0;
$totApos = 0.0;
foreach ($linhas as &$L) {
    $c = (float)($L['cobrado'] ?? 0);
    $g = (float)($L['glosado'] ?? 0);
    $L['apos'] = max(0, $c - $g);
    $totCobrado += $c;
    $totGlosa   += $g;
    $totApos    += $L['apos'];
}
unset($L);
if ($valorFinalCapeante > 0) {
    $totApos = $valorFinalCapeante;
}
$valorFinal = max(0, $totApos - $desconto);

// ===================== SE MODO DEBUG, MOSTRA TEXTO E SAI =====================
if ($DEBUG) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "OK (debug)\n";
    echo "Paciente: {$pacienteNome}\nHospital: {$hospitalNome}\n";
    echo "Totais -> Cobrado: " . brl($totCobrado) . " | Glosado: " . brl($totGlosa) . " | Após: " . brl($totApos) . " | Desconto: " . brl($desconto) . " | Final: " . brl($valorFinal) . "\n";
    ob_end_clean(); // nada de PDF em debug
    exit;
}

// ===================== TCPDF =====================
require_once __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';

class PDFCapeanteRAH extends TCPDF
{
    public function Header() {}
    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 10, 'Gerado por FullCare • ' . date('d/m/Y H:i'), 0, 0, 'R');
    }
}

$pdf = new PDFCapeanteRAH('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle('Relatório de Auditoria Hospitalar');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

// Título
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'Relatório de Auditoria Hospitalar (RAH)', 0, 1, 'L');
$pdf->Ln(1);
$pdf->SetFont('helvetica', '', 9);

// Cabeçalho
$headHtml = '
<table cellpadding="3" cellspacing="0" border="0" width="100%" style="line-height:1.3;">
  <tr>
    <td width="50%"><b>Referenciado:</b> ' . safe($hospitalNome) . '</td>
    <td width="25%"><b>Senha:</b> ' . safe($senhaAut) . '</td>
    <td width="25%"><b>Internação:</b> ' . dt($dataInternacao) . '</td>
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
    (!empty($prorrogacaoTxt) ? '<tr><td colspan="3"><b>Prorrogação vigente:</b> ' . $prorrogacaoTxt . '</td></tr>' : '')
    . '</table>';
$pdf->writeHTML($headHtml, true, false, false, false, '');

// Tabela
$tbl = '
<style>
  .rah th { background-color:#e9ecef; font-weight:bold; }
  .rah td, .rah th { border:1px solid #cfcfcf; }
</style>
<table class="rah" cellpadding="3" cellspacing="0" border="0" width="100%">
  <tr>
    <th style="width:44%;">Grupo / Procedimento</th>
    <th style="width:14%;">Cobrado</th>
    <th style="width:14%;">Glosado</th>
    <th style="width:14%;">Após Auditoria</th>
    <th style="width:14%;">Obs.</th>
  </tr>';
$grupoAtual = '';
foreach ($linhas as $l) {
    if (!empty($l['grupo']) && $l['grupo'] !== $grupoAtual) {
        $grupoAtual = $l['grupo'];
        $tbl .= '<tr style="background-color:#f1f1f1;"><td colspan="5" style="padding:6px 8px;"><b>' . safe($grupoAtual) . '</b></td></tr>';
    }
    $tbl .= '<tr>
        <td>' . safe($l['rotulo']) . '</td>
        <td style="text-align:right">' . brl((float)$l['cobrado']) . '</td>
        <td style="text-align:right">' . brl((float)$l['glosado']) . '</td>
        <td style="text-align:right">' . brl((float)$l['apos']) . '</td>
        <td></td>
    </tr>';
}
$tbl .= '</table>';
$pdf->Ln(2);
$pdf->writeHTML($tbl, true, false, true, false, '');

// Totais
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
    <td width="34%" style="text-align:right;"><b>Valor Final:</b> ' . brl($valorFinal) . '</td>
  </tr>
</table>';
$pdf->Ln(1);
$pdf->writeHTML($totHtml, true, false, false, false, '');

// Comentário
$comentario = $dados['comentario_auditoria'] ?? '';
$pdf->Ln(2);
$pdf->MultiCell(0, 6, "Comentário: " . ($comentario !== '' ? $comentario : '—'), 0, 'L', false, 1);

// Assinatura
$pdf->Ln(4);
$assinatura = '
<table cellpadding="3" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="60%"><b>Auditor(a):</b> ' . safe($dados['fk_id_aud_med'] ?? '') . ' &nbsp;&nbsp; <b>Data:</b> ' . date('d/m/Y') . '</td>
    <td width="40%" style="text-align:right;"><b>' . safe($hospitalNome) . '</b> &nbsp;&nbsp; CNPJ: ' . safe($hospitalCNPJ) . '</td>
  </tr>
</table>';
$pdf->writeHTML($assinatura, true, false, false, false, '');

// Limpa QUALQUER saída e envia PDF
@ob_end_clean();
$pdf->Output('RAH_Capeante_' . $idCapeante . '.pdf', 'I');