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

/**
 * Converte índice numérico (0,1,2...) em letra de coluna Excel (A,B,C...)
 */
function colLetterFromIndex(int $index): string
{
    $index += 1; // 1-based
    $letter = '';
    while ($index > 0) {
        $mod = ($index - 1) % 26;
        $letter = chr(65 + $mod) . $letter;
        $index = (int)(($index - $mod) / 26);
    }
    return $letter;
}

// -----------------------------------------------------
// 1) Recuperar filtros (os mesmos da listagem/URL)
// -----------------------------------------------------

$pesquisa_nome   = getParam('pesquisa_nome', '');
$pesquisa_pac    = getParam('pesquisa_pac', '');
$pesqInternado   = getParam('pesqInternado', 's'); // não usado no WHERE, mas mantido p/ compatibilidade
$limite          = (int) getParam('limite', 10);
$ordenar         = getParam('ordenar', 'id_internacao');
$data_alta       = getParam('data_alta', '');
$data_alta_max   = getParam('data_alta_max', '');
$colsParam       = getParam('cols', ''); // campos vindos do modal (ex: "id_int,hosp,pac,tipo_alta,data_alta,uti")

// Se veio data_alta sem data_alta_max, usar hoje
if ($data_alta && !$data_alta_max) {
    $data_alta_max = date('Y-m-d');
}

// -----------------------------------------------------
// 2) Montar WHERE (MESMA LÓGICA DO form_list_internacao_alta.php)
// -----------------------------------------------------

$condicoes = [];

// Hospital (ho.nome_hosp)
if (strlen(trim($pesquisa_nome)) > 0) {
    $buscaEsc = escLike($pesquisa_nome);
    $condicoes[] = 'ho.nome_hosp LIKE "%' . $buscaEsc . '%"';
}

// Paciente (pa.nome_pac)
if (strlen(trim($pesquisa_pac)) > 0) {
    $pacEsc = escLike($pesquisa_pac);
    $condicoes[] = 'pa.nome_pac LIKE "%' . $pacEsc . '%"';
}

// Data de alta
if (strlen(trim($data_alta)) > 0) {
    $ini = escLike($data_alta);
    $fim = escLike($data_alta_max ?: $data_alta);
    $condicoes[] = 'alta.data_alta_alt BETWEEN "' . $ini . '" AND "' . $fim . '"';
}

$where = implode(' AND ', array_filter($condicoes));

// -----------------------------------------------------
// 3) Ordenação
// -----------------------------------------------------

$order = $ordenar ?: 'data_alta_alt DESC';

// -----------------------------------------------------
// 4) Campos selecionados no modal
// -----------------------------------------------------

$colsCodes = [];
if (!empty($colsParam)) {
    $colsCodes = array_filter(array_map('trim', explode(',', $colsParam)));
}

// Se nada selecionado (ou veio vazio), usamos padrão com todos
if (empty($colsCodes)) {
    $colsCodes = ['id_int', 'hosp', 'pac', 'tipo_alta', 'data_alta', 'uti'];
}

// Mapeamento de código -> label
$labelsMap = [
    'id_int'        => 'ID Internação',
    'hosp'          => 'Hospital',
    'pac'           => 'Paciente',
    'tipo_alta'     => 'Tipo Alta',
    'data_alta'     => 'Data Alta',
    'uti'           => 'UTI',
    'senha'         => 'Senha',
    'matricula'     => 'Matrícula',
    'evolucao'      => 'Evolução',
    'acoes'         => 'Ações',
    'programacao'   => 'Programação',
    'especialidade' => 'Especialidade',
];


// -----------------------------------------------------
// 5) Buscar dados na DAO (SEM paginação)
// -----------------------------------------------------

$altaDao = new altaDAO($conn, $BASE_URL);

try {
    // assinatura: findAltaWhere($where, $order, $limit)
    // para export, sem limite (pega todos os registros filtrados)
    $registros = $altaDao->findAltaWhere($where, $order ?: null, null);
} catch (Throwable $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Erro ao buscar altas para exportação:\n\n";
    echo $e->getMessage();
    exit;
}

// -----------------------------------------------------
// 6) Montar Excel
// -----------------------------------------------------

$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();

// Ocultar gridlines
$sheet->setShowGridlines(false);

// Logo
$logoPath = __DIR__ . '/img/LogoConexAud.png';
if (file_exists($logoPath)) {
    $logo = new Drawing();
    $logo->setName('Logo');
    $logo->setDescription('Logo da Empresa');
    $logo->setPath($logoPath);
    $logo->setHeight(32);
    $logo->setCoordinates('A2');
    $logo->setWorksheet($sheet);
}

$lastCol = colLetterFromIndex(count($colsCodes) - 1);
$sheet->getRowDimension(1)->setRowHeight(28);
$sheet->getRowDimension(2)->setRowHeight(18);

// Título
$sheet->setCellValue('D1', 'Alta Hospitalar - Listagem');
$sheet->mergeCells('D1:' . $lastCol . '1');
$sheet->getStyle('D1')->getFont()->setBold(true)->setSize(14);

// Data de extração
$sheet->setCellValue('D2', 'Data de Extração: ' . date('d/m/Y H:i'));
$sheet->mergeCells('D2:' . $lastCol . '2');

$headerRow = 6;

// Cabeçalhos conforme campos escolhidos
foreach (array_values($colsCodes) as $index => $code) {
    $colLetter = colLetterFromIndex($index);
    $headerLabel = $labelsMap[$code] ?? strtoupper($code);
    $sheet->setCellValue($colLetter . $headerRow, $headerLabel);
}

// Estilo do cabeçalho
$headerStyle = [
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D3D3D3'],
    ],
    'font' => [
        'bold' => true,
    ],
];

foreach (array_keys($colsCodes) as $index) {
    $colLetter = colLetterFromIndex($index);
    $sheet->getStyle($colLetter . $headerRow)->applyFromArray($headerStyle);
}

$row = $headerRow + 1;

// Dados
foreach ($registros as $alta) {

    foreach (array_values($colsCodes) as $index => $code) {
        $colLetter = colLetterFromIndex($index);
        $value     = '';

        switch ($code) {
            case 'id_int':
                $value = $alta['fk_id_int_alt'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'hosp':
                $value = $alta['nome_hosp'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'pac':
                $value = $alta['nome_pac'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'tipo_alta':
                $value = $alta['tipo_alta_alt'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'data_alta':
                $dataAltaStr = $alta['data_alta_alt'] ?? null;
                $dataExcel = null;
                if (!empty($dataAltaStr) && $dataAltaStr !== '0000-00-00') {
                    $dt = parseDateOrNull($dataAltaStr);
                    if ($dt) {
                        $dataExcel = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dt);
                    }
                }
                if ($dataExcel !== null) {
                    $sheet->setCellValue($colLetter . $row, $dataExcel);
                    $sheet->getStyle($colLetter . $row)
                        ->getNumberFormat()
                        ->setFormatCode('dd/mm/yyyy');
                } else {
                    $sheet->setCellValue($colLetter . $row, '');
                }
                break;

            case 'uti':
                $uti = (!empty($alta['id_uti'])) ? 'Sim' : 'Não';
                $sheet->setCellValue($colLetter . $row, $uti);
                break;

            // NOVOS CAMPOS

            case 'senha':
                $value = $alta['senha_int'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'matricula':
                $value = $alta['matricula_pac'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'evolucao':
                $value = $alta['rel_int'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'acoes':
                $value = $alta['acoes_int'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'programacao':
                $value = $alta['programacao_int'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            case 'especialidade':
                $value = $alta['especialidade_int'] ?? '';
                $sheet->setCellValue($colLetter . $row, $value);
                break;

            default:
                $sheet->setCellValue($colLetter . $row, '');
                break;
        }
    }

    $row++;
}


// Auto largura nas colunas utilizadas
for ($i = 0; $i < count($colsCodes); $i++) {
    $colLetter = colLetterFromIndex($i);
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
}

// Bordas da tabela inteira
$lastDataRow = $row - 1;
if ($lastDataRow >= $headerRow) {
    $firstColLetter = colLetterFromIndex(0);
    $lastColLetter  = colLetterFromIndex(count($colsCodes) - 1);
    $allCells       = $firstColLetter . $headerRow . ':' . $lastColLetter . $lastDataRow;

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
// 7) Download
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
