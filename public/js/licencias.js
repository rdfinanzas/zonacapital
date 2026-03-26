var idEdit = 0;
var dataTable;
var idPersonal = 0;
var idRelacion = 0;

function editar(id, ind) {
    idEdit = id;
    var registro = dataTable[ind];
    idPersonal = registro.LegajoPersonal;

    // Seleccionar el personal en el select2
    $('#personal').val(idPersonal).trigger('change');

    $('#fechaOrden').val(registro.FechaCreacion || formatDate(new Date()));
    $('#motivo').val(registro.Motivo_Id).trigger('change');
    $('#dias').val(registro.DiasTotal);
    $('#d').val(formatDate(registro.FIni || registro.FechaLic));
    $('#h').val(formatDate(registro.FFin || registro.FechaLicFin));
    $('#obs').val(registro.ObservacionLic);

    // OM/CM y disposiciones
    $('#om').val(registro.OrdenMedica || '');
    $('#cm').val(registro.CertMedico || '');
    $('#NumDisp').val(registro.NumDisp || '').trigger('change');

    // Mostrar/ocultar OM o CM según corresponda
    if (registro.OrdenMedica && registro.OrdenMedica != 0) {
        $('.om').show();
    } else {
        $('.om').hide();
    }

    if (registro.CertMedico && registro.CertMedico != 0) {
        $('.cm').show();
    } else {
        $('.cm').hide();
    }
}

function guardar() {
    var data = $('#form_main').serializeJSON();

    // Agregar legajo del personal
    data.legajo = idPersonal;

    // Convertir valores nulos
    if (data.motivo_id === 'NULL' || data.motivo_id === '') {
        data.motivo_id = null;
    }
    if (data.NumDisp === 'NULL' || data.NumDisp === '') {
        data.NumDisp = null;
    }
    
    // Validar que haya un personal seleccionado
    if (!idPersonal || idPersonal == 0) {
        toastr.error('Debe seleccionar un personal');
        return;
    }
    
    // Validar y convertir fechas antes de enviar
    var desde = $('#d').val(); // YYYY-MM-DD
    var hasta = $('#h').val(); // YYYY-MM-DD
    
    if (!desde || !hasta) {
        toastr.error('Debe completar las fechas Desde y Hasta');
        return;
    }
    
    // Convertir a DD/MM/YYYY para el backend
    data.desde = formatToDDMMYYYY(desde);
    data.hasta = formatToDDMMYYYY(hasta);
    
    // Asegurar que el año esté seteado (tomarlo de la fecha de inicio)
    if (!data.anio) {
        var fechaDesde = new Date(desde);
        data.anio = fechaDesde.getFullYear();
    }
    
    console.log('Guardando - legajo:', idPersonal, 'anio:', data.anio);

    var url = idEdit == 0 ? 'licencias' : 'licencias/' + idEdit;
    var method = idEdit == 0 ? 'POST' : 'PUT';

    apiLaravel(url, method, data).then(function (response) {
        limpiar();
        cargarDatos();
        toastr.success('Licencia guardada correctamente');
    }).catch(function (error) {
        console.error('Error al guardar:', error);
        toastr.error(error || 'Error al guardar la licencia');
    });
}

function modalEliminar(id) {
    idEdit = id;
    $('#modal_eliminar').modal();
}

function eliminar() {
    apiLaravel('licencias/' + idEdit, 'DELETE', {}).then(function (data) {
        limpiar();
        cargarDatos();
        $('#modal_eliminar').modal('hide');
        toastr.success('Licencia eliminada correctamente');
    }).catch(function (error) {
        console.error('Error al eliminar:', error);
        toastr.error(error || 'Error al eliminar la licencia');
    });
}

function cargarDatos() {
    if (idPersonal == 0) {
        $("#table_data").empty();
        $('#info_personal').html('').hide();
        return;
    }

    apiLaravel('licencias/legajo/' + idPersonal, 'GET').then(function (response) {
        if (response.success) {
            var htmlTableLic = '';
            dataTable = response.licencias;
            var info = response.info;

            // Mostrar información del empleado
            if (info) {
                $('#info_personal').html(
                    '<strong>Relación:</strong> ' + info.Relacion +
                    ' | <strong>Fecha alta (A.P.):</strong> ' + info.FF
                ).show();
                idRelacion = info.idRelacion;
            } else {
                $('#info_personal').html('Faltan datos en el legajo').show();
                idRelacion = 0;
            }

            // Cargar licencias normales
            response.licencias.forEach(function (licencia, i) {
                var desde = licencia.FIni || '';
                var hasta = licencia.FFin || '';
                var motivo = licencia.motivo ? licencia.motivo.Motivo : '';
                var om = licencia.OrdenMedica || '';
                var cm = licencia.CertMedico || '';
                var usuario = licencia.usuario ? licencia.usuario.Usuario : '';

                htmlTableLic += '<tr style="cursor: pointer;" onclick="editar(' + licencia.IdLicencia + ', ' + i + ')" title="Click para editar">';
                htmlTableLic += '<td class="text-center font-weight-bold">' + licencia.DiasTotal + '</td>';
                htmlTableLic += '<td><small>' + desde + '</small></td>';
                htmlTableLic += '<td><small>' + hasta + '</small></td>';
                htmlTableLic += '<td><small>' + motivo + '</small></td>';
                htmlTableLic += '<td class="text-center"><small>' + (om != 0 ? om : '-') + '</small></td>';
                htmlTableLic += '<td class="text-center"><small>' + (cm != 0 ? cm : '-') + '</small></td>';
                htmlTableLic += '<td><small class="text-muted">' + usuario + '</small></td>';
                htmlTableLic += '</tr>';
            });

            $("#table_data").html(htmlTableLic);
        }
    }).catch(function (error) {
        console.error('Error al cargar datos:', error);
        toastr.error(error || 'Error al cargar las licencias');
    });
}

function limpiar() {
    idEdit = 0;
    idPersonal = 0;
    $('#form_main')[0].reset();
    $('#personal').val('').trigger('change');
    $('#info_personal').html('').hide();
    $('#motivo').val('').trigger('change');
    $('#NumDisp').val('').trigger('change');
    $('#om').val('');
    $('#cm').val('');
    $('.om').hide();
    $('.cm').hide();
    $('#info_motivo').html('');
    $("#table_data").empty();
    idRelacion = 0;
}

// Print functions
function imprimirCD(id) {
    window.open('/licencias/imprimir/cd/' + id, '_blank');
}

function imprimirArticulo30(id) {
    window.open('/licencias/imprimir/articulo30/' + id, '_blank');
}

function imprimirArticulo43(id) {
    window.open('/licencias/imprimir/articulo43/' + id, '_blank');
}

// Get days taken by motivo in the current year
function getDiasMotivoXLegajo() {
    if (!idPersonal) return;
    const motivo = $('#motivo').val();

    if (!motivo) {
        return;
    }

    apiLaravel('/licencias/dias-motivo?legajo=' + idPersonal + '&motivo=' + motivo, 'GET')
    .then(function(response) {
        if (response.success) {
            $('#info_motivo').html('Días de licencias en el año: ' + response.response + ' días').show();
        }
    })
    .catch(function(error) {
        console.error('Error getting days by motivo:', error);
    });
}

// Funciones de utilidad para conversión de fechas (igual que en LAR)
function formatToDDMMYYYY(yyyyMmDd) {
    // Convierte YYYY-MM-DD (input date nativo) a DD/MM/YYYY (para el backend)
    if (!yyyyMmDd) return '';
    const partes = yyyyMmDd.split('-');
    if (partes.length === 3) {
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }
    return yyyyMmDd;
}

function formatToYYYYMMDD(ddMmYyyy) {
    // Convierte DD/MM/YYYY (respuesta del backend) a YYYY-MM-DD (para input date nativo)
    if (!ddMmYyyy) return '';
    const partes = ddMmYyyy.split('/');
    if (partes.length === 3) {
        return partes[2] + '-' + partes[1] + '-' + partes[0];
    }
    return ddMmYyyy;
}

// Date calculation functions
function calcularXDia() {
    const dias = parseInt($('#dias').val());
    const desde = $('#d').val(); // YYYY-MM-DD del input nativo

    if (!dias || !desde) {
        toastr.warning('Debe completar los días y la fecha desde');
        return;
    }

    // Convertir a DD/MM/YYYY para el backend
    const desdeBackend = formatToDDMMYYYY(desde);
    const corridos = $('#corridos').is(':checked') ? 1 : 0;

    console.log('Enviando al backend - dias:', dias, 'desde:', desdeBackend, 'corridos:', corridos);

    apiLaravel('/licencias/calcular-fecha', 'POST', {
        dias: dias,
        desde: desdeBackend,
        corridos: corridos
    })
    .then(function(response) {
        console.log('Respuesta del backend:', response);
        if (response.success) {
            // Convertir DD/MM/YYYY a YYYY-MM-DD para el input nativo
            const fechaHastaInput = formatToYYYYMMDD(response.hasta);
            console.log('Fecha hasta convertida:', fechaHastaInput);
            
            if (fechaHastaInput) {
                $('#h').val(fechaHastaInput);
            } else {
                console.error('Error al convertir fecha:', response.hasta);
                toastr.error('Error al procesar la fecha recibida');
            }
        }
    })
    .catch(function(error) {
        console.error('Error calculating date:', error);
    });
}

function calcularXFecha() {
    const desde = $('#d').val(); // YYYY-MM-DD
    const hasta = $('#h').val(); // YYYY-MM-DD

    if (!desde || !hasta) {
        toastr.warning('Debe completar las fechas desde y hasta');
        return;
    }

    // Convertir a DD/MM/YYYY para el backend
    const desdeBackend = formatToDDMMYYYY(desde);
    const hastaBackend = formatToDDMMYYYY(hasta);
    const corridos = $('#corridos').is(':checked') ? 1 : 0;

    apiLaravel('/licencias/calcular-dias', 'POST', {
        desde: desdeBackend,
        hasta: hastaBackend,
        corridos: corridos
    })
    .then(function(response) {
        if (response.success) {
            $('#dias').val(response.dias);
        }
    })
    .catch(function(error) {
        console.error('Error calculating days:', error);
    });
}



// Helper function to format dates (para edición de registros)
// Convierte DD/MM/YYYY (del backend) a YYYY-MM-DD (para input date nativo)
function formatDate(dateStr) {
    if (!dateStr) return '';
    
    // Si viene en formato DD/MM/YYYY, convertir a YYYY-MM-DD
    if (typeof dateStr === 'string' && dateStr.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
        var parts = dateStr.split('/');
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }
    
    // Si ya viene en formato YYYY-MM-DD, devolverlo directamente
    if (typeof dateStr === 'string' && dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return dateStr;
    }
    
    // Para otros formatos, intentar con Date
    var date = new Date(dateStr);
    if (isNaN(date.getTime())) return '';
    
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    return year + '-' + month + '-' + day; // YYYY-MM-DD para input nativo
}

// Función para recalcular automáticamente cuando cambian los valores
function setupAutoCalculate() {
    // Al cambiar la cantidad de días, recalcular fecha hasta
    $('#dias').on('change keyup', function() {
        var dias = parseInt($(this).val());
        var desde = $('#d').val();
        if (dias && desde) {
            calcularXDia();
        }
    });
    
    // Al cambiar la fecha desde, recalcular fecha hasta
    $('#d').on('change', function() {
        var dias = parseInt($('#dias').val());
        var desde = $(this).val();
        if (dias && desde) {
            calcularXDia();
        }
    });
}

// Document ready
$(function () {
    $('.select2').select2();
    
    // Configurar cálculo automático (como en licencias-lar)
    setupAutoCalculate();
    
    // Evento change del checkbox corridos - recalcular automáticamente
    $('#corridos').on('change', function() {
        const dias = parseInt($('#dias').val());
        const desde = $('#d').val();
        
        if (dias && desde) {
            console.log('Checkbox corridos cambiado a:', $(this).is(':checked'), '- recalculando...');
            calcularXDia();
        }
    });

    // Evento change de personal (select2)
    $('#personal').on('change', function () {
        // El valor ahora es el legajo directamente
        idPersonal = $(this).val();
        if (idPersonal) {
            cargarDatos();
        } else {
            $("#table_data").empty();
            $('#info_personal').html('').hide();
        }
    });

    $('#motivo').on('change', function () {
        if ($(this).val()) {
            $(".om").show();
            $(".cm").show();
            // Cargar días tomados del motivo en el año actual
            getDiasMotivoXLegajo();
        } else {
            $(".om").hide();
            $(".cm").hide();
            $('#info_motivo').html('').hide();
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

    $('#btn_eliminar').click(function () {
        if (idEdit > 0) {
            modalEliminar(idEdit);
        }
    });
});
