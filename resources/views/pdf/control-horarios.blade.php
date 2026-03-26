<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Control de Horarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .ausente {
            background-color: #ff0101;
            color: white;
            font-weight: bold;
        }
        .falta-dato {
            background-color: #ffc18a;
        }
        .licencia {
            background-color: #b4ffff;
        }
        .header {
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .header p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Control de Horarios</h2>
        <p>Período: {{ $desde }} al {{ $hasta }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Personal</th>
                <th>Fecha</th>
                <th>Lic/Fer</th>
                <th>Prog. Turn. 1</th>
                <th>Guardias</th>
                <th>Marcas</th>
                <th>Horas</th>
                <th>Prog. Turn. 2</th>
                <th>Marcas</th>
                <th>Horas</th>
                <th>Responsable</th>
                <th>Situación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $key => $row)
                @php
                    $situacion = '';
                    $clase = '';
                    if (isset($row[9])) {
                        if ($row[9] == 2) {
                            $situacion = 'Falta Dato';
                            $clase = 'falta-dato';
                        } elseif ($row[9] == 1) {
                            $situacion = 'Ausente';
                            $clase = 'ausente';
                        } elseif (!empty($row[1])) {
                            $situacion = 'Día Justificado';
                            $clase = 'licencia';
                        }
                    }
                    $fecha = '';
                    if (isset($row[12]) && $row[12]) {
                        try {
                            $fecha = \Carbon\Carbon::createFromFormat('Y-m-d', $row[12])->format('d/m/Y');
                        } catch (Exception $e) {
                            $fecha = $row[12];
                        }
                    }
                @endphp
                <tr class="{{ $clase }}">
                    <td>{{ $row[0] ?? '' }}</td>
                    <td>{{ $fecha }}</td>
                    <td>{{ $row[1] ?? '' }}</td>
                    <td>{{ $row[2] ?? '' }}</td>
                    <td>{{ $row[3] ?? '' }}</td>
                    <td>{{ $row[4] ?? '' }}</td>
                    <td>{{ $row[5] ?? '' }}</td>
                    <td>{{ $row[6] ?? '' }}</td>
                    <td>{{ $row[7] ?? '' }}</td>
                    <td>{{ $row[8] ?? '' }}</td>
                    <td>{{ $row[11] ?? '' }}</td>
                    <td>{{ $situacion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
