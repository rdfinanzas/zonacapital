<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeriadoFijo;

class VerificarFeriadosFijos extends Command
{
    protected $signature = 'feriados:verificar-fijos';
    protected $description = 'Verificar el contenido de la tabla feriados_fijos';

    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE FERIADOS FIJOS ===');

        $count = FeriadoFijo::count();
        $this->info("Total de registros: $count");

        if ($count == 0) {
            $this->warn('No hay feriados fijos configurados!');
            return 1;
        }

        $this->info("\nFeriados fijos configurados:");
        $this->table(
            ['ID', 'Nombre', 'Día', 'Mes', 'DiaMes', 'Activo', 'Descripción'],
            FeriadoFijo::orderBy('mes')->orderBy('dia')->get()->map(function($f) {
                return [
                    $f->id,
                    $f->nombre,
                    $f->dia,
                    $f->mes,
                    $f->dia_mes,
                    $f->activo ? 'SÍ' : 'NO',
                    substr($f->descripcion ?? '', 0, 30) . (strlen($f->descripcion ?? '') > 30 ? '...' : '')
                ];
            })
        );

        // Verificar si alguno está inactivo
        $inactivos = FeriadoFijo::where('activo', false)->count();
        if ($inactivos > 0) {
            $this->warn("Hay $inactivos feriados inactivos que no se generarán automáticamente.");
        }

        return 0;
    }
}
