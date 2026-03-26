<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feriados - Test Directo</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Gestión de Feriados</h1>
        <div class="alert alert-info" id="debug-info">Cargando JavaScript...</div>

        <!-- Formulario -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Nuevo Feriado</div>
                    <div class="card-body">
                        <form id="form_main">
                            <div class="mb-3">
                                <label for="feriado" class="form-label">Descripción:</label>
                                <input type="text" class="form-control" id="feriado" name="feriado" required>
                            </div>
                            <div class="mb-3">
                                <label for="f_fer" class="form-label">Fecha:</label>
                                <input type="text" class="form-control" id="f_fer" name="fecha" placeholder="DD/MM/YYYY" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <button type="button" id="btn_limpiar" class="btn btn-warning">Limpiar</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lista -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Lista de Feriados</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table_data">
                                    <tr><td colspan="3" class="text-center">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="page-selection"></div>
                        <div id="total_info" class="text-muted"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- Configuración Laravel -->
    <script>
        window.Laravel = {
            baseUrl: '{{ url('/') }}',
            csrfToken: '{{ csrf_token() }}'
        };

        window.feriadosRoutes = {
            listar: '{{ route('feriados.listar') }}',
            store: '{{ route('feriados.store') }}',
            update: '{{ route('feriados.update', ':id') }}',
            destroy: '{{ route('feriados.destroy', ':id') }}',
            show: '{{ route('feriados.show', ':id') }}'
        };

        window.feriadosPermisos = {
            crear: true,
            editar: true,
            eliminar: true,
            leer: true
        };
    </script>

    <!-- Test JavaScript -->
    <script>
        $(document).ready(function() {
            $('#debug-info').html('✅ jQuery cargado correctamente');
            console.log('JavaScript funcionando en página directa');

            // Test básico
            alert('JavaScript se ejecuta correctamente');
        });
    </script>

    <!-- API Laravel -->
    <script src="{{ asset('js/api-laravel.js') }}"></script>

    <!-- Feriados JS -->
    <script src="{{ asset('js/feriados.js') }}"></script>
</body>
</html>
