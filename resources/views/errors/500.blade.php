<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del Servidor</title>
    <link rel="stylesheet" href="{{ versioned_asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: var(--font-family);
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 400px;
        }
        .error-icon {
            font-size: 4rem;
            color: var(--red);
            margin-bottom: 1.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .error-message {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .error-btn:hover {
            background: var(--accent-hover);
        }
        .error-btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }
        .error-btn-secondary:hover {
            background: var(--bg-secondary);
        }
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1 class="error-title">Error del Servidor</h1>
        <p class="error-message">
            Ha ocurrido un error inesperado. Por favor intenta nuevamente o contacta al administrador si el problema persiste.
        </p>
        <div class="error-actions">
            <a href="javascript:location.reload()" class="error-btn">
                <i class="fas fa-redo"></i>
                <span>Reintentar</span>
            </a>
            <a href="{{ route('admin.dashboard') }}" class="error-btn error-btn-secondary">
                <i class="fas fa-home"></i>
                <span>Ir al Inicio</span>
            </a>
        </div>
    </div>
</body>
</html>
