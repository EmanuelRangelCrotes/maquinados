<?php
require_once './templates/header.php';
require_once './db_conexion.php';
session_start();

if (!isset($_SESSION['name']) || $_SESSION['rol'] != 'compras') {
    header('Location: ../login.php');
    exit();
}
// Establecer idioma español para nombres de mes
$cnnPDO->query("SET lc_time_names = 'es_ES'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mes_seleccionado = intval($_POST['mes']);

    $sql_filtro = "
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
  AND MONTH(fecha_surtido) = $mes_seleccionado
GROUP BY mes, p.id_productos, mes_texto, p.nombre, ultimo_precio.old_price
HAVING precio_unitario > 0  -- Filtro adicional de seguridad
ORDER BY mes DESC, producto ASC
";
    $query_filtro = $cnnPDO->query($sql_filtro)->fetchAll(PDO::FETCH_ASSOC);
}

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
  AND MONTH(fecha_surtido) = MONTH(CURDATE())
GROUP BY mes, p.id_productos, mes_texto, p.nombre, ultimo_precio.old_price
HAVING precio_unitario > 0  -- Filtro adicional de seguridad
ORDER BY mes DESC, producto ASC;
";
$reporte_anterior = $cnnPDO->query($sql_before)->fetchAll(PDO::FETCH_ASSOC);

// Si viene un filtro, usa esa consulta, si no usa la del mes actual
$datos = !empty($query_filtro) ? $query_filtro : $reporte_anterior;

?>

<body>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown" aria-expanded="false">
            Reportes
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="./reporte_mensual.php">
                <h6>Reporte Mensual</h6>
            </a>
            <a class="dropdown-item" href="./reporte_bitacora.php">
                <h6>Bitacora de Reportes</h6>
            </a>
            <div class="dropdown-divider"></div>
        </div>
    </div>
    <div class="container mt-5">
        <div class="container mt-5">
            <h2 class="text-center mb-4">Reporte Mensual de Material Surtido con Precios Anteriores</h2>
            <div class="dropdown">
                <form action="" method="post">
                    <select class="btn btn-secondary dropdown-toggle" type="button" name="mes" id="mes">
                        <label for="mes">Selecciona el mes</label>
                        <option value="1">Enero</option>
                        <option value="2">Febreo</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                    <input type="submit" value="filtrar" class="btn btn-primary">
                </form>

            </div>
            <?php if (empty($datos)): ?>
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
                        foreach ($datos as $fila):
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