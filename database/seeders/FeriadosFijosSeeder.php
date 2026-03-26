<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeriadosFijosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $feriadosFijos = [
            ['nombre' => 'Año Nuevo', 'dia' => 1, 'mes' => 1, 'descripcion' => 'Primer día del año'],
            ['nombre' => 'Día del Trabajador', 'dia' => 1, 'mes' => 5, 'descripcion' => 'Día Internacional del Trabajo'],
            ['nombre' => 'Día de la Revolución de Mayo', 'dia' => 25, 'mes' => 5, 'descripcion' => '25 de Mayo de 1810'],
            ['nombre' => 'Día de la Independencia', 'dia' => 9, 'mes' => 7, 'descripcion' => '9 de Julio de 1816'],
            ['nombre' => 'Día de San Martín', 'dia' => 17, 'mes' => 8, 'descripcion' => 'Paso a la Inmortalidad del Gral. José de San Martín'],
            ['nombre' => 'Día de la Soberanía Nacional', 'dia' => 20, 'mes' => 11, 'descripcion' => 'Vuelta de Obligado'],
            ['nombre' => 'Inmaculada Concepción de María', 'dia' => 8, 'mes' => 12, 'descripcion' => 'Día de la Virgen'],
            ['nombre' => 'Navidad', 'dia' => 25, 'mes' => 12, 'descripcion' => 'Nacimiento de Jesucristo'],
        ];

        foreach ($feriadosFijos as $feriado) {
            \App\Models\FeriadoFijo::create([
                'nombre' => $feriado['nombre'],
                'dia' => $feriado['dia'],
                'mes' => $feriado['mes'],
                'descripcion' => $feriado['descripcion'],
                'activo' => true
            ]);
        }

        $this->command->info('Se crearon ' . count($feriadosFijos) . ' feriados fijos.');
    }
}
