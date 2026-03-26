/**
 * Módulo JavaScript para la gestión de Personal
 * -----------------------------------------------
 *
 * DESCRIPCIÓN GENERAL:
 * Este archivo contiene la lógica completa del frontend para el módulo de Personal,
 * implementando un patrón de Single Page Application (SPA) con carga dinámica de datos
 * mediante AJAX y manejo de formularios con validación.
 *
 * ESTRUCTURA DEL CÓDIGO:
 * - Inicialización y variables globales: Configuración inicial, permisos y variables de estado
 * - Funciones de inicialización: Configuración de UI, inicialización de plugins y event listeners
 * - Funciones de paginación y carga: Gestión de paginación y carga de datos mediante AJAX
 * - Funciones de UI: Mostrar/ocultar secciones (listado vs formulario)
 * - Funciones CRUD: Operaciones sobre registros de personal
 * - Funciones auxiliares: Manejo de imágenes, validaciones y utilidades
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
        eliminar: document.getElementById('permiso_eliminar')?.value === '1'
    };

    // Variables de estado
    let modoFormulario = 'crear'; // 'crear', 'editar', 'ver'
    let empleadoEditando = null;
    let cargandoDatos = false; // Variable para controlar cuando estamos cargando datos iniciales
    let cropImg = null;
    let selectoresIniciales = {};
    let num_relaciones = 0;
    let num_doc = 0;
    let arrImgsDel = [];
    let del_jor = [];
    let data_local = [];
    let initCameraModal = false;
    let Toast;
    let JornadaOriginal_Id = 0;
    let eliminarFotoFlag = false; // Bandera para indicar si se debe eliminar la foto
    let FechaJornadaOri = "";

    // Funciones para validar DNI y legajo
    function validarDniExistente(dni) {
        if (!dni || dni.trim() === '') return;

        const idEmpleado = document.getElementById('idEmpleado')?.value;

        apiLaravel('/personal/check-dni', 'POST', {
            dni: dni,
            exclude_id: idEmpleado || null
        })
            .then(response => {
                if (response.exists) {
                    Toast.fire({
                        icon: "error",
                        title: "Este DNI ya está registrado en el sistema"
                    });
                    document.getElementById('dni').value = '';
                    document.getElementById('dni').focus();
                }
            })
            .catch(error => {
                console.error('Error al validar DNI:', error);
            });
    }

    function validarLegajoExistente(legajo) {
        if (!legajo || legajo.trim() === '') return;

        const idEmpleado = document.getElementById('idEmpleado')?.value;

        apiLaravel('/personal/check-legajo', 'POST', {
            legajo: legajo,
            exclude_id: idEmpleado || null
        })
            .then(response => {
                if (response.exists) {
                    Toast.fire({
                        icon: "error",
                        title: "Este Legajo ya está registrado en el sistema"
                    });
                    document.getElementById('legajo').value = '';
                    document.getElementById('legajo').focus();
                }
            })
            .catch(error => {
                console.error('Error al validar Legajo:', error);
            });
    }

    // Variables de paginación
    const paginacionContenedor = document.getElementById('paginacion-contenedor');
    let paginacion;

    // Contenedores de secciones
    const seccionListado = document.getElementById('seccion-listado');
    const seccionFormulario = document.getElementById('seccion-formulario');



    // Cargar selectores iniciales
    const selectoresPromise = cargarSelectoresIniciales();

    // Inicializar componentes UI
    initUI();

    // Agregar event listeners
    setupEventListeners();

    // Cargar datos iniciales
    if (document.getElementById('formFiltros')) {
        cargarPersonal(1);
    }

    // Verificar si hay parámetro de edición en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const editarId = urlParams.get('editar');
    if (editarId && !isNaN(editarId)) {
        // Esperar a que se carguen los selectores antes de editar
        selectoresPromise.then(() => {
            setTimeout(() => {
                editarPersonal(editarId);
            }, 300);
        }).catch(error => {
            console.error('Error cargando selectores para edición:', error);
            // Intentar editar de todos modos
            setTimeout(() => {
                editarPersonal(editarId);
            }, 500);
        });
    }

    // ========================================
    // FUNCIONES DE INICIALIZACIÓN
    // ========================================

    /**
     * Cargar todos los selectores iniciales desde el backend
     */
    function cargarSelectoresIniciales() {
        return apiLaravel('/personal/selectores-iniciales', 'GET')
            .then(response => {
                const data = response.data;

                // Cargar provincias
                let provinciaOptions = '<option value="">- Seleccionar -</option>';
                data.provincias.forEach(prov => {
                    provinciaOptions += `<option value="${prov.IdProvincia}" ${prov.IdProvincia == 14 ? 'selected' : ''}>${prov.Provincia}</option>`;
                });
                $('#provincia').html(provinciaOptions);

                // Cargar estados civiles
                let estadoCivilOptions = '<option value="">- Seleccionar -</option>';
                data.estados_civiles.forEach(ec => {
                    estadoCivilOptions += `<option value="${ec.idEstadoCivil}">${ec.EstadoCivil}</option>`;
                });
                $('#estado_civil').html(estadoCivilOptions);

                // Cargar países/nacionalidades
                let nacionalidadOptions = '<option value="">- Seleccionar -</option>';
                data.paises.forEach(pais => {

                    nacionalidadOptions += `<option value="${pais.IdPais}" ${pais.IdPais == 80 ? 'selected' : ''}>${pais.Pais}</option>`;
                });
                $('#nacionalidad').html(nacionalidadOptions);

                // Cargar instrucciones
                let instruccionOptions = '<option value="">- Seleccionar -</option>';
                data.instrucciones.forEach(inst => {
                    instruccionOptions += `<option value="${inst.idInstruccion}">${inst.instruccion}</option>`;
                });
                $('#instruccion').html(instruccionOptions);

                // Cargar tipos de relación (para historial)
                let relacionOptions = '<option value="">- Seleccionar -</option>';
                data.tipos_relacion.forEach(rel => {
                    relacionOptions += `<option value="${rel.idRelacion}">${rel.Relacion}</option>`;
                });
                $('#relacion').html(relacionOptions);

                // Cargar tipos de jornada
                let jornadaOptions = '<option value="">- Seleccionar -</option>';
                data.tipos_jornada.forEach(jor => {
                    jornadaOptions += `<option value="${jor.IdTipoJornada}">${jor.Jornada}</option>`;
                });
                $('#tipo_jornada').html(jornadaOptions);

                // Cargar motivos de baja
                let motivoBajaOptions = '<option value="">- Seleccionar -</option>';
                data.motivos_baja.forEach(mb => {
                    motivoBajaOptions += `<option value="${mb.IdMotivoBaja}">${mb.MotivoBaja}</option>`;
                });
                $('#motivo_baja').html(motivoBajaOptions);

                // Cargar instrucciones para "Tipo de Tarea"
                let tipoTareaOptions = '<option value="">- Seleccionar -</option>';
                data.instrucciones.forEach(inst => {
                    tipoTareaOptions += `<option value="${inst.idInstruccion}">${inst.instruccion}</option>`;
                });
                $('#tipo_tarea').html(tipoTareaOptions);

                // Cargar empleados con cargo para "Certifica"
                let certificaOptions = '<option value="">- Seleccionar -</option>';
                data.empleados_con_cargo.forEach(emp => {
                    certificaOptions += `<option value="${emp.idEmpleado}">${emp.Apellido}, ${emp.Nombre} (${emp.Legajo})</option>`;
                });
                $('#certifica').html(certificaOptions);

                // Cargar agrupamientos
                let agrupamientoOptions = '<option value="">- Seleccionar -</option>';
                data.agrupamientos.forEach(agr => {
                    agrupamientoOptions += `<option value="${agr.idAgrupamiento}">${agr.agrupamiento}</option>`;
                });
                $('#agrupamiento').html(agrupamientoOptions);

                // Cargar categorías
                let categoriaOptions = '<option value="">- Seleccionar -</option>';
                data.categorias.forEach(cat => {
                    categoriaOptions += `<option value="${cat.idcategoria}">${cat.categoria}</option>`;
                });
                $('#categoria').html(categoriaOptions);

                // Cargar cargos - OCULTO: Solo se gestiona desde el organigrama
                // let cargoOptions = '<option value="">- Seleccionar -</option>';
                // data.cargos.forEach(cargo => {
                //     cargoOptions += `<option value="${cargo.idCargo}">${cargo.cargo}</option>`;
                // });
                // $('#cargo').html(cargoOptions);

                selectoresIniciales = data; // Guardar para uso posterior
            })
            .catch(error => {
                console.error('Error cargando selectores iniciales:', error);
            });
    }

    /**
     * Inicializar componentes de la interfaz
     */
    function initUI() {
        // Inicializar Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',

            });
        }

        // Inicializar DateTimePickers con Tempus Dominus 6 (Bootstrap 5)
        if (typeof tempusDominus !== 'undefined') {
            // Configurar idioma
            tempusDominus.DefaultOptions.localization.locale = 'es';

            // Inicializar los date pickers
            ['#fecha_nacimiento_picker', '#fecha_alta_picker', '#fecha_adm_publica_picker', '#fecha_baja_picker', '#fecha_movimiento_picker', '#f_jornada_picker'].forEach(selector => {
                const element = document.querySelector(selector);
                if (element) {
                    // Check for invalid values in input before initializing
                    const input = element.querySelector('input');
                    if (input && input.value) {
                       // Simple validation: must contain '/'
                       if (!input.value.includes('/')) {
                           console.warn(`Saneamiento: Limpiando valor inválido "${input.value}" en ${selector}`);
                           input.value = '';
                       }
                    }

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

            $('#fecha_nacimiento_picker, #fecha_alta_picker, #fecha_adm_publica_picker, #fecha_baja_picker, #f_jornada_picker').datetimepicker({
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
                    clear: 'fas fa-trash',
                    close: 'fas fa-times'
                }
            });
        } else if (typeof flatpickr !== 'undefined') {
            // Fallback usando Flatpickr si está disponible
            flatpickr('#fecha_nacimiento, #fecha_alta, #fecha_adm_publica, #fecha_baja, #f_jornada', {
                locale: 'es',
                dateFormat: "d/m/Y",
                allowInput: true
            });
        } else {
            // Fallback: inputs de texto con formato dd/mm/yyyy
            console.log('DatePicker: usando inputs de texto con formato dd/mm/yyyy');
            $('#fecha_nacimiento, #fecha_alta, #fecha_adm_publica, #fecha_baja, #fecha_movimiento, #f_jornada').attr('placeholder', 'dd/mm/yyyy');
            // Agregar evento para formatear entrada
            $('#fecha_nacimiento, #fecha_alta, #fecha_adm_publica, #fecha_baja, #fecha_movimiento, #f_jornada').on('blur', function() {
                let val = $(this).val();
                // Reemplazar guiones por barras
                if (val.includes('-')) {
                    val = val.replace(/-/g, '/');
                    $(this).val(val);
                }
            });
        }


        // Inicializar validación de formulario
        if (typeof $.fn.validate !== 'undefined') {
            $('#formPersonal').validate({
                ignore: [],
                rules: {
                    legajo: {
                        required: true,
                        number: true,
                        remote: {
                            url: '/personal/check-legajo',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                legajo: function () {
                                    return $('#legajo').val();
                                },
                                exclude_id: function () {
                                    return $('#idEmpleado').val() || null;
                                }
                            },
                            dataFilter: function (data) {
                                var json = JSON.parse(data);
                                return json.exists ? '"El legajo ya existe"' : 'true';
                            }
                        }
                    },
                    apellido: {
                        required: true,
                        maxlength: 50
                    },
                    nombre: {
                        required: true,
                        maxlength: 50
                    },
                    dni: {
                        required: true,
                        number: true,
                        minlength: 7,
                        maxlength: 8,
                        remote: {
                            url: '/personal/check-dni',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                dni: function () {
                                    return $('#dni').val();
                                },
                                exclude_id: function () {
                                    return $('#idEmpleado').val() || null;
                                }
                            },
                            dataFilter: function (data) {
                                var json = JSON.parse(data);
                                return json.exists ? '"El DNI ya existe"' : 'true';
                            }
                        }
                    },
                    sexo: 'required',
                    email: {
                        email: true
                    },
                    // Datos laborales obligatorios
                    tipo_tarea: 'required',
                    relacion: 'required',
                    profesion: 'required',
                    categoria: 'required',
                    agrupamiento: 'required',
                    // cargo: 'required', // OCULTO: Solo se gestiona desde el organigrama
                    certifica: 'required',
                    // Jerarquía organizacional obligatoria
                    gerencia: 'required',
                    departamento: 'required',
                    'servicios[]': 'required',
                    // Fechas obligatorias
                    fecha_alta: 'required',
                    fecha_nacimiento: 'required',
                    fecha_adm_publica: 'required',
                    // Campos de baja (solo requeridos si están visibles)
                    fecha_baja: {
                        required: function() {
                            return $('#fecha_baja_group').is(':visible');
                        }
                    },
                    motivo_baja: {
                        required: function() {
                            return $('#motivo_baja_group').is(':visible');
                        }
                    },
                    // Jornada
                    tipo_jornada: 'required',
                    f_jornada: 'required'
                },
                messages: {
                    legajo: {
                        required: 'El legajo es requerido',
                        number: 'Debe ser un número válido'
                    },
                    apellido: {
                        required: 'El apellido es requerido',
                        maxlength: 'Máximo 50 caracteres'
                    },
                    nombre: {
                        required: 'El nombre es requerido',
                        maxlength: 'Máximo 50 caracteres'
                    },
                    dni: {
                        required: 'El DNI es requerido',
                        number: 'Debe ser un número válido',
                        minlength: 'Mínimo 7 dígitos',
                        maxlength: 'Máximo 8 dígitos'
                    },
                    sexo: 'Seleccione el sexo',
                    email: 'Ingrese un email válido',
                    // Mensajes para datos laborales
                    tipo_tarea: 'Seleccione el tipo de tarea',
                    relacion: 'Seleccione la relación laboral',
                    profesion: 'Seleccione la profesión',
                    categoria: 'Seleccione la categoría',
                    agrupamiento: 'Seleccione el agrupamiento',
                    // cargo: 'Seleccione el cargo', // OCULTO: Solo se gestiona desde el organigrama
                    certifica: 'Seleccione quién certifica',
                    // Mensajes para jerarquía organizacional
                    gerencia: 'Seleccione la gerencia',
                    departamento: 'Seleccione el departamento',
                    'servicios[]': 'Seleccione al menos un servicio',
                    // Mensajes para fechas
                    fecha_alta: 'Ingrese la fecha de alta',
                    fecha_nacimiento: 'Ingrese la fecha de nacimiento',
                    fecha_adm_publica: 'Ingrese la fecha de admisión pública',
                    // Campos de baja
                    fecha_baja: 'Ingrese la fecha de baja',
                    motivo_baja: 'Seleccione el motivo de baja',
                    // Jornada
                    tipo_jornada: 'Seleccione el tipo de jornada',
                    f_jornada: 'Ingrese la fecha de jornada'
                },
                errorElement: 'span',
                errorClass: 'invalid-feedback',
                errorPlacement: function(error, element) {
                    // Para Select2
                    if (element.hasClass('select2-hidden-accessible')) {
                        error.insertAfter(element.next('.select2-container'));
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                    // Marcar también el contenedor Select2
                    if ($(element).hasClass('select2-hidden-accessible')) {
                        $(element).next('.select2-container').find('.select2-selection').addClass('is-invalid');
                    }
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                    // Limpiar también el contenedor Select2
                    if ($(element).hasClass('select2-hidden-accessible')) {
                        $(element).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                    }
                },
                invalidHandler: function(event, validator) {
                    // Mostrar mensaje de error general
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var message = errors === 1
                            ? 'Hay 1 campo con error. Por favor revíselo.'
                            : 'Hay ' + errors + ' campos con errores. Por favor revíselos.';

                        mostrarMensaje('error', message);

                        // Scroll al primer campo con error
                        var firstError = $(validator.errorList[0].element);
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 500);

                        // Si el campo está en un tab oculto, activar ese tab
                        var tabPane = firstError.closest('.tab-pane');
                        if (tabPane.length && !tabPane.hasClass('show')) {
                            var tabId = tabPane.attr('id');
                            $('button[data-bs-target="#' + tabId + '"]').tab('show');
                        }
                    }
                },
                submitHandler: function (form) {
                    guardarPersonal();
                    return false;
                }
            });
        }

        // Inicializar typeahead para jefes
        if (typeof $.fn.typeahead !== 'undefined' && typeof listJefes !== 'undefined') {
            $('#jefe').typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            }, {
                name: 'jefes',
                source: listJefes,
                display: function (item) {
                    return item.apellido + ', ' + item.nombre;
                },
                templates: {
                    suggestion: function (item) {
                        return '<div><strong>' + item.apellido + ', ' + item.nombre + '</strong> - Legajo: ' + item.legajo + '</div>';
                    }
                }
            }).bind('typeahead:select', function (ev, suggestion) {
                $('#jefe_id').val(suggestion.id);
            });
        }
        Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 10000,
        });
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
                cargarPersonal(1);
            });
        }

        // Event listener para el botón de agregar
        const btnAgregar = document.getElementById('btnAgregar');
        if (btnAgregar) {
            btnAgregar.addEventListener('click', function () {
                modoFormulario = 'crear';
                limpiarFormulario();
                mostrarFormulario();
            });
        }

        // Event listener para los botones de acción en la tabla
        const tablaPersonal = document.getElementById('tabla-personal');
        if (tablaPersonal) {
            tablaPersonal.addEventListener('click', function (e) {
                const btn = e.target.closest('button');
                if (!btn) return;

                const id = btn.dataset.id;
                const action = btn.dataset.action;

                console.log('=== CLICK EN TABLA PERSONAL ===');
                console.log('Botón clickeado:', btn);
                console.log('ID extraído:', id);
                console.log('Acción:', action);
                console.log('Tipo de ID:', typeof id);

                switch (action) {
                    case 'ver':
                        console.log('Llamando verPersonal con ID:', id);
                        verPersonal(id);
                        break;
                    case 'editar':
                        editarPersonal(id);
                        break;
                    case 'eliminar':
                        eliminarPersonal(id);
                        break;
                }
            });
        }

        // Event listener para el botón guardar
        const btnGuardar = document.getElementById('btnGuardar');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function () {
                $('#formPersonal').submit();
            });
        }

        // Event listener para el botón de limpiar
        const btnLimpiar = document.getElementById('btnLimpiar');
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', function () {
                limpiarFormulario();
            });
        }

        // Event listener para el botón de volver
        const btnVolver = document.getElementById('btnVolver');
        if (btnVolver) {
            btnVolver.addEventListener('click', function () {
                mostrarListado();
            });
        }

        // Event listeners para validación de DNI y legajo
        const dniInput = document.getElementById('dni');
        if (dniInput) {
            dniInput.addEventListener('blur', function () {
                validarDniExistente(this.value);
            });
        }

        const legajoInput = document.getElementById('legajo');
        if (legajoInput) {
            legajoInput.addEventListener('blur', function () {
                validarLegajoExistente(this.value);
            });
        }

        // Event listener para el botón de eliminar en el formulario
        const btnEliminar = document.getElementById('btnEliminar');
        if (btnEliminar) {
            btnEliminar.addEventListener('click', function () {
                if (empleadoEditando) {
                    eliminarPersonal(empleadoEditando.idEmpleado);
                }
            });
        }



        // Event listener para el botón de limpiar filtros
        const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
        if (btnLimpiarFiltros) {
            btnLimpiarFiltros.addEventListener('click', function () {
                document.getElementById('formFiltros').reset();
                // Reponer filtro de estado a '1' (activo) por defecto
                const filtroEstado = document.getElementById('filtro_estado');
                if (filtroEstado) {
                    filtroEstado.value = '1';
                }
                cargarPersonal(1);
            });
        }

        // Event listener para el botón de mostrar/ocultar filtros
        const btnToggleFiltros = document.getElementById('btnToggleFiltros');
        if (btnToggleFiltros) {
            btnToggleFiltros.addEventListener('click', function () {
                const contenedorFiltros = document.getElementById('contenedor-filtros');
                const icon = this.querySelector('i');

                if (contenedorFiltros.classList.contains('d-none')) {
                    // Mostrar filtros
                    contenedorFiltros.classList.remove('d-none');
                    this.innerHTML = '<i class="bi bi-chevron-up me-1"></i> Ocultar filtros';
                } else {
                    // Ocultar filtros
                    contenedorFiltros.classList.add('d-none');
                    this.innerHTML = '<i class="bi bi-chevron-down me-1"></i> Mostrar filtros';
                }
            });
        }

        // Event listener para el botón de capturar foto
        const btnCapturar = document.getElementById('btn_capturar');
        if (btnCapturar) {
            btnCapturar.addEventListener('click', function () {
                takeSnapshot();
            });
        }

        // Event listener para cambio de fecha de nacimiento (calcular edad)
        const fechaNacimiento = document.getElementById('fecha_nacimiento');
        if (fechaNacimiento) {
            fechaNacimiento.addEventListener('change', function () {
                calcularEdad();
            });
        }

        // Event listener para click en la imagen de perfil (cargar nueva imagen)
        const imgCrop = document.getElementById('img_crop');
        if (imgCrop) {
            imgCrop.addEventListener('click', function () {
                // Solo permitir cambiar imagen si no estamos en modo solo lectura
                if (modoFormulario !== 'ver') {
                    document.getElementById('foto_file').click();
                }
            });
            // Agregar cursor pointer para indicar que es clickeable
            imgCrop.style.cursor = 'pointer';
        }
        const imgCargada = document.getElementById('img_foto');
        if (imgCargada) {
            imgCargada.addEventListener('click', function () {
                // Solo permitir cambiar imagen si no estamos en modo solo lectura
                if (modoFormulario !== 'ver') {
                    document.getElementById('foto_file').click();
                }
            });
            // Agregar cursor pointer para indicar que es clickeable
            imgCargada.style.cursor = 'pointer';
        }


    }

    // ========================================
    // FUNCIONES DE PAGINACIÓN Y CARGA DE DATOS
    // ========================================

    /**
     * Inicializar o actualizar la paginación
     */
    function initPaginacion(totalRegistros, porPagina, paginaActual) {
        totalRegistros = totalRegistros || 0;
        porPagina = porPagina || 10;
        paginaActual = paginaActual || 1;

        if (paginacion) {
            paginacion.setPaginationData(totalRegistros, porPagina, paginaActual);
        } else if (typeof CustomPagination !== 'undefined') {
            paginacion = new CustomPagination(paginacionContenedor, {
                initialPageSize: porPagina,
                onPageChange: cargarPersonal,
                onPageSizeChange: function() {
                    cargarPersonal(1);
                }
            });
            paginacion.setPaginationData(totalRegistros, porPagina, paginaActual);
        }
    }
    

    /**
     * Cargar datos de personal con AJAX
     */
    function cargarPersonal(pagina = 1) {
        console.log('Cargando personal, página:', pagina);
        // Obtener datos del formulario de filtros
        const formData = new FormData(document.getElementById('formFiltros'));
        const params = new URLSearchParams(formData);
        params.append('pagina', pagina);

        apiLaravel('/personal/filtrar?' + params.toString(), 'GET')
            .then(response => {
                // Estructura esperada del backend: { data: [...], totalRegistros, paginaActual, porPagina, totalPaginas }
                const resp = response;
                const datos = resp.data || [];
                console.log(datos)
                actualizarTabla(datos);

                // Inicializar/actualizar paginación con los valores que devuelve el servidor.
                const total = parseInt(resp.totalRegistros || 0, 10);
                const paginaResp = parseInt(resp.paginaActual || pagina, 10);
                const porPagina = parseInt(resp.porPagina || 10, 10);

                initPaginacion(total, porPagina, paginaResp);
            })
            .catch(error => {
                console.error('Error cargando personal:', error);
                mostrarMensaje('error', 'Error al cargar los datos del personal');
            });
    }

    /**
     * Actualizar la tabla de personal
     */
    function actualizarTabla(datos) {
        const tbody = document.getElementById('tabla-personal');
        console.log(tbody)
        console.log(datos)
        if (!tbody) return;

        if (datos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>';
            return;
        }

        let html = '';
        datos.forEach(empleado => {
            // Construir ruta jerárquica incluyendo todos los servicios activos
            let serviciosTexto = '';

            if (empleado.servicios_activos && empleado.servicios_activos.length > 0) {
                // Mostrar todos los servicios activos
                const servicios = empleado.servicios_activos.map((serv, index) => {
                    let partes = [];
                    if (serv.gerencia && serv.gerencia !== '-') partes.push(serv.gerencia);
                    if (serv.departamento && serv.departamento !== '-') partes.push(serv.departamento);
                    if (serv.servicio && serv.servicio !== '-') partes.push(serv.servicio);
                    return partes.join(' / ');
                }).filter(s => s && s !== '-');

                if (servicios.length > 0) {
                    serviciosTexto = '<div class="d-flex flex-column gap-1">';
                    servicios.forEach(s => {
                        serviciosTexto += `<small class="text-secondary d-block">• ${s}</small>`;
                    });
                    serviciosTexto += '</div>';
                } else {
                    serviciosTexto = '<small class="text-secondary">-</small>';
                }
            } else {
                // Si no hay servicios activos, mostrar la jerarquía tradicional
                let rutaJerarquica = [];
                if (empleado.gerencia && empleado.gerencia.toString().trim() !== '' && empleado.gerencia !== '-') {
                    rutaJerarquica.push(empleado.gerencia);
                }
                const deptValor = (empleado.Dto && empleado.Dto.toString().trim() !== '') ? empleado.Dto : ((empleado.departamento && empleado.departamento.toString().trim() !== '') ? empleado.departamento : null);
                if (deptValor && deptValor !== '-') {
                    rutaJerarquica.push(deptValor);
                }
                if (empleado.servicio && empleado.servicio.toString().trim() !== '' && empleado.servicio !== '-') {
                    rutaJerarquica.push(empleado.servicio);
                }
                if (empleado.sector && empleado.sector.toString().trim() !== '' && empleado.sector !== '-') {
                    rutaJerarquica.push(empleado.sector);
                }
                const jerarquiaTexto = rutaJerarquica.length > 0 ? rutaJerarquica.join(' / ') : '-';
                serviciosTexto = `<small class="text-secondary">${jerarquiaTexto}</small>`;
            }

            // Debug: Verificar el ID del empleado antes de generar botones
            console.log(`Generando botones para empleado:`, {
                idEmpleado: empleado.idEmpleado,
                nombre: empleado.nombre_completo,
                legajo: empleado.legajo,
                dni: empleado.dni
            });

            html += `
                <tr>
                    <td>${empleado.legajo || ''}</td>
                    <td>${empleado.nombre_completo || ''}</td>
                    <td>${empleado.dni || ''}</td>
                    <td>${serviciosTexto}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            ${permisos.leer ? `<button type="button" class="btn btn-info btn-sm" data-id="${empleado.idEmpleado}" data-action="ver" title="Ver" onclick="console.log('Click Ver ID:', ${empleado.idEmpleado})"><i class="bi bi-eye"></i></button>` : ''}
                            ${permisos.leer ? `<button type="button" class="btn btn-success btn-sm" data-id="${empleado.idEmpleado}" data-action="gestion-hijos" title="Gestionar Hijos" onclick="gestionarHijos(${empleado.idEmpleado})"><i class="bi bi-person-arms-up"></i></button>` : ''}
                            ${permisos.editar ? `<button type="button" class="btn btn-warning btn-sm" data-id="${empleado.idEmpleado}" data-action="editar" title="Editar"><i class="bi bi-pencil"></i></button>` : ''}
                            ${permisos.eliminar ? `<button type="button" class="btn btn-danger btn-sm" data-id="${empleado.idEmpleado}" data-action="eliminar" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // ========================================
    // FUNCIONES DE MOSTRAR/OCULTAR SECCIONES
    // ========================================

    /**
     * Mostrar el formulario y ocultar el listado
     */
    function mostrarFormulario() {
        seccionListado.classList.add('d-none');
        seccionFormulario.classList.remove('d-none');
        configurarFormulario();
    }

    /**
     * Mostrar el listado y ocultar el formulario
     */
    function mostrarListado() {
        seccionFormulario.classList.add('d-none');
        seccionListado.classList.remove('d-none');
        cargarPersonal(1);
    }

    // ========================================
    // FUNCIONES DEL FORMULARIO DE PERSONAL
    // ========================================

    /**
     * Configurar el formulario según el modo
     */
    function configurarFormulario() {
        const titulo = document.getElementById('titulo-formulario');
        const btnGuardar = document.getElementById('btnGuardar');
        const btnEliminar = document.getElementById('btnEliminar');
        const btnLimpiar = document.getElementById('btnLimpiar');

        // Configurar título y botones según el modo
        switch (modoFormulario) {
            case 'crear':
                titulo.textContent = 'Agregar Personal';
                if (btnGuardar) btnGuardar.style.display = permisos.crear ? 'inline-block' : 'none';
                if (btnEliminar) btnEliminar.style.display = 'none';
                if (btnLimpiar) btnLimpiar.style.display = 'inline-block';
                habilitarFormulario(true);
                // Asegurar que el campo legajo esté habilitado en creación
                if (document.getElementById('legajo')) document.getElementById('legajo').disabled = false;
                if (document.getElementById('btnActLegajo')) {
                    document.getElementById('btnActLegajo').classList.remove('btn-outline-success');
                    document.getElementById('btnActLegajo').classList.add('btn-outline-secondary');
                    document.getElementById('btnActLegajo').innerHTML = '<i class="fas fa-lock"></i>';
                }
                break;
            case 'editar':
                titulo.textContent = 'Editar Personal';
                if (btnGuardar) btnGuardar.style.display = permisos.editar ? 'inline-block' : 'none';
                if (btnEliminar) btnEliminar.style.display = permisos.eliminar ? 'inline-block' : 'none';
                if (btnLimpiar) btnLimpiar.style.display = 'none';
                habilitarFormulario(true);
                // En edición, bloquear el campo legajo por defecto
                if (document.getElementById('legajo')) document.getElementById('legajo').disabled = true;
                if (document.getElementById('btnActLegajo')) {
                    document.getElementById('btnActLegajo').classList.remove('btn-outline-success');
                    document.getElementById('btnActLegajo').classList.add('btn-outline-secondary');
                    document.getElementById('btnActLegajo').innerHTML = '<i class="fas fa-lock"></i>';
                }
                break;
            case 'ver':
                titulo.textContent = 'Ver Personal';
                if (btnGuardar) btnGuardar.style.display = 'none';
                if (btnEliminar) btnEliminar.style.display = 'none';
                if (btnLimpiar) btnLimpiar.style.display = 'none';
                habilitarFormulario(false);
                // En visualización, bloquear legajo
                if (document.getElementById('legajo')) document.getElementById('legajo').disabled = true;
                if (document.getElementById('btnActLegajo')) {
                    document.getElementById('btnActLegajo').classList.remove('btn-outline-success');
                    document.getElementById('btnActLegajo').classList.add('btn-outline-secondary');
                    document.getElementById('btnActLegajo').innerHTML = '<i class="fas fa-lock"></i>';
                }
                break;
        }
    }

    /**
     * Habilitar o deshabilitar campos del formulario
     */
    function habilitarFormulario(habilitar) {
        const form = document.getElementById('formPersonal');
        if (!form) return;

        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (habilitar) {
                input.removeAttribute('disabled');
                input.removeAttribute('readonly');
            } else {
                input.setAttribute('readonly', 'true');
                if (input.tagName === 'SELECT') {
                    input.setAttribute('disabled', 'true');
                }
            }
        });

        // Deshabilitar botones especiales en modo solo lectura
        if (!habilitar) {
            const botonesEspeciales = form.querySelectorAll('button[onclick], .btn');
            botonesEspeciales.forEach(btn => {
                if (!btn.id || btn.id !== 'btnVolver') {
                    btn.style.display = 'none';
                }
            });
        }
    }

    /**
     * Limpiar el formulario
     */
    function limpiarFormulario() {
        const form = document.getElementById('formPersonal');
        if (form) {
            form.reset();
            $('#idEmpleado').val('');
            $('#cuil').val('');


            // Limpiar Select2
            $('.select2').val(null).trigger('change');

            // Limpiar contenedores dinámicos
            $('#container_relaciones').empty();
            $('#container_doc').empty();

            // Resetear contadores
            num_relaciones = 0;
            num_doc = 0;
            arrImgsDel = [];
            del_jor = [];
            JornadaOriginal_Id = 0;
            FechaJornadaOri = "";
            // Resetear imagen
            resetearImagen();

            // Resetear servicios
            serviciosAsignados = [];
            serviciosParaDarBaja = [];
            contadorServicios = 0;

            // Ocultar campos condicionales
            $('#fecha_baja_group').hide();
            $('#motivo_baja_group').hide();
            $('#des_baja_group').hide();


            // Resetear checkboxes a valores por defecto
            $('#f_doble').prop('checked', true); // Checked por defecto
            $('#fe').prop('checked', false);


            $('#nacionalidad').val(80).trigger("change");
            $('#provincia').val(14).trigger("change");
            $('#cp').val(3300);


            $('#estado').val(1);
            empleadoEditando = null;
            eliminarFotoFlag = false
        }
    }

    /**
     * Ver personal (solo lectura) - Redirige a la nueva vista elegante
     */
    function verPersonal(id) {
        console.log('=== FUNCIÓN verPersonal ===');
        console.log('ID recibido:', id);
        console.log('Tipo de ID:', typeof id);
        console.log('ID como número:', Number(id));
        console.log('ID es válido:', !isNaN(id) && id != null && id !== '');

        if (!id || isNaN(id)) {
            console.error('❌ ID inválido para verPersonal:', id);
            alert('Error: ID de empleado inválido: ' + id);
            return;
        }

        const url = `${window.location.origin}/personal/${id}/ver`;
        console.log('URL de redirección:', url);
        console.log('Redirigiendo...');

        window.location.href = url;
    }

    /**
     * Editar personal
     */
    function editarPersonal(id) {
        limpiarFormulario();
        modoFormulario = 'editar';
        mostrarFormulario();
        cargarDatosPersonal(id, false);
    }

    /**
     * Generar Formulario de Subsidio Familiar
     */
    function generarFormularioSubsidio(id) {
        // Abrir en una nueva ventana/tab para imprimir como PDF
        const url = `/personal/${id}/hijos/formulario/subsidio-familiar/pdf`;
        window.open(url, '_blank');
    }

    /**
     * Cargar datos de personal para ver o editar
     */
    function cargarDatosPersonal(id, soloLectura) {
        // Resetear la bandera de eliminar foto al cargar datos
        eliminarFotoFlag = false;

        apiLaravel(`/personal/${id}`, 'GET')
            .then(response => {
                console.log('=== RESPONSE FROM /personal/${id} ===', response);
                // La respuesta del controlador tiene formato: { success: true, data: {...} }
                if (!response.success) {
                    mostrarMensaje('error', response.message || 'Error al cargar los datos del personal');
                    return;
                }

                const empleado = response.data;
                console.log('=== EMPLEADO DATA ===', empleado);
                empleadoEditando = empleado;

                // Llenar campos básicos
                $('#idEmpleado').val(empleado.idEmpleado);
                $('#legajo').val(empleado.legajo);
                // Store original legajo value as a data attribute for comparing on update
                $('#legajo').attr('data-original-legajo', empleado.legajo);
                $('#apellido').val(empleado.apellido);
                $('#nombre').val(empleado.nombre);
                $('#dni').val(empleado.dni);
                $('#sexo').val(empleado.sexo);
                $('#cuit').val(empleado.cuit);
                $('#fecha_nacimiento').val(empleado.fecha_nacimiento); // Ya viene formateado

                // Calcular edad a partir de la fecha de nacimiento
                calcularEdad();

                $('#telefono').val(empleado.telefono);
                $('#celular').val(empleado.celular);
                $('#email').val(empleado.email);

                // Campos de domicilio
                $('#calle').val(empleado.calle);
                $('#num_calle').val(empleado.calle_num);
                $('#piso').val(empleado.piso);
                $('#dto').val(empleado.departamento_dir);
                $('#cp').val(empleado.cp);
                $('#barrio').val(empleado.barrio);

                // Campos adicionales de domicilio si existen
                if ($('#manzana').length) $('#manzana').val(empleado.manzana);
                if ($('#casa').length) $('#casa').val(empleado.casa);                // Campos laborales
                $('#fecha_alta').val(empleado.fecha_alta);
                $('#fecha_baja').val(empleado.fecha_baja);
                if ($('#fecha_adm_publica').length) $('#fecha_adm_publica').val(empleado.fecha_adm_publica);
                if ($('#observacion').length) $('#observacion').val(empleado.observacion);
                if ($('#des_baja').length) $('#des_baja').val(empleado.descripcion_baja);
                if ($('#matricula').length) $('#matricula').val(empleado.matricula);
                if ($('#num_matricula').length) $('#num_matricula').val(empleado.matricula);
                if ($('#nro_contrato').length) $('#nro_contrato').val(empleado.nro_contrato);
                if ($('#categoria').length) $('#categoria').val(empleado.categoria_id ?? '').trigger("change");
                if ($('#agrupamiento').length) $('#agrupamiento').val(empleado.agrupamiento_id ?? '').trigger("change");

                // Cargo - OCULTO: Solo se gestiona desde el organigrama
                // El campo hidden enviará vacío (null/0) al guardar
                // const cargoId = empleado.cargo_id ?? 0;
                // if ($('#cargo').length) {
                //     // Si cargo_id es 0, asegurarse de que existe una opción con value="0"
                //     if (cargoId === 0 && $('#cargo option[value="0"]').length === 0) {
                //         // Agregar opción "Sin cargo" con value="0"
                //         $('#cargo').append('<option value="0">Sin cargo</option>');
                //     }
                //     $('#cargo').val(cargoId).trigger("change");
                // }

                // Certifica (cargar el valor del certificador)
                if ($('#certifica').length) {
                    $('#certifica').val(empleado.certifica_id ?? '').trigger("change");
                }


                // Checkboxes
                if ($('#f_doble').length) $('#f_doble').prop('checked', empleado.doble_fs == 1);

                if ($('#fe').length) $('#fe').prop('checked', empleado.fe == 1);


                // Selects
                $('#nacionalidad').val(empleado.nacionalidad_id).trigger('change');
                $('#estado_civil').val(empleado.estado_civil_id).trigger('change');
                $('#provincia').val(empleado.provincia_id).trigger('change');
                $('#profesion').val(empleado.profesion_id).trigger('change');
                $('#funcion').val(empleado.funcion_id).trigger('change');
                $('#idClasificacion').val(empleado.idClasificacion).trigger('change');
                $('#instruccion').val(empleado.instruccion_id).trigger('change');
                $('#gerencia').val(empleado.gerencia_id).trigger('change');
                $('#estado').val(empleado.estado).trigger('change');
                // Cargar jornadas - usar la última jornada si existe
                if (empleado.jornadas && empleado.jornadas.length != 0) {
                    $("#tipo_jornada").val(empleado.jornadas[0].jornada_id).trigger('change');
                    $("#f_jornada").val(empleado.jornadas[0].fecha);
                    JornadaOriginal_Id = empleado.jornadas[0].jornada_id || 0;
                    FechaJornadaOri = empleado.jornadas[0].fechaSinFormato
                } else {
                    $("#f_jornada").val("");
                    $("#tipo_jornada").val("").trigger('change');
                    JornadaOriginal_Id = 0;
                    FechaJornadaOri = ""
                }
                $('#motivo_baja').val(empleado.motivo_baja_id).trigger('change');
                // Nuevos selects - usar IDs correctos
                if ($('#relacion').length) $('#relacion').val(empleado.tipo_relacion_id).trigger('change');
                if ($('#tipo_tarea').length) $('#tipo_tarea').val(empleado.tipo_tarea_id).trigger('change');

                // Cargar localidades si hay provincia
                if (empleado.provincia) {
                    setTimeout(() => {
                        getLocalidades(empleado.localidad);
                    }, 500);
                }

                cargandoDatos = true;
                CargaSelectDto()
                    .then(() => {
                        $('#departamento').val(empleado.departamento_id).trigger('change');
                        return CargaSelectServ();
                    })
                    .then(async () => {
                        // Cargar servicios asignados desde el backend
                        serviciosAsignados = [];

                        console.log('Datos de empleado recibidos:', empleado);
                        console.log('servicios_asignados:', empleado.servicios_asignados);
                        console.log('historial_servicios:', empleado.historial_servicios);

                        // Intentar cargar desde servicios_asignados o historial_servicios (activos)
                        let serviciosParaCargar = [];

                        if (empleado.servicios_asignados && empleado.servicios_asignados.length > 0) {
                            serviciosParaCargar = empleado.servicios_asignados;
                        } else if (empleado.historial_servicios && empleado.historial_servicios.length > 0) {
                            // Usar solo servicios activos del historial
                            serviciosParaCargar = empleado.historial_servicios
                                .filter(h => h.activo == 1 || h.activo == '1')
                                .map(h => ({
                                    servicio_id: h.id || h.servicio_id,
                                    certificador_id: empleado.certifica_id || null,
                                    fecha_pase: h.fecha_inicio || '',
                                    sector_id: empleado.sector_id || null
                                }));
                        }

                        console.log('Servicios a cargar:', serviciosParaCargar);

                        if (serviciosParaCargar.length > 0) {
                            for (const sa of serviciosParaCargar) {
                                const servicioId = sa.servicio_id;

                                if (!servicioId) {
                                    console.warn('Servicio sin ID, omitiendo:', sa);
                                    continue;
                                }

                                // Obtener nombre del servicio
                                const nombreServicio = sa.nombre || $(`#servicio option[value="${servicioId}"]`).text() || `Servicio ${servicioId}`;

                                console.log(`Cargando servicio: ${nombreServicio} (ID: ${servicioId}) - Certificador: ${sa.certificador_id}`);

                                serviciosAsignados.push({
                                    id: contadorServicios++,
                                    servicio_id: servicioId.toString(),
                                    nombre_servicio: nombreServicio,
                                    certificador_id: sa.certificador_id || '',
                                    fecha_pase: sa.fecha_pase || '',
                                    sector_id: sa.sector_id || '',
                                    es_existente: true // Servicios cargados desde la BD
                                });
                            }
                        }

                        console.log('serviciosAsignados final:', serviciosAsignados);

                        // Renderizar servicios asignados
                        await renderizarServiciosAsignados();

                        cargandoDatos = false;
                        // Finalizar carga de datos

                    }).catch(error => {
                        cargandoDatos = false;
                        console.error('Error cargando datos de selects:', error);
                    });

                // Configurar campos condicionales
                if (empleado.estado == 3) { // Baja
                    $('#fecha_baja_group').show();
                    $('#motivo_baja_group').show();
                }

                if (empleado.tipo_jornada && empleado.tipo_jornada > 0) {
                    $('#f_jornada').removeAttr('disabled');
                }

                // Cargar foto si existe
                if (empleado.foto) {
                    // Construir la URL completa a la imagen
                    const fotoUrl = `/storage/empleados/fotos/${empleado.foto}.png`;
                    $('#img_foto').attr('src', fotoUrl);

                    $('#crop_content').show()
                    $('#img_crop').hide()
                    // Mostrar el botón de eliminar foto cuando hay una foto existente
                    $('#btn_eliminar_foto').show();
                } else {
                    $('#img_crop').attr('src', '/img/dummy.png');
                    $('#btn_eliminar_foto').hide();
                }

                // Cargar historial de relaciones
                if (empleado.historial_relaciones && empleado.historial_relaciones.length > 0) {
                    $('#container_relaciones').empty();
                    num_relaciones = 0;

                    empleado.historial_relaciones.forEach(rel => {
                        addHistorialRel();
                        const index = num_relaciones - 1;
                        $(`#relacion_${index}`).val(rel.relacion_id).trigger('change');
                        $(`#desde_rel_${index}`).val(rel.desde);
                        $(`#hasta_rel_${index}`).val(rel.hasta);
                        $(`#obs_rel_${index}`).val(rel.observacion);
                    });
                }

                // Cargar documentos escaneados
                if (empleado.documentos && empleado.documentos.length > 0) {
                    $('#container_doc').empty();
                    num_doc = 0;

                    empleado.documentos.forEach(doc => {
                        addDoc(true);
                        const index = num_doc - 1;
                        $(`#doc_nombre_${index}`).val(doc.nombre);
                        $(`#id_img_${index}`).val(doc.IdDocumento);
                        // Asignar atributo data-id_img al input file correspondiente
                        const $fileInput = $(`#file_${index}`);
                        if ($fileInput.length) {
                            $fileInput.attr('data-id_img', doc.IdDocumento);
                        }
                        if (doc.imagen) {
                            $(`#img_prev_${index}_big`).attr('src', `/storage/empleados/documentos/${doc.imagen}.png`).show();

                        }
                    });
                }

                // Cargar Historial de Servicios
                const tbodyHistorial = document.getElementById('tabla_historial_servicios');
                if (tbodyHistorial) {
                    if (empleado.historial_servicios && empleado.historial_servicios.length > 0) {
                        let htmlHistorial = '';
                        empleado.historial_servicios.forEach(s => {
                            const claseEstado = s.activo ? 'text-success' : 'text-muted';
                            const textoEstado = s.activo ? 'Activo' : 'Inactivo';
                            htmlHistorial += `
                                <tr>
                                    <td class="text-start">${s.nombre}</td>
                                    <td>${s.fecha_inicio}</td>
                                    <td>${s.fecha_fin}</td>
                                    <td class="${claseEstado}"><strong>${textoEstado}</strong></td>
                                    <td class="text-start small">${s.motivo || '-'}</td>
                                </tr>
                            `;
                        });
                        tbodyHistorial.innerHTML = htmlHistorial;
                    } else {
                        tbodyHistorial.innerHTML = '<tr><td colspan="5">Sin historial registrado</td></tr>';
                    }
                }

                mostrarFormulario();
            })
            .catch(error => {
                console.error('Error cargando datos del personal:', error);
                mostrarMensaje('error', 'Error al cargar los datos del personal');
            });
    }

    // Validar formato DD/MM/AAAA para las fechas requeridas antes de convertirlas
    function isValidDDMMYYYY(dateStr) {
        if (!dateStr || typeof dateStr !== 'string') return false;
        // Formato básico dd/mm/yyyy
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dateStr)) return false;
        const parts = dateStr.split('/');
        const d = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10);
        const y = parseInt(parts[2], 10);
        const dt = new Date(y, m - 1, d);
        return dt.getFullYear() === y && dt.getMonth() === m - 1 && dt.getDate() === d;
    }
    /**
     * Guardar personal (crear o actualizar)
     */
    async function guardarPersonal() {
        const form = document.getElementById('formPersonal');

        // Crear objeto de datos en lugar de FormData
        const datos = {};

        // Obtener datos básicos del formulario
        const campos = form.querySelectorAll('input, select, textarea');
        campos.forEach(campo => {
            if (campo.name) {
                // Ignorar campos de archivos, se manejarán por separado
                if (campo.type !== 'file' && campo.name !== 'servicios[]') {
                    // Incluir siempre el campo, incluso si está vacío (excepto checkboxes)
                    if (campo.type !== 'checkbox' && campo.type !== 'radio') {
                        datos[campo.name] = campo.value;
                    } else if (campo.checked) {
                        // Para checkboxes, solo incluir si está marcado
                        datos[campo.name] = campo.value;
                    }
                }
            }
        });

        // Convertir sexo de 1/2 a M/F para validación del servidor
        if (datos.sexo) {
            datos.sexo = datos.sexo === '1' ? 'M' : 'F';
        }

        // Agregar servicios asignados con toda su información
        datos.servicios_asignados = serviciosAsignados.map(s => ({
            servicio_id: s.servicio_id,
            certificador_id: s.certificador_id || null,
            fecha_pase: s.fecha_pase ? formatDate(s.fecha_pase) : null,
            sector_id: s.sector_id || null
        }));

        // Si hay servicios asignados, usar el certificador del primer servicio como certifica principal
        // Esto es para compatibilidad con el backend que requiere certifica
        if (datos.servicios_asignados && datos.servicios_asignados.length > 0) {
            const primerCertificador = datos.servicios_asignados[0].certificador_id;
            if (primerCertificador) {
                datos.certifica = primerCertificador;
            }
        }

        // Agregar servicios a dar de baja (solo en modo editar)
        if (modoFormulario === 'editar' && serviciosParaDarBaja.length > 0) {
            datos.servicios_dar_baja = serviciosParaDarBaja;
        }

        // Si estamos editando y el legajo cambió, incluir el legajo original
        if (modoFormulario === 'editar') {
            const legajoInput = document.getElementById('legajo');
            const legajoOriginal = legajoInput.getAttribute('data-original-legajo');

            if (legajoInput.value !== legajoOriginal) {
                datos.legajoOriginal = legajoOriginal;
            }
        }

        // Convertir todas las fechas al formato YYYY-MM-DD


        const fechaCampos = {
            fecha_nacimiento: 'fecha de nacimiento',
            fecha_alta: 'fecha de alta',
            fecha_baja: 'fecha de baja',
            fecha_adm_publica: 'fecha de admisión pública',
            f_jornada: 'fecha de jornada'
        };

        // Comprobar y convertir cada fecha; si alguna no cumple el formato, mostrar error y detener
        for (const campo in fechaCampos) {
            if (Object.prototype.hasOwnProperty.call(datos, campo) && datos[campo]) {
                if (!isValidDDMMYYYY(datos[campo])) {
                    mostrarMensaje('error', `La ${fechaCampos[campo]} debe tener formato DD/MM/AAAA`);
                    return; // Detener la ejecución de guardarPersonal
                }
                // Si es válida, convertir a YYYY-MM-DD
                datos[campo] = formatDate(datos[campo]);
            }
        }
        datos.JornadaOriginal_Id = JornadaOriginal_Id
        datos.FechaJornadaOri = FechaJornadaOri

        // Convertir fechas en historial de relaciones
        datos.relaciones = [];
        for (let i = 0; i < num_relaciones; i++) {
            const relacion = $(`#relacion_${i}`).val();
            let desde = $(`#desde_rel_${i}`).val();
            let hasta = $(`#hasta_rel_${i}`).val();
            const obs = $(`#obs_rel_${i}`).val();

            if (relacion) {
                // Si hay fecha, validar formato DD/MM/YYYY antes de convertir
                if (desde && !isValidDDMMYYYY(desde)) {
                    mostrarMensaje('error', `La fecha "Desde" en la relación ${i + 1} debe tener formato DD/MM/AAAA`);
                    return; // Detener la ejecución de guardarPersonal
                }
                if (hasta && !isValidDDMMYYYY(hasta)) {
                    mostrarMensaje('error', `La fecha "Hasta" en la relación ${i + 1} debe tener formato DD/MM/AAAA`);
                    return; // Detener la ejecución de guardarPersonal
                }

                // Convertir fechas a formato YYYY-MM-DD (si existen)
                desde = desde ? formatDate(desde) : '';
                hasta = hasta ? formatDate(hasta) : '';

                datos.relaciones.push({
                    relacion_id: relacion,
                    desde: desde,
                    hasta: hasta,
                    observacion: obs
                });
            }
        }
        // Agregar estados de checkbox (sin usar formData.has)
        datos.f_doble = $('#f_doble').is(':checked') ? 1 : 0;
        datos.fe = $('#fe').is(':checked') ? 1 : 0;



        // Agregar foto de perfil
        // Prioridad: 1. Input hidden de recorte (nueva) 2. Imagen recortada mostrada 3. Imagen original (si no hubo cambios)
        const fotoBase64 = $('#foto_base64').val();
        if (fotoBase64) {
             datos.foto = fotoBase64;
        } else {
             // Fallback a lo que se esté mostrando
             if ($('#img_crop').is(':visible')) {
                 const cropSrc = $('#img_crop').attr('src');
                 if (cropSrc && cropSrc.length > 200) { // Check if it's base64
                     datos.foto = cropSrc;
                 }
             } else if ($('#img_foto').length > 0) {
                 const imgSrc = $('#img_foto').attr('src');
                 if (imgSrc) {
                     datos.foto = imgSrc;
                 }
             }
        }

        // Agregar indicador separado para eliminación de foto (usando booleano simple)
        datos.eliminar_foto = eliminarFotoFlag;        // Agregar datos de documentos como array de objetos con imágenes en base64
        datos.documentos = [];

        // Procesar archivos de forma asíncrona para convertirlos a base64

        for (let i = 0; i < num_doc; i++) {
            const nombre = $(`#doc_nombre_${i}`).val();


            if ($(`#doc_nombre_${i}`).length != 0) {
                const doc = {
                    nombre: nombre,
                };

                // Si hay una imagen previa ya cargada pero no hay nuevo archivo
                const imgSrc = $(`#img_prev_${i}_big`).attr('src');

                doc.imagen_data = imgSrc;

                datos.documentos.push(doc);
            }
        }

        // Agregar jornadas eliminadas como array
        if (del_jor.length > 0) {
            datos.jornadas_eliminadas = del_jor;
        }

        // Agregar imágenes eliminadas como array
        if (arrImgsDel.length > 0) {
            datos.imagenes_eliminadas = arrImgsDel;
        }

        const url = modoFormulario === 'crear' ? '/personal' : `/personal/${empleadoEditando.idEmpleado}`;
        const method = modoFormulario === 'crear' ? 'POST' : 'PUT';

        // DEBUG: Ver qué datos se envían al servidor
        console.log('=== DATOS A ENVIAR AL SERVIDOR ===');
        console.log('URL:', url, 'Método:', method);
        console.log('certifica:', datos.certifica);
        console.log('servicios_asignados:', datos.servicios_asignados);
        console.log('================================');

        apiLaravel(url, method, datos)
            .then(response => {
                mostrarMensaje('success', response.message || 'Personal guardado correctamente');
                mostrarListado();
            })
            .catch(error => {
                console.error('Error guardando personal:', error);
                mostrarMensaje('error', error);
            });
    }



    /**
   * Eliminar un registro de productividad
   * @param {number} id - IdProductividad del registro
   */
    function eliminarPersonal(id) {
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

                apiLaravel(`/personal/${id}`, 'DELETE')
                    .then(respuesta => {
                        if (respuesta.success) {
                            mostrarMensaje('success', respuesta.message || 'Registro eliminado correctamente');

                            mostrarListado();
                        } else {
                            mostrarMensaje('error', respuesta.message || 'Error al eliminar el registro');
                        }


                    })
                    .catch(error => {

                        mostrarMensaje('error', 'Error al eliminar el registro: ' + error);

                    });
            }
        });
    }


    // ========================================
    // FUNCIONES AUXILIARES
    // ========================================

    /**
     * Mostrar modal de eliminación
     */
    let idEliminar = null;
    function modalEliminar(id = null) {
        idEliminar = id;
        $('#modal_eliminar').modal('show');
    }

    /**
     * Mostrar u ocultar indicador de carga
     */
    // La función mostrarIndicadorCarga fue removida porque apiLaravel
    // ya maneja el indicador de carga de manera centralizada.

    /**
     * Mostrar mensaje de notificación
     */
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

    // ========================================
    // FUNCIONES ESPECÍFICAS DEL MÓDULO
    // ========================================

    /**
     * Cargar el jefe correspondiente según la jerarquía organizacional
     */
    function cargarCertifica() {

        // Determinar el nivel más específico seleccionado
        const sector = $('#sector').val();
        let servicio = $('#servicio').val();
         // Si es array (multiple), usamos el primero si es único, o null si son varios
         if (Array.isArray(servicio)) {
             servicio = servicio.length === 1 ? servicio[0] : "";
         }
        const departamento = $('#departamento').val();
        const gerencia = $('#gerencia').val();

        let nivelId = null;
        let tipoNivel = null;
        let cargoJefe = null;

        // Determinar el nivel más específico con valor
        if (sector && sector !== "") {
            nivelId = sector;
            tipoNivel = 'sector';
            cargoJefe = 1; // Jefe de sector
        } else if (servicio && servicio !== "") {
            nivelId = servicio;
            tipoNivel = 'servicio';
            cargoJefe = 2; // Jefe de servicio
        } else if (departamento && departamento !== "") {
            nivelId = departamento;
            tipoNivel = 'departamento';
            cargoJefe = 3; // Jefe de departamento
        } else if (gerencia && gerencia !== "") {
            nivelId = gerencia;
            tipoNivel = 'gerencia';
            cargoJefe = 4; // Jefe de gerencia
        }



        // Buscar el jefe correspondiente

        apiLaravel(`/personal/buscar-jefe?nivel=${tipoNivel}&nivel_id=${nivelId}&cargo_jefe=${cargoJefe}`, 'GET')
            .then(response => {

                if (response.jefe) {
                    // Buscar en las opciones del select si existe el empleado
                    const jefeId = response.jefe.idEmpleado;
                    $('#certifica').val(jefeId).trigger('change');
                    /*
                    const option = $(`#certifica option[value="${jefeId}"]`);

                    if (option.length > 0) {
                        // Si existe en las opciones, seleccionarlo
                        $('#certifica').val(jefeId).trigger('change');
                    } else {
                        // Si no existe, agregarlo como nueva opción y seleccionarlo
                        const newOption = new Option(
                            `${response.jefe.apellidos}, ${response.jefe.nombres} (${response.jefe.legajo})`,
                            jefeId,
                            true,
                            true
                        );
                        $('#certifica').append(newOption).trigger('change');
                    }*/
                } else {
                    // No hay jefe asignado, limpiar selección
                    $('#certifica').val('').trigger('change');
                }
            })
            .catch(error => {

                console.error('Error cargando jefe:', error);
                $('#certifica').val('').trigger('change');
                mostrarMensaje('error', 'Error cargando jefe:' + error);
            });
    }

    /**
     * Cargar departamentos según gerencia seleccionada
     */
    function CargaSelectDto() {
        const gerenciaId = $('#gerencia').val();
        if (!gerenciaId || gerenciaId == "0") {
            $('#departamento').html('<option value="">- Seleccionar -</option>').trigger('change');
            return Promise.resolve();
        }

        return apiLaravel('/personal/departamentos?gerencia_id=' + gerenciaId, 'GET')
            .then(response => {
                let options = '<option value="">- Seleccionar -</option>';
                response.data.forEach(dept => {
                    options += `<option value="${dept.idDepartamento}">${dept.departamento}</option>`;
                });
                // Destruir Select2 antes de manipular el DOM
                if (typeof $.fn.select2 !== 'undefined') {
                    if ($('#departamento').data('select2')) { try { $('#departamento').select2('destroy'); } catch (e) { } }
                    if ($('#servicio').data('select2')) { try { $('#servicio').select2('destroy'); } catch (e) { } }
                    if ($('#sector').data('select2')) { try { $('#sector').select2('destroy'); } catch (e) { } }
                }

                $('#departamento').html(options);

                // Limpiar selectores dependientes (sin trigger hasta reinit)
                $('#servicio').html('<option value="" selected>- Seleccionar -</option>');
                $('#sector').html('<option value="" selected>- Seleccionar -</option>');

                // Volver a inicializar Select2 en los selects afectados
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#departamento').select2({
                        theme: 'bootstrap-5',
                        width: '100%',

                    });

                    $('#servicio').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: "- Seleccionar -"
                    });
                    $('#sector').select2({
                        theme: 'bootstrap-5',
                        width: '100%',

                    });
                }

                // Cargar jefe correspondiente solo si se solicita
                if (!cargandoDatos) {
                    cargarCertifica();
                }
            })
            .catch(error => {
                console.error('Error cargando departamentos:', error);
            });
    }

    /**
     * Cargar servicios según departamento seleccionado
     */
    function CargaSelectServ() {
        const departamentoId = $('#departamento').val();
        if (!departamentoId || departamentoId == "0") {
            $('#servicio').html('<option value="">- Seleccionar -</option>').trigger('change');
            return Promise.resolve();
        }

        return apiLaravel('/personal/servicios?departamento_id=' + departamentoId, 'GET')
            .then(response => {
                let options = '<option value="">- Seleccionar -</option>';
                response.data.forEach(serv => {
                    options += `<option value="${serv.idServicio}">${serv.servicio}</option>`;
                });
                // Destruir Select2 antes de manipular el DOM
                if (typeof $.fn.select2 !== 'undefined') {
                    if ($('#servicio').data('select2')) { try { $('#servicio').select2('destroy'); } catch (e) { } }
                }

                $('#servicio').html(options);

                // Volver a inicializar Select2 (SIMPLE, no múltiple)
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#servicio').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: "- Seleccionar -"
                    });
                }
            })
            .catch(error => {
                console.error('Error cargando servicios:', error);
            });
    }

    /**
     * Cargar sectores según servicio seleccionado
     */
    function CargaSelectSect() {
        let servicioId = $('#servicio').val();

        // Si es array (multiple), tomamos decisión:
        // Opción A: No cargar sectores (disable) si hay más de 1.
        // Opción B: Tomar el primero para cargar sectores (comportamiento "principal").
        // Comportamiento actual: Validar si es array
        if (Array.isArray(servicioId)) {
             if (servicioId.length === 1) {
                 servicioId = servicioId[0];
             } else {
                 // Si hay múltiples o ninguno, limpiamos sector y salimos
                 $('#sector').html('<option value="">- Seleccionar -</option>').trigger('change');
                 return Promise.resolve();
             }
        }

        if (!servicioId || servicioId == "0") {
            $('#sector').html('<option value="">- Seleccionar -</option>').trigger('change');
            return Promise.resolve();
        }

        return apiLaravel('/personal/sectores?servicio_id=' + servicioId, 'GET')
            .then(response => {
                let options = '<option value="">- Seleccionar -</option>';
                response.data.forEach(sect => {
                    options += `<option value="${sect.IdSector}">${sect.Sector}</option>`;
                });
                // Destruir Select2 antes de manipular el DOM
                if (typeof $.fn.select2 !== 'undefined') {
                    if ($('#sector').data('select2')) { try { $('#sector').select2('destroy'); } catch (e) { } }
                }

                $('#sector').html(options);

                // Volver a inicializar Select2
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#sector').select2({
                        theme: 'bootstrap-5',
                        width: '100%',

                    });
                }

                if (!cargandoDatos) {
                    cargarCertifica();
                }
            })
            .catch(error => {
                console.error('Error cargando sectores:', error);
            });
    }

    /**
     * Funciones para filtros (versiones simplificadas)
     */
    function CargaSelectDtoFiltro() {
        const gerenciaId = $('#filtro_gerencia').val();
        if (!gerenciaId || gerenciaId == "0") {
            // Destruir Select2 antes de manipular el DOM
            if (typeof $.fn.select2 !== 'undefined') {
                if ($('#filtro_departamento').data('select2')) { try { $('#filtro_departamento').select2('destroy'); } catch (e) { } }
                if ($('#filtro_servicio').data('select2')) { try { $('#filtro_servicio').select2('destroy'); } catch (e) { } }
                if ($('#filtro_sector').data('select2')) { try { $('#filtro_sector').select2('destroy'); } catch (e) { } }
            }

            $('#filtro_departamento').html('<option value="0">Todos</option>');
            $('#filtro_servicio').html('<option value="0">Todos</option>');
            $('#filtro_sector').html('<option value="0">Todos</option>');

            // Volver a inicializar Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('#filtro_departamento').select2({ width: '100%', placeholder: 'Todos' });
                $('#filtro_servicio').select2({ width: '100%', placeholder: 'Todos' });
                $('#filtro_sector').select2({ width: '100%', placeholder: 'Todos' });
            }
            return;
        }

        apiLaravel('/personal/departamentos?gerencia_id=' + gerenciaId, 'GET')
            .then(response => {
                // Destruir Select2 antes de manipular el DOM
                if (typeof $.fn.select2 !== 'undefined') {
                    if ($('#filtro_departamento').data('select2')) { try { $('#filtro_departamento').select2('destroy'); } catch (e) { } }
                    if ($('#filtro_servicio').data('select2')) { try { $('#filtro_servicio').select2('destroy'); } catch (e) { } }
                    if ($('#filtro_sector').data('select2')) { try { $('#filtro_sector').select2('destroy'); } catch (e) { } }
                }

                let options = '<option value="0">Todos</option>';
                response.data.forEach(dept => {
                    options += `<option value="${dept.idDepartamento}">${dept.departamento}</option>`;
                });
                $('#filtro_departamento').html(options);
                $('#filtro_servicio').html('<option value="0">Todos</option>');
                $('#filtro_sector').html('<option value="0">Todos</option>');

                // Volver a inicializar Select2
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#filtro_departamento').select2({ width: '100%', placeholder: 'Todos' });
                    $('#filtro_servicio').select2({ width: '100%', placeholder: 'Todos' });
                    $('#filtro_sector').select2({ width: '100%', placeholder: 'Todos' });
                }
            })
            .catch(error => {
                console.error('Error cargando departamentos filtro:', error);
            });
    }

    function CargaSelectServFiltro() {
        const departamentoId = $('#filtro_departamento').val();
        if (!departamentoId || departamentoId == "0") {
            // Destruir Select2 antes de manipular el DOM
            if (typeof $.fn.select2 !== 'undefined') {
                if ($('#filtro_servicio').data('select2')) { try { $('#filtro_servicio').select2('destroy'); } catch (e) { } }
                if ($('#filtro_sector').data('select2')) { try { $('#filtro_sector').select2('destroy'); } catch (e) { } }
            }

            $('#filtro_servicio').html('<option value="0">Todos</option>');
            $('#filtro_sector').html('<option value="0">Todos</option>');

            // Volver a inicializar Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('#filtro_servicio').select2({ width: '100%', placeholder: 'Todos' });
                $('#filtro_sector').select2({ width: '100%', placeholder: 'Todos' });
            }
            return;
        }

        apiLaravel('/personal/servicios?departamento_id=' + departamentoId, 'GET')
            .then(response => {
                // Destruir Select2 antes de manipular el DOM
                if (typeof $.fn.select2 !== 'undefined') {
                    if ($('#filtro_servicio').data('select2')) { try { $('#filtro_servicio').select2('destroy'); } catch (e) { } }
                    if ($('#filtro_sector').data('select2')) { try { $('#filtro_sector').select2('destroy'); } catch (e) { } }
                }

                let options = '<option value="0">Todos</option>';
                response.data.forEach(serv => {
                    options += `<option value="${serv.idServicio}">${serv.servicio}</option>`;
                });
                $('#filtro_servicio').html(options);
                $('#filtro_sector').html('<option value="0">Todos</option>');

                // Volver a inicializar Select2
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#filtro_servicio').select2({ width: '100%', placeholder: 'Todos' });
                    $('#filtro_sector').select2({ width: '100%', placeholder: 'Todos' });
                }
            })
            .catch(error => {
                console.error('Error cargando servicios filtro:', error);
            });
    }

    function CargaSelectSectFiltro() {
        const servicioId = $('#filtro_servicio').val();
        if (!servicioId || servicioId == "0") {
            // Destruir Select2 antes de manipular el DOM
            if (typeof $.fn.select2 !== 'undefined') {
                if ($('#filtro_sector').data('select2')) { try { $('#filtro_sector').select2('destroy'); } catch (e) { } }
            }

            $('#filtro_sector').html('<option value="0">Todos</option>');

            // Volver a inicializar Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('#filtro_sector').select2({ width: '100%', placeholder: 'Todos' });
            }
            return;
        }

        apiLaravel('/personal/sectores?servicio_id=' + servicioId, 'GET')
            .then(response => {
                // Destruir Select2 antes de manipular el DOM
                if (typeof $.fn.select2 !== 'undefined') {
                    if ($('#filtro_sector').data('select2')) { try { $('#filtro_sector').select2('destroy'); } catch (e) { } }
                }

                let options = '<option value="0">Todos</option>';
                response.data.forEach(sect => {
                    options += `<option value="${sect.IdSector}">${sect.Sector}</option>`;
                });
                $('#filtro_sector').html(options);

                // Volver a inicializar Select2
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#filtro_sector').select2({ width: '100%', placeholder: 'Todos' });
                }
            })
            .catch(error => {
                console.error('Error cargando sectores filtro:', error);
            });
    }

    /**
     * Cargar localidades según provincia
     */
    function getLocalidades(localidadId = null) {
        const provinciaId = $('#provincia').val();
        if (!provinciaId || provinciaId == "0") {
            $('#localidad').html('<option value="">- Seleccionar -</option>').trigger('change');
            return;
        }

        apiLaravel('/personal/localidades?provincia_id=' + provinciaId, 'GET')
            .then(response => {
                let options = '<option value="">- Seleccionar -</option>';
                response.data.forEach(loc => {
                    const selected = localidadId && loc.IdLocalidad == localidadId ? 'selected' : '';
                    options += `<option value="${loc.IdLocalidad}" ${((localidadId && loc.IdLocalidad == localidadId) || loc.IdLocalidad == 12108) ? 'selected' : ''}>${loc.Localidad}</option>`;
                });
                $('#localidad').html(options);
                data_local = response.data;
            })
            .catch(error => {
                console.error('Error cargando localidades:', error);
            });
    }

    /**
     * Obtener código postal de la localidad seleccionada
     */
    function getCP() {
        const localidadId = $('#localidad').val();
        if (!localidadId || localidadId == "0") {
            $('#codigo_postal').val('');
            return;
        }

        const localidad = data_local.find(loc => loc.IdLocalidad == localidadId);
        console.log(localidad);
        alert(localidad.CP)
        if (localidad && localidad.CP) {
            $('#codigo_postal').val(localidad.CP);
        }
    }

    /**
     * Cambiar estado del empleado (mostrar campos de baja si corresponde)
     */
    function changeEstado() {
        const estadoId = $('#estado').val();
        if (estadoId == 3) { // Estado de baja
            $('#fecha_baja_group').show();
            $('#motivo_baja_group').show();
            $('#des_baja_group').show();
        } else {
            $('#fecha_baja_group').hide();
            $('#motivo_baja_group').hide();
            $('#des_baja_group').hide();
            $('#fecha_baja').val('');
            $('#motivo_baja').val('').trigger('change');
            $('#des_baja').val('');
        }
    }

    /**
     * Cambiar tipo de jornada (habilitar fecha de jornada)
     */
    function changJornada() {
        const jornadaId = $('#tipo_jornada').val();
        if (jornadaId && jornadaId != "0") {
            $('#f_jornada').removeAttr('disabled');
        } else {
            $('#f_jornada').attr('disabled', true).val('');
        }
    }

    /**
     * Generar CUIT a partir de DNI y sexo
     */
    function getCuit() {
        const dni = $('#dni').val();
        const sexo = $('#sexo').val();

        if (!dni || !sexo) return;

        const cuit = Genera_cuti(sexo, dni);
        if (cuit) {
            $('#cuit').val(cuit);
        }
    }

    /**
     * Función para generar CUIT
     */
    function Genera_cuti(sex, doc) {
        if (!doc || doc.length === 0) return '';

        let primero = sex == 1 ? '20' : '27';
        let verificador = calcularVerificadorCUIT(primero + doc);

        return primero + '-' + doc + '-' + verificador;
    }

    /**
     * Calcular dígito verificador del CUIT
     */
    function calcularVerificadorCUIT(numero) {
        const multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        let suma = 0;

        for (let i = 0; i < numero.length; i++) {
            suma += parseInt(numero[i]) * multiplicadores[i];
        }

        const resto = suma % 11;
        if (resto === 0) return 0;
        if (resto === 1) return 9;
        return 11 - resto;
    }

    /**
     * Calcular edad a partir de fecha de nacimiento
     */
    function calcularEdad() {
        const fechaNac = $('#fecha_nacimiento').val();
        if (!fechaNac) return;

        const partes = fechaNac.split('/');
        if (partes.length !== 3) return;

        const fechaNacimiento = new Date(partes[2], partes[1] - 1, partes[0]);
        const hoy = new Date();

        let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        const mes = hoy.getMonth() - fechaNacimiento.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
            edad--;
        }

        $('#edad').val(edad);
    }


    /**
     * Formatear fecha de DD/MM/YYYY a YYYY-MM-DD
     */
    function formatDate(date) {
        if (!date || date.length === 0) return '';

        if (date.includes('-')) return date; // Ya está en formato correcto

        const partes = date.split('/');
        if (partes.length === 3) {
            return partes[2] + '-' + partes[1] + '-' + partes[0];
        }
        return date;
    }

    /**
     * Agregar historial de relaciones
     */
    function addHistorialRel() {
        const relacionOptions = $('#relacion').html();
        const html = `
            <div class="row" id="rel_${num_relaciones}">
                <div class="form-group col-md-3">
                    <select id="relacion_${num_relaciones}" class="form-control select2-rel" name="relacion_${num_relaciones}">
                        ${relacionOptions}
                    </select>
                </div>
                <div class="form-group col-md-3">

                    <div class="input-group date" id="desde_rel_${num_relaciones}_picker" data-target-input="nearest">
                        <input type="text" name="desde_rel_${num_relaciones}" required id="desde_rel_${num_relaciones}"
                            class="form-control datetimepicker-input" data-target="#desde_rel_${num_relaciones}_picker" />
                        <div class="input-group-append" data-target="#desde_rel_${num_relaciones}_picker"
                            data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">



                     <div class="input-group date" id="hasta_rel_${num_relaciones}_picker" data-target-input="nearest">
                        <input type="text" name="hasta_rel_${num_relaciones}"  id="hasta_rel_${num_relaciones}"
                            class="form-control datetimepicker-input" data-target="#hasta_rel_${num_relaciones}_picker" />
                        <div class="input-group-append" data-target="#hasta_rel_${num_relaciones}_picker"
                            data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <input type="text" id="obs_rel_${num_relaciones}" placeholder="Observación" class="form-control" name="obs_rel_${num_relaciones}" />
                </div>
                <div class="form-group col-md-1">
                    <button class="btn btn-danger" onclick="deleteHisto(${num_relaciones})" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

        $('#container_relaciones').append(html);

        // Inicializar datepickers
        $(`#desde_rel_${num_relaciones}_picker`).datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'es'
        });
        $(`#hasta_rel_${num_relaciones}_picker`).datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'es'
        });

        // Inicializar Select2
        $(`#relacion_${num_relaciones}`).select2({
            theme: 'bootstrap-5',
            width: '100%',

        });

        num_relaciones++;
    }

    /**
     * Eliminar relación del historial
     */
    function deleteHisto(ind) {
        $(`#rel_${ind}`).remove();
    }

    function onBase64Result(error, result, id) {
        try {
            if (error) {
                console.error('Error al convertir a base64:', error);
                mostrarMensaje('error', 'Error al convertir a base64:' + error);
            } else {
                // If we have a document ID, add it to the array of documents to delete
                // since we're replacing it with a new image
                if (typeof id !== 'undefined' && id !== null && id !== '') {
                    const exists = arrImgsDel.find(item => String(item) === String(id));
                    if (!exists) {
                        arrImgsDel.push(id);
                    }
                }
            }
        } catch (e) {
            mostrarMensaje('error', 'onBase64Result error:' + e);
            console.error('onBase64Result error:', e);
        }
    }
    /**
     * Agregar documento escaneado
     */
    function addDoc(edit = false) {
        let html = `<div class="row" id="doc_${num_doc}">`;

        if (!edit) {
            html += `<div class="form-group col-md-2" style="padding-top: 30px;">
                        <input type="file" id="file_${num_doc}" style="display:none;" accept="image/*" onchange="previewDoc(${num_doc})">
                        <button type="button" class="btn btn-primary" onclick="selectFile(${num_doc})">
                            <i class="fas fa-upload"></i> Archivo
                        </button>
                     </div>`;
        } else {
            html += `<div class="form-group col-md-2" style="padding-top: 30px;">
                        <input type="file" id="file_${num_doc}" style="display:none;" accept="image/*"  data-prev="img_prev_${num_doc}_big"  onchange="cargarImg(this, 1000, 1000,onBase64Result)">
                        <input type="hidden" id="id_img_${num_doc}" value="">
                        <button type="button" class="btn btn-primary" onclick="selectFile(${num_doc})">
                            <i class="fas fa-upload"></i> Cambiar
                        </button>
                     </div>`;
        }

        html += `<div class="form-group col-md-7" style="padding-top: 30px;">
                    <input type="text" id="doc_nombre_${num_doc}" placeholder="Nombre del documento" class="form-control" name="doc_nombre_${num_doc}" />
                 </div>
                 <div class="form-group col-md-2" style="padding-top: 30px;">
                    <img style="display:none;" class="img_prev img-thumbnail" id="img_prev_${num_doc}_big" style="width:100px;" src="/img/dummy.png">
                 </div>
                 <div class="form-group col-md-1" style="padding-top: 30px;">`;

        if (!edit) {
            html += `<button class="btn btn-danger" onclick="deleteDoc(${num_doc})" type="button">
                        <i class="fas fa-times"></i>
                     </button>`;
        } else {
            html += `<button class="btn btn-danger" onclick="deleteDocEdit(${num_doc})" type="button">
                        <i class="fas fa-times"></i>
                     </button>`;
        }

        html += `</div></div>`;

        $('#container_doc').append(html);
        num_doc++;
    }

    /**
     * Seleccionar archivo para documento
     */
    function selectFile(ind) {
        $(`#file_${ind}`).trigger('click');
    }

    /**
     * Eliminar documento
     */
    function deleteDoc(ind) {
        $(`#doc_${ind}`).remove();
    }

    /**
     * Eliminar documento en edición
     */
    function deleteDocEdit(ind) {
        const idImg = $(`#id_img_${ind}`).val();
        if (idImg) {
            arrImgsDel.push(idImg);
        }
        $(`#doc_${ind}`).remove();
    }

    /**
     * Vista previa del documento
     */
    function previewDoc(ind) {
        const file = $(`#file_${ind}`)[0].files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                // Mostrar la vista previa
                $(`#img_prev_${ind}_big`).attr('src', e.target.result).show();

                // Almacenar temporalmente la imagen base64 en un atributo data para usarlo después
                $(`#img_prev_${ind}_big`).attr('data-base64', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    }

    /**
     * Ver historial de jornadas
     */
    function verJornadas() {
        if (!empleadoEditando) return;

        // Si ya tenemos las jornadas cargadas en empleadoEditando, usarlas directamente
        if (empleadoEditando.jornadas && empleadoEditando.jornadas.length >= 0) {
            mostrarJornadas(empleadoEditando.jornadas);
            return;
        }

        // Si no, hacer la petición al endpoint específico
        apiLaravel(`/personal/${empleadoEditando.idEmpleado}/jornadas`, 'GET')
            .then(response => {
                const jornadas = response.data;
                mostrarJornadas(jornadas);
            })
            .catch(error => {
                console.error('Error cargando jornadas:', error);
                mostrarMensaje('error', 'Error al cargar el historial de jornadas');
            });
    }

    /**
     * Mostrar las jornadas en el modal
     */
    function mostrarJornadas(jornadas) {
        let html = '';

        if (jornadas.length === 0) {
            html = '<tr><td colspan="3" class="text-center">No hay jornadas registradas</td></tr>';
        } else {
            jornadas.forEach((jornada, index) => {
                html += `
                    <tr>
                        <td>${jornada.jornada_nombre || jornada.jornada}</td>
                        <td>${jornada.fecha}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="eliminarJornadas(${index}, ${jornada.IdJornadaXEmp || jornada.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        $('#tabla_his').html(html);
        $('#histo_modal').modal('show');
    }

    /**
     * Eliminar jornada del historial
     */
    function eliminarJornadas(ind, id) {
        del_jor.push(id);
        $(`#tabla_his tr:eq(${ind})`).remove();
    }

    /**
     * Activar edición del legajo
     */
    /**
     * Activar edición del legajo
     */
    function actLegajo() {
        const legajoInput = document.getElementById('legajo');
        const btn = document.getElementById('btnActLegajo');
        if (!legajoInput || !btn) return;

        if (legajoInput.disabled) {
            // Habilitar edición
            legajoInput.disabled = false;
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-outline-success');
            btn.innerHTML = '<i class="fas fa-lock-open"></i>';
        } else {
            // Deshabilitar edición
            legajoInput.disabled = true;
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-outline-secondary');
            btn.innerHTML = '<i class="fas fa-lock"></i>';
        }
    }

    /**
     * Inicializar cámara para captura de foto
     */
    function initCamera() {
        if (typeof Webcam !== 'undefined') {
            Webcam.set({
                dest_width: 320 * 3,
                dest_height: 240 * 3,
                image_format: 'png',
                constraints: { facingMode: 'environment' }
            });
            Webcam.attach('#my_camera');
            initCameraModal = true;
        }
    }

    /**
     * Capturar foto de la cámara
     */
    function takeSnapshot() {
        if (typeof Webcam !== 'undefined') {
            Webcam.snap(function (dataUri) {
                $('#img_foto').attr('src', dataUri);
                activeCrop();
            });
        }
    }


    /**
     * Activar recorte de imagen
     */
    function activeCrop() {
        if (cropImg != null) {
            cropImg.croppie('destroy');
        }

        $('#btn_eliminar_foto').hide();

        if (typeof $.fn.croppie !== 'undefined') {
            cropImg = $('#img_foto').croppie({
                viewport: { width: 200, height: 200 },
                boundary: { width: 200, height: 200 },
                enableOrientation: true
            });
        }

        $('#img_crop').hide();
        $('#modal_foto').modal('hide');
        $('#btn_recortar').show();
    }

    /**
     * Recortar imagen
     */
    function recortar() {
        if (cropImg && typeof $.fn.croppie !== 'undefined') {
            cropImg.croppie('result', {
                type: 'base64',
                format: 'png',
                quality: 1,
                size: { width: 512, height: 512 }
            }).then(function (base64_result) {
                $('#img_crop').attr('src', base64_result).show();

                $('#btn_recortar').hide();
                $('#btn_eliminar_foto').show();

                // Guardar la imagen en un campo hidden
                if ($('#foto_base64').length === 0) {
                    $('#formPersonal').append('<input type="hidden" id="foto_base64" name="foto">');
                }
                $('#foto_base64').val(base64_result);
            });
        }
    }

    /**
     * Eliminar foto
     */
    /**
     * Eliminar foto
     */
    function eliminarFoto() {
        // Resetear la imagen a la imagen por defecto
        $('#img_crop').attr('src', '/img/dummy.png');

        $('#btn_recortar').hide();
        $('#btn_eliminar_foto').hide();

        $('#crop_content').hide();
        $('#img_crop').show();



        // Marcar la foto para eliminación usando el booleano simple
        eliminarFotoFlag = true;

        if (cropImg) {
            cropImg.croppie('destroy');
            cropImg = null;
        }
    }
    function onBase64ResizeFotoPerfil() {
        $("#crop_content").show()
        $("#img_crop").hide()

    }
    /**
     * Resetear imagen a estado inicial
     */
    function resetearImagen() {
        // Resetear ambas imágenes a la imagen dummy
        $('#img_crop').attr('src', '/img/dummy.png');
        $('#img_foto').attr('src', '/img/dummy.png');
        $('#img_crop').show();
        $('#crop_content').hide();



        $('#btn_recortar').hide();
        $('#btn_eliminar_foto').hide();




        // Resetear la bandera de eliminar foto
        eliminarFotoFlag = false;

        // Reiniciar Croppie si existe
        if (cropImg) {
            cropImg.croppie('destroy');
            cropImg = null;
        }
    }    // Hacer funciones disponibles globalmente para los event handlers inline
    window.CargaSelectDto = CargaSelectDto;
    window.CargaSelectServ = CargaSelectServ;
    window.CargaSelectSect = CargaSelectSect;
    window.CargaSelectDtoFiltro = CargaSelectDtoFiltro;
    window.CargaSelectServFiltro = CargaSelectServFiltro;
    window.CargaSelectSectFiltro = CargaSelectSectFiltro;
    window.getLocalidades = getLocalidades;
    window.getCP = getCP;
    window.changeEstado = changeEstado;
    window.changJornada = changJornada;
    window.getCuit = getCuit;
    window.addHistorialRel = addHistorialRel;
    window.deleteHisto = deleteHisto;
    window.addDoc = addDoc;
    window.selectFile = selectFile;
    window.deleteDoc = deleteDoc;
    window.deleteDocEdit = deleteDocEdit;
    window.previewDoc = previewDoc;
    window.verJornadas = verJornadas;
    window.eliminarJornadas = eliminarJornadas;
    window.actLegajo = actLegajo;
    window.modalEliminar = modalEliminar;
    window.initCamera = initCamera;
    window.takeSnapshot = takeSnapshot;

    window.activeCrop = activeCrop;
    window.recortar = recortar;
    window.eliminarFoto = eliminarFoto;
    window.onBase64ResizeFotoPerfil = onBase64ResizeFotoPerfil;
    window.onBase64Result = onBase64Result;

    // Función para editar desde la vista de solo lectura
    window.editarPersonal = editarPersonal;

    // ========================================
    // NUEVA LÓGICA DE SERVICIOS ASIGNADOS
    // ========================================

    let serviciosAsignados = []; // Array de objetos: {servicio_id, nombre_servicio, certificador_id, fecha_pase, sector_id, es_existente}
    let empleadosDisponibles = []; // Lista de empleados para los selects de certificadores
    let sectoresPorServicio = {}; // Cache de sectores por servicio {servicioId: [sectores]}
    let contadorServicios = 0; // Contador para IDs únicos de filas
    let serviciosParaDarBaja = []; // Array de servicio_id para dar de baja en el historial

    // Función para cargar empleados disponibles
    function cargarEmpleadosDisponibles() {
        return apiLaravel('/personal/empleados-activos', 'GET')
            .then(response => {
                empleadosDisponibles = response.empleados || [];
                return empleadosDisponibles;
            })
            .catch(error => {
                console.error('Error cargando empleados:', error);
                return [];
            });
    }

    // Función para cargar sectores de un servicio específico
    function cargarSectoresServicio(servicioId) {
        if (sectoresPorServicio[servicioId]) {
            return Promise.resolve(sectoresPorServicio[servicioId]);
        }

        return apiLaravel('/personal/sectores?servicio_id=' + servicioId, 'GET')
            .then(response => {
                sectoresPorServicio[servicioId] = response.data || [];
                return sectoresPorServicio[servicioId];
            })
            .catch(error => {
                console.error('Error cargando sectores:', error);
                return [];
            });
    }

    /**
     * Función para buscar el jefe de un servicio.
     *
     * Dado un servicio, devuelve su jefe único.
     * El backend garantiza que solo devuelve UN jefe por servicio.
     * Si no hay jefe asignado, devuelve null.
     *
     * IMPORTANTE: El certificador se infiere del SERVICIO seleccionado.
     * Esta función se usa para asignar automáticamente el certificador
     * cuando se agrega un servicio a un empleado.
     *
     * @param {number} servicioId - ID del servicio
     * @returns {Promise<object|null>} - Objeto con {id, nombre} del jefe o null
     */
    function buscarJefeServicio(servicioId) {
        console.log('Buscando jefe para servicio ID:', servicioId);
        return apiLaravel('/personal/jefe-servicio?servicio_id=' + servicioId, 'GET')
            .then(response => {
                console.log('Respuesta jefe servicio:', response);
                if (response.jefe) {
                    console.log('Jefe encontrado:', response.jefe.nombre, 'ID:', response.jefe.id);
                } else {
                    console.log('No se encontró jefe para el servicio');
                }
                return response.jefe || null;
            })
            .catch(error => {
                console.error('Error buscando jefe:', error);
                return null;
            });
    }

    /**
     * Función para agregar un servicio a la lista de servicios asignados.
     *
     * Al agregar un servicio, el certificador se asigna AUTOMÁTICAMENTE
     * buscando el jefe de ese servicio. El certificador se infiere del SERVICIO.
     *
     * Flujo:
     * 1. Valida que se haya seleccionado un servicio
     * 2. Verifica que el servicio no esté duplicado
     * 3. Busca automáticamente el jefe del servicio (certificador)
     * 4. Crea el objeto de servicio asignado con el certificador
     * 5. Renderiza la lista de servicios
     */
    window.agregarServicio = async function() {
        let servicioId = $('#servicio').val();

        // Si viene como array, tomar el primer elemento
        if (Array.isArray(servicioId)) {
            servicioId = servicioId[0];
        }

        if (!servicioId || servicioId === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe seleccionar un servicio',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Verificar si el servicio ya está asignado (convertir ambos a string para comparar)
        const yaExiste = serviciosAsignados.find(s => s.servicio_id.toString() === servicioId.toString());
        if (yaExiste) {
            Swal.fire({
                icon: 'warning',
                title: 'Servicio Duplicado',
                text: 'Este servicio ya está asignado',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const nombreServicio = $('#servicio option:selected').text();
        const fechaHoy = new Date().toLocaleDateString('es-AR', {day: '2-digit', month: '2-digit', year: 'numeric'}).replace(/\//g, '/');

        // IMPORTANTE: El certificador se infiere del SERVICIO seleccionado.
        // Buscar automáticamente el jefe del servicio (será el certificador).
        const jefe = await buscarJefeServicio(servicioId);
        const certificadorId = jefe ? jefe.id : '';

        // Crear objeto de servicio asignado con el certificador automático
        const servicioAsignado = {
            id: contadorServicios++,
            servicio_id: servicioId.toString(),
            nombre_servicio: nombreServicio,
            certificador_id: certificadorId, // Certificador asignado automáticamente
            fecha_pase: fechaHoy,
            sector_id: '',
            es_existente: false // Servicios nuevos agregados ahora
        };

        serviciosAsignados.push(servicioAsignado);

        // Renderizar la lista
        await renderizarServiciosAsignados();

        // Limpiar selector correctamente
        const $servicioSelect = $('#servicio');
        $servicioSelect.val(null).trigger('change');

        // Si Select2 está inicializado, limpiarlo también
        if ($servicioSelect.data('select2')) {
            $servicioSelect.select2('val', '');
        }

        if (jefe) {
            Swal.fire({
                icon: 'success',
                title: 'Servicio Agregado',
                html: `<strong>${nombreServicio}</strong><br>Certificador: ${jefe.nombre}`,
                timer: 2500,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Servicio Agregado',
                html: `<strong>${nombreServicio}</strong><br><small>No se encontró jefe asignado. Por favor, seleccione un certificador manualmente.</small>`,
                timer: 3000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
        }
    };

    // Función para eliminar un servicio de la lista
    window.eliminarServicioAsignado = function(id) {
        const servicio = serviciosAsignados.find(s => s.id === id);

        if (!servicio) return;

        // Si es un servicio existente (viene de la BD), marcarlo para dar de baja
        if (servicio.es_existente) {
            serviciosParaDarBaja.push({
                servicio_id: servicio.servicio_id,
                fecha_baja: new Date().toISOString().split('T')[0],
                motivo: 'Baja de servicio'
            });
        }

        // Remover de la lista activa (tanto nuevos como existentes)
        serviciosAsignados = serviciosAsignados.filter(s => s.id !== id);
        renderizarServiciosAsignados();
    };

    // Función para renderizar la lista de servicios asignados
    async function renderizarServiciosAsignados() {
        const $container = $('#servicios-asignados-container');
        const $sinServiciosMsg = $('#sin-servicios-msg');

        if (serviciosAsignados.length === 0) {
            $sinServiciosMsg.show();
            // Destruir Select2 antes de eliminar elementos
            $container.find('.certificador-servicio').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
            });
            $container.find('.servicio-asignado-row').remove();
            return;
        }

        $sinServiciosMsg.hide();
        // Destruir Select2 antes de eliminar elementos
        $container.find('.certificador-servicio').each(function() {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });
        $container.find('.servicio-asignado-row').remove();

        for (const servicio of serviciosAsignados) {
            // Cargar sectores para este servicio
            const sectores = await cargarSectoresServicio(servicio.servicio_id);

            const html = `
                <div class="servicio-asignado-row mb-3 p-3 border rounded bg-white" data-servicio-asignado-id="${servicio.id}">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>${servicio.nombre_servicio}</strong>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Certificador:</label>
                            <select class="form-select form-select-sm certificador-servicio select2-certificador" data-servicio-id="${servicio.id}" data-placeholder="Seleccionar certificador...">
                                <option value="">- Seleccionar -</option>
                                ${empleadosDisponibles.map(emp =>
                                    `<option value="${emp.id}" ${servicio.certificador_id == emp.id ? 'selected' : ''}>${emp.nombre}</option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Fecha Pase:</label>
                            <input type="text" class="form-control form-control-sm fecha-pase-servicio"
                                   data-servicio-id="${servicio.id}" value="${servicio.fecha_pase}"
                                   placeholder="dd/mm/yyyy">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Sector:</label>
                            <select class="form-select form-select-sm sector-servicio" data-servicio-id="${servicio.id}">
                                <option value="">- Seleccionar -</option>
                                ${sectores.map(sect =>
                                    `<option value="${sect.IdSector}" ${servicio.sector_id == sect.IdSector ? 'selected' : ''}>${sect.Sector}</option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarServicioAsignado(${servicio.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $container.append(html);

            // Inicializar Select2 para el certificador (con pequeño retraso para asegurar que esté en el DOM)
            setTimeout(() => {
                const $certificadorSelect = $(`.certificador-servicio[data-servicio-id="${servicio.id}"]`);
                if ($certificadorSelect.length > 0) {
                    // Destruir Select2 si ya existe para evitar duplicados
                    if ($certificadorSelect.data('select2')) {
                        $certificadorSelect.select2('destroy');
                    }
                    $certificadorSelect.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Seleccionar certificador...',
                        allowClear: true
                    });
                }
            }, 10);

            // Inicializar datepicker para la fecha
            const $fechaInput = $(`.fecha-pase-servicio[data-servicio-id="${servicio.id}"]`);
            new tempusDominus.TempusDominus($fechaInput[0], {
                display: {
                    components: {
                        decades: false,
                        year: true,
                        month: true,
                        date: true,
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
        }
    }

    // Listeners para actualizar el array cuando cambien los valores
    // Para Select2, usamos select2:select y el evento change tradicional
    $(document).on('select2:select', '.certificador-servicio', function(e) {
        const servicioId = parseInt($(this).data('servicio-id'));
        const certificadorId = e.params.data.id;
        const servicio = serviciosAsignados.find(s => s.id === servicioId);
        if (servicio) {
            servicio.certificador_id = certificadorId;
        }
    });

    // Para cuando se limpia el Select2 (se presiona la X)
    $(document).on('select2:clear', '.certificador-servicio', function(e) {
        const servicioId = parseInt($(this).data('servicio-id'));
        const servicio = serviciosAsignados.find(s => s.id === servicioId);
        if (servicio) {
            servicio.certificador_id = '';
        }
    });

    $(document).on('change', '.fecha-pase-servicio', function() {
        const servicioId = parseInt($(this).data('servicio-id'));
        const fecha = $(this).val();
        const servicio = serviciosAsignados.find(s => s.id === servicioId);
        if (servicio) {
            servicio.fecha_pase = fecha;
        }
    });

    $(document).on('change', '.sector-servicio', function() {
        const servicioId = parseInt($(this).data('servicio-id'));
        const sectorId = $(this).val();
        const servicio = serviciosAsignados.find(s => s.id === servicioId);
        if (servicio) {
            servicio.sector_id = sectorId;
        }
    });

    // Cargar empleados disponibles al iniciar
    cargarEmpleadosDisponibles();

    window.changSect = function () {

    };
});

// ========================================
// FUNCIONES GLOBALES (fuera del DOMContentLoaded)
// ========================================

/**
 * Navegar a la vista de gestión de hijos del empleado
 * Esta función debe estar fuera del DOMContentLoaded para ser accesible desde onclick
 */
function gestionarHijos(id) {
    window.location.href = `/personal/${id}/hijos/gestionar`;
}
