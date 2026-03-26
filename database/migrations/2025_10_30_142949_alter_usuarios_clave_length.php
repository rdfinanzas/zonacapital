<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Aumentar longitud de Clave para soportar hashes de bcrypt/argon
        DB::statement('ALTER TABLE `usuarios` MODIFY `Clave` VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir longitud (ajústalo si la longitud previa era distinta)
        DB::statement('ALTER TABLE `usuarios` MODIFY `Clave` VARCHAR(60) NOT NULL');
    }
};
