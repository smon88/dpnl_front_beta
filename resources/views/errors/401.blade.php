<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>No Autorizado</title>
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
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
        .error-countdown {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 1rem;
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
        .spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h1 class="error-title">No Autorizado</h1>
        <p class="error-message">
            No tienes autorización para acceder a este recurso.
            Por favor inicia sesión nuevamente.
        </p>
        <p class="error-countdown">
            Redirigiendo en <span id="countdown">3</span> segundos...
        </p>
        <a href="{{ route('admin.login') }}" class="error-btn" id="redirectBtn">
            <i class="fas fa-sync-alt spinner"></i>
            <span>Iniciar Sesión</span>
        </a>
    </div>

    <script>
        (function() {
            let seconds = 3;
            const countdownEl = document.getElementById('countdown');
            const redirectUrl = '{{ route("admin.login") }}';

            const interval = setInterval(function() {
                seconds--;
                if (countdownEl) countdownEl.textContent = seconds;

                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = redirectUrl;
                }
            }, 1000);
        })();
    </script>
</body>
</html>
