/**
 * Gestión de Feriados - Laravel
 * Migrado del sistema PHP original
 */

// Variables globales
let dataTable = [];
let pagina = 1;
let Toast;
let indEditar = 0;
let idEdit = 0;
let idEliminar = 0;

/**
 * Modal para confirmar eliminación
 */
function modalEliminar(id = null) {
    if (id == null) {
        idEliminar = idEdit;
    } else {
        idEliminar = id;
    }
    $("#modal_eliminar").modal('show');
}

/**
 * Eliminar feriado
 */
function eliminar() {
    if (!idEliminar) {
        Toast.fire({
            icon: 'error',
            title: 'No se ha seleccionado ningún feriado para eliminar'
        });
        return;
    }

    const url = window.feriadosRoutes.destroy.replace(':id', idEliminar);

    $("#modal_eliminar").modal('hide');

    apiLaravel(url, 'DELETE')
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message || 'Feriado eliminado correctamente'
                });
                limpiar();
                refrescarTabla();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al eliminar el feriado'
                });
            }
        })
        .catch(error => {
            console.error('Error al eliminar:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al eliminar el feriado'
            });
        });
}

/**
 * Editar feriado
 */
function editar(id, ind) {
    $("html, body").animate({ scrollTop: 0 }, 600);
    $("#card_form").fadeIn("slow");
    limpiar();

    idEdit = id;
    indEditar = ind;

    // Cargar datos del feriado seleccionado
    if (dataTable[ind]) {
        // Convertir de DD/MM/YYYY a YYYY-MM-DD para el input type="date"
        const fechaOriginal = dataTable[ind].FF || '';
        if (fechaOriginal && fechaOriginal.includes('/')) {
            const partes = fechaOriginal.split('/');
            const fechaFormatoInput = `${partes[2]}-${partes[1].padStart(2, '0')}-${partes[0].padStart(2, '0')}`;
            $("#f_fer").val(fechaFormatoInput);
        } else {
            $("#f_fer").val('');
        }
        $("#feriado").val(dataTable[ind].Feriado || '');
    }
}

/**
 * Refrescar tabla (reiniciar en página 1)
 */
function refrescarTabla() {
    pagina = 1;
    cargarTabla();
}

/**
 * Cambiar de página
 */
function cambiarPagina(nuevaPagina) {
    pagina = nuevaPagina;
    cargarTabla();
}

/**
 * Determinar si un feriado es fijo basándose en nombres conocidos
 */
function esFeriadoFijo(nombreFeriado) {
    const feriadosFijos = [
        'Año Nuevo',
        'Día del Trabajador',
        'Día de la Revolución de Mayo',
        'Revolución de Mayo',
        'Día de la Independencia',
        'Independencia',
        'Día de San Martín',
        'San Martín',
        'Día de la Soberanía Nacional',
        'Soberanía Nacional',
        'Inmaculada Concepción',
        'Navidad'
    ];

    return feriadosFijos.some(fijo =>
        nombreFeriado.toLowerCase().includes(fijo.toLowerCase())
    );
}

/**
 * Cargar tabla de feriados con paginación
 */
function cargarTabla() {
    $('#page-selection').unbind();

    const cantidad = $("#page-selection_input_num_page").val() || 10;

    const params = {
        pagina: pagina,
        cantidad: cantidad
    };

    apiLaravel(window.feriadosRoutes.listar, 'GET', params)
        .then(response => {
            if (response.success) {
                dataTable = response.data;

                let htmlTable = "";

                dataTable.forEach(function (registro, i) {
                    htmlTable += "<tr>";
                    htmlTable += "<td>" + (registro.FF || '') + "</td>";
                    htmlTable += "<td><small>" + (registro.Feriado || '') + "</small></td>";

                    // Usar el campo EsFijo de la base de datos
                    const tipoFeriado = registro.EsFijo == 1 ?
                        '<span class="badge badge-success" style="background-color: #28a745 !important; color: #fff !important;"><i class="fas fa-calendar-check"></i> Fijo</span>' :
                        '<span class="badge badge-primary" style="background-color: #007bff !important; color: #fff !important;"><i class="fas fa-calendar-plus"></i> Variable</span>';
                    htmlTable += "<td>" + tipoFeriado + "</td>";

                    htmlTable += "<td>";
                    htmlTable += '<div class="btn-group">';

                    if (window.feriadosPermisos.editar) {
                        htmlTable += '<button type="button" onclick="editar(' + registro.IdFeriado + ',' + i + ')" class="btn btn-primary btn-xs" title="Editar">';
                        htmlTable += '<i class="fas fa-edit"></i></button>';
                    }

                    if (window.feriadosPermisos.eliminar) {
                        htmlTable += '<button type="button" onclick="modalEliminar(' + registro.IdFeriado + ')" class="btn btn-danger btn-xs" title="Eliminar">';
                        htmlTable += '<i class="fa fa-trash"></i></button>';
                    }

                    htmlTable += '</div>';
                    htmlTable += "</td>";
                    htmlTable += "</tr>";
                });

                $("#table_data").html(htmlTable);

                // Información de paginación
                $('#total_info').html(response.total + " registros");

                // Configurar paginación usando la paginación personalizada
                if (response.paginas > 1) {
                    // Generar HTML de paginación
                    let paginationHtml = '<div class="custom-pagination"><ul class="pagination justify-content-center">';

                    // Botón anterior
                    if (pagina > 1) {
                        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${pagina - 1})">&laquo;</a></li>`;
                    } else {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
                    }

                    // Páginas
                    let inicio = Math.max(1, pagina - 2);
                    let fin = Math.min(response.paginas, pagina + 2);

                    for (let i = inicio; i <= fin; i++) {
                        if (i === pagina) {
                            paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                        } else {
                            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a></li>`;
                        }
                    }

                    // Botón siguiente
                    if (pagina < response.paginas) {
                        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${pagina + 1})">&raquo;</a></li>`;
                    } else {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
                    }

                    paginationHtml += '</ul></div>';

                    $('#page-selection').html(paginationHtml);
                } else {
                    $('#page-selection').html('');
                }
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al cargar los feriados'
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar tabla:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al cargar los feriados'
            });
        });
}

/**
 * Limpiar formulario
 */
function limpiar() {
    indEditar = 0;
    idEdit = 0;
    idEliminar = 0;

    $("#form_main")[0].reset();

    // Limpiar validaciones
    $("#form_main .is-invalid").removeClass('is-invalid');
    $("#form_main .invalid-feedback").remove();
}



/**
 * Guardar feriado (crear o actualizar)
 */
function guardar() {
    // Obtener datos del formulario
    let fechaInput = $("#f_fer").val().trim();

    // Convertir de YYYY-MM-DD (input type="date") a DD/MM/YYYY (backend)
    letFechaFormateada = '';
    if (fechaInput && fechaInput.includes('-')) {
        const partes = fechaInput.split('-');
        letFechaFormateada = `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    const formData = {
        feriado: $("#feriado").val().trim(),
        fecha: letFechaFormateada
    };

    // Validar campos requeridos
    if (!formData.feriado) {
        Toast.fire({
            icon: 'error',
            title: 'La descripción del feriado es obligatoria'
        });
        return;
    }

    if (!formData.fecha) {
        Toast.fire({
            icon: 'error',
            title: 'La fecha es obligatoria'
        });
        return;
    }

    let url, method;

    if (idEdit == 0) {
        // Crear nuevo feriado
        url = window.feriadosRoutes.store;
        method = 'POST';
    } else {
        // Actualizar feriado existente
        url = window.feriadosRoutes.update.replace(':id', idEdit);
        method = 'PUT';
    }

    apiLaravel(url, method, formData)
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message || (idEdit == 0 ? 'Feriado creado correctamente' : 'Feriado actualizado correctamente')
                });
                limpiar();
                refrescarTabla();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al guardar el feriado'
                });
            }
        })
        .catch(error => {
            console.error('Error al guardar:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al guardar el feriado'
            });
        });
}

/**
 * Inicialización cuando se carga el documento
 */
$(document).ready(function () {
    console.log('=== INICIANDO FERIADOS.JS ===');
    console.log('jQuery disponible:', typeof $ !== 'undefined');
    console.log('apiLaravel disponible:', typeof apiLaravel !== 'undefined');
    console.log('Swal disponible:', typeof Swal !== 'undefined');
    console.log('window.feriadosRoutes:', window.feriadosRoutes);
    console.log('window.feriadosPermisos:', window.feriadosPermisos);



    // Verificar que todas las dependencias estén disponibles
    if (typeof apiLaravel === 'undefined') {
        console.error('La función apiLaravel no está disponible');
        alert('Error: La función apiLaravel no está cargada');
        return;
    }

    if (typeof window.feriadosRoutes === 'undefined') {
        console.error('Las rutas de feriados no están definidas');
        alert('Error: Las rutas no están definidas');
        return;
    }

    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no está disponible');
        alert('Error: SweetAlert2 no está cargado');
        return;
    }

    if (typeof moment === 'undefined') {
        console.warn('Moment.js no está disponible (opcional para input nativo)');
    }

    // Inicializar SweetAlert Toast
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });

    // Configurar validación del formulario
    $("#form_main").validate({
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        rules: {
            feriado: {
                required: true,
                minlength: 3
            },
            fecha: {
                required: true
            }
        },
        messages: {
            feriado: {
                required: "La descripción del feriado es obligatoria",
                minlength: "La descripción debe tener al menos 3 caracteres"
            },
            fecha: {
                required: "La fecha es obligatoria"
            }
        },
        submitHandler: function(form) {
            guardar();
            return false;
        }
    });

    // Event listeners
    $("#btn_eliminar_modal").click(function() {
        eliminar();
    });

    $("#btn_limpiar").click(function() {
        limpiar();
    });

    $("#btn_eliminar").click(function() {
        if (idEdit == 0) {
            Toast.fire({
                icon: 'warning',
                title: 'Primero debe seleccionar un feriado para eliminar'
            });
            return;
        }
        modalEliminar();
    });

    // Eventos para gestión de feriados fijos
    $("#btn_gestionar_fijos").click(function() {
        cargarFeriadosFijos();
         $("#modal_feriados_fijos").modal('show');
    });

    // Evento para vista de calendario
    $("#btn_vista_calendario").click(function() {
        abrirCalendario();
    });

    // Eventos del modal calendario
    $("#calendario_mes, #calendario_anio").change(function() {
        cargarCalendario();
    });

    // Ambos botones usan la misma función unificada
    $("#btn_generar_fijos, #btn_generar_anio").click(function() {
        $("#modal_generar_anio").modal('show');
    });

    $("#btn_ejecutar_generacion").click(function() {
        const anio = $("#anio_generar").val();
        generarFeriadosUnificado(anio);
    });

    $("#form_feriado_fijo").submit(function(e) {
        e.preventDefault();
        guardarFeriadoFijo();
    });

    // Cambio en el selector de cantidad por página
    $("#page-selection_input_num_page").change(function() {
        refrescarTabla();
    });

    // Cargar tabla inicial
    cargarTabla();
});

// === FUNCIONES PARA GESTIÓN DE FERIADOS FIJOS ===

function cargarFeriadosFijos() {
    console.log('=== CARGANDO FERIADOS FIJOS ===');
    console.log('Ruta configurada:', window.feriadosRoutes?.feriadosFijos);

    // Verificar que la ruta esté disponible
    if (!window.feriadosRoutes || !window.feriadosRoutes.feriadosFijos) {
        console.error('Rutas no disponibles:', window.feriadosRoutes);
        Toast.fire({
            icon: 'error',
            title: 'Error: Rutas no configuradas correctamente'
        });
        return;
    }

    // Mostrar loading en la tabla
    $("#tbody_feriados_fijos").html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
console.log("url",window.feriadosRoutes.feriadosFijos)
    apiLaravel(window.feriadosRoutes.feriadosFijos, 'GET')
        .then(response => {
            console.log('Respuesta recibida:', response);
            if (response && response.success) {
                console.log('Datos de feriados fijos:', response.data);
                mostrarFeriadosFijos(response.data);
            } else {
                console.error('Error en respuesta:', response?.message || 'Respuesta inválida');
                $("#tbody_feriados_fijos").html('<tr><td colspan="4" class="text-center text-danger">Error al cargar datos</td></tr>');
                Toast.fire({
                    icon: 'error',
                    title: response?.message || 'Error al cargar feriados fijos'
                });
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            $("#tbody_feriados_fijos").html('<tr><td colspan="4" class="text-center text-danger">Error de conexión</td></tr>');

            // Verificar si es error de autenticación
            if (error.status === 401 || error.status === 403) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Sesión expirada. Recarga la página e inicia sesión.'
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de conexión: ' + (error.message || error.status || 'Desconocido')
                });
            }
        });
}





/**
 * Mostrar feriados fijos en la tabla
 */
function mostrarFeriadosFijos(feriados) {
    console.log('Mostrando feriados fijos:', feriados);
    let html = '';

    if (!feriados || feriados.length === 0) {
        html = '<tr><td colspan="4" class="text-center">No hay feriados fijos configurados</td></tr>';
    } else {
        feriados.forEach(feriado => {
            const estadoBadge = feriado.activo ?
                '<span class="badge badge-success">Activo</span>' :
                '<span class="badge badge-secondary">Inactivo</span>';

            const btnToggle = feriado.activo ?
                `<button class="btn btn-sm btn-warning" onclick="toggleFeriadoFijo(${feriado.id}, false)" title="Desactivar">
                    <i class="fas fa-pause"></i>
                </button>` :
                `<button class="btn btn-sm btn-success" onclick="toggleFeriadoFijo(${feriado.id}, true)" title="Activar">
                    <i class="fas fa-play"></i>
                </button>`;

            // Construir fecha correctamente
            let fecha = 'N/A';
            if (feriado.fecha_formateada) {
                fecha = feriado.fecha_formateada;
            } else if (feriado.dia_mes) {
                // dia_mes viene como MM-DD, convertir a DD/MM
                const partes = feriado.dia_mes.split('-');
                if (partes.length === 2) {
                    fecha = `${partes[1]}/${partes[0]}`; // DD/MM
                }
            } else if (feriado.dia && feriado.mes) {
                fecha = `${feriado.dia.toString().padStart(2, '0')}/${feriado.mes.toString().padStart(2, '0')}`;
            }

            html += `
                <tr>
                    <td>${fecha}</td>
                    <td>${feriado.nombre || 'Sin nombre'}</td>
                    <td>${estadoBadge}</td>
                    <td>
                        ${btnToggle}
                        <button class="btn btn-sm btn-danger ml-1" onclick="eliminarFeriadoFijo(${feriado.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    $("#tbody_feriados_fijos").html(html);
}

/**
 * Guardar nuevo feriado fijo
 */
function guardarFeriadoFijo() {
    const datos = {
        nombre: $("#nombre_fijo").val(),
        dia: $("#dia_fijo").val(),
        mes: $("#mes_fijo").val(),
        descripcion: $("#descripcion_fijo").val()
    };

    if (!datos.nombre || !datos.dia || !datos.mes) {
        Toast.fire({
            icon: 'error',
            title: 'Todos los campos son obligatorios'
        });
        return;
    }

    apiLaravel(window.feriadosRoutes.storeFijo, 'POST', datos)
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });

                // Limpiar formulario
                $("#form_feriado_fijo")[0].reset();

                // Recargar lista
                cargarFeriadosFijos();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al guardar feriado fijo'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error de conexión'
            });
        });
}

/**
 * Alternar estado de feriado fijo (activo/inactivo)
 */
function toggleFeriadoFijo(id, nuevoEstado) {
    const url = window.feriadosRoutes.toggleFijo.replace(':id', id);

    apiLaravel(url, 'PATCH')
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                cargarFeriadosFijos();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al actualizar estado'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error de conexión'
            });
        });
}

/**
 * Eliminar feriado fijo
 */
function eliminarFeriadoFijo(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: '¿Desea eliminar este feriado fijo? Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = window.feriadosRoutes.destroyFijo.replace(':id', id);

            apiLaravel(url, 'DELETE')
                .then(response => {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        cargarFeriadosFijos();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message || 'Error al eliminar feriado fijo'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Toast.fire({
                        icon: 'error',
                        title: 'Error de conexión'
                    });
                });
        }
    });
}

/**
 * Función unificada para generar feriados (reemplaza las dos funciones anteriores)
 */
function generarFeriadosUnificado(anio) {
    if (!anio) {
        Toast.fire({
            icon: 'error',
            title: 'Debe seleccionar un año'
        });
        return;
    }

    $("#modal_generar_anio").modal('hide');

    // Mostrar loading
    Swal.fire({
        title: 'Generando feriados...',
        text: `Procesando feriados fijos para el año ${anio}`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    apiLaravel(window.feriadosRoutes.generarFijos, 'POST', { anio: anio })
        .then(response => {
            if (response.success) {
                Swal.fire({
                    title: 'Generación Completada',
                    text: `Se generaron ${response.creados} feriados fijos para el año ${anio}`,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });

                // Refrescar tabla principal
                refrescarTabla();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Error al generar feriados',
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor',
                icon: 'error'
            });
        });
}

/**
 * Funciones para Vista de Calendario
 */
function abrirCalendario() {
    // Inicializar selectores de mes y año
    const mesActual = new Date().getMonth() + 1;
    const anioActual = new Date().getFullYear();

    // Llenar selector de años (5 años atrás, 5 adelante)
    const $anioSelect = $('#calendario_anio');
    $anioSelect.empty();
    for (let i = anioActual - 5; i <= anioActual + 5; i++) {
        $anioSelect.append(`<option value="${i}"${i === anioActual ? ' selected' : ''}>${i}</option>`);
    }

    // Seleccionar mes actual
    $('#calendario_mes').val(mesActual);

    // Mostrar modal y cargar calendario
    $('#modal_calendario').modal('show');
    cargarCalendario();
}

function cargarCalendario() {
    const mes = parseInt($('#calendario_mes').val());
    const anio = parseInt($('#calendario_anio').val());

    // Mostrar loading
    $('#calendario_container').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Cargando calendario...</p></div>');
    $('#feriados_items').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando feriados...</div>');

    // Obtener feriados del mes
    apiLaravel(window.feriadosRoutes.mes, 'GET', { month: mes, year: anio })
        .then(response => {
            if (response.success) {
                generarCalendario(mes, anio, response.data);
                mostrarListaFeriados(response.data);
            } else {
                $('#calendario_container').html('<div class="alert alert-danger">Error al cargar el calendario</div>');
                $('#feriados_items').html('<div class="alert alert-danger">Error al cargar feriados</div>');
            }
        })
        .catch(error => {
            console.error('Error al cargar calendario:', error);
            $('#calendario_container').html('<div class="alert alert-danger">Error de conexión</div>');
            $('#feriados_items').html('<div class="alert alert-danger">Error de conexión</div>');
        });
}

function generarCalendario(mes, anio, feriados) {
    const nombresMeses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    const nombresDias = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    // Crear diccionario de feriados por día
    const feriadosPorDia = {};
    feriados.forEach(feriado => {
        // Verificar que la fecha esté presente
        if (!feriado.FechaFer) {
            console.warn('Feriado sin fecha:', feriado);
            return;
        }

        // Crear la fecha desde formato YYYY-MM-DD
        const fecha = new Date(feriado.FechaFer + 'T00:00:00');

        // Verificar que la fecha sea válida
        if (isNaN(fecha.getTime())) {
            console.warn('Fecha inválida para feriado:', feriado.FechaFer);
            return;
        }

        const dia = fecha.getDate();
        if (!feriadosPorDia[dia]) {
            feriadosPorDia[dia] = [];
        }
        feriadosPorDia[dia].push(feriado);
    });

    // Obtener primer día del mes y cantidad de días
    const primerDia = new Date(anio, mes - 1, 1);
    const ultimoDia = new Date(anio, mes, 0).getDate();
    const primerDiaSemana = primerDia.getDay();
    const ajustePrimerDia = primerDiaSemana === 0 ? 6 : primerDia.getDay() - 1;

    let html = `
        <div class="card">
            <div class="card-header text-center">
                <h5 class="card-title mb-0">
                    ${nombresMeses[mes - 1]} ${anio}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="calendario-grid">
    `;

    // Encabezados de días
    nombresDias.forEach((dia, index) => {
        const esFinSemana = index >= 5; // Sábado y Domingo
        const claseHeader = esFinSemana ? 'bg-light' : 'bg-secondary';
        html += `<div class="calendario-dia-header ${claseHeader} ${esFinSemana ? 'text-muted' : 'text-white'}">${dia}</div>`;
    });

    // Espacios vacíos antes del primer día
    for (let i = 0; i < ajustePrimerDia; i++) {
        html += `<div class="calendario-dia calendario-dia-vacio bg-light"></div>`;
    }

    // Días del mes
    for (let dia = 1; dia <= ultimoDia; dia++) {
        const esFeriado = feriadosPorDia[dia];
        const hoy = new Date();
        const esHoy = (anio === hoy.getFullYear() && mes === (hoy.getMonth() + 1) && dia === hoy.getDate());

        // Determinar si es fin de semana
        const fechaActual = new Date(anio, mes - 1, dia);
        const diaSemana = fechaActual.getDay();
        const esFinSemana = diaSemana === 0 || diaSemana === 6;

        let clases = 'calendario-dia';
        let bgColor = '';
        let textColor = '';
        let borderClass = '';

        // Aplicar estilos según el tipo de día
        if (esFeriado) {
            if (esFeriado.some(f => f.EsFijo)) {
                bgColor = 'bg-light border-left border-danger border-3';
                textColor = 'text-dark';
            } else {
                bgColor = 'bg-light border-left border-warning border-3';
                textColor = 'text-dark';
            }
        } else if (esFinSemana) {
            bgColor = 'bg-light';
            textColor = 'text-muted';
        } else {
            bgColor = 'bg-white';
            textColor = 'text-dark';
        }

        if (esHoy) {
            borderClass = 'border border-primary';
        }

        let contenido = `
            <div class="d-flex justify-content-between align-items-start h-100">
                <span class="numero font-weight-bold">${dia}</span>
        `;

        if (esFeriado) {
            contenido += `</div>`;
            contenido += `<div class="feriado-info mt-1">`;
            esFeriado.forEach((f, index) => {
                const tipoFeriado = f.EsFijo ? 'Nacional' : 'Especial';
                contenido += `
                    <small class="feriado-nombre d-block text-truncate text-muted"
                           title="${f.Feriado} (${tipoFeriado})"
                           style="font-size: 9px; line-height: 1.1;">
                        ${f.Feriado}
                    </small>
                `;
            });
            contenido += `</div>`;
        } else {
            contenido += `</div>`;
        }

        html += `<div class="${clases} ${bgColor} ${textColor} ${borderClass}" style="min-height: 80px; padding: 6px; border: 1px solid #dee2e6;">${contenido}</div>`;
    }

    html += `
                </div>
            </div>
            <div class="card-footer bg-light text-center">
                <div class="row">
                    <div class="col-md-4">
                        <div class="border-left border-danger d-inline-block mr-1" style="width: 3px; height: 15px;"></div>
                        <small class="text-muted">Feriados Nacionales</small>
                    </div>
                    <div class="col-md-4">
                        <div class="border-left border-warning d-inline-block mr-1" style="width: 3px; height: 15px;"></div>
                        <small class="text-muted">Fechas Especiales</small>
                    </div>
                    <div class="col-md-4">
                        <div class="border border-primary d-inline-block mr-1" style="width: 15px; height: 15px;"></div>
                        <small class="text-muted">Día Actual</small>
                    </div>
                </div>
            </div>
        </div>
        <style>
        .calendario-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: #dee2e6;
        }
        .calendario-dia-header {
            padding: 10px 6px;
            text-align: center;
            font-weight: 600;
            font-size: 12px;
        }
        .calendario-dia {
            transition: box-shadow 0.2s ease;
        }
        .calendario-dia:hover {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .numero {
            font-size: 14px;
        }
        .feriado-nombre {
            max-width: 100%;
        }
        @media (max-width: 768px) {
            .calendario-dia {
                min-height: 60px !important;
                padding: 4px !important;
            }
            .numero {
                font-size: 12px;
            }
            .feriado-nombre {
                font-size: 8px !important;
            }
            .card-footer .col-md-4 {
                margin-bottom: 5px;
            }
        }
        </style>
    `;

    $('#calendario_container').html(html);
}

function mostrarListaFeriados(feriados) {
    if (feriados.length === 0) {
        $('#feriados_items').html(`
            <div class="alert alert-info alert-dismissible">
                <h5><i class="icon fas fa-info"></i> Sin Feriados</h5>
                No hay feriados registrados en este mes.
            </div>
        `);
        return;
    }

    let html = `
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    Feriados del Mes (${feriados.length})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
    `;

    feriados.forEach((feriado, index) => {
        const tipo = feriado.EsFijo ? 'Nacional' : 'Especial';
        const fecha = new Date(feriado.FechaFer + 'T00:00:00');
        const nombreDia = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][fecha.getDay()];

        // Verificar que la fecha sea válida
        if (isNaN(fecha.getTime())) {
            console.warn('Fecha inválida para feriado:', feriado.FechaFer);
            return;
        }

        html += `
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="mb-1">
                            <h6 class="mb-0">${feriado.Feriado}</h6>
                        </div>
                        <div class="text-muted">
                            <small class="mr-3">${feriado.FF}</small>
                            <small>${nombreDia}</small>
                        </div>
                    </div>
                    <div class="text-right">
                        <small class="text-muted">${tipo}</small>
                    </div>
                </div>
            </div>
        `;
    });

    html += `
                </div>
            </div>
            <div class="card-footer text-center">
                <small class="text-muted">
                    ${feriados.filter(f => f.EsFijo).length} Nacionales •
                    ${feriados.filter(f => !f.EsFijo).length} Especiales
                </small>
            </div>
        </div>
    `;

    $('#feriados_items').html(html);
}

/**
 * Ir al mes y año actual
 */
function irAHoy() {
    const hoy = new Date();
    const mesActual = hoy.getMonth() + 1;
    const anioActual = hoy.getFullYear();

    $('#calendario_mes').val(mesActual);
    $('#calendario_anio').val(anioActual);

    // Mostrar animación de carga
    $('#calendario_container').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-success" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2 text-success">Navegando al mes actual...</p>
        </div>
    `);

    setTimeout(() => {
        cargarCalendario();
    }, 500);
}

/**
 * Funciones auxiliares para cerrar modales
 */
function cerrarModal(modalId) {
    $('#' + modalId).modal('hide');
}

// Agregar funciones de compatibilidad para Bootstrap 5
$(document).ready(function() {
    // Forzar cierre de modales para ambas versiones de Bootstrap
    $('[data-dismiss="modal"], [data-bs-dismiss="modal"]').on('click', function() {
        const modal = $(this).closest('.modal');
        if (modal.length) {
            modal.modal('hide');
        }
    });

    // También manejar el cierre con la tecla Escape
    $(document).on('keyup', function(e) {
        if (e.key === "Escape") {
            $('.modal.show').modal('hide');
        }
    });

    // Función para cerrar cualquier modal abierto
    window.cerrarTodosLosModales = function() {
        $('.modal').modal('hide');
    };
});
