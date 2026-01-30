@extends('admin.layouts.app')

@section('title', 'Editar Usuario - Devil Panels')
@section('header_title', 'Editar Usuario')
@section('page_id', 'users-edit')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-user-edit"></i> Editar Usuario</h2>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST" class="form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Usuario
                </label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username', $user->username) }}"
                    required
                    placeholder="Nombre de usuario"
                />
                @error('username')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="alias">
                    <i class="fas fa-id-card"></i> Alias (opcional)
                </label>
                <input
                    type="text"
                    id="alias"
                    name="alias"
                    value="{{ old('alias', $user->alias) }}"
                    placeholder="Nombre para mostrar"
                />
                @error('alias')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="tg_user">
                    <i class="fab fa-telegram"></i> Usuario Telegram
                </label>
                <div class="input-with-status">
                    <input
                        type="text"
                        id="tg_user"
                        name="tg_user"
                        value="{{ old('tg_user', $user->tg_user) }}"
                        placeholder="@username"
                    />
                    @if($user->tg_linked)
                        <span class="status-badge success">
                            <i class="fas fa-check"></i> Vinculado
                        </span>
                    @elseif($user->tg_user)
                        <span class="status-badge warning">
                            <i class="fas fa-clock"></i> Pendiente
                        </span>
                    @endif
                </div>
                <span class="hint">El usuario debe enviar /start al bot para vincular su cuenta.</span>
                @error('tg_user')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Nueva Contraseña (opcional)
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Dejar vacio para mantener"
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
                        placeholder="Repetir contraseña"
                    />
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                    />
                    <span class="checkmark"></span>
                    Usuario activo
                </label>
                <span class="hint">Si se desactiva, el usuario no podra iniciar sesion.</span>
            </div>

            <div class="user-info">
                <div class="info-item">
                    <span class="label">Creado:</span>
                    <span class="value">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Ultimo login:</span>
                    <span class="value">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ asset('assets/css/users.css') }}">
<style>
    .card { max-width: 600px; }
</style>
@endpush
