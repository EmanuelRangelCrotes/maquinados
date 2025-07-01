<?php
require_once './db_conexion.php';

header('Content-Type: application/json');

$searchTerm = isset($_GET['q']) ? '%' . trim($_GET['q']) . '%' : '%%';

try {
    // Consulta a la tabla descripcion
    $sql = "SELECT nombre AS text FROM descripcion WHERE nombre LIKE ? ORDER BY nombre LIMIT 10";
    $stmt = $cnnPDO->prepare($sql);
    $stmt->execute([$searchTerm]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug (opcional)
    error_log("Búsqueda descripción: " . $_GET['q'] . " - Resultados: " . count($results));

    echo json_encode($results);
} catch (PDOException $e) {
    error_log("Error en buscar_descripcion.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
