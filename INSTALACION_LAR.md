# Instalación del Módulo LAR Separado

## Resumen de Cambios

Se ha separado el módulo de Licencias en dos módulos independientes:

1. **Licencias** (`/licencias`) - Para licencias normales con motivo
2. **LAR** (`/licencias/lar`) - Para Licencias Anuales por Remuneración

## Cambios Realizados

### Archivos Nuevos/Modificados

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `resources/views/licencias-lar.blade.php` | Nuevo | Vista exclusiva para LAR |
| `resources/views/licencias.blade.php` | Modificado | Vista solo para licencias normales |
| `public/js/licencias-lar.js` | Nuevo | JavaScript para módulo LAR |
| `public/js/licencias.js` | Modificado | JavaScript solo para licencias |
| `app/Http/Controllers/LicenciasController.php` | Modificado | Agregado método `indexLar()` |
| `routes/web.php` | Modificado | Agregada ruta `/licencias/lar` |
| `database/migrations/agregar_modulo_lar.sql` | Nuevo | Script SQL para permisos |

## Características del Módulo LAR

- ✅ **Formulario simplificado** sin selector de motivo (no aplica para LAR)
- ✅ **Botón "Ver Parámetros LAR"** que abre un modal
- ✅ **Modal con scroll** (max-height: 300px) para los parámetros
- ✅ **Historial de LAR** a la derecha, más espacioso y con mejor diseño
- ✅ **Diseño responsive** con columnas mejor distribuidas

## Instrucciones de Instalación

### Paso 1: Ejecutar el SQL

Ejecutar el script SQL para agregar el módulo a la base de datos:

```bash
# Usando MySQL directamente
mysql -u usuario -p nombre_base_datos < database/migrations/agregar_modulo_lar.sql

# O usando phpMyAdmin
# Importar el archivo: database/migrations/agregar_modulo_lar.sql
```

### Paso 2: Verificar Permisos

El SQL automáticamente copiará los permisos de "Licencias" a "LAR" para todos los usuarios y roles que ya tienen acceso.

Para verificar:

```sql
-- Ver módulos de licencias
SELECT * FROM modulos WHERE Url LIKE '%licencia%';

-- Ver permisos asignados
SELECT u.Usuario, m.Label, pxu.Ver, pxu.Crear, pxu.Editar, pxu.Eliminar
FROM permisos_x_usuario pxu
JOIN usuarios u ON pxu.UsuarioP_Id = u.IdUsuario
JOIN modulos m ON pxu.ModuloP_Id = m.IdModulo
WHERE m.Url = 'licencias/lar';
```

### Paso 3: Limpiar Caché (si es necesario)

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Paso 4: Acceder al Sistema

- **Licencias Normales**: `/licencias`
- **LAR**: `/licencias/lar`

Ambos deberían aparecer en el sidebar bajo el mismo menú padre.

## Notas Importantes

1. **Lógica de Cálculo Preservada**: La función de cálculo de fechas (`calcularXDia`, `calcularXFecha`) se mantiene igual en ambos módulos.

2. **Base de Datos Compartida**: Ambos módulos usan la misma tabla `licencias`, pero:
   - LAR: `Motivo_Id = NULL` y `AnioLar` tiene valor
   - Licencias: `Motivo_Id` tiene valor

3. **API Endpoints Compartidos**: Ambos usan los mismos endpoints:
   - `POST /licencias` - Guardar
   - `PUT /licencias/{id}` - Actualizar
   - `DELETE /licencias/{id}` - Eliminar
   - `GET /licencias/legajo/{legajo}` - Obtener datos

4. **Permisos Heredados**: El script SQL copia automáticamente los permisos del módulo "Licencias" original al nuevo módulo "LAR".

## Solución de Problemas

### El módulo no aparece en el sidebar
1. Verificar que el SQL se ejecutó correctamente
2. Verificar que el usuario tiene permisos en `permisos_x_usuario` o su rol en `permisos_x_rol`
3. Limpiar caché de sesión y recargar

### Error 404 en /licencias/lar
1. Verificar que la ruta está definida en `routes/web.php`
2. Verificar que el método `indexLar()` existe en el controlador
3. Ejecutar `php artisan route:clear`
