// Critica 001 - verifica quais códigos TUSS já foram liberados para um determinado paciente dentro da mesma internação

<?php
// Parâmetros recebidos
$id_paciente = $_GET['id_paciente'];
$id_internacao = $_GET['id_internacao'];

$sql = "
    SELECT DISTINCT codigo_tuss
    FROM procedimentos_liberados
    WHERE id_paciente = :id_paciente
      AND id_internacao = :id_internacao
";

// Preparar e executar
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
$stmt->bindParam(':id_internacao', $id_internacao, PDO::PARAM_INT);
$stmt->execute();

// Obter os resultados
$codigos_liberados = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Exibir ou usar como quiser
foreach ($codigos_liberados as $codigo) {
    echo "Código TUSS liberado: " . htmlspecialchars($codigo) . "<br>";
}
?>