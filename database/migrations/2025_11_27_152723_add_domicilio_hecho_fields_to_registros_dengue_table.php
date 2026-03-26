<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registros_dengue', function (Blueprint $table) {
            // Campos para el domicilio donde ocurrió el hecho de dengue
            $table->text('DomicilioHecho')->nullable()->after('AntViaje')->comment('Domicilio donde ocurrió el caso de dengue');
            $table->string('DepartamentoHecho', 100)->nullable()->after('DomicilioHecho')->comment('Departamento del hecho');
            $table->string('LocalidadHecho', 100)->nullable()->after('DepartamentoHecho')->comment('Localidad del hecho');
            $table->string('BarrioHecho', 100)->nullable()->after('LocalidadHecho')->comment('Barrio del hecho');
            $table->text('ReferenciasHecho')->nullable()->after('BarrioHecho')->comment('Referencias del hecho');
            $table->decimal('LatitudHecho', 10, 8)->nullable()->after('ReferenciasHecho')->comment('Latitud del hecho');
            $table->decimal('LongitudHecho', 11, 8)->nullable()->after('LatitudHecho')->comment('Longitud del hecho');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registros_dengue', function (Blueprint $table) {
            $table->dropColumn([
                'DomicilioHecho',
                'DepartamentoHecho',
                'LocalidadHecho',
                'BarrioHecho',
                'ReferenciasHecho',
                'LatitudHecho',
                'LongitudHecho'
            ]);
        });
    }
};
