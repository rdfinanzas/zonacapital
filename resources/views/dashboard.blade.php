<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">


    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>

<body class="antialiased">
    <h1> Hola {{ $usuario->Nombre }}</h1>
    <p>Este es tu perfil.</p>
    <h2>Permisos del módulo</h2>
    <ul>
        <li>Crear: {{ $permisos['crear'] }}</li>
        <li>Leer: {{ $permisos['leer'] }}</li>
        <li>Editar: {{ $permisos['editar'] }}</li>
        <li>Eliminar: {{ $permisos['eliminar'] }}</li>
    </ul>
</body>

</html>
