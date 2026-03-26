@extends('layouts.main')

@section('content')
    @yield('content')
@endsection

@push('scripts')
    @yield('js')
@endpush
