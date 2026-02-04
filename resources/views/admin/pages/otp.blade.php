<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificacion 2FA - Devil Panels</title>

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
                <span class="pill pill-2fa">
                    <i class="fas fa-shield-halved"></i>
                    Verificacion 2FA
                </span>
            </div>

            <div class="otp-info">
                <i class="fab fa-telegram"></i>
                <p>Se ha enviado un codigo de <strong>6 digitos</strong> a tu Telegram.<br>Ingresalo a continuacion para verificar tu identidad.</p>
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

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-error">
                            <div class="alert-content">
                                @if(str_contains($error, 'incorrecto') || str_contains($error, 'expirado'))
                                    <i class="fas fa-times-circle alert-icon"></i>
                                @else
                                    <i class="fas fa-exclamation-circle alert-icon"></i>
                                @endif
                                <span class="alert-message">{{ $error }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <form method="POST" action="{{ route('admin.login.otp.submit') }}" class="login-form">
                @csrf

                <div class="input-group">
                    <label for="otp">
                        <i class="fas fa-key"></i>
                        Codigo OTP
                    </label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        required
                        autocomplete="one-time-code"
                        placeholder="000000"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autofocus
                    />
                </div>
            </form>

            <div class="otp-actions">
                <a href="{{ route('admin.login') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Volver al login
                </a>
            </div>

            <div class="login-footer">
                <span>Developed by <strong>Dev1lB0y</strong></span>
            </div>
        </div>
    </div>

    <script>
        // Auto-format OTP input (only numbers)
        const otpInput = document.getElementById('otp');
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });

        // Auto-submit when 6 digits are entered
        otpInput.addEventListener('keyup', function(e) {
            if (this.value.length === 6) {
                // Small delay to show the complete code
                setTimeout(() => {
                    this.form.submit();
                }, 300);
            }
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
                const delay = isError ? 8000 : 5000;
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
