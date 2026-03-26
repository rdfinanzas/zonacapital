<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\Region;

// Ruta de debug para dengue sin middleware
Route::get('/debug-dengue', function() {
    try {
        // Simular sesión de usuario
        Session::put('usuario_id', 1);
        $usuarioId = Session::get('usuario_id');
        
        echo "<h2>DEBUG - Registros de Dengue</h2>";
        echo "<p><strong>Usuario ID de sesión:</strong> " . ($usuarioId ?? 'NO HAY SESIÓN') . "</p>";
        
        // Test 1: Verificar tabla servicios
        echo "<h3>1. Servicios (con Region_Id != 0)</h3>";
        $servicios = Servicio::where('Region_Id', '!=', 0)->get();
        echo "<p><strong>Total servicios:</strong> " . $servicios->count() . "</p>";
        if ($servicios->count() > 0) {
            echo "<ul>";
            foreach ($servicios->take(5) as $s) {
                echo "<li>ID: {$s->idServicio} - {$s->servicio} (Region: {$s->Region_Id})</li>";
            }
            echo "</ul>";
        }
        
        // Test 2: Verificar tabla usuarios
        echo "<h3>2. Usuarios</h3>";
        $usuarios = Usuario::all();
        echo "<p><strong>Total usuarios:</strong> " . $usuarios->count() . "</p>";
        if ($usuarios->count() > 0) {
            echo "<ul>";
            foreach ($usuarios->take(5) as $u) {
                echo "<li>ID: {$u->IdUsuario} - {$u->Usuario} ({$u->Apellido}, {$u->Nombre})</li>";
            }
            echo "</ul>";
        }
        
        // Test 3: Verificar tabla regiones
        echo "<h3>3. Regiones</h3>";
        $regiones = Region::all();
        echo "<p><strong>Total regiones:</strong> " . $regiones->count() . "</p>";
        if ($regiones->count() > 0) {
            echo "<ul>";
            foreach ($regiones->take(5) as $r) {
                echo "<li>ID: {$r->IdRegion} - {$r->Region}</li>";
            }
            echo "</ul>";
        }
        
        // Test 4: Verificar efector del usuario
        echo "<h3>4. Efector del Usuario</h3>";
        $efectorQuery = DB::table('empleados as emp')
            ->join('usuarios as u', 'u.Personal_Id', '=', 'emp.idEmpleado')
            ->join('servicio as serv', 'serv.idServicio', '=', 'emp.idServicio')
            ->where('u.IdUsuario', $usuarioId)
            ->select('serv.*', 'u.Usuario', 'emp.Apellido', 'emp.Nombre')
            ->first();
        
        if ($efectorQuery) {
            echo "<p><strong>Efector encontrado:</strong></p>";
            echo "<ul>";
            echo "<li>Usuario: {$efectorQuery->Usuario}</li>";
            echo "<li>Empleado: {$efectorQuery->Apellido}, {$efectorQuery->Nombre}</li>";
            echo "<li>Servicio: {$efectorQuery->servicio} (ID: {$efectorQuery->idServicio})</li>";
            echo "<li>Región: {$efectorQuery->Region_Id}</li>";
            echo "</ul>";
        } else {
            echo "<p><strong>No se encontró efector para el usuario {$usuarioId}</strong></p>";
            
            // Debug adicional
            $usuario = DB::table('usuarios')->where('IdUsuario', $usuarioId)->first();
            if ($usuario) {
                echo "<p>Usuario existe: {$usuario->Usuario} (Personal_Id: {$usuario->Personal_Id})</p>";
                
                $empleado = DB::table('empleados')->where('idEmpleado', $usuario->Personal_Id)->first();
                if ($empleado) {
                    echo "<p>Empleado existe: {$empleado->Apellido}, {$empleado->Nombre} (Servicio: {$empleado->idServicio})</p>";
                } else {
                    echo "<p>No se encontró empleado con ID {$usuario->Personal_Id}</p>";
                }
            } else {
                echo "<p>No existe usuario con ID {$usuarioId}</p>";
            }
        }
        
        // Test 5: Verificar tablas registros_dengue
        echo "<h3>5. Tabla registros_dengue</h3>";
        $countDengue = DB::table('registros_dengue')->count();
        echo "<p><strong>Total registros dengue:</strong> " . $countDengue . "</p>";
        
        // Test 6: Verificar tabla pacientes_reg_trabajo
        echo "<h3>6. Tabla pacientes_reg_trabajo</h3>";
        $countPacientes = DB::table('pacientes_reg_trabajo')->count();
        echo "<p><strong>Total pacientes:</strong> " . $countPacientes . "</p>";
        
    } catch (Exception $e) {
        echo "<h2 style='color: red;'>ERROR: " . $e->getMessage() . "</h2>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
});