@extends('layouts.main')

@section('header-title', 'Registro Influenza ETI')

@section('content')
<div class="container-fluid">
    <div class="card card-primary" id="card_form">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Formulario</h3>
            <button type="button" class="btn btn-secondary" id="btn_volver" style="display:none"><i class="fas fa-arrow-left"></i> Volver</button>
        </div>
        <form id="form_main">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-12 col-md-6">
                        <label for="efector_sel">Efector</label>
                        <select class="form-control select2" id="efector_sel" name="efectorSel">
                            <option value="">-EFECTOR-</option>
                            @foreach($servicios as $serv)
                                <option data-region="{{ $serv->Region_Id }}" value="{{ $serv->idServicio }}">{{ $serv->servicio }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-12 col-md-6">
                        <label for="dni">DNI Paciente</label>
                        <div class="input-group">
                            <input type="number" class="form-control" required id="dni" name="dni">
                            <div class="input-group-append">
                                <button class="btn btn-primary" id="btn_buscar_dni" type="button"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="form_persona" style="display:none;">
                    <h5 class="mt-3 mb-3">Datos del paciente</h5>
                    <div class="row">
                        <div class="form-group col-12 col-md-6">
                            <label for="nombre">Apellido/Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre">
                        </div>
                        <div class="form-group col-12 col-md-2">
                            <label for="sexo">Sexo</label>
                            <select class="form-control" id="sexo" name="sexo">
                                <option value="1">M</option>
                                <option value="2">F</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="fecha_nac">Fecha Nacimiento</label>
                            <input type="text" class="form-control" name="fecha_nac" id="fecha_nac" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" placeholder="dd/mm/aaaa" data-mask>
                        </div>
                        <div class="form-group col-12">
                            <label for="celular">Celular</label>
                            <input type="text" id="celular" name="celular" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-4">
                            <label for="domicilio">Domicilio</label>
                            <input type="text" id="domicilio" name="domicilio" class="form-control">
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="dto">Departamento</label>
                            <input type="text" id="dto" name="dto" class="form-control">
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="localidad">Localidad</label>
                            <input type="text" id="localidad" name="localidad" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="barrio">Barrio</label>
                            <input type="text" id="barrio" name="barrio" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="referencia">Referencias</label>
                            <input type="text" id="referencia" name="referencia" class="form-control">
                        </div>
                    </div>
                </div>

                <h5 class="mt-4 pt-2 mb-3 border-top">Datos Clínicos</h5>
                <div class="row">
                    <div class="form-group col-12 col-md-3">
                        <label for="semana">Semana</label>
                        <input type="number" id="semana" name="semana" class="form-control">
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="f_fis">FIS</label>
                        <input type="text" id="f_fis" name="f_fis" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" placeholder="dd/mm/aaaa" data-mask>
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="f_consulta">Consulta</label>
                        <input type="text" id="f_consulta" name="f_consulta" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" placeholder="dd/mm/aaaa" data-mask>
                    </div>
                    <div class="form-group col-12 col-md-3">
                        <label for="f_muestra">Fecha Muestra</label>
                        <input type="text" id="f_muestra" name="f_muestra" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" placeholder="dd/mm/aaaa" data-mask>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-12 col-md-6">
                        <label for="laboratorio">Laboratorio</label>
                        <input type="text" id="laboratorio" name="laboratorio" class="form-control">
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label for="obs">Observaciones</label>
                        <input type="text" id="obs" name="obs" class="form-control">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="form-group col-12 col-md-6">
                        <button type="button" id="btn_guardar" class="btn btn-success btn-block">Guardar</button>
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <button type="button" id="btn_limpiar" class="btn btn-secondary btn-block">Limpiar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Registros</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-12 col-md-3">
                    <label>Desde</label>
                    <input type="text" id="list_desde" class="form-control" placeholder="dd/mm/aaaa" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label>Hasta</label>
                    <input type="text" id="list_hasta" class="form-control" placeholder="dd/mm/aaaa" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label>Usuario</label>
                    <select id="list_usuario" class="form-control select2">
                        <option value="0">-USUARIO-</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->IdUsuario }}">{{ $u->Nombre }} {{ $u->Apellido }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label>Región</label>
                    <select id="list_region" class="form-control select2">
                        <option value="">-REGIÓN-</option>
                        @foreach($regiones as $r)
                            <option value="{{ $r->IdRegion }}">{{ $r->Region }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-12 col-md-6">
                    <label>Efector</label>
                    <select id="list_efector" class="form-control select2">
                        <option value="0">-EFECTOR-</option>
                        @foreach($servicios as $s)
                            <option data-region="{{ $s->Region_Id }}" value="{{ $s->idServicio }}">{{ $s->servicio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-6 d-flex align-items-end">
                    <button id="btn_filtrar" class="btn btn-primary btn-block">Filtrar</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="tabla_registros">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Paciente</th>
                            <th>DNI</th>
                            <th>Efector</th>
                            <th>Consulta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/registro-eti.js') }}"></script>
@endsection