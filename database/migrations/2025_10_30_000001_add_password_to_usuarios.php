<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Nuevo campo para autenticación Laravel (hash Bcrypt)
            if (!Schema::hasColumn('usuarios', 'Password')) {
                $table->string('Password', 255)->nullable()->after('Clave');
            }
            // Campo opcional para auditoría
            if (!Schema::hasColumn('usuarios', 'PasswordUpdatedAt')) {
                $table->timestamp('PasswordUpdatedAt')->nullable()->after('Password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'PasswordUpdatedAt')) {
                $table->dropColumn('PasswordUpdatedAt');
            }
            if (Schema::hasColumn('usuarios', 'Password')) {
                $table->dropColumn('Password');
            }
        });
    }
};