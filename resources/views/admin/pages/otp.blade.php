<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificacion 2FA - Devil Panels</title>

    <link rel="stylesheet" href="{{ versioned_asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('assets/css/login.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

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
                <form method="POST" action="{{ route('admin.login.otp.resend') }}" id="resendForm" style="display: inline;">
                    @csrf
                    <button type="submit" id="resendBtn" class="resend-link" disabled>
                        <i class="fas fa-sync-alt"></i>
                        <span id="resendText">Reenviar codigo</span>
                    </button>
                </form>
                <span id="resendCounter" class="resend-counter"></span>
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
        .otp-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
            margin-top: 16px;
        }
        .resend-link {
            background: none;
            border: none;
            color: var(--text-secondary, #8b949e);
            cursor: pointer;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .resend-link:hover:not(:disabled) {
            color: var(--text-primary, #c9d1d9);
            background: var(--bg-secondary, rgba(255,255,255,0.05));
        }
        .resend-link:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .resend-link i {
            transition: transform 0.3s ease;
        }
        .resend-link:not(:disabled):hover i {
            transform: rotate(180deg);
        }
        .resend-counter {
            font-size: 12px;
            color: var(--text-muted, #6e7681);
        }
    </style>

    <script>
        const TIMEOUT_LOCKOUT_DURATION = 30000; // 30 segundos de bloqueo
        const RESEND_COOLDOWN = 30; // 30 segundos entre reenvíos

        // Toast notification function
        function showToast(type, title, message, duration = 5000) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            toast.innerHTML = `
                <i class="fas ${icons[type]} toast-icon"></i>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.classList.add('toast-exit'); setTimeout(() => this.parentElement.remove(), 200);">
                    <i class="fas fa-times"></i>
                </button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('toast-exit');
                setTimeout(() => toast.remove(), 200);
            }, duration);
        }

        // Disable input during timeout
        function lockInputForTimeout(seconds) {
            const otpInput = document.getElementById('otp');
            const label = document.querySelector('label[for="otp"]');

            otpInput.disabled = true;
            otpInput.style.opacity = '0.5';
            otpInput.style.cursor = 'not-allowed';

            let remaining = seconds;
            const originalLabelHTML = label.innerHTML;

            const countdown = setInterval(() => {
                label.innerHTML = `<i class="fas fa-clock"></i> Espera ${remaining}s para reintentar`;
                remaining--;

                if (remaining < 0) {
                    clearInterval(countdown);
                    otpInput.disabled = false;
                    otpInput.style.opacity = '1';
                    otpInput.style.cursor = '';
                    otpInput.value = '';
                    label.innerHTML = originalLabelHTML;
                    otpInput.focus();
                    showToast('info', 'Listo', 'Ya puedes ingresar el codigo nuevamente', 3000);
                }
            }, 1000);
        }

        // Check for timeout errors on page load
        (function checkTimeoutErrors() {
            const alerts = document.querySelectorAll('.login-alerts .alert-error');
            const timeoutKeywords = ['timeout', 'tiempo', 'espera', 'demasiados intentos', 'bloqueado', 'limite', 'muchos intentos'];

            alerts.forEach(alert => {
                const message = alert.textContent.toLowerCase();
                const isTimeout = timeoutKeywords.some(keyword => message.includes(keyword));

                if (isTimeout) {
                    showToast('warning', 'Demasiados intentos', 'Por favor espera antes de intentar nuevamente', TIMEOUT_LOCKOUT_DURATION);
                    lockInputForTimeout(TIMEOUT_LOCKOUT_DURATION / 1000);
                }
            });
        })();

        // Auto-format OTP input (only numbers)
        const otpInput = document.getElementById('otp');
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });

        // Auto-submit when 6 digits are entered
        otpInput.addEventListener('keyup', function(e) {
            if (this.value.length === 6 && !this.disabled) {
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

        // Resend OTP functionality
        (function() {
            const resendBtn = document.getElementById('resendBtn');
            const resendText = document.getElementById('resendText');
            const resendCounter = document.getElementById('resendCounter');
            const resendForm = document.getElementById('resendForm');

            let cooldownRemaining = RESEND_COOLDOWN;
            let cooldownInterval = null;

            // Cargar estado de cooldown de localStorage
            const savedCooldownEnd = localStorage.getItem('otp_resend_cooldown_end');
            if (savedCooldownEnd) {
                const remaining = Math.ceil((parseInt(savedCooldownEnd) - Date.now()) / 1000);
                if (remaining > 0) {
                    cooldownRemaining = remaining;
                    startCooldown();
                } else {
                    localStorage.removeItem('otp_resend_cooldown_end');
                    enableResendBtn();
                }
            } else {
                enableResendBtn();
            }

            function startCooldown() {
                resendBtn.disabled = true;
                updateCooldownUI();

                cooldownInterval = setInterval(() => {
                    cooldownRemaining--;
                    updateCooldownUI();

                    if (cooldownRemaining <= 0) {
                        clearInterval(cooldownInterval);
                        localStorage.removeItem('otp_resend_cooldown_end');
                        enableResendBtn();
                    }
                }, 1000);
            }

            function updateCooldownUI() {
                resendText.textContent = `Espera ${cooldownRemaining}s`;
            }

            function enableResendBtn() {
                resendBtn.disabled = false;
                resendText.textContent = 'Reenviar codigo';
            }

            // Manejar submit del form
            resendForm.addEventListener('submit', function(e) {
                if (resendBtn.disabled) {
                    e.preventDefault();
                    return;
                }

                // Guardar cooldown en localStorage
                cooldownRemaining = RESEND_COOLDOWN;
                localStorage.setItem('otp_resend_cooldown_end', Date.now() + (RESEND_COOLDOWN * 1000));
                startCooldown();
            });

            // Obtener intentos restantes al cargar
            fetch('{{ route("admin.login.otp.attempts") }}')
                .then(res => res.json())
                .then(data => {
                    if (data.remaining !== undefined) {
                        resendCounter.textContent = `(${data.remaining} de 5 restantes)`;
                        if (data.remaining <= 0) {
                            resendBtn.disabled = true;
                            const minutes = Math.ceil(data.availableIn / 60);
                            resendText.textContent = `Sin reenvios`;
                            resendCounter.textContent = `(disponible en ${minutes} min)`;
                        }
                    }
                })
                .catch(() => {
                    // Silently fail
                });
        })();
    </script>
</body>
</html>
