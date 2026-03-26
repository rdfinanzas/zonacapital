@extends('layouts.main')

@section('header-title', 'Informe Registro Trabajo (ETI/Influenza)')

@section('content')
<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-12 col-md-3">
                    <label>Desde</label>
                    <input type="text" id="f_desde" class="form-control" placeholder="dd/mm/aaaa" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label>Hasta</label>
                    <input type="text" id="f_hasta" class="form-control" placeholder="dd/mm/aaaa" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label>Usuario</label>
                    <select id="usuario_sel" class="form-control select2">
                        <option value="0">-USUARIO-</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->IdUsuario }}">{{ $u->Nombre }} {{ $u->Apellido }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label>Región</label>
                    <select id="region_sel" class="form-control select2">
                        <option value="">-REGIÓN-</option>
                        @foreach($regiones as $r)
                            <option value="{{ $r->IdRegion }}">{{ $r->Region }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-12 col-md-6">
                    <label>Efector</label>
                    <select id="efector_sel" class="form-control select2">
                        <option value="0">-EFECTOR-</option>
                        @foreach($servicios as $s)
                            <option data-region="{{ $s->Region_Id }}" value="{{ $s->idServicio }}">{{ $s->servicio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 col-md-6 d-flex align-items-end">
                    <button id="btn_buscar" class="btn btn-primary btn-block">Buscar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div id="map" style="height: 500px; width: 100%;"></div>
            <div class="mt-2" id="leyenda"></div>
        </div>
    </div>
</div>

<!-- Google Maps JS (Places) -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&language=es&region=AR"></script>
<script src="{{ asset('js/informe-registro-trabajo.js') }}"></script>
@endsection