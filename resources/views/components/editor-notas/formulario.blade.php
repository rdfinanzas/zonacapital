@props([
    'id' => 'modalFormularioNota',
    'personal' => [],
])

<!-- Modal Formulario Nueva Nota Jurídica -->
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="{{ $id }}Label">
                    <i class="fas fa-file-alt"></i> Nueva Nota Jurídica
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Botón Volver (solo visible en modo edición) -->
                <div class="mb-3" id="volver_container" style="display: none;">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_volver_lista">
                        <i class="fas fa-arrow-left"></i> Volver
                    </button>
                </div>

                <form id="form_nueva_nota">
                    <!-- Fila 1: Personal, N° Nota, Año, Fecha -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Personal Vinculado</label>
                            <select id="form_personal_id" name="personal_id" class="form-select">
                                <option value="">- SELECCIONAR -</option>
                                @foreach($personal as $emp)
                                    <option value="{{ $emp->idEmpleado }}">
                                        {{ $emp->Apellido }}, {{ $emp->Nombre }} ({{ $emp->DNI }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">N° Nota</label>
                            <input type="number" id="form_numero" name="numero" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Año</label>
                            <input type="number" id="form_anio" name="anio" class="form-control" value="{{ date('Y') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha Creación</label>
                            <input type="date" id="form_fecha_creacion" name="fecha_creacion" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="form_guardar_plantilla" name="guardar_plantilla">
                                <label class="form-check-label" for="form_guardar_plantilla">
                                    <i class="fas fa-save"></i> Guardar como plantilla
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Título -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Título / Asunto <span class="text-danger">*</span></label>
                            <input type="text" id="form_titulo" name="titulo" class="form-control" placeholder="Ingrese el título o asunto de la nota" required>
                        </div>
                    </div>

                    <!-- Fila 3: Referencia y Estado -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Referencia a Nota Anterior</label>
                            <select id="form_nota_referencia_id" name="nota_referencia_id" class="form-select">
                                <option value="">- SIN REFERENCIA -</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select id="form_estado" name="estado" class="form-select">
                                <option value="borrador" selected>Borrador</option>
                                <option value="finalizada">Finalizada</option>
                                <option value="enviada">Enviada</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="nombre_plantilla_container" style="display: none;">
                            <label class="form-label">Nombre Plantilla</label>
                            <input type="text" id="form_nombre_plantilla" name="nombre_plantilla" class="form-control" placeholder="Nombre de la plantilla">
                        </div>
                    </div>

                    <!-- Fila 4: Tipo de Nota -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Tipo de Nota</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo" id="tipo_creada" value="creada" checked>
                                    <label class="form-check-label" for="tipo_creada">
                                        <i class="fas fa-edit text-primary"></i> <strong>Crear Nota</strong>
                                        <small class="d-block text-muted">Redactar con el editor de texto</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo" id="tipo_adjunta" value="adjunta">
                                    <label class="form-check-label" for="tipo_adjunta">
                                        <i class="fas fa-paperclip text-success"></i> <strong>Adjuntar Archivo</strong>
                                        <small class="d-block text-muted">Subir PDF o imagen escaneada</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido para tipo "creada" -->
                    <div id="contenido_creada" class="mb-3">
                        <label class="form-label">Descripción / Contenido de la Nota</label>
                        <div class="border rounded">
                            <div class="bg-light border-bottom p-2">
                                <small class="text-muted"><i class="fas fa-image"></i> Leyenda del encabezado (opcional)...</small>
                            </div>
                            <textarea id="form_descripcion" name="descripcion" class="form-control border-0" rows="6" placeholder="Contenido de la nota...

Este contenido se mostrará en el PDF final."></textarea>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="btn_abrir_editor_completo" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-expand"></i> Abrir Editor Completo
                            </button>
                            <small class="text-muted ms-2">Abre el editor avanzado con más opciones de formato</small>
                        </div>
                    </div>

                    <!-- Contenido para tipo "adjunta" -->
                    <div id="contenido_adjunta" class="mb-3" style="display: none;">
                        <label class="form-label">Archivo Adjunto <span class="text-danger">*</span></label>
                        <div class="border rounded p-3 bg-light">
                            <input type="file" id="form_archivo" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Formatos permitidos: PDF, JPG, JPEG, PNG</small>
                        </div>
                        <div id="preview_archivo" class="mt-2" style="display: none;">
                            <label class="form-label">Vista previa:</label>
                            <div class="border rounded p-2 bg-white">
                                <img id="img_preview" src="" alt="Preview" style="max-height: 200px; max-width: 100%;">
                                <embed id="pdf_preview" src="" type="application/pdf" width="100%" height="200px" style="display: none;">
                            </div>
                        </div>
                    </div>

                    <!-- Observación Interna -->
                    <div class="mb-3">
                        <label class="form-label">Observación Interna</label>
                        <textarea id="form_observacion" name="observacion" class="form-control" rows="2" placeholder="Notas internas (no se incluyen en la nota)"></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" id="btn_limpiar_formulario" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
                <button type="button" id="btn_guardar_formulario" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #{{ $id }} .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
    }
    
    #{{ $id }} .form-check-label {
        font-weight: normal;
    }
    
    #{{ $id }} .form-check-label strong {
        color: #333;
    }
    
    #form_descripcion {
        resize: vertical;
        min-height: 150px;
    }
</style>
