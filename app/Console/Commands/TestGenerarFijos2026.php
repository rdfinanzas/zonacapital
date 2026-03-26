<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feriado;
use App\Models\FeriadoFijo;

class TestGenerarFijos2026 extends Command
{
    protected $signature = 'feriados:test-generar-2026';
    protected $description = 'Probar la generación de feriados fijos para 2026';

    public function handle()
    {
        $this->info('=== PROBANDO GENERACIÓN DE FERIADOS FIJOS 2026 ===');

        // 1. Estado inicial
        $activosAntes = Feriado::activos()->whereYear('FechaFer', 2026)->count();
        $this->info("1. Feriados activos de 2026 ANTES: {$activosAntes}");

        // 2. Generar feriados fijos
        $this->info("2. Generando feriados fijos para 2026...");
        $creados = FeriadoFijo::generarFeriadosParaAnio(2026);
        $this->info("   Resultado: {$creados} feriados creados");

        // 3. Estado final
        $activosDespues = Feriado::activos()->whereYear('FechaFer', 2026)->count();
        $this->info("3. Feriados activos de 2026 DESPUÉS: {$activosDespues}");

        // 4. Mostrar los feriados creados
        if ($activosDespues > 0) {
            $this->info("4. Feriados de 2026:");
            $feriados2026 = Feriado::activos()->whereYear('FechaFer', 2026)->orderBy('FechaFer')->get();
            foreach ($feriados2026 as $f) {
                $tipo = $f->EsFijo ? 'FIJO' : 'VARIABLE';
                $this->line("   - {$f->Feriado} ({$f->FechaFer}) - {$tipo}");
            }
        }

        return 0;
    }
}
