/**
 * Módulo de Notas Jurídicas
 * Flujo: Index (lista) → Formulario → Google Docs
 * Versión simplificada sin CKEditor - usa Google Docs para notas creadas
 */
document.addEventListener('DOMContentLoaded', function () {
    // ============================================
    // ELEMENTOS DEL DOM
    // ============================================

    // Paneles
    const panelList = document.getElementById('panel_list');
    const panelForm = document.getElementById('panel_form');
    const cardHeaderFiltros = document.getElementById('card_header_filtros');

    // Botones principales
    const btnFiltrar = document.getElementById('btn-filtrar');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
    const btnAdd = document.getElementById('btn_add');
    const btnVolver = document.getElementById('btn_volver');
    const btnLimpiar = document.getElementById('btn_limpiar');
    const form = document.getElementById('form_nota');

    // Filtros
    const inputAnio = document.getElementById('anio_filtro');
    const inputFechaDesde = document.getElementById('fecha_desde');
    const inputFechaHasta = document.getElementById('fecha_hasta');
    const inputPersonal = document.getElementById('personal_filtro');
    const inputNumero = document.getElementById('numero_filtro');
    const inputBusqueda = document.getElementById('busqueda_filtro');
    const inputEstado = document.getElementById('estado_filtro');

    // Campos del formulario
    const notaIdEl = document.getElementById('nota_id');
    const numeroEl = document.getElementById('numero');
    const anioEl = document.getElementById('anio');
    const fechaCreacionEl = document.getElementById('fecha_creacion');
    const personalIdEl = document.getElementById('personal_id');
    const tituloEl = document.getElementById('titulo');
    const descripcionEl = document.getElementById('descripcion');
    const configuracionEl = document.getElementById('configuracion');
    const observacionEl = document.getElementById('observacion');
    const notaReferenciaEl = document.getElementById('nota_referencia_id');
    const estadoFormEl = document.getElementById('estado');
    // Ya no hay radio buttons de tipo - ambos campos pueden coexistir
    const googleDocIdEl = document.getElementById('google_doc_id');
    const googleDocLinkEl = document.getElementById('google_doc_link');

    // Elementos de archivo adjunto
    const archivoNotaEl = document.getElementById('archivo_nota');
    const archivoPreviewContainer = document.getElementById('archivo_preview_container');
    const archivoIcon = document.getElementById('archivo_icon');
    const archivoNombreEl = document.getElementById('archivo_nombre');
    const btnEliminarArchivo = document.getElementById('btn_eliminar_archivo');
    const imagenPreview = document.getElementById('imagen_preview');
    const imgPreviewEl = document.getElementById('img_preview');
    const pdfPreview = document.getElementById('pdf_preview');
    const archivoBase64El = document.getElementById('archivo_base64');
    const archivoNombreRealEl = document.getElementById('archivo_nombre_real');
    const archivoTipoEl = document.getElementById('archivo_tipo');

    // Plantilla
    const esPlantillaEl = document.getElementById('es_plantilla');
    const nombrePlantillaEl = document.getElementById('nombre_plantilla');
    const contenedorNombrePlantilla = document.getElementById('contenedor_nombre_plantilla');
    const contenedorPreviewArchivo = document.getElementById('contenedor_preview_archivo');
    const archivoSinPreview = document.getElementById('archivo_sin_preview');

    // Título del formulario
    const tituloFormulario = document.getElementById('titulo_formulario');

    // Tabla
    const tbody = document.getElementById('tbody-notas');

    // ============================================
    // VARIABLES DE ESTADO
    // ============================================
    let currentPage = 1;
    let lastPage = 1;
    let perPage = 15;
    let notaEditandoId = null;
    let moduloId = null;
    let guardandoNota = false; // Bandera para evitar múltiples envíos

    const csrfToken = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const routes = window.laravelRoutes || {};

    // ============================================
    // INICIALIZACIÓN
    // ============================================
    async function init() {
        await cargarModuloId();
        cargarNotas(1);
        cargarPlantillas();
        initSelect2();
        setupEventListeners();
    }

    async function cargarModuloId() {
        try {
            const resp = await fetch('/plantillas-documentos/por-modulo?modulo_url=laravel-notas-juridicas', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();
            if (data.success && data.modulo_id) {
                moduloId = data.modulo_id;
                window.moduloId = moduloId;
            } else {
                console.log('Módulo no registrado, las plantillas se guardarán sin módulo asociado');
            }
        } catch (e) {
            console.log('No se pudo cargar módulo ID (esto es normal si el módulo no está registrado)');
        }
    }

    function initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        $(personalIdEl).select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#panel_form'),
            placeholder: '- SELECCIONAR -',
            ajax: {
                url: '/personal/buscar',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { query: params.term };
                },
                processResults: function(data) {
                    const results = (data || []).map(function(item) {
                        return {
                            id: item.id,
                            text: item.value,
                            Apellido: item.tokens ? item.tokens[0] : '',
                            Nombre: item.tokens ? item.tokens[1] : ''
                        };
                    });
                    return { results: results };
                },
                cache: true
            },
            minimumInputLength: 2,
            templateResult: function(data) {
                if (data.loading) return data.text;
                return `${data.Apellido || ''}, ${data.Nombre || ''} (${data.DNI || ''})`;
            },
            templateSelection: function(data) {
                return data.text || `${data.Apellido || ''}, ${data.Nombre || ''}`;
            }
        });

        $(notaReferenciaEl).select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#panel_form'),
            placeholder: '- SIN REFERENCIA -',
            allowClear: true,
            ajax: {
                url: routes.notasJuridicasBuscar,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term, page: params.page || 1 };
                },
                processResults: function(data) {
                    return {
                        results: data.results || [],
                        pagination: data.pagination || { more: false }
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    function setupEventListeners() {
        // Filtros
        if (btnFiltrar) {
            btnFiltrar.addEventListener('click', function(e) {
                e.preventDefault();
                cargarNotas(1);
            });
        }

        if (btnLimpiarFiltros) {
            btnLimpiarFiltros.addEventListener('click', limpiarFiltros);
        }

        // Navegación: Index → Formulario
        if (btnAdd) {
            btnAdd.addEventListener('click', mostrarFormulario);
        }

        // Navegación: Formulario → Index
        if (btnVolver) {
            btnVolver.addEventListener('click', mostrarLista);
        }

        // Limpiar formulario
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', limpiarFormulario);
        }

        // Botón Exportar Excel
        const btnExportarExcel = document.getElementById('btn_exportar_excel');
        if (btnExportarExcel) {
            btnExportarExcel.addEventListener('click', exportarExcel);
        }

        // Botón "Generar Notas" - Crear documento en Google Drive
        const btnAbrirDocDrive = document.getElementById('btn_abrir_doc_drive');
        if (btnAbrirDocDrive) {
            btnAbrirDocDrive.addEventListener('click', crearDocumentoDrive);
        }

        // Google Docs - Mostrar selector de plantillas al marcar checkbox
        const crearGoogleDocEl = document.getElementById('crear_google_doc');
        const contenedorPlantillasDrive = document.getElementById('contenedor_plantillas_drive');
        const googleDocTemplateEl = document.getElementById('google_doc_template_id');

        if (crearGoogleDocEl) {
            crearGoogleDocEl.addEventListener('change', function() {
                if (this.checked) {
                    if (contenedorPlantillasDrive) {
                        contenedorPlantillasDrive.classList.remove('d-none');
                    }
                    cargarPlantillasDrive();
                } else {
                    if (contenedorPlantillasDrive) {
                        contenedorPlantillasDrive.classList.add('d-none');
                    }
                }
            });
        }

        // Cargar plantillas desde Google Drive
        async function cargarPlantillasDrive() {
            if (!googleDocTemplateEl || !window.laravelRoutes.notasJuridicasPlantillasDrive) return;

            try {
                const response = await fetch(window.laravelRoutes.notasJuridicasPlantillasDrive, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();

                if (data.success && data.data) {
                    googleDocTemplateEl.innerHTML = '<option value="">- Seleccionar Plantilla -</option>';
                    data.data.forEach(doc => {
                        const option = document.createElement('option');
                        option.value = doc.id;
                        option.textContent = doc.name;
                        googleDocTemplateEl.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error cargando plantillas Drive:', error);
            }
        }

        // Selección de archivo
        if (archivoNotaEl) {
            archivoNotaEl.addEventListener('change', manejarSeleccionArchivo);
        }

        // Eliminar archivo
        if (btnEliminarArchivo) {
            btnEliminarArchivo.addEventListener('click', eliminarArchivoSeleccionado);
        }

        // Guardar formulario
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                guardarNota();
            });
        }

        // Filtro por año
        if (inputAnio) {
            inputAnio.addEventListener('change', function() {
                cargarNotas(1);
            });
        }

        // Cambio de fecha
        if (fechaCreacionEl) {
            fechaCreacionEl.addEventListener('change', function() {
                const fecha = new Date(this.value);
                if (anioEl) anioEl.value = fecha.getFullYear();
                cargarProximoNumero();
            });
        }

        // Checkbox de plantilla
        if (esPlantillaEl) {
            esPlantillaEl.addEventListener('change', function() {
                if (this.checked) {
                    contenedorNombrePlantilla.classList.remove('d-none');
                    nombrePlantillaEl.focus();
                } else {
                    contenedorNombrePlantilla.classList.add('d-none');
                    nombrePlantillaEl.value = '';
                }
            });
        }

        // Validar número cuando cambia
        if (numeroEl) {
            let timeoutNumero = null;
            numeroEl.addEventListener('input', function() {
                clearTimeout(timeoutNumero);
                timeoutNumero = setTimeout(async () => {
                    const numero = parseInt(this.value);
                    const anio = parseInt(anioEl?.value || new Date().getFullYear());

                    if (numero > 0 && anio > 0) {
                        try {
                            const resp = await fetch(routes.notasJuridicasVerificarNumero + '?' + new URLSearchParams({
                                numero: numero,
                                anio: anio,
                                excluir_id: notaEditandoId || ''
                            }));
                            const data = await resp.json();

                            if (data.success && data.existe) {
                                mostrarAlertaValidacion(data.mensaje || 'Este número ya está en uso', 'Número duplicado');
                                this.classList.add('is-invalid');
                            } else {
                                this.classList.remove('is-invalid');
                            }
                        } catch (e) {
                            console.error('Error al verificar número:', e);
                        }
                    }
                }, 500); // Esperar 500ms después de que el usuario deje de escribir
            });
        }

        // Búsqueda con Enter
        [inputPersonal, inputNumero, inputBusqueda].forEach(function(input) {
            if (input) {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        cargarNotas(1);
                    }
                });
            }
        });
    }

    // ============================================
    // NAVEGACIÓN
    // ============================================
    function mostrarFormulario() {
        limpiarFormulario();

        // Ocultar header de filtros
        if (cardHeaderFiltros) cardHeaderFiltros.style.display = 'none';

        // Mostrar formulario
        panelList.classList.add('d-none');
        panelForm.classList.remove('d-none');

        // Reinicializar Select2
        setTimeout(() => {
            if (typeof $.fn.select2 !== 'undefined') {
                $(personalIdEl).select2('destroy');
                $(notaReferenciaEl).select2('destroy');
                initSelect2();
            }
        }, 100);

        cargarProximoNumero();
    }

    function mostrarLista() {
        if (cardHeaderFiltros) cardHeaderFiltros.style.display = 'block';

        panelForm.classList.add('d-none');
        panelList.classList.remove('d-none');

        limpiarFormulario();
        cargarNotas(currentPage);
    }

    function limpiarFormulario() {
        notaEditandoId = null;

        if (form) form.reset();

        // Resetear campos específicos
        if (notaIdEl) notaIdEl.value = '';
        if (anioEl) anioEl.value = new Date().getFullYear();
        if (fechaCreacionEl) fechaCreacionEl.value = new Date().toISOString().split('T')[0];
        if (estadoFormEl) estadoFormEl.value = '1'; // ID de PENDIENTE
        if (descripcionEl) descripcionEl.value = '';
        if (configuracionEl) configuracionEl.value = '';
        if (googleDocIdEl) googleDocIdEl.value = '';
        if (googleDocLinkEl) googleDocLinkEl.value = '';

        // Resetear Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $(personalIdEl).val('').trigger('change');
            $(notaReferenciaEl).val('').trigger('change');
        }

        // Resetear campos de archivo
        eliminarArchivoSeleccionado();

        // Resetear plantilla
        if (esPlantillaEl) esPlantillaEl.checked = false;
        if (contenedorNombrePlantilla) contenedorNombrePlantilla.classList.add('d-none');
        if (nombrePlantillaEl) nombrePlantillaEl.value = '';

        // Resetear título
        if (tituloFormulario) {
            tituloFormulario.innerHTML = '<i class="fas fa-file-alt"></i> Nueva Nota Jurídica';
        }

        // Resetear botón submit
        const btnSubmit = form?.querySelector('button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
        }

        // Resetear interfaz de Google Doc
        resetearInterfazGoogleDoc();
    }

    // Función para resetear la interfaz del Google Doc
    function resetearInterfazGoogleDoc() {
        const btnDocDrive = document.getElementById('btn_abrir_doc_drive');
        const estadoDiv = document.getElementById('estado_google_doc');
        const enlaceDiv = document.getElementById('enlace_google_doc');

        if (btnDocDrive) {
            btnDocDrive.innerHTML = '<i class="fab fa-google-drive"></i> <span id="btn_doc_text">Generar Documento</span>';
            btnDocDrive.onclick = crearDocumentoDrive;
            btnDocDrive.classList.remove('btn-success');
            btnDocDrive.classList.add('btn-primary');
        }

        if (estadoDiv) {
            estadoDiv.innerHTML = `
                <div class="alert alert-info py-2 mb-0">
                    <i class="fas fa-info-circle"></i>
                    El documento Google Docs es opcional. Haga clic en "Generar Documento" para crear uno, o guarde la nota sin contenido adjunto.
                </div>
            `;
        }

        if (enlaceDiv) {
            enlaceDiv.classList.add('d-none');
        }
    }

    // ============================================
    // DATOS
    // ============================================
    async function cargarNotas(page = 1) {
        const params = new URLSearchParams();

        if (inputAnio && inputAnio.value) params.append('anio', inputAnio.value);
        if (inputFechaDesde && inputFechaDesde.value) params.append('fecha_desde', inputFechaDesde.value);
        if (inputFechaHasta && inputFechaHasta.value) params.append('fecha_hasta', inputFechaHasta.value);
        if (inputPersonal && inputPersonal.value.trim()) params.append('personal', inputPersonal.value.trim());
        if (inputNumero && inputNumero.value.trim()) params.append('numero', inputNumero.value.trim());
        if (inputBusqueda && inputBusqueda.value.trim()) params.append('busqueda', inputBusqueda.value.trim());
        if (inputEstado && inputEstado.value) params.append('estado', inputEstado.value);

        params.append('page', page);
        params.append('per_page', perPage);

        try {
            const url = `${routes.notasJuridicasFiltrar}?${params.toString()}`;
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

            const data = await resp.json();
            currentPage = data.current_page || 1;
            lastPage = data.last_page || 1;

            renderTabla(data.data || []);
            renderPaginacion(data);

        } catch (e) {
            console.error('Error cargando notas:', e);
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar las notas</td></tr>';
        }
    }

    async function cargarProximoNumero() {
        const anio = anioEl ? anioEl.value : new Date().getFullYear();

        try {
            const resp = await fetch(`${routes.notasJuridicasProximoNumero}?anio=${anio}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();

            if (data.success && numeroEl) {
                numeroEl.value = data.numero;
            }
        } catch (e) {
            console.error('Error obteniendo número:', e);
        }
    }

    function renderTabla(items) {
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No se encontraron notas</td></tr>';
            return;
        }

        items.forEach(function(nota) {
            const tr = document.createElement('tr');

            const numeroCompleto = `${nota.numero}/${nota.anio}`;
            const fechaFormateada = formatearFecha(nota.fecha_creacion);
            const personalNombre = nota.personal ? `${nota.personal.Apellido}, ${nota.personal.Nombre}` : '-';

            // Determinar iconos según lo que tenga la nota
            const tieneGoogleDoc = !!(nota.google_doc_id || nota.google_doc_link);
            const tieneArchivo = !!(nota.archivo_path || nota.google_drive_link || nota.google_drive_file_id);

            let tipoBadges = '';
            if (tieneGoogleDoc) {
                tipoBadges += `<span class="badge bg-primary me-1"><i class="fas fa-edit"></i> Docs</span>`;
            }
            if (tieneArchivo) {
                tipoBadges += `<span class="badge bg-success"><i class="fas fa-paperclip"></i> Archivo</span>`;
            }
            if (!tipoBadges) {
                tipoBadges = `<span class="badge bg-secondary">Sin contenido</span>`;
            }

            // Usar estado_texto y estado_badge que vienen del servidor (IDs numéricos)
            const estadoLabel = nota.estado_texto || 'PENDIENTE';
            const estadoClass = nota.estado_badge || 'bg-warning';
            const creadorNombre = nota.creador ? `${nota.creador.Apellido}, ${nota.creador.Nombre}` : '-';

            tr.innerHTML = `
                <td class="text-center"><strong>${numeroCompleto}</strong></td>
                <td class="text-center">${fechaFormateada}</td>
                <td>${nota.titulo || ''}</td>
                <td>${personalNombre}</td>
                <td class="text-center">${tipoBadges}</td>
                <td class="text-center">
                    <span class="badge ${estadoClass}">${estadoLabel}</span>
                </td>
                <td class="text-center" style="font-size: 0.8em;">${creadorNombre}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-primary btn-editar" data-id="${nota.idNotaJuridica}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${nota.google_doc_link ? `<a class="btn btn-warning" target="_blank" href="${nota.google_doc_link}" title="Abrir en Google Docs"><i class="fab fa-google-drive"></i></a>` : ''}
                        ${nota.google_drive_link ? `<a class="btn btn-info" target="_blank" href="${nota.google_drive_link}" title="Ver en Drive"><i class="fas fa-cloud"></i></a>` : ''}
                        ${nota.archivo_path ? `<a class="btn btn-info" target="_blank" href="/${nota.archivo_path}" title="Abrir archivo adjunto"><i class="fas fa-paperclip"></i></a>` : ''}
                        ${nota.observacion ? `<button class="btn btn-secondary btn-ver-obs" data-nota="${numeroCompleto}" data-obs="${nota.observacion.replace(/"/g, '&quot;')}" title="Ver observación"><i class="fas fa-info-circle"></i></button>` : ''}
                        <button class="btn btn-danger btn-eliminar" data-id="${nota.idNotaJuridica}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        });

        setupActionButtons();
    }

    function setupActionButtons() {
        // Editar
        document.querySelectorAll('.btn-editar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                editarNota(this.dataset.id);
            });
        });

        // Eliminar
        document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                if (confirm('¿Está seguro de eliminar esta nota jurídica?')) {
                    await eliminarNota(this.dataset.id);
                }
            });
        });

        // Ver observación
        document.querySelectorAll('.btn-ver-obs').forEach(function(btn) {
            btn.addEventListener('click', function() {
                mostrarModalObservacion(this.dataset.nota, this.dataset.obs);
            });
        });
    }

    function renderPaginacion(data) {
        const infoPaginacion = document.getElementById('info-paginacion');
        const controlesPaginacion = document.getElementById('paginacion-controles');

        if (!data || !infoPaginacion || !controlesPaginacion) return;

        const { current_page, last_page, from, to, total } = data;

        if (total > 0) {
            infoPaginacion.textContent = `Mostrando ${from} a ${to} de ${total} resultados`;
        } else {
            infoPaginacion.textContent = 'No se encontraron resultados';
        }

        controlesPaginacion.innerHTML = '';
        if (last_page <= 1) return;

        // Anterior
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${current_page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${current_page - 1}">Anterior</a>`;
        controlesPaginacion.appendChild(prevLi);

        // Números
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === current_page ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            controlesPaginacion.appendChild(pageLi);
        }

        // Siguiente
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${current_page === last_page ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${current_page + 1}">Siguiente</a>`;
        controlesPaginacion.appendChild(nextLi);

        // Event listeners
        controlesPaginacion.querySelectorAll('a.page-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page >= 1 && page <= last_page && page !== current_page) {
                    cargarNotas(page);
                }
            });
        });
    }

    async function cargarPlantillas() {
        try {
            const resp = await fetch(routes.notasJuridicasPlantillas, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();

            const listaPlantillas = document.getElementById('lista_plantillas');
            if (!listaPlantillas) return;

            if (data.success && data.data && data.data.length > 0) {
                listaPlantillas.innerHTML = data.data.map(p => {
                    const nombre = p.nombre || p.nombre_plantilla || 'Sin nombre';
                    const id = p.idPlantilla || p.idNotaJuridica;
                    return `
                        <li>
                            <a class="dropdown-item plantilla-item" href="#" data-id="${id}">
                                <i class="fas fa-file-alt"></i> ${nombre}
                            </a>
                        </li>
                    `;
                }).join('');

                listaPlantillas.querySelectorAll('.plantilla-item').forEach(item => {
                    item.addEventListener('click', async function(e) {
                        e.preventDefault();
                        await usarPlantilla(this.dataset.id);
                    });
                });
            } else {
                listaPlantillas.innerHTML = '<li><a class="dropdown-item disabled" href="#">Sin plantillas guardadas</a></li>';
            }
        } catch (e) {
            console.error('Error cargando plantillas:', e);
        }
    }

    async function usarPlantilla(id) {
        try {
            const resp = await fetch(`${routes.notasJuridicasBase}/plantillas/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!json.success) {
                mostrarMensaje('Error al cargar la plantilla', 'error');
                return;
            }

            // Mostrar formulario primero
            mostrarFormulario();

            // Cargar configuración
            if (json.configuracion) {
                if (descripcionEl) descripcionEl.value = json.configuracion.contenido || '';
                if (configuracionEl) configuracionEl.value = JSON.stringify(json.configuracion);
            } else if (json.data) {
                if (tituloEl) tituloEl.value = json.data.titulo || '';
            }

            mostrarMensaje('Plantilla cargada correctamente', 'success');

        } catch (e) {
            console.error('Error usando plantilla:', e);
            mostrarMensaje('Error al cargar la plantilla', 'error');
        }
    }

    async function editarNota(id) {
        try {
            const resp = await fetch(`${routes.notasJuridicasBase}/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!json.success) {
                mostrarMensaje('Error al cargar la nota', 'error');
                return;
            }

            // Mostrar formulario
            if (cardHeaderFiltros) cardHeaderFiltros.style.display = 'none';
            panelList.classList.add('d-none');
            panelForm.classList.remove('d-none');

            // Reinicializar Select2
            setTimeout(() => {
                if (typeof $.fn.select2 !== 'undefined') {
                    $(personalIdEl).select2('destroy');
                    $(notaReferenciaEl).select2('destroy');
                    initSelect2();
                }
            }, 100);

            notaEditandoId = id;
            const nota = json.data;

            // Cargar datos
            if (notaIdEl) notaIdEl.value = nota.idNotaJuridica;
            if (numeroEl) numeroEl.value = nota.numero;
            if (anioEl) anioEl.value = nota.anio;
            // Asegurar formato correcto para input date (YYYY-MM-DD)
            if (fechaCreacionEl) {
                const fecha = nota.fecha_creacion ? nota.fecha_creacion.toString().split('T')[0].split(' ')[0] : '';
                fechaCreacionEl.value = fecha;
            }
            if (tituloEl) tituloEl.value = nota.titulo || '';
            if (observacionEl) observacionEl.value = nota.observacion || '';
            
            // Asignar estado (ID numérico)
            if (estadoFormEl && nota.estado !== undefined && nota.estado !== null) {
                estadoFormEl.value = nota.estado;
                console.log('Estado asignado (ID):', nota.estado, 'Texto:', nota.estado_texto);
            }
            
            if (googleDocIdEl) googleDocIdEl.value = nota.google_doc_id || '';
            if (googleDocLinkEl) googleDocLinkEl.value = nota.google_doc_link || '';

            console.log('=== DATOS CARGADOS PARA EDICIÓN ===');
            console.log('Nota ID:', nota.idNotaJuridica);
            console.log('Fecha creación cargada:', fechaCreacionEl?.value);
            console.log('Título cargado:', tituloEl?.value);
            console.log('Estado ID (DB):', nota.estado, 'Texto:', nota.estado_texto);
            console.log('Estado del formulario:', estadoFormEl?.value);
            console.log('Google Doc ID cargado:', googleDocIdEl?.value);
            console.log('=====================================');

            // Si tiene Google Doc, mostrar enlace y actualizar interfaz
            if (nota.google_doc_link || nota.google_doc_id) {
                actualizarEstadoGoogleDoc(nota.google_doc_link || `https://docs.google.com/document/d/${nota.google_doc_id}/edit`);
            }

            // Cargar archivo si existe (independiente del tipo)
            if (nota.archivo_path) {
                if (archivoNombreEl) archivoNombreEl.textContent = nota.archivo_path.split('/').pop();
                if (archivoPreviewContainer) archivoPreviewContainer.classList.remove('d-none');

                // Establecer enlace para abrir el archivo
                const btnAbrirArchivo = document.getElementById('btn_abrir_archivo');
                if (btnAbrirArchivo) {
                    btnAbrirArchivo.href = '/' + nota.archivo_path;
                }

                // Si es imagen, mostrar preview
                const extension = nota.archivo_path.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                    if (archivoIcon) archivoIcon.className = 'fas fa-file-image fa-2x text-success';
                    if (imagenPreview && imgPreviewEl) {
                        imagenPreview.classList.remove('d-none');
                        imgPreviewEl.src = '/' + nota.archivo_path;
                    }
                } else {
                    if (archivoIcon) archivoIcon.className = 'fas fa-file-pdf fa-2x text-danger';
                    if (imagenPreview) imagenPreview.classList.add('d-none');
                }
            }

            // Cargar personal
            if (nota.personal_id && typeof $.fn.select2 !== 'undefined') {
                setTimeout(() => {
                    const opcion = new Option(
                        nota.personal ? `${nota.personal.Apellido}, ${nota.personal.Nombre}` : nota.personal_id,
                        nota.personal_id,
                        true,
                        true
                    );
                    $(personalIdEl).append(opcion).trigger('change');
                }, 200);
            }

            // Cargar referencia
            if (nota.nota_referencia_id && typeof $.fn.select2 !== 'undefined') {
                setTimeout(() => {
                    const opcion = new Option(
                        `Nota ${nota.nota_referencia?.numero_completo || ''} - ${nota.nota_referencia?.titulo || ''}`,
                        nota.nota_referencia_id,
                        true,
                        true
                    );
                    $(notaReferenciaEl).append(opcion).trigger('change');
                }, 200);
            }

            // Cargar configuración
            if (json.configuracion) {
                if (descripcionEl) descripcionEl.value = json.configuracion.contenido || '';
                if (configuracionEl) configuracionEl.value = JSON.stringify(json.configuracion);
            } else {
                if (descripcionEl) descripcionEl.value = nota.descripcion || '';
            }

            // Cambiar título y botón
            if (tituloFormulario) {
                tituloFormulario.innerHTML = '<i class="fas fa-edit"></i> Editar Nota Jurídica';
            }
            const btnSubmit = form?.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.innerHTML = '<i class="fas fa-save"></i> Actualizar';
            }

            // Cargar historial de novedades
            cargarHistorial(id);

        } catch (e) {
            console.error('Error cargando nota:', e);
            mostrarMensaje('Error al cargar la nota', 'error');
        }
    }

    async function guardarNota() {
        // Evitar múltiples envíos simultáneos
        if (guardandoNota) {
            console.log('Ya se está guardando la nota...');
            return;
        }
        
        // Deshabilitar el botón de guardar visualmente
        const btnSubmit = form?.querySelector('button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.dataset.textoOriginal = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        }
        
        guardandoNota = true;

        // Determinar tipo según lo que tenga la nota
        // Para notas existentes, mantener el tipo lógico basado en google_doc_id/archivo
        const tieneGoogleDoc = !!(googleDocIdEl?.value || googleDocLinkEl?.value);
        const tieneArchivo = !!(archivoBase64El?.value);
        let tipo = null;

        if (tieneGoogleDoc && tieneArchivo) {
            tipo = 'completa'; // Tiene ambos
        } else if (tieneGoogleDoc) {
            tipo = 'creada';
        } else if (tieneArchivo) {
            tipo = 'adjunta';
        }
        // Si tipo es null pero estamos editando, el backend recalculará basado en datos existentes

        const payload = {
            titulo: tituloEl?.value || '',
            descripcion: descripcionEl?.value || '',
            configuracion: configuracionEl?.value ? JSON.parse(configuracionEl.value) : null,
            observacion: observacionEl?.value || '',
            fecha_creacion: fechaCreacionEl?.value || '',
            personal_id: personalIdEl?.value || null,
            nota_referencia_id: notaReferenciaEl?.value || null,
            tipo: tipo, // Puede ser null, creada, adjunta o completa
            estado: parseInt(estadoFormEl?.value) || 1, // ID numérico
            es_plantilla: esPlantillaEl?.checked || false,
            nombre_plantilla: nombrePlantillaEl?.value || null,
            // Archivo adjunto (si existe)
            archivo_base64: archivoBase64El?.value || null,
            archivo_nombre: archivoNombreRealEl?.value || null,
            // Google Doc (si existe)
            google_doc_id: googleDocIdEl?.value || null,
            google_doc_link: googleDocLinkEl?.value || null
        };

        // Datos de Google Docs para crear nuevo
        const crearGoogleDocEl = document.getElementById('crear_google_doc');
        const googleDocTemplateEl = document.getElementById('google_doc_template_id');

        if (crearGoogleDocEl?.checked) {
            payload.crear_google_doc = true;
            payload.google_doc_template_id = googleDocTemplateEl?.value || null;
        }

        // Validación
        if (!payload.titulo) {
            mostrarAlertaValidacion('El título es obligatorio', 'Campo requerido');
            tituloEl?.focus();
            guardandoNota = false;
            // Restaurar el botón
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnSubmit.dataset.textoOriginal || '<i class="fas fa-save"></i> Guardar';
            }
            return;
        }

        // NOTA: Ya no es obligatorio tener Google Doc o archivo adjunto
        // El usuario puede guardar notas sin contenido adjunto

        // Log para depuración de edición de estado
        if (notaEditandoId) {
            console.log('Editando nota existente:', {
                id: notaEditandoId,
                estadoID: payload.estado,
                estadoTexto: window.estadosNotas?.[payload.estado] || 'N/A',
                tipo: payload.tipo
            });
        }

        const url = notaEditandoId
            ? `${routes.notasJuridicasBase}/${notaEditandoId}`
            : routes.notasJuridicasBase;
        const method = notaEditandoId ? 'PUT' : 'POST';

        console.log('=== ENVIANDO AL SERVIDOR ===');
        console.log('URL:', url);
        console.log('Método:', method);
        console.log('Payload:', payload);
        console.log('============================');

        try {
            const resp = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const json = await resp.json();

            if (json.success) {
                mostrarMensaje(notaEditandoId ? 'Nota actualizada' : 'Nota guardada exitosamente', 'success');

                // Si se creó un Google Doc, mostrar enlace
                if (json.data?.google_doc_link) {
                    const abrir = confirm('Documento creado en Google Drive.\n\n¿Desea abrirlo ahora?');
                    if (abrir) {
                        window.open(json.data.google_doc_link, '_blank');
                    }
                }

                mostrarLista();
                cargarPlantillas();
            } else {
                mostrarMensaje(json.message || 'Error al guardar la nota', 'error');
            }
        } catch (e) {
            console.error('Error guardando nota:', e);
            mostrarMensaje('Error al guardar la nota', 'error');
        } finally {
            // Siempre resetear la bandera de guardado y el botón, incluso si hay error
            guardandoNota = false;
            
            // Restaurar el botón de guardar
            const btnSubmitRestore = form?.querySelector('button[type="submit"]');
            if (btnSubmitRestore) {
                btnSubmitRestore.disabled = false;
                if (btnSubmitRestore.dataset.textoOriginal) {
                    btnSubmitRestore.innerHTML = btnSubmitRestore.dataset.textoOriginal;
                } else {
                    btnSubmitRestore.innerHTML = notaEditandoId 
                        ? '<i class="fas fa-save"></i> Actualizar' 
                        : '<i class="fas fa-save"></i> Guardar';
                }
            }
        }
    }

    async function eliminarNota(id) {
        try {
            const resp = await fetch(`${routes.notasJuridicasBase}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const json = await resp.json();

            if (json.success) {
                cargarNotas(currentPage);
                mostrarMensaje('Nota eliminada', 'success');
            } else {
                mostrarMensaje(json.message || 'Error al eliminar la nota', 'error');
            }
        } catch (e) {
            console.error('Error eliminando nota:', e);
            mostrarMensaje('Error al eliminar la nota', 'error');
        }
    }

    // ============================================
    // ACTUALIZAR INTERFAZ DOCUMENTO CREADO
    // ============================================
    function actualizarInterfazDocCreado(docLink, existe = false) {
        const btn = document.getElementById('btn_abrir_doc_drive');
        const estadoDiv = document.getElementById('estado_google_doc');
        const enlaceDiv = document.getElementById('enlace_google_doc');
        const linkAbrirDoc = document.getElementById('link_abrir_doc');

        // Actualizar el botón principal
        if (btn) {
            btn.innerHTML = '<i class="fas fa-external-link-alt"></i> Abrir Documento';
            btn.onclick = function() {
                window.open(docLink, '_blank');
            };
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
        }

        // Actualizar el estado
        if (estadoDiv) {
            if (existe) {
                estadoDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Documento encontrado en Google Drive.</strong><br>
                        El documento ya existe y está vinculado a esta nota.
                    </div>
                `;
            } else {
                estadoDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Documento creado exitosamente.</strong><br>
                        El documento está listo para editar en Google Docs.
                    </div>
                `;
            }
        }

        // Mostrar el enlace directo
        if (enlaceDiv) {
            enlaceDiv.classList.remove('d-none');
            if (linkAbrirDoc) {
                linkAbrirDoc.href = docLink;
            }
        }
    }

    // ============================================
    // AUXILIARES
    // ============================================
    function limpiarFiltros() {
        if (inputAnio) inputAnio.value = new Date().getFullYear();
        if (inputFechaDesde) inputFechaDesde.value = '';
        if (inputFechaHasta) inputFechaHasta.value = '';
        if (inputPersonal) inputPersonal.value = '';
        if (inputNumero) inputNumero.value = '';
        if (inputBusqueda) inputBusqueda.value = '';
        if (inputEstado) inputEstado.value = '';

        cargarNotas(1);
    }

    function mostrarModalObservacion(numeroNota, observacion) {
        document.getElementById('modalNotaNumero').textContent = numeroNota || 'N/A';
        document.getElementById('modalObservacionTexto').textContent = observacion || 'Sin observación';

        const modal = document.getElementById('modalObservacion');
        if (window.bootstrap) {
            new bootstrap.Modal(modal).show();
        } else if (window.jQuery) {
            jQuery(modal).modal('show');
        }
    }

    function mostrarMensaje(mensaje, tipo = 'info') {
        // Intentar usar toastr si está disponible
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3000
            };
            toastr[tipo === 'success' ? 'success' : (tipo === 'error' ? 'error' : 'info')](mensaje);
            return;
        }
        // Fallback: crear toast manual
        const toast = document.createElement('div');
        toast.className = `alert alert-${tipo === 'success' ? 'success' : 'danger'} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
        toast.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${mensaje}
            <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
        `;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 3000);
    }

    // Variable para rastrear alertas activas y evitar duplicados
    let alertaActiva = null;

    /**
     * Muestra una alerta de validación elegante (no desaparece automáticamente)
     * @param {string} mensaje - Mensaje a mostrar
     * @param {string} titulo - Título opcional
     */
    function mostrarAlertaValidacion(mensaje, titulo = 'Atención') {
        // Crear una clave única para esta alerta
        const claveAlerta = `${titulo}:${mensaje}`;
        
        // Si ya hay una alerta activa con el mismo mensaje, no mostrar otra
        if (alertaActiva === claveAlerta) {
            return;
        }
        
        // Marcar alerta como activa
        alertaActiva = claveAlerta;
        
        // Intentar usar toastr si está disponible
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                progressBar: false,
                positionClass: 'toast-top-right',
                timeOut: 5000,       // 5 segundos para que desaparezca
                extendedTimeOut: 1000,
                tapToDismiss: true,  // Se cierra al hacer clic
                onclick: function() { toastr.clear(); },
                onHidden: function() { alertaActiva = null; }  // Liberar al cerrar
            };
            toastr.warning(mensaje, titulo);
            return;
        }
        // Fallback: usar el toast manual
        mostrarMensaje(mensaje, 'warning');
        
        // Liberar la alerta después de 5 segundos
        setTimeout(() => { alertaActiva = null; }, 5000);
    }

    function formatearFecha(fecha) {
        if (!fecha) return '';
        const date = new Date(fecha);
        return date.toLocaleDateString('es-AR');
    }

    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // ============================================
    // FUNCIONES PARA GOOGLE DOC
    // ============================================
    function actualizarEstadoGoogleDoc(link, esNuevo = false) {
        const btnDocDrive = document.getElementById('btn_abrir_doc_drive');
        const estadoDiv = document.getElementById('estado_google_doc');
        const enlaceDiv = document.getElementById('enlace_google_doc');
        const linkAbrirDoc = document.getElementById('link_abrir_doc');

        if (btnDocDrive && link) {
            btnDocDrive.innerHTML = '<i class="fas fa-external-link-alt"></i> Abrir Documento';
            btnDocDrive.onclick = function() {
                window.open(link, '_blank');
            };
            btnDocDrive.classList.remove('btn-primary');
            btnDocDrive.classList.add('btn-success');
        }

        // Actualizar mensaje informativo
        if (estadoDiv) {
            estadoDiv.innerHTML = `
                <div class="alert alert-success py-2 mb-0">
                    <i class="fas fa-check-circle"></i>
                    <strong>Documento vinculado.</strong> Puede cambiar el estado de la nota libremente.
                </div>
            `;
        }

        // Mostrar enlace directo
        if (enlaceDiv) {
            enlaceDiv.classList.remove('d-none');
            if (linkAbrirDoc) {
                linkAbrirDoc.href = link;
            }
        }
    }

    function manejarSeleccionArchivo(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validar tamaño (máx 10MB)
        if (file.size > 10 * 1024 * 1024) {
            mostrarAlertaValidacion('El archivo es demasiado grande. Máximo permitido: 10MB', 'Archivo muy grande');
            e.target.value = '';
            return;
        }

        // Validar tipo
        const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!tiposPermitidos.includes(file.type)) {
            mostrarAlertaValidacion('Tipo de archivo no permitido. Use PDF o imágenes (JPG, PNG, GIF)', 'Formato inválido');
            e.target.value = '';
            return;
        }

        // Leer archivo como base64
        const reader = new FileReader();
        reader.onload = function(event) {
            const base64 = event.target.result;

            // Guardar datos
            if (archivoBase64El) archivoBase64El.value = base64;
            if (archivoNombreRealEl) archivoNombreRealEl.value = file.name;
            if (archivoTipoEl) archivoTipoEl.value = file.type;

            // Mostrar preview
            if (archivoPreviewContainer) archivoPreviewContainer.classList.remove('d-none');
            if (archivoNombreEl) archivoNombreEl.textContent = file.name;

            // Mostrar según tipo
            if (file.type === 'application/pdf') {
                // Es PDF
                if (archivoIcon) {
                    archivoIcon.className = 'fas fa-file-pdf fa-2x text-danger';
                }
                if (imagenPreview) imagenPreview.classList.add('d-none');
                if (pdfPreview) {
                    pdfPreview.classList.remove('d-none');
                    pdfPreview.innerHTML = `<embed src="${base64}" type="application/pdf" width="100%" height="300px"></embed>`;
                }
            } else {
                // Es imagen, mostrar preview
                if (archivoIcon) {
                    archivoIcon.className = 'fas fa-file-image fa-2x text-success';
                }
                if (pdfPreview) pdfPreview.classList.add('d-none');
                if (imagenPreview && imgPreviewEl) {
                    imagenPreview.classList.remove('d-none');
                    imgPreviewEl.src = base64;
                }
            }
        };
        reader.readAsDataURL(file);
    }

    function eliminarArchivoSeleccionado() {
        if (archivoNotaEl) archivoNotaEl.value = '';
        if (archivoBase64El) archivoBase64El.value = '';
        if (archivoNombreRealEl) archivoNombreRealEl.value = '';
        if (archivoTipoEl) archivoTipoEl.value = '';
        if (archivoPreviewContainer) archivoPreviewContainer.classList.add('d-none');
        if (archivoSinPreview) archivoSinPreview.classList.remove('d-none');
        if (imagenPreview) imagenPreview.classList.add('d-none');
        if (pdfPreview) pdfPreview.classList.add('d-none');
    }

    // ============================================
    // FUNCIONES PARA HISTORIAL/NOVEDADES
    // ============================================

    // Elementos del historial
    const seccionHistorial = document.getElementById('seccion_historial');
    const historialLista = document.getElementById('historial_lista');
    const btnAgregarNovedad = document.getElementById('btn_agregar_novedad');
    const modalAgregarNovedad = document.getElementById('modalAgregarNovedad');
    const formNovedad = document.getElementById('form_novedad');
    const novedadDescripcion = document.getElementById('novedad_descripcion');
    let modalNovedadInstance = null;

    async function cargarHistorial(notaId) {
        if (!notaId) {
            if (seccionHistorial) seccionHistorial.style.display = 'none';
            return;
        }

        try {
            const resp = await fetch(`${routes.notasJuridicasBase}/${notaId}/historial`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (json.success) {
                if (seccionHistorial) seccionHistorial.style.display = 'flex';
                renderHistorial(json.data || []);
            } else {
                if (seccionHistorial) seccionHistorial.style.display = 'none';
            }
        } catch (e) {
            console.error('Error al cargar historial:', e);
            if (seccionHistorial) seccionHistorial.style.display = 'none';
        }
    }

    function renderHistorial(items) {
        if (!historialLista) return;

        if (!items || items.length === 0) {
            historialLista.innerHTML = '<p class="text-muted text-center">No hay novedades registradas</p>';
            return;
        }

        historialLista.innerHTML = items.map(item => {
            const fecha = new Date(item.created_at).toLocaleString('es-AR');
            const usuario = item.usuario ? `${item.usuario.Apellido}, ${item.usuario.Nombre}` : 'Sistema';
            const descripcion = item.descripcion || '';

            return `
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-info text-white me-2">
                                <i class="fas fa-clock"></i> ${fecha}
                            </span>
                            <span class="badge bg-secondary">
                                <i class="fas fa-user"></i> ${usuario}
                            </span>
                        </div>
                    </div>
                    <div class="ps-3 border-start">
                        <p class="mb-0">${descripcion}</p>
                    </div>
                </div>
            `;
        }).join('');
    }

    function abrirModalNovedad() {
        if (novedadDescripcion) novedadDescripcion.value = '';

        if (modalAgregarNovedad) {
            if (window.bootstrap && modalAgregarNovedad) {
                modalNovedadInstance = new bootstrap.Modal(modalAgregarNovedad);
                modalNovedadInstance.show();
            } else if (window.jQuery) {
                jQuery(modalAgregarNovedad).modal('show');
            }
        }
    }

    async function guardarNovedad(e) {
        e.preventDefault();

        if (!notaEditandoId) {
            mostrarAlertaValidacion('No hay una nota seleccionada para agregar la novedad', 'Atención');
            return;
        }

        const descripcion = novedadDescripcion?.value?.trim();
        if (!descripcion) {
            mostrarAlertaValidacion('La descripción de la novedad es obligatoria', 'Campo requerido');
            return;
        }

        try {
            const resp = await fetch(`${routes.notasJuridicasBase}/${notaEditandoId}/novedad`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ descripcion: descripcion })
            });

            const json = await resp.json();

            if (json.success) {
                mostrarMensaje('Novedad agregada exitosamente', 'success');
                cargarHistorial(notaEditandoId);

                // Cerrar modal
                if (modalNovedadInstance) {
                    modalNovedadInstance.hide();
                } else if (window.jQuery) {
                    jQuery(modalAgregarNovedad).modal('hide');
                }
            } else {
                mostrarMensaje(json.message || 'Error al guardar la novedad', 'error');
            }
        } catch (e) {
            console.error('Error guardando novedad:', e);
            mostrarMensaje('Error al guardar la novedad', 'error');
        }
    }

    // Event listeners para historial
    if (btnAgregarNovedad) {
        btnAgregarNovedad.addEventListener('click', abrirModalNovedad);
    }

    if (formNovedad) {
        formNovedad.addEventListener('submit', guardarNovedad);
    }

    // ============================================
    // FUNCIÓN PARA CREAR DOCUMENTO EN GOOGLE DRIVE
    // ============================================
    async function crearDocumentoDrive() {
        // VALIDACIÓN: El título es obligatorio
        const titulo = tituloEl?.value?.trim() || '';
        if (!titulo) {
            mostrarAlertaValidacion('Debe ingresar un título antes de generar el documento', 'Campo requerido');
            tituloEl?.focus();
            return;
        }


        const templateId = document.getElementById('google_doc_template_id')?.value || null;
        const numero = numeroEl?.value || null;
        const anio = anioEl?.value || new Date().getFullYear();

        // Mostrar indicador de carga
        const btn = document.getElementById('btn_abrir_doc_drive');
        const textoOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando documento...';

        try {
            const response = await fetch(routes.notasJuridicasCrearDocDrive, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    titulo: titulo,
                    template_id: templateId,
                    numero: numero,
                    anio: anio
                })
            });

            const data = await response.json();

            if (data.success && data.data) {
                // Guardar los datos del documento en los campos ocultos
                if (googleDocIdEl) googleDocIdEl.value = data.data.google_doc_id;
                if (googleDocLinkEl) googleDocLinkEl.value = data.data.google_doc_link;

                // Actualizar la interfaz con el nuevo documento
                actualizarInterfazDocCreado(data.data.google_doc_link, data.existe);

                // Mostrar mensaje según si existe o es nuevo
                if (data.existe) {
                    mostrarMensaje('Documento existente encontrado: ' + (data.data.nombre || ''), 'info');
                } else {
                    mostrarMensaje('Documento creado exitosamente', 'success');
                }

                // AUTO-GUARDAR LA NOTA DESPUES DE GENERAR EL DOCUMENTO
                await guardarNotaAutomaticamente();

                // Abrir el documento en una nueva pestaña
                window.open(data.data.google_doc_link, '_blank');
            } else {
                mostrarMensaje(data.message || 'Error al crear el documento en Google Drive', 'error');
                btn.innerHTML = textoOriginal;
            }
        } catch (error) {
            console.error('Error al crear documento:', error);
            mostrarMensaje('Error al comunicarse con el servidor', 'error');
            btn.innerHTML = textoOriginal;
        } finally {
            btn.disabled = false;
        }
    }

    // ============================================
    // EXPORTAR A EXCEL
    // ============================================
    function exportarExcel() {
        // Obtener los filtros actuales
        const params = new URLSearchParams();
        
        if (inputAnio && inputAnio.value) params.append('anio', inputAnio.value);
        if (inputFechaDesde && inputFechaDesde.value) params.append('fecha_desde', inputFechaDesde.value);
        if (inputFechaHasta && inputFechaHasta.value) params.append('fecha_hasta', inputFechaHasta.value);
        if (inputPersonal && inputPersonal.value.trim()) params.append('personal', inputPersonal.value.trim());
        if (inputNumero && inputNumero.value.trim()) params.append('numero', inputNumero.value.trim());
        if (inputBusqueda && inputBusqueda.value.trim()) params.append('busqueda', inputBusqueda.value.trim());
        if (inputEstado && inputEstado.value) params.append('estado', inputEstado.value);

        const url = routes.notasJuridicasExportarExcel + '?' + params.toString();
        
        console.log('Exportando Excel:', url);
        
        // Abrir en nueva pestaña para descargar
        window.open(url, '_blank');
        
        mostrarMensaje('Descargando archivo Excel...', 'success');
    }

    // Función para guardar la nota automáticamente después de crear el documento
    // NO llama a guardarNota para evitar recursión
    async function guardarNotaAutomaticamente() {
        // Obtener fecha de creación (usar fecha actual si está vacía)
        let fechaCreacion = fechaCreacionEl?.value || new Date().toISOString().split('T')[0];

        // Determinar tipo según lo que tenga
        const tieneGoogleDoc = !!(googleDocIdEl?.value || googleDocLinkEl?.value);
        const tieneArchivo = !!(archivoBase64El?.value);
        let tipo = null;

        if (tieneGoogleDoc && tieneArchivo) {
            tipo = 'completa';
        } else if (tieneGoogleDoc) {
            tipo = 'creada';
        } else if (tieneArchivo) {
            tipo = 'adjunta';
        }

        // Preparar payload con todos los campos requeridos
        const payload = {
            titulo: tituloEl?.value?.trim() || '',
            descripcion: descripcionEl?.value || '',
            observacion: observacionEl?.value || '',
            fecha_creacion: fechaCreacion,
            personal_id: personalIdEl?.value || null,
            nota_referencia_id: notaReferenciaEl?.value || null,
            tipo: tipo,  // Se calcula dinámicamente
            estado: parseInt(estadoFormEl?.value) || 1, // ID numérico
            es_plantilla: esPlantillaEl?.checked || false,
            nombre_plantilla: nombrePlantillaEl?.value || null,
            google_doc_id: googleDocIdEl?.value || null,
            google_doc_link: googleDocLinkEl?.value || null,
            archivo_base64: archivoBase64El?.value || null,
            archivo_nombre: archivoNombreRealEl?.value || null,
            configuracion: null  // El controlador lo maneja
        };

        const url = notaEditandoId
            ? `${routes.notasJuridicasBase}/${notaEditandoId}`
            : routes.notasJuridicasBase;
        const method = notaEditandoId ? 'PUT' : 'POST';

        console.log('Guardando nota automáticamente:', { url, method, payload });

        try {
            const resp = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const json = await resp.json();
            console.log('Respuesta guardar nota:', json);

            if (json.success) {
                mostrarMensaje('Nota guardada automáticamente', 'success');
                // Actualizar el ID si es una nota nueva
                if (json.data?.idNotaJuridica && !notaEditandoId) {
                    notaEditandoId = json.data.idNotaJuridica;
                    if (notaIdEl) notaIdEl.value = json.data.idNotaJuridica;
                }
            } else {
                console.error('Error al guardar nota:', json);
                mostrarMensaje(json.message || 'Error al guardar la nota automáticamente', 'error');
            }
        } catch (e) {
            console.error('Error guardando nota automáticamente:', e);
            mostrarMensaje('Error al guardar la nota automáticamente', 'error');
        }
    }
    // Iniciar
    init();
});
