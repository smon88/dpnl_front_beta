@extends('admin.layouts.app')

@section('title', 'Crear Proyecto - Devil Panels')
@section('header_title', 'Crear Proyecto')
@section('page_id', 'projects')

@section('content')
<div class="page-header">
    <h2><i class="fas fa-plus"></i> Nuevo Proyecto</h2>
    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<x-session-alerts />

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Logo Upload --}}
            <div class="form-group">
                <label>
                    <i class="fas fa-image"></i> Logo del Proyecto
                </label>
                <div class="logo-upload-container">
                    <div class="logo-preview" id="logoPreview">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Click para subir</span>
                    </div>
                    <input type="file" name="logo" id="logoInput" accept="image/*" class="logo-input">
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
                    value="{{ old('name') }}" required placeholder="Mi Proyecto">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="url">
                    <i class="fas fa-link"></i> URL del Proyecto *
                </label>
                <input type="url" name="url" id="url" class="form-control @error('url') is-invalid @enderror"
                    value="{{ old('url') }}" required placeholder="https://ejemplo.com">
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <span class="form-text">URL completa del proyecto (ej: https://app.ejemplo.com)</span>
            </div>

            <div class="form-group">
                <label for="description">
                    <i class="fas fa-align-left"></i> Descripcion
                </label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                    rows="3" placeholder="Descripcion opcional del proyecto">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">
                    <i class="fas fa-toggle-on"></i> Estado *
                </label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                    @foreach(\App\Models\Project::getStatuses() as $value => $label)
                        <option value="{{ $value }}" {{ old('status', 'active') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Crear Proyecto
                </button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('head')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/projects.css') }}">
<style>
    .card { max-width: 600px; }

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
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('logoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('logoPreview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }
    });

    // Click on preview to trigger file input
    document.getElementById('logoPreview').addEventListener('click', function() {
        document.getElementById('logoInput').click();
    });
</script>
@endpush
