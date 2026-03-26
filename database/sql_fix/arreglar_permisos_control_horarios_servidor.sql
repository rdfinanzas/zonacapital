-- ============================================================
-- VERIFICAR Y ARREGLAR PERMISOS DE CONTROL DE HORARIOS
-- Para replicar la configuración de LOCAL a SERVIDOR
-- ============================================================
-- En LOCAL funciona con:
-- - Módulo ID: 102 (laravel-control-horarios)
-- - Permiso Extra ID: 27 (todo_personal_control)
-- - Rol ADMIN (ID: 1) tiene el permiso
-- ============================================================

-- ============================================================
-- PASO 1: VERIFICAR MÓDULO EN SERVIDOR
-- ============================================================
SELECT '=== VERIFICANDO MÓDULO CONTROL HORARIOS ===' AS '';
SELECT
    IdModulo,
    Label,
    Url,
    Icono
FROM modulos
WHERE Url = 'laravel-control-horarios';

-- Si NO devuelve resultados, crear el módulo:
-- INSERT INTO modulos (Label, Url, Icono, ModuloPadreId, Orden, Padre)
-- VALUES ('Control de Horarios', 'laravel-control-horarios', 'fas fa-clock', 0, 0, 0);

-- ============================================================
-- PASO 2: OBTENER ID DEL MÓDULO (GUARDAR ESTE VALOR)
-- ============================================================
-- Ejecutar esto y ANOTAR el IdModulo que devuelve:
SELECT IdModulo INTO @id_modulo
FROM modulos
WHERE Url = 'laravel-control-horarios';

SELECT @id_modulo AS 'ID del Módulo (GUARDAR ESTE VALOR)';

-- ============================================================
-- PASO 3: VERIFICAR PERMISO EXTRA
-- ============================================================
SELECT '=== VERIFICANDO PERMISO EXTRA ===' AS '';
SELECT
    pe.IdPermisoExtra,
    pe.PermisoExtra,
    pe.Clave,
    pe.ModuloId,
    CASE
        WHEN pe.ModuloId = @id_modulo THEN 'CORRECTO'
        ELSE 'INCORRECTO - Debería ser ' + CAST(@id_modulo AS CHAR)
    END AS Estado
FROM permisos_extras pe
WHERE pe.Clave = 'todo_personal_control';

-- ============================================================
-- PASO 4: SI NO EXISTE EL PERMISO EXTRA, CREARLO
-- ============================================================
-- Descomentar si el permiso extra no existe:
-- INSERT INTO permisos_extras (PermisoExtra, ModuloId, Clave)
-- VALUES ('Ver todo el personal', @id_modulo, 'todo_personal_control');

-- ============================================================
-- PASO 5: SI EXISTE PERO CON ModuloId INCORRECTO, ACTUALIZAR
-- ============================================================
-- Descomentar si el permiso existe pero apunta al módulo equivocado:
-- UPDATE permisos_extras
-- SET ModuloId = @id_modulo
-- WHERE Clave = 'todo_personal_control';

-- ============================================================
-- PASO 6: OBTENER ID DEL PERMISO EXTRA
-- ============================================================
SELECT IdPermisoExtra INTO @id_permiso_extra
FROM permisos_extras
WHERE Clave = 'todo_personal_control';

SELECT @id_permiso_extra AS 'ID del Permiso Extra (GUARDAR ESTE VALOR)';

-- ============================================================
-- PASO 7: VERIFICAR ROLES CON PERMISO
-- ============================================================
SELECT '=== ROLES CON PERMISO TODO_PERSONAL_CONTROL ===' AS '';
SELECT
    pext.TipoUsuarioId,
    t.UsuarioTipo,
    pext.Permiso
FROM permisos_extras_x_tipos_usuarios pext
INNER JOIN usuario_tipos t ON t.IdUsuarioTipo = pext.TipoUsuarioId
WHERE pext.PermisoExtraId = @id_permiso_extra;

-- ============================================================
-- PASO 8: ASIGNAR PERMISO AL ROL ADMIN
-- ============================================================
-- Descomentar para asignar el permiso al rol ADMIN (ID: 1):
-- INSERT IGNORE INTO permisos_extras_x_tipos_usuarios (TipoUsuarioId, PermisoExtraId, Permiso)
-- VALUES (1, @id_permiso_extra, 1);

-- ============================================================
-- PASO 9: VERIFICAR PERMISOS CRUD DEL MÓDULO
-- ============================================================
SELECT '=== PERMISOS CRUD DEL MÓDULO ===' AS '';
SELECT
    pt.TipoUsuarioId,
    t.UsuarioTipo,
    pt.C,
    pt.R,
    pt.U,
    pt.D
FROM permisos_x_tipos_usuarios pt
INNER JOIN usuario_tipos t ON t.IdUsuarioTipo = pt.TipoUsuarioId
WHERE pt.ModuloId = @id_modulo;

-- ============================================================
-- PASO 10: ASIGNAR PERMISOS CRUD AL ROL ADMIN
-- ============================================================
-- Descomentar para dar permisos completos al ADMIN:
-- INSERT IGNORE INTO permisos_x_tipos_usuarios (TipoUsuarioId, ModuloId, C, R, U, D)
-- VALUES (1, @id_modulo, 1, 1, 1, 1);

-- ============================================================
-- RESUMEN FINAL
-- ============================================================
SELECT '=== RESUMEN FINAL ===' AS '';
SELECT
    'Módulo' AS Concepto,
    IdModulo AS ID,
    Label AS Nombre,
    Url AS Valor
FROM modulos
WHERE Url = 'laravel-control-horarios'

UNION ALL

SELECT
    'Permiso Extra',
    CAST(pe.IdPermisoExtra AS CHAR),
    pe.PermisoExtra,
    pe.Clave
FROM permisos_extras pe
WHERE pe.Clave = 'todo_personal_control'

UNION ALL

SELECT
    'Asignación Rol-Permiso',
    CAST(pext.TipoUsuarioId AS CHAR),
    t.UsuarioTipo,
    CASE WHEN pext.Permiso = 1 THEN 'SI' ELSE 'NO' END
FROM permisos_extras_x_tipos_usuarios pext
INNER JOIN usuario_tipos t ON t.IdUsuarioTipo = pext.TipoUsuarioId
WHERE pext.PermisoExtraId = (SELECT IdPermisoExtra FROM permisos_extras WHERE Clave = 'todo_personal_control');
