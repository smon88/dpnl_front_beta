<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Devil Panels</title>

    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-brand">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Devil Panels" class="login-logo-img">
                    <h1>Devil Panels</h1>
                </div>
                <span class="pill">Pre Beta</span>
            </div>

            {{-- Session Alerts --}}
            <div class="login-alerts">
                @if(session('success'))
                    <div class="alert alert-success">
                        <div class="alert-content">
                            <i class="fas fa-check-circle alert-icon"></i>
                            <span class="alert-message">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info">
                        <div class="alert-content">
                            <i class="fas fa-info-circle alert-icon"></i>
                            <span class="alert-message">{{ session('info') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning">
                        <div class="alert-content">
                            <i class="fas fa-exclamation-triangle alert-icon"></i>
                            <span class="alert-message">{{ session('warning') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-error">
                            <div class="alert-content">
                                @if(str_contains($error, 'Telegram'))
                                    <i class="fab fa-telegram alert-icon"></i>
                                @elseif(str_contains($error, 'desactivada'))
                                    <i class="fas fa-user-slash alert-icon"></i>
                                @elseif(str_contains($error, 'Credenciales'))
                                    <i class="fas fa-key alert-icon"></i>
                                @elseif(str_contains($error, 'expirada'))
                                    <i class="fas fa-clock alert-icon"></i>
                                @else
                                    <i class="fas fa-exclamation-circle alert-icon"></i>
                                @endif
                                <span class="alert-message">{{ $error }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <form method="POST" action="{{ route('admin.login.submit') }}" class="login-form">
                @csrf

                <div class="input-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Usuario
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        placeholder="Ingresa tu usuario"
                    />
                </div>

                <div class="input-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <div class="input-password">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Ingresa tu contraseña"
                        />
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="login-footer">
                <span>Developed by <strong>Dev1lB0y</strong></span>
                <a class="tg-footer-btn" href="https://t.me/Dev1lb0y666">
                     <i class="fa-brands fa-telegram"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        // Auto-dismiss alerts
        (function() {
            const alerts = document.querySelectorAll('.login-alerts .alert');
            alerts.forEach((alert, index) => {
                // Click to dismiss
                alert.style.cursor = 'pointer';
                alert.addEventListener('click', () => dismissAlert(alert));

                // Auto dismiss after 5 seconds (staggered)
                const isError = alert.classList.contains('alert-error');
                const delay = isError ? 8000 : 5000; // Errors stay longer
                setTimeout(() => dismissAlert(alert), delay + (index * 200));
            });

            function dismissAlert(alert) {
                alert.style.animation = 'alertSlideOut 0.3s ease-in forwards';
                setTimeout(() => alert.remove(), 300);
            }
        })();
    </script>
</body>
</html>
