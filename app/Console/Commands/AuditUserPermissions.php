<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditUserPermissions extends Command
{
    protected $signature = 'permissions:audit-user {userId}';
    protected $description = 'Muestra el rol actual del usuario y lista permisos a nivel usuario y por rol para módulos Laravel.';

    public function handle()
    {
        $userId = (int)$this->argument('userId');

        // Intentar encontrar por IdUsuario; si no existe, intentar por DNI vinculado al empleado
        $usuario = DB::table('usuarios')->where('IdUsuario', $userId)->first();
        if (!$usuario) {
            $empleado = DB::table('empleados')->where('DNI', $userId)->first();
            if ($empleado) {
                $usuario = DB::table('usuarios')->where('Personal_Id', $empleado->idEmpleado)->first();
            }
        }
        if (!$usuario) {
            $this->error("Usuario {$userId} no encontrado (ni por IdUsuario ni por DNI vinculado). ");
            return Command::FAILURE;
        }

        $rolId = (int)($usuario->UsuarioTipo_Id ?? 0);
        $rolNombre = null;
        if ($rolId) {
            $rolNombre = DB::table('usuario_tipos')->where('IdUsuarioTipo', $rolId)->value('UsuarioTipo');
        }
        $this->info("Usuario {$userId} → Rol= {$rolId} ({$rolNombre}) | IdUsuario={$usuario->IdUsuario}");

        // Permisos a nivel usuario
        $permisosUsuario = DB::table('permisos_x_usuarios')
            ->join('modulos', 'permisos_x_usuarios.ModuloId', '=', 'modulos.IdModulo')
            ->where('UsuarioId', $usuario->IdUsuario)
            ->where('modulos.Url', 'like', '%laravel%')
            ->select('modulos.Url', 'permisos_x_usuarios.C', 'permisos_x_usuarios.R', 'permisos_x_usuarios.U', 'permisos_x_usuarios.D')
            ->orderBy('modulos.Url')
            ->get();

        $this->line('Permisos a nivel USUARIO:');
        if ($permisosUsuario->isEmpty()) {
            $this->line('  (sin permisos usuario en módulos Laravel)');
        } else {
            foreach ($permisosUsuario as $p) {
                $this->line(sprintf('  %s → C:%d R:%d U:%d D:%d', $p->Url, $p->C, $p->R, $p->U, $p->D));
            }
        }

        // Permisos por rol
        $this->line('Permisos por ROL:');
        if ($rolId) {
            $permisosRol = DB::table('permisos_x_tipos_usuarios')
                ->join('modulos', 'permisos_x_tipos_usuarios.ModuloId', '=', 'modulos.IdModulo')
                ->where('TipoUsuarioId', $rolId)
                ->where('modulos.Url', 'like', '%laravel%')
                ->select('modulos.Url', 'permisos_x_tipos_usuarios.C', 'permisos_x_tipos_usuarios.R', 'permisos_x_tipos_usuarios.U', 'permisos_x_tipos_usuarios.D')
                ->orderBy('modulos.Url')
                ->get();
            if ($permisosRol->isEmpty()) {
                $this->line('  (sin permisos rol en módulos Laravel)');
            } else {
                foreach ($permisosRol as $p) {
                    $this->line(sprintf('  %s → C:%d R:%d U:%d D:%d', $p->Url, $p->C, $p->R, $p->U, $p->D));
                }
            }
        } else {
            $this->line('  (usuario sin rol asignado en UsuarioTipo_Id)');
        }

        return Command::SUCCESS;
    }
}