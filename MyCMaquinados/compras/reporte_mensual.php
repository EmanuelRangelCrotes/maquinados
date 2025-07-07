<?php

require_once 'db_conexion.php';
session_start();

if (!isset($_SESSION['name']) || $_SESSION['rol'] != 'compras') {
    header('Location: ../login.php');
    exit();
}

// Establecer idioma español para nombres de mes
$cnnPDO->query("SET lc_time_names = 'es_ES'");

// Consulta para obtener reporte mensual de material surtido
$sql = "
    SELECT 
        DATE_FORMAT(sm.fecha_surtido, '%Y-%m') AS mes,
        DATE_FORMAT(sm.fecha_surtido, '%M %Y') AS mes_texto,
        p.id_productos,
        p.nombre AS producto,
        p.precio AS precio_unitario,
        SUM(sm.cantidad_surtida) AS cantidad_total,
        SUM(sm.cantidad_surtida * p.precio) AS costo_total
    FROM solicitar_material sm
    JOIN productos p ON sm.id_productos = p.id_productos
    WHERE sm.estatus = 'Surtido'
      AND sm.fecha_surtido IS NOT NULL
    GROUP BY mes, p.id_productos
    ORDER BY mes DESC, producto ASC
";

$reporte = $cnnPDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <h1 class="navbar-brand">Compras</h1>
            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="pedidos_pendientes.php">Pedidos de Almacen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="material_surtido.php">Material Surtido</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reporte_mensual.php">Reporte mensual</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
            </div>
        </div>
    </nav>
</body>

<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual de Material Surtido</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Reporte Mensual de Material Surtido</h2>

    <?php if (empty($reporte)): ?>
        <div class="alert alert-info">No hay datos de surtido registrados.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Mes</th>
                    <th>ID Producto</th>
                    <th>Producto</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad Total</th>
                    <th>Costo Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $mes_actual = '';
                foreach ($reporte as $fila):
                    if ($fila['mes'] !== $mes_actual):
                        $mes_actual = $fila['mes'];
                ?>
                    <tr class="table-primary fw-bold">
                        <td colspan="6"><?= mb_strtoupper(htmlspecialchars($fila['mes_texto']), 'UTF-8') ?></td>
                    </tr>
                <?php endif; ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['mes']) ?></td>
                        <td><?= htmlspecialchars($fila['id_productos']) ?></td>
                        <td><?= htmlspecialchars($fila['producto']) ?></td>
                        <td>$<?= number_format($fila['precio_unitario'], 2) ?></td>
                        <td><?= htmlspecialchars($fila['cantidad_total']) ?></td>
                        <td>$<?= number_format($fila['costo_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>

</html>