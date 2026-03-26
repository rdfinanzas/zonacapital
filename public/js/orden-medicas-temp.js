document.addEventListener('DOMContentLoaded', function () {
    const btnFiltrar = document.getElementById('btn-filtrar');
    const tbody = document.getElementById('tbody-om') || document.getElementById('table_data_om');
    const inputDni = document.getElementById('dni_filtro');
    const inputLegajo = document.getElementById('legajo_filtro');
    const inputPersonal = document.getElementById('personal_filtro');
    const inputFechaDesde = document.getElementById('fecha_desde');
    const inputFechaHasta = document.getElementById('fecha_hasta');
    const inputAnioLar = document.getElementById('anio_lar_filtro');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
    const omAnioEl = document.getElementById('om_anio');
    const omSugEl = document.getElementById('om_sugerido');

    // Form elements (alta/ediciÃ³n)
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
    const routeBase = routes.ordenMedicasBase || routes.ordenMedicasGuardar || routes['orden-medicas'] || '';

    let currentPage = 1;
    let lastPage = 1;
    let perPage = 15;

    // FunciÃ³n para mapear estado numÃ©rico a texto
    function getEstadoTexto(estadoNumerico) {
        const estados = {
            1: 'Finalizado',
            2: 'Pendiente envio',
            3: 'Enviado',
            4: 'Anulado'
        };
        return estados[estadoNumerico] || 'Pendiente';
    }

    async function cargarOM(page = 1) {
        // Solo aplicar filtro si estamos en la vista de tabla (panel_list)
        const panelList = document.getElementById('panel_list');
        const panelAdd = document.getElementById('panel_add');

        // Obtener todos los valores de filtros
        const anioLar = inputAnioLar ? inputAnioLar.value : '';

        const params = new URLSearchParams();

        // Filtros bÃ¡sicos
        if (anioLar) params.append('anio_lar', anioLar);
        if (inputDni && inputDni.value.trim()) params.append('dni', inputDni.value.trim());
        if (inputLegajo && inputLegajo.value.trim()) params.append('legajo', inputLegajo.value.trim());
        if (inputPersonal && inputPersonal.value.trim()) params.append('personal', inputPersonal.value.trim());

        // Filtros de fecha de creaciÃ³n
        if (inputFechaDesde && inputFechaDesde.value) params.append('fecha_desde', inputFechaDesde.value);
        if (inputFechaHasta && inputFechaHasta.value) params.append('fecha_hasta', inputFechaHasta.value);

        params.append('page', page);
        params.append('per_page', perPage);

        console.log('ParÃ¡metros de filtro enviados:', {
            anio: anio,
            dni: inputDni ? inputDni.value.trim() : '',
            legajo: inputLegajo ? inputLegajo.value.trim() : '',
            personal: inputPersonal ? inputPersonal.value.trim() : '',
            page: page,
            per_page: perPage
        });

        try {
            // Construir URL correctamente, evitando ? vacÃ­o
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

    async function cargarUltimoNumero() {
        // Si el formulario estÃ¡ visible, usar el campo anio del formulario
        const panelAdd = document.getElementById('panel_add');
        const anioForm = document.getElementById('anio')?.value || '';
        const anioFiltro = document.getElementById('anio_filtro');

        // Priorizar aÃ±o del formulario si estÃ¡ visible, sino usar filtro
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
            // Rellenar el formulario con el nÃºmero sugerido
            if (omEl) {
                omEl.value = data?.sugerido ?? '';
            }
        } catch (e) {
            console.error('Error obteniendo Ãºltimo nÃºmero OM:', e);
        }
    }

    // FunciÃ³n para convertir fecha de YYYY-MM-DD a DD/MM/YYYY
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
            // Formatear nÃºmero de orden mÃ©dica como "NÃºmero/AÃ±o"
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
                        <button class="btn btn-primary btn-sm" data-action="edit" data-id="${it.IdLicencia}" title="Editar">
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
                            `<button class="btn btn-warning btn-sm" data-action="view-obs" data-id="${it.IdLicencia}" data-om="${numeroOM}" data-obs="${it.ObservacionLic.replace(/"/g, '&quot;')}" title="Ver observaciÃ³n">
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
                if (!confirm('Â¿Eliminar esta orden mÃ©dica?')) return;
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

        tbody.querySelectorAll('button[data-action="edit"]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                if (!id) return;
                try {
                    const url = `${window.laravelRoutes.ordenMedicasBase}/${id}`;
                    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await resp.json();
                    llenarFormulario(data);
                } catch (err) {
                    console.error('Error cargando OM', err);
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
                        // Usar la librerÃ­a imageLoad para mostrar la imagen
                        if (typeof imageLoad !== 'undefined') {
                            imageLoad.show(data.imagen_ficha || data.imagen_url || data.imagen_base64);
                        } else {
                            // Fallback: mostrar en una nueva ventana
                            const nuevaVentana = window.open('', '_blank');
                            nuevaVentana.document.write(`
                                <html>
                                    <head><title>Imagen - Orden MÃ©dica ${data.OrdenMedica}/${data.AnioLar}</title></head>
                                    <body style="margin:0; text-align:center;">
                                        <img src="${data.imagen_ficha || data.imagen_url || data.imagen_base64}"
                                             style="max-width:100%; max-height:100vh; object-fit:contain;">
                                    </body>
                                </html>
                            `);
                        }
                    } else {
                        alert('Esta orden mÃ©dica no tiene imagen asociada');
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
                document.getElementById('modalObservacionTexto').textContent = observacion || 'Sin observaciÃ³n';

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

        // Mostrar informaciÃ³n de la paginaciÃ³n
        if (total > 0) {
            infoPaginacion.textContent = `Mostrando ${from} a ${to} de ${total} resultados`;
        } else {
            infoPaginacion.textContent = 'No se encontraron resultados';
        }

        // Generar controles de paginaciÃ³n
        controlesPaginacion.innerHTML = '';

        if (last_page <= 1) return;

        // BotÃ³n anterior
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${current_page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${current_page - 1}">Anterior</a>`;
        controlesPaginacion.appendChild(prevLi);

        // PÃ¡ginas numeradas
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

        // BotÃ³n siguiente
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${current_page === last_page ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${current_page + 1}">Siguiente</a>`;
        controlesPaginacion.appendChild(nextLi);

        // Event listeners para los enlaces de paginaciÃ³n
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

    function llenarFormulario(data) {
        if (!data) return;
        btnGuardar.dataset.id = data.IdLicencia || data.id;
        if (legajoEl) legajoEl.value = data.LegajoPersonal || data.legajo || '';
        if (motivoEl) motivoEl.value = data.Motivo_Id || data.motivo_id || '';
        if (omEl) omEl.value = data.OrdenMedica || data.num || '';
        if (omAnioFormEl) omAnioFormEl.value = data.AnioLar || data.anio || '';
        if (fechaOrdenEl) fechaOrdenEl.value = (data.FechaCreacion || '').slice(0,10);
        if (desdeEl) desdeEl.value = data.FechaLic || data.desde || '';
        if (hastaEl) hastaEl.value = data.FechaLicFin || data.hasta || '';
        if (diasEl) diasEl.value = data.DiasTotal || data.dias || '';
        if (estadoEl) estadoEl.value = data.estado_om ?? 1;
        if (obsEl) obsEl.value = data.ObservacionLic || data.observacion || '';
        if (corridoEl) corridoEl.checked = (data.Cont ?? 0) === 1;
        if (cmEl) cmEl.value = data.CertMedico || data.certificado || 0;
        if (disposicionEl) disposicionEl.value = data.NumDisp || data.disposicion_id || '';

        // Campos de postergaciÃ³n
        const posterEl = document.getElementById('poster');
        const disp2El = document.getElementById('disp2');
        if (posterEl) posterEl.value = data.MotPoster || '';
        if (disp2El) disp2El.value = data.NumDispPoster || '';

        // Eliminar lÃ³gica de tipo - sistema viejo no tenÃ­a tipos
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
        actualizarPosterAlert();
    }

    function recolectarPayload() {
        const payload = {
            personal_id: Number(legajoEl ? legajoEl.value : 0),
            motivo_id: Number(motivoEl ? motivoEl.value : 0),
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
            disposicion_id: Number(disposicionEl ? (disposicionEl.value || 0) : 0)
        };

        // Campos de postergaciÃ³n
        const posterEl = document.getElementById('poster');
        const disp2El = document.getElementById('disp2');
        if (posterEl) payload.poster = posterEl.value;
        if (disp2El) payload.disp2 = Number(disp2El.value || 0);

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

        // ValidaciÃ³n de OM duplicado
        if (payload.anio && payload.numero) {
            const dup = await existeOMDuplicado(payload.anio, payload.numero, id ? Number(id) : 0);
            if (dup) {
                alert('El nÃºmero de OM ya existe para ese aÃ±o.');
                return;
            }
        }

        const base = routeBase;
        const url = id ? `${base}/${id}` : `${base}`;
        const method = id ? 'PUT' : 'POST';

        // Sin imagen, usar el mÃ©todo original con JSON
        if (window.jQuery && jQuery.ajax) {
            jQuery.ajax({
                url,
                method,
                contentType: 'application/json',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                data: JSON.stringify(payload)
            }).done(function(json){
                if (json && json.success) {
                    if (form) form.reset();
                    if (btnGuardar) btnGuardar.dataset.id = '';
                    cargarOM();
                    cargarUltimoNumero();
                } else {
                    alert((json && json.message) || 'No se pudo guardar la OM');
                }
            }).fail(function(err){
                console.error('Error guardando OM (jQuery):', err);
                alert('OcurriÃ³ un error guardando la OM');
            });
            return;
        }

        try {
            const resp = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            });
            const json = await resp.json();
            if (json?.success) {
                if (form) form.reset();
                if (btnGuardar) btnGuardar.dataset.id = '';
                cargarOM(1); // Volver a la primera pÃ¡gina despuÃ©s de guardar
                cargarUltimoNumero();
            } else {
                alert(json?.message || 'No se pudo guardar la OM');
            }
        } catch (err) {
            console.error('Error guardando OM:', err);
        }
    }

    // Usar submit del formulario como mecanismo principal
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            guardarHandler();
        });
    }
    // En caso de que exista botÃ³n de guardar, mantener compatibilidad
    if (btnGuardar) {
        btnGuardar.addEventListener('click', function (e) {
            e.preventDefault();
            guardarHandler();
        });
    }

    if (btnCancelar) btnCancelar.addEventListener('click', () => {
        if (form) form.reset();
        if (btnGuardar) btnGuardar.dataset.id = '';
        cargarUltimoNumero();
    });

    // Alternativa: guardar con submit del formulario
    if (form && !btnGuardar) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const clickEvt = new Event('click');
            // Simular click del botÃ³n si apareciera en futuro
            if (btnGuardar) {
                btnGuardar.dispatchEvent(clickEvt);
            }
        });
    }

    // Eliminar lÃ³gica de selector de tipo - sistema viejo no tenÃ­a tipos

    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function (e) {
            e.preventDefault();
            cargarOM(1); // Reiniciar a la primera pÃ¡gina al filtrar
            cargarUltimoNumero();
        });
    }

    // Filtrado automÃ¡tico al cambiar aÃ±o LAR
    if (inputAnioLar) {
        inputAnioLar.addEventListener('change', function() {
            console.log('AÃ±o LAR cambiado a:', this.value);
            cargarOM(1);
            cargarUltimoNumero();
        });
    }

    // Filtrado automÃ¡tico al cambiar fechas
    [inputFechaDesde, inputFechaHasta].forEach(input => {
        if (input) {
            input.addEventListener('change', function() {
                console.log('Fecha cambiada:', this.id, this.value);
                cargarOM(1);
            });
        }
    });

    // Filtrado con Enter en campos de texto
    [inputDni, inputLegajo, inputPersonal].forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    cargarOM(1);
                }
            });
        }
    });

    // BotÃ³n limpiar filtros
    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', function() {
            // Limpiar campos de texto
            if (inputDni) inputDni.value = '';
            if (inputLegajo) inputLegajo.value = '';
            if (inputPersonal) inputPersonal.value = '';

            // Limpiar campos de fecha
            if (inputFechaDesde) inputFechaDesde.value = '';
            if (inputFechaHasta) inputFechaHasta.value = '';

            // Resetear aÃ±o LAR a "Todos"
            if (inputAnioLar) inputAnioLar.value = '';

            cargarOM(1);
        });
    }

    // CÃ¡lculos automÃ¡ticos: Desde + DÃ­as => Hasta
    if (diasEl) diasEl.addEventListener('change', async () => { await calcularHasta(); });
    if (desdeEl) desdeEl.addEventListener('change', async () => { await calcularHasta(); });
    if (corridoEl) corridoEl.addEventListener('change', async () => { await calcularHasta(); });

    // CÃ¡lculos automÃ¡ticos: Desde + Hasta => DÃ­as
    if (hastaEl) hastaEl.addEventListener('change', async () => { await calcularDias(); });





    function actualizarPosterAlert() {
        if (!posterAlert) return;
        const anioFiltro = document.getElementById('anio_filtro');
        const omAnioFormEl = document.getElementById('anio');
        const anioSel = Number((omAnioFormEl ? omAnioFormEl.value : '') || (anioFiltro ? anioFiltro.value : '') || new Date().getFullYear());
        const hastaStr = (hastaEl.value || '').trim();
        if (!hastaStr || !anioSel) { posterAlert.style.display = 'none'; return; }
        const partes = hastaStr.split('/');
        if (partes.length !== 3) { posterAlert.style.display = 'none'; return; }
        const hastaYear = Number(partes[2]);
        posterAlert.style.display = (hastaYear > anioSel) ? '' : 'none';
    }

    async function existeOMDuplicado(anio, num, idActual) {
        try {
            const url = `${window.laravelRoutes.ordenMedicasFiltrar}?anio=${encodeURIComponent(anio)}`;
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



    // Inicializar imageLoad para el manejo avanzado de imÃ¡genes
    imagenFoto = new imageLoad(document.getElementById("imagen"), {
        resize: [1200, 1200],
        maxSize: 2, // 2MB mÃ¡ximo
        onDeleteCB: function(dataUser) {
            // Callback cuando se elimina la imagen
            console.log("Imagen eliminada");
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

    // FunciÃ³n checkCorrido para resetear el flag calculado
    window.checkCorrido = function() {
        calculado = false;
        // Eliminado el check verde - no es necesario
    }

    // IntegraciÃ³n jQuery: validaciÃ³n del formulario si estÃ¡ disponible
    if (window.jQuery && jQuery.fn && jQuery.fn.validate) {
        const $form = jQuery('#form_main');
        if ($form.length) {
            $form.validate({
                rules: {
                    personal_id: { required: true },
                    motivo_id: { required: true },
                    fecha: { required: true },
                    dias: { required: true, min: 1 },
                    d: { required: true },
                    h: { required: true }
                },
                messages: {
                    personal_id: 'Seleccione personal',
                    motivo_id: 'Seleccione motivo',
                    fecha: 'Ingrese la fecha',
                    dias: 'Ingrese dÃ­as (mÃ­nimo 1)',
                    d: 'Ingrese fecha desde',
                    h: 'Ingrese fecha hasta'
                },
                submitHandler: function(formEl, e) {
                    e.preventDefault();
                    guardarHandler();
                }
            });
        }
    }

    // Inicializar datepicker jQuery en campos con clase opt-in
    if (window.jQuery && jQuery.fn && jQuery.fn.datetimepicker) {
        jQuery('.js-date').datetimepicker({
            format: 'Y-m-d',
            timepicker: false,
        });
    }

   //INICIA BOTONES
    if (window.jQuery) {
        console.log("tiene jqeury")
        // BotÃ³n Agregar - muestra el formulario y oculta la tabla y el filtro
        jQuery('#btn_add').on('click', function() {
            limpiarFormulario();
            jQuery('#panel_add').removeClass('d-none').show();
            jQuery('#panel_list').hide();
            jQuery('.card-header').hide(); // Ocultar el Ã¡rea del filtro y botÃ³n Agregar
            // Cargar Ãºltimo nÃºmero al abrir el formulario
            cargarUltimoNumero();
        });

        // BotÃ³n Volver - recarga la pÃ¡gina (como en la versiÃ³n original)
        jQuery('#btn_volver').on('click', function() {
            location.reload();
            jQuery('.card-header').show(); // Mostrar nuevamente el Ã¡rea del filtro y botÃ³n Agregar
        });

        // BotÃ³n Limpiar
        jQuery('#btn_clear').on('click', function() {
            limpiarFormulario();
        });

        // BotÃ³n Calcular
        jQuery('#btn_calc').on('click', function() {
            calcularFechas();
        });

    } else {
        // Fallback sin jQuery
        const btnAdd = document.getElementById('btn_add');
        const btnVolver = document.getElementById('btn_volver');
        const btnClear = document.getElementById('btn_clear');
        const btnCalc = document.getElementById('btn_calc');
        const panelAdd = document.getElementById('panel_add');
        const panelList = document.getElementById('panel_list');

        if (btnAdd) {
            btnAdd.addEventListener('click', function() {
                limpiarFormulario();
                if (panelAdd) {
                    panelAdd.classList.remove('d-none');
                    panelAdd.style.display = 'block';
                }
                if (panelList) panelList.style.display = 'none';
                // Cargar Ãºltimo nÃºmero al abrir el formulario
                cargarUltimoNumero();
            });
        }

        if (btnVolver) {
            btnVolver.addEventListener('click', function() {
                location.reload();
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
    }

    // FunciÃ³n para limpiar el formulario
    function limpiarFormulario() {
        if (form) form.reset();
        // Limpiar selects
        if (legajoEl) legajoEl.value = '';
        if (motivoEl) motivoEl.value = '';
        if (estadoEl) estadoEl.value = 'Pendiente';
        if (corridoEl) corridoEl.checked = false;
        if (diasEl) diasEl.value = '';
        // Limpiar nÃºmero OM
        if (omEl) omEl.value = '';
        // Limpiar historial
        const historial = document.getElementById('historial_licencias');
        if (historial) historial.innerHTML = '';
        // Ocultar alertas
        const posterAlert = document.getElementById('poster_alert');
        if (posterAlert) posterAlert.style.display = 'none';
        // Limpiar imagen con imageLoad
        if (imagenFoto) {
            imagenFoto.deleteImg();
        }
    }

    // FunciÃ³n para calcular fechas
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

    // FunciÃ³n para cargar el Ãºltimo nÃºmero de orden mÃ©dica
    function cargarUltimoNumero() {
        fetch(window.laravelRoutes.ordenMedicasUltimoNumero)
            .then(response => response.json())
            .then(data => {
                if (data.numero) {
                    omEl.value = data.numero;
                }
            })
            .catch(error => console.error('Error al cargar Ãºltimo nÃºmero:', error));
    }

    // FunciÃ³n para cargar historial de licencias
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
                    console.error('No se encontrÃ³ el elemento historial_licencias');
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

    // FunciÃ³n para calcular fecha hasta usando el backend con feriados
    async function calcularHasta() {
        console.log('calcularHasta() llamada');
        
        const desde = desdeEl ? desdeEl.value : null;
        const dias = diasEl ? parseInt(diasEl.value) : null;
        const corridos = document.getElementById('corridos')?.checked || false;

        console.log('Valores:', { desde, dias, corridos, desdeEl: !!desdeEl, diasEl: !!diasEl });

        if (!desde || !dias || isNaN(dias)) {
            console.log('Retornando: valores no vÃ¡lidos');
            return;
        }

        try {
            // Convertir fecha de YYYY-MM-DD a DD/MM/YYYY para el backend
            const fechaParts = (desde && typeof desde === 'string') ? desde.split('-') : [];
            
            if (fechaParts.length !== 3) {
                throw new Error(Formato de fecha inválido: +desde);
            }
            
            const fechaFormateada = `${fecheParts[2]}/${fechaParts[1]}/${fechaParts[0]}`;

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
                console.error('Error en cÃ¡lculo de fecha:', data.message);
            }
        } catch (error) {
            console.error('Error al calcular fecha hasta:', error);
            // Fallback al cÃ¡lculo simple si falla el backend
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

    // FunciÃ³n para calcular dÃ­as usando el backend con feriados
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
                console.error('Error en cÃ¡lculo de dÃ­as:', data.message);
            }
        } catch (error) {
            console.error('Error al calcular dÃ­as:', error);
            // Fallback al cÃ¡lculo simple si falla el backend
            const fechaDesde = new Date(desde);
            const fechaHasta = new Date(hasta);
            const diffTime = Math.abs(fechaHasta - fechaDesde);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

            diasEl.value = diffDays;
            actualizarPosterAlert();
        }
    }

    // FunciÃ³n para actualizar alerta de postergaciÃ³n
    function actualizarPosterAlert() {
        const desde = desdeEl.value;
        const hasta = hastaEl.value;

        if (desde && hasta) {
            const fechaDesde = new Date(desde);
            const fechaHasta = new Date(hasta);
            const fechaActual = new Date();

            // Mostrar/ocultar campos de postergaciÃ³n segÃºn condiciones
            const divPoster = document.querySelector('.div_poster');
            if (divPoster) {
                // Mostrar campos de postergaciÃ³n si la fecha de inicio es posterior a la fecha actual
                if (fechaDesde > fechaActual) {
                    divPoster.style.display = 'flex';
                } else {
                    divPoster.style.display = 'none';
                }
            }

            // Mostrar/ocultar alerta de postergaciÃ³n
            if (posterAlert) {
                if (fechaDesde > fechaActual) {
                    posterAlert.style.display = 'block';
                } else {
                    posterAlert.style.display = 'none';
                }
            }
        }
    }

    // FunciÃ³n mostrarValidado eliminada - el check verde no es necesario

    // Event listeners adicionales

    // Event listeners para cÃ¡lculo automÃ¡tico de fechas ya configurados arriba en la lÃ­nea 629

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




    // Configurar event listener para Select2 del personal
    if (window.jQuery) {
        // Usar el evento select2:select para capturar cambios en Select2
        jQuery(document).on('select2:select', '#personal_id', function(e) {
            const personalId = e.params.data.id;
            if (personalId) {
                cargarHistorial(personalId);
            }
        });

        // TambiÃ©n mantener el evento change como fallback
        jQuery('#personal_id').on('change', function() {
            const personalId = jQuery(this).val();
            if (personalId) {
                cargarHistorial(personalId);
            }
        });
    } else {
        // Fallback sin jQuery
        if (legajoEl) {
            legajoEl.addEventListener('change', function() {
                if (this.value) {
                    cargarHistorial(this.value);
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
         // BotÃ³n X del header
         const btnCloseHeader = modalObservacion.querySelector('button.btn-close');
         if (btnCloseHeader) {
             btnCloseHeader.addEventListener('click', function() {
                 cerrarModalObservacion();
             });
         }

         // BotÃ³n Cerrar del footer
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

     // FunciÃ³n para cerrar el modal
     function cerrarModalObservacion() {
         if (window.bootstrap) {
             const modal = bootstrap.Modal.getInstance(document.getElementById('modalObservacion'));
             if (modal) {
                 modal.hide();
             }
         } else if (window.jQuery) {
             jQuery('#modalObservacion').modal('hide');
         } else {
             // Fallback vanilla JavaScript
             const modal = document.getElementById('modalObservacion');
             if (modal) {
                 modal.style.display = 'none';
                 modal.classList.remove('show');
                 // Remover backdrop si existe
                 const backdrop = document.querySelector('.modal-backdrop');
                 if (backdrop) {
                     backdrop.remove();
                 }
             }
         }
     }

     inicio()
});
