@props([
    'id' => 'modalEditorNotas',
    'moduloId' => null,
    'moduloUrl' => null,
    'titulo' => 'Editor de Notas',
    'logoDefault' => null,
    'leyendaDefault' => null,
    'mostrarEncabezado' => true,
])

<!-- Modal Editor de Notas Universal -->
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="{{ $id }}Label">
                    <i class="fas fa-file-alt"></i> {{ $titulo }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    <!-- Panel Izquierdo: Editor -->
                    <div class="col-md-8 border-end">
                        <div class="p-3 h-100 d-flex flex-column">
                            <!-- Encabezado configurable -->
                            <div class="card mb-3" id="seccion_encabezado">
                                <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="check_incluir_encabezado" {{ $mostrarEncabezado ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="check_incluir_encabezado">
                                            <i class="fas fa-heading"></i> Incluir Encabezado
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body py-2" id="contenedor_encabezado">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="logo-upload-container" id="logo_container" title="Click para subir logo">
                                                <div class="logo-placeholder">
                                                    <i class="fas fa-image fa-2x"></i>
                                                    <small class="d-block">Logo</small>
                                                </div>
                                            </div>
                                            <input type="file" id="logo_input" accept="image/*" style="display: none;">
                                            <input type="hidden" id="logo_path" name="logo_path">
                                            <button type="button" id="btn_eliminar_logo" class="btn btn-sm btn-outline-danger mt-1 d-none">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="col">
                                            <label class="form-label small">Leyenda del encabezado</label>
                                            <textarea id="leyenda_encabezado" name="leyenda_encabezado" class="form-control form-control-sm"
                                                rows="2" placeholder="Ej: Ministerio de Salud Pública&#10;Dirección Zona Capital"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Editor CKEditor 5 -->
                            <div class="card flex-grow-1 mb-3 d-flex flex-column" id="card_editor_contenido">
                                <div class="card-header py-2 bg-light">
                                    <span class="fw-bold"><i class="fas fa-edit"></i> Contenido</span>
                                </div>
                                <div class="card-body p-0 flex-grow-1" id="editor_container">
                                    <!-- Toolbar de CKEditor -->
                                    <div id="toolbar-container-{{ $id }}"></div>
                                    <!-- Área de edición (simula hoja de papel) -->
                                    <div id="editor-container-{{ $id }}"></div>
                                </div>
                            </div>

                            <!-- Configuración de página -->
                            <div class="card">
                                <div class="card-header py-2 bg-light" data-bs-toggle="collapse" data-bs-target="#configPagina"
                                    style="cursor: pointer;">
                                    <span class="fw-bold"><i class="fas fa-cog"></i> Configuración de Página</span>
                                    <i class="fas fa-chevron-down float-end"></i>
                                </div>
                                <div class="collapse" id="configPagina">
                                    <div class="card-body py-2">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label small">Tamaño</label>
                                                <select id="tamano_pagina" name="tamano_pagina" class="form-select form-select-sm">
                                                    <option value="legal">Legal</option>
                                                    <option value="a4">A4</option>
                                                    <option value="letter">Carta</option>
                                                    <option value="oficio">Oficio</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Orientación</label>
                                                <select id="orientacion" name="orientacion" class="form-select form-select-sm">
                                                    <option value="portrait">Vertical</option>
                                                    <option value="landscape">Horizontal</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Márgenes (cm)</label>
                                                <div class="row g-1">
                                                    <div class="col-3">
                                                        <input type="number" id="margen_superior" name="margen_superior"
                                                            class="form-control form-control-sm" value="2.0" min="0.5" max="5" step="0.5" title="Superior">
                                                        <small class="text-muted d-block text-center">Sup.</small>
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" id="margen_inferior" name="margen_inferior"
                                                            class="form-control form-control-sm" value="2.0" min="0.5" max="5" step="0.5" title="Inferior">
                                                        <small class="text-muted d-block text-center">Inf.</small>
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" id="margen_izquierdo" name="margen_izquierdo"
                                                            class="form-control form-control-sm" value="2.5" min="0.5" max="5" step="0.5" title="Izquierdo">
                                                        <small class="text-muted d-block text-center">Izq.</small>
                                                    </div>
                                                    <div class="col-3">
                                                        <input type="number" id="margen_derecho" name="margen_derecho"
                                                            class="form-control form-control-sm" value="2.5" min="0.5" max="5" step="0.5" title="Derecho">
                                                        <small class="text-muted d-block text-center">Der.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Derecho: Configuración -->
                    <div class="col-md-4 bg-light">
                        <div class="p-3 h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold"><i class="fas fa-cog"></i> Configuración</span>
                            </div>

                            <!-- Resumen de configuración -->
                            <div class="card mb-3">
                                <div class="card-body py-2">
                                    <div class="mb-2">
                                        <strong>Tamaño:</strong> <span id="info_pagina">Legal - Vertical</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Márgenes:</strong> <span id="info_margenes">2/2/2.5/2.5 cm</span>
                                    </div>
                                    <div>
                                        <strong>Encabezado:</strong> <span id="info_encabezado_estado">Incluido</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón de Vista Previa -->
                            <button type="button" id="btn_vista_previa" class="btn btn-primary mb-3">
                                <i class="fas fa-eye"></i> Ver Vista Previa
                            </button>

                            <!-- Instrucciones -->
                            <div class="alert alert-info small mb-0">
                                <i class="fas fa-info-circle"></i>
                                <strong>Tip:</strong> Edita tu documento y haz clic en "Ver Vista Previa" para ver cómo quedará el documento final.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="me-auto">
                    <!-- Guardar como plantilla -->
                    <div class="input-group input-group-sm" style="width: auto;">
                        <input type="text" id="nombre_plantilla" class="form-control" placeholder="Nombre de plantilla (opcional)">
                        <button type="button" id="btn_guardar_plantilla" class="btn btn-outline-info">
                            <i class="fas fa-save"></i> Guardar como Plantilla
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" id="btn_guardar_nota" class="btn btn-success">
                    <i class="fas fa-check"></i> Guardar Nota
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Vista Previa -->
<div class="modal fade" id="modalVistaPrevia{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Vista Previa del Documento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-secondary p-4">
                <div class="d-flex justify-content-center overflow-auto">
                    <div class="preview-document" id="preview_document_{{ $id }}">
                        <div class="preview-doc-header" id="preview_doc_header"></div>
                        <div class="preview-doc-content" id="preview_doc_content"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="me-auto">
                    <span class="badge bg-info" id="preview_doc_info">Legal - Vertical</span>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btn_imprimir_preview">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Contenedor de logo */
    .logo-upload-container {
        width: 80px;
        height: 60px;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background-color: #f8f9fa;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .logo-upload-container:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }

    .logo-upload-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .logo-placeholder {
        text-align: center;
        color: #adb5bd;
    }

    /* CKEditor 5 */
    #card_editor_contenido {
        min-height: 400px;
    }

    #editor_container {
        display: flex;
        flex-direction: column;
        align-items: center;
        background: #e9ecef;
        padding: 20px;
        overflow-y: auto;
    }

    #toolbar-container-{{ $id }} {
        width: 816px;
        max-width: 100%;
    }

    #toolbar-container-{{ $id }} .ck-toolbar {
        border: 1px solid #dee2e6 !important;
        border-bottom: none !important;
        border-radius: 0 !important;
        background: #f8f9fa !important;
    }

    #editor-container-{{ $id }} {
        width: 816px;
        max-width: 100%;
        min-height: 1056px;
        border: 1px solid #dee2e6;
        border-top: none;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        box-sizing: border-box;
    }

    #editor-container-{{ $id }} .ck-editor__editable {
        min-height: 1000px;
        padding: 20px 25px;
        line-height: 1.6;
        box-sizing: border-box;
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
    }

    .ck-content {
        box-sizing: border-box;
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
    }

    .ck-content p {
        margin: 0 0 10px 0;
    }

    .ck-content ul,
    .ck-content ol {
        margin: 0 0 10px 0;
        padding-left: 40px;
    }

    .ck-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }

    .ck-content table td,
    .ck-content table th {
        border: 1px solid #333;
        padding: 8px;
    }

    /* Vista previa en modal */
    .preview-document {
        background: white;
        width: 816px;
        min-height: 1056px;
        padding: 20px 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
        box-sizing: border-box;
    }

    .preview-doc-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 10px;
        border-bottom: 1px solid #333;
        margin-bottom: 15px;
        min-height: 60px;
    }

    .preview-doc-content {
        text-align: justify;
    }

    .preview-doc-content p {
        margin: 0 0 10px 0;
    }

    @@media (max-width: 992px) {
        .preview-document {
            width: 100%;
            min-height: auto;
        }
    }
</style>

<!-- CKEditor 5 desde CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/decoupled-document/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/decoupled-document/translations/es.js"></script>

<script>
    window.EditorNotas = window.EditorNotas || {};
    window.EditorNotas['{{ $id }}'] = {
        moduloId: {{ $moduloId ?? 'null' }},
        moduloUrl: '{{ $moduloUrl ?? '' }}',
        editorInstance: null,
        logoBase64: null,
        configActual: null,
        callbackGuardar: null,
        callbackGuardarPlantilla: null,
        modalInstance: null,
        editorReady: false,
        mostrarEncabezado: {{ $mostrarEncabezado ? 'true' : 'false' }},
        defaultsSistema: {
            logoPath: '{{ $logoDefault ?? '' }}',
            leyenda: {!! json_encode($leyendaDefault ?? '') !!}
        },

        init: function() {
            console.log('Inicializando editor de notas...');
            this.initCKEditor();
            this.initEventListeners();
            this.initLogoUpload();
            this.loadDefaults();
        },

        initCKEditor: async function() {
            const self = this;

            if (this.editorReady && this.editorInstance) return;

            try {
                this.editorInstance = await DecoupledEditor
                    .create(document.getElementById('editor-container-{{ $id }}'), {
                        language: 'es',
                        toolbar: {
                            items: [
                                'undo', 'redo',
                                '|', 'heading', 'fontsize', 'fontFamily',
                                '|', 'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
                                '|', 'fontColor', 'fontBackgroundColor',
                                '|', 'alignment', 'outdent', 'indent',
                                '|', 'bulletedList', 'numberedList', 'todoList',
                                '|', 'insertTable', 'blockQuote', 'horizontalLine',
                                '|', 'link', 'imageInsert', 'mediaEmbed',
                                '|', 'findAndReplace', 'sourceEditing'
                            ]
                        },
                        fontSize: {
                            options: [8, 9, 10, 11, 12, 'default', 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72],
                            supportAllValues: true
                        },
                        fontFamily: {
                            options: [
                                'default',
                                'Arial, Helvetica, sans-serif',
                                'Times New Roman, Times, serif',
                                'Courier New, Courier, monospace',
                                'Georgia, serif',
                                'Verdana, Geneva, sans-serif'
                            ],
                            supportAllValues: true
                        },
                        heading: {
                            options: [
                                { model: 'paragraph', title: 'Párrafo', class: 'ck-heading_paragraph' },
                                { model: 'heading1', view: 'h1', title: 'Título 1', class: 'ck-heading_heading1' },
                                { model: 'heading2', view: 'h2', title: 'Título 2', class: 'ck-heading_heading2' },
                                { model: 'heading3', view: 'h3', title: 'Título 3', class: 'ck-heading_heading3' }
                            ]
                        },
                        table: {
                            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
                        },
                        image: {
                            toolbar: ['imageTextAlternative', 'toggleImageCaption', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side']
                        }
                    });

                const toolbarContainer = document.getElementById('toolbar-container-{{ $id }}');
                toolbarContainer.innerHTML = '';
                toolbarContainer.appendChild(this.editorInstance.ui.view.toolbar.element);

                this.editorReady = true;
                console.log('CKEditor 5 inicializado correctamente');

            } catch (error) {
                console.error('Error inicializando CKEditor 5:', error);
            }
        },

        initLogoUpload: function() {
            if (!this.mostrarEncabezado) return;

            const container = document.getElementById('logo_container');
            const input = document.getElementById('logo_input');
            const self = this;

            container.addEventListener('click', () => input.click());

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    self.logoBase64 = e.target.result;
                    document.getElementById('logo_path').value = self.logoBase64;
                    container.innerHTML = `<img src="${self.logoBase64}" alt="Logo">`;
                    document.getElementById('btn_eliminar_logo').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });

            document.getElementById('btn_eliminar_logo').addEventListener('click', function(e) {
                e.stopPropagation();
                self.logoBase64 = null;
                document.getElementById('logo_path').value = '';

                if (self.defaultsSistema.logoPath) {
                    container.innerHTML = `<img src="/${self.defaultsSistema.logoPath}" alt="Logo">`;
                    document.getElementById('btn_eliminar_logo').classList.remove('d-none');
                } else {
                    container.innerHTML = `
                        <div class="logo-placeholder">
                            <i class="fas fa-image fa-2x"></i>
                            <small class="d-block">Logo</small>
                        </div>
                    `;
                    document.getElementById('btn_eliminar_logo').classList.add('d-none');
                }
            });
        },

        initEventListeners: function() {
            const self = this;

            // Checkbox de incluir encabezado
            const checkEncabezado = document.getElementById('check_incluir_encabezado');
            if (checkEncabezado) {
                checkEncabezado.addEventListener('change', function() {
                    self.mostrarEncabezado = this.checked;
                    self.toggleEncabezado(this.checked);
                });
            }

            // Botón de Vista Previa
            document.getElementById('btn_vista_previa').addEventListener('click', () => {
                self.mostrarVistaPrevia();
            });

            // Cambios en configuración de página
            ['tamano_pagina', 'orientacion', 'margen_superior', 'margen_inferior',
             'margen_izquierdo', 'margen_derecho', 'leyenda_encabezado'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', () => self.actualizarInfo());
            });

            // Guardar nota
            document.getElementById('btn_guardar_nota').addEventListener('click', function() {
                const config = self.obtenerConfiguracion();
                if (self.callbackGuardar) {
                    self.callbackGuardar(config);
                } else {
                    self.guardarNota(config);
                }
            });

            // Guardar como plantilla
            document.getElementById('btn_guardar_plantilla').addEventListener('click', function() {
                const nombre = document.getElementById('nombre_plantilla').value.trim();
                if (!nombre) {
                    alert('Ingrese un nombre para la plantilla');
                    return;
                }
                const config = self.obtenerConfiguracion();
                if (self.callbackGuardarPlantilla) {
                    self.callbackGuardarPlantilla(nombre, config);
                } else {
                    self.guardarPlantilla(nombre, config);
                }
            });

            // Imprimir desde preview
            document.getElementById('btn_imprimir_preview').addEventListener('click', function() {
                window.print();
            });
        },

        toggleEncabezado: function(mostrar) {
            const contenedor = document.getElementById('contenedor_encabezado');
            if (contenedor) {
                contenedor.style.display = mostrar ? '' : 'none';
            }
            this.actualizarInfo();
        },

        loadDefaults: async function() {
            const checkEncabezado = document.getElementById('check_incluir_encabezado');
            const contenedorEncabezado = document.getElementById('contenedor_encabezado');

            if (checkEncabezado) {
                checkEncabezado.checked = this.mostrarEncabezado;
            }

            if (contenedorEncabezado) {
                contenedorEncabezado.style.display = this.mostrarEncabezado ? '' : 'none';
            }

            if (this.mostrarEncabezado) {
                const container = document.getElementById('logo_container');
                const leyendaEl = document.getElementById('leyenda_encabezado');

                if (this.defaultsSistema.logoPath) {
                    this.logoBase64 = '/' + this.defaultsSistema.logoPath;
                    document.getElementById('logo_path').value = this.logoBase64;
                    container.innerHTML = `<img src="/${this.defaultsSistema.logoPath}" alt="Logo">`;
                    document.getElementById('btn_eliminar_logo').classList.remove('d-none');
                }

                if (this.defaultsSistema.leyenda && leyendaEl) {
                    leyendaEl.value = this.defaultsSistema.leyenda;
                }
            }

            this.configActual = {
                encabezado: {
                    logo_path: this.logoBase64,
                    leyenda: this.defaultsSistema.leyenda
                },
                contenido: '',
                margenes: { superior: 2.0, inferior: 2.0, izquierdo: 2.5, derecho: 2.5 },
                pagina: { tamano: 'legal', orientacion: 'portrait' }
            };

            this.actualizarInfo();
        },

        obtenerConfiguracion: function() {
            const config = {
                contenido: this.editorInstance ? this.editorInstance.getData() : '',
                margenes: {
                    superior: parseFloat(document.getElementById('margen_superior').value) || 2.0,
                    inferior: parseFloat(document.getElementById('margen_inferior').value) || 2.0,
                    izquierdo: parseFloat(document.getElementById('margen_izquierdo').value) || 2.5,
                    derecho: parseFloat(document.getElementById('margen_derecho').value) || 2.5,
                },
                pagina: {
                    tamano: document.getElementById('tamano_pagina').value || 'legal',
                    orientacion: document.getElementById('orientacion').value || 'portrait',
                }
            };

            if (this.mostrarEncabezado) {
                config.encabezado = {
                    logo_path: this.logoBase64 || document.getElementById('logo_path').value || null,
                    leyenda: document.getElementById('leyenda_encabezado').value || null,
                };
            }

            return config;
        },

        cargarConfiguracion: function(config) {
            this.configActual = config;

            if (this.mostrarEncabezado && config.encabezado) {
                if (config.encabezado.logo_path) {
                    this.logoBase64 = config.encabezado.logo_path;
                    document.getElementById('logo_path').value = this.logoBase64;
                    document.getElementById('logo_container').innerHTML = `<img src="${this.logoBase64}" alt="Logo">`;
                    document.getElementById('btn_eliminar_logo').classList.remove('d-none');
                }
                if (config.encabezado.leyenda) {
                    document.getElementById('leyenda_encabezado').value = config.encabezado.leyenda;
                }
            }

            if (this.editorInstance && config.contenido) {
                this.editorInstance.setData(config.contenido);
            }

            if (config.margenes) {
                document.getElementById('margen_superior').value = config.margenes.superior || 2.0;
                document.getElementById('margen_inferior').value = config.margenes.inferior || 2.0;
                document.getElementById('margen_izquierdo').value = config.margenes.izquierdo || 2.5;
                document.getElementById('margen_derecho').value = config.margenes.derecho || 2.5;
            }

            if (config.pagina) {
                document.getElementById('tamano_pagina').value = config.pagina.tamano || 'legal';
                document.getElementById('orientacion').value = config.pagina.orientacion || 'portrait';
            }

            this.actualizarInfo();
        },

        actualizarInfo: function() {
            const config = this.obtenerConfiguracion();
            const isLandscape = config.pagina.orientacion === 'landscape';
            const orientacionText = isLandscape ? 'Horizontal' : 'Vertical';

            document.getElementById('info_pagina').textContent =
                `${config.pagina.tamano.toUpperCase()} - ${orientacionText}`;
            document.getElementById('info_margenes').textContent =
                `${config.margenes.superior}/${config.margenes.inferior}/${config.margenes.izquierdo}/${config.margenes.derecho} cm`;

            const estadoEncabezado = document.getElementById('info_encabezado_estado');
            if (estadoEncabezado) {
                estadoEncabezado.textContent = this.mostrarEncabezado ? 'Incluido' : 'No incluido';
            }
        },

        mostrarVistaPrevia: function() {
            const config = this.obtenerConfiguracion();
            const modal = new bootstrap.Modal(document.getElementById('modalVistaPrevia{{ $id }}'));

            // Tamaños de página
            const tamanos = {
                'legal': { w: 816, h: 1056 },
                'a4': { w: 794, h: 1122 },
                'letter': { w: 816, h: 792 },
                'oficio': { w: 816, h: 990 }
            };

            const dims = tamanos[config.pagina.tamano] || tamanos['legal'];
            const isLandscape = config.pagina.orientacion === 'landscape';
            const pageWidth = isLandscape ? dims.h : dims.w;
            const pageHeight = isLandscape ? dims.w : dims.h;

            // Configurar documento
            const docEl = document.getElementById('preview_document_{{ $id }}');
            docEl.style.width = pageWidth + 'px';
            docEl.style.minHeight = pageHeight + 'px';

            // Header
            const headerEl = document.getElementById('preview_doc_header');
            if (this.mostrarEncabezado) {
                let headerHTML = '<div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:10px;border-bottom:1px solid #333;margin-bottom:15px;">';
                if (config.encabezado && config.encabezado.logo_path) {
                    headerHTML += `<div><img src="${config.encabezado.logo_path}" alt="Logo" style="max-width:80px;max-height:60px;"></div>`;
                } else {
                    headerHTML += '<div></div>';
                }
                if (config.encabezado && config.encabezado.leyenda) {
                    headerHTML += `<div style="text-align:right;font-style:italic;font-size:10pt;color:#555;white-space:pre-line;">${config.encabezado.leyenda}</div>`;
                }
                headerHTML += '</div>';
                headerEl.innerHTML = headerHTML;
            } else {
                headerEl.innerHTML = '';
            }

            // Contenido
            const contentEl = document.getElementById('preview_doc_content');
            if (this.editorInstance) {
                contentEl.innerHTML = this.editorInstance.getData();
            } else {
                contentEl.innerHTML = '<p style="text-align:center;color:#999;">Sin contenido</p>';
            }

            // Info
            const orientacionText = isLandscape ? 'Horizontal' : 'Vertical';
            document.getElementById('preview_doc_info').textContent =
                `${config.pagina.tamano.toUpperCase()} - ${orientacionText} | Márgenes: ${config.margenes.superior}/${config.margenes.inferior}/${config.margenes.izquierdo}/${config.margenes.derecho} cm`;

            modal.show();
        },

        limpiar: function() {
            document.getElementById('nombre_plantilla').value = '';

            const checkEncabezado = document.getElementById('check_incluir_encabezado');
            const contenedorEncabezado = document.getElementById('contenedor_encabezado');

            this.mostrarEncabezado = {{ $mostrarEncabezado ? 'true' : 'false' }};
            if (checkEncabezado) checkEncabezado.checked = this.mostrarEncabezado;
            if (contenedorEncabezado) contenedorEncabezado.style.display = this.mostrarEncabezado ? '' : 'none';

            if (this.mostrarEncabezado) {
                const logoContainer = document.getElementById('logo_container');
                const leyendaEl = document.getElementById('leyenda_encabezado');

                if (this.defaultsSistema.logoPath) {
                    this.logoBase64 = '/' + this.defaultsSistema.logoPath;
                    document.getElementById('logo_path').value = this.logoBase64;
                    logoContainer.innerHTML = `<img src="/${this.defaultsSistema.logoPath}" alt="Logo">`;
                    document.getElementById('btn_eliminar_logo').classList.remove('d-none');
                } else {
                    this.logoBase64 = null;
                    document.getElementById('logo_path').value = '';
                    logoContainer.innerHTML = `
                        <div class="logo-placeholder">
                            <i class="fas fa-image fa-2x"></i>
                            <small class="d-block">Logo</small>
                        </div>
                    `;
                    document.getElementById('btn_eliminar_logo').classList.add('d-none');
                }

                if (this.defaultsSistema.leyenda && leyendaEl) {
                    leyendaEl.value = this.defaultsSistema.leyenda;
                } else if (leyendaEl) {
                    leyendaEl.value = '';
                }
            }

            if (this.editorInstance) {
                this.editorInstance.setData('');
            }

            document.getElementById('margen_superior').value = 2.0;
            document.getElementById('margen_inferior').value = 2.0;
            document.getElementById('margen_izquierdo').value = 2.5;
            document.getElementById('margen_derecho').value = 2.5;
            document.getElementById('tamano_pagina').value = 'legal';
            document.getElementById('orientacion').value = 'portrait';

            this.actualizarInfo();
        },

        guardarNota: async function(config) {
            console.log('Guardando nota:', config);
            alert('Configure el callback de guardar nota desde el módulo');
        },

        guardarPlantilla: async function(nombre, config) {
            try {
                const resp = await fetch('/plantillas-documentos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        nombre: nombre,
                        modulo_id: this.moduloId,
                        configuracion: config
                    })
                });

                const data = await resp.json();

                if (data.success) {
                    alert('Plantilla guardada exitosamente');
                    document.getElementById('nombre_plantilla').value = '';
                } else {
                    alert(data.message || 'Error al guardar la plantilla');
                }
            } catch (e) {
                console.error('Error guardando plantilla:', e);
                alert('Error al guardar la plantilla');
            }
        },

        abrir: function(callbackGuardar, callbackGuardarPlantilla) {
            this.callbackGuardar = callbackGuardar;
            this.callbackGuardarPlantilla = callbackGuardarPlantilla;

            this.modalInstance = new bootstrap.Modal(document.getElementById('{{ $id }}'));
            this.modalInstance.show();
        },

        cerrar: function() {
            if (this.modalInstance) {
                this.modalInstance.hide();
            } else {
                const modal = bootstrap.Modal.getInstance(document.getElementById('{{ $id }}'));
                if (modal) modal.hide();
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.EditorNotas['{{ $id }}'].init();
    });
</script>
