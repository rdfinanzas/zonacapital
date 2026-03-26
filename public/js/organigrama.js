// Variables globales
var idEdit = 0;
var tipoEdit = -1;
var idsEdit = [];
var jefeTipoEdit = 0;
var jefeIdEdit = 0;
var jefeIdsEdit = [];
var datosJefeForm = null; // Guardar datos del formulario para reutilizar en confirmación

/**
 * Limpiar formulario y variables
 */
function limpiar() {
    idEdit = 0;
    tipoEdit = -1;
    idsEdit = [];
    const formCont = document.getElementById('form_cont');
    if (formCont) {
        formCont.remove();
    }
}

/**
 * Obtener organigrama desde el servidor
 */
async function getOrganigrama() {
    try {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('div_tree').style.display = 'none';
        document.getElementById('empty_state').style.display = 'none';

        const response = await apiLaravel('/organigrama-final', 'GET');

        document.getElementById('loading').style.display = 'none';

        if (response.response && response.response.trim() !== '') {
            document.getElementById('div_tree').innerHTML = response.response;
            document.getElementById('div_tree').style.display = 'block';
            initializeTreeTogglers();
        } else {
            document.getElementById('empty_state').style.display = 'block';
        }

    } catch (error) {
        console.error('Error al cargar organigrama:', error);
        document.getElementById('loading').style.display = 'none';

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar el organigrama'
        });
    }
}

/**
 * Inicializar los togglers del árbol
 */
function initializeTreeTogglers() {
    const togglers = document.getElementsByClassName("caret");

    for (let i = 0; i < togglers.length; i++) {
        // Remover listeners previos
        togglers[i].removeEventListener("click", toggleTreeNode);
        // Agregar nuevo listener
        togglers[i].addEventListener("click", toggleTreeNode);
    }
}

/**
 * Función para toggle de nodos del árbol
 */
function toggleTreeNode(event) {
    // Buscar el elemento nested en el elemento li padre
    let liElement = this.closest('li');
    let nested = liElement.querySelector('.nested');

    if (nested) {
        nested.classList.toggle("active");
        this.classList.toggle("caret-down");
    }
    event.stopPropagation();
}

/**
 * Editar nodo del árbol
 */
function editarTree(tipo, id) {
    limpiar(); // Remover formularios previos

    const labelElement = document.getElementById(`label_tree_${tipo}_${id}`);
    const currentName = labelElement.textContent.trim();

    let formHtml = `
        <div id='form_cont'>
            <div class="form-group">
                <label><strong>Editar nombre:</strong></label>
                <input id='text_add' type='text' class='form-control' value='${currentName}'>
                <button type='button' onclick='guardarEdit()' id='btn_add' class='btn btn-primary'>
                    <i class='fas fa-save'></i> Guardar
                </button>
                <button type='button' onclick='limpiar()' class='btn btn-secondary'>
                    <i class='fas fa-times'></i> Cancelar
                </button>
            </div>
        </div>
    `;

    // Insertar el formulario después del nodo completo
    const liElement = labelElement.closest('li');
    if (liElement) {
        liElement.insertAdjacentHTML('afterbegin', formHtml);
    }

    tipoEdit = tipo;
    idEdit = id;

    // Enfocar el input
    setTimeout(() => {
        document.getElementById('text_add').focus();
        document.getElementById('text_add').select();
    }, 100);
}

/**
 * Guardar edición de nodo
 */
async function guardarEdit() {
    const nombre = document.getElementById('text_add').value.trim();

    if (!nombre) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El nombre no puede estar vacío'
        });
        return;
    }

    try {
        const data = {
            id: idEdit,
            tipo: tipoEdit,
            nombre: nombre
        };

        const response = await apiLaravel('/organigrama/modificar', 'POST', data);

        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: response.message || 'Nodo modificado correctamente',
            timer: 2000,
            showConfirmButton: false
        });

        limpiar();
        await getOrganigrama();

    } catch (error) {
        console.error('Error al guardar:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al modificar el nodo'
        });
    }
}

/**
 * Eliminar nodo del árbol
 */
function delTree(tipo, id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Se eliminará este elemento y todos sus dependientes",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const data = {
                    id: id,
                    tipo: tipo
                };

                const response = await apiLaravel('/organigrama/eliminar', 'POST', data);

                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: response.message || 'Elemento eliminado correctamente',
                    timer: 2000,
                    showConfirmButton: false
                });

                limpiar();
                await getOrganigrama();

            } catch (error) {
                console.error('Error al eliminar:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al eliminar el elemento'
                });
            }
        }
    });
}

/**
 * Agregar nuevo nodo
 */
function addTree(tipo, id, ids) {
    limpiar(); // Remover formularios previos

    let placeholder = '';
    let tipoTexto = '';
    switch (tipo) {
        case -1:
            placeholder = 'Nombre de la gerencia';
            tipoTexto = 'gerencia';
            break;
        case 0:
            placeholder = 'Nombre del departamento';
            tipoTexto = 'departamento';
            break;
        case 1:
            placeholder = 'Nombre del servicio';
            tipoTexto = 'servicio';
            break;
        case 2:
            placeholder = 'Nombre del sector';
            tipoTexto = 'sector';
            break;
        default:
            placeholder = 'Nombre del elemento';
            tipoTexto = 'elemento';
    }

    let formHtml = `
        <div id='form_cont'>
            <div class="form-group">
                <label><strong><i class='fas fa-plus text-success'></i> Agregar nuevo ${tipoTexto}:</strong></label>
                <input id='text_add' type='text' class='form-control' placeholder='${placeholder}'>
                <button type='button' onclick='addNodo()' id='btn_add' class='btn btn-success'>
                    <i class='fas fa-plus'></i> Agregar
                </button>
                <button type='button' onclick='limpiar()' class='btn btn-secondary'>
                    <i class='fas fa-times'></i> Cancelar
                </button>
            </div>
        </div>
    `;

    if (tipo === -1) {
        // Para nueva gerencia, insertar al principio del contenedor
        document.querySelector('.card-body').insertAdjacentHTML('afterbegin', formHtml);
    } else {
        // Para otros elementos, insertar después del li padre
        const labelElement = document.getElementById(`label_tree_${tipo}_${id}`);
        const liElement = labelElement.closest('li');
        if (liElement) {
            liElement.insertAdjacentHTML('afterbegin', formHtml);
        }
    }

    tipoEdit = tipo;
    idEdit = id;
    idsEdit = ids || [];

    // Enfocar el input
    setTimeout(() => {
        document.getElementById('text_add').focus();
    }, 100);
}

/**
 * Agregar nuevo nodo al servidor
 */
async function addNodo() {
    const nombre = document.getElementById('text_add').value.trim();

    if (!nombre) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El nombre no puede estar vacío'
        });
        return;
    }

    try {
        const data = {
            id: idEdit,
            tipo: tipoEdit,
            ids: idsEdit,
            nombre: nombre
        };

        const response = await apiLaravel('/organigrama/add', 'POST', data);

        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: response.message || 'Elemento agregado correctamente',
            timer: 2000,
            showConfirmButton: false
        });

        limpiar();
        await getOrganigrama();

        // Expandir el árbol para mostrar el nuevo elemento
        setTimeout(() => {
            switch (Number(tipoEdit)) {
                case 0:
                    const ul0 = document.getElementById(`ul_0_${idEdit}`);
                    if (ul0) ul0.classList.add("active");
                    break;

                case 1:
                    if (idsEdit[0]) {
                        const ul0_1 = document.getElementById(`ul_0_${idsEdit[0]}`);
                        if (ul0_1) ul0_1.classList.add("active");
                    }
                    const ul1 = document.getElementById(`ul_1_${idEdit}`);
                    if (ul1) ul1.classList.add("active");
                    break;

                case 2:
                    if (idsEdit[0]) {
                        const ul0_2 = document.getElementById(`ul_0_${idsEdit[0]}`);
                        if (ul0_2) ul0_2.classList.add("active");
                    }
                    if (idsEdit[1]) {
                        const ul1_2 = document.getElementById(`ul_1_${idsEdit[1]}`);
                        if (ul1_2) ul1_2.classList.add("active");
                    }
                    const ul2 = document.getElementById(`ul_2_${idEdit}`);
                    if (ul2) ul2.classList.add("active");
                    break;
            }
        }, 500);

    } catch (error) {
        console.error('Error al agregar:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al agregar el elemento'
        });
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🌳 Módulo de Organigrama inicializado');

    // Cargar organigrama inicial
    getOrganigrama();

    // Manejar tecla Enter en inputs
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const target = e.target;
            if (target.id === 'text_add') {
                const btnAdd = document.getElementById('btn_add');
                if (btnAdd && btnAdd.onclick) {
                    btnAdd.click();
                }
            }
        }
    });

    // Manejar tecla Escape para cancelar
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Escape') {
            limpiar();
        }
    });
});

/**
 * Asignar jefe a un nodo del organigrama
 */
async function asignarJefe(tipo, id, ids, jefeNombreActual, jefeIdActual) {
    limpiar();

    // Crear nivel texto para el modal
    const niveles = ['Gerencia', 'Departamento', 'Servicio', 'Sector'];
    const nivelTexto = niveles[tipo] || 'Nodo';

    let formHtml = `
        <div id='form_jefe'>
            <div class="form-group">
                <label><strong><i class='fas fa-user-tie text-primary'></i> Asignar Jefe de ${nivelTexto}:</strong></label>
                <p class="text-muted small mb-2">Jefe actual: <strong>${jefeNombreActual}</strong></p>
                <select id='select_jefe' class='form-control select2' style='width: 100%;'>
                    <option value="">-- Sin Jefe --</option>
                </select>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="check_migrar" checked>
                    <label class="form-check-label" for="check_migrar">
                        Migrar automáticamente el personal al nuevo jefe
                    </label>
                </div>
                <button type='button' onclick='guardarJefe()' id='btn_guardar_jefe' class='btn btn-primary mt-2'>
                    <i class='fas fa-save'></i> Guardar
                </button>
                <button type='button' onclick='limpiar()' class='btn btn-secondary mt-2'>
                    <i class='fas fa-times'></i> Cancelar
                </button>
            </div>
        </div>
    `;

    // Insertar el formulario en el body (usando SweetAlert2)
    Swal.fire({
        title: 'Asignar Jefe',
        html: formHtml,
        showConfirmButton: false,
        showCloseButton: true,
        width: '600px',
        didOpen: () => {
            // Inicializar Select2 con AJAX
            $('#select_jefe').select2({
                placeholder: 'Escribe para buscar empleado por nombre o legajo...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#swal2-html-container'),
                minimumInputLength: 2, // Empezar a buscar después de 2 caracteres
                ajax: {
                    url: '/organigrama/empleados-disponibles',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.empleados.map(function(emp) {
                                return {
                                    id: emp.idEmpleado,
                                    text: emp.Apellido + ' ' + emp.Nombre + ' (Leg: ' + emp.Legajo + ')'
                                };
                            })
                        };
                    }
                },
                templateResult: function(result) {
                    if (!result.id) return result.text;
                    const text = result.text;
                    const match = text.match(/^(.+?) \(Leg:/);
                    if (match) {
                        return $('<span><strong>' + match[1] + '</strong> <small class="text-muted">(' + text + ')</small></span>');
                    }
                    return $('<span><strong>' + text + '</strong></span>');
                },
                templateSelection: function(result) {
                    if (!result.id) return result.text;
                    const text = result.text;
                    const match = text.match(/^(.+?) \(Leg:/);
                    return match ? match[1] : text;
                }
            });

            // Cargar el jefe actual si existe
            if (jefeIdActual) {
                // Crear opción manual para el jefe actual
                const option = new Option(jefeNombreActual, jefeIdActual, true, true);
                $('#select_jefe').append(option).trigger('change');
            }
        }
    });

    jefeTipoEdit = tipo;
    jefeIdEdit = id;
    jefeIdsEdit = ids || [];
}

/**
 * Guardar asignación de jefe
 */
async function guardarJefe(confirmarReemplazo = false) {
    console.log('=== GUARDAR JEFE - INICIO ===');
    
    let data;
    
    if (confirmarReemplazo && datosJefeForm) {
        // Usar datos guardados del formulario cuando se confirma el reemplazo
        data = { ...datosJefeForm, confirmar_reemplazo: true };
        console.log('Usando datos guardados:', data);
    } else {
        // Primera vez - obtener datos del formulario
        const nuevoJefeId = $('#select_jefe').val();
        const checkMigrar = document.getElementById('check_migrar');
        const migrarPersonal = checkMigrar ? checkMigrar.checked : true;
        
        data = {
            tipo: jefeTipoEdit,
            id: jefeIdEdit,
            ids: jefeIdsEdit,
            jefe_id: nuevoJefeId || null,
            migrar_personal: migrarPersonal,
            confirmar_reemplazo: false
        };
        
        // Guardar datos para reutilizar en confirmación
        datosJefeForm = { ...data };
    }
    
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    console.log('Data:', data);

    try {
        const response = await fetch('/organigrama/asignar-jefe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        console.log('Response status:', response.status);
        
        const result = await response.json();
        console.log('Result:', result);

        // Si requiere confirmación (código 409)
        if (response.status === 409 || result.requires_confirmation === true) {
            console.log('>>> ENTRA EN CONFIRMACION <<<');
            // Guardar datos por si se confirma
            datosJefeForm = { ...data };
            
            Swal.fire({
                icon: 'warning',
                title: 'Confirmar Reemplazo',
                html: result.message,
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fas fa-exchange-alt"></i> Sí, Reemplazar',
                cancelButtonText: 'Cancelar'
            }).then(async (resultSwal) => {
                if (resultSwal.isConfirmed) {
                    await guardarJefe(true);
                } else {
                    // Limpiar datos si cancela
                    datosJefeForm = null;
                }
            });
            return;
        }

        if (!response.ok) {
            throw new Error(result.message || 'Error al asignar jefe');
        }

        // Éxito - limpiar datos guardados
        datosJefeForm = null;
        
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: result.message || 'Jefe asignado correctamente',
            timer: 2000,
            showConfirmButton: false
        });

        Swal.close();
        limpiar();
        await getOrganigrama();

    } catch (error) {
        console.error('=== ERROR EN GUARDAR JEFE ===');
        console.error('error:', error);
        datosJefeForm = null;
        
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al asignar el jefe'
        });
    }
}
