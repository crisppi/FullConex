<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['email_user']) || ($_SESSION['ativo'] ?? '') !== 's') {
    http_response_code(401);
    exit('Não autorizado');
}

require_once __DIR__ . '/globals.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function fetchChartData(PDO $conn, string $sql): array
{
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$baseCondition = "(ng.deletado_neg IS NULL OR ng.deletado_neg != 's')";

$monthlySaving = fetchChartData(
    $conn,
    "
        SELECT 
            DATE_FORMAT(COALESCE(ng.data_inicio_neg, ng.data_fim_neg, ng.updated_at), '%Y-%m') AS periodo_ordenacao,
            DATE_FORMAT(COALESCE(ng.data_inicio_neg, ng.data_fim_neg, ng.updated_at), '%d/%m/%Y') AS referencia,
            SUM(COALESCE(ng.saving, 0)) AS total
        FROM tb_negociacao ng
        WHERE $baseCondition
        GROUP BY periodo_ordenacao, referencia
        ORDER BY periodo_ordenacao
    "
);

$monthlyCount = fetchChartData(
    $conn,
    "
        SELECT 
            DATE_FORMAT(COALESCE(ng.data_inicio_neg, ng.data_fim_neg, ng.updated_at), '%Y-%m') AS periodo_ordenacao,
            DATE_FORMAT(COALESCE(ng.data_inicio_neg, ng.data_fim_neg, ng.updated_at), '%d/%m/%Y') AS referencia,
            COUNT(*) AS total
        FROM tb_negociacao ng
        WHERE $baseCondition
        GROUP BY periodo_ordenacao, referencia
        ORDER BY periodo_ordenacao
    "
);

$savingByAuditor = fetchChartData(
    $conn,
    "
        SELECT 
            COALESCE(us.usuario_user, 'Sem responsável') AS auditor,
            SUM(COALESCE(ng.saving, 0)) AS total
        FROM tb_negociacao ng
        LEFT JOIN tb_user us ON ng.fk_usuario_neg = us.id_usuario
        WHERE $baseCondition
        GROUP BY auditor
        ORDER BY total DESC
    "
);

$countByAuditor = fetchChartData(
    $conn,
    "
        SELECT 
            COALESCE(us.usuario_user, 'Sem responsável') AS auditor,
            COUNT(*) AS total
        FROM tb_negociacao ng
        LEFT JOIN tb_user us ON ng.fk_usuario_neg = us.id_usuario
        WHERE $baseCondition
        GROUP BY auditor
        ORDER BY total DESC
    "
);

$savingByType = fetchChartData(
    $conn,
    "
        SELECT 
            COALESCE(ng.tipo_negociacao, 'Não informado') AS tipo,
            SUM(COALESCE(ng.saving, 0)) AS total
        FROM tb_negociacao ng
        WHERE $baseCondition
        GROUP BY tipo
        ORDER BY total DESC
    "
);

$typeByAuditor = fetchChartData(
    $conn,
    "
        SELECT 
            COALESCE(us.usuario_user, 'Sem responsável') AS auditor,
            COALESCE(ng.tipo_negociacao, 'Não informado') AS tipo,
            COUNT(*) AS total
        FROM tb_negociacao ng
        LEFT JOIN tb_user us ON ng.fk_usuario_neg = us.id_usuario
        WHERE $baseCondition
        GROUP BY auditor, tipo
        ORDER BY auditor, tipo
    "
);

$savingByHospital = fetchChartData(
    $conn,
    "
        SELECT 
            COALESCE(ho.nome_hosp, 'Sem hospital') AS hospital,
            SUM(COALESCE(ng.saving, 0)) AS total
        FROM tb_negociacao ng
        LEFT JOIN tb_internacao ac ON ng.fk_id_int = ac.id_internacao
        LEFT JOIN tb_hospital ho ON ac.fk_hospital_int = ho.id_hospital
        WHERE $baseCondition
        GROUP BY hospital
        ORDER BY total DESC
    "
);

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

$addSheet = function (Spreadsheet $spreadsheet, string $title, array $headers, array $rows) {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle($title);
    $col = 1;
    foreach ($headers as $header) {
        $sheet->setCellValueByColumnAndRow($col, 1, $header);
        $col++;
    }
    $rowIdx = 2;
    foreach ($rows as $row) {
        $col = 1;
        foreach ($row as $value) {
            $sheet->setCellValueByColumnAndRow($col, $rowIdx, $value);
            $col++;
        }
        $rowIdx++;
    }
    $sheet->getStyleByColumnAndRow(1, 1, count($headers), 1)->getFont()->setBold(true);
    for ($i = 1; $i <= count($headers); $i++) {
        $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
    }
};

$spreadsheet->addSheet(new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'tmp'), 0);
$spreadsheet->setActiveSheetIndex(0);
$spreadsheet->getActiveSheet()->setTitle('Saving mensal');
$spreadsheet->getActiveSheet()->setCellValue('A1', 'Período');
$spreadsheet->getActiveSheet()->setCellValue('B1', 'Saving (R$)');
$row = 2;
foreach ($monthlySaving as $item) {
    $spreadsheet->getActiveSheet()->setCellValue("A{$row}", $item['referencia']);
    $spreadsheet->getActiveSheet()->setCellValue("B{$row}", (float)$item['total']);
    $row++;
}
$spreadsheet->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);

$addSheet($spreadsheet, 'Negociações mensais', ['Período', 'Qtd'], array_map(fn($r) => [$r['referencia'], $r['total']], $monthlyCount));
$addSheet($spreadsheet, 'Saving x Auditor', ['Auditor', 'Saving (R$)'], array_map(fn($r) => [$r['auditor'], $r['total']], $savingByAuditor));
$addSheet($spreadsheet, 'Quantidade x Auditor', ['Auditor', 'Negociações'], array_map(fn($r) => [$r['auditor'], $r['total']], $countByAuditor));
$addSheet($spreadsheet, 'Saving x Tipo', ['Tipo', 'Saving (R$)'], array_map(fn($r) => [$r['tipo'], $r['total']], $savingByType));
$addSheet($spreadsheet, 'Tipo x Auditor', ['Auditor', 'Tipo', 'Qtd'], array_map(fn($r) => [$r['auditor'], $r['tipo'], $r['total']], $typeByAuditor));
$addSheet($spreadsheet, 'Saving x Hospital', ['Hospital', 'Saving (R$)'], array_map(fn($r) => [$r['hospital'], $r['total']], $savingByHospital));

$fileName = 'graficos_negociacoes_' . date('Ymd_His') . '.xlsx';
while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
