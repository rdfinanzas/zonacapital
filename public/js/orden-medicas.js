document.addEventListener('DOMContentLoaded', function () {
    const btnFiltrar = document.getElementById('btn-filtrar');
    const tbody = document.getElementById('tbody-om') || document.getElementById('table_data_om');
    const inputDni = document.getElementById('dni_filtro');
    const inputLegajo = document.getElementById('legajo_filtro');
    const inputPersonal = document.getElementById('personal_filtro');
    const inputFechaDesde = document.getElementById('fecha_desde');
    const inputFechaHasta = document.getElementById('fecha_hasta');
    const inputAnioLar = document.getElementById('anio_lar_filtro');
    const inputNumeroOm = document.getElementById('numero_om_filtro');
    const inputMultiplesOm = document.getElementById('multiples_om_filtro');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
    const omAnioEl = document.getElementById('om_anio');
    const omSugEl = document.getElementById('om_sugerido');

    // Form elements (alta/edición)
    const form = document.getElementById('form-om') || document.getElementById('form_main');
    const legajoEl = document.getElementById('personal_id');
    const motivoEl = document.getElementById('motivo_id');
    const omEl = document.getElementById('numero_om');
    const omAnioFormEl = document.getElementById('anio');
    const fechaOrdenEl = document.getElementById('fecha');
    const desdeEl = document.getElementById('d');
    const hastaEl = document.getElementById('h');
    const diasEl = document.getElementById('dias');
    const estadoEl = document.getElementById('estado');
    const obsEl = document.getElementById('observacion');
    const corridoEl = document.getElementById('corridos');
    const cmEl = document.getElementById('certificado');
    const disposicionEl = document.getElementById('disposicion_id');
    const btnGuardar = document.getElementById('btn-guardar') || document.querySelector('#form_main button[type="submit"]') || document.querySelector('button[type="submit"]');
    const btnCancelar = document.getElementById('btn-cancelar') || document.getElementById('btn_volver');
    const posterAlert = document.getElementById('poster_alert');
    const btnAdd = document.getElementById('btn_add');
    const panelList = document.getElementById('panel_list');
    const panelAdd = document.getElementById('panel_add');
    const btnClear = document.getElementById('btn_clear') || document.getElementById('btn_limpiar');
    const btnCalc = document.getElementById('btn_calc');

    // Elementos de imagen
    let imagenFoto; // Variable para la instancia de imageLoad

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken;
    const routes = window.laravelRoutes || {};
    const routeList = routes.ordenMedicasFiltrar || routes.ordenMedicasList;
    const routeUltimoNumero = routes.ordenMedicasUltimoNumero || routes['ultimo-numero'] || routes.ordenMedicasLastNumber;
    const routeProximoCertificado = routes.ordenMedicasProximoCertificado || '/orden-medicas/proximo-certificado';
    const routeBase = routes.ordenMedicasBase || routes.ordenMedicasGuardar || routes['orden-medicas'] || '';

    let currentPage = 1;
    let lastPage = 1;
    let perPage = 15;
    let modoEdicion = false;
    let ordenEditandoId = null;

    // Función para mapear estado numérico a texto
    function getEstadoTexto(estadoNumerico) {
        const estados = {
            1: 'Finalizado',
            2: 'Pendiente envio',
            3: 'Enviado',
            4: 'Anulado'
        };
        return estados[estadoNumerico] || 'Pendiente';
    }

    // Evento delegado para todos los botones editar
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.btn-editar')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-editar');
            const id = btn.getAttribute('data-id');
            console.log('Click en editar, ID:', id);

            if (!id) {
                console.error('No se encontró ID en el botón');
                return;
            }

            try {
                const url = `${window.laravelRoutes.ordenMedicasBase}/${id}`;
                console.log('URL de consulta:', url);

                const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
                console.log('Respuesta del servidor:', resp.status, resp.statusText);

                if (!resp.ok) {
                    throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
                }

                const data = await resp.json();
                console.log('Datos recibidos:', data);

                llenarFormulario(data);
            } catch (err) {
                console.error('Error cargando OM:', err);
                alert('Error al cargar los datos de la orden médica: ' + err.message);
            }
        }
    });

    async function cargarOM(page = 1) {
        // Solo aplicar filtro si estamos en la vista de tabla (panel_list)
        const panelList = document.getElementById('panel_list');
        const panelAdd = document.getElementById('panel_add');

        // Obtener todos los valores de filtros
        const anioLar = inputAnioLar ? inputAnioLar.value : '';

        const params = new URLSearchParams();

        // Filtros básicos
        if (anioLar) params.append('anio_lar', anioLar);
        if (inputDni && inputDni.value.trim()) params.append('dni', inputDni.value.trim());
        if (inputLegajo && inputLegajo.value.trim()) params.append('legajo', inputLegajo.value.trim());
        if (inputPersonal && inputPersonal.value.trim()) params.append('personal', inputPersonal.value.trim());

        // Filtros de fecha de creación
        if (inputFechaDesde && inputFechaDesde.value) params.append('fecha_desde', inputFechaDesde.value);
        if (inputFechaHasta && inputFechaHasta.value) params.append('fecha_hasta', inputFechaHasta.value);

        // Filtros por número OM
        if (inputNumeroOm && inputNumeroOm.value.trim()) params.append('numero_om', inputNumeroOm.value.trim());
        if (inputMultiplesOm && inputMultiplesOm.value.trim()) params.append('multiples_om', inputMultiplesOm.value.trim());

        params.append('page', page);
        params.append('per_page', perPage);

        console.log('Parámetros de filtro enviados:', {
            anio_lar: anioLar,
            dni: inputDni ? inputDni.value.trim() : '',
            legajo: inputLegajo ? inputLegajo.value.trim() : '',
            personal: inputPersonal ? inputPersonal.value.trim() : '',
            numero_om: inputNumeroOm ? inputNumeroOm.value.trim() : '',
            multiples_om: inputMultiplesOm ? inputMultiplesOm.value.trim() : '',
            page: page,
            per_page: perPage
        });

        try {
            // Construir URL correctamente, evitando ? vacío
            let url = routeList;
            const queryString = params.toString();
            if (queryString) {
                url += `?${queryString}`;
            }
            console.log('URL completa:', url);

            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });

            if (!resp.ok) {
                console.error('Error HTTP:', resp.status, resp.statusText);
                const errorText = await resp.text();
                console.error('Error response body:', errorText);
                renderTabla([]);
                return;
            }

            const data = await resp.json();
            console.log('Datos paginados del servidor:', data);

            // Manejar respuesta paginada de Laravel
            const items = Array.isArray(data?.data) ? data.data : [];
            currentPage = data?.current_page || 1;
            lastPage = data?.last_page || 1;

            renderTabla(items);
            renderPaginacion(data);
        } catch (e) {
            console.error('Error cargando OM:', e);
            renderTabla([]);
        }
    }

    async function cargarProximoCertificado() {
        try {
            const resp = await fetch(routeProximoCertificado, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();

            // Actualizar campo oculto y display
            const certificadoInput = document.getElementById('certificado');
            const certificadoDisplay = document.getElementById('certificado_display');

            if (certificadoInput) {
                certificadoInput.value = data?.numero || '';
            }
            if (certificadoDisplay) {
                certificadoDisplay.value = data?.numero ? `Cert. Médico ${data.numero}` : 'Auto-generado';
            }
        } catch (e) {
            console.error('Error obteniendo próximo certificado médico:', e);
            // En caso de error, mostrar texto por defecto
            const certificadoDisplay = document.getElementById('certificado_display');
            if (certificadoDisplay) {
                certificadoDisplay.value = 'Auto-generado';
            }
        }
    }

    async function cargarUltimoNumero() {
        // Si el formulario está visible, usar el campo anio del formulario
        const panelAdd = document.getElementById('panel_add');
        const anioForm = document.getElementById('anio')?.value || '';
        const anioFiltro = document.getElementById('anio_filtro');

        // Priorizar año del formulario si está visible, sino usar filtro
        const anio = (panelAdd && !panelAdd.classList.contains('d-none') && anioForm)
                    ? anioForm
                    : (anioFiltro ? anioFiltro.value : '');

        if (!anio) return;
        if (omAnioEl) omAnioEl.textContent = anio;
        if (document.getElementById('om_anio')) {
            document.getElementById('om_anio').value = anio;
        }
        try {
            const url = `${routeUltimoNumero}?anio=${encodeURIComponent(anio)}`;
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await resp.json();
            if (omSugEl) omSugEl.textContent = data?.sugerido ?? '';
            // Rellenar el formulario con el número sugerido
            if (omEl) {
                omEl.value = data?.sugerido ?? '';
            }
        } catch (e) {
            console.error('Error obteniendo último número OM:', e);
        }

        // Cargar también el próximo número de certificado médico si no estamos en modo edición
        if (!modoEdicion) {
            await cargarProximoCertificado();
        }
    }

    // Función para convertir fecha de YYYY-MM-DD a DD/MM/YYYY
    function formatearFecha(fecha) {
        if (!fecha || fecha === '0000-00-00' || fecha === '') return '';
        const partes = fecha.split('-');
        if (partes.length === 3) {
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }
        return fecha;
    }

    function renderTabla(items) {
        tbody.innerHTML = '';
        if (!Array.isArray(items)) return;
        for (const it of items) {
            const tr = document.createElement('tr');
            // Formatear número de orden médica como "Número/Año"
            const numeroOM = it.OrdenMedica && it.AnioLar ? `${it.OrdenMedica}/${it.AnioLar}` : (it.OrdenMedica ?? '');

            tr.innerHTML = `
                <td class="text-center">${numeroOM}</td>
                <td class="text-center">${it.FechaCreacion ?? ''}</td>
                <td class="text-center">${it.personal?.Apellido ?? ''}, ${it.personal?.Nombre ?? ''}</td>
                <td class="text-center">${it.personal?.DNI ?? ''}</td>
                <td class="text-center">${it.personal?.Legajo ?? it.LegajoPersonal ?? ''}</td>
                <td class="text-center">${formatearFecha(it.FechaLic)}</td>
                <td class="text-center">${formatearFecha(it.FechaLicFin)}</td>
                <td class="text-center">${it.DiasTotal ?? ''}</td>
                <td class="text-center">${it.motivo?.Motivo ?? ''}</td>
                <td class="text-center">${getEstadoTexto(it.estado_om)}</td>
                <td class="text-center" style="font-size: 0.75em;">${it.creador ? (it.creador.Apellido + ', ' + it.creador.Nombre + ' (' + it.creador.Usuario + ')') : (it.Creador_Id || '')}</td>
                <td class="text-left">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-primary btn-sm btn-editar" data-action="edit" data-id="${it.IdLicencia}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a class="btn btn-success btn-sm" target="_blank" href="${window.laravelRoutes.ordenMedicasBase}/${it.IdLicencia}/imprimir" title="Imprimir">
                            <i class="fas fa-print"></i>
                        </a>


                        <button class="btn btn-danger btn-sm" data-action="delete" data-id="${it.IdLicencia}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                        ${it.imagen_ficha || it.imagen_url || it.imagen_base64 ?
                            `<button class="btn btn-info btn-sm" data-action="view-image" data-id="${it.IdLicencia}" title="Ver Imagen">
                                <i class="fas fa-image"></i>
                            </button>` : ''
                        }
                        ${it.ObservacionLic && it.ObservacionLic.trim() ?
                            `<button class="btn btn-warning btn-sm" data-action="view-obs" data-id="${it.IdLicencia}" data-om="${numeroOM}" data-obs="${it.ObservacionLic.replace(/"/g, '&quot;')}" title="Ver observación">
                                <i class="fas fa-info-circle"></i>
                            </button>` : ''
                        }
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        }

        tbody.querySelectorAll('button[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = btn.getAttribute('data-id');
                if (!id) return;
                if (!confirm('¿Eliminar esta orden médica?')) return;
                try {
                    const url = `${window.laravelRoutes.ordenMedicasBase}/${id}`;
                    const resp = await fetch(url, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                    });
                    const json = await resp.json();
                    if (json?.success) {
                        cargarOM(currentPage);
                        cargarUltimoNumero();
                    } else {
                        alert(json?.message || 'No se pudo eliminar');
                    }
                } catch (err) {
                    console.error('Error eliminando OM:', err);
                }
            });
        });



        tbody.querySelectorAll('button[data-action="view-image"]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!id) return;
                try {
                    const url = `${window.laravelRoutes.ordenMedicasBase}/${id}`;
                    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await resp.json();

                    if (data && (data.imagen_ficha || data.imagen_url || data.imagen_base64)) {
                        // Usar la librería imageLoad para mostrar la imagen
                        if (typeof imageLoad !== 'undefined') {
                            imageLoad.show(data.imagen_ficha || data.imagen_url || data.imagen_base64);
                        } else {
                            // Fallback: mostrar en una nueva ventana
                            const nuevaVentana = window.open('', '_blank');
                            nuevaVentana.document.write(`
                                <html>
                                    <head><title>Imagen - Orden Médica ${data.OrdenMedica}/${data.AnioLar}</title></head>
                                    <body style="margin:0; text-align:center;">
                                        <img src="${data.imagen_ficha || data.imagen_url || data.imagen_base64}"
                                             style="max-width:100%; max-height:100vh; object-fit:contain;">
                                    </body>
                                </html>
                            `);
                        }
                    } else {
                        alert('Esta orden médica no tiene imagen asociada');
                    }
                } catch (err) {
                    console.error('Error cargando imagen:', err);
                    alert('Error al cargar la imagen');
                }
            });
        });

        tbody.querySelectorAll('button[data-action="view-obs"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const om = btn.getAttribute('data-om');
                const observacion = btn.getAttribute('data-obs');

                // Actualizar contenido del modal
                document.getElementById('modalObservacionOM').textContent = om || 'N/A';
                document.getElementById('modalObservacionTexto').textContent = observacion || 'Sin observación';

                // Mostrar modal
                if (window.bootstrap) {
                    const modal = new bootstrap.Modal(document.getElementById('modalObservacion'));
                    modal.show();
                } else if (window.jQuery) {
                    jQuery('#modalObservacion').modal('show');
                } else {
                    // Fallback para mostrar modal sin jQuery
                    const modal = document.getElementById('modalObservacion');
                    modal.style.display = 'block';
                    modal.classList.add('show');
                }
            });
        });
    }

    function renderPaginacion(data) {
        const infoPaginacion = document.getElementById('info-paginacion');
        const controlesPaginacion = document.getElementById('paginacion-controles');

        if (!data || !infoPaginacion || !controlesPaginacion) return;

        const { current_page, last_page, from, to, total } = data;

        // Mostrar información de la paginación
        if (total > 0) {
            infoPaginacion.textContent = `Mostrando ${from} a ${to} de ${total} resultados`;
        } else {
            infoPaginacion.textContent = 'No se encontraron resultados';
        }

        // Generar controles de paginación
        controlesPaginacion.innerHTML = '';

        if (last_page <= 1) return;

        // Botón anterior
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${current_page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${current_page - 1}">Anterior</a>`;
        controlesPaginacion.appendChild(prevLi);

        // Páginas numeradas
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        if (startPage > 1) {
            const firstLi = document.createElement('li');
            firstLi.className = 'page-item';
            firstLi.innerHTML = `<a class="page-link" href="#" data-page="1">1</a>`;
            controlesPaginacion.appendChild(firstLi);

            if (startPage > 2) {
                const dotsLi = document.createElement('li');
                dotsLi.className = 'page-item disabled';
                dotsLi.innerHTML = `<span class="page-link">...</span>`;
                controlesPaginacion.appendChild(dotsLi);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === current_page ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            controlesPaginacion.appendChild(pageLi);
        }

        if (endPage < last_page) {
            if (endPage < last_page - 1) {
                const dotsLi = document.createElement('li');
                dotsLi.className = 'page-item disabled';
                dotsLi.innerHTML = `<span class="page-link">...</span>`;
                controlesPaginacion.appendChild(dotsLi);
            }

            const lastLi = document.createElement('li');
            lastLi.className = 'page-item';
            lastLi.innerHTML = `<a class="page-link" href="#" data-page="${last_page}">${last_page}</a>`;
            controlesPaginacion.appendChild(lastLi);
        }

        // Botón siguiente
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${current_page === last_page ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${current_page + 1}">Siguiente</a>`;
        controlesPaginacion.appendChild(nextLi);

        // Event listeners para los enlaces de paginación
        controlesPaginacion.querySelectorAll('a.page-link[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= last_page && page !== current_page) {
                    cargarOM(page);
                }
            });
        });
    }

    function llenarFormulario(response) {
        console.log('llenarFormulario llamada con datos:', response);

        if (!response) {
            console.error('No hay respuesta para llenar el formulario');
            return;
        }

        // Extraer datos del wrapper de respuesta
        const data = response.data || response;
        console.log('Datos extraídos:', data);

        if (!data) {
            console.error('No hay datos válidos en la respuesta');
            return;
        }

        // USAR LA MISMA LÓGICA QUE EL BOTÓN AGREGAR
        limpiarFormulario();

        // Ocultar header de filtros y mostrar panel de formulario (igual que btn_add)
        const headerFiltros = document.getElementById('card_header_filtros');
        if (headerFiltros) headerFiltros.style.display = 'none';
        if (panelList) {
            panelList.classList.add('d-none');
        }
        if (panelAdd) {
            panelAdd.classList.remove('d-none');
        }

        // CRÍTICO: Re-inicializar Select2 después de mostrar el panel
        // Select2 no funciona correctamente cuando está oculto
        if (window.jQuery && typeof jQuery.fn.select2 !== 'undefined') {
            jQuery('.select2').each(function() {
                if (jQuery(this).data('select2')) {
                    jQuery(this).select2('destroy');
                }
            });
            jQuery('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: jQuery('#panel_add')
            });
            console.log('Select2 re-inicializado después de mostrar panel');
        }

        // Activar modo edición DESPUÉS de mostrar el panel
        console.log('Activando modo edición...');
        modoEdicion = true;
        ordenEditandoId = data.IdLicencia || data.id;

        if (btnGuardar) {
            btnGuardar.dataset.id = ordenEditandoId;
            console.log('ID asignado al botón guardar:', ordenEditandoId);
        } else {
            console.error('No se encontró el botón guardar');
        }
        console.log('=== CARGANDO DATOS EN FORMULARIO ===');

        // Cargar campos
        // El personal puede venir como LegajoPersonal, legajo_personal, o en el objeto personal
        const personalId = data.LegajoPersonal || data.legajo_personal || data.personal?.Legajo || data.personal?.idEmpleado;
        console.log('DEBUG PERSONAL - personalId:', personalId);
        console.log('DEBUG PERSONAL - data.LegajoPersonal:', data.LegajoPersonal);
        console.log('DEBUG PERSONAL - data.personal?.Legajo:', data.personal?.Legajo);
        console.log('DEBUG PERSONAL - legajoEl:', legajoEl);
        
        if (legajoEl && personalId) {
            // Usar la función global para establecer valor en Select2
            if (window.setSelect2Value) {
                window.setSelect2Value('#personal_id', personalId);
            } else {
                legajoEl.value = personalId;
                if (window.jQuery) {
                    setTimeout(function() {
                        jQuery(legajoEl).val(personalId).trigger('change');
                    }, 200);
                }
            }
            console.log('✓ Personal cargado:', personalId);
        } else {
            console.warn('✗ No se pudo cargar el personal. LegajoPersonal:', data.LegajoPersonal, 'personal:', data.personal);
        }

        // Debug del motivo
        console.log('DEBUG MOTIVO - data:', data);
        console.log('DEBUG MOTIVO - data.Motivo_Id:', data.Motivo_Id);
        console.log('DEBUG MOTIVO - data.motivo_id:', data.motivo_id);
        console.log('DEBUG MOTIVO - motivoEl:', motivoEl);
        
        // Cargar motivo usando Select2 o valor nativo
        // El motivo puede venir como Motivo_Id o motivo_id dependiendo de cómo Laravel serialice
        const motivoId = data.Motivo_Id || data.motivo_id;
        if (motivoEl && motivoId) {
            // Usar la función global específica si está disponible
            if (window.setMotivoSelect2Value) {
                window.setMotivoSelect2Value(motivoId);
            } else if (window.setSelect2Value) {
                window.setSelect2Value('#motivo_id', motivoId);
            } else {
                // Fallback: establecer valor nativo
                motivoEl.value = motivoId;
                // Intentar actualizar Select2 manualmente
                if (window.jQuery) {
                    setTimeout(function() {
                        jQuery(motivoEl).val(motivoId).trigger('change');
                    }, 200);
                }
            }
            console.log('✓ Motivo cargado:', motivoId);
        } else {
            console.warn('✗ No se pudo cargar el motivo. Motivo_Id:', data.Motivo_Id, 'motivo_id:', data.motivo_id, 'motivoEl:', !!motivoEl);
        }

        if (omEl && data.OrdenMedica) {
            omEl.value = data.OrdenMedica;
            console.log('✓ Número OM cargado:', data.OrdenMedica);
        }

        if (omAnioFormEl && data.AnioLar) {
            omAnioFormEl.value = data.AnioLar;
            console.log('✓ Año cargado:', data.AnioLar);
        }

        if (fechaOrdenEl && data.FechaCreacion) {
            fechaOrdenEl.value = data.FechaCreacion.slice(0,10);
            console.log('✓ Fecha creación cargada:', data.FechaCreacion.slice(0,10));
        }

        // Debug para campos de fecha
        console.log('Debug fechas - FechaLic:', data.FechaLic, 'FechaLicFin:', data.FechaLicFin);
        console.log('Elementos - desdeEl:', !!desdeEl, 'hastaEl:', !!hastaEl);

        if (desdeEl && data.FechaLic) {
            desdeEl.value = data.FechaLic;
            console.log('✓ Fecha desde cargada:', data.FechaLic);
        } else {
            console.log('❌ Fecha desde NO cargada - desdeEl:', !!desdeEl, 'FechaLic:', data.FechaLic);
        }

        if (hastaEl && data.FechaLicFin) {
            hastaEl.value = data.FechaLicFin;
            console.log('✓ Fecha hasta cargada:', data.FechaLicFin);
        } else {
            console.log('❌ Fecha hasta NO cargada - hastaEl:', !!hastaEl, 'FechaLicFin:', data.FechaLicFin);
        }

        if (diasEl && data.DiasTotal) {
            diasEl.value = data.DiasTotal;
            console.log('✓ Días cargados:', data.DiasTotal);
        }

        // El estado puede venir como estado_om o EstadoOM
        const estadoOm = data.estado_om || data.EstadoOM || data.EstadoOm;
        console.log('DEBUG ESTADO - estado_om:', data.estado_om, 'EstadoOM:', data.EstadoOM);
        if (estadoEl && estadoOm) {
            estadoEl.value = estadoOm;
            console.log('✓ Estado cargado:', estadoOm);
        } else {
            console.warn('✗ No se pudo cargar el estado. estado_om:', data.estado_om, 'estadoEl:', !!estadoEl);
        }

        if (obsEl && data.ObservacionLic) {
            obsEl.value = data.ObservacionLic;
            console.log('✓ Observación cargada');
        }

        if (corridoEl) {
            corridoEl.checked = (data.Cont === 1);
            console.log('✓ Corridos:', data.Cont === 1 ? 'Sí' : 'No');
        }

        if (cmEl) {
            cmEl.value = data.CertMedico || 0;
            console.log('✓ Certificado cargado:', data.CertMedico || 0);
        }

        // La disposición puede venir como NumDisp, num_disp, o en el objeto disposicion
        const numDisp = data.NumDisp || data.num_disp || data.disposicion?.IdNumDisp;
        console.log('DEBUG DISPOSICION - NumDisp:', data.NumDisp, 'disposicion?.IdNumDisp:', data.disposicion?.IdNumDisp);
        if (numDisp) {
            if (window.setSelect2Value) {
                window.setSelect2Value('#disposicion_id', numDisp);
            } else {
                const disposicionEl = document.getElementById('disposicion_id');
                if (disposicionEl) {
                    disposicionEl.value = numDisp;
                    if (window.jQuery) {
                        jQuery(disposicionEl).val(numDisp).trigger('change');
                    }
                }
            }
            console.log('✓ Disposición cargada:', numDisp);
        } else {
            console.warn('✗ No se pudo cargar la disposición. NumDisp:', data.NumDisp);
        }

        // Campos de postergación
        const posterEl = document.getElementById('poster');
        const disp2El = document.getElementById('disp2');
        const divPoster = document.querySelector('.div_poster');

        console.log('=== DATOS DE POSTERGACIÓN ===');
        console.log('MotPoster:', data.MotPoster, 'NumDispPoster:', data.NumDispPoster);
        console.log('FechaLic (desde):', data.FechaLic);

        // Cargar valores de postergación si existen
        if (posterEl && data.MotPoster && data.MotPoster !== '0') {
            posterEl.value = data.MotPoster;
            console.log('✓ Motivo postergación cargado:', data.MotPoster);
        }
        if (disp2El && data.NumDispPoster) {
            disp2El.value = data.NumDispPoster;
            console.log('✓ Disposición postergación cargada:', data.NumDispPoster);
        }

        // La visibilidad se manejará con actualizarPosterAlert()
        console.log('Postergación configurada, actualizarPosterAlert() se encargará de la visibilidad');

        // Eliminar lógica de tipo - sistema viejo no tenía tipos
        // Manejar imagen con imageLoad
        if (imagenFoto) {
            if (data.imagen_url) {
                imagenFoto.setImgPreviewUrl(data.imagen_url);
            } else if (data.imagen_base64) {
                imagenFoto.setBase64Img(data.imagen_base64);
            } else {
                imagenFoto.deleteImg();
            }
        }

        // Cambiar texto del botón guardar y título del formulario
        if (btnGuardar) {
            btnGuardar.innerHTML = '<i class="fas fa-save"></i> Actualizar';
            console.log('Texto del botón cambiado a "Actualizar"');
        }

        // Cambiar título del formulario para edición
        const tituloFormulario = document.querySelector('#panel_add .card-header h5');
        if (tituloFormulario) {
            tituloFormulario.innerHTML = '<i class="fas fa-edit"></i> Editar Orden Médica';
        }

        // No re-inicializar Select2 aquí, ya se maneja en la vista

        // Cargar historial si hay personal seleccionado
        const personalIdHistorial = data.LegajoPersonal || data.personal?.Legajo || data.personal?.idEmpleado;
        if (personalIdHistorial) {
            console.log('Cargando historial para personal ID:', personalIdHistorial);
            cargarHistorial(personalIdHistorial);
        }

        actualizarPosterAlert();
        console.log('llenarFormulario completado');
    }

    function recolectarPayload() {
        // Obtener valor de personal_id considerando Select2
        let personalValor = null;
        if (legajoEl) {
            if (window.jQuery && jQuery(legajoEl).data('select2')) {
                personalValor = jQuery(legajoEl).val();
            } else {
                personalValor = legajoEl.value;
            }
        }
        
        // Obtener valor de motivo considerando Select2
        let motivoValor = null;
        if (motivoEl) {
            if (window.jQuery && jQuery(motivoEl).data('select2')) {
                motivoValor = jQuery(motivoEl).val();
            } else {
                motivoValor = motivoEl.value;
            }
        }
        
        // Obtener valor de disposicion considerando Select2
        let disposicionValor = null;
        if (disposicionEl) {
            if (window.jQuery && jQuery(disposicionEl).data('select2')) {
                disposicionValor = jQuery(disposicionEl).val();
            } else {
                disposicionValor = disposicionEl.value;
            }
        }

        const payload = {
            personal_id: personalValor ? Number(personalValor) : null,
            motivo_id: motivoValor ? Number(motivoValor) : null,
            numero: Number(omEl ? (omEl.value || 0) : 0),
            anio: Number(omAnioFormEl ? omAnioFormEl.value : (inputAnio ? inputAnio.value : new Date().getFullYear())),
            fecha: fechaOrdenEl ? fechaOrdenEl.value : '',
            desde: desdeEl ? desdeEl.value : '',
            hasta: hastaEl ? hastaEl.value : '',
            dias: Number(diasEl ? (diasEl.value || 0) : 0),
            estado: estadoEl ? estadoEl.value : 'Pendiente',
            observacion: obsEl ? obsEl.value : '',
            corridos: !!(corridoEl && corridoEl.checked),
            certificado: Number(cmEl ? (cmEl.value || 0) : 0),
            disposicion_id: disposicionValor ? Number(disposicionValor) : null
        };

        // Campos de postergación
        const posterEl = document.getElementById('poster');
        const disp2El = document.getElementById('disp2');
        if (posterEl) payload.poster = posterEl.value || null;
        if (disp2El) payload.disp2 = disp2El.value ? Number(disp2El.value) : null;

        // Si hay imagen en base64, agregarla al payload
        if (imagenFoto && imagenFoto.getBase64Img()) {
            payload.imagen_base64 = imagenFoto.getBase64Img();
        }

        // Si hay URL de preview (imagen existente), agregarla
        if (imagenFoto && imagenFoto.getUrlPreview()) {
            payload.imagen_url = imagenFoto.getUrlPreview();
        }

        return payload;
    }

    async function guardarHandler() {
        const id = btnGuardar?.dataset?.id;
        const payload = recolectarPayload();

        // Validación: motivo es obligatorio
        if (!payload.motivo_id || payload.motivo_id === 0) {
            alert('Debe seleccionar un motivo de licencia');
            if (motivoEl) {
                motivoEl.focus();
                // Si es Select2, abrir el dropdown
                if (window.jQuery && jQuery(motivoEl).data('select2')) {
                    jQuery(motivoEl).select2('open');
                }
            }
            return;
        }

        // Validación: estado (A Reconocimiento) es obligatorio
        if (!payload.estado || payload.estado === '') {
            alert('Debe seleccionar un estado (A Reconocimiento)');
            if (estadoEl) {
                estadoEl.focus();
            }
            return;
        }

        // Validación: agente es obligatorio
        if (!payload.personal_id || payload.personal_id === 0) {
            alert('Debe seleccionar un agente');
            if (legajoEl) {
                legajoEl.focus();
                if (window.jQuery && jQuery(legajoEl).data('select2')) {
                    jQuery(legajoEl).select2('open');
                }
            }
            return;
        }

        // Validación: fechas son obligatorias
        if (!payload.desde) {
            alert('Debe ingresar la fecha de inicio (Desde)');
            if (desdeEl) desdeEl.focus();
            return;
        }
        if (!payload.hasta) {
            alert('Debe ingresar la fecha de fin (Hasta)');
            if (hastaEl) hastaEl.focus();
            return;
        }

        // Validación de OM duplicado
        if (payload.anio && payload.numero) {
            const dup = await existeOMDuplicado(payload.anio, payload.numero, id ? Number(id) : 0);
            if (dup) {
                alert('El número de OM ya existe para ese año.');
                return;
            }
        }

        const base = routeBase;
        const url = id ? `${base}/${id}` : `${base}`;
        const method = id ? 'PUT' : 'POST';

            try {
            const resp = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            });
            const json = await resp.json();
            if (json?.success) {
                limpiarFormulario();
                // Mostrar header de filtros y volver a la lista
                const headerFiltros = document.getElementById('card_header_filtros');
                if (headerFiltros) headerFiltros.style.display = 'block';
                if (panelAdd) panelAdd.classList.add('d-none');
                if (panelList) panelList.classList.remove('d-none');
                cargarOM(1);
                cargarUltimoNumero();
            } else {
                alert(json?.message || 'No se pudo guardar la OM');
            }
        } catch (err) {
            console.error('Error guardando OM:', err);
            alert('Error al guardar la orden médica');
        }
    }

    // Manejador único para guardar
    if (btnGuardar) {
        btnGuardar.addEventListener('click', function (e) {
            e.preventDefault();
            guardarHandler();
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            guardarHandler();
        });
    }

    if (btnCancelar) {
        btnCancelar.addEventListener('click', () => {
            limpiarFormulario();
            // Mostrar header de filtros y volver a la lista
            const headerFiltros = document.getElementById('card_header_filtros');
            if (headerFiltros) headerFiltros.style.display = 'block';
            if (panelAdd) panelAdd.classList.add('d-none');
            if (panelList) panelList.classList.remove('d-none');
        });
    }

    // Eliminar lógica de selector de tipo - sistema viejo no tenía tipos

    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function (e) {
            e.preventDefault();
            cargarOM(1); // Reiniciar a la primera página al filtrar
            cargarUltimoNumero();
        });
    }

    // Filtrado automático al cambiar año LAR
    if (inputAnioLar) {
        inputAnioLar.addEventListener('change', function() {
            console.log('Año LAR cambiado a:', this.value);
            cargarOM(1);
            cargarUltimoNumero();
        });
    }

    // Filtrado automático al cambiar fechas
    [inputFechaDesde, inputFechaHasta].forEach(input => {
        if (input) {
            input.addEventListener('change', function() {
                console.log('Fecha cambiada:', this.id, this.value);
                cargarOM(1);
            });
        }
    });

    // Filtrado con Enter en campos de texto
    [inputDni, inputLegajo, inputPersonal, inputNumeroOm, inputMultiplesOm].forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    cargarOM(1);
                }
            });
        }
    });

    // Botón limpiar filtros
    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', function() {
            // Limpiar campos de texto
            if (inputDni) inputDni.value = '';
            if (inputLegajo) inputLegajo.value = '';
            if (inputPersonal) inputPersonal.value = '';
            if (inputNumeroOm) inputNumeroOm.value = '';
            if (inputMultiplesOm) inputMultiplesOm.value = '';

            // Limpiar campos de fecha
            if (inputFechaDesde) inputFechaDesde.value = '';
            if (inputFechaHasta) inputFechaHasta.value = '';

            // Resetear año LAR a "Todos"
            if (inputAnioLar) inputAnioLar.value = '';

            cargarOM(1);
        });
    }

    // Cálculos automáticos: Desde + Días => Hasta
    if (diasEl) diasEl.addEventListener('change', async () => { await calcularHasta(); });
    if (desdeEl) desdeEl.addEventListener('change', async () => { await calcularHasta(); });
    if (corridoEl) corridoEl.addEventListener('change', async () => { await calcularHasta(); });

    // Cálculos automáticos: Desde + Hasta => Días
    if (hastaEl) hastaEl.addEventListener('change', async () => { await calcularDias(); });





    // Función para verificar si una fecha requiere postergación (fecha futura)
    function verificarSiRequierePostergacion(fechaInicio) {
        if (!fechaInicio) return false;

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0); // Resetear horas para comparación solo de fecha

        const fechaLicencia = new Date(fechaInicio);
        fechaLicencia.setHours(0, 0, 0, 0);

        return fechaLicencia > hoy;
    }



    // Función para limpiar el formulario y restaurar modo creación
    function limpiarFormulario() {
        // Resetear modo edición
        modoEdicion = false;
        ordenEditandoId = null;

        // Limpiar datos del botón y restaurar título
        if (btnGuardar) {
            btnGuardar.removeAttribute('data-id');
            btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar';
        }

        // Restaurar título del formulario para creación
        const tituloFormulario = document.querySelector('#panel_add .card-header h5');
        if (tituloFormulario) {
            tituloFormulario.innerHTML = '<i class="fas fa-file-medical"></i> Nueva Orden Médica';
        }

        // Resetear formulario
        if (form) form.reset();
        
        // Resetear Select2 si está inicializado
        if (window.jQuery) {
            jQuery('.select2').each(function() {
                if (jQuery(this).data('select2')) {
                    jQuery(this).val('').trigger('change');
                }
            });
        }

        // Ocultar campos de postergación
        const divPoster = document.querySelector('.div_poster');
        if (divPoster) divPoster.style.display = 'none';

        // Ocultar alerta de postergación
        if (posterAlert) posterAlert.style.display = 'none';

        // Limpiar historial
        const historialTabla = document.getElementById('historial_licencias');
        if (historialTabla) historialTabla.innerHTML = '';

        // Limpiar imagen si existe
        if (imagenFoto && imagenFoto.deleteImg) {
            imagenFoto.deleteImg();
        }

        // Cargar nuevo número de OM para modo creación
        cargarUltimoNumero();
    }

    async function existeOMDuplicado(anio, num, idActual) {
        try {
            const url = `${window.laravelRoutes.ordenMedicasList}?anio=${encodeURIComponent(anio)}`;
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await resp.json();
            if (!Array.isArray(data)) return false;
            const found = data.find(it => Number(it.num) === Number(num) && Number(it.id) !== Number(idActual));
            return !!found;
        } catch (e) {
            console.error('Error validando duplicado OM:', e);
            return false;
        }
    }



    // Inicializar imageLoad para el manejo avanzado de imágenes
    imagenFoto = new imageLoad(document.getElementById("imagen"), {
        resize: [1200, 1200],
        maxSize: 2, // 2MB máximo
        onDeleteCB: async function(dataUser) {
            // Callback cuando se elimina la imagen
            console.log("Imagen eliminada del frontend");
            
            // Si estamos en modo edición, eliminar la imagen del backend también
            if (modoEdicion && ordenEditandoId) {
                try {
                    const url = `${window.laravelRoutes.ordenMedicasBase}/${ordenEditandoId}/imagen`;
                    console.log('Eliminando imagen del backend:', url);
                    
                    const resp = await fetch(url, {
                        method: 'DELETE',
                        headers: { 
                            'Accept': 'application/json', 
                            'X-CSRF-TOKEN': csrfToken 
                        }
                    });
                    
                    const json = await resp.json();
                    
                    if (json?.success) {
                        alert('Imagen eliminada exitosamente');
                        console.log('✓ Imagen eliminada del backend');
                    } else {
                        console.warn('No se pudo eliminar la imagen del backend:', json?.message);
                    }
                } catch (err) {
                    console.error('Error eliminando imagen del backend:', err);
                    alert('Error al eliminar la imagen del servidor');
                }
            }
        },
        onViewCB: function(src) {
            // Callback para ver la imagen
            if (src) {
                window.open(src, '_blank');
            }
        },
        onChangeCB: function(base64) {
            // Callback cuando cambia la imagen
            console.log("Imagen cambiada");
        }
    });

    // Función checkCorrido para resetear el flag calculado
    window.checkCorrido = function() {
        calculado = false;
        // Eliminado el check verde - no es necesario
    }



    // Botones principales
    if (btnAdd) {
        btnAdd.addEventListener('click', function() {
            limpiarFormulario();
            // Ocultar header de filtros y mostrar formulario
            const headerFiltros = document.getElementById('card_header_filtros');
            if (headerFiltros) headerFiltros.style.display = 'none';
            if (panelAdd) panelAdd.classList.remove('d-none');
            if (panelList) panelList.classList.add('d-none');
            
            // Re-inicializar Select2 después de mostrar el panel
            if (window.jQuery && typeof jQuery.fn.select2 !== 'undefined') {
                jQuery('.select2').each(function() {
                    if (jQuery(this).data('select2')) {
                        jQuery(this).select2('destroy');
                    }
                });
                jQuery('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: jQuery('#panel_add')
                });
                console.log('Select2 re-inicializado en modo agregar');
            }
            
            cargarUltimoNumero();
        });
    }

    const btnVolver = document.getElementById('btn_volver');
    if (btnVolver) {
        btnVolver.addEventListener('click', function() {
            // Mostrar header de filtros y ocultar formulario
            const headerFiltros = document.getElementById('card_header_filtros');
            if (headerFiltros) headerFiltros.style.display = 'block';
            if (panelAdd) panelAdd.classList.add('d-none');
            if (panelList) panelList.classList.remove('d-none');
            limpiarFormulario();
        });
    }

    if (btnClear) {
        btnClear.addEventListener('click', function() {
            limpiarFormulario();
        });
    }

    if (btnCalc) {
        btnCalc.addEventListener('click', function() {
            calcularFechas();
        });
    }



    // Función para calcular fechas
    function calcularFechas() {
        const desde = desdeEl.value;
        const hasta = hastaEl.value;
        const dias = diasEl.value;

        if (desde && dias) {
            calcularHasta();
        } else if (desde && hasta) {
            calcularDias();
        }
    }



    // Función para cargar historial de licencias
    function cargarHistorial(personalId) {
        if (!personalId) return;

        console.log('Cargando historial para personal ID:', personalId);
        const url = `${window.laravelRoutes.licenciasHistorialBase}/${personalId}`;
        console.log('URL del historial:', url);

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos del historial recibidos:', data);
                const historialBody = document.getElementById('historial_licencias');

                if (!historialBody) {
                    console.error('No se encontró el elemento historial_licencias');
                    return;
                }

                historialBody.innerHTML = '';

                if (data.success && data.data && Array.isArray(data.data)) {
                    if (data.data.length === 0) {
                        const row = document.createElement('tr');
                        row.innerHTML = '<td colspan="7" class="text-center text-muted">No hay licencias registradas</td>';
                        historialBody.appendChild(row);
                    } else {
                        data.data.forEach(licencia => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${licencia.NombreCompleto || ''}</td>
                                <td>${licencia.Legajo || ''}</td>
                                <td>${licencia.FC || licencia.FechaOrden || licencia.FechaCreacion || ''}</td>
                                <td>${licencia.FIni || licencia.Desde || ''}</td>
                                <td>${licencia.FFin || licencia.Hasta || ''}</td>
                                <td>${licencia.Dias || licencia.DiasTotal || ''}</td>
                                <td>${licencia.motivo?.Motivo || licencia.Motivo || ''}</td>
                            `;
                            historialBody.appendChild(row);
                        });
                    }
                } else {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="7" class="text-center text-warning">Error al cargar el historial</td>';
                    historialBody.appendChild(row);
                }
            })
            .catch(error => {
                console.error('Error al cargar historial:', error);
                const historialBody = document.getElementById('historial_licencias');
                if (historialBody) {
                    historialBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar el historial</td></tr>';
                }
            });
    }

    // Función para calcular fecha hasta usando el backend con feriados
    async function calcularHasta() {
        console.log('calcularHasta() llamada');

        const desde = desdeEl ? desdeEl.value : null;
        const dias = diasEl ? parseInt(diasEl.value) : null;
        const corridos = document.getElementById('corridos')?.checked || false;

        console.log('Valores:', { desde, dias, corridos, desdeEl: !!desdeEl, diasEl: !!diasEl });

        if (!desde || !dias || isNaN(dias)) {
            console.log('Retornando: valores no válidos');
            return;
        }

        try {
            // Convertir fecha de YYYY-MM-DD a DD/MM/YYYY para el backend
            if (!desde || typeof desde !== 'string') {
                console.error('desde no válido:', desde);
                return;
            }
            const fechaParts = desde.split('-');
            const fechaFormateada = `${fechaParts[2]}/${fechaParts[1]}/${fechaParts[0]}`;

            const response = await fetch(window.laravelRoutes.licenciasCalcularFecha, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    dias: dias,
                    desde: fechaFormateada,
                    corridos: corridos
                })
            });

            const data = await response.json();

            if (data.success && data.hasta) {
                // Convertir DD/MM/YYYY a YYYY-MM-DD para el input date
                const hastaParts = data.hasta.split('/');
                const fechaHasta = `${hastaParts[2]}-${hastaParts[1]}-${hastaParts[0]}`;
                hastaEl.value = fechaHasta;
                actualizarPosterAlert();
            } else {
                console.error('Error en cálculo de fecha:', data.message);
            }
        } catch (error) {
            console.error('Error al calcular fecha hasta:', error);
            // Fallback al cálculo simple si falla el backend
            const fechaDesde = new Date(desde);
            const fechaHasta = new Date(fechaDesde);
            fechaHasta.setDate(fechaHasta.getDate() + dias - 1);

            const year = fechaHasta.getFullYear();
            const month = String(fechaHasta.getMonth() + 1).padStart(2, '0');
            const day = String(fechaHasta.getDate()).padStart(2, '0');

            hastaEl.value = `${year}-${month}-${day}`;
            actualizarPosterAlert();
        }
    }

    // Función para calcular días usando el backend con feriados
    async function calcularDias() {
        const desde = desdeEl.value;
        const hasta = hastaEl.value;
        const corridos = document.getElementById('corridos')?.checked || false;

        if (!desde || !hasta) return;

        try {
            // Convertir fechas de YYYY-MM-DD a DD/MM/YYYY para el backend
            const desdeParts = desde.split('-');
            const desdeFormateada = `${desdeParts[2]}/${desdeParts[1]}/${desdeParts[0]}`;

            const hastaParts = hasta.split('-');
            const hastaFormateada = `${hastaParts[2]}/${hastaParts[1]}/${hastaParts[0]}`;

            const response = await fetch(window.laravelRoutes.licenciasCalcularDias, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    desde: desdeFormateada,
                    hasta: hastaFormateada,
                    corridos: corridos
                })
            });

            const data = await response.json();

            if (data.success && data.dias) {
                diasEl.value = data.dias;
                actualizarPosterAlert();
            } else {
                console.error('Error en cálculo de días:', data.message);
            }
        } catch (error) {
            console.error('Error al calcular días:', error);
            // Fallback al cálculo simple si falla el backend
            const fechaDesde = new Date(desde);
            const fechaHasta = new Date(hasta);
            const diffTime = Math.abs(fechaHasta - fechaDesde);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

            diasEl.value = diffDays;
            actualizarPosterAlert();
        }
    }

    // Función para actualizar alerta de postergación
    function actualizarPosterAlert() {
        const desde = desdeEl ? desdeEl.value : null;
        const hasta = hastaEl ? hastaEl.value : null;
        const divPoster = document.querySelector('.div_poster');

        console.log('actualizarPosterAlert() - desde:', desde, 'hasta:', hasta);

        // Si no hay fecha desde, ocultar postergación
        if (!desde) {
            if (posterAlert) posterAlert.style.display = 'none';
            if (divPoster) divPoster.style.display = 'none';
            return;
        }

        const fechaDesde = new Date(desde);
        const fechaActual = new Date();

        // Resetear horas para comparación solo de fecha
        fechaDesde.setHours(0, 0, 0, 0);
        fechaActual.setHours(0, 0, 0, 0);

        const requierePostergacion = fechaDesde > fechaActual;
        console.log('Fecha desde:', fechaDesde, 'Fecha actual:', fechaActual, 'Requiere postergación:', requierePostergacion);

        // Mostrar/ocultar campos de postergación
        if (divPoster) {
            divPoster.style.display = requierePostergacion ? 'flex' : 'none';
        }

        // Mostrar/ocultar alerta de postergación
        if (posterAlert) {
            posterAlert.style.display = requierePostergacion ? 'block' : 'none';
        }
    }

    // Función mostrarValidado eliminada - el check verde no es necesario

    // Event listeners adicionales

    // Event listeners para cálculo automático de fechas ya configurados arriba en la línea 629

    if (desdeEl) {
        desdeEl.addEventListener('change', function() {
            if (diasEl.value && this.value) {
                calcularHasta();
            } else if (hastaEl.value && this.value) {
                calcularDias();
            }
            // Cargar historial cuando se selecciona personal
            if (legajoEl && legajoEl.value) {
                cargarHistorial(legajoEl.value);
            }
        });
    }

    if (hastaEl) {
        hastaEl.addEventListener('change', function() {
            if (desdeEl.value && this.value) {
                calcularDias();
            }
        });
    }




    // Event listener para cambios en personal
    if (legajoEl) {
        legajoEl.addEventListener('change', function() {
            if (this.value) {
                cargarHistorial(this.value);
            }
        });

        // Si hay Select2, también escuchar su evento específico
        if (window.jQuery) {
            jQuery(legajoEl).on('select2:select', function(e) {
                const personalId = e.params.data.id;
                if (personalId) {
                    cargarHistorial(personalId);
                }
            });
        }
    }

    function inicio() {
        cargarOM(1);
        cargarUltimoNumero();

        if (corridoEl) {
            corridoEl.addEventListener('change', function() {
                calcularFechas();
            });
        }
    }

     // Configurar botones de cerrar modal
     const modalObservacion = document.getElementById('modalObservacion');
     if (modalObservacion) {
         // Botón X del header
         const btnCloseHeader = modalObservacion.querySelector('button.btn-close');
         if (btnCloseHeader) {
             btnCloseHeader.addEventListener('click', function() {
                 cerrarModalObservacion();
             });
         }

         // Botón Cerrar del footer
         const btnCloseFooter = modalObservacion.querySelector('.btn-secondary[data-bs-dismiss="modal"]');
         if (btnCloseFooter) {
             btnCloseFooter.addEventListener('click', function() {
                 cerrarModalObservacion();
             });
         }

         // Cerrar al hacer click en el backdrop
         modalObservacion.addEventListener('click', function(e) {
             if (e.target === modalObservacion) {
                 cerrarModalObservacion();
             }
         });
     }

     // Función para cerrar el modal
     function cerrarModalObservacion() {
         const modal = document.getElementById('modalObservacion');
         if (window.bootstrap) {
             const bsModal = bootstrap.Modal.getInstance(modal);
             if (bsModal) bsModal.hide();
         } else if (window.jQuery) {
             jQuery(modal).modal('hide');
         } else if (modal) {
             modal.style.display = 'none';
             modal.classList.remove('show');
             const backdrop = document.querySelector('.modal-backdrop');
             if (backdrop) backdrop.remove();
         }
     }

     inicio()
});
