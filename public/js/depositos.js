var idEdit = 0;
var dataTable;

function editar(id) {
    apiLaravel.get('depositos/' + id, function (data) {
        idEdit = id;
        $('#deposito').val(data.Deposito);
        var responsables = data.responsables.map(function (responsable) {
            return responsable.idEmpleado;
        });
        $('#resp').val(responsables).trigger('change');
    });
}

function guardar() {
    var data = {
        deposito: $('#deposito').val(),
        responsables: $('#resp').val(),
    };
    var url = idEdit == 0 ? 'depositos' : 'depositos/' + idEdit;
    var method = idEdit == 0 ? 'POST' : 'PUT';

    apiLaravel.exec(url, method, data, function (data) {
        limpiar();
        cargarLista();
    });
}

function modalEliminar(id) {
    idEdit = id;
    $('#modal_eliminar').modal();
}

function eliminarRegistro() {
    apiLaravel.exec('depositos/' + idEdit, 'DELETE', {}, function (data) {
        limpiar();
        cargarLista();
        $('#modal_eliminar').modal('hide');
    });
}

function cargarLista() {
    apiLaravel.get('depositos/filtrar', function (data) {
        dataTable = data.data;
        var htmlTable = "";
        dataTable.forEach(function (registro) {
            htmlTable += "<tr>";
            htmlTable += "<td>" + registro.IdDeposito + "</td>";
            htmlTable += "<td>" + registro.Deposito + "</td>";
            htmlTable += "<td>";
            htmlTable += '<div class="btn-group">';
            htmlTable += '<button type="button" onclick="editar(' + registro.IdDeposito + ')" class="btn btn-primary btn-xs"><i class="fas fa-edit" aria-hidden="true"></i></button>';
            htmlTable += '<button type="button" onclick="modalEliminar(' + registro.IdDeposito + ')" class="btn btn-danger btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>';
            htmlTable += '</div>';
            htmlTable += "</td>";
            htmlTable += "</tr>";
        });
        $("#table_data").html(htmlTable);
    });
}

function limpiar() {
    idEdit = 0;
    $('#form_main')[0].reset();
    $('#resp').val(null).trigger('change');
}

function resetTable() {
    cargarLista();
}

$(function () {
    $('.select2').select2();

    $('#form_main').submit(function (e) {
        e.preventDefault();
        guardar();
    });

    $('#btn_eliminar_modal').click(function () {
        eliminarRegistro();
    });

    $('#btn_limpiar').click(function () {
        limpiar();
    });

    $('#btn_eliminar').click(function () {
        modalEliminar(idEdit);
    });

    cargarLista();
});
