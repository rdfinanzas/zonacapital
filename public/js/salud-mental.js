/**
 * JavaScript para el módulo de Salud Mental
 */

// Variables globales
let dataTable = [];
let pagina = 1;
let totalRegistros = 0;
let cantidadPorPagina = 10;
let idEdit = 0;
let indEditar = 0;
let pacienteId = null;
let idEliminar = 0;

// Inicialización
document.addEventListener('DOMContentLoaded', function () {
    // Inicializar selectores
    initSelects();

    // Inicializar eventos
    initEvents();

    // Cargar tabla inicial
    cargarTabla();
});

/**
 * Inicializa los selectores con Select2
 */
function initSelects() {
    // Inicializar los select2
    $('.form-select').select2({
        width: '100%',
        placeholder: 'Seleccione una opción'
    });
}

/**
 * Inicializa los eventos de los elementos
 */
function initEvents() {
    // Botón agregar
    document.getElementById('btnAgregar').addEventListener('click', function () {
        limpiar();
        mostrarFormulario();
    });

    // Botón volver
    document.getElementById('btnVolver').addEventListener('click', function () {
        ocultarFormulario();
    });

    // Botón guardar
    document.getElementById('btnGuardar').addEventListener('click', function () {
        guardar();
    });

    // Botón limpiar
    document.getElementById('btnLimpiar').addEventListener('click', function () {
        limpiar();
    });

    // Botón eliminar
    document.getElementById('btnEliminar').addEventListener('click', function () {
        modalEliminar(idEdit);
    });

    // Botón confirmar eliminar
    document.getElementById('btn_eliminar_modal').addEventListener('click', function () {
        eliminar();
    });

    // Botón exportar
    document.getElementById('btnExportar').addEventListener('click', function () {
        exportar();
    });

    // Botón buscar DNI
    document.getElementById('btn_buscar_dni').addEventListener('click', function () {
        buscarDni();
    });

    // Formulario de filtros
    document.getElementById('formFiltros').addEventListener('submit', function (e) {
        e.preventDefault();
        pagina = 1;
        cargarTabla();
    });

    // Validación del formulario
    $('#formSaludMental').validate({
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
        submitHandler: function (form) {
            guardar();
        }
    });

    // Enter en DNI para buscar
    document.getElementById('dni').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarDni();
        }
    });
}

/**
 * Muestra el formulario y oculta el listado
 */
function mostrarFormulario() {
    document.getElementById('seccion-listado').classList.add('d-none');
    document.getElementById('seccion-formulario').classList.remove('d-none');
    document.getElementById('titulo-formulario').textContent = idEdit ? 'Editar Registro de Salud Mental' : 'Nuevo Registro de Salud Mental';
    window.scrollTo(0, 0);
}

/**
 * Oculta el formulario y muestra el listado
 */
function ocultarFormulario() {
    document.getElementById('seccion-formulario').classList.add('d-none');
    document.getElementById('seccion-listado').classList.remove('d-none');
}

/**
 * Busca un paciente por DNI
 */
function buscarDni() {
    let dni = document.getElementById('dni').value;

    if (!dni) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe ingresar un DNI para buscar'
        });
        return;
    }

    // Mostrar spinner
    Swal.fire({
        title: 'Buscando paciente...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Realizar petición AJAX
    apiLaravel(`/salud-mental/buscar-paciente?dni=${dni}`, 'GET')
        .then(response => {
            Swal.close();

            if (response.success) {
                const paciente = response.paciente;

                if (paciente) {
                    // Cargar datos del paciente
                    document.getElementById('paciente_id').value = paciente.IdPacienteRegTrab;
                    document.getElementById('ApellidoNombre').value = paciente.ApellidoNombre;
                    document.getElementById('FechaNacimiento').value = paciente.FechaNacimiento;
                    document.getElementById('domicilio').value = paciente.Domicilio;
                    document.getElementById('celular').value = paciente.Celular;                    // Seleccionar sexo
                    if (paciente.Sexo == 1) {
                        document.getElementById('sexo_m').checked = true;
                    } else if (paciente.Sexo == 2) {
                        document.getElementById('sexo_f').checked = true;
                    }

                    // Asignar pacienteId global
                    pacienteId = paciente.IdPacienteRegTrab;

                    // Mostrar formulario de persona
                    document.getElementById('form_persona').classList.remove('d-none');
                } else {
                    // Si no existe, limpiar campos y preparar para nuevo paciente
                    document.getElementById('paciente_id').value = '';
                    document.getElementById('ApellidoNombre').value = '';
                    document.getElementById('FechaNacimiento').value = '';
                    document.getElementById('domicilio').value = '';
                    document.getElementById('celular').value = '';
                    document.getElementById('sexo_m').checked = false;
                    document.getElementById('sexo_f').checked = false;

                    pacienteId = null;

                    // Mostrar formulario para nuevo paciente
                    document.getElementById('form_persona').classList.remove('d-none');

                    Swal.fire({
                        icon: 'info',
                        title: 'Información',
                        text: 'No se encontró un paciente con ese DNI. Complete los datos para registrarlo.'
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al buscar el paciente'
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error al buscar paciente:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al buscar el paciente'
            });
        });
}

/**
 * Limpia el formulario
 */
function limpiar() {
    // Resetear formulario
    document.getElementById('formSaludMental').reset();

    // Resetear variables
    idEdit = 0;
    indEditar = 0;
    idEliminar = 0;
    pacienteId = null;

    // Restablecer fecha actual
    document.getElementById('fecha_consulta').value = new Date().toISOString().substr(0, 10);

    // Ocultar sección de paciente
    document.getElementById('form_persona').classList.add('d-none');

    // Resetear select2
    $('.form-select').val('').trigger('change');
}

/**
 * Muestra el modal de confirmación para eliminar
 */
function modalEliminar(id) {
    if (id) {
        idEliminar = id;
        $('#modal_eliminar').modal('show');
    }
}

/**
 * Elimina un registro
 */
function eliminar() {
    // Mostrar spinner
    Swal.fire({
        title: 'Eliminando registro...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Cerrar modal
    $('#modal_eliminar').modal('hide');

    // Realizar petición AJAX
    apiLaravel(`/salud-mental/${idEliminar}`, 'DELETE')
        .then(response => {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message
                });

                // Limpiar formulario y ocultar
                limpiar();
                ocultarFormulario();

                // Recargar tabla
                cargarTabla();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Ocurrió un error al eliminar el registro'
                });
            }
        })
        .catch(error => {
            console.error('Error al eliminar:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al eliminar el registro'
            });
        });
}

/**
 * Guarda un registro (nuevo o edición)
 */
function guardar() {
    // Validar que el formulario sea válido
    if (!$('#formSaludMental').valid()) {
        return;
    }

    // Validar fecha de consulta
    const fechaConsulta = document.getElementById('fecha_consulta').value;
    if (!validarFecha(fechaConsulta)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La fecha de consulta no es válida'
        });
        return;
    }

    // Obtener datos del formulario
    const formData = new FormData(document.getElementById('formSaludMental'));

    // Agregar pacienteId si existe
    if (pacienteId) {
        formData.set('paciente_id', pacienteId);
    }

    // Preparar datos para AJAX
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    // Mostrar spinner
    Swal.fire({
        title: 'Guardando registro...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Determinar si es edición o nuevo registro
    const url = idEdit ? `/salud-mental/${idEdit}` : '/salud-mental';
    const method = idEdit ? 'PUT' : 'POST';

    // Realizar petición AJAX
    apiLaravel(url, method, data)
        .then(response => {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.data.message
                });

                // Limpiar formulario y ocultar
                limpiar();
                ocultarFormulario();

                // Recargar tabla
                cargarTabla();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Ocurrió un error al guardar el registro'
                });
            }
        })
        .catch(error => {
            console.error('Error al guardar:', error);

            let errorMessage = 'Ocurrió un error al guardar el registro';

            // Mostrar errores de validación si existen
            if (error.response && error.response.data && error.response.data.errors) {
                const errors = error.response.data.errors;
                errorMessage = Object.values(errors).flat().join('\n');
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
        });
}

/**
 * Edita un registro existente
 */
function editar(id, ind) {
    // Scroll al inicio de la página
    window.scrollTo(0, 0);

    // Limpiar formulario
    limpiar();

    // Establecer ID y índice
    idEdit = id;
    indEditar = ind;

    // Mostrar spinner
    Swal.fire({
        title: 'Cargando registro...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Realizar petición AJAX
    apiLaravel(`/salud-mental/${id}`, 'GET')
        .then(response => {
            if (response.success) {
                const registro = response.registro;

                // Asignar valores al formulario
                document.getElementById('idSaludMental').value = registro.id;
                document.getElementById('fecha_consulta').value = registro.fecha_consulta;

                // Seleccionar efector
                $('#efector_sel').val(registro.efector_Id).trigger('change');

                // Cargar datos del paciente
                if (registro.paciente) {
                    document.getElementById('dni').value = registro.paciente.DNI;
                    document.getElementById('ApellidoNombre').value = registro.paciente.ApellidoNombre;
                    document.getElementById('FechaNacimiento').value = registro.paciente.FechaNacimiento;
                    document.getElementById('domicilio').value = registro.paciente.Domicilio;
                    document.getElementById('celular').value = registro.paciente.Celular;

                    // Seleccionar sexo
                    if (registro.paciente.Sexo == 1) {
                        document.getElementById('sexo_m').checked = true;
                    } else if (registro.paciente.Sexo == 2) {
                        document.getElementById('sexo_f').checked = true;
                    }

                    // Asignar pacienteId global
                    pacienteId = registro.paciente.IdPacienteRegTrab;
                    document.getElementById('paciente_id').value = pacienteId;
                }

                // Seleccionar personal
                $('#personal_id').val(registro.personal_id).trigger('change');

                // Seleccionar presencia
                $('#presencia').val(registro.presencia).trigger('change');

                // Seleccionar turno
                $('#turno_asignado').val(registro.turno_asignado).trigger('change');

                // Seleccionar tipo de demanda
                $('#tipo_demanda').val(registro.tipo_demanda).trigger('change');

                // Seleccionar intervención
                $('#intervencion').val(registro.intervencion).trigger('change');

                // Seleccionar tipo de problema
                $('#tipo_problema').val(registro.tipo_problema).trigger('change');

                // Evolución
                document.getElementById('evolucion').value = registro.evolucion;

                // Mostrar formulario de persona
                document.getElementById('form_persona').classList.remove('d-none');

                // Mostrar formulario
                mostrarFormulario();

                Swal.close();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al cargar el registro'
                });
            }
        })
        .catch(error => {
            console.error('Error al editar:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al cargar el registro'
            });
        });
}

/**
 * Carga la tabla con los registros
 */
function cargarTabla() {
    // Obtener los valores de los filtros
    const dni = document.getElementById('dni_fil').value;
    const efector = document.getElementById('efector_sel_fil').value;
    const personalId = document.getElementById('personal_id2').value;
    const presencia = document.getElementById('presencia2').value;
    const turnoAsignado = document.getElementById('turno_asignado2').value;
    const tipoDemanda = document.getElementById('tipo_demanda2').value;
    const intervencion = document.getElementById('intervencion2').value;
    const tipoProblema = document.getElementById('tipo_problema2').value;
    const fechaDesde = document.getElementById('d_fil').value;
    const fechaHasta = document.getElementById('h_fil').value;

    // Mostrar spinner
    Swal.fire({
        title: 'Cargando registros...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Construir URL con parámetros
    let url = '/salud-mental/registros?';
    url += `pagina=${pagina}`;
    url += `&cantidad=${cantidadPorPagina}`;

    if (dni) url += `&dni_fil=${dni}`;
    if (efector) url += `&efector=${efector}`;
    if (personalId) url += `&personal_id=${personalId}`;
    if (presencia) url += `&presencia=${presencia}`;
    if (turnoAsignado) url += `&turno_asignado=${turnoAsignado}`;
    if (tipoDemanda) url += `&tipo_demanda=${tipoDemanda}`;
    if (intervencion) url += `&intervencion=${intervencion}`;
    if (tipoProblema) url += `&tipo_problema=${tipoProblema}`;
    if (fechaDesde) url += `&d=${fechaDesde}`;
    if (fechaHasta) url += `&h=${fechaHasta}`;

    // Realizar petición AJAX
    apiLaravel(url, 'GET')
        .then(response => {
            if (response.success) {
                // Guardar datos en variable global
                dataTable = response.registros;
                totalRegistros = response.total;

                // Actualizar tabla
                actualizarTabla();

                // Actualizar paginación
                actualizarPaginacion();

                Swal.close();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al cargar los registros'
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar tabla:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al cargar los registros'
            });
        });
}

/**
 * Actualiza el contenido de la tabla con los datos cargados
 */
function actualizarTabla() {
    const tbody = document.getElementById('tabla-salud-mental');
    const permisoEditar = document.getElementById('permiso_editar').value === '1';
    const permisoEliminar = document.getElementById('permiso_eliminar').value === '1';

    // Limpiar tabla
    tbody.innerHTML = '';

    if (dataTable.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="9" class="text-center">No se encontraron registros</td>`;
        tbody.appendChild(tr);
        return;
    }

    // Formatear fecha
    const formatearFecha = (fecha) => {
        if (!fecha) return '';
        const f = new Date(fecha);
        return f.toLocaleDateString('es-AR');
    };

    // Agregar filas
    dataTable.forEach((item, index) => {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${item.id}</td>
            <td>${formatearFecha(item.fecha_consulta)}</td>
            <td>${item.paciente ? item.paciente.DNI : '-'}</td>
            <td>${item.paciente ? item.paciente.ApellidoNombre : '-'}</td>
            <td>${item.efector ? item.efector.Nombre : '-'}</td>
            <td>${item.personal ? item.personal.ApellidoNombre : '-'}</td>
            <td>${getTipoDemandaLabel(item.tipo_demanda)}</td>
            <td>${getIntervencionLabel(item.intervencion)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    ${permisoEditar ? `<button type="button" class="btn btn-primary btn-editar" data-id="${item.id}" data-index="${index}">
                        <i class="bi bi-pencil"></i>
                    </button>` : ''}
                    ${permisoEliminar ? `<button type="button" class="btn btn-danger btn-eliminar" data-id="${item.id}">
                        <i class="bi bi-trash"></i>
                    </button>` : ''}
                </div>
            </td>
        `;

        tbody.appendChild(tr);
    });

    // Agregar eventos a los botones
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const index = this.getAttribute('data-index');
            editar(id, index);
        });
    });

    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            modalEliminar(id);
        });
    });
}

/**
 * Actualiza la paginación
 */
function actualizarPaginacion() {
    const paginacionContainer = document.getElementById('paginacion-contenedor');

    // Calcular total de páginas
    const totalPaginas = Math.ceil(totalRegistros / cantidadPorPagina);

    // Limpiar contenedor
    paginacionContainer.innerHTML = '';

    // Si no hay páginas, no mostrar paginación
    if (totalPaginas <= 1) {
        return;
    }

    // Crear paginación
    const nav = document.createElement('nav');
    nav.setAttribute('aria-label', 'Paginación');

    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';

    // Botón anterior
    const liAnterior = document.createElement('li');
    liAnterior.className = `page-item ${pagina === 1 ? 'disabled' : ''}`;

    const aAnterior = document.createElement('a');
    aAnterior.className = 'page-link';
    aAnterior.href = '#';
    aAnterior.setAttribute('aria-label', 'Anterior');
    aAnterior.innerHTML = '<span aria-hidden="true">&laquo;</span>';

    liAnterior.appendChild(aAnterior);
    ul.appendChild(liAnterior);

    // Agregar evento al botón anterior
    aAnterior.addEventListener('click', function (e) {
        e.preventDefault();
        if (pagina > 1) {
            pagina--;
            cargarTabla();
        }
    });

    // Números de página
    let startPage = Math.max(1, pagina - 2);
    let endPage = Math.min(totalPaginas, pagina + 2);

    if (endPage - startPage < 4) {
        if (startPage === 1) {
            endPage = Math.min(5, totalPaginas);
        } else {
            startPage = Math.max(1, totalPaginas - 4);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === pagina ? 'active' : ''}`;

        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;

        li.appendChild(a);
        ul.appendChild(li);

        // Agregar evento al número de página
        a.addEventListener('click', function (e) {
            e.preventDefault();
            pagina = i;
            cargarTabla();
        });
    }

    // Botón siguiente
    const liSiguiente = document.createElement('li');
    liSiguiente.className = `page-item ${pagina === totalPaginas ? 'disabled' : ''}`;

    const aSiguiente = document.createElement('a');
    aSiguiente.className = 'page-link';
    aSiguiente.href = '#';
    aSiguiente.setAttribute('aria-label', 'Siguiente');
    aSiguiente.innerHTML = '<span aria-hidden="true">&raquo;</span>';

    liSiguiente.appendChild(aSiguiente);
    ul.appendChild(liSiguiente);

    // Agregar evento al botón siguiente
    aSiguiente.addEventListener('click', function (e) {
        e.preventDefault();
        if (pagina < totalPaginas) {
            pagina++;
            cargarTabla();
        }
    });

    nav.appendChild(ul);
    paginacionContainer.appendChild(nav);

    // Mostrar información de paginación
    const info = document.createElement('div');
    info.className = 'text-center mt-2';
    info.innerHTML = `Mostrando ${(pagina - 1) * cantidadPorPagina + 1} a ${Math.min(pagina * cantidadPorPagina, totalRegistros)} de ${totalRegistros} registros`;

    paginacionContainer.appendChild(info);
}

/**
 * Exporta los registros a Excel
 */
function exportar() {
    // Obtener los valores de los filtros
    const dni = document.getElementById('dni_fil').value;
    const efector = document.getElementById('efector_sel_fil').value;
    const personalId = document.getElementById('personal_id2').value;
    const presencia = document.getElementById('presencia2').value;
    const turnoAsignado = document.getElementById('turno_asignado2').value;
    const tipoDemanda = document.getElementById('tipo_demanda2').value;
    const intervencion = document.getElementById('intervencion2').value;
    const tipoProblema = document.getElementById('tipo_problema2').value;
    const fechaDesde = document.getElementById('d_fil').value;
    const fechaHasta = document.getElementById('h_fil').value;

    // Construir URL
    let url = '/salud-mental/exportar?';
    url += `dni_fil=${dni || ''}`;
    url += `&efector=${efector || ''}`;
    url += `&personal_id=${personalId || ''}`;
    url += `&presencia=${presencia || ''}`;
    url += `&turno_asignado=${turnoAsignado || ''}`;
    url += `&tipo_demanda=${tipoDemanda || ''}`;
    url += `&intervencion=${intervencion || ''}`;
    url += `&tipo_problema=${tipoProblema || ''}`;
    url += `&d=${fechaDesde || ''}`;
    url += `&h=${fechaHasta || ''}`;

    // Mostrar spinner
    Swal.fire({
        title: 'Generando Excel...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Descargar archivo
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al generar el Excel');
            }
            return response.blob();
        })
        .then(blob => {
            Swal.close();

            // Crear objeto URL
            const url = window.URL.createObjectURL(blob);

            // Crear link de descarga
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'salud_mental.xlsx';

            // Agregar al DOM, hacer clic y eliminar
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        })
        .catch(error => {
            console.error('Error al exportar:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al generar el Excel'
            });
        });
}

/**
 * Valida una fecha
 * @param {string} fecha - Fecha en formato YYYY-MM-DD
 * @returns {boolean} - True si la fecha es válida
 */
function validarFecha(fecha) {
    const datePattern = /^\d{4}-\d{2}-\d{2}$/; // Formato YYYY-MM-DD

    if (!datePattern.test(fecha)) {
        return false;
    }

    const inputDate = new Date(fecha);
    const today = new Date();

    // Establece las horas, minutos y segundos de la fecha de hoy a 00:00:00 para una comparación más precisa
    today.setHours(0, 0, 0, 0);

    if (inputDate > today) {
        return false;
    }

    // Convertir la fecha ingresada a un objeto Date
    const [year, month, day] = fecha.split('-').map(Number);
    const inputDate2 = new Date(year, month - 1, day);

    const anoactual = new Date().getFullYear();

    if (year < 1900 || year > anoactual) {
        return false;
    }

    const minDate = new Date(1900, 0, 1);

    // Validar rango de fecha
    if (inputDate2 < minDate || inputDate2 > today) {
        return false;
    }

    return true;
}

/**
 * Obtiene la etiqueta de tipo de demanda
 */
function getTipoDemandaLabel(id) {
    // Esta función debería obtener la etiqueta del tipo de demanda según su ID
    // Como no tenemos acceso a los datos de la tabla key_value desde el JavaScript,
    // se puede implementar con un switch case o dejar para que el backend lo resuelva
    return id || '-';
}

/**
 * Obtiene la etiqueta de intervención
 */
function getIntervencionLabel(id) {
    // Esta función debería obtener la etiqueta de la intervención según su ID
    // Como no tenemos acceso a los datos de la tabla key_value desde el JavaScript,
    // se puede implementar con un switch case o dejar para que el backend lo resuelva
    return id || '-';
}
