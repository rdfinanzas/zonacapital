<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Modulo;
use App\Models\PermisoPorUsuario;

class InsertarModulosFinancieros extends Command
{
    protected $signature = 'modulos:financieros';
    protected $description = 'Insertar módulos financieros en la base de datos';

    public function handle()
    {
        $this->info('Insertando módulos financieros...');

        $parent = Modulo::where('Url', 'laravel-modulos')->first();
        if (!$parent) {
            $this->error('No se encontró el módulo padre "Módulos Laravel"');
            return 1;
        }

        $modulos = [
            ['Label' => 'Proveedores', 'Url' => 'laravel-proveedores', 'Icono' => 'fas fa-truck', 'Orden' => 100],
            ['Label' => 'Cotizaciones', 'Url' => 'laravel-cotizaciones', 'Icono' => 'fas fa-file-invoice-dollar', 'Orden' => 110],
            ['Label' => 'Pedidos Internos', 'Url' => 'laravel-pedidos-internos', 'Icono' => 'fas fa-clipboard-list', 'Orden' => 120],
            ['Label' => 'Actas de Recepción', 'Url' => 'laravel-actas-recepcion', 'Icono' => 'fas fa-check-double', 'Orden' => 130],
            ['Label' => 'Órdenes de Compra', 'Url' => 'laravel-ordenes-compra', 'Icono' => 'fas fa-shopping-cart', 'Orden' => 140],
            ['Label' => 'Pagos', 'Url' => 'laravel-pagos', 'Icono' => 'fas fa-money-bill-wave', 'Orden' => 150],
            ['Label' => 'Órdenes de Pago', 'Url' => 'laravel-ordenes-pago', 'Icono' => 'fas fa-file-invoice', 'Orden' => 160],
            ['Label' => 'Deuda', 'Url' => 'laravel-deuda', 'Icono' => 'fas fa-chart-line', 'Orden' => 170],
        ];

        foreach ($modulos as $moduloData) {
            $existe = Modulo::where('Url', $moduloData['Url'])->first();

            if (!$existe) {
                Modulo::create([
                    'Label' => $moduloData['Label'],
                    'Url' => $moduloData['Url'],
                    'Icono' => $moduloData['Icono'],
                    'ModuloPadreId' => $parent->IdModulo,
                    'Orden' => $moduloData['Orden'],
                    'Padre' => 0,
                ]);
                $this->info("Módulo '{$moduloData['Label']}' creado.");
            } else {
                $this->info("Módulo '{$moduloData['Label']}' ya existe.");
            }
        }

        $this->info('Concediendo permisos a todos los usuarios...');
        $usuarios = DB::table('usuarios')->select('IdUsuario')->get();
        $urls = array_column($modulos, 'Url');

        foreach ($usuarios as $usuario) {
            foreach ($urls as $url) {
                $modulo = Modulo::where('Url', $url)->first();
                if ($modulo) {
                    $existePermiso = PermisoPorUsuario::where('UsuarioId', $usuario->IdUsuario)
                        ->where('ModuloId', $modulo->IdModulo)
                        ->exists();

                    if (!$existePermiso) {
                        PermisoPorUsuario::create([
                            'UsuarioId' => $usuario->IdUsuario,
                            'ModuloId' => $modulo->IdModulo,
                            'C' => 0,
                            'R' => 1,
                            'U' => 0,
                            'D' => 0,
                        ]);
                    }
                }
            }
        }

        $this->info('¡Módulos financieros insertados correctamente!');
        return 0;
    }
}
