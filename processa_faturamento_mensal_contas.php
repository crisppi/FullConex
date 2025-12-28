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
    echo json_encode(['success' => false, 'message' => 'Nenhuma conta informada.']);
    exit;
}

$ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($v) => $v > 0)));
if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Selecione ao menos uma conta.']);
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sqlSenhas = "
        SELECT ca.id_capeante, i.senha_int
        FROM tb_capeante ca
        JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
        WHERE ca.id_capeante IN ($placeholders)
    ";
    $stmtSenhas = $conn->prepare($sqlSenhas);
    foreach ($ids as $idx => $id) {
        $stmtSenhas->bindValue($idx + 1, $id, PDO::PARAM_INT);
    }
    $stmtSenhas->execute();
    $rows = $stmtSenhas->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        echo json_encode(['success' => false, 'message' => 'Nenhuma conta válida encontrada.']);
        exit;
    }

    $bySenha = [];
    foreach ($rows as $r) {
        $senha = trim((string)($r['senha_int'] ?? ''));
        if ($senha === '') {
            continue;
        }
        $bySenha[$senha][] = (int)$r['id_capeante'];
    }
    $dupSenhas = array_keys(array_filter($bySenha, fn($list) => count($list) > 1));
    if ($dupSenhas) {
        echo json_encode([
            'success' => false,
            'message' => 'Selecione apenas 1 conta por senha. Duplicadas: ' . implode(', ', $dupSenhas) . '.'
        ]);
        exit;
    }

    $senhas = array_keys($bySenha);
    if ($senhas) {
        $placeholdersSenhas = implode(',', array_fill(0, count($senhas), '?'));
        $sqlBloq = "
            SELECT DISTINCT i.senha_int
            FROM tb_capeante ca
            JOIN tb_internacao i ON i.id_internacao = ca.fk_int_capeante
            WHERE i.senha_int IN ($placeholdersSenhas)
              AND LOWER(COALESCE(ca.conta_faturada_cap, '')) = 's'
              AND ca.id_capeante NOT IN ($placeholders)
        ";
        $stmtBloq = $conn->prepare($sqlBloq);
        $p = 1;
        foreach ($senhas as $senha) {
            $stmtBloq->bindValue($p++, $senha);
        }
        foreach ($ids as $id) {
            $stmtBloq->bindValue($p++, $id, PDO::PARAM_INT);
        }
        $stmtBloq->execute();
        $bloqueadas = $stmtBloq->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($bloqueadas)) {
            echo json_encode([
                'success' => false,
                'message' => 'Esta senha já foi faturada: ' . implode(', ', $bloqueadas) . '.'
            ]);
            exit;
        }
    }

    $sql = "
        UPDATE tb_capeante
           SET conta_faturada_cap = 's',
               conta_fatura_cap   = 's'
         WHERE id_capeante IN ($placeholders)
    ";
    $stmt = $conn->prepare($sql);
    foreach ($ids as $idx => $id) {
        $stmt->bindValue($idx + 1, $id, PDO::PARAM_INT);
    }
    $stmt->execute();
    $affected = (int)$stmt->rowCount();
    if ($affected === 0) {
        echo json_encode(['success' => false, 'message' => 'Nenhuma conta foi atualizada.']);
        exit;
    }
    echo json_encode(['success' => true, 'message' => $affected . ' conta(s) marcada(s) como faturadas.']);
} catch (Throwable $e) {
    error_log('Erro ao faturar contas mensais: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Falha ao atualizar as contas selecionadas.']);
}
