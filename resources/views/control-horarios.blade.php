@extends('layouts.main')

@section('title', 'Control de Horarios | ZonaCapital')

@section('header-title', 'Control de Horarios')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Control de Horarios</li>
@endsection

@push('styles')
<style>
    #table_informe tr td {
        border: 1px solid #8e8e8e !important;
    }
    #table_informe tr th {
        border: 1px solid #8e8e8e !important;
    }
    .table th, .table td {
        padding: 2px !important;
        font-size: 10px !important;
        padding-left: 10px !important;
        border: 1px solid #8e8e8e !important;
    }

    #table_informe thead th {
        position: sticky;
        top: 0;
        padding: 5px !important;
        background: white;
        z-index: 10;
    }

    .hover_td:hover {
        background: #c1f4b9 !important;
        cursor: pointer;
    }

    #table_informe thead th div {
        border: solid;
        margin: -5px;
        border-width: thin;
        padding: 5px;
        font-size: 12px;
    }

    /* Estilos para formato de calendario matricial */
    .table-responsive {
        overflow-x: auto;
        max-width: 100%;
        display: block;
    }

    #table_informe {
        min-width: max-content;
    }

    /* Columna sticky de Personal */
    .sticky-col, .nombre-empleado {
        position: sticky;
        left: 0;
        background: white !important;
        z-index: 5;
        min-width: 180px;
        max-width: 180px;
        font-weight: 500;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        font-size: 11px !important;
    }

    /* Header sticky para columnas de fecha */
    #table_informe thead th:nth-child(1),
    #table_informe thead th[rowspan] {
        z-index: 15;
        background: #f8f9fa !important;
    }

    /* Ajustar celdas de horario */
    .celda-horario {
        min-width: 90px;
        white-space: nowrap;
        font-size: 10px !important;
        line-height: 1.2;
        padding: 2px !important;
    }

    /* Mejorar visualización de fechas en header */
    #table_informe thead th[data-fecha] {
        min-width: 95px;
        max-width: 95px;
        vertical-align: middle !important;
        padding: 4px !important;
    }

    #table_informe thead th[data-fecha] div {
        font-weight: bold;
        font-size: 12px;
    }

    #table_informe thead th[data-fecha] small {
        display: block;
        font-weight: normal;
        font-size: 10px;
    }

    /* Ajuste para Responsive */
    @media (max-width: 768px) {
        .sticky-col, .nombre-empleado {
            min-width: 120px;
            max-width: 120px;
            font-size: 9px !important;
        }
        #table_informe thead th[data-fecha] {
            min-width: 70px;
            max-width: 70px;
        }
    }

    /* Estilos para Select2 */
    .select2-container .select2-selection--single {
        height: 38px;
        border-color: #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .select2-results__option {
        padding: 8px 12px;
    }

    /* Estilos específicos para filas de control de horarios */
    /* Usamos box-shadow y variables CSS para superar el override de Bootstrap table-striped */
    .fila-fecha {
        font-weight: bold !important;
        --bs-table-accent-bg: #f0f0f0 !important;
        --bs-table-bg: #f0f0f0 !important;
        --bs-table-bg-state: #f0f0f0 !important;
        background-color: #f0f0f0 !important;
        box-shadow: inset 0 0 0 9999px #f0f0f0 !important;
    }

    .fila-marca-incompleta {
        --bs-table-accent-bg: #ffc18a !important;
        --bs-table-bg: #ffc18a !important;
        --bs-table-bg-state: #ffc18a !important;
        background-color: #ffc18a !important;
        box-shadow: inset 0 0 0 9999px #ffc18a !important;
    }

    .fila-ausencia {
        --bs-table-accent-bg: #ff0101 !important;
        --bs-table-bg: #ff0101 !important;
        --bs-table-bg-state: #ff0101 !important;
        background-color: #ff0101 !important;
        color: white !important;
        box-shadow: inset 0 0 0 9999px #ff0101 !important;
    }

    .fila-licencia {
        --bs-table-accent-bg: #b4ffff !important;
        --bs-table-bg: #b4ffff !important;
        --bs-table-bg-state: #b4ffff !important;
        background-color: #b4ffff !important;
        box-shadow: inset 0 0 0 9999px #b4ffff !important;
    }
</style>
@endpush

@section('content')
<!-- Hidden permissions inputs -->
<input type="hidden" id="permisos" value="{{ $permisos['crear'] ? 1 : 0 }}|{{ $permisos['editar'] ? 1 : 0 }}|{{ $permisos['eliminar'] ? 1 : 0 }}">
<input type="hidden" id="todo_personal_control" value="{{ $permisos['todo_personal_control'] ?? ($permisos['extras']['todo_personal_control'] ?? 0) }}">
<input type="hidden" id="_usid" value="{{ $usuario->IdUsuario ?? '' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Modal de Control -->
<div class="modal fade" id="modal_control" tabindex="-1" aria-labelledby="modal_control_label" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_control_label">Ajuste de Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cont_editar"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" id="btn_guardar_control" class="btn btn-info">Guardar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Contenido Principal -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Control de Horarios</h3>
            </div>
            <div class="card-body">
                <!-- Formulario de Búsqueda -->
                <form id="form_buscar">
                    <div class="row">
                        <!-- Fechas y Tipo -->
                        <div class="col-md-3">
                            <label for="d_fil">Fecha desde:</label>
                            <input type="date" id="d_fil" class="form-control form-control-sm"/>
                        </div>

                        <div class="col-md-3">
                            <label for="h_fil">Fecha hasta:</label>
                            <input type="date" id="h_fil" class="form-control form-control-sm"/>
                        </div>

                        <div class="col-md-2">
                            <label for="tipo">Tipo:</label>
                            <select class="form-control form-control-sm" name="tipo" id="tipo">
                                <option value="0" selected>-TODOS-</option>
                                <option value="1">Ausencias</option>
                                <option value="4">Falta de datos</option>
                                <option value="5">Falta de datos y ausencias</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" id="btn_submit" class="btn btn-primary btn-sm">
                                    Generar <i class="fas fa-search"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="exportarPdf()">
                                    Exportar <i class="fas fa-file-pdf"></i>
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="exportarExcel()">
                                    Exportar <i class="fas fa-file-excel"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de Organigrama (solo para usuarios con permiso) -->
                    @if(($permisos['todo_personal_control'] ?? ($permisos['extras']['todo_personal_control'] ?? 0)) == 1)
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="ger_fil">Gerencia:</label>
                            <select class="form-control form-control-sm" onchange="changeOrganigrama(0,this)" name="ger_fil" id="ger_fil">
                                <option selected value="">-TODAS-</option>
                                @foreach($gerencias as $gerencia)
                                    <option value="{{ $gerencia->idGerencia }}">{{ $gerencia->Gerencia }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="dep_fil">Departamento:</label>
                            <select class="form-control form-control-sm" onchange="changeOrganigrama(1,this)" name="dep_fil" id="dep_fil">
                                <option value="">-</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="servicio_fil">Servicio:</label>
                            <select class="form-control form-control-sm" onchange="changeOrganigrama(2,this)" name="servicio_fil" id="servicio_fil">
                                <option value="">-</option>
                            </select>
                        </div>
                    </div>
                    @endif

                    <!-- Búsqueda por Personal -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="certifica">Certifica:</label>
                            <select id="certifica" class="form-control form-control-sm" style="width: 100%;"></select>
                        </div>

                        <div class="col-md-6">
                            <label for="personal">Personal:</label>
                            <select id="personal" class="form-control form-control-sm" style="width: 100%;"></select>
                        </div>
                    </div>
                </form>

                <!-- Tabla de resultados -->
                <div class="table-responsive mt-4">
                    <table id="table_informe" class="table table-striped table-hover table-sm">
                        <thead id="head_table"></thead>
                        <tbody id="table_horarios"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Select2 CSS y JS desde CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Configuración global para el módulo
window.controlHorariosConfig = {
    todoPersonalControl: {{ $permisos['todo_personal_control'] ?? ($permisos['extras']['todo_personal_control'] ?? 0) }},
    personalFull: @json($personalFull ?? []),
    personalLimit: @json($personalLimit ?? []),
    usuarioDefaults: @json($usuarioDefaults ?? ['idGerencia' => 0, 'idDepartamento' => 0, 'idServicio' => 0]),
    servicioDefault: {{ $servicioDefault ?? 0 }}
};
</script>
<script src="{{ asset('js/control-horarios.js') }}?v=2026-03-26-1520"></script>
@endpush
