/**
 * Informe Personal - JavaScript
 * Módulo para generar informes del personal
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Inicializar datepickers
    initializeDatePickers();

    // Inicializar validación del formulario
    initializeFormValidation();

    // Configurar DataTable
    initializeDataTable();

    // Limpiar filtros
    $('#btnLimpiarFiltros').on('click', function () {
        limpiarFiltros();
    });
});

/**
 * Inicializar los datepickers
 */
function initializeDatePickers() {
    // Inicializar DateTimePickers con Tempus Dominus 6 (Bootstrap 5)
    if (typeof tempusDominus !== 'undefined') {
        // Configurar idioma
        tempusDominus.DefaultOptions.localization.locale = 'es';

        // Array con todos los selectores de datepickers
        const datePickerSelectors = [
            '#desde_ap_fil_picker',
            '#hasta_ap_fil_picker',
            '#desde_fil_picker',
            '#hasta_fil_picker',
            '#desde_baja_fil_picker',
            '#hasta_baja_fil_picker'
        ];

        datePickerSelectors.forEach(selector => {
            const element = document.querySelector(selector);
            if (element) {
                try {
                    new tempusDominus.TempusDominus(element, {
                        display: {
                            components: {
                                calendar: true,
                                date: true,
                                month: true,
                                year: true,
                                decades: true,
                                clock: false,
                                hours: false,
                                minutes: false,
                                seconds: false
                            }
                        },
                        localization: {
                            locale: 'es',
                            format: 'dd/MM/yyyy'
                        }
                    });
                } catch (err) {
                    console.error(`Error initializing DatePicker for ${selector}:`, err);
                }
            }
        });
    } else if (typeof $.fn.datetimepicker !== 'undefined') {
        // Fallback para Tempus Dominus Bootstrap 4
        if (typeof moment !== 'undefined') {
            moment.locale('es');
        }

        const datePickerConfig = {
            format: 'DD/MM/YYYY',
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
        };

        // Fecha Alta AP
        $('#desde_ap_fil_picker').datetimepicker(datePickerConfig);
        $('#hasta_ap_fil_picker').datetimepicker(datePickerConfig);

        // Fecha Alta Normal
        $('#desde_fil_picker').datetimepicker(datePickerConfig);
        $('#hasta_fil_picker').datetimepicker(datePickerConfig);

        // Fecha Baja
        $('#desde_baja_fil_picker').datetimepicker(datePickerConfig);
        $('#hasta_baja_fil_picker').datetimepicker(datePickerConfig);
    } else {
        console.warn('DateTimePicker no está disponible');
    }
}

/**
 * Inicializar validación del formulario
 */
function initializeFormValidation() {
    $('#form_filter').on('submit', function (e) {
        e.preventDefault();
        buscarPersonas();
    });
}

/**
 * Inicializar DataTable
 */
function initializeDataTable() {
    $('#table_persona').DataTable({
        destroy: true, // Destruir instancia previa si existe
        paging: true,
        ordering: true,
        searching: true,
        pageLength: 50,
        lengthChange: false,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es.json',
            emptyTable: 'Realice una búsqueda para ver resultados'
        },
        columnDefs: [
            { orderable: true, targets: [0, 1, 2, 3, 4, 5, 6] },
            { orderable: false, targets: [7] } // Columna de acciones no ordenable
        ],
        order: [[1, 'asc']] // Ordenar por Apellido/Nombre
    });
}

/**
 * Buscar personas según los filtros
 */
function buscarPersonas() {
    // Mostrar loading
    showLoading();

    // Obtener datos del formulario
    const formData = $('#form_filter').serialize();

    // Realizar petición AJAX
    $.ajax({
        url: '/informe-personal/filtrar',
        method: 'GET',
        data: formData,
        dataType: 'json',
        success: function (response) {
            hideLoading();

            if (response.status === 1) {
                actualizarTabla(response.response);
            } else {
                showToast('error', 'Error al obtener los datos');
            }
        },
        error: function (xhr, status, error) {
            hideLoading();
            showToast('error', 'Error: ' + error);
        }
    });
}

/**
 * Actualizar la tabla con los resultados
 */
function actualizarTabla(data) {
    const table = $('#table_persona').DataTable();

    // Limpiar datos existentes
    table.clear();

    // Obtener permiso de lectura
    const permisoLeer = $('#permiso_leer').val() == '1';

    // Agregar nuevos datos
    if (data && data.length > 0) {
        const rows = data.map(function (persona) {
            // Crear botón de acción si tiene permiso
            let acciones = '-';
            if (permisoLeer && persona.idEmpleado) {
                acciones = '<a href="/personal/' + persona.idEmpleado + '/ver" ' +
                    'class="btn btn-primary btn-sm" title="Ver detalle">' +
                    '<i class="bi bi-eye"></i> Ver</a>';
            }

            return [
                persona.Legajo || '',
                (persona.Apellido || '') + ' ' + (persona.Nombre || ''),
                persona.DNI || '',
                persona.Edad || '',
                persona.Telefono || '-',
                persona.servicio || '-',
                persona.FAlta || '-',
                acciones
            ];
        });
        table.rows.add(rows);
    }

    // Redibujar tabla
    table.draw();
}

/**
 * Limpiar todos los filtros
 */
function limpiarFiltros() {
    // Limpiar campos de texto
    $('#dni_fil, #apellido_nombre_fil, #legajo_fil, #edad_fil, #anti_fil, #anti_ap_fil').val('');

    // Limpiar select2 (ahora todos son simples)
    $('#prof_fil, #cargo_fil, #relacion_fil, #funcion_fil, #servicio_fil').val('').trigger('change');

    // Limpiar selects normales
    $('#sexo_fil, #estado_fil, #jornada_fil, #ger_fil, #cert_fil').val('').trigger('change');

    // Limpiar datepickers
    $('#d_ap_fil, #h_ap_fil, #d_fil, #h_fil, #d_b_fil, #h_b_fil').val('');

    // Limpiar tabla
    const table = $('#table_persona').DataTable();
    table.clear().draw();

    // Resetear selects en cascada
    $('#dep_fil').html('<option value="">Todos</option>');
    $('#sector_fil').html('<option value="">Todos</option>');
}

/**
 * Exportar a Excel
 */
function exportar() {
    showLoading();

    const formData = $('#form_filter').serialize();

    // Crear formulario temporal para la descarga
    const $form = $('<form>', {
        'method': 'GET',
        'action': '/informe-personal/exportar'
    });

    // Agregar parámetros del formulario
    const params = $('#form_filter').serializeArray();
    params.forEach(function (param) {
        if (param.value) {
            $('<input>', {
                'type': 'hidden',
                'name': param.name,
                'value': param.value
            }).appendTo($form);
        }
    });

    $form.appendTo('body').submit().remove();

    // Ocultar loading después de un momento
    setTimeout(function () {
        hideLoading();
    }, 2000);
}

/**
 * Cambiar organigrama (gerencia -> departamento -> servicio -> sector)
 */
function changeOrganigrama(tipo, element) {
    const value = $(element).val();

    if (!value || value === '') {
        // Limpiar selects dependientes
        if (tipo === 0) {
            $('#dep_fil').html('<option value="">Todos</option>');
            $('#servicio_fil').html('<option value="">Todos</option>');
            $('#sector_fil').html('<option value="">Todos</option>');
        } else if (tipo === 1) {
            $('#servicio_fil').html('<option value="">Todos</option>');
            $('#servicio_fil').trigger('change'); // Actualizar select2
            $('#sector_fil').html('<option value="">Todos</option>');
        } else if (tipo === 2) {
            $('#sector_fil').html('<option value="">Todos</option>');
        }
        return;
    }

    let url = '';
    let targetSelect = '';

    switch (tipo) {
        case 0: // Gerencia cambiada -> cargar departamentos
            url = '/informe-personal/departamentos?id=' + value;
            targetSelect = '#dep_fil';
            break;
        case 1: // Departamento cambiado -> cargar servicios
            url = '/informe-personal/servicios?id=' + value;
            targetSelect = '#servicio_fil';
            break;
        case 2: // Servicio cambiado -> cargar sectores
            url = '/informe-personal/sectores?id=' + value;
            targetSelect = '#sector_fil';
            break;
    }

    if (!url) return;

    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 1) {
                let html = '<option value="">Todos</option>';
                response.response.forEach(function (item) {
                    const id = item.idDepartamento || item.idServicio || item.idSector;
                    const nombre = item.departamento || item.servicio || item.sector;
                    html += '<option value="' + id + '">' + nombre + '</option>';
                });

                // Si es select2, actualizar y recargar
                if ($(targetSelect).hasClass('select2')) {
                    $(targetSelect).html(html).trigger('change');
                } else {
                    $(targetSelect).html(html);
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar datos:', error);
        }
    });
}

/**
 * Mostrar indicador de carga
 */
function showLoading() {
    if ($('#overlay').length === 0) {
        const html = `
            <div class="overlay-fixed" id="overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                 background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="text-center text-white">
                    <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                    <div class="h5">Cargando...</div>
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

/**
 * Mostrar toast de notificación
 */
function showToast(icon, title) {
    // Usar SweetAlert2 si está disponible, sino usar alert
    if (typeof Swal !== 'undefined') {
        Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        }).fire({
            icon: icon,
            title: title
        });
    } else {
        alert(title);
    }
}
