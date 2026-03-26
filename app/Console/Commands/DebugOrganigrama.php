<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugOrganigrama extends Command
{
    protected $signature = 'organigrama:debug';
    protected $description = 'Debug del módulo de organigrama';

    public function handle()
    {
        $this->info("=== DEBUG MÓDULO ORGANIGRAMA ===");

        // Buscar tablas relacionadas
        $this->info("\n1. Buscando tablas relacionadas:");
        $tables = DB::select('SHOW TABLES');
        $relevantTables = [];

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            if (stripos($tableName, 'organigrama') !== false ||
                stripos($tableName, 'gerencia') !== false ||
                stripos($tableName, 'departamento') !== false ||
                stripos($tableName, 'servicio') !== false ||
                stripos($tableName, 'sector') !== false) {
                $relevantTables[] = $tableName;
                $this->line("   ✓ {$tableName}");
            }
        }

        if (empty($relevantTables)) {
            $this->error("   ❌ No se encontraron tablas relacionadas");
        }

        // Verificar estructura de las tablas encontradas
        foreach ($relevantTables as $tableName) {
            $this->info("\n2. Estructura de tabla '{$tableName}':");
            try {
                $estructura = DB::select("DESCRIBE {$tableName}");
                foreach ($estructura as $campo) {
                    $this->line("   {$campo->Field} - {$campo->Type}");
                }

                // Contar registros
                $count = DB::table($tableName)->count();
                $this->info("   Registros: {$count}");

            } catch (Exception $e) {
                $this->error("   ❌ Error: " . $e->getMessage());
            }
        }

        return 0;
    }
}
