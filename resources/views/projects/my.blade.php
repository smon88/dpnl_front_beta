@extends('admin.layouts.app')

@section('title', 'Mis Proyectos - Devil Panels')
@section('header_title', 'Mis Proyectos')
@section('page_id', 'projects')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-folder"></i> Mis Proyectos</h2>
    <a href="{{ route('projects.available') }}" class="btn btn-primary">
        <i class="fas fa-folder-open"></i> Ver Disponibles
    </a>
</div>

<x-session-alerts />

{{-- Pending Projects --}}
@if($pendingProjects->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5>
            <i class="fas fa-clock"></i>
            Solicitudes Pendientes
            <span class="badge badge-warning">{{ $pendingProjects->total() }}</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="projects-grid">
            @foreach($pendingProjects as $project)
                <div class="project-card project-pending card">
                    <div class="card-body">
                        <h5 class="project-title">
                            <i class="fas fa-project-diagram"></i>
                            {{ $project->name }}
                        </h5>
                        <p class="project-url">
                            <i class="fas fa-link"></i>
                            {{ Str::limit($project->url, 30) }}
                        </p>
                        <span class="badge badge-warning">
                            <i class="fas fa-clock"></i> Pendiente de aprobacion
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
        <x-pagination :paginator="$pendingProjects" pageName="pending" />
    </div>
</div>
@endif

{{-- Approved Projects --}}
<div class="card">
    <div class="card-header">
        <h5>
            <i class="fas fa-check-circle"></i>
            Proyectos Aprobados
            <span class="badge badge-success">{{ $approvedProjects->total() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($approvedProjects->count() > 0)
            <div class="projects-grid">
                @foreach($approvedProjects as $project)
                    <div class="project-card project-approved card">
                        <div class="project-header-badge">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="project-title">
                                <i class="fas fa-project-diagram"></i>
                                {{ $project->name }}
                            </h5>
                            <p class="project-url">
                                <i class="fas fa-link"></i>
                                <a href="{{ $project->url }}" target="_blank" class="text-info">
                                    {{ Str::limit($project->url, 30) }}
                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                </a>
                            </p>
                            @if($project->description)
                                <p class="project-description">{{ Str::limit($project->description, 80) }}</p>
                            @endif
                            <div class="project-role">
                                <span class="badge badge-info">
                                    <i class="fas fa-user-tag"></i>
                                    {{ ucfirst($project->pivot->role) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <x-pagination :paginator="$approvedProjects" pageName="approved" />
        @else
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                No tienes proyectos aprobados todavia.
                <a href="{{ route('projects.available') }}">Solicita acceso a un proyecto</a>.
            </div>
        @endif
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}">
<style>
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .project-card {
        position: relative;
        overflow: hidden;
    }

    .project-pending {
        border-color: rgba(245, 158, 11, 0.3);
    }

    .project-approved {
        border-color: rgba(34, 197, 94, 0.3);
    }

    .project-header-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.3), rgba(34, 197, 94, 0.15));
        border: 1px solid rgba(34, 197, 94, 0.4);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .project-header-badge i {
        color: #22c55e;
        font-size: 12px;
    }

    .project-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 12px;
        font-size: 16px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.95);
    }

    .project-title i {
        color: var(--devil-red);
        font-size: 14px;
    }

    .project-url {
        margin-bottom: 12px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.5);
    }

    .project-url i:first-child {
        margin-right: 6px;
    }

    .project-description {
        color: rgba(255, 255, 255, 0.6);
        font-size: 13px;
        line-height: 1.5;
        margin: 0 0 12px;
    }

    .project-role {
        margin-top: auto;
    }

    .mb-4 {
        margin-bottom: 24px;
    }
</style>
@endpush
