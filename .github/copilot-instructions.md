# Laravel Gateway de Autenticación por Sesión

Este proyecto es un **gateway de autenticación en Laravel** que permite validar credenciales de usuarios provenientes de un sistema externo. El backend trabaja con una base de datos que no cifra las contraseñas y permite redirigir a distintas rutas según el login.

---

## 🎯 Objetivo

- Recibir un **POST** con los siguientes parámetros:
  - `usuario`: nombre de usuario (string)
  - `password`: contraseña en texto plano (string)
  - `vista`: nombre de una ruta Laravel definida (`dashboard`, `perfil`, etc.)

- Validar el usuario contra la tabla `usuario` en base de datos.
- Si es válido:
  - Guardar el ID del usuario en sesión.
  - Redirigir a la **ruta Laravel** pasada como parámetro.
- Las rutas están protegidas por un middleware que verifica la sesión activa.

La ruta del gateway es `/gateway/login`. 

Estructura de la tabla de permisos:
CREATE TABLE `permisos_x_usuarios` (
  `UsuarioId` int(10) UNSIGNED NOT NULL,
  `ModuloId` tinyint(3) UNSIGNED NOT NULL,
  `C` tinyint(1) NOT NULL COMMENT 'create',
  `R` tinyint(1) NOT NULL COMMENT 'Read',
  `U` tinyint(1) NOT NULL COMMENT 'update',
  `D` tinyint(1) NOT NULL COMMENT 'delete'
)

Estructura de la tabla modulos:
CREATE TABLE `modulos` (
  `IdModulo` tinyint(3) UNSIGNED NOT NULL,
  `Label` varchar(256) NOT NULL,
  `Url` varchar(100) NOT NULL,
  `Icono` varchar(256) NOT NULL,
  `ModuloPadreId` tinyint(4) NOT NULL,
  `Orden` tinyint(3) UNSIGNED NOT NULL,
  `Padre` tinyint(1) NOT NULL
)

siempre a la vista blade hay que pasarle los permisos, con la funcion obtenerPermisos
ejemplo:
$permisos = PermisoHelper::obtenerPermisos($usuarioId, request()->path());


    return view('modelo-ejemplo', [
        'usuario' => $usuario,
        'permisos' => $permisos
    ]);

estructura de permisos

  [
                'crear' => false,
                'leer' => false,
                'editar' => false,
                'eliminar' => false,
            ];

            
/**
INSTRUCCIONES PARA EL USO DE PETICIONES AJAX

Todas las peticiones AJAX en esta aplicación deben realizarse exclusivamente mediante la función apiLaravel.
No utilices fetch, axios, jQuery.ajax ni ningún otro método directo, para asegurar la consistencia en el manejo de solicitudes.

Importante: Todas las lista paginadas se hacen con ajax

Ejemplos de uso:

1. Petición GET:
   apiLaravel('/api/usuarios', 'GET', { filtro: 'activos' })
     .then(respuesta => {
         // Procesar respuesta exitosa
         console.log(respuesta);
     })
     .catch(error => {
         // Procesar error
         console.error('Error:', error);
     });

2. Petición POST:
   apiLaravel('/api/usuarios', 'POST', { 
     nombre: 'Juan', 
     email: 'juan@ejemplo.com'
   })
     .then(respuesta => {
         // Procesar respuesta exitosa
     })
     .catch(error => {
         // Procesar error
     });

3. Petición PUT:
   apiLaravel('/api/usuarios/1', 'PUT', { nombre: 'Juan Modificado' })
     .then(respuesta => { /* código */ })
     .catch(error => { /* código */ });

4. Petición DELETE:
   apiLaravel('/api/usuarios/1', 'DELETE')
     .then(respuesta => { /* código */ })
     .catch(error => { /* código */ });

5. Uso con async/await:
   async function obtenerDatos() {
     try {
       const datos = await apiLaravel('/api/datos', 'GET');
       // Procesar datos
     } catch (error) {
       // Procesar errores
     }
   }

IMPORTANTE:
- Para rutas protegidas, asegúrate de que el token CSRF esté habilitado en la función.
- La función siempre devuelve una Promesa; utiliza then/catch o async/await.
- Los errores se capturan automáticamente y se devuelven mediante el reject de la promesa.
- La respuesta siempre estará en formato JSON.