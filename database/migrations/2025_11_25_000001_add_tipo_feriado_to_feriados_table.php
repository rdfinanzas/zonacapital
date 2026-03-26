<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoFeriadoToFeriadosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('feriados', function (Blueprint $table) {
            $table->tinyInteger('EsFijo')->default(0)->comment('1 = Feriado fijo anual, 0 = Feriado variable');
            $table->string('DiaMes', 5)->nullable()->comment('Formato MM-DD para feriados fijos');
            $table->year('Anio')->nullable()->comment('Año del feriado');

            // Índice para mejorar performance
            $table->index(['EsFijo', 'Anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('feriados', function (Blueprint $table) {
            $table->dropIndex(['EsFijo', 'Anio']);
            $table->dropColumn(['EsFijo', 'DiaMes', 'Anio']);
        });
    }
}
