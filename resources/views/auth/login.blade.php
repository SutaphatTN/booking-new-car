<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>New Car System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .branch-bg {
      background: url('../images/branch_chookiat.png') no-repeat center center;
      background-size: cover;
    }

    @media (max-width: 767.98px) {
      .branch-bg {
        min-height: 50vh;
      }
    }
  </style>
</head>

<body>
  <div class="container-fluid vh-100">
    <div class="row h-100">

      <div class="col-md-7 d-flex flex-column justify-content-center align-items-center text-white branch-bg"></div>

      <div class="col-md-5 d-flex flex-column justify-content-center p-5 bg-white">
        <h2 class="mb-4 text-center">Login</h2>

        <form method="POST" action="{{ route('login.store') }}">
          @csrf

          @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
          @endif

          {{-- Username --}}
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input id="username" type="text"
              class="form-control @error('username') is-invalid @enderror"
              name="username" value="{{ old('username') }}" required autofocus>

            @error('username')
            <span class="invalid-feedback">
              <strong>{{ $message }}</strong>
            </span>
            @enderror
          </div>

          {{-- Password --}}
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password"
              class="form-control @error('password') is-invalid @enderror"
              name="password" required>

            @error('password')
            <span class="invalid-feedback">
              <strong>{{ $message }}</strong>
            </span>
            @enderror
          </div>

          {{-- Remember Me --}}
          <div class="mb-3 d-flex justify-content-between align-items-center">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" id="remember"
                {{ old('remember') ? 'checked' : '' }}>
              <label class="form-check-label" for="remember">Remember Me</label>
            </div>

            <a class="text-decoration-none" href="{{ route('forgot.index') }}">
              Reset Password?
            </a>
          </div>

          @guest
          {{-- Login button --}}
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">Login</button>
          </div>

          {{-- Register / Sign Up --}}
          {{-- <div class="text-center">
                        <span>Don't have an account? </span>
                        <a href="{{ route('register') }}">Sign Up</a>
      </div> --}}
      @endguest
      </form>
    </div>
  </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>