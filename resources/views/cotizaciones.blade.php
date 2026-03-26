@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cotizaciones</h1>
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
                            <h3 class="card-title">Gestión de Cotizaciones</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pedidoInternoId">Pedido Interno ID:</label>
                                        <input type="number" class="form-control" id="pedidoInternoId" placeholder="Ingrese ID">
                                    </div>
                                    <button type="button" id="btnCargarPedido" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cargar Pedido
                                    </button>
                                </div>
                                <div class="col-md-8">
                                    <div id="infoPedido" style="display: none;">
                                        <div class="alert alert-info">
                                            <h5>Información del Pedido</h5>
                                            <p><strong>Solicitante:</strong> <span id="servicioSolicitante"></span></p>
                                            <p><strong>Destino:</strong> <span id="servicioDestino"></span></p>
                                            <p><strong>Estado:</strong> <span id="estadoPedido"></span></p>
                                            <p><strong>Creado:</strong> <span id="fechaCreacion"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="seccionCotizaciones" style="display: none;">
                <div class="col-md-12">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Bienes a Cotizar</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Bien</th>
                                        <th>Cantidad</th>
                                        <th>Detalle/Obs</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bienesTable">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="seccionFormularioCoti" style="display: none;">
                <div class="col-md-12">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Agregar Cotización</h3>
                        </div>
                        <div class="card-body">
                            <form id="formCotizacion">
                                <input type="hidden" id="cotizacionId" value="0">
                                <input type="hidden" id="pedidoInternoXBienId">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="proveedorId">Proveedor:</label>
                                            <select class="form-control select2" id="proveedorId" required>
                                                <option value="">-SELECCIONAR-</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="cantidad">Cantidad:</label>
                                            <input type="number" class="form-control" id="cantidad" required min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="precio">Precio ($):</label>
                                            <input type="number" class="form-control" id="precio" required min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Subtotal:</label>
                                            <input type="text" class="form-control" id="subtotal" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2" style="padding-top: 30px;">
                                        <button type="submit" id="btnGuardarCoti" class="btn btn-success">
                                            <i class="fas fa-save"></i> Guardar
                                        </button>
                                        <button type="button" id="btnCancelarCoti" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="proforma">Proforma:</label>
                                            <input type="text" class="form-control" id="proforma" placeholder="Nro. de proforma">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="seccionCotizacionesDetalle" style="display: none;">
                <div class="col-md-12">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Cotizaciones Cargadas</h3>
                        </div>
                        <div class="card-body">
                            <div id="contenedorCotizaciones">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('js/cotizaciones.js') }}"></script>
@endsection
