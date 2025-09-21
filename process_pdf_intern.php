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

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    die("ID de internação inválido.");
}

$visitaDao     = new visitaDao($conn, $BASE_URL);
$internacaoDao = new internacaoDao($conn, $BASE_URL);

// 1) Busca todas as visitas atreladas à internação
$visitas = $visitaDao->joinVisitaInternacao($id);

// 2) Busca os dados da internação; como o DAO retorna um array de arrays, pegamos o primeiro elemento
$internacoes = $internacaoDao->selectAllInternacao('id_internacao = ' . $id);
if (empty($internacoes)) {
    die("Nenhuma internação encontrada para o ID informado.");
}
$internacao = $internacoes[0]; // $internacao é um array associativo

// --- CÁLCULO DE INDICADORES SIMPLES ---
// Total de visitas
$totalVisitas = count($visitas);

// Dias desde a data de internação até hoje (ou até a última visita)
$dataInternacaoObj   = DateTime::createFromFormat('Y-m-d', $internacao['data_intern_int'] ?? '');
$dataUltimaVisitaObj = null;
if (!empty($visitas)) {
    // pega a maior data_visita_vis
    $datas = array_column($visitas, 'data_visita_vis');
    rsort($datas);
    $dataUltimaVisitaObj = DateTime::createFromFormat('Y-m-d', $datas[0]);
}
$baseData      = $dataUltimaVisitaObj ?: new DateTime(); // se não houver visita, usa hoje
$diasInternado = $dataInternacaoObj
    ? $dataInternacaoObj->diff($baseData)->days
    : '—';

// --------- Início da geração de PDF ---------
$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle("Relatório de Visita - Internação #{$id}");
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// --- 1) Logo e cabeçalho ---
$logoPath = 'img/logo_novo.png';
if (file_exists($logoPath)) {
    // largura 40mm, altura proporcional
    $pdf->Image($logoPath, 15, 10, 40);
}
$pdf->Ln(15);

// Definindo cores principais (baseadas no logo)
$corRoxo   = [106,  46, 126]; // roxo escuro do logo
$corAzul   = [74, 201, 224]; // azul-claro do logo (não usado diretamente aqui, mas à disposição)
$corCinza  = [230, 230, 230]; // cinza claro para preenchimento de células
$corBranco = [255, 255, 255]; // branco

// --- 2) Título principal ---
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'RELATÓRIO DE VISITA', 0, 1, 'C');
$pdf->Ln(5);

// --- 3) Seção de Resumo / Indicadores ---
$pdf->SetFillColor(...$corRoxo);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, 'RESUMO DA INTERNAÇÃO', 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);

// Resumo em duas linhas (total de visitas e dias desde internação)
$pdf->SetFillColor(...$corCinza);
$pdf->Cell(60, 8, 'Total de Visitas:', 1, 0, 'L', true);
$pdf->Cell(0, 8, $totalVisitas, 1, 1, 'L', false);

$pdf->SetFillColor(...$corCinza);
$pdf->Cell(60, 8, 'Dias Internação:', 1, 0, 'L', true);
$pdf->Cell(0, 8, is_numeric($diasInternado) ? $diasInternado . ' dias' : $diasInternado, 1, 1, 'L', false);
$pdf->Ln(5);

// --- 4) INFORMAÇÕES DA INTERNAÇÃO em DUAS COLUNAS ---
// 4.1) Cabeçalho da seção
$pdf->SetFillColor(...$corRoxo);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, 'INFORMAÇÕES DA INTERNAÇÃO', 0, 1, 'L', true);
$pdf->Ln(2);

// 4.2) Inicia conteúdo
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(...$corCinza);
$pdf->SetDrawColor(180, 180, 180);

// 4.3) Nome Paciente ocupa a linha inteira
$pdf->Cell(50, 8, 'Nome do Paciente:', 1, 0, 'L', true);
$pdf->Cell(0, 8, $internacao['nome_pac'] ?? '', 1, 1, 'L', false);
$pdf->Ln(2);

// 4.4) Campos restantes em duas colunas (cada célula 45 mm)
// Removi “Data de Criação” para manter número par de campos
$dadosInternacao = [
    'ID da Internação'   => $internacao['id_internacao'] ?? '',
    'Data da Internação' => formatDate($internacao['data_intern_int'] ?? ''),
    'Hora da Internação' => $internacao['hora_intern_int'] ? substr($internacao['hora_intern_int'], 0, 5) : '',
    'Hospital'            => $internacao['nome_hosp'] ?? '',
    'Especialidade'       => $internacao['especialidade_int'] ?? '',
    'Origem'              => $internacao['origem_int'] ?? '',
    'Modo de Internação'  => $internacao['modo_internacao_int'] ?? '',
    'Tipo de Admissão'    => $internacao['tipo_admissao_int'] ?? '',
    'Acomodação'          => $internacao['acomodacao_int'] ?? '',
    'Grupo de Patologia'  => $internacao['grupo_patologia_int'] ?? '',
    'Patologia Principal'         => $internacao['patologia_pat'] ?? '',
    'Patologia '         => $internacao['patologia2_pat'] ?? '',
    'UTI'   => formatBool($internacao['internado_uti_int'] ?? ''),
    'Senha'               => $internacao['senha_int'] ?? '',
];

$chavesInt  = array_keys($dadosInternacao);
$valoresInt = array_values($dadosInternacao);
$totalInt    = count($dadosInternacao);

for ($i = 0; $i < $totalInt; $i += 2) {
    // Coluna 1 (rótulo + valor)
    $campo1 = $chavesInt[$i];
    $valor1 = $valoresInt[$i];
    $pdf->Cell(45, 8, $campo1, 1, 0, 'L', true);
    $pdf->Cell(45, 8, $valor1, 1, 0, 'L', false);

    // Coluna 2 (rótulo + valor), se existir
    if (isset($chavesInt[$i + 1])) {
        $campo2 = $chavesInt[$i + 1];
        $valor2 = $valoresInt[$i + 1];
        $pdf->Cell(45, 8, $campo2, 1, 0, 'L', true);
        $pdf->Cell(45, 8, $valor2, 1, 1, 'L', false);
    } else {
        // Se for ímpar, preenche duas células vazias
        $pdf->Cell(45, 8, '', 1, 0, 'L', true);
        $pdf->Cell(45, 8, '', 1, 1, 'L', false);
    }
}
$pdf->Ln(5);

// --- 5) DETALHES DE CADA VISITA ---
// Se não houver visitas, exibe mensagem
if (empty($visitas)) {
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell(0, 8, 'Nenhuma visita cadastrada para esta internação.', 0, 1, 'L');
} else {
    foreach ($visitas as $idx => $visita) {
        // Cabeçalho de cada visita
        $pdf->SetFillColor(...$corRoxo);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'DETALHES DA VISITA #' . ($idx + 1), 0, 1, 'L', true);
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        // 5.1) Campos em pares (duas colunas, cada célula 45 mm)
        $camposDuplos = [
            'ID da Visita'            => $visita['id_visita'] ?? '',
            'Data da Visita'          => formatDate($visita['data_visita_vis'] ?? ''),
            'ID Paciente'             => $visita['id_paciente'] ?? '',
            'Internação Relacionada'  => $visita['fk_internacao_vis'] ?? '',
            'Visita Médica'           => formatBool($visita['visita_med_vis'] ?? ''),
            'Visita Enfermagem'       => formatBool($visita['visita_enf_vis'] ?? ''),
            'Visita Noturna'          => formatBool($visita['visita_no_vis'] ?? ''),
            'Auditor Médico'          => $visita['visita_auditor_prof_med'] ?? '',
            'Auditor Enfermagem'      => $visita['visita_auditor_prof_enf'] ?? '',
            'Hospital da Visita'      => $visita['nome_hosp'] ?? '',
            'Grupo de Patologia' => $visita['grupo_patologia_int'] ?? '',
            'Titular '           => $visita['titular_int'] ?? '',
            'Modo Internação '   => $visita['modo_internacao_int'] ?? '',
            'Tipo de Admissão '  => $visita['tipo_admissao_int'] ?? '',
            'Acomodação '        => $visita['acomodacao_int'] ?? '',
        ];

        $chavesVis  = array_keys($camposDuplos);
        $valoresVis = array_values($camposDuplos);
        $totalVis   = count($camposDuplos);

        for ($j = 0; $j < $totalVis; $j += 2) {
            // Coluna 1 (rótulo + valor)
            $campoV1 = $chavesVis[$j];
            $valorV1 = $valoresVis[$j];
            $pdf->SetFillColor(...$corCinza);
            $pdf->Cell(45, 8, $campoV1, 1, 0, 'L', true);
            $pdf->Cell(45, 8, $valorV1, 1, 0, 'L', false);

            // Coluna 2 (rótulo + valor), se existir
            if (isset($chavesVis[$j + 1])) {
                $campoV2 = $chavesVis[$j + 1];
                $valorV2 = $valoresVis[$j + 1];
                $pdf->SetFillColor(...$corCinza);
                $pdf->Cell(45, 8, $campoV2, 1, 0, 'L', true);
                $pdf->Cell(45, 8, $valorV2, 1, 1, 'L', false);
            } else {
                // Se faltar um par, preenche duas células vazias
                $pdf->Cell(45, 8, '', 1, 0, 'L', true);
                $pdf->Cell(45, 8, '', 1, 1, 'L', false);
            }
        }
        $pdf->Ln(3);

        // 5.2) “Relatório da Visita” (full width)
        $pdf->SetFillColor(...$corCinza);
        $pdf->MultiCell(0, 8, 'Relatório da Visita:', 1, 'L', true);
        $pdf->MultiCell(0, 8, $visita['rel_visita_vis'] ?? '', 1, 'L', false);
        $pdf->Ln(2);

        // 5.3) “Ações da Visita” (full width)
        $pdf->SetFillColor(...$corCinza);
        $pdf->MultiCell(0, 8, 'Ações da Visita:', 1, 'L', true);
        $pdf->MultiCell(0, 8, $visita['acoes_int_vis'] ?? '', 1, 'L', false);
        $pdf->Ln(6);
    }
}

// --- 6) Rodapé (opcional) ---
$pdf->SetY(-20);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 0, 'R');

// Envia o PDF para download
ob_end_clean();
$pdf->Output("relatorio_visita_{$id}.pdf", 'D');
exit();
