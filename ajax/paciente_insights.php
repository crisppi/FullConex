<?php
// ajax/paciente_insights.php
header('Content-Type: application/json; charset=utf-8');
session_start();

$ROOT = dirname(__DIR__);
chdir($ROOT);

require_once 'globals.php';
require_once 'db.php';
require_once 'models/internacao.php';
require_once 'dao/internacaoDao.php';

try {
    $pacienteId = filter_input(INPUT_GET, 'id_paciente', FILTER_VALIDATE_INT);
    if (!$pacienteId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'id_paciente obrigatório'
        ]);
        exit;
    }

    $internacaoDao = new internacaoDAO($conn, $BASE_URL);

    $totalInternacoes = (int) $internacaoDao->countByPaciente($pacienteId);

    $totalDiariasRow = $internacaoDao->findTotalDiariasByPacId($pacienteId);
    $totalDiarias = 0;
    if (is_array($totalDiariasRow) && isset($totalDiariasRow[0]['total_diarias'])) {
        $totalDiarias = (int) $totalDiariasRow[0]['total_diarias'];
    }

    $mp = $totalInternacoes > 0 ? round($totalDiarias / $totalInternacoes, 1) : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'total_internacoes' => $totalInternacoes,
            'total_diarias'     => $totalDiarias,
            'mp'                => $mp
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Erro ao recuperar dados do paciente',
        'detail'  => $e->getMessage()
    ]);
}
