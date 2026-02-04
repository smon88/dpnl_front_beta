@extends('admin.layouts.app')

@section('title', 'Registros')
@section('header_title', 'Devil Panels')
@section('page_id', 'records')

@section('content')
  <div class="card">
    <div class="cardHeader">
      <h2>Registros del Sistema</h2>
    </div>
    <div class="cardBody">
      <div class="tabs-container">
        <div class="tabs-header">
          <button class="tab-btn active" data-tab="panel-logs">
            <i class="fas fa-sign-in-alt"></i>
            <span>Logs de Usuarios</span>
          </button>
          <button class="tab-btn" data-tab="project-assignments">
            <i class="fas fa-user-check"></i>
            <span>Proyectos Asignados</span>
          </button>
          <button class="tab-btn" data-tab="project-logs">
            <i class="fas fa-project-diagram"></i>
            <span>Logs de Proyectos</span>
          </button>
          <button class="tab-btn" data-tab="blacklist">
            <i class="fas fa-ban"></i>
            <span>Blacklist</span>
          </button>
        </div>

        <div class="tabs-content">
          <div class="tab-pane active" id="panel-logs">
            <div class="dev-placeholder">
              <div class="dev-placeholder-icon">
                <i class="fas fa-sign-in-alt"></i>
              </div>
              <h3 class="dev-placeholder-title">Logs de Usuarios del Panel</h3>
              <p class="dev-placeholder-subtitle">
                Historial de inicios de sesion, actividad y acciones de todos los usuarios del panel.
              </p>
              <div class="dev-placeholder-badge">
                <i class="fas fa-code"></i>
                <span>En Desarrollo</span>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="project-assignments">
            <div class="dev-placeholder">
              <div class="dev-placeholder-icon">
                <i class="fas fa-user-check"></i>
              </div>
              <h3 class="dev-placeholder-title">Proyectos Asignados</h3>
              <p class="dev-placeholder-subtitle">
                Registro de asignaciones de usuarios a proyectos, solicitudes y cambios de roles.
              </p>
              <div class="dev-placeholder-badge">
                <i class="fas fa-code"></i>
                <span>En Desarrollo</span>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="project-logs">
            <div class="dev-placeholder">
              <div class="dev-placeholder-icon">
                <i class="fas fa-project-diagram"></i>
              </div>
              <h3 class="dev-placeholder-title">Logs de Proyectos Desplegados</h3>
              <p class="dev-placeholder-subtitle">
                Trafico, usuarios conectados y actividad de los proyectos desplegados.
              </p>
              <div class="dev-placeholder-badge">
                <i class="fas fa-code"></i>
                <span>En Desarrollo</span>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="blacklist">
            <div class="dev-placeholder">
              <div class="dev-placeholder-icon">
                <i class="fas fa-ban"></i>
              </div>
              <h3 class="dev-placeholder-title">IPs en Blacklist</h3>
              <p class="dev-placeholder-subtitle">
                Lista de IPs baneadas de los proyectos desplegados.
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
