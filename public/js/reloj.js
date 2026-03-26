var idEdit = 0;
var dataTable;

async function editar(id) {
    try {
        const data = await apiLaravel('/reloj/' + id, 'GET');
        idEdit = id;
        $('#reloj').val(data.Reloj);
        $('#servicio_id').val(data.ServicioReloj_Id).trigger('change');
        $('#tipo').val(data.tipo);
        $('#ip').val(data.ip);
        $('#user_admin').val(data.user_admin);
        $('#password').val(data.password);
        $('#observacion').val(data.Observacion);
    } catch (error) {
        console.error('Error al cargar reloj:', error);
    }
}

async function guardar() {
    try {
        var formData = $('#form_main').serializeArray();
        var data = {};
        formData.forEach(function(field) {
            data[field.name] = field.value;
        });

        var url = idEdit == 0 ? '/reloj' : '/reloj/' + idEdit;
        var method = idEdit == 0 ? 'POST' : 'PUT';

        const response = await apiLaravel(url, method, data);

        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: response.message || 'Operación completada correctamente'
        });

        limpiar();
        cargarLista();
    } catch (error) {
        console.error('Error al guardar:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar el reloj'
        });
    }
}

function modalEliminar(id) {
    idEdit = id;
    $('#modal_eliminar').modal();
}

async function eliminarRegistro() {
    try {
        const response = await apiLaravel('/reloj/' + idEdit, 'DELETE');

        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: response.message || 'Reloj eliminado correctamente'
        });

        limpiar();
        cargarLista();
        $('#modal_eliminar').modal('hide');
    } catch (error) {
        console.error('Error al eliminar:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al eliminar el reloj'
        });
    }
}

async function cargarLista() {
    try {
        const data = await apiLaravel('/reloj/filtrar', 'GET');
        dataTable = data.data;
        var htmlTable = "";
        var permisos = JSON.parse($('#permisos').val());

        if (!dataTable || dataTable.length === 0) {
            htmlTable = '<tr><td colspan="4" class="text-center text-muted">No hay relojes registrados</td></tr>';
        } else {
            dataTable.forEach(function (registro) {
                htmlTable += "<tr>";
                htmlTable += "<td>" + registro.IdReloj + "</td>";
                htmlTable += "<td>" + registro.Reloj + "</td>";
                htmlTable += "<td>" + (registro.servicio ? registro.servicio.servicio : 'Sin servicio') + "</td>";
                htmlTable += "<td>";
                htmlTable += '<div class="btn-group">';
                if (permisos.U == 1) {
                    htmlTable += '<button type="button" onclick="editar(' + registro.IdReloj + ')" class="btn btn-primary btn-xs"><i class="fas fa-edit" aria-hidden="true"></i></button>';
                }
                if (permisos.D == 1) {
                    htmlTable += '<button type="button" onclick="modalEliminar(' + registro.IdReloj + ')" class="btn btn-danger btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                }
                htmlTable += '</div>';
                htmlTable += "</td>";
                htmlTable += "</tr>";
            });
        }

        $("#table_data").html(htmlTable);
        $('#total_info').html(data.total + " registros");

        if (typeof $.fn.bootpag !== 'undefined') {
            $('#page-selection').bootpag({
                total: data.last_page,
                page: data.current_page,
                maxVisible: 5
            });
        }
    } catch (error) {
        console.error('Error al cargar lista:', error);
        $("#table_data").html('<tr><td colspan="4" class="text-center text-danger">Error al cargar los relojes</td></tr>');
    }
}

function limpiar() {
    idEdit = 0;
    $('#form_main')[0].reset();
    $('#servicio_id').trigger('change');
}

$(function () {
    $('.select2').select2();

    $("#form_main").validate({
        submitHandler: function (form) {
            guardar();
            return false;
        }
    });

    $("#btn_eliminar_modal").click(function () {
        eliminarRegistro();
    });

    $("#btn_limpiar").click(function () {
        limpiar();
    });

    $('#page-selection').on("page", function (event, num) {
        cargarPagina(num);
    });

async function cargarPagina(pagina) {
    try {
        const data = await apiLaravel('/reloj/filtrar?page=' + pagina, 'GET');
        dataTable = data.data;
        var htmlTable = "";
        var permisos = JSON.parse($('#permisos').val());

        if (!dataTable || dataTable.length === 0) {
            htmlTable = '<tr><td colspan="4" class="text-center text-muted">No hay relojes registrados</td></tr>';
        } else {
            dataTable.forEach(function (registro) {
                htmlTable += "<tr>";
                htmlTable += "<td>" + registro.IdReloj + "</td>";
                htmlTable += "<td>" + registro.Reloj + "</td>";
                htmlTable += "<td>" + (registro.servicio ? registro.servicio.servicio : 'Sin servicio') + "</td>";
                htmlTable += "<td>";
                htmlTable += '<div class="btn-group">';
                if (permisos.U == 1) {
                    htmlTable += '<button type="button" onclick="editar(' + registro.IdReloj + ')" class="btn btn-primary btn-xs"><i class="fas fa-edit" aria-hidden="true"></i></button>';
                }
                if (permisos.D == 1) {
                    htmlTable += '<button type="button" onclick="modalEliminar(' + registro.IdReloj + ')" class="btn btn-danger btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                }
                htmlTable += '</div>';
                htmlTable += "</td>";
                htmlTable += "</tr>";
            });
        }

        $("#table_data").html(htmlTable);
        $('#total_info').html(data.total + " registros");
    } catch (error) {
        console.error('Error al cargar página:', error);
    }
}

    cargarLista();
});
