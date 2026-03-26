<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ControlHorariosController;

class TestControlHorarios extends Command
{
    protected $signature = 'test:control-horarios';
    protected $description = 'Testear el formato de datos de Control Horarios';

    public function handle()
    {
        $data = [
            'desde' => '01/03/2026',
            'hasta' => '05/03/2026',
            'tipo' => '0',
            'idEmpleado' => 0,
            'ger' => 0,
            'dep' => 0,
            'serv' => 0,
            'clasificacion' => 0,
        ];

        $controller = new ControlHorariosController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('controlHorarios');
        $method->setAccessible(true);

        $this->info('=== TEST CONTROL HORARIOS ===');
        $this->newLine();

        try {
            $resultado = $method->invoke($controller, $data);

            $this->info("Estructura de resultados:");
            $this->info("- Fechas: " . count($resultado['fechas'] ?? []) . " fechas");
            $this->info("- Empleados: " . count($resultado['empleados'] ?? []) . " empleados");
            $this->newLine();

            if (isset($resultado['fechas'])) {
                $this->info("Fechas generadas:");
                foreach ($resultado['fechas'] as $fecha) {
                    $this->info("  - {$fecha['display']} (key: {$fecha['key']})");
                }
                $this->newLine();
            }

            if (isset($resultado['empleados']) && count($resultado['empleados']) > 0) {
                $this->info("Primeros 5 empleados:");
                $cont = 0;
                foreach ($resultado['empleados'] as $emp) {
                    if ($cont++ >= 5) break;
                    $this->info("  - {$emp['nombreCompleto']} (ID: {$emp['idEmpleado']})");
                    if (isset($emp['fechas'])) {
                        $numFechas = count($emp['fechas']);
                        $this->info("    Tiene datos en $numFechas fechas");
                        $primeraFecha = array_key_first($emp['fechas']);
                        if ($primeraFecha) {
                            $celda = $emp['fechas'][$primeraFecha];
                            $this->info("    Ejemplo ($primeraFecha): lic_fer='{$celda['lic_fer']}', marcas1='{$celda['marcas1']}', tipo={$celda['tipo_marca']}");
                        }
                    }
                }
            }

            $this->newLine();
            $this->info("Formato de datos: " . (isset($resultado['empleados']) ? "NUEVO (matricial)" : "VIEJO (lista)"));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("ERROR: " . $e->getMessage());
            $this->error("Archivo: " . $e->getFile() . ":" . $e->getLine());
            return Command::FAILURE;
        }
    }
}
