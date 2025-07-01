<?php
require_once 'db_conexion.php';
session_start();

if (!isset($_SESSION['name']) || $_SESSION['rol'] != 'compras') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Obtener parámetros
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;
$filtro = $_POST['filtro'] ?? 'todos';

// Validar fechas
if (!$startDate || !$endDate) {
    echo json_encode(['error' => 'Fechas no proporcionadas']);
    exit();
}

// Construir consulta SQL
$filtro_sql = match ($filtro) {
    'pendientes' => "AND sm.estatus = 'Pendiente'",
    'parciales' => "AND sm.estatus = 'Parcial'",
    'surtidos' => "AND sm.estatus = 'Surtido'",
    default => ""
};

$sql = "SELECT sm.*, p.nombre, p.sku, p.clase
        FROM solicitar_material sm
        JOIN productos p ON sm.id_productos = p.id_productos
        WHERE sm.fecha BETWEEN :start_date AND :end_date
        $filtro_sql
        ORDER BY 
            CASE sm.estatus 
                WHEN 'Pendiente' THEN 1
                WHEN 'Parcial' THEN 2
                ELSE 3
            END,
            sm.fecha DESC";

try {
    $stmt = $cnnPDO->prepare($sql);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();
    
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => $solicitudes]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>