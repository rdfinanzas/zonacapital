@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Órdenes de Compra</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary" id="card_form">
                        <div class="card-header">
                            <h3 class="card-title">Formulario</h3>
                        </div>
                        <form role="form" id="form_main">
                            <input type="hidden" id="IdOrdenCompra" value="0">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="Cuenta_Id">Cuenta:</label>
                                    <select class="form-control" id="Cuenta_Id">
                                        <option value="">-SELECCIONAR-</option>
                                        @foreach($cuentas as $cuenta)
                                            <option value="{{ $cuenta->IdCuenta }}">{{ $cuenta->Nombre_Cuenta }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Estado_Id">Estado:</label>
                                    <select class="form-control" id="Estado_Id">
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->IdOrdenCompraEstado }}">{{ $estado->Estado }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Autorizado">Autorizado:</label>
                                    <select class="form-control" id="Autorizado">
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Plazo">Plazo:</label>
                                    <input type="text" class="form-control" id="Plazo" placeholder="Días de entrega">
                                </div>
                                <div class="form-group">
                                    <label for="Lugar">Lugar de entrega:</label>
                                    <input type="text" class="form-control" id="Lugar">
                                </div>
                                <div class="form-group">
                                    <label for="ContraEntrega">Contra entrega:</label>
                                    <select class="form-control" id="ContraEntrega">
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Obs">Observación:</label>
                                    <textarea class="form-control" id="Obs" rows="3"></textarea>
                                </div>

                                <hr>
                                <h5>Bienes de la Orden</h5>
                                <div id="bienes_orden_container">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" id="btn_submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button type="button" id="btn_limpiar" class="btn btn-warning">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                                <button type="button" id="btn_eliminar" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Órdenes de Compra</h3>
                        </div>
                        <div class="card-body">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Filtros</h3>
                                </div>
                                <form action="javascript:refrescarTabla()">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <label for="BuscarNumero">Número:</label>
                                                <input type="text" name="BuscarNumero" id="BuscarNumero" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="BuscarProveedor">Proveedor:</label>
                                                <select class="form-control select2" name="BuscarProveedor" id="BuscarProveedor">
                                                    <option value="">-TODOS-</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="Estado_Id">Estado:</label>
                                                <select class="form-control" name="Estado_Id" id="Estado_Id">
                                                    <option value="">-TODOS-</option>
                                                    @foreach($estados as $estado)
                                                        <option value="{{ $estado->IdOrdenCompraEstado }}">{{ $estado->Estado }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2" style="padding-top: 30px">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Buscar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="table-responsive p-0">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nº</th>
                                            <th>Proveedor</th>
                                            <th>Cuenta</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th width="120">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_data">
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
    </section>

    <div class="modal fade" id="modal_seleccionar_cotizaciones" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Cotizaciones</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="seleccionar_pi_id">
                    <div id="seleccionar_cotizaciones_content">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_seleccionar_cotizaciones" class="btn btn-primary">Seleccionar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_ver_detalle" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de la Orden de Compra</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detalle_orden_content">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('js/ordenes-compra.js') }}"></script>
@endsection
