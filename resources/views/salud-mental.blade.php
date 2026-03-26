@extends('layouts.main')

@section('title', 'Salud Mental | ZonaCapital')

@section('header-title', 'Salud Mental')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Salud Mental</li>
@endsection

@section('content')
    <!-- Hidden permissions inputs for JavaScript -->
    <input type="hidden" id="permiso_crear" value="{{ $permisos['crear'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_leer" value="{{ $permisos['leer'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_editar" value="{{ $permisos['editar'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_eliminar" value="{{ $permisos['eliminar'] ? 1 : 0 }}">
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- List Card with Filters -->
    <div id="seccion-listado" class="@if ($permisos['leer'] ?? false) d-block @else d-none @endif">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Registros de Salud Mental</h3>
                <div>
                    @if ($permisos['crear'] ?? false)
                        <button type="button" id="btnAgregar" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Agregar Registro
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form id="formFiltros" action="{{ route('salud-mental.filtrar') }}" method="GET">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="d_fil" class="form-label">Desde</label>
                                <input type="date" class="form-control" id="d_fil" name="d">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="h_fil" class="form-label">Hasta</label>
                                <input type="date" class="form-control" id="h_fil" name="h">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="dni_fil" class="form-label">DNI</label>
                                <input type="text" class="form-control" id="dni_fil" name="dni_fil">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="efector_sel_fil" class="form-label">Efector</label>
                                <select class="form-select" id="efector_sel_fil" name="efector">
                                    <option value="">Todos</option>
                                    @foreach ($servicios as $servicio)
                                        <option value="{{ $servicio->IdServicio }}">{{ $servicio->Nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="personal_id2" class="form-label">Profesional</label>
                                <select class="form-select" id="personal_id2" name="personal_id">
                                    <option value="">Todos</option>
                                    @foreach ($profesionales as $profesional)
                                        <option value="{{ $profesional->IdEmpleado }}">{{ $profesional->ApellidoNombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="presencia2" class="form-label">Presencia</label>
                                <select class="form-select" id="presencia2" name="presencia">
                                    <option value="">Todos</option>
                                    @foreach ($tiposDemanda as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->valor }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="turno_asignado2" class="form-label">Turno</label>
                                <select class="form-select" id="turno_asignado2" name="turno_asignado">
                                    <option value="">Todos</option>
                                    <option value="1">Mañana</option>
                                    <option value="2">Tarde</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo_demanda2" class="form-label">Tipo Demanda</label>
                                <select class="form-select" id="tipo_demanda2" name="tipo_demanda">
                                    <option value="">Todos</option>
                                    @foreach ($tiposDemanda as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->valor }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="intervencion2" class="form-label">Intervención</label>
                                <select class="form-select" id="intervencion2" name="intervencion">
                                    <option value="">Todos</option>
                                    @foreach ($intervenciones as $intervencion)
                                        <option value="{{ $intervencion->id }}">{{ $intervencion->valor }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo_problema2" class="form-label">Tipo Problema</label>
                                <select class="form-select" id="tipo_problema2" name="tipo_problema">
                                    <option value="">Todos</option>
                                    @foreach ($tiposProblematica as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->valor }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary mb-2 flex-fill">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                            @if ($permisos['leer'] ?? false)
                                <button type="button" id="btnExportar" class="btn btn-success mb-2 flex-fill">
                                    <i class="bi bi-file-excel me-1"></i> Exportar
                                </button>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px">ID</th>
                                <th>Fecha</th>
                                <th>DNI</th>
                                <th>Paciente</th>
                                <th>Efector</th>
                                <th>Profesional</th>
                                <th>Tipo Demanda</th>
                                <th>Intervención</th>
                                <th style="width: 100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-salud-mental">
                            <!-- Los datos se cargan dinámicamente con JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="paginacion-contenedor" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- Formulario de Salud Mental - Inicialmente oculto -->
    <div id="seccion-formulario" class="d-none">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" id="titulo-formulario">Registro de Salud Mental</h3>
            </div>
            <div class="card-body">
                <form role="form" id="formSaludMental">
                    <input type="hidden" id="idSaludMental" name="id" value="">
                    <input type="hidden" id="paciente_id" name="paciente_id" value="">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="efector_sel">Efector:</label>
                                <select class="form-select" name="efector_Id" required id="efector_sel">
                                    <option value="">Seleccione un efector</option>
                                    @foreach ($servicios as $servicio)
                                        <option value="{{ $servicio->IdServicio }}">{{ $servicio->Nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="dni">DNI:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="dni" name="dni" required>
                                    <button type="button" class="btn btn-primary" id="btn_buscar_dni">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fecha_consulta">Fecha de Consulta:</label>
                                <input type="date" name="fecha_consulta" id="fecha_consulta"
                                    value="{{ date('Y-m-d') }}" required class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Información del paciente - Inicialmente oculto -->
                    <div id="form_persona" class="d-none">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ApellidoNombre">Apellido y Nombre:</label>
                                    <input type="text" class="form-control" id="ApellidoNombre" name="ApellidoNombre"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="FechaNacimiento">Fecha de Nacimiento:</label>
                                    <input type="date" class="form-control" id="FechaNacimiento"
                                        name="FechaNacimiento">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Sexo:</label>
                                    <div class="d-flex mt-2">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="sexo" id="sexo_m"
                                                value="1">
                                            <label class="form-check-label" for="sexo_m">
                                                Masculino
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sexo" id="sexo_f"
                                                value="2">
                                            <label class="form-check-label" for="sexo_f">
                                                Femenino
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="domicilio">Domicilio:</label>
                                    <input type="text" class="form-control" id="domicilio" name="domicilio">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="celular">Celular:</label>
                                    <input type="text" class="form-control" id="celular" name="celular">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="personal_id">Profesional:</label>
                                    <select class="form-select" name="personal_id" required id="personal_id">
                                        <option value="">Seleccione un profesional</option>
                                        @foreach ($profesionales as $profesional)
                                            <option value="{{ $profesional->IdEmpleado }}">
                                                {{ $profesional->ApellidoNombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="presencia">Presencia:</label>
                                    <select class="form-select" name="presencia" required id="presencia">
                                        <option value="">Seleccione una opción</option>
                                        @foreach ($tiposDemanda as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->valor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="turno_asignado">Turno:</label>
                                    <select class="form-select" name="turno_asignado" required id="turno_asignado">
                                        <option value="">Seleccione una opción</option>
                                        <option value="1">Mañana</option>
                                        <option value="2">Tarde</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_demanda">Tipo de Demanda:</label>
                                    <select class="form-select" name="tipo_demanda" required id="tipo_demanda">
                                        <option value="">Seleccione una opción</option>
                                        @foreach ($tiposDemanda as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->valor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="intervencion">Intervención:</label>
                                    <select class="form-select" name="intervencion" required id="intervencion">
                                        <option value="">Seleccione una opción</option>
                                        @foreach ($intervenciones as $intervencion)
                                            <option value="{{ $intervencion->id }}">{{ $intervencion->valor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_problema">Tipo de Problema:</label>
                                    <select class="form-select" name="tipo_problema" required id="tipo_problema">
                                        <option value="">Seleccione una opción</option>
                                        @foreach ($tiposProblematica as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->valor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="evolucion">Evolución:</label>
                                    <textarea class="form-control" name="evolucion" id="evolucion" rows="4" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer d-flex gap-2" id="footer_btn">
                @if ($permisos['crear'] || $permisos['editar'])
                    <button type="button" id="btnGuardar" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar
                    </button>
                @endif

                @if ($permisos['crear'])
                    <button type="button" id="btnLimpiar" class="btn btn-warning">
                        <i class="bi bi-eraser me-1"></i> Limpiar
                    </button>
                @endif

                @if ($permisos['eliminar'])
                    <button type="button" id="btnEliminar" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                @endif

                <button type="button" id="btnVolver" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="modal_eliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-danger">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarLabel">¡Atención!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar este registro?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_eliminar_modal" class="btn btn-outline-light">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    @if (!($permisos['leer'] ?? false))
        <div class="alert alert-warning">
            No tiene permisos para ver el listado de salud mental.
        </div>
    @endif
@endsection

@push('scripts')
    <!-- Include custom JavaScript file -->
    <script src="{{ asset('js/salud-mental.js') }}"></script>
@endpush

@push('styles')
    <style>
        /* Estilos adicionales para la tabla */
        .table th {
            background-color: #f4f6f9;
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
        }

        /* Estilos responsivos */
        @media (max-width: 767.98px) {
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
@endpush
