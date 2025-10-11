<?php
// export_visitas.php
include_once __DIR__ . "/db.php";
header("Content-type: text/html; charset=utf-8");

// ==== campos ====
$fieldsMap = [
    'nome_paciente'   => ['label' => 'Nome do paciente',   'sql' => 'p.nome AS nome_paciente'],
    'data_internacao' => ['label' => 'Data de internação', 'sql' => 'i.data_internacao'],
    'hospital'        => ['label' => 'Hospital',           'sql' => 'h.nome_hospital AS hospital'],
    'acomodacao'      => ['label' => 'Acomodação',         'sql' => 'a.descricao AS acomodacao'],
    'data_visita'     => ['label' => 'Data da visita',     'sql' => 'v.data_visita'],
    'medico_visita'   => ['label' => 'Médico (visita)',    'sql' => 'pr.nome_profissional AS medico_visita'],
];

$selected = isset($_GET['fields']) && is_array($_GET['fields'])
    ? array_values(array_intersect(array_keys($fieldsMap), $_GET['fields']))
    : array_keys($fieldsMap);
if (empty($selected)) $selected = array_keys($fieldsMap);

$select = implode(", ", array_map(fn($k) => $fieldsMap[$k]['sql'], $selected));

// filtros
$nomePaciente = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$hospitalId   = isset($_GET['hospital_id']) ? trim($_GET['hospital_id']) : '';
$dtIni        = isset($_GET['dt_ini']) ? trim($_GET['dt_ini']) : '';
$dtFim        = isset($_GET['dt_fim']) ? trim($_GET['dt_fim']) : '';

$sql = "
SELECT $select
FROM tb_internacao i
JOIN tb_paciente     p  ON p.id_paciente      = i.fk_paciente
LEFT JOIN tb_hospital h ON h.id_hospital      = i.fk_hospital
LEFT JOIN tb_acomodacao a ON a.id_acomodacao  = i.fk_acomodacao
LEFT JOIN tb_visita   v  ON v.fk_internacao   = i.id_internacao
LEFT JOIN tb_profissional pr ON pr.id_profissional = v.fk_profissional
WHERE 1=1
";

$params = [];
if ($nomePaciente !== '') {
    $sql .= " AND p.nome LIKE :nome ";
    $params[':nome'] = "%$nomePaciente%";
}
if ($hospitalId !== '') {
    $sql .= " AND i.fk_hospital = :hid ";
    $params[':hid'] = $hospitalId;
}
if ($dtIni !== '') {
    $sql .= " AND DATE(i.data_internacao) >= :dtini ";
    $params[':dtini'] = $dtIni;
}
if ($dtFim !== '') {
    $sql .= " AND DATE(i.data_internacao) <= :dtfim ";
    $params[':dtfim'] = $dtFim;
}

$sql .= " ORDER BY COALESCE(v.data_visita, i.data_internacao) DESC, p.nome ASC ";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

// ==== cabeçalhos para download CSV (Excel-friendly) ====
$fname = "visitas_" . date("Ymd_His") . ".csv";
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $fname . '"');
// BOM para Excel reconhecer UTF-8
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');

// cabeçalho
$header = array_map(fn($k) => $fieldsMap[$k]['label'], $selected);
// usa ; como separador (pt-BR)
fputcsv($out, $header, ';');

// linhas
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $line = [];
    foreach ($selected as $k) {
        $val = $row[$k] ?? '';
        if (in_array($k, ['data_internacao', 'data_visita'], true) && !empty($val)) {
            $ts = strtotime($val);
            $val = $ts ? date('d/m/Y', $ts) : $val;
        }
        $line[] = $val;
    }
    fputcsv($out, $line, ';');
}
fclose($out);
exit;