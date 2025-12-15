<?php
// ajax/hospital_insights.php
header('Content-Type: application/json; charset=utf-8');
session_start();

$ROOT = dirname(__DIR__);
chdir($ROOT);

require_once 'globals.php';
require_once 'db.php';

try {
    $hospitalId = filter_input(INPUT_GET, 'id_hospital', FILTER_VALIDATE_INT);
    if (!$hospitalId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'id_hospital obrigatório'
        ]);
        exit;
    }

    $threshold = 5;

    $stmtNeg = $conn->prepare("
        SELECT COUNT(*) 
          FROM tb_negociacao ng
          INNER JOIN tb_internacao ac ON ng.fk_id_int = ac.id_internacao
         WHERE ac.fk_hospital_int = :hospId
    ");
    $stmtNeg->bindValue(':hospId', $hospitalId, PDO::PARAM_INT);
    $stmtNeg->execute();
    $totalNegociacoes = (int) $stmtNeg->fetchColumn();

    $stmtTotal = $conn->prepare("
        SELECT COUNT(*) 
          FROM tb_internacao ac
         WHERE ac.fk_hospital_int = :hospId
    ");
    $stmtTotal->bindValue(':hospId', $hospitalId, PDO::PARAM_INT);
    $stmtTotal->execute();
    $totalInternacoes = (int) $stmtTotal->fetchColumn();

    $stmtUti = $conn->prepare("
        SELECT COUNT(DISTINCT ac.id_internacao)
          FROM tb_internacao ac
          LEFT JOIN tb_uti ut ON ut.fk_internacao_uti = ac.id_internacao
         WHERE ac.fk_hospital_int = :hospId
           AND (
                ac.internado_uti_int = 's'
             OR ac.internacao_uti_int = 's'
             OR ut.internado_uti = 's'
             OR ut.internacao_uti = 's'
           )
    ");
    $stmtUti->bindValue(':hospId', $hospitalId, PDO::PARAM_INT);
    $stmtUti->execute();
    $pacientesUti = (int) $stmtUti->fetchColumn();

    $percentUti = $totalInternacoes > 0
        ? round(($pacientesUti / $totalInternacoes) * 100, 1)
        : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'negociacoes'       => $totalNegociacoes,
            'total_internacoes' => $totalInternacoes,
            'inter_uti'         => $pacientesUti,
            'percent_uti'       => $percentUti,
            'threshold'         => $threshold,
            'uti_alert'         => $pacientesUti >= $threshold
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Erro ao recuperar insights',
        'detail'  => $e->getMessage()
    ]);
}
