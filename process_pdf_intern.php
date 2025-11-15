<?php
ob_start();

require_once("globals.php");
require_once("db.php");
require_once("dao/visitaDao.php");
require_once("dao/internacaoDao.php");
require_once('vendor/autoload.php');

/**
 * Formata uma data no formato YYYY-MM-DD para DD/MM/YYYY
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
 * Converte valores 's'/'n' para 'Sim' ou 'Não'
 */
function formatBool($value)
{
    $value = strtolower(trim((string) $value));
    if ($value === 's') return 'Sim';
    if ($value === 'n') return 'Não';
    return '';
}

/**
 * Desenha o cabeçalho padrão: logo + linha + título
 * com pouco espaço acima/abaixo do “RELATÓRIO DE VISITA”
 */
function renderHeader($pdf, $logoPath)
{
    if (file_exists($logoPath)) {
        $logoWidth = 28;
        $logoY     = 10;

        // Logo
        $pdf->Image($logoPath, 15, $logoY, $logoWidth);
        $yAfterLogo = $pdf->getImageRBY();

        // Linha logo abaixo do logo
        $linhaY = $yAfterLogo + 1;
        $pdf->SetLineWidth(0.1);
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Line(15, $linhaY, 195, $linhaY);

        // Cursor um pouco acima, para reduzir espaço até o título
        $pdf->SetY($linhaY + 1.5);
    } else {
        // Se não houver logo, posiciona relativamente alto
        $pdf->SetY(22);
    }

    // Título centralizado com altura menor e pouco espaço abaixo
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 5, 'RELATÓRIO DE VISITA', 0, 1, 'C');
    $pdf->Ln(1);
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    die("ID de internação inválido.");
}

$visitaDao     = new visitaDao($conn, $BASE_URL);
$internacaoDao = new internacaoDao($conn, $BASE_URL);

// 1) Visitas
$visitas = $visitaDao->joinVisitaInternacao($id);

// 2) Internação
$internacoes = $internacaoDao->selectAllInternacao('id_internacao = ' . $id);
if (empty($internacoes)) {
    die("Nenhuma internação encontrada para o ID informado.");
}
$internacao = $internacoes[0];

// --- Indicadores simples ---
$totalVisitas = count($visitas);

$dataInternacaoObj   = DateTime::createFromFormat('Y-m-d', $internacao['data_intern_int'] ?? '');
$dataUltimaVisitaObj = null;
if (!empty($visitas)) {
    $datas = array_column($visitas, 'data_visita_vis');
    rsort($datas);
    $dataUltimaVisitaObj = DateTime::createFromFormat('Y-m-d', $datas[0]);
}
$baseData      = $dataUltimaVisitaObj ?: new DateTime();
$diasInternado = $dataInternacaoObj
    ? $dataInternacaoObj->diff($baseData)->days
    : '—';

// --------- PDF ---------
$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle("Relatório de Visita - Internação #{$id}");
$pdf->SetMargins(15, 15, 15);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$logoPath = 'img/LogoConexAud.png';
renderHeader($pdf, $logoPath);

// Cores
$corRoxo   = [106,  46, 126];
$corCinza  = [230, 230, 230];

// ===================== RESUMO =====================
$pdf->SetFillColor(...$corRoxo);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Cell(0, 6, 'RESUMO DA INTERNAÇÃO', 0, 1, 'L', true);
$pdf->Ln(1);

$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFillColor(...$corCinza);
$pdf->Cell(60, 6, 'Total de Visitas:', 1, 0, 'L', true);
$pdf->Cell(0, 6, $totalVisitas, 1, 1, 'L', false);

$pdf->SetFillColor(...$corCinza);
$pdf->Cell(60, 6, 'Dias Internação:', 1, 0, 'L', true);
$pdf->Cell(0, 6, is_numeric($diasInternado) ? $diasInternado . ' dias' : $diasInternado, 1, 1, 'L', false);
$pdf->Ln(3);

// ===================== INFORMAÇÕES DA INTERNAÇÃO =====================
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Cell(0, 5, 'INFORMAÇÕES DA INTERNAÇÃO', 0, 1, 'L', false);

$pdf->SetDrawColor(200, 200, 200);
$yLinhaInfo = $pdf->GetY();
$pdf->Line(15, $yLinhaInfo, 195, $yLinhaInfo);
$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(...$corCinza);
$pdf->SetDrawColor(180, 180, 180);

// Nome Paciente (linha inteira)
$pdf->Cell(50, 6, 'Nome do Paciente:', 1, 0, 'L', true);
$pdf->Cell(0, 6, $internacao['nome_pac'] ?? '', 1, 1, 'L', false);
$pdf->Ln(1);

// Campos em 3 colunas, com label em negrito
$dadosInternacao = [
    'ID da Internação'    => $internacao['id_internacao'] ?? '',
    'Data da Internação'  => formatDate($internacao['data_intern_int'] ?? ''),
    'Hora da Internação'  => $internacao['hora_intern_int'] ? substr($internacao['hora_intern_int'], 0, 5) : '',
    'Hospital'            => $internacao['nome_hosp'] ?? '',
    'Especialidade'       => $internacao['especialidade_int'] ?? '',
    'Origem'              => $internacao['origem_int'] ?? '',
    'Modo de Internação'  => $internacao['modo_internacao_int'] ?? '',
    'Tipo de Admissão'    => $internacao['tipo_admissao_int'] ?? '',
    'Acomodação'          => $internacao['acomodacao_int'] ?? '',
    'Grupo de Patologia'  => $internacao['grupo_patologia_int'] ?? '',
    'Patologia Principal' => $internacao['patologia_pat'] ?? '',
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
            1,     // border
            0,     // ln
            1,     // fill
            false, // reseth
            'L',
            true   // autopadding
        );
    }
    $pdf->SetY($currentY + 6);
    $pdf->SetX($startX);
}
$pdf->Ln(3);

// ===================== DETALHES DAS VISITAS =====================
if (empty($visitas)) {
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->Cell(0, 6, 'Nenhuma visita cadastrada para esta internação.', 0, 1, 'L');
} else {
    foreach ($visitas as $idx => $visita) {

        // A partir da 2ª visita, nova página + cabeçalho
        if ($idx > 0) {
            $pdf->AddPage();
            renderHeader($pdf, $logoPath);
        }

        // Título da visita
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(0, 5, 'DETALHES DA VISITA #' . ($idx + 1), 0, 1, 'L', false);

        $pdf->SetDrawColor(200, 200, 200);
        $yLinhaVis = $pdf->GetY();
        $pdf->Line(15, $yLinhaVis, 195, $yLinhaVis);
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(0, 0, 0);

        // Campos da visita em 3 colunas, label em negrito
        $dadosVisita = [
            'ID da Visita'           => $visita['id_visita'] ?? '',
            'Data da Visita'         => formatDate($visita['data_visita_vis'] ?? ''),
            'ID Paciente'            => $visita['id_paciente'] ?? '',
            'Internação Relacionada' => $visita['fk_internacao_vis'] ?? '',
            'Visita Médica'          => formatBool($visita['visita_med_vis'] ?? ''),
            'Visita Enfermagem'      => formatBool($visita['visita_enf_vis'] ?? ''),
            'Visita Noturna'         => formatBool($visita['visita_no_vis'] ?? ''),
            'Auditor Médico'         => $visita['visita_auditor_prof_med'] ?? '',
            'Auditor Enfermagem'     => $visita['visita_auditor_prof_enf'] ?? '',
            'Hospital da Visita'     => $visita['nome_hosp'] ?? '',
            'Grupo de Patologia'     => $visita['grupo_patologia_int'] ?? '',
            'Titular'                => $visita['titular_int'] ?? '',
            'Modo Internação'        => $visita['modo_internacao_int'] ?? '',
            'Tipo de Admissão'       => $visita['tipo_admissao_int'] ?? '',
            'Acomodação'             => $visita['acomodacao_int'] ?? '',
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

        // Relatório da Visita
        $pdf->SetFillColor(...$corCinza);
        $pdf->MultiCell(0, 6, 'Relatório da Visita:', 1, 'L', true);
        $pdf->MultiCell(0, 6, $visita['rel_visita_vis'] ?? '', 1, 'L', false);
        $pdf->Ln(1);

        // Ações da Visita
        $pdf->SetFillColor(...$corCinza);
        $pdf->MultiCell(0, 6, 'Ações da Visita:', 1, 'L', true);
        $pdf->MultiCell(0, 6, $visita['acoes_int_vis'] ?? '', 1, 'L', false);
        $pdf->Ln(3);
    }
}

// --- Rodapé ---
$pdf->SetY(-15);
$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 0, 'R');

ob_end_clean();
$pdf->Output("relatorio_visita_{$id}.pdf", 'D');
exit();
