@extends('layouts.app')

@section('content')
    <section class="content-header py-2">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h4 class="mb-0"><i class="fas fa-file-alt text-primary"></i> Informe de Licencias</h4>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros del Informe</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="filter_form">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-search"></i> Generar Informe
                                        </button>
                                        <button type="button" id="btn_exportar" class="btn btn-success btn-lg">
                                            <i class="fas fa-file-excel"></i> Exportar Excel
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtros básicos -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="Ape_fl">Nombre/Apellido</label>
                                            <input type="text" class="form-control" id="Ape_fl" name="Ape_fl" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="Legajo_fl">Legajo</label>
                                            <input type="text" class="form-control" id="Legajo_fl" name="Legajo_fl" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="DNI_fl">DNI</label>
                                            <input type="text" class="form-control" id="DNI_fl" name="DNI_fl" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="us_fl">Usuario</label>
                                            <input type="text" class="form-control" id="us_fl" name="us_fl" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Filtros de licencia -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="lic_fl">Licencia</label>
                                            <select class="form-control select2" id="lic_fl" name="lic_fl">
                                                <option value="0">---</option>
                                                @foreach($motivos as $motivo)
                                                    <option value="{{ $motivo->IdMotivoLicencia }}">{{ $motivo->Motivo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="lar_fl">Año LAR</label>
                                            <input type="text" class="form-control" id="lar_fl" name="lar_fl" placeholder="ej: 2024" />
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for="solo_lar">Solo LAR</label>
                                            <div class="checkbox">
                                                <label><input type="checkbox" name="solo_lar" value="1"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="ini_lic">Desde (fecha licencia)</label>
                                            <input type="text" class="form-control datepicker" id="ini_lic" name="ini_lic" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fin_lic">Hasta (fecha licencia)</label>
                                            <input type="text" class="form-control datepicker" id="fin_lic" name="fin_lic" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for="lic_med">Lic. Médica</label>
                                            <div class="checkbox">
                                                <label><input type="checkbox" name="lic_med" value="1"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="c_ini_lic">F. carga desde</label>
                                            <input type="text" class="form-control datepicker" id="c_ini_lic" name="c_ini_lic" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="c_fin_lic">F. carga hasta</label>
                                            <input type="text" class="form-control datepicker" id="c_fin_lic" name="c_fin_lic" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Filtros de personal -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sex_fl">Sexo</label>
                                            <select class="form-control" id="sex_fl" name="sex_fl">
                                                <option value="0">-Seleccionar-</option>
                                                <option value="1">Masculino</option>
                                                <option value="2">Femenino</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="Edad_fl">Edad</label>
                                            <input type="text" class="form-control" id="Edad_fl" name="Edad_fl" placeholder="ej: 28-35" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="profesion_fl">Profesión</label>
                                            <select class="form-control select2" id="profesion_fl" name="profesion_fl">
                                                <option value="0">-Seleccionar-</option>
                                                @foreach($profesiones ?? [] as $profesion)
                                                    <option value="{{ $profesion->idprofesion }}">{{ $profesion->profesion }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="funcion_fl">Función</label>
                                            <select class="form-control select2" id="funcion_fl" name="funcion_fl">
                                                <option value="0">-Seleccionar-</option>
                                                @foreach($funciones ?? [] as $funcion)
                                                    <option value="{{ $funcion->IdFuncion }}">{{ $funcion->Funcion }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    @if($todoPersonal ?? false)
                                    <!-- Tiene permiso: mostrar selectores completos -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="ger_fl">Gerencia</label>
                                            <select class="form-control select2" id="ger_fl" name="ger_fl">
                                                <option value="0">-Seleccionar-</option>
                                                @foreach($gerencias ?? [] as $gerencia)
                                                    <option value="{{ $gerencia->idGerencia }}">{{ $gerencia->Gerencia }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="dto_fl">Departamento</label>
                                            <select class="form-control select2" id="dto_fl" name="dto_fl">
                                                <option value="0">-Seleccionar-</option>
                                                @foreach($departamentos ?? [] as $departamento)
                                                    <option value="{{ $departamento->idDepartamento }}" data-gerencia="{{ $departamento->idGerencia }}">{{ $departamento->Departamento }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="servicio_fl">Servicio</label>
                                            <select class="form-control select2" id="servicio_fl" name="servicio_fl">
                                                <option value="0">-Seleccionar-</option>
                                                @foreach($servicios ?? [] as $servicio)
                                                    <option value="{{ $servicio->idServicio }}" data-departamento="{{ $servicio->idDepartamento }}">{{ $servicio->Servicio }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sector_fl">Sector</label>
                                            <select class="form-control select2" id="sector_fl" name="sector_fl">
                                                <option value="0">-Seleccionar-</option>
                                            </select>
                                        </div>
                                    </div>
                                    @else
                                    <!-- NO tiene permiso: mostrar solo su servicio -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="servicio_fl">Servicio:</label>
                                            <select class="form-control select2" id="servicio_fl" name="servicio_fl">
                                                @if($servicioDefault)
                                                    <option value="{{ $servicioDefault }}" selected>
                                                        {{ $servicios->first()->Servicio ?? 'Sin servicio' }}
                                                    </option>
                                                @else
                                                    <option value="0">Sin servicio asignado</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="certifica_fl">Certifica</label>
                                            <select class="form-control select2" id="certifica_fl" name="certifica_fl" style="width: 100%;">
                                                <option value="">- Seleccionar -</option>
                                            </select>
                                            <input type="hidden" id="certifica_id" name="certifica_id" value="0" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="agrup_fl">Agrupación</label>
                                            <select class="form-control select2" id="agrup_fl" name="agrup_fl">
                                                <option value="0">-Seleccionar-</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="cate_fl">Categoría</label>
                                            <select class="form-control select2" id="cate_fl" name="cate_fl">
                                                <option value="0">-Seleccionar-</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="carg_fl">Cargo</label>
                                            <select class="form-control select2" id="carg_fl" name="carg_fl">
                                                <option value="0">-Seleccionar-</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="estado_fl">Estado</label>
                                            <select class="form-control" id="estado_fl" name="estado_fl">
                                                <option value="0" selected>-Seleccionar-</option>
                                                <option value="1">ACTIVO</option>
                                                <option value="2">INACTIVO</option>
                                                <option value="3">BAJA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="inst_fl">Instrucción</label>
                                            <select class="form-control select2" id="inst_fl" name="inst_fl">
                                                <option value="0">-Seleccionar-</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tcon_fl">Tipo de contrato</label>
                                            <select class="form-control select2" id="tcon_fl" name="tcon_fl">
                                                <option value="0">-Seleccionar-</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-table"></i> Resultados del Informe</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Legajo</th>
                                        <th>Apellido/Nombre</th>
                                        <th>Motivo</th>
                                        <th>Días</th>
                                        <th>Orden</th>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Fecha de carga</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_resultados">
                                    <tr>
                                        <td colspan="9" class="text-center">Generar informe para ver resultados</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
window.informeLicenciasConfig = {
    todoPersonal: {{ $todoPersonal ?? 0 }},
    servicioDefault: {{ $servicioDefault ?? 0 }}
};
</script>
<script src="{{ asset('js/informe-licencias.js') }}?v={{ time() }}"></script>
@endpush
