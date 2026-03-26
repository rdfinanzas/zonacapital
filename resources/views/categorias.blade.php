@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Carga de categorías</h1>
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
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Categorías</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <button type="button" onclick="addTree(0,[],0)" class="btn btn-success btn-xs"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                    <div id="div_tree">
                                        @include('partials.categorias_tree', ['categorias' => $categorias, 'parent' => 0, 'level' => 0])
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <form role="form" id="form_main">
                                        <h3 id="titulo_form">Editar</h3>
                                        <div class="row">
                                            <div class="form-group col">
                                                <label>Padre:</label>
                                                <span id="padre"></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col">
                                                <label for="mover">Mover:</label>
                                                <select class="form-control" disabled name="mover" id="mover">
                                                    <option value="0" selected>-SELECCIONAR-</option>
                                                    @foreach($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->categoria }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col">
                                                <label for="categoria">Categoria:</label>
                                                <input type="text" class="form-control" required name="categoria" id="categoria">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col">
                                                <label for="obs">Observación:</label>
                                                <textarea class="form-control" name="obs" id="obs"></textarea>
                                            </div>
                                        </div>
                                        <button type="submit" id="btn_submit" disabled class="btn btn-primary">Guardar <i class="fas fa-save"></i></button>
                                        <button type="button" id="btn_limpiar" class="btn btn-warning">Limpiar <i class="fas fa-times"></i></button>
                                    </form>
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
    <script src="{{ asset('js/categorias.js') }}"></script>
@endsection
