@extends('admin.layouts.app')

@section('title', 'Mi Perfil')
@section('header_title', 'Mi Perfil')
@section('page_id', 'profile')

@section('content')
<div class="profile-container">
    {{-- Profile Header Card --}}
    <div class="profile-header-card">
        <div class="profile-avatar">
            <div class="avatar-circle">
                <i class="fas fa-user"></i>
            </div>
            <div class="avatar-status {{ Auth::user()->isActive() ? 'online' : 'offline' }}"></div>
        </div>
        <div class="profile-info">
            <h2 class="profile-name">{{ Auth::user()->alias ?? Auth::user()->username }}</h2>
            <span class="profile-username">{{ '@' . Auth::user()->username }}</span>
            <div class="profile-badges">
                <span class="badge {{ Auth::user()->isAdmin() ? 'badge-primary' : 'badge-secondary' }}">
                    <i class="fas {{ Auth::user()->isAdmin() ? 'fa-crown' : 'fa-user' }}"></i>
                    {{ Auth::user()->isAdmin() ? 'Administrador' : 'Carder' }}
                </span>
                @if(Auth::user()->hasTelegramLinked())
                    <span class="badge badge-success">
                        <i class="fab fa-telegram"></i>
                        Vinculado
                    </span>
                @else
                    <span class="badge badge-warning">
                        <i class="fab fa-telegram"></i>
                        Sin vincular
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="profile-grid">
        {{-- Edit Profile Card --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-edit"></i> Editar Perfil</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.update') }}" method="POST" class="form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Usuario
                        </label>
                        <input
                            type="text"
                            id="username"
                            value="{{ Auth::user()->username }}"
                            disabled
                            class="input-disabled"
                        />
                        <span class="hint">El nombre de usuario no se puede cambiar.</span>
                    </div>

                    <div class="form-group">
                        <label for="alias">
                            <i class="fas fa-id-card"></i> Alias
                        </label>
                        <input
                            type="text"
                            id="alias"
                            name="alias"
                            value="{{ old('alias', Auth::user()->alias) }}"
                            placeholder="Nombre para mostrar"
                        />
                        <span class="hint">Este nombre se mostrara en la interfaz.</span>
                        @error('alias')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password Card --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.update') }}" method="POST" class="form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-key"></i> Contraseña Actual
                        </label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            placeholder="Ingresa tu contraseña actual"
                        />
                        @error('current_password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Nueva Contraseña
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimo 6 caracteres"
                        />
                        @error('password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">
                            <i class="fas fa-lock"></i> Confirmar Contraseña
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Repite la nueva contraseña"
                        />
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Telegram Card --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="fab fa-telegram"></i> Telegram</h3>
            </div>
            <div class="card-body">
                <div class="telegram-status">
                    @if(Auth::user()->hasTelegramLinked())
                        <div class="telegram-linked">
                            <div class="telegram-icon success">
                                <i class="fab fa-telegram"></i>
                            </div>
                            <div class="telegram-info">
                                <span class="telegram-label">Cuenta vinculada</span>
                                <span class="telegram-user">{{ Auth::user()->tg_user ?? 'Usuario de Telegram' }}</span>
                            </div>
                            <span class="status-indicator success">
                                <i class="fas fa-check-circle"></i>
                                Activo
                            </span>
                        </div>
                        <p class="telegram-description">
                            Tu cuenta de Telegram esta vinculada. Recibiras los codigos OTP para iniciar sesion.
                        </p>
                    @else
                        <div class="telegram-unlinked">
                            <div class="telegram-icon warning">
                                <i class="fab fa-telegram"></i>
                            </div>
                            <div class="telegram-info">
                                <span class="telegram-label">Sin vincular</span>
                                <span class="telegram-user">{{ Auth::user()->tg_user ?? 'No configurado' }}</span>
                            </div>
                            <span class="status-indicator warning">
                                <i class="fas fa-exclamation-circle"></i>
                                Pendiente
                            </span>
                        </div>
                        <div class="telegram-instructions">
                            <p>Para vincular tu cuenta de Telegram:</p>
                            <ol>
                                <li>Abre Telegram y busca el bot</li>
                                <li>Envia el comando <code>/start</code></li>
                                <li>Sigue las instrucciones del bot</li>
                            </ol>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Account Info Card --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Informacion de Cuenta</h3>
            </div>
            <div class="card-body">
                <div class="account-info">
                    <div class="info-row">
                        <div class="info-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Cuenta creada</span>
                            <span class="info-value">{{ Auth::user()->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Ultimo acceso</span>
                            <span class="info-value">
                                {{ Auth::user()->last_login_at ? Auth::user()->last_login_at->format('d/m/Y H:i') : 'Primera sesion' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Estado de cuenta</span>
                            <span class="info-value">
                                @if(Auth::user()->isActive())
                                    <span class="status-text success"><i class="fas fa-check"></i> Activa</span>
                                @else
                                    <span class="status-text danger"><i class="fas fa-times"></i> Inactiva</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Alerts --}}
<div class="profile-alerts">
    @if(session('success'))
        <div class="alert alert-success">
            <div class="alert-content">
                <i class="fas fa-check-circle alert-icon"></i>
                <span class="alert-message">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <div class="alert-content">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span class="alert-message">{{ session('error') }}</span>
            </div>
        </div>
    @endif
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ asset('assets/css/users.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
@endpush

@push('scripts')
<script>
    // Auto-dismiss alerts
    (function() {
        const alerts = document.querySelectorAll('.profile-alerts .alert');
        alerts.forEach((alert, index) => {
            alert.style.cursor = 'pointer';
            alert.addEventListener('click', () => dismissAlert(alert));

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
@endpush
