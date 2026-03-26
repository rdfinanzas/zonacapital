<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('empleado_servicio', function (Blueprint $table) {
            // Agregar campo para identificar si el empleado es jefe de este servicio
            $table->boolean('es_jefe')->default(false)->after('activo');
            
            // Agregar índice para búsquedas eficientes
            $table->index(['servicio_id', 'es_jefe', 'activo'], 'idx_servicio_jefe_activo');
        });

        // Migrar datos existentes: empleados con idCargo=2 (Jefe de Servicio) 
        // que estén activos en un servicio, marcarlos como jefes
        $empleadosJefes = DB::table('empleados')
            ->where('idCargo', 2)
            ->where('Estado', 1)
            ->where(function($q) {
                $q->where('FBaja', '0000-00-00')->orWhereNull('FBaja');
            })
            ->whereNotNull('idServicio')
            ->where('idServicio', '!=', 0)
            ->get();

        foreach ($empleadosJefes as $empleado) {
            // Buscar si existe registro en empleado_servicio
            $existe = DB::table('empleado_servicio')
                ->where('empleado_id', $empleado->idEmpleado)
                ->where('servicio_id', $empleado->idServicio)
                ->first();

            if ($existe) {
                // Actualizar el registro existente
                DB::table('empleado_servicio')
                    ->where('id', $existe->id)
                    ->update(['es_jefe' => true]);
            } else {
                // Crear nuevo registro con es_jefe = true
                DB::table('empleado_servicio')->insert([
                    'empleado_id' => $empleado->idEmpleado,
                    'servicio_id' => $empleado->idServicio,
                    'es_jefe' => true,
                    'fecha_inicio' => ($empleado->FAlta && $empleado->FAlta != '0000-00-00') ? $empleado->FAlta : now(),
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
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
        Schema::table('empleado_servicio', function (Blueprint $table) {
            $table->dropIndex('idx_servicio_jefe_activo');
            $table->dropColumn('es_jefe');
        });
    }
};
