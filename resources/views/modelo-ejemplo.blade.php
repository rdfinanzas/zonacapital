@extends('layouts.main')

@section('title', 'Modelo de Ejemplo | AdminLTE')

@section('header-title', 'Modelo de Ejemplo')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Modelo de Ejemplo</li>
@endsection

@section('content')
    <!-- Hidden permissions inputs for JavaScript -->
    <input type="hidden" id="permiso_crear" value="{{ $permisos['crear'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_leer" value="{{ $permisos['leer'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_editar" value="{{ $permisos['editar'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_eliminar" value="{{ $permisos['eliminar'] ? 1 : 0 }}">
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Action Buttons -->
    <div class="mb-3">
        @if ($permisos['crear'] ?? false)
            <button type="button" id="btnAgregar" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Agregar Registro
            </button>
        @endif
        <a href="{{ route('dashboard') ?? '#' }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Form Card (Initially Hidden) -->
    @if ($permisos['crear'] ?? false)
        <div class="card mb-4" id="formCard" style="display: none;">
            <div class="card-header">
                <h3 class="card-title">Formulario de Registro</h3>
                <div class="card-tools">
                    <button type="button" class="btn-close" id="btnCloseForm" aria-label="Close"></button>
                </div>
            </div>
            <div class="card-body">
                <form id="exampleForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" placeholder="Ingrese nombre">
                            </div>
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="Ingrese email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" placeholder="Ingrese teléfono">
                            </div>
                            <div class="form-group mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria">
                                    <option selected disabled>Seleccione una categoría</option>
                                    <option value="1">Categoría 1</option>
                                    <option value="2">Categoría 2</option>
                                    <option value="3">Categoría 3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" rows="3" placeholder="Ingrese descripción"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="activo">
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="reset" class="btn btn-secondary">Limpiar</button>
                </form>
            </div>
        </div>
    @endif

    <!-- List Card with Filters -->
    @if ($permisos['leer'] ?? false)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Registros</h3>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterNombre" class="form-label">Filtrar por Nombre</label>
                            <input type="text" class="form-control" id="filterNombre" placeholder="Buscar por nombre">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterCategoria" class="form-label">Filtrar por Categoría</label>
                            <select class="form-select" id="filterCategoria">
                                <option value="">Todas las categorías</option>
                                <option value="1">Categoría 1</option>
                                <option value="2">Categoría 2</option>
                                <option value="3">Categoría 3</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterEstado" class="form-label">Filtrar por Estado</label>
                            <select class="form-select" id="filterEstado">
                                <option value="">Todos los estados</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary mb-2 w-100">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th style="width: 150px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Juan Pérez</td>
                                <td>juan@example.com</td>
                                <td>555-1234</td>
                                <td>Categoría 1</td>
                                <td><span class="badge text-bg-success">Activo</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if ($permisos['editar'] ?? false)
                                        <button type="button" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if ($permisos['eliminar'] ?? false)
                                        <button type="button" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>María López</td>
                                <td>maria@example.com</td>
                                <td>555-5678</td>
                                <td>Categoría 2</td>
                                <td><span class="badge text-bg-success">Activo</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if ($permisos['editar'] ?? false)
                                        <button type="button" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if ($permisos['eliminar'] ?? false)
                                        <button type="button" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Carlos Rodríguez</td>
                                <td>carlos@example.com</td>
                                <td>555-9012</td>
                                <td>Categoría 1</td>
                                <td><span class="badge text-bg-danger">Inactivo</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if ($permisos['editar'] ?? false)
                                        <button type="button" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if ($permisos['eliminar'] ?? false)
                                        <button type="button" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>Mostrando 1-3 de 3 registros</div>
                    <nav aria-label="Page navigation example">
                        <ul class="pagination mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            No tiene permisos para ver el listado de registros.
        </div>
    @endif
@endsection

@push('scripts')
    <!-- Form Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnAgregar = document.getElementById('btnAgregar');
            const btnCloseForm = document.getElementById('btnCloseForm');
            const formCard = document.getElementById('formCard');

            if (btnAgregar && btnCloseForm && formCard) {
                btnAgregar.addEventListener('click', function() {
                    formCard.style.display = 'block';
                });

                btnCloseForm.addEventListener('click', function() {
                    formCard.style.display = 'none';
                });
            }
        });
    </script>

    <!-- Include custom JavaScript file -->
    <script src="{{ asset('js/javascript_modelo.js') }}"></script>
@endpush
