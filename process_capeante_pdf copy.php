<?php
ob_start();

require_once("globals.php");
require_once("db.php");
require_once("dao/capeanteDAO.php");
require_once("vendor/autoload.php");

function formatDate($date)
{
    if (!$date || $date === '0000-00-00') return '';
    $dt = \DateTime::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format('d/m/Y') : $date;
}

function formatMoney($valor)
{
    return 'R$ ' . number_format(floatval($valor), 2, ',', '.');
}

$id_capeante     = filter_input(INPUT_GET, "id_capeante", FILTER_SANITIZE_NUMBER_INT);
$fk_int_capeante = filter_input(INPUT_GET, "fk_int_capeante", FILTER_SANITIZE_NUMBER_INT);

if (!$id_capeante || !$fk_int_capeante) die("ID(s) inválido(s).");

$capeanteDao = new capeanteDAO($conn, $BASE_URL);
$where = "ca.id_capeante = {$id_capeante}";
$internacao = $capeanteDao->selectAllcapeante($where);

if (empty($internacao)) die("Conta Capeante não encontrada.");
$data = $internacao[0];

$pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle("Conta Capeante #{$id_capeante}");
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$corRoxo  = [106, 46, 126];
$corCinza = [230, 230, 230];

$logoPath = 'img/logo_novo.png';
if (file_exists($logoPath)) $pdf->Image($logoPath, 10, 10, 35);
$pdf->Ln(18);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 8, "CONTA CAPEANTE Nº {$data['id_capeante']}", 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(...$corRoxo);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 7, 'RESUMO DA INTERNAÇÃO', 0, 1, 'L', true);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(...$corCinza);

$dadosResumo = [
    'Hospital' => $data['nome_hosp'],
    'Paciente' => $data['nome_pac'],
    'Senha' => $data['senha_int'],
    'Data Internação' => formatDate($data['data_intern_int']),
    'Tipo Internação' => $data['tipo_admissao_int'],
    'Modo Admissão' => $data['modo_internacao_int'],
    'Data Inicial' => formatDate($data['data_inicial_capeante']),
    'Data Final' => formatDate($data['data_final_capeante']),
];

$colunas = 3;
$largura_total_util = 277;
$largura_coluna_total = $largura_total_util / $colunas;
$labelW = 45;
$valueW = $largura_coluna_total - $labelW;

foreach (array_chunk($dadosResumo, 3, true) as $linha) {
    foreach ($linha as $rotulo => $valor) {
        if ($rotulo === 'Paciente') {
            $pdf->SetFont('helvetica', 'B', 9);
        } else {
            $pdf->SetFont('helvetica', '', 9);
        }
        $pdf->Cell($labelW, 6, $rotulo, 1, 0, 'L', true);
        $pdf->Cell($valueW, 6, $valor, 1, 0, 'L');
    }
    $pdf->Ln();
}

$blocos = [
    'CONSOLIDADO CONTA' => [
        'Valor Apresentado' => $data['valor_apresentado_capeante'],
        'Valor Final' => $data['valor_final_capeante'],
    ],
    'GLOSAS CONSOLIDADAS' => [
        'Glosa Total' => $data['valor_glosa_total'],
        'Glosa Médica' => $data['valor_glosa_med'],
        'Glosa Enfermagem' => $data['valor_glosa_enf'],
    ],
    'VALORES POR SEGMENTO' => [
        'Honorários' => $data['valor_honorarios'],
        'MatMed' => $data['valor_matmed'],
        'SADT' => $data['valor_sadt'],
        'Oxigenioterapia' => $data['valor_oxig'],
        'Taxas' => $data['valor_taxa'],
        'Diárias' => $data['valor_diarias'],
        'OPME' => $data['valor_opme'],
    ],
    'GLOSAS POR SEGMENTO' => [
        'Honorários' => $data['glosa_honorarios'],
        'MatMed' => $data['glosa_matmed'],
        'SADT' => $data['glosa_sadt'],
        'Oxigenioterapia' => $data['glosa_oxig'],
        'Taxas' => $data['glosa_taxas'],
        'Diárias' => $data['glosa_diaria'],
        'OPME' => $data['glosa_opme'],
    ],
];

foreach ($blocos as $titulo => $valores) {
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(...$corRoxo);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 7, $titulo, 0, 1, 'L', true);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetFillColor(...$corCinza);
    $pdf->SetTextColor(0, 0, 0);

    $col = 0;
    foreach ($valores as $rotulo => $valor) {
        $pdf->Cell($labelW, 6, $rotulo, 1, 0, 'L', true);
        $pdf->Cell($valueW, 6, formatMoney($valor), 1, 0, 'L');
        $col++;
        if ($col == 3) {
            $pdf->Ln();
            $col = 0;
        }
    }
    if ($col !== 0) $pdf->Ln();
}

$pdf->Ln(6);
$pdf->SetFont('helvetica', 'I', 9);
$assinaturas = [
    'Médico(a) Auditor(a)' => $data['nome_med'],
    'Enfermeiro(a) Auditor(a)' => $data['nome_enf'],
    'Administrativo(a)' => $data['nome_adm'],
    'Responsável Hospital' => $data['nome_aud_hosp']
];

foreach ($assinaturas as $cargo => $nome) {
    $pdf->MultiCell(65, 10, $nome . "\n" . $cargo, 0, 'C', false, 0);
}

$pdf->Ln(25);
$pdf->SetFont('helvetica', 'I', 8);
setlocale(LC_TIME, 'pt_BR.utf8');
$pdf->Cell(0, 10, 'São Paulo, ' . strftime('%d de %B de %Y'), 0, 1, 'C');

ob_end_clean();
$pdf->Output("ContaCapeante_{$id_capeante}.pdf", 'D');
exit();