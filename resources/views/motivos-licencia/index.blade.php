@extends('layouts.main')

@section('title', 'Motivos de Licencia | ZonaCapital')

@section('header-title', 'Gestión de Motivos de Licencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Motivos de Licencia</li>
@endsection

@section('content')
    <div class="row">
        <!-- Formulario -->
        <div class="col-md-5">
            <div class="card card-primary" id="cardFormulario">
                <div class="card-header">
                    <h3 class="card-title" id="formTitle">
                        <i class="fas fa-plus"></i> Nuevo Motivo
                    </h3>
                </div>
                <form id="formMotivo" method="POST" action="{{ route('motivos-licencia.store') }}">
                    @csrf
                    <input type="hidden" id="formMethod" name="_method" value="POST">
                    <input type="hidden" id="IdMotivoLicencia" name="IdMotivoLicencia">

                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="Motivo">Motivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('Motivo') is-invalid @enderror" id="Motivo"
                                name="Motivo" maxlength="105" required placeholder="Ej: Licencia por razones de salud">
                        </div>

                        <div class="form-group">
                            <label for="DiasMax">Días Máximos <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('DiasMax') is-invalid @enderror" id="DiasMax"
                                name="DiasMax" min="1" max="999" required placeholder="Ej: 30">
                        </div>

                        <div class="form-group">
                            <label for="ObservacionMot">Observación / Fundamento Legal</label>
                            <textarea class="form-control @error('ObservacionMot') is-invalid @enderror" id="ObservacionMot"
                                name="ObservacionMot" rows="3" maxlength="500"
                                placeholder="Ej: Art. 2° Decreto 683/89"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="ModuloId">Vincular a Módulo <span class="text-danger">*</span></label>
                            <select class="form-control @error('ModuloId') is-invalid @enderror" id="ModuloId"
                                name="ModuloId" required>
                                <option value="">-- Seleccione un módulo --</option>
                                @foreach($modulosDisponibles as $modulo)
                                    <option value="{{ $modulo->IdModulo }}">
                                        {{ $modulo->Label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                Este motivo solo estará disponible en el módulo seleccionado.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                <strong>Nota:</strong> Los motivos de licencia deben vincularse a un módulo específico:<br>
                                • <strong>Orden Médica:</strong> Licencias médicas (razones de salud, accidentes, etc.)<br>
                                • <strong>Licencias:</strong> Licencias administrativas (estudio, familiar, etc.)
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnLimpiar">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                        <button type="button" class="btn btn-warning" id="btnCancelar" style="display: none;">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Listado de Motivos
                    </h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('motivos-licencia.index') }}" class="form-inline">
                            <input type="text" name="search" class="form-control form-control-sm mr-2" 
                                placeholder="Buscar motivo..." value="{{ request('search') }}" style="width: 200px;">
                            <label class="mr-2">Módulo:</label>
                            <select name="modulo" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach($modulosDisponibles as $modulo)
                                    <option value="{{ $modulo->IdModulo }}" {{ request('modulo') == $modulo->IdModulo ? 'selected' : '' }}>
                                        {{ $modulo->Label }}
                                    </option>
                                @endforeach
                            </select>
                            @if(request('search'))
                                <a href="{{ route('motivos-licencia.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Motivo</th>
                                <th>Días Máx.</th>
                                <th>Módulo</th>
                                <th>Observación</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($motivos as $motivo)
                                <tr data-id="{{ $motivo->IdMotivoLicencia }}">
                                    <td>{{ $motivo->IdMotivoLicencia }}</td>
                                    <td>{{ $motivo->Motivo }}</td>
                                    <td>
                                        <span class="badge bg-dark text-white">{{ $motivo->DiasMax }} días</span>
                                    </td>
                                    <td>
                                        @if($motivo->modulo)
                                            <span class="badge bg-primary text-white">
                                                {{ $motivo->modulo->Label }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary text-white">General</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted" title="{{ $motivo->ObservacionMot }}">
                                            {{ Str::limit($motivo->ObservacionMot, 40) }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning btn-editar"
                                            data-id="{{ $motivo->IdMotivoLicencia }}" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('motivos-licencia.destroy', $motivo->IdMotivoLicencia) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('¿Está seguro de eliminar este motivo?')">
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
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>No hay motivos registrados</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $motivos->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formMotivo');
            const formTitle = document.getElementById('formTitle');
            const btnGuardar = document.getElementById('btnGuardar');
            const btnCancelar = document.getElementById('btnCancelar');
            const btnLimpiar = document.getElementById('btnLimpiar');
            const cardFormulario = document.getElementById('cardFormulario');

            // Botón editar
            document.querySelectorAll('.btn-editar').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;

                    // Obtener datos vía AJAX
                    fetch(`{{ url('motivos-licencia') }}/${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const motivo = data.data;

                                // Llenar formulario
                                document.getElementById('IdMotivoLicencia').value = motivo.IdMotivoLicencia;
                                document.getElementById('Motivo').value = motivo.Motivo;
                                document.getElementById('DiasMax').value = motivo.DiasMax;
                                document.getElementById('ObservacionMot').value = motivo.ObservacionMot || '';
                                document.getElementById('ModuloId').value = motivo.ModuloId || '';

                                // Cambiar a modo edición
                                form.action = `{{ url('motivos-licencia') }}/${id}`;
                                document.getElementById('formMethod').value = 'PUT';

                                formTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Motivo';
                                btnGuardar.innerHTML = '<i class="fas fa-save"></i> Actualizar';
                                btnCancelar.style.display = 'inline-block';

                                // Scroll al formulario
                                cardFormulario.scrollIntoView({ behavior: 'smooth' });
                                document.getElementById('Motivo').focus();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al cargar los datos');
                        });
                });
            });

            // Botón cancelar
            btnCancelar.addEventListener('click', function () {
                resetForm();
            });

            // Botón limpiar
            btnLimpiar.addEventListener('click', function () {
                resetForm();
            });

            function resetForm() {
                form.reset();
                document.getElementById('IdMotivoLicencia').value = '';
                document.getElementById('formMethod').value = 'POST';
                form.action = '{{ route('motivos-licencia.store') }}';

                formTitle.innerHTML = '<i class="fas fa-plus"></i> Nuevo Motivo';
                btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar';
                btnCancelar.style.display = 'none';
            }

            // Si hay errores de validación, mantener modo edición si es necesario
            @if($errors->any() && old('IdMotivoLicencia'))
                formTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Motivo';
                btnGuardar.innerHTML = '<i class="fas fa-save"></i> Actualizar';
                btnCancelar.style.display = 'inline-block';
            @endif
            });
    </script>
@endpush