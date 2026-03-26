@extends('layouts.main')

@section('title', 'Usuarios')

@push('styles')
    <!-- Estilos del mismo origen ya incluidos en layout (AdminLTE, FontAwesome). -->
    <!-- Si hace falta CSS extra específico de usuarios, se puede agregar aquí.
         Evitamos referencias a http://localhost:8000 que causaban CORS. -->
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Usuarios</h3>
                </div>
                <div class="card-body">
                    <!-- Formulario de Usuario -->
                    <div id="panel2">
                        <form id="form_usuario" class="mb-4">
                            @csrf
                            <input type="hidden" id="usuario_id" name="id">

                                 <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="persona">Persona</label>
                                        <select class="form-control" id="persona" name="persona" style="width: 100%;"></select>
                                        <input type="hidden" id="persona_id" name="persona_id">
                                    </div>
                                </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="usuario">Usuario <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Contraseña <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="apellido">Apellido <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo_usuario">Tipo de Usuario <span class="text-danger">*</span></label>
                                        <select class="form-control" id="tipo_usuario" name="tipo_usuario" required>
                                            <option value="">Seleccione un tipo</option>
                                        </select>
                                    </div>
                                </div>

                                
                            </div>



                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="blanqueo" name="blanqueo">
                                        <label class="custom-control-label" for="blanqueo">Blanqueo</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="baja" name="baja" value="1">
                                        <label class="custom-control-label" for="baja">Baja</label>
                                    </div>
                                </div>
                            </div>



                            <!-- Botones de Acción -->
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" id="btn_guardar">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-secondary" id="btn_limpiar">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </button>
                                @if(($permisosRuta['eliminar'] ?? false))
                                <button type="button" class="btn btn-danger" id="btn_eliminar" style="display: none;">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                                @endif
                            </div>
                        </form>
                            <!-- Gestión de permisos movida al módulo Roles -->

                            {{-- <!-- Permisos Extras -->
                            <div class="mt-4">
                                <h5>Permisos Extras</h5>
                                <div id="permisos_extras" class="row">
                                    <!-- Los permisos extras se cargarán dinámicamente -->
                                </div>
                            </div> --}}
                    </div>
                    <hr>

                    <!-- Filtros y Búsqueda -->
                    <div id="panel1">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="buscar_texto" placeholder="Buscar por usuario o nombre">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="filtro_tipo">
                                    <option value="">Todos los tipos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="filtro_estado">
                                    <option value="">Todos los estados</option>
                                    <option value="0">Activo</option>
                                    <option value="1">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-info" id="btn_buscar">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                               {{-- @dd($permisosRuta) --}}
                                @if(($permisosRuta['crear'] ?? false))
                                    <button type="button" class="btn btn-primary ml-2" id="btn_agregar">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                @endif
                            </div>
                            <div class="col-md-12 text-right mt-2 d-none">
                                @if(($permisosRuta['eliminar'] ?? false))
                                <button type="button" class="btn btn-danger" id="btn_eliminar_masivo" disabled>
                                    <i class="fas fa-trash-alt"></i> Eliminar Seleccionados
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Tabla de Usuarios -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tabla_usuarios">
                                <thead>
                                    <tr>
                                        @if(($permisosRuta['eliminar'] ?? false))
                                        <th width="30">
                                            <input type="checkbox" id="select_all">
                                        </th>
                                        @endif
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Apellido, Nombre</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th width="100">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="usuarios_tbody">
                                    <!-- Los usuarios se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div id="info_paginacion"></div>
                            </div>
                            <div class="col-md-6">
                                <nav>
                                    <ul class="pagination justify-content-end" id="paginacion">
                                        <!-- La paginación se generará dinámicamente -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="modal_eliminar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este usuario?</p>
                <p><strong id="usuario_a_eliminar"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn_confirmar_eliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Eliminación Masiva -->
<div class="modal fade" id="modal_eliminar_masivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación Masiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar los usuarios seleccionados?</p>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn_confirmar_eliminar_masivo">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Persona -->
<div class="modal fade" id="modal_agregar_persona" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nueva Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_nueva_persona">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nueva_persona_nombre">Nombre</label>
                                <input type="text" class="form-control" id="nueva_persona_nombre" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nueva_persona_apellido">Apellido</label>
                                <input type="text" class="form-control" id="nueva_persona_apellido" name="apellido" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nueva_persona_dni">DNI</label>
                                <input type="text" class="form-control" id="nueva_persona_dni" name="dni">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nueva_persona_cuil">CUIL</label>
                                <input type="text" class="form-control" id="nueva_persona_cuil" name="cuil">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn_guardar_persona">Guardar Persona</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/usuarios.js') }}"></script>
<script>
    // Variables globales para el módulo
    window.Laravel = window.Laravel || {};
    window.Laravel.baseUrl = '{{ url('/') }}';
    window.Laravel.csrfToken = '{{ csrf_token() }}';

    // Inicializar el módulo cuando el documento esté listo
    $(document).ready(function() {
        if (typeof initUsuarios === 'function') {
            initUsuarios();
        }
    });
</script>
@endpush
