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
        max-height: 300px;
        object-fit: contain;
    }

    /* Card header filtros */
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

    /* Badge estados */
    .badge-borrador { background-color: #6c757d; }
    .badge-finalizada { background-color: #198754; }
    .badge-enviada { background-color: #0d6efd; }

    /* Plantillas dropdown */
    .plantilla-item {
        cursor: pointer;
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
    }
    .plantilla-item:hover {
        background-color: #e7f1ff;
    }
    .plantilla-item:last-child {
        border-bottom: none;
    }
</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- Card header para filtros -->
                    <div class="card-header" id="card_header_filtros">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-1">
                                <label class="form-label">Año</label>
                                <select id="anio_filtro" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    @for ($y = date('Y'); $y >= date('Y') - 10; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
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
                                <label class="form-label">Personal</label>
                                <input type="text" id="personal_filtro" class="form-control form-control-sm" placeholder="Nombre, DNI, Legajo">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Nº Nota</label>
                                <input type="text" id="numero_filtro" class="form-control form-control-sm" placeholder="123">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Búsqueda</label>
                                <input type="text" id="busqueda_filtro" class="form-control form-control-sm" placeholder="Título, descripción...">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Estado</label>
                                <select id="estado_filtro" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="borrador">Borrador</option>
                                    <option value="finalizada">Finalizada</option>
                                    <option value="enviada">Enviada</option>
                                </select>
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
                            <div class="col-md-2 text-end">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownPlantillas" data-bs-toggle="dropdown">
                                            <i class="fas fa-copy"></i> Plantillas
                                        </button>
                                        <ul class="dropdown-menu" id="lista_plantillas" style="min-width: 250px;">
                                            <li><a class="dropdown-item disabled" href="#">Cargando...</a></li>
                                        </ul>
                                    </div>
                                    <button id="btn_add" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Nueva Nota
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de la lista -->
                    <div class="card-body" id="panel_list">
                        <div class="table-responsive">
                            <table id="tabla-notas" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nº Nota</th>
                                        <th>Fecha</th>
                                        <th>Título</th>
                                        <th>Personal</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Creador</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-notas"></tbody>
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
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal para ver observación -->
<div class="modal fade" id="modalObservacion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Observación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><strong>Nota:</strong></label>
                    <p id="modalNotaNumero" class="mb-2"></p>
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

<!-- Incluir el modal editor de notas -->
@include('components.editor-notas.modal', [
    'id' => 'modalEditorNotas',
    'titulo' => 'Editor de Nota Jurídica'
])
@endsection

@section('js')
<script>
    // Rutas del módulo
    window.laravelRoutes = {
        notasJuridicasBase: '{{ url("notas-juridicas") }}',
        notasJuridicasFiltrar: '{{ route("notas-juridicas.filtrar") }}',
        notasJuridicasProximoNumero: '{{ route("notas-juridicas.proximo-numero") }}',
        notasJuridicasBuscar: '{{ route("notas-juridicas.buscar") }}',
        notasJuridicasStore: '{{ route("notas-juridicas.store") }}',
        notasJuridicasPlantillas: '{{ route("notas-juridicas.plantillas") }}',
        notasJuridicasDefaults: '{{ route("plantillas-documentos.defaults") }}',
    };
    window.csrfToken = '{{ csrf_token() }}';
    window.moduloId = null; // Se cargará dinámicamente
</script>
<!-- CKEditor 5 desde CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.0/classic/ckeditor.js"></script>
<script src="{{ asset('js/imageLoad.js') }}"></script>
<script src="{{ asset('js/notas-juridicas-new.js') }}?v=1.0"></script>
@endsection
