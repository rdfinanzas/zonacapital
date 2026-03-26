<?php
// Verificar estado de OPcache
echo "<h2>Estado de OPcache</h2>";
echo "<pre>";

if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    echo "OPcache está habilitado: SÍ\n";
    echo "Memoria usada: " . ($status['memory_usage']['used_memory'] / 1024 / 1024) . " MB\n";
    echo "Memoria total: " . ($status['memory_usage']['free_memory'] / 1024 / 1024 + $status['memory_usage']['used_memory'] / 1024 / 1024) . " MB\n";
    echo "Scripts cacheados: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "Hits: " . $status['opcache_statistics']['hits'] . "\n";
    echo "Misses: " . $status['opcache_statistics']['misses'] . "\n";

    echo "\n--- Últimos scripts cacheados ---\n";
    $scripts = opcache_get_status()['scripts'];
    $contador = 0;
    foreach ($scripts as $script => $info) {
        if (strpos($script, 'ControlHorariosController') !== false) {
            echo "\n$script\n";
            echo "  Último uso: " . date('Y-m-d H:i:s', $info['last_used']) . "\n";
            echo "  Última modificación del archivo: " . date('Y-m-d H:i:s', $info['last_modified']) . "\n";
            echo "  Tamaño: " . $info['memory_consumption'] . " bytes\n";
            $contador++;
        }
    }
    if ($contador === 0) {
        echo "\nControlHorariosController.php NO está en caché\n";
    }
} else {
    echo "OPcache está habilitado: NO\n";
}

echo "\n</pre>";

if (function_exists('opcache_reset')) {
    echo "<form method='post'>";
    echo "<button type='submit' name='reset_opcache' class='btn btn-warning'>Resetear OPcache</button>";
    echo "</form>";
}

if (isset($_POST['reset_opcache'])) {
    if (opcache_reset()) {
        echo "<div class='alert alert-success mt-3'>OPcache reseteado correctamente. Los cambios ahora están activos.</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>No se pudo resetear OPcache.</div>";
    }
}

// Verificar si el archivo PHP existe y su fecha de modificación
echo "<hr>";
echo "<h3>Archivo ControlHorariosController.php</h3>";
echo "<pre>";
$archivo = __DIR__ . '/../app/Http/Controllers/ControlHorariosController.php';
if (file_exists($archivo)) {
    echo "Existe: SÍ\n";
    echo "Fecha modificación: " . date('Y-m-d H:i:s', filemtime($archivo)) . "\n";
    echo "Tamaño: " . filesize($archivo) . " bytes\n";
} else {
    echo "Existe: NO\n";
}
echo "</pre>";
