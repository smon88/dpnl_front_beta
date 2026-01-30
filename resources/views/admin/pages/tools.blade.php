@extends('admin.layouts.app')

@section('title', 'Herramientas')
@section('header_title', 'Herramientas')
@section('page_id', 'tools')

@section('content')
  <div class="card">
    <div class="cardHeader">
      <h2>Herramientas</h2>
    </div>
    <div class="cardBody">
      <div class="dev-placeholder">
        <div class="dev-placeholder-icon">🛠️</div>
        <h3 class="dev-placeholder-title">Herramientas</h3>
        <p class="dev-placeholder-subtitle">
          Esta sección incluirá herramientas útiles para la gestión y administración del sistema.
        </p>
        <div class="dev-placeholder-badge">
          <i class="fas fa-code"></i>
          <span>En Desarrollo</span>
        </div>
      </div>
    </div>
  </div>
@endsection
