<?php

use Illuminate\Support\Facades\Route;
use App\Models\Feriado;
use Carbon\Carbon;

// Ruta de prueba para el cálculo de fechas
Route::get('/test-calculo', function() {
    try {
        // Datos de prueba
        $desde = '2024-11-19'; // Fecha de hoy
        $dias = 5;
        $corrido_false = false; // Días hábiles
        $corrido_true = true;   // Días corridos

        echo "<h2>Pruebas de Cálculo de Fechas</h2>";

        // Test 1: Calcular fecha final con días hábiles
        echo "<h3>Test 1: Calcular fecha final (días hábiles)</h3>";
        echo "Desde: " . Carbon::parse($desde)->format('d/m/Y') . "<br>";
        echo "Días: $dias (hábiles)<br>";
        $resultado1 = Feriado::calcularXDia($desde, $corrido_false, $dias);
        echo "Hasta: $resultado1<br><br>";

        // Test 2: Calcular fecha final con días corridos
        echo "<h3>Test 2: Calcular fecha final (días corridos)</h3>";
        echo "Desde: " . Carbon::parse($desde)->format('d/m/Y') . "<br>";
        echo "Días: $dias (corridos)<br>";
        $resultado2 = Feriado::calcularXDia($desde, $corrido_true, $dias);
        echo "Hasta: $resultado2<br><br>";

        // Test 3: Calcular días entre fechas (hábiles)
        echo "<h3>Test 3: Calcular días entre fechas (hábiles)</h3>";
        $hasta = '2024-11-26';
        echo "Desde: " . Carbon::parse($desde)->format('d/m/Y') . "<br>";
        echo "Hasta: " . Carbon::parse($hasta)->format('d/m/Y') . "<br>";
        $resultado3 = Feriado::calcularXFecha($desde, $hasta, $corrido_false);
        echo "Días hábiles: $resultado3<br><br>";

        // Test 4: Calcular días entre fechas (corridos)
        echo "<h3>Test 4: Calcular días entre fechas (corridos)</h3>";
        echo "Desde: " . Carbon::parse($desde)->format('d/m/Y') . "<br>";
        echo "Hasta: " . Carbon::parse($hasta)->format('d/m/Y') . "<br>";
        $resultado4 = Feriado::calcularXFecha($desde, $hasta, $corrido_true);
        echo "Días corridos: $resultado4<br><br>";

        // Test 5: Mostrar feriados activos
        echo "<h3>Test 5: Feriados activos en 2024</h3>";
        $feriados = Feriado::getFeriadosAno(2024);
        foreach($feriados as $feriado) {
            echo "- " . Carbon::parse($feriado)->format('d/m/Y') . "<br>";
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
});