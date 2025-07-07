<?php include_once './templates/header.php';

require_once './db_conexion.php';
session_start();

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];

// Validar si el formulario fue enviado
if (isset($_POST['agregar'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $id_pedidos = isset($_POST['id_pedidos']) ? htmlspecialchars(trim($_POST['id_pedidos']), ENT_QUOTES, 'UTF-8') : '';
    $cantidad = isset($_POST['cantidad']) ? htmlspecialchars(trim($_POST['cantidad']), ENT_QUOTES, 'UTF-8') : '';
    $fecha = date('Y-m-d'); // Fecha actual
    $id_productos = isset($_POST['id_productos']) ? htmlspecialchars(trim($_POST['id_productos']), ENT_QUOTES, 'UTF-8') : '';
    $usuario_id = $_SESSION['id_usuario'];


    // Validar que los campos no estén vacíos
    if (
        empty($id_productos) ||
        empty($cantidad)
    ) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: solicitar_material.php");
        exit();
    }

    try {
        // Insertar el producto en la base de datos
        $sql = "INSERT INTO pedidos_taller (id_pedidos,cantidad, fecha, id_productos, id_usuario) VALUES (?, ?, ?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$id_pedidos, $cantidad, $fecha, $id_productos, $usuario_id]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Solicitud agregada correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al agregar la solicitud. Intente nuevamente.'
        ];
    }

    // Redirigir para evitar reenvío de formulario
    header("Location: solicitar_material.php");
    exit();
}

// Inicializar variables para mantener los valores del formulario tras error
$nombre = isset($_POST['nombre']) ? htmlspecialchars($_POST['name']) : '';
$sku = isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '';
$clase = isset($_POST['clase']) ? htmlspecialchars($_POST['clase']) : '';
$descripcion = isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '';
$cantidad = isset($_POST['cantidad']) ? htmlspecialchars($_POST['cantidad']) : '';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: ../login.php');
    exit();
}

$sql_search = "SELECT pt.id_pedidos, pt.cantidad, pt.fecha, pt.estatus, u.id_usuario, p.id_productos, p.nombre,p.sku, p.clase, p.descripcion, p.existencia
FROM pedidos_taller pt
JOIN productos p ON pt.id_productos = p.id_productos
JOIN users u ON pt.id_usuario = u.id_usuario
WHERE pt.id_usuario = :usuario_id
ORDER BY fecha DESC";
$query_search = $cnnPDO->prepare($sql_search);
$query_search->execute(['usuario_id' => $usuario_id]);
$solicitudes = $query_search->fetchAll(PDO::FETCH_ASSOC);


$sql_productos = "SELECT id_productos, nombre, sku, clase, descripcion, existencia FROM productos ORDER BY nombre ASC";
$query_productos = $cnnPDO->prepare($sql_productos);
$query_productos->execute();
$productos = $query_productos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                        <h3 class="card-title">Mis Solicitudes</h3>
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 text-end">
                        <button type="button" name="addPurchase" id="addPurchase"
                            class="btn btn-primary btn-sm rounded-0" data-bs-toggle="modal"
                            data-bs-target="#purchaseModal">
                            Solicitar Material
                        </button>
                    </div>
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
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12 table-responsive">
                        <table id="purchaseList" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>SKU</th>
                                    <th>Clase</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Existencia</th>
                                    <th>Estatus</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitudes as $solicitud): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($solicitud['id_pedidos'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['clase'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['cantidad'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['existencia'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['estatus'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['fecha'], ENT_QUOTES, 'UTF-8') ?></td>


                                    </tr>
                                <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<script>
function cargarSolicitudes() {
    fetch('get_pedidos_almacen.php')
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

<div id="purchaseModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Solicitar Material</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-8" style="margin: 0 auto; margin-top: 50px;">
                    <div class="card">
                        <div class="card-header">
                            Datos de los productos
                        </div>
                        <div class="card-body">
                            <form method="post" id="solicitudForm">
                                <input type="hidden" name="id_productos" id="id_productos">

                                <div class="mb-3">
                                    <label for="sku">SKU:</label>
                                    <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                                    <div id="sku_suggestions" class="list-group"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="nombre">Nombre:</label>
                                    <input type="text" class="form-control" name="nombre" id="material_nombre" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="clase">Clase:</label>
                                    <input type="text" class="form-control" name="clase" id="material_clase" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion">Descripción:</label>
                                    <input type="text" class="form-control" name="descripcion"
                                        id="material_descripcion" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="cantidad">Cantidad:</label>
                                    <input type="number" class="form-control" name="cantidad" id="cantidad" autocomplete="off">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="agregar" class="btn btn-primary" form="solicitudForm">Solicitar</button>
                <button type="button" class="btn btn-default border btn-sm rounded-0"
                    data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const skuInput = document.getElementById('sku');
        const suggestionsBox = document.getElementById('sku_suggestions');

        skuInput.addEventListener('input', function() {
            const term = this.value.trim();
            if (term.length < 1) {
                suggestionsBox.innerHTML = '';
                return;
            }

            fetch('buscar_sku.php?term=' + encodeURIComponent(term))
                .then(response => response.json())
                .then(data => {
                    suggestionsBox.innerHTML = ''; // Limpiar resultados anteriores

                    data.forEach(item => {
                        const btn = document.createElement('button'); // Creamos el botón
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action';
                        btn.textContent = item.sku + ' - ' + item.nombre;

                        btn.addEventListener('click', () => {
                            skuInput.value = item.sku;
                            document.getElementById('id_productos').value = item.id;
                            document.getElementById('material_nombre').value = item.nombre;
                            document.getElementById('material_clase').value = item.clase;
                            document.getElementById('material_descripcion').value = item.descripcion;
                            suggestionsBox.innerHTML = ''; // Limpiar sugerencias tras seleccionar
                        });

                        suggestionsBox.appendChild(btn); // Añadir el botón a la lista
                    });
                })
                .catch(error => {
                    console.error('Error al buscar SKUs:', error);
                });
        });
    });
</script>


<style>
    #sku_suggestions {
        position: absolute;
        width: 100%;
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
    }
</style>
<?php include_once './templates/footer.php'; ?>
