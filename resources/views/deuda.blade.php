@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Deudas y Saldo</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Sección de Ingresos -->
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ingresos Contables</h3>
                        </div>
                        <div class="card-body">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4 class="card-title">Nuevo Ingreso</h4>
                                </div>
                                <form id="form_ingreso">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="ingreso_fecha">Fecha:</label>
                                            <input type="date" class="form-control" id="ingreso_fecha" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ingreso_cuenta">Cuenta:</label>
                                            <select class="form-control" id="ingreso_cuenta" required>
                                                <option value="">-SELECCIONAR-</option>
                                                @foreach($cuentas as $cuenta)
                                                    <option value="{{ $cuenta->IdCuenta }}">{{ $cuenta->Nombre_Cuenta }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ingreso_monto">Monto:</label>
                                            <input type="number" step="0.01" class="form-control" id="ingreso_monto" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ingreso_porcentaje">Porcentaje:</label>
                                            <input type="number" step="0.01" class="form-control" id="ingreso_porcentaje" value="100">
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">Agregar Ingreso</button>
                                    </div>
                                </form>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Listado de Ingresos</h4>
                                    <div class="float-right">
                                        <select class="form-control" id="anio_ingresos" style="width: 100px;">
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Cuenta</th>
                                                    <th>Monto</th>
                                                    <th>Porcentaje</th>
                                                    <th>Reserva</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla_ingresos">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Saldos y Reservas -->
                <div class="col-md-6">
                    <!-- Saldo Inicial -->
                    <div class="card card-info mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Saldo Inicial</h3>
                        </div>
                        <div class="card-body">
                            <form id="form_saldo_inicial">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="si_anio">Año:</label>
                                            <input type="number" class="form-control" id="si_anio" value="{{ date('Y') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="si_cuenta">Cuenta:</label>
                                            <select class="form-control" id="si_cuenta" required>
                                                <option value="">-SELECCIONAR-</option>
                                                @foreach($cuentas as $cuenta)
                                                    <option value="{{ $cuenta->IdCuenta }}">{{ $cuenta->Nombre_Cuenta }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="si_saldo">Saldo:</label>
                                            <input type="number" step="0.01" class="form-control" id="si_saldo" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-info">Agregar Saldo Inicial</button>
                            </form>
                        </div>
                    </div>

                    <!-- Reservas -->
                    <div class="card card-warning mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Reservas</h3>
                        </div>
                        <div class="card-body">
                            <form id="form_reserva" class="mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="reserva_cuenta">Cuenta:</label>
                                            <select class="form-control" id="reserva_cuenta" required>
                                                <option value="">-SELECCIONAR-</option>
                                                @foreach($cuentas as $cuenta)
                                                    <option value="{{ $cuenta->IdCuenta }}">{{ $cuenta->Nombre_Cuenta }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="reserva_monto">Monto:</label>
                                            <input type="number" step="0.01" class="form-control" id="reserva_monto" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="reserva_detalle">Detalle:</label>
                                            <input type="text" class="form-control" id="reserva_detalle" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-warning">Agregar Reserva</button>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Cuenta</th>
                                            <th>Monto</th>
                                            <th>Detalle</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_reservas">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Saldos Disponibles -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Saldos Disponibles</h3>
                        </div>
                        <div class="card-body">
                            <form id="form_saldo_disponible" class="mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sd_cuenta">Cuenta:</label>
                                            <select class="form-control" id="sd_cuenta" required>
                                                <option value="">-SELECCIONAR-</option>
                                                @foreach($cuentas as $cuenta)
                                                    <option value="{{ $cuenta->IdCuenta }}">{{ $cuenta->Nombre_Cuenta }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sd_saldo">Saldo:</label>
                                            <input type="number" step="0.01" class="form-control" id="sd_saldo" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Registrar Saldo</button>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Cuenta</th>
                                            <th>Saldo</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_saldos">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen Mensual -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Resumen Mensual de Ingresos</h3>
                            <div class="float-right">
                                <select class="form-control" id="anio_resumen" style="width: 100px;">
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Mes</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_resumen">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('js/deuda.js') }}"></script>
@endsection
