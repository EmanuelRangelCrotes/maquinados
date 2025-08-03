<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['name']) || $_SESSION['rol'] !== 'user') {
    http_response_code(401); // No autorizado
    exit("Sesión expirada");
}

require_once './db_conexion.php';

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT s.id_solicitud, s.id_usuario, s.id_productos, s.cantidad, s.fecha_solicitud, s.estado,
               p.nombre AS nombre_producto, p.sku, p.existencia, u.name AS nombre_usuario
        FROM solicitudes s
        JOIN productos p ON s.id_productos = p.id_productos
        JOIN users u ON s.id_usuario = u.id_usuario
        WHERE s.estado = 'Pendiente' AND u.id_usuario = :id_usuario
        ORDER BY s.fecha_solicitud DESC";

$query = $cnnPDO->prepare($sql);
$query->bindParam(':id_usuario', $id_usuario);
$query->execute();
$pendientes = $query->fetchAll(PDO::FETCH_ASSOC);

if (empty($pendientes)) {
    echo '<h2 class="text-danger text-center">No hay solicitudes registradas.</h2>';
    exit;
}

foreach ($pendientes as $solicitud) {
    echo '<div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">';
    echo '<h3>Orden #' . $solicitud['id_solicitud'] . ' - ' . $solicitud['fecha_solicitud'] . ' (' . $solicitud['estado'] . ')</h3>';
    echo '<table class="table">';
    echo '<thead><tr><th>Producto</th><th>SKU</th><th>Cantidad</th></tr></thead>';
    echo '<tbody><tr>';
    echo '<td>' . htmlspecialchars($solicitud['nombre_producto']) . '</td>';
    echo '<td>' . htmlspecialchars($solicitud['sku']) . '</td>';
    echo '<td>' . htmlspecialchars($solicitud['cantidad']) . '</td>';
    echo '</tr></tbody></table></div>';
}
?>
