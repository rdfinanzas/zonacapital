<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar la URL del módulo LAR para que apunte a la lista
        // El módulo Laravel tiene el prefijo 'laravel-'
        DB::table('modulos')
            ->where('Url', 'laravel-licencias/lar')
            ->update(['Url' => 'laravel-licencias/lar-lista']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir la URL del módulo LAR
        DB::table('modulos')
            ->where('Url', 'laravel-licencias/lar-lista')
            ->update(['Url' => 'laravel-licencias/lar']);
    }
};
