<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RevokeAllUserLaravelPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:revoke-user-all-laravel {userId} {--dry-run : Solo muestra cuántos eliminaría}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todos los permisos a nivel usuario en módulos Laravel (Url like %laravel%) para el usuario dado.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inputUserId = (int)$this->argument('userId');
        // Resolver IdUsuario: aceptar IdUsuario directo o DNI vinculado a empleados
        $usuario = DB::table('usuarios')->where('IdUsuario', $inputUserId)->first();
        if (!$usuario) {
            $empleado = DB::table('empleados')->where('DNI', $inputUserId)->first();
            if ($empleado) {
                $usuario = DB::table('usuarios')->where('Personal_Id', $empleado->idEmpleado)->first();
            }
        }
        if (!$usuario) {
            $this->error("Usuario {$inputUserId} no encontrado (ni por IdUsuario ni por DNI vinculado). ");
            return Command::FAILURE;
        }
        $dryRun = (bool)$this->option('dry-run');

        $modulosLaravelIds = DB::table('modulos')
            ->where('Url', 'like', '%laravel%')
            ->pluck('IdModulo')
            ->all();

        if (empty($modulosLaravelIds)) {
            $this->warn('No hay módulos Laravel registrados.');
            return Command::SUCCESS;
        }

        $query = DB::table('permisos_x_usuarios')
            ->where('UsuarioId', $usuario->IdUsuario)
            ->whereIn('ModuloId', $modulosLaravelIds);

        $count = $query->count();
        if ($dryRun) {
            $this->info("Se eliminarían {$count} permisos a nivel usuario (módulos Laravel) para el usuario {$usuario->IdUsuario}.");
            return Command::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Eliminados {$deleted} permisos a nivel usuario (módulos Laravel) para el usuario {$usuario->IdUsuario}.");
        return Command::SUCCESS;
    }
}