# Funcionalidades Faltantes en Programación de Horarios

## 🚨 FUNCIONES CRÍTICAS FALTANTES

### 1. Sistema de Guardias (CRÍTICO)
**Estado**: ❌ NO IMPLEMENTADO
**Impacto**: ALTO - Funcionalidad principal del módulo

**Funciones del original**:
- `ProgramacionHorarioModel::insertarGuardia()`
- `ProgramacionHorarioModel::deleteGuardiaXId()`
- `ProgramacionHorarioModel::deleteGuardiaXFechaYEmp()`
- `ProgramacionHorarioModel::updateGuardia()`
- `guardarGuardias()` en JavaScript

**Rutas necesarias**:
```php
Route::post('/guardias/guardar', [ProgramacionPersonalController::class, 'guardarGuardias']);
Route::delete('/guardias/{id}', [ProgramacionPersonalController::class, 'eliminarGuardia']);
Route::get('/guardias/empleado/{id}', [ProgramacionPersonalController::class, 'obtenerGuardiasEmpleado']);
```

### 2. Exportación a Excel (CRÍTICO)
**Estado**: ❌ FUNCIÓN PLACEHOLDER
**Impacto**: ALTO - Reporte requerido

**Original tiene dos tipos**:
- `exportarExcel()` - Informe general
- `exportarExcelTurnos()` - Informe por turnos

**Endpoint original**: `php/Informe_2.php?accion=informeProgramacion`

**Implementar**:
```php
public function exportarExcel(Request $request)
public function exportarExcelTurnos(Request $request)
```

### 3. Modales de Interfaz (ALTO)
**Estado**: ❌ NO IMPLEMENTADOS
**Impacto**: ALTO - UX degradada

**Modales faltantes**:
- Modal programación por día (`modal_programacion_dia`)
- Modal cargar horario masivo (`modal_cargar_horario`)
- Modal de guardias

### 4. Gestión de Eliminación (MEDIO)
**Estado**: ❌ NO IMPLEMENTADO
**Impacto**: MEDIO - Funcionalidad administrativa

**Funciones originales**:
- `eliminarHorario(id, fila, columna, tipo)`
- `eliminarGuardia(ind, id)`
- `deleteProgramacionXId()`
- `eliminarProgramacionXRangoEmpleadoId()`

**Rutas necesarias**:
```php
Route::delete('/programacion/{id}', [ProgramacionPersonalController::class, 'eliminarProgramacion']);
Route::delete('/programacion/rango', [ProgramacionPersonalController::class, 'eliminarRango']);
```

### 5. Funciones de Rotación y Programación Avanzada (MEDIO)
**Estado**: ⚠️ PARCIALMENTE IMPLEMENTADO
**Impacto**: MEDIO - Funcionalidad específica

**Original**:
- Rotaciones por días (`rotativo_ini`, `rotativo_fin`)
- Programación sin feriados (`no_feriados`)
- Validaciones de solapamiento

### 6. Cálculos de Horas y Totales (MEDIO)
**Estado**: ❌ NO IMPLEMENTADO EN FRONTEND
**Impacto**: MEDIO - Información de gestión

**JavaScript original**:
- `calcularHorasXFila()`
- `sumarTotalesCeldas()`
- `calcularDiferenciaHoras()`

## 🔧 IMPLEMENTACIÓN REQUERIDA

### Prioridad 1 - CRÍTICO
1. **Sistema completo de guardias**
2. **Exportación a Excel funcional** 
3. **Modales de interfaz principales**

### Prioridad 2 - ALTO  
1. **Funciones de eliminación**
2. **Validaciones de solapamiento**
3. **Cálculos de horas en frontend**

### Prioridad 3 - MEDIO
1. **Rotaciones avanzadas**
2. **Gestión de feriados**
3. **Funciones de auditoría**

## 📊 ESTIMACIÓN DE DESARROLLO

- **Sistema de Guardias**: 2-3 días
- **Exportación Excel**: 1-2 días  
- **Modales**: 1-2 días
- **Eliminaciones**: 1 día
- **Frontend avanzado**: 2-3 días

**TOTAL ESTIMADO**: 7-11 días de desarrollo

## 🚦 ESTADO ACTUAL

**Funcionalidad básica**: ✅ 60% implementada
**Funcionalidad completa**: ❌ 40% implementada
**Listo para producción**: ❌ NO

## ⚠️ RECOMENDACIÓN

El módulo requiere implementar las funcionalidades críticas antes de considerar la migración completa. Sin el sistema de guardias y exportación, el módulo no cumple con los requisitos operativos básicos.
