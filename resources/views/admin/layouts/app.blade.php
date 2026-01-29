<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin')</title>

  {{-- Tu CSS actual del dashboard --}}
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/layout.css') }}">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  @stack('head')
</head>

<body>
  <div class="topbar">
        <div class="topbar-item"><h1>@yield('header_title', 'Devil Panels')</h1></div>
        <div class="topbar-item topbar-logout"><a href="#">Cerrar Sesión</a></div>

      <!-- <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <a class="pill" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a class="pill" href="{{ route('admin.profile') }}">Perfil</a>
        <a class="pill" href="{{ route('admin.traffic') }}">Tráfico</a>

        <div class="pill" id="connPill"></div>
      </div> -->
    </div>
  <div class="container">
    @yield('content')
  </div>

  {{-- Socket.io siempre antes que tu app --}}
  <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

  {{-- Config global, sin hardcodear IP en JS --}}
  <script>
    window.ADMIN_CFG = {
      nodeUrl: @json(config('services.realtime.node_url')),
      tokenEndpoint: @json(url('/admin/socket-token')),
      page: @json(trim($__env->yieldContent('page_id'))),
    };
  </script>

  {{-- App modular --}}
  <script type="module" src="{{ asset('assets/js/admin/app.js') }}"></script>

  @stack('scripts')
</body>
</html>