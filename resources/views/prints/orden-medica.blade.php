<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orden Médica - {{ $licencia->personal->Legajo }}</title>
    <style>
        @import url('https://fonts.cdnfonts.com/css/times-new-roman');
        body {
            font-size: 12px;
            font-family: Calibri, sans-serif;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 0px 0px 0px 0px !important;
        }
        .cabecera {
            margin: 10px 10px 10px 10px !important;
        }
        .cuerpo {
            margin: 0px 60px 0px 60px !important;
        }
        .cabecera_cd br {
            line-height: 5px !important;
        }
        .br_medio br {
            line-height: 20px !important;
        }
        .br_small br {
            line-height: 10px !important;
        }
        .dotted-line {
            border-bottom: 1px dotted black;
            display: inline-block;
        }
        .solid-line {
            border-bottom: 1px solid thin black;
            display: inline-block;
        }
        br {
            line-height: 25px;
        }
    </style>
</head>
<body>
    @php
        $personal = $licencia->personal;
        $fechaCreacion = \Carbon\Carbon::parse($licencia->FechaCreacion);
        $fechaIni = \Carbon\Carbon::parse($licencia->FechaLic);
        $fechaFin = \Carbon\Carbon::parse($licencia->FechaLicFin);
        $fechaNac = \Carbon\Carbon::parse($personal->FecNac);
        $edad = $fechaNac->age;
        
        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        
        $domicilio = $personal->Calle . ' ' . $personal->CalleNum . (trim($personal->Piso) != '' ? ' PISO: ' . $personal->Piso : '') . (trim($personal->Departamento) != '' ? ' DTO: ' . $personal->Departamento : '');
        
        $fecha_actual = date('h', time()) . ' Hs. del día ' . $fechaCreacion->format('d') . ' de ' . $meses[intval($fechaCreacion->format('n'))] . ' del ' . $fechaCreacion->format('Y');
        $fecha_simple = 'POSADAS  ' . $fechaCreacion->format('d') . ' de ' . $meses[intval($fechaCreacion->format('n'))] . ' del ' . $fechaCreacion->format('Y');
        
        $nOrden = $licencia->OrdenMedica;
        $anioLar = $licencia->AnioLar;
    @endphp

    <div class="cabecera">
        <table style="width:100%;">
            <tr>
                <td width="50%">
                    <div style="text-align:left; margin-left: 40px;"><img width="260" src="{{ public_path($logoPath) }}" ></div>
                </td>
                <td valign="top" width="50%" style="padding-right: 30px;">
                    <i>{{ $leyenda }}</i>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="cuerpo">
        <table style="width:100%; margin-left: 10px;">
            <tr>
                <td>
                    <span class="cabecera_cd" style='font-weight: bold;font-size:12px;'>LICENCIA POR ENFERMEDAD<br>
                        PROVINCIA DE MISIONES <br>
                        DIRECCION GENERAL DE <br>
                        ADMINISTRACION DE PERSONAL   <br>	
                    </span>
                </td>
                <td valign="top" style="text-align:right">	
                    <b>LEGAJO Nº: {{ $personal->Legajo }}</b>
                </td>
            </tr>
        </table>
    
        <div style="font-size:12px;font-weight: bold; margin-left: 10px;">A-ORDEN DE RECONOCIMIENTO MÉDICOS: A CONSULTORIO Nº <span style="font-size:16px;">{{ $nOrden }}/{{ $anioLar }}</span> </div>	
        <div style="text-decoration: underline;font-size:8px;font-weight: bold; margin-left: 10px;">PARA EL AGENTE:</div>	
        <table style='width:600px;text-align:center;' >
            <tr>
                <td style="border-bottom: 1px dotted black;">{{ $personal->Apellido }} {{ $personal->Nombre }}</td>
                <td style="border-bottom: 1px dotted black;">{{ $edad }}</td>
                <td style="border-bottom: 1px dotted black;">{{ $personal->DNI }}</td>
                <td style="border-bottom: 1px dotted black;">{{ $personal->categoria_nombre ?? '' }}</td>
            </tr>
            <tr style='font-size:12px;text-align:center;'>
                <td><b>APELLIDO Y NOMBRE</b></td>
                <td><b>EDAD</b></td>
                <td><b>DNI</b></td>
                <td><b>CATEGORÍA</b></td>
            </tr>	
        </table>
        <div style="text-align:justify;margin-left: 10px;"><b>Domiclio:</b> {{ $domicilio }}</div>  
        <div style="text-align:justify;font-size:15px; margin-left: 10px;">La presente Orden Médica Expedida por:	<b>"DIRECCION ZONA CAPITAL"</b> Ministerio de Salud Pública</div> 
        <div style="text-align:justify;margin-left: 10px;"> Orden Médica Entregada a:</div>  
        <br> 
        <table class="br_small" width="100%" style="margin-top:30px; margin-left: 10px">
            <tr>
                <td> 
                    <div style="text-align:center;font-size:12px;width:170px;margin-left: 10px;">
                        .............................................................. <br>
                        FIRMA<br><br>
                        .............................................................. <br>
                        Aclaración	
                    </div>
                </td>
                <td>	
                    <div style="margin-left:50px;"><b>{{ $fecha_actual }}</b></div><br><br><br>
                    <div style="text-align:center;font-size:12px;line-height:5px;">
                        ..............................................................<br>
                        Firma y sello del emisor<br>
                        Responsable de la Orden Médica<br>
                    </div>
                </td>
            </tr>
        </table><br>
        <table style='width:600px;margin-left: 10px;' >
            <tr>
                <td style="text-decoration: underline; "><b>RECEPCIÓN</b></td>
                <td><b>Hora</b></td>
                <td><b>Fecha</b></td>
            </tr>
        </table>

        <div class="br_medio" style="font-size:12px;margin-left: 10px;">
            Se ha dado cumplimiento a la Orden Con los siguientes Resultados:<br>
            B – Reconocimiento a: ...............................................................................................<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1- Se ubica el domicilio...............................................................................................<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2- Se atendió la llamada del médico...............................................................................................<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3- Se encontraba el agente en su domicilio ...............................................................................................<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4- En caso negativo de los puntos 1 a 2 –Hora ..............Fecha......./........./......... <br>
            El causante se encuentra comprendido en los términos del Decreto Nº 683/89.<br>
            Art.........................por lo que...................................le corresponde licencia y reposo en domicilio. 
            <br>
            <table style='width:600px;text-align:center;margin-left: 10px;' cellspacing="10">
                <tr>
                    <td style="border-bottom: 1px dotted black;"></td>
                    <td style="border-bottom: 1px dotted black;"></td>
                    <td style="border-bottom: 1px dotted black;"></td>
                    <td style="border-bottom: 1px dotted black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                </tr>
                <tr style='font-size:12px;text-align:center;'>
                    <td>Desde</td>
                    <td>Hasta</td>
                    <td>Días Hábiles</td>
                    <td>Días Corridos</td>
                </tr>	
            </table>
        </div><br>

        <div class="br_medio" style="font-size:12px;margin-left: 10px;">
    
            <div style="width:100%" class="solid-line"></div>
            
            <div style="font-size:10px;width:100%;text-align:center;font-weight: bold;margin-top:10px;margin-bottom:10px;">Talón para el Organismo de Revista del Agente</div>
            
    
            <div style="font-size:12px;font-weight: bold;text-align:center;width:100%;border-top: 1px dotted black;padding-top:5px;margin-left: 10px;"></div>
            
            <br>
            <div style="font-size:12px;font-weight: bold; margin-left: 10px;">A-ORDEN DE RECONOCIMIENTO MÉDICOS: A CONSULTORIO Nº <span style="font-size:16px;">{{ $nOrden }}/{{ $anioLar }}</span> </div>	
        <br>
            EL / LA AGENTE: <b>{{ $personal->Apellido }} {{ $personal->Nombre }}</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LEGAJO Nº: {{ $personal->Legajo }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Categoría: {{ $personal->categoria_nombre ?? '' }}	<br>
            D.N.I.Nº {{ $personal->DNI }} Que presta servicios en el centro de salud {{ $personal->servicio_nombre ?? '' }}<br>
            Se le concedió licencia por .............. día/s conforme al Art. Nº.............. Decreto Nº 683/89.<br>
            Desde el ............................................ hasta el ............................................. del año {{ $anioLar }}<br><br>
            {{ $fecha_simple }}<br><br>

            <div style="font-size:12px;font-weight: bold;text-align:center;float:right;border-top: 1px dotted black;width:250px;margin-left: 10px;"> Firma y Sello del Médico</div>
            <div style="font-style: italic; font-size: 11px;margin-top:30px; text-decoration: underline;margin-left: 10px;">Talón para el M.S.P</div>

        </div>

        <br>

        <div class="br_medio" style="font-size:12px;">

        
            <div style="font-size:12px;font-weight: bold;text-align:center;width:100%;border-top: 1px dotted black;padding-top:5px;margin-left: 10px;"></div>

            <br>
            <div style="font-size:12px;font-weight: bold; margin-left: 10px;">A-ORDEN DE RECONOCIMIENTO MÉDICOS: A CONSULTORIO Nº <span style="font-size:16px;">{{ $nOrden }}/{{ $anioLar }}</span> </div>	
        <br>
            EL / LA AGENTE: <b>{{ $personal->Apellido }} {{ $personal->Nombre }}</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LEGAJO Nº: {{ $personal->Legajo }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Categoría: {{ $personal->categoria_nombre ?? '' }}	<br>
            D.N.I.Nº {{ $personal->DNI }} Que presta servicios en el centro de salud {{ $personal->servicio_nombre ?? '' }}<br>
            Se le concedió licencia por .............. día/s conforme al Art. Nº.............. Decreto Nº 683/89.<br>
            Desde el ............................................ hasta el ............................................. del año {{ $anioLar }}<br><br>
            {{ $fecha_simple }}<br><br>

            <div style="font-size:12px;font-weight: bold;text-align:center;float:right;border-top: 1px dotted black;width:250px;margin-left: 10px;"> Firma y Sello del Médico</div>

            <div style="font-style: italic; font-size: 11px;margin-top:30px; text-decoration: underline;margin-left: 10px;">Talón para el Agente</div>

        </div>
    </div>
    
</body>
</html>
