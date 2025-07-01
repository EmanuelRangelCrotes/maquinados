$(document).ready(function() {
    // Función para aplicar el filtro de fechas
    $('#filtro').click(function() {
        var startDate = $('#start').val();
        var endDate = $('#end').val();
        var filtro = '<?= $filtro ?>'; // Obtener el filtro actual de estatus
        
        if (startDate && endDate) {
            if (new Date(startDate) > new Date(endDate)) {
                $('#error-message').text('La fecha de inicio no puede ser mayor a la fecha final').show();
                return;
            }
            
            $('#loading').show();
            $('#error-message').hide();
            
            $.ajax({
                url: 'filtrar.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    filtro: filtro
                },
                success: function(response) {
                    $('#loading').hide();
                    if (response.error) {
                        $('#error-message').text(response.error).show();
                    } else {
                        actualizarTabla(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $('#loading').hide();
                    $('#error-message').text('Error al filtrar los datos: ' + error).show();
                }
            });
        } else {
            $('#error-message').text('Por favor seleccione ambas fechas').show();
        }
    });
    
    // Función para actualizar la tabla con los datos filtrados
    function actualizarTabla(data) {
        var tbody = $('#DataTable tbody');
        tbody.empty();
        
        if (data.length === 0) {
            tbody.append('<tr><td colspan="8" class="text-center">No se encontraron resultados</td></tr>');
            return;
        }
        
        $.each(data, function(index, solicitud) {
        
            var badgeClass = solicitud.estatus == 'Surtido' ? 'success' : 
                            (solicitud.estatus == 'Parcial' ? 'warning' : 'danger');
            
            var fechaSurtido = solicitud.fecha_surtido ? solicitud.fecha_surtido : 'N/A';
            
            var row = '<tr>' +
                '<td>' + solicitud.id_solicitud + '</td>' +
                '<td>' + solicitud.nombre + '</td>' +
                '<td>' + solicitud.sku + '</td>' +
                '<td>' + solicitud.cantidad + '</td>' +
                '<td>' + solicitud.cantidad_surtida + '</td>' +
                '<td><span class="badge bg-' + badgeClass + '">' + solicitud.estatus + '</span></td>' +
                '<td>' + solicitud.fecha + '</td>' +
                '<td>' + fechaSurtido + '</td>' +
                '</tr>';
                
            tbody.append(row);
        });
    }
    
    // Opcional: Restablecer el filtro al cambiar entre pestañas de estatus
    $('.btn-group .btn').click(function() {
        $('#start').val('');
        $('#end').val('');
    });
});

// Limpiar filtros
$('#limpiar').click(function() {
    $('#start').val('');
    $('#end').val('');
    $('#filtro').click(); // Esto volverá a cargar los datos sin filtro de fecha
});