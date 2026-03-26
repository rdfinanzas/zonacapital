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
        Schema::table('motivo_licencia', function (Blueprint $table) {
            $table->unsignedTinyInteger('ModuloId')->nullable()->after('ObservacionMot');
            
            // Clave foránea opcional (puede ser nulo si no se quiere vincular a ningún módulo específico)
            $table->foreign('ModuloId')
                  ->references('IdModulo')
                  ->on('modulos')
                  ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('motivo_licencia', function (Blueprint $table) {
            $table->dropForeign(['ModuloId']);
            $table->dropColumn('ModuloId');
        });
    }
};
