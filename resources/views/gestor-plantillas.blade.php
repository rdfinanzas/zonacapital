@extends('layouts.app')

@section('css')
<style>
    .card-header .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        color: #495057;
    }

    .plantilla-item {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .plantilla-item:hover {
        background-color: #f8f9fa;
    }

    .preview-mini {
        background: white;
        border: 1px solid #dee2e6;
        padding: 10px;
        font-family: 'Times New Roman', serif;
        font-size: 6px;
        line-height: 1.3;
        max-height: 150px;
        overflow: hidden;
    }

    .preview-mini-header {
        border-bottom: 1px solid #333;
        padding-bottom: 5px;
        margin-bottom: 5px;
        display: flex;
        justify-content: space-between;
    }

    .modulo-badge {
        font-size: 0.7rem;
    }
</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h3 class="card-title">
                                    <i class="fas fa-file-alt"></i> Gestor de Plantillas de Documentos
                                </h3>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <select id="filtro_modulo" class="form-select form-select-sm">
                                            <option value="">Todos los módulos</option>
                                            @foreach ($modulos as $modulo)
                                                <option value="{{ $modulo->IdModulo }}">{{ $modulo->Label }}</option>
                                                @foreach ($modulo->hijos as $hijo)
                                                    <option value="{{ $hijo->IdModulo }}">-- {{ $hijo->Label }}</option>
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" id="filtro_busqueda" class="form-control form-control-sm"
                                            placeholder="Buscar plantilla...">
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button id="btn_nueva_plantilla" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Nueva Plantilla
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabla_plantillas">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Módulo</th>
                                        <th>Vista Previa</th>
                                        <th>Configuración</th>
                                        <th>Creado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_plantillas">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Cargando plantillas...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div id="paginacion_container" class="d-flex justify-content-between align-items-center mt-3">
                            <div id="info_paginacion"></div>
                            <ul class="pagination" id="controles_paginacion"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de confirmación eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar la plantilla <strong id="nombre_eliminar"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn_confirmar_eliminar">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    window.csrfToken = '{{ csrf_token() }}';
    window.routes = {
        filtrar: '{{ route("plantillas-documentos.filtrar") }}',
        store: '{{ route("plantillas-documentos.store") }}',
        update: '{{ route("plantillas-documentos.update", ["id" => "__ID__"]) }}',
        destroy: '{{ route("plantillas-documentos.destroy", ["id" => "__ID__"]) }}',
        duplicar: '{{ route("plantillas-documentos.duplicar", ["id" => "__ID__"]) }}',
        show: '{{ route("plantillas-documentos.show", ["id" => "__ID__"]) }}',
        defaults: '{{ route("plantillas-documentos.defaults") }}',
    };
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.0/classic/ckeditor.js"></script>
<script>
    (function() {
        let currentPage = 1;
        let plantillaEliminarId = null;

        // Elementos
        const tbody = document.getElementById('tbody_plantillas');
        const filtroModulo = document.getElementById('filtro_modulo');
        const filtroBusqueda = document.getElementById('filtro_busqueda');
        const btnNuevaPlantilla = document.getElementById('btn_nueva_plantilla');

        // Inicializar
        function init() {
            cargarPlantillas();
            setupEventListeners();
        }

        // Event listeners
        function setupEventListeners() {
            filtroModulo.addEventListener('change', () => cargarPlantillas(1));
            filtroBusqueda.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') cargarPlantillas(1);
            });

            btnNuevaPlantilla.addEventListener('click', abrirEditorNuevaPlantilla);

            document.getElementById('btn_confirmar_eliminar').addEventListener('click', confirmarEliminar);
        }

        // Cargar plantillas
        async function cargarPlantillas(page = 1) {
            currentPage = page;
            const params = new URLSearchParams();
            params.append('page', page);
            params.append('per_page', 10);

            if (filtroModulo.value) params.append('modulo_id', filtroModulo.value);
            if (filtroBusqueda.value.trim()) params.append('busqueda', filtroBusqueda.value.trim());

            try {
                const resp = await fetch(`${routes.filtrar}?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await resp.json();

                renderTabla(data.data || []);
                renderPaginacion(data);

            } catch (e) {
                console.error('Error cargando plantillas:', e);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar plantillas</td></tr>';
            }
        }

        // Renderizar tabla
        function renderTabla(plantillas) {
            tbody.innerHTML = '';

            if (!plantillas || plantillas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No se encontraron plantillas</td></tr>';
                return;
            }

            plantillas.forEach(p => {
                const config = p.configuracion_decodificada || {};
                const modulo = p.modulo ? p.modulo.Label : 'Sin módulo';
                const creador = p.creador ? `${p.creador.Apellido || ''} ${p.creador.Nombre || ''}`.trim() : 'Sistema';
                const fecha = new Date(p.created_at).toLocaleDateString('es-AR');

                // Info de configuración
                const pagina = config.pagina || {};
                const margenes = config.margenes || {};
                const configInfo = `${pagina.tamano || 'legal'} - ${pagina.orientacion === 'landscape' ? 'Horizontal' : 'Vertical'}<br>
                    Márgenes: ${margenes.superior || 2}/${margenes.inferior || 2}/${margenes.izquierdo || 2.5}/${margenes.derecho || 2.5} cm`;

                // Preview mini
                const previewContent = config.contenido ? config.contenido.substring(0, 200) : '';
                const logoPreview = config.encabezado?.logo_path ?
                    `<img src="${config.encabezado.logo_path}" style="max-width:30px;max-height:15px;">` : '';

                const tr = document.createElement('tr');
                tr.className = 'plantilla-item';
                tr.innerHTML = `
                    <td>
                        <strong>${p.nombre}</strong>
                        ${p.descripcion ? `<br><small class="text-muted">${p.descripcion}</small>` : ''}
                    </td>
                    <td>
                        <span class="badge bg-secondary modulo-badge">${modulo}</span>
                    </td>
                    <td style="max-width: 200px;">
                        <div class="preview-mini">
                            <div class="preview-mini-header">
                                ${logoPreview}
                                <small style="font-style:italic;">${config.encabezado?.leyenda || ''}</small>
                            </div>
                            ${previewContent}...
                        </div>
                    </td>
                    <td><small>${configInfo}</small></td>
                    <td>
                        <small>${fecha}</small><br>
                        <small class="text-muted">${creador}</small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-editar" data-id="${p.idPlantilla}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-info btn-duplicar" data-id="${p.idPlantilla}" title="Duplicar">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-eliminar" data-id="${p.idPlantilla}"
                                data-nombre="${p.nombre}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;

                tbody.appendChild(tr);
            });

            setupActionButtons();
        }

        // Botones de acción
        function setupActionButtons() {
            document.querySelectorAll('.btn-editar').forEach(btn => {
                btn.addEventListener('click', () => editarPlantilla(btn.dataset.id));
            });

            document.querySelectorAll('.btn-duplicar').forEach(btn => {
                btn.addEventListener('click', () => duplicarPlantilla(btn.dataset.id));
            });

            document.querySelectorAll('.btn-eliminar').forEach(btn => {
                btn.addEventListener('click', () => mostrarModalEliminar(btn.dataset.id, btn.dataset.nombre));
            });
        }

        // Paginación
        function renderPaginacion(data) {
            const info = document.getElementById('info_paginacion');
            const controles = document.getElementById('controles_paginacion');

            if (!data || data.total === 0) {
                info.textContent = 'No hay resultados';
                controles.innerHTML = '';
                return;
            }

            info.textContent = `Mostrando ${data.from} a ${data.to} de ${data.total} plantillas`;
            controles.innerHTML = '';

            if (data.last_page <= 1) return;

            // Anterior
            controles.innerHTML += `
                <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${data.current_page - 1}">Ant</a>
                </li>
            `;

            // Números
            for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
                controles.innerHTML += `
                    <li class="page-item ${i === data.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }

            // Siguiente
            controles.innerHTML += `
                <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${data.current_page + 1}">Sig</a>
                </li>
            `;

            // Event listeners paginación
            controles.querySelectorAll('a').forEach(a => {
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = parseInt(a.dataset.page);
                    if (page >= 1 && page <= data.last_page) {
                        cargarPlantillas(page);
                    }
                });
            });
        }

        // Abrir editor para nueva plantilla
        function abrirEditorNuevaPlantilla() {
            // TODO: Abrir modal editor
            alert('Modal editor de notas - En desarrollo');
        }

        // Editar plantilla
        async function editarPlantilla(id) {
            try {
                const resp = await fetch(routes.show.replace('__ID__', id), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await resp.json();

                if (data.success) {
                    // TODO: Abrir modal editor con datos
                    console.log('Editar:', data);
                    alert('Modal editor - En desarrollo');
                }
            } catch (e) {
                console.error('Error:', e);
            }
        }

        // Duplicar plantilla
        async function duplicarPlantilla(id) {
            if (!confirm('¿Duplicar esta plantilla?')) return;

            try {
                const resp = await fetch(routes.duplicar.replace('__ID__', id), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await resp.json();

                if (data.success) {
                    cargarPlantillas(currentPage);
                } else {
                    alert(data.message || 'Error al duplicar');
                }
            } catch (e) {
                console.error('Error:', e);
            }
        }

        // Modal eliminar
        function mostrarModalEliminar(id, nombre) {
            plantillaEliminarId = id;
            document.getElementById('nombre_eliminar').textContent = nombre;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        async function confirmarEliminar() {
            if (!plantillaEliminarId) return;

            try {
                const resp = await fetch(routes.destroy.replace('__ID__', plantillaEliminarId), {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await resp.json();

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
                    cargarPlantillas(currentPage);
                } else {
                    alert(data.message || 'Error al eliminar');
                }
            } catch (e) {
                console.error('Error:', e);
            }
        }

        // Iniciar
        init();
    })();
</script>
@endsection
