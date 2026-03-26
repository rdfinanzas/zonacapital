<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Feriados Simple</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
</head>
<body>
    <h1>Test de Feriados - JavaScript Simple</h1>
    <div id="resultado"></div>

    <!-- jQuery desde CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 desde CDN -->
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

    <!-- API Laravel -->
    <script src="{{ asset('js/api-laravel.js') }}"></script>

    <!-- Test JavaScript -->
    <script>
        $(document).ready(function() {
            console.log('=== TEST FERIADOS SIMPLE ===');
            console.log('jQuery:', typeof $ !== 'undefined');
            console.log('Swal:', typeof Swal !== 'undefined');
            console.log('apiLaravel:', typeof apiLaravel !== 'undefined');
            console.log('Routes:', window.feriadosRoutes);

            $('#resultado').html('<p>jQuery: ' + (typeof $ !== 'undefined' ? '✅' : '❌') + '</p>' +
                               '<p>SweetAlert2: ' + (typeof Swal !== 'undefined' ? '✅' : '❌') + '</p>' +
                               '<p>apiLaravel: ' + (typeof apiLaravel !== 'undefined' ? '✅' : '❌') + '</p>' +
                               '<p>Routes: ' + (window.feriadosRoutes ? '✅' : '❌') + '</p>');

            alert('Test JavaScript funcionando!');

            // Test básico de apiLaravel
            if (typeof apiLaravel !== 'undefined') {
                console.log('Probando apiLaravel con endpoint de test...');

                apiLaravel('/test-feriados-api', 'GET')
                    .then(response => {
                        console.log('Respuesta de API:', response);
                        $('#resultado').append('<p>✅ API funcionando: ' + JSON.stringify(response).substring(0, 100) + '...</p>');
                    })
                    .catch(error => {
                        console.error('Error en API:', error);
                        $('#resultado').append('<p>❌ Error en API: ' + error.message + '</p>');
                    });
            }
        });
    </script>
</body>
</html>
