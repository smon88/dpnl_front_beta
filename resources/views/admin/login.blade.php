<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login</title>
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
  @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body>
  <div class="shell">
    <div class="card">
      <div class="cardHeader">
        <div class="brand">
          <h1>Acceso Admin</h1>
          <span class="pill">Panel Seguro</span>
        </div>
        <p class="sub">Ingresa la clave para abrir el dashboard.</p>

        @if ($errors->any())
          <div class="alert">
            <b>Error</b>
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>

      <div class="cardBody">
        <form method="POST" action="{{ route('admin.login.submit') }}">
          @csrf

          <div>
            <label>Clave</label>
            <input
              type="password"
              name="password"
              required
              class="input"
              placeholder="********"
              autofocus
            />
          </div>

          <button type="submit" class="btn">
            Entrar
          </button>

          <div class="footerNote">
            <span>Conexión cifrada</span>
            <span class="kbd">Enter</span>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
