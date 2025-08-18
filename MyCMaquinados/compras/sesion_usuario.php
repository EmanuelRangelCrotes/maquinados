<?php
include_once './templates/header.php';
require_once './db_conexion.php';
session_start();

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];
// Verificación robusta de sesión
if (
    !isset($_SESSION['logged_in'], $_SESSION['rol'], $_SESSION['id_usuario']) ||
    $_SESSION['rol'] !== 'compras' ||
    $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']
) {

    error_log("Intento de acceso no autorizado desde " . $_SERVER['REMOTE_ADDR']);
    header('Location: ../login.php');
    exit();
}
// Validar si el formulario fue enviado
if (isset($_POST['agregar'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $sku = htmlspecialchars(trim($_POST['sku']), ENT_QUOTES, 'UTF-8');
    $clase = htmlspecialchars(trim($_POST['clase']), ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
    $unidad_medida = htmlspecialchars(trim($_POST['unidad_medida']), ENT_QUOTES, 'UTF-8');
    $precio = trim($_POST['precio']);
    $existencia = htmlspecialchars(trim($_POST['existencia']), ENT_QUOTES, 'UTF-8');

    // Validar que los campos no estén vacíos
    if (
        empty($nombre) ||
        empty($clase) ||
        empty($descripcion) ||
        empty($unidad_medida) ||
        empty($existencia)
    ) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: sesion_usuario.php");
        exit();
    }

    try {
        // Insertar el producto en la base de datos
        $sql = "INSERT INTO productos (nombre, sku, clase, descripcion, unidad_medida, precio, existencia) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$nombre, $sku, $clase, $descripcion, $unidad_medida, $precio, $existencia]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Producto agregado correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al agregar el producto. Intente nuevamente.'
        ];
    }

    // Redirigir para evitar reenvío de formulario
    header("Location: sesion_usuario.php");
    exit();
}

// Inicializar variables para mantener los valores del formulario tras error
$nombre = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
$sku = isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '';
$clase = isset($_POST['clase']) ? htmlspecialchars($_POST['clase']) : '';
$descripcion = isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '';
$unidad_medida = isset($_POST['unidad_medida']) ? htmlspecialchars($_POST['unidad_medida']) : '';
$precio = isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : '';
$existencia = isset($_POST['existencia']) ? htmlspecialchars($_POST['existencia']) : '';


// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: ../login.php');
    exit();
}

$sql_search = "SELECT id_productos, nombre, sku, clase, descripcion, unidad_medida, precio, existencia FROM productos";
$query_search = $cnnPDO->prepare($sql_search);
$query_search->execute();




$sql_total  = "SELECT SUM(existencia * precio) AS cantidad_total
FROM productos";
$query_total = $cnnPDO->prepare($sql_total);
$query_total->execute();


if (isset($_POST['añadir_clase'])) {
    $nombre_clase = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');

    // Validar que el campo no esté vacío
    if (empty($nombre_clase)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El campo clase es obligatorio.'
        ];
        header("Location: sesion_usuario.php");
        exit();
    }

    try {
        // Insertar la clase en la base de datos
        $sql = "INSERT INTO clase (nombre) VALUES (?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$nombre_clase]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Clase agregada correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al agregar la clase. Intente nuevamente.'
        ];
    };

    // Redirigir para evitar reenvío de formulario
    header("Location: sesion_usuario.php");
    exit();
};
if (isset($_POST['añadir_descripcion'])) {
    $nombre_descripcion = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');

    // Validar que el campo no esté vacío
    if (empty($nombre_descripcion)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El campo descripción es obligatorio.'
        ];
        header("Location: sesion_usuario.php");
        exit();
    }

    try {
        // Insertar la descripción en la base de datos
        $sql = "INSERT INTO descripcion (nombre) VALUES (?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$nombre_descripcion]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Descripción agregada correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al agregar la descripción. Intente nuevamente.'
        ];
    };

    // Redirigir para evitar reenvío de formulario
    header("Location: sesion_usuario.php");
    exit();
}


?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                        <h3 class="card-title">Lista de Productos</h3>
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 text-end">
                        <button type="button" name="addPurchase" id="addPurchase"
                            class="btn btn-primary btn-sm rounded-0"
                            data-bs-toggle="modal" data-bs-target="#purchaseModal">
                            <i class="far fa-plus-square"></i> Agregar Producto
                        </button>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown" aria-expanded="false">
                            Agregar
                        </button>
                        <div class="dropdown-menu" aria-labelledby="triggerId">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalPendientes">
                                <h6>Clase</h6>
                            </a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalAceptadas">
                                <h6>Descripcion</h6>
                            </a>
                            <div class="dropdown-divider"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12 table-responsive">
                        <table id="purchaseList" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>SKU</th>
                                    <th>Clase</th>
                                    <th>Descripción</th>
                                    <th>Unidad de Medida</th>
                                    <th>Precio</th>
                                    <th>Existencia</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="content">
                                <?php foreach ($query_total as $total) : ?>
                                    <?php while ($row = $query_search->fetch(PDO::FETCH_ASSOC)) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                                            <td><?= htmlspecialchars($row['sku']) ?></td>
                                            <td><?= htmlspecialchars($row['clase']) ?></td>
                                            <td><?= htmlspecialchars($row['descripcion']) ?></td>
                                            <td><?= htmlspecialchars($row['unidad_medida']) ?></td>
                                            <td><?= htmlspecialchars($row['precio']) ?></td>
                                            <td><?= htmlspecialchars($row['existencia']) ?></td>
                                            <td>
                                                <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 text-end">
                                                    <!-- Botón editar precio -->
                                                    <button type="button"
                                                        class="btn btn-primary btn-sm rounded-0 edit-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal"
                                                        data-id="<?= htmlspecialchars($row['id_productos']) ?>"
                                                        data-precio="<?= htmlspecialchars($row['precio']) ?>"
                                                        data-nombre="<?= htmlspecialchars($row['nombre']) ?>"
                                                        data-sku="<?= htmlspecialchars($row['sku']) ?>"
                                                        data-clase="<?= htmlspecialchars($row['clase']) ?>"
                                                        data-descripcion="<?= htmlspecialchars($row['descripcion']) ?>"
                                                        data-unidad_medida="<?= htmlspecialchars($row['unidad_medida']) ?>">
                                                        <i class="far fa-plus-square"></i> Editar Precio
                                                    </button>
                                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 text-end">
                                                        <div class="icon-info">
                                                            <button type="button"
                                                                class="btn btn-info btn-sm rounded-0"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#preciosModal"
                                                                data-id_producto="<?= htmlspecialchars($row['id_productos']) ?>"
                                                                data-total_precio="$<?= htmlspecialchars($row['precio'] * $row['existencia']) ?>"
                                                                data-total_inventario="$<?= htmlspecialchars($total['cantidad_total']) ?>">
                                                                <i class="fa-solid fa-circle-info"></i>Precio por Producto
                                                            </button>
                                                        </div>
                                                    </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endforeach; ?>

                            </tbody>
                        </table>


                    </div>
                </div>
            </div>
        </div>
    </div>



</div>


<div id="purchaseModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="far fa-plus-square"></i> Agregar Producto</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="productForm">
                    <div class="mb-3">
                        <label for="add_nombre">Nombre:</label>
                        <input type="text" class="form-control" name="nombre" id="add_nombre">
                    </div>
                    <div class="mb-3">
                        <label for="add_sku">SKU</label>
                        <input type="text" class="form-control" name="sku" id="add_sku">
                    </div>
                    <div class="mb-3">
                        <label for="add_clase">Clase</label>
                        <input type="text" class="form-control" name="clase" id="add_clase" autocomplete="off">
                        <div id="clase-suggestions" class="suggestions-container"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_descripcion">Descripción</label>
                        <input type="text" class="form-control" name="descripcion" id="add_descripcion" autocomplete="off">
                        <div id="descripcion-suggestions" class="suggestions-container"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add_unidad_medida">Unidad de Medida</label>
                        <select class="form-control" name="unidad_medida" id="add_unidad_medida">
                            <option value="">Seleccione la Unidad de Medida</option>
                            <option value="KG">KG</option>
                            <option value="LT">LT</option>
                            <option value="PZ">PZ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_existencia">Existencia</label>
                        <input type="text" class="form-control" name="existencia" id="add_existencia">
                    </div>
                    <div class="mb-3">
                        <label for="add_precio">Precio</label>
                        <input type="text" class="form-control" name="precio" id="add_precio">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" name="agregar" class="btn btn-primary" form="productForm">Agregar</button>
                <button type="button" class="btn btn-default border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modales para cada opción -->
<div class="modal fade" id="modalPendientes" tabindex="-1" aria-labelledby="modalPendientesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPendientesLabel">Añadir Clase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="claseForm">
                    <div class="mb-3">
                        <label for="nombre">Clase</label>
                        <input type="text" class="form-control" name="nombre" autocomplete="off">
                        <div id="clase-suggestions" class="suggestions-container"></div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="submit" name="añadir_clase" class="btn btn-primary" form="claseForm">Agregar</button>
                    <button type="button" class="btn btn-default border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAceptadas" tabindex="-1" aria-labelledby="modalAceptadasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAceptadasLabel">Añadir Descripcion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="descripcionForm">
                    <div class="mb-3">
                        <label for="nombre">Descripcion</label>
                        <input type="text" class="form-control" name="nombre" autocomplete="off">
                        <div id="clase-suggestions" class="suggestions-container"></div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="submit" name="añadir_descripcion" class="btn btn-primary" form="descripcionForm">Agregar</button>
                    <button type="button" class="btn btn-default border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="far fa-edit"></i> Editar Precio</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="editForm" action="actualizar_precio.php">
                    <input type="hidden" name="id_productos" id="edit_id_productos">
                    <div class="mb-3">
                        <label for="edit_nombre">Nombre:</label>
                        <input type="text" class="form-control" name="nombre" id="edit_nombre">
                    </div>
                    <div class="mb-3">
                        <label for="edit_sku">SKU</label>
                        <input type="text" class="form-control" name="sku" id="edit_sku">
                    </div>
                    <div class="mb-3">
                        <label for="edit_clase">Clase</label>
                        <input type="text" class="form-control" name="clase" id="edit_clase">
                    </div>
                    <div class="mb-3">
                        <label for="edit_descripcion">Descripción</label>
                        <input type="text" class="form-control" name="descripcion" id="edit_descripcion">
                    </div>
                    <div class="mb-3">
                        <label for="edit_unidad_medida">Unidad de Medida</label>
                        <input type="text" class="form-control" name="unidad_medida" id="edit_unidad_medida">
                    </div>
                    <div class="mb-3">
                        <label for="edit_precio">Precio Actual</label>
                        <input type="text" class="form-control" name="old_price" id="edit_precio" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nuevo_precio">Nuevo Precio</label>
                        <input type="text" class="form-control" name="precio" id="edit_nuevo_precio">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" name="actualizar" class="btn btn-primary" form="editForm">Actualizar</button>
                <button type="button" class="btn btn-default border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<div id="preciosModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="far fa-money-bill-alt"></i> Precio Total</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="preciosForm">
                    <input type="hidden" name="id_producto" id="precios_id_producto">
                    <div class="mb-3">
                        <label for="precios_total_precio">Precio Total</label>
                        <input type="text" class="form-control" name="total_precio" id="precios_total_precio" readonly>
                        <label for="precios_total_precio">Total en Inventario</label>
                        <input type="text" class="form-control" name="total_precio" id="precios_cantidad_total" readonly>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script type="module" src="../functions/functions.js"></script>

<script>
    // Script para la tabla de data table
    $(document).ready(function() {
        $('#purchaseList').DataTable({
            language: {
                "url": "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            }
        });
    });
</script>
<?php include_once './templates/footer.php'; ?>