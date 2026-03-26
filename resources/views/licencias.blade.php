@extends('layouts.app')

@section('content')
    <section class="content-header py-2">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h4 class="mb-0"><i class="fas fa-calendar-minus text-primary"></i> Licencias</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modal_eliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-danger">
                <div class="modal-header">
                    <h4 class="modal-title text-white"><i class="fas fa-exclamation-triangle"></i> Atención!</h4>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-white mb-0">¿Está seguro que desea eliminar este registro?</p>
                </div>
                <div class="modal-footer bg-danger">
                    <button type="button" id="btn_eliminar_modal" class="btn btn-light">Eliminar</button>
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Columna izquierda - Formulario -->
                <div class="col-md-5">
                    <div class="card card-primary card-outline" id="card_form">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit"></i> Formulario de Licencias</h3>
                        </div>
                        <form role="form" id="form_main">
                            <div class="card-body">
                                <!-- Personal -->
                                <div class="form-group">
                                    <label for="personal"><i class="fas fa-user text-primary"></i> Personal:</label>
                                    <select class="form-control select2" required id="personal" name="personal">
                                        <option value="" selected disabled>- SELECCIONAR PERSONAL -</option>
                                        @foreach($personal as $p)
                                            <option value="{{ $p->Legajo }}" data-legajo="{{ $p->Legajo }}" data-dni="{{ $p->DNI }}">
                                                {{ $p->Apellido }}, {{ $p->Nombre }} (Leg: {{ $p->Legajo }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 p-2 bg-light rounded border" id="info_personal" style="display: none;"></div>
                                </div>

                                <!-- Fecha -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="fechaOrden"><i class="fas fa-calendar text-primary"></i> Fecha:</label>
                                        <input type="date" id="fechaOrden" name="fechaOrden" value="{{ date('Y-m-d') }}" required class="form-control" />
                                    </div>
                                </div>

                                <!-- Motivo -->
                                <div class="form-group">
                                    <label for="motivo"><i class="fas fa-clipboard-list text-primary"></i> Motivo:</label>
                                    <select class="form-control select2" required name="motivo_id" id="motivo">
                                        <option value="" selected disabled>- SELECCIONAR MOTIVO -</option>
                                        @foreach($motivos as $motivo)
                                            <option value="{{ $motivo->IdMotivoLicencia }}">{{ $motivo->Motivo }}</option>
                                        @endforeach
                                    </select>
                                    <div class="alert alert-info py-1 mt-2 mb-0" id="info_motivo" style="display: none;">
                                        <small><i class="fas fa-info-circle"></i> <span id="texto_info_motivo"></span></small>
                                    </div>
                                </div>

                                <!-- Disposición -->
                                <div class="form-group" id="cont_disp" style="display:none">
                                    <label for="NumDisp"><i class="fas fa-file-alt text-primary"></i> Disposición:</label>
                                    <select class="form-control select2" name="NumDisp" id="NumDisp">
                                        <option value="" selected disabled>- SELECCIONAR DISPOSICIÓN -</option>
                                        @foreach($disposiciones as $disp)
                                            <option value="{{ $disp->IdNumDisp }}">{{ $disp->NumDisp }}{{ $disp->AnioDisp ? '/'.$disp->AnioDisp : '' }} - {{ $disp->Descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- OM y CM -->
                                <div class="row om" style="display: none">
                                    <div class="form-group col-md-6">
                                        <label for="om"><i class="fas fa-file-medical text-primary"></i> Orden médica:</label>
                                        <input type="number" name="om" id="om" class="form-control" placeholder="N° OM" />
                                    </div>
                                    <div class="form-group col-md-6 cm" style="display: none">
                                        <label for="cm"><i class="fas fa-notes-medical text-primary"></i> Cert. médico:</label>
                                        <input type="number" name="cm" id="cm" class="form-control" placeholder="N° Certificado" />
                                    </div>
                                </div>

                                <!-- Días, Corridos -->
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="dias"><i class="fas fa-hashtag text-primary"></i> Días:</label>
                                        <input type="number" name="dias" id="dias" required class="form-control" placeholder="Cant. días" />
                                    </div>
                                    <div class="form-group col-md-3 d-flex align-items-center mt-4">
                                        <div class="icheck-primary">
                                            <input type="checkbox" id="corridos" name="corridos">
                                            <label for="corridos">Corridos</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-5 d-flex align-items-end">
                                        <button type="button" onclick="calcularXDia()" id="btn_calcular_x_dia" class="btn btn-outline-secondary btn-sm" title="Recalcular fecha hasta">
                                            <i class="fas fa-sync-alt"></i> Recalcular
                                        </button>
                                    </div>
                                </div>

                                <!-- Fechas Desde/Hasta -->
                                <div class="row">
                                    <div class="form-group col-md-5">
                                        <label for="d"><i class="fas fa-calendar-day text-primary"></i> Desde:</label>
                                        <input type="date" id="d" value="{{ date('Y-m-d') }}" required class="form-control" />
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label for="h"><i class="fas fa-calendar-check text-primary"></i> Hasta:</label>
                                        <input type="date" id="h" required class="form-control" />
                                    </div>
                                    <div class="form-group col-md-2 d-flex align-items-end">
                                        <button type="button" onclick="calcularXFecha()" id="btn_calcular_x_fecha" class="btn btn-outline-secondary btn-sm w-100" title="Calcular días">
                                            <i class="fas fa-calculator"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Campos ocultos -->
                                <input type="hidden" name="anio" id="anio" value="{{ date('Y') }}" />
                                
                                <!-- Observación -->
                                <div class="form-group">
                                    <label for="obs"><i class="fas fa-sticky-note text-primary"></i> Observación:</label>
                                    <textarea class="form-control" name="obs" id="obs" rows="2" placeholder="Observaciones opcionales..."></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" id="btn_submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button type="button" id="btn_limpiar" class="btn btn-outline-warning">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                                <button type="button" id="btn_eliminar" class="btn btn-outline-danger float-right">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Columna derecha - Historial de licencias -->
                <div class="col-md-7">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Historial de Licencias</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="thead-light sticky-top">
                                        <tr>
                                            <th class="text-center" style="width: 8%;">Días</th>
                                            <th style="width: 16%;">Fecha Inicio</th>
                                            <th style="width: 16%;">Fecha Fin</th>
                                            <th style="width: 24%;">Motivo</th>
                                            <th class="text-center" style="width: 8%;">OM</th>
                                            <th class="text-center" style="width: 8%;">CM</th>
                                            <th style="width: 20%;">Usuario</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_data">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                                Seleccione un personal para ver sus licencias
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('js/licencias.js') }}"></script>
    <script>
        // Configuración de select2 para personal con búsqueda
        $(document).ready(function() {
            // Guardar referencia al matcher original
            var defaultMatcher = $.fn.select2.defaults.defaults.matcher;

            $('#personal').select2({
                placeholder: '- SELECCIONAR PERSONAL -',
                allowClear: true,
                width: '100%',
                matcher: function(params, data) {
                    // Si siempre retorna true (mostrar todo) si no hay búsqueda
                    if (!params.term || params.term.trim() === '') {
                        return data;
                    }

                    // Si data no tiene elemento, usar matcher por defecto
                    if (!data.element) {
                        return defaultMatcher ? defaultMatcher(params, data) : data;
                    }

                    // Obtener término de búsqueda
                    var term = params.term.toLowerCase();

                    // Obtener DNI y Legajo de los atributos data
                    var $element = $(data.element);
                    var dni = ($element.data('dni') || '').toString();
                    var legajo = ($element.data('legajo') || '').toString();
                    var text = data.text.toLowerCase();

                    // Buscar en nombre, DNI o legajo
                    if (text.indexOf(term) !== -1 || dni.indexOf(term) !== -1 || legajo.indexOf(term) !== -1) {
                        return data;
                    }

                    // No hay coincidencias
                    return null;
                },
                templateResult: function(result) {
                    if (!result.id) {
                        return result.text;
                    }
                    var $option = $('<div style="font-size: 13px;"></div>');
                    var legajo = $(result.element).data('legajo');
                    var dni = $(result.element).data('dni');
                    var nombre = result.text;
                    $option.html('<strong>' + nombre + '</strong><br/><small class="text-muted">DNI: ' + dni + ' | Leg: ' + legajo + '</small>');
                    return $option;
                },
                templateSelection: function(result) {
                    if (!result.id) {
                        return result.text;
                    }
                    var $option = $('<span></span>');
                    var dni = $(result.element).data('dni');
                    $option.text(result.text.split(' (Leg:')[0] + ' | DNI: ' + dni);
                    return $option;
                }
            });

            $('#motivo, #NumDisp').select2({
                placeholder: '- SELECCIONAR -',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
