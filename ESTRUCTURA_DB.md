# 📊 ESTRUCTURA BASE DE DATOS - zoncap_zonacap
**Generado:** 2025-12-23 16:12:39

---

## 🏢 TABLAS PRINCIPALES DEL SISTEMA

### 👥 EMPLEADOS (`empleados`)
**Campos principales:**
- `idEmpleado` (PK, int, auto_increment) - ID único del empleado
- `Apellido` (varchar(50), NOT NULL) - Apellido del empleado
- `Nombre` (varchar(50), NOT NULL) - Nombre del empleado
- `DNI` (int, NOT NULL) - Documento Nacional de Identidad
- `Legajo` (int, NOT NULL, UNIQUE) - Legajo único del empleado
- `idCargo` (int, NOT NULL) - FK a tabla cargos
- `idServicio` (int, NOT NULL) - FK a tabla servicios
- `idDepartamento` (int, NOT NULL) - FK a tabla departamento
- `idGerencia` (int, NOT NULL) - FK a tabla gerencia
- `Estado` (int, NOT NULL) - Estado del empleado (1=activo, 0=inactivo)
- `Jefe` (int) - FK que apunta a otro empleado que es el jefe
- `Email` (varchar(45)) - Email del empleado
- `Telefono` (varchar(45)) - Teléfono del empleado
- `Celular` (varchar(45)) - Celular del empleado

**Relaciones:**
- `idGerencia` → `gerencia.IdGerencia`
- `idDepartamento` → `departamento.idDepartamento`
- `idServicio` → `servicios.IdServicio`
- `Jefe` → `empleados.idEmpleado` (autoreferencia)

---

### 🏛️ GERENCIAS (`gerencia`)
**Campos:**
- `IdGerencia` (PK, int, auto_increment)
- `Gerencia` (varchar(50), NOT NULL) - Nombre de la gerencia

**Ejemplo:** Dirección Ejecutiva

---

### 🏢 DEPARTAMENTOS (`departamento`)
**Campos:**
- `idDepartamento` (PK, int, auto_increment)
- `departamento` (varchar(50), NOT NULL) - Nombre del departamento
- `idGerencia` (int, NOT NULL) - FK a gerencia

**Relaciones:**
- `idGerencia` → `gerencia.IdGerencia`

**Ejemplos:**
- Jefe Área programática
- Jefe Área Administrativa

---

### 🔧 SERVICIOS (`servicios`)
**Campos:**
- `IdServicio` (PK, int unsigned, auto_increment)
- `Servicio` (varchar(255)) - Nombre del servicio
- `Responsable` (varchar(255)) - Responsable del servicio
- `Ubicacion` (varchar(255)) - Ubicación del servicio
- `Notas` (text) - Notas adicionales
- `FechaCreacion` (datetime)
- `Creador_Id` (int unsigned) - Quien creó el registro

**Ejemplos:**
- Patrimonio
- Compras
- Contabilidad y tesorería

---

### ⏰ HORARIOS PERSONAL (`horario_x_personal`)
**Campos actuales:**
- `IdHorarioXPersonal` (int) - ID del registro
- `Horario_Id` (int) - ID del horario
- `Empleado_Id` (int) - FK al empleado
- `FechaCreacion` (date) - Fecha de creación
- `Creador_Id` (int) - Quien creó el horario
- `Dia` (int) - Día relacionado

**⚠️ NOTA:** Esta tabla tiene estructura diferente al modelo Laravel creado. 
**Modelo Laravel esperado:**
- `id` (PK)
- `empleado_id` (FK a empleados.idEmpleado)
- `fecha` (date)
- `hora_inicio` (time)
- `hora_fin` (time)
- `tipo` (int) - 0=normal, 1=guardia contrato, 2=guardia paga

---

### 🔐 PERMISOS (`permisos_x_usuarios`)
**Campos:**
- `UsuarioId` (PK, int unsigned) - FK al usuario
- `ModuloId` (PK, tinyint unsigned) - FK al módulo
- `C` (tinyint(1)) - Permiso de crear (create)
- `R` (tinyint(1)) - Permiso de leer (read)
- `U` (tinyint(1)) - Permiso de actualizar (update)
- `D` (tinyint(1)) - Permiso de eliminar (delete)

**Relaciones:**
- `UsuarioId` → `usuario.IdUsuario`
- `ModuloId` → `modulos.IdModulo`

---

### 📋 MÓDULOS (`modulos`)
**Campos:**
- `IdModulo` (PK, tinyint unsigned, auto_increment)
- `Label` (varchar(256)) - Nombre del módulo
- `Url` (varchar(100)) - URL del módulo
- `Icono` (varchar(256)) - Icono a mostrar
- `ModuloPadreId` (tinyint) - FK al módulo padre
- `Orden` (tinyint unsigned) - Orden de visualización
- `Padre` (tinyint(1)) - Si es módulo padre (1) o hijo (0)

**Ejemplos:**
- Usuarios (fas fa-lock)
- Sistema (fa fa-desktop)
- Logs (fas fa-history)

---

### 🏥 LICENCIAS (`licencias`)
**Campos principales:**
- `IdLicencia` (PK, int, auto_increment)
- `FechaLic` (date) - Fecha inicio de licencia
- `FechaLicFin` (date) - Fecha fin de licencia
- `LegajoPersonal` (int) - FK al empleado (por legajo)
- `DiasTotal` (int) - Total de días de licencia
- `FechaCreacion` (date)
- `Creador_Id` (int)

**Relaciones:**
- `LegajoPersonal` → `empleados.Legajo`

---

### 🅿️ FRANCOS (`francos`)
**Campos:**
- `IdFranco` (PK, int, auto_increment)
- `LegFranco` (varchar(10)) - Legajo del empleado
- `HorasFran` (time) - Horas de franco
- `Mes` (int) - Mes del franco
- `Anio` (int) - Año del franco
- `FechaCreacion` (datetime)
- `Creador_Id` (int)

---

## 🔗 RELACIONES PRINCIPALES

```
empleados
├── idGerencia → gerencia.IdGerencia
├── idDepartamento → departamento.idDepartamento
├── idServicio → servicios.IdServicio
└── Jefe → empleados.idEmpleado (autoreferencia)

departamento
└── idGerencia → gerencia.IdGerencia

horario_x_personal
└── Empleado_Id → empleados.idEmpleado

permisos_x_usuarios
├── UsuarioId → usuario.IdUsuario
└── ModuloId → modulos.IdModulo

licencias
└── LegajoPersonal → empleados.Legajo

francos
└── LegFranco → empleados.Legajo
```

---

## 📝 CONSULTAS ÚTILES

### Obtener empleados con información completa:
```sql
SELECT 
    e.idEmpleado, e.Apellido, e.Nombre, e.DNI,
    g.Gerencia,
    d.departamento as Departamento,
    s.Servicio
FROM empleados e
LEFT JOIN gerencia g ON e.idGerencia = g.IdGerencia
LEFT JOIN departamento d ON e.idDepartamento = d.idDepartamento  
LEFT JOIN servicios s ON e.idServicio = s.IdServicio
WHERE e.Estado = 1;
```

### Obtener jefes:
```sql
SELECT e.* FROM empleados e 
WHERE e.Estado = 1 AND e.idCargo IN (1,2,3,4,5);
```

### Obtener empleados por jefe:
```sql
SELECT e.* FROM empleados e 
WHERE e.Estado = 1 AND e.Jefe = ?;
```

---

## ⚠️ NOTAS IMPORTANTES

1. **Campo Jefe**: En tabla empleados, el campo `Jefe` contiene el `idEmpleado` del jefe directo
2. **Estados**: `Estado = 1` significa empleado activo
3. **Cargos**: Los valores 1,2,3,4,5 en `idCargo` corresponden a jefes
4. **Horarios**: La tabla actual `horario_x_personal` tiene estructura diferente al modelo Laravel
5. **Legajos**: Se usan tanto `idEmpleado` como `Legajo` como identificadores
6. **Permisos**: Sistema CRUD con valores 0/1 para cada operación

---

## 📊 OTRAS TABLAS RELEVANTES

- `usuario` - Tabla de usuarios del sistema
- `cargos` - Catálogo de cargos
- `horarios` - Catálogo de horarios
- `guardias` - Registros de guardias
- `turnos` - Definición de turnos
- `feriados` - Calendario de feriados
- `marcas_reloj` - Marcaciones de reloj biométrico

---

*Estructura generada automáticamente para referencia rápida en desarrollo*
