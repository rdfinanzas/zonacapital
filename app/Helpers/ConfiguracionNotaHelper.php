<?php

namespace App\Helpers;

/**
 * Helper para manejar la configuración de notas y plantillas
 * Centraliza la lógica de codificación/decodificación de JSON
 * y preparación de datos para PDF y vista previa
 */
class ConfiguracionNotaHelper
{
    /**
     * Valores por defecto para una nueva nota
     */
    public static function defaults(): array
    {
        return [
            'encabezado' => [
                'logo_path' => null,
                'leyenda' => null,
            ],
            'contenido' => '',
            'margenes' => [
                'superior' => 2.0,
                'inferior' => 2.0,
                'izquierdo' => 2.5,
                'derecho' => 2.5,
            ],
            'pagina' => [
                'tamano' => 'legal',
                'orientacion' => 'portrait',
            ],
        ];
    }

    /**
     * Tamaños de página disponibles
     */
    public static function tamanosPagina(): array
    {
        return [
            'legal' => 'Legal (21.6 x 35.6 cm)',
            'a4' => 'A4 (21 x 29.7 cm)',
            'letter' => 'Carta (21.6 x 27.9 cm)',
            'oficio' => 'Oficio (21.6 x 33 cm)',
        ];
    }

    /**
     * Orientaciones disponibles
     */
    public static function orientaciones(): array
    {
        return [
            'portrait' => 'Vertical',
            'landscape' => 'Horizontal',
        ];
    }

    /**
     * Decodificar JSON de configuración
     * Retorna array con valores completos (usa defaults para campos faltantes)
     */
    public static function decodificar(?string $json): array
    {
        if (empty($json)) {
            return self::defaults();
        }

        $config = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return self::defaults();
        }

        // Combinar con defaults para asegurar que todos los campos existan
        return array_replace_recursive(self::defaults(), $config);
    }

    /**
     * Codificar array a JSON para guardar
     */
    public static function codificar(array $config): string
    {
        return json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Preparar configuración para la vista de impresión PDF
     * Retorna objeto con CSS y datos listos para DomPDF
     */
    public static function prepararParaPdf(array $config): object
    {
        $m = $config['margenes'];
        $p = $config['pagina'];

        return (object) [
            'encabezado' => (object) $config['encabezado'],
            'contenido' => $config['contenido'],
            'margenes' => (object) $m,
            'pagina' => (object) $p,
            'css_margenes' => "{$m['superior']}cm {$m['derecho']}cm {$m['inferior']}cm {$m['izquierdo']}cm",
            'css_pagina' => "{$p['tamano']} {$p['orientacion']}",
        ];
    }

    /**
     * Preparar configuración para la vista previa en el navegador
     * Incluye estilos adicionales para simular el papel
     */
    public static function prepararParaPreview(array $config): object
    {
        $pdf = self::prepararParaPdf($config);

        // Agregar dimensiones aproximadas para la vista previa
        $dimensiones = self::obtenerDimensiones($config['pagina']['tamano'], $config['pagina']['orientacion']);
        $pdf->ancho_preview = $dimensiones['ancho'];
        $pdf->alto_preview = $dimensiones['alto'];

        return $pdf;
    }

    /**
     * Obtener dimensiones de página en píxeles para vista previa
     * (aproximado para pantalla, escala reducida)
     */
    public static function obtenerDimensiones(string $tamano, string $orientacion): array
    {
        $dimensiones = [
            'legal' => ['ancho' => 216, 'alto' => 356], // mm * 10 para escala
            'a4' => ['ancho' => 210, 'alto' => 297],
            'letter' => ['ancho' => 216, 'alto' => 279],
            'oficio' => ['ancho' => 216, 'alto' => 330],
        ];

        $dims = $dimensiones[$tamano] ?? $dimensiones['legal'];

        if ($orientacion === 'landscape') {
            return ['ancho' => $dims['alto'], 'alto' => $dims['ancho']];
        }

        return $dims;
    }

    /**
     * Validar configuración antes de guardar
     */
    public static function validar(array $config): array
    {
        $errores = [];

        // Validar márgenes (entre 0.5 y 5 cm)
        foreach (['superior', 'inferior', 'izquierdo', 'derecho'] as $margen) {
            $valor = $config['margenes'][$margen] ?? 2;
            if (!is_numeric($valor) || $valor < 0.5 || $valor > 5) {
                $errores[] = "Margen {$margen} debe estar entre 0.5 y 5 cm";
            }
        }

        // Validar tamaño de página
        $tamanosValidos = array_keys(self::tamanosPagina());
        if (!in_array($config['pagina']['tamano'] ?? 'legal', $tamanosValidos)) {
            $errores[] = 'Tamaño de página inválido';
        }

        // Validar orientación
        if (!in_array($config['pagina']['orientacion'] ?? 'portrait', ['portrait', 'landscape'])) {
            $errores[] = 'Orientación inválida';
        }

        return $errores;
    }

    /**
     * Crear configuración desde datos de formulario
     */
    public static function desdeRequest(array $data): array
    {
        return [
            'encabezado' => [
                'logo_path' => $data['logo_path'] ?? null,
                'leyenda' => $data['leyenda_encabezado'] ?? null,
            ],
            'contenido' => $data['contenido'] ?? '',
            'margenes' => [
                'superior' => (float) ($data['margen_superior'] ?? 2.0),
                'inferior' => (float) ($data['margen_inferior'] ?? 2.0),
                'izquierdo' => (float) ($data['margen_izquierdo'] ?? 2.5),
                'derecho' => (float) ($data['margen_derecho'] ?? 2.5),
            ],
            'pagina' => [
                'tamano' => $data['tamano_pagina'] ?? 'legal',
                'orientacion' => $data['orientacion'] ?? 'portrait',
            ],
        ];
    }
}
