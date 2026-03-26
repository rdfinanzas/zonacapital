<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ejemplo de Módulo Migrado</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs"></i>
                            Ejemplo de Módulo Migrado a Laravel
                        </h5>

                        @if($permisos['crear'])
                            <button type="button" class="btn btn-success" onclick="abrirModalCrear()">
                                <i class="fas fa-plus"></i> Crear Nuevo
                            </button>
                        @endif
                    </div>

                    <div class="card-body">
                        @if($permisos['leer'])
                            <!-- Filtros de búsqueda -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="filtro-buscar"
                                               placeholder="Buscar por nombre...">
                                        <button class="btn btn-outline-secondary" type="button" onclick="buscar()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">
                                        Permisos:
                                        @if($permisos['crear']) <span class="badge bg-success">Crear</span> @endif
                                        @if($permisos['leer']) <span class="badge bg-info">Leer</span> @endif
                                        @if($permisos['editar']) <span class="badge bg-warning">Editar</span> @endif
                                        @if($permisos['eliminar']) <span class="badge bg-danger">Eliminar</span> @endif
                                    </small>
                                </div>
                            </div>

                            <!-- Tabla de datos -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-datos">
                                        <!-- Los datos se cargan via AJAX -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <div id="paginacion" class="d-flex justify-content-center mt-3">
                                <!-- La paginación se genera via AJAX -->
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No tiene permisos para ver esta información.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($permisos['crear'] || $permisos['editar'])
    <!-- Modal para Crear/Editar -->
    <div class="modal fade" id="modalFormulario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Crear Nuevo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formulario" onsubmit="enviarFormulario(event)">
                    <div class="modal-body">
                        <input type="hidden" id="registro-id" value="">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Incluir la función apiLaravel (debe existir en tu proyecto) -->
    <script src="{{ asset('js/api-laravel.js') }}"></script>

    <script>
    let paginaActual = 1;
    let modalFormulario;

    document.addEventListener('DOMContentLoaded', function() {
        @if($permisos['crear'] || $permisos['editar'])
            modalFormulario = new bootstrap.Modal(document.getElementById('modalFormulario'));
        @endif

        @if($permisos['leer'])
            cargarDatos();
        @endif

        // Búsqueda con Enter
        document.getElementById('filtro-buscar')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscar();
            }
        });
    });

    @if($permisos['leer'])
    function cargarDatos(pagina = 1) {
        const buscar = document.getElementById('filtro-buscar')?.value || '';

        apiLaravel('/api/ejemplo-modulo/listar', 'GET', {
            page: pagina,
            buscar: buscar
        })
        .then(response => {
            if (response.status === 1) {
                mostrarDatos(response.data);
                mostrarPaginacion(response.pagination);
                paginaActual = pagina;
            } else {
                alert('Error: ' + response.msj);
            }
        })
        .catch(error => {
            console.error('Error cargando datos:', error);
            alert('Error al cargar los datos');
        });
    }

    function mostrarDatos(datos) {
        const tbody = document.getElementById('tabla-datos');
        let html = '';

        if (datos.length === 0) {
            html = '<tr><td colspan="4" class="text-center">No se encontraron datos</td></tr>';
        } else {
            datos.forEach(item => {
                html += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.nombre}</td>
                        <td>${item.fecha}</td>
                        <td>
                            @if($permisos['editar'])
                                <button class="btn btn-sm btn-warning me-1" onclick="editarRegistro(${item.id}, '${item.nombre}')" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endif
                            @if($permisos['eliminar'])
                                <button class="btn btn-sm btn-danger" onclick="eliminarRegistro(${item.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                `;
            });
        }

        tbody.innerHTML = html;
    }

    function mostrarPaginacion(pagination) {
        const container = document.getElementById('paginacion');
        let html = '<nav><ul class="pagination">';

        // Botón anterior
        if (pagination.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="cargarDatos(${pagination.current_page - 1})">Anterior</a></li>`;
        }

        // Páginas
        for (let i = 1; i <= pagination.last_page; i++) {
            const active = i === pagination.current_page ? 'active' : '';
            html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="cargarDatos(${i})">${i}</a></li>`;
        }

        // Botón siguiente
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="cargarDatos(${pagination.current_page + 1})">Siguiente</a></li>`;
        }

        html += '</ul></nav>';
        container.innerHTML = html;
    }

    function buscar() {
        cargarDatos(1);
    }
    @endif

    @if($permisos['crear'])
    function abrirModalCrear() {
        document.getElementById('modalTitulo').textContent = 'Crear Nuevo';
        document.getElementById('registro-id').value = '';
        document.getElementById('nombre').value = '';
        modalFormulario.show();
    }
    @endif

    @if($permisos['editar'])
    function editarRegistro(id, nombre) {
        document.getElementById('modalTitulo').textContent = 'Editar Registro';
        document.getElementById('registro-id').value = id;
        document.getElementById('nombre').value = nombre;
        modalFormulario.show();
    }
    @endif

    @if($permisos['crear'] || $permisos['editar'])
    function enviarFormulario(event) {
        event.preventDefault();

        const id = document.getElementById('registro-id').value;
        const nombre = document.getElementById('nombre').value;

        const datos = { nombre: nombre };
        const url = id ? `/api/ejemplo-modulo/editar/${id}` : '/api/ejemplo-modulo/crear';
        const metodo = id ? 'PUT' : 'POST';

        apiLaravel(url, metodo, datos)
        .then(response => {
            if (response.status === 1) {
                alert(response.msj);
                modalFormulario.hide();
                @if($permisos['leer'])
                    cargarDatos(paginaActual);
                @endif
            } else {
                alert('Error: ' + response.msj);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
    @endif

    @if($permisos['eliminar'])
    function eliminarRegistro(id) {
        if (confirm('¿Está seguro de que desea eliminar este registro?')) {
            apiLaravel(`/api/ejemplo-modulo/eliminar/${id}`, 'DELETE')
            .then(response => {
                if (response.status === 1) {
                    alert(response.msj);
                    @if($permisos['leer'])
                        cargarDatos(paginaActual);
                    @endif
                } else {
                    alert('Error: ' + response.msj);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el registro');
            });
        }
    }
    @endif
    </script>
</body>
</html>
