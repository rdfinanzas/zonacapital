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
        Schema::create('feriados_fijos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->comment('Nombre del feriado');
            $table->tinyInteger('dia')->comment('Día del mes (1-31)');
            $table->tinyInteger('mes')->comment('Mes del año (1-12)');
            $table->string('dia_mes', 5)->comment('Formato MM-DD para búsquedas');
            $table->boolean('activo')->default(true)->comment('Si está activo para generar');
            $table->text('descripcion')->nullable()->comment('Descripción adicional');
            $table->timestamps();

            // Índices
            $table->unique(['dia', 'mes'], 'unique_dia_mes');
            $table->index('dia_mes');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feriados_fijos');
    }
};
