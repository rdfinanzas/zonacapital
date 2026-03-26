<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RevokeUserModulePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:revoke-user-module {moduleUrl} {userId?} {--all-users : Revoca para todos los usuarios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoca permisos a nivel usuario (C/R/U/D) para un módulo dado. Acepta URL con o sin prefijo "laravel-".';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moduleUrl = trim($this->argument('moduleUrl'));
        $inputUserId = $this->argument('userId');
        $allUsers = $this->option('all-users');

        if (!$allUsers && !$inputUserId) {
            $this->error('Debe especificar {userId} o usar --all-users.');
            return Command::FAILURE;
        }

        // Resolver IdModulo probando con y sin prefijo "laravel-"
        $module = DB::table('modulos')->where('Url', $moduleUrl)->first();
        if (!$module && !str_starts_with($moduleUrl, 'laravel-')) {
            $module = DB::table('modulos')->where('Url', 'laravel-' . $moduleUrl)->first();
        }

        if (!$module) {
            $this->error("No se encontró el módulo por Url: {$moduleUrl} (ni con prefijo 'laravel-').");
            return Command::FAILURE;
        }

        // Resolver IdUsuario si se especifica un usuario
        $usuario = null;
        if (!$allUsers) {
            $uid = (int)$inputUserId;
            $usuario = DB::table('usuarios')->where('IdUsuario', $uid)->first();
            if (!$usuario) {
                $empleado = DB::table('empleados')->where('DNI', $uid)->first();
                if ($empleado) {
                    $usuario = DB::table('usuarios')->where('Personal_Id', $empleado->idEmpleado)->first();
                }
            }
            if (!$usuario) {
                $this->error("Usuario {$inputUserId} no encontrado (ni por IdUsuario ni por DNI vinculado). ");
                return Command::FAILURE;
            }
        }

        $query = DB::table('permisos_x_usuarios')->where('ModuloId', $module->IdModulo);
        if ($allUsers) {
            $deleted = $query->delete();
            $this->info("Revocados {$deleted} permisos a nivel usuario para el módulo '{$module->Url}' en TODOS los usuarios.");
        } else {
            $deleted = $query->where('UsuarioId', $usuario->IdUsuario)->delete();
            $this->info("Revocados {$deleted} permisos a nivel usuario para el módulo '{$module->Url}' en el usuario {$usuario->IdUsuario}.");
        }

        return Command::SUCCESS;
    }
}