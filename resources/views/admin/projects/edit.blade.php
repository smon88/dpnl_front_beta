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
        <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Logo Upload --}}
            <div class="form-group">
                <label>
                    <i class="fas fa-image"></i> Logo del Proyecto
                </label>
                <div class="logo-upload-wrapper">
                    <div class="logo-upload-container">
                        <div class="logo-preview {{ $project->logo_url ? 'has-image' : '' }}" id="logoPreview">
                            @if($project->logo_url)
                                <img src="{{ $project->logo_url }}" alt="{{ $project->name }}">
                            @else
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Click para subir</span>
                            @endif
                        </div>
                        <input type="file" name="logo" id="logoInput" accept="image/*" class="logo-input">
                    </div>
                    @if($project->logo_url)
                        <label class="remove-logo-label">
                            <input type="checkbox" name="remove_logo" value="1" id="removeLogo">
                            <span>Eliminar logo actual</span>
                        </label>
                    @endif
                </div>
                @error('logo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <span class="form-text">Formatos: JPG, PNG, GIF, SVG, WebP. Max: 2MB</span>
            </div>

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
                <label for="status">
                    <i class="fas fa-toggle-on"></i> Estado *
                </label>
                <div class="status-selector">
                    @foreach(\App\Models\Project::getStatuses() as $value => $label)
                        <label class="status-option {{ old('status', $project->status) === $value ? 'active' : '' }}">
                            <input type="radio" name="status" value="{{ $value }}"
                                {{ old('status', $project->status) === $value ? 'checked' : '' }} required>
                            <span class="status-dot {{ \App\Models\Project::getStatusBadgeClass($value) }}">
                                <i class="fas {{ \App\Models\Project::getStatusIcon($value) }}"></i>
                            </span>
                            <span class="status-label">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('status')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
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

    .logo-upload-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .logo-upload-container {
        position: relative;
        width: 120px;
        height: 120px;
    }

    .logo-preview {
        width: 100%;
        height: 100%;
        border: 2px dashed rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        background: rgba(255, 255, 255, 0.02);
        overflow: hidden;
    }

    .logo-preview:hover {
        border-color: var(--devil-red);
        background: rgba(230, 57, 70, 0.05);
    }

    .logo-preview i {
        font-size: 28px;
        color: rgba(255, 255, 255, 0.4);
        margin-bottom: 8px;
    }

    .logo-preview span {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.4);
    }

    .logo-preview.has-image {
        border-style: solid;
        border-color: rgba(255, 255, 255, 0.1);
    }

    .logo-preview.has-image i,
    .logo-preview.has-image span {
        display: none;
    }

    .logo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .logo-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .remove-logo-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.6);
        cursor: pointer;
        margin-top: 40px;
    }

    .remove-logo-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--devil-red);
    }

    .status-selector {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .status-option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        background: rgba(255, 255, 255, 0.02);
    }

    .status-option:hover {
        border-color: rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.05);
    }

    .status-option.active {
        border-color: var(--devil-red);
        background: rgba(230, 57, 70, 0.1);
    }

    .status-option input[type="radio"] {
        display: none;
    }

    .status-dot {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }

    .status-dot.badge-success {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
    }

    .status-dot.badge-danger {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }

    .status-dot.badge-warning {
        background: rgba(251, 191, 36, 0.2);
        color: #fbbf24;
    }

    .status-label {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.8);
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('logoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('logoPreview');
        const removeCheckbox = document.getElementById('removeLogo');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.classList.add('has-image');
                if (removeCheckbox) removeCheckbox.checked = false;
            };
            reader.readAsDataURL(file);
        }
    });

    // Click on preview to trigger file input
    document.getElementById('logoPreview').addEventListener('click', function() {
        document.getElementById('logoInput').click();
    });

    // Status selector
    document.querySelectorAll('.status-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.status-option').forEach(opt => opt.classList.remove('active'));
            this.closest('.status-option').classList.add('active');
        });
    });
</script>
@endpush
