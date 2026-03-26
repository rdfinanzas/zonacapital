<?php
// app/Helpers/PermisoHelper.php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class PermisoHelper
{
    /**
     * Detecta si el usuario es Super Admin por UsuarioTipo_Id
     */
    public static function esSuperAdmin($usuarioId)
    {
        if (!$usuarioId) return false;
        $tipoId = DB::table('usuarios')
            ->where('IdUsuario', $usuarioId)
            ->value('UsuarioTipo_Id');

        if ($tipoId === null) return false;
        $tipoId = (int) $tipoId;
        return $tipoId === -1;
    }
    /**
     * Verifica si un usuario tiene un permiso específico para la ruta actual
     *
     * @param int $usuarioId ID del usuario
     * @param string $accion Tipo de permiso ('C', 'R', 'U', 'D')
     * @return bool True si tiene el permiso, false en caso contrario
     * @throws \Exception Si no se encuentra la ruta en la solicitud
     */
    public static function tienePermiso($usuarioId, $accion)
    {
        // Super Admin: acceso total a todas las rutas/acciones
        if (self::esSuperAdmin($usuarioId)) {
            return true;
        }
        $ruta = request()->path();

        if (!$ruta) {
            throw new \Exception("Ruta no encontrada en la solicitud.");
        }
        //si ruta viene "modelo-ejemplo/buscar/as" necesito lo primero que es "modelo-ejemplo"
        $ruta = explode('/', $ruta)[0];

        //      dd($ruta);
        $modulo = DB::table('modulos')->where('Url', 'laravel-' . $ruta)->first();

        if (!$modulo) return false;

        // Primero permisos específicos por usuario
        $permiso = DB::table('permisos_x_usuarios')
            ->where('UsuarioId', $usuarioId)
            ->where('ModuloId', $modulo->IdModulo)
            ->first();

        if ($permiso && isset($permiso->{$accion}) && $permiso->{$accion} == 1) {
            return true;
        }

        // Fallback a permisos por rol
        $usuarioTipo = DB::table('usuarios')
            ->select('UsuarioTipo_Id')
            ->where('IdUsuario', $usuarioId)
            ->first();

        if ($usuarioTipo && $usuarioTipo->UsuarioTipo_Id) {
            $permisoRol = DB::table('permisos_x_tipos_usuarios')
                ->where('TipoUsuarioId', $usuarioTipo->UsuarioTipo_Id)
                ->where('ModuloId', $modulo->IdModulo)
                ->first();
            return $permisoRol && isset($permisoRol->{$accion}) && $permisoRol->{$accion} == 1;
        }

        return false;
    }

    /**
     * Verifica si un usuario tiene un permiso específico para un módulo determinado
     *
     * @param int $usuarioId ID del usuario
     * @param string $accion Tipo de permiso ('C', 'R', 'U', 'D')
     * @param string $nombreModulo Nombre del módulo a verificar
     * @return bool True si tiene el permiso, false en caso contrario
     */
    public static function tienePermisoModulo($usuarioId, $accion, $nombreModulo)
    {
        // Super Admin: acceso total a todos los módulos/acciones
        if (self::esSuperAdmin($usuarioId)) {
            return true;
        }
        if (!$nombreModulo) {
            return false;
        }

        $modulo = DB::table('modulos')->where('Url', 'laravel-' . $nombreModulo)->first();

        if (!$modulo) return false;

        // Primero permisos específicos por usuario
        $permiso = DB::table('permisos_x_usuarios')
            ->where('UsuarioId', $usuarioId)
            ->where('ModuloId', $modulo->IdModulo)
            ->first();

        if ($permiso && isset($permiso->{$accion}) && $permiso->{$accion} == 1) {
            return true;
        }

        // Fallback a permisos por rol
        $usuarioTipo = DB::table('usuarios')
            ->select('UsuarioTipo_Id')
            ->where('IdUsuario', $usuarioId)
            ->first();

        if ($usuarioTipo && $usuarioTipo->UsuarioTipo_Id) {
            $permisoRol = DB::table('permisos_x_tipos_usuarios')
                ->where('TipoUsuarioId', $usuarioTipo->UsuarioTipo_Id)
                ->where('ModuloId', $modulo->IdModulo)
                ->first();
            return $permisoRol && isset($permisoRol->{$accion}) && $permisoRol->{$accion} == 1;
        }

        return false;
    }

    /**
     * Obtiene los permisos del usuario para un módulo específico
     *
     * @param int $usuarioId ID del usuario
     * @param string $modulo Nombre del módulo
     * @return array Arreglo con los permisos (crear, leer, editar, eliminar)
     */
    public static function obtenerPermisos($usuarioId, $modulo)
    {
        // Super Admin: permisos CRUD completos
        if (self::esSuperAdmin($usuarioId)) {
            return [
                'C' => true,
                'R' => true,
                'U' => true,
                'D' => true,
                'crear' => true,
                'leer' => true,
                'editar' => true,
                'eliminar' => true,
            ];
        }
        // Primero intentar con el prefijo laravel-
        $moduloDb = DB::table('modulos')->where('Url', 'laravel-' . $modulo)->first();

        // Si no se encuentra, intentar sin el prefijo (para compatibilidad con módulos antiguos)
        if (!$moduloDb) {
            $moduloDb = DB::table('modulos')->where('Url', $modulo)->first();
        }

        // Si aún no se encuentra, intentar reemplazando guiones por guiones bajos

        if (!$moduloDb) {
            $moduloConGuionBajo = str_replace('-', '_', $modulo);
            $moduloDb = DB::table('modulos')->where('Url', $moduloConGuionBajo)->first();
        }

        if (!$moduloDb) {
            return [
                'C' => false,
                'R' => false,
                'U' => false,
                'D' => false,
                'crear' => false,
                'leer' => false,
                'editar' => false,
                'eliminar' => false,
            ];
        }

        // Permisos CRUD: prioriza específicos por usuario, luego por rol
        $permiso = DB::table('permisos_x_usuarios')
            ->where('UsuarioId', $usuarioId)
            ->where('ModuloId', $moduloDb->IdModulo)
            ->first();

        $permisoRol = null;
        if (!$permiso) {
            $usuarioTipo = DB::table('usuarios')
                ->select('UsuarioTipo_Id')
                ->where('IdUsuario', $usuarioId)
                ->first();

            if ($usuarioTipo && $usuarioTipo->UsuarioTipo_Id) {
                $permisoRol = DB::table('permisos_x_tipos_usuarios')
                    ->where('TipoUsuarioId', $usuarioTipo->UsuarioTipo_Id)
                    ->where('ModuloId', $moduloDb->IdModulo)
                    ->first();
            }
        }

        // Obtener permisos extras por ROL como fallback si el usuario no lo tiene
        $usuarioTipo = DB::table('usuarios')
            ->select('UsuarioTipo_Id')
            ->where('IdUsuario', $usuarioId)
            ->first();

        $permisosExtrasRol = collect();

        if ($usuarioTipo && $usuarioTipo->UsuarioTipo_Id) {

            $permisosExtrasRol = DB::table('permisos_extras_x_tipos_usuarios')
                ->join('permisos_extras', 'permisos_extras_x_tipos_usuarios.PermisoExtraId', '=', 'permisos_extras.IdPermisoExtra')
                ->where('permisos_extras_x_tipos_usuarios.TipoUsuarioId', $usuarioTipo->UsuarioTipo_Id)
                ->where('permisos_extras.ModuloId', $moduloDb->IdModulo)
                ->select('permisos_extras.Clave', 'permisos_extras_x_tipos_usuarios.Permiso')
                ->get();
        }

        $permisos = [
            'C' => ($permiso?->C == 1) || ($permisoRol?->C == 1),
            'R' => ($permiso?->R == 1) || ($permisoRol?->R == 1),
            'U' => ($permiso?->U == 1) || ($permisoRol?->U == 1),
            'D' => ($permiso?->D == 1) || ($permisoRol?->D == 1),
            'crear' => ($permiso?->C == 1) || ($permisoRol?->C == 1),
            'leer' => ($permiso?->R == 1) || ($permisoRol?->R == 1),
            'editar' => ($permiso?->U == 1) || ($permisoRol?->U == 1),
            'eliminar' => ($permiso?->D == 1) || ($permisoRol?->D == 1),
        ];

        // Agregar permisos extras únicamente por ROL
        foreach ($permisosExtrasRol as $permisoExtraRol) {
            $permisos[$permisoExtraRol->Clave] = $permisoExtraRol->Permiso == 1;
        }
        //   dd($permisos,$permisosExtras);
        return $permisos;
    }

    /**
     * Verifica si un usuario tiene un permiso extra específico
     *
     * @param int $usuarioId ID del usuario
     * @param string $clavePermiso Clave del permiso extra a verificar
     * @return bool True si tiene el permiso, false en caso contrario
     */
    public static function tienePermisoExtra($usuarioId, $clavePermiso)
    {
        // Buscar el permiso extra por clave
        $permisoExtra = DB::table('permisos_extras')
            ->where('Clave', $clavePermiso)
            ->first();

        if (!$permisoExtra) {
            return false;
        }

        // Permisos extras por rol (única fuente)
        $usuarioTipo = DB::table('usuarios')
            ->select('UsuarioTipo_Id')
            ->where('IdUsuario', $usuarioId)
            ->first();
        if ($usuarioTipo && $usuarioTipo->UsuarioTipo_Id) {
            $permisoRolExtra = DB::table('permisos_extras_x_tipos_usuarios')
                ->where('TipoUsuarioId', $usuarioTipo->UsuarioTipo_Id)
                ->where('PermisoExtraId', $permisoExtra->IdPermisoExtra)
                ->first();
            return $permisoRolExtra && $permisoRolExtra->Permiso == 1;
        }

        return false;
    }

    /**
     * Obtiene los permisos del usuario para una ruta específica
     *
     * @param int $usuarioId ID del usuario
     * @param string $ruta Ruta actual
     * @return array
     */
    public static function obtenerPermisosPorRuta($usuarioId, $ruta)
    {
        // Super Admin: permisos CRUD completos por cualquier ruta
        if (self::esSuperAdmin($usuarioId)) {
            return [
                'crear' => true,
                'leer' => true,
                'editar' => true,
                'eliminar' => true,
            ];
        }
        // Buscar el módulo correspondiente a la ruta actual
        $modulo = DB::table('modulos')
            ->where('Url', $ruta)
            ->first();

        // Si no se encuentra el módulo, devolver permisos vacíos
        if (!$modulo) {
            return [
                'crear' => false,
                'leer' => false,
                'editar' => false,
                'eliminar' => false,
            ];
        }

        // Obtener los permisos del usuario para el módulo
        $permiso = DB::table('permisos_x_usuarios')
            ->where('UsuarioId', $usuarioId)
            ->where('ModuloId', $modulo->IdModulo)
            ->first();

        // Si no hay permisos específicos, devolver permisos vacíos
        if (!$permiso) {
            return [
                'crear' => false,
                'leer' => false,
                'editar' => false,
                'eliminar' => false,
            ];
        }

        // Devolver los permisos formateados
        return [
            'crear' => (bool)$permiso->C,
            'leer' => (bool)$permiso->R,
            'editar' => (bool)$permiso->U,
            'eliminar' => (bool)$permiso->D,
            'moduloId' => $modulo->IdModulo
        ];
    }

    /**
     * Obtiene todos los permisos del usuario para todos los módulos
     *
     * @param int $usuarioId ID del usuario
     * @return array
     */
    public static function obtenerTodosLosPermisos($usuarioId)
    {
        // Super Admin: todos los módulos Laravel con CRUD completo
        if (self::esSuperAdmin($usuarioId)) {
            $modulosDb = DB::table('modulos')
                ->where('Url', 'like', '%laravel%')
                ->select('IdModulo')
                ->get();
            $permisos = [];
            foreach ($modulosDb as $m) {
                $permisos[] = [
                    'moduloId' => $m->IdModulo,
                    'crear' => true,
                    'leer' => true,
                    'editar' => true,
                    'eliminar' => true,
                ];
            }
            return $permisos;
        }
        // Obtener todos los permisos del usuario (específicos)
        $permisosDb = DB::table('permisos_x_usuarios')
            ->join('modulos', 'permisos_x_usuarios.ModuloId', '=', 'modulos.IdModulo')
            ->where('permisos_x_usuarios.UsuarioId', $usuarioId)
            ->where('modulos.Url', 'like', '%laravel%')
            ->select('permisos_x_usuarios.*', 'modulos.Url')
            ->get();

        $permisos = [];

        foreach ($permisosDb as $permiso) {
            $permisos[] = [
                'moduloId' => $permiso->ModuloId,
                'crear' => (bool)$permiso->C,
                'leer' => (bool)$permiso->R,
                'editar' => (bool)$permiso->U,
                'eliminar' => (bool)$permiso->D
            ];
        }

        // Agregar permisos por rol (fallback) para módulos Laravel donde no haya permiso específico
        $usuarioTipo = DB::table('usuarios')
            ->select('UsuarioTipo_Id')
            ->where('IdUsuario', $usuarioId)
            ->first();

        if ($usuarioTipo && $usuarioTipo->UsuarioTipo_Id) {
            $permisosRolDb = DB::table('permisos_x_tipos_usuarios')
                ->join('modulos', 'permisos_x_tipos_usuarios.ModuloId', '=', 'modulos.IdModulo')
                ->where('permisos_x_tipos_usuarios.TipoUsuarioId', $usuarioTipo->UsuarioTipo_Id)
                ->where('modulos.Url', 'like', '%laravel%')
                ->select('permisos_x_tipos_usuarios.*', 'modulos.IdModulo as ModuloId')
                ->get();

            foreach ($permisosRolDb as $pr) {
                $existe = false;
                foreach ($permisos as $p) {
                    if ($p['moduloId'] == $pr->ModuloId) {
                        $existe = true;
                        break;
                    }
                }
                if (!$existe) {
                    $permisos[] = [
                        'moduloId' => $pr->ModuloId,
                        'crear' => (bool)$pr->C,
                        'leer' => (bool)$pr->R,
                        'editar' => (bool)$pr->U,
                        'eliminar' => (bool)$pr->D
                    ];
                }
            }
        }

        return $permisos;
    }

    /**
     * Método auxiliar para mantener compatibilidad con las vistas blade
     * Convierte un path de Laravel a formato de módulo y llama al método original
     *
     * @param int $usuarioId ID del usuario
     * @param string $path Ruta actual de Laravel (ej: "modelo-ejemplo")
     * @return array Permisos formateados
     */
    public static function obtenerPermisosPorPath($usuarioId, $path)
    {
        // Limpiar el path para obtener solo el nombre del módulo
        $modulo = explode('/', $path)[0];

        // Llamar al método original con el módulo
        return self::obtenerPermisos($usuarioId, $modulo);
    }
}
