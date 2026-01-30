@extends('admin.layouts.app')

@section('title', 'Registros')
@section('header_title', 'Registros')
@section('page_id', 'records')

@section('content')
  <div class="card">
    <div class="cardHeader">
      <h2>Registros</h2>
    </div>
    <div class="cardBody">
      <div class="dev-placeholder">
        <div class="dev-placeholder-icon">📊</div>
        <h3 class="dev-placeholder-title">Registros del Sistema</h3>
        <p class="dev-placeholder-subtitle">
          Aquí podrás consultar los registros históricos, logs y estadísticas del sistema.
        </p>
        <div class="dev-placeholder-badge">
          <i class="fas fa-code"></i>
          <span>En Desarrollo</span>
        </div>
      </div>
    </div>
  </div>
@endsection
