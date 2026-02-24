/**
 * Módulo de Notas Jurídicas - Nueva versión con Modal Editor
 * Integración con sistema de plantillas multi-módulo
 */
document.addEventListener('DOMContentLoaded', function () {
    // Elementos del DOM
    const btnFiltrar = document.getElementById('btn-filtrar');
    const tbody = document.getElementById('tbody-notas');
    const btnAdd = document.getElementById('btn_add');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');

    // Filtros
    const inputAnio = document.getElementById('anio_filtro');
    const inputFechaDesde = document.getElementById('fecha_desde');
    const inputFechaHasta = document.getElementById('fecha_hasta');
    const inputPersonal = document.getElementById('personal_filtro');
    const inputNumero = document.getElementById('numero_filtro');
    const inputBusqueda = document.getElementById('busqueda_filtro');
    const inputEstado = document.getElementById('estado_filtro');

    // Variables de estado
    let currentPage = 1;
    let lastPage = 1;
    let perPage = 15;
    let notaEditandoId = null;
    let moduloId = null;

    const csrfToken = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const routes = window.laravelRoutes || {};

    /**
     * Inicialización
     */
    async function init() {
        await cargarModuloId();
        cargarNotas(1);
        cargarPlantillas();
        setupEventListeners();
    }

    /**
     * Cargar ID del módulo
     */
    async function cargarModuloId() {
        try {
            const resp = await fetch('/plantillas-documentos/por-modulo?url=laravel-notas-juridicas', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();
            if (data.success && data.modulo_id) {
                moduloId = data.modulo_id;
                window.moduloId = moduloId;
                if (window.EditorNotas && window.EditorNotas['modalEditorNotas']) {
                    window.EditorNotas['modalEditorNotas'].moduloId = moduloId;
                }
            }
        } catch (e) {
            console.error('Error cargando módulo ID:', e);
        }
    }

    /**
     * Configurar event listeners
     */
    function setupEventListeners() {
        if (btnFiltrar) {
            btnFiltrar.addEventListener('click', function(e) {
                e.preventDefault();
                cargarNotas(1);
            });
        }

        if (btnLimpiarFiltros) {
            btnLimpiarFiltros.addEventListener('click', limpiarFiltros);
        }

        if (btnAdd) {
            btnAdd.addEventListener('click', function() {
                abrirModalNuevaNota();
            });
        }

        if (inputAnio) {
            inputAnio.addEventListener('change', function() {
                cargarNotas(1);
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

    /**
     * Cargar notas con filtros y paginación
     */
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

    /**
     * Renderizar tabla de notas
     */
    function renderTabla(items) {
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No se encontraron notas</td></tr>';
            return;
        }

        items.forEach(function(nota) {
            const tr = document.createElement('tr');

            const numeroCompleto = `${nota.numero}/${nota.anio}`;
            const fechaFormateada = formatearFecha(nota.fecha_creacion);
            const personalNombre = nota.personal ? `${nota.personal.Apellido}, ${nota.personal.Nombre}` : '-';
            const tipoLabel = nota.tipo === 'creada' ? 'Creada' : 'Adjunta';
            const tipoIcon = nota.tipo === 'creada' ? 'fa-edit' : 'fa-paperclip';
            const estadoLabel = capitalizeFirst(nota.estado);
            const estadoClass = `badge-${nota.estado}`;
            const creadorNombre = nota.creador ? `${nota.creador.Apellido}, ${nota.creador.Nombre}` : '-';

            tr.innerHTML = `
                <td class="text-center"><strong>${numeroCompleto}</strong></td>
                <td class="text-center">${fechaFormateada}</td>
                <td>${nota.titulo || ''}</td>
                <td>${personalNombre}</td>
                <td class="text-center">
                    <span class="badge bg-info"><i class="fas ${tipoIcon}"></i> ${tipoLabel}</span>
                </td>
                <td class="text-center">
                    <span class="badge ${estadoClass}">${estadoLabel}</span>
                </td>
                <td class="text-center" style="font-size: 0.75em;">${creadorNombre}</td>
                <td class="text-left">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-primary btn-sm btn-editar" data-id="${nota.idNotaJuridica}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a class="btn btn-success btn-sm" target="_blank" href="${routes.notasJuridicasBase}/${nota.idNotaJuridica}/pdf" title="Ver PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        ${nota.google_drive_link ? `<a class="btn btn-info btn-sm" target="_blank" href="${nota.google_drive_link}" title="Ver en Drive"><i class="fab fa-google-drive"></i></a>` : ''}
                        ${nota.observacion ? `<button class="btn btn-warning btn-sm btn-ver-obs" data-id="${nota.idNotaJuridica}" data-nota="${numeroCompleto}" data-obs="${nota.observacion.replace(/"/g, '&quot;')}" title="Ver observación"><i class="fas fa-info-circle"></i></button>` : ''}
                        <button class="btn btn-danger btn-sm btn-eliminar" data-id="${nota.idNotaJuridica}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        });

        setupActionButtons();
    }

    /**
     * Configurar botones de acción en la tabla
     */
    function setupActionButtons() {
        document.querySelectorAll('.btn-editar').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                await editarNota(id);
            });
        });

        document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                if (confirm('¿Está seguro de eliminar esta nota jurídica?')) {
                    await eliminarNota(id);
                }
            });
        });

        document.querySelectorAll('.btn-ver-obs').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const numeroNota = this.dataset.nota;
                const observacion = this.dataset.obs;
                mostrarModalObservacion(numeroNota, observacion);
            });
        });
    }

    /**
     * Renderizar paginación
     */
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

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${current_page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${current_page - 1}">Anterior</a>`;
        controlesPaginacion.appendChild(prevLi);

        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === current_page ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            controlesPaginacion.appendChild(pageLi);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${current_page === last_page ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${current_page + 1}">Siguiente</a>`;
        controlesPaginacion.appendChild(nextLi);

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

    /**
     * Cargar plantillas disponibles
     */
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

                // Agregar event listeners
                listaPlantillas.querySelectorAll('.plantilla-item').forEach(item => {
                    item.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const id = this.dataset.id;
                        await usarPlantilla(id);
                    });
                });
            } else {
                listaPlantillas.innerHTML = '<li><a class="dropdown-item disabled" href="#">Sin plantillas guardadas</a></li>';
            }
        } catch (e) {
            console.error('Error cargando plantillas:', e);
        }
    }

    /**
     * Usar una plantilla
     */
    async function usarPlantilla(id) {
        try {
            const resp = await fetch(`${routes.notasJuridicasBase}/plantillas/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!json.success) {
                alert('Error al cargar la plantilla');
                return;
            }

            // Abrir modal y cargar configuración
            const editor = window.EditorNotas['modalEditorNotas'];
            if (editor && json.configuracion) {
                editor.limpiar();
                editor.cargarConfiguracion(json.configuracion);
                editor.abrir(guardarNotaDesdeModal, guardarPlantillaDesdeModal);
            } else if (editor && json.data) {
                // Compatibilidad con formato antiguo
                editor.limpiar();
                const config = {
                    encabezado: {
                        logo_path: json.data.logo_path || null,
                        leyenda: json.data.leyenda_encabezado || null
                    },
                    contenido: json.data.descripcion || json.data.titulo || '',
                    margenes: { superior: 2, inferior: 2, izquierdo: 2.5, derecho: 2.5 },
                    pagina: { tamano: 'legal', orientacion: 'portrait' }
                };
                editor.cargarConfiguracion(config);
                editor.abrir(guardarNotaDesdeModal, guardarPlantillaDesdeModal);
            }
        } catch (e) {
            console.error('Error usando plantilla:', e);
            alert('Error al cargar la plantilla');
        }
    }

    /**
     * Abrir modal para nueva nota
     */
    function abrirModalNuevaNota() {
        notaEditandoId = null;
        const editor = window.EditorNotas['modalEditorNotas'];
        if (editor) {
            editor.limpiar();
            editor.abrir(guardarNotaDesdeModal, guardarPlantillaDesdeModal);
        }
    }

    /**
     * Editar nota existente
     */
    async function editarNota(id) {
        try {
            const resp = await fetch(`${routes.notasJuridicasBase}/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!json.success) {
                alert('Error al cargar la nota');
                return;
            }

            notaEditandoId = id;
            const nota = json.data;
            const editor = window.EditorNotas['modalEditorNotas'];

            if (editor) {
                editor.limpiar();

                // Cargar configuración si existe
                if (json.configuracion) {
                    editor.cargarConfiguracion(json.configuracion);
                } else {
                    // Compatibilidad con formato antiguo
                    const config = {
                        encabezado: {
                            logo_path: nota.logo_path || null,
                            leyenda: nota.leyenda_encabezado || null
                        },
                        contenido: nota.descripcion || '',
                        margenes: { superior: 2, inferior: 2, izquierdo: 2.5, derecho: 2.5 },
                        pagina: { tamano: 'legal', orientacion: 'portrait' }
                    };
                    editor.cargarConfiguracion(config);
                }

                editor.abrir(guardarNotaDesdeModal, guardarPlantillaDesdeModal);
            }
        } catch (e) {
            console.error('Error cargando nota:', e);
            alert('Error al cargar la nota');
        }
    }

    /**
     * Guardar nota desde el modal
     */
    async function guardarNotaDesdeModal(config) {
        // Obtener datos adicionales del formulario
        const titulo = config.contenido ?
            (config.contenido.match(/<h1[^>]*>(.*?)<\/h1>/i) ||
             config.contenido.match(/<strong>(.*?)<\/strong>/i) ||
             config.contenido.match(/<p[^>]*>(.*?)<\/p>/i))?.[1]?.replace(/<[^>]+>/g, '').trim() :
            'Sin título';

        const payload = {
            titulo: titulo.substring(0, 255),
            descripcion: config.contenido,
            configuracion: config,
            fecha_creacion: new Date().toISOString().split('T')[0],
            tipo: 'creada',
            estado: 'borrador'
        };

        const url = notaEditandoId
            ? `${routes.notasJuridicasBase}/${notaEditandoId}`
            : routes.notasJuridicasBase;
        const method = notaEditandoId ? 'PUT' : 'POST';

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
                const editor = window.EditorNotas['modalEditorNotas'];
                if (editor) editor.cerrar();
                notaEditandoId = null;
                cargarNotas(currentPage);
                cargarPlantillas();
                alert(notaEditandoId ? 'Nota actualizada exitosamente' : 'Nota guardada exitosamente');
            } else {
                alert(json.message || 'Error al guardar la nota');
            }
        } catch (e) {
            console.error('Error guardando nota:', e);
            alert('Error al guardar la nota');
        }
    }

    /**
     * Guardar plantilla desde el modal
     */
    async function guardarPlantillaDesdeModal(nombre, config) {
        try {
            const resp = await fetch('/plantillas-documentos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    nombre: nombre,
                    modulo_id: moduloId,
                    configuracion: config
                })
            });

            const data = await resp.json();

            if (data.success) {
                alert('Plantilla guardada exitosamente');
                document.getElementById('nombre_plantilla').value = '';
                cargarPlantillas();
            } else {
                alert(data.message || 'Error al guardar la plantilla');
            }
        } catch (e) {
            console.error('Error guardando plantilla:', e);
            alert('Error al guardar la plantilla');
        }
    }

    /**
     * Eliminar nota
     */
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
            } else {
                alert(json.message || 'Error al eliminar la nota');
            }
        } catch (e) {
            console.error('Error eliminando nota:', e);
            alert('Error al eliminar la nota');
        }
    }

    /**
     * Limpiar filtros
     */
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

    /**
     * Mostrar modal de observación
     */
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

    // Funciones auxiliares
    function formatearFecha(fecha) {
        if (!fecha) return '';
        const date = new Date(fecha);
        return date.toLocaleDateString('es-AR');
    }

    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Iniciar módulo
    init();
});
