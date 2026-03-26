/**
 * JavaScript para Modelo de Ejemplo
 * Contiene funciones para AJAX y manipulación del DOM
 */

document.addEventListener('DOMContentLoaded', function () {
    // Obtener permisos desde los inputs hidden
    const permisos = {
        crear: document.getElementById('permiso_crear').value === '1',
        leer: document.getElementById('permiso_leer').value === '1',
        editar: document.getElementById('permiso_editar').value === '1',
        eliminar: document.getElementById('permiso_eliminar').value === '1'
    };


    console.log('Permisos del usuario:', permisos);

    // Buscar botón para prueba de AJAX
    const btnBuscar = document.querySelector('.btn-primary i.bi-search').closest('button');

    if (btnBuscar) {
        btnBuscar.addEventListener('click', function () {
            // Obtener valores de filtros
            const nombreFiltro = document.getElementById('filterNombre').value;
            const categoriaFiltro = document.getElementById('filterCategoria').value;
            const estadoFiltro = document.getElementById('filterEstado').value;

            // Preparar datos para enviar
            const datos = {
                nombre: nombreFiltro,
                categoria: categoriaFiltro,
                estado: estadoFiltro
            };

            // Mostrar mensaje de carga
            console.log('Realizando búsqueda con filtros:', datos);

            // Realizar petición AJAX utilizando la función apiLaravel
            apiLaravel('/modelo-ejemplo/buscar', 'GET', datos)
                .then(respuesta => {
                    console.log('Respuesta recibida:', respuesta);

                    // Aquí se procesaría la respuesta para mostrar los resultados
                    // Por ejemplo, actualizar la tabla con los resultados

                    // Mostrar mensaje de éxito
                    alert('Búsqueda completada. Revise la consola para ver los resultados.');
                })
                .catch(error => {
                    console.error('Error en la búsqueda:', error);
                    alert('Error al realizar la búsqueda.');
                });
        });
    }

    // Función de prueba para ver los permisos
    function mostrarPermisos() {
        console.log('Permisos actuales:');
        console.log('- Crear:', permisos.crear ? 'Sí' : 'No');
        console.log('- Leer:', permisos.leer ? 'Sí' : 'No');
        console.log('- Editar:', permisos.editar ? 'Sí' : 'No');
        console.log('- Eliminar:', permisos.eliminar ? 'Sí' : 'No');
    }

    // Ejecutar función de prueba
    mostrarPermisos();
});
