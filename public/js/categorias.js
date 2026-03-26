var idEdit = 0;
var tipoEdit = 0;

function addTree(id, arr, nivel) {
    limpiar();
    idEdit = id;
    tipoEdit = 2; // 2 for add
    $('#titulo_form').html('Agregar');
    $('#btn_submit').attr('disabled', false);
}

function editarTree(id, arr, nivel) {
    limpiar();
    idEdit = id;
    tipoEdit = 1; // 1 for edit
    apiLaravel.get('categorias/' + id, function (data) {
        $('#categoria').val(data.categoria);
        $('#obs').val(data.obs);
        $('#mover').val(data.padre);
        $('#padre').html(data.padre_nombre);
        $('#titulo_form').html('Editar');
        $('#btn_submit').attr('disabled', false);
        $('#mover').attr('disabled', false);
    });
}

function delTree(id, arr, nivel) {
    idEdit = id;
    $('#modal_eliminar').modal();
}

function guardar() {
    var data = {
        categoria: $('#categoria').val(),
        obs: $('#obs').val(),
        padre: tipoEdit === 2 ? idEdit : $('#mover').val(),
    };

    var url = tipoEdit === 2 ? 'categorias' : 'categorias/' + idEdit;
    var method = tipoEdit === 2 ? 'POST' : 'PUT';

    apiLaravel.exec(url, method, data, function (data) {
        limpiar();
        getCategorias();
    });
}

function eliminar() {
    apiLaravel.exec('categorias/' + idEdit, 'DELETE', {}, function (data) {
        limpiar();
        getCategorias();
        $('#modal_eliminar').modal('hide');
    });
}

function getCategorias() {
    apiLaravel.get('categorias', function (data) {
        $('#div_tree').html(data);
    });
}

function limpiar() {
    idEdit = 0;
    tipoEdit = 0;
    $('#form_main')[0].reset();
    $('#btn_submit').attr('disabled', true);
    $('#mover').attr('disabled', true);
    $('#titulo_form').html('');
    $('#padre').html('');
}

$(function () {
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

    getCategorias();
});
