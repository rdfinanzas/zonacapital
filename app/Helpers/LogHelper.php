<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LogHelper
{
    /**
     * Constantes para los tipos de acciones en logs
     */
    const TIPO_INSERTAR = 1;
    const TIPO_UPDATE = 3;
    const TIPO_ELIMINAR = 4;

    /**
     * Crea un registro en la tabla de logs_usuarios
     *
     * @param string $mensaje Descripción de la acción realizada
     * @param int $tipo Tipo de acción: 1=insertar, 3=actualizar, 4=eliminar
     * @param string $tabla Nombre de la tabla afectada
     * @param string $modulo Nombre del módulo o sección de la aplicación
     * @param int|null $registro ID del registro afectado
     * @return int ID del registro de log creado
     */
    public static function create($mensaje, $tipo, $tabla, $modulo, $registro = null)
    {
        try {
            // Obtener el ID del usuario autenticado
            $usuarioId = session('usuario_id') ?? 0;

            // Insertar registro en la tabla de logs
            $id = DB::table('logs_usuarios')->insertGetId([
                'IP' => Request::ip(),
                'Mensaje' => $mensaje,
                'Tipo' => $tipo,
                'Tabla' => $tabla,
                'Fecha' => now(),
                'UsuarioId' => $usuarioId,
                'Modulo' => $modulo,
                'RegistroId' => $registro
            ]);

            return $id;
        } catch (\Exception $e) {
            // En producción, es mejor registrar este error en otro lugar 
            // (como el log de Laravel) en lugar de lanzar una excepción
            \Illuminate\Support\Facades\Log::error('Error al crear log: ' . $e->getMessage());

            // Devolver 0 para indicar error sin interrumpir el flujo de la aplicación
            return 0;
        }
    }

    /**
     * Registra una operación de inserción
     *
     * @param string $tabla Nombre de la tabla afectada
     * @param string $modulo Nombre del módulo o sección
     * @param int $registroId ID del registro insertado
     * @param string $mensaje Mensaje personalizado (opcional)
     * @return int ID del log creado
     */
    public static function insertar($tabla, $modulo, $registroId, $mensaje = null)
    {
        if ($mensaje === null) {
            $mensaje = "Se ha creado un nuevo registro en {$tabla}";
        }

        return self::create($mensaje, self::TIPO_INSERTAR, $tabla, $modulo, $registroId);
    }

    /**
     * Registra una operación de actualización
     *
     * @param string $tabla Nombre de la tabla afectada
     * @param string $modulo Nombre del módulo o sección
     * @param int $registroId ID del registro actualizado
     * @param string $mensaje Mensaje personalizado (opcional)
     * @return int ID del log creado
     */
    public static function actualizar($tabla, $modulo, $registroId, $mensaje = null)
    {
        if ($mensaje === null) {
            $mensaje = "Se ha actualizado el registro #{$registroId} en {$tabla}";
        }

        return self::create($mensaje, self::TIPO_UPDATE, $tabla, $modulo, $registroId);
    }

    /**
     * Registra una operación de eliminación
     *
     * @param string $tabla Nombre de la tabla afectada
     * @param string $modulo Nombre del módulo o sección
     * @param int $registroId ID del registro eliminado
     * @param string $mensaje Mensaje personalizado (opcional)
     * @return int ID del log creado
     */
    public static function eliminar($tabla, $modulo, $registroId, $mensaje = null)
    {
        if ($mensaje === null) {
            $mensaje = "Se ha eliminado el registro #{$registroId} de {$tabla}";
        }

        return self::create($mensaje, self::TIPO_ELIMINAR, $tabla, $modulo, $registroId);
    }
}
