@props([
    'id' => 'modalEditorNotas',
    'moduloId' => null,
    'moduloUrl' => null,
    'titulo' => 'Editor de Notas',
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
                            <div class="card mb-3">
                                <div class="card-header py-2 bg-light">
                                    <span class="fw-bold"><i class="fas fa-heading"></i> Encabezado</span>
                                </div>
                                <div class="card-body py-2">
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

                            <!-- Editor CKEditor -->
                            <div class="card flex-grow-1 mb-3">
                                <div class="card-header py-2 bg-light">
                                    <span class="fw-bold"><i class="fas fa-edit"></i> Contenido</span>
                                </div>
                                <div class="card-body p-0">
                                    <textarea id="contenido_editor" name="contenido" class="form-control border-0"
                                        style="min-height: 400px;" autocomplete="off"></textarea>
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

                    <!-- Panel Derecho: Vista Previa -->
                    <div class="col-md-4 bg-light">
                        <div class="p-3 h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold"><i class="fas fa-eye"></i> Vista Previa</span>
                                <button type="button" id="btn_actualizar_preview" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-sync"></i> Actualizar
                                </button>
                            </div>

                            <!-- Contenedor de vista previa -->
                            <div class="preview-container flex-grow-1 overflow-auto" id="preview_container">
                                <div class="preview-page" id="preview_page">
                                    <div class="preview-header" id="preview_header">
                                        <div class="preview-logo" id="preview_logo"></div>
                                        <div class="preview-leyenda" id="preview_leyenda"></div>
                                    </div>
                                    <div class="preview-content" id="preview_content">
                                        <p class="text-muted text-center">El contenido aparecerá aquí...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de configuración -->
                            <div class="mt-2 small text-muted" id="preview_info">
                                <span id="info_pagina">Legal - Vertical</span> |
                                <span id="info_margenes">Márgenes: 2/2/2.5/2.5 cm</span>
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

    /* Vista previa */
    .preview-container {
        background: #6c757d;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        justify-content: center;
    }

    .preview-page {
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        transform-origin: top center;
        /* Tamaño base que se ajustará con JS */
        width: 216px;
        min-height: 356px;
        padding: 20px 25px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 8px;
        line-height: 1.4;
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 8px;
        border-bottom: 1px solid #333;
        margin-bottom: 10px;
        min-height: 30px;
    }

    .preview-logo img {
        max-width: 60px;
        max-height: 25px;
    }

    .preview-leyenda {
        text-align: right;
        font-style: italic;
        font-size: 6px;
        color: #555;
        white-space: pre-line;
    }

    .preview-content {
        text-align: justify;
    }

    .preview-content p {
        margin-bottom: 6px;
    }

    .preview-content ul, .preview-content ol {
        margin-left: 1.5em;
        margin-bottom: 6px;
    }

    .preview-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 6px 0;
    }

    .preview-content table th,
    .preview-content table td {
        border: 1px solid #333;
        padding: 4px;
    }

    /* Responsive del modal */
    @@media (max-width: 992px) {
        .preview-container {
            padding: 10px;
        }

        .preview-page {
            width: 100%;
            min-height: auto;
        }
    }
</style>

<script>
    // Variables del componente
    window.EditorNotas = window.EditorNotas || {};
    window.EditorNotas['{{ $id }}'] = {
        moduloId: {{ $moduloId ?? 'null' }},
        moduloUrl: '{{ $moduloUrl ?? '' }}',
        ckeditorInstance: null,
        logoBase64: null,
        configActual: null,
        callbackGuardar: null,
        callbackGuardarPlantilla: null,

        // Inicializar el editor
        init: function() {
            this.initCKEditor();
            this.initLogoUpload();
            this.initEventListeners();
            this.loadDefaults();
        },

        // Inicializar CKEditor
        initCKEditor: function() {
            const el = document.getElementById('contenido_editor');
            if (!el) return;

            // Esperar a que CKEditor esté disponible
            if (typeof ClassicEditor === 'undefined') {
                setTimeout(() => this.initCKEditor(), 100);
                return;
            }

            const self = this;

            ClassicEditor.create(el, {
                language: 'es',
                toolbar: [
                    'undo', 'redo',
                    '|', 'bold', 'italic', 'underline', 'strikethrough',
                    '|', 'bulletedList', 'numberedList',
                    '|', 'link', 'blockQuote', 'insertTable',
                    '|', 'heading', 'removeFormat'
                ]
            }).then(editor => {
                self.ckeditorInstance = editor;
                editor.model.document.on('change:data', () => {
                    self.actualizarVistaPrevia();
                });
            }).catch(err => console.error('Error CKEditor:', err));
        },

        // Inicializar carga de logo
        initLogoUpload: function() {
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
                    self.actualizarVistaPrevia();
                };
                reader.readAsDataURL(file);
            });

            document.getElementById('btn_eliminar_logo').addEventListener('click', function() {
                self.logoBase64 = null;
                document.getElementById('logo_path').value = '';
                container.innerHTML = `
                    <div class="logo-placeholder">
                        <i class="fas fa-image fa-2x"></i>
                        <small class="d-block">Logo</small>
                    </div>
                `;
                this.classList.add('d-none');
                self.actualizarVistaPrevia();
            });
        },

        // Event listeners
        initEventListeners: function() {
            const self = this;

            // Actualizar vista previa
            document.getElementById('btn_actualizar_preview').addEventListener('click', () => {
                self.actualizarVistaPrevia();
            });

            // Cambios en configuración de página
            ['tamano_pagina', 'orientacion', 'margen_superior', 'margen_inferior',
             'margen_izquierdo', 'margen_derecho', 'leyenda_encabezado'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', () => self.actualizarVistaPrevia());
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
        },

        // Cargar valores por defecto
        loadDefaults: async function() {
            try {
                const resp = await fetch('/plantillas-documentos/defaults', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await resp.json();

                if (data.success) {
                    this.configActual = data.data;
                    this.actualizarVistaPrevia();
                }
            } catch (e) {
                console.error('Error cargando defaults:', e);
            }
        },

        // Obtener configuración actual
        obtenerConfiguracion: function() {
            return {
                encabezado: {
                    logo_path: this.logoBase64 || document.getElementById('logo_path').value || null,
                    leyenda: document.getElementById('leyenda_encabezado').value || null,
                },
                contenido: this.ckeditorInstance ? this.ckeditorInstance.getData() : '',
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
        },

        // Cargar configuración existente
        cargarConfiguracion: function(config) {
            this.configActual = config;

            // Encabezado
            if (config.encabezado) {
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

            // Contenido
            if (this.ckeditorInstance && config.contenido) {
                this.ckeditorInstance.setData(config.contenido);
            }

            // Márgenes
            if (config.margenes) {
                document.getElementById('margen_superior').value = config.margenes.superior || 2.0;
                document.getElementById('margen_inferior').value = config.margenes.inferior || 2.0;
                document.getElementById('margen_izquierdo').value = config.margenes.izquierdo || 2.5;
                document.getElementById('margen_derecho').value = config.margenes.derecho || 2.5;
            }

            // Página
            if (config.pagina) {
                document.getElementById('tamano_pagina').value = config.pagina.tamano || 'legal';
                document.getElementById('orientacion').value = config.pagina.orientacion || 'portrait';
            }

            this.actualizarVistaPrevia();
        },

        // Actualizar vista previa
        actualizarVistaPrevia: function() {
            const config = this.obtenerConfiguracion();
            const preview = document.getElementById('preview_page');

            // Actualizar tamaño de página
            const tamanos = {
                'legal': { w: 216, h: 356 },
                'a4': { w: 210, h: 297 },
                'letter': { w: 216, h: 279 },
                'oficio': { w: 216, h: 330 }
            };

            const dims = tamanos[config.pagina.tamano] || tamanos['legal'];
            const isLandscape = config.pagina.orientacion === 'landscape';

            // Escala para visualización (1mm = 1px aprox)
            const scale = 1;
            const width = (isLandscape ? dims.h : dims.w) * scale;
            const height = (isLandscape ? dims.w : dims.h) * scale;

            preview.style.width = width + 'px';
            preview.style.minHeight = height + 'px';

            // Márgenes (escalados)
            const ms = config.margenes.superior * scale;
            const mi = config.margenes.izquierdo * scale;
            const md = config.margenes.derecho * scale;
            const marginH = mi + md;

            preview.style.padding = `${ms}px ${md}px ${config.margenes.inferior * scale}px ${mi}px`;

            // Actualizar encabezado
            const logoEl = document.getElementById('preview_logo');
            const leyendaEl = document.getElementById('preview_leyenda');

            if (config.encabezado.logo_path) {
                logoEl.innerHTML = `<img src="${config.encabezado.logo_path}" alt="Logo">`;
            } else {
                logoEl.innerHTML = '';
            }

            if (config.encabezado.leyenda) {
                leyendaEl.innerHTML = config.encabezado.leyenda;
            } else {
                leyendaEl.innerHTML = '';
            }

            // Actualizar contenido
            const contentEl = document.getElementById('preview_content');
            if (config.contenido && config.contenido.trim() !== '') {
                contentEl.innerHTML = config.contenido;
            } else {
                contentEl.innerHTML = '<p class="text-muted text-center">El contenido aparecerá aquí...</p>';
            }

            // Actualizar info
            const orientacionText = isLandscape ? 'Horizontal' : 'Vertical';
            document.getElementById('info_pagina').textContent =
                `${config.pagina.tamano.toUpperCase()} - ${orientacionText}`;
            document.getElementById('info_margenes').textContent =
                `Márgenes: ${config.margenes.superior}/${config.margenes.inferior}/${config.margenes.izquierdo}/${config.margenes.derecho} cm`;
        },

        // Limpiar editor
        limpiar: function() {
            this.logoBase64 = null;
            document.getElementById('logo_path').value = '';
            document.getElementById('logo_container').innerHTML = `
                <div class="logo-placeholder">
                    <i class="fas fa-image fa-2x"></i>
                    <small class="d-block">Logo</small>
                </div>
            `;
            document.getElementById('btn_eliminar_logo').classList.add('d-none');
            document.getElementById('leyenda_encabezado').value = '';
            document.getElementById('nombre_plantilla').value = '';

            if (this.ckeditorInstance) {
                this.ckeditorInstance.setData('');
            }

            // Resetear configuración
            document.getElementById('margen_superior').value = 2.0;
            document.getElementById('margen_inferior').value = 2.0;
            document.getElementById('margen_izquierdo').value = 2.5;
            document.getElementById('margen_derecho').value = 2.5;
            document.getElementById('tamano_pagina').value = 'legal';
            document.getElementById('orientacion').value = 'portrait';

            this.actualizarVistaPrevia();
        },

        // Guardar nota (método por defecto, puede ser sobrescrito)
        guardarNota: async function(config) {
            console.log('Guardando nota:', config);
            alert('Configure el callback de guardar nota desde el módulo');
        },

        // Guardar plantilla (método por defecto)
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

        // Abrir modal
        abrir: function(callbackGuardar, callbackGuardarPlantilla) {
            this.callbackGuardar = callbackGuardar;
            this.callbackGuardarPlantilla = callbackGuardarPlantilla;
            const modal = new bootstrap.Modal(document.getElementById('{{ $id }}'));
            modal.show();
        },

        // Cerrar modal
        cerrar: function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('{{ $id }}'));
            if (modal) modal.hide();
        }
    };

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        window.EditorNotas['{{ $id }}'].init();
    });
</script>
