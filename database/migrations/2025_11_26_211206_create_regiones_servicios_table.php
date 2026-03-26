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
        if (Schema::hasTable('regiones_servicios')) {
            return;
        }

        Schema::create('regiones_servicios', function (Blueprint $table) {
            $table->tinyIncrements('IdRegion');
            $table->string('Region', 100);
            $table->string('Clase', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regiones_servicios');
    }
};
