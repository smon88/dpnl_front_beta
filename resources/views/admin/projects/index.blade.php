@extends('admin.layouts.app')

@section('title', 'Proyectos - Devil Panels')
@section('header_title', 'Gestión de Proyectos')
@section('page_id', 'projects')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-project-diagram"></i> Proyectos</h2>
    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Proyecto
    </a>
</div>

<x-session-alerts />

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;"></th>
                    <th>Nombre</th>
                    <th style="width: 140px;">Estado</th>
                    <th style="width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td>
                            <div class="project-logo">
                                @if($project->logo_url)
                                    <img src="{{ $project->logo_url }}" alt="{{ $project->name }}">
                                @else
                                    <div class="project-logo-placeholder">
                                        <i class="fas fa-project-diagram"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong>{{ $project->name }}</strong>
                            <small class="text-muted">{{ $project->slug }}</small>
                        </td>
                        <td>
                            <span class="badge {{ \App\Models\Project::getStatusBadgeClass($project->status) }}">
                                <i class="fas {{ \App\Models\Project::getStatusIcon($project->status) }}"></i>
                                {{ $project->status_label }}
                            </span>
                        </td>
                        <td class="actions">
                            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-secondary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Estas seguro de eliminar este proyecto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No hay proyectos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-pagination :paginator="$projects" />
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/projects.css') }}">
<style>
    .project-logo {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .project-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .project-logo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.3);
        font-size: 18px;
    }
</style>
@endpush
