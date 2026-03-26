<!DOCTYPE html>
<html>
<head>
    <title>Autorización Google Drive</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; }
        h2 { color: green; }
        .token { background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; word-break: break-all; }
        .env-vars { background: #e7f3ff; padding: 15px; border-radius: 5px; }
        pre { background: #333; color: #fff; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>¡Autorización exitosa!</h2>
    <p>El refresh token ha sido obtenido correctamente.</p>
    
    <h3>Refresh Token:</h3>
    <div class="token">{{ $refreshToken }}</div>
    
    <h3>Agregar al archivo .env:</h3>
    <div class="env-vars">
        <pre>GOOGLE_DRIVE_REFRESH_TOKEN={{ $refreshToken }}
GOOGLE_DRIVE_FOLDER_ID={{ $folderId }}</pre>
    </div>
</body>
</html>
