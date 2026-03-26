/**
 * Módulo de Registros de Dengue - JavaScript RENOVADO
 * Nueva implementación con modal de pacientes y domicilios separados
 */

// Variables globales
var data_form;              // Datos del formulario serializados
var dataTable;              // Array con datos de la tabla actual
var pagina = 1;             // Página actual para paginación
var Toast;                  // Instancia de SweetAlert para notificaciones toast
var indEditar = 0;          // Índice del registro en edición en el array dataTable
var idEdit = 0;             // ID del registro que se está editando
var idEliminar = 0;         // ID del registro a eliminar
var dataAutocomplete;       // Datos para autocompletado de usuarios
var listUsuario;            // Instancia de Bloodhound para autocompletado
var idUsuarioBuscar = 0;    // ID del usuario seleccionado para filtrar búsquedas
var idPersonaRegistro = null; // ID del paciente en la base de datos
var esMovil = "";           // Flag para detectar si es dispositivo móvil
var borrarImg = false;      // Flag para indicar si se debe eliminar/actualizar imagen
var clickMap;               // Instancia del mapa de Google Maps

// Variables para Google Maps - SEPARADAS PARA PACIENTE Y HECHO
var autocompletePaciente;      // Autocompletado para domicilio del paciente (modal)
var autocompleteHecho;         // Autocompletado para domicilio del hecho
var autocompleteViaje;         // Autocompletado para antecedente de viaje

// Variables de coordenadas SEPARADAS
var latitudePaciente = null, longitudePaciente = null;    // Coordenadas del domicilio del paciente
var latitudeHecho = null, longitudeHecho = null;          // Coordenadas del domicilio del hecho
var latitudeViaje = null, longitudeViaje = null;          // Coordenadas del viaje

// Control de inicialización de autocompletados
var autocompletadosInicializados = {
    hecho: false,
    viaje: false,
    paciente: false
};

// Variables para mapas
var clickMapHecho;          // Mapa para marcar ubicación del hecho
var clickMapViaje;          // Mapa para marcar ubicación del viaje
var clickMarkerHecho = null;    // Marcador del hecho
var clickMarkerViaje = null;    // Marcador del viaje

// Variable para almacenar datos del paciente actual
var pacienteActual = null;

/**
 * FUNCIÓN CENTRALIZADA: Inicializar todos los autocompletados de Google Maps
 */
function inicializarTodosLosAutocompletados() {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        console.warn('Google Maps no está disponible');
        return;
    }

    console.log('🔄 Iniciando verificación de autocompletados...');

    // 1. Inicializar autocompletado del hecho (domicilio donde ocurrió el dengue)
    if (document.getElementById('domicilio_hecho') && !autocompletadosInicializados.hecho) {
        try {
            initAutocompleteHecho();
            autocompletadosInicializados.hecho = true;
            console.log('✅ Autocompletado del HECHO inicializado');
        } catch (error) {
            console.error('❌ Error inicializando autocompletado del hecho:', error);
        }
    }

    // 2. Inicializar autocompletado de viaje
    if (document.getElementById('ant_viaje') && !autocompletadosInicializados.viaje) {
        try {
            initAutocompleteViajeOnly();
            autocompletadosInicializados.viaje = true;
            console.log('✅ Autocompletado de VIAJE inicializado');
        } catch (error) {
            console.error('❌ Error inicializando autocompletado de viaje:', error);
        }
    }

    // 3. Inicializar autocompletado del modal del paciente (solo si el modal es visible)
    const modalDomicilio = document.getElementById('modal_domicilio');
    const modalVisible = $('#modal_datos_paciente').is(':visible');

    if (modalDomicilio && modalVisible && !autocompletadosInicializados.paciente) {
        try {
            initModalAutocomplete();
            autocompletadosInicializados.paciente = true;
            console.log('✅ Autocompletado del PACIENTE (modal) inicializado');
        } catch (error) {
            console.error('❌ Error inicializando autocompletado del paciente:', error);
        }
    }

    // Mostrar estado final
    console.log('📊 Estado final de autocompletados:', autocompletadosInicializados);
}

/**
 * FUNCIÓN: Reinicializar autocompletados principales (hecho y viaje)
 */
function reinicializarAutocompletadosPrincipales() {
    autocompletadosInicializados.hecho = false;
    autocompletadosInicializados.viaje = false;

    setTimeout(() => {
        inicializarTodosLosAutocompletados();
    }, 300);
}

/**
 * FUNCIÓN MEJORADA: Forzar reinicialización del autocompletado del modal
 */
function forzarReinicicializarModalAutocomplete() {
    console.log('🔄 Forzando reinicialización completa del autocompletado del modal...');

    // Resetear flag
    autocompletadosInicializados.paciente = false;

    // Limpiar instancia previa completamente
    if (autocompletePaciente) {
        const input = document.getElementById('modal_domicilio');
        if (input) {
            console.log('🧹 Limpiando listeners previos del modal');
            google.maps.event.clearInstanceListeners(input);
            // También limpiar eventos jQuery
            $(input).off('input.manual blur.manual');
        }
        autocompletePaciente = null;
    }

    // Esperar un momento para que se limpie completamente
    setTimeout(() => {
        console.log('🚀 Intentando reinicializar autocompletado del modal...');

        // Verificar que el modal esté visible
        if (!$('#modal_datos_paciente').is(':visible')) {
            console.warn('⚠️  Modal no está visible, no se puede reinicializar');
            return;
        }

        // Intentar reinicializar
        const success = initModalAutocomplete();
        if (success) {
            autocompletadosInicializados.paciente = true;
            console.log('✅ Modal autocompletado reinicializado exitosamente');
        } else {
            console.error('❌ Falló la reinicialización del modal autocompletado');
        }
    }, 200); // Aumentar delay para mayor seguridad
}

/**
 * FUNCIÓN DEBUG MEJORADA: Verificar estado completo de autocompletados
 */
function debugAutocompletados() {
    console.log('\n🔍 === DIAGNÓSTICO COMPLETO DE AUTOCOMPLETADOS ===');

    // 1. Verificar Google Maps
    const googleDisponible = typeof google !== 'undefined' && google.maps && google.maps.places;
    console.log('🗺️  Google Maps disponible:', googleDisponible);

    // 2. Verificar campos en DOM
    const campos = {
        domicilio_hecho: !!document.getElementById('domicilio_hecho'),
        ant_viaje: !!document.getElementById('ant_viaje'),
        modal_domicilio: !!document.getElementById('modal_domicilio')
    };
    console.log('📍 Campos encontrados en DOM:', campos);

    // 3. Verificar visibilidad de campos
    const visibilidad = {
        domicilio_hecho: $('#domicilio_hecho').is(':visible'),
        ant_viaje: $('#ant_viaje').is(':visible'),
        modal_domicilio: $('#modal_domicilio').is(':visible')
    };
    console.log('👁️  Campos visibles:', visibilidad);

    // 4. Estado del modal
    const modal = {
        modal_existe: !!$('#modal_datos_paciente').length,
        modal_visible: $('#modal_datos_paciente').is(':visible'),
        modal_abierto: $('#modal_datos_paciente').hasClass('show')
    };
    console.log('🪟 Estado del modal:', modal);

    // 5. Estado de inicialización
    console.log('✅ Flags de inicialización:', autocompletadosInicializados);

    // 6. Instancias reales
    const instancias = {
        autocompleteHecho: !!autocompleteHecho,
        autocompleteViaje: !!autocompleteViaje,
        autocompletePaciente: !!autocompletePaciente
    };
    console.log('🏭 Instancias de autocompletado:', instancias);

    // 7. Análisis de problemas
    console.log('\n🚨 ANÁLISIS DE PROBLEMAS:');

    if (!googleDisponible) {
        console.error('❌ Google Maps no está cargado correctamente');
    }

    if (campos.modal_domicilio && !visibilidad.modal_domicilio) {
        console.warn('⚠️  Campo modal_domicilio existe pero no es visible');
    }

    if (modal.modal_visible && campos.modal_domicilio && !autocompletadosInicializados.paciente) {
        console.error('❌ Modal abierto pero autocompletado del paciente no inicializado');
    }

    if (autocompletadosInicializados.paciente && !instancias.autocompletePaciente) {
        console.error('❌ Flag indica inicializado pero instancia no existe');
    }

    // 8. Recomendaciones
    console.log('\n💡 RECOMENDACIONES:');

    if (modal.modal_visible && !autocompletadosInicializados.paciente) {
        console.log('🔧 Ejecutar: forzarReinicicializarModalAutocomplete()');
    }

    if (!googleDisponible) {
        console.log('🔧 Verificar carga de Google Maps API');
    }

    console.log('================================================\n');
}

// Hacer las funciones disponibles globalmente para debug
window.debugAutocompletados = debugAutocompletados;
window.forzarReinicicializarModalAutocomplete = forzarReinicicializarModalAutocomplete;

// Configuración de carga de imágenes
var imagenFoto = new imageLoad(document.getElementById("imagen"), {
    resize: [1200, 1200],
    onLoad: function(file) {
        console.log('Imagen cargada:', file.name);
    },
    onError: function(error) {
        console.error('Error al cargar imagen:', error);
    },
    onDelete: function() {
        borrarImg = true;
    }
});

/**
 * NUEVA FUNCIÓN: Busca paciente por DNI con lógica de modal
 */
function buscarDni() {
    const dni = $("#dni").val();

    if (!dni || dni.length < 6) {
        Toast.fire({
            icon: 'warning',
            title: 'Ingrese un DNI válido (mínimo 6 dígitos)'
        });
        return;
    }

    $("#btn_buscar_dni").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    apiLaravel(window.laravelRoutes.registrosDengueBuscarDni, 'GET', { dni: dni })
        .then(response => {
            if (response.success) {
                if (response.paciente) {
                    // Paciente encontrado - mostrar modal con datos y opción de editar
                    mostrarDatosPaciente(response.paciente);
                    mostrarFormularioDengue();
                } else {
                    // Paciente no encontrado - abrir modal para crear nuevo
                    abrirModalCrearPaciente(dni);
                }
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Error al buscar paciente'
                });
            }
        })
        .catch(error => {
            console.error('Error en buscarDni:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al buscar paciente'
            });
        })
        .finally(() => {
            $("#btn_buscar_dni").prop('disabled', false).html('<i class="fas fa-search"></i>');
        });
}

/**
 * NUEVA FUNCIÓN: Muestra los datos del paciente encontrado de forma elegante
 */
function mostrarDatosPaciente(paciente) {
    // Almacenar datos del paciente globalmente
    pacienteActual = paciente;

    // Llenar resumen elegante
    $('#resumen_nombre').text(paciente.ApellidoNombre || '-');
    $('#resumen_sexo').text(paciente.Sexo == 1 ? 'F' : 'M');

    if (paciente.FechaNacimiento) {
        const fecha = new Date(paciente.FechaNacimiento);
        const fechaFormateada = fecha.getDate().toString().padStart(2, '0') + '/' +
                               (fecha.getMonth() + 1).toString().padStart(2, '0') + '/' +
                               fecha.getFullYear();
        $('#resumen_fecha_nac').text(fechaFormateada);
    } else {
        $('#resumen_fecha_nac').text('-');
    }

    $('#resumen_celular').text(paciente.Celular || '-');

    // Domicilio completo del paciente
    let domicilioCompleto = '';
    if (paciente.Domicilio) {
        domicilioCompleto = paciente.Domicilio;
        if (paciente.Localidad) domicilioCompleto += ', ' + paciente.Localidad;
        if (paciente.Departamento) domicilioCompleto += ', ' + paciente.Departamento;
    }
    $('#resumen_domicilio_completo').text(domicilioCompleto || 'Sin domicilio registrado');

    // Llenar campos ocultos para el formulario principal
    $('#paciente_id_form').val(paciente.IdPacienteRegTrab);
    $('#nombre').val(paciente.ApellidoNombre);
    $('#sexo').val(paciente.Sexo);

    if (paciente.FechaNacimiento) {
        const fecha = new Date(paciente.FechaNacimiento);
        const fechaFormateada = fecha.getDate().toString().padStart(2, '0') + '/' +
                               (fecha.getMonth() + 1).toString().padStart(2, '0') + '/' +
                               fecha.getFullYear();
        $('#fecha_nac').val(fechaFormateada);
    }

    $('#celular').val(paciente.Celular);
    $('#domicilio').val(paciente.Domicilio);
    $('#dto').val(paciente.Departamento);
    $('#localidad').val(paciente.Localidad);
    $('#barrio').val(paciente.Barrio);
    $('#referencia').val(paciente.Referencias);

    // Coordenadas del paciente
    if (paciente.Latitud && paciente.Longitud) {
        latitudePaciente = parseFloat(paciente.Latitud);
        longitudePaciente = parseFloat(paciente.Longitud);
        $('#latitud').val(latitudePaciente);
        $('#longitud').val(longitudePaciente);
    }

    // Mostrar resumen del paciente
    $('#resumen_paciente').show();

    // Almacenar ID para el formulario
    idPersonaRegistro = paciente.IdPacienteRegTrab;
}

/**
 * NUEVA FUNCIÓN: Abre modal para crear nuevo paciente
 */
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
        forzarReinicicializarModalAutocomplete();
    }, 600);
}

/**
 * NUEVA FUNCIÓN: Muestra el formulario principal para el caso de dengue
 */
function mostrarFormularioDengue() {
    $("#form_persona").show();
    $("#footer_btn").show();

    // Pre-llenar domicilio del hecho con datos del paciente si el checkbox está marcado
    if ($('#usar_domicilio_paciente').is(':checked')) {
        copiarDomicilioPacienteAHecho();
    }

    // Inicializar autocompletado para campos del hecho
    setTimeout(() => {
        inicializarTodosLosAutocompletados();
    }, 100);
}

/**
 * NUEVA FUNCIÓN: Copia domicilio del paciente al domicilio del hecho
 */
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

/**
 * NUEVA FUNCIÓN: Ver/Editar datos del paciente desde el resumen
 */
function verEditarPaciente() {
    if (!pacienteActual) return;

    // Llenar datos en el modal para visualización
    llenarModalConDatosPaciente(pacienteActual);

    // Configurar modal para visualización
    $('#modal_paciente_titulo').html('<i class="fas fa-user-circle mr-2"></i>Datos del Paciente - DNI: ' + pacienteActual.DNI);

    // Mostrar vista de datos existentes
    $('#formulario_paciente').hide();
    $('#paciente_existente').show();
    $('#botones_edicion').hide();
    $('#botones_vista').show();

    // Abrir modal
    $('#modal_datos_paciente').modal('show');
}

// Función auxiliar para asegurar que el autocompletado se inicialize cuando se necesite
function asegurarAutocompletadoModal() {
    if ($('#modal_datos_paciente').is(':visible') && $('#modal_domicilio').is(':visible')) {
        if (!autocompletadosInicializados.paciente || !autocompletePaciente) {
            console.log('Asegurando inicialización del autocompletado del modal...');
            forzarReinicicializarModalAutocomplete();
        }
    }
}

/**
 * NUEVA FUNCIÓN: Llena el modal con datos del paciente para visualización
 */
function llenarModalConDatosPaciente(paciente) {
    // Llenar datos para visualización
    $('#display_nombre').text(paciente.ApellidoNombre || '-');
    $('#display_sexo').text(paciente.Sexo == 1 ? 'Femenino' : 'Masculino');

    if (paciente.FechaNacimiento) {
        const fecha = new Date(paciente.FechaNacimiento);
        const fechaFormateada = fecha.getDate().toString().padStart(2, '0') + '/' +
                               (fecha.getMonth() + 1).toString().padStart(2, '0') + '/' +
                               fecha.getFullYear();
        $('#display_fecha_nac').text(fechaFormateada);
    } else {
        $('#display_fecha_nac').text('-');
    }

    $('#display_celular').text(paciente.Celular || '-');
    $('#display_domicilio').text(paciente.Domicilio || '-');
    $('#display_departamento').text(paciente.Departamento || '-');
    $('#display_localidad').text(paciente.Localidad || '-');
    $('#display_barrio').text(paciente.Barrio || '-');
    $('#display_referencias').text(paciente.Referencias || '-');

    // También llenar formulario para futuras ediciones
    $('#modal_nombre').val(paciente.ApellidoNombre || '');
    $('#modal_sexo').val(paciente.Sexo || '0');

    if (paciente.FechaNacimiento) {
        const fecha = new Date(paciente.FechaNacimiento);
        const fechaFormateada = fecha.getDate().toString().padStart(2, '0') + '/' +
                               (fecha.getMonth() + 1).toString().padStart(2, '0') + '/' +
                               fecha.getFullYear();
        $('#modal_fecha_nac').val(fechaFormateada);
    }

    $('#modal_celular').val(paciente.Celular || '');
    $('#modal_domicilio').val(paciente.Domicilio || '');
    $('#modal_dto').val(paciente.Departamento || '');
    $('#modal_localidad').val(paciente.Localidad || '');
    $('#modal_barrio').val(paciente.Barrio || '');
    $('#modal_referencia').val(paciente.Referencias || '');
    $('#paciente_id').val(paciente.IdPacienteRegTrab);

    // Coordenadas
    if (paciente.Latitud && paciente.Longitud) {
        $('#modal_latitud').val(paciente.Latitud);
        $('#modal_longitud').val(paciente.Longitud);
    }
}

/**
 * NUEVA FUNCIÓN: Cambiar modal a modo edición
 */
function editarPacienteModal() {
    $('#formulario_paciente').show();
    $('#paciente_existente').hide();
    $('#botones_edicion').show();
    $('#botones_vista').hide();
    $('#btn_cancelar_edicion').show();

    $('#modal_paciente_titulo').html('<i class="fas fa-user-edit mr-2"></i>Editar Datos del Paciente');
    $('#texto_btn_guardar').text('Actualizar Paciente');

    // Inicializar autocompletado en el modal
    setTimeout(initModalAutocomplete, 300);
}

/**
 * NUEVA FUNCIÓN: Cancelar edición y volver a vista
 */
function cancelarEdicionPaciente() {
    $('#formulario_paciente').hide();
    $('#paciente_existente').show();
    $('#botones_edicion').hide();
    $('#botones_vista').show();
    $('#btn_cancelar_edicion').hide();

    $('#modal_paciente_titulo').html('<i class="fas fa-user-circle mr-2"></i>Datos del Paciente - DNI: ' + pacienteActual.DNI);
}

/**
 * NUEVA FUNCIÓN: Guardar/Actualizar datos del paciente
 */
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

    // Obtener coordenadas (pueden estar vacías si se escribió manualmente)
    const latitud = $('#modal_latitud').val() || null;
    const longitud = $('#modal_longitud').val() || null;

    const datos = {
        dni: $('#dni').val(),
        modal_nombre: $('#modal_nombre').val(),
        modal_sexo: $('#modal_sexo').val(),
        modal_fecha_nac: $('#modal_fecha_nac').val(),
        modal_celular: $('#modal_celular').val(),
        modal_domicilio: $('#modal_domicilio').val(),
        modal_dto: $('#modal_dto').val(),
        modal_localidad: $('#modal_localidad').val(),
        modal_barrio: $('#modal_barrio').val(),
        modal_referencia: $('#modal_referencia').val(),
        modal_latitud: latitud,
        modal_longitud: longitud
    };

    $('#btn_guardar_paciente').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    const url = pacienteId ?
        window.laravelRoutes.registrosDengueUpdatePaciente.replace(':id', pacienteId) :
        window.laravelRoutes.registrosDengueStorePaciente;

    const method = pacienteId ? 'PUT' : 'POST';

    apiLaravel(url, method, datos)
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message
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

/**
 * NUEVA FUNCIÓN: Inicializar autocompletado para domicilio del hecho
 */
function initAutocompleteHecho() {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        console.warn('Google Maps no está disponible para autocompletado del hecho');
        return;
    }

    try {
        const inputElement = document.getElementById('domicilio_hecho');
        if (!inputElement) {
            console.error('Campo domicilio_hecho no encontrado');
            return;
        }

        // Limpiar listener previo si existe
        if (autocompleteHecho) {
            google.maps.event.clearInstanceListeners(inputElement);
        }

        autocompleteHecho = new google.maps.places.Autocomplete(
            inputElement,
            {
                types: ['geocode'],
                componentRestrictions: { country: 'AR' },
                strictBounds: false,  // Permitir direcciones fuera de los límites
                fields: ['formatted_address', 'geometry', 'address_components']
            }
        );

        // Configurar para permitir escritura libre
        inputElement.addEventListener('keydown', function(e) {
            // No bloquear ninguna tecla, permitir escritura libre
            e.stopPropagation();
        });

        // Permitir escritura libre sin restricciones
        inputElement.addEventListener('input', function(e) {
            const valor = e.target.value;
            if (valor.length > 0) {
                // Resetear indicadores mientras escribe
                $("#check_no_dir_hecho").hide();
                $("#check_ok_dir_hecho").hide();
                // Limpiar coordenadas previas si escribe encima
                if (latitudeHecho) {
                    latitudeHecho = null;
                    longitudeHecho = null;
                    $('#latitud_hecho').val('');
                    $('#longitud_hecho').val('');
                }
            } else {
                $("#check_no_dir_hecho").show();
                $("#check_ok_dir_hecho").hide();
            }
        });

        autocompleteHecho.addListener('place_changed', function() {
            const place = autocompleteHecho.getPlace();
            if (!place.geometry) {
                $("#check_no_dir_hecho").show();
                $("#check_ok_dir_hecho").hide();
                return;
            }

            latitudeHecho = place.geometry.location.lat();
            longitudeHecho = place.geometry.location.lng();

            // Actualizar campos ocultos
            $('#latitud_hecho').val(latitudeHecho);
            $('#longitud_hecho').val(longitudeHecho);

            // Extraer información de la dirección
            let departamento = '';
            let localidad = '';

            place.address_components.forEach(component => {
                const types = component.types;
                if (types.includes('administrative_area_level_2')) {
                    departamento = component.long_name;
                } else if (types.includes('locality') || types.includes('administrative_area_level_3')) {
                    localidad = component.long_name;
                }
            });

            // Rellenar campos automáticamente si están vacíos
            if (departamento && $('#dto_hecho').val() === '') {
                $('#dto_hecho').val(departamento);
            }
            if (localidad && $('#localidad_hecho').val() === '') {
                $('#localidad_hecho').val(localidad);
            }

            // Mostrar indicador de éxito
            $("#check_no_dir_hecho").hide();
            $("#check_ok_dir_hecho").show();

            console.log('Domicilio del hecho seleccionado:', place.formatted_address);
            console.log('Coordenadas del hecho:', latitudeHecho, longitudeHecho);
            console.log('Coordenadas del hecho:', latitudeHecho, longitudeHecho);
        });

    } catch (error) {
        console.error('Error al inicializar autocompletado del hecho:', error);
    }
}

/**
 * FUNCIÓN EXISTENTE MEJORADA: Inicializar autocompletado para antecedente de viaje
 */
function initAutocompleteViajeOnly() {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        console.warn('Google Maps no está disponible para autocompletado de viaje');
        return;
    }

    try {
        const inputElement = document.getElementById('ant_viaje');
        if (!inputElement) {
            console.error('Campo ant_viaje no encontrado');
            return;
        }

        // Limpiar listener previo si existe
        if (autocompleteViaje) {
            google.maps.event.clearInstanceListeners(inputElement);
        }

        autocompleteViaje = new google.maps.places.Autocomplete(
            inputElement,
            {
                types: ['geocode'],
                componentRestrictions: { country: 'AR' },
                strictBounds: false,  // Permitir direcciones fuera de los límites
                fields: ['formatted_address', 'geometry', 'address_components']
            }
        );

        // Configurar para permitir escritura libre
        inputElement.addEventListener('keydown', function(e) {
            // No bloquear ninguna tecla, permitir escritura libre
            e.stopPropagation();
        });

        // Permitir escritura libre sin restricciones
        inputElement.addEventListener('input', function(e) {
            const valor = e.target.value;
            if (valor.length > 0) {
                // Resetear indicadores mientras escribe
                $("#check_no_dir_viaje").hide();
                $("#check_ok_dir_viaje").hide();
                // Limpiar coordenadas previas si escribe encima
                if (latitudeViaje) {
                    latitudeViaje = null;
                    longitudeViaje = null;
                }
            } else {
                $("#check_no_dir_viaje").show();
                $("#check_ok_dir_viaje").hide();
            }
        });

        autocompleteViaje.addListener('place_changed', function() {
            const place = autocompleteViaje.getPlace();
            if (!place.geometry) return;

            latitudeViaje = place.geometry.location.lat();
            longitudeViaje = place.geometry.location.lng();

            $("#check_no_dir_viaje").hide();
            $("#check_ok_dir_viaje").show();

            console.log('Lugar de viaje seleccionado:', place.formatted_address);
            console.log('Coordenadas de viaje:', latitudeViaje, longitudeViaje);
        });

    } catch (error) {
        console.error('Error al inicializar autocompletado de viaje:', error);
    }
}

/**
 * FUNCIÓN MEJORADA: Inicializar autocompletado en el modal del paciente
 * Esta función ahora es más robusta y maneja mejor la inicialización
 */
function initModalAutocomplete() {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        console.warn('🚨 Google Maps no está disponible para autocompletado del paciente');
        return false;
    }

    try {
        const inputElement = document.getElementById('modal_domicilio');
        if (!inputElement) {
            console.error('🚨 Campo modal_domicilio no encontrado en el DOM');
            return false;
        }

        // Verificar que el input sea visible
        if (!$(inputElement).is(':visible')) {
            console.warn('⚠️  Campo modal_domicilio no es visible, no se puede inicializar');
            return false;
        }

        console.log('🔧 Inicializando autocompletado del modal del paciente...');

        // Limpiar autocompletado previo si existe
        if (autocompletePaciente) {
            console.log('🧹 Limpiando autocompletado previo del paciente');
            google.maps.event.clearInstanceListeners(inputElement);
            autocompletePaciente = null;
        }

        // Crear nuevo autocompletado
        autocompletePaciente = new google.maps.places.Autocomplete(
            inputElement,
            {
                types: ['geocode'],
                componentRestrictions: { country: 'AR' },
                strictBounds: false,  // Permitir direcciones fuera de los límites
                fields: ['formatted_address', 'geometry', 'address_components']
            }
        );

        // Configurar para permitir escritura libre
        inputElement.addEventListener('keydown', function(e) {
            // No bloquear ninguna tecla, permitir escritura libre
            e.stopPropagation();
        });

        // Permitir escritura libre sin restricciones
        inputElement.addEventListener('input', function(e) {
            const valor = e.target.value;
            if (valor.length > 0) {
                // Resetear indicadores mientras escribe
                $("#modal_check_no_dir").hide();
                $("#modal_check_ok_dir").hide();
                // Limpiar coordenadas previas si escribe encima
                if (latitudePaciente) {
                    latitudePaciente = null;
                    longitudePaciente = null;
                    $('#modal_latitud').val('');
                    $('#modal_longitud').val('');
                }
            } else {
                $("#modal_check_no_dir").show();
                $("#modal_check_ok_dir").hide();
            }
        });

        // Agregar listener para cuando se selecciona un lugar
        autocompletePaciente.addListener('place_changed', function() {
            console.log('📍 Lugar seleccionado en modal del paciente');
            const place = autocompletePaciente.getPlace();

            if (!place.geometry) {
                console.warn('⚠️  Lugar sin geometría válida en modal del paciente');
                $("#modal_check_no_dir").show();
                $("#modal_check_ok_dir").hide();
                $('#modal_latitud').val('');
                $('#modal_longitud').val('');
                return;
            }

            // Coordenadas del paciente
            latitudePaciente = place.geometry.location.lat();
            longitudePaciente = place.geometry.location.lng();

            $('#modal_latitud').val(latitudePaciente);
            $('#modal_longitud').val(longitudePaciente);

            console.log('🗺️  Coordenadas del paciente obtenidas:', latitudePaciente, longitudePaciente);

            // Extraer información de la dirección
            let departamento = '';
            let localidad = '';

            place.address_components.forEach(component => {
                const types = component.types;
                if (types.includes('administrative_area_level_2')) {
                    departamento = component.long_name;
                } else if (types.includes('locality') || types.includes('administrative_area_level_3')) {
                    localidad = component.long_name;
                }
            });

            // Rellenar campos automáticamente si están vacíos
            if (departamento && $('#modal_dto').val() === '') {
                $('#modal_dto').val(departamento);
                console.log('📍 Departamento auto-rellenado:', departamento);
            }
            if (localidad && $('#modal_localidad').val() === '') {
                $('#modal_localidad').val(localidad);
                console.log('📍 Localidad auto-rellenada:', localidad);
            }

            // Mostrar indicador de éxito
            $("#modal_check_no_dir").hide();
            $("#modal_check_ok_dir").show();

            console.log('✅ Domicilio del paciente procesado:', place.formatted_address);
        });

        // Manejar validación cuando se pierde el foco
        inputElement.addEventListener('blur', function() {
            const valor = this.value.trim();
            if (valor.length > 0) {
                if (!$('#modal_latitud').val()) {
                    // Dirección manual sin coordenadas
                    $("#modal_check_no_dir").hide();
                    $("#modal_check_ok_dir").hide();
                    $(this).attr('title', 'Dirección manual - Sin coordenadas GPS');
                    console.log('✍️  Domicilio manual:', valor);
                } else {
                    // Dirección con coordenadas de Google Maps
                    $(this).removeAttr('title');
                }
            }
        });

        // Configurar placeholder útil
        $('#modal_domicilio').attr('placeholder', 'Escriba la dirección y seleccione de la lista...');

        // Verificación final
        if (autocompletePaciente && inputElement) {
            console.log('✅ Autocompletado del modal del paciente inicializado exitosamente');
            return true;
        } else {
            console.error('❌ Falló la inicialización del autocompletado del modal');
            return false;
        }

    } catch (error) {
        console.error('💥 Error crítico al inicializar autocompletado del paciente:', error);
        autocompletePaciente = null;
        return false;
    }
}

// CONTINÚA EN EL SIGUIENTE ARCHIVO (parte 2)...
// PARTE 2 - Continuación de registros-dengue-nuevo.js

/**
 * FUNCIÓN MEJORADA: Guardar registro de dengue
 */
function guardar() {
    // Validar que existe un paciente
    if (!$('#paciente_id_form').val()) {
        Toast.fire({
            icon: 'error',
            title: 'Debe buscar y registrar un paciente primero'
        });
        return;
    }

    // Validar campos requeridos del hecho
    if (!$('#domicilio_hecho').val().trim()) {
        Toast.fire({
            icon: 'error',
            title: 'El domicilio donde ocurrió el hecho es requerido'
        });
        return;
    }

    if (!$('#dto_hecho').val().trim()) {
        Toast.fire({
            icon: 'error',
            title: 'El departamento del hecho es requerido'
        });
        return;
    }

    if (!$('#localidad_hecho').val().trim()) {
        Toast.fire({
            icon: 'error',
            title: 'La localidad del hecho es requerida'
        });
        return;
    }

    // Preparar datos para envío
    const datosFormulario = {
        // Datos del paciente (van como hidden inputs)
        paciente_id_form: $('#paciente_id_form').val(),

        // Datos epidemiológicos
        semana: $('#semana').val(),
        f_fis: $('#f_fis').val(),
        f_consulta: $('#f_consulta').val(),
        f_muestra: $('#f_muestra').val(),

        // Internación
        internacion: $('#internacion').val(),
        f_ingreso: $('#f_ingreso').val(),
        f_alta: $('#f_alta').val(),

        // Domicilio del hecho (NUEVO)
        domicilio_hecho: $('#domicilio_hecho').val(),
        dto_hecho: $('#dto_hecho').val(),
        localidad_hecho: $('#localidad_hecho').val(),
        barrio_hecho: $('#barrio_hecho').val(),
        referencia_hecho: $('#referencia_hecho').val(),
        latitud_hecho: latitudeHecho,
        longitud_hecho: longitudeHecho,

        // Tests de laboratorio
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

        // Otros datos
        ant_vacu: $('#ant_vacu').val() === '1',
        obito: $('#obito').val() === '1',
        comor: $('#comor').val(),
        obs: $('#obs').val(),

        // Antecedente de viaje
        ant_viaje: $('#ant_viaje').val(),
        f_ant: $('#f_ant').val(),
        latitud_viaje: latitudeViaje,
        longitud_viaje: longitudeViaje,

        // Efector
        efector_sel: $('#efector_sel').val(),

        // Imagen (si existe)
        imagen_base64: imagenFoto && imagenFoto.getBase64() ? imagenFoto.getBase64() : null
    };

    // Mostrar loading
    $('#btn_submit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    const url = idEdit > 0 ?
        window.laravelRoutes.registrosDengueUpdate.replace(':id', idEdit) :
        window.laravelRoutes.registrosDengueStore;

    const method = idEdit > 0 ? 'PUT' : 'POST';

    apiLaravel(url, method, datosFormulario)
        .then(response => {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message || 'Registro guardado correctamente'
                });

                // Limpiar formulario y refrescar tabla
                limpiar();
                refrescarTabla();
                volverALista();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al guardar el registro'
                });
            }
        })
        .catch(error => {
            console.error('Error al guardar:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al guardar el registro'
            });
        })
        .finally(() => {
            $('#btn_submit').prop('disabled', false).html('Guardar <i class="fas fa-save"></i>');
        });
}

/**
 * FUNCIÓN MEJORADA: Editar registro existente
 */
function editar(id, ind) {
    if (!id) return;

    idEdit = id;
    indEditar = ind;

    // Cambiar a vista de formulario
    mostrarFormulario();

    // Cargar datos del registro
    apiLaravel(window.laravelRoutes.registrosDengueGet.replace(':id', id), 'GET')
        .then(response => {
            if (response.success && response.data) {
                const registro = response.data;

                // Cargar datos del paciente primero
                if (registro.paciente) {
                    // Pre-llenar DNI y simular búsqueda
                    $('#dni').val(registro.paciente.DNI);
                    mostrarDatosPaciente(registro.paciente);
                    mostrarFormularioDengue();
                }

                // Llenar datos epidemiológicos
                $('#semana').val(registro.Semana);
                $('#f_fis').val(formatearFecha(registro.Fis));
                $('#f_consulta').val(formatearFecha(registro.Consulta));
                $('#f_muestra').val(formatearFecha(registro.FechaTomaMuestra));

                // Internación
                $('#internacion').val(registro.Internacion ? '1' : '0');
                $('#f_ingreso').val(formatearFecha(registro.FechaIngreso));
                $('#f_alta').val(formatearFecha(registro.FechaAlta));

                // Datos del hecho (NUEVOS CAMPOS)
                $('#domicilio_hecho').val(registro.DomicilioHecho || '');
                $('#dto_hecho').val(registro.DepartamentoHecho || '');
                $('#localidad_hecho').val(registro.LocalidadHecho || '');
                $('#barrio_hecho').val(registro.BarrioHecho || '');
                $('#referencia_hecho').val(registro.ReferenciasHecho || '');

                if (registro.LatitudHecho && registro.LongitudHecho) {
                    latitudeHecho = parseFloat(registro.LatitudHecho);
                    longitudeHecho = parseFloat(registro.LongitudHecho);
                    $('#latitud_hecho').val(latitudeHecho);
                    $('#longitud_hecho').val(longitudeHecho);
                    $("#check_ok_dir_hecho").show();
                    $("#check_no_dir_hecho").hide();
                }

                // Tests de laboratorio
                $('#laboratorio').val(registro.Laboratorio);
                $('#testAgNS1').val(registro.TestAgNS1);
                $('#tipoNs1').val(registro.TipoNs1);
                $('#testIgM').val(registro.TestIgM);
                $('#testIGG').val(registro.TestIGG);
                $('#testPCR').val(registro.TestPCR);
                $('#testRapidoIgG').val(registro.TestRapidoIgG);
                $('#testRapidoIgM').val(registro.TestRapidoIgM);
                $('#testChikungunya').val(registro.TestChikungunya);
                $('#testZika').val(registro.TestZika);

                // Otros datos
                $('#ant_vacu').val(registro.AntVacunacion ? '1' : '0');
                $('#obito').val(registro.Obito ? '1' : '0');
                $('#comor').val(registro.Comorbilidad);
                $('#obs').val(registro.Observaciones);

                // Antecedente de viaje
                $('#ant_viaje').val(registro.AntViaje);
                $('#f_ant').val(formatearFecha(registro.FechaAnt));

                if (registro.LatitudAnt && registro.LongitudAnt) {
                    latitudeViaje = parseFloat(registro.LatitudAnt);
                    longitudeViaje = parseFloat(registro.LongitudAnt);
                    $("#check_ok_dir_viaje").show();
                    $("#check_no_dir_viaje").hide();
                }

                // Efector
                if (registro.Efector_Id) {
                    $('#efector_sel').val(registro.Efector_Id);
                }

                // Imagen
                if (registro.ImagenFicha) {
                    // Manejar imagen existente
                    console.log('Registro tiene imagen:', registro.ImagenFicha);
                }

                // Reinicializar autocompletado
                reinicializarAutocompletadosPrincipales();

            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo cargar el registro'
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar registro:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error al cargar el registro'
            });
        });
}

/**
 * FUNCIÓN MEJORADA: Limpiar formulario
 */
function limpiar() {
    // Limpiar formulario principal
    $('#form_main')[0].reset();

    // Limpiar variables globales
    idEdit = 0;
    idPersonaRegistro = null;
    pacienteActual = null;

    // Limpiar coordenadas
    latitudePaciente = null;
    longitudePaciente = null;
    latitudeHecho = null;
    longitudeHecho = null;
    latitudeViaje = null;
    longitudeViaje = null;

    // Ocultar elementos
    $('#form_persona').hide();
    $('#resumen_paciente').hide();
    $('#footer_btn').hide();
    $('#leyenda_consulta').hide();

    // Limpiar indicadores de validación
    $('.fa-check, .fa-times').hide();

    // Limpiar imagen
    if (imagenFoto) {
        imagenFoto.reset();
    }

    // Resetear selectores
    $('.select2').val(null).trigger('change');

    // Ocultar elementos de internación
    $('.cont_int').hide();

    console.log('Formulario limpiado completamente');
}

/**
 * FUNCIÓN NUEVA: Formatear fecha para mostrar
 */
function formatearFecha(fecha) {
    if (!fecha) return '';

    const date = new Date(fecha);
    if (isNaN(date.getTime())) return '';

    return date.getDate().toString().padStart(2, '0') + '/' +
           (date.getMonth() + 1).toString().padStart(2, '0') + '/' +
           date.getFullYear();
}

/**
 * FUNCIÓN NUEVA: Ubicación actual para el hecho
 */
function ubicacionActualHecho() {
    if (navigator.geolocation) {
        $("#btn_ubicacion_actual").html('<i class="fas fa-spinner fa-spin"></i> Obteniendo...');

        navigator.geolocation.getCurrentPosition(function(position) {
            latitudeHecho = position.coords.latitude;
            longitudeHecho = position.coords.longitude;

            $('#latitud_hecho').val(latitudeHecho);
            $('#longitud_hecho').val(longitudeHecho);

            // Realizar geocodificación inversa
            const geocoder = new google.maps.Geocoder();
            const latlng = { lat: latitudeHecho, lng: longitudeHecho };

            geocoder.geocode({ location: latlng }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    $('#domicilio_hecho').val(results[0].formatted_address);

                    // Extraer departamento y localidad
                    results[0].address_components.forEach(component => {
                        const types = component.types;
                        if (types.includes('administrative_area_level_2')) {
                            $('#dto_hecho').val(component.long_name);
                        } else if (types.includes('locality') || types.includes('administrative_area_level_3')) {
                            $('#localidad_hecho').val(component.long_name);
                        }
                    });

                    $("#check_no_dir_hecho").hide();
                    $("#check_ok_dir_hecho").show();
                }

                $("#btn_ubicacion_actual").html('Ubicación Actual <i class="fas fa-map-marker"></i>');
            });

        }, function(error) {
            $("#error_gps").show();
            $("#btn_ubicacion_actual").html('Ubicación Actual <i class="fas fa-map-marker"></i>');
            console.error('Error obteniendo ubicación:', error);
        });
    } else {
        Toast.fire({
            icon: 'error',
            title: 'Geolocalización no soportada por este navegador'
        });
    }
}

/**
 * FUNCIÓN NUEVA: Marcar en mapa para el hecho
 */
function marcarMapaHecho() {
    $('#clickMapHecho').toggle();

    if ($('#clickMapHecho').is(':visible')) {
        if (!clickMapHecho) {
            clickMapHecho = new google.maps.Map(document.getElementById('clickMapHecho'), {
                zoom: 13,
                center: { lat: -29.4133, lng: -66.8567 } // San Fernando del Valle de Catamarca
            });

            clickMapHecho.addListener('click', function(event) {
                if (clickMarkerHecho) {
                    clickMarkerHecho.setMap(null);
                }

                clickMarkerHecho = new google.maps.Marker({
                    position: event.latLng,
                    map: clickMapHecho,
                    title: 'Ubicación del Hecho'
                });

                latitudeHecho = event.latLng.lat();
                longitudeHecho = event.latLng.lng();

                $('#latitud_hecho').val(latitudeHecho);
                $('#longitud_hecho').val(longitudeHecho);

                // Geocodificación inversa
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: event.latLng }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        $('#domicilio_hecho').val(results[0].formatted_address);
                        $("#check_no_dir_hecho").hide();
                        $("#check_ok_dir_hecho").show();
                    }
                });
            });
        }
    }
}

/**
 * Funciones existentes que se mantienen (filtros, paginación, etc.)
 */

// Función para mostrar/ocultar formulario vs lista
function mostrarFormulario() {
    $('#card_form').show();
    $('#card_lista').hide();
}

function volverALista() {
    $('#card_form').hide();
    $('#card_lista').show();
    limpiar();
}

// Eliminar registro
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

// Función para refrescar tabla (mantener la existente pero agregar validaciones)
function refrescarTabla() {
    const datos = {
        d: $("#d_fil").val(),
        h: $("#h_fil").val(),
        efector: $("#efector_sel_fil").val(),
        id_usuario: $("#usuario_fil").val(),
        reg: $("#region_fil").val(),
        pagina: pagina,
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
        .catch(error => {
            console.error('Error al cargar datos:', error);
        });
}

function mostrarTabla(datos) {
    let html = '';

    datos.forEach((registro, index) => {
        html += `
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
        `;
    });

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

// Event Listeners cuando el documento esté listo
$(document).ready(function() {
    // Configurar Toast para notificaciones
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // Event listeners principales
    $("#btn_buscar_dni").click(buscarDni);
    $("#btn_agregar").click(mostrarFormulario);
    $("#btn_volver").click(volverALista);
    $("#btn_limpiar").click(limpiar);
    $("#btn_submit").click(function(e) {
        e.preventDefault();
        guardar();
    });

    // Event listeners del modal de paciente
    $("#btn_ver_editar_paciente").click(verEditarPaciente);
    $("#btn_editar_paciente").click(editarPacienteModal);
    $("#btn_cancelar_edicion").click(cancelarEdicionPaciente);
    $("#btn_guardar_paciente").click(guardarPaciente);

    // Event listener para asegurar autocompletado cuando se hace clic en el campo
    $(document).on('focus', '#modal_domicilio', function() {
        console.log('Campo modal_domicilio enfocado - verificando autocompletado...');
        asegurarAutocompletadoModal();
    });

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

    // Event listener para eliminar
    $("#btn_eliminar_modal").click(eliminar);

    // Inicializar autocompletado cuando se muestre el modal del paciente
    $('#modal_datos_paciente').on('shown.bs.modal', function() {
        console.log('Modal del paciente mostrado - inicializando autocompletado');
        setTimeout(function() {
            // Resetear flag y reinicializar solo el modal
            autocompletadosInicializados.paciente = false;
            const modalInput = document.getElementById('modal_domicilio');
            if (modalInput) {
                console.log('Campo modal_domicilio encontrado, inicializando...');
                initModalAutocomplete();
                autocompletadosInicializados.paciente = true;
            } else {
                console.error('Campo modal_domicilio no encontrado en el modal');
            }
        }, 500); // Aumentar delay para asegurar que el modal esté completamente renderizado
    });

    // Limpiar datos cuando se cierra el modal
    $('#modal_datos_paciente').on('hidden.bs.modal', function() {
        // Limpiar autocompletado previo
        if (autocompletePaciente) {
            google.maps.event.clearInstanceListeners(document.getElementById('modal_domicilio'));
            autocompletePaciente = null;
        }
        // Limpiar coordenadas globales
        latitudePaciente = null;
        longitudePaciente = null;
        // Resetear flag para permitir reinicialización
        autocompletadosInicializados.paciente = false;
    });

    // Inicializar autocompletados de Google Maps
    setTimeout(() => {
        inicializarTodosLosAutocompletados();
    }, 500);

    // Inicializar elementos
    refrescarTabla();

    // Configurar máscaras de fecha si existe la librería
    if (typeof $.fn.inputmask !== 'undefined') {
        $('[data-mask]').inputmask();
    }

    // Configurar datepickers
    if (typeof $.fn.datetimepicker !== 'undefined') {
        $('.datetimepicker-input').datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'es'
        });
    }

    // Configurar select2 si existe
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2();
    }

    console.log('Módulo de Registros de Dengue inicializado correctamente');
});

// Funciones de utilidad adicionales
function convertirFechaFormato(fecha) {
    if (!fecha) return '';

    const partes = fecha.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return fecha;
}

// Función para API Laravel (debe estar definida globalmente)
function apiLaravel(url, method, data) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            method: method,
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                resolve(response);
            },
            error: function(xhr, status, error) {
                reject(xhr.responseJSON || { message: error });
            }
        });
    });
}

console.log('Registros de Dengue - JavaScript cargado completamente');
