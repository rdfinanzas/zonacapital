/**
 * Módulo JavaScript para la gestión de Productividad
 * -----------------------------------------------
 * 
 * DESCRIPCIÓN GENERAL:
 * Este archivo contiene la lógica completa del frontend para el módulo de Productividad,
 * implementando un patrón de Single Page Application (SPA) con carga dinámica de datos
 * mediante AJAX y manejo de formularios con validación.
 * 
 * ESTRUCTURA DEL CÓDIGO:
 * - Inicialización y variables globales: Configuración inicial, permisos y variables de estado
 * - Funciones de inicialización: Configuración de UI, inicialización de plugins y event listeners
 * - Funciones de paginación y carga: Gestión de paginación y carga de datos mediante AJAX
 * - Funciones de UI: Mostrar/ocultar secciones (listado vs formulario)
 * - Funciones CRUD: Operaciones sobre registros de productividad
 *   - Ver: verProductividad(id) -> cargarDatosProductividad(id, true)
 *   - Crear/Editar: guardarProductividad() -> apiLaravel(url, metodo, datos)
 *   - Eliminar: eliminarProductividad(id) -> apiLaravel(`/productividad/eliminar/${id}`, 'DELETE')
 * - Funciones auxiliares: Manejo de meses cerrados, mensajes y carga
 * 
 * FLUJO PRINCIPAL:
 * 1. Inicialización de componentes UI y validadores (initUI)
 * 2. Configuración de listeners para eventos de usuario (setupEventListeners)
 * 3. Carga inicial de datos si existe el formulario de filtros (cargarProductividad)
 * 4. Interacción del usuario con el listado o formulario
 * 5. Operaciones CRUD mediante apiLaravel (función global definida en common.js)
 * 
 * VALIDACIÓN:
 * - Utiliza jQuery Validate para validación del lado del cliente
 * - Reglas configuradas para cada campo en initUI()
 * 
 * PERMISOS:
 * - Obtiene permisos desde inputs hidden en el HTML
 * - Ajusta la interfaz según los permisos del usuario (crear, leer, editar, eliminar)
 * 
 * TECNOLOGÍAS:
 * - jQuery y jQuery Validate para manipulación del DOM y validación
 * - Select2 para mejorar la experiencia con elementos select
 * - SweetAlert2 para notificaciones y confirmaciones
 * - CustomPagination para la paginación (componente personalizado)
 */

document.addEventListener('DOMContentLoaded', function () {
    // ========================================
    // INICIALIZACIÓN Y VARIABLES GLOBALES
    // ========================================

    // Obtener permisos desde los inputs hidden
    const permisos = {
        crear: document.getElementById('permiso_crear')?.value === '1',
        leer: document.getElementById('permiso_leer')?.value === '1',
        editar: document.getElementById('permiso_editar')?.value === '1',
        eliminar: document.getElementById('permiso_eliminar')?.value === '1',
        jefe: document.getElementById('permiso_jefe')?.value === '1'
    };
    let Toast;
    // Variables de paginación
    const paginacionContenedor = document.getElementById('paginacion-contenedor');
    let paginacion;

    // Contenedores de secciones
    const seccionListado = document.getElementById('seccion-listado');
    const seccionFormulario = document.getElementById('seccion-formulario');
    const accionesPrincipales = document.getElementById('acciones-principales');

    // Variable para el modo del formulario (crear, editar, ver)
    let modoFormulario = 'crear';
    let mesesCerrados = [];

    // Inicializar componentes UI
    initUI();

    // Agregar event listeners
    setupEventListeners();

    // Cargar datos iniciales (si hay un formulario)
    if (document.getElementById('formFiltros')) {
        cargarProductividad(1);
    }

    // ========================================
    // FUNCIONES DE INICIALIZACIÓN
    // ========================================

    /**
     * Inicializar componentes de la interfaz
     */
    function initUI() {
        // Inicializar Select2 para los filtros si está disponible
        if (typeof $.fn.select2 !== 'undefined') {
            const selectOptions = {
                theme: 'bootstrap-5',
                allowClear: true
            };
            Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 10000,
            });


            $('#filterProfesional').select2({
                ...selectOptions,
                placeholder: 'Seleccione un profesional'
            });

            $('#filterEfector').select2({
                ...selectOptions,
                placeholder: 'Seleccione un efector'
            });

            $('#filterServicio').select2({
                ...selectOptions,
                placeholder: 'Seleccione un servicio'
            });

            // Inicializar Select2 para el formulario
            $('#idPersonal').select2({
                ...selectOptions,
                placeholder: 'Seleccione un profesional'
            });

            $('#efectorSel').select2({
                ...selectOptions,
                placeholder: 'Seleccione un efector'
            });

            $('#servicio').select2({
                ...selectOptions,
                placeholder: 'Seleccione una especialidad'
            });
        }

        // Inicializar validación de formulario con jQuery Validate
        if (typeof $.fn.validate !== 'undefined') {
            $("#formProductividad").validate({
                errorElement: "span",
                errorPlacement: function (error, element) {
                    error.addClass("invalid-feedback");
                    element.closest(".form-group").append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass("is-invalid");
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass("is-invalid");
                },
                rules: {
                    anio: {
                        required: true,
                        digits: true
                    },
                    mes: {
                        required: true,
                        digits: true
                    },
                    idPersonal: {
                        required: true
                    },
                    servicio: {
                        required: true
                    },
                    dias: {
                        required: true,
                        digits: true,

                    }
                },
                messages: {
                    anio: {
                        required: "El año es obligatorio",
                        digits: "Ingrese un año válido"
                    },
                    mes: {
                        required: "El mes es obligatorio",
                        digits: "Seleccione un mes válido"
                    },
                    idPersonal: {
                        required: "Debe seleccionar un profesional"
                    },
                    servicio: {
                        required: "Debe seleccionar una especialidad"
                    },
                    dias: {
                        required: "El número de días es obligatorio",
                        digits: "Ingrese solo números enteros",
                        min: "El valor mínimo es 0",
                        max: "El valor máximo es 31"
                    }
                },
                submitHandler: function (form, event) {
                    event.preventDefault();
                    guardarProductividad();
                }
            });
        }
    }

    /**
     * Configurar event listeners
     */
    function setupEventListeners() {
        // Event listener para el formulario de filtros
        const formFiltros = document.getElementById('formFiltros');
        if (formFiltros) {
            formFiltros.addEventListener('submit', function (e) {
                e.preventDefault();
                cargarProductividad(1); // Cargar primera página con nuevos filtros
            });
        }

        // Event listener para el botón de agregar
        const btnAgregar = document.getElementById('btnAgregar');
        if (btnAgregar) {
            btnAgregar.addEventListener('click', function () {
                modoFormulario = 'crear';
                limpiarFormulario();
                document.getElementById('titulo-formulario').textContent = 'Agregar Productividad';

                // Habilitar o deshabilitar elementos según el modo
                configurarFormulario();

                // Verificar meses cerrados
                getMesesCerrados();

                // Mostrar formulario y ocultar listado
                mostrarFormulario();
            });
        }

        // Event listener para el botón de exportar
        const btnExportar = document.getElementById('btnExportar');
        if (btnExportar) {
            btnExportar.addEventListener('click', function () {
                exportarExcel();
            });
        }

        // Event listener para los botones de acción en la tabla
        const tablaProductividad = document.getElementById('tabla-productividad');
        if (tablaProductividad) {
            tablaProductividad.addEventListener('click', function (e) {
                // Buscar el botón más cercano
                const boton = e.target.closest('button');
                if (!boton) return;

                const id = boton.getAttribute('data-id');
                if (!id) return;

                if (boton.classList.contains('btn-ver')) {
                    verProductividad(id);
                } else if (boton.classList.contains('btn-editar')) {
                    editarProductividad(id);
                } else if (boton.classList.contains('btn-eliminar')) {
                    eliminarProductividad(id);
                }
            });
        }

        // Event listener para el botón guardar (submit del formulario se maneja con jQuery Validate)
        const btnGuardar = document.getElementById('btnGuardar');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function () {
                // Disparar la validación del formulario
                $("#formProductividad").submit();
            });
        }

        // Event listener para el botón de limpiar
        const btnLimpiar = document.getElementById('btnLimpiar');
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', function () {
                limpiarFormulario();
            });
        }

        // Event listener para el botón de eliminar en el formulario
        const btnEliminar = document.getElementById('btnEliminar');
        if (btnEliminar) {
            btnEliminar.addEventListener('click', function () {
                const id = document.getElementById('idProductividad').value;
                if (id) {
                    eliminarProductividad(id);
                }
            });
        }

        // Event listener para el botón de volver
        const btnVolver = document.getElementById('btnVolver');
        if (btnVolver) {
            btnVolver.addEventListener('click', function () {
                mostrarListado();
            });
        }

        // Event listener para el botón de guardar (se usa jQuery Validate)
        // Ya configurado arriba

        // Event listener para el botón de cerrar mes
        const btnCerrarMes = document.getElementById('btnCerrarMes');
        if (btnCerrarMes) {
            btnCerrarMes.addEventListener('click', function () {
                cerrarMes();
            });
        }

        // Event listener para el cambio de año (verificar meses cerrados)
        const selectAnio = document.getElementById('anio');
        if (selectAnio) {
            selectAnio.addEventListener('change', function () {
                getMesesCerrados();
            });
        }
    }

    // ========================================
    // FUNCIONES DE PAGINACIÓN Y CARGA DE DATOS
    // ========================================

    /**
     * Inicializar o actualizar la paginación
     * @param {number} totalRegistros - Total de registros
     * @param {number} porPagina - Registros por página
     * @param {number} paginaActual - Página actual
     */
    function initPaginacion(totalRegistros, porPagina, paginaActual) {
        // Si no hay registros, usar valores predeterminados
        totalRegistros = totalRegistros || 0;
        porPagina = porPagina || 10;
        paginaActual = paginaActual || 1;

        if (paginacion) {
            paginacion.setPaginationData(totalRegistros, porPagina, paginaActual);
        } else if (paginacionContenedor) {
            paginacion = new CustomPagination('#paginacion-contenedor', {
                onPageChange: (pagina) => cargarProductividad(pagina),
                onPageSizeChange: () => cargarProductividad(1),
                initialPageSize: porPagina,
                pageSizeOptions: [5, 10, 25, 50, 100]
            });

            paginacion.setPaginationData(totalRegistros, porPagina, paginaActual);
        }
    }

    /**
     * Cargar datos de productividad con AJAX
     * @param {number} pagina - Número de página a cargar
     */
    function cargarProductividad(pagina = 1) {
        // Obtener valores de filtros
        const anio = document.getElementById('filterAnio')?.value || '';
        const mes = document.getElementById('filterMes')?.value || '';
        const profesional = document.getElementById('filterProfesional')?.value || '';
        const efector = document.getElementById('filterEfector')?.value || '';
        const servicio = document.getElementById('filterServicio')?.value || '';
        const porPagina = paginacion ? paginacion.pageSize : 10;

        // Preparar datos para enviar
        const datos = { anio, mes, profesional, efector, servicio, pagina, porPagina };

        // Mostrar indicador de carga
        mostrarIndicadorCarga(true);

        // Realizar petición AJAX utilizando la función apiLaravel
        apiLaravel('/productividad/filtrar', 'GET', datos)
            .then(respuesta => {
                // Actualizar la tabla con los resultados
                actualizarTabla(respuesta.data);

                // Actualizar la paginación
                initPaginacion(respuesta.total, respuesta.porPagina, respuesta.pagina);

                // Ocultar indicador de carga
                mostrarIndicadorCarga(false);
            })
            .catch(error => {
                console.error('Error al cargar datos:', error);
                mostrarMensaje('error', 'Error al cargar los datos: ' + error);
                mostrarIndicadorCarga(false);
            });
    }

    /**
     * Actualizar la tabla de productividad con los datos recibidos
     * @param {Array} datos - Array de objetos con los datos de productividad
     */
    function actualizarTabla(datos) {
        const tabla = document.getElementById('tabla-productividad');
        if (!tabla) return;

        // Limpiar la tabla
        tabla.innerHTML = '';

        // Si no hay datos, mostrar mensaje
        if (!datos || datos.length === 0) {
            tabla.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">No se encontraron registros</td>
                </tr>
            `;
            return;
        }

        // Generar filas con los datos
        datos.forEach(item => {
            let accionesBotones = `
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-sm btn-info btn-ver" data-id="${item.IdProductividad}">
                        <i class="bi bi-eye"></i>
                    </button>
            `;

            if (permisos.editar) {
                accionesBotones += `
                    <button type="button" class="btn btn-sm btn-warning btn-editar" data-id="${item.IdProductividad}">
                        <i class="bi bi-pencil"></i>
                    </button>
                `;
            }

            if (permisos.eliminar) {
                accionesBotones += `
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="${item.IdProductividad}">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
            }

            accionesBotones += `</div>`;

            const fila = `
                <tr>
                    <td>${item.IdProductividad}</td>
                    <td>${item.Anio}</td>
                    <td>${item.Mes}</td>
                    <td>${item.Efector}</td>
                    <td>${item.Personal}</td>
                    <td>${item.Operador}</td>
                    <td>${item.Fecha_registro}</td>
                    <td>${accionesBotones}</td>
                </tr>
            `;

            tabla.innerHTML += fila;
        });
    }

    // ========================================
    // FUNCIONES DE MOSTRAR/OCULTAR SECCIONES
    // ========================================

    /**
     * Mostrar el formulario y ocultar el listado
     */
    function mostrarFormulario() {
        if (seccionListado) seccionListado.classList.add('d-none');
        if (seccionFormulario) seccionFormulario.classList.remove('d-none');
        if (accionesPrincipales) accionesPrincipales.classList.add('d-none');
    }

    /**
     * Mostrar el listado y ocultar el formulario
     */
    function mostrarListado() {
        if (seccionListado) seccionListado.classList.remove('d-none');
        if (seccionFormulario) seccionFormulario.classList.add('d-none');
        if (accionesPrincipales) accionesPrincipales.classList.remove('d-none');
    }

    // ========================================
    // FUNCIONES DEL FORMULARIO DE PRODUCTIVIDAD
    // ========================================

    /**
     * Limpiar el formulario de productividad
     */
    function limpiarFormulario() {
        // Limpiar campos ocultos
        document.getElementById('idProductividad').value = '';

        // Resetear selects
        document.getElementById('anio').value = new Date().getFullYear();
        document.getElementById('mes').value = '';

        if (document.getElementById('efectorSel')) {
            document.getElementById('efectorSel').value = '';
            if (typeof $.fn.select2 !== 'undefined') {
                $('#efectorSel').trigger('change');
            }
        }

        document.getElementById('idPersonal').value = '';
        document.getElementById('servicio').value = '';
        if (typeof $.fn.select2 !== 'undefined') {
            $('#idPersonal').trigger('change');
            $('#servicio').trigger('change');
        }

        // Resetear inputs numéricos
        document.getElementById('dias').value = '';

        // Resetear tablas de datos
        const inputs = document.querySelectorAll('input[name^="c_"]');
        inputs.forEach(input => {
            input.value = '0';
        });
    }

    /**
     * Configurar el formulario según el modo (crear, editar, ver)
     */
    function configurarFormulario() {
        const esVisualizacion = modoFormulario === 'ver';

        // Seleccionar elementos del formulario y botones del footer
        const inputsFormulario = document.querySelectorAll('#formProductividad input, #formProductividad select');
        const botones = document.querySelectorAll('#btnGuardar, #btnLimpiar, #btnEliminar, #btnVolver');

        // Configurar inputs y selects del formulario
        inputsFormulario.forEach(input => {
            if (input.type !== 'button' && input.type !== 'submit' && input.type !== 'hidden') {
                input.disabled = esVisualizacion;
            }
        });

        // Configurar botones individualmente
        const btnGuardar = document.getElementById('btnGuardar');
        const btnLimpiar = document.getElementById('btnLimpiar');
        const btnEliminar = document.getElementById('btnEliminar');
        const btnVolver = document.getElementById('btnVolver');

        if (btnGuardar) {
            btnGuardar.style.display = esVisualizacion ? 'none' : 'inline-block';
        }

        if (btnLimpiar) {
            btnLimpiar.style.display = esVisualizacion ? 'none' : 'inline-block';
        }

        if (btnEliminar) {
            btnEliminar.style.display = ((esVisualizacion || modoFormulario === 'editar') && permisos.eliminar) ? 'inline-block' : 'none';
        }

        if (btnVolver) {
            btnVolver.style.display = 'inline-block'; // Siempre visible
        }
    }

    /**
     * Cargar datos de un registro para visualización
     * @param {number} id - IdProductividad del registro
     */
    function verProductividad(id) {
        modoFormulario = 'ver';
        document.getElementById('titulo-formulario').textContent = 'Ver Productividad';

        // Cargar datos del registro
        cargarDatosProductividad(id, true);
    }

    /**
     * Cargar datos de un registro para edición
     * @param {number} id - IdProductividad del registro
     */
    function editarProductividad(id) {
        modoFormulario = 'editar';
        document.getElementById('titulo-formulario').textContent = 'Editar Productividad';

        // Verificar meses cerrados
        getMesesCerrados();

        // Cargar datos del registro
        cargarDatosProductividad(id, false);
    }

    /**
     * Cargar datos de productividad para ver o editar
     * @param {number} id - IdProductividad del registro
     * @param {boolean} soloLectura - Si es true, deshabilita los campos
     */
    function cargarDatosProductividad(id, soloLectura) {
        mostrarIndicadorCarga(true);

        apiLaravel(`/productividad/${id}`, 'GET')
            .then(respuesta => {
                if (respuesta.success) {
                    const datos = respuesta.data;

                    // Cargar IdProductividad oculto
                    document.getElementById('idProductividad').value = datos.IdProductividad;

                    // Cargar selects
                    document.getElementById('anio').value = datos.Anio;
                    document.getElementById('mes').value = datos.Mes;

                    if (document.getElementById('efectorSel')) {
                        document.getElementById('efectorSel').value = datos.Efector_Id;
                        if (typeof $.fn.select2 !== 'undefined') {
                            $('#efectorSel').trigger('change');
                        }
                    }

                    document.getElementById('idPersonal').value = datos.Personal_Id;
                    document.getElementById('servicio').value = datos.Servicio_Id;
                    if (typeof $.fn.select2 !== 'undefined') {
                        $('#idPersonal').trigger('change');
                        $('#servicio').trigger('change');
                    }

                    // Cargar inputs numéricos
                    document.getElementById('dias').value = datos.Dias;

                    // Cargar tablas de datos
                    document.querySelector('input[name="c_0_0"]').value = datos.In_0_0 || 0;
                    document.querySelector('input[name="c_0_1"]').value = datos.In_0_1 || 0;
                    document.querySelector('input[name="c_0_2"]').value = datos.In_0_2 || 0;
                    document.querySelector('input[name="c_0_3"]').value = datos.In_0_3 || 0;
                    document.querySelector('input[name="c_0_4"]').value = datos.In_0_4 || 0;
                    document.querySelector('input[name="c_0_5"]').value = datos.In_0_5 || 0;
                    document.querySelector('input[name="c_0_6"]').value = datos.In_0_6 || 0;

                    document.querySelector('input[name="c_1_0"]').value = datos.In_1_0 || 0;
                    document.querySelector('input[name="c_1_1"]').value = datos.In_1_1 || 0;
                    document.querySelector('input[name="c_1_2"]').value = datos.In_1_2 || 0;
                    document.querySelector('input[name="c_1_3"]').value = datos.In_1_3 || 0;
                    document.querySelector('input[name="c_1_4"]').value = datos.In_1_4 || 0;
                    document.querySelector('input[name="c_1_5"]').value = datos.In_1_5 || 0;
                    document.querySelector('input[name="c_1_6"]').value = datos.In_1_6 || 0;

                    // Configurar formulario según el modo
                    configurarFormulario();

                    // Mostrar formulario
                    mostrarFormulario();
                } else {
                    mostrarMensaje('error', respuesta.message || 'Error al cargar los datos');
                }

                mostrarIndicadorCarga(false);
            })
            .catch(error => {
                console.error('Error al cargar datos de productividad:', error);
                mostrarMensaje('error', 'Error al cargar los datos: ' + error);
                mostrarIndicadorCarga(false);
            });
    }

    /**
     * Guardar datos de productividad (crear o actualizar)
     */
    function guardarProductividad() {
        // La validación ya se ha realizado mediante jQuery Validate
        mostrarIndicadorCarga(true);

        // Obtener datos del formulario usando jQuery (más compatible con jQuery Validate)
        const formData = new FormData(document.getElementById('formProductividad'));
        const datos = {};

        // Convertir FormData a objeto
        for (let [key, value] of formData.entries()) {
            datos[key] = value;
        }

        // Determinar si es creación o actualización
        const id = datos.IdProductividad;
        const metodo = id ? 'PUT' : 'POST';
        const url = id ? `/productividad/${id}` : '/productividad';

        apiLaravel(url, metodo, datos)
            .then(respuesta => {
                if (respuesta.success) {
                    mostrarMensaje('success', respuesta.message || 'Registro guardado correctamente');
                    mostrarListado();
                    cargarProductividad(paginacion ? paginacion.currentPage : 1);
                } else {
                    mostrarMensaje('error', respuesta.message || 'Error al guardar los datos');
                }

                mostrarIndicadorCarga(false);
            })
            .catch(error => {
                console.error('Error al guardar datos:', error);
                mostrarMensaje('error', 'Error al guardar los datos: ' + error);
                mostrarIndicadorCarga(false);
            });
    }

    /**
     * Eliminar un registro de productividad
     * @param {number} id - IdProductividad del registro
     */
    function eliminarProductividad(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarIndicadorCarga(true);

                apiLaravel(`/productividad/eliminar/${id}`, 'DELETE')
                    .then(respuesta => {
                        if (respuesta.success) {
                            mostrarMensaje('success', respuesta.message || 'Registro eliminado correctamente');

                            // Si estamos en el formulario, volver al listado
                            if (modoFormulario === 'ver' || modoFormulario === 'editar') {
                                mostrarListado();
                            }

                            // Recargar datos
                            cargarProductividad(paginacion ? paginacion.currentPage : 1);
                        } else {
                            mostrarMensaje('error', respuesta.message || 'Error al eliminar el registro');
                        }

                        mostrarIndicadorCarga(false);
                    })
                    .catch(error => {
                        console.error('Error al eliminar:', error);
                        mostrarMensaje('error', 'Error al eliminar el registro: ' + error);
                        mostrarIndicadorCarga(false);
                    });
            }
        });
    }

    /**
     * Obtener meses cerrados para un año específico
     */
    function getMesesCerrados() {
        const anio = document.getElementById('anio').value;

        apiLaravel('/productividad/meses-cerrados', 'GET', { anio })
            .then(respuesta => {
                if (respuesta.success) {
                    mesesCerrados = respuesta.data;

                    // Deshabilitar opciones en el select de meses
                    const selectMes = document.getElementById('mes');
                    const opciones = selectMes.options;

                    for (let i = 0; i < opciones.length; i++) {
                        const valor = opciones[i].value;
                        if (valor && esMesCerrado(valor)) {
                            opciones[i].disabled = true;
                            opciones[i].textContent = opciones[i].textContent + ' (Cerrado)';
                        } else {
                            opciones[i].disabled = false;
                            opciones[i].textContent = opciones[i].textContent.replace(' (Cerrado)', '');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error al obtener meses cerrados:', error);
            });
    }

    /**
     * Verificar si un mes está cerrado
     * @param {number} mes - Número de mes
     * @returns {boolean} - true si el mes está cerrado
     */
    function esMesCerrado(mes) {
        return mesesCerrados.some(item => item.MesCerr == mes);
    }

    /**
     * Cerrar un mes para que no se puedan modificar registros
     */
    function cerrarMes() {
        const anio = document.getElementById('anio').value;
        const mes = document.getElementById('mes').value;

        if (!anio || !mes) {
            mostrarMensaje('error', 'Debe seleccionar año y mes');
            return;
        }

        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción bloqueará la edición de registros para este mes',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cerrar mes',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarIndicadorCarga(true);

                apiLaravel('/productividad/cerrar-mes', 'POST', { anio, mes })
                    .then(respuesta => {
                        if (respuesta.success) {
                            mostrarMensaje('success', respuesta.message || 'Mes cerrado correctamente');
                            getMesesCerrados();
                        } else {
                            mostrarMensaje('error', respuesta.message || 'Error al cerrar el mes');
                        }

                        mostrarIndicadorCarga(false);
                    })
                    .catch(error => {
                        console.error('Error al cerrar mes:', error);
                        mostrarMensaje('error', 'Error al cerrar el mes: ' + error);
                        mostrarIndicadorCarga(false);
                    });
            }
        });
    }

    /**
     * Exportar datos de productividad a Excel
     */
    function exportarExcel() {
        // Obtener valores de filtros actuales
        const anio = document.getElementById('filterAnio')?.value || '';
        const mes = document.getElementById('filterMes')?.value || '';
        const profesional = document.getElementById('filterProfesional')?.value || '';
        const efector = document.getElementById('filterEfector')?.value || '';
        const servicio = document.getElementById('filterServicio')?.value || '';

        // Preparar parámetros para la URL
        const params = new URLSearchParams();
        if (anio) params.append('anio', anio);
        if (mes) params.append('mes', mes);
        if (profesional) params.append('profesional', profesional);
        if (efector) params.append('efector', efector);
        if (servicio) params.append('servicio', servicio);

        // Crear la URL de exportación
        const exportUrl = `/productividad/exportar?${params.toString()}`;

        // Mostrar mensaje de inicio de exportación
        mostrarMensaje('info', 'Iniciando exportación...');

        // Abrir la URL en una nueva ventana para descargar el archivo
        window.open(exportUrl, '_blank');
    }

    // ========================================
    // FUNCIONES DE UTILIDAD
    // ========================================

    /**
     * Mostrar u ocultar indicador de carga
     * @param {boolean} mostrar - true para mostrar, false para ocultar
     */
    function mostrarIndicadorCarga(mostrar) {
        // Implementar según la UI (spinners, overlay, etc.)
        if (mostrar) {
            document.body.style.cursor = 'wait';
        } else {
            document.body.style.cursor = 'default';
        }
    }

    /**
     * Mostrar mensaje de notificación
     * @param {string} tipo - Tipo de mensaje (success, error, warning, info)
     * @param {string} mensaje - Texto del mensaje
     */
    function mostrarMensaje(tipo, mensaje) {
        // Usar SweetAlert2 si está disponible
        if (typeof Swal !== 'undefined') {
            Toast.fire({
                icon: tipo,
                title: mensaje
            });
        } else {
            // Fallback a alert
            alert(mensaje);
        }
    }
});

