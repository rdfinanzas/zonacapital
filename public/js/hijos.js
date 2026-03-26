// ========================================
// GESTIÓN DE HIJOS DE EMPLEADOS
// ========================================

let hijosAsignados = [];
let empleadoId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Obtener ID del empleado
    empleadoId = document.getElementById('empleado_id').value;
    if (empleadoId) {
        cargarHijos();
    }
});

// ========================================
// FUNCIÓN PARA NAVEGAR A GESTIÓN DE HIJOS
// ========================================

/**
 * Navegar a la vista de gestión de hijos
 * Esta función se llama desde personal.js
 */
function gestionarHijos(id) {
    window.location.href = `/personal/${id}/hijos/gestionar`;
}

// ========================================
// CARGAR HIJOS
// ========================================

/**
 * Cargar los hijos del empleado desde la API
 */
async function cargarHijos() {
    try {
        const response = await fetch(`${window.Laravel.baseUrl}/personal/${empleadoId}/hijos`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        });

        const result = await response.json();

        if (result.success) {
            hijosAsignados = result.data;
            renderizarHijos();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Error al cargar los hijos'
            });
        }
    } catch (error) {
        console.error('Error al cargar hijos:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al comunicarse con el servidor'
        });
    }
}

// ========================================
// RENDERIZAR HIJOS
// ========================================

/**
 * Renderizar la tabla de hijos
 */
function renderizarHijos() {
    const tbody = document.getElementById('tbody_hijos');

    if (hijosAsignados.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted">
                    No hay hijos cargados. Haga clic en "Agregar Hijo" para comenzar.
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    hijosAsignados.forEach(hijo => {
        const fechaNac = hijo.FecNac ? formatearFecha(hijo.FecNac) : '-';
        const edad = hijo.Edad ? `${hijo.Edad} años` : '-';
        const convive = hijo.Convive ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-danger">No</span>';
        const estudia = hijo.Estudia ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-danger">No</span>';
        const nivel = hijo.NivelEducativo || '-';

        html += `
            <tr>
                <td>${hijo.Apellido || ''} ${hijo.Nombre || ''}</td>
                <td>${hijo.DNI || '-'}</td>
                <td>${fechaNac}</td>
                <td>${edad}</td>
                <td>${convive}</td>
                <td>${estudia}</td>
                <td>${nivel}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-warning" onclick="editarHijo(${hijo.IdHijo})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmarEliminarHijo(${hijo.IdHijo})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

// ========================================
// MODAL DE HIJO
// ========================================

/**
 * Abrir modal para agregar un nuevo hijo
 */
function abrirModalHijo() {
    limpiarFormHijo();
    document.getElementById('modal_hijo_title').textContent = 'Agregar Hijo';
    $('#modal_hijo').modal('show');
}

/**
 * Editar un hijo existente
 */
function editarHijo(hijoId) {
    const hijo = hijosAsignados.find(h => h.IdHijo === hijoId);
    if (!hijo) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hijo no encontrado'
        });
        return;
    }

    // Llenar el formulario con los datos del hijo
    document.getElementById('hijo_id').value = hijo.IdHijo;
    document.getElementById('hijo_apellido').value = hijo.Apellido || '';
    document.getElementById('hijo_nombre').value = hijo.Nombre || '';
    document.getElementById('hijo_dni').value = hijo.DNI || '';
    document.getElementById('hijo_fecnac').value = hijo.FecNac || '';
    document.getElementById('hijo_nivel_educativo').value = hijo.NivelEducativo || '';
    document.getElementById('hijo_grado_anio').value = hijo.GradoAnio || '';
    document.getElementById('hijo_convive').checked = hijo.Convive || false;
    document.getElementById('hijo_estudia').checked = hijo.Estudia || false;
    document.getElementById('hijo_impedido').checked = hijo.ImpedidoTrabaja || false;
    document.getElementById('hijo_remuneracion').checked = hijo.RemuneracionEmpleador || false;
    document.getElementById('hijo_ingresos').checked = hijo.IngresosMensuales || false;

    // Datos del otro padre/madre
    document.getElementById('otro_padre_nombre').value = hijo.OtroPadre_ApellidoNombre || '';
    document.getElementById('otro_padre_dni').value = hijo.OtroPadre_DNI || '';
    document.getElementById('otro_padre_domicilio').value = hijo.OtroPadre_Domicilio || '';
    document.getElementById('otro_padre_trabaja').checked = hijo.OtroPadre_Trabaja || false;
    document.getElementById('otro_padre_empleador').value = hijo.OtroPadre_Empleador || '';
    document.getElementById('otro_padre_asig').checked = hijo.OtroPadre_AsigFamiliares || false;
    document.getElementById('otro_padre_convive').checked = hijo.OtroPadre_Convive || false;

    // Fecha de casamiento
    document.getElementById('fecha_casamiento').value = hijo.FechaCasamiento || '';

    // Otros empleos
    document.getElementById('otro_empleador').value = hijo.OtroEmpleador || '';
    document.getElementById('percibe_salario').checked = hijo.PercibeSalario || false;
    document.getElementById('monto_salario').value = hijo.MontoSalario || '';
    document.getElementById('obs_otros_empleos').value = hijo.ObservacionesOtrosEmpleos || '';

    // Observaciones
    document.getElementById('hijo_observaciones').value = hijo.Observaciones || '';

    document.getElementById('modal_hijo_title').textContent = 'Editar Hijo';
    $('#modal_hijo').modal('show');
}

/**
 * Limpiar el formulario de hijo
 */
function limpiarFormHijo() {
    document.getElementById('form_hijo').reset();
    document.getElementById('hijo_id').value = '';
    document.getElementById('hijo_convive').checked = true;
    document.getElementById('hijo_estudia').checked = true;
}

// ========================================
// GUARDAR HIJO
// ========================================

/**
 * Guardar un hijo (crear o actualizar)
 */
async function guardarHijo() {
    const hijoId = document.getElementById('hijo_id').value;

    // Recopilar datos del formulario
    const datos = {
        Apellido: document.getElementById('hijo_apellido').value,
        Nombre: document.getElementById('hijo_nombre').value,
        DNI: document.getElementById('hijo_dni').value,
        FecNac: document.getElementById('hijo_fecnac').value,
        NivelEducativo: document.getElementById('hijo_nivel_educativo').value,
        GradoAnio: document.getElementById('hijo_grado_anio').value,
        Convive: document.getElementById('hijo_convive').checked,
        Estudia: document.getElementById('hijo_estudia').checked,
        ImpedidoTrabaja: document.getElementById('hijo_impedido').checked,
        RemuneracionEmpleador: document.getElementById('hijo_remuneracion').checked,
        IngresosMensuales: document.getElementById('hijo_ingresos').checked,
        OtroPadre_ApellidoNombre: document.getElementById('otro_padre_nombre').value,
        OtroPadre_DNI: document.getElementById('otro_padre_dni').value,
        OtroPadre_Domicilio: document.getElementById('otro_padre_domicilio').value,
        OtroPadre_Trabaja: document.getElementById('otro_padre_trabaja').checked,
        OtroPadre_Empleador: document.getElementById('otro_padre_empleador').value,
        OtroPadre_AsigFamiliares: document.getElementById('otro_padre_asig').checked,
        OtroPadre_Convive: document.getElementById('otro_padre_convive').checked,
        FechaCasamiento: document.getElementById('fecha_casamiento').value,
        OtroEmpleador: document.getElementById('otro_empleador').value,
        PercibeSalario: document.getElementById('percibe_salario').checked,
        MontoSalario: document.getElementById('monto_salario').value,
        ObservacionesOtrosEmpleos: document.getElementById('obs_otros_empleos').value,
        Observaciones: document.getElementById('hijo_observaciones').value
    };

    // Validar campos requeridos
    if (!datos.Apellido && !datos.Nombre && !datos.DNI) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debe ingresar al menos Apellido, Nombre o DNI del hijo'
        });
        return;
    }

    try {
        const url = hijoId
            ? `/personal/${empleadoId}/hijos/${hijoId}`
            : `/personal/${empleadoId}/hijos`;

        const method = hijoId ? 'PUT' : 'POST';

        const response = await fetch(window.Laravel.baseUrl + url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            },
            body: JSON.stringify(datos)
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: hijoId ? 'Hijo actualizado correctamente' : 'Hijo agregado correctamente',
                timer: 2000,
                showConfirmButton: false
            });

            $('#modal_hijo').modal('hide');
            cargarHijos(); // Recargar la lista
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Error al guardar el hijo'
            });
        }
    } catch (error) {
        console.error('Error al guardar hijo:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al comunicarse con el servidor'
        });
    }
}

// ========================================
// ELIMINAR HIJO
// ========================================

/**
 * Confirmar eliminación de un hijo
 */
function confirmarEliminarHijo(hijoId) {
    const hijo = hijosAsignados.find(h => h.IdHijo === hijoId);
    if (!hijo) return;

    Swal.fire({
        title: '¿Eliminar Hijo?',
        text: `Está a punto de eliminar a ${hijo.Apellido || ''} ${hijo.Nombre || ''}. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            await eliminarHijo(hijoId);
        }
    });
}

/**
 * Eliminar un hijo
 */
async function eliminarHijo(hijoId) {
    try {
        const response = await fetch(`${window.Laravel.baseUrl}/personal/${empleadoId}/hijos/${hijoId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: 'Hijo eliminado correctamente',
                timer: 2000,
                showConfirmButton: false
            });

            cargarHijos(); // Recargar la lista
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Error al eliminar el hijo'
            });
        }
    } catch (error) {
        console.error('Error al eliminar hijo:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al comunicarse con el servidor'
        });
    }
}

// ========================================
// FORMULARIO DE SUBSIDIO
// ========================================

/**
 * Generar Formulario de Subsidio Familiar
 */
function generarFormularioSubsidio(id) {
    // Abrir en una nueva ventana/tab para imprimir como PDF
    const url = `/personal/${id}/hijos/formulario/subsidio-familiar/pdf`;
    window.open(url, '_blank');
}

// ========================================
// UTILIDADES
// ========================================

/**
 * Formatear fecha para mostrar
 */
function formatearFecha(fecha) {
    if (!fecha) return '-';
    const f = new Date(fecha);
    return f.toLocaleDateString('es-AR');
}
