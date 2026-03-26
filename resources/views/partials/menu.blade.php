<!-- Sidebar Wrapper -->
<style>
    /* Indent child menu items to visually separate from parents */
    .sidebar-wrapper .nav-treeview {
        margin-left: 0.4rem;
    }
    .sidebar-wrapper .nav-treeview > .nav-item > .nav-link {
        padding-left: 2.1rem;
    }
    .sidebar-wrapper .nav-treeview .nav-icon {
        margin-left: 0.15rem;
    }
</style>
<div class="sidebar-wrapper">
    <nav class="mt-2">
        <!-- Sidebar Menu -->
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
            <!-- Módulos dinámicos basados en permisos -->
            @isset($modulos)
                @php
                    // Fallback: si no hay permisos cargados, asumir lectura para mostrar el menú
                    if (empty($permisosAll)) {
                        $permisosAll = [];
                        foreach ($modulos as $m) {
                            $permisosAll[] = [
                                'moduloId' => $m->IdModulo ?? null,
                                'crear' => true,
                                'leer' => true,
                                'editar' => true,
                                'eliminar' => true,
                            ];
                        }
                    }
                @endphp
                @php
                    // Agrupar módulos por padres
                    $modulesByParent = [];
                    foreach ($modulos as $modulo) {
                        if ($modulo->Padre == 1) {
                            // Es un módulo padre
                            $modulesByParent[$modulo->IdModulo] = [
                                'parent' => $modulo,
                                'children' => [],
                            ];
                        }
                    }

                    // Agregar hijos a sus respectivos padres
                    foreach ($modulos as $modulo) {
                        // Padre puede ser 0, null o vacío para indicar que es hijo
                        $esHijo = ($modulo->Padre == 0 || $modulo->Padre === null || $modulo->Padre === '') 
                                  && $modulo->ModuloPadreId > 0;
                        if ($esHijo && isset($modulesByParent[$modulo->ModuloPadreId])) {
                            $modulesByParent[$modulo->ModuloPadreId]['children'][] = $modulo;
                        }
                    }
                @endphp

                @foreach ($modulesByParent as $parentId => $moduleGroup)
                    @php
                        $parentModule = $moduleGroup['parent'];
                        $childModules = $moduleGroup['children'];
                        $hasPermittedChild = false;

                        // Verificar si al menos un hijo tiene permisos
                        if (is_array($permisosAll) || is_object($permisosAll)) {
                            foreach ($childModules as $childModule) {
                                foreach ($permisosAll as $permiso) {
                                    // Debug de condiciones
                                    // Puedes ver esto en el log de Laravel (storage/logs/laravel.log)
                                    /*
                                    echo '<pre>';
                                    print_r([
                                        'permiso' => $permiso,
                                        'moduloId_permiso' => $permiso['moduloId'] ?? null,
                                        'moduloId_child' => $childModule->IdModulo,
                                        'crear' => $permiso['crear'] ?? null,
                                        'leer' => $permiso['leer'] ?? null,
                                        'editar' => $permiso['editar'] ?? null,
                                        'eliminar' => $permiso['eliminar'] ?? null,
                                        'condicion_moduloId' =>
                                            isset($permiso['moduloId']) &&
                                            $permiso['moduloId'] == $childModule->IdModulo,
                                        'condicion_crear' => isset($permiso['crear']) && $permiso['crear'],
                                        'condicion_leer' => isset($permiso['leer']) && $permiso['leer'],
                                        'condicion_editar' => isset($permiso['editar']) && $permiso['editar'],
                                        'condicion_eliminar' => isset($permiso['eliminar']) && $permiso['eliminar'],
                                    ]);
                                    echo '</pre>';*/
                                    if (
                                        is_array($permiso) &&
                                        isset($permiso['moduloId']) &&
                                        $permiso['moduloId'] == $childModule->IdModulo &&
                                        ((isset($permiso['crear']) && $permiso['crear']) ||
                                            (isset($permiso['leer']) && $permiso['leer']) ||
                                            (isset($permiso['editar']) && $permiso['editar']) ||
                                            (isset($permiso['eliminar']) && $permiso['eliminar']))
                                    ) {
                                        $hasPermittedChild = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    @endphp

                    @if ($hasPermittedChild && count($childModules) > 0)
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-dot-circle"></i>
                                <p>
                                    {{ $parentModule->Label }}
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview nav-child-indent">
                                @foreach ($childModules as $childModule)
                                    @php
                                        $hasPermission = false;
                                        foreach ($permisosAll as $permiso) {
                                            if (
                                                $permiso['moduloId'] == $childModule->IdModulo &&
                                                ($permiso['crear'] ||
                                                    $permiso['leer'] ||
                                                    $permiso['editar'] ||
                                                    $permiso['eliminar'])
                                            ) {
                                                $hasPermission = true;
                                                break;
                                            }
                                        }
                                    @endphp

                                    @if ($hasPermission)
                                        <li class="nav-item">
                                            <a href="{{ url(str_replace('laravel-', '', $childModule->Url)) }}"
                                                class="nav-link">
                                                <i class="nav-icon  {{ $childModule->Icono }}"></i>
                                                <p>{{ $childModule->Label }}</p>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach
            @endisset

            <!-- Cerrar Sesión - Siempre visible -->
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form-menu').submit();">
                    <i class="nav-icon bi bi-box-arrow-right"></i>
                    <p>Cerrar Sesión</p>
                </a>
                <form id="logout-form-menu" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
        <!-- End Sidebar Menu -->
    </nav>
</div>
