<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('horarios_rotativos', function (Blueprint $table) {
            $table->unsignedInteger('servicio_id')->nullable()->after('EmpleadoRot_Id');
            $table->index('servicio_id');
        });

        Schema::table('guardias', function (Blueprint $table) {
            $table->unsignedInteger('servicio_id')->nullable()->after('EmpleadoGuar_Id');
            $table->index('servicio_id');
        });

        Schema::table('francos_efrm', function (Blueprint $table) {
            $table->unsignedInteger('servicio_id')->nullable()->after('EmpleadoFrancEnfrm_Id');
            $table->index('servicio_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('horarios_rotativos', function (Blueprint $table) {
            $table->dropColumn('servicio_id');
        });

        Schema::table('guardias', function (Blueprint $table) {
            $table->dropColumn('servicio_id');
        });

        Schema::table('francos_efrm', function (Blueprint $table) {
            $table->dropColumn('servicio_id');
        });
    }
};

