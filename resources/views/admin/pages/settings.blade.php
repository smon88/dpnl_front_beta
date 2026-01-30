@extends('admin.layouts.app')

@section('title', 'Configuración')
@section('header_title', 'Configuración')
@section('page_id', 'settings')

@section('content')
  <div class="card">
    <div class="cardHeader">
      <h2>Configuración</h2>
    </div>
    <div class="cardBody">
      <div class="dev-placeholder">
        <div class="dev-placeholder-icon">⚙️</div>
        <h3 class="dev-placeholder-title">Configuración del Sistema</h3>
        <p class="dev-placeholder-subtitle">
          Configura las preferencias del sistema, notificaciones, integraciones y opciones avanzadas.
        </p>
        <div class="dev-placeholder-badge">
          <i class="fas fa-code"></i>
          <span>En Desarrollo</span>
        </div>
      </div>
    </div>
  </div>
@endsection
