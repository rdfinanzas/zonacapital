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
        if (Schema::hasTable('registros_dengue')) {
            return;
        }

        Schema::create('registros_dengue', function (Blueprint $table) {
            $table->increments('IdRegistroDengue');
            $table->integer('PersonaRegistro_Id')->nullable();
            $table->integer('Efector_Id')->nullable();
            $table->tinyInteger('Region_Id')->nullable();
            $table->integer('Semana')->nullable();
            $table->date('Fis')->nullable();
            $table->datetime('Consulta')->nullable();
            $table->date('FechaTomaMuestra')->nullable();
            $table->string('Laboratorio', 200)->nullable();
            $table->string('TestAgNS1', 50)->nullable();
            $table->string('TipoNs1', 50)->nullable();
            $table->string('TestIgM', 50)->nullable();
            $table->string('TestIGG', 50)->nullable();
            $table->string('TestPCR', 50)->nullable();
            $table->string('TestRapidoIgG', 50)->nullable();
            $table->string('TestRapidoIgM', 50)->nullable();
            $table->string('TestChikungunya', 50)->nullable();
            $table->string('TestZika', 50)->nullable();
            $table->boolean('AntVacunacion')->default(false);
            $table->boolean('Obito')->default(false);
            $table->text('Comorbilidad')->nullable();
            $table->text('Observaciones')->nullable();
            $table->text('AntViaje')->nullable();
            $table->decimal('LongitudAnt', 11, 8)->nullable();
            $table->decimal('LatitudAnt', 10, 8)->nullable();
            $table->date('FechaAnt')->nullable();
            $table->string('ImagenFicha', 100)->nullable();
            $table->integer('Creador_Id')->nullable();
            $table->datetime('FechaCreacion')->nullable();
            $table->boolean('febril')->default(true);

            $table->index('PersonaRegistro_Id');
            $table->index('Efector_Id');
            $table->index('Region_Id');
            $table->index('Creador_Id');
            $table->index('Consulta');
            $table->index('febril');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registros_dengue');
    }
};
