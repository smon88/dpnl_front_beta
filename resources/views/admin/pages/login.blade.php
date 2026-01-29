<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Devil Panel</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
  

</head>

<body>
  <div class="shell">
    <div class="login-logo">
      <H1>Devil Panel's</H1>
    </div>
    <div class="card">
      <div class="cardHeader">
        <div class="brand">
          <h1>Bienvenido!</h1>
          <span class="pill">Pre beta</span>
        </div>
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
            <label>Usuario</label>
            <input type="user" name="username" required class="input" autofocus />
          </div>
          <div>
            <label>Contraseña</label>
            <input type="password" name="password" required class="input" autofocus />
          </div>
          <div>
            <label>Codigo Otp</label>
            <input type="text" name="2fa" required class="input" autofocus />
          </div>

          <button type="submit" class="btn">
            Entrar
          </button>

          <div class="footerNote">
            <span class="kbd">Developed by: Dev1lB0y</span>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>