<?php

// Test simple para verificar el método update
Route::get('/test-simple-update', function() {
    session(['usuario_id' => 1]);

    try {
        // Simular request
        $request = new Illuminate\Http\Request();
        $request->merge([
            'nombre' => 'Test Simple Update',
            'dni' => '12345678',
            '_method' => 'PUT'
        ]);

        $controller = app(App\Http\Controllers\RegistrosDengueController::class);
        $response = $controller->update(7, $request);

        // Convertir response a array para debug
        $content = $response->getContent();

        return response()->json([
            'test_result' => 'success',
            'response_content' => $content,
            'response_status' => $response->getStatusCode(),
            'response_headers' => $response->headers->all()
        ]);

    } catch (Exception $e) {
        return response()->json([
            'test_result' => 'error',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
