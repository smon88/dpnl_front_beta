@extends('admin.layouts.app')

@section('title', $project->name . ' - Devil Panels')
@section('header_title', 'Detalles del Proyecto')
@section('page_id', 'projects')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-project-diagram"></i> {{ $project->name }}</h2>
    <div>
        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<x-session-alerts />

<div class="projects-detail-layout">
    <div class="sidebar">
        {{-- Project Info --}}
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Informacion</h5>
            </div>
            <div class="card-body project-info">
                <p><strong>Slug:</strong> <code>{{ $project->slug }}</code></p>
                <p>
                    <strong>Project ID:</strong>
                    @if($project->backend_uid)
                        <code class="copyable" onclick="navigator.clipboard.writeText('{{ $project->backend_uid }}'); this.classList.add('copied');" title="Click para copiar">
                            {{ Str::limit($project->backend_uid, 20) }}
                        </code>
                        <i class="fas fa-copy copy-icon" onclick="navigator.clipboard.writeText('{{ $project->backend_uid }}')" title="Copiar ID completo"></i>
                    @else
                        <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> No sincronizado</span>
                    @endif
                </p>
                <p>
                    <strong>URL:</strong>
                    <a href="{{ $project->url }}" target="_blank">
                        {{ Str::limit($project->url, 25) }}
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </p>
                <p><strong>Descripcion:</strong> {{ $project->description ?? 'Sin descripcion' }}</p>
                <p>
                    <strong>Estado:</strong>
                    <span class="badge {{ $project->is_active ? 'badge-success' : 'badge-danger' }}">
                        <i class="fas {{ $project->is_active ? 'fa-check' : 'fa-times' }}"></i>
                        {{ $project->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>
                <p><strong>Creado:</strong> {{ $project->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Assign User --}}
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user-plus"></i> Asignar Usuario</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.projects.assign', $project) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="user_id">
                            <i class="fas fa-user"></i> Usuario
                        </label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            @foreach(\App\Models\User::where('role', 'user')->whereNotIn('id', $project->users->pluck('id'))->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->alias ?? 'Sin alias' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role">
                            <i class="fas fa-user-tag"></i> Rol
                        </label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="user">User</option>
                            <option value="useradmin">UserAdmin</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus"></i> Asignar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="main-content">
        {{-- Pending Requests --}}
        @if($pendingUsers->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="fas fa-clock"></i>
                    Solicitudes Pendientes
                    <span class="badge badge-warning">{{ $pendingUsers->count() }}</span>
                </h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingUsers as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->username }}</strong>
                                    <small class="text-muted">({{ $user->alias ?? 'Sin alias' }})</small>
                                </td>
                                <td>{{ $user->pivot->created_at->diffForHumans() }}</td>
                                <td class="actions">
                                    <form action="{{ route('admin.projects.approve', [$project, $user]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.projects.reject', [$project, $user]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Rechazar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Approved Users --}}
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="fas fa-users"></i>
                    Usuarios del Proyecto
                    <span class="badge badge-success">{{ $approvedUsers->count() }}</span>
                </h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Desde</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvedUsers as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->username }}</strong>
                                    <small class="text-muted">({{ $user->alias ?? 'Sin alias' }})</small>
                                </td>
                                <td>
                                    <form action="{{ route('admin.projects.role', [$project, $user]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="user" {{ $user->pivot->role === 'user' ? 'selected' : '' }}>User</option>
                                            <option value="useradmin" {{ $user->pivot->role === 'useradmin' ? 'selected' : '' }}>UserAdmin</option>
                                            <option value="owner" {{ $user->pivot->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                        </select>
                                    </form>
                                </td>
                                <td>{{ $user->pivot->created_at->diffForHumans() }}</td>
                                <td class="actions">
                                    <form action="{{ route('admin.projects.remove', [$project, $user]) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Remover este usuario del proyecto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No hay usuarios asignados a este proyecto.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}">
<style>
    .project-info code {
        background: rgba(230, 57, 70, 0.15);
        border: 1px solid rgba(230, 57, 70, 0.3);
        color: var(--devil-red-light);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
    }

    .project-info code.copyable {
        cursor: pointer;
        transition: all 0.2s;
    }

    .project-info code.copyable:hover {
        background: rgba(230, 57, 70, 0.25);
    }

    .project-info code.copied {
        background: rgba(34, 197, 94, 0.2);
        border-color: rgba(34, 197, 94, 0.4);
    }

    .copy-icon {
        cursor: pointer;
        margin-left: 8px;
        color: rgba(255, 255, 255, 0.5);
        transition: color 0.2s;
    }

    .copy-icon:hover {
        color: var(--devil-red-light);
    }
</style>
@endpush
