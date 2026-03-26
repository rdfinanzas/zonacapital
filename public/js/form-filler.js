/**
 * Utilidad genérica para llenar formularios con datos de prueba
 * ============================================================
 * 
 * Esta función llena automáticamente todos los campos de un formulario
 * con datos de prueba apropiados según el tipo de campo.
 * 
 * Uso:
 * fillFormWithTestData('formPersonal');
 * fillFormWithTestData('#miFormulario');
 * fillFormWithTestData(document.getElementById('miForm'));
 */

/**
 * Datos de prueba predefinidos
 */
const testData = {
    nombres: ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Elena', 'Pedro', 'Laura', 'Miguel', 'Carmen'],
    apellidos: ['García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Martín'],
    emails: ['test@example.com', 'prueba@test.com', 'demo@mail.com'],
    telefonos: ['11-1234-5678', '11-2345-6789', '11-3456-7890'],
    direcciones: ['Av. Corrientes 1234', 'Calle Falsa 123', 'San Martín 567', 'Belgrano 890'],
    ciudades: ['Buenos Aires', 'Córdoba', 'Rosario', 'Mendoza', 'La Plata'],
    empresas: ['Empresa Test', 'Compañía Demo', 'Organización Prueba'],
    descripciones: [
        'Este es un texto de prueba para campos de descripción.',
        'Contenido de ejemplo para testing.',
        'Datos de prueba para validación del formulario.'
    ]
};

/**
 * Función principal para llenar formulario con datos de prueba
 * @param {string|HTMLElement} formSelector - Selector CSS, ID del formulario o elemento DOM
 * @param {Object} options - Opciones de configuración
 */
function fillFormWithTestData(formSelector, options = {}) {
    const defaultOptions = {
        skipHidden: true,           // Saltar campos hidden
        skipReadonly: true,         // Saltar campos readonly
        skipDisabled: true,         // Saltar campos disabled
        triggerEvents: true,        // Disparar eventos change después de llenar
        skipFieldsWithData: false,  // Saltar campos que ya tienen datos
        customData: {},             // Datos personalizados por campo
        debug: false                // Mostrar información de debug
    };

    const config = { ...defaultOptions, ...options };

    // Obtener el elemento del formulario
    let form;
    if (typeof formSelector === 'string') {
        if (formSelector.startsWith('#')) {
            form = document.getElementById(formSelector.substring(1));
        } else if (formSelector.startsWith('.')) {
            form = document.querySelector(formSelector);
        } else {
            form = document.getElementById(formSelector);
        }
    } else if (formSelector instanceof HTMLElement) {
        form = formSelector;
    }

    if (!form) {
        console.error('Formulario no encontrado:', formSelector);
        return;
    }

    if (config.debug) {
        console.log('Llenando formulario:', form);
    }

    // Obtener todos los campos del formulario
    const fields = form.querySelectorAll('input, select, textarea');

    fields.forEach(field => {
        try {
            fillField(field, config);
        } catch (error) {
            console.warn('Error llenando campo:', field.name || field.id, error);
        }
    });

    if (config.debug) {
        console.log(`Formulario llenado. Campos procesados: ${fields.length}`);
    }
}

/**
 * Llenar un campo individual
 * @param {HTMLElement} field - Campo a llenar
 * @param {Object} config - Configuración
 */
function fillField(field, config) {
    const fieldName = field.name || field.id || '';
    const fieldType = field.type || field.tagName.toLowerCase();

    // Verificar si debemos saltar este campo
    if (shouldSkipField(field, config)) {
        return;
    }

    // Usar datos personalizados si están disponibles
    if (config.customData[fieldName]) {
        setFieldValue(field, config.customData[fieldName], config);
        return;
    }

    // Generar valor según el tipo de campo
    const value = generateValueByType(field, fieldType, fieldName);

    if (value !== null) {
        setFieldValue(field, value, config);
    }
}

/**
 * Verificar si debemos saltar un campo
 * @param {HTMLElement} field - Campo a verificar
 * @param {Object} config - Configuración
 * @returns {boolean}
 */
function shouldSkipField(field, config) {
    // Saltar campos hidden
    if (config.skipHidden && field.type === 'hidden') {
        return true;
    }

    // Saltar campos readonly
    if (config.skipReadonly && field.readOnly) {
        return true;
    }

    // Saltar campos disabled
    if (config.skipDisabled && field.disabled) {
        return true;
    }

    // Saltar campos que ya tienen datos
    if (config.skipFieldsWithData && field.value && field.value.trim() !== '') {
        return true;
    }

    // Saltar campos de archivo
    if (field.type === 'file') {
        return true;
    }

    return false;
}

/**
 * Generar valor según el tipo de campo
 * @param {HTMLElement} field - Campo
 * @param {string} fieldType - Tipo de campo
 * @param {string} fieldName - Nombre del campo
 * @returns {string|null}
 */
function generateValueByType(field, fieldType, fieldName) {
    const nameLower = fieldName.toLowerCase();

    // Fechas
    if (fieldType === 'date' || nameLower.includes('fecha') || field.classList.contains('datepicker')) {
        return generateRandomDate();
    }

    // Emails
    if (fieldType === 'email' || nameLower.includes('email') || nameLower.includes('mail')) {
        return getRandomItem(testData.emails);
    }

    // Teléfonos
    if (fieldType === 'tel' || nameLower.includes('telefono') || nameLower.includes('celular') || nameLower.includes('phone')) {
        return getRandomItem(testData.telefonos);
    }

    // Números
    if (fieldType === 'number' || nameLower.includes('edad') || nameLower.includes('cantidad')) {
        return generateRandomNumber(fieldName);
    }

    // Selectores
    if (fieldType === 'select' || field.tagName.toLowerCase() === 'select') {
        return getRandomSelectOption(field);
    }

    // Checkboxes
    if (fieldType === 'checkbox') {
        return Math.random() > 0.5; // 50% probabilidad
    }

    // Radio buttons
    if (fieldType === 'radio') {
        return handleRadioButton(field);
    }

    // Textareas
    if (field.tagName.toLowerCase() === 'textarea') {
        return getRandomItem(testData.descripciones);
    }

    // Campos de texto específicos por nombre
    if (nameLower.includes('nombre')) {
        return getRandomItem(testData.nombres);
    }
    if (nameLower.includes('apellido')) {
        return getRandomItem(testData.apellidos);
    }
    if (nameLower.includes('dni') || nameLower.includes('documento')) {
        return generateRandomDNI();
    }
    if (nameLower.includes('legajo')) {
        return generateRandomLegajo();
    }
    if (nameLower.includes('cuit') || nameLower.includes('cuil')) {
        return generateRandomCUIT();
    }
    if (nameLower.includes('direccion') || nameLower.includes('calle')) {
        return getRandomItem(testData.direcciones);
    }
    if (nameLower.includes('ciudad') || nameLower.includes('localidad')) {
        return getRandomItem(testData.ciudades);
    }
    if (nameLower.includes('empresa') || nameLower.includes('organizacion')) {
        return getRandomItem(testData.empresas);
    }
    if (nameLower.includes('cp') || nameLower.includes('postal')) {
        return generateRandomCP();
    }

    // Campo de texto genérico
    if (fieldType === 'text' || fieldType === 'search') {
        return 'Texto de prueba';
    }

    return null;
}

/**
 * Establecer valor en un campo
 * @param {HTMLElement} field - Campo
 * @param {any} value - Valor a establecer
 * @param {Object} config - Configuración
 */
function setFieldValue(field, value, config) {
    if (field.type === 'checkbox') {
        field.checked = Boolean(value);
    } else if (field.type === 'radio') {
        if (value) field.checked = true;
    } else {
        field.value = value;
    }

    // Disparar eventos si está habilitado
    if (config.triggerEvents) {
        triggerFieldEvents(field);
    }

    if (config.debug) {
        console.log(`Campo llenado: ${field.name || field.id} = ${value}`);
    }
}

/**
 * Disparar eventos en un campo
 * @param {HTMLElement} field - Campo
 */
function triggerFieldEvents(field) {
    // Crear y disparar evento 'input'
    const inputEvent = new Event('input', { bubbles: true });
    field.dispatchEvent(inputEvent);

    // Crear y disparar evento 'change'
    const changeEvent = new Event('change', { bubbles: true });
    field.dispatchEvent(changeEvent);

    // Para Select2, disparar evento específico
    if (field.classList.contains('select2') || field.parentElement.querySelector('.select2')) {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $(field).trigger('change');
        }
    }
}

/**
 * Obtener opción aleatoria de un select
 * @param {HTMLSelectElement} select - Elemento select
 * @returns {string|null}
 */
function getRandomSelectOption(select) {
    const options = Array.from(select.options).filter(option => option.value && option.value !== '');
    if (options.length === 0) return null;

    const randomOption = getRandomItem(options);
    return randomOption.value;
}

/**
 * Manejar radio buttons
 * @param {HTMLElement} field - Campo radio
 * @returns {boolean}
 */
function handleRadioButton(field) {
    const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
    if (radioGroup.length > 0) {
        // Seleccionar uno aleatoriamente del grupo
        const randomIndex = Math.floor(Math.random() * radioGroup.length);
        if (field === radioGroup[randomIndex]) {
            return true;
        }
    }
    return false;
}

/**
 * Generar fecha aleatoria
 * @returns {string}
 */
function generateRandomDate() {
    const start = new Date(1980, 0, 1);
    const end = new Date();
    const randomTime = start.getTime() + Math.random() * (end.getTime() - start.getTime());
    const randomDate = new Date(randomTime);

    const day = String(randomDate.getDate()).padStart(2, '0');
    const month = String(randomDate.getMonth() + 1).padStart(2, '0');
    const year = randomDate.getFullYear();

    return `${day}/${month}/${year}`;
}

/**
 * Generar número aleatorio según el contexto
 * @param {string} fieldName - Nombre del campo
 * @returns {number}
 */
function generateRandomNumber(fieldName) {
    const nameLower = fieldName.toLowerCase();

    if (nameLower.includes('edad')) {
        return Math.floor(Math.random() * 60) + 18; // 18-78 años
    }
    if (nameLower.includes('cantidad')) {
        return Math.floor(Math.random() * 100) + 1; // 1-100
    }

    return Math.floor(Math.random() * 1000) + 1; // Número genérico
}

/**
 * Generar DNI aleatorio
 * @returns {string}
 */
function generateRandomDNI() {
    return String(Math.floor(Math.random() * 90000000) + 10000000);
}

/**
 * Generar legajo aleatorio
 * @returns {string}
 */
function generateRandomLegajo() {
    return String(Math.floor(Math.random() * 9000) + 1000);
}

/**
 * Generar CUIT aleatorio
 * @returns {string}
 */
function generateRandomCUIT() {
    const dni = generateRandomDNI();
    const prefijo = Math.random() > 0.5 ? '20' : '27';
    const verificador = Math.floor(Math.random() * 10);
    return `${prefijo}-${dni}-${verificador}`;
}

/**
 * Generar código postal aleatorio
 * @returns {string}
 */
function generateRandomCP() {
    return String(Math.floor(Math.random() * 9000) + 1000);
}

/**
 * Obtener elemento aleatorio de un array
 * @param {Array} array - Array de elementos
 * @returns {any}
 */
function getRandomItem(array) {
    return array[Math.floor(Math.random() * array.length)];
}

/**
 * Función de ayuda para limpiar formulario
 * @param {string|HTMLElement} formSelector - Selector del formulario
 */
function clearFormData(formSelector) {
    let form;
    if (typeof formSelector === 'string') {
        form = document.getElementById(formSelector.replace('#', ''));
    } else {
        form = formSelector;
    }

    if (form && form.reset) {
        form.reset();
    }
}

/**
 * Función para llenar solo campos específicos
 * @param {string|HTMLElement} formSelector - Selector del formulario
 * @param {Array} fieldNames - Array de nombres de campos a llenar
 */
function fillSpecificFields(formSelector, fieldNames) {
    const customData = {};

    fieldNames.forEach(fieldName => {
        // Generar datos específicos para cada campo
        const field = document.querySelector(`[name="${fieldName}"], #${fieldName}`);
        if (field) {
            const value = generateValueByType(field, field.type, fieldName);
            if (value !== null) {
                customData[fieldName] = value;
            }
        }
    });

    fillFormWithTestData(formSelector, { customData });
}

// Hacer las funciones disponibles globalmente
window.fillFormWithTestData = fillFormWithTestData;
window.clearFormData = clearFormData;
window.fillSpecificFields = fillSpecificFields;

// Función de conveniencia para el módulo de personal
window.fillPersonalFormWithTestData = function () {
    fillFormWithTestData('formPersonal', {
        debug: true,
        customData: {
            // Datos específicos para el formulario de personal
            legajo: '2024',
            dni: '12345678',
            cuit: '20-12345678-9',
            sexo: '1',
            estado_civil: '1',
            nacionalidad: '1',
            estado: '1'
        }
    });
};

console.log('✅ Form Filler cargado. Usa fillFormWithTestData("formId") para llenar formularios con datos de prueba.');
