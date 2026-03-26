<?php

// Debug temporal para verificar efectores
Route::get('/debug-efector', function() {
    try {
        $registro = App\Models\RegistroDengue::with(['efector', 'paciente', 'usuario'])->find(7);

        if (!$registro) {
            return response()->json(['error' => 'Registro no encontrado']);
        }

        return response()->json([
            'registro_id' => $registro->IdRegistroDengue,
            'efector_id' => $registro->Efector_Id,
            'efector_objeto' => $registro->efector,
            'efector_servicio' => $registro->efector->servicio ?? null,
            'paciente' => $registro->paciente->ApellidoNombre ?? null,
            'usuario' => ($registro->usuario->Nombre ?? '') . ' ' . ($registro->usuario->Apellido ?? ''),
            'raw_data' => $registro->toArray()
        ]);

    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
