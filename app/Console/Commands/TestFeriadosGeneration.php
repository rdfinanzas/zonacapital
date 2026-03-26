<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feriado;

class TestFeriadosGeneration extends Command
{
    protected $signature = 'feriados:test-generation {year=2026}';
    protected $description = 'Prueba la generación de feriados fijos para un año específico';

    public function handle()
    {
        $year = $this->argument('year');

        $this->info("Probando generación de feriados fijos para el año $year...");

        // Verificar cuántos feriados ya existen para ese año
        $existentes = Feriado::whereYear('FechaFer', $year)->count();
        $this->info("Feriados existentes para $year: $existentes");

        // Generar feriados fijos
        $creados = Feriado::generarFeriadosFijos($year);
        $this->info("Se crearon $creados feriados fijos para $year");

        // Mostrar todos los feriados del año
        $this->info("\nFeriados para $year:");
        $feriados = Feriado::activos()
            ->whereYear('FechaFer', $year)
            ->orderBy('FechaFer')
            ->get();

        $this->table(
            ['Fecha', 'Feriado', 'Tipo', 'DiaMes'],
            $feriados->map(function($f) {
                return [
                    $f->FechaFer->format('d/m/Y'),
                    $f->Feriado,
                    $f->EsFijo ? 'Fijo' : 'Variable',
                    $f->DiaMes ?: '-'
                ];
            })
        );

        return 0;
    }
}
