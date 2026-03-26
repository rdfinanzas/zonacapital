# Form Filler - Utilidad para Llenar Formularios con Datos de Prueba

Esta utilidad genérica permite llenar automáticamente formularios con datos de prueba apropiados según el tipo de campo.

## Características

- **Genérico**: Funciona con cualquier formulario HTML
- **Inteligente**: Detecta automáticamente el tipo de campo y genera datos apropiados
- **Configurable**: Permite personalizar el comportamiento y datos específicos
- **Reutilizable**: Se puede usar en cualquier módulo del sistema

## Uso Básico

```javascript
// Llenar formulario por ID
fillFormWithTestData('formPersonal');

// Llenar formulario por selector CSS
fillFormWithTestData('#miFormulario');

// Llenar formulario pasando el elemento DOM
const form = document.getElementById('miForm');
fillFormWithTestData(form);
```

## Uso Específico para Personal

```javascript
// Función específica para el módulo de personal
fillPersonalFormWithTestData();
```

## Opciones Avanzadas

```javascript
fillFormWithTestData('formPersonal', {
    skipHidden: true,           // Saltar campos hidden (default: true)
    skipReadonly: true,         // Saltar campos readonly (default: true)
    skipDisabled: true,         // Saltar campos disabled (default: true)
    triggerEvents: true,        // Disparar eventos change (default: true)
    skipFieldsWithData: false,  // Saltar campos con datos (default: false)
    debug: true,                // Mostrar información debug (default: false)
    customData: {               // Datos personalizados por campo
        'legajo': '2024',
        'dni': '12345678',
        'email': 'test@example.com'
    }
});
```

## Tipos de Campos Soportados

### Campos de Texto
- **Nombres**: Genera nombres aleatorios
- **Apellidos**: Genera apellidos aleatorios
- **DNI**: Genera números de documento válidos
- **CUIT/CUIL**: Genera códigos de identificación
- **Direcciones**: Genera direcciones de ejemplo
- **Empresas**: Genera nombres de empresas

### Campos Especiales
- **Email**: Genera emails de prueba
- **Teléfonos**: Genera números telefónicos
- **Fechas**: Genera fechas aleatorias en formato DD/MM/YYYY
- **Números**: Genera números apropiados según contexto (edad, cantidad, etc.)

### Controles
- **Select**: Selecciona opción aleatoria válida
- **Checkbox**: Marca/desmarca aleatoriamente
- **Radio**: Selecciona una opción del grupo
- **Textarea**: Inserta texto descriptivo

## Funciones Auxiliares

```javascript
// Limpiar formulario
clearFormData('formPersonal');

// Llenar solo campos específicos
fillSpecificFields('formPersonal', ['nombre', 'apellido', 'dni']);
```

## Detección Inteligente de Campos

La función detecta automáticamente el tipo de campo basándose en:

1. **Tipo HTML**: `type="email"`, `type="date"`, etc.
2. **Nombre del campo**: campos que contengan "fecha", "email", "telefono", etc.
3. **Clases CSS**: `.datepicker`, `.select2`, etc.
4. **Etiqueta HTML**: `<select>`, `<textarea>`, etc.

## Integración con Librerías

- **Select2**: Dispara eventos apropiados para actualizar Select2
- **Datepickers**: Detecta campos de fecha automáticamente
- **Validadores**: Dispara eventos `input` y `change` para activar validaciones

## Ejemplos de Datos Generados

```javascript
// Ejemplos de datos que genera la función:
nombres: ['Juan', 'María', 'Carlos', 'Ana', 'Luis']
apellidos: ['García', 'Rodríguez', 'González', 'Fernández']
emails: ['test@example.com', 'prueba@test.com']
telefonos: ['11-1234-5678', '11-2345-6789']
fechas: '15/03/1990', '22/07/1985'
dni: '12345678', '87654321'
```

## Modo Debug

Activa el modo debug para ver qué campos se están llenando:

```javascript
fillFormWithTestData('formPersonal', { debug: true });
```

Esto mostrará en la consola:
```
Llenando formulario: <form id="formPersonal">
Campo llenado: nombre = Juan
Campo llenado: apellido = García
Campo llenado: dni = 12345678
Formulario llenado. Campos procesados: 25
```

## Personalización de Datos

Puedes proporcionar datos específicos para campos particulares:

```javascript
fillFormWithTestData('formPersonal', {
    customData: {
        'nombre': 'Juan Carlos',
        'email': 'mi.email@test.com',
        'telefono': '11-5555-5555',
        'fecha_nacimiento': '01/01/1990'
    }
});
```

## Uso en Consola del Navegador

También puedes usar la función directamente en la consola del navegador para pruebas rápidas:

```javascript
// En la consola del navegador
fillFormWithTestData('formPersonal');
```

## Notas Importantes

- La función respeta campos `readonly`, `disabled` y `hidden` por defecto
- No llena campos de tipo `file` por seguridad
- Dispara eventos apropiados para que las validaciones y librerías funcionen correctamente
- Es segura para usar en producción (puedes ocultar el botón en producción)

## Botón de Prueba

En el formulario de personal se incluye un botón "Datos de Prueba" que activa la función automáticamente. Este botón se puede ocultar en producción modificando la vista.
