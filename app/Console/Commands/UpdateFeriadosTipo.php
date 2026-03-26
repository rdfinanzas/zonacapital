<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Feriado;
use Carbon\Carbon;

class UpdateFeriadosTipo extends Command
{
    protected $signature = 'feriados:update-tipos';
    protected $description = 'Actualizar feriados existentes marcando cuáles son fijos y cuáles variables';

    public function handle()
    {
        $this->info('=== ACTUALIZANDO TIPOS DE FERIADOS ===');

        // Definir patrones de feriados fijos basándose en el análisis
        $feriadosFijos = [
            ['patron' => 'AÑO NUEVO', 'fecha' => '01-01'],
            ['patron' => 'TRABAJADOR', 'fecha' => '05-01'],
            ['patron' => 'REVOLUCIÓN DE MAYO', 'fecha' => '05-25'],
            ['patron' => 'INDEPENDENCIA', 'fecha' => '07-09'],
            ['patron' => 'SAN MARTIN', 'fecha' => '08-17'], // Puede variar 15 o 17
            ['patron' => 'BANDERA', 'fecha' => '06-20'],
            ['patron' => 'MALVINAS', 'fecha' => '04-02'],
            ['patron' => 'INMACULADA', 'fecha' => '12-08'],
            ['patron' => 'NAVIDAD', 'fecha' => '12-25'],
        ];

        $actualizados = 0;

        foreach ($feriadosFijos as $fijo) {
            // Buscar feriados que coincidan con el patrón
            $feriados = Feriado::whereNull('FechaEliminacion')
                ->where(function($query) use ($fijo) {
                    $query->where('Feriado', 'LIKE', '%' . $fijo['patron'] . '%')
                          ->orWhere(DB::raw("DATE_FORMAT(FechaFer, '%m-%d')"), $fijo['fecha']);
                })
                ->get();

            foreach ($feriados as $feriado) {
                $diaMes = Carbon::parse($feriado->FechaFer)->format('m-d');
                $anio = Carbon::parse($feriado->FechaFer)->year;

                $feriado->update([
                    'EsFijo' => 1,
                    'DiaMes' => $diaMes,
                    'Anio' => $anio
                ]);

                $actualizados++;
                $this->line("✓ Marcado como fijo: {$feriado->Feriado} ({$diaMes})");
            }
        }

        // Marcar el resto como variables
        $variables = Feriado::whereNull('FechaEliminacion')
            ->whereNull('EsFijo')
            ->get();

        foreach ($variables as $feriado) {
            $diaMes = Carbon::parse($feriado->FechaFer)->format('m-d');
            $anio = Carbon::parse($feriado->FechaFer)->year;

            $feriado->update([
                'EsFijo' => 0,
                'DiaMes' => $diaMes,
                'Anio' => $anio
            ]);

            $this->line("- Marcado como variable: {$feriado->Feriado} ({$diaMes})");
        }

        $this->info("\n✅ Proceso completado. {$actualizados} feriados marcados como fijos, " . count($variables) . " como variables.");

        return 0;
    }
}
