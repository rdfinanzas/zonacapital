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

    /* Contenido de nota preview */
    .nota-preview {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background-color: #f8f9fa;
        min-height: 150px;
        max-height: 300px;
        overflow-y: auto;
        padding: 15px;
        font-family: 'Times New Roman', serif;
        font-size: 14px;
        line-height: 1.6;
    }

    .nota-preview.vacia {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #6c757d;
        font-style: italic;
    }

    /* File upload container */
    .file-upload-container {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        background-color: #f8f9fa;
        text-align: center;
        transition: all 0.3s ease;
    }

    .file-upload-container:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }

    .file-upload-container input[type="file"] {
        cursor: pointer;
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
                                    @foreach($estados as $id => $estado)
                                        <option value="{{ $id }}">{{ $estado['texto'] }}</option>
                                    @endforeach
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
                                    <button id="btn_exportar_excel" class="btn btn-outline-success btn-sm" title="Exportar a Excel">
                                        <i class="fas fa-file-excel"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownPlantillas" data-bs-toggle="dropdown">
                                            <i class="fas fa-copy"></i> Plantillas
                                        </button>
                                        <ul class="dropdown-menu" id="lista_plantillas" style="min-width: 250px;">
                                            <li><a class="dropdown-item disabled" href="#">Cargando...</a></li>
                                        </ul>
                                    </div>
                                    <button id="btn_add" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i>
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
                                        <th>Contenido</th>
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

                    <!-- Panel del formulario (oculto por defecto) -->
                    <div class="card-body d-none" id="panel_form">
                        <!-- Header específico del formulario -->
                        <div class="card-header bg-primary text-white mb-4">
                            <h5 class="mb-0" id="titulo_formulario">
                                <i class="fas fa-file-alt"></i> Nueva Nota Jurídica
                            </h5>
                        </div>

                        <div class="mb-3">
                            <button id="btn_volver" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver
                            </button>
                        </div>

                        <form id="form_nota">
                            <input type="hidden" id="nota_id" name="nota_id" value="">
                            <input type="hidden" id="configuracion" name="configuracion" value="">
                            <input type="hidden" id="google_doc_id" name="google_doc_id" value="">
                            <input type="hidden" id="google_doc_link" name="google_doc_link" value="">

                            <!-- ROW 1: Formulario + Vista Previa -->
                            <div class="row">
                                <!-- Columna izquierda - Campos del formulario -->
                                <div class="col-md-8">
                                    <div class="row">
                                        <!-- Fila 1: Personal, N° Nota, Año, Fecha -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Personal Vinculado</label>
                                                <select id="personal_id" name="personal_id" class="form-select select2">
                                                    <option value="">- SELECCIONAR -</option>
                                                    @foreach($personal as $p)
                                                        <option value="{{ $p->idEmpleado }}">{{ $p->Apellido }}, {{ $p->Nombre }} ({{ $p->DNI }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>N° Nota</label>
                                                <input type="number" id="numero" name="numero" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Año</label>
                                                <input type="number" id="anio" name="anio" class="form-control" value="{{ date('Y') }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Fecha Creación</label>
                                                <input type="date" id="fecha_creacion" name="fecha_creacion" class="form-control" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Estado</label>
                                                <select id="estado" name="estado" class="form-select">
                                                    @foreach($estados as $id => $estado)
                                                        <option value="{{ $id }}" {{ $id == 1 ? 'selected' : '' }}>{{ $estado['texto'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Fila 2: Título -->
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Título / Asunto <span class="text-danger">*</span></label>
                                                <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ingrese el título o asunto de la nota" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Fila 3: Referencia a Nota Anterior -->
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Referencia a Nota Anterior</label>
                                                <select id="nota_referencia_id" name="nota_referencia_id" class="form-select">
                                                    <option value="">- SIN REFERENCIA -</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- Fila 5: Archivo Adjunto (PDF o Imagen) -->
                                        <div class="col-md-12">
                                            <div class="card border-success mb-3">
                                                <div class="card-header bg-success text-white py-2">
                                                    <span class="fw-bold"><i class="fas fa-paperclip"></i> Archivo Adjunto (opcional)</span>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Adjuntar archivo de respaldo (PDF o Imagen)</label>
                                                        <div class="file-upload-container" id="file_upload_container">
                                                            <input type="file" id="archivo_nota" name="archivo_nota" accept=".pdf,image/*" class="form-control">
                                                            <small class="text-muted">Formatos permitidos: PDF, JPG, PNG, GIF (máx. 10MB)</small>
                                                        </div>
                                                        <input type="hidden" id="archivo_base64" name="archivo_base64" value="">
                                                        <input type="hidden" id="archivo_nombre_real" name="archivo_nombre_real" value="">
                                                        <input type="hidden" id="archivo_tipo" name="archivo_tipo" value="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- Fila 4: Documento Google Docs -->
                                        <div class="col-md-12">
                                            <div class="card border-primary mb-3">
                                                <div class="card-header bg-primary text-white py-2">
                                                    <span class="fw-bold"><i class="fab fa-google-drive"></i> Crear con Google Docs</span>
                                                </div>
                                                <div class="card-body text-center py-3">
                                                    <div class="form-check ms-3 text-start">
                                                        <input class="form-check-input" type="checkbox" id="es_plantilla" name="es_plantilla" value="1">
                                                        <label class="form-check-label" for="es_plantilla">
                                                            <i class="fas fa-copy text-info"></i> Guardar como plantilla
                                                        </label>
                                                    </div>
                                                    <input type="hidden" id="descripcion" name="descripcion" value="">

                                                    
                                                    <button type="button" id="btn_abrir_doc_drive" class="btn btn-primary">
                                                        <i class="fab fa-google-drive"></i>
                                                        <span id="btn_doc_text">Generar Documento</span>
                                                    </button>
                                                    <div id="enlace_google_doc" class="mt-2 d-none">
                                                        <a id="link_abrir_doc" href="#" target="_blank" class="btn btn-outline-success btn-sm">
                                                            <i class="fas fa-external-link-alt"></i> Abrir en Google Docs
                                                        </a>
                                                    </div>
                                                    <div id="estado_google_doc" class="mb-2">
                                                        <div class="alert alert-info py-2 mb-0">
                                                            <i class="fas fa-info-circle"></i>
                                                            El documento Google Docs es opcional. Haga clic en "Generar Documento" para crear uno, o guarde la nota sin contenido adjunto.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    

                                    <div class="row">
                                        <!-- Fila 5: Observación -->
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Observación Interna</label>
                                                <textarea id="observacion" name="observacion" class="form-control" rows="2" placeholder="Notas internas (no se incluyen en la nota)"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Fila 6: Botones y Plantilla -->
                                        <div class="col-md-12">
                                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-save"></i> Guardar
                                                </button>
                                                <button type="button" id="btn_limpiar" class="btn btn-outline-secondary">
                                                    <i class="fas fa-eraser"></i> Limpiar
                                                </button>

                                                <div id="contenedor_nombre_plantilla" class="d-none ms-2">
                                                    <input type="text" id="nombre_plantilla" name="nombre_plantilla" class="form-control form-control-sm" placeholder="Nombre de plantilla" style="width: 200px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- FIN col-md-8 -->

                                <!-- Columna derecha - Preview de archivo (para notas ADJUNTAS) -->
                                <div class="col-md-4" id="contenedor_preview_archivo">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <span class="fw-bold"><i class="fas fa-eye"></i> Vista Previa del Archivo</span>
                                        </div>
                                        <div class="card-body">
                                            <!-- Mensaje cuando no hay archivo -->
                                            <div id="archivo_sin_preview" class="text-center py-4">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                                <p class="text-muted mb-0">Seleccione un archivo para ver la vista previa</p>
                                            </div>
                                            <!-- Preview del archivo -->
                                            <div id="archivo_preview_container" class="d-none">
                                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="fas fa-file-pdf fa-2x text-danger" id="archivo_icon"></i>
                                                        <span id="archivo_nombre" class="fw-bold text-truncate" style="max-width: 150px;"></span>
                                                    </div>
                                                    <div class="d-flex gap-1">
                                                        <a id="btn_abrir_archivo" href="#" target="_blank" class="btn btn-sm btn-outline-primary" title="Abrir archivo">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                        <button type="button" id="btn_eliminar_archivo" class="btn btn-sm btn-outline-danger" title="Eliminar archivo">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <!-- Preview de imagen -->
                                                <div id="imagen_preview" class="text-center d-none">
                                                    <img id="img_preview" src="" alt="Preview" style="max-width: 100%; max-height: 250px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                </div>
                                                <!-- Preview de PDF -->
                                                <div id="pdf_preview" class="text-center d-none">
                                                    <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i>
                                                    <p class="text-muted mb-0 small">Archivo PDF seleccionado</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- FIN col-md-4 preview archivo -->
                            </div>
                            <!-- FIN ROW 1: Formulario + Vista Previa -->

                            <!-- ROW 2: Historial de Novedades (FUERA del row anterior) -->
                            <div class="row mt-4" id="seccion_historial" style="display: none;">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-history"></i> Historial de Novedades</span>
                                            <button type="button" id="btn_agregar_novedad" class="btn btn-sm btn-light">
                                                <i class="fas fa-plus"></i> Agregar Novedad
                                            </button>
                                        </div>
                                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                            <div id="historial_lista">
                                                <p class="text-muted text-center">No hay novedades registradas</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- FIN ROW 2: Historial -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal para agregar novedad -->
<div class="modal fade" id="modalAgregarNovedad" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Agregar Novedad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_novedad">
                <div class="modal-body">
                    <input type="hidden" id="novedad_nota_id" value="">
                    <div class="form-group">
                        <label>Descripción de la novedad <span class="text-danger">*</span></label>
                        <textarea id="novedad_descripcion" class="form-control" rows="4" placeholder="Describa la novedad o cambio realizado..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Novedad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

{{-- El modal editor de notas NO se usa en este módulo - se usa Google Docs directamente --}}
@endsection

@section('js')
<script>
    // Rutas del módulo
    window.laravelRoutes = {
        notasJuridicasBase: '{{ url("notas-juridicas") }}',
        notasJuridicasFiltrar: '{{ route("notas-juridicas.filtrar") }}',
        notasJuridicasProximoNumero: '{{ route("notas-juridicas.proximo-numero") }}',
        notasJuridicasVerificarNumero: '{{ route("notas-juridicas.verificar-numero") }}',
        notasJuridicasBuscar: '{{ route("notas-juridicas.buscar") }}',
        notasJuridicasStore: '{{ route("notas-juridicas.store") }}',
        notasJuridicasPlantillas: '{{ route("notas-juridicas.plantillas") }}',
        notasJuridicasPlantillasDrive: '{{ route("notas-juridicas.plantillas-drive") }}',
        notasJuridicasCrearDocDrive: '{{ route("notas-juridicas.crear-doc-drive") }}',
        notasJuridicasCargarPlantilla: function(id) { return '{{ url("notas-juridicas") }}' + '/plantillas/' + id; },
        notasJuridicasExportarExcel: '{{ route("notas-juridicas.exportar-excel") }}',
    };
    window.csrfToken = '{{ csrf_token() }}';
    window.moduloId = null;
    window.moduloUrl = 'laravel-notas-juridicas';
    
    // Estados disponibles - mapeo ID => Texto para el JS
    window.estadosNotas = @json(array_map(fn($e) => $e['texto'], $estados));
</script>
<script src="{{ asset('js/imageLoad.js') }}"></script>
<script src="{{ asset('js/notas-juridicas.js') }}?v={{ time() }}"></script>
@endsection
