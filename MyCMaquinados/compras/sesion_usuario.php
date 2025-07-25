<?php
require_once './db_conexion.php';
session_start();

// Tiempo límite de inactividad (30 minutos)
$tiempo_limite = 1800;
if (isset($_SESSION['ultimo_acceso'])) {
    $inactividad = time() - $_SESSION['ultimo_acceso'];
    if ($inactividad > $tiempo_limite) {
        session_unset(); // Eliminar datos de la sesión
        session_destroy(); // Destruir la sesión
        header('Location: ../login.php');
        exit();
    }
}
$_SESSION['ultimo_acceso'] = time(); // Actualizar el tiempo de acceso

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: ../login.php');
    exit();
}

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];

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
    <script>
function cargarSolicitudes() {
    fetch('get_pedidos_compras.php')
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
