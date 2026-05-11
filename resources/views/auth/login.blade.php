<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>New Car System</title>

  <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/chookiat.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/chookiat.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/chookiat.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/chookiat.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f0f2f5;
    }

    .branch-bg {
      background: url('../images/branch_chookiat.png') no-repeat center center;
      background-size: cover;
    }

    @media (max-width: 767.98px) {
      .branch-bg {
        min-height: 50vh;
      }
    }

    .login-panel {
      background-color: #f0f2f5;
    }

    .login-card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.10);
      padding: 2.5rem 2.5rem 2rem;
      width: 100%;
      max-width: 420px;
    }

    .login-logo {
      width: 64px;
      height: 64px;
      object-fit: contain;
    }

    .login-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #1a1a2e;
      letter-spacing: -0.3px;
    }

    .login-subtitle {
      font-size: 0.85rem;
      color: #6c757d;
    }

    .form-label {
      font-size: 0.8rem;
      font-weight: 600;
      color: #495057;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.4rem;
    }

    .input-group .form-control {
      border-left: 0;
      padding-left: 0;
    }

    .input-group-text {
      background-color: #fff;
      border-right: 0;
      color: #adb5bd;
    }

    .input-group:focus-within .input-group-text {
      border-color: #86b7fe;
      color: #0d6efd;
    }

    .form-control:focus {
      box-shadow: none;
      border-color: #86b7fe;
    }

    .btn-login {
      background-color: #1a1a2e;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-weight: 600;
      letter-spacing: 0.5px;
      padding: 0.65rem;
      transition: background-color 0.2s ease;
    }

    .btn-login:hover {
      background-color: #16213e;
      color: #fff;
    }

    .form-check-input:checked {
      background-color: #1a1a2e;
      border-color: #1a1a2e;
    }

    .reset-link {
      font-size: 0.85rem;
      color: #6c757d;
      text-decoration: none;
    }

    .reset-link:hover {
      color: #1a1a2e;
      text-decoration: underline;
    }

    .divider-text {
      font-size: 0.75rem;
      color: #adb5bd;
    }
  </style>
</head>

<body>
  <div class="container-fluid vh-100">
    <div class="row h-100">

      <div class="col-md-7 d-none d-md-flex flex-column justify-content-center align-items-center text-white branch-bg"></div>

      <div class="col-md-5 login-panel d-flex flex-column justify-content-center align-items-center p-4">
        <div class="login-card">

          {{-- Logo + Title --}}
          <div class="d-flex align-items-center gap-3 mb-4">
            <img src="{{ asset('assets/img/chookiat.png') }}" alt="Logo" class="login-logo">
            <div>
              <div class="login-title">เข้าสู่ระบบ</div>
              <div class="login-subtitle">New Car Management System</div>
            </div>
          </div>

          <hr class="my-3">

          <form method="POST" action="{{ route('login.store') }}">
            @csrf

            @if (session('error'))
            <div class="alert alert-danger py-2 small">
              <i class="bi bi-exclamation-circle me-1"></i>{{ session('error') }}
            </div>
            @endif

            {{-- Username --}}
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input id="username" type="text"
                  class="form-control @error('username') is-invalid @enderror"
                  name="username" value="{{ old('username') }}" placeholder="กรอก Username" required autofocus>
                @error('username')
                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input id="password" type="password"
                  class="form-control @error('password') is-invalid @enderror"
                  name="password" placeholder="กรอก Password" required>
                @error('password')
                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>
            </div>

            {{-- Remember Me + Reset --}}
            <div class="mb-4 d-flex justify-content-between align-items-center">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                  {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small" for="remember">จดจำฉัน</label>
              </div>
              <a class="reset-link" href="{{ route('forgot.index') }}">ลืมรหัสผ่าน?</a>
            </div>

            @guest
            <div class="d-grid">
              <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
              </button>
            </div>
            @endguest
          </form>

        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>