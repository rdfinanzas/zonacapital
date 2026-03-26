var pedidoInternoId = null;
var bienEditandoId = null;

$(function() {
    $('.select2').select2();

    $('#btnCargarPedido').click(function() {
        var id = $('#pedidoInternoId').val();
        if (!id) {
            alert('Debe ingresar un ID de pedido interno');
            return;
        }
        cargarPedido(id);
    });

    $('#cantidad, #precio').on('input', function() {
        calcularSubtotal();
    });

    $('#formCotizacion').submit(function(e) {
        e.preventDefault();
        guardarCotizacion();
    });

    $('#btnCancelarCoti').click(function() {
        $('#formCotizacion')[0].reset();
        $('#cotizacionId').val(0);
        $('#pedidoInternoXBienId').val('');
        bienEditandoId = null;
        $('#seccionFormularioCoti').hide();
    });

    apiLaravel.get('proveedores/get-todos', function(data) {
        var options = '<option value="">-SELECCIONAR-</option>';
        data.forEach(function(prov) {
            options += '<option value="' + prov.IdProveedor + '">' + prov.Proveedor + '</option>';
        });
        $('#proveedorId').html(options);
    });
});

function cargarPedido(id) {
    pedidoInternoId = id;

    apiLaravel.get('cotizaciones/info-pedido-interno/' + id, function(data) {
        if (!data) {
            alert('Pedido interno no encontrado');
            $('#infoPedido').hide();
            $('#seccionCotizaciones').hide();
            return;
        }

        $('#servicioSolicitante').text(data.servicioSolicitante ? data.servicioSolicitante.Servicio : '-');
        $('#servicioDestino').text(data.servicioDestino ? data.servicioDestino.Servicio : '-');
        $('#estadoPedido').text(data.estado ? data.estado.PedidoInternoEstado : '-');
        $('#fechaCreacion').text(data.FechaCreacion ? data.FechaCreacion : '-');

        $('#infoPedido').show();

        cargarBienes(id);
        cargarCotizaciones(id);
    });
}

function cargarBienes(id) {
    apiLaravel.get('cotizaciones/bienes-por-pedido-interno/' + id, function(data) {
        var html = '';
        data.forEach(function(bien) {
            html += '<tr>';
            html += '<td>' + (bien.bien ? bien.bien.NombreB : '-') + '</td>';
            html += '<td>' + bien.Cantidad + '</td>';
            html += '<td>' + (bien.Detalle || bien.ObsCoti || '') + '</td>';
            html += '<td>';
            html += '<button type="button" onclick="agregarCotizacion(' + bien.IdPedidoInternoXbien + ')" class="btn btn-sm btn-success">';
            html += '<i class="fas fa-plus"></i> Cotizar';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });
        $('#bienesTable').html(html);
        $('#seccionCotizaciones').show();
    });
}

function cargarCotizaciones(id) {
    apiLaravel.get('cotizaciones/get-cotizacion/' + id, function(data) {
        var html = '';

        if (data.registros && data.registros.length > 0) {
            data.registros.forEach(function(registro) {
                if (!registro.proveedor) return;

                html += '<div class="card mb-3">';
                html += '<div class="card-header bg-primary">';
                html += '<h5 class="card-title mb-0">' + registro.proveedor.Proveedor + '</h5>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<table class="table table-sm table-striped">';
                html += '<thead>';
                html += '<tr>';
                html += '<th>Bien</th>';
                html += '<th>Cantidad</th>';
                html += '<th>Precio</th>';
                html += '<th>Subtotal</th>';
                html += '<th>Proforma</th>';
                html += '<th>Ganador</th>';
                html += '<th>Acciones</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';

                registro.cotizaciones.forEach(function(coti) {
                    var bienNombre = coti.pedidoInternoXBien && coti.pedidoInternoXBien.bien ? coti.pedidoInternoXBien.bien.NombreB : '-';
                    var tieneOrden = coti.ordenCompraXBienes && coti.ordenCompraXBienes.length > 0;

                    html += '<tr>';
                    html += '<td>' + bienNombre + '</td>';
                    html += '<td>' + coti.Cantidad + '</td>';
                    html += '<td>$' + parseFloat(coti.Precio).toFixed(2) + '</td>';
                    html += '<td>$' + parseFloat(coti.Subtotal).toFixed(2) + '</td>';
                    html += '<td>' + (coti.Proforma || '') + '</td>';
                    html += '<td>';
                    html += '<input type="checkbox" ' + (coti.Prem ? 'checked' : '') +
                        ' onclick="togglePremiado(' + coti.IdCotizacion + ', this.checked)" ' +
                        (tieneOrden ? 'disabled' : '') + '>';
                    html += '</td>';
                    html += '<td>';
                    if (!tieneOrden) {
                        html += '<button type="button" onclick="editarCotizacion(' + coti.IdCotizacion + ')" class="btn btn-xs btn-primary">';
                        html += '<i class="fas fa-edit"></i>';
                        html += '</button> ';
                        html += '<button type="button" onclick="eliminarCotizacion(' + coti.IdCotizacion + ')" class="btn btn-xs btn-danger">';
                        html += '<i class="fas fa-trash"></i>';
                        html += '</button>';
                    } else {
                        html += '<span class="badge badge-success">En OC</span>';
                    }
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody>';
                html += '</table>';
                html += '</div>';
                html += '</div>';
            });
        } else {
            html = '<div class="alert alert-warning">No hay cotizaciones cargadas</div>';
        }

        $('#contenedorCotizaciones').html(html);
        $('#seccionCotizacionesDetalle').show();
    });
}

function agregarCotizacion(pedidoInternoXBienId) {
    $('#pedidoInternoXBienId').val(pedidoInternoXBienId);
    $('#cotizacionId').val(0);
    $('#formCotizacion')[0].reset();
    bienEditandoId = pedidoInternoXBienId;
    $('#seccionFormularioCoti').show();

    $('html, body').animate({
        scrollTop: $('#seccionFormularioCoti').offset().top
    }, 500);
}

function editarCotizacion(id) {
    apiLaravel.get('cotizaciones/' + id, function(data) {
        if (!data) {
            alert('Cotización no encontrada');
            return;
        }

        $('#cotizacionId').val(data.IdCotizacion);
        $('#pedidoInternoXBienId').val(data.PedidoInternoXbien_Id);
        $('#proveedorId').val(data.Proveedor_Id).trigger('change');
        $('#cantidad').val(data.Cantidad);
        $('#precio').val(data.Precio);
        $('#subtotal').val('$' + data.Subtotal);
        $('#proforma').val(data.Proforma || '');

        bienEditandoId = data.PedidoInternoXbien_Id;
        $('#seccionFormularioCoti').show();

        $('html, body').animate({
            scrollTop: $('#seccionFormularioCoti').offset().top
        }, 500);
    });
}

function guardarCotizacion() {
    var id = $('#cotizacionId').val();
    var url = 'cotizaciones';
    var method = 'POST';

    var data = {
        PedidoInternoXBienId: $('#pedidoInternoXBienId').val(),
        Proveedor_Id: $('#proveedorId').val(),
        Cantidad: $('#cantidad').val(),
        Precio: $('#precio').val(),
        Proforma: $('#proforma').val(),
        PIId: pedidoInternoId,
    };

    if (id > 0) {
        url = 'cotizaciones/' + id;
        method = 'PUT';
    }

    apiLaravel.exec(url, method, data, function(response) {
        if (response.success) {
            alert(response.message);
            $('#formCotizacion')[0].reset();
            $('#cotizacionId').val(0);
            $('#pedidoInternoXBienId').val('');
            bienEditandoId = null;
            $('#seccionFormularioCoti').hide();
            cargarCotizaciones(pedidoInternoId);
        } else {
            alert(response.message);
        }
    });
}

function calcularSubtotal() {
    var cantidad = parseFloat($('#cantidad').val()) || 0;
    var precio = parseFloat($('#precio').val()) || 0;
    var subtotal = cantidad * precio;
    $('#subtotal').val('$' + subtotal.toFixed(2));
}

function eliminarCotizacion(id) {
    if (confirm('¿Está seguro que desea eliminar esta cotización?')) {
        apiLaravel.exec('cotizaciones/' + id, 'DELETE', {}, function(response) {
            if (response.success) {
                alert(response.message);
                cargarCotizaciones(pedidoInternoId);
            } else {
                alert(response.message);
            }
        });
    }
}

function togglePremiado(id, checked) {
    apiLaravel.exec('cotizaciones/update-premiado/' + id, 'PUT', { ck: checked ? 1 : 0 }, function(response) {
        if (response.success) {
            cargarCotizaciones(pedidoInternoId);
        }
    });
}
