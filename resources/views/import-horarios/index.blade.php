@extends('layouts.app')

@section('title', 'Importación de Horarios')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h3 class="mb-0">Importaciones</h3>
        </div>
    </div>

    <div class="modal fade" id="modal_eliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-danger text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarLabel">Atención</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro que desea eliminar este registro?
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="btn_eliminar_modal" class="btn btn-outline-light">Eliminar</button>
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4" id="card_form">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Formulario</h5>
        </div>
        <div class="card-body">
            <form id="form_main" class="row g-3">
                <div class="col-md-6">
                    <label for="fileInput" class="form-label">Seleccionar archivo (.txt)</label>
                    <input type="file" class="form-control" id="fileInput" accept=".txt" @if(!$permisos['crear'] && !$permisos['editar']) disabled @endif>
                    <div class="form-text">El archivo debe contener las marcas del reloj.</div>
                </div>
                <div class="col-md-6">
                    <label for="reloj" class="form-label">Reloj</label>
                    <select class="form-select" name="reloj" id="reloj" @if(!$permisos['crear'] && !$permisos['editar']) disabled @endif required>
                        @foreach($relojes as $item)
                            <option value="{{ $item->IdReloj }}">{{ $item->Reloj }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="observacion" class="form-label">Observación</label>
                    <textarea class="form-control" name="observacion" id="observacion" rows="3" @if(!$permisos['crear'] && !$permisos['editar']) disabled @endif></textarea>
                </div>
            </form>
        </div>
        <div class="card-footer d-flex gap-2">
            @if($permisos['crear'] || $permisos['editar'])
            <button type="button" id="btn_submit" class="btn btn-primary">Guardar <i class="fas fa-save"></i></button>
            <button type="button" id="btn_limpiar" class="btn btn-warning text-dark">Limpiar <i class="fas fa-times"></i></button>
            @endif
            @if($permisos['eliminar'])
            <button type="button" id="btn_eliminar" class="btn btn-danger">Eliminar <i class="fa fa-trash"></i></button>
            @endif
            <button type="button" id="btn_descargar" class="btn btn-secondary" style="display:none">Descargar TXT</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Lista de importaciones</h5>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="perPage">Mostrar</label>
                <select id="perPage" class="form-select form-select-sm" style="width: 90px;">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Reloj</th>
                            <th class="text-center">Estado</th>
                            <th>Observación</th>
                            <th>Usuario</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table_data"></tbody>
                </table>
            </div>
            <div class="d-flex align-items-center justify-content-between mt-3">
                <div id="total_info" class="text-muted small"></div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm" id="btn_prev">&laquo;</button>
                    <span class="small" id="page_info"></span>
                    <button class="btn btn-outline-secondary btn-sm" id="btn_next">&raquo;</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.importHorariosConfig = {
        permisos: @json($permisos),
        scope: 'all'
    };
</script>
<script src="{{ asset('js/import-horarios.js') }}?v={{ time() }}"></script>
@endpush
