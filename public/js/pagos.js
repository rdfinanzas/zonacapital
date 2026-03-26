var idEdit = 0;
var dataTable;
var bienesSeleccionados = [];

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
            alert('Debe seleccionar un pago');
            return;
        }
        if (confirm('¿Está seguro que desea eliminar este pago?')) {
            eliminar();
        }
    });

    apiLaravel.get('proveedores/get-todos', function(data) {
        var options = '<option value="">-SELECCIONAR-</option>';
        data.forEach(function(prov) {
            options += '<option value="' + prov.IdProveedor + '">' + prov.Proveedor + '</option>';
        });
        $('#Proveedor_Id').html(options);
        $('#BuscarProveedor').html('<option value="">-TODOS-</option>' + options);
    });

    refrescarTabla();
});

function limpiar() {
    idEdit = 0;
    $('#IdPago').val(0);
    $('#form_main')[0].reset();
    $('#bienes_pago_container').empty();
    bienesSeleccionados = [];
    $('#Total').val(0);
}

function guardar() {
    var data = {
        NroCD: $('#NroCD').val(),
        FechaCheque: $('#FechaCheque').val() || null,
        Disposicion: $('#Disposicion').val(),
        Cheque: $('#Cheque').val(),
        Factura: $('#Factura').val(),
        NumLote: $('#NumLote').val(),
        Destino: $('#Destino').val(),
        Concepto: $('#Concepto').val(),
        Proveedor_Id: $('#Proveedor_Id').val(),
        Cuenta_Id: $('#Cuenta_Id').val(),
        Servicio_Id: $('#Servicio_Id').val(),
        Servicio_Otro: $('#Servicio_Otro').val(),
        Estado_Id: $('#Estado_Id').val(),
        RetencionDGR: $('#RetencionDGR').val() || 0,
        RetencionDGI: $('#RetencionDGI').val() || 0,
        Total: $('#Total').val() || 0,
        bienes: bienesSeleccionados,
    };

    var url = 'pagos';
    var method = 'POST';

    if ($('#IdPago').val() > 0) {
        url = 'pagos/' + $('#IdPago').val();
        method = 'PUT';
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

    apiLaravel.get('pagos/' + id, function(data) {
        if (!data) {
            alert('Pago no encontrado');
            return;
        }

        $('#IdPago').val(data.IdPago);
        $('#NroCD').val(data.NroCD || '');
        $('#FechaCheque').val(data.FechaCheque ? data.FechaCheque.split(' ')[0] : '');
        $('#Disposicion').val(data.Disposicion || '');
        $('#Cheque').val(data.Cheque || '');
        $('#Factura').val(data.Factura || '');
        $('#NumLote').val(data.NumLote || '');
        $('#Destino').val(data.Destino || '');
        $('#Concepto').val(data.Concepto || '');
        $('#Proveedor_Id').val(data.Proveedor_Id).trigger('change');
        $('#Cuenta_Id').val(data.Cuenta_Id);
        $('#Servicio_Id').val(data.Servicio_Id).trigger('change');
        $('#Servicio_Otro').val(data.Servicio_Otro || '');
        $('#Estado_Id').val(data.Estado_Id);
        $('#RetencionDGR').val(data.RetencionDGR || 0);
        $('#RetencionDGI').val(data.RetencionDGI || 0);
        $('#Total').val(data.Total || 0);

        $('#bienes_pago_container').empty();
        bienesSeleccionados = [];

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                var html = '<div class="card mb-2 bien-pago-row" data-id="' + bien.IdCDXB + '">';
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
                html += '<strong>Subtotal:</strong> $' + parseFloat(bien.Subtotal).toFixed(2) + '<br>';
                html += '</div>';
                html += '<div class="col-md-2" style="text-align: right;">';
                html += '<button type="button" onclick="eliminarBienPago(' + bien.IdCDXB + ')" class="btn btn-danger btn-xs">';
                html += '<i class="fas fa-trash"></i> Eliminar';
                html += '</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#bienes_pago_container').append(html);

                bienesSeleccionados.push({
                    IdCDXB: bien.IdCDXB,
                    Bien_Id: bien.Bien_Id,
                    Bien_Red: bien.Bien_Red || null,
                    Proforma: bien.Proforma || '',
                    Cantidad: bien.Cantidad,
                    Precio: bien.Precio,
                });
            });
        }
    });
}

function eliminar() {
    apiLaravel.exec('pagos/' + idEdit, 'DELETE', {}, function(response) {
        if (response.success) {
            alert(response.message);
            limpiar();
            refrescarTabla();
        } else {
            alert(response.message);
        }
    });
}

function verDetalle(id) {
    apiLaravel.get('pagos/' + id, function(data) {
        if (!data) {
            alert('Pago no encontrado');
            return;
        }

        var html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<strong>Número CD:</strong> ' + (data.NroCD || '-') + '<br>';
        html += '<strong>Fecha Cheque:</strong> ' + (data.FechaCheque || '-') + '<br>';
        html += '<strong>Disposición:</strong> ' + (data.Disposicion || '-') + '<br>';
        html += '<strong>Cheque:</strong> ' + (data.Cheque || '-') + '<br>';
        html += '<strong>Factura:</strong> ' + (data.Factura || '-') + '<br>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<strong>Proveedor:</strong> ' + (data.proveedor ? data.proveedor.Proveedor : '-') + '<br>';
        html += '<strong>Cuenta:</strong> ' + (data.cuenta ? data.cuenta.Nombre_Cuenta : '-') + '<br>';
        html += '<strong>Servicio:</strong> ' + (data.servicio ? data.servicio.Servicio : '-') + ' ' + (data.Servicio_Otro || '') + '<br>';
        html += '<strong>Estado:</strong> ' + (data.estado ? data.estado.Estado : '-') + '<br>';
        html += '<strong>Creado por:</strong> ' + (data.creador ? data.creador.Nombre + ' ' + data.creador.Apellido : '-') + '<br>';
        html += '</div>';
        html += '</div>';

        html += '<hr>';
        html += '<h5>Bienes del Pago</h5>';
        html += '<table class="table table-sm table-bordered">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Bien</th>';
        html += '<th>Cantidad</th>';
        html += '<th>Precio</th>';
        html += '<th>Subtotal</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                var bienNombre = bien.bien ? bien.bien.NombreB : '-';
                html += '<tr>';
                html += '<td>' + bienNombre + '</td>';
                html += '<td>' + bien.Cantidad + '</td>';
                html += '<td>$' + parseFloat(bien.Precio).toFixed(2) + '</td>';
                html += '<td>$' + parseFloat(bien.Subtotal).toFixed(2) + '</td>';
                html += '</tr>';
            });
        } else {
            html += '<tr><td colspan="5" class="text-center">No hay bienes</td></tr>';
        }

        html += '</tbody>';
        html += '</table>';

        $('#detalle_pago_content').html(html);
        $('#modal_ver_detalle').modal();
    });
}

function refrescarTabla() {
    var params = $('.card form').serialize();
    apiLaravel.get('pagos/get-pagos?' + params, function(data) {
        dataTable = data.registros;

        var htmlTable = '';
        dataTable.forEach(function(registro) {
            htmlTable += '<tr>';
            htmlTable += '<td>' + (registro.NroCD || '-') + '</td>';
            htmlTable += '<td>' + (registro.FechaCheque || '-') + '</td>';
            htmlTable += '<td>' + (registro.proveedor ? registro.proveedor.Proveedor : '-') + '</td>';
            htmlTable += '<td>' + (registro.cuenta ? registro.cuenta.Nombre_Cuenta : '-') + '</td>';
            htmlTable += '<td>' + (registro.estado ? registro.estado.Estado : '-') + '</td>';
            htmlTable += '<td>$' + (registro.Total ? parseFloat(registro.Total).toFixed(2) : '-') + '</td>';
            htmlTable += '<td>';
            htmlTable += '<div class="btn-group">';
            htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdPago + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
            htmlTable += '<button type="button" onclick="editar(' + registro.IdPago + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
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
            apiLaravel.get('pagos/get-pagos?Pagina=' + pagina + '&' + busqueda, function(data) {
                dataTable = data.registros;
                var htmlTable = '';
                dataTable.forEach(function(registro) {
                    htmlTable += '<tr>';
                    htmlTable += '<td>' + (registro.NroCD || '-') + '</td>';
                    htmlTable += '<td>' + (registro.FechaCheque || '-') + '</td>';
                    htmlTable += '<td>' + (registro.proveedor ? registro.proveedor.Proveedor : '-') + '</td>';
                    htmlTable += '<td>' + (registro.cuenta ? registro.cuenta.Nombre_Cuenta : '-') + '</td>';
                    htmlTable += '<td>' + (registro.estado ? registro.estado.Estado : '-') + '</td>';
                    htmlTable += '<td>$' + (registro.Total ? parseFloat(registro.Total).toFixed(2) : '-') + '</td>';
                    htmlTable += '<td>';
                    htmlTable += '<div class="btn-group">';
                    htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdPago + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
                    htmlTable += '<button type="button" onclick="editar(' + registro.IdPago + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
                    htmlTable += '</div>';
                    htmlTable += '</td>';
                    htmlTable += '</tr>';
                });
                $('#table_data').html(htmlTable);
            });
        });
    });
}

function agregarBienPago() {
    if ($('#IdPago').val() == 0) {
        alert('Debe guardar el pago primero');
        return;
    }

    var html = '<div class="modal fade" id="modal_agregar_bien" tabindex="-1">';
    html += '<div class="modal-dialog">';
    html += '<div class="modal-content">';
    html += '<div class="modal-header">';
    html += '<h5 class="modal-title">Agregar Bien al Pago</h5>';
    html += '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>';
    html += '</div>';
    html += '<div class="modal-body">';
    html += '<div class="form-group">';
    html += '<label>Buscar Bien:</label>';
    html += '<input type="text" class="form-control" id="bien_buscar_pago" placeholder="Buscar bien...">';
    html += '</div>';
    html += '<div class="form-group">';
    html += '<label>Cantidad:</label>';
    html += '<input type="number" class="form-control" id="bien_cantidad_pago" value="1" min="1">';
    html += '</div>';
    html += '<div class="form-group">';
    html += '<label>Precio:</label>';
    html += '<input type="number" step="0.01" class="form-control" id="bien_precio_pago" value="0">';
    html += '</div>';
    html += '</div>';
    html += '<div class="modal-footer">';
    html += '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
    html += '<button type="button" id="btn_guardar_bien" class="btn btn-primary">Agregar</button>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    $('body').append(html);
    $('#modal_agregar_bien').modal();

    $('#btn_guardar_bien').click(function() {
        var bienId = $('#bien_buscar_pago').data('bien-id');
        var cantidad = $('#bien_cantidad_pago').val();
        var precio = $('#bien_precio_pago').val();

        if (!bienId) {
            alert('Debe seleccionar un bien');
            return;
        }

        apiLaravel.exec('pagos/agregar-bien-pago', 'POST', {
            CompraDirecta_Id: $('#IdPago').val(),
            Bien_Id: bienId,
            Cantidad: cantidad,
            Precio: precio,
        }, function(response) {
            if (response.success) {
                alert(response.message);
                $('#modal_agregar_bien').modal('hide');
                $('#modal_agregar_bien').remove();
            } else {
                alert(response.message);
            }
        });
    });

    $('#bien_buscar_pago').autocomplete({
        source: function(request, response) {
            apiLaravel.get('pagos/get-bienes?Bienes=' + encodeURIComponent(request.term), function(data) {
                var items = [];
                data.forEach(function(bien) {
                    items.push({
                        label: bien.NombreB,
                        value: bien.IdBien
                    });
                });
                response(items);
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $('#bien_buscar_pago').data('bien-id', ui.item.value);
        }
    });
}

function eliminarBienPago(id) {
    if (confirm('¿Está seguro que desea eliminar este bien?')) {
        apiLaravel.exec('pagos/eliminar-bien-pago/' + id, 'DELETE', {}, function(response) {
            if (response.success) {
                alert(response.message);
                $('.bien-pago-row[data-id="' + id + '"]').remove();
                
                bienesSeleccionados = bienesSeleccionados.filter(function(item) {
                    return item.IdCDXB != id;
                });
            } else {
                alert(response.message);
            }
        });
    }
}
