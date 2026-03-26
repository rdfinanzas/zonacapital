@extends('layouts.main')

@section('title', 'Gestión de Personal')

@section('header-title', 'Gestión de Personal')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Personal</li>
@endsection

@section('content')
    <!-- Hidden permissions inputs for JavaScript -->
    <input type="hidden" id="permiso_crear" value="{{ $permisos['crear'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_leer" value="{{ $permisos['leer'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_editar" value="{{ $permisos['editar'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_eliminar" value="{{ $permisos['eliminar'] ? 1 : 0 }}">
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Legacy compatibility inputs from original ABM -->
    <input type="hidden" id="_token" data-url="{{ 'personal__' . time() }}" autocomplete="off"
        value="{{ csrf_token() }}">
    <input type="hidden" id="_idpi" autocomplete="off" value="{{ request()->query('pi', '') }}">
    <input type="hidden" id="permisos" autocomplete="off"
        value="{{ ($permisos['crear'] ? 1 : 0) . '|' . ($permisos['editar'] ? 1 : 0) . '|' . ($permisos['eliminar'] ? 1 : 0) }}">
    <script>
        window.localStorage.setItem('Authorization', '{{ session('auth') ?? '' }}');
    </script>
    <script>
        var refererModule = '{{ 'personal__' . time() }}';
        try {
            document.cookie = 'referer=' + '{{ 'personal__' . time() }}';
        } catch (e) {
            /* noop */
        }
    </script>

    <!-- Modal de eliminación -->
    <div class="modal fade" id="modal_eliminar" style="z-index: 1060;">
        <div class="modal-dialog">
            <div class="modal-content bg-danger">
                <div class="modal-header">
                    <h4 class="modal-title">Atención!</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar este registro?</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="btn_eliminar_modal" class="btn btn-outline-light">Eliminar</button>
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para webcam -->
    <div class="modal fade" id="modal_foto">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Cámara</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="my_camera" style="width:320px; height:240px;"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="btn_capturar" class="btn btn-primary">Tomar foto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de historial de jornadas -->
    <div class="modal fade" id="histo_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Historial de jornadas</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Jornada</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_his">
                            <!-- Contenido dinámico de historial -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- List Card with Filters -->
    <div id="seccion-listado" class="@if ($permisos['leer'] ?? false) d-block @else d-none @endif">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Listado de Personal</h3>
                <div>
                    @if ($permisos['crear'] ?? false)
                        <button type="button" id="btn-agregar" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Agregar Personal
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form id="formFiltros">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filtro_apellido_nombre" class="form-label">Apellido y Nombre</label>
                                <input type="text" class="form-select" id="filtro_apellido_nombre"
                                    name="apellido_nombre" placeholder="Buscar por apellido o nombre">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filtro_legajo" class="form-label">Legajo</label>
                                <input type="text" class="form-select" id="filtro_legajo" name="legajo"
                                    placeholder="Legajo">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filtro_dni" class="form-label">DNI</label>
                                <input type="text" class="form-select" id="filtro_dni" name="dni"
                                    placeholder="DNI">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filtro_sexo" class="form-label">Sexo</label>
                                <select class="form-select" id="filtro_sexo" name="sexo">
                                    <option value="0">Todos</option>
                                    <option value="1">Masculino</option>
                                    <option value="2">Femenino</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filtro_edad" class="form-label">Edad</label>
                                <input type="number" class="form-select" id="filtro_edad" name="edad"
                                    placeholder="Edad" min="0" max="100">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filtro_profesion" class="form-label">Profesión</label>
                                <select class="form-select" id="filtro_profesion" name="profesion">
                                    <option value="0">Todas</option>
                                    @foreach ($profesiones as $profesion)
                                        <option value="{{ $profesion->idprofesion }}">
                                            {{ $profesion->profesion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filtro_funcion" class="form-label">Función</label>
                                <select class="form-select" id="filtro_funcion" name="funcion">
                                    <option value="0">Todas</option>
                                    @foreach ($funciones as $funcion)
                                        <option value="{{ $funcion->IdFuncion }}">
                                            {{ $funcion->Funcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filtro_gerencia" class="form-label">Gerencia</label>
                                <select class="form-select" id="filtro_gerencia" name="gerencia">
                                    <option value="0">Todas</option>
                                    @foreach ($gerencias as $gerencia)
                                        <option value="{{ $gerencia->idGerencia }}">
                                            {{ $gerencia->Gerencia }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filtro_estado" class="form-label">Estado</label>
                                <select class="form-select" id="filtro_estado" name="estado">
                                    <option value="0">Todos</option>
                                    @foreach ($estados as $estado)
                                        <option value="{{ $estado->idEstado }}">{{ $estado->estado }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary mb-2 flex-fill">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Legajo</th>
                                <th>Apellido y Nombre</th>
                                <th>DNI</th>
                                <th>Sexo</th>
                                <th>Edad</th>
                                <th>Gerencia</th>
                                <th>Estado</th>
                                <th>Fecha Alta</th>
                                <th style="width: 100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-personal">
                            <!-- Contenido dinámico -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="paginacion-contenedor" class="mt-3"></div>

                <!-- Legacy compatibility placeholders (hidden) -->
                <form id="filter_form" action="javascript:refrescarTabla()" style="display:none;"></form>
                <div class="progress" id="load" style="display:none;">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="100"
                        aria-valuemax="100" style="width: 100%;color:#000;">Cargando...</div>
                </div>
                <div class="card-body table-responsive p-0" style="display:none;">
                    <table class="table table-striped">
                        <tbody id="table_data"></tbody>
                    </table>
                </div>
                <div id="total_info" style="display:none;"></div>
                <div class="row" style="display:none;">
                    <div class="col-md-2" id="page-selection_num_page" style="padding-top: 20px"></div>
                    <div class="col">
                        <div id="page-selection"></div>
                    </div>
                </div>

                <!-- Legacy action placeholders (hidden) to keep backward-compatible IDs used by old JS -->
                <button type="button" id="btn_agregar" style="display:none;">Agregar</button>
                <form id="form_main" style="display:none;"><button type="submit" id="btn_submit"></button></form>
                <button type="button" id="btn_limpiar" style="display:none;"></button>
                <button type="button" id="btn_imprimir" style="display:none;"></button>
            </div>
        </div>
    </div>

    <!-- Formulario de Personal - Inicialmente oculto -->
    <div id="seccion-formulario" class="d-none">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" id="titulo-formulario">Formulario de Personal</h3>
            </div>
            <div class="card-body">
                <form role="form" id="form-personal">
                    <input type="hidden" id="empleado_id" name="empleado_id">

                    <!-- Datos básicos -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Datos Básicos</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="legajo">Legajo *</label>
                                        <input type="text" class="form-control" id="legajo" name="legajo"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="apellido">Apellido *</label>
                                        <input type="text" class="form-control" id="apellido" name="apellido"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nombre">Nombre *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="dni">DNI *</label>
                                        <input type="number" class="form-control" id="dni" name="dni"
                                            onchange="getCuit()" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cuit">CUIT</label>
                                        <input type="text" class="form-control" id="cuit" name="cuit"
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="sexo">Sexo</label>
                                        <select class="form-control" id="sexo" name="sexo" onchange="getCuit()">
                                            <option value="1">Masculino</option>
                                            <option value="2">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                                        <input type="text" class="form-control datepicker" id="fecha_nacimiento"
                                            name="fecha_nacimiento" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="instruccion">Instrucción</label>
                                        <select class="form-control select2" id="instruccion" name="instruccion">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de contacto -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Datos de Contacto</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="celular">Celular</label>
                                        <input type="text" class="form-control" id="celular" name="celular">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="estado_civil">Estado Civil</label>
                                        <select class="form-control select2" id="estado_civil" name="estado_civil">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="calle">Calle</label>
                                        <input type="text" class="form-control" id="calle" name="calle">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="num_calle">Número</label>
                                        <input type="text" class="form-control" id="num_calle" name="num_calle">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="piso">Piso</label>
                                        <input type="text" class="form-control" id="piso" name="piso">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="dto">Departamento</label>
                                        <input type="text" class="form-control" id="dto" name="dto">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="barrio">Barrio</label>
                                        <input type="text" class="form-control" id="barrio" name="barrio">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="manzana">Manzana</label>
                                        <input type="text" class="form-control" id="manzana" name="manzana">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="casa">Casa</label>
                                        <input type="text" class="form-control" id="casa" name="casa">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="provincia">Provincia</label>
                                        <select class="form-control select2" id="provincia" name="provincia"
                                            onchange="getLocalidades()">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="localidad">Localidad</label>
                                        <select class="form-control select2" id="localidad" name="localidad"
                                            onchange="getCP()">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cp">Código Postal</label>
                                        <input type="text" class="form-control" id="cp" name="cp">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="nacionalidad">País Natal</label>
                                        <select class="form-control select2" id="nacionalidad" name="nacionalidad">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos laborales -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Datos Laborales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="profesion">Profesión</label>
                                        <select class="form-control select2" id="profesion" name="profesion">
                                            <option value="">- Seleccionar -</option>
                                            @foreach ($profesiones as $profesion)
                                                <option value="{{ $profesion->idprofesion }}">{{ $profesion->profesion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="funcion">Función</label>
                                        <select class="form-control select2" id="funcion" name="funcion">
                                            <option value="">- Seleccionar -</option>
                                            @foreach ($funciones as $funcion)
                                                <option value="{{ $funcion->IdFuncion }}">{{ $funcion->Funcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="agrupamiento">Grado/Agrupamiento</label>
                                        <select class="form-control select2" id="agrupamiento" name="agrupamiento">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="num_matricula">Nº Matrícula</label>
                                        <input type="text" class="form-control" id="num_matricula"
                                            name="num_matricula">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_alta">Fecha de Alta *</label>
                                        <input type="text" class="form-control datepicker" id="fecha_alta"
                                            name="fecha_alta" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_adm_publica">Fecha Administración Pública</label>
                                        <input type="text" class="form-control datepicker" id="fecha_adm_publica"
                                            name="fecha_adm_publica">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="categoria">Categoría</label>
                                        <select class="form-control select2" id="categoria" name="categoria">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="estado">Estado</label>
                                        <select class="form-control" id="estado" name="estado"
                                            onchange="changeEstado()">
                                            <option value="1">Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gerencia">Gerencia</label>
                                        <select class="form-control select2" id="gerencia" name="gerencia"
                                            onchange="CargaSelectDto()">
                                            <option value="">- Seleccionar -</option>
                                            @foreach ($gerencias as $gerencia)
                                                <option value="{{ $gerencia->idGerencia }}">{{ $gerencia->Gerencia }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="departamento">Departamento</label>
                                        <select class="form-control select2" id="departamento" name="departamento"
                                            onchange="CargaSelectServ()">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="servicio">Servicio</label>
                                        <select class="form-control select2" id="servicio" name="servicio"
                                            onchange="CargaSelectSect()">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="sector">Sector</label>
                                        <select class="form-control select2" id="sector" name="sector"
                                            onchange="changSect()">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cargo">Cargo</label>
                                        <select class="form-control select2" id="cargo" name="cargo">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="nro_contrato">Nro. Contrato</label>
                                        <input type="text" class="form-control" id="nro_contrato"
                                            name="nro_contrato">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="relacion">Relación</label>
                                        <select class="form-control select2" id="relacion_laboral"
                                            name="relacion_laboral">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo_tarea">Tipo Tarea</label>
                                        <select class="form-control select2" id="tipo_tarea" name="tipo_tarea">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="certifica">Certifica</label>
                                        <select class="form-control select2" id="certifica" name="certifica">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo_jornada">Jornada laboral</label>
                                        <select class="form-control select2" id="tipo_jornada" name="tipo_jornada"
                                            onchange="changJornada()">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="f_jornada">Jornada a partir de</label>
                                        <input type="text" class="form-control datepicker" id="f_jornada"
                                            name="f_jornada" disabled>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" class="form-check-input" id="f_doble"
                                                name="f_doble" value="1">
                                            <label class="form-check-label" for="f_doble">F doble</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="fe"
                                                name="fe" value="1">
                                            <label class="form-check-label" for="fe">NO FE</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo_contrato">Tipo de Contrato</label>
                                        <select class="form-control select2" id="tipo_contrato" name="tipo_contrato">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="horas_semanales">Horas Semanales</label>
                                        <input type="number" class="form-control" id="horas_semanales"
                                            name="horas_semanales">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="sueldo">Sueldo</label>
                                        <input type="number" class="form-control" id="sueldo" name="sueldo">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fecha_baja">Fecha de Baja</label>
                                        <input type="text" class="form-control datepicker" id="fecha_baja"
                                            name="fecha_baja">
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="cont_baja" style="display:none">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="motivo_baja">Motivo de Baja</label>
                                        <select class="form-control" id="motivo_baja" name="motivo_baja">
                                            <option value="">- Seleccionar -</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="descript_baja" style="display:none">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="descripcion_baja">Descripción de Baja</label>
                                        <textarea class="form-control" id="descripcion_baja" name="descripcion_baja" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Foto del empleado -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Foto del Empleado</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div id="crop_content" style="display: none;">
                                        <img id="img_foto" class="profile-photo">
                                        <div class="mt-2">
                                            <button type="button" id="btn_recortar" class="btn btn-success"
                                                style="display: none;">Recortar</button>
                                            <button type="button" id="btn_eliminar_foto" class="btn btn-danger">Eliminar
                                                Foto</button>
                                        </div>
                                    </div>
                                    <div id="img_crop" class="text-center">
                                        <img id="foto_preview" class="profile-photo" style="display: none;">
                                        <div class="crop-container" id="drop_zone">
                                            <p class="text-center mt-5">Arrastra una imagen aquí o haz clic para
                                                seleccionar</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="file" id="file_foto" accept="image/*" style="display: none;">
                                    <input type="hidden" id="foto_base64" name="foto">

                                    <div class="form-group">
                                        <button type="button" id="btn_seleccionar_foto" class="btn btn-primary">
                                            <i class="fas fa-upload"></i> Seleccionar Foto
                                        </button>
                                        <button type="button" id="btn_camara" class="btn btn-info ml-2">
                                            <i class="fas fa-camera"></i> Tomar Foto
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        Formatos aceptados: JPG, PNG. Tamaño máximo: 2MB.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de relaciones -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Historial de relaciones</h3>
                            <button class="btn btn-primary btn-sm float-right" onclick="addHistorialRel()"
                                type="button">+</button>
                        </div>
                        <div class="card-body">
                            <!-- Select oculto con tipos de relación para clonado -->
                            <select id="relacion" style="display:none;">
                                <option value="">- Seleccionar -</option>
                            </select>
                            <div id="container_relaciones">
                                <!-- El contenido se generará dinámicamente con JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Documentos</h3>
                            <button class="btn btn-primary btn-sm float-right" onclick="addDoc()"
                                type="button">+</button>
                        </div>
                        <div class="card-body">
                            <div id="container_doc">
                                <!-- El contenido se generará dinámicamente con JavaScript -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                @if ($permisos['crear'] || $permisos['editar'])
                    <button type="submit" id="btn-guardar" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar
                    </button>
                @endif

                @if ($permisos['crear'])
                    <button type="button" id="btn-limpiar-form" class="btn btn-warning">
                        <i class="bi bi-eraser me-1"></i> Limpiar
                    </button>
                @endif

                @if ($permisos['eliminar'])
                    <button type="button" id="btn-eliminar" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                @endif

                <button type="button" id="btn-volver" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </button>
            </div>
        </div>
    </div>

    @if (!($permisos['leer'] ?? false))
        <div class="alert alert-warning">
            No tiene permisos para ver el listado de personal.
        </div>
    @endif
@endsection

@push('scripts')
    <!-- Webcam library for photo capture -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcam/1.0.25/webcam.min.js"></script>
    <!-- Croppie for image cropping -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css">
    <!-- Bootstrap DateTimePicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/es.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js">
    </script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
    <!-- jQuery Validate -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/localization/messages_es.min.js"></script>
    <script src="{{ asset('js/personal.js') }}?v={{ time() }}"></script>
@endpush

@push('styles')
    <style>
        /* Estilos adicionales para la vista de personal */
        .profile-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }

        .crop-container {
            width: 200px;
            height: 200px;
            border: 2px dashed #ccc;
            margin: 10px auto;
        }

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
