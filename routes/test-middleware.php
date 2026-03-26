<?php

use Illuminate\Http\Request;
use App\Http\Controllers\RegistrosDengueController;

// Ruta temporal SIN middleware para probar funcionalidad
Route::get('/test-dengue-sin-middleware', function() {
    // Simular sesión
    session(['usuario_id' => 1]);

    try {
        $controller = app(RegistrosDengueController::class);
        return $controller->index();
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

// Test UPDATE sin middleware
Route::post('/test-update-sin-middleware/{id}', function($id, Request $request) {
    // Simular sesión
    session(['usuario_id' => 1]);

    try {
        // Log datos recibidos
        \Log::info('TEST UPDATE: ID=' . $id);
        \Log::info('TEST UPDATE: Datos:', $request->all());

        $controller = app(RegistrosDengueController::class);
        $response = $controller->update($id, $request);

        \Log::info('TEST UPDATE: Respuesta exitosa');
        return $response;

    } catch (Exception $e) {
        \Log::error('TEST UPDATE: Error:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'debug' => 'Exception capturada en ruta de test'
        ], 500);
    }
});

// Test GET sin middleware
Route::get('/test-get-sin-middleware/{id}', function($id) {
    // Simular sesión
    session(['usuario_id' => 1]);

    try {
        $controller = app(RegistrosDengueController::class);
        return $controller->getById($id);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }
});
