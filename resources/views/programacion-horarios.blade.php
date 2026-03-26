@extends('layouts.app')

@section('title', 'Programación de Horarios - Zona Capital')

@push('styles')
<style>
    .color-circle {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-left: 10px;
    }

    .darkviolet { background-color: darkviolet; }
    .darkgreen { background-color: darkgreen; }
    .b4ffff { background-color: #b4ffff; }
    .small-text { font-size: 0.875rem; }

    .custom-checkbox {
        display: inline;
        position: relative;
        padding-left: 35px;
        margin-right: 15px;
        cursor: pointer;
    }

    .custom-checkbox input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .checkmark {
        position: absolute;
        top: -1px;
        left: 0;
        height: 24px;
        width: 20px;
        background-color: #fff;
        border: 2px solid #0062cc;
        border-radius: 5px;
        margin-left: 5px;
    }

    .checkmark::after {
        content: "F";
        font-size: 15px;
        color: #000;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .custom-checkbox input[type="checkbox"]:checked~.checkmark {
        background-color: #007bff;
    }

    .custom-checkbox input[type="checkbox"]:checked~.checkmark::after {
        color: #fff;
    }

    a:hover { cursor: pointer; }

    /* Estilos para tabla de programación */
    #tabla_programacion tr td {
        border: 1px solid #8e8e8e !important;
        min-width: 200px;
        font-size: 13px;
        padding: 3px 3px 3px 10px !important;
        color: black !important; /* Asegurar texto negro */
    }

    #tabla_programacion tr th {
        border: 1px solid #8e8e8e !important;
        min-width: 150px;
        color: black !important; /* Asegurar texto negro */
        background-color: #fff !important;
    }

    .nombre_personal {
        min-width: 250px !important;
        font-size: 12px;
        color: black !important;
    }

    /* Columnas fijas */
    #tabla_programacion td:nth-child(1),
    #tabla_programacion th:nth-child(1) {
        box-shadow: inset 0px 0px 1px 1px;
        left: 0px;
        position: sticky;
        z-index: 2;
        background-color: #fff !important;
    }

    #tabla_programacion thead tr th {
        box-shadow: inset 0px 0px 1px 1px;
        top: 0px;
        position: sticky;
        z-index: 1;
        background-color: #fff !important;
    }

    /* Primera celda del header (esquina) */
    #tabla_programacion thead tr th:nth-child(1) {
        z-index: 3;
        background-color: #fff !important;
    }

    .td_horario:hover {
        background: #d5f9d5 !important;
    }

    .nombre_personal a {
        color: black !important;
        text-decoration: none;
    }

    .nombre_personal a:hover {
        color: grey !important;
        text-decoration: underline;
    }

    .shift-row {
        transition: background-color 0.3s, color 0.3s;
    }

    .normal { background-color: transparent; color: black; }
    .contrato { background-color: darkgreen; color: white; }
    .paga { background-color: darkviolet; color: white; }

    .cont_horario {
        position: relative;
        margin-bottom: 2px;
        padding: 2px 5px;
        border-radius: 3px;
        display: inline-block;
        margin-right: 2px;
        font-weight: bold;
    }

    .horario-input { width: 80px; font-size: 12px; }
    .btn-xs {
        padding: 0.125rem 0.25rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.15rem;
    }

    /* Estilos para headers fijos (solo para columna Personal) */
    .fixed-header {
        position: sticky;
        right: 0;
        z-index: 1;
        border-left: 1px solid #8e8e8e !important;
    }

    /* Estilos para headers de totales - solo sticky verticalmente */
    .total-header {
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: #fff !important;
        border-left: 1px solid #8e8e8e !important;
    }

    /* Headers de totales - solo sticky en la parte superior */
    #tabla_programacion th:nth-last-child(4),
    #tabla_programacion th:nth-last-child(3),
    #tabla_programacion th:nth-last-child(2) {
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: #fff !important;
        border-left: 1px solid #8e8e8e !important;
    }

    /* Celdas de totales - comportamiento normal, no sticky */
    #tabla_programacion td:nth-last-child(4),
    #tabla_programacion td:nth-last-child(3),
    #tabla_programacion td:nth-last-child(2) {
        border-left: 1px solid #8e8e8e !important;
        background-color: #fff !important;
    }

    /* Override Bootstrap 5 table styles */
    .table > :not(caption) > * > * {
        padding: 3px 3px 3px 10px !important;
        background-color: transparent;
        border-bottom-width: 1px;
        box-shadow: none;
    }

    /* Asegurar que el texto sea visible */
    .table, .table td, .table th, .table-striped > tbody > tr > td, .table-striped > tbody > tr > th {
        color: black !important;
    }
</style>
@endpush

@section('content')
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Programación de Horarios</h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Programación de Horarios</h3>
                        </div>

                        <form id="form_buscar">
                            <div class="card-body">
                                <div class="alert alert-warning" id="deshabilitado_prog" role="alert" style="display:none;">
                                    <strong>Advertencia:</strong> Este periodo ya se encuentra deshabilitado para programar.
                                </div>

                                                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <label for="d_fil">Mes:</label>
                                        <div class="input-group">
                                            <input type="text" id="d_fil" value="{{ date('m/Y') }}" class="form-control" placeholder="Selecciona mes/año" readonly />
                                            <div class="input-group-text" id="calendar-trigger" style="cursor: pointer;">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                        </div>
                                    </div>

                                    @if(isset($permisos['todo_personal_pla']) && $permisos['todo_personal_pla'])
                                    {{-- Filtros jerárquicos para usuarios con permisos --}}
                                    <div class="form-group col-md-2">
                                        <label for="gerencia_fil">Gerencia:</label>
                                        <select class="form-control select2-filtro" name="gerencia_fil" id="gerencia_fil">
                                            <option value="">-TODAS-</option>
                                            @foreach($gerencias ?? [] as $gerencia)
                                                <option value="{{ $gerencia->IdGerencia }}">{{ $gerencia->Gerencia }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="departamento_fil">Departamento:</label>
                                        <select class="form-control select2-filtro" name="departamento_fil" id="departamento_fil">
                                            <option value="">-TODOS-</option>
                                            @foreach($departamentos ?? [] as $departamento)
                                                <option value="{{ $departamento->idDepartamento }}" data-gerencia="{{ $departamento->idGerencia }}">
                                                    {{ $departamento->departamento }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif

                                    <div class="form-group col-md-3">
                                        <label for="servicios_fil">Servicio:</label>
                                        <select class="form-control select2" name="servicios_fil" id="servicios_fil">
                                            @if(!isset($permisos['todo_personal_pla']) || !$permisos['todo_personal_pla'])
                                                <option selected value="0">-MIS SERVICIOS-</option>
                                            @else
                                                <option value="">-TODOS-</option>
                                            @endif

                                            @foreach($servicios ?? [] as $servicio)
                                                <option value="{{ $servicio->IdServicio }}" 
                                                        data-jefe="{{ $servicio->JefeNombreCompleto }}" 
                                                        data-departamento="{{ $servicio->idDepartamento ?? '' }}" 
                                                        data-gerencia="{{ $servicio->idGerencia ?? '' }}">
                                                    {{ $servicio->Servicio }}
                                                    @if($servicio->JefeNombreCompleto && $servicio->JefeNombreCompleto !== 'Sin jefe asignado')
                                                        <small> (Jefe: {{ $servicio->JefeNombreCompleto }})</small>
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="clasificacion_fil">Clasificación:</label>
                                        <select class="form-control select2-filtro" name="clasificacion_fil" id="clasificacion_fil">
                                            <option value="">-TODAS-</option>
                                            @foreach($clasificaciones ?? [] as $clasif)
                                                <option value="{{ $clasif->idClasificacion }}">{{ $clasif->clasificacion }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-auto d-flex align-items-end gap-2">
                                        <button type="button" id="btn_submit" class="btn btn-primary btn-sm" onclick="getProgramacion()">
                                            Buscar <i class="fas fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="exportarExcel()">
                                            Exportar <i class="fas fa-file-excel"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="exportarExcelTurnos()">
                                            Exportar Turnos <i class="fas fa-file-excel"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Fila de búsqueda y paginación -->
                                <div class="row mt-3 align-items-center" id="contenedor_busqueda_paginacion" style="display: none;">
                                    <div class="form-group col-md-4">
                                        <label for="busqueda_empleado">Buscar empleado (Nombre o DNI):</label>
                                        <div class="input-group">
                                            <input type="text" id="busqueda_empleado" class="form-control form-control-sm" placeholder="Ej: Juan o 12345678" />
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="limpiarBusqueda()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-8 d-flex justify-content-end align-items-end">
                                        <nav aria-label="Paginación">
                                            <ul class="pagination pagination-sm mb-0" id="controles_paginacion">
                                                <!-- Controles de paginación generados dinámicamente -->
                                            </ul>
                                        </nav>
                                        <span id="info_paginacion" class="ms-3 text-muted small"></span>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Tabla principal -->
                        <div style="overflow-x: auto; max-height: 700px;">
                            <table id="tabla_programacion" class="table table-striped" style="min-width: 2200px !important;">
                                <thead>
                                    <tr id="head_table">
                                        <th class="nombre_personal fixed-header">Personal</th>
                                        <!-- Headers de días se generan dinámicamente -->
                                    </tr>
                                </thead>
                                <tbody id="table_horarios"></tbody>
                            </table>
                        </div>

                        <!-- Leyenda -->
                        <div class="container mt-3">
                            <table class="table table-bordered table-sm">
                                <tbody>
                                    <tr>
                                        <td class="small-text">Guardias pagas</td>
                                        <td><div class="color-circle darkviolet"></div></td>
                                    </tr>
                                    <tr>
                                        <td class="small-text">Guardias contrato</td>
                                        <td><div class="color-circle darkgreen"></div></td>
                                    </tr>
                                    <tr>
                                        <td class="small-text">Licencia</td>
                                        <td><div class="color-circle b4ffff"></div></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Form para exportar Excel -->
                        <form id="exportar_excel" style="display:none" method="post" action="{{ route('programacion-personal.exportar') }}" target="_blank">
                            @csrf
                            <input type="hidden" name="accion" value="informeGenerico" />
                            <textarea name="body"></textarea>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal programación por día -->
    <div class="modal fade" id="modal_programacion_dia" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Horarios - <span id="nombre_persona_horario_x_dia" style="font-size: 15px;"></span></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex mb-2">
                        <button type="button" alt="Agregar Horario" title="Agregar Horario" onclick="agregarHorarioDia()" class="btn btn-primary">
                            Agregar Horario <i class="fas fa-plus"></i>
                        </button>
                        <div class="btn-group ms-2">
                            <button type="button" onclick="agregarTurno('06:00','14:00')" class="btn btn-info">Mañana</button>
                            <button type="button" onclick="agregarTurno('14:00','22:00')" class="btn btn-info">Tarde</button>
                            <button type="button" onclick="agregarTurno('22:00','06:00')" class="btn btn-info">Noche</button>
                        </div>
                    </div>
                    <table class="table table-striped mt-3">
                        <thead>
                            <tr>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Tipo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="table_hoararios_por_dia"></tbody>
                    </table>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-success btn-sm" id="btn_guardar_horario_x_dia" onclick="guardarHorarioXDia()">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cargar horario -->
    <div class="modal fade" id="modal_cargar_horario" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Cargar Horario - <span id="nombre_persona_cargar"></span></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="prog_simple-tab" data-bs-toggle="tab" data-bs-target="#prog_simple" role="tab">
                                Programación simple
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="guardias_cont-tab" data-bs-toggle="tab" data-bs-target="#guardias_cont" role="tab">
                                Guardias Contrato
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="guardias_pagas-tab" data-bs-toggle="tab" data-bs-target="#guardias_pagas" role="tab">
                                Guardias Pagas
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab Programación Simple -->
                        <div class="tab-pane fade show active" id="prog_simple" role="tabpanel">
                            <div class="row pt-3">
                                <div class="form-group col-md-6">
                                    <label for="tipo_prog">Tipo:</label>
                                    <select class="form-control" name="tipo_prog" id="tipo_prog">
                                        <option value="0" selected>HORARIO SIMPLE</option>
                                        <option value="1">HORARIO ROTATIVO</option>
                                        <option value="2">GUARDIAS PAGAS</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6" style="margin-top: 32px;">
                                    <button type="button" id="btn_guardar_prog_simple" class="btn btn-primary btn-sm">
                                        Guardar <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col">
                                    <label for="desde_prog">Desde:</label>
                                    <input type="date" id="d_prog" class="form-control" />
                                </div>
                                <div class="form-group col">
                                    <label for="hasta_prog">Hasta:</label>
                                    <input type="date" id="h_prog" class="form-control" />
                                </div>
                            </div>

                            <!-- Horario Simple -->
                            <div id="cont_horario_simple">
                                <div class="mb-3">
                                    <label><input type="checkbox" onchange="changeLunes();" id="all"> Lun a Vie</label>
                                    <label class="ms-4"><input type="checkbox" checked id="no_feriados"> No incluir Feriados</label>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <h6>Turno 1</h6>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr><th></th><th>Entrada</th><th>Salida</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>LUN</td><td><input class="form-control" type="time" id="dia_1_e" onchange="changeLunes()"></td><td><input class="form-control" type="time" id="dia_1_s" onchange="changeLunes()"></td></tr>
                                                <tr><td>MAR</td><td><input class="form-control" type="time" id="dia_2_e"></td><td><input class="form-control" type="time" id="dia_2_s"></td></tr>
                                                <tr><td>MIE</td><td><input class="form-control" type="time" id="dia_3_e"></td><td><input class="form-control" type="time" id="dia_3_s"></td></tr>
                                                <tr><td>JUE</td><td><input class="form-control" type="time" id="dia_4_e"></td><td><input class="form-control" type="time" id="dia_4_s"></td></tr>
                                                <tr><td>VIE</td><td><input class="form-control" type="time" id="dia_5_e"></td><td><input class="form-control" type="time" id="dia_5_s"></td></tr>
                                                <tr><td>SAB</td><td><input class="form-control" type="time" id="dia_6_e"></td><td><input class="form-control" type="time" id="dia_6_s"></td></tr>
                                                <tr><td>DOM</td><td><input class="form-control" type="time" id="dia_0_e"></td><td><input class="form-control" type="time" id="dia_0_s"></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col">
                                        <h6>Turno 2</h6>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr><th></th><th>Entrada</th><th>Salida</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>LUN</td><td><input class="form-control" type="time" id="dia_1_1_e"></td><td><input class="form-control" type="time" id="dia_1_1_s"></td></tr>
                                                <tr><td>MAR</td><td><input class="form-control" type="time" id="dia_2_1_e"></td><td><input class="form-control" type="time" id="dia_2_1_s"></td></tr>
                                                <tr><td>MIE</td><td><input class="form-control" type="time" id="dia_3_1_e"></td><td><input class="form-control" type="time" id="dia_3_1_s"></td></tr>
                                                <tr><td>JUE</td><td><input class="form-control" type="time" id="dia_4_1_e"></td><td><input class="form-control" type="time" id="dia_4_1_s"></td></tr>
                                                <tr><td>VIE</td><td><input class="form-control" type="time" id="dia_5_1_e"></td><td><input class="form-control" type="time" id="dia_5_1_s"></td></tr>
                                                <tr><td>SAB</td><td><input class="form-control" type="time" id="dia_6_1_e"></td><td><input class="form-control" type="time" id="dia_6_1_s"></td></tr>
                                                <tr><td>DOM</td><td><input class="form-control" type="time" id="dia_0_1_e"></td><td><input class="form-control" type="time" id="dia_0_1_s"></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Guardias Contrato -->
                        <div class="tab-pane fade" id="guardias_cont" role="tabpanel">
                            <div class="row pt-3">
                                <div class="col">
                                    <h5>Agregar día
                                        <button type="button" onclick="agregarGuardia(0)" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </h5>
                                    <div id="cont_guardia_contrato"></div>
                                    <button type="button" onclick="guardarGuardias(0)" class="btn btn-primary btn-sm mt-3">
                                        Guardar <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Guardias Pagas -->
                        <div class="tab-pane fade" id="guardias_pagas" role="tabpanel">
                            <div class="row pt-3">
                                <div class="col">
                                    <h5>Agregar día
                                        <button type="button" onclick="agregarGuardia(1)" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </h5>
                                    <div id="cont_guardia_pagas"></div>
                                    <button type="button" onclick="guardarGuardias(1)" class="btn btn-primary btn-sm mt-3">
                                        Guardar <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Variables para JavaScript -->
    <input type="hidden" id="permisos" value="{{ ($permisos['crear'] ?? false) ? '1' : '0' }}|{{ ($permisos['editar'] ?? false) ? '1' : '0' }}|{{ ($permisos['eliminar'] ?? false) ? '1' : '0' }}" />
    <input type="hidden" id="todo_personal_pla" value="{{ isset($permisos['todo_personal_pla']) && $permisos['todo_personal_pla'] ? '1' : '0' }}" />
    <input type="hidden" id="_usid" value="{{ session('usuario_id') }}" />
    <input type="hidden" id="_fecha_server" value="{{ date('Y-m-d') }}" />
    <input type="hidden" id="_permiso_fechas" value="1" />

@endsection

@push('scripts')
<script src="{{ asset('js/programacion_horarios.js') }}?t={{ time() }}"></script>
@endpush
