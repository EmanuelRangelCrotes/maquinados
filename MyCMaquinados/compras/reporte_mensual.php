<?php
include_once 'templates/header.php';
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
        sm.precio_unitario,
        SUM(sm.cantidad_surtida) AS cantidad_total,
        SUM(sm.cantidad_surtida * sm.precio_unitario) AS costo_total
    FROM solicitar_material sm
    JOIN productos p ON sm.id_productos = p.id_productos
    WHERE sm.estatus = 'Surtido'
      AND sm.fecha_surtido IS NOT NULL
    GROUP BY mes, p.id_productos
    ORDER BY mes DESC, producto ASC
";
$reporte = $cnnPDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);


// Consulta para obtener reporte mensual de material surtido
$sql_before = "
SELECT 
    DATE_FORMAT(sm.fecha_surtido, '%Y-%m') AS mes,
    DATE_FORMAT(sm.fecha_surtido, '%M %Y') AS mes_texto,
    p.id_productos,
    p.nombre AS producto,
    ultimo_precio.old_price AS precio_unitario,
    SUM(sm.cantidad_surtida) AS cantidad_total,
    SUM(sm.cantidad_surtida * ultimo_precio.old_price) AS costo_total
FROM solicitar_material sm
JOIN productos p ON sm.id_productos = p.id_productos
JOIN (
    SELECT 
        ph1.id_productos,
        ph1.old_price
    FROM precio_productos_historial ph1
    JOIN (
        SELECT 
            id_productos, 
            MAX(fecha) AS max_fecha
        FROM precio_productos_historial
        GROUP BY id_productos
    ) ph2 ON ph1.id_productos = ph2.id_productos AND ph1.fecha = ph2.max_fecha
    WHERE ph1.old_price > 0  -- Excluye precios en cero
) ultimo_precio ON p.id_productos = ultimo_precio.id_productos
WHERE sm.estatus = 'Surtido'
  AND sm.fecha_surtido IS NOT NULL
GROUP BY mes, p.id_productos, mes_texto, p.nombre, ultimo_precio.old_price
HAVING precio_unitario > 0  -- Filtro adicional de seguridad
ORDER BY mes DESC, producto ASC;
";
$reporte_anterior = $cnnPDO->query($sql_before)->fetchAll(PDO::FETCH_ASSOC);
?>



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


    <div class="container mt-5">
        <h2 class="text-center mb-4">Reporte Mensual de Material Surtido con Precios Anteriores</h2>

        <?php if (empty($reporte_anterior)): ?>
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
                    foreach ($reporte_anterior as $fila):
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
    <?php include_once 'templates/footer.php'; ?>