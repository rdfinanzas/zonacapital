// ========================================
// INDICADOR DE CARGA GLOBAL
// ========================================

// Variables globales para el manejo del indicador de carga
let contadorSolicitudes = 0;
let loadingOverlay = null;
let animacionPuntos = null;

/**
 * Crear el overlay de carga con animación de puntos
 */
function crearIndicadorCarga() {
    if (loadingOverlay) return;

    loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loading-overlay';
    loadingOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        font-family: Arial, sans-serif;
    `;

    const contenidoCarga = document.createElement('div');
    contenidoCarga.style.cssText = `
        background: white;
        padding: 20px 40px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        font-size: 18px;
        color: #333;
    `;

    const textoCarga = document.createElement('span');
    textoCarga.textContent = 'Cargando';

    const puntosAnimados = document.createElement('span');
    puntosAnimados.id = 'puntos-animados';
    puntosAnimados.style.display = 'inline-block';
    puntosAnimados.style.width = '20px';
    puntosAnimados.style.textAlign = 'left';

    contenidoCarga.appendChild(textoCarga);
    contenidoCarga.appendChild(puntosAnimados);
    loadingOverlay.appendChild(contenidoCarga);

    document.body.appendChild(loadingOverlay);

    // Iniciar animación de puntos
    iniciarAnimacionPuntos();
}

/**
 * Iniciar la animación de los puntos suspensivos
 */
function iniciarAnimacionPuntos() {
    if (animacionPuntos) return;

    let contador = 0;
    animacionPuntos = setInterval(() => {
        const puntosElement = document.getElementById('puntos-animados');
        if (puntosElement) {
            contador = (contador + 1) % 4; // Ciclo de 0 a 3
            puntosElement.textContent = '.'.repeat(contador);
        }
    }, 500); // Cambia cada 500ms
}

/**
 * Mostrar el indicador de carga
 */
function mostrarIndicadorCarga() {
    contadorSolicitudes++;
    if (contadorSolicitudes === 1) {
        crearIndicadorCarga();
    }
}

/**
 * Ocultar el indicador de carga
 */
function ocultarIndicadorCarga() {
    contadorSolicitudes--;
    if (contadorSolicitudes <= 0) {
        contadorSolicitudes = 0;

        // Detener animación
        if (animacionPuntos) {
            clearInterval(animacionPuntos);
            animacionPuntos = null;
        }

        // Remover overlay
        if (loadingOverlay) {
            document.body.removeChild(loadingOverlay);
            loadingOverlay = null;
        }
    }
}

// ========================================
// EJEMPLOS DE USO DE apiLaravel
// ========================================

//
//Ejemplos de uso:
//
//1. Petición GET:
//   apiLaravel('/api/usuarios', 'GET', { filtro: 'activos' })
//     .then(respuesta => {
//         // Procesar respuesta exitosa
//         console.log(respuesta);
//     })
//     .catch(error => {
//         // Procesar error
//         console.error('Error:', error);
//     });
//
//2. Petición POST:
//   apiLaravel('/api/usuarios', 'POST', { 
//     nombre: 'Juan', 
//     email: 'juan@ejemplo.com'
//   })
//     .then(respuesta => {
//         // Procesar respuesta exitosa
//     })
//     .catch(error => {
//         // Procesar error
//     });
//
//3. Petición PUT:
//   apiLaravel('/api/usuarios/1', 'PUT', { nombre: 'Juan Modificado' })
//     .then(respuesta => { /* código */ })
//     .catch(error => { /* código */ });
//
//4. Petición DELETE:
//   apiLaravel('/api/usuarios/1', 'DELETE')
//     .then(respuesta => { /* código */ })
//     .catch(error => { /* código */ });
//
//5. Uso con async/await:
//   async function obtenerDatos() {
//     try {
//       const datos = await apiLaravel('/api/datos', 'GET');
//       // Procesar datos
//     } catch (error) {
//       // Procesar errores
//     }
//   }


/**
 * Función para realizar peticiones AJAX a APIs de Laravel
 * @param {string} ruta - URL o endpoint de la API a la que se realizará la petición
 * @param {string} metodo - Método HTTP (GET, POST, PUT, DELETE, etc.)
 * @param {object} datos - Datos a enviar en la petición
 * @returns {Promise} Promesa que resuelve con los datos de la respuesta o rechaza con un error
 */
function apiLaravel(ruta, metodo = 'POST', datos = {}) {
    // Mostrar indicador de carga
    mostrarIndicadorCarga();

    // Retorna una promesa para manejar la asincronía
    return new Promise((resolve, reject) => {
        // Configuración de la petición fetch
        const opciones = {
            method: metodo.toUpperCase(), // Convierte el método a mayúsculas (estándar HTTP)
            headers: {
                'Content-Type': 'application/json', // Indica que enviamos JSON
                'Accept': 'application/json', // Indica que esperamos recibir JSON
                // Usa el token CSRF desde la configuración global
                'X-CSRF-TOKEN': window.Laravel ? window.Laravel.csrfToken : document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        };

        // Añade el cuerpo de la petición si el método no es GET
        if (metodo.toUpperCase() !== 'GET') {
            opciones.body = JSON.stringify(datos); // Convierte el objeto a string JSON
        }

        // Determinar la base de Laravel en tiempo de ejecución
        const getScriptBase = () => {
            try {
                const scripts = document.getElementsByTagName('script');
                for (const s of scripts) {
                    if (s.src && s.src.includes('/js/common.js')) {
                        const u = new URL(s.src, window.location.origin);
                        const basePath = u.pathname.replace(/\/js\/common\.js.*$/, '');
                        return u.origin + basePath;
                    }
                }
            } catch (e) {}
            return null;
        };

        const base = (window.Laravel && window.Laravel.baseUrl) || getScriptBase() || window.location.origin;
        // Si la ruta ya es absoluta (http/https), la usamos tal como está
        let fullUrl;
        if (ruta.startsWith('http')) {
            fullUrl = ruta;
        } else {
            // Construir absoluta a partir de la base detectada
            const cleanBase = base.replace(/\/$/, '');
            const cleanRuta = ruta.replace(/^\//, '');
            fullUrl = `${cleanBase}/${cleanRuta}`;
        }

        // Para peticiones GET, los parámetros van en la URL como query string
        let url;
        if (metodo.toUpperCase() === 'GET') {
            const queryString = new URLSearchParams(datos).toString();
            // Si no hay parámetros, no agregamos '?' o '&'
            if (queryString) {
                const separator = fullUrl.includes('?') ? '&' : '?';
                url = `${fullUrl}${separator}${queryString}`; // Añade parámetros a la URL respetando si ya hay '?'
            } else {
                url = fullUrl;
            }
        } else {
            url = fullUrl; // Para otros métodos, usa la ruta completa
        }

        // Realiza la petición fetch
        fetch(url, opciones)
            .then(async response => {
                // Verifica que la respuesta sea JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Respuesta no es JSON válida');
                }

                // Parsea la respuesta a JSON
                const json = await response.json();

                // Ocultar indicador de carga antes de resolver
                ocultarIndicadorCarga();
                if ('success' in json && json.success === false) {
                    return reject(json.message || 'Error en la aplicación');
                }

                if (json.exception) {

                    const mensajeError = json.message;
                    reject(mensajeError);
                }

                // Si la respuesta contiene un mensaje de error, rechaza la promesa
                // Comprueba si hay mensajes de error en la respuesta
                if (json.errorMensaje || json.error || json.errors) {

                    // Prioriza errorMensaje, luego error, luego message, y finalmente un texto por defecto
                    const mensajeError = json.errorMensaje || json.error || json.message || 'Error en la aplicación';
                    reject(mensajeError);
                } else if (json.exception) {
                    reject(json.message || 'Error en la aplicación');
                } else {
                    // Si no hay error, resuelve la promesa con los datos
                    resolve(json);
                }
            })
            .catch(error => {
                // Ocultar indicador de carga antes de rechazar
                ocultarIndicadorCarga();

                // Captura cualquier error durante la petición
                reject(error.message || 'Error en la petición');
            });
    });
}
// Función para redimensionar imágenes con Promise
function resizeImage(base64Str, targetElement, maxWidth, maxHeight) {
    return new Promise((resolve, reject) => {
        const img = new Image();

        img.onload = function () {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                const sourceWidth = img.width;
                const sourceHeight = img.height;

                // Calcular nuevas dimensiones manteniendo aspect ratio
                let newWidth, newHeight;

                if (sourceWidth >= sourceHeight) {
                    // Imagen horizontal o cuadrada
                    const ratio = sourceHeight / sourceWidth;
                    newWidth = Math.min(maxWidth, sourceWidth);
                    newHeight = ratio * newWidth;
                } else {
                    // Imagen vertical
                    const ratio = sourceWidth / sourceHeight;
                    newHeight = Math.min(maxHeight, sourceHeight);
                    newWidth = ratio * newHeight;
                }

                canvas.width = newWidth;
                canvas.height = newHeight;

                // Dibujar imagen redimensionada
                ctx.drawImage(img, 0, 0, newWidth, newHeight);

                const resizedBase64 = canvas.toDataURL('image/jpeg', 0.8);

                // Actualizar elemento target si existe
                if (targetElement && targetElement.length > 0) {
                    targetElement.attr("src", resizedBase64);
                }

                resolve(resizedBase64);
            } catch (error) {
                reject(error);
            }
        };

        img.onerror = function () {
            reject(new Error('Error al cargar la imagen para redimensionar'));
        };

        img.src = base64Str;
    });
}

// Función para convertir archivo a base64 con Promise
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        if (file.convertToBase64) {
            // Usar método personalizado si existe
            file.convertToBase64(resolve);
        } else {
            // Fallback usando FileReader
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        }
    });
}

// Función principal mejorada con async/await y callback
async function cargarImg(inputElement, wMin = null, hMin = null, callback = null) {
    try {
        const file = inputElement.files[0];

        // Guardar en una variable el atributo data-id_img si existe
        const dataIdImg = (inputElement && inputElement.getAttribute) ? inputElement.getAttribute('data-id_img') : null;
        if (!file) {
            console.warn('No se seleccionó ningún archivo');
            if (callback) callback(new Error('No file selected'), null);
            return;
        }

        // Validar que es una imagen
        if (!file.type.startsWith('image/')) {
            const error = new Error('El archivo seleccionado no es una imagen válida');
            console.error(error.message);
            if (callback) callback(error, null);
            return;
        }

        console.log('Procesando archivo:', file.name);

        // Convertir archivo a base64
        const base64 = await fileToBase64(file);

        const $input = $(inputElement);
        const previewId = $input.attr("data-prev");

        if (!previewId) {
            console.warn('No se encontró el atributo data-prev en el input');
            if (callback) callback(new Error('Missing data-prev attribute'), null);
            return;
        }

        const results = {
            original: base64,
            preview: null,
            big: null
        };

        if (wMin === null && hMin === null) {
            // Solo mostrar imagen original
            const $previewElement = $("#" + previewId);
            if ($previewElement.length > 0) {
                $previewElement.attr("src", base64);
                results.preview = base64;
            }
        } else {
            // Crear versiones redimensionadas
            const resizePromises = [];

            // Versión grande (1000x1000)
            const $bigElement = $("#" + previewId + "_big");
            if ($bigElement.length > 0) {
                resizePromises.push(
                    resizeImage(base64, $bigElement, wMin, hMin)
                        .then(resized => { results.big = resized; })
                );
            }

            // Versión preview (250x250)
            const $previewElement = $("#" + previewId + "_small");
            if ($previewElement.length > 0) {
                resizePromises.push(
                    resizeImage(base64, $previewElement, 250, 250)
                        .then(resized => { results.preview = resized; })
                );
            }
            const $defaulElement = $("#" + previewId);
            if ($defaulElement.length > 0) {
                resizePromises.push(
                    resizeImage(base64, $defaulElement, wMin, hMin)
                        .then(resized => { results.big = resized; })
                );
            }


            // Esperar a que todas las redimensiones terminen
            await Promise.all(resizePromises);
        }

        console.log('Imagen procesada exitosamente');

        // Ejecutar callback con resultados exitosos
        if (callback) {

            callback(null, results, dataIdImg);
        }

    } catch (error) {
        console.error('Error al procesar la imagen:', error);
        if (callback) {
            callback(error, null);
        }
    }
}

// Ejemplo de uso con callback:
/*
cargarImg(inputElement, 250, 250, function(error, results) {
    if (error) {
        console.error('Error:', error.message);
        // Manejar error
    } else {
        console.log('Imágenes procesadas:', results);
        // results.original - imagen original en base64
        // results.preview - imagen redimensionada para preview
        // results.big - imagen redimensionada grande
        
        // Hacer algo después de que todo esté listo
        alert('¡Imagen cargada exitosamente!');
    }
});
*/