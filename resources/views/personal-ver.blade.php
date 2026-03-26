@extends('layouts.main')

@section('title', 'Ver Personal | ZonaCapital')

@section('header-title', 'Ver Personal')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('personal') }}">Personal</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ver Datos</li>
@endsection

@section('content')
    <!-- Hidden permissions inputs for JavaScript -->
    <input type="hidden" id="permiso_crear" value="{{ $permisos['crear'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_leer" value="{{ $permisos['leer'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_editar" value="{{ $permisos['editar'] ? 1 : 0 }}">
    <input type="hidden" id="permiso_eliminar" value="{{ $permisos['eliminar'] ? 1 : 0 }}">

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container-fluid">
        <!-- Header con foto y datos principales -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        Información del Personal
                    </h4>
                    <div class="btn-group">
                        @if ($permisos['editar'] ?? false)
                            <button type="button" id="btnEditarPersonal" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i> Editar
                            </button>
                        @endif
                        @if ($permisos['eliminar'] ?? false)
                            <button type="button" id="btnEliminarPersonal" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Eliminar
                            </button>
                        @endif
                        <a href="{{ route('personal') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Foto del empleado -->
                    <div class="col-md-3 text-center">
                        <div class="position-relative d-inline-block">
                            <img id="foto-empleado" src="/img/dummy.png" class="img-fluid rounded-circle shadow mb-3"
                                style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #dee2e6;">
                        </div>
                        <div class="mt-2">
                            <span class="badge fs-6" id="estado-empleado" @if($empleado) @if($empleado['estado'] == 1)
                                    class="badge bg-success fs-6">Activo
                                @elseif($empleado['estado'] == 2)
                                        class="badge bg-warning fs-6">Licencia
                                    @elseif($empleado['estado'] == 3)
                                        class="badge bg-danger fs-6">Baja
                                    @else
                                        class="badge bg-secondary fs-6">Inactivo
                                    @endif
                            @else
                                    class="badge bg-secondary fs-6">Sin datos
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Datos principales -->
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <h2 class="text-primary mb-1" id="nombre-completo">
                                    @if($empleado)
                                        {{ $empleado['apellido'] ?? 'Sin apellido' }}, {{ $empleado['nombre'] ?? 'Sin nombre' }}
                                    @else
                                        - (Sin datos de empleado)
                                    @endif
                                </h2>
                                <div class="mb-3">
                                    <span class="badge bg-info fs-6 me-2">Legajo: <span
                                            id="legajo">{{ $empleado['legajo'] ?? '-' }}</span></span>
                                    <span class="badge bg-secondary fs-6">DNI: <span
                                            id="dni">{{ $empleado['dni'] ?? '-' }}</span></span>
                                </div>

                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Sexo:</strong></div>
                                        <div class="col-8" id="sexo">
                                            @if($empleado)
                                                {{ $empleado['sexo'] == 1 ? 'Masculino' : 'Femenino' }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Fecha Nac.:</strong></div>
                                        <div class="col-8">
                                            <span id="fecha-nacimiento">{{ $empleado['fecha_nacimiento'] ?? '-' }}</span>
                                            <span class="text-muted">(<span id="edad">
                                                    @if($empleado && $empleado['fecha_nacimiento'])
                                                        @php
                                                            $partes = explode('/', $empleado['fecha_nacimiento']);
                                                            if (count($partes) === 3) {
                                                                $fechaNac = new DateTime($partes[2] . '-' . $partes[1] . '-' . $partes[0]);
                                                                $hoy = new DateTime();
                                                                $edad = $hoy->diff($fechaNac)->y;
                                                                echo $edad;
                                                            } else {
                                                                echo '-';
                                                            }
                                                        @endphp
                                                    @else
                                                        -
                                                    @endif
                                                </span> años)</span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Estado Civil:</strong></div>
                                        <div class="col-8" id="estado-civil">{{ $empleado['estado_civil'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>CUIT:</strong></div>
                                        <div class="col-8" id="cuit">{{ $empleado['cuit'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-secondary mb-3">Contacto</h5>
                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Email:</strong></div>
                                        <div class="col-8" id="email">{{ $empleado['email'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Teléfono:</strong></div>
                                        <div class="col-8" id="telefono">{{ $empleado['telefono'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Celular:</strong></div>
                                        <div class="col-8" id="celular">{{ $empleado['celular'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs para organizar la información -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="personalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="laboral-tab" data-bs-toggle="tab" data-bs-target="#laboral"
                            type="button" role="tab">
                            <i class="bi bi-briefcase me-1"></i> Información Laboral
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal"
                            type="button" role="tab">
                            <i class="bi bi-person me-1"></i> Datos Personales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="domicilio-tab" data-bs-toggle="tab" data-bs-target="#domicilio"
                            type="button" role="tab">
                            <i class="bi bi-house me-1"></i> Domicilio
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos"
                            type="button" role="tab">
                            <i class="bi bi-file-earmark me-1"></i> Documentos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial"
                            type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i> Historial
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="personalTabsContent">
                    <!-- Información Laboral -->
                    <div class="tab-pane fade show active" id="laboral" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Organización</h5>
                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Gerencia:</strong></div>
                                        <div class="col-8" id="gerencia">{{ $empleado['gerencia'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Departamento:</strong></div>
                                        <div class="col-8" id="departamento">{{ $empleado['departamento'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Servicio(s):</strong></div>
                                        <div class="col-8" id="servicio">
                                            @if(isset($empleado['servicios_asignados']) && count($empleado['servicios_asignados']) > 0)
                                                @foreach($empleado['servicios_asignados'] as $servicio)
                                                    <span class="badge bg-info me-1">{{ $servicio['nombre'] }}</span>
                                                @endforeach
                                            @else
                                                {{ $empleado['servicio'] ?? '-' }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Sector:</strong></div>
                                        <div class="col-8" id="sector">{{ $empleado['sector'] ?? '-' }}</div>
                                    </div>
                                </div>

                                <h5 class="text-primary mb-3 mt-4">Fechas</h5>
                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Fecha Alta:</strong></div>
                                        <div class="col-8" id="fecha-alta">{{ $empleado['fecha_alta'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Fecha Baja:</strong></div>
                                        <div class="col-8" id="fecha-baja">{{ $empleado['fecha_baja'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Fecha Adm. Pública:</strong></div>
                                        <div class="col-8" id="fecha-adm-publica">
                                            {{ $empleado['fecha_adm_publica'] ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Función y Cargo</h5>
                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Profesión:</strong></div>
                                        <div class="col-8" id="profesion">{{ $empleado['profesion'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Función:</strong></div>
                                        <div class="col-8" id="funcion">{{ $empleado['funcion'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Cargo:</strong></div>
                                        <div class="col-8" id="cargo">{{ $empleado['cargo'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Categoría:</strong></div>
                                        <div class="col-8" id="categoria">{{ $empleado['categoria'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Agrupamiento:</strong></div>
                                        <div class="col-8" id="agrupamiento">{{ $empleado['agrupamiento'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Certifica:</strong></div>
                                        <div class="col-8" id="certifica">{{ $empleado['certifica'] ?? '-' }}</div>
                                    </div>
                                </div>

                                <h5 class="text-primary mb-3 mt-4">Jornada Actual</h5>
                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Tipo:</strong></div>
                                        <div class="col-8" id="tipo-jornada">{{ $empleado['tipo_jornada'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Desde:</strong></div>
                                        <div class="col-8" id="fecha-jornada">
                                            @if($empleado && isset($empleado['jornadas']) && count($empleado['jornadas']) > 0)
                                                {{ $empleado['jornadas'][0]['fecha'] ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Opciones:</strong></div>
                                        <div class="col-8">
                                            @if($empleado && $empleado['doble_fs'])
                                                <span class="badge bg-info me-1">Doble FS</span>
                                            @endif
                                            @if($empleado && $empleado['fe'])
                                                <span class="badge bg-warning">FE</span>
                                            @endif
                                            @if($empleado && !$empleado['doble_fs'] && !$empleado['fe'])
                                                <span class="text-muted">Sin opciones especiales</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos Personales -->
                    <div class="tab-pane fade" id="personal" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Información Personal</h5>
                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Nacionalidad:</strong></div>
                                        <div class="col-8" id="nacionalidad">{{ $empleado['nacionalidad'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Instrucción:</strong></div>
                                        <div class="col-8" id="instruccion">{{ $empleado['instruccion'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Matrícula:</strong></div>
                                        <div class="col-8" id="matricula">{{ $empleado['matricula'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Nro. Contrato:</strong></div>
                                        <div class="col-8" id="nro-contrato">{{ $empleado['nro_contrato'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Observaciones</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <p class="mb-0" id="observaciones">
                                            {{ $empleado['observacion'] ?? 'Sin observaciones' }}
                                        </p>
                                    </div>
                                </div>

                                <div id="baja-info" class="mt-3" style="display: none;">
                                    <h5 class="text-danger mb-3">Información de Baja</h5>
                                    <div class="info-grid">
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Motivo:</strong></div>
                                            <div class="col-8" id="motivo-baja">-</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Descripción:</strong></div>
                                            <div class="col-8" id="descripcion-baja">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Domicilio -->
                    <div class="tab-pane fade" id="domicilio" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Dirección</h5>
                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Calle:</strong></div>
                                        <div class="col-8" id="calle-direccion">{{ $empleado['calle'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Número:</strong></div>
                                        <div class="col-8" id="numero-calle">{{ $empleado['calle_num'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Piso:</strong></div>
                                        <div class="col-8" id="piso">{{ $empleado['piso'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Departamento:</strong></div>
                                        <div class="col-8" id="departamento-dir">{{ $empleado['departamento_dir'] ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Barrio:</strong></div>
                                        <div class="col-8" id="barrio">{{ $empleado['barrio'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Ubicación</h5>
                                <div class="info-grid">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Provincia:</strong></div>
                                        <div class="col-8" id="provincia">ID: {{ $empleado['provincia'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Localidad:</strong></div>
                                        <div class="col-8" id="localidad">ID: {{ $empleado['localidad'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Código Postal:</strong></div>
                                        <div class="col-8" id="codigo-postal">{{ $empleado['cp'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Manzana:</strong></div>
                                        <div class="col-8" id="manzana">{{ $empleado['manzana'] ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Casa:</strong></div>
                                        <div class="col-8" id="casa">{{ $empleado['casa'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="tab-pane fade" id="documentos" role="tabpanel">
                        <div class="row" id="documentos-container">
                            <div class="col-12 text-center text-muted">
                                <i class="bi bi-file-earmark-x fs-1"></i>
                                <p>No hay documentos cargados</p>
                            </div>
                        </div>
                    </div>

                    <!-- Historial -->
                    <div class="tab-pane fade" id="historial" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Historial de Relaciones</h5>
                                <div id="historial-relaciones">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-clock-history fs-1"></i>
                                        <p>No hay historial de relaciones</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">
                                    Historial de Jornadas
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btnVerJornadas">
                                        <i class="bi bi-eye me-1"></i> Ver completo
                                    </button>
                                </h5>
                                <div id="historial-jornadas">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-calendar-event fs-1"></i>
                                        <p>No hay historial de jornadas</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">
                                    <i class="bi bi-building me-2"></i>Cambios Organizacionales
                                </h5>
                                <div id="historial-organizacional">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-building fs-1"></i>
                                        <p>No hay cambios organizacionales</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-info mb-3">
                                    <i class="bi bi-person me-2"></i>Cambios de Datos Personales
                                </h5>
                                <div id="historial-personal">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-person fs-1"></i>
                                        <p>No hay cambios de datos personales</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de historial de jornadas -->
    <div class="modal fade" id="histo_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title">Historial completo de jornadas</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Jornada</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_his">
                            <!-- Contenido dinámico de historial -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .info-grid .row {
            border-bottom: 1px solid #f0f0f0;
            padding: 8px 0;
        }

        .info-grid .row:last-child {
            border-bottom: none;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
        }

        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .document-card {
            transition: transform 0.2s;
        }

        .document-card:hover {
            transform: translateY(-2px);
        }
    </style>
@endsection

@push('scripts')
    <!-- Include necessary JavaScript files -->
    <script src="{{ asset('js/api-laravel.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log("hola")
        // Verificar que apiLaravel esté disponible
        if (typeof apiLaravel === 'undefined') {
            console.error('apiLaravel no está disponible');
            // Fallback usando fetch
            window.apiLaravel = function (url, method, data) {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                };

                if (method !== 'GET' && data) {
                    options.body = JSON.stringify(data);
                }

                return fetch(url, options).then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                });
            };
        }
        document.addEventListener('DOMContentLoaded', function () {
            // Variables globales
            let empleadoActual = null;
            console.log("hola")
            // Event listeners
            // Botón volver ahora es un enlace directo

            document.getElementById('btnEditarPersonal').addEventListener('click', function () {
                const empleadoId = '{{ $empleadoId }}';
                if (empleadoId && empleadoId !== '' && empleadoId !== 'undefined') {
                    // Redirigir a la página principal en modo edición
                    window.location.href = '{{ route("personal") }}' + '?editar=' + empleadoId;
                } else {
                    console.error('No hay ID de empleado disponible');
                    alert('Error: No se puede editar, ID de empleado no disponible');
                }
            });

            document.getElementById('btnEliminarPersonal').addEventListener('click', function () {
                if (empleadoActual) {
                    eliminarPersonal(empleadoActual.idEmpleado);
                }
            });

            document.getElementById('btnVerJornadas').addEventListener('click', function () {
                if (empleadoActual && empleadoActual.jornadas) {
                    mostrarJornadasCompletas(empleadoActual.jornadas);
                }
            });

            // Cargar datos del empleado - ahora vienen directamente desde el controlador
            console.log('=== DEBUG VISTA PERSONAL-VER ===');
            console.log('empleadoId variable:', '{{ $empleadoId }}');
            console.log('empleado isset:', {{ isset($empleado) ? 'true' : 'false' }});

            // Debug más detallado del empleado
            const empleadoFromBlade = @json($empleado ?? null);
            console.log('empleado value:', empleadoFromBlade);
            console.log('empleado type:', typeof empleadoFromBlade);
            console.log('empleado is null:', empleadoFromBlade === null);
            console.log('empleado is empty object:', empleadoFromBlade && Object.keys(empleadoFromBlade).length === 0);

            console.log('URL actual:', window.location.href);
            console.log('Referer:', document.referrer);

            // Debug de la condición blade
            console.log('=== DEBUG CONDICION BLADE ===');
            console.log('isset($empleado):', {{ isset($empleado) ? 'true' : 'false' }});
            console.log('$empleado truthy:', {{ $empleado ? 'true' : 'false' }});
            console.log('Condición completa:', {{ (isset($empleado) && $empleado) ? 'true' : 'false' }});

            @if(isset($empleado) && !empty($empleado) && is_array($empleado))
                console.log('=== ✅ DATOS DEL EMPLEADO RECIBIDOS DESDE CONTROLADOR ===');
                const empleadoData = @json($empleado);
                console.log('Empleado data completo:', empleadoData);
                console.log('Nombre empleado:', empleadoData.apellido + ', ' + empleadoData.nombre);

                // Cargar datos directamente sin AJAX
                cargarDatosEmpleadoDirecto(empleadoData);
            @else
                console.log('=== NO HAY DATOS DE EMPLEADO ===');
                console.log('empleado variable:', @json($empleado ?? null));
                console.log('empleadoId variable:', '{{ $empleadoId }}');

                const empleadoId = '{{ $empleadoId }}';
                const nombreElement = document.getElementById('nombre-completo');

                if (empleadoId && empleadoId !== '' && empleadoId !== 'undefined') {
                    console.log('⚠️ Empleado ID válido pero sin datos. Mostrando error...');
                    nombreElement.textContent = `❌ Empleado ID ${empleadoId} no encontrado`;

                    // Mostrar mensaje de error en la página
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    errorDiv.innerHTML = `
                                <h4>⚠️ Empleado no encontrado</h4>
                                <p>El empleado con ID <strong>${empleadoId}</strong> no existe en la base de datos.</p>
                                <p>Por favor, verifique el ID y vuelva a intentar.</p>
                                <a href="{{ route('personal') }}" class="btn btn-primary">🔙 Volver a la lista</a>
                            `;

                    // Insertar después del header
                    const cardBody = document.querySelector('.card-body');
                    if (cardBody) {
                        cardBody.innerHTML = '';
                        cardBody.appendChild(errorDiv);
                    }

                    console.log('⚠️ Fallback: Intentando cargar por AJAX...');
                    cargarDatosEmpleado(empleadoId);
                } else {
                    console.error('❌ ID de empleado no válido:', empleadoId);
                    nombreElement.textContent = '❌ Error: ID de empleado no válido';
                }
            @endif

            // FALLBACK: Intentar cargar SIEMPRE, independientemente de la condición
            console.log('=== FALLBACK: INTENTANDO CARGAR DATOS SIEMPRE ===');
            const empleadoFallback = @json($empleado ?? null);
            if (empleadoFallback && typeof empleadoFallback === 'object' && empleadoFallback.idEmpleado) {
                console.log('✅ FALLBACK: Datos encontrados, cargando...', empleadoFallback);
                cargarDatosEmpleadoDirecto(empleadoFallback);
            } else {
                console.log('❌ FALLBACK: No se encontraron datos válidos', empleadoFallback);
            }

            /**
             * Calcular edad a partir de fecha de nacimiento
             */
            function calcularEdad(fechaNac) {
                if (!fechaNac) return '-';

                const partes = fechaNac.split('/');
                if (partes.length !== 3) return '-';

                const fechaNacimiento = new Date(partes[2], partes[1] - 1, partes[0]);
                const hoy = new Date();

                let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
                const mes = hoy.getMonth() - fechaNacimiento.getMonth();

                if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                    edad--;
                }

                return edad;
            }

            /**
             * Cargar datos del empleado directamente desde el controlador
             */
            function cargarDatosEmpleadoDirecto(empleado) {
                console.log('=== CARGANDO DATOS DIRECTAMENTE ===');
                console.log('Empleado recibido:', empleado);

                empleadoActual = empleado;

                // Helper function para setear texto de elemento de forma segura
                function setElementText(id, text) {
                    const element = document.getElementById(id);
                    if (element) {
                        element.textContent = text;
                        console.log(`✅ ${id}: ${text}`);
                    } else {
                        console.warn(`❌ Elemento no encontrado: ${id}`);
                    }
                }

                // Llenar datos básicos
                setElementText('nombre-completo', `${empleado.apellido}, ${empleado.nombre}`);
                setElementText('legajo', empleado.legajo || '-');
                setElementText('dni', empleado.dni || '-');
                setElementText('sexo', empleado.sexo == 1 ? 'Masculino' : 'Femenino');

                // Estado del empleado
                const estadoBadge = document.getElementById('estado-empleado');
                if (estadoBadge) {
                    if (empleado.estado == 1) {
                        estadoBadge.textContent = 'Activo';
                        estadoBadge.className = 'badge bg-success fs-6';
                    } else if (empleado.estado == 2) {
                        estadoBadge.textContent = 'Licencia';
                        estadoBadge.className = 'badge bg-danger fs-6';
                    } else {
                        estadoBadge.textContent = 'Inactivo';
                        estadoBadge.className = 'badge bg-warning fs-6';
                    }
                    console.log('✅ Estado badge actualizado:', estadoBadge.textContent);
                }

                // Foto del empleado
                if (empleado.foto) {
                    const fotoElement = document.getElementById('foto-empleado');
                    if (fotoElement) {
                        fotoElement.src = `/storage/empleados/fotos/${empleado.foto}.png`;
                        console.log('✅ Foto cargada:', empleado.foto);
                    }
                }

                // Edad
                const edad = calcularEdad(empleado.fecha_nacimiento);
                setElementText('edad', edad);

                // Datos personales
                setElementText('observaciones', empleado.observacion || 'Sin observaciones');
                setElementText('matricula', empleado.matricula || '-');
                setElementText('nro-contrato', empleado.nro_contrato || '-');

                // Información de baja
                if (empleado.estado == 3) {
                    const bajaInfo = document.getElementById('baja-info');
                    if (bajaInfo) {
                        bajaInfo.style.display = 'block';
                    }
                    setElementText('descripcion-baja', empleado.descripcion_baja || '-');
                }

                // Domicilio
                setElementText('calle-direccion', empleado.calle || '-');
                setElementText('numero-calle', empleado.calle_num || '-');
                setElementText('piso', empleado.piso || '-');
                setElementText('departamento-dir', empleado.departamento_dir || '-');
                setElementText('barrio', empleado.barrio || '-');

                // Contacto
                setElementText('telefono', empleado.telefono || '-');
                setElementText('celular', empleado.celular || '-');
                setElementText('email', empleado.email || '-');

                // Fechas
                setElementText('fecha-nacimiento', empleado.fecha_nacimiento || '-');
                setElementText('fecha-alta', empleado.fecha_alta || '-');
                setElementText('fecha-baja', empleado.fecha_baja || '-');
                setElementText('fecha-adm-publica', empleado.fecha_adm_publica || '-');

                // Otros datos
                setElementText('nacionalidad', empleado.nacionalidad || '-');
                setElementText('cp', empleado.cp || '-');
                setElementText('cuit', empleado.cuit || '-');

                // Doble FS y FE badges
                const dobleFs = document.getElementById('doble-fs');
                if (dobleFs && empleado.doble_fs) {
                    dobleFs.style.display = 'inline-block';
                }

                const fe = document.getElementById('fe');
                if (fe && empleado.fe) {
                    fe.style.display = 'inline-block';
                }

                // Actualizar el título de la página
                document.title = `Ver Personal: ${empleado.apellido}, ${empleado.nombre} | ZonaCapital`;

                // Mostrar historiales
                if (empleado.historial_relaciones) {
                    mostrarHistorialRelaciones(empleado.historial_relaciones);
                }
                if (empleado.jornadas) {
                    mostrarHistorialJornadas(empleado.jornadas.slice(0, 5));
                }
                if (empleado.historial_modificaciones) {
                    mostrarHistorialModificaciones(empleado.historial_modificaciones);
                }

                console.log('✅ Datos cargados directamente con éxito');
            }

            /**
             * Cargar datos del empleado por AJAX (fallback)
             */
            function cargarDatosEmpleado(id) {
                console.log('=== INICIANDO CARGA DE EMPLEADO ===');
                console.log('ID recibido:', id);
                console.log('Tipo de ID:', typeof id);

                if (typeof apiLaravel === 'undefined') {
                    console.error('apiLaravel no está disponible');
                    alert('Error: La función apiLaravel no está disponible. Verifique que el archivo api-laravel.js esté cargado.');
                    return;
                }

                console.log('apiLaravel está disponible');

                // Verificar que el elemento existe
                const nombreElement = document.getElementById('nombre-completo');
                if (!nombreElement) {
                    console.error('Elemento nombre-completo no encontrado');
                    alert('Error: Elemento nombre-completo no encontrado en el DOM');
                    return;
                }

                // Mostrar loading
                nombreElement.textContent = 'Cargando...';

                const url = `/personal/${id}`;
                console.log('URL de petición:', url);

                // Test directo con fetch primero
                console.log('=== PROBANDO CON FETCH DIRECTO ===');
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        console.log('Fetch - Status:', response.status);
                        console.log('Fetch - OK:', response.ok);
                        return response.text();
                    })
                    .then(data => {
                        console.log('Fetch - Raw response:', data);
                    })
                    .catch(error => {
                        console.error('Fetch - Error:', error);
                    });

                apiLaravel(url, 'GET')
                    .then(response => {
                        console.log('=== RESPUESTA RECIBIDA ===');
                        console.log('Response completo:', response);
                        console.log('Tipo de response:', typeof response);

                        if (!response) {
                            console.error('Response es null o undefined');
                            alert('Error: No se recibió respuesta del servidor');
                            return;
                        }

                        if (!response.success) {
                            console.error('Response.success es false:', response);
                            alert('Error al cargar los datos del personal: ' + (response.message || 'Error desconocido'));
                            return;
                        }

                        if (!response.data) {
                            console.error('Response.data no existe:', response);
                            alert('Error: No se recibieron datos del empleado');
                            return;
                        }

                        console.log('Datos del empleado:', response.data);
                        const empleado = response.data;
                        empleadoActual = empleado;

                        // Llenar datos básicos
                        document.getElementById('nombre-completo').textContent = `${empleado.apellido}, ${empleado.nombre}`;
                        document.getElementById('legajo').textContent = empleado.legajo || '-';
                        document.getElementById('dni').textContent = empleado.dni || '-';
                        document.getElementById('sexo').textContent = empleado.sexo == 1 ? 'Masculino' : 'Femenino';
                        document.getElementById('fecha-nacimiento').textContent = empleado.fecha_nacimiento || '-';
                        document.getElementById('cuit').textContent = empleado.cuit || '-';
                        document.getElementById('email').textContent = empleado.email || '-';
                        document.getElementById('telefono').textContent = empleado.telefono || '-';
                        document.getElementById('celular').textContent = empleado.celular || '-';

                        // Calcular y mostrar edad
                        if (empleado.fecha_nacimiento) {
                            const edad = calcularEdad(empleado.fecha_nacimiento);
                            document.getElementById('edad').textContent = edad;
                        }

                        // Estado del empleado
                        const estadoBadge = document.getElementById('estado-empleado');
                        if (empleado.estado == 1) {
                            estadoBadge.textContent = 'Activo';
                            estadoBadge.className = 'badge bg-success fs-6';
                        } else if (empleado.estado == 3) {
                            estadoBadge.textContent = 'Baja';
                            estadoBadge.className = 'badge bg-danger fs-6';
                        } else {
                            estadoBadge.textContent = 'Inactivo';
                            estadoBadge.className = 'badge bg-warning fs-6';
                        }

                        // Foto del empleado
                        if (empleado.foto) {
                            document.getElementById('foto-empleado').src = `/storage/empleados/fotos/${empleado.foto}.png`;
                        }

                        // Información laboral - se cargará con los selectores
                        llenarDatosLaborales(empleado);

                        // Datos personales
                        document.getElementById('observaciones').textContent = empleado.observacion || 'Sin observaciones';
                        document.getElementById('matricula').textContent = empleado.matricula || '-';
                        document.getElementById('nro-contrato').textContent = empleado.nro_contrato || '-';

                        // Información de baja
                        if (empleado.estado == 3) {
                            document.getElementById('baja-info').style.display = 'block';
                            document.getElementById('descripcion-baja').textContent = empleado.descripcion_baja || '-';
                        }

                        // Domicilio
                        document.getElementById('calle-direccion').textContent = empleado.calle || '-';
                        document.getElementById('numero-calle').textContent = empleado.calle_num || '-';
                        document.getElementById('piso').textContent = empleado.piso || '-';
                        document.getElementById('departamento-dir').textContent = empleado.departamento_dir || '-';
                        document.getElementById('barrio').textContent = empleado.barrio || '-';
                        document.getElementById('codigo-postal').textContent = empleado.cp || '-';
                        document.getElementById('manzana').textContent = empleado.manzana || '-';
                        document.getElementById('casa').textContent = empleado.casa || '-';

                        // Jornada actual
                        if (empleado.jornadas && empleado.jornadas.length > 0) {
                            const jornadaActual = empleado.jornadas[0];
                            document.getElementById('tipo-jornada').textContent = jornadaActual.jornada_nombre || '-';
                            document.getElementById('fecha-jornada').textContent = jornadaActual.fecha || '-';
                        }

                        // Opciones de jornada
                        if (empleado.doble_fs == 1) {
                            document.getElementById('doble-fs').style.display = 'inline-block';
                        }
                        if (empleado.fe == 1) {
                            document.getElementById('fe').style.display = 'inline-block';
                        }

                        // Documentos
                        mostrarDocumentos(empleado.documentos || []);

                        // Historial de relaciones
                        mostrarHistorialRelaciones(empleado.historial_relaciones || []);

                        // Historial de jornadas (solo las últimas 5)
                        mostrarHistorialJornadas(empleado.jornadas ? empleado.jornadas.slice(0, 5) : []);

                        // Historial de modificaciones (cambios de cargo, jefes, etc.)
                        mostrarHistorialModificaciones(empleado.historial_modificaciones || []);

                        // Asegurar que el título de la página también se actualice
                        document.title = `Ver Personal: ${empleado.apellido}, ${empleado.nombre} | ZonaCapital`;
                    })
                    .catch(error => {
                        console.error('=== ERROR EN PETICIÓN AJAX ===');
                        console.error('Error completo:', error);
                        console.error('Tipo de error:', typeof error);

                        const nombreElement = document.getElementById('nombre-completo');
                        nombreElement.textContent = `❌ Error: Empleado ${id} no encontrado`;

                        // Mostrar mensaje de error más amigable
                        const cardBody = document.querySelector('.card-body');
                        if (cardBody) {
                            cardBody.innerHTML = `
                                <div class="alert alert-danger">
                                    <h4>⚠️ Error al cargar el empleado</h4>
                                    <p>No se pudo cargar la información del empleado con ID <strong>${id}</strong>.</p>
                                    <p><strong>Posibles causas:</strong></p>
                                    <ul>
                                        <li>El empleado no existe en la base de datos</li>
                                        <li>No tiene permisos para ver este empleado</li>
                                        <li>Error de conexión con el servidor</li>
                                    </ul>
                                    <a href="{{ route('personal') }}" class="btn btn-primary">🔙 Volver a la lista</a>
                                </div>
                            `;
                        }
                    });
            }

            /**
             * Llenar datos laborales con nombres descriptivos
             */
            function llenarDatosLaborales(empleado) {
                // Cargar selectores para obtener nombres descriptivos
                apiLaravel('/personal/selectores-iniciales', 'GET')
                    .then(response => {
                        const data = response.data;

                        // Buscar nombres descriptivos
                        const profesion = data.profesiones.find(p => p.idprofesion == empleado.profesion);
                        const funcion = data.funciones.find(f => f.IdFuncion == empleado.funcion);
                        const categoria = data.categorias.find(c => c.idcategoria == empleado.categoria);
                        const agrupamiento = data.agrupamientos.find(a => a.idAgrupamiento == empleado.agrupamiento);
                        const cargo = data.cargos.find(c => c.idCargo == empleado.cargo);
                        const certifica = data.empleados_con_cargo.find(e => e.idEmpleado == empleado.certifica);
                        const estadoCivil = data.estados_civiles.find(e => e.idEstadoCivil == empleado.estado_civil);
                        const nacionalidad = data.paises.find(p => p.IdPais == empleado.nacionalidad);
                        const instruccion = data.instrucciones.find(i => i.idInstruccion == empleado.instruccion);
                        const motivoBaja = data.motivos_baja.find(m => m.IdMotivoBaja == empleado.motivo_baja);

                        // Asignar valores
                        document.getElementById('profesion').textContent = profesion ? profesion.profesion : '-';
                        document.getElementById('funcion').textContent = funcion ? funcion.Funcion : '-';
                        document.getElementById('categoria').textContent = categoria ? categoria.categoria : '-';
                        document.getElementById('agrupamiento').textContent = agrupamiento ? agrupamiento.agrupamiento : '-';
                        document.getElementById('cargo').textContent = cargo ? cargo.cargo : '-';
                        document.getElementById('certifica').textContent = certifica ? `${certifica.Apellido}, ${certifica.Nombre} (${certifica.Legajo})` : '-';
                        document.getElementById('estado-civil').textContent = estadoCivil ? estadoCivil.EstadoCivil : '-';
                        document.getElementById('nacionalidad').textContent = nacionalidad ? nacionalidad.Pais : '-';
                        document.getElementById('instruccion').textContent = instruccion ? instruccion.instruccion : '-';
                        if (motivoBaja) {
                            document.getElementById('motivo-baja').textContent = motivoBaja.MotivoBaja;
                        }
                    })
                    .catch(error => {
                        console.error('Error cargando selectores:', error);
                    });

                // Cargar jerarquía organizacional
                cargarJerarquiaOrganizacional(empleado);

                // Fechas
                document.getElementById('fecha-alta').textContent = empleado.fecha_alta || '-';
                document.getElementById('fecha-baja').textContent = empleado.fecha_baja || '-';
                document.getElementById('fecha-adm-publica').textContent = empleado.fecha_adm_publica || '-';
            }

            /**
             * Cargar jerarquía organizacional
             */
            function cargarJerarquiaOrganizacional(empleado) {
                // Gerencia
                if (empleado.gerencia) {
                    apiLaravel('/personal/selectores-iniciales', 'GET')
                        .then(response => {
                            const gerencia = response.data.gerencias.find(g => g.idGerencia == empleado.gerencia);
                            document.getElementById('gerencia').textContent = gerencia ? gerencia.Gerencia : '-';

                            // Departamento
                            if (empleado.departamento) {
                                return apiLaravel(`/personal/departamentos?gerencia_id=${empleado.gerencia}`, 'GET');
                            }
                        })
                        .then(response => {
                            if (response && empleado.departamento) {
                                const departamento = response.data.find(d => d.idDepartamento == empleado.departamento);
                                document.getElementById('departamento').textContent = departamento ? departamento.departamento : '-';

                                // Servicio
                                if (empleado.servicio) {
                                    return apiLaravel(`/personal/servicios?departamento_id=${empleado.departamento}`, 'GET');
                                }
                            }
                        })
                        .then(response => {
                            if (response && empleado.servicio) {
                                const servicio = response.data.find(s => s.idServicio == empleado.servicio);
                                document.getElementById('servicio').textContent = servicio ? servicio.servicio : '-';

                                // Sector
                                if (empleado.sector) {
                                    return apiLaravel(`/personal/sectores?servicio_id=${empleado.servicio}`, 'GET');
                                }
                            }
                        })
                        .then(response => {
                            if (response && empleado.sector) {
                                const sector = response.data.find(s => s.IdSector == empleado.sector);
                                document.getElementById('sector').textContent = sector ? sector.Sector : '-';
                            }
                        })
                        .catch(error => {
                            console.error('Error cargando jerarquía:', error);
                        });
                }

                // Provincia y localidad
                if (empleado.provincia) {
                    apiLaravel('/personal/selectores-iniciales', 'GET')
                        .then(response => {
                            const provincia = response.data.provincias ? response.data.provincias.find(p => p.IdProvincia == empleado.provincia) : null;
                            document.getElementById('provincia').textContent = provincia ? provincia.Provincia : '-';

                            if (empleado.localidad) {
                                return apiLaravel(`/personal/localidades?provincia_id=${empleado.provincia}`, 'GET');
                            }
                        })
                        .then(response => {
                            if (response && empleado.localidad) {
                                const localidad = response.data.find(l => l.IdLocalidad == empleado.localidad);
                                document.getElementById('localidad').textContent = localidad ? localidad.Localidad : '-';
                            }
                        })
                        .catch(error => {
                            console.error('Error cargando ubicación:', error);
                        });
                }
            }

            /**
             * Mostrar documentos
             */
            function mostrarDocumentos(documentos) {
                const container = document.getElementById('documentos-container');

                if (documentos.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center text-muted">
                            <i class="bi bi-file-earmark-x fs-1"></i>
                            <p>No hay documentos cargados</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                documentos.forEach(doc => {
                    html += `
                        <div class="col-md-4 mb-3">
                            <div class="card document-card h-100">
                                <div class="card-img-top text-center p-3" style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                    ${doc.imagen ?
                            `<img src="/storage/empleados/documentos/${doc.imagen}.png" class="img-fluid" style="max-height: 150px; cursor: pointer;" onclick="verDocumento('${doc.imagen}')">` :
                            `<i class="bi bi-file-earmark fs-1 text-muted"></i>`
                        }
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title">${doc.nombre || 'Documento sin nombre'}</h6>
                                    <small class="text-muted">${doc.fecha || 'Sin fecha'}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            }

            /**
             * Mostrar historial de relaciones
             */
            function mostrarHistorialRelaciones(relaciones) {
                const container = document.getElementById('historial-relaciones');

                if (relaciones.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-muted">
                            <i class="bi bi-clock-history fs-1"></i>
                            <p>No hay historial de relaciones</p>
                        </div>
                    `;
                    return;
                }

                let html = '<div class="timeline">';
                relaciones.forEach(rel => {
                    html += `
                        <div class="card mb-2">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between">
                                    <strong>${rel.relacion_nombre}</strong>
                                    <small class="text-muted">${rel.desde} - ${rel.hasta || 'Actual'}</small>
                                </div>
                                ${rel.observacion ? `<small class="text-muted">${rel.observacion}</small>` : ''}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';

                container.innerHTML = html;
            }

            /**
             * Mostrar historial de jornadas (resumido)
             */
            function mostrarHistorialJornadas(jornadas) {
                const container = document.getElementById('historial-jornadas');

                if (jornadas.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-muted">
                            <i class="bi bi-calendar-event fs-1"></i>
                            <p>No hay historial de jornadas</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                jornadas.forEach(jor => {
                    html += `
                        <div class="card mb-2">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between">
                                    <strong>${jor.jornada_nombre}</strong>
                                    <small class="text-muted">${jor.fecha}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            }

            /**
             * Mostrar historial de modificaciones separadas por tipo (organizacional/personal)
             */
            function mostrarHistorialModificaciones(modificaciones) {
                const containerOrganizacional = document.getElementById('historial-organizacional');
                const containerPersonal = document.getElementById('historial-personal');

                // Filtrar cambios por tipo
                const cambiosOrganizacionales = modificaciones.filter(mod => mod.tipo_cambio === 'organizacional');
                const cambiosPersonales = modificaciones.filter(mod => mod.tipo_cambio === 'personal');

                // Renderizar cambios organizacionales
                if (cambiosOrganizacionales.length === 0) {
                    containerOrganizacional.innerHTML = `
                        <div class="text-center text-muted">
                            <i class="bi bi-building fs-1"></i>
                            <p>No hay cambios organizacionales</p>
                        </div>
                    `;
                } else {
                    let html = '';
                    cambiosOrganizacionales.forEach(mod => {
                        html += `
                            <div class="card mb-2 border-primary">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="mb-1">${mod.modificaciones}</div>
                                            ${mod.modificador ? `<small class="text-muted"><i class="bi bi-person me-1"></i>Por: ${mod.modificador}</small>` : ''}
                                        </div>
                                        <small class="text-muted ms-2">${mod.fecha}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    containerOrganizacional.innerHTML = html;
                }

                // Renderizar cambios personales
                if (cambiosPersonales.length === 0) {
                    containerPersonal.innerHTML = `
                        <div class="text-center text-muted">
                            <i class="bi bi-person fs-1"></i>
                            <p>No hay cambios de datos personales</p>
                        </div>
                    `;
                } else {
                    let html = '';
                    cambiosPersonales.forEach(mod => {
                        html += `
                            <div class="card mb-2 border-info">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="mb-1">${mod.modificaciones}</div>
                                            ${mod.modificador ? `<small class="text-muted"><i class="bi bi-person me-1"></i>Por: ${mod.modificador}</small>` : ''}
                                        </div>
                                        <small class="text-muted ms-2">${mod.fecha}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    containerPersonal.innerHTML = html;
                }
            }

            /**
             * Mostrar todas las jornadas en modal
             */
            function mostrarJornadasCompletas(jornadas) {
                let html = '';

                if (jornadas.length === 0) {
                    html = '<tr><td colspan="2" class="text-center">No hay jornadas registradas</td></tr>';
                } else {
                    jornadas.forEach(jornada => {
                        html += `
                            <tr>
                                <td>${jornada.jornada_nombre}</td>
                                <td>${jornada.fecha}</td>
                            </tr>
                        `;
                    });
                }

                document.getElementById('tabla_his').innerHTML = html;

                // Mostrar modal usando Bootstrap 5
                const modal = new bootstrap.Modal(document.getElementById('histo_modal'));
                modal.show();
            }

            /**
             * Calcular edad
             */
            function calcularEdad(fechaNac) {
                if (!fechaNac) return '-';

                const partes = fechaNac.split('/');
                if (partes.length !== 3) return '-';

                const fechaNacimiento = new Date(partes[2], partes[1] - 1, partes[0]);
                const hoy = new Date();

                let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
                const mes = hoy.getMonth() - fechaNacimiento.getMonth();

                if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                    edad--;
                }

                return edad;
            }

            /**
             * Ver documento en tamaño completo
             */
            window.verDocumento = function (imagen) {
                // Crear modal para mostrar imagen
                const modalHtml = `
                    <div class="modal fade" id="modalDocumento" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Documento</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="/storage/empleados/documentos/${imagen}.png" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.body.insertAdjacentHTML('beforeend', modalHtml);
                const modal = new bootstrap.Modal(document.getElementById('modalDocumento'));
                modal.show();

                // Limpiar modal al cerrarse
                document.getElementById('modalDocumento').addEventListener('hidden.bs.modal', function () {
                    this.remove();
                });
            };

            /**
             * Eliminar personal
             */
            function eliminarPersonal(id) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Está seguro?',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            apiLaravel(`/personal/${id}`, 'DELETE')
                                .then(respuesta => {
                                    if (respuesta.success) {
                                        Swal.fire('Eliminado', respuesta.message || 'Registro eliminado correctamente', 'success')
                                            .then(() => {
                                                window.location.href = '{{ route("personal") }}';
                                            });
                                    } else {
                                        Swal.fire('Error', respuesta.message || 'Error al eliminar el registro', 'error');
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Error', 'Error al eliminar el registro: ' + error, 'error');
                                });
                        }
                    });
                } else {
                    if (confirm('¿Está seguro de que desea eliminar este registro?')) {
                        apiLaravel(`/personal/${id}`, 'DELETE')
                            .then(respuesta => {
                                if (respuesta.success) {
                                    alert(respuesta.message || 'Registro eliminado correctamente');
                                    window.location.href = '{{ route("personal") }}';
                                } else {
                                    alert(respuesta.message || 'Error al eliminar el registro');
                                }
                            })
                            .catch(error => {
                                alert('Error al eliminar el registro: ' + error);
                            });
                    }
                }
            }
        });
    </script>
@endpush