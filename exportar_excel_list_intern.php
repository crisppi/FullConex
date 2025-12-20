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
 * Pega campos selecionados (GET/POST) no formato:
 *  - campos[]=pac&campos[]=data_intern
 *  - ou campos=pac,data_intern
 */
function getCamposSelecionados(): array
{
    // 1) Tenta campos[] via GET
    $arr = filter_input(INPUT_GET, 'campos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

    // 2) Se não for array, tenta campos[] via POST
    if (!is_array($arr)) {
        $arr = filter_input(INPUT_POST, 'campos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    }

    // 3) Se ainda não for array, tenta campos como string CSV (GET/POST)
    if (!is_array($arr)) {
        $raw = filter_input(INPUT_GET, 'campos', FILTER_UNSAFE_RAW);
        if ($raw === null) {
            $raw = filter_input(INPUT_POST, 'campos', FILTER_UNSAFE_RAW);
        }

        if ($raw !== null && trim($raw) !== '') {
            $pieces = preg_split('/[;,]+/', $raw);
            $arr = is_array($pieces) ? $pieces : [];
        } else {
            $arr = [];
        }
    }

    // Limpa espaços e vazios
    $arr = array_map('trim', $arr);
    $arr = array_filter($arr, fn($v) => $v !== '');

    return array_values($arr);
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
// FLAG DE DEBUG (usar: ?debug=1 na URL)
// -----------------------------------------------------
$DEBUG = getParam('debug', '') === '1';

// -----------------------------------------------------
// 1) Filtros (MESMOS da listagem)
// -----------------------------------------------------

$pesquisa_nome        = getParam('pesquisa_nome', '');
$pesqInternado        = getParam('pesqInternado', 's');
$limite_pag           = (int) getParam('limite_pag', 10);
$pesquisa_pac         = getParam('pesquisa_pac', '');
$ordenar_param        = getParam('ordenar', '1');
$senha_int            = getParam('senha_int', '');
$data_intern_int      = getParam('data_intern_int', '');
$data_intern_int_max  = getParam('data_intern_int_max', '');
// Campos selecionados no modal
$camposSelecionados   = getCamposSelecionados();

// Limite grande só para exportar
$limiteExport = 1000000;

// -----------------------------------------------------
// 2) Montar WHERE (copiar mesma lógica da listagem)
// -----------------------------------------------------

$condicoes = [];

// Nome do paciente (nome_pac)
if (strlen(trim($pesquisa_nome)) > 0) {
    $buscaEsc = escLike($pesquisa_nome);
    $condicoes[] = '(nome_pac LIKE "%' . $buscaEsc . '%")';
}

// Senha de internação
if (strlen(trim($senha_int)) > 0) {
    $senhaEsc = escLike($senha_int);
    $condicoes[] = '(senha_int LIKE "%' . $senhaEsc . '%")';
}

// Outro identificador (ajuste se usar prontuário/doc específicos)
if (strlen(trim($pesquisa_pac)) > 0) {
    $pacEsc = escLike($pesquisa_pac);
    $condicoes[] = '(nome_pac LIKE "%' . $pacEsc . '%")';
}

// Só internados
if ($pesqInternado === 's') {
    $condicoes[] = "internado_int = 's'";
}

// Intervalo de datas de internação
if (!empty($data_intern_int)) {
    $dataIniEsc = escLike($data_intern_int);
    $condicoes[] = 'data_intern_int >= "' . $dataIniEsc . '"';
}

if (!empty($data_intern_int_max)) {
    $dataFimEsc = escLike($data_intern_int_max);
    $condicoes[] = 'data_intern_int <= "' . $dataFimEsc . '"';
}

$where = implode(' AND ', $condicoes);

// -----------------------------------------------------
// 3) Ordenação
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
// 4) Buscar dados via DAO
// -----------------------------------------------------

$internacaoDao = new internacaoDao($conn, $BASE_URL);

try {
    $registros = $internacaoDao->selectAllInternacao($where, $order, $limiteExport);
} catch (Throwable $e) {

    if ($DEBUG) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Erro ao buscar internações para exportação:\n\n";
        echo $e->getMessage();
        exit;
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo "Erro ao buscar internações para exportação.";
    exit;
}

// -----------------------------------------------------
// 4.1) Última visita médica (texto quadro clínico)
// -----------------------------------------------------
$ultimaVisitaMedica = [];
if (is_array($registros) && count($registros) > 0) {
    $internacaoIds = array_values(array_unique(array_filter(array_map(function ($row) {
        return isset($row['id_internacao']) ? (int)$row['id_internacao'] : 0;
    }, $registros))));

    if (!empty($internacaoIds)) {
        $placeholders = implode(',', array_fill(0, count($internacaoIds), '?'));
        $sqlUltima = "
            SELECT sub.fk_internacao_vis AS id_internacao,
                   v.rel_visita_vis AS quadro_clinico
            FROM (
                SELECT fk_internacao_vis,
                       COALESCE(
                           MAX(CASE WHEN LOWER(COALESCE(visita_med_vis, '')) IN ('s','sim','1') THEN id_visita END),
                           MAX(id_visita)
                       ) AS id_visita_target
                FROM tb_visita
                WHERE fk_internacao_vis IN ($placeholders)
                  AND (retificado IS NULL OR retificado IN ('', '0', 0, 'n', 'N'))
                GROUP BY fk_internacao_vis
            ) sub
            INNER JOIN tb_visita v ON v.id_visita = sub.id_visita_target
        ";

        try {
            $stmtUlt = $conn->prepare($sqlUltima);
            foreach ($internacaoIds as $idx => $internacaoId) {
                $stmtUlt->bindValue($idx + 1, $internacaoId, PDO::PARAM_INT);
            }
            $stmtUlt->execute();
            $rowsUlt = $stmtUlt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rowsUlt as $row) {
                $idInt = (int)($row['id_internacao'] ?? 0);
                if ($idInt > 0) {
                    $ultimaVisitaMedica[$idInt] = trim((string)($row['quadro_clinico'] ?? ''));
                }
            }
        } catch (Throwable $e) {
            error_log('Falha ao buscar última visita médica para exportação: ' . $e->getMessage());
        }
    }
}

foreach ($registros as &$internacao) {
    $idAtual = (int)($internacao['id_internacao'] ?? 0);
    $internacao['ultima_visita_medico'] = $ultimaVisitaMedica[$idAtual] ?? '';
}
unset($internacao);

// -----------------------------------------------------
// 4.1) DEBUG opcional
// -----------------------------------------------------
if ($DEBUG) {
    $primeiro = is_array($registros) && count($registros) > 0 ? $registros[0] : null;

    header('Content-Type: text/plain; charset=utf-8');
    var_dump([
        'GET'               => $_GET,
        'camposSelecionados' => $camposSelecionados,
        'where'             => $where,
        'order'             => $order,
        'limiteExport'      => $limiteExport,
        'qtd_registros'     => is_array($registros) ? count($registros) : 'N/A',
        'primeiro_registro' => $primeiro,
    ]);
    exit;
}

// -----------------------------------------------------
// 5) MAPAS (labels, fields, types)
// -----------------------------------------------------

$labelsMap = [
    'id_int'        => 'ID Internação',
    'hosp'          => 'Hospital',
    'pac'           => 'Paciente',
    'data_intern'   => 'Data Internação',
    'hora_intern'   => 'Hora Internação',
    'senha'         => 'Senha',
    'acomodacao'    => 'Acomodação',
    'uti'           => 'UTI',
    'modo'          => 'Modo Internação',
    'tipo_adm'      => 'Tipo Admissão',
    'internado'     => 'Internado',

    // NOVOS CAMPOS
    'especialidade'   => 'Especialidade',
    'patologia'       => 'Patologia',
    'relatorio'       => 'Relatório',
    'acoes'           => 'Ações',
    'programacao'     => 'Programação',
    'medico_titular'  => 'Médico Titular',
    'matricula'       => 'Matrícula',
    'profissional'    => 'Nome do Profissional',
    'profissional_cargo' => 'Cargo do Profissional',
    'profissional_registro' => 'Registro Profissional',
    'ultima_visita_medico' => 'Última visita médica (quadro clínico)',
];


$fieldMap = [
    'id_int'        => 'id_internacao',
    'hosp'          => 'nome_hosp',
    'pac'           => 'nome_pac',
    'data_intern'   => 'data_intern_int',
    'hora_intern'   => 'hora_intern_int',
    'senha'         => 'senha_int',
    'acomodacao'    => 'acomodacao_int',
    'uti'           => 'internacao_uti_int',
    'modo'          => 'modo_internacao_int',
    'tipo_adm'      => 'tipo_admissao_int',
    'internado'     => 'internado_int',

    // NOVOS CAMPOS (usando os nomes que você passou)
    'especialidade'   => 'especialidade_int',
    'patologia'       => 'patologia_pato',
    'relatorio'       => 'rel_int',
    'acoes'           => 'acoes_int',
    'programacao'     => 'programacao_int',
    'medico_titular'  => 'titular_int',
    'matricula'       => 'matricula_pac',
    'profissional'    => 'auditor_nome',
    'profissional_cargo' => 'auditor_cargo',
    'profissional_registro' => 'auditor_registro',
    'ultima_visita_medico' => 'ultima_visita_medico',
];


$typeMap = [
    'id_int'        => 'text',
    'hosp'          => 'text',
    'pac'           => 'text',
    'data_intern'   => 'date',
    'hora_intern'   => 'text',
    'senha'         => 'text',
    'acomodacao'    => 'text',
    'uti'           => 'uti',
    'modo'          => 'text',
    'tipo_adm'      => 'text',
    'internado'     => 'sn',
    'patologia'       => 'text',
    'relatorio'       => 'text',
    'acoes'           => 'text',
    'programacao'     => 'text',
    'medico_titular'  => 'text',
    'matricula'       => 'text',
    'profissional'    => 'text',
    'profissional_cargo' => 'text',
    'profissional_registro' => 'text',
    'ultima_visita_medico' => 'text',
];


// -----------------------------------------------------
// 5.1) APLICAR FILTRO DOS CAMPOS DO MODAL
// -----------------------------------------------------

if (!empty($camposSelecionados)) {
    $labelsAtivos = [];

    foreach ($camposSelecionados as $sel) {

        $selKey = null;

        // 1) Se o valor já é uma chave lógica (ex: 'pac', 'data_intern')
        if (isset($labelsMap[$sel])) {
            $selKey = $sel;
        } else {
            // 2) Ou se veio como nome do campo do banco (ex: 'nome_pac')
            $keyFound = array_search($sel, $fieldMap, true);
            if ($keyFound !== false && isset($labelsMap[$keyFound])) {
                $selKey = $keyFound;
            }
        }

        if ($selKey !== null && isset($labelsMap[$selKey])) {
            $labelsAtivos[$selKey] = $labelsMap[$selKey];
        }
    }

    // Se nada bateu, exporta tudo pra não vir vazio
    if (empty($labelsAtivos)) {
        $labelsAtivos = $labelsMap;
    }
} else {
    // Se o modal não mandou nada, exporta todas as colunas
    $labelsAtivos = $labelsMap;
}

// -----------------------------------------------------
// 6) Criar Spreadsheet
// -----------------------------------------------------

$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();

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

$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(max(1, count($labelsAtivos)));
$sheet->getRowDimension(1)->setRowHeight(28);
$sheet->getRowDimension(2)->setRowHeight(18);

// Título
$sheet->setCellValue('D1', 'Listagem de Internações');
$sheet->mergeCells('D1:' . $lastCol . '1');
$sheet->getStyle('D1')->getFont()->setBold(true)->setSize(14);

// Data/hora extração
$sheet->setCellValue('D2', 'Data de Extração: ' . date('d/m/Y H:i'));
$sheet->mergeCells('D2:' . $lastCol . '2');

$headerRow = 6;

// Letras das colunas
$colLetters = [];
$letter = 'A';
foreach ($labelsAtivos as $key => $label) {
    $colLetters[$key] = $letter;
    $letter++;
}

// Cabeçalhos
foreach ($labelsAtivos as $key => $label) {
    $col = $colLetters[$key];
    $sheet->setCellValue($col . $headerRow, $label);
}

// Estilo cabeçalho
$headerStyle = [
    'fill' => [
        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D3D3D3'],
    ],
    'font' => [
        'bold' => true,
    ],
];

$firstCol = reset($colLetters);
$lastCol  = end($colLetters);
$sheet->getStyle($firstCol . $headerRow . ':' . $lastCol . $headerRow)
    ->applyFromArray($headerStyle);

// -----------------------------------------------------
// 7) Dados
// -----------------------------------------------------

$row = $headerRow + 1;

foreach ($registros as $internacao) {

    foreach ($labelsAtivos as $key => $label) {

        $col   = $colLetters[$key];
        $field = $fieldMap[$key] ?? null;
        $type  = $typeMap[$key]  ?? 'text';

        $value = $field !== null && isset($internacao[$field])
            ? $internacao[$field]
            : '';

        switch ($type) {
            case 'date':
                $dataExcel = null;
                if (!empty($value) && $value !== '0000-00-00') {
                    $dt = parseDateOrNull($value);
                    if ($dt) {
                        $dataExcel = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dt);
                    }
                }
                $sheet->setCellValue($col . $row, $dataExcel);
                if ($dataExcel !== null) {
                    $sheet->getStyle($col . $row)
                        ->getNumberFormat()
                        ->setFormatCode('dd/mm/yyyy');
                }
                break;

            case 'uti':
                $uti = (!empty($value) && strtolower(trim((string)$value)) === 's') ? 'Sim' : 'Não';
                $sheet->setCellValue($col . $row, $uti);
                break;

            case 'sn':
                $v = strtolower(trim((string)$value));
                if ($v === 's') {
                    $sheet->setCellValue($col . $row, 'Sim');
                } elseif ($v === 'n') {
                    $sheet->setCellValue($col . $row, 'Não');
                } else {
                    $sheet->setCellValue($col . $row, '');
                }
                break;

            default:
                $sheet->setCellValue($col . $row, $value);
                break;
        }
    }

    $row++;
}

// Auto largura
foreach ($labelsAtivos as $key => $label) {
    $col = $colLetters[$key];
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Bordas
$lastDataRow = $row - 1;
if ($lastDataRow >= $headerRow) {
    $allCells = $firstCol . $headerRow . ':' . $lastCol . $lastDataRow;

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
// 8) Download
// -----------------------------------------------------

$writer   = new Xlsx($spreadsheet);
$filename = 'internacoes_' . date('YmdHis') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

if (function_exists('ob_get_length') && ob_get_length()) {
    @ob_end_clean();
}

$writer->save('php://output');
exit;
