-- =============================================================================
-- MIGRACIÓN: Tabla empleados.idServicio → empleado_servicio
-- =============================================================================

-- Verificar cuántos registros se crearán
SELECT COUNT(*) AS total_a_migrar
FROM empleados e
WHERE e.idServicio > 0
AND e.Estado = 1
AND e.idEmpleado NOT IN (
    SELECT DISTINCT empleado_id FROM empleado_servicio WHERE activo = 1
)
AND e.idEmpleado NOT IN (
    SELECT idEmpleado FROM empleados WHERE FBaja > '2000-01-01'
);

-- =============================================================================
-- MIGRAR: Insertar registros en empleado_servicio para empleados legacy
-- =============================================================================
INSERT INTO empleado_servicio (
    empleado_id,
    servicio_id,
    certificador_id,
    fecha_inicio,
    activo,
    motivo,
    created_at,
    updated_at
)
SELECT 
    e.idEmpleado,
    e.idServicio,
    e.IdEmpleado2,
    '2020-01-01',
    1,
    'Migracion automatica desde tabla empleados (legacy)',
    NOW(),
    NOW()
FROM empleados e
WHERE e.idServicio > 0
AND e.Estado = 1
AND e.idEmpleado NOT IN (
    SELECT DISTINCT empleado_id 
    FROM empleado_servicio 
    WHERE activo = 1
)
AND e.idEmpleado NOT IN (
    SELECT idEmpleado FROM empleados WHERE FBaja > '2000-01-01'
);

-- =============================================================================
-- VERIFICAR: Comparar que ambos campos estén sincronizados
-- =============================================================================
SELECT 
    e.idEmpleado,
    e.Legajo,
    e.Apellido,
    e.Nombre,
    e.idServicio AS idServicio_tabla_empleados,
    es.servicio_id AS idServicio_empleado_servicio,
    CASE 
        WHEN e.idServicio = es.servicio_id THEN 'OK'
        ELSE 'DISCREPANCIA'
    END AS estado
FROM empleados e
LEFT JOIN (
    SELECT empleado_id, servicio_id 
    FROM empleado_servicio 
    WHERE activo = 1
) es ON e.idEmpleado = es.empleado_id
WHERE e.idServicio > 0
AND e.Estado = 1
AND e.idEmpleado NOT IN (
    SELECT idEmpleado FROM empleados WHERE FBaja > '2000-01-01'
)
LIMIT 20;