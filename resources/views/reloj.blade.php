@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Relojes</h1>
                </div>
            </div>
        </div>
    </section>

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

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Lista de relojes</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Reloj</th>
                                            <th>Servicio</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_data">
                                    </tbody>
                                </table>
                            </div>
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
                <div class="col-md-6">
                    <div class="card card-primary" id="card_form">
                        <div class="card-header">
                            <h3 class="card-title">Formulario</h3>
                        </div>
                        <form role="form" id="form_main">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="reloj">Reloj</label>
                                            <input type="text" class="form-control" required name="Reloj" id="reloj">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="servicio">Servicio:</label>
                                        <select class="form-control select2" name="ServicioReloj_Id" id="servicio_id">
                                            <option value="">-NINGUNO-</option>
                                            @foreach($servicios as $servicio)
                                                <option value="{{$servicio->idServicio}}">{{$servicio->servicio}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="tipo">Tipo:</label>
                                        <select class="form-control" name="tipo" id="tipo">
                                            <option value="">-NINGUNO-</option>
                                            <option value="1">Manual</option>
                                            <option value="2">Automatico</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ip">IP</label>
                                            <input type="text" class="form-control" name="ip" id="ip">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="user_admin">User Admin</label>
                                            <input type="text" class="form-control" name="user_admin" id="user_admin">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="text" class="form-control" name="password" id="password">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="observacion">Observación</label>
                                            <textarea class="form-control" name="Observacion" id="observacion" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                @if($permisos['C'] == 1 || $permisos['U'] == 1)
                                    <button type="submit" id="btn_submit" class="btn btn-primary">Guardar <i class="fas fa-save"></i></button>
                                @endif
                                @if($permisos['C'] == 1)
                                    <button type="button" id="btn_limpiar" class="btn btn-warning">Limpiar <i class="fas fa-times"></i></button>
                                @endif
                                @if($permisos['D'] == 1)
                                    <button type="button" id="btn_eliminar" class="btn btn-danger">Eliminar <i class="fa fa-trash"></i></button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <input type="hidden" id="permisos" value="{{ json_encode($permisos) }}">
@endsection

@section('js')
    <script src="{{ asset('js/reloj.js') }}"></script>
@endsection
