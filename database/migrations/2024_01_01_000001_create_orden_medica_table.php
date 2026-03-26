<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orden_medica', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personal_id');
            $table->integer('numero');
            $table->date('fecha');
            $table->integer('anio');
            $table->string('estado', 50)->default('Pendiente');
            $table->unsignedBigInteger('motivo_id');
            $table->integer('dias');
            $table->date('desde');
            $table->date('hasta');
            $table->boolean('corridos')->default(true);
            $table->unsignedBigInteger('disposicion_id')->nullable();
            $table->text('observacion')->nullable();
            $table->string('certificado', 255)->nullable();
            $table->timestamps();

            // Índices
            $table->index('personal_id');
            $table->index('anio');
            $table->index('estado');
            $table->index('motivo_id');
            $table->index('disposicion_id');
            $table->index(['anio', 'numero']);

            // Claves foráneas (comentadas temporalmente)
            // $table->foreign('personal_id')->references('id')->on('personal');
            // $table->foreign('motivo_id')->references('id')->on('motivos');
            // $table->foreign('disposicion_id')->references('id')->on('disposiciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_medica');
    }
};