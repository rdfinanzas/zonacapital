@extends('layouts.app')

@section('css')
<style>
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
    .table-responsive {
        margin-top: 20px;
    }
    .btn-group-sm .btn {
        margin-right: 5px;
    }
    #form_orden_medica .row {
        margin-bottom: 15px;
    }

    /* Estilos para imageLoad */
    .image_load_cont {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        background-color: #f8f9fa;
        margin-top: 10px;
    }

    .preview {
        margin-bottom: 15px;
    }

    .image_empty {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .image_empty:hover {
        opacity: 0.7;
    }

    .image_empty svg {
        border: 2px dashed #6c757d;
        border-radius: 8px;
    }

    .image_preview {
        text-align: center;
    }

    .image_preview img {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .overlay_image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255,255,255,0.9);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        border-radius: 8px;
    }

    .text_error_image {
        margin-top: 10px;
        text-align: center;
    }

    .image_load_cont .row .col button {
        margin: 0 5px;
        min-width: 40px;
    }

    /* Estilos para los filtros */
    .card-header .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        color: #495057;
    }

    .card-header .form-control-sm,
    .card-header .form-select-sm {
        font-size: 0.875rem;
    }

    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }

    .d-flex.gap-2 .btn {
        white-space: nowrap;
    }


</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- Card header solo para la lista/filtros -->
                    <div class="card-header" id="card_header_filtros">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-1">
                                <label class="form-label">Año LAR</label>
                                <select id="anio_lar_filtro" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            {{-- <div class="col-md-1">
                                <label class="form-label">Año</label>
                                <select id="anio_filtro" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div> --}}
                            <div class="col-md-2">
                                <label class="form-label">Fecha Creación</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="date" id="fecha_desde" class="form-control form-control-sm" placeholder="Desde">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" id="fecha_hasta" class="form-control form-control-sm" placeholder="Hasta">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">DNI / Legajo</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="text" id="dni_filtro" class="form-control form-control-sm" placeholder="DNI">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" id="legajo_filtro" class="form-control form-control-sm" placeholder="Legajo">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Agente</label>
                                <input type="text" id="personal_filtro" class="form-control form-control-sm" placeholder="Nombre/Apellido">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Nº OM</label>
                                <input type="text" id="numero_om_filtro" class="form-control form-control-sm" placeholder="123">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Múltiples OM</label>
                                <input type="text" id="multiples_om_filtro" class="form-control form-control-sm" placeholder="123,456,789">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button id="btn-filtrar" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                    <button id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-eraser"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1 text-end">
                                <label class="form-label">&nbsp;</label>
                                <button id="btn_add" class="btn btn-primary btn-sm d-block w-100">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de la lista -->
                    <div class="card-body" id="panel_list">
                        <div class="table-responsive">
                            <table id="tabla-om" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th># OM</th>
                                        <th>Fecha</th>
                                        <th>Agente</th>
                                        <th>DNI</th>
                                        <th>Legajo</th>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Días</th>
                                        <th>Motivo</th>
                                        <th>Estado</th>
                                        <th>Creador</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table_data_om"></tbody>
                            </table>
                        </div>
                        <div id="paginacion-container" class="d-flex justify-content-between align-items-center mt-3">
                            <div class="dataTables_info">
                                <span id="info-paginacion"></span>
                            </div>
                            <div class="dataTables_paginate paging_simple_numbers">
                                <ul class="pagination" id="paginacion-controles"></ul>
                            </div>
                        </div>
                    </div>

                    <!-- Panel del formulario (sin header) -->
                    <div class="card-body d-none" id="panel_add">
                        <!-- Header específico del formulario -->
                        <div class="card-header bg-primary text-white mb-4">
                            <h5 class="mb-0">
                                <i class="fas fa-file-medical"></i> Nueva Orden Médica
                            </h5>
                        </div>

                        <div id="poster_alert" class="alert alert-warning" style="display:none">
                            Atención: la fecha Hasta cae en un año posterior al seleccionado.
                        </div>

                        <div class="mb-3">
                            <button id="btn_volver" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver
                            </button>
                        </div>

                            <form id="form_main">
                                <div class="row">
                                    <div class="col-9">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Agente <span class="text-danger">*</span></label>
                                                    {{-- @dd($personal[0]) --}}
                                                    <select id="personal_id" name="personal_id" class="form-select select2" required>
                                                        <option value="">-SELECCIONAR-</option>
                                                        @foreach ($personal as $p)
                                                            <option value="{{ $p->Legajo }}">{{ $p->Apellido }}, {{ $p->Nombre }}-{{ $p->DNI }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>Nº OM</label>
                                                    <input id="numero_om" name="numero_om" type="text" class="form-control" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Año</label>
                                                    <input id="anio" name="anio" type="number" class="form-control" value="{{ date('Y') }}" readonly>
                                                </div>
                                            </div>


                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Fecha</label>
                                                    <input id="fecha" name="fecha" type="date" class="form-control" value="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Motivo <span class="text-danger">*</span></label>
                                                    <select id="motivo_id" name="motivo_id" class="form-select select2" required>
                                                        <option value="">-SELECCIONAR-</option>
                                                        @isset($motivos)
                                                            @foreach ($motivos as $m)
                                                                <option value="{{ $m->IdMotivoLicencia }}">{{ $m->Motivo }}</option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>A Reconocimiento <span class="text-danger">*</span></label>
                                                    <select id="estado" name="estado" class="form-select" required>
                                                        <option value="">-SELECCIONAR-</option>
                                                        <option value="2">Pendiente envio</option>
                                                        <option value="3">Enviado</option>
                                                        <option value="1">Finalizado</option>
                                                        <option value="4">Anulado</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Días <span class="text-danger">*</span></label>
                                                    <input id="dias" name="dias" type="number" class="form-control" min="1" value="1" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">

                                        </div>

                                        <!-- Alerta de postergación -->
                                        <div class="row">
                                            <div class="col-12">
                                                <div id="poster_alert" class="alert alert-warning alert-dismissible" style="display: none;">
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Atención!</h5>
                                                    La fecha de inicio de la licencia es posterior al día de la fecha.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Desde <span class="text-danger">*</span></label>
                                                    <input id="d" name="d" type="date" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Hasta <span class="text-danger">*</span></label>
                                                    <input id="h" name="h" type="date" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2 pt-4">
                                                <div class="form-group">
                                                    <div class="icheck-danger d-inline">
                                                        <input type="checkbox" id="corridos" name="corridos" onclick="checkCorrido()">
                                                        <label for="corridos">Corridos</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                               {{-- <button type="button" id="btn_calc" class="btn btn-info btn-sm mr-2">
                                                        <i class="fas fa-calculator"></i> Calcular
                                                    </button> --}}

                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Certificado Médico</label>
                                                    <input id="certificado" name="certificado" type="hidden">
                                                    <input type="text" class="form-control" id="certificado_display" readonly style="background-color: #f8f9fa;">
                                                    <small class="form-text text-muted">Auto-generado</small>
                                                </div>
                                            </div>
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <label>Observaciones</label>
                                                    <textarea id="observacion" name="observacion" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Campos de postergación - inicialmente ocultos -->
                                        <div class="row div_poster" style="display:none">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Motivo postergación:</label>
                                                    <select class="form-select" name="poster" id="poster">
                                                        <option value="" selected>-SELECCIONAR-</option>
                                                        <option value="1">SALUD</option>
                                                        <option value="2">SERVICIO</option>
                                                        <option value="3">MATERNIDAD</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Disposición postergación:</label>
                                                    <select class="form-select select2" name="disp2" id="disp2">
                                                        <option value="">-SELECCIONAR-</option>
                                                        @isset($disposiciones)
                                                            @foreach($disposiciones as $d)
                                                                <option value="{{ $d->IdNumDisp }}">{{ $d->NumDisp }}{{ $d->AnioDisp ? '/'.$d->AnioDisp : '' }} - {{ $d->Descripcion }}</option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                            </div>
                                        </div>


                                        @isset($disposiciones)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="disposicion_id">
                                                        Disposición
                                                        <button type="button" class="btn btn-link btn-sm p-0 ml-2"
                                                            onclick="DisposicionesModule.abrirModalNuevaDisposicion()" title="Agregar nueva disposición">
                                                            <i class="fas fa-plus-circle text-success"></i>
                                                        </button>
                                                    </label>
                                                    <select id="disposicion_id" name="disposicion_id" class="form-select select2">
                                                        <option value="">-SELECCIONAR-</option>
                                                        @foreach ($disposiciones as $d)
                                                            <option value="{{ $d->IdNumDisp }}">{{ $d->NumDisp }}{{ $d->AnioDisp ? '/'.$d->AnioDisp : '' }} - {{ $d->Descripcion }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endisset

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-success">Guardar</button>
                                                    <button type="button" id="btn_clear" class="btn btn-outline-secondary">
                                                        <i class="fas fa-eraser"></i> Limpiar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group text-center">
                                            <label>Imagen del Certificado</label>
                                            <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*" style="display: none;">
                                            <!-- El contenedor de imageLoad se generará automáticamente aquí -->
                                        </div>
                                    </div>
                                 </div>
                            </form>
                            <hr>

                        <div class="row">
                            <div class="col-12">
                                <h5>Historial de Licencias</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Agente</th>
                                                <th>Legajo</th>
                                                <th>Fecha</th>
                                                <th>Desde</th>
                                                <th>Hasta</th>
                                                <th>Días</th>
                                                <th>Motivo</th>
                                            </tr>
                                        </thead>
                                        <tbody id="historial_licencias"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Modal para mostrar observaciones -->
<div class="modal fade" id="modalObservacion" tabindex="-1" role="dialog" aria-labelledby="modalObservacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalObservacionLabel">
                    <i class="fas fa-info-circle"></i> Observación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><strong>Orden Médica:</strong></label>
                    <p id="modalObservacionOM" class="mb-2"></p>
                </div>
                <div class="form-group">
                    <label><strong>Observación:</strong></label>
                    <div id="modalObservacionTexto" class="border rounded p-3 bg-light" style="min-height: 100px; white-space: pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Componente de Disposiciones (Modal) -->
<x-disposiciones-component
    :is-modal="true"
    target-dropdown="disposicion_id"
    :show-stats="false" />

@endsection

@section('js')
<script>
    window.laravelRoutes = {
        // Ajustadas a rutas Laravel con route() helper
        ordenMedicasBase: '{{ url('orden-medicas') }}',
        ordenMedicasList: '{{ route('orden-medicas.filtrar') }}',
        ordenMedicasUltimoNumero: '{{ route('orden-medicas.ultimo-numero') }}',
        ordenMedicasProximoCertificado: '{{ route('orden-medicas.proximo-certificado') }}',
        ordenMedicasGuardar: '{{ route('orden-medicas.store') }}',
        ordenMedicasEliminarImagen: '{{ url('orden-medicas') }}',
        licenciasCalcularFecha: '{{ route('licencias.calcular.fecha') }}',
        licenciasCalcularDias: '{{ route('licencias.calcular.dias') }}',
        licenciasHistorialBase: '{{ url('licencias/historial-personal') }}',
    };
    window.csrfToken = '{{ csrf_token() }}';

    // Variable global para controlar si Select2 está listo
    window.select2Ready = false;
    
    // Función para inicializar Select2 de forma segura
    function initSelect2() {
        if (typeof $.fn.select2 !== 'undefined') {
            // Destruir instancias previas si existen para evitar duplicados
            $('.select2').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
            });
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#panel_add')
            });
            window.select2Ready = true;
            console.log('Select2 inicializado correctamente');
        }
    }

    // Inicializar Select2 cuando el documento esté listo
    $(document).ready(function() {
        initSelect2();
    });
    
    // Función global para establecer valor en Select2
    window.setSelect2Value = function(selector, value) {
        var $el = $(selector);
        if ($el.length === 0) {
            console.error('Elemento no encontrado:', selector);
            return false;
        }
        
        // Esperar a que Select2 esté listo
        function trySetValue(attempts) {
            if (attempts <= 0) {
                console.error('No se pudo establecer el valor después de varios intentos');
                return;
            }
            
            if ($el.hasClass('select2-hidden-accessible') || $el.data('select2')) {
                $el.val(value).trigger('change');
                console.log('Valor establecido en Select2:', selector, value);
            } else {
                console.log('Select2 no listo, reintentando... intentos restantes:', attempts);
                setTimeout(function() {
                    trySetValue(attempts - 1);
                }, 100);
            }
        }
        
        trySetValue(10);
        return true;
    };
    
    // Función específica para establecer el motivo (usada por orden-medicas.js)
    window.setMotivoSelect2Value = function(value) {
        return window.setSelect2Value('#motivo_id', value);
    };
</script>
<script src="{{ asset('js/imageLoad.js') }}"></script>
<script src="{{ asset('js/disposiciones.js') }}?v={{ time() }}"></script>
<script>
    // Inicializar módulo de disposiciones
    DisposicionesModule.init({
        listar: '{{ route('disposiciones.listar') }}',
        store: '{{ route('disposiciones.store') }}',
        update: '{{ route('disposiciones.update', ':id') }}',
        destroy: '{{ route('disposiciones.destroy', ':id') }}',
        proximoNumero: '{{ route('disposiciones.proximo-numero') }}',
        estadisticas: '{{ route('disposiciones.estadisticas') }}'
    }, { isModal: true, targetDropdown: 'disposicion_id' });

    // Cargar disposiciones en el dropdown
    DisposicionesModule.cargarDropdown('disposicion_id');
</script>
<script src="{{ asset('js/orden-medicas.js') }}?v=om12_postergacion"></script>
@endsection
