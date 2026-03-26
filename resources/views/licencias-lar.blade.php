@extends('layouts.app')

@section('content')
    <section class="content-header py-2">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h4 class="mb-0"><i class="fas fa-calendar-check text-success"></i> Licencias Anuales por Remuneración
                        (LAR)</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modal_eliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-danger">
                <div class="modal-header">
                    <h4 class="modal-title text-white"><i class="fas fa-exclamation-triangle"></i> Atención!</h4>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-white mb-0">¿Está seguro que desea eliminar este registro?</p>
                </div>
                <div class="modal-footer bg-danger">
                    <button type="button" id="btn_eliminar_modal" class="btn btn-light">Eliminar</button>
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Columna izquierda - Formulario LAR -->
                <div class="col-md-6">
                    <div class="card card-success card-outline" id="card_form">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit"></i> Formulario de LAR</h3>

                            <!-- Menú Contextual Parámetros LAR -->
                            <div class="card-tools">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false" id="btn_parametros_menu" disabled>
                                        <i class="fas fa-cogs"></i> Parámetros LAR
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" style="width: 350px; padding: 15px;">
                                        <h6 class="dropdown-header"><i class="fas fa-cogs text-success"></i> Configurar
                                            Parámetros LAR</h6>
                                        <div class="dropdown-divider"></div>

                                        <!-- Formulario dentro del dropdown -->
                                        <form id="form_param_dropdown"
                                            onsubmit="event.preventDefault(); createParamFromDropdown();">
                                            <div class="mb-2">
                                                <label class="small fw-bold">Año:</label>
                                                <input type="number" id="anio_dropdown" class="form-control form-control-sm"
                                                    placeholder="Ej: 2024" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="small fw-bold">Total días:</label>
                                                <input type="number" id="total_dropdown"
                                                    class="form-control form-control-sm" placeholder="Ej: 20" required>
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-plus"></i> Agregar
                                                </button>
                                            </div>
                                        </form>

                                        <div class="dropdown-divider"></div>
                                        <h6 class="dropdown-header">Parámetros Configurados</h6>
                                        <div style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="small">Año</th>
                                                        <th class="small text-center">Tom.</th>
                                                        <th class="small text-center">Pend.</th>
                                                        <th class="small text-center">Tot.</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="table_param_dropdown">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted small py-2">Seleccione
                                                            un personal</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form role="form" id="form_main">
                            <div class="card-body">
                                <!-- Fecha -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="fechaOrden"><i class="fas fa-calendar text-success"></i> Fecha:</label>
                                        <input type="date" id="fechaOrden" name="fechaOrden" value="{{ date('Y-m-d') }}"
                                            required class="form-control" />
                                    </div>
                                </div>
                                <!-- Personal -->
                                <div class="form-group">
                                    <label for="personal"><i class="fas fa-user text-success"></i> Personal:</label>
                                    <select class="form-control select2" required id="personal" name="personal">
                                        <option value="" selected disabled>- SELECCIONAR PERSONAL -</option>
                                        @foreach($personal as $p)
                                            <option value="{{ $p->Legajo }}" data-legajo="{{ $p->Legajo }}"
                                                data-dni="{{ $p->DNI }}">
                                                {{ $p->Apellido }}, {{ $p->Nombre }} (Leg: {{ $p->Legajo }}-{{ $p->DNI }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 p-2 bg-light rounded border" id="info_personal" style="display: none;">
                                    </div>
                                </div>



                                <!-- Info Alert -->
                                <div class="alert alert-info py-2" role="alert">
                                    <small><i class="fas fa-info-circle"></i> Para ver los días disponibles, coloque el año
                                        y presione "Traer días"</small>
                                </div>

                                <!-- Año y Botón Traer Días -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="anio"><i class="fas fa-calendar-alt text-success"></i> Año:</label>
                                        <input type="number" name="anio" id="anio" required class="form-control"
                                            placeholder="Ej: 2024" />
                                    </div>
                                    <div class="form-group col-md-6 d-flex align-items-end">
                                        <button type="button" onclick="getDiasLar()" id="btn_traer_dias" disabled
                                            class="btn btn-primary w-100">
                                            <i class="fas fa-sync-alt"></i> Traer días
                                        </button>
                                    </div>
                                </div>

                                <!-- Info LAR -->
                                <div class="alert alert-warning py-2" id="info_lar" style="display: none;"></div>

                                <!-- Días, Corridos y Calcular -->
                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <label for="dias"><i class="fas fa-hashtag text-success"></i> Días:</label>
                                        <input type="number" name="dias" id="dias" required class="form-control"
                                            placeholder="Cant. días" />
                                    </div>
                                    <div class="form-group col-md-2 d-flex align-items-end">
                                        <div class="icheck-primary">
                                            <input type="checkbox" id="corridos" name="corridos">
                                            <label for="corridos">Corridos</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="d"><i class="fas fa-calendar-day text-success"></i> Desde:</label>
                                         <input type="date" id="d" value="{{ date('Y-m-d') }}" required class="form-control" />
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="h"><i class="fas fa-calendar-check text-success"></i> Hasta:</label>
                                        <input type="date" id="h" required class="form-control" />
                                    </div>
                                </div>

                                <!-- Fechas Desde/Hasta -->
                                <div class="row">

                                    <div class="form-group col-md-12 mb-0">
                                        <label for="NumDispPoster" class="small">
                                            Disposición:
                                            <button type="button" class="btn btn-link btn-sm p-0 ml-1"
                                                onclick="DisposicionesModule.abrirModalNuevaDisposicion()" title="Agregar nueva disposición">
                                                <i class="fas fa-plus-circle text-success"></i>
                                            </button>
                                        </label>
                                        <select class="form-control form-control-sm select2" name="NumDispPoster"
                                            id="NumDispPoster">
                                            <option value="" selected>- SELECCIONAR -</option>
                                        </select>

                                    </div>
                                </div>

                                <!-- Postergación -->
                                <div class="card border-light bg-light mt-2 div_poster" style="display: none;">
                                    <div class="card-header py-1 bg-light">
                                        <small class="font-weight-bold text-muted"><i class="fas fa-redo"></i> Postergación
                                            (opcional)</small>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="row">


                                            <div class="form-group col-md-6 mb-0">
                                                <label for="MotPoster" class="small">Motivo:</label>
                                                <select class="form-control form-control-sm" name="MotPoster"
                                                    id="MotPoster">
                                                    <option value="" selected>- SELECCIONAR -</option>
                                                    <option value="1">SALUD</option>
                                                    <option value="2">SERVICIO</option>
                                                    <option value="3">MATERNIDAD</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Observación -->
                                <div class="form-group mt-2">
                                    <label for="obs"><i class="fas fa-sticky-note text-success"></i> Observación:</label>
                                    <textarea class="form-control" name="obs" id="obs" rows="2"
                                        placeholder="Observaciones opcionales..."></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" id="btn_submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Guardar LAR
                                </button>
                                <button type="button" id="btn_limpiar" class="btn btn-outline-warning">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                                <button type="button" id="btn_eliminar" class="btn btn-outline-danger float-right">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Columna derecha - Historial de LAR -->
                <div class="col-md-6">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Historial de LAR</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="thead-light sticky-top">
                                        <tr>
                                            <th class="text-center" style="width: 10%;">Días</th>
                                            <th style="width: 18%;">Fecha Inicio</th>
                                            <th style="width: 18%;">Fecha Fin</th>
                                            <th class="text-center" style="width: 10%;">Año</th>
                                            <th style="width: 24%;">Usuario</th>
                                            <th class="text-center" style="width: 20%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_data_lar">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                                Seleccione un personal para ver sus LAR
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Componente Blade del Modal de Disposiciones -->
    <x-disposiciones-component
        :is-modal="true"
        target-dropdown="NumDispPoster"
        :show-stats="false" />
@endsection

@push('styles')
    <style>
        /* Fix para select2 dropdown position */
        .select2-dropdown {
            z-index: 9999 !important;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush

@section('js')
    <script src="{{ asset('js/licencias-lar.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/disposiciones.js') }}?v={{ time() }}"></script>
    <script>
        DisposicionesModule.init({
            listar: '{{ route('disposiciones.listar') }}',
            store: '{{ route('disposiciones.store') }}',
            update: '{{ route('disposiciones.update', ':id') }}',
            destroy: '{{ route('disposiciones.destroy', ':id') }}',
            proximoNumero: '{{ route('disposiciones.proximo-numero') }}',
            estadisticas: '{{ route('disposiciones.estadisticas') }}'
        }, { isModal: true, targetDropdown: 'NumDispPoster' });

        DisposicionesModule.cargarDropdown('NumDispPoster');
    </script>
@endsection