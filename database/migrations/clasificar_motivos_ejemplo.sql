-- ============================================
-- SCRIPT DE CLASIFICACIÓN DE MOTIVOS DE LICENCIA
-- ============================================
-- Ejecutar este script para asignar módulos a los motivos existentes
-- 
-- Módulos disponibles:
--   95 = Orden Médica (licencias médicas)
--   88 = Licencias (licencias administrativas/no médicas)
--   NULL = General (visible en todos los módulos)
-- ============================================

-- Primero, ver los motivos actuales y sus IDs
SELECT IdMotivoLicencia, Motivo, DiasMax, ModuloId 
FROM motivo_licencia 
WHERE FechaEliminacion IS NULL 
ORDER BY Motivo;

-- ============================================
-- EJEMPLOS DE CLASIFICACIÓN
-- Modifica según tus necesidades
-- ============================================

-- Motivos médicos -> Orden Médica (95)
UPDATE motivo_licencia SET ModuloId = 95 
WHERE Motivo LIKE '%salud%' 
   OR Motivo LIKE '%medica%'
   OR Motivo LIKE '%accidente%'
   OR Motivo LIKE '%enfermedad%'
   OR Motivo LIKE '%afección%'
   OR Motivo LIKE '%hospital%'
   AND FechaEliminacion IS NULL;

-- Motivos de estudio/familiares -> Licencias (88)
UPDATE motivo_licencia SET ModuloId = 88 
WHERE Motivo LIKE '%estudio%'
   OR Motivo LIKE '%examen%'
   OR Motivo LIKE '%familiar%'
   OR Motivo LIKE '%matrimonio%'
   OR Motivo LIKE '%nacimiento%'
   OR Motivo LIKE '%fallecimiento%'
   OR Motivo LIKE '%mudanza%'
   AND FechaEliminacion IS NULL;

-- Verificar resultado
SELECT 
    m.IdMotulo,
    m.Label as Modulo,
    COUNT(ml.IdMotivoLicencia) as CantidadMotivos
FROM modulos m
LEFT JOIN motivo_licencia ml ON m.IdModulo = ml.ModuloId AND ml.FechaEliminacion IS NULL
WHERE m.IdModulo IN (88, 95)
GROUP BY m.IdModulo, m.Label;

-- Motivos sin clasificar (visibles en todos los módulos)
SELECT IdMotivoLicencia, Motivo, ObservacionMot
FROM motivo_licencia 
WHERE ModuloId IS NULL 
  AND FechaEliminacion IS NULL
ORDER BY Motivo;
