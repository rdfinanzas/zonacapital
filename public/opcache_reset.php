<?php
// Script para resetear el OPcache del servidor web
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "OPcache reseteado correctamente. Los cambios de PHP ahora están activos.";
    } else {
        echo "No se pudo resetear el OPcache.";
    }
} else {
    echo "OPcache no está habilitado en este servidor.";
}
