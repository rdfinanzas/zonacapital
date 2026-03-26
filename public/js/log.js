var pagina = 1;

function buscarLogs() {
    pagina = 1;
    cargarLogs();
}

function cargarLogs() {
    var params = {
        pagina: pagina,
        cantidad: $('#page-selection_input_num_page').val(),
        log: $('#log_search').val(),
        id: $('#search_usuario').val(),
        modulo: $('#modulo').val(),
        accion: $('#accion').val(),
        d: $('#d_fil').val(),
        h: $('#h_fil').val(),
    };

    apiLaravel.get('log/filtrar', params, function (data) {
        var htmlTable = "";
        data.data.forEach(function (registro) {
            htmlTable += "<tr>";
            htmlTable += "<td>" + registro.usuario.Usuario + "</td>";
            htmlTable += "<td>" + registro.usuario.Apellido + " " + registro.usuario.Nombre + "</td>";
            var accion = "";
            switch (Number(registro.Tipo)) {
                case -1:
                    accion = "Logout";
                    break;
                case 0:
                    accion = "Login";
                    break;
                case 1:
                    accion = "Crear";
                    break;
                case 3:
                    accion = "Modificar";
                    break;
                case 4:
                    accion = "Eliminar";
                    break;
            }
            htmlTable += "<td>" + accion + "</td>";
            htmlTable += "<td>" + registro.Mensaje + "</td>";
            htmlTable += "<td>" + (registro.modulo ? registro.modulo.Label : '-') + "</td>";
            htmlTable += "<td>" + registro.FF + "</td>";
            htmlTable += "<td>" + registro.usuario.tipo_usuario.UsuarioTipo + "</td>";
            htmlTable += "<td>" + registro.IP + "</td>";
            htmlTable += "</tr>";
        });
        $("#table_log").html(htmlTable);

        $('#total_info').html(data.total + " registros");
        $('#page-selection').bootpag({
            total: data.last_page,
            page: pagina,
        }).on("page", function (event, num) {
            pagina = num;
            cargarLogs();
        });
    });
}

$(function () {
    $('.select2').select2();
    $('#desde_fil, #hasta_fil').datetimepicker({ format: 'DD/MM/YYYY' });

    cargarLogs();
});
