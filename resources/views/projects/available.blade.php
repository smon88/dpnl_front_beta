@extends('admin.layouts.app')

@section('title', 'Proyectos Disponibles - Devil Panels')
@section('header_title', 'Proyectos Disponibles')
@section('page_id', 'projects')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-folder-open"></i> Proyectos Disponibles</h2>
    <a href="{{ route('projects.my') }}" class="btn btn-secondary">
        <i class="fas fa-folder"></i> Mis Proyectos
    </a>
</div>

<x-session-alerts />

<div class="projects-grid">
    @forelse($projects as $project)
        <div class="project-card card">
            <div class="card-header">
                <h5><i class="fas fa-project-diagram"></i> {{ $project->name }}</h5>
            </div>
            <div class="card-body">
                <p class="project-url">
                    <i class="fas fa-link"></i>
                    <a href="{{ $project->url }}" target="_blank" class="text-info">
                        {{ Str::limit($project->url, 35) }}
                        <i class="fas fa-external-link-alt fa-xs"></i>
                    </a>
                </p>
                @if($project->description)
                    <p class="project-description">{{ Str::limit($project->description, 100) }}</p>
                @endif
            </div>
            <div class="card-footer">
                <form action="{{ route('projects.request', $project) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Solicitar Acceso
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                No hay proyectos disponibles para solicitar acceso en este momento.
            </div>
        </div>
    @endforelse
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}">
<style>
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    .empty-state {
        grid-column: 1 / -1;
    }

    .project-card {
        display: flex;
        flex-direction: column;
    }

    .project-card .card-body {
        flex: 1;
    }

    .project-url {
        margin-bottom: 12px;
        font-size: 13px;
    }

    .project-url i:first-child {
        color: rgba(255, 255, 255, 0.4);
        margin-right: 6px;
    }

    .project-description {
        color: rgba(255, 255, 255, 0.6);
        font-size: 13px;
        line-height: 1.5;
        margin: 0;
    }
</style>
@endpush
