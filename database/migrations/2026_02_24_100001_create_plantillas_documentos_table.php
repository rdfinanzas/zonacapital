<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla centralizada de plantillas de documentos para todos los módulos
     */
    public function up(): void
    {
        Schema::create('plantillas_documentos', function (Blueprint $table) {
            $table->id('idPlantilla');

            // Vinculación al módulo
            $table->unsignedBigInteger('ModuloId')->nullable();

            // Datos de la plantilla
            $table->string('nombre', 255);
            $table->string('descripcion', 500)->nullable();

            // Configuración completa en JSON
            // Estructura:
            // {
            //   "encabezado": { "logo_path": "...", "leyenda": "..." },
            //   "contenido": "<html>...</html>",
            //   "margenes": { "superior": 2.5, "inferior": 2.0, "izquierdo": 3.0, "derecho": 2.0 },
            //   "pagina": { "tamano": "legal", "orientacion": "portrait" }
            // }
            $table->json('configuracion');

            // Auditoría
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('ModuloId');
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_documentos');
    }
};
