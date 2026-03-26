@extends('layouts.app')

@section('title', 'Control de Horarios')

@push('styles')
<style>
    #table_informe tr td {
        border: 1px solid #8e8e8e !important;
    }
    #table_informe tr th {
        border: 1px solid #8e8e8e !important;
    }
    .table th, .table td {
        padding: 2px !important;
        font-size: 10px !important;
        padding-left: 10px !important;
        border: 1px solid #8e8e8e !important;
    }

    #table_informe thead th {
        position: sticky;
        top: 0;
        padding: 5px !important;
        background: white;
    }

    .hover_td:hover {
        background: #c1f4b9;
    }

    #table_informe thead th div {
        border: solid;
        margin: -5px;
        border-width: thin;
        padding: 5px;
        font-size: 12px;
    }

    /* Sobrescribir table-striped cuando hay estilos inline de color */
    #table_informe tbody tr[style*="background-color"] {
        background-color: inherit !important;
    }

    #table_informe tbody tr[style*="background-color"] td {
        background-color: inherit !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Control de Horarios</h1>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_control" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Ajuste</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cont_editar"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" id="btn_guardar_control" class="btn btn-info">Guardar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Control de Horarios</h3>
                    </div>

                    <form id="form_buscar">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col">
                                    <label for="d_fil">Fecha desde:</label>
                                    <input type="date" id="d_fil" class="form-control" required>
                                </div>
                                <div class="form-group col">
                                    <label for="h_fil">Fecha hasta:</label>
                                    <input type="date" id="h_fil" class="form-control" required>
                                </div>
                                <div class="form-group col">
                                    <label for="tipo">Tipo:</label>
                                    <select class="form-control" name="tipo" id="tipo">
                                        <option value="0" selected>-TODOS-</option>
                                        <option value="1">Ausencias</option>
                                        <option value="4">Falta de datos</option>
                                        <option value="5">Falta de datos y ausencias</option>
                                    </select>
                                </div>
                                <div class="form-group col" style="padding-top:32px">
                                    <button type="submit" id="btn_submit" class="btn btn-primary">Generar <i class="fas fa-search"></i></button>
                                </div>
                                <div class="form-group col" style="padding-top:32px">
                                    <button type="button" class="btn btn-danger" id="btn_export_pdf">Exportar <i class="fas fa-file-pdf"></i></button>
                                </div>
                                <div class="form-group col" style="padding-top:32px">
                                    <button type="button" class="btn btn-success" id="btn_export_excel">Exportar <i class="fas fa-file-excel"></i></button>
                                </div>
                            </div>

                            @php $disabled = (($todoPersonalControl ?? 0) == 1) ? '' : 'disabled'; @endphp
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="ger_fil">Gerencia:</label>
                                    <select class="form-control" onchange="changeOrganigrama(0,this)" name="ger_fil" id="ger_fil" {{ $disabled }}>
                                        <option value="">-TODAS-</option>
                                        @foreach($gerencias as $gerencia)
                                            <option value="{{ $gerencia->idGerencia }}" @if(($usuarioDefaults['idGerencia'] ?? 0) == $gerencia->idGerencia) selected @endif>{{ $gerencia->Gerencia }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="dep_fil">Departamento:</label>
                                    <select class="form-control" onchange="changeOrganigrama(1,this)" name="dep_fil" id="dep_fil" {{ $disabled }}>
                                        <option value="">-</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="servicio_fil">Servicio:</label>
                                    <select class="form-control" onchange="changeOrganigrama(2,this)" name="servicio_fil" id="servicio_fil" {{ $disabled }}>
                                        <option value="">-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="certifica">Certifica:</label>
                                    <input type="text" id="certifica" class="form-control" placeholder="Buscar jefe...">
                                    <small class="text-muted">Seleccionar jefe certificador</small>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="personal">Personal:</label>
                                    <input type="text" id="personal" class="form-control" placeholder="Buscar empleado...">
                                    <small class="text-muted">Filtrar por legajo o nombre</small>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table_informe" class="table table_informe table-striped">
                                <thead id="head_table"></thead>
                                <tbody id="table_horarios"></tbody>
                                <tfoot></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<input type="hidden" id="permisos_crud" value="{{ ($permisos['crear'] ?? 0) }}|{{ ($permisos['editar'] ?? 0) }}|{{ ($permisos['eliminar'] ?? 0) }}">
<input type="hidden" id="todo_personal_control" value="{{ $todoPersonalControl ?? 0 }}">
<input type="hidden" id="_usid" value="{{ $usuario->IdUsuario ?? '' }}">
@endsection

@push('scripts')
<script>
    window.controlHorariosConfig = {
        permisos: @json($permisos),
        todoPersonalControl: {{ $todoPersonalControl ?? 0 }},
        personalFull: @json($personalFull),
        personalLimit: @json($personalLimit),
        jefesFull: @json($jefesFull),
        jefesLimit: @json($jefesLimit),
        usuarioId: {{ $usuario->IdUsuario ?? 0 }},
        usuarioDefaults: @json($usuarioDefaults)
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/control-horarios.js') }}?t={{ time() }}"></script>
@endpush
