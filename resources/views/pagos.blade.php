@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Pagos</h1>
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
                            <input type="hidden" id="IdPago" value="0">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="NroCD">Número CD:</label>
                                    <input type="text" class="form-control" id="NroCD" required>
                                </div>
                                <div class="form-group">
                                    <label for="FechaCheque">Fecha Cheque:</label>
                                    <input type="date" class="form-control" id="FechaCheque">
                                </div>
                                <div class="form-group">
                                    <label for="Disposicion">Disposición:</label>
                                    <input type="text" class="form-control" id="Disposicion">
                                </div>
                                <div class="form-group">
                                    <label for="Cheque">Cheque:</label>
                                    <input type="text" class="form-control" id="Cheque">
                                </div>
                                <div class="form-group">
                                    <label for="Factura">Factura:</label>
                                    <input type="text" class="form-control" id="Factura">
                                </div>
                                <div class="form-group">
                                    <label for="NumLote">Número Lote:</label>
                                    <input type="text" class="form-control" id="NumLote">
                                </div>
                                <div class="form-group">
                                    <label for="Destino">Destino:</label>
                                    <input type="text" class="form-control" id="Destino">
                                </div>
                                <div class="form-group">
                                    <label for="Concepto">Concepto:</label>
                                    <input type="text" class="form-control" id="Concepto">
                                </div>
                                <div class="form-group">
                                    <label for="Proveedor_Id">Proveedor:</label>
                                    <select class="form-control select2" id="Proveedor_Id">
                                        <option value="">-SELECCIONAR-</option>
                                    </select>
                                </div>
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
                                    <label for="Servicio_Id">Servicio:</label>
                                    <select class="form-control select2" id="Servicio_Id">
                                        <option value="">-SELECCIONAR-</option>
                                        @foreach($servicios as $servicio)
                                            <option value="{{ $servicio->IdServicio }}">{{ $servicio->Servicio }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Servicio_Otro">Servicio Otro:</label>
                                    <input type="text" class="form-control" id="Servicio_Otro">
                                </div>
                                <div class="form-group">
                                    <label for="Estado_Id">Estado:</label>
                                    <select class="form-control" id="Estado_Id">
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->IdPagoEstado }}">{{ $estado->Estado }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="RetencionDGR">Retención IGR:</label>
                                            <input type="number" step="0.01" class="form-control" id="RetencionDGR" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="RetencionDGI">Retención IVA:</label>
                                            <input type="number" step="0.01" class="form-control" id="RetencionDGI" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="Total">Total:</label>
                                    <input type="number" step="0.01" class="form-control" id="Total" value="0">
                                </div>

                                <hr>
                                <h5>Bienes del Pago</h5>
                                <div id="bienes_pago_container">
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
                            <h3 class="card-title">Listado de Pagos</h3>
                        </div>
                        <div class="card-body">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Filtros</h3>
                                </div>
                                <form action="javascript:refrescarTabla()">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-2">
                                                <label for="BuscarNumero">Número:</label>
                                                <input type="text" name="BuscarNumero" id="BuscarNumero" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="BuscarCuenta">Cuenta:</label>
                                                <select class="form-control" name="BuscarCuenta" id="BuscarCuenta">
                                                    <option value="">-TODOS-</option>
                                                    @foreach($cuentas as $cuenta)
                                                        <option value="{{ $cuenta->IdCuenta }}">{{ $cuenta->Nombre_Cuenta }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="BuscarProveedor">Proveedor:</label>
                                                <select class="form-control" name="BuscarProveedor" id="BuscarProveedor">
                                                    <option value="">-TODOS-</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="BuscarEstado">Estado:</label>
                                                <select class="form-control" name="BuscarEstado" id="BuscarEstado">
                                                    <option value="">-TODOS-</option>
                                                    @foreach($estados as $estado)
                                                        <option value="{{ $estado->IdPagoEstado }}">{{ $estado->Estado }}</option>
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
                                            <th>Nº CD</th>
                                            <th>Fecha</th>
                                            <th>Proveedor</th>
                                            <th>Cuenta</th>
                                            <th>Estado</th>
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

    <div class="modal fade" id="modal_ver_detalle" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle del Pago</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detalle_pago_content">
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
    <script src="{{ asset('js/pagos.js') }}"></script>
@endsection
