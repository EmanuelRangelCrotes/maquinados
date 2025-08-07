<?php
include_once './templates/header.php';

require_once './db_conexion.php';
session_start();

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];
// Verificación robusta de sesión
if (
    !isset($_SESSION['logged_in'], $_SESSION['rol'], $_SESSION['id_usuario']) ||
    $_SESSION['rol'] !== 'almacen' ||
    (!isset($_SESSION['user_agent']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'])
) {
    error_log("Intento de acceso no autorizado desde " . $_SERVER['REMOTE_ADDR']);
    header('Location: ../login.php');
    exit();
}

// Asegurar que la conexión use utf8mb4
$cnnPDO->exec("SET NAMES utf8mb4");

// Procesar agregado de producto
if (isset($_POST['agregar'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $sku = htmlspecialchars(trim($_POST['sku']), ENT_QUOTES, 'UTF-8');
    $clase = htmlspecialchars(trim($_POST['clase']), ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
    $unidad_medida = htmlspecialchars(trim($_POST['unidad_medida']), ENT_QUOTES, 'UTF-8');
    $precio = trim($_POST['precio']);
    $existencia = htmlspecialchars(trim($_POST['existencia']), ENT_QUOTES, 'UTF-8');

    if (
        empty($nombre) ||
        empty($sku) ||
        empty($clase) ||
        empty($descripcion) ||
        empty($unidad_medida) ||
        $existencia === '' ||
        $precio === ''
    ) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: productos.php");
        exit();
    }

    if (!is_numeric($precio)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El precio debe ser un valor numérico.'
        ];
        header("Location: productos.php");
        exit();
    }

    try {
        $sql = "INSERT INTO productos (nombre, sku, clase, descripcion, unidad_medida, precio, existencia) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$nombre, $sku, $clase, $descripcion, $unidad_medida, $precio, $existencia]);

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

    header("Location: productos.php");
    exit();
}

// Inicializar valores (por si necesitas reusar)
$nombre = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
$sku = isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '';
$clase = isset($_POST['clase']) ? htmlspecialchars($_POST['clase']) : '';
$descripcion = isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '';
$unidad_medida = isset($_POST['unidad_medida']) ? htmlspecialchars($_POST['unidad_medida']) : '';
$precio = isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : '';
$existencia = isset($_POST['existencia']) ? htmlspecialchars($_POST['existencia']) : '';

// Verificar sesión activa
if (!isset($_SESSION['name'])) {
    header('Location: ../login.php');
    exit();
}

// Consulta con ordenamiento: primero nombres que NO empiezan con número (letras) y luego los que sí, todo alfabetizado (collate compatible)

$sql_search = "
    SELECT id_productos, nombre, sku, clase, descripcion, unidad_medida, precio, existencia 
    FROM productos
";
$query_search = $cnnPDO->prepare($sql_search);
$query_search->execute();


?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-md-8 col-sm-12">
                        <h3 class="card-title">Lista de Productos</h3>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="busquedaProductos" class="form-control" placeholder="Buscar por nombre o clase...">
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-4 text-end">
                        <button type="button" name="addPurchase" id="addPurchase"
                            class="btn btn-primary btn-sm rounded-0"
                            data-bs-toggle="modal" data-bs-target="#purchaseModal">
                            Agregar Producto
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
    <div class="col-md-6">
        <button class="btn btn-outline-secondary btn-sm" onclick="sortTable('nombre')">Ordenar por Nombre</button>
        <button class="btn btn-outline-secondary btn-sm" onclick="sortTable('clase')">Ordenar por Clase</button>
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
</div>

<!-- Modal de Agregar Producto -->
<div id="purchaseModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Agregar Producto</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            Datos de los productos
                        </div>
                        <div class="card-body">
                            <form method="post" id="productForm">
                                <div class="mb-3">
                                    <label for="nombre">Nombre:</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre">
                                </div>
                                <div class="mb-3">
                                    <label for="sku">SKU</label>
                                    <input type="text" class="form-control" name="sku" id="sku">
                                </div>
                                <div class="mb-3 position-relative">
                                    <label for="clase">Clase</label>
                                    <input type="text" class="form-control" name="clase" id="clase" autocomplete="off">
                                    <div id="clase-suggestions" class="suggestions-container"></div>
                                </div>
                                <div class="mb-3 position-relative">
                                    <label for="descripcion">Descripción</label>
                                    <input type="text" class="form-control" name="descripcion" id="descripcion" autocomplete="off">
                                    <div id="descripcion-suggestions" class="suggestions-container"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="unidad_medida">Unidad de Medida</label>
                                    <select class="form-control" name="unidad_medida" id="unidad_medida">
                                        <option value="">Seleccione la Unidad de Medida</option>
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

<script>
    // Filtro en tiempo real por nombre o clase
    document.getElementById('busquedaProductos').addEventListener('keyup', function () {
        const filtro = this.value.toLowerCase().trim();
        const filas = document.querySelectorAll('#purchaseList tbody tr');

        filas.forEach(fila => {
            const nombre = fila.cells[0].textContent.toLowerCase();
            const clase = fila.cells[2].textContent.toLowerCase();

            if (filtro === '' || nombre.includes(filtro) || clase.includes(filtro)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });

    // Autocompletado de 'clase'
    (function () {
        const claseInput = document.getElementById('clase');
        const suggestionsContainer = document.getElementById('clase-suggestions');
        let timeoutId;

        function loadSuggestions(searchTerm) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'buscar_clase.php?q=' + encodeURIComponent(searchTerm), true);
            xhr.responseType = 'json';
            xhr.onload = function () {
                suggestionsContainer.innerHTML = '';
                if (xhr.status === 200 && Array.isArray(xhr.response) && xhr.response.length) {
                    xhr.response.forEach(item => {
                        if (item.text) {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.textContent = item.text;
                            suggestionsContainer.appendChild(div);
                        }
                    });
                    suggestionsContainer.style.display = 'block';
                } else {
                    suggestionsContainer.innerHTML = '<div class="no-results">No se encontraron resultados</div>';
                    suggestionsContainer.style.display = searchTerm.length >= 2 ? 'block' : 'none';
                }
            };
            xhr.onerror = function () {
                suggestionsContainer.style.display = 'none';
            };
            xhr.send();
        }

        claseInput.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const val = this.value.trim();
            if (val.length >= 2) {
                timeoutId = setTimeout(() => loadSuggestions(val), 300);
            } else {
                suggestionsContainer.style.display = 'none';
            }
        });

        suggestionsContainer.addEventListener('click', function (e) {
            if (e.target.matches('.suggestion-item')) {
                claseInput.value = e.target.textContent;
                suggestionsContainer.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#clase') && !e.target.closest('#clase-suggestions')) {
                suggestionsContainer.style.display = 'none';
            }
        });
    })();

    // Autocompletado de 'descripcion'
    (function () {
        const descInput = document.getElementById('descripcion');
        const suggestionsContainer = document.getElementById('descripcion-suggestions');
        let timeoutId;

        function loadSuggestions(searchTerm) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'buscar_descripcion.php?q=' + encodeURIComponent(searchTerm), true);
            xhr.responseType = 'json';
            xhr.onload = function () {
                suggestionsContainer.innerHTML = '';
                if (xhr.status === 200 && Array.isArray(xhr.response) && xhr.response.length) {
                    xhr.response.forEach(item => {
                        if (item.text) {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.textContent = item.text;
                            suggestionsContainer.appendChild(div);
                        }
                    });
                    suggestionsContainer.style.display = 'block';
                } else {
                    suggestionsContainer.innerHTML = '<div class="no-results">No se encontraron coincidencias</div>';
                    suggestionsContainer.style.display = searchTerm.length >= 2 ? 'block' : 'none';
                }
            };
            xhr.onerror = function () {
                suggestionsContainer.style.display = 'none';
            };
            xhr.send();
        }

        descInput.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const val = this.value.trim();
            if (val.length >= 2) {
                timeoutId = setTimeout(() => loadSuggestions(val), 300);
            } else {
                suggestionsContainer.style.display = 'none';
            }
        });

        suggestionsContainer.addEventListener('click', function (e) {
            if (e.target.matches('.suggestion-item')) {
                descInput.value = e.target.textContent;
                suggestionsContainer.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#descripcion') && !e.target.closest('#descripcion-suggestions')) {
                suggestionsContainer.style.display = 'none';
            }
        });
    })();
</script>
<script>
function sortTable(colName) {
    const table = document.getElementById("purchaseList");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    
    const colIndex = {
        'nombre': 0,
        'clase': 2
    }[colName];

    rows.sort((a, b) => {
        const aText = a.cells[colIndex].textContent.trim();
        const bText = b.cells[colIndex].textContent.trim();

        const aIsNumber = /^\d/.test(aText);
        const bIsNumber = /^\d/.test(bText);

        if (aIsNumber && !bIsNumber) return 1;
        if (!aIsNumber && bIsNumber) return -1;

        return aText.localeCompare(bText, undefined, { sensitivity: 'base' });
    });

    // Reinsertar las filas ordenadas
    rows.forEach(row => tbody.appendChild(row));
}
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
