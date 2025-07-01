<?php
require_once 'db_conexion.php';
session_start();

// Validación de sesión
if (!isset($_SESSION['name']) || $_SESSION['rol'] != 'compras') {
    header('Location: ../login.php');
    exit();
}

// Consulta para obtener productos surtidos con su precio total
$sql = "SELECT 
            p.nombre AS nombre_producto,
            p.precio_unitario,
            SUM(sm.cantidad_surtida) AS total_surtido,
            (SUM(sm.cantidad_surtida) * p.precio_unitario) AS total_comprado
        FROM solicitar_material sm
        JOIN productos p ON sm.id_productos = p.id_productos
        WHERE sm.estatus = 'Surtido'
        GROUP BY p.id_productos, p.nombre, p.precio_unitario
        ORDER BY total_comprado DESC";

$reporte = $cnnPDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Compras</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Reporte de Compras</h2>
    <?php if (empty($reporte)): ?>
        <div class="alert alert-info text-center">No hay productos surtidos para mostrar.</div>
    <?php else: ?>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Precio Unitario</th>
                    <th>Total Surtido</th>
                    <th>Total Comprado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte as $fila): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                        <td>$<?= number_format($fila['precio_unitario'], 2) ?></td>
                        <td><?= $fila['total_surtido'] ?></td>
                        <td>$<?= number_format($fila['total_comprado'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
