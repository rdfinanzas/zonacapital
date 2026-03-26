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
        Schema::create('notas_juridicas_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nota_juridica_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->text('descripcion');
            $table->timestamp('created_at')->useCurrent();

            // Índices sin foreign keys para evitar problemas
            $table->index('nota_juridica_id');
            $table->index('usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notas_juridicas_historial');
    }
};
