// ======================= VARIABLES GLOBALES =======================

let dataProgramacion = [];
let currentMonth = new Date().getMonth() + 1;
let currentYear = new Date().getFullYear();

// Variables para paginación
let paginaActual = 1;
let totalPaginas = 1;
let totalEmpleados = 0;
let porPagina = 50;
let terminoBusqueda = '';

// Variables para modales
let idEmpleadoModal = 0;
let nombrePersonalModal = '';
let indEmpleadoModal = 0;
let idEmpXDia = 0;
let ind1Dia = 0;
let ind2Dia = 0;
let numHorarioXDia = 0;
let deleteHorariosXDia = [];
let numGuardias = 0;
let arrEliminarGuardias = [];

// Variables para datos
let idServicio = null;
let idJefe = 0;
let idPersonal = "";
let tipoGuardias = 0;
let puedeGuardias = false;
let avisoHorasIrregularidades = "";

// Toast para mensajes
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

// ======================= INICIALIZACIÓN =======================

$(document).ready(function() {
    // Establecer fecha por defecto en formato mm/yyyy
    const today = new Date();
    const mes = (today.getMonth() + 1).toString().padStart(2, '0');
    const año = today.getFullYear();
    $('#d_fil').val(`${mes}/${año}`);

    // Inicializar componentes básicos
    inicializarDateTimePickers();
    inicializarEventosTab();
    inicializarEventosFormulario();

    // Cargar datos iniciales y luego inicializar Select2
    inicializarSelect2();
    // Cargar programación inicial (sin filtro de servicio al inicio)
    getProgramacion();

    // Event listeners principales
    $('#servicios_fil').on('change', function() {
        idServicio = $(this).val() || null;
        getProgramacion();
    });

    $('#d_fil').on('change', function() {
        const fechaValue = $(this).val(); // formato mm/yyyy
        const [mes, año] = fechaValue.split('/');
        currentMonth = parseInt(mes);
        currentYear = parseInt(año);
        getProgramacion();
    });

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Inicializar eventos de hora en inputs existentes
    $('.input_horario').each(function() {
        inicializarEventosHora(this);
    });
});

/**
 * Verificar si una fecha es feriado
 */
function verificarSiEsFeriado(fecha) {
    // Lista de feriados fijos (formato MM-DD)
    const feriadosFijos = [
        '01-01', // Año Nuevo
        '05-01', // Día del Trabajador
        '05-25', // Revolución de Mayo
        '06-20', // Día de la Bandera
        '07-09', // Día de la Independencia
        '08-17', // Muerte de San Martín
        '10-12', // Día de la Raza
        '11-20', // Día de la Soberanía Nacional
        '12-08', // Inmaculada Concepción
        '12-25'  // Navidad
    ];

    // Lista de feriados variables que se pueden agregar dinámicamente
    // Estos podrían venir del servidor en una implementación más completa
    const feriadosVariables = [
        // Ejemplo: '2025-03-24', // Lunes de Carnaval
        // '2025-03-25', // Martes de Carnaval
        // '2025-04-02', // Día del Veterano de Guerra
        // Aquí se pueden agregar más feriados variables
    ];

    const mesDay = fecha.toISOString().slice(5, 10); // MM-DD
    const fullDate = fecha.toISOString().slice(0, 10); // YYYY-MM-DD

    return feriadosFijos.includes(mesDay) || feriadosVariables.includes(fullDate);
}

// ======================= FUNCIONES PRINCIPALES =======================

/**
 * Obtener programación
 */
function getProgramacion(resetPagina = true) {
    // Resetear página si es nueva búsqueda
    if (resetPagina) {
        paginaActual = 1;
    }

    // Obtener valores de filtros
    const servicioId = $('#servicios_fil').val();
    const departamentoId = $('#departamento_fil').val();
    const gerenciaId = $('#gerencia_fil').val();
    const clasificacionId = $('#clasificacion_fil').val();
    
    const datos = {
        servicio_id: servicioId || null,
        departamento_id: departamentoId || null,
        gerencia_id: gerenciaId || null,
        clasificacion_id: clasificacionId || null,
        jefe_id: 0,
        mes: $('#d_fil').val(),
        pagina: paginaActual,
        por_pagina: porPagina,
        busqueda: terminoBusqueda
    };

    // Debug temporal
    console.log('Datos enviados:', datos);

    mostrarLoader();

    apiLaravel('/api/programacion-personal/obtener', 'POST', datos)
        .then(response => {
            ocultarLoader();

            // Debug temporal
            console.log('Respuesta recibida:', response);

            if (response.success) {
                dataProgramacion = response.data.empleados || [];
                console.log('Data programacion:', dataProgramacion);

                // Actualizar información de paginación
                if (response.paginacion) {
                    paginaActual = response.paginacion.pagina_actual;
                    totalPaginas = response.paginacion.total_paginas;
                    totalEmpleados = response.paginacion.total_empleados;
                    renderizarPaginacion();
                }

                // Mostrar/ocultar controles de búsqueda y paginación
                if (totalEmpleados > 0) {
                    $('#contenedor_busqueda_paginacion').show();
                } else {
                    $('#contenedor_busqueda_paginacion').hide();
                }

                if (dataProgramacion.length === 0 && response.message) {
                    Toast.fire({
                        icon: 'info',
                        title: response.message
                    });
                }

                generarTablaProgramacion();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al obtener programación'
                });
            }
        })
        .catch(error => {
            ocultarLoader();
            console.error('Error:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error en la conexión'
            });
        });
}

/**
 * Renderizar controles de paginación
 */
function renderizarPaginacion() {
    const $controles = $('#controles_paginacion');
    const $info = $('#info_paginacion');
    
    // Actualizar info
    $info.text(`Mostrando ${dataProgramacion.length} de ${totalEmpleados} empleados`);
    
    // Si solo hay una página, no mostrar controles
    if (totalPaginas <= 1) {
        $controles.html('');
        return;
    }
    
    let html = '';
    
    // Botón anterior
    html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${paginaActual - 1})">Anterior</a>
    </li>`;
    
    // Números de página (mostrar máximo 5 páginas alrededor de la actual)
    const inicio = Math.max(1, paginaActual - 2);
    const fin = Math.min(totalPaginas, paginaActual + 2);
    
    if (inicio > 1) {
        html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(1)">1</a></li>`;
        if (inicio > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    for (let i = inicio; i <= fin; i++) {
        html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${i})">${i}</a>
        </li>`;
    }
    
    if (fin < totalPaginas) {
        if (fin < totalPaginas - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${totalPaginas})">${totalPaginas}</a></li>`;
    }
    
    // Botón siguiente
    html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${paginaActual + 1})">Siguiente</a>
    </li>`;
    
    $controles.html(html);
}

/**
 * Cambiar de página
 */
function cambiarPagina(nuevaPagina) {
    if (nuevaPagina < 1 || nuevaPagina > totalPaginas) return;
    paginaActual = nuevaPagina;
    getProgramacion(false); // No resetear página
}

/**
 * Buscar empleado
 */
function buscarEmpleado() {
    terminoBusqueda = $('#busqueda_empleado').val().trim();
    paginaActual = 1; // Resetear a primera página
    getProgramacion(false);
}

/**
 * Limpiar búsqueda
 */
function limpiarBusqueda() {
    $('#busqueda_empleado').val('');
    terminoBusqueda = '';
    paginaActual = 1;
    getProgramacion(false);
}

/**
 * Generar tabla de programación
 */
function generarTablaProgramacion() {
    const tbody = $('#table_horarios');
    tbody.empty();

    if (!dataProgramacion || dataProgramacion.length === 0) {
        tbody.append('<tr><td colspan="100%" class="text-center">No hay datos para mostrar</td></tr>');
        return;
    }

    // Generar headers de días
    generarHeadersDias();

    // Generar filas de empleados
    dataProgramacion.forEach((empleado, index) => {
        const fila = generarFilaEmpleado(empleado, index);
        tbody.append(fila);
    });

    // Calcular totales
    actualizarTotales();
}

/**
 * Generar headers de días del mes
 */
function generarHeadersDias() {
    // Parsear el formato mm/yyyy del input
    const fechaValue = $('#d_fil').val(); // formato mm/yyyy
    const [mes, año] = fechaValue.split('/');
    const ultimoDia = new Date(año, mes, 0).getDate(); // último día del mes

    const thead = $('#head_table');

    // Limpiar headers existentes de días (mantener solo la primera columna de Personal)
    thead.find('th').not(':first').remove();

    // Agregar días del mes
    for (let dia = 1; dia <= ultimoDia; dia++) {
        const fechaDia = new Date(año, mes - 1, dia); // mes - 1 porque Date usa índices de 0-11
        const nombreDia = fechaDia.toLocaleDateString('es-ES', { weekday: 'short' });
        const diaSemana = fechaDia.getDay(); // 0=domingo, 6=sábado
        const esFinesSemana = diaSemana === 0 || diaSemana === 6;
        const esFeriado = verificarSiEsFeriado(fechaDia);

        // Aplicar color de fondo si es fin de semana o feriado
        const estiloFondo = (esFinesSemana || esFeriado) ? 'background: #f9e3ff;' : '';

        thead.append(`<th style="min-width: 150px; text-align: center; color: black; ${estiloFondo}">${nombreDia.toUpperCase()} ${dia.toString().padStart(2, '0')}/${mes.toString().padStart(2, '0')}</th>`);
    }

    // Agregar totales
    thead.append('<th class="total-header" style="color: black;">Horas<br>Ctrto</th>');
    thead.append('<th class="total-header" style="color: black;">Guardias<br>Ctrto</th>');
    thead.append('<th class="total-header" style="color: black;">Guardias<br>Pagas</th>');
    thead.append('<th style="color: black;">Total</th>');
}

/**
 * Generar fila de empleado
 */
function generarFilaEmpleado(item, index) {
    let fila = `<tr id="fila_${index}">`;
    fila += `<td class="nombre_personal fixed-header" style="background-color: #fff; color: black;">
                <a onclick="cargarHorario(${item.empleado.idEmpleado}, this, ${index}, '${item.empleado.nombre}')" style="color: black; text-decoration: none;">
                    ${item.empleado.nombre}
                </a>
             </td>`;

    // Parsear el formato mm/yyyy del input
    const fechaValue = $('#d_fil').val(); // formato mm/yyyy
    const [mesStr, añoStr] = fechaValue.split('/');
    const año = parseInt(añoStr);
    const mes = parseInt(mesStr) - 1; // -1 porque Date usa índices de 0-11
    const ultimoDia = new Date(año, mes + 1, 0).getDate();

    let totalHoras = 0;
    let totalGuardiasContrato = 0;
    let totalGuardiasPagas = 0;

    // Generar celdas de días
    for (let dia = 1; dia <= ultimoDia; dia++) {
        const fechaDia = `${año}-${(mes + 1).toString().padStart(2, '0')}-${dia.toString().padStart(2, '0')}`;
        const programacionDia = item.programacion ? item.programacion[fechaDia] : null;

        const celda = generarCeldaDia(programacionDia, item.empleado.idEmpleado, index, dia - 1);
        fila += celda.html;

        totalHoras += celda.horas;
        totalGuardiasContrato += celda.guardiasContrato;
        totalGuardiasPagas += celda.guardiasPagas;
    }

    // Celdas de totales
    fila += `<td id="total_hr_ctrto_${index}" class="text-center" style="background-color: #fff; color: black;">${formatearHoras(totalHoras)}</td>`;
    fila += `<td id="total_hr_guardias_ctrato_${index}" class="text-center" style="background-color: #fff; color: black;">${formatearHoras(totalGuardiasContrato)}</td>`;
    fila += `<td id="total_hr_guardias_pagas_${index}" class="text-center" style="background-color: #fff; color: black;">${formatearHoras(totalGuardiasPagas)}</td>`;
    fila += `<td id="total_hr_total_${index}" class="text-center" style="color: black;">${formatearHoras(totalHoras + totalGuardiasContrato + totalGuardiasPagas)}</td>`;

    fila += '</tr>';

    return fila;
}

/**
 * Generar celda de día
 */
function generarCeldaDia(programacionDia, idEmpleado, filaIndex, diaIndex) {
    let html = '';
    let horas = 0;
    let guardiasContrato = 0;
    let guardiasPagas = 0;

    let estiloCelda = 'text-align: center; color: black; background-color: white;';

    // Calcular la fecha del día para detectar fines de semana y feriados
    const fechaValue = $('#d_fil').val(); // formato mm/yyyy
    const [mesStr, añoStr] = fechaValue.split('/');
    const año = parseInt(añoStr);
    const mes = parseInt(mesStr) - 1; // -1 porque Date usa índices de 0-11
    const fechaDia = new Date(año, mes, diaIndex + 1);
    const diaSemana = fechaDia.getDay(); // 0=domingo, 6=sábado

    // Verificar si es fin de semana (sábado o domingo) o feriado
    const esFinesSemana = diaSemana === 0 || diaSemana === 6;
    const esFeriado = verificarSiEsFeriado(fechaDia);

    if (programacionDia && programacionDia.licencia) {
        // Licencias -> azul claro
        estiloCelda += 'background: #b4ffff;';
    } else if ((programacionDia && programacionDia.tipo == 1) || esFinesSemana || esFeriado) {
        // Tipo 1, fines de semana o feriados -> rosa claro
        estiloCelda += 'background: #f9e3ff;';
    }

    html += `<td id="td_horario_${filaIndex}_${diaIndex}" ondblclick="verHorarioDia(${idEmpleado}, '${filaIndex},${diaIndex}', this)"
             style="${estiloCelda}" class="td_horario">
`;

    html += `<div style="width: 150px;">
`;
    html += `<div id="cont_horario_horario_${filaIndex}_${diaIndex}" style="display:inline-block;">
`;

    if (programacionDia && !programacionDia.licencia && programacionDia.horario) {
        const horarios = programacionDia.horario[0] ? programacionDia.horario[0].split(',') : [];
        const ids = programacionDia.horario[1] ? programacionDia.horario[1].split(',') : [];
        const tipos = programacionDia.horario[2] ? programacionDia.horario[2].split(',') : [];

        for (let k = 0; k < horarios.length; k++) {
            if (horarios[k] && ids[k]) {
                const tipoHorario = tipos[k] || '0';
                const colorFondo = tipoHorario === '0' ? 'darkgreen' : tipoHorario === '1' ? 'darkviolet' : '';

                html += `<div style="background-color: ${colorFondo}" class="cont_horario" id="horario_${ids[k]}">
                         ${horarios[k]}
                         <button type="button" class="btn btn-danger btn-xs" onclick="eliminarHorario(${ids[k]}, ${filaIndex}, ${diaIndex}, ${tipoHorario})">
                            <i class="fas fa-times"></i>
                         </button>
                      </div>`;

                // Calcular horas
                const horasCalculadas = calcularHorasPorHorario(horarios[k]);
                if (tipoHorario === '0') {
                    horas += horasCalculadas;
                } else if (tipoHorario === '1') {
                    guardiasPagas += horasCalculadas;
                } else {
                    guardiasContrato += horasCalculadas;
                }
            }
        }
    }

    if (programacionDia && programacionDia.licencia) {
        html += '<span style="color: black; font-weight: bold;">LIC</span>';
    }

    html += '</div>';

    // Checkbox de franco
    if (!programacionDia || !programacionDia.licencia) {
        const tienefranco = (programacionDia && programacionDia.tipo == 2) ? 'checked' : '';
        html += `<div style="margin-bottom: 10px;margin-top: 10px;display:inline-block;width: 30px;">
                    <label class="custom-checkbox">
                        <input id="check_franco_${filaIndex}_${diaIndex}" onclick="clickFranco(${idEmpleado}, '${filaIndex},${diaIndex}', this)" type="checkbox" ${tienefranco}>
                        <span class="checkmark"></span>
                    </label>
                 </div>`;
    }

    html += '</div></td>';

    return {
        html: html,
        horas: horas,
        guardiasContrato: guardiasContrato,
        guardiasPagas: guardiasPagas
    };
}

// ======================= FUNCIONES DE MODALES =======================

/**
 * Cargar horario (abrir modal)
 */
function cargarHorario(idEmp, elemento, indice, personal) {
    idEmpleadoModal = idEmp;
    nombrePersonalModal = personal;
    indEmpleadoModal = indice;

    $('#nombre_persona_cargar').text(personal);

    // Limpiar formularios
    limpiarFormulariosProgramacion();

    // Cargar guardias existentes
    cargarGuardiasEmpleado(idEmp);

    // Mostrar modal
    $('#modal_cargar_horario').modal('show');
}

/**
 * Ver horario de día específico
 */
function verHorarioDia(idEmp, indices, elemento) {
    const [fila, columna] = indices.split(',');
    idEmpXDia = idEmp;
    ind1Dia = parseInt(fila);
    ind2Dia = parseInt(columna);

    const item = dataProgramacion[ind1Dia];
    const empleado = item.empleado;
    const [mesStr, anioStr] = $('#d_fil').val().split('/');
    // Construir fecha segura (primer día del mes seleccionado) y avanzar al día seleccionado
    const fecha = new Date(`${anioStr}-${mesStr}-01T00:00:00`);
    fecha.setDate(parseInt(columna) + 1);

    $('#nombre_persona_horario_x_dia').text(`${empleado.nombre} - Día ${fecha.getDate()}`);
    $('#empleado_horario_dia').text(empleado.nombre);
    $('#fecha_horario_dia').text(formatDate(fecha));

    // Cargar horarios existentes del día
    cargarHorariosDia(item, fecha);

    $('#modal_programacion_dia').modal('show');
}

/**
 * Cargar horarios del día en el modal
 */
function cargarHorariosDia(item, fecha) {
    const fechaStr = formatDateISO(fecha);
    const programacionDia = item.programacion ? item.programacion[fechaStr] : null;

    $('#table_hoararios_por_dia').empty();
    numHorarioXDia = 0;
    deleteHorariosXDia = [];

    if (programacionDia && programacionDia.horario) {
        const horarios = programacionDia.horario[0] ? programacionDia.horario[0].split(',') : [];
        const ids = programacionDia.horario[1] ? programacionDia.horario[1].split(',') : [];
        const tipos = programacionDia.horario[2] ? programacionDia.horario[2].split(',') : [];

        for (let i = 0; i < horarios.length; i++) {
            if (horarios[i] && ids[i]) {
                const partes = horarios[i].split(' - ');
                if (partes.length === 2) {
                    agregarHorarioDiaExistente(ids[i], tipos[i], partes[0], partes[1]);
                }
            }
        }
    }

    if (numHorarioXDia === 0) {
        agregarHorarioDia();
    }
}

/**
 * Agregar horario del día
 */
function agregarHorarioDia() {
    const html = `
        <tr id="horario_dia_${numHorarioXDia}">
            <td>
                <input type="time" id="dia_entrada_x_dia_${numHorarioXDia}" class="form-control">
                <input type="hidden" id="id_horario_x_dia_${numHorarioXDia}" value="0">
            </td>
            <td>
                <input type="time" id="dia_salida_x_dia_${numHorarioXDia}" class="form-control">
            </td>
            <td>
                <select id="tipo_horario_x_dia_${numHorarioXDia}" class="form-select">
                    <option value="0">Normal</option>
                    <option value="1">Guardia</option>
                    <option value="2">Franco</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarHorarioXDia(${numHorarioXDia})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

    $('#table_hoararios_por_dia').append(html);
    numHorarioXDia++;
}

/**
 * Agregar horario del día existente
 */
function agregarHorarioDiaExistente(id, tipo, entrada, salida) {
    const html = `
        <tr id="horario_dia_${numHorarioXDia}">
            <td>
                <input type="time" id="dia_entrada_x_dia_${numHorarioXDia}" class="form-control" value="${entrada}">
                <input type="hidden" id="id_horario_x_dia_${numHorarioXDia}" value="${id}">
            </td>
            <td>
                <input type="time" id="dia_salida_x_dia_${numHorarioXDia}" class="form-control" value="${salida}">
            </td>
            <td>
                <select id="tipo_horario_x_dia_${numHorarioXDia}" class="form-select">
                    <option value="0" ${tipo == '0' ? 'selected' : ''}>Normal</option>
                    <option value="1" ${tipo == '1' ? 'selected' : ''}>Guardia</option>
                    <option value="2" ${tipo == '2' ? 'selected' : ''}>Franco</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarHorarioXDia(${numHorarioXDia})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

    $('#table_hoararios_por_dia').append(html);
    numHorarioXDia++;
}

/**
 * Eliminar horario del día
 */
function eliminarHorarioXDia(index) {
    const id = $(`#id_horario_x_dia_${index}`).val();
    if (id && id != '0') {
        deleteHorariosXDia.push(id);
    }
    $(`#horario_dia_${index}`).remove();
}

/**
 * Guardar horario del día
 */
function guardarHorarioXDia() {
    // Reconstruimos la fecha con el mes/año seleccionado y la columna del día para evitar strings inválidos
    const [mesStr, anioStr] = $('#d_fil').val().split('/');
    const fechaDia = new Date(`${anioStr}-${mesStr}-01T00:00:00`);
    fechaDia.setDate(ind2Dia + 1);

    const datos = {
        dia: formatDateISO(fechaDia),
        idEmp: idEmpXDia,
        horarios: [],
        deleteHorariosXDia: deleteHorariosXDia
    };

    let todoOk = true;

    // Recopilar horarios
    for (let i = 0; i < numHorarioXDia; i++) {
        const entrada = $(`#dia_entrada_x_dia_${i}`);
        const salida = $(`#dia_salida_x_dia_${i}`);
        const tipo = $(`#tipo_horario_x_dia_${i}`);
        const id = $(`#id_horario_x_dia_${i}`);

        if (entrada.length && salida.length) {
            if (!entrada.val() || !salida.val()) {
                entrada.addClass('is-invalid');
                salida.addClass('is-invalid');
                todoOk = false;
            } else {
                entrada.removeClass('is-invalid');
                salida.removeClass('is-invalid');

                datos.horarios.push({
                    id: id.val(),
                    entrada: entrada.val(),
                    salida: salida.val(),
                    tipo: tipo.val()
                });
            }
        }
    }

    if (!todoOk) {
        Toast.fire({
            icon: 'error',
            title: 'Complete todos los campos'
        });
        return;
    }

    apiLaravel('/api/programacion-personal/guardar-horario-dia', 'POST', datos)
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                $('#modal_programacion_dia').modal('hide');

                // Actualizar solo la fila del empleado afectado (como en el original)
                getFilaPersonalFetch(ind1Dia, idEmpXDia, nombrePersonalModal);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al guardar'
            });
        });
}

// ======================= FUNCIONES DE PROGRAMACIÓN MASIVA =======================

/**
 * Cambiar tipo de programación
 */
function cambiarTipoProgramacion() {
    const tipo = $('#tipo_prog').val();

    if (tipo == '1') { // Rotativo
        $('#cont_prog_semanal').hide();
        $('#cont_prog_rotativo').show();
        generarFormularioRotativo();
    } else {
        $('#cont_prog_semanal').show();
        $('#cont_prog_rotativo').hide();
    }
}

/**
 * Generar formulario rotativo
 */
function generarFormularioRotativo() {
    const rotIni = $('#rotativo_ini').val() || 1;
    const rotFin = $('#rotativo_fin').val() || 7;

    let html = '<div class="table-responsive"><table class="table table-bordered">';
    html += '<thead><tr><th>Día</th><th>Entrada</th><th>Salida</th></tr></thead><tbody>';

    for (let i = 0; i < rotFin; i++) {
        html += `<tr>
            <td>Día ${i + 1}</td>
            <td><input type="time" id="dia_rot_e_${i}" class="form-control horario-input"></td>
            <td><input type="time" id="dia_rot_s_${i}" class="form-control horario-input"></td>
        </tr>`;
    }

    html += '</tbody></table></div>';
    $('#cont_dias_rotativos').html(html);
}

/**
 * Guardar programación simple
 */
function guardarProgramcionSimple() {
    const datos = {
        tipo: $('#tipo_prog').val(),
        desde: $('#d_prog').val(),
        hasta: $('#h_prog').val(),
        idEmpleado: idEmpleadoModal,
        programacion: [],
        rotativoIni: $('#rotativo_ini').val() || 0,
        rotativoFin: $('#rotativo_fin').val() || 0,
        noFeriados: $('#no_feriados').is(':checked')
    };

    if (!datos.desde || !datos.hasta) {
        Toast.fire({
            icon: 'error',
            title: 'Seleccione el rango de fechas'
        });
        return;
    }

    // Recopilar programación según el tipo
    if (datos.tipo == '0' || datos.tipo == '2') { // Semanal
        for (let i = 0; i < 7; i++) {
            const turno1 = {
                entrada: $(`#dia_${i}_e`).val(),
                salida: $(`#dia_${i}_s`).val()
            };
            const turno2 = {
                entrada: $(`#dia_${i}_1_e`).val(),
                salida: $(`#dia_${i}_1_s`).val()
            };
            datos.programacion[i] = { turno1, turno2 };
        }
    } else { // Rotativo
        const cantidadDias = $('#rotativo_fin').val() || 7;
        for (let i = 0; i < cantidadDias; i++) {
            const turno1 = {
                entrada: $(`#dia_rot_e_${i}`).val(),
                salida: $(`#dia_rot_s_${i}`).val()
            };
            datos.programacion[i] = { turno1 };
        }
    }

    apiLaravel('/api/programacion-personal/guardar-simple', 'POST', datos)
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                $('#modal_cargar_horario').modal('hide');
                getProgramacion();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al guardar'
            });
        });
}

// ======================= FUNCIONES DE GUARDIAS =======================

/**
 * Cargar guardias del empleado
 */
function cargarGuardiasEmpleado(idEmpleado) {
    const fechaValue = $('#d_fil').val(); // formato mm/yyyy
    const [mes, año] = fechaValue.split('/');

    // Crear fecha del primer día del mes
    const desde = fechaValue;
    const hasta = new Date(parseInt(año), parseInt(mes), 0); // Último día del mes siguiente

    apiLaravel(`/api/programacion-personal/empleado/${idEmpleado}/guardias?desde=${desde}&hasta=${formatDateISO(hasta)}`, 'GET')
        .then(response => {
            if (response.success) {
                mostrarGuardias(response.guardias);
            }
        })
        .catch(error => {
            console.error('Error al cargar guardias:', error);
        });
}

/**
 * Mostrar guardias en el modal
 */
function mostrarGuardias(guardias) {
    $('#cont_guardia_contrato').empty();
    $('#cont_guardia_pagas').empty();
    numGuardias = 0;
    arrEliminarGuardias = [];

    if (guardias && guardias.length > 0) {
        guardias.forEach(guardia => {
            agregarGuardiaExistente(guardia);
        });
    }

    if (numGuardias === 0) {
        agregarGuardia(0); // Agregar guardia de contrato por defecto
    }
}

/**
 * Agregar nueva guardia
 */
function agregarGuardia(tipo = 0) {
    const contenedor = tipo === 0 ? '#cont_guardia_contrato' : '#cont_guardia_pagas';

    const html = `
        <div class="row mb-2" id="guardia_${numGuardias}">
            <div class="col-md-3">
                <label>Fecha:</label>
                <input type="date" id="dgrdctrto_${numGuardias}" class="form-control">
                <input type="hidden" id="id_guardia_${numGuardias}" value="0">
                <input type="hidden" id="tipo_guardia_${numGuardias}" value="${tipo}">
            </div>
            <div class="col-md-3">
                <label>Entrada:</label>
                <input type="time" id="dia_entrada_dgrdctrto_${numGuardias}" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Salida:</label>
                <input type="time" id="dia_salida_dgrdctrto_${numGuardias}" class="form-control">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarGuardia(${numGuardias})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    $(contenedor).append(html);
    numGuardias++;
}

/**
 * Agregar guardia existente
 */
function agregarGuardiaExistente(guardia) {
    const tipo = guardia.Tipo || 0; // Asumir que viene un campo Tipo en la guardia
    const contenedor = tipo === 0 ? '#cont_guardia_contrato' : '#cont_guardia_pagas';

    const html = `
        <div class="row mb-2" id="guardia_${numGuardias}">
            <div class="col-md-3">
                <label>Fecha:</label>
                <input type="date" id="dgrdctrto_${numGuardias}" class="form-control" value="${guardia.FechaGuard}">
                <input type="hidden" id="id_guardia_${numGuardias}" value="${guardia.IdGuardia}">
                <input type="hidden" id="tipo_guardia_${numGuardias}" value="${tipo}">
            </div>
            <div class="col-md-3">
                <label>Entrada:</label>
                <input type="time" id="dia_entrada_dgrdctrto_${numGuardias}" class="form-control" value="${guardia.HoraEntrada}">
            </div>
            <div class="col-md-3">
                <label>Salida:</label>
                <input type="time" id="dia_salida_dgrdctrto_${numGuardias}" class="form-control" value="${guardia.HoraSalida}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarGuardia(${numGuardias})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    $(contenedor).append(html);
    numGuardias++;
}

/**
 * Eliminar guardia
 */
function eliminarGuardia(index) {
    const id = $(`#id_guardia_${index}`).val();
    if (id && id != '0') {
        arrEliminarGuardias.push(id);
    }
    $(`#guardia_${index}`).remove();
}

/**
 * Guardar guardias
 */
function guardarGuardias(tipo) {
    const datos = {
        tipo: tipo,
        idEmp: idEmpleadoModal,
        arrGuardias: [],
        arrEliminarGuardias: arrEliminarGuardias
    };

    let todoOk = true;

    // Recopilar guardias
    for (let i = 0; i < numGuardias; i++) {
        const fecha = $(`#dgrdctrto_${i}`);
        const entrada = $(`#dia_entrada_dgrdctrto_${i}`);
        const salida = $(`#dia_salida_dgrdctrto_${i}`);
        const id = $(`#id_guardia_${i}`);

        if (fecha.length && entrada.length && salida.length) {
            if (!fecha.val() || !entrada.val() || !salida.val()) {
                fecha.addClass('is-invalid');
                entrada.addClass('is-invalid');
                salida.addClass('is-invalid');
                todoOk = false;
            } else {
                fecha.removeClass('is-invalid');
                entrada.removeClass('is-invalid');
                salida.removeClass('is-invalid');

                datos.arrGuardias.push({
                    id: id.val(),
                    dia: fecha.val(),
                    entrada: entrada.val(),
                    salida: salida.val()
                });
            }
        }
    }

    if (!todoOk) {
        Toast.fire({
            icon: 'error',
            title: 'Complete todos los campos'
        });
        return;
    }

    if (datos.arrGuardias.length === 0) {
        Toast.fire({
            icon: 'error',
            title: 'Agregue al menos una guardia'
        });
        return;
    }

    apiLaravel('/api/programacion-personal/guardias/guardar', 'POST', datos)
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                cargarGuardiasEmpleado(idEmpleadoModal);

                // Actualizar solo la fila del empleado afectado
                getFilaPersonalFetch(indEmpleadoModal, idEmpleadoModal, nombrePersonalModal);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al guardar guardias'
            });
        });
}

// ======================= FUNCIONES DE EXPORTACIÓN =======================

/**
 * Exportar a Excel general
 */
function exportarExcel() {
    mostrarLoader();

    const datos = {
        idServicio: $('#servicios_fil').val() || null,
        mes: $('#d_fil').val(),
        tipo: 'general'
    };

    // Crear formulario para descarga
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '/programacion-personal/exportar';
    form.target = '_blank';

    Object.keys(datos).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    setTimeout(() => {
        ocultarLoader();
    }, 1000);
}

/**
 * Exportar a Excel por turnos
 */
function exportarExcelTurnos() {
    mostrarLoader();

    const datos = {
        idServicio: $('#servicios_fil').val() || null,
        mes: $('#d_fil').val()
    };

    // Crear formulario para descarga
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '/api/programacion-personal/exportar-turnos';
    form.target = '_blank';

    Object.keys(datos).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    setTimeout(() => {
        ocultarLoader();
    }, 1000);
}

// ======================= FUNCIONES AUXILIARES =======================

/**
 * Eliminar horario específico
 */
function eliminarHorario(id, fila, columna, tipo) {
    if (!confirm('¿Está seguro de eliminar este horario?')) {
        return;
    }

    apiLaravel('/api/programacion-personal/programacion/eliminar', 'POST', { id: id, tipo: tipo })
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: 'Horario eliminado'
                });
                const fechaDia = calcularFechaDelDia(columna);
                const empleadoData = dataProgramacion[fila];

                if (empleadoData && empleadoData.programacion && empleadoData.programacion[fechaDia] && empleadoData.programacion[fechaDia].horario) {
                    const horariosRaw = empleadoData.programacion[fechaDia].horario;
                    const horarios = horariosRaw[0] ? horariosRaw[0].split(',') : [];
                    const ids = horariosRaw[1] ? horariosRaw[1].split(',') : [];
                    const tipos = horariosRaw[2] ? horariosRaw[2].split(',') : [];

                    const nuevosHorarios = [];
                    const nuevosIds = [];
                    const nuevosTipos = [];

                    ids.forEach((valorId, idx) => {
                        if (valorId && valorId.toString() !== id.toString()) {
                            nuevosIds.push(valorId);
                            nuevosHorarios.push(horarios[idx]);
                            nuevosTipos.push(tipos[idx]);
                        }
                    });

                    if (nuevosIds.length > 0) {
                        empleadoData.programacion[fechaDia].horario[0] = nuevosHorarios.join(',');
                        empleadoData.programacion[fechaDia].horario[1] = nuevosIds.join(',');
                        empleadoData.programacion[fechaDia].horario[2] = nuevosTipos.join(',');
                    } else {
                        delete empleadoData.programacion[fechaDia].horario;
                    }
                }

                if (empleadoData) {
                    const nuevaFila = generarFilaEmpleado(empleadoData, fila);
                    $(`#fila_${fila}`).replaceWith(nuevaFila);
                } else {
                    getProgramacion();
                }

                actualizarTotales();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al eliminar'
            });
        });
}

/**
 * Manejar click en franco (función principal)
 */
function clickFranco(idEmp, indices, el) {
    const [ind1, ind2] = indices.split(',');
    const fechaDia = calcularFechaDelDia(parseInt(ind2));
    const marcado = $(el).is(':checked');

    // Sincronizar vista local como hacía el original: al marcar franco, limpiar horarios del día
    const filaIndex = parseInt(ind1);
    const colIndex = parseInt(ind2);
    const empleadoData = dataProgramacion[filaIndex];

    if (marcado && empleadoData && empleadoData.programacion && empleadoData.programacion[fechaDia]) {
        delete empleadoData.programacion[fechaDia].horario;
    }

    apiLaravel('/api/programacion-personal/franco', 'POST', {
        id: idEmp,
        dia: fechaDia,
        ck: marcado
    })
    .then(response => {
        if (response.success) {
            // Actualizar estado local para reflejar el franco en la celda
            if (empleadoData) {
                if (!empleadoData.programacion) empleadoData.programacion = {};
                if (!empleadoData.programacion[fechaDia]) empleadoData.programacion[fechaDia] = {};

                empleadoData.programacion[fechaDia].tipo = marcado ? 2 : 0;
                if (!marcado && !empleadoData.programacion[fechaDia].horario) {
                    delete empleadoData.programacion[fechaDia];
                }

                const nuevaFila = generarFilaEmpleado(empleadoData, filaIndex);
                $(`#fila_${filaIndex}`).replaceWith(nuevaFila);
            }

            Toast.fire({
                icon: 'success',
                title: marcado ? 'Franco marcado' : 'Franco desmarcado'
            });

            actualizarTotales();
        } else {
            $(el).prop('checked', !marcado);
            Toast.fire({
                icon: 'error',
                title: 'Error al guardar franco'
            });
        }
    })
    .catch(error => {
        $(el).prop('checked', !marcado);
        console.error('Error:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error de conexión'
        });
    });
}

/**
 * Marcar franco (función auxiliar)
 */
function marcarFranco(idEmpleado, indices, marcado) {
    const [fila, columna] = indices.split(',');
    const fecha = new Date($('#d_fil').val());
    fecha.setDate(parseInt(columna) + 1);

    if (marcado) {
        // Marcar como franco
        const datos = {
            idEmpleado: idEmpleado,
            fecha: formatDateISO(fecha),
            tipo: 'franco'
        };

        // Implementar lógica de franco
        console.log('Marcar franco:', datos);
    }
}

/**
 * Limpiar formularios de programación
 */
function limpiarFormulariosProgramacion() {
    // Limpiar programación semanal
    for (let i = 0; i < 7; i++) {
        $(`#dia_${i}_e`).val('');
        $(`#dia_${i}_s`).val('');
        $(`#dia_${i}_1_e`).val('');
        $(`#dia_${i}_1_s`).val('');
    }

    // Limpiar fechas
    $('#d_prog').val('');
    $('#h_prog').val('');

    // Resetear tipo
    $('#tipo_prog').val('0');
    cambiarTipoProgramacion();
}

// ======================= FUNCIONES PARA TURNOS PREDEFINIDOS =======================

/**
 * Agregar turno mañana
 */
function turnoManana() {
    agregarTurno('07:00', '14:00');
}

/**
 * Agregar turno tarde
 */
function turnoTarde() {
    agregarTurno('14:00', '21:00');
}

/**
 * Agregar turno noche
 */
function turnoNoche() {
    agregarTurno('21:00', '07:00');
}

/**
 * Agregar turno completo
 */
function turnoCompleto() {
    agregarTurno('07:00', '15:00');
}

/**
 * Agregar turno personalizado
 */
function agregarTurno(entrada, salida) {
    agregarHorarioDia();
    const index = numHorarioXDia - 1;
    $(`#dia_entrada_x_dia_${index}`).val(entrada);
    $(`#dia_salida_x_dia_${index}`).val(salida);
}

// ======================= FUNCIONES ADICIONALES PARA FRANCOS =======================

/**
 * Verificar franco en enfermería
 */
function checkFrancoEnf() {
    // Lógica específica para enfermería
    const fecha = $('#d_fil').val();

    apiLaravel('/api/programacion-personal/validar-solapamiento', 'POST', {
        fecha: fecha,
        tipo: 'enfermeria'
    })
    .then(response => {
        if (!response.valido) {
            Toast.fire({
                icon: 'warning',
                title: 'Conflicto en programación de enfermería'
            });
        }
    })
    .catch(error => {
        console.error('Error al verificar franco:', error);
    });
}

/**
 * Guardar franco (función principal)
 */
function guardarFranco() {
    const datos = {
        idEmp: idEmpleadoModal,
        fecha: $('#fecha_franco').val(),
        tipo: 'franco'
    };

    apiLaravel('/api/programacion-personal/franco', 'POST', datos)
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: 'Franco guardado correctamente'
                });
                getProgramacion();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Error al guardar franco'
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
 * Eliminar franco
 */
function deleteFranco(id) {
    if (!confirm('¿Está seguro de eliminar este franco?')) {
        return;
    }

    apiLaravel(`/api/programacion-personal/franco/${id}`, 'DELETE')
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: 'Franco eliminado correctamente'
                });
                getProgramacion();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Error al eliminar franco'
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

// ======================= FUNCIONES PARA ACTUALIZACIÓN DE DATOS =======================

/**
 * Obtener fila de personal actualizada (igual que el original)
 */
function getFilaPersonalFetch(ind, idEmp, personal) {
    const fechaValue = $('#d_fil').val(); // formato mm/yyyy

    apiLaravel(`/api/programacion-personal/empleado/${idEmp}/programacion?mes=${fechaValue}`, 'GET')
        .then(response => {
            if (response.success) {
                // Tomar estructura correcta del controlador (nested en data)
                const prog = response.data && response.data.programacion ? response.data.programacion : response.programacion;
                if (!prog) {
                    console.warn('Estructura inesperada en respuesta de programación:', response);
                    return;
                }
                // Actualizar solo la data de este empleado
                dataProgramacion[ind].programacion = prog;

                // Regenerar la fila usando el mismo generador que el render inicial para mantener consistencia
                const empleadoData = { empleado: { idEmpleado: idEmp, nombre: personal }, programacion: prog };
                const nuevaFila = generarFilaEmpleado(empleadoData, ind);
                $(`#fila_${ind}`).replaceWith(nuevaFila);

                // Actualizar totales generales
                actualizarTotales();

                // Reinicializar eventos
                inicializarEventosFilas();
            }
        })
        .catch(error => {
            console.error('Error al actualizar fila:', error);
        });
}

/**
 * Encontrar empleado por índice de fila
 */
function encontrarEmpleadoPorFila(filaIndex) {
    if (dataProgramacion && dataProgramacion[filaIndex]) {
        const empleado = dataProgramacion[filaIndex].empleado;
        return {
            id: empleado.idEmpleado,
            nombre: empleado.nombre
        };
    }
    return null;
}

/**
 * Sumar totales de celdas
 */
function sumarTotalesCeldas(clase) {
    let total = 0;
    $(`.${clase}`).each(function() {
        const valor = $(this).text();
        const horas = parseFloat(valor.replace(':', '.')) || 0;
        total += horas;
    });
    return formatearHoras(total);
}

/**
 * Inicializar eventos de filas
 */
function inicializarEventosFilas() {
    // Reinicializar eventos de hora en todos los inputs
    $('.input_horario').each(function() {
        inicializarEventosHora(this);
    });

    // Reinicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
}

// ======================= FUNCIONES DE INICIALIZACIÓN ADICIONALES =======================

/**
 * Inicializar Select2 y filtros en cascada
 */
function inicializarSelect2() {
    if (typeof $.fn.select2 !== 'undefined') {
        // Inicializar todos los selects
        $('#servicios_fil').select2({
            placeholder: 'Seleccionar servicio',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });

        // Inicializar selects de filtros jerárquicos si existen
        $('.select2-filtro').select2({
            placeholder: 'Seleccionar',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Inicializar select de clasificación si existe
        if ($('#clasificacion_fil').length) {
            $('#clasificacion_fil').select2({
                placeholder: '-TODAS-',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            $('#clasificacion_fil').on('change', function() {
                getProgramacion();
            });
        }

        // Event listener para cambio en Gerencia
        $('#gerencia_fil').on('change', function() {
            const gerenciaId = $(this).val();
            filtrarDepartamentosPorGerencia(gerenciaId);
            filtrarServiciosPorGerencia(gerenciaId);
            getProgramacion();
        });

        // Event listener para cambio en Departamento
        $('#departamento_fil').on('change', function() {
            const departamentoId = $(this).val();
            const gerenciaId = $(this).find(':selected').data('gerencia');
            
            // Sincronizar gerencia si es diferente
            if (gerenciaId && $('#gerencia_fil').val() != gerenciaId) {
                $('#gerencia_fil').val(gerenciaId).trigger('change.select2');
            }
            
            // Filtrar servicios pero NO seleccionar automáticamente
            filtrarServiciosPorDepartamento(departamentoId, false);
            
            // Si hay departamento seleccionado, cargar programación (mostrará jefes)
            // Si no hay departamento, getProgramacion manejará el caso
            getProgramacion();
        });

    } else {
        console.warn('Select2 no está disponible. Asegúrate de que la librería esté cargada.');
    }
}

/**
 * Filtrar departamentos por gerencia seleccionada
 */
function filtrarDepartamentosPorGerencia(gerenciaId) {
    if (!gerenciaId) {
        // Mostrar todos los departamentos
        $('#departamento_fil option').show();
        return;
    }

    // Filtrar departamentos que pertenecen a la gerencia
    $('#departamento_fil option').each(function() {
        const deptoGerenciaId = $(this).data('gerencia');
        if ($(this).val() === '') {
            $(this).show(); // Siempre mostrar "-TODOS-"
        } else if (deptoGerenciaId == gerenciaId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    // Resetear selección de departamento
    $('#departamento_fil').val('').trigger('change.select2');
}

/**
 * Filtrar servicios por gerencia seleccionada
 */
function filtrarServiciosPorGerencia(gerenciaId) {
    if (!gerenciaId) {
        $('#servicios_fil option').show();
        return;
    }

    $('#servicios_fil option').each(function() {
        const servGerenciaId = $(this).data('gerencia');
        if ($(this).val() === '' || $(this).val() === '0') {
            $(this).show();
        } else if (servGerenciaId == gerenciaId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    // Resetear selección
    const primeraOpcionVisible = $('#servicios_fil option:visible').first();
    $('#servicios_fil').val(primeraOpcionVisible.val()).trigger('change.select2');
}

/**
 * Filtrar servicios por departamento seleccionado
 * NOTA: No llamar getProgramacion() aquí porque se llama desde el evento change del departamento
 */
function filtrarServiciosPorDepartamento(departamentoId) {
    if (!departamentoId) {
        // Si no hay departamento seleccionado, usar filtro de gerencia
        const gerenciaId = $('#gerencia_fil').val();
        if (gerenciaId) {
            filtrarServiciosPorGerencia(gerenciaId);
        } else {
            $('#servicios_fil option').show();
        }
        return;
    }

    $('#servicios_fil option').each(function() {
        const servDepartamentoId = $(this).data('departamento');
        if ($(this).val() === '' || $(this).val() === '0') {
            $(this).show();
        } else if (servDepartamentoId == departamentoId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    // Seleccionar primera opción visible (que será "-TODOS-")
    const primeraOpcionVisible = $('#servicios_fil option:visible').first();
    // Usar 'change.select2' sin trigger para no disparar el evento change
    $('#servicios_fil').val(primeraOpcionVisible.val()).trigger('change.select2');
}

/**
 * Inicializar DateTimePickers
 */
function inicializarDateTimePickers() {
    // Bootstrap 5 - crear selector personalizado de mes/año

    // Configurar el selector de mes personalizado
    const monthInput = document.getElementById('d_fil');
    const calendarTrigger = document.getElementById('calendar-trigger');

    if (monthInput) {
        // Crear modal personalizado para selector de meses
        function createMonthPicker() {
            // Eliminar modal existente si existe
            const existingModal = document.getElementById('monthPickerModal');
            if (existingModal) {
                existingModal.remove();
            }

            const modal = document.createElement('div');
            modal.id = 'monthPickerModal';
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Seleccionar Mes</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <button type="button" id="prevYear" class="btn btn-outline-secondary btn-sm">&lt;</button>
                                <span id="currentYear" class="mx-3 fw-bold">${new Date().getFullYear()}</span>
                                <button type="button" id="nextYear" class="btn btn-outline-secondary btn-sm">&gt;</button>
                            </div>
                            <div class="row g-2" id="monthsGrid">
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="0">ene</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="1">feb</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="2">mar</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="3">abr</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="4">may</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="5">jun</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="6">jul</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="7">ago</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="8">sep</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="9">oct</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="10">nov</button></div>
                                <div class="col-4"><button type="button" class="btn btn-outline-primary btn-sm w-100 month-btn" data-month="11">dic</button></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            return modal;
        }

        function showMonthPicker() {
            const modal = createMonthPicker();
            const bsModal = new bootstrap.Modal(modal);

            let currentYear = new Date().getFullYear();
            const yearSpan = modal.querySelector('#currentYear');
            const prevYearBtn = modal.querySelector('#prevYear');
            const nextYearBtn = modal.querySelector('#nextYear');

            // Actualizar año
            function updateYear() {
                yearSpan.textContent = currentYear;
            }

            // Eventos de navegación de año
            prevYearBtn.onclick = () => {
                currentYear--;
                updateYear();
            };

            nextYearBtn.onclick = () => {
                currentYear++;
                updateYear();
            };

            // Eventos de selección de mes
            const monthBtns = modal.querySelectorAll('.month-btn');
            monthBtns.forEach(btn => {
                btn.onclick = () => {
                    const month = parseInt(btn.dataset.month) + 1; // +1 porque los meses van de 1-12
                    const formattedValue = `${month.toString().padStart(2, '0')}/${currentYear}`;
                    monthInput.value = formattedValue;

                    // Disparar evento de cambio para que se actualice la tabla
                    monthInput.dispatchEvent(new Event('change'));

                    bsModal.hide();
                };
            });

            bsModal.show();
        }

        // Eventos para mostrar el picker
        monthInput.onclick = showMonthPicker;
        if (calendarTrigger) {
            calendarTrigger.onclick = showMonthPicker;
        }
    }

    // Para los otros inputs de fecha usar Flatpickr si está disponible
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#d_prog, #h_prog', {
            locale: 'es',
            dateFormat: "Y-m-d"
        });
    } else {
        // Fallback: usar inputs nativos HTML5
        console.log('DatePicker: usando inputs nativos HTML5');

        // El campo #d_fil mantiene su formato mm/yyyy manejado manualmente
        // Los campos de fecha usan type="date" nativo
        $('#d_prog, #h_prog').attr('type', 'date');
    }
}

/**
 * Inicializar eventos de tabs
 */
function inicializarEventosTab() {
    $('#guardias_cont-tab').on('shown.bs.tab', function() {
        numGuardias = 0;
        tipoGuardias = 0;
        $('#cont_guardia_contrato').empty();
    });

    $('#guardias_pagas-tab').on('shown.bs.tab', function() {
        numGuardias = 0;
        tipoGuardias = 1;
        $('#cont_guardia_pagas').empty();
    });
}

/**
 * Inicializar eventos de formulario
 */
function inicializarEventosFormulario() {
    $('#form_buscar').on('submit', function(e) {
        e.preventDefault();
        getProgramacion();
    });

    $('#btn_guardar_prog_simple').on('click', function() {
        guardarProgramcionSimple();
    });

    $('#tipo_prog').on('change', function() {
        cambiarTipoProgramacion();
    });

    // Evento para copiar lunes a toda la semana
    $('#all').on('change', function() {
        changeLunes();
    });

    // Evento de búsqueda de empleado (Enter)
    $('#busqueda_empleado').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarEmpleado();
        }
    });

    // Evento de búsqueda al escribir (con debounce)
    let timeoutBusqueda;
    $('#busqueda_empleado').on('input', function() {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(function() {
            buscarEmpleado();
        }, 500); // Esperar 500ms después de dejar de escribir
    });
}

/**
 * Calcular horas por horario
 */
function calcularHorasPorHorario(horario) {
    if (!horario || !horario.includes(' - ')) {
        return 0;
    }

    const partes = horario.split(' - ');
    if (partes.length !== 2) {
        return 0;
    }

    try {
        const inicio = new Date(`1970-01-01T${partes[0]}:00`);
        const fin = new Date(`1970-01-01T${partes[1]}:00`);

        if (fin < inicio) {
            fin.setDate(fin.getDate() + 1);
        }

        const diferencia = fin - inicio;
        return diferencia / (1000 * 60 * 60); // Convertir a horas
    } catch (error) {
        return 0;
    }
}

/**
 * Formatear horas
 */
function formatearHoras(horas) {
    if (horas === 0) return '0:00';

    const horasEnteras = Math.floor(horas);
    const minutos = Math.round((horas - horasEnteras) * 60);

    return `${horasEnteras}:${minutos.toString().padStart(2, '0')}`;
}

/**
 * Actualizar totales
 */
function actualizarTotales() {
    // Calcular totales por columna
    const filas = $('#tabla_programacion tbody tr').length;

    let totalHoras = 0;
    let totalGuardiasContrato = 0;
    let totalGuardiasPagas = 0;
    let totalGeneral = 0;

    for (let i = 0; i < filas; i++) {
        const horas = parseFloat($(`#total_hr_ctrto_${i}`).text()) || 0;
        const guardiasC = parseFloat($(`#total_hr_guardias_ctrato_${i}`).text()) || 0;
        const guardiasP = parseFloat($(`#total_hr_guardias_pagas_${i}`).text()) || 0;

        totalHoras += horas;
        totalGuardiasContrato += guardiasC;
        totalGuardiasPagas += guardiasP;
        totalGeneral += horas + guardiasC + guardiasP;
    }

    // Actualizar footer si existe
    $('#total_hr_ctrto').text(formatearHoras(totalHoras));
    $('#total_hr_guardias_ctrato').text(formatearHoras(totalGuardiasContrato));
    $('#total_hr_guardias_pagas').text(formatearHoras(totalGuardiasPagas));
    $('#total_hr_total').text(formatearHoras(totalGeneral));
}

/**
 * Mostrar loader
 */
function mostrarLoader() {
    if ($('#overlay').length === 0) {
        const html = `
            <div class="overlay-fixed overlay-wrapper" id="overlay">
                <div class="overlay">
                    <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                    <div class="text-bold pt-2">Cargando...</div>
                </div>
            </div>
        `;
        $('body').append(html);
    } else {
        $('#overlay').show();
    }
}

/**
 * Ocultar loader
 */
function ocultarLoader() {
    $('#overlay').hide();
}

/**
 * Formatear fecha para mostrar
 */
function formatDate(fecha) {
    if (typeof fecha === 'string') {
        fecha = new Date(fecha);
    }
    return fecha.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

/**
 * Formatear fecha ISO
 */
function formatDateISO(fecha) {
    if (!fecha) return '';

    let fechaParsed = fecha;

    if (typeof fecha === 'string') {
        // Intentamos parsear solo si viene en formato ISO o similar; si no, devolvemos vacío para evitar RangeError
        fechaParsed = fecha.includes('-') ? new Date(fecha) : null;
    }

    if (!(fechaParsed instanceof Date) || Number.isNaN(fechaParsed.getTime())) {
        return '';
    }

    return fechaParsed.toISOString().split('T')[0];
}

// ======================= FUNCIONES DE VALIDACIÓN DE HORARIOS =======================

/**
 * Inicializar eventos de hora en input
 */
function inicializarEventosHora(elemento) {
    if (!elemento) return;

    $(elemento).on('input', function(e) {
        inputHour(e);
    });

    $(elemento).on('keydown', function(e) {
        if (e.key === 'Backspace' || e.key === 'Delete') {
            backSpaceDeleteHour(e);
        }
    });

    $(elemento).on('blur', function(e) {
        blurHour(e);
    });
}

/**
 * Validar entrada de hora
 */
function inputHour(event) {
    const input = event.target;
    let value = input.value.replace(/[^0-9]/g, ''); // Solo números

    // Formatear automáticamente
    if (value.length >= 3) {
        value = value.substring(0, 2) + ':' + value.substring(2, 4);
    }

    input.value = value;
}

/**
 * Manejar teclas de borrado
 */
function backSpaceDeleteHour(event) {
    const input = event.target;
    const value = input.value;

    // Si hay : y se está borrando, quitar el :
    if (value.includes(':') && value.length <= 3) {
        setTimeout(() => {
            input.value = value.replace(':', '');
        }, 0);
    }
}

/**
 * Validar hora al perder foco
 */
function blurHour(event) {
    const input = event.target;
    const value = input.value;

    if (!value) return;

    // Validar formato HH:MM
    const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;

    if (!timeRegex.test(value)) {
        // Si no es válido, intentar corregir
        let corrected = value.replace(/[^0-9:]/g, '');

        if (corrected.length === 4 && !corrected.includes(':')) {
            corrected = corrected.substring(0, 2) + ':' + corrected.substring(2);
        }

        input.value = corrected;
    }

    // Validar horario lógico
    const isValid = validarInputHorario(value, '');
    if (!isValid && value) {
        Toast.fire({
            icon: 'warning',
            title: 'Horario inválido: ' + value
        });
    }
}

/**
 * Validar input de horario
 */
function validarInputHorario(entrada, salida) {
    if (!entrada) return false;

    const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;

    if (!timeRegex.test(entrada)) return false;
    if (salida && !timeRegex.test(salida)) return false;

    return true;
}

/**
 * Cambiar horario del lunes a toda la semana
 */
function changeLunes() {
    if ($('#all').is(':checked')) {
        const entradaLunes = $('#dia_1_e').val();
        const salidaLunes = $('#dia_1_s').val();

        // Aplicar a martes-viernes
        for (let dia = 2; dia <= 5; dia++) {
            $(`#dia_${dia}_e`).val(entradaLunes);
            $(`#dia_${dia}_s`).val(salidaLunes);
        }
    }
}

/**
 * Calcular fecha del día según índice
 */
function calcularFechaDelDia(diaIndex) {
    const fechaValue = $('#d_fil').val(); // formato mm/yyyy
    const [mesStr, añoStr] = fechaValue.split('/');
    const año = parseInt(añoStr);
    const mes = parseInt(mesStr) - 1; // -1 porque Date usa índices de 0-11
    const dia = parseInt(diaIndex) + 1; // +1 porque diaIndex es 0-based

    const fecha = new Date(año, mes, dia);
    return formatDateISO(fecha);
}

/**
 * Calcular diferencia de horas
 */
function calcularDiferenciaHoras(horaInicio, horaFin) {
    const inicioMin = convertirHoraAMinutos(horaInicio);
    const finMin = convertirHoraAMinutos(horaFin);

    let diferencia = finMin - inicioMin;
    if (diferencia < 0) {
        diferencia += 24 * 60; // Cruza medianoche
    }

    return diferencia / 60; // Retornar en horas decimales
}

/**
 * Convertir hora a minutos
 */
function convertirHoraAMinutos(hora) {
    const partes = hora.split(':');
    return parseInt(partes[0]) * 60 + parseInt(partes[1]);
}

/**
 * Verificar si puede programar una fecha
 */
function puedeProgramar(fechaActual, fechaDeseada) {
    apiLaravel('/api/programacion-personal/puede-programar/' + fechaDeseada, 'GET')
        .then(response => {
            if (!response.puede) {
                Toast.fire({
                    icon: 'warning',
                    title: response.mensaje || 'No se puede programar en esta fecha'
                });
                return false;
            }
            return true;
        })
        .catch(error => {
            console.error('Error al verificar fecha:', error);
            return false;
        });
}

/**
 * Verificar diferencia de tiempo
 */
function checkTimeDifference(time1, time2, threshold) {
    const diff = Math.abs(calcularDiferenciaHoras(time1, time2));
    return diff >= threshold;
}

/**
 * Calcular horas por columna
 */
function calcularHorasXColumna(numeroColumna) {
    let total = 0;
    $(`.td_horario:nth-child(${numeroColumna + 2})`).each(function() {
        const texto = $(this).text();
        if (texto && texto.includes('-')) {
            const horarios = texto.split('\n');
            horarios.forEach(horario => {
                if (horario.includes('-')) {
                    total += calcularHorasDeHorario(horario.trim());
                }
            });
        }
    });
    return total;
}

/**
 * Calcular horas por fila
 */
function calcularHorasXFila(fila) {
    let total = 0;
    $(`#fila_${fila} .td_horario`).each(function() {
        const texto = $(this).text();
        if (texto && texto.includes('-')) {
            const horarios = texto.split('\n');
            horarios.forEach(horario => {
                if (horario.includes('-')) {
                    total += calcularHorasDeHorario(horario.trim());
                }
            });
        }
    });
    return total;
}

/**
 * Calcular horas de un horario string
 */
function calcularHorasDeHorario(horario) {
    const partes = horario.split(' - ');
    if (partes.length !== 2) return 0;

    return calcularDiferenciaHoras(partes[0], partes[1]);
}
