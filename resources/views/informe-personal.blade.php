@extends('layouts.main')

@section('title', 'Informe del Personal | ZonaCapital')

@section('header-title', 'Informe del Personal')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Informe Personal</li>
@endsection

@section('content')
    <!-- Hidden permissions inputs for JavaScript -->
    <input type="hidden" id="permiso_crear" value="{{ $permisos['crear'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_leer" value="{{ $permisos['leer'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_editar" value="{{ $permisos['editar'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_eliminar" value="{{ $permisos['eliminar'] ? 1 : 0 }}">
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Main Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="form_filter">
                        <!-- Fila 1: Datos principales y botones -->
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-2">
                                <label for="dni_fil" class="form-label">DNI</label>
                                <input type="text" class="form-control form-control-sm" id="dni_fil" name="dni_fil"
                                    placeholder="DNI">
                            </div>

                            <div class="col-md-3">
                                <label for="apellido_nombre_fil" class="form-label">Apellido y Nombre</label>
                                <input type="text" class="form-control form-control-sm" id="apellido_nombre_fil"
                                    name="apellido_nombre_fil" placeholder="Apellido o nombre">
                            </div>

                            <div class="col-md-2">
                                <label for="legajo_fil" class="form-label">Legajo</label>
                                <input type="text" class="form-control form-control-sm" id="legajo_fil" name="legajo_fil"
                                    placeholder="Legajo">
                            </div>

                            <div class="col-md-2">
                                <label for="estado_fil" class="form-label">Estado</label>
                                <select class="form-select form-select-sm" name="estado_fil" id="estado_fil">
                                    <option selected value="">Todos</option>
                                    <option value="1">ACTIVO</option>
                                    <option value="2">INACTIVO</option>
                                    <option value="3">BAJA</option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="exportar()">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                                <button type="button" id="btnLimpiarFiltros" class="btn btn-warning btn-sm"
                                    title="Limpiar todos los filtros">
                                    <i class="bi bi-eraser"></i> Limpiar
                                </button>
                            </div>
                        </div>

                        <!-- Fila 2: Sexo, Edad, Profesión -->
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="sexo_fil" class="form-label">Sexo</label>
                                <select class="form-select form-select-sm" id="sexo_fil" name="sexo_fil">
                                    <option value="">Todos</option>
                                    <option value="1">Masculino</option>
                                    <option value="2">Femenino</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="edad_fil" class="form-label">Edad</label>
                                <input type="text" class="form-control form-control-sm" id="edad_fil" name="edad_fil"
                                    placeholder="20-35">
                            </div>

                            <div class="col-md-2">
                                <label for="anti_fil" class="form-label">Antigüedad</label>
                                <input type="text" class="form-control form-control-sm" id="anti_fil" name="anti_fil"
                                    placeholder="20-35">
                            </div>

                            <div class="col-md-2">
                                <label for="anti_ap_fil" class="form-label">Antigüedad AP</label>
                                <input type="text" class="form-control form-control-sm" id="anti_ap_fil" name="anti_ap_fil"
                                    placeholder="20-35">
                            </div>

                            <div class="col-md-4">
                                <label for="prof_fil" class="form-label">Profesión</label>
                                <select class="select2" name="prof_fil" id="prof_fil">
                                    <option value="">Todas</option>
                                    @foreach ($profesiones as $profesion)
                                        <option value="{{ $profesion->idprofesion }}">{{ $profesion->profesion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Fila 3: Cargo, Relación, Función -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="cargo_fil" class="form-label">Cargo</label>
                                <select class="select2" name="cargo_fil" id="cargo_fil">
                                    <option value="">Todos</option>
                                    @foreach ($cargos as $cargo)
                                        <option value="{{ $cargo->idCargo }}">{{ $cargo->cargo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="relacion_fil" class="form-label">Relación</label>
                                <select class="select2" name="relacion_fil" id="relacion_fil">
                                    <option value="">Todas</option>
                                    @foreach ($relaciones as $relacion)
                                        <option value="{{ $relacion->idRelacion }}">{{ $relacion->Relacion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="funcion_fil" class="form-label">Función</label>
                                <select class="select2" name="funcion_fil" id="funcion_fil">
                                    <option value="">Todas</option>
                                    @foreach ($funciones as $funcion)
                                        <option value="{{ $funcion->IdFuncion }}">{{ $funcion->Funcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Fila 4: Organigrama -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="ger_fil" class="form-label">Gerencia</label>
                                <select class="form-select form-select-sm" onchange="changeOrganigrama(0, this)"
                                    name="ger_fil" id="ger_fil">
                                    <option selected value="">Todas</option>
                                    @foreach ($gerencias as $gerencia)
                                        <option value="{{ $gerencia->idGerencia }}">{{ $gerencia->Gerencia }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="dep_fil" class="form-label">Departamento</label>
                                <select class="form-select form-select-sm" onchange="changeOrganigrama(1, this)"
                                    name="dep_fil" id="dep_fil">
                                    <option value="">Todos</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="servicio_fil" class="form-label">Servicio</label>
                                <select class="select2" onchange="changeOrganigrama(2, this)"
                                    name="servicio_fil" id="servicio_fil">
                                    <option value="">Todos</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="sector_fil" class="form-label">Sector</label>
                                <select class="form-select form-select-sm" name="sector_fil" id="sector_fil">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila 5: Fechas Alta -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="d_ap_fil" class="form-label">F. Alta AP (desde)</label>
                                <div class="input-group input-group-sm date" id="desde_ap_fil_picker"
                                    data-td-target-input="nearest">
                                    <input type="text" name="d_ap_fil" id="d_ap_fil"
                                        class="form-control datetimepicker-input"
                                        data-target="#desde_ap_fil_picker" placeholder="dd/mm/yyyy" />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="h_ap_fil" class="form-label">F. Alta AP (hasta)</label>
                                <div class="input-group input-group-sm date" id="hasta_ap_fil_picker"
                                    data-td-target-input="nearest">
                                    <input type="text" name="h_ap_fil" id="h_ap_fil"
                                        class="form-control datetimepicker-input"
                                        data-target="#hasta_ap_fil_picker" placeholder="dd/mm/yyyy" />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="d_fil" class="form-label">F. Alta (desde)</label>
                                <div class="input-group input-group-sm date" id="desde_fil_picker"
                                    data-td-target-input="nearest">
                                    <input type="text" name="d_fil" id="d_fil"
                                        class="form-control datetimepicker-input"
                                        data-target="#desde_fil_picker" placeholder="dd/mm/yyyy" />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="h_fil" class="form-label">F. Alta (hasta)</label>
                                <div class="input-group input-group-sm date" id="hasta_fil_picker"
                                    data-td-target-input="nearest">
                                    <input type="text" name="h_fil" id="h_fil"
                                        class="form-control datetimepicker-input"
                                        data-target="#hasta_fil_picker" placeholder="dd/mm/yyyy" />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 6: Jornada, Certifica, Fecha Baja -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="jornada_fil" class="form-label">Jornada</label>
                                <select class="form-select form-select-sm" name="jornada_fil" id="jornada_fil">
                                    <option selected value="">Todas</option>
                                    @foreach ($jornadas as $jornada)
                                        <option value="{{ $jornada->IdTipoJornada }}">{{ $jornada->Jornada }}
                                            ({{ $jornada->Horas }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="cert_fil" class="form-label">Certifica</label>
                                <select class="form-select form-select-sm" name="cert_fil" id="cert_fil">
                                    <option value="">Todos</option>
                                    @foreach ($jefes as $jefe)
                                        <option value="{{ $jefe->idEmpleado }}">
                                            {{ $jefe->Apellido }}, {{ $jefe->Nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="d_b_fil" class="form-label">F. Baja (desde)</label>
                                <div class="input-group input-group-sm date" id="desde_baja_fil_picker"
                                    data-td-target-input="nearest">
                                    <input type="text" name="d_b_fil" id="d_b_fil"
                                        class="form-control datetimepicker-input"
                                        data-target="#desde_baja_fil_picker" placeholder="dd/mm/yyyy" />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="h_b_fil" class="form-label">F. Baja (hasta)</label>
                                <div class="input-group input-group-sm date" id="hasta_baja_fil_picker"
                                    data-td-target-input="nearest">
                                    <input type="text" name="h_b_fil" id="h_b_fil"
                                        class="form-control datetimepicker-input"
                                        data-target="#hasta_baja_fil_picker" placeholder="dd/mm/yyyy" />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Results Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-striped table-hover table-sm" id="table_persona">
                            <thead>
                                <tr>
                                    <th>Legajo</th>
                                    <th>Apellido/Nombre</th>
                                    <th>DNI</th>
                                    <th>Edad</th>
                                    <th>Teléfono</th>
                                    <th>Servicio</th>
                                    <th>F. Ingreso</th>
                                    <th class="action-column">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="table_data">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/informe-personal.js') }}?v={{ time() }}"></script>
@endpush
