<?php

// Test directo de datos sin middleware
Route::get('/test-efector-directo', function() {
    try {
        // Simular sesión
        session(['usuario_id' => 1]);

        // Hacer la consulta directamente como en el controlador
        $registros = App\Models\RegistroDengue::with(['paciente', 'efector', 'usuario'])
            ->where('febril', 1)
            ->take(3)
            ->get();

        $data = [];
        foreach($registros as $registro) {
            // Debug del efector
            $servicioNombre = '';
            $debugInfo = [];

            if ($registro->efector) {
                $servicioNombre = $registro->efector->servicio;
                $debugInfo['efector_existe'] = 'SI';
                $debugInfo['efector_objeto'] = $registro->efector->toArray();
            } else {
                // Si no hay relación, buscar directamente por ID
                $servicioDirecto = DB::table('servicio')->where('idServicio', $registro->Efector_Id)->first();
                $servicioNombre = $servicioDirecto ? $servicioDirecto->servicio : 'No encontrado';
                $debugInfo['efector_existe'] = 'NO';
                $debugInfo['servicio_directo'] = $servicioDirecto ? (array)$servicioDirecto : null;
            }

            $data[] = [
                'IdRegistroDengue' => $registro->IdRegistroDengue,
                'ApellidoNombre' => $registro->paciente->ApellidoNombre ?? 'Sin paciente',
                'servicio' => $servicioNombre,
                'efector_id' => $registro->Efector_Id,
                'debug' => $debugInfo
            ];
        }

        return response()->json([
            'success' => true,
            'total_registros' => $registros->count(),
            'data' => $data
        ]);

    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
