<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertAdultoMayorModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Buscar o crear módulo padre "Salud" (o similar)
        $moduloPadre = DB::table('modulos')->where('Label', 'Salud')->first();

        if (!$moduloPadre) {
            // Buscar el último orden de módulos padre
            $ultimoOrdenPadre = DB::table('modulos')
                ->where('Padre', 1)
                ->max('Orden') ?? 0;

            // Crear módulo padre "Salud"
            $moduloPadreId = DB::table('modulos')->insertGetId([
                'Label' => 'Salud',
                'Url' => '#',
                'Icono' => 'fas fa-heartbeat',
                'ModuloPadreId' => 0,
                'Orden' => $ultimoOrdenPadre + 1,
                'Padre' => 1
            ]);
            
            echo "Módulo padre 'Salud' creado con ID: $moduloPadreId\n";
        } else {
            $moduloPadreId = $moduloPadre->IdModulo;
            echo "Módulo padre 'Salud' ya existe con ID: $moduloPadreId\n";
        }

        // Verificar si ya existe el módulo Adulto Mayor
        $moduloExiste = DB::table('modulos')
            ->where('Url', 'like', '%laravel-adulto-mayor%')
            ->first();

        if (!$moduloExiste) {
            // Obtener el último orden dentro del padre
            $ultimoOrden = DB::table('modulos')
                ->where('ModuloPadreId', $moduloPadreId)
                ->max('Orden') ?? 0;

            // Insertar módulo Adulto Mayor
            $adultoMayorModuloId = DB::table('modulos')->insertGetId([
                'Label' => 'Adulto Mayor',
                'Url' => 'laravel-adulto-mayor',
                'Icono' => 'fas fa-pills',
                'ModuloPadreId' => $moduloPadreId,
                'Orden' => $ultimoOrden + 1,
                'Padre' => 0
            ]);

            echo "Módulo 'Adulto Mayor' creado con ID: $adultoMayorModuloId\n";

            // Dar permisos completos al super admin (tipo usuario 1)
            $permisoExiste = DB::table('permisos_x_tipos_usuarios')
                ->where('TipoUsuarioId', 1)
                ->where('ModuloId', $adultoMayorModuloId)
                ->first();

            if (!$permisoExiste) {
                DB::table('permisos_x_tipos_usuarios')->insert([
                    'TipoUsuarioId' => 1,
                    'ModuloId' => $adultoMayorModuloId,
                    'C' => 1,
                    'R' => 1,
                    'U' => 1,
                    'D' => 1
                ]);
                echo "Permisos asignados al super admin\n";
            }

            // Copiar permisos desde módulos similares (PAP, Salud Mental)
            $modulosSimilares = DB::table('modulos')
                ->whereIn('Url', ['laravel-pap', 'laravel-salud-mental', 'pap', 'salud-mental'])
                ->pluck('IdModulo');

            foreach ($modulosSimilares as $moduloSimilarId) {
                $permisosSimilares = DB::table('permisos_x_tipos_usuarios')
                    ->where('ModuloId', $moduloSimilarId)
                    ->get();

                foreach ($permisosSimilares as $permiso) {
                    if ($permiso->TipoUsuarioId != 1) { // Ya asignamos al admin
                        $permisoSimilarExiste = DB::table('permisos_x_tipos_usuarios')
                            ->where('TipoUsuarioId', $permiso->TipoUsuarioId)
                            ->where('ModuloId', $adultoMayorModuloId)
                            ->first();

                        if (!$permisoSimilarExiste) {
                            DB::table('permisos_x_tipos_usuarios')->insert([
                                'TipoUsuarioId' => $permiso->TipoUsuarioId,
                                'ModuloId' => $adultoMayorModuloId,
                                'C' => $permiso->C,
                                'R' => $permiso->R,
                                'U' => $permiso->U,
                                'D' => $permiso->D
                            ]);
                        }
                    }
                }
            }

            // Copiar permisos por usuario (columnas: UsuarioId, ModuloId, C, R, U, D)
            foreach ($modulosSimilares as $moduloSimilarId) {
                $permisosUsuarios = DB::table('permisos_x_usuarios')
                    ->where('ModuloId', $moduloSimilarId)
                    ->get();

                foreach ($permisosUsuarios as $permiso) {
                    $permisoUsuarioExiste = DB::table('permisos_x_usuarios')
                        ->where('UsuarioId', $permiso->UsuarioId)
                        ->where('ModuloId', $adultoMayorModuloId)
                        ->first();

                    if (!$permisoUsuarioExiste) {
                        DB::table('permisos_x_usuarios')->insert([
                            'UsuarioId' => $permiso->UsuarioId,
                            'ModuloId' => $adultoMayorModuloId,
                            'C' => $permiso->C,
                            'R' => $permiso->R,
                            'U' => $permiso->U,
                            'D' => $permiso->D
                        ]);
                    }
                }
            }

            echo "Permisos copiados desde módulos similares\n";
        } else {
            echo "El módulo Adulto Mayor ya existe\n";
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Obtener ID del módulo
        $moduloId = DB::table('modulos')
            ->where('Url', 'like', '%laravel-adulto-mayor%')
            ->value('IdModulo');

        if ($moduloId) {
            // Eliminar permisos
            DB::table('permisos_x_tipos_usuarios')->where('ModuloId', $moduloId)->delete();
            DB::table('permisos_x_usuarios')->where('ModuloId', $moduloId)->delete();

            // Eliminar módulo
            DB::table('modulos')->where('IdModulo', $moduloId)->delete();
        }
    }
}
