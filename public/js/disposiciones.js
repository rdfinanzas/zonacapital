const DisposicionesModule = (function() {
    let _disposicionesData = [];
    let _idEditar = 0;
    let _idEliminar = 0;
    let _paginaActual = 1;
    let _elementosPorPagina = 10;
    let _totalElementos = 0;
    let _totalPaginas = 0;
    let _datosDisposiciones = null;
    let _routes = {};
    let _isModal = false;
    let _targetDropdown = null;

    function setRoutes(routes) {
        _routes = routes;
    }

    function setConfig(config) {
        if (config.isModal !== undefined) _isModal = config.isModal;
        if (config.targetDropdown !== undefined) _targetDropdown = config.targetDropdown;
    }

    function limpiarFormulario() {
        _idEditar = 0;
        _idEliminar = 0;

        const form = document.getElementById('dispo_form') || document.getElementById('form_main');
        if (form) form.reset();

        const anioInput = document.getElementById('dispo_anio') || document.getElementById('anio');
        if (anioInput) anioInput.value = new Date().getFullYear();

        actualizarContadorCaracteres();

        const btnSubmit = document.getElementById('btn_submit');
        const btnLimpiar = document.getElementById('btn_limpiar');
        const btnEliminar = document.getElementById('btn_eliminar');

        if (btnSubmit) btnSubmit.disabled = false;
        if (btnLimpiar) btnLimpiar.disabled = false;
        if (btnEliminar) btnEliminar.disabled = false;

        cargarProximoNumero();
    }

    async function cargarDisposiciones() {
        try {
            const buscarInput = document.getElementById('buscar_disposiciones');
            const filtroAnioInput = document.getElementById('filtro_anio');
            const busqueda = buscarInput ? buscarInput.value.trim() : '';
            const filtroAnio = filtroAnioInput ? filtroAnioInput.value : '';

            const params = {
                page: _paginaActual,
                per_page: _elementosPorPagina
            };

            if (busqueda) params.busqueda = busqueda;
            if (filtroAnio) params.filtro_anio = filtroAnio;

            const response = await apiLaravel(_routes.listar, 'GET', params);

            _datosDisposiciones = response;
            _disposicionesData = response.data;
console.log(_disposicionesData);
            if (response.pagination) {
                _totalElementos = response.pagination.total;
                _totalPaginas = response.pagination.last_page;
                _paginaActual = response.pagination.current_page;
            } else {
                _totalElementos = response.data.length;
                _totalPaginas = 1;
            }

            renderizarTablaDisposiciones();
            renderizarPaginacion();
            actualizarInformacionPaginacion();
            await cargarEstadisticas();

        } catch (error) {
            console.error('Error al cargar disposiciones:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar las disposiciones'
            });
        }
    }

    function renderizarTablaDisposiciones() {
        const tbody = document.getElementById('tabla_disposiciones_body');

        if (!tbody) return;

        if (!_disposicionesData || _disposicionesData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No hay disposiciones registradas
                    </td>
                </tr>`;
            return;
        }

        let html = '';

        _disposicionesData.forEach((disposicion, index) => {
            const numeroCompleto = disposicion.NumDisp + (disposicion.AnioDisp ? '/' + disposicion.AnioDisp : '');
            const creadorTexto = disposicion.creador ? disposicion.creador.nombre : 'N/A';
            const creadorTitulo = disposicion.creador ? `Creado por: ${disposicion.creador.nombre}` : 'Sin información de creador';

            html += `
                <tr>
                    <td class="font-weight-bold">${numeroCompleto}</td>
                    <td>
                        <small>${disposicion.Descripcion || ''}</small>
                    </td>
                    <td>
                        <div class="btn-group" role="group">`;

            if (window.disposicionesPermisos && window.disposicionesPermisos.editar) {
                html += `
                            <button type="button" onclick="DisposicionesModule.editarDisposicion(${disposicion.IdNumDisp}, ${index})"
                                    class="btn btn-primary btn-sm" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>`;
            }

            if (window.disposicionesPermisos && window.disposicionesPermisos.eliminar) {
                const puedeEliminar = window.usuarioEsCreador === true && disposicion.creador ?
                    (window.usuarioId == disposicion.creador.id) : true;

                if (puedeEliminar) {
                    html += `
                            <button type="button" onclick="DisposicionesModule.modalEliminarDisposicion(${disposicion.IdNumDisp})"
                                    class="btn btn-danger btn-sm" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>`;
                }
            }

            html += `
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" title="${creadorTitulo}">
                            <i class="fas fa-user"></i>
                        </button>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;

        if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    }

    function editarDisposicion(id, index) {
        limpiarFormulario();
        _idEditar = id;

        const disposicion = _disposicionesData[index];

        const numInput = document.getElementById('dispo_num') || document.getElementById('num');
        const obsInput = document.getElementById('dispo_obs') || document.getElementById('obs');
        const anioInput = document.getElementById('dispo_anio') || document.getElementById('anio');

        if (numInput) numInput.value = disposicion.NumDisp || '';
        if (obsInput) obsInput.value = disposicion.Descripcion || '';
        if (anioInput) anioInput.value = disposicion.AnioDisp || new Date().getFullYear();

        actualizarContadorCaracteres();
    }

    async function guardarDisposicion() {
        const anioInput = document.getElementById('dispo_anio') || document.getElementById('anio');
        const numInput = document.getElementById('dispo_num') || document.getElementById('num');
        const obsInput = document.getElementById('dispo_obs') || document.getElementById('obs');

        const formData = {
            anio: anioInput ? anioInput.value : null,
            num: numInput ? numInput.value || null : null,
            obs: obsInput ? obsInput.value.trim() : ''
        };

        if (!formData.anio || formData.anio < 2000 || formData.anio > 2100) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'El año debe estar entre 2000 y 2100'
            });
            return false;
        }

        if (!formData.obs) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'La descripción es obligatoria'
            });
            return false;
        }

        try {
            let response;

            if (_idEditar === 0) {
                response = await apiLaravel(_routes.store, 'POST', formData);

                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Disposición creada correctamente',
                    timer: 2000
                });

                if (!numInput.value && response.numero_generado) {
                    const modalGenerado = document.getElementById('numero_disposicion_generada');
                    if (modalGenerado) {
                        modalGenerado.textContent = response.numero_generado + '/' + formData.anio;
                        $('#modal_disposicion_generada').modal('show');
                    }
                }

                await cargarDropdown(_targetDropdown);

            } else {
                const url = _routes.update.replace(':id', _idEditar);
                response = await apiLaravel(url, 'PUT', formData);

                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Disposición actualizada correctamente',
                    timer: 2000
                });
            }

            limpiarFormulario();
            await cargarDisposiciones();

            const modal = document.getElementById('modal_nueva_disposicion');
            if (modal) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                $('#modal_nueva_disposicion').modal('hide');
            }

            return true;

        } catch (error) {
            console.error('Error al guardar disposición:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Error al guardar la disposición'
            });
            return false;
        }
    }

    function modalEliminarDisposicion(id) {
        _idEliminar = id;
        $('#modal_eliminar').modal('show');
    }

    async function eliminarDisposicion() {
        if (!_idEliminar) return;

        try {
            const url = _routes.destroy.replace(':id', _idEliminar);
            const response = await apiLaravel(url, 'DELETE');

            $('#modal_eliminar').modal('hide');

            if (response && !response.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudo eliminar la disposición'
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Disposición eliminada correctamente',
                timer: 2000
            });

            limpiarFormulario();
            await cargarDisposiciones();

        } catch (error) {
            console.error('Error al eliminar disposición:', error);
            $('#modal_eliminar').modal('hide');

            let mensajeError = 'Error al eliminar la disposición';
            if (error.message) {
                mensajeError = error.message;
            } else if (typeof error === 'string') {
                mensajeError = error;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensajeError
            });
        }
    }

    async function buscarDisposiciones() {
        _paginaActual = 1;
        await cargarDisposiciones();
    }

    async function cargarEstadisticas() {
        try {
            const response = await apiLaravel(_routes.estadisticas, 'GET');
            const stats = response.data;

            const totalElement = document.getElementById('total_disposiciones');
            const esteAnioElement = document.getElementById('disposiciones_este_anio');

            if (totalElement) totalElement.textContent = stats.total || 0;
            if (esteAnioElement) esteAnioElement.textContent = stats.este_anio || 0;

        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }

    function actualizarContadorCaracteres() {
        const textarea = document.getElementById('dispo_obs') || document.getElementById('obs');
        const contador = document.getElementById('dispo_contador') || document.getElementById('contador_caracteres');

        if (textarea && contador) {
            contador.textContent = textarea.value.length;

            if (textarea.value.length > 900) {
                contador.className = 'text-danger font-weight-bold';
            } else if (textarea.value.length > 700) {
                contador.className = 'text-warning font-weight-bold';
            } else {
                contador.className = 'font-weight-bold';
            }
        }
    }

    async function cargarProximoNumero() {
        const anioInput = document.getElementById('dispo_anio') || document.getElementById('anio');
        const numInput = document.getElementById('dispo_num') || document.getElementById('num');

        if (!anioInput || !numInput) return;

        const anio = anioInput.value || new Date().getFullYear();

        if (_idEditar === 0) {
            numInput.placeholder = 'Cargando próximo número...';
            numInput.disabled = true;
        }

        try {
            const response = await apiLaravel(_routes.proximoNumero, 'GET', { anio: anio });
            const proximoNumero = response.proximo_numero;
            const mensaje = response.mensaje;

            if (_idEditar === 0) {
                numInput.value = proximoNumero;
                numInput.placeholder = `Sugerido: ${proximoNumero}/${anio}`;
                numInput.disabled = false;

                const helpText = document.querySelector('#num')?.parentNode?.parentNode?.querySelector('.form-text');
                if (helpText) {
                    helpText.innerHTML = `
                        <i class="fas fa-info-circle"></i>
                        ${mensaje}. Puedes modificarlo o dejarlo vacío para generar automáticamente.
                    `;
                }
            }

        } catch (error) {
            console.error('Error al obtener próximo número:', error);

            if (_idEditar === 0) {
                numInput.value = '';
                numInput.placeholder = 'Ingrese número manualmente';
                numInput.disabled = false;

                const helpText = document.querySelector('#num')?.parentNode?.parentNode?.querySelector('.form-text');
                if (helpText) {
                    helpText.innerHTML = `
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        No se pudo obtener el próximo número automáticamente. Ingrese el número manualmente.
                    `;
                }
            }
        }
    }

    function renderizarPaginacion() {
        const contenedorPaginacion = document.getElementById('page-selection');

        if (!contenedorPaginacion || _totalPaginas <= 1) {
            if (contenedorPaginacion) {
                contenedorPaginacion.innerHTML = '';
            }
            return;
        }

        let html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';

        const anteriorDeshabilitado = _paginaActual <= 1 ? 'disabled' : '';
        html += `<li class="page-item ${anteriorDeshabilitado}">
                    <a class="page-link" href="#" onclick="DisposicionesModule.cambiarPagina(${_paginaActual - 1})" data-page="${_paginaActual - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                 </li>`;

        const inicio = Math.max(1, _paginaActual - 2);
        const fin = Math.min(_totalPaginas, _paginaActual + 2);

        if (inicio > 1) {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="DisposicionesModule.cambiarPagina(1)" data-page="1">1</a>
                     </li>`;
            if (inicio > 2) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for (let i = inicio; i <= fin; i++) {
            const activa = i === _paginaActual ? 'active' : '';
            html += `<li class="page-item ${activa}">
                        <a class="page-link" href="#" onclick="DisposicionesModule.cambiarPagina(${i})" data-page="${i}">${i}</a>
                     </li>`;
        }

        if (fin < _totalPaginas) {
            if (fin < _totalPaginas - 1) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="DisposicionesModule.cambiarPagina(${_totalPaginas})" data-page="${_totalPaginas}">${_totalPaginas}</a>
                     </li>`;
        }

        const siguienteDeshabilitado = _paginaActual >= _totalPaginas ? 'disabled' : '';
        html += `<li class="page-item ${siguienteDeshabilitado}">
                    <a class="page-link" href="#" onclick="DisposicionesModule.cambiarPagina(${_paginaActual + 1})" data-page="${_paginaActual + 1}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                 </li>`;

        html += '</ul></nav>';
        contenedorPaginacion.innerHTML = html;
    }

    function cambiarPagina(nuevaPagina) {
        if (nuevaPagina < 1 || nuevaPagina > _totalPaginas || nuevaPagina === _paginaActual) {
            return;
        }

        _paginaActual = nuevaPagina;
        cargarDisposiciones();
    }

    async function cargarDropdown(targetDropdownId) {
        try {
            const response = await apiLaravel(_routes.listar, 'GET', { per_page: 1000 });
            const disposiciones = response.data || [];

            console.log('Disposiciones para dropdown:', disposiciones);

            const select = document.getElementById(targetDropdownId);
            if (!select) return;

            const currentValue = select.value;
            select.innerHTML = '<option value="" selected>- SELECCIONAR -</option>';

            disposiciones.forEach(disposicion => {
                const numeroCompleto = disposicion.NumDisp + (disposicion.AnioDisp ? '/' + disposicion.AnioDisp : '');
                const option = new Option(numeroCompleto + ' - ' + (disposicion.Descripcion || ''), disposicion.IdNumDisp);
                select.appendChild(option);
            });

            select.value = currentValue;

        } catch (error) {
            console.error('Error al cargar dropdown:', error);
        }
    }

    function actualizarInformacionPaginacion() {
        const infoPaginacion = document.getElementById('total_info');

        if (!infoPaginacion) return;

        if (_totalElementos === 0) {
            infoPaginacion.innerHTML = '<small class="text-muted">No hay elementos para mostrar</small>';
            return;
        }

        const inicio = ((_paginaActual - 1) * _elementosPorPagina) + 1;
        const fin = Math.min(_paginaActual * _elementosPorPagina, _totalElementos);

        infoPaginacion.innerHTML = `
            <small class="text-muted">
                Mostrando <strong>${inicio}</strong> a <strong>${fin}</strong> de <strong>${_totalElementos}</strong> disposiciones
            </small>
        `;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function abrirModalNuevaDisposicion() {
        limpiarFormulario();

        const modal = document.getElementById('modal_nueva_disposicion');
        if (modal) {
            $('#modal_nueva_disposicion').modal('show');
        }
    }

    function init(routes, config = {}) {
        setRoutes(routes);
        setConfig(config);

        const form = document.getElementById('dispo_form') || document.getElementById('form_main');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                guardarDisposicion();
            });
        }

        const btnLimpiar = document.getElementById('btn_limpiar');
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', limpiarFormulario);
        }

        const btnEliminar = document.getElementById('btn_eliminar');
        if (btnEliminar) {
            btnEliminar.addEventListener('click', function() {
                if (_idEditar > 0) {
                    modalEliminarDisposicion(_idEditar);
                }
            });
        }

        const btnEliminarModal = document.getElementById('btn_eliminar_modal');
        if (btnEliminarModal) {
            btnEliminarModal.addEventListener('click', eliminarDisposicion);
        }

        const inputBuscar = document.getElementById('buscar_disposiciones');
        const filtroAnioInput = document.getElementById('filtro_anio');
        const btnBuscar = document.getElementById('btn_buscar');

        if (filtroAnioInput) {
            filtroAnioInput.addEventListener('change', function() {
                _paginaActual = 1;
                cargarDisposiciones();
            });
        }

        if (inputBuscar) {
            const buscarConDebounce = debounce(buscarDisposiciones, 500);
            inputBuscar.addEventListener('input', buscarConDebounce);

            inputBuscar.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    buscarDisposiciones();
                }
            });
        }

        if (btnBuscar) {
            btnBuscar.addEventListener('click', buscarDisposiciones);
        }

        const textareaObs = document.getElementById('dispo_obs') || document.getElementById('obs');
        if (textareaObs) {
            textareaObs.addEventListener('input', actualizarContadorCaracteres);
        }

        const inputAnio = document.getElementById('dispo_anio') || document.getElementById('anio');
        if (inputAnio) {
            inputAnio.addEventListener('change', function() {
                if (_idEditar === 0) {
                    cargarProximoNumero();
                }
            });
        }

        const numericos = document.querySelectorAll('input[type=number]');
        numericos.forEach(input => {
            input.addEventListener('wheel', function(e) {
                e.preventDefault();
            });
        });

        const selectElementosPorPagina = document.getElementById('page-selection_input_num_page');
        if (selectElementosPorPagina) {
            selectElementosPorPagina.addEventListener('change', function(e) {
                _elementosPorPagina = parseInt(e.target.value);
                _paginaActual = 1;
                cargarDisposiciones();
            });
        }

        cargarDisposiciones();
        cargarProximoNumero();

        console.log('✅ Módulo de Disposiciones inicializado correctamente');
    }

    return {
        init: init,
        limpiarFormulario: limpiarFormulario,
        cargarDisposiciones: cargarDisposiciones,
        guardarDisposicion: guardarDisposicion,
        editarDisposicion: editarDisposicion,
        modalEliminarDisposicion: modalEliminarDisposicion,
        eliminarDisposicion: eliminarDisposicion,
        buscarDisposiciones: buscarDisposiciones,
        cambiarPagina: cambiarPagina,
        abrirModalNuevaDisposicion: abrirModalNuevaDisposicion,
        cargarProximoNumero: cargarProximoNumero,
        actualizarContadorCaracteres: actualizarContadorCaracteres,
        cargarDropdown: cargarDropdown
    };
})();
