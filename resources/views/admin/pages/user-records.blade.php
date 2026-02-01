@extends('admin.layouts.app')

@section('title', 'Mis Registros')
@section('header_title', 'Mis Registros')
@section('page_id', 'user-records')

@section('content')
  <div class="card">
    <div class="cardHeader">
      <h2>Mi Historial</h2>
    </div>
    <div class="cardBody">
      <div class="tabs-container">
        <div class="tabs-header">
          <button class="tab-btn active" data-tab="login-history">
            <i class="fas fa-sign-in-alt"></i>
            <span>Inicios de Sesion</span>
          </button>
          <button class="tab-btn" data-tab="requests-history">
            <i class="fas fa-paper-plane"></i>
            <span>Solicitudes</span>
          </button>
          <button class="tab-btn" data-tab="activity-history">
            <i class="fas fa-clock"></i>
            <span>Actividad</span>
          </button>
        </div>

        <div class="tabs-content">
          <div class="tab-pane active" id="login-history">
            <div class="dev-placeholder">
              <div class="dev-placeholder-icon">
                <i class="fas fa-sign-in-alt"></i>
              </div>
              <h3 class="dev-placeholder-title">Historial de Inicios de Sesion</h3>
              <p class="dev-placeholder-subtitle">
                Aqui veras todos tus inicios de sesion, incluyendo fecha, hora e IP.
              </p>
              <div class="dev-placeholder-badge">
                <i class="fas fa-code"></i>
                <span>En Desarrollo</span>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="requests-history">
            <div class="dev-placeholder">
              <div class="dev-placeholder-icon">
                <i class="fas fa-paper-plane"></i>
              </div>
              <h3 class="dev-placeholder-title">Historial de Solicitudes</h3>
              <p class="dev-placeholder-subtitle">
                Aqui veras tus solicitudes de acceso a proyectos y su estado.
              </p>
              <div class="dev-placeholder-badge">
                <i class="fas fa-code"></i>
                <span>En Desarrollo</span>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="activity-history">
            <div class="dev-placeholder">
              <div class="dev-placeholder-icon">
                <i class="fas fa-clock"></i>
              </div>
              <h3 class="dev-placeholder-title">Historial de Actividad</h3>
              <p class="dev-placeholder-subtitle">
                Aqui veras tu actividad reciente en el panel.
              </p>
              <div class="dev-placeholder-badge">
                <i class="fas fa-code"></i>
                <span>En Desarrollo</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });
</script>
@endpush
