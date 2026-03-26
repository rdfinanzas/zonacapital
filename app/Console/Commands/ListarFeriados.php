<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feriado;

class ListarFeriados extends Command
{
    protected $signature = 'feriados:listar {cantidad=5}';
    protected $description = 'Listar los primeros feriados para debug';

    public function handle()
    {
        $cantidad = $this->argument('cantidad');

        $this->info("=== PRIMEROS $cantidad FERIADOS ===");

        $feriados = Feriado::take($cantidad)->get(['IdFeriado', 'Feriado', 'FechaFer', 'FechaEliminacion']);

        $this->table(
            ['ID', 'Feriado', 'Fecha', 'Eliminado'],
            $feriados->map(function($f) {
                return [
                    $f->IdFeriado,
                    substr($f->Feriado, 0, 30) . (strlen($f->Feriado) > 30 ? '...' : ''),
                    $f->FechaFer ? $f->FechaFer->format('d/m/Y') : 'NULL',
                    $f->FechaEliminacion ? 'SÍ (' . $f->FechaEliminacion->format('d/m/Y') . ')' : 'NO'
                ];
            })
        );

        $totalActivos = Feriado::activos()->count();
        $totalEliminados = Feriado::whereNotNull('FechaEliminacion')->count();

        $this->info("\nResumen:");
        $this->info("- Total activos: $totalActivos");
        $this->info("- Total eliminados: $totalEliminados");

        return 0;
    }
}
