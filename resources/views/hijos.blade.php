@extends('layouts.main')

@section('title', 'Gestión de Hijos | ZonaCapital')

@section('header-title', 'Gestión de Hijos del Empleado')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('personal') }}">Personal</a></li>
    <li class="breadcrumb-item active">Gestión de Hijos</li>
@endsection

@section('content')
    <!-- Hidden permissions inputs for JavaScript -->
    <input type="hidden" id="permiso_crear" value="{{ $permisos['crear'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_leer" value="{{ $permisos['leer'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_editar" value="{{ $permisos['editar'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_eliminar" value="{{ $permisos['eliminar'] ? 1 : 0 }}">
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Empleado info -->
    <input type="hidden" id="empleado_id" value="{{ $empleado->idEmpleado }}">
    <input type="hidden" id="empleado_nombre" value="{{ $empleado->Apellido }} {{ $empleado->Nombre }}">

    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">Datos del Empleado</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('personal') }}'">
                    <i class="fas fa-arrow-left"></i> Volver a Personal
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Legajo:</strong> {{ $empleado->Legajo }}
                </div>
                <div class="col-md-3">
                    <strong>Apellido y Nombre:</strong> {{ $empleado->Apellido }} {{ $empleado->Nombre }}
                </div>
                <div class="col-md-3">
                    <strong>DNI:</strong> {{ $empleado->DNI }}
                </div>
                <div class="col-md-3">
                    <strong>Estado Civil:</strong> {{ $empleado->estadoCivil->DescripcionEstadoCivil ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <br>

    <!-- Hijos del Empleado -->
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">Hijos del Empleado</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm" onclick="abrirModalHijo()">
                    <i class="fas fa-plus"></i> Agregar Hijo
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="generarFormularioSubsidio({{ $empleado->idEmpleado }})">
                    <i class="fas fa-file-pdf"></i> Formulario Subsidio
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="tabla_hijos">
                    <thead class="bg-light">
                        <tr>
                            <th>Apellido y Nombre</th>
                            <th>DNI</th>
                            <th>Fecha Nacimiento</th>
                            <th>Edad</th>
                            <th>Convive</th>
                            <th>Estudia</th>
                            <th>Nivel Educativo</th>
                            <th style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_hijos">
                        <tr>
                            <td colspan="8" class="text-center text-muted">Cargando hijos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar hijo -->
    <div class="modal fade" id="modal_hijo" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="modal_hijo_title">Agregar Hijo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="form_hijo">
                        <input type="hidden" id="hijo_id">

                        <!-- Datos Personales del Hijo -->
                        <h5 class="text-primary mb-3">Datos Personales del Hijo</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hijo_apellido">Apellido:</label>
                                    <input type="text" class="form-control" id="hijo_apellido" name="Apellido">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hijo_nombre">Nombre:</label>
                                    <input type="text" class="form-control" id="hijo_nombre" name="Nombre">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hijo_dni">DNI:</label>
                                    <input type="text" class="form-control" id="hijo_dni" name="DNI">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hijo_fecnac">Fecha de Nacimiento:</label>
                                    <input type="date" class="form-control" id="hijo_fecnac" name="FecNac">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hijo_nivel_educativo">Nivel Educativo:</label>
                                    <select class="form-control" id="hijo_nivel_educativo" name="NivelEducativo">
                                        <option value="">- Seleccionar -</option>
                                        <option value="JARDIN">Jardín</option>
                                        <option value="PRIMARIA">Primaria</option>
                                        <option value="SECUNDARIA">Secundaria</option>
                                        <option value="UNIVERSITARIO">Universitario</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hijo_grado_anio">Grado/Año:</label>
                                    <input type="text" class="form-control" id="hijo_grado_anio" name="GradoAnio">
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox Group -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="hijo_convive" name="Convive" checked>
                                        <label class="custom-control-label" for="hijo_convive">Convive con el empleado</label>
                                    </div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="hijo_estudia" name="Estudia" checked>
                                        <label class="custom-control-label" for="hijo_estudia">Estudia</label>
                                    </div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="hijo_impedido" name="ImpedidoTrabaja">
                                        <label class="custom-control-label" for="hijo_impedido">Impedido para trabajar</label>
                                    </div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="hijo_remuneracion" name="RemuneracionEmpleador">
                                        <label class="custom-control-label" for="hijo_remuneracion">Recibe remuneración de empleador</label>
                                    </div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="hijo_ingresos" name="IngresosMensuales">
                                        <label class="custom-control-label" for="hijo_ingresos">Tiene ingresos mensuales propios</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos del Otro Padre/Madre -->
                        <hr>
                        <h5 class="text-primary mb-3">Datos del Otro Padre/Madre</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="otro_padre_nombre">Apellido y Nombre:</label>
                                    <input type="text" class="form-control" id="otro_padre_nombre" name="OtroPadre_ApellidoNombre">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="otro_padre_dni">DNI:</label>
                                    <input type="text" class="form-control" id="otro_padre_dni" name="OtroPadre_DNI">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="otro_padre_domicilio">Domicilio:</label>
                                    <input type="text" class="form-control" id="otro_padre_domicilio" name="OtroPadre_Domicilio">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="otro_padre_trabaja" name="OtroPadre_Trabaja">
                                        <label class="custom-control-label" for="otro_padre_trabaja">Trabaja</label>
                                    </div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="otro_padre_convive" name="OtroPadre_Convive">
                                        <label class="custom-control-label" for="otro_padre_convive">Convive</label>
                                    </div>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="otro_padre_asig" name="OtroPadre_AsigFamiliares">
                                        <label class="custom-control-label" for="otro_padre_asig">Recibe asignaciones familiares</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="otro_padre_empleador">Empleador:</label>
                                    <input type="text" class="form-control" id="otro_padre_empleador" name="OtroPadre_Empleador">
                                </div>
                            </div>
                        </div>

                        <!-- Fecha de Casamiento -->
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_casamiento">Fecha de Casamiento (si corresponde):</label>
                                    <input type="date" class="form-control" id="fecha_casamiento" name="FechaCasamiento">
                                </div>
                            </div>
                        </div>

                        <!-- Otros Empleos del Empleado -->
                        <hr>
                        <h5 class="text-primary mb-3">Otros Empleos del Empleado</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="otro_empleador">Otro Empleador:</label>
                                    <input type="text" class="form-control" id="otro_empleador" name="OtroEmpleador">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="monto_salario">Monto Salario:</label>
                                    <input type="number" step="0.01" class="form-control" id="monto_salario" name="MontoSalario">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox custom-control-inline mt-4">
                                        <input type="checkbox" class="custom-control-input" id="percibe_salario" name="PercibeSalario">
                                        <label class="custom-control-label" for="percibe_salario">Percibe Salario</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="obs_otros_empleos">Observaciones Otros Empleos:</label>
                                    <textarea class="form-control" id="obs_otros_empleos" name="ObservacionesOtrosEmpleos" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones Generales -->
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="hijo_observaciones">Observaciones Generales:</label>
                                    <textarea class="form-control" id="hijo_observaciones" name="Observaciones" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="guardarHijo()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/hijos.js') }}"></script>
@endpush
