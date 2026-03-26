<?php

namespace App\Helpers;

class FechaHelper
{
    /**
     * Obtiene un array con los meses del año en español
     *
     * @return array
     */
    public static function getMeses()
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
    }

    /**
     * Obtiene el nombre del mes en español a partir de su número
     *
     * @param int $numeroMes
     * @return string
     */
    public static function getNombreMes($numeroMes)
    {
        $meses = self::getMeses();
        return $meses[$numeroMes] ?? '';
    }
}
