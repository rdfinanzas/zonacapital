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
        Schema::table('notas_juridicas', function (Blueprint $table) {
            // Campos para encabezado configurable
            $table->string('logo_path')->nullable()->after('archivo_path');
            $table->text('leyenda_encabezado')->nullable()->after('logo_path');

            // Campos para sistema de plantillas
            $table->boolean('es_plantilla')->default(false)->after('estado');
            $table->string('nombre_plantilla')->nullable()->after('es_plantilla');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_juridicas', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'leyenda_encabezado', 'es_plantilla', 'nombre_plantilla']);
        });
    }
};
