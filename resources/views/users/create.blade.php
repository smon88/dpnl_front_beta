@extends('admin.layouts.app')

@section('title', 'Crear Usuario - Devil Panels')
@section('header_title', 'Nuevo Usuario')
@section('page_id', 'users-create')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-user-plus"></i> Nuevo Usuario</h2>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST" class="form">
            @csrf

            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Usuario
                </label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
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
                    value="{{ old('alias') }}"
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
                <input
                    type="text"
                    id="tg_user"
                    name="tg_user"
                    value="{{ old('tg_user') }}"
                    placeholder="@username"
                />
                <span class="hint">El usuario debe enviar /start al bot para vincular su cuenta.</span>
                @error('tg_user')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
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
                        required
                        placeholder="Repetir contraseña"
                    />
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Crear Usuario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/users.css') }}">
<style>
    .card { max-width: 600px; }
</style>
@endpush
