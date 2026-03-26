var idEdit = 0;
var dataTable = [];
var currentPage = 1;
var perPage = 25;

function editar(id) {
    fetch('bienes/' + id, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function(data) {
        idEdit = id;
        $('#modal_title').html('<i class="fas fa-edit mr-2"></i>Editar Bien');
        $('#Nombre').val(data.Nombre);
        $('#Codigo').val(data.Codigo);
        $('#BienesFiscales').val(data.BienesFiscales);
        $('#Minimo').val(data.Minimo);
        $('#BienCategoria_Id').val(data.BienCategoria_Id).trigger('change');
        $('#Notas').val(data.Notas);
        $('#btn_eliminar').show();
        $('#modal_form').modal('show');
    })
    .catch(function() {
        toastr.error('Error al cargar datos');
    });
}

function guardar() {
    var data = {
        Nombre: $('#Nombre').val(),
        Codigo: $('#Codigo').val(),
        BienesFiscales: $('#BienesFiscales').val(),
        Minimo: $('#Minimo').val(),
        BienCategoria_Id: $('#BienCategoria_Id').val(),
        Notas: $('#Notas').val()
    };
    
    var url = idEdit == 0 ? 'bienes' : 'bienes/' + idEdit;
    var method = idEdit == 0 ? 'POST' : 'PUT';
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    $('#btn_submit').prop('disabled', true);

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(function(response) {
        $('#btn_submit').prop('disabled', false);
        
        if (response.success) {
            toastr.success(response.message);
            $('#modal_form').modal('hide');
            limpiar();
            cargarTabla();
        } else {
            toastr.error(response.message);
        }
    })
    .catch(function() {
        $('#btn_submit').prop('disabled', false);
        toastr.error('Error al guardar');
    });
}

function modalEliminar(id) {
    idEdit = id;
    $('#modal_eliminar').modal('show');
}

function eliminar() {
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    fetch('bienes/' + idEdit, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) {
            toastr.success(data.message);
            limpiar();
            cargarTabla();
            $('#modal_eliminar').modal('hide');
            $('#modal_form').modal('hide');
        } else {
            toastr.error(data.message);
        }
    })
    .catch(function() {
        toastr.error('Error al eliminar');
    });
}

function cargarTabla(page) {
    currentPage = page || 1;
    perPage = parseInt($('#filtro_cantidad').val()) || 25;
    
    var params = new URLSearchParams();
    params.append('page', currentPage);
    params.append('cantidad', perPage);
    
    var nombre = $('#filtro_nombre').val();
    if (nombre && nombre.trim()) {
        params.append('nombre', nombre.trim());
    }
    
    var categoria = $('#filtro_categoria').val();
    console.log('Categoría seleccionada:', categoria);
    if (categoria && categoria !== '') {
        params.append('categoria_id', categoria);
    }
    
    fetch('bienes/filtrar?' + params.toString(), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        dataTable = data.data || [];
        var html = '';
        
        if (dataTable.length === 0) {
            html = '<tr><td colspan="7" class="text-center text-muted py-4">No se encontraron registros</td></tr>';
        } else {
            dataTable.forEach(function(registro) {
                var categoriaNombre = registro.categoria ? registro.categoria.BienCategoria : '-';
                html += '<tr>';
                html += '<td>' + registro.IdBien + '</td>';
                html += '<td>' + (registro.Nombre || '-') + '</td>';
                html += '<td>' + (registro.Codigo || '-') + '</td>';
                html += '<td>' + (registro.BienesFiscales || '-') + '</td>';
                html += '<td>' + categoriaNombre + '</td>';
                html += '<td class="text-center">' + (registro.Minimo || 0) + '</td>';
                html += '<td>';
                html += '<div class="btn-group btn-group-sm">';
                html += '<button type="button" onclick="editar(' + registro.IdBien + ')" class="btn btn-primary" title="Editar"><i class="fas fa-edit"></i></button>';
                html += '<button type="button" onclick="modalEliminar(' + registro.IdBien + ')" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
            });
        }
        
        $('#table_data').html(html);
        $('#total_registros').text((data.total || 0) + ' registros');
        renderPagination(data);
    })
    .catch(function(error) {
        console.error('Error:', error);
        $('#table_data').html('<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar datos</td></tr>');
    });
}

function renderPagination(data) {
    var from = data.from || 0;
    var to = data.to || 0;
    var total = data.total || 0;
    $('#page-info').html('<small class="text-muted">Mostrando ' + from + ' - ' + to + ' de ' + total + '</small>');
    
    var html = '';
    var current = data.current_page || 1;
    var last = data.last_page || 1;
    
    if (last <= 1) {
        $('#page-pagination').html('');
        return;
    }
    
    if (current > 1) {
        html += '<li class="page-item"><a class="page-link" href="javascript:goToPage(' + (current - 1) + ')">«</a></li>';
    }
    
    var start = Math.max(1, current - 2);
    var end = Math.min(last, start + 4);
    
    for (var i = start; i <= end; i++) {
        if (i === current) {
            html += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
        } else {
            html += '<li class="page-item"><a class="page-link" href="javascript:goToPage(' + i + ')">' + i + '</a></li>';
        }
    }
    
    if (current < last) {
        html += '<li class="page-item"><a class="page-link" href="javascript:goToPage(' + (current + 1) + ')">»</a></li>';
    }
    
    $('#page-pagination').html(html);
}

function goToPage(page) {
    cargarTabla(page);
}

function limpiar() {
    idEdit = 0;
    $('#form_main')[0].reset();
    $('#BienCategoria_Id').val('').trigger('change');
    $('#btn_eliminar').hide();
    $('#modal_title').html('<i class="fas fa-box mr-2"></i>Nuevo Bien');
}

function refrescarTabla() {
    console.log('Refrescando tabla...');
    cargarTabla(1);
}

// Exponer función global
window.refrescarTabla = refrescarTabla;

$(function() {
    $('.select2').select2({ width: '100%', dropdownParent: $('#modal_form') });

    // Botón agregar
    $('#btn_add').click(function() {
        limpiar();
        $('#modal_form').modal('show');
    });

    $('#form_main').submit(function(e) {
        e.preventDefault();
        guardar();
    });

    $('#btn_eliminar_modal').click(function() {
        eliminar();
    });

    $('#btn_limpiar').click(function() {
        limpiar();
    });
    
    $('#filtro_nombre').keypress(function(e) {
        if (e.which === 13) refrescarTabla();
    });
    
    $('#filtro_cantidad').change(function() {
        refrescarTabla();
    });
    
    $('#filtro_categoria').change(function() {
        console.log('Change event en categoria, valor:', $(this).val());
        refrescarTabla();
    });

    cargarTabla();
});
