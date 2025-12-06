<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$ROOT = dirname(__DIR__);
chdir($ROOT);

require_once 'globals.php';
require_once 'db.php';
require_once 'models/internacao.php';
require_once 'dao/internacaoDao.php';

$response = ['success' => true, 'hasActive' => false];

try {
    $pacienteId = filter_input(INPUT_GET, 'id_paciente', FILTER_VALIDATE_INT);
    if (!$pacienteId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id_paciente obrigatório']);
        exit;
    }

    $internacaoDao = new internacaoDAO($conn, $BASE_URL);
    $idAtiva = $internacaoDao->checkInternAtiva($pacienteId);

    if ($idAtiva) {
        $stmt = $conn->prepare("SELECT ac.id_internacao, ac.data_intern_int, ac.hora_intern_int, ho.nome_hosp
            FROM tb_internacao ac
            LEFT JOIN tb_hospital ho ON ho.id_hospital = ac.fk_hospital_int
            WHERE ac.id_internacao = :id LIMIT 1");
        $stmt->bindValue(':id', $idAtiva, PDO::PARAM_INT);
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($info) {
            $dataFmt = null;
            if (!empty($info['data_intern_int']) && $info['data_intern_int'] !== '0000-00-00') {
                $dt = DateTime::createFromFormat('Y-m-d', $info['data_intern_int']) ?: new DateTime($info['data_intern_int']);
                if ($dt) {
                    $dataFmt = $dt->format('d/m/Y');
                }
            }

            $response['hasActive'] = true;
            $response['active'] = [
                'id_internacao'   => (int) $info['id_internacao'],
                'hospital'        => $info['nome_hosp'] ?? null,
                'data_internacao' => $info['data_intern_int'] ?? null,
                'hora'            => $info['hora_intern_int'] ?? null,
                'data_formatada'  => $dataFmt,
            ];
        }
    }

    echo json_encode($response);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Erro interno',
        'detail'  => $e->getMessage(),
    ]);
}
