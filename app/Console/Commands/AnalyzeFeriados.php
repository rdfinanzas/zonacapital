<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Feriado;

class AnalyzeFeriados extends Command
{
    protected $signature = 'feriados:analyze';
    protected $description = 'Analizar feriados existentes para identificar patrones';

    public function handle()
    {
        $this->info('=== ANÁLISIS DE FERIADOS EXISTENTES ===');

        // Consultar feriados repetidos por fecha
        $feriadosRepetidos = DB::select("
            SELECT
                DATE_FORMAT(FechaFer, '%m-%d') as DiaMes,
                Feriado,
                COUNT(*) as repeticiones,
                MIN(YEAR(FechaFer)) as primer_año,
                MAX(YEAR(FechaFer)) as ultimo_año
            FROM feriados
            WHERE FechaEliminacion IS NULL
            GROUP BY DATE_FORMAT(FechaFer, '%m-%d'), Feriado
            HAVING repeticiones > 1
            ORDER BY repeticiones DESC, DiaMes
        ");

        $this->info('Feriados que se repiten en múltiples años:');
        foreach ($feriadosRepetidos as $feriado) {
            $this->line("- {$feriado->Feriado} ({$feriado->DiaMes}) - {$feriado->repeticiones} veces ({$feriado->primer_año}-{$feriado->ultimo_año})");
        }

        // Consultar todas las fechas únicas
        $fechasUnicas = DB::select("
            SELECT
                DATE_FORMAT(FechaFer, '%m-%d') as DiaMes,
                GROUP_CONCAT(DISTINCT Feriado ORDER BY Feriado SEPARATOR ' | ') as nombres,
                COUNT(DISTINCT YEAR(FechaFer)) as años_distintos
            FROM feriados
            WHERE FechaEliminacion IS NULL
            GROUP BY DATE_FORMAT(FechaFer, '%m-%d')
            HAVING años_distintos > 1
            ORDER BY DiaMes
        ");

        $this->info("\n=== FECHAS CON MÚLTIPLES AÑOS ===");
        foreach ($fechasUnicas as $fecha) {
            $this->line("{$fecha->DiaMes}: {$fecha->nombres} ({$fecha->años_distintos} años)");
        }

        return 0;
    }
}
