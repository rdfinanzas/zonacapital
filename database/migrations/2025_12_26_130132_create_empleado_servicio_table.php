<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('empleado_servicio', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empleado_id');
            $table->unsignedInteger('servicio_id');
            $table->date('fecha_inicio')->useCurrent();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->index(['empleado_id', 'activo']);
            $table->index(['servicio_id', 'activo']);
        });

        // Migrar datos existentes
        $empleados = DB::table('empleados')->whereNotNull('idServicio')->where('idServicio', '!=', 0)->get();
        foreach ($empleados as $empleado) {
            DB::table('empleado_servicio')->insert([
                'empleado_id' => $empleado->idEmpleado,
                'servicio_id' => $empleado->idServicio,
                'fecha_inicio' => ($empleado->FAlta && $empleado->FAlta != '0000-00-00') ? $empleado->FAlta : now(),
                'activo' => ($empleado->Estado == 1),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empleado_servicio');
    }
};

