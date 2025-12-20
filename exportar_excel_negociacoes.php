<?php
ob_start();

require_once("globals.php");
require_once("db.php");

require_once("models/negociacao.php");
require_once("dao/negociacaoDao.php");
require_once("vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$negociacaoDao = new negociacaoDAO($conn, $BASE_URL);

function getParam(string $name, $default = '')
{
    $value = filter_input(INPUT_GET, $name, FILTER_UNSAFE_RAW);
    return $value !== null ? $value : $default;
}

function esc_like_export(string $value): string
{
    return addslashes(trim($value));
}

$pesquisa_hosp  = getParam('pesquisa_hosp', '');
$pesquisa_pac   = getParam('pesquisa_pac', '');
$tipo_neg       = getParam('tipo_neg', '');
$data_ini       = getParam('data_ini', '');
$data_fim       = getParam('data_fim', '');
$saving_min     = getParam('saving_min', '');
$ordenar        = getParam('ordenar', 'ng.data_inicio_neg DESC');

if ($data_ini && !$data_fim) {
    $data_fim = $data_ini;
}

$condicoes = ['(ng.deletado_neg IS NULL OR ng.deletado_neg != "s")'];

if ($pesquisa_hosp !== '') {
    $condicoes[] = 'ho.nome_hosp LIKE "%' . esc_like_export($pesquisa_hosp) . '%"';
}
if ($pesquisa_pac !== '') {
    $condicoes[] = 'pa.nome_pac LIKE "%' . esc_like_export($pesquisa_pac) . '%"';
}
if ($tipo_neg !== '') {
    $condicoes[] = 'ng.tipo_negociacao = "' . esc_like_export($tipo_neg) . '"';
}
if ($data_ini !== '') {
    $ini = $data_ini;
    $fim = $data_fim ?: $data_ini;
    $condicoes[] = 'DATE(ng.data_inicio_neg) BETWEEN "' . esc_like_export($ini) . '" AND "' . esc_like_export($fim) . '"';
}
if ($saving_min !== '' && is_numeric($saving_min)) {
    $condicoes[] = 'ng.saving >= ' . (float)$saving_min;
}

$where = implode(' AND ', $condicoes);
$dados = $negociacaoDao->selectNegociacoesDetalhes($where, $ordenar, null);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Negociacoes');

$logoPath = __DIR__ . '/img/LogoConexAud.png';
if (file_exists($logoPath)) {
    $logo = new Drawing();
    $logo->setName('Logo');
    $logo->setDescription('Logo Conex');
    $logo->setPath($logoPath);
    $logo->setHeight(32);
    $logo->setCoordinates('A2');
    $logo->setWorksheet($sheet);
}

$lastCol = Coordinate::stringFromColumnIndex(count($headers));
$sheet->getRowDimension(1)->setRowHeight(28);
$sheet->getRowDimension(2)->setRowHeight(18);
$sheet->setCellValue('D1', 'Negociações - Exportação');
$sheet->mergeCells('D1:' . $lastCol . '1');
$sheet->getStyle('D1')->getFont()->setBold(true)->setSize(13);
$sheet->setCellValue('D2', 'Data da extração: ' . date('d/m/Y H:i'));
$sheet->mergeCells('D2:' . $lastCol . '2');

$sheet->setShowGridlines(false);
$headerRow = 6;

$headers = [
    'ID Internação',
    'Senha',
    'Matrícula',
    'Hospital',
    'Paciente',
    'Tipo',
    'Troca de',
    'Troca para',
    'Quantidade',
    'Saving',
    'Data início',
    'Data fim',
    'Auditor'
];

$sheet->fromArray($headers, null, 'A' . $headerRow);

$linha = $headerRow + 1;
foreach ($dados as $item) {
    $sheet->setCellValue('A' . $linha, $item['fk_id_int'] ?? '');
    $sheet->setCellValue('B' . $linha, $item['senha_int'] ?? '');
    $sheet->setCellValue('C' . $linha, $item['matricula_pac'] ?? '');
    $sheet->setCellValue('D' . $linha, $item['nome_hosp'] ?? '');
    $sheet->setCellValue('E' . $linha, $item['nome_pac'] ?? '');
    $sheet->setCellValue('F' . $linha, $item['tipo_negociacao'] ?? '');
    $sheet->setCellValue('G' . $linha, $item['troca_de'] ?? '');
    $sheet->setCellValue('H' . $linha, $item['troca_para'] ?? '');
    $sheet->setCellValue('I' . $linha, $item['qtd'] ?? '');
    $sheet->setCellValue('J' . $linha, (float)($item['saving'] ?? 0));
    $sheet->setCellValue('K' . $linha, $item['data_inicio_neg'] ? date('d/m/Y', strtotime($item['data_inicio_neg'])) : '');
    $sheet->setCellValue('L' . $linha, $item['data_fim_neg'] ? date('d/m/Y', strtotime($item['data_fim_neg'])) : '');
    $sheet->setCellValue('M' . $linha, $item['nome_usuario'] ?? '');
    $linha++;
}

$headerStyle = [
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E5E5E5'],
    ],
    'font' => ['bold' => true],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'BDBDBD'],
        ],
    ],
];
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'D0D0D0'],
        ],
    ],
];

$sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->applyFromArray($headerStyle);
$sheet->getStyle('A' . $headerRow . ':' . $lastCol . ($linha - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
$sheet->getStyle('A' . $headerRow . ':' . $lastCol . ($linha - 1))->applyFromArray($borderStyle);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getColumnDimension('D')->setWidth(30);

$writer = new Xlsx($spreadsheet);
$fileName = 'negociacoes_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
