<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campo de configuración JSON a notas_juridicas y limpia estructura
     */
    public function up(): void
    {
        Schema::table('notas_juridicas', function (Blueprint $table) {
            // Agregar campo de configuración JSON
            $table->json('configuracion')->nullable()->after('estado');

            // Agregar referencia a plantilla usada
            $table->unsignedBigInteger('plantilla_id')->nullable()->after('configuracion');
        });

        // Migrar datos existentes al nuevo formato JSON
        // Solo si hay registros existentes
        $notas = DB::table('notas_juridicas')->get();

        foreach ($notas as $nota) {
            $configuracion = [
                'encabezado' => [
                    'logo_path' => $nota->logo_path ?? null,
                    'leyenda' => $nota->leyenda_encabezado ?? null,
                ],
                'contenido' => $nota->descripcion ?? '',
                'margenes' => [
                    'superior' => 2.0,
                    'inferior' => 2.0,
                    'izquierdo' => 2.0,
                    'derecho' => 2.0,
                ],
                'pagina' => [
                    'tamano' => 'legal',
                    'orientacion' => 'portrait',
                ],
            ];

            DB::table('notas_juridicas')
                ->where('idNotaJuridica', $nota->idNotaJuridica)
                ->update(['configuracion' => json_encode($configuracion)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_juridicas', function (Blueprint $table) {
            $table->dropColumn(['configuracion', 'plantilla_id']);
        });
    }
};
