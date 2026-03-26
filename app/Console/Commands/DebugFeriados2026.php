<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feriado;
use App\Models\FeriadoFijo;

class DebugFeriados2026 extends Command
{
    protected $signature = 'feriados:debug-2026';
    protected $description = 'Debug feriados 2026 generation';

    public function handle()
    {
        $this->info('=== INVESTIGANDO FERIADOS 2026 ===');

        // 1. Contar todos los registros de 2026 (incluso eliminados)
        $todosCount = Feriado::whereYear('FechaFer', 2026)->count();
        $this->info("1. Total registros 2026 (con eliminados): {$todosCount}");

        // 2. Mostrar registros eliminados (con FechaEliminacion)
        $eliminados = Feriado::whereYear('FechaFer', 2026)->whereNotNull('FechaEliminacion')->get();
        $this->info("2. Registros eliminados: {$eliminados->count()}");
        foreach($eliminados as $f) {
            $this->line("   - ID:{$f->IdFeriado} {$f->Feriado} ({$f->FechaFer}) - ELIMINADO ({$f->FechaEliminacion})");
        }

        // 3. Contar activos
        $activosCount = Feriado::activos()->whereYear('FechaFer', 2026)->count();
        $this->info("3. Registros activos: {$activosCount}");

        // 4. Ver qué feriados fijos existen
        $feriadosFijos = FeriadoFijo::activos()->get();
        $this->info("4. Feriados fijos disponibles: {$feriadosFijos->count()}");
        foreach($feriadosFijos as $ff) {
            $this->line("   - {$ff->nombre} ({$ff->dia}/{$ff->mes})");
        }

        // 5. Intentar generar
        $this->info("5. Intentando generar feriados fijos para 2026...");
        $creados = Feriado::generarFeriadosFijos(2026);
        $this->info("   Resultado: {$creados} feriados creados");

        // 6. Verificar después
        $despuesCount = Feriado::activos()->whereYear('FechaFer', 2026)->count();
        $this->info("6. Registros activos después de generar: {$despuesCount}");

        // 7. Mostrar los registros actuales
        $actuales = Feriado::activos()->whereYear('FechaFer', 2026)->orderBy('FechaFer')->get();
        $this->info("7. Feriados actuales de 2026:");
        foreach($actuales as $f) {
            $tipo = $f->EsFijo ? 'FIJO' : 'VARIABLE';
            $this->line("   - {$f->Feriado} ({$f->FechaFer}) - {$tipo}");
        }
    }
}
