@extends('layouts.main')

@section('title', 'Configuración | ZonaCapital')

@section('header-title', 'Configuración del Sistema')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Configuración</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Configuración del Sistema</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Nav tabs -->
            <ul class="nav nav-tabs mb-3" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ session('active_tab') != 'leyendas' && session('active_tab') != 'logo' ? 'active' : '' }}" 
                            id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <i class="fas fa-cog"></i> General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ session('active_tab') == 'leyendas' ? 'active' : '' }}" 
                            id="leyendas-tab" data-bs-toggle="tab" data-bs-target="#leyendas" type="button" role="tab">
                        <i class="fas fa-scroll"></i> Leyendas Anuales
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ session('active_tab') == 'logo' ? 'active' : '' }}" 
                            id="logo-tab" data-bs-toggle="tab" data-bs-target="#logo" type="button" role="tab">
                        <i class="fas fa-image"></i> Logo
                    </button>
                </li>
            </ul>

            <!-- Tab content -->
            <div class="tab-content" id="configTabsContent">
                <!-- Tab General -->
                <div class="tab-pane fade {{ session('active_tab') != 'leyendas' ? 'show active' : '' }}" id="general" role="tabpanel">
                    <form method="POST" action="{{ route('configuracion.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="require_password_migration" 
                                   name="require_password_migration" 
                                   value="1"
                                   @if($configuracion['require_password_migration']) checked @endif>
                            <label class="form-check-label" for="require_password_migration">
                                <strong>Forzar cambio de contraseña al iniciar sesión con Clave</strong>
                            </label>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <strong>Nota:</strong> Cuando esta opción está activada, los usuarios que ingresen con su <em>Clave</em> 
                                actual serán redirigidos para generar una nueva contraseña moderna y su <em>Clave</em> será eliminada.
                                Si está desactivada, podrán seguir usando su <em>Clave</em> sin cambios.
                            </small>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tab Leyendas Anuales -->
                <div class="tab-pane fade {{ session('active_tab') == 'leyendas' ? 'show active' : '' }}" id="leyendas" role="tabpanel">
                    <div class="row">
                        <!-- Formulario para agregar/editar -->
                        <div class="col-md-5">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0" id="leyendaFormTitle">
                                        <i class="fas fa-plus"></i> Nueva Leyenda
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('configuracion.leyenda.guardar') }}" id="leyendaForm">
                                        @csrf
                                        
                                        @if($errors->leyenda->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach($errors->leyenda->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label for="anio" class="form-label">Año <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('anio', 'leyenda') is-invalid @enderror" 
                                                   id="anio" name="anio" min="2000" max="2100" required
                                                   value="{{ old('anio', date('Y')) }}">
                                            <div class="form-text">Año al que corresponde la leyenda</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="leyenda" class="form-label">Leyenda <span class="text-danger">*</span></label>
                                            <textarea class="form-control @error('leyenda', 'leyenda') is-invalid @enderror" 
                                                      id="leyenda" name="leyenda" rows="4" maxlength="500" required
                                                      placeholder="Ej: 2026 - Año de la concientización...">{{ old('leyenda') }}</textarea>
                                            <div class="form-text">Máximo 500 caracteres. Esta leyenda aparecerá en los PDF de las LAR.</div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary" id="btnGuardarLeyenda">
                                                <i class="fas fa-save"></i> Guardar Leyenda
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" id="btnCancelarEdicion" style="display: none;">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de leyendas -->
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-list"></i> Leyendas Registradas
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 80px;">Año</th>
                                                    <th>Leyenda</th>
                                                    <th class="text-center" style="width: 120px;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($leyendas as $leyenda)
                                                    <tr>
                                                        <td class="align-middle">
                                                            <span class="badge bg-primary fs-6">{{ $leyenda->Anio ?? 'Sin año' }}</span>
                                                        </td>
                                                        <td class="align-middle">
                                                            {{ $leyenda->Leyenda }}
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-sm btn-warning btn-editar-leyenda" 
                                                                    data-id="{{ $leyenda->id }}" data-anio="{{ $leyenda->Anio ?? '' }}"
                                                                    data-leyenda="{{ $leyenda->Leyenda }}"
                                                                    title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form action="{{ route('configuracion.leyenda.eliminar', $leyenda->id) }}" 
                                                                  method="POST" class="d-inline"
                                                                  onsubmit="return confirm('¿Está seguro de eliminar la leyenda del año {{ $leyenda->Anio }}?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center py-4">
                                                            <div class="text-muted">
                                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                                <p>No hay leyendas registradas</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Información:</strong> Al imprimir una LAR, el sistema usará automáticamente 
                                    la leyenda correspondiente al año de la licencia. Si no existe una leyenda para ese año, 
                                    se usará la más reciente disponible.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Logo -->
                <div class="tab-pane fade {{ session('active_tab') == 'logo' ? 'show active' : '' }}" id="logo" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-image"></i> Configurar Logo
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('configuracion.logo.guardar') }}" enctype="multipart/form-data">
                                        @csrf
                                        
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">Seleccionar imagen</label>
                                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                            <div class="form-text">Formatos permitidos: JPEG, PNG, JPG, GIF, SVG. Tamaño máximo: 2MB</div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> Actualizar Logo
                                            </button>
                                        </div>
                                    </form>

                                    <hr>

                                    <form method="POST" action="{{ route('configuracion.logo.restaurar') }}">
                                        @csrf
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-outline-secondary">
                                                <i class="fas fa-undo"></i> Restaurar Logo por Defecto
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-eye"></i> Vista Previa
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    @if($logoExists)
                                        <img src="{{ asset($logoPath) }}" alt="Logo actual" class="img-fluid" style="max-height: 150px;">
                                        <div class="mt-2">
                                            <small class="text-muted">Ruta: {{ $logoPath }}</small>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            No se encontró el logo en: {{ $logoPath }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Información:</strong> Este logo aparecerá en los PDF de Órdenes Médicas, LAR y otros documentos del sistema.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Botones de editar leyenda
        document.querySelectorAll('.btn-editar-leyenda').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const anio = this.dataset.anio;
                const leyenda = this.dataset.leyenda;
                
                // Llenar formulario
                document.getElementById('anio').value = anio;
                document.getElementById('leyenda').value = leyenda;
                
                // Cambiar título
                document.getElementById('leyendaFormTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Leyenda';
                
                // Mostrar botón cancelar
                document.getElementById('btnCancelarEdicion').style.display = 'block';
                
                // Focus
                document.getElementById('leyenda').focus();
            });
        });

        // Botón cancelar edición
        document.getElementById('btnCancelarEdicion').addEventListener('click', function() {
            // Limpiar formulario
            document.getElementById('leyendaForm').reset();
            document.getElementById('anio').value = new Date().getFullYear();
            
            // Restaurar título
            document.getElementById('leyendaFormTitle').innerHTML = '<i class="fas fa-plus"></i> Nueva Leyenda';
            
            // Ocultar botón cancelar
            this.style.display = 'none';
        });
    });
</script>
@endpush
