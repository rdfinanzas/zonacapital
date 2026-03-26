<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configuraciones = [
            [
                'clave' => 'firma_directora_nombre',
                'valor' => 'Dra. NANCY BEATRIZ KARTUN',
                'descripcion' => 'Nombre de la directora para firmas',
            ],
            [
                'clave' => 'firma_directora_cargo',
                'valor' => 'Directora de Zona Capital',
                'descripcion' => 'Cargo de la directora para firmas',
            ],
        ];

        foreach ($configuraciones as $config) {
            Configuracion::updateOrCreate(
                ['clave' => $config['clave']],
                $config
            );
        }
    }
}
