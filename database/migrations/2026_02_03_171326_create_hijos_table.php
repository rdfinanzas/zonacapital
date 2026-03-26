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
    public function up(): void
    {
        Schema::create('hijos', function (Blueprint $table) {
            $table->id('IdHijo');
            $table->unsignedBigInteger('empleado_id');
            $table->string('Apellido', 100)->nullable();
            $table->string('Nombre', 100)->nullable();
            $table->string('DNI', 20)->nullable();
            $table->date('FecNac')->nullable();
            $table->integer('Edad')->nullable();
            $table->boolean('ImpedidoTrabaja')->default(false)->comment('Si tiene impedimento para trabajar');
            $table->boolean('RemuneracionEmpleador')->default(false)->comment('Si recibe remuneracion de empleador');
            $table->boolean('IngresosMensuales')->default(false)->comment('Si tiene ingresos mensuales propios');
            $table->string('NivelEducativo', 50)->nullable()->comment('PRIMARIA/SECUNDARIA/UNIVERSITARIA');
            $table->string('GradoAnio', 50)->nullable()->comment('Grado o año cursado');
            $table->boolean('Convive')->default(true)->comment('Si convive con el empleado');
            $table->boolean('Estudia')->default(true)->comment('Si esta estudiando');
            $table->text('Observaciones')->nullable();

            // Datos del otro padre/madre si existe
            $table->string('OtroPadre_ApellidoNombre', 200)->nullable();
            $table->string('OtroPadre_DNI', 20)->nullable();
            $table->string('OtroPadre_Domicilio', 200)->nullable();
            $table->boolean('OtroPadre_Trabaja')->nullable();
            $table->string('OtroPadre_Empleador', 200)->nullable();
            $table->boolean('OtroPadre_AsigFamiliares')->nullable();
            $table->boolean('OtroPadre_Convive')->nullable();

            // Fecha de casamiento del empleado (para estado civil casado)
            $table->date('FechaCasamiento')->nullable();

            // Otros empleos del empleado (para el formulario)
            $table->string('OtroEmpleador', 200)->nullable();
            $table->boolean('PercibeSalario')->nullable();
            $table->decimal('MontoSalario', 12, 2)->nullable();
            $table->text('ObservacionesOtrosEmpleos')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('empleado_id')->references('idEmpleado')->on('empleados')->onDelete('cascade');
            $table->index('empleado_id');
            $table->index('DNI');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('hijos');
    }
};
