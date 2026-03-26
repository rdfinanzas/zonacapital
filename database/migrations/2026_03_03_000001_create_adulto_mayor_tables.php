<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdultoMayorTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabla principal de adultos mayores
        Schema::create('adulto_mayor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedInteger('servicio_id');
            $table->enum('estado', ['activo', 'inactivo', 'fallecido'])->default('activo');
            $table->text('observaciones_generales')->nullable();
            $table->unsignedInteger('user_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            
            $table->index('paciente_id');
            $table->index('servicio_id');
            $table->index('estado');
        });

        // Tabla de medicamentos asignados
        Schema::create('adulto_mayor_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adulto_mayor_id');
            $table->unsignedInteger('bien_id');
            $table->string('dosis_especifica', 255)->nullable();
            $table->string('frecuencia', 100)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->text('observaciones')->nullable();
            $table->date('ultima_entrega')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->unsignedInteger('user_at')->nullable();
            
            $table->index('adulto_mayor_id');
            $table->index('bien_id');
            $table->index('activo');
        });

        // Tabla de historial de entregas
        Schema::create('adulto_mayor_entregas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicamento_id');
            $table->date('fecha_entrega');
            $table->integer('cantidad')->default(1);
            $table->text('observaciones')->nullable();
            $table->unsignedInteger('user_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('medicamento_id');
            $table->index('fecha_entrega');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adulto_mayor_entregas');
        Schema::dropIfExists('adulto_mayor_medicamentos');
        Schema::dropIfExists('adulto_mayor');
    }
}
