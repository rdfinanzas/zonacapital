@extends('layouts.app')

@section('css')
<style>
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
    .table-responsive {
        margin-top: 20px;
    }
    .btn-group-sm .btn {
        margin-right: 5px;
    }

    /* Estilos para los filtros */
    .card-header .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        color: #495057;
    }

    .card-header .form-control-sm,
    .card-header .form-select-sm {
        font-size: 0.875rem;
    }

    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }

    .d-flex.gap-2 .btn {
        white-space: nowrap;
    }

    /* Badge styles */
    .badge-lar {
        background-color: #28a745;
        color: white;
        font-size: 0.9em;
    }
    
    /* Estilos para columnas de días */
    .dias-tomados {
        background-color: #17a2b8;
        color: white;
    }
    
    .dias-corresponde {
        color: #007bff;
        font-weight: 600;
    }
    
    .dias-pendiente-alto {
        color: #28a745;
        font-weight: 700;
    }
    
    .dias-pendiente-bajo {
        color: #dc3545;
        font-weight: 700;
    }
</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- Card header con filtros -->
                    <div class="card-header" id="card_header_filtros">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-12">
                                <h4 class="mb-0"><i class="fas fa-calendar-check text-success"></i> Lista de Licencias Anuales por Remuneración (LAR)</h4>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <label class="form-label">Año LAR</label>
                                <select id="anio_lar_filtro" class="form-select form-select-sm">
                                    <option value="" selected>Todos</option>
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fecha Creación</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="date" id="fecha_desde" class="form-control form-control-sm" placeholder="Desde">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" id="fecha_hasta" class="form-control form-control-sm" placeholder="Hasta">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">DNI / Legajo</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="text" id="dni_filtro" class="form-control form-control-sm" placeholder="DNI">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" id="legajo_filtro" class="form-control form-control-sm" placeholder="Legajo">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Agente</label>
                                <input type="text" id="personal_filtro" class="form-control form-control-sm" placeholder="Nombre/Apellido">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Personal</label>
                                <select id="personal_id_filtro" class="form-select form-select-sm select2">
                                    <option value="">Todos</option>
                                    @foreach ($personal as $p)
                                        <option value="{{ $p->Legajo }}">{{ $p->Apellido }}, {{ $p->Nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button id="btn-filtrar" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                    <button id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-eraser"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <label class="form-label">&nbsp;</label>
                                <a href="{{ url('licencias/lar') }}" id="btn_add" class="btn btn-success btn-sm d-block">
                                    <i class="fas fa-plus"></i> Nueva LAR
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de la lista -->
                    <div class="card-body" id="panel_list">
                        <div class="table-responsive">
                            <table id="tabla-lar" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Agente</th>
                                        <th>DNI</th>
                                        <th>Legajo</th>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Tomados</th>
                                        <th>Corresponde</th>
                                        <th>Pendiente</th>
                                        <th>Año LAR</th>
                                        <th>Disposición</th>
                                        <th>Observaciones</th>
                                        <th>Creador</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table_data_lar"></tbody>
                            </table>
                        </div>
                        <div id="paginacion-container" class="d-flex justify-content-between align-items-center mt-3">
                            <div class="dataTables_info">
                                <span id="info-paginacion"></span>
                            </div>
                            <div class="dataTables_paginate paging_simple_numbers">
                                <ul class="pagination" id="paginacion-controles"></ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
@endsection

<!-- Modal para mostrar observaciones -->
<div class="modal fade" id="modalObservacion" tabindex="-1" role="dialog" aria-labelledby="modalObservacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalObservacionLabel">
                    <i class="fas fa-info-circle"></i> Observación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><strong>LAR:</strong></label>
                    <p id="modalObservacionLAR" class="mb-2"></p>
                </div>
                <div class="form-group">
                    <label><strong>Observación:</strong></label>
                    <div id="modalObservacionTexto" class="border rounded p-3 bg-light" style="min-height: 100px; white-space: pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    window.laravelRoutes = {
        larListaFiltrar: '{{ route('lar-lista.filtrar') }}',
        larEditar: '{{ route('licencias.lar') }}',
        larImprimir: '{{ url('licencias/imprimir/lar') }}',
        larNuevo: '{{ route('licencias.lar') }}',
    };
    window.csrfToken = '{{ csrf_token() }}';

    // Variable global para controlar si Select2 está listo
    window.select2Ready = false;
    
    // Función para inicializar Select2 de forma segura
    function initSelect2() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
            });
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
            window.select2Ready = true;
        }
    }

    $(document).ready(function() {
        initSelect2();
        cargarLAR();
    });

    let currentPage = 1;
    let currentPerPage = 15;

    // Función para cargar las LAR
    function cargarLAR(page = 1) {
        currentPage = page;
        
        const filtros = {
            anio_lar: $('#anio_lar_filtro').val(),
            fecha_desde: $('#fecha_desde').val(),
            fecha_hasta: $('#fecha_hasta').val(),
            personal_id: $('#personal_id_filtro').val(),
            dni: $('#dni_filtro').val(),
            legajo: $('#legajo_filtro').val(),
            personal: $('#personal_filtro').val(),
            per_page: currentPerPage,
            page: page
        };

        console.log('Cargando LAR con filtros:', filtros);

        $.ajax({
            url: window.laravelRoutes.larListaFiltrar,
            method: 'GET',
            data: filtros,
            success: function(response) {
                console.log('Respuesta recibida:', response);
                
                if (response.success === false) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al cargar las LAR'
                    });
                    return;
                }
                
                renderizarTabla(response);
                renderizarPaginacion(response);
            },
            error: function(xhr) {
                console.error('Error al cargar LAR:', xhr);
                let errorMsg = 'No se pudieron cargar las LAR';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        });
    }

    // Función para renderizar la tabla
    function renderizarTabla(data) {
        let html = '';
        
        if (data.data && data.data.length > 0) {
            data.data.forEach(function(lar) {
                const personal = lar.personal || {};
                const creador = lar.creador || {};
                const disposicion = lar.disposicion || {};
                
                // Mostrar disposición - primero intentar con la relación, sino con el campo directo
                let dispText = '-';
                if (disposicion && disposicion.NumDisp) {
                    dispText = disposicion.NumDisp + '/' + (disposicion.AnioDisp || lar.AnioLar || '');
                } else if (lar.NumDisp) {
                    dispText = lar.NumDisp;
                }
                
                // Datos de días LAR
                const corresponde = lar.dias_correspondientes !== null ? lar.dias_correspondientes : '-';
                const tomados = lar.dias_tomados !== null ? lar.dias_tomados : lar.DiasTotal;
                const pendiente = lar.dias_pendientes !== null ? lar.dias_pendientes : '-';
                
                // Clase para pendiente (verde si tiene días, rojo si está en 0 o negativo)
                let pendienteClass = 'text-muted';
                if (lar.dias_pendientes !== null) {
                    pendienteClass = lar.dias_pendientes > 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
                }
                
                html += `<tr>
                    <td>${lar.IdLicencia}</td>
                    <td>${formatFecha(lar.FechaCreacion)}</td>
                    <td>${personal.Apellido || ''}, ${personal.Nombre || ''}</td>
                    <td>${personal.DNI || ''}</td>
                    <td>${personal.Legajo || ''}</td>
                    <td>${formatFecha(lar.FechaLic)}</td>
                    <td>${formatFecha(lar.FechaLicFin)}</td>
                    <td class="text-center"><span class="badge bg-info">${lar.DiasTotal}</span></td>
                    <td class="text-center text-primary fw-bold">${corresponde}</td>
                    <td class="text-center ${pendienteClass}">${pendiente}</td>
                    <td class="text-center">${lar.AnioLar || '-'}</td>
                    <td>${dispText}</td>
                    <td>`;
                
                if (lar.ObservacionLic) {
                    html += `<button type="button" class="btn btn-link btn-sm p-0" onclick="verObservacion(${lar.IdLicencia}, '${escapeHtml(lar.ObservacionLic)}', '${personal.Apellido || ''}, ${personal.Nombre || ''}')">
                        <i class="fas fa-eye text-info"></i> Ver
                    </button>`;
                } else {
                    html += '-';
                }
                
                html += `</td>
                    <td>${creador.Nombre || ''} ${creador.Apellido || ''}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="${window.laravelRoutes.larEditar}?editar=${lar.IdLicencia}&legajo=${lar.LegajoPersonal}" class="btn btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="${window.laravelRoutes.larImprimir}/${lar.IdLicencia}" class="btn btn-info" title="Imprimir" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </td>
                </tr>`;
            });
        } else {
            html = `<tr><td colspan="15" class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                No se encontraron LAR
            </td></tr>`;
        }
        
        $('#table_data_lar').html(html);
        
        // Actualizar info de paginación
        const from = data.from || 0;
        const to = data.to || 0;
        const total = data.total || 0;
        $('#info-paginacion').text(`Mostrando ${from} a ${to} de ${total} registros`);
    }

    // Función para renderizar paginación
    function renderizarPaginacion(data) {
        let html = '';
        
        // Botón anterior
        html += `<li class="paginate_button page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a href="#" class="page-link" onclick="${data.current_page !== 1 ? 'cargarLAR(' + (data.current_page - 1) + ')' : 'return false;'}; return false;">Anterior</a>
        </li>`;
        
        // Números de página
        const startPage = Math.max(1, data.current_page - 2);
        const endPage = Math.min(data.last_page, data.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="paginate_button page-item ${i === data.current_page ? 'active' : ''}">
                <a href="#" class="page-link" onclick="cargarLAR(${i}); return false;">${i}</a>
            </li>`;
        }
        
        // Botón siguiente
        html += `<li class="paginate_button page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a href="#" class="page-link" onclick="${data.current_page !== data.last_page ? 'cargarLAR(' + (data.current_page + 1) + ')' : 'return false;'}; return false;">Siguiente</a>
        </li>`;
        
        $('#paginacion-controles').html(html);
    }

    // Función para formatear fecha
    function formatFecha(fecha) {
        if (!fecha) return '-';
        const date = new Date(fecha);
        if (isNaN(date.getTime())) return fecha;
        return date.toLocaleDateString('es-AR');
    }

    // Función para escapar HTML
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Función para ver observación
    function verObservacion(id, observacion, personal) {
        $('#modalObservacionLAR').text(`LAR #${id} - ${personal}`);
        $('#modalObservacionTexto').text(observacion);
        $('#modalObservacion').modal('show');
    }

    // Event listeners
    $('#btn-filtrar').on('click', function() {
        cargarLAR(1);
    });

    $('#btn-limpiar-filtros').on('click', function() {
        $('#anio_lar_filtro').val('');
        $('#fecha_desde').val('');
        $('#fecha_hasta').val('');
        $('#personal_id_filtro').val('').trigger('change');
        $('#dni_filtro').val('');
        $('#legajo_filtro').val('');
        $('#personal_filtro').val('');
        cargarLAR(1);
    });

    // Enter key en filtros
    $('#dni_filtro, #legajo_filtro, #personal_filtro').on('keypress', function(e) {
        if (e.which === 13) {
            cargarLAR(1);
        }
    });
</script>
@endsection
