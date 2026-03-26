var idEdit = 0;
var dataTable;
var numBienes = 0;
var bienes = [];

function editar(id, ind) {
    limpiar();
    idEdit = id;
    var registro = dataTable[ind];
    $('#tip_mov').val(registro.TipoMovMa).trigger('change');
    $('#f_mov').val(registro.FF);
    $('#dep_ori').val(registro.IdDepoMa).trigger('change');
    $('#tipo_desti').val(registro.TipoDestinoMa_Id).trigger('change');
    if (registro.TipoDestinoMa_Id == 3) {
        $('#depo_dest').val(registro.IdDepoDestMa).trigger('change');
    }
    // Cargar bienes
    registro.bienes.forEach(function (bien) {
        agregarBien(bien.IdBien, bien.NombreB, bien.pivot.Cantidad, bien.stock, bien.UltPrecio);
    });
}

function guardar() {
    var data = {
        tip_mov: $('#tip_mov').val(),
        f_mov: $('#f_mov').val(),
        dep_ori: $('#dep_ori').val(),
        tipo_desti: $('#tipo_desti').val(),
        depo_dest: $('#depo_dest').val(),
        bienes: [],
    };

    for (var i = 0; i < numBienes; i++) {
        if ($('#mov_bien_' + i).length > 0) {
            data.bienes.push({
                id: $('#bien_id_' + i).val(),
                cantidad: $('#cantidad_' + i).val(),
            });
        }
    }

    var url = idEdit == 0 ? 'mov-consumos' : 'mov-consumos/' + idEdit;
    var method = idEdit == 0 ? 'POST' : 'PUT';

    apiLaravel.exec(url, method, data, function (data) {
        limpiar();
        cargarTabla();
    });
}

function modalEliminar(id) {
    idEdit = id;
    $('#modal_eliminar').modal();
}

function eliminar() {
    apiLaravel.exec('mov-consumos/' + idEdit, 'DELETE', {}, function (data) {
        limpiar();
        cargarTabla();
        $('#modal_eliminar').modal('hide');
    });
}

function cargarTabla() {
    var params = $('#form_filter').serialize();
    apiLaravel.get('mov-consumos/filtrar', params, function (data) {
        dataTable = data.data;
        var htmlTable = "";
        dataTable.forEach(function (registro, i) {
            htmlTable += "<tr>";
            htmlTable += "<td>" + registro.NumsMov + "</td>";
            htmlTable += "<td>" + getTipoMovimiento(registro.TipoMovMa) + "</td>";
            htmlTable += "<td>" + registro.FF + "</td>";
            htmlTable += "<td>" + registro.usuario.Apellido + " " + registro.usuario.Nombre + "</td>";
            htmlTable += "<td>" + registro.deposito_origen.Deposito + "</td>";
            htmlTable += "<td>" + (registro.deposito_destino ? registro.deposito_destino.Deposito : '-') + "</td>";
            htmlTable += "<td>" + getEstado(registro.EstadoMov) + "</td>";
            htmlTable += "<td>";
            htmlTable += '<div class="btn-group">';
            htmlTable += '<button type="button" onclick="editar(' + registro.IdTablaMaestra + ', ' + i + ')" class="btn btn-default btn-xs"><i class="fas fa-eye" aria-hidden="true"></i></button>';
            htmlTable += '<button type="button" onclick="modalEliminar(' + registro.IdTablaMaestra + ')" class="btn btn-danger btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>';
            htmlTable += '</div>';
            htmlTable += "</td>";
            htmlTable += "</tr>";
        });
        $("#table_data").html(htmlTable);
    });
}

function limpiar() {
    idEdit = 0;
    numBienes = 0;
    $('#form_main')[0].reset();
    $('#dep_ori').val(null).trigger('change');
    $('#depo_dest').val(null).trigger('change');
    $('#tabla_bienes').html('');
}

function agregarBien(id, nombre, cantidad, stock, precio) {
    var html = '<tr id="mov_bien_' + numBienes + '">' +
        '<td><input type="hidden" id="bien_id_' + numBienes + '" value="' + id + '">' + (numBienes + 1) + '</td>' +
        '<td>' + nombre + '</td>' +
        '<td><input type="number" id="cantidad_' + numBienes + '" value="' + (cantidad || '') + '" class="form-control"></td>' +
        '<td>' + stock + '</td>' +
        '<td>' + precio + '</td>' +
        '<td><button type="button" onclick="eliminarBien(' + numBienes + ')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    $('#tabla_bienes').append(html);
    numBienes++;
}

function eliminarBien(index) {
    $('#mov_bien_' + index).remove();
}

function getTipoMovimiento(tipo) {
    switch (Number(tipo)) {
        case 0: return 'Egreso';
        case 2: return 'Ajuste';
        case 5: return 'Ingreso (lavadero)';
        case 7: return 'Baja (costureria)';
        default: return '-';
    }
}

function getEstado(estado) {
    switch (Number(estado)) {
        case 0: return 'Pendiente';
        case 1: return 'Confirmado';
        case 2: return 'Rechazado';
        default: return '-';
    }
}

function refrescarTabla() {
    cargarTabla();
}

$(function () {
    $('.select2').select2();
    $('#fecha_mov').datetimepicker({ format: 'DD/MM/YYYY HH:mm' });

    $('#tipo_desti').change(function () {
        if ($(this).val() == 3) {
            $('#cont_dep_destino').show();
        } else {
            $('#cont_dep_destino').hide();
        }
    });

    $('#bien').on('keypress', function (e) {
        if (e.which == 13) {
            apiLaravel.get('bienes/filtrar', { bien_fil: $(this).val() }, function (data) {
                bienes = data.data;
                var html = '';
                bienes.forEach(function (bien, i) {
                    html += '<tr>' +
                        '<td>' + bien.NombreB + '</td>' +
                        '<td>' + bien.stock + '</td>' +
                        '<td>' + bien.UltPrecio + '</td>' +
                        '<td>' + bien.categoria.nombre + '</td>' +
                        '<td><button type="button" onclick="agregarBien(' + bien.IdBien + ', \'' + bien.NombreB + '\', null, ' + bien.stock + ', ' + bien.UltPrecio + ')" class="btn btn-primary btn-xs"><i class="fas fa-plus-circle"></i></button></td>' +
                        '</tr>';
                });
                $('#table_bienes_buscados').html(html);
                $('#modal_lista_bienes').modal('show');
            });
        }
    });

    $('#form_main').submit(function (e) {
        e.preventDefault();
        guardar();
    });

    $('#btn_eliminar_modal').click(function () {
        eliminar();
    });

    $('#btn_limpiar').click(function () {
        limpiar();
    });

    $('#form_filter').submit(function (e) {
        e.preventDefault();
        refrescarTabla();
    });

    cargarTabla();
});
