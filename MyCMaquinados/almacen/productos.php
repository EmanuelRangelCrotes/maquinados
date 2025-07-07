<?php include_once './templates/header.php';

require_once './db_conexion.php';
session_start();

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];
// Verificación robusta de sesión
if (
    !isset($_SESSION['logged_in'], $_SESSION['rol'], $_SESSION['id_usuario']) ||
    $_SESSION['rol'] !== 'almacen' ||
    $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']
) {

    error_log("Intento de acceso no autorizado desde " . $_SERVER['REMOTE_ADDR']);
    header('Location: login.php');
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
        empty($sku) ||
        empty($clase) ||
        empty($descripcion) ||
        empty($unidad_medida) ||
        empty($existencia) ||
        empty($precio)
    ) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: productos.php");
        exit();
    }

    // Validar que el precio sea numérico
    if (!is_numeric($precio)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El precio debe ser un valor numérico.'
        ];
        header("Location: productos.php");
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
    header("Location: productos.php");
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
    header('Location: login.php');
    exit();
}

$sql_search = "SELECT id_productos, nombre, sku, clase, descripcion, unidad_medida, precio, existencia FROM productos";
$query_search = $cnnPDO->prepare($sql_search);
$query_search->execute();


if (isset($_POST['añadir_clase'])) {
    $nombre_clase = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');

    // Validar que el campo no esté vacío
    if (empty($nombre_clase)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El campo clase es obligatorio.'
        ];
        header("Location: productos.php");
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
    header("Location: productos.php");
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
        header("Location: productos.php");
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
    header("Location: productos.php");
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
                            Agregar Producto
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
                <div class="row mb-3">
                    <div class="col-md-4 ms-auto">
                        <input type="text" id="busquedaProductos" class="form-control" align="center" placeholder="Buscar por nombre o clase...">
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $query_search->fetch(PDO::FETCH_ASSOC)) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                                            <td><?= htmlspecialchars($row['sku']) ?></td>
                                            <td><?= htmlspecialchars($row['clase']) ?></td>
                                            <td><?= htmlspecialchars($row['descripcion']) ?></td>
                                            <td><?= htmlspecialchars($row['unidad_medida']) ?></td>
                                            <td><?= htmlspecialchars($row['precio']) ?></td>
                                            <td><?= htmlspecialchars($row['existencia']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('busquedaProductos').addEventListener('keyup', function() {
                const filtro = this.value.toLowerCase().trim();
                const filas = document.querySelectorAll('#purchaseList tbody tr');

                filas.forEach(fila => {
                    const nombre = fila.cells[0].textContent.toLowerCase();
                    const clase = fila.cells[2].textContent.toLowerCase();

                    if (filtro === '') {
                        fila.style.display = '';
                    } else if (nombre.includes(filtro) || clase.includes(filtro)) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            });
        </script>

    </div>


    <div id="purchaseModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Agregar Producto</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-8" style="margin: 0 auto; margin-top: 50px;">
                        <div class="card">
                            <div class="card-header">
                                Datos de los productos
                            </div>
                            <div class="card-body">
                                <form method="post" id="productForm">
                                    <div class="mb-3">
                                        <label for="name">Nombre:</label>
                                        <input type="text" class="form-control" name="nombre" id="nombre">
                                    </div>
                                    <div class="mb-3">
                                        <label for="sku">SKU</label>
                                        <input type="text" class="form-control" name="sku" id="sku">
                                    </div>
                                    <div class="mb-3">
                                        <label for="clase">Clase</label>
                                        <input type="text" class="form-control" name="clase" id="clase" autocomplete="off">
                                        <div id="clase-suggestions" class="suggestions-container"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descripcion">Descripción</label>
                                        <input type="text" class="form-control" name="descripcion" id="descripcion" autocomplete="off">
                                        <div id="descripcion-suggestions" class="suggestions-container"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="unidad_medida">Unidad de Medida</label>
                                        <select type="text" class="form-control" name="unidad_medida" id="unidad_medida">
                                            <option value="">Seleccione la Unida de Medida</option>
                                            <option value="KG">KG</option>
                                            <option value="LT">LT</option>
                                            <option value="PZ">PZ</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="existencia">Existencia</label>
                                        <input type="text" class="form-control" name="existencia" id="existencia">
                                    </div>
                                    <div class="mb-3">
                                        <label for="precio">Precio</label>
                                        <input type="text" class="form-control" name="precio" id="precio">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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

    <script>
        $(document).ready(function() {
            const claseInput = $('#clase');
            const suggestionsContainer = $('#clase-suggestions');
            let timeoutId;

            // Función mejorada para cargar sugerencias
            function loadSuggestions(searchTerm) {
                console.log("Buscando: ", searchTerm); // Debug

                $.ajax({
                    url: 'buscar_clase.php',
                    dataType: 'json',
                    data: {
                        q: searchTerm
                    },
                    success: function(data) {
                        console.log("Respuesta recibida: ", data); // Debug

                        suggestionsContainer.empty();

                        if (data && data.length > 0) {
                            data.forEach(item => {
                                if (item.text) { // Asegurarse que existe text
                                    suggestionsContainer.append(
                                        `<div class="suggestion-item">${item.text}</div>`
                                    );
                                }
                            });
                            suggestionsContainer.show();
                        } else {
                            suggestionsContainer.hide();
                            // Mostrar mensaje si no hay resultados
                            if (searchTerm.length >= 2) {
                                suggestionsContainer.html('<div class="no-results">No se encontraron resultados</div>').show();
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en AJAX: ", status, error); // Debug
                        suggestionsContainer.hide();
                    }
                });
            }

            // Evento al escribir - versión mejorada
            claseInput.on('input', function() {
                clearTimeout(timeoutId);
                const searchTerm = $(this).val().trim();

                if (searchTerm.length >= 2) {
                    timeoutId = setTimeout(() => {
                        loadSuggestions(searchTerm);
                    }, 300);
                } else {
                    suggestionsContainer.hide();
                }
            });

            // Selección de sugerencia
            suggestionsContainer.on('click', '.suggestion-item', function() {
                claseInput.val($(this).text());
                suggestionsContainer.hide();
            });

            // Ocultar al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#clase, #clase-suggestions').length) {
                    suggestionsContainer.hide();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            const descripcionInput = $('#descripcion');
            const suggestionsContainer = $('#descripcion-suggestions');
            let searchTimeout;

            // Función para cargar sugerencias
            function loadSuggestions(searchTerm) {
                clearTimeout(searchTimeout);

                if (searchTerm.length < 2) {
                    suggestionsContainer.hide();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: 'buscar_descripcion.php',
                        data: {
                            q: searchTerm
                        },
                        dataType: 'json',
                        beforeSend: function() {
                            suggestionsContainer.html('<div class="loading">Buscando...</div>').show();
                        },
                        success: function(data) {
                            suggestionsContainer.empty();

                            if (data && data.length > 0) {
                                data.forEach(item => {
                                    suggestionsContainer.append(
                                        `<div class="suggestion-item">${item.text}</div>`
                                    );
                                });
                            } else {
                                suggestionsContainer.append(
                                    '<div class="no-results">No se encontraron coincidencias</div>'
                                );
                            }
                            suggestionsContainer.show();
                        },
                        error: function() {
                            suggestionsContainer.hide();
                        }
                    });
                }, 300);
            }

            // Evento al escribir
            descripcionInput.on('input', function() {
                loadSuggestions($(this).val().trim());
            });

            // Seleccionar sugerencia
            suggestionsContainer.on('click', '.suggestion-item', function() {
                descripcionInput.val($(this).text());
                suggestionsContainer.hide();
            });

            // Ocultar al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#descripcion, #descripcion-suggestions').length) {
                    suggestionsContainer.hide();
                }
            });
        });
    </script>

    <style>
        .suggestions-container {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }

        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .suggestion-item:hover {
            background-color: #f5f5f5;
        }

        .no-results {
            padding: 8px 12px;
            color: #777;
            font-style: italic;
        }
    </style>



    <?php include_once './templates/footer.php'; ?>