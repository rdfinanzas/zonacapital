@extends('layouts.main')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/registros-dengue.css') }}">
@endpush

@section('header-title', 'Registro Febril')

@section('content')
<style>
    .image_empty {
        cursor: pointer;
    }
    .overlay_image {
        justify-content: center;
        z-index: 50;
        display: flex;
        background: rgba(255, 255, 255, 0.7);
        align-items: center;
        height: 100%;
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
        font-size: 12px;
    }
    .uppercase-text {
        text-transform: uppercase;
        font-size: 10px !important;
    }
</style>

<!-- Content Header (Page header) -->
{{-- <section class="content-header d-none d-md-block" id="contentHeader">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Registro Febril</h1>
            </div>
        </div>
    </div>
</section> --}}

<!-- Modal Eliminar -->
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

<!-- Modal Confirmar -->
<div class="modal fade" id="modal_confirmar">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Atención!</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Desea guardar el registro? No ha ingresado el antecedente de viaje o no ha proporcionado una dirección válida.
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-success" onclick="guardar()">Guardar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Datos del Paciente -->
<div class="modal fade" id="modal_datos_paciente">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white">
                    <i class="fas fa-user-circle mr-2"></i>
                    <span id="modal_paciente_titulo">Datos del Paciente</span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Vista de datos cuando el paciente existe -->
                <div id="paciente_existente" style="display:none;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-id-card mr-2"></i>Información del Paciente
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" id="btn_editar_paciente" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Editar Datos
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-primary">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Nombre Completo</span>
                                                    <span class="info-box-number text-primary" id="display_nombre">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-venus-mars"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Sexo</span>
                                                    <span class="info-box-number text-info" id="display_sexo">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-success">
                                                    <i class="fas fa-birthday-cake"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Fecha Nacimiento</span>
                                                    <span class="info-box-number text-success" id="display_fecha_nac">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-warning">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Celular</span>
                                                    <span class="info-box-number text-warning" id="display_celular">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <h5 class="text-primary border-bottom pb-2">
                                                <i class="fas fa-map-marker-alt mr-2"></i>Domicilio del Paciente
                                            </h5>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted">Domicilio:</label>
                                                <p class="font-weight-bold" id="display_domicilio">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-muted">Departamento:</label>
                                                <p class="font-weight-bold" id="display_departamento">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-muted">Localidad:</label>
                                                <p class="font-weight-bold" id="display_localidad">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted">Barrio:</label>
                                                <p class="font-weight-bold" id="display_barrio">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-muted">Referencias:</label>
                                                <p class="font-weight-bold" id="display_referencias">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario para editar/crear paciente -->
                <div id="formulario_paciente">
                    <form id="form_paciente_modal">
                        @csrf
                        <input type="hidden" id="paciente_id" name="paciente_id">

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-user-edit mr-2"></i>Datos Personales
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="modal_nombre">Apellido/Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" required id="modal_nombre" name="modal_nombre">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="modal_sexo">Sexo <span class="text-danger">*</span></label>
                                <select class="form-control" name="modal_sexo" required id="modal_sexo">
                                    <option value="0">M</option>
                                    <option value="1">F</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="modal_fecha_nac">Fecha Nacimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" required name="modal_fecha_nac" id="modal_fecha_nac"
                                       data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy"
                                       placeholder="dd/mm/aaaa" data-mask>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="modal_celular">Celular <span class="text-danger">*</span></label>
                                <input type="text" required id="modal_celular" name="modal_celular" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="text-primary border-bottom pb-2 mt-3">
                                    <i class="fas fa-map-marker-alt mr-2"></i>Domicilio del Paciente
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="modal_domicilio">
                                    Domicilio <span class="text-danger">*</span>
                                    <i class="fas fa-times" style="color:red;display:none;font-size: 18px;" id="modal_check_no_dir"></i>
                                    <i class="fas fa-check" style="color:green;display:none;font-size: 18px;" id="modal_check_ok_dir"></i>
                                </label>
                                <input type="text" required id="modal_domicilio" name="modal_domicilio" class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="modal_dto">Departamento <span class="text-danger">*</span></label>
                                <input type="text" required id="modal_dto" name="modal_dto" class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="modal_localidad">Localidad <span class="text-danger">*</span></label>
                                <input type="text" required id="modal_localidad" name="modal_localidad" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="modal_barrio">Barrio</label>
                                <input type="text" id="modal_barrio" name="modal_barrio" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="modal_referencia">Referencias</label>
                                <input type="text" id="modal_referencia" name="modal_referencia" class="form-control">
                            </div>
                        </div>

                        <input type="hidden" id="modal_latitud" name="modal_latitud">
                        <input type="hidden" id="modal_longitud" name="modal_longitud">
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <div id="botones_vista" style="display:none;">
                    <button type="button" class="btn btn-success" data-dismiss="modal">
                        <i class="fas fa-check"></i> Continuar con Registro
                    </button>
                </div>
                <div id="botones_edicion">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" id="btn_cancelar_edicion" class="btn btn-warning" style="display:none;">
                        <i class="fas fa-undo"></i> Cancelar Edición
                    </button>
                    <button type="button" id="btn_guardar_paciente" class="btn btn-primary">
                        <i class="fas fa-save"></i> <span id="texto_btn_guardar">Guardar Paciente</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row mt-md-0">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="card card-primary" id="card_form" style="display:none">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <span class="d-md-none">Registro Febril</span>
                            <span class="d-none d-md-inline">Formulario</span>
                        </h3>
                        <button type="button" class="btn btn-secondary" id="btn_volver">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                    </div>
                    {{-- //  @dd($permisos['todos_efect_dengue']) --}}
                    <!-- form start -->
                    <form role="form" id="form_main">
                        @csrf
                        <div class="card-body">
                            <div class="row mt-md-0">

                                @if(!($permisos['todos_efect_dengue'] ?? false))
                                    @if($efector)
                                        @if($efector['Region_Id'] == 0)
                                            <div class="alert alert-warning alert-dismissible mt-3">
                                                <h5><i class="icon fas fa-exclamation-triangle"></i> Error!</h5>
                                                Este Efector no está vinculado a ninguna región.
                                            </div>
                                        @endif
                                        <div class="form-group col-12 col-md-6">
                                            <label for="efector">Efector:</label><br>
                                            <span id="efector">{{ $efector['servicio'] }}</span>
                                        </div>
                                    @else
                                        <div class="alert alert-warning alert-dismissible mt-3">
                                            <h5><i class="icon fas fa-exclamation-triangle"></i> Error!</h5>
                                            No existe un efector para este usuario. Asegúrese que este usuario esté vinculado a un personal y a su vez este a un servicio.
                                        </div>
                                    @endif
                                @else
                                    <div class="form-group col-12 col-md-6">
                                        <label for="efector_sel">Efector:</label>
                                        <select class="form-control select2" name="efector_sel" required id="efector_sel">
                                            <option value="">-EFECTOR-</option>
                                            @foreach($efector_todos as $arr)
                                                <option data-region="{{ $arr['Region_Id'] }}" value="{{ $arr['idServicio'] }}">{{ $arr['servicio'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>

                            <div class="row mt-md-0">
                                <div class="form-group col-12 col-md-6">
                                    <label for="dni">DNI Paciente</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" required id="dni" name="dni">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" id="btn_buscar_dni" style="width:100px;" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="form_persona" style="display:none;">
                                <h5 id="leyenda_consulta" style="display:none"></h5>

                                <!-- Resumen elegante del paciente -->
                                <div id="resumen_paciente" class="card card-outline card-primary mb-3" style="display:none;">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-user mr-2"></i>Paciente Registrado
                                        </h3>
                                        <div class="card-tools">
                                            <button type="button" id="btn_ver_editar_paciente" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Ver/Editar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong><i class="fas fa-user mr-1"></i> Nombre:</strong>
                                                <span id="resumen_nombre" class="text-primary">-</span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong><i class="fas fa-venus-mars mr-1"></i> Sexo:</strong>
                                                <span id="resumen_sexo">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong><i class="fas fa-birthday-cake mr-1"></i> F. Nac:</strong>
                                                <span id="resumen_fecha_nac">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong><i class="fas fa-phone mr-1"></i> Celular:</strong>
                                                <span id="resumen_celular">-</span>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <strong><i class="fas fa-home mr-1"></i> Domicilio:</strong>
                                                <span id="resumen_domicilio_completo" class="text-muted">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulario principal después del resumen del paciente -->
                                <h5 class="mt-4 mb-3 border-top pt-3"><i class="fas fa-map-marked-alt mr-2"></i>Domicilio donde ocurrió el Caso de Dengue</h5>
                                <div class="alert alert-info alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h5><i class="icon fas fa-info"></i> Importante!</h5>
                                    Indique la dirección específica donde ocurrió este caso de dengue. Puede ser diferente al domicilio del paciente.
                                </div>

                                <div class="row col-12 mt-md-0">
                                    <div class="form-group col-12 col-md-4 mt-md-6">
                                        <label for="domicilio_hecho">
                                            Domicilio del Hecho <span class="text-danger">*</span>
                                            <i class="fas fa-times" style="color:red;display:none;font-size: 18px;" id="check_no_dir_hecho"></i>
                                            <i class="fas fa-check" style="color:green;display:none;font-size: 18px;" id="check_ok_dir_hecho"></i>
                                        </label>
                                        <input type="text" required id="domicilio_hecho" name="domicilio_hecho" class="form-control" placeholder="Dirección donde ocurrió el caso">
                                    </div>

                                    <div class="row">
                                        <div id="error_gps" style="display:none" class="alert alert-danger alert-dismissible mt-3">
                                            <h5><i class="icon fas fa-exclamation-triangle"></i> Error!</h5>
                                            Ha ocurrido un error al obtener su posición actual. Asegúrese de activar el GPS.
                                        </div>
                                        <div class="form-group col-12 col-md-6">
                                            <div class="cont_marca_mapa d-md-none">
                                                <button type="button" id="btn_ubicacion_actual" onclick="ubicacionActualHecho()"
                                                        class="btn btn-primary btn-lg d-md-none btn-block">
                                                    Ubicación Actual del Hecho <i class="fas fa-map-marker"></i>
                                                </button>
                                                <small class="d-md-none">Obtener dirección del hecho a través del GPS</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-md-4 mt-md-6">
                                        <label for="dto_hecho">Departamento <span class="text-danger">*</span></label>
                                        <input type="text" required id="dto_hecho" name="dto_hecho" class="form-control">
                                    </div>

                                    <div class="form-group col-12 col-md-4 mt-md-6">
                                        <label for="localidad_hecho">Localidad <span class="text-danger">*</span></label>
                                        <input type="text" required id="localidad_hecho" name="localidad_hecho" class="form-control">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6 col-xs-12">
                                        <label for="barrio_hecho">Barrio del Hecho</label>
                                        <input type="text" id="barrio_hecho" name="barrio_hecho" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6 col-xs-12">
                                        <label for="referencia_hecho">Referencias del Hecho</label>
                                        <input type="text" id="referencia_hecho" name="referencia_hecho" class="form-control" placeholder="Puntos de referencia para ubicar el lugar">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="usar_domicilio_paciente">
                                            <label class="form-check-label text-primary" for="usar_domicilio_paciente">
                                                <i class="fas fa-copy mr-1"></i>Usar el mismo domicilio del paciente
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="cont_marca_mapa">
                                    <div class="row pt-3">
                                        <div class="form-group col-12">
                                            <button type="button" onclick="marcarMapaHecho()" class="btn btn-primary">
                                                Marcar Ubicación del Hecho en el Mapa <i class="fas fa-map-marker"></i>
                                            </button>
                                        </div>
                                        <div class="form-group col-12">
                                            <div id="clickMapHecho" style="display:none;height: 400px; width: 100%;"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campos ocultos para coordenadas del hecho -->
                                <input type="hidden" id="latitud_hecho" name="latitud_hecho">
                                <input type="hidden" id="longitud_hecho" name="longitud_hecho">

                                <!-- Campos ocultos para datos del paciente -->
                                <input type="hidden" id="paciente_id_form" name="paciente_id_form">
                                <input type="hidden" id="nombre" name="nombre">
                                <input type="hidden" id="sexo" name="sexo">
                                <input type="hidden" id="fecha_nac" name="fecha_nac">
                                <input type="hidden" id="celular" name="celular">
                                <input type="hidden" id="domicilio" name="domicilio">
                                <input type="hidden" id="dto" name="dto">
                                <input type="hidden" id="localidad" name="localidad">
                                <input type="hidden" id="barrio" name="barrio">
                                <input type="hidden" id="referencia" name="referencia">
                                <input type="hidden" id="latitud" name="latitud">
                                <input type="hidden" id="longitud" name="longitud">

                                <h5 class="mt-4 pt-2 mb-3 border-top">Datos Epidemiológicos</h5>
                                <div class="row">
                                    <div class="form-group col-6 col-md-3 mt-md-4">
                                        <label for="semana">Semana:</label>
                                        <input type="number" id="semana" name="semana" class="form-control" required>
                                    </div>

                                    <div class="form-group col-6 col-md-3 mt-md-4">
                                        <label for="fecha_fis">F.I.S.:</label>
                                        <div class="input-group date" id="fecha_fis" data-target-input="nearest">
                                            <input type="text" name="f_fis" id="f_fis" autocomplete="off" required
                                                   class="form-control datetimepicker-input" data-target="#fecha_fis">
                                            <div class="input-group-append" data-target="#fecha_fis" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-md-3 mt-md-4">
                                        <label for="fecha_consulta">Consulta:</label>
                                        <div class="input-group date" id="fecha_consulta" data-target-input="nearest">
                                            <input type="text" name="f_consulta" id="f_consulta" autocomplete="off" required
                                                   class="form-control datetimepicker-input" data-target="#fecha_consulta">
                                            <div class="input-group-append" data-target="#fecha_consulta" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-md-3 mt-md-4">
                                        <label for="fecha_muestra">Toma de Muestra:</label>
                                        <div class="input-group date" id="fecha_muestra" data-target-input="nearest">
                                            <input type="text" name="f_muestra" id="f_muestra" autocomplete="off"
                                                   class="form-control datetimepicker-input" data-target="#fecha_muestra">
                                            <div class="input-group-append" data-target="#fecha_muestra" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 col-md-4 mt-md-4">
                                        <label for="internacion">Internación:</label>
                                        <select class="form-control" name="internacion" required id="internacion">
                                            <option value="1">SI</option>
                                            <option value="0" selected>NO</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-md-4 mt-md-4 cont_int" style="display:none;">
                                        <label for="fecha_ingreso">Fecha Ingreso:</label>
                                        <div class="input-group date" id="fecha_ingreso" data-target-input="nearest">
                                            <input type="text" name="f_ingreso" id="f_ingreso" autocomplete="off"
                                                   class="form-control datetimepicker-input" data-target="#fecha_ingreso">
                                            <div class="input-group-append" data-target="#fecha_ingreso" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-md-4 mt-md-4 cont_int" style="display:none;">
                                        <label for="fecha_alta">Fecha Alta:</label>
                                        <div class="input-group date" id="fecha_alta" data-target-input="nearest">
                                            <input type="text" name="f_alta" id="f_alta" autocomplete="off"
                                                   class="form-control datetimepicker-input" data-target="#fecha_alta">
                                            <div class="input-group-append" data-target="#fecha_alta" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tests de Laboratorio -->
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="laboratorio">Laboratorio:</label>
                                        <input type="text" id="laboratorio" name="laboratorio" class="form-control">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="testAgNS1">Ag- NS1</label>
                                        <select class="form-control" id="testAgNS1" name="testAgNS1">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="1">Positivo</option>
                                            <option value="0">Negativo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="tipoNs1">Tipo Ag-NS1</label>
                                        <select class="form-control" id="tipoNs1" name="tipoNs1">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="0">ELISA</option>
                                            <option value="1">TR</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="testIgM">IgM UM-ELISA</label>
                                        <select class="form-control" id="testIgM" name="testIgM">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="1">Positivo</option>
                                            <option value="0">Negativo</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="testIGG">IGG</label>
                                        <select class="form-control" id="testIGG" name="testIGG">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="1">Positivo</option>
                                            <option value="0">Negativo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="testPCR">PCR</label>
                                        <select class="form-control" id="testPCR" name="testPCR">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="1">Positivo</option>
                                            <option value="0">Negativo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="testRapidoIgG">Test rápido IgG</label>
                                        <select class="form-control" id="testRapidoIgG" name="testRapidoIgG">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="1">Positivo</option>
                                            <option value="0">Negativo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="testRapidoIgM">Test rápido IgM</label>
                                        <select class="form-control" id="testRapidoIgM" name="testRapidoIgM">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="1">Positivo</option>
                                            <option value="0">Negativo</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="testChikungunya">CHIKUNGUNYA</label>
                                        <select class="form-control" id="testChikungunya" name="testChikungunya">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="1">Positivo</option>
                                            <option value="0">Negativo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="testZika">ZIKA</label>
                                        <select class="form-control" id="testZika" name="testZika">
                                            <option value="" selected>-Seleccionar-</option>
                                            <option value="1">Positivo</option>
                                            <option value="0">Negativo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="ant_vacu">Ant. de Vacunación</label>
                                        <select class="form-control" id="ant_vacu" name="ant_vacu">
                                            <option value="1">SI</option>
                                            <option value="0" selected>NO</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 col-md-4">
                                        <label for="obito">Obito:</label>
                                        <select class="form-control" name="obito" required id="obito">
                                            <option value="1">SI</option>
                                            <option value="0" selected>NO</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-md-4">
                                        <label for="comor">Comorbilidad:</label>
                                        <input type="text" id="comor" name="comor" class="form-control">
                                    </div>

                                    <div class="form-group col-12 col-md-4">
                                        <label for="obs">Observaciones:</label>
                                        <textarea id="obs" name="obs" class="form-control"></textarea>
                                    </div>
                                </div>

                                <div class="row mt-4 mt-md-0">
                                    <div class="form-group col-md-6">
                                        <label for="ant_viaje">
                                            Ant. de Viaje:
                                            <i class="fas fa-times" style="color:red;display:none;font-size: 18px;" id="check_no_dir_viaje"></i>
                                            <i class="fas fa-check" style="color:green;display:none;font-size: 18px;" id="check_ok_dir_viaje"></i>
                                        </label>
                                        <input type="text" id="ant_viaje" name="ant_viaje" class="form-control">
                                    </div>

                                    <div class="form-group col">
                                        <label for="fecha_ant">Fecha:</label>
                                        <div class="input-group date" id="fecha_ant" data-target-input="nearest">
                                            <input type="text" name="f_ant" id="f_ant" autocomplete="off"
                                                   class="form-control datetimepicker-input" data-target="#fecha_ant">
                                            <div class="input-group-append" data-target="#fecha_ant" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="cont_marca_mapa_viaje">
                                    <div class="row pt-3 d-none d-md-block">
                                        <div class="form-group col-12">
                                            <button type="button" onclick="marcarMapaViaje()" class="btn btn-primary">
                                                Marcar en el Mapa <i class="fas fa-map-marker"></i>
                                            </button>
                                        </div>
                                        <div class="form-group col-12">
                                            <div id="clickMapViaje" style="display:none;height: 400px; width: 100%;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col">
                                        <label>Ficha</label>
                                        <input type="file" id="imagen">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer" id="footer_btn" style="display:none">
                            @if(($permisos['todos_efect_dengue'] ?? false) || ($efector && $efector['Region_Id'] != 0))
                                <div class="row">
                                    <div class="col mt-md-0">
                                        @if($permisos['C'] || $permisos['U'])
                                            <button type="submit" id="btn_submit" class="btn btn-primary">
                                                Guardar <i class="fas fa-save"></i>
                                            </button>
                                        @endif

                                        @if($permisos['C'])
                                            <button type="button" id="btn_limpiar" class="btn btn-warning">
                                                Limpiar <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        @if($permisos['D'])
                                            <button type="button" id="btn_eliminar" class="btn btn-danger">
                                                Eliminar <i class="fa fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de Registros -->
        <div class="row mt-4 mt-md-0">
            <div class="col-md-12">
                <div class="card card-primary" id="card_lista">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Lista de Registros de Dengue</h3>
                        <div class="card-tools">
                            <button type="button" id="btn_agregar" class="btn btn-success btn-sm">Agregar <i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mt-4 mt-md-0">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">Filtros</h3>

                                    </div>
                                    <form action="javascript:refrescarTabla()">
                                        <div class="card-body">
                                            <div class="row mt-4 mt-md-0">
                                                <div class="form-group col-12 col-md-3">
                                                    <label for="d_fil">Fecha Carga desde:</label>
                                                    <div class="input-group date" id="desde_fil" data-target-input="nearest">
                                                        <input type="text" id="d_fil" class="form-control datetimepicker-input" data-target="#desde_fil">
                                                        <div class="input-group-append" data-target="#desde_fil" data-toggle="datetimepicker">
                                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group col-12 col-md-3">
                                                    <label for="h_fil">Fecha Carga hasta:</label>
                                                    <div class="input-group date" id="hasta_fil" data-target-input="nearest">
                                                        <input type="text" id="h_fil" class="form-control datetimepicker-input" data-target="#hasta_fil">
                                                        <div class="input-group-append" data-target="#hasta_fil" data-toggle="datetimepicker">
                                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                        </div>
                                                    </div>
                                                </div>

                                <div class="form-group col-12 col-md-3">
                                    <label for="efector_sel_fil">Efector:</label>
                                    <select class="form-control select2" name="efector_sel_fil" id="efector_sel_fil">
                                        <option value="0">-TODOS-</option>
                                        @foreach($efector_todos as $arr)
                                            <option value="{{ $arr['idServicio'] }}">{{ $arr['servicio'] }}</option>
                                        @endforeach
                                        {{-- Debug: Mostrar cantidad de efectores --}}
                                        @if(count($efector_todos) == 0)
                                            <option disabled>No hay efectores disponibles</option>
                                        @endif
                                    </select>
                                    {{-- <small class="text-muted">Efectores cargados: {{ count($efector_todos) }}</small> --}}
                                </div>                                                <div class="form-group col-12 col-md-3">
                                                    <label for="usuario_fil">Usuario:</label>
                                                    <select class="form-control select2" name="usuario_fil" id="usuario_fil">
                                                        <option value="0">-TODOS-</option>
                                                        @foreach($usuarios as $usuario)
                                                            <option value="{{ $usuario->IdUsuario }}">{{ $usuario->Apellido }}, {{ $usuario->Nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mt-4 mt-md-0">
                                                <div class="form-group col-md-3 col-12">
                                                    <label for="region_fil">Región:</label>
                                                    <select class="form-control select2" multiple name="region_fil" id="region_fil">
                                                        <option value="">-TODAS-</option>
                                                        <option value="1">REGION I</option>
                                                        <option value="2">REGION II</option>
                                                        <option value="3">REGION III</option>
                                                        <option value="4">REGION IV</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">
                                                Buscar <i class="fas fa-search"></i>
                                            </button>

                                            <button type="button" id="btn_exportar" class="btn btn-success" title="Exportar a CSV (compatible con Excel)">
                                             <i class="fas fa-file-excel"></i></i> Exportar
                                        </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Consulta</th>
                                        <th>Efector</th>
                                        <th>Paciente</th>
                                        <th>Operador</th>
                                        <th>Fecha Reg.</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="table_data">
                                </tbody>
                            </table>
                        </div>

                        <div id="total_info" class="info-pagination"></div>
                        <div class="row mt-4 mt-md-0">
                            <div class="col-md-2" id="page-selection_num_page" style="padding-top: 20px"></div>
                            <div class="col">
                                <div id="page-selection"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hidden inputs para JavaScript -->
<input type="hidden" id="_usuario_session" value="{{ auth()->user()?->id ?? '' }}">
<input type="hidden" id="_token" value="{{ csrf_token() }}">
<input type="hidden" id="permisos" value="{{ $permisos['C'] }}|{{ $permisos['U'] }}|{{ $permisos['D'] }}|{{ ($permisos['todos_efect_dengue'] ?? false) ? 1 : 0 }}">

@endsection

@push('scripts')
<!-- Google Maps JS (Places) -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&language=es&region=AR"></script>
<script src="{{ asset('js/imageLoad.js') }}"></script>
<script>
    // Laravel routes for JavaScript - Using Laravel route() helper
    window.laravelRoutes = {
        usuariosAutocomplete: '{{ url("/api/usuarios-autocomplete") }}',
        registrosDengueFiltrar: '{{ route("registros-dengue.filtrar") }}',
        registrosDengueInforme: '{{ route("registros-dengue.informe") }}',
        registrosDengueStore: '{{ route("registros-dengue.store") }}',
        registrosDengueUpdate: '{{ route("registros-dengue.update", ":id") }}',
        registrosDengueDestroy: '{{ route("registros-dengue.destroy", ":id") }}',
        registrosDengueGet: '{{ route("registros-dengue.get", ":id") }}',
        registrosDengueBuscarDni: '{{ route("registros-dengue.buscar-dni") }}',
        registrosDengueStorePaciente: '{{ route("registros-dengue.store-paciente") }}',
        registrosDengueGetPaciente: '{{ route("registros-dengue.get-paciente", ":id") }}',
        registrosDengueUpdatePaciente: '{{ route("registros-dengue.update-paciente", ":id") }}'
    };
  //  console.log("hola",window.laravelRoutes)
</script>
<script src="{{ asset('js/registros-dengue.js') }}?v={{ filemtime(public_path('js/registros-dengue.js')) }}"></script>
<script>
    var refererModule = 'registros-dengue';
    if (window.innerWidth <= 768) {
        document.getElementById('contentHeader').style.display = 'none';
    }
</script>
@endpush
