$(function() {
    cargarIngresos();
    cargarReservas();
    cargarSaldos();
    cargarResumen();

    $('#anio_ingresos').change(function() {
        cargarIngresos();
    });

    $('#anio_resumen').change(function() {
        cargarResumen();
    });

    $('#form_ingreso').submit(function(e) {
        e.preventDefault();
        agregarIngreso();
    });

    $('#form_saldo_inicial').submit(function(e) {
        e.preventDefault();
        agregarSaldoInicial();
    });

    $('#form_reserva').submit(function(e) {
        e.preventDefault();
        agregarReserva();
    });

    $('#form_saldo_disponible').submit(function(e) {
        e.preventDefault();
        agregarSaldo();
    });
});

function cargarIngresos() {
    var anio = $('#anio_ingresos').val();
    apiLaravel.get('deuda/get-ingresos?anio=' + anio, function(data) {
        var html = '';
        var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        data.ingresos.forEach(function(ingreso) {
            var fecha = ingreso.Fecha ? ingreso.Fecha.split(' ')[0] : '';
            var cuenta = ingreso.cuenta ? ingreso.cuenta.Nombre_Cuenta : '-';
            var esReserva = ingreso.SaldoIngre ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>';

            html += '<tr>';
            html += '<td>' + fecha + '</td>';
            html += '<td>' + cuenta + '</td>';
            html += '<td>$' + parseFloat(ingreso.Monto).toFixed(2) + '</td>';
            html += '<td>' + ingreso.Porcentaje + '%</td>';
            html += '<td>' + esReserva + '</td>';
            html += '<td>';
            html += '<button type="button" onclick="cambiarEstadoReserva(' + ingreso.IdIngreConta + ', ' + ingreso.SaldoIngre + ', ' + anio + ')" class="btn btn-xs btn-warning" title="Cambiar estado reserva">';
            html += '<i class="fas fa-sync"></i>';
            html += '</button> ';
            html += '<button type="button" onclick="eliminarIngreso(' + ingreso.IdIngreConta + ')" class="btn btn-xs btn-danger" title="Eliminar">';
            html += '<i class="fas fa-trash"></i>';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });

        $('#tabla_ingresos').html(html);
    });
}

function agregarIngreso() {
    var data = {
        fecha: $('#ingreso_fecha').val(),
        cuenta: $('#ingreso_cuenta').val(),
        monto: $('#ingreso_monto').val(),
        porcentaje: $('#ingreso_porcentaje').val(),
    };

    apiLaravel.exec('deuda/store-ingreso', 'POST', data, function(response) {
        if (response.success) {
            alert(response.message);
            $('#form_ingreso')[0].reset();
            cargarIngresos();
            cargarResumen();
        } else {
            alert(response.message);
        }
    });
}

function eliminarIngreso(id) {
    if (confirm('¿Está seguro que desea eliminar este ingreso?')) {
        apiLaravel.exec('deuda/delete-ingreso/' + id, 'DELETE', {}, function(response) {
            if (response.success) {
                alert(response.message);
                cargarIngresos();
                cargarResumen();
            } else {
                alert(response.message);
            }
        });
    }
}

function cambiarEstadoReserva(id, estadoActual, anio) {
    var nuevoEstado = estadoActual ? 0 : 1;
    var cuenta = $('#ingreso_cuenta').val() || 0;

    apiLaravel.exec('deuda/update-saldo-ingreso', 'POST', {
        id: id,
        sts: nuevoEstado,
        anio: anio,
        cuenta: cuenta
    }, function(response) {
        if (response.success) {
            alert(response.message);
            cargarIngresos();
        } else {
            alert(response.message);
        }
    });
}

function agregarSaldoInicial() {
    var data = {
        anio: $('#si_anio').val(),
        cuenta: $('#si_cuenta').val(),
        saldo: $('#si_saldo').val(),
    };

    apiLaravel.exec('deuda/store-saldo-inicial', 'POST', data, function(response) {
        if (response.success) {
            alert(response.message);
            $('#form_saldo_inicial')[0].reset();
        } else {
            alert(response.message);
        }
    });
}

function cargarReservas() {
    apiLaravel.get('deuda/get-reservas', function(data) {
        var html = '';

        data.forEach(function(reserva) {
            var cuenta = reserva.cuenta ? reserva.cuenta.Nombre_Cuenta : '-';

            html += '<tr>';
            html += '<td>' + cuenta + '</td>';
            html += '<td>$' + parseFloat(reserva.Monto).toFixed(2) + '</td>';
            html += '<td>' + reserva.Detalle + '</td>';
            html += '<td>';
            html += '<button type="button" onclick="eliminarReserva(' + reserva.IdReserva + ')" class="btn btn-xs btn-danger" title="Eliminar">';
            html += '<i class="fas fa-trash"></i>';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });

        $('#tabla_reservas').html(html);
    });
}

function agregarReserva() {
    var data = {
        cuenta: $('#reserva_cuenta').val(),
        monto: $('#reserva_monto').val(),
        detalle: $('#reserva_detalle').val(),
    };

    apiLaravel.exec('deuda/store-reserva', 'POST', data, function(response) {
        if (response.success) {
            alert(response.message);
            $('#form_reserva')[0].reset();
            cargarReservas();
        } else {
            alert(response.message);
        }
    });
}

function eliminarReserva(id) {
    if (confirm('¿Está seguro que desea eliminar esta reserva?')) {
        apiLaravel.exec('deuda/delete-reserva/' + id, 'DELETE', {}, function(response) {
            if (response.success) {
                alert(response.message);
                cargarReservas();
            } else {
                alert(response.message);
            }
        });
    }
}

function cargarSaldos() {
    apiLaravel.get('deuda/get-saldos', function(data) {
        var html = '';

        data.forEach(function(saldo) {
            var cuenta = saldo.cuenta ? saldo.cuenta.Nombre_Cuenta : '-';
            var fecha = saldo.FechaSaldo ? saldo.FechaSaldo.split(' ')[0] : '-';

            html += '<tr>';
            html += '<td>' + cuenta + '</td>';
            html += '<td>$' + parseFloat(saldo.Saldo).toFixed(2) + '</td>';
            html += '<td>' + fecha + '</td>';
            html += '</tr>';
        });

        $('#tabla_saldos').html(html);
    });
}

function agregarSaldo() {
    var data = {
        cuenta: $('#sd_cuenta').val(),
        saldo: $('#sd_saldo').val(),
    };

    apiLaravel.exec('deuda/store-saldo', 'POST', data, function(response) {
        if (response.success) {
            alert(response.message);
            $('#form_saldo_disponible')[0].reset();
            cargarSaldos();
        } else {
            alert(response.message);
        }
    });
}

function cargarResumen() {
    var anio = $('#anio_resumen').val();
    var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    apiLaravel.get('deuda/get-ingresos-meses?anio=' + anio, function(data) {
        var html = '';

        for (var i = 1; i <= 12; i++) {
            var mesEncontrado = data.find(function(mes) { return mes.Mes == i; });
            var total = mesEncontrado ? mesEncontrado.Suma : 0;

            html += '<tr>';
            html += '<td>' + meses[i - 1] + '</td>';
            html += '<td>$' + parseFloat(total).toFixed(2) + '</td>';
            html += '</tr>';
        }

        $('#tabla_resumen').html(html);
    });
}
