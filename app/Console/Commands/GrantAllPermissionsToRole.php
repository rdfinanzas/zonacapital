<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrantAllPermissionsToRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:grant-all {roleId=1} {--all-modules : Incluir todos los módulos, no solo hijos (Padre=0)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otorga C/R/U/D=1 en permisos_x_tipos_usuarios para todos los módulos al rol indicado (por defecto 1).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $roleId = (int) $this->argument('roleId');
        $allModules = (bool) $this->option('all-modules');

        try {
            // Obtener módulos: por defecto hijos (Padre = 0) para coincidir con UI de Roles
            if ($allModules) {
                $modulos = DB::table('modulos')->select('IdModulo')->pluck('IdModulo')->all();
            } else {
                $modulos = DB::table('modulos')
                    ->where('Padre', 0)
                    ->select('IdModulo')
                    ->pluck('IdModulo')
                    ->all();
            }

            if (empty($modulos)) {
                $this->warn('No se encontraron módulos para actualizar.');
                return self::SUCCESS;
            }

            $count = 0;
            foreach ($modulos as $moduloId) {
                DB::table('permisos_x_tipos_usuarios')->updateOrInsert(
                    [
                        'TipoUsuarioId' => $roleId,
                        'ModuloId' => $moduloId,
                    ],
                    [
                        'C' => 1,
                        'R' => 1,
                        'U' => 1,
                        'D' => 1,
                    ]
                );
                $count++;
            }

            $this->info("Permisos C/R/U/D=1 aplicados a {$count} módulos para rol {$roleId}.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error al otorgar permisos: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}