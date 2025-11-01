@extends('layouts/blankLayout')

@section('title', 'Register Person')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
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
          <form
            action="{{ route('register.store') }}"
            method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <input type="text" class="form-control" id="name" name="name" />
            </div>

            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" />
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" />
            </div>

            <div class="mb-3">
              <label for="cardID" class="form-label">เลขบัตรประชาชน</label>
              <input type="text" class="form-control" id="cardID" name="cardID" />
            </div>

            <div class="mb-3">
              <label for="role" class="form-label">Role</label>
              <select name="role" class="form-select">
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
                <input type="password" id="password" class="form-control" name="password" aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
            </div>
            <!-- <div class="my-7">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                <label class="form-check-label" for="terms-conditions">
                  I agree to
                  <a href="javascript:void(0);">privacy policy & terms</a>
                </label>
              </div>
            </div> -->
            <div class="d-grid mt-4">
              <button type="submit" class="btn btn-primary mb-3">{{ __('Register') }}</button>
              <a href="#" class="btn btn-secondary text-center">ย้อนกลับ</a>
            </div>
          </form>

          <!-- <p class="text-center">
            <span>Already have an account?</span>
            <a href="{{ url('auth/login-basic') }}">
              <span>Sign in instead</span>
            </a>
          </p> -->
        </div>
      </div>
      <!-- Register Card -->
    </div>
  </div>
</div>
@endsection