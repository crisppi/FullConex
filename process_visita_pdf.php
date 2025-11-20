<?php
ob_start();

require_once("globals.php");
require_once("db.php");
require_once("dao/visitaDao.php");
require_once("dao/internacaoDao.php");
require_once('vendor/autoload.php');

/**
 * Formata datas do banco (YYYY-MM-DD) para DD/MM/YYYY.
 */
function formatDate($date)
{
    if (!$date || $date === '0000-00-00') {
        return '';
    }
    $dt = \DateTime::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format('d/m/Y') : $date;
}

/**
 * Converte indicadores 's'/'n' em 'Sim'/'Não'.
 */
function formatBool($value)
{
    $value = strtolower(trim((string) $value));
    if ($value === 's') return 'Sim';
    if ($value === 'n') return 'Não';
    return '';
}

/**
 * TCPDF com rodapé padrão.
 */
class RelatorioVisitaPDF extends \TCPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 7);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 0, 'R');
    }
}

/**
 * Cabeçalho com logo + título.
 */
function renderHeader($pdf, $logoPath)
{
    if (file_exists($logoPath)) {
        $logoWidth = 28;
        $logoY     = 10;
        $pdf->Image($logoPath, 15, $logoY, $logoWidth);
        $yAfterLogo = $pdf->getImageRBY();
        $linhaY = $yAfterLogo + 1;
        $pdf->SetLineWidth(0.1);
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Line(15, $linhaY, 195, $linhaY);
        $pdf->SetY($linhaY + 1.5);
    } else {
        $pdf->SetY(22);
    }

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 5, 'RELATÓRIO DE VISITA', 0, 1, 'C');
    $pdf->Ln(1);
}

$idVisita = filter_input(INPUT_GET, "id_visita", FILTER_VALIDATE_INT);
$idInternacaoOverride = filter_input(INPUT_GET, "id_internacao", FILTER_VALIDATE_INT);

if (!$idVisita) {
    die("ID da visita inválido.");
}

$visitaDao     = new visitaDao($conn, $BASE_URL);
$internacaoDao = new internacaoDao($conn, $BASE_URL);

$visitaRows = $visitaDao->joinVisitaInternacaoShow($idVisita);
if (empty($visitaRows)) {
    die("Visita não encontrada.");
}

$visita = $visitaRows[0];
$idInternacao = $idInternacaoOverride ?: ($visita['id_internacao'] ?? null);
if (!$idInternacao) {
    die("Internação relacionada não encontrada.");
}

$internacoes = $internacaoDao->selectAllInternacao('id_internacao = ' . (int) $idInternacao);
$internacao = $internacoes[0] ?? $visita;

$pdf = new RelatorioVisitaPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle("Relatório de Visita - #{$idVisita}");
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 18);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->AddPage();

$logoPath = 'img/LogoConexAud.png';
renderHeader($pdf, $logoPath);

$corAzulHeader = [0, 86, 143];
$corCinza      = [236, 239, 241];

// ===================== INFORMAÇÕES DA INTERNAÇÃO =====================
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 5, 'INFORMAÇÕES DA INTERNAÇÃO', 0, 1, 'L');
$pdf->SetDrawColor(200, 200, 200);
$yLinhaInfo = $pdf->GetY();
$pdf->Line(15, $yLinhaInfo, 195, $yLinhaInfo);
$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 7);
$pdf->SetFillColor(...$corCinza);
$pdf->Cell(50, 6, 'Nome do Paciente:', 1, 0, 'L', true);
$pdf->Cell(0, 6, $internacao['nome_pac'] ?? '', 1, 1, 'L');
$pdf->Ln(1);

$dadosInternacao = [
    'ID da Internação'    => $internacao['id_internacao'] ?? '',
    'Data da Internação'  => formatDate($internacao['data_intern_int'] ?? ''),
    'Hospital'            => $internacao['nome_hosp'] ?? '',
    'Especialidade'       => $internacao['especialidade_int'] ?? '',
    'Origem'              => $internacao['origem_int'] ?? '',
    'Modo de Internação'  => $internacao['modo_internacao_int'] ?? '',
    'Tipo de Admissão'    => $internacao['tipo_admissao_int'] ?? '',
    'Acomodação'          => $internacao['acomodacao_int'] ?? '',
    'Grupo de Patologia'  => $internacao['grupo_patologia_int'] ?? '',
    'Patologia'           => $internacao['patologia2_pat'] ?? '',
    'UTI'                 => formatBool($internacao['internado_uti_int'] ?? ''),
    'Senha'               => $internacao['senha_int'] ?? '',
];

$itensInt = [];
foreach ($dadosInternacao as $campo => $valor) {
    $itensInt[] = ['label' => $campo, 'valor' => $valor];
}

$colsPerRow  = 3;
$colWidth    = 60;
$totalItens  = count($itensInt);
$totalRows   = (int) ceil($totalItens / $colsPerRow);

$pdf->SetFillColor(...$corCinza);
$pdf->SetDrawColor(180, 180, 180);
$startX = $pdf->GetX();
for ($row = 0; $row < $totalRows; $row++) {
    $currentY = $pdf->GetY();
    for ($col = 0; $col < $colsPerRow; $col++) {
        $idx  = $row * $colsPerRow + $col;
        $html = '';

        if (isset($itensInt[$idx])) {
            $label = htmlspecialchars($itensInt[$idx]['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $valor = htmlspecialchars((string)$itensInt[$idx]['valor'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html  = '<b>' . $label . ':</b> ' . $valor;
        }

        $x = $startX + $col * $colWidth;
        $pdf->writeHTMLCell(
            $colWidth,
            6,
            $x,
            $currentY,
            $html,
            1,
            0,
            1,
            false,
            'L',
            true
        );
    }
    $pdf->SetY($currentY + 6);
    $pdf->SetX($startX);
}
$pdf->Ln(4);

// ===================== DETALHES DA VISITA =====================
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Cell(0, 5, 'DETALHES DA VISITA #' . ($visita['visita_no_vis'] ?? $idVisita), 0, 1, 'L');
$pdf->SetDrawColor(200, 200, 200);
$yLinhaVis = $pdf->GetY();
$pdf->Line(15, $yLinhaVis, 195, $yLinhaVis);
$pdf->Ln(2);

$dadosVisita = [
    'Id Visita'      => $visita['id_visita'] ?? '',
    'Data da Visita' => formatDate($visita['data_visita_vis'] ?? ''),
    'Hospital'       => $visita['nome_hosp'] ?? '',
    'Titular'        => $visita['titular_int'] ?? '',
    'Acomodação'     => $visita['acomodacao_int'] ?? '',
];

$itensVis = [];
foreach ($dadosVisita as $campo => $valor) {
    $itensVis[] = ['label' => $campo, 'valor' => $valor];
}

$colsPerRowV = 3;
$colWidthV   = 60;
$totalItensV = count($itensVis);
$totalRowsV  = (int) ceil($totalItensV / $colsPerRowV);

$pdf->SetFillColor(...$corCinza);
$pdf->SetDrawColor(180, 180, 180);
$startXv = $pdf->GetX();
for ($row = 0; $row < $totalRowsV; $row++) {
    $currentYv = $pdf->GetY();
    for ($col = 0; $col < $colsPerRowV; $col++) {
        $idxV  = $row * $colsPerRowV + $col;
        $htmlV = '';

        if (isset($itensVis[$idxV])) {
            $labelV = htmlspecialchars($itensVis[$idxV]['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $valorV = htmlspecialchars((string)$itensVis[$idxV]['valor'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $htmlV  = '<b>' . $labelV . ':</b> ' . $valorV;
        }

        $xv = $startXv + $col * $colWidthV;
        $pdf->writeHTMLCell(
            $colWidthV,
            6,
            $xv,
            $currentYv,
            $htmlV,
            1,
            0,
            1,
            false,
            'L',
            true
        );
    }
    $pdf->SetY($currentYv + 6);
    $pdf->SetX($startXv);
}
$pdf->Ln(3);

// Relatório
$pdf->SetFillColor(...$corCinza);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->MultiCell(0, 6, 'Relatório da Visita:', 1, 'L', true);
$pdf->SetFont('helvetica', '', 7);
$pdf->MultiCell(0, 6, $visita['rel_visita_vis'] ?? '', 1, 'L');
$pdf->Ln(1);

// Ações
$pdf->SetFillColor(...$corCinza);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->MultiCell(0, 6, 'Ações da Visita:', 1, 'L', true);
$pdf->SetFont('helvetica', '', 7);
$pdf->MultiCell(0, 6, $visita['acoes_int_vis'] ?? '', 1, 'L');
$pdf->Ln(1);

// Profissional
$profissionalNome     = trim((string)($visita['auditor_nome'] ?? ''));
$profissionalRegistro = trim((string)($visita['auditor_registro'] ?? ''));
$profissionalValor    = trim($profissionalNome . ($profissionalRegistro ? ' - ' . $profissionalRegistro : ''));

$pdf->SetFillColor(...$corCinza);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->MultiCell(0, 6, 'Profissional:', 1, 'L', true);
$pdf->SetFont('helvetica', '', 7);
$pdf->MultiCell(0, 6, $profissionalValor, 1, 'L');
$pdf->Ln(3);

ob_end_clean();
$nomeArquivo = sprintf("relatorio_visita_%d.pdf", $idVisita);
$pdf->Output($nomeArquivo, 'D');
exit();
