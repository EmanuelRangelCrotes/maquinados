// Script para editar en el modal
document.addEventListener('DOMContentLoaded', function () {
    // Editar modal
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;
        document.getElementById('edit_id_productos').value = button.getAttribute('data-id');
        document.getElementById('edit_precio').value = button.getAttribute('data-precio');
        document.getElementById('edit_nombre').value = button.getAttribute('data-nombre');
        document.getElementById('edit_sku').value = button.getAttribute('data-sku');
        document.getElementById('edit_clase').value = button.getAttribute('data-clase');
        document.getElementById('edit_descripcion').value = button.getAttribute('data-descripcion');
        document.getElementById('edit_unidad_medida').value = button.getAttribute('data-unidad_medida');
    });

    // Modal precios
    const preciosModal = document.getElementById('preciosModal');
    preciosModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;
        document.getElementById('precios_id_producto').value = button.getAttribute('data-id_producto');
        document.getElementById('precios_total_precio').value = button.getAttribute('data-total_precio');
        document.getElementById('precios_total_inventario').value = button.getAttribute('data-total_inventario');

    });
});

preciosModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    if (!button) return;

    const idProducto = button.getAttribute('data-id_producto');
    const totalPrecio = button.getAttribute('data-total_precio');
    const totalInventario = button.getAttribute('data-total_inventario');


    const idInput = document.getElementById('precios_id_producto');
    const totalInput = document.getElementById('precios_total_precio');
    const totalInventarioInput = document.getElementById('precios_cantidad_total');


    if (idInput) idInput.value = idProducto;
    if (totalInput) totalInput.value = totalPrecio;
    if (totalInventarioInput) totalInventarioInput.value = totalInventario;
});
document.addEventListener('DOMContentLoaded', function () {
    const preciosModal = document.getElementById('preciosModal');
    preciosModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;
        const idProducto = button.getAttribute('data-id_producto');
        const totalPrecio = button.getAttribute('data-total_precio');
        const totalInventario = button.getAttribute('data-total_inventario');
        preciosModal.querySelector('#precios_id_producto').value = idProducto;
        preciosModal.querySelector('#precios_total_precio').value = totalPrecio;
        preciosModal.querySelector('#precios_cantidad_total').value = totalInventario;
    });
});

// Script para sugerir una clase en el modal de insercion de productos
$(document).ready(function () {
    const claseInput = $('#add_clase');
    const suggestionsContainer = $('#clase-suggestions');
    let timeoutId;

    // Función mejorada para cargar sugerencias
    function loadSuggestions(searchTerm) {
        console.log("Buscando: ", searchTerm); // Debug

        $.ajax({
            url: '../users/buscar_clase.php',
            dataType: 'json',
            data: {
                q: searchTerm
            },
            success: function (data) {
                console.log("Respuesta recibida: ", data); // Debug

                suggestionsContainer.empty();

                if (data && data.length > 0) {
                    data.forEach(item => {
                        if (item.text) { // Asegurarse que existe text
                            suggestionsContainer.append(
                                '<div class="suggestion-item">' + item.text + '</div>'
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
            error: function (xhr, status, error) {
                console.error("Error en AJAX: ", status, error); // Debug
                suggestionsContainer.hide();
            }
        });
    }

    // Evento al escribir - versión mejorada
    claseInput.on('input', function () {
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
    suggestionsContainer.on('click', '.suggestion-item', function () {
        claseInput.val($(this).text());
        suggestionsContainer.hide();
    });

    // Ocultar al hacer clic fuera
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#clase, #clase-suggestions').length) {
            suggestionsContainer.hide();
        }
    });
});

// Script para sugerir una descripcion en el modal de insercion de productos
$(document).ready(function () {
    const descripcionInput = $('#add_descripcion');
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
                beforeSend: function () {
                    suggestionsContainer.html('<div class="loading">Buscando...</div>').show();
                },
                success: function (data) {
                    suggestionsContainer.empty();

                    if (data && data.length > 0) {
                        data.forEach(item => {
                            suggestionsContainer.append(
                                '<div class="suggestion-item">' + item.text + '</div>'
                            );

                        });
                    } else {
                        suggestionsContainer.append(
                            '<div class="no-results">No se encontraron coincidencias</div>'
                        );
                    }
                    suggestionsContainer.show();
                },
                error: function () {
                    suggestionsContainer.hide();
                }
            });
        }, 300);
    }

    // Evento al escribir
    descripcionInput.on('input', function () {
        loadSuggestions($(this).val().trim());
    });

    // Seleccionar sugerencia
    suggestionsContainer.on('click', '.suggestion-item', function () {
        descripcionInput.val($(this).text());
        suggestionsContainer.hide();
    });

    // Ocultar al hacer clic fuera
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#descripcion, #descripcion-suggestions').length) {
            suggestionsContainer.hide();
        }
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return; // Asegúrate de que el botón existe
        const id = button.getAttribute('data-id');
        const precio = button.getAttribute('data-precio');
        const nombre = button.getAttribute('data-nombre');
        const sku = button.getAttribute('data-sku');
        const clase = button.getAttribute('data-clase');
        const descripcion = button.getAttribute('data-descripcion');
        const unidadMedida = button.getAttribute('data-unidad_medida');
        // Llena los campos del modal
        editModal.querySelector('#edit_id_productos').value = id;
        editModal.querySelector('#edit_precio').value = precio;
        editModal.querySelector('#edit_nombre').value = nombre;
        editModal.querySelector('#edit_sku').value = sku;
        editModal.querySelector('#edit_clase').value = clase;
        editModal.querySelector('#edit_descripcion').value = descripcion;
        editModal.querySelector('#edit_unidad_medida').value = unidadMedida;
    });
});