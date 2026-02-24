<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Nº {{ $nota->numero }}/{{ $nota->anio }}</title>
    <style>
        @php
            $config = $nota->configuracion_pdf;
            $m = $config->margenes;
            $p = $config->pagina;
        @endphp

        @page {
            margin: {{ $m->superior }}cm {{ $m->derecho }}cm {{ $m->inferior }}cm {{ $m->izquierdo }}cm;
            size: {{ $p->tamano }} {{ $p->orientacion }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
        }

        /* Encabezado configurable */
        .header-configurable {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .header-configurable table {
            width: 100%;
        }

        .header-logo {
            width: 120px;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 120px;
            max-height: 80px;
        }

        .header-leyenda {
            vertical-align: middle;
            text-align: right;
            font-size: 9pt;
            font-style: italic;
            color: #555;
            line-height: 1.3;
        }

        /* Contenido de la nota */
        .contenido {
            text-align: justify;
        }

        .contenido p {
            margin-bottom: 10px;
        }

        .contenido ul, .contenido ol {
            margin-left: 2em;
            margin-bottom: 10px;
        }

        .contenido li {
            margin-bottom: 5px;
        }

        .contenido table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .contenido table th,
        .contenido table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        .contenido table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .contenido img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    @php
        $config = $nota->configuracion_pdf;
        $encabezado = $config->encabezado;
        $contenido = $nota->configuracion_completa['contenido'] ?? $nota->descripcion;
    @endphp

    <!-- Encabezado configurable (solo si hay logo o leyenda) -->
    @if($encabezado->logo_path || $encabezado->leyenda)
    <div class="header-configurable">
        <table>
            <tr>
                @if($encabezado->logo_path)
                <td class="header-logo">
                    @if(filter_var($encabezado->logo_path, FILTER_VALIDATE_URL) || str_starts_with($encabezado->logo_path, 'data:'))
                        <img src="{{ $encabezado->logo_path }}" alt="Logo">
                    @else
                        <img src="{{ public_path($encabezado->logo_path) }}" alt="Logo">
                    @endif
                </td>
                @endif
                @if($encabezado->leyenda)
                <td class="header-leyenda">
                    {!! nl2br(e($encabezado->leyenda)) !!}
                </td>
                @elseif($encabezado->logo_path)
                <td></td>
                @endif
            </tr>
        </table>
    </div>
    @endif

    <!-- Contenido de la nota -->
    <div class="contenido">
        {!! $contenido !!}
    </div>
</body>
</html>
