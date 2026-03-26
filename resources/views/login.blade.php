<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Zona Capital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Iniciar sesión</h5>
                    <form method="POST" action="{{ route('gateway.login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Clave</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <input type="hidden" name="vista" value="dashboard">
                        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                    </form>
                    @if(session('error'))
                        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col text-center text-muted">
            <small>Si no tenés usuario, solicitá acceso al administrador.</small>
        </div>
    </div>
}</div>
</body>
</html>