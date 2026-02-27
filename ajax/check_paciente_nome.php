<?php
require_once(__DIR__ . '/../globals.php');
require_once(__DIR__ . '/../db.php');

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
    exit;
}

$nome = trim((string)($_POST['nome_pac'] ?? ''));
if ($nome === '') {
    echo json_encode(['success' => true, 'matches' => []]);
    exit;
}
$nome = preg_replace('/\s+/', ' ', $nome);
$tokens = array_values(array_filter(explode(' ', $nome), function ($t) {
    return mb_strlen(trim((string)$t), 'UTF-8') >= 3;
}));
if (count($tokens) > 4) {
    $tokens = array_slice($tokens, 0, 4);
}

function onlyDigits($v)
{
    return preg_replace('/\D+/', '', (string)$v);
}

function formatCpf($cpf)
{
    $d = onlyDigits($cpf);
    if (strlen($d) !== 11) return '';
    return substr($d, 0, 3) . '.' . substr($d, 3, 3) . '.' . substr($d, 6, 3) . '-' . substr($d, 9, 2);
}

try {
    $nomeLike = '%' . str_replace(' ', '%', $nome) . '%';
    $tokenClause = '';
    if (!empty($tokens)) {
        $tokenParts = [];
        foreach ($tokens as $idx => $tk) {
            $tokenParts[] = "UPPER(pa.nome_pac) LIKE UPPER(:tk{$idx})";
        }
        $tokenClause = '(' . implode(' AND ', $tokenParts) . ')';
    }

    $whereNome = "(UPPER(TRIM(pa.nome_pac)) = UPPER(TRIM(:nome)) OR UPPER(pa.nome_pac) LIKE UPPER(:nome_like))";
    if ($tokenClause !== '') {
        $whereNome = '(' . $whereNome . ' OR ' . $tokenClause . ')';
    }

    $sql = "SELECT pa.id_paciente, pa.nome_pac, pa.matricula_pac, pa.cpf_pac, pa.data_nasc_pac, se.seguradora_seg
              FROM tb_paciente pa
         LEFT JOIN tb_seguradora se ON se.id_seguradora = pa.fk_seguradora_pac
             WHERE {$whereNome}
               AND IFNULL(pa.deletado_pac, 'n') <> 's'
          ORDER BY pa.id_paciente DESC
             LIMIT 15";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':nome_like', $nomeLike);
    foreach ($tokens as $idx => $tk) {
        $stmt->bindValue(":tk{$idx}", '%' . $tk . '%');
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($rows as &$r) {
        $r['cpf_pac_formatado'] = formatCpf($r['cpf_pac'] ?? '');
    }
    unset($r);

    echo json_encode(['success' => true, 'matches' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'query_failed']);
}
