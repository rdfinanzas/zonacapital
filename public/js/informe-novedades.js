/**
 * Informe Novedades - JavaScript
 * Módulo para generar informe de novedades de personal
 */

var Toast;
var idEmpleado = 0;
var idPersonal = "";
var idJefe = 0;

document.addEventListener('DOMContentLoaded', function () {
    // Inicializar Toast
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 6000
    });

    // Inicializar Select2
    initializeSelect2();

    // Inicializar datepickers
    initializeDatePickers();
});

/**
 * Inicializar Select2
 */
function initializeSelect2() {
    // Select2 básico para organigrama
    $('#ger_fil, #dep_fil, #servicio_fil').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        language: {
            noResults: function() {
                return 'No se encontraron resultados';
            }
        }
    });

    // Select2 con AJAX para certifica (jefes)
    $('#certifica').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Buscar jefe...',
        allowClear: true,
        minimumInputLength: 2,
        language: {
            inputTooShort: function() {
                return 'Ingrese al menos 2 caracteres';
            },
            noResults: function() {
                return 'No se encontraron resultados';
            },
            searching: function() {
                return 'Buscando...';
            }
        },
        ajax: {
            url: $('#certifica').data('url'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function (data, params) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.value
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Select2 con AJAX para personal
    $('#personal').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Buscar personal...',
        allowClear: true,
        minimumInputLength: 2,
        language: {
            inputTooShort: function() {
                return 'Ingrese al menos 2 caracteres';
            },
            noResults: function() {
                return 'No se encontraron resultados';
            },
            searching: function() {
                return 'Buscando...';
            }
        },
        ajax: {
            url: $('#personal').data('url'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function (data, params) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.value
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Evento change para certifica
    $('#certifica').on('change', function() {
        idJefe = $(this).val() ? parseInt($(this).val()) : 0;
    });

    // Evento change para personal
    $('#personal').on('change', function() {
        var data = $(this).select2('data')[0];
        if (data && data.id) {
            idPersonal = data.id;
            idEmpleado = data.id;
        } else {
            idPersonal = "";
            idEmpleado = 0;
        }
    });
}

/**
 * Inicializar los datepickers (formato MM/YYYY)
 */
function initializeDatePickers() {
    if (typeof $.fn.datetimepicker !== 'undefined') {
        if (typeof moment !== 'undefined') {
            moment.locale('es');
        }

        $('#desde_fil').datetimepicker({
            format: 'MM/YYYY',
            locale: 'es',
            useCurrent: false,
            icons: {
                time: 'fas fa-clock',
                date: 'fas fa-calendar',
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'fas fa-calendar-check',
                clear: 'fas fa-trash-alt',
                close: 'fas fa-times'
            }
        });
    } else {
        console.warn('DateTimePicker no está disponible');
    }
}

/**
 * Buscar datos para previsualización
 */
function buscar() {
    // Resetear IDs si los campos están vacíos
    if ($('#personal').val() === "" || $('#personal').val() === null) {
        idEmpleado = 0;
        idPersonal = "";
    }
    if ($('#certifica').val() === "" || $('#certifica').val() === null) {
        idJefe = 0;
    }

    // Validar período
    if ($("#d_fil").val() === "") {
        Toast.fire({
            icon: "error",
            title: "Seleccione un período"
        });
        return;
    }

    // Mostrar loading
    showLoading();

    // Construir URL con parámetros
    var params = {
        fecha: $("#d_fil").val(),
        ger: $("#ger_fil").val() || 0,
        dep: $("#dep_fil").val() || 0,
        serv: $("#servicio_fil").val() || 0,
        idEmpleado: idEmpleado || 0,
        idJefe: idJefe || 0,
        id: idPersonal || ''
    };

    $.ajax({
        url: '/informe-novedades/buscar',
        method: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            console.log('Response:', response);
            
            if (response.status === 1) {
                mostrarResultados(response.data, response.total, response.mes, response.anio, response.feriados, response.ultimo_dia);
            } else {
                Toast.fire({
                    icon: "error",
                    title: "Error al buscar datos"
                });
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            console.log('Error:', xhr.responseText);
            Toast.fire({
                icon: "error",
                title: "Error al buscar: " + error
            });
        }
    });
}

/**
 * Mostrar resultados en la tabla con formato similar al Excel
 */
function mostrarResultados(data, total, mes, anio, feriados, ultimo_dia) {
    var tabla = $('#tabla_resultados');
    tabla.empty();
    
    if (data.length === 0) {
        var colspan = 1 + parseInt(ultimo_dia) + 6;
        tabla.html('<tr><td colspan="' + colspan + '" class="text-center">No se encontraron registros</td></tr>');
    } else {
        var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        var nombreMes = meses[parseInt(mes) - 1] || mes;
        
        // Crear array de días con licencia para cada empleado
        var empleadosConLicencia = {};
        data.forEach(function(item) {
            if (item.DiasLic) {
                var dias = item.DiasLic.split(',');
                dias.forEach(function(dia) {
                    empleadosConLicencia[item.Legajo + '_' + dia.trim()] = 'L';
                });
            }
        });
        
        // Crear la tabla
        var html = '';
        
        // Headers
        html += '<thead class="text-center" style="font-size: 10px;">';
        
        // Fila 1: Títulos principales
        html += '<tr>';
        html += '<th rowspan="2" class="align-middle" style="min-width: 150px;">Apellido y Nombre</th>';
        html += '<th colspan="' + ultimo_dia + '">DÍAS</th>';
        html += '<th colspan="5">PERIODO</th>';
        html += '<th rowspan="2" class="align-middle">NÚMERO<br>Legajo</th>';
        html += '<th rowspan="2" class="align-middle" style="min-width: 200px;">Observaciones</th>';
        html += '</tr>';
        
        // Fila 2: Días del mes
        html += '<tr>';
        for (var dia = 1; dia <= ultimo_dia; dia++) {
            var fechaActual = anio + '-' + mes.padStart(2, '0') + '-' + dia.toString().padStart(2, '0');
            var numDiaSemana = new Date(fechaActual).getDay();
            var esFinSemana = (numDiaSemana === 0 || numDiaSemana === 6);
            var esFeriado = feriados && feriados[fechaActual];
            var bgColor = '';
            if (esFinSemana || esFeriado) {
                bgColor = 'background-color: #808080;';
            }
            html += '<th style="' + bgColor + ' width: 20px;">' + dia + '</th>';
        }
        html += '<th>D</th><th>M</th><th>D</th><th>M</th><th>T</th>';
        html += '</tr>';
        html += '</thead>';
        
        // Body
        html += '<tbody style="font-size: 9px;">';
        
        data.forEach(function(item) {
            var nombreCompleto = item.Apellido + ' ' + item.Nombre;
            html += '<tr>';
            html += '<td class="text-start">' + nombreCompleto + '</td>';
            
            // Días del mes
            for (var dia = 1; dia <= ultimo_dia; dia++) {
                var fechaActual = anio + '-' + mes.padStart(2, '0') + '-' + dia.toString().padStart(2, '0');
                var key = item.Legajo + '_' + fechaActual;
                var numDiaSemana = new Date(fechaActual).getDay();
                var esFinSemana = (numDiaSemana === 0 || numDiaSemana === 6);
                var esFeriado = feriados && feriados[fechaActual];
                var bgColor = '';
                var contenido = '';
                
                if (esFinSemana || esFeriado) {
                    bgColor = 'background-color: #808080;';
                }
                if (empleadosConLicencia[key]) {
                    contenido = '<span style="color: red; font-weight: bold;">L</span>';
                }
                
                html += '<td style="' + bgColor + ' text-align: center;">' + contenido + '</td>';
            }
            
            // Periodo (D, M, D, M, T)
            if (item.FechaLic) {
                var partesFecha = item.FechaLic.split('-');
                var diaInicio = partesFecha[2];
                var mesInicio = partesFecha[1];
                
                partesFecha = item.FechaLicFin.split('-');
                var diaFin = partesFecha[2];
                var mesFin = partesFecha[1];
                
                html += '<td style="text-align: center;">' + parseInt(diaInicio) + '</td>';
                html += '<td style="text-align: center;">' + parseInt(mesInicio) + '</td>';
                html += '<td style="text-align: center;">' + parseInt(diaFin) + '</td>';
                html += '<td style="text-align: center;">' + parseInt(mesFin) + '</td>';
                html += '<td style="text-align: center;">' + (item.DiasTotal || 0) + '</td>';
            } else {
                html += '<td></td><td></td><td></td><td></td><td></td>';
            }
            
            // Legajo
            html += '<td style="text-align: center;">' + item.Legajo + '</td>';
            
            // Observaciones
            html += '<td>' + (item.Novedades || '') + '</td>';
            
            html += '</tr>';
        });
        
        html += '</tbody>';
        
        tabla.html(html);
    }
    
    $('#total_registros').text(total);
    $('#resultados_section').show();
}

/**
 * Exportar informe a Excel
 */
function exportar() {
    // Resetear IDs si los campos están vacíos
    if ($('#personal').val() === "" || $('#personal').val() === null) {
        idEmpleado = 0;
        idPersonal = "";
    }
    if ($('#certifica').val() === "" || $('#certifica').val() === null) {
        idJefe = 0;
    }

    // Validar período
    if ($("#d_fil").val() === "") {
        Toast.fire({
            icon: "error",
            title: "Seleccione un período"
        });
        return;
    }

    // Mostrar loading
    showLoading();

    // Construir URL con parámetros
    var params = {
        fecha: $("#d_fil").val(),
        ger: $("#ger_fil").val() || 0,
        dep: $("#dep_fil").val() || 0,
        serv: $("#servicio_fil").val() || 0,
        idEmpleado: idEmpleado || 0,
        idJefe: idJefe || 0,
        id: idPersonal || ''
    };

    // Crear formulario temporal para la descarga
    var $form = $('<form>', {
        'method': 'GET',
        'action': '/informe-novedades/exportar'
    });

    // Agregar parámetros
    $.each(params, function (key, value) {
        $('<input>', {
            'type': 'hidden',
            'name': key,
            'value': value
        }).appendTo($form);
    });

    $form.appendTo('body').submit().remove();

    // Ocultar loading después de un momento
    setTimeout(function () {
        hideLoading();
    }, 2000);
}

/**
 * Cambiar organigrama (gerencia -> departamento -> servicio)
 */
function changeOrganigrama(tipo, el) {
    if ($(el).val() === "") {
        // Limpiar selects dependientes
        if (tipo === 0) {
            $("#dep_fil").html('<option value="">-</option>').trigger('change');
            $("#servicio_fil").html('<option value="">-</option>').trigger('change');
        } else if (tipo === 1) {
            $("#servicio_fil").html('<option value="">-</option>').trigger('change');
        }
        return;
    }

    var url = '';
    var targetSelect = '';

    switch (tipo) {
        case 0: // Gerencia cambiada -> cargar departamentos
            url = '/informe-novedades/departamentos?id=' + $(el).val();
            targetSelect = '#dep_fil';
            break;
        case 1: // Departamento cambiado -> cargar servicios
            url = '/informe-novedades/servicios?id=' + $(el).val();
            targetSelect = '#servicio_fil';
            break;
        case 2: // Servicio cambiado - no hacer nada adicional
            return;
    }

    if (!url) return;

    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function (dataResponse) {
            if (dataResponse.status === 1) {
                var dataSelect = dataResponse.response;
                var html = '<option value="">-</option>';
                dataSelect.forEach(function (registro) {
                    var id = registro.idDepartamento || registro.idServicio;
                    var nombre = registro.departamento || registro.servicio;
                    html += '<option value="' + id + '">' + nombre + '</option>';
                });
                $(targetSelect).html(html).trigger('change');
            }
        },
        error: function (xhr, status, error) {
            Toast.fire({
                icon: "error",
                title: "Error al cargar datos: " + error
            });
        }
    });
}

/**
 * Mostrar indicador de carga
 */
function showLoading() {
    if ($('#overlay').length === 0) {
        var html = `
            <div class="overlay-fixed" id="overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                 background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="text-center text-white">
                    <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                    <div class="h5">Generando informe...</div>
                </div>
            </div>
        `;
        $('body').append(html);
    } else {
        $('#overlay').show();
    }
}

/**
 * Ocultar indicador de carga
 */
function hideLoading() {
    $('#overlay').hide();
}
