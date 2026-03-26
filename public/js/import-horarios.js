/* Importación de horarios (relojes) */
(function () {
    const state = {
        page: 1,
        perPage: 10,
        totalPages: 1,
        total: 0,
        data: [],
        idEdit: 0,
        indEdit: 0,
        txtBase64: '',
        marcasPersonal: [],
    };

    const permisos = window.importHorariosConfig?.permisos || { crear: false, editar: false, eliminar: false };
    const scope = window.importHorariosConfig?.scope || 'all';

    let Toast;

    function initToast() {
        Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
    }

    function parseFile(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            state.marcasPersonal = [];
            const lines = e.target.result.split('\n').slice(2);
            lines.forEach(line => {
                if (!line.trim()) return;
                const fields = line.split('\t');
                const legajo = fields[2]?.trim();
                const fecha = (fields[6] || '').replace(/\r/g, '').trim();
                if (legajo && fecha) {
                    state.marcasPersonal.push({ id: legajo, date: fecha });
                }
            });
        };
        reader.readAsText(file);

        // Base64 para guardar en servidor
        const base64Reader = new FileReader();
        base64Reader.onload = function (e) {
            const base64 = e.target.result || '';
            state.txtBase64 = base64.includes(',') ? base64.split(',')[1] : base64;
        };
        base64Reader.readAsDataURL(file);
    }

    function renderTable() {
        const tbody = document.getElementById('table_data');
        const rows = state.data.map((registro, i) => {
            const puedeEditar = permisos.editar && registro.Apellido !== null;
            const puedeEliminar = permisos.eliminar && Number(registro.Procesada) === 0 && registro.Apellido !== null;
            const estado = Number(registro.Procesada) === 0
                ? '<i class="fas fa-exclamation-circle text-warning"></i>'
                : '<i class="fas fa-check-circle text-success"></i>';

            return `
                <tr>
                    <td>${registro.FF || ''}</td>
                    <td>${registro.Reloj || ''}</td>
                    <td class="text-center">${estado}</td>
                    <td><small>${registro.ObsImport || ''}</small></td>
                    <td><small>${registro.Apellido === null ? 'DESCARGA AUTOMÁTICA' : `${registro.Apellido} ${registro.Nombre || ''}`}</small></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            ${puedeEditar ? `<button type="button" class="btn btn-primary" data-action="edit" data-index="${i}"><i class="fas fa-edit"></i></button>` : ''}
                            ${puedeEliminar ? `<button type="button" class="btn btn-danger" data-action="delete" data-id="${registro.IdListaReloj}"><i class="fa fa-trash"></i></button>` : ''}
                        </div>
                    </td>
                </tr>`;
        }).join('');
        tbody.innerHTML = rows || '<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>';

        document.getElementById('total_info').textContent = `${state.total} registros`;
        document.getElementById('page_info').textContent = `${state.page} / ${state.totalPages}`;

        tbody.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', onTableAction);
        });
    }

    function onTableAction(event) {
        const action = event.currentTarget.dataset.action;
        if (action === 'edit') {
            const idx = Number(event.currentTarget.dataset.index);
            editar(idx);
        } else if (action === 'delete') {
            const id = Number(event.currentTarget.dataset.id);
            modalEliminar(id);
        }
    }

    function cargarLista() {
        apiLaravel('/api/import-horarios', 'GET', {
            page: state.page,
            perPage: state.perPage,
            scope: scope === 'mine' ? 'mine' : 'all'
        }).then(res => {
            state.data = res.data || [];
            state.total = res.total || 0;
            state.totalPages = res.paginas || 1;
            renderTable();
        }).catch(err => {
            Toast.fire({ icon: 'error', title: err.message || 'Error al cargar' });
        });
    }

    function limpiar() {
        state.idEdit = 0;
        state.indEdit = 0;
        state.txtBase64 = '';
        state.marcasPersonal = [];
        document.getElementById('form_main').reset();
        const btnDesc = document.getElementById('btn_descargar');
        if (btnDesc) btnDesc.style.display = 'none';
        const btnDel = document.getElementById('btn_eliminar');
        if (btnDel) btnDel.style.display = permisos.eliminar ? '' : 'none';
        const btnSubmit = document.getElementById('btn_submit');
        if (btnSubmit) btnSubmit.disabled = false;
        const file = document.getElementById('fileInput');
        if (file) file.disabled = false;
    }

    function editar(index) {
        const registro = state.data[index];
        if (!registro) return;

        state.idEdit = registro.IdListaReloj;
        state.indEdit = index;

        document.getElementById('reloj').value = registro.Reloj_Id;
        document.getElementById('observacion').value = registro.ObsImport || '';
        const fileInput = document.getElementById('fileInput');
        if (fileInput) fileInput.disabled = true;
        const btnDesc = document.getElementById('btn_descargar');
        if (btnDesc) btnDesc.style.display = 'inline-flex';

        if (Number(registro.Procesada) === 1) {
            const btnDel = document.getElementById('btn_eliminar');
            if (btnDel) btnDel.style.display = 'none';
            const btnSubmit = document.getElementById('btn_submit');
            if (btnSubmit) btnSubmit.style.display = 'none';
        } else {
            const btnDel = document.getElementById('btn_eliminar');
            if (btnDel) btnDel.style.display = permisos.eliminar ? '' : 'none';
            const btnSubmit = document.getElementById('btn_submit');
            if (btnSubmit) btnSubmit.style.display = '';
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function descargar() {
        if (!state.idEdit) return;
        window.location.href = `/import-horarios/${state.idEdit}/txt`;
    }

    function guardar() {
        const payload = {
            reloj: Number(document.getElementById('reloj').value),
            observacion: document.getElementById('observacion').value || ''
        };

        if (state.idEdit === 0) {
            if (!state.txtBase64 || state.marcasPersonal.length === 0) {
                Toast.fire({ icon: 'error', title: 'Seleccione un archivo válido para importar' });
                return;
            }
            payload.txtFile = state.txtBase64;
            payload.marcasPersonal = state.marcasPersonal;
        }

        const method = state.idEdit === 0 ? 'POST' : 'PUT';
        const url = state.idEdit === 0 ? '/api/import-horarios' : `/api/import-horarios/${state.idEdit}`;

        apiLaravel(url, method, payload)
            .then(() => {
                Toast.fire({ icon: 'success', title: 'Guardado correctamente' });
                limpiar();
                cargarLista();
            })
            .catch(err => {
                Toast.fire({ icon: 'error', title: err.message || 'Error al guardar' });
            });
    }

    let idEliminar = 0;
    function modalEliminar(id) {
        idEliminar = id || state.idEdit;
        const modal = new bootstrap.Modal(document.getElementById('modal_eliminar'));
        modal.show();
    }

    function eliminarRegistro() {
        if (!idEliminar) return;
        apiLaravel(`/api/import-horarios/${idEliminar}`, 'DELETE')
            .then(() => {
                Toast.fire({ icon: 'success', title: 'Eliminado correctamente' });
                limpiar();
                cargarLista();
            })
            .catch(err => Toast.fire({ icon: 'error', title: err.message || 'Error al eliminar' }));
    }

    function bindEvents() {
        document.getElementById('fileInput').addEventListener('change', parseFile);
        document.getElementById('btn_submit')?.addEventListener('click', guardar);
        document.getElementById('btn_descargar')?.addEventListener('click', descargar);
        document.getElementById('btn_eliminar_modal').addEventListener('click', eliminarRegistro);
        document.getElementById('btn_eliminar')?.addEventListener('click', () => modalEliminar());
        document.getElementById('btn_limpiar')?.addEventListener('click', limpiar);
        document.getElementById('perPage').addEventListener('change', (e) => {
            state.perPage = Number(e.target.value) || 10;
            state.page = 1;
            cargarLista();
        });
        document.getElementById('btn_prev').addEventListener('click', () => {
            if (state.page > 1) {
                state.page -= 1;
                cargarLista();
            }
        });
        document.getElementById('btn_next').addEventListener('click', () => {
            if (state.page < state.totalPages) {
                state.page += 1;
                cargarLista();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initToast();
        bindEvents();
        cargarLista();
    });
})();
