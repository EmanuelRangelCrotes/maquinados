<?php
require_once './db_conexion.php';

header('Content-Type: application/json');

// Debug: Registrar la solicitud recibida
error_log("Solicitud de búsqueda recibida: " . print_r($_GET, true));

$searchTerm = isset($_GET['q']) ? '%' . trim($_GET['q']) . '%' : '%%';

try {
    $sql = "SELECT nombre AS text FROM clase WHERE nombre LIKE ? ORDER BY nombre LIMIT 10";
    $stmt = $cnnPDO->prepare($sql);
    $stmt->execute([$searchTerm]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Registrar los resultados
    error_log("Resultados encontrados: " . print_r($results, true));

    echo json_encode($results);
} catch (PDOException $e) {
    // Debug: Registrar errores
    error_log("Error en buscar_clase.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
