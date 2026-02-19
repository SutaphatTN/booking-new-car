@extends('layouts/blankLayout')

@section('title', 'Register Person')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('page-script')
@vite(['resources/assets/js/auth.js'])
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
              <label for="name" class="form-label">ชื่อ - นามสกุล</label>
              <input type="text" class="form-control" id="name" name="name" autocomplete="off" required>
            </div>

            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" autocomplete="off" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" autocomplete="off" >
            </div>

            <div class="mb-3">
              <label for="cardID" class="form-label">เลขบัตรประชาชน</label>
              <input type="text" class="form-control" id="cardID" name="cardID" maxlength="17" required>
            </div>

            <div class="mb-3">
              <label for="branch" class="form-label">สาขา</label>
              <select id="branch" name="branch" class="form-select">
                <option value="">-- เลือกสาขา --</option>
                @foreach ($branch as $item)
                <option value="{{ @$item->id }}">{{ @$item->name }}</option>
                @endforeach
              </select>
            </div>

             <div class="mb-3">
              <label for="brand" class="form-label">Brand</label>
              <select id="brand" name="brand" class="form-select">
                <option value="">-- เลือก brand --</option>
                @foreach ($brand as $item)
                <option value="{{ @$item->id }}">{{ @$item->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label for="userZone" class="form-label">Zone</label>
              <select id="userZone" name="userZone" class="form-select" required>
                <option value="">-- เลือก Zone --</option>
                <option value="10">ปัตตานี</option>
                <option value="40">กระบี่</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="role" class="form-label">Role</label>
              <select id="role" name="role" class="form-select" required>
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
                <span class="btn btn-outline-secondary cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
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