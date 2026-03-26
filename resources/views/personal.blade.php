@extends('layouts.main')

@section('title', 'Gestión de Personal | ZonaCapital')

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

    <!-- List Section - Initially visible -->
    <div id="seccion-listado" class="@if ($permisos['leer'] ?? false) d-block @else d-none @endif">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Listado de Personal</h3>
                <div>
                    @if ($permisos['crear'] ?? false)
                        <button type="button" id="btnAgregar" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Agregar Personal
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form id="formFiltros" action="{{ route('personal.filtrar') }}" method="GET">
                    <!-- Filtros Principales (Siempre visibles) -->
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filtro_apellido_nombre" class="form-label">Apellido y Nombre</label>
                                <input type="text" class="form-control" id="filtro_apellido_nombre" name="apellido_nombre"
                                    placeholder="Buscar por apellido o nombre">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filtro_legajo" class="form-label">Legajo</label>
                                <input type="text" class="form-control" id="filtro_legajo" name="legajo"
                                    placeholder="Legajo">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="filtro_dni" class="form-label">DNI</label>
                                <input type="text" class="form-control" id="filtro_dni" name="dni" placeholder="DNI">
                            </div>
                        </div>
                        <div class="col-md-5 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                            <button type="button" id="btnToggleFiltros" class="btn btn-outline-secondary">
                                <i class="bi bi-chevron-down me-1"></i> Más filtros
                            </button>
                            <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary" title="Limpiar filtros">
                                <i class="bi bi-eraser"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Filtros Secundarios (Ocultos por defecto) -->
                    <div id="contenedor-filtros" class="d-none border-top pt-3 mt-3">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_sexo" class="form-label">Sexo</label>
                                    <select class="form-select" id="filtro_sexo" name="sexo">
                                        <option value="0" selected>Todos</option>
                                        <option value="1">Masculino</option>
                                        <option value="2">Femenino</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_profesion" class="form-label">Profesión</label>
                                    <select class="form-select" id="filtro_profesion" name="profesion">
                                        <option value="0" selected>Todas</option>
                                        @foreach ($profesiones as $profesion)
                                            <option value="{{ $profesion->idprofesion }}">
                                                {{ $profesion->profesion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_estado" class="form-label">Estado</label>
                                    <select class="form-select" id="filtro_estado" name="estado">
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->idEstado }}" {{ $estado->idEstado == 1 ? 'selected' : '' }}>
                                                {{ $estado->estado }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_cargo" class="form-label">Cargo</label>
                                    <select class="form-select" id="filtro_cargo" name="cargo">
                                        <option value="0" selected>Todos</option>
                                        @foreach ($cargos as $cargo)
                                            <option value="{{ $cargo->idCargo }}">
                                                {{ $cargo->cargo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_clasificacion" class="form-label">Clasificación</label>
                                    <select class="form-select" id="filtro_clasificacion" name="clasificacion">
                                        <option value="0" selected>Todas</option>
                                        @foreach ($clasificaciones as $clasif)
                                            <option value="{{ $clasif->idClasificacion }}">
                                                {{ $clasif->clasificacion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_gerencia" class="form-label">Gerencia</label>
                                    <select class="form-select" id="filtro_gerencia" name="gerencia"
                                        onchange="CargaSelectDtoFiltro()">
                                        <option value="0" selected>Todas</option>
                                        @foreach ($gerencias as $gerencia)
                                            <option value="{{ $gerencia->idGerencia }}">
                                                {{ $gerencia->Gerencia }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_departamento" class="form-label">Departamento</label>
                                    <select class="form-select" id="filtro_departamento" name="departamento"
                                        onchange="CargaSelectServFiltro()">
                                        <option value="0" selected>Todos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_servicio" class="form-label">Servicio</label>
                                    <select class="form-select" id="filtro_servicio" name="servicio"
                                        onchange="CargaSelectSectFiltro()">
                                        <option value="0" selected>Todos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filtro_sector" class="form-label">Sector</label>
                                    <select class="form-select" id="filtro_sector" name="sector">
                                        <option value="0" selected>Todos</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="filtro_jefe" class="form-label">Jefe</label>
                                    <select class="form-select select2" id="filtro_jefe" name="jefe">
                                        <option value="0" selected>Todos</option>
                                        @foreach ($jefes as $jefe)
                                            <option value="{{ $jefe->idEmpleado }}">
                                                {{ $jefe->Apellido }}, {{ $jefe->Nombre }} ({{ $jefe->Legajo }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Loading indicator -->
                <div class="progress" id="load" style="display:none;">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="100"
                        aria-valuemax="100" style="width: 100%;color:#000;">
                        Cargando...
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px">Legajo</th>
                                <th>Apellido y Nombre</th>
                                <th style="width: 80px">DNI</th>

                                <th>Servicio</th>
                                <th style="width: 100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-personal">

                        </tbody>
                    </table>
                </div>

                <!-- Total info and pagination -->
                <div id="total_info" class="info-pagination mt-3"></div>
                <div id="paginacion-contenedor" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- Form Section - Initially hidden -->
    <div id="seccion-formulario" class="d-none">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" id="titulo-formulario">Formulario de Personal</h3>
                <div class="card-tools">
                    <button type="button" id="btnVolver" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form role="form" id="formPersonal">
                    <input type="hidden" id="idEmpleado" name="idEmpleado" value="">

                    <!-- Datos Personales -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Datos Personales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Columna Izquierda: Inputs -->
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="form-group col-md-3"> <!-- Antes col-md-2 -->
                                            <label for="legajo">Legajo:</label>
                                            <div class="input-group">
                                                <input type="text" id="legajo" name="legajo" class="form-control" required>
                                                <button type="button" id="btnActLegajo" class="btn btn-outline-secondary"
                                                    title="Editar legajo" onclick="actLegajo()">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4"> <!-- Antes col-md-3 -->
                                            <label for="apellido">Apellido:</label>
                                            <input type="text" id="apellido" name="apellido" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-5"> <!-- Antes col-md-3 -->
                                            <label for="nombre">Nombre:</label>
                                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-3"> <!-- Antes col-md-2 -->
                                            <label for="dni">DNI:</label>
                                            <input type="text" id="dni" name="dni" class="form-control" required
                                                onchange="getCuit()">
                                        </div>
                                        <div class="form-group col-md-3"> <!-- Antes col-md-2 -->
                                            <label for="sexo">Sexo:</label>
                                            <select class="form-select" id="sexo" name="sexo" required onchange="getCuit()">
                                                <option value="">- Seleccionar -</option>
                                                <option value="1">Masculino</option>
                                                <option value="2">Femenino</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3"> <!-- Antes col-md-2 -->
                                            <label for="cuit">CUIT:</label>
                                            <input type="text" id="cuit" name="cuit" class="form-control">
                                        </div>
                                        <div class="form-group col-md-3"> <!-- Antes col-md-3 -->
                                            <label for="fecha_nacimiento">Fecha Nac.:</label>
                                            <div class="input-group date" id="fecha_nacimiento_picker"
                                                data-td-target-input="nearest">
                                                <input type="text" name="fecha_nacimiento" required id="fecha_nacimiento"
                                                    class="form-control datetimepicker-input"
                                                    data-td-target="#fecha_nacimiento_picker" />
                                                <div class="input-group-text" data-td-target="#fecha_nacimiento_picker"
                                                    data-td-toggle="datetimepicker">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="nacionalidad">Nacionalidad:</label>
                                            <select class="form-select select2" id="nacionalidad" name="nacionalidad">
                                                <option value="">- Seleccionar -</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="estado_civil">Estado Civil:</label>
                                            <select class="form-select select2" id="estado_civil" name="estado_civil">
                                                <option value="">- Seleccionar -</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="telefono">Teléfono:</label>
                                            <input type="text" id="telefono" name="telefono" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="celular">Celular:</label>
                                            <input type="text" id="celular" name="celular" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="email">Email:</label>
                                            <input type="email" id="email" name="email" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna Derecha: Foto -->
                                <div class="col-md-3 d-flex flex-column align-items-center justify-content-start pt-3">
                                    <label class="mb-2">Foto del empleado:</label>

                                    <div id="crop_content" style="display: none; margin-bottom: 10px;">
                                        <img id="img_foto" class="img-thumbnail"
                                            style="width: 200px; height: 200px; object-fit: cover;"
                                            src="{{ asset('img/dummy.png') }}">
                                    </div>
                                    <img id="img_crop" class="img-thumbnail"
                                        style="width: 200px; height: 200px; display: block; cursor: pointer; object-fit: cover; margin-bottom: 15px;"
                                        src="{{ asset('img/dummy.png') }}" title="Haz clic para cambiar la imagen">

                                    <div class="d-grid gap-2 col-10 mx-auto">
                                        <input type="file" id="foto_file" accept="image/*" style="display: none;"
                                            data-prev="img_foto"
                                            onchange="cargarImg(this, 200, 200,onBase64ResizeFotoPerfil)">

                                        <button type="button" class="btn btn-primary btn-sm"
                                            onclick="$('#foto_file').click()">
                                            <i class="fas fa-upload me-1"></i> Seleccionar
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm"
                                            onclick="initCamera(); $('#modal_foto').modal('show')">
                                            <i class="fas fa-camera me-1"></i> Cámara
                                        </button>
                                        <button type="button" id="btn_eliminar_foto" class="btn btn-danger btn-sm"
                                            style="display: none;" onclick="eliminarFoto()">
                                            <i class="fas fa-trash me-1"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Domicilio -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Domicilio</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="calle">Calle:</label>
                                    <input type="text" id="calle" name="calle" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="num_calle">Número:</label>
                                    <input type="text" id="num_calle" name="num_calle" class="form-control">
                                </div>
                                <div class="form-group col-md-1">
                                    <label for="piso">Piso:</label>
                                    <input type="text" id="piso" name="piso" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="dto">Departamento:</label>
                                    <input type="text" id="dto" name="dto" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="manzana">Manzana:</label>
                                    <input type="text" id="manzana" name="manzana" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="casa">Casa:</label>
                                    <input type="text" id="casa" name="casa" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label for="cp">Código Postal:</label>
                                    <input type="text" id="cp" name="cp" class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="provincia">Provincia:</label>
                                    <select class="form-select select2" id="provincia" name="provincia"
                                        onchange="getLocalidades()">
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="localidad">Localidad:</label>
                                    <select class="form-select select2" id="localidad" name="localidad" onchange="getCP()">
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="barrio">Barrio:</label>
                                    <input type="text" id="barrio" name="barrio" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Datos Profesionales -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Datos Profesionales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="profesion">Profesión:</label>
                                    <select class="form-select select2" id="profesion" name="profesion">
                                        <option value="">- Seleccionar -</option>
                                        @foreach ($profesiones as $profesion)
                                            <option value="{{ $profesion->idprofesion }}">
                                                {{ $profesion->profesion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="funcion">Función:</label>
                                    <select class="form-select select2" id="funcion" name="funcion">
                                        <option value="">- Seleccionar -</option>
                                        @foreach ($funciones as $funcion)
                                            <option value="{{ $funcion->IdFuncion }}">
                                                {{ $funcion->Funcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="idClasificacion">Clasificación:</label>
                                    <select class="form-select select2" id="idClasificacion" name="idClasificacion">
                                        <option value="">- Seleccionar -</option>
                                        @foreach ($clasificaciones as $clasif)
                                            <option value="{{ $clasif->idClasificacion }}">
                                                {{ $clasif->clasificacion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="tipo_tarea">Tipo de Tarea:</label>
                                    <select class="form-select select2" id="tipo_tarea" name="tipo_tarea" required>
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="num_matricula">Matrícula:</label>
                                    <input type="text" maxlength="50" id="num_matricula" name="num_matricula"
                                        class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos Laborales -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Datos Laborales</h3>
                        </div>
                        <div class="card-body">
                            <!-- Fechas y Contrato -->
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="fecha_alta">Fecha Alta en Zona:</label>
                                    <div class="input-group date" id="fecha_alta_picker" data-td-target-input="nearest">
                                        <input type="text" name="fecha_alta" required id="fecha_alta"
                                            class="form-control datetimepicker-input" data-td-target="#fecha_alta_picker" />
                                        <div class="input-group-text" data-td-target="#fecha_alta_picker"
                                            data-td-toggle="datetimepicker">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="fecha_adm_publica">Fecha Alta Adm. Publ.:</label>
                                    <div class="input-group date" id="fecha_adm_publica_picker"
                                        data-td-target-input="nearest">
                                        <input type="text" name="fecha_adm_publica" required id="fecha_adm_publica"
                                            class="form-control datetimepicker-input"
                                            data-td-target="#fecha_adm_publica_picker" />
                                        <div class="input-group-text" data-td-target="#fecha_adm_publica_picker"
                                            data-td-toggle="datetimepicker">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="nro_contrato">N° Contrato:</label>
                                    <input type="text" maxlength="10" id="nro_contrato" name="nro_contrato"
                                        class="form-control">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="relacion">Relación Laboral:</label>
                                    <select class="form-select select2" id="relacion" name="relacion" required>
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Categorización -->
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="agrupamiento">Grado/Agrupamiento:</label>
                                    <select class="form-select select2" id="agrupamiento" name="agrupamiento" required>
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="categoria">Categoría:</label>
                                    <select class="form-select select2" id="categoria" name="categoria">
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                                <!-- Campo cargo oculto - Solo se gestiona desde el organigrama -->
                                <input type="hidden" id="cargo" name="cargo" value="">
                            </div>

                            <!-- Asignación Organizacional -->
                            <hr class="my-4">
                            <h5 class="mb-3 text-secondary">Ubicación Organizacional</h5>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="gerencia">Gerencia:</label>
                                    <select class="form-select select2" id="gerencia" name="gerencia"
                                        onchange="CargaSelectDto()" data-id_jefe="4">>
                                        <option value="">- Seleccionar -</option>
                                        @foreach ($gerencias as $gerencia)
                                            <option value="{{ $gerencia->idGerencia }}">
                                                {{ $gerencia->Gerencia }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="departamento">Departamento:</label>
                                    <select class="form-select select2" id="departamento" name="departamento"
                                        onchange="CargaSelectServ()" data-id_jefe="3">>
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Asignación de Servicios -->
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3 text-secondary">Asignación de Servicios</h5>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="form-group col-md-4">
                                    <label for="servicio">Servicio:</label>
                                    <select class="form-select select2" id="servicio" name="servicio">
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" onclick="agregarServicio()">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                            </div>

                            <!-- Lista de servicios asignados -->
                            <div class="row">
                                <div class="col-12">
                                    <div id="servicios-asignados-container" class="border rounded p-3 bg-light">
                                        <p class="text-muted text-center" id="sin-servicios-msg">No hay servicios asignados</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Historial de Servicios -->
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3 text-secondary">Historial de Servicios</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm text-center">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Servicio</th>
                                                    <th>Fecha Inicio</th>
                                                    <th>Fecha Fin</th>
                                                    <th>Estado</th>
                                                    <th>Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla_historial_servicios">
                                                <!-- Se llena via JS -->
                                                <tr>
                                                    <td colspan="5">Sin historial</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado y Baja -->
                            <hr class="my-4">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="estado">Estado del Personal:</label>
                                    <select class="form-select" id="estado" name="estado" onchange="changeEstado()">
                                        <option value="">- Seleccionar -</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->idEstado }}">
                                                {{ $estado->estado }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3" id="fecha_baja_group" style="display: none;">
                                    <label for="fecha_baja">Fecha de Baja:</label>
                                    <div class="input-group date" id="fecha_baja_picker" data-td-target-input="nearest">
                                        <input type="text" name="fecha_baja" id="fecha_baja"
                                            class="form-control datetimepicker-input" data-td-target="#fecha_baja_picker" />
                                        <div class="input-group-text" data-td-target="#fecha_baja_picker"
                                            data-td-toggle="datetimepicker">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-3" id="motivo_baja_group" style="display: none;">
                                    <label for="motivo_baja">Motivo de Baja:</label>
                                    <select class="form-select select2" id="motivo_baja" name="motivo_baja">
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>
                                <div class="form-group col-12" id="des_baja_group" style="display: none;">
                                    <label for="des_baja">Descripción de Baja:</label>
                                    <textarea id="des_baja" name="des_baja" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="f_doble" name="f_doble"
                                            checked>
                                        <label class="custom-control-label" for="f_doble">F doble</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="fe" name="fe">
                                        <label class="custom-control-label" for="fe">NO FE</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="tipo_jornada">Tipo de Jornada:</label>
                                    <select class="form-select select2" required id="tipo_jornada" name="tipo_jornada"
                                        onchange="changJornada()">
                                        <option value="">- Seleccionar -</option>
                                    </select>
                                </div>


                                <div class="form-group col-md-3">
                                    <label for="f_jornada">Jornada a partir de:</label>
                                    <div class="input-group date" id="f_jornada_picker" data-target-input="nearest">
                                        <input type="text" name="f_jornada" required id="f_jornada"
                                            class="form-control datetimepicker-input" data-target="#f_jornada_picker" />
                                        <div class="input-group-append" data-target="#f_jornada_picker"
                                            data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <button type="button" class="btn btn-info" onclick="verJornadas()"
                                        style="margin-top: 32px;">
                                        <i class="fas fa-history"></i> Ver Historial de Jornadas
                                    </button>
                                </div>

                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="observacion">Observaciones:</label>
                                    <textarea id="observacion" name="observacion" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Relaciones -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Historial de Relaciones</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-success btn-sm" onclick="addHistorialRel()">
                                    <i class="fas fa-plus"></i> Agregar Relación
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="container_relaciones">
                                <!-- Relaciones dinámicas se agregan aquí -->
                            </div>
                        </div>
                    </div>

                    <!-- Documentos Escaneados -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Documentos Escaneados</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-success btn-sm" onclick="addDoc()">
                                    <i class="fas fa-plus"></i> Agregar Documento
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="container_doc">
                                <!-- Documentos dinámicos se agregan aquí -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                @if ($permisos['crear'] || $permisos['editar'])
                    <button type="button" id="btnGuardar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                @endif
                <button type="button" id="btnLimpiar" class="btn btn-warning">
                    <i class="fas fa-times"></i> Limpiar
                </button>
                <button type="button" id="btnImprimir" class="btn btn-info">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                @if ($permisos['eliminar'])
                    <button type="button" id="btnEliminar" class="btn btn-danger" onclick="modalEliminar()">
                        <i class="fa fa-trash"></i> Eliminar
                    </button>
                @endif
                <!-- Botón para llenar con datos de prueba (solo en desarrollo) -->
                <button type="button" id="btnDatosPrueba" class="btn btn-secondary" onclick="fillPersonalFormWithTestData()"
                    title="Llenar con datos de prueba">
                    <i class="fas fa-flask"></i> Datos de Prueba
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
    <!-- Include custom JavaScript file -->
    <script src="{{ asset('js/croppie.js') }}"></script>
    <script src="{{ asset('js/webcam.min.js') }}"></script>
    <script src="{{ asset('js/form-filler.js') }}"></script>
    <script src="{{ asset('js/personal.js') }}"></script>
@endpush

@push('styles')
    <style>
        /* Estilos adicionales para el módulo de personal */
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

        .typeahead {
            background-color: #fff;
        }

        .tt-menu {
            width: 100%;
            margin: 2px 0;
            padding: 8px 0;
            background-color: #fff;
            border: 1px solid #ccc;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
        }

        .tt-suggestion {
            padding: 3px 20px;
            line-height: 24px;
        }

        .tt-suggestion:hover {
            cursor: pointer;
            color: #fff;
            background-color: #0097cf;
        }

        .tt-suggestion.tt-cursor {
            color: #fff;
            background-color: #0097cf;
        }

        .info-pagination {
            font-size: 0.9rem;
            color: #666;
        }

        .select2-container,
        .select2-dropdown,
        .select2-search,
        .select2-results {
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -ms-transition: none !important;
            -o-transition: none !important;
            transition: none !important;
        }

        /* Estilos para la imagen de perfil clickeable */
        #img_crop:hover {
            border-color: #007bff !important;
            opacity: 0.8;
        }

        #img_crop:active {
            transform: scale(0.98);
        }
    </style>
@endpush
