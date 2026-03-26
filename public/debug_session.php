<?php
// debug_session.php - Script para verificar el estado de la sesión y permisos
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug de Sesión y Permisos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
        .card { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 15px 0; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { padding: 8px; border: 1px solid #dee2e6; text-align: left; }
        table th { background: #e9ecef; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔍 Debug de Sesión y Permisos - Personal</h1>

    <?php
    // Obtener usuario de sesión
    $usuarioId = session('usuario_id');

    if (!$usuarioId): ?>
        <div class="card error">
            <h3>❌ Sin Sesión Activa</h3>
            <p>No hay usuario en sesión. Necesitas autenticarte primero.</p>
            <a href="test_personal_access.html" class="btn">🔑 Ir a Login</a>
        </div>

        <div class="card warning">
            <h3>⚠️ Usuarios Disponibles para Prueba</h3>
            <?php
            $usuarios = DB::table('usuarios')
                ->select('IdUsuario', 'Usuario', 'Nombre', 'Apellido', 'UsuarioTipo_Id')
                ->where('Usuario', '30362296')
                ->get();
            ?>
            <table>
                <tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Tipo</th></tr>
                <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td><?= $user->IdUsuario ?></td>
                    <td><?= $user->Usuario ?></td>
                    <td><?= $user->Nombre ?> <?= $user->Apellido ?></td>
                    <td><?= $user->UsuarioTipo_Id ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

    <?php else:
        // Usuario logueado - verificar permisos
        $usuario = DB::table('usuarios')->where('IdUsuario', $usuarioId)->first();
        ?>

        <div class="card success">
            <h3>✅ Sesión Activa</h3>
            <p><strong>Usuario ID:</strong> <?= $usuarioId ?></p>
            <?php if ($usuario): ?>
                <p><strong>Nombre:</strong> <?= $usuario->Nombre ?> <?= $usuario->Apellido ?></p>
                <p><strong>Usuario:</strong> <?= $usuario->Usuario ?></p>
                <p><strong>Tipo:</strong> <?= $usuario->UsuarioTipo_Id ?></p>
            <?php endif; ?>
        </div>

        <?php
        // Verificar permisos
        $permisos = \App\Helpers\PermisoHelper::obtenerPermisos($usuarioId, 'personal');
        ?>

        <div class="card <?= $permisos['leer'] ? 'success' : 'error' ?>">
            <h3>🔐 Permisos para Personal</h3>
            <table>
                <tr><th>Permiso</th><th>Estado</th></tr>
                <tr><td>Crear</td><td><?= $permisos['crear'] ? '✅ SÍ' : '❌ NO' ?></td></tr>
                <tr><td>Leer</td><td><?= $permisos['leer'] ? '✅ SÍ' : '❌ NO' ?></td></tr>
                <tr><td>Editar</td><td><?= $permisos['editar'] ? '✅ SÍ' : '❌ NO' ?></td></tr>
                <tr><td>Eliminar</td><td><?= $permisos['eliminar'] ? '✅ SÍ' : '❌ NO' ?></td></tr>
            </table>
        </div>

        <?php if ($permisos['leer']): ?>
            <div class="card success">
                <h3>🎉 Todo Correcto</h3>
                <p>Tienes permisos de lectura. Puedes acceder al módulo de personal.</p>
                <a href="/personal" class="btn">👥 Ir a Personal</a>
                <a href="/personal/1/ver" class="btn">👁️ Ver Personal (Test)</a>
            </div>
        <?php else: ?>
            <div class="card error">
                <h3>❌ Sin Permisos</h3>
                <p>No tienes permisos de lectura para el módulo personal.</p>

                <?php
                // Verificar permisos específicos
                $permisosEspecificos = DB::table('permisos_x_usuarios')
                    ->where('UsuarioId', $usuarioId)
                    ->whereIn('ModuloId', [75, 84])
                    ->get();
                ?>

                <h4>Permisos Específicos del Usuario:</h4>
                <?php if ($permisosEspecificos->count() > 0): ?>
                    <table>
                        <tr><th>Módulo ID</th><th>C</th><th>R</th><th>U</th><th>D</th></tr>
                        <?php foreach ($permisosEspecificos as $p): ?>
                        <tr>
                            <td><?= $p->ModuloId ?></td>
                            <td><?= $p->C ?></td>
                            <td><?= $p->R ?></td>
                            <td><?= $p->U ?></td>
                            <td><?= $p->D ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>No hay permisos específicos configurados.</p>
                <?php endif; ?>

                <?php
                // Verificar permisos por rol
                if ($usuario && $usuario->UsuarioTipo_Id) {
                    $permisosRol = DB::table('permisos_x_tipos_usuarios')
                        ->where('TipoUsuarioId', $usuario->UsuarioTipo_Id)
                        ->whereIn('ModuloId', [75, 84])
                        ->get();
                ?>
                    <h4>Permisos por Rol (Tipo <?= $usuario->UsuarioTipo_Id ?>):</h4>
                    <?php if ($permisosRol->count() > 0): ?>
                        <table>
                            <tr><th>Módulo ID</th><th>C</th><th>R</th><th>U</th><th>D</th></tr>
                            <?php foreach ($permisosRol as $pr): ?>
                            <tr>
                                <td><?= $pr->ModuloId ?></td>
                                <td><?= $pr->C ?></td>
                                <td><?= $pr->R ?></td>
                                <td><?= $pr->U ?></td>
                                <td><?= $pr->D ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>No hay permisos por rol configurados.</p>
                    <?php endif; ?>
                <?php } ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <div class="card info">
        <h3>ℹ️ Información de Debug</h3>
        <p><strong>Session ID:</strong> <?= session()->getId() ?></p>
        <p><strong>CSRF Token:</strong> <?= csrf_token() ?></p>
        <p><strong>Hora:</strong> <?= date('Y-m-d H:i:s') ?></p>

        <h4>Variables de Sesión:</h4>
        <pre><?php print_r(session()->all()); ?></pre>
    </div>

    <div class="card">
        <h3>🔧 Acciones de Debug</h3>
        <a href="<?= url('/gateway/login') ?>" class="btn">🔑 Gateway Login</a>
        <a href="<?= url('/personal') ?>" class="btn">👥 Módulo Personal</a>
        <a href="test_personal_access.html" class="btn">📝 Formulario de Acceso</a>
        <button onclick="location.reload()" class="btn">🔄 Recargar</button>
    </div>
</body>
</html>
