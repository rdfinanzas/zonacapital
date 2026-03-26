<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Helpers\PermisoHelper;

class MenuHelper
{
    /**
     * Obtiene los módulos a los que el usuario tiene acceso
     *
     * @param int $usuarioId
     * @return array
     */
    public static function obtenerModulos($usuarioId)
    {
        // Obtener todos los módulos
        // Obtener todos los módulos que sean padres o tengan hijos con Url like '%laravel%'
        $modulos = DB::table('modulos')
            ->where(function ($query) {
                $query
                    ->where('Url', 'like', '%laravel%')
                    ->orWhereIn('IdModulo', function ($sub) {
                        $sub->select('ModuloPadreId')
                            ->from('modulos')
                            ->where('Url', 'like', '%laravel%');
                    });
            })
            ->orderBy('ModuloPadreId')
            ->orderBy('Orden')
            ->get();

        // Super Admin: ve todos los módulos sin filtrar
        if (PermisoHelper::esSuperAdmin($usuarioId)) {
            return $modulos->all();
        }

        // Obtener los permisos del usuario (heredados/legado)
        $permisosUsuario = DB::table('permisos_x_usuarios')
            ->join('modulos', 'permisos_x_usuarios.ModuloId', '=', 'modulos.IdModulo')
            ->where('permisos_x_usuarios.UsuarioId', $usuarioId)
            ->where('modulos.Url', 'like', '%laravel%')
            ->select('permisos_x_usuarios.*')
            ->get();

        // Obtener roles del usuario (pivot + legacy simple)
        $rolesIds = [];
        // Legacy simple rol
        $legacyRol = DB::table('usuarios')
            ->where('IdUsuario', $usuarioId)
            ->value('UsuarioTipo_Id');
        if (!empty($legacyRol)) {
            $rolesIds[] = (int)$legacyRol;
        }
        // Revertir: sin soporte pivot, sólo rol legacy
        $rolesIds = array_values(array_unique($rolesIds));

        // Obtener permisos por todos los roles del usuario
        $permisosRol = collect();
        if (count($rolesIds) > 0) {
            $permisosRol = DB::table('permisos_x_tipos_usuarios')
                ->join('modulos', 'permisos_x_tipos_usuarios.ModuloId', '=', 'modulos.IdModulo')
                ->whereIn('permisos_x_tipos_usuarios.TipoUsuarioId', $rolesIds)
                ->where('modulos.Url', 'like', '%laravel%')
                ->select('permisos_x_tipos_usuarios.*')
                ->get();
        }

        // Filtrar módulos accesibles (hijos con al menos un permiso) y agregar sólo padres con hijos permitidos
        $hijosAccesibles = [];
        $padresConHijos = [];

        foreach ($modulos as $modulo) {
            if ($modulo->Padre == 0) {
                $tienePermisosUsuario = false;
                foreach ($permisosUsuario as $pu) {
                    if (
                        $pu->ModuloId == $modulo->IdModulo &&
                        ($pu->C || $pu->R || $pu->U || $pu->D)
                    ) {
                        $tienePermisosUsuario = true;
                        break;
                    }
                }

                $tienePermisosRol = false;
                foreach ($permisosRol as $pr) {
                    if (
                        $pr->ModuloId == $modulo->IdModulo &&
                        ($pr->C || $pr->R || $pr->U || $pr->D)
                    ) {
                        $tienePermisosRol = true;
                        break;
                    }
                }

                if ($tienePermisosUsuario || $tienePermisosRol) {
                    $hijosAccesibles[] = $modulo;
                    if (!empty($modulo->ModuloPadreId)) {
                        $padresConHijos[$modulo->ModuloPadreId] = true;
                    }
                }
            }
        }

        $resultado = [];
        // Agregar padres que tienen al menos un hijo accesible
        foreach ($modulos as $modulo) {
            if ($modulo->Padre == 1 && isset($padresConHijos[$modulo->IdModulo])) {
                $resultado[] = $modulo;
            }
        }
        // Agregar hijos accesibles
        foreach ($hijosAccesibles as $hijo) {
            $resultado[] = $hijo;
        }

        // Si no hay nada accesible (evita sidebar vacío por permisos mal cargados), mostrar todos los módulos filtrados
        if (empty($resultado)) {
            return $modulos->all();
        }

        return $resultado;
    }
}
