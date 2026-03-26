/**
 * Módulo de Registros de Dengue - JavaScript
 * Adaptado para Laravel desde el proyecto legacy
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

// Variables para Google Maps
var autocomplete;           // Instancia de autocompletado de Google Places
var clickMarker = null;     // Marcador actual en el mapa de domicilio
var latitude = null;
var longitude = null;
var autocompleteViaje;
var clickMarkerViaje = null;
var latitudeViaje = null;
var longitudeViaje = null;

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
 * Muestra imagen en modal
 * @param {string} url - URL de la imagen
 */
function verImg(url) {
    window.open(url, '_blank');
}

/**
 * Muestra modal de confirmación para eliminar registro
 * @param {number} id - ID del registro a eliminar
 */
function modalEliminar(id = null) {
    if (id !== null) {
        idEliminar = id;
        $("#modal_eliminar").modal();
    }
}

/**
 * Elimina un registro de dengue
 */
function eliminar() {
    if (idEliminar > 0) {
        $.ajax({
        url: window.laravelRoutes.registrosDengueDestroy.replace(':id', idEliminar),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: 'Registro eliminado correctamente'
                });
                $("#modal_eliminar").modal("hide");
                refrescarTabla();
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error al eliminar el registro'
                });
            }
        });
    }
}

/**
 * Carga datos de un registro para edición
 * @param {number} id - ID del registro
 * @param {number} ind - Índice en el array dataTable
 */
function editar(id, ind) {
    $.ajax({
        url: window.laravelRoutes.registrosDengueGet.replace(':id', id),
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // Mostrar formulario y ocultar lista al iniciar edición
            $("#card_lista").hide();
            $("#card_form").show();
            $("html, body").animate({ scrollTop: 0 }, 300);
            if (response.success) {
                const registro = response.data || {};
                const paciente = registro.paciente || {};

                // Configurar modo edición
                idEdit = id;
                indEditar = ind;

                // Cargar datos del paciente
                $("#dni").val(paciente.DNI || '');
                $("#nombre").val(paciente.ApellidoNombre || '');
                $("#sexo").val(paciente.Sexo != null ? paciente.Sexo : '');
                $("#fecha_nac").val(convertirFechaFormato(paciente.FechaNacimiento));
                $("#celular").val(paciente.Celular || '');
                $("#domicilio").val(paciente.Domicilio || '');
                $("#dto").val(paciente.Departamento || '');
                $("#localidad").val(paciente.Localidad || '');
                $("#barrio").val(paciente.Barrio || '');
                $("#referencia").val(paciente.Referencias || '');

                // Cargar datos epidemiológicos
                $("#semana").val(registro.Semana != null ? registro.Semana : '');
                $("#f_fis").val(convertirFechaFormato(registro.Fis));
                $("#f_consulta").val(convertirFechaFormato(registro.Consulta));
                $("#f_muestra").val(convertirFechaFormato(registro.FechaTomaMuestra));
                $("#internacion").val(Number(registro.Internacion || 0)).trigger('change');
                $("#f_ingreso").val(convertirFechaFormato(registro.FechaIngreso));
                $("#f_alta").val(convertirFechaFormato(registro.FechaAlta));

                // Cargar laboratorio y tests
                $("#laboratorio").val(registro.Laboratorio || '');
                $("#testAgNS1").val(registro.TestAgNS1 != null ? registro.TestAgNS1 : '');
                $("#tipoNs1").val(registro.TipoNs1 != null ? registro.TipoNs1 : '');
                $("#testIgM").val(registro.TestIgM != null ? registro.TestIgM : '');
                $("#testIGG").val(registro.TestIGG != null ? registro.TestIGG : '');
                $("#testPCR").val(registro.TestPCR != null ? registro.TestPCR : '');
                $("#testRapidoIgG").val(registro.TestRapidoIgG != null ? registro.TestRapidoIgG : '');
                $("#testRapidoIgM").val(registro.TestRapidoIgM != null ? registro.TestRapidoIgM : '');
                $("#testChikungunya").val(registro.TestChikungunya != null ? registro.TestChikungunya : '');
                $("#testZika").val(registro.TestZika != null ? registro.TestZika : '');

                $("#ant_vacu").val(registro.AntVacunacion ? 1 : 0);
                $("#obito").val(registro.Obito ? 1 : 0);
                $("#comor").val(registro.Comorbilidad || '');
                $("#obs").val(registro.Observaciones || '');
                $("#ant_viaje").val(registro.AntViaje || '');
                $("#f_ant").val(convertirFechaFormato(registro.FechaAnt));

                // Coordenadas de domicilio y viaje
                latitude = paciente.Latitud != null ? Number(paciente.Latitud) : null;
                longitude = paciente.Longitud != null ? Number(paciente.Longitud) : null;
                latitudeViaje = registro.LatitudAnt != null ? Number(registro.LatitudAnt) : null;
                longitudeViaje = registro.LongitudAnt != null ? Number(registro.LongitudAnt) : null;

                // Indicadores de ubicación
                if (latitude && longitude) {
                    $("#check_no_dir").hide();
                    $("#check_ok_dir").show();
                } else {
                    $("#check_no_dir").show();
                    $("#check_ok_dir").hide();
                }
                if (latitudeViaje && longitudeViaje) {
                    $("#check_no_dir_viaje").hide();
                    $("#check_ok_dir_viaje").show();
                } else {
                    $("#check_no_dir_viaje").show();
                    $("#check_ok_dir_viaje").hide();
                }

                // Efector
                if ($("#efector_sel").length > 0) {
                    $("#efector_sel").val(registro.Efector_Id || '').trigger('change');
                }

                // Imagen de ficha si existe
                if (registro.ImagenFicha) {
                    try {
                        imagenFoto.setImgPreviewUrl('/storage/planillas_dengue/' + registro.ImagenFicha + '.png');
                        borrarImg = false;
                    } catch (e) {
                        console.warn('No se pudo previsualizar la imagen:', e);
                    }
                }

                // Mostrar formulario y botones de acción
                $("#form_persona").show();
                $("#footer_btn").show();

                idPersonaRegistro = registro.PersonaRegistro_Id || paciente.IdPacienteRegTrab || null;

                // Inicializar autocompletado después de mostrar el formulario
                setTimeout(function() {
                    initAutocompleteOnly();
                    initAutocompleteViajeOnly();
                }, 200);
            }
        },
        error: function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Error al cargar el registro'
            });
        }
    });
}

/**
 * Limpia un campo específico del formulario
 * @param {HTMLElement} el - Elemento a limpiar
 */
function limpiarCampo(el) {
    $(el).val('');
}

/**
 * Refresca la tabla de registros
 */
function refrescarTabla() {
    pagina = 1;
    cargarTabla();
}

/**
 * Inicializa el sistema de autocompletado para búsqueda de usuarios
 */
function initAutocompletar() {
    // AJAX común y silvestre como pidió el usuario
    $.ajax({
        url: window.laravelRoutes.usuariosAutocomplete,
        type: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // Dataset para el autocompletado
            dataAutocomplete = Array.isArray(response.data) ? response.data : [];

            // Asegurar que Select2 esté disponible
            if (!($.fn && $.fn.select2)) {
                console.warn('Select2 no está disponible. Carga la librería antes de registros-dengue.js');
                return;
            }

            // El select de usuarios ya está en la vista, no necesitamos reemplazarlo
            var $el = $('#usuario_fil');
            if (!$el.length) {
                console.warn('#usuario_fil no existe en el DOM');
                return;
            }

            // Adaptar datos a formato Select2
            var datos = dataAutocomplete.map(function(u) {
                return { id: u.id, text: u.value, tipo: u.tipo };
            });

            // Inicializar Select2
            $el.select2({
                data: datos,
                placeholder: 'Buscar usuario',
                allowClear: true,
                width: '100%',
                templateResult: function(d) {
                    if (!d.id) return d.text;
                    return $(
                        '<p style="border-left: 5px solid #007bff; margin:0;">' +
                            '<strong>' + (d.text || '') + '</strong> ' +
                            (d.tipo ? '(' + d.tipo + ')' : '') +
                        '</p>'
                    );
                },
                templateSelection: function(d) {
                    return d.text || d.id;
                },
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    var term = params.term.toLowerCase();
                    var text = (data.text || '').toLowerCase();
                    var tipo = (data.tipo || '').toLowerCase();
                    return (text.indexOf(term) > -1 || tipo.indexOf(term) > -1) ? data : null;
                }
            });

            // Evento de selección: reemplaza typeahead:selected
            $el.on('select2:select', function(e) {
                var datum = e.params.data;
                pagina = 1;
                idUsuarioBuscar = datum.id || 0;
            });

            // Evento de limpiar
            $el.on('select2:clear', function() {
                idUsuarioBuscar = 0;
            });
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar usuarios para autocompletar:', error);
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'error', title: 'Error al cargar la lista de usuarios' });
            }
        }
    });
}

/**
 * Exporta los registros de dengue a archivo Excel
 */
function exportar() {
    // Crear overlay de carga
    if ($("#overlay").length == 0) {
        let html_modal = '<div class="overlay-fixed overlay-wrapper" id="overlay">' +
            '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Cargando...</div></div>' +
            '</div>';
        $("body").append(html_modal);
    } else {
        $("#overlay").show();
    }

    // Resetear filtro de usuario si está vacío (Select2)
    var vSearch = $("#usuario_fil").val();
    if (vSearch === null || vSearch === '' || vSearch === undefined || vSearch === '0') idUsuarioBuscar = 0;
    else idUsuarioBuscar = vSearch;

    // Construir parámetros de filtro
    const params = new URLSearchParams({
        d: $("#d_fil").val() || '',
        h: $("#h_fil").val() || '',
        reg: $("#region_fil").val() || '',
        id_usuario: idUsuarioBuscar,
        efector: $("#efector_sel_fil").val() || ''
    });

    // Realizar petición para descargar archivo Excel
    fetch(window.laravelRoutes.registrosDengueInforme + '?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(resp => resp.blob())
    .then(blob => {
        $("#overlay").hide();
        // Crear enlace de descarga automática
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'informe_dengue.xlsx';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    })
    .catch(() => {
        $("#overlay").hide();
        Toast.fire({
            icon: 'error',
            title: 'Error al generar el informe'
        });
    });
}

/**
 * Busca un paciente por DNI
 */
function buscarDni() {
    let dni = $("#dni").val();

    if (!dni) {
        Toast.fire({
            icon: 'warning',
            title: 'Ingrese un DNI válido'
        });
        return;
    }

    limpiar();
    $("#dni").val(dni);

    $.ajax({
        url: window.laravelRoutes.registrosDengueBuscarDni,
        type: 'get',
        data: {
            dni: dni,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $("#form_persona").show();
            $("#footer_btn").show();

            // Aceptar tanto contrato unificado {success, data:{paciente, consulta}} como respuesta legacy {success, paciente, consulta}
            const rawPaciente = (response && response.data && response.data.paciente) ? response.data.paciente : (response && response.paciente) ? response.paciente : null;
            const rawConsulta = (response && response.data && response.data.consulta) ? response.data.consulta : (response && response.consulta) ? response.consulta : null;

            if (response.success && rawPaciente) {
                // Normalizar campos del paciente para el frontend
                const paciente = {
                    id: rawPaciente.id || rawPaciente.IdPacienteRegTrab || null,
                    celular: rawPaciente.celular || rawPaciente.Celular || '',
                    domicilio: rawPaciente.domicilio || rawPaciente.Domicilio || '',
                    apellido_nombre: rawPaciente.apellido_nombre || rawPaciente.ApellidoNombre || '',
                    fecha_nacimiento: rawPaciente.fecha_nacimiento || rawPaciente.FechaNacimiento || rawPaciente.FF || '',
                    barrio: rawPaciente.barrio || rawPaciente.Barrio || '',
                    referencias: rawPaciente.referencias || rawPaciente.Referencias || '',
                    localidad: rawPaciente.localidad || rawPaciente.Localidad || '',
                    departamento: rawPaciente.departamento || rawPaciente.Departamento || '',
                    sexo: rawPaciente.sexo || rawPaciente.Sexo || '',
                    latitud: rawPaciente.latitud || rawPaciente.Latitud || '',
                    longitud: rawPaciente.longitud || rawPaciente.Longitud || ''
                };

                // Paciente encontrado - cargar datos existentes
                idPersonaRegistro = paciente.id;
                $("#celular").val(paciente.celular);
                $("#domicilio").val(paciente.domicilio);
                $("#nombre").val(paciente.apellido_nombre);
                $("#fecha_nac").val(convertirFechaFormato(paciente.fecha_nacimiento));
                $("#barrio").val(paciente.barrio);
                $("#referencia").val(paciente.referencias);
                $("#localidad").val(paciente.localidad);
                $("#dto").val(paciente.departamento);
                $("#sexo").val(paciente.sexo);

                // Configurar coordenadas geográficas
                latitude = paciente.latitud;
                longitude = paciente.longitud;

                $("#check_no_dir").hide();
                $("#check_ok_dir").show();

                // Mostrar leyenda si ya existe consulta previa
                if (rawConsulta) {
                    const consulta = {
                        servicio: rawConsulta.servicio || rawConsulta.Servicio || '',
                        fecha_consulta: rawConsulta.fecha_consulta || rawConsulta.Consulta || ''
                    };
                    $("#leyenda_consulta").show();
                    $("#leyenda_consulta").html(`*YA EXISTE UN REGISTRO CARGADO DE ESTE PACIENTE EN "${consulta.servicio}" EL ${consulta.fecha_consulta}`);
                } else {
                    $("#leyenda_consulta").hide();
                }
            } else {
                // Paciente no encontrado - habilitar creación
                idPersonaRegistro = null;
                $("#check_no_dir").show();
                $("#check_ok_dir").hide();
            }

            // Inicializar autocompletado después de mostrar el formulario
            setTimeout(function() {
                initAutocompleteOnly();
                initAutocompleteViajeOnly();
            }, 200);
        },
        error: function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Error al buscar el paciente'
            });
        }
    });
}

/**
 * Detecta si el dispositivo actual es móvil
 */
function esDispositivoMovil() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Carga los datos de la tabla con paginación y filtros
 */
function cargarTabla() {
    $('#page-selection').unbind();

    // Resetear filtro de usuario si está vacío (Select2)
    var vSearch = $("#usuario_fil").val();
    if (vSearch === null || vSearch === '' || vSearch === undefined || vSearch === '0') idUsuarioBuscar = 0;
    else idUsuarioBuscar = vSearch;

    const params = {
        pagina: pagina,
        cantidad: $("#page-selection_input_num_page").val() || 10,
        d: $("#d_fil").val() || '',
        h: $("#h_fil").val() || '',
        reg: $("#region_fil").val() || '',
        id_usuario: idUsuarioBuscar,
        efector: $("#efector_sel_fil").val() || ''
    };

    $.ajax({
        url: window.laravelRoutes.registrosDengueFiltrar,
        type: 'POST',
        data: {
            ...params,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        headers: { 'Accept': 'application/json' },
        success: function(response) {
            // Contrato fijo esperado: { success: true, data: [], total: N, paginas: M }
            if (!response || response.success !== true || !Array.isArray(response.data)) {
                Toast.fire({
                    icon: 'error',
                    title: 'Respuesta inválida del servidor'
                });
                return;
            }

            dataTable = response.data;
            var total = typeof response.total === 'number' ? response.total : dataTable.length;
            var paginas = typeof response.paginas === 'number' ? response.paginas : 1;

            var permisosVal = $("#permisos").val() || "0|0|0";
            var permisos = permisosVal.split("|");

            var htmlTable = '';
            dataTable.forEach(function(rec, i) {
                var id = rec.IdRegistroDengue || '';
                var fechaConsulta = rec.Consulta || '';
                var servicio = rec.servicio || '';
                var apellidoNombre = rec.ApellidoNombre || '';
                var usuario = ((rec.Apellido || '') + ' ' + (rec.Nombre || '')).trim();
                var creado = rec.FFCreacion || '';

                htmlTable += '<tr>';
                htmlTable += '<td>' + id + '</td>';
                htmlTable += '<td>' + convertirFechaFormato(fechaConsulta) + '</td>';
                htmlTable += '<td><p><small>' + servicio + '</small></p></td>';
                htmlTable += '<td><p><small>' + apellidoNombre + '</small></p></td>';
                htmlTable += '<td><p><small>' + usuario + '</small></p></td>';
                htmlTable += '<td><p><small>' + convertirFechaFormato(creado) + '</small></p></td>';
                htmlTable += '<td>';
                htmlTable += '<div class="btn-group">';
                if (permisos[1] == 1) {
                    htmlTable += '<button type="button" onclick="editar(' + id + ',' + i + ')" class="btn btn-primary btn-xs"><i class="fas fa-edit"></i></button>';
                }
                if (permisos[2] == 1) {
                    htmlTable += '<button type="button" onclick="modalEliminar(' + id + ')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>';
                }
                htmlTable += '</div>';
                htmlTable += '</td>';
                htmlTable += '</tr>';
            });

            $("#table_data").html(htmlTable);

            // Totales y paginación
            $('#total_info').html(total + ' registros');

            // Crear paginación con Bootstrap nativo
            crearPaginacionBootstrap(paginas, pagina);
        },
        error: function() {
            Toast.fire({
                icon: 'error',
                title: 'Error al cargar los datos'
            });
        }
    });
}

/**
 * Limpia completamente el formulario
 */
function limpiar() {
    // Resetear variables de estado
    indEditar = 0;
    idEdit = 0;
    idEliminar = 0;
    idPersonaRegistro = null;
    let efector = $("#efector_sel").val();

    // Resetear formulario manteniendo efector
    $("#form_main")[0].reset();
    $("#efector_sel").val(efector);
    $("#efector_sel").trigger("change");

    // Restaurar controles de mapa
    $(".cont_marca_mapa").show();
    $("#domicilio").attr("disabled", false);

    // Resetear variables de ubicación
    latitude = null;
    longitude = null;
    latitudeViaje = null;
    longitudeViaje = null;
    imagenFoto.deleteImg();
    borrarImg = false;

    // Resetear indicadores de ubicación
    $("#check_no_dir").show();
    $("#check_ok_dir").hide();
    $("#check_no_dir_viaje").show();
    $("#check_ok_dir_viaje").hide();
    $("#clickMap").hide();
    $("#clickMapViaje").hide();

    // Limpiar elementos de interfaz
    $("#form_persona").hide();
    $("#footer_btn").hide();
    $("#leyenda_consulta").hide();
    $(".cont_int").hide();
}

/**
 * Guarda o actualiza un registro de dengue
 */
function guardar() {
    // Validaciones básicas
    if (!$("#dni").val()) {
        Toast.fire({
            icon: 'error',
            title: 'El DNI es requerido'
        });
        return;
    }

    if (!$("#nombre").val()) {
        Toast.fire({
            icon: 'error',
            title: 'El nombre del paciente es requerido'
        });
        return;
    }

    // Validar ubicación geográfica
    if (longitude === null) {
        Toast.fire({
            icon: 'error',
            title: "Por favor seleccione una ubicación válida"
        });
        return;
    }

    // Validar Ag-NS1 y tipo
    var testAgNS1 = $("#testAgNS1").val();
    var tipoNs1 = $("#tipoNs1").val();

    if ((testAgNS1 === "1" || testAgNS1 === "0") && (tipoNs1 === "" || tipoNs1 === null)) {
        Toast.fire({
            icon: 'error',
            title: "Si seleccionó resultado de Ag-NS1, debe especificar el tipo (ELISA o TR)"
        });
        return;
    }

    // Preparar datos del formulario
    const formData = new FormData(document.getElementById('form_main'));

    // Agregar datos adicionales
    formData.append('id_persona_registro', idPersonaRegistro);
    formData.append('latitud', latitude);
    formData.append('longitud', longitude);
    formData.append('latitud_viaje', latitudeViaje);
    formData.append('longitud_viaje', longitudeViaje);
    formData.append('borrar_img', borrarImg);

    // Asegurar que region_id se incluya
    const regionId = $('#region_id').val() || $('#efector_sel option:selected').data('region');
    if (regionId) {
        formData.append('region_id', regionId);
    }

    // Agregar imagen si existe
    let base64Foto = imagenFoto.getBase64Img();
    if (base64Foto) {
        formData.append('imagen_base64', base64Foto.substring(22));
    }

    // Determinar URL según si es creación o actualización
    let url = idEdit == 0 ? window.laravelRoutes.registrosDengueStore : window.laravelRoutes.registrosDengueUpdate.replace(':id', idEdit);
    let method = idEdit == 0 ? 'POST' : 'PUT';

    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: 'Registro guardado correctamente'
                });
                $("#modal_confirmar").modal("hide");
                limpiar();
                // Ocultar formulario y mostrar lista
                $("#card_form").hide();
                $("#card_lista").show();
                refrescarTabla();
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al guardar el registro'
                });
            }
        },
        error: function(xhr) {
            let message = 'Error al guardar el registro';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            Toast.fire({
                icon: 'error',
                title: message
            });
        }
    });
}

/**
 * Control previo al guardado
 */
function guardarControl() {
    if (longitudeViaje === null) {
        $("#modal_confirmar").modal();
    } else {
        guardar();
    }
}

/**
 * Convierte fecha de formato YYYY-MM-DD a DD/MM/YYYY
 */
function convertirFechaFormato(fecha) {
    if (!fecha) return '';

    // Si ya está en formato DD/MM/YYYY, devolverla tal como está
    if (typeof fecha === 'string' && fecha.includes('/')) return fecha;

    // Normalizar fecha ISO: YYYY-MM-DD o YYYY-MM-DDTHH:MM:SSZ
    if (typeof fecha === 'string') {
        let base = fecha.split('T')[0].split(' ')[0];
        // Fechas inválidas o de placeholder
        if (!base || base.startsWith('-') || base === '0000-00-00') return '';
        const partes = base.split('-');
        if (partes.length === 3) {
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }
        return '';
    }

    return '';
}

/**
 * Obtiene ubicación actual del dispositivo
 */
function ubicacionActual() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                latitude = position.coords.latitude;
                longitude = position.coords.longitude;

                // Usar geocoding inverso para obtener dirección
                const geocoder = new google.maps.Geocoder();
                const latlng = new google.maps.LatLng(latitude, longitude);

                geocoder.geocode({'latLng': latlng}, function(results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            $("#domicilio").val(results[0].formatted_address);
                            $("#check_no_dir").hide();
                            $("#check_ok_dir").show();
                        }
                    }
                });
            },
            function(error) {
                $("#error_gps").show();
                Toast.fire({
                    icon: 'error',
                    title: 'Error al obtener ubicación GPS'
                });
            }
        );
    } else {
        Toast.fire({
            icon: 'error',
            title: 'Geolocalización no soportada'
        });
    }
}

/**
 * Muestra mapa para marcar ubicación del domicilio
 */
function marcarMapa() {
    $("#clickMap").toggle();

    if ($("#clickMap").is(":visible")) {
        initAutocomplete();
    }
}

/**
 * Muestra mapa para marcar ubicación del viaje
 */
function marcarMapaViaje() {
    $("#clickMapViaje").toggle();

    if ($("#clickMapViaje").is(":visible")) {
        initAutocompleteViaje();
    }
}

/**
 * Inicializa Google Maps y autocompletado para domicilio
 */
function initAutocomplete() {
    if (typeof google === 'undefined' || !google.maps) {
        if (typeof Toast !== 'undefined') {
            Toast.fire({ icon: 'error', title: 'Google Maps no cargó' });
        } else {
            alert('Google Maps no cargó. Verifique la clave y conexión.');
        }
        return;
    }
    // Configurar mapa centrado en Argentina
    clickMap = new google.maps.Map(document.getElementById('clickMap'), {
        // Posadas, Misiones
        center: {lat: -27.362, lng: -55.900},
        zoom: 13,
        mapTypeId: 'roadmap'
    });

    // Configurar autocompletado
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('domicilio'),
        {types: ['geocode']}
    );

    autocomplete.bindTo('bounds', clickMap);

    // Listener para cuando se selecciona un lugar
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (!place.geometry) return;

        latitude = place.geometry.location.lat();
        longitude = place.geometry.location.lng();

        // Centrar mapa en la ubicación
        clickMap.setCenter(place.geometry.location);
        clickMap.setZoom(17);

        // Agregar marcador
        if (clickMarker) {
            clickMarker.setMap(null);
        }

        clickMarker = new google.maps.Marker({
            position: place.geometry.location,
            map: clickMap,
            title: 'Ubicación seleccionada'
        });

        $("#check_no_dir").hide();
        $("#check_ok_dir").show();
    });

    // Listener para clicks en el mapa
    clickMap.addListener('click', function(event) {
        latitude = event.latLng.lat();
        longitude = event.latLng.lng();

        // Agregar marcador
        if (clickMarker) {
            clickMarker.setMap(null);
        }

        clickMarker = new google.maps.Marker({
            position: event.latLng,
            map: clickMap,
            title: 'Ubicación seleccionada'
        });

        // Geocoding inverso para obtener dirección
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({'latLng': event.latLng}, function(results, status) {
            if (status === google.maps.GeocoderStatus.OK && results[0]) {
                $("#domicilio").val(results[0].formatted_address);
            }
        });

        $("#check_no_dir").hide();
        $("#check_ok_dir").show();
    });
}

/**
 * Inicializa Google Maps para ubicación de viaje
 */
function initAutocompleteViaje() {
    if (typeof google === 'undefined' || !google.maps) {
        if (typeof Toast !== 'undefined') {
            Toast.fire({ icon: 'error', title: 'Google Maps no cargó' });
        } else {
            alert('Google Maps no cargó. Verifique la clave y conexión.');
        }
        return;
    }
    // Similar a initAutocomplete pero para el campo de viaje
    const clickMapViaje = new google.maps.Map(document.getElementById('clickMapViaje'), {
        // Posadas, Misiones
        center: {lat: -27.362, lng: -55.900},
        zoom: 13,
        mapTypeId: 'roadmap'
    });

    autocompleteViaje = new google.maps.places.Autocomplete(
        document.getElementById('ant_viaje'),
        {types: ['geocode']}
    );

    autocompleteViaje.bindTo('bounds', clickMapViaje);

    autocompleteViaje.addListener('place_changed', function() {
        const place = autocompleteViaje.getPlace();
        if (!place.geometry) return;

        latitudeViaje = place.geometry.location.lat();
        longitudeViaje = place.geometry.location.lng();

        clickMapViaje.setCenter(place.geometry.location);
        clickMapViaje.setZoom(17);

        if (clickMarkerViaje) {
            clickMarkerViaje.setMap(null);
        }

        clickMarkerViaje = new google.maps.Marker({
            position: place.geometry.location,
            map: clickMapViaje,
            title: 'Ubicación de viaje'
        });

        $("#check_no_dir_viaje").hide();
        $("#check_ok_dir_viaje").show();
    });

    clickMapViaje.addListener('click', function(event) {
        latitudeViaje = event.latLng.lat();
        longitudeViaje = event.latLng.lng();

        if (clickMarkerViaje) {
            clickMarkerViaje.setMap(null);
        }

        clickMarkerViaje = new google.maps.Marker({
            position: event.latLng,
            map: clickMapViaje,
            title: 'Ubicación de viaje'
        });

        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({'latLng': event.latLng}, function(results, status) {
            if (status === google.maps.GeocoderStatus.OK && results[0]) {
                $("#ant_viaje").val(results[0].formatted_address);
            }
        });

        $("#check_no_dir_viaje").hide();
        $("#check_ok_dir_viaje").show();
    });
}

/**
 * Crea paginación usando Bootstrap nativo
 * @param {number} totalPaginas - Total de páginas
 * @param {number} paginaActual - Página actual
 */
function crearPaginacionBootstrap(totalPaginas, paginaActual) {
    const container = $('#page-selection');

    if (totalPaginas <= 1) {
        container.html('<p class="text-muted">Página 1 de 1</p>');
        return;
    }

    let html = '<nav aria-label="Paginación de registros">';
    html += '<ul class="pagination justify-content-center mb-0">';

    // Botón Anterior
    const prevDisabled = paginaActual <= 1 ? 'disabled' : '';
    html += `<li class="page-item ${prevDisabled}">`;
    html += `<a class="page-link" href="#" data-page="${paginaActual - 1}" ${prevDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>`;
    html += '<i class="fas fa-chevron-left"></i> Anterior</a></li>';

    // Lógica para mostrar páginas
    let startPage = Math.max(1, paginaActual - 2);
    let endPage = Math.min(totalPaginas, paginaActual + 2);

    // Ajustar si estamos cerca del inicio o final
    if (paginaActual <= 3) {
        endPage = Math.min(5, totalPaginas);
    }
    if (paginaActual > totalPaginas - 3) {
        startPage = Math.max(1, totalPaginas - 4);
    }

    // Primera página si no está en el rango
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
        if (startPage > 2) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Páginas del rango
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === paginaActual ? 'active' : '';
        html += `<li class="page-item ${activeClass}">`;
        html += `<a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
    }

    // Última página si no está en el rango
    if (endPage < totalPaginas) {
        if (endPage < totalPaginas - 1) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        html += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPaginas}">${totalPaginas}</a></li>`;
    }

    // Botón Siguiente
    const nextDisabled = paginaActual >= totalPaginas ? 'disabled' : '';
    html += `<li class="page-item ${nextDisabled}">`;
    html += `<a class="page-link" href="#" data-page="${paginaActual + 1}" ${nextDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>`;
    html += 'Siguiente <i class="fas fa-chevron-right"></i></a></li>';

    html += '</ul></nav>';

    // Información adicional
    html += `<div class="text-center mt-2 text-muted">`;
    html += `Página ${paginaActual} de ${totalPaginas}`;
    html += '</div>';

    container.html(html);

    // Agregar eventos de click
    container.find('a.page-link[data-page]').on('click', function(e) {
        e.preventDefault();
        const nuevaPagina = parseInt($(this).data('page'));
        if (nuevaPagina !== paginaActual && nuevaPagina >= 1 && nuevaPagina <= totalPaginas) {
            pagina = nuevaPagina;
            cargarTabla();
        }
    });
}

// Inicialización cuando el documento está listo
$(function() {


    esMovil = esDispositivoMovil();

    // Configurar SweetAlert para notificaciones toast
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });

    // Configurar selectores de fecha
    $('#fecha_fis, #fecha_consulta, #fecha_muestra, #fecha_ingreso, #fecha_alta, #fecha_ant, #desde_fil, #hasta_fil').datetimepicker({
        format: 'DD/MM/YYYY',
        locale: 'es'
    });

    // Configurar máscaras de entrada
    $('[data-mask]').inputmask();

    // Configurar Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Event listeners
    $("#btn_buscar_dni").click(buscarDni);
    $("#btn_submit").click(function(e) {
        e.preventDefault();
        guardarControl();
    });
    $("#btn_limpiar").click(limpiar);
    $("#btn_eliminar_modal").click(eliminar);

    // Event listeners para filtros
    $("#usuario_fil").change(function() {
        console.log('Filtro usuario cambiado a:', $(this).val());
        refrescarTabla();
    });
    $("#efector_sel_fil").change(function() {
        console.log('Filtro efector cambiado a:', $(this).val());
        refrescarTabla();
    });
    $("#region_fil").change(function() {
        console.log('Filtro región cambiado a:', $(this).val());
        refrescarTabla();
    });

    // Mostrar/ocultar campos de internación
    $("#internacion").change(function() {
        if ($(this).val() == "1") {
            $(".cont_int").show();
        } else {
            $(".cont_int").hide();
        }
    });

    // Evento change para efector - establecer region_id automáticamente
    $(document).on('change', '#efector_sel', function() {
        const selectedOption = $(this).find('option:selected');
        const regionId = selectedOption.data('region');
        // Crear campo oculto para region_id si no existe
        if ($('#region_id').length === 0) {
            $('#form_main').append('<input type="hidden" id="region_id" name="region_id">');
        }
        $('#region_id').val(regionId || '');
    });

    // Inicializar autocompletado y cargar tabla
    initAutocompletar();
    cargarTabla();

    // Configurar paginación
    if ($("#page-selection_num_page").length == 0) {
        $("#page-selection_num_page").html('<label>Registros por página: <select id="page-selection_input_num_page" class="form-control" style="width: auto; display: inline-block;"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select></label>');
    }

    $("#page-selection_input_num_page").change(function() {
        refrescarTabla();
    });
});

// Mostrar lista por defecto y ocultar formulario
$("#card_form").hide();
$("#card_lista").show();

// Botón Agregar -> muestra formulario y oculta lista
$(document).on('click', '#btn_agregar', function () {
    limpiar();
    idEdit = 0;
    $("#card_lista").hide();
    $("#card_form").show();
    $("html, body").animate({ scrollTop: 0 }, 300);

    // Inicializar autocompletado después de mostrar el formulario
    setTimeout(function() {
        initAutocompleteOnly();
        initAutocompleteViajeOnly();
    }, 100);
});

// Botón Exportar CSV -> descarga archivo CSV con filtros aplicados
$(document).on('click', '#btn_exportar', function () {
    // Obtener los valores de los filtros actuales
    const filtros = {
        d: $("#d_fil").val() || '',
        h: $("#h_fil").val() || '',
        id_usuario: $("#usuario_fil").val() || 0,
        efector: $("#efector_fil").val() || 0,
        reg: $("#region_fil").val() || '',
        _t: Date.now() // Timestamp para evitar caché
    };

    // Construir URL con parámetros de filtro
    const params = new URLSearchParams(filtros);
    const url = '/registros-dengue/exportar?' + params.toString();

    // Crear enlace temporal y hacer clic para forzar descarga
    const link = document.createElement('a');
    link.href = url;
    link.download = 'registros_dengue.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

// Botón Volver -> regresa a la lista
$(document).on('click', '#btn_volver', function () {
    $("#card_form").hide();
    $("#card_lista").show();
    $("html, body").animate({ scrollTop: $("#card_lista").offset().top }, 300);
});

/**
 * Inicializar autocompletado de Google Places para domicilio sin mapa
 */
function initAutocompleteOnly() {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        console.warn('Google Maps Places API no está disponible');
        return;
    }

    try {
        // Configurar autocompletado solo para el campo domicilio
        autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('domicilio'),
            {
                types: ['geocode'],
                componentRestrictions: { country: 'AR' } // Restringir a Argentina
            }
        );

        // Listener para cuando se selecciona un lugar
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry) {
                console.log('No se encontraron detalles del lugar para:', place.name);
                return;
            }

            // Guardar coordenadas globales
            latitude = place.geometry.location.lat();
            longitude = place.geometry.location.lng();

            // Extraer componentes de la dirección
            const addressComponents = place.address_components;
            let departamento = '';
            let localidad = '';

            addressComponents.forEach(component => {
                const types = component.types;
                if (types.includes('administrative_area_level_2')) {
                    departamento = component.long_name;
                } else if (types.includes('locality') || types.includes('administrative_area_level_3')) {
                    localidad = component.long_name;
                }
            });

            // Rellenar campos automáticamente
            if (departamento && $('#dto').val() === '') {
                $('#dto').val(departamento);
            }
            if (localidad && $('#localidad').val() === '') {
                $('#localidad').val(localidad);
            }

            // Mostrar indicador de éxito
            $("#check_no_dir").hide();
            $("#check_ok_dir").show();

            console.log('Dirección seleccionada:', place.formatted_address);
            console.log('Coordenadas:', latitude, longitude);
        });

    } catch (error) {
        console.error('Error al inicializar autocompletado:', error);
    }
}

/**
 * Inicializar autocompletado para campo de viaje sin mapa
 */
function initAutocompleteViajeOnly() {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        return;
    }

    try {
        autocompleteViaje = new google.maps.places.Autocomplete(
            document.getElementById('ant_viaje'),
            {
                types: ['geocode']
            }
        );

        autocompleteViaje.addListener('place_changed', function() {
            const place = autocompleteViaje.getPlace();
            if (!place.geometry) return;

            latitudeViaje = place.geometry.location.lat();
            longitudeViaje = place.geometry.location.lng();

            $("#check_no_dir_viaje").hide();
            $("#check_ok_dir_viaje").show();
        });

    } catch (error) {
        console.error('Error al inicializar autocompletado de viaje:', error);
    }
}


