<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear/obtener módulo padre para agrupar los módulos Laravel
        $parent = DB::table('modulos')->where('Url', 'laravel-modulos')->first();
        if (!$parent) {
            $parentId = DB::table('modulos')->insertGetId([
                'Label' => 'Módulos Laravel',
                'Url' => 'laravel-modulos',
                'Icono' => 'fas fa-cubes',
                'ModuloPadreId' => 0,
                'Orden' => 90,
                'Padre' => 1,
            ]);
        } else {
            $parentId = $parent->IdModulo;
        }

        // Agregar módulo hijo: Relojes
        $reloj = DB::table('modulos')->where('Url', 'laravel-reloj')->first();
        if (!$reloj) {
            DB::table('modulos')->insert([
                'Label' => 'Relojes',
                'Url' => 'laravel-reloj',
                'Icono' => 'fas fa-clock',
                'ModuloPadreId' => $parentId,
                'Orden' => 10,
                'Padre' => 0,
            ]);
            $reloj = DB::table('modulos')->where('Url', 'laravel-reloj')->first();
        }

        // Agregar módulo hijo: Nota de Crédito
        $notaCredito = DB::table('modulos')->where('Url', 'laravel-nota-credito')->first();
        if (!$notaCredito) {
            DB::table('modulos')->insert([
                'Label' => 'Nota de Crédito',
                'Url' => 'laravel-nota-credito',
                'Icono' => 'fas fa-file-invoice',
                'ModuloPadreId' => $parentId,
                'Orden' => 20,
                'Padre' => 0,
            ]);
            $notaCredito = DB::table('modulos')->where('Url', 'laravel-nota-credito')->first();
        }

        // Agregar módulo hijo: Usuarios
        $modUsuarios = DB::table('modulos')->where('Url', 'laravel-usuarios')->first();
        if (!$modUsuarios) {
            DB::table('modulos')->insert([
                'Label' => 'Usuarios',
                'Url' => 'laravel-usuarios',
                'Icono' => 'fas fa-users',
                'ModuloPadreId' => $parentId,
                'Orden' => 30,
                'Padre' => 0,
            ]);
            $modUsuarios = DB::table('modulos')->where('Url', 'laravel-usuarios')->first();
        }

        // Agregar módulo hijo: Roles
        $modRoles = DB::table('modulos')->where('Url', 'laravel-roles')->first();
        if (!$modRoles) {
            DB::table('modulos')->insert([
                'Label' => 'Roles',
                'Url' => 'laravel-roles',
                'Icono' => 'fas fa-user-shield',
                'ModuloPadreId' => $parentId,
                'Orden' => 40,
                'Padre' => 0,
            ]);
            $modRoles = DB::table('modulos')->where('Url', 'laravel-roles')->first();
        }

        // Agregar módulo hijo: Personal (Laravel)
        $modPersonalLaravel = DB::table('modulos')->where('Url', 'laravel-personal')->first();
        if (!$modPersonalLaravel) {
            DB::table('modulos')->insert([
                'Label' => 'Personal',
                'Url' => 'laravel-personal',
                'Icono' => 'fas fa-id-badge',
                'ModuloPadreId' => $parentId,
                'Orden' => 50,
                'Padre' => 0,
            ]);
            $modPersonalLaravel = DB::table('modulos')->where('Url', 'laravel-personal')->first();
        }

        // Migrar permisos desde el módulo legacy 'personal' al nuevo 'laravel-personal'
        $modPersonalLegacy = DB::table('modulos')->where('Url', 'personal')->first();
        if ($modPersonalLegacy && $modPersonalLaravel) {
            $legacyId = $modPersonalLegacy->IdModulo;
            $laravelId = $modPersonalLaravel->IdModulo;

            // Copiar permisos por usuarios si no existen aún para el módulo Laravel
            $permisosUsuarios = DB::table('permisos_x_usuarios')
                ->where('ModuloId', $legacyId)
                ->get();
            foreach ($permisosUsuarios as $p) {
                $exists = DB::table('permisos_x_usuarios')
                    ->where('UsuarioId', $p->UsuarioId)
                    ->where('ModuloId', $laravelId)
                    ->exists();
                if (!$exists) {
                    DB::table('permisos_x_usuarios')->insert([
                        'UsuarioId' => $p->UsuarioId,
                        'ModuloId' => $laravelId,
                        'C' => $p->C,
                        'R' => $p->R,
                        'U' => $p->U,
                        'D' => $p->D,
                    ]);
                }
            }

            // Copiar permisos por tipos de usuarios (roles) si no existen para el módulo Laravel
            $permisosRoles = DB::table('permisos_x_tipos_usuarios')
                ->where('ModuloId', $legacyId)
                ->get();
            foreach ($permisosRoles as $pr) {
                $exists = DB::table('permisos_x_tipos_usuarios')
                    ->where('TipoUsuarioId', $pr->TipoUsuarioId)
                    ->where('ModuloId', $laravelId)
                    ->exists();
                if (!$exists) {
                    DB::table('permisos_x_tipos_usuarios')->insert([
                        'TipoUsuarioId' => $pr->TipoUsuarioId,
                        'ModuloId' => $laravelId,
                        'C' => $pr->C,
                        'R' => $pr->R,
                        'U' => $pr->U,
                        'D' => $pr->D,
                    ]);
                }
            }
        }

        // Agregar módulo hijo: Orden Médicas (Laravel)
        $modOMLaravel = DB::table('modulos')->where('Url', 'laravel-orden-medicas')->first();
        if (!$modOMLaravel) {
            DB::table('modulos')->insert([
                'Label' => 'Orden Médicas',
                'Url' => 'laravel-orden-medicas',
                'Icono' => 'fas fa-notes-medical',
                'ModuloPadreId' => $parentId,
                'Orden' => 60,
                'Padre' => 0,
            ]);
            $modOMLaravel = DB::table('modulos')->where('Url', 'laravel-orden-medicas')->first();
        }

        // Migrar permisos desde el módulo legacy de OM si existiera (ej: 'num_orden_med')
        $modOMLegacy = DB::table('modulos')->where('Url', 'num_orden_med')->first();
        if ($modOMLegacy && $modOMLaravel) {
            $legacyId = $modOMLegacy->IdModulo;
            $laravelId = $modOMLaravel->IdModulo;

            // Copiar permisos por usuarios
            $permisosUsuariosOM = DB::table('permisos_x_usuarios')
                ->where('ModuloId', $legacyId)
                ->get();
            foreach ($permisosUsuariosOM as $p) {
                $exists = DB::table('permisos_x_usuarios')
                    ->where('UsuarioId', $p->UsuarioId)
                    ->where('ModuloId', $laravelId)
                    ->exists();
                if (!$exists) {
                    DB::table('permisos_x_usuarios')->insert([
                        'UsuarioId' => $p->UsuarioId,
                        'ModuloId' => $laravelId,
                        'C' => $p->C,
                        'R' => $p->R,
                        'U' => $p->U,
                        'D' => $p->D,
                    ]);
                }
            }

            // Copiar permisos por tipos de usuarios (roles)
            $permisosRolesOM = DB::table('permisos_x_tipos_usuarios')
                ->where('ModuloId', $legacyId)
                ->get();
            foreach ($permisosRolesOM as $pr) {
                $exists = DB::table('permisos_x_tipos_usuarios')
                    ->where('TipoUsuarioId', $pr->TipoUsuarioId)
                    ->where('ModuloId', $laravelId)
                    ->exists();
                if (!$exists) {
                    DB::table('permisos_x_tipos_usuarios')->insert([
                        'TipoUsuarioId' => $pr->TipoUsuarioId,
                        'ModuloId' => $laravelId,
                        'C' => $pr->C,
                        'R' => $pr->R,
                        'U' => $pr->U,
                        'D' => $pr->D,
                    ]);
                }
            }
        }

        // Agregar módulo hijo: Proveedores
        $modProveedores = DB::table('modulos')->where('Url', 'laravel-proveedores')->first();
        if (!$modProveedores) {
            DB::table('modulos')->insert([
                'Label' => 'Proveedores',
                'Url' => 'laravel-proveedores',
                'Icono' => 'fas fa-truck',
                'ModuloPadreId' => $parentId,
                'Orden' => 100,
                'Padre' => 0,
            ]);
            $modProveedores = DB::table('modulos')->where('Url', 'laravel-proveedores')->first();
        }

        // Agregar módulo hijo: Cotizaciones
        $modCotizaciones = DB::table('modulos')->where('Url', 'laravel-cotizaciones')->first();
        if (!$modCotizaciones) {
            DB::table('modulos')->insert([
                'Label' => 'Cotizaciones',
                'Url' => 'laravel-cotizaciones',
                'Icono' => 'fas fa-file-invoice-dollar',
                'ModuloPadreId' => $parentId,
                'Orden' => 110,
                'Padre' => 0,
            ]);
            $modCotizaciones = DB::table('modulos')->where('Url', 'laravel-cotizaciones')->first();
        }

        // Agregar módulo hijo: Pedidos Internos
        $modPedidosInternos = DB::table('modulos')->where('Url', 'laravel-pedidos-internos')->first();
        if (!$modPedidosInternos) {
            DB::table('modulos')->insert([
                'Label' => 'Pedidos Internos',
                'Url' => 'laravel-pedidos-internos',
                'Icono' => 'fas fa-clipboard-list',
                'ModuloPadreId' => $parentId,
                'Orden' => 120,
                'Padre' => 0,
            ]);
            $modPedidosInternos = DB::table('modulos')->where('Url', 'laravel-pedidos-internos')->first();
        }

        // Agregar módulo hijo: Actas de Recepción
        $modActasRecepcion = DB::table('modulos')->where('Url', 'laravel-actas-recepcion')->first();
        if (!$modActasRecepcion) {
            DB::table('modulos')->insert([
                'Label' => 'Actas de Recepción',
                'Url' => 'laravel-actas-recepcion',
                'Icono' => 'fas fa-check-double',
                'ModuloPadreId' => $parentId,
                'Orden' => 130,
                'Padre' => 0,
            ]);
            $modActasRecepcion = DB::table('modulos')->where('Url', 'laravel-actas-recepcion')->first();
        }

        // Agregar módulo hijo: Órdenes de Compra
        $modOrdenesCompra = DB::table('modulos')->where('Url', 'laravel-ordenes-compra')->first();
        if (!$modOrdenesCompra) {
            DB::table('modulos')->insert([
                'Label' => 'Órdenes de Compra',
                'Url' => 'laravel-ordenes-compra',
                'Icono' => 'fas fa-shopping-cart',
                'ModuloPadreId' => $parentId,
                'Orden' => 140,
                'Padre' => 0,
            ]);
            $modOrdenesCompra = DB::table('modulos')->where('Url', 'laravel-ordenes-compra')->first();
        }

        // Agregar módulo hijo: Pagos
        $modPagos = DB::table('modulos')->where('Url', 'laravel-pagos')->first();
        if (!$modPagos) {
            DB::table('modulos')->insert([
                'Label' => 'Pagos',
                'Url' => 'laravel-pagos',
                'Icono' => 'fas fa-money-bill-wave',
                'ModuloPadreId' => $parentId,
                'Orden' => 150,
                'Padre' => 0,
            ]);
            $modPagos = DB::table('modulos')->where('Url', 'laravel-pagos')->first();
        }

        // Agregar módulo hijo: Órdenes de Pago
        $modOrdenesPago = DB::table('modulos')->where('Url', 'laravel-ordenes-pago')->first();
        if (!$modOrdenesPago) {
            DB::table('modulos')->insert([
                'Label' => 'Órdenes de Pago',
                'Url' => 'laravel-ordenes-pago',
                'Icono' => 'fas fa-file-invoice',
                'ModuloPadreId' => $parentId,
                'Orden' => 160,
                'Padre' => 0,
            ]);
            $modOrdenesPago = DB::table('modulos')->where('Url', 'laravel-ordenes-pago')->first();
        }

        // Agregar módulo hijo: Deuda
        $modDeuda = DB::table('modulos')->where('Url', 'laravel-deuda')->first();
        if (!$modDeuda) {
            DB::table('modulos')->insert([
                'Label' => 'Deuda',
                'Url' => 'laravel-deuda',
                'Icono' => 'fas fa-chart-line',
                'ModuloPadreId' => $parentId,
                'Orden' => 170,
                'Padre' => 0,
            ]);
            $modDeuda = DB::table('modulos')->where('Url', 'laravel-deuda')->first();
        }

        // Conceder permiso de lectura (R=1) en el menú para todos los usuarios
        $relojId = $reloj ? $reloj->IdModulo : null;
        $notaCreditoId = $notaCredito ? $notaCredito->IdModulo : null;
        $usuariosModId = $modUsuarios ? $modUsuarios->IdModulo : null;
        $rolesModId = $modRoles ? $modRoles->IdModulo : null;
        $personalModId = $modPersonalLaravel ? $modPersonalLaravel->IdModulo : null;
        $omModId = $modOMLaravel ? $modOMLaravel->IdModulo : null;
        $proveedoresModId = $modProveedores ? $modProveedores->IdModulo : null;
        $cotizacionesModId = $modCotizaciones ? $modCotizaciones->IdModulo : null;
        $pedidosInternosModId = $modPedidosInternos ? $modPedidosInternos->IdModulo : null;
        $actasRecepcionModId = $modActasRecepcion ? $modActasRecepcion->IdModulo : null;
        $ordenesCompraModId = $modOrdenesCompra ? $modOrdenesCompra->IdModulo : null;
        $pagosModId = $modPagos ? $modPagos->IdModulo : null;
        $ordenesPagoModId = $modOrdenesPago ? $modOrdenesPago->IdModulo : null;
        $deudaModId = $modDeuda ? $modDeuda->IdModulo : null;
        $usuarios = DB::table('usuarios')->select('IdUsuario')->get();
        foreach ($usuarios as $u) {
            // No forzar permisos de Personal para todos; se migran los existentes arriba
            foreach ([$relojId, $notaCreditoId, $usuariosModId, $rolesModId, $omModId, $proveedoresModId, $cotizacionesModId, $pedidosInternosModId, $actasRecepcionModId, $ordenesCompraModId, $pagosModId, $ordenesPagoModId, $deudaModId] as $modId) {
                if (!$modId) continue;
                $exists = DB::table('permisos_x_usuarios')
                    ->where('UsuarioId', $u->IdUsuario)
                    ->where('ModuloId', $modId)
                    ->exists();
                if (!$exists) {
                    DB::table('permisos_x_usuarios')->insert([
                        'UsuarioId' => $u->IdUsuario,
                        'ModuloId' => $modId,
                        'C' => 0,
                        'R' => 1,
                        'U' => 0,
                        'D' => 0,
                    ]);
                } else {
                    DB::table('permisos_x_usuarios')
                        ->where('UsuarioId', $u->IdUsuario)
                        ->where('ModuloId', $modId)
                        ->update(['R' => 1]);
                }
            }
        }
    }
}
