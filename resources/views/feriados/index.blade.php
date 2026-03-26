@extends('layouts.app')

@section('title', 'Gestión de Feriados')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Feriados</h1>
            </div>
        </div>
    </div>
</section>

<!-- Modal Eliminar -->
<div class="modal fade" id="modal_eliminar">
    <div class="modal-dialog">
        <div class="modal-content bg-danger">
            <div class="modal-header">
                <h4 class="modal-title">¡Atención!</h4>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este feriado?</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" id="btn_eliminar_modal" class="btn btn-outline-light">Eliminar</button>
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Vista Calendario -->
<div class="modal fade" id="modal_calendario" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Vista de Calendario - Feriados
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Controles de Navegación -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            Seleccionar Período
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="calendario_mes">Mes:</label>
                                    <select id="calendario_mes" class="form-control">
                                        <option value="1">Enero</option>
                                        <option value="2">Febrero</option>
                                        <option value="3">Marzo</option>
                                        <option value="4">Abril</option>
                                        <option value="5">Mayo</option>
                                        <option value="6">Junio</option>
                                        <option value="7">Julio</option>
                                        <option value="8">Agosto</option>
                                        <option value="9">Septiembre</option>
                                        <option value="10">Octubre</option>
                                        <option value="11">Noviembre</option>
                                        <option value="12">Diciembre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="calendario_anio">Año:</label>
                                    <select id="calendario_anio" class="form-control">
                                        <!-- Se llena dinámicamente -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="cargarCalendario()">
                                    Actualizar Vista
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm ml-2" onclick="irAHoy()">
                                    Ir a Hoy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendario -->
                <div id="calendario_container" class="mb-3">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Preparando calendario...</p>
                    </div>
                </div>

                <!-- Lista de Feriados -->
                <div id="feriados_lista">
                    <div id="feriados_items">
                        <div class="text-center p-3">
                            <div class="spinner-border spinner-border-sm text-info" role="status">
                                <span class="sr-only">Cargando feriados...</span>
                            </div>
                            <p class="mt-2 text-muted small">Cargando información de feriados...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="row w-100">
                    <div class="col-md-8 text-left">
                        <small class="text-muted">
                            Vista de calendario mensual con feriados
                        </small>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Botones de Acción -->
            <div class="col-12 mb-3">
                <button type="button" class="btn btn-info" id="btn_gestionar_fijos">
                    <i class="fas fa-calendar-plus"></i> Gestionar Feriados Fijos
                </button>
                <button type="button" class="btn btn-success" id="btn_generar_anio">
                    <i class="fas fa-magic"></i> Generar Feriados de un Año
                </button>
                <button type="button" class="btn btn-primary" id="btn_vista_calendario">
                    <i class="fas fa-calendar-alt"></i> Vista de Calendario
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Formulario -->
            <div class="col-md-6">
                <div class="card card-primary" id="card_form">
                    <div class="card-header">
                        <h3 class="card-title">Formulario</h3>
                    </div>

                    <form id="form_main">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-8">
                                    <label for="feriado">Descripción del Feriado:</label>
                                    <input type="text" class="form-control" required name="feriado" id="feriado" placeholder="Ej: Día de la Independencia">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="fecha">Fecha:</label>
                                    <input type="date" id="f_fer" required class="form-control" name="fecha"/>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            @if($permisos['crear'] || $permisos['editar'])
                            <button type="submit" id="btn_submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            @endif

                            @if($permisos['crear'])
                            <button type="button" id="btn_limpiar" class="btn btn-warning">
                                <i class="fas fa-broom"></i> Limpiar
                            </button>
                            @endif

                            @if($permisos['eliminar'])
                            <button type="button" id="btn_eliminar" class="btn btn-danger">
                                <i class="fa fa-trash"></i> Eliminar
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de feriados -->
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Feriados</h3>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                        <th>Tipo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table_data">
                                    <!-- Los datos se cargan vía AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Información de paginación -->
                        <div id="total_info" class="info-pagination">
                            <!-- Total de registros -->
                        </div>

                        <!-- Controles de paginación -->
                        <div class="row">
                            <div class="col-md-3" id="page-selection_num_page" style="padding-top: 20px">
                                <select id="page-selection_input_num_page" class="form-control form-control-sm">
                                    <option value="5">5 por página</option>
                                    <option value="10" selected>10 por página</option>
                                    <option value="25">25 por página</option>
                                    <option value="50">50 por página</option>
                                </select>
                            </div>
                            <div class="col-md-9">
                                <div id="page-selection">
                                    <!-- Paginación se carga aquí -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Gestionar Feriados Fijos -->
<div class="modal fade" id="modal_feriados_fijos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">
                    <i class="fas fa-calendar-plus"></i> Gestionar Feriados Fijos
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar nuevo feriado fijo -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card card-success">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Agregar Nuevo Feriado Fijo</h5>
                            </div>
                            <div class="card-body">
                                <form id="form_feriado_fijo">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nombre del Feriado</label>
                                                <input type="text" class="form-control" id="nombre_fijo" maxlength="100" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Día</label>
                                                <select class="form-control" id="dia_fijo" required>
                                                    <option value="">Seleccione...</option>
                                                    @for($i = 1; $i <= 31; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Mes</label>
                                                <select class="form-control" id="mes_fijo" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="1">Enero</option>
                                                    <option value="2">Febrero</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Abril</option>
                                                    <option value="5">Mayo</option>
                                                    <option value="6">Junio</option>
                                                    <option value="7">Julio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Septiembre</option>
                                                    <option value="10">Octubre</option>
                                                    <option value="11">Noviembre</option>
                                                    <option value="12">Diciembre</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Descripción (opcional)</label>
                                                <textarea class="form-control" id="descripcion_fijo" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-plus"></i> Agregar Feriado Fijo
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de feriados fijos existentes -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Feriados Fijos Configurados</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="tabla_feriados_fijos">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Nombre</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_feriados_fijos">
                                            <!-- Se carga vía AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Generar Feriados por Año -->
<div class="modal fade" id="modal_generar_anio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">
                    <i class="fas fa-magic"></i> Generar Feriados de un Año
                </h4>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Este proceso generará automáticamente todos los feriados fijos configurados para el año seleccionado.</p>
                <div class="form-group">
                    <label>Seleccione el Año</label>
                    <select class="form-control" id="anio_generar">
                        @for($i = 2020; $i <= 2035; $i++)
                            <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn_ejecutar_generacion">
                    <i class="fas fa-magic"></i> Generar Feriados
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    /* Estilos específicos para los badges de tipo de feriado */
    .badge-success {
        background-color: #28a745 !important;
        color: #fff !important;
    }

    .badge-primary {
        background-color: #007bff !important;
        color: #fff !important;
    }

    .badge {
        font-size: 0.875em !important;
        padding: 0.25rem 0.5rem !important;
    }

    /* Asegurar que el texto de la tabla sea visible */
    .table td {
        color: #495057 !important;
    }

    .table th {
        color: #495057 !important;
    }
</style>
@endsection

@section('js')
<script>
    // Variables globales para las rutas
    window.feriadosRoutes = {
        listar: '{{ route('feriados.listar') }}',
        store: '{{ route('feriados.store') }}',
        update: '{{ route('feriados.update', ':id') }}',
        destroy: '{{ route('feriados.destroy', ':id') }}',
        show: '{{ route('feriados.show', ':id') }}',
        generarFijos: '{{ route('feriados.generar-fijos') }}',
        feriadosFijos: '{{ route('feriados.fijos') }}',
        storeFijo: '{{ route('feriados.fijos.store') }}',
        toggleFijo: '{{ route('feriados.fijos.toggle', ':id') }}',
        destroyFijo: '{{ route('feriados.fijos.destroy', ':id') }}',
        mes: '{{ route('feriados.mes') }}'
    };

    // Permisos del usuario
    window.feriadosPermisos = {
        crear: {{ $permisos['crear'] ? 'true' : 'false' }},
        editar: {{ $permisos['editar'] ? 'true' : 'false' }},
        eliminar: {{ $permisos['eliminar'] ? 'true' : 'false' }},
        leer: {{ $permisos['leer'] ? 'true' : 'false' }}
    };

    // Debug: mostrar rutas configuradas
    console.log('=== RUTAS CONFIGURADAS ===');
    console.log('feriadosRoutes:', window.feriadosRoutes);
    console.log('feriadosPermisos:', window.feriadosPermisos);
</script>


<!-- Nota: Moment.js, Tempus Dominus, SweetAlert2 y jQuery Validation ya están incluidos en el layout principal -->
<script src="{{ asset('js/feriados.js') }}?t={{ time() }}"></script>
@endsection
