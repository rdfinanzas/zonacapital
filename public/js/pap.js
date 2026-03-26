var idEdit = 0;
var dataTable;
var paciente_id = null;

function editar(id) {
    apiLaravel('pap/' + id, 'GET').then(function (data) {
        idEdit = id;
        paciente_id = data.paciente_id;
        $('#dni').val(data.paciente.DNI);
        $('#ApellidoNombre').val(data.paciente.ApellidoNombre);
        $('#FechaNacimiento').val(data.paciente.FechaNacimiento);
        $('input[name="sexo"][value="' + data.paciente.Sexo + '"]').prop('checked', true);
        $('#celular').val(data.paciente.Celular);
        $('#Domicilio').val(data.paciente.Domicilio);
        $('#fecha_consulta').val(data.fecha_consulta);
        $('#ficha_n').val(data.ficha_n);
        $('#resultado').val(data.resultado);
        $('#practica_hijo_id').prop('checked', !!data.practica_hijo_id);
        $('#personal_id').val(data.personal_id).trigger('change');
        $('#efector_sel').val(data.efector_id).trigger('change');
        $('#form_persona').show();
        $('#footer_btn').show();
        $('#modal_add').modal('show');
    });
}

function guardar() {
    var data = $('#form_main').serializeJSON();
    data.paciente_id = paciente_id;
    var url = idEdit == 0 ? 'pap' : 'pap/' + idEdit;
    var method = idEdit == 0 ? 'POST' : 'PUT';

    apiLaravel(url, method, data).then(function (data) {
        limpiar();
        cargarTabla();
        $('#modal_add').modal('hide');
    });
}

function modalEliminar(id) {
    idEdit = id;
    $('#modal_eliminar').modal();
}

function eliminar() {
    apiLaravel('pap/' + idEdit, 'DELETE', {}).then(function (data) {
        limpiar();
        cargarTabla();
        $('#modal_eliminar').modal('hide');
    });
}

function buscarDni() {
    var dni = $('#dni').val();
    apiLaravel('pap/buscar-paciente?dni=' + dni, 'GET').then(function (data) {
        if (data) {
            paciente_id = data.IdPacienteRegTrab;
            $('#ApellidoNombre').val(data.ApellidoNombre);
            $('#FechaNacimiento').val(data.FechaNacimiento);
            $('input[name="sexo"][value="' + data.Sexo + '"]').prop('checked', true);
            $('#celular').val(data.Celular);
            $('#Domicilio').val(data.Domicilio);
        }
        $('#form_persona').show();
        $('#footer_btn').show();
    });
}
function exportar() {
    if ($("#overlay").length == 0) {
        let html_modal = '<div class="overlay-fixed overlay-wrapper" id="overlay">' +
            '  <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Cargando...</div></div>' +
            '</div>'
        $("body").append(html_modal)
    } else {
        $("#overlay").show()
    }
    // Usar el mismo patrón de filtros que el resto (serialize del form)
    const params = $('#filter form').serialize();
    const url = 'pap/exportar?' + params;

    fetch(url, {
        headers: { 'Authorization': 'Bearer ' + (window.localStorage.getItem('Authorization') || '') },
        method: 'GET'
    })
        .then(resp => {
            if (!resp.ok) throw new Error('Error de descarga');
            return resp.blob();
        })
        .then(blob => {
            $("#overlay").hide();
            const urlBlob = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = urlBlob;
            a.download = 'pap.xlsx';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(urlBlob);
        })
        .catch((e) => {
            $("#overlay").hide();
            alert('Error al exportar: ' + (e && e.message ? e.message : 'Intente nuevamente'));
        });
}

// Utilidades de fecha para filtros
function formatearFecha(fecha) {
    var fechaObj = new Date(fecha);
    var dia = fechaObj.getDate().toString().padStart(2, '0');
    var mes = (fechaObj.getMonth() + 1).toString().padStart(2, '0');
    var anio = fechaObj.getFullYear().toString();
    return dia + '/' + mes + '/' + anio;
}

// Lee una fecha desde un input y la devuelve en formato DD/MM/YYYY
function valorFecha(selector) {
    var v = $(selector).val() || '';
    if (v === '') return '';
    if (v.indexOf('/') !== -1) return v; // ya viene como DD/MM/YYYY
    var m = v.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) {
        return m[3] + '/' + m[2] + '/' + m[1];
    }
    try {
        return formatearFecha(v);
    } catch (e) {
        return v;
    }
}

// Asegurar disponibilidad global para manejadores inline
window.exportar = exportar;

function cargarTabla() {
    var params = $('#filter form').serialize();
    apiLaravel('pap/filtrar?' + params, 'GET').then(function (data) {
        dataTable = data.data;
        var htmlTable = "";
        dataTable.forEach(function (registro) {
            htmlTable += "<tr>";
            htmlTable += "<td>" + registro.fecha_consulta + "</td>";
            htmlTable += "<td>" + registro.ficha_n + "</td>";
            htmlTable += "<td>" + (registro.practica_hijo_id ? 'PAP y PVH' : 'PAP') + "</td>";
            htmlTable += "<td>" + (registro.efector && registro.efector.servicio ? registro.efector.servicio : '') + "</td>";
            htmlTable += "<td>" + (registro.profesional ? (registro.profesional.Apellido + ', ' + registro.profesional.Nombre) : '') + "</td>";
            htmlTable += "<td>" + registro.paciente.ApellidoNombre + "</td>";
            htmlTable += "<td>" + registro.paciente.DNI + "</td>";
            htmlTable += "<td>" + registro.paciente.FechaNacimiento + "</td>";
            htmlTable += "<td>" + '' + "</td>"; // Edad
            htmlTable += "<td>" + registro.paciente.Domicilio + "</td>";
            htmlTable += "<td>" + registro.paciente.Celular + "</td>";
            var resultadoTxt = registro.resultado_texto || registro.resultado || '';
            htmlTable += "<td>" + resultadoTxt + "</td>";
            htmlTable += "<td>" + '' + "</td>"; // Operador
            htmlTable += "<td>";
            htmlTable += '<div class="btn-group">';
            htmlTable += '<button type="button" onclick="editar(' + registro.id + ')" class="btn btn-primary btn-xs"><i class="fas fa-edit" aria-hidden="true"></i></button>';
            htmlTable += '<button type="button" onclick="modalEliminar(' + registro.id + ')" class="btn btn-danger btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>';
            htmlTable += '</div>';
            htmlTable += "</td>";
            htmlTable += "</tr>";
        });
        $("#table_data").html(htmlTable);
    });
}

function limpiar() {
    idEdit = 0;
    paciente_id = null;
    $('#form_main')[0].reset();
    $('#form_persona').hide();
    $('#footer_btn').hide();
}

function verFiltro() {
    $('#filter').toggle();
}

function refrescarTabla() {
    cargarTabla();
}

$(function () {
    // Inicializar Select2 con dropdownParent para selects dentro del modal
    $('#modal_add .select2').select2({ dropdownParent: $('#modal_add'), width: '100%' });
    // Inicializar Select2 para el resto de selects
    $('.select2').not('#modal_add .select2').select2({ width: '100%' });

    $('#btn_add').click(function () {
        limpiar();
        $('#modal_add').modal('show');
    });

    $('#btn_buscar_dni').click(function () {
        buscarDni();
    });

    $('#form_main').submit(function (e) {
        e.preventDefault();
        // Validación: fecha de consulta no puede ser futura
        var val = $('#fecha_consulta').val();
        if (val) {
            // parse ISO yyyy-mm-dd de input type=date
            var parts = val.split('-');
            if (parts.length === 3) {
                var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                var hoy = new Date();
                d.setHours(0,0,0,0);
                hoy.setHours(0,0,0,0);
                if (d > hoy) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('La fecha de consulta no puede ser mayor al día actual');
                    } else {
                        alert('La fecha de consulta no puede ser mayor al día actual');
                    }
                    return;
                }
            }
        }
        guardar();
    });

    $('#btn_eliminar_modal').click(function () {
        eliminar();
    });

    $('#btn_limpiar').click(function () {
        limpiar();
    });

    $('#filter form').submit(function (e) {
        e.preventDefault();
        refrescarTabla();
    });

    cargarTabla();
});
