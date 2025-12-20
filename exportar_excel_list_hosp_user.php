<?php
// Incluir as dependências do PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Definir a conexão com o banco de dados (ajustar conforme necessário)
include_once("globals.php");
include_once("db.php");
include_once("models/hospitalUser.php");
include_once("dao/hospitalUserDao.php");

// Recuperar os dados filtrados
$busca = filter_input(INPUT_GET, 'pesquisa_nome') ? filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS) : "";
$busca_user = filter_input(INPUT_GET, 'pesquisa_user') ? filter_input(INPUT_GET, 'pesquisa_user', FILTER_SANITIZE_SPECIAL_CHARS) : "";

// Definir as condições de busca
$condicoes = [
    strlen($busca) ? '(nome_hosp LIKE "%' . $busca . '%" OR cnpj_hosp LIKE "%' . $busca . '%")' : null,
    strlen($busca_user) ? '(usuario_user LIKE "%' . $busca_user . '%" OR email_user LIKE "%' . $busca_user . '%")' : null,
    'ativo_user = "s"'
];

$condicoes = array_filter($condicoes);
$where = implode(' AND ', $condicoes);

// Instanciar a classe hospitalUserDAO
$hospitalUser = new hospitalUserDAO($conn, $BASE_URL);

// Obter todos os dados da tabela conforme os filtros (remover a limitação de paginação)
$query = $hospitalUser->selectAllhospitalUser($where, 'usuario_user', 9999); // 9999 para buscar todos os registros

// Criar um novo Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Ocultar as linhas de grade
$sheet->setShowGridlines(false); // Não exibir as linhas de grade

// Inserir o logo
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

$sheet->getRowDimension(1)->setRowHeight(28);
$sheet->getRowDimension(2)->setRowHeight(18);

// Adicionando título "Hospitais por Usuário" e data de extração
$sheet->setCellValue('D1', 'Hospitais por Usuário')
    ->mergeCells('D1:H1');
$sheet->getStyle('D1')->getFont()->setBold(true)->setSize(14);
$sheet->setCellValue('D2', 'Data de Extração: ' . date('d/m/Y H:i'));
$sheet->mergeCells('D2:H2');

$row = 6; // linha do cabeçalho da tabela

// Cabeçalho das colunas no Excel
$headerRow = $row;
$sheet->setCellValue('A' . $row, 'Id')
    ->setCellValue('B' . $row, 'Hospital')
    ->setCellValue('C' . $row, 'Nome')
    ->setCellValue('D' . $row, 'E-mail')
    ->setCellValue('E' . $row, 'Id Usuário')
    ->setCellValue('F' . $row, 'Cargo')
    ->setCellValue('G' . $row, 'Nível');

// Define a cor de fundo cinza para os cabeçalhos
$headerStyle = [
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D3D3D3'  // Cor cinza claro
        ]
    ],
    'font' => [
        'bold' => true  // Deixar o texto em negrito
    ]
];

// Aplica o estilo para as células de cabeçalho
foreach (range('A', 'G') as $columnID) {
    $sheet->getStyle($columnID . $row)->applyFromArray($headerStyle);
}

$row++; // Incrementa a linha para começar o preenchimento dos dados

// Preenche as células com os dados
foreach ($query as $hospitalUserSel) {
    extract($hospitalUserSel);

    // Preencher cada linha com os dados
    $sheet->setCellValue('A' . $row, $id_hospitalUser)
        ->setCellValue('B' . $row, $nome_hosp)
        ->setCellValue('C' . $row, $usuario_user)
        ->setCellValue('D' . $row, $email_user)
        ->setCellValue('E' . $row, $fk_usuario_hosp)
        ->setCellValue('F' . $row, $cargo_user)
        ->setCellValue('G' . $row, $nivel_user);
    $row++;
}

// Ajuste automático da largura das colunas após o preenchimento dos dados
foreach (range('A', 'G') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Adicionando bordas em todas as células
$allCells = 'A' . $headerRow . ':G' . ($row - 1); // Define o intervalo de todas as células preenchidas

// Estilo para as bordas
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, // Define o estilo da borda
            'color' => ['rgb' => '000000'] // Cor preta para as bordas
        ]
    ]
];

// Aplica as bordas em todas as células
$sheet->getStyle($allCells)->applyFromArray($borderStyle);

// Criação do arquivo Excel
$writer = new Xlsx($spreadsheet);

// Definindo o nome do arquivo de saída
$filename = 'Hospitais_Usuarios_' . date('YmdHis') . '.xlsx';

// Enviando o cabeçalho para download do arquivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Evita o envio de qualquer outro conteúdo antes da exportação
ob_clean(); // Limpa o buffer de saída
flush(); // Garante que o conteúdo seja enviado

// Envia o conteúdo para o navegador
$writer->save('php://output');
exit;
