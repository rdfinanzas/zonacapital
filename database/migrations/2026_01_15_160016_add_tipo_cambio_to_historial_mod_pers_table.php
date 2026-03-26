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
        Schema::table('historial_mod_pers', function (Blueprint $table) {
            $table->enum('tipo_cambio', ['organizacional', 'personal'])->default('personal')->after('Modificaciones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('historial_mod_pers', function (Blueprint $table) {
            $table->dropColumn('tipo_cambio');
        });
    }
};
