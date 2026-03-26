<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertFeriadosModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Primero verificar si ya existe un módulo padre "Licencias y Feriados"
        $moduloPadre = DB::table('modulos')->where('Label', 'Licencias y Feriados')->first();

        if (!$moduloPadre) {
            // Crear módulo padre
            $moduloPadreId = DB::table('modulos')->insertGetId([
                'Label' => 'Licencias y Feriados',
                'Url' => '#',
                'Icono' => 'fas fa-calendar-alt',
                'ModuloPadreId' => 0,
                'Orden' => 15,
                'Padre' => 1
            ]);
        } else {
            $moduloPadreId = $moduloPadre->IdModulo;
        }

        // Verificar si ya existe el módulo feriados
        $feriadosExiste = DB::table('modulos')->where('Url', 'like', '%laravel-feriados%')->first();

        if (!$feriadosExiste) {
            // Insertar módulo hijo "Feriados"
            $feriadosModuloId = DB::table('modulos')->insertGetId([
                'Label' => 'Gestión de Feriados',
                'Url' => 'laravel-feriados',
                'Icono' => 'fas fa-calendar-day',
                'ModuloPadreId' => $moduloPadreId,
                'Orden' => 2,
                'Padre' => 0
            ]);

            echo "Módulo Feriados creado con ID: $feriadosModuloId\n";

            // Dar permisos completos al super admin (tipo usuario 1)
            DB::table('permisos_x_tipos_usuarios')->insert([
                'TipoUsuarioId' => 1,
                'ModuloId' => $feriadosModuloId,
                'C' => 1,
                'R' => 1,
                'U' => 1,
                'D' => 1
            ]);

            echo "Permisos asignados al super admin\n";
        } else {
            echo "El módulo Feriados ya existe\n";
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar módulo feriados
        DB::table('modulos')->where('Url', 'like', '%laravel-feriados%')->delete();

        // Eliminar permisos relacionados
        $moduloId = DB::table('modulos')->where('Url', 'like', '%laravel-feriados%')->value('IdModulo');
        if ($moduloId) {
            DB::table('permisos_x_tipos_usuarios')->where('ModuloId', $moduloId)->delete();
            DB::table('permisos_x_usuarios')->where('ModuloId', $moduloId)->delete();
        }
    }
}
