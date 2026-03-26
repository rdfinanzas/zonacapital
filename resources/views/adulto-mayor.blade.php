@extends('layouts.main')

@section('content')
<style>
    .card-am { border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .card-am .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 20px; }
    .card-am .card-header h3 { font-size: 1.1rem; margin: 0; font-weight: 500; }
    .btn-am { border-radius: 6px; font-size: 0.875rem; padding: 6px 14px; }
    .btn-am i { font-size: 0.8rem; }
    .table-am { font-size: 0.875rem; }
    .table-am th { background: #f8f9fa; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; padding: 10px; }
    .table-am td { padding: 10px; vertical-align: middle; }
    .badge-am { font-size: 0.75rem; padding: 4px 10px; border-radius: 12px; color: white !important; font-weight: 600; }
    .filter-box { background: #f8f9fa; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
    .modal-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .modal-header .close { color: white; opacity: 0.8; }
    .paciente-info { background: #e8f4f8; border-radius: 6px; padding: 12px; margin-bottom: 15px; }
    .paciente-info label { font-size: 0.75rem; color: #666; margin-bottom: 2px; }
    .paciente-info .form-control { font-size: 0.875rem; font-weight: 500; }
    .section-title { font-size: 0.9rem; font-weight: 600; color: #667eea; margin: 15px 0 10px; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
    .badge-entregado { background-color: #28a745 !important; }
    .badge-no-entregado { background-color: #dc3545 !important; }
    .badge-pendiente { background-color: #ffc107 !important; color: #000 !important; }
    .badge-verificada { background-color: #28a745 !important; }
    .badge-rechazada { background-color: #dc3545 !important; }
    .receta-card { border: 1px solid #dee2e6; border-radius: 6px; padding: 10px; margin-bottom: 10px; background: #fafafa; }
    .receta-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .detalle-row { display: flex; align-items: center; padding: 6px 0; border-bottom: 1px solid #eee; }
    .detalle-row:last-child { border-bottom: none; }
    .tabs-container { border-bottom: 2px solid #dee2e6; margin-bottom: 15px; }
    .tabs-container .nav-link { border: none; padding: 10px 20px; color: #666; cursor: pointer; }
    .tabs-container .nav-link.active { border-bottom: 2px solid #667eea; color: #667eea; font-weight: 600; }
</style>

<section class="content-header py-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="m-0 text-primary">
                    <i class="fas fa-pills mr-2"></i>Programa Adulto Mayor
                </h4>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" id="btn_add" class="btn btn-primary btn-am">
                    <i class="fas fa-user-plus mr-1"></i> Nuevo Paciente
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Modal Eliminar -->
<div class="modal fade" id="modal_eliminar">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Confirmar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-0">¿Eliminar este registro?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" id="btn_eliminar_modal" class="btn btn-danger btn-sm">Sí, eliminar</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Paciente -->
<div class="modal fade" id="modal_paciente" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Inscribir Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_paciente_main">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="row_busqueda" class="mb-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            </div>
                            <input type="number" class="form-control" id="dni" placeholder="Ingrese DNI del paciente..." required>
                            <div class="input-group-append">
                                <button class="btn btn-primary" id="btn_buscar_dni" type="button">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="form_nuevo_paciente" style="display: none;">
                        <div class="alert alert-warning py-2">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <strong>Paciente no encontrado.</strong> Complete los datos para crearlo.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>DNI <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nuevo_dni" readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Apellido y Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nuevo_nombre" placeholder="Ej: PEREZ JUAN CARLOS">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label>Fecha Nacimiento</label>
                                <input type="date" class="form-control" id="nuevo_fecha_nac">
                            </div>
                            <div class="col-md-4">
                                <label>Sexo</label>
                                <select class="form-control" id="nuevo_sexo">
                                    <option value="0">Masculino</option>
                                    <option value="1">Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Celular</label>
                                <input type="text" class="form-control" id="nuevo_celular">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <label>Domicilio</label>
                                <input type="text" class="form-control" id="nuevo_domicilio">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button type="button" class="btn btn-success btn-sm" id="btn_guardar_nuevo_paciente">
                                    <i class="fas fa-save mr-1"></i> Guardar Paciente
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="btn_cancelar_nuevo">
                                    <i class="fas fa-times mr-1"></i> Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="form_paciente" style="display: none;">
                        <input type="hidden" id="paciente_id">
                        <input type="hidden" id="adulto_mayor_id">
                        
                        <div class="paciente-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Paciente</label>
                                    <input type="text" class="form-control bg-white" id="ApellidoNombre" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label>Fecha Nac.</label>
                                    <input type="text" class="form-control bg-white" id="FechaNacimiento" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label>Celular</label>
                                    <input type="text" class="form-control bg-white" id="Celular" readonly>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <label>Domicilio</label>
                                    <input type="text" class="form-control bg-white" id="Domicilio" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label>Servicio/Efector <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="servicio_id" id="servicio_id" required style="width: 100%;">
                                    <option value="">- Seleccionar -</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->idServicio }}">{{ $servicio->servicio }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Fecha Inscripción</label>
                                <input type="date" class="form-control" id="fecha_inscripcion" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label>Estado</label>
                                <select class="form-control" name="estado" id="estado">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="fallecido">Fallecido</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Observaciones</label>
                                <textarea class="form-control form-control-sm" name="observaciones_generales" id="observaciones_generales" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" id="footer_paciente" style="display: none;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_limpiar" class="btn btn-warning btn-sm">Limpiar</button>
                    <button type="submit" id="btn_submit_paciente" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                    <button type="button" id="btn_eliminar_paciente" class="btn btn-danger btn-sm" style="display: none;">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Receta -->
<div class="modal fade" id="modal_receta" data-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title"><i class="fas fa-prescription mr-2"></i>Nueva Receta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_receta">
                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                    <input type="hidden" id="receta_adulto_mayor_id">
                    <input type="hidden" id="receta_id">
                    
                    <div class="row">
                        <div class="col-md-3">
                            <label>Fecha Receta <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="receta_fecha" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label>Programa Financiador</label>
                            <select class="form-control" id="receta_programa_id">
                                <option value="">- Seleccionar -</option>
                                @foreach($programas as $programa)
                                    <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>Diagnóstico</label>
                            <input type="text" class="form-control" id="receta_diagnostico" placeholder="Diagnóstico médico">
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-capsules mr-2"></i>Medicamentos Recetados
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <select class="form-control form-control-sm select2" id="receta_medicamento_select" style="width: 100%;">
                                <option value="">- Medicamento -</option>
                                @foreach($medicamentos as $med)
                                    <option value="{{ $med->IdBien }}" data-nombre="{{ $med->Nombre }}">{{ $med->Nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" id="receta_dosis" placeholder="Dosis">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" id="receta_frecuencia" placeholder="Frecuencia">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="receta_obs" placeholder="Observaciones">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-success btn-sm btn-block" id="btn_add_receta_medicamento">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <table class="table table-sm table-bordered" id="tabla_receta_medicamentos">
                        <thead class="thead-light">
                            <tr>
                                <th>Medicamento</th>
                                <th>Dosis</th>
                                <th>Frecuencia</th>
                                <th>Entregado</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody_receta_medicamentos"></tbody>
                    </table>
                    <div id="sin_receta_medicamentos" class="text-center text-muted py-3 bg-light rounded">
                        <small><i class="fas fa-info-circle mr-1"></i>Agregue al menos un medicamento</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Guardar Receta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Verificación -->
<div class="modal fade" id="modal_verificar" style="z-index: 1070;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-check-circle mr-2"></i>Verificar Receta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_verificar">
                <div class="modal-body">
                    <input type="hidden" id="verificar_receta_id">
                    <div class="form-group">
                        <label>Estado de Verificación</label>
                        <select class="form-control" id="verificar_estado" required>
                            <option value="verificada">✓ Verificada</option>
                            <option value="rechazada">✗ Rechazada</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" id="verificar_obs" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Entrega -->
<div class="modal fade" id="modal_entrega" style="z-index: 1080;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-hand-holding-medical mr-2"></i>Registrar Entrega</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_entrega">
                <div class="modal-body">
                    <input type="hidden" id="entrega_receta_id">
                    <input type="hidden" id="entrega_detalle_id">
                    <div class="alert alert-info py-2">
                        <strong id="entrega_medicamento_nombre"></strong>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Fecha Entrega</label>
                            <input type="date" class="form-control form-control-sm" id="entrega_fecha" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label>Cantidad</label>
                            <input type="number" class="form-control form-control-sm" id="entrega_cantidad" value="1" min="1">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label>Observaciones</label>
                        <textarea class="form-control form-control-sm" id="entrega_observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm">Registrar Entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambio Medicamento -->
<div class="modal fade" id="modal_cambio" style="z-index: 1090;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exchange-alt mr-2"></i>Cambiar Medicamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_cambio">
                <div class="modal-body">
                    <input type="hidden" id="cambio_detalle_original_id">
                    <div class="alert alert-warning py-2">
                        <small>El medicamento original será marcado como no entregado</small>
                    </div>
                    <div class="form-group">
                        <label>Nuevo Medicamento <span class="text-danger">*</span></label>
                        <select class="form-control" id="cambio_bien_id" required>
                            <option value="">- Seleccionar -</option>
                            @foreach($medicamentos as $med)
                                <option value="{{ $med->IdBien }}">{{ $med->Nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Dosis</label>
                            <input type="text" class="form-control form-control-sm" id="cambio_dosis">
                        </div>
                        <div class="col-md-6">
                            <label>Frecuencia</label>
                            <input type="text" class="form-control form-control-sm" id="cambio_frecuencia">
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label>Motivo del Cambio</label>
                        <select class="form-control" id="cambio_motivo" required>
                            <option value="">- Seleccionar -</option>
                            <option value="faltante_stock">Faltante de stock</option>
                            <option value="indicacion_medica">Indicación médica</option>
                            <option value="efecto_adverso">Efecto adverso</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control form-control-sm" id="cambio_obs" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm">Confirmar Cambio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="filter-box">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label class="small text-muted">Servicio</label>
                    <select class="form-control form-control-sm" id="filtro_servicio">
                        <option value="0">Todos</option>
                        @foreach($servicios as $servicio)
                            <option value="{{ $servicio->idServicio }}">{{ $servicio->servicio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">Estado</label>
                    <select class="form-control form-control-sm" id="filtro_estado">
                        <option value="">Todos</option>
                        <option value="activo" selected>Activos</option>
                        <option value="inactivo">Inactivos</option>
                        <option value="fallecido">Fallecidos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">DNI</label>
                    <input type="text" class="form-control form-control-sm" id="filtro_dni" placeholder="Buscar...">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">Paciente</label>
                    <input type="text" class="form-control form-control-sm" id="filtro_nombre" placeholder="Buscar...">
                </div>
                <div class="col-md-3 text-right">
                    <button type="button" class="btn btn-primary btn-sm btn-am" onclick="refrescarTabla()">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                    <button type="button" class="btn btn-success btn-sm btn-am" onclick="exportarExcel()">
                        <i class="fas fa-file-excel mr-1"></i> Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="card card-am">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Pacientes Inscriptos</h3>
                <span class="badge badge-light" id="total_registros">0 registros</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-am table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="80">Servicio</th>
                                <th width="90">DNI</th>
                                <th>Paciente</th>
                                <th width="100">Fecha Insc.</th>
                                <th width="100">Última Receta</th>
                                <th width="100">Verificación</th>
                                <th width="100">Entrega</th>
                                <th width="70">Estado</th>
                                <th width="140">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="table_data">
                            <tr><td colspan="9" class="text-center text-muted py-4">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer py-2">
                <div class="row">
                    <div class="col-md-2" id="page-selection_num_page"></div>
                    <div class="col-md-10" id="page-selection"></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/serializejson-polyfill.js') }}"></script>
<script>
    window.laravelRoutes = window.laravelRoutes || {};
    window.laravelRoutes.adultoMayorExportar = "{{ route('adulto-mayor.exportar') }}";
    window.laravelRoutes.adultoMayorStoreReceta = "{{ route('adulto-mayor.receta.store') }}";
    window.laravelRoutes.adultoMayorVerificar = "{{ route('adulto-mayor.receta.verificar', ['id' => ':id']) }}";
    window.laravelRoutes.adultoMayorCambiarMedicamento = "{{ route('adulto-mayor.cambiar-medicamento') }}";
</script>
<script src="{{ asset('js/adulto-mayor.js') }}"></script>
@endpush
