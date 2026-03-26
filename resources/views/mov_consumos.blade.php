@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Movimiento de bienes de consumo</h1>
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
                    <p>¿Está seguro que desea anular este registro?</p>
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
                        <form id="form_main">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6" id="cont_form">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="tip_mov">Tipo movimiento:</label>
                                                <select name="tip_mov" id="tip_mov" required class="form-control">
                                                    <option value="">-</option>
                                                    <option value="0">Egreso</option>
                                                    <option value="2">Ajuste</option>
                                                    <option value="5">Ingreso (lavadero)</option>
                                                    <option value="7">Baja (costureria)</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="f_mov">Fecha/Hora:</label>
                                                <div class="input-group date" id="fecha_mov" data-target-input="nearest">
                                                    <input type="text" id="f_mov" required class="form-control datetimepicker-input" data-target="#fecha_mov">
                                                    <div class="input-group-append" data-target="#fecha_mov" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="dep_ori" id="label_ori">Depósito de origen:</label>
                                                <select class="select2 form-control" name="dep_ori" required id="dep_ori">
                                                    <option value="" selected>-SELECCIONAR-</option>
                                                    @foreach($depositos as $deposito)
                                                        <option value="{{ $deposito->IdDeposito }}">{{ $deposito->Deposito }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="tipo_desti" id="label_tipo">Tipo de destino:</label>
                                                <select class="form-control" name="tipo_desti" required id="tipo_desti">
                                                    <option value="-1" selected>-SELECCIONAR-</option>
                                                    <option value="2">Bien inventariable</option>
                                                    <option value="3">Depósito</option>
                                                    <option value="0">Unidad organizativa</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6" style="display: none" id="cont_dep_destino">
                                                <label for="depo_dest" id="label_tipo">Depósito destino:</label>
                                                <select class="form-control select2" required name="depo_dest" id="depo_dest">
                                                    <option value="" selected>-SELECCIONAR-</option>
                                                    @foreach($depositos as $deposito)
                                                        <option value="{{ $deposito->IdDeposito }}">{{ $deposito->Deposito }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 border-left">
                                        <h5>Bienes</h5>
                                        <div class="row border-top">
                                            <div class="form-group col-md-6">
                                                <input type="text" name="bien" placeholder="Buscar bien.." id="bien" autocomplete="off" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row border-top pt-2">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>Bien</th>
                                                        <th>Cantidad</th>
                                                        <th>Stock</th>
                                                        <th>Ultimo prec.</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabla_bienes"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div id="cont_btn">
                                    <button type="submit" id="btn_submit" class="btn btn-primary">Guardar <i class="fas fa-save"></i></button>
                                    <button type="button" id="btn_limpiar" class="btn btn-warning">Limpiar <i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Lista de movimientos</h3>
                        </div>
                        <div class="card-body">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Filtros</h3>
                                </div>
                                <form id="form_filter" action="javascript:refrescarTabla()">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col">
                                                <label for="num_fil">Nº mov:</label>
                                                <input type="text" name="num_fil" id="num_fil" autocomplete="off" class="form-control">
                                            </div>
                                            <div class="form-group col">
                                                <label for="t_mov_fil">Tipo mov.:</label>
                                                <select name="t_mov_fil" class="form-control">
                                                    <option value="-1">-</option>
                                                    <option value="1">Egreso</option>
                                                    <option value="4">Ajuste</option>
                                                    <option value="5">Ingreso</option>
                                                </select>
                                            </div>
                                            <div class="form-group col">
                                                <label for="depo_fil">Depósito:</label>
                                                <select class="form-control select2" name="depo_fil" id="depo_fil">
                                                    <option value="-1" selected>-</option>
                                                    @foreach($depositos as $deposito)
                                                        <option value="{{ $deposito->IdDeposito }}">{{ $deposito->Deposito }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">Buscar <i class="fas fa-search"></i></button>
                                    </div>
                                </form>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nº mov</th>
                                            <th>Tipo</th>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_data"></tbody>
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
    </section>
@endsection

@section('js')
    <script src="{{ asset('js/mov_consumos.js') }}"></script>
@endsection
