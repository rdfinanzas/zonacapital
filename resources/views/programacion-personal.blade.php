@extends('layouts.app')

@section('title', 'Programación de Horarios')

@push('styles')
<style>
    .color-circle {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-left: 10px;
    }

    .darkviolet { background-color: darkviolet; }
    .darkgreen { background-color: darkgreen; }
    .b4ffff { background-color: #b4ffff; }

    .small-text { font-size: 0.875rem; }

    .custom-checkbox {
        display: inline;
        position: relative;
        padding-left: 35px;
        margin-right: 15px;
        cursor: pointer;
    }

    .custom-checkbox input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .checkmark {
        position: absolute;
        top: -1px;
        left: 0;
        height: 24px;
        width: 20px;
        background-color: #fff;
        border: 2px solid #0062cc;
        border-radius: 5px;
        margin-left: 5px;
    }

    .checkmark::after {
        content: "F";
        font-size: 15px;
        color: #000;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .custom-checkbox input[type="checkbox"]:checked~.checkmark {
        background-color: #007bff;
    }

    #table_informe {
        font-size: 10px;
    }

    #table_informe thead tr th {
        box-shadow: inset 0px 0px 1px 1px;
        top: 0px;
        position: sticky;
        z-index: 1;
        background-color: #fff;
        white-space: nowrap;
    }

    #table_informe td:first-child,
    #table_informe th:first-child {
        max-width: 180px;
        min-width: 120px;
    }

    .td_horario:hover {
        background: #d5f9d5 !important;
    }

    .nombre_personal a {
        color: black !important;
    }

    .nombre_personal a:hover {
        color: grey !important;
    }

    .shift-options {
        display: inline-block;
        margin-left: 10px;
    }

    label {
        margin-right: 5px;
    }

    .shift-row {
        transition: background-color 0.3s, color 0.3s;
    }

    .normal {
        background-color: transparent;
        color: black;
    }

    .contrato {
        background-color: darkgreen;
        color: white;
    }

    .paga {
        background-color: darkviolet;
        color: white;
    }
</style>
@endpush

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Programación de Horarios</h1>
            </div>
        </div>
    </div>
</div>

<!-- Modal para programación de empleado -->
<div class="modal fade" id="modal_programacion">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Programación de Horarios</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cont_programacion"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" id="btn_guardar_programacion" class="btn btn-success btn-sm">Guardar Programación</button>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para configurar horario -->
<div class="modal fade" id="modal_horario">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Configurar Horario</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo de Horario:</label>
                            <select id="tipo_horario" class="form-control">
                                <option value="simple">Horario Simple</option>
                                <option value="rotativo">Horario Rotativo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Configuración de fechas -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Desde:</label>
                            <div class="input-group date" id="desde_prog" data-target-input="nearest">
                                <input type="text" id="desde_prog_input" class="form-control datetimepicker-input" data-target="#desde_prog"/>
                                <div class="input-group-append" data-target="#desde_prog" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hasta:</label>
                            <div class="input-group date" id="hasta_prog" data-target-input="nearest">
                                <input type="text" id="hasta_prog_input" class="form-control datetimepicker-input" data-target="#hasta_prog"/>
                                <div class="input-group-append" data-target="#hasta_prog" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horario Simple -->
                <div id="cont_horario_simple">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="all" onchange="changeLunes();">
                        <label class="form-check-label" for="all">Lun a Vie</label>

                        <input type="checkbox" class="form-check-input ml-4" id="no_feriados" checked>
                        <label class="form-check-label" for="no_feriados">No incluir Feriados</label>
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            <b>Turno 1</b>
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th></th>
                                        <th>Entrada</th>
                                        <th>/</th>
                                        <th>Salida</th>
                                    </tr>
                                    <tr>
                                        <td>LUN</td>
                                        <td><input class="input_horario form-control" type="text" id="dia_1_e" onkeyup="changeLunes();"></td>
                                        <td></td>
                                        <td><input class="input_horario form-control" type="text" id="dia_1_s" onkeyup="changeLunes();"></td>
                                    </tr>
                                    <tr>
                                        <td>MAR</td>
                                        <td><input class="input_horario form-control" type="text" id="dia_2_e"></td>
                                        <td></td>
                                        <td><input class="input_horario form-control" type="text" id="dia_2_s"></td>
                                    </tr>
                                    <tr>
                                        <td>MIE</td>
                                        <td><input class="input_horario form-control" type="text" id="dia_3_e"></td>
                                        <td></td>
                                        <td><input class="input_horario form-control" type="text" id="dia_3_s"></td>
                                    </tr>
                                    <tr>
                                        <td>JUE</td>
                                        <td><input class="input_horario form-control" type="text" id="dia_4_e"></td>
                                        <td></td>
                                        <td><input class="input_horario form-control" type="text" id="dia_4_s"></td>
                                    </tr>
                                    <tr>
                                        <td>VIE</td>
                                        <td><input class="input_horario form-control" type="text" id="dia_5_e"></td>
                                        <td></td>
                                        <td><input class="input_horario form-control" type="text" id="dia_5_s"></td>
                                    </tr>
                                    <tr>
                                        <td>SAB</td>
                                        <td><input class="input_horario form-control" type="text" id="dia_6_e"></td>
                                        <td></td>
                                        <td><input class="input_horario form-control" type="text" id="dia_6_s"></td>
                                    </tr>
                                    <tr>
                                        <td>DOM</td>
                                        <td><input class="input_horario form-control" type="text" id="dia_0_e"></td>
                                        <td></td>
                                        <td><input class="input_horario form-control" type="text" id="dia_0_s"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_aplicar_horario" class="btn btn-primary btn-sm">Aplicar Horario</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Contenido Principal -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Programación de Horarios del Personal</h3>
                        <span id="contador_empleados" class="badge bg-dark text-white ml-2" style="display:none;">0 empleados</span>
                    </div>

                    <!-- Formulario de Búsqueda -->
                    <form id="form_buscar_prog">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col">
                                    <label for="d_fil_prog">Fecha desde:</label>
                                    <div class="input-group date" id="desde_fil_prog" data-target-input="nearest">
                                        <input type="text" id="d_fil_prog" class="form-control datetimepicker-input" data-target="#desde_fil_prog" required/>
                                        <div class="input-group-append" data-target="#desde_fil_prog" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col">
                                    <label for="h_fil_prog">Fecha hasta:</label>
                                    <div class="input-group date" id="hasta_fil_prog" data-target-input="nearest">
                                        <input type="text" id="h_fil_prog" class="form-control datetimepicker-input" data-target="#hasta_fil_prog" required/>
                                        <div class="input-group-append" data-target="#hasta_fil_prog" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col" style="padding-top:32px">
                                    <button type="submit" id="btn_submit_prog" class="btn btn-primary btn-sm">
                                        Generar <i class="fas fa-search"></i>
                                    </button>
                                </div>

                                <div class="form-group col" style="padding-top:32px">
                                    <button type="button" class="btn btn-info btn-sm" onclick="abrirModalHorario()">
                                        Configurar Horario <i class="fas fa-clock"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Filtros adicionales -->
                            @if(isset($permisos['todo_personal_pla']) && $permisos['todo_personal_pla'] == 1)
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="ger_fil_prog">Gerencia:</label>
                                    <select class="form-control" onchange="changeOrganigramaProg(0,this)" name="ger_fil_prog" id="ger_fil_prog">
                                        <option selected value="">-TODAS-</option>
                                        @foreach($gerencias as $gerencia)
                                            <option value="{{ $gerencia->idGerencia }}">{{ $gerencia->Gerencia }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="dep_fil_prog">Departamento:</label>
                                    <select class="form-control" onchange="changeOrganigramaProg(1,this)" name="dep_fil_prog" id="dep_fil_prog">
                                        <option value="">-</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="servicio_fil_prog">Servicio:</label>
                                    <select class="form-control" onchange="changeOrganigramaProg(2,this)" name="servicio_fil_prog" id="servicio_fil_prog">
                                        <option value="">-</option>
                                    </select>
                                </div>
                            </div>
                            @endif

                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="jefe_prog">Jefe:</label>
                                    <select class="form-control" id="jefe_prog">
                                        <option value="0">-TODOS-</option>
                                        @foreach($jefes as $jefe)
                                            <option value="{{ $jefe->idEmpleado }}">{{ $jefe->Apellido }}, {{ $jefe->Nombre }} - {{ $jefe->Servicio }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="personal_prog">Personal:</label>
                                    <input type="text" id="personal_prog" class="form-control" placeholder="Buscar empleado..."/>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla de resultados -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table_informe" class="table table-striped table-bordered">
                                <thead id="head_table_prog"></thead>
                                <tbody id="table_programacion"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Variables para JavaScript -->
<input type="hidden" id="permisos_prog" value="{{ $permisos['crear'] ? 1 : 0 }}|{{ $permisos['editar'] ? 1 : 0 }}|{{ $permisos['eliminar'] ? 1 : 0 }}">
<input type="hidden" id="todo_personal_pla" value="{{ $permisos['todo_personal_pla'] ?? 0 }}">
<input type="hidden" id="_usid_prog" value="{{ $usuario }}">
<input type="hidden" id="_fecha_server" value="{{ date('Y-m-d') }}">
@endsection

@push('scripts')
<script>
// Variables globales
var dataProgramacion = {};
var idEmpleadoModal = "";
var nombrePersonalModal = "";
var Toast;

// Configuración de Toast
Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 6000
});

// Inicialización
$(function () {
    // Configurar date pickers
    $("#desde_fil_prog").datetimepicker({
        format: "DD/MM/YYYY",
    });

    $("#hasta_fil_prog").datetimepicker({
        format: "DD/MM/YYYY",
    });

    $("#desde_prog").datetimepicker({
        format: "DD/MM/YYYY",
    });

    $("#hasta_prog").datetimepicker({
        format: "DD/MM/YYYY",
    });

    // Manejo del formulario de búsqueda
    $('#form_buscar_prog').on('submit', function (e) {
        e.preventDefault();
        obtenerDatosProgramacion();
    });
});

// Función para obtener datos de programación
function obtenerDatosProgramacion() {
    const parametros = {
        desde: $("#d_fil_prog").val(),
        hasta: $("#h_fil_prog").val(),
        ger: $("#ger_fil_prog").val(),
        dep: $("#dep_fil_prog").val(),
        serv: $("#servicio_fil_prog").val(),
        idJefe: $("#jefe_prog").val(),
        idEmpleado: 0
    };

    const queryString = new URLSearchParams(parametros).toString();

    apiLaravel(`/api/programacion-personal/datos?${queryString}`, 'GET')
        .then(response => {
            if (response.success) {
                dataProgramacion = response.data.empleados;
                renderizarTablaProgramacion(response.data.empleados);

                // Mostrar contador de empleados
                if (response.cantidadEmpleados !== undefined) {
                    $("#contador_empleados").text(response.cantidadEmpleados + ' empleado' + (response.cantidadEmpleados !== 1 ? 's' : '')).show();
                }
            } else {
                Toast.fire({
                    icon: "error",
                    title: response.error || "Error al obtener datos",
                });
            }
        })
        .catch(error => {
            Toast.fire({
                icon: "error",
                title: "Error de conexión",
            });
        });
}

// Función para renderizar tabla de programación
function renderizarTablaProgramacion(data) {
    // data es un array de objetos con {empleado, programacion}
    // programacion es un objeto indexado por fecha Y-m-d

    if (!data || data.length === 0) {
        $("#head_table_prog").html('<tr><th>Personal</th></tr>');
        $("#table_programacion").html('<tr><td colspan="1" class="text-center">No se encontraron empleados para los filtros seleccionados</td></tr>');
        return;
    }

    // Recopilar todas las fechas únicas de las programaciones
    let fechas = new Set();
    data.forEach(item => {
        if (item.programacion) {
            Object.keys(item.programacion).forEach(fecha => {
                fechas.add(fecha);
            });
        }
    });

    // Convertir a array y ordenar
    let fechasArray = Array.from(fechas).sort();

    // Si no hay fechas, mostrar mensaje
    if (fechasArray.length === 0) {
        $("#head_table_prog").html('<tr><th>Personal</th></tr>');
        $("#table_programacion").html('<tr><td colspan="1" class="text-center">No hay datos de programación para el período seleccionado</td></tr>');
        return;
    }

    // Generar encabezados
    let htmlHead = '<tr><th>Personal</th>';
    fechasArray.forEach(fecha => {
        const fechaObj = new Date(fecha + 'T00:00:00');
        const dia = fechaObj.toLocaleDateString('es-ES', { weekday: 'short' });
        const fechaCorta = fechaObj.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
        htmlHead += `<th>${dia}<br>${fechaCorta}</th>`;
    });
    htmlHead += '</tr>';

    $("#head_table_prog").html(htmlHead);

    // Renderizar filas por empleado
    let html = "";
    data.forEach(item => {
        const empleado = item.empleado;
        const programacion = item.programacion || {};

        html += `<tr>
            <td class="nombre_personal">
                <a href="#" onclick="abrirModalProgramacion('${empleado.idEmpleado}', '${empleado.Apellido}, ${empleado.Nombre}')">
                    ${empleado.Apellido}, ${empleado.Nombre}
                </a>
                <br><small>${empleado.Servicio || ''}</small>
            </td>`;

        fechasArray.forEach(fecha => {
            const prog = programacion[fecha];
            let contenido = '';
            let claseEstilo = '';

            if (prog) {
                if (prog.horario && prog.horario.length > 0 && prog.horario[0]) {
                    // Tiene horario
                    const horarios = prog.horario[0]; // Primer elemento tiene los horarios
                    contenido = horarios; // Mostrar el string de horarios
                } else if (prog.licencia) {
                    contenido = '<span class="badge badge-warning">Licencia</span>';
                } else if (prog.tipo === 2) {
                    contenido = '<span class="badge badge-success">Franco</span>';
                } else {
                    contenido = '<span class="text-muted">Sin programar</span>';
                }

                // Estilos según tipo
                if (prog.tipo === 1) claseEstilo = 'contrato';
                else if (prog.tipo === 2) claseEstilo = 'paga';
            } else {
                contenido = '<span class="text-muted">Sin programar</span>';
            }

            html += `<td class="td_horario ${claseEstilo}" onclick="editarHorarioDia('${empleado.idEmpleado}', '${fecha}')" style="cursor:pointer">${contenido}</td>`;
        });

        html += '</tr>';
    });

    $("#table_programacion").html(html);
}

// Función para abrir modal de programación
function abrirModalProgramacion(empleadoId, nombreEmpleado) {
    idEmpleadoModal = empleadoId;
    nombrePersonalModal = nombreEmpleado;

    $("#modal_programacion").modal();
    $("#cont_programacion").html(`<h5>Programación para: ${nombreEmpleado}</h5><p>Funcionalidad en desarrollo...</p>`);
}

// Función para abrir modal de horario
function abrirModalHorario() {
    $("#modal_horario").modal();
}

// Función para cambiar organigrama
function changeOrganigramaProg(nivel, elemento) {
    const id = $(elemento).val();

    if (nivel === 0) { // Gerencia
        if (id) {
            apiLaravel(`/api/programacion-personal/departamentos/${id}`, 'GET')
                .then(response => {
                    let html = '<option value="">-</option>';
                    response.response.forEach(dep => {
                        html += `<option value="${dep.idDepartamento}">${dep.Departamento}</option>`;
                    });
                    $("#dep_fil_prog").html(html);
                    $("#servicio_fil_prog").html('<option value="">-</option>');
                });
        } else {
            $("#dep_fil_prog").html('<option value="">-</option>');
            $("#servicio_fil_prog").html('<option value="">-</option>');
        }
    } else if (nivel === 1) { // Departamento
        if (id) {
            apiLaravel(`/api/programacion-personal/servicios/${id}`, 'GET')
                .then(response => {
                    let html = '<option value="">-</option>';
                    response.response.forEach(serv => {
                        html += `<option value="${serv.idServicio}">${serv.Servicio}</option>`;
                    });
                    $("#servicio_fil_prog").html(html);
                });
        } else {
            $("#servicio_fil_prog").html('<option value="">-</option>');
        }
    }
}

// Función para cambiar lunes a viernes
function changeLunes() {
    if ($("#all").is(":checked")) {
        const entrada = $("#dia_1_e").val();
        const salida = $("#dia_1_s").val();

        for (let i = 2; i <= 5; i++) {
            $(`#dia_${i}_e`).val(entrada);
            $(`#dia_${i}_s`).val(salida);
        }
    }
}

// Función para editar horario específico de un día
function editarHorarioDia(empleadoId, fecha) {
    Toast.fire({
        icon: 'info',
        title: 'Funcionalidad de edición individual en desarrollo'
    });
}
</script>
@endpush
