@extends('layouts.main')

@section('title', 'Productividad | ZonaCapital')

@section('header-title', 'Productividad')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Productividad</li>
@endsection

@section('content')
    <!-- Hidden permissions inputs for JavaScript -->
    <input type="hidden" id="permiso_crear" value="{{ $permisos['crear'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_leer" value="{{ $permisos['leer'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_editar" value="{{ $permisos['editar'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_eliminar" value="{{ $permisos['eliminar'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_jefe" value="{{ $tienePermisoJefe ? 1 : 0 }}">
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Action Buttons -->


    <!-- List Card with Filters -->
    <div id="seccion-listado" class="@if ($permisos['leer'] ?? false) d-block @else d-none @endif">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Registros de Productividad</h3>
                <div>
                    @if ($permisos['crear'] ?? false)
                        <button type="button" id="btnAgregar" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Agregar Productividad
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form id="formFiltros" action="{{ route('productividad.filtrar') }}" method="GET">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filterAnio" class="form-label">Año</label>
                                <select class="form-select" id="filterAnio" name="anio">
                                    <option value="0">Todos</option>
                                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}">
                                            {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filterMes" class="form-label">Mes</label>
                                <select class="form-select" id="filterMes" name="mes">
                                    <option value="0">Todos</option>
                                    @foreach ($meses as $numero => $nombre)
                                        <option value="{{ $numero }}">
                                            {{ $nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filterProfesional" class="form-label">Profesional</label>
                                <select class="form-select" id="filterProfesional" name="profesional">
                                    <option value="0">Todos</option>
                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->idEmpleado }}"
                                            {{ $filtros['profesional'] == $empleado->idEmpleado ? 'selected' : '' }}>
                                            {{ $empleado->Apellido }}, {{ $empleado->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filterEfector" class="form-label">Efector</label>
                                <select class="form-select" id="filterEfector" name="efector">
                                    <option value="0">Todos</option>
                                    @foreach ($servicios as $servicio)
                                        <option value="{{ $servicio->idServicio }}"
                                            {{ $filtros['efector'] == $servicio->idServicio ? 'selected' : '' }}>
                                            {{ $servicio->servicio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filterServicio" class="form-label">Servicio</label>
                                <select class="form-select" id="filterServicio" name="servicio">
                                    <option value="0">Todos</option>
                                    @foreach ($especialidades as $especialidad)
                                        <option value="{{ $especialidad->id }}"
                                            {{ $filtros['servicio'] == $especialidad->id ? 'selected' : '' }}>
                                            {{ $especialidad->especialidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary mb-2 flex-fill">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                            @if ($permisos['leer'] ?? false)
                                <button type="button" id="btnExportar" class="btn btn-success mb-2 flex-fill">
                                    <i class="bi bi-download me-1"></i> Exportar Excel
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
                                <th style="width: 80px">Año</th>
                                <th style="width: 80px">Mes</th>
                                <th>Efector</th>
                                <th>Personal</th>
                                <th>Operador</th>
                                <th>Fecha registro</th>
                                <th style="width: 100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-productividad">

                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="paginacion-contenedor" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- Formulario de Productividad - Inicialmente oculto -->
    <div id="seccion-formulario" class="d-none">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" id="titulo-formulario">Registro de Productividad</h3>
            </div>
            <div class="card-body">
                <form role="form" id="formProductividad">
                    <input type="hidden" id="idProductividad" name="idProductividad" value="">
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="anio">Periodo:</label>
                            <select class="form-select" onchange="getMesesCerrados()" name="anio" required
                                id="anio">
                                <option value="{{ intval(date('Y')) - 1 }}">{{ intval(date('Y')) - 1 }}</option>
                                <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                                <option value="{{ intval(date('Y')) + 1 }}">{{ intval(date('Y')) + 1 }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">

                            <select class="form-select" style="margin-top: 22px;" name="mes" required
                                id="mes">
                                <option value="">-MES-</option>
                                @foreach ($meses as $numero => $nombre)
                                    <option value="{{ $numero }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($tienePermisoJefe)
                            <div class="col-md-3" style="margin-top:22px;">
                                <button type="button" id="btnCerrarMes" class="btn btn-primary">Cerrar
                                    Mes</button>
                            </div>
                        @endif
                    </div>
                    <div class="row mt-3">
                        @if (!$tienePermisoJefe)
                            <div class="form-group col">
                                <label for="efector">Efector:</label><br>
                                <span
                                    id="efectorNombre">{{ $efectorUsuario ? $efectorUsuario->servicio : 'Sin efector asignado' }}</span>
                                <input type="hidden" id="efector" name="efector"
                                    value="{{ $efectorUsuario ? $efectorUsuario->idServicio : '' }}">
                            </div>
                        @else
                            <div class="form-group col">
                                <label for="efectorSel">Efector:</label>
                                <select class="form-select" name="efectorSel" required id="efectorSel">
                                    <option value="">-EFECTOR-</option>
                                    @foreach ($servicios as $servicio)
                                        <option value="{{ $servicio->idServicio }}">{{ $servicio->servicio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-group col">
                            <label for="profe">Profesional:</label>
                            <select class="form-select" name="idPersonal" required id="idPersonal">
                                <option value="">-SELECCIONAR-</option>
                                @foreach ($empleados as $empleado)
                                    <option value="{{ $empleado->idEmpleado }}">{{ $empleado->Apellido }},
                                        {{ $empleado->Nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col">
                            <label for="servicio">Especialidad:</label>
                            <select class="form-select" name="servicio" required id="servicio">
                                <option value="" selected>-SELECCIONAR-</option>
                                @foreach ($especialidades as $especialidad)
                                    <option value="{{ $especialidad->id }}">{{ $especialidad->especialidad }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col">
                            <label for="dias">Días trabajados:</label>
                            <input type="number" id="dias" name="dias" required class="form-control">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <table class="table table_prod table-striped" border="1">
                            <thead>
                                <tr>
                                    <th colspan="7" style="text-align:center;background: #ffd1e5;">FEMENINO
                                    </th>
                                </tr>
                                <tr style="text-align:center;background: #ffd1e5;">
                                    <th>1 < AÑO</th>
                                    <th>1 AÑO</th>
                                    <th>2 A 4 AÑO</th>
                                    <th>5 A 9 AÑO</th>
                                    <th>10 A 14 AÑO</th>
                                    <th>15 A 49 AÑO</th>
                                    <th>> A 50</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input name="c_0_0" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_0_1" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_0_2" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_0_3" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_0_4" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_0_5" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_0_6" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row pt-3">
                        <table class="table table_prod table-striped">
                            <thead>
                                <tr>
                                    <th colspan="7" style="text-align:center;background: #b9dbff;">MASCULINO
                                    </th>
                                </tr>
                                <tr style="text-align:center;background: #b9dbff;">
                                    <th>1 < AÑO</th>
                                    <th>1 AÑO</th>
                                    <th>2 A 4 AÑO</th>
                                    <th>5 A 9 AÑO</th>
                                    <th>10 A 14 AÑO</th>
                                    <th>15 A 49 AÑO</th>
                                    <th>> A 50</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input name="c_1_0" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_1_1" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_1_2" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_1_3" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_1_4" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_1_5" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                    <td><input name="c_1_6" type="text" class="form-control form-control-sm"
                                            value="0" min="0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="card-footer">
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

    @if (!($permisos['leer'] ?? false))
        <div class="alert alert-warning">
            No tiene permisos para ver el listado de productividad.
        </div>
    @endif
@endsection


@push('scripts')
    <!-- Include custom JavaScript file -->
    <script src="{{ asset('js/productividad.js') }}"></script>
@endpush

@push('styles')
    <style>
        /* Estilos adicionales para la tabla de productividad */
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

        /* Estilos para las tablas de datos del formulario */
        .table_prod th {
            text-align: center;
            font-size: 0.9rem;
        }

        .form-control-sm {
            text-align: center;
        }
    </style>
@endpush
