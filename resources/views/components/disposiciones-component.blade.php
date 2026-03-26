@php
    $permisos = $permisos ?? [
        'crear' => true,
        'editar' => true,
        'eliminar' => true,
        'leer' => true
    ];
@endphp

@if($isModal)
    <!-- Versión Modal -->
    <div class="modal fade" id="modal_nueva_disposicion" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title"><i class="fas fa-file-alt"></i> Gestión de Disposiciones</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="disp_target_dropdown" value="{{ $targetDropdown ?? 'NumDispPoster' }}" />
                    <div class="row">
                        <!-- Formulario de Nueva Disposición -->
                        <div class="col-md-4">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Nueva Disposición</h5>
                                </div>
                                <div class="card-body">
                                    <form id="dispo_form">
                                        <div class="row">
                                            <div class="form-group col-md-12 mb-3">
                                                <label for="dispo_anio">Año:</label>
                                                <input type="number" class="form-control" required min="2000" max="2100"
                                                       name="dispo_anio" id="dispo_anio" value="{{ date('Y') }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-12 mb-3">
                                                <label for="dispo_num">Nº Disposición:</label>
                                                <input type="number" class="form-control" min="1" name="dispo_num" id="dispo_num"
                                                       placeholder="Se carga automáticamente...">
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i> Se sugiere automáticamente
                                                </small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-12 mb-3">
                                                <label for="dispo_obs">Descripción:</label>
                                                <textarea class="form-control" name="dispo_obs" id="dispo_obs" rows="3"
                                                          maxlength="1000" required></textarea>
                                                <small class="form-text text-muted">
                                                    <span id="dispo_contador">0</span>/1000 caracteres
                                                </small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="fas fa-save"></i> Guardar
                                                </button>
                                                <button type="button" class="btn btn-warning w-100 mt-2"
                                                    onclick="DisposicionesModule.limpiarFormulario()">
                                                    <i class="fas fa-broom"></i> Limpiar
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Disposiciones -->
                        <div class="col-md-8">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Disposiciones Recientes</h5>
                                    <div class="card-tools">
                                        <div class="input-group input-group-sm">
                                            <select id="filtro_anio" class="form-control" style="width: 100px;">
                                                <option value="">Todos</option>
                                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                            <input type="text" id="buscar_disposiciones" class="form-control"
                                                   placeholder="Descripción..." style="width: 250px;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-default" id="btn_buscar">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm mb-0" id="tabla_disposiciones">
                                            <thead>
                                                <tr>
                                                    <th width="12%">Nº</th>
                                                    <th width="65%">Descripción</th>
                                                    <th width="12%">Acciones</th>
                                                    <th width="11%">Info</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla_disposiciones_body">
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">
                                                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Paginación simplificada -->
                                    <div class="card-footer bg-light">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <small class="text-muted" id="total_info">
                                                    Mostrando 0 de 0
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="btn-group btn-group-sm float-right" id="page-selection">
                                                    <!-- Paginación se carga aquí -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else
    <!-- Versión Página Completa -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Formulario -->
                <div class="col-md-4">
                    <div class="card card-primary" id="card_form">
                        <div class="card-header">
                            <h3 class="card-title">Formulario</h3>
                        </div>

                        <form id="form_main">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label for="anio">Año:</label>
                                        <input type="number" class="form-control" required min="2000" max="2100"
                                               name="anio" id="anio" value="{{ date('Y') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label for="num">Nº Disposición:</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" min="1" name="num" id="num"
                                                   placeholder="Se carga automáticamente...">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary"
                                                        onclick="DisposicionesModule.cargarProximoNumero()" title="Recargar próximo número">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Se sugiere automáticamente el próximo número correlativo. Puedes modificarlo o dejarlo vacío para generar automáticamente.
                                        </small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label for="obs">Descripción:</label>
                                        <textarea class="form-control" name="obs" id="obs" rows="4"
                                                  maxlength="1000" required></textarea>
                                        <small class="form-text text-muted">
                                            <span id="contador_caracteres">0</span>/1000 caracteres
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                @if($permisos['crear'] || $permisos['editar'])
                                <button type="submit" id="btn_submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                @endif

                                @if($permisos['crear'])
                                <button type="button" id="btn_limpiar" class="btn btn-warning">
                                    <i class="fas fa-broom"></i> Limpiar
                                </button>
                                @endif

                                @if($permisos['eliminar'])
                                <button type="button" id="btn_eliminar" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de disposiciones -->
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Lista de disposiciones</h3>
                             <div class="card-tools">
                                <div class="input-group input-group-sm">
                                    <select id="filtro_anio" class="form-control" style="width: 100px;">
                                        <option value="">Todos</option>
                                        @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <input type="text" id="buscar_disposiciones" class="form-control"
                                           placeholder="Descripción..." style="width: 250px;">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default" id="btn_buscar">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="tabla_disposiciones">
                                    <thead>
                                        <tr>
                                            <th width="12%">Nº</th>
                                            <th width="65%">Descripción</th>
                                            <th width="12%">Acciones</th>
                                            <th width="11%">Info</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_disposiciones_body">
                                        <!-- Los datos se cargan vía AJAX -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Información de paginación -->
                            <div id="total_info" class="info-pagination">
                                <!-- Total de registros -->
                            </div>

                            <!-- Controles de paginación -->
                            <div class="row">
                                <div class="col-md-3" id="page-selection_num_page" style="padding-top: 20px">
                                    <select id="page-selection_input_num_page" class="form-control form-control-sm">
                                        <option value="5">5 por página</option>
                                        <option value="10" selected>10 por página</option>
                                        <option value="25">25 por página</option>
                                        <option value="50">50 por página</option>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <div id="page-selection">
                                        <!-- Paginación se carga aquí -->
                                    </div>
                                </div>
                            </div>

                            @if($showStats)
                            <!-- Información adicional -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-primary">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total de disposiciones</span>
                                            <span class="info-box-number" id="total_disposiciones">-</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 70%"></div>
                                            </div>
                                            <span class="progress-description">
                                                <span id="disposiciones_este_anio">-</span> disposiciones este año
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de disposición generada -->
    <div class="modal fade" id="modal_disposicion_generada">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">
                        <i class="fas fa-check-circle"></i> Disposición generada
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <p>Se generó una disposición con el número:</p>
                    <p id="numero_disposicion_generada" class="h4 text-success font-weight-bold"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modal_eliminar">
        <div class="modal-dialog">
            <div class="modal-content bg-danger">
                <div class="modal-header">
                    <h4 class="modal-title">¡Atención!</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar esta disposición?</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="btn_eliminar_modal" class="btn btn-outline-light">Eliminar</button>
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endif
