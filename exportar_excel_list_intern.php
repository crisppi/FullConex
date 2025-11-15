<?php
ob_start();

require_once("globals.php");
require_once("db.php");
require_once("models/internacao.php");
require_once("dao/internacaoDao.php");
require_once("vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Pega parâmetro de GET com valor padrão
 */
function getParam(string $name, $default = '')
{
    $value = filter_input(INPUT_GET, $name, FILTER_UNSAFE_RAW);
    return $value !== null ? $value : $default;
}

/**
 * Pequena proteção de string (para montar o WHERE com LIKE)
 */
function escLike($str)
{
    return addslashes(trim((string)$str));
}

/**
 * Converte data YYYY-MM-DD para DateTime (ou null)
 */
function parseDateOrNull(?string $date): ?DateTime
{
    if (!$date || $date === '0000-00-00') {
        return null;
    }
    try {
        return new DateTime($date);
    } catch (Exception $e) {
        return null;
    }
}

// -------------------------
// 1) Recuperar filtros
// -------------------------

$busca      = getParam('pesquisa_nome', '');
$busca_user = getParam('pesquisa_user', '');
$limite     = (int) getParam('limite', 10);
$ordenar    = getParam('ordenar', 1); // aqui você pode tratar depois na DAO

// Se tiver mais filtros na listagem, adicione aqui:
// $data_ini   = getParam('data_ini', '');
// $data_fim   = getParam('data_fim', '');
// $hospital   = getParam('fk_hospital_int', '');
// $acomodacao = getParam('acomodacao_int', '');

// -------------------------
// 2) Montar WHERE
// -------------------------

$condicoes = [];

// Filtro por hospital / CNPJ (texto livre)
if (strlen(trim($busca)) > 0) {
    $buscaEsc = escLike($busca);
    $condicoes[] = '(nome_hosp LIKE "%' . $buscaEsc . '%" OR cnpj_hosp LIKE "%' . $buscaEsc . '%")';
}

// Filtro por usuário / e-mail (se fizer sentido na sua JOIN)
if (strlen(trim($busca_user)) > 0) {
    $buscaUserEsc = escLike($busca_user);
    $condicoes[] = '(usuario_user LIKE "%' . $buscaUserEsc . '%" OR email_user LIKE "%' . $buscaUserEsc . '%")';
}

// Exemplos de mais filtros:
/*
if (!empty($hospital)) {
    $condicoes[] = 'fk_hospital_int = ' . (int) $hospital;
}

if (!empty($acomodacao)) {
    $acomEsc = escLike($acomodacao);
    $condicoes[] = 'acomodacao_int = "' . $acomEsc . '"';
}

if (!empty($data_ini) && !empty($data_fim)) {
    $condicoes[] = "data_intern_int BETWEEN '" . escLike($data_ini) . "' AND '" . escLike($data_fim) . "'";
}
*/

$where = implode(' AND ', $condicoes);

// -------------------------
// 3) Buscar dados (sem paginação)
// -------------------------

$internacaoDao = new internacaoDao($conn, $BASE_URL);

// para exportação, ignora limite da tela
$limiteExport = 1000000;

// se sua DAO aceitar (where, order, limit), use assim:
$query = $internacaoDao->selectAllInternacao($where, $ordenar, $limiteExport);

// -------------------------
// 4) Montar Excel
// -------------------------

$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();

// Ocultar gridlines
$sheet->setShowGridlines(false);

// Logo
$logoPath = 'img/full-03.jpeg';
if (file_exists($logoPath)) {
    $logo = new Drawing();
    $logo->setName('Logo');
    $logo->setDescription('Logo da Empresa');
    $logo->setPath($logoPath);
    $logo->setHeight(80);
    $logo->setCoordinates('A1');
    $logo->setWorksheet($sheet);
}

// Linha inicial após logo
$row = 6;

// Título
$sheet->setCellValue('A' . $row, 'Listagem Internação');
$sheet->mergeCells('A' . $row . ':I' . $row);
$sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);

// Data de extração
$sheet->setCellValue('A' . ($row + 1), 'Data de Extração: ' . date('d/m/Y'));
$sheet->mergeCells('A' . ($row + 1) . ':I' . ($row + 1));

$row = $row + 3; // linha do cabeçalho

$headerRow = $row;

// Cabeçalhos
$sheet->setCellValue('A' . $headerRow, 'ID Internação')
    ->setCellValue('B' . $headerRow, 'Hospital')
    ->setCellValue('C' . $headerRow, 'Nome do Paciente')
    ->setCellValue('D' . $headerRow, 'Data de Internação')
    ->setCellValue('E' . $headerRow, 'Senha')
    ->setCellValue('F' . $headerRow, 'Acomodação')
    ->setCellValue('G' . $headerRow, 'UTI')
    ->setCellValue('H' . $headerRow, 'Modo de Internação')
    ->setCellValue('I' . $headerRow, 'Tipo de Admissão');

// Estilo do cabeçalho
$headerStyle = [
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D3D3D3',
        ],
    ],
    'font' => [
        'bold' => true,
    ],
];

foreach (range('A', 'I') as $columnID) {
    $sheet->getStyle($columnID . $headerRow)->applyFromArray($headerStyle);
}

$row = $headerRow + 1;

// Dados
foreach ($query as $internacao) {
    // Data de internação
    $dataInternacaoStr = $internacao['data_intern_int'] ?? null;
    $dataExcel = null;

    if (!empty($dataInternacaoStr) && $dataInternacaoStr !== '0000-00-00') {
        $dt = parseDateOrNull($dataInternacaoStr);
        if ($dt) {
            $dataExcel = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dt);
        }
    }

    // UTI (Sim se 's')
    $uti = (!empty($internacao['internacao_uti_int']) && strtolower($internacao['internacao_uti_int']) === 's')
        ? 'Sim'
        : '';

    $sheet->setCellValue('A' . $row, $internacao['id_internacao'] ?? '')
        ->setCellValue('B' . $row, $internacao['nome_hosp'] ?? '')
        ->setCellValue('C' . $row, $internacao['nome_pac'] ?? '')
        ->setCellValue('D' . $row, $dataExcel)
        ->setCellValue('E' . $row, $internacao['senha_int'] ?? '')
        ->setCellValue('F' . $row, $internacao['acomodacao_int'] ?? '')
        ->setCellValue('G' . $row, $uti)
        ->setCellValue('H' . $row, $internacao['modo_internacao_int'] ?? '')
        ->setCellValue('I' . $row, $internacao['tipo_admissao_int'] ?? '');

    if ($dataExcel !== null) {
        $sheet->getStyle('D' . $row)
            ->getNumberFormat()
            ->setFormatCode('dd/mm/yyyy');
    }

    $row++;
}

// Auto largura
foreach (range('A', 'I') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Bordas da tabela inteira
$lastDataRow = $row - 1;
if ($lastDataRow >= $headerRow) {
    $allCells = 'A' . $headerRow . ':I' . $lastDataRow;

    $borderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color'       => ['rgb' => '000000'],
            ],
        ],
    ];

    $sheet->getStyle($allCells)->applyFromArray($borderStyle);
}

// -------------------------
// 5) Download
// -------------------------

$writer   = new Xlsx($spreadsheet);
$filename = 'internacoes_' . date('YmdHis') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Limpa qualquer saída anterior (igual no PDF)
if (function_exists('ob_get_length') && ob_get_length()) {
    ob_end_clean();
}

$writer->save('php://output');
exit;
