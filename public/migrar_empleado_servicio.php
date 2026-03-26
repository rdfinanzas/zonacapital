<?php
// Script de migración: empleados.idServicio -> empleado_servicio
// Copiar este archivo a la carpeta public/ y ejecutar desde el navegador

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Migración: empleados -> empleado_servicio</h1>";

try {
    // Verificar cuántos se van a migrar
    $countSql = "SELECT COUNT(*) as total FROM empleados e 
                 WHERE e.idServicio > 0 
                 AND e.Estado = 1 
                 AND e.idEmpleado NOT IN (
                     SELECT DISTINCT empleado_id FROM empleado_servicio WHERE activo = 1
                 )
                 AND e.idEmpleado NOT IN (
                     SELECT idEmpleado FROM empleados WHERE FBaja > '2000-01-01'
                 )";
    
    $aMigrar = DB::select($countSql);
    $total = $aMigrar[0]->total ?? 0;
    
    echo "<p>Empleados a migrar: <strong>$total</strong></p>";
    
    if ($total > 0) {
        // Ejecutar migración
        $insertSql = "INSERT INTO empleado_servicio (
                          empleado_id, servicio_id, certificador_id, fecha_inicio,
                          activo, motivo, created_at, updated_at
                      )
                      SELECT 
                          e.idEmpleado, e.idServicio, e.IdEmpleado2, '2020-01-01',
                          1, 'Migracion automatica desde tabla empleados (legacy)',
                          NOW(), NOW()
                      FROM empleados e
                      WHERE e.idServicio > 0 
                      AND e.Estado = 1 
                      AND e.idEmpleado NOT IN (
                          SELECT DISTINCT empleado_id FROM empleado_servicio WHERE activo = 1
                      )
                      AND e.idEmpleado NOT IN (
                          SELECT idEmpleado FROM empleados WHERE FBaja > '2000-01-01'
                      )";
        
        DB::statement($insertSql);
        
        echo "<p style='color:green'><strong>¡Migración completada!</strong></p>";
    } else {
        echo "<p>No hay empleados para migrar.</p>";
    }
    
    // Verificar total en empleado_servicio
    $totalAhora = DB::table('empleado_servicio')->count();
    echo "<p>Total registros en empleado_servicio: <strong>$totalAhora</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr><p><a href='/'>Volver al inicio</a></p>";
