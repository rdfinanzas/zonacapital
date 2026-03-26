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
        Schema::table('notas_juridicas', function (Blueprint $table) {
            // Hacer nullable el campo tipo para permitir notas con ambos contenidos
            $table->string('tipo', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notas_juridicas', function (Blueprint $table) {
            // Volver a hacerlo requerido
            $table->string('tipo', 20)->nullable(false)->default('adjunta')->change();
        });
    }
};
