<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin')</title>

  {{-- Global Theme --}}
  <link rel="stylesheet" href="{{ versioned_asset('assets/css/theme.css') }}">
  {{-- Tu CSS actual del dashboard --}}
  <link rel="stylesheet" href="{{ versioned_asset('assets/css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ versioned_asset('assets/css/layout.css') }}">
  {{-- Reusable Components --}}
  <link rel="stylesheet" href="{{ versioned_asset('assets/css/components.css') }}">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  @stack('head')
</head>

<body>
  {{-- Page Loader (navigation spinner) --}}
  <x-page-loader />

  {{-- Toast Notifications Container --}}
  <x-toast-container />

  {{-- Error Handler for Session/Token Expiration & Rate Limiting --}}
  <script>
    (function() {
      const RATE_LIMIT_LOCKOUT = 30000; // 30 segundos de bloqueo por rate limit
      let rateLimitedUntil = 0;

      // Interceptar errores de fetch para mostrar toast y redirigir
      const originalFetch = window.fetch;
      window.fetch = async function(...args) {
        try {
          const response = await originalFetch.apply(this, args);

          // Verificar errores de sesión/token
          if (response.status === 419 || response.status === 401) {
            handleSessionError(response.status);
            return response;
          }

          // Verificar error de rate limiting (429 Too Many Requests)
          if (response.status === 429) {
            handleRateLimitError(response);
            return response;
          }

          return response;
        } catch (error) {
          throw error;
        }
      };

      // Manejar errores de sesión
      function handleSessionError(status) {
        const messages = {
          419: {
            title: 'Sesión Expirada',
            message: 'Tu sesión ha expirado. Redirigiendo al inicio...'
          },
          401: {
            title: 'No Autorizado',
            message: 'Tu sesión no es válida. Redirigiendo al inicio...'
          }
        };

        const config = messages[status] || messages[419];

        // Mostrar toast si está disponible
        if (window.Toast) {
          Toast.show({
            type: 'warning',
            title: config.title,
            message: config.message,
            duration: 3000,
            dismissible: false
          });
        }

        // Redirigir después de mostrar el toast
        setTimeout(function() {
          window.location.href = '{{ route("admin.login") }}';
        }, 2500);
      }

      // Manejar errores de rate limiting
      function handleRateLimitError(response) {
        // Obtener tiempo de espera del header Retry-After si existe
        const retryAfter = response.headers.get('Retry-After');
        const lockoutTime = retryAfter ? parseInt(retryAfter) * 1000 : RATE_LIMIT_LOCKOUT;

        rateLimitedUntil = Date.now() + lockoutTime;

        if (window.Toast) {
          Toast.show({
            type: 'warning',
            title: 'Demasiadas solicitudes',
            message: `Por favor espera ${Math.ceil(lockoutTime / 1000)} segundos antes de intentar nuevamente`,
            duration: lockoutTime,
            dismissible: false
          });
        }

        // Disparar evento personalizado para que las páginas puedan reaccionar
        window.dispatchEvent(new CustomEvent('rateLimitError', {
          detail: { lockoutTime, retryAfter: rateLimitedUntil }
        }));
      }

      // Verificar si está en rate limit
      function isRateLimited() {
        return Date.now() < rateLimitedUntil;
      }

      // Obtener tiempo restante de rate limit
      function getRateLimitRemaining() {
        return Math.max(0, rateLimitedUntil - Date.now());
      }

      // Exponer funciones para uso manual
      window.handleSessionError = handleSessionError;
      window.handleRateLimitError = handleRateLimitError;
      window.isRateLimited = isRateLimited;
      window.getRateLimitRemaining = getRateLimitRemaining;
    })();
  </script>

  {{-- Navigation Drawer --}}
  <div class="nav-drawer-overlay" id="navDrawerOverlay"></div>
  <nav class="nav-drawer" id="navDrawer" aria-hidden="true">
      <div class="nav-drawer-header">
          <div class="nav-drawer-logo">
              <img src="{{ asset('assets/img/logo.png') }}" alt="" class="nav-logo-img">
              <span>Devil Panels</span>
          </div>
          <button class="nav-drawer-close" id="navDrawerClose" aria-label="Cerrar menú">
              <i class="fas fa-times"></i>
          </button>
      </div>
      <div class="nav-drawer-content">
          <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
              <i class="fas fa-credit-card"></i>
              <span>Registros</span>
          </a>
          <a href="{{ route('admin.tools') }}" class="nav-item {{ request()->routeIs('admin.tools') ? 'active' : '' }}">
              <i class="fas fa-tools"></i>
              <span>Herramientas</span>
          </a>
          <div class="nav-divider"></div>
          {{-- Opciones para usuarios normales --}}
          @if(!Auth::user()->isAdmin())
          <a href="{{ route('projects.available') }}" class="nav-item {{ request()->routeIs('projects.available') ? 'active' : '' }}">
              <i class="fas fa-folder"></i>
              <span>Scams</span>
          </a>
          <a href="{{ route('projects.my') }}" class="nav-item {{ request()->routeIs('projects.my') ? 'active' : '' }}">
              <i class="fas fa-folder"></i>
              <span>Mis Scams</span>
          </a>
          <a href="{{ route('user.records') }}" class="nav-item {{ request()->routeIs('user.records') ? 'active' : '' }}">
              <i class="fas fa-history"></i>
              <span>Logs</span>
          </a>
          @endif
          {{-- Opciones solo para admin --}}
          @if(Auth::user()->isAdmin())
          <a href="{{ route('admin.traffic') }}" class="nav-item {{ request()->routeIs('admin.traffic') ? 'active' : '' }}">
              <i class="fas fa-signal"></i>
              <span>Tráfico</span>
          </a>
          <a href="{{ route('admin.records') }}" class="nav-item {{ request()->routeIs('admin.records.*') ? 'active' : '' }}">
              <i class="fas fa-database"></i>
              <span>Registros</span>
          </a>
          <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
              <i class="fas fa-users"></i>
              <span>Usuarios</span>
          </a>
          <a href="{{ route('admin.projects.index') }}" class="nav-item {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
              <i class="fas fa-project-diagram"></i>
              <span>Proyectos</span>
          </a>
          @endif
          <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
              <i class="fas fa-cog"></i>
              <span>Configuracion</span>
          </a>
      </div>
      <div class="nav-drawer-footer">
          <span class="nav-version">v1.0.0</span>
      </div>
  </nav>

  <div class="topbar">
        <div class="topbar-left">
            <button class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-item">
                <h1>
                    <img src="{{ asset('assets/img/logo.png') }}" alt="" class="topbar-logo">
                    @yield('header_title', 'Devil Panels')
                </h1>
            </div>
        </div>

        <div class="user-menu">
            <button class="user-menu-trigger" id="userMenuTrigger" aria-expanded="false" aria-haspopup="true">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span class="user-name">{{ Auth::user()->alias ?? Auth::user()->username ?? 'Usuario' }}</span>
                <i class="fas fa-chevron-down user-chevron"></i>
            </button>

            <div class="user-dropdown" id="userDropdown" aria-hidden="true">
                <div class="dropdown-header">
                    <div class="dropdown-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="dropdown-user-info">
                        <span class="dropdown-user-name">{{ Auth::user()->alias ?? Auth::user()->username ?? 'Usuario' }}</span>
                        <span class="dropdown-user-role">{{ Auth::user()->role === 'admin' ? 'Administrador' : 'Carder' }}</span>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.profile') }}" class="dropdown-item">
                    <i class="fas fa-user-circle"></i>
                    <span>Perfil</span>
                </a>
                <!-- <a href="#" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a> -->
                <div class="dropdown-divider"></div>
                <form action="{{ route('admin.logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="dropdown-item dropdown-item-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
  <div class="container">
    @yield('content')
  </div>

  {{-- Socket.io siempre antes que tu app --}}
  <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

  {{-- Config global, sin hardcodear IP en JS --}}
  <script>
    window.ADMIN_CFG = {
      nodeUrl: @json(config('services.node_backend.url')),
      tokenEndpoint: @json(url('/admin/socket-token')),
      page: @json(trim($__env->yieldContent('page_id'))),
    };
  </script>

  {{-- Admin App (Vite) --}}
  @vite('resources/js/admin/app.js')

  {{-- Navigation Drawer Script --}}
  <script>
    (function() {
      const toggle = document.getElementById('navToggle');
      const drawer = document.getElementById('navDrawer');
      const overlay = document.getElementById('navDrawerOverlay');
      const closeBtn = document.getElementById('navDrawerClose');

      if (!toggle || !drawer || !overlay) return;

      function toggleDrawer(open) {
        const isOpen = open ?? !drawer.classList.contains('open');
        drawer.classList.toggle('open', isOpen);
        overlay.classList.toggle('active', isOpen);
        toggle.setAttribute('aria-expanded', isOpen);
        drawer.setAttribute('aria-hidden', !isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
      }

      toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDrawer();
      });

      closeBtn?.addEventListener('click', () => toggleDrawer(false));
      overlay.addEventListener('click', () => toggleDrawer(false));

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('open')) {
          toggleDrawer(false);
          toggle.focus();
        }
      });
    })();
  </script>

  {{-- User Menu Script --}}
  <script>
    (function() {
      const trigger = document.getElementById('userMenuTrigger');
      const dropdown = document.getElementById('userDropdown');
      const menu = trigger?.closest('.user-menu');

      if (!trigger || !dropdown || !menu) return;

      // Create overlay for mobile
      const overlay = document.createElement('div');
      overlay.className = 'user-menu-overlay';
      document.body.appendChild(overlay);

      function toggleMenu(open) {
        const isOpen = open ?? !menu.classList.contains('open');
        menu.classList.toggle('open', isOpen);
        trigger.setAttribute('aria-expanded', isOpen);
        dropdown.setAttribute('aria-hidden', !isOpen);
        overlay.classList.toggle('active', isOpen);

        if (isOpen) {
          document.body.style.overflow = window.innerWidth <= 520 ? 'hidden' : '';
        } else {
          document.body.style.overflow = '';
        }
      }

      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu();
      });

      overlay.addEventListener('click', () => toggleMenu(false));

      document.addEventListener('click', (e) => {
        if (!menu.contains(e.target)) {
          toggleMenu(false);
        }
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('open')) {
          toggleMenu(false);
          trigger.focus();
        }
      });

      window.addEventListener('resize', () => {
        if (menu.classList.contains('open')) {
          document.body.style.overflow = window.innerWidth <= 520 ? 'hidden' : '';
        }
      });
    })();
  </script>

  {{-- Pagination Script --}}
  <script>
    (function() {
      document.addEventListener('DOMContentLoaded', function() {
        // Handle per-page select change
        document.querySelectorAll('[data-per-page-select]').forEach(function(select) {
          select.addEventListener('change', function(e) {
            const wrapper = e.target.closest('[data-pagination]');
            if (!wrapper) return;

            const pageName = wrapper.dataset.pageName || 'page';
            const perPageParam = pageName === 'page' ? 'per_page' : 'per_page_' + pageName;

            const url = new URL(window.location.href);
            url.searchParams.set(perPageParam, e.target.value);
            url.searchParams.set(pageName, '1'); // Reset to page 1

            // Show loading state
            wrapper.classList.add('loading');

            window.location.href = url.toString();
          });
        });
      });
    })();
  </script>

  @stack('scripts')
</body>
</html>