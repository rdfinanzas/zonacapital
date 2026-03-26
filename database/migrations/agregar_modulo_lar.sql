-- ============================================================
-- SCRIPT PARA AGREGAR MÓDULO LAR AL SISTEMA
-- ============================================================
-- Este script agrega el módulo "LAR" (Licencias Anuales por Remuneración)
-- como hijo del mismo padre que tiene el módulo "Licencias"
-- ============================================================

-- 1. Buscar el módulo padre de "Licencias" y el ID del módulo Licencias
SET @modulo_licencias_id = (SELECT IdModulo FROM modulos WHERE Url = 'licencias' AND Padre = 0 LIMIT 1);
SET @modulo_padre_id = (SELECT ModuloPadreId FROM modulos WHERE IdModulo = @modulo_licencias_id);

-- Verificar si existe el módulo LAR
SET @modulo_lar_existe = (SELECT COUNT(*) FROM modulos WHERE Url = 'licencias/lar');

-- 2. Insertar el módulo LAR si no existe
INSERT INTO modulos (Label, Url, Icono, Padre, ModuloPadreId, Estado, Orden) 
SELECT 
    'LAR', 
    'licencias/lar', 
    'fas fa-calendar-check', 
    0, 
    @modulo_padre_id, 
    1,
    (SELECT IFNULL(MAX(Orden), 0) + 1 FROM modulos m WHERE m.ModuloPadreId = @modulo_padre_id)
WHERE @modulo_lar_existe = 0;

-- Obtener el ID del módulo LAR recién creado (o existente)
SET @modulo_lar_id = (SELECT IdModulo FROM modulos WHERE Url = 'licencias/lar');

-- 3. Actualizar el Label del módulo Licencias original para diferenciarlos mejor
UPDATE modulos SET Label = 'Licencias' WHERE IdModulo = @modulo_licencias_id AND Label != 'Licencias';

-- 4. Copiar permisos de "licencias" a "licencias-lar" para todos los usuarios que tienen permisos
-- Esto asegura que quienes pueden ver licencias, también puedan ver LAR

-- Insertar permisos para usuarios que tienen permisos de licencias
INSERT IGNORE INTO permisos_x_usuario (UsuarioP_Id, ModuloP_Id, Ver, Crear, Editar, Eliminar)
SELECT 
    pxu.UsuarioP_Id,
    @modulo_lar_id,
    pxu.Ver,
    pxu.Crear,
    pxu.Editar,
    pxu.Eliminar
FROM permisos_x_usuario pxu
WHERE pxu.ModuloP_Id = @modulo_licencias_id
AND NOT EXISTS (
    SELECT 1 FROM permisos_x_usuario pxu2 
    WHERE pxu2.UsuarioP_Id = pxu.UsuarioP_Id 
    AND pxu2.ModuloP_Id = @modulo_lar_id
);

-- 5. Copiar permisos para roles que tienen permisos de licencias
INSERT IGNORE INTO permisos_x_rol (RolP_Id, ModuloP_Id, Ver, Crear, Editar, Eliminar)
SELECT 
    pxr.RolP_Id,
    @modulo_lar_id,
    pxr.Ver,
    pxr.Crear,
    pxr.Editar,
    pxr.Eliminar
FROM permisos_x_rol pxr
WHERE pxr.ModuloP_Id = @modulo_licencias_id
AND NOT EXISTS (
    SELECT 1 FROM permisos_x_rol pxr2 
    WHERE pxr2.RolP_Id = pxr.RolP_Id 
    AND pxr2.ModuloP_Id = @modulo_lar_id
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
WHERE m.Url LIKE '%licencia%'
ORDER BY m.ModuloPadreId, m.Orden;

-- Verificación: Contar permisos asignados
SELECT 
    'Usuarios con permisos LAR' as Tipo,
    COUNT(*) as Cantidad
FROM permisos_x_usuario 
WHERE ModuloP_Id = @modulo_lar_id
UNION ALL
SELECT 
    'Roles con permisos LAR' as Tipo,
    COUNT(*) as Cantidad
FROM permisos_x_rol 
WHERE ModuloP_Id = @modulo_lar_id;
