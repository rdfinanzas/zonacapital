<?php
// debug_permisos_ruta.php - Debug específico para verificar qué está pasando con los permisos
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simular una request HTTP para inicializar Laravel
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Debug Permisos por Ruta</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; }
        .card { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 15px 0; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { padding: 8px; border: 1px solid #dee2e6; text-align: left; }
        table th { background: #e9ecef; }
    </style>
</head>
<body>
    <h1>🔍 Debug Detallado de Permisos</h1>

    <?php
    // Simular diferentes rutas para ver cómo se comportan los permisos
    $usuarioId = session('usuario_id');

    if (!$usuarioId) {
        // Intentar obtener usuario de Laravel Auth
        if (auth()->check()) {
            $usuarioId = auth()->user()->IdUsuario ?? auth()->id();
            echo "<div class='card success'><p>✅ Usuario obtenido de Laravel Auth: $usuarioId</p></div>";
        } else {
            echo "<div class='card error'><p>❌ No hay usuario en sesión ni en Laravel Auth</p></div>";
            exit;
        }
    }

    echo "<div class='card success'><p>✅ Usuario ID: $usuarioId</p></div>";

    // Simular diferentes rutas para ver qué pasa
    $rutasPrueba = [
        'personal',
        'personal/1/ver',
        'laravel-personal',
        request()->path()
    ];

    echo "<h2>🧪 Pruebas de Permisos por Ruta</h2>";

    foreach ($rutasPrueba as $ruta) {
        echo "<div class='card'>";
        echo "<h3>Ruta: '$ruta'</h3>";

        try {
            // Obtener permisos usando el helper
            $permisos = \App\Helpers\PermisoHelper::obtenerPermisos($usuarioId, $ruta);

            echo "<table>";
            echo "<tr><th>Permiso</th><th>Valor</th></tr>";
            foreach ($permisos as $key => $value) {
                $valor = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                $clase = $value ? 'success' : 'error';
                echo "<tr class='$clase'><td>$key</td><td>$valor</td></tr>";
            }
            echo "</table>";

            // Probar método específico tienePermiso
            $tieneR = \App\Helpers\PermisoHelper::tienePermiso($usuarioId, 'R');
            echo "<p><strong>tienePermiso(R):</strong> " . ($tieneR ? '✅ true' : '❌ false') . "</p>";

        } catch (Exception $e) {
            echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }

        echo "</div>";
    }

    // Debug específico del middleware
    echo "<h2>🔧 Debug del Middleware PuedeVer</h2>";
    echo "<div class='card'>";

    echo "<p><strong>Ruta actual:</strong> " . request()->path() . "</p>";

    // Simular lo que hace el middleware
    try {
        $rutaActual = request()->path();
        echo "<p><strong>Verificando ruta:</strong> $rutaActual</p>";

        $tienePermisoR = \App\Helpers\PermisoHelper::tienePermiso($usuarioId, 'R');
        echo "<p><strong>Resultado tienePermiso('R'):</strong> " . ($tienePermisoR ? '✅ true' : '❌ false') . "</p>";

        if (!$tienePermisoR) {
            echo "<div class='error'><p>❌ El middleware bloquearía el acceso aquí</p></div>";

            // Intentar diagnosticar por qué falla
            echo "<h4>🔍 Diagnóstico del fallo:</h4>";

            // Verificar si es super admin
            $esSuperAdmin = \App\Helpers\PermisoHelper::esSuperAdmin($usuarioId);
            echo "<p>¿Es Super Admin? " . ($esSuperAdmin ? '✅ SÍ' : '❌ NO') . "</p>";

            if (!$esSuperAdmin) {
                // Buscar módulo
                $ruta = explode('/', $rutaActual)[0];
                echo "<p><strong>Ruta procesada:</strong> laravel-$ruta</p>";

                $modulo = DB::table('modulos')->where('Url', 'laravel-' . $ruta)->first();
                if ($modulo) {
                    echo "<p>✅ Módulo encontrado: {$modulo->Label} (ID: {$modulo->IdModulo})</p>";

                    // Verificar permisos específicos
                    $permisoEspecifico = DB::table('permisos_x_usuarios')
                        ->where('UsuarioId', $usuarioId)
                        ->where('ModuloId', $modulo->IdModulo)
                        ->first();

                    if ($permisoEspecifico) {
                        echo "<p>✅ Permiso específico encontrado: R={$permisoEspecifico->R}</p>";
                    } else {
                        echo "<p>❌ No hay permiso específico</p>";

                        // Verificar por rol
                        $usuario = DB::table('usuarios')->where('IdUsuario', $usuarioId)->first();
                        if ($usuario && $usuario->UsuarioTipo_Id) {
                            $permisoRol = DB::table('permisos_x_tipos_usuarios')
                                ->where('TipoUsuarioId', $usuario->UsuarioTipo_Id)
                                ->where('ModuloId', $modulo->IdModulo)
                                ->first();

                            if ($permisoRol) {
                                echo "<p>✅ Permiso por rol encontrado: R={$permisoRol->R}</p>";
                            } else {
                                echo "<p>❌ No hay permiso por rol</p>";
                            }
                        }
                    }
                } else {
                    echo "<p>❌ Módulo NO encontrado para: laravel-$ruta</p>";

                    // Buscar módulos similares
                    $modulosSimilares = DB::table('modulos')
                        ->where('Url', 'LIKE', "%$ruta%")
                        ->get();

                    if ($modulosSimilares->count() > 0) {
                        echo "<h5>Módulos similares encontrados:</h5>";
                        echo "<table>";
                        echo "<tr><th>ID</th><th>Label</th><th>URL</th></tr>";
                        foreach ($modulosSimilares as $mod) {
                            echo "<tr><td>{$mod->IdModulo}</td><td>{$mod->Label}</td><td>{$mod->Url}</td></tr>";
                        }
                        echo "</table>";
                    }
                }
            }
        } else {
            echo "<div class='success'><p>✅ El middleware permitiría el acceso</p></div>";
        }

    } catch (Exception $e) {
        echo "<div class='error'>Error en middleware: " . $e->getMessage() . "</div>";
    }

    echo "</div>";

    // Información adicional del usuario
    echo "<h2>👤 Información del Usuario</h2>";
    echo "<div class='card'>";

    $usuario = DB::table('usuarios')->where('IdUsuario', $usuarioId)->first();
    if ($usuario) {
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        foreach ((array)$usuario as $key => $value) {
            echo "<tr><td>$key</td><td>$value</td></tr>";
        }
        echo "</table>";
    }

    echo "</div>";
    ?>

    <div class="card">
        <h3>🔧 Enlaces de Prueba</h3>
        <p><a href="/personal">👥 Ir a Personal</a></p>
        <p><a href="/personal/1/ver">👁️ Ver Personal ID 1</a></p>
        <p><a href="/debug_session.php">🔍 Debug Sesión</a></p>
    </div>
</body>
</html>
