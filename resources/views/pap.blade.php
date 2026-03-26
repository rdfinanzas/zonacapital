@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Carga de PAP</h1>
                </div>
            </div>
        </div>
    </section>

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
                    <p>¿Está seguro que desea eliminar este registro?</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="btn_eliminar_modal" class="btn btn-outline-light">Eliminar</button>
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Agregar</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form role="form" id="form_main">
                    <div class="modal-body">
                        <div class="card card-primary" id="card_form">
                            <div class="card-body">
                                <div class="row mt-12 mt-md-0">
                                    <div class="form-group col-12 col-md-5">
                                        <label for="dni">DNI Paciente</label>
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" required id="dni" name="dni">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" id="btn_buscar_dni" style="width:100px;" type="button">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-12 col-md-3">
                                        <label for="fecha_consulta">Fecha de Consulta:</label>
                        <input type="date" name="fecha_consulta" id="fecha_consulta" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="form-control">
                                    </div>
                                    <div class="form-group col-12 col-md-4">
                                        <label for="efector_sel">Efector:</label>
                                        <select class="form-control select2" name="efector_id" required id="efector_sel">
                                            <option value="">-EFECTOR-</option>
                                            @foreach($efectores as $efector)
                                                <option value="{{ $efector->idServicio }}">{{ $efector->servicio }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div id="form_persona" style="display: none;">
                                    <div class="row">
                                        <div class="form-group col-12 col-md-5">
                                            <label for="ApellidoNombre">Apellido y Nombre:</label>
                                            <input type="text" required id="ApellidoNombre" name="ApellidoNombre" class="form-control">
                                        </div>
                                        <div class="form-group col-12 col-md-3">
                                            <label for="FechaNacimiento">Fecha Nacimiento</label>
                                            <input type="date" class="form-control" required name="FechaNacimiento" id="FechaNacimiento">
                                        </div>
                                        <div class="form-group col-md-1">
                                            <label for="sexo">Sexo:</label>
                                            <div class="form-group col-3 col-md-1">
                                                <div class="icheck-primary d-inline">
                                                    <input type="radio" checked value="0" id="hombre" name="sexo" required>
                                                    <label for="hombre">M</label>
                                                </div>
                                                <div class="icheck-primary d-inline">
                                                    <input type="radio" value="1" id="mujer" name="sexo" required>
                                                    <label for="mujer">F</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-12 col-md-3">
                                            <label for="celular">Celular:</label>
                                            <input type="text" required id="celular" name="celular" class="form-control">
                                        </div>
                                        <div class="form-group col-12 col-md-12">
                                            <label for="Domicilio">Domicilio</label>
                                            <input type="text" class="form-control" required name="Domicilio" id="Domicilio">
                                        </div>
                                        <div class="form-group col-4 col-md-5">
                                            <label for="personal_id">Profesional:</label>
                                            <select class="form-control select2" name="personal_id" required id="personal_id">
                                                <option value="">-Profesional-</option>
                                                @foreach($profesionales as $profesional)
                                                    <option value="{{ $profesional->idEmpleado }}">{{ $profesional->Apellido }}, {{ $profesional->Nombre }} - {{ $profesional->profesion->profesion ?? '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-12 col-md-2">
                                            <label for="ficha_n">Nº Ficha:</label>
                                            <input type="text" required id="ficha_n" name="ficha_n" class="form-control">
                                            <div id="error_message" style="display: none; color: red;"></div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="practica_hijo_id">Practicas:</label><br>
                                            <label class="checkbox-inline"><input type="checkbox" name="pap_visible" disabled checked>Pap</label>
                                            <label class="checkbox-inline"><input type="checkbox" value="2" name="practica_hijo_id" id="practica_hijo_id">PVH</label>
                                            <input type="hidden" name="pap" value="1">
                                        </div>
                                        <div class="form-group col-2 col-md-2">
                                            <label for="resultado">Resultado:</label>
                                            <select class="form-control" name="resultado" id="resultado">
                                                <option value="1">-Pendiente-</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer" id="footer_btn" style="display: none;">
                                <div class="row">
                                    <div class="col-12 col-md-4 mt-2 mt-md-0">
                                        <button type="submit" id="btn_submit" class="btn btn-primary btn-block">Guardar <i class="fas fa-save"></i></button>
                                    </div>
                                    <div class="col-12 col-md-4 mt-2 mt-md-0">
                                        <button type="button" id="btn_limpiar" class="btn btn-warning btn-block">Limpiar <i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="col-12 col-md-4 mt-2 mt-md-0">
                                        <button type="button" id="btn_eliminar" class="btn btn-danger btn-block">Eliminar <i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row mt-4 mt-md-0">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Registros de Trabajo</h3>
                        </div>
                        <div class="card-body">
                            <div class="row mt-4 mt-md-0">
                                <div class="col-12 col">
                                    <div class="justify-content-between mb-4">
                                        <button type="button" id="btn_add" class="btn btn-primary">Agregar</button>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title"><a href="javascript:verFiltro()">Filtros</a></h3>
                                        </div>
                                        <div style="display:none" id="filter">
                                            <form action="javascript:refrescarTabla()">
                                                <div class="card-body">
                                                    <div class="row mt-4 mt-md-0">
                                                        <div class="form-group col-12 col-md-3">
                                                            <label for="d_fil">Fecha desde:</label>
                                                            <input type="date" id="d_fil" name="d_fil" class="form-control" max="{{ date('Y-m-d') }}" />
                                                        </div>
                                                        <div class="form-group col-12 col-md-3">
                                                            <label for="h_fil">Fecha hasta:</label>
                                                            <input type="date" id="h_fil" name="h_fil" class="form-control" max="{{ date('Y-m-d') }}" />
                                                        </div>
                                                        <div class="form-group col-12 col-md-3">
                                                            <label for="efector_sel_fil">Efector:</label>
                                                            <select class="form-control select2" name="efector_sel_fil" id="efector_sel_fil">
                                                                <option value="0">-TODOS-</option>
                                                                @foreach($efectores as $efector)
                                                                    <option value="{{ $efector->idServicio }}">{{ $efector->servicio }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-4 col-md-4">
                                                            <label for="personal_id2">Profesional:</label>
                                                            <select class="form-control select2" name="personal_id2" id="personal_id2">
                                                                <option value="">-Profesional-</option>
                                                                @foreach($profesionales as $profesional)
                                                                    <option value="{{ $profesional->idEmpleado }}">{{ $profesional->Apellido }}, {{ $profesional->Nombre }} - {{ $profesional->profesion->profesion ?? '' }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-2 col-md-2">
                                                            <label for="dni_f">DNI:</label>
                                                            <input type="text" id="dni_f" name="dni_f" class="form-control">
                                                        </div>
                                                        <div class="form-group col-2 col-md-2">
                                                            <label for="ficha_f">Ficha:</label>
                                                            <input type="text" id="ficha_f" name="ficha_f" class="form-control">
                                                        </div>
                                                        <div class="form-group col-2 col-md-2">
                                                            <label for="resultado_f">Resultado</label>
                                                            <select class="form-control" name="resultado_f" id="resultado_f">
                                                                <option value="">-OTROS-</option>
                                                                <option value="1">PENDIENTES</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <button type="submit" id="btn_submit_filter" class="btn btn-primary">Buscar <i class="fas fa-search"></i></button>
                                                    <button type="button" class="btn btn-success" onclick="exportar()">Exportar <i class="fas fa-file-excel"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Muestra</th>
                                            <th>N° Ficha</th>
                                            <th>Practica</th>
                                            <th>Efector</th>
                                            <th>Profesional</th>
                                            <th>Paciente</th>
                                            <th>DNI</th>
                                            <th>Nacimiento</th>
                                            <th>Edad</th>
                                            <th>Domicilio</th>
                                            <th>Cel</th>
                                            <th>Resultado</th>
                                            <th>Operador</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_data">
                                    </tbody>
                                </table>
                            </div>
                            <div id="total_info" class="info-pagination"></div>
                            <div class="row mt-4 mt-md-0">
                                <div class="col-md-2" id="page-selection_num_page" style="padding-top: 20px"></div>
                                <div class="col">
                                    <div id="page-selection"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/serializejson-polyfill.js') }}"></script>
    <script>
        window.laravelRoutes = window.laravelRoutes || {};
        window.laravelRoutes.papExportar = "{{ route('pap.exportar') }}";
    </script>
    <script src="{{ asset('js/pap.js') }}"></script>
@endpush
