@extends('layouts.app')
@section('content')
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

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Parámetros</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Parámetros del Sistema</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Clasificación Personal -->
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Clasificación Personal:</label>
                                        <select class="form-control" id="clasificacion_personal_sel">
                                            <option value="0">-Clasificación Personal:-</option>
                                        </select>
                                        <input id="clasificacion_personal_txt" class="form-control" type="text" style="margin-top: 10px;" placeholder="Nombre">
                                        <input id="clasificacion_personal_txt_1" class="form-control" type="text" style="margin-top: 10px;" placeholder="Abreviatura">
                                    </div>
                                    <div class="col-md-4 pt-4">
                                        <div class="btn-group-vertical" role="group">
                                            <button type="button" onclick="guardar('clasificacion_personal')" class="btn btn-primary btn-sm mb-1"><i class="fas fa-save"></i> Guardar</button>
                                            @if($permisos['eliminar'] ?? false)
                                            <button type="button" onclick="del('clasificacion_personal')" class="btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i> Eliminar</button>
                                            @endif
                                            <button type="button" onclick="limpiarCampos()" class="btn btn-warning btn-sm"><i class="fas fa-broom"></i> Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Funciones -->
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Funciones:</label>
                                        <select class="form-control" id="funciones_sel">
                                            <option value="0">-Funciones:-</option>
                                        </select>
                                        <input id="funciones_txt" class="form-control" type="text" style="margin-top: 10px;" placeholder="Nombre">
                                    </div>
                                    <div class="col-md-4 pt-4">
                                        <div class="btn-group-vertical" role="group">
                                            <button type="button" onclick="guardar('funciones')" class="btn btn-primary btn-sm mb-1"><i class="fas fa-save"></i> Guardar</button>
                                            @if($permisos['eliminar'] ?? false)
                                            <button type="button" onclick="del('funciones')" class="btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i> Eliminar</button>
                                            @endif
                                            <button type="button" onclick="limpiarCampos()" class="btn btn-warning btn-sm"><i class="fas fa-broom"></i> Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profesiones -->
                            <div class="col-md-6 mt-3">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Profesiones:</label>
                                        <select class="form-control" id="profesion_sel">
                                            <option value="0">-Profesiones:-</option>
                                        </select>
                                        <input id="profesion_txt" class="form-control" type="text" style="margin-top: 10px;" placeholder="Nombre">
                                    </div>
                                    <div class="col-md-4 pt-4">
                                        <div class="btn-group-vertical" role="group">
                                            <button type="button" onclick="guardar('profesion')" class="btn btn-primary btn-sm mb-1"><i class="fas fa-save"></i> Guardar</button>
                                            @if($permisos['eliminar'] ?? false)
                                            <button type="button" onclick="del('profesion')" class="btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i> Eliminar</button>
                                            @endif
                                            <button type="button" onclick="limpiarCampos()" class="btn btn-warning btn-sm"><i class="fas fa-broom"></i> Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Relaciones Laborales -->
                            <div class="col-md-6 mt-3">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Relaciones Laborales:</label>
                                        <select class="form-control" id="tiporelacion_sel">
                                            <option value="0">-Relaciones:-</option>
                                        </select>
                                        <input id="tiporelacion_txt" class="form-control" type="text" style="margin-top: 10px;" placeholder="Nombre">
                                    </div>
                                    <div class="col-md-4 pt-4">
                                        <div class="btn-group-vertical" role="group">
                                            <button type="button" onclick="guardar('tiporelacion')" class="btn btn-primary btn-sm mb-1"><i class="fas fa-save"></i> Guardar</button>
                                            @if($permisos['eliminar'] ?? false)
                                            <button type="button" onclick="del('tiporelacion')" class="btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i> Eliminar</button>
                                            @endif
                                            <button type="button" onclick="limpiarCampos()" class="btn btn-warning btn-sm"><i class="fas fa-broom"></i> Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Agrupamiento -->
                            <div class="col-md-6 mt-3">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Grado/Agrupamiento:</label>
                                        <select class="form-control" id="agrupamiento_sel">
                                            <option value="0">-Grado:-</option>
                                        </select>
                                        <input id="agrupamiento_txt" class="form-control" type="text" style="margin-top: 10px;" placeholder="Nombre">
                                    </div>
                                    <div class="col-md-4 pt-4">
                                        <div class="btn-group-vertical" role="group">
                                            <button type="button" onclick="guardar('agrupamiento')" class="btn btn-primary btn-sm mb-1"><i class="fas fa-save"></i> Guardar</button>
                                            @if($permisos['eliminar'] ?? false)
                                            <button type="button" onclick="del('agrupamiento')" class="btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i> Eliminar</button>
                                            @endif
                                            <button type="button" onclick="limpiarCampos()" class="btn btn-warning btn-sm"><i class="fas fa-broom"></i> Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tipo Jornada -->
                            <div class="col-md-6 mt-3">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label>Jornadas:</label>
                                        <select class="form-control" id="tipo_jornada_sel">
                                            <option value="0">-Jornadas:-</option>
                                        </select>
                                        <input id="tipo_jornada_txt" class="form-control" type="text" style="margin-top: 10px;" placeholder="Nombre">
                                        <input id="tipo_jornada_txt_1" class="form-control" type="text" style="margin-top: 10px;" placeholder="Horas (HH:MM)">
                                    </div>
                                    <div class="col-md-4 pt-4">
                                        <div class="btn-group-vertical" role="group">
                                            <button type="button" onclick="guardar('tipo_jornada')" class="btn btn-primary btn-sm mb-1"><i class="fas fa-save"></i> Guardar</button>
                                            @if($permisos['eliminar'] ?? false)
                                            <button type="button" onclick="del('tipo_jornada')" class="btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i> Eliminar</button>
                                            @endif
                                            <button type="button" onclick="limpiarCampos()" class="btn btn-warning btn-sm"><i class="fas fa-broom"></i> Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
<script src="{{ asset('js/parametros.js') }}"></script>
@endsection
