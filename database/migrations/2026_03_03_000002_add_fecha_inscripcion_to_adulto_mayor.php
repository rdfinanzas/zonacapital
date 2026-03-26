<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFechaInscripcionToAdultoMayor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->date('fecha_inscripcion')->nullable()->after('servicio_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->dropColumn('fecha_inscripcion');
        });
    }
}
