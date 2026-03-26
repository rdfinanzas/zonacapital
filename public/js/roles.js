// Módulo Roles (RBAC)
// Requiere common.js con apiLaravel y toastr

(function () {
    var rolEditando = null; // Id del rol que se está editando
    var roles = [];
    var modulos = [];
    var modulosGrupos = [];
    var extrasPorModulo = {};

    function initRoles() {
        cargarRoles();
        cargarModulos();
        wireEvents();
    }

    function wireEvents() {
        // Nuevo
        $('#btn_nuevo_rol').on('click', function () {
            limpiarFormulario();
        });

        // Guardar
        $('#form_rol').on('submit', function (e) {
            e.preventDefault();
            var nombre = ($('#rol_nombre').val() || '').trim();
            if (!nombre) {
                toastr.warning('Ingresá un nombre de rol');
                return;
            }

            var url = '/roles';
            var method = 'POST';
            var payload = { nombre: nombre };

            if (rolEditando) {
                url = '/roles/' + rolEditando;
                method = 'PUT';
            }

            apiLaravel(url, method, payload)
                .then(function (resp) {
                    if (resp && resp.success) {
                        toastr.success('Rol guardado correctamente');
                        // Si es nuevo, usar el id devuelto
                        if (!rolEditando && resp.id) {
                            rolEditando = resp.id;
                            $('#rol_id').val(rolEditando);
                        }
                        cargarRoles();
                    } else {
                        toastr.error((resp && resp.message) || 'Error al guardar rol');
                    }
                })
                .catch(function (err) {
                    toastr.error('Error al guardar rol');
                    console.error(err);
                });
        });

        // Delegar acciones de tabla (editar / eliminar)
        $('#tabla_roles').on('click', '.btn-editar-rol', function () {
            var id = $(this).data('id');
            var rol = roles.find(function (r) { return r.IdUsuarioTipo === id; });
            if (rol) {
                setFormulario(rol);
                rolEditando = id;
                marcarPermisosDelRol(id);
            }
        });

        $('#tabla_roles').on('click', '.btn-eliminar-rol', function () {
            var id = $(this).data('id');
            var rol = roles.find(function (r) { return r.IdUsuarioTipo === id; });
            if (!rol) return;
            $('#rol_eliminar_nombre').text(rol.UsuarioTipo);
            $('#modalEliminarRol').data('id', id).modal('show');
        });

        $('#btn_confirmar_eliminar_rol').on('click', function () {
            var id = $('#modalEliminarRol').data('id');
            if (!id) return;
            apiLaravel('/roles/' + id, 'DELETE').then(function (resp) {
                if (resp && resp.success) {
                    toastr.success('Rol eliminado');
                    $('#modalEliminarRol').modal('hide');
                    limpiarFormulario();
                    cargarRoles();
                    desmarcarPermisos();
                } else {
                    toastr.error((resp && resp.message) || 'No se pudo eliminar');
                }
            }).catch(function (err) {
                toastr.error('Error al eliminar rol');
                console.error(err);
            });
        });

        // Delegar cambios de permisos (CRUD)
        $(document).on('change', 'input[name^="permisos_modulos["]', function () {
            if (!rolEditando) {
                toastr.info('Seleccioná un rol primero');
                $(this).prop('checked', !$(this).prop('checked'));
                return;
            }
            var name = $(this).attr('name');
            var matches = name.match(/permisos_modulos\[(\d+)\]\[(C|R|U|D)\]/);
            if (!matches) return;
            var moduloId = parseInt(matches[1]);
            var accion = matches[2];
            var valor = $(this).is(':checked') ? 1 : 0;

            apiLaravel('/roles/' + rolEditando + '/toggle-permiso-modulo', 'POST', {
                moduloId: moduloId,
                accion: accion,
                valor: valor
            }).then(function (resp) {
                if (!resp || !resp.success) {
                    toastr.error((resp && resp.message) || 'No se pudo actualizar el permiso');
                }
            }).catch(function (err) {
                toastr.error('Error al actualizar el permiso');
                console.error(err);
            });
        });

        // Delegar cambios de permisos extras por Rol
        $(document).on('change', '#permisos_modulos .ck_permiso_pe', function () {
            if (!rolEditando) {
                toastr.info('Seleccioná un rol primero');
                $(this).prop('checked', !$(this).prop('checked'));
                return;
            }
            var name = $(this).attr('name');
            var matches = name.match(/permisos_extras\[(\d+)\]/);
            if (!matches) return;
            var permisoExtraId = parseInt(matches[1]);
            var valor = $(this).is(':checked') ? 1 : 0;

            apiLaravel('/roles/' + rolEditando + '/toggle-permiso-extra', 'POST', {
                permisoExtraId: permisoExtraId,
                valor: valor
            }).then(function (resp) {
                if (!resp || !resp.success) {
                    toastr.error((resp && resp.message) || 'No se pudo actualizar el permiso extra');
                }
            }).catch(function (err) {
                toastr.error('Error al actualizar el permiso extra');
                console.error(err);
            });
        });
    }

    // Toggle delegado para el botón del accordion (tomado de usuarios.js)
    $(document).on('click', '#permisos_modulos .card-header .btn.btn-tool', function (e) {
        e.preventDefault();
        var target = $(this).attr('data-bs-target');
        if (!target) return;

        var $collapse = $(target);
        var $icon = $(this).find('i');
        if ($collapse.length === 0) return;

        var isOpen = $collapse.hasClass('show');
        if (isOpen) {
            $collapse.collapse('hide');
            if ($icon.length) $icon.removeClass('fa-minus').addClass('fa-plus');
        } else {
            $collapse.collapse('show');
            if ($icon.length) $icon.removeClass('fa-plus').addClass('fa-minus');
        }
    });

    function cargarRoles() {
        apiLaravel('/roles/list', 'GET')
            .then(function (resp) {
                if (resp && resp.success) {
                    roles = resp.data || [];
                    renderRolesTable();
                } else {
                    toastr.error((resp && resp.message) || 'Error al cargar roles');
                }
            })
            .catch(function (err) {
                toastr.error('Error al cargar roles');
                console.error(err);
            });
    }

    function renderRolesTable() {
        var tbody = $('#tabla_roles tbody');
        tbody.empty();
        roles.forEach(function (r) {
            var tr = $('<tr>');
            tr.append('<td>' + (r.IdUsuarioTipo) + '</td>');
            tr.append('<td>' + (r.UsuarioTipo || '') + '</td>');
            tr.append('<td>' +
                '<button class="btn btn-sm btn-primary me-2 btn-editar-rol" data-id="' + r.IdUsuarioTipo + '">Editar</button>' +
                '<button class="btn btn-sm btn-danger btn-eliminar-rol" data-id="' + r.IdUsuarioTipo + '">Eliminar</button>' +
            '</td>');
            tbody.append(tr);
        });
    }

    function limpiarFormulario() {
        rolEditando = null;
        $('#rol_id').val('');
        $('#rol_nombre').val('');
        desmarcarPermisos();
    }

    function setFormulario(rol) {
        $('#rol_id').val(rol.IdUsuarioTipo || '');
        $('#rol_nombre').val(rol.UsuarioTipo || '');
    }

    function cargarModulos() {
        apiLaravel('/roles/modulos', 'GET').then(function (resp) {
            if (resp && resp.success) {
                var rows = resp.data || [];
                modulos = rows;

                // Construir grupos padre→hijos similares a usuarios.js
                var grupos = {};
                (rows || []).forEach(function (r) {
                    var pid = r.PadreId;
                    if (grupos[pid] == null) {
                        grupos[pid] = {
                            PadreId: r.PadreId,
                            PadreLabel: r.PadreLabel,
                            PadreOrden: r.PadreOrden,
                            hijos: []
                        };
                    }
                    grupos[pid].hijos.push({ IdModulo: r.IdModulo, Url: r.Url, Label: r.Label, Orden: r.Orden });
                });

                modulosGrupos = Object.values(grupos).sort(function (a, b) { return (a.PadreOrden || 0) - (b.PadreOrden || 0); });
                modulosGrupos.forEach(function (g) {
                    g.hijos.sort(function (a, b) { return (a.Orden || 0) - (b.Orden || 0); });
                });

                generarPermisosModulosPadreHijos();

                // Cargar extras y re-render para incluir columna Extras
                cargarPermisosExtras(true);
            } else {
                toastr.error((resp && resp.message) || 'Error al cargar módulos');
            }
        }).catch(function (err) {
            toastr.error('Error al cargar módulos');
            console.error(err);
        });
    }

    // Cargar permisos extras agrupados por ModuloId (reutilizamos endpoint de Usuarios)
    function cargarPermisosExtras(reRender) {
        apiLaravel('/usuarios/permisos-extras', 'GET')
            .then(function (response) {
                if (response && response.success) {
                    extrasPorModulo = response.data || {};
                    if (reRender) {
                        generarPermisosModulosPadreHijos();
                    }
                }
            })
            .catch(function (err) {
                console.error('Error al cargar permisos extras', err);
            });
    }

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
            html += '            <th class="text-center" style="width:11%">Ver (R)</th>';
            html += '            <th class="text-center" style="width:11%">Crear (C)</th>';
            html += '            <th class="text-center" style="width:11%">Actualizar (U)</th>';
            html += '            <th class="text-center" style="width:11%">Eliminar (D)</th>';
            html += '            <th class="text-center" style="width:21%">Extras</th>';
            html += '          </tr>';
            html += '        </thead>';
            html += '        <tbody>';

            (grupo.hijos || []).forEach(function (hijo) {
                var id = hijo.IdModulo || '';
                html += '          <tr data-url="' + (hijo.Url || '') + '" data-id="' + id + '">';
                html += '            <td>' + (hijo.Label || hijo.Url || 'Submódulo') + '</td>';
                html += '            <td class="text-center">';
                html += '              <input class="form-check-input" type="checkbox" id="perm_' + id + '_R" name="permisos_modulos[' + id + '][R]" value="1">';
                html += '            </td>';
                html += '            <td class="text-center">';
                html += '              <input class="form-check-input" type="checkbox" id="perm_' + id + '_C" name="permisos_modulos[' + id + '][C]" value="1">';
                html += '            </td>';
                html += '            <td class="text-center">';
                html += '              <input class="form-check-input" type="checkbox" id="perm_' + id + '_U" name="permisos_modulos[' + id + '][U]" value="1">';
                html += '            </td>';
                html += '            <td class="text-center">';
                html += '              <input class="form-check-input" type="checkbox" id="perm_' + id + '_D" name="permisos_modulos[' + id + '][D]" value="1">';
                html += '            </td>';
                // Columna Extras: checkboxes por permiso extra del módulo (editable por Rol)
                html += '            <td class="text-center">';
                var extras = extrasPorModulo[id] || [];
                if (extras.length > 0) {
                    extras.forEach(function (ex) {
                        var exId = ex.IdPermisoExtra;
                        var exLabel = ex.PermisoExtra || ex.Clave || 'Extra';
                        html += '              <div class="form-check form-check-inline">';
                        html += '                <input class="form-check-input ck_permiso_pe" type="checkbox" id="pe_' + exId + '" name="permisos_extras[' + exId + ']" value="1">';
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

    function desmarcarPermisos() {
        $('input[name^="permisos_modulos["]').prop('checked', false);
    }

    function marcarPermisosDelRol(roleId) {
        desmarcarPermisos();
        apiLaravel('/roles/' + roleId + '/permisos', 'GET')
            .then(function (resp) {
                if (resp && resp.success) {
                    var permisos = resp.data || [];
                    permisos.forEach(function (p) {
                        ['C','R','U','D'].forEach(function (acc) {
                            var val = p[acc];
                            var selector = 'input[name="permisos_modulos[' + p.ModuloId + '][' + acc + ']" ]';
                            $(selector).prop('checked', val == 1);
                        });
                    });
                    // Luego marcar extras por rol
                    marcarPermisosExtrasDelRol(roleId);
                }
            })
            .catch(function (err) {
                console.error('Error al cargar permisos del rol', err);
            });
    }

    function marcarPermisosExtrasDelRol(roleId) {
        // Limpiar extras primero
        $('input[name^="permisos_extras["]').prop('checked', false);
        apiLaravel('/roles/' + roleId + '/permisos-extras', 'GET')
            .then(function (resp) {
                if (resp && resp.success) {
                    var extras = resp.data || [];
                    extras.forEach(function (pe) {
                        var id = pe.PermisoExtraId || pe.IdPermisoExtra;
                        var val = pe.Permiso != null ? pe.Permiso : 1;
                        if (id && val == 1) {
                            $('input[name="permisos_extras[' + id + ']" ]').prop('checked', true);
                        }
                    });
                }
            })
            .catch(function (err) {
                console.error('Error al cargar permisos extras del rol', err);
            });
    }

    // Init
    $(document).ready(initRoles);
})();