var Toast;

$(function () {
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });

    cargarSelect('clasificacion_personal');
    cargarSelect('funciones');
    cargarSelect('profesion');
    cargarSelect('tiporelacion');
    cargarSelect('agrupamiento');
    cargarSelect('tipo_jornada');

    $("#tipo_jornada_txt_1").inputmask("99:99");
});

function cargarSelect(tabla) {
    $.ajax({
        url: '/parametros/select',
        type: 'GET',
        data: { tabla: tabla },
        success: function(response) {
            if (response.success) {
                var options = '<option value="0">--</option>';
                response.data.forEach(function(item) {
                    var id = item.IdFuncion || item.idprofesion || item.idRelacion || item.idAgrupamiento || item.IdTipoJornada || item.idClasificacion;
                    var nombre = item.Funcion || item.profesion || item.Relacion || item.agrupamiento || item.Jornada || item.clasificacion;
                    options += '<option value="' + id + '">' + nombre + '</option>';
                });
                $('#' + tabla + '_sel').html(options);
            }
        },
        error: function(xhr, status, error) {
            Toast.fire({ icon: 'error', title: 'Error al cargar: ' + error });
        }
    });
}

function guardar(tabla) {
    var idSeleccionado = $('#' + tabla + '_sel').val();
    var valorInput = $('#' + tabla + '_txt').val();
    var valorInput2 = $('#' + tabla + '_txt_1').val();
    
    console.log('Guardando - Tabla:', tabla, 'ID:', idSeleccionado, 'Valor:', valorInput);
    
    if (!valorInput || valorInput.trim() === '') {
        Toast.fire({ icon: 'warning', title: 'Ingrese un valor' });
        return;
    }
    
    $.ajax({
        url: '/parametros/guardar',
        type: 'POST',
        data: {
            tabla: tabla,
            id: idSeleccionado,
            val: valorInput,
            val2: valorInput2
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Response:', response);
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                $('#' + tabla + '_txt').val('');
                $('#' + tabla + '_txt_1').val('');
                cargarSelect(tabla);
            } else {
                Toast.fire({ icon: 'error', title: response.message });
            }
        },
        error: function(xhr, status, error) {
            Toast.fire({ icon: 'error', title: 'Error: ' + error });
        }
    });
}

function del(tabla) {
    var id = $('#' + tabla + '_sel').val();
    
    if (id == 0) {
        Toast.fire({ icon: 'warning', title: 'Seleccione un registro para eliminar' });
        return;
    }

    $('#modal_eliminar').modal('show');
    
    $('#btn_eliminar_modal').off('click').on('click', function() {
        $.ajax({
            url: '/parametros/eliminar',
            type: 'DELETE',
            data: { tabla: tabla, id: id },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                    $('#' + tabla + '_txt').val('');
                    $('#' + tabla + '_txt_1').val('');
                    cargarSelect(tabla);
                } else {
                    Toast.fire({ icon: 'error', title: response.message });
                }
                $('#modal_eliminar').modal('hide');
            },
            error: function(xhr, status, error) {
                Toast.fire({ icon: 'error', title: 'Error: ' + error });
                $('#modal_eliminar').modal('hide');
            }
        });
    });
}

function limpiarCampos() {
    $("input[type='text']").val("");
    $("select").val(0);
}

$('[id$="_sel"]').on('change', function() {
    var tabla = this.id.replace('_sel', '');
    var id = $(this).val();
    
    if (id != 0) {
        var texto = $(this).find('option:selected').text();
        $('#' + tabla + '_txt').val(texto);
    } else {
        $('#' + tabla + '_txt').val('');
        $('#' + tabla + '_txt_1').val('');
    }
});
