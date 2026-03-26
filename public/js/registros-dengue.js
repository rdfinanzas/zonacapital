/**
 * Módulo de Registros de Dengue - OPTIMIZADO ⚡
 * Versión limpia y eficiente
 *
 * USA: google.maps.places.Autocomplete (API clásica)
 * La nueva API PlaceAutocompleteElement cause problemas de CSS y funcionalidad
 * Mantiene la API clásica que funciona perfectamente
 */

// ========== VARIABLES GLOBALES NECESARIAS ==========
let dataTable = [];
let pagina = 1;
let Toast;
let idEdit = 0;
let idEliminar = 0;
let idPersonaRegistro = null;
let borrarImg = false;

// Google Maps - Instancias únicas
let autocompletePaciente, autocompleteHecho, autocompleteViaje;
let latitudePaciente = null, longitudePaciente = null;
let latitudeHecho = null, longitudeHecho = null;
let latitudeViaje = null, longitudeViaje = null;

// Control de inicialización
const autocompletadosInicializados = {
    hecho: false,
    viaje: false,
    paciente: false
};

// Mapa para click manual
let clickMapHecho, clickMarkerHecho = null;

// Datos del paciente
let pacienteActual = null;

// Configuración de imagen
let imagenFoto;

// ========== FUNCIONES PRINCIPALES ==========

/**
 * FUNCIÓN: Inicializar autocompletado de Google Maps
 * Usando API clásica (google.maps.places.Autocomplete) - FUNCIONA BIEN
 * @param {string} inputId - ID del campo input
 * @param {string} tipo - 'hecho', 'viaje', 'paciente'
 * @param {Object} coordenadas - Objeto para almacenar lat/lng
 */
function initAutocompletado(inputId, tipo, coordenadas) {
    if (typeof google === 'undefined' || !google.maps?.places) {
        console.warn('Google Maps no disponible');
        return false;
    }

    const inputElement = document.getElementById(inputId);
    if (!inputElement) {
        console.warn(`Input ${inputId} no encontrado para ${tipo}`);
        return false;
    }

    console.log(`Inicializando autocompletado clásico para ${tipo}`);

    const options = {
        types: ['geocode'],
        componentRestrictions: { country: 'AR' }
    };

    const autocomplete = new google.maps.places.Autocomplete(inputElement, options);

    // Event listener clásico
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();

        if (!place.geometry) {
            showNoIndicator(tipo);
            clearCoordinates(coordenadas);
            return;
        }

        console.log(place.geometry.location.lat());

        // Guardar coordenadas (API clásica)
        coordenadas.lat = place.geometry.location.lat();
        coordenadas.lng = place.geometry.location.lng();
        updateCoordinateFields(tipo, coordenadas);

        // Auto-rellenar campos administrativos
        if (place.address_components) {
            autoFillLocationFields(place.address_components, tipo);
        }
        showOkIndicator(tipo);

        console.log(`Lugar seleccionado (${tipo}):`, place.formatted_address);
    });

    // Manejar escritura manual y FIX Z-INDEX para modal
    inputElement.addEventListener('input', function(e) {
        // FIX Z-INDEX: Corregir z-index de pac-containers para modal
        if (inputId === 'modal_domicilio') {
            setTimeout(() => {
                const pacContainers = document.querySelectorAll('.pac-container');
                pacContainers.forEach((container) => {
                    const currentZIndex = window.getComputedStyle(container).zIndex;
                    const isVisible = container.style.display !== 'none';

                    // Aumentar z-index si está dentro de modal y es visible
                    if (isVisible && currentZIndex === '1000') {
                        container.style.zIndex = '99999';
                    }
                });
            }, 500);
        }

        if (this.value.length > 0) {
            hideIndicators(tipo);
            clearCoordinates(coordenadas);
        } else {
            showNoIndicator(tipo);
        }
    });

    return autocomplete;
}



/**
 * FUNCIONES DE UTILIDAD PARA INDICADORES
 */
function hideIndicators(tipo) {
    const prefixes = { hecho: 'hecho', viaje: 'viaje', paciente: 'modal' };
    const prefix = prefixes[tipo];
    $(`#check_no_dir_${prefix === 'modal' ? '' : prefix}, #check_ok_dir_${prefix === 'modal' ? '' : prefix}`).hide();
    if (prefix === 'modal') {
        $('#modal_check_no_dir, #modal_check_ok_dir').hide();
    }
}

function showOkIndicator(tipo) {
    const prefixes = { hecho: 'hecho', viaje: 'viaje', paciente: 'modal' };
    const prefix = prefixes[tipo];
    if (prefix === 'modal') {
        $("#modal_check_no_dir").hide();
        $("#modal_check_ok_dir").show();
    } else {
        $(`#check_no_dir_${prefix}`).hide();
        $(`#check_ok_dir_${prefix}`).show();
    }
}

function showNoIndicator(tipo) {
    const prefixes = { hecho: 'hecho', viaje: 'viaje', paciente: 'modal' };
    const prefix = prefixes[tipo];
    if (prefix === 'modal') {
        $("#modal_check_no_dir").show();
        $("#modal_check_ok_dir").hide();
    } else {
        $(`#check_no_dir_${prefix}`).show();
        $(`#check_ok_dir_${prefix}`).hide();
    }
}

function clearCoordinates(coordenadas) {
    coordenadas.lat = null;
    coordenadas.lng = null;
}

function updateCoordinateFields(tipo, coordenadas) {
    if (tipo === 'hecho') {
        $('#latitud_hecho').val(coordenadas.lat);
        $('#longitud_hecho').val(coordenadas.lng);
        latitudeHecho = coordenadas.lat;
        longitudeHecho = coordenadas.lng;
    } else if (tipo === 'viaje') {
        latitudeViaje = coordenadas.lat;
        longitudeViaje = coordenadas.lng;
    } else if (tipo === 'paciente') {
        $('#modal_latitud').val(coordenadas.lat);
        $('#modal_longitud').val(coordenadas.lng);
        latitudePaciente = coordenadas.lat;
        longitudePaciente = coordenadas.lng;
    }
}

function autoFillLocationFields(components, tipo) {
    let departamento = '', localidad = '';

    if (!components || !Array.isArray(components)) {
        console.log('No hay componentes de dirección disponibles');
        return;
    }

    components.forEach(component => {
        const types = component.types || [];
        const longName = component.long_name || '';

        if (types.includes('administrative_area_level_2')) {
            departamento = longName;
        } else if (types.includes('locality') || types.includes('administrative_area_level_3')) {
            localidad = longName;
        }
    });

    if (tipo === 'hecho') {
        if (departamento && !$('#dto_hecho').val()) $('#dto_hecho').val(departamento);
        if (localidad && !$('#localidad_hecho').val()) $('#localidad_hecho').val(localidad);
    } else if (tipo === 'paciente') {
        if (departamento && !$('#modal_dto').val()) $('#modal_dto').val(departamento);
        if (localidad && !$('#modal_localidad').val()) $('#modal_localidad').val(localidad);
    }
}

/**
 * INICIALIZACIÓN CENTRALIZADA - API CLÁSICA
 */
function inicializarAutocompletados() {
    // Hecho
    if (document.getElementById('domicilio_hecho') && !autocompletadosInicializados.hecho) {
        autocompleteHecho = initAutocompletado('domicilio_hecho', 'hecho', { lat: null, lng: null });
        autocompletadosInicializados.hecho = !!autocompleteHecho;
        console.log('Autocompletado HECHO inicializado:', !!autocompleteHecho);
    }

    // Viaje
    if (document.getElementById('ant_viaje') && !autocompletadosInicializados.viaje) {
        autocompleteViaje = initAutocompletado('ant_viaje', 'viaje', { lat: null, lng: null });
        autocompletadosInicializados.viaje = !!autocompleteViaje;
        console.log('Autocompletado VIAJE inicializado:', !!autocompleteViaje);
    }

    // Paciente (siempre disponible)
    if (document.getElementById('modal_domicilio') && !autocompletadosInicializados.paciente) {
        autocompletePaciente = initAutocompletado('modal_domicilio', 'paciente', { lat: null, lng: null });
        autocompletadosInicializados.paciente = !!autocompletePaciente;
        console.log('Autocompletado PACIENTE inicializado:', !!autocompletePaciente);
    }
}

/**
 * FUNCIÓN DE DEBUG PARA MODAL
 */
function debugModalAutocompletado() {
    console.log('=== DEBUG MODAL AUTOCOMPLETADO ===');
    const modalInput = document.getElementById('modal_domicilio');
    console.log('Input modal_domicilio:', {
        existe: !!modalInput,
        visible: modalInput ? (modalInput.offsetWidth > 0 && modalInput.offsetHeight > 0) : false,
        estilo: modalInput ? window.getComputedStyle(modalInput).display : 'N/A',
        valor: modalInput ? modalInput.value : 'N/A',
        modal_visible: $('#modal_datos_paciente').is(':visible'),
        autocompletado_inicializado: autocompletadosInicializados.paciente,
        instancia_existe: !!autocompletePaciente
    });

    if (modalInput && typeof google !== 'undefined') {
        console.log('Intentando reinicializar modal_domicilio...');
        autocompletadosInicializados.paciente = false;
        if (autocompletePaciente) {
            google.maps.event.clearInstanceListeners(autocompletePaciente);
        }
        autocompletePaciente = initAutocompletado('modal_domicilio', 'paciente', { lat: null, lng: null });
        console.log('Reinicialización resultado:', !!autocompletePaciente);
    }
}

/**
 * FUNCIÓN ÚNICA PARA FORMATEAR FECHAS
 */
function formatearFecha(fecha) {
    if (!fecha) return '';
    const date = new Date(fecha);
    if (isNaN(date.getTime())) return '';
    return date.toLocaleDateString('es-AR');
}

// ========== FUNCIONES DE NEGOCIO ==========

function buscarDni() {
    const dni = $("#dni").val();
    if (!dni || dni.length < 6) {
        Toast.fire({ icon: 'warning', title: 'Ingrese un DNI válido (mínimo 6 dígitos)' });
        return;
    }

    $("#btn_buscar_dni").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    apiLaravel(window.laravelRoutes.registrosDengueBuscarDni, 'GET', { dni })
        .then(response => {
            if (response.success) {
                response.paciente ?
                    (mostrarDatosPaciente(response.paciente), mostrarFormularioDengue()) :
                    abrirModalCrearPaciente(dni);
            } else {
                Toast.fire({ icon: 'error', title: 'Error al buscar paciente' });
            }
        })
        .catch(error => {
            console.error('Error al buscar paciente:', error);
            Toast.fire({ icon: 'error', title: 'Error al buscar paciente' });
        })
        .then(() => {
            // Equivalente a finally - siempre se ejecuta
            $("#btn_buscar_dni").prop('disabled', false).html('<i class="fas fa-search"></i>');
        });
}

function mostrarDatosPaciente(paciente) {
    pacienteActual = paciente;

    // Resumen elegante
    $('#resumen_nombre').text(paciente.ApellidoNombre || '-');
    $('#resumen_sexo').text(paciente.Sexo == 1 ? 'F' : 'M');
    $('#resumen_fecha_nac').text(formatearFecha(paciente.FechaNacimiento) || '-');
    $('#resumen_celular').text(paciente.Celular || '-');

    // Domicilio completo
    const domicilio = [paciente.Domicilio, paciente.Localidad, paciente.Departamento]
        .filter(Boolean).join(', ') || 'Sin domicilio registrado';
    $('#resumen_domicilio_completo').text(domicilio);

    // Campos ocultos
    $('#paciente_id_form').val(paciente.IdPacienteRegTrab);
    $('#nombre').val(paciente.ApellidoNombre);
    $('#sexo').val(paciente.Sexo);
    $('#fecha_nac').val(formatearFecha(paciente.FechaNacimiento));
    $('#celular').val(paciente.Celular);
    $('#domicilio').val(paciente.Domicilio);
    $('#dto').val(paciente.Departamento);
    $('#localidad').val(paciente.Localidad);
    $('#barrio').val(paciente.Barrio);
    $('#referencia').val(paciente.Referencias);

    // Coordenadas
    if (paciente.Latitud && paciente.Longitud) {
        latitudePaciente = parseFloat(paciente.Latitud);
        longitudePaciente = parseFloat(paciente.Longitud);
        $('#latitud').val(latitudePaciente);
        $('#longitud').val(longitudePaciente);
    }

    $('#resumen_paciente').show();
    idPersonaRegistro = paciente.IdPacienteRegTrab;
}

function mostrarFormularioDengue() {
    $("#form_persona").show();
    $("#footer_btn").show();

    if ($('#usar_domicilio_paciente').is(':checked')) {
        copiarDomicilioPacienteAHecho();
    }

    setTimeout(inicializarAutocompletados, 100);
}

function guardar() {
    // Validaciones
    if (!$('#paciente_id_form').val()) {
        Toast.fire({ icon: 'error', title: 'Debe buscar y registrar un paciente primero' });
        return;
    }

    const camposRequeridos = [
        { campo: 'domicilio_hecho', mensaje: 'El domicilio donde ocurrió el hecho es requerido' },
        { campo: 'dto_hecho', mensaje: 'El departamento del hecho es requerido' },
        { campo: 'localidad_hecho', mensaje: 'La localidad del hecho es requerida' }
    ];

    for (const {campo, mensaje} of camposRequeridos) {
        if (!$(`#${campo}`).val().trim()) {
            Toast.fire({ icon: 'error', title: mensaje });
            return;
        }
    }

    // Preparar datos
    const datosFormulario = {
        paciente_id_form: $('#paciente_id_form').val(),
        semana: $('#semana').val(),
        f_fis: $('#f_fis').val(),
        f_consulta: $('#f_consulta').val(),
        f_muestra: $('#f_muestra').val(),
        internacion: $('#internacion').val(),
        f_ingreso: $('#f_ingreso').val(),
        f_alta: $('#f_alta').val(),
        domicilio_hecho: $('#domicilio_hecho').val(),
        dto_hecho: $('#dto_hecho').val(),
        localidad_hecho: $('#localidad_hecho').val(),
        barrio_hecho: $('#barrio_hecho').val(),
        referencia_hecho: $('#referencia_hecho').val(),
        latitud_hecho: latitudeHecho,
        longitud_hecho: longitudeHecho,
        laboratorio: $('#laboratorio').val(),
        testAgNS1: $('#testAgNS1').val(),
        tipoNs1: $('#tipoNs1').val(),
        testIgM: $('#testIgM').val(),
        testIGG: $('#testIGG').val(),
        testPCR: $('#testPCR').val(),
        testRapidoIgG: $('#testRapidoIgG').val(),
        testRapidoIgM: $('#testRapidoIgM').val(),
        testChikungunya: $('#testChikungunya').val(),
        testZika: $('#testZika').val(),
        ant_vacu: $('#ant_vacu').val() === '1',
        obito: $('#obito').val() === '1',
        comor: $('#comor').val(),
        obs: $('#obs').val(),
        ant_viaje: $('#ant_viaje').val(),
        f_ant: $('#f_ant').val(),
        latitud_viaje: latitudeViaje,
        longitud_viaje: longitudeViaje,
        // Manejar efector: puede ser select o estar fijo por permisos
        efector_sel: $('#efector_sel').length ? $('#efector_sel').val() : null,
        imagen_base64: imagenFoto?.getBase64() || null
    };

    $('#btn_submit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    const url = idEdit > 0 ?
        window.laravelRoutes.registrosDengueUpdate.replace(':id', idEdit) :
        window.laravelRoutes.registrosDengueStore;
    const method = idEdit > 0 ? 'PUT' : 'POST';

    apiLaravel(url, method, datosFormulario)
        .then(response => {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message || 'Registro guardado correctamente' });
                limpiar();
                refrescarTabla();
                volverALista();
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al guardar el registro' });
            }
        })
        .catch(error => {
            console.error('Error al guardar:', error);
            Toast.fire({ icon: 'error', title: 'Error al guardar el registro' });
        })
        .then(() => {
            // Equivalente a finally - siempre se ejecuta
            $('#btn_submit').prop('disabled', false).html('Guardar <i class="fas fa-save"></i>');
        });
}

function limpiar() {
    $('#form_main')[0].reset();
    Object.assign(window, {
        idEdit: 0,
        idPersonaRegistro: null,
        pacienteActual: null,
        latitudePaciente: null,
        longitudePaciente: null,
        latitudeHecho: null,
        longitudeHecho: null,
        latitudeViaje: null,
        longitudeViaje: null
    });

    $('#form_persona, #resumen_paciente, #footer_btn, #leyenda_consulta').hide();
    $('.fa-check, .fa-times').hide();
    $('.cont_int').hide();

    if (imagenFoto) imagenFoto.reset();
    $('.select2').val(null).trigger('change');

    // Reinicializar flags de autocompletado
    autocompletadosInicializados.hecho = false;
    autocompletadosInicializados.viaje = false;
    autocompletadosInicializados.paciente = false;
}

// ========== FUNCIONES DE TABLA ==========
function refrescarTabla() {
    const datos = {
        d: $("#d_fil").val(),
        h: $("#h_fil").val(),
        efector: $("#efector_sel_fil").val(),
        id_usuario: $("#usuario_fil").val(),
        reg: $("#region_fil").val(),
        pagina,
        cantidad: 10
    };

    apiLaravel(window.laravelRoutes.registrosDengueFiltrar, 'POST', datos)
        .then(response => {
            if (response.success) {
                dataTable = response.data;
                mostrarTabla(response.data);
                mostrarPaginacion(response.total, response.paginas);
            }
        })
        .catch(error => console.error('Error al cargar datos:', error));
}

function mostrarTabla(datos) {
    const html = datos.map((registro, index) => `
        <tr>
            <td>${registro.IdRegistroDengue}</td>
            <td>${registro.Consulta}</td>
            <td>${registro.servicio}</td>
            <td>${registro.ApellidoNombre}</td>
            <td>${registro.Apellido}, ${registro.Nombre}</td>
            <td>${registro.FFCreacion}</td>
            <td>
                <button class="btn btn-primary btn-sm" onclick="editar(${registro.IdRegistroDengue}, ${index})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="modalEliminar(${registro.IdRegistroDengue})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    $('#table_data').html(html);
}

function mostrarPaginacion(total, totalPaginas) {
    $('#total_info').html(`Mostrando ${dataTable.length} de ${total} registros`);

    let paginacionHtml = '';

    // Botón anterior
    if (pagina > 1) {
        paginacionHtml += `<button class="btn btn-outline-primary" onclick="cambiarPagina(${pagina - 1})">Anterior</button>`;
    }

    // Números de página
    for (let i = Math.max(1, pagina - 2); i <= Math.min(totalPaginas, pagina + 2); i++) {
        const active = i === pagina ? 'btn-primary' : 'btn-outline-primary';
        paginacionHtml += `<button class="btn ${active}" onclick="cambiarPagina(${i})">${i}</button>`;
    }

    // Botón siguiente
    if (pagina < totalPaginas) {
        paginacionHtml += `<button class="btn btn-outline-primary" onclick="cambiarPagina(${pagina + 1})">Siguiente</button>`;
    }

    $('#page-selection').html(paginacionHtml);
}

function cambiarPagina(nuevaPagina) {
    pagina = nuevaPagina;
    refrescarTabla();
}

// ========== FUNCIÓN GUARDAR PACIENTE ==========

function guardarPaciente() {
    // Validar campos requeridos
    if (!$('#modal_nombre').val().trim()) {
        Toast.fire({icon: 'error', title: 'El nombre es requerido'});
        return;
    }

    if (!$('#modal_fecha_nac').val().trim()) {
        Toast.fire({icon: 'error', title: 'La fecha de nacimiento es requerida'});
        return;
    }

    if (!$('#modal_celular').val().trim()) {
        Toast.fire({icon: 'error', title: 'El celular es requerido'});
        return;
    }

    if (!$('#modal_domicilio').val().trim()) {
        Toast.fire({icon: 'error', title: 'El domicilio es requerido'});
        return;
    }

    const pacienteId = $('#paciente_id').val();

    // Preparar FormData para envío
    const formData = new FormData();
    formData.append('dni', $('#dni').val());
    formData.append('modal_nombre', $('#modal_nombre').val());
    formData.append('modal_sexo', $('#modal_sexo').val());
    formData.append('modal_fecha_nac', $('#modal_fecha_nac').val());
    formData.append('modal_celular', $('#modal_celular').val());
    formData.append('modal_domicilio', $('#modal_domicilio').val());
    formData.append('modal_dto', $('#modal_dto').val());
    formData.append('modal_localidad', $('#modal_localidad').val());
    formData.append('modal_barrio', $('#modal_barrio').val());
    formData.append('modal_referencia', $('#modal_referencia').val());
    formData.append('modal_latitud', $('#modal_latitud').val() || '');
    formData.append('modal_longitud', $('#modal_longitud').val() || '');

    $('#btn_guardar_paciente').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    const url = pacienteId ?
        window.laravelRoutes.registrosDengueUpdatePaciente.replace(':id', pacienteId) :
        window.laravelRoutes.registrosDengueStorePaciente;

    const method = pacienteId ? 'PUT' : 'POST';

    // Usar FormData con fetch para envío
    const fetchConfig = {
        method: method,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: formData
    };

    fetch(url, fetchConfig)
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message || 'Paciente guardado correctamente'
                });

                // Actualizar datos globales
                pacienteActual = response.paciente;

                // Actualizar resumen en el formulario principal
                mostrarDatosPaciente(response.paciente);

                // Cerrar modal
                $('#modal_datos_paciente').modal('hide');

                // Mostrar formulario de dengue si es paciente nuevo
                if (!pacienteId) {
                    mostrarFormularioDengue();
                }
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al guardar paciente'
                });
            }
        })
        .catch(error => {
            console.error('Error al guardar paciente:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al guardar paciente'
            });
        })
        .finally(() => {
            $('#btn_guardar_paciente').prop('disabled', false).html('<i class="fas fa-save"></i> <span id="texto_btn_guardar">Guardar Paciente</span>');
        });
}

// ========== FUNCIONES ADICIONALES NECESARIAS ==========

function abrirModalCrearPaciente(dni) {
    // Limpiar formulario del modal
    $('#form_paciente_modal')[0].reset();
    $('#paciente_id').val('');

    // Pre-llenar el DNI desde la búsqueda
    $('#dni').val(dni);

    // Configurar modal para creación
    $('#modal_paciente_titulo').text('Crear Nuevo Paciente - DNI: ' + dni);
    $('#texto_btn_guardar').text('Crear Paciente');

    // Mostrar formulario de edición y ocultar vista
    $('#formulario_paciente').show();
    $('#paciente_existente').hide();
    $('#botones_edicion').show();
    $('#botones_vista').hide();
    $('#btn_cancelar_edicion').hide();

    // Abrir modal
    $('#modal_datos_paciente').modal('show');

    // Forzar inicialización del autocompletado después de que se muestre el modal
    setTimeout(() => {
        autocompletadosInicializados.paciente = false;
        inicializarAutocompletados();
    }, 600);
}

function copiarDomicilioPacienteAHecho() {
    if (pacienteActual) {
        $('#domicilio_hecho').val(pacienteActual.Domicilio || '');
        $('#dto_hecho').val(pacienteActual.Departamento || '');
        $('#localidad_hecho').val(pacienteActual.Localidad || '');
        $('#barrio_hecho').val(pacienteActual.Barrio || '');
        $('#referencia_hecho').val(pacienteActual.Referencias || '');

        // Copiar también coordenadas
        if (latitudePaciente && longitudePaciente) {
            latitudeHecho = latitudePaciente;
            longitudeHecho = longitudePaciente;
            $('#latitud_hecho').val(latitudeHecho);
            $('#longitud_hecho').val(longitudeHecho);

            // Mostrar indicadores de validación
            $("#check_no_dir_hecho").hide();
            $("#check_ok_dir_hecho").show();
        }
    }
}

function mostrarFormulario() {
    $('#card_form').show();
    $('#card_lista').hide();
}

function volverALista() {
    $('#card_form').hide();
    $('#card_lista').show();
    limpiar();
}

function modalEliminar(id = null) {
    if (id !== null) {
        idEliminar = id;
        $("#modal_eliminar").modal();
    }
}

function eliminar() {
    if (idEliminar > 0) {
        apiLaravel(window.laravelRoutes.registrosDengueDestroy.replace(':id', idEliminar), 'DELETE')
            .then(response => {
                Toast.fire({
                    icon: 'success',
                    title: 'Registro eliminado correctamente'
                });
                $("#modal_eliminar").modal("hide");
                refrescarTabla();
            })
            .catch(error => {
                Toast.fire({
                    icon: 'error',
                    title: 'Error al eliminar el registro'
                });
            });
    }
}

function editar(id, ind) {
    // Función básica de editar - se puede expandir según necesidades
    console.log('Editando registro:', id, ind);
    // TODO: Implementar lógica de edición completa
}

// ========== FUNCIONES DE UTILIDAD ==========
function apiLaravel(url, method, data) {
    return new Promise((resolve, reject) => {
        const ajaxConfig = {
            url: url,
            method: method,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json'
        };

        // Configurar datos según el método HTTP
        if (method.toUpperCase() === 'GET') {
            // Para GET, enviar como query parameters
            ajaxConfig.data = data;
        } else {
            // Para POST/PUT/DELETE, enviar como JSON si es objeto, sino como form data
            if (typeof data === 'string') {
                ajaxConfig.data = data;
                ajaxConfig.headers['Content-Type'] = 'application/json';
            } else {
                ajaxConfig.data = data;
                ajaxConfig.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            }
        }

        $.ajax(ajaxConfig)
        .done(response => {
            resolve(response);
        })
        .fail((jqXHR, textStatus, errorThrown) => {
            console.error('Error en apiLaravel:', {
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                responseText: jqXHR.responseText,
                error: errorThrown
            });
            reject({
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                responseText: jqXHR.responseText,
                error: errorThrown
            });
        });
    });
}

// ========== INICIALIZACIÓN ==========
$(document).ready(function() {
    // Configurar Toast
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // Configurar imagen
    imagenFoto = new imageLoad(document.getElementById("imagen"), {
        resize: [1200, 1200],
        onLoad: file => console.log('Imagen cargada:', file.name),
        onError: error => console.error('Error al cargar imagen:', error),
        onDelete: () => { borrarImg = true; }
    });

    // Event listeners principales
    $("#btn_buscar_dni").click(buscarDni);
    $("#btn_submit").click(e => { e.preventDefault(); guardar(); });
    $("#btn_agregar").click(mostrarFormulario);
    $("#btn_volver").click(volverALista);
    $("#btn_limpiar").click(limpiar);
    $("#btn_eliminar_modal").click(eliminar);

    // Event listener para guardar paciente
    $("#btn_guardar_paciente").click(guardarPaciente);

    // Event listener para el checkbox de usar domicilio del paciente
    $("#usar_domicilio_paciente").change(function() {
        if ($(this).is(':checked')) {
            copiarDomicilioPacienteAHecho();
        } else {
            // Limpiar campos del hecho
            $('#domicilio_hecho, #dto_hecho, #localidad_hecho, #barrio_hecho, #referencia_hecho').val('');
            $('#latitud_hecho, #longitud_hecho').val('');
            latitudeHecho = null;
            longitudeHecho = null;
            $("#check_ok_dir_hecho").hide();
            $("#check_no_dir_hecho").show();
        }
    });

    // Event listener para internación
    $("#internacion").change(function() {
        if ($(this).val() === '1') {
            $('.cont_int').show();
        } else {
            $('.cont_int').hide();
            $('#f_ingreso, #f_alta').val('');
        }
    });

    // Modal del paciente
    $('#modal_datos_paciente').on('shown.bs.modal', function() {
        console.log('Modal mostrado, inicializando autocompletado del paciente...');

        setTimeout(() => {
            // Forzar reinicialización del modal_domicilio
            autocompletadosInicializados.paciente = false;

            // Limpiar instancia anterior si existe
            if (autocompletePaciente) {
                google.maps.event.clearInstanceListeners(autocompletePaciente);
                autocompletePaciente = null;
            }

            // Inicializar específicamente el modal_domicilio
            const modalInput = document.getElementById('modal_domicilio');
            if (modalInput) {
                console.log('Forzando inicialización de modal_domicilio...');
                autocompletePaciente = initAutocompletado('modal_domicilio', 'paciente', { lat: null, lng: null });
                autocompletadosInicializados.paciente = !!autocompletePaciente;
                console.log('Resultado inicialización modal:', !!autocompletePaciente);
            } else {
                console.error('modal_domicilio no encontrado en el DOM');
            }
        }, 500);
    });

    // Limpiar datos cuando se cierra el modal
    $('#modal_datos_paciente').on('hidden.bs.modal', function() {
        // Limpiar input del modal
        const modalInput = document.getElementById('modal_domicilio');
        if (modalInput) {
            modalInput.value = '';
        }

        // Limpiar coordenadas globales
        latitudePaciente = null;
        longitudePaciente = null;

        // El autocompletado sigue funcionando, no necesita reinicialización
    });

    // Inicializar
    setTimeout(inicializarAutocompletados, 500);
    refrescarTabla();

    // Configurar librerías opcionales
    if (typeof $.fn.inputmask !== 'undefined') $('[data-mask]').inputmask();
    if (typeof $.fn.select2 !== 'undefined') $('.select2').select2();

    // Configurar datepickers - Tempus Dominus
    if (typeof $.fn.datetimepicker !== 'undefined') {
        console.log('Inicializando datepickers...');

        // Configuración común para todos los datepickers
        const datePickerConfig = {
            format: 'DD/MM/YYYY',
            locale: 'es',
            icons: {
                time: 'far fa-clock',
                date: 'far fa-calendar',
                up: 'fas fa-arrow-up',
                down: 'fas fa-arrow-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'fas fa-calendar-check',
                clear: 'far fa-trash-alt',
                close: 'fas fa-times'
            },
            buttons: {
                showToday: true,
                showClear: true,
                showClose: true
            }
        };

        // Inicializar todos los datepickers
        try {
            $('#fecha_fis').datetimepicker(datePickerConfig);
            $('#f_fis').datetimepicker(datePickerConfig);
            $('#fecha_consulta').datetimepicker(datePickerConfig);
            $('#f_consulta').datetimepicker(datePickerConfig);
            $('#fecha_muestra').datetimepicker(datePickerConfig);
            $('#f_muestra').datetimepicker(datePickerConfig);
            $('#fecha_ingreso').datetimepicker(datePickerConfig);
            $('#f_ingreso').datetimepicker(datePickerConfig);
            $('#fecha_alta').datetimepicker(datePickerConfig);
            $('#f_alta').datetimepicker(datePickerConfig);
            $('#fecha_ant').datetimepicker(datePickerConfig);
            $('#f_ant').datetimepicker(datePickerConfig);

            // Datepicker del modal si existe
            if ($('#modal_fecha_nac').length) {
                $('#modal_fecha_nac').datetimepicker(datePickerConfig);
            }

            console.log('✅ Datepickers inicializados correctamente');
        } catch (error) {
            console.error('❌ Error inicializando datepickers:', error);
        }
    } else {
        console.warn('⚠️ Tempus Dominus datetimepicker no está disponible');
    }

    // Exponer función de debug globalmente
    window.debugModalAutocompletado = debugModalAutocompletado;
    console.log('Para debuggear el modal, ejecuta: debugModalAutocompletado()');
});
