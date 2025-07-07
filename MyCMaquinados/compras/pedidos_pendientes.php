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
WHERE pt.estatus = 'pendiente'
ORDER BY pt.fecha DESC";
$query_pendientes = $cnnPDO->prepare($sql_pendientes);
$query_pendientes->execute();
$pendientes = $query_pendientes->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedidos'], $_POST['accion'])) {
    $id_pedidos = (int)$_POST['id_pedidos'];
    $accion = $_POST['accion'];
    if ($accion === 'aceptar') {
        $nuevo_estatus = 'Aceptado';
        // Actualiza el estatus del pedido
        $sql_update = "UPDATE pedidos_taller SET estatus = ? WHERE id_pedidos = ?";
        $query_update = $cnnPDO->prepare($sql_update);
        $query_update->execute([$nuevo_estatus, $id_pedidos]);

        // Obtén los datos del pedido aceptado
        $sql_select = "SELECT cantidad, fecha, id_productos FROM pedidos_taller WHERE id_pedidos = ?";
        $query_select = $cnnPDO->prepare($sql_select);
        $query_select->execute([$id_pedidos]);
        $pedido = $query_select->fetch(PDO::FETCH_ASSOC);

        if ($pedido) {
            // Inserta en solicitar_material
            $sql_insert = "INSERT INTO solicitar_material (id_pedidos, id_productos, cantidad, cantidad_surtida, estatus, fecha)
                           VALUES (?, ?, ?, 0, 'Pendiente', ?)";
            $query_insert = $cnnPDO->prepare($sql_insert);
            $query_insert->execute([
                $id_pedidos,
                $pedido['id_productos'],
                $pedido['cantidad'],
                $pedido['fecha']
            ]);
        };

        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Pedido aceptado y solicitud creada correctamente.'
        ];
        header("Location: pedidos_pendientes.php");
        exit();
    } elseif ($accion === 'rechazar') {
        $nuevo_estatus = 'Rechazado';
        // Actualiza el estatus del pedido
        $sql_update = "UPDATE pedidos_taller SET estatus = ? WHERE id_pedidos = ?";
        $query_update = $cnnPDO->prepare($sql_update);
        $query_update->execute([$nuevo_estatus, $id_pedidos]);

        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Pedido rechazado correctamente.'
        ];
        header("Location: pedidos_pendientes.php");
        exit();
    };
}
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
<h2 style="text-align: center;">Pedidos Pendientes</h2>
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
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Cantidad</th>
                        <th>Existencia</th>
                        <th>Acciones</th>
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
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id_pedidos" value="<?= $solicitud['id_pedidos'] ?>">
                                <button type="submit" name="accion" value="aceptar" class="btn btn-success btn-sm">Aceptar</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id_pedidos" value="<?= $solicitud['id_pedidos'] ?>">
                                <button type="submit" name="accion" value="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>

</html>