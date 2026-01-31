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
        <form action="{{ route('admin.projects.store') }}" method="POST">
            @csrf

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
<link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}">
<style>
    .card { max-width: 600px; }
</style>
@endpush
