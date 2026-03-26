<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cambiar columna estado de ENUM a INTEGER
     */
    public function up(): void
    {
        // Mapeo de estados textuales a IDs numéricos
        $mapeo = [
            'PENDIENTE' => 1,
            'CEDULA ENVIADA' => 2,
            'CON DESCARGO' => 3,
            'SIN DESCARGO' => 4,
            'DICTAMEN' => 5,
            'DISPOSICION' => 6,
            'ELEVACION MSP' => 7,
            'ARCHIVO' => 8,
            // Mapeo de estados antiguos por si existen
            'borrador' => 1,
            'finalizada' => 3,
            'enviada' => 2,
        ];

        // Actualizar los registros existentes antes de cambiar la columna
        foreach ($mapeo as $texto => $id) {
            DB::table('notas_juridicas')
                ->where('estado', $texto)
                ->update(['estado' => $id]);
        }

        // Cambiar la columna a integer
        Schema::table('notas_juridicas', function (Blueprint $table) {
            $table->unsignedTinyInteger('estado')->default(1)->change();
        });
    }

    /**
     * Revertir cambios
     */
    public function down(): void
    {
        Schema::table('notas_juridicas', function (Blueprint $table) {
            $table->enum('estado', ['PENDIENTE', 'CEDULA ENVIADA', 'CON DESCARGO', 'SIN DESCARGO', 'DICTAMEN', 'DISPOSICION', 'ELEVACION MSP', 'ARCHIVO'])->default('PENDIENTE')->change();
        });
    }
};
