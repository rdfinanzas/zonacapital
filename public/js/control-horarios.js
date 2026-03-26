/**
 * Control de Horarios - JavaScript
 * Migración del sistema original manteniendo la misma lógica
 * Usando Select2 en lugar de Typeahead/Bloodhound
 */

var data_form;
var Toast;
var pagina = 1;
var total = 0;
var dataTable;
var idEmpleado;
var dataControl;
var idJefe = 0;
var idPersonal = "";

// Configuración desde Laravel
const config = window.controlHorariosConfig || {};
const personalFullData = config.personalFull || [];
const personalLimitData = config.personalLimit || [];

function inputHour(event) {
    let valor = event.target.value;
    valor = valor.replace(/[^\d:]/g, '');
    if (!valor) return;
    if (valor.length >= 2 && valor.indexOf(':') === -1) valor = valor + ':';
    if (parseInt(valor.substring(0, 2)) > 23) valor = '23' + valor.substring(2);
    if (parseInt(valor.substring(3)) > 59) valor = valor.substring(0, 3) + '59';
    event.target.value = valor;
}

function backSpaceDeleteHour(event) {
    if (event.key === 'Backspace' && event.target.value.charAt(2) === ':' && event.target.value.length === 3) {
        event.target.value = event.target.value.substring(0, 2);
    }
    if (event.key === 'Backspace' && event.target.value.charAt(1) === ':' && event.target.value.length === 2) {
        event.target.value = event.target.value.substring(0, 1);
    }
}

function limpiarCampo(el) {
    $($(el).attr("for")).val("");
    $($(el).attr("for")).select2('val', '').trigger('change');
}

function exportar() {
    if ($('#personal').val() === "") idEmpleado = 0;
    if ($('#personal').val() === "") idPersonal = "";
    if ($('#certifica').val() === "") idJefe = 0;

    const params = new URLSearchParams({
        id: idPersonal,
        ger: $("#ger_fil").val() || 0,
        dep: $("#dep_fil").val() || 0,
        serv: $("#servicio_fil").val() || 0,
        tipo: $("#tipo").val(),
        idEmpleado: idEmpleado,
        idJefe: idJefe,
        desde: formatDateForBackend($("#d_fil").val()),
        hasta: formatDateForBackend($("#h_fil").val())
    });

    window.open("/api/control-horarios/exportar-pdf?" + params.toString(), "_blank");
}

function exportarTabla() {
    exportar();
}

function exportarPdf() {
    exportar();
}

function exportarExcel() {
    if ($("#overlay").length == 0) {
        let html_modal =
            '<div class="overlay-fixed overlay-wrapper" id="overlay">' +
            '  <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Cargando...</div></div>' +
            "</div>";
        $("body").append(html_modal);
    } else {
        $("#overlay").show();
    }

    if ($('#personal').val() === "") idEmpleado = 0;
    if ($('#personal').val() === "") idPersonal = "";
    if ($('#certifica').val() === "") idJefe = 0;

    const params = new URLSearchParams({
        id: idPersonal,
        ger: $("#ger_fil").val() || 0,
        dep: $("#dep_fil").val() || 0,
        serv: $("#servicio_fil").val() || 0,
        tipo: $("#tipo").val(),
        idEmpleado: idEmpleado,
        idJefe: idJefe,
        desde: formatDateForBackend($("#d_fil").val()),
        hasta: formatDateForBackend($("#h_fil").val())
    });

    fetch("/api/control-horarios/exportar-excel?" + params.toString(), {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
        method: "GET",
    })
        .then((resp) => resp.blob())
        .then((blob) => {
            $("#overlay").hide();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.style.display = "none";
            a.href = url;
            a.download = "control_horarios.xlsx";
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(() => {
            $("#overlay").hide();
            Toast.fire({ icon: "error", title: "Error al exportar" });
        });
}

function changeOrganigrama(tipo, el) {
    if ($(el).val() == "") return 0;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    switch (tipo) {
        case 0:
            if (!$(el).val()) {
                $("#dep_fil").html('<option value="">-</option>').trigger('change');
                $("#servicio_fil").html('<option value="">-</option>').trigger('change');
                return;
            }
            $.ajax({
                url: "/api/control-horarios/departamentos/" + $(el).val(),
                headers: { 'X-CSRF-TOKEN': token },
                success: function (dataResponse) {
                    let dataSelect = dataResponse.response;
                    let html = "";
                    html += '<option value="">-</option>';
                    dataSelect.forEach(function (registro) {
                        html += "<option value=" + registro.idDepartamento + ">" + registro.Departamento + "</option>";
                    });
                    $("#dep_fil").html(html).trigger('change');
                    $("#servicio_fil").html('<option value="">-</option>').trigger('change');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    Toast.fire({ icon: "error", title: errorThrown });
                }
            });
            break;

        case 1:
            if (!$(el).val()) {
                $("#servicio_fil").html('<option value="">-</option>').trigger('change');
                return;
            }
            $.ajax({
                url: "/api/control-horarios/servicios/" + $(el).val(),
                headers: { 'X-CSRF-TOKEN': token },
                success: function (dataResponse) {
                    let dataSelect = dataResponse.response;
                    let html = "";
                    html += '<option value="">-</option>';
                    dataSelect.forEach(function (registro) {
                        html += "<option value=" + registro.idServicio + ">" + registro.Servicio + "</option>";
                    });
                    $("#servicio_fil").html(html).trigger('change');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    Toast.fire({ icon: "error", title: errorThrown });
                }
            });
            break;

        case 2:
            // Sectores - actualmente no implementado
            break;
    }
}

function editar(key, ind) {
    if (document.getElementById("todo_personal_control").value !== "1") return;

    // Buscar el item en dataControl por _key
    const item = dataControl.find(i => i._key === key);
    if (!item) return;

    // La clave es fecha_legajo (formato: YYYY-MM-DD_legajo)
    const fecha = key.split("_")[0];
    const legajo = key.split("_")[1];

    const splitHour = item[4].split(" - ");
    let entrada = splitHour[0] ?? '';
    let salida = splitHour[1] ?? '';

    const splitHour2 = item[7].split(" - ");
    let entrada2 = splitHour2[0] ?? '';
    let salida2 = splitHour2[1] ?? '';

    entrada = (entrada === "SIN DATO") ? '' : entrada;
    salida = (salida === "SIN DATO") ? '' : salida;
    entrada2 = (entrada2 === "SIN DATO") ? '' : entrada2;
    salida2 = (salida2 === "SIN DATO") ? '' : salida2;

    let check1 = false;
    let check2 = false;

    if (entrada !== "" && salida !== "") {
        if ((Number(entrada.split(":")[0]) >= Number(salida.split(":")[0]) || Number(item[5].split(":")[0]) >= 24) && Number(item[5].split(":")[0]) >= 1) {
            check1 = true;
        }
    }

    if (entrada2 !== "" && salida2 !== "" && item[8] !== 0) {
        if ((Number(entrada2.split(":")[0]) >= Number(salida2.split(":")[0]) || Number(item[8].split(":")[0]) >= 24) && Number(item[8].split(":")[0]) >= 1) {
            check2 = true;
        }
    }

    const modalEl = document.getElementById('modal_control');
    const modal = new bootstrap.Modal(modalEl);

    $("#cont_editar").html(`
        <div class="row" style="min-width: 170px;margin-right: 0px;">
            <div class="form-group col" style="padding: 0px;">
                <label>Entrada</label>
            </div>
            <div class="form-group col" style="padding-left: 5px;">
                <label>Salida</label>
            </div>
            <div class="form-group col-md-2" style="padding: 0px;">
                <label>Dia Sig.</label>
            </div>
        </div>
        <div class="row" style="min-width: 170px;margin-right: 0px;">
            <div class="form-group col" style="padding: 0px;">
                <input type="text" name="${key}_e_0" value="${entrada ?? ''}" placeholder="00:00" id="${key}_e_0" autocomplete="off" class="form-control form-control-sm">
            </div>
            <div class="form-group col" style="padding-left: 5px;">
                <input type="text" name="${key}_s_0" value="${salida ?? ''}" placeholder="00:00" id="${key}_s_0" autocomplete="off" class="form-control form-control-sm">
            </div>
            <div class="form-group col-md-2" style="padding: 0px;">
                <input title="La salida es del dia siguiente" alt="La salida es del dia siguiente" type="checkbox" ${check1 ? 'checked' : ''} id="${key}_ds_0" name="${key}_ds_0">
            </div>
        </div>
        <div class="row" style="min-width: 170px;margin-right: 0px;">
            <div class="form-group col" style="padding: 0px;">
                <input type="text" name="${key}_e_1" value="${entrada2 ?? ''}" placeholder="00:00" id="${key}_e_1" autocomplete="off" class="form-control form-control-sm">
            </div>
            <div class="form-group col" style="padding-left: 5px;">
                <input type="text" name="${key}_s_1" value="${salida2 ?? ''}" placeholder="00:00" id="${key}_s_1" autocomplete="off" class="form-control form-control-sm">
            </div>
            <div class="form-group col-md-2" style="padding: 0px;">
                <input title="La salida es del dia siguiente" alt="La salida es del dia siguiente" type="checkbox" ${check2 ? 'checked' : ''} id="${key}_ds_1" name="${key}_ds_1">
            </div>
        </div>
    `);

    $("#btn_guardar_control").off('click').on('click', function() {
        guardarEdit(key, ind);
    });

    $(`#${key}_e_0`)[0].addEventListener('input', inputHour);
    $(`#${key}_e_0`)[0].addEventListener('keydown', backSpaceDeleteHour);
    $(`#${key}_e_1`)[0].addEventListener('input', inputHour);
    $(`#${key}_e_1`)[0].addEventListener('keydown', backSpaceDeleteHour);
    $(`#${key}_s_0`)[0].addEventListener('input', inputHour);
    $(`#${key}_s_0`)[0].addEventListener('keydown', backSpaceDeleteHour);
    $(`#${key}_s_1`)[0].addEventListener('input', inputHour);
    $(`#${key}_s_1`)[0].addEventListener('keydown', backSpaceDeleteHour);

    modal.show();
}

function isNumeric(value) {
    return /^-?\d+$/.test(value);
}

function guardarEdit(key, ind) {
    $(`#${key}_e_0`).removeClass("is-invalid");
    $(`#${key}_s_0`).removeClass("is-invalid");
    $(`#${key}_e_1`).removeClass("is-invalid");
    $(`#${key}_s_1`).removeClass("is-invalid");

    if ($(`#${key}_e_0`).val() !== "") {
        if (!isNumeric($(`#${key}_e_0`).val().split(":")[0])) {
            $(`#${key}_e_0`).addClass("is-invalid");
            return;
        } else {
            if (Number($(`#${key}_e_0`).val().split(":")[0]) >= 24) {
                $(`#${key}_e_0`).addClass("is-invalid");
                return;
            }
        }
        if (!isNumeric($(`#${key}_e_0`).val().split(":")[1])) {
            $(`#${key}_e_0`).addClass("is-invalid");
            return;
        } else {
            if (Number($(`#${key}_e_0`).val().split(":")[1]) >= 60) {
                $(`#${key}_e_0`).addClass("is-invalid");
                return;
            }
        }
    }
    if ($(`#${key}_s_0`).val() !== "") {
        if (!isNumeric($(`#${key}_s_0`).val().split(":")[0])) {
            $(`#${key}_s_0`).addClass("is-invalid");
            return;
        } else {
            if (Number($(`#${key}_s_0`).val().split(":")[0]) >= 24) {
                $(`#${key}_s_0`).addClass("is-invalid");
                return;
            }
        }
        if (!isNumeric($(`#${key}_s_0`).val().split(":")[1])) {
            $(`#${key}_s_0`).addClass("is-invalid");
            return;
        } else {
            if (Number($(`#${key}_s_0`).val().split(":")[1]) >= 60) {
                $(`#${key}_s_0`).addClass("is-invalid");
                return;
            }
        }
    }
    if ($(`#${key}_e_1`).val() !== "") {
        if (!isNumeric($(`#${key}_e_1`).val().split(":")[0])) {
            $(`#${key}_e_1`).addClass("is-invalid");
            return;
        } else {
            if (Number($(`#${key}_e_1`).val().split(":")[0]) >= 24) {
                $(`#${key}_e_1`).addClass("is-invalid");
                return;
            }
        }
        if (!isNumeric($(`#${key}_e_1`).val().split(":")[1])) {
            $(`#${key}_e_1`).addClass("is-invalid");
            return;
        } else {
            if (Number($(`#${key}_e_1`).val().split(":")[1]) >= 60) {
                $(`#${key}_e_1`).addClass("is-invalid");
                return;
            }
        }
    }
    if ($(`#${key}_s_1`).val() !== "") {
        if (!isNumeric($(`#${key}_s_1`).val().split(":")[0])) {
            $(`#${key}_s_1`).addClass("is-invalid");
            return;
        } else {
            if (Number($(`#${key}_s_1`).val().split(":")[0]) >= 24) {
                $(`#${key}_s_1`).addClass("is-invalid");
                return;
            }
        }
        if (!isNumeric($(`#${key}_s_1`).val().split(":")[1])) {
            $(`#${key}_s_1`).addClass("is-invalid");
            return;
        } else {
            if (Number($(`#${key}_s_1`).val().split(":")[1]) >= 60) {
                $(`#${key}_s_1`).addClass("is-invalid");
                return;
            }
        }
    }

    // La clave es fecha_legajo (formato: YYYY-MM-DD_legajo)
    const fecha = key.split("_")[0];
    const legajo = key.split("_")[1];

    data_form = {
        e0: $(`#${key}_e_0`).val(),
        e1: $(`#${key}_e_1`).val(),
        ds0: $(`#${key}_ds_0`).is(":checked"),
        s0: $(`#${key}_s_0`).val(),
        s1: $(`#${key}_s_1`).val(),
        ds1: $(`#${key}_ds_1`).is(":checked"),
        fecha: fecha,
        legajo: legajo,
    };

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Buscar el item para actualizar la UI
    const item = dataControl.find(i => i._key === key);

    $(`#td_${key}_${ind}`).parent().css("background-color", "#8ae887");
    $(`#td_${key}_${ind}`).parent().css("color", "#000");
    $(`#td_${key}_${ind}`).parent().css("font-weight", "normal");
    $(`#td_${key}_${ind}`).html(ind === 0 ? (item ? item[4] : '') : (item ? item[7] : ''));

    $.ajax({
        url: "/api/control-horarios/marcas",
        headers: { 'X-CSRF-TOKEN': token },
        method: "POST",
        data: data_form,
        success: function (dataResponse) {
            if (dataResponse.status === 1) {
                Toast.fire({ icon: 'success', title: 'Se guardó correctamente' });
                bootstrap.Modal.getInstance(document.getElementById('modal_control')).hide();
                $('#form_buscar').trigger('submit');
            } else {
                Toast.fire({ icon: 'error', title: dataResponse.error || 'Error al guardar' });
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            Toast.fire({ icon: 'error', title: errorThrown });
        }
    });
    window.event.stopPropagation();
}

function cancelarEdit(key, ind) {
    const item = dataControl.find(i => i._key === key);
    $(`#td_${key}_${ind}`).html(ind === 0 ? (item ? item[4] : '') : (item ? item[7] : ''));
    window.event.stopPropagation();
}

// Inicialización de Select2
function initSelect2() {
    const todoPersonal = document.getElementById("todo_personal_control")?.value === "1";
    const personalData = todoPersonal ? personalFullData : personalLimitData;

    // Formatear datos para Select2
    const select2Data = personalData.map(item => ({
        id: item.id,
        text: item.value,
        idEmpleado: item.idEmpleado || item.id
    }));

    // Select2 para certifica (jefes)
    $('#certifica').select2({
        width: '100%',
        placeholder: 'Buscar jefe/certificador...',
        allowClear: true,
        data: select2Data
    });

    $('#certifica').on('select2:select', function(e) {
        const data = e.params.data;
        idJefe = data.idEmpleado || data.id;
    });

    $('#certifica').on('select2:clear', function() {
        idJefe = 0;
    });

    // Select2 para personal
    $('#personal').select2({
        width: '100%',
        placeholder: 'Buscar personal...',
        allowClear: true,
        data: select2Data
    });

    $('#personal').on('select2:select', function(e) {
        const data = e.params.data;
        idPersonal = data.id;
        idEmpleado = data.idEmpleado || data.id;
    });

    $('#personal').on('select2:clear', function() {
        idPersonal = "";
        idEmpleado = 0;
    });

    // Select2 para organigrama
    $('#ger_fil, #dep_fil, #servicio_fil').select2({
        width: '100%',
        placeholder: '-TODOS-',
        allowClear: true
    });
}

// Función para convertir fecha de formato nativo (YYYY-MM-DD) a formato backend (DD/MM/YYYY)
function formatDateForBackend(dateString) {
    if (!dateString) return '';
    const parts = dateString.split('-');
    if (parts.length !== 3) return dateString;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

// Función para establecer fechas por defecto (primer y último día del mes actual)
function setDefaultDates() {
    const hoy = new Date();
    const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

    // Formato YYYY-MM-DD para input type="date"
    const formatYMD = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (!$('#d_fil').val()) {
        $('#d_fil').val(formatYMD(primerDia));
    }
    if (!$('#h_fil').val()) {
        $('#h_fil').val(formatYMD(ultimoDia));
    }
}

// Inicialización
$(function () {
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 6000
    });

    // Inicializar Select2
    initSelect2();

    // Establecer fechas por defecto
    setDefaultDates();

    // Manejar envío del formulario
    $('#form_buscar').on('submit', function (e) {
        e.preventDefault();

        if ($('#personal').val() === "") idEmpleado = 0;
        if ($('#personal').val() === "") idPersonal = "";
        if ($('#certifica').val() === "") idJefe = 0;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Convertir fechas de YYYY-MM-DD a DD/MM/YYYY
        const desdeFormatted = formatDateForBackend($("#d_fil").val());
        const hastaFormatted = formatDateForBackend($("#h_fil").val());

        // Mostrar loading
        if ($("#overlay").length === 0) {
            $('body').append(`
                <div class="overlay-fixed" id="overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                     background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-white">
                        <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                        <div class="h5">Cargando...</div>
                    </div>
                </div>
            `);
        } else {
            $("#overlay").show();
        }

        $.ajax({
            url: "/api/control-horarios",
            headers: { 'X-CSRF-TOKEN': token },
            method: "GET",
            data: {
                id: idPersonal,
                ger: $("#ger_fil").val(),
                dep: $("#dep_fil").val(),
                serv: $("#servicio_fil").val(),
                tipo: $("#tipo").val(),
                idEmpleado: idEmpleado,
                idJefe: idJefe,
                desde: desdeFormatted,
                hasta: hastaFormatted
            },
            success: function (response) {
                $("#overlay").hide();

                if (response.status !== 1) {
                    Toast.fire({ icon: "error", title: response.error || 'Error al obtener datos' });
                    return;
                }

                dataControl = response.response;
                let html = "";
                let style = "";
                let htmlHead = `<tr>
                    <th><div>Personal</div></th>
                    <th style="width: 100px;"><div>Lic/Fer</div></th>
                    <th><div>Prog. Turn. 1</div></th>
                    <th><div>Guardias</div></th>
                    <th><div>Marcas</div></th>
                    <th><div>Horas</div></th>
                </tr>`;

                let doble = false;
                // dataControl ahora es un array, iterar directamente
                dataControl.forEach(function(item) {
                    // Solo verificar items de empleados (no filas de fecha)
                    if (item._tipo === 'empleado' && item[7] !== "" && item[7] !== undefined) {
                        doble = true;
                        htmlHead = `<tr>
                            <th><div>Personal</div></th>
                            <th style="width: 100px;"><div>Lic/Fer</div></th>
                            <th><div>Prog. Turn. 1</div></th>
                            <th><div>Guardias</div></th>
                            <th><div>Marcas</div></th>
                            <th><div>Horas</div></th>
                            <th><div>Prog. Turn. 2</div></th>
                            <th><div>Marcas</div></th>
                            <th><div>Horas</div></th>
                        </tr>`;
                        return; // Salir del loop una vez encontrado
                    }
                });

                $("#head_table").html(htmlHead);

                // dataControl ahora es un array indexado, no un objeto asociativo
                // Cada elemento tiene _key y _tipo para identificarlo
                dataControl.forEach(function(item) {
                    const key = item._key;
                    const esFilaFecha = item._tipo === 'fecha';
                    const esFilaFechaVacia = item._tipo === 'fecha_vacia';

                    // DEBUG: Mostrar items con tipo_marca = 2
                    if (!esFilaFecha && !esFilaFechaVacia && item[9] == 2) {
                        console.log('Item con tipo_marca=2:', item);
                    }

                    // Determinar clase de estilo de fila (usando clases CSS con !important)
                    let rowClass = "";
                    if (esFilaFecha) {
                        rowClass = "fila-fecha";
                    } else if (esFilaFechaVacia) {
                        // Fila vacía sin estilo especial
                        rowClass = "";
                    } else if (item[9] == 2) {
                        rowClass = "fila-marca-incompleta";
                    } else if (item[9] == 1) {
                        rowClass = "fila-ausencia";
                    } else if (item[1] != "" && item[1]) {
                        rowClass = "fila-licencia";
                    }

                    // Determinar estilo y onclick de la primera celda (nombre)
                    const nombreLength = item[0].length;
                    const canEdit = !esFilaFecha && !esFilaFechaVacia && nombreLength >= 10;
                    let nombreStyle = "cursor:pointer;";
                    let nombreOnclick = "";

                    // Para filas de fecha (no vacías), ajustar el tamaño de fuente
                    if (esFilaFecha && item.length === 6 && nombreLength === 5) {
                        nombreStyle += "font-size: 13px !important;font-weight: bold;";
                    } else if (esFilaFecha && item.length === 6 && nombreLength !== 5) {
                        nombreStyle += "font-size: 11px !important;font-weight: bold;";
                    }

                    if (canEdit) {
                        // Escape the key for HTML attributes to prevent breaking
                        const escapedKey = key.replace(/'/g, "\\'");
                        nombreOnclick = `onclick="editar('${escapedKey}',0)"`;
                    }

                    // Determinar onclick de la celda de marcas
                    let marcasOnclick = "";
                    let marcasId = `td_${key}_0`;
                    if (canEdit) {
                        const escapedKey = key.replace(/'/g, "\\'");
                        marcasOnclick = `style="cursor:pointer" onclick="editar('${escapedKey}',0)"`;
                    }

                    // Construir fila
                    let classAttr = rowClass ? `class="${rowClass}"` : "";

                    let rowHTML = "<tr " + classAttr + ">";

                    html += rowHTML;

                    // Si es fila de fecha vacía, solo tiene una celda
                    if (esFilaFechaVacia) {
                        html += "<td>" + item[0] + "</td>";
                    } else {
                        // Celda de nombre
                        html += "<td " + nombreOnclick + " style=\"" + nombreStyle + "\" class=\"hover_td\">" + item[0] + "</td>";

                        // Celda de Lic/Fer
                        let licFer = item[1] ? item[1].substring(0, 20) : "";
                        html += "<td>" + licFer + "</td>";

                        // Celdas de datos
                        html += "<td class=\"text-center\">" + (item[2] || "") + "</td>";
                        html += "<td class=\"text-center\">" + (item[3] || "") + "</td>";

                        // Celda de marcas (con id especial y onclick)
                        html += "<td id=\"" + marcasId + "\" " + marcasOnclick + " class=\"hover_td text-center\">" + (item[4] || "") + "</td>";

                        // Celda de horas
                        html += "<td class=\"text-center font-weight-bold\">" + (item[5] || "") + "</td>";

                        // Columnas adicionales si es doble turno
                        if (doble) {
                            html += "<td class=\"text-center\">" + (item[6] || "") + "</td>";
                            html += "<td class=\"text-center\">" + (item[7] || "") + "</td>";
                            html += "<td class=\"text-center font-weight-bold\">" + (item[8] || "") + "</td>";
                        }
                    }

                    html += "</tr>";
                });

                $("#table_horarios").html(html);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#overlay").hide();
                Toast.fire({ icon: "error", title: errorThrown });
            }
        });
    });
});
