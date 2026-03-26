-- ============================================================
-- ASIGNAR PERMISO "VER TODOS LOS SERVICIOS" A TIPOS DE USUARIO
-- ============================================================
-- Este script asigna el permiso extra "Ver todos los servicios"
-- del módulo "Informe de Licencias" a los tipos de usuario especificados
-- ============================================================

-- Permiso Extra ID: 24
-- Clave: ver_todos_servicios
-- Módulo ID: 129 (Informe de Licencias)

-- TABLA: usuario_tipos
-- CAMPO ID: IdUsuarioTipo
-- CAMPO NOMBRE: UsuarioTipo

-- ============================================================
-- EJEMPLOS DE ASIGNACIÓN
-- ============================================================

-- Ejemplo 1: Asignar al tipo de usuario ADMIN (ID: 1)
INSERT IGNORE INTO permisos_extras_x_tipos_usuarios (TipoUsuarioId, PermisoExtraId, Permiso)
VALUES (1, 24, 1);

-- Ejemplo 2: Asignar al tipo de usuario Jefe de servicio (ID: 13)
-- INSERT IGNORE INTO permisos_extras_x_tipos_usuarios (TipoUsuarioId, PermisoExtraId, Permiso)
-- VALUES (13, 24, 1);

-- Ejemplo 3: Asignar al tipo de usuario Personal (ID: 11)
-- INSERT IGNORE INTO permisos_extras_x_tipos_usuarios (TipoUsuarioId, PermisoExtraId, Permiso)
-- VALUES (11, 24, 1);

-- ============================================================
-- VERIFICACIÓN
-- ============================================================

-- Mostrar todos los tipos de usuario con el permiso
SELECT
    t.IdUsuarioTipo,
    t.UsuarioTipo,
    pe.PermisoExtra,
    pe.Clave,
    pext.Permiso as TienePermiso
FROM usuario_tipos t
JOIN permisos_extras_x_tipos_usuarios pext ON t.IdUsuarioTipo = pext.TipoUsuarioId
JOIN permisos_extras pe ON pext.PermisoExtraId = pe.IdPermisoExtra
WHERE pe.Clave = 'ver_todos_servicios';

-- ============================================================
-- LISTA DE TIPOS DE USUARIO DISPONIBLES
-- ============================================================

SELECT
    IdUsuarioTipo,
    UsuarioTipo
FROM usuario_tipos
ORDER BY UsuarioTipo;
