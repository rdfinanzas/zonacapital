-- ============================================================
-- SCRIPT PARA AGREGAR MÓDULO INFORME DE LICENCIAS AL SISTEMA
-- ============================================================
-- Este script agrega el módulo "Informe de Licencias" como hijo
-- del mismo padre que tiene el módulo "Licencias"
-- ============================================================

-- 1. Buscar el módulo padre de "Licencias" y el ID del módulo Licencias
SET @modulo_licencias_id = (SELECT IdModulo FROM modulos WHERE Url = 'licencias' AND Padre = 0 LIMIT 1);
SET @modulo_padre_id = (SELECT ModuloPadreId FROM modulos WHERE IdModulo = @modulo_licencias_id);

-- Verificar si existe el módulo Informe de Licencias
SET @modulo_informe_existe = (SELECT COUNT(*) FROM modulos WHERE Url = 'licencias/informe-licencias');

-- 2. Insertar el módulo Informe de Licencias si no existe
INSERT INTO modulos (Label, Url, Icono, Padre, ModuloPadreId, Estado, Orden)
SELECT
    'Informe de Licencias',
    'licencias/informe-licencias',
    'fas fa-file-alt',
    0,
    @modulo_padre_id,
    1,
    (SELECT IFNULL(MAX(Orden), 0) + 1 FROM modulos m WHERE m.ModuloPadreId = @modulo_padre_id)
WHERE @modulo_informe_existe = 0;

-- Obtener el ID del módulo Informe de Licencias recién creado (o existente)
SET @modulo_informe_id = (SELECT IdModulo FROM modulos WHERE Url = 'licencias/informe-licencias');

-- 3. Copiar permisos de "licencias" a "informe-licencias" para todos los usuarios que tienen permisos
-- Esto asegura que quienes pueden ver licencias, también puedan ver el informe

-- Insertar permisos para usuarios que tienen permisos de licencias
INSERT IGNORE INTO permisos_x_usuario (UsuarioP_Id, ModuloP_Id, Ver, Crear, Editar, Eliminar)
SELECT
    pxu.UsuarioP_Id,
    @modulo_informe_id,
    pxu.Ver,
    pxu.Crear,
    pxu.Editar,
    pxu.Eliminar
FROM permisos_x_usuario pxu
WHERE pxu.ModuloP_Id = @modulo_licencias_id
AND NOT EXISTS (
    SELECT 1 FROM permisos_x_usuario pxu2
    WHERE pxu2.UsuarioP_Id = pxu.UsuarioP_Id
    AND pxu2.ModuloP_Id = @modulo_informe_id
);

-- 4. Copiar permisos para roles que tienen permisos de licencias
INSERT IGNORE INTO permisos_x_rol (RolP_Id, ModuloP_Id, Ver, Crear, Editar, Eliminar)
SELECT
    pxr.RolP_Id,
    @modulo_informe_id,
    pxr.Ver,
    pxr.Crear,
    pxr.Editar,
    pxr.Eliminar
FROM permisos_x_rol pxr
WHERE pxr.ModuloP_Id = @modulo_licencias_id
AND NOT EXISTS (
    SELECT 1 FROM permisos_x_rol pxr2
    WHERE pxr2.RolP_Id = pxr.RolP_Id
    AND pxr2.ModuloP_Id = @modulo_informe_id
);

-- Verificación: Mostrar los módulos de licencias
SELECT
    m.IdModulo,
    m.Label,
    m.Url,
    m.Icono,
    m.Padre,
    m.ModuloPadreId,
    m.Estado,
    (SELECT mp.Label FROM modulos mp WHERE mp.IdModulo = m.ModuloPadreId) as PadreNombre
FROM modulos m
WHERE m.Url LIKE '%licencia%' OR m.Url LIKE '%informe%'
ORDER BY m.ModuloPadreId, m.Orden;

-- Verificación: Contar permisos asignados
SELECT
    'Usuarios con permisos Informe Licencias' as Tipo,
    COUNT(*) as Cantidad
FROM permisos_x_usuario
WHERE ModuloP_Id = @modulo_informe_id
UNION ALL
SELECT
    'Roles con permisos Informe Licencias' as Tipo,
    COUNT(*) as Cantidad
FROM permisos_x_rol
WHERE ModuloP_Id = @modulo_informe_id;
