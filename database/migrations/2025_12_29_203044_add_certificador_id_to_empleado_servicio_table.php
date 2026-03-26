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
        Schema::table('empleado_servicio', function (Blueprint $table) {
            $table->unsignedInteger('certificador_id')->nullable()->after('servicio_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('empleado_servicio', function (Blueprint $table) {
            $table->dropColumn('certificador_id');
        });
    }
};
