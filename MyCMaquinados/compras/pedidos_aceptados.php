<?php
require_once './db_conexion.php';
session_start();
$id_usuario = $_SESSION['id_usuario'];
$name = $_SESSION['name'];

$sql_pendientes = "SELECT pt.id_pedidos, pt.cantidad, pt.fecha, pt.estatus, pt.id_usuario, 
       p.id_productos, p.nombre, p.sku, p.clase, p.descripcion, p.existencia,
       u.name AS nombre_usuario
FROM pedidos_taller pt
JOIN productos p ON pt.id_productos = p.id_productos
JOIN users u ON pt.id_usuario = u.id_usuario
WHERE pt.estatus = 'aceptado'
ORDER BY pt.fecha DESC";
$query_pendientes = $cnnPDO->prepare($sql_pendientes);
$query_pendientes->execute();
$pendientes = $query_pendientes->fetchAll(PDO::FETCH_ASSOC);


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
<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <div class="container-fluid">
        <h1 class="navbar-brand">Compras</h1>
        <div class="collapse navbar-collapse" id="navbarColor01">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="./sesion_usuario.php">Pagina Principal</a>
                </li>
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
<div class="dropdown d-inline me-2">
    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        Pedidos
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <li><a class="dropdown-item" href="pedidos_pendientes.php">Pedidos Pendientes</a></li>
        <li><a class="dropdown-item" href="pedidos_aceptados.php">Pedidos Aceptados</a></li>
        <li><a class="dropdown-item" href="pedidos_rechazados.php">Pedidos Rechazados</a></li>
    </ul>
</div>
<h2 style="text-align: center;">Pedidos Aceptados</h2>
<br>
<?php if (empty($pendientes)): ?>
    <h2 class="text-danger text-center">No hay pedidos aceptados.</h2>
<?php else: ?>
    <?php foreach ($pendientes as $solicitud): ?>

        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <h3>Orden #<?= $solicitud['id_pedidos'] ?> - <?= $solicitud['fecha'] ?> (<?= $solicitud['estatus'] ?>)</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Cantidad</th>
                        <th>Existencia</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= $solicitud['id_pedidos'] ?></td>
                        <td><?= $solicitud['nombre_usuario'] ?></td>
                        <td><?= $solicitud['nombre'] ?></td>
                        <td><?= $solicitud['sku'] ?></td>
                        <td><?= $solicitud['cantidad'] ?></td>
                        <td><?= $solicitud['existencia'] ?></td>

                    </tr>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>

</html>