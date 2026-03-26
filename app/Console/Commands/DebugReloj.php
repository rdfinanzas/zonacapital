<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reloj;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;

class DebugReloj extends Command
{
    protected $signature = 'reloj:debug';
    protected $description = 'Debug del módulo de reloj';

    public function handle()
    {
        $this->info("=== DEBUG MÓDULO RELOJ ===");

        // Verificar si existe la tabla relojes
        try {
            $this->info("\n1. Verificando tabla 'relojes':");
            $estructura = DB::select('DESCRIBE relojes');
            foreach ($estructura as $campo) {
                $this->line("   {$campo->Field} - {$campo->Type}");
            }
        } catch (Exception $e) {
            $this->error("❌ Error tabla relojes: " . $e->getMessage());
        }

        // Verificar si existe la tabla servicio
        try {
            $this->info("\n2. Verificando tabla 'servicio':");
            $estructura = DB::select('DESCRIBE servicio');
            foreach ($estructura as $campo) {
                $this->line("   {$campo->Field} - {$campo->Type}");
            }
        } catch (Exception $e) {
            $this->error("❌ Error tabla servicio: " . $e->getMessage());
        }

        // Contar registros
        try {
            $this->info("\n3. Contando registros:");
            $countRelojes = Reloj::count();
            $this->info("   Relojes: {$countRelojes}");

            $countServicios = Servicio::count();
            $this->info("   Servicios: {$countServicios}");
        } catch (Exception $e) {
            $this->error("❌ Error contando: " . $e->getMessage());
        }

        // Probar consulta del controlador
        try {
            $this->info("\n4. Probando consulta del controlador:");
            $relojes = Reloj::with('servicio')->paginate(10);
            $this->info("   Consulta exitosa: " . $relojes->count() . " registros");

            if ($relojes->count() > 0) {
                $primer = $relojes->first();
                $this->line("   Primer registro: ID {$primer->id} - {$primer->reloj}");
                if ($primer->servicio) {
                    $this->line("   Servicio: " . json_encode($primer->servicio->toArray()));
                } else {
                    $this->warn("   ⚠️ Sin servicio asociado");
                }
            }
        } catch (Exception $e) {
            $this->error("❌ Error en consulta: " . $e->getMessage());
        }

        return 0;
    }
}
