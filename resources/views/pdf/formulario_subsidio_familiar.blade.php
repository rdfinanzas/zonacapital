<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Declaración Jurada - Subsidio Familiar</title>
    <style>
        @page {
            size: 216mm 330mm;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .btn-imprimir {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .btn-imprimir:hover {
            background-color: #45a049;
        }
        .contenedor-formulario {
            background-color: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .titulo-centro {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .titulo-derecha {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
        }
        .seccion {
            margin-bottom: 15px;
            border: 1px solid #000;
            padding: 10px;
            background-color: white;
        }
        .seccion-titulo {
            font-weight: bold;
            font-size: 11px;
            background-color: #ddd;
            padding: 3px 8px;
            margin: -10px -10px 10px -10px;
        }
        .fila {
            display: flex;
            margin-bottom: 5px;
        }
        .campo {
            border-bottom: 1px solid #000;
            padding: 2px 5px;
            min-height: 16px;
        }
        .etiqueta {
            font-weight: bold;
            min-width: 100px;
        }
        .etiqueta-pequena {
            font-weight: bold;
            min-width: 60px;
        }
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            margin-right: 3px;
        }
        .marcado {
            background-color: #000;
        }
        .hijos-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .hijos-tabla th, .hijos-tabla td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            font-size: 8px;
        }
        .hijos-tabla th {
            background-color: #eee;
            font-weight: bold;
        }
        .hijos-tabla td {
            text-align: left;
        }
        .firma-linea {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 5px;
            text-align: center;
            font-weight: bold;
        }
        .texto-jurado {
            font-size: 9px;
            text-align: justify;
            margin: 15px 0;
            line-height: 1.4;
        }
        .bold {
            font-weight: bold;
        }

        /* Ocultar botón al imprimir */
        @media print {
            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }
            .btn-imprimir {
                display: none;
            }
            .contenedor-formulario {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: none;
            }
            .seccion {
                page-break-inside: avoid;
            }
        }
    </style>
    <script>
        function imprimirFormulario() {
            window.print();
        }
    </script>
</head>
<body>

    <button class="btn-imprimir" onclick="imprimirFormulario()">🖨️ Imprimir / Guardar PDF</button>

    <div class="contenedor-formulario">
        <!-- Título -->
        <div class="titulo-derecha">SUBSIDIO FAMILIAR</div>
        <div class="titulo-centro">DECLARACIÓN JURADA</div>

        <!-- SECCIÓN DECLARANTE -->
        <div class="seccion">
            <div class="seccion-titulo">DECLARANTE</div>

            <div class="fila">
                <div class="etiqueta">Apellido y Nombre:</div>
                <div class="campo" style="flex: 1;">
                    {{ $empleado->Apellido ?? '' }}, {{ $empleado->Nombre ?? '' }}
                </div>
                <div class="etiqueta">Legajo N°:</div>
                <div class="campo" style="width: 80px;">{{ $empleado->Legajo ?? '' }}</div>
            </div>

            <div class="fila">
                <div class="etiqueta">Repartición:</div>
                <div class="campo" style="flex: 1;">
                    {{ $empleado->gerencia->Gerencia ?? '' }} - {{ $empleado->departamento->departamento ?? '' }} - {{ $empleado->servicio->servicio ?? '' }}
                </div>
            </div>

            <div class="fila">
                <div class="etiqueta">Estado Civil:</div>
                <div class="campo" style="width: 100px;">{{ $empleado->estadoCivil->EstadoCivil ?? '' }}</div>
                <div class="etiqueta">Fecha de Casamiento:</div>
                <div class="campo" style="width: 80px;">
                    @if($hijos->count() > 0 && $hijos->first()->FechaCasamiento)
                        {{ \Carbon\Carbon::parse($hijos->first()->FechaCasamiento)->format('d/m/Y') }}
                    @else
                        {{ '--/--/----' }}
                    @endif
                </div>
            </div>

            <div class="fila">
                <div class="etiqueta">Dirección Zona de Salud "Capital":</div>
                <div class="campo" style="flex: 1;">
                    {{ $empleado->calle ?? '' }} {{ $empleado->CalleNum ?? '' }}{{ $empleado->Piso ? ', Piso ' . $empleado->Piso : '' }}{{ $empleado->Departamento ? ', Dto ' . $empleado->Departamento : '' }}
                </div>
            </div>

            <div class="fila">
                <div class="etiqueta">D.N.I. N°:</div>
                <div class="campo" style="width: 100px;">{{ $empleado->DNI ?? '' }}</div>
            </div>
        </div>

        <!-- SECCIÓN PADRE/MADRE DEL MENOR -->
        @if($hijos->count() > 0 && $hijos->first()->OtroPadre_ApellidoNombre)
        <div class="seccion">
            <div class="seccion-titulo">PADRE/MADRE DEL MENOR</div>

            <div class="fila">
                <div class="etiqueta">Apellido y Nombre:</div>
                <div class="campo" style="flex: 1;">{{ $hijos->first()->OtroPadre_ApellidoNombre ?? '' }}</div>
                <div class="etiqueta">Trabaja:</div>
                <div class="campo" style="width: 40px;">
                    @if($hijos->first()->OtroPadre_Trabaja) SI @else NO @endif
                </div>
                <div class="etiqueta">Empleador:</div>
                <div class="campo" style="width: 150px;">{{ $hijos->first()->OtroPadre_Empleador ?? '' }}</div>
            </div>

            <div class="fila">
                <div class="etiqueta">Asig. Familiares:</div>
                <div class="campo" style="width: 40px;">
                    @if($hijos->first()->OtroPadre_AsigFamiliares) SI @else NO @endif
                </div>
                <div class="etiqueta">Doc. Identidad:</div>
                <div class="campo" style="width: 100px;">{{ $hijos->first()->OtroPadre_DNI ?? '' }}</div>
                <div class="etiqueta">Domicilio:</div>
                <div class="campo" style="flex: 1;">{{ $hijos->first()->OtroPadre_Domicilio ?? '' }}</div>
            </div>

            <div class="fila">
                <div class="etiqueta">Convive:</div>
                <div class="campo" style="width: 40px;">
                    @if($hijos->first()->OtroPadre_Convive) SI @else NO @endif
                </div>
            </div>
        </div>
        @endif

        <!-- SECCIÓN HIJOS -->
        <div class="seccion">
            <div class="seccion-titulo">HIJOS</div>

            <table class="hijos-tabla">
                <thead>
                    <tr>
                        <th style="width: 25px;">#</th>
                        <th style="width: 120px;">Apellido y Nombre</th>
                        <th style="width: 60px;">D.N.I.</th>
                        <th style="width: 30px;">Edad</th>
                        <th style="width: 40px;">Impedido Trabaja</th>
                        <th style="width: 40px;">Renumeración Empleador</th>
                        <th style="width: 40px;">Mensual</th>
                        <th style="width: 80px;">Nivel Educativo</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 1; $i <= 7; $i++)
                        @php $hijo = $hijos->get($i - 1) @endphp
                        <tr style="{{ $hijo ? '' : 'height: 25px;' }}">
                            <td>{{ $i }}</td>
                            <td>{{ $hijo->Apellido ?? '' }} {{ $hijo->Nombre ?? '' }}</td>
                            <td>{{ $hijo->DNI ?? '' }}</td>
                            <td>{{ $hijo ? $hijo->Edad : '' }}</td>
                            <td style="text-align: center;">
                                {{ $hijo && $hijo->ImpedidoTrabaja ? 'SI' : 'NO' }}
                            </td>
                            <td style="text-align: center;">
                                {{ $hijo && $hijo->RemuneracionEmpleador ? 'SI' : 'NO' }}
                            </td>
                            <td style="text-align: center;">
                                {{ $hijo && $hijo->IngresosMensuales ? 'SI' : 'NO' }}
                            </td>
                            <td>{{ $hijo->NivelEducativo ?? '' }} {{ $hijo->GradoAnio ? ' - ' . $hijo->GradoAnio : '' }}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- SECCIÓN OTROS EMPLEOS -->
        @if($hijos->count() > 0 && ($hijos->first()->OtroEmpleador || $hijos->first()->PercibeSalario))
        <div class="seccion">
            <div class="seccion-titulo">OTROS EMPLEOS</div>

            <div class="fila">
                <div class="etiqueta">Empleador:</div>
                <div class="campo" style="flex: 1;">{{ $hijos->first()->OtroEmpleador ?? '' }}</div>
                <div class="etiqueta">Percibe Salario:</div>
                <div class="campo" style="width: 40px;">
                    @if($hijos->first()->PercibeSalario) SI @else NO @endif
                </div>
            </div>

            <div class="fila">
                <div class="etiqueta">M.S.P.:</div>
                <div class="campo" style="width: 100px;">
                    @if($hijos->first()->MontoSalario)
                        $ {{ number_format($hijos->first()->MontoSalario, 2, ',', '.') }}
                    @endif
                </div>
                <div class="etiqueta">Observaciones:</div>
                <div class="campo" style="flex: 1;">{{ $hijos->first()->ObservacionesOtrosEmpleos ?? '' }}</div>
            </div>
        </div>
        @endif

        <!-- DECLARACIÓN JURADA -->
        <div class="seccion">
            <div class="texto-jurado">
                <strong>DECLARACIÓN JURADA:</strong><br/>
                Afirmo bajo juramento que los datos asentados en esta declaración son exactos y completos, que los he confeccionado sin omitir ni falsear informaciones, en conocimiento de las normas que rigen la materia, quedando obligado de producirse variantes de las situaciones y datos denunciados a comunicar dentro de los (10) días hábiles acompañando la documentación correspondiente y efectuado la actualización de la declaración jurada.
            </div>

            <div class="fila" style="margin-top: 30px;">
                <div style="flex: 1; text-align: center;">
                    <div style="margin-bottom: 40px;">Lugar: <span style="border-bottom: 1px solid #000; padding: 0 20px;">POSADAS</span></div>
                    <div>Fecha: <span style="border-bottom: 1px solid #000; padding: 0 20px;">{{ $fecha }}</span></div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                <div style="width: 45%;">
                    <div class="firma-linea">FIRMA DEL DECLARANTE</div>
                </div>
                <div style="width: 45%;">
                    <div style="text-align: center; margin-bottom: 10px; font-size: 8px;">CERTIFICADO QUE LA FIRMA DEL DECLARANTE ES AUTÉNTICA</div>
                    <div class="firma-linea">FIRMA DEL JEFE DE LA REPARTICIÓN</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
