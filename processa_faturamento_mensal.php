<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['email_user']) || ($_SESSION['ativo'] ?? 'n') !== 's') {
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}
require_once __DIR__ . '/globals.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);
if (!is_array($data)) {
    $data = $_POST;
}

$ids = $data['ids'] ?? [];
if (!is_array($ids)) {
    echo json_encode(['success' => false, 'message' => 'Nenhuma visita informada.']);
    exit;
}

$ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($v) => $v > 0)));
if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Selecione ao menos uma visita.']);
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "
        UPDATE tb_visita
           SET faturado_vis = 's',
               data_faturamento_vis = NOW()
         WHERE id_visita IN ($placeholders)
    ";
    $stmt = $conn->prepare($sql);
    foreach ($ids as $idx => $id) {
        $stmt->bindValue($idx + 1, $id, PDO::PARAM_INT);
    }
    $stmt->execute();
    $affected = (int)$stmt->rowCount();
    if ($affected === 0) {
        echo json_encode(['success' => false, 'message' => 'Nenhuma visita foi atualizada.']);
        exit;
    }
    echo json_encode(['success' => true, 'message' => $affected . ' visita(s) marcada(s) como faturadas.']);
} catch (Throwable $e) {
    error_log('Erro ao faturar visitas: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Falha ao atualizar as visitas selecionadas.']);
}
