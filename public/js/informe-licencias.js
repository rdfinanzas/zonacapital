$(document).ready(function() {
    // Inicializar select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Inicializar flatpickr para los campos de fecha
    $('.datepicker').flatpickr({
        dateFormat: 'd/m/Y',
        locale: 'es',
        allowInput: true
    });

    // Cargar selectores iniciales
    cargarSelectoresIniciales();

    // Inicializar select2 para el campo certifica (con AJAX)
    cargarJefesSelect2();

    // Manejar envío del formulario
    $('#filter_form').on('submit', function(e) {
        e.preventDefault();
        generarInforme();
    });

    // Manejar botón exportar
    $('#btn_exportar').on('click', function() {
        exportarExcel();
    });
});

let cert_id_fl = 0;

function cargarSelectoresIniciales() {
    $.ajax({
        url: '/personal/selectores-iniciales',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Cargar funciones
            if (data.funciones) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.funciones.forEach(function(f) {
                    options += '<option value="' + f.IdFuncion + '">' + f.Funcion + '</option>';
                });
                $('#funcion_fl').html(options);
            }

            // Cargar profesiones
            if (data.profesiones) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.profesiones.forEach(function(p) {
                    options += '<option value="' + p.idprofesion + '">' + p.profesion + '</option>';
                });
                $('#profesion_fl').html(options);
            }

            // Cargar servicios
            if (data.servicios) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.servicios.forEach(function(s) {
                    options += '<option value="' + s.idServicio + '">' + s.servicio + '</option>';
                });
                $('#servicio_fl').html(options);
            }

            // Cargar sectores
            if (data.sectores) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.sectores.forEach(function(s) {
                    options += '<option value="' + s.idSector + '">' + s.sector + '</option>';
                });
                $('#sector_fl').html(options);
            }

            // Cargar gerencias
            if (data.gerencias) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.gerencias.forEach(function(g) {
                    options += '<option value="' + g.idGerencia + '">' + g.Gerencia + '</option>';
                });
                $('#ger_fl').html(options);
            }

            // Cargar departamentos
            if (data.departamentos) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.departamentos.forEach(function(d) {
                    options += '<option value="' + d.idDepartamento + '">' + d.departamento + '</option>';
                });
                $('#dto_fl').html(options);
            }

            // Cargar categorías
            if (data.categorias) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.categorias.forEach(function(c) {
                    options += '<option value="' + c.idcategoria + '">' + c.categoria + '</option>';
                });
                $('#cate_fl').html(options);
            }

            // Cargar cargos
            if (data.cargos) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.cargos.forEach(function(c) {
                    options += '<option value="' + c.idCargo + '">' + c.cargo + '</option>';
                });
                $('#carg_fl').html(options);
            }

            // Cargar agrupamientos
            if (data.agrupamientos) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.agrupamientos.forEach(function(a) {
                    options += '<option value="' + a.idAgrupamiento + '">' + a.agrupamiento + '</option>';
                });
                $('#agrup_fl').html(options);
            }

            // Cargar instrucciones
            if (data.instrucciones) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.instrucciones.forEach(function(i) {
                    options += '<option value="' + i.idInstruccion + '">' + i.instruccion + '</option>';
                });
                $('#inst_fl').html(options);
            }

            // Cargar tipos de relación (contratos)
            if (data.tipos_relacion) {
                let options = '<option value="0">-Seleccionar-</option>';
                data.tipos_relacion.forEach(function(t) {
                    options += '<option value="' + t.idRelacion + '">' + t.Relacion + '</option>';
                });
                $('#tcon_fl').html(options);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando selectores:', error);
        }
    });
}

function cargarJefesSelect2() {
    $('#certifica_fl').select2({
        theme: 'bootstrap4',
        width: '100%',
        minimumInputLength: 2,
        ajax: {
            url: '/personal/buscar',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    query: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.Apellido + ', ' + item.Nombre
                        };
                    })
                };
            }
        }
    });

    $('#certifica_fl').on('select2:select', function(e) {
        cert_id_fl = e.params.data.id;
        $('#certifica_id').val(e.params.data.id);
    });

    $('#certifica_fl').on('select2:clear', function(e) {
        cert_id_fl = 0;
        $('#certifica_id').val(0);
    });
}

function generarInforme() {
    // Mostrar loading
    let loadingHtml = '<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div></td></tr>';
    $('#tabla_resultados').html(loadingHtml);

    // Serializar formulario
    let formData = $('#filter_form').serialize();

    $.ajax({
        url: '/informe-licencias/filtrar',
        type: 'GET',
        data: formData + '&certifica_id=' + cert_id_fl,
        dataType: 'json',
        success: function(data) {
            if (data && data.length > 0) {
                let html = '';
                data.forEach(function(row) {
                    let motivo = row.Motivo ? row.Motivo : 'LAR ' + row.AnioLar;
                    let orden = (row.OrdenMedica != 0) ? row.OrdenMedica + '/' + row.AnioLar : '';

                    html += '<tr>';
                    html += '<td>' + row.Legajo + '</td>';
                    html += '<td>' + row.Apellido + ', ' + row.Nombre + '</td>';
                    html += '<td>' + motivo + '</td>';
                    html += '<td>' + row.DiasTotal + '</td>';
                    html += '<td>' + orden + '</td>';
                    html += '<td>' + row.Inicio + '</td>';
                    html += '<td>' + row.Hasta + '</td>';
                    html += '<td>' + row.FF + '</td>';
                    html += '<td>' + row.AU + ', ' + row.NU + '</td>';
                    html += '</tr>';
                });
                $('#tabla_resultados').html(html);
            } else {
                $('#tabla_resultados').html('<tr><td colspan="9" class="text-center">No se encontraron resultados</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            $('#tabla_resultados').html('<tr><td colspan="9" class="text-center text-danger">Error al generar el informe: ' + error + '</td></tr>');
        }
    });
}

function exportarExcel() {
    // Serializar formulario
    let formData = $('#filter_form').serialize();

    // Redirigir a la URL de exportación
    window.location.href = '/informe-licencias/exportar?' + formData + '&certifica_id=' + cert_id_fl;
}
