/**
 * Función para realizar peticiones AJAX a Laravel con manejo automático de CSRF
 * Utilizada en todo el sistema para consistencia
 */
function apiLaravel(url, method = 'GET', data = null, options = {}) {
    console.log('=== API LARAVEL VERSION DEBUG ===');
    console.log('URL:', url);
    console.log('Method:', method);
    return new Promise((resolve, reject) => {
        const config = {
            method: method.toUpperCase(),
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            ...options
        };

        if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(config.method)) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                config.headers['X-CSRF-TOKEN'] = token;
            }
        }

        if (data) {
            if (config.method === 'GET') {
                const params = new URLSearchParams(data);
                url += (url.includes('?') ? '&' : '?') + params.toString();
            } else {
                config.body = JSON.stringify(data);
            }
        }

        fetch(url, config)
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                return response.text().then(text => {
                    console.log('Response text:', text);
                    let data = {};
                    try {
                        data = JSON.parse(text);
                        console.log('Parsed data:', data);
                    } catch (e) {
                        data = { message: text };
                    }
                    
                    // Si es error (no 2xx), pero requiere confirmación, resolver en lugar de reject
                    if (!response.ok && data.requires_confirmation === true) {
                        console.log('>>> RESOLVIENDO CON requires_confirmation <<<');
                        resolve(data);
                        return;
                    }
                    
                    // Para otros errores, rechazar
                    if (!response.ok) {
                        console.log('>>> RECHAZANDO ERROR <<<');
                        console.log('data:', data);
                        console.log('data.requires_confirmation:', data.requires_confirmation);
                        const error = new Error(data.message || `Error ${response.status}`);
                        error.responseData = data;
                        reject(error);
                        return;
                    }
                    
                    // Respuesta exitosa
                    resolve(data);
                });
            })
            .catch(error => {
                console.error('Error en apiLaravel:', error);
                reject(error);
            });
    });
}

// Funciones auxiliares
function mostrarErroresValidacion(errors) {
    if (typeof errors === 'object') {
        const mensajes = Object.values(errors).flat();
        return mensajes.join('\n');
    }
    return errors;
}

function limpiarParametros(params) {
    const limpio = {};
    for (const [key, value] of Object.entries(params)) {
        if (value !== null && value !== undefined && value !== '') {
            limpio[key] = value;
        }
    }
    return limpio;
}

// Métodos auxiliares para compatibilidad con código existente
apiLaravel.get = function(url, callback) {
    return apiLaravel(url, 'GET', null)
        .then(callback)
        .catch(function(error) {
            console.error('Error en apiLaravel.get:', error);
            if (callback) callback({ success: false, message: error.message });
        });
};

apiLaravel.post = function(url, data, callback) {
    return apiLaravel(url, 'POST', data)
        .then(callback)
        .catch(function(error) {
            console.error('Error en apiLaravel.post:', error);
            if (callback) callback({ success: false, message: error.message });
        });
};

apiLaravel.put = function(url, data, callback) {
    return apiLaravel(url, 'PUT', data)
        .then(callback)
        .catch(function(error) {
            console.error('Error en apiLaravel.put:', error);
            if (callback) callback({ success: false, message: error.message });
        });
};

apiLaravel.delete = function(url, data, callback) {
    return apiLaravel(url, 'DELETE', data)
        .then(callback)
        .catch(function(error) {
            console.error('Error en apiLaravel.delete:', error);
            if (callback) callback({ success: false, message: error.message });
        });
};

apiLaravel.exec = function(url, method, data, callback) {
    return apiLaravel(url, method, data)
        .then(callback)
        .catch(function(error) {
            console.error('Error en apiLaravel.exec:', error);
            if (callback) callback({ success: false, message: error.message });
        });
};

// Exportar para uso global (después de agregar todos los métodos)
window.apiLaravel = apiLaravel;
window.mostrarErroresValidacion = mostrarErroresValidacion;
window.limpiarParametros = limpiarParametros;
