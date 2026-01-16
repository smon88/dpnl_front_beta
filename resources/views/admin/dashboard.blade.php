<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

</head>

<body>
  <div class="container">
    <div class="topbar">
      <h1>Dashboard Admin</h1>
      <div class="pill" id="connPill"></div>
    </div>

    <div class="wrap">
      <!-- Sessions list -->
      <div class="card">
        <div class="cardHeader">
          <h2>Sesiones</h2>
          <div class="search">
            <span style="opacity:.7;font-size:12px;">🔎</span>
            <input id="q" placeholder="Buscar por id/user/estado..." />
          </div>
        </div>
        <div class="cardBody">
          <div id="sessionsList"></div>
        </div>
      </div>

      <!-- Desktop detail card (hidden on small screens) -->
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
  </div>

  <!-- Mobile modal -->
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
        <div class="modalPre" id="modalDetailBox">{}</div>
      </div>

      <div class="modalActions" id="modalActions"></div>
    </div>
  </div>

  <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
</body>
</html>
