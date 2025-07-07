<?php
include_once './templates/header.php';
require_once './db_conexion.php';
session_start();
$id_usuario = $_SESSION['id_usuario'];
$name = $_SESSION['name'];

$sql_pendientes = "SELECT pt.id_pedidos, pt.id_usuario, pt.id_productos, pt.cantidad, pt.fecha, pt.estatus, p.nombre , p.sku, p.existencia
                   FROM pedidos_taller pt
                   JOIN productos p ON pt.id_productos = p.id_productos
                   JOIN users u ON pt.id_usuario = u.id_usuario
                   WHERE pt.estatus = 'Rechazado' AND u.id_usuario = :id_usuario
                   ORDER BY fecha DESC";
$query_pendientes = $cnnPDO->prepare($sql_pendientes);
$query_pendientes->bindParam(':id_usuario', $id_usuario);
$query_pendientes->execute();
$pendientes = $query_pendientes->fetchAll(PDO::FETCH_ASSOC);

?>
 <div class="dropdown d-inline me-2">
            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                Pedidos
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item" href="mis_pedidos_pendientes.php">Pedidos Pendientes</a></li>
                <li><a class="dropdown-item" href="mis_pedidos_aceptados.php">Pedidos Aceptados</a></li>
                <li><a class="dropdown-item" href="mis_pedidos_rechazados.php">Pedidos Rechazados</a></li>
            </ul>
        </div>
<h2 style="text-align: center;">Mis Pedidos Rechazados</h2>
<br>
<?php if (empty($pendientes)): ?>
    <h2 class="text-danger text-center">No hay solicitudes registradas.</h2>
<?php else: ?>
    <?php foreach ($pendientes as $solicitud): ?>
       
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <h3>Orden #<?= $solicitud['id_pedidos'] ?> - <?= $solicitud['fecha'] ?> (<?= $solicitud['estatus'] ?>)</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Cantidad</th>
                        <th>Existencia</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
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

<?php include_once './templates/footer.php'; ?>