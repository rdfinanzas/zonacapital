@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Actas de Recepción</h1>
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
                            <input type="hidden" id="IdUnico" value="0">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="FechaEmicion">Fecha Emisión:</label>
                                    <input type="date" class="form-control" id="FechaEmicion" required>
                                </div>
                                <div class="form-group">
                                    <label for="Numero">Número Acta:</label>
                                    <input type="text" class="form-control" id="Numero" required>
                                </div>
                                <div class="form-group">
                                    <label for="NumExp">Número Expediente:</label>
                                    <input type="text" class="form-control" id="NumExp">
                                </div>
                                <div class="form-group">
                                    <label for="TipoAc">Tipo Acta:</label>
                                    <select class="form-control" id="TipoAc">
                                        <option value="1">Compra</option>
                                        <option value="2">Otro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Estado_Id">Estado:</label>
                                    <select class="form-control" id="Estado_Id">
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->IdActaRecepcionEstado }}">{{ $estado->Estado }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="Proveedor_Id">Proveedor:</label>
                                    <select class="form-control select2" id="Proveedor_Id">
                                        <option value="">-SELECCIONAR-</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="NumProv">Número Prov:</label>
                                    <input type="text" class="form-control" id="NumProv">
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
                                    <label for="DescripActa">Descripción:</label>
                                    <textarea class="form-control" id="DescripActa" rows="3"></textarea>
                                </div>

                                <hr>
                                <h5>Bienes del Acta</h5>
                                <div id="bienes_acta_container">
                                    <div class="row bien-row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Buscar Bien:</label>
                                                <input type="text" class="form-control bien-buscar" placeholder="Buscar bien...">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Cantidad:</label>
                                                <input type="number" class="form-control bien-cantidad" value="1" min="1">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Remito:</label>
                                                <input type="text" class="form-control bien-remito">
                                            </div>
                                        </div>
                                        <div class="col-md-1" style="padding-top: 30px;">
                                            <button type="button" class="btn btn-danger btn-xs" onclick="eliminarBienRow(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-info" onclick="agregarBienRow()">
                                    <i class="fas fa-plus"></i> Agregar Bien
                                </button>
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
                            <h3 class="card-title">Listado de Actas</h3>
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
                                                <select class="form-control" name="BuscarProveedor" id="BuscarProveedor">
                                                    <option value="">-TODOS-</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="BuscarEstado">Estado:</label>
                                                <select class="form-control" name="BuscarEstado" id="BuscarEstado">
                                                    <option value="">-TODOS-</option>
                                                    @foreach($estados as $estado)
                                                        <option value="{{ $estado->IdActaRecepcionEstado }}">{{ $estado->Estado }}</option>
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
                                            <th>Exp.</th>
                                            <th>Proveedor</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle del Acta</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detalle_acta_content">
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
    <script src="{{ asset('js/actas-recepcion.js') }}"></script>
@endsection
