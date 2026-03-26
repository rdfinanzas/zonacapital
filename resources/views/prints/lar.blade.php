<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Disposición LAR - {{ $licencia->personal->Apellido }} {{ $licencia->personal->Nombre }}</title>
    <style>
        body {
            font-size: 12pt;
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 40px 30px 40px 30px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-logo {
            width: 50%;
            vertical-align: top;
        }
        .header-logo img {
            width: 300px;
        }
        .header-leyenda {
            width: 50%;
            text-align: justify;
            font-size: 10pt;
            font-style: italic;
            vertical-align: top;
            padding-left: 20px;
        }
        .fecha {
            text-align: right;
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .disposicion-numero {
            font-size: 18px;
            font-weight: bold;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 20px;
        }
        .content {
            margin-left: 45px;
            margin-right: 45px;
            line-height: 20px;
        }
        .visto {
            text-align: justify;
            text-indent: 200px;
            font-size: 12pt;
            margin-bottom: 15px;
        }
        .titulo-seccion {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .considerando {
            text-align: justify;
            text-indent: 200px;
            font-size: 12pt;
            margin-bottom: 15px;
        }
        .directora {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .dispone {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 5px;
            margin-bottom: 20px;
        }
        .articulo {
            text-align: justify;
            font-size: 12pt;
            margin-bottom: 15px;
        }
        .articulo-titulo {
            font-weight: bold;
            text-decoration: underline;
        }
        .page_break {
            page-break-after: always;
            border: none;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    @php
        $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        
        $fechaCreacion = \Carbon\Carbon::parse($licencia->FechaCreacion);
        $fechaIni = \Carbon\Carbon::parse($licencia->FechaLic);
        
        $f_arr = [
            $fechaCreacion->format('Y'),
            $fechaCreacion->format('m'),
            $fechaCreacion->format('d')
        ];
        $f_ini_arr = [
            $fechaIni->format('Y'),
            $fechaIni->format('m'),
            $fechaIni->format('d')
        ];
        
        // La leyenda viene del controller según el año de la LAR
        // $leyenda ya está definida en el controller
        
        $cabecera = "<b>POSADAS,</b> " . $f_arr[2] . " de " . $meses[intval($f_arr[1])] . " de " . $f_arr[0];
        
        $relacion = $licencia->personal->tipoRelacion->Relacion ?? 'Planta Permanente';
        $idRelacion = $licencia->personal->idTipoRelacion ?? 0;
        
        // Verificar si es segunda fracción
        $diasTomados = \App\Models\Licencia::where('LegajoPersonal', $licencia->LegajoPersonal)
            ->where('AnioLar', $licencia->AnioLar)
            ->where('IdLicencia', '!=', $licencia->IdLicencia)
            ->whereNull('Motivo_Id')
            ->sum('DiasTotal');
        
        $esSegundaFraccion = $diasTomados > 0;
        $fraccion = $esSegundaFraccion ? "segunda fracción" : "primera fracción";
        
        // Obtener total de días del parámetro LAR
        $paramLar = \DB::table('config_lar')->where('LegajoLarP', $licencia->LegajoPersonal)->where('Anio', $licencia->AnioLar)->first();
        $totalDiasLic = $paramLar ? $paramLar->Total : 20;
        
        // Texto VISTO según tipo de personal
        $esPlantaPermanente = in_array($idRelacion, [3]);
        if ($esPlantaPermanente) {
            $cuerpo = "<b>VISTO:</b> la nota presentada por el Agente Provincial de Planta Permanente " . $licencia->personal->Apellido . " " . $licencia->personal->Nombre . " con fecha " . $f_arr[2] . " de " . $meses[intval($f_arr[1])] . " de " . $f_arr[0] . ", por lo cual solicita autorización para hacer uso de la Licencia Anual Reglamentaria correspondiente al Año " . $licencia->AnioLar . ", un total de " . $licencia->DiasTotal . " días hábiles";
        } else {
            $cuerpo = "<b>VISTO:</b> la nota presentada por el Agente Provincial de Planta Temporaria " . $licencia->personal->Apellido . " " . $licencia->personal->Nombre . " con fecha " . $f_arr[2] . " de " . $meses[intval($f_arr[1])] . " de " . $f_arr[0] . ", por lo cual solicita autorización para hacer uso de la Licencia Anual Reglamentaria correspondiente al Año " . $licencia->AnioLar . ", un total de " . $licencia->DiasTotal . " días hábiles";
        }
        
        // Agregar postergación si existe
        if ($licencia->MotPoster && $licencia->MotPoster != 0) {
            $motivosPoster = [1 => "Salud", 2 => "Servicio", 3 => "Maternidad"];
            $mot_poster = $motivosPoster[$licencia->MotPoster] ?? "";
            $disp2 = $licencia->disposicionPoster ? $licencia->disposicionPoster->NumDisp : '';
            $anioDisp2 = $licencia->disposicionPoster ? $licencia->disposicionPoster->AnioDisp : '';
            $cuerpo .= " DENEGADO por razones de " . $mot_poster . " Disposición Nº " . $disp2 . "/" . $anioDisp2 . "";
        }
        
        // Agregar fracción si no toma todos los días
        if (intval($licencia->DiasTotal) != intval($totalDiasLic)) {
            $cuerpo .= " " . $fraccion;
        }
        
        $cuerpo .= " ; y";
        
        // CONSIDERANDO según tipo de personal
        if ($esPlantaPermanente) {
            $cuerpo2 = "<b>QUE</b>, el Artículo 1º del Reglamento aprobado por Decreto 683/89 establece que el personal de Planta Permanente deberá usufructuar la Licencia Anual Reglamentaria en el periodo entre el 1º de julio del año que corresponde el beneficio, y al 30 de junio del siguiente año, vencido dicho plazo caducara el derecho de la misma.";
        } else {
            $cuerpo2 = "<b>QUE</b>, el Artículo 1º del Reglamento aprobado por Decreto 683/89 establece que el personal de Planta Temporaria deberá usufructuar la Licencia Anual Reglamentaria en el periodo entre el 1º de Octubre del año que corresponde el beneficio, y al 31 de Diciembre del año, debiendo gozar esta licencia indefectiblemente dentro de este término.";
        }
        
        $cuerpo3 = "<b>QUE</b>, en la misma la Señora Directora de Zona Capital le Otorga el VISTO BUENO a lo solicitado por el Agente en cuestión, No existiendo objeciones que formular al respecto cabe el dictado del instrumento legal pertinente.-";
        
        // Artículos con formato exacto
        $disp1 = $licencia->disposicion ? $licencia->disposicion->NumDisp : '';
        
        $art1 = "<div><span class='articulo-titulo'>ARTÍCULO 1°.</span>-AUTORIZASE, la &nbsp;&nbsp;&nbsp;Licencia&nbsp;&nbsp;&nbsp; Anual &nbsp;&nbsp;&nbsp;Reglamentaria &nbsp;&nbsp;&nbsp;correspondiente</div><div style='text-indent: 110px;'>al Año " . $licencia->AnioLar . " al agente Provincial " . $licencia->personal->Apellido . " " . $licencia->personal->Nombre . " - D.N.I Nº " . $licencia->personal->DNI . " – Legajo Nº " . $licencia->LegajoPersonal . " – a partir del " . $f_ini_arr[2] . " de " . $meses[intval($f_ini_arr[1])] . " de " . $f_ini_arr[0] . ", haciendo uso un total de " . $licencia->DiasTotal . " días hábiles; </div>";
        
        $art2 = "<div><span class='articulo-titulo'>ARTÍCULO 2°.</span>-DÉJESE; &nbsp; &nbsp; Constancia &nbsp;&nbsp;de lo &nbsp;dispuesto en el &nbsp;&nbsp;&nbsp;Legajo &nbsp; &nbsp;  Personal  &nbsp; &nbsp; del</div><div style='text-indent: 110px;'>Agente en cuestión  previa notificación de la  presente  Disposición.-</div>";
        
        $art3 = "<div><span class='articulo-titulo'>ARTÍCULO 3°.</span>-REGÍSTRESE; &nbsp;&nbsp;Comuníquese.&nbsp;&nbsp; &nbsp;Notifíquese.&nbsp;&nbsp;&nbsp;&nbsp;Tomen &nbsp;&nbsp;Conocimiento </div><div style='text-indent: 110px;'>Dirección de Personal. Dirección General de Coordinación del Sector Público. Cumplido ARCHIVESE el presente disposición en la Unidad Sectorial Personal correspondiente.-</div>";
    @endphp

    <!-- PRIMERA HOJA -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path($logoPath) }}" alt="Logo">
            </td>
            <td class="header-leyenda">
                <i>{{ $leyenda }}</i>
            </td>
        </tr>
    </table>

    <div class="content">
        <div class="fecha">
            {!! $cabecera !!}
        </div>

        <div class="disposicion-numero">
            DISPOSICIÓN N° {{ $disp1 }}- 
        </div>

        <div class="visto">
            {!! $cuerpo !!}
        </div>

        <div class="titulo-seccion">CONSIDERANDO:</div>

        <div class="considerando">
            {!! $cuerpo2 !!}
        </div>

        <div class="considerando">
            {!! $cuerpo3 !!}
        </div>

        <div class="titulo-seccion">POR ELLO:</div>

        <div class="directora">
            LA SEÑORA DIRECTORA ZONA DE SALUD "CAPITAL"
        </div>

        <div class="dispone">
            DISPONE:
        </div>

        <div class="articulo">
            {!! $art1 !!}
        </div>

        <div class="articulo">
            {!! $art2 !!}
        </div>

        <div class="articulo">
            {!! $art3 !!}
        </div>
    </div>

    <!-- SALTO DE PÁGINA -->
    <div class="page_break"></div>

    <!-- SEGUNDA HOJA (DUPLICADO) -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path($logoPath) }}" alt="Logo">
            </td>
            <td class="header-leyenda">
                <i>{{ $leyenda }}</i>
            </td>
        </tr>
    </table>

    <div class="content">
        <div class="fecha">
            {!! $cabecera !!}
        </div>

        <div class="disposicion-numero">
            DISPOSICIÓN N° {{ $disp1 }}- 
        </div>

        <div class="visto">
            {!! $cuerpo !!}
        </div>

        <div class="titulo-seccion">CONSIDERANDO:</div>

        <div class="considerando">
            {!! $cuerpo2 !!}
        </div>

        <div class="considerando">
            {!! $cuerpo3 !!}
        </div>

        <div class="titulo-seccion">POR ELLO:</div>

        <div class="directora">
            LA SEÑORA DIRECTORA ZONA DE SALUD "CAPITAL"
        </div>

        <div class="dispone">
            DISPONE:
        </div>

        <div class="articulo">
            {!! $art1 !!}
        </div>

        <div class="articulo">
            {!! $art2 !!}
        </div>

        <div class="articulo">
            {!! $art3 !!}
        </div>
    </div>
</body>
</html>
