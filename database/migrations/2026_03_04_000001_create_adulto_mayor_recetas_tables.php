<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAdultoMayorRecetasTables extends Migration
{
    public function up()
    {
        Schema::dropIfExists('adulto_mayor_entregas');
        Schema::dropIfExists('adulto_mayor_medicamentos');

        Schema::create('adulto_mayor_programas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });

        DB::table('adulto_mayor_programas')->insert([
            ['nombre' => 'Programa Provincial de Medicamentos', 'descripcion' => 'Financiado por Provincia', 'activo' => 1],
            ['nombre' => 'Programa Nacional', 'descripcion' => 'Financiado por Nación', 'activo' => 1],
            ['nombre' => 'Programa Municipal', 'descripcion' => 'Financiado por Municipio', 'activo' => 1],
            ['nombre' => 'Cobertura Social', 'descripcion' => 'Obra Social / Prepaga', 'activo' => 1],
            ['nombre' => 'Recurso propio', 'descripcion' => 'Sin programa financiador', 'activo' => 1],
        ]);

        Schema::create('adulto_mayor_recetas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adulto_mayor_id');
            $table->date('fecha_receta');
            $table->text('diagnostico')->nullable();
            $table->unsignedInteger('programa_id')->nullable();
            $table->enum('estado_verificacion', ['pendiente', 'verificada', 'rechazada'])->default('pendiente');
            $table->unsignedInteger('verificado_por')->nullable();
            $table->timestamp('fecha_verificacion')->nullable();
            $table->text('observaciones_verificacion')->nullable();
            $table->enum('estado_entrega', ['pendiente', 'parcial', 'completada', 'no_entregada'])->default('pendiente');
            $table->unsignedInteger('entregado_por')->nullable();
            $table->timestamp('fecha_entrega')->nullable();
            $table->text('observaciones_entrega')->nullable();
            $table->unsignedInteger('user_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            
            $table->index('adulto_mayor_id');
            $table->index('estado_verificacion');
            $table->index('estado_entrega');
            $table->index('fecha_receta');
        });

        Schema::create('adulto_mayor_receta_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receta_id');
            $table->unsignedInteger('bien_id')->comment('Medicamento del catálogo de bienes');
            $table->string('dosis', 255)->nullable();
            $table->string('frecuencia', 100)->nullable();
            $table->enum('entregado', ['S', 'N'])->default('N');
            $table->unsignedBigInteger('detalle_original_id')->nullable()->comment('ID del detalle original si hubo cambio');
            $table->enum('motivo_cambio', [null, 'faltante_stock', 'indicacion_medica', 'efecto_adverso', 'otro'])->nullable();
            $table->text('observaciones')->nullable();
            $table->unsignedInteger('user_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            
            $table->index('receta_id');
            $table->index('bien_id');
            $table->index('entregado');
        });

        Schema::create('adulto_mayor_entregas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receta_detalle_id');
            $table->unsignedBigInteger('receta_id');
            $table->date('fecha_entrega');
            $table->integer('cantidad')->default(1);
            $table->enum('tipo_entrega', ['receta_original', 'cambio'])->default('receta_original');
            $table->text('observaciones')->nullable();
            $table->unsignedInteger('user_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('receta_detalle_id');
            $table->index('receta_id');
            $table->index('fecha_entrega');
        });
    }

    public function down()
    {
        Schema::dropIfExists('adulto_mayor_entregas');
        Schema::dropIfExists('adulto_mayor_receta_detalles');
        Schema::dropIfExists('adulto_mayor_recetas');
        Schema::dropIfExists('adulto_mayor_programas');
    }
}
