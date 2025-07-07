<?php
include_once './templates/header.php';
session_start();
require_once './db_conexion.php';

// Verificación de sesión mejorada
if (!isset($_SESSION['logged_in'], $_SESSION['id_usuario'], $_SESSION['rol']) || $_SESSION['rol'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

// Obtener productos disponibles
$sql = "SELECT id_productos, sku, nombre, clase, descripcion, existencia FROM productos ";
$stmt = $cnnPDO->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($productos)) {
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'No hay productos disponibles.'
    ];
    exit();
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                        <h3 class="card-title">Productos Disponibles</h3>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 text-end">
                        <button type="button" name="addPurchase" id="addPurchase"
                            class="btn btn-primary btn-sm rounded-0" data-bs-toggle="modal"
                            data-bs-target="#purchaseModal">
                            <i class="far fa-plus-square"></i> Solicitar Material
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 ms-auto">
                        <input type="text" id="busquedaProductos" class="form-control" align="center"
                            placeholder="Buscar por nombre o clase..."">
                    </div>
                    <div class=" row">
                        <div class="col-sm-12 table-responsive">
                            <table id="purchaseList" class="table table-bordered table-striped" style="margin-top: 20px;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>SKU</th>
                                        <th>Clase</th>
                                        <th>Existencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos as $producto): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($producto['id_productos']) ?></td>
                                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                            <td><?= htmlspecialchars($producto['sku']) ?></td>
                                            <td><?= htmlspecialchars($producto['clase']) ?></td>
                                            <td><?= htmlspecialchars($producto['existencia']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para solicitar material -->
    <div id="purchaseModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Solicitar Material</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <form method="post" action="procesar_solicitud.php" id="solicitudForm">
                            <input type="hidden" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>">
                            <input type="hidden" name="id_productos" id="id_productos">
                            <div class="mb-3">
                                <label for="sku">Material (SKU):</label>
                                <input type="text" class="form-control" id="sku" name="sku" autocomplete="off" required>
                                <div id="sku_suggestions" class="list-group position-absolute w-100"></div>
                            </div>
                            <div class="mb-3">
                                <label>Nombre:</label>
                                <input type="text" class="form-control" id="material_nombre" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Clase:</label>
                                <input type="text" class="form-control" id="material_clase" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Descripción:</label>
                                <input type="text" class="form-control" id="material_descripcion" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Existencia:</label>
                                <input type="text" class="form-control" id="material_existencia" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="cantidad">Cantidad a solicitar:</label>
                                <input type="number" class="form-control" name="cantidad" id="cantidad_solicitud" min="1" required>
                                <small class="text-muted">Puedes solicitar más que la existencia actual si es necesario.</small>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="solicitudForm" class="btn btn-primary">Solicitar</button>
                    <button type="button" class="btn btn-default border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('busquedaProductos').addEventListener('keyup', function() {
            const filtro = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('#purchaseList tbody tr');

            filas.forEach(fila => {
                const nombre = fila.cells[1].textContent.toLowerCase();
                const clase = fila.cells[3].textContent.toLowerCase();

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
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action';
                            btn.textContent = item.sku + ' - ' + item.nombre;

                            btn.addEventListener('click', () => {
                                skuInput.value = item.sku;
                                document.getElementById('id_productos').value = item.id_productos;
                                document.getElementById('material_nombre').value = item.nombre;
                                document.getElementById('material_clase').value = item.clase;
                                document.getElementById('material_descripcion').value = item.descripcion;
                                document.getElementById('material_existencia').value = item.existencia;
                                suggestionsBox.innerHTML = '';
                            });

                            suggestionsBox.appendChild(btn);
                        });
                    })
                    .catch(error => {
                        console.error('Error al buscar SKUs:', error);
                    });
            });

            // Ocultar sugerencias si se hace clic fuera
            document.addEventListener('click', function(e) {
                if (!skuInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.innerHTML = '';
                }
            });
        });
    </script>


    <style>
        #sku_suggestions {
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
        }
    </style>
    <?php include_once './templates/footer.php'; ?>