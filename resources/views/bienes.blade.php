@extends('layouts.main')

@section('content')
<style>
    .card-bienes { border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .card-bienes .card-header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
    .btn-bienes { border-radius: 6px; font-size: 0.875rem; padding: 6px 14px; }
    .table-bienes { font-size: 0.875rem; }
    .table-bienes th { background: #f8f9fa; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; }
    .filter-box { background: #f8f9fa; border-radius: 6px; padding: 12px; margin-bottom: 12px; }
</style>

<section class="content-header py-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="m-0 text-success">
                    <i class="fas fa-boxes mr-2"></i>Gestión de Bienes
                </h4>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" id="btn_add" class="btn btn-success btn-bienes">
                    <i class="fas fa-plus mr-1"></i> Nuevo Bien
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Modal Formulario -->
<div class="modal fade" id="modal_form" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modal_title"><i class="fas fa-box mr-2"></i>Nuevo Bien</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_main">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Nombre: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" required name="Nombre" id="Nombre">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Código:</label>
                            <input type="text" class="form-control text-uppercase" name="Codigo" id="Codigo">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Stock mínimo:</label>
                            <input type="number" class="form-control" name="Minimo" id="Minimo" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Bienes Fiscales:</label>
                            <input type="text" class="form-control text-uppercase" name="BienesFiscales" id="BienesFiscales">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Categoría: <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="BienCategoria_Id" required id="BienCategoria_Id" style="width: 100%;">
                                <option value="">- SELECCIONAR -</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->IdBienCategoria }}">{{ $categoria->BienCategoria }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Observación:</label>
                            <textarea class="form-control text-uppercase" name="Notas" id="Notas" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_limpiar" class="btn btn-warning btn-sm">Limpiar</button>
                    <button type="submit" id="btn_submit" class="btn btn-success btn-sm">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                    <button type="button" id="btn_eliminar" class="btn btn-danger btn-sm" style="display: none;">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modal_eliminar">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Confirmar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-0">¿Eliminar este bien?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" id="btn_eliminar_modal" class="btn btn-danger btn-sm">Sí, eliminar</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Lista -->
<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="filter-box">
            <div class="row align-items-end">
                <div class="form-group col-md-4 mb-0">
                    <label class="small text-muted">Buscar por nombre</label>
                    <input type="text" class="form-control form-control-sm" id="filtro_nombre" placeholder="Ej: METFORMINA...">
                </div>
                <div class="form-group col-md-3 mb-0">
                    <label class="small text-muted">Categoría</label>
                    <select class="form-control form-control-sm" id="filtro_categoria">
                        <option value="">Todas</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->IdBienCategoria }}">{{ $categoria->BienCategoria }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2 mb-0">
                    <label class="small text-muted">Por página</label>
                    <select class="form-control form-control-sm" id="filtro_cantidad">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-3 mb-0">
                    <button type="button" class="btn btn-primary btn-sm btn-block" onclick="refrescarTabla()">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card card-bienes">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Lista de Bienes</h3>
                <span class="badge badge-light" id="total_registros">0 registros</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bienes table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Nombre</th>
                                <th>Código</th>
                                <th>Bienes Fiscales</th>
                                <th>Categoría</th>
                                <th width="80">Stock Min</th>
                                <th width="90">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="table_data">
                            <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer py-2">
                <div class="row">
                    <div class="col-md-3" id="page-info"></div>
                    <div class="col-md-9">
                        <ul class="pagination pagination-sm justify-content-end mb-0" id="page-pagination"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script src="{{ asset('js/bienes.js') }}"></script>
@endpush
