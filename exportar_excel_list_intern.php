<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Incluir as dependências do PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Definir a conexão com o banco de dados (ajustar conforme necessário)
include_once("globals.php");
include_once("db.php");
include_once("models/internacao.php");
include_once("dao/internacaoDao.php");

// Recuperar os dados filtrados
$busca = filter_input(INPUT_GET, 'pesquisa_nome') ? filter_input(INPUT_GET, 'pesquisa_nome', FILTER_SANITIZE_SPECIAL_CHARS) : "";
$busca_user = filter_input(INPUT_GET, 'pesquisa_user') ? filter_input(INPUT_GET, 'pesquisa_user', FILTER_SANITIZE_SPECIAL_CHARS) : "";
$limite = filter_input(INPUT_GET, 'limite') ? filter_input(INPUT_GET, 'limite') : 10;
$ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;

// Definir as condições de busca
$condicoes = [
    strlen($busca) ? '(nome_hosp LIKE "%' . $busca . '%" OR cnpj_hosp LIKE "%' . $busca . '%")' : null,
    strlen($busca_user) ? '(usuario_user LIKE "%' . $busca_user . '%" OR email_user LIKE "%' . $busca_user . '%")' : null,
    // 'ativo_user = "s"'
];

// Filtrar e construir a query de busca
$condicoes = array_filter($condicoes);
$where = implode(' AND ', $condicoes);

// Instanciar a classe internacaoDAO
$internacaoDao = new internacaoDAO($conn, $BASE_URL);

// Obter todos os dados de internação conforme os filtros (sem paginação, para pegar todos os registros)
$query = $internacaoDao->selectAllInternacao($where, $ordenar, $limite);

// Criar um novo Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Ocultar as linhas de grade
$sheet->setShowGridlines(false); // Não exibir as linhas de grade

// Inserir o logo
$logoPath = 'img/full-03.jpeg';  // Caminho do logo na pasta "img"
$logo = new Drawing();
$logo->setName('Logo');
$logo->setDescription('Logo da Empresa');
$logo->setPath($logoPath); // Caminho para o arquivo do logo
$logo->setHeight(100); // Ajuste a altura do logo conforme necessário
$logo->setCoordinates('A1'); // Coloca o logo na célula A1
$logo->setWorksheet($sheet); // Adiciona o logo à planilha

// Pular 4 linhas após o logo
$row = 6; // Começa na linha 6 após o logo

// Adicionando título "Listagem Internação" e data de extração
$sheet->setCellValue('A' . $row, 'Listagem Internação')
    ->mergeCells('A' . $row . ':H' . $row); // Mescla as células de A6 até H6 para o título
$sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14); // Definindo o estilo do título
$sheet->setCellValue('A' . ($row + 1), 'Data de Extração: ' . date('d/m/Y')); // Adiciona a data da extração
$sheet->mergeCells('A' . ($row + 1) . ':H' . ($row + 1)); // Mescla as células de A7 até H7 para a data

// Pule uma linha
$row = $row + 3; // A partir da linha 9, onde começará a tabela

// Cabeçalho das colunas no Excel
$sheet->setCellValue('A' . $row, 'ID Internação')
    ->setCellValue('B' . $row, 'Hospital')
    ->setCellValue('C' . $row, 'Nome do Paciente')
    ->setCellValue('D' . $row, 'Data de Internação')
    ->setCellValue('E' . $row, 'Senha')
    ->setCellValue('F' . $row, 'Acomodação')
    ->setCellValue('G' . $row, 'UTI')
    ->setCellValue('H' . $row, 'Modo de Internação')
    ->setCellValue('I' . $row, 'Tipo de Admissão');

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

// Aplica o estilo para as células de cabeçalho (A9 a I9)
foreach (range('A', 'I') as $columnID) {
    $sheet->getStyle($columnID . $row)->applyFromArray($headerStyle);
}

$row++; // Incrementa a linha para começar o preenchimento dos dados

// Preenche as células com os dados da internação (seguindo a nova ordem)
foreach ($query as $internacao) {
    // Convertendo a data para o formato PHP 'd/m/Y'
    $dataInternacao = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new DateTime($internacao['data_intern_int']));

    // Verifica o valor de internacao_uti_int e coloca "Sim" se for "s"
    $uti = ($internacao['internacao_uti_int'] == 's') ? 'Sim' : '';

    // Preenche as células conforme a nova ordem
    $sheet->setCellValue('A' . $row, $internacao['id_internacao'])
        ->setCellValue('B' . $row, $internacao['nome_hosp'])
        ->setCellValue('C' . $row, $internacao['nome_pac'])
        ->setCellValue('D' . $row, $dataInternacao)
        ->setCellValue('E' . $row, $internacao['senha_int'])
        ->setCellValue('F' . $row, $internacao['acomodacao_int'])
        ->setCellValue('G' . $row, $uti)
        ->setCellValue('H' . $row, $internacao['modo_internacao_int'])
        ->setCellValue('I' . $row, $internacao['tipo_admissao_int']);

    // Aplicando a formatação de data na coluna D
    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
    $row++;
}

// Ajuste automático da largura das colunas após o preenchimento dos dados
foreach (range('A', 'I') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Adicionando bordas em todas as células
$allCells = 'A9:I' . $row; // Define o intervalo de todas as células preenchidas, começando após o título

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
$filename = 'internacoes_' . date('YmdHis') . '.xlsx';

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