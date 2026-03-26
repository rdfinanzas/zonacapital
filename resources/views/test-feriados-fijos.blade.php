<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Modal Feriados Fijos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Test Modal Feriados Fijos</h2>

        <button type="button" class="btn btn-info" id="btn_test_modal">
            <i class="fas fa-calendar-plus"></i> Test Modal Feriados Fijos
        </button>

        <div class="mt-3">
            <h4>Debug Info:</h4>
            <div id="debug-info"></div>
        </div>

        <div class="mt-3">
            <h4>Test Directo API:</h4>
            <button type="button" class="btn btn-primary" id="btn_test_api">Test API Feriados Fijos</button>
            <div id="api-result" class="mt-2"></div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal_test" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title">Test Feriados Fijos</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_test">
                                <tr>
                                    <td colspan="4" class="text-center">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/api-laravel.js') }}"></script>

    <script>
        // Configurar rutas
        window.feriadosRoutes = {
            feriadosFijos: '/test-feriados-fijos'
        };

        $(document).ready(function() {
            console.log('Test page loaded');

            // Debug info
            $('#debug-info').html(`
                <p><strong>Rutas:</strong> ${JSON.stringify(window.feriadosRoutes)}</p>
                <p><strong>jQuery:</strong> ${typeof $ !== 'undefined' ? 'OK' : 'NO'}</p>
                <p><strong>apiLaravel:</strong> ${typeof apiLaravel !== 'undefined' ? 'OK' : 'NO'}</p>
            `);

            // Test modal
            $('#btn_test_modal').click(function() {
                $('#modal_test').modal('show');
            });

            // Cargar datos cuando se abre el modal
            $('#modal_test').on('shown.bs.modal', function() {
                cargarDatosTest();
            });

            // Test API directo
            $('#btn_test_api').click(function() {
                testApiDirecto();
            });
        });

        function cargarDatosTest() {
            console.log('Cargando datos test...');

            apiLaravel(window.feriadosRoutes.feriadosFijos, 'GET')
                .then(response => {
                    console.log('Respuesta:', response);

                    if (response.success) {
                        let html = '';
                        response.data.forEach(item => {
                            html += `
                                <tr>
                                    <td>${item.id}</td>
                                    <td>${item.nombre}</td>
                                    <td>${item.dia}/${item.mes}</td>
                                    <td>${item.activo ? 'Activo' : 'Inactivo'}</td>
                                </tr>
                            `;
                        });
                        $('#tbody_test').html(html);
                    } else {
                        $('#tbody_test').html('<tr><td colspan="4" class="text-danger">Error: ' + response.message + '</td></tr>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    $('#tbody_test').html('<tr><td colspan="4" class="text-danger">Error de conexión</td></tr>');
                });
        }

        function testApiDirecto() {
            $('#api-result').html('<i class="fas fa-spinner fa-spin"></i> Cargando...');

            fetch('/test-feriados-fijos')
                .then(response => response.json())
                .then(data => {
                    $('#api-result').html(`
                        <div class="alert alert-success">
                            <strong>Éxito:</strong> ${data.data.length} feriados encontrados
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `);
                })
                .catch(error => {
                    $('#api-result').html(`
                        <div class="alert alert-danger">
                            <strong>Error:</strong> ${error.message}
                        </div>
                    `);
                });
        }
    </script>
</body>
</html>