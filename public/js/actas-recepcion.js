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
            alert('Debe seleccionar un acta');
            return;
        }
        if (confirm('¿Está seguro que desea eliminar este acta?')) {
            eliminar();
        }
    });

    agregarBienRow();

    $(document).on('focus', '.bien-buscar', function() {
        var input = $(this);
        input.autocomplete({
            source: function(request, response) {
                apiLaravel.get('actas-recepcion/get-bienes?Bienes=' + encodeURIComponent(request.term), function(data) {
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

function agregarBienRow() {
    var html = '<div class="row bien-row mt-2">';
    html += '<div class="col-md-5">';
    html += '<div class="form-group">';
    html += '<label>Buscar Bien:</label>';
    html += '<input type="text" class="form-control bien-buscar" placeholder="Buscar bien...">';
    html += '</div>';
    html += '</div>';
    html += '<div class="col-md-2">';
    html += '<div class="form-group">';
    html += '<label>Cantidad:</label>';
    html += '<input type="number" class="form-control bien-cantidad" value="1" min="1">';
    html += '</div>';
    html += '</div>';
    html += '<div class="col-md-3">';
    html += '<div class="form-group">';
    html += '<label>Remito:</label>';
    html += '<input type="text" class="form-control bien-remito">';
    html += '</div>';
    html += '</div>';
    html += '<div class="col-md-1" style="padding-top: 30px;">';
    html += '<button type="button" class="btn btn-danger btn-xs" onclick="eliminarBienRow(this)">';
    html += '<i class="fas fa-trash"></i>';
    html += '</button>';
    html += '</div>';
    html += '</div>';

    $('#bienes_acta_container').append(html);

    $('.bien-buscar').off('focus').on('focus', function() {
        var input = $(this);
        input.autocomplete({
            source: function(request, response) {
                apiLaravel.get('actas-recepcion/get-bienes?Bienes=' + encodeURIComponent(request.term), function(data) {
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
    $('#IdUnico').val(0);
    $('#form_main')[0].reset();
    $('#bienes_acta_container').empty();
    agregarBienRow();
}

function guardar() {
    var idsBienes = [];
    var cantidades = [];
    var remitos = [];

    $('.bien-row').each(function() {
        var bienId = $(this).find('.bien-buscar').data('bien-id');
        if (bienId) {
            idsBienes.push(bienId);
            cantidades.push($(this).find('.bien-cantidad').val());
            remitos.push($(this).find('.bien-remito').val());
        }
    });

    var data = {
        FechaEmicion: $('#FechaEmicion').val(),
        Numero: $('#Numero').val(),
        NumExp: $('#NumExp').val(),
        TipoAc: $('#TipoAc').val(),
        Estado_Id: $('#Estado_Id').val(),
        Proveedor_Id: $('#Proveedor_Id').val(),
        NumProv: $('#NumProv').val(),
        Cuenta_Id: $('#Cuenta_Id').val(),
        DescripActa: $('#DescripActa').val(),
    };

    var url = 'actas-recepcion';
    var method = 'POST';

    if ($('#IdUnico').val() > 0) {
        url = 'actas-recepcion/' + $('#IdUnico').val();
        method = 'PUT';
    }

    apiLaravel.exec(url, method, data, function(response) {
        if (response.success) {
            alert(response.message);
            
            if (idsBienes.length > 0 && $('#IdUnico').val() == 0) {
                for (var i = 0; i < idsBienes.length; i++) {
                    apiLaravel.exec('actas-recepcion/agregar-bien', 'POST', {
                        ActaRecepcion_Id: response.id,
                        Bien_Id: idsBienes[i],
                        Cantidad: cantidades[i],
                        Remito: remitos[i] || '',
                    }, function(res) {
                        if (res.success) {
                            console.log('Bien agregado');
                        }
                    });
                }
            }
            
            limpiar();
            refrescarTabla();
        } else {
            alert(response.message);
        }
    });
}

function editar(id) {
    idEdit = id;

    apiLaravel.get('actas-recepcion/' + id, function(data) {
        if (!data.acta) {
            alert('Acta no encontrada');
            return;
        }

        $('#IdUnico').val(data.acta.IdUnico);
        $('#FechaEmicion').val(data.acta.FechaEmicion ? data.acta.FechaEmicion.split(' ')[0] : '');
        $('#Numero').val(data.acta.Numero || '');
        $('#NumExp').val(data.acta.NumExp || '');
        $('#TipoAc').val(data.acta.TipoAc || 1);
        $('#Estado_Id').val(data.acta.Estado_Id);
        $('#Proveedor_Id').val(data.acta.Proveedor_Id).trigger('change');
        $('#NumProv').val(data.acta.NumProv || '');
        $('#Cuenta_Id').val(data.acta.Cuenta_Id);
        $('#DescripActa').val(data.acta.DescripActa || '');

        $('#bienes_acta_container').empty();

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                var html = '<div class="row bien-row mt-2">';
                html += '<div class="col-md-5">';
                html += '<div class="form-group">';
                html += '<label>Buscar Bien:</label>';
                html += '<input type="text" class="form-control bien-buscar" value="' + (bien.bien ? bien.bien.NombreB : '') + '" data-bien-id="' + bien.Bien_Id + '">';
                html += '</div>';
                html += '</div>';
                html += '<div class="col-md-2">';
                html += '<div class="form-group">';
                html += '<label>Cantidad:</label>';
                html += '<input type="number" class="form-control bien-cantidad" value="' + bien.Cantidad + '" min="1">';
                html += '</div>';
                html += '</div>';
                html += '<div class="col-md-3">';
                html += '<div class="form-group">';
                html += '<label>Remito:</label>';
                html += '<input type="text" class="form-control bien-remito" value="' + (bien.Remito || '') + '">';
                html += '</div>';
                html += '</div>';
                html += '<div class="col-md-1" style="padding-top: 30px;">';
                html += '<button type="button" class="btn btn-danger btn-xs" onclick="eliminarBienRow(this)">';
                html += '<i class="fas fa-trash"></i>';
                html += '</button>';
                html += '</div>';
                html += '</div>';

                $('#bienes_acta_container').append(html);
            });
        } else {
            agregarBienRow();
        }
    });
}

function eliminar() {
    apiLaravel.exec('actas-recepcion/' + idEdit, 'DELETE', {}, function(response) {
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
    apiLaravel.get('actas-recepcion/' + id, function(data) {
        if (!data.acta) {
            alert('Acta no encontrada');
            return;
        }

        var html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<strong>Número:</strong> ' + (data.acta.Numero || '-') + '<br>';
        html += '<strong>Expediente:</strong> ' + (data.acta.NumExp || '-') + '<br>';
        html += '<strong>Estado:</strong> ' + (data.acta.estado ? data.acta.estado.Estado : '-') + '<br>';
        html += '<strong>Tipo:</strong> ' + (data.acta.TipoAc == 1 ? 'Compra' : 'Otro') + '<br>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<strong>Proveedor:</strong> ' + (data.acta.proveedor ? data.acta.proveedor.Proveedor : '-') + '<br>';
        html += '<strong>Nº Prov:</strong> ' + (data.acta.NumProv || '-') + '<br>';
        html += '<strong>Fecha Emisión:</strong> ' + data.acta.FechaEmicion + '<br>';
        html += '<strong>Creado por:</strong> ' + (data.acta.creador ? data.acta.creador.Nombre + ' ' + data.acta.creador.Apellido : '-') + '<br>';
        html += '</div>';
        html += '</div>';

        html += '<hr>';
        html += '<h5>Bienes del Acta</h5>';
        html += '<table class="table table-sm table-bordered">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Bien</th>';
        html += '<th>Cantidad</th>';
        html += '<th>Remito</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        if (data.bienes && data.bienes.length > 0) {
            data.bienes.forEach(function(bien) {
                html += '<tr>';
                html += '<td>' + (bien.bien ? bien.bien.NombreB : '-') + '</td>';
                html += '<td>' + bien.Cantidad + '</td>';
                html += '<td>' + (bien.Remito || '-') + '</td>';
                html += '</tr>';
            });
        } else {
            html += '<tr><td colspan="3" class="text-center">No hay bienes</td></tr>';
        }

        html += '</tbody>';
        html += '</table>';

        $('#detalle_acta_content').html(html);
        $('#modal_ver_detalle').modal();
    });
}

function refrescarTabla() {
    var params = $('.card form').serialize();
    apiLaravel.get('actas-recepcion/get-actas?' + params, function(data) {
        dataTable = data.registros;

        var htmlTable = '';
        dataTable.forEach(function(registro) {
            htmlTable += '<tr>';
            htmlTable += '<td>' + (registro.Numero || '-') + '</td>';
            htmlTable += '<td>' + (registro.NumExp || '-') + '</td>';
            htmlTable += '<td>' + (registro.proveedor ? registro.proveedor.Proveedor : '-') + '</td>';
            htmlTable += '<td>' + (registro.estado ? registro.estado.Estado : '-') + '</td>';
            htmlTable += '<td>' + registro.FechaEmicion + '</td>';
            htmlTable += '<td>';
            htmlTable += '<div class="btn-group">';
            htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdUnico + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
            htmlTable += '<button type="button" onclick="editar(' + registro.IdUnico + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
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
            apiLaravel.get('actas-recepcion/get-actas?Pagina=' + pagina + '&' + busqueda, function(data) {
                dataTable = data.registros;
                var htmlTable = '';
                dataTable.forEach(function(registro) {
                    htmlTable += '<tr>';
                    htmlTable += '<td>' + (registro.Numero || '-') + '</td>';
                    htmlTable += '<td>' + (registro.NumExp || '-') + '</td>';
                    htmlTable += '<td>' + (registro.proveedor ? registro.proveedor.Proveedor : '-') + '</td>';
                    htmlTable += '<td>' + (registro.estado ? registro.estado.Estado : '-') + '</td>';
                    htmlTable += '<td>' + registro.FechaEmicion + '</td>';
                    htmlTable += '<td>';
                    htmlTable += '<div class="btn-group">';
                    htmlTable += '<button type="button" onclick="verDetalle(' + registro.IdUnico + ')" class="btn btn-primary btn-xs" title="Ver detalle"><i class="fas fa-eye"></i></button>';
                    htmlTable += '<button type="button" onclick="editar(' + registro.IdUnico + ')" class="btn btn-success btn-xs" title="Editar"><i class="fas fa-edit"></i></button>';
                    htmlTable += '</div>';
                    htmlTable += '</td>';
                    htmlTable += '</tr>';
                });
                $('#table_data').html(htmlTable);
            });
        });
    });
}
