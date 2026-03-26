<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas_juridicas', function (Blueprint $table) {
            $table->string('google_doc_id', 255)->nullable()->after('google_drive_link');
            $table->string('google_doc_link', 500)->nullable()->after('google_doc_id');
        });
    }

    public function down(): void
    {
        Schema::table('notas_juridicas', function (Blueprint $table) {
            $table->dropColumn(['google_doc_id', 'google_doc_link']);
        });
    }
};
