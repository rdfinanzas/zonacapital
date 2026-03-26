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
            alert('Debe seleccionar un pedido');
            return;
        }
        if (confirm('¿Está seguro que desea eliminar este pedido?')) {
            eliminar();
        }
    });

    agregarBienRow();

    $(document).on('focus', '.bien-buscar', function() {
        var input = $(this);
        input.autocomplete({
            source: function(request, response) {
                apiLaravel.get('pedidos-internos/get-bienes?Bienes=' + encodeURIComponent(request.term), function(data) {
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
                input.closest('.bien-row').find('.bien-buscar').data('bien-id', ui.item.value);
            }
        });
    });

    refrescarTabla();
});

function agregarBienRow() {
    var html = '<div class="row bien-row mt-2">';
    html += '<div class="col-md-5">';
    html += '<div class="form-group">';
    html += '<label>Buscar Bien:</label>';
    html += '<input type="text" class="form-control bien-buscar" placeholder="Buscar bien...">';
    html += '</div>';
    html += '</div>';
    html += '<div class="col-md-3">';
    html += '<div class="form-group">';
    html += '<label>Cantidad:</label>';
    html += '<input type="number" class="form-control bien-cantidad" value="1" min="1">';
    html += '</div>';
    html += '</div>';
    html += '<div class="col-md-3">';
    html += '<div class="form-group">';
    html += '<label>Detalle:</label>';
    html += '<input type="text" class="form-control bien-detalle">';
    html += '</div>';
    html += '</div>';
    html += '<div class="col-md-1" style="padding-top: 30px;">';
    html += '<button type="button" class="btn btn-danger btn-xs" onclick="eliminarBienRow(this)">';
    html += '<i class="fas fa-trash"></i>';
    html += '</button>';
    html += '</div>';
    html += '</div>';

    $('#bienes_pedido_container').append(html);

    $('.bien-buscar').off('focus').on('focus', function() {
        var input = $(this);
        input.autocomplete({
            source: function(request, response) {
                apiLaravel.get('pedidos-internos/get-bienes?Bienes=' + encodeURIComponent(request.term), function(data) {
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
                input.data('bien-id', ui.item.value);
            }
        });
    });
}

function eliminarBienRow(boton) {
    $(boton).closest('.bien-row').remove();
}

function limpiar() {
    idEdit = 0;
    $('#IdPedidoInterno').val(0);
    $('#form_main')[0].reset();
    $('#bienes_pedido_container').empty();
    agregarBienRow();
}

function guardar() {
    var idsBienes = [];
    var cantidades = [];
    var detalles = [];

    $('.bien-row').each(function() {
        var bienId = $(this).find('.bien-buscar').data('bien-id');
        if (bienId) {
            idsBienes.push(bienId);
            cantidades.push($(this).find('.bien-cantidad').val());
            detalles.push($(this).find('.bien-detalle').val());
        }
    });

    var data = {
        ServicioSolicitanteId: $('#ServicioSolicitanteId').val(),
        ServicioDestinoId: $('#ServicioDestinoId').val(),
        EstadoId: $('#EstadoId').val(),
        Observacion: $('#Observacion').val(),
        IdsBienes: idsBienes,
        Cantidades: cantidades,
        Detalles: detalles,
        Num: idsBienes.length,
    };

    var url = 'pedidos-internos';
    var method = 'POST';

    if ($('#IdPedidoInterno').val() > 0) {
        url = 'pedidos-internos/' + $('#IdPedidoInterno').val();
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

    apiLaravel.get('pedidos-internos/' + id, function(data) {
        if (!data.pedido) {
            alert('Pedido no encontrado');
            return;
        }

        $('#IdPedidoInterno').val(data.pedido.IdPedidoInterno);
        $('#ServicioSolicitanteId').val(data.pedido.ServicioSolicitante_Id).trigger('change');
        $('#ServicioDestinoId').val(data.pedido.ServicioDestino_Id).trigger('change');
        $('#EstadoId').val(data.pedido.PedidoInternoEstado_Id);
        $('#Observacion').val(data.pedido.Observacion || '');

        $('#bienes_pedido_container').empty();

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                var html = '<div class="row bien-row mt-2">';
                html += '<div class="col-md-5">';
                html += '<div class="form-group">';
                html += '<label>Buscar Bien:</label>';
                html += '<input type="text" class="form-control bien-buscar" value="' + (bien.bien ? bien.bien.NombreB : '') + '" data-bien-id="' + bien.Bien_Id + '">';
                html += '</div>';
                html += '</div>';
                html += '<div class="col-md-3">';
                html += '<div class="form-group">';
                html += '<label>Cantidad:</label>';
                html += '<input type="number" class="form-control bien-cantidad" value="' + bien.Cantidad + '" min="1">';
                html += '</div>';
                html += '</div>';
                html += '<div class="col-md-3">';
                html += '<div class="form-group">';
                html += '<label>Detalle:</label>';
                html += '<input type="text" class="form-control bien-detalle" value="' + (bien.Detalle || '') + '">';
                html += '</div>';
                html += '</div>';
                html += '<div class="col-md-1" style="padding-top: 30px;">';
                html += '<button type="button" class="btn btn-danger btn-xs" onclick="eliminarBienRow(this)">';
                html += '<i class="fas fa-trash"></i>';
                html += '</button>';
                html += '</div>';
                html += '</div>';

                $('#bienes_pedido_container').append(html);
            });
        } else {
            agregarBienRow();
        }
    });
}

function eliminar() {
    apiLaravel.exec('pedidos-internos/' + idEdit, 'DELETE', {}, function(response) {
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
    apiLaravel.get('pedidos-internos/' + id, function(data) {
        if (!data.pedido) {
            alert('Pedido no encontrado');
            return;
        }

        var html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<strong>Número:</strong> ' + data.pedido.IdPedidoInterno + '<br>';
        html += '<strong>Solicitante:</strong> ' + (data.pedido.servicioSolicitante ? data.pedido.servicioSolicitante.Servicio : '-') + '<br>';
        html += '<strong>Destino:</strong> ' + (data.pedido.servicioDestino ? data.pedido.servicioDestino.Servicio : '-') + '<br>';
        html += '<strong>Estado:</strong> ' + (data.pedido.estado ? data.pedido.estado.PedidoInternoEstado : '-') + '<br>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<strong>Creado por:</strong> ' + (data.pedido.creador ? data.pedido.creador.Nombre + ' ' + data.pedido.creador.Apellido : '-') + '<br>';
        html += '<strong>Fecha:</strong> ' + data.pedido.FechaCreacion + '<br>';
        html += '<strong>Observación:</strong> ' + (data.pedido.Observacion || '-') + '<br>';
        html += '</div>';
        html += '</div>';

        html += '<hr>';
        html += '<h5>Bienes del Pedido</h5>';
        html += '<table class="table table-sm table-bordered">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Bien</th>';
        html += '<th>Cantidad</th>';
        html += '<th>Detalle</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                html += '<tr>';
                html += '<td>' + (bien.bien ? bien.bien.NombreB : '-') + '</td>';
                html += '<td>' + bien.Cantidad + '</td>';
                html += '<td>' + (bien.Detalle || '-') + '</td>';
                html += '</tr>';
            });
        } else {
            html += '<tr><td colspan="3" class="text-center">No hay bienes</td></tr>';
        }

        html += '</tbody>';
        html += '</table>';

        $('#detalle_pedido_content').html(html);
        $('#modal_ver_detalle').modal();
    });
}

function refrescarTabla() {
    var params = $('.card form').serialize();
    apiLaravel.get('pedidos-internos/get-pedidos-internos?' + params, function(data) {
        dataTable = data.registros;

        var htmlTable = '';
        dataTable.forEach(function(registro) {
            htmlTable += '<tr>';
            htmlTable += '<td>' + registro.IdPedidoInterno + '</td>';
            htmlTable += '<td>' + (registro.servicioSolicitante ? registro.servicioSolicitante.Servicio : '-') + '</td>';
            htmlTable += '<td>' + (registro.servicioDestino ? registro.servicioDestino.Servicio : '-') + '</td>';
            htmlTable += '<td>' + (registro.estado ? registro.estado.PedidoInternoEstado : '-') + '</td>';
            htmlTable += '<td>' + registro.FechaCreacion + '</td>';
            htmlTable += '<td>';
            htmlTable += '<div class="btn-group">';
            htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdPedidoInterno + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
            htmlTable += '<button type="button" onclick="editar(' + registro.IdPedidoInterno + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
            htmlTable += '<button type="button" onclick="irACotizaciones(' + registro.IdPedidoInterno + ')" class="btn btn-info btn-xs" title="Cotizar"><i class="fas fa-file-invoice-dollar"></i></button>';
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
            pagina = (this.value - 1) * 10;
            var busqueda = $('form').serialize();
            apiLaravel.get('pedidos-internos/get-pedidos-internos?Pagina=' + pagina + '&' + busqueda, function(data) {
                dataTable = data.registros;
                var htmlTable = '';
                dataTable.forEach(function(registro) {
                    htmlTable += '<tr>';
                    htmlTable += '<td>' + registro.IdPedidoInterno + '</td>';
                    htmlTable += '<td>' + (registro.servicioSolicitante ? registro.servicioSolicitante.Servicio : '-') + '</td>';
                    htmlTable += '<td>' + (registro.servicioDestino ? registro.servicioDestino.Servicio : '-') + '</td>';
                    htmlTable += '<td>' + (registro.estado ? registro.estado.PedidoInternoEstado : '-') + '</td>';
                    htmlTable += '<td>' + registro.FechaCreacion + '</td>';
                    htmlTable += '<td>';
                    htmlTable += '<div class="btn-group">';
                    htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdPedidoInterno + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
                    htmlTable += '<button type="button" onclick="editar(' + registro.IdPedidoInterno + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
                    htmlTable += '<button type="button" onclick="irACotizaciones(' + registro.IdPedidoInterno + ')" class="btn btn-info btn-xs" title="Cotizar"><i class="fas fa-file-invoice-dollar"></i></button>';
                    htmlTable += '</div>';
                    htmlTable += '</td>';
                    htmlTable += '</tr>';
                });
                $('#table_data').html(htmlTable);
            });
        });
    });
}

function irACotizaciones(idPedido) {
    window.location.href = '/cotizaciones#' + idPedido;
}
