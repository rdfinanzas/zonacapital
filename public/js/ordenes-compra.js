var idEdit = 0;
var dataTable;
var cotizacionesSeleccionadas = [];
var piSeleccionadoId = null;

$(function() {
    $('.select2').select2();

    $('#form_main').submit(function(e) {
        e.preventDefault();
        guardar();
    });

    $('#btn_limpiar').click(function() {
        limpiar();
    });

    $('#btn_eliminar').click(function() {
        if (idEdit == 0) {
            alert('Debe seleccionar una orden');
            return;
        }
        if (confirm('¿Está seguro que desea eliminar esta orden de compra?')) {
            eliminar();
        }
    });

    $('#btn_seleccionar_cotizaciones').click(function() {
        seleccionarCotizaciones();
    });

    apiLaravel.get('proveedores/get-todos', function(data) {
        var options = '<option value="">-TODOS-</option>';
        data.forEach(function(prov) {
            options += '<option value="' + prov.IdProveedor + '">' + prov.Proveedor + '</option>';
        });
        $('#BuscarProveedor').html(options);
    });

    refrescarTabla();
});

function limpiar() {
    idEdit = 0;
    $('#IdOrdenCompra').val(0);
    $('#form_main')[0].reset();
    $('#bienes_orden_container').empty();
    cotizacionesSeleccionadas = [];
    piSeleccionadoId = null;
}

function guardar() {
    var data = {
        Cuenta_Id: $('#Cuenta_Id').val(),
        Estado_Id: $('#Estado_Id').val(),
        Autorizado: $('#Autorizado').val(),
        Plazo: $('#Plazo').val(),
        Lugar: $('#Lugar').val(),
        ContraEntrega: $('#ContraEntrega').val(),
        Obs: $('#Obs').val(),
    };

    var url = 'ordenes-compra';
    var method = 'POST';

    if ($('#IdOrdenCompra').val() > 0) {
        url = 'ordenes-compra/' + $('#IdOrdenCompra').val();
        method = 'PUT';
        data.bienes_actuales = cotizacionesSeleccionadas;
    }

    apiLaravel.exec(url, method, data, function(response) {
        if (response.success) {
            alert(response.message);
            limpiar();
            refrescarTabla();
        } else {
            alert(response.message);
        }
    });
}

function editar(id) {
    idEdit = id;

    apiLaravel.get('ordenes-compra/' + id, function(data) {
        if (!data) {
            alert('Orden no encontrada');
            return;
        }

        $('#IdOrdenCompra').val(data.IdOrdenCompra);
        $('#Cuenta_Id').val(data.Cuenta_Id);
        $('#Estado_Id').val(data.Estado_Id);
        $('#Autorizado').val(data.Autorizado);
        $('#Plazo').val(data.Plazo || '');
        $('#Lugar').val(data.Lugar || '');
        $('#ContraEntrega').val(data.ContraEntrega);
        $('#Obs').val(data.Obs || '');

        $('#bienes_orden_container').empty();
        cotizacionesSeleccionadas = [];

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                var html = '<div class="card mb-2 bien-orden-row" data-id="' + bien.IdOrdenCompraXbien + '">';
                html += '<div class="card-body p-2">';
                html += '<div class="row">';
                html += '<div class="col-md-4">';
                html += '<strong>Bien:</strong> ' + (bien.bien ? bien.bien.NombreB : '-') + '<br>';
                html += '</div>';
                html += '<div class="col-md-2">';
                html += '<strong>Cantidad:</strong> ' + bien.Cantidad + '<br>';
                html += '</div>';
                html += '<div class="col-md-2">';
                html += '<strong>Precio:</strong> $' + parseFloat(bien.Precio).toFixed(2) + '<br>';
                html += '</div>';
                html += '<div class="col-md-2">';
                html += '<strong>Total:</strong> $' + parseFloat(bien.Total).toFixed(2) + '<br>';
                html += '</div>';
                html += '<div class="col-md-2" style="text-align: right;">';
                html += '<button type="button" onclick="eliminarBienOrden(' + bien.IdOrdenCompraXbien + ')" class="btn btn-danger btn-xs">';
                html += '<i class="fas fa-trash"></i> Eliminar';
                html += '</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#bienes_orden_container').append(html);

                cotizacionesSeleccionadas.push({
                    IdOrdenCompraXbien: bien.IdOrdenCompraXbien,
                    cotizacion_id: bien.Cotizacion_Id,
                    Bien_Id: bien.BienIdPart || (bien.cotizacion ? bien.cotizacion.pedidoInternoXBien.bien.IdBien : null),
                    Cantidad: bien.Cantidad,
                    Cuenta_Id: bien.Cuenta_Id,
                });
            });
        }
    });
}

function eliminar() {
    apiLaravel.exec('ordenes-compra/' + idEdit, 'DELETE', {}, function(response) {
        if (response.success) {
            alert(response.message);
            limpiar();
            refrescarTabla();
        } else {
            alert(response.message);
        }
    });
}

function eliminarBienOrden(id) {
    if (confirm('¿Está seguro que desea eliminar este bien?')) {
        apiLaravel.exec('ordenes-compra/eliminar-bien/' + id, 'DELETE', {}, function(response) {
            if (response.success) {
                alert(response.message);
                $('.bien-orden-row[data-id="' + id + '"]').remove();
                
                cotizacionesSeleccionadas = cotizacionesSeleccionadas.filter(function(item) {
                    return item.IdOrdenCompraXbien != id;
                });
            } else {
                alert(response.message);
            }
        });
    }
}

function verDetalle(id) {
    apiLaravel.get('ordenes-compra/' + id, function(data) {
        if (!data) {
            alert('Orden no encontrada');
            return;
        }

        var html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<strong>Número:</strong> ' + data.IdOrdenCompra + '<br>';
        html += '<strong>Estado:</strong> ' + (data.estado ? data.estado.Estado : '-') + '<br>';
        html += '<strong>Autorizado:</strong> ' + (data.Autorizado == 1 ? 'Sí' : 'No') + '<br>';
        html += '<strong>Plazo:</strong> ' + (data.Plazo || '-') + '<br>';
        html += '<strong>Lugar:</strong> ' + (data.Lugar || '-') + '<br>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<strong>Proveedor:</strong> ' + (data.proveedor ? data.proveedor.Proveedor : '-') + '<br>';
        html += '<strong>Cuenta:</strong> ' + (data.cuenta ? data.cuenta.Nombre_Cuenta : '-') + '<br>';
        html += '<strong>Creado por:</strong> ' + (data.creador ? data.creador.Nombre + ' ' + data.creador.Apellido : '-') + '<br>';
        html += '<strong>Fecha:</strong> ' + data.FechaCreacion + '<br>';
        html += '<strong>Contra entrega:</strong> ' + (data.ContraEntrega == 1 ? 'Sí' : 'No') + '<br>';
        html += '</div>';
        html += '</div>';

        html += '<hr>';
        html += '<h5>Bienes de la Orden</h5>';
        html += '<table class="table table-sm table-bordered">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Bien</th>';
        html += '<th>Cantidad</th>';
        html += '<th>Precio</th>';
        html += '<th>Total</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                var bienNombre = bien.bien ? bien.bien.NombreB : (bien.cotizacion && bien.cotizacion.pedidoInternoXBien.bien ? bien.cotizacion.pedidoInternoXBien.bien.NombreB : '-');
                html += '<tr>';
                html += '<td>' + bienNombre + '</td>';
                html += '<td>' + bien.Cantidad + '</td>';
                html += '<td>$' + parseFloat(bien.Precio).toFixed(2) + '</td>';
                html += '<td>$' + parseFloat(bien.Total).toFixed(2) + '</td>';
                html += '</tr>';
            });
        } else {
            html += '<tr><td colspan="5" class="text-center">No hay bienes</td></tr>';
        }

        html += '</tbody>';
        html += '</table>';

        $('#detalle_orden_content').html(html);
        $('#modal_ver_detalle').modal();
    });
}

function seleccionarPedidoInterno(id) {
    piSeleccionadoId = id;
    
    apiLaravel.get('pedidos-internos/' + id, function(data) {
        if (!data.pedido) {
            alert('Pedido interno no encontrado');
            return;
        }

        $('#seleccionar_pi_id').val(id);

        var html = '<h5>Pedido Interno Nº ' + data.pedido.IdPedidoInterno + '</h5>';
        html += '<p><strong>Solicitante:</strong> ' + (data.pedido.servicioSolicitante ? data.pedido.servicioSolicitante.Servicio : '-') + '</p>';
        html += '<p><strong>Destino:</strong> ' + (data.pedido.servicioDestino ? data.pedido.servicioDestino.Servicio : '-') + '</p>';
        
        if (data.bienes && data.bienes.length > 0) {
            html += '<hr>';
            html += '<h6>Cotizaciones Disponibles</h6>';
            html += '<div class="row">';
            
            data.bienes.forEach(function(bien) {
                html += '<div class="col-md-6 mb-3">';
                html += '<div class="card">';
                html += '<div class="card-header bg-info">';
                html += '<strong>' + (bien.bien ? bien.bien.NombreB : '-') + '</strong>';
                html += '</div>';
                html += '<div class="card-body p-2">';
                html += '<p><strong>Cantidad solicitada:</strong> ' + bien.Cantidad + '</p>';
                html += '<p><strong>Detalle:</strong> ' + (bien.Detalle || '-') + '</p>';
                
                if (bien.cotizaciones && bien.cotizaciones.length > 0) {
                    html += '<div class="listado-cotizaciones bien-id-' + bien.IdPedidoInternoXbien + '">';
                    bien.cotizaciones.forEach(function(coti) {
                        if (coti.ordenCompraXBienes && coti.ordenCompraXBienes.length == 0) {
                            html += '<div class="form-check mb-1">';
                            html += '<input type="checkbox" class="form-check-input cotizacion-check" data-id="' + coti.IdCotizacion + '" data-bien="' + bien.IdPedidoInternoXbien + '">';
                            html += '<label class="form-check-label">';
                            html += '<strong>' + (coti.proveedor ? coti.proveedor.Proveedor : '-') + '</strong>';
                            html += ' - Cantidad: ' + coti.Cantidad;
                            html += ' - Precio: $' + parseFloat(coti.Precio).toFixed(2);
                            html += ' - Subtotal: $' + parseFloat(coti.Subtotal).toFixed(2);
                            if (coti.Prem == 1) {
                                html += ' <span class="badge badge-success">Ganador</span>';
                            }
                            html += '</label>';
                            html += '</div>';
                        }
                    });
                    html += '</div>';
                } else {
                    html += '<p class="text-muted">No hay cotizaciones disponibles</p>';
                }
                
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
            
            html += '</div>';
        }

        $('#seleccionar_cotizaciones_content').html(html);
        $('#modal_seleccionar_cotizaciones').modal();
    });
}

function seleccionarCotizaciones() {
    var checkboxes = document.querySelectorAll('.cotizacion-check:checked');
    var cotizaciones = [];

    checkboxes.forEach(function(checkbox) {
        cotizaciones.push({
            cotizacion_id: checkbox.dataset.id,
            Bien_Id: checkbox.dataset.bien,
            cantidad: 1,
        });
    });

    if (cotizaciones.length == 0) {
        alert('Debe seleccionar al menos una cotización');
        return;
    }

    apiLaravel.exec('ordenes-compra', 'POST', {
        cotizaciones: cotizaciones,
        Cuenta_Id: $('#Cuenta_Id').val(),
        Estado_Id: $('#Estado_Id').val(),
        Autorizado: $('#Autorizado').val(),
        Plazo: $('#Plazo').val(),
        Lugar: $('#Lugar').val(),
        ContraEntrega: $('#ContraEntrega').val(),
        Obs: $('#Obs').val(),
    }, function(response) {
        if (response.success) {
            alert(response.message);
            $('#modal_seleccionar_cotizaciones').modal('hide');
            limpiar();
            refrescarTabla();
        } else {
            alert(response.message);
        }
    });
}

function refrescarTabla() {
    var params = $('.card form').serialize();
    apiLaravel.get('ordenes-compra/get-ordenes-compra?' + params, function(data) {
        dataTable = data.registros;

        var htmlTable = '';
        dataTable.forEach(function(registro) {
            htmlTable += '<tr>';
            htmlTable += '<td>' + registro.IdOrdenCompra + '</td>';
            htmlTable += '<td>' + (registro.proveedor ? registro.proveedor.Proveedor : '-') + '</td>';
            htmlTable += '<td>' + (registro.cuenta ? registro.cuenta.Nombre_Cuenta : '-') + '</td>';
            htmlTable += '<td>' + (registro.estado ? registro.estado.Estado : '-') + '</td>';
            htmlTable += '<td>' + registro.FechaCreacion + '</td>';
            htmlTable += '<td>$' + (registro.Total ? parseFloat(registro.Total).toFixed(2) : '-') + '</td>';
            htmlTable += '<td>';
            htmlTable += '<div class="btn-group">';
            htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdOrdenCompra + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
            htmlTable += '<button type="button" onclick="editar(' + registro.IdOrdenCompra + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
            htmlTable += '</div>';
            htmlTable += '</td>';
            htmlTable += '</tr>';
        });

        $('#table_data').html(htmlTable);
        $('#total_info').html('Total: ' + data.total + ' registro(s)');

        var htmlPage = '<select id="cbopaginas">';
        for (var i = 0; i < data.paginas; i++) {
            var pagina = i + 1;
            var selected = (i == 0) ? 'selected="selected"' : '';
            htmlPage += '<option value="' + pagina + '" ' + selected + '>' + pagina + '</option>';
        }
        htmlPage += '</select>';
        $('#page-selection_num_page').html(htmlPage);

        $('#cbopaginas').change(function() {
            var pagina = (this.value - 1) * 10;
            var busqueda = $('form').serialize();
            apiLaravel.get('ordenes-compra/get-ordenes-compra?Pagina=' + pagina + '&' + busqueda, function(data) {
                dataTable = data.registros;
                var htmlTable = '';
                dataTable.forEach(function(registro) {
                    htmlTable += '<tr>';
                    htmlTable += '<td>' + registro.IdOrdenCompra + '</td>';
                    htmlTable += '<td>' + (registro.proveedor ? registro.proveedor.Proveedor : '-') + '</td>';
                    htmlTable += '<td>' + (registro.cuenta ? registro.cuenta.Nombre_Cuenta : '-') + '</td>';
                    htmlTable += '<td>' + (registro.estado ? registro.estado.Estado : '-') + '</td>';
                    htmlTable += '<td>' + registro.FechaCreacion + '</td>';
                    htmlTable += '<td>$' + (registro.Total ? parseFloat(registro.Total).toFixed(2) : '-') + '</td>';
                    htmlTable += '<td>';
                    htmlTable += '<div class="btn-group">';
                    htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdOrdenCompra + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
                    htmlTable += '<button type="button" onclick="editar(' + registro.IdOrdenCompra + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
                    htmlTable += '</div>';
                    htmlTable += '</td>';
                    htmlTable += '</tr>';
                });
                $('#table_data').html(htmlTable);
            });
        });
    });
}
