@extends('admin.layouts.app')

@section('title', 'Editar ' . $project->name . ' - Devil Panels')
@section('header_title', 'Editar Proyecto')
@section('page_id', 'projects')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-edit"></i> Editar: {{ $project->name }}</h2>
    <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<x-session-alerts />

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.projects.update', $project) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">
                    <i class="fas fa-project-diagram"></i> Nombre del Proyecto *
                </label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $project->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="slug">
                    <i class="fas fa-hashtag"></i> Slug
                </label>
                <input type="text" id="slug" class="form-control" value="{{ $project->slug }}" disabled>
                <span class="form-text">El slug no puede ser modificado.</span>
            </div>

            <div class="form-group">
                <label for="url">
                    <i class="fas fa-link"></i> URL del Proyecto *
                </label>
                <input type="url" name="url" id="url" class="form-control @error('url') is-invalid @enderror"
                    value="{{ old('url', $project->url) }}" required>
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">
                    <i class="fas fa-align-left"></i> Descripcion
                </label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                    rows="3">{{ old('description', $project->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $project->is_active) ? 'checked' : '' }}>
                    <span class="checkmark"></span>
                    Proyecto Activo
                </label>
                <span class="form-text">Si se desactiva, el proyecto no estara disponible para los usuarios.</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/projects.css') }}">
<style>
    .card { max-width: 600px; }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
    }

    .checkbox-label input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: var(--devil-red);
    }
</style>
@endpush
