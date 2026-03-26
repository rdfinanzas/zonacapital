var idEdit = 0;
var dataTable;
var bienesSeleccionados = [];
var remitosSeleccionados = [];

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
            alert('Debe seleccionar una orden de pago');
            return;
        }
        if (confirm('¿Está seguro que desea eliminar esta orden de pago?')) {
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

    apiLaravel.get('expedientes/get-expedientes', function(data) {
        var options = '<option value="">-SELECCIONAR-</option>';
        data.forEach(function(exp) {
            options += '<option value="' + exp.IdExp + '">' + exp.Numero + '</option>';
        });
        $('#NumExp_Id').html(options);
    });

    refrescarTabla();
});

function limpiar() {
    idEdit = 0;
    $('#IdOrdenPago').val(0);
    $('#form_main')[0].reset();
    $('#bienes_orden_container').empty();
    $('#remitos_container').empty();
    bienesSeleccionados = [];
    remitosSeleccionados = [];
    $('#Total').val(0);
}

function guardar() {
    var data = {
        Numero: $('#Numero').val(),
        FechaEmicion: $('#FechaEmicion').val() || null,
        Proveedor_Id: $('#Proveedor_Id').val(),
        Cuenta_Id: $('#Cuenta_Id').val(),
        Estado_Id: $('#Estado_Id').val(),
        NumExp_Id: $('#NumExp_Id').val(),
        Obs: $('#Obs').val(),
        Total: $('#Total').val() || 0,
        bienes: bienesSeleccionados,
        remitos: remitosSeleccionados,
    };

    var url = 'ordenes-pago';
    var method = 'POST';

    if ($('#IdOrdenPago').val() > 0) {
        url = 'ordenes-pago/' + $('#IdOrdenPago').val();
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

    apiLaravel.get('ordenes-pago/' + id, function(data) {
        if (!data) {
            alert('Orden de pago no encontrada');
            return;
        }

        $('#IdOrdenPago').val(data.IdOrdenPago);
        $('#Numero').val(data.Numero || '');
        $('#FechaEmicion').val(data.FechaEmicion ? data.FechaEmicion.split(' ')[0] : '');
        $('#Proveedor_Id').val(data.Proveedor_Id).trigger('change');
        $('#Cuenta_Id').val(data.Cuenta_Id);
        $('#Estado_Id').val(data.Estado_Id);
        $('#NumExp_Id').val(data.NumExp_Id);
        $('#Obs').val(data.Obs || '');
        $('#Total').val(data.Total || 0);

        $('#bienes_orden_container').empty();
        bienesSeleccionados = [];

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                var html = '<div class="card mb-2 bien-orden-row" data-id="' + bien.IdOrdenPagoXB + '">';
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
                html += '<button type="button" onclick="eliminarBienOrden(' + bien.IdOrdenPagoXB + ')" class="btn btn-danger btn-xs">';
                html += '<i class="fas fa-trash"></i> Eliminar';
                html += '</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#bienes_orden_container').append(html);

                bienesSeleccionados.push({
                    IdOrdenPagoXB: bien.IdOrdenPagoXB,
                    Bien_Id: bien.Bien_Id,
                    Cantidad: bien.Cantidad,
                    Precio: bien.Precio,
                });
            });
        }

        $('#remitos_container').empty();
        remitosSeleccionados = [];

        if (data.remitos && data.remitos.length > 0) {
            data.remitos.forEach(function(remito) {
                var html = '<div class="card mb-2 remito-row" data-id="' + remito.IdRemito + '">';
                html += '<div class="card-body p-2">';
                html += '<div class="row">';
                html += '<div class="col-md-8">';
                html += '<strong>Número:</strong> ' + remito.Numero + '<br>';
                html += '</div>';
                html += '<div class="col-md-4" style="text-align: right;">';
                html += '<button type="button" onclick="eliminarRemitoOrden(' + remito.IdRemito + ')" class="btn btn-danger btn-xs">';
                html += '<i class="fas fa-trash"></i> Eliminar';
                html += '</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#remitos_container').append(html);

                remitosSeleccionados.push({
                    IdRemito: remito.IdRemito,
                    Numero: remito.Numero,
                });
            });
        }
    });
}

function eliminar() {
    apiLaravel.exec('ordenes-pago/' + idEdit, 'DELETE', {}, function(response) {
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
    apiLaravel.get('ordenes-pago/' + id, function(data) {
        if (!data) {
            alert('Orden de pago no encontrada');
            return;
        }

        var html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<strong>Número:</strong> ' + (data.Numero || '-') + '<br>';
        html += '<strong>Fecha Emisión:</strong> ' + (data.FechaEmicion || '-') + '<br>';
        html += '<strong>Observaciones:</strong> ' + (data.Obs || '-') + '<br>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<strong>Proveedor:</strong> ' + (data.proveedor ? data.proveedor.Proveedor : '-') + '<br>';
        html += '<strong>Cuenta:</strong> ' + (data.cuenta ? data.cuenta.Nombre_Cuenta : '-') + '<br>';
        html += '<strong>Estado:</strong> ' + (data.estado ? data.estado.Estado : '-') + '<br>';
        html += '<strong>Expediente:</strong> ' + (data.expediente ? data.expediente.Numero : '-') + '<br>';
        html += '<strong>Creado por:</strong> ' + (data.creador ? data.creador.Nombre + ' ' + data.creador.Apellido : '-') + '<br>';
        html += '</div>';
        html += '</div>';

        html += '<hr>';
        html += '<h5>Bienes de la Orden de Pago</h5>';
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
            html += '<tr><td colspan="4" class="text-center">No hay bienes</td></tr>';
        }

        html += '</tbody>';
        html += '</table>';

        html += '<hr>';
        html += '<h5>Remitos</h5>';
        html += '<table class="table table-sm table-bordered">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Número</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        if (data.remitos && data.remitos.length > 0) {
            data.remitos.forEach(function(remito) {
                html += '<tr>';
                html += '<td>' + remito.Numero + '</td>';
                html += '</tr>';
            });
        } else {
            html += '<tr><td colspan="1" class="text-center">No hay remitos</td></tr>';
        }

        html += '</tbody>';
        html += '</table>';

        $('#detalle_orden_content').html(html);
        $('#modal_ver_detalle').modal();
    });
}

function refrescarTabla() {
    var params = $('.card form').serialize();
    apiLaravel.get('ordenes-pago/get-ordenes-pago?' + params, function(data) {
        dataTable = data.registros;

        var htmlTable = '';
        dataTable.forEach(function(registro) {
            htmlTable += '<tr>';
            htmlTable += '<td>' + (registro.Numero || '-') + '</td>';
            htmlTable += '<td>' + (registro.FechaCreacion ? registro.FechaCreacion.split(' ')[0] : '-') + '</td>';
            htmlTable += '<td>' + (registro.proveedor ? registro.proveedor.Proveedor : '-') + '</td>';
            htmlTable += '<td>' + (registro.cuenta ? registro.cuenta.Nombre_Cuenta : '-') + '</td>';
            htmlTable += '<td>' + (registro.estado ? registro.estado.Estado : '-') + '</td>';
            htmlTable += '<td>$' + (registro.Total ? parseFloat(registro.Total).toFixed(2) : '-') + '</td>';
            htmlTable += '<td>';
            htmlTable += '<div class="btn-group">';
            htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdOrdenPago + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
            htmlTable += '<button type="button" onclick="editar(' + registro.IdOrdenPago + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
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
            apiLaravel.get('ordenes-pago/get-ordenes-pago?Pagina=' + pagina + '&' + busqueda, function(data) {
                dataTable = data.registros;
                var htmlTable = '';
                dataTable.forEach(function(registro) {
                    htmlTable += '<tr>';
                    htmlTable += '<td>' + (registro.Numero || '-') + '</td>';
                    htmlTable += '<td>' + (registro.FechaCreacion ? registro.FechaCreacion.split(' ')[0] : '-') + '</td>';
                    htmlTable += '<td>' + (registro.proveedor ? registro.proveedor.Proveedor : '-') + '</td>';
                    htmlTable += '<td>' + (registro.cuenta ? registro.cuenta.Nombre_Cuenta : '-') + '</td>';
                    htmlTable += '<td>' + (registro.estado ? registro.estado.Estado : '-') + '</td>';
                    htmlTable += '<td>$' + (registro.Total ? parseFloat(registro.Total).toFixed(2) : '-') + '</td>';
                    htmlTable += '<td>';
                    htmlTable += '<div class="btn-group">';
                    htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdOrdenPago + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
                    htmlTable += '<button type="button" onclick="editar(' + registro.IdOrdenPago + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
                    htmlTable += '</div>';
                    htmlTable += '</td>';
                    htmlTable += '</tr>';
                });
                $('#table_data').html(htmlTable);
            });
        });
    });
}

function agregarBienOrden() {
    if ($('#IdOrdenPago').val() == 0) {
        alert('Debe guardar la orden de pago primero');
        return;
    }

    var html = '<div class="modal fade" id="modal_agregar_bien" tabindex="-1">';
    html += '<div class="modal-dialog">';
    html += '<div class="modal-content">';
    html += '<div class="modal-header">';
    html += '<h5 class="modal-title">Agregar Bien a la Orden de Pago</h5>';
    html += '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>';
    html += '</div>';
    html += '<div class="modal-body">';
    html += '<div class="form-group">';
    html += '<label>Buscar Bien:</label>';
    html += '<input type="text" class="form-control" id="bien_buscar_orden" placeholder="Buscar bien...">';
    html += '</div>';
    html += '<div class="form-group">';
    html += '<label>Cantidad:</label>';
    html += '<input type="number" class="form-control" id="bien_cantidad_orden" value="1" min="1">';
    html += '</div>';
    html += '<div class="form-group">';
    html += '<label>Precio:</label>';
    html += '<input type="number" step="0.01" class="form-control" id="bien_precio_orden" value="0">';
    html += '</div>';
    html += '<div class="form-group">';
    html += '<label>Observación:</label>';
    html += '<input type="text" class="form-control" id="bien_obs_orden">';
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
        var bienId = $('#bien_buscar_orden').data('bien-id');
        var cantidad = $('#bien_cantidad_orden').val();
        var precio = $('#bien_precio_orden').val();
        var obs = $('#bien_obs_orden').val();

        if (!bienId) {
            alert('Debe seleccionar un bien');
            return;
        }

        apiLaravel.exec('ordenes-pago/agregar-bien-orden', 'POST', {
            OrdenPago_Id: $('#IdOrdenPago').val(),
            Bien_Id: bienId,
            Cantidad: cantidad,
            Precio: precio,
            Obs: obs,
        }, function(response) {
            if (response.success) {
                alert(response.message);
                $('#modal_agregar_bien').modal('hide');
                $('#modal_agregar_bien').remove();
                actualizarTotal();
            } else {
                alert(response.message);
            }
        });
    });

    $('#bien_buscar_orden').autocomplete({
        source: function(request, response) {
            apiLaravel.get('ordenes-pago/get-bienes?Bienes=' + encodeURIComponent(request.term), function(data) {
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
            $('#bien_buscar_orden').data('bien-id', ui.item.value);
        }
    });
}

function eliminarBienOrden(id) {
    if (confirm('¿Está seguro que desea eliminar este bien?')) {
        apiLaravel.exec('ordenes-pago/eliminar-bien-orden/' + id, 'DELETE', {}, function(response) {
            if (response.success) {
                alert(response.message);
                $('.bien-orden-row[data-id="' + id + '"]').remove();
                actualizarTotal();
            } else {
                alert(response.message);
            }
        });
    }
}

function agregarRemitoOrden() {
    if ($('#IdOrdenPago').val() == 0) {
        alert('Debe guardar la orden de pago primero');
        return;
    }

    var html = '<div class="modal fade" id="modal_agregar_remito" tabindex="-1">';
    html += '<div class="modal-dialog">';
    html += '<div class="modal-content">';
    html += '<div class="modal-header">';
    html += '<h5 class="modal-title">Agregar Remito</h5>';
    html += '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>';
    html += '</div>';
    html += '<div class="modal-body">';
    html += '<div class="form-group">';
    html += '<label>Número de Remito:</label>';
    html += '<input type="text" class="form-control" id="remito_numero" placeholder="Ingrese número de remito...">';
    html += '</div>';
    html += '</div>';
    html += '<div class="modal-footer">';
    html += '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
    html += '<button type="button" id="btn_guardar_remito" class="btn btn-primary">Agregar</button>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    $('body').append(html);
    $('#modal_agregar_remito').modal();

    $('#btn_guardar_remito').click(function() {
        var numero = $('#remito_numero').val();

        if (!numero) {
            alert('Debe ingresar un número de remito');
            return;
        }

        apiLaravel.exec('ordenes-pago/agregar-remito-orden', 'POST', {
            OrdenPago_Id: $('#IdOrdenPago').val(),
            Numero: numero,
        }, function(response) {
            if (response.success) {
                alert(response.message);
                $('#modal_agregar_remito').modal('hide');
                $('#modal_agregar_remito').remove();
                editar($('#IdOrdenPago').val());
            } else {
                alert(response.message);
            }
        });
    });
}

function eliminarRemitoOrden(id) {
    if (confirm('¿Está seguro que desea eliminar este remito?')) {
        apiLaravel.exec('ordenes-pago/eliminar-remito-orden/' + id, 'DELETE', {}, function(response) {
            if (response.success) {
                alert(response.message);
                $('.remito-row[data-id="' + id + '"]').remove();
            } else {
                alert(response.message);
            }
        });
    }
}

function actualizarTotal() {
    if ($('#IdOrdenPago').val() == 0) {
        return;
    }

    apiLaravel.exec('ordenes-pago/actualizar-total-orden/' + $('#IdOrdenPago').val(), 'PUT', {}, function(response) {
        if (response.success) {
            $('#Total').val(response.total);
            refrescarTabla();
        }
    });
}

$(document).on('click', '#btn_agregar_bien', function() {
    agregarBienOrden();
});

$(document).on('click', '#btn_agregar_remito', function() {
    agregarRemitoOrden();
});
