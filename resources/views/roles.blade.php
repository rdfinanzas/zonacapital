@extends('layouts.main')

@section('title', 'Roles')

@section('header-title', 'Gestión de Roles')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Roles</li>
@endsection

@push('styles')
    <!-- Estilos específicos del módulo de Roles (si fueran necesarios) -->
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Gestión de Roles</h3>
                        <button type="button" class="btn btn-primary btn-sm" id="btn_nuevo_rol">
                            <i class="fas fa-plus"></i> Nuevo Rol
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Formulario de Rol -->
                    <form id="form_rol" class="mb-4">
                        @csrf
                        <input type="hidden" id="rol_id" name="id">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="rol_nombre" class="form-label">Nombre del Rol</label>
                                <input type="text" id="rol_nombre" name="rol_nombre" class="form-control" placeholder="Ej: Admin, Compras" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary" id="btn_guardar_rol">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-secondary" id="btn_nuevo_rol">
                                    <i class="fas fa-arrow-left"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Listado de Roles -->
                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-hover" id="tabla_roles">
                            <thead>
                                <tr>
                                    <th style="width: 60px">ID</th>
                                    <th>Rol</th>
                                    <th style="width: 160px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se completa vía JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Permisos por Módulos -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Permisos por Módulos</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Seleccioná un rol del listado para editar sus permisos.</p>
                            <div id="permisos_modulos" class="accordion"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="modalEliminarRol" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Confirmás eliminar el rol <strong id="rol_eliminar_nombre"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn_confirmar_eliminar_rol">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/roles.js') }}"></script>
@endpush