<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar nueva contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Configurar nueva contraseña</h5>
                    <p class="text-muted">Usuario: <strong>{{ $usuario->Usuario }}</strong></p>
                    <p>Detectamos que tu cuenta aún utiliza el sistema anterior de claves. Por seguridad, por favor definí una nueva contraseña.</p>
                    <form method="POST" action="{{ route('password.setup.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Contraseña legacy actual</label>
                            <input type="password" name="legacy_password" class="form-control" required>
                            <div class="form-text">Ingresá tu contraseña actual para confirmar tu identidad.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                            <div class="form-text">Mínimo 8 caracteres.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar y continuar</button>
                    </form>
                    @if($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-muted">Volver al login</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>