<?php
ob_start();

require_once("globals.php");
require_once("db.php");

require_once("models/alta.php");
require_once("dao/altaDao.php");
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

// -----------------------------------------------------
// 1) Recuperar filtros (os mesmos da listagem de ALTAS)
// -----------------------------------------------------

$pesquisa_nome   = getParam('pesquisa_nome', '');   // hospital (ho.nome_hosp)
$pesquisa_pac    = getParam('pesquisa_pac',  '');   // paciente (pa.nome_pac)
$pesqInternado   = getParam('pesqInternado', 's');  // só pra manter compatibilidade de URL
$ordenar_param   = getParam('ordenar', 'data_alta_alt');
$data_alta       = getParam('data_alta', '');
$data_alta_max   = getParam('data_alta_max', '');

// Para exportação, usamos limite grande (se o altaDao tiver parâmetro limit)
$limiteExport = 1000000;

// Mesmo comportamento da tela: se vier data_alta e não vier max, max = hoje
if ($data_alta && !$data_alta_max) {
    $data_alta_max = date('Y-m-d');
}

// -----------------------------------------------------
// 2) Montar WHERE (espelhando list_internacao_alta.php)
// -----------------------------------------------------

$condicoes = [];

// Hospital (ho.nome_hosp)
if (strlen(trim($pesquisa_nome)) > 0) {
    $condicoes[] = 'ho.nome_hosp LIKE "%' . escLike($pesquisa_nome) . '%"';
}

// Paciente (pa.nome_pac)
if (strlen(trim($pesquisa_pac)) > 0) {
    $condicoes[] = 'pa.nome_pac LIKE "%' . escLike($pesquisa_pac) . '%"';
}

// Data de alta
if (strlen(trim($data_alta)) > 0) {
    $ini = escLike($data_alta);
    $fim = escLike($data_alta_max ?: $data_alta);
    $condicoes[] = 'alta.data_alta_alt BETWEEN "' . $ini . '" AND "' . $fim . '"';
}

$condicoes = array_filter($condicoes);
$where     = implode(' AND ', $condicoes);

// -----------------------------------------------------
// 3) Ordenação
//    (a tela envia: id_internacao, nome_pac, nome_hosp, data_alta_alt)
// -----------------------------------------------------

switch ($ordenar_param) {
    case 'nome_pac':
        $order = 'nome_pac ASC';
        break;
    case 'nome_hosp':
        $order = 'nome_hosp ASC';
        break;
    case 'id_internacao':
        // na view de altas o campo é fk_id_int_alt, então ordenamos por ele
        $order = 'fk_id_int_alt ASC';
        break;
    case 'data_alta_alt':
        $order = 'data_alta_alt DESC';
        break;
    default:
        // fallback: por data da alta, mais recente primeiro
        $order = 'data_alta_alt DESC';
        break;
}

// -----------------------------------------------------
// 4) Buscar dados na DAO (SEM paginação)
// -----------------------------------------------------

$altaDao = new altaDAO($conn, $BASE_URL);

try {
    // Assinatura suposta: findAltaWhere($where, $order = null, $limit = null)
    // Para export, não limitamos (null)
    $registros = $altaDao->findAltaWhere($where, $order, null);
} catch (Throwable $e) {
    // Se der erro de SQL, mostra mensagem simples
    header('Content-Type: text/plain; charset=utf-8');
    echo "Erro ao buscar altas para exportação:\n\n";
    echo $e->getMessage();
    exit;
}

// -----------------------------------------------------
// 5) Montar Excel (layout similar ao da Internação)
// -----------------------------------------------------

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
$sheet->setCellValue('A' . $row, 'Listagem Alta Hospitalar');
$sheet->mergeCells('A' . $row . ':F' . $row);
$sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);

// Data de extração
$sheet->setCellValue('A' . ($row + 1), 'Data de Extração: ' . date('d/m/Y'));
$sheet->mergeCells('A' . ($row + 1) . ':F' . ($row + 1));

$row = $row + 3; // linha do cabeçalho
$headerRow = $row;

// Cabeçalhos – espelhando a tabela da tela: Id-Int, UTI, Hospital, Paciente, Tipo Alta, Data Alta
$sheet->setCellValue('A' . $headerRow, 'ID Internação')
    ->setCellValue('B' . $headerRow, 'UTI')
    ->setCellValue('C' . $headerRow, 'Hospital')
    ->setCellValue('D' . $headerRow, 'Paciente')
    ->setCellValue('E' . $headerRow, 'Tipo Alta')
    ->setCellValue('F' . $headerRow, 'Data Alta');

// Estilo do cabeçalho (fundo cinza + negrito)
$headerStyle = [
    'fill' => [
        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D3D3D3'],
    ],
    'font' => [
        'bold' => true,
    ],
];

foreach (range('A', 'F') as $columnID) {
    $sheet->getStyle($columnID . $headerRow)->applyFromArray($headerStyle);
}

$row = $headerRow + 1;

// Dados
foreach ($registros as $alta) {

    // ID Internação (fk_id_int_alt)
    $idInternacao = $alta['fk_id_int_alt'] ?? '';

    // UTI (Sim/Não, com base em id_uti vindo do join)
    $uti = !empty($alta['id_uti']) ? 'Sim' : 'Não';

    // Data de alta
    $dataAltaStr = $alta['data_alta_alt'] ?? null;
    $dataExcel   = null;

    if (!empty($dataAltaStr) && $dataAltaStr !== '0000-00-00') {
        $dt = parseDateOrNull($dataAltaStr);
        if ($dt) {
            $dataExcel = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dt);
        }
    }

    $sheet->setCellValue('A' . $row, $idInternacao)
        ->setCellValue('B' . $row, $uti)
        ->setCellValue('C' . $row, $alta['nome_hosp']     ?? '')
        ->setCellValue('D' . $row, $alta['nome_pac']      ?? '')
        ->setCellValue('E' . $row, $alta['tipo_alta_alt'] ?? '')
        ->setCellValue('F' . $row, $dataExcel);

    if ($dataExcel !== null) {
        $sheet->getStyle('F' . $row)
            ->getNumberFormat()
            ->setFormatCode('dd/mm/yyyy');
    }

    $row++;
}

// Auto largura
foreach (range('A', 'F') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Bordas da tabela inteira
$lastDataRow = $row - 1;
if ($lastDataRow >= $headerRow) {
    $allCells = 'A' . $headerRow . ':F' . $lastDataRow;

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

// -----------------------------------------------------
// 6) Download
// -----------------------------------------------------

$writer   = new Xlsx($spreadsheet);
$filename = 'altas_hospitalares_' . date('YmdHis') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Limpa qualquer saída anterior
if (function_exists('ob_get_length') && ob_get_length()) {
    @ob_end_clean();
}

$writer->save('php://output');
exit;
