<?php
session_start(); // ✅ Solo esta

// Verifica si el usuario está logueado
if (!isset($_SESSION['name']) || $_SESSION['rol'] !== 'user') {
    // Evita el uso del historial del navegador para acceder después de cerrar sesión
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
    header("Pragma: no-cache"); // HTTP 1.0.
    header("Expires: 0"); // Proxies.
    
    header("Location: ../login.php");
    exit();
}

require_once './templates/header.php';
require_once './db_conexion.php';

// Ya no pongas otro session_start aquí ❌


$id_usuario = $_SESSION['id_usuario'];
$name = $_SESSION['name'];


$sql_pendientes = "SELECT s.id_solicitud, s.id_usuario, s.id_productos, s.cantidad, s.fecha_solicitud, s.estado,
                        p.nombre AS nombre_producto, p.sku, p.existencia, u.name AS nombre_usuario
                  FROM solicitudes s
                  JOIN productos p ON s.id_productos = p.id_productos
                  JOIN users u ON s.id_usuario = u.id_usuario
                  WHERE s.estado = 'Pendiente' AND u.id_usuario = :id_usuario
                  ORDER BY s.fecha_solicitud DESC";
$query_pendientes = $cnnPDO->prepare($sql_pendientes);
$query_pendientes->bindParam(':id_usuario', $id_usuario);
$query_pendientes->execute();
$pendientes = $query_pendientes->fetchAll(PDO::FETCH_ASSOC);

?>



<body>
   
    <br>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown" aria-expanded="false">
            Solicitudes
        </button>
        <div class="dropdown-menu" aria-labelledby="triggerId">
            <a class="dropdown-item" href="pedidos_user.php">
                <h6>Pendientes</h6>
            </a>
            <a class="dropdown-item " href="pedidos_aceptados_user.php">
                <h6>Aceptadas</h6>
            </a>
            <a class="dropdown-item" href="pedidos_rechazados_user.php">
                <h6>Rechazadas</h6>
            </a>
            <div class="dropdown-divider"></div>
        </div>
    </div>
    <br>
    <h2 style="text-align: center;">Mis Pedidos Pendientes</h2>
    <br>
    <?php if (empty($pendientes)): ?>
        <h2 class="text-danger text-center">No hay solicitudes registradas.</h2>
    <?php else: ?>
        <?php foreach ($pendientes as $solicitud): ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
                <h3>Orden #<?= $solicitud['id_solicitud'] ?> - <?= $solicitud['fecha_solicitud'] ?> (<?= $solicitud['estado'] ?>)</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $solicitud['nombre_producto'] ?></td>
                            <td><?= $solicitud['sku'] ?></td>
                            <td><?= $solicitud['cantidad'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <script>
function cargarSolicitudes() {
    fetch('get_pedidos_user.php')
        .then(response => {
            if (response.status === 401) {
                // Redirigir si sesión expira
                window.location.href = '../login.php';
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('contenedor-solicitudes').innerHTML = html;
        })
        .catch(error => {
            console.error('Error al cargar solicitudes:', error);
            document.getElementById('contenedor-solicitudes').innerHTML = '<p class="text-danger">Error al cargar las solicitudes.</p>';
        });
}

// Cargar inmediatamente al abrir la página
cargarSolicitudes();

// Refrescar cada 30 segundos automáticamente
setInterval(cargarSolicitudes, 30000);
</script>

</body>

</html>
