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
    let cropImg = null;
    let selectoresIniciales = {};

    // Variables de paginación
    const paginacionContenedor = document.getElementById('paginacion-contenedor');
    let paginacion;

    // Contenedores de secciones
    const seccionListado = document.getElementById('seccion-listado');
    const seccionFormulario = document.getElementById('seccion-formulario');
    const accionesPrincipales = document.getElementById('acciones-principales');

    // Inicializar componentes UI
    initUI();

    // Agregar event listeners
    setupEventListeners();

    // Cargar selectores iniciales
    cargarSelectoresIniciales();

    // Cargar datos iniciales
    cargarPersonal(1);

    // ========================================
    // FUNCIONES DE INICIALIZACIÓN
    // ========================================

    /**
     * Cargar todos los selectores iniciales desde el backend
     */
    function cargarSelectoresIniciales() {
        apiLaravel('/personal/selectores-iniciales', 'GET')
            .then(response => {
                const data = response.data;

                // Cargar provincias
                let provinciaOptions = '<option value="">- Seleccionar -</option>';
                data.provincias.forEach(prov => {
                    provinciaOptions += `<option value="${prov.IdProvincia}">${prov.Provincia}</option>`;
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
                    nacionalidadOptions += `<option value="${pais.IdPais}">${pais.Pais}</option>`;
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

                selectoresIniciales = data; // Guardar para uso posterior
            })
            .catch(error => {
                console.error('Error cargando selectores iniciales:', error);
            });
    }

    // ========================================
    // FUNCIONES DE INICIALIZACIÓN
    // ========================================

    /**
     * Inicializar componentes de la interfaz
     */
    function initUI() {
        // Inicializar Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                theme: 'bootstrap-5',
                allowClear: true,
                width: '100%',
                placeholder: '- Seleccionar -'
            });
        }

        // Inicializar datepickers
        if (typeof $.fn.datetimepicker !== 'undefined') {
            $('.datepicker').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'es'
            });
        }

        // Inicializar validación de formulario
        if (typeof $.fn.validate !== 'undefined') {
            $('#form-personal').validate({
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
                                    return $('#empleado_id').val() || null;
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
                                    return $('#empleado_id').val() || null;
                                }
                            },
                            dataFilter: function (data) {
                                var json = JSON.parse(data);
                                return json.exists ? '"El DNI ya existe"' : 'true';
                            }
                        }
                    },
                    fecha_nacimiento: {
                        required: true
                    },
                    fecha_alta: {
                        required: true
                    },
                    email: {
                        email: true
                    }
                },
                messages: {
                    legajo: {
                        required: 'El legajo es obligatorio',
                        number: 'Debe ser un número válido'
                    },
                    apellido: {
                        required: 'El apellido es obligatorio',
                        maxlength: 'Máximo 50 caracteres'
                    },
                    nombre: {
                        required: 'El nombre es obligatorio',
                        maxlength: 'Máximo 50 caracteres'
                    },
                    dni: {
                        required: 'El DNI es obligatorio',
                        number: 'Debe ser un número válido',
                        minlength: 'Mínimo 7 dígitos',
                        maxlength: 'Máximo 8 dígitos'
                    },
                    fecha_nacimiento: {
                        required: 'La fecha de nacimiento es obligatoria'
                    },
                    fecha_alta: {
                        required: 'La fecha de alta es obligatoria'
                    },
                    email: {
                        email: 'Ingrese un email válido'
                    }
                },
                errorClass: 'is-invalid',
                validClass: 'is-valid',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                }
            });
        }

        // Inicializar webcam si está disponible
        if (typeof Webcam !== 'undefined') {
            Webcam.set({
                dest_width: 320 * 3,
                dest_height: 240 * 3,
                image_format: 'png',
                constraints: { facingMode: 'environment' }
            });
        }

        // Inicializar Toast
        if (typeof Swal !== 'undefined') {
            window.Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
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
                cargarPersonal(1);
            });
        }

        // Event listener para el botón de agregar
        const btnAgregar = document.getElementById('btn-agregar');

        if (btnAgregar) {
            btnAgregar.addEventListener('click', function () {
                mostrarFormulario('crear');
            });
        }

        // Event listener para el botón de volver
        const btnVolver = document.getElementById('btn-volver');
        if (btnVolver) {
            btnVolver.addEventListener('click', function () {
                mostrarListado();
            });
        }

        // Event listener para el botón de limpiar filtros
        const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
        if (btnLimpiarFiltros) {
            btnLimpiarFiltros.addEventListener('click', function () {
                limpiarFiltros();
            });
        }

        // Event listener para toggle de filtros
        const btnToggleFiltros = document.getElementById('btn-toggle-filtros');
        if (btnToggleFiltros) {
            btnToggleFiltros.addEventListener('click', function () {
                toggleFiltros();
            });
        }

        // Event listener para el formulario de personal
        const formPersonal = document.getElementById('form-personal');
        if (formPersonal) {
            formPersonal.addEventListener('submit', function (e) {
                e.preventDefault();
                guardarPersonal();
            });
        }

        // Event listener para el botón de limpiar formulario
        const btnLimpiarForm = document.getElementById('btn-limpiar-form');
        if (btnLimpiarForm) {
            btnLimpiarForm.addEventListener('click', function () {
                limpiarFormulario();
            });
        }

        // Event listener para el botón de eliminar
        const btnEliminar = document.getElementById('btn-eliminar');
        if (btnEliminar) {
            btnEliminar.addEventListener('click', function () {
                if (empleadoEditando) {
                    confirmarEliminacion(empleadoEditando);
                }
            });
        }

        // Event listeners para la tabla
        const tablaPersonal = document.getElementById('tabla-personal');
        if (tablaPersonal) {
            tablaPersonal.addEventListener('click', function (e) {
                if (e.target.classList.contains('btn-ver')) {
                    const id = e.target.getAttribute('data-id');
                    verPersonal(id);
                } else if (e.target.classList.contains('btn-editar')) {
                    const id = e.target.getAttribute('data-id');
                    editarPersonal(id);
                } else if (e.target.classList.contains('btn-eliminar')) {
                    const id = e.target.getAttribute('data-id');
                    confirmarEliminacion(id);
                }
            });
        }

        // Event listeners para los selectores jerárquicos
        setupSelectoresJerarquicos();

        // Event listeners para la gestión de imágenes
        setupImageManagement();

        // Event listener para generar CUIT automáticamente
        $('#dni, #sexo').on('change', function () {
            generarCuit();
        });

        // Event listeners para filtros jerárquicos
        $('#filtro_gerencia').on('change', function () {
            cargarDepartamentosFiltro();
        });
    }

    /**
     * Configurar selectores jerárquicos
     */
    function setupSelectoresJerarquicos() {
        $('#gerencia').on('change', function () {
            cargarDepartamentos();
        });

        $('#departamento').on('change', function () {
            cargarServicios();
        });

        $('#servicio').on('change', function () {
            cargarSectores();
        });

        $('#provincia').on('change', function () {
            cargarLocalidades();
        });
    }

    /**
     * Configurar gestión de imágenes
     */
    function setupImageManagement() {
        // Botón seleccionar foto
        $('#btn_seleccionar_foto').on('click', function () {
            $('#file_foto').click();
        });

        // Input file para foto
        $('#file_foto').on('change', function () {
            cargarImagen(this);
        });

        // Botón cámara
        $('#btn_camara').on('click', function () {
            abrirCamara();
        });

        // Botón capturar foto
        $('#btn_capturar').on('click', function () {
            tomarFoto();
        });

        // Botón recortar
        $('#btn_recortar').on('click', function () {
            recortarImagen();
        });

        // Botón eliminar foto
        $('#btn_eliminar_foto').on('click', function () {
            eliminarFoto();
        });

        // Drag and drop para la zona de foto
        const dropZone = document.getElementById('drop_zone');
        if (dropZone) {
            dropZone.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('dragover');
            });

            dropZone.addEventListener('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
            });

            dropZone.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    procesarArchivoImagen(files[0]);
                }
            });

            dropZone.addEventListener('click', function () {
                $('#file_foto').click();
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
                onPageChange: (pagina) => cargarPersonal(pagina),
                onPageSizeChange: () => cargarPersonal(1),
                initialPageSize: porPagina,
                pageSizeOptions: [5, 10, 25, 50, 100]
            });

            paginacion.setPaginationData(totalRegistros, porPagina, paginaActual);
        }
    }

    /**
     * Cargar datos de personal con AJAX
     */
    function cargarPersonal(pagina = 1) {
        mostrarIndicadorCarga(true);

        // Obtener filtros del formulario
        const filtros = {
            pagina: pagina,
            porPagina: 10,
            apellido_nombre: $('#filtro_apellido_nombre').val() || '',
            legajo: $('#filtro_legajo').val() || '',
            dni: $('#filtro_dni').val() || '',
            sexo: $('#filtro_sexo').val() || 0,
            edad: $('#filtro_edad').val() || '',
            profesion: $('#filtro_profesion').val() || 0,
            funcion: $('#filtro_funcion').val() || 0,
            gerencia: $('#filtro_gerencia').val() || 0,
            departamento: $('#filtro_departamento').val() || 0,
            estado: $('#filtro_estado').val() || 0
        };

        apiLaravel('/personal/filtrar', 'GET', filtros)
            .then(response => {
                if (response.data) {
                    actualizarTabla(response.data);
                    actualizarInfoPaginacion(response);

                    initPaginacion(response.totalRegistros, response.porPagina, response.paginaActual);
                } else {
                    mostrarMensaje('error', 'Error al cargar los datos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('error', 'Error al cargar los datos: ' + error.message);
            })
            .finally(() => {
                mostrarIndicadorCarga(false);
            });
    }

    /**
     * Actualizar la tabla de personal con los datos recibidos
     */
    function actualizarTabla(datos) {
        const tbody = document.querySelector('#tabla-personal');

        if (datos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">No se encontraron registros</td></tr>';
            return;
        }

        let html = '';
        datos.forEach(item => {
            html += `
                <tr>
                    <td>${item.legajo}</td>
                    <td>${item.nombre_completo}</td>
                    <td>${item.dni}</td>
                    <td>${item.sexo}</td>
                    <td>${item.edad}</td>
                    <td>${item.gerencia}</td>
                    <td>
                        <span class="badge ${item.estado === 'Activo' ? 'badge-success' : 'badge-secondary'}">
                            ${item.estado}
                        </span>
                    </td>
                    <td>${item.fecha_alta}</td>
                    <td>
                        ${permisos.leer ? `<button type="button" class="btn btn-info btn-sm btn-ver" data-id="${item.idEmpleado}" title="Ver"><i class="fas fa-eye"></i></button>` : ''}
                        ${permisos.editar ? `<button type="button" class="btn btn-warning btn-sm btn-editar ml-1" data-id="${item.idEmpleado}" title="Editar"><i class="fas fa-edit"></i></button>` : ''}
                        ${permisos.eliminar ? `<button type="button" class="btn btn-danger btn-sm btn-eliminar ml-1" data-id="${item.idEmpleado}" title="Eliminar"><i class="fas fa-trash"></i></button>` : ''}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    /**
     * Actualizar información de paginación
     */
    function actualizarInfoPaginacion(response) {
        const totalInfo = document.getElementById('total_info');
        if (totalInfo && response.totalRegistros !== undefined) {
            const desde = ((response.paginaActual - 1) * response.porPagina) + 1;
            const hasta = Math.min(response.paginaActual * response.porPagina, response.totalRegistros);
            totalInfo.innerHTML = `Mostrando ${desde} a ${hasta} de ${response.totalRegistros} registros`;
        }
    }

    // ========================================
    // FUNCIONES DE MOSTRAR/OCULTAR SECCIONES
    // ========================================

    /**
     * Mostrar el formulario y ocultar el listado
     */
    function mostrarFormulario(modo = 'crear', id = null) {
        modoFormulario = modo;
        empleadoEditando = id;

        // Usar clases de Bootstrap en lugar de style.display
        if (seccionListado) {
            seccionListado.classList.add('d-none');
            seccionListado.classList.remove('d-block');
        }

        if (seccionFormulario) {
            seccionFormulario.classList.remove('d-none');
            seccionFormulario.classList.add('d-block');
        }

        // Configurar formulario según el modo
        configurarFormulario();

        if (modo === 'editar' || modo === 'ver') {
            cargarDatosPersonal(id, modo === 'ver');
        } else {
            limpiarFormulario();
        }
    }

    /**
     * Mostrar el listado y ocultar el formulario
     */
    function mostrarListado() {
        // Usar clases de Bootstrap en lugar de style.display
        seccionListado.classList.remove('d-none');
        seccionListado.classList.add('d-block');
        seccionFormulario.classList.add('d-none');
        seccionFormulario.classList.remove('d-block');
        modoFormulario = 'crear';
        empleadoEditando = null;
        limpiarFormulario();
    }

    /**
     * Configurar el formulario según el modo
     */
    function configurarFormulario() {
        const btnGuardar = document.getElementById('btn-guardar');
        const btnEliminar = document.getElementById('btn-eliminar');
        const titulo = document.getElementById('titulo-formulario');

        if (modoFormulario === 'crear') {
            if (titulo) titulo.textContent = 'Crear Personal';
            if (btnGuardar) btnGuardar.style.display = 'inline-block';
            if (btnEliminar) btnEliminar.style.display = 'none';
            habilitarFormulario(true);
        } else if (modoFormulario === 'editar') {
            if (titulo) titulo.textContent = 'Editar Personal';
            if (btnGuardar) btnGuardar.style.display = 'inline-block';
            if (btnEliminar) btnEliminar.style.display = permisos.eliminar ? 'inline-block' : 'none';
            habilitarFormulario(true);
        } else if (modoFormulario === 'ver') {
            if (titulo) titulo.textContent = 'Ver Personal';
            if (btnGuardar) btnGuardar.style.display = 'none';
            if (btnEliminar) btnEliminar.style.display = 'none';
            habilitarFormulario(false);
        }
    }

    /**
     * Habilitar o deshabilitar campos del formulario
     */
    function habilitarFormulario(habilitar) {
        const campos = document.querySelectorAll('#form-personal input, #form-personal select, #form-personal textarea');
        campos.forEach(campo => {
            if (habilitar) {
                campo.removeAttribute('disabled');
                campo.removeAttribute('readonly');
            } else {
                campo.setAttribute('disabled', 'disabled');
            }
        });

        // Siempre deshabilitar CUIT (se genera automáticamente)
        document.getElementById('cuit').setAttribute('readonly', 'readonly');
    }

    // ========================================
    // FUNCIONES CRUD
    // ========================================

    /**
     * Ver un registro de personal
     */
    function verPersonal(id) {
        mostrarFormulario('ver', id);
    }

    /**
     * Editar un registro de personal
     */
    function editarPersonal(id) {
        mostrarFormulario('editar', id);
    }

    /**
     * Cargar datos de personal para ver o editar
     */
    function cargarDatosPersonal(id, soloLectura = false) {
        mostrarIndicadorCarga(true);

        apiLaravel(`/personal/${id}`, 'GET')
            .then(response => {
                if (response.success && response.data) {
                    llenarFormulario(response.data);
                } else {
                    mostrarMensaje('error', response.message || 'Error al cargar los datos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('error', 'Error al cargar los datos: ' + error.message);
            })
            .finally(() => {
                mostrarIndicadorCarga(false);
            });
    }

    /**
     * Llenar el formulario con los datos del empleado
     */
    function llenarFormulario(data) {
        // Llenar campos básicos
        document.getElementById('empleado_id').value = data.idEmpleado || '';
        document.getElementById('legajo').value = data.legajo || '';
        document.getElementById('apellido').value = data.apellido || '';
        document.getElementById('nombre').value = data.nombre || '';
        document.getElementById('dni').value = data.dni || '';
        document.getElementById('cuit').value = data.cuit || '';
        document.getElementById('sexo').value = data.sexo || '1';
        document.getElementById('fecha_nacimiento').value = data.fecha_nacimiento || '';
        document.getElementById('email').value = data.email || '';
        document.getElementById('telefono').value = data.telefono || '';
        document.getElementById('celular').value = data.celular || '';
        document.getElementById('fecha_alta').value = data.fecha_alta || '';
        document.getElementById('estado').value = data.estado || '1';

        // Seleccionar valores en los selects
        if (data.estado_civil) $('#estado_civil').val(data.estado_civil).trigger('change');
        if (data.profesion) $('#profesion').val(data.profesion).trigger('change');
        if (data.funcion) $('#funcion').val(data.funcion).trigger('change');
        if (data.gerencia) $('#gerencia').val(data.gerencia).trigger('change');

        // Cargar selectores dependientes
        if (data.gerencia && data.departamento) {
            cargarDepartamentos(data.departamento);
        }
        if (data.departamento && data.servicio) {
            cargarServicios(data.servicio);
        }
        if (data.servicio && data.sector) {
            cargarSectores(data.sector);
        }

        // Cargar foto si existe
        if (data.foto) {
            mostrarFoto(`/storage/empleados/fotos/${data.foto}`);
        }
    }

    /**
     * Guardar personal (crear o actualizar)
     */
    function guardarPersonal() {
        if (!$('#form-personal').valid()) {
            mostrarMensaje('warning', 'Por favor, corrija los errores en el formulario');
            return;
        }

        mostrarIndicadorCarga(true);

        const formData = obtenerDatosFormulario();
        const url = modoFormulario === 'crear' ? '/personal' : `/personal/${empleadoEditando}`;
        const metodo = modoFormulario === 'crear' ? 'POST' : 'PUT';

        apiLaravel(url, metodo, formData)
            .then(response => {
                if (response.success) {
                    mostrarMensaje('success', response.message || 'Registro guardado correctamente');
                    mostrarListado();
                    cargarPersonal(1);
                } else {
                    mostrarMensaje('error', response.message || 'Error al guardar el registro');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('error', 'Error al guardar: ' + error.message);
            })
            .finally(() => {
                mostrarIndicadorCarga(false);
            });
    }

    /**
     * Obtener datos del formulario
     */
    function obtenerDatosFormulario() {
        const formData = {
            legajo: document.getElementById('legajo').value,
            apellido: document.getElementById('apellido').value,
            nombre: document.getElementById('nombre').value,
            dni: document.getElementById('dni').value,
            cuit: document.getElementById('cuit').value,
            sexo: document.getElementById('sexo').value,
            fecha_nacimiento: document.getElementById('fecha_nacimiento').value,
            email: document.getElementById('email').value,
            telefono: document.getElementById('telefono').value,
            celular: document.getElementById('celular').value,
            estado_civil: $('#estado_civil').val(),
            profesion: $('#profesion').val(),
            funcion: $('#funcion').val(),
            fecha_alta: document.getElementById('fecha_alta').value,
            estado: document.getElementById('estado').value,
            gerencia: $('#gerencia').val(),
            departamento: $('#departamento').val(),
            servicio: $('#servicio').val(),
            sector: $('#sector').val()
        };

        // Agregar foto si existe
        const fotoBase64 = document.getElementById('foto_base64').value;
        if (fotoBase64) {
            formData.foto = fotoBase64;
        }

        return formData;
    }

    /**
     * Confirmar eliminación
     */
    function confirmarEliminacion(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarPersonal(id);
                }
            });
        } else {
            if (confirm('¿Está seguro que desea eliminar este registro?')) {
                eliminarPersonal(id);
            }
        }
    }

    /**
     * Eliminar personal
     */
    function eliminarPersonal(id) {
        mostrarIndicadorCarga(true);

        apiLaravel(`/personal/${id}`, 'DELETE')
            .then(response => {
                if (response.success) {
                    mostrarMensaje('success', response.message || 'Registro eliminado correctamente');
                    if (modoFormulario === 'editar') {
                        mostrarListado();
                    }
                    cargarPersonal(1);
                } else {
                    mostrarMensaje('error', response.message || 'Error al eliminar el registro');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensaje('error', 'Error al eliminar: ' + error.message);
            })
            .finally(() => {
                mostrarIndicadorCarga(false);
            });
    }

    // ========================================
    // FUNCIONES DE SELECTORES
    // ========================================

    /**
     * Cargar selectores iniciales
     */
    function cargarSelectoresIniciales() {
        apiLaravel('/personal/selectores-iniciales', 'GET')
            .then(response => {
                if (response.data) {
                    selectoresIniciales = response.data;
                    poblarSelectores();
                }
            })
            .catch(error => {
                console.error('Error al cargar selectores:', error);
            });
    }

    /**
     * Poblar selectores con datos iniciales
     */
    function poblarSelectores() {
        // Gerencias
        poblarSelect('#gerencia', selectoresIniciales.gerencias, 'idGerencia', 'Gerencia');

        // Estados civiles
        poblarSelect('#estado_civil', selectoresIniciales.estados_civiles, 'idEstadoCivil', 'EstadoCivil');

        // Profesiones
        poblarSelect('#profesion', selectoresIniciales.profesiones, 'idprofesion', 'profesion');

        // Funciones
        poblarSelect('#funcion', selectoresIniciales.funciones, 'IdFuncion', 'Funcion');
    }

    /**
     * Poblar un select con datos
     */
    function poblarSelect(selector, datos, valueField, textField) {
        const select = $(selector);
        select.empty().append('<option value="">- Seleccionar -</option>');

        if (datos && datos.length > 0) {
            datos.forEach(item => {
                select.append(`<option value="${item[valueField]}">${item[textField]}</option>`);
            });
        }

        select.trigger('change');
    }

    /**
     * Cargar departamentos por gerencia
     */
    function cargarDepartamentos(departamentoSeleccionado = null) {
        const gerenciaId = $('#gerencia').val();
        const departamentoSelect = $('#departamento');

        departamentoSelect.empty().append('<option value="">- Seleccionar -</option>');
        $('#servicio').empty().append('<option value="">- Seleccionar -</option>');
        $('#sector').empty().append('<option value="">- Seleccionar -</option>');

        if (!gerenciaId) return;

        apiLaravel('/personal/departamentos', 'GET', { gerencia_id: gerenciaId })
            .then(response => {
                if (response.data) {
                    response.data.forEach(item => {
                        departamentoSelect.append(`<option value="${item.idDepartamento}">${item.departamento}</option>`);
                    });

                    if (departamentoSeleccionado) {
                        departamentoSelect.val(departamentoSeleccionado).trigger('change');
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar departamentos:', error);
            });
    }

    /**
     * Cargar servicios por departamento
     */
    function cargarServicios(servicioSeleccionado = null) {
        const departamentoId = $('#departamento').val();
        const servicioSelect = $('#servicio');

        servicioSelect.empty().append('<option value="">- Seleccionar -</option>');
        $('#sector').empty().append('<option value="">- Seleccionar -</option>');

        if (!departamentoId) return;

        apiLaravel('/personal/servicios', 'GET', { departamento_id: departamentoId })
            .then(response => {
                if (response.data) {
                    response.data.forEach(item => {
                        servicioSelect.append(`<option value="${item.idServicio}">${item.servicio}</option>`);
                    });

                    if (servicioSeleccionado) {
                        servicioSelect.val(servicioSeleccionado).trigger('change');
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar servicios:', error);
            });
    }

    /**
     * Cargar sectores por servicio
     */
    function cargarSectores(sectorSeleccionado = null) {
        const servicioId = $('#servicio').val();
        const sectorSelect = $('#sector');

        sectorSelect.empty().append('<option value="">- Seleccionar -</option>');

        if (!servicioId) return;

        apiLaravel('/personal/sectores', 'GET', { servicio_id: servicioId })
            .then(response => {
                if (response.data) {
                    response.data.forEach(item => {
                        sectorSelect.append(`<option value="${item.idSector}">${item.sector}</option>`);
                    });

                    if (sectorSeleccionado) {
                        sectorSelect.val(sectorSeleccionado);
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar sectores:', error);
            });
    }

    /**
     * Cargar departamentos para filtros
     */
    function cargarDepartamentosFiltro() {
        const gerenciaId = $('#filtro_gerencia').val();
        const departamentoSelect = $('#filtro_departamento');

        departamentoSelect.empty().append('<option value="0">- Todos -</option>');

        if (!gerenciaId || gerenciaId === '0') return;

        apiLaravel('/personal/departamentos', 'GET', { gerencia_id: gerenciaId })
            .then(response => {
                if (response.data) {
                    response.data.forEach(item => {
                        departamentoSelect.append(`<option value="${item.idDepartamento}">${item.departamento}</option>`);
                    });
                }
            })
            .catch(error => {
                console.error('Error al cargar departamentos:', error);
            });
    }

    // ========================================
    // FUNCIONES DE GESTIÓN DE IMÁGENES
    // ========================================

    /**
     * Cargar imagen desde input file
     */
    function cargarImagen(input) {
        if (input.files && input.files[0]) {
            procesarArchivoImagen(input.files[0]);
        }
    }

    /**
     * Procesar archivo de imagen
     */
    function procesarArchivoImagen(file) {
        // Validar tipo de archivo
        if (!file.type.match('image.*')) {
            mostrarMensaje('error', 'Solo se permiten archivos de imagen');
            return;
        }

        // Validar tamaño (2MB máximo)
        if (file.size > 2 * 1024 * 1024) {
            mostrarMensaje('error', 'El archivo es demasiado grande. Máximo 2MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            activarRecorte(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    /**
     * Abrir cámara para tomar foto
     */
    function abrirCamara() {
        if (typeof Webcam === 'undefined') {
            mostrarMensaje('error', 'Funcionalidad de cámara no disponible');
            return;
        }

        $('#modal_foto').modal('show');

        $('#modal_foto').on('shown.bs.modal', function () {
            Webcam.attach('#my_camera');
        });

        $('#modal_foto').on('hidden.bs.modal', function () {
            Webcam.reset();
        });
    }

    /**
     * Tomar foto con la cámara
     */
    function tomarFoto() {
        Webcam.snap(function (dataUri) {
            $('#modal_foto').modal('hide');
            activarRecorte(dataUri);
        });
    }

    /**
     * Activar el recorte de imagen
     */
    function activarRecorte(imageSrc) {
        if (cropImg != null) {
            cropImg.croppie('destroy');
        }

        $('#img_foto').attr('src', imageSrc);
        $('#crop_content').show();
        $('#img_crop').hide();
        $('#btn_recortar').show();

        cropImg = $('#img_foto').croppie({
            viewport: { width: 200, height: 200 },
            boundary: { width: 250, height: 250 },
            enableOrientation: true,
            enableResize: false,
            showZoomer: true
        });
    }

    /**
     * Recortar la imagen
     */
    function recortarImagen() {
        if (!cropImg) return;

        cropImg.croppie('result', {
            type: 'base64',
            format: 'png',
            quality: 1,
            size: { width: 300, height: 300 }
        }).then(function (base64Result) {
            document.getElementById('foto_base64').value = base64Result;
            mostrarFoto(base64Result);

            // Destruir el croppie y mostrar la imagen final
            cropImg.croppie('destroy');
            cropImg = null;
            $('#crop_content').hide();
            $('#img_crop').show();
            $('#btn_recortar').hide();
        });
    }

    /**
     * Mostrar foto en el preview
     */
    function mostrarFoto(src) {
        $('#foto_preview').attr('src', src).show();
        $('#drop_zone').hide();
    }

    /**
     * Eliminar foto
     */
    function eliminarFoto() {
        document.getElementById('foto_base64').value = '';
        $('#foto_preview').hide();
        $('#drop_zone').show();

        if (cropImg) {
            cropImg.croppie('destroy');
            cropImg = null;
        }

        $('#crop_content').hide();
        $('#img_crop').show();
        $('#btn_recortar').hide();
    }

    // ========================================
    // FUNCIONES AUXILIARES
    // ========================================

    /**
     * Generar CUIT automáticamente
     */
    function generarCuit() {
        const dni = document.getElementById('dni').value;
        const sexo = document.getElementById('sexo').value;

        if (dni && dni.length >= 7) {
            const cuit = calcularCuit(dni, sexo);
            document.getElementById('cuit').value = cuit;
        }
    }

    /**
     * Calcular CUIT basado en DNI y sexo
     */
    function calcularCuit(dni, sexo) {
        const prefijo = sexo === '1' ? '20' : '27'; // 20 para masculino, 27 para femenino
        const codigo = prefijo + dni.padStart(8, '0');

        // Calcular dígito verificador
        const multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        let suma = 0;

        for (let i = 0; i < 10; i++) {
            suma += parseInt(codigo[i]) * multiplicadores[i];
        }

        let resto = suma % 11;
        let dv = 11 - resto;

        if (dv === 11) dv = 0;
        if (dv === 10) dv = 9;

        return codigo + dv;
    }

    /**
     * Limpiar formulario
     */
    function limpiarFormulario() {
        document.getElementById('form-personal').reset();
        document.getElementById('empleado_id').value = '';

        // Limpiar selects
        $('.select2').val('').trigger('change');

        // Limpiar foto
        eliminarFoto();

        // Limpiar validaciones
        $('#form-personal').validate().resetForm();
        $('#form-personal .is-invalid').removeClass('is-invalid');
        $('#form-personal .is-valid').removeClass('is-valid');
    }

    /**
     * Limpiar filtros
     */
    function limpiarFiltros() {
        document.getElementById('formFiltros').reset();
        $('.select2').val('').trigger('change');
        cargarPersonal(1);
    }

    /**
     * Toggle de filtros
     */
    function toggleFiltros() {
        const panel = document.getElementById('panel-filtros');
        const btn = document.getElementById('btn-toggle-filtros');
        const icon = btn.querySelector('i');

        if (panel.style.display === 'none') {
            panel.style.display = 'block';
            icon.className = 'fas fa-chevron-up';
            btn.innerHTML = '<i class="fas fa-chevron-up"></i> Ocultar filtros';
        } else {
            panel.style.display = 'none';
            icon.className = 'fas fa-chevron-down';
            btn.innerHTML = '<i class="fas fa-chevron-down"></i> Mostrar filtros';
        }
    }

    /**
     * Mostrar u ocultar indicador de carga
     */
    function mostrarIndicadorCarga(mostrar) {
        const indicador = document.getElementById('indicador-carga');
        if (indicador) {
            indicador.style.display = mostrar ? 'block' : 'none';
        }
    }

    /**
     * Mostrar mensaje de notificación
     */
    function mostrarMensaje(tipo, mensaje) {
        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: tipo,
                title: mensaje
            });
        } else {
            alert(mensaje);
        }
    }

    // ========================================
    // FUNCIONES ADICIONALES DEL ABM ORIGINAL
    // ========================================

    /**
     * Generar CUIT a partir de DNI y sexo
     */
    function getCuit() {
        let dni = $("#dni").val();
        let sexo = $("#sexo").val();

        // Early return if dni or sexo is empty
        if (!dni || !sexo || sexo == -1 || sexo == 0) return;

        let cuit = Genera_cuti(sexo, dni);
        $("#cuit").val(cuit);
    }

    /**
     * Función para generar CUIT
     */
    function Genera_cuti(sex, doc) {
        if (!doc || doc.length === 0) return "";

        let $Primero;
        if (sex == 1) {
            $Primero = "20";
        } else {
            $Primero = "27";
        }

        let X = $Primero.split("");
        let y = doc.split("");

        let OL = ((X[0] * 5) + (X[1] * 4) + (y[0] * 3) + (y[1] * 2) + (y[2] * 7) + (y[3] * 6) + (y[4] * 5) + (y[5] * 4) + (y[6] * 3) + (y[7] * 2));
        let resto = Math.floor(OL / 11);
        resto = OL - (resto * 11);

        let z;
        let XY = $Primero;

        if (resto == 0) {
            z = resto;
        } else if (resto == 1) {
            if (sex == 1) {
                z = 9;
                XY = "23";
            } else {
                z = 4;
                XY = "23";
            }
        } else {
            z = 11 - resto;
        }

        let cuit = XY + '-' + doc + '-' + z;
        return cuit;
    }

    /**
     * Obtener código postal desde localidad seleccionada
     */
    function getCP() {
        // Early return if localidad value is -1 or 0
        if ($("#localidad").val() == -1 || $("#localidad").val() == 0) return;

        // Obtener el índice seleccionado (restando 1 porque el primer option es "- Seleccionar -")
        let idx = $("#localidad")[0].selectedIndex - 1;

        if (data_local && idx >= 0 && data_local[idx] && typeof data_local[idx]["CP"] !== "undefined") {
            $("#cp").val(data_local[idx]["CP"]);
        } else {
            $("#cp").val("");
        }
    }

    /**
     * Cambiar estado del formulario y mostrar/ocultar campos relacionados
     */
    function changeEstado() {
        // Early return if estado value is -1 or 0
        if ($("#estado").val() == -1 || $("#estado").val() == 0) return;

        if ($("#estado").val() == 3) {
            $("#cont_baja").show();
            $("#descript_baja").show();
        } else {
            $("#cont_baja").hide();
            $("#descript_baja").hide();
        }
    }

    /**
     * Habilitar campo de fecha jornada
     */
    function changJornada() {
        // Early return if tipo_jornada value is -1 or 0
        if ($("#tipo_jornada").val() == -1 || $("#tipo_jornada").val() == 0) return;

        $("#f_jornada").attr("disabled", false);
        $("#f_jornada").val("");
    }

    /**
     * Cargar departamentos basado en gerencia seleccionada
     */
    function CargaSelectDto() {
        let gerenciaId = $("#gerencia").val();
        if (!gerenciaId) {
            $("#departamento").empty().append('<option value="">- Seleccionar -</option>');
            $("#servicio").empty().append('<option value="">- Seleccionar -</option>');
            $("#sector").empty().append('<option value="">- Seleccionar -</option>');
            return;
        }

        apiLaravel('/personal/departamentos', 'GET', { gerencia_id: gerenciaId })
            .then(response => {
                let options = '<option value="">- Seleccionar -</option>';
                response.data.forEach(dept => {
                    options += `<option value="${dept.idDepartamento}">${dept.Departamento}</option>`;
                });
                $("#departamento").html(options);
                $("#servicio").empty().append('<option value="">- Seleccionar -</option>');
                $("#sector").empty().append('<option value="">- Seleccionar -</option>');
            })
            .catch(error => {
                console.error('Error cargando departamentos:', error);
            });
    }

    /**
     * Cargar servicios basado en departamento seleccionado
     */
    function CargaSelectServ() {
        let departamentoId = $("#departamento").val();
        if (!departamentoId) {
            $("#servicio").empty().append('<option value="">- Seleccionar -</option>');
            $("#sector").empty().append('<option value="">- Seleccionar -</option>');
            return;
        }

        apiLaravel('/personal/servicios', 'GET', { departamento_id: departamentoId })
            .then(response => {
                let options = '<option value="">- Seleccionar -</option>';
                response.data.forEach(serv => {
                    options += `<option value="${serv.idServicio}">${serv.Servicio}</option>`;
                });
                $("#servicio").html(options);
                $("#sector").empty().append('<option value="">- Seleccionar -</option>');
            })
            .catch(error => {
                console.error('Error cargando servicios:', error);
            });
    }

    /**
     * Cargar sectores basado en servicio seleccionado
     */
    function CargaSelectSect() {
        let servicioId = $("#servicio").val();
        if (!servicioId) {
            $("#sector").empty().append('<option value="">- Seleccionar -</option>');
            return;
        }

        apiLaravel('/personal/sectores', 'GET', { servicio_id: servicioId })
            .then(response => {
                let options = '<option value="">- Seleccionar -</option>';
                response.data.forEach(sect => {
                    options += `<option value="${sect.idSector}">${sect.Sector}</option>`;
                });
                $("#sector").html(options);
            })
            .catch(error => {
                console.error('Error cargando sectores:', error);
            });
    }

    /**
     * Función ejecutada al cambiar sector
     */
    function changSect() {
        // Función placeholder para mantener compatibilidad
        // Agregar lógica específica si es necesaria
    }

    /**
     * Cargar localidades basado en provincia seleccionada
     */
    function getLocalidades(localidadId = null) {
        let provinciaId = $("#provincia").val();
        if (!provinciaId) {
            $("#localidad").empty().append('<option value="">- Seleccionar -</option>');
            $("#cp").val("");
            return;
        }

        apiLaravel('/personal/localidades', 'GET', { provincia_id: provinciaId })
            .then(response => {
                let options = '<option value="">- Seleccionar -</option>';
                data_local = response.data; // Guardar datos para uso en getCP()
                response.data.forEach(loc => {
                    const selected = localidadId && localidadId == loc.idLocalidad ? 'selected' : '';
                    options += `<option value="${loc.idLocalidad}" ${selected}>${loc.Localidad}</option>`;
                });
                $("#localidad").html(options);

                if (localidadId) {
                    $("#localidad").val(localidadId).trigger('change');
                }
            })
            .catch(error => {
                console.error('Error cargando localidades:', error);
            });
    }

    /**
     * Agregar historial de relación
     */
    function addHistorialRel() {
        let str = '<div class="row" id="rel_' + num_relaciones + '">' +
            '<div class="form-group col-md-3">' +
            '<select id="relacion_' + num_relaciones + '" class="form-control select2-rel">' + $("#relacion").html() + '</select>' +
            '</div>' +
            '<div class="form-group col-md-3">' +
            '<div class="input-group date" id="desde_rel_' + num_relaciones + '_picker" data-target-input="nearest">' +
            '<input type="text" id="desde_rel_' + num_relaciones + '" placeholder="Desde" class="form-control datetimepicker-input" data-target="#desde_rel_' + num_relaciones + '_picker"/>' +
            '<div class="input-group-append" data-target="#desde_rel_' + num_relaciones + '_picker" data-toggle="datetimepicker">' +
            '<div class="input-group-text"><i class="fa fa-calendar"></i></div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="form-group col-md-3">' +
            '<div class="input-group date" id="hasta_rel_' + num_relaciones + '_picker" data-target-input="nearest">' +
            '<input type="text" id="hasta_rel_' + num_relaciones + '" placeholder="Hasta" class="form-control datetimepicker-input" data-target="#hasta_rel_' + num_relaciones + '_picker"/>' +
            '<div class="input-group-append" data-target="#hasta_rel_' + num_relaciones + '_picker" data-toggle="datetimepicker">' +
            '<div class="input-group-text"><i class="fa fa-calendar"></i></div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="form-group col-md-2">' +
            '<input type="text" id="obs_rel_' + num_relaciones + '" placeholder="Observacion" class="form-control" />' +
            '</div>' +
            '<div class="form-group col-md-1">' +
            '<button class="btn btn-danger" onclick="deleteHisto(' + num_relaciones + ')" type="button"><i class="fas fa-times"></i></button>' +
            '</div>' +
            '</div>';

        $("#container_relaciones").append(str);

        // Inicializar datepicker
        $('#desde_rel_' + num_relaciones + '_picker').datetimepicker({
            format: 'DD/MM/YYYY'
        });
        $('#hasta_rel_' + num_relaciones + '_picker').datetimepicker({
            format: 'DD/MM/YYYY'
        });

        // Inicializar Select2 para el nuevo elemento
        $('#relacion_' + num_relaciones).select2({
            width: '100%',
            placeholder: "- Seleccionar -"
        });

        num_relaciones++;
    }

    /**
     * Eliminar historial de relación
     */
    function deleteHisto(ind) {
        $("#rel_" + ind).remove();
    }

    /**
     * Agregar documento
     */
    function addDoc(edit = false) {
        let html = '<div class="row" id="doc_' + num_doc + '">';

        if (!edit) {
            html += '<input type="hidden" id="id_img_' + num_doc + '" value="0">';
        } else {
            html += '<input type="hidden" id="id_img_' + num_doc + '" value="">';
        }

        html += '<div class="form-group col-md-7" style="padding-top: 30px;">' +
            '<input type="text" id="doc_nombre_' + num_doc + '" placeholder="Documento" class="form-control" />' +
            '</div>' +
            '<div class="form-group col-md-3">';

        if (!edit) {
            html += '<input type="file" id="file_' + num_doc + '" class="form-control" accept="image/*" onchange="cargarImg(this)" style="display:none;">' +
                '<button class="btn btn-primary" onclick="selectFile(' + num_doc + ')" type="button">Seleccionar archivo</button>';
        } else {
            html += '<input type="file" id="file_' + num_doc + '" class="form-control" accept="image/*" onchange="cargarImg(this)" style="display:none;">' +
                '<button class="btn btn-primary" onclick="selectFile(' + num_doc + ')" type="button">Cambiar archivo</button>';
        }

        html += '<img style="display:none;" class="img_prev" id="img_prev_' + num_doc + '_big" style="width:100px;" src="img/dummy.png">' +
            '</div>' +
            '<div class="form-group col-md-1" style="padding-top: 30px;">';

        if (!edit) {
            html += '<button class="btn btn-danger" onclick="deleteDoc(' + num_doc + ')" type="button"><i class="fas fa-times"></i></button>';
        } else {
            html += '<button class="btn btn-danger" onclick="deleteDocEdit(' + num_doc + ')" type="button"><i class="fas fa-times"></i></button>';
        }

        html += '</div></div>';

        $("#container_doc").append(html);
        num_doc++;
    }

    /**
     * Eliminar documento
     */
    function deleteDoc(ind) {
        $("#doc_" + ind).remove();
    }

    /**
     * Eliminar documento en edición
     */
    function deleteDocEdit(ind) {
        arrImgsDel.push($("#id_img_" + ind).val());
        $("#doc_" + ind).remove();
    }

    /**
     * Seleccionar archivo
     */
    function selectFile(ind) {
        $("#file_" + ind).trigger("click");
    }

    // Variables globales necesarias para compatibilidad
    var num_relaciones = 0;
    var num_doc = 0;
    var data_local = [];
    var arrImgsDel = [];

});
