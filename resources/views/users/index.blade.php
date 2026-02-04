@extends('admin.layouts.app')

@section('title', 'Usuarios - Devil Panels')
@section('header_title', 'Gestion de Usuarios')
@section('page_id', 'users')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-users"></i> Usuarios</h2>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Usuario
    </a>
</div>

<x-session-alerts />

<div class="card">
    <div class="card-body">
        <table class="table" id="usersTable">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Alias</th>
                    <th>Telegram</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Conexion</th>
                    <th>Ultimo Login</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr data-user-id="{{ $user->backend_uid }}">
                        <td>
                            <strong>{{ $user->username }}</strong>
                        </td>
                        <td>{{ $user->alias ?? '-' }}</td>
                        <td>
                            @if($user->tg_user)
                                <span class="badge {{ $user->tg_linked ? 'badge-success' : 'badge-warning' }}">
                                    {{ $user->tg_user }}
                                    @if($user->tg_linked)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-clock"></i>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'badge-primary' : 'badge-secondary' }}">
                                {{ $user->role === 'admin' ? 'Admin' : 'User' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <span class="online-status offline" data-online-indicator>
                                <span class="online-dot"></span>
                                <span class="online-text">Offline</span>
                            </span>
                        </td>
                        <td>
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca' }}
                        </td>
                        <td class="actions">
                            @if(!$user->isAdmin())
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;" onsubmit="return confirm('Estas seguro de eliminar este usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-pagination :paginator="$users" />
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/users.css') }}">
@endpush
