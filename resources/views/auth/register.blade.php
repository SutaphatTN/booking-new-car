@extends('layouts/blankLayout')

@section('title', 'Register Person')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('page-script')
@vite(['resources/js/pages/auth.js'])
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Register Card -->
      <div class="card px-sm-6 px-0">
        <div class="card-body">
          <!-- Logo -->
          <div class="justify-content-center">
            <div class="card-header text-center fs-4 fw-bold">{{ __('Register') }}</div>
          </div>
          <!-- /Logo -->
          <form id="registerForm" action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data" data-action="{{ route('register.store') }}">
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
              <label for="cardID" class="form-label">เลขบัตรประชาชน</label>
              <input type="text" class="form-control" id="cardID" name="cardID" maxlength="17" required>
            </div>

            <div class="mb-3">
              <label for="role" class="form-label">Role</label>
              <select name="role" class="form-select" required>
                <option value="">-- เลือก Role --</option>
                <option value="sale">Sale</option>
                <option value="audit">Audit</option>
                <option value="manager">Manager</option>
                <option value="md">MD</option>
              </select>
            </div>

            <div class="form-password-toggle">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password" aria-describedby="password" required>
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
            </div>
            <div class="d-grid mt-4">
              <button type="submit" class="btn btn-primary mb-3">{{ __('Register') }}</button>
              <a href="{{url('/home')}}" class="btn btn-secondary text-center">ย้อนกลับ</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection