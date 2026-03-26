<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ============================================
        // 1. AGREGAR MÓDULO LAR
        // ============================================
        
        // Buscar el módulo padre de "Licencias"
        $moduloLicencias = DB::table('modulos')
            ->where('Url', 'licencias')
            ->where('Padre', 0)
            ->first();
        
        if ($moduloLicencias) {
            $moduloPadreId = $moduloLicencias->ModuloPadreId;
            
            // Verificar si existe el módulo LAR
            $moduloLarExiste = DB::table('modulos')
                ->where('Url', 'licencias/lar')
                ->exists();
            
            if (!$moduloLarExiste) {
                // Obtener el último orden para el padre
                $ultimoOrden = DB::table('modulos')
                    ->where('ModuloPadreId', $moduloPadreId)
                    ->max('Orden') ?? 0;
                
                // Insertar módulo LAR
                $moduloLarId = DB::table('modulos')->insertGetId([
                    'Label' => 'LAR',
                    'Url' => 'licencias/lar',
                    'Icono' => 'fas fa-calendar-check',
                    'Padre' => 0,
                    'ModuloPadreId' => $moduloPadreId,
                    'Orden' => $ultimoOrden + 1
                ]);
                
                // Copiar permisos de "licencias" a "licencias/lar"
                $permisosUsuarios = DB::table('permisos_x_usuario')
                    ->where('ModuloP_Id', $moduloLicencias->IdModulo)
                    ->get();
                
                foreach ($permisosUsuarios as $permiso) {
                    DB::table('permisos_x_usuario')->insert([
                        'UsuarioP_Id' => $permiso->UsuarioP_Id,
                        'ModuloP_Id' => $moduloLarId,
                        'Ver' => $permiso->Ver,
                        'Crear' => $permiso->Crear,
                        'Editar' => $permiso->Editar,
                        'Eliminar' => $permiso->Eliminar
                    ]);
                }
            }
        }
        
        // ============================================
        // 2. AGREGAR MÓDULO MOTIVOS DE LICENCIA
        // ============================================
        
        // Buscar módulo padre para parámetros (puede ser Sistema o similar)
        $moduloSistema = DB::table('modulos')
            ->where('Label', 'Sistema')
            ->where('Padre', 1)
            ->first();
        
        $moduloPadreParams = $moduloSistema ? $moduloSistema->IdModulo : 
            (DB::table('modulos')->where('Padre', 1)->first()->IdModulo ?? 5);
        
        // Verificar si existe el módulo Motivos de Licencia
        $moduloMotivosExiste = DB::table('modulos')
            ->where('Url', 'motivos-licencia')
            ->exists();
        
        if (!$moduloMotivosExiste) {
            // Obtener el último orden
            $ultimoOrden = DB::table('modulos')
                ->where('ModuloPadreId', $moduloPadreParams)
                ->max('Orden') ?? 0;
            
            // Insertar módulo Motivos de Licencia
            $moduloMotivosId = DB::table('modulos')->insertGetId([
                'Label' => 'Motivos de Licencia',
                'Url' => 'motivos-licencia',
                'Icono' => 'fas fa-clipboard-list',
                'Padre' => 0,
                'ModuloPadreId' => $moduloPadreParams,
                'Orden' => $ultimoOrden + 1
            ]);
            
            // Copiar permisos del módulo Configuración a Motivos de Licencia
            $moduloConfig = DB::table('modulos')
                ->where('Url', 'configuracion')
                ->first();
            
            if ($moduloConfig) {
                $permisosConfig = DB::table('permisos_x_usuario')
                    ->where('ModuloP_Id', $moduloConfig->IdModulo)
                    ->get();
                
                foreach ($permisosConfig as $permiso) {
                    DB::table('permisos_x_usuario')->insert([
                        'UsuarioP_Id' => $permiso->UsuarioP_Id,
                        'ModuloP_Id' => $moduloMotivosId,
                        'Ver' => $permiso->Ver,
                        'Crear' => $permiso->Crear,
                        'Editar' => $permiso->Editar,
                        'Eliminar' => $permiso->Eliminar
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar módulo LAR
        $moduloLar = DB::table('modulos')->where('Url', 'licencias/lar')->first();
        if ($moduloLar) {
            DB::table('permisos_x_usuario')->where('ModuloP_Id', $moduloLar->IdModulo)->delete();
            DB::table('modulos')->where('IdModulo', $moduloLar->IdModulo)->delete();
        }
        
        // Eliminar módulo Motivos de Licencia
        $moduloMotivos = DB::table('modulos')->where('Url', 'motivos-licencia')->first();
        if ($moduloMotivos) {
            DB::table('permisos_x_usuario')->where('ModuloP_Id', $moduloMotivos->IdModulo)->delete();
            DB::table('modulos')->where('IdModulo', $moduloMotivos->IdModulo)->delete();
        }
    }
};
