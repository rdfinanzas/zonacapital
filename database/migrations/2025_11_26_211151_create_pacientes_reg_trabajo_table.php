<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabla ya existe, no hacer nada
        if (Schema::hasTable('pacientes_reg_trabajo')) {
            return;
        }

        Schema::create('pacientes_reg_trabajo', function (Blueprint $table) {
            $table->increments('IdPacienteRegTrab');
            $table->integer('DNI');
            $table->string('ApellidoNombre', 200);
            $table->string('Celular', 50)->nullable();
            $table->tinyInteger('Sexo')->nullable();
            $table->date('FechaNacimiento')->nullable();
            $table->text('Domicilio')->nullable();
            $table->string('Departamento', 100)->nullable();
            $table->string('Localidad', 100)->nullable();
            $table->string('Barrio', 100)->nullable();
            $table->text('Referencias')->nullable();
            $table->decimal('Latitud', 10, 8)->nullable();
            $table->decimal('Longitud', 11, 8)->nullable();
            $table->integer('Creador_Id')->nullable();
            $table->datetime('FechaCreacion')->nullable();

            $table->index('DNI');
            $table->index('Creador_Id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pacientes_reg_trabajo');
    }
};
