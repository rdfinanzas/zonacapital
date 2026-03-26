<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPermisosExtrasLaravel extends Command
{
    protected $signature = 'sync:extras-laravel {--dry-run} {--relink-existing}';
    protected $description = 'Vincula permisos_extras.ModuloId a módulos con prefijo laravel- según el Url base.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $relinkExisting = $this->option('relink-existing');

        $extras = DB::table('permisos_extras')->get();
        $total = $extras->count();
        $updated = 0;
        $skipped = 0;
        $notFound = 0;

        $this->info("Procesando {$total} permisos extras...");

        foreach ($extras as $ex) {
            $moduloActual = DB::table('modulos')->where('IdModulo', $ex->ModuloId)->first();

            if (!$moduloActual) {
                $this->warn("PermisoExtra {$ex->IdPermisoExtra} sin Modulo actual (Id={$ex->ModuloId})");
                $skipped++;
                continue;
            }

            $urlActual = $moduloActual->Url;
            $esLaravel = str_starts_with($urlActual, 'laravel-');

            if ($esLaravel && !$relinkExisting) {
                // Ya está vinculado a un módulo laravel-, se omite por defecto
                $skipped++;
                continue;
            }

            // Si tiene prefijo laravel-, recuperar url base; si no, usar actual como base
            $urlBase = $esLaravel ? substr($urlActual, strlen('laravel-')) : $urlActual;
            // Normalizar: laravel usa guiones en lugar de underscores
            $urlLaravel = 'laravel-' . str_replace('_', '-', $urlBase);

            $moduloLaravel = DB::table('modulos')->where('Url', $urlLaravel)->first();

            if (!$moduloLaravel) {
                $this->error("No se encontró módulo laravel para '{$urlActual}' -> '{$urlLaravel}' (PermisoExtra {$ex->IdPermisoExtra})");
                $notFound++;
                continue;
            }

            if ($moduloLaravel->IdModulo == $ex->ModuloId) {
                $skipped++;
                continue;
            }

            $this->line("PermisoExtra {$ex->IdPermisoExtra}: ModuloId {$ex->ModuloId} ({$urlActual}) -> {$moduloLaravel->IdModulo} ({$moduloLaravel->Url})");

            if (!$dryRun) {
                DB::table('permisos_extras')
                    ->where('IdPermisoExtra', $ex->IdPermisoExtra)
                    ->update(['ModuloId' => $moduloLaravel->IdModulo]);
                $updated++;
            }
        }

        $this->info("Resumen: actualizados={$updated}, omitidos={$skipped}, sin_mapeo={$notFound}, total={$total}");

        if ($dryRun) {
            $this->comment('Dry-run: no se aplicaron cambios. Ejecute sin --dry-run para aplicar.');
        }

        return Command::SUCCESS;
    }
}