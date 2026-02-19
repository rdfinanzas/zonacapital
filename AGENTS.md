# Documentación del Sistema - Zona Capital Laravel

## Arquitectura del Sistema de Módulos y Sidebar

### 1. Estructura de Módulos

Los módulos del sistema se almacenan en la tabla `modulos` de la base de datos. Cada módulo tiene las siguientes propiedades clave:

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| `IdModulo` | ID único del módulo | 107 |
| `Label` | Nombre visible en el menú | "LAR" |
| `Url` | URL del módulo (con prefijo `laravel-` para módulos nuevos) | `laravel-licencias/lar-lista` |
| `Icono` | Clase de icono FontAwesome | `fas fa-calendar-check` |
| `Padre` | 1=Es padre, 0=Es hijo | 0 |
| `ModuloPadreId` | ID del módulo padre (si es hijo) | 78 |
| `Orden` | Orden de aparición en el menú | 5 |

### 2. Funcionamiento del Sidebar (MenuHelper)

El sidebar se genera dinámicamente mediante `App\Helpers\MenuHelper::obtenerModulos()`:

```php
// Filtra módulos que contengan 'laravel' en la URL
$modulos = DB::table('modulos')
    ->where(function ($query) {
        $query->where('Url', 'like', '%laravel%')
              ->orWhereIn('IdModulo', function ($sub) {
                  $sub->select('ModuloPadreId')
                      ->from('modulos')
                      ->where('Url', 'like', '%laravel%');
              });
    })
    ->orderBy('ModuloPadreId')
    ->orderBy('Orden')
    ->get();
```

**Proceso de renderizado en `resources/views/partials/menu.blade.php`**:

1. **Agrupación por padres**: Los módulos se agrupan por `ModuloPadreId`
2. **Verificación de permisos**: Se verifica que el usuario tenga al menos un permiso (C, R, U, D) en el módulo
3. **Transformación de URL**: Se remueve el prefijo `laravel-` al generar el enlace:
   ```blade
   {{ url(str_replace('laravel-', '', $childModule->Url)) }}
   ```
   
   Ejemplo: `laravel-licencias/lar-lista` → `/licencias/lar-lista`

### 3. Permisos de Usuarios

Los permisos se almacenan en dos tablas:

#### Permisos por Usuario (Legacy)
- Tabla: `permisos_x_usuario`
- Campos: `UsuarioP_Id`, `ModuloP_Id`, `Ver`, `Crear`, `Editar`, `Eliminar`

#### Permisos por Tipo de Usuario (Legacy)
- Tabla: `permisos_x_tipos_usuarios`
- Campos: `TipoUsuarioId`, `ModuloId`, `C`, `R`, `U`, `D`

### 4. Flujo para Agregar un Nuevo Módulo al Sidebar

Para agregar un nuevo módulo que aparezca en el sidebar, seguir estos pasos:

#### Paso 1: Crear Controlador
```php
// app/Http/Controllers/NuevoModuloController.php
namespace App\Http\Controllers;

class NuevoModuloController extends Controller
{
    public function index()
    {
        $permisos = PermisoHelper::obtenerPermisos(session('usuario_id'), 'nombre-modulo');
        return view('nuevo-modulo', compact('permisos'));
    }
}
```

#### Paso 2: Crear Vista
```php
// resources/views/nuevo-modulo.blade.php
@extends('layouts.app')
@section('content')
    // Contenido del módulo
@endsection
```

#### Paso 3: Definir Rutas
```php
// routes/web.php
Route::prefix('licencias')->middleware('puede.ver')->group(function () {
    Route::get('/nuevo-modulo', [NuevoModuloController::class, 'index'])
         ->name('nombre-modulo');
});
```

#### Paso 4: Crear Migración del Módulo
```php
// database/migrations/XXXX_XX_XX_agregar_modulo_nuevo.php
public function up()
{
    // Buscar el módulo padre
    $moduloPadre = DB::table('modulos')
        ->where('Url', 'licencias')  // O el padre correspondiente
        ->where('Padre', 0)
        ->first();
    
    if ($moduloPadre) {
        $moduloPadreId = $moduloPadre->ModuloPadreId;
        
        // Verificar si ya existe
        $existe = DB::table('modulos')
            ->where('Url', 'laravel-licencias/nuevo-modulo')
            ->exists();
        
        if (!$existe) {
            // Obtener último orden
            $ultimoOrden = DB::table('modulos')
                ->where('ModuloPadreId', $moduloPadreId)
                ->max('Orden') ?? 0;
            
            // Insertar módulo
            $nuevoModuloId = DB::table('modulos')->insertGetId([
                'Label' => 'Nombre del Módulo',
                'Url' => 'laravel-licencias/nuevo-modulo',  // IMPORTANTE: prefijo laravel-
                'Icono' => 'fas fa-icono',
                'Padre' => 0,
                'ModuloPadreId' => $moduloPadreId,
                'Orden' => $ultimoOrden + 1
            ]);
            
            // Copiar permisos desde módulo relacionado
            $this->copiarPermisos($moduloPadre->IdModulo, $nuevoModuloId);
        }
    }
}
```

#### Paso 5: Ejecutar Migración
```bash
php artisan migrate --force
```

### 5. Convenciones Importantes

#### URLs de Módulos
- **Módulos Laravel nuevos**: Usar prefijo `laravel-` en la URL de la BD
  - Ejemplo: `laravel-licencias/lar-lista`
  - El sistema automáticamente lo convierte a `/licencias/lar-lista`

- **Módulos legacy**: No llevan prefijo
  - Ejemplo: `licencias`, `personal`

#### Nombres de Rutas
- Usar notación kebab-case: `nombre-modulo`
- Para acciones específicas: `nombre-modulo.accion`
  - Ejemplos: `lar-lista.filtrar`, `licencias.store`

#### Controladores
- Suffix `Controller`: `LicenciasController`
- Métodos RESTful preferidos:
  - `index()` - Lista/vista principal
  - `store()` - Crear
  - `update()` - Actualizar
  - `destroy()` - Eliminar
  - `show()` - Mostrar detalle

### 6. Estructura de Datos de LAR (Licencia Anual por Remuneración)

#### Modelo: `App\Models\Licencia`

**Campos principales de una LAR**:
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `IdLicencia` | int | ID único |
| `LegajoPersonal` | int | Legajo del empleado |
| `Motivo_Id` | int | NULL para LAR (identificador) |
| `FechaLic` | date | Fecha inicio |
| `FechaLicFin` | date | Fecha fin |
| `DiasTotal` | int | Cantidad de días |
| `AnioLar` | int | Año de la licencia |
| `FechaCreacion` | date | Fecha de creación del registro |
| `OrdenMedica` | int | 0 o NULL para LAR (diferenciador de OM) |
| `ObservacionLic` | text | Observaciones |
| `NumDisp` | int | Disposición |
| `Creador_Id` | int | Usuario que creó el registro |

**Identificación de LAR vs Orden Médica vs Licencia común**:
```php
// Es LAR: Motivo_Id IS NULL AND (OrdenMedica IS NULL OR OrdenMedica = 0)
// Es Orden Médica: OrdenMedica IS NOT NULL AND OrdenMedica != 0
// Es Licencia común: Motivo_Id IS NOT NULL AND (OrdenMedica IS NULL OR OrdenMedica = 0)
```

#### Cálculo de Días LAR

El sistema calcula automáticamente los días que le corresponden a cada empleado según:

1. **Antigüedad**: Calculada desde la fecha de alta en la Administración Pública (`FAltaAP`)
2. **Tipo de Relación Laboral**: (`idTipoRelacion` - Permanente, Contrato, etc.)
3. **Año de la LAR**: Cada año puede tener diferentes parámetros

**Fórmula de antigüedad**:
```sql
{$anio} - YEAR(FAltaAP) + IF(DATE_FORMAT('{$anio}-12-31','%m-%d') > DATE_FORMAT(FAltaAP,'%m-%d'), 0, -1)
```

**Tablas de parámetros**:
- `parametros_lar`: Define los parámetros base por tipo de relación y año de vigencia
- `param_lar_detalle`: Define los rangos de antigüedad y días correspondientes

**Ejemplo de visualización en lista**:
| Tomados | Corresponde | Pendiente |
|---------|-------------|-----------|
| 10 | 25 | 15 (verde) |
| 30 | 25 | -5 (rojo) |

**Métodos involucrados**:
- `LicenciasController::calcularDiasLarParaEmpleado()` - Calcula días para un empleado/año
- `LicenciasController::obtenerDiasLarParametro()` - Obtiene días según parámetros
- `LicenciasController::getDiasLar()` - API para el formulario

#### API Endpoints de LAR

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/licencias/lar-lista` | Vista lista de LAR |
| GET | `/licencias/lar-lista/filtrar` | Filtrar LAR (AJAX) |
| GET | `/licencias/lar` | Formulario LAR |
| GET | `/licencias/legajo/{legajo}` | Obtener LAR por legajo |
| POST | `/licencias` | Crear LAR |
| PUT | `/licencias/{id}` | Actualizar LAR |
| DELETE | `/licencias/{id}` | Eliminar LAR |
| GET | `/licencias/imprimir/lar/{id}` | Imprimir LAR (PDF) |

### 7. Flujo de Trabajo LAR

#### 7.1 Lista de LAR (`/licencias/lar-lista`)

1. **Usuario hace clic en "LAR" en el sidebar**
   - URL: `/licencias/lar-lista`
   - Controlador: `LicenciasController@listarLar`

2. **Lista de LAR carga vía AJAX**
   - Endpoint: `/licencias/lar-lista/filtrar`
   - Método: `filtrarLar()`
   - Retorna: JSON con datos paginados incluyendo:
     - Datos básicos de la LAR
     - **Días calculados**: tomados, correspondientes, pendientes
     - Información del personal
   - **Cálculo de días**: Para cada LAR, el sistema calcula en tiempo real:
     - Días que le corresponden según antigüedad y parámetros
     - Días ya tomados en ese año por el empleado
     - Días pendientes (corresponde - tomados)

3. **Columnas mostradas**:
   - ID, Fecha, Agente, DNI, Legajo
   - Desde, Hasta
   - **Tomados**: Días de esta LAR
   - **Corresponde**: Días totales que le corresponden en el año
   - **Pendiente**: Días restantes (verde si positivo, rojo si negativo)
   - Año LAR, Disposición, Observaciones, Creador, Acciones

#### 7.2 Formulario LAR (`/licencias/lar`)

4. **Usuario hace clic en "Nueva LAR"**
   - Redirige a: `/licencias/lar`
   - Controlador: `LicenciasController@indexLar`

5. **Usuario hace clic en "Editar"**
   - Redirige a: `/licencias/lar?editar={id}&legajo={legajo}`
   - El JS `licencias-lar.js` detecta los parámetros y carga los datos

6. **Guardar LAR**
   - POST/PUT a: `/licencias` o `/licencias/{id}`
   - Método: `store()` o `update()`
   - Validaciones: No puede exceder los días pendientes

### 8. Helpers Importantes

#### PermisoHelper
```php
// Verificar permisos del usuario
$permisos = PermisoHelper::obtenerPermisos($usuarioId, 'nombre-modulo');

// Verificar si es super admin
$esAdmin = PermisoHelper::esSuperAdmin($usuarioId);
```

#### MenuHelper
```php
// Obtener módulos para el sidebar
$modulos = MenuHelper::obtenerModulos($usuarioId);
```

### 9. Debugging

Para ver los logs del sistema:
```bash
tail -f storage/logs/laravel.log
```

Logs importantes para el menú:
- `MenuHelper::obtenerModulos()` - Módulos disponibles
- `CheckSessionMiddleware` - Variables compartidas con vistas

### 10. Checklist para Nuevo Módulo

- [ ] Controlador creado con método `index()`
- [ ] Vista Blade creada en `resources/views/`
- [ ] Rutas definidas en `routes/web.php`
- [ ] Migración creada para insertar en tabla `modulos`
- [ ] URL con prefijo `laravel-` en la BD
- [ ] Permisos copiados para usuarios/roles
- [ ] Icono seleccionado (FontAwesome)
- [ ] Orden definido en el menú
- [ ] Prueba de acceso al módulo
- [ ] Prueba de permisos (usuarios sin permiso no ven el menú)

---

## Contacto y Soporte

Para dudas sobre la arquitectura del sistema, consultar:
- Estructura de módulos: `app/Helpers/MenuHelper.php`
- Permisos: `app/Helpers/PermisoHelper.php`
- Middleware de sesión: `app/Http/Middleware/CheckSessionMiddleware.php`
