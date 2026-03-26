<?php

// Ruta de prueba temporal para debuggear UPDATE de registros dengue
use App\Http\Controllers\RegistrosDengueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/debug-update-dengue/{id}', function($id, Request $request) {
    try {
        // Simular sesión para la prueba
        session(['usuario_id' => 1]);

        // Log para debug
        \Log::info('DEBUG UPDATE: Iniciando update para ID: ' . $id);
        \Log::info('DEBUG UPDATE: Datos recibidos:', $request->all());
        \Log::info('DEBUG UPDATE: Headers:', $request->headers->all());

        // Llamar al controlador
        $controller = app(RegistrosDengueController::class);
        $response = $controller->update($id, $request);

        \Log::info('DEBUG UPDATE: Respuesta generada correctamente');

        return $response;

    } catch (\Exception $e) {
        \Log::error('DEBUG UPDATE: Error capturado:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error capturado: ' . $e->getMessage(),
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ], 500);
    }
})->name('debug-update-dengue');

Route::get('/debug-update-form', function() {
    return '<!DOCTYPE html>
<html>
<head>
    <title>Debug Update Form</title>
    <meta name="csrf-token" content="' . csrf_token() . '">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Debug Update Form</h1>
    <button onclick="testDebugUpdate()">Test Debug Update</button>
    <div id="result"></div>

    <script>
        function testDebugUpdate() {
            const formData = new FormData();
            formData.append("nombre", "Test Debug Usuario");
            formData.append("dni", "87654321");
            formData.append("sexo", "M");
            formData.append("_method", "PUT");

            $.ajax({
                url: "/debug-update-dengue/1",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content"),
                    "Accept": "application/json"
                },
                success: function(response) {
                    $("#result").html("<pre style=\"background: #d4edda; padding: 10px;\">" + JSON.stringify(response, null, 2) + "</pre>");
                },
                error: function(xhr, status, error) {
                    let errorMsg = "Status: " + xhr.status + "\\nError: " + error;
                    if (xhr.responseText) {
                        errorMsg += "\\nResponse: " + xhr.responseText.substring(0, 500);
                    }
                    $("#result").html("<pre style=\"background: #f8d7da; padding: 10px;\">" + errorMsg + "</pre>");
                }
            });
        }
    </script>
</body>
</html>';
});
