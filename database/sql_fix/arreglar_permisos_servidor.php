<?php
/**
 * SCRIPT PARA ARREGLAR PERMISOS DE CONTROL DE HORARIOS EN SERVIDOR
 *
 * Este script verifica y crea automáticamente los permisos necesarios
 * para que el módulo de Control de Horarios funcione igual que en local.
 *
 * USO:
 * 1. Subir este archivo al servidor
 * 2. Ejecutar: php arreglar_permisos_servidor.php
 * 3. Eliminar el archivo después de usarlo
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ARREGlando PERMISOS DE CONTROL HORARIOS ===" . PHP_EOL . PHP_EOL;

// ============================================================
// PASO 1: Verificar/Crear módulo
// ============================================================
echo "1. Verificando módulo 'laravel-control-horarios'..." . PHP_EOL;

$modulo = DB::table('modulos')->where('Url', 'laravel-control-horarios')->first();

if (!$modulo) {
    echo "   → Módulo NO encontrado. Creándolo..." . PHP_EOL;
    $idModulo = DB::table('modulos')->insertGetId([
        'Label' => 'Control de Horarios',
        'Url' => 'laravel-control-horarios',
        'Icono' => 'fas fa-clock',
        'ModuloPadreId' => 0,
        'Orden' => 0,
        'Padre' => 0
    ]);
    echo "   ✓ Módulo creado con ID: {$idModulo}" . PHP_EOL;
} else {
    $idModulo = $modulo->IdModulo;
    echo "   ✓ Módulo encontrado con ID: {$idModulo}" . PHP_EOL;
}

// ============================================================
// PASO 2: Verificar/Crear permiso extra
// ============================================================
echo PHP_EOL . "2. Verificando permiso extra 'todo_personal_control'..." . PHP_EOL;

$permisoExtra = DB::table('permisos_extras')
    ->where('Clave', 'todo_personal_control')
    ->first();

if (!$permisoExtra) {
    echo "   → Permiso extra NO encontrado. Creándolo..." . PHP_EOL;
    $idPermisoExtra = DB::table('permisos_extras')->insertGetId([
        'PermisoExtra' => 'Ver todo el personal',
        'ModuloId' => $idModulo,
        'Clave' => 'todo_personal_control'
    ]);
    echo "   ✓ Permiso extra creado con ID: {$idPermisoExtra}" . PHP_EOL;
} else {
    $idPermisoExtra = $permisoExtra->IdPermisoExtra;

    // Verificar si apunta al módulo correcto
    if ($permisoExtra->ModuloId != $idModulo) {
        echo "   → Permiso extra apunta al módulo incorrecto ({$permisoExtra->ModuloId}). Actualizando..." . PHP_EOL;
        DB::table('permisos_extras')
            ->where('IdPermisoExtra', $idPermisoExtra)
            ->update(['ModuloId' => $idModulo]);
        echo "   ✓ Permiso extra actualizado al módulo correcto: {$idModulo}" . PHP_EOL;
    } else {
        echo "   ✓ Permiso extra encontrado con ID: {$idPermisoExtra} (correcto)" . PHP_EOL;
    }
}

// ============================================================
// PASO 3: Verificar/Asignar permiso al rol ADMIN
// ============================================================
echo PHP_EOL . "3. Verificando asignación al rol ADMIN..." . PHP_EOL;

$asignacion = DB::table('permisos_extras_x_tipos_usuarios')
    ->where('TipoUsuarioId', 1)
    ->where('PermisoExtraId', $idPermisoExtra)
    ->first();

if (!$asignacion) {
    echo "   → Rol ADMIN NO tiene el permiso. Asignándolo..." . PHP_EOL;
    DB::table('permisos_extras_x_tipos_usuarios')->insert([
        'TipoUsuarioId' => 1,
        'PermisoExtraId' => $idPermisoExtra,
        'Permiso' => 1
    ]);
    echo "   ✓ Permiso asignado al rol ADMIN" . PHP_EOL;
} elseif ($asignacion->Permiso != 1) {
    echo "   → Rol ADMIN tiene el permiso desactivado. Activándolo..." . PHP_EOL;
    DB::table('permisos_extras_x_tipos_usuarios')
        ->where('TipoUsuarioId', 1)
        ->where('PermisoExtraId', $idPermisoExtra)
        ->update(['Permiso' => 1]);
    echo "   ✓ Permiso activado para el rol ADMIN" . PHP_EOL;
} else {
    echo "   ✓ Rol ADMIN ya tiene el permiso activado" . PHP_EOL;
}

// ============================================================
// PASO 4: Verificar/Asignar permisos CRUD al rol ADMIN
// ============================================================
echo PHP_EOL . "4. Verificando permisos CRUD para el módulo..." . PHP_EOL;

$permisosCrud = DB::table('permisos_x_tipos_usuarios')
    ->where('TipoUsuarioId', 1)
    ->where('ModuloId', $idModulo)
    ->first();

if (!$permisosCrud) {
    echo "   → Rol ADMIN NO tiene permisos CRUD. Asignándolos..." . PHP_EOL;
    DB::table('permisos_x_tipos_usuarios')->insert([
        'TipoUsuarioId' => 1,
        'ModuloId' => $idModulo,
        'C' => 1,
        'R' => 1,
        'U' => 1,
        'D' => 1
    ]);
    echo "   ✓ Permisos CRUD completos asignados al rol ADMIN" . PHP_EOL;
} else {
    echo "   ✓ Rol ADMIN ya tiene permisos CRUD (C:{$permisosCrud->C} R:{$permisosCrud->R} U:{$permisosCrud->U} D:{$permisosCrud->D})" . PHP_EOL;
}

// ============================================================
// RESUMEN FINAL
// ============================================================
echo PHP_EOL . "=== RESUMEN FINAL ===" . PHP_EOL;
echo "Módulo ID: {$idModulo}" . PHP_EOL;
echo "Permiso Extra ID: {$idPermisoExtra}" . PHP_EOL;
echo "Rol ADMIN (ID: 1) tiene permiso 'todo_personal_control': ";
$check = DB::table('permisos_extras_x_tipos_usuarios')
    ->where('TipoUsuarioId', 1)
    ->where('PermisoExtraId', $idPermisoExtra)
    ->where('Permiso', 1)
    ->exists();
echo ($check ? "✓ SI" : "✗ NO") . PHP_EOL;

echo PHP_EOL . "=== PROXIMO PASO ===" . PHP_EOL;
echo "1. Verifica que tu usuario en el servidor tenga el rol ADMIN (UsuarioTipo_Id = 1)" . PHP_EOL;
echo "2. Si tu usuario es Super Admin (UsuarioTipo_Id = -1), ya debería funcionar" . PHP_EOL;
echo "3. Si tu usuario tiene otro rol, asigna el permiso a ese rol:" . PHP_EOL;
echo "   INSERT IGNORE INTO permisos_extras_x_tipos_usuarios (TipoUsuarioId, PermisoExtraId, Permiso)" . PHP_EOL;
echo "   VALUES (ID_DE_TU_ROL, {$idPermisoExtra}, 1);" . PHP_EOL;

echo PHP_EOL . "=== ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO ===" . PHP_EOL;
