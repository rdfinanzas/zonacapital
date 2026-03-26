var pagina = 0;
var total = 0;
var paginas = 0;
var data_ob = null;
var idEdit = 0;

function addObs() {
    var obs = $('#obs_prov_txt').val();
    var idProv = $('#IdProveedor').val();

    if (!obs.trim()) {
        alert('Debe ingresar una observación');
        return;
    }

    if (idProv == 0) {
        alert('Debe seleccionar un proveedor');
        return;
    }

    $.ajax({
        url: 'proveedores/add-obs',
        type: 'POST',
        data: {
            obs: obs,
            id_prov: idProv
        },
        success: function(data) {
            if (data.success) {
                alert('Se guardó correctamente');
                $('#obs_prov_txt').val('');
                getProveedores();
            }
        },
        error: function() {
            alert('Error al guardar');
        }
    });
}

function editar(i) {
    idEdit = data_ob.registros[i].IdProveedor;

    $.ajax({
        url: 'proveedores/change-sts-msj',
        type: 'POST',
        data: {
            id: idEdit
        },
        success: function(data) {
        }
    });

    $('#IdProveedor').val(data_ob.registros[i].IdProveedor);
    $('#Proveedor').val(data_ob.registros[i].Proveedor);
    $('#doc').val(data_ob.registros[i].Documento);
    $('#tipo_doc').val(data_ob.registros[i].TipoDoc);
    $('#cp').val(data_ob.registros[i].Cp);
    $('#piso').val(data_ob.registros[i].PisoDep);
    $('#puerta').val(data_ob.registros[i].NumPuerta);
    $('#cond_ing_br').val(data_ob.registros[i].CondIngBruto);
    $('#cond_gn').val(data_ob.registros[i].CondGanan);
    $('#ing_bruto').val(data_ob.registros[i].NumIngBruto);
    $('#Cuenta').val(data_ob.registros[i].CuentaPago);
    $('#Direccion').val(data_ob.registros[i].Direccion);
    $('#Telefono').val(data_ob.registros[i].Telefono);
    $('#Email').val(data_ob.registros[i].Email);
    $('#clave').val(data_ob.registros[i].ClaveIden);
    $('#Localidad').val(data_ob.registros[i].Localidad);
    $('#prov').val(data_ob.registros[i].ProvinciaProv_Id);
    $('#TipoEmpresa_Id').val(data_ob.registros[i].TipoEmpresa_Id);
    $('#CondicionIva_Id').val(data_ob.registros[i].CondIva);
    $('#NombreFantasia').val(data_ob.registros[i].NombreFantasia);
    $('#Notas').val(data_ob.registros[i].Notas);
    $('#Banco').val(data_ob.registros[i].Banco);
    $('#cbu').val(data_ob.registros[i].CBU);

    var str = '';
    var obs = data_ob.obs[i] || [];
    for (var j = 0; j < obs.length; j++) {
        var bgcolor = (j % 2 == 0) ? '#FFFFFF' : '#EDEDED';
        str += '<tr style="background-color: ' + bgcolor + '">';
        str += '<td>' + obs[j].FF + '</td>';
        str += '<td>' + obs[j].Apellido + ', ' + obs[j].Nombre + '</td>';
        str += '<td>' + obs[j].ObsProv + '</td>';
        str += '</tr>';
    }
    $('#obs_list').html(str);
}

function eliminar(i) {
    if (confirm('¿Está seguro que desea borrar?')) {
        $.ajax({
            url: 'proveedores/' + data_ob.registros[i].IdProveedor,
            type: 'DELETE',
            data: {},
            success: function(data) {
                if (data.success) {
                    alert('Se borró correctamente');
                } else {
                    alert('No se pudo borrar');
                }
                pagina = 0;
                buscarProv();
            },
            error: function() {
                alert('Error al eliminar');
            }
        });
    }
}

function irA(p) {
    pagina = p;
    getProveedores();
}

function buscarProv() {
    idEdit = 0;
    $('#IdProveedor').val(0);
    irA(0);
}

function getProveedores() {
    $('#loading').show();
    $('#listado').hide();

    var buscar = $('#BuscarProveedor').val();

    $.ajax({
        url: 'proveedores/get-proveedores',
        type: 'GET',
        data: {
            buscar: buscar,
            pagina: pagina
        },
        success: function(data) {
            $('#loading').hide();
            data_ob = data;
            paginas = data.paginas;
            $('#total').html(data.total);

            var listado = '';
            listado += '<table class="table table-striped">';
            listado += '<thead>';
            listado += '<tr>';
            listado += '<th>Razón social</th>';
            listado += '<th>Nombre fantasía</th>';
            listado += '<th width="120">Acciones</th>';
            listado += '</tr>';
            listado += '</thead>';
            listado += '<tbody>';

            for (var i = 0; i < data.registros.length; i++) {
                var bgcolor = '#FFFFFF';
                if (data.registros[i].Msj != null) {
                    bgcolor = '#9FF781';
                }

                listado += '<tr style="background-color: ' + bgcolor + '">';
                listado += '<td>' + data.registros[i].Proveedor + '</td>';
                listado += '<td>' + data.registros[i].NombreFantasia + '</td>';
                listado += '<td>';
                listado += '<div class="btn-group">';
                listado += '<button type="button" onclick="editar(' + i + ')" class="btn btn-primary btn-xs"><i class="fas fa-edit" aria-hidden="true"></i></button>';
                listado += '<button type="button" onclick="eliminar(' + i + ')" class="btn btn-danger btn-xs"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                listado += '</div>';
                listado += '</td>';
                listado += '</tr>';
            }

            listado += '</tbody>';
            listado += '</table>';
            $('#tabla_registros').html(listado);

            var html = '<select id="cbopaginas">';
            for (var i = 0; i < data.paginas; i++) {
                var sel = (pagina == i) ? 'selected="selected"' : '';
                html += '<option value="' + (i + 1) + '" ' + sel + '>' + (i + 1) + '</option>';
            }
            html += '</select>';
            $('#paginas').html(html);

            $('#cbopaginas').change(function() {
                pagina = (this.value - 1);
                $('#BuscarProveedor').click();
            });

            html = '';
            if (pagina > 0) {
                html += '<button type="button" onclick="irA(0)" class="btn btn-sm btn-default"><i class="fas fa-step-backward"></i></button> ';
                html += '<button type="button" onclick="irA(' + (pagina - 1) + ')" class="btn btn-sm btn-default"><i class="fas fa-chevron-left"></i></button> ';
            }
            html += 'Página ' + (pagina + 1) + ' de ' + paginas + ' ';
            if (pagina < (paginas - 1)) {
                html += '<button type="button" onclick="irA(' + (pagina + 1) + ')" class="btn btn-sm btn-default"><i class="fas fa-chevron-right"></i></button> ';
                html += '<button type="button" onclick="irA(' + (paginas - 1) + ')" class="btn btn-sm btn-default"><i class="fas fa-step-forward"></i></button>';
            }
            $('#navegador').html(html);

            $('#listado').show();

            if (idEdit != 0) {
                for (var i = 0; i < data.registros.length; i++) {
                    if (data.registros[i].IdProveedor == idEdit) {
                        editar(i);
                        break;
                    }
                }
            }
        },
        error: function() {
            $('#loading').hide();
            alert('Error al cargar proveedores');
        }
    });
}

$(function() {
    $('#btn_add_obs').click(function() {
        addObs();
    });

    $('#btn_limpiar').click(function() {
        $('#IdProveedor').val(0);
        idEdit = 0;
        $('#form_main')[0].reset();
        $('#obs_list').html('');
    });

    $('#btn_eliminar').click(function() {
        if (idEdit == 0) {
            alert('Debe seleccionar un proveedor');
            return;
        }

        if (confirm('¿Está seguro que desea borrar?')) {
            $.ajax({
                url: 'proveedores/' + idEdit,
                type: 'DELETE',
                data: {},
                success: function(data) {
                    if (data.success) {
                        alert('Se borró correctamente');
                    } else {
                        alert('No se pudo borrar');
                    }
                    pagina = 0;
                    $('#btn_limpiar').click();
                    buscarProv();
                },
                error: function() {
                    alert('Error al eliminar');
                }
            });
        }
    });

    $('#form_main').submit(function(e) {
        e.preventDefault();

        var data = $('#form_main').serializeJSON();
        var url = 'proveedores';
        var method = 'POST';

        if ($('#IdProveedor').val() > 0) {
            url = 'proveedores/' + $('#IdProveedor').val();
            method = 'PUT';
        }

        $.ajax({
            url: url,
            type: method,
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    pagina = 0;
                    $('#btn_limpiar').click();
                    buscarProv();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Error al guardar');
            }
        });
    });

    $.ajax({
        url: 'proveedores/get-provincias',
        type: 'GET',
        success: function(data) {
            var options = '<option value="">-SELECCIONAR-</option>';
            data.forEach(function(prov) {
                options += '<option value="' + prov.IdProvincia + '">' + prov.Provincia + '</option>';
            });
            $('#prov').html(options);
        }
    });

    $.ajax({
        url: 'proveedores/get-tipo-empresas',
        type: 'GET',
        success: function(data) {
            var options = '<option value="">-SELECCIONAR-</option>';
            data.forEach(function(tipo) {
                options += '<option value="' + tipo.IdTipoEmpresa + '">' + tipo.TipoEmpresa + '</option>';
            });
            $('#TipoEmpresa_Id').html(options);
        }
    });

    $.ajax({
        url: 'proveedores/get-condiciones-iva',
        type: 'GET',
        success: function(data) {
            var options = '<option value="0">-</option>';
            data.forEach(function(cond) {
                options += '<option value="' + cond.IdCondicionIva + '">' + cond.CondicionIva + '</option>';
            });
            $('#CondicionIva_Id').html(options);
        }
    });

    getProveedores();
});
