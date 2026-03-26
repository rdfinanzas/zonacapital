<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('usuarios_roles')) {
            Schema::create('usuarios_roles', function (Blueprint $table) {
                $table->unsignedInteger('UsuarioId');
                $table->unsignedInteger('TipoUsuarioId');
                $table->primary(['UsuarioId', 'TipoUsuarioId']);

                // Indices para performance
                $table->index('UsuarioId');
                $table->index('TipoUsuarioId');

                // Opcional: claves foráneas si las tablas existen
                // $table->foreign('UsuarioId')->references('IdUsuario')->on('usuarios')->onDelete('cascade');
                // $table->foreign('TipoUsuarioId')->references('IdUsuarioTipo')->on('usuario_tipos')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_roles');
    }
};