<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificacion 2FA - Devil Panels</title>

    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
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
                <span class="pill">Verificacion 2FA</span>
            </div>

            <div class="otp-info">
                <i class="fab fa-telegram"></i>
                <p>Se ha enviado un codigo de 6 digitos a tu Telegram.</p>
            </div>

            @if ($errors->any())
                <div class="login-alert">
                    @foreach ($errors->all() as $error)
                        <span><i class="fas fa-exclamation-circle"></i> {{ $error }}</span>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.otp.submit') }}" class="login-form">
                @csrf

                <div class="input-group">
                    <label for="otp">
                        <i class="fas fa-shield-halved"></i>
                        Codigo OTP
                    </label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        required
                        autocomplete="one-time-code"
                        placeholder="Ingresa el codigo de 6 digitos"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autofocus
                    />
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-check-circle"></i>
                    Verificar
                </button>
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

    <style>
        .otp-info {
            text-align: center;
            padding: 1rem;
            margin-bottom: 1rem;
            background: rgba(0, 136, 204, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(0, 136, 204, 0.3);
        }
        .otp-info i {
            font-size: 2rem;
            color: #0088cc;
            margin-bottom: 0.5rem;
        }
        .otp-info p {
            margin: 0;
            color: #ccc;
            font-size: 0.9rem;
        }
        .otp-actions {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link {
            color: #888;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #fff;
        }
        .back-link i {
            margin-right: 0.3rem;
        }
        #otp {
            text-align: center;
            font-size: 1.5rem;
            letter-spacing: 0.5rem;
            font-weight: bold;
        }
    </style>
</body>
</html>
