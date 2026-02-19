var idEdit = 0;
var dataTable;
var idPersonal = 0;
var idRelacion = 0;

function editar(id, ind) {
    idEdit = id;
    var registro = dataTable[ind];
    idPersonal = registro.LegajoPersonal;

    $('#personal').val(idPersonal).trigger('change');

    $('#fechaOrden').val(registro.FechaCreacion || formatDate(new Date()));
    $('#anio').val(registro.AnioLar);
    $('#dias').val(registro.DiasTotal);

    // Usar los valores ya formateados si existen, sino formatear las fechas
    var fechaDesde = registro.FIni || registro.FechaLic;
    var fechaHasta = registro.FFin || registro.FechaLicFin;

    var valDesde = '';
    var valHasta = '';

    if (fechaDesde) {
        valDesde = formatDate(fechaDesde);
    }

    if (fechaHasta) {
        valHasta = formatDate(fechaHasta);
    }

    // Establecer valores en los inputs nativos de fecha
    if (valDesde && valDesde !== '') {
        $('#d').val(valDesde);
    } else {
        $('#d').val(formatDate(new Date()));
    }

    if (valHasta && valHasta !== '') {
        $('#h').val(valHasta);
    } else {
        $('#h').val('');
    }

    $('#obs').val(registro.ObservacionLic);

    if (registro.MotPoster || registro.NumDispPoster) {
        $('#MotPoster').val(registro.MotPoster || '').trigger('change');
        $('#NumDispPoster').val(registro.NumDispPoster || '').trigger('change');
    }

    evaluarPostergacion();
    getDiasLar();
    validarDiasDisponibles();
}

function guardar() {
    if (!validarDiasDisponibles()) {
        toastr.error('Los días exceden el límite permitido. Corrija los datos antes de guardar.');
        return;
    }

    // Convertir YYYY-MM-DD a DD/MM/YYYY para el backend
    function formatToDDMMYYYY(yyyyMmDd) {
        if (!yyyyMmDd) return '';
        const partes = yyyyMmDd.split('-');
        if (partes.length === 3) {
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }
        return yyyyMmDd;
    }

    var data = {
        legajo: idPersonal,
        motivo_id: null,
        lar: false,
        fechaOrden: $('#fechaOrden').val(),
        personal: $('#personal').val(),
        anio: $('#anio').val(),
        dias: $('#dias').val(),
        desde: formatToDDMMYYYY($('#d').val()),
        hasta: formatToDDMMYYYY($('#h').val()),
        corrido: $('#corridos').is(':checked') ? 1 : 0,
        obs: $('#obs').val(),
        NumDispPoster: $('#NumDispPoster').val(),
        MotPoster: $('#MotPoster').val()
    };

    if (data.MotPoster === '') {
        data.MotPoster = null;
    }
    if (data.NumDispPoster === 'NULL' || data.NumDispPoster === '') {
        data.NumDispPoster = null;
    }

    var url = idEdit == 0 ? 'licencias' : 'licencias/' + idEdit;
    var method = idEdit == 0 ? 'POST' : 'PUT';

    apiLaravel(url, method, data).then(function (response) {
        limpiar();
        cargarDatos();
        toastr.success('LAR guardada correctamente');
    }).catch(function (error) {
        console.error('Error al guardar:', error);
        toastr.error(error || 'Error al guardar la LAR');
    });
}

function modalEliminar(id) {
    idEdit = id;
    $('#modal_eliminar').modal();
}

function eliminar() {
    apiLaravel('licencias/' + idEdit, 'DELETE', {}).then(function (data) {
        limpiar();
        cargarDatos();
        $('#modal_eliminar').modal('hide');
        toastr.success('LAR eliminada correctamente');
    }).catch(function (error) {
        console.error('Error al eliminar:', error);
        toastr.error(error || 'Error al eliminar la LAR');
    });
}

function cargarDatos() {
    console.log('cargarDatos() iniciado con idPersonal:', idPersonal);

    if (idPersonal == 0) {
        $("#table_data_lar").empty();
        $("#table_data_param").empty();
        $('#info_personal').html('').hide();
        return;
    }

    console.log('Llamando API: licencias/legajo/' + idPersonal);
    apiLaravel('licencias/legajo/' + idPersonal, 'GET').then(function (response) {
        console.log('Response recibido:', response);
        if (response.success) {
            var htmlTableLar = '';
            dataTable = response.licencias_lar;
            var larParams = response.lar;
            var info = response.info;

            if (info) {
                $('#info_personal').html(
                    '<strong>Relación:</strong> ' + info.Relacion +
                    ' | <strong>Fecha alta (A.P.):</strong> ' + info.FF
                ).show();
                idRelacion = info.idRelacion;
            } else {
                $('#info_personal').html('Faltan datos en el legajo').show();
                idRelacion = 0;
            }

            response.licencias_lar.forEach(function (licencia, i) {
                var desde = licencia.FIni || '';
                var hasta = licencia.FFin || '';
                var usuario = licencia.usuario ? licencia.usuario.Usuario : '';

                htmlTableLar += '<tr style="cursor: pointer;" onclick="editar(' + licencia.IdLicencia + ', ' + i + ')" title="Click para editar">';
                htmlTableLar += '<td class="text-center font-weight-bold">' + licencia.DiasTotal + '</td>';
                htmlTableLar += '<td><small>' + desde + '</small></td>';
                htmlTableLar += '<td><small>' + hasta + '</small></td>';
                htmlTableLar += '<td class="text-center"><span class="badge bg-success">' + licencia.AnioLar + '</span></td>';
                htmlTableLar += '<td><small class="text-muted">' + usuario + '</small></td>';
                htmlTableLar += '<td class="text-center">';
                htmlTableLar += '<div class="btn-group btn-group-sm">';
                htmlTableLar += '<button type="button" onclick="event.stopPropagation(); editar(' + licencia.IdLicencia + ', ' + i + ')" class="btn btn-warning" title="Editar"><i class="fas fa-edit"></i></button>';
                htmlTableLar += '<button type="button" onclick="event.stopPropagation(); modalEliminar(' + licencia.IdLicencia + ')" class="btn btn-danger" title="Eliminar"><i class="fa fa-trash"></i></button>';
                htmlTableLar += '<button type="button" onclick="event.stopPropagation(); imprimirLar(' + licencia.IdLicencia + ')" class="btn btn-success" title="Imprimir"><i class="fas fa-print"></i></button>';
                htmlTableLar += '</div>';
                htmlTableLar += '</td>';
                htmlTableLar += '</tr>';
            });

            $("#table_data_lar").html(htmlTableLar);

            $("#table_data_param").empty();
            if (larParams && larParams.length > 0) {
                larParams.forEach(function (param) {
                    var pendiente = param.Total - param.Tomados;
                    var htmlParam = '<tr>';
                    htmlParam += '<td class="text-center">' + param.Anio + '</td>';
                    htmlParam += '<td class="text-center">' + param.Tomados + '</td>';
                    htmlParam += '<td class="text-center">' + pendiente + '</td>';
                    htmlParam += '<td class="text-center">' + param.Total + '</td>';
                    htmlParam += '<td class="text-center">';
                    htmlParam += '<button type="button" onclick="eliminarParam(' + param.IdConfigLar + ')" class="btn btn-danger btn-xs">';
                    htmlParam += '<i class="fa fa-trash"></i></button>';
                    htmlParam += '</td>';
                    htmlParam += '</tr>';
                    $("#table_data_param").append(htmlParam);
                });
            } else {
                $("#table_data_param").html('<tr><td colspan="5" class="text-center text-muted">Sin parámetros</td></tr>');
            }
        }
    }).catch(function (error) {
        console.error('Error al cargar datos:', error);
        toastr.error(error || 'Error al cargar las LAR');
    });
}

function limpiarCamposLar() {
    idEdit = 0;
    $('#anio').val('');
    $('#dias').val('');
    $('#d').val(formatDate(new Date()));
    $('#h').val('');
    $('#obs').val('');
    $('#info_lar').html('').hide();
    $('#info_personal').html('').hide();
    $('#NumDispPoster').val('').trigger('change');
    $('#MotPoster').val('').trigger('change');
    $('.div_poster').hide();
    $('#btn_traer_dias').attr('disabled', true);
    $('#dias').attr('disabled', false);
    $('#d').attr('disabled', false);
    $('#h').attr('disabled', false);
}

function limpiar() {
    idEdit = 0;
    idPersonal = 0;
    idRelacion = 0;
    $('#form_main')[0].reset();
    $('#personal').val('').trigger('change');
    $('#info_personal').html('').hide();
    $('#info_lar').html('').hide();
    $("#table_data_lar").empty();
    $("#table_data_param").empty();
    $('#btn_parametros_menu').prop('disabled', true);
    cargarParametrosDropdown();
}

function imprimirLar(id) {
    window.open('/licencias/imprimir/lar/' + id, '_blank');
}

function getDiasLar() {
    const legajo = $('#personal').val();
    const anio = $('#anio').val();

    if (!legajo || !anio) {
        return;
    }

    apiLaravel('/licencias/dias-lar', 'POST', {
        legajo: idPersonal,
        anio: anio
    })
    .then(function(response) {
        if (response.success) {
            const antiguedad = response.response[0];
            const diasLar = response.response[1];
            const tomados = response.response[2];
            const pendiente = diasLar - tomados;

            $('#info_lar').html(
                'Antiguedad: ' + antiguedad +
                '<br>Tomados: ' + tomados +
                '<br>Pendiente: <span id="pendiente_lar">' + pendiente + '</span>' +
                '<br>Total: <span id="total_lar">' + diasLar + '</span>'
            ).removeClass('alert-danger').addClass('alert-warning');

            $('#dias').removeClass('is-invalid');

            $('#dias').attr('disabled', false);
            $('#d').attr('disabled', false);
            $('#h').attr('disabled', false);
            $('#btn_traer_dias').attr('disabled', false);

            validarDiasDisponibles();
        }
    })
    .catch(function(error) {
        console.error('Error getting LAR days:', error);
        if (error && error.includes('No se puede calcular la antigüedad')) {
            $('#info_lar').html(
                '<i class="fas fa-exclamation-triangle"></i> <strong>Atención:</strong> ' + error +
                '<br><small class="text-muted">Diríjase al módulo de Administración Pública para cargar la fecha de alta.</small>'
            ).show();
            toastr.warning(error);
        } else {
            toastr.error(error || 'Error al obtener días LAR');
        }
    });
}

function eliminarParam(id) {
    if (!confirm('¿Está seguro de eliminar este parámetro LAR?')) {
        return;
    }

    apiLaravel('/licencias/parametro/' + id, 'DELETE')
    .then(function(response) {
        if (response.success) {
            toastr.success(response.message);
            cargarParametrosLar();
        }
    })
    .catch(function(error) {
        console.error('Error deleting parameter:', error);
        toastr.error(error || 'Error al eliminar parámetro');
    });
}

function cargarParametrosLar() {
    if (!idPersonal) {
        $("#table_data_param").empty();
        return;
    }

    $("#table_data_param").empty();

    apiLaravel('licencias/parametros/' + idPersonal, 'GET')
    .then(function(response) {
        if (response.success) {
            if (response.data.length === 0) {
                $("#table_data_param").html('<tr><td colspan="5" class="text-center text-muted">Sin parámetros</td></tr>');
                return;
            }
            response.data.forEach(function(param) {
                var pendiente = param.Total - param.Tomados;
                var html = '<tr>';
                html += '<td class="text-center"><span class="badge bg-info text-dark">' + param.Anio + '</span></td>';
                html += '<td class="text-center">' + param.Tomados + '</td>';
                html += '<td class="text-center font-weight-bold text-' + (pendiente > 0 ? 'success' : 'danger') + '">' + pendiente + '</td>';
                html += '<td class="text-center">' + param.Total + '</td>';
                html += '<td class="text-center">';
                html += '<button type="button" onclick="eliminarParam(' + param.IdConfigLar + ')" class="btn btn-outline-danger btn-sm" title="Eliminar parámetro">';
                html += '<i class="fa fa-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
                $("#table_data_param").append(html);
            });
        }
    })
    .catch(function(error) {
        console.error('Error loading LAR parameters:', error);
    });
}

function cargarParametrosDropdown() {
    if (!idPersonal) {
        $("#table_param_dropdown").html('<tr><td colspan="5" class="text-center text-muted small py-2">Seleccione un personal</td></tr>');
        return;
    }

    $("#table_param_dropdown").empty();

    console.log('cargarParametrosDropdown() - idPersonal:', idPersonal);
    apiLaravel('licencias/parametros/' + idPersonal, 'GET')
    .then(function(response) {
        console.log('cargarParametrosDropdown() - response:', response);
        if (response.success) {
            console.log('cargarParametrosDropdown() - response.data:', response.data);
            if (response.data.length === 0) {
                $("#table_param_dropdown").html('<tr><td colspan="5" class="text-center text-muted small py-2">Sin parámetros configurados</td></tr>');
                return;
            }
            $("#table_param_dropdown").empty();
            response.data.forEach(function(param) {
                console.log('Procesando param:', param);
                var tomados = parseInt(param.Tomados) || 0;
                var total = parseInt(param.Total) || 0;
                var pendiente = total - tomados;
                var html = '<tr>';
                html += '<td class="small"><span class="badge bg-info text-dark">' + param.Anio + '</span></td>';
                html += '<td class="small text-center">' + tomados + '</td>';
                html += '<td class="small text-center fw-bold text-' + (pendiente > 0 ? 'success' : 'danger') + '">' + pendiente + '</td>';
                html += '<td class="small text-center">' + total + '</td>';
                html += '<td class="text-center">';
                html += '<button type="button" onclick="event.stopPropagation(); eliminarParam(' + param.IdConfigLar + ');" class="btn btn-link btn-sm text-danger p-0" title="Eliminar">';
                html += '<i class="fa fa-trash fa-xs"></i></button>';
                html += '</td>';
                html += '</tr>';
                $("#table_param_dropdown").append(html);
            });
        }
    })
    .catch(function(error) {
        console.error('Error loading LAR parameters in dropdown:', error);
    });
}

function createParamFromDropdown() {
    const legajo = idPersonal;
    const anio = $('#anio_dropdown').val();
    const total = $('#total_dropdown').val();

    console.log('createParamFromDropdown() - legajo:', legajo, 'anio:', anio, 'total:', total);

    if (!legajo || !anio || !total) {
        toastr.error('Debe completar todos los campos');
        return;
    }

    apiLaravel('/licencias/parametro', 'POST', {
        legajo: legajo,
        anio: anio,
        total: total
    })
    .then(function(response) {
        console.log('createParamFromDropdown() - response:', response);
        if (response.success) {
            toastr.success(response.message);
            $('#form_param_dropdown')[0].reset();
            console.log('Llamando cargarParametrosDropdown()...');
            cargarParametrosDropdown();
            if ($('#anio').val() == anio) {
                console.log('Llamando getDiasLar()...');
                getDiasLar();
            }
        }
    })
    .catch(function(error) {
        console.error('Error creating parameter:', error);
        toastr.error(error || 'Error al crear parámetro');
    });
}

function validarDiasDisponibles() {
    const anio = $('#anio').val();
    const dias = parseInt($('#dias').val()) || 0;
    const pendienteElemento = $('#pendiente_lar');
    const totalElemento = $('#total_lar');

    if (!anio || !pendienteElemento.length || !totalElemento.length) {
        return true;
    }

    const pendiente = parseInt(pendienteElemento.text()) || 0;
    const total = parseInt(totalElemento.text()) || 0;

    let limite = pendiente;

    if (idEdit > 0) {
        // Al editar, el límite es pendiente + días actuales de la licencia
        const licenciaActual = dataTable.find(function(l) { return l.IdLicencia === idEdit; });
        const diasActuales = licenciaActual ? parseInt(licenciaActual.DiasTotal) : 0;
        limite = pendiente + diasActuales;
    }

    if (dias > limite) {
        $('#dias').addClass('is-invalid');
        $('#info_lar').html(
            '<i class="fas fa-exclamation-triangle text-danger"></i> <strong>Error:</strong> Excede el límite de días. ' +
            'Disponibles: ' + limite + ', Solicitados: ' + dias
        ).removeClass('alert-warning').addClass('alert-danger');
        return false;
    }

    $('#dias').removeClass('is-invalid');
    return true;
}

function evaluarPostergacion() {
    const desde = $('#d').val();
    const anioLar = $('#anio').val();

    if (!desde || !anioLar) {
        $('.div_poster').hide();
        $('#MotPoster').val('');
        return;
    }

    const partes = desde.split('-');
    if (partes.length !== 3) {
        $('.div_poster').hide();
        return;
    }

    const dia = parseInt(partes[2]);
    const mes = parseInt(partes[1]);
    const year = parseInt(partes[0]);

    const anioLarNum = parseInt(anioLar);

    let mostrar = false;

    if (year - anioLarNum >= 2) {
        mostrar = true;
    } else if (year > anioLarNum) {
        if (idRelacion == 3) {
            if (mes > 6) {
                mostrar = true;
            }
        } else {
            mostrar = true;
        }
    }

    if (mostrar) {
        $('.div_poster').show();
    } else {
        $('.div_poster').hide();
        $('#MotPoster').val('');
    }
}

function calcularDesde(origen) {
    const dias = parseInt($('#dias').val());
    const desde = $('#d').val();
    const hasta = $('#h').val();
    const corridos = $('#corridos').is(':checked');

    // Convertir YYYY-MM-DD a DD/MM/YYYY para el backend
    function formatToDDMMYYYY(yyyyMmDd) {
        if (!yyyyMmDd) return '';
        const partes = yyyyMmDd.split('-');
        if (partes.length === 3) {
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }
        return yyyyMmDd;
    }

    // Convertir DD/MM/YYYY a YYYY-MM-DD para el input
    function formatToYYYYMMDD(ddMmYyyy) {
        if (!ddMmYyyy) return '';
        const partes = ddMmYyyy.split('/');
        if (partes.length === 3) {
            return partes[2] + '-' + partes[1] + '-' + partes[0];
        }
        return ddMmYyyy;
    }

    if (origen === 'dias' && dias && desde) {
        const desdeBackend = formatToDDMMYYYY(desde);
        const corridosNum = $('#corridos').is(':checked') ? 1 : 0;

        apiLaravel('/licencias/calcular-fecha', 'POST', {
            dias: dias,
            desde: desdeBackend,
            corridos: corridosNum
        })
        .then(function(response) {
            if (response.success) {
                $('#h').val(formatToYYYYMMDD(response.hasta));
                evaluarPostergacion();
            }
        })
        .catch(function(error) {
            console.error('Error calculating date:', error);
        });
    } else if (origen === 'desde' && dias && desde) {
        const desdeBackend = formatToDDMMYYYY(desde);
        const corridosNum = $('#corridos').is(':checked') ? 1 : 0;

        apiLaravel('/licencias/calcular-fecha', 'POST', {
            dias: dias,
            desde: desdeBackend,
            corridos: corridosNum
        })
        .then(function(response) {
            if (response.success) {
                $('#h').val(formatToYYYYMMDD(response.hasta));
                evaluarPostergacion();
            }
        })
        .catch(function(error) {
            console.error('Error calculating date:', error);
        });
    } else if (origen === 'hasta' && desde && hasta) {
        const desdeBackend = formatToDDMMYYYY(desde);
        const hastaBackend = formatToDDMMYYYY(hasta);
        const corridosNum = $('#corridos').is(':checked') ? 1 : 0;

        apiLaravel('/licencias/calcular-dias', 'POST', {
            desde: desdeBackend,
            hasta: hastaBackend,
            corridos: corridosNum
        })
        .then(function(response) {
            if (response.success) {
                $('#dias').val(response.dias);
                evaluarPostergacion();
            }
        })
        .catch(function(error) {
            console.error('Error calculating days:', error);
        });
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '';

    var date;

    if (typeof dateStr === 'string') {
        if (dateStr.includes('/')) {
            // Viene en formato DD/MM/YYYY del backend, convertir a YYYY-MM-DD para el input nativo
            var partes = dateStr.split('/');
            if (partes.length === 3) {
                var d = parseInt(partes[0]);
                var m = parseInt(partes[1]);
                var a = parseInt(partes[2]);
                if (!isNaN(d) && !isNaN(m) && !isNaN(a) && a > 1900 && a < 2100) {
                    var date = new Date(a, m - 1, d);
                    if (!isNaN(date.getTime())) {
                        return date.toISOString().split('T')[0];
                    }
                }
            }
            return '';
        } else if (dateStr.includes('-')) {
            // Ya viene en formato YYYY-MM/DD, validar y devolver tal cual
            var partes = dateStr.split('-');
            if (partes.length === 3) {
                var a = parseInt(partes[0]);
                var m = parseInt(partes[1]);
                var d = parseInt(partes[2]);
                if (!isNaN(a) && !isNaN(m) && !isNaN(d) && a > 1900 && a < 2100) {
                    var date = new Date(a, m - 1, d);
                    if (!isNaN(date.getTime())) {
                        return date.toISOString().split('T')[0];
                    }
                }
            }
            return '';
        }
    }

    date = new Date(dateStr);

    if (isNaN(date.getTime())) {
        return '';
    }

    return date.toISOString().split('T')[0];
}

$(function () {
    $('.select2').select2();

    $('#d').on('change', function() {
        var val = $(this).val();
        if (val) {
            calcularDesde('desde');
            if ($('#anio').val()) {
                evaluarPostergacion();
            }
        }
    });

    $('#h').on('change', function() {
        var val = $(this).val();
        if (val) {
            calcularDesde('hasta');
            if ($('#anio').val() && $('#d').val()) {
                evaluarPostergacion();
            }
        }
    });

    $('#dias').on('keyup change', function() {
        calcularDesde('dias');
        validarDiasDisponibles();
    });

    $('#corridos').on('change', function() {
        const dias = parseInt($('#dias').val());
        const desde = $('#d').val();

        if (dias && desde) {
            calcularDesde('dias');
        }
    });

    $('#personal').on('change', function () {
        console.log('Evento change ejecutado en #personal');
        limpiarCamposLar();
        idPersonal = $(this).val();
        console.log('idPersonal:', idPersonal);
        if (idPersonal) {
            console.log('Llamando cargarDatos()...');
            cargarDatos();
            $('#btn_parametros_menu').prop('disabled', false);
            cargarParametrosDropdown();
        } else {
            $("#table_data_lar").empty();
            $("#table_data_param").empty();
            $('#btn_parametros_menu').prop('disabled', true);
            cargarParametrosDropdown();
        }
    });

    $('#anio').on('keyup', function() {
        const valor = $(this).val();
        if (valor && valor.length > 0) {
            $('#btn_traer_dias').attr('disabled', false);
            if (idPersonal && valor.length === 4) {
                getDiasLar();
            }
        } else {
            $('#btn_traer_dias').attr('disabled', true);
        }
    });

    $('#form_main').submit(function (e) {
        e.preventDefault();
        guardar();
    });

    $('#btn_eliminar_modal').click(function () {
        eliminar();
    });

    $('#btn_limpiar').click(function () {
        limpiar();
    });

    $('#btn_eliminar').click(function () {
        if (idEdit > 0) {
            modalEliminar(idEdit);
        }
    });

    $('#personal').val('').trigger('change');

    // Verificar si venimos de la lista con parámetros de edición
    checkEditarDesdeLista();
});

// Función para verificar y cargar datos de edición desde la URL
function checkEditarDesdeLista() {
    const urlParams = new URLSearchParams(window.location.search);
    const editarId = urlParams.get('editar');
    const legajo = urlParams.get('legajo');

    if (editarId && legajo) {
        console.log('Modo edición desde lista - LAR ID:', editarId, 'Legajo:', legajo);
        
        // Seleccionar el personal
        $('#personal').val(legajo).trigger('change');
        
        // Esperar a que carguen los datos y luego seleccionar la LAR para editar
        setTimeout(function() {
            if (dataTable && dataTable.length > 0) {
                const index = dataTable.findIndex(function(l) { 
                    return l.IdLicencia == editarId; 
                });
                if (index !== -1) {
                    editar(parseInt(editarId), index);
                    toastr.info('Editando LAR #' + editarId);
                } else {
                    // Si no se encuentra en la primera carga, intentar cargar directamente
                    cargarLarParaEditar(editarId, legajo);
                }
            } else {
                // Si dataTable no está cargado aún, intentar cargar directamente
                cargarLarParaEditar(editarId, legajo);
            }
        }, 800);
    }
}

// Función para cargar una LAR específica para editar
function cargarLarParaEditar(id, legajo) {
    apiLaravel('licencias/legajo/' + legajo, 'GET').then(function(response) {
        if (response.success && response.licencias_lar) {
            dataTable = response.licencias_lar;
            const index = dataTable.findIndex(function(l) { 
                return l.IdLicencia == id; 
            });
            if (index !== -1) {
                editar(parseInt(id), index);
                toastr.info('Editando LAR #' + id);
            } else {
                toastr.warning('No se encontró la LAR #' + id);
            }
        }
    }).catch(function(error) {
        console.error('Error al cargar LAR para editar:', error);
        toastr.error('Error al cargar la LAR');
    });
}
