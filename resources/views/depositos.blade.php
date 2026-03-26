@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Depósitos</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modal_eliminar">
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
                <div class="col-md-12">
                    <div class="card card-primary" id="card_form">
                        <div class="card-header">
                            <h3 class="card-title">Formulario</h3>
                        </div>
                        <form role="form" id="form_main">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="deposito">Depósito:</label>
                                        <input type="text" class="form-control" required name="deposito" id="deposito">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="resp">Responsables:</label>
                                        <select class="form-control select2" id="resp" multiple>
                                            @foreach($responsables as $responsable)
                                                <option value="{{ $responsable->idEmpleado }}">{{ $responsable->Apellido }}, {{ $responsable->Nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" id="btn_submit" class="btn btn-primary">Guardar <i class="fas fa-save"></i></button>
                                <button type="button" id="btn_limpiar" class="btn btn-warning">Limpiar <i class="fas fa-times"></i></button>
                                <button type="button" id="btn_eliminar" class="btn btn-danger">Eliminar <i class="fa fa-trash"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Lista de depósitos</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Depósito</th>
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
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('js/depositos.js') }}"></script>
@endsection
