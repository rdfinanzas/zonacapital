var idEdit = 0;
var dataTable = [];
var pacienteData = null;
var medicamentosRecetaTemp = [];
var adultoMayorActual = null;

$(function() {
    $('.select2').select2({ 
        width: '100%',
        dropdownParent: $('#modal_paciente'),
        language: { noResults: () => 'No encontrado' }
    });
    
    $('#receta_medicamento_select').select2({
        width: '100%',
        dropdownParent: $('#modal_receta'),
        language: { noResults: () => 'No encontrado' }
    });

    $('#cambio_bien_id').select2({
        width: '100%',
        dropdownParent: $('#modal_cambio'),
        language: { noResults: () => 'No encontrado' }
    });
    
    cargarTabla();
    inicializarEventos();
});

function inicializarEventos() {
    $('#btn_add').click(function() {
        limpiarFormulario();
        $('#modal_title').html('<i class="fas fa-user-plus mr-2"></i>Inscribir Paciente');
        $('#btn_eliminar_paciente').hide();
        $('#modal_paciente').modal('show');
    });

    $('#btn_buscar_dni').click(buscarPaciente);
    $('#dni').keypress(function(e) {
        if (e.which === 13) { e.preventDefault(); buscarPaciente(); }
    });

    $('#btn_guardar_nuevo_paciente').click(guardarNuevoPaciente);
    $('#btn_cancelar_nuevo').click(function() {
        $('#form_nuevo_paciente').slideUp(200);
        $('#dni').val('').focus();
    });

    $('#form_paciente_main').submit(function(e) {
        e.preventDefault();
        guardarPaciente();
    });

    $('#btn_limpiar').click(limpiarFormulario);

    $('#btn_eliminar_paciente').click(function() {
        $('#modal_eliminar').modal('show');
    });

    $('#btn_eliminar_modal').click(eliminarPaciente);

    $('#form_receta').submit(function(e) {
        e.preventDefault();
        guardarReceta();
    });

    $('#btn_add_receta_medicamento').click(agregarMedicamentoReceta);

    $('#form_entrega').submit(function(e) {
        e.preventDefault();
        guardarEntrega();
    });

    $('#form_verificar').submit(function(e) {
        e.preventDefault();
        guardarVerificacion();
    });

    $('#form_cambio').submit(function(e) {
        e.preventDefault();
        guardarCambioMedicamento();
    });

    $('#filtro_dni, #filtro_nombre').keypress(function(e) {
        if (e.which === 13) refrescarTabla();
    });
}

function buscarPaciente() {
    var dni = $('#dni').val().trim();
    if (!dni) { toastr.warning('Ingrese un DNI'); return; }

    $('#form_nuevo_paciente').hide();
    $('#btn_buscar_dni').html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

    fetch('adulto-mayor/buscar-paciente?dni=' + encodeURIComponent(dni), {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function(data) {
        $('#btn_buscar_dni').html('<i class="fas fa-search mr-1"></i> Buscar').prop('disabled', false);
        
        if (data.success) {
            pacienteData = data.paciente;
            if (data.ya_inscripto) {
                toastr.info('El paciente ya está inscripto. Puede editar los datos.');
                // Si ya está inscripto, buscar sus datos para editar
                buscarAdultoMayorPorPaciente(data.paciente.IdPacienteRegTrab);
            } else {
                mostrarDatosPaciente(data.paciente);
            }
        } else if (data.nuevo) {
            mostrarFormularioNuevoPaciente(dni);
        } else {
            toastr.error(data.message || 'Error al buscar');
        }
    })
    .catch(function(error) {
        $('#btn_buscar_dni').html('<i class="fas fa-search mr-1"></i> Buscar').prop('disabled', false);
        console.error('Error:', error);
        toastr.error('Error de conexión');
    });
}

function mostrarFormularioNuevoPaciente(dni) {
    $('#nuevo_dni').val(dni);
    $('#nuevo_nombre').val('');
    $('#nuevo_fecha_nac').val('');
    $('#nuevo_sexo').val('0');
    $('#nuevo_celular').val('');
    $('#nuevo_domicilio').val('');
    $('#form_nuevo_paciente').slideDown(200);
    $('#nuevo_nombre').focus();
}

function guardarNuevoPaciente() {
    var data = {
        DNI: $('#nuevo_dni').val(),
        ApellidoNombre: $('#nuevo_nombre').val().trim(),
        FechaNacimiento: $('#nuevo_fecha_nac').val() || null,
        Sexo: $('#nuevo_sexo').val(),
        Celular: $('#nuevo_celular').val().trim() || null,
        Domicilio: $('#nuevo_domicilio').val().trim() || null
    };

    if (!data.ApellidoNombre) { toastr.warning('Ingrese el Apellido y Nombre'); $('#nuevo_nombre').focus(); return; }

    $('#btn_guardar_nuevo_paciente').html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...').prop('disabled', true);

    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('adulto-mayor/crear-paciente', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(function(response) {
        $('#btn_guardar_nuevo_paciente').html('<i class="fas fa-save mr-1"></i> Guardar Paciente').prop('disabled', false);
        
        if (response.success) {
            toastr.success('Paciente creado correctamente');
            $('#form_nuevo_paciente').slideUp(200);
            pacienteData = response.paciente;
            mostrarDatosPaciente(response.paciente);
        } else {
            toastr.error(response.message || 'Error al crear paciente');
        }
    })
    .catch(function(error) {
        $('#btn_guardar_nuevo_paciente').html('<i class="fas fa-save mr-1"></i> Guardar Paciente').prop('disabled', false);
        toastr.error('Error al crear paciente');
        console.error(error);
    });
}

function mostrarDatosPaciente(paciente) {
    $('#paciente_id').val(paciente.IdPacienteRegTrab);
    $('#ApellidoNombre').val(paciente.ApellidoNombre);
    $('#FechaNacimiento').val(paciente.FechaNacimiento ? formatFecha(paciente.FechaNacimiento) : '');
    $('#Celular').val(paciente.Celular || '');
    $('#Domicilio').val(paciente.Domicilio || '');

    $('#form_paciente').slideDown(200);
    $('#footer_paciente').show();
    $('#row_busqueda').hide();
}

function guardarPaciente() {
    var data = {
        paciente_id: $('#paciente_id').val(),
        servicio_id: $('#servicio_id').val(),
        fecha_inscripcion: $('#fecha_inscripcion').val(),
        estado: $('#estado').val(),
        observaciones_generales: $('#observaciones_generales').val()
    };

    var url = idEdit > 0 ? '/adulto-mayor/' + idEdit : '/adulto-mayor';
    var method = idEdit > 0 ? 'PUT' : 'POST';
    
    var token = $('meta[name="csrf-token"]').attr('content');

    $('#btn_submit_paciente').html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...').prop('disabled', true);

    $.ajax({
        url: url,
        type: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            $('#btn_submit_paciente').html('<i class="fas fa-save mr-1"></i> Guardar').prop('disabled', false);
            if (response.success) {
                toastr.success(response.message);
                $('#modal_paciente').modal('hide');
                idEdit = 0;
                cargarTabla();
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            $('#btn_submit_paciente').html('<i class="fas fa-save mr-1"></i> Guardar').prop('disabled', false);
            try {
                var response = JSON.parse(xhr.responseText);
                toastr.error(response.message || 'Error al guardar');
            } catch(e) {
                toastr.error('Error al guardar');
            }
        }
    });
    
    return false;
}

function buscarAdultoMayorPorPaciente(pacienteId) {
    fetch('adulto-mayor/filtrar?paciente_id=' + pacienteId, {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function(response) {
        var registros = response.data || [];
        if (registros.length > 0) {
            var am = registros[0];
            idEdit = am.id;
            $('#adulto_mayor_id').val(am.id);
            $('#servicio_id').val(am.servicio_id).trigger('change');
            $('#fecha_inscripcion').val(am.fecha_inscripcion || '');
            $('#estado').val(am.estado);
            $('#observaciones_generales').val(am.observaciones_generales || '');
            $('#btn_eliminar_paciente').show();
            mostrarDatosPaciente(pacienteData);
        } else {
            mostrarDatosPaciente(pacienteData);
        }
    })
    .catch(function() {
        mostrarDatosPaciente(pacienteData);
    });
}

function editarPaciente(id) {
    idEdit = id;
    $('#adulto_mayor_id').val(id);
    $('#modal_title').html('<i class="fas fa-user-edit mr-2"></i>Editar Paciente');
    $('#btn_eliminar_paciente').show();
    $('#row_busqueda').hide();
    $('#form_nuevo_paciente').hide();
    $('#form_paciente').show();
    $('#footer_paciente').show();

    fetch('/adulto-mayor/' + id, {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(response) {
        if (response.success) {
            var data = response.data;
            
            if (data.paciente) {
                $('#dni').val(data.paciente.DNI);
                $('#paciente_id').val(data.paciente.IdPacienteRegTrab);
                $('#ApellidoNombre').val(data.paciente.ApellidoNombre);
                $('#FechaNacimiento').val(data.paciente.FechaNacimiento ? formatFecha(data.paciente.FechaNacimiento) : '');
                $('#Celular').val(data.paciente.Celular || '');
                $('#Domicilio').val(data.paciente.Domicilio || '');
            }

            $('#servicio_id').val(data.servicio_id).trigger('change');
            $('#fecha_inscripcion').val(data.fecha_inscripcion || '');
            $('#estado').val(data.estado);
            $('#observaciones_generales').val(data.observaciones_generales || '');

            $('#modal_paciente').modal('show');
        }
    });
}

function eliminarPaciente() {
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('adulto-mayor/' + idEdit, {
        method: 'DELETE',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function(response) {
        if (response.success) {
            toastr.success(response.message);
            $('#modal_eliminar').modal('hide');
            $('#modal_paciente').modal('hide');
            limpiarFormulario();
            cargarTabla();
        } else {
            toastr.error(response.message);
        }
    })
    .catch(function() {
        toastr.error('Error al eliminar');
    });
}

// ==================== RECETAS ====================

function nuevaReceta(adultoMayorId, pacienteNombre) {
    $('#receta_adulto_mayor_id').val(adultoMayorId);
    $('#receta_id').val('');
    $('#receta_fecha').val('{{ date("Y-m-d") }}');
    $('#receta_programa_id').val('');
    $('#receta_diagnostico').val('');
    medicamentosRecetaTemp = [];
    renderizarMedicamentosReceta();
    $('#modal_receta').modal('show');
}

function editarReceta(recetaId) {
    fetch('adulto-mayor/receta/' + recetaId, {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function(response) {
        if (response.success) {
            var data = response.data;
            $('#receta_adulto_mayor_id').val(data.adulto_mayor_id);
            $('#receta_id').val(data.id);
            $('#receta_fecha').val(data.fecha_receta);
            $('#receta_programa_id').val(data.programa_id || '');
            $('#receta_diagnostico').val(data.diagnostico || '');
            
            medicamentosRecetaTemp = [];
            if (data.detalles) {
                data.detalles.forEach(function(d) {
                    medicamentosRecetaTemp.push({
                        id: d.id,
                        bien_id: d.bien_id,
                        nombre: d.bien ? d.bien.Nombre : 'Sin nombre',
                        dosis: d.dosis,
                        frecuencia: d.frecuencia,
                        observaciones: d.observaciones,
                        entregado: d.entregado
                    });
                });
            }
            renderizarMedicamentosReceta();
            $('#modal_receta').modal('show');
        }
    });
}

function agregarMedicamentoReceta() {
    var bienId = $('#receta_medicamento_select').val();
    var nombre = $('#receta_medicamento_select option:selected').data('nombre');
    var dosis = $('#receta_dosis').val().trim();
    var frecuencia = $('#receta_frecuencia').val().trim();
    var obs = $('#receta_obs').val().trim();

    if (!bienId) { toastr.warning('Seleccione un medicamento'); return; }

    medicamentosRecetaTemp.push({
        bien_id: bienId,
        nombre: nombre,
        dosis: dosis,
        frecuencia: frecuencia,
        observaciones: obs,
        entregado: 'N'
    });

    renderizarMedicamentosReceta();
    $('#receta_medicamento_select').val('').trigger('change');
    $('#receta_dosis').val('');
    $('#receta_frecuencia').val('');
    $('#receta_obs').val('');
}

function renderizarMedicamentosReceta() {
    var tbody = $('#tbody_receta_medicamentos');
    tbody.empty();

    if (medicamentosRecetaTemp.length === 0) {
        $('#sin_receta_medicamentos').show();
        $('#tabla_receta_medicamentos').hide();
        return;
    }

    $('#sin_receta_medicamentos').hide();
    $('#tabla_receta_medicamentos').show();

    medicamentosRecetaTemp.forEach(function(med, index) {
        var entregadoBadge = med.entregado === 'S' 
            ? '<span class="badge badge-entregado">✓</span>' 
            : '<span class="badge badge-no-entregado">✗</span>';
        
        var tr = '<tr>';
        tr += '<td>' + escapeHtml(med.nombre) + '</td>';
        tr += '<td>' + escapeHtml(med.dosis || '-') + '</td>';
        tr += '<td>' + escapeHtml(med.frecuencia || '-') + '</td>';
        tr += '<td class="text-center">' + entregadoBadge + '</td>';
        tr += '<td class="text-center">';
        if (!med.id) {
            tr += '<button type="button" class="btn btn-danger btn-xs" onclick="eliminarMedicamentoRecetaTemp(' + index + ')" title="Quitar">';
            tr += '<i class="fas fa-times"></i></button>';
        }
        tr += '</td>';
        tr += '</tr>';
        tbody.append(tr);
    });
}

function eliminarMedicamentoRecetaTemp(index) {
    medicamentosRecetaTemp.splice(index, 1);
    renderizarMedicamentosReceta();
}

function guardarReceta() {
    if (medicamentosRecetaTemp.length === 0) { toastr.warning('Agregue al menos un medicamento'); return; }

    var data = {
        adulto_mayor_id: $('#receta_adulto_mayor_id').val(),
        fecha_receta: $('#receta_fecha').val(),
        programa_id: $('#receta_programa_id').val() || null,
        diagnostico: $('#receta_diagnostico').val(),
        detalles: medicamentosRecetaTemp
    };

    var recetaId = $('#receta_id').val();
    var url = recetaId ? 'adulto-mayor/receta/' + recetaId : window.laravelRoutes.adultoMayorStoreReceta;
    var method = recetaId ? 'PUT' : 'POST';
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(function(response) {
        if (response.success) {
            toastr.success(response.message);
            $('#modal_receta').modal('hide');
            cargarTabla();
        } else {
            toastr.error(response.message);
        }
    })
    .catch(function() {
        toastr.error('Error al guardar receta');
    });
}

function eliminarReceta(id) {
    if (!confirm('¿Eliminar esta receta?')) return;
    
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch('adulto-mayor/receta/' + id, {
        method: 'DELETE',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function(response) {
        if (response.success) {
            toastr.success(response.message);
            cargarTabla();
        } else {
            toastr.error(response.message);
        }
    })
    .catch(function() {
        toastr.error('Error al eliminar receta');
    });
}

// ==================== VERIFICACIÓN ====================

function verificarReceta(recetaId) {
    $('#verificar_receta_id').val(recetaId);
    $('#verificar_estado').val('verificada');
    $('#verificar_obs').val('');
    $('#modal_verificar').modal('show');
}

function guardarVerificacion() {
    var recetaId = $('#verificar_receta_id').val();
    var url = window.laravelRoutes.adultoMayorVerificar.replace(':id', recetaId);
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify({
            estado_verificacion: $('#verificar_estado').val(),
            observaciones_verificacion: $('#verificar_obs').val()
        })
    })
    .then(r => r.json())
    .then(function(response) {
        if (response.success) {
            toastr.success('Receta verificada');
            $('#modal_verificar').modal('hide');
            cargarTabla();
        } else {
            toastr.error(response.message);
        }
    })
    .catch(function() {
        toastr.error('Error al verificar');
    });
}

// ==================== ENTREGAS ====================

function abrirEntrega(detalleId, recetaId, nombreMedicamento) {
    $('#entrega_detalle_id').val(detalleId);
    $('#entrega_receta_id').val(recetaId);
    $('#entrega_medicamento_nombre').text(nombreMedicamento);
    $('#entrega_fecha').val('{{ date("Y-m-d") }}');
    $('#entrega_cantidad').val(1);
    $('#entrega_observaciones').val('');
    $('#modal_entrega').modal('show');
}

function guardarEntrega() {
    var data = {
        receta_detalle_id: $('#entrega_detalle_id').val(),
        receta_id: $('#entrega_receta_id').val(),
        fecha_entrega: $('#entrega_fecha').val(),
        cantidad: $('#entrega_cantidad').val(),
        observaciones: $('#entrega_observaciones').val()
    };

    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('adulto-mayor/entrega', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(function(response) {
        if (response.success) {
            toastr.success('Entrega registrada');
            $('#modal_entrega').modal('hide');
            cargarTabla();
        } else {
            toastr.error(response.message);
        }
    })
    .catch(function() {
        toastr.error('Error al registrar entrega');
    });
}

// ==================== CAMBIO MEDICAMENTO ====================

function cambiarMedicamento(detalleId) {
    $('#cambio_detalle_original_id').val(detalleId);
    $('#cambio_bien_id').val('').trigger('change');
    $('#cambio_dosis').val('');
    $('#cambio_frecuencia').val('');
    $('#cambio_motivo').val('');
    $('#cambio_obs').val('');
    $('#modal_cambio').modal('show');
}

function guardarCambioMedicamento() {
    var data = {
        detalle_original_id: $('#cambio_detalle_original_id').val(),
        bien_id: $('#cambio_bien_id').val(),
        dosis: $('#cambio_dosis').val(),
        frecuencia: $('#cambio_frecuencia').val(),
        motivo_cambio: $('#cambio_motivo').val(),
        observaciones: $('#cambio_obs').val()
    };

    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(window.laravelRoutes.adultoMayorCambiarMedicamento, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(function(response) {
        if (response.success) {
            toastr.success('Medicamento cambiado');
            $('#modal_cambio').modal('hide');
            cargarTabla();
        } else {
            toastr.error(response.message);
        }
    })
    .catch(function() {
        toastr.error('Error al cambiar medicamento');
    });
}

// ==================== UTILIDADES ====================

function limpiarFormulario() {
    idEdit = 0;
    pacienteData = null;
    medicamentosRecetaTemp = [];
    adultoMayorActual = null;
    
    $('#form_paciente_main')[0].reset();
    $('#form_paciente').hide();
    $('#form_nuevo_paciente').hide();
    $('#footer_paciente').hide();
    $('#row_busqueda').show();
    $('#tbody_receta_medicamentos').empty();
    $('#sin_receta_medicamentos').show();
    $('#tabla_receta_medicamentos').hide();
    $('#btn_eliminar_paciente').hide();
    
    $('.select2').val('').trigger('change');
}

var currentPage = 1;
var perPage = 25;

function cargarTabla() {
    var params = {
        page: currentPage,
        cantidad: perPage,
        servicio_id: $('#filtro_servicio').val(),
        estado: $('#filtro_estado').val(),
        dni: $('#filtro_dni').val(),
        nombre: $('#filtro_nombre').val()
    };

    fetch('adulto-mayor/filtrar?' + $.param(params), {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function(data) {
        dataTable = data.data || [];
        var html = '';
        
        if (dataTable.length === 0) {
            html = '<tr><td colspan="9" class="text-center text-muted py-4">No se encontraron registros</td></tr>';
        } else {
            dataTable.forEach(function(r) {
                var pac = r.paciente || {};
                var ultimaReceta = r.ultima_receta;
                
                var estadoVerificacion = '-';
                if (ultimaReceta) {
                    if (ultimaReceta.estado_verificacion === 'pendiente') estadoVerificacion = '<span class="badge badge-pendiente">⏳</span>';
                    else if (ultimaReceta.estado_verificacion === 'verificada') estadoVerificacion = '<span class="badge badge-verificada">✓</span>';
                    else if (ultimaReceta.estado_verificacion === 'rechazada') estadoVerificacion = '<span class="badge badge-rechazada">✗</span>';
                }
                
                var estadoEntrega = '-';
                if (ultimaReceta) {
                    if (ultimaReceta.estado_entrega === 'pendiente') estadoEntrega = '<span class="badge badge-pendiente">⏳</span>';
                    else if (ultimaReceta.estado_entrega === 'parcial') estadoEntrega = '<span class="badge bg-info">📦</span>';
                    else if (ultimaReceta.estado_entrega === 'completada') estadoEntrega = '<span class="badge badge-entregado">✓</span>';
                    else if (ultimaReceta.estado_entrega === 'no_entregada') estadoEntrega = '<span class="badge badge-rechazada">✗</span>';
                }
                
                var estadoBadge = '';
                if (r.estado === 'activo') estadoBadge = '<span class="badge badge-success">Activo</span>';
                else if (r.estado === 'inactivo') estadoBadge = '<span class="badge badge-secondary">Inactivo</span>';
                else if (r.estado === 'fallecido') estadoBadge = '<span class="badge badge-dark">Fallecido</span>';

                html += '<tr>';
                html += '<td><small>' + (r.servicio ? r.servicio.servicio : '-') + '</small></td>';
                html += '<td><small>' + (pac.DNI || '-') + '</small></td>';
                html += '<td>' + (pac.ApellidoNombre || '-') + '</td>';
                html += '<td><small>' + formatFecha(r.fecha_inscripcion) + '</small></td>';
                html += '<td><small>' + (ultimaReceta ? formatFecha(ultimaReceta.fecha_receta) : '-') + '</small></td>';
                html += '<td class="text-center">' + estadoVerificacion + '</td>';
                html += '<td class="text-center">' + estadoEntrega + '</td>';
                html += '<td>' + estadoBadge + '</td>';
                html += '<td>';
                html += '<div class="btn-group btn-group-sm">';
                html += '<button class="btn btn-primary" onclick="editarPaciente(' + r.id + ')" title="Editar"><i class="fas fa-edit"></i></button>';
                html += '<button class="btn btn-success" onclick="nuevaReceta(' + r.id + ', \'' + escapeHtml(pac.ApellidoNombre) + '\')" title="Nueva Receta"><i class="fas fa-prescription"></i></button>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
            });
        }
        
        $("#table_data").html(html);
        $("#total_registros").text((data.total || 0) + ' registros');
        renderPagination(data);
    })
    .catch(function() {
        $("#table_data").html('<tr><td colspan="9" class="text-center text-muted py-4">Error al cargar datos</td></tr>');
    });
}

function renderPagination(data) {
    var pagination = $('#page-selection');
    pagination.empty();
    
    if (!data.last_page || data.last_page <= 1) return;

    var html = '<ul class="pagination pagination-sm mb-0 justify-content-end">';
    
    if (data.prev_page_url) {
        html += '<li class="page-item"><a class="page-link" href="javascript:goToPage(' + (data.current_page - 1) + ')">«</a></li>';
    }
    
    var start = Math.max(1, data.current_page - 2);
    var end = Math.min(data.last_page, start + 4);
    
    for (var i = start; i <= end; i++) {
        if (i === data.current_page) {
            html += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
        } else {
            html += '<li class="page-item"><a class="page-link" href="javascript:goToPage(' + i + ')">' + i + '</a></li>';
        }
    }
    
    if (data.next_page_url) {
        html += '<li class="page-item"><a class="page-link" href="javascript:goToPage(' + (data.current_page + 1) + ')">»</a></li>';
    }
    
    html += '</ul>';
    pagination.html(html);
    
    var selectHtml = '<select class="form-control form-control-sm" onchange="changePerPage(this)" style="width: 60px;">';
    [10, 25, 50, 100].forEach(function(n) {
        selectHtml += '<option value="' + n + '"' + (data.per_page === n ? ' selected' : '') + '>' + n + '</option>';
    });
    selectHtml += '</select>';
    $('#page-selection_num_page').html(selectHtml);
}

function goToPage(page) { currentPage = page; cargarTabla(); }
function changePerPage(select) { perPage = parseInt(select.value); currentPage = 1; cargarTabla(); }
function refrescarTabla() { currentPage = 1; cargarTabla(); }

function exportarExcel() {
    if ($("#overlay").length == 0) {
        $('body').append('<div class="overlay-fixed overlay-wrapper" id="overlay"><div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Generando...</div></div></div>');
    } else {
        $("#overlay").show();
    }

    var params = { servicio_id: $('#filtro_servicio').val(), estado: $('#filtro_estado').val() };

    fetch('adulto-mayor/exportar?' + $.param(params), { headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('Authorization') || '') } })
    .then(resp => resp.blob())
    .then(blob => {
        $("#overlay").hide();
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'adulto_mayor_' + new Date().toISOString().slice(0,19).replace(/:/g,'-') + '.xlsx';
        a.click();
    })
    .catch(() => { $("#overlay").hide(); toastr.error('Error al exportar'); });
}

function escapeHtml(text) {
    if (!text) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

function formatFecha(fecha) {
    if (!fecha) return '-';
    var parts = fecha.split('-');
    return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : fecha;
}

window.editarPaciente = editarPaciente;
window.nuevaReceta = nuevaReceta;
window.editarReceta = editarReceta;
window.eliminarReceta = eliminarReceta;
window.verificarReceta = verificarReceta;
window.abrirEntrega = abrirEntrega;
window.cambiarMedicamento = cambiarMedicamento;
window.eliminarMedicamentoRecetaTemp = eliminarMedicamentoRecetaTemp;
window.refrescarTabla = refrescarTabla;
window.exportarExcel = exportarExcel;
window.goToPage = goToPage;
window.changePerPage = changePerPage;
