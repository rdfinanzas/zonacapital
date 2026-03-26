/*
 * Croppie.js Placeholder
 * Para utilizar este módulo, debe descargar e incluir la librería Croppie.js
 * Descargar desde: https://foliotek.github.io/Croppie/
 * 
 * Incluir en el head del layout:
 * <link rel="stylesheet" href="{{ asset('css/croppie.css') }}" />
 * <script src="{{ asset('js/croppie.min.js') }}"></script>
 */

console.warn('Croppie.js no está cargado. Por favor, descargue e incluya la librería desde https://foliotek.github.io/Croppie/');

// Función mock para evitar errores
if (typeof jQuery !== 'undefined') {
    jQuery.fn.croppie = function () {
        console.warn('Función croppie() llamada pero la librería no está disponible');
        return this;
    };
}
