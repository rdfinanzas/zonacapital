@extends('layouts.app')

@section('title', 'Disposiciones')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Disposiciones</h1>
            </div>
        </div>
    </div>
</section>

<x-disposiciones-component
    :is-modal="false"
    :show-stats="true"
    :permisos="$permisos" />
@endsection

@push('styles')
<style>
    .card-tools .input-group {
        margin-bottom: 0;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .form-text small {
        font-size: 0.875em;
    }

    #contador_caracteres {
        font-weight: bold;
    }

    .info-box {
        margin-bottom: 0;
    }
</style>
@endpush

@section('js')
<script>
    window.disposicionesPermisos = {
        crear: {{ $permisos['crear'] ? 'true' : 'false' }},
        editar: {{ $permisos['editar'] ? 'true' : 'false' }},
        eliminar: {{ $permisos['eliminar'] ? 'true' : 'false' }},
        leer: {{ $permisos['leer'] ? 'true' : 'false' }}
    };

    window.usuarioId = {{ session('usuario_id') ?: 'null' }};
    window.usuarioEsCreador = {{ $permisos['eliminar'] ? 'true' : 'false' }};
</script>

<script src="{{ asset('js/disposiciones.js') }}?v={{ time() }}"></script>
<script>
    DisposicionesModule.init({
        listar: '{{ route('disposiciones.listar') }}',
        store: '{{ route('disposiciones.store') }}',
        update: '{{ route('disposiciones.update', ':id') }}',
        destroy: '{{ route('disposiciones.destroy', ':id') }}',
        proximoNumero: '{{ route('disposiciones.proximo-numero') }}',
        estadisticas: '{{ route('disposiciones.estadisticas') }}'
    }, { isModal: false });
</script>
@endsection
