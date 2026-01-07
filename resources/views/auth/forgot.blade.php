@extends('layouts/blankLayout')

@section('title', 'Forgot Password')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Forgot Password -->
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="justify-content-center">
                        <div class="card-header text-center fs-4 fw-bold">{{ __('Forgot Password') }}</div>
                    </div>
                    <!-- /Logo -->
                    <form method="POST" action="{{ route('forgot.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label fs-6">{{ __('Username') }}</label>
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                                name="username" value="{{ old('username') }}" required>

                            @error('username')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label fs-6">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" required>
                            @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary mb-3">{{ __('Reset Password') }}</button>
                            <a href="{{ route('login') }}" class="btn btn-secondary text-center">ย้อนกลับ</a>
                        </div>

                    </form>
                </div>
            </div>
            <!-- /Forgot Password -->
        </div>
    </div>
</div>

@endsection