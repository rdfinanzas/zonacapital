<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migración para el módulo de Notas Jurídicas
     * Ejecutar con: php artisan migrate --path=database/migrations/2026_02_22_000001_create_notas_juridicas_table.php
     */
    public function up(): void
    {
        Schema::create('notas_juridicas', function (Blueprint $table) {
            $table->id('idNotaJuridica');

            // Numeración automática por año (formato: numero/anio, ej: 1/2027)
            $table->unsignedInteger('numero')->default(1);
            $table->unsignedSmallInteger('anio');

            // Datos principales de la nota
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable(); // Contenido HTML del editor Summernote
            $table->text('observacion')->nullable(); // Notas internas
            $table->date('fecha_creacion');

            // Relaciones
            $table->unsignedBigInteger('personal_id')->nullable(); // Personal vinculado
            $table->unsignedBigInteger('nota_referencia_id')->nullable(); // Referencia a nota anterior

            // Archivos
            $table->string('archivo_path', 500)->nullable(); // Ruta local del archivo
            $table->string('google_drive_file_id', 255)->nullable(); // ID del archivo en Google Drive
            $table->string('google_drive_link', 500)->nullable(); // Enlace para ver en Drive

            // Tipo y estado
            $table->enum('tipo', ['creada', 'adjunta'])->default('creada'); // Creada en editor o adjuntada
            $table->enum('estado', ['borrador', 'finalizada', 'enviada'])->default('borrador');

            // Auditoría
            $table->unsignedBigInteger('creado_por')->nullable();

            // Timestamps y soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar consultas
            $table->unique(['numero', 'anio'], 'uk_numero_anio'); // Número único por año
            $table->index('personal_id');
            $table->index('estado');
            $table->index('fecha_creacion');
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_juridicas');
    }
};
