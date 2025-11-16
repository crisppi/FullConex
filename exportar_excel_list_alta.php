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

// -----------------------------------------------------
// 1) Recuperar filtros (os mesmos da listagem/URL)
// -----------------------------------------------------

$pesquisa_nome       = getParam('pesquisa_nome', '');   // nome do paciente
$pesqInternado       = getParam('pesqInternado', 's');  // 's' = só internados
$limite_pag          = (int) getParam('limite_pag', 10);
$pesquisa_pac        = getParam('pesquisa_pac', '');    // prontuário / doc
$ordenar_param       = getParam('ordenar', '1');        // código de ordenação
$senha_int         = $_GET['senha_int']         ?? '';
$data_intern_int   = $_GET['data_intern_int']   ?? '';
$data_intern_int_max = $_GET['data_intern_int_max'] ?? '';


// (se depois quiser datas, é só incluir na URL e aqui)
// $data_intern_int     = getParam('data_intern_int', null);
// $data_intern_int_max = getParam('data_intern_int_max', null);

// Para exportação, usamos limite grande
$limiteExport = 1000000;

// -----------------------------------------------------
// 2) Montar WHERE (usando campos que vimos no var_dump)
// -----------------------------------------------------

$condicoes = [];

// Filtro por nome do paciente (campo: nome_pac)
if (strlen(trim($pesquisa_nome)) > 0) {
    $buscaEsc = escLike($pesquisa_nome);
    $condicoes[] = '(nome_hosp LIKE "%' . $buscaEsc . '%")';
}

// Filtro por nome do paciente (campo: nome_pac)
if (strlen(trim($senha_int)) > 0) {
    $senhaEsc = escLike($senha_int);
    $condicoes[] = '(senha_int LIKE "%' . $senhaEsc . '%")';
}
// Filtro por prontuário / identificador (se tiver esse campo na view)
if (strlen(trim($pesquisa_pac)) > 0) {
    $pacEsc = escLike($pesquisa_pac);
    $condicoes[] = '(nome_pac LIKE "%' . $pacEsc . '%")';
}

// Filtro "só internados" usando internado_int = 's'
if ($pesqInternado === 's') {
    $condicoes[] = "internado_int = 's'";
}

// Se depois quiser usar datas:
// if (!empty($data_intern_int)) {
//     $dataIniEsc = escLike($data_intern_int);
//     $condicoes[] = 'data_intern_int >= "' . $dataIniEsc . '"';
// }
// if (!empty($data_intern_int_max)) {
//     $dataFimEsc = escLike($data_intern_int_max);
//     $condicoes[] = 'data_intern_int <= "' . $dataFimEsc . '"';
// }

$where = implode(' AND ', $condicoes);

// -----------------------------------------------------
// 3) Ordenação (data_intern_int / nome_pac)
// -----------------------------------------------------

switch ($ordenar_param) {
    case '2':
        $order = 'data_intern_int ASC';
        break;
    case '3':
        $order = 'nome_pac ASC';
        break;
    case '4':
        $order = 'nome_pac DESC';
        break;
    default:
        $order = 'data_intern_int DESC';
        break;
}

// -----------------------------------------------------
// 4) Buscar dados na DAO (SEM paginação)
// -----------------------------------------------------

$internacaoDao = new internacaoDao($conn, $BASE_URL);

try {
    // Assumindo assinatura: selectAllInternacao($where, $order, $limit)
    $registros = $internacaoDao->selectAllInternacao($where, $order, $limiteExport);
} catch (Throwable $e) {
    // Se der erro de SQL, mostra mensagem simples
    header('Content-Type: text/plain; charset=utf-8');
    echo "Erro ao buscar internações para exportação:\n\n";
    echo $e->getMessage();
    exit;
}
// var_dump($where); // Remover esta linha após testes
// exit();
// -----------------------------------------------------
// 4.1) (OPCIONAL) DEBUG – se quiser testar de novo, descomente:
//
// $primeiro = is_array($registros) && count($registros) > 0 ? $registros[0] : null;
// header('Content-Type: text/plain; charset=utf-8');
// var_dump([
//     'GET'              => $_GET,
//     'where'            => $where,
//     'order'            => $order,
//     'limiteExport'     => $limiteExport,
//     'qtd_registros'    => is_array($registros) ? count($registros) : 'N/A',
//     'primeiro_registro'=> $primeiro,
// ]);
// exit;
//
// -----------------------------------------------------

// -----------------------------------------------------
// 5) Montar Excel
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

// Estilo do cabeçalho (fundo cinza + negrito)
// Se você mudou cor do texto, cuidado para não deixar a fonte
// da coluna B igual ao fundo nas linhas de dados.
$headerStyle = [
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D3D3D3',
        ],
    ],
    'font' => [
        'bold' => true,
        // 'color' => ['rgb' => '000000'], // fonte preta (se quiser garantir)
    ],
];

foreach (range('A', 'I') as $columnID) {
    $sheet->getStyle($columnID . $headerRow)->applyFromArray($headerStyle);
}

$row = $headerRow + 1;

// Dados
foreach ($registros as $internacao) {

    // Campo do hospital: já vimos no var_dump que é nome_hosp
    $hospital = $internacao['nome_hosp'] ?? '';

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

    $sheet->setCellValue('A' . $row, $internacao['id_internacao']       ?? '')
        ->setCellValue('B' . $row, $hospital)
        ->setCellValue('C' . $row, $internacao['nome_pac']              ?? '')
        ->setCellValue('D' . $row, $dataExcel)
        ->setCellValue('E' . $row, $internacao['senha_int']             ?? '')
        ->setCellValue('F' . $row, $internacao['acomodacao_int']        ?? '')
        ->setCellValue('G' . $row, $uti)
        ->setCellValue('H' . $row, $internacao['modo_internacao_int']   ?? '')
        ->setCellValue('I' . $row, $internacao['tipo_admissao_int']     ?? '');

    if ($dataExcel !== null) {
        $sheet->getStyle('D' . $row)
            ->getNumberFormat()
            ->setFormatCode('dd/mm/yyyy');
    }

    // (Opcional: se você mexeu em cor de fonte antes, pode garantir aqui
    // que a linha de dados vai ficar com cor padrão preta:)
    // $sheet->getStyle('A' . $row . ':I' . $row)
    //       ->getFont()->getColor()->setRGB('000000');

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

// -----------------------------------------------------
// 6) Download
// -----------------------------------------------------

$writer   = new Xlsx($spreadsheet);
$filename = 'internacoes_' . date('YmdHis') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Limpa qualquer saída anterior
if (function_exists('ob_get_length') && ob_get_length()) {
    @ob_end_clean();
}

$writer->save('php://output');
exit;
