@extends('layouts.main')

@section('title', 'Inicio | ZonaCapital')

@section('header-title', 'Inicio')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Inicio</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h1 class="display-4">ZONACAPITAL</h1>
                    <p class="lead">Bienvenido al sistema de gestión</p>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página de inicio cargada correctamente');
        });
    </script>
@endpush
