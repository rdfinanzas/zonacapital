<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ModuloController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Usuarios autocomplete endpoint
Route::get('/usuarios-autocomplete', [UsuarioController::class, 'usuariosAutocomplete']);

// Rutas para el módulo de gestión de módulos y permisos
Route::prefix('modulos')->middleware('session.auth')->group(function () {
    // Obtener permisos extras agrupados por módulo
    Route::get('/permisos-extras', [ModuloController::class, 'getPermisosExtras']);

    // Obtener todos los módulos disponibles para usuarios
    Route::get('/usuarios', [ModuloController::class, 'getModulosUsuarios']);

    // Obtener menú con permisos para el usuario autenticado
    Route::get('/menu-permisos', [ModuloController::class, 'getMenuPermisos']);

    // Verificar permiso de acceso a un módulo específico
    Route::post('/check-permiso', [ModuloController::class, 'checkPermisoModulo']);
});

// Ejemplo de uso del módulo migrado
Route::prefix('ejemplo-modulo')->middleware('session.auth')->group(function () {
    Route::get('/listar', [App\Http\Controllers\EjemploModuloController::class, 'listar']);
    Route::post('/crear', [App\Http\Controllers\EjemploModuloController::class, 'crear']);
    Route::put('/editar/{id}', [App\Http\Controllers\EjemploModuloController::class, 'editar']);
    Route::delete('/eliminar/{id}', [App\Http\Controllers\EjemploModuloController::class, 'eliminar']);
});

// Rutas para el módulo de control de horarios
Route::prefix('control-horarios')->middleware('check.session')->group(function () {
    Route::get('/', [App\Http\Controllers\ControlHorariosController::class, 'listar']);
    Route::post('/marcas', [App\Http\Controllers\ControlHorariosController::class, 'actualizarMarca']);
    Route::get('/departamentos/{id}', [App\Http\Controllers\ControlHorariosController::class, 'departamentos']);
    Route::get('/servicios/{id}', [App\Http\Controllers\ControlHorariosController::class, 'servicios']);
    Route::get('/exportar-excel', [App\Http\Controllers\ControlHorariosController::class, 'exportarExcel']);
    Route::get('/exportar-pdf', [App\Http\Controllers\ControlHorariosController::class, 'exportarPdf']);
});

// Rutas para el módulo de programación del personal
Route::prefix('programacion-personal')->middleware('check.session')->group(function () {
    // Obtener programación de horarios
    Route::get('/datos', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerProgramacion']);
    Route::get('/obtener', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerProgramacion']);

    // Guardar programación
    Route::post('/guardar', [App\Http\Controllers\ProgramacionPersonalController::class, 'guardarProgramacion']);
    Route::post('/guardar-horario-dia', [App\Http\Controllers\ProgramacionPersonalController::class, 'guardarHorarioDia']);
    Route::post('/guardar-simple', [App\Http\Controllers\ProgramacionPersonalController::class, 'guardarProgramacionSimple']);

    // Obtener departamentos por gerencia
    Route::get('/departamentos/{id}', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerDepartamentosXGerencia']);

    // Obtener servicios por departamento
    Route::get('/servicios/{id}', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerServiciosXDepartamento']);
});
