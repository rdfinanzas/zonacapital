@extends('layouts.app')

@section('content')
<style type="text/css">
/* Organigrama Tree Styles */
ul, #myUL {
    list-style-type: none;
}

#myUL {
    margin: 0;
    padding: 0;
}

/* Nodos del organigrama */
.org-node {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin: 8px 0;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-height: 60px;
}

.org-node:hover {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-color: #2196f3;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Contenido principal del nodo */
.org-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Títulos de los nodos */
.org-title {
    font-weight: bold;
    font-size: 16px;
    color: #2c3e50;
    margin-bottom: 4px;
    display: block;
}

/* Información del personal */
.org-info {
    font-size: 12px;
    color: #495057;
    margin: 2px 0;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Estadísticas */
.org-stats {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Botones de acción */
.org-actions {
    display: flex;
    gap: 6px;
    align-items: flex-start;
    margin-left: 15px;
    flex-shrink: 0;
}

/* Expandir/Contraer - Actualizado */
.caret {
    cursor: pointer;
    user-select: none;
    position: relative;
    padding-left: 20px;
    display: block;
    width: 100%;
}

.caret::before {
    content: "▶";
    position: absolute;
    left: 0;
    top: 2px;
    color: #495057;
    font-size: 12px;
    transition: transform 0.2s ease;
}

.caret-down::before {
    transform: rotate(90deg);
}

/* Listas anidadas - Mejoradas */
.nested {
    display: none;
    padding-left: 25px;
    margin-top: 10px;
    border-left: 2px solid #e9ecef;
}

.nested.active {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 1000px;
    }
}

.org-actions .btn {
    padding: 4px 6px;
    font-size: 11px;
    border-radius: 3px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.org-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-org-add {
    background: #28a745;
    color: white;
}

.btn-org-edit {
    background: #007bff;
    color: white;
}

.btn-org-delete {
    background: #dc3545;
    color: white;
}

.btn-org-jefe {
    background: #6f42c1;
    color: white;
}

/* Expandir/Contraer */
.caret {
    cursor: pointer;
    user-select: none;
    position: relative;
    padding-left: 20px;
    display: block;
    width: 100%;
}

.caret::before {
    content: "▶";
    position: absolute;
    left: 0;
    top: 0;
    color: #495057;
    font-size: 10px;
    transition: transform 0.2s ease;
}

.caret-down::before {
    transform: rotate(90deg);
}

/* Listas anidadas */
.nested {
    display: none;
    padding-left: 25px;
    margin-top: 10px;
    border-left: 2px solid #e9ecef;
}

.nested.active {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 500px;
    }
}

/* Diferentes colores por nivel */
.nivel-gerencia {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border-color: #ff9800;
}

.nivel-departamento {
    background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
    border-color: #9c27b0;
}

.nivel-servicio {
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    border-color: #4caf50;
}

.nivel-sector {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-color: #2196f3;
}

/* Formulario de edición */
#form_cont {
    margin: 15px 0;
    padding: 20px;
    background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
    border: 2px solid #ffc107;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

#text_add {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-right: 10px;
    width: 300px;
    font-size: 14px;
}

/* Estados vacíos */
.empty-state {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

/* Contador de personal */
.personal-count {
    background: #17a2b8;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .org-actions {
        position: static;
        margin-top: 8px;
        justify-content: flex-start;
    }

    .nested {
        padding-left: 15px;
    }

    #text_add {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Organigrama</h1>
            </div>
        </div>
    </div>
</section>

<!-- Modal de eliminación -->
<div class="modal fade" id="modal_eliminar" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content bg-danger">
            <div class="modal-header">
                <h4 class="modal-title">Atención!</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este elemento del organigrama?</p>
                <p><strong>Nota:</strong> Se eliminarán también todos los elementos dependientes.</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" id="btn_eliminar_modal" class="btn btn-outline-light">Eliminar</button>
                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Estructura Organizacional</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($permisos['crear'])
                            <div class="mb-3">
                                <button type="button" onclick="addTree(-1,0,[])" class="btn btn-success">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Agregar Gerencia
                                </button>
                                <small class="text-muted ml-2">Comience creando una gerencia para estructurar el organigrama</small>
                            </div>
                        @endif

                        <div id="loading" class="text-center" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Cargando organigrama...
                        </div>

                        <div id="div_tree">
                            <!-- Aquí se cargará el árbol del organigrama -->
                        </div>

                        <div id="empty_state" class="text-center text-muted" style="display: none;">
                            <i class="fas fa-sitemap fa-3x mb-3"></i>
                            <h5>No hay estructura organizacional</h5>
                            <p>Comience agregando una gerencia para crear el organigrama</p>
                        </div>
                    </div>

                    <div class="card-footer">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            <strong>Estructura:</strong> Gerencia → Departamento → Servicio → Sector
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<input type="hidden" id="permisos" value="{{ json_encode($permisos) }}">
@endsection

@section('js')
<script src="{{ asset('js/organigrama.js') }}"></script>
@endsection
