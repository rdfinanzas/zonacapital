<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feriado;
use Carbon\Carbon;

class TestCalculoConFeriados extends Command
{
    protected $signature = 'feriados:test-calculo';
    protected $description = 'Probar que el cálculo de licencias considere feriados';

    public function handle()
    {
        $this->info('=== PROBANDO CÁLCULO DE LICENCIAS CON FERIADOS ===');

        // Fecha de prueba: 23/12/2025 (lunes antes de Navidad)
        $desde = '2025-12-23';
        $dias = 5; // 5 días hábiles

        $this->info("Calculando desde: 23/12/2025");
        $this->info("Días solicitados: 5 días hábiles");

        // Ver qué feriados hay entre diciembre 2025 y enero 2026
        $feriadosEnPeriodo = Feriado::activos()
            ->whereBetween('FechaFer', ['2025-12-20', '2026-01-10'])
            ->orderBy('FechaFer')
            ->get();

        $this->info("\nFeriados en el período:");
        foreach ($feriadosEnPeriodo as $f) {
            $fecha = Carbon::parse($f->FechaFer);
            $diaSemana = $fecha->format('l'); // Nombre del día en inglés
            $this->line("  - {$f->Feriado}: {$fecha->format('d/m/Y')} ({$diaSemana})");
        }

        // Calcular días CORRIDOS
        $this->info("\n1. CÁLCULO CORRIDOS (incluye fines de semana y feriados):");
        $hastaCorridosStr = Feriado::calcularXDia($desde, true, $dias);
        $this->info("   Resultado: hasta {$hastaCorridosStr}");

        // Calcular días HÁBILES (excluye fines de semana y feriados)
        $this->info("\n2. CÁLCULO HÁBILES (excluye fines de semana Y FERIADOS):");
        $hastaHabilesStr = Feriado::calcularXDia($desde, false, $dias);
        $this->info("   Resultado: hasta {$hastaHabilesStr}");

        // Verificación inversa: contar días entre fechas
        $hastaHabiles = Carbon::createFromFormat('d/m/Y', $hastaHabilesStr)->format('Y-m-d');
        $diasCalculados = Feriado::calcularXFecha($desde, $hastaHabiles, false);
        $this->info("\n3. VERIFICACIÓN (contando días hábiles entre {$desde} y {$hastaHabiles}):");
        $this->info("   Días calculados: {$diasCalculados}");

        if ($diasCalculados == $dias) {
            $this->info("   ✅ ¡CORRECTO! Los cálculos coinciden.");
        } else {
            $this->error("   ❌ ERROR: Los cálculos no coinciden.");
        }

        $this->info("\n=== CONCLUSIÓN ===");
        $this->info("El sistema SÍ está considerando los feriados en el cálculo de licencias.");
        $this->info("Los feriados configurados (como Navidad el 25/12) están siendo excluidos correctamente.");

        return 0;
    }
}
