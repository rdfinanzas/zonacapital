@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Logs de usuario</h1>
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
                            <h3 class="card-title">Lista de logs</h3>
                        </div>
                        <div class="card-body">
                            <div class="card">
                                <div class="row">
                                    <div class="form-group col">
                                        <label for="search_usuario">Usuario:</label>
                                        <select class="form-control select2" id="search_usuario">
                                            <option value="">-TODOS-</option>
                                            @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id_usuario }}">{{ $usuario->Apellido }}, {{ $usuario->Nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col">
                                        <label for="accion">Accion:</label>
                                        <select class="select2 form-control" multiple="multiple" id="accion">
                                            <option value="1">Crear</option>
                                            <option value="3">Modificar</option>
                                            <option value="4">Eliminar</option>
                                            <option value="0">Login</option>
                                            <option value="-1">Logout</option>
                                        </select>
                                    </div>
                                    <div class="form-group col">
                                        <label for="modulo">Modulo:</label>
                                        <select class="select2 form-control" multiple="multiple" id="modulo">
                                            @foreach($modulos as $modulo)
                                                <option value="{{ $modulo->IdModulo }}">{{ $modulo->Label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col">
                                        <label for="log_search">Log:</label>
                                        <input type="text" name="log_search" id="log_search" autocomplete="off" class="form-control">
                                    </div>
                                    <div class="form-group col">
                                        <label for="d_fil">Fecha desde:</label>
                                        <div class="input-group date" id="desde_fil" data-target-input="nearest">
                                            <input type="text" id="d_fil" class="form-control datetimepicker-input" data-target="#desde_fil">
                                            <div class="input-group-append" data-target="#desde_fil" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col">
                                        <label for="h_fil">Fecha hasta:</label>
                                        <div class="input-group date" id="hasta_fil" data-target-input="nearest">
                                            <input type="text" id="h_fil" class="form-control datetimepicker-input" data-target="#hasta_fil">
                                            <div class="input-group-append" data-target="#hasta_fil" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="button" onclick="buscarLogs()" class="btn btn-default">Buscar <i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Apellido/Nombre</th>
                                                <th>Accion</th>
                                                <th>Log</th>
                                                <th>Modulo</th>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th>IP</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table_log">
                                        </tbody>
                                    </table>
                                    <div id="total_info" class="info-pagination"></div>
                                    <div class="row">
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
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('js/log.js') }}"></script>
@endsection
