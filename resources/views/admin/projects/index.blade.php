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
                    <th style="width: 60px;">ID</th>
                    <th>Nombre</th>
                    <th style="width: 120px;">Estado</th>
                    <th style="width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td>
                            <span class="project-id">#{{ $project->id }}</span>
                        </td>
                        <td>
                            <strong>{{ $project->name }}</strong>
                            <small class="text-muted">{{ $project->slug }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $project->is_active ? 'badge-success' : 'badge-danger' }}">
                                <i class="fas {{ $project->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                {{ $project->is_active ? 'Activo' : 'Inactivo' }}
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
<link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}">
<style>
    .project-id {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.5);
        font-weight: 600;
    }
</style>
@endpush
