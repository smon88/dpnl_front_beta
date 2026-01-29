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

        <div class="user-menu">
            <button class="user-menu-trigger" id="userMenuTrigger" aria-expanded="false" aria-haspopup="true">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span class="user-name">{{ Auth::user()->name ?? 'Usuario' }}</span>
                <i class="fas fa-chevron-down user-chevron"></i>
            </button>

            <div class="user-dropdown" id="userDropdown" aria-hidden="true">
                <div class="dropdown-header">
                    <div class="dropdown-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="dropdown-user-info">
                        <span class="dropdown-user-name">{{ Auth::user()->name ?? 'Usuario' }}</span>
                        <span class="dropdown-user-email">{{ Auth::user()->email ?? '' }}</span>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.profile') }}" class="dropdown-item">
                    <i class="fas fa-user-circle"></i>
                    <span>Perfil</span>
                </a>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.logout') }}" class="dropdown-item dropdown-item-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
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
      nodeUrl: @json(config('services.realtime.node_url')),
      tokenEndpoint: @json(url('/admin/socket-token')),
      page: @json(trim($__env->yieldContent('page_id'))),
    };
  </script>

  {{-- App modular --}}
  <script type="module" src="{{ asset('assets/js/admin/app.js') }}"></script>

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

  @stack('scripts')
</body>
</html>