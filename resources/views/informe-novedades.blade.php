@extends('layouts.main')

@section('title', 'Informe de Novedades | ZonaCapital')

@section('header-title', 'Informe de Novedades')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Informe de Novedades</li>
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
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Control de Horario</h3>
                </div>
                <div class="card-body">
                    <form id="form_buscar">
                        <!-- Fila 1: Periodo y Exportar -->
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-3">
                                <label for="d_fil" class="form-label">Período:</label>
                                <div class="input-group date" id="desde_fil" data-target-input="nearest">
                                    <input type="text" id="d_fil" name="d_fil" 
                                        class="form-control datetimepicker-input" 
                                        data-target="#desde_fil" placeholder="MM/YYYY" />
                                    <span class="input-group-text" data-target="#desde_fil" data-toggle="datetimepicker">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary me-2" onclick="buscar()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportar()">
                                    <i class="bi bi-file-earmark-excel"></i> Exportar
                                </button>
                            </div>
                        </div>

                        <!-- Fila 2: Organigrama -->
                        @if($todoPersonal ?? false)
                        <!-- Tiene permiso: mostrar selectores completos -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="ger_fil" class="form-label">Gerencia:</label>
                                <select class="form-select form-select-sm select2" onchange="changeOrganigrama(0, this)"
                                    name="ger_fil" id="ger_fil">
                                    <option selected value="">-TODAS-</option>
                                    @foreach ($gerencias as $gerencia)
                                        <option value="{{ $gerencia->idGerencia }}">{{ $gerencia->Gerencia }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="dep_fil" class="form-label">Departamento:</label>
                                <select class="form-select form-select-sm select2" onchange="changeOrganigrama(1, this)"
                                    name="dep_fil" id="dep_fil">
                                    <option value="">-</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="servicio_fil" class="form-label">Servicio:</label>
                                <select class="form-select form-select-sm select2" onchange="changeOrganigrama(2, this)"
                                    name="servicio_fil" id="servicio_fil">
                                    <option value="">-</option>
                                </select>
                            </div>
                        </div>
                        @else
                        <!-- NO tiene permiso: mostrar solo su servicio -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="servicio_fil" class="form-label">Servicio:</label>
                                <select class="form-select form-select-sm select2"
                                    name="servicio_fil" id="servicio_fil">
                                    @if($servicioDefault)
                                        @foreach ($servicios as $servicio)
                                            <option value="{{ $servicio->idServicio }}" selected>{{ $servicio->Servicio }}</option>
                                        @endforeach
                                    @else
                                        <option value="">Sin servicio asignado</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        @endif

                        <!-- Fila 3: Certifica y Personal (búsqueda específica) -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="certifica" class="form-label">Certifica:</label>
                                <select class="form-select form-select-sm select2-ajax" id="certifica" name="certifica" 
                                    data-url="{{ route('personal.buscar') }}" data-placeholder="Buscar jefe...">
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="personal" class="form-label">Personal:</label>
                                <select class="form-select form-select-sm select2-ajax" id="personal" name="personal" 
                                    data-url="{{ route('personal.buscar') }}" data-placeholder="Buscar personal...">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- Info message -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        Seleccione un período y haga clic en "Buscar" para previsualizar o "Exportar" para descargar el informe en Excel.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="row mt-3" id="resultados_section" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Resultados de la búsqueda</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="overflow: auto; max-height: 600px;">
                        <table class="table table-bordered table-sm table-striped" id="tabla_resultados" style="min-width: 1500px;">
                        </table>
                    </div>
                    <div class="mt-2 text-muted">
                        <small>Total de registros: <span id="total_registros">0</span></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Variables globales para el módulo
        var idEmpleado = 0;
        var idPersonal = "";
        var idJefe = 0;
    </script>
    <script src="{{ asset('js/informe-novedades.js') }}?v={{ time() }}"></script>
@endpush
