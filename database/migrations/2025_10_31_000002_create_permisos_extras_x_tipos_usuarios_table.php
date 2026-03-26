<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('permisos_extras_x_tipos_usuarios')) {
            Schema::create('permisos_extras_x_tipos_usuarios', function (Blueprint $table) {
                $table->bigIncrements('IdPermisoExtraXTipoUsuario');
                $table->unsignedBigInteger('TipoUsuarioId');
                $table->unsignedBigInteger('PermisoExtraId');
                $table->tinyInteger('Permiso')->default(1);

                $table->unique(['TipoUsuarioId', 'PermisoExtraId'], 'ux_tipo_permiso_extra');

                // Nota: se omiten claves foráneas para evitar errores en instalaciones existentes
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_extras_x_tipos_usuarios');
    }
};