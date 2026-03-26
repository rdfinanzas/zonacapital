// Variables globales
var usuarios = [];
var modulos = [];
var modulosGrupos = [];
var modulosPorUrl = {};
var extrasPorModulo = {};

var tiposUsuarios = [];
var permisosExtrasPorTipo = [];
var paginaActual = 1;
var totalPaginas = 1;
var usuarioEditando = null;

// Mapa de caracteres para normalización
var charMap = {
    'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A', 'Æ': 'AE', 'Ç': 'C',
    'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E', 'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I',
    'Ð': 'D', 'Ñ': 'N', 'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O', 'Ø': 'O',
    'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U', 'Ý': 'Y', 'ß': 's', 'à': 'a', 'á': 'a',
    'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a', 'æ': 'ae', 'ç': 'c', 'è': 'e', 'é': 'e',
    'ê': 'e', 'ë': 'e', 'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i', 'ñ': 'n', 'ò': 'o',
    'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o', 'ø': 'o', 'ù': 'u', 'ú': 'u', 'û': 'u',
    'ü': 'u', 'ý': 'y', 'ÿ': 'y'
};

// Función para normalizar texto
function normalize(input) {
    var output = "";
    for (var i = 0; i < input.length; i++) {
        output += charMap[input.charAt(i)] || input.charAt(i);
    }
    return output.toLowerCase();
}

// Helpers de modal para Bootstrap 5
function showModal(modalId) {
    var el = document.getElementById(modalId);
    var m = bootstrap.Modal.getOrCreateInstance(el);
    m.show();
}
function hideModal(modalId) {
    var el = document.getElementById(modalId);
    var m = bootstrap.Modal.getInstance(el);
    if (m) m.hide();
}

// Inicializar Select2 de personas
function initSelect2Personas() {
    $('#persona').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar persona...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: window.Laravel.baseUrl + '/usuarios/empleados-autocomplete',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                // Controlador retorna { success: true, response: [...] }
                var arr = Array.isArray(data) ? data : (data.response || []);
                return {
                    results: arr.map(function (p) {
                        return {
                            id: p.id,
                            text: p.value + (p.DNI ? ' (DNI: ' + p.DNI + ')' : ''), // Mostrar DNI en el texto
                            empleado: p // Almacenar todos los datos del empleado
                        };
                    })
                };
            },
            cache: true
        }
    });

    $('#persona').on('select2:select', function (e) {
        var data = e.params.data;
        var id = data.id;
        $('#persona_id').val(id || '');
        
        // Autocompletar los campos de nombre y apellido con los datos del empleado
        if (data.empleado) {
            $('#nombre').val(data.empleado.Nombre || '');
            $('#apellido').val(data.empleado.Apellido || '');
        }

        // Verificar si el empleado ya tiene un usuario vinculado
        if (id) {
            apiLaravel('/usuarios/por-personal/' + id, 'GET')
                .then(function (response) {
                    if (response && response.success) {
                        // Ya existe un usuario vinculado: advertir y cargar sus datos
                        toastr.warning('Este empleado ya tiene un usuario vinculado');
                        var usuario = response.data && response.data.usuario ? response.data.usuario : null;
                        if (usuario && usuario.IdUsuario) {
                            // Usar flujo estándar de visualización/edición
                            ver(usuario.IdUsuario);
                        }
                    }
                })
                .catch(function (err) {
                    // 404 esperado cuando no existe usuario; no mostrar error
                    // Solo manejar errores reales del servidor
                    if (err && err.status && err.status !== 404) {
                        console.error('Error al verificar vinculación de usuario:', err);
                    }
                });
        }
    });

    $('#persona').on('select2:clear', function () {
        $('#persona_id').val('');
    });
}

// Inicializar Select2 de usuarios (búsqueda)
function initSelect2Usuarios() {
    $('#buscar_usuario').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar usuario...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: window.Laravel.baseUrl + '/usuarios/autocomplete',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                // Controlador retorna { success: true, response: [...] }
                var arr = Array.isArray(data) ? data : (data.response || []);
                return {
                    results: arr.map(function (u) {
                        return {
                            id: u.id,
                            text: (u.value || '') + (u.tipo ? ' — ' + u.tipo : '')
                        };
                    })
                };
            },
            cache: true
        }
    });

    $('#buscar_usuario').on('select2:select', function (e) {
        var id = e.params.data.id;
        if (id) ver(id);
    });
}

// Función para eliminar usuario
function eliminarUsuario(id) {
    usuarioEditando = id;
    var usuario = usuarios.find(u => u.id == id);
    if (usuario) {
        $('#usuario_a_eliminar').text(usuario.usuario + ' (' + usuario.apellido + ', ' + usuario.nombre + ')');
        showModal('modal_eliminar');
    }
}

// Modal de eliminación
function modalEliminar() {
    showModal('modal_eliminar');
}

// Ver detalles de usuario
function ver(id) {
    apiLaravel('/usuarios/' + id, 'GET')
        .then(function (response) {
            if (response && response.success) {
                var usuario = response.data && response.data.usuario ? response.data.usuario : null;
                // Permisos desactivados en UI: trabajar sólo con rol vinculado

                if (!usuario) return;

                usuarioEditando = id;

                // Llenar formulario
                $('#usuario_id').val(usuario.IdUsuario);
                $('#usuario').val(usuario.Usuario);
                $('#password').val(''); // No mostrar contraseña
                $('#tipo_usuario').val(usuario.UsuarioTipo_Id);
                $('#persona_id').val(usuario.Empleado_Id || '');
                $('#nombre').val(usuario.Nombre || '');
                $('#apellido').val(usuario.Apellido || '');
                $('#blanqueo').prop('checked', (usuario.Blanqueo || 0) == 1);
                $('#baja').prop('checked', (usuario.Baja || 0) == 1);

                // Mostrar persona en Select2 si existe
                if (usuario.Empleado_Id && usuario.PersonaNombre) {
                    var option = new Option(usuario.PersonaNombre, usuario.Empleado_Id, true, true);
                    $('#persona').append(option).trigger('change');
                } else {
                    $('#persona').val(null).trigger('change');
                }

                // No mostrar ni cargar permisos en la UI de usuarios

                // Mostrar botón eliminar
                $('#btn_eliminar').show();
                // Mostrar formulario y ocultar tabla/filtros
                mostrarFormulario();
                // Scroll al formulario
                $('html, body').animate({
                    scrollTop: $('#form_usuario').offset().top
                }, 300);
            }
        })
        .catch(function (err) {
            toastr.error('Error al obtener usuario: ' + err);
        });
}

// Cargar usuarios con paginación
function cargarUsuarios(pagina = 1) {
    var texto = ($('#buscar_texto').val() || '').trim();
    var tipo = $('#filtro_tipo').val() || '';
    var estado = $('#filtro_estado').val() || '';

    var params = { pagina: pagina, texto: texto, tipo: tipo, estado: estado };
  //mostrarIndicadorCarga(false);
    apiLaravel('/usuarios/filtrar', 'GET', params)
        .then(function (response) {
            if (response && response.success) {
                var payload = response.response || {};
                var lista = payload.personas || [];
                usuarios = lista.map(function (u) {
                    return {
                        id: u.IdUsuario,
                        usuario: u.Usuario,
                        nombre: u.Nombre,
                        apellido: u.Apellido,
                        tipo_usuario_nombre: u.UsuarioTipo,
                        baja: u.Baja
                    };
                });
                paginaActual = pagina;
                totalPaginas = payload.paginas || 1;

                mostrarUsuarios();
                mostrarPaginacion({
                    current_page: paginaActual,
                    last_page: totalPaginas,
                    total: payload.total || usuarios.length,
                    per_page: 10
                });
            }
        })
        .catch(function (err) {
            toastr.error('Error al cargar usuarios: ' + err);
        });
}

// Mostrar usuarios en la tabla
function mostrarUsuarios(lista) {
    var tbody = $('#usuarios_tbody');
    tbody.empty();
    var data = Array.isArray(lista) ? lista : usuarios;

    data.forEach(function(usuario) {
        var fila = '<tr>';

        // Checkbox para eliminación masiva
        if ($('#select_all').length > 0) {
            fila += '<td><input type="checkbox" class="usuario-checkbox" value="' + usuario.id + '"></td>';
        }

        fila += '<td>' + usuario.id + '</td>';
        fila += '<td>' + usuario.usuario + '</td>';
        fila += '<td>' + usuario.apellido + ', ' + usuario.nombre + '</td>';
        fila += '<td>' + (usuario.tipo_usuario_nombre || 'N/A') + '</td>';
        fila += '<td>' + (usuario.baja == 1 ? '<span class="badge badge-estado-inactivo">Inactivo</span>' : '<span class="badge badge-estado-activo">Activo</span>') + '</td>';
        fila += '<td>';
        fila += '<button class="btn btn-sm btn-info" onclick="ver(' + usuario.id + ')" title="Ver/Editar">';
        fila += '<i class="fas fa-eye"></i>';
        fila += '</button> ';
        fila += '<button class="btn btn-sm btn-danger" onclick="eliminarUsuario(' + usuario.id + ')" title="Eliminar">';
        fila += '<i class="fas fa-trash"></i>';
        fila += '</button>';
        fila += '</td>';
        fila += '</tr>';

        tbody.append(fila);
    });

    // Actualizar contador de seleccionados
    actualizarContadorSeleccionados();
}

// Mostrar solo el formulario y ocultar la tabla y filtros
function mostrarFormulario() {
    // Ocultar tabla y secciones de listado
     $('#panel1').addClass('d-none');
    $('#panel2').removeClass('d-none');
}

// Mostrar tabla y ocultar el formulario
// function mostrarTabla() {
//     $('#panel2').addClass('d-none');
//     $('#tabla_usuarios').closest('.table-responsive').removeClass('d-none');
//     $('#paginacion').closest('.row').removeClass('d-none');
//     $('#btn_buscar').closest('.row').removeClass('d-none');
// }
function mostrarPanel1() {
  // Estado inicial: ocultar formulario y mostrar tabla
    $('#panel2').addClass('d-none');
    $('#panel1').removeClass('d-none');

}


// Mostrar paginación
function mostrarPaginacion(data) {
    var paginacion = $('#paginacion');
    paginacion.empty();

    // Información de paginación (simple)
    var inicio = (data.current_page - 1) * data.per_page + 1;
    var fin = Math.min(data.current_page * data.per_page, data.total);
    $('#info_paginacion').text('Mostrando ' + inicio + ' a ' + fin + ' de ' + data.total + ' registros');

    // Botón anterior
    if (data.current_page > 1) {
        paginacion.append('<li class="page-item"><a class="page-link" href="#" onclick="cargarUsuarios(' + (data.current_page - 1) + ')">Anterior</a></li>');
    }

    // Páginas
    for (var i = 1; i <= data.last_page; i++) {
        var clase = i == data.current_page ? 'active' : '';
        paginacion.append('<li class="page-item ' + clase + '"><a class="page-link" href="#" onclick="cargarUsuarios(' + i + ')">' + i + '</a></li>');
    }

    // Botón siguiente
    if (data.current_page < data.last_page) {
        paginacion.append('<li class="page-item"><a class="page-link" href="#" onclick="cargarUsuarios(' + (data.current_page + 1) + ')">Siguiente</a></li>');
    }
}

// Limpiar formulario
function limpiar() {
    $('#form_usuario')[0].reset();
    $('#usuario_id').val('');
    $('#persona_id').val('');
    usuarioEditando = null;
    $('#btn_eliminar').hide();

    // Limpiar permisos
    $('#permisos_modulos input[type="checkbox"]').prop('checked', false);
    $('input[name^="permisos_extras["]').prop('checked', false);

    // Limpiar Select2 persona
    $('#persona').val(null).trigger('change');

    // Ocultar sección de permisos al iniciar una creación nueva
    $('#permisos_section').addClass('d-none');

    // Volver a mostrar la tabla y ocultar formulario
    mostrarPanel1();
}

// Cargar tipos de usuarios
function cargarTiposUsuarios() {
    apiLaravel('/usuarios/tipos-usuarios', 'GET')
        .then(function (response) {
            if (response && response.success) {
                tiposUsuarios = response.data || [];
                var select = $('#tipo_usuario');
                var filtro = $('#filtro_tipo');

                select.empty().append('<option value="">Seleccione un tipo</option>');
                filtro.empty().append('<option value="">Todos los tipos</option>');

                tiposUsuarios.forEach(function (tipo) {
                    // Backend: IdUsuarioTipo, UsuarioTipo
                    select.append('<option value="' + tipo.IdUsuarioTipo + '">' + tipo.UsuarioTipo + '</option>');
                    filtro.append('<option value="' + tipo.IdUsuarioTipo + '">' + tipo.UsuarioTipo + '</option>');
                });
            }
        })
        .catch(function (err) {
            toastr.error('Error al cargar tipos: ' + err);
        });
}

// Cargar módulos y generar permisos
function cargarModulos() {
    apiLaravel('/usuarios/modulos', 'GET')
        .then(function (response) {
            if (response && response.success) {
                var rows = response.data || [];

                var grupos = {};
                modulosPorUrl = {};
                rows.forEach(function (r) {
                    if (grupos[r.PadreId] == null) {
                        grupos[r.PadreId] = {
                            PadreId: r.PadreId,
                            PadreLabel: r.PadreLabel,
                            PadreOrden: r.PadreOrden,
                            hijos: []
                        };
                    }
                    grupos[r.PadreId].hijos.push({ IdModulo: r.IdModulo, Url: r.Url, Label: r.Label, Orden: r.Orden });
                    modulosPorUrl[r.Url] = { IdModulo: r.IdModulo, Url: r.Url, Label: r.Label, PadreId: r.PadreId, PadreLabel: r.PadreLabel };
                });

                modulosGrupos = Object.values(grupos).sort(function (a, b) { return (a.PadreOrden||0) - (b.PadreOrden||0); });
                modulos = [];
                modulosGrupos.forEach(function (g) {
                    g.hijos.sort(function (a,b){ return (a.Orden||0) - (b.Orden||0); });
                    g.hijos.forEach(function (h) { modulos.push(h); });
                });

                generarPermisosModulosPadreHijos();
                cargarPermisosExtras(true);
            }
        })
        .catch(function (err) {
            toastr.error('Error al cargar módulos: ' + err);
        });
}

// Generar tabla de permisos por módulos
function generarPermisosModulos() {
    var tbody = $('#permisos_modulos');
    tbody.empty();

    modulos.forEach(function (modulo) {
        var fila = '<tr data-url="' + (modulo.Url || '') + '" data-id="' + (modulo.IdModulo || '') + '">';
        fila += '<td>' + (modulo.Modulo || modulo.Label || 'Módulo') + '</td>';
        fila += '<td class="text-center"><input type="checkbox" name="permisos_modulos[' + modulo.IdModulo + '][ver]" value="1"></td>';
        fila += '<td class="text-center"><input type="checkbox" name="permisos_modulos[' + modulo.IdModulo + '][crear]" value="1"></td>';
        fila += '<td class="text-center"><input type="checkbox" name="permisos_modulos[' + modulo.IdModulo + '][actualizar]" value="1"></td>';
        fila += '<td class="text-center"><input type="checkbox" name="permisos_modulos[' + modulo.IdModulo + '][eliminar]" value="1"></td>';
        fila += '</tr>';
        tbody.append(fila);
    });
}

// Cargar permisos extras por tipo de usuario
// Cargar permisos por tipo de usuario (preconfigurados por módulo)
function cargarPermisosPorTipo(tipoId) {
    if (!tipoId) {
        // Limpiar checks
        $('#permisos_modulos input[type="checkbox"]').prop('checked', false);
        return;
    }

    apiLaravel('/usuarios/permisos-tipos', 'GET')
        .then(function (response) {
            if (response && response.success) {
                var mapa = response.response || {};
                var porTipo = mapa[tipoId] || {};

                // Limpiar todos primero
                $('#permisos_modulos input[type="checkbox"]').prop('checked', false);

                // porTipo está indexado por URL de módulo
                Object.keys(porTipo).forEach(function (url) {
                    var perms = porTipo[url];
                    // Encontrar el módulo por URL para obtener su IdModulo
                    var mod = (modulos || []).find(function (m) { return (m.Url || '') === url; });
                    if (!mod) return;
                    var id = mod.IdModulo;
                    // Mapear C,R,U,D a ver,crear,actualizar,eliminar
                    $('input[name="permisos_modulos[' + id + '][ver]"]').prop('checked', perms.R == 1);
                    $('input[name="permisos_modulos[' + id + '][crear]"]').prop('checked', perms.C == 1);
                    $('input[name="permisos_modulos[' + id + '][actualizar]"]').prop('checked', perms.U == 1);
                    $('input[name="permisos_modulos[' + id + '][eliminar]"]').prop('checked', perms.D == 1);
                });
            }
        })
        .catch(function (err) {
            toastr.error('Error al cargar permisos por tipo: ' + err);
        });
}

// Generar checkboxes de permisos extras
// Extras: no definidos por backend actual; dejamos contenedor vacío
function generarPermisosExtras() {
    $('#permisos_extras').empty();
}

// Cargar permisos de un usuario
function cargarPermisosUsuario(permisos) {
    // Limpiar todos los permisos
    $('#permisos_modulos input[type="checkbox"]').prop('checked', false);
    $('#permisos_extras input[type="checkbox"]').prop('checked', false);

    // Backend: lista con campos IdModulo, C,R,U,D
    if (Array.isArray(permisos)) {
        permisos.forEach(function (p) {
            var id = p.IdModulo;
            $('input[name="permisos_modulos[' + id + '][ver]"]').prop('checked', (p.R || 0) == 1);
            $('input[name="permisos_modulos[' + id + '][crear]"]').prop('checked', (p.C || 0) == 1);
            $('input[name="permisos_modulos[' + id + '][actualizar]"]').prop('checked', (p.U || 0) == 1);
            $('input[name="permisos_modulos[' + id + '][eliminar]"]').prop('checked', (p.D || 0) == 1);
        });
    }
}

// Marcar permisos extras del usuario en los checkboxes
function marcarPermisosExtrasDelUsuario(permisosExtras) {
    try {
        $('input[name^="permisos_extras["]').prop('checked', false);
        if (Array.isArray(permisosExtras)) {
            permisosExtras.forEach(function (pe) {
                var id = pe.PermisoExtra_Id || pe.IdPermisoExtra || pe.Id;
                var val = pe.Valor != null ? pe.Valor : 1;
                if (id && val == 1) {
                    $('input[name="permisos_extras[' + id + ']"]').prop('checked', true);
                }
            });
        }
    } catch (e) {
        // no-op
    }
}

// Actualizar contador de seleccionados
function actualizarContadorSeleccionados() {
    var seleccionados = $('.usuario-checkbox:checked').length;
    $('#btn_eliminar_masivo').prop('disabled', seleccionados === 0);

    if (seleccionados > 0) {
        $('#btn_eliminar_masivo').text('Eliminar Seleccionados (' + seleccionados + ')');
    } else {
        $('#btn_eliminar_masivo').text('Eliminar Seleccionados');
    }
}

// Función de inicialización principal
function initUsuarios() {
    // Cargar datos iniciales
    cargarTiposUsuarios();
    cargarUsuarios();

    // Inicializar Select2
    initSelect2Personas();
    initSelect2Usuarios();
    mostrarPanel1();

    // Reemplazar buscador con select por input de texto (solo nombre/usuario)
    var $colBuscarUsuario = $('#buscar_usuario').closest('.col-md-4');
    if ($colBuscarUsuario.length) {
        try { if (typeof $('#buscar_usuario').select2 === 'function') { $('#buscar_usuario').select2('destroy'); } } catch(e) {}
        $colBuscarUsuario.removeClass('col-md-4').addClass('col-md-3');
        $colBuscarUsuario.empty().append('<input type="text" class="form-control" id="buscar_texto" placeholder="Buscar por usuario o nombre">');
    }
    // Asegurar que los filtros estén visibles
    $('#filtro_tipo').closest('.col-md-3').removeClass('d-none');
    $('#filtro_estado').closest('.col-md-3').removeClass('d-none');

    // Event listeners (sin cargar permisos por tipo en usuarios)

    $('#btn_limpiar').click(function() {
        limpiar();
    });

    // Aplicar filtros server-side
    $('#btn_buscar').click(function() {
        cargarUsuarios(1);
    });
    $(document).on('keyup', '#buscar_texto', debounce(function() {
        cargarUsuarios(1);
    }, 250));
    $('#filtro_tipo').change(function() { cargarUsuarios(1); });
    $('#filtro_estado').change(function() { cargarUsuarios(1); });

    // Select all checkbox
    $('#select_all').change(function() {
        $('.usuario-checkbox').prop('checked', $(this).prop('checked'));
        actualizarContadorSeleccionados();
    });

    // Individual checkboxes
    $(document).on('change', '.usuario-checkbox', function() {
        var total = $('.usuario-checkbox').length;
        var seleccionados = $('.usuario-checkbox:checked').length;
        $('#select_all').prop('checked', total === seleccionados);
        actualizarContadorSeleccionados();
    });

    // Eliminación masiva
    $('#btn_eliminar_masivo').click(function() {
        var seleccionados = $('.usuario-checkbox:checked').length;
        if (seleccionados > 0) {
            showModal('modal_eliminar_masivo');
        }
    });

    $('#btn_confirmar_eliminar_masivo').click(function() {
        var ids = [];
        $('.usuario-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length > 0) {
            apiLaravel('/usuarios/eliminar-masivo', 'POST', { ids: ids })
                .then(function (response) {
                    if (response.success) {
                        hideModal('modal_eliminar_masivo');
                        cargarUsuarios(paginaActual);
                        toastr.success('Usuarios eliminados correctamente');
                    } else {
                        toastr.error('Error al eliminar usuarios');
                    }
                })
                .catch(function (err) {
                    toastr.error('Error al eliminar usuarios: ' + err);
                });
        }
    });

    // Eliminación individual
    $('#btn_confirmar_eliminar').click(function() {
        if (usuarioEditando) {
            apiLaravel('/usuarios/' + usuarioEditando, 'DELETE')
                .then(function (response) {
                    if (response.success) {
                        hideModal('modal_eliminar');
                        cargarUsuarios(paginaActual);
                        limpiar();
                        toastr.success('Usuario eliminado correctamente');
                    } else {
                        toastr.error('Error al eliminar usuario');
                    }
                })
                .catch(function (err) {
                    toastr.error('Error al eliminar usuario: ' + err);
                });
        }
    });

    // Validación y envío del formulario
    $('#form_usuario').validate({
        rules: {
            usuario: {
                required: true,
                minlength: 3
            },
            password: {
                required: function() {
                    return !usuarioEditando; // Solo requerido para nuevos usuarios
                },
                minlength: 4,
                digits: true
            },
            tipo_usuario: {
                required: true
            },
            nombre: {
                required: true
            },
            apellido: {
                required: true
            }
        },
        messages: {
            usuario: {
                required: "El nombre de usuario es requerido",
                minlength: "El usuario debe tener al menos 3 caracteres"
            },
            password: {
                required: "La contraseña es requerida",
                minlength: "La contraseña debe tener al menos 4 dígitos",
                digits: "La contraseña debe contener solo números"
            },
            tipo_usuario: "Seleccione un tipo de usuario",
            nombre: "El nombre es requerido",
            apellido: "El apellido es requerido"
        },
        submitHandler: function(form) {
            var formData = $(form).serializeArray();
            var data = {};

            // Convertir array a objeto
            $.each(formData, function(i, field) {
                data[field.name] = field.value;
            });

            // No recolectar permisos; sólo vincular el rol seleccionado

            // Determinar si es creación o actualización
            var url = usuarioEditando ? '/usuarios/' + usuarioEditando : '/usuarios';
            var method = usuarioEditando ? 'PUT' : 'POST';

            // Adaptar datos al backend
            var payload = {
                usuario: data.usuario,
                clave: data.password || undefined,
                tipo: data.tipo_usuario,
                nombre: data.nombre,
                apellido: data.apellido,
                personaId: data.persona_id ? parseInt(data.persona_id) : null,
                blanqueo: $('#blanqueo').is(':checked') ? 1 : 0,
                baja: $('#baja').is(':checked') ? 1 : 0
            };

            // Sin permisos ni extras en payload

            apiLaravel(url, method, payload)
                .then(function (response) {
                    if (response.success) {
                        // Éxito: recargar listado y mostrar mensaje, sin sección de permisos
                        cargarUsuarios(paginaActual);
                        toastr.success(usuarioEditando ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente');
                    } else {
                        toastr.error('Error al guardar usuario');
                    }
                })
                .catch(function (err) {
                    toastr.error('Error al guardar usuario: ' + err);
                });
        }
    });

    // Botón Agregar: limpiar y mostrar el formulario (sin sección de permisos)
    $('#btn_agregar').on('click', function() {
        limpiar();
        mostrarFormulario();
    });
}

// Debounce helper para inputs
function debounce(fn, delay) {
    var t;
    return function() {
        var ctx = this, args = arguments;
        clearTimeout(t);
        t = setTimeout(function() { fn.apply(ctx, args); }, delay);
    };
}

// Aplicar filtros locales y renderizar tabla
function aplicarFiltrosYRender() {
    // Redirigir al filtro server-side
    cargarUsuarios(1);
}

// Sin listeners de toggles de permisos: gestión se hace por Roles

// Generar árbol/accordion de permisos por módulos (UI agrupada por módulo)
function generarPermisosModulosAccordion() {
    var contenedor = $('#permisos_modulos');
    contenedor.empty();

    // Usar los grupos padre→hijos para armar un accordion con la tabla de hijos
    (modulosGrupos || []).forEach(function (grupo) {
        var padreId = grupo.PadreId || 'padre';
        var padreLabel = grupo.PadreLabel || 'Módulo';
        var headingId = 'grupo_heading_' + padreId;
        var collapseId = 'grupo_collapse_' + padreId;

        var html = '';
        html += '<div class="card card-outline card-secondary mb-3">';
        html += '  <div class="card-header" id="' + headingId + '">';
        html += '    <h3 class="card-title">' + padreLabel + '</h3>';
        html += '    <div class="card-tools">';
        html += '      <button class="btn btn-tool" type="button" data-toggle="collapse" data-target="#' + collapseId + '" aria-expanded="false" aria-controls="' + collapseId + '">';
        html += '        <i class="fas fa-plus"></i>';
        html += '      </button>';
        html += '    </div>';
        html += '  </div>';
        html += '  <div id="' + collapseId + '" class="collapse" aria-labelledby="' + headingId + '" data-parent="#permisos_modulos">';
        html += '    <div class="card-body">';
        html += '      <div class="table-responsive">';
        html += '        <table class="table table-bordered table-striped table-sm">';
        html += '          <thead>';
        html += '            <tr>';
        html += '              <th style="width:35%">Submódulo</th>';
        html += '              <th class="text-center" style="width:11%">Ver</th>';
        html += '              <th class="text-center" style="width:11%">Crear</th>';
        html += '              <th class="text-center" style="width:11%">Actualizar</th>';
        html += '              <th class="text-center" style="width:11%">Eliminar</th>';
        html += '              <th class="text-center" style="width:21%">Extras</th>';
        html += '            </tr>';
        html += '          </thead>';
        html += '          <tbody>';

        (grupo.hijos || []).forEach(function (hijo) {
            html += '            <tr data-url="' + (hijo.Url || '') + '" data-id="' + (hijo.IdModulo || '') + '">';
            html += '              <td>' + (hijo.Label || hijo.Url || 'Submódulo') + '</td>';
            html += '              <td class="text-center">';
            html += '                <input class="form-check-input" type="checkbox" id="perm_' + hijo.IdModulo + '_ver" name="permisos_modulos[' + hijo.IdModulo + '][ver]" value="1">';
            html += '              </td>';
            html += '              <td class="text-center">';
            html += '                <input class="form-check-input" type="checkbox" id="perm_' + hijo.IdModulo + '_crear" name="permisos_modulos[' + hijo.IdModulo + '][crear]" value="1">';
            html += '              </td>';
            html += '              <td class="text-center">';
            html += '                <input class="form-check-input" type="checkbox" id="perm_' + hijo.IdModulo + '_actualizar" name="permisos_modulos[' + hijo.IdModulo + '][actualizar]" value="1">';
            html += '              </td>';
            html += '              <td class="text-center">';
            html += '                <input class="form-check-input" type="checkbox" id="perm_' + hijo.IdModulo + '_eliminar" name="permisos_modulos[' + hijo.IdModulo + '][eliminar]" value="1">';
            html += '              </td>';
            html += '              <td class="text-center">';
            var extras = extrasPorModulo[hijo.IdModulo] || [];
            if (extras.length > 0) {
                extras.forEach(function (ex) {
                    var exId = ex.IdPermisoExtra;
                    var exLabel = ex.PermisoExtra || ex.Clave || 'Extra';
                    html += '                <div class="form-check form-check-inline">';
                    html += '                  <input class="form-check-input ck_permiso_pe" type="checkbox" id="pe_' + exId + '" name="permisos_extras[' + exId + ']" value="1" disabled title="Los permisos extras se administran por Rol">';
                    html += '                  <label class="form-check-label" for="pe_' + exId + '">' + exLabel + '</label>';
                    html += '                </div>';
                });
            } else {
                html += '                <span class="text-muted">—</span>';
            }
            html += '              </td>';
            html += '            </tr>';
        });

        html += '          </tbody>';
        html += '        </table>';
        html += '      </div>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>';

        contenedor.append(html);
    });
}

// Toggle delegado para el botón del accordion (fallback sin Bootstrap)
// Asegura expandir/colapsar dinámicamente y alternar ícono +/−
$(document).on('click', '#permisos_modulos .card-header .btn.btn-tool', function (e) {
    e.preventDefault();
    var target = $(this).attr('data-target');
    if (!target) return;

    var $collapse = $(target);
    var $icon = $(this).find('i');
    if ($collapse.length === 0) return;

    var isOpen = $collapse.hasClass('show') || $collapse.is(':visible');
    if (isOpen) {
        $collapse.slideUp(150).removeClass('show');
        $(this).attr('aria-expanded', 'false');
        if ($icon.length) $icon.removeClass('fa-minus').addClass('fa-plus');
    } else {
        $collapse.slideDown(150).addClass('show');
        $(this).attr('aria-expanded', 'true');
        if ($icon.length) $icon.removeClass('fa-plus').addClass('fa-minus');
    }
});

// Generar lista por módulo con ítems debajo (sin colapsar)
function generarPermisosModulosLista() {
    var contenedor = $('#permisos_modulos');
    contenedor.empty();

    (modulos || []).forEach(function (modulo) {
        var modId = modulo.IdModulo || '';
        var moduloNombre = (modulo.Modulo || modulo.Label || 'Módulo');

        var cardHtml = '';
        cardHtml += '<div class="card card-outline card-secondary mb-2" data-url="' + (modulo.Url || '') + '" data-id="' + modId + '">';
        cardHtml += '  <div class="card-header">';
        cardHtml += '    <h3 class="card-title">' + moduloNombre + '</h3>';
        cardHtml += '  </div>';
        cardHtml += '  <div class="card-body">';
        cardHtml += '    <div class="row">';

        cardHtml += '      <div class="col-sm-3">';
        cardHtml += '        <div class="form-check">';
        cardHtml += '          <input class="form-check-input" type="checkbox" id="perm_' + modId + '_ver" name="permisos_modulos[' + modId + '][ver]" value="1">';
        cardHtml += '          <label class="form-check-label" for="perm_' + modId + '_ver">Ver</label>';
        cardHtml += '        </div>';
        cardHtml += '      </div>';

        cardHtml += '      <div class="col-sm-3">';
        cardHtml += '        <div class="form-check">';
        cardHtml += '          <input class="form-check-input" type="checkbox" id="perm_' + modId + '_crear" name="permisos_modulos[' + modId + '][crear]" value="1">';
        cardHtml += '          <label class="form-check-label" for="perm_' + modId + '_crear">Crear</label>';
        cardHtml += '        </div>';
        cardHtml += '      </div>';

        cardHtml += '      <div class="col-sm-3">';
        cardHtml += '        <div class="form-check">';
        cardHtml += '          <input class="form-check-input" type="checkbox" id="perm_' + modId + '_actualizar" name="permisos_modulos[' + modId + '][actualizar]" value="1">';
        cardHtml += '          <label class="form-check-label" for="perm_' + modId + '_actualizar">Actualizar</label>';
        cardHtml += '        </div>';
        cardHtml += '      </div>';

        cardHtml += '      <div class="col-sm-3">';
        cardHtml += '        <div class="form-check">';
        cardHtml += '          <input class="form-check-input" type="checkbox" id="perm_' + modId + '_eliminar" name="permisos_modulos[' + modId + '][eliminar]" value="1">';
        cardHtml += '          <label class="form-check-label" for="perm_' + modId + '_eliminar">Eliminar</label>';
        cardHtml += '        </div>';
        cardHtml += '      </div>';

        cardHtml += '    </div>';
        cardHtml += '  </div>';
        cardHtml += '</div>';

        contenedor.append(cardHtml);
    });
}

// Renderizar permisos agrupados: padre card y lista de hijos con CRUD
function generarPermisosModulosPadreHijos() {
    var contenedor = $('#permisos_modulos');
    contenedor.empty();

    (modulosGrupos || []).forEach(function (grupo) {
        var html = '';
        html += '<div class="card card-outline card-secondary mb-3">';
        html += '  <div class="card-header">';
        html += '    <h3 class="card-title">' + (grupo.PadreLabel || 'Módulo') + '</h3>';
        html += '  </div>';
        html += '  <div class="card-body">';
        html += '    <div class="table-responsive">';
        html += '      <table class="table table-bordered table-striped table-sm">';
        html += '        <thead>';
        html += '          <tr>';
        html += '            <th style="width:35%">Submódulo</th>';
        html += '            <th class="text-center" style="width:11%">Ver</th>';
        html += '            <th class="text-center" style="width:11%">Crear</th>';
        html += '            <th class="text-center" style="width:11%">Actualizar</th>';
        html += '            <th class="text-center" style="width:11%">Eliminar</th>';
        html += '            <th class="text-center" style="width:21%">Extras</th>';
        html += '          </tr>';
        html += '        </thead>';
        html += '        <tbody>';

        (grupo.hijos || []).forEach(function (hijo) {
            html += '          <tr data-url="' + (hijo.Url || '') + '" data-id="' + (hijo.IdModulo || '') + '">';
            html += '            <td>' + (hijo.Label || hijo.Url || 'Submódulo') + '</td>';
            html += '            <td class="text-center">';
            html += '              <input class="form-check-input" type="checkbox" id="perm_' + hijo.IdModulo + '_ver" name="permisos_modulos[' + hijo.IdModulo + '][ver]" value="1">';
            html += '            </td>';
            html += '            <td class="text-center">';
            html += '              <input class="form-check-input" type="checkbox" id="perm_' + hijo.IdModulo + '_crear" name="permisos_modulos[' + hijo.IdModulo + '][crear]" value="1">';
            html += '            </td>';
            html += '            <td class="text-center">';
            html += '              <input class="form-check-input" type="checkbox" id="perm_' + hijo.IdModulo + '_actualizar" name="permisos_modulos[' + hijo.IdModulo + '][actualizar]" value="1">';
            html += '            </td>';
            html += '            <td class="text-center">';
            html += '              <input class="form-check-input" type="checkbox" id="perm_' + hijo.IdModulo + '_eliminar" name="permisos_modulos[' + hijo.IdModulo + '][eliminar]" value="1">';
            html += '            </td>';
            html += '            <td class="text-center">';
            var extras = extrasPorModulo[hijo.IdModulo] || [];
            if (extras.length > 0) {
                extras.forEach(function (ex) {
                    var exId = ex.IdPermisoExtra;
                    var exLabel = ex.PermisoExtra || ex.Clave || 'Extra';
                    html += '              <div class="form-check form-check-inline">';
                    html += '                <input class="form-check-input ck_permiso_pe" type="checkbox" id="pe_' + exId + '" name="permisos_extras[' + exId + ']" value="1" disabled title="Los permisos extras se administran por Rol">';
                    html += '                <label class="form-check-label" for="pe_' + exId + '">' + exLabel + '</label>';
                    html += '              </div>';
                });
            } else {
                html += '              <span class="text-muted">—</span>';
            }
            html += '            </td>';
            html += '          </tr>';
        });

        html += '        </tbody>';
        html += '      </table>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>';

        contenedor.append(html);
    });
}

// Cargar permisos extras agrupados por ModuloId
function cargarPermisosExtras(reRender) {
    apiLaravel('/usuarios/permisos-extras', 'GET')
        .then(function (response) {
            if (response && response.success) {
                extrasPorModulo = response.data || {};
                if (reRender) {
                  //  generarPermisosModulosPadreHijos();
                  generarPermisosModulosAccordion()
                }
            }
        })
        .catch(function (err) {
            toastr.error('Error al cargar permisos extras: ' + err);
        });
}

// Nueva versión: cargar permisos por tipo usando mapa URL→IdModulo
function cargarPermisosPorTipoNueva(tipoId) {
    if (tipoId == null || tipoId === '') {
        $('#permisos_modulos input[type="checkbox"]').prop('checked', false);
        return;
    }

    apiLaravel('/usuarios/permisos-tipos', 'GET')
        .then(function (response) {
            if (response && response.success) {
                var mapa = response.response || {};
                var porTipo = mapa[tipoId] || {};

                $('#permisos_modulos input[type="checkbox"]').prop('checked', false);

                Object.keys(porTipo).forEach(function (url) {
                    var perms = porTipo[url];
                    var mod = modulosPorUrl[url];
                    if (!mod) return;
                    var id = mod.IdModulo;
                    $('input[name="permisos_modulos[' + id + '][ver]"]').prop('checked', perms.R == 1);
                    $('input[name="permisos_modulos[' + id + '][crear]"]').prop('checked', perms.C == 1);
                    $('input[name="permisos_modulos[' + id + '][actualizar]"]').prop('checked', perms.U == 1);
                    $('input[name="permisos_modulos[' + id + '][eliminar]"]').prop('checked', perms.D == 1);
                });
            }
        })
        .catch(function (err) {
            toastr.error('Error al cargar permisos por tipo: ' + err);
        });
}
