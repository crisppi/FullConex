<?php
// alta_reverte.php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once "globals.php"; // fornece $conn (PDO)

header('Content-Type: application/json; charset=utf-8');

$altaIds = $_POST['ids'] ?? [];
if (!is_array($altaIds) || !$altaIds) {
    echo json_encode(['ok' => false, 'msg' => 'Nenhum ID informado.']);
    exit;
}
$altaIds = array_values(array_filter(array_map('intval', $altaIds), fn($v) => $v > 0));
if (!$altaIds) {
    echo json_encode(['ok' => false, 'msg' => 'IDs inválidos.']);
    exit;
}

try {
    $conn->beginTransaction();

    // 1) Pega os id_internacao vinculados às altas selecionadas
    $place = implode(',', array_fill(0, count($altaIds), '?'));
    $sqlGet = "SELECT fk_id_int_alt AS id_internacao
                 FROM tb_alta
                WHERE id_alta IN ($place)";
    $st = $conn->prepare($sqlGet);
    foreach ($altaIds as $i => $v) $st->bindValue($i + 1, $v, PDO::PARAM_INT);
    $st->execute();
    $idsInt = array_values(array_unique(array_map(
        'intval',
        array_column($st->fetchAll(PDO::FETCH_ASSOC), 'id_internacao')
    )));

    // 2) Apaga as altas na tb_alta
    $sqlDel = "DELETE FROM tb_alta WHERE id_alta IN ($place)";
    $stDel = $conn->prepare($sqlDel);
    foreach ($altaIds as $i => $v) $stDel->bindValue($i + 1, $v, PDO::PARAM_INT);
    $stDel->execute();
    $apagadas = $stDel->rowCount();

    // 3) Marca as internações como internadas novamente (internado_int='s')
    $atualizadas = 0;
    if ($idsInt) {
        $placeInt = implode(',', array_fill(0, count($idsInt), '?'));
        $sqlUp = "UPDATE tb_internacao
                     SET internado_int = 's',
                         updated_at    = NOW()
                   WHERE id_internacao IN ($placeInt)";
        $stUp = $conn->prepare($sqlUp);
        foreach ($idsInt as $i => $v) $stUp->bindValue($i + 1, $v, PDO::PARAM_INT);
        $stUp->execute();
        $atualizadas = $stUp->rowCount();
    }

    $conn->commit();
    echo json_encode([
        'ok'  => true,
        'msg' => "Altas apagadas: {$apagadas}. Internações marcadas como 's': {$atualizadas}."
    ]);
} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    if (!headers_sent()) http_response_code(500);
    error_log("[alta_reverte] " . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Erro ao reverter altas.']);
}